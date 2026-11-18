<?php

namespace App\Controllers\Admin;

use App\Libraries\ServerCheck;

/**
 * Cek server — `/admin/cek-server` (admin only; owner, 18 November 2026):
 * PHP, its extensions and php.ini settings, the application settings, the
 * database, writable folders and disk, and the post-pull steps, judged
 * against docs/08_DEPLOYMENT.md. Read only; see ServerCheck.
 */
class ServerCheckController extends BaseAdminController
{
    public function index(): string
    {
        $check = ServerCheck::forServer();

        return view('admin/server-check', [
            'groups'    => $check->groups(),
            'summary'   => $check->summary(),
            'pageTitle' => lang('Admin.menuServerCheck'),
        ]);
    }
}
