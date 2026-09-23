<?php

namespace App\Models;

use App\Entities\StaffUser;
use App\Libraries\PasswordPolicy;
use CodeIgniter\Model;

class StaffUserModel extends Model
{
    protected $table         = 'staff_users';
    protected $primaryKey    = 'id';
    protected $returnType    = StaffUser::class;
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $allowedFields = [
        'username', 'email', 'password_hash', 'must_change_password', 'role', 'display_name', 'school_id',
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

    /**
     * password_hash tidak pernah diisi langsung dari input.
     * $mustChange = true untuk sandi yang diketahui admin (sandi sementara hasil
     * reset); ganti sandi oleh pemiliknya sendiri selalu menghapus kewajiban itu.
     */
    public function setPassword(int $id, string $plain, bool $mustChange = false): bool
    {
        return (bool) $this->update($id, [
            'password_hash'        => password_hash($plain, PASSWORD_DEFAULT),
            'must_change_password' => $mustChange ? 1 : 0,
        ]);
    }

    /**
     * Reset oleh admin (panel `/admin/staf` atau `gelita:staff:password`): sandi
     * sementara yang wajib diganti saat masuk, dan kunci login dibuka.
     * Sandi dikembalikan untuk ditampilkan sekali; pemanggil tidak menyimpannya.
     */
    public function resetToTemporary(int $id): string
    {
        $temporary = self::temporaryPassword();

        $this->setPassword($id, $temporary, true);
        $this->update($id, ['failed_login_count' => 0, 'locked_until' => null]);

        return $temporary;
    }

    /** Sandi sementara staf: 16 karakter acak yang memenuhi kebijakan sandi. */
    public static function temporaryPassword(): string
    {
        $pool   = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$%*?';
        $policy = new PasswordPolicy();

        for ($attempt = 0; $attempt < 10; $attempt++) {
            $candidate = '';

            for ($i = 0; $i < 16; $i++) {
                $candidate .= $pool[random_int(0, strlen($pool) - 1)];
            }

            if ($policy->check($candidate)['acceptable']) {
                return $candidate;
            }
        }

        throw new \RuntimeException('Gagal membuat sandi sementara yang memenuhi kebijakan.');
    }
}
