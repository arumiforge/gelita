<?php

use App\Libraries\AdminMenu;
use App\Libraries\ServerCheck;
use App\Libraries\StaffAccess;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\RawDataWorld;

/**
 * `/admin/cek-server` (owner, 18 November 2026): the admin sees whether the
 * server meets docs/08_DEPLOYMENT.md. The rules are checked with made-up
 * facts; the page itself with the real test machine.
 *
 * @internal
 */
final class ServerCheckTest extends CIUnitTestCase
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

    public function testAHealthyServerPassesEveryJudgedRow(): void
    {
        $check = new ServerCheck($this->facts());

        $this->assertSame(0, $check->summary()['fail'], $this->failing($check));
        $this->assertSame(0, $check->summary()['warn'], $this->failing($check));
        $this->assertSame(
            ['server', 'php', 'ekstensi', 'php-ini', 'aplikasi', 'database', 'folder', 'pembaruan'],
            array_column($check->groups(), 'id'),
        );
    }

    /** The cause of the 18 November 2026 failure: Data mentah and downloads break on 32-bit PHP. */
    public function test32BitPhpFails(): void
    {
        $row = $this->row(new ServerCheck($this->facts(['int_size' => 4])), 'php', 'Jenis PHP');

        $this->assertSame(ServerCheck::FAIL, $row['state']);
        $this->assertSame('32-bit', $row['value']);
        $this->assertStringContainsString('x64', $row['note']);
    }

    public function testOldPhpFailsAndPhp82OnlyWarns(): void
    {
        $this->assertSame(ServerCheck::FAIL, $this->row(new ServerCheck($this->facts(['php_version' => '8.1.30'])), 'php', 'Versi PHP')['state']);
        $this->assertSame(ServerCheck::WARN, $this->row(new ServerCheck($this->facts(['php_version' => '8.2.28'])), 'php', 'Versi PHP')['state']);
    }

    /** PHP 8.5 is the server's version since 23 November 2026; an 8.3 server is reminded to move up. */
    public function testPhp83WarnsAndPhp85Passes(): void
    {
        $this->assertSame(ServerCheck::WARN, $this->row(new ServerCheck($this->facts(['php_version' => '8.3.14'])), 'php', 'Versi PHP')['state']);
        $this->assertSame(ServerCheck::OK, $this->row(new ServerCheck($this->facts(['php_version' => '8.5.13'])), 'php', 'Versi PHP')['state']);
    }

    public function testAMissingExtensionFailsAndSaysWhatBreaks(): void
    {
        $facts = $this->facts();
        $facts['extensions'] = array_values(array_diff($facts['extensions'], ['intl']));

        $row = $this->row(new ServerCheck($facts), 'ekstensi', 'intl');

        $this->assertSame(ServerCheck::FAIL, $row['state']);
        $this->assertStringContainsString('CodeIgniter', $row['note']);
        $this->assertSame(ServerCheck::WARN, $this->row(new ServerCheck($this->facts(['opcache' => false])), 'ekstensi', 'opcache')['state']);
    }

    /** A php.ini copied from the 32-bit folder keeps loading that folder's DLLs. */
    public function testWindowsExtensionFolderMustBelongToThisPhp(): void
    {
        $windows = ['os_family' => 'Windows', 'binary' => 'C:\laragon\bin\php\php-8.3.36-1-nts-Win32-vs16-x64\php-cgi.exe'];

        foreach (['C:/laragon/bin/php/php-8.3.36-1-nts-Win32-vs16-x64/ext', 'ext'] as $dir) {
            $facts = $this->facts($windows);
            $facts['ini']['extension_dir'] = $dir;
            $this->assertSame(ServerCheck::OK, $this->row(new ServerCheck($facts), 'php', 'Folder ekstensi')['state'], $dir);
        }

        $facts = $this->facts($windows);
        $facts['ini']['extension_dir'] = 'C:/laragon/bin/php/php-8.3/ext';
        $row = $this->row(new ServerCheck($facts), 'php', 'Folder ekstensi');
        $this->assertSame(ServerCheck::FAIL, $row['state']);
        $this->assertStringContainsString('php-8.3.36-1-nts-Win32-vs16-x64\ext', $row['note']);

        // Linux keeps extensions outside the PHP folder: information only
        $facts = $this->facts();
        $facts['ini']['extension_dir'] = '/usr/lib/php/20230831';
        $this->assertSame(ServerCheck::INFO, $this->row(new ServerCheck($facts), 'php', 'Folder ekstensi')['state']);
    }

    public function testIniValuesFollowTheDeploymentGuide(): void
    {
        $ini = static fn (array $values): array => ['ini' => $values + [
            'memory_limit' => '512M', 'max_execution_time' => '300', 'upload_max_filesize' => '64M', 'post_max_size' => '72M',
            'max_file_uploads' => '100', 'date.timezone' => 'Asia/Jakarta', 'extension_dir' => 'ext',
        ]];

        $this->assertSame(ServerCheck::WARN, $this->row(new ServerCheck($this->facts($ini(['post_max_size' => '64M']))), 'php-ini', 'post_max_size')['state'], 'not larger than the upload limit');
        $this->assertSame(ServerCheck::WARN, $this->row(new ServerCheck($this->facts($ini(['max_execution_time' => '36000']))), 'php-ini', 'max_execution_time')['state']);
        $this->assertSame(ServerCheck::WARN, $this->row(new ServerCheck($this->facts($ini(['max_execution_time' => '0']))), 'php-ini', 'max_execution_time')['state']);
        $this->assertSame(ServerCheck::OK, $this->row(new ServerCheck($this->facts($ini(['memory_limit' => '-1']))), 'php-ini', 'memory_limit')['state']);
        $this->assertSame(ServerCheck::WARN, $this->row(new ServerCheck($this->facts($ini(['memory_limit' => '128M']))), 'php-ini', 'memory_limit')['state']);
        $this->assertSame(ServerCheck::WARN, $this->row(new ServerCheck($this->facts($ini(['date.timezone' => '']))), 'php-ini', 'date.timezone')['state']);
        $this->assertSame(ServerCheck::WARN, $this->row(new ServerCheck($this->facts($ini(['max_file_uploads' => '20']))), 'php-ini', 'max_file_uploads')['state']);

        $this->assertSame(2147483648.0, ServerCheck::bytes('2G'), 'a float, so 32-bit PHP can read it');
        $this->assertSame(512.0 * 1024 * 1024, ServerCheck::bytes('512M'));
        $this->assertSame(-1.0, ServerCheck::bytes('-1'));
    }

    public function testApplicationSettings(): void
    {
        $this->assertSame(ServerCheck::FAIL, $this->row(new ServerCheck($this->facts(['key_set' => false])), 'aplikasi', 'Kunci enkripsi (encryption.key)')['state']);
        $this->assertSame(ServerCheck::WARN, $this->row(new ServerCheck($this->facts(['environment' => 'development'])), 'aplikasi', 'Mode aplikasi (CI_ENVIRONMENT)')['state']);
        $this->assertSame(ServerCheck::WARN, $this->row(new ServerCheck($this->facts(['base_url' => 'http://10.0.0.5/'])), 'aplikasi', 'Alamat aplikasi (app.baseURL)')['state']);
    }

    public function testDatabaseVersionCharsetAndClock(): void
    {
        $db = static fn (array $values): array => ['database' => $values + [
            'error' => null, 'platform' => 'MySQLi', 'version' => '8.4.3', 'charset' => 'utf8mb4', 'now' => '2026-11-18 15:00:00', 'php_now' => '2026-11-18 15:00:30',
        ]];

        $clock = $this->row(new ServerCheck($this->facts($db(['now' => '2026-11-18 08:00:00']))), 'database', 'Jam database');
        $this->assertSame(ServerCheck::FAIL, $clock['state']);
        $this->assertStringContainsString('7,0 jam', $clock['note']);

        $this->assertSame(ServerCheck::OK, $this->row(new ServerCheck($this->facts($db(['version' => '10.11.8-MariaDB']))), 'database', 'Versi database')['state']);
        $this->assertSame(ServerCheck::WARN, $this->row(new ServerCheck($this->facts($db(['version' => '10.4.32-MariaDB']))), 'database', 'Versi database')['state']);
        $this->assertSame(ServerCheck::WARN, $this->row(new ServerCheck($this->facts($db(['version' => '5.7.44']))), 'database', 'Versi database')['state']);
        $this->assertSame(ServerCheck::WARN, $this->row(new ServerCheck($this->facts($db(['charset' => 'utf8']))), 'database', 'Set karakter')['state']);

        $down = new ServerCheck($this->facts($db(['error' => 'Access denied for user gelita_app'])));
        $this->assertSame(ServerCheck::FAIL, $this->row($down, 'database', 'Sambungan database')['state']);
        $this->assertStringNotContainsString('gelita_app', json_encode($down->groups()), 'no database account on the page');
    }

    public function testHardwareFollowsTheGuidePerOperatingSystem(): void
    {
        $gb      = 1024 ** 3;
        $machine = static fn (array $values): array => ['machine' => $values + [
            'os' => 'Microsoft Windows Server 2025 Datacenter', 'arch' => 'AMD64', 'cpus' => 4, 'memory_total' => 12.0 * $gb, 'memory_free' => 6.5 * $gb, 'disk_total' => 100.0 * $gb,
        ]];
        $windows = static fn (array $values): array => $machine($values) + ['os_family' => 'Windows'];

        // The owner's server (18 November 2026): 4 vCPU, 12 GB, Windows Server 2025
        $check = new ServerCheck($this->facts($windows([])));
        $this->assertSame(ServerCheck::OK, $this->row($check, 'server', 'Prosesor (CPU)')['state']);
        $this->assertSame(ServerCheck::OK, $this->row($check, 'server', 'Memori (RAM)')['state']);

        $this->assertSame(ServerCheck::OK, $this->row(new ServerCheck($this->facts($windows(['memory_total' => 7.8 * $gb]))), 'server', 'Memori (RAM)')['state'], '8 GB reads as 7.8 GB');
        $this->assertSame(ServerCheck::WARN, $this->row(new ServerCheck($this->facts($windows(['memory_total' => 6.0 * $gb]))), 'server', 'Memori (RAM)')['state']);
        $this->assertSame(ServerCheck::FAIL, $this->row(new ServerCheck($this->facts($windows(['memory_total' => 3.0 * $gb]))), 'server', 'Memori (RAM)')['state']);
        $this->assertSame(ServerCheck::WARN, $this->row(new ServerCheck($this->facts($machine(['memory_total' => 3.0 * $gb]))), 'server', 'Memori (RAM)')['state'], 'Linux needs less');
        $this->assertSame(ServerCheck::WARN, $this->row(new ServerCheck($this->facts($windows(['cpus' => 2]))), 'server', 'Prosesor (CPU)')['state']);
        $this->assertSame(ServerCheck::FAIL, $this->row(new ServerCheck($this->facts($windows(['disk_total' => 15.0 * $gb]))), 'server', 'Ukuran disk')['state']);

        $unknown = $this->row(new ServerCheck($this->facts($windows(['memory_total' => null]))), 'server', 'Memori (RAM)');
        $this->assertSame(ServerCheck::INFO, $unknown['state'], 'not readable is not a failure');
        $this->assertSame('tidak terbaca', $unknown['value']);
    }

    public function testFoldersAndDisk(): void
    {
        $folder = static fn (array $values): array => $values + ['label' => 'Unduhan data', 'path' => '/srv/gelita/writable/exports', 'exists' => true, 'writable' => true, 'creatable' => false];

        $check = new ServerCheck($this->facts(['folders' => [
            $folder(['writable' => false]),
            $folder(['label' => 'Paket aset', 'exists' => false, 'writable' => false, 'creatable' => true]),
        ], 'disk_free' => 0.5 * 1024 ** 3]));

        $this->assertSame(ServerCheck::FAIL, $this->row($check, 'folder', 'Unduhan data')['state']);
        $this->assertSame(ServerCheck::OK, $this->row($check, 'folder', 'Paket aset')['state'], 'made on first use');
        $this->assertSame(ServerCheck::FAIL, $this->row($check, 'folder', 'Sisa ruang disk')['state']);
    }

    public function testOnlyTheAdminHasThePage(): void
    {
        $this->assertTrue(StaffAccess::allows('admin', 'server.check'));

        foreach (['peneliti', 'ketua'] as $role) {
            $this->assertFalse(StaffAccess::allows($role, 'server.check'), $role);
            $can = static fn (string $capability): bool => StaffAccess::allows($role, $capability);
            $this->assertNotContains('admin/cek-server', array_column(AdminMenu::items($can, 'admin'), 'href'), $role);
        }

        $routes = service('routes');
        $routes->loadRoutes();
        $routes->setHTTPVerb('GET');
        $this->assertSame(['staffAuth', 'staffCan:server.check'], $routes->getFiltersForRoute('admin/cek-server'));

        $_SESSION['staff_role'] = 'ketua';

        try {
            $this->expectException(PageNotFoundException::class);
            (new App\Filters\StaffCanFilter())->before(service('request'), ['server.check']);
        } finally {
            unset($_SESSION['staff_role']);
        }
    }

    public function testTheAdminSeesEverySectionOfThisMachine(): void
    {
        $body = (string) $this->withSession(['staff_id' => self::STAFF['admin'], 'staff_role' => 'admin', 'staff_name' => 'Uji'])
            ->call(Method::GET, 'admin/cek-server')->getBody();

        $this->assertStringContainsString(lang('Admin.serverCheckLead'), $body);

        foreach (['Server', 'PHP', 'Ekstensi PHP', 'Pengaturan php.ini', 'Aplikasi', 'Database', 'Folder dan ruang disk', 'Langkah setelah pembaruan'] as $title) {
            $this->assertStringContainsString('<h2 class="panel-title">' . $title, $body, $title);
        }

        $this->assertStringContainsString(PHP_VERSION, $body);
        $this->assertStringContainsString('admin/cek-server', $body, 'menu item');
        $this->assertStringNotContainsString((string) config('Encryption')->key ?: 'no-key-set', $body, 'the key itself never shows');
    }

    // ------------------------------------------------------------- helpers

    /**
     * Facts of a server that meets the guide, with $override on top.
     *
     * @return array<string, mixed>
     */
    private function facts(array $override = []): array
    {
        $gb = 1024 ** 3;

        return $override + [
            'php_version'  => '8.5.13',
            'int_size'     => 8,
            'os_family'    => 'Linux',
            'machine'      => ['os' => 'Linux 6.8.0', 'arch' => 'x86_64', 'cpus' => 4, 'memory_total' => 8.0 * $gb, 'memory_free' => 5.0 * $gb, 'disk_total' => 80.0 * $gb],
            'sapi'         => 'fpm-fcgi',
            'binary'       => '/usr/sbin/php-fpm8.5',
            'ini_file'     => '/etc/php/8.5/fpm/php.ini',
            'ini'          => [
                'memory_limit' => '512M', 'max_execution_time' => '300', 'upload_max_filesize' => '64M', 'post_max_size' => '72M',
                'max_file_uploads' => '100', 'date.timezone' => 'Asia/Jakarta', 'extension_dir' => '/usr/lib/php/20250925',
            ],
            'extensions'   => array_keys(ServerCheck::EXTENSIONS),
            'opcache'      => true,
            'environment'  => 'production',
            'ci_version'   => '4.7.4',
            'app_timezone' => 'Asia/Jakarta',
            'base_url'     => 'https://gelita.id/',
            'key_set'      => true,
            'database'     => ['error' => null, 'platform' => 'MySQLi', 'version' => '8.4.3', 'charset' => 'utf8mb4', 'now' => '2026-11-18 15:00:00', 'php_now' => '2026-11-18 15:00:05'],
            'folders'      => [['label' => 'Folder writable', 'path' => '/srv/gelita/writable', 'exists' => true, 'writable' => true, 'creatable' => false]],
            'disk_free'    => 30.0 * $gb,
            'steps'        => ['error' => null, 'steps' => [['id' => 'migrasi', 'title' => 'Struktur database (migration)', 'state' => 'sesuai', 'reason' => 'Semua migration sudah dijalankan.', 'command' => null, 'runnable' => true]]],
        ];
    }

    /** @return array<string, string> */
    private function row(ServerCheck $check, string $group, string $label): array
    {
        foreach ($check->groups() as $g) {
            if ($g['id'] !== $group) {
                continue;
            }

            foreach ($g['rows'] as $row) {
                if ($row['label'] === $label) {
                    return $row;
                }
            }
        }

        $this->fail("No row {$label} in {$group}");
    }

    private function failing(ServerCheck $check): string
    {
        $out = [];

        foreach ($check->groups() as $group) {
            foreach ($group['rows'] as $row) {
                if (in_array($row['state'], [ServerCheck::FAIL, ServerCheck::WARN], true)) {
                    $out[] = "{$group['id']}/{$row['label']}: {$row['state']} ({$row['value']})";
                }
            }
        }

        return implode("\n", $out);
    }
}
