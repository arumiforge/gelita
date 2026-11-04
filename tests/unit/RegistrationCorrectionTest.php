<?php

use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\ConsentWorld;

/**
 * The admin corrects age, gender, and class (research revision 10, part 4:
 * 30 §9, K13, §19.1 `RegistrationCorrectionTest`).
 *
 * @internal
 */
final class RegistrationCorrectionTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use ConsentWorld;

    private \CodeIgniter\Database\BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createConsentWorld();
        $this->conn->table('participants')->where('id', 1)->update(['age' => 10, 'gender' => 'laki-laki', 'class_level' => '5']);
        $this->asStaff('admin');
    }

    protected function tearDown(): void
    {
        $this->dropConsentWorld();
        parent::tearDown();
    }

    public function testOnlyAgeGenderAndClassChangeWithAReasonAndAudit(): void
    {
        $this->post(['age' => '11', 'gender' => 'perempuan', 'class_level' => '5', 'reason' => 'permintaan orang tua, T10 18 Nov 2027',
            'participant_code' => 'X99', 'school_id' => '2', 'rombel' => 'B', 'username' => 'lain']);

        $row = $this->conn->table('participants')->where('id', 1)->get()->getRowArray();
        $this->assertSame(['11', 'perempuan', 'S03-A01', '1', 'A', 'jaka5'], [(string) $row['age'], $row['gender'], $row['participant_code'], (string) $row['school_id'], $row['rombel'], $row['username']]);
        $this->assertNotNull($row['corrected_at']);

        $audit = json_decode((string) $this->conn->table('audit_logs')->where('action', 'participant_corrected')->get()->getRow('metadata_json'), true);
        $this->assertSame(['participant_code' => 'S03-A01', 'changes' => ['age' => [10, 11], 'gender' => ['laki-laki', 'perempuan']], 'reason' => 'permintaan orang tua, T10 18 Nov 2027'], $audit);
    }

    public function testRegistrationRulesAndTheReasonApply(): void
    {
        foreach ([['age' => '30'], ['gender' => 'lainnya'], ['class_level' => '7'], ['reason' => '']] as $bad) {
            $this->post($bad + ['age' => '11', 'gender' => 'laki-laki', 'class_level' => '5', 'reason' => 'alasan']);
            $this->assertNotEmpty($_SESSION['error'] ?? null, json_encode($bad));
        }

        $this->assertSame('10', (string) $this->conn->table('participants')->where('id', 1)->get()->getRow('age'));
        $this->assertSame(0, $this->conn->table('audit_logs')->where('action', 'participant_corrected')->countAllResults());
    }

    public function testAStalePageChangesNothing(): void
    {
        $this->post(['age' => '11', 'gender' => 'laki-laki', 'class_level' => '5', 'reason' => 'x', 'stamp' => '2000-01-01 00:00:00']);

        $this->assertSame(lang('Admin.consentStale'), $_SESSION['error'] ?? null);
        $this->assertSame('10', (string) $this->conn->table('participants')->where('id', 1)->get()->getRow('age'));
    }

    public function testOnlyTheAdmin(): void
    {
        $this->asStaff('ketua');
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->send(Method::POST, 'admin/peserta/1/betulkan', ['age' => '11']);
    }

    private function post(array $data): void
    {
        unset($_SESSION['error'], $_SESSION['message']);
        $this->asStaff('admin');
        $this->send(Method::POST, 'admin/peserta/1/betulkan', $data);
    }
}
