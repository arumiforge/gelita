<?php

namespace App\Models;

use CodeIgniter\Model;

class AudioAssetModel extends Model
{
    protected $table         = 'audio_assets';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'media_asset_id', 'locale', 'character_code', 'context_code', 'transcript',
        'production_method', 'voice_profile', 'duration_ms', 'approval_status',
        'approved_by', 'approved_at', 'version',
    ];
    protected $validationRules = [
        'media_asset_id'  => 'required|is_natural_no_zero',
        'locale'          => 'required|valid_locale',
        'context_code'    => 'required|max_length[80]',
        'transcript'      => 'required',
        'approval_status' => 'permit_empty|in_list[draft,review,approved,rejected]',
    ];

    /**
     * Audio yang boleh diputar pemain. Status selain `approved` tidak pernah dikirim.
     */
    public function approvedFor(string $context, string $locale): ?array
    {
        return $this->where('context_code', $context)
            ->where('locale', $locale)
            ->where('approval_status', 'approved')
            ->orderBy('id', 'DESC')
            ->first();
    }

    /** @return list<array<string, mixed>> */
    public function approvedForCharacter(string $characterCode, string $locale): array
    {
        return $this->where('character_code', $characterCode)
            ->where('locale', $locale)
            ->where('approval_status', 'approved')
            ->orderBy('context_code', 'ASC')
            ->findAll();
    }
}
