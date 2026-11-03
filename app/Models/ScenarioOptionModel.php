<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Actions and reasons of one Skenario Digital item (31 §6.3, §15). Owned by
 * part 5 as bank data; the scenario engine of part 6 reads it through
 * ContentRepository::scenarioOptions(). Scores, codes, and feedback never go
 * into the player payload.
 */
class ScenarioOptionModel extends Model
{
    public const KINDS = ['action', 'reason'];

    protected $table         = 'scenario_options';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'challenge_item_id', 'kind', 'option_key', 'sequence', 'text_id', 'text_en', 'score', 'code', 'feedback_id', 'feedback_en',
    ];

    /** @return list<array<string, mixed>> actions first, then reasons, each in sequence order */
    public function forItem(int $itemId): array
    {
        return $this->where('challenge_item_id', $itemId)
            ->orderBy('kind', 'ASC')
            ->orderBy('sequence', 'ASC')
            ->findAll();
    }

    /**
     * Replaces every option of one item.
     *
     * @param list<array<string, mixed>> $rows
     */
    public function replaceForItem(int $itemId, array $rows): int
    {
        $this->where('challenge_item_id', $itemId)->delete();

        foreach ($rows as $row) {
            $this->insert(['challenge_item_id' => $itemId] + $row);
        }

        return count($rows);
    }
}
