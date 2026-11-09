<?php

use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\ConsentWorld;

/**
 * Part 9 (35 §5, §11, §13; 16.1 StudyThresholdsTest): the head researcher
 * locks the thresholds with the audit `study_thresholds_locked`; afterwards
 * part 7's settings refuse both thresholds; researchers and admins get 404;
 * *Hitung ulang* and its status are the head researcher's too.
 *
 * @internal
 */
final class StudyThresholdsTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use ConsentWorld;

    private \CodeIgniter\Database\BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createConsentWorld();
        Config\Services::resetSingle('derivedData');
    }

    protected function tearDown(): void
    {
        $this->dropConsentWorld();
        parent::tearDown();
    }

    public function testHeadResearcherLocksOnceWithAnAudit(): void
    {
        $this->post('ketua', 'admin/studi/1/ambang/kunci');

        $this->assertSame(lang('Admin.studyThresholdsLockDone'), $_SESSION['message'] ?? null);
        $this->assertNotNull($this->study()['thresholds_locked_at']);
        $audit = $this->conn->table('audit_logs')->where('action', 'study_thresholds_locked')->get()->getRowArray();
        $this->assertSame(['idle_threshold_ms' => 180000, 'rapid_guess_ms' => 3000, 'test_rapid_guess_ms' => 3000], json_decode((string) $audit['metadata_json'], true));

        // Part 7's settings refuse both thresholds now
        $this->post('ketua', 'admin/studi/1/pengaturan', ['rapid_guess_s' => '2']);
        $this->assertSame(lang('Admin.studyThresholdsLocked'), $_SESSION['error'] ?? null);
        $this->assertSame(3000, (int) $this->study()['rapid_guess_ms']);

        // A second lock changes nothing
        $this->post('ketua', 'admin/studi/1/ambang/kunci');
        $this->assertSame(1, $this->conn->table('audit_logs')->where('action', 'study_thresholds_locked')->countAllResults());

        $this->asStaff('ketua');
        $page = (string) $this->send(Method::GET, 'admin/studi')->getBody();
        $this->assertStringContainsString(esc(lang('Admin.derivedTitle')), $page);
        $this->assertStringNotContainsString('data-thresholds-lock', $page, 'no lock button once locked');
    }

    public function testResearcherAndAdminGetNotFound(): void
    {
        foreach (['peneliti', 'admin'] as $role) {
            foreach (['admin/studi/1/ambang/kunci', 'admin/studi/1/hitung-ulang'] as $path) {
                try {
                    $this->post($role, $path);
                    $this->fail("{$role} {$path}");
                } catch (CodeIgniter\Exceptions\PageNotFoundException) {
                    // staffCan hides it
                }
            }
        }

        $this->assertNull($this->study()['thresholds_locked_at']);
        $this->assertSame(0, $this->conn->table('derived_runs')->countAllResults());

        $this->asStaff('admin');
        $page = (string) $this->send(Method::GET, 'admin/studi')->getBody();
        $this->assertStringNotContainsString('data-derived-recompute', $page);
        $this->assertStringNotContainsString('data-thresholds-lock', $page);
    }

    public function testRecomputeAndStatus(): void
    {
        $this->post('ketua', 'admin/studi/1/hitung-ulang');
        $this->assertSame(lang('Admin.derivedDone'), $_SESSION['message'] ?? null);

        $this->asStaff('ketua');
        $json = json_decode((string) $this->send(Method::GET, 'admin/studi/1/hitung-ulang/status')->response()->getBody(), true);
        $this->assertTrue($json['field_mode']);
        $this->assertFalse($json['stale']);
        $this->assertSame((int) $this->study()['derived_run_id'], $json['current']['run_id']);

        // Busy while another run holds the claim
        $this->conn->table('research_studies')->update(['derived_running_since' => date('Y-m-d H:i:s')]);
        $this->post('ketua', 'admin/studi/1/hitung-ulang');
        $this->assertSame(lang('Admin.derivedRunning'), $_SESSION['error'] ?? null);
    }

    public function testDerivedTabsOnTheRawDataScreen(): void
    {
        $this->post('ketua', 'admin/studi/1/hitung-ulang');
        $this->asStaff('ketua');

        foreach (array_keys(App\Libraries\DerivedSheets::TABLES) as $sheet) {
            Config\Services::resetSingle('rawDataService');   // a shared instance would skip the staff scope
            $page = (string) $this->send(Method::GET, 'admin/data/' . $sheet)->response()->getBody();
            $this->assertStringContainsString('data-derived-status', $page, $sheet);
        }

        // research.browse stays the ketua's (ResearcherPanelTest checks /admin/data for the other roles)
    }

    private function study(): array
    {
        return $this->conn->table('research_studies')->where('id', 1)->get()->getRowArray();
    }

    private function post(string $role, string $path, array $values = []): void
    {
        unset($_SESSION['error'], $_SESSION['message']);
        $this->asStaff($role);
        $this->send(Method::POST, $path, $values);
    }
}
