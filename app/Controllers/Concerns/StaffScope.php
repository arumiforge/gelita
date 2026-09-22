<?php

namespace App\Controllers\Concerns;

use App\Services\AnalyticsService;

/**
 * Batas akses staf, lapis kedua dari tiga (route → controller → service).
 *
 * `schoolScope()` dipanggil di setiap method yang membaca data peserta.
 * Guru tanpa sekolah menghasilkan scope 0 — tidak cocok dengan `school_id`
 * mana pun, jadi kegagalan konfigurasi berakhir "tidak melihat apa-apa",
 * bukan "melihat semuanya".
 */
trait StaffScope
{
    /** NULL untuk admin (semua sekolah), school_id untuk guru. */
    protected function schoolScope(): ?int
    {
        return session('staff_role') === 'admin' ? null : (int) session('staff_school_id');
    }

    protected function isAdmin(): bool
    {
        return session('staff_role') === 'admin';
    }

    protected function staffId(): int
    {
        return (int) session('staff_id');
    }

    /**
     * Filter penelitian dari query string. Nilai kosong dibuang agar
     * apply_research_filters() tidak menambahkan WHERE yang tidak perlu.
     *
     * @return array<string, mixed>
     */
    protected function readFilters(): array
    {
        $allowed = [
            'study_id', 'participant_id', 'phase_code', 'level_id', 'node_id',
            'school_id', 'class_level', 'province_code', 'locale', 'date_from', 'date_to',
        ];

        $filters = [];

        foreach ($allowed as $key) {
            $value = $this->request->getGet($key);

            if (is_string($value) && trim($value) !== '') {
                $filters[$key] = trim($value);
            }
        }

        return $filters;
    }

    /**
     * Filter yang sudah dipaksa ke cakupan sekolah pemanggil.
     *
     * @param array<string, mixed>|null $filters
     *
     * @return array<string, mixed>
     */
    protected function scopedFilters(?array $filters = null): array
    {
        $filters = $filters ?? $this->readFilters();
        $scope   = $this->schoolScope();

        if ($scope === null) {
            return $filters;
        }

        $filters['school_id'] = $scope;

        return $filters;
    }

    /** AnalyticsService yang sudah terikat cakupan sekolah pemanggil. */
    protected function analytics(bool $anonymous = false): AnalyticsService
    {
        return service('analyticsService', $this->schoolScope(), $anonymous);
    }
}
