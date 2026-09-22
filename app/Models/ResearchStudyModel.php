<?php

namespace App\Models;

use CodeIgniter\Model;

class ResearchStudyModel extends Model
{
    protected $table         = 'research_studies';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $allowedFields = [
        'code', 'name', 'description', 'year_label', 'status', 'retention_days',
        'default_locale', 'unlock_mode', 'item_selection_mode', 'require_consent',
        'active_phase_code', 'allow_phase_choice',
    ];
    protected $validationRules = [
        'code'                => 'required|max_length[50]|is_unique[research_studies.code,id,{id}]',
        'name'                => 'required|max_length[200]',
        'status'              => 'required|in_list[draft,active,closed]',
        'unlock_mode'         => 'required|in_list[sequential,free]',
        'item_selection_mode' => 'required|in_list[fixed,random]',
        'active_phase_code'   => 'required|in_list[umum,pretest,posttest]',
    ];

    /** Studi yang sedang berjalan; NULL bila belum ada. */
    public function activeStudy(): ?array
    {
        return $this->where('status', 'active')->orderBy('id', 'DESC')->first();
    }

    /** Studi aktif atau pengecualian — dipakai service yang tidak dapat berjalan tanpanya. */
    public function requireActiveStudy(): array
    {
        $study = $this->activeStudy();

        if ($study === null) {
            throw new \RuntimeException('Tidak ada research_studies dengan status active.');
        }

        return $study;
    }
}
