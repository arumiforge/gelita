<?php

namespace App\Services;

use App\Entities\GameSession;
use App\Models\AuditLogModel;
use App\Models\ChallengeAttemptModel;
use App\Models\DataDeletionRequestModel;
use App\Models\GameSessionModel;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\BaseConnection;

/**
 * Penghapusan data penelitian (FITUR 16) dan retensi terjadwal (FITUR 17).
 *
 * Satu-satunya jalur yang menghapus data penelitian. Penghapusan selalu dua
 * langkah — pratinjau jumlah baris per tabel, lalu eksekusi yang dikonfirmasi
 * manusia — dan retensi terjadwal tidak pernah menghapus data penelitian
 * sendiri: ia hanya membuat pratinjau dan mengingatkan admin.
 */
class RetentionService
{
    /** Urutan penghapusan keras: anak lebih dulu, induk terakhir. */
    public const HARD_ORDER = [
        'game_event_logs', 'audio_usage_events', 'item_responses', 'challenge_attempts',
        'session_progress', 'participant_feedback', 'game_sessions',
        'participant_consents', 'participants',
    ];

    /** Jenis cakupan utama; tepat satu yang dipakai. */
    public const SCOPE_KEYS = ['participant_id', 'session_id', 'study_id'];

    /** Penyempit cakupan studi. */
    public const NARROWING_KEYS = ['phase_code', 'date_from', 'date_to'];

    public const ORIGIN_RETENTION = 'retention';

    // ----------------------------------------------------------- cakupan

    /**
     * Cakupan penghapusan yang sah dari input bebas, atau null.
     * Tepat satu kunci utama (peserta → sesi → studi); penyempit fase dan
     * rentang tanggal hanya berlaku untuk cakupan studi.
     *
     * @param array<string, mixed> $input
     *
     * @return array<string, int|string>|null
     */
    public function normalizeScope(array $input): ?array
    {
        foreach (self::SCOPE_KEYS as $key) {
            $value = (int) ($input[$key] ?? 0);

            if ($value <= 0) {
                continue;
            }

            $scope = [$key => $value];

            if ($key !== 'study_id') {
                return $scope;
            }

            $phase = trim((string) ($input['phase_code'] ?? ''));

            if (in_array($phase, config('Gelita')->phases, true)) {
                $scope['phase_code'] = $phase;
            }

            foreach (['date_from', 'date_to'] as $dateKey) {
                $date = trim((string) ($input[$dateKey] ?? ''));

                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1) {
                    $scope[$dateKey] = $date;
                }
            }

            return $scope;
        }

