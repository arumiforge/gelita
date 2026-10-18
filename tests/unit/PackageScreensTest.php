<?php

use App\Controllers\Game\PackageController;
use App\Services\AssetPackageBuilder;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\PackageWorld;

/**
 * Asset package Stage 3 (docs/rencana-paket-aset.md §5.1, §8.7, P11): the
 * "Siapkan GELITA" and "Periksa paket" screens and the Mulai gate markers.
 * What happens in the browser (download, online choice, teacher buttons) is
 * covered by tools/qa/package-screens.mjs.
 *
 * @internal
 */
final class PackageScreensTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use PackageWorld;

    private string $code;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['gelita', 'content']);
        $this->createPackageWorld();
        $this->code = (new AssetPackageBuilder($this->packages))->build(['copy_library' => false])['package_code'];
    }

    protected function tearDown(): void
    {
        $this->dropPackageWorld();
        parent::tearDown();
    }

    private function fresh(string $path): CodeIgniter\Test\TestResponse
    {
        Config\Services::resetSingle('response');

        return $this->get($path);
    }

    public function testWithPackageModeOffThereIsNoDownloadScreen(): void
    {
        $this->fresh('siapkan')->assertRedirectTo(site_url('mulai'));
        $this->fresh('siapkan?lanjut=masuk')->assertRedirectTo(site_url('masuk'));
    }

    public function testDownloadScreenListsEveryPartInDownloadOrder(): void
    {
        $this->useRelease($this->code);
        $response = $this->fresh('siapkan');
        $response->assertOK();
        $html = $response->getBody();

        $this->assertStringContainsString('data-screen="prepare"', $html);
        $this->assertStringContainsString(lang('Package.prepareTitle'), $html);
        $this->assertMatchesRegularExpression('/<button[^>]*data-prepare-start[^>]*disabled/', $html, 'Mulai starts disabled');
        $this->assertStringNotContainsString('start-jaka', $html, 'full width, no character');
        $this->assertStringContainsString('role="progressbar"', $html);
        $this->assertStringContainsString('role="status"', $html);
        $this->assertStringContainsString('data-next="' . base_url('mulai') . '"', $html);

        preg_match_all('/data-group="([^"]+)"/', $html, $groups);
        $sizes    = array_filter($this->packages->manifest($this->code)['groups'], static fn (int $bytes): bool => $bytes > 0);
        $expected = array_values(array_intersect(AssetPackageBuilder::groupOrder(), array_keys($sizes)));
        $this->assertSame($expected, $groups[1]);
        $this->assertStringContainsString('Wilayah Magelang', $html);
    }

    public function testOnlyKnownPagesAreAllowedAfterTheDownload(): void
    {
        $this->useRelease($this->code);

        foreach (['masuk' => 'masuk', 'daftar/2' => 'daftar/2', 'mulai' => 'mulai', 'admin' => 'mulai', '//evil.example' => 'mulai', 'daftar/9' => 'mulai'] as $asked => $next) {
            $html = $this->fresh('siapkan?lanjut=' . rawurlencode($asked))->getBody();
            $this->assertStringContainsString('data-next="' . base_url($next) . '"', $html, $asked);
        }
    }

    public function testInspectScreenOpensWithPackageModeOnAndOff(): void
    {
        foreach ([null, $this->code] as $code) {
            if ($code !== null) {
                $this->useRelease($code);
            }
            $response = $this->fresh('periksa-paket');
            $response->assertOK();
            $html = $response->getBody();
            $this->assertStringContainsString('data-screen="package-inspect"', $html);

            foreach (['verify', 'repair', 'persist', 'clear'] as $action) {
                $this->assertStringContainsString('data-inspect-action="' . $action . '"', $html);
            }
        }
    }

    public function testPagesBeforeASessionCarryTheGate(): void
    {
        $this->assertStringContainsString('data-package-gate="link"', $this->fresh('/')->getBody());
        $this->assertStringContainsString('data-package-start', $this->fresh('/')->getBody());
        $this->assertStringContainsString('data-package-gate="page" data-package-next="mulai"', $this->fresh('mulai')->getBody());
        $this->assertStringContainsString('data-package-gate="page" data-package-next="masuk"', $this->fresh('masuk')->getBody());
    }

    public function testGroupLabels(): void
    {
        $this->assertSame(lang('Package.groupKode'), PackageController::groupLabel('kode'));
        $this->assertSame(lang('Package.groupInti'), PackageController::groupLabel('inti'));
        $this->assertSame('Wilayah Wonosobo', PackageController::groupLabel('wilayah-wonosobo'));
        $this->assertSame(lang('Package.groupPenutup'), PackageController::groupLabel('penutup'));
    }
}
