<?php

namespace App\Libraries;

use CodeIgniter\CodeIgniter;
use Throwable;

/**
 * Server check (`/admin/cek-server`, admin only; owner, 18 November 2026):
 * whether this install meets what GELITA needs, the same list as
 * docs/08_DEPLOYMENT.md → *Spesifikasi server*, *Ekstensi PHP wajib*,
 * *Pengaturan PHP*.
 *
 * collect() reads the live server once; groups() only judges those facts,
 * so tests feed it made-up facts (a 32-bit PHP, a missing extension, a
 * wrong database clock). Nothing here changes anything.
 *
 * Row states: `ok`, `warn` (works, but not as the guide says), `fail`
 * (a part of GELITA breaks), `info` (shown, not judged). Texts are for the
 * admin: plain Indonesian, and never a password, key or database account.
 *
 * Why the check exists: a 32-bit PHP on a Windows server made Data mentah
 * and every data download fail (milliseconds since 1970 do not fit a 32-bit
 * integer; decision log, 18 November 2026).
 */
final class ServerCheck
{
    public const OK   = 'ok';
    public const WARN = 'warn';
    public const FAIL = 'fail';
    public const INFO = 'info';

    /** Lowest PHP version composer.json accepts, and the one the guide recommends. */
    public const PHP_MIN         = '8.2.0';
    public const PHP_RECOMMENDED = '8.3.0';

    /**
     * Extensions GELITA needs (docs/08 → Ekstensi PHP wajib, and the ext-*
     * requirements in composer.lock) => what breaks without it.
     */
    public const EXTENSIONS = [
        'intl'      => 'CodeIgniter tidak mau berjalan tanpanya.',
        'mbstring'  => 'Teks, laporan PDF, dan unduhan XLSX.',
        'mysqli'    => 'Sambungan ke database.',
        'gd'        => 'Gambar, laporan PDF, dan unduhan XLSX.',
        'zip'       => 'Unduhan XLSX/CSV-ZIP dan impor bank soal.',
        'fileinfo'  => 'Unggahan berkas.',
        'openssl'   => 'HTTPS dan enkripsi.',
        'curl'      => 'Pemeriksaan tautan Katalog Aset.',
        'iconv'     => 'Unduhan XLSX.',
        'dom'       => 'Unduhan XLSX dan impor bank soal.',
        'xml'       => 'Unduhan XLSX dan impor bank soal.',
        'xmlreader' => 'Impor bank soal.',
        'xmlwriter' => 'Unduhan XLSX.',
        'simplexml' => 'Unduhan XLSX dan impor bank soal.',
        'libxml'    => 'Unduhan XLSX dan impor bank soal.',
        'zlib'      => 'Unduhan CSV-ZIP dan laporan PDF.',
        'ctype'     => 'Unduhan XLSX.',
        'filter'    => 'Unduhan XLSX.',
        'json'      => 'Seluruh aplikasi.',
    ];

    /** Lowest database versions the guide supports (docs/08 → Perangkat lunak). */
    public const MYSQL_MIN   = '8.0.0';
    public const MARIADB_MIN = '10.6.0';

    /** Free disk below these (bytes) is a warning / a failure. */
    public const DISK_WARN = 5 * 1024 ** 3;
    public const DISK_FAIL = 1024 ** 3;

    /**
     * Hardware per operating system (docs/08 → Spesifikasi server): minimum
     * and recommended CPU count, memory and disk size. Windows itself takes
     * 2-3 GB of memory, so path W asks for more.
     */
    public const HARDWARE = [
        'Windows' => ['cpu' => [2, 4], 'memory' => [4, 8], 'disk' => [20, 50]],
        'Linux'   => ['cpu' => [2, 4], 'memory' => [2, 4], 'disk' => [20, 50]],
    ];

    /** Database and PHP clocks may differ by this much (seconds). */
    public const CLOCK_TOLERANCE = 120;

    /** @param array<string, mixed> $facts what collect() returns */
    public function __construct(private readonly array $facts)
    {
    }

    public static function forServer(): self
    {
        return new self(self::collect());
    }

