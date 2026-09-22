<?php

namespace App\Models;

use App\Entities\ReadingPassage;
use CodeIgniter\Model;

class ReadingPassageModel extends Model
{
    protected $table         = 'reading_passages';
    protected $primaryKey    = 'id';
    protected $returnType    = ReadingPassage::class;
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $allowedFields = [
        'level_id', 'passage_key', 'title_id', 'title_en', 'body_id', 'body_en',
        'media_asset_id', 'reference_source', 'is_active',
    ];
    protected $validationRules = [
        'level_id'    => 'required|is_natural_no_zero',
        'passage_key' => 'required|max_length[60]|is_unique[reading_passages.passage_key,id,{id}]',
        'body_id'     => 'required',
        'body_en'     => 'required',
    ];

    /** @return list<ReadingPassage> */
    public function forLevel(int $levelId): array
    {
        return $this->where('level_id', $levelId)
            ->where('is_active', 1)
            ->orderBy('passage_key', 'ASC')
            ->findAll();
    }

    public function findByKey(string $key): ?ReadingPassage
    {
        return $this->where('passage_key', trim($key))->first();
    }

    /**
     * Teks bacaan untuk sekumpulan item — satu query, keyed by id.
     *
     * @param list<int> $ids
     *
     * @return array<int, ReadingPassage>
     */
    public function forIds(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        if ($ids === []) {
            return [];
        }

        $out = [];

        foreach ($this->whereIn('id', $ids)->findAll() as $passage) {
            $out[$passage->id] = $passage;
        }

        return $out;
    }
}
