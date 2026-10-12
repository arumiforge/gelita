<?php

namespace Config;

use App\Filters\ApiSessionFilter;
use App\Filters\ClientHintsFilter;
use App\Filters\DebugToolbar;
use App\Filters\GameSessionFilter;
use App\Filters\JsonResponseFilter;
use App\Filters\LocaleFilter;
use App\Filters\PackageHeaderFilter;
use App\Filters\ParticipantAuthFilter;
use App\Filters\ScreenViewFilter;
use App\Filters\StaffAuthFilter;
use App\Filters\StaffRoleFilter;
use CodeIgniter\Config\Filters as BaseFilters;
use CodeIgniter\Filters\Cors;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\ForceHTTPS;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\PageCache;
use CodeIgniter\Filters\PerformanceMetrics;
use CodeIgniter\Filters\SecureHeaders;

class Filters extends BaseFilters
{
    /** @var array<string, class-string|list<class-string>> */
    public array $aliases = [
        'csrf'            => CSRF::class,
        // App\Filters\DebugToolbar: toolbar bawaan + penyamaran field kata sandi
        'toolbar'         => DebugToolbar::class,
        'honeypot'        => Honeypot::class,
        'invalidchars'    => InvalidChars::class,
        'secureheaders'   => SecureHeaders::class,
        'cors'            => Cors::class,
        'forcehttps'      => ForceHTTPS::class,
        'pagecache'       => PageCache::class,
        'performance'     => PerformanceMetrics::class,
        'staffAuth'       => StaffAuthFilter::class,
        'staffRole'       => StaffRoleFilter::class,
        'participantAuth' => ParticipantAuthFilter::class,
        'gameSession'     => GameSessionFilter::class,
        'apiSession'      => ApiSessionFilter::class,
        'locale'          => LocaleFilter::class,
        'jsonResponse'    => JsonResponseFilter::class,
        'clientHints'     => ClientHintsFilter::class,
        'screenView'      => ScreenViewFilter::class,
        'packageHeader'   => PackageHeaderFilter::class,
    ];

    /**
     * 'toolbar' sudah ada di sini (wajib), jadi tidak diulang di $globals.
     *
     * @var array{before: list<string>, after: list<string>}
     */
    public array $required = [
        'before' => [
            'forcehttps',
            'pagecache',
        ],
        'after' => [
            'pagecache',
            'performance',
            'toolbar',
        ],
    ];

    /** @var array<string, array<string, array<string, string|list<string>>>>|array<string, list<string>> */
    public array $globals = [
        'before' => [
            'locale',
        ],
        'after' => [
            'secureheaders',
        ],
    ];

    /**
     * CSRF untuk semua request yang mengubah data, termasuk /api
     * (JavaScript mengirim token lewat header X-CSRF-TOKEN).
     * Aman karena auto-routing mati (Config\Routing::$autoRoute = false).
     *
     * @var array<string, list<string>>
     */
    public array $methods = [
        'POST'   => ['csrf'],
        'PUT'    => ['csrf'],
        'PATCH'  => ['csrf'],
        'DELETE' => ['csrf'],
    ];

    /**
     * Client Hints entropi tinggi diminta sebelum masuk/daftar (Dok 1 §11.4).
     * `packageHeader`: X-Gelita-Paket on every API response (asset package Stage 2).
     *
     * @var array<string, array<string, list<string>>>
     */
    public array $filters = [
        'clientHints' => ['after' => ['/', 'mulai', 'masuk', 'daftar', 'daftar/*']],
        'packageHeader' => ['after' => ['api', 'api/*']],
    ];
}