    /**
     * Reads this server. Every probe catches its own failure, so one broken
     * part (say, the database) still leaves the rest of the page.
     *
     * @return array<string, mixed>
     */
    public static function collect(): array
    {
        $ini = [];

        foreach (['memory_limit', 'max_execution_time', 'upload_max_filesize', 'post_max_size', 'max_file_uploads', 'date.timezone', 'extension_dir'] as $key) {
            $ini[$key] = (string) ini_get($key);
        }

        $opcache = null;

        if (function_exists('opcache_get_status')) {
            $status  = @opcache_get_status(false);
            $opcache = is_array($status) && ! empty($status['opcache_enabled']);
        }

        return [
            'php_version'  => PHP_VERSION,
            'int_size'     => PHP_INT_SIZE,
            'os_family'    => PHP_OS_FAMILY,
            'machine'      => self::machine(),
            'sapi'         => PHP_SAPI,
            'binary'       => PHP_BINARY,
            'ini_file'     => php_ini_loaded_file() ?: null,
            'ini'          => $ini,
            'extensions'   => array_map('strtolower', get_loaded_extensions()),
            'opcache'      => $opcache,
            'environment'  => ENVIRONMENT,
            'ci_version'   => CodeIgniter::CI_VERSION,
            'app_timezone' => (string) config('App')->appTimezone,
            'base_url'     => (string) config('App')->baseURL,
            'key_set'      => (string) config('Encryption')->key !== '',
            'database'     => self::database(),
            'folders'      => self::folders(),
            'disk_free'    => self::diskFree(),
            'steps'        => self::steps(),
        ];
    }

    /**
     * Rows per group, in page order.
     *
     * @return list<array{id: string, title: string, help: string, rows: list<array{label: string, value: string, expected: string, state: string, note: string}>}>
     */
    public function groups(): array
    {
        return [
            ['id' => 'server', 'title' => 'Server', 'help' => 'Sistem operasi dan perangkat kerasnya, dibandingkan dengan spesifikasi di panduan pemasangan.', 'rows' => $this->machineRows()],
            ['id' => 'php', 'title' => 'PHP', 'help' => 'Versi dan jenis PHP yang menjalankan halaman ini.', 'rows' => $this->phpRows()],
            ['id' => 'ekstensi', 'title' => 'Ekstensi PHP', 'help' => 'Bagian tambahan PHP yang dipakai GELITA. Nyalakan yang belum ada di php.ini, lalu mulai ulang server web.', 'rows' => $this->extensionRows()],
            ['id' => 'php-ini', 'title' => 'Pengaturan php.ini', 'help' => 'Nilai yang dibaca halaman ini dari php.ini, dibandingkan dengan panduan pemasangan.', 'rows' => $this->iniRows()],
            ['id' => 'aplikasi', 'title' => 'Aplikasi', 'help' => 'Pengaturan GELITA di berkas .env.', 'rows' => $this->appRows()],
            ['id' => 'database', 'title' => 'Database', 'help' => 'Sambungan, versi, dan jam database.', 'rows' => $this->databaseRows()],
            ['id' => 'folder', 'title' => 'Folder dan ruang disk', 'help' => 'Folder yang harus bisa ditulisi server web, dan sisa ruang disk.', 'rows' => $this->folderRows()],
            ['id' => 'pembaruan', 'title' => 'Langkah setelah pembaruan', 'help' => 'Sama dengan perintah php spark gelita:setelah-pull. Langkah yang "perlu" dijalankan dari terminal server dengan php spark gelita:setelah-pull --jalankan.', 'rows' => $this->stepRows()],
        ];
    }

    /** @return array{fail: int, warn: int, ok: int} */
    public function summary(): array
    {
        $count = [self::FAIL => 0, self::WARN => 0, self::OK => 0];

        foreach ($this->groups() as $group) {
            foreach ($group['rows'] as $row) {
                if (isset($count[$row['state']])) {
                    $count[$row['state']]++;
                }
            }
        }

        return $count;
    }

    // ------------------------------------------------------------- server

