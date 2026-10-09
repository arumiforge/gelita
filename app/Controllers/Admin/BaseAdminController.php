<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Controllers\Concerns\StaffScope;
use App\Libraries\AdminTable;
use App\Models\AuditLogModel;
use App\Models\ParticipantModel;
use App\Models\ResearchStudyModel;
use App\Models\SchoolModel;
use CodeIgniter\HTTP\DownloadResponse;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

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

        $districts = model(ParticipantModel::class)->scopedForStaff($scope)
            ->select('district_code, MAX(district_name_snapshot) AS district_name', false)
            ->where('participants.deleted_at', null)
            ->where('district_code IS NOT NULL')
            ->groupBy('district_code')
            ->orderBy('district_name', 'ASC')
            ->get()
            ->getResultArray();

        $ages = model(ParticipantModel::class)->scopedForStaff($scope)
            ->select('age')
            ->distinct()
            ->where('participants.deleted_at', null)
            ->where('age IS NOT NULL')
            ->orderBy('age', 'ASC')
            ->get()
            ->getResultArray();

        $ownSchool = $scope === null ? null : model(SchoolModel::class)->find($scope);

        return [
            'studies'    => model(ResearchStudyModel::class)->orderBy('id', 'DESC')->findAll(),
            'levels'     => service('contentRepository')->levels(),
            'nodes'      => $this->nodeOptions(),
            'schools'    => $scope === null ? model(SchoolModel::class)->inUseList() : [],
            'own_school' => $ownSchool['name'] ?? null,
            'provinces'  => array_column($provinces, 'province_name', 'province_code'),
            'districts'  => array_column($districts, 'district_name', 'district_code'),
            'ages'       => array_map('strval', array_column($ages, 'age')),
            'classes'    => $this->classOptions($scope),
        ];
    }

    /**
     * Kelas yang ada di data siswa dalam cakupan staf, selalu memuat kelas
     * sasaran (Config\Gelita::$registrationClass).
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

        // angka kelas berurutan, nilai lain di akhir
        usort($classes, static fn (string $a, string $b): int => [! ctype_digit($a), (int) $a, $a] <=> [! ctype_digit($b), (int) $b, $b]);

        return $classes;
    }

    /**
     * Pilihan tantangan untuk filter: id node → "tmg-2 · Judul".
     *
     * @return array<int, string>
     */
    protected function nodeOptions(): array
    {
        $content = service('contentRepository');
        $options = [];

        foreach ($content->levels() as $level) {
            foreach ($content->nodesForLevel($level->id) as $node) {
                $options[$node->id] = node_ref($level->code, $node->sequence) . ' · ' . $node->text('title', 'id');
            }
        }

        return $options;
    }

    /**
     * Kode tantangan per id node: id → `tmg-2`.
     *
     * @return array<int, string>
     */
    protected function nodeRefs(): array
    {
        $content = service('contentRepository');
        $refs    = [];

        foreach ($content->levels() as $level) {
            foreach ($content->nodesForLevel($level->id) as $node) {
                $refs[$node->id] = node_ref((string) $level->code, (int) $node->sequence);
            }
        }

        return $refs;
    }

    /**
     * RawDataService (sesi data mentah, Dok 3 §8.4) untuk pemanggil ini, atau
     * null selama layanan itu belum ada di branch ini. Layanan sungguhan
     * diambil lewat StaffScope::rawData() (cakupan sekolah & anonim diikat
     * di sana); uji dapat menyuntikkan tiruan lewat
     * `Services::injectMock('rawDataService', …)`.
     */
    protected function rawService(): ?object
    {
        if (\Config\Services::has('rawdataservice')) {
            return \Config\Services::get('rawdataservice');
        }

        if (! class_exists(\App\Services\RawDataService::class) || ! method_exists($this, 'rawData')) {
            return null;
        }

        return $this->rawData();
    }

    /**
     * CSV mentah satu dimensi dengan filter yang sama dengan tabelnya:
     * `rawCsvDownload()` milik sesi data mentah bila sudah ada (mencatat
     * audit), selain itu RawDataService::streamCsv() lewat csvResponse().
     *
     * @param array<string, mixed> $filters sudah dipaksa ke cakupan sekolah
     */
    protected function rawCsv(string $dimension, array $filters): ResponseInterface
    {
        if (method_exists($this, 'rawCsvDownload') && ! \Config\Services::has('rawdataservice')) {
            return $this->rawCsvDownload($dimension, $filters);
        }

        $service = $this->rawService();

        return $this->csvResponse('gelita-' . $dimension, static fn ($handle) => $service->streamCsv($dimension, $filters, $handle));
    }

    /**
     * Satu halaman RawDataService::page() untuk components/data-table:
     * urutan dari query string (`kolom` / `-kolom`), pagination 25/50/100,
     * dan penanda kolom yang dapat diurutkan (`sortable($dimension)`).
     *
     * @param array<string, mixed> $filters sudah dipaksa ke cakupan sekolah
     *
     * @return array<string, mixed>
     */
    protected function rawPage(object $service, string $dimension, array $filters): array
    {
        $sortable = method_exists($service, 'sortable') ? (array) $service->sortable($dimension) : null;
        $params   = AdminTable::params($this->request, $sortable ?? [], null);
        $sort     = trim((string) ($this->request->getGet('sort') ?? ''));
        $sort     = $sortable === null ? ($sort === '' ? null : $sort) : AdminTable::validSort($sort, $sortable);
        $table    = $service->page($dimension, $filters, $params['page'], $params['per_page'], $sort);

        $table['columns'] = array_map(static fn (array $column): array => $column + [
            'sortable' => $sortable === null || in_array($column['key'], $sortable, true),
        ], $table['columns']);

        return $table;
    }

    /** `?format=csv`: halaman tabel meminta CSV mentah dengan filter yang sama. */
    protected function wantsCsv(): bool
    {
        return $this->request->getGet('format') === 'csv';
    }

    /**
     * Respons CSV dari penulis aliran (AdminTable::streamCsv() atau
     * RawDataService::streamCsv()). Berkas ditulis ke php://temp lebih dulu
     * (meluap ke disk di atas 2 MB), lalu dikirim sekali.
     *
     * @param callable(resource): mixed $write
     */
    protected function csvResponse(string $name, callable $write): ResponseInterface
    {
        $handle = fopen('php://temp/maxmemory:2097152', 'w+b');
        $write($handle);
        rewind($handle);
        $body = (string) stream_get_contents($handle);
        fclose($handle);

        $file = $name . '-' . date('Ymd-His') . '.csv';

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $file . '"')
            ->setHeader('Cache-Control', 'no-store')
            ->setBody($body);
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

    /**
     * CSV satu tabel data mentah (`?format=csv` di `/admin/data/{dimensi}`,
     * Dok 3 §8.2): baris dan kolom sama dengan tabelnya, nilai berkode,
     * dalam cakupan pemanggil. Berkas sementara di writable/cache dihapus
     * sesudah dikirim. Dimensi yang tidak boleh dibaca → DomainException.
     *
     * @param array<string, mixed> $filters
     */
    protected function rawCsvDownload(string $dimension, array $filters): DownloadResponse
    {
        $path   = WRITEPATH . 'cache' . DIRECTORY_SEPARATOR . 'csv-' . bin2hex(random_bytes(8)) . '.csv';
        $handle = fopen($path, 'w+b');

        if ($handle === false) {
            throw new \RuntimeException('Berkas sementara CSV tidak dapat dibuat.');
        }

        try {
            $rows = $this->rawData()->streamCsv($dimension, $filters, $handle);
        } finally {
            fclose($handle);
        }

        register_shutdown_function(static function () use ($path): void {
            if (is_file($path)) {
                unlink($path);
            }
        });

        model(AuditLogModel::class)->record('export_csv', [
            'staff_user_id' => $this->staffId(),
            'target_type'   => 'raw_data',
            'target_id'     => $dimension,
            'metadata'      => ['filters' => $filters, 'row_count' => $rows],
        ]);

        return $this->response->download($path, null)
            ->setFileName('gelita-' . $dimension . '-' . date('Ymd-His') . '.csv')
            ->setContentType('text/csv; charset=UTF-8');
    }
}
