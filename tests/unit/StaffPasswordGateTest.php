<?php

use App\Filters\StaffAuthFilter;
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

/**
 * Staf dengan sandi sementara dari admin (`must_change_password = 1`) hanya
 * dapat membuka halaman Ubah sandi: halaman lain dialihkan ke sana dan API
 * membalas 403. Baris staf disuntikkan ke service staffContext, sehingga test
 * ini tidak memerlukan tabel staff_users.
 *
 * @internal
 */
final class StaffPasswordGateTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function tearDown(): void
    {
        $this->injectStaff(null);
        parent::tearDown();
    }

    public function testTemporaryPasswordOnlyOpensThePasswordPage(): void
    {
        $this->loginAs(mustChange: true);

        $this->assertNull($this->runFilter(StaffAuthFilter::PASSWORD_PAGE));

        $redirect = $this->runFilter('admin/dashboard');

        $this->assertInstanceOf(RedirectResponse::class, $redirect);
        $this->assertSame(site_url(StaffAuthFilter::PASSWORD_PAGE), $redirect->getHeaderLine('Location'));
    }

    public function testApiAnswersPasswordChangeRequired(): void
    {
        $this->loginAs(mustChange: true);

        $response = $this->runFilter('api/admin/summary');

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame('PASSWORD_CHANGE_REQUIRED', json_decode((string) $response->getBody(), true)['code'] ?? null);
    }

    public function testRegularAccountPassesThrough(): void
    {
        $this->loginAs(mustChange: false);

        $this->assertNull($this->runFilter('admin/dashboard'));
        $this->assertNull($this->runFilter('api/admin/summary'));
    }

    public function testRoutedPageIsRedirectedToPasswordPage(): void
    {
        $this->injectStaff($this->staffRow(true));

        $this->withSession(['staff_id' => 7])
            ->call(Method::GET, 'admin/peserta')
            ->assertRedirectTo(site_url(StaffAuthFilter::PASSWORD_PAGE));
    }

    private function loginAs(bool $mustChange): void
    {
        $this->injectStaff($this->staffRow($mustChange));
        session()->set('staff_id', 7);
    }

    private function runFilter(string $path): mixed
    {
        $config  = config(App::class);
        $request = new IncomingRequest($config, new SiteURI($config, $path), null, new UserAgent());

        return (new StaffAuthFilter())->before($request);
    }

    /** Bentuk baris yang dimuat Config\Services::staffContext(). */
    private function staffRow(bool $mustChange): object
    {
        return (object) [
            'id'                   => 7,
            'username'             => 'guru_uji',
            'email'                => null,
            'role'                 => 'guru',
            'display_name'         => 'Guru Uji',
            'school_id'            => 3,
            'is_active'            => 1,
            'must_change_password' => $mustChange ? 1 : 0,
            'last_login_at'        => null,
        ];
    }

    /** null = hapus suntikan agar test lain kembali membaca database. */
    private function injectStaff(?object $staff): void
    {
        $property  = new ReflectionProperty(BaseService::class, 'instances');
        $instances = $property->getValue();

        if ($staff === null) {
            unset($instances['staffcontext']);
        } else {
            $instances['staffcontext'] = $staff;
        }

        $property->setValue(null, $instances);
    }
}
