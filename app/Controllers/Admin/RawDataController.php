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
 * Jawaban, dan Riwayat percobaan. Guru melihat sekolahnya saja dan selalu
 * anonim (dipaksa RawDataService lewat StaffScope).
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
        'perangkat'  => 'Perangkat',
    ];

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

        $filters = $this->scopedFilters(array_intersect_key(
            $this->readFilters(),
            array_flip([...self::FILTERS, 'date_to']),
        ));
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
            'isAdmin'    => $this->isAdmin(),
            'readable'   => $this->readableAnswers(),
            'breadcrumb' => [self::TABS[$dimension] => null],
        ]);
    }
}
