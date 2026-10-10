<?php

use App\Commands\PackageBuild;
use App\Commands\PackageCheck;
use App\Commands\PackagePrune;
use App\Models\AssetPackageModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\Mock\MockInputOutput;
use Config\Services;
use Tests\Support\Database\PackageWorld;

/**
 * `gelita:paket:bangun`, `gelita:paket:periksa`, `gelita:paket:bersihkan`,
 * and `GET /api/paket/aktif` (docs/rencana-paket-aset.md §5.3, §5.5, §6.3).
 *
 * @internal
 */
final class AssetPackageCommandTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use PackageWorld;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['gelita', 'content']);
        $this->createPackageWorld();
    }

    protected function tearDown(): void
    {
        $this->dropPackageWorld();
        parent::tearDown();
    }

    public function testBuildCommandPrintsTheSummaryAndCheckPasses(): void
    {
        [$exit, $out] = $this->command(PackageBuild::class, ['tanpa-pustaka' => null, 'catatan' => 'paket uji']);

        $row = model(AssetPackageModel::class)->latestBuilt();
        $this->assertSame(EXIT_SUCCESS, $exit, $out);
        $this->assertStringContainsString('Paket ' . $row['package_code'] . ' selesai', $out);
        $this->assertStringContainsString('wilayah-temanggung', $out);
        $this->assertStringContainsString('gambar contoh yang belum diganti: 1', $out);
        $this->assertStringContainsString('narasi belum disetujui (tidak ikut paket): 1', $out);
        $this->assertSame('paket uji', $row['notes']);

        // Without an argument: the latest built package (no release names one yet)
        [$exit, $out] = $this->command(PackageCheck::class, []);
        $this->assertSame(EXIT_SUCCESS, $exit, $out);
        $this->assertStringContainsString('Paket ' . $row['package_code'] . ' utuh', $out);

        unlink($this->public . $this->packages->manifest($row['package_code'])['files'][0]['url']);
        [$exit, $out] = $this->command(PackageCheck::class, [$row['package_code']]);
        $this->assertSame(EXIT_ERROR, $exit);
        $this->assertStringContainsString('berkas tidak ada', $out);
    }

    public function testCheckWithoutAnyPackageFails(): void
    {
        [$exit, $out] = $this->command(PackageCheck::class, []);

        $this->assertSame(EXIT_ERROR, $exit);
        $this->assertStringContainsString('Belum ada paket aset', $out);
    }

    public function testPruneCommandRefusesAPackageInUse(): void
    {
        $this->command(PackageBuild::class, ['tanpa-pustaka' => null]);
        $code = model(AssetPackageModel::class)->latestBuilt()['package_code'];
        $this->useRelease($code);

        [$exit, $out] = $this->command(PackagePrune::class, ['hapus' => $code]);
        $this->assertSame(EXIT_ERROR, $exit);
        $this->assertStringContainsString("Paket {$code} tidak dihapus", $out);
        $this->assertNotNull(model(AssetPackageModel::class)->findByCode($code));

        $this->useRelease('1');
        [$exit, $out] = $this->command(PackagePrune::class, ['hapus' => $code]);
        $this->assertSame(EXIT_SUCCESS, $exit, $out);
        $this->assertNull(model(AssetPackageModel::class)->findByCode($code));
        $this->assertFileDoesNotExist($this->packages->manifestPath($code));

        // The deleted code is never handed out again (its manifest URL is cached as immutable)
        $this->command(PackageBuild::class, ['tanpa-pustaka' => null]);
        $this->assertStringEndsWith('-02', model(AssetPackageModel::class)->latestBuilt()['package_code']);
    }

    public function testActivePackageApi(): void
    {
        $response = $this->get('api/paket/aktif');
        $response->assertOK();
        $this->assertStringContainsString('no-store', $response->response()->getHeaderLine('Cache-Control'));
        $this->assertNull($this->data($response)['package_code']);
        $this->assertFalse($this->data($response)['disabled_by_server']);

        $this->command(PackageBuild::class, ['tanpa-pustaka' => null]);
        $row = model(AssetPackageModel::class)->latestBuilt();
        $this->useRelease($row['package_code']);

        $data = $this->data($this->get('api/paket/aktif'));
        $this->assertSame($row['package_code'], $data['package_code']);
        $this->assertSame('paket/' . $row['package_code'] . '.json', $data['manifest_url']);
        $this->assertSame($row['manifest_sha256'], $data['manifest_sha256']);
        $this->assertSame(hash_file('sha256', $this->public . $data['manifest_url']), $data['manifest_sha256']);
        $this->assertSame((int) $row['total_bytes'], $data['total_bytes']);
        $this->assertSame((int) $row['file_count'], $data['file_count']);

        // Kill switch: package_code null even though the release names one
        config('Gelita')->paket['aktif'] = false;
        $this->packages->forget();
        $data = $this->data($this->get('api/paket/aktif'));
        $this->assertNull($data['package_code']);
        $this->assertNull($data['manifest_sha256']);
        $this->assertTrue($data['disabled_by_server']);
    }

    /**
     * @param class-string<BaseCommand> $class
     * @param array<int|string, mixed>  $params
     *
     * @return array{0: int, 1: string}
     */
    private function command(string $class, array $params): array
    {
        $io = new MockInputOutput();
        CLI::setInputOutput($io);

        try {
            $code = (new $class(Services::logger(), Services::commands()))->run($params);
        } finally {
            CLI::resetInputOutput();
        }

        $this->packages->forget();

        return [(int) $code, $io->getOutput()];
    }

    /** @return array<string, mixed> */
    private function data(\CodeIgniter\Test\TestResponse $response): array
    {
        return json_decode($response->getJSON(), true)['data'];
    }
}
