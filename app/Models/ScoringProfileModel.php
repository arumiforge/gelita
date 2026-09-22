<?php

namespace App\Models;

use CodeIgniter\Model;

class ScoringProfileModel extends Model
{
    protected $table         = 'scoring_profiles';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'code', 'version', 'first_pass_weight', 'final_weight', 'independence_weight',
        'hint_penalty_per_use', 'retry_penalty_per_extra_attempt',
        'three_star_min_score', 'three_star_min_first_pass', 'two_star_min_score', 'is_active',
    ];
    protected $validationRules = [
        'code'    => 'required|max_length[50]',
        'version' => 'required|max_length[20]',
    ];

    /** Profil skor aktif; tanpa ini skor tidak dapat dihitung. */
    public function active(): array
    {
        $profile = $this->where('is_active', 1)->orderBy('id', 'DESC')->first();

        if ($profile === null) {
            throw new \RuntimeException('Tidak ada scoring_profiles dengan is_active = 1.');
        }

        return $profile;
    }

    public function findVersion(string $code, string $version): ?array
    {
        return $this->where('code', $code)->where('version', $version)->first();
    }

    /** Hanya satu profil yang boleh aktif; ditegakkan dalam transaction. */
    public function activate(int $id): bool
    {
        $this->db->transStart();
        $this->db->table($this->table)->where('is_active', 1)->update(['is_active' => 0]);
        $this->update($id, ['is_active' => 1]);
        $this->db->transComplete();

        return $this->db->transStatus();
    }
}
