<?php

namespace App\Models;

use CodeIgniter\Model;

class ResearchPhaseModel extends Model
{
    protected $table         = 'research_phases';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['study_id', 'code', 'sequence', 'label_id', 'label_en', 'is_active'];

    protected $validationRules = [
        'study_id' => 'required|is_natural_no_zero',
        'code'     => 'required|in_list[umum,pretest,posttest]',
        'label_id' => 'required|max_length[150]',
        'label_en' => 'required|max_length[150]',
    ];

    /** @return list<array<string, mixed>> */
    public function forStudy(int $studyId): array
    {
        return $this->where('study_id', $studyId)
            ->where('is_active', 1)
            ->orderBy('sequence', 'ASC')
            ->findAll();
    }

    public function findByCode(int $studyId, string $code): ?array
    {
        return $this->where('study_id', $studyId)->where('code', $code)->first();
    }

    /** Fase atau pengecualian — sesi tidak boleh dibuat tanpa fase yang sah. */
    public function requireByCode(int $studyId, string $code): array
    {
        $phase = $this->findByCode($studyId, $code);

        if ($phase === null) {
            throw new \RuntimeException("Fase '{$code}' tidak terdaftar pada studi {$studyId}.");
        }

        return $phase;
    }
}
