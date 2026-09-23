<?php

namespace App\Libraries;

use CodeIgniter\HTTP\Method;
use CodeIgniter\Router\RouteCollection;

/**
 * Request HEAD dilayani rute GET: handler DAN opsi rutenya (filter staffAuth,
 * staffRole, gameSession, …) sama persis.
 *
 * CodeIgniter hanya mencocokkan HEAD dengan rute yang didaftarkan lewat
 * $routes->head(), sehingga tanpa kelas ini setiap halaman menjawab HEAD
 * dengan 404 — pemantau uptime dan `curl -I` melaporkan situs mati.
 * RFC 9110 § 9.3.2: HEAD identik dengan GET tanpa isi; Nginx dan server
 * bawaan PHP tidak mengirim isi untuk HEAD.
 *
 * Handler dan opsi dipetakan bersama. Memetakan getRoutes() saja akan
 * menjalankan controller admin TANPA filter autentikasinya.
 * Filter yang punya efek samping khusus GET (menyimpan tujuan login, ?lang=)
 * memeriksa getMethod() === 'GET', jadi tidak berjalan untuk HEAD.
 */
class HeadAsGetRouteCollection extends RouteCollection
{
    public function getRoutes(?string $verb = null, bool $includeWildcard = true): array
    {
        return parent::getRoutes($this->asGet($verb), $includeWildcard);
    }

    protected function loadRoutesOptions(?string $verb = null): array
    {
        return parent::loadRoutesOptions($this->asGet($verb));
    }

    private function asGet(?string $verb): string
    {
        $verb = (string) $verb === '' ? $this->getHTTPVerb() : $verb;

        return $verb === Method::HEAD ? Method::GET : $verb;
    }
}
