<?php

use App\Entities\GameSession;
use App\Models\GameSessionModel;
use App\Services\FieldSessionService;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\FieldWorld;

/**
 * Field session codes, hours, and closing (research revision 10, part 2:
 * 28 §6.1, §6.2, §6.6, §6.7, §19.1 `FieldSessionLifecycleTest`).
 *
 * @internal
 */
final class FieldSessionLifecycleTest extends CIUnitTestCase
{
    use FieldWorld;

    private \CodeIgniter\Database\BaseConnection $conn;

    private const FUTURE = ['school_id' => 1, 'rombel' => 'A', 'meeting_no' => 2, 'session_date' => '2030-01-15', 'opens' => '07:30', 'closes' => '09:30'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createFieldWorld();
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testCodesForMainAndMakeupSessions(): void
    {
        $this->assertSame('S03-A-P1', FieldSessionService::sessionCode('S03', 'A', 1));
        $this->assertSame('S03-A-P3-S', FieldSessionService::sessionCode('S03', 'A', 3, 1));
        $this->assertSame('S03-A-P3-S2', FieldSessionService::sessionCode('S03', 'A', 3, 2));

        $service = service('fieldSessions');
        $main    = $this->fieldSession($this->conn, 3, 'GHJKMN', ['closed_at' => date('Y-m-d H:i:s')]);
        $hours   = ['session_date' => '2030-01-16', 'opens' => '07:30', 'closes' => '09:00'];

        $this->assertSame('S03-A-P3-S', $service->createMakeup($main['id'], $hours, [], 2)['field_session']['code']);
        $this->assertSame('S03-A-P3-S2', $service->createMakeup($main['id'], $hours, [], 2)['field_session']['code']);
    }

    public function testASecondMainSessionForTheSameMeetingIsRefused(): void
    {
        $service = service('fieldSessions');
        $created = $service->create(self::FUTURE, 2);

        $this->assertSame('S03-A-P2', $created['field_session']['code']);
        $this->assertSame('pending', $service->state($created['field_session']));
        $this->assertSame(1, $this->conn->table('participant_code_counters')->where('school_id', 1)->where('rombel', 'A')->countAllResults(), 'counter row exists once');
        $this->assertSame(1, $this->conn->table('audit_logs')->where('action', 'field_session_created')->countAllResults());

        try {
            $service->create(self::FUTURE, 2);
            $this->fail('duplicate accepted');
        } catch (\InvalidArgumentException $e) {
            $this->assertSame(lang('Admin.fieldSessionDuplicate'), $e->getMessage());
        }

        $this->expectException(\InvalidArgumentException::class);
        $service->create(['closes' => '12:00'] + self::FUTURE, 2);   // more than 4 hours
    }

    public function testHoursChangeAndEarlyClose(): void
    {
        $service = service('fieldSessions');
        $fs      = $this->fieldSession($this->conn, 2, 'GHJKMN', ['opens_at' => date('Y-m-d H:i:s', time() + 600), 'closes_at' => date('Y-m-d H:i:s', time() + 3600)]);

        $row = $service->updateHours($fs['id'], ['open_now' => true], 2);
        $this->assertSame('open', $service->state($row));
        $this->assertSame(1, $this->conn->table('audit_logs')->where('action', 'field_session_hours_changed')->countAllResults());

        $this->assertTrue($service->close($fs['id'], 2));
        $this->assertFalse($service->close($fs['id'], 2), 'a second click changes nothing');
        $this->assertSame('closed', $service->state(model(\App\Models\FieldSessionModel::class)->find($fs['id'])));
        $this->assertSame(1, $this->conn->table('audit_logs')->where('action', 'field_session_closed')->countAllResults());

        $this->expectException(\InvalidArgumentException::class);
        $service->updateHours($fs['id'], ['closes' => '23:59'], 2);
    }

    public function testClosingWritesOnePauseEvenForTwoRequestsAtOnce(): void
    {
        $fs = $this->fieldSession($this->conn, 2, 'GHJKMN');
        $this->conn->table('game_sessions')->where('id', 1)->update(['field_session_id' => $fs['id'], 'meeting_no' => 2, 'status' => 'active']);

        // Both requests read the session while it still ran (stale entity)
        $stale = model(GameSessionModel::class)->find(1);
        $this->assertInstanceOf(GameSession::class, $stale);

        $service = service('fieldSessions');
        $this->assertTrue($service->close($fs['id'], 2));
        $this->assertFalse($service->pauseForClosedSession($stale), 'the late request loses the conditional UPDATE');

        $row = $this->conn->table('game_sessions')->where('id', 1)->get()->getRowArray();
        $this->assertSame('paused', $row['status']);
        $this->assertNotNull($row['ended_at']);
        $this->assertSame(1, $this->conn->table('game_event_logs')->where('event_type', 'session_paused')->like('payload_json', 'field_session_closed')->countAllResults());

        // Retention leaves field-session sessions alone (28 §6.6)
        $this->conn->table('game_sessions')->where('id', 1)->update(['last_active_at' => '2020-01-01 00:00:00']);
        $this->assertSame(0, service('retentionService')->abandonStaleSessions(30));
    }
}
