<?php

namespace App\Models;

use App\Entities\ChallengeItem;
use App\Entities\ItemResponse;
use App\Models\Traits\EncodesJson;
use CodeIgniter\Model;

class ItemResponseModel extends Model
{
    use EncodesJson;

    protected $table         = 'item_responses';
    protected $primaryKey    = 'id';
    protected $returnType    = ItemResponse::class;
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $allowedFields = [
        'challenge_attempt_id', 'challenge_item_id', 'display_order', 'status',
        'first_answer_json', 'final_answer_json', 'reason_text', 'reason_review_status',
        'first_pass_correct', 'is_correct', 'change_count', 'hint_used', 'wrong_click_count',
        'started_at', 'answered_at', 'duration_ms',
    ];
    protected $validationRules = [
        'challenge_attempt_id' => 'required|is_natural_no_zero',
        'challenge_item_id'    => 'required|is_natural_no_zero',
        'status'               => 'permit_empty|in_list[pending,answered,skipped]',
    ];
    protected $beforeInsert      = ['encodeJson'];
    protected $beforeUpdate      = ['encodeJson'];
    protected $beforeInsertBatch = ['encodeJsonBatch'];

    /** @var list<string> kolom JSON milik tabel ini */
    protected array $jsonFields = ['first_answer_json', 'final_answer_json'];

    /**
     * Satu baris pending per item terpilih, urut sesuai urutan tampil.
     *
     * @param list<ChallengeItem> $items
     */
    public function createPlaceholders(int $attemptId, array $items): void
    {
        $rows = [];
        $now  = date('Y-m-d H:i:s');

        foreach (array_values($items) as $index => $item) {
            $rows[] = [
                'challenge_attempt_id' => $attemptId,
                'challenge_item_id'    => $item->id,
                'display_order'        => $index + 1,
                'status'               => 'pending',
                'started_at'           => $now,
            ];
        }

        if ($rows !== []) {
            $this->insertBatch($rows);
        }
    }

    /** @return array<int, ItemResponse> keyed by challenge_item_id */
    public function forAttempt(int $attemptId): array
    {
        $out = [];

        foreach ($this->where('challenge_attempt_id', $attemptId)->orderBy('display_order', 'ASC')->findAll() as $response) {
            $out[$response->challenge_item_id] = $response;
        }

        return $out;
    }

    public function findOne(int $attemptId, int $itemId): ?ItemResponse
    {
        return $this->where('challenge_attempt_id', $attemptId)
            ->where('challenge_item_id', $itemId)
            ->first();
    }

    /**
     * Jumlah benar, dihitung hanya dari item yang scorable.
     *
     * @return array{first_pass: int, final: int, scorable: int}
     */
    public function countCorrect(int $attemptId): array
    {
        $row = $this->db->table('item_responses ir')
            ->select('COUNT(*) AS scorable', false)
            ->select('SUM(CASE WHEN ir.first_pass_correct = 1 THEN 1 ELSE 0 END) AS first_pass', false)
            ->select('SUM(CASE WHEN ir.is_correct = 1 THEN 1 ELSE 0 END) AS final', false)
            ->join('challenge_items ci', 'ci.id = ir.challenge_item_id')
            ->where('ir.challenge_attempt_id', $attemptId)
            ->where('ci.scorable', 1)
            ->get()
            ->getRowArray();

        return [
            'scorable'   => (int) ($row['scorable'] ?? 0),
            'first_pass' => (int) ($row['first_pass'] ?? 0),
            'final'      => (int) ($row['final'] ?? 0),
        ];
    }

    /**
     * Analisis butir: muncul, benar, rata durasi, jawaban salah tersering.
     *
     * @return array<int, array{item_id: int, item_key: string, appeared: int, correct: int,
     *                          p: float, mean_duration_ms: int, top_wrong: ?string}>
     */
    public function itemAnalysis(array $filters = []): array
    {
        $rows = $this->analysisBuilder($filters)
            ->select('ci.id AS item_id, ci.item_key', false)
            ->select('COUNT(*) AS appeared', false)
            ->select('SUM(CASE WHEN ir.first_pass_correct = 1 THEN 1 ELSE 0 END) AS correct', false)
            ->select('AVG(ir.duration_ms) AS mean_duration_ms', false)
            ->where('ir.status', 'answered')
            ->groupBy('ci.id, ci.item_key')
            ->orderBy('ci.item_key', 'ASC')
            ->get()
            ->getResultArray();

        $out = [];

        foreach ($rows as $row) {
            $appeared = (int) $row['appeared'];
            $itemId   = (int) $row['item_id'];

            $out[$itemId] = [
                'item_id'          => $itemId,
                'item_key'         => (string) $row['item_key'],
                'appeared'         => $appeared,
                'correct'          => (int) $row['correct'],
                'p'                => $appeared > 0 ? round((int) $row['correct'] / $appeared, 4) : 0.0,
                'mean_duration_ms' => (int) round((float) ($row['mean_duration_ms'] ?? 0)),
                'top_wrong'        => $this->topWrongAnswer($itemId, $filters),
            ];
        }

        return $out;
    }

    /** Jawaban salah yang paling sering muncul pada satu butir. */
    public function topWrongAnswer(int $itemId, array $filters = []): ?string
    {
        $row = $this->analysisBuilder($filters)
            ->select('ir.final_answer_json AS answer, COUNT(*) AS total', false)
            ->where('ir.challenge_item_id', $itemId)
            ->where('ir.is_correct', 0)
            ->where('ir.final_answer_json IS NOT NULL')
            ->groupBy('ir.final_answer_json')
            ->orderBy('total', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        return $row === null ? null : (string) $row['answer'];
    }

    private function analysisBuilder(array $filters): \CodeIgniter\Database\BaseBuilder
    {
        $builder = $this->db->table('item_responses ir')
            ->join('challenge_items ci', 'ci.id = ir.challenge_item_id')
            ->join('challenge_attempts ca', 'ca.id = ir.challenge_attempt_id')
            ->join('game_sessions', 'game_sessions.id = ca.session_id')
            ->join('participants', 'participants.id = game_sessions.participant_id')
            ->join('research_phases', 'research_phases.id = game_sessions.phase_id')
            ->join('challenge_nodes', 'challenge_nodes.id = ca.challenge_node_id')
            ->where('participants.deleted_at', null);

        return apply_research_filters($builder, $filters);
    }
}
