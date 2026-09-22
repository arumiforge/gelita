<?php

namespace App\Models;

use CodeIgniter\Model;

class MediaAssetModel extends Model
{
    protected $table         = 'media_assets';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $allowedFields = [
        'asset_key', 'asset_type', 'storage_path', 'mime_type', 'file_size', 'sha256',
        'width_px', 'height_px', 'locale', 'credit', 'version', 'is_active',
    ];
    protected $validationRules = [
        'asset_key'    => 'required|max_length[160]|is_unique[media_assets.asset_key,id,{id}]',
        'asset_type'   => 'required|in_list[image,audio,video,document]',
        'storage_path' => 'required|max_length[500]',
        'mime_type'    => 'required|max_length[100]',
    ];

    /**
     * Peta id => storage_path seluruh media aktif. Satu query, di-cache ContentRepository.
     *
     * @return array<int, string>
     */
    public function map(): array
    {
        $rows = $this->db->table($this->table)
            ->select('id, storage_path')
            ->where('is_active', 1)
            ->get()
            ->getResultArray();

        $out = [];

        foreach ($rows as $row) {
            $out[(int) $row['id']] = (string) $row['storage_path'];
        }

        return $out;
    }

    public function findByKey(string $key): ?array
    {
        return $this->where('asset_key', trim($key))->first();
    }

    /** @return array<string, int> asset_key => id, untuk impor workbook */
    public function keyMap(): array
    {
        $rows = $this->db->table($this->table)->select('id, asset_key')->get()->getResultArray();
        $out  = [];

        foreach ($rows as $row) {
            $out[(string) $row['asset_key']] = (int) $row['id'];
        }

        return $out;
    }
}
