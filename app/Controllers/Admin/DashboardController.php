<?php

namespace App\Controllers\Admin;

use App\Models\ParticipantModel;
use App\Models\ResearchStudyModel;

/**
 * Ringkasan panel: KPI dan data awal untuk chart.
 * Angka dihitung AnalyticsService; controller hanya meneruskan filter
 * beserta cakupan sekolah pemanggil. Chart ECharts (tahap 6) mengambil ulang
 * datanya dari /api/admin/*; data di sini dipakai untuk KPI, tabel, dan
 * visual cadangan yang terbaca tanpa JavaScript.
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
            'nodes'           => $analytics->nodeDifficulty($filters),
            'cohort'          => model(ParticipantModel::class)->cohortSummary($filters),
            'ages'            => model(ParticipantModel::class)->ageDistribution($filters),
            'digitalSecurity' => $analytics->digitalSecurityLiteracy($filters),
            'recentSessions'  => $this->recentSessions($filters),
            'study'           => model(ResearchStudyModel::class)->activeStudy(),
            'isAdmin'         => $this->isAdmin(),
        ]);
    }

    /**
     * Sepuluh sesi terakhir dalam cakupan pemanggil.
     *
     * @return list<array<string, mixed>>
     */
    private function recentSessions(array $filters): array
    {
        $builder = db_connect()->table('game_sessions')
            ->select('game_sessions.id, game_sessions.status, game_sessions.locale, game_sessions.started_at, game_sessions.duration_ms', false)
            ->select('participants.participant_code, research_phases.code AS phase_code, session_progress.completed_nodes, session_progress.total_score', false)
            ->join('participants', 'participants.id = game_sessions.participant_id')
            ->join('research_phases', 'research_phases.id = game_sessions.phase_id')
            ->join('session_progress', 'session_progress.session_id = game_sessions.id', 'left')
            ->where('participants.deleted_at', null);

        apply_research_filters($builder, $filters, ['level_id', 'node_id']);

        return $builder->orderBy('game_sessions.started_at', 'DESC')->limit(10)->get()->getResultArray();
    }
}
