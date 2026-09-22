<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Area /admin dan /api/admin: staf harus login dan akunnya aktif.
 * Role & sekolah di session disinkronkan dengan database setiap request,
 * sehingga perubahan role/nonaktif oleh admin langsung berlaku.
 */
class StaffAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $isApi = is_api_path($request->getPath());

        if ((int) session('staff_id') <= 0) {
            return $this->reject($isApi);
        }

        $staff = service('staffContext');

        if ($staff === null || (int) $staff->is_active !== 1) {
            session()->destroy();

            return $this->reject($isApi);
        }

        session()->set([
            'staff_role'      => $staff->role,
            'staff_school_id' => $staff->school_id === null ? null : (int) $staff->school_id,
            'staff_name'      => $staff->display_name,
        ]);

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }

    private function reject(bool $isApi)
    {
        if ($isApi) {
            return api_error('INVALID_SESSION', 'Sesi Anda sudah berakhir. Silakan masuk kembali.', 401);
        }

        return redirect()->to(site_url('admin/login'));
    }
}
