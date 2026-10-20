<?php

use App\Libraries\PasswordPolicy;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\ResetTables;

/**
 * Touch-friendly play (display plan Phase F).
 *
 * - F1: no tap highlight, touch-action on controls, no double-tap zoom on
 *   pieces (drag owns the gesture), hovers that transform only under
 *   `(hover: hover)`, explicit reduced-motion rules.
 * - F2: small controls get a 48 dp hit area (`--tap`) through ::before.
 * - F3: finger drag (core/drag.js) replaces HTML5 drag-and-drop on the
 *   puzzle, the Cari Objek zoom button and `hunt_zoom` overlay event, swipe
 *   on slides, the on-screen keyboard shift, and the iOS focus-zoom flag.
 *
 * @internal
 */
final class TouchPlayTest extends CIUnitTestCase
{
    use ResetTables;

    private const GAME_CSS = ['base', 'stage', 'components', 'game', 'cinematic'];

    private function css(string $name): string
    {
        return (string) preg_replace('~/\*.*?\*/~s', '', (string) file_get_contents(FCPATH . 'assets/css/' . $name . '.css'));
    }

    private function js(string $path): string
    {
        return (string) file_get_contents(FCPATH . 'assets/js/' . $path);
    }

    /**
     * CSS with every `@media (hover: hover)` and reduced-motion block removed
     * (reduced-motion blocks may switch hover transforms off).
     */
    private function withoutHoverBlocks(string $css): string
    {
        while (preg_match('~@media \((?:hover: hover|prefers-reduced-motion: reduce)\)~', $css, $match, PREG_OFFSET_CAPTURE) === 1) {
            $start = $match[0][1];
            $open  = strpos($css, '{', $start);
            $depth = 0;

            for ($i = $open, $n = strlen($css); $i < $n; $i++) {
                $depth += $css[$i] === '{' ? 1 : ($css[$i] === '}' ? -1 : 0);
                if ($depth === 0) {
                    break;
                }
            }
            $css = substr($css, 0, $start) . substr($css, $i + 1);
        }

        return $css;
    }

    public function testTouchBasics(): void
    {
        $all = implode("\n", array_map($this->css(...), self::GAME_CSS));

        $this->assertMatchesRegularExpression('~body\.game\s*\{[^}]*-webkit-tap-highlight-color:\s*transparent~', $all);
        $this->assertStringContainsString('touch-action: manipulation', $all);
        $this->assertMatchesRegularExpression('~\.puzzle-board \.piece\s*\{[^}]*touch-action:\s*none~', $all);
        $this->assertStringContainsString('-webkit-touch-callout: none', $all);
        $this->assertMatchesRegularExpression('~\.btn:active\s*\{[^}]*scale\(\.96\)~', $all);
    }

    public function testTransformingHoversOnlyApplyWithARealPointer(): void
    {
        $outside = implode("\n", array_map(fn (string $name): string => $this->withoutHoverBlocks($this->css($name)), self::GAME_CSS));

        foreach (['.object:hover', '.piece:hover', '.map-pin:hover', '.card-link:hover', '.rating-star:hover', '.icon-btn:hover', '.btn-primary:hover', '.btn-start:hover', '.kenal-btn:hover', '.card-image:hover'] as $selector) {
            $this->assertStringNotContainsString($selector, $outside, "{$selector} outside @media (hover: hover)");
        }
    }

    public function testReducedMotionStopsLoopingAndEntranceAnimations(): void
    {
        preg_match_all('~@media \(prefers-reduced-motion: reduce\)\s*\{(.*?)\n\}~s', implode("\n", array_map($this->css(...), self::GAME_CSS)), $blocks);
        $reduced = implode("\n", $blocks[1]);

        $this->assertMatchesRegularExpression('~\.btn-start[^{]*\{[^}]*animation:\s*none~', $reduced);
        $this->assertMatchesRegularExpression('~\.kenal\.is-open[^{]*\{[^}]*animation:\s*none~', $reduced);
        $this->assertMatchesRegularExpression('~\.cine-stage \.character-img[^{]*\{[^}]*animation:\s*none~', $reduced);
    }

    public function testSmallControlsGetA48DpHitArea(): void
    {
        $components = $this->css('components');
        $game       = $this->css('game');
        $cinematic  = $this->css('cinematic');

        $this->assertMatchesRegularExpression('~\.btn::before, \.icon-btn::before\s*\{[^}]*width:\s*max\(100%, var\(--tap\)\);[^}]*height:\s*max\(100%, var\(--tap\)\);~', $components);
        $this->assertMatchesRegularExpression('~\.lang-opt::before\s*\{[^}]*var\(--tap\)~', $components);

        foreach (['.slide-dot', '.chip-btn', '.word', '.blank'] as $selector) {
            $this->assertMatchesRegularExpression('~' . preg_quote($selector, '~') . '[^{,]*::before[^{]*\{[^}]*var\(--tap\)~', $game, "{$selector} hit area");
        }
        $this->assertMatchesRegularExpression('~\.narrator-auto[^{,]*::before[^{]*\{[^}]*var\(--tap\)~', $cinematic);
    }

