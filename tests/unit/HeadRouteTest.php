<?php

use App\Libraries\HeadAsGetRouteCollection;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * HEAD dilayani rute GET dengan handler DAN filter yang sama. Tanpa ini setiap
 * halaman menjawab HEAD dengan 404; memetakan handler tanpa filter akan
 * membuka halaman staf tanpa login.
 *
 * @internal
 */
final class HeadRouteTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private function routesFor(string $verb): HeadAsGetRouteCollection
    {
        $routes = service('routes', false);
        $routes->loadRoutes();
        $routes->setHTTPVerb($verb);

        return $routes;
    }

    public function testRoutesServiceIsHeadAware(): void
    {
        $this->assertInstanceOf(HeadAsGetRouteCollection::class, service('routes'));
    }

    public function testHeadResolvesExactlyTheGetRoutes(): void
    {
        $get  = $this->routesFor(Method::GET)->getRoutes();
        $head = $this->routesFor(Method::HEAD)->getRoutes();

        $this->assertSame($get, $head);
        $this->assertArrayHasKey('admin/dashboard', $head);
        // rute khusus POST tetap tidak terjangkau HEAD
        $this->assertArrayNotHasKey('admin/konten/verifikasi', $head);
    }

    public function testHeadKeepsTheGetFilters(): void
    {
        $get  = $this->routesFor(Method::GET);
        $head = $this->routesFor(Method::HEAD);

        foreach (['admin/dashboard', 'admin/konten', 'admin/staf', 'peta', 'api/admin/summary'] as $route) {
            $filters = $get->getFiltersForRoute($route);

            $this->assertNotSame([], $filters, "{$route} seharusnya berfilter");
            $this->assertSame($filters, $head->getFiltersForRoute($route), "HEAD {$route} kehilangan filter");
        }
    }

    public function testHeadOnProtectedPageRunsTheAuthFilter(): void
    {
        $result = $this->call(Method::HEAD, 'admin/dashboard');

        $result->assertRedirectTo(site_url('admin/login'));
    }
}
