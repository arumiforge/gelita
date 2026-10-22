<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Layar tantangan dan layar selesai lebar penuh tanpa tokoh Jaka
 * (Dok 3 §5, §12.1). Avatar Mbah Kedu di bilah petunjuk Cari Objek tetap:
 * ia yang membacakan petunjuk.
 *
 * @internal
 */
final class ChallengeFrameTest extends CIUnitTestCase
{
    private function view(string $name): string
    {
        return (string) file_get_contents(APPPATH . 'Views/' . $name . '.php');
    }

    public function testFrameAndFinishedScreenDoNotRenderJaka(): void
    {
        foreach (['game/challenge/_frame', 'game/challenge-finished', 'game/challenge/puzzle', 'game/challenge/rumpang', 'game/challenge/boleh', 'game/challenge/cari'] as $name) {
            $source = $this->view($name);

            $this->assertDoesNotMatchRegularExpression("~'character'\\s*=>\\s*'jaka'~", $source, "{$name} merender Jaka");
            $this->assertStringNotContainsString('challenge-character', $source);
            $this->assertStringNotContainsString('finished-jaka', $source);
            $this->assertStringNotContainsString('finished-layout', $source);
        }
    }

    public function testHuntKeepsMbahKeduAvatar(): void
    {
        // His round avatar, not a pose frame (owner, 21 October 2026)
        $cari = $this->view('game/challenge/cari');
        $this->assertStringContainsString("media_key_src('ui.kedu-avatar')", $cari);
        $this->assertStringNotContainsString("component('character'", $cari);
    }

    public function testFrameUsesStageCoordinates(): void
    {
        $frame = $this->view('game/challenge/_frame');
        $this->assertStringContainsString('challenge-head stage-box', $frame);
        $this->assertStringContainsString('challenge-aside', $frame);

        $stage = (string) file_get_contents(FCPATH . 'assets/css/stage.css');
        $this->assertMatchesRegularExpression('~\.challenge-head\s*\{\s*--x:\s*32;\s*--y:\s*64;\s*--w:\s*1216;\s*--h:\s*56;~', $stage);
        foreach (['puzzle-board', 'rumpang-reading', 'boleh-passages', 'hunt-scene'] as $panel) {
            $this->assertStringContainsString('.' . $panel, $stage);
        }
    }

    /** Owner, 21 October 2026: no shard plate on the challenge screens; the region name in its place, the logo left. */
    public function testChallengeHudShowsTheRegionInTheMiddle(): void
    {
        foreach (['puzzle', 'rumpang', 'boleh', 'cari'] as $engine) {
            $this->assertStringContainsString("<?= \$this->section('hudLayout') ?>region-centre<?= \$this->endSection() ?>", $this->view('game/challenge/' . $engine), $engine);
        }

        $hud = $this->view('components/hud-menu');
        $this->assertStringContainsString("\$shards   = \$inGame && ! \$logoCentre && ! \$regionCentre", $hud);
        $this->assertStringContainsString('<span class="hud-identity-region hud-region-centre">', $hud);
        $this->assertStringContainsString('.hud-region-centre {', (string) file_get_contents(FCPATH . 'assets/css/stage.css'));
    }

    /** Owner, 21 October 2026: the header plate starts under the number ring, and the frame keeps clear of it. */
    /** Owner, 22 October 2026: no frame box, header plate, or glow line; the stage is darkened instead. */
    public function testChallengeHasNoFrameBoxButADarkerStage(): void
    {
        $css = (string) preg_replace('~/\*.*?\*/~s', '', (string) file_get_contents(FCPATH . 'assets/css/game.css'));
        $this->assertMatchesRegularExpression('~\n\.challenge::before \{[^}]*inset: 0;[^}]*radial-gradient~', $css);
        preg_match('~\n\.challenge::before \{([^}]*)\}~', $css, $veil);
        $this->assertDoesNotMatchRegularExpression('~border|box-shadow~', $veil[1]);
        $this->assertStringNotContainsString('.challenge-head::before', $css);
        $this->assertStringNotContainsString('.challenge-head::after', $css);
        $this->assertMatchesRegularExpression('~\n\.chip-tally \{ gap: var\(--gap-2\); \}~', $css, 'the ✓/✗ count has no pill');
    }

