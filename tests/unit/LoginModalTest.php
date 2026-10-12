<?php

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Session\Session;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\TestResponse;
use Config\Services;
use Tests\Support\Database\GradeFiveWorld;

/**
 * Student login without a page reload (owner request, 12 October 2026).
 *
 * game/login.js posts `/masuk` with `X-Requested-With: XMLHttpRequest` and
 * gets JSON: `{ ok: true, redirect }`, or `{ ok: false, field, message }`
 * that it shows in a modal. A plain POST (no JavaScript) still redirects.
 *
 * @internal
 */
final class LoginModalTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use GradeFiveWorld;

    private const CSRF_HASH = '0123456789abcdef0123456789abcdef';
    private const PASSWORD  = 'Pelangi#Lama2026';

    private BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['url', 'gelita', 'content']);

        $this->conn = $this->createGradeFiveWorld();
        $this->conn->table('participants')->where('id', 1)->update([
            'password_hash' => password_hash(self::PASSWORD, PASSWORD_DEFAULT),
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

    public function testWrongPasswordAnswersJsonForTheModal(): void
    {
        $response = $this->login(['username' => 'jaka5', 'password' => 'salah-sekali'], true);

        $this->assertFalse($response->isRedirect());
        $data = json_decode($response->getJSON(), true);
        $this->assertFalse($data['ok']);
        $this->assertSame('password', $data['field']);
        $this->assertSame(lang('Auth.loginFailed'), $data['message']);
        $this->assertSame('gelita_csrf', $data['csrfName']);
        $this->assertNotSame('', $data['csrf']);
        $this->assertArrayNotHasKey('participant_id', $_SESSION);
    }

    public function testEmptyNameAnswersJsonNamingTheField(): void
    {
        $data = json_decode($this->login(['username' => '', 'password' => self::PASSWORD], true)->getJSON(), true);

        $this->assertFalse($data['ok']);
        $this->assertSame('username', $data['field']);
        $this->assertNotSame('', $data['message']);
    }

    public function testRightPasswordAnswersJsonWithTheNextPage(): void
    {
        $data = json_decode($this->login(['username' => 'jaka5', 'password' => self::PASSWORD], true)->getJSON(), true);

        $this->assertTrue($data['ok']);
        $this->assertStringEndsWith('/mulai', $data['redirect']);
        $this->assertSame(1, (int) ($_SESSION['participant_id'] ?? 0));
    }

    public function testMustChangeAnswersJsonWithThePasswordPage(): void
    {
        $this->conn->table('participants')->where('id', 1)->update(['must_change_password' => 1]);

        $data = json_decode($this->login(['username' => 'jaka5', 'password' => self::PASSWORD], true)->getJSON(), true);

        $this->assertTrue($data['ok']);
        $this->assertStringEndsWith('/ganti-sandi', $data['redirect']);
    }

    /** The page asked for before login survives a failed try that did not reload /masuk. */
    public function testFailedTryKeepsTheWantedPage(): void
    {
        session()->setFlashdata('redirect_after_login', '/pustaka'); // kept by GET /masuk
        $this->nextRequest();                                         // POST /masuk starts: the flash ages

        $this->login(['username' => 'jaka5', 'password' => 'salah-sekali'], true, keepVars: true);
        $this->nextRequest();                                         // the next try starts

        $this->assertSame('/pustaka', session('redirect_after_login'), 'still there for the next try');
    }

    /** Session start of a new request: CodeIgniter ages flash data here. */
    private function nextRequest(): void
    {
        (new ReflectionMethod(Session::class, 'initVars'))->invoke(session());
    }

    public function testPlainPostStillRedirectsWithTheMessage(): void
    {
        $response = $this->login(['username' => 'jaka5', 'password' => 'salah-sekali'], false);

        $this->assertTrue($response->isRedirect());
        $this->assertStringEndsWith('/masuk', $response->getRedirectUrl());
        $this->assertSame(['password' => lang('Auth.loginFailed')], $_SESSION['errors'] ?? null);
    }

    /**
     * One POST /masuk with a valid CSRF token and the session carried over,
     * like a browser; `$ajax` adds the header game/login.js sends.
     *
     * @param array<string, string> $params
     */
    private function login(array $params, bool $ajax, bool $keepVars = false): TestResponse
    {
        Services::resetSingle('security');
        Services::resetSingle('gameContext');

        if (! $keepVars) {
            unset($_SESSION['__ci_vars']);
        }

        $session               = ['gelita_csrf' => self::CSRF_HASH] + $_SESSION;
        $params['gelita_csrf'] = self::CSRF_HASH . str_repeat('0', 32);
        $headers               = $ajax ? ['X-Requested-With' => 'XMLHttpRequest', 'Accept' => 'application/json'] : [];

        return $this->withSession($session)->withHeaders($headers)->call(Method::POST, 'masuk', $params);
    }
}
