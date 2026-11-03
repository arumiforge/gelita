<?php

namespace App\Controllers\Concerns;

use App\Libraries\StaffAccess;
use App\Services\AnalyticsService;
use App\Services\RawDataService;
use App\Services\StaffScope as DataScope;

/**
 * Staff access, second layer of three (route → controller → service).
 *
 * Rights come from the capability map (App\Libraries\StaffAccess) through
 * `can()`, read from `staff_role`, which StaffAuthFilter syncs with the
 * database on every request. Since revision 10 part 1 there is no school
 * scope: the `guru` role is gone, `admin` sees accounts, and `peneliti` and
 * `ketua` see coded research data of every school (27 §5).
 */
trait StaffScope
{
    /** Whether the signed-in staff member holds $capability. */
    protected function can(string $capability): bool
    {
        return StaffAccess::allows(session('staff_role'), $capability);
    }

    /** Student names and usernames are shown only to `participants.accounts` (admin). */
    protected function seesIdentity(): bool
    {
        return $this->can('participants.accounts');
    }

    protected function staffId(): int
    {
        return (int) session('staff_id');
    }

    /**
     * Research filters from the query string. Empty values are dropped so
     * apply_research_filters() does not add needless WHERE clauses; keys
     * outside the standard filter map are ignored by that helper.
     *
     * @return array<string, mixed>
     */
    protected function readFilters(): array
    {
        $allowed = [
            'study_id', 'participant_id', 'participant_code', 'level_id', 'node_id', 'variant_group',
            'school_id', 'class_level', 'province_code', 'locale', 'date_from', 'date_to',
            // Panel admin Dok 3 §8.3: daftar siswa dan data mentah
            'q', 'district_code', 'gender', 'age', 'progress',
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

    /** AnalyticsService for the caller: coded unless the caller may see identities. */
    protected function analytics(): AnalyticsService
    {
        return service('analyticsService', null, ! $this->seesIdentity());
    }

    /** Raw data scope of the caller (third layer, App\Services\StaffScope). */
    protected function dataScope(): DataScope
    {
        return DataScope::fromRole((string) session('staff_role'));
    }

    /** RawDataService bound to the caller's scope (page(), streamCsv()). */
    protected function rawData(): RawDataService
    {
        return service('rawDataService', $this->dataScope());
    }
}
