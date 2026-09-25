<?php

use App\Controllers\Game\GateController;
use App\Controllers\Game\HomeController;
use App\Controllers\Game\MapController;
use App\Database\Seeds\MediaAssetSeeder;
use App\Entities\GameSession;
use App\Entities\Participant;
use App\Libraries\MediaStore;
use App\Libraries\MediaUsage;
use App\Services\ContentRepository;
use App\Services\GameContext;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * Alur tombol Mulai dan cerita pembuka wajib.
 *
 * - `/mulai`: belum login → pilihan "Saya baru / Sudah punya akun";
 *   sudah login → `/gerbang`.
 * - `/gerbang`: belum pernah menonton cerita pembuka → `/intro`.
 * - `/peta`, `/dialog/{code}`, dan `/wilayah/{code}` tidak terbuka sebelum
 *   cerita pembuka ditonton, juga lewat URL yang diketik langsung atau
 *   redirect setelah login.
 * - "Lewati" di `/intro` hanya untuk yang sudah pernah menonton.
 * - Jalan ke peta dari gerbang membawa flash `curtain=map`.
 *
 * Tanpa database: GameContext dan ContentRepository diganti tiruan.
 *
 * @internal
 */
final class StartGateTest extends CIUnitTestCase
{
    /** asset_key → storage_path aktif yang dikembalikan tiruan ContentRepository */
    private array $media = [];

    protected function setUp(): void
    {
        parent::setUp();
        helper(['url', 'gelita', 'content', 'ui']);

        $test = $this;

        Services::injectMock('contentRepository', new class ($test) extends ContentRepository {
            public function __construct(private StartGateTest $test)
            {
            }

            public function mediaKeyMap(): array
            {
                return $this->test->media();
            }

            public function dialogues(?int $levelId, string $context = 'level_open'): array
            {
                return [];
            }
        });

        session()->remove(['participant_id', 'game_session_id', 'curtain']);

        // Renderer menyimpan data view antar-render; satu uji = satu halaman
        Services::resetSingle('renderer');
    }

    protected function tearDown(): void
    {
        service('language')->setLocale(config('App')->defaultLocale);
        Services::resetSingle('contentRepository');
        Services::resetSingle('gameContext');
        session()->remove(['participant_id', 'game_session_id', 'curtain']);
        parent::tearDown();
    }

    // ------------------------------------------------------------ /mulai

    public function testStartShowsAccountChoiceWhenLoggedOut(): void
    {
        $page = (new HomeController())->start();

        $this->assertIsString($page);
        $this->assertStringContainsString(base_url('persetujuan'), $page);
        $this->assertStringContainsString(base_url('masuk'), $page);
    }

    public function testStartSendsLoggedInPlayerToTheGate(): void
    {
        session()->set(['participant_id' => 5, 'game_session_id' => 9]);

        $page = (new HomeController())->start();

        $this->assertInstanceOf(RedirectResponse::class, $page);
        $this->assertStringEndsWith('/gerbang', $page->getHeaderLine('Location'));
    }

    public function testStartWithoutGameSessionStillShowsAccountChoice(): void
    {
        session()->set('participant_id', 5);

        $this->assertIsString((new HomeController())->start());
    }

    // ------------------------------------------------------------ /gerbang

    public function testGateSendsNewPlayerToTheIntro(): void
    {
        $this->play(introSeen: false);

        $gate = (new GateController())->index();

        $this->assertInstanceOf(RedirectResponse::class, $gate);
        $this->assertStringEndsWith('/intro', $gate->getHeaderLine('Location'));
    }

    public function testGateToMapCarriesTheCurtainFlash(): void
    {
        $this->play(introSeen: true);

        $redirect = (new GateController())->map();

        $this->assertStringEndsWith('/peta', $redirect->getHeaderLine('Location'));
        $this->assertSame('map', session('curtain'));
    }

    // ------------------------------------------------------------ /peta

    public function testMapRedirectsToIntroUntilItWasWatched(): void
    {
        $this->play(introSeen: false);

        $map = (new MapController())->kedu();

        $this->assertInstanceOf(RedirectResponse::class, $map);
        $this->assertStringEndsWith('/intro', $map->getHeaderLine('Location'));
    }

    /** Tahap 5 (audit): URL wilayah yang diketik langsung juga tidak melewati cerita pembuka. */
    public function testRegionUrlsRedirectToIntroUntilItWasWatched(): void
    {
        $this->play(introSeen: false);

        foreach ([
            'dialog'  => static fn () => (new App\Controllers\Game\DialogueController())->show('temanggung'),
            'wilayah' => static fn () => (new MapController())->level('temanggung'),
        ] as $path => $open) {
            $response = $open();

            $this->assertInstanceOf(RedirectResponse::class, $response, $path);
            $this->assertStringEndsWith('/intro', $response->getHeaderLine('Location'), $path);
        }
    }

    public function testIntroGateOpensAfterTheIntroWasWatched(): void
    {
        $this->play(introSeen: true);

        $probe = new class () extends GateController {
            public function gate(): ?RedirectResponse
            {
                return $this->introGate();
            }
        };

        $this->assertNull($probe->gate());
    }

    // ------------------------------------------------------------ /intro

    public function testNewPlayerCannotSkipTheIntro(): void
    {
        $this->play(introSeen: false);

        $page = (new GateController())->intro();

        $this->assertStringNotContainsString(esc(lang('Game.skip')), $page);
        $this->assertStringContainsString(base_url('intro/selesai'), $page, 'akhir cerita menandai sudah menonton');
    }

