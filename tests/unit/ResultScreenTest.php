<?php

use App\Entities\ChallengeAttempt;
use App\Entities\ChallengeNode;
use App\Entities\Level;
use App\Services\ContentRepository;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * Result screen redesign of 18 October 2026 (`/selesai/{id}`, mockup 01 in
 * gelita-katalog/mockup/gambar-mockup/tantangan-selesai-temanggung).
 *
 * Rules kept:
 * - the page uses the full-size HUD (logo, region, language popover, sound,
 *   menu) without the shard pill; other screens get the same HUD compact;
 * - every value comes from the attempt and progress, nothing from the mockup;
 * - skins come from image slots, with the plain look while a slot is empty;
 * - Cari Objek shows wrong taps instead of tries.
 *
 * No database: ContentRepository is replaced by a stub.
 *
 * @internal
 */
final class ResultScreenTest extends CIUnitTestCase
{
    /** asset_key → storage_path */
    private array $media = [];

    protected function setUp(): void
    {
        parent::setUp();
        helper(['url', 'gelita', 'content', 'ui']);

        $test = $this;

        Services::injectMock('contentRepository', new class ($test) extends ContentRepository {
            public function __construct(private ResultScreenTest $test)
            {
            }

            public function nodesForLevel(int $levelId): array
            {
                return [ResultScreenTest::node(1, 'puzzle'), ResultScreenTest::node(2, 'rumpang')];
            }

            public function mediaKeyMap(): array
            {
                return $this->test->media();
            }

            public function mediaMap(): array
            {
                return [];
            }
        });

        Services::resetSingle('renderer');
    }

    protected function tearDown(): void
    {
        Services::resetSingle('contentRepository');
        Services::resetSingle('renderer');
        parent::tearDown();
    }

    /** @return array<string, string> */
    public function media(): array
    {
        return $this->media;
    }

    public static function node(int $sequence, string $engine): ChallengeNode
    {
        $node = new ChallengeNode();
        $node->injectRawData(['id' => 10 + $sequence, 'level_id' => 1, 'sequence' => $sequence, 'engine_type' => $engine, 'title_id' => 'Tantangan ' . $sequence]);

        return $node;
    }

    public function testUsesTheRedesignedHud(): void
    {
        $page = $this->finished();

        $this->assertStringContainsString('class="hud hud-menu-style" data-in-game', $page);
        $this->assertStringNotContainsString('class="lantern', $page, 'no shard lantern on the result screen');
        $this->assertStringContainsString('<span class="hud-identity-region">Temanggung</span>', $page);

        // Language popover: the same POST form as the old switch, the chosen language marked
        $this->assertMatchesRegularExpression('/<details class="hud-pop hud-lang" data-popover>\s*<summary class="hud-ctl" aria-label="' . preg_quote(esc('Bahasa: Bahasa Indonesia', 'attr'), '/') . '"/', $page);
        $this->assertMatchesRegularExpression('/<form class="lang-switch hud-lang-list" method="post" action="[^"]*bahasa"/', $page);
        $this->assertMatchesRegularExpression('/value="id" lang="id"\s+class="hud-sheet-row hud-lang-row is-active" aria-pressed="true"/', $page);

        // Sound stays on the HUD; fullscreen, profile, Peta Kedu, and logout sit in the menu
        $this->assertStringContainsString('id="btn-sound"', $page);
        $menu = substr($page, strpos($page, 'class="hud-pop hud-menu"'));
        foreach (['id="btn-fullscreen"', 'href="' . base_url('profil') . '"', 'href="' . base_url('peta') . '"', 'href="' . base_url('keluar') . '"', '@jaka'] as $needle) {
            $this->assertStringContainsString($needle, $menu, $needle);
        }

        // Owner, 18 October 2026: every other screen has the same HUD, compact, with the shard pill in the middle
        Services::resetSingle('renderer');
        $other = view('game/challenge-result', $this->hud() + [
            'level' => $this->level(), 'node' => self::node(1, 'puzzle'), 'sequence' => 1, 'attempts' => [], 'best' => null,
        ]);
        $this->assertStringContainsString('<header class="hud hud-menu-style is-compact" data-in-game', $other);
        $this->assertMatchesRegularExpression('/<div class="hud-shards" style="[^"]*">(?:<!--[^>]*-->\s*)?<details class="lantern"/', $other);
        // The owner's shard plate (20 October 2026): frame and fill texture as absolute URLs, the crystal's glow layer
        $this->assertStringContainsString('--shards-frame: url(&quot;' . esc(asset_url_versioned('ui/hud-shards-frame.webp'), 'attr') . '&quot;)', $other);
        $this->assertStringContainsString('--shards-fill: url(&quot;' . esc(asset_url_versioned('ui/hud-shards-fill.webp'), 'attr') . '&quot;)', $other);
        $this->assertStringContainsString('<span class="lantern-gem" aria-hidden="true"></span>', $other);
        $this->assertMatchesRegularExpression('/<span class="lantern-row">\s*<span class="lantern-count num"><b>5<\/b>\/12<\/span>\s*<span class="lantern-bar"/', $other, 'the bar beside the count');
        foreach (['class="hud-pop hud-lang"', 'id="btn-sound"', 'class="hud-pop hud-menu"', '<span class="hud-identity-region">Temanggung</span>'] as $needle) {
            $this->assertStringContainsString($needle, $other, $needle);
        }
    }

