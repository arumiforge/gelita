<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class ItemResponse extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'                   => 'int',
        'challenge_attempt_id' => 'int',
        'challenge_item_id'    => 'int',
        'display_order'        => 'int',
        'first_answer_json'    => '?json-array',
        'final_answer_json'    => '?json-array',
        'first_pass_correct'   => '?boolean',
        'is_correct'           => '?boolean',
        'change_count'         => 'int',
        'wrong_click_count'    => 'int',
        'hint_used'            => 'boolean',
        'duration_ms'          => '?int',
    ];

    /** Belum pernah dinilai — pemeriksaan berikutnya menetapkan first_pass_correct */
    public function isFirstPassOpen(): bool
    {
        return $this->first_answer_json === null;
    }

    public function isAnswered(): bool
    {
        return $this->status === 'answered';
    }
}
