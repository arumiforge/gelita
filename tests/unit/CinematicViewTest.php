<?php

use App\Controllers\Game\GateController;
use App\Entities\GameSession;
use App\Entities\Participant;
use App\Services\ContentRepository;
use App\Services\GameContext;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * Tahap 2: cerita pembuka sinematik, tirai "Membuka Peta Kedu", dan narasi
 * peta. Tanpa database: ContentRepository dan GameContext diganti tiruan,
 * peta dirender langsung dari view dengan data wilayah buatan.
 *
 * Aturan yang dijaga:
 * - layar bernarasi dibuka kartu "Ketuk untuk mulai" yang tersembunyi tanpa JS;
 * - tanpa JS tirai tidak pernah menutupi halaman (noscript.css + pengaman CSS);
 * - pemain baru tidak melihat "Lewati"; akhir cerita tetap ke /intro/selesai;
 * - pose, efek, dan judul naskah sampai ke markup.
 *
 * @internal
 */
final class CinematicViewTest extends CIUnitTestCase
{
    /** @var list<array<string, mixed>> */
    private array $dialogues = [];

    protected function setUp(): void
    {
        parent::setUp();
        helper(['url', 'gelita', 'content', 'ui']);

        $test = $this;

        Services::injectMock('contentRepository', new class ($test) extends ContentRepository {
            public function __construct(private CinematicViewTest $test)
            {
            }

            public function mediaKeyMap(): array
            {
                return [];
            }

            public function mediaMap(): array
            {
                return [];
            }

            public function dialogues(?int $levelId, string $context = 'level_open'): array
            {
                return $this->test->dialogues($context);
            }
        });

        $this->dialogues = [
            $this->line('intro', 1, 'narator', null, 'glow', 'Dataran yang Bercahaya', 'Dahulu kala, di antara Gunung Sindoro.'),
            $this->line('intro', 2, 'mbah_kedu', 'weak', 'dim', 'Penjaga yang Menua', 'Aku Mbah Kedu, penjaga lentera ini.'),
            $this->line('intro', 3, 'jaka', 'determined', null, 'Jaka, Pembawa Lentera', 'Biar aku yang pergi, Mbah!'),
            $this->line('map_intro', 1, 'jaka', 'idle', 'fog', 'Dataran Kedu', 'Inilah Dataran Kedu.'),
            $this->line('map_intro', 2, 'jaka', 'happy', 'glow', 'Kenali Dulu', 'Ayo, kita berangkat!'),
        ];

        session()->remove(['participant_id', 'game_session_id', 'welcome', 'curtain']);
        Services::resetSingle('renderer');
    }

    protected function tearDown(): void
    {
        Services::resetSingle('contentRepository');
        Services::resetSingle('gameContext');
        session()->remove(['participant_id', 'game_session_id', 'welcome', 'curtain']);
        parent::tearDown();
    }

    /** @return list<array<string, mixed>> */
    public function dialogues(string $context): array
    {
        return array_values(array_filter($this->dialogues, static fn (array $d): bool => $d['context_code'] === $context));
    }

    // ------------------------------------------------------------ /intro

    public function testIntroIsANarratedScreenWithTapToStart(): void
    {
        $page = $this->intro(introSeen: false);

        $this->assertStringContainsString('data-narrator data-context="intro" data-prefix="slide-" data-mode="tap"', $page);
        $this->assertMatchesRegularExpression('/class="narrator-tap" data-narrator-tap hidden/', $page, 'tersembunyi tanpa JS');
        $this->assertMatchesRegularExpression('/data-narrator-controls[^>]*hidden/', $page);
        $this->assertStringContainsString(esc(lang('Game.tapToStart')), $page);
        $this->assertStringContainsString('is-cinematic', $page);
    }

    public function testIntroCarriesPoseEffectAndTitle(): void
    {
        $page = $this->intro(introSeen: false);

        $this->assertStringContainsString('id="slide-1"', $page);
        $this->assertMatchesRegularExpression('/id="slide-1"[^>]*data-effect="glow"/', $page);
        $this->assertMatchesRegularExpression('/id="slide-2"[^>]*data-pose="weak"[^>]*data-effect="dim"/', $page);
        $this->assertStringContainsString('Penjaga yang Menua', $page);
        $this->assertStringContainsString('class="cine-caption" data-narrator-advance', $page);
        // Narator tidak tampil sebagai gambar tokoh
        $this->assertDoesNotMatchRegularExpression('/id="slide-1".*?class="cine-stage".*?id="slide-2"/s', $page);
        $this->assertStringContainsString('data-pose="determined"', $page);
    }

