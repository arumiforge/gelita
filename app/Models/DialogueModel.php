<?php

namespace App\Models;

use CodeIgniter\Model;

class DialogueModel extends Model
{
    protected $table         = 'dialogues';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'level_id', 'context_code', 'sequence', 'character_code',
        'title_id', 'title_en', 'text_id', 'text_en',
        'audio_id_asset_id', 'audio_en_asset_id', 'background_media_id', 'is_active',
    ];
    protected $validationRules = [
        'context_code'   => 'required|max_length[50]',
        'sequence'       => 'required|is_natural_no_zero',
        'character_code' => 'required|max_length[30]',
        'text_id'        => 'required',
        'text_en'        => 'required',
    ];

    /** Dialog pembuka permainan: level_id NULL, context_code `intro` */
    public function intro(): array
    {
        return $this->where('level_id', null)
            ->where('context_code', 'intro')
            ->where('is_active', 1)
            ->orderBy('sequence', 'ASC')
            ->findAll();
    }

    public function forLevel(int $levelId, string $context = 'level_open'): array
    {
        return $this->where('level_id', $levelId)
            ->where('context_code', $context)
            ->where('is_active', 1)
            ->orderBy('sequence', 'ASC')
            ->findAll();
    }
}
