<?php

namespace App\Models;

use App\Entities\ChallengeAttempt;
use App\Entities\GameSession;
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
        'session_id', 'challenge_node_id', 'attempt_no', 'variant_group', 'status',
        'completion_reason', 'selected_item_ids_json', 'scorable_items',
        'first_pass_correct', 'final_correct', 'check_count', 'max_tries',
        'hint_count', 'answer_change_count', 'audio_use_count', 'stars',
        'started_at', 'completed_at', 'duration_ms', 'variant_source', 'booklet_at_open',
        // Part 7 (33 K6)
        'is_retake',
    ];
    protected $validationRules = [
        'session_id'        => 'required|is_natural_no_zero',
        'challenge_node_id' => 'required|is_natural_no_zero',
        'status'            => 'permit_empty|in_list[in_progress,completed,abandoned]',
        'completion_reason' => 'permit_empty|in_list[solved,tries_exhausted]',
    ];
    protected $beforeInsert      = ['encodeJson'];
    protected $beforeUpdate      = ['encodeJson'];
    protected $beforeInsertBatch = ['encodeJsonBatch'];

    /** @var list<string> kolom JSON milik tabel ini */
    protected array $jsonFields = ['selected_item_ids_json'];

    /**
     * Next attempt number of the student of this game session at the node,
     * counted over every game session of that student (part 7, 33 §7.3): an
     * attempt moved to a new session must not share its number with a new one.
     */
    public function nextAttemptNo(int $sessionId, int $nodeId): int
    {
        $participantId = (int) ($this->db->table('game_sessions')->select('participant_id')->where('id', $sessionId)->get()->getRow('participant_id') ?? 0);

        $row = $this->db->table('challenge_attempts ca')
            ->selectMax('ca.attempt_no', 'max_no')
            ->join('game_sessions gs', 'gs.id = ca.session_id')
            ->where('gs.participant_id', $participantId)
            ->where('ca.challenge_node_id', $nodeId)
            ->get()
            ->getRowArray();

        return ((int) ($row['max_no'] ?? 0)) + 1;
    }

    /**
     * Part 7 (33 §7.3, K3): the newest `in_progress` attempt of one student at
     * a node, in any of the student's game sessions of the study (all studies
     * when $studyId is null).
     */
    public function openForParticipant(int $participantId, int $nodeId, ?int $studyId): ?ChallengeAttempt
    {
        $builder = $this->select('challenge_attempts.*')
            ->join('game_sessions', 'game_sessions.id = challenge_attempts.session_id')
            ->where('game_sessions.participant_id', $participantId)
            ->where('challenge_attempts.challenge_node_id', $nodeId)
            ->where('challenge_attempts.status', 'in_progress');

        if ($studyId !== null) {
            $builder->where('game_sessions.study_id', $studyId);
        }

        return $builder->orderBy('challenge_attempts.id', 'DESC')->first();
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
     * Pengerjaan `completed` terbaik per node untuk satu sesi: bintang
     * terbanyak, lalu yang paling awal bila sama.
     *
     * @return array<int, ChallengeAttempt> keyed by challenge_node_id
     */
    public function completedForSession(int $sessionId): array
    {
        $attempts = $this->where('session_id', $sessionId)
            ->where('status', 'completed')
            ->orderBy('challenge_node_id', 'ASC')
            ->orderBy('stars', 'ASC')
            ->orderBy('attempt_no', 'DESC')
            ->findAll();

        $out = [];

        foreach ($attempts as $attempt) {
            $out[$attempt->challenge_node_id] = $attempt;
        }

        return $out;
    }

    /**
     * Best `completed` attempt per node of one student across every game
     * session of a study (research revision 10, part 2: progress follows the
     * student, 28 §8): most stars, then the earliest when equal.
     *
     * @return array<int, ChallengeAttempt> keyed by challenge_node_id
     */
    public function completedForParticipant(int $participantId, int $studyId): array
    {
        $attempts = $this->select('challenge_attempts.*')
            ->join('game_sessions', 'game_sessions.id = challenge_attempts.session_id')
            ->where('game_sessions.participant_id', $participantId)
            ->where('game_sessions.study_id', $studyId)
            ->where('challenge_attempts.status', 'completed')
            ->orderBy('challenge_attempts.challenge_node_id', 'ASC')
            ->orderBy('challenge_attempts.stars', 'ASC')
            ->orderBy('challenge_attempts.id', 'DESC')
            ->findAll();

        $out = [];

        foreach ($attempts as $attempt) {
            $out[$attempt->challenge_node_id] = $attempt;
        }

        return $out;
    }

    /**
     * completedForParticipant() for the student and study of a game session.
     *
     * @return array<int, ChallengeAttempt> keyed by challenge_node_id
     */
    public function completedFor(GameSession $session): array
    {
        return $this->completedForParticipant($session->participant_id, $session->study_id);
    }

    /**
     * Part 6 (32 K9): whether the student of this session already has any
     * attempt (open, closed, or abandoned) at the node in this study, across
     * every game session. Gates the bridge story once per student per study.
     */
    public function hasAttemptInStudy(GameSession $session, int $nodeId): bool
    {
        return $this->db->table('challenge_attempts ca')
            ->join('game_sessions gs', 'gs.id = ca.session_id')
            ->where('gs.participant_id', $session->participant_id)
            ->where('gs.study_id', $session->study_id)
            ->where('ca.challenge_node_id', $nodeId)
            ->countAllResults() > 0;
    }

    /**
     * Variasi yang sudah pernah didapat seorang siswa pada satu node, di semua
     * sesinya (Dok 2 §3.2) — termasuk pengerjaan yang ditinggalkan.
     *
     * @return list<string>
     */
    public function variantsServed(int $participantId, int $nodeId): array
    {
        $rows = $this->db->table('challenge_attempts ca')
            ->distinct()
            ->select('ca.variant_group')
            ->join('game_sessions gs', 'gs.id = ca.session_id')
            ->where('gs.participant_id', $participantId)
            ->where('ca.challenge_node_id', $nodeId)
            ->where('ca.variant_group IS NOT NULL', null, false)
            ->get()
            ->getResultArray();

        return array_values(array_map(static fn (array $r): string => (string) $r['variant_group'], $rows));
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
            ->join('challenge_nodes', 'challenge_nodes.id = challenge_attempts.challenge_node_id')
            ->where('participants.deleted_at', null);

        return apply_research_filters($builder, $filters);
    }
}
