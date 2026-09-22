<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * After-filter grup /api: Content-Type JSON + header X-Request-Id.
 * Exception tak tertangani di /api diubah menjadi JSON standar oleh
 * App\Libraries\GelitaExceptionHandler (filter tidak berjalan saat exception).
 */
class JsonResponseFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        if (! str_contains($response->getHeaderLine('Content-Type'), 'application/json')) {
            $response->setContentType('application/json');
        }

        $response->setHeader('X-Request-Id', (string) service('gelitaRequestId'));

        return $response;
    }
}
