<?php

use App\Database\Seeds\StaffUserSeeder;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\GradeFiveWorld;

/**
 * StaffUserSeeder on the current schema: `staff_users.school_id` was dropped
 * by migration 003400, so the seeder must not write it, or
 * `db:seed DatabaseSeeder` fails on a fresh install (9 November 2026, 04).
 *
 * @internal
 */
final class StaffUserSeederTest extends CIUnitTestCase
{
    use GradeFiveWorld;

    private BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createGradeFiveWorld();
        putenv('GELITA_ADMIN_PASSWORD=SandiUjiKuat12345!');
    }

    protected function tearDown(): void
    {
        putenv('GELITA_ADMIN_PASSWORD');
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testCreatesTheAdminOnTheCurrentSchema(): void
    {
        $this->assertFalse($this->conn->fieldExists('school_id', 'staff_users'), 'column dropped by 003400');
        $this->conn->table('staff_users')->where('username', 'admin')->delete();

        (new StaffUserSeeder(config('Database'), $this->conn))->run();

        $admin = $this->conn->table('staff_users')->where('username', 'admin')->get()->getRowArray();
        $this->assertNotNull($admin);
        $this->assertSame('admin', $admin['role']);
        $this->assertTrue(password_verify('SandiUjiKuat12345!', $admin['password_hash']));
    }
}
