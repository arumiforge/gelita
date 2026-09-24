<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Layar putar: perangkat sentuh yang dipegang tegak tidak dilayani.
 *
 * Penghalang ditampilkan CSS (layout.css) dan aksesibilitasnya dijaga
 * JavaScript (game/rotate-gate.js) dengan media query yang ditulis dua kali;
 * bila keduanya berbeda, isi di belakang penghalang dapat terkunci `inert`
 * tanpa penghalang terlihat, atau sebaliknya.
 *
 * @internal
 */
final class RotateGateTest extends CIUnitTestCase
{
    private const QUERY = '(orientation: portrait) and (pointer: coarse)';

    public function testCssAndJavascriptUseTheSameMediaQuery(): void
    {
        $css = (string) file_get_contents(FCPATH . 'assets/css/layout.css');
        $js  = (string) file_get_contents(FCPATH . 'assets/js/game/rotate-gate.js');

        $this->assertStringContainsString('@media ' . self::QUERY . ' {', $css);
        $this->assertStringContainsString("const QUERY = '" . self::QUERY . "';", $js);
    }

    public function testGameLayoutAlwaysCarriesTheGate(): void
    {
        $layout = (string) file_get_contents(APPPATH . 'Views/layouts/game.php');

        $this->assertStringContainsString("include('components/rotate-gate')", $layout);
    }

    public function testGateRendersBothLanguagesWithoutSkipButton(): void
    {
        foreach (['id', 'en'] as $locale) {
            service('language')->setLocale($locale);
            $html = view('components/rotate-gate', [], ['debug' => false]);

            $this->assertStringContainsString('role="alertdialog"', $html);
            $this->assertStringContainsString(esc(lang('Game.rotateTitle', [], $locale)), $html);
            $this->assertStringContainsString(esc(lang('Game.rotateStuck', [], $locale)), $html);
            $this->assertStringNotContainsString('<button', $html, 'penghalang tidak boleh dapat dilewati');
            $this->assertStringNotContainsString('<a ', $html);
        }

        service('language')->setLocale(config('App')->defaultLocale);
    }
}
