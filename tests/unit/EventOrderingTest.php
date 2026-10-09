<?php

use App\Entities\GameSession;
use App\Models\GameSessionModel;
use App\Services\EventService;
use App\Services\RawDataService;
use App\Services\StaffScope;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;
use Tests\Support\Database\GradeFiveWorld;

/**
 * Event order and time after an offline period (rencana-paket-aset §7.3,
 * §11.3 `EventOrderingTest`):
 *
 * - `clock_offset_ms` = server_received_at − client_sent_at of the batch;
 *   raw `occurred_at` is never changed;
 * - `sequence_no` is stored as the client sent it (it continues across pages);
 * - a queue that was offline for 2 minutes and is re-sent in several batches,
 *   partly twice, arrives complete, without duplicates, in increasing
 *   `sequence_no`, with `clock_offset_ms` set;
 * - the client's `dropped` report becomes one server-side `events_dropped`;
 * - `package_changed` is accepted and exported in the Navigasi sheet.
 *
 * @internal
 */
final class EventOrderingTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use GradeFiveWorld;

    /** CSRF hash (session mode); token = hash . zero key (tokenRandomize). */
    private const CSRF_HASH = '0123456789abcdef0123456789abcdef';

    private BaseConnection $conn;
    private string $originalZone;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['gelita', 'content']);

        $this->originalZone = date_default_timezone_get();
        date_default_timezone_set('Asia/Jakarta');

        $this->conn = $this->createGradeFiveWorld();
        Services::resetSingle('rawDataService');
    }

    protected function tearDown(): void
    {
        date_default_timezone_set($this->originalZone);
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testClockOffsetComesFromClientSentAt(): void
    {
        // Device clock 90 s behind the server
        $sentAt     = $this->iso(-90_000);
        $occurredAt = $this->iso(-95_000);
        $result     = $this->service()->ingest($this->session(), [
            $this->event('page_hidden', 7, $occurredAt),
        ], ['client_sent_at' => $sentAt]);

        $this->assertSame(1, $result['accepted']);

        $row = $this->rows()[0];
        $this->assertEqualsWithDelta(90_000, (int) $row['clock_offset_ms'], 5_000);
        $this->assertSame(
            (new DateTimeImmutable($occurredAt))->setTimezone(new DateTimeZone('Asia/Jakarta'))->format('Y-m-d H:i:s.v'),
            substr((string) $row['occurred_at'], 0, 23),
            'occurred_at stays the raw client time',
        );
    }

    public function testMissingOrInvalidClientSentAtLeavesOffsetEmpty(): void
    {
        $this->service()->ingest($this->session(), [$this->event('page_hidden', 1, $this->iso(0))]);
        $this->service()->ingest($this->session(), [$this->event('page_visible', 2, $this->iso(0))], ['client_sent_at' => 'tomorrow']);

        $this->assertSame([null, null], array_column($this->rows(), 'clock_offset_ms'));
    }

    public function testSequenceNoIsStoredAsSentAndServerEventsHaveNoOffset(): void
    {
        $this->service()->ingest($this->session(), [
            $this->event('page_hidden', 1041, $this->iso(-2_000)),
            $this->event('page_visible', 1042, $this->iso(-1_000)),
        ], ['client_sent_at' => $this->iso(0)]);

        service('eventService')->record($this->session(), 'session_paused', ['via' => 'logout']);

        $rows = $this->rows();
        $this->assertSame([1041, 1042, 1043], array_map('intval', array_column($rows, 'sequence_no')), 'server continues after the client');
        $this->assertSame(0, (int) $rows[2]['clock_offset_ms']);
    }

    /**
     * §11.2: the network drops for 2 minutes in the middle of a challenge.
     * The device (clock 5 minutes behind) keeps emitting across three pages;
     * the first batch reached the server but its answer was lost, so the
     * queue still holds it. When the network returns the queue is re-sent in
     * batches of 50, and a late beacon repeats the last page.
     */
    public function testOfflineQueueArrivesCompleteOrderedWithoutDuplicates(): void
    {
        $skewMs = -300_000;
        $events = [];

        for ($i = 0; $i < 130; $i++) {
            // one event about every 0.92 s over 2 minutes; sequence continues across pages
            $events[] = $this->event(
                $i % 2 === 0 ? 'reading_progress' : 'answer_changed',
                40 + $i,
                $this->iso(-125_000 + (int) round($i * 923) + $skewMs),
                ['page' => intdiv($i, 50) + 1],
            );
        }

        $session = $this->session();
        $service = $this->service();

        // Reached the server; the client never saw the answer
        $service->ingest($session, array_slice($events, 0, 20), ['client_sent_at' => $this->iso($skewMs)]);

        // Network back: the stored queue (all 130, oldest first) in batches of 50
        $accepted = 0;
        $dupes    = 0;

        foreach (array_chunk($events, 50) as $batch) {
            $result = $service->ingest($session, $batch, ['client_sent_at' => $this->iso($skewMs)]);
            $accepted += $result['accepted'];
            $dupes    += $result['duplicate'];
            $this->assertSame([], $result['rejected']);
        }

        // A late beacon of the last page arrives once more
        $again = $service->ingest($session, array_slice($events, 100), ['client_sent_at' => $this->iso($skewMs)]);

        $this->assertSame(110, $accepted);
        $this->assertSame(20, $dupes);
        $this->assertSame(0, $again['accepted']);
        $this->assertSame(30, $again['duplicate']);

        $rows = $this->rows();
        $this->assertCount(130, $rows, 'complete and without duplicates');
        $this->assertSame(
            array_column($events, 'client_event_id'),
            array_column($rows, 'client_event_id'),
            'order by (session, sequence_no) is the order the events happened',
        );

        $sequence = array_map('intval', array_column($rows, 'sequence_no'));
        $this->assertSame(range(40, 169), $sequence, 'sequence_no increases across pages');

        foreach ($rows as $i => $row) {
            $this->assertNotNull($row['clock_offset_ms'], 'clock_offset_ms set on every event');
            $this->assertEqualsWithDelta(-$skewMs, (int) $row['clock_offset_ms'], 5_000, 'offset = device clock error');

            // Corrected time = occurred_at + offset is the real time of the event
            $corrected = (float) (new DateTimeImmutable((string) $row['occurred_at']))->format('U.u') + ((int) $row['clock_offset_ms']) / 1000;
            $this->assertEqualsWithDelta(microtime(true) - 125 + $i * 0.923, $corrected, 5, 'corrected time');
        }
    }

    public function testDroppedReportIsWrittenOnceAndCannotBeSentAsAnEvent(): void
    {
        $session  = $this->session();
        $envelope = ['client_sent_at' => $this->iso(0), 'dropped' => 7, 'dropped_id' => 'b0c3a1f2-0000-4000-8000-000000000001'];

        $this->service()->ingest($session, [$this->event('page_hidden', 3, $this->iso(-1_000))], $envelope);
        $this->service()->ingest($session, [$this->event('page_visible', 4, $this->iso(-500))], $envelope);   // answer was lost: same report again

        $forged = $this->service()->ingest($session, [$this->event('events_dropped', 5, $this->iso(0), ['dropped' => 999])]);
        $this->assertSame('event_type_server_only', $forged['rejected'][0]['reason'] ?? null);

        $drops = array_values(array_filter($this->rows(), static fn (array $r): bool => $r['event_type'] === 'events_dropped'));
        $this->assertCount(1, $drops);
        $this->assertSame(['dropped' => 7, 'channel' => 'events'], json_decode((string) $drops[0]['payload_json'], true));

        foreach ([['dropped' => 0, 'dropped_id' => 'x'], ['dropped' => 'banyak', 'dropped_id' => 'y'], ['dropped' => 3]] as $invalid) {
            $this->assertFalse($this->service()->recordDropped($session, $invalid, 'events'));
        }
    }

    public function testPackageChangedIsAcceptedAndExportedInNavigation(): void
    {
        $result = $this->service()->ingest($this->session(), [
            $this->event('package_changed', 12, $this->iso(-1_000), ['from' => 'pkt-2026-10-20-01', 'to' => 'pkt-2026-10-27-01', 'reason' => 'evicted']),
        ], ['client_sent_at' => $this->iso(0)]);

        $this->assertSame(1, $result['accepted']);
        $this->assertContains('package_changed', config('Gelita')->eventTypes);

        $raw  = service('rawDataService');
        $rows = iterator_to_array($raw->rows('navigasi', [], StaffScope::admin(false)), false);
        $row  = array_values(array_filter($rows, static fn (array $r): bool => $r['event_type'] === 'package_changed'));

        $this->assertCount(1, $row);
        $this->assertSame(['from' => 'pkt-2026-10-20-01', 'to' => 'pkt-2026-10-27-01', 'reason' => 'evicted'], json_decode((string) $row[0]['payload'], true));
        $this->assertSame(12, (int) $row[0]['sequence_no']);
        $this->assertNotNull($row[0]['clock_offset_ms']);
        $this->assertContains('clock_offset_ms', $raw->columns('navigasi', StaffScope::admin(false)));
        $this->assertContains('clock_offset_ms', $raw->columns('event', StaffScope::admin(false)));
        $this->assertContains('package_changed', RawDataService::NAVIGATION_EVENTS);
        $this->assertContains('events_dropped', RawDataService::NAVIGATION_EVENTS);
    }

    /** Audio batches carry client_sent_at too; their raw event gets the offset. */
    public function testAudioEventsGetTheBatchClockOffset(): void
    {
        $this->conn->table('audio_assets')->insert([
            'media_asset_id' => 999, 'locale' => 'id', 'context_code' => 'uji', 'transcript' => 'Halo.',
        ]);
        $assetId = (int) $this->conn->insertID();

        $written = $this->service()->recordAudio($this->session(), [
            'audio_asset_id' => $assetId, 'action' => 'play', 'client_event_id' => 'aud-1', 'occurred_at' => $this->iso(-40_000),
        ], $this->iso(-30_000));

        $this->assertTrue($written);
        $this->assertEqualsWithDelta(30_000, (int) $this->rows()[0]['clock_offset_ms'], 5_000);
    }

    /** The API passes the batch envelope (core/events.js) to the service. */
    public function testEventApiReadsTheBatchEnvelope(): void
    {
        Services::resetSingle('security');
        Services::resetSingle('gameContext');

        $response = $this->withSession(['participant_id' => 1, 'game_session_id' => 1, 'gelita_csrf' => self::CSRF_HASH])
            ->withHeaders(['X-CSRF-TOKEN' => self::CSRF_HASH . str_repeat('0', 32)])
            ->withBodyFormat('json')
            ->call(Method::POST, 'api/events', [
                'events'         => [$this->event('page_hidden', 9, $this->iso(-61_000))],
                'client_sent_at' => $this->iso(-60_000),
                'dropped'        => 2,
                'dropped_id'     => 'r-1',
            ]);

        $response->assertOK();
        $this->assertSame(1, json_decode((string) $response->getJSON(), true)['data']['accepted'] ?? null);

        $rows = array_column($this->rows(), null, 'event_type');
        $this->assertEqualsWithDelta(60_000, (int) $rows['page_hidden']['clock_offset_ms'], 5_000);
        $this->assertSame(['dropped' => 2, 'channel' => 'events'], json_decode((string) $rows['events_dropped']['payload_json'], true));
    }

    /**
     * The next page's screen_viewed is written before the previous page's
     * beacon arrives; the browser's last number (cookie) keeps it after them.
     */
    public function testServerEventsFollowTheBrowsersLastSequenceNumber(): void
    {
        $this->service()->ingest($this->session(), [$this->event('page_hidden', 5, $this->iso(-2_000))], ['client_sent_at' => $this->iso(0)]);

        $request = service('request');

        try {
            // Events 6 and 7 are still in a beacon
            $request->setGlobal('cookie', [EventService::SEQUENCE_COOKIE => session_tag(1) . '.7']);
            $this->assertSame(8, event_sequence_floor(1));
            service('eventService')->record($this->session(), 'screen_viewed', ['screen' => 'map']);

            // Another session's cookie, or garbage, is ignored
            $request->setGlobal('cookie', [EventService::SEQUENCE_COOKIE => session_tag(2) . '.900']);
            $this->assertSame(9, event_sequence_floor(1));
            $request->setGlobal('cookie', [EventService::SEQUENCE_COOKIE => 'x.900']);
            $this->assertSame(9, event_sequence_floor(1));
        } finally {
            $request->setGlobal('cookie', []);
        }

        $this->service()->ingest($this->session(), [
            $this->event('answer_changed', 6, $this->iso(-1_500)),
            $this->event('page_hidden', 7, $this->iso(-1_000)),
        ], ['client_sent_at' => $this->iso(0)]);

        $this->assertSame(
            ['page_hidden', 'answer_changed', 'page_hidden', 'screen_viewed'],
            array_column($this->rows(), 'event_type'),
            'order by sequence_no is the order of events',
        );
    }

    /** A new tab continues after the events already on the server. */
    public function testJsConfigSequenceFloorIsTheSessionsNextNumber(): void
    {
        $this->service()->ingest($this->session(), [$this->event('page_hidden', 57, $this->iso(0))], ['client_sent_at' => $this->iso(0)]);

        session()->set(['participant_id' => 1, 'game_session_id' => 1]);

        try {
            $this->assertSame(58, js_config('id')['sequenceFloor']);
        } finally {
            session()->remove(['participant_id', 'game_session_id']);
        }

        $this->assertSame(0, js_config('id')['sequenceFloor'], 'no game session, no floor');
    }

    private function service(): EventService
    {
        return service('eventService');
    }

    private function session(): GameSession
    {
        return model(GameSessionModel::class)->find(1);
    }

    /** @return list<array<string, mixed>> */
    private function rows(): array
    {
        return $this->conn->table('game_event_logs')->where('session_id', 1)
            ->orderBy('sequence_no', 'ASC')->orderBy('id', 'ASC')->get()->getResultArray();
    }

    /** ISO 8601 UTC with milliseconds, `$ms` from now (core/events.js now()). */
    private function iso(int $ms): string
    {
        $at = microtime(true) + $ms / 1000;

        return gmdate('Y-m-d\TH:i:s', (int) floor($at)) . sprintf('.%03dZ', (int) floor(fmod($at, 1) * 1000));
    }

    /** @param array<string, mixed> $payload */
    private function event(string $type, int $sequence, string $occurredAt, array $payload = []): array
    {
        return [
            'client_event_id' => sprintf('c-%s-%05d', $type, $sequence),
            'event_type'      => $type,
            'occurred_at'     => $occurredAt,
            'sequence_no'     => $sequence,
            'payload'         => $payload ?: null,
        ];
    }
}
