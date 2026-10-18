<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Panggung 16:9 tetap (Dok 3 §1–§3, §12.1).
 *
 * CSS game diukur dalam rem (1rem = 16 dp, ikut lebar panggung), jadi satuan
 * viewport, media query lebar/tinggi, dan px selain garis 1px akan membuat
 * tata letak berbeda per layar. Satu-satunya pengecualian: cadangan
 * --app-w/--app-h di tokens.css (di luar panggung).
 *
 * @internal
 */
final class StageCssTest extends CIUnitTestCase
{
    private const GAME_CSS = ['tokens', 'base', 'stage', 'components', 'game', 'cinematic'];

    /** Isi berkas tanpa komentar CSS (komentar boleh menyebut vw/px). */
    private function css(string $name): string
    {
        $path = FCPATH . 'assets/css/' . $name . '.css';
        $this->assertFileExists($path);

        return (string) preg_replace('~/\*.*?\*/~s', '', (string) file_get_contents($path));
    }

    public function testLayoutCssIsSplitIntoStageAndAdmin(): void
    {
        $this->assertFileDoesNotExist(FCPATH . 'assets/css/layout.css');
        $this->assertStringContainsString('.admin-sidebar', $this->css('admin'));
        $this->assertStringContainsString('.stage-frame', $this->css('stage'));
    }

    public function testGameCssHasNoViewportUnitsExceptAppFallback(): void
    {
        foreach (self::GAME_CSS as $name) {
            $css = $this->css($name);

            if ($name === 'tokens') {
                // Cadangan tanpa JavaScript (§2.1): hanya di sini dan hanya untuk --app-*
                $css = (string) preg_replace('~--app-[wh]:\s*100d?v[wh];~', '', $css);
                $css = str_replace(['@supports (height: 100dvh)', 'calc(var(--app-w) / 24)'], '', $css);
            }

            $this->assertDoesNotMatchRegularExpression('~\d(?:d|s|l)?v(?:w|h|min|max)\b~', $css, "{$name}.css memakai satuan viewport");
        }
    }

    public function testGameCssHasNoWidthOrHeightMediaQueries(): void
    {
        foreach (self::GAME_CSS as $name) {
            $this->assertDoesNotMatchRegularExpression(
                '~@media[^{]*\((?:max|min)-(?:width|height)~',
                $this->css($name),
                "{$name}.css memakai media query lebar/tinggi",
            );
        }
    }

    public function testGameCssUsesPxOnlyForHairlines(): void
    {
        foreach (self::GAME_CSS as $name) {
            preg_match_all('~(?<![\w.#-])-?(\d*\.?\d+)px\b~', $this->css($name), $matches);
            $bad = array_values(array_filter($matches[1], static fn (string $v): bool => ! in_array((float) $v, [0.0, 1.0], true)));

            $this->assertSame([], $bad, "{$name}.css memakai px selain garis 1px");
        }
    }

    public function testStageIsEightyByFortyFiveRem(): void
    {
        $stage = $this->css('stage');

        $this->assertMatchesRegularExpression('~\.stage\s*\{[^}]*width:\s*80rem;[^}]*height:\s*45rem;~s', $stage);
        $this->assertMatchesRegularExpression('~:root\.game-stage\s*\{[^}]*font-size:\s*calc\(var\(--stage-w\)\s*/\s*80\)~s', $this->css('tokens'));
        // Hanya media query yang diizinkan §2.2
        preg_match_all('~@media\s*([^{]+)\{~', implode("\n", array_map($this->css(...), self::GAME_CSS)), $queries);
        foreach ($queries[1] as $query) {
            $this->assertMatchesRegularExpression('~orientation: portrait\) and \(pointer: coarse|prefers-reduced-motion|hover: hover~', $query);
        }
    }

    public function testGameLayoutPutsEverythingButTheRotateGateInsideTheStage(): void
    {
        $layout = (string) file_get_contents(APPPATH . 'Views/layouts/game.php');

        $this->assertStringContainsString('<html class="game-stage"', $layout);
        $this->assertStringContainsString("css/stage.css", $layout);
        $this->assertStringNotContainsString('css/layout.css', $layout);

        $gate  = strpos($layout, "include('components/rotate-gate')");
        $stage = strpos($layout, 'id="stage"');
        $close = strpos($layout, '</div>', (int) strpos($layout, "component('toast'"));
        $this->assertNotFalse($gate);
        $this->assertNotFalse($stage);
        $this->assertLessThan($stage, $gate, 'layar putar di luar panggung');

        foreach (['id="modal-layer"', "component('toast'", "'components/hud'", "'components/hud-menu'", '<main id="app"', "renderSection('overlay')", "renderSection('nav')"] as $needle) {
            $at = strpos($layout, $needle);
            $this->assertNotFalse($at, $needle);
            $this->assertGreaterThan($stage, $at, "{$needle} harus di dalam #stage");
            $this->assertLessThan($close, $at, "{$needle} harus di dalam #stage");
        }
    }

    public function testAdminLayoutsNoLongerLoadLayoutCss(): void
    {
        foreach (['admin', 'auth'] as $layout) {
            $html = (string) file_get_contents(APPPATH . "Views/layouts/{$layout}.php");
            $this->assertStringNotContainsString('css/layout.css', $html);
            $this->assertStringContainsString('css/admin.css', $html);
        }
    }
}
