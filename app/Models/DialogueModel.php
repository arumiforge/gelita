<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Baris narasi dan dialog (docs/naskah-cerita.md). Konteks global
 * (`intro`, `map_intro`, `ending`) ber-level_id NULL; konteks wilayah
 * (`region_intro`, `level_open`, `level_done`) terikat satu level.
 * Daftar konteks: Config\Gelita::$dialogueContexts.
 */
class DialogueModel extends Model
{
    protected $table         = 'dialogues';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'level_id', 'context_code', 'sequence', 'character_code', 'pose', 'effect',
        'title_id', 'title_en', 'text_id', 'text_en',
        'audio_id_asset_id', 'audio_en_asset_id', 'background_media_id', 'is_active',
    ];
    protected $validationRules = [
        'context_code'   => 'required|max_length[50]',
        'sequence'       => 'required|is_natural_no_zero',
        'character_code' => 'required|max_length[30]',
        // NULL = pose `idle` (narator: tanpa gambar) · NULL = tanpa efek
        'pose'           => 'permit_empty|max_length[30]|valid_dialogue_pose[character_code]',
        'effect'         => 'permit_empty|max_length[30]|valid_dialogue_effect',
        'text_id'        => 'required',
        'text_en'        => 'required',
    ];
    protected $validationMessages = [
        'pose'   => ['valid_dialogue_pose' => 'Pose tidak dikenal untuk tokoh ini.'],
        'effect' => ['valid_dialogue_effect' => 'Efek layar tidak dikenal.'],
    ];

    /**
     * Baris global (level_id NULL) satu konteks, mis. `intro`, `map_intro`,
     * `ending`. Kolom level_id NULL tidak dijaga UNIQUE oleh MySQL, jadi
     * nomor urut ganda mungkin ada; id menjadi urutan kedua agar hasilnya stabil.
     */
    public function global(string $context): array
    {
        return $this->where('level_id', null)
            ->where('context_code', $context)
            ->where('is_active', 1)
            ->orderBy('sequence', 'ASC')
            ->orderBy('id', 'ASC')
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
