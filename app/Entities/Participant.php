<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Participant extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at', 'intro_seen_at'];
    protected $casts   = [
        'id'                       => 'int',
        'age'                      => '?int',
        'school_id'                => '?int',
        'must_change_password'     => 'boolean',
        'failed_login_count'       => 'int',
        'pw_first_submit_criteria' => '?int',
        'pw_weak_submit_count'     => 'int',
    ];

    /** Nama untuk export anonim */
    public function anonLabel(): string
    {
        return (string) $this->participant_code;
    }

    /** Nama untuk tampilan guru */
    public function label(): string
    {
        return (string) ($this->display_name ?: $this->username);
    }

    public function verifyPassword(string $plain): bool
    {
        return password_verify($plain, (string) $this->password_hash);
    }

    public function isLocked(): bool
    {
        return $this->locked_until !== null && strtotime((string) $this->locked_until) > time();
    }

    /** Sisa menit penguncian, 0 bila tidak terkunci */
    public function lockMinutesLeft(): int
    {
        if (! $this->isLocked()) {
            return 0;
        }

        return (int) ceil((strtotime((string) $this->locked_until) - time()) / 60);
    }

    public function mustChangePassword(): bool
    {
        return (bool) $this->must_change_password;
    }

    /** Sudah pernah menyelesaikan cerita pembuka (`/intro/selesai`)? */
    public function hasSeenIntro(): bool
    {
        return $this->intro_seen_at !== null;
    }

    public function isActive(): bool
    {
        return $this->deleted_at === null;
    }

    /** Satu-satunya bentuk yang boleh dikirim ke view/API: tanpa password_hash & kolom throttle */
    public function toSafeArray(): array
    {
        return [
            'id'               => $this->id,
            'participant_code' => $this->participant_code,
            'username'         => $this->username,
            'display_name'     => $this->display_name,
            'age'              => $this->age,
            'gender'           => $this->gender,
            'class_level'      => $this->class_level,
            'school_name'      => $this->school_name_snapshot,
        ];
    }
}