    /** Owner, 22 October 2026: the header and instruction use the round pictures of the owner's sheets. */
    public function testHeaderAndInstructionUseTheRoundPictures(): void
    {
        $frame = (string) file_get_contents(APPPATH . 'Views/game/challenge/_frame.php');
        foreach (["round_icon('clock', 'stat-art'", "round_icon('right', 'stat-art'", "round_icon('wrong', 'stat-art'", "round_icon('help', 'chip-art'", "round_icon('logout', 'chip-art'", "round_icon('info', 'instruction-art')"] as $call) {
            $this->assertStringContainsString($call, $frame, $call);
        }
        $this->assertStringContainsString("round_icon('info', 'puzzle-caption-art')", (string) file_get_contents(APPPATH . 'Views/game/challenge/puzzle.php'));

        $finished = (string) file_get_contents(APPPATH . 'Views/game/challenge-finished.php');
        foreach (["round_icon('clock', 'result-stat-art'", "round_icon('wrong', 'result-stat-art'", "round_icon('replay', 'result-stat-art'", "'result-badge-art'"] as $call) {
            $this->assertStringContainsString($call, $finished, $call);
        }

        $css = (string) file_get_contents(FCPATH . 'assets/css/game.css');
        $this->assertStringContainsString('.stage[style*="--round-blank"][style*="--round-neutral"] .tries-dots > .tries-dot.is-left { background-image: var(--round-blank);', $css);
        $this->assertStringContainsString('.stage[style*="--round-blank"][style*="--round-neutral"] .result-shards-bar i.is-on { background-image: var(--round-blank);', $css);
        $this->assertMatchesRegularExpression('~\.stage\[style\*="--gold-pill-2"\] \.btn-danger \{[^}]*filter: hue-rotate\(-38deg\)~', $css, 'danger buttons on the gold pill turned red');
        $this->assertStringContainsString('.stage .modal-layer .modal-text.modal-extra {', (string) file_get_contents(FCPATH . 'assets/css/stage.css'));
    }

    /** Owner, 21 October 2026: the 💡 button reads "Bantuan"; "Petunjuk" is left to Cari Objek's clues. */
    public function testHintButtonSaysBantuan(): void
    {
        $id = require APPPATH . 'Language/id/Game.php';
        $js = require APPPATH . 'Language/id/Js.php';
        $this->assertSame('Bantuan', $id['hint']);
        $this->assertSame('Bantuan', $js['hintTitle']);
        $this->assertStringContainsString('Bantuan', $id['puzzleTipHint']);

        foreach (['hintPenalty', 'hintOpen', 'hintAllOpened', 'hintNone', 'hintUsed'] as $key) {
            $this->assertStringNotContainsStringIgnoringCase('petunjuk', $js[$key], $key);
        }
    }

    public function testCssNoLongerStylesJaka(): void
    {
        $css = (string) file_get_contents(FCPATH . 'assets/css/game.css');
        $this->assertStringNotContainsString('.challenge-character', $css);
        $this->assertStringNotContainsString('.finished-jaka', $css);
    }

    public function testStatementImagesCanBeEnlarged(): void
    {
        $boleh = $this->view('game/challenge/boleh');
        $this->assertStringContainsString('class="card-image" data-zoom=', $boleh);

        $js = (string) file_get_contents(FCPATH . 'assets/js/engines/boleh.js');
        $this->assertStringContainsString('createLightbox', $js);
        $this->assertStringNotContainsString('max-width', $js, 'tanpa cabang matchMedia lebar layar');

        // Dok 3 §11 audit, item 8: the event carries the region code like every other overlay_opened
        $this->assertStringContainsString("overlayOpened('statement_image', { levelId: ctx.levelId ?? null, level: ctx.levelCode ?? null })", $js);
        $this->assertMatchesRegularExpression('~const ctx = \{[^}]*\blevelCode,~', (string) file_get_contents(FCPATH . 'assets/js/game/challenge.js'));
    }

