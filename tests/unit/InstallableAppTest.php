<?php

use App\Controllers\Game\AppController;
use App\Services\ContentRepository;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

/**
 * GELITA sebagai aplikasi terpasang (layar utama).
 *
 * - The manifest (`GET /manifest.json`, AppController) locks landscape and
 *   fullscreen; its URLs are relative so an install in a subfolder stays
 *   right. Icons come from the panel slots `app.*` with `?v=`, or from the
 *   static files in `assets/app/`, which must exist at the promised sizes
 *   (Chrome refuses to install when the 192/512 icons do not match).
 * - The manifest route starts no session (LocaleFilter::STATELESS_PATHS).
 * - The offline page is self-contained. What the service worker may cache
 *   (package files and the offline page only, never HTML, API, or admin) is
 *   checked by AssetPackageServiceWorkerTest (asset package plan §9.1).
 *
 * @internal
 */
final class InstallableAppTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    /** asset_key → versioned storage path returned by the fake ContentRepository */
    private array $media = [];

    protected function setUp(): void
    {
        parent::setUp();
        helper(['url', 'ui']);

        $test = $this;
        Services::injectMock('contentRepository', new class ($test) extends ContentRepository {
            public function __construct(private InstallableAppTest $test)
            {
            }

            public function mediaKeyMap(): array
            {
                return $this->test->media();
            }
        });
    }

    protected function tearDown(): void
    {
        Services::resetSingle('contentRepository');
        parent::tearDown();
    }

    /** @return array<string, string> */
    public function media(): array
    {
        return $this->media;
    }

    /** @return array<string, mixed> */
    private function manifest(): array
    {
        $response = $this->call('GET', 'manifest.json');
        $response->assertStatus(200);
        $this->assertStringStartsWith('application/manifest+json', $response->response()->getHeaderLine('Content-Type'));
        $this->assertSame('', $response->response()->getHeaderLine('Set-Cookie'), 'no session for the manifest');

        return json_decode((string) $response->response()->getBody(), true, 512, JSON_THROW_ON_ERROR);
    }

    public function testStaticManifestFileIsGone(): void
    {
        $this->assertFileDoesNotExist(FCPATH . 'manifest.json', 'a static file would shadow the route on Nginx and Apache');
    }

    public function testManifestLocksLandscapeFullscreen(): void
    {
        $manifest = $this->manifest();

        $this->assertSame('landscape', $manifest['orientation']);
        $this->assertSame('fullscreen', $manifest['display']);
        $this->assertSame(['fullscreen', 'standalone'], $manifest['display_override']);
        $this->assertSame('GELITA', $manifest['short_name']);

        foreach (['id', 'start_url', 'scope'] as $key) {
            $this->assertSame('./', $manifest[$key], "{$key} relatif terhadap manifest");
        }
    }

    public function testFallbackIconsExistWithDeclaredSizes(): void
    {
        $purposes = [];

        foreach ($this->manifest()['icons'] as $icon) {
            $this->assertStringStartsWith(base_url('assets/app/'), $icon['src'], 'empty slots use the static icon');
            $path = FCPATH . substr($icon['src'], strlen(base_url()));
            $this->assertFileExists($path);

            [$width, $height, $type] = getimagesize($path);
            $this->assertSame($icon['sizes'], "{$width}x{$height}", $icon['src']);
            $this->assertSame(IMAGETYPE_PNG, $type, $icon['src']);

            $purposes[$icon['purpose']][] = $icon['sizes'];
        }

        $this->assertContains('192x192', $purposes['any']);
        $this->assertContains('512x512', $purposes['any']);
        $this->assertContains('512x512', $purposes['maskable']);

        [$width, $height] = getimagesize(FCPATH . 'assets/app/apple-touch-icon.png');
        $this->assertSame([180, 180], [$width, $height]);
        $this->assertFileExists(FCPATH . 'favicon.ico');
    }

    public function testUploadedIconsReplaceTheStaticOnes(): void
    {
        $this->media = [
            'app.icon-192'      => 'assets/uploads/app.icon-192.png?v=aaa111',
            'app.icon-maskable' => 'assets/uploads/app.icon-maskable.png?v=bbb222',
            'app.favicon'       => 'assets/uploads/app.favicon.png?v=ccc333',
        ];

        $icons = array_column($this->manifest()['icons'], 'src');

        $this->assertSame([
            base_url('assets/uploads/app.icon-192.png?v=aaa111'),
            base_url('assets/app/icon-512.png'),
            base_url('assets/uploads/app.icon-maskable.png?v=bbb222'),
        ], $icons);

        $this->assertSame(base_url('assets/uploads/app.favicon.png?v=ccc333'), app_icon_src('app.favicon', 'favicon.ico'));
        $this->assertSame(base_url('assets/app/apple-touch-icon.png'), app_icon_src('app.apple-touch', 'assets/app/apple-touch-icon.png'));
    }

    public function testIconSlotsAreSizedPngSlots(): void
    {
        foreach (array_keys(AppController::MANIFEST_ICONS) as $slot) {
            $this->assertSame(['png'], App\Libraries\ImageSlots::types($slot), $slot);
            $this->assertNotNull((new App\Libraries\MediaStore())->requiredSize($slot), $slot);
        }
    }

    public function testLayoutsUseTheIconSlots(): void
    {
        $game = (string) file_get_contents(APPPATH . 'Views/layouts/game.php');
        $this->assertStringContainsString("<link rel=\"manifest\" href=\"<?= base_url('manifest.json') ?>\">", $game);
        $this->assertStringContainsString("app_icon_src('app.apple-touch', 'assets/app/apple-touch-icon.png')", $game);

        foreach (['game', 'admin', 'auth'] as $layout) {
            $html = (string) file_get_contents(APPPATH . "Views/layouts/{$layout}.php");
            $this->assertStringContainsString("app_icon_src('app.favicon', 'favicon.ico')", $html, $layout);

            if ($layout !== 'game') {
                $this->assertStringNotContainsString('rel="manifest"', $html, 'only the game is installable');
            }
        }
    }

    public function testOfflinePageIsSelfContained(): void
    {
        $this->assertFileExists(FCPATH . 'offline.html');
        $html = (string) file_get_contents(FCPATH . 'offline.html');

        // Saat offline tidak ada berkas lain yang dapat diambil
        $this->assertDoesNotMatchRegularExpression('/\s(?:src|href)="(?!#)/', $html);
        $this->assertStringContainsString('lang="en"', $html, 'teks dwibahasa');
    }
}
