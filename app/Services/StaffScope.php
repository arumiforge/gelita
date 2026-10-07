<?php

namespace App\Services;

/**
 * Hak baca data penelitian satu staf, lapis ketiga otorisasi (route →
 * controller → service). Dipakai `RawDataService` dan `ExportService`.
 *
 * - Admin: semua sekolah; mode anonim boleh dipilih.
 * - Guru: hanya `school_id` sekolahnya (tanpa sekolah → 0, tidak cocok apa
 *   pun), selalu anonim, tanpa Event Mentah dan tanpa kunci jawaban.
 *
 * Bukan trait `App\Controllers\Concerns\StaffScope` (pembaca sesi login di
 * controller); trait itu membuat objek ini lewat `dataScope()`.
 */
final class StaffScope
{
    private function __construct(
        public readonly bool $isAdmin,
        public readonly ?int $schoolId,
        public readonly bool $anonymous,
    ) {
    }

    public static function admin(bool $anonymous = false): self
    {
        return new self(true, null, $anonymous);
    }

    /** Guru selalu anonim; sekolah kosong menjadi 0 agar tidak melihat apa-apa. */
    public static function teacher(?int $schoolId): self
    {
        return new self(false, max(0, (int) $schoolId), true);
    }

    /**
     * Dari baris `staff_users` (bukan dari isi formulir).
     *
     * @param array{role?: string, school_id?: int|string|null} $staff
     */
    public static function fromStaff(array $staff, bool $anonymous = true): self
    {
        return ($staff['role'] ?? '') === 'admin'
            ? self::admin($anonymous)
            : self::teacher(isset($staff['school_id']) ? (int) $staff['school_id'] : null);
    }

    /** Admin memilih anonim atau tidak; guru tetap anonim. */
    public function withAnonymous(bool $anonymous): self
    {
        return $this->isAdmin ? new self(true, null, $anonymous) : $this;
    }
}
