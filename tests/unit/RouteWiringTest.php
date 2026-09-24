<?php

use CodeIgniter\Controller;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Seluruh route tahap 4 harus menunjuk controller dan method yang benar-benar ada.
 * Menangkap salah ketik nama kelas/method di Routes.php dan controller yang
 * lupa dibuat — tanpa perlu database.
 *
 * @internal
 */
final class RouteWiringTest extends CIUnitTestCase
{
    private const CONTROLLER_COUNT = [
        'Game'  => 11,  // 10 controller + BaseGameController
        'Admin' => 14,  // 13 controller + BaseAdminController
        'Api'   => 9,   // 8 controller + BaseApiController
    ];

    /** Route yang wajib ada beserta handlernya. */
    private const SPOT_CHECKS = [
        'GET'  => [
            '/'                           => 'Game\HomeController::index',
            'peta'                        => 'Game\MapController::kedu',
            'mulai'                       => 'Game\HomeController::start',
            'gerbang'                     => 'Game\GateController::index',
            'gerbang/peta'                => 'Game\GateController::map',
            'intro'                       => 'Game\GateController::intro',
            'intro/selesai'               => 'Game\GateController::finishIntro',
            'dialog/([^/]+)'              => 'Game\DialogueController::show',
            'tuntas/([^/]+)'              => 'Game\DialogueController::done',
            'penutup'                     => 'Game\DialogueController::ending',
            'pustaka'                     => 'Game\LibraryController::index',
            'pustaka/([^/]+)'             => 'Game\LibraryController::show',
            'api/session'                 => 'Api\SessionApiController::show',
            'api/auth/username-available' => 'Api\AuthApiController::usernameAvailable',
            'admin/login'                 => 'Admin\AuthController::loginForm',
            'admin/dashboard'             => 'Admin\DashboardController::index',
            'admin/akun/sandi'            => 'Admin\AccountController::passwordForm',
        ],
        'POST' => [
            'daftar'                 => 'Game\RegisterController::store',
            'masuk'                  => 'Game\LoginController::login',
            'api/events'             => 'Api\EventApiController::ingest',
            'admin/konten/verifikasi' => 'Admin\ContentController::verify',
            'admin/akun/sandi'       => 'Admin\AccountController::changePassword',
        ],
    ];

    /** Routes.php tidak dimuat otomatis dalam pengujian; muat sekali di sini. */
    private function routes(): \CodeIgniter\Router\RouteCollection
    {
        $routes = service('routes');
        $routes->loadRoutes();

        return $routes;
    }

    public function testAutoRoutingStaysDisabled(): void
    {
        $this->assertFalse(config('Routing')->autoRoute, 'Auto-route wajib mati.');
        $this->assertFalse($this->routes()->shouldAutoRoute());
    }

    public function testEveryRouteHandlerExists(): void
    {
        $routes  = $this->routes();
        $checked = 0;

        foreach (['GET', 'POST'] as $method) {
            foreach ($routes->getRoutes($method) as $from => $handler) {
                if (! is_string($handler)) {
                    continue;
                }

                [$class, $call] = $this->split($handler);

                $this->assertTrue(class_exists($class), "{$method} {$from} → kelas {$class} tidak ada");
                $this->assertTrue(
                    method_exists($class, $call),
                    "{$method} {$from} → {$class}::{$call}() tidak ada",
                );
                $this->assertTrue(
                    (new ReflectionMethod($class, $call))->isPublic(),
                    "{$method} {$from} → {$class}::{$call}() bukan method publik",
                );

                $checked++;
            }
        }

        $this->assertGreaterThan(90, $checked, 'Jumlah route jauh lebih sedikit dari yang diharapkan.');
    }

    public function testSpotCheckedRoutesPointAtTheirController(): void
    {
        $routes = $this->routes();

        foreach (self::SPOT_CHECKS as $method => $expected) {
            $defined = $routes->getRoutes($method);

            foreach ($expected as $from => $handler) {
                $this->assertArrayHasKey($from, $defined, "Route {$method} {$from} tidak terdaftar");
                $this->assertStringContainsString($handler, (string) $defined[$from]);
            }
        }
    }

    /** Tahap 3: adegan wilayah tuntas dan penutup hanya untuk peserta dengan sesi permainan. */
    public function testStoryScenesNeedAGameSession(): void
    {
        $routes = $this->routes();
        $routes->setHTTPVerb('GET');

        // getFiltersForRoute() menerima pola route, bukan URI
        foreach (['tuntas/([^/]+)', 'penutup'] as $path) {
            $this->assertSame(['gameSession'], $routes->getFiltersForRoute($path), $path);
        }
    }

    /** Ganti sandi sendiri: wajib login staf, tetapi terbuka untuk guru (bukan khusus admin). */
    public function testOwnPasswordChangeNeedsLoginButNotAdminRole(): void
    {
        $routes = $this->routes();

        foreach (['GET', 'POST'] as $method) {
            $routes->setHTTPVerb($method);

            $this->assertSame(['staffAuth'], $routes->getFiltersForRoute('admin/akun/sandi'), $method);
        }
    }

    public function testEveryControllerLoadsAndExtendsBaseController(): void
    {
        foreach (self::CONTROLLER_COUNT as $area => $expected) {
            $files = glob(APPPATH . 'Controllers/' . $area . '/*.php');

            $this->assertCount($expected, $files, "Jumlah controller {$area} tidak sesuai.");

            foreach ($files as $file) {
                $class      = 'App\\Controllers\\' . $area . '\\' . basename($file, '.php');
                $reflection = new ReflectionClass($class);

                $this->assertTrue($reflection->isSubclassOf(Controller::class), $class);
            }
        }
    }

    /**
     * Setiap nama view yang disebut controller harus punya berkasnya.
     * Tanpa ini, salah ketik nama view baru ketahuan saat halaman dibuka.
     */
    public function testEveryViewReferencedByControllersExists(): void
    {
        $referenced = [];

        foreach ($this->phpFilesIn(APPPATH . 'Controllers') as $file) {
            // hanya nama view literal; view('game/challenge/' . $engine, …)
            // dirakit saat berjalan dan diperiksa terpisah di bawah
            preg_match_all(
                "/(?:view|panel)\\('([a-z0-9\\/_.-]+)'\\s*[,)]/i",
                (string) file_get_contents($file),
                $matches,
            );

            foreach ($matches[1] as $view) {
                $referenced[$view] = $file;
            }
        }

        $this->assertNotEmpty($referenced, 'Tidak ada view yang terdeteksi di controller.');

        foreach ($referenced as $view => $file) {
            $this->assertFileExists(
                APPPATH . 'Views/' . $view . '.php',
                'View ' . $view . ' dipakai ' . basename($file) . ' tetapi berkasnya tidak ada',
            );
        }
    }

    /** Lima view arena tantangan dipilih dari `engine_type` saat berjalan. */
    public function testEveryChallengeEngineHasItsView(): void
    {
        $engines = config('Gelita')->engineTypes;

        $this->assertCount(5, $engines);

        foreach ($engines as $engine) {
            $this->assertFileExists(APPPATH . 'Views/game/challenge/' . $engine . '.php');
        }
    }

    /** @return list<string> */
    private function phpFilesIn(string $directory): array
    {
        $files    = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    /** @return array{0: string, 1: string} */
    private function split(string $handler): array
    {
        // "\App\Controllers\Game\MapController::level/$1" → kelas + nama method
        [$class, $call] = explode('::', $handler, 2);

        return [ltrim($class, '\\'), explode('/', $call, 2)[0]];
    }
}
