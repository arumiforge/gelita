<?php

use CodeIgniter\Router\RouteCollection;

/**
 * Tahap 2 — route dasar. Route lengkap Game/API/Admin ditulis pada tahap 4.
 *
 * @var RouteCollection $routes
 */
$routes->setAutoRoute(false);

// ---------------------------------------------------------------- GAME
$routes->get('/', 'Game\HomeController::index');

// ---------------------------------------------------------------- DEV (hanya development)
// Pratinjau layout admin/auth dan bentuk respons API sebelum login & controller tahap 4 ada.
if (ENVIRONMENT === 'development') {
    $routes->get('dev/layout/admin', 'DevPreview::adminLayout');
    $routes->get('dev/layout/auth', 'DevPreview::authLayout');
    $routes->get('api/dev/ping', 'DevPreview::ping', ['filter' => 'jsonResponse']);
}
