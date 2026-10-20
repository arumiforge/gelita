<?php

use App\Controllers\Game\GateController;
use App\Controllers\Game\HomeController;
use App\Controllers\Game\LoginController;
use App\Controllers\Game\MapController;
use App\Database\Seeds\MediaAssetSeeder;
use App\Entities\GameSession;
use App\Entities\Participant;
use App\Libraries\MediaStore;
use App\Libraries\MediaUsage;
use App\Models\ParticipantModel;
use App\Services\ContentRepository;
use App\Services\GameContext;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Config\Factories;
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
        service('request')->setLocale(config('App')->defaultLocale);
        Factories::reset('models');
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
        $this->assertStringContainsString(base_url('daftar'), $page);
        $this->assertStringNotContainsString(base_url('persetujuan'), $page, 'halaman persetujuan wali sudah dihapus');
        $this->assertStringContainsString(base_url('masuk'), $page);
    }

    public function testStartPutsBackInTheHudAndHasNoNavBar(): void
    {
        $page = (new HomeController())->start();

        $this->assertStringContainsString('class="btn btn-quiet hud-back" href="' . base_url() . '"', html_entity_decode($page));
        $this->assertStringContainsString('<header class="hud hud-menu-style is-compact">', $page);
        $this->assertStringNotContainsString('class="nav-bar"', $page);
        $this->assertStringContainsString('start-jaka', $page, 'Jaka is his own image, not part of the background');
        $this->assertSame(1, substr_count($page, 'start-card is-primary'));
    }

    public function testLoginKeepsTheOrnatePanelWithBackInTheHud(): void
    {
        $page = (new LoginController())->form();

        $this->assertIsString($page);
        $this->assertStringContainsString('class="btn btn-quiet hud-back" href="' . base_url('mulai') . '"', html_entity_decode($page));
        $this->assertStringNotContainsString('class="nav-bar"', $page);
        $this->assertStringNotContainsString('start-jaka', $page, 'no animated figure (owner redesign, 18 October 2026)');
        $this->assertStringContainsString('class="auth-jaka stage-box" src="' . esc(asset_url_versioned('ui/jaka-wave.webp'), 'attr') . '"', $page, 'one still picture of Jaka');
        $this->assertStringContainsString('login-panel ornate-panel stage-box', $page);
        $this->assertStringContainsString('ornate-body stage-scroll', $page, 'the form scrolls inside the fixed panel');
    }

    /** Owner redesign (18 October 2026): the shipped lantern-tree picture, whatever bg.auth or bg.welcome hold. */
    public function testLoginUsesTheShippedPicture(): void
    {
        $this->media = ['bg.welcome' => 'assets/uploads/bg.welcome.jpg?v=w1', 'bg.auth' => 'assets/uploads/bg.auth.jpg?v=a1'];
        $page        = (new LoginController())->form();

        $this->assertStringContainsString('src="' . esc(asset_url_versioned('ui/bg-auth-login.webp'), 'attr') . '"', $page);
        $this->assertStringContainsString('is-auth-art', $page);
        $this->assertStringNotContainsString('bg.auth.jpg', $page);
        $this->assertStringNotContainsString('bg.welcome.jpg', $page);
        $this->assertFileExists(FCPATH . 'assets/ui/bg-auth-login.webp');
        $this->assertFileExists(FCPATH . 'assets/ui/bg-auth-register.webp');
        $this->assertFileExists(FCPATH . 'assets/ui/jaka-wave.webp');
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
        // Owner decision 12 October 2026: one thin staff link (welcome-staff), no other admin entry
        $this->assertSame(1, substr_count($page, base_url('admin/login')), 'only the thin staff link');
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

    /** The HUD language popover (every page since 18 October 2026): flag icons, or the language code while one is missing. */
    public function testLanguageSwitchUsesIconsOnlyWhenBothExist(): void
    {
        $text = (new HomeController())->index();
        $this->assertStringContainsString('<form class="lang-switch hud-lang-list" method="post"', $text);
        $this->assertMatchesRegularExpression('/<summary class="hud-ctl" aria-label="' . preg_quote(esc(lang('Game.language') . ': Bahasa Indonesia', 'attr'), '/') . '"[^>]*>\s*<span class="hud-ctl-code" aria-hidden="true">ID<\/span>/', $text);
        $this->assertMatchesRegularExpression('/value="id" lang="id"\s+class="hud-sheet-row hud-lang-row is-active" aria-pressed="true">\s*<span class="hud-sheet-code" aria-hidden="true">ID<\/span>/', $text);

        $this->media = ['ui.lang.id' => 'assets/ui/lang-id.webp', 'ui.lang.en' => 'assets/ui/lang-en.webp'];
        Services::resetSingle('renderer');
        $page = (new HomeController())->index();
        $this->assertStringContainsString('<img class="hud-ctl-flag" src="' . $this->attr('assets/ui/lang-id.webp') . '" alt=""', $page, 'the current language on the control');
        $this->assertStringContainsString('<img class="hud-sheet-flag" src="' . $this->attr('assets/ui/lang-en.webp') . '" alt=""', $page);
        $this->assertMatchesRegularExpression('/value="en" lang="en"\s+class="hud-sheet-row hud-lang-row" aria-pressed="false">/', $page);
        $this->assertStringContainsString('<span class="hud-sheet-label">English</span>', $page);

        $store = new MediaStore();
        $this->assertSame([256, 256], $store->requiredSize('ui.lang.id'));
        $this->assertSame([256, 256], $store->requiredSize('ui.lang.en'));
        $slots = array_column((new ReflectionClassConstant(MediaAssetSeeder::class, 'SLOTS'))->getValue(), 1, 0);
        $this->assertSame('assets/ui/lang-en', $slots['ui.lang.en']);
        $this->assertNotNull(MediaUsage::slotLabel('ui.lang.id'));
    }

    /** Owner, 20 October 2026: a signed-in student finds Profil and Keluar in the start page's menu. */
    public function testWelcomeMenuHasProfileAndLogoutWhenSignedIn(): void
    {
        $out = (new HomeController())->index();
        $this->assertStringNotContainsString('href="' . base_url('profil') . '"', $out, 'before login: no profile');
        $this->assertStringNotContainsString('href="' . base_url('keluar') . '"', $out, 'before login: no logout');

        Factories::injectMock('models', ParticipantModel::class, new class () extends ParticipantModel {
            public function __construct()
            {
            }

            public function find($id = null)
            {
                $participant = new Participant();
                $participant->injectRawData(['id' => $id, 'username' => 'jaka', 'display_name' => 'Jaka', 'avatar_no' => null]);

                return $participant;
            }
        });
        session()->set('participant_id', 5);
        Services::resetSingle('renderer');
        $in = (new HomeController())->index();

        $this->assertStringContainsString('class="hud-sheet-row hud-leave hud-profile" href="' . base_url('profil') . '"', $in);
        $this->assertStringContainsString('@jaka', $in);
        $this->assertStringContainsString('href="' . base_url('keluar') . '"', $in);
        $this->assertStringNotContainsString(esc(lang('Game.backHome')), $in, 'no Kembali ke awal on the start page itself');
        $this->assertSame(1, substr_count($in, 'class="btn-start'), 'still one Mulai button');
    }

    /** Owner, 20 October 2026: Masuk is the lettered lantern plate, MASUK or LOG IN by language. */
    public function testLoginButtonIsTheLetteredPicture(): void
    {
        $id = view('game/login', ['errors' => []]);
        $this->assertStringContainsString('<button class="btn-art" type="submit"><img src="' . esc(asset_url_versioned('ui/btn-login.webp'), 'attr') . '" alt="' . esc(lang('Game.login'), 'attr') . '"', $id);
        $this->assertStringNotContainsString('btn-block', $id);

        Services::resetSingle('renderer');
        service('request')->setLocale('en');
        $this->assertStringContainsString(esc(asset_url_versioned('ui/btn-login-en.webp'), 'attr'), view('game/login', ['errors' => []]));
        $this->assertFileExists(FCPATH . 'assets/ui/btn-login-en.webp');
    }

    /** A thin staff login link sits on the welcome page only (owner, 12 October 2026). */
    public function testStaffLinkOnlyOnTheWelcomePage(): void
    {
        $this->assertMatchesRegularExpression('/<a class="welcome-staff" href="[^"]*admin\/login">.*' . preg_quote(lang('Game.staffLogin'), '/') . '<\/a>/s', (new HomeController())->index());

        Services::resetSingle('renderer');
        $this->assertStringNotContainsString('welcome-staff', (new HomeController())->start());
    }

    /** The modal icon sits outside the scrolling card, so it can rise above it without being clipped. */
    public function testModalIconIsOutsideTheCard(): void
    {
        $html = view('components/modal');

        $this->assertMatchesRegularExpression('/<div class="modal"[^>]*data-type="info">\s*(?:<\?php.*?\?>\s*)?<div class="modal-icon"[^>]*>.*?<\/div>\s*<div class="modal-card/s', $html);
    }

    public function testLogoPrefersLandscapeThenSquareThenText(): void
    {
        $this->assertStringContainsString('class="welcome-title"', (new HomeController())->index());

        $this->media = ['ui.logo' => 'assets/ui/logo-gelita.png'];
        Services::resetSingle('renderer');
        $page = (new HomeController())->index();
        $this->assertStringContainsString($this->attr('assets/ui/logo-gelita.png'), $page);
        $this->assertStringContainsString('<p class="welcome-tagline">' . esc(lang('Game.tagline')) . '</p>', $page);

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
        $this->assertSame([720, 360], $store->requiredSize('ui.btn-start'));
        $this->assertSame([720, 360], $store->requiredSize('ui.btn-start.en'));

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