    /**
     * Operating system, CPU count, memory and disk of this server. Linux
     * reads /proc; Windows asks WMI, through COM when the com_dotnet
     * extension is on, otherwise through one short PowerShell call. A value
     * that cannot be read stays null and shows as "tidak terbaca".
     *
     * @return array{os: string, arch: string, cpus: int|null, memory_total: float|null, memory_free: float|null, disk_total: float|null}
     */
    private static function machine(): array
    {
        $facts = [
            'os'           => trim(php_uname('s') . ' ' . php_uname('r') . ' ' . php_uname('v')),
            'arch'         => php_uname('m'),
            'cpus'         => null,
            'memory_total' => null,
            'memory_free'  => null,
            'disk_total'   => ($total = @disk_total_space(WRITEPATH)) === false ? null : (float) $total,
        ];

        try {
            if (PHP_OS_FAMILY === 'Windows') {
                $cpus           = (int) getenv('NUMBER_OF_PROCESSORS');
                $facts['cpus']  = $cpus > 0 ? $cpus : null;
                $facts          = self::windowsMemory() + $facts;
            } elseif (is_readable('/proc/meminfo')) {
                $info = (string) file_get_contents('/proc/meminfo');

                if (preg_match('/^MemTotal:\s+(\d+)/m', $info, $m) === 1) {
                    $facts['memory_total'] = (float) $m[1] * 1024;
                }

                if (preg_match('/^MemAvailable:\s+(\d+)/m', $info, $m) === 1) {
                    $facts['memory_free'] = (float) $m[1] * 1024;
                }

                $cpus          = is_readable('/proc/cpuinfo') ? preg_match_all('/^processor\s*:/m', (string) file_get_contents('/proc/cpuinfo')) : 0;
                $facts['cpus'] = $cpus > 0 ? $cpus : null;
            }
        } catch (Throwable) {
            // Leave what could not be read as null.
        }

        return $facts;
    }

    /**
     * Total and free memory, and the edition name ("Microsoft Windows Server
     * 2025 Datacenter"), from WMI's Win32_OperatingSystem (sizes in KB).
     *
     * @return array{memory_total?: float, memory_free?: float, os?: string}
     */
    private static function windowsMemory(): array
    {
        $total = $free = null;
        $name  = '';

        if (class_exists('COM')) {
            $wmi = new \COM('winmgmts:{impersonationLevel=impersonate}//./root/cimv2');

            foreach ($wmi->ExecQuery('SELECT TotalVisibleMemorySize, FreePhysicalMemory, Caption FROM Win32_OperatingSystem') as $os) {
                [$total, $free, $name] = [(float) $os->TotalVisibleMemorySize, (float) $os->FreePhysicalMemory, (string) $os->Caption];
            }
        } elseif (function_exists('shell_exec') && ! in_array('shell_exec', array_map('trim', explode(',', (string) ini_get('disable_functions'))), true)) {
            $command = 'powershell -NoProfile -NonInteractive -Command "$o = Get-CimInstance Win32_OperatingSystem; \'{0}|{1}|{2}\' -f $o.TotalVisibleMemorySize, $o.FreePhysicalMemory, $o.Caption"';
            $output  = trim((string) @shell_exec($command));

            if (preg_match('/^(\d+)\|(\d+)\|(.*)$/m', $output, $m) === 1) {
                [$total, $free, $name] = [(float) $m[1], (float) $m[2], trim($m[3])];
            }
        }

        $facts = [];

        if ($total !== null && $total > 0) {
            $facts['memory_total'] = $total * 1024;
            $facts['memory_free']  = (float) $free * 1024;
        }

        if ($name !== '') {
            $facts['os'] = $name;
        }

        return $facts;
    }

