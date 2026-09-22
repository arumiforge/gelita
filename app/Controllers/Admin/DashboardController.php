<?php

namespace App\Controllers\Admin;

use App\Models\ParticipantModel;
use App\Models\ResearchStudyModel;

/**
 * Ringkasan panel: KPI dan data awal untuk chart.
 * Angka dihitung AnalyticsService; controller hanya meneruskan filter
 * beserta cakupan sekolah pemanggil.
 */
class DashboardController extends BaseAdminController
{
    public function index(): string
    {
        $filters   = $this->scopedFilters();
        $analytics = $this->analytics();

        return $this->panel('admin/dashboard', 'Beranda', [
            'filters'         => $filters,
            'summary'         => $analytics->summary($filters),
            'levels'          => $analytics->levelBreakdown($filters),
            'cohort'          => model(ParticipantModel::class)->cohortSummary($filters),
            'digitalSecurity' => $analytics->digitalSecurityLiteracy($filters),
            'study'           => model(ResearchStudyModel::class)->activeStudy(),
            'isAdmin'         => $this->isAdmin(),
        ]);
    }
}
