<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

/**
 * `X-Gelita-Paket` on API responses (docs/rencana-paket-aset.md §6.2, §9.3):
 * the code of the asset package students use right now
 * (AssetPackageService::activeCode()), so a client can tell that a new
 * package was published without another request. The header is left out
 * while package mode is off. It carries only the package code, nothing
 * about the student or the session.
 *
 * A running session never switches package because of it (S8): the device
 * changes package only at the start of the next session (core/package.js).
 */
class PackageHeaderFilter implements FilterInterface
{
    public const HEADER = 'X-Gelita-Paket';

    public function before(RequestInterface $request, $arguments = null)
    {
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        try {
            $code = service('assetPackages')->activeCode();
        } catch (Throwable) {
            $code = null;
        }

        if ($code !== null) {
            $response->setHeader(self::HEADER, $code);
        }

        return $response;
    }
}
