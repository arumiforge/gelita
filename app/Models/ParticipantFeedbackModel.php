<?php

namespace App\Models;

use CodeIgniter\Model;

class ParticipantFeedbackModel extends Model
{
    protected $table         = 'participant_feedback';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'participant_id', 'session_id', 'rating',
        'liked_most', 'hardest_part', 'new_learning', 'suggestion', 'submitted_at',
    ];
    protected $validationRules = [
        'participant_id' => 'required|is_natural_no_zero',
        'rating'         => 'required|greater_than[0]|less_than[6]',
    ];

    /** @return list<array<string, mixed>> */
    public function forStudy(int $studyId): array
    {
        return $this->db->table('participant_feedback pf')
            ->select('pf.*, participants.participant_code')
            ->join('game_sessions', 'game_sessions.id = pf.session_id')
            ->join('participants', 'participants.id = pf.participant_id')
            ->where('game_sessions.study_id', $studyId)
            ->where('participants.deleted_at', null)
            ->orderBy('pf.submitted_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return array{count: int, mean: float, distribution: array<int, int>}
     */
    public function ratingDistribution(array $filters = []): array
    {
        $builder = $this->db->table('participant_feedback pf')
            ->join('game_sessions', 'game_sessions.id = pf.session_id')
            ->join('participants', 'participants.id = pf.participant_id')
            ->join('research_phases', 'research_phases.id = game_sessions.phase_id')
            ->where('participants.deleted_at', null);

        apply_research_filters($builder, $filters, ['level_id', 'node_id']);

        $rows = $builder
            ->select('pf.rating, COUNT(*) AS total', false)
            ->groupBy('pf.rating')
            ->orderBy('pf.rating', 'ASC')
            ->get()
            ->getResultArray();

        $distribution = array_fill(1, 5, 0);
        $count        = 0;
        $sum          = 0;

        foreach ($rows as $row) {
            $rating = max(1, min(5, (int) $row['rating']));
            $total  = (int) $row['total'];

            $distribution[$rating] = $total;
            $count += $total;
            $sum += $rating * $total;
        }

        return [
            'count'        => $count,
            'mean'         => $count > 0 ? round($sum / $count, 2) : 0.0,
            'distribution' => $distribution,
        ];
    }
}
