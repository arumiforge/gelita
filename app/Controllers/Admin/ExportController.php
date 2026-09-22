<?php

namespace App\Controllers\Admin;

use App\Models\AuditLogModel;
use App\Models\DataExportModel;
use App\Models\ResearchStudyModel;
use App\Models\SchoolModel;
use CodeIgniter\HTTP\DownloadResponse;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Permintaan ekspor XLSX/PDF beserta unduhannya.
 *
 * Controller ini mencatat permintaan, menegakkan aturan anonimitas, dan
 * mengaudit setiap unduhan. Pembangunan berkasnya sendiri dikerjakan
 * `App\Services\ExportService` yang dipasang pada tahap 7; selama kelas itu
 * belum ada, baris `data_exports` ditandai `failed` dengan alasan yang jelas
 * alih-alih menggantung di status `running`.
 */
class ExportController extends BaseAdminController
{
    /** Sheet yang dapat dipilih; `Raw Events` hanya untuk admin. */
    private const SHEETS = [
        'Participants', 'Sessions', 'Levels', 'Challenge Summary', 'Item Responses',
        'Raw Events', 'Audio Usage', 'Indicators', 'Demographic Summary', 'Feedback',
    ];

    private const ADMIN_ONLY_SHEETS = ['Raw Events'];

    public function index(): string
    {
        $exports = model(DataExportModel::class);

        if (! $this->isAdmin()) {
            $exports->where('requested_by', $this->staffId());
        }

        return $this->panel('admin/export/index', 'Ekspor data', [
            'filters'    => $this->readFilters(),
            'sheets'     => self::SHEETS,
            'adminOnly'  => self::ADMIN_ONLY_SHEETS,
            'isAdmin'    => $this->isAdmin(),
            'studies'    => model(ResearchStudyModel::class)->findAll(),
            'schools'    => model(SchoolModel::class)->activeList(),
            'recent'     => $exports->orderBy('created_at', 'DESC')->findAll(20),
            'retention'  => config('Gelita')->exportRetentionDays,
        ]);
    }

    public function xlsx(): RedirectResponse
    {
        return $this->queue('xlsx');
    }

    public function pdf(): RedirectResponse
    {
        return $this->queue('pdf');
    }

    public function download(int $exportId): DownloadResponse|RedirectResponse
    {
        $exports = model(DataExportModel::class);
        $export  = $exports->find($exportId);

        if ($export === null) {
            return $this->back('admin/ekspor', 'Ekspor tidak ditemukan.');
        }

        if (! $this->isAdmin() && (int) $export['requested_by'] !== $this->staffId()) {
            return $this->back('admin/ekspor', 'Ekspor ini bukan milik Anda.');
        }

        if ($export['expires_at'] !== null && strtotime((string) $export['expires_at']) < time()) {
            return $this->back('admin/ekspor', 'Berkas ekspor sudah kedaluwarsa dan dihapus.');
        }

        $path = (string) $export['file_path'];

        if ($path === '' || ! is_file($path)) {
            return $this->back('admin/ekspor', 'Berkas ekspor belum tersedia.');
        }

        model(AuditLogModel::class)->record('export_download', [
            'staff_user_id' => $this->staffId(),
            'target_type'   => 'data_export',
            'target_id'     => (string) $exportId,
            'metadata'      => ['format' => $export['format'], 'anonymized' => (int) $export['anonymized']],
        ]);

        return $this->response->download($path, null)->setFileName(basename($path));
    }

    /**
     * Mencatat permintaan ekspor lalu menyerahkannya ke ExportService.
     * Guru selalu dipaksa mode anonim dan tidak pernah mendapat sheet
     * `Raw Events`, berapa pun isi formulir yang dikirim.
     */
    private function queue(string $format): RedirectResponse
    {
        $isAdmin    = $this->isAdmin();
        $anonymized = $isAdmin ? ($this->request->getPost('anonymized') ? 1 : 0) : 1;
        $sheets     = $this->requestedSheets($isAdmin);
        $filters    = $this->scopedFilters($this->postFilters());

        $exports   = model(DataExportModel::class);
        $exportId  = $exports->insert([
            'requested_by' => $this->staffId(),
            'study_id'     => $filters['study_id'] ?? null,
            'format'       => $format,
            'scope_json'   => ['filters' => $filters, 'sheets' => $sheets],
            'anonymized'   => $anonymized,
            'status'       => 'running',
        ], true);

        if ($exportId === false) {
            return $this->back('admin/ekspor', 'Permintaan ekspor ditolak: ' . $this->modelErrors($exports));
        }

        $exportId = (int) $exportId;

        model(AuditLogModel::class)->record('export', [
            'staff_user_id' => $this->staffId(),
            'target_type'   => 'data_export',
            'target_id'     => (string) $exportId,
            'metadata'      => ['format' => $format, 'anonymized' => $anonymized, 'sheets' => $sheets],
        ]);

        if (! class_exists('App\Services\ExportService')) {
            $exports->markFailed($exportId, 'ExportService belum tersedia (dipasang pada tahap 7).');

            return $this->back(
                'admin/ekspor',
                'Permintaan tercatat, tetapi mesin ekspor belum aktif pada tahap ini.',
            );
        }

        // @codeCoverageIgnoreStart — jalur ini aktif setelah ExportService dipasang (tahap 7)
        service('exportService')->build($exportId);

        return $this->done('admin/ekspor', 'Ekspor sedang dibuat. Status diperbarui otomatis.');
        // @codeCoverageIgnoreEnd
    }

    /**
     * Sheet yang diminta, disaring terhadap daftar resmi dan hak pemanggil.
     *
     * @return list<string>
     */
    private function requestedSheets(bool $isAdmin): array
    {
        $requested = (array) ($this->request->getPost('sheets') ?? self::SHEETS);
        $allowed   = $isAdmin ? self::SHEETS : array_diff(self::SHEETS, self::ADMIN_ONLY_SHEETS);

        $sheets = array_values(array_intersect($allowed, array_map('strval', $requested)));

        return $sheets === [] ? array_values($allowed) : $sheets;
    }

    /**
     * Filter ekspor datang lewat POST, bukan query string.
     *
     * @return array<string, mixed>
     */
    private function postFilters(): array
    {
        $allowed = [
            'study_id', 'phase_code', 'level_id', 'school_id',
            'class_level', 'province_code', 'locale', 'date_from', 'date_to',
        ];

        $filters = [];

        foreach ($allowed as $key) {
            $value = $this->request->getPost($key);

            if (is_string($value) && trim($value) !== '') {
                $filters[$key] = trim($value);
            }
        }

        return $filters;
    }
}
