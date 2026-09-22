<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Controllers\Concerns\StaffScope;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Dasar seluruh controller panel.
 *
 * `schoolScope()` dan `readFilters()` datang dari trait StaffScope, yang
 * dipakai juga oleh Api\AdminApiController agar aturan cakupan sekolah
 * hanya ditulis satu kali.
 */
abstract class BaseAdminController extends BaseController
{
    use StaffScope;

    /**
     * Render halaman panel dengan data bersama (judul, filter, pesan flash).
     *
     * @param array<string, mixed> $data
     */
    protected function panel(string $view, string $title, array $data = []): string
    {
        return view($view, $data + [
            'pageTitle' => $title,
            'filters'   => $data['filters'] ?? $this->readFilters(),
            'errors'    => session('errors') ?? [],
        ]);
    }

    /** Kembali ke halaman panel dengan pesan galat. */
    protected function back(string $path, string $message): RedirectResponse
    {
        return redirect()->to(site_url($path))->with('error', $message);
    }

    /** Kembali ke halaman panel dengan pesan berhasil. */
    protected function done(string $path, string $message): RedirectResponse
    {
        return redirect()->to(site_url($path))->with('message', $message);
    }

    /**
     * Pesan penolakan dari validasi Model, atau null bila tidak ada.
     *
     * Model GELITA memvalidasi sendiri (mis. teks Inggris wajib), dan
     * `insert()`/`update()` hanya mengembalikan false saat menolak. Tanpa
     * memeriksa nilai baliknya, panel akan melaporkan "tersimpan" untuk
     * penyimpanan yang sebenarnya tidak terjadi.
     */
    protected function modelErrors(\CodeIgniter\Model $model): ?string
    {
        $errors = $model->errors();

        return $errors === [] ? null : implode(' ', $errors);
    }
}
