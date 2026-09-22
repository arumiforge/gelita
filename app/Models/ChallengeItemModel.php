<?php

namespace App\Models;

use App\Entities\ChallengeItem;
use App\Entities\ChallengeOption;
use App\Models\Traits\EncodesJson;
use CodeIgniter\Model;

class ChallengeItemModel extends Model
{
    use EncodesJson;

    protected $table         = 'challenge_items';
    protected $primaryKey    = 'id';
    protected $returnType    = ChallengeItem::class;
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $allowedFields = [
        'challenge_node_id', 'item_key', 'sequence', 'interaction_type',
        'prompt_id', 'prompt_en', 'source_text_id', 'source_text_en', 'passage_id',
        'answer_key_json', 'media_asset_id', 'indicator_id', 'config_json',
        'reference_source', 'review_status', 'review_note', 'scorable', 'is_active',
    ];
    protected $validationRules = [
        'challenge_node_id' => 'required|is_natural_no_zero',
        'item_key'          => 'required|max_length[100]',
        'interaction_type'  => 'required|in_list[puzzle_arrange,ordering,fill_blank_bank,fill_blank_free,verdict_card,verdict_reason,single_choice,source_trust,find_object]',
        'review_status'     => 'permit_empty|in_list[draft,needs_verification,verified]',
    ];
    protected $beforeInsert      = ['encodeJson'];
    protected $beforeUpdate      = ['encodeJson'];
    protected $beforeInsertBatch = ['encodeJsonBatch'];

    /** @var list<string> kolom JSON milik tabel ini */
    protected array $jsonFields = ['answer_key_json', 'config_json'];

    /**
     * Seluruh bank soal aktif satu node, urut sequence.
     *
     * @return list<ChallengeItem>
     */
    public function bankForNode(int $nodeId): array
    {
        return $this->where('challenge_node_id', $nodeId)
            ->where('is_active', 1)
            ->orderBy('sequence', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    /**
     * Memilih item untuk satu attempt.
     *
     * mode 'fixed'  → seeded_shuffle(bank, seed): butir identik pada pretest & posttest
     * mode 'random' → acak penuh
     *
     * Item decoy (engine `cari`) tidak dihitung sebagai target, tetapi selalu
     * ikut dikirim agar objek jebakan tetap tampil di layar.
     * Bila bank lebih sedikit dari $count, seluruh bank dipakai.
     *
     * @param list<ChallengeItem>|null $bank bank yang sudah dimuat, hindari query ulang
     *
     * @return list<ChallengeItem>
     */
    public function pickForAttempt(int $nodeId, int $count, string $mode, string $seed, ?array $bank = null): array
    {
        $bank ??= $this->bankForNode($nodeId);

        $targets = [];
        $decoys  = [];

        foreach ($bank as $item) {
            if ($item->isDecoy()) {
                $decoys[] = $item;
            } else {
                $targets[] = $item;
            }
        }

        $ordered = $mode === 'random'
            ? $this->shuffleRandom($targets)
            : seeded_shuffle($targets, $seed);

        $selected = array_slice($ordered, 0, max(1, $count));

        return array_merge($selected, $decoys);
    }

    /**
     * Opsi jawaban untuk banyak item sekaligus — satu query (hindari N+1).
     *
     * @param list<int> $itemIds
     *
     * @return array<int, list<ChallengeOption>> keyed by challenge_item_id
     */
    public function withOptions(array $itemIds): array
    {
        $itemIds = array_values(array_unique(array_filter(array_map('intval', $itemIds))));

        if ($itemIds === []) {
            return [];
        }

        $options = model(ChallengeOptionModel::class)->forItems($itemIds);
        $out     = array_fill_keys($itemIds, []);

        foreach ($options as $option) {
            $out[$option->challenge_item_id][] = $option;
        }

        return $out;
    }

    /** Item yang sudah pernah dijawab tidak boleh diganti kunci jawabannya. */
    public function hasResponses(int $itemId): bool
    {
        return $this->db->table('item_responses')
            ->where('challenge_item_id', $itemId)
            ->countAllResults() > 0;
    }

    public function findByKey(string $itemKey): ?ChallengeItem
    {
        return $this->where('item_key', trim($itemKey))->first();
    }

    /** @param list<ChallengeItem> $items */
    private function shuffleRandom(array $items): array
    {
        $items = array_values($items);
        shuffle($items);

        return $items;
    }
}
