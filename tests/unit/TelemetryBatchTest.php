<?php

use App\Entities\GameSession;
use App\Models\GameSessionModel;
use App\Services\EventService;
use App\Services\SessionService;
use CodeIgniter\Config\Factories;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Events\Events;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\Mock\MockSession;
use Config\Services;
use Tests\Support\Database\GradeFiveWorld;

/**
 * Phase 2 of the asynchronous logging plan (docs/rencana-log-asinkron.md
 * §5.1 R2/R3, §5.2 R5–R7):
 *
 * - POST /api/events carries `events` and `audio` together, with an
 *   `audio_dropped` report, and answers with an `audio` key whenever the
 *   request had an `audio` array;
 * - every successful batch is also the session heartbeat, at most once per
 *   50 s per session;
 * - the three telemetry endpoints release the PHP session first;
 * - audio entries are written as one batch in one transaction, with the
 *   per-entry rejections, idempotency, and play index rule of the old
 *   per-event writer;
 * - js_config() exposes the flush rhythm, and Ringkasan says how fresh its
 *   recent activity list is.
 *
 * SQLite shows none of the lock behaviour; that is checked on MariaDB/MySQL.
 *
 * @internal
 */
final class TelemetryBatchTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use GradeFiveWorld;

    /** CSRF hash (session mode); token = hash . zero key (tokenRandomize). */
    private const CSRF_HASH = '0123456789abcdef0123456789abcdef';

    private BaseConnection $conn;

    /** @var list<int> */
    private array $assets = [];

    private int $attemptId;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['gelita', 'content', 'ui']);

        $this->conn = $this->createGradeFiveWorld();
        service('cache')->clean();

        foreach (['a', 'b'] as $code) {
            $this->conn->table('audio_assets')->insert([
                'media_asset_id' => $code === 'a' ? 900 : 901, 'locale' => 'id', 'context_code' => 'uji-' . $code, 'transcript' => 'Halo.',
            ]);
            $this->assets[] = (int) $this->conn->insertID();
        }

        $this->attemptId = service('challengeService')->openNode($this->session(), $this->nodeId($this->conn, 'tmg-1'))['attempt']->id;
    }

    protected function tearDown(): void
    {
        service('cache')->clean();
        Factories::reset('models');
        $this->dropResetTables();
        parent::tearDown();
    }

    // ------------------------------------------------------------ combined envelope

    public function testOneRequestCarriesEventsAudioAndBothDropReports(): void
    {
        $response = $this->send('api/events', [
            'events'           => [$this->event('page_hidden', 3)],
            'audio'            => [$this->audio('a-1', 0, 'play'), $this->audio('a-2', 0, 'complete', $this->attemptId)],
            'client_sent_at'   => $this->iso(-20_000),
            'dropped'          => 4,
            'dropped_id'       => 'ev-1',
            'audio_dropped'    => 2,
            'audio_dropped_id' => 'au-1',
        ]);

        $response->assertOK();
        $data = $this->data($response);
        $this->assertSame(1, $data['accepted']);
        $this->assertSame(['accepted' => 2, 'duplicate' => 0, 'rejected' => []], $data['audio']);
        $this->assertArrayHasKey('server_time', $data);

        $drops = $this->conn->table('game_event_logs')->where('event_type', 'events_dropped')->orderBy('id')->get()->getResultArray();
        $this->assertSame(
            [['dropped' => 4, 'channel' => 'events'], ['dropped' => 2, 'channel' => 'audio']],
            array_map(static fn (array $r): array => json_decode((string) $r['payload_json'], true), $drops),
        );

        $audioRows = $this->conn->table('game_event_logs')->whereIn('event_type', ['audio_play', 'audio_completed'])->get()->getResultArray();
        $this->assertCount(2, $audioRows);

        foreach ($audioRows as $row) {
            $this->assertEqualsWithDelta(20_000, (int) $row['clock_offset_ms'], 5_000, 'audio rows get the batch offset');
        }

        $this->assertSame(2, $this->conn->table('audio_usage_events')->countAllResults());
        $this->assertSame(1, $this->audioUseCount());

        // The answer was lost: the same request again writes nothing new
        $again = $this->data($this->send('api/events', [
            'events' => [$this->event('page_hidden', 3)],
            'audio'  => [$this->audio('a-1', 0, 'play'), $this->audio('a-2', 0, 'complete', $this->attemptId)],
            'audio_dropped' => 2, 'audio_dropped_id' => 'au-1',
        ]));
        $this->assertSame(1, $again['duplicate']);
        $this->assertSame(['accepted' => 0, 'duplicate' => 2, 'rejected' => []], $again['audio']);
        $this->assertSame(2, $this->conn->table('game_event_logs')->where('event_type', 'events_dropped')->countAllResults());
        $this->assertSame(1, $this->audioUseCount());
    }

    public function testAudioOnlyBatchAndTheAudioKey(): void
    {
        $audioOnly = $this->data($this->send('api/events', ['events' => [], 'audio' => [$this->audio('a-1', 0, 'autoplay')]]));
        $this->assertSame(0, $audioOnly['accepted']);
        $this->assertSame(1, $audioOnly['audio']['accepted']);

        $emptyAudio = $this->data($this->send('api/events', ['events' => [$this->event('page_visible', 1)], 'audio' => []]));
        $this->assertSame(['accepted' => 0, 'duplicate' => 0, 'rejected' => []], $emptyAudio['audio'], 'present even for an empty array');

        $noAudio = $this->data($this->send('api/events', ['events' => [$this->event('page_hidden', 2)]]));
        $this->assertArrayNotHasKey('audio', $noAudio, 'absent when no audio array was sent');

        // A drop report alone is recorded without an audio array
        $this->send('api/events', ['events' => [], 'audio_dropped' => 5, 'audio_dropped_id' => 'au-9'])->assertOK();
        $drop = $this->conn->table('game_event_logs')->where('event_type', 'events_dropped')->get()->getRowArray();
        $this->assertSame(['dropped' => 5, 'channel' => 'audio'], json_decode((string) $drop['payload_json'], true));
    }

    public function testOverLimitAndMalformedAudio(): void
    {
        $max    = config('Gelita')->maxEventsPerBatch;
        $audio  = [];
        $before = $this->conn->table('game_event_logs')->countAllResults();   // challenge_opened from setUp

        for ($i = 0; $i <= $max; $i++) {
            $audio[] = $this->audio('a-' . $i, 0, 'play');
        }

        $tooMany = $this->send('api/events', ['events' => [], 'audio' => $audio]);
        $tooMany->assertStatus(413);
        $this->assertSame('TOO_MANY_EVENTS', json_decode((string) $tooMany->getJSON(), true)['code'] ?? null);

        $events = array_map(fn (int $i): array => $this->event('page_hidden', $i + 1), range(0, $max));
        $this->send('api/events', ['events' => $events])->assertStatus(413);

        $this->send('api/events', ['events' => [], 'audio' => 'play'])->assertStatus(422);
        $this->send('api/events', ['audio' => []])->assertStatus(422);

        $this->assertSame($before, $this->conn->table('game_event_logs')->countAllResults(), 'a refused request writes nothing');
        $this->assertSame(0, $this->conn->table('audio_usage_events')->countAllResults());
    }

    public function testOldAudioEndpointUsesTheSameBatchWriter(): void
    {
        $data = $this->data($this->send('api/audio-events', [
            'events'         => [$this->audio('a-1', 0, 'play'), 'x', $this->audio('a-1', 0, 'play')],
            'client_sent_at' => $this->iso(0),
            'dropped'        => 3,
            'dropped_id'     => 'au-old',
        ]));

        $this->assertSame(1, $data['accepted']);
        $this->assertSame(1, $data['duplicate']);
        $this->assertSame([['index' => 1, 'reason' => 'format_invalid']], $data['rejected']);
        $this->assertSame(1, $this->conn->table('game_event_logs')->where('event_type', 'events_dropped')->countAllResults());
    }

    // ------------------------------------------------------------ heartbeat in the batch

    public function testBatchHeartbeatRunsAtMostOncePerInterval(): void
    {
        $longAgo = date('Y-m-d H:i:s', time() - 600);
        $this->setLastActive($longAgo);

        $this->send('api/events', ['events' => [$this->event('page_hidden', 1)]])->assertOK();
        $first = $this->gameSessionRow();
        $this->assertNotSame($longAgo, $first['last_active_at'], 'the first batch beats');
        $this->assertGreaterThanOrEqual(599_000, (int) $first['duration_ms']);

        $this->setLastActive($longAgo);
        $this->send('api/events', ['events' => [$this->event('page_visible', 2)]])->assertOK();
        $this->assertSame($longAgo, $this->gameSessionRow()['last_active_at'], 'within 50 s: no second heartbeat');

        // A refused batch is not a heartbeat
        service('cache')->clean();
        $this->send('api/events', ['events' => 'x'])->assertStatus(422);
        $this->assertSame($longAgo, $this->gameSessionRow()['last_active_at']);

        // Interval over (cache entry expired)
        $this->send('api/events', ['events' => []])->assertOK();
        $this->assertNotSame($longAgo, $this->gameSessionRow()['last_active_at']);
    }

    public function testHeartbeatIfDueThrottlesPerSession(): void
    {
        $sessions = service('sessionService');

        $this->assertTrue($sessions->heartbeatIfDue(1));
        $this->assertFalse($sessions->heartbeatIfDue(1));
        $this->assertTrue($sessions->heartbeatIfDue(2), 'another session has its own key');
        $this->assertSame(50, SessionService::BATCH_HEARTBEAT_SECONDS);
    }

    /** Two tabs beat at once: the one that read a stale last_active_at adds nothing. */
    public function testRacingHeartbeatsCountTheTimeOnce(): void
    {
        $this->setLastActive(date('Y-m-d H:i:s', time() - 120));
        $stale = model(GameSessionModel::class)->find(1);

        service('sessionService')->heartbeat(1);
        $afterFirst = $this->gameSessionRow();
        $this->assertGreaterThanOrEqual(119_000, (int) $afterFirst['duration_ms']);

        // Second tab: same moment, still holding the old row
        $sessions = new class () extends GameSessionModel {
            public ?GameSession $stale = null;

            public function find($id = null)
            {
                return $this->stale ?? parent::find($id);
            }
        };
        $sessions->stale = $stale;
        Factories::injectMock('models', GameSessionModel::class, $sessions);

        service('sessionService')->heartbeat(1);

        $this->assertSame((int) $afterFirst['duration_ms'], (int) $this->gameSessionRow()['duration_ms']);
    }

    // ------------------------------------------------------------ session released early

    public function testTelemetryEndpointsReleaseTheSessionFirst(): void
    {
        foreach ([
            'api/events'            => ['events' => [$this->event('page_hidden', 1)], 'audio' => [$this->audio('a-1', 0, 'play')]],
            'api/audio-events'      => ['events' => [$this->audio('a-2', 0, 'play')]],
            'api/session/heartbeat' => [],
        ] as $path => $body) {
            $spy = $this->sessionSpy();

            $response = $this->send($path, $body);

            $response->assertOK();
            $this->assertSame(1, $spy->closed, $path . ' closes the session');
            $this->assertSame([], $spy->writesAfterClose, $path . ' writes nothing to $_SESSION after closing');
        }

        $this->assertSame(2, $this->conn->table('audio_usage_events')->countAllResults(), 'the endpoints still work with a closed session');
    }

    // ------------------------------------------------------------ batched audio writer

    public function testDuplicatesWithinAndAcrossBatches(): void
    {
        $first = $this->service()->recordAudioBatch($this->session(), [
            $this->audio('a-1', 0, 'play'),
            $this->audio('a-1', 0, 'play'),          // repeated in the same batch
            $this->audio('', 0, 'pause'),            // no id: never a duplicate
            $this->audio('', 0, 'pause'),
        ]);
        $this->assertSame(['accepted' => 3, 'duplicate' => 1, 'rejected' => []], $first);

        $second = $this->service()->recordAudioBatch($this->session(), [
            $this->audio('a-1', 0, 'play'),          // stored by the first batch
            $this->audio('a-3', 0, 'complete'),
        ]);
        $this->assertSame(['accepted' => 1, 'duplicate' => 1, 'rejected' => []], $second);

        $this->assertSame(4, $this->conn->table('audio_usage_events')->countAllResults());
        $this->assertSame(1, $this->conn->table('game_event_logs')->where('client_event_id', 'a-1')->countAllResults());
    }

    public function testPlayIndexFollowsBatchOrderAcrossAssetsAndActions(): void
    {
        [$a, $b] = [0, 1];

        // Before any play: a pause belongs to playback 1, the next play opens 2 (nextPlayIndex rule)
        $this->service()->recordAudioBatch($this->session(), [
            $this->audio('p-1', $a, 'pause'),
            $this->audio('p-2', $a, 'play'),
            $this->audio('p-3', $b, 'autoplay'),
            $this->audio('p-4', $a, 'pause'),
            $this->audio('p-5', $a, 'complete'),
            $this->audio('p-6', $b, 'complete'),
            $this->audio('p-7', $a, 'replay'),
        ]);
        // A later batch continues from the stored maximum per asset
        $this->service()->recordAudioBatch($this->session(), [
            $this->audio('p-8', $b, 'play'),
            $this->audio('p-9', $a, 'pause'),
        ]);

        $rows = $this->conn->table('audio_usage_events')->orderBy('id')->get()->getResultArray();
        $this->assertSame(
            [
                [$this->assets[$a], 'pause', 1],
                [$this->assets[$a], 'play', 2],
                [$this->assets[$b], 'autoplay', 1],
                [$this->assets[$a], 'pause', 2],
                [$this->assets[$a], 'complete', 2],
                [$this->assets[$b], 'complete', 1],
                [$this->assets[$a], 'replay', 3],
                [$this->assets[$b], 'play', 2],
                [$this->assets[$a], 'pause', 3],
            ],
            array_map(static fn (array $r): array => [(int) $r['audio_asset_id'], $r['action'], (int) $r['play_index']], $rows),
        );

        // Raw events keep batch order in sequence_no, after the server's next number
        $sequence = array_map('intval', array_column(
            $this->conn->table('game_event_logs')->like('client_event_id', 'p-', 'after')->orderBy('id')->get()->getResultArray(),
            'sequence_no',
        ));
        $this->assertSame(range($sequence[0], $sequence[0] + 8), $sequence);
    }

    public function testAttemptCounterAndRejectedReasons(): void
    {
        $this->conn->table('game_sessions')->insert([
            'session_code' => 'S-LAIN', 'participant_id' => 1, 'study_id' => 1, 'release_id' => 1, 'locale' => 'id', 'status' => 'active',
        ]);
        $otherAttempt = service('challengeService')->openNode(model(GameSessionModel::class)->find(2), $this->nodeId($this->conn, 'tmg-1'))['attempt']->id;

        $result = $this->service()->recordAudioBatch($this->session(), [
            $this->audio('r-0', 0, 'play', $this->attemptId),
            'not an object',
            $this->audio('r-2', 0, 'play', $otherAttempt),
            ['audio_asset_id' => $this->assets[0], 'action' => 'rewind', 'client_event_id' => 'r-3'],
            ['audio_asset_id' => 999_999, 'action' => 'play', 'client_event_id' => 'r-4'],
            ['action' => 'play', 'client_event_id' => 'r-5'],
            ['audio_asset_id' => $this->assets[0], 'action' => 'play', 'client_event_id' => str_repeat('x', 121)],
            ['audio_asset_id' => $this->assets[1], 'action' => 'pause', 'challenge_attempt_id' => $this->attemptId, 'client_event_id' => 'r-7'],
            $this->audio('r-8', 1, 'complete'),
        ]);

        $this->assertSame(3, $result['accepted']);
        $this->assertSame([
            ['index' => 1, 'reason' => 'format_invalid'],
            ['index' => 2, 'reason' => 'attempt_not_in_session'],
            ['index' => 3, 'reason' => 'audio_event_invalid'],
            ['index' => 4, 'reason' => 'audio_event_invalid'],
            ['index' => 5, 'reason' => 'audio_event_invalid'],
            ['index' => 6, 'reason' => 'audio_event_invalid'],
        ], $result['rejected']);

        $this->assertSame(2, $this->audioUseCount(), 'one update adds both entries of the attempt');
        $this->assertSame(0, (int) $this->conn->table('challenge_attempts')->where('id', $otherAttempt)->get()->getRow('audio_use_count'));
    }

    public function testWritesRunInOneTransactionAndRollBackTogether(): void
    {
        $depths = [];
        $listener = static function ($query) use (&$depths): void {
            $sql = ltrim((string) $query->getQuery());

            if (preg_match('/^(INSERT|UPDATE)/i', $sql) === 1) {
                $depths[] = db_connect()->transDepth;
            }
        };
        Events::on('DBQuery', $listener);

        try {
            $this->service()->recordAudioBatch($this->session(), [
                $this->audio('t-1', 0, 'play', $this->attemptId),
                $this->audio('t-2', 1, 'play', $this->attemptId),
            ]);
        } finally {
            Events::removeListener('DBQuery', $listener);
        }

        $this->assertSame([1, 1, 1], $depths, 'two inserts and one counter update, all inside the transaction');

        // The second insert fails: the raw events and the counter roll back with it
        $table = $this->conn->prefixTable('audio_usage_events');
        $this->conn->query("ALTER TABLE {$table} RENAME TO {$table}_off");

        try {
            $this->service()->recordAudioBatch($this->session(), [$this->audio('t-3', 0, 'play', $this->attemptId)]);
            $this->fail('the batch should fail');
        } catch (\Throwable) {
            // expected
        } finally {
            $this->conn->query("ALTER TABLE {$table}_off RENAME TO {$table}");
        }

        $this->assertSame(0, $this->conn->table('game_event_logs')->where('client_event_id', 't-3')->countAllResults());
        $this->assertSame(2, $this->audioUseCount());
        $this->assertSame(0, (int) db_connect()->transDepth);
    }

    /** Another tab stored the same entry between the existence check and the insert. */
    public function testEntryWonByAnotherTabIsADuplicateAndWritesNoUsageRow(): void
    {
        $service = new class () extends EventService {
            public ?Closure $beforeWrite = null;

            public function nextServerSequence(int $sessionId): int
            {
                if ($this->beforeWrite !== null) {
                    ($this->beforeWrite)();
                    $this->beforeWrite = null;
                }

                return parent::nextServerSequence($sessionId);
            }
        };
        $service->beforeWrite = function (): void {
            service('eventService')->recordAudioBatch($this->session(), [$this->audio('w-1', 0, 'play', $this->attemptId)]);
        };

        $result = $service->recordAudioBatch($this->session(), [
            $this->audio('w-1', 0, 'play', $this->attemptId),
            $this->audio('w-2', 0, 'pause', $this->attemptId),
        ]);

        $this->assertSame(['accepted' => 1, 'duplicate' => 1, 'rejected' => []], $result);
        $this->assertSame(2, $this->conn->table('audio_usage_events')->countAllResults());
        $this->assertSame(2, $this->audioUseCount(), 'the lost entry is not counted twice');
    }

    public function testSingleRecordAudioStillWorks(): void
    {
        $this->assertTrue($this->service()->recordAudio($this->session(), $this->audio('s-1', 0, 'play')));
        $this->assertFalse($this->service()->recordAudio($this->session(), $this->audio('s-1', 0, 'play')));

        $this->expectException(InvalidArgumentException::class);
        $this->service()->recordAudio($this->session(), ['audio_asset_id' => $this->assets[0], 'action' => 'rewind']);
    }

    // ------------------------------------------------------------ config and dashboard

    public function testJsConfigExposesTheFlushRhythm(): void
    {
        $this->assertSame(['intervalMs' => 30000, 'maxEvents' => 40], js_config('id')['eventFlush']);

        $config = config('Gelita');
        $saved  = $config->eventFlush;

        try {
            // .env values arrive as strings
            $config->eventFlush = ['intervalMs' => '15000', 'maxEvents' => '20'];
            $this->assertSame(['intervalMs' => 15000, 'maxEvents' => 20], js_config('id')['eventFlush']);

            $config->eventFlush = ['intervalMs' => '5', 'maxEvents' => '500'];
            $this->assertSame(['intervalMs' => 1000, 'maxEvents' => $config->maxEventsPerBatch], event_flush_config(), 'clamped');
        } finally {
            $config->eventFlush = $saved;
        }
    }

    public function testDashboardSaysHowFreshRecentActivityIs(): void
    {
        $table = ['rows' => [], 'total' => 0, 'page' => 1, 'per_page' => 20, 'sort' => null, 'columns' => []];
        $html  = view('admin/dashboard', [
            'kpi'             => ['participants' => 0, 'active_sessions' => 0, 'completed_today' => 0, 'feedback' => 0],
            'events'          => $table,
            'progress'        => $table,
            'readiness'       => null,
            'retentionAlerts' => [],
            'isAdmin'         => true,
        ]);

        $this->assertStringContainsString('<p class="table-note muted">Diperbarui tiap ±30 detik.</p>', $html);
        $this->assertSame(1, substr_count($html, 'table-note'), 'only under the activity list');

        $config = config('Gelita');
        $saved  = $config->eventFlush;

        try {
            $config->eventFlush = ['intervalMs' => 15000, 'maxEvents' => 40];
            $this->assertStringContainsString('Diperbarui tiap ±15 detik.', view('admin/dashboard', [
                'kpi' => ['participants' => 0, 'active_sessions' => 0, 'completed_today' => 0, 'feedback' => 0],
                'events' => $table, 'progress' => $table, 'readiness' => null, 'retentionAlerts' => [], 'isAdmin' => false,
            ]), 'worded from the configured interval');
        } finally {
            $config->eventFlush = $saved;
        }
    }

    // ------------------------------------------------------------ helpers

    private function service(): EventService
    {
        return service('eventService');
    }

    private function session(): GameSession
    {
        return model(GameSessionModel::class)->find(1);
    }

    /** @param array<string, mixed> $body */
    private function send(string $path, array $body)
    {
        Services::resetSingle('security');
        Services::resetSingle('gameContext');

        return $this->withSession(['participant_id' => 1, 'game_session_id' => 1, 'gelita_csrf' => self::CSRF_HASH])
            ->withHeaders(['X-CSRF-TOKEN' => self::CSRF_HASH . str_repeat('0', 32)])
            ->withBodyFormat('json')
            ->call(Method::POST, $path, $body);
    }

    /** @return array<string, mixed> */
    private function data($response): array
    {
        return json_decode((string) $response->getJSON(), true)['data'] ?? [];
    }

    /**
     * A session that counts close() and notes any $_SESSION write after it.
     */
    private function sessionSpy(): object
    {
        $spy = new class (new \CodeIgniter\Session\Handlers\ArrayHandler(config('Session'), '0.0.0.0'), config('Session')) extends MockSession {
            public int $closed = 0;

            /** @var list<string> */
            public array $writesAfterClose = [];

            public function close(): void
            {
                $this->closed++;
            }

            public function set($data, $value = null): void
            {
                if ($this->closed > 0) {
                    $this->writesAfterClose[] = is_array($data) ? implode(',', array_keys($data)) : (string) $data;
                }

                parent::set($data, $value);
            }

            public function remove($key): void
            {
                if ($this->closed > 0) {
                    $this->writesAfterClose[] = 'remove:' . (is_array($key) ? implode(',', $key) : $key);
                }

                parent::remove($key);
            }
        };

        Services::injectMock('session', $spy);

        return $spy;
    }

    private function audioUseCount(): int
    {
        return (int) $this->conn->table('challenge_attempts')->where('id', $this->attemptId)->get()->getRow('audio_use_count');
    }

    /** @return array<string, mixed> */
    private function gameSessionRow(): array
    {
        return $this->conn->table('game_sessions')->where('id', 1)->get()->getRowArray();
    }

    private function setLastActive(string $at): void
    {
        $this->conn->table('game_sessions')->where('id', 1)->update(['last_active_at' => $at, 'duration_ms' => 0]);
    }

    /** ISO 8601 UTC with milliseconds, `$ms` from now (core/events.js now()). */
    private function iso(int $ms): string
    {
        $at = microtime(true) + $ms / 1000;

        return gmdate('Y-m-d\TH:i:s', (int) floor($at)) . sprintf('.%03dZ', (int) floor(fmod($at, 1) * 1000));
    }

    /** @return array<string, mixed> */
    private function event(string $type, int $sequence): array
    {
        return [
            'client_event_id' => sprintf('c-%s-%05d', $type, $sequence),
            'event_type'      => $type,
            'occurred_at'     => $this->iso(-1_000),
            'sequence_no'     => $sequence,
        ];
    }

    /** @return array<string, mixed> */
    private function audio(string $id, int $asset, string $action, ?int $attemptId = null): array
    {
        return array_filter([
            'client_event_id' => $id,
            'audio_asset_id'  => $this->assets[$asset],
            'action'          => $action,
            'listened_ms'     => $action === 'complete' ? 4_200 : null,
            'completed'       => $action === 'complete',
            'occurred_at'     => $this->iso(-25_000),
            'attempt_id'      => $attemptId,
        ], static fn ($v): bool => $v !== null && $v !== '');
    }
}
