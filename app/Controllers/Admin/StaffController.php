<?php

namespace App\Controllers\Admin;

use App\Libraries\StaffAccess;
use App\Models\AuditLogModel;
use App\Models\StaffUserModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Akun staf: `admin`, `peneliti`, `ketua` (StaffAccess::ROLES, revision 10
 * part 1, 27 §11). Only `staff.manage` (admin). Accounts are deactivated,
 * never deleted (27 K10).
 *
 * Kata sandi baru dan sandi sementara hasil reset tidak pernah masuk flash,
 * `old()`, maupun `audit_logs` — hanya ditampilkan sekali pada respons reset.
 */
class StaffController extends BaseAdminController
{
    public function index(): string
    {
        return $this->panel('admin/staff/index', lang('Admin.menuStaff'), [
            'rows'      => $this->viewRows(),
            'temporary' => null,
        ]);
    }

    public function store(): RedirectResponse
    {
        $staff = model(StaffUserModel::class);

        $rules = [
            'username'     => ['label' => 'Nama pengguna', 'rules' => 'required|alpha_dash|min_length[3]|max_length[100]|is_unique[staff_users.username]'],
            'display_name' => ['label' => 'Nama yang ditampilkan', 'rules' => 'required|max_length[150]'],
            'role'         => ['label' => 'Peran', 'rules' => 'required|in_list[' . implode(',', StaffAccess::ROLES) . ']'],
            'email'        => ['label' => 'Email', 'rules' => 'permit_empty|valid_email|max_length[150]'],
            // Same strong-password rule as students, plus the staff minimum of 12 (27 §11.1, K15)
            'password'     => ['label' => 'Kata sandi awal', 'rules' => 'required|min_length[12]|max_length[72]|strong_password[username]', 'errors' => ['strong_password' => lang('Admin.staffPasswordWeak')]],
        ];

        if (! $this->validate($rules)) {
            return redirect()->to(site_url('admin/staf'))->with('errors', $this->validator->getErrors());
        }

        $role = (string) $this->request->getPost('role');

        // sandi awal dipilih admin → pemilik wajib menggantinya saat pertama masuk
        $staffId = $staff->insert([
            'username'             => (string) $this->request->getPost('username'),
            'email'                => $this->nullIfBlank($this->request->getPost('email')),
            'password_hash'        => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
            'must_change_password' => 1,
            'role'                 => $role,
            'display_name'         => (string) $this->request->getPost('display_name'),
            'is_active'            => 1,
        ], true);

        if ($staffId === false) {
            return $this->back('admin/staf', 'Akun belum dapat disimpan: ' . $this->modelErrors($staff));
        }

        $this->audit('staff_create', (int) $staffId, ['role' => $role]);

        return $this->done('admin/staf', 'Akun baru sudah dibuat. Sampaikan nama pengguna dan kata sandi awalnya kepada pemilik akun.');
    }

    public function update(int $staffId): RedirectResponse
    {
        $staff = model(StaffUserModel::class);

        if ($staff->find($staffId) === null) {
            return $this->back('admin/staf', 'Akun tidak ditemukan.');
        }

        $rules = [
            'display_name' => ['label' => 'Nama yang ditampilkan', 'rules' => 'required|max_length[150]'],
            'role'         => ['label' => 'Peran', 'rules' => 'required|in_list[' . implode(',', StaffAccess::ROLES) . ']'],
            'email'        => ['label' => 'Email', 'rules' => 'permit_empty|valid_email|max_length[150]'],
        ];

        if (! $this->validate($rules)) {
            return redirect()->to(site_url('admin/staf'))->with('errors', $this->validator->getErrors());
        }

        $role   = (string) $this->request->getPost('role');
        $active = $this->request->getPost('is_active') ? 1 : 0;

        // Sama seperti deactivate(): admin tidak boleh mengunci dirinya sendiri
        // keluar dari panel dengan menurunkan role atau menonaktifkan akunnya.
        if ($staffId === $this->staffId() && ($role !== 'admin' || $active !== 1)) {
            return $this->back('admin/staf', 'Anda tidak dapat mengubah peran atau menonaktifkan akun Anda sendiri.');
        }

        $saved = $staff->update($staffId, [
            'display_name' => (string) $this->request->getPost('display_name'),
            'email'        => $this->nullIfBlank($this->request->getPost('email')),
            'role'         => $role,
            'is_active'    => $active,
        ]);

        if (! $saved) {
            return $this->back('admin/staf', 'Akun belum dapat disimpan: ' . $this->modelErrors($staff));
        }

        $this->audit('staff_update', $staffId, ['role' => $role]);

        return $this->done('admin/staf', 'Perubahan akun sudah disimpan.');
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
            return $this->back('admin/staf', 'Ketik RESET (huruf besar) di kotak konfirmasi untuk membuat sandi sementara.');
        }

        // sandi sementara diketahui admin → pemilik wajib menggantinya saat masuk
        $temporary = $staff->resetToTemporary($staffId);

        $this->audit('staff_password_reset', $staffId);

        // Respons berisi sandi sementara: jangan disimpan cache browser/proxy
        $this->response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $this->response->setHeader('Pragma', 'no-cache');

        return $this->panel('admin/staff/index', lang('Admin.menuStaff'), [
            'rows'      => $this->viewRows(),
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

        return $this->done('admin/staf', 'Akun sudah dinonaktifkan dan tidak dapat masuk lagi.');
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

    private function nullIfBlank($value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }
}
