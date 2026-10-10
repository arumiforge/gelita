<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Rows of `asset_packages` (docs/rencana-paket-aset.md §5.4). Written only by
 * App\Services\AssetPackageBuilder; read by AssetPackageService and the
 * package commands.
 */
class AssetPackageModel extends Model
{
    public const STATUS_BUILDING  = 'membangun';
    public const STATUS_BUILT     = 'dibangun';
    public const STATUS_PUBLISHED = 'terbit';
    public const STATUS_WITHDRAWN = 'ditarik';

    protected $table         = 'asset_packages';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $allowedFields = [
        'package_code', 'app_version', 'code_version', 'content_version', 'manifest_sha256',
        'total_bytes', 'file_count', 'groups_json', 'warnings_json', 'status',
        'built_by', 'built_at', 'published_at', 'notes',
    ];

    public function findByCode(string $code): ?array
    {
        return $this->where('package_code', trim($code))->first();
    }

    /** Latest package whose build finished (any status except `membangun`). */
    public function latestBuilt(): ?array
    {
        return $this->where('status !=', self::STATUS_BUILDING)->orderBy('id', 'DESC')->first();
    }

    /**
     * Next free code for today: `pkt-YYYY-MM-DD-NN` (17 characters, fits the
     * 20-character asset_version field of the release form). $isTaken also
     * rules out codes of deleted packages: their manifest URL may still sit
     * in browser caches as immutable, so a code is never handed out twice.
     *
     * @param (callable(string): bool)|null $isTaken
     */
    public function nextCode(?int $time = null, ?callable $isTaken = null): string
    {
        $prefix = 'pkt-' . date('Y-m-d', $time ?? time()) . '-';
        $codes  = array_column(
            $this->db->table($this->table)->select('package_code')->like('package_code', $prefix, 'after')->get()->getResultArray(),
            'package_code',
        );

        $next = 1;

        foreach ($codes as $code) {
            $next = max($next, (int) substr((string) $code, strlen($prefix)) + 1);
        }

        while ($isTaken !== null && $isTaken($prefix . str_pad((string) $next, 2, '0', STR_PAD_LEFT))) {
            $next++;
        }

        return $prefix . str_pad((string) $next, 2, '0', STR_PAD_LEFT);
    }
}
