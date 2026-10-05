<?php

namespace App\Models;

use App\Entities\ChallengeItem;
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
        'challenge_node_id', 'item_key', 'sequence', 'variant_group', 'interaction_type',
        'prompt_id', 'prompt_en', 'source_text_id', 'source_text_en', 'passage_id',
        'answer_key_json', 'media_asset_id', 'audio_prompt_id', 'audio_prompt_en_id',
        'indicator_id', 'bloom_level', 'config_json',
        'reference_source', 'review_status', 'review_note', 'scorable', 'is_active',
    ];
    protected $validationRules = [
        'challenge_node_id' => 'required|is_natural_no_zero',
        'item_key'          => ['label' => 'Kode soal', 'rules' => 'required|max_length[100]'],
        'interaction_type'  => ['label' => 'Jenis soal', 'rules' => 'required|in_list[puzzle_arrange,fill_blank_bank,verdict_card,find_object]'],
        'variant_group'     => ['label' => 'Variasi', 'rules' => 'permit_empty|alpha_numeric|max_length[10]'],
        'review_status'     => ['label' => 'Status pemeriksaan isi', 'rules' => 'permit_empty|in_list[draft,needs_verification,verified]'],
        'bloom_level'       => ['label' => 'Tingkat berpikir (Bloom)', 'rules' => 'permit_empty|in_list[C1,C2,C3,C4,C5,C6]'],
    ];
    protected $beforeInsert      = ['encodeJson'];
    protected $beforeUpdate      = ['encodeJson'];
    protected $beforeInsertBatch = ['encodeJsonBatch'];

    /** @var list<string> kolom JSON milik tabel ini */
    protected array $jsonFields = ['answer_key_json', 'config_json'];

    /**
     * Seluruh bank soal aktif satu node, urut variasi lalu sequence.
     *
     * @return list<ChallengeItem>
     */
    public function bankForNode(int $nodeId): array
    {
        return $this->where('challenge_node_id', $nodeId)
            ->where('is_active', 1)
            ->orderBy('variant_group', 'ASC')
            ->orderBy('sequence', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    /**
     * Soal aktif dikelompokkan per variasi. Soal tanpa variant_group masuk
     * kelompok `a` (bank lama satu variasi).
     *
     * @param list<ChallengeItem> $bank
     *
     * @return array<string, list<ChallengeItem>> keyed by variant_group, urut nama variasi
     */
    public static function groupByVariant(array $bank): array
    {
        $groups = [];

        foreach ($bank as $item) {
            $group = trim((string) $item->variant_group);
            $groups[$group === '' ? 'a' : $group][] = $item;
        }

        ksort($groups, SORT_STRING);

        foreach ($groups as $group => $items) {
            usort($items, static fn (ChallengeItem $x, ChallengeItem $y): int => [$x->sequence, $x->id] <=> [$y->sequence, $y->id]);
            $groups[$group] = $items;
        }

        return $groups;
    }

    /**
     * Memilih satu variasi untuk pengerjaan baru (Dok 1 §9.2): variasi yang
     * sudah pernah didapat siswa ($excludeGroups) dibuang selama masih ada
     * yang lain, lalu satu dipilih dengan random_int() — acak sungguhan, tidak
     * berbenih. Semua soal aktif variasi itu dipakai, urut sequence; objek
     * jebakan `cari` ikut di akhir agar tetap tampil di adegan.
     *
     * @param list<string>             $excludeGroups
     * @param list<ChallengeItem>|null $bank          bank yang sudah dimuat, hindari query ulang
     *
     * @return array{group: string, items: list<ChallengeItem>} items kosong bila bank kosong
     */
    public function pickVariant(int $nodeId, array $excludeGroups, ?array $bank = null): array
    {
        $groups = self::groupByVariant($bank ?? $this->bankForNode($nodeId));

        if ($groups === []) {
            return ['group' => '', 'items' => []];
        }

        $candidates = array_values(array_diff(array_keys($groups), $excludeGroups));

        if ($candidates === []) {
            $candidates = array_keys($groups);
        }

        $group = (string) $candidates[random_int(0, count($candidates) - 1)];

        $targets = [];
        $decoys  = [];

        foreach ($groups[$group] as $item) {
            if ($item->isDecoy()) {
                $decoys[] = $item;
            } else {
                $targets[] = $item;
            }
        }

        return ['group' => $group, 'items' => array_merge($targets, $decoys)];
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
}
