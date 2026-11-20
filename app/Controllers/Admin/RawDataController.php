<?php

namespace App\Controllers\Admin;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Data mentah (Dok 3 §8.3) — `/admin/data/{dimensi}`: satu tab per lembar
 * ekspor §9.2, tabel server-side dari RawDataService::page(), CSV per tabel
 * dengan filter yang sama (`?format=csv`, nilai mentah).
 *
 * Dimensi waktu tidak punya tab sendiri: kolomnya ada di Pengerjaan,
 * Jawaban, dan Riwayat percobaan. Only `ketua` opens this page
 * (`research.browse`); rows are always coded (RawDataService via StaffScope).
 */
class RawDataController extends BaseAdminController
{
    /** Kode dimensi → judul tab, urutan §8.3. */
    public const TABS = [
        'jawaban'    => 'Jawaban & ketepatan',
        'percobaan'  => 'Riwayat percobaan',
        'pengerjaan' => 'Pengerjaan',
        'progres'    => 'Progres',
        'selesai'    => 'Penyelesaian',
        'navigasi'   => 'Navigasi',
        'bantuan'    => 'Bantuan',
        // Part 3 (29 K1, §5.6): raw events for ketua; device data moved to the admin tech log
        'event'      => 'Event mentah',
        // Part 9 (35 §10.4): the derived sheets of the current run
        'turunan_peserta'    => 'Turunan: peserta',
        'turunan_sesi'       => 'Turunan: sesi',
        'turunan_tantangan'  => 'Turunan: tantangan',
        'turunan_pengerjaan' => 'Turunan: pengerjaan',
        'turunan_butir'      => 'Turunan: butir',
        'turunan_percobaan'  => 'Turunan: percobaan',
        'turunan_pengamatan' => 'Turunan: pengamatan',
        'turunan_tes'        => 'Turunan: tes',
    ];

    /**
     * Sidebar sub-menu and page sections (owner, 20 November 2026): the tabs
     * grouped by what a head researcher looks for. Group key => [label key,
     * lead key, tabs]. Every tab of TABS is in exactly one group.
     */
    public const GROUPS = [
        'jawaban' => ['Admin.rawGroupAnswers', 'Admin.rawGroupAnswersLead', ['jawaban', 'percobaan', 'pengerjaan', 'bantuan']],
        'jejak'   => ['Admin.rawGroupTrail', 'Admin.rawGroupTrailLead', ['progres', 'selesai', 'navigasi', 'event']],
        'turunan' => ['Admin.rawGroupDerived', 'Admin.rawGroupDerivedLead', [
            'turunan_peserta', 'turunan_sesi', 'turunan_tantangan', 'turunan_pengerjaan',
            'turunan_butir', 'turunan_percobaan', 'turunan_pengamatan', 'turunan_tes',
        ]],
    ];

    /** Group key of a tab. */
    public static function groupOf(string $dimension): string
    {
        foreach (self::GROUPS as $key => [, , $tabs]) {
            if (in_array($dimension, $tabs, true)) {
                return $key;
            }
        }

        return array_key_first(self::GROUPS);
    }

    /** Filter tabel data mentah (§8.3). */
    public const FILTERS = ['participant_code', 'school_id', 'level_id', 'node_id', 'variant_group', 'date_from'];

    public function index(): RedirectResponse
    {
        return redirect()->to(site_url('admin/data/' . array_key_first(self::TABS)));
    }

    public function show(string $dimension): string|ResponseInterface
    {
        if (! array_key_exists($dimension, self::TABS)) {
            throw PageNotFoundException::forPageNotFound("Dimensi {$dimension} tidak dikenal.");
        }

        $filters = array_intersect_key(
            $this->readFilters(),
            array_flip([...self::FILTERS, 'date_to']),
        );
        $service = $this->rawService();
        $table   = null;

        if ($service !== null) {
            try {
                if ($this->wantsCsv()) {
                    return $this->rawCsv($dimension, $filters);
                }

                $table = $this->rawPage($service, $dimension, $filters);
            } catch (\DomainException) {
                throw PageNotFoundException::forPageNotFound("Dimensi {$dimension} tidak tersedia.");
            }
        }

        return $this->panel('admin/data/index', lang('Admin.menuRawData'), [
            'dimension'  => $dimension,
            'tabs'       => self::TABS,
            'table'      => $table,
            'filters'    => $filters,
            'readable'   => $this->readableAnswers(),
            'group'      => self::groupOf($dimension),
            'breadcrumb' => [lang(self::GROUPS[self::groupOf($dimension)][0]) => 'admin/data/' . self::GROUPS[self::groupOf($dimension)][2][0], self::TABS[$dimension] => null],
            // Part 9 (35 §10.4): the run status above a derived tab, for the running field-mode study
            'derivedStudy' => \App\Libraries\DerivedSheets::has($dimension) ? model(\App\Models\ResearchStudyModel::class)->fieldModeStudy() : null,
        ]);
    }
}
