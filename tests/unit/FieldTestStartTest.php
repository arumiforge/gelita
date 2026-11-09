<?php

use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\InstrumentWorld;

/**
 * Mulai tes awal/akhir and Tambah waktu on the field-session page (research
 * revision 10, part 8: 34 §5.3, §5.4, §7.4, K1, K3, K10, §15.1 `FieldTestStartTest`).
 *
 * @internal
 */
final class FieldTestStartTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use InstrumentWorld;

    private \CodeIgniter\Database\BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createInstrumentWorld();
    }

    protected function tearDown(): void
    {
        $this->dropInstrumentWorld();
        parent::tearDown();
    }

    public function testThePreTestWaitsUntilEveryStudentHasAForm(): void
    {
        $id = $this->meeting($this->conn, 1, false);
        $this->student($this->conn, 'S03-A02', 'dewi');

        $this->asStaff('peneliti');
        $page = (string) $this->go(Method::GET, 'admin/sesi-lapangan/' . $id)->getBody();
        $this->assertStringContainsString(esc(lang('Admin.fieldTestNeedSeats')), $page);
        $this->assertStringContainsString(esc(lang('Admin.fieldTestStartPre')), $page);

        $this->go(Method::POST, 'admin/sesi-lapangan/' . $id . '/mulai-tes');
        $this->assertNull($this->conn->table('field_sessions')->where('id', $id)->get()->getRow('test_started_at'));

        $this->conn->table('participant_seats')->insert(['participant_id' => 2, 'study_id' => 1, 'row_no' => 1, 'seat_no' => 2, 'booklet' => 'B2', 'pre_form' => 'B', 'post_form' => 'A']);
        $this->go(Method::POST, 'admin/sesi-lapangan/' . $id . '/mulai-tes');
        $row = $this->conn->table('field_sessions')->where('id', $id)->get()->getRowArray();
        $this->assertNotNull($row['test_started_at']);
        $this->assertSame(2, (int) $row['test_started_by']);
        $audit = $this->conn->table('audit_logs')->where('action', 'field_test_started')->get()->getRowArray();
        $this->assertSame(['field_session_code' => 'S03-A-P1', 'occasion' => 'pre'], json_decode($audit['metadata_json'], true));

        // The conditional UPDATE: a second press changes nothing
        $first = $row['test_started_at'];
        $this->go(Method::POST, 'admin/sesi-lapangan/' . $id . '/mulai-tes');
        $this->assertSame($first, $this->conn->table('field_sessions')->where('id', $id)->get()->getRow('test_started_at'));
    }

    public function testThePostTestClosesTheChallenges(): void
    {
        $id   = $this->meeting($this->conn, 5, false);
        $node = $this->nodeId($this->conn, 'tmg-1');
        $this->assertNotSame(site_url('tes'), $this->go(Method::GET, 'peta')->getRedirectUrl(), 'leftovers may be played before the button (§5.4)');

        $this->asStaff('peneliti');
        $this->go(Method::POST, 'admin/sesi-lapangan/' . $id . '/mulai-tes');
        $this->assertNotNull($this->conn->table('field_sessions')->where('id', $id)->get()->getRow('test_started_at'));

        $this->signIn(1, 1);
        $this->assertRedirectsTo('tes', $this->go(Method::GET, 'peta'));
        $this->assertRedirectsTo('tes', $this->go(Method::GET, 'tantangan/temanggung/1'));

        $api = $this->go(Method::POST, 'api/nodes/' . $node . '/attempts');
        $this->assertSame(409, $api->response()->getStatusCode());
        $this->assertStringContainsString('INSTRUMENT_STARTED', (string) $api->getBody());
        $this->assertSame(0, $this->conn->table('challenge_attempts')->countAllResults(), 'nothing opened');
    }

    public function testExtraMinutesMoveTheLimitAndAreAudited(): void
    {
        $id = $this->meeting($this->conn, 1);
        $this->go(Method::POST, 'tes/mulai');
        $this->go(Method::POST, 'tes/bagian/mb/mulai');

        $this->asStaff('peneliti');
        $this->go(Method::POST, 'admin/sesi-lapangan/' . $id . '/tambah-waktu', ['participant_code' => 'S03-A01', 'block' => 'p1', 'minutes' => '5', 'reason' => 'perangkat mati']);
        $this->assertSame(5, (int) $this->conn->table('instrument_blocks')->get()->getRow('extra_minutes'));
        $audit = json_decode((string) $this->conn->table('audit_logs')->where('action', 'instrument_time_extended')->get()->getRow('metadata_json'), true);
        $this->assertSame(['participant_code' => 'S03-A01', 'occasion' => 'pre', 'block' => 'p1', 'minutes' => 5, 'reason' => 'perangkat mati'], $audit);

        // No reason, too many minutes, or a block not started: refused
        $this->go(Method::POST, 'admin/sesi-lapangan/' . $id . '/tambah-waktu', ['participant_code' => 'S03-A01', 'block' => 'p1', 'minutes' => '5', 'reason' => '']);
        $this->go(Method::POST, 'admin/sesi-lapangan/' . $id . '/tambah-waktu', ['participant_code' => 'S03-A01', 'block' => 'p1', 'minutes' => '26', 'reason' => 'x']);
        $this->go(Method::POST, 'admin/sesi-lapangan/' . $id . '/tambah-waktu', ['participant_code' => 'S03-A01', 'block' => 'p23', 'minutes' => '2', 'reason' => 'x']);
        $this->assertSame(5, (int) $this->conn->table('instrument_blocks')->get()->getRow('extra_minutes'));
        $this->assertSame(1, $this->conn->table('audit_logs')->where('action', 'instrument_time_extended')->countAllResults());
    }
}