    /**
     * `/hasil/{code}/{n}` (owner, 21 October 2026): no panel box, Kembali (left) and Ulangi (right)
     * right under the heading, the best play as a slim band of three figures, no nav bar.
     */
    public function testAttemptListPutsBothButtonsUnderTheHeading(): void
    {
        $best = new ChallengeAttempt();
        $best->injectRawData(['id' => 105, 'attempt_no' => 1, 'session_id' => 9, 'challenge_node_id' => 11, 'status' => 'completed',
            'completion_reason' => 'solved', 'stars' => 2, 'duration_ms' => 125000, 'first_pass_correct' => 0, 'final_correct' => 1,
            'scorable_items' => 1, 'check_count' => 2, 'max_tries' => 3, 'started_at' => '2026-10-21 10:06:00']);

        $page = view('game/challenge-result', $this->hud() + [
            'level' => $this->level(), 'node' => self::node(1, 'puzzle'), 'sequence' => 1, 'attempts' => [$best], 'best' => $best,
        ]);

        $this->assertStringContainsString('<div class="result-panel stage-box">', $page, 'no .panel box around the page');
        $this->assertStringNotContainsString('class="nav-bar"', $page);
        $this->assertMatchesRegularExpression(
            '/<\/header>\s*<nav class="result-list-actions"[^>]*>\s*<a class="btn btn-quiet" href="' . preg_quote(base_url('wilayah/temanggung'), '/')
            . '">.*?<\/a>\s*<a ' . preg_quote(plate_attrs('back'), '/') . ' href="' . preg_quote(base_url('misi/temanggung/1'), '/') . '"><span>' . preg_quote(esc(lang('Game.retry')), '/') . '<\/span>/s',
            $page,
        );
        $this->assertStringContainsString('<section class="result-best" aria-labelledby="result-best-title">', $page);
        $this->assertSame(3, substr_count($page, '<div><dt>'), 'three figures: first try, time, tries');
        $this->assertStringContainsString('<dd class="num">2:05</dd>', $page);
        $this->assertStringContainsString('<tr class="is-best">', $page);
    }

    /** The owner's shard plate (20 October 2026): the pictures ship with the game, the page draws the fill. */
    public function testShardPlateDrawsTheFillOverTheOwnersFrame(): void
    {
        foreach (['hud-shards-frame.webp', 'hud-shards-fill.webp'] as $file) {
            $this->assertFileExists(FCPATH . 'assets/ui/' . $file);
        }

        $css = (string) file_get_contents(FCPATH . 'assets/css/stage.css');
        $this->assertStringContainsString('background: var(--shards-frame) center / 100% 100% no-repeat;', $css);
        $this->assertStringContainsString('var(--shards-fill) left center', $css, 'the fill uses the owner\'s texture, not one flat colour');
        $this->assertStringContainsString('.hud-shards .lantern-toggle > .flame { display: none; }', $css, 'the crystal replaces the lantern icon');
        $this->assertMatchesRegularExpression('/\.hud-shards \.lantern\[data-stage="3"\] \.lantern-gem \{[^}]*animation: gem-pulse/', $css);
        // Owner, 20 October 2026: the compact plate fits the 56 dp strip with room above and below
        $this->assertStringContainsString('.is-compact .hud-shards { --plate-w: 16rem;', $css);
    }

