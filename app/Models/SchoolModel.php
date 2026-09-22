<?php

namespace App\Models;

use CodeIgniter\Model;

class SchoolModel extends Model
{
    protected $table         = 'schools';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $allowedFields = [
        'code', 'name', 'country_code', 'province_code', 'district_code', 'is_active',
    ];
    protected $validationRules = [
        'name' => 'required|max_length[200]',
    ];

    /** @return list<array<string, mixed>> */
    public function activeList(): array
    {
        return $this->where('is_active', 1)->orderBy('name', 'ASC')->findAll();
    }

    /**
     * Mencari sekolah berdasarkan nama (case-insensitive), membuat bila belum ada.
     * $region: country_code, province_code, district_code.
     */
    public function findOrCreateByName(string $name, array $region = []): int
    {
        $name = trim($name);

        if ($name === '') {
            throw new \InvalidArgumentException('Nama sekolah tidak boleh kosong.');
        }

        // Kolom memakai collation utf8mb4_unicode_ci, sehingga perbandingan
        // ini sudah mengabaikan besar-kecil huruf tanpa fungsi SQL tambahan.
        $existing = $this->where('name', $name)->first();

        if ($existing !== null) {
            return (int) $existing['id'];
        }

        $this->insert([
            'name'          => $name,
            'country_code'  => $region['country_code'] ?? 'ID',
            'province_code' => $region['province_code'] ?? null,
            'district_code' => $region['district_code'] ?? null,
            'is_active'     => 1,
        ], false);

        return (int) $this->getInsertID();
    }
}
