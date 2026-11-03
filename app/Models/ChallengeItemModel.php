<?php

namespace App\Models;

use App\Entities\ChallengeItem;
use App\Libraries\BankRules;
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
        'round', 'dimension', 'subaspect', 'akm_level', 'akm_level_source', 'pirls_process', 'bloom_level_source',
        'testlet_key', 'error_code', 'd2_topic', 'd3_clue_type', 'explanation_id', 'explanation_en', 'surface_json',
    ];
    protected $validationRules = [
        'challenge_node_id' => 'required|is_natural_no_zero',
        'item_key'          => ['label' => 'Kode soal', 'rules' => 'required|max_length[100]'],
        'interaction_type'  => ['label' => 'Jenis soal', 'rules' => 'required|in_list[puzzle_arrange,fill_blank_bank,verdict_card,find_object,find_part,scenario_choice]'],
        'variant_group'     => ['label' => 'Variasi', 'rules' => 'permit_empty|alpha_numeric|max_length[10]'],
        'review_status'     => ['label' => 'Status pemeriksaan isi', 'rules' => 'permit_empty|in_list[draft,needs_verification,verified]'],
        'bloom_level'       => ['label' => 'Tingkat berpikir (Bloom)', 'rules' => 'permit_empty|in_list[C1,C2,C3,C4,C5,C6]'],
    ];
    protected $beforeInsert      = ['encodeJson'];
    protected $beforeUpdate      = ['encodeJson'];
    protected $beforeInsertBatch = ['encodeJsonBatch'];

    /** @var list<string> kolom JSON milik tabel ini */
    protected array $jsonFields = ['answer_key_json', 'config_json', 'surface_json'];

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
     * Soal aktif dikelompokkan per variasi. Soal tanpa variant_group (soal
     * yang ditambah lewat editor tanpa variasi) masuk kelompok `a`.
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
     * Picks one variant for a new attempt (Dok 1 §9.2, 31 §11). $group, when
     * given and present in the bank, is used as it is (booklet, VariantPicker).
     * Otherwise the first attempt (nothing served yet) draws from
     * BankRules::FIRST_ATTEMPT_GROUPS (a, b) when the bank has any of them,
     * and later attempts drop the variants the student already got while
     * others remain. The draw uses random_int(), truly random and unseeded.
     *
     * The shared group `x` is never a variant: its active items join every
     * attempt (K11): Cari Objek round 1 first, Benar atau Salah anchor cards
     * after the variant's statements. `cari` decoys follow their targets.
     *
     * @param list<string>             $excludeGroups
     * @param list<ChallengeItem>|null $bank          bank yang sudah dimuat, hindari query ulang
     *
     * @return array{group: string, items: list<ChallengeItem>} items kosong bila bank kosong
     */
    public function pickVariant(int $nodeId, array $excludeGroups, ?array $bank = null, ?string $group = null): array
    {
        $groups = self::groupByVariant($bank ?? $this->bankForNode($nodeId));
        $shared = $groups[BankRules::SHARED_GROUP] ?? [];
        unset($groups[BankRules::SHARED_GROUP]);

        if ($groups === []) {
            return ['group' => '', 'items' => self::targetsFirst($shared)];
        }

        $all = array_map('strval', array_keys($groups));

        if ($group === null || ! isset($groups[$group])) {
            $candidates = $excludeGroups === []
                ? array_values(array_intersect($all, BankRules::FIRST_ATTEMPT_GROUPS))
                : array_values(array_diff($all, $excludeGroups));

            if ($candidates === []) {
                $candidates = $all;
            }

            $group = (string) $candidates[$this->randomIndex(count($candidates))];
        }

        $items = self::targetsFirst($groups[$group]);

        if ($shared !== []) {
            $roundOne = array_filter($shared, static fn (ChallengeItem $i): bool => $i->interaction_type === 'find_object');
            $items    = $roundOne !== []
                ? [...self::targetsFirst($shared), ...$items]
                : [...$items, ...$shared];
        }

        return ['group' => $group, 'items' => $items];
    }

    /**
     * @param list<ChallengeItem> $items
     *
     * @return list<ChallengeItem> targets first, `cari` decoys last
     */
    private static function targetsFirst(array $items): array
    {
        $targets = [];
        $decoys  = [];

        foreach ($items as $item) {
            if ($item->isDecoy()) {
                $decoys[] = $item;
            } else {
                $targets[] = $item;
            }
        }

        return [...$targets, ...$decoys];
    }

    /**
     * Indeks acak 0..$count−1 untuk pickVariant(). Dipisah agar data simulasi
     * (`gelita:simulasi --seed`) dapat memakai pengacak berbenih.
     */
    protected function randomIndex(int $count): int
    {
        return random_int(0, $count - 1);
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
