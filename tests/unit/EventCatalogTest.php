<?php

use App\Libraries\EventCatalog;
use App\Models\GameSessionModel;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\ResetTables;

/**
 * One owner per event type (research revision 10, part 3: 29 §4, K7).
 *
 * @internal
 */
final class EventCatalogTest extends CIUnitTestCase
{
    use ResetTables;

    protected function tearDown(): void
    {
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testEveryTypeHasExactlyOneOwner(): void
    {
        $removed = array_keys(EventCatalog::REMOVED);
        $all     = [...EventCatalog::RESEARCH, ...EventCatalog::ADMIN, ...$removed];

        $this->assertSame(count($all), count(array_unique($all)), 'no type in two lists');
        $this->assertSame([...EventCatalog::RESEARCH, ...EventCatalog::ADMIN], EventCatalog::accepted());

        foreach (EventCatalog::SERVER_ONLY as $type) {
            $this->assertContains($type, EventCatalog::accepted(), $type);
        }

        foreach ([...EventCatalog::NAVIGATION, ...EventCatalog::COMPLETION] as $type) {
            $this->assertTrue(EventCatalog::isResearch($type), $type . ' feeds a research sheet');
        }

        foreach (['dialogue_completed', 'passage_reopened', 'empty_tap'] as $type) {
            $this->assertTrue(EventCatalog::isResearch($type), $type);
        }

        $this->assertSame(['password_changed', 'viewport_changed', 'package_changed', 'events_dropped'], EventCatalog::ADMIN);
        $this->assertFalse(EventCatalog::isResearch('events_dropped'));
        $this->assertTrue(EventCatalog::isAdmin('viewport_changed'));
    }

    public function testRemovedTypesAreRefusedAsUnknown(): void
    {
        $db = $this->createResetTables(['participants', 'game_sessions', 'game_event_logs']);
        $db->table('participants')->insert(['participant_code' => 'GLT-000001', 'username' => 'a', 'password_hash' => 'x']);
        $db->table('game_sessions')->insert(['session_code' => 'S1', 'participant_id' => 1, 'study_id' => 1, 'release_id' => 1, 'locale' => 'id', 'status' => 'active']);
        $session = model(GameSessionModel::class)->find(1);

        $events = [];

        foreach (array_keys(EventCatalog::REMOVED) as $n => $type) {
            $events[] = ['event_type' => $type, 'client_event_id' => 'old-' . $n, 'occurred_at' => '2026-11-04T08:00:00.000Z'];
        }

        $events[] = ['event_type' => 'screen_viewed', 'client_event_id' => 'forged', 'occurred_at' => '2026-11-04T08:00:00.000Z'];

        $result = service('eventService')->ingest($session, $events);

        $this->assertSame(0, $result['accepted']);
        $this->assertSame(['event_type_unknown', 'event_type_unknown', 'event_type_unknown', 'event_type_unknown', 'event_type_server_only'], array_column($result['rejected'], 'reason'));
        $this->assertSame(0, $db->table('game_event_logs')->countAllResults(), 'a refused queued event is not counted as events_dropped');

        $this->expectException(InvalidArgumentException::class);
        service('eventService')->record($session, 'answer_changed');
    }
}
