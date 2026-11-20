<?php

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\TestResponse;
use Config\Services;
use Tests\Support\Database\GradeFiveWorld;

/**
 * No research study is `active` (owner, 20 November 2026): students get a
 * friendly message instead of a server error. `/masuk` and `/daftar` show a
 * notice, POSTs are refused before the password check (no failed-login
 * count), and a student already playing is logged out and sent to `/masuk`
 * (pages) or answered INVALID_SESSION (game API). No study or a draft →
 * Game.gameNotOpen; the newest study closed → Game.studyEnded.
 *
 * @internal
 */
final class NoActiveStudyTest extends CIUnitTestCase
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

    /** @return iterable<string, array{string, string}> */
    public static function studyStates(): iterable
    {
        yield 'draft' => ['draft', 'Game.gameNotOpen'];
        yield 'closed' => ['closed', 'Game.studyEnded'];
    }

    private function setStudy(string $status): void
    {
        $this->conn->table('research_studies')->update(['status' => $status]);
    }

    public function testNoStudyAtAllMeansNotOpen(): void
    {
        $this->conn->table('research_studies')->update(['status' => 'draft']);
        $model = model(\App\Models\ResearchStudyModel::class);
        $this->assertSame('not_open', $model->gameClosedReason());

        $this->conn->table('research_studies')->update(['status' => 'active']);
        $this->assertNull($model->gameClosedReason());
        $this->assertNull($model->gameClosedMessage());

        // An older closed study and a newer draft: the newest decides
        $this->conn->table('research_studies')->update(['status' => 'closed']);
        $this->conn->table('research_studies')->insert(['code' => 'STUDI-BARU', 'name' => 'Studi baru', 'status' => 'draft']);
        $this->assertSame('not_open', $model->gameClosedReason());
    }

    /** @dataProvider studyStates */
    public function testLoginPageShowsTheNotice(string $status, string $key): void
    {
        $this->setStudy($status);

        $response = $this->get('masuk');

        $response->assertStatus(200);
        $this->assertStringContainsString(esc(lang($key)), $response->getBody());
        $this->assertStringNotContainsString('name="password"', $response->getBody(), 'no form to fill in');
    }

    /** @dataProvider studyStates */
    public function testPlainLoginPostIsRefusedBeforeThePassword(string $status, string $key): void
    {
        $this->setStudy($status);

        $response = $this->login(['username' => 'jaka5', 'password' => 'salah-sekali'], false);

        $this->assertTrue($response->isRedirect());
        $this->assertStringEndsWith('/masuk', $response->getRedirectUrl());
        $this->assertSame(['username' => lang($key)], $_SESSION['errors'] ?? null);
        $this->assertNoFailedLogin();
    }

    /** @dataProvider studyStates */
    public function testAjaxLoginAnswersJsonForTheModal(string $status, string $key): void
    {
        $this->setStudy($status);

        foreach (['salah-sekali', self::PASSWORD] as $password) {
            $response = $this->login(['username' => 'jaka5', 'password' => $password], true);
            $response->assertStatus(200);
            $data = json_decode($response->getJSON(), true);

            $this->assertFalse($data['ok']);
            $this->assertSame('username', $data['field']);
            $this->assertSame(lang($key), $data['message']);
        }

        $this->assertArrayNotHasKey('participant_id', $_SESSION);
        $this->assertNoFailedLogin();
        $this->assertSame(1, $this->conn->table('game_sessions')->countAllResults(), 'no game session made');
    }

    public function testServiceLoginRefusesWithoutStudy(): void
    {
        $this->setStudy('closed');

        $result = service('sessionService')->login('jaka5', 'salah-sekali', 'id', []);

        $this->assertSame('not_open', $result['status']);
        $this->assertSame(lang('Game.studyEnded'), $result['message']);
        $this->assertNoFailedLogin();
    }

    /** @dataProvider studyStates */
    public function testRegisterPagesShowTheNotice(string $status, string $key): void
    {
        $this->setStudy($status);

        foreach (['daftar', 'daftar/0', 'daftar/1', 'daftar/3'] as $path) {
            $response = $this->get($path);
            $response->assertStatus(200);
            $this->assertStringContainsString(esc(lang($key)), $response->getBody(), $path);
            $this->assertStringNotContainsString('<form method="post" action="' . base_url('daftar'), $response->getBody(), "{$path}: no form");
        }
    }

    /** @dataProvider studyStates */
    public function testRegisterPostIsRefused(string $status, string $key): void
    {
        $this->setStudy($status);
        $_SESSION = [
            'reg_wizard' => ['1' => ['display_name' => 'Sari'], '2' => ['country_code' => 'ID']],
        ];

        $response = $this->post3([
            'username' => 'sari5', 'password' => 'Pelangi#Baru2026', 'password_confirm' => 'Pelangi#Baru2026', 'assent' => '1',
        ]);

        $this->assertTrue($response->isRedirect());
        $this->assertStringEndsWith('/daftar', $response->getRedirectUrl());
        $this->assertSame(['study' => lang($key)], $_SESSION['errors'] ?? null);
        $this->assertSame(0, $this->conn->table('participants')->where('username', 'sari5')->countAllResults());
    }

    /** @dataProvider studyStates */
    public function testPlayingStudentIsLoggedOutFromAGamePage(string $status): void
    {
        $this->setStudy($status);

        $response = $this->withSession(['participant_id' => 1, 'game_session_id' => 1])->get('peta');

        $this->assertTrue($response->isRedirect());
        $this->assertStringEndsWith('/masuk', $response->getRedirectUrl());
        $this->assertSame('paused', $this->conn->table('game_sessions')->where('id', 1)->get()->getRow()->status);
    }

    /** @dataProvider studyStates */
    public function testTokenAndPasswordPagesLogOutButKeluarWorks(string $status): void
    {
        $this->setStudy($status);

        foreach (['token', 'ganti-sandi', 'sampai-jumpa'] as $path) {
            Services::resetSingle('gameContext');
            $response = $this->withSession(['participant_id' => 1])->get($path);
            $this->assertTrue($response->isRedirect(), $path);
            $this->assertStringEndsWith('/masuk', $response->getRedirectUrl(), $path);
        }

        $response = $this->withSession(['participant_id' => 1, 'game_session_id' => 1])->get('keluar');
        $this->assertTrue($response->isRedirect());
        $this->assertStringEndsWith('/', $response->getRedirectUrl());
    }

    /** @dataProvider studyStates */
    public function testPlayingStudentGetsJsonFromTheGameApi(string $status, string $key): void
    {
        $this->setStudy($status);

        $response = $this->withSession(['participant_id' => 1, 'game_session_id' => 1])->get('api/progress');

        $response->assertStatus(401);
        $data = json_decode((string) $response->getJSON(), true);
        $this->assertSame('INVALID_SESSION', $data['code']);
        $this->assertSame('GAME_NOT_OPEN', $data['reason']);
        $this->assertSame(lang($key), $data['message']);
        $this->assertSame('paused', $this->conn->table('game_sessions')->where('id', 1)->get()->getRow()->status);
    }

    public function testActiveStudyStillOpensTheGame(): void
    {
        $this->assertStringContainsString('name="password"', $this->get('masuk')->getBody());
        $this->withSession(['participant_id' => 1, 'game_session_id' => 1])->get('api/progress')->assertStatus(200);
    }

    private function assertNoFailedLogin(): void
    {
        $row = $this->conn->table('participants')->where('id', 1)->get()->getRow();
        $this->assertSame(0, (int) $row->failed_login_count);
        $this->assertNull($row->locked_until);
    }

    /** @param array<string, string> $params */
    private function post3(array $params): TestResponse
    {
        Services::resetSingle('security');
        $session               = ['gelita_csrf' => self::CSRF_HASH] + $_SESSION;
        $params['gelita_csrf'] = self::CSRF_HASH . str_repeat('0', 32);

        return $this->withSession($session)->call(Method::POST, 'daftar/3', $params);
    }

    /** @param array<string, string> $params */
    private function login(array $params, bool $ajax): TestResponse
    {
        Services::resetSingle('security');
        Services::resetSingle('gameContext');
        unset($_SESSION['__ci_vars']);

        $session               = ['gelita_csrf' => self::CSRF_HASH] + $_SESSION;
        $params['gelita_csrf'] = self::CSRF_HASH . str_repeat('0', 32);
        $headers               = $ajax ? ['X-Requested-With' => 'XMLHttpRequest', 'Accept' => 'application/json'] : [];

        return $this->withSession($session)->withHeaders($headers)->call(Method::POST, 'masuk', $params);
    }
}