    /** @return list<array<string, string>> */
    private function machineRows(): array
    {
        $machine = $this->facts['machine'];
        $windows = $this->facts['os_family'] === 'Windows';
        $need    = self::HARDWARE[$windows ? 'Windows' : 'Linux'];
        $rows    = [];

        $rows[] = self::row(
            'Sistem operasi',
            $machine['os'] . ($machine['arch'] === '' ? '' : ' (' . $machine['arch'] . ')'),
            $windows ? 'Windows 10/11 Pro 64-bit atau Windows Server 2019+' : 'Ubuntu 24.04 LTS (paling rendah 22.04) atau Debian 12',
            self::INFO,
        );

        $rows[] = $this->sizeRow('Prosesor (CPU)', $machine['cpus'] === null ? null : (float) $machine['cpus'], $need['cpu'], static fn (float $n): string => (int) $n . ' inti', 'Halaman terasa lambat saat banyak siswa bermain bersamaan.');
        $rows[] = $this->sizeRow('Memori (RAM)', $machine['memory_total'] === null ? null : $machine['memory_total'] / 1024 ** 3, $need['memory'], static fn (float $n): string => number_format($n, 1, ',', '.') . ' GB', 'Unduhan XLSX yang besar butuh ruang memori.');

        if ($machine['memory_free'] !== null) {
            $rows[] = self::row('Memori yang masih kosong', self::gigabytes($machine['memory_free']), '', self::INFO);
        }

        $rows[] = $this->sizeRow('Ukuran disk', $machine['disk_total'] === null ? null : $machine['disk_total'] / 1024 ** 3, $need['disk'], static fn (float $n): string => number_format($n, 0, ',', '.') . ' GB', 'Gambar, video, event mentah, dan cadangan butuh ruang.');

        return $rows;
    }

    /**
     * One hardware row: below the minimum fails, below the recommendation
     * warns.
     *
     * @param array{0: int, 1: int}    $need [minimum, recommended]
     * @param callable(float): string $show
     *
     * @return array<string, string>
     */
    private function sizeRow(string $label, ?float $value, array $need, callable $show, string $why): array
    {
        $expected = $show((float) $need[1]) . ' (paling sedikit ' . $show((float) $need[0]) . ')';

        if ($value === null) {
            return self::row($label, 'tidak terbaca', $expected, self::INFO, 'Periksa sendiri di pengaturan server.');
        }

        // Memory is reported slightly below its nominal size (7.8 GB for 8 GB)
        $state = $value < $need[0] * 0.95 ? self::FAIL : ($value < $need[1] * 0.95 ? self::WARN : self::OK);

        return self::row($label, $show($value), $expected, $state, $state === self::OK ? '' : $why);
    }

    // ------------------------------------------------------------- PHP

    /** @return list<array<string, string>> */
    private function phpRows(): array
    {
        $version = (string) $this->facts['php_version'];
        $bits    = (int) $this->facts['int_size'] * 8;
        $rows    = [];

        $rows[] = self::row(
            'Versi PHP',
            $version,
            '8.3 (paling rendah 8.2)',
            version_compare($version, self::PHP_MIN, '<') ? self::FAIL : (version_compare($version, self::PHP_RECOMMENDED, '<') ? self::WARN : self::OK),
            version_compare($version, self::PHP_MIN, '<') ? 'GELITA tidak bisa dipasang di versi ini.' : '',
        );

        $rows[] = self::row(
            'Jenis PHP',
            $bits . '-bit',
            '64-bit (x64)',
            $bits >= 64 ? self::OK : self::FAIL,
            $bits >= 64 ? '' : 'PHP 32-bit (x86) membuat halaman Data mentah dan Unduh data gagal. Pasang PHP x64, pilih di Laragon (PHP → Version), lalu mulai ulang.',
        );

        $rows[] = self::row('Cara PHP berjalan', (string) $this->facts['sapi'], 'php-cgi (Windows) atau php-fpm (Linux)', self::INFO);
        $rows[] = self::row('Program PHP', (string) $this->facts['binary'], '', self::INFO);
        $rows[] = self::row(
            'Berkas php.ini',
            $this->facts['ini_file'] ?? 'tidak ada',
            '',
            $this->facts['ini_file'] === null ? self::WARN : self::INFO,
            $this->facts['ini_file'] === null ? 'PHP berjalan tanpa php.ini, jadi semua pengaturan memakai nilai bawaan.' : '',
        );

        $rows[] = $this->extensionDirRow();

        return $rows;
    }

