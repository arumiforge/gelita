<?php

namespace Config;

use CodeIgniter\Events\Events;
use CodeIgniter\Exceptions\FrameworkException;
use CodeIgniter\HotReloader\HotReloader;

Events::on('pre_system', static function (): void {
    if (ENVIRONMENT !== 'testing') {
        $value = ini_get('zlib.output_compression');
        if (filter_var($value, FILTER_VALIDATE_BOOLEAN) || (int) $value > 0) {
            throw FrameworkException::forEnabledZlibOutputCompression();
        }

        while (ob_get_level() > 0) {
            ob_end_flush();
        }

        ob_start(static fn ($buffer) => $buffer);
    }

    if (CI_DEBUG && ! is_cli()) {
        Events::on('DBQuery', 'CodeIgniter\Debug\Toolbar\Collectors\Database::collect');
        service('toolbar')->respond();

        if (ENVIRONMENT === 'development') {
            service('routes')->get('__hot-reload', static function (): void {
                (new HotReloader())->run();
            });
        }
    }
});

/*
 * Samakan time_zone koneksi MySQL dengan Config\App::$appTimezone (Asia/Jakarta),
 * agar DEFAULT CURRENT_TIMESTAMP(6) dan waktu yang ditulis PHP berada di zona yang sama.
 * Event pre_system hanya terpicu pada request web, tidak pada `php spark`:
 * command GELITA yang menulis waktu (diisi tahap 7) wajib memanggil
 * db_sync_timezone() sendiri di awal run().
 */
Events::on('pre_system', static function (): void {
    if (ENVIRONMENT !== 'testing') {
        db_sync_timezone();
    }
});

/*
 * Expired `ci_sessions` rows are removed by PHP's session garbage collector
 * (DatabaseHandler::gc(), rows older than Config\Session::$expiration).
 * Debian and Ubuntu ship `session.gc_probability = 0`, because their cron job
 * cleans session files; nothing cleans a session table. Without this no row
 * was ever removed on such a machine, and since 21 November 2026 the row of a
 * replaced session ID is kept until this collector takes it
 * (Config\Session::$regenerateDestroy). One request in a hundred runs it.
 */
Events::on('pre_system', static function (): void {
    if (ENVIRONMENT !== 'testing' && session_status() !== PHP_SESSION_ACTIVE && ! headers_sent()) {
        ini_set('session.gc_probability', '1');
        ini_set('session.gc_divisor', '100');
    }
});