    public function testPuzzleUsesPointerDragInsteadOfHtml5DragAndDrop(): void
    {
        $drag   = $this->js('core/drag.js');
        $puzzle = $this->js('engines/puzzle.js');

        $this->assertStringContainsString('export function initDrag(', $drag);
        $this->assertStringContainsString('export function onSwipe(', $drag);
        $this->assertStringContainsString('setPointerCapture', $drag);
        $this->assertStringContainsString("from '../core/drag.js'", $puzzle);
        $this->assertStringNotContainsString("addEventListener('dragstart'", $puzzle);
        $this->assertStringNotContainsString('draggable = true', $puzzle);
    }

    public function testHuntZoomButtonAndEvent(): void
    {
        helper(['url', 'gelita', 'ui']);
        $view = (string) file_get_contents(APPPATH . 'Views/game/challenge/cari.php');
        $cari = $this->js('engines/cari.js');

        $this->assertStringContainsString('id="hunt-zoom"', $view);
        $this->assertStringContainsString('aria-controls="hunt-scene"', $view);
        $this->assertStringContainsString("'hunt_zoom'", $cari);
        $this->assertSame('Perbesar adegan', lang('Game.huntZoom', [], 'id'));
        $this->assertSame('Enlarge the scene', lang('Game.huntZoom', [], 'en'));
        $this->assertNotSame('Game.huntZoomClose', lang('Game.huntZoomClose', [], 'en'));
    }

    public function testSlidesSwipeAndKeyboardShiftAreWired(): void
    {
        $this->assertStringContainsString('onSwipe', $this->js('game/slides.js'));
        $this->assertStringContainsString('swipe', $this->js('game/narrator.js'));
        $this->assertStringContainsString('swipe', $this->js('game/library.js'));

        $game = $this->js('game.js');
        $this->assertStringContainsString("addEventListener('touchstart', () => {}, { passive: true })", $game);
        $this->assertStringContainsString('maximum-scale=1', $game);
        $this->assertStringContainsString("import('./core/keyboard.js')", $game);

        $keyboard = $this->js('core/keyboard.js');
        $this->assertStringContainsString('visualViewport', $keyboard);
        $this->assertStringContainsString('export function initKeyboardShift()', $keyboard);
    }

    public function testFormFieldsCarryKeyboardHints(): void
    {
        $login = (string) file_get_contents(APPPATH . 'Views/game/login.php');
        $step2 = (string) file_get_contents(APPPATH . 'Views/game/register/step-2.php');
        $step3 = (string) file_get_contents(APPPATH . 'Views/game/register/step-3.php');

        $this->assertMatchesRegularExpression('~id="username"[^>]*autocorrect="off"[^>]*enterkeyhint="next"~s', $login);
        $this->assertMatchesRegularExpression('~id="password"[^>]*enterkeyhint="done"~s', $login);
        $this->assertMatchesRegularExpression('~id="school_npsn"[^>]*autocorrect="off"~s', $step2);
        $this->assertMatchesRegularExpression('~id="school_name"[^>]*autocorrect="off"~s', $step2);
        $this->assertMatchesRegularExpression('~id="username"[^>]*autocorrect="off"~s', $step3);
    }

    /**
     * The new-password component (components/password-field.php) on both of
     * its pages: the password field moves on to the confirm field, and the
     * confirm field is the last text field of the form.
     */
    public function testPasswordFieldsCarryKeyboardHints(): void
    {
        // The game layout reads media keys, so the pages need the schema.
        $this->createResetTables();
        service('contentRepository')->flush();

        $policy = (new PasswordPolicy())->toClient();

        try {
            $pages = [
                '/daftar/3' => view('game/register/step-3', [
                    'locale' => 'id', 'step' => 3, 'done' => [1, 2], 'values' => [], 'errors' => [], 'passwordPolicy' => $policy,
                ]),
                '/ganti-sandi' => view('game/change-password', [
                    'locale' => 'id', 'mustChange' => false, 'username' => 'sekar_ayu', 'errors' => [], 'passwordPolicy' => $policy,
                ]),
            ];
        } finally {
            $this->dropResetTables();
        }

        foreach ($pages as $page => $html) {
            $this->assertMatchesRegularExpression('~<input id="password" [^>]*enterkeyhint="next"~s', $html, $page);
            $this->assertMatchesRegularExpression('~<input id="password_confirm" [^>]*enterkeyhint="done"~s', $html, $page);
        }

        $this->assertStringContainsString('class="password-field is-split"', $pages['/daftar/3'], 'sign-up: split for its columns');
        $this->assertStringContainsString('class="password-field"', $pages['/ganti-sandi'], 'ganti sandi: one column as before');
        $this->assertStringNotContainsString('pw-main', $pages['/ganti-sandi']);
    }
}
