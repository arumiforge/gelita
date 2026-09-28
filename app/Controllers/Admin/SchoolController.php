<?php

namespace App\Controllers\Admin;

use App\Libraries\RegionDirectory;
use App\Libraries\SchoolImporter;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Direktori sekolah — hanya role `admin`.
 *
 * - Cari sekolah resmi (NPSN, nama, kecamatan, atau desa) untuk menyiapkan
 *   NPSN yang ditulis di papan saat sesi kelas.
 * - Rapikan nama sekolah ketikan siswa (NPSN tidak ada di daftar, atau
 *   provinsi di luar direktori): gabungkan ke sekolah lain, atau sahkan
 *   sebagai sekolah baru ber-NPSN. Semua langkah tercatat di audit log.
 *
 * Aturan pencocokan dan penggabungan: App\Services\SchoolDirectory.
 */
class SchoolController extends BaseAdminController
{
    private const SEARCH_LIMIT = 50;

    public function index(): string
    {
        $directory = service('schoolDirectory');
        $districts = [];

        foreach (config('Gelita')->schoolDirectoryProvinces as $province) {
            $districts += RegionDirectory::districts($province);
        }

        $query    = trim((string) $this->request->getGet('q'));
        $district = (string) $this->request->getGet('kab');
        $district = isset($districts[$district]) ? $district : '';
        $results  = $query === '' && $district === ''
            ? null
            : $directory->search($query, ['district_code' => $district !== '' ? $district : null], self::SEARCH_LIMIT);

        $unverified = $directory->unverified();

        foreach ($unverified as &$row) {
            $row['region']      = $this->regionLabel($row);
            $row['suggestions'] = $directory->suggestions($row);
        }

        unset($row);

        return $this->panel('admin/schools/index', 'Sekolah', [
            'query'        => $query,
            'district'     => $district,
            'districts'    => $districts,
            'results'      => $results,
            'limit'        => self::SEARCH_LIMIT,
            'unverified'   => $unverified,
            'clearMatches' => count($directory->autoMerge(true)),
            'stats'        => $this->stats(),
            'dataMeta'     => $this->dataMeta(),
        ]);
    }

    /** Gabungkan entri belum terverifikasi ke sekolah pilihan saran atau ber-NPSN. */
    public function merge(int $schoolId): RedirectResponse
    {
        $directory = service('schoolDirectory');
        $targetId  = (int) $this->request->getPost('target_id');
        $npsn      = trim((string) $this->request->getPost('target_npsn'));

        if ($npsn !== '') {
            $target = $directory->findByNpsn($npsn);

            if ($target === null) {
                return $this->back('admin/sekolah', "NPSN {$npsn} tidak ada di daftar sekolah resmi.");
            }

            $targetId = (int) $target['id'];
        }

        if ($targetId <= 0) {
            return $this->back('admin/sekolah', 'Pilih sekolah tujuan atau isi NPSN-nya.');
        }

        try {
            $moved = $directory->merge($schoolId, $targetId);
        } catch (\InvalidArgumentException $e) {
            return $this->back('admin/sekolah', $e->getMessage());
        }

        return $this->done('admin/sekolah', sprintf(
            'Digabungkan: %d siswa dan %d akun guru dipindahkan. Ketikan yang sama berikutnya langsung tertaut.',
            $moved['participants'],
            $moved['staff'],
        ));
    }

    /** Gabungkan semua entri yang cocok jelas dengan sekolah resmi. */
    public function autoMerge(): RedirectResponse
    {
        $pairs = service('schoolDirectory')->autoMerge();

        return $this->done('admin/sekolah', $pairs === []
            ? 'Tidak ada nama sekolah yang cocok jelas dengan sekolah resmi.'
            : sprintf('%d nama sekolah digabungkan ke sekolah resmi.', count($pairs)));
    }

    /** Sahkan entri belum terverifikasi sebagai sekolah baru ber-NPSN. */
    public function verify(int $schoolId): RedirectResponse
    {
        try {
            $school = service('schoolDirectory')->verify(
                $schoolId,
                (string) $this->request->getPost('npsn'),
                (string) $this->request->getPost('name'),
            );
        } catch (\InvalidArgumentException $e) {
            return $this->back('admin/sekolah', $e->getMessage());
        }

        return $this->done('admin/sekolah', "Disahkan: {$school['name']} (NPSN {$school['code']}). Siswa berikutnya bisa mendaftar dengan NPSN ini.");
    }

    // -------------------------------------------------------------- bantuan

    /** @return array{official: int, unverified: int, in_use: int} */
    private function stats(): array
    {
        $db = db_connect();

        return [
            'official'   => $db->table('schools')->where('is_verified', 1)->where('is_active', 1)->where('code IS NOT NULL')->countAllResults(),
            'unverified' => $db->table('schools')->where('is_verified', 0)->where('is_active', 1)->countAllResults(),
            'in_use'     => (int) $db->table('participants')
                ->select('COUNT(DISTINCT school_id) AS total')
                ->where('school_id IS NOT NULL')
                ->where('deleted_at', null)
                ->get()
                ->getRow('total'),
        ];
    }

    /**
     * Sumber dan tanggal daftar resmi (docs/sekolah/, berkas .meta.json).
     *
     * @return array<string, mixed>|null
     */
    private function dataMeta(): ?array
    {
        $path = dirname(SchoolImporter::DEFAULT_FILE) . '/sekolah-jateng.meta.json';
        $meta = is_file($path) ? json_decode((string) file_get_contents($path), true) : null;

        return is_array($meta) ? $meta : null;
    }

    /** @param array<string, mixed> $school */
    private function regionLabel(array $school): string
    {
        if (($school['country_code'] ?? 'ID') !== 'ID') {
            return 'Luar negeri';
        }

        $parts = array_filter([
            RegionDirectory::districtName($school['district_code'] ?? null),
            RegionDirectory::province($school['province_code'] ?? null)['name'] ?? null,
        ]);

        return $parts === [] ? 'Wilayah tidak diisi' : implode(', ', $parts);
    }
}
