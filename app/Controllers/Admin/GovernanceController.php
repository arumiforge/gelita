<?php

namespace App\Controllers\Admin;

use App\Models\AuditLogModel;
use App\Models\DataDeletionRequestModel;
use App\Models\DataExportModel;
use App\Models\GameEventLogModel;
use App\Models\GameSessionModel;
use App\Models\StaffUserModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Penghapusan data penelitian, retensi, dan audit log.
 *
 * Penghapusan tidak pernah senyap dan selalu dua langkah: pratinjau jumlah
 * baris terdampak, lalu eksekusi dengan konfirmasi teks `HAPUS`. Tidak ada
 * endpoint lain di aplikasi ini yang menghapus data penelitian.
 */
class GovernanceController extends BaseAdminController
{
    private const CONFIRM_WORD = 'HAPUS';

    /** Urutan penghapusan keras: anak lebih dulu, induk terakhir. */
    private const HARD_ORDER = [
        'game_event_logs', 'audio_usage_events', 'item_responses', 'challenge_attempts',
        'session_progress', 'participant_feedback', 'game_sessions',
        'participant_consents', 'participants',
    ];

    public function index(): string
    {
        return $this->panel('admin/governance/index', 'Tata kelola data', [
            'requests'      => model(DataDeletionRequestModel::class)->orderBy('created_at', 'DESC')->findAll(30),
            'confirmWord'   => self::CONFIRM_WORD,
            'idleMinutes'   => config('Gelita')->sessionIdleMinutes,
            'exportRetention' => config('Gelita')->exportRetentionDays,
            'retention'     => session('retention_result'),
        ]);
    }

    /** Menghitung baris terdampak tanpa menghapus apa pun. */
    public function deletePreview(): RedirectResponse
    {
        $scope = $this->readScope();

        if ($scope === null) {
            return $this->back('admin/tata-kelola', 'Pilih cakupan: peserta, sesi, atau studi.');
        }

        $counts   = $this->countAffected($scope);
        $requests = model(DataDeletionRequestModel::class);

        $requestId = $requests->insert([
            'requested_by'   => $this->staffId(),
            'scope_json'     => ['scope' => $scope, 'counts' => $counts],
            'reason'         => $this->request->getPost('reason') ?: null,
            'mode'           => $this->request->getPost('mode') === 'hard' ? 'hard' : 'soft',
            'status'         => 'preview',
            'affected_count' => array_sum($counts),
        ], true);

        model(AuditLogModel::class)->record('delete_preview', [
            'staff_user_id' => $this->staffId(),
            'target_type'   => 'data_deletion_request',
            'target_id'     => (string) $requestId,
            'metadata'      => ['scope' => $scope, 'counts' => $counts],
        ]);

        return $this->done('admin/tata-kelola', 'Pratinjau tersimpan. Periksa jumlah baris sebelum mengeksekusi.');
    }

    public function deleteExecute(int $requestId): RedirectResponse
    {
        $requests = model(DataDeletionRequestModel::class);
        $request  = $requests->find($requestId);
        $back     = 'admin/tata-kelola';

        if ($request === null) {
            return $this->back($back, 'Permintaan penghapusan tidak ditemukan.');
        }

        if ($request['status'] !== 'preview') {
            return $this->back($back, 'Hanya permintaan berstatus pratinjau yang dapat dieksekusi.');
        }

        if ((string) $this->request->getPost('confirm') !== self::CONFIRM_WORD) {
            return $this->back($back, 'Ketik ' . self::CONFIRM_WORD . ' pada kotak konfirmasi untuk melanjutkan.');
        }

        // scope_json disimpan sebagai string JSON oleh EncodesJson
        $stored = json_decode((string) $request['scope_json'], true);
        $scope  = is_array($stored) && is_array($stored['scope'] ?? null) ? $stored['scope'] : [];

        if ($scope === []) {
            return $this->back($back, 'Cakupan permintaan ini kosong.');
        }

        $mode   = $request['mode'] === 'hard' ? 'hard' : 'soft';
        $reason = (string) ($request['reason'] ?? 'permintaan penghapusan data');

        $db = db_connect();
        $db->transBegin();

        try {
            $affected = $mode === 'hard'
                ? $this->hardDelete($scope)
                : $this->softDelete($scope, $reason);

            $requests->update($requestId, [
                'status'         => 'executed',
                'approved_by'    => $this->staffId(),
                'affected_count' => array_sum($affected),
                'executed_at'    => date('Y-m-d H:i:s'),
            ]);

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();

            log_message('error', 'Penghapusan data gagal: {msg}', ['msg' => $e->getMessage()]);

            return $this->back($back, 'Penghapusan dibatalkan karena galat; tidak ada baris yang berubah.');
        }

        model(AuditLogModel::class)->record('delete_execute', [
            'staff_user_id' => $this->staffId(),
            'target_type'   => 'data_deletion_request',
            'target_id'     => (string) $requestId,
            'metadata'      => ['mode' => $mode, 'scope' => $scope, 'affected_count' => array_sum($affected)],
        ]);

        return $this->done($back, 'Penghapusan selesai: ' . array_sum($affected) . ' baris terdampak.');
    }

