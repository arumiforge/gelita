<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

/**
 * Hanya development (lihat Routes.php): pratinjau layout admin/auth dan
 * bentuk respons API sebelum login & controller tahap 4 tersedia.
 */
class DevPreview extends BaseController
{
    public function adminLayout(): string
    {
        return view('admin/dev-preview', ['pageTitle' => 'Pratinjau layout admin']);
    }

    public function authLayout(): string
    {
        return view('admin/dev-auth-preview');
    }

    public function ping(): ResponseInterface
    {
        return $this->ok([
            'app'         => 'GELITA',
            'locale'      => $this->locale,
            'server_time' => date('Y-m-d\TH:i:sP'),
            'db_time'     => db_connect()->query('SELECT NOW(6) AS now')->getRow()->now,
            'timezone'    => config('App')->appTimezone,
        ]);
    }
}
