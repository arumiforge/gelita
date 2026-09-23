<?php

namespace App\Controllers\Admin;

use App\Models\AuditLogModel;
use App\Models\DataExportModel;
use App\Models\ResearchStudyModel;
use App\Models\SchoolModel;
use App\Services\ExportService;
use CodeIgniter\HTTP\DownloadResponse;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Permintaan ekspor XLSX/PDF beserta unduhannya.
 *
 * Controller ini mencatat permintaan, menegakkan aturan anonimitas (lapis
 * kedua), dan mengaudit setiap unduhan. Berkasnya dibangun
 * `App\Services\ExportService`, yang membaca ulang hak pemohon dari
 * `staff_users` sebagai lapis ketiga.
 */
class ExportController extends BaseAdminController
{
    private const SHEETS = ExportService::SHEETS;

    private const ADMIN_ONLY_SHEETS = ExportService::ADMIN_ONLY_SHEETS;

    public function index(): string
    {
        $exports = model(DataExportModel::class);

        if (! $this->isAdmin()) {
            $exports->where('requested_by', $this->staffId());
        }

        return $this->panel('admin/export/index', 'Ekspor data', [
            'filters'       => $this->readFilters(),
            'sheets'        => self::SHEETS,
            'adminOnly'     => self::ADMIN_ONLY_SHEETS,
            'isAdmin'       => $this->isAdmin(),
            'studies'       => model(ResearchStudyModel::class)->findAll(),
            'schools'       => model(SchoolModel::class)->activeList(),
            'recent'        => $exports->orderBy('created_at', 'DESC')->findAll(20),
            'retention'     => config('Gelita')->exportRetentionDays,
            'rawEventLimit' => config('Gelita')->exportMaxRawEvents,
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

        if ($path === '' || ! is_file($path) || ! service('exportService')->isInsideExportDir($path)) {
            return $this->back('admin/ekspor', 'Berkas ekspor belum tersedia.');
        }

        model(AuditLogModel::class)->record('export_download', [
            'staff_user_id' => $this->staffId(),
            'target_type'   => 'data_export',
            'target_id'     => (string) $exportId,
            'metadata'      => ['format' => $export['format'], 'anonymized' => (int) $export['anonymized']],
        ]);

        $mime = $export['format'] === 'pdf'
            ? 'application/pdf'
            : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

        return $this->response->download($path, null)->setFileName(basename($path))->setContentType($mime);
    }

    /**
     * Mencatat permintaan ekspor lalu membangunnya lewat ExportService.
     * Guru selalu dipaksa mode anonim dan tidak pernah mendapat sheet
     * `Raw Events`, berapa pun isi formulir yang dikirim.
     *
     * PDF memakai templat `study` (default) atau `participant` dengan
     * `participant_id` — laporan individual dari halaman detail peserta.
     */
    private function queue(string $format): RedirectResponse
    {
        $isAdmin    = $this->isAdmin();
        $anonymized = $isAdmin ? ($this->request->getPost('anonymized') ? 1 : 0) : 1;
        $sheets     = $format === 'xlsx' ? $this->requestedSheets($isAdmin) : [];
        $filters    = $this->scopedFilters($this->postFilters());
        $scope      = ['filters' => $filters, 'sheets' => $sheets];
        $back       = 'admin/ekspor';

        if ($format === 'pdf') {
            $participantId = (int) ($this->request->getPost('participant_id') ?? 0);
            $scope['template'] = $participantId > 0 ? 'participant' : 'study';

            if ($participantId > 0) {
                $scope['participant_id'] = $participantId;
            }
        }

        $exports  = model(DataExportModel::class);
        $exportId = $exports->insert([
            'requested_by' => $this->staffId(),
            'study_id'     => isset($filters['study_id']) ? (int) $filters['study_id'] : null,
            'format'       => $format,
            'scope_json'   => $scope,
            'anonymized'   => $anonymized,
            'status'       => 'running',
        ], true);

        if ($exportId === false) {
            return $this->back($back, 'Permintaan ekspor ditolak: ' . $this->modelErrors($exports));
        }

        $exportId = (int) $exportId;

        @set_time_limit(300);

        $export = service('exportService')->build($exportId);

        model(AuditLogModel::class)->record('export', [
            'staff_user_id' => $this->staffId(),
            'target_type'   => 'data_export',
            'target_id'     => (string) $exportId,
            'metadata'      => [
                'format'      => $format,
                'anonymized'  => (int) $export['anonymized'],
                'sheets'      => $sheets,
                'template'    => $scope['template'] ?? null,
                'status'      => $export['status'],
                'row_count'   => $export['row_count'] === null ? null : (int) $export['row_count'],
                'file_sha256' => $export['file_sha256'],
            ],
        ]);

        if ($export['status'] !== 'done') {
            return $this->back($back, 'Ekspor #' . $exportId . ' gagal: ' . $export['error_message']);
        }

        $label = ($scope['template'] ?? null) === 'participant' ? 'Laporan peserta' : 'Ekspor';

        return $this->done($back, $label . ' #' . $exportId . ' siap diunduh.');
    }

    /**
     * Sheet yang diminta, disaring terhadap daftar resmi dan hak pemanggil.
     *
     * @return list<string>
     */
    private function requestedSheets(bool $isAdmin): array
    {
        return service('exportService')->allowedSheets(
            (array) ($this->request->getPost('sheets') ?? self::SHEETS),
            $isAdmin,
        );
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
