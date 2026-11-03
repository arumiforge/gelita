<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Tap parts of one *Periksa Unggahan* post: box x, y, w, h in percent of the
 * 16:9 scene (K8), drawn as plain text by engines/cari.js.
 */
class PostPartModel extends Model
{
    public const KINDS = ['account', 'credit', 'caption', 'board', 'comment', 'button', 'meta'];

    protected $table         = 'post_parts';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'post_scene_id', 'part_key', 'kind', 'label_id', 'label_en', 'text_id', 'text_en', 'x', 'y', 'w', 'h', 'sequence',
    ];

    /** @return list<array<string, mixed>> parts in sequence order */
    public function forScene(int $sceneId): array
    {
        return $this->where('post_scene_id', $sceneId)->orderBy('sequence', 'ASC')->orderBy('id', 'ASC')->findAll();
    }

    /**
     * Replaces every part of one post.
     *
     * @param list<array<string, mixed>> $rows
     */
    public function replaceForScene(int $sceneId, array $rows): int
    {
        $this->where('post_scene_id', $sceneId)->delete();

        foreach ($rows as $row) {
            $this->insert(['post_scene_id' => $sceneId] + $row);
        }

        return count($rows);
    }
}
