<?php

use App\Entities\GameSession;
use App\Filters\PackageHeaderFilter;
use App\Services\AssetPackageBuilder;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\PackageWorld;

/**
 * Asset package Stage 2, server side (docs/rencana-paket-aset.md §5.2, §6.2,
 * §11.1): code URLs, the boot script attributes, package audio for
 * core/audio.js, and the `X-Gelita-Paket` header on API responses.
 *
 * With package mode off everything is as before Stage 2; with a package the
 * game's CSS/JS entries point at `assets/k/{code_version}/…`; the `.env` kill
 * switch turns both off and asks pages for the empty service worker.
 *
 * @internal
 */
final class AssetPackageCodeUrlTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use PackageWorld;

    private string $code;

    private string $codeDir;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['gelita', 'content']);
        $this->createPackageWorld();

        $summary       = (new AssetPackageBuilder($this->packages))->build(['copy_library' => false]);
        $this->code    = $summary['package_code'];
        $this->codeDir = $this->packages->manifest($this->code)['code_dir'];
    }

    protected function tearDown(): void
    {
        $this->dropPackageWorld();
        parent::tearDown();
    }

    private function versioned(string $path): string
    {
        return base_url('assets/' . $path) . '?v=' . rawurlencode(asset_file_version($path));
    }

    public function testWithoutPackageEverythingIsAsBefore(): void
    {
        $this->assertNull($this->packages->activeCode());

        $this->assertSame($this->versioned('js/game.js'), asset_url_versioned('js/game.js'));
        $this->assertSame($this->versioned('css/base.css'), asset_url_versioned('/css/base.css'));
        $this->assertSame(base_url('assets/fonts/a.woff2'), game_asset_url('fonts/a.woff2'));
        $this->assertNull(asset_package_path('js/game.js'));
        $this->assertSame('', asset_package_attrs());
        $this->assertSame([], package_audio_urls());
        $this->assertSame([], js_config('id')['audioUrls']);
    }

    public function testActivePackageServesGameCodeFromTheCodeFolder(): void
    {
        $this->useRelease($this->code);

        $this->assertMatchesRegularExpression('#^assets/k/2\.0\.0-[0-9a-f]{8}/$#', $this->codeDir);
        $this->assertSame(base_url($this->codeDir . 'js/game.js'), asset_url_versioned('js/game.js'));
        $this->assertSame(base_url($this->codeDir . 'css/base.css'), asset_url_versioned('css/base.css'));
        $this->assertSame(base_url($this->codeDir . 'vendor/howler.min.js'), asset_url_versioned('vendor/howler.min.js'));
        $this->assertSame(base_url($this->codeDir . 'fonts/a.woff2'), game_asset_url('fonts/a.woff2'), 'font preload matches the URL the CSS asks for');
        $this->assertFileExists($this->public . $this->codeDir . 'js/game.js');

        // Not packaged: admin code and files added after the build keep their normal URLs
        $this->assertSame($this->versioned('css/admin.css'), asset_url_versioned('css/admin.css'));
        $this->assertSame($this->versioned('js/admin.js'), asset_url_versioned('js/admin.js'));
        $this->assertSame($this->versioned('js/core/package-boot.js'), asset_url_versioned('js/core/package-boot.js'));
        $this->assertSame(base_url('assets/fonts/new.woff2'), game_asset_url('fonts/new.woff2'));

        // A package copy missing on disk (restore without assets/k/) falls back instead of breaking the page
        unlink($this->public . $this->codeDir . 'css/base.css');
        $this->assertSame($this->versioned('css/base.css'), asset_url_versioned('css/base.css'));
    }

    public function testBootScriptAttributesCarryOnlyThePackageCode(): void
    {
        $this->useRelease($this->code);
        $this->assertSame(' data-package="' . $this->code . '"', asset_package_attrs());

        config('Gelita')->paket['aktif'] = false;
        $this->packages->forget();
        $this->assertSame(' data-sw="off"', asset_package_attrs(), 'kill switch: no package, empty service worker');
        $this->assertSame($this->versioned('js/game.js'), asset_url_versioned('js/game.js'));
        $this->assertSame([], package_audio_urls());
    }

    public function testAnOpenGameSessionKeepsItsOwnPackageUntilItEnds(): void
    {
        $this->useRelease($this->code);
        $newer = (new AssetPackageBuilder($this->packages))->build(['copy_library' => false])['package_code'];
        $this->useRelease($newer);

        $context = service('gameContext');
        $session = static fn (array $row): GameSession => new GameSession($row + ['id' => 1, 'participant_id' => 1, 'status' => 'active']);

        try {
            // Started on the older package: its pages keep naming it (S8); devices still learn the active one
            $context->session = $session(['package_code' => $this->code, 'package_mode' => 'lengkap']);
            $this->assertSame($this->code, $this->packages->pageCode());
            $this->assertSame(' data-package="' . $this->code . '"', asset_package_attrs());
            $this->assertSame($this->packages->map($this->code)['keys']['bg.welcome'], $this->packages->urlForKey('bg.welcome'));
            $this->assertSame($newer, $this->packages->activeCode());

            // A paused session is still open; online mode pins as well
            $context->session = $session(['package_code' => $this->code, 'package_mode' => 'daring', 'status' => 'paused']);
            $this->assertSame($this->code, $this->packages->pageCode());

            // Started with package mode off: normal URLs until it ends
            $context->session = $session(['package_code' => null, 'package_mode' => 'nonaktif']);
            $this->assertNull($this->packages->pageCode());
            $this->assertSame('', asset_package_attrs());
            $this->assertSame($this->versioned('js/game.js'), asset_url_versioned('js/game.js'));
            $this->assertSame([], package_audio_urls());

            // Nothing to pin: ended session, session from before package tracking, unknown package
            foreach ([
                ['package_code' => $this->code, 'package_mode' => 'lengkap', 'status' => 'completed'],
                ['package_code' => null, 'package_mode' => null],
                ['package_code' => 'pkt-2000-01-01-01', 'package_mode' => 'lengkap'],
            ] as $row) {
                $context->session = $session($row);
                $this->assertSame($newer, $this->packages->pageCode(), json_encode($row));
            }

            // The kill switch overrides the pin
            $context->session = $session(['package_code' => $this->code, 'package_mode' => 'lengkap']);
            config('Gelita')->paket['aktif'] = false;
            $this->assertNull($this->packages->pageCode());
        } finally {
            $context->session = null;
        }
    }

    public function testPackageAudioForMusicAndSoundEffects(): void
    {
        $this->useRelease($this->code);
        $keys = $this->packages->map($this->code)['keys'];

        $urls = package_audio_urls();
        $this->assertSame(['music/challenge', 'sfx/click'], array_keys($urls), 'narration is not included');
        $this->assertSame(base_url($keys['audio.sfx.click']), $urls['sfx/click']);
        $this->assertStringStartsWith(base_url('assets/p/'), $urls['music/challenge']);
        $this->assertSame($urls, js_config('en')['audioUrls']);
    }

    public function testLayoutRendersPackageUrlsAndBootAttributes(): void
    {
        $this->useRelease($this->code);
        $html = view('layouts/game', [], ['saveData' => false]);

        $this->assertStringContainsString('<script src="' . base_url($this->codeDir . 'js/game.js') . '"', str_replace('type="module" ', '', $html));
        $this->assertStringContainsString('package-boot.js?v=', $html, 'not in this test package, so the normal URL');
        $this->assertStringContainsString('data-package="' . $this->code . '"', $html);
        $this->assertStringContainsString('href="' . base_url($this->codeDir . 'css/base.css') . '"', $html);
    }

    public function testApiResponsesCarryTheActivePackageCode(): void
    {
        $response = $this->fresh('api/paket/aktif');
        $response->assertOK();
        $this->assertFalse($response->response()->hasHeader(PackageHeaderFilter::HEADER), 'package mode off: no header');

        $this->useRelease($this->code);
        $this->assertFalse($this->fresh('manifest.json')->response()->hasHeader('X-Gelita-Paket'), 'API responses only');
        $this->assertSame($this->code, $this->fresh('api/paket/aktif')->response()->getHeaderLine('X-Gelita-Paket'));

        // Other API paths too
        $other = $this->fresh('api/auth/username-available?username=bima5');
        $other->assertOK();
        $this->assertSame($this->code, $other->response()->getHeaderLine('X-Gelita-Paket'));

        config('Gelita')->paket['aktif'] = false;
        $this->packages->forget();
        $this->assertFalse($this->fresh('api/paket/aktif')->response()->hasHeader('X-Gelita-Paket'), 'kill switch: no header');
    }

    /** GET with a new response object (the shared one keeps headers between test requests). */
    private function fresh(string $path): CodeIgniter\Test\TestResponse
    {
        Config\Services::resetSingle('response');

        return $this->get($path);
    }
}
