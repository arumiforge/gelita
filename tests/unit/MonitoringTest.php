<?php

use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\FieldWorld;

/**
 * The monitoring panel (research revision 10, part 2: 28 §12, K2,
 * §19.1 `MonitoringTest`).
 *
 * @internal
 */
final class MonitoringTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use FieldWorld;

    private \CodeIgniter\Database\BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createFieldWorld();

        // Student 1 is in and active, dewi came and left, eko never came
        $dewi = $this->student($this->conn, 'S03-A02', 'dewi');
        $this->student($this->conn, 'S03-A03', 'eko');
        $fs = $this->fieldSession($this->conn, 2, 'GHJKMN');
        $this->conn->table('game_sessions')->insertBatch([
            ['session_code' => 'F1', 'participant_id' => 1, 'study_id' => 1, 'release_id' => 1, 'field_session_id' => $fs['id'], 'meeting_no' => 2, 'status' => 'active', 'last_active_at' => date('Y-m-d H:i:s')],
            ['session_code' => 'F2', 'participant_id' => $dewi, 'study_id' => 1, 'release_id' => 1, 'field_session_id' => $fs['id'], 'meeting_no' => 2, 'status' => 'paused', 'last_active_at' => date('Y-m-d H:i:s', time() - 3600)],
        ]);
        $f1 = (int) $this->conn->table('game_sessions')->where('session_code', 'F1')->get()->getRow('id');
        $this->conn->table('game_event_logs')->insertBatch([
            ['event_uuid' => 'd1', 'client_event_id' => 'c1', 'session_id' => $f1, 'participant_id' => 1, 'event_type' => 'events_dropped', 'payload_json' => '{"dropped":4}', 'occurred_at' => date('Y-m-d H:i:s')],
            ['event_uuid' => 'd2', 'client_event_id' => 'c2', 'session_id' => $f1, 'participant_id' => 1, 'event_type' => 'events_dropped', 'payload_json' => '{"dropped":3}', 'occurred_at' => date('Y-m-d H:i:s')],
        ]);
        $this->conn->table('participant_seats')->insert([
            'participant_id' => 1, 'study_id' => 1, 'row_no' => 1, 'seat_no' => 1, 'booklet' => 'B1', 'pre_form' => 'A', 'post_form' => 'B',
        ]);
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testCountsForEveryViewer(): void
    {
        $row = service('analyticsService')->monitoring(1, false)[0];

        $this->assertSame('S03-A-P2', $row['code']);
        $this->assertSame([2, 1, 1, 7, 2], [$row['entered'], $row['active'], $row['not_entered'], $row['dropped'], $row['no_seat']]);
        $this->assertArrayNotHasKey('students', $row, 'admin: numbers only (K2)');
        $this->assertArrayNotHasKey('token', $row);
    }

    public function testResearchersSeeCodesAndTokens(): void
    {
        $_SESSION = ['staff_id' => 2, 'staff_role' => 'peneliti', 'staff_last_seen' => time()];
        $page     = (string) $this->send(Method::GET, 'admin/pemantauan')->getBody();
        $this->assertStringContainsString('S03-A03', $page, 'not entered: material for the make-up');
        $this->assertStringContainsString('GHJKMN', $page, 'the token');

        $api = (string) $this->send(Method::GET, 'api/admin/monitoring')->getBody();
        $this->assertStringContainsString('S03-A01', $api);
    }

    public function testTheAdminSeesNumbersOnly(): void
    {
        $_SESSION = ['staff_id' => 1, 'staff_role' => 'admin', 'staff_last_seen' => time()];
        $page     = $this->send(Method::GET, 'admin/pemantauan');
        $page->assertOK();
        $html = (string) $page->getBody();
        $this->assertStringContainsString('S03-A-P2', $html);
        $this->assertStringNotContainsString('S03-A03', $html);
        $this->assertStringNotContainsString('GHJKMN', $html);
        $this->assertStringNotContainsString('S03-A01', (string) $this->send(Method::GET, 'api/admin/monitoring')->getBody());
    }
}
