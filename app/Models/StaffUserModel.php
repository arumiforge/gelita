<?php

namespace App\Models;

use App\Entities\StaffUser;
use CodeIgniter\Model;

class StaffUserModel extends Model
{
    protected $table         = 'staff_users';
    protected $primaryKey    = 'id';
    protected $returnType    = StaffUser::class;
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $allowedFields = [
        'username', 'email', 'password_hash', 'role', 'display_name', 'school_id',
        'is_active', 'failed_login_count', 'locked_until', 'last_login_at',
    ];
    protected $validationRules = [
        'username'     => 'required|alpha_dash|min_length[3]|max_length[100]|is_unique[staff_users.username,id,{id}]',
        'role'         => 'required|in_list[admin,guru]',
        'display_name' => 'required|max_length[150]',
    ];

    public function findByUsername(string $username): ?StaffUser
    {
        return $this->where('username', trim($username))->first();
    }

    /** Gagal login: increment; melewati batas → kunci sementara dan reset pencacah. */
    public function registerFailedLogin(int $id): void
    {
        $staff = $this->find($id);

        if ($staff === null) {
            return;
        }

        $throttle = config('Gelita')->loginThrottle['staff'];
        $count    = $staff->failed_login_count + 1;

        if ($count >= (int) $throttle['max_fail']) {
            $this->update($id, [
                'failed_login_count' => 0,
                'locked_until'       => date('Y-m-d H:i:s', time() + ((int) $throttle['lock_minutes'] * 60)),
            ]);

            return;
        }

        $this->update($id, ['failed_login_count' => $count]);
    }

    public function clearFailedLogin(int $id): void
    {
        $this->update($id, [
            'failed_login_count' => 0,
            'locked_until'       => null,
            'last_login_at'      => date('Y-m-d H:i:s'),
        ]);
    }

    /** @return list<StaffUser> */
    public function activeStaff(): array
    {
        return $this->where('is_active', 1)->orderBy('display_name', 'ASC')->findAll();
    }

    /** password_hash tidak pernah diisi langsung dari input. */
    public function setPassword(int $id, string $plain): bool
    {
        return (bool) $this->update($id, ['password_hash' => password_hash($plain, PASSWORD_DEFAULT)]);
    }
}