    /**
     * On Windows the ext folder must belong to the PHP that runs: a php.ini
     * copied from another PHP folder keeps that folder's extension_dir and
     * loads its DLLs (or none of them). Linux keeps extensions elsewhere, so
     * there the row is information only.
     *
     * @return array<string, string>
     */
    private function extensionDirRow(): array
    {
        $dir = (string) ($this->facts['ini']['extension_dir'] ?? '');

        if ($this->facts['os_family'] !== 'Windows' || $dir === '') {
            return self::row('Folder ekstensi', $dir === '' ? '—' : $dir, '', self::INFO);
        }

        $phpDir = self::folderOf((string) $this->facts['binary']);
        $extDir = self::normalize($dir);
        $own    = $extDir === 'ext' || str_starts_with($extDir, self::normalize($phpDir) . '/');

        return self::row(
            'Folder ekstensi',
            $dir,
            'folder ext milik PHP ini',
            $own ? self::OK : self::FAIL,
            $own ? '' : 'php.ini menunjuk folder ext milik PHP lain. Ubah extension_dir ke ' . $phpDir . '\\ext.',
        );
    }

    /** @return list<array<string, string>> */
    private function extensionRows(): array
    {
        $loaded = $this->facts['extensions'];
        $rows   = [];

        foreach (self::EXTENSIONS as $name => $use) {
            $has    = in_array($name, $loaded, true);
            $rows[] = self::row($name, $has ? 'aktif' : 'tidak ada', 'aktif', $has ? self::OK : self::FAIL, $has ? '' : 'Dibutuhkan untuk: ' . $use);
        }

        $opcache = $this->facts['opcache'];
        $rows[]  = self::row(
            'opcache',
            $opcache === null ? 'tidak ada' : ($opcache ? 'aktif' : 'mati'),
            'aktif',
            $opcache === true ? self::OK : self::WARN,
            $opcache === true ? '' : 'GELITA tetap berjalan, tetapi setiap halaman lebih lambat. Nyalakan zend_extension=opcache dan opcache.enable=1.',
        );

        return $rows;
    }

    /** @return list<array<string, string>> */
    private function iniRows(): array
    {
        $ini  = $this->facts['ini'];
        $rows = [];

        $memory = self::bytes($ini['memory_limit']);
        $rows[] = self::row(
            'memory_limit',
            $ini['memory_limit'],
            '512M',
            $memory < 0 || $memory >= 512 * 1024 ** 2 ? self::OK : self::WARN,
            $memory < 0 || $memory >= 512 * 1024 ** 2 ? '' : 'Unduhan XLSX yang besar bisa gagal karena kehabisan memori.',
        );

        $time   = (int) $ini['max_execution_time'];
        $timeOk = $time >= 300 && $time <= 900;
        $rows[] = self::row(
            'max_execution_time',
            $time === 0 ? '0 (tanpa batas)' : $time . ' detik',
            '300 detik',
            $timeOk ? self::OK : self::WARN,
            $timeOk ? '' : ($time > 0 && $time < 300
                ? 'Unduhan data yang besar bisa terputus sebelum selesai.'
                : 'Halaman yang macet menahan satu proses PHP terlalu lama; siswa lain bisa ikut menunggu.'),
        );

        $upload = self::bytes($ini['upload_max_filesize']);
        $post   = self::bytes($ini['post_max_size']);
        $rows[] = self::row(
            'upload_max_filesize',
            $ini['upload_max_filesize'],
            '64M',
            $upload >= 64 * 1024 ** 2 ? self::OK : self::WARN,
            $upload >= 64 * 1024 ** 2 ? '' : 'Video atau rekaman yang besar tidak bisa diunggah.',
        );
        $rows[] = self::row(
            'post_max_size',
            $ini['post_max_size'],
            '72M, lebih besar dari upload_max_filesize',
            $post == 0 || $post > $upload ? self::OK : self::WARN,
            $post == 0 || $post > $upload ? '' : 'Harus lebih besar dari upload_max_filesize. Kalau tidak, unggahan besar terputus tanpa pesan yang jelas.',
        );

        $files  = (int) $ini['max_file_uploads'];
        $rows[] = self::row(
            'max_file_uploads',
            (string) $files,
            '100',
            $files >= 100 ? self::OK : self::WARN,
            $files >= 100 ? '' : 'Unggahan rekaman narasi sekaligus dibatasi ' . $files . ' berkas.',
        );

        $zone   = $ini['date.timezone'];
        $rows[] = self::row(
            'date.timezone',
            $zone === '' ? 'tidak diisi' : $zone,
            'Asia/Jakarta',
            $zone === 'Asia/Jakarta' ? self::OK : self::WARN,
            $zone === 'Asia/Jakarta' ? '' : 'GELITA memakai WIB dari pengaturannya sendiri; isi tetap Asia/Jakarta supaya alat lain di server memakai jam yang sama.',
        );

        return $rows;
    }

