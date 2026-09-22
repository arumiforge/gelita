<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class ChallengeAttempt extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at'];
    protected $casts   = [
        'id'                     => 'int',
        'session_id'             => 'int',
        'challenge_node_id'      => 'int',
        'attempt_no'             => 'int',
        'selected_item_ids_json' => 'json-array',
        'scorable_items'         => 'int',
        'first_pass_correct'     => 'int',
        'final_correct'          => 'int',
        'first_pass_accuracy'    => 'float',
        'final_accuracy'         => 'float',
        'check_count'            => 'int',
        'hint_count'             => 'int',
        'retry_count'            => 'int',
        'answer_change_count'    => 'int',
        'audio_use_count'        => 'int',
        'independence'           => 'float',
        'score'                  => 'float',
        'stars'                  => 'int',
        'scoring_profile_id'     => '?int',
        'duration_ms'            => '?int',
    ];

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /** @return list<int> */
    public function selectedItemIds(): array
    {
        return array_map('intval', $this->selected_item_ids_json ?: []);
    }

    public function hasItem(int $itemId): bool
    {
        return in_array($itemId, $this->selectedItemIds(), true);
    }
}
