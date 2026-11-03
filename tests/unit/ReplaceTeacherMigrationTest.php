<?php

use App\Database\Migrations\ReplaceTeacherWithResearcherRoles;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;
use Tests\Support\Database\ResetTables;

/**
 * Migration 2026-01-01-003400 (revision 10, part 1:
 * docs/revisi-10-analitik/27_IMPLEMENTASI_PERAN_PENELITI.md §6.1, K16,
 * §14.1 `ReplaceTeacherMigrationTest`).
 *
 * The real migration chain uses MySQL-only SQL, so the tables come from
 * ResetTables and `staff_users` is rebuilt in its old shape (with
 * `school_id` and the `guru` default) before up() runs directly.
 *
 * @internal
 */
final class ReplaceTeacherMigrationTest extends CIUnitTestCase
{
    use ResetTables;

    private bool $allowBefore;
    private string $exportFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->allowBefore = config('Gelita')->allowTeacherRemoval;
        $this->db          = $this->createResetTables();
        $this->createOldStaffTable();

        $this->exportFile = WRITEPATH . 'exports-test-' . bin2hex(random_bytes(4)) . '.xlsx';
    }

    protected function tearDown(): void
    {
        config('Gelita')->allowTeacherRemoval = $this->allowBefore;

        if (is_file($this->exportFile)) {
            unlink($this->exportFile);
        }

        $this->dropResetTables();

        parent::tearDown();
    }

    public function testGuardStopsWhileTeacherAccountsExist(): void
    {
        config('Gelita')->allowTeacherRemoval = false;
        $this->seedTeacherData();

        try {
            $this->migrate();
            $this->fail('The migration must stop while guru accounts exist.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('allowTeacherRemoval', $e->getMessage());
        }

        $this->assertSame(2, $this->db->table('staff_users')->countAllResults());
        $this->assertSame(1, $this->db->table('data_exports')->countAllResults());
        $this->assertSame(1, $this->db->table('data_deletion_requests')->countAllResults());
        $this->assertSame(2, (int) $this->db->table('audit_logs')->where('staff_user_id', 2)->countAllResults());
        $this->assertFileExists($this->exportFile);
        $this->assertContains('school_id', $this->db->getFieldNames('staff_users'));
    }

    public function testWithTheFlagTeacherDataIsRemoved(): void
    {
        config('Gelita')->allowTeacherRemoval = true;
        $this->seedTeacherData();

        $this->migrate();

        $this->assertSame(['admin_utama'], array_column($this->db->table('staff_users')->get()->getResultArray(), 'username'));
        $this->assertSame(0, $this->db->table('data_exports')->countAllResults());
        $this->assertFileDoesNotExist($this->exportFile);
        $this->assertSame(0, $this->db->table('data_deletion_requests')->countAllResults());

        // Audit history stays, with an empty actor
        $this->assertSame(3, $this->db->table('audit_logs')->countAllResults());
        $this->assertSame(2, $this->db->table('audit_logs')->where('staff_user_id', null)->countAllResults());
        $this->assertSame(1, $this->db->table('audit_logs')->where('staff_user_id', 1)->countAllResults());

        $this->assertNull($this->db->table('audio_assets')->get()->getRow('approved_by'));
        $this->assertNull($this->db->table('asset_packages')->get()->getRow('built_by'));

        $this->assertNotContains('school_id', $this->db->getFieldNames('staff_users'));
        $this->assertRoleHasNoDefault();
    }

    public function testRunsWithoutTheFlagWhenNoTeacherAccountsExist(): void
    {
        config('Gelita')->allowTeacherRemoval = false;
        $this->insertStaff(1, 'admin_utama', 'admin');
        $this->db->table('audit_logs')->insert(['staff_user_id' => 1, 'action' => 'staff_login']);

        $this->migrate();

        $this->assertSame(1, $this->db->table('staff_users')->countAllResults());
        $this->assertSame(1, (int) $this->db->table('audit_logs')->get()->getRow('staff_user_id'));
        $this->assertNotContains('school_id', $this->db->getFieldNames('staff_users'));
        $this->assertRoleHasNoDefault();
    }

    // ---------------------------------------------------------------- helpers

    private function migrate(): void
    {
        // Migration file names carry a date prefix, so PSR-4 cannot find the class
        require_once APPPATH . 'Database/Migrations/2026-01-01-003400_ReplaceTeacherWithResearcherRoles.php';

        (new ReplaceTeacherWithResearcherRoles(Database::forge('tests')))->up();
    }

    /** `staff_users` as it was before 003400: `school_id` and role default `guru`. */
    private function createOldStaffTable(): void
    {
        $forge = Database::forge('tests');
        $forge->dropTable('staff_users', true);
        $forge->addField([
            'id'                   => ['type' => 'INTEGER', 'auto_increment' => true],
            'username'             => ['type' => 'VARCHAR', 'constraint' => 100],
            'email'                => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => true],
            'password_hash'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'must_change_password' => ['type' => 'INTEGER', 'default' => 0],
            'role'                 => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'guru'],
            'display_name'         => ['type' => 'VARCHAR', 'constraint' => 150],
            'school_id'            => ['type' => 'INTEGER', 'null' => true],
            'is_active'            => ['type' => 'INTEGER', 'default' => 1],
            'failed_login_count'   => ['type' => 'INTEGER', 'default' => 0],
            'locked_until'         => ['type' => 'DATETIME', 'null' => true],
            'last_login_at'        => ['type' => 'DATETIME', 'null' => true],
            'created_at'           => ['type' => 'DATETIME', 'null' => true],
            'updated_at'           => ['type' => 'DATETIME', 'null' => true],
        ]);
        $forge->addPrimaryKey('id');
        $forge->addUniqueKey('username');
        $forge->createTable('staff_users');
    }

    private function insertStaff(int $id, string $username, ?string $role): void
    {
        $row = ['id' => $id, 'username' => $username, 'password_hash' => 'x', 'display_name' => ucfirst($username), 'school_id' => 3];

        if ($role !== null) {
            $row['role'] = $role;
        }

        $this->db->table('staff_users')->insert($row);
    }

    /** Admin 1 and teacher 2 (role from the old default), with the teacher's traces. */
    private function seedTeacherData(): void
    {
        $this->insertStaff(1, 'admin_utama', 'admin');
        $this->insertStaff(2, 'guru_alpha', null);
        $this->assertSame('guru', $this->db->table('staff_users')->where('id', 2)->get()->getRow('role'));

        file_put_contents($this->exportFile, 'xlsx');
        $this->db->table('data_exports')->insert([
            'requested_by' => 2, 'format' => 'xlsx', 'scope_json' => '{}', 'status' => 'done', 'file_path' => $this->exportFile,
        ]);
        $this->db->table('data_deletion_requests')->insert(['requested_by' => 2, 'scope_json' => '{}']);
        $this->db->table('audit_logs')->insertBatch([
            ['staff_user_id' => 1, 'action' => 'staff_login'],
            ['staff_user_id' => 2, 'action' => 'staff_login'],
            ['staff_user_id' => 2, 'action' => 'export_create'],
        ]);
        $this->db->table('audio_assets')->insert([
            'media_asset_id' => 1, 'locale' => 'id', 'context_code' => 'intro', 'transcript' => 'Halo', 'approved_by' => 2,
        ]);
        $this->db->table('asset_packages')->insert(['package_code' => 'pkt-2026-01-01-01', 'app_version' => '1', 'built_by' => 2]);
    }

    private function assertRoleHasNoDefault(): void
    {
        foreach ($this->db->getFieldData('staff_users') as $field) {
            if ($field->name === 'role') {
                $this->assertNull($field->default, 'role has no default');
                $this->assertFalse((bool) $field->nullable, 'role is required');

                return;
            }
        }

        $this->fail('staff_users.role is missing');
    }
}
