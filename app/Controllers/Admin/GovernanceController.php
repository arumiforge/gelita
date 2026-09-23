<?php

namespace App\Controllers\Admin;

use App\Models\AuditLogModel;
use App\Models\DataDeletionRequestModel;
use App\Models\StaffUserModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Penghapusan data penelitian, retensi, dan audit log.
 *
 * Penghapusan tidak pernah senyap dan selalu dua langkah: pratinjau jumlah
 * baris terdampak, lalu eksekusi dengan konfirmasi teks `HAPUS`. Logikanya
 * ada di `App\Services\RetentionService`; tidak ada endpoint lain di
 * aplikasi ini yang menghapus data penelitian.
 */
class GovernanceController extends BaseAdminController
{
    private const CONFIRM_WORD = 'HAPUS';

    public function index(): string
    {
        $config = config('Gelita');

        return $this->panel('admin/governance/index', 'Tata kelola data', [
            'requests'        => model(DataDeletionRequestModel::class)->orderBy('created_at', 'DESC')->findAll(30),
            'confirmWord'     => self::CONFIRM_WORD,
            'idleMinutes'     => $config->sessionIdleMinutes,
            'attemptHours'    => $config->attemptAbandonHours,
            'sessionDays'     => $config->sessionAbandonDays,
            'exportRetention' => $config->exportRetentionDays,
            'phases'          => $config->phases,
            'retention'       => session('retention_result'),
        ]);
    }

    /** Menghitung baris terdampak tanpa menghapus apa pun. */
    public function deletePreview(): RedirectResponse
    {
        $retention = service('retentionService');
        $scope     = $retention->normalizeScope((array) $this->request->getPost());

        if ($scope === null) {
            return $this->back('admin/tata-kelola', 'Pilih cakupan: peserta, sesi, atau studi.');
        }

        $preview = $retention->preview(
            $scope,
            $this->request->getPost('mode') === 'hard' ? 'hard' : 'soft',
            (string) ($this->request->getPost('reason') ?? ''),
            $this->staffId(),
        );

        if ($preview['total'] === 0) {
            return $this->done('admin/tata-kelola', 'Pratinjau tersimpan, tetapi tidak ada baris yang cocok dengan cakupan ini.');
        }

        return $this->done('admin/tata-kelola', 'Pratinjau tersimpan. Periksa jumlah baris sebelum mengeksekusi.');
    }

    public function deleteExecute(int $requestId): RedirectResponse
    {
        $back = 'admin/tata-kelola';

        if ((string) $this->request->getPost('confirm') !== self::CONFIRM_WORD) {
            return $this->back($back, 'Ketik ' . self::CONFIRM_WORD . ' pada kotak konfirmasi untuk melanjutkan.');
        }

        try {
            $result = service('retentionService')->execute($requestId, $this->staffId());
        } catch (\DomainException $e) {
            return $this->back($back, $e->getMessage());
        }

        return $this->done($back, 'Penghapusan selesai: ' . $result['total'] . ' baris terdampak.');
    }

    public function deleteCancel(int $requestId): RedirectResponse
    {
        try {
            service('retentionService')->cancel($requestId, $this->staffId());
        } catch (\DomainException $e) {
            return $this->back('admin/tata-kelola', $e->getMessage());
        }

        return $this->done('admin/tata-kelola', 'Pratinjau dibatalkan.');
    }

    public function audit(): string
    {
        $logs   = model(AuditLogModel::class);
        $action = trim((string) ($this->request->getGet('action') ?? ''));
        $from   = trim((string) ($this->request->getGet('date_from') ?? ''));
        $to     = trim((string) ($this->request->getGet('date_to') ?? ''));

        if ($action !== '') {
            $logs->where('action', $action);
        }

        if ($from !== '') {
            $logs->where('occurred_at >=', $from . ' 00:00:00');
        }

        if ($to !== '') {
            $logs->where('occurred_at <=', $to . ' 23:59:59');
        }

        $rows = $logs->orderBy('occurred_at', 'DESC')->paginate(50);

        return $this->panel('admin/governance/audit', 'Audit log', [
            'rows'    => $rows,
            'pager'   => $logs->pager,
            'action'  => $action,
            'from'    => $from,
            'to'      => $to,
            'actions' => $this->knownActions(),
            'staff'   => array_column(
                model(StaffUserModel::class)->asArray()->select('id, username')->findAll(),
                'username',
                'id',
            ),
        ]);
    }

    /** Menjalankan sekarang pekerjaan yang biasanya dijalankan cron `gelita:retention:run`. */
    public function runRetention(): RedirectResponse
    {
        $result = service('retentionService')->run($this->staffId());

        return redirect()->to(site_url('admin/tata-kelola'))
            ->with('retention_result', $result)
            ->with('message', 'Retensi dijalankan.');
    }

    // -------------------------------------------------------------- bantuan

    /** @return list<string> aksi yang pernah tercatat, untuk filter */
    private function knownActions(): array
    {
        $rows = db_connect()->table('audit_logs')
            ->select('action')
            ->distinct()
            ->orderBy('action', 'ASC')
            ->get()
            ->getResultArray();

        return array_map(static fn (array $row): string => (string) $row['action'], $rows);
    }
}
