<?php

namespace App\Models;

use CodeIgniter\Model;

class LearningIndicatorModel extends Model
{
    protected $table         = 'learning_indicators';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'code', 'name_id', 'name_en', 'description_id', 'description_en', 'domain', 'is_active',
    ];
    protected $validationRules = [
        'code'    => 'required|max_length[50]|is_unique[learning_indicators.code,id,{id}]',
        'name_id' => 'required|max_length[200]',
        'name_en' => 'required|max_length[200]',
    ];

    /** @return array<string, array<string, mixed>> keyed by code */
    public function map(): array
    {
        $out = [];

        foreach ($this->where('is_active', 1)->orderBy('id', 'ASC')->findAll() as $row) {
            $out[(string) $row['code']] = $row;
        }

        return $out;
    }
}
