<?php

namespace App\Entities;

use App\Libraries\StaffAccess;
use CodeIgniter\Entity\Entity;

class StaffUser extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'                   => 'int',
        'is_active'            => 'boolean',
        'must_change_password' => 'boolean',
        'failed_login_count'   => 'int',
    ];

    /** Only for the guard "an admin cannot demote or deactivate themselves" (StaffController). */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /** Whether this account's role holds $capability (App\Libraries\StaffAccess). */
    public function can(string $capability): bool
    {
        return StaffAccess::allows((string) $this->role, $capability);
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

    /** Tanpa password_hash & kolom throttle */
    public function toSafeArray(): array
    {
        return [
            'id'           => $this->id,
            'username'     => $this->username,
            'email'        => $this->email,
            'role'         => $this->role,
            'display_name' => $this->display_name,
            'is_active'    => $this->is_active,
        ];
    }
}
