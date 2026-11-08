<?php

use App\Models\GameSessionModel;
use CodeIgniter\Config\Factories;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\ResetTables;

/**
 * `markStale()` writes `session_paused {via: idle}` once per session it
 * really changed, timed at the session's last activity (research revision
 * 10, part 3: 29 §5.7, K14). A second caller that read the same sessions
 * loses the conditional UPDATE and writes nothing.
 *
 * @internal
 */
final class MarkStaleEventTest extends CIUnitTestCase
{
    use ResetTables;

    private \CodeIgniter\Database\BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();

        $this->conn = $this->createResetTables(['participants', 'game_sessions', 'game_event_logs']);
        $this->conn->table('participants')->insert(['participant_code' => 'GLT-000001', 'username' => 'a', 'password_hash' => 'x']);

        $old = date('Y-m-d H:i:s', time() - 3 * 3600);
        $this->conn->table('game_sessions')->insertBatch([
            ['session_code' => 'S1', 'participant_id' => 1, 'study_id' => 1, 'release_id' => 1, 'locale' => 'id', 'status' => 'active', 'last_active_at' => $old],
            ['session_code' => 'S2', 'participant_id' => 1, 'study_id' => 1, 'release_id' => 1, 'locale' => 'id', 'status' => 'active', 'last_active_at' => date('Y-m-d H:i:s')],
            ['session_code' => 'S3', 'participant_id' => 1, 'study_id' => 1, 'release_id' => 1, 'locale' => 'id', 'status' => 'paused', 'last_active_at' => $old],
        ]);
    }

    protected function tearDown(): void
    {
        Factories::reset('models');
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testOneIdleEventPerChangedSessionAtItsLastActivity(): void
    {
        $this->assertSame(1, model(GameSessionModel::class)->markStale(45));

        $rows = $this->conn->table('game_event_logs')->get()->getResultArray();
        $this->assertCount(1, $rows);
        $this->assertSame(['session_paused', 1], [$rows[0]['event_type'], (int) $rows[0]['session_id']]);

        $lastActive = (string) $this->conn->table('game_sessions')->where('id', 1)->get()->getRow('last_active_at');
        $this->assertSame(['via' => 'idle', 'last_active_at' => $lastActive], json_decode((string) $rows[0]['payload_json'], true));
        $this->assertStringStartsWith($lastActive, (string) $rows[0]['occurred_at'], 'the event keeps the time of the last activity');
        $this->assertSame(['paused', 'active', 'paused'], array_column($this->conn->table('game_sessions')->orderBy('id')->get()->getResultArray(), 'status'));

        $this->assertSame(0, model(GameSessionModel::class)->markStale(45), 'nothing left to change');
        $this->assertSame(1, $this->conn->table('game_event_logs')->countAllResults());
    }

    public function testTwoCallersNeverWriteTheEventTwice(): void
    {
        // The second caller read the stale ids before the first one changed them
        $stale = new class () extends GameSessionModel {
            protected function staleIds(string $cutoff): array
            {
                return [1, 3];
            }
        };

        $this->assertSame(1, model(GameSessionModel::class)->markStale(45));
        Factories::injectMock('models', GameSessionModel::class, $stale);
        $this->assertSame(0, model(GameSessionModel::class)->markStale(45), 'loses the conditional UPDATE for both');
        $this->assertSame(1, $this->conn->table('game_event_logs')->where('event_type', 'session_paused')->countAllResults());
    }
}