    public function testPanelShowsTheAttempt(): void
    {
        $page = $this->finished();

        $this->assertStringContainsString('<p class="result-kind">' . lang('Game.engine_puzzle') . '</p>', $page);
        $this->assertStringContainsString('aria-label="' . esc(lang('Game.resultStarsOf', [2, 3]), 'attr') . '"', $page);
        $this->assertSame(2, substr_count($page, 'class="result-star is-on"'));
        $this->assertSame(1, substr_count($page, 'class="result-star is-off"'), 'the empty star is the same picture, grey and faded');
        $this->assertStringContainsString('<p class="result-shards-count num" aria-label="' . esc(lang('Game.shardEarned', [5, 12]), 'attr') . '">5 / 12</p>', $page);
        $this->assertSame(5, substr_count($page, '<i class="is-on"></i>'), 'one lit segment per shard');
        $this->assertStringContainsString('<dd class="num">2:05</dd>', $page);
        $this->assertStringContainsString('<dd class="num is-words">' . lang('Game.triesOf', [2, 3]) . '</dd>', $page);
        $this->assertStringContainsString('class="result-badge is-solved"', $page);
        $this->assertStringContainsString(lang('Game.multiCheckNote', [2]), $page);

        // Buttons: region map, Detail hasil (closed, first-try result inside), Berikutnya to challenge 2
        $this->assertStringContainsString('href="' . base_url('wilayah/temanggung') . '"><svg', $page);
        $this->assertStringContainsString(lang('Game.resultRegionMap', ['Temanggung']), $page);
        $this->assertMatchesRegularExpression('/<details class="result-detail" data-popover>\s*<summary class="result-btn is-detail">/', $page);
        $this->assertMatchesRegularExpression('/<dt>' . preg_quote(lang('Game.firstPass'), '/') . '<\/dt>\s*<dd>' . lang('Game.firstTryNo') . '<\/dd>/', $page);
        $this->assertStringContainsString('<a class="result-btn is-primary" href="' . base_url('misi/temanggung/2') . '"><span>' . lang('Game.next') . '</span>', $page);
        $this->assertStringNotContainsString('result-kedu', $page, 'region not complete');
    }

    public function testCariShowsWrongTapsAndTheExhaustedNote(): void
    {
        $page = $this->finished(['completion_reason' => 'tries_exhausted', 'check_count' => 0, 'stars' => 1], 'cari', 4);

        $this->assertStringContainsString(lang('Game.wrongTaps'), $page);
        $this->assertStringContainsString('<dd class="num">4</dd>', $page);
        $this->assertStringNotContainsString(' ' . lang('Game.resultTries') . '</dt>', $page);
        $this->assertStringContainsString('class="result-badge is-saved"', $page);
        $this->assertStringContainsString(esc(lang('Game.exhaustedNote')), $page);
    }

    public function testFirstTryMessage(): void
    {
        $page = $this->finished(['check_count' => 1, 'stars' => 3, 'first_pass_correct' => 1]);

        $this->assertStringContainsString(lang('Game.resultFirstTry'), $page);
        $this->assertSame(3, substr_count($page, 'class="result-star is-on"'));
    }

    /** Owner request 18 October 2026: the region-done card is a modal over a blurred, dimmed layer. */
    public function testRegionDoneCardSitsOverABlurLayer(): void
    {
        $page = $this->finished(levelDone: true);

        $this->assertMatchesRegularExpression('/<div class="result-scrim" aria-hidden="true"><\/div>\s*<aside class="result-sheet result-kedu stage-box" role="dialog" aria-labelledby="result-kedu-title"/', $page);
        $this->assertStringContainsString(lang('Game.regionDone', ['Temanggung']) . ' ' . lang('Game.allShardsDone', [12]), $page);
        // Owner request 21 October 2026: "Ketuk layar untuk melanjutkan" instead of the cross; Mbah Kedu's avatar and nameplate
        $this->assertMatchesRegularExpression('/class="result-kedu-tap" data-dismiss hidden>' . preg_quote(esc(lang('Game.tapToContinue')), '/') . '</', $page, 'the tap prompt needs JavaScript');
        $this->assertStringNotContainsString('hud-sheet-close" data-dismiss', $page);
        $this->assertMatchesRegularExpression('/<div class="kedu-speaker">.*<span class="kenal-nameplate">' . preg_quote(lang('Game.char_mbah_kedu'), '/') . '<\/span>/s', $page);
        $this->assertStringNotContainsString('result-scrim', $this->finishedPlain(), 'no layer while the region is open');
    }

