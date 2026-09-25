<?php

use App\Controllers\Game\DialogueController;
use App\Controllers\Game\MapController;
use App\Entities\ChallengeAttempt;
use App\Entities\ChallengeNode;
use App\Entities\GameSession;
use App\Entities\Level;
use App\Entities\LibraryPage;
use App\Entities\Participant;
use App\Models\GameEventLogModel;
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
                return [];
            }
        });

        $session = new GameSession();
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
        $this->assertStringContainsString('data-narrator data-context="ending" data-prefix="slide-" data-mode="tap"', $page);
        $this->assertMatchesRegularExpression('/class="narrator-tap" data-narrator-tap hidden/', $page);
        $this->assertStringContainsString('Lentera Menyala Kembali', $page);
        $this->assertMatchesRegularExpression('/id="slide-4"[^>]*data-character="jaka" data-pose="bow"/', $page);
        $this->assertMatchesRegularExpression('/id="slide-5".*?' . preg_quote($this->href('refleksi'), '/') . ' data-slide-final/s', $page);
    }

    // ------------------------------------------------------------ /dialog/{code}

    public function testDialogueIsNarratedWithChapterCardAndPoseFrames(): void
    {
        $this->media = ['char.jaka.afraid.1' => 'assets/char/jaka-afraid-1.png', 'char.kedu.idle.1' => 'assets/char/kedu-idle-1.png'];

        $page = $this->dialogue(['magelang' => 'open'])->show('magelang');

        $this->assertStringContainsString('data-screen="dialogue"', $page);
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

        // Panggung dua tokoh, akhir ke peta wilayah, nav Lewati tetap
        $this->assertStringContainsString('character-jaka stage-left', $page);
        $this->assertStringContainsString('character-mbah_kedu stage-right', $page);
        $this->assertStringContainsString(esc(lang('Game.enterRegionName', ['Magelang'])), $page);
        $this->assertStringContainsString('href="' . esc(base_url('wilayah/magelang'), 'attr') . '" data-slide-final', $page);
        $this->assertStringContainsString('<span>' . esc(lang('Game.skip')) . '</span>', $page);
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
            // Tombol lentera pada pin dan kartu, keduanya tautan :target tanpa JavaScript
            $this->assertSame(2, substr_count($page, 'href="#kenal-' . $code . '"'), "tombol Kenali {$code}");
        }

        $this->assertStringContainsString(esc(lang('Game.kenalRegion', ['Wonosobo'])), $page);
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

    public function testUnheardBadgeFollowsServerEvents(): void
    {
        $page = $this->map(['temanggung' => 'open', 'magelang' => 'locked', 'wonosobo' => 'locked'], heard: [2]);

        $this->assertStringContainsString('class="kenal-btn is-unheard" href="#kenal-temanggung"', $page);
        $this->assertStringContainsString('class="kenal-btn" href="#kenal-magelang"', $page, 'Magelang sudah didengar');
        $this->assertSame(2, substr_count($page, esc(lang('Game.kenalUnheard'))) / 2, 'lencana pin + kartu untuk 2 wilayah');
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
        // Pin + kartu wilayah terbuka bertirai; terkunci tidak
        $this->assertSame(2, substr_count($page, $this->href('dialog/magelang') . ' data-curtain="region"'));
        $this->assertSame(2, substr_count($page, $this->href('wilayah/temanggung') . ' data-curtain="region"'));
        $this->assertStringNotContainsString($this->href('wilayah/wonosobo') . ' data-curtain', $page);
        // Tagline & chip dari naskah; aset tujuan dialog: frame tokoh
        $this->assertStringContainsString(esc(json_encode(['title' => 'Menuju Temanggung…', 'tagline' => 'Di Antara Dua Gunung', 'chip' => 'Mudah'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'attr'), $page);
        $this->assertStringContainsString(esc(json_encode([base_url('assets/char/jaka-idle-1.png')], JSON_UNESCAPED_SLASHES), 'attr'), $page);
    }

    public function testMapNavOffersTheEndingOnceEverythingIsDone(): void
    {
        $page = $this->map(['temanggung' => 'completed', 'magelang' => 'completed', 'wonosobo' => 'completed'], heard: [1, 2, 3], allDone: true);

        $this->assertMatchesRegularExpression('/' . preg_quote($this->href('penutup'), '/') . '><svg[^>]*>.*?<\/svg><span>' . preg_quote(lang('Game.watchEnding'), '/') . '/s', $page);
        $this->assertStringContainsString($this->href('refleksi'), $page);

        Services::resetSingle('renderer');
        $this->assertStringNotContainsString(base_url('penutup'), $this->map(['temanggung' => 'completed'], heard: []));
    }

    // ------------------------------------------------------------ layar selesai

    public function testClosingAttemptContinuesToTheDoneScene(): void
    {
        $page = $this->finished(closes: true, allDone: false);

        $this->assertMatchesRegularExpression('/class="finished-actions">\s*<a class="btn btn-primary btn-xl" href="' . preg_quote(base_url('tuntas/temanggung'), '/') . '">' . preg_quote(lang('Game.continue'), '/') . '/', $page);
        $this->assertStringContainsString('class="library-unlocked"', $page, 'sorotan Pustaka Tahap 4 tetap');

        // Wilayah terakhir yang menuntaskan semua node pun lewat adegan tuntas dulu (lalu penutup)
        Services::resetSingle('renderer');
        $this->assertStringContainsString('href="' . base_url('tuntas/temanggung') . '"', $this->finished(closes: true, allDone: true));
    }

    public function testReplayInFinishedRegionOffersNextRegionWithCurtain(): void
    {
        $page = $this->finished(closes: false, allDone: false);

        $this->assertStringNotContainsString(base_url('tuntas/temanggung'), $page);
        $this->assertMatchesRegularExpression('/href="[^"]*wilayah\/magelang" data-curtain="region"[^>]*>' . preg_quote(lang('Game.goToRegion', ['Magelang']), '/') . '/', $page);
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
        $this->assertMatchesRegularExpression('/@media \(prefers-reduced-motion: reduce\).*\.footstep,.*\.cc-puzzle i, \.cc-gap b, \.cc-card, \.cc-pilihan i, \.cc-light/s', $css);
    }

    public function testKenaliHashOpensTheOverlayOnceTheMapIsReady(): void
    {
        $map = (string) file_get_contents(FCPATH . 'assets/js/game/map.js');

        // Ketukan Kenali sebelum modul siap hanya mengubah hash #kenal-{code}[-n]:
        // overlay dibuka saat init, lalu hash dibersihkan agar :target tidak tertinggal
        $this->assertStringContainsString('window.location.hash.slice(1)', $map);
        $this->assertStringContainsString('`^kenal-${code}(?:-(', $map);
        $this->assertStringContainsString('window.history.replaceState(', $map);
        $this->assertStringContainsString('gesture: false }', $map);
        // Tanpa ketukan di halaman ini narasi tidak berbunyi sendiri: teks + tombol ▶
        $this->assertStringContainsString('if (gesture) Sfx.unlock();', $map);
        $this->assertStringContainsString('userInitiated: gesture || Sfx.isUnlocked()', $map);
        // Narasi peta tidak dimulai di balik overlay yang sudah terbuka
        $this->assertStringContainsString('if (!introOpen()) story?.start();', $map);
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
            'progress'    => ['completed_nodes' => $allDone ? 15 : 5, 'completed_levels' => 1, 'unlocked_level_sequence' => 2, 'total_score' => 0.0,
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
            'audio_id_asset_id' => null, 'audio_en_asset_id' => null, 'background_media_id' => null, 'is_active' => 1,
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
                'status' => $state, 'entry' => ($state === 'open' ? 'dialog/' : 'wilayah/') . $code, 'score' => 0.0, 'stars' => 0,
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
            'duration_ms' => 50000, 'first_pass_accuracy' => 100, 'score' => 90, 'check_count' => 1]);

        $node = new ChallengeNode();
        $node->injectRawData(['id' => 15, 'level_id' => 1, 'sequence' => 5, 'engine_type' => 'pilihan', 'title_id' => 'Kuis Temanggung']);

        $next = self::overview(['temanggung' => 'completed', 'magelang' => 'in_progress'])[1];
        $next['curtain'] = ['text' => ['title' => 'Menuju Magelang…', 'tagline' => 'Tanah Candi Agung', 'chip' => 'Sedang'], 'preload' => []];

        return view('game/challenge-finished', self::hud($allDone) + [
            'attempt'         => $attempt,
            'node'            => $node,
            'level'           => self::level('temanggung'),
            'levelScore'      => ['score' => 90.0, 'stars' => 15, 'completed_nodes' => 5, 'total_nodes' => 5],
            'levelCompleted'  => true,
            'closesRegion'    => $closes,
            'libraryUnlocked' => $closes,
            'allCompleted'    => $allDone,
            'nextRegion'      => $next,
        ]);
    }
}