    public function deleteCancel(int $requestId): RedirectResponse
    {
        $requests = model(DataDeletionRequestModel::class);
        $request  = $requests->find($requestId);

        if ($request === null || $request['status'] !== 'preview') {
            return $this->back('admin/tata-kelola', 'Hanya pratinjau yang dapat dibatalkan.');
        }

        $requests->update($requestId, ['status' => 'cancelled']);

        model(AuditLogModel::class)->record('delete_cancel', [
            'staff_user_id' => $this->staffId(),
            'target_type'   => 'data_deletion_request',
            'target_id'     => (string) $requestId,
        ]);

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

    /**
     * Menjalankan sekarang pekerjaan yang biasanya dijalankan cron.
     *
     * Dua pekerjaan yang sudah punya penopang di tahap ini: menandai sesi
     * menganggur sebagai `paused` dan membuang berkas ekspor kedaluwarsa.
     * Pembersihan per `retention_days` studi menyusul bersama RetentionService
     * pada tahap 7.
     */
    public function runRetention(): RedirectResponse
    {
        $stale   = model(GameSessionModel::class)->markStale(config('Gelita')->sessionIdleMinutes);
        $exports = model(DataExportModel::class);
        $removed = 0;

        foreach ($exports->expired() as $export) {
            $path = (string) ($export['file_path'] ?? '');

            if ($path === '') {
                continue;   // berkasnya sudah dibuang pada retensi sebelumnya
            }

            if (is_file($path)) {
                unlink($path);
            }

            // `status` tetap `done`: daftar statusnya ditetapkan skema
            // (queued|running|done|failed). Yang hilang adalah berkasnya.
            $exports->update((int) $export['id'], [
                'file_path'     => null,
                'error_message' => 'Berkas kedaluwarsa dan sudah dihapus.',
            ]);
            $removed++;
        }

        $result = ['stale_sessions' => $stale, 'expired_exports' => $removed, 'at' => date('Y-m-d H:i:s')];

        model(AuditLogModel::class)->record('retention_run', [
            'staff_user_id' => $this->staffId(),
            'metadata'      => $result,
        ]);

        return redirect()->to(site_url('admin/tata-kelola'))
            ->with('retention_result', $result)
            ->with('message', 'Retensi dijalankan.');
    }

    // -------------------------------------------------------------- bantuan

    /**
     * Cakupan penghapusan dari POST; tepat satu jenis cakupan.
     *
     * @return array{participant_id?: int, session_id?: int, study_id?: int}|null
     */
    private function readScope(): ?array
    {
        foreach (['participant_id', 'session_id', 'study_id'] as $key) {
            $value = (int) ($this->request->getPost($key) ?? 0);

            if ($value > 0) {
                return [$key => $value];
            }
        }

        return null;
    }

    /**
     * @param array<string, int> $scope
     *
     * @return array<string, int> nama tabel → jumlah baris terdampak
     */
    private function countAffected(array $scope): array
    {
        $db         = db_connect();
        $sessionIds = $this->sessionIds($scope);
        $counts     = [];

        foreach (self::HARD_ORDER as $table) {
            $counts[$table] = $this->scopedBuilder($db, $table, $scope, $sessionIds)?->countAllResults() ?? 0;
        }

        return $counts;
    }

    /**
     * Soft delete: `participants.deleted_at` dan `game_event_logs` ditandai;
     * baris lain dipertahankan agar agregat penelitian tetap dapat diaudit.
     *
     * @param array<string, int> $scope
     *
     * @return array<string, int>
     */
    private function softDelete(array $scope, string $reason): array
    {
        $affected = ['game_event_logs' => model(GameEventLogModel::class)
            ->softDeleteScope($scope, $this->staffId(), $reason)];

        if (isset($scope['participant_id'])) {
            db_connect()->table('participants')
                ->where('id', $scope['participant_id'])
                ->where('deleted_at', null)
                ->update(['deleted_at' => date('Y-m-d H:i:s')]);

            $affected['participants'] = db_connect()->affectedRows();
        }

        return $affected;
    }

    /**
     * Hard delete: baris dihapus dari anak ke induk agar foreign key RESTRICT
     * pada `participants` → `game_sessions` tidak menggagalkan transaksi.
     *
     * @param array<string, int> $scope
     *
     * @return array<string, int>
     */
    private function hardDelete(array $scope): array
    {
        $db         = db_connect();
        $sessionIds = $this->sessionIds($scope);
        $affected   = [];

        foreach (self::HARD_ORDER as $table) {
            $builder = $this->scopedBuilder($db, $table, $scope, $sessionIds);

            if ($builder === null) {
                $affected[$table] = 0;

                continue;
            }

            $builder->delete();
            $affected[$table] = $db->affectedRows();
        }

        return $affected;
    }

    /**
     * Builder terbatas cakupan untuk satu tabel, atau null bila tabel itu
     * tidak tersentuh cakupan ini (mis. `participants` pada cakupan studi).
     *
     * @param array<string, int> $scope
     * @param list<int>          $sessionIds
     */
    private function scopedBuilder(
        \CodeIgniter\Database\BaseConnection $db,
        string $table,
        array $scope,
        array $sessionIds,
    ): ?\CodeIgniter\Database\BaseBuilder {
        $bySession = ['game_event_logs', 'audio_usage_events', 'challenge_attempts', 'session_progress'];

        if (in_array($table, $bySession, true)) {
            return $sessionIds === [] ? null : $db->table($table)->whereIn('session_id', $sessionIds);
        }

        if ($table === 'item_responses') {
            if ($sessionIds === []) {
                return null;
            }

            return $db->table($table)->whereIn(
                'challenge_attempt_id',
                static fn ($sub) => $sub->select('id')->from('challenge_attempts')->whereIn('session_id', $sessionIds),
            );
        }

        if ($table === 'game_sessions') {
            return $sessionIds === [] ? null : $db->table($table)->whereIn('id', $sessionIds);
        }

        // tabel milik peserta: hanya tersentuh bila cakupannya satu peserta
        if (! isset($scope['participant_id'])) {
            return null;
        }

        return match ($table) {
            'participant_feedback', 'participant_consents' => $db->table($table)
                ->where('participant_id', $scope['participant_id']),
            'participants' => $db->table($table)->where('id', $scope['participant_id']),
            default        => null,
        };
    }

    /**
     * @param array<string, int> $scope
     *
     * @return list<int>
     */
    private function sessionIds(array $scope): array
    {
        $builder = db_connect()->table('game_sessions')->select('id');

        if (isset($scope['session_id'])) {
            $builder->where('id', $scope['session_id']);
        } elseif (isset($scope['participant_id'])) {
            $builder->where('participant_id', $scope['participant_id']);
        } elseif (isset($scope['study_id'])) {
            $builder->where('study_id', $scope['study_id']);
        } else {
            return [];
        }

        return array_map(
            static fn (array $row): int => (int) $row['id'],
            $builder->get()->getResultArray(),
        );
    }

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
