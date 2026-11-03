<?php

use App\Controllers\Game\RegisterController;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\FieldWorld;

/**
 * Registration in field mode starts with the token (research revision 10,
 * part 2: 28 §6.5, §10.2, §19.1 `RegisterWithTokenTest`).
 *
 * @internal
 */
final class RegisterWithTokenTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use FieldWorld;

    private const ABOUT = ['display_name' => 'Sekar Ayu', 'age' => '10', 'gender' => 'perempuan'];

    private \CodeIgniter\Database\BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createFieldWorld();
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        service('cache')->clean();
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testTokenFirstSchoolStepSkippedAndCodesInOrder(): void
    {
        $fs = $this->fieldSession($this->conn, 1, 'GHJKMN');

        $this->assertRedirectsTo('daftar/0', $this->send(Method::GET, 'daftar'));
        $this->assertRedirectsTo('daftar/0', $this->send(Method::GET, 'daftar/1'));
        $this->assertStringContainsString(esc(lang('Game.tokenHelp')), (string) $this->send(Method::GET, 'daftar/0')->getBody());

        $first = $this->register('sekar_ayu', 'ghj kmn');
        $this->assertSame('S03-A02', $first['participant_code'], 'the world already used A01');
        $this->assertSame(['1', 'A'], [(string) $first['school_id'], (string) $first['rombel']]);
        $this->assertNull($first['province_code'], 'no region asked (M-11)');

        $session = $this->conn->table('game_sessions')->where('participant_id', $first['id'])->get()->getRowArray();
        $this->assertSame([$fs['id'], 1], [(int) $session['field_session_id'], (int) $session['meeting_no']]);
        $this->assertSame('S03-A02', $_SESSION['research_code'] ?? null, 'the research code card');

        $_SESSION = [];
        $this->assertSame('S03-A03', $this->register('bayu_aji', 'GHJKMN')['participant_code']);
    }

    public function testAMeetingTwoTokenDoesNotRegister(): void
    {
        $this->fieldSession($this->conn, 2, 'GHJKMN');

        $this->assertRedirectsTo('daftar/0', $this->send(Method::POST, 'daftar/0', ['token' => 'GHJKMN']));
        $this->assertSame(lang('Game.tokenUnknown'), $_SESSION['errors']['token'] ?? null);
    }

    public function testTheHundredthCodeIsRefused(): void
    {
        $this->fieldSession($this->conn, 1, 'GHJKMN');
        $this->conn->table('participant_code_counters')->update(['next_no' => 100]);

        $this->assertRedirectsTo('daftar/0', $this->registerResponse('sekar_ayu', 'GHJKMN'));
        $this->assertSame(lang('Game.codeFull'), $_SESSION['errors']['token'] ?? null);
        $this->assertSame(0, $this->conn->table('participants')->where('username', 'sekar_ayu')->countAllResults());
    }

    /** @return array<string, mixed> the new participants row */
    private function register(string $username, string $token): array
    {
        $this->assertRedirectsTo('intro', $this->registerResponse($username, $token));

        return $this->conn->table('participants')->where('username', $username)->get()->getRowArray();
    }

    private function registerResponse(string $username, string $token): \CodeIgniter\Test\TestResponse
    {
        $this->assertRedirectsTo('daftar/1', $this->send(Method::POST, 'daftar/0', ['token' => $token]));
        $this->assertRedirectsTo('daftar/3', $this->send(Method::POST, 'daftar/1', self::ABOUT));
        $this->assertArrayNotHasKey('2', $_SESSION[RegisterController::WIZARD]);

        return $this->send(Method::POST, 'daftar/3', [
            'username' => $username, 'password' => 'Kopi-Kedu7!', 'password_confirm' => 'Kopi-Kedu7!', 'assent' => '1', 'locale' => 'id',
        ]);
    }
}
