<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Controllers\Concerns\StaffScope;
use App\Models\DataExportModel;
use App\Models\ParticipantModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Dataset JSON untuk chart panel admin.
 *
 * Cakupan sekolah diterapkan di sini (lapis controller) dan sekali lagi di
 * AnalyticsService (lapis service); guru tidak pernah menerima baris dari
 * sekolah lain meski memaksa `?school_id=` di query string.
 */
class AdminApiController extends BaseController
{
    use StaffScope;

    public function summary(): ResponseInterface
    {
        return $this->ok($this->analytics()->summary($this->scopedFilters()));
    }

    public function levels(): ResponseInterface
    {
        return $this->ok(['rows' => $this->analytics()->levelBreakdown($this->scopedFilters())]);
    }

    public function nodes(): ResponseInterface
    {
        return $this->ok(['rows' => $this->analytics()->nodeDifficulty($this->scopedFilters())]);
    }

    public function items(): ResponseInterface
    {
        return $this->ok(['rows' => $this->analytics()->itemAnalysis($this->scopedFilters())]);
    }

    public function indicators(): ResponseInterface
    {
        return $this->ok(['rows' => $this->analytics()->indicatorMastery($this->scopedFilters())]);
    }

    public function prePost(): ResponseInterface
    {
        return $this->ok($this->analytics()->prePostComparison($this->scopedFilters()));
    }

    public function participants(): ResponseInterface
    {
        $filters = $this->scopedFilters();

        return $this->ok([
            'cohort'           => model(ParticipantModel::class)->cohortSummary($filters),
            'digital_security' => $this->analytics()->digitalSecurityLiteracy($filters),
        ]);
    }

    public function timeline(int $sessionId): ResponseInterface
    {
        try {
            $rows = $this->analytics()->eventTimeline($sessionId);
        } catch (\RuntimeException) {
            return $this->fail('FORBIDDEN', 'Sesi ini di luar cakupan akses Anda.', 403);
        }

        return $this->ok(['rows' => $rows]);
    }

    /** Dipanggil berkala export.js selama berkas dibangun. */
    public function exportStatus(int $exportId): ResponseInterface
    {
        $export = model(DataExportModel::class)->find($exportId);

        if ($export === null) {
            return $this->fail('NOT_FOUND', 'Ekspor tidak ditemukan.', 404);
        }

        if (! $this->isAdmin() && (int) $export['requested_by'] !== $this->staffId()) {
            return $this->fail('FORBIDDEN', 'Ekspor ini bukan milik Anda.', 403);
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
}