        return null;
    }

    /**
     * Jumlah baris terdampak per tabel, tanpa mengubah apa pun.
     *
     * @param array<string, int|string> $scope
     *
     * @return array<string, int>
     */
    public function countAffected(array $scope): array
    {
        $db         = $this->db();
        $sessionIds = $this->sessionIds($scope);
        $counts     = [];

        foreach (self::HARD_ORDER as $table) {
            $counts[$table] = $this->scopedBuilder($db, $table, $scope, $sessionIds)?->countAllResults() ?? 0;
        }

        return $counts;
    }

    /**
     * Id sesi dalam cakupan.
     *
     * @param array<string, int|string> $scope
     *
     * @return list<int>
     */
    public function sessionIds(array $scope): array
    {
        $builder = $this->db()->table('game_sessions')->select('game_sessions.id');

        if (isset($scope['session_id'])) {
            $builder->where('game_sessions.id', (int) $scope['session_id']);
        } elseif (isset($scope['participant_id'])) {
            $builder->where('game_sessions.participant_id', (int) $scope['participant_id']);
        } elseif (isset($scope['study_id'])) {
            $builder->where('game_sessions.study_id', (int) $scope['study_id']);

            if (isset($scope['phase_code'])) {
                $builder->join('research_phases', 'research_phases.id = game_sessions.phase_id')
                    ->where('research_phases.code', (string) $scope['phase_code']);
            }

            if (isset($scope['date_from'])) {
                $builder->where('game_sessions.started_at >=', $scope['date_from'] . ' 00:00:00');
            }

            if (isset($scope['date_to'])) {
                $builder->where('game_sessions.started_at <=', $scope['date_to'] . ' 23:59:59');
            }
        } else {
            return [];
        }

        return array_map(
            static fn (array $row): int => (int) $row['id'],
            $builder->orderBy('game_sessions.id', 'ASC')->get()->getResultArray(),
        );
    }

    // ---------------------------------------------------------- pratinjau

    /**
     * Langkah 1: hitung dan simpan pratinjau berstatus `preview`.
     *
     * @param array<string, int|string> $scope hasil normalizeScope()
     *
     * @return array{id: int, counts: array<string, int>, total: int}
     */
    public function preview(array $scope, string $mode, ?string $reason, int $staffId): array
    {
        $counts   = $this->countAffected($scope);
        $requests = model(DataDeletionRequestModel::class);

        $requestId = $requests->insert([
            'requested_by'   => $staffId,
            'scope_json'     => ['scope' => $scope, 'counts' => $counts],
            'reason'         => $reason !== null && trim($reason) !== '' ? trim($reason) : null,
            'mode'           => $mode === 'hard' ? 'hard' : 'soft',
            'status'         => 'preview',
            'affected_count' => array_sum($counts),
        ], true);

        if ($requestId === false) {
            throw new \RuntimeException('Pratinjau gagal disimpan: ' . implode(' ', $requests->errors()));
        }

        model(AuditLogModel::class)->record('delete_preview', [
            'staff_user_id' => $staffId,
            'target_type'   => 'data_deletion_request',
            'target_id'     => (string) $requestId,
            'metadata'      => ['scope' => $scope, 'mode' => $mode, 'counts' => $counts],
        ]);

        return ['id' => (int) $requestId, 'counts' => $counts, 'total' => array_sum($counts)];
    }

    /**
     * Langkah 2: eksekusi dalam satu transaction. Melempar DomainException
     * dengan pesan untuk admin bila permintaan tidak dapat dieksekusi.
     *
     * @return array{mode: string, affected: array<string, int>, total: int}
     */
    public function execute(int $requestId, int $staffId): array
    {
        $requests = model(DataDeletionRequestModel::class);
        $request  = $requests->find($requestId);

        if ($request === null) {
            throw new \DomainException('Permintaan penghapusan tidak ditemukan.');
        }

        if ($request['status'] !== 'preview') {
            throw new \DomainException('Hanya permintaan berstatus pratinjau yang dapat dieksekusi.');
        }

        $stored = $this->decode($request);
        $scope  = $this->normalizeScope((array) ($stored['scope'] ?? []));

        if ($scope === null) {
            throw new \DomainException('Cakupan permintaan ini kosong.');
        }

        $previewTotal = (int) ($request['affected_count'] ?? 0);
        $nowCounts    = $this->countAffected($scope);
        $nowTotal     = array_sum($nowCounts);

        if ($this->driftExceeded($previewTotal, $nowTotal)) {
            throw new \DomainException(sprintf(
                'Jumlah baris terdampak berubah dari %d menjadi %d sejak pratinjau dibuat. Eksekusi dibatalkan; batalkan pratinjau ini lalu buat pratinjau baru.',
                $previewTotal,
                $nowTotal,
            ));
        }

        $mode   = $request['mode'] === 'hard' ? 'hard' : 'soft';
        $reason = (string) ($request['reason'] ?? 'permintaan penghapusan data');
        $db     = $this->db();

        $db->transBegin();

        try {
            $affected = $mode === 'hard'
                ? $this->hardDelete($scope)
                : $this->softDelete($scope, $staffId, $reason);

            $requests->update($requestId, [
                'status'         => 'executed',
                'approved_by'    => $staffId,
                'affected_count' => array_sum($affected),
                'executed_at'    => date('Y-m-d H:i:s'),
            ]);

            model(AuditLogModel::class)->record('delete_execute', [
                'staff_user_id' => $staffId,
                'target_type'   => 'data_deletion_request',
                'target_id'     => (string) $requestId,
                'metadata'      => [
                    'mode'           => $mode,
                    'scope'          => $scope,
                    'affected'       => $affected,
                    'affected_count' => array_sum($affected),
                    'preview_count'  => $previewTotal,
                ],
            ]);

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaction penghapusan gagal.');
            }

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();

            log_message('error', 'Penghapusan data #{id} gagal: {msg}', ['id' => $requestId, 'msg' => $e->getMessage()]);

            throw new \DomainException('Penghapusan dibatalkan karena galat; tidak ada baris yang berubah.', 0, $e);
        }

        return ['mode' => $mode, 'affected' => $affected, 'total' => array_sum($affected)];
    }

    public function cancel(int $requestId, int $staffId): void
    {
        $requests = model(DataDeletionRequestModel::class);
        $request  = $requests->find($requestId);

        if ($request === null || $request['status'] !== 'preview') {
            throw new \DomainException('Hanya pratinjau yang dapat dibatalkan.');
        }

        $requests->update($requestId, ['status' => 'cancelled']);

        model(AuditLogModel::class)->record('delete_cancel', [
            'staff_user_id' => $staffId,
            'target_type'   => 'data_deletion_request',
            'target_id'     => (string) $requestId,
        ]);
    }

    /**
     * Penyimpangan jumlah terdampak yang membatalkan eksekusi:
     * |sekarang − pratinjau| > max(minimum baris, fraksi × pratinjau).
     */
    public function driftExceeded(int $previewTotal, int $currentTotal): bool
    {
        $config    = config('Gelita');
        $tolerance = max($config->deletionDriftMinRows, (int) ceil($previewTotal * $config->deletionDriftFraction));

        return abs($currentTotal - $previewTotal) > $tolerance;
    }

    /**
     * Pratinjau buatan retensi yang masih menunggu keputusan admin.
     *
     * @return list<array<string, mixed>>
     */
    public function pendingRetentionRequests(): array
    {
        return array_values(array_filter(
            model(DataDeletionRequestModel::class)->pendingApproval(),
            fn (array $row): bool => ($this->decode($row)['origin'] ?? null) === self::ORIGIN_RETENTION,
        ));
    }

    // ------------------------------------------------------------- retensi

    /**
     * Retensi terjadwal (cron harian / tombol di panel).
     *
     * @return array<string, mixed> ringkasan, juga ditulis ke audit_logs
     */
    public function run(?int $staffId = null): array
    {
        $config = config('Gelita');

        $result = [
            'stale_sessions'      => model(GameSessionModel::class)->markStale($config->sessionIdleMinutes),
            'abandoned_attempts'  => $this->abandonStaleAttempts($config->attemptAbandonHours),
            'abandoned_sessions'  => $this->abandonStaleSessions($config->sessionAbandonDays),
            'expired_exports'     => count(service('exportService')->purgeExpired($staffId)),
            'retention_previews'  => [],
            'warnings'            => [],
            'at'                  => date('Y-m-d H:i:s'),
        ];

        ['previews' => $result['retention_previews'], 'warnings' => $result['warnings']]
            = $this->flagExpiredStudyData($staffId);

        model(AuditLogModel::class)->record('retention_run', [
            'staff_user_id' => $staffId,
            'metadata'      => $result,
        ]);

        return $result;
    }

    /**
     * Attempt `in_progress` yang tidak tersentuh lebih dari $hours jam →
     * `abandoned` (skor 0, bintang 0) + event `challenge_abandoned {via: retention}`.
     * Sentuhan terakhir = waktu mulai, jawaban terakhir, atau event terakhir attempt itu.
     */
    public function abandonStaleAttempts(int $hours): int
    {
        $cutoff = date('Y-m-d H:i:s', time() - ($hours * 3600));

        $rows = $this->db()->table('challenge_attempts ca')
            ->select('ca.id')
            ->select(
                'GREATEST(ca.started_at,'
                . ' COALESCE((SELECT MAX(ir.answered_at) FROM item_responses ir WHERE ir.challenge_attempt_id = ca.id), ca.started_at),'
                . ' COALESCE((SELECT MAX(e.server_received_at) FROM game_event_logs e WHERE e.challenge_attempt_id = ca.id), ca.started_at)'
                . ') AS last_touch',
                false,
            )
            ->where('ca.status', 'in_progress')
            ->where('ca.started_at <', $cutoff)
            ->having('last_touch <', $cutoff)
            ->get()
            ->getResultArray();

        $attempts  = model(ChallengeAttemptModel::class);
        $abandoned = 0;

        foreach ($rows as $row) {
            $attempt = $attempts->find((int) $row['id']);

            if ($attempt === null || ! $attempt->isInProgress()) {
                continue;
            }

            $db = $this->db();
            $db->transBegin();

            try {
                service('challengeService')->abandonAttempt($attempt, [
                    'via'        => self::ORIGIN_RETENTION,
                    'idle_hours' => $hours,
                    'last_touch' => (string) $row['last_touch'],
                ]);
                $db->transCommit();
                $abandoned++;
            } catch (\Throwable $e) {
                $db->transRollback();
                log_message('error', 'Retensi: attempt {id} gagal ditandai abandoned: {msg}', ['id' => $row['id'], 'msg' => $e->getMessage()]);
            }
        }

        return $abandoned;
    }

    /** Sesi `paused` yang tidak tersentuh lebih dari $days hari → `abandoned` + event `session_abandoned`. */
    public function abandonStaleSessions(int $days): int
    {
        $cutoff   = date('Y-m-d H:i:s', time() - ($days * 86400));
        $sessions = model(GameSessionModel::class);
        $stale    = $sessions->where('status', 'paused')->where('last_active_at <', $cutoff)->findAll();
        $count    = 0;

        foreach ($stale as $session) {
            /** @var GameSession $session */
            $db = $this->db();
            $db->transBegin();

            try {
                $db->table('game_sessions')
                    ->where('id', $session->id)
                    ->where('status', 'paused')
                    ->update(['status' => 'abandoned']);

                if ($db->affectedRows() === 1) {
                    service('eventService')->record($session, 'session_abandoned', [
                        'via'            => self::ORIGIN_RETENTION,
                        'idle_days'      => $days,
                        'last_active_at' => (string) $session->last_active_at,
                    ]);
                    $count++;
                }

                $db->transCommit();
            } catch (\Throwable $e) {
                $db->transRollback();
                log_message('error', 'Retensi: sesi {id} gagal ditandai abandoned: {msg}', ['id' => $session->id, 'msg' => $e->getMessage()]);
            }
        }

        return $count;
    }

    /**
     * Data studi yang melewati `research_studies.retention_days` TIDAK dihapus:
     * dibuatkan (atau diperbarui) satu pratinjau `hard` per studi berasal
     * `retention`, yang tampil sebagai peringatan di dasbor admin.
     *
     * @return array{previews: list<array{study_id: int, request_id: int, date_to: string, total: int}>, warnings: list<string>}
     */
    public function flagExpiredStudyData(?int $staffId = null): array
    {
        $studies  = $this->db()->table('research_studies')->select('id, code, retention_days')->where('retention_days >', 0)->get()->getResultArray();
        $previews = [];
        $warnings = [];

        foreach ($studies as $study) {
            $days   = (int) $study['retention_days'];
            $dateTo = date('Y-m-d', strtotime('today -' . ($days + 1) . ' days'));
            $scope  = ['study_id' => (int) $study['id'], 'date_to' => $dateTo];

            if ($this->sessionIds($scope) === []) {
                continue;
            }

            $requester = $staffId ?? $this->systemRequester();

            if ($requester === null) {
                $warnings[] = "Studi {$study['code']} punya data melewati masa simpan, tetapi tidak ada akun admin aktif untuk mencatat pratinjau.";

                continue;
            }

            $counts   = $this->countAffected($scope);
            $reason   = "Melewati masa simpan {$days} hari studi {$study['code']} (sesi dimulai s.d. {$dateTo}). Dibuat otomatis oleh retensi; periksa sebelum mengeksekusi.";
            $existing = $this->pendingRetentionFor((int) $study['id']);
            $payload  = [
                'scope_json'     => ['scope' => $scope, 'counts' => $counts, 'origin' => self::ORIGIN_RETENTION],
                'reason'         => $reason,
                'mode'           => 'hard',
                'affected_count' => array_sum($counts),
            ];

            $requests = model(DataDeletionRequestModel::class);

            if ($existing !== null) {
                $requests->update((int) $existing['id'], $payload);
                $requestId = (int) $existing['id'];
            } else {
                $requestId = (int) $requests->insert($payload + ['requested_by' => $requester, 'status' => 'preview'], true);

                model(AuditLogModel::class)->record('delete_preview', [
                    'staff_user_id' => $staffId,
                    'target_type'   => 'data_deletion_request',
                    'target_id'     => (string) $requestId,
                    'metadata'      => ['scope' => $scope, 'mode' => 'hard', 'counts' => $counts, 'origin' => self::ORIGIN_RETENTION],
                ]);
            }

            $previews[] = ['study_id' => (int) $study['id'], 'request_id' => $requestId, 'date_to' => $dateTo, 'total' => array_sum($counts)];
        }

        return ['previews' => $previews, 'warnings' => $warnings];
    }

    // ------------------------------------------------------------ internal

    /**
     * Soft delete: `game_event_logs` ditandai (deleted_at/by/reason) dan, untuk
     * cakupan peserta, `participants.deleted_at` diisi. Baris lain dipertahankan
     * agar agregat penelitian tetap dapat diaudit.
     *
     * @param array<string, int|string> $scope
     *
     * @return array<string, int>
     */
    private function softDelete(array $scope, int $staffId, string $reason): array
    {
        $db         = $this->db();
        $sessionIds = $this->sessionIds($scope);
        $now        = date('Y-m-d H:i:s');
        $affected   = ['game_event_logs' => 0];

        if ($sessionIds !== []) {
            $db->table('game_event_logs')
                ->whereIn('session_id', $sessionIds)
                ->where('deleted_at', null)
                ->update([
                    'deleted_at'    => $now,
                    'deleted_by'    => $staffId,
                    'delete_reason' => mb_substr($reason, 0, 255),
                ]);

            $affected['game_event_logs'] = $db->affectedRows();
        }

        if (isset($scope['participant_id'])) {
            $db->table('participants')
                ->where('id', (int) $scope['participant_id'])
                ->where('deleted_at', null)
                ->update(['deleted_at' => $now]);

            $affected['participants'] = $db->affectedRows();
        }

        return $affected;
    }

    /**
     * Hard delete dari anak ke induk agar FK RESTRICT pada
     * `participants` → `game_sessions` tidak menggagalkan transaction.
     *
     * @param array<string, int|string> $scope
     *
     * @return array<string, int>
     */
    private function hardDelete(array $scope): array
    {
        $db         = $this->db();
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
     * @param array<string, int|string> $scope
     * @param list<int>                 $sessionIds
     */
    private function scopedBuilder(BaseConnection $db, string $table, array $scope, array $sessionIds): ?BaseBuilder
    {
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
                static fn (BaseBuilder $sub): BaseBuilder => $sub->select('id')->from('challenge_attempts')->whereIn('session_id', $sessionIds),
            );
        }

        if ($table === 'game_sessions') {
            return $sessionIds === [] ? null : $db->table($table)->whereIn('id', $sessionIds);
        }

        if ($table === 'participant_feedback') {
            if (isset($scope['participant_id'])) {
                return $db->table($table)->where('participant_id', (int) $scope['participant_id']);
            }

            return $sessionIds === [] ? null : $db->table($table)->whereIn('session_id', $sessionIds);
        }

        // tabel milik peserta: hanya tersentuh bila cakupannya satu peserta
        if (! isset($scope['participant_id'])) {
            return null;
        }

        return match ($table) {
            'participant_consents' => $db->table($table)->where('participant_id', (int) $scope['participant_id']),
            'participants'         => $db->table($table)->where('id', (int) $scope['participant_id']),
            default                => null,
        };
    }

    private function pendingRetentionFor(int $studyId): ?array
    {
        foreach ($this->pendingRetentionRequests() as $row) {
            if ((int) ($this->decode($row)['scope']['study_id'] ?? 0) === $studyId) {
                return $row;
            }
        }

        return null;
    }

    /** Admin aktif pertama, pemilik pratinjau yang dibuat cron. */
    private function systemRequester(): ?int
    {
        $row = $this->db()->table('staff_users')
            ->select('id')
            ->where('role', 'admin')
            ->where('is_active', 1)
            ->orderBy('id', 'ASC')
            ->get()
            ->getRowArray();

        return $row === null ? null : (int) $row['id'];
    }

    /** @return array<string, mixed> isi scope_json (disimpan sebagai string JSON) */
    public function decode(array $request): array
    {
        $raw  = $request['scope_json'] ?? null;
        $data = is_array($raw) ? $raw : json_decode((string) $raw, true);

        return is_array($data) ? $data : [];
    }

    private function db(): BaseConnection
    {
        return db_connect();
    }
}
