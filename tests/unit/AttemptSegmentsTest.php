<?php

use App\Models\ChallengeAttemptModel;
use App\Models\ChallengeAttemptSegmentModel;
use App\Models\GameSessionModel;
use App\Services\ChallengeService;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Encryption;
use Tests\Support\Database\BankPlay;

/**
 * Attempt segments (research revision 10, part 7: 33 §5.4, §13.1
 * `AttemptSegmentsTest`): opened by openNode(), closed by abandonAttempt(),
 * closeAttempt(), and a paused session; a reload adds none; two closes at
 * once write one.
 *
 * @internal
 */
final class AttemptSegmentsTest extends CIUnitTestCase
{
    use BankPlay;

    private string $originalKey;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['gelita', 'content']);
        $this->originalKey             = (string) config(Encryption::class)->key;
        config(Encryption::class)->key = 'kunci-uji-potongan';
        $this->conn                    = $this->createGradeFiveWorld();
        $this->service                 = new ChallengeService();
    }

    protected function tearDown(): void
    {
        config(Encryption::class)->key = $this->originalKey;
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testOpenReloadAndAbandon(): void
    {
        $attempt = $this->open('tmg-2');
        $this->service->openNode($this->session(), $attempt->challenge_node_id);   // reload

        $segments = $this->segmentsOf($attempt->id);
        $this->assertCount(1, $segments, 'a reload adds no segment');
        $this->assertSame([1, 1, null], [(int) $segments[0]['segment_no'], (int) $segments[0]['session_id'], $segments[0]['left_at']]);
        $this->assertStringStartsWith(substr((string) $this->reload($attempt)->toRawArray()['started_at'], 0, 19), (string) $segments[0]['opened_at']);

        $this->service->abandonAttempt($this->reload($attempt));

        $this->assertSame('user_exit', $this->segmentsOf($attempt->id)[0]['left_via']);
        $this->assertNotNull($this->segmentsOf($attempt->id)[0]['left_at']);
    }

    public function testCloseEndsTheSegmentAtCompletedAt(): void
    {
        $attempt = $this->open('tmg-3');
        $answers = [];

        foreach ($this->attemptItems($attempt) as $item) {
            if (! $item->isDecoy() && $item->scorable) {
                $answers[$item->id] = ['verdict' => $item->verdict()];
            }
        }

        $result = $this->service->submitCheck($this->reload($attempt), $answers);
        $this->assertTrue($result['completed']);

        $segment = $this->segmentsOf($attempt->id)[0];
        $this->assertSame('closed', $segment['left_via']);
        $this->assertStringStartsWith((string) $this->reload($attempt)->toRawArray()['completed_at'], (string) $segment['left_at']);
    }

    public function testAPausedSessionClosesItsSegmentsAndTheNextOpenAddsOne(): void
    {
        $attempt = $this->open('tmg-2');

        // Logout pauses the session (session_paused {via: logout})
        $_SESSION = ['participant_id' => 1, 'game_session_id' => 1];
        service('sessionService')->logout();

        $this->assertSame('session_paused', $this->segmentsOf($attempt->id)[0]['left_via']);

        $this->conn->table('game_sessions')->where('id', 1)->update(['status' => 'active']);
        $this->service->openNode($this->session(), $attempt->challenge_node_id);

        $segments = $this->segmentsOf($attempt->id);
        $this->assertCount(2, $segments);
        $this->assertSame([2, null], [(int) $segments[1]['segment_no'], $segments[1]['left_at']]);
    }

    public function testIdleCleanupClosesAtLastActivity(): void
    {
        $attempt = $this->open('tmg-2');
        $this->conn->table('game_sessions')->where('id', 1)->update(['last_active_at' => '2026-11-04 08:00:00']);

        $this->assertSame(1, model(GameSessionModel::class)->markStale(30));

        $segment = $this->segmentsOf($attempt->id)[0];
        $this->assertSame('session_paused', $segment['left_via']);
        $this->assertStringStartsWith('2026-11-04 08:00:00', (string) $segment['left_at']);
    }

    public function testTwoClosesAtOnceWriteOne(): void
    {
        $attempt  = $this->open('tmg-2');
        $segments = model(ChallengeAttemptSegmentModel::class);

        $this->assertTrue($segments->closeOpen($attempt->id, '2026-11-04 08:00:00.000', 'pagehide'));
        $this->assertFalse($segments->closeOpen($attempt->id, '2026-11-04 08:00:05.000', 'user_exit'));
        $this->assertSame('pagehide', $this->segmentsOf($attempt->id)[0]['left_via']);
        $this->assertSame(0, $segments->closeForSession(1, '2026-11-04 08:00:09'));
    }

    /** @return list<array<string, mixed>> */
    private function segmentsOf(int $attemptId): array
    {
        return $this->conn->table('challenge_attempt_segments')->where('challenge_attempt_id', $attemptId)->orderBy('segment_no')->get()->getResultArray();
    }
}
