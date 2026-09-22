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
            ->join('participants', 'participants.id = participant_feedback.participant_id');

        if ($scope !== null) {
            $feedback->where('participants.school_id', $scope);
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
