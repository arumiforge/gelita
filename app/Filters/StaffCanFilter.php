<?php

namespace App\Filters;

use App\Libraries\StaffAccess;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Route guard by capability (revision 10, part 1, 27 §5.3).
 *
 * Usage: ['filter' => 'staffCan:research.export'] or
 * 'staffCan:participants.accounts,participants.research' (any one is enough).
 * Runs after `staffAuth`, which keeps `staff_role` in sync with the database.
 * A page without the right → 404 (the panel's structure stays unmapped);
 * an API path → 403 FORBIDDEN.
 */
class StaffCanFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $role = session('staff_role');

        if ($role === null) {
            return redirect()->to(site_url('admin/login'));
        }

        if (! StaffAccess::allowsAny((string) $role, (array) $arguments)) {
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
