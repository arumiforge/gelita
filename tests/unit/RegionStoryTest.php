<?php

use App\Controllers\Game\DialogueController;
use App\Controllers\Game\MapController;
use App\Entities\ChallengeAttempt;
use App\Entities\ChallengeNode;
use App\Entities\GameSession;
use App\Entities\Level;
use App\Entities\LibraryPage;
use App\Entities\Participant;
use App\Libraries\ImageSlots;
use App\Models\GameEventLogModel;
use App\Services\AssetPackageBuilder;
use App\Services\ContentRepository;
use App\Services\GameContext;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * Tahap 3: Kenali wilayah, tirai wilayah & tantangan, dialog dramatis,
 * adegan wilayah tuntas (`/tuntas/{code}`), dan penutup (`/penutup`).
 *
 * Aturan yang dijaga:
 * - `/tuntas/{code}` hanya untuk wilayah yang tuntas; selain itu ke peta
 *   wilayahnya. Slide akhirnya: Baca Pustaka + Lanjut ke wilayah berikutnya
 *   (tirai wilayah, `entry`), atau Lanjut ke `/penutup` di wilayah terakhir;
 * - `/penutup` hanya setelah seluruh node tuntas, slide akhirnya ke Balai Refleksi;
 * - peta membawa overlay Kenali per wilayah (termasuk yang terkunci), lencana
 *   "Belum didengar" dari event server, dan tirai wilayah pada pin/kartu;
 * - layar selesai attempt penuntas wilayah menuju `/tuntas/{code}`;
 * - tirai tantangan diputar di kartu misi, sebelum attempt dibuka.
 *
 * Tanpa database: ContentRepository dan GameContext diganti tiruan; data yang
 * dibaca dari database (status wilayah, progres, HUD) diisi lewat probe.
 *
 * @internal
 */
final class RegionStoryTest extends CIUnitTestCase
{
    private const LEVELS = ['temanggung' => 1, 'magelang' => 2, 'wonosobo' => 3];

    private const TAGLINES = [1 => 'Di Antara Dua Gunung', 2 => 'Tanah Candi Agung', 3 => 'Negeri di Atas Awan'];

    private const DIFFICULTY = [1 => 'mudah', 2 => 'sedang', 3 => 'sulit'];

    /** asset_key → storage_path aktif */
    private array $media = [];

    /** `{context}.{sequence}` → media id of that test slide's own background (ids in mediaMap()) */
    public static array $slideBackgrounds = [];

    protected function setUp(): void
    {
        parent::setUp();
        helper(['url', 'gelita', 'content', 'ui']);

        $test = $this;

        Services::injectMock('contentRepository', new class ($test) extends ContentRepository {
            public function __construct(private RegionStoryTest $test)
            {
            }

            public function level(string $code): ?Level
            {
                return RegionStoryTest::level($code);
            }

            public function dialogues(?int $levelId, string $context = 'level_open'): array
            {
                return RegionStoryTest::lines($levelId, $context);
            }

            public function libraryPages(int $levelId): array
            {
                $page = new LibraryPage();
                $page->injectRawData(['id' => 40 + $levelId, 'level_id' => $levelId, 'sequence' => 1, 'title_id' => 'Halaman', 'body_id' => 'Isi.']);

                return [$page];
            }

            public function nodesForLevel(int $levelId): array
            {
                return [];
            }

            public function mediaKeyMap(): array
            {
                return $this->test->media();
            }

            public function mediaMap(): array
            {
                return [701 => 'assets/uploads/bg-story-a.webp', 702 => 'assets/uploads/bg-story-b.webp'];
            }
        });

        self::$slideBackgrounds = [];
        $session                = new GameSession();
        $session->injectRawData(['id' => 9, 'participant_id' => 5, 'locale' => 'id', 'status' => 'active']);

        $participant = new Participant();
        $participant->injectRawData(['id' => 5, 'username' => 'jaka', 'display_name' => 'Jaka', 'intro_seen_at' => '2026-09-01 08:00:00']);

        $context              = new GameContext();
        $context->session     = $session;
        $context->participant = $participant;
        Services::injectMock('gameContext', $context);

        session()->remove(['curtain', 'dialogues_shown']);
        Services::resetSingle('renderer');
    }

    protected function tearDown(): void
    {
        foreach (['contentRepository', 'gameContext', 'renderer'] as $name) {
            Services::resetSingle($name);
        }

        session()->remove(['curtain', 'dialogues_shown']);
        self::$slideBackgrounds = [];
        parent::tearDown();
    }

    // ------------------------------------------------------------ /tuntas/{code}

    /** @return array<string, array{string}> */
    public static function unfinishedStatuses(): array
    {
        return ['baru terbuka' => ['open'], 'sedang dijelajahi' => ['in_progress'], 'terkunci' => ['locked']];
    }

    /**
     * @dataProvider unfinishedStatuses
     */
    public function testUnfinishedRegionGoesToItsRegionMap(string $status): void
    {
        $page = $this->dialogue(['magelang' => $status])->done('magelang');

        $this->assertInstanceOf(RedirectResponse::class, $page);
        $this->assertStringEndsWith('/wilayah/magelang', $page->getHeaderLine('Location'));
    }

