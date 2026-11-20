<?php

use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;
use Tests\Support\Database\RawDataWorld;

/**
 * The shared `staffContext` service keeps the signed-in staff row "for the
 * rest of the request"; in one PHPUnit process that is until someone resets
 * it. A head researcher's request in one test class used to answer the
 * admin's request in the next class with 404, because StaffAuthFilter gave
 * the session the cached row's role (21 November 2026: `ServerCheckTest`
 * after `DocumentStatusPageTest` or `FieldModeSwitchOffTest`). ResetTables
 * now forgets the row whenever it creates or drops `staff_users`.
 *
 * @internal
 */
final class StaffContextLeakTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use RawDataWorld;

    private const STAFF = ['admin' => 1, 'ketua' => 2, 'peneliti' => 3];

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRawDataWorld();
    }

    protected function tearDown(): void
    {
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testTheCachedStaffRowIsForgottenWithItsTable(): void
    {
        $this->cacheHeadResearcher();
        $this->createRawDataWorld();
        $this->assertNull(service('staffContext'), 'a new staff_users table');

        $this->cacheHeadResearcher();
        $this->dropResetTables();
        $this->assertNull(service('staffContext'), 'the staff_users table is gone');
    }

    public function testAnotherRolesRequestInAnEarlierTestDoesNotAnswerThisOne(): void
    {
        $this->assertStringContainsString('admin/status-dokumen', $this->page('ketua', 'admin'), 'the head researcher is signed in');

        // What tearDown() and the next test's setUp() do.
        $this->dropResetTables();
        $this->createRawDataWorld();

        $this->assertStringContainsString(lang('Admin.serverCheckLead'), $this->page('admin', 'admin/cek-server'));
    }

    /** Leaves the head researcher's row in the service, as a request of that role does. */
    private function cacheHeadResearcher(): void
    {
        Services::resetSingle('staffContext');
        $_SESSION = ['staff_id' => self::STAFF['ketua']];
        $this->assertSame('ketua', service('staffContext')->role);

        $_SESSION = [];
        $this->assertSame('ketua', service('staffContext')->role, 'kept after the session is gone');
    }

    private function page(string $role, string $path): string
    {
        foreach (['security', 'renderer'] as $name) {
            Services::resetSingle($name);
        }

        return (string) $this->withSession(['staff_id' => self::STAFF[$role], 'staff_role' => $role, 'staff_name' => ucfirst($role)])
            ->call(Method::GET, $path)->getBody();
    }
}
