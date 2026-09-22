<?php

namespace App\Models;

use App\Entities\ChallengeAttempt;
use App\Models\Traits\EncodesJson;
use CodeIgniter\Model;

class ChallengeAttemptModel extends Model
{
    use EncodesJson;

    protected $table         = 'challenge_attempts';
    protected $primaryKey    = 'id';
    protected $returnType    = ChallengeAttempt::class;
    protected $useTimestamps = false;
    protected $allowedFields = [
        'session_id', 'challenge_node_id', 'attempt_no', 'status',
        'selected_item_ids_json', 'scorable_items',
        'first_pass_correct', 'final_correct', 'first_pass_accuracy', 'final_accuracy',
        'check_count', 'hint_count', 'retry_count', 'answer_change_count', 'audio_use_count',
        'independence', 'score', 'stars', 'scoring_profile_id', 'scoring_version',
        'started_at', 'completed_at', 'duration_ms',
    ];
    protected $validationRules = [
        'session_id'        => 'required|is_natural_no_zero',
        'challenge_node_id' => 'required|is_natural_no_zero',
        'status'            => 'permit_empty|in_list[in_progress,completed,abandoned]',
    ];
    protected $beforeInsert      = ['encodeJson'];
    protected $beforeUpdate      = ['encodeJson'];
    protected $beforeInsertBatch = ['encodeJsonBatch'];

    /** @var list<string> kolom JSON milik tabel ini */
    protected array $jsonFields = ['selected_item_ids_json'];

    public function nextAttemptNo(int $sessionId, int $nodeId): int
    {
        $row = $this->db->table($this->table)
            ->selectMax('attempt_no', 'max_no')
            ->where('session_id', $sessionId)
            ->where('challenge_node_id', $nodeId)
            ->get()
            ->getRowArray();

        return ((int) ($row['max_no'] ?? 0)) + 1;
    }

    public function currentInProgress(int $sessionId, int $nodeId): ?ChallengeAttempt
    {
        return $this->where('session_id', $sessionId)
            ->where('challenge_node_id', $nodeId)
            ->where('status', 'in_progress')
            ->orderBy('attempt_no', 'DESC')
            ->first();
    }

    /**
     * Attempt `completed` terbaik per node untuk satu sesi.
     *
     * @return array<int, ChallengeAttempt> keyed by challenge_node_id
     */
    public function completedForSession(int $sessionId): array
    {
        $attempts = $this->where('session_id', $sessionId)
            ->where('status', 'completed')
            ->orderBy('challenge_node_id', 'ASC')
            ->orderBy('score', 'ASC')
            ->orderBy('attempt_no', 'ASC')
            ->findAll();

        $out = [];

        foreach ($attempts as $attempt) {
            $out[$attempt->challenge_node_id] = $attempt;
        }

        return $out;
    }

    /**
     * Statistik satu node untuk analitik kesulitan.
     *
     * @return array{attempts: int, mean_first_pass: float, mean_final: float,
     *               mean_retry: float, mean_hint: float, median_duration_ms: int, skip_rate: float}
     */
    public function statsForNode(int $nodeId, array $filters = []): array
    {
        $builder = $this->filteredBuilder($filters)->where('challenge_attempts.challenge_node_id', $nodeId);

        $row = $builder
            ->select('COUNT(*) AS attempts', false)
            ->select('AVG(challenge_attempts.first_pass_accuracy) AS mean_first_pass', false)
            ->select('AVG(challenge_attempts.final_accuracy) AS mean_final', false)
            ->select('AVG(challenge_attempts.retry_count) AS mean_retry', false)
            ->select('AVG(challenge_attempts.hint_count) AS mean_hint', false)
            ->select("SUM(CASE WHEN challenge_attempts.status = 'abandoned' THEN 1 ELSE 0 END) AS abandoned", false)
            ->get()
            ->getRowArray();

        $attempts = (int) ($row['attempts'] ?? 0);

        return [
            'attempts'           => $attempts,
            'mean_first_pass'    => round((float) ($row['mean_first_pass'] ?? 0), 2),
            'mean_final'         => round((float) ($row['mean_final'] ?? 0), 2),
            'mean_retry'         => round((float) ($row['mean_retry'] ?? 0), 2),
            'mean_hint'          => round((float) ($row['mean_hint'] ?? 0), 2),
            'median_duration_ms' => $this->medianDuration($nodeId, $filters),
            'skip_rate'          => $attempts > 0 ? round(((int) ($row['abandoned'] ?? 0)) / $attempts, 4) : 0.0,
        ];
    }

    /**
     * Median durasi attempt selesai. MySQL 8 belum punya fungsi median,
     * jadi nilai tengah diambil dari daftar durasi yang sudah diurutkan.
     */
    public function medianDuration(int $nodeId, array $filters = []): int
    {
        $rows = $this->filteredBuilder($filters)
            ->select('challenge_attempts.duration_ms')
            ->where('challenge_attempts.challenge_node_id', $nodeId)
            ->where('challenge_attempts.status', 'completed')
            ->where('challenge_attempts.duration_ms IS NOT NULL')
            ->orderBy('challenge_attempts.duration_ms', 'ASC')
            ->get()
            ->getResultArray();

        $count = count($rows);

        if ($count === 0) {
            return 0;
        }

        $middle = intdiv($count, 2);

        if ($count % 2 === 1) {
            return (int) $rows[$middle]['duration_ms'];
        }

        return (int) round(((int) $rows[$middle - 1]['duration_ms'] + (int) $rows[$middle]['duration_ms']) / 2);
    }

    /**
     * Builder attempt + join sesi/peserta agar filter penelitian dapat dipakai.
     * $filters mengikuti daftar di 03_MODEL_ENTITY.md.
     */
    public function filteredBuilder(array $filters = []): \CodeIgniter\Database\BaseBuilder
    {
        $builder = $this->db->table('challenge_attempts')
            ->join('game_sessions', 'game_sessions.id = challenge_attempts.session_id')
            ->join('participants', 'participants.id = game_sessions.participant_id')
            ->join('research_phases', 'research_phases.id = game_sessions.phase_id')
            ->join('challenge_nodes', 'challenge_nodes.id = challenge_attempts.challenge_node_id')
            ->where('participants.deleted_at', null);

        return apply_research_filters($builder, $filters);
    }
}