    /** Dok 3 §11 audit, item 7: every literal pose in a view exists for that character. */
    public function testViewsRequestOnlyKnownPoses(): void
    {
        $poses = config('Gelita')->characterPoses;
        $found = 0;

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(APPPATH . 'Views', FilesystemIterator::SKIP_DOTS)) as $file) {
            preg_match_all("~'character'\\s*=>\\s*'(\\w+)',\\s*'pose'\\s*=>\\s*'(\\w+)'~", (string) file_get_contents($file->getPathname()), $matches, PREG_SET_ORDER);

            foreach ($matches as [, $character, $pose]) {
                $found++;
                $this->assertContains($pose, $poses[$character] ?? [], "{$file->getFilename()}: {$character} has no pose {$pose}");
            }
        }

        // The result screen's and Balai Refleksi's Mbah Kedu became his avatar, and Mulai lost its Jaka
        // (owner, 21 October 2026): no view names a literal pose any more. Keep the pattern honest
        // so a literal pose added later is still checked.
        $this->assertSame(1, preg_match("~'character'\\s*=>\\s*'(\\w+)',\\s*'pose'\\s*=>\\s*'(\\w+)'~", "['character' => 'jaka', 'pose' => 'happy']"));
    }

    /** Owner's header, 20 October 2026: number ring, title, one stats capsule; no visible eyebrow. */
    public function testHeaderFollowsTheOwnersPicture(): void
    {
        $frame = $this->view('game/challenge/_frame');

        $this->assertStringContainsString("asset_url_versioned('ui/challenge-badge.webp')", $frame);
        $this->assertFileExists(FCPATH . 'assets/ui/challenge-badge.webp');
        $this->assertStringContainsString('<span class="badge-pos-no">', $frame);
        $this->assertStringContainsString('<div class="challenge-stats">', $frame);
        $this->assertStringNotContainsString('class="eyebrow"', $frame, 'the region · position · kind line left the screen');
        $this->assertMatchesRegularExpression("~<span class=\"visually-hidden\">[^\n]*Game\.challengeOf~", $frame, 'screen readers still hear it');

        // Timer, tally, and tries sit in the capsule; hint and exit come after it
        $capsule = substr($frame, (int) strpos($frame, 'challenge-stats'), (int) strpos($frame, 'id="btn-hint"') - (int) strpos($frame, 'challenge-stats'));
        foreach (['id="timer"', 'id="count-correct"', 'id="count-wrong"', 'id="tries"'] as $needle) {
            $this->assertStringContainsString($needle, $capsule, $needle);
        }
    }

    /** Owner's action bar, 20 October 2026: one centred bar across the arena in the batch engines. */
    public function testBatchEnginesShareTheCentredActionBar(): void
    {
        $stage = (string) file_get_contents(FCPATH . 'assets/css/stage.css');

        foreach (['puzzle', 'rumpang', 'boleh'] as $engine) {
            $view = $this->view('game/challenge/' . $engine);
            $this->assertMatchesRegularExpression('~class="btn btn-primary btn-lg btn-action" id="btn-check" type="button"><span><\?= esc\(lang\(\'Game\.\w+\'\)\) \?></span> <\?= icon\(\'right\'\) \?></button>~', $view, $engine);
            // Lanjut on the owner's arrow plate (21 October 2026)
            $this->assertStringContainsString("<button <?= plate_attrs('next', 'btn-action') ?> id=\"btn-next\" type=\"button\" hidden>", $view, $engine);
            $this->assertMatchesRegularExpression('~\.challenge-' . $engine . ' \.arena-actions\s*\{\s*--x:\s*32;\s*--y:\s*648;\s*--w:\s*1216;\s*--h:\s*56;~', $stage, $engine);
        }

        $css = (string) file_get_contents(FCPATH . 'assets/css/game.css');
        $this->assertStringContainsString('.arena-actions::before,', $css, 'gold rules on both sides');
        $this->assertMatchesRegularExpression('~\.arena-actions \{[^}]*justify-content: center;~', $css);
    }

    /** Owner, 20 October 2026: a blank is a dotted line with the word on it; no box, no number. */
    public function testRumpangBlanksAreDottedLinesWithoutNumbers(): void
    {
        $view = $this->view('game/challenge/rumpang');
        $this->assertStringNotContainsString('blank-no', $view);
        $this->assertStringNotContainsString('blank-wrap', $view);
        $this->assertSame(2, substr_count($view, '>&nbsp;</button>'), 'reading and sentence blanks start empty');

        $js = (string) file_get_contents(FCPATH . 'assets/js/engines/rumpang.js');
        $this->assertStringContainsString("const placeholder = '\\u00a0';", $js);

        $css = (string) file_get_contents(FCPATH . 'assets/css/game.css');
        $this->assertStringNotContainsString('.blank-no', $css);
        $this->assertMatchesRegularExpression('~\.blank \{[^}]*border: 0;[^}]*radial-gradient\(circle, var\(--dots\)~', $css);
    }

    /** Owner, 20 October 2026: Isi Rumpang words and Benar/Salah choices on the navy plate. */
    public function testAnswerChoicesUseTheNavyPlate(): void
    {
        $this->assertStringContainsString("--plate-navy: url(&quot;<?= esc(asset_url_versioned('ui/kenal-plate-navy.webp'), 'attr') ?>&quot;)", $this->view('game/challenge/_frame'));

        $css = (string) file_get_contents(FCPATH . 'assets/css/game.css');
        $this->assertStringContainsString('.challenge[style*="--plate-navy"] :is(.word, .choice) {', $css);
        $this->assertStringContainsString('border-image: var(--plate-navy) 0 115 fill / 0 2.375rem stretch;', $css);
    }

    /** Owner's "Tentang gambar ini" panel, 20 October 2026; puzzle.js still reads the one <p>. */
    public function testPuzzleCaptionFollowsTheOwnersPanel(): void
    {
        $view    = $this->view('game/challenge/puzzle');
        $caption = substr($view, (int) strpos($view, 'id="puzzle-caption"'));
        $caption = substr($caption, 0, (int) strpos($caption, '</div>' . "
" . '    <?php endif ?>'));

        $this->assertStringContainsString('<span class="puzzle-caption-icon" aria-hidden="true">i</span>', $caption);
        $this->assertStringContainsString("<b><?= esc(lang('Game.puzzleCaption')) ?></b>", $caption);
        $this->assertSame(1, substr_count($caption, '<p>'));
        $this->assertStringContainsString("\$('p', caption)", (string) file_get_contents(FCPATH . 'assets/js/engines/puzzle.js'));
    }
}