    // ------------------------------------------------------------- application

    /** @return list<array<string, string>> */
    private function appRows(): array
    {
        $env  = (string) $this->facts['environment'];
        $url  = (string) $this->facts['base_url'];
        $rows = [];

        $rows[] = self::row(
            'Mode aplikasi (CI_ENVIRONMENT)',
            $env,
            'production',
            $env === 'production' ? self::OK : self::WARN,
            $env === 'production' ? '' : 'Di luar production, halaman galat menampilkan rincian teknis kepada siapa pun. Ubah CI_ENVIRONMENT di .env.',
        );

        $https  = str_starts_with($url, 'https://');
        $rows[] = self::row(
            'Alamat aplikasi (app.baseURL)',
            $url,
            'diawali https://',
            $https ? self::OK : self::WARN,
            $https ? '' : 'Tanpa HTTPS, kata sandi dikirim tanpa enkripsi. Wajar hanya untuk server di jaringan sekolah tanpa internet.',
        );

        $rows[] = self::row(
            'Kunci enkripsi (encryption.key)',
            $this->facts['key_set'] ? 'terisi' : 'kosong',
            'terisi, tidak pernah diganti',
            $this->facts['key_set'] ? self::OK : self::FAIL,
            $this->facts['key_set'] ? '' : 'Tantangan Cari Objek tidak bisa dibuka. Isi dengan php spark key:generate, sekali saja, lalu simpan salinan .env.',
        );

        $zone   = (string) $this->facts['app_timezone'];
        $rows[] = self::row('Zona waktu aplikasi', $zone, 'Asia/Jakarta', $zone === 'Asia/Jakarta' ? self::OK : self::FAIL, $zone === 'Asia/Jakarta' ? '' : 'Semua waktu penelitian disimpan dalam WIB.');
        $rows[] = self::row('Versi CodeIgniter', (string) $this->facts['ci_version'], 'sesuai composer.lock', self::INFO);

        return $rows;
    }

    // ------------------------------------------------------------- database

    /**
     * @return array{error: string|null, platform: string, version: string, charset: string, now: string|null, php_now: string}
     */
    private static function database(): array
    {
        $facts = ['error' => null, 'platform' => '', 'version' => '', 'charset' => '', 'now' => null, 'php_now' => date('Y-m-d H:i:s')];

        try {
            $db = db_connect();
            $db->initialize();

            $facts['platform'] = (string) $db->getPlatform();
            $facts['version']  = (string) $db->getVersion();
            $facts['charset']  = (string) ($db->charset ?? '');

            if ($facts['platform'] === 'MySQLi') {
                $row              = $db->query('SELECT NOW() AS now')->getRowArray();
                $facts['now']     = isset($row['now']) ? (string) $row['now'] : null;
                $facts['php_now'] = date('Y-m-d H:i:s');
            }
        } catch (Throwable $e) {
            $facts['error'] = $e->getMessage();
        }

        return $facts;
    }

