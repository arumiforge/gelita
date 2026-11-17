<?php

namespace App\Controllers\Admin;

use App\Libraries\DocumentStatusPage;

/**
 * Status dokumen — `/admin/status-dokumen` (head researcher only; owner,
 * 17 November 2026). Shows docs/revisi-10-analitik/status-dokumen.html
 * from the server's checkout inside the panel; see DocumentStatusPage.
 */
class DocumentStatusController extends BaseAdminController
{
    public function index(): string
    {
        return view('admin/document-status', [
            'content'   => DocumentStatusPage::content(),
            'pageTitle' => lang('Admin.menuDocStatus'),
        ]);
    }
}
