<?php

namespace App\Controllers\Admin;

use App\Models\ResearchStudyModel;

/**
 * Monitoring panel — `/admin/pemantauan` (research revision 10, part 2:
 * 28 §12). For running field sessions, not research results. Researchers
 * and the head researcher see codes and tokens; the admin sees numbers only
 * (K2). The page refreshes itself from GET /api/admin/monitoring
 * (public/assets/js/admin/monitoring.js).
 */
class MonitoringController extends BaseAdminController
{
    public function index(): string
    {
        $study = model(ResearchStudyModel::class)->activeStudy();

        return $this->panel('admin/monitoring', lang('Admin.menuMonitoring'), [
            'sessions' => $study === null ? [] : service('analyticsService')->monitoring((int) $study['id'], $this->can('field_sessions.manage')),
            'detail'   => $this->can('field_sessions.manage'),
        ]);
    }
}
