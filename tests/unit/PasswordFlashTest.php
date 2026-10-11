<?php

use App\Controllers\Game\GateController;
use App\Entities\GameSession;
use App\Entities\Participant;
use App\Services\GameContext;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\Method;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Session\Session;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\TestResponse;
use Config\Services;
use Tests\Support\Database\GradeFiveWorld;

/**
 * Display plan Phase 0 (docs/arsip/rencana-perbaikan-tampilan.md): the messages
 * around a password change.
 *
 * - "Kata sandi baru tersimpan" used to go to `/mulai`, which redirected a
 *   logged-in student again to `/gerbang`; the flash was used up by `/mulai`
 *   and never shown. Now it goes straight to `/gerbang`, and the redirect to
 *   `/intro` keeps it for one more request.
 * - "Kamu harus mengganti kata sandi" was shown twice on `/ganti-sandi`
 *   (flash toast and the page alert); only the alert is left. The same for
 *   staff on `/admin/akun/sandi`.
 *
 * @internal
 */
final class PasswordFlashTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use GradeFiveWorld;

    private const CSRF_HASH = '0123456789abcdef0123456789abcdef';
    private const OLD       = 'Pelangi#Lama2026';
    private const NEW       = 'Gunung#Sindoro77';

    private BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['url', 'gelita', 'content']);

        $this->conn = $this->createGradeFiveWorld();
        $this->conn->table('participants')->where('id', 1)->update([
            'password_hash' => password_hash(self::OLD, PASSWORD_DEFAULT),
        ]);
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $this->dropResetTables();
        Services::resetSingle('gameContext');
        $_SESSION = [];
        parent::tearDown();
    }

    public function testPasswordChangedMessageReachesTheGate(): void
    {
        $_SESSION = ['participant_id' => 1, 'game_session_id' => 1];

        $response = $this->send(Method::POST, 'ganti-sandi', [
            'current_password' => self::OLD, 'password' => self::NEW, 'password_confirm' => self::NEW,
        ]);

        $this->assertTrue($response->isRedirect());
        $this->assertStringEndsWith('/gerbang', $response->getRedirectUrl(), 'no stop at /mulai, which used up the flash');
        $this->assertSame(lang('Auth.passwordChanged'), $_SESSION['message'] ?? null);
    }

    public function testMustChangeLoginHasNoDuplicateToast(): void
    {
        $this->conn->table('participants')->where('id', 1)->update(['must_change_password' => 1]);

        $response = $this->send(Method::POST, 'masuk', ['username' => 'jaka5', 'password' => self::OLD]);

        $this->assertTrue($response->isRedirect());
        $this->assertStringEndsWith('/ganti-sandi', $response->getRedirectUrl());
        $this->assertArrayNotHasKey('message', $_SESSION, 'the /ganti-sandi alert already says it');
    }

    public function testStaffMustChangeLoginHasNoDuplicateMessage(): void
    {
        $this->conn->table('staff_users')->where('username', 'admin_uji')->update([
            'password_hash' => password_hash(self::OLD, PASSWORD_DEFAULT), 'must_change_password' => 1,
        ]);

        $response = $this->send(Method::POST, 'admin/login', ['username' => 'admin_uji', 'password' => self::OLD]);

        $this->assertTrue($response->isRedirect());
        $this->assertStringEndsWith('/admin/akun/sandi', $response->getRedirectUrl());
        $this->assertArrayNotHasKey('message', $_SESSION, 'the password page alert already says it');
    }

    /** Flash set on one request, then /gerbang sends a new student to /intro: the message survives. */
    public function testIntroRedirectKeepsTheMessage(): void
    {
        $this->play(introSeen: false);
        session()->setFlashdata('message', 'Kata sandi baru tersimpan.');
        $this->nextRequest(); // the request that renders /gerbang

        $gate = (new GateController())->index();

        $this->assertInstanceOf(RedirectResponse::class, $gate);
        $this->assertStringEndsWith('/intro', $gate->getHeaderLine('Location'));

        $this->nextRequest(); // the request that renders /intro
        $this->assertSame('Kata sandi baru tersimpan.', session('message'));
    }

    /** Session start of a new request: CodeIgniter ages flash data here. */
    private function nextRequest(): void
    {
        (new ReflectionMethod(Session::class, 'initVars'))->invoke(session());
    }

    private function play(bool $introSeen): void
    {
        $session = new GameSession();
        $session->injectRawData(['id' => 1, 'participant_id' => 1, 'locale' => 'id', 'status' => 'active']);

        $participant = new Participant();
        $participant->injectRawData([
            'id' => 1, 'username' => 'jaka5', 'display_name' => 'Jaka',
            'intro_seen_at' => $introSeen ? '2026-09-01 08:00:00' : null,
        ]);

        $context              = new GameContext();
        $context->session     = $session;
        $context->participant = $participant;

        Services::injectMock('gameContext', $context);
        session()->set(['participant_id' => 1, 'game_session_id' => 1]);
    }

    /**
     * One form request with a valid CSRF token and the session carried over
     * from the previous request, like a browser.
     *
     * @param array<string, string> $params
     */
    private function send(string $method, string $path, array $params = []): TestResponse
    {
        Services::resetSingle('security');
        Services::resetSingle('gameContext');
        unset($_SESSION['__ci_vars']);

        $session                = ['gelita_csrf' => self::CSRF_HASH] + $_SESSION;
        $params['gelita_csrf']  = self::CSRF_HASH . str_repeat('0', 32);

        return $this->withSession($session)->call($method, $path, $params);
    }
}
