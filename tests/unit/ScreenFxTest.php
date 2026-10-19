<?php

use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * WebGL screen effects (owner, 19 October 2026): the narrated scenes name the
 * vendored three.js through `data-fx3d` (screen_fx_attr()), the switch
 * Config\Gelita::$screenFx removes it, the vendored files are the checked
 * release, and the CSS effects stay in place as the fallback.
 *
 * @internal
 */
final class ScreenFxTest extends CIUnitTestCase
{
    private const THREE_DIR = 'assets/vendor/three-0.186.1/';

    protected function setUp(): void
    {
        parent::setUp();
        helper(['url', 'gelita', 'content', 'ui']);
    }

    protected function tearDown(): void
    {
        Services::resetSingle('renderer');
        parent::tearDown();
    }

    public function testAttributeNamesTheVersionedThreeEntry(): void
    {
        $config = config('Gelita');
        $this->assertTrue($config->screenFx, 'on by default');
        $this->assertSame('vendor/three-0.186.1/three.module.js', $config->screenFxThree);

        // esc(…, 'attr') writes the URL with entities; the browser reads it decoded
        $this->assertMatchesRegularExpression('#^ data-fx3d="[^"]*/assets/vendor/three-0\.186\.1/three\.module\.js\?v=[^"]+"$#', html_entity_decode(screen_fx_attr()));
    }

    public function testSwitchOffLeavesTheCssEffectsOnly(): void
    {
        $config           = config('Gelita');
        $config->screenFx = false;

        try {
            $this->assertSame('', screen_fx_attr());
        } finally {
            $config->screenFx = true;
        }
    }

    public function testEveryFullScreenNarratedSceneCarriesTheAttribute(): void
    {
        foreach (['game/intro.php', 'game/ending.php', 'partials/dialogue-scene.php', 'partials/region-intro.php'] as $view) {
            $this->assertStringContainsString('<?= screen_fx_attr() ?>', (string) file_get_contents(APPPATH . 'Views/' . $view), $view);
        }

        // The Peta Kedu story bar is not a full-screen scene
        $this->assertStringNotContainsString('screen_fx_attr', (string) file_get_contents(APPPATH . 'Views/game/map-kedu.php'));
    }

    /** vendor/README.md lists the sha256 of each file; the files are the official release, unchanged. */
    public function testVendoredFilesMatchTheReadme(): void
    {
        $readme = (string) file_get_contents(FCPATH . 'assets/vendor/README.md');

        foreach (['three.module.js', 'three.core.js'] as $file) {
            $this->assertFileExists(FCPATH . self::THREE_DIR . $file);
            $this->assertMatchesRegularExpression(
                '/`three-0\.186\.1\/' . preg_quote($file, '/') . '` \| three\.js \| 0\.186\.1 \| `' . hash_file('sha256', FCPATH . self::THREE_DIR . $file) . '`/',
                $readme,
                $file,
            );
        }

        $this->assertFileExists(FCPATH . self::THREE_DIR . 'LICENSE-three.txt');

        // The entry only imports its neighbour, so the folder works as is, also in an asset package
        preg_match_all("/from '([^']+)'/", (string) file_get_contents(FCPATH . self::THREE_DIR . 'three.module.js'), $imports);
        $this->assertSame(['./three.core.js'], array_values(array_unique($imports[1])));
    }

    public function testAssetPackagesCarryTheVendorFolder(): void
    {
        $sources = (new ReflectionClassConstant(App\Services\AssetPackageBuilder::class, 'CODE_SOURCES'))->getValue();

        $this->assertArrayHasKey('assets/vendor', $sources);
        $this->assertContains('js', $sources['assets/vendor']['ext']);
    }

    public function testCssEffectsStayTheFallback(): void
    {
        $css = (string) file_get_contents(FCPATH . 'assets/css/cinematic.css');

        // The CSS effects are still defined; only while WebGL runs do fog, fog-lift, and glow step aside
        foreach (['fog', 'fog-lift', 'glow', 'flash', 'dim'] as $effect) {
            $this->assertStringContainsString('.narrator-fx.fx-' . $effect . ' {', $css, $effect);
        }
        $this->assertStringContainsString('.narrator.has-fx3d > .narrator-fx:is(.fx-fog, .fx-fog-lift, .fx-glow) { background: none; }', $css);

        // Parallax moves layers through the translate property, so it composes with Ken Burns and the speaking zoom
        $this->assertStringContainsString('.has-parallax :is(.scene-img, .kenal-bg, .cine-bg) {', $css);
        $this->assertStringContainsString('.has-parallax :is(.dialogue-stage, .cine-stage, .kenal-stage) {', $css);
    }

    public function testNarratorLoadsTheEffectsOnlyWithoutReducedMotion(): void
    {
        $narrator = (string) file_get_contents(FCPATH . 'assets/js/game/narrator.js');
        $fx       = (string) file_get_contents(FCPATH . 'assets/js/game/screen-fx.js');

        $this->assertStringContainsString("const visible = root.getClientRects().length > 0 && !reducedMotion();", $narrator);
        $this->assertStringContainsString("import('./screen-fx.js')", $narrator, 'loaded on demand, not with every page');
        // No WebGL 2: neither three.js nor its console errors
        $this->assertMatchesRegularExpression("/getContext\\('webgl2'\\);\\s*if \\(!probe\\) return null;[\\s\\S]*await import\\(threeUrl\\)/", $fx);
        $this->assertStringContainsString("canvas.addEventListener('webglcontextlost'", $fx);
    }
}
