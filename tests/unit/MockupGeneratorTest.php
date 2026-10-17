<?php

use App\Commands\Mockups;
use App\Libraries\ContentVerifier;
use App\Libraries\MediaStore;
use App\Libraries\MockupGenerator;
use App\Models\MediaAssetModel;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockInputOutput;
use Config\Services;
use Tests\Support\Database\GradeFiveWorld;

/**
 * Gambar contoh (mockup) slot gambar tantangan (Dok 2 §10): 45 slot dari
 * bank soal kelas 5, SVG sah dengan ukuran slot, `is_placeholder = 1`, dan
 * gambar asli yang diunggah admin tidak pernah ditimpa, juga dengan --paksa.
 *
 * @internal
 */
final class MockupGeneratorTest extends CIUnitTestCase
{
    use GradeFiveWorld;

    private BaseConnection $conn;

    /** Folder mockup uji, relatif FCPATH */
    private string $dir;

    /** @var list<string> berkas tambahan di public/assets/uploads yang dihapus sesudah uji */
    private array $cleanup = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->conn = $this->createGradeFiveWorld();
        $this->dir  = MediaStore::UPLOAD_DIR . 'mockup-uji-' . bin2hex(random_bytes(4)) . '/';

        // Slot impor menunjuk ke assets/uploads/{kunci}.png; gambar asli yang mungkin
        // ada di folder unggahan mesin pengembang tidak boleh memengaruhi uji.
        $this->conn->table('media_assets')->like('asset_key', 'challenge.', 'after')
            ->set('storage_path', "'" . $this->dir . "belum-ada-' || id || '.png'", false)->update();
    }

    protected function tearDown(): void
    {
        CLI::resetInputOutput();
        array_map('unlink', glob(FCPATH . $this->dir . '*') ?: []);

        if (is_dir(FCPATH . $this->dir)) {
            rmdir(FCPATH . $this->dir);
        }

        foreach ($this->cleanup as $path) {
            if (is_file(FCPATH . $path)) {
                unlink(FCPATH . $path);
            }
        }

        $this->dropResetTables();
        parent::tearDown();
    }

    public function testBankKelas5HasEveryChallengeImageSlot(): void
    {
        $slots = $this->generator()->slots();

        $this->assertCount(66, $slots);
        $this->assertSame(
            ['object' => 30, 'puzzle' => 30, 'scene' => 3, 'statement' => 3],
            $this->tally(array_column($slots, 'kind')),
        );
        $this->assertSame(['empty'], array_values(array_unique(array_column($slots, 'status'))), 'slot impor belum berisi berkas');

        foreach (['challenge.item.tmg-1-01', 'challenge.item.tmg-3-a03', 'challenge.tmg-4.scene', 'challenge.item.tmg-4-12',
            'challenge.item.mgl-1-03', 'challenge.mgl-4.scene', 'challenge.item.mgl-4-a10', 'challenge.item.wnb-1-07', 'challenge.wnb-4.scene', 'challenge.item.wnb-4-a12'] as $key) {
            $this->assertContains($key, array_column($slots, 'asset_key'));
        }

        $this->assertCount(9, $this->generator()->slots('tmg-4'), '--node: adegan + 8 benda');
        $this->assertSame([], $this->generator()->slots('tmg-2'), 'Isi Rumpang tanpa gambar');
    }

    public function testGeneratesValidSvgWithTheSlotSizeAndMarksThemAsPlaceholders(): void
    {
        $result = $this->generator()->generateMissing();

        $this->assertSame(66, $result['count']);
        $this->assertSame(66, $result['created']);
        $this->assertSame([], $result['failed']);
        $this->assertSame(66, model(MediaAssetModel::class)->placeholderCount());

        $sizes = config('Gelita')->assetSizes;

        foreach ($this->challengeMedia() as $row) {
            $this->assertSame($this->dir . str_replace(['/', ' '], '-', $row['asset_key']) . '.svg', $row['storage_path']);
            $this->assertSame('1', (string) $row['is_placeholder'], $row['asset_key']);
            $this->assertSame('1', (string) $row['is_active'], $row['asset_key']);
            $this->assertSame(MockupGenerator::CREDIT, $row['credit']);
            $this->assertSame('image/svg+xml', $row['mime_type']);

            $kind = MockupGenerator::kindFor($row['asset_key']);
            [$w, $h] = MockupGenerator::SIZES[$kind];
            $this->assertSame([$w, $h], $sizes[match ($kind) {
                'scene' => 'challenge.*.scene', 'puzzle' => 'challenge.item.*-1-*', 'object' => 'challenge.item.*-4-*', 'statement' => 'challenge.item.*-3-*',
            }], 'ukuran mockup = ukuran wajib slot');
            $this->assertSame([$w, $h], [(int) $row['width_px'], (int) $row['height_px']]);

            $svg = $this->svgRoot(FCPATH . $row['storage_path']);
            $this->assertSame((string) $w, $svg->getAttribute('width'), $row['asset_key']);
            $this->assertSame((string) $h, $svg->getAttribute('height'));
            $this->assertSame("0 0 {$w} {$h}", $svg->getAttribute('viewBox'));
            $this->assertStringContainsString('Plus Jakarta Sans', $svg->getAttribute('font-family'));
            $this->assertStringContainsString('GAMBAR CONTOH', $svg->textContent);
            $this->assertStringContainsString((string) $row['label'], $svg->textContent, 'label tertulis di mockup');
        }
    }

    public function testEachKindFollowsSection103(): void
    {
        $this->generator()->generateMissing();

        // Adegan: satu alas di bawah setiap posisi benda, spanduk judul variasi
        $scene  = $this->svgRoot(FCPATH . $this->path('challenge.tmg-4.scene'));
        $xpath  = new DOMXPath($scene->ownerDocument);
        $xpath->registerNamespace('s', 'http://www.w3.org/2000/svg');
        $this->assertSame(8, $xpath->query('//s:rect[@class="alas"]')->length, '4 target + 4 pengecoh tmg-4');
        $this->assertStringContainsString('Pasar Budaya Temanggung', $scene->textContent);
        $this->assertSame(12, $this->platforms('challenge.wnb-4.scene'), '4 target + 8 pengecoh wnb-4');

        // Alas tepat di bawah kotak benda (x, y, w %; tinggi kotak w × 16/9 %)
        $first = $xpath->query('//s:rect[@class="alas"]')->item(0);
        $tops  = [];

        foreach (array_column($this->generator()->slots('tmg-4'), 'positions', 'asset_key')['challenge.tmg-4.scene'] as $c) {
            $tops[] = sprintf('%.1f', $c['y'] / 100 * 720 + $c['w'] / 100 * 1280);
        }

        $this->assertCount(8, $tops);
        $this->assertContains($first->getAttribute('y'), $tops);

        // Susun Gambar: tanpa angka urutan (teks hanya label, pita, dan kunci aset)
        $puzzle = $this->svgRoot(FCPATH . $this->path('challenge.item.tmg-1-01'));

        foreach ($puzzle->getElementsByTagName('text') as $text) {
            $this->assertDoesNotMatchRegularExpression('/^\s*\d+\s*$/', $text->textContent);
        }

        // Benda: latar transparan (tanpa persegi penuh), target dan pengecoh bergaya identik
        $target = (string) file_get_contents(FCPATH . $this->path('challenge.item.tmg-4-03'));
        $decoy  = (string) file_get_contents(FCPATH . $this->path('challenge.item.tmg-4-09'));
        $this->assertStringNotContainsString('<rect width="240" height="240"', $target);
        $this->assertSame($this->skeleton($target), $this->skeleton($decoy), 'tidak ada beda rupa target dan pengecoh selain ikon dan nama');

        // Palet per wilayah
        $this->assertStringContainsString(MockupGenerator::PALETTES['mgl']['accent'], (string) file_get_contents(FCPATH . $this->path('challenge.item.mgl-1-01')));
        $this->assertStringContainsString(MockupGenerator::PALETTES['wnb']['accent'], (string) file_get_contents(FCPATH . $this->path('challenge.item.wnb-4-a01')));

        // Gambar pernyataan: label dan keterangan
        $statement = $this->svgRoot(FCPATH . $this->path('challenge.item.tmg-3-a03'));
        $this->assertStringContainsString('Butir padi hangus', $statement->textContent);
        $this->assertStringContainsString('butir padi hangus kehitaman', $statement->textContent);

        // Catatan admin dalam kurung tidak ikut tertulis (membocorkan kunci pernyataan)
        $this->assertStringContainsString('sengaja tidak sesuai bacaan', (string) $this->row('challenge.item.tmg-3-b03')['description']);
        $this->assertStringNotContainsString('sengaja', $this->svgRoot(FCPATH . $this->path('challenge.item.tmg-3-b03'))->textContent);
    }

    public function testRealImagesAreNeverOverwrittenEvenWithForce(): void
    {
        $generator = $this->generator();
        $generator->generateMissing();

        $mockup = FCPATH . $this->path('challenge.item.tmg-4-03');
        $this->assertFileExists($mockup);

        // Admin mengganti satu mockup dengan gambar asli lewat MediaStore
        if (is_file(FCPATH . MediaStore::UPLOAD_DIR . 'challenge.item.tmg-4-03.png')) {
            $this->markTestSkipped('Folder unggahan sudah berisi gambar asli challenge.item.tmg-4-03.png; uji tidak menimpanya.');
        }

        $png    = $this->png(240, 240);
        $stored = (new MediaStore())->storeCopy($png, 'challenge.item.tmg-4-03', ['image'], null, true);
        unlink($png);
        $this->assertNull($stored['error']);

        $row = $this->row('challenge.item.tmg-4-03');
        $this->cleanup[] = $row['storage_path'];
        $this->assertSame('0', (string) $row['is_placeholder'], 'unggahan admin = gambar asli');
        $this->assertNull($row['credit'], 'kredit mockup dibuang');
        $this->assertFileDoesNotExist($mockup, 'berkas mockup lama dibuang');
        $this->assertSame(65, model(MediaAssetModel::class)->placeholderCount());

        $hash = hash_file('sha256', FCPATH . $row['storage_path']);

        foreach ([[false, 'existing'], [true, 'refresh']] as [$force, $action]) {
            $result = $generator->run($force);
            $this->assertSame(1, $result['kept']);
            $this->assertSame(65, $force ? $result['refreshed'] : $result['existing']);
            $this->assertSame(0, $result['created']);

            $actions = array_column($result['slots'], 'action', 'asset_key');
            $this->assertSame('kept', $actions['challenge.item.tmg-4-03']);
            $this->assertSame($action, $actions['challenge.item.tmg-4-04']);
            $this->assertSame($row, $this->row('challenge.item.tmg-4-03'), 'baris gambar asli tidak berubah');
            $this->assertSame($hash, hash_file('sha256', FCPATH . $row['storage_path']));
        }

        // Gambar asli yang dinonaktifkan admin juga tidak ditimpa
        $this->conn->table('media_assets')->where('asset_key', 'challenge.item.tmg-4-03')->update(['is_active' => 0]);
        $this->assertSame('kept', array_column($generator->run(true)['slots'], 'action', 'asset_key')['challenge.item.tmg-4-03']);
        $this->assertSame('0', (string) $this->row('challenge.item.tmg-4-03')['is_active']);
    }

    public function testMissingMockupFileIsRecreatedAndDryRunWritesNothing(): void
    {
        $generator = $this->generator();

        $plan = $generator->run(false, true);
        $this->assertSame(66, $plan['created']);
        $this->assertSame(0, $plan['count']);
        $this->assertDirectoryDoesNotExist(FCPATH . $this->dir);
        $this->assertSame(0, model(MediaAssetModel::class)->placeholderCount());

        $generator->generateMissing();
        unlink(FCPATH . $this->path('challenge.item.wnb-1-05'));

        $again = $generator->generateMissing();
        $this->assertSame(1, $again['created']);
        $this->assertSame(65, $again['existing']);
        $this->assertFileExists(FCPATH . $this->path('challenge.item.wnb-1-05'));
    }

    public function testResearchReadyVerificationFailsWhileMockupsRemain(): void
    {
        $this->generator()->generateMissing();

        $ready = (new ContentVerifier())->run(true);
        $this->assertGreaterThan(0, ContentVerifier::errorCount($ready));
        $this->assertStringContainsString('66 gambar masih gambar contoh', implode(' ', array_column($ready, 'message')));

        $normal = (new ContentVerifier())->run();
        $this->assertSame(0, ContentVerifier::errorCount($normal), 'tanpa --siap-penelitian mockup hanya peringatan');
    }

    public function testVerifierSuggestsMockupsOnlyForMockupSlots(): void
    {
        $this->conn->table('media_assets')->insert([
            'asset_key' => 'map.region.uji', 'asset_type' => 'image', 'storage_path' => 'assets/uploads/peta-uji.png',
            'mime_type' => 'image/png', 'is_active' => 0,
        ]);
        $missing = (int) $this->conn->insertID();
        service('contentRepository')->flush();

        $findings = new ReflectionMethod(ContentVerifier::class, 'mediaFindings');
        $verifier = new ContentVerifier();
        $region   = $findings->invoke($verifier, $missing, 'Wilayah Temanggung', 'Gambar peta wilayah')[0]['message'];
        $item     = $findings->invoke($verifier, $missing, 'tmg-1', 'Gambar soal', true)[0]['message'];

        // Peta, latar, lencana, dan gambar bacaan bukan slot gelita:mockups (Dok 2 §10.4)
        $this->assertStringEndsWith('belum diunggah; permainan memakai tampilan pengganti. Unggah di menu Gambar & mockup.', $region);
        $this->assertStringNotContainsString('gelita:mockups', $region);
        $this->assertStringContainsString('Jalankan php spark gelita:mockups', $item);
    }

    public function testCommandReportsThePlan(): void
    {
        $io = new MockInputOutput();
        CLI::setInputOutput($io);

        try {
            $code = (new Mockups(Services::logger(), Services::commands()))->run(['dry-run' => null, 'node' => 'tmg-4']);
            $bad  = (new Mockups(Services::logger(), Services::commands()))->run(['node' => 'xyz-9']);
            $eq   = (new Mockups(Services::logger(), Services::commands()))->run(['dry-run' => null, 'node=wnb-4' => null]);
        } finally {
            CLI::resetInputOutput();
        }

        $this->assertSame(EXIT_SUCCESS, $code);
        $this->assertSame(EXIT_ERROR, $bad);
        $output = $io->getOutput();
        $this->assertStringContainsString('--dry-run: tidak ada yang diubah. Rencana: 9 dibuat', $output);
        $this->assertStringContainsString('challenge.item.tmg-4-12', $output);
        $this->assertSame(EXIT_SUCCESS, $eq);
        $this->assertStringContainsString('Rencana: 13 dibuat', $output, '--node=wnb-4: adegan + 12 benda');
        $this->assertSame(0, model(MediaAssetModel::class)->placeholderCount());
    }

    // ------------------------------------------------------------ bantu

    private function generator(): MockupGenerator
    {
        return new MockupGenerator($this->conn, $this->dir);
    }

    /** @return list<array<string, mixed>> */
    private function challengeMedia(): array
    {
        $keys = array_column($this->generator()->slots(), 'asset_key');

        return $this->conn->table('media_assets')->whereIn('asset_key', $keys)->get()->getResultArray();
    }

    /** @return array<string, mixed> */
    private function row(string $key): array
    {
        return $this->conn->table('media_assets')->where('asset_key', $key)->get()->getRowArray();
    }

    private function path(string $key): string
    {
        return (string) $this->row($key)['storage_path'];
    }

    private function platforms(string $key): int
    {
        $xpath = new DOMXPath($this->svgRoot(FCPATH . $this->path($key))->ownerDocument);
        $xpath->registerNamespace('s', 'http://www.w3.org/2000/svg');

        return $xpath->query('//s:rect[@class="alas"]')->length;
    }

    private function svgRoot(string $file): DOMElement
    {
        $dom = new DOMDocument();
        $this->assertTrue($dom->loadXML((string) file_get_contents($file)), 'SVG sah: ' . $file);
        $this->assertSame('svg', $dom->documentElement->localName);
        $this->assertSame('http://www.w3.org/2000/svg', $dom->documentElement->namespaceURI);

        return $dom->documentElement;
    }

    /** SVG benda tanpa teks, ikon, dan judul — kerangka rupa yang harus sama. */
    private function skeleton(string $svg): string
    {
        $svg = (string) preg_replace('#<(text|title)\b[^>]*>.*?</\1>#s', '', $svg);
        $svg = (string) preg_replace('#aria-label="[^"]*"#', '', $svg);

        return (string) preg_replace('#<g class="ikon">.*?</g>#s', '', $svg);
    }

    /**
     * @param list<string> $values
     *
     * @return array<string, int>
     */
    private function tally(array $values): array
    {
        $out = array_count_values($values);
        ksort($out);

        return $out;
    }

    private function png(int $w, int $h): string
    {
        $image = imagecreatetruecolor($w, $h);
        imagefill($image, 0, 0, imagecolorallocate($image, 200, 120, 40));
        $file = tempnam(sys_get_temp_dir(), 'asli') . '.png';
        imagepng($image, $file);
        imagedestroy($image);

        return $file;
    }
}
