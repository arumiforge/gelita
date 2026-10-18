<?php

namespace App\Controllers\Game;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * The installable app: `GET /manifest.json` (display plan Phase A3).
 *
 * Served by PHP instead of a static file so the icons can be replaced in the
 * panel (slots `app.icon-192`, `app.icon-512`, `app.icon-maskable`). The URL
 * stays the same, so apps that are already installed pick up new icons.
 * An empty slot falls back to the static icon in `public/assets/app/`.
 * The route is listed in LocaleFilter::STATELESS_PATHS: browsers fetch the
 * manifest without cookies, and each fetch must not start a session.
 *
 * Display is plain `standalone` with no fixed orientation. Xiaomi phones
 * (HyperOS) refuse to start an installed app from its home-screen icon when
 * the manifest asks for `fullscreen` or `landscape`: it opens once from
 * Chrome, then the icon only flashes a "Web App" card and closes (decision
 * log, 18 October 2026). The rotate screen (components/rotate-gate) asks for
 * landscape instead, and the HUD keeps its full-screen button.
 */
class AppController extends BaseController
{
    /** Slot → [static fallback, sizes, purpose]. */
    public const MANIFEST_ICONS = [
        'app.icon-192'      => ['assets/app/icon-192.png', '192x192', 'any'],
        'app.icon-512'      => ['assets/app/icon-512.png', '512x512', 'any'],
        'app.icon-maskable' => ['assets/app/icon-maskable-512.png', '512x512', 'maskable'],
    ];

    public function manifest(): ResponseInterface
    {
        $icons = [];

        foreach (self::MANIFEST_ICONS as $slot => [$fallback, $sizes, $purpose]) {
            $icons[] = ['src' => app_icon_src($slot, $fallback), 'sizes' => $sizes, 'type' => 'image/png', 'purpose' => $purpose];
        }

        $manifest = [
            // Relative to this URL, so an install in a subfolder stays right
            'id'               => './',
            'name'             => 'GELITA — Game Edukasi Literasi dan Etnopedagogi Kedu',
            'short_name'       => 'GELITA',
            'description'      => 'Game edukasi literasi dan budaya Kedu: Temanggung, Magelang, Wonosobo.',
            'lang'             => 'id',
            'dir'              => 'ltr',
            'start_url'        => './',
            'scope'            => './',
            'display'          => 'standalone',
            'background_color' => '#0B1320',
            'theme_color'      => '#0B1320',
            'categories'       => ['education', 'games'],
            'icons'            => $icons,
        ];

        return $this->response
            ->setHeader('Content-Type', 'application/manifest+json; charset=UTF-8')
            ->setHeader('Cache-Control', 'no-cache')
            ->setBody(json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
}
