<?php

use CodeIgniter\HTTP\Method;
use CodeIgniter\Security\Exceptions\SecurityException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;
use Tests\Support\Database\ConsentWorld;

/**
 * 30 §19.3 finding (e): after a withdrawal, the student's open page still
 * sends its queued events with the old CSRF token. The first request ends
 * the session (401, 30 §5.3); the later ones no longer match any session
 * token. They get the same plain 401 INVALID_SESSION, without an exception
 * (which the framework logs as a critical error), so core/events.js stops.
 *
 * @internal
 */
final class EndedSessionCsrfTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use ConsentWorld;

    /** CSRF hash (session mode); token = hash . zero key (tokenRandomize). */
    private const CSRF_HASH = '0123456789abcdef0123456789abcdef';

    private \CodeIgniter\Database\BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createConsentWorld();
        $this->conn->table('game_sessions')->where('id', 1)->update(['status' => 'active']);
        $this->consent($this->conn, 1);
        Services::resetSingle('security');
        Services::resetSingle('gameContext');
    }

    protected function tearDown(): void
    {
        $this->dropConsentWorld();
        parent::tearDown();
    }

    /** @param array<string, mixed> $session */
    private function postEvents(array $session, string $path = 'api/events'): \CodeIgniter\Test\TestResponse
    {
        Services::resetSingle('security');

        return $this->withSession($session)
            ->withHeaders(['X-CSRF-TOKEN' => self::CSRF_HASH . str_repeat('0', 32)])
            ->withBodyFormat('json')
            ->call(Method::POST, $path, ['events' => [], 'client_sent_at' => gmdate('Y-m-d\TH:i:s.000\Z')]);
    }

    /** @return array{code: ?string} */
    private function body(\CodeIgniter\Test\TestResponse $response): array
    {
        return json_decode((string) $response->getJSON(), true) ?? [];
    }

    public function testQueuedEventsAfterAWithdrawalGetAPlain401(): void
    {
        service('consentService')->apply(1, 1, ['status' => 'withdrawn_keep', 'status_on' => date('Y-m-d')], 2);

        // The first batch still carries a valid token: the session filter ends the session
        $first = $this->postEvents(['participant_id' => 1, 'game_session_id' => 1, 'gelita_csrf' => self::CSRF_HASH]);
        $first->assertStatus(401);
        $this->assertSame('INVALID_SESSION', $this->body($first)['code'] ?? null);

        // The next batch (or beacon) of the same page: the session holds no login and no token any more
        $next = $this->postEvents([]);
        $next->assertStatus(401);
        $this->assertSame('INVALID_SESSION', $this->body($next)['code'] ?? null);
        $this->assertSame(0, $this->conn->table('game_event_logs')->countAllResults(), 'nothing stored after the withdrawal');

        // The beacon path of the audio channel behaves the same
        $this->postEvents([], 'api/audio-events')->assertStatus(401);
    }

    public function testARefusedTokenWithALoginIsStillThrown(): void
    {
        // Still signed in with a stale page token: GelitaExceptionHandler answers 403 CSRF_EXPIRED
        $this->expectException(SecurityException::class);
        $this->postEvents(['participant_id' => 1, 'game_session_id' => 1, 'gelita_csrf' => 'ffffffffffffffffffffffffffffffff']);
    }

    public function testAPageFormWithoutLoginIsStillThrown(): void
    {
        $this->expectException(SecurityException::class);
        $this->withSession([])->call(Method::POST, 'masuk', ['username' => 'jaka5', 'password' => 'x']);
    }
}
