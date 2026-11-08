<?php

namespace App\Services;

use App\Libraries\StaffAccess;

/**
 * What one staff member may read from participant data, the third layer of
 * authorisation (route → controller → service). Used by `RawDataService`
 * and `ExportService` (revision 10, part 1, 27 §5.5).
 *
 * - `accounts()`: the admin's view of student accounts: identity columns,
 *   all schools. The admin never reaches research data (routes are guarded
 *   by capability), so this scope is only used for account lists.
 * - `research()`: `peneliti`: all schools, always coded (no name, username,
 *   school name or NPSN), without the `ketua`-only raw data.
 * - `head()`: `ketua` (`research.browse`): the same, plus the `event` and
 *   `kode_pilihan` sheets and the answer key (part 3, 29 K1).
 *
 * The difference between `peneliti` and `ketua` (one student by code, or all
 * students and downloads) is decided at the route level by capability, not
 * here.
 *
 * Not the controller trait `App\Controllers\Concerns\StaffScope` (it reads
 * the signed-in session); that trait builds this object via `dataScope()`.
 */
final class StaffScope
{
    private function __construct(
        public readonly bool $anonymous,
        public readonly bool $fullDetail,
    ) {
    }

    public static function accounts(): self
    {
        return new self(false, false);
    }

    public static function research(): self
    {
        return new self(true, false);
    }

    public static function head(): self
    {
        return new self(true, true);
    }

    /**
     * Any combination, for column listings that are not tied to a signed-in
     * role (ExportService::columns(), the data dictionary, tests). Never
     * built from form input.
     */
    public static function of(bool $anonymous, bool $fullDetail): self
    {
        return new self($anonymous, $fullDetail);
    }

    /**
     * From a `staff_users` row (never from form input).
     *
     * @param array{role?: string} $staff
     */
    public static function fromStaff(array $staff): self
    {
        return self::fromRole((string) ($staff['role'] ?? ''));
    }

    public static function fromRole(string $role): self
    {
        if (StaffAccess::allows($role, 'participants.accounts')) {
            return self::accounts();
        }

        if (StaffAccess::allows($role, 'research.browse')) {
            return self::head();
        }

        if (StaffAccess::allows($role, 'participants.research')) {
            return self::research();
        }

        throw new \DomainException("Peran staf '{$role}' tidak dikenal.");
    }
}
