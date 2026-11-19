<?php

use App\Libraries\ResearchModeNotice;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\GradeFiveWorld;

/**
 * The research-mode notice on every panel page (owner, 19 November 2026):
 * off by default, on with the study's name once the running study has
 * `field_mode`, and the same table of differences for every staff role.
 *
 * @internal
 */
final class ResearchModeNoticeTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use GradeFiveWorld;

    private \CodeIgniter\Database\BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createGradeFiveWorld();
        $this->conn->table('staff_users')->insert(['id' => 13, 'username' => 'ketua_notice_uji', 'password_hash' => 'x', 'role' => 'ketua', 'display_name' => 'Ketua', 'is_active' => 1]);
        $this->conn->table('staff_users')->insert(['id' => 14, 'username' => 'peneliti_notice_uji', 'password_hash' => 'x', 'role' => 'peneliti', 'display_name' => 'Peneliti', 'is_active' => 1]);
        $this->conn->table('staff_users')->insert(['id' => 15, 'username' => 'admin_notice_uji', 'password_hash' => 'x', 'role' => 'admin', 'display_name' => 'Admin', 'is_active' => 1]);
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testRowsNameTheStudyScreenThreshold(): void
    {
        $rows = ResearchModeNotice::rows(['min_stage_px' => 1024]);

        $this->assertCount(10, $rows);
        $this->assertStringContainsString('1.024', $rows[4][2]);
        $this->assertFalse(ResearchModeNotice::build(['field_mode' => 0])['on']);
        $this->assertTrue(ResearchModeNotice::build(['field_mode' => 1])['on']);
        $this->assertFalse(ResearchModeNotice::build(null)['on']);
    }

    public function testEveryRoleSeesOffThenOn(): void
    {
        foreach ([13 => 'ketua', 14 => 'peneliti', 15 => 'admin'] as $id => $role) {
            $page = $this->dashboard($id, $role);
            $this->assertStringContainsString('data-research-mode="off"', $page, $role);
            $this->assertStringContainsString(lang('Admin.researchModePillOff'), $page, $role);
            $this->assertStringContainsString(esc(lang('Admin.researchModeRowEntryOn')), $page, $role);
        }

        $study = $this->conn->table('research_studies')->where('status', 'active')->get()->getRowArray();
        $this->assertNotNull($study, 'the grade-five world has a running study');
        $this->conn->table('research_studies')->where('id', $study['id'])->update(['field_mode' => 1]);
        service('fieldSessions')->forget();

        foreach ([13 => 'ketua', 14 => 'peneliti', 15 => 'admin'] as $id => $role) {
            $page = $this->dashboard($id, $role);
            $this->assertStringContainsString('data-research-mode="on"', $page, $role);
            $this->assertStringContainsString(lang('Admin.researchModePillOn'), $page, $role);
            $this->assertStringContainsString(esc(lang('Admin.researchModeOnTitle', [$study['name']])), $page, $role);
        }
    }

    public function testNoNoticeOnTheLoginPage(): void
    {
        $page = (string) $this->call(Method::GET, 'admin/login')->getBody();

        $this->assertStringNotContainsString('data-research-mode', $page);
    }

    private function dashboard(int $id, string $role): string
    {
        $_SESSION = ['staff_id' => $id, 'staff_role' => $role, 'staff_last_seen' => time()];
        Config\Services::resetSingle('renderer');

        return (string) $this->withSession($_SESSION)->call(Method::GET, 'admin')->getBody();
    }
}
