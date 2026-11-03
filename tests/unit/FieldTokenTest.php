<?php

use App\Services\FieldSessionService;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\FieldWorld;

/**
 * Session tokens (research revision 10, part 2: 28 §6.3, K5, §19.1 `FieldTokenTest`).
 *
 * @internal
 */
final class FieldTokenTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use FieldWorld;

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

    public function testTokensUseSixCharactersWithoutLookAlikes(): void
    {
        $service = new FieldSessionService();

        for ($i = 0; $i < 200; $i++) {
            $token = $service->randomToken();
            $this->assertMatchesRegularExpression('/^[ABCDEFGHJKMNPQRSTUVWXYZ23456789]{6}$/', $token);
        }

        foreach (['O', '0', 'I', '1', 'L'] as $char) {
            $this->assertStringNotContainsString($char, FieldSessionService::TOKEN_ALPHABET);
        }

        $this->assertSame('ABC234', FieldSessionService::normalizeToken(" abc 23\t4 "));
    }

    public function testACollisionWithAnEarlierTokenIsRetried(): void
    {
        $this->fieldSession($this->conn, 2, 'AAAAAA');
        $candidates = ['AAAAAA', 'AAAAAA', 'BBBBBB'];
        $service    = new FieldSessionService(static function () use (&$candidates): string {
            return array_shift($candidates);
        });

        $created = $service->create([
            'school_id' => 1, 'rombel' => 'B', 'meeting_no' => 2, 'session_date' => '2030-01-15', 'opens' => '07:30', 'closes' => '09:30',
        ], 2);

        $this->assertSame('BBBBBB', $created['token']);
        $this->assertSame('S03-B-P2', $created['field_session']['code']);
    }

    public function testTokensWorkOnlyWhileTheSessionIsOpenAndOnlyTheLatest(): void
    {
        $service = service('fieldSessions');
        $student = model(\App\Models\ParticipantModel::class)->find(1);

        $pending = $this->fieldSession($this->conn, 3, 'PENDNG', ['opens_at' => date('Y-m-d H:i:s', time() + 600)]);
        $this->assertSame('not_open_yet', $service->checkToken('pendng', $student)['status']);

        $this->fieldSession($this->conn, 4, 'CXSDXX', ['closes_at' => date('Y-m-d H:i:s', time() - 60)]);
        $this->assertSame('closed', $service->checkToken('CXSDXX', $student)['status']);

        $open = $this->fieldSession($this->conn, 2, 'GHJKMN');
        $this->assertSame('ok', $service->checkToken(' ghj kmn ', $student)['status'], 'lower case and spaces accepted');

        $new = $service->regenerateToken($open['id'], 2);
        $service->forget();
        $this->assertSame('token_unknown', $service->checkToken('GHJKMN', $student)['status'], 'the old token stops at once');
        $this->assertSame('ok', $service->checkToken($new, $student)['status']);
        $this->assertSame('token_unknown', $service->checkToken('ZZZZZZ', $student)['status']);
        $this->assertSame(1, $this->conn->table('audit_logs')->where('action', 'field_session_token_regenerated')->countAllResults());
        $this->assertNotNull($pending['id']);
    }

    public function testTenWrongTokensStartAFiveMinutePause(): void
    {
        $fs = $this->fieldSession($this->conn, 2, 'GHJKMN');
        $this->signIn(1);

        for ($i = 0; $i < 10; $i++) {
            $this->assertRedirectsTo('token', $this->send(Method::POST, 'token', ['token' => 'WRONG' . $i]));
        }

        $this->assertSame(lang('Game.tokenTooMany'), $_SESSION['errors']['token'] ?? null);

        // Even the right token waits now
        $this->assertRedirectsTo('token', $this->send(Method::POST, 'token', ['token' => $fs['token']]));
        $this->assertSame(lang('Game.tokenTooMany'), $_SESSION['errors']['token'] ?? null);
        $this->assertSame(0, $this->conn->table('game_sessions')->where('field_session_id', $fs['id'])->countAllResults());
    }

    public function testAnAcceptedTokenStartsABoundGameSession(): void
    {
        $fs = $this->fieldSession($this->conn, 2, 'GHJKMN');
        $this->signIn(1);

        $this->assertRedirectsTo('peta', $this->send(Method::POST, 'token', ['token' => 'ghjkmn']));

        $session = $this->conn->table('game_sessions')->where('field_session_id', $fs['id'])->get()->getRowArray();
        $this->assertSame(2, (int) $session['meeting_no']);
        $this->assertSame((int) $session['id'], (int) $_SESSION['game_session_id']);
        $this->assertSame(1, $this->conn->table('game_event_logs')->where('event_type', 'session_started')->like('payload_json', '"token"')->countAllResults());

        // Signed in again in the same field session: no second token (K7)
        $this->assertRedirectsTo('gerbang', $this->send(Method::GET, 'token'));
    }
}