    public function testReturningPlayerMaySkipTheIntro(): void
    {
        $this->play(introSeen: true);

        $page = (new GateController())->intro();

        $this->assertStringContainsString(esc(lang('Game.skip')), $page);
        $this->assertStringContainsString(base_url('intro/selesai'), $page);
    }

    // ------------------------------------------------------------ /

    public function testWelcomeHasOneStartButtonAndNoBrand(): void
    {
        $page = (new HomeController())->index();

        $this->assertSame(1, substr_count($page, 'class="btn-start'), 'tepat satu tombol Mulai');
        $this->assertStringContainsString('href="' . base_url('mulai') . '"', $page);
        $this->assertStringNotContainsString('hud-brand', $page, 'merek HUD tidak diulang di halaman awal');
        $this->assertStringNotContainsString(base_url('admin/login'), $page, 'staf masuk lewat /admin/login langsung');
        $this->assertStringNotContainsString(base_url('masuk'), $page);
    }

    public function testStartButtonFallsBackToCssButton(): void
    {
        $page = (new HomeController())->index();

        $this->assertMatchesRegularExpression('/class="btn-start btn btn-primary btn-xl"[^>]*>' . preg_quote(lang('Game.start'), '/') . '/', $page);
    }

    public function testStartButtonImageHonoursLocaleVariant(): void
    {
        $this->media = ['ui.btn-start' => 'assets/ui/btn-start.png', 'ui.btn-start.en' => 'assets/ui/btn-start-en.png'];

        $id = (new HomeController())->index();
        $this->assertStringContainsString('src="' . $this->attr('assets/ui/btn-start.png') . '" alt="Mulai"', $id);

        Services::resetSingle('renderer');
        service('language')->setLocale('en');
        $en = $this->homeIn('en');
        $this->assertStringContainsString('src="' . $this->attr('assets/ui/btn-start-en.png') . '" alt="Start"', $en);

        // Varian en belum diunggah → varian Indonesia
        unset($this->media['ui.btn-start.en']);
        Services::resetSingle('renderer');
        $this->assertStringContainsString($this->attr('assets/ui/btn-start.png'), $this->homeIn('en'));
    }

    public function testLogoPrefersLandscapeThenSquareThenText(): void
    {
        $this->assertStringContainsString('class="welcome-title"', (new HomeController())->index());

        $this->media = ['ui.logo' => 'assets/ui/logo-gelita.png'];
        Services::resetSingle('renderer');
        $page = (new HomeController())->index();
        $this->assertStringContainsString($this->attr('assets/ui/logo-gelita.png'), $page);
        $this->assertStringContainsString('<p class="visually-hidden">' . esc(lang('Game.tagline')) . '</p>', $page);

        $this->media['ui.logo-hero'] = 'assets/ui/logo-hero.png';
        Services::resetSingle('renderer');
        $page = (new HomeController())->index();
        $this->assertStringContainsString($this->attr('assets/ui/logo-hero.png'), $page);
        $this->assertStringContainsString('welcome-logo is-hero', $page);
    }

    /** Admin dapat mengunggah kedua slot baru, termasuk varian bahasa tombol. */
    public function testNewSlotsAreRegisteredSizedAndLabelled(): void
    {
        $store = new MediaStore();

        $this->assertSame([1600, 600], $store->requiredSize('ui.logo-hero'));
        $this->assertSame([720, 240], $store->requiredSize('ui.btn-start'));
        $this->assertSame([720, 240], $store->requiredSize('ui.btn-start.en'));

        $this->assertNull(MediaStore::localeFromKey('ui.btn-start'));
        $this->assertSame('en', MediaStore::localeFromKey('ui.btn-start.en'));

        foreach (['ui.logo-hero', 'ui.btn-start', 'ui.btn-start.en'] as $key) {
            $this->assertNotNull(MediaUsage::slotLabel($key), $key);
        }

        $slots = array_column((new ReflectionClassConstant(MediaAssetSeeder::class, 'SLOTS'))->getValue(), 1, 0);
        $this->assertSame('assets/ui/logo-hero', $slots['ui.logo-hero']);
        $this->assertSame('assets/ui/btn-start', $slots['ui.btn-start']);
        $this->assertSame('assets/ui/btn-start-en', $slots['ui.btn-start.en']);
    }

    public function testStartPageKeepsTheHudBrand(): void
    {
        $this->assertStringContainsString('hud-brand', (new HomeController())->start());
    }

    // ------------------------------------------------------------ bantuan

    public function media(): array
    {
        return $this->media;
    }

    /** URL aset seperti tertulis di atribut src (esc 'attr'). */
    private function attr(string $path): string
    {
        return esc(base_url($path), 'attr');
    }

    /** Halaman awal dengan locale request $locale (BaseController membacanya di initController). */
    private function homeIn(string $locale): string
    {
        $home = new HomeController();
        (new ReflectionProperty(HomeController::class, 'locale'))->setValue($home, $locale);

        return $home->index();
    }

    private function play(bool $introSeen): void
    {
        $session = new GameSession();
        $session->injectRawData(['id' => 9, 'participant_id' => 5, 'locale' => 'id', 'status' => 'active']);

        $participant = new Participant();
        $participant->injectRawData([
            'id'            => 5,
            'username'      => 'jaka',
            'display_name'  => 'Jaka',
            'intro_seen_at' => $introSeen ? '2026-09-01 08:00:00' : null,
        ]);

        $context              = new GameContext();
        $context->session     = $session;
        $context->participant = $participant;

        Services::injectMock('gameContext', $context);
        session()->set(['participant_id' => 5, 'game_session_id' => 9]);
    }
}
