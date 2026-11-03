<?php

namespace App\Filters;

use App\Libraries\StaffAccess;
use App\Models\AuditLogModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Area /admin dan /api/admin: staf harus login dan akunnya aktif.
 * The role in the session is synced with the database on every request, so
 * a role change or deactivation by the admin applies at once. An account
 * whose role is not one of StaffAccess::ROLES (e.g. a leftover `guru` while
 * migration 003400 is held back, 27 K16) is signed out like an inactive one.
 *
 * Idle limit (27 §11.1, K15): a staff session ends after
 * Config\Gelita::$staffIdleSeconds without activity. Only page requests
 * count as activity; background API calls (dashboard refresh, monitoring)
 * are checked against the limit but never extend it, so a tab left open
 * still expires.
 *
 * Sandi sementara dari admin (reset / akun baru) wajib diganti dulu: selain
 * halaman Ubah sandi, halaman dialihkan ke sana dan API membalas 403
 * PASSWORD_CHANGE_REQUIRED. Dibaca dari database setiap request, jadi reset
 * saat staf sedang login juga langsung berlaku.
 */
class StaffAuthFilter implements FilterInterface
{
    /** Satu-satunya halaman yang terbuka selama sandi wajib diganti */
    public const PASSWORD_PAGE = 'admin/akun/sandi';

    /** Session key: Unix time of the last page request of this staff session. */
    public const LAST_SEEN_KEY = 'staff_last_seen';

    public function before(RequestInterface $request, $arguments = null)
    {
        $isApi = is_api_path($request->getPath());

        if ((int) session('staff_id') <= 0) {
            return $this->reject($isApi);
        }

        $staff = service('staffContext');

        if ($staff === null || (int) $staff->is_active !== 1 || ! StaffAccess::isRole((string) $staff->role)) {
            session()->destroy();

            return $this->reject($isApi);
        }

        $idle     = (int) config('Gelita')->staffIdleSeconds;
        $lastSeen = (int) session(self::LAST_SEEN_KEY);

        if ($idle > 0 && $lastSeen > 0 && time() - $lastSeen > $idle) {
            model(AuditLogModel::class)->record('staff_idle_logout', [
                'staff_user_id' => (int) $staff->id,
                'target_type'   => 'staff_user',
                'target_id'     => (string) $staff->id,
                'metadata'      => ['idle_seconds' => time() - $lastSeen],
            ]);
            session()->remove(['staff_id', 'staff_role', 'staff_name', self::LAST_SEEN_KEY]);

            if ($isApi) {
                return $this->reject(true);
            }

            return redirect()->to(site_url('admin/login'))->with('error', lang('Admin.staffIdleLogout'));
        }

        $data = [
            'staff_role' => $staff->role,
            'staff_name' => $staff->display_name,
        ];

        if (! $isApi || $lastSeen === 0) {
            $data[self::LAST_SEEN_KEY] = time();
        }

        session()->set($data);

        if ((int) ($staff->must_change_password ?? 0) === 1
            && trim($request->getPath(), '/') !== self::PASSWORD_PAGE) {
            if ($isApi) {
                return api_error('PASSWORD_CHANGE_REQUIRED', 'Ganti kata sandi sementara Anda lebih dulu.', 403);
            }

            return redirect()->to(site_url(self::PASSWORD_PAGE));
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }

    private function reject(bool $isApi)
    {
        if ($isApi) {
            return api_error('INVALID_SESSION', 'Sesi Anda sudah berakhir. Silakan masuk kembali.', 401);
        }

        return redirect()->to(site_url('admin/login'));
    }
}
