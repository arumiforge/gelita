<?php

use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\ConsentWorld;

/**
 * Study closure: conditions, the feedback name check, and names and schools
 * replaced with codes (research revision 10, part 4: 30 §10.2, K11,
 * §19.1 `StudyClosureTest`).
 *
 * @internal
 */
final class StudyClosureTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use ConsentWorld;

    private \CodeIgniter\Database\BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createConsentWorld();
        $this->consent($this->conn, 1);
        $this->playData($this->conn, 1, 1);
        $this->conn->table('participant_feedback')->update(['suggestion' => 'Jaka suka main bersama Dewi', 'liked_most' => 'Petanya']);
        $this->conn->table('participants')->where('id', 1)->update(['password_hash' => password_hash('Kopi-Kedu7!', PASSWORD_DEFAULT)]);
        $this->fieldSession($this->conn, 2, 'GHJKMN');
    }

    protected function tearDown(): void
    {
        $this->dropConsentWorld();
        parent::tearDown();
    }

    public function testConditionsHoldTheButton(): void
    {
        $this->asStaff('admin');
        $this->assertStringContainsString('disabled', (string) $this->send(Method::GET, 'admin/studi/1/penutupan')->getBody());
        $this->send(Method::POST, 'admin/studi/1/penutupan/hitung');
        $this->assertSame(lang('Admin.closureNotReady'), $_SESSION['error'] ?? null);

        $this->close();
        $this->assertSame(['closed' => true, 'requests_done' => true, 'register_current' => true, 'feedback_checked' => false], service('studyClosure')->conditions($this->study()));

        // A request still open holds the closure too
        $this->conn->table('data_deletion_requests')->insert(['requested_by' => 2, 'scope_json' => '{}', 'kind' => 'recording', 'participant_code' => 'S03-A01', 'status' => 'requested']);
        $this->assertFalse(service('studyClosure')->conditions($this->study())['requests_done']);
    }

    public function testTheNameCheckMasksWithoutShowingNames(): void
    {
        $this->asStaff('ketua');
        $page = (string) $this->send(Method::GET, 'admin/studi/1/penutupan/nama')->getBody();
        $this->assertStringContainsString('[nama] suka main bersama Dewi', $page);
        $this->assertStringNotContainsString('Jaka', $page);

        $id = (int) $this->conn->table('participant_feedback')->get()->getRow('id');
        $this->send(Method::POST, 'admin/studi/1/penutupan/nama', ['texts' => [$id => ['suggestion' => 'Jaka suka main bersama [nama]']]]);

        $this->assertSame('[nama] suka main bersama [nama]', $this->conn->table('participant_feedback')->get()->getRow('suggestion'), 'a typed name is masked again');
        $this->assertSame('Petanya', $this->conn->table('participant_feedback')->get()->getRow('liked_most'));
        $this->assertNotNull($this->study()['feedback_names_checked_at']);
        $audit = json_decode((string) $this->conn->table('audit_logs')->where('action', 'feedback_names_masked')->get()->getRow('metadata_json'), true);
        $this->assertSame(['study_code' => $this->study()['code'], 'rows' => 1], $audit);
    }

    public function testGantiReplacesNamesUsernamesPasswordsAndSchools(): void
    {
        $this->close();
        $this->conn->table('research_studies')->update(['feedback_names_checked_at' => date('Y-m-d H:i:s')]);

        $this->asStaff('admin');
        $this->send(Method::POST, 'admin/studi/1/penutupan/hitung');
        $this->assertSame(['participants' => 1, 'schools' => 1], $_SESSION['closure_preview'] ?? null);

        $this->send(Method::POST, 'admin/studi/1/penutupan/jalankan', ['confirm' => 'ganti']);
        $this->assertNull($this->study()['identities_replaced_at'], 'GANTI in capitals');

        $this->asStaff('admin');
        $this->send(Method::POST, 'admin/studi/1/penutupan/jalankan', ['confirm' => 'GANTI']);

        $row = $this->conn->table('participants')->where('id', 1)->get()->getRowArray();
        $this->assertSame(['S03-A01', 's03-a01', 'S03'], [$row['display_name'], $row['username'], $row['school_name_snapshot']]);
        $this->assertFalse(password_verify('Kopi-Kedu7!', $row['password_hash']));
        $this->assertNotNull($row['deactivated_at']);

        $coded = $this->conn->table('schools')->where('research_code', 'S03')->get()->getRowArray();
        $this->assertSame(['S03', null], [$coded['name'], $coded['code']]);
        $this->assertSame((int) $coded['id'], (int) $row['school_id']);
        $this->assertNull($this->conn->table('schools')->where('id', 1)->get()->getRow('research_code'), 'the named school stays without its code');
        $this->assertSame('SD Lapangan', $this->conn->table('schools')->where('id', 1)->get()->getRow('name'));
        $this->assertSame(0, $this->conn->table('field_sessions')->where('school_id', 1)->countAllResults());
        $this->assertSame((int) $coded['id'], (int) $this->conn->table('participant_code_counters')->get()->getRow('school_id'));
        $this->assertNotNull($this->study()['identities_replaced_at']);
        $audit = json_decode((string) $this->conn->table('audit_logs')->where('action', 'study_identities_replaced')->get()->getRow('metadata_json'), true);
        $this->assertSame(['participants' => 1, 'schools' => 1], array_intersect_key($audit, ['participants' => 0, 'schools' => 0]));

        // Afterwards the data can no longer be linked to a child
        $this->assertSame(lang('Admin.closureReplaced'), service('dataRequests')->create(['participant_code' => 'S03-A01', 'kind' => 'all', 'received_at' => date('Y-m-d H:i', time() - 60), 'received_via' => 'paper', 'verified' => '1', 'verified_at' => date('Y-m-d H:i', time() - 60)], 3, 'ketua')['message'] ?? null);

        try {
            service('participantCopy')->build(1, 'copy', 'none', 3);
            $this->fail('copy refused');
        } catch (\DomainException $e) {
            $this->assertSame(lang('Admin.closureReplaced'), $e->getMessage());
        }

        unset($_SESSION['error']);
        $this->asStaff('admin');
        $this->send(Method::POST, 'admin/peserta/1/betulkan', ['age' => '11', 'gender' => 'laki-laki', 'class_level' => '5', 'reason' => 'x']);
        $this->assertSame(lang('Admin.closureReplaced'), $_SESSION['error'] ?? null);
    }

    private function close(): void
    {
        $now = date('Y-m-d H:i:s', time() + 1);
        $this->conn->table('research_studies')->update(['status' => 'closed', 'closed_at' => $now, 'consent_register_exported_at' => $now]);
    }

    /** @return array<string, mixed> */
    private function study(): array
    {
        return $this->conn->table('research_studies')->where('id', 1)->get()->getRowArray();
    }
}
