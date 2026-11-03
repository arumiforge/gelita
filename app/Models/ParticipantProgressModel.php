<?php

namespace App\Models;

use App\Entities\GameSession;
use CodeIgniter\Model;

/**
 * A student's progress in one study, carried from meeting to meeting
 * (research revision 10, part 2: 28 §8; replaces the old per-game-session progress table).
 * Written only by ChallengeService::refreshProgress() and openNode().
 */
class ParticipantProgressModel extends Model
{
    protected $table         = 'participant_progress';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'participant_id', 'study_id', 'current_level_id', 'current_node_id', 'unlocked_level_sequence',
        'completed_nodes', 'completed_levels', 'total_stars', 'updated_at',
    ];

    /**
     * The row for this student and study; created when missing. A second
     * request creating it at the same moment hits the unique key, and the
     * row it made is read back instead.
     *
     * @return array<string, mixed>
     */
    public function ensure(int $participantId, int $studyId): array
    {
        $row = $this->findFor($participantId, $studyId);

        if ($row !== null) {
            return $row;
        }

        $this->db->table($this->table)->ignore(true)->insert([
            'participant_id'          => $participantId,
            'study_id'                => $studyId,
            'unlocked_level_sequence' => 1,
            'completed_nodes'         => 0,
            'completed_levels'        => 0,
            'total_stars'             => 0,
        ]);

        return $this->findFor($participantId, $studyId) ?? throw new \RuntimeException('Kemajuan siswa gagal dibuat.');
    }

    /** @return array<string, mixed> the row of the session's student and study */
    public function forSession(GameSession $session): array
    {
        return $this->ensure($session->participant_id, $session->study_id);
    }

    /** @return array<string, mixed>|null */
    public function findFor(int $participantId, int $studyId): ?array
    {
        return $this->where('participant_id', $participantId)->where('study_id', $studyId)->first();
    }
}
