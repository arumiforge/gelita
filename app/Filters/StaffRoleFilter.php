<?php

namespace App\Filters;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Pemakaian: ['filter' => 'staffRole:admin'] atau 'staffRole:admin,guru'.
 * HTML tanpa hak → 404 (struktur panel tidak terpetakan); API → 403 FORBIDDEN.
 */
class StaffRoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $role = session('staff_role');

        if ($role === null) {
            return redirect()->to(site_url('admin/login'));
        }

        if ($arguments && ! in_array($role, (array) $arguments, true)) {
            if (is_api_path($request->getPath())) {
                return api_error('FORBIDDEN', 'Anda tidak memiliki akses ke bagian ini.', 403);
            }

            throw PageNotFoundException::forPageNotFound();
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
