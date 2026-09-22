<?php

namespace App\Models;

use App\Entities\ChallengeOption;
use CodeIgniter\Model;

class ChallengeOptionModel extends Model
{
    protected $table         = 'challenge_options';
    protected $primaryKey    = 'id';
    protected $returnType    = ChallengeOption::class;
    protected $useTimestamps = false;
    protected $allowedFields = [
        'challenge_item_id', 'option_key', 'label_id', 'label_en', 'media_asset_id',
        'is_correct', 'feedback_id', 'feedback_en', 'display_order',
    ];
    protected $validationRules = [
        'challenge_item_id' => 'required|is_natural_no_zero',
        'option_key'        => 'required|max_length[60]',
        'label_id'          => 'required',
        'label_en'          => 'required',
    ];

    /**
     * @param list<int> $itemIds
     *
     * @return list<ChallengeOption>
     */
    public function forItems(array $itemIds): array
    {
        $itemIds = array_values(array_unique(array_filter(array_map('intval', $itemIds))));

        if ($itemIds === []) {
            return [];
        }

        return $this->whereIn('challenge_item_id', $itemIds)
            ->orderBy('challenge_item_id', 'ASC')
            ->orderBy('display_order', 'ASC')
            ->findAll();
    }

    public function findByKey(int $itemId, string $optionKey): ?ChallengeOption
    {
        return $this->where('challenge_item_id', $itemId)->where('option_key', $optionKey)->first();
    }
}
