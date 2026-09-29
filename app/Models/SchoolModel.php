<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;

/**
 * Direktori sekolah. Baris resmi (NPSN di `code`, `is_verified = 1`) berasal
 * dari `gelita:schools:import`; baris `is_verified = 0` adalah nama yang
 * diketik siswa tanpa NPSN yang cocok. Logika pencocokan ada di
 * App\Services\SchoolDirectory.
 *
 * Tabel berisi puluhan ribu sekolah resmi: jangan pernah memuat semuanya
 * untuk pilihan di layar — pakai inUseList() atau pencarian.
 */
class SchoolModel extends Model
{
    protected $table         = 'schools';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $allowedFields = [
        'code', 'name', 'level', 'stage', 'status',
        'country_code', 'province_code', 'district_code', 'subdistrict_name', 'village_name',
        'is_active', 'is_verified', 'match_key', 'merged_into_id',
    ];
    protected $validationRules = [
        'name' => ['label' => 'Nama sekolah', 'rules' => 'required|max_length[200]'],
    ];

    /**
     * Sekolah aktif yang dipakai peserta atau staf — pilihan filter panel,
     * ekspor, dan akun guru.
     *
     * @return list<array<string, mixed>>
     */
    public function inUseList(): array
    {
        return $this->where('is_active', 1)
            ->groupStart()
            ->whereIn('id', static fn (BaseBuilder $builder): BaseBuilder => $builder->select('school_id')
                ->from('participants')
                ->where('school_id IS NOT NULL')
                ->where('deleted_at', null))
            ->orWhereIn('id', static fn (BaseBuilder $builder): BaseBuilder => $builder->select('school_id')
                ->from('staff_users')
                ->where('school_id IS NOT NULL'))
            ->groupEnd()
            ->orderBy('name', 'ASC')
            ->findAll();
    }
}
