<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Hanya memeriksa siswa sudah login dan masih ada (langkah 1–2).
 * Dipakai /ganti-sandi dan /keluar, yang harus dapat dibuka walau sandi wajib diganti.
 */
class ParticipantAuthFilter implements FilterInterface
{
    use ParticipantCheck;

    public function before(RequestInterface $request, $arguments = null)
    {
        if ($this->checkParticipant(false)['status'] === 'OK') {
            return null;
        }

        return redirect()->to(site_url('masuk'));
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
