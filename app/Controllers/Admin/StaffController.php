<?php

namespace App\Controllers\Admin;

use App\Libraries\PasswordPolicy;
use App\Models\AuditLogModel;
use App\Models\SchoolModel;
use App\Models\StaffUserModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Akun guru dan admin. Hanya role `admin`.
 *
 * Kata sandi baru dan sandi sementara hasil reset tidak pernah masuk flash,
 * `old()`, maupun `audit_logs` — hanya ditampilkan sekali pada respons reset.
 */
class StaffController extends BaseAdminController
{
    public function index(): string
    {
        return $this->panel('admin/staff/index', 'Akun staf', [
            'rows'      => $this->viewRows(),
            'schools'   => model(SchoolModel::class)->activeList(),
            'temporary' => null,
        ]);
    }

    public function store(): RedirectResponse
    {
        $staff = model(StaffUserModel::class);

        $rules = [
            'username'     => 'required|alpha_dash|min_length[3]|max_length[100]|is_unique[staff_users.username]',
            'display_name' => 'required|max_length[150]',
            'role'         => 'required|in_list[admin,guru]',
            'email'        => 'permit_empty|valid_email|max_length[150]',
            'password'     => 'required|min_length[12]|max_length[72]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to(site_url('admin/staf'))->with('errors', $this->validator->getErrors());
        }

        $role      = (string) $this->request->getPost('role');
        $schoolId  = $this->idOrNull($this->request->getPost('school_id'));

        if ($role === 'guru' && $schoolId === null) {
            return $this->back('admin/staf', 'Akun guru wajib terikat pada satu sekolah.');
        }

        // sandi awal dipilih admin → pemilik wajib menggantinya saat pertama masuk
        $staffId = $staff->insert([
            'username'             => (string) $this->request->getPost('username'),
            'email'                => $this->nullIfBlank($this->request->getPost('email')),
            'password_hash'        => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
            'must_change_password' => 1,
            'role'                 => $role,
            'display_name'         => (string) $this->request->getPost('display_name'),
            'school_id'            => $role === 'admin' ? null : $schoolId,
            'is_active'            => 1,
        ], true);

        if ($staffId === false) {
            return $this->back('admin/staf', 'Akun ditolak: ' . $this->modelErrors($staff));
        }

        $this->audit('staff_create', (int) $staffId, ['role' => $role]);

        return $this->done('admin/staf', 'Akun staf dibuat.');
    }

    public function update(int $staffId): RedirectResponse
    {
        $staff = model(StaffUserModel::class);

        if ($staff->find($staffId) === null) {
            return $this->back('admin/staf', 'Akun tidak ditemukan.');
        }

        $rules = [
            'display_name' => 'required|max_length[150]',
            'role'         => 'required|in_list[admin,guru]',
            'email'        => 'permit_empty|valid_email|max_length[150]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to(site_url('admin/staf'))->with('errors', $this->validator->getErrors());
        }

        $role     = (string) $this->request->getPost('role');
        $schoolId = $this->idOrNull($this->request->getPost('school_id'));
        $active   = $this->request->getPost('is_active') ? 1 : 0;

        if ($role === 'guru' && $schoolId === null) {
            return $this->back('admin/staf', 'Akun guru wajib terikat pada satu sekolah.');
        }

        // Sama seperti deactivate(): admin tidak boleh mengunci dirinya sendiri
        // keluar dari panel dengan menurunkan role atau menonaktifkan akunnya.
        if ($staffId === $this->staffId() && ($role !== 'admin' || $active !== 1)) {
            return $this->back('admin/staf', 'Anda tidak dapat menurunkan role atau menonaktifkan akun Anda sendiri.');
        }

        $saved = $staff->update($staffId, [
            'display_name' => (string) $this->request->getPost('display_name'),
            'email'        => $this->nullIfBlank($this->request->getPost('email')),
            'role'         => $role,
            'school_id'    => $role === 'admin' ? null : $schoolId,
            'is_active'    => $active,
        ]);

        if (! $saved) {
            return $this->back('admin/staf', 'Akun ditolak: ' . $this->modelErrors($staff));
        }

        $this->audit('staff_update', $staffId, ['role' => $role]);

        return $this->done('admin/staf', 'Akun staf diperbarui.');
    }

    /** Sandi sementara ditampilkan sekali pada respons ini, lalu tidak tersimpan. */
    public function resetPassword(int $staffId): string|RedirectResponse
    {
        $staff  = model(StaffUserModel::class);
        $target = $staff->find($staffId);

        if ($target === null) {
            return $this->back('admin/staf', 'Akun tidak ditemukan.');
        }

        if ((string) $this->request->getPost('confirm') !== 'RESET') {
            return $this->back('admin/staf', 'Ketik RESET pada kotak konfirmasi untuk mengatur ulang kata sandi.');
        }

        $temporary = $this->temporaryPassword();

        // sandi sementara diketahui admin → pemilik wajib menggantinya saat masuk
        $staff->setPassword($staffId, $temporary, true);
        $staff->update($staffId, ['failed_login_count' => 0, 'locked_until' => null]);

        $this->audit('staff_password_reset', $staffId);

        // Respons berisi sandi sementara: jangan disimpan cache browser/proxy
        $this->response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $this->response->setHeader('Pragma', 'no-cache');

        return $this->panel('admin/staff/index', 'Akun staf', [
            'rows'      => $this->viewRows(),
            'schools'   => model(SchoolModel::class)->activeList(),
            'temporary' => ['staff' => $target->toSafeArray(), 'password' => $temporary],
        ]);
    }

    public function deactivate(int $staffId): RedirectResponse
    {
        $staff = model(StaffUserModel::class);

        if ($staff->find($staffId) === null) {
            return $this->back('admin/staf', 'Akun tidak ditemukan.');
        }

        if ($staffId === $this->staffId()) {
            return $this->back('admin/staf', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $staff->update($staffId, ['is_active' => 0]);
        $this->audit('staff_deactivate', $staffId);

        return $this->done('admin/staf', 'Akun dinonaktifkan.');
    }

    // -------------------------------------------------------------- bantuan

    /**
     * Baris tabel staf tanpa password_hash; status kunci login ikut dihitung.
     *
     * @return list<array<string, mixed>>
     */
    private function viewRows(): array
    {
        $rows = [];

        foreach (model(StaffUserModel::class)->orderBy('display_name', 'ASC')->findAll() as $staff) {
            $rows[] = $staff->toSafeArray() + [
                'locked'        => $staff->isLocked(),
                'must_change'   => $staff->mustChangePassword(),
                'last_login_at' => $staff->last_login_at,
            ];
        }

        return $rows;
    }

    /** Sandi sementara staf: 16 karakter acak yang memenuhi kebijakan sandi. */
    private function temporaryPassword(): string
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

    /** @param array<string, mixed> $metadata */
    private function audit(string $action, int $staffId, array $metadata = []): void
    {
        model(AuditLogModel::class)->record($action, [
            'staff_user_id' => $this->staffId(),
            'target_type'   => 'staff_user',
            'target_id'     => (string) $staffId,
            'metadata'      => $metadata,
        ]);
    }

    private function idOrNull($value): ?int
    {
        $value = (int) $value;

        return $value > 0 ? $value : null;
    }

    private function nullIfBlank($value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }
}
