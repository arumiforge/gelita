<?php

use App\Filters\StaffAuthFilter;
use App\Libraries\PasswordPolicy;
use App\Models\StaffUserModel;
use CodeIgniter\Config\BaseService;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Method;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\SiteURI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\App;
use Config\Services;
use Tests\Support\Database\ResetTables;

/**
 * Staff sign-in security (revision 10, part 1:
 * docs/revisi-10-analitik/27_IMPLEMENTASI_PERAN_PENELITI.md §11.1, K15,
 * §14.1 `StaffLoginSecurityTest`): strong passwords of at least 12
 * characters without the username, and a 2-hour idle limit on staff
 * sessions that background API calls never extend.
 *
 * Staff 7 is a `peneliti` (`peneliti_uji`) with the password PASSWORD.
 *
 * @internal
 */
final class StaffLoginSecurityTest extends CIUnitTestCase
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

        db_connect()->table('staff_users')->insert([
            'id' => 7, 'username' => 'peneliti_uji', 'password_hash' => password_hash(self::PASSWORD, PASSWORD_DEFAULT),
            'role' => 'peneliti', 'display_name' => 'Peneliti Uji',
        ]);
    }

    protected function tearDown(): void
    {
        $this->injectStaff(null);
        $this->dropResetTables();

        parent::tearDown();
    }

    // ------------------------------------------------------------ passwords

    public function testWeakOwnPasswordIsRefused(): void
    {
        $before = $this->passwordHash();

        foreach (['kuatsandi2026', 'Pendek#1x', 'Peneliti_Uji#2026', 'XPENELITI_UJI#2026x'] as $weak) {
            $result = $this->changeOwnPassword($weak);
            $result->assertRedirectTo(site_url(StaffAuthFilter::PASSWORD_PAGE));
            $this->assertArrayHasKey('password', (array) session('errors'), $weak);
        }

        $this->assertSame(lang('Admin.staffPasswordWeak'), session('errors')['password'], 'contains the username');
        $this->assertSame($before, $this->passwordHash());

        $this->changeOwnPassword('Baru#Kuat2026yz')->assertRedirectTo(site_url(StaffAuthFilter::PASSWORD_PAGE));
        $this->assertTrue(password_verify('Baru#Kuat2026yz', $this->passwordHash()), 'a strong password is accepted');
    }

    public function testWeakPasswordIsRefusedOnAccountCreation(): void
    {
        foreach (['kuatsandi2026', 'Akun#1x', 'peneliti_baru#A1'] as $weak) {
            $this->asStaff('admin')->withHeaders([])->call(Method::POST, 'admin/staf', [
                'username' => 'peneliti_baru', 'display_name' => 'Peneliti Baru', 'role' => 'peneliti', 'email' => '', 'password' => $weak,
            ] + self::CSRF)->assertRedirectTo(site_url('admin/staf'));

            $this->assertArrayHasKey('password', (array) session('errors'), $weak);
        }

        $this->assertSame(0, db_connect()->table('staff_users')->where('username', 'peneliti_baru')->countAllResults());
    }

    public function testTemporaryPasswordPassesThePolicy(): void
    {
        $policy = new PasswordPolicy();

        for ($i = 0; $i < 20; $i++) {
            $temporary = StaffUserModel::temporaryPassword();
            $this->assertGreaterThanOrEqual(12, strlen($temporary));
            $this->assertTrue($policy->check($temporary, 'peneliti_uji')['acceptable'], $temporary);
        }
    }

    // ------------------------------------------------------------ idle limit

    public function testIdleSessionIsEnded(): void
    {
        $this->signIn(time() - config('Gelita')->staffIdleSeconds - 5);

        $redirect = $this->runFilter('admin/dashboard');

        $this->assertInstanceOf(RedirectResponse::class, $redirect);
        $this->assertSame(site_url('admin/login'), $redirect->getHeaderLine('Location'));
        $this->assertNull(session('staff_id'));
        $this->assertNull(session(StaffAuthFilter::LAST_SEEN_KEY));

        $audit = db_connect()->table('audit_logs')->where('action', 'staff_idle_logout')->get()->getResultArray();
        $this->assertCount(1, $audit);
        $this->assertSame(7, (int) $audit[0]['staff_user_id']);
    }

    public function testIdleApiRequestAnswers401(): void
    {
        $this->signIn(time() - config('Gelita')->staffIdleSeconds - 5);

        $response = $this->runFilter('api/admin/summary');

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame(1, db_connect()->table('audit_logs')->where('action', 'staff_idle_logout')->countAllResults());
    }

    public function testApiRequestDoesNotExtendTheSession(): void
    {
        $lastSeen = time() - 600;
        $this->signIn($lastSeen);

        $this->assertNull($this->runFilter('api/admin/summary'));
        $this->assertSame($lastSeen, session(StaffAuthFilter::LAST_SEEN_KEY));

        $this->assertNull($this->runFilter('admin/dashboard'));
        $this->assertGreaterThan($lastSeen, session(StaffAuthFilter::LAST_SEEN_KEY), 'a page request counts as activity');
        $this->assertSame(0, db_connect()->table('audit_logs')->countAllResults());
    }

    public function testRoleOutsideTheListIsSignedOut(): void
    {
        $this->injectStaff('guru');
        session()->set('staff_id', 7);

        $redirect = $this->runFilter('admin/dashboard');

        $this->assertInstanceOf(RedirectResponse::class, $redirect);
        $this->assertSame(site_url('admin/login'), $redirect->getHeaderLine('Location'));
        // Session::destroy() is a no-op under ENVIRONMENT 'testing'; the
        // session is at least never refreshed for the leftover role.
        $this->assertNull(session(StaffAuthFilter::LAST_SEEN_KEY));
        $this->assertNull(session('staff_role'));

        $this->injectStaff('guru');
        session()->set('staff_id', 7);
        $this->assertSame(401, $this->runFilter('api/admin/summary')->getStatusCode());
    }

    // ---------------------------------------------------------------- helpers

    private function changeOwnPassword(string $password)
    {
        return $this->asStaff('peneliti')->withHeaders([])->call(Method::POST, StaffAuthFilter::PASSWORD_PAGE, [
            'current_password' => self::PASSWORD, 'password' => $password, 'password_confirm' => $password,
        ] + self::CSRF);
    }

    private function passwordHash(): string
    {
        return (string) db_connect()->table('staff_users')->where('id', 7)->get()->getRow('password_hash');
    }

    private function asStaff(string $role): self
    {
        $this->injectStaff($role);
        Services::resetSingle('renderer');

        return $this->withSession(['staff_id' => 7, 'staff_role' => $role, 'staff_name' => 'Uji', 'gelita_csrf' => self::CSRF_HASH]);
    }

    private function signIn(int $lastSeen): void
    {
        $this->injectStaff('peneliti');
        session()->set(['staff_id' => 7, StaffAuthFilter::LAST_SEEN_KEY => $lastSeen]);
    }

    private function runFilter(string $path): mixed
    {
        $config  = config(App::class);
        $request = new IncomingRequest($config, new SiteURI($config, $path), null, new UserAgent());

        return (new StaffAuthFilter())->before($request);
    }

    /** null removes the injected staff so later tests read the database again. */
    private function injectStaff(?string $role): void
    {
        $property  = new ReflectionProperty(BaseService::class, 'instances');
        $instances = $property->getValue();

        if ($role === null) {
            unset($instances['staffcontext']);
        } else {
            $instances['staffcontext'] = (object) [
                'id' => 7, 'username' => 'peneliti_uji', 'email' => null, 'role' => $role, 'display_name' => 'Peneliti Uji',
                'is_active' => 1, 'must_change_password' => 0, 'last_login_at' => null,
            ];
        }

        $property->setValue(null, $instances);
    }
}