    private function finishedPlain(): string
    {
        Services::resetSingle('renderer');

        return $this->finished();
    }

    public function testSkinsComeFromTheirSlots(): void
    {
        $plain = $this->finished();
        $this->assertStringNotContainsString('--skin-', $plain, 'empty slots keep the plain look');
        $this->assertStringContainsString('<span class="orb">', $plain, 'medal fallback');
        $this->assertStringContainsString('<img class="result-star is-on" src="' . esc(asset_url_versioned('ui/star.webp'), 'attr'), $plain, 'the shipped star until the slot is filled');

        $this->media = [
            'ui.result-panel'          => 'assets/uploads/ui.result-panel.webp',
            'ui.result-button-primary' => 'assets/uploads/ui.result-button-primary.webp',
            'ui.result-hud-control'    => 'assets/uploads/ui.result-hud-control.webp',
            'ui.result-star'           => 'assets/uploads/ui.result-star.webp',
            'ui.result-medal'          => 'assets/uploads/ui.result-medal.webp',
            'bg.result.temanggung'     => 'assets/uploads/bg.result.temanggung.webp',
        ];
        service('contentRepository')->flush();
        Services::resetSingle('renderer');
        $page = $this->finished();

        $this->assertMatchesRegularExpression('/<article class="result-card stage-box" aria-labelledby="result-title" style="--skin-panel: url\(&quot;[^"]*ui\.result-panel\.webp&quot;\); --skin-primary: url\(&quot;[^"]*ui\.result-button-primary\.webp&quot;\)">/', $page);
        $this->assertStringContainsString('style="--skin-hud: url(&quot;', $page);
        $this->assertStringContainsString('<img class="result-star is-on" src="' . esc(base_url('assets/uploads/ui.result-star.webp'), 'attr'), $page);
        $this->assertStringContainsString('<div class="result-medal has-art" aria-hidden="true">', $page);
        $this->assertStringContainsString('id="scene-a"' . "\n" . '             src="' . esc(base_url('assets/uploads/bg.result.temanggung.webp'), 'attr'), $page, 'the region result background');
    }

    private function level(): Level
    {
        $level = new Level();
        $level->injectRawData(['id' => 1, 'code' => 'temanggung', 'sequence' => 1, 'name_id' => 'Temanggung', 'name_en' => 'Temanggung',
            'difficulty' => 'mudah', 'background_media_id' => null, 'map_media_id' => null]);

        return $level;
    }

    /** @return array<string, mixed> */
    private function hud(): array
    {
        return [
            'locale'      => 'id',
            'participant' => ['id' => 5, 'username' => 'jaka', 'display_name' => 'Jaka', 'avatar_no' => null],
            'progress'    => ['completed_nodes' => 5, 'completed_levels' => 0, 'unlocked_level_sequence' => 1,
                'total_stars' => 0, 'shards' => 5, 'shards_total' => 12, 'current_level_id' => null, 'current_node_id' => null],
            'lantern' => [],
        ];
    }

    /** @param array<string, mixed> $attempt */
    private function finished(array $attempt = [], string $engine = 'puzzle', ?int $wrongTaps = null, bool $levelDone = false): string
    {
        $entity = new ChallengeAttempt();
        $entity->injectRawData($attempt + ['id' => 105, 'session_id' => 9, 'challenge_node_id' => 11, 'status' => 'completed',
            'completion_reason' => 'solved', 'stars' => 2, 'duration_ms' => 125000, 'first_pass_correct' => 0, 'final_correct' => 1,
            'scorable_items' => 1, 'check_count' => 2, 'max_tries' => 3]);

        return view('game/challenge-finished', $this->hud() + [
            'attempt'         => $entity,
            'node'            => self::node(1, $engine),
            'level'           => $this->level(),
            'levelSummary'    => ['stars' => 2, 'completed_nodes' => 1, 'total_nodes' => 4],
            'levelCompleted'  => $levelDone,
            'closesRegion'    => false,
            'libraryUnlocked' => false,
            'allCompleted'    => $levelDone,
            'nextRegion'      => null,
            'wrongTaps'       => $wrongTaps,
        ]);
    }
}
