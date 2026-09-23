<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class StaffUser extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'                   => 'int',
        'school_id'            => '?int',
        'is_active'            => 'boolean',
        'must_change_password' => 'boolean',
        'failed_login_count'   => 'int',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /** Sandi sementara dari admin (reset / akun baru) belum diganti pemiliknya. */
    public function mustChangePassword(): bool
    {
        return (bool) $this->must_change_password;
    }

    public function verifyPassword(string $plain): bool
    {
        return password_verify($plain, (string) $this->password_hash);
    }

    public function isLocked(): bool
    {
        return $this->locked_until !== null && strtotime((string) $this->locked_until) > time();
    }

    /**
     * Batas akses data: admin melihat semua (NULL), guru hanya sekolahnya.
     * Dipakai AnalyticsService sebagai $schoolScope.
     */
    public function schoolScope(): ?int
    {
        return $this->isAdmin() ? null : $this->school_id;
    }

    /** Tanpa password_hash & kolom throttle */
    public function toSafeArray(): array
    {
        return [
            'id'           => $this->id,
            'username'     => $this->username,
            'email'        => $this->email,
            'role'         => $this->role,
            'display_name' => $this->display_name,
            'school_id'    => $this->school_id,
            'is_active'    => $this->is_active,
        ];
    }
}
