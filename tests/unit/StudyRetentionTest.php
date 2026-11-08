<?php

use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\ConsentWorld;

/**
 * Retention of research studies (research revision 10, part 4: 30 §0.1, §11,
 * K10, §19.1 `StudyRetentionTest`).
 *
 * @internal
 */
final class StudyRetentionTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use ConsentWorld;

    private \CodeIgniter\Database\BaseConnection $conn;

    private array $form;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createConsentWorld();
        $study      = $this->conn->table('research_studies')->get()->getRowArray();
        $this->form = ['code' => $study['code'], 'name' => $study['name'], 'status' => 'active', 'default_locale' => 'id', 'unlock_mode' => 'sequential', 'meeting_plan' => 'lima_pertemuan', 'confirm_not_ready' => '1'];
    }

    protected function tearDown(): void
    {
        $this->dropConsentWorld();
        parent::tearDown();
    }

    public function testDefaultsAndOldRowsStay(): void
    {
        $this->assertSame(1825, (int) $this->conn->table('research_studies')->get()->getRow('retention_days'), 'the seeded study keeps 1825');
        $this->conn->table('research_studies')->insert(['code' => 'BARU', 'name' => 'Baru', 'status' => 'draft']);
        $this->assertSame(1825, (int) $this->conn->table('research_studies')->where('code', 'BARU')->get()->getRow('retention_days'), 'the column default stays');
    }

    public function testFieldModeFormFillsAndGuards7305Days(): void
    {
        $this->save(['field_mode' => '1', 'retention_days' => '']);
        $this->assertSame(7305, (int) $this->conn->table('research_studies')->get()->getRow('retention_days'));

        $this->save(['field_mode' => '1', 'retention_days' => '1825']);
        $this->assertSame(lang('Admin.retentionMinStudy'), $_SESSION['error'] ?? null);

        config('Gelita')->researchPrivacy = false;
        $this->save(['field_mode' => '1', 'retention_days' => '7305']);
        $this->assertSame(lang('Admin.researchPrivacyRequired'), $_SESSION['error'] ?? null);

        $this->save(['retention_days' => '365']);
        $this->assertSame(365, (int) $this->conn->table('research_studies')->get()->getRow('retention_days'), 'no field mode: as before');
    }

    public function testClosingFillsClosedAtAndOpeningEmptiesIt(): void
    {
        $this->save(['field_mode' => '1', 'retention_days' => '7305', 'status' => 'closed']);
        $this->assertNotNull($this->conn->table('research_studies')->get()->getRow('closed_at'));

        $this->save(['field_mode' => '1', 'retention_days' => '7305', 'status' => 'active']);
        $this->assertNull($this->conn->table('research_studies')->get()->getRow('closed_at'));
    }

    public function testAFieldModeStudyCountsFromItsClosing(): void
    {
        $this->consent($this->conn, 1);
        $this->conn->table('game_sessions')->update(['started_at' => '2000-01-01 08:00:00']);
        $this->conn->table('research_studies')->update(['retention_days' => 7305, 'field_mode' => 1]);

        $this->assertSame([], service('retentionService')->flagExpiredStudyData(1)['previews'], 'not closed: never a preview');

        $this->conn->table('research_studies')->update(['status' => 'closed', 'closed_at' => date('Y-m-d H:i:s', strtotime('-7000 days'))]);
        $this->assertSame([], service('retentionService')->flagExpiredStudyData(1)['previews'], 'closed less than 7305 days ago');

        $this->conn->table('research_studies')->update(['closed_at' => date('Y-m-d H:i:s', strtotime('-7400 days'))]);
        $previews = service('retentionService')->flagExpiredStudyData(1)['previews'];
        $this->assertCount(1, $previews);

        $counts = service('retentionService')->decode(model(App\Models\DataDeletionRequestModel::class)->find($previews[0]['request_id']))['counts'];
        $this->assertSame([1, 1], [$counts['participants'], $counts['participant_consents']], 'the whole cohort, students included');
    }

    public function testOtherStudiesStillCountFromTheSessionStart(): void
    {
        $this->conn->table('research_studies')->update(['retention_days' => 30, 'field_mode' => 0]);
        $this->conn->table('game_sessions')->update(['started_at' => date('Y-m-d H:i:s', strtotime('-40 days'))]);

        $previews = service('retentionService')->flagExpiredStudyData(1)['previews'];
        $this->assertCount(1, $previews);
        $counts = service('retentionService')->decode(model(App\Models\DataDeletionRequestModel::class)->find($previews[0]['request_id']))['counts'];
        $this->assertSame(0, $counts['participants'], 'sessions only, as before');
    }

    public function testGovernanceWarnsWithoutTheSecondBackupLimit(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'bs');
        $this->assertSame(lang('Admin.backupSecondDaysMissing'), App\Controllers\Admin\GovernanceController::backupWarning($file . '-none'));

        file_put_contents($file, json_encode(['ran_at' => date('c'), 'kedua_hari' => 30]));
        $this->assertNull(App\Controllers\Admin\GovernanceController::backupWarning($file));
        unlink($file);
    }

    private function save(array $values): void
    {
        unset($_SESSION['error'], $_SESSION['message']);
        $this->asStaff('ketua');
        $this->send(Method::POST, 'admin/studi/1', $values + $this->form);
    }
}
