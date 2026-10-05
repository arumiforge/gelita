<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Meminta Client Hints entropi tinggi (Dok 1 §11.4) pada halaman sebelum
 * masuk/daftar: `/`, `/mulai`, `/masuk`, `/daftar/*`. Peramban Chromium lalu
 * mengirim `Sec-CH-UA-Model`, `…-Platform-Version`, dst. pada permintaan
 * berikutnya (termasuk POST /masuk dan POST /daftar/3) untuk
 * App\Libraries\DeviceProfile. Peramban lain mengabaikannya.
 */
class ClientHintsFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $hints = config('Gelita')->clientHints;

        if ($hints !== []) {
            $response->setHeader('Accept-CH', implode(', ', $hints));
        }

        return $response;
    }
}
