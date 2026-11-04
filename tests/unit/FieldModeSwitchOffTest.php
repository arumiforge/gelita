<?php

use App\Controllers\Admin\GovernanceController;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\GradeFiveWorld;

/**
 * Without a field-mode study everything stays as before part 4 (research
 * revision 10, part 4: 30 §0.1, §19.1 `FieldModeSwitchOffTest`).
 *
 * @internal
 */
final class FieldModeSwitchOffTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use GradeFiveWorld;

    private \CodeIgniter\Database\BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createGradeFiveWorld();
        $this->conn->table('staff_users')->insert(['id' => 3, 'username' => 'ketua_uji', 'password_hash' => 'x', 'role' => 'ketua', 'display_name' => 'Ketua', 'is_active' => 1]);
        $this->conn->table('participants')->where('id', 1)->update(['password_hash' => password_hash('Kopi-Kedu7!', PASSWORD_DEFAULT)]);
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testRegistrationLoginMenuGovernanceAndSessionsAsBefore(): void
    {
        $result = service('sessionService')->registerAndStart([
            'username' => 'bayu_aji', 'password' => 'Kopi-Kedu7!', 'assent' => true, 'country_code' => 'MY', 'school_name' => 'SD Luar',
        ]);
        $this->assertSame(0, $this->conn->table('participant_consents')->countAllResults(), 'no consent row');
        $this->assertSame('umum-1', $result['participant']->assent_version);

        $this->assertSame('ok', service('sessionService')->login('jaka5', 'Kopi-Kedu7!', 'id', [])['status']);

        $_SESSION = ['staff_id' => 3, 'staff_role' => 'ketua', 'staff_last_seen' => time()];
        $page     = (string) $this->withSession($_SESSION)->call(Method::GET, 'admin')->getBody();
        $this->assertStringNotContainsString(lang('Admin.menuDataRequests'), $page);
        $this->assertStringNotContainsString(lang('Admin.menuRiskFollowUp'), $page);

        $this->assertNull(GovernanceController::backupWarning('/nonexistent'));
        $this->assertSame(CodeIgniter\Session\Handlers\DatabaseHandler::class, (new Config\Session())->driver);
    }
}
