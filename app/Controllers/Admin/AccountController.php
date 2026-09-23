<?php

namespace App\Controllers\Admin;

use App\Models\AuditLogModel;
use App\Models\StaffUserModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Akun staf yang sedang login: ganti kata sandi sendiri (admin & guru).
 *
 * Aturan sandi baru sama dengan pembuatan akun di StaffController::store()
 * (12–72 karakter; 72 = batas bcrypt). Sandi saat ini wajib benar. Salah tebak
 * dihitung ke throttle login staf, dan bila akun sampai terkunci, sesi ini
 * diputus — sesi yang dibajak tidak dapat dipakai menebak sandi.
 *
 * Kata sandi tidak pernah masuk flash, `old()`, maupun `audit_logs`.
 */
class AccountController extends BaseAdminController
{
    private const FORM = 'admin/akun/sandi';

    public function passwordForm(): string
    {
        return $this->panel('admin/account/password', 'Ubah sandi', [
            'username' => (string) (service('staffContext')->username ?? ''),
        ]);
    }

    public function changePassword(): RedirectResponse
    {
        $staffModel = model(StaffUserModel::class);
        $staff      = $staffModel->find($this->staffId());

        if ($staff === null) {
            return redirect()->to(site_url('admin/login'));
        }

        $rules = [
            'current_password' => 'required',
            'password'         => 'required|min_length[12]|max_length[72]|differs[current_password]',
            'password_confirm' => 'required|matches[password]',
        ];

        $messages = [
            'current_password' => ['required' => 'Isi kata sandi saat ini.'],
            'password'         => [
                'required'   => 'Isi kata sandi baru.',
                'min_length' => 'Kata sandi baru minimal 12 karakter.',
                'max_length' => 'Kata sandi baru maksimal 72 karakter.',
                'differs'    => 'Kata sandi baru harus berbeda dari kata sandi saat ini.',
            ],
            'password_confirm' => [
                'required' => 'Ulangi kata sandi baru.',
                'matches'  => 'Ulangan kata sandi baru tidak sama.',
            ],
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->to(site_url(self::FORM))->with('errors', $this->validator->getErrors());
        }

        if (! $staff->verifyPassword((string) $this->request->getPost('current_password'))) {
            $staffModel->registerFailedLogin($staff->id);
            $this->audit('staff_password_change_failed', $staff->id);

            if ($staffModel->find($staff->id)?->isLocked()) {
                // Keluarkan staf tanpa menghancurkan sesi agar pesan flash sampai
                $session = session();
                $session->remove(['staff_id', 'staff_role', 'staff_school_id', 'staff_name']);
                $session->regenerate(true);

                return redirect()->to(site_url('admin/login'))->with('errors', [
                    'password' => 'Terlalu banyak kata sandi salah. Akun dikunci sementara; masuk lagi setelah beberapa menit.',
                ]);
            }

            return redirect()->to(site_url(self::FORM))->with('errors', [
                'current_password' => 'Kata sandi saat ini salah.',
            ]);
        }

        $staffModel->setPassword($staff->id, (string) $this->request->getPost('password'));
        $staffModel->update($staff->id, ['failed_login_count' => 0]);

        session()->regenerate(true);
        $this->audit('staff_password_change', $staff->id);

        return $this->done(self::FORM, 'Kata sandi diperbarui. Pakai sandi baru saat masuk berikutnya.');
    }

    private function audit(string $action, int $staffId): void
    {
        model(AuditLogModel::class)->record($action, [
            'staff_user_id' => $staffId,
            'target_type'   => 'staff_user',
            'target_id'     => (string) $staffId,
        ]);
    }
}