    /** @return list<array<string, string>> */
    private function databaseRows(): array
    {
        $db = $this->facts['database'];

        if ($db['error'] !== null) {
            return [self::row('Sambungan database', 'gagal', 'tersambung', self::FAIL, 'Periksa nama database, akun, dan kata sandinya di .env. Rincian galat ada di log server.')];
        }

        $rows   = [self::row('Sambungan database', 'tersambung', 'tersambung', self::OK)];
        $mysql  = $db['platform'] === 'MySQLi';
        $maria  = stripos($db['version'], 'mariadb') !== false;
        $number = preg_match('/\d+\.\d+(\.\d+)?/', $db['version'], $m) === 1 ? $m[0] : $db['version'];

        if (! $mysql) {
            $rows[] = self::row('Jenis database', $db['platform'] . ' ' . $db['version'], 'MySQL 8.0+ atau MariaDB 10.6+', self::INFO);

            return $rows;
        }

        $min    = $maria ? self::MARIADB_MIN : self::MYSQL_MIN;
        $rows[] = self::row(
            'Versi database',
            ($maria ? 'MariaDB ' : 'MySQL ') . $number,
            $maria ? 'MariaDB 10.6 atau lebih baru' : 'MySQL 8.0 atau lebih baru',
            version_compare($number, $min, '>=') ? self::OK : self::WARN,
            version_compare($number, $min, '>=') ? '' : 'Versi ini tidak diuji untuk GELITA.',
        );

        $rows[] = self::row(
            'Set karakter',
            $db['charset'],
            'utf8mb4',
            $db['charset'] === 'utf8mb4' ? self::OK : self::WARN,
            $db['charset'] === 'utf8mb4' ? '' : 'Nama dengan karakter khusus atau emoji bisa rusak saat disimpan.',
        );

        $rows[] = $this->clockRow($db['now'], $db['php_now']);

        return $rows;
    }

    /** @return array<string, string> */
    private function clockRow(?string $dbNow, string $phpNow): array
    {
        $db  = $dbNow === null ? false : strtotime($dbNow);
        $php = strtotime($phpNow);

        if ($db === false || $php === false) {
            return self::row('Jam database', $dbNow ?? '—', 'sama dengan jam PHP (WIB)', self::WARN, 'Jam database tidak terbaca.');
        }

        $diff = abs($db - $php);

        return self::row(
            'Jam database',
            $dbNow,
            'sama dengan jam PHP (' . $phpNow . ' WIB)',
            $diff <= self::CLOCK_TOLERANCE ? self::OK : self::FAIL,
            $diff <= self::CLOCK_TOLERANCE ? '' : 'Jam database berbeda ' . self::duration($diff) . ' dari jam PHP. Waktu penelitian akan tercatat salah; periksa zona waktu dan jam Windows server.',
        );
    }

    // ------------------------------------------------------------- folders

    /**
     * The folders the web server writes to, with whether each exists, is
     * writable, or can be created (a missing export or package folder is
     * made on first use).
     *
     * @return list<array{label: string, path: string, exists: bool, writable: bool, creatable: bool}>
     */
    private static function folders(): array
    {
        $list = [
            'Folder writable'          => WRITEPATH,
            'Log (writable/logs)'      => WRITEPATH . 'logs',
            'Cache (writable/cache)'   => WRITEPATH . 'cache',
            'Unduhan data'             => WRITEPATH . 'exports',
            'Unggahan sementara'       => WRITEPATH . 'uploads',
            'Paket aset'               => WRITEPATH . 'paket',
            'Gambar dan suara unggahan' => FCPATH . trim(MediaStore::uploadDir(), '/'),
        ];
        $rows = [];

        foreach ($list as $label => $path) {
            $exists = is_dir($path);
            $rows[] = [
                'label'     => $label,
                'path'      => rtrim($path, '/\\'),
                'exists'    => $exists,
                'writable'  => $exists && is_writable($path),
                'creatable' => ! $exists && is_dir(dirname($path)) && is_writable(dirname($path)),
            ];
        }

        return $rows;
    }

    private static function diskFree(): ?float
    {
        $free = @disk_free_space(WRITEPATH);

        return $free === false ? null : (float) $free;
    }

