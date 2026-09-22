<?php

namespace App\Models;

use CodeIgniter\Model;

class HintModel extends Model
{
    protected $table         = 'hints';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'challenge_node_id', 'challenge_item_id', 'sequence', 'text_id', 'text_en', 'is_active',
    ];
    protected $validationRules = [
        'sequence' => 'required|is_natural_no_zero',
        'text_id'  => 'required',
        'text_en'  => 'required',
    ];

    /** Petunjuk tingkat node (challenge_item_id NULL) */
    public function forNode(int $nodeId): array
    {
        return $this->where('challenge_node_id', $nodeId)
            ->where('challenge_item_id', null)
            ->where('is_active', 1)
            ->orderBy('sequence', 'ASC')
            ->findAll();
    }

    /** Petunjuk tingkat item */
    public function forItem(int $itemId): array
    {
        return $this->where('challenge_item_id', $itemId)
            ->where('is_active', 1)
            ->orderBy('sequence', 'ASC')
            ->findAll();
    }
}
