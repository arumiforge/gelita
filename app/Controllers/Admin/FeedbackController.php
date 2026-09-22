<?php

namespace App\Controllers\Admin;

use App\Models\ParticipantFeedbackModel;

/**
 * Kritik & saran dari Balai Refleksi.
 * Guru hanya melihat masukan peserta sekolahnya.
 */
class FeedbackController extends BaseAdminController
{
    private const PER_PAGE = 25;

    public function index(): string
    {
        $feedback = model(ParticipantFeedbackModel::class);
        $filters  = $this->scopedFilters();
        $scope    = $this->schoolScope();

        $feedback
            ->select('participant_feedback.*, participants.participant_code, participants.display_name, participants.class_level')
            ->select('research_phases.code AS phase_code')
            ->join('participants', 'participants.id = participant_feedback.participant_id')
            ->join('game_sessions', 'game_sessions.id = participant_feedback.session_id', 'left')
            ->join('research_phases', 'research_phases.id = game_sessions.phase_id', 'left');

        if ($scope !== null) {
            $feedback->where('participants.school_id', $scope);
        } elseif (isset($filters['school_id'])) {
            $feedback->where('participants.school_id', $filters['school_id']);
        }

        if (isset($filters['phase_code'])) {
            $feedback->where('research_phases.code', $filters['phase_code']);
        }

        if (isset($filters['class_level'])) {
            $feedback->where('participants.class_level', $filters['class_level']);
        }

        $rows = $feedback->orderBy('participant_feedback.submitted_at', 'DESC')->paginate(self::PER_PAGE);

        return $this->panel('admin/feedback/index', 'Kritik & saran', [
            'filters'      => $filters,
            'rows'         => $rows,
            'pager'        => $feedback->pager,
            'distribution' => model(ParticipantFeedbackModel::class)->ratingDistribution($filters),
            'isAdmin'      => $this->isAdmin(),
        ]);
    }
}
