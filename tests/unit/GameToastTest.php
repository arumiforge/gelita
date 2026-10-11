<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Game-style toasts (display plan Phase E).
 *
 * - The game layer renders flash toasts with a round icon badge and gives
 *   JavaScript toasts the same badges through <template data-toast-icon>.
 * - A game `error` flash is a "not open yet" lock message, shown as `warn`.
 * - The admin layer keeps its look and never shows the `error` flash.
 * - core/toast.js keeps the toast(message, type, requestId) API, queues one
 *   toast at a time, and uses role=alert for `bad`.
 * - The layer stays at x 856, y 72, w 400 dp, above the modal veil.
 *
 * @internal
 */
final class GameToastTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper(['url', 'gelita', 'ui']);
    }

    protected function tearDown(): void
    {
        session()->remove(['message', 'error']);
        parent::tearDown();
    }

    public function testGameFlashToastsHaveBadgesAndErrorIsWarn(): void
    {
        session()->setFlashdata('message', 'Kata sandi baru tersimpan.');
        session()->setFlashdata('error', 'Wilayah ini belum terbuka.');

        $html = component('toast', ['withErrors' => true]);

        $this->assertMatchesRegularExpression('~class="toast is-ok is-flash" data-toast-type="ok"><span class="toast-badge"~', $html);
        $this->assertMatchesRegularExpression('~class="toast is-warn is-flash" data-toast-type="warn"><span class="toast-badge"~', $html);
        $this->assertStringNotContainsString('is-bad', $html);
        $this->assertStringContainsString('Wilayah ini belum terbuka.', $html);

        foreach (['ok', 'warn', 'bad', 'info'] as $type) {
            $this->assertStringContainsString('<template data-toast-icon="' . $type . '"><span class="toast-badge"', $html);
        }
    }

    public function testAdminLayerKeepsItsLookWithoutErrors(): void
    {
        session()->setFlashdata('message', 'Data tersimpan.');
        session()->setFlashdata('error', 'Ada yang gagal.');

        $html = component('toast', ['withErrors' => false]);

        $this->assertStringContainsString('class="toast is-ok is-flash"><svg class="icon"', $html);
        $this->assertStringNotContainsString('Ada yang gagal.', $html);
        $this->assertStringNotContainsString('data-toast-icon', $html);
        $this->assertStringNotContainsString('toast-badge', $html);
    }

    public function testToastScriptKeepsApiAndGameBehaviour(): void
    {
        $js = (string) file_get_contents(FCPATH . 'assets/js/core/toast.js');

        $this->assertStringContainsString("export function toast(message, type = 'info', requestId = null)", $js);
        $this->assertStringContainsString("type === 'bad'", $js);
        $this->assertStringContainsString("setAttribute('role', 'alert')", $js);
        $this->assertStringContainsString('const QUEUE_MAX = 3;', $js);
        $this->assertStringContainsString('const MIN_TTL = 1800;', $js);
        $this->assertStringContainsString('const MAX_TTL = 5000;', $js);
        $this->assertStringContainsString("t('toastClose')", $js);
        $this->assertStringContainsString("'Escape'", $js);
    }

    public function testCloseTextExistsInBothLocales(): void
    {
        $this->assertSame('Ketuk untuk menutup', lang('Js.toastClose', [], 'id'));
        $this->assertSame('Tap to close', lang('Js.toastClose', [], 'en'));
    }

    public function testLayerStaysAboveModalVeilAtItsStagePlace(): void
    {
        $stage = (string) preg_replace('~/\*.*?\*/~s', '', (string) file_get_contents(FCPATH . 'assets/css/stage.css'));

        $this->assertMatchesRegularExpression('~\.toast-layer\s*\{[^}]*top:\s*4\.5rem;[^}]*left:\s*53\.5rem;[^}]*z-index:\s*60;[^}]*width:\s*25rem;[^}]*pointer-events:\s*none;~s', $stage);
        $this->assertMatchesRegularExpression('~\.modal-layer\s*\{[^}]*z-index:\s*50;~s', $stage);
    }

    public function testFlashFadesWithoutJavaScriptAndLeavingFadesUpward(): void
    {
        $css = (string) file_get_contents(FCPATH . 'assets/css/components.css');

        $this->assertMatchesRegularExpression('~\.toast\.is-flash\s*\{[^}]*toast-out \.4s ease-in 3\.5s forwards~', $css);
        $this->assertMatchesRegularExpression('~@keyframes toast-pop\s*\{[^@]*scale\(\.6\)[^@]*scale\(1\.06\)~s', $css);
        $this->assertMatchesRegularExpression('~@keyframes toast-leave\s*\{[^}]*translateY\(-~', $css);
    }
}