    /** @return list<array<string, string>> */
    private function folderRows(): array
    {
        $rows = [];

        foreach ($this->facts['folders'] as $folder) {
            [$value, $state, $note] = match (true) {
                $folder['writable']  => ['bisa ditulisi', self::OK, ''],
                $folder['exists']    => ['tidak bisa ditulisi', self::FAIL, 'Beri akun server web hak menulis ke folder ini.'],
                $folder['creatable'] => ['belum ada', self::OK, 'Dibuat sendiri saat pertama dipakai.'],
                default              => ['belum ada', self::FAIL, 'Buat folder ini dan beri akun server web hak menulis.'],
            };

            $rows[] = self::row($folder['label'], $value, 'bisa ditulisi', $state, trim($folder['path'] . '. ' . $note, '. '));
        }

        $free   = $this->facts['disk_free'];
        $rows[] = $free === null
            ? self::row('Sisa ruang disk', 'tidak terbaca', 'lebih dari 5 GB', self::WARN)
            : self::row(
                'Sisa ruang disk',
                self::gigabytes($free),
                'lebih dari 5 GB',
                $free < self::DISK_FAIL ? self::FAIL : ($free < self::DISK_WARN ? self::WARN : self::OK),
                $free < self::DISK_WARN ? 'Unduhan data, cadangan, dan log butuh ruang. Hapus cadangan lama atau tambah disk.' : '',
            );

        return $rows;
    }

    // ------------------------------------------------------------- update steps

    /** @return array{error: string|null, steps: list<array<string, mixed>>} */
    private static function steps(): array
    {
        try {
            return ['error' => null, 'steps' => PostPullCheck::forInstall()->steps()];
        } catch (Throwable $e) {
            return ['error' => $e->getMessage(), 'steps' => []];
        }
    }

    /** PostPullCheck states (stored as codes there) in words for the page. */
    private const STEP_WORDS = [
        PostPullCheck::OK      => 'sesuai',
        PostPullCheck::DUE     => 'perlu dijalankan',
        PostPullCheck::UNKNOWN => 'belum tercatat',
        PostPullCheck::SKIPPED => 'dilewati',
        PostPullCheck::MANUAL  => 'periksa sendiri',
    ];

    /** @return list<array<string, string>> */
    private function stepRows(): array
    {
        $steps = $this->facts['steps'];

        if ($steps['error'] !== null) {
            return [self::row('Langkah setelah pembaruan', 'tidak terbaca', '', self::WARN, 'Jalankan php spark gelita:setelah-pull di terminal server.')];
        }

        $rows = [];

        foreach ($steps['steps'] as $step) {
            $state = match ($step['state']) {
                PostPullCheck::OK  => self::OK,
                PostPullCheck::DUE => self::WARN,
                default            => self::INFO,
            };

            $rows[] = self::row((string) $step['title'], self::STEP_WORDS[$step['state']] ?? (string) $step['state'], self::STEP_WORDS[PostPullCheck::OK], $state, (string) $step['reason']);
        }

        return $rows;
    }

    // ------------------------------------------------------------- helpers

    /** @return array{label: string, value: string, expected: string, state: string, note: string} */
    private static function row(string $label, string $value, string $expected, string $state, string $note = ''): array
    {
        return ['label' => $label, 'value' => $value, 'expected' => $expected, 'state' => $state, 'note' => $note];
    }

    /**
     * "512M" → bytes; -1 (no limit) stays -1; 0 stays 0. A float, because
     * this page must also work on the 32-bit PHP it warns about, where
     * "2G" in bytes does not fit an int.
     */
    public static function bytes(string $value): float
    {
        $value = trim($value);

        if ($value === '' || $value === '0') {
            return 0.0;
        }

        if ($value === '-1') {
            return -1.0;
        }

        $number = (float) $value;

        return match (strtolower(substr($value, -1))) {
            'g'     => $number * 1024 ** 3,
            'm'     => $number * 1024 ** 2,
            'k'     => $number * 1024,
            default => $number,
        };
    }

    /** Folder of a file path, for either slash (dirname() keeps a Windows path whole on Linux). */
    private static function folderOf(string $path): string
    {
        return (string) preg_replace('#[\\\\/][^\\\\/]*$#', '', $path);
    }

    private static function normalize(string $path): string
    {
        return rtrim(strtolower(str_replace('\\', '/', $path)), '/');
    }

    private static function gigabytes(float $bytes): string
    {
        return number_format($bytes / 1024 ** 3, 1, ',', '.') . ' GB';
    }

    private static function duration(int $seconds): string
    {
        return $seconds >= 3600
            ? number_format($seconds / 3600, 1, ',', '.') . ' jam'
            : (int) ceil($seconds / 60) . ' menit';
    }
}
