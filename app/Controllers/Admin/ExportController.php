<?php

namespace App\Controllers\Admin;

use App\Libraries\ExportLabels;
use App\Models\AuditLogModel;
use App\Models\DataExportModel;
use App\Models\ResearchStudyModel;
use App\Services\ExportService;
use CodeIgniter\HTTP\DownloadResponse;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Permintaan ekspor data mentah (XLSX / CSV-ZIP, Dok 3 §9), laporan PDF
 * satu siswa (§10), beserta unduhannya.
 *
 * Only `ketua` reaches this controller (`research.export`, 27 K14): every
 * export is coded, and each account sees and downloads only its own files.
 * Controller ini mencatat permintaan dan mengaudit setiap unduhan. Berkasnya dibangun
 * `App\Services\ExportService`, yang membaca ulang hak pemohon dari
 * `staff_users` sebagai lapis ketiga.
 *
 * Isian formulir (POST `admin/ekspor/{xlsx|csv|pdf}`):
 * - filter `ExportService::FILTER_KEYS`;
 * - `sheets[]` kode lembar §9.2 (kosong = semua yang boleh; Kamus Data selalu ikut);
 * - `labels` = `label` (nilai berlabel) | `code` (berkode); bawaan XLSX berlabel, CSV berkode;
 * - `anonymized` is always 1 (the identified mode was removed, 27 K14);
 * - `participant_id` = ekspor per siswa §9.3 (wajib untuk PDF).
 */
class ExportController extends BaseAdminController
{
    public function index(): string
    {
        $exports = model(DataExportModel::class)->where('requested_by', $this->staffId());
        $sheets  = service('exportService')->allowedSheets([], $this->dataScope()->fullDetail);

        return $this->panel('admin/export/index', 'Unduh data', [
            'filters'       => $this->readFilters(),
            'sheets'        => $sheets,
            'sheetTitles'   => array_combine($sheets, array_map(ExportLabels::sheetTitle(...), $sheets)),
            'sheetNotes'    => array_combine($sheets, array_map(ExportLabels::sheetMeaning(...), $sheets)),
            'studies'       => model(ResearchStudyModel::class)->findAll(),
            'recent'        => $exports->orderBy('created_at', 'DESC')->findAll(20),
            'retention'     => config('Gelita')->exportRetentionDays,
            'rawEventLimit' => config('Gelita')->exportMaxRawEvents,
        ]);
    }

    public function xlsx(): RedirectResponse
    {
        return $this->queue('xlsx');
    }

    /** CSV-ZIP: satu CSV per lembar. */
    public function csv(): RedirectResponse
    {
        return $this->queue('csv');
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
            return $this->back('admin/ekspor', 'Unduhan tidak ditemukan.');
        }

        if ((int) $export['requested_by'] !== $this->staffId()) {
            return $this->back('admin/ekspor', 'Unduhan ini dibuat oleh akun lain.');
        }

        if ($export['expires_at'] !== null && strtotime((string) $export['expires_at']) < time()) {
            return $this->back('admin/ekspor', 'Berkas ini sudah dihapus otomatis karena melewati batas waktu. Silakan buat unduhan baru.');
        }

        $path = (string) $export['file_path'];

        if ($path === '' || ! is_file($path) || ! service('exportService')->isInsideExportDir($path)) {
            return $this->back('admin/ekspor', 'Berkasnya belum siap. Tunggu sebentar, lalu muat ulang halaman.');
        }

        model(AuditLogModel::class)->record('export_download', [
            'staff_user_id' => $this->staffId(),
            'target_type'   => 'data_export',
            'target_id'     => (string) $exportId,
            'metadata'      => ['format' => $export['format'], 'anonymized' => (int) $export['anonymized']],
        ]);

        return $this->response->download($path, null)
            ->setFileName(basename($path))
            ->setContentType(ExportService::mimeType((string) $export['format']));
    }

    /**
     * Mencatat permintaan ekspor lalu membangunnya lewat ExportService.
     * Always coded, whatever the form sends; ExportService re-reads the
     * requester's role as the third layer.
     */
    private function queue(string $format): RedirectResponse
    {
        $anonymized    = 1;
        $participantId = (int) ($this->request->getPost('participant_id') ?? 0);
        $filters       = $this->postFilters();
        $back          = $participantId > 0 ? 'admin/peserta/' . $participantId : 'admin/ekspor';
        $scope         = ['filters' => $filters];

        if ($participantId > 0) {
            $scope['participant_id'] = $participantId;
        }

        if ($format === 'pdf') {
            if ($participantId <= 0) {
                return redirect()->to(site_url($back))->with('error', 'Laporan PDF hanya untuk satu siswa. Buat laporan dari halaman profil siswa, atau unduh data mentah sebagai Excel/CSV.');
            }

            $scope['template'] = 'participant';
        } else {
            $labels           = (string) $this->request->getPost('labels');
            $scope['sheets']  = $this->requestedSheets();
            $scope['labels']  = in_array($labels, ['label', 'code'], true) ? $labels : ($format === 'xlsx' ? 'label' : 'code');
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
            return $this->back($back, 'Unduhan gagal dibuat: ' . $this->modelErrors($exports));
        }

        $exportId = (int) $exportId;

        @set_time_limit(300);

        $export = service('exportService')->build($exportId);

        model(AuditLogModel::class)->record('export', [
            'staff_user_id' => $this->staffId(),
            'target_type'   => 'data_export',
            'target_id'     => (string) $exportId,
            'metadata'      => [
                'format'         => $format,
                'anonymized'     => (int) $export['anonymized'],
                'sheets'         => $scope['sheets'] ?? [],
                'labels'         => $scope['labels'] ?? null,
                'participant_id' => $participantId > 0 ? $participantId : null,
                'template'       => $scope['template'] ?? null,
                'status'         => $export['status'],
                'row_count'      => $export['row_count'] === null ? null : (int) $export['row_count'],
                'file_sha256'    => $export['file_sha256'],
            ],
        ]);

        if ($export['status'] !== 'done') {
            return $this->back($back, 'Unduhan #' . $exportId . ' gagal dibuat: ' . $export['error_message']);
        }

        $label = match (true) {
            $format === 'pdf'  => 'Laporan siswa',
            $participantId > 0 => 'Data siswa',
            default            => 'Berkas',
        };

        return $this->done($back, $label . ' #' . $exportId . ' siap diunduh di menu Unduh data.');
    }

    /**
     * Lembar yang diminta, disaring terhadap daftar resmi dan hak pemanggil.
     *
     * @return list<string>
     */
    private function requestedSheets(): array
    {
        return service('exportService')->allowedSheets((array) ($this->request->getPost('sheets') ?? []), $this->dataScope()->fullDetail);
    }


    /**
     * Filter ekspor datang lewat POST, bukan query string.
     *
     * @return array<string, mixed>
     */
    private function postFilters(): array
    {
        $filters = [];

        foreach (ExportService::FILTER_KEYS as $key) {
            $value = $this->request->getPost($key);

            if (is_string($value) && trim($value) !== '') {
                $filters[$key] = trim($value);
            }
        }

        return $filters;
    }
}
