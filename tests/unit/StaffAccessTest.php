<?php

use App\Libraries\StaffAccess;
use App\Models\StaffUserModel;
use CodeIgniter\Router\RouteCollection;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * Capability map and route matrix (revision 10, part 1:
 * docs/revisi-10-analitik/27_IMPLEMENTASI_PERAN_PENELITI.md §4, §5, §14.1
 * `StaffAccessTest` and `StaffRouteMatrixTest`).
 *
 * The matrix reads the `staffCan` filter of each route and asks the map,
 * so it checks the routes and the map together against the table in 27 §4.
 *
 * @internal
 */
final class StaffAccessTest extends CIUnitTestCase
{
    public function testEveryCapabilityHoldsKnownRolesOnly(): void
    {
        foreach (StaffAccess::capabilities() as $capability) {
            $roles = StaffAccess::rolesFor($capability);

            $this->assertNotSame([], $roles, $capability);
            $this->assertSame([], array_diff($roles, StaffAccess::ROLES), $capability);
        }

        $this->assertSame(['admin', 'peneliti', 'ketua'], StaffAccess::ROLES);
    }

    public function testUnknownRolesAndCapabilitiesAreRefused(): void
    {
        $this->assertFalse(StaffAccess::allows('guru', 'dashboard.view'), 'the removed role holds nothing');
        $this->assertFalse(StaffAccess::allows(null, 'dashboard.view'));
        $this->assertFalse(StaffAccess::allows('admin', 'dashboard.viw'), 'a typo closes access');
        $this->assertFalse(StaffAccess::allowsAny('ketua', []));
        $this->assertFalse(StaffAccess::isRole('guru'));
    }

    public function testRightsFollowTheSeparationRules(): void
    {
        foreach (['research.browse', 'research.export', 'participants.research', 'studies.manage', 'risk_flags.view'] as $capability) {
            $this->assertFalse(StaffAccess::allows('admin', $capability), "admin never holds {$capability}");
        }

        foreach (['research.export', 'studies.manage', 'research.browse', 'content.edit', 'participants.accounts'] as $capability) {
            $this->assertFalse(StaffAccess::allows('peneliti', $capability), "peneliti never holds {$capability}");
        }

        // ketua holds every right of peneliti
        foreach (StaffAccess::capabilities() as $capability) {
            if (StaffAccess::allows('peneliti', $capability)) {
                $this->assertTrue(StaffAccess::allows('ketua', $capability), $capability);
            }
        }

        $this->assertTrue(StaffAccess::allows('ketua', 'research.export'));
        $this->assertTrue(StaffAccess::allows('admin', 'content.edit'));
    }

    public function testModelAcceptsExactlyTheRoles(): void
    {
        $rule = (new StaffUserModel())->getValidationRules()['role']['rules'];

        $this->assertSame('required|in_list[' . implode(',', StaffAccess::ROLES) . ']', $rule);
    }

    /**
     * 27 §4: GET path => [admin, peneliti, ketua]. 404 for a page and 403
     * for an API path without the right comes from StaffCanFilter.
     */
    public function testRouteMatrixMatchesTheAccessTable(): void
    {
        $matrix = [
            'admin'                                => [true, true, true],
            'admin/akun/sandi'                     => [true, true, true],
            'admin/peserta'                        => [true, true, true],
            'admin/peserta/([0-9]+)'               => [true, true, true],
            'admin/peserta/reset-sandi'            => [false, true, true],
            'admin/sesi'                           => [false, false, true],
            'admin/sesi/([0-9]+)/event'            => [false, false, true],
            'admin/data/([^/]+)'                   => [false, false, true],
            'admin/masukan'                        => [false, false, true],
            'admin/ekspor'                         => [false, false, true],
            'admin/ekspor/unduh/([0-9]+)'          => [false, false, true],
            'admin/konten'                         => [true, true, true],
            'admin/konten/node/([0-9]+)'           => [true, true, true],
            'admin/konten/narasi'                  => [true, true, true],
            'admin/konten/impor-bank'              => [true, false, false],
            'admin/konten/narasi/unggah'           => [true, false, false],
            'admin/media'                          => [true, true, true],
            'admin/media/kelengkapan'              => [true, true, true],
            'admin/studi'                          => [true, false, true],
            'admin/studi/rilis'                    => [true, false, false],
            'admin/studi/rilis/paket'              => [true, false, false],
            'admin/tata-kelola'                    => [true, false, false],
            'admin/tata-kelola/audit'              => [true, false, false],
            'admin/sekolah'                        => [true, false, false],
            'admin/staf'                           => [true, false, false],
            'api/admin/summary'                    => [true, true, true],
            'api/admin/sessions/([0-9]+)/timeline' => [false, false, true],
            'api/admin/exports/([0-9]+)/status'    => [false, false, true],
        ];

        $this->assertRoutes('GET', $matrix);

        $this->assertRoutes('POST', [
            'admin/peserta/([0-9]+)/reset-sandi' => [true, false, false],
            'admin/peserta/reset-sandi'          => [false, true, true],
            'admin/studi'                        => [false, false, true],
            'admin/studi/([0-9]+)'               => [false, false, true],
            'admin/konten/node/([0-9]+)'         => [true, false, false],
            'admin/media/unggah'                 => [true, false, false],
            'admin/ekspor/xlsx'                  => [false, false, true],
            'admin/tata-kelola/retensi'          => [true, false, false],
            'admin/staf'                         => [true, false, false],
        ]);
    }

    public function testEveryPanelRouteIsGuarded(): void
    {
        $routes = $this->routes();

        foreach (['GET', 'POST'] as $method) {
            $routes->setHTTPVerb($method);

            foreach (array_keys($routes->getRoutes($method)) as $path) {
                $path = (string) $path;

                if (! str_starts_with($path, 'admin/') && ! str_starts_with($path, 'api/admin') && $path !== 'admin') {
                    continue;
                }

                if (in_array($path, ['admin/login', 'admin/logout'], true)) {
                    continue;
                }

                $filters = $routes->getFiltersForRoute($path);
                $this->assertContains('staffAuth', $filters, "{$method} {$path}");

                if ($path !== 'admin/akun/sandi') {
                    $this->assertNotSame([], array_filter($filters, static fn (string $f): bool => str_starts_with($f, 'staffCan:')), "{$method} {$path} has a capability");
                }
            }
        }
    }

    /** @param array<string, array{0: bool, 1: bool, 2: bool}> $matrix */
    private function assertRoutes(string $method, array $matrix): void
    {
        $routes = $this->routes();
        $routes->setHTTPVerb($method);

        foreach ($matrix as $path => $expected) {
            $filters = $routes->getFiltersForRoute($path);
            $this->assertContains('staffAuth', $filters, "{$method} {$path}");

            $capabilities = [];

            foreach ($filters as $filter) {
                if (str_starts_with($filter, 'staffCan:')) {
                    $capabilities = explode(',', substr($filter, strlen('staffCan:')));
                }
            }

            foreach (StaffAccess::ROLES as $i => $role) {
                // A route without staffCan is open to every signed-in staff role
                $allowed = $capabilities === [] || StaffAccess::allowsAny($role, $capabilities);
                $this->assertSame($expected[$i], $allowed, "{$method} {$path} for {$role}");
            }
        }
    }

    private function routes(): RouteCollection
    {
        $routes = Services::routes(false);
        $routes->resetRoutes();
        $routes->loadRoutes();

        return $routes;
    }
}
