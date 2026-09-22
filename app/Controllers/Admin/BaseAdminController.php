<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Controllers\Concerns\StaffScope;
use App\Models\ParticipantModel;
use App\Models\ResearchStudyModel;
use App\Models\SchoolModel;
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
            'pageTitle'     => $title,
            'filters'       => $data['filters'] ?? $this->readFilters(),
            'errors'        => session('errors') ?? [],
            'activeStudy'   => model(ResearchStudyModel::class)->activeStudy(),
            // Dievaluasi hanya oleh halaman yang merender admin-filter-bar.
            'filterOptions' => fn (): array => $this->filterOptions(),
        ]);
    }

    /**
     * Pilihan untuk components/admin-filter-bar: studi, wilayah, sekolah, provinsi.
     * Provinsi diambil dari data peserta dalam cakupan pemanggil, sehingga
     * guru hanya melihat provinsi yang memang ada di sekolahnya.
     *
     * @return array<string, mixed>
     */
    protected function filterOptions(): array
    {
        $scope = $this->schoolScope();

        $provinces = model(ParticipantModel::class)->scopedForStaff($scope)
            ->select('province_code, MAX(province_name_snapshot) AS province_name', false)
            ->where('participants.deleted_at', null)
            ->where('province_code IS NOT NULL')
            ->groupBy('province_code')
            ->orderBy('province_name', 'ASC')
            ->get()
            ->getResultArray();

        $ownSchool = $scope === null ? null : model(SchoolModel::class)->find($scope);

        return [
            'studies'    => model(ResearchStudyModel::class)->orderBy('id', 'DESC')->findAll(),
            'levels'     => service('contentRepository')->levels(),
            'schools'    => $scope === null ? model(SchoolModel::class)->activeList() : [],
            'own_school' => $ownSchool['name'] ?? null,
            'provinces'  => array_column($provinces, 'province_name', 'province_code'),
        ];
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
