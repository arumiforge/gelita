<?php

use App\Libraries\DeadlockRetry;
use App\Models\ChallengeAttemptModel;
use App\Models\GameEventLogModel;
use App\Models\GameSessionModel;
use CodeIgniter\Config\Factories;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Events\Events;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\GradeFiveWorld;

/**
 * Event batches under the deadlock the production load test found
 * (21 November 2026): on MySQL 8 a multi-row INSERT IGNORE that meets a
 * stored row keeps a gap lock at the end of `game_event_logs`, and two such
 * batches of any two students deadlock (1213 → HTTP 500).
 *
 *  - re-sent events are left out before the INSERT, so the usual re-send
 *    (the pagehide copy on the next page) never reaches that lock;
 *  - a deadlock that still happens is run again, and the batch is stored
 *    once.
 *
 * SQLite has no such locks: the model is replaced by one that fails like
 * MySQL does (CLAUDE.md → Testing and known pitfalls).
 *
 * @internal
 */
final class EventIngestDeadlockTest extends CIUnitTestCase
{
    use GradeFiveWorld;

    private \CodeIgniter\Database\BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['gelita', 'content']);
        $this->conn             = $this->createGradeFiveWorld();
        DeadlockRetry::$sleeper = static function (int $microseconds): void {};
    }

    protected function tearDown(): void
    {
        DeadlockRetry::$sleeper = null;
        Factories::reset('models');
        $this->dropResetTables();
        parent::tearDown();
    }

    /** A model whose first $failures INSERTs end like a MySQL lock error. */
    private function failingModel(int $failures, int $code = 1213): GameEventLogModel
    {
        $model = new class () extends GameEventLogModel {
            public int $failures = 0;

            public int $code = 1213;

            /** @var list<list<string>> client_event_id of every INSERT that ran */
            public array $inserts = [];

            protected function insertIgnore(array $rows): int
            {
                $this->inserts[] = array_column($rows, 'client_event_id');

                if ($this->failures > 0) {
                    $this->failures--;

                    throw new DatabaseException('Deadlock found when trying to get lock; try restarting transaction', $this->code);
                }

                return parent::insertIgnore($rows);
            }
        };

        $model->failures = $failures;
        $model->code     = $code;
        Factories::injectMock('models', GameEventLogModel::class, $model);

        return $model;
    }

    /** @return list<array<string, mixed>> */
    private static function batch(string ...$ids): array
    {
        return array_map(static fn (string $id): array => ['event_type' => 'page_hidden', 'client_event_id' => $id, 'payload' => ['screen' => 'peta']], $ids);
    }

    private function stored(string $prefix): int
    {
        return $this->conn->table('game_event_logs')->like('client_event_id', $prefix, 'after')->countAllResults();
    }

    public function testADeadlockedBatchIsRunAgainAndStoredOnce(): void
    {
        $model   = $this->failingModel(2);
        $session = model(GameSessionModel::class)->find(1);

        $result = service('eventService')->ingest($session, self::batch('d-1', 'd-2'));

        $this->assertSame(['accepted' => 2, 'duplicate' => 0, 'rejected' => []], $result);
        $this->assertCount(3, $model->inserts, 'two refused runs, then the one that wrote');
        $this->assertSame(2, $this->stored('d-'));
    }

    public function testALockWaitTimeoutIsRunAgainToo(): void
    {
        $this->failingModel(1, 1205);

        $result = service('eventService')->ingest(model(GameSessionModel::class)->find(1), self::batch('t-1'));

        $this->assertSame(1, $result['accepted']);
        $this->assertSame(1, $this->stored('t-'));
    }

    public function testADeadlockOnEveryRunReachesTheCaller(): void
    {
        $model = $this->failingModel(99);

        try {
            service('eventService')->ingest(model(GameSessionModel::class)->find(1), self::batch('x-1'));
            $this->fail('After the last run the error must not be swallowed: the client keeps the batch and sends it again.');
        } catch (DatabaseException $e) {
            $this->assertSame(1213, $e->getCode());
        }

        $this->assertCount(DeadlockRetry::MAX_RUNS, $model->inserts);
        $this->assertSame(0, $this->stored('x-'));
    }

    public function testReSentEventsNeverReachTheInsert(): void
    {
        $model   = $this->failingModel(0);
        $session = model(GameSessionModel::class)->find(1);
        $service = service('eventService');

        // The pagehide beacon, then the same event again on the next page's first batch
        $service->ingest($session, self::batch('r-1'));
        $result = $service->ingest($session, self::batch('r-1', 'r-2'));

        $this->assertSame(['accepted' => 1, 'duplicate' => 1, 'rejected' => []], $result);
        $this->assertSame([['r-1'], ['r-2']], $model->inserts, 'the stored event is left out of the second INSERT');

        // A batch that is stored already writes nothing at all
        $result = $service->ingest($session, self::batch('r-1', 'r-2'));

        $this->assertSame(['accepted' => 0, 'duplicate' => 2, 'rejected' => []], $result);
        $this->assertCount(2, $model->inserts);
        $this->assertSame(2, $this->stored('r-'));
    }

    public function testACopyStillOnItsWayIsCaughtByTheUniqueKey(): void
    {
        // Two requests with the same event pass the lookup together; the second INSERT meets the row
        $session = model(GameSessionModel::class)->find(1);
        $model   = model(GameEventLogModel::class);
        $row     = static fn (string $id): array => [
            'event_uuid' => uuid4(), 'client_event_id' => $id, 'session_id' => $session->id, 'participant_id' => $session->participant_id,
            'event_type' => 'page_hidden', 'sequence_no' => 1, 'occurred_at' => '2026-11-21 00:00:00', 'server_received_at' => '2026-11-21 00:00:01',
        ];

        $this->assertSame(['accepted' => 1, 'duplicate' => 0], $model->appendBatch([$row('u-1')], false));
        $this->assertSame(['accepted' => 1, 'duplicate' => 1], $model->appendBatch([$row('u-1'), $row('u-2')], false));
        $this->assertSame(2, $this->stored('u-'));
    }

    public function testAFailedInsertInsideACallersTransactionIsNotSilent(): void
    {
        // Inside a transaction the framework returns false for a failed statement; reported as
        // "all duplicates" the client would drop the events, and after a deadlock the caller
        // would go on writing without its transaction (ChallengeService records events this way)
        $session = model(GameSessionModel::class)->find(1);
        // A statement that cannot succeed stands in for the one MySQL refuses
        $model = new class () extends GameEventLogModel {
            protected $table = 'game_event_logs_gone';
        };

        $this->conn->transBegin();

        try {
            $model->appendBatch([[
                'event_uuid' => uuid4(), 'client_event_id' => 'f-1', 'session_id' => $session->id, 'participant_id' => $session->participant_id,
                'event_type' => 'page_hidden', 'sequence_no' => 1, 'occurred_at' => '2026-11-21 00:00:00',
            ]], false);
            $this->fail('The failed INSERT must reach the owner of the transaction.');
        } catch (DatabaseException $e) {
            $this->assertStringContainsString('game_event_logs_gone', $e->getMessage());
        } finally {
            $this->conn->transRollback();
            $this->conn->resetTransStatus();
        }
    }

    public function testADeadlockedAudioBatchIsWrittenOnceWithItsCounters(): void
    {
        $this->conn->table('audio_assets')->insert(['media_asset_id' => 900, 'locale' => 'id', 'context_code' => 'uji', 'transcript' => 'Halo.']);
        $asset   = (int) $this->conn->insertID();
        $session = model(GameSessionModel::class)->find(1);
        $nodeId  = $this->nodeId($this->conn, 'tmg-3');

        service('challengeService')->openNode($session, $nodeId);
        $attemptId = (int) model(ChallengeAttemptModel::class)->currentInProgress(1, $nodeId)->id;

        // The deadlock arrives at the last statement of the first run, after the events,
        // the audio rows and the counter were written: all of it has to be undone
        $failed   = false;
        $listener = static function ($query) use (&$failed): void {
            if (! $failed && str_contains((string) $query, 'audio_use_count')) {
                $failed = true;

                throw new DatabaseException('Deadlock found when trying to get lock; try restarting transaction', 1213);
            }
        };
        Events::on('DBQuery', $listener);

        try {
            $result = service('eventService')->recordAudioBatch($session, [
                ['client_event_id' => 'a-1', 'audio_asset_id' => $asset, 'action' => 'play', 'attempt_id' => $attemptId],
                ['client_event_id' => 'a-2', 'audio_asset_id' => $asset, 'action' => 'complete', 'listened_ms' => 4200, 'completed' => true, 'attempt_id' => $attemptId],
            ]);
        } finally {
            Events::removeListener('DBQuery', $listener);
        }

        $this->assertTrue($failed, 'the first run met the deadlock');
        $this->assertSame(['accepted' => 2, 'duplicate' => 0, 'rejected' => []], $result);
        $this->assertSame(2, $this->stored('a-'));
        $this->assertSame(2, $this->conn->table('audio_usage_events')->countAllResults(), 'one row per entry, not two');
        $this->assertSame(2, (int) $this->conn->table('challenge_attempts')->where('id', $attemptId)->get()->getRow('audio_use_count'), 'counted once');
        $this->assertSame(0, $this->conn->transDepth);
    }
}
