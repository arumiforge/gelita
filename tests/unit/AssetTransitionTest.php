<?php

use App\Services\ContentRepository;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * Asset package Stage 4 (docs/rencana-paket-aset.md §4.2, §4.4, §4.5, P5, P8):
 * cross-document view transitions, next-screen decode, short curtain when
 * assets are local, and the language button hidden during a challenge.
 *
 * Static checks on CSS/JS plus rendered curtain markup; the browser behaviour
 * is covered by tools/qa/stage-check.mjs (Chromium).
 *
 * @internal
 */
final class AssetTransitionTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper(['url', 'gelita', 'content', 'ui']);

        // No database: curtains only look up uploaded media by key
        Services::injectMock('contentRepository', new class () extends ContentRepository {
            public function __construct()
            {
            }

            public function mediaKeyMap(): array
            {
                return [];
            }

            public function mediaMap(): array
            {
                return [];
            }
        });
    }

    protected function tearDown(): void
    {
        Services::resetSingle('contentRepository');
        parent::tearDown();
    }

    public function testGamePagesUseCrossDocumentViewTransitions(): void
    {
        $css = (string) file_get_contents(FCPATH . 'assets/css/stage.css');

        $this->assertStringContainsString('@view-transition { navigation: auto; }', $css);
        $this->assertStringContainsString('#stage { view-transition-name: gelita-stage; }', $css);
        $this->assertStringContainsString('#stage > .scene { view-transition-name: gelita-scene; }', $css);
        $this->assertStringContainsString('#stage > .hud { view-transition-name: gelita-hud; }', $css);
        // Reduced motion: no transition at all
        $this->assertMatchesRegularExpression('/@media \(prefers-reduced-motion: reduce\) \{\s*@view-transition \{ navigation: none; \}/', $css);

        // Only the game layout loads stage.css; the panel keeps plain navigation
        $this->assertStringContainsString("css/stage.css", (string) file_get_contents(APPPATH . 'Views/layouts/game.php'));
        $this->assertStringNotContainsString('stage.css', (string) file_get_contents(APPPATH . 'Views/layouts/admin.php'));
        $this->assertStringNotContainsString('view-transition', (string) file_get_contents(FCPATH . 'assets/css/admin.css'));
    }

    public function testBetweenScreenCurtainsShortenWhenAssetsAreLocal(): void
    {
        $this->assertStringContainsString('data-local-min-ms="600"', component('curtain', ['kind' => 'region']));
        $this->assertStringContainsString('data-local-min-ms="600"', component('curtain', ['kind' => 'challenge', 'engine' => 'puzzle']));
        // P8: the curtain that opens the game after the story keeps 2.5 s
        $this->assertStringNotContainsString('data-local-min-ms', component('curtain', ['kind' => 'map']));

        $curtain = (string) file_get_contents(FCPATH . 'assets/js/core/curtain.js');
        $this->assertStringContainsString("import { assetsLocal } from './config.js';", $curtain);
        $this->assertStringContainsString('opts.minMs ?? curtainMinMs(layer)', $curtain);
        $this->assertStringContainsString('const MIN_MS = 2500;', $curtain);
    }

    public function testAssetsLocalHookIsOffByDefault(): void
    {
        $config = (string) file_get_contents(FCPATH . 'assets/js/core/config.js');

        $this->assertStringContainsString('export function assetsLocal()', $config);
        $this->assertStringContainsString('export function setAssetsLocal(on)', $config);
        $this->assertStringContainsString("document.documentElement.dataset.assetsLocal === '1'", $config);
        $this->assertStringContainsString('let assetsLocalFlag = null;', $config);
        // Nothing in Stage 4 switches it on: that is Stage 2's job
        $this->assertStringNotContainsString('data-assets-local', (string) file_get_contents(APPPATH . 'Views/layouts/game.php'));
    }

    public function testLanguageButtonIsHiddenDuringAChallenge(): void
    {
        $this->assertStringContainsString('body.is-challenge .lang-switch { display: none; }', (string) file_get_contents(FCPATH . 'assets/css/game.css'));

        foreach (config('Gelita')->engineTypes as $engine) {
            $view = (string) file_get_contents(APPPATH . "Views/game/challenge/{$engine}.php");
            $this->assertStringContainsString("section('bodyClass') ?>is-challenge", $view, "{$engine} marks the page as a challenge");
        }
    }

    public function testLanguageSwitchUsesTheTwoLanguageTexts(): void
    {
        $hud = (string) file_get_contents(FCPATH . 'assets/js/game/hud.js');

        $this->assertStringContainsString('setLocale(code);', $hud);
        $this->assertStringContainsString("form.lang-switch", $hud);
        $this->assertStringContainsString('export function setLocale(code)', (string) file_get_contents(FCPATH . 'assets/js/core/config.js'));
    }

    public function testUsernameCheckStandsDownOnceTheFormIsSubmitting(): void
    {
        // A late check carries the pre-registration session cookie and would log the new student out
        $register = (string) file_get_contents(FCPATH . 'assets/js/game/register.js');

        $this->assertMatchesRegularExpression('/const check = debounce\(async \(\) => \{.*?if \(form\.dataset\.submitting\) return;\s*const value = input\.value;/s', $register);
    }

    /** @return array<string, array{string}> */
    public static function screensWithNextAssets(): array
    {
        return [
            'intro'           => ['game/intro.php'],
            'peta kedu'       => ['game/map-kedu.php'],
            'peta wilayah'    => ['game/map-level.php'],
            'misi'            => ['game/mission-brief.php'],
            'hasil'           => ['game/challenge-result.php'],
            'selesai'         => ['game/challenge-finished.php'],
            'dialog / tuntas' => ['partials/dialogue-scene.php'],
        ];
    }

    /**
     * @dataProvider screensWithNextAssets
     */
    public function testScreensNameTheNextScreensImages(string $view): void
    {
        $this->assertStringContainsString('next_assets_attr(', (string) file_get_contents(APPPATH . 'Views/' . $view));
    }

    public function testNextAssetsAreDecodedOneScreenAhead(): void
    {
        $js = (string) file_get_contents(FCPATH . 'assets/js/game/next-assets.js');

        $this->assertStringContainsString('const MAX_IMAGES = 6;', $js);
        $this->assertStringContainsString('img.decode()', $js);
        $this->assertStringContainsString('requestIdleCallback', $js);
        $this->assertStringContainsString("window.addEventListener('pagehide'", $js);
        $this->assertStringContainsString('initNextAssets({ ready: page });', (string) file_get_contents(FCPATH . 'assets/js/game.js'));
    }
}
