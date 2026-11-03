<?php

use App\Models\StaffUserModel;
use CodeIgniter\Config\BaseService;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;
use Tests\Support\Database\ResetTables;

/**
 * Staff accounts page with the three roles `admin`, `peneliti`, `ketua`
 * (revision 10, part 1: docs/revisi-10-analitik/27_IMPLEMENTASI_PERAN_PENELITI.md
 * §11, §14.1 `StaffRolesTest`).
 *
 * The signed-in admin is staff 7; staff 8 is a `peneliti`.
 *
 * @internal
 */
final class StaffRolesTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use ResetTables;

    private const CSRF_HASH = '0123456789abcdef0123456789abcdef';
    private const CSRF      = ['gelita_csrf' => self::CSRF_HASH . '00000000000000000000000000000000'];
    private const PASSWORD  = 'Kuat#Sandi2026x';

    protected function setUp(): void
    {
        parent::setUp();

        $this->createResetTables();
        Services::resetSingle('security');

        db_connect()->table('staff_users')->insertBatch([
            ['id' => 7, 'username' => 'admin_uji', 'password_hash' => password_hash(self::PASSWORD, PASSWORD_DEFAULT), 'role' => 'admin', 'display_name' => 'Admin Uji'],
            ['id' => 8, 'username' => 'peneliti_uji', 'password_hash' => 'x', 'role' => 'peneliti', 'display_name' => 'Peneliti Uji'],
        ]);
    }

    protected function tearDown(): void
    {
        $this->injectStaff(false);
        $this->dropResetTables();

        parent::tearDown();
    }

    public function testAdminCreatesResearcherAndLeadAccounts(): void
    {
        foreach (['peneliti' => 'peneliti_baru', 'ketua' => 'ketua_baru'] as $role => $username) {
            $this->post('admin/staf', $this->newAccount($username, $role))->assertRedirectTo(site_url('admin/staf'));

            $row = db_connect()->table('staff_users')->where('username', $username)->get()->getRowArray();
            $this->assertNotNull($row, $role);
            $this->assertSame($role, $row['role']);
            $this->assertSame(1, (int) $row['must_change_password']);
        }

        $this->assertSame(2, db_connect()->table('audit_logs')->where('action', 'staff_create')->countAllResults());
    }

    public function testTeacherAndEmptyRolesAreRefused(): void
    {
        foreach (['guru', ''] as $role) {
            $result = $this->post('admin/staf', $this->newAccount('akun_' . ($role ?: 'kosong'), $role));
            $result->assertRedirectTo(site_url('admin/staf'));
            $this->assertArrayHasKey('role', (array) session('errors'), "role '{$role}'");
        }

        $this->assertSame(2, db_connect()->table('staff_users')->countAllResults());

        $this->post('admin/staf/8', ['display_name' => 'Peneliti Uji', 'role' => 'guru', 'is_active' => '1'])
            ->assertRedirectTo(site_url('admin/staf'));
        $this->assertSame('peneliti', db_connect()->table('staff_users')->where('id', 8)->get()->getRow('role'));
    }

    public function testAdminChangesAnotherAccountsRole(): void
    {
        $this->post('admin/staf/8', ['display_name' => 'Ketua Uji', 'role' => 'ketua', 'is_active' => '1'])
            ->assertRedirectTo(site_url('admin/staf'));

        $this->assertSame('ketua', db_connect()->table('staff_users')->where('id', 8)->get()->getRow('role'));
    }

    public function testAdminCannotDemoteOrDeactivateSelf(): void
    {
        $this->post('admin/staf/7', ['display_name' => 'Admin Uji', 'role' => 'ketua', 'is_active' => '1'])
            ->assertRedirectTo(site_url('admin/staf'));
        $this->post('admin/staf/7', ['display_name' => 'Admin Uji', 'role' => 'admin'])
            ->assertRedirectTo(site_url('admin/staf'));
        $this->post('admin/staf/7/nonaktif', [])->assertRedirectTo(site_url('admin/staf'));

        $self = db_connect()->table('staff_users')->where('id', 7)->get()->getRowArray();
        $this->assertSame('admin', $self['role']);
        $this->assertSame(1, (int) $self['is_active']);
        $this->assertSame(0, db_connect()->table('audit_logs')->countAllResults());
    }

    public function testModelValidationRefusesTheTeacherRole(): void
    {
        $model = model(StaffUserModel::class, false);

        $this->assertFalse($model->insert(['username' => 'guru_lama', 'password_hash' => 'x', 'role' => 'guru', 'display_name' => 'Guru']));
        $this->assertArrayHasKey('role', $model->errors());
        $this->assertFalse($model->update(8, ['role' => 'guru']));
        $this->assertSame('peneliti', db_connect()->table('staff_users')->where('id', 8)->get()->getRow('role'));

        foreach (['admin', 'peneliti', 'ketua'] as $role) {
            $this->assertIsNumeric($model->insert(['username' => "uji_{$role}", 'password_hash' => 'x', 'role' => $role, 'display_name' => $role]), $role);
        }
    }

    // ---------------------------------------------------------------- helpers

    /** @return array<string, string> */
    private function newAccount(string $username, string $role): array
    {
        return ['username' => $username, 'display_name' => ucfirst($username), 'role' => $role, 'email' => '', 'password' => self::PASSWORD];
    }

    /** @param array<string, string> $params */
    private function post(string $path, array $params)
    {
        $this->injectStaff(true);
        Services::resetSingle('renderer');

        return $this->withSession(['staff_id' => 7, 'staff_role' => 'admin', 'staff_name' => 'Admin Uji', 'gelita_csrf' => self::CSRF_HASH])
            ->withHeaders([])
            ->call(Method::POST, $path, $params + self::CSRF);
    }

    /** false removes the injected staff so later tests read the database again. */
    private function injectStaff(bool $inject): void
    {
        $property  = new ReflectionProperty(BaseService::class, 'instances');
        $instances = $property->getValue();

        if ($inject) {
            $instances['staffcontext'] = (object) [
                'id' => 7, 'username' => 'admin_uji', 'email' => null, 'role' => 'admin', 'display_name' => 'Admin Uji',
                'is_active' => 1, 'must_change_password' => 0, 'last_login_at' => null,
            ];
        } else {
            unset($instances['staffcontext']);
        }

        $property->setValue(null, $instances);
    }
}
