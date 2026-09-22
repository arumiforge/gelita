<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class GameSession extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at'];
    protected $casts   = [
        'id'                     => 'int',
        'participant_id'         => 'int',
        'study_id'               => 'int',
        'phase_id'               => 'int',
        'release_id'             => 'int',
        'duration_ms'            => 'int',
        'last_level_id'          => '?int',
        'last_challenge_node_id' => '?int',
        'is_touch'               => '?boolean',
    ];

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isFinished(): bool
    {
        return in_array($this->status, ['completed', 'abandoned'], true);
    }

    /** Locale sesi yang sudah dipastikan didukung */
    public function resolvedLocale(): string
    {
        return in_array($this->locale, config('Gelita')->locales, true) ? (string) $this->locale : 'id';
    }
}
