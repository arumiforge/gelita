<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Route game yang butuh siswa login, sandi sah, dan sesi permainan aktif.
 * Sesi 'completed' tetap lolos (peta, profil, refleksi, ulang tantangan).
 */
class GameSessionFilter implements FilterInterface
{
    use ParticipantCheck;

    public function before(RequestInterface $request, $arguments = null)
    {
        $check = $this->checkParticipant(true);

        switch ($check['status']) {
            case 'OK':
                return null;

            case 'MUST_CHANGE':
                if ($request->getPath() === 'ganti-sandi') {
                    return null;
                }

                return redirect()->to(site_url('ganti-sandi'));

            case 'NO_LOGIN':
                // simpan tujuan (path internal saja) agar login dapat kembali ke sini
                if ($request->getMethod() === 'GET' && session_status() === PHP_SESSION_ACTIVE) {
                    session()->setFlashdata('redirect_after_login', '/' . ltrim($request->getPath(), '/'));
                }

                return redirect()->to(site_url('masuk'));

            default: // NO_SESSION — login membuat/melanjutkan sesi
                return redirect()->to(site_url('masuk'));
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
