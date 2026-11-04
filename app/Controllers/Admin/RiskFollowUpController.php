<?php

namespace App\Controllers\Admin;

use App\Models\ResearchStudyModel;

/**
 * Tindak lanjut — `/admin/tindak-lanjut` (research revision 10, part 4:
 * 30 §6, K7). Head researcher only (`risk_flags.view`). Flags are computed
 * on opening, never stored; no download or print, `Cache-Control: no-store`.
 */
class RiskFollowUpController extends BaseAdminController
{
    public function index(): string
    {
        $service = service('riskFollowUp');
        $study   = model(ResearchStudyModel::class)->where('field_mode', 1)->orderBy('id', 'DESC')->first();
        $closed  = $study === null || $service->studyClosed((int) $study['id']);
        $groups  = $closed ? [] : $service->flags((int) $study['id']);

        if (! $closed) {
            $service->auditView($groups, $this->staffId());
        }

        $this->response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');

        return $this->panel('admin/risk/index', lang('Admin.menuRiskFollowUp'), [
            'groups' => $groups,
            'closed' => $closed && $study !== null,
        ]);
    }
}
