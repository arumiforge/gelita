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
            'schools'    => $scope === null ? model(SchoolModel::class)->inUseList() : [],
            'own_school' => $ownSchool['name'] ?? null,
            'provinces'  => array_column($provinces, 'province_name', 'province_code'),
            'classes'    => $this->classOptions($scope),
        ];
    }

    /**
     * Kelas yang benar-benar ada di data siswa dalam cakupan staf, selalu
     * memuat kelas sasaran (5). Data lama dari kelas lain tetap dapat dipilih.
     *
     * @return list<string>
     */
    private function classOptions(?int $scope): array
    {
        $rows = model(ParticipantModel::class)->scopedForStaff($scope)
            ->select('class_level')
            ->distinct()
            ->where('participants.deleted_at', null)
            ->where('class_level IS NOT NULL')
            ->where('class_level !=', '')
            ->get()
            ->getResultArray();

        $classes = array_values(array_unique(array_merge(
            [config('Gelita')->registrationClass],
            array_map('strval', array_column($rows, 'class_level')),
        )));

        // 1, 2, …, 9 lalu "lainnya" di akhir
        usort($classes, static fn (string $a, string $b): int => [! ctype_digit($a), (int) $a, $a] <=> [! ctype_digit($b), (int) $b, $b]);

        return $classes;
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
