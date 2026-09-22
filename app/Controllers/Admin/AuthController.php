<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AuditLogModel;
use App\Models\StaffUserModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Login dan logout staf (admin & guru).
 *
 * Akun tidak ada, kata sandi salah, dan akun nonaktif dibalas dengan pesan
 * yang sama persis. Setiap percobaan — berhasil maupun gagal — masuk
 * `audit_logs` dengan hash IP, bukan IP mentah.
 */
class AuthController extends BaseController
{
    private const SAME_MESSAGE = 'Nama pengguna atau kata sandi salah.';

    public function loginForm(): string|RedirectResponse
    {
        if ((int) session('staff_id') > 0) {
            return redirect()->to(site_url('admin/dashboard'));
        }

        return view('admin/login', [
            'redirectTo' => (string) ($this->request->getGet('redirect_to') ?? ''),
            'errors'     => session('errors') ?? [],
        ]);
    }

    public function login(): RedirectResponse
    {
        $target = safe_internal_url($this->request->getPost('redirect_to'), 'admin/dashboard');

        if (! $this->validate(['username' => 'required', 'password' => 'required'])) {
            return redirect()->to(site_url('admin/login'))->with('errors', $this->validator->getErrors());
        }

        $username = trim((string) $this->request->getPost('username'));
        $password = (string) $this->request->getPost('password');

        $staff = model(StaffUserModel::class)->findByUsername($username);

        if ($staff !== null && $staff->isLocked()) {
            return $this->reject($staff->id, 'Akun terkunci sementara. Coba lagi dalam beberapa menit.', 'locked');
        }

        if ($staff === null || ! $staff->verifyPassword($password) || ! $staff->is_active) {
            if ($staff !== null && $staff->is_active) {
                model(StaffUserModel::class)->registerFailedLogin($staff->id);
            }

            return $this->reject($staff?->id, self::SAME_MESSAGE, 'invalid');
        }

        model(StaffUserModel::class)->clearFailedLogin($staff->id);

        $session = session();
        $session->regenerate(true);
        $session->set([
            'staff_id'        => $staff->id,
            'staff_role'      => $staff->role,
            'staff_school_id' => $staff->school_id,
            'staff_name'      => $staff->display_name,
        ]);

        model(AuditLogModel::class)->record('login', [
            'staff_user_id' => $staff->id,
            'target_type'   => 'staff_user',
            'target_id'     => (string) $staff->id,
            'metadata'      => ['role' => $staff->role],
        ]);

        return redirect()->to($target);
    }

    public function logout(): RedirectResponse
    {
        $staffId = (int) session('staff_id');

        if ($staffId > 0) {
            model(AuditLogModel::class)->record('logout', [
                'staff_user_id' => $staffId,
                'target_type'   => 'staff_user',
                'target_id'     => (string) $staffId,
            ]);
        }

        session()->destroy();

        return redirect()->to(site_url('admin/login'));
    }

    /** Satu jalur penolakan: pesan seragam, audit `login_failed`, tanpa kata sandi. */
    private function reject(?int $staffId, string $message, string $reason): RedirectResponse
    {
        model(AuditLogModel::class)->record('login_failed', [
            'staff_user_id' => $staffId,
            'target_type'   => 'staff_user',
            'target_id'     => $staffId === null ? null : (string) $staffId,
            'metadata'      => ['reason' => $reason],
        ]);

        return redirect()->to(site_url('admin/login'))->with('errors', ['password' => $message]);
    }
}