    public function testFinishedRegionPlaysTheDoneSceneWithNextRegionCurtain(): void
    {
        $page = $this->dialogue(['temanggung' => 'completed', 'magelang' => 'open', 'wonosobo' => 'locked'])->done('temanggung');

        $this->assertIsString($page);
        $this->assertStringContainsString('data-screen="region-done"', $page);
        $this->assertStringContainsString('data-music="tuntas-temanggung"', $page, 'the region\'s celebration track');
        $this->assertStringContainsString('data-context="level_done" data-prefix="line-" data-mode="tap"', $page);
        // Kartu ketuk "Serpihan {wilayah} kembali!"
        $this->assertStringContainsString('class="narrator-tap is-done" data-narrator-tap hidden', $page);
        $this->assertStringContainsString(esc(lang('Game.doneTitle', ['Temanggung'])), $page);
        $this->assertStringContainsString('Lima serpihan sudah kembali ke lentera!', $page);
        $this->assertMatchesRegularExpression('/id="line-1"[^>]*data-pose="smile"[^>]*data-effect="fog-lift"/', $page);

        // Slide akhir: Baca Pustaka + Lanjut ke wilayah berikutnya lewat dialog pembukanya, bertirai
        $this->assertStringContainsString('href="' . esc(base_url('pustaka/temanggung'), 'attr') . '"', $page);
        $this->assertStringContainsString(esc(lang('Game.readLibraryRegion', ['Temanggung'])), $page);
        $this->assertStringContainsString($this->href('dialog/magelang') . ' data-slide-final data-curtain="region"', $page);
        $this->assertStringContainsString(esc(lang('Game.goToRegion', ['Magelang'])), $page);
        $this->assertStringContainsString('<template id="tpl-curtain-region">', $page);
        $this->assertStringContainsString(esc(json_encode(['title' => 'Menuju Magelang…', 'tagline' => 'Tanah Candi Agung', 'chip' => 'Sedang'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'attr'), $page);
    }

    public function testLastRegionContinuesToTheEnding(): void
    {
        $all = ['temanggung' => 'completed', 'magelang' => 'completed', 'wonosobo' => 'completed'];

        $page = $this->dialogue($all, allDone: true)->done('wonosobo');
        $this->assertStringContainsString($this->href('penutup') . ' data-slide-final>' . esc(lang('Game.continue')), $page);
        $this->assertStringNotContainsString('tpl-curtain-region', $page, 'wilayah terakhir tanpa tirai wilayah');

        // Mode unlock `free`: wilayah terakhir tuntas lebih dulu → kembali ke peta, bukan penutup
        Services::resetSingle('renderer');
        $early = $this->dialogue(['temanggung' => 'in_progress', 'magelang' => 'open', 'wonosobo' => 'completed'], allDone: false)->done('wonosobo');
        $this->assertStringNotContainsString(base_url('penutup'), $early);
        $this->assertStringContainsString($this->href('peta') . ' data-slide-final>', $early);
    }

    // ------------------------------------------------------------ /penutup

    public function testEndingWaitsForEveryNode(): void
    {
        $page = $this->dialogue([], allDone: false)->ending();

        $this->assertInstanceOf(RedirectResponse::class, $page);
        $this->assertStringEndsWith('/peta', $page->getHeaderLine('Location'));
    }

    public function testEndingIsACinematicScreenThatLeadsToReflection(): void
    {
        $page = $this->dialogue([], allDone: true)->ending();

        $this->assertIsString($page);
        $this->assertStringContainsString('is-cinematic', $page);
        $this->assertStringContainsString('data-screen="ending"', $page);
        $this->assertStringContainsString('data-music="ending"', $page);
        $this->assertStringContainsString('data-narrator data-context="ending" data-prefix="slide-" data-mode="tap"', $page);
        $this->assertMatchesRegularExpression('/class="narrator-tap" data-narrator-tap hidden/', $page);
        $this->assertStringContainsString('Lentera Menyala Kembali', $page);
        $this->assertMatchesRegularExpression('/id="slide-4"[^>]*data-character="jaka" data-pose="bow"/', $page);
        $this->assertMatchesRegularExpression('/id="slide-5".*?' . preg_quote($this->href('refleksi'), '/') . ' data-slide-final/s', $page);
    }

    // ------------------------------------------------------------ /dialog/{code}

    public function testDialogueIsNarratedWithChapterCardAndPoseFrames(): void
    {
        $this->media = ['char.jaka.afraid.1' => 'assets/char/jaka-afraid-1.png', 'char.jaka.afraid.2' => 'assets/char/jaka-afraid-2.png', 'char.kedu.idle.1' => 'assets/char/kedu-idle-1.png'];

        $page = $this->dialogue(['magelang' => 'open'])->show('magelang');

        $this->assertStringContainsString('data-screen="dialogue"', $page);
        $this->assertStringContainsString('data-music="dialog-magelang"', $page, 'the region\'s dialogue track');
        $this->assertStringContainsString('data-narrator data-context="level_open" data-prefix="line-" data-mode="tap"', $page);
        // Kartu bab: "Bab {n} · {wilayah}", tagline, ajakan, jejak kaki
        $this->assertStringContainsString('class="narrator-tap is-chapter"', $page);
        $this->assertStringContainsString(esc(lang('Game.chapterOf', [2, 'Magelang'])), $page);
        $this->assertStringContainsString('Tanah Candi Agung', $page);
        $this->assertStringContainsString(esc(lang('Game.chapterTap')), $page);
        $this->assertStringContainsString('class="footsteps tap-trail"', $page);

        // Frame pose per baris dari server: pose ada → pose; pose belum ada → idle; tanpa gambar → kosong
        $this->assertMatchesRegularExpression('/id="line-1"[^>]*data-pose="afraid" data-pose-src="[^"]*jaka-afraid-1\.png"/', $page);
        $this->assertMatchesRegularExpression('/id="line-2"[^>]*data-pose="worried" data-pose-src="[^"]*kedu-idle-1\.png"[^>]*data-effect="fog"/', $page);
        $this->assertMatchesRegularExpression('/id="line-3"[^>]*data-pose="happy" data-pose-src=""/', $page);
        // Several frames: the line also carries them for core/sprite.js; one frame: none
        $this->assertMatchesRegularExpression('/id="line-1"[^>]*data-pose-frames="[^"]*jaka-afraid-2\.png[^"]*" data-pose-fps="1" data-pose-loop="1"/', $page);
        $this->assertDoesNotMatchRegularExpression('/id="line-2"[^>]*data-pose-frames/', $page);

        // Panggung dua tokoh, akhir ke peta wilayah, nav Lewati tetap
        $this->assertStringContainsString('character-jaka stage-left', $page);
        $this->assertStringContainsString('character-mbah_kedu stage-right', $page);
        $this->assertStringContainsString(esc(lang('Game.enterRegionName', ['Magelang'])), $page);
        $this->assertStringContainsString('href="' . esc(base_url('wilayah/magelang'), 'attr') . '" data-slide-final', $page);
        $this->assertStringContainsString('<span>' . esc(lang('Game.skip')) . '</span>', $page);
    }

    /** Owner, 19 October 2026: a region slide may have its own background; a slide without one keeps the region background. */
    public function testRegionDialogueLineCarriesItsOwnBackground(): void
    {
        self::$slideBackgrounds = ['level_open.2' => 701];

        $page = $this->dialogue(['magelang' => 'open'])->show('magelang');

        $this->assertStringContainsString('data-backdrop="stage"', $page);
        $this->assertMatchesRegularExpression('#data-backdrop="stage" data-fx3d="[^"]*three-0\.186\.1/three\.module\.js\?v=#', html_entity_decode($page), 'WebGL screen effects');
        $this->assertMatchesRegularExpression('/id="line-2"[^>]*data-bg="[^"]*bg-story-a\.webp"/', $page);
        $this->assertDoesNotMatchRegularExpression('/id="line-1"[^>]*data-bg=/', $page);
        $this->assertDoesNotMatchRegularExpression('/id="line-3"[^>]*data-bg=/', $page);
        // The layout still draws both scene images the crossfade uses
        $this->assertStringContainsString('id="scene-b"', $page);
    }

    public function testRegionDoneLineCarriesItsOwnBackground(): void
    {
        self::$slideBackgrounds = ['level_done.1' => 702];

        $page = $this->dialogue(['temanggung' => 'completed', 'magelang' => 'open', 'wonosobo' => 'locked'])->done('temanggung');

        $this->assertStringContainsString('data-backdrop="stage"', $page);
        $this->assertMatchesRegularExpression('/id="line-1"[^>]*data-bg="[^"]*bg-story-b\.webp"/', $page);
        $this->assertDoesNotMatchRegularExpression('/id="line-2"[^>]*data-bg=/', $page);
    }

    public function testRegionIntroSlideCarriesItsOwnBackground(): void
    {
        self::$slideBackgrounds = ['region_intro.3' => 701];

        $page = $this->map(['temanggung' => 'open', 'magelang' => 'locked', 'wonosobo' => 'locked'], heard: []);

        $this->assertMatchesRegularExpression('/id="kenal-wonosobo-3"[^>]*data-bg="[^"]*bg-story-a\.webp"/', $page);
        $this->assertDoesNotMatchRegularExpression('/id="kenal-wonosobo-2"[^>]*data-bg=/', $page);

        // Every overlay has the two pictures the crossfade needs, before its header, and the WebGL effects
        $this->assertSame(3, preg_match_all('#data-kenal="[a-z]+"[^>]*data-fx3d="[^"]*three\.module\.js#', html_entity_decode($page)));
        foreach (array_keys(self::LEVELS) as $code) {
            $overlay = substr($page, strpos($page, 'id="kenal-' . $code . '"'));
            $overlay = substr($overlay, 0, strpos($overlay, 'class="kenal-head"'));
            $this->assertSame(2, substr_count($overlay, '<img class="kenal-bg'), $code);
        }
    }

    /** Owner, 19 October 2026 (with the same day's follow-ups): the region dialogue in the intro layout, parchment box, difficulty in the HUD. */
    public function testDialogueUsesTheIntroLayoutWithTheDifficultyBadge(): void
    {
        $page  = $this->dialogue(['magelang' => 'open'])->show('magelang');
        $total = count(self::lines(2, 'level_open'));

        // Full-size HUD with the shard pill (placed under the logo); Lewati under the menu button (is-cinematic)
        $this->assertStringContainsString('is-cinematic is-region-dialogue', $page);
        $this->assertStringContainsString('class="hud hud-menu-style"', $page);
        $this->assertStringContainsString('class="hud-shards"', $page);
        $this->assertStringContainsString('class="story dialogue narrator cine dialogue-cine"', $page);
        $this->assertStringNotContainsString('dialogue-head', $page);
        $this->assertStringNotContainsString('class="cine-top"', $page, 'no progress in the HUD centre');

        // The difficulty badge sits in the HUD, right before the language control, without a cheer line
        $controls = (int) strpos($page, '<div class="hud-right hud-controls">');
        $badge    = (int) strpos($page, '<div class="region-difficulty is-sedang">');
        $this->assertGreaterThan($controls, $badge);
        $this->assertGreaterThan($badge, (int) strpos($page, '<details class="hud-pop hud-lang"'));
        $this->assertSame(2, substr_count($page, '<i class="is-on"'), 'two of three bars lit');
        $this->assertStringContainsString(esc(lang('Game.difficultyLabel')), $page);
        $this->assertStringContainsString(esc(lang('Game.difficultyLevel', [lang('Game.difficulty_sedang')])), $page);
        $this->assertStringNotContainsString('region-difficulty-hint', $page);

        // The parchment box: the speaker's name, then the dots only (no "Bagian n dari N" text); ▶ reads "Lanjut"
        $this->assertMatchesRegularExpression('/id="line-1".*?<div class="dialogue-box panel-parchment dialogue-caption" data-narrator-advance>\s*<div class="dialogue-caption-head">\s*<cite class="dialogue-speaker">[^<]+<\/cite>\s*<nav class="dialogue-progress" aria-label="' . preg_quote(esc(lang('Game.slideOf', [1, $total]), 'attr'), '/') . '">\s*<ol class="slide-dots">/s', $page);
        $this->assertStringNotContainsString('<span class="eyebrow">' . esc(lang('Game.slideOf', [1, $total])), $page);
        $this->assertStringNotContainsString('cine-caption', $page);
        $this->assertStringContainsString('data-narrator="next" aria-label="' . esc(lang('Game.continue'), 'attr') . '"><span class="narrator-next-label"', $page);

        // The last line's main button sits in the middle of the page
        $this->assertMatchesRegularExpression('/id="line-' . $total . '".*?<div class="cine-final">\s*<a class="btn btn-primary btn-lg" ' . preg_quote($this->href('wilayah/magelang'), '/') . ' data-slide-final>/s', $page);

        // Only Lewati at the bottom; Peta Kedu lives in the menu
        $nav = substr($page, (int) strpos($page, '<nav class="nav-bar"'));
        $nav = substr($nav, 0, (int) strpos($nav, '</nav>'));
        $this->assertStringContainsString(esc(lang('Game.skip')), $nav);
        $this->assertStringNotContainsString(esc(lang('Game.mapKedu')), $nav);
        $this->assertStringNotContainsString($this->href('peta'), $nav);
    }

    public function testRegionDoneSceneKeepsTheParchmentBox(): void
    {
        $page = $this->dialogue(['temanggung' => 'completed', 'magelang' => 'open', 'wonosobo' => 'locked'])->done('temanggung');

        $this->assertStringContainsString('class="story dialogue narrator"', $page);
        $this->assertStringContainsString('dialogue-box panel-parchment', $page);
        $this->assertStringContainsString('screen-head dialogue-head stage-box', $page);
        $this->assertStringNotContainsString('region-difficulty', $page);
    }

    public function testDialogueJsLeavesTheEventToTheNarrator(): void
    {
        $dialogue = (string) file_get_contents(FCPATH . 'assets/js/game/dialogue.js');
        $narrator = (string) file_get_contents(FCPATH . 'assets/js/game/narrator.js');

        $this->assertStringNotContainsString("emit('dialogue_advanced'", $dialogue, 'tidak menggandakan event');
        $this->assertStringContainsString('initNarrator(screen, { onSlide:', $dialogue);
        $this->assertSame(1, substr_count($narrator, "emit('dialogue_advanced'"));
    }

    // ------------------------------------------------------------ peta: Kenali wilayah

    public function testMapCarriesARegionIntroOverlayForEveryRegion(): void
    {
        $page = $this->map(['temanggung' => 'open', 'magelang' => 'locked', 'wonosobo' => 'locked'], heard: []);

        foreach (self::LEVELS as $code => $id) {
            $this->assertMatchesRegularExpression(
                '/<section class="kenal narrator" id="kenal-' . $code . '"[^>]*data-kenal="' . $code . '" data-narrator data-context="region_intro"'
                . '\s+data-prefix="kenal-' . $code . '-" data-mode="external" data-keyboard="0" data-hash="0"\s+data-level-id="' . $id . '"/',
                $page,
                "overlay {$code}",
            );
            $this->assertStringContainsString(esc(lang('Game.kenalTitle', [ucfirst($code)])), $page);
            $this->assertStringContainsString(self::TAGLINES[$id], $page);
            $this->assertStringContainsString('id="kenal-' . $code . '-4"', $page, '4 slide');
            $this->assertStringNotContainsString('id="kenal-' . $code . '-5"', $page);
            // Peta Kedu redesign: one Kenali button, in the region panel, a :target link without JavaScript
            $this->assertSame(1, substr_count($page, 'href="#kenal-' . $code . '"'), "tombol Kenali {$code}");
        }

        $this->assertStringContainsString('aria-label="' . esc(lang('Game.kenalRegion', ['Wonosobo']), 'attr') . '"', $page);
        // Overlay di luar <main>, pose & efek naskah sampai ke markup
        $this->assertLessThan(strpos($page, '<main id="app"'), strpos($page, 'id="kenal-temanggung"'));
        $this->assertMatchesRegularExpression('/id="kenal-wonosobo-1"[^>]*data-pose="smile"\s+data-effect="fog"/', $page);
    }

    public function testRegionIntroEndsWithEnterOrClose(): void
    {
        $page = $this->map(['temanggung' => 'open', 'magelang' => 'locked', 'wonosobo' => 'locked'], heard: []);

        // Terbuka: "Masuk ke {wilayah}" lewat entry, dengan tirai wilayah
        $this->assertStringContainsString($this->href('dialog/temanggung') . ' data-slide-final data-curtain="region"', $page);
        $this->assertStringContainsString(esc(lang('Game.enterRegionName', ['Temanggung'])), $page);
        // Terkunci: boleh dikenali, slide akhir "Tutup" ke kartu wilayahnya
        $this->assertStringContainsString('href="' . esc('#region-magelang', 'attr') . '" data-slide-final data-kenal-close', $page);
        $this->assertStringNotContainsString(esc(lang('Game.enterRegionName', ['Magelang'])), $page);
        $this->assertStringContainsString('id="region-magelang"', $page);
    }

    /**
     * Owner, 19 October 2026: HUD-like band, the gold title over its ornament
     * rule under it, the avatar with its nameplate and the caption at the
     * bottom, the owner's plates on Back/Kembali (navy) and Lanjut (gold), the
     * narrator controls on the HUD skin, no progress text.
     */
    public function testRegionIntroLayoutHasPlatesANameplateAndAGoldTitle(): void
    {
        $this->media = ['ui.kedu-avatar' => 'assets/uploads/ui.kedu-avatar.webp?v=k1', 'ui.logo-hero' => 'assets/uploads/ui.logo-hero.webp?v=l1',
            'ui.result-hud-control' => 'assets/uploads/ui.result-hud-control.webp?v=h1', 'char.mbah_kedu.idle.1' => 'assets/uploads/char.kedu.idle.1.webp'];
        $page    = $this->map(['temanggung' => 'open', 'magelang' => 'locked', 'wonosobo' => 'locked'], heard: []);
        $overlay = substr($page, (int) strpos($page, 'id="kenal-temanggung"'));
        $overlay = substr($overlay, 0, (int) strpos($overlay, '</section>'));
        $open    = substr($overlay, 0, (int) strpos($overlay, '>'));
        $head    = substr($overlay, (int) strpos($overlay, '<header class="kenal-head">'));
        $head    = substr($head, 0, (int) strpos($head, '</header>'));

        // Band: Back (no cross, on the navy plate) and the logo; the title is under the band, not in it
        $this->assertStringContainsString('class="btn btn-quiet kenal-back" href="' . esc('#region-temanggung', 'attr') . '" data-kenal-close', $head);
        $this->assertStringContainsString('<img class="kenal-logo" src="' . esc(base_url('assets/uploads/ui.logo-hero.webp?v=l1'), 'attr') . '"', $head);
        $this->assertStringNotContainsString('icon-btn kenal-close', $overlay);
        $this->assertStringNotContainsString('<h2', $head);
        $this->assertMatchesRegularExpression('/<\/header>\s*<div class="kenal-heading">\s*<h2 class="kenal-title" id="kenal-temanggung-title">' . preg_quote(esc(lang('Game.kenalTitle', ['Temanggung'])), '/') . '<\/h2>\s*<span class="kenal-rule" aria-hidden="true">\s*<svg class="kenal-ornament"/', $overlay);

        // The plates (shipped pictures) and the HUD skin reach the CSS as custom properties
        foreach (['kenal-plate-gold', 'kenal-plate-navy'] as $plate) {
            $this->assertStringContainsString(esc('--' . $plate . ': url("' . game_asset_url('ui/' . $plate . '.webp') . '")', 'attr'), $open);
            $this->assertFileExists(FCPATH . 'assets/ui/' . $plate . '.webp');
        }
        $this->assertStringContainsString(esc('--skin-hud: url("' . base_url('assets/uploads/ui.result-hud-control.webp?v=h1') . '")', 'attr'), $open);
        // The region's Kenali track while the overlay is open (game/map.js)
        $this->assertStringContainsString('data-music="kenal-temanggung"', $open);

        // No "Wilayah n · tingkat" and no "Bagian n dari N" text; the dots and Lanjut share the title row
        $this->assertStringNotContainsString('class="eyebrow"', $overlay);
        $this->assertStringNotContainsString('class="kenal-stage"', $overlay);
        $this->assertStringNotContainsString('cine-speaker', $overlay, 'the name is on the nameplate');
        $this->assertSame(4, preg_match_all('/<div class="cine-caption-head">\s*<h3 class="cine-title">[^<]*<\/h3>\s*(?:<!--[^>]*-->\s*)*<nav class="slide-nav"/', $overlay));

        // Mbah Kedu's round avatar with his name on the nameplate, never a pose frame
        $this->assertSame(4, preg_match_all('/<div class="kenal-avatar">\s*(?:<!--[^>]*-->\s*)*<span class="kedu-portrait has-art" aria-hidden="true">.*?<\/span>\s*(?:<!--[^>]*-->\s*)*<span class="kenal-nameplate">' . preg_quote(esc(lang('Game.char_mbah_kedu')), '/') . '<\/span>\s*<\/div>/s', $overlay));
        $this->assertStringContainsString('<img class="kedu-avatar" src="' . esc(base_url('assets/uploads/ui.kedu-avatar.webp?v=k1'), 'attr') . '"', $overlay);
        $this->assertStringNotContainsString('char.kedu.idle', $overlay);

        // Without the pictures: his initial in the gold disc, no HUD skin (the plates are shipped)
        Services::resetSingle('renderer');
        $this->media = [];
        $plain       = $this->map(['temanggung' => 'open', 'magelang' => 'locked', 'wonosobo' => 'locked'], heard: []);
        $this->assertStringContainsString('<span class="kedu-portrait-initial">M</span>', $plain);
        $this->assertStringContainsString('<span class="hud-brand kenal-logo">GELITA</span>', $plain);
        $this->assertStringNotContainsString('--skin-hud', $plain);

        $css = (string) file_get_contents(FCPATH . 'assets/css/cinematic.css');
        $this->assertStringContainsString('.kenal-avatar {', $css);
        $this->assertStringContainsString('margin-left: calc(var(--kenal-avatar) / 2);', $css, 'the bar starts under the avatar centre');
        $this->assertMatchesRegularExpression('/\.kenal-avatar \{[^}]*bottom: 0;/', $css, 'the avatar stands on the bar\'s bottom');
        $this->assertStringContainsString('border-image: var(--kenal-plate-gold) 0 250 fill / 0 4rem stretch;', $css);
        $this->assertStringContainsString('border-image: var(--kenal-plate-navy) 0 115 fill / 0 3.5rem stretch;', $css);
        $this->assertStringContainsString('.kenal[style*="--skin-hud"] .narrator-controls .icon-btn::after {', $css);
        $this->assertStringContainsString('.kenal.is-live .slide-nav > .btn-quiet { display: none; }', $css);
    }

    public function testUnheardBadgeFollowsServerEvents(): void
    {
        $page = $this->map(['temanggung' => 'open', 'magelang' => 'locked', 'wonosobo' => 'locked'], heard: [2]);

        $this->assertStringContainsString('class="kedu-btn is-secondary kenal-card-btn is-unheard" href="#kenal-temanggung"', $page);
        $this->assertStringContainsString('class="kedu-btn is-secondary kenal-card-btn" href="#kenal-magelang"', $page, 'Magelang sudah didengar');
        $this->assertSame(2, substr_count($page, esc(lang('Game.kenalUnheard'))), 'badge on the Kenali button of 2 regions');
        $this->assertSame(2, substr_count($page, 'class="kedu-unheard"'), 'and a dot on their pins');
    }

    public function testReachedLastSlideReadsOnlyRegionIntroEventsAtTheEnd(): void
    {
        $rows = [
            ['level_id' => 1, 'payload_json' => '{"index":2,"total":4,"context":"region_intro"}'],
            ['level_id' => 2, 'payload_json' => '{"index":4,"total":4,"context":"region_intro","character":"mbah_kedu"}'],
            ['level_id' => 3, 'payload_json' => '{"index":16,"total":16,"context":"level_open"}'],
            ['level_id' => 2, 'payload_json' => ['index' => 4, 'total' => 4, 'context' => 'region_intro']],
            ['level_id' => null, 'payload_json' => '{"index":4,"total":4,"context":"region_intro"}'],
            ['level_id' => 1, 'payload_json' => 'bukan json'],
        ];

        $this->assertSame([2], GameEventLogModel::reachedLastSlide($rows, 'region_intro'));
        $this->assertSame([3], GameEventLogModel::reachedLastSlide($rows, 'level_open'));
        $this->assertSame([], GameEventLogModel::reachedLastSlide([], 'region_intro'));
    }

    public function testPinsAndCardsPlayTheRegionCurtain(): void
    {
        $this->media = ['char.jaka.idle.1' => 'assets/char/jaka-idle-1.png'];

        $page = $this->map(['temanggung' => 'completed', 'magelang' => 'open', 'wonosobo' => 'locked'], heard: []);

        $this->assertStringContainsString('<template id="tpl-curtain-region">', $page);
        $this->assertMatchesRegularExpression('/<template id="tpl-curtain-region">.*?data-curtain-layer="region"\s+data-tap="0"\s+data-min-ms="1800" data-max-ms="2400" data-skip="0"/s', $page);
        $this->assertMatchesRegularExpression('/<template id="tpl-curtain-region">.*?class="footsteps curtain-trail"/s', $page);
        // Peta Kedu redesign: pins select a region; Lanjut in its panel enters with the curtain; locked does not
        $this->assertSame(1, substr_count($page, $this->href('dialog/magelang') . ' data-curtain="region"'));
        $this->assertSame(1, substr_count($page, $this->href('wilayah/temanggung') . ' data-curtain="region"'));
        $this->assertStringNotContainsString($this->href('wilayah/wonosobo') . ' data-curtain', $page);
        $this->assertStringContainsString($this->href('wilayah/wonosobo') . ' aria-disabled="true" data-locked-message="', $page, 'locked Lanjut only shows the toast');
        $this->assertMatchesRegularExpression('/<a class="kedu-city" href="#region-magelang" data-region-select="magelang"\s+aria-controls="region-magelang" aria-current="true"/', $page, 'the region to play is selected');
        // Tagline & chip dari naskah; aset tujuan dialog: frame tokoh
        $this->assertStringContainsString(esc(json_encode(['title' => 'Menuju Temanggung…', 'tagline' => 'Di Antara Dua Gunung', 'chip' => 'Mudah'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'attr'), $page);
        $this->assertStringContainsString(esc(json_encode([base_url('assets/char/jaka-idle-1.png')], JSON_UNESCAPED_SLASHES), 'attr'), $page);
    }

    /** Display plan Phase C: city pins and Kenali lanterns from the panel, with automatic status marks. */
    public function testMapUsesCityPinsAndLanternsWithFallbacks(): void
    {
        $status = ['temanggung' => 'completed', 'magelang' => 'open', 'wonosobo' => 'locked'];

        // Nothing uploaded: the built-in icons as before
        $plain = $this->map($status, heard: []);
        $this->assertStringNotContainsString('has-art', $plain);

        // Global pin and lantern for every city, Magelang with its own pin
        Services::resetSingle('renderer');
        $this->media = [
            'ui.pin'            => 'assets/uploads/ui.pin.png?v=p1',
            'map.pin.magelang'  => 'assets/uploads/map.pin.magelang.png?v=m1',
            'ui.lantern'        => 'assets/uploads/ui.lantern.png?v=l1',
            'map.lantern.wonosobo' => 'assets/uploads/map.lantern.wonosobo.png?v=w1',
        ];
        $page = $this->map($status, heard: []);

        $this->assertSame(3, substr_count($page, '<span class="kedu-medal has-art"'));
        $this->assertStringContainsString(esc(base_url('assets/uploads/map.pin.magelang.png?v=m1'), 'attr'), $page);
        $this->assertSame(2, substr_count($page, esc(base_url('assets/uploads/ui.pin.png?v=p1'), 'attr')), 'Temanggung and Wonosobo use the global pin');
        $this->assertSame(1, substr_count($page, 'class="kedu-lock"'), 'a locked region gets the lock badge');
        // Peta Kedu redesign: Kenali uses the book icon, so the city lanterns are no longer shown
        $this->assertStringNotContainsString('map.lantern.wonosobo', $page);
        $this->assertStringContainsString('<span class="flame flame-', $page);
        $this->assertMatchesRegularExpression('/<span class="flame flame-\d has-art"/', $page, 'HUD lantern uses ui.lantern');

        $this->assertSame('region', ImageSlots::groupOf('map.pin.magelang'));
        $this->assertSame('wilayah-wonosobo', AssetPackageBuilder::groupFor('bg.loading.wonosobo'));
        $this->assertSame('wilayah-magelang', AssetPackageBuilder::groupFor('map.lantern.magelang'));
        $this->assertSame('inti', AssetPackageBuilder::groupFor('ui.pin'));
    }

    /** Page redesign 2 (18 October 2026): diorama slices, trails, one panel, menu HUD, bottom row without Profil. */
    public function testMapIsTheThreeRegionDiorama(): void
    {
        $status = ['temanggung' => 'completed', 'magelang' => 'open', 'wonosobo' => 'locked'];

        // No diorama yet: the old map picture, pins at their map_x / map_y
        $plain = $this->map($status, heard: []);
        $this->assertStringNotContainsString('kedu-slice', $plain);
        $this->assertStringContainsString('<div class="kedu-art is-drawn">', $plain);
        $this->assertStringContainsString('<span class="kedu-portrait" aria-hidden="true"><span class="kedu-portrait-initial">J</span></span>', $plain, 'no avatar yet: his initial');

        Services::resetSingle('renderer');
        $this->media = ['map.kedu-diorama' => 'assets/uploads/map.kedu-diorama.webp?v=d1', 'ui.jaka-avatar' => 'assets/uploads/ui.jaka-avatar.webp?v=a1',
            'char.jaka.idle.1' => 'assets/uploads/char.jaka.idle.1.webp'];
        $page   = $this->map($status, heard: []);
        $shapes = config('Gelita')->mapDiorama;

        $this->assertSame(3, substr_count($page, 'class="kedu-slice'));
        $this->assertSame(1, substr_count($page, 'class="kedu-slice is-locked"'), 'locked Wonosobo is dimmed');
        $this->assertStringContainsString('style="clip-path: polygon(' . esc($shapes['magelang']['clip'], 'attr') . ')"', $page);
        $this->assertStringContainsString('<path class="kedu-route" d="' . esc($shapes['magelang']['route'], 'attr') . '"/>', $page, 'open trail is gold');
        $this->assertStringContainsString('<path class="kedu-route is-locked" d="' . esc($shapes['wonosobo']['route'], 'attr') . '"/>', $page);
        $this->assertStringContainsString('style="left: ' . $shapes['temanggung']['pin'][0] . '%; top: ' . $shapes['temanggung']['pin'][1] . '%"', $page);

        // One panel per region, the region to play selected; a city pin selects, it does not leave
        $this->assertSame(3, substr_count($page, '<section class="kedu-panel '));
        $this->assertStringContainsString('<section class="kedu-panel is-open is-selected"', $page);
        $this->assertStringContainsString('<a class="kedu-city" href="#region-wonosobo" data-region-select="wonosobo"', $page);

        // Owner, 18 October 2026: completed Temanggung wears a done badge, since 20 October 2026 the
        // owner's green medallion (assets/ui/status-done.webp); Kenali is the shipped picture
        $this->assertSame(1, substr_count($page, '<span class="kedu-lock kedu-done has-badge"><img src="' . esc(game_asset_url('ui/status-done.webp'), 'attr') . '"'));
        $this->assertSame(1, substr_count($page, 'class="kedu-lock"><svg'), 'locked Wonosobo keeps its lock');
        $this->assertStringContainsString('<img class="kenali-art" src="' . esc(base_url('assets/ui/icon-kenali.webp'), 'attr') . '"', $page);
        $this->assertFileExists(FCPATH . 'assets/ui/icon-kenali.webp');
        $this->assertContains('webp', \App\Services\AssetPackageBuilder::CODE_SOURCES['assets/ui']['ext'], 'offline packages carry it');

        // Owner, 18 October 2026: the story portrait is Jaka's avatar, never a pose frame; Mulai/Lanjut without the arrow
        $story = substr($page, (int) strpos($page, '<aside class="kedu-story'));
        $story = substr($story, 0, (int) strpos($story, '</aside>'));
        $this->assertStringContainsString('<img class="kedu-avatar" src="' . esc(base_url('assets/uploads/ui.jaka-avatar.webp?v=a1'), 'attr') . '"', $story);
        $this->assertStringNotContainsString('char.jaka.idle', $story);
        // Owner, 19 October 2026: the avatar brings its own gold ring, so the portrait draws none
        $this->assertMatchesRegularExpression('/\.kedu-portrait\.has-art \{[^}]*border: 0;[^}]*background: none;/', (string) file_get_contents(FCPATH . 'assets/css/game.css'));
        $this->assertMatchesRegularExpression('/<a class="kedu-btn is-primary" href="[^"]*"[^>]*>\s*<span>' . lang('Game.start') . '<\/span>\s*<\/a>/', $page);

        // Menu HUD with the title and the shard lantern; Profil is in the menu, not in the page buttons
        $this->assertStringContainsString('class="hud hud-menu-style" data-in-game', $page);
        $this->assertStringContainsString('<span class="hud-identity-region">' . lang('Game.mapKedu') . '</span>', $page);
        $this->assertMatchesRegularExpression('/<div class="hud-shards">(?:<!--[^>]*-->\s*)?<details class="lantern"/', $page);
        // Page buttons right of the story, under the region panel; no bottom nav bar
        $this->assertStringNotContainsString('class="nav-bar"', $page);
        $nav = substr($page, (int) strpos($page, '<nav class="kedu-nav stage-box"'));
        $this->assertStringContainsString($this->href('pustaka'), $nav);
        $this->assertStringNotContainsString(base_url('profil'), $nav);
        $this->assertStringNotContainsString(base_url('penutup'), $nav, 'the ending only once everything is done');
    }

    /** Display plan Phase C: the "Menuju …" curtain shows bg.loading.{wilayah}, else the region background. */
    public function testRegionCurtainCarriesTheCityBackground(): void
    {
        $status = ['temanggung' => 'completed', 'magelang' => 'open', 'wonosobo' => 'locked'];

        $plain = $this->map($status, heard: []);
        $this->assertStringNotContainsString('data-curtain-bg=', $plain, 'no picture: the gradient as before');
        $this->assertMatchesRegularExpression('/<template id="tpl-curtain-region">.*?<img class="curtain-bg is-deferred" alt="" hidden>/s', $plain);

        Services::resetSingle('renderer');
        $this->media = ['bg.loading.magelang' => 'assets/uploads/bg.loading.magelang.jpg?v=b1'];
        $page = $this->map($status, heard: []);
        $bg   = esc(base_url('assets/uploads/bg.loading.magelang.jpg?v=b1'), 'attr');

        $this->assertSame(2, substr_count($page, 'data-curtain-bg="' . $bg . '"'), 'Lanjut in the panel and the last Kenali slide of Magelang');
        $this->assertMatchesRegularExpression('/data-curtain-preload="' . preg_quote(esc(json_encode([base_url('assets/uploads/bg.loading.magelang.jpg?v=b1')], JSON_UNESCAPED_SLASHES), 'attr'), '/') . '/', $page, 'decoded first');
        $this->assertStringContainsString('data-preload="' . $bg . '"', $page, 'warmed up on the map');

        // Fallback: the region background resolved by levelOverview()
        $this->assertSame('/bg-region.jpg', region_loading_bg('wonosobo', '/bg-region.jpg'));
        $this->assertNull(region_loading_bg('wonosobo', ''));

        $js = (string) file_get_contents(FCPATH . 'assets/js/core/curtain.js');
        $this->assertStringContainsString('bg: link.dataset.curtainBg', $js);
    }

    /** Owner, 18 October 2026 (mockup "Wilayah dipilih"): the city's loading picture fills the panel; each status has its own icon. */
    public function testRegionPanelShowsTheCityPictureAndStatus(): void
    {
        $status = ['temanggung' => 'completed', 'magelang' => 'in_progress', 'wonosobo' => 'locked'];

        $plain = $this->map($status, heard: []);
        $this->assertStringNotContainsString('kedu-panel-art', $plain, 'no picture: the plain dark panel');

        Services::resetSingle('renderer');
        $this->media = ['bg.loading.magelang' => 'assets/uploads/bg.loading.magelang.jpg?v=b1'];
        $page = $this->map($status, heard: []);

        $this->assertMatchesRegularExpression('/<section class="kedu-panel is-in_progress is-selected has-art"[^>]*>\s*<img class="kedu-panel-art" src="' . preg_quote(esc(base_url('assets/uploads/bg.loading.magelang.jpg?v=b1'), 'attr'), '/') . '"/', $page);
        $this->assertStringContainsString('<p class="kedu-panel-eyebrow"><span>' . lang('Game.mapSelected') . '</span></p>', $page);
        foreach (['completed' => 'check', 'in_progress' => 'compass', 'locked' => 'lock'] as $state => $icon) {
            $this->assertMatchesRegularExpression('/<span class="kedu-panel-status is-' . $state . '">' . preg_quote(icon($icon), '/') . ' <span>/', $page, $state);
        }
        $this->assertStringContainsString('<span class="num">' . esc(lang('Game.nodesProgress', [5, 5])) . '</span>', $page);

        // Owner, 20 October 2026: no "Wilayah n · difficulty" line; "Lihat cerita pembuka" opens the
        // region's opening dialogue, except in a locked region
        $this->assertStringNotContainsString('kedu-panel-meta', $page);
        foreach (['temanggung' => true, 'magelang' => true, 'wonosobo' => false] as $code => $link) {
            $panel = substr($page, (int) strpos($page, 'id="region-' . $code . '"'));
            $panel = substr($panel, 0, (int) strpos($panel, '</section>'));
            $href  = '<a class="kedu-opening" href="' . esc(base_url('dialog/' . $code), 'attr') . '">';
            $link ? $this->assertStringContainsString($href, $panel, $code) : $this->assertStringNotContainsString('kedu-opening', $panel, $code);
        }
        $this->assertStringContainsString('<span>' . esc(lang('Game.watchRegionOpening')) . '</span>', $page);
    }

    public function testMapDecodesTheCurrentRegionAheadOfTime(): void
    {
        $this->media = ['char.jaka.idle.1' => 'assets/char/jaka-idle-1.png'];

        // Asset package Stage 4 (§4.5): the first unfinished region's entry assets, one screen ahead
        $page = $this->map(['temanggung' => 'completed', 'magelang' => 'open', 'wonosobo' => 'locked'], heard: []);
        $this->assertMatchesRegularExpression('/data-screen="map-kedu"[^>]*data-next-assets="' . preg_quote(esc(json_encode([base_url('assets/char/jaka-idle-1.png')], JSON_UNESCAPED_SLASHES), 'attr'), '/') . '"/', $page);

        // Nothing left to open: no next assets
        Services::resetSingle('renderer');
        $done = $this->map(['temanggung' => 'completed', 'magelang' => 'completed', 'wonosobo' => 'completed'], heard: [1, 2, 3], allDone: true);
        $this->assertStringNotContainsString('data-next-assets', $done);
    }

    public function testDoneSceneDecodesTheNextRegionAheadOfTime(): void
    {
        $this->media = ['char.jaka.idle.1' => 'assets/char/jaka-idle-1.png'];

        $page = $this->dialogue(['temanggung' => 'completed', 'magelang' => 'open', 'wonosobo' => 'locked'])->done('temanggung');
        $this->assertMatchesRegularExpression('/data-screen="region-done"[^>]*data-next-assets="[^"]*jaka-idle-1\.png/', $page);
    }

    public function testMapNavOffersTheEndingOnceEverythingIsDone(): void
    {
        $page = $this->map(['temanggung' => 'completed', 'magelang' => 'completed', 'wonosobo' => 'completed'], heard: [1, 2, 3], allDone: true);

        $this->assertStringContainsString('<nav class="kedu-nav stage-box is-all-done"', $page);
        $this->assertMatchesRegularExpression('/' . preg_quote($this->href('penutup'), '/') . '><svg[^>]*>.*?<\/svg> <span>' . preg_quote(lang('Game.watchEnding'), '/') . '/s', $page);
        $this->assertStringContainsString('<a class="kedu-btn is-primary" ' . $this->href('refleksi') . '><span>' . lang('Game.reflection') . '</span></a>', $page);

        Services::resetSingle('renderer');
        $this->assertStringNotContainsString(base_url('penutup'), $this->map(['temanggung' => 'completed'], heard: []));
    }

    // ------------------------------------------------------------ layar selesai

    public function testClosingAttemptContinuesToTheDoneScene(): void
    {
        $page = $this->finished(closes: true, allDone: false);

        $this->assertMatchesRegularExpression('/<a class="result-btn is-primary" href="' . preg_quote(base_url('tuntas/temanggung'), '/') . '"><span>' . preg_quote(lang('Game.continue'), '/') . '/', $page);
        $this->assertStringContainsString('class="library-unlocked"', $page, 'sorotan Pustaka Tahap 4 tetap');

        // Wilayah terakhir yang menuntaskan semua node pun lewat adegan tuntas dulu (lalu penutup)
        Services::resetSingle('renderer');
        $this->assertStringContainsString('href="' . base_url('tuntas/temanggung') . '"', $this->finished(closes: true, allDone: true));
    }

    public function testReplayInFinishedRegionOffersNextRegionWithCurtain(): void
    {
        $page = $this->finished(closes: false, allDone: false);

        $this->assertStringNotContainsString(base_url('tuntas/temanggung'), $page);
        $this->assertMatchesRegularExpression('/href="[^"]*wilayah\/magelang" data-curtain="region"[^>]*><span>' . preg_quote(lang('Game.goToRegion', ['Magelang']), '/') . '/', $page);
        $this->assertStringContainsString('<template id="tpl-curtain-region">', $page);
    }

    // ------------------------------------------------------------ tirai tantangan

    public function testChallengeCurtainIsShortSkippableAndPlaysBeforeTheAttemptOpens(): void
    {
        $html = component('curtain', ['kind' => 'challenge', 'engine' => 'cari', 'eyebrow' => 'Cari Objek', 'title' => 'Temukan Budaya', 'lead' => lang('Game.challengeLead_cari')]);

        $this->assertMatchesRegularExpression('/data-curtain-layer="challenge"\s+data-tap="0"\s+data-min-ms="1200" data-max-ms="1500" data-skip="1"\s+data-engine="cari"/', $html);
        $this->assertStringContainsString('class="cc-art cc-cari"', $html);
        $this->assertStringContainsString('Temukan Budaya', $html);
        $this->assertStringContainsString(esc(lang('Game.curtainSkip')), $html);

        foreach (config('Gelita')->engineTypes as $engine) {
            $this->assertStringContainsString('cc-' . $engine, component('partials/challenge-art', ['engine' => $engine]));
            $this->assertNotSame('Game.challengeLead_' . $engine, lang('Game.challengeLead_' . $engine));
        }

        // Kartu misi memutar tirai lalu berpindah ke /tantangan; attempt baru dibuka
        // saat halaman tantangan dirender (openNode() di play()), bukan selama tirai
        $brief = (string) file_get_contents(APPPATH . 'Views/game/mission-brief.php');
        $this->assertStringContainsString('<template id="tpl-curtain-challenge">', $brief);
        $this->assertStringContainsString("base_url('tantangan/' . \$level->code . '/' . \$sequence) ?>\"<?= curtain_attrs('challenge'", $brief);
        $this->assertStringContainsString("service('challengeService')->openNode(", (string) file_get_contents(APPPATH . 'Controllers/Game/ChallengeController.php'));
        $curtainJs = (string) file_get_contents(FCPATH . 'assets/js/core/curtain.js');
        $this->assertStringNotContainsString('apiRequest', $curtainJs, 'tirai tidak membuka attempt');
        $this->assertStringNotContainsString('/attempts', $curtainJs);
    }

    public function testRegionIntroNeedsNoJavaScriptAndHidesWithIt(): void
    {
        $css = (string) file_get_contents(FCPATH . 'assets/css/cinematic.css');

        $this->assertStringContainsString('.kenal:target, .kenal:has(.slide:target) { display: grid; }', $css);
        $this->assertStringContainsString('html.js .kenal { display: none; }', $css);
        $this->assertStringContainsString('html.js .kenal.is-open { display: grid;', $css);
        // Gerak dikurangi: jejak kaki dan visual engine tanpa animasi
        $this->assertMatchesRegularExpression('/@media \(prefers-reduced-motion: reduce\).*\.footstep,.*\.cc-puzzle i, \.cc-gap b, \.cc-card, \.cc-light/s', $css);
    }

    // ------------------------------------------------------------ bantuan

    public function media(): array
    {
        return $this->media;
    }

    /** Atribut href seperti ditulis view (esc 'attr' mengodekan `:`, `/`, `#`). */
    private function href(string $path): string
    {
        return 'href="' . esc(base_url($path), 'attr') . '"';
    }

    /** Data HUD tetap (hudData() asli membaca database). */
    public static function hud(bool $allDone = false): array
    {
        return [
            'locale'      => 'id',
            'participant' => ['id' => 5, 'username' => 'jaka', 'display_name' => 'Jaka', 'avatar_code' => null],
            'progress'    => ['completed_nodes' => $allDone ? 15 : 5, 'completed_levels' => 1, 'unlocked_level_sequence' => 2,
                'total_stars' => 0, 'shards' => $allDone ? 15 : 5, 'shards_total' => 15, 'current_level_id' => null, 'current_node_id' => null],
            'lantern' => [],
        ];
    }

    public static function level(string $code): ?Level
    {
        if (! isset(self::LEVELS[$code])) {
            return null;
        }

        $id    = self::LEVELS[$code];
        $level = new Level();
        $level->injectRawData([
            'id' => $id, 'code' => $code, 'sequence' => $id, 'name_id' => ucfirst($code), 'name_en' => ucfirst($code),
            'difficulty' => self::DIFFICULTY[$id], 'background_media_id' => null, 'map_media_id' => null,
        ]);

        return $level;
    }

    /** Potongan naskah (docs/naskah-cerita.md) secukupnya per konteks. */
    public static function lines(?int $levelId, string $context): array
    {
        $row = static fn (int $n, string $character, ?string $pose, ?string $effect, ?string $title, string $text): array => [
            'id' => $n, 'level_id' => $levelId, 'context_code' => $context, 'sequence' => $n,
            'character_code' => $character, 'pose' => $pose, 'effect' => $effect,
            'title_id' => $title, 'title_en' => $title, 'text_id' => $text, 'text_en' => $text,
            'audio_id_asset_id' => null, 'audio_en_asset_id' => null,
            'background_media_id' => self::$slideBackgrounds[$context . '.' . $n] ?? null, 'is_active' => 1,
        ];

        return match ($context) {
            'region_intro' => [
                $row(1, 'mbah_kedu', 'smile', $levelId === 3 ? 'fog' : null, self::TAGLINES[$levelId] ?? 'Wilayah', 'Inilah wilayahnya, Le.'),
                $row(2, 'mbah_kedu', 'idle', null, 'Kedua', 'Slide kedua.'),
                $row(3, 'mbah_kedu', 'idle', null, 'Ketiga', 'Slide ketiga.'),
                $row(4, 'mbah_kedu', 'smile', 'glow', 'Pesan Mbah', 'Bacalah dengan teliti.'),
            ],
            'level_open' => [
                $row(1, 'jaka', 'afraid', null, null, 'Kabutnya tebal sekali.'),
                $row(2, 'mbah_kedu', 'worried', 'fog', null, 'Hati-hati, Le.'),
                $row(3, 'jaka', 'happy', null, null, 'Aku siap!'),
            ],
            'level_done' => [
                $row(1, 'mbah_kedu', 'smile', 'fog-lift', null, 'Lihat, Le! Kabutnya tersibak.'),
                $row(2, 'jaka', 'happy', 'glow', null, 'Lima serpihan sudah kembali ke lentera!'),
            ],
            'ending' => [
                $row(1, 'narator', null, 'glow', 'Lentera Menyala Kembali', 'Di puncak bukit.'),
                $row(2, 'narator', null, 'fog-lift', 'Kabut Tersibak', 'Kabut Lupa tersibak.'),
                $row(3, 'mbah_kedu', 'smile', 'glow', 'Mbah Kedu Pulih', 'Badan Mbah terasa segar lagi.'),
                $row(4, 'jaka', 'bow', null, 'Janji Jaka', 'Aku akan terus membaca.'),
                $row(5, 'mbah_kedu', 'smile', null, 'Penjaga Lentera', 'Kamulah Penjaga Lentera.'),
            ],
            default => [],
        };
    }

    /**
     * Baris levelOverview() tiruan dari status wilayah.
     *
     * @param array<string, string> $status kode → status
     */
    public static function overview(array $status): array
    {
        $rows = [];

        foreach (self::LEVELS as $code => $id) {
            $state  = $status[$code] ?? 'locked';
            $rows[] = [
                'id' => $id, 'code' => $code, 'sequence' => $id, 'name' => ucfirst($code), 'difficulty' => self::DIFFICULTY[$id],
                'status' => $state, 'entry' => ($state === 'open' ? 'dialog/' : 'wilayah/') . $code, 'stars' => 0,
                'completed_nodes' => $state === 'completed' ? 5 : 0, 'total_nodes' => 5, 'map_x' => 20 * $id, 'map_y' => 40,
                'map_media' => base_url('assets/ui/placeholder.svg'), 'background' => null,
            ];
        }

        return $rows;
    }

    /** DialogueController dengan status wilayah, progres, dan HUD tiruan. */
    private function dialogue(array $status, bool $allDone = false): DialogueController
    {
        return new class ($status, $allDone) extends DialogueController {
            public function __construct(private array $status, private bool $allDone)
            {
            }

            protected function regionStatus(GameSession $session, Level $level): string
            {
                return $this->status[$level->code] ?? 'locked';
            }

            protected function levelUnlocked(GameSession $session, int $sequence): bool
            {
                return true;
            }

            protected function levelOverview(GameSession $session): array
            {
                return RegionStoryTest::overview($this->status);
            }

            protected function allNodesCompleted(GameSession $session): bool
            {
                return $this->allDone;
            }

            protected function hudData(): array
            {
                return RegionStoryTest::hud($this->allDone);
            }
        };
    }

    /** Peta Kedu lewat MapController::kedu() dengan status, lencana, dan HUD tiruan. */
    private function map(array $status, array $heard, bool $allDone = false): string
    {
        $map = new class ($status, $heard, $allDone) extends MapController {
            public function __construct(private array $status, private array $heardIds, private bool $allDone)
            {
            }

            protected function levelOverview(GameSession $session): array
            {
                return RegionStoryTest::overview($this->status);
            }

            protected function unlockMode(GameSession $session): string
            {
                return 'sequential';
            }

            protected function heardRegionIntros(): array
            {
                return $this->heardIds;
            }

            protected function hudData(): array
            {
                return RegionStoryTest::hud($this->allDone);
            }
        };

        $page = $map->kedu();
        $this->assertIsString($page);

        return $page;
    }

    private function finished(bool $closes, bool $allDone): string
    {
        $attempt = new ChallengeAttempt();
        $attempt->injectRawData(['id' => 105, 'session_id' => 9, 'challenge_node_id' => 15, 'status' => 'completed', 'stars' => 3,
            'duration_ms' => 50000, 'first_pass_correct' => 4, 'final_correct' => 4, 'scorable_items' => 4, 'check_count' => 1]);

        $node = new ChallengeNode();
        $node->injectRawData(['id' => 15, 'level_id' => 1, 'sequence' => 4, 'engine_type' => 'cari', 'title_id' => 'Cari Objek Temanggung']);

        $next = self::overview(['temanggung' => 'completed', 'magelang' => 'in_progress'])[1];
        $next['curtain'] = ['text' => ['title' => 'Menuju Magelang…', 'tagline' => 'Tanah Candi Agung', 'chip' => 'Sedang'], 'preload' => []];

        return view('game/challenge-finished', self::hud($allDone) + [
            'attempt'         => $attempt,
            'node'            => $node,
            'level'           => self::level('temanggung'),
            'levelSummary'      => ['stars' => 12, 'completed_nodes' => 4, 'total_nodes' => 4],
            'levelCompleted'  => true,
            'closesRegion'    => $closes,
            'libraryUnlocked' => $closes,
            'allCompleted'    => $allDone,
            'nextRegion'      => $next,
        ]);
    }
}
