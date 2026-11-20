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
        $status = $this->checkParticipant(false)['status'];

        if ($status === 'OK') {
            return null;
        }

        // No running study (owner, 20 November 2026): /keluar still works; the
        // token and password pages log out and send the student to /masuk
        if ($status === 'NO_STUDY') {
            if (trim($request->getPath(), '/') === 'keluar') {
                return null;
            }

            $this->logoutWithoutStudy();
        }

        return redirect()->to(site_url('masuk'));
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
