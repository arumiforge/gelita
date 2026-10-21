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

        $this->assertStringContainsString('class="hud-back-plate" href="' . base_url() . '"', html_entity_decode($page));
        $this->assertStringContainsString('<header class="hud hud-menu-style is-compact is-logo-centre">', $page);
        $this->assertStringNotContainsString('hud-end', $page, 'Back in the top-left corner, not beside the controls');
        $this->assertStringNotContainsString('btn-quiet hud-back', $page);
        $this->assertStringNotContainsString('class="nav-bar"', $page);
        $this->assertSame(1, substr_count($page, 'start-card is-primary'));
    }

    /** Owner redesign (20 October 2026): the form stands on the picture, with no panel fill, frame, ornaments, or crest. */
    public function testLoginStandsOnThePictureWithBackInTheHud(): void
    {
        $page = (new LoginController())->form();

        $this->assertIsString($page);
        $this->assertStringContainsString('class="hud-back-plate" href="' . base_url('mulai') . '"', html_entity_decode($page));
        $this->assertStringNotContainsString('class="nav-bar"', $page);
        $this->assertStringNotContainsString('start-jaka', $page, 'no animated figure (owner redesign, 18 October 2026)');
        $this->assertStringContainsString('class="auth-jaka stage-box" src="' . esc(asset_url_versioned('ui/jaka-wave.webp'), 'attr') . '"', $page, 'one still picture of Jaka');
        $this->assertStringContainsString('class="login-panel stage-box stage-scroll"', $page, 'the form scrolls inside its fixed box');
        $this->assertStringNotContainsString('ornate-panel', $page, 'no panel fill or frame');
        $this->assertStringNotContainsString('ornate-corner', $page, 'no corner ornaments');
        $this->assertStringNotContainsString('ornate-crest', $page, 'no compass crest');
        $this->assertStringContainsString('class="input-lead"', $page);
        $this->assertStringContainsString('class="password-input input-lead"', $page, 'the show/hide button keeps its wrapper');
    }

    /**
     * Owner, 20 October 2026: on the logo-centre pages Back is the navy plate (the picture of
     * Kenali's Kembali) in the top-left corner; elsewhere it stays beside the controls.
     */
    public function testBackPlateUsesTheNavyPlatePicture(): void
    {
        $page = (new HomeController())->start();

        $this->assertStringContainsString('class="hud-back-plate" href="' . esc(base_url(), 'attr') . '" style="--plate-navy: url(&quot;' . esc(asset_url_versioned('ui/kenal-plate-navy.webp'), 'attr') . '&quot;)"', $page);
        $this->assertLessThan(strpos($page, 'hud-controls'), strpos($page, 'hud-back-plate'), 'Back before the controls, on the left');
        $this->assertFileExists(FCPATH . 'assets/ui/kenal-plate-navy.webp');

        $other = view('game/package-inspect');

        $this->assertStringContainsString('<a class="hud-ctl hud-back" href=', $other, 'pages without logo-centre keep Back beside the controls, as the round back button');
        $this->assertStringContainsString('<div class="hud-end">', $other);
        $this->assertStringNotContainsString('hud-back-plate', $other);
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

    /**
     * Owner redesign (20 October 2026): the arch picture, Jaka as one tall still picture, the
     * cards without a panel or corner ornaments, the owner's emblems as card icons; in the HUD the
     * logo instead of the shard plate, Back in the top-right corner instead of the nav bar.
     */
    public function testGateShowsTheOwnersPictureAndEmblems(): void
    {
        $page = view('game/start-choice', [
            'name'        => 'Jaka',
            'participant' => ['id' => 5, 'username' => 'jaka', 'display_name' => 'Jaka'],
            'progress'    => ['shards_total' => 0],
        ]);

        $this->assertStringContainsString('src="' . esc(asset_url_versioned('ui/bg-gate.webp'), 'attr') . '"', $page);
        $this->assertStringContainsString('is-gate-art', $page);
        $this->assertStringContainsString('class="gate-jaka stage-box" src="' . esc(asset_url_versioned('ui/jaka-gate.webp'), 'attr') . '"', $page);
        $this->assertStringNotContainsString('data-character="jaka"', $page, 'no animation frames');
        $this->assertStringNotContainsString('ornate-panel', $page, 'no panel fill');
        $this->assertStringNotContainsString('ornate-corner', $page, 'no corner ornaments');
        $this->assertStringContainsString(esc(asset_url_versioned('ui/gate-icon-intro.webp'), 'attr'), $page);
        $this->assertStringContainsString(esc(asset_url_versioned('ui/gate-icon-map.webp'), 'attr'), $page);
        $this->assertStringNotContainsString('start-icon', $page);

        $this->assertStringContainsString('<header class="hud hud-menu-style is-compact is-logo-centre" data-in-game>', $page);
        $this->assertStringNotContainsString('hud-shards', $page, 'the logo replaces the shard plate');
        $this->assertStringContainsString('class="hud-back-plate" href="' . base_url() . '"', html_entity_decode($page));
        $this->assertStringNotContainsString('class="nav-bar"', $page);

        foreach (['bg-gate', 'jaka-gate', 'gate-icon-intro', 'gate-icon-map', 'ornate-crest'] as $file) {
            $this->assertFileExists(FCPATH . 'assets/ui/' . $file . '.webp');
        }
    }

    /** The compass crest of every ornate panel is the owner's emblem picture (20 October 2026). */
    public function testOrnateCrestIsTheOwnersEmblem(): void
    {
        $html = view('partials/ornate-frame');

        $this->assertStringContainsString('<img class="ornate-crest" src="' . esc(asset_url_versioned('ui/ornate-crest.webp'), 'attr') . '"', $html);
        $this->assertStringNotContainsString('<svg class="ornate-crest"', $html);

        $plain = view('partials/ornate-frame', ['crest' => false]);

        $this->assertStringNotContainsString('ornate-crest', $plain, 'the wide sign-up card leaves the crest out');
        $this->assertSame(4, substr_count($plain, 'class="ornate-corner'), 'the corners stay');
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
        // Owner, 21 October 2026: the staff entry is the HUD menu's Masuk staf row, not a corner link
        $this->assertSame(1, substr_count($page, base_url('admin/login')), 'only the menu row');
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

    /**
     * The HUD language popover (every page since 18 October 2026): the owner's round flag
     * buttons (21 October 2026), whatever the `ui.lang.*` slots hold; the slots stay the
     * fallback while a round picture is not installed.
     */
    public function testLanguageSwitchUsesTheRoundFlags(): void
    {
        $this->media = ['ui.lang.id' => 'assets/ui/lang-id.webp', 'ui.lang.en' => 'assets/ui/lang-en.webp'];
        $page        = (new HomeController())->index();

        $this->assertStringContainsString('<form class="lang-switch hud-lang-list" method="post"', $page);
        $this->assertMatchesRegularExpression('/<summary class="hud-ctl" aria-label="' . preg_quote(esc(lang('Game.language') . ': Bahasa Indonesia', 'attr'), '/') . '"[^>]*>\s*<img class="hud-ctl-art" src="' . preg_quote(esc(asset_url_versioned('ui/round-lang-id.webp'), 'attr'), '/') . '" alt=""/', $page, 'the current language on the control');
        $this->assertStringContainsString('<img class="hud-sheet-flag is-round" src="' . esc(asset_url_versioned('ui/round-lang-en.webp'), 'attr') . '" alt=""', $page);
        $this->assertStringNotContainsString($this->attr('assets/ui/lang-id.webp'), $page, 'the round flags win over the slot icons');
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

    /** Owner, 21 October 2026: Masuk is the blank gold plate with the word written on it, so it reads in both languages. */
    public function testLoginButtonIsTheGoldPlate(): void
    {
        $plate = esc('--gold-plate: url("' . asset_url_versioned('ui/plate-gold-3.webp') . '")');
        $id    = view('game/login', ['errors' => []]);
        $this->assertStringContainsString('<button class="btn-gold-plate is-short" type="submit" style="' . $plate . '"><span>' . esc(lang('Game.login')) . '</span></button>', $id);
        $this->assertStringNotContainsString('btn-block', $id);

        Services::resetSingle('renderer');
        service('request')->setLocale('en');
        service('language')->setLocale('en');
        $this->assertStringContainsString('<span>Log in</span></button>', view('game/login', ['errors' => []]));
    }

    /** Owner, 21 October 2026: the login head greets ("Selamat Datang!" / "Welcome Back!") and says what to do, in kind words. */
    public function testLoginHeadGreetsTheStudent(): void
    {
        $page = (new LoginController())->form();

        $this->assertStringContainsString('<h1>Selamat Datang!</h1>', $page);
        $this->assertStringContainsString('<p>' . esc(lang('Game.loginLead')) . '</p>', $page);
        $this->assertStringContainsString('<title>' . esc(lang('Game.loginTitle')), $page, 'the tab still says Masuk');
    }

    /** Owner, 21 October 2026: staff sign in through the HUD menu, only while no student is signed in; no corner link. */
    public function testStaffLoginSitsInTheMenuBeforeSignIn(): void
    {
        $page = (new HomeController())->index();
        $menu = substr($page, (int) strpos($page, '<details class="hud-pop hud-menu"'));
        $this->assertStringNotContainsString('welcome-staff', $page);
        $this->assertMatchesRegularExpression('~<a class="hud-sheet-row hud-leave hud-staff" href="[^"]*admin/login">.*?' . preg_quote(esc(lang('Game.staffLogin')), '~') . '~s', $menu);
        $this->assertStringContainsString('href="' . base_url('tentang') . '"', $menu, 'Tentang GELITA for everyone');
        $this->assertStringNotContainsString('btn-fullscreen', $page, 'no Layar penuh row');

        Services::resetSingle('renderer');
        $this->assertStringContainsString('hud-staff', (new HomeController())->start());
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
        $this->assertSame([1200, 220], $store->requiredSize('ui.btn-start'));
        $this->assertSame([1200, 220], $store->requiredSize('ui.btn-start.en'));

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

    /**
     * The owner's round gold buttons (21 October 2026, assets/ui/round-*.webp): sound, mute,
     * and menu in the HUD; register, login, and the next arrow on the Mulai cards; the turning
     * ring on the login and sign-up buttons while the form is sent.
     */
    public function testRoundButtonsDrawTheHudAndTheMulaiCards(): void
    {
        $names = ['profile', 'login', 'register', 'lang-id', 'lang-en', 'sound', 'mute', 'menu', 'back', 'next', 'pause', 'loading', 'replay', 'blank'];

        foreach ($names as $name) {
            $this->assertFileExists(FCPATH . 'assets/ui/round-' . $name . '.webp');
            $this->assertSame([256, 256], array_slice(getimagesize(FCPATH . 'assets/ui/round-' . $name . '.webp'), 0, 2), $name);
        }

        $src  = static fn (string $name): string => esc(asset_url_versioned('ui/round-' . $name . '.webp'), 'attr');
        $page = (new HomeController())->start();

        $this->assertStringContainsString('<img class="hud-ctl-art icon-on" src="' . $src('sound') . '"', $page);
        $this->assertStringContainsString('<img class="hud-ctl-art icon-off" src="' . $src('mute') . '"', $page);
        $this->assertMatchesRegularExpression('~<summary class="hud-ctl" aria-label="' . preg_quote(esc(lang('Game.resultMenu'), 'attr'), '~') . '"[^>]*>\s*<img class="hud-ctl-art" src="' . preg_quote($src('menu'), '~') . '"~', $page);
        $this->assertStringContainsString('<span class="start-icon" aria-hidden="true"><img class="start-round" src="' . $src('register') . '"', $page);
        $this->assertStringContainsString('<span class="start-icon" aria-hidden="true"><img class="start-round" src="' . $src('login') . '"', $page);
        $this->assertSame(2, substr_count($page, '<span class="start-go" aria-hidden="true"><img class="start-round" src="' . $src('next') . '"'));

        $login = (new LoginController())->form();
        $this->assertStringContainsString('class="form login-form" data-login-form style="--round-loading: url(&quot;' . esc(asset_url_versioned('ui/round-loading.webp')) . '&quot;)"', $login);
    }

    /** Story narrator controls: ◀ ▶ ↻ and the ⏸/▶ toggle as round buttons; ▶ with its "Lanjut" label keeps its look. */
    public function testNarratorControlsUseTheRoundButtons(): void
    {
        $art = static fn (string $name): string => 'has-round-art" style="' . esc('--round-art: url("' . asset_url_versioned('ui/round-' . $name . '.webp') . '")', 'attr') . '" data-narrator=';

        $plain = view('components/narrator-controls');
        $this->assertStringContainsString('class="icon-btn ' . $art('back') . '"prev"', $plain);
        $toggle = esc('--round-art: url("' . asset_url_versioned('ui/round-pause.webp') . '"); --round-art-play: url("' . asset_url_versioned('ui/round-play.webp') . '")', 'attr');
        $this->assertStringContainsString('class="icon-btn narrator-toggle has-round-art" style="' . $toggle . '" data-narrator="toggle"', $plain, '⏸ while playing, ▶ from the second sheet otherwise');
        $this->assertStringContainsString('class="icon-btn ' . $art('replay') . '"replay"', $plain);
        $this->assertStringContainsString('class="icon-btn ' . $art('next') . '"next"', $plain);

        $labelled = view('components/narrator-controls', ['nextLabel' => true]);
        $this->assertStringContainsString('class="icon-btn narrator-next has-label" data-narrator="next"', $labelled);
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