    public function testFirstTimePlayerHasNoSkipAndEndsAtFinish(): void
    {
        $page = $this->intro(introSeen: false);

        $this->assertStringNotContainsString(esc(lang('Game.skip')), $page);
        $this->assertStringContainsString('href="' . esc(base_url('intro/selesai'), 'attr') . '"', $page);
    }

    public function testReturningPlayerSkipsThroughTheGateForTheCurtain(): void
    {
        $page = $this->intro(introSeen: true);

        $this->assertStringContainsString(esc(lang('Game.skip')), $page);
        $this->assertStringContainsString(esc(base_url('gerbang/peta'), 'attr'), $page);
    }

    public function testWelcomeCardSurvivesAfterRegistration(): void
    {
        session()->setFlashdata('welcome', 'Selamat datang, jaka!');

        $page = $this->intro(introSeen: false);

        $this->assertStringContainsString('class="welcome-card"', $page);
        $this->assertStringContainsString('data-narrator-welcome', $page, 'JS memindahkannya ke kartu ketuk');
    }

    // ------------------------------------------------------------- tirai

    public function testCurtainComponentPreloadsAndWaitsForTap(): void
    {
        $html = component('curtain', ['kind' => 'map', 'preload' => ['/a.png', '/a.png', '', '/b.mp3']]);

        $this->assertStringContainsString('data-curtain-layer="map"', $html);
        $this->assertStringContainsString('data-tap="1"', $html);
        $this->assertStringContainsString(esc(json_encode(['/a.png', '/b.mp3'], JSON_UNESCAPED_SLASHES), 'attr'), $html, 'unik, tanpa kosong');
        $this->assertStringContainsString(esc(lang('Game.curtainMapTitle')), $html);
        $this->assertMatchesRegularExpression('/data-curtain-tap hidden/', $html);
        $this->assertStringContainsString('role="progressbar"', $html);

        // Tiga baris status bergilir ada di data-statuses
        foreach ([1, 2, 3] as $n) {
            $this->assertStringContainsString(lang('Game.curtainMapStatus' . $n), html_entity_decode($html));
        }
    }

    public function testCurtainNeverCoversThePageWithoutJavaScript(): void
    {
        $layout = (string) file_get_contents(APPPATH . 'Views/layouts/game.php');
        $css    = (string) file_get_contents(FCPATH . 'assets/css/cinematic.css');

        $this->assertMatchesRegularExpression('/<noscript><link rel="stylesheet" href="<\?= asset_url_versioned\(\'css\/noscript\.css\'\) \?>"><\/noscript>/', $layout);
        $this->assertStringContainsString('.curtain { display: none !important; }', (string) file_get_contents(FCPATH . 'assets/css/noscript.css'));
        // Pengaman: memudar sendiri ±10 detik bila modul JS gagal; curtain.js membatalkannya
        $this->assertStringContainsString('animation: curtain-failsafe .8s ease 10s forwards;', $css);
        $this->assertStringContainsString('.curtain.is-live { animation: none; }', $css);
        $this->assertStringContainsString("layer.classList.add('is-live')", (string) file_get_contents(FCPATH . 'assets/js/core/curtain.js'));
        // Tirai dirender di luar <main> (main dibuat inert saat tirai tampil)
        $this->assertLessThan(strpos($layout, '<main id="app"'), strpos($layout, "renderSection('overlay')"));
    }

    public function testLoadingSlotIsRegisteredAndSized(): void
    {
        $slots = array_column((new ReflectionClassConstant(App\Database\Seeds\MediaAssetSeeder::class, 'SLOTS'))->getValue(), 1, 0);

        $this->assertSame('assets/bg/bg-loading', $slots['bg.loading']);
        $this->assertSame([1920, 1080], (new App\Libraries\MediaStore())->requiredSize('bg.loading'));
        $this->assertNotNull(App\Libraries\MediaUsage::slotLabel('bg.loading'));
    }

    // -------------------------------------------------------------- peta

    public function testMapWithCurtainFlashStartsTheStoryExternally(): void
    {
        $page = $this->map(curtain: true);

        $this->assertStringContainsString('data-curtain-layer="map"', $page);
        $this->assertStringContainsString(esc(json_encode(['/assets/map/map-kedu.png'], JSON_UNESCAPED_SLASHES), 'attr'), $page);
        $this->assertStringContainsString('data-context="map_intro" data-prefix="peta-" data-keyboard="0"', $page);
        $this->assertStringContainsString('data-mode="external"', $page);
        $this->assertStringContainsString('id="peta-2"', $page);
        $this->assertStringNotContainsString(esc(lang('Game.mapLead')), $page, 'balon lama digantikan narasi');
        $this->assertLessThan(strpos($page, '<main id="app"'), strpos($page, 'data-curtain-layer'), 'tirai di luar <main>');
    }

