<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Owner, 22 October 2026: the framed dialog (two frame pictures, chosen by
 * content), round icons from the owner's sheets in the game's dialogs, the
 * gold pills on the main buttons, the game dropdown, and the square close
 * button of the HUD menu.
 *
 * @internal
 */
final class DialogFrameTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper(['gelita', 'content', 'ui']);
    }

    private function css(string $name): string
    {
        return (string) preg_replace('~/\*.*?\*/~s', '', (string) file_get_contents(FCPATH . 'assets/css/' . $name . '.css'));
    }

    public function testShippedPicturesArePassedOnTheStage(): void
    {
        // Document 40, U-01 to U-05 (19 November 2026): one plate per colour, the track, the frame
        foreach (['ui-plate-gold', 'ui-plate-navy', 'ui-plate-green', 'ui-track', 'ui-frame', 'select-arrow'] as $file) {
            $this->assertFileExists(FCPATH . 'assets/ui/' . $file . '.webp');
        }
        foreach (['gold-pill-1', 'gold-pill-2', 'gold-pill-3', 'gold-pill-4', 'kenal-plate-gold', 'kenal-plate-navy', 'plate-green', 'loading-track', 'map-progress-track', 'modal-frame-4x3', 'modal-frame-16x9'] as $old) {
            $this->assertFileDoesNotExist(FCPATH . 'assets/ui/' . $old . '.webp', $old . ' replaced by U-01 to U-05');
        }

        $urls = stage_picture_urls();
        $this->assertSame(
            ['plate-gold', 'plate-navy', 'plate-green', 'ui-track', 'ui-frame', 'select-arrow', 'round-close', 'round-blank'],
            array_keys($urls),
        );
        $this->assertNotContains(null, $urls);
        $this->assertStringContainsString('stage_picture_urls()', (string) file_get_contents(APPPATH . 'Views/layouts/game.php'));
        $this->assertNull(ui_picture_src('does-not-exist.webp'));
    }

    public function testGameDialogsUseRoundIconsAndThePanelKeepsLineIcons(): void
    {
        $game = view('components/modal', ['roundArt' => true]);
        $this->assertStringContainsString('class="modal-icon is-round"', $game);
        $this->assertStringContainsString('<template data-modal-icon="correct"><img class="modal-icon-art" src="' . esc(round_icon_src('right'), 'attr'), $game);
        $this->assertStringContainsString('<template data-modal-icon="wrong"><img class="modal-icon-art" src="' . esc(round_icon_src('wrong'), 'attr'), $game);
        // No round picture of its own: the line icon on the plain dark face
        $this->assertMatchesRegularExpression('~<template data-modal-icon="install"><span class="modal-icon-stack"><img class="modal-icon-art" src="[^"]*round-neutral\.webp[^"]*"[^>]*><svg~', $game);

        $panel = view('components/modal', ['roundArt' => false]);
        $this->assertStringNotContainsString('modal-icon-art', $panel);
        $this->assertStringContainsString('<template data-modal-icon="key"><svg', $panel);
        $this->assertStringContainsString("<template id=\"tpl-modal\"><?= view('components/modal', ['roundArt' => true]) ?></template>", (string) file_get_contents(APPPATH . 'Views/layouts/game.php'));
    }

    public function testDialogIsTheNineSliceFrameAtTwoRatios(): void
    {
        $stage = $this->css('stage');
        $this->assertMatchesRegularExpression('~\.stage\[style\*="--ui-frame"\] \.modal-layer > \.modal \{[^}]*aspect-ratio: 1428 / 822;[^}]*border-image: var\(--ui-frame\) var\(--frame-cut\) / 4rem stretch;~', $stage);
        $this->assertStringContainsString('--frame-cut: 180 fill;', $stage);
        $this->assertMatchesRegularExpression('~\.modal\[data-frame="wide"\] \{[^}]*aspect-ratio: 1672 / 698;~', $stage);
        // The card loses its own box: no background, border, or shadow on the frame
        $this->assertMatchesRegularExpression('~\.modal-layer > \.modal > \.modal-card \{[^}]*border: 0;[^}]*background: none;[^}]*box-shadow: none;~', $stage);

        $js = (string) file_get_contents(FCPATH . 'assets/js/core/modal.js');
        $this->assertStringContainsString("const FRAME_STEPS = [['compact', 30], ['compact', 35], ['wide', 45]", $js);
        $this->assertStringContainsString('fitFrame(modal);', $js);

        $this->assertStringContainsString("getPropertyValue('--ui-frame')", $js);
        $this->assertStringContainsString('.stage[style*="--ui-frame"] .lightbox {', $this->css('game'));
    }

    /** U-01 (19 November 2026): every main button on the one gold plate, its ends never squashed. */
    public function testMainButtonsStandOnTheGoldPlate(): void
    {
        $game = $this->css('game');
        $this->assertStringContainsString('.stage[style*="--plate-gold"] .kedu-btn.is-primary::before {', $game);
        $this->assertStringContainsString('.stage[style*="--plate-gold"] .result-btn.is-primary::before {', $game);
        $this->assertMatchesRegularExpression('~\.stage\[style\*="--plate-gold"\] :is\(\.btn-primary, \.btn-danger\) \{[^}]*--pill-end: calc\(var\(--pill-h\) \* 1\.165\);[^}]*border-image: var\(--plate-gold\) var\(--plate-cut\) / 0 var\(--pill-end\) stretch;~', $game);
        $this->assertStringContainsString('.stage[style*="--plate-gold"] .cine .narrator-next.has-label:not(:disabled) {', $this->css('cinematic'));
        $this->assertStringContainsString('--plate-cut: 23 184 19 fill;', $this->css('stage'));
        // No picture of the family is stretched to its box any more
        foreach (['game', 'cinematic', 'stage'] as $name) {
            $this->assertDoesNotMatchRegularExpression('~var\(--(plate-(gold|navy|green)|ui-track|ui-frame)\)\s+center\s*/\s*100% 100%~', $this->css($name), $name);
        }

        foreach (['game', 'cinematic', 'stage', 'components'] as $name) {
            $this->assertStringNotContainsString('--skin-primary', $this->css($name), $name);
        }
        foreach (['map-kedu', 'challenge-finished'] as $view) {
            $this->assertStringNotContainsString("'skin-primary'", (string) file_get_contents(APPPATH . 'Views/game/' . $view . '.php'), $view);
        }
    }

    public function testRegionSelectsBecomeTheGameDropdown(): void
    {
        $step = (string) file_get_contents(APPPATH . 'Views/game/register/step-2.php');
        $this->assertSame(3, substr_count($step, 'data-fancy-select>'));

        $register = (string) file_get_contents(FCPATH . 'assets/js/game/register.js');
        $this->assertStringContainsString("import { enhanceSelects } from '../core/select.js';", $register);
        $this->assertStringContainsString('enhanceSelects(form);', $register);

        $select = (string) file_get_contents(FCPATH . 'assets/js/core/select.js');
        $this->assertStringContainsString("role: 'combobox'", $select);
        $this->assertStringContainsString("select.dispatchEvent(new Event('change', { bubbles: true }));", $select);
        $this->assertStringContainsString('.fancy-select-arrow {', $this->css('game'));
        $this->assertStringContainsString('background: var(--select-arrow) center / contain no-repeat;', $this->css('game'));
    }

    public function testHudMenuCloseButtonStaysSquare(): void
    {
        $this->assertMatchesRegularExpression(
            '~\.hud-sheet-close > img\.hud-sheet-close-art \{[^}]*max-width: none;[^}]*aspect-ratio: 1;~',
            $this->css('stage'),
        );
        $this->assertMatchesRegularExpression('~\n\.hud-sheet-close \{[^}]*flex: none;[^}]*padding: 0;~', $this->css('stage'));
    }
}
