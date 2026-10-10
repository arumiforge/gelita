<?php

use App\Filters\LocaleFilter;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\SiteURI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Session\Session;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * The no-login API endpoints must not open the session: every response that
 * touches it carries a Set-Cookie, and a late username check answering while
 * /daftar submits would overwrite the new session cookie and log the student
 * out.
 *
 * @internal
 */
final class LocaleFilterSessionTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('gelita');
    }

    protected function tearDown(): void
    {
        Services::resetSingle('session');
        parent::tearDown();
    }

    public function testStatelessApiPathsNeverTouchTheSession(): void
    {
        foreach (LocaleFilter::STATELESS_PATHS as $path) {
            $session = $this->createMock(Session::class);
            $session->expects($this->never())->method($this->anything());
            Services::injectMock('session', $session);

            $request = $this->request($path);
            $this->assertNull((new LocaleFilter())->before($request), $path);
            $this->assertSame('id', $request->getLocale(), $path);
        }
    }

    public function testOtherApiPathsStillReadTheSessionLocale(): void
    {
        $session = $this->createMock(Session::class);
        $session->method('get')->willReturnMap([['locale', 'en']]);
        Services::injectMock('session', $session);

        $request = $this->request('api/session');
        (new LocaleFilter())->before($request);

        $this->assertSame('en', $request->getLocale());
    }

    public function testStatelessPathsAreLoginFreeGetRoutes(): void
    {
        $routes = service('routes');
        $routes->loadRoutes();
        $routes->setHTTPVerb('GET');
        $defined = $routes->getRoutes('GET');

        // Control: the logged-in API group carries apiSession
        $this->assertContains('apiSession', $routes->getFiltersForRoute('api/session'));

        foreach (LocaleFilter::STATELESS_PATHS as $path) {
            $this->assertArrayHasKey($path, $defined, $path);
            $this->assertNotContains('apiSession', $routes->getFiltersForRoute($path), $path);
        }
    }

    private function request(string $path): IncomingRequest
    {
        return new IncomingRequest(config('App'), new SiteURI(config('App'), $path), null, new UserAgent());
    }
}
