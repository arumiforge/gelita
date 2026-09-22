<?php

namespace App\Models;

use CodeIgniter\Model;

class GameReleaseModel extends Model
{
    protected $table         = 'game_releases';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'release_code', 'app_version', 'content_version', 'asset_version',
        'scoring_version', 'published_at', 'notes', 'is_active',
    ];
    protected $validationRules = [
        'release_code'    => 'required|max_length[80]|is_unique[game_releases.release_code,id,{id}]',
        'app_version'     => 'required|max_length[30]',
        'content_version' => 'required|max_length[30]',
        'scoring_version' => 'required|max_length[30]',
    ];

    /** Release aktif; tanpa ini sesi tidak boleh dibuat. */
    public function active(): array
    {
        $release = $this->where('is_active', 1)->orderBy('id', 'DESC')->first();

        if ($release === null) {
            throw new \RuntimeException('Tidak ada game_releases dengan is_active = 1.');
        }

        return $release;
    }

    /** Release aktif tanpa pengecualian — dipakai cache key konten. */
    public function activeOrNull(): ?array
    {
        return $this->where('is_active', 1)->orderBy('id', 'DESC')->first();
    }

    /** Hanya satu release yang boleh aktif; ditegakkan dalam transaction. */
    public function activate(int $id): bool
    {
        $this->db->transStart();
        $this->db->table($this->table)->where('is_active', 1)->update(['is_active' => 0]);
        $this->update($id, ['is_active' => 1, 'published_at' => date('Y-m-d H:i:s')]);
        $this->db->transComplete();

        return $this->db->transStatus();
    }
}
