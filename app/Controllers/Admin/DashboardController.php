<?php

namespace App\Controllers\Admin;

use App\Models\ParticipantModel;
use App\Models\ResearchStudyModel;

/**
 * Ringkasan panel: hitungan data mentah (AnalyticsService::summary()) dan
 * sesi terakhir. Controller hanya meneruskan filter beserta cakupan sekolah
 * pemanggil.
 */
class DashboardController extends BaseAdminController
{
    public function index(): string
    {
        $filters = $this->scopedFilters();

        return $this->panel('admin/dashboard', 'Beranda', [
            'filters'         => $filters,
            'summary'         => $this->analytics()->summary($filters),
            'cohort'          => model(ParticipantModel::class)->cohortSummary($filters),
            'recentSessions'  => $this->recentSessions($filters),
            'study'           => model(ResearchStudyModel::class)->activeStudy(),
            'isAdmin'         => $this->isAdmin(),
            // pratinjau penghapusan buatan retensi: hanya admin yang boleh memutuskan
            'retentionAlerts' => $this->isAdmin() ? service('retentionService')->pendingRetentionRequests() : [],
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
            ->select('participants.participant_code, session_progress.completed_nodes, session_progress.total_stars', false)
            ->join('participants', 'participants.id = game_sessions.participant_id')
            ->join('session_progress', 'session_progress.session_id = game_sessions.id', 'left')
            ->where('participants.deleted_at', null);

        apply_research_filters($builder, $filters, ['level_id', 'node_id']);

        return $builder->orderBy('game_sessions.started_at', 'DESC')->limit(10)->get()->getResultArray();
    }
}