    public function testMapWithoutFlashShowsStoryAsTextWithPlayButton(): void
    {
        $page = $this->map(curtain: false);

        // Tirai peta tidak tampil; tirai wilayah hanya ada sebagai <template> (Tahap 3)
        $this->assertStringNotContainsString('data-curtain-layer="map"', $page);
        $this->assertStringContainsString('<template id="tpl-curtain-region">', $page);
        $this->assertStringContainsString('data-mode="manual"', $page);
        $this->assertStringContainsString('data-narrator="toggle"', $page);
        $this->assertStringContainsString('Inilah Dataran Kedu.', $page);
    }

    public function testMapFallsBackToTheLeadBalloonWithoutStory(): void
    {
        $this->dialogues = [];

        $page = $this->map(curtain: false);

        $this->assertStringContainsString(esc(lang('Game.mapLead')), $page);
        $this->assertStringNotContainsString('data-narrator', $page);
    }

    public function testMapControllerReadsTheCurtainFlash(): void
    {
        $source = (string) file_get_contents(APPPATH . 'Controllers/Game/MapController.php');

        $this->assertStringContainsString("getFlashdata('curtain') === 'map'", $source);
        $this->assertStringContainsString("dialogues(null, 'map_intro')", $source);
    }

    // ----------------------------------------------------- telemetry audio

    public function testAutoplayIsItsOwnAudioAction(): void
    {
        $this->assertContains('autoplay', App\Models\AudioUsageEventModel::ACTIONS);
        $this->assertContains('audio_autoplay', config('Gelita')->eventTypes);

        $model = (string) file_get_contents(APPPATH . 'Models/AudioUsageEventModel.php');
        $this->assertStringContainsString('in_list[play,autoplay,pause,replay,complete]', $model);
        $this->assertStringContainsString("aue.action = 'autoplay'", $model, 'dihitung terpisah dari play');

        $service = (string) file_get_contents(APPPATH . 'Services/EventService.php');
        $this->assertStringContainsString("'autoplay' => 'audio_autoplay'", $service);

        $audio = (string) file_get_contents(FCPATH . 'assets/js/core/audio.js');
        $this->assertStringContainsString("return this.restart('autoplay');", $audio);
        $this->assertStringContainsString('if (!unlocked || !enabled', $audio, 'tidak berbunyi sebelum interaksi');
    }

    // ------------------------------------------------------------ bantuan

    private function intro(bool $introSeen): string
    {
        $session = new GameSession();
        $session->injectRawData(['id' => 9, 'participant_id' => 5, 'locale' => 'id', 'status' => 'active']);

        $participant = new Participant();
        $participant->injectRawData([
            'id' => 5, 'username' => 'jaka', 'display_name' => 'Jaka',
            'intro_seen_at' => $introSeen ? '2026-09-01 08:00:00' : null,
        ]);

        $context              = new GameContext();
        $context->session     = $session;
        $context->participant = $participant;
        Services::injectMock('gameContext', $context);

        return (new GateController())->intro();
    }

    private function map(bool $curtain): string
    {
        $levels = [];

        foreach (['temanggung' => 'open', 'magelang' => 'locked', 'wonosobo' => 'locked'] as $code => $status) {
            $levels[] = [
                'id' => count($levels) + 1, 'code' => $code, 'sequence' => count($levels) + 1, 'name' => ucfirst($code),
                'difficulty' => 'mudah', 'status' => $status, 'entry' => 'dialog/' . $code, 'score' => 0, 'stars' => 0,
                'completed_nodes' => 0, 'total_nodes' => 5, 'map_x' => '20', 'map_y' => '40',
                'map_media' => '', 'background' => null,
            ];
        }

        return view('game/map-kedu', [
            'locale'        => 'id',
            'progress'      => ['shards' => 0, 'shards_total' => 15, 'completed_nodes' => 0, 'completed_levels' => 0],
            'levels'        => $levels,
            'unlockMode'    => 'sequential',
            'story'         => $this->dialogues('map_intro'),
            'curtain'       => $curtain,
            'curtainAssets' => $curtain ? ['/assets/map/map-kedu.png'] : [],
        ]);
    }

    /** @return array<string, mixed> */
    private function line(string $context, int $sequence, string $character, ?string $pose, ?string $effect, string $title, string $text): array
    {
        return [
            'id' => $sequence, 'level_id' => null, 'context_code' => $context, 'sequence' => $sequence,
            'character_code' => $character, 'pose' => $pose, 'effect' => $effect,
            'title_id' => $title, 'title_en' => $title, 'text_id' => $text, 'text_en' => $text,
            'audio_id_asset_id' => null, 'audio_en_asset_id' => null, 'background_media_id' => null, 'is_active' => 1,
        ];
    }
}
