<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Controllers\Concerns\StaffScope;
use App\Models\DataExportModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Dataset JSON panel admin: KPI hitungan, linimasa event, dan status ekspor.
 * Chart, sebaran siswa, dan analitik olahan dihapus (Dok 1 §14, Dok 3 §8.3).
 *
 * Each route is guarded by capability (`staffCan`, 27 §4): the summary for
 * every staff role, the timeline for `research.browse`, and the export
 * status for `research.export`, own exports only.
 */
class AdminApiController extends BaseController
{
    use StaffScope;

    public function summary(): ResponseInterface
    {
        return $this->ok($this->analytics()->summary($this->readFilters()));
    }

    public function timeline(int $sessionId): ResponseInterface
    {
        try {
            $rows = $this->analytics()->eventTimeline($sessionId);
        } catch (\RuntimeException) {
            return $this->fail('FORBIDDEN', 'Sesi ini bukan milik siswa di sekolah Anda.', 403);
        }

        return $this->ok(['rows' => $rows]);
    }

    /** Dipanggil berkala export.js selama berkas dibangun. */
    public function exportStatus(int $exportId): ResponseInterface
    {
        $export = model(DataExportModel::class)->find($exportId);

        if ($export === null) {
            return $this->fail('NOT_FOUND', 'Unduhan tidak ditemukan.', 404);
        }

        if ((int) $export['requested_by'] !== $this->staffId()) {
            return $this->fail('FORBIDDEN', 'Unduhan ini dibuat oleh akun lain.', 403);
        }

        return $this->ok([
            'id'          => (int) $export['id'],
            'status'      => (string) $export['status'],
            'format'      => (string) $export['format'],
            'anonymized'  => (bool) $export['anonymized'],
            'row_count'   => $export['row_count'] === null ? null : (int) $export['row_count'],
            'file_sha256' => $export['file_sha256'],
            'expires_at'  => $export['expires_at'],
            'error'       => $export['error_message'],
        ]);
    }

    /**
     * Monitoring data, polled every 30 seconds by admin/monitoring.js with
     * `X-Gelita-Background: 1` (not counted as staff activity, 27 §11.1).
     * Codes and tokens only for `field_sessions.manage` (28 §12, K2).
     */
    public function monitoring(): ResponseInterface
    {
        $study = model(\App\Models\ResearchStudyModel::class)->activeStudy();

        return $this->ok([
            'sessions' => $study === null ? [] : service('analyticsService')->monitoring((int) $study['id'], $this->can('field_sessions.manage')),
        ]);
    }
}
