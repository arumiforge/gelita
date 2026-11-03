<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * One *Periksa Unggahan* post (Cari Objek round 2) per challenge and variant
 * (31 §6.3, §15). Its tap parts live in `post_parts`.
 */
class PostSceneModel extends Model
{
    protected $table         = 'post_scenes';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $allowedFields = [
        'challenge_node_id', 'variant_group', 'title_id', 'title_en', 'text_genre', 'media_asset_id', 'reference_source',
    ];

    /** @return array<string, mixed>|null */
    public function findFor(int $nodeId, string $group): ?array
    {
        return $this->where('challenge_node_id', $nodeId)->where('variant_group', $group)->first();
    }

    /**
     * Inserts or updates the post of one variant.
     *
     * @param array<string, mixed> $data
     */
    public function upsert(int $nodeId, string $group, array $data): int
    {
        $existing = $this->findFor($nodeId, $group);

        if ($existing !== null) {
            $this->update((int) $existing['id'], $data);

            return (int) $existing['id'];
        }

        $this->insert(['challenge_node_id' => $nodeId, 'variant_group' => $group] + $data);

        return (int) $this->getInsertID();
    }
}
