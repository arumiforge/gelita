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
        // Part 3 (29 §12): typed field sheets; rows per student, imports in a study cleanup
        'field_sheet_rows', 'field_sheet_imports',
        'game_event_logs', 'audio_usage_events', 'item_tries', 'item_responses',
        // Part 7 (33 §9, 30 §7.3): attempt segments, before `challenge_attempts`
        'challenge_attempt_segments',
        'challenge_attempts',
        'device_logs', 'participant_progress', 'participant_seats', 'field_session_members',
        'participant_feedback', 'game_sessions',
        // Part 4 (30 §7.3): counted one by one although the FK cascades, so the preview and the minutes show them.
        // Later parts add their own tables here: `instrument_responses`, `instrument_parts`, `instrument_blocks`,
        // `instrument_sittings` (part 8, 34 §11.1) and `derived_*` (part 9, 35 §12), all before `participants`.
        'participant_consents',
        // Part 8 (34 §11.3, 30 §15): the test module's answers, children first; the FK cascades too
        'instrument_responses', 'instrument_parts', 'instrument_blocks', 'instrument_sittings',
        'participants',
    ];

    /**
     * Part 4 (30 §7.3): scope `kind = risk_items` deletes one student's
     * `risk` sitting of the test module with its parts and answers
     * (ang-21–ang-23, `risk_offered`, `risk_reconsent`); the tables are part 8's (34 §11.1).
     */
    public const RISK_ORDER = ['instrument_responses', 'instrument_parts', 'instrument_blocks', 'instrument_sittings'];

    /** Tables kept per student: whole rows go with the student (scope per student or a closed study's cohort). */
    private const STUDENT_TABLES = ['participant_progress', 'participant_seats', 'field_session_members', 'participant_feedback', 'participant_consents', 'participants'];

    /** Jenis cakupan utama; tepat satu yang dipakai. */
    public const SCOPE_KEYS = ['participant_id', 'session_id', 'study_id'];

    /** Penyempit cakupan studi. */
    public const NARROWING_KEYS = ['date_from', 'date_to'];

    public const ORIGIN_RETENTION = 'retention';

    // ----------------------------------------------------------- cakupan

    /**
     * Cakupan penghapusan yang sah dari input bebas, atau null.
     * Tepat satu kunci utama (peserta → sesi → studi); penyempit rentang
     * tanggal hanya berlaku untuk cakupan studi.
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

            // Part 4 (30 §7.3): one student's risk-item answers only
            if ($key === 'participant_id' && ($input['kind'] ?? null) === 'risk_items') {
                return $scope + ['kind' => 'risk_items'];
            }

            if ($key !== 'study_id') {
                return $scope;
            }

            // Part 4 (30 §11): a closed field-mode study's whole cohort, students included
            if (! empty($input['participants'])) {
                return $scope + ['participants' => 1];
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
        $db             = $this->db();
        $sessionIds     = $this->sessionIds($scope);
        $participantIds = $this->participantIds($scope);
        $counts         = [];

        foreach ($this->tablesFor($scope) as $table) {
            $counts[$table] = $this->scopedBuilder($db, $table, $scope, $sessionIds, $participantIds)?->countAllResults() ?? 0;
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
        if (($scope['kind'] ?? null) === 'risk_items') {
            return [];
        }

        $builder = $this->db()->table('game_sessions')->select('game_sessions.id');

        if (isset($scope['session_id'])) {
            $builder->where('game_sessions.id', (int) $scope['session_id']);
        } elseif (isset($scope['participant_id'])) {
            $builder->where('game_sessions.participant_id', (int) $scope['participant_id']);
        } elseif (isset($scope['study_id'])) {
            $builder->where('game_sessions.study_id', (int) $scope['study_id']);

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
            throw new \DomainException('Permintaan ini sudah dijalankan atau dibatalkan sebelumnya.');
        }

        $stored = $this->decode($request);
        $scope  = $this->normalizeScope((array) ($stored['scope'] ?? []));

        if ($scope === null) {
            throw new \DomainException('Permintaan ini tidak menyebut data yang akan dihapus.');
        }

        $previewTotal = (int) ($request['affected_count'] ?? 0);
        $nowCounts    = $this->countAffected($scope);
        $nowTotal     = array_sum($nowCounts);

        if ($this->driftExceeded($previewTotal, $nowTotal)) {
            throw new \DomainException(sprintf(
                'Jumlah data yang akan dihapus berubah dari %d menjadi %d sejak dihitung. Demi keamanan penghapusan dibatalkan; batalkan permintaan ini lalu hitung ulang.',
                $previewTotal,
                $nowTotal,
            ));
        }

        $mode   = $request['mode'] === 'hard' ? 'hard' : 'soft';
        $reason = (string) ($request['reason'] ?? 'permintaan penghapusan data');
        $db     = $this->db();

        $db->transBegin();

        // Part 4 (30 §7.1): the status moves conditionally first; a second admin pressing at once changes nothing
        $db->table('data_deletion_requests')->where('id', $requestId)->where('status', 'preview')
            ->update(['status' => 'executed', 'approved_by' => $staffId, 'executed_at' => date('Y-m-d H:i:s')]);

        if ($db->affectedRows() !== 1) {
            $db->transRollback();

            throw new \DomainException(lang('Admin.requestAlreadyRun'));
        }

        try {
            $affected = $mode === 'hard'
                ? $this->hardDelete($scope)
                : $this->softDelete($scope, $staffId, $reason);

            $requests->update($requestId, [
                'affected_count' => array_sum($affected),
                'scope_json'     => $stored + ['affected' => $affected],
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

            throw new \DomainException('Penghapusan gagal karena kesalahan sistem; tidak ada data yang berubah.', 0, $e);
        }

        // Part 4 (30 §7.4, §7.6): a parent's request purges the server downloads and ticks its system items
        if (($request['kind'] ?? null) !== null) {
            service('dataRequests')->afterExecute($requestId, $staffId);
        }

        return ['mode' => $mode, 'affected' => $affected, 'total' => array_sum($affected)];
    }

    public function cancel(int $requestId, int $staffId): void
    {
        $requests = model(DataDeletionRequestModel::class);
        $request  = $requests->find($requestId);

        if ($request === null || ! in_array($request['status'], ['requested', 'preview'], true)) {
            throw new \DomainException('Permintaan ini sudah dijalankan atau dibatalkan sebelumnya.');
        }

        // Conditional on the status read (30 §7.1)
        $this->db()->table('data_deletion_requests')->where('id', $requestId)->where('status', $request['status'])->update(['status' => 'cancelled']);

        if ($this->db()->affectedRows() !== 1) {
            throw new \DomainException('Permintaan ini sudah dijalankan atau dibatalkan sebelumnya.');
        }

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

        // Part 8 (34 §7.4): test blocks past their hidden limit with no request since
        $result['closed_test_blocks'] = service('instruments')->closeOverdueBlocks();

        // Part 4 (30 §12): only with the research privacy switch on
        if ($config->researchPrivacy) {
            $result['expired_browser_sessions'] = $this->purgeExpiredBrowserSessions();
        }

        model(AuditLogModel::class)->record('retention_run', [
            'staff_user_id' => $staffId,
            'metadata'      => $result,
        ]);

        return $result;
    }

    /**
     * Attempt `in_progress` yang tidak tersentuh lebih dari $hours jam →
     * `abandoned` (bintang 0) + event `challenge_abandoned {via: retention}`.
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
        // Sessions of a field session are paused when it closes and never abandoned here (28 §6.6)
        $stale    = $sessions->where('status', 'paused')->where('last_active_at <', $cutoff)->where('field_session_id', null)->findAll();
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
        $studies  = $this->db()->table('research_studies')->select('id, code, retention_days, field_mode, closed_at')->where('retention_days >', 0)->get()->getResultArray();
        $previews = [];
        $warnings = [];

        foreach ($studies as $study) {
            $days   = (int) $study['retention_days'];
            $dateTo = date('Y-m-d', strtotime('today -' . ($days + 1) . ' days'));
            $scope  = ['study_id' => (int) $study['id'], 'date_to' => $dateTo];

            // Part 4 (30 §11, K10): a field-mode study counts from its closing, never before, and the whole cohort goes
            if ((int) ($study['field_mode'] ?? 0) === 1) {
                if ($study['closed_at'] === null || substr((string) $study['closed_at'], 0, 10) > $dateTo) {
                    continue;
                }

                $scope = ['study_id' => (int) $study['id'], 'participants' => 1];

                if (array_sum($this->countAffected($scope)) === 0) {
                    continue;
                }
            } elseif ($this->sessionIds($scope) === []) {
                continue;
            }

            $requester = $staffId ?? $this->systemRequester();

            if ($requester === null) {
                $warnings[] = "Studi {$study['code']} punya data yang melewati batas lama penyimpanan, tetapi tidak ada akun admin aktif untuk mencatat permintaan penghapusannya.";

                continue;
            }

            $counts   = $this->countAffected($scope);
            $reason   = isset($scope['participants'])
                ? "Melewati masa simpan {$days} hari studi {$study['code']} sejak studi ditutup. Dibuat otomatis oleh retensi; periksa sebelum mengeksekusi."
                : "Melewati masa simpan {$days} hari studi {$study['code']} (sesi dimulai s.d. {$dateTo}). Dibuat otomatis oleh retensi; periksa sebelum mengeksekusi.";
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

    /**
     * Part 4 (30 §12, K12): removes `ci_sessions` rows older than the session
     * lifetime, and rows whose `ip_address` is not a 64-character hash (raw
     * IPs from DatabaseHandler before the switch). The cutoff is computed in
     * PHP so the same query runs on SQLite. Called only with the switch on.
     */
    public function purgeExpiredBrowserSessions(): int
    {
        $db = $this->db();

        if (! $db->tableExists('ci_sessions')) {
            return 0;
        }

        $cutoff = date('Y-m-d H:i:s', time() - config('Session')->expiration);
        $db->table('ci_sessions')->where('timestamp <', $cutoff)->delete();
        $removed = $db->affectedRows();

        $raw = array_column(array_filter(
            $db->table('ci_sessions')->select('id, ip_address')->get()->getResultArray(),
            static fn (array $row): bool => preg_match('/^[0-9a-f]{64}$/', (string) $row['ip_address']) !== 1,
        ), 'id');

        if ($raw !== []) {
            $db->table('ci_sessions')->whereIn('id', $raw)->delete();
            $removed += $db->affectedRows();
        }

        return $removed;
    }

    // ------------------------------------------------------------ internal

    /** @return list<string> the tables a scope deletes from, children first */
    private function tablesFor(array $scope): array
    {
        if (($scope['kind'] ?? null) === 'risk_items') {
            return self::RISK_ORDER;
        }

        return self::HARD_ORDER;
    }

    /**
     * Students in scope: the one student, or a closed field-mode study's
     * cohort (students with a consent row in it, 30 §10.2, §11), read before
     * anything is deleted.
     *
     * @return list<int>
     */
    private function participantIds(array $scope): array
    {
        if (isset($scope['participant_id'])) {
            return [(int) $scope['participant_id']];
        }

        if (isset($scope['study_id'], $scope['participants'])) {
            return array_map('intval', array_column(
                $this->db()->table('participant_consents')->select('participant_id')->where('study_id', (int) $scope['study_id'])->get()->getResultArray(),
                'participant_id',
            ));
        }

        return [];
    }

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
     * Deletes one student's play data and keeps the account (`gelita:uji:akun
     * --reset`, research revision 10, part 2: 28 §13): every HARD_ORDER table
     * in the student's scope except `participants` itself.
     *
     * @return array<string, int> rows deleted per table
     */
    public function deletePlayData(int $participantId): array
    {
        $db       = $this->db();
        $scope    = ['participant_id' => $participantId];
        $sessions = $this->sessionIds($scope);
        $affected = [];

        $db->transBegin();

        try {
            foreach (self::HARD_ORDER as $table) {
                // The account and its consent row stay (a test account keeps its risk-items consent)
                $builder = in_array($table, ['participants', 'participant_consents'], true) ? null : $this->scopedBuilder($db, $table, $scope, $sessions, [$participantId]);

                if ($builder === null) {
                    continue;
                }

                $builder->delete();
                $affected[$table] = $db->affectedRows();
            }

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();

            throw $e;
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
        $db             = $this->db();
        $sessionIds     = $this->sessionIds($scope);
        $participantIds = $this->participantIds($scope);
        $affected       = [];

        foreach ($this->tablesFor($scope) as $table) {
            $builder = $this->scopedBuilder($db, $table, $scope, $sessionIds, $participantIds);

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
     * @param list<int>                 $participantIds participantIds()
     */
    private function scopedBuilder(BaseConnection $db, string $table, array $scope, array $sessionIds, array $participantIds = []): ?BaseBuilder
    {
        $bySession = ['game_event_logs', 'audio_usage_events', 'challenge_attempts', 'device_logs'];

        // Part 3 (29 §12, 30 §7.3): a student's field-sheet rows; a whole study's imports and rows
        if ($table === 'field_sheet_rows' || $table === 'field_sheet_imports') {
            $wholeStudy = isset($scope['study_id']) && ! isset($scope['date_from']) && ! isset($scope['date_to']);

            if ($table === 'field_sheet_rows' && isset($scope['participant_id'])) {
                return $db->table($table)->where('participant_id', (int) $scope['participant_id']);
            }

            if (! $wholeStudy) {
                return null;
            }

            $sessions = static fn (BaseBuilder $sub): BaseBuilder => $sub->select('id')->from('field_sessions')->where('study_id', (int) $scope['study_id']);

            return $table === 'field_sheet_imports'
                ? $db->table($table)->whereIn('field_session_id', $sessions)
                : $db->table($table)->whereIn('import_id', static fn (BaseBuilder $sub): BaseBuilder => $sub->select('id')->from('field_sheet_imports')->whereIn('field_session_id', $sessions));
        }

        // Part 4 (30 §7.3): the student's `risk` sitting and what hangs on it (part 8 tables)
        if (in_array($table, self::RISK_ORDER, true) && ($scope['kind'] ?? null) === 'risk_items') {
            $sittings = static fn (BaseBuilder $sub): BaseBuilder => $sub->select('id')->from('instrument_sittings')
                ->where('participant_id', (int) $scope['participant_id'])->where('kind', 'risk');

            return $table === 'instrument_sittings'
                ? $db->table($table)->where('participant_id', (int) $scope['participant_id'])->where('kind', 'risk')
                : $db->table($table)->whereIn('sitting_id', $sittings);
        }

        // Part 8 (34 §11.3): every sitting of the student(s), or of a whole study without a date range
        if (in_array($table, self::RISK_ORDER, true)) {
            $byStudent = isset($scope['participant_id']) || isset($scope['participants']);
            $ids       = $byStudent ? $participantIds : [];

            if ($byStudent ? $ids === [] : ! (isset($scope['study_id']) && ! isset($scope['date_from']) && ! isset($scope['date_to']))) {
                return null;
            }

            $sittings = static function (BaseBuilder $sub) use ($ids, $scope): BaseBuilder {
                $sub->select('id')->from('instrument_sittings');

                return $ids !== [] ? $sub->whereIn('participant_id', $ids) : $sub->where('study_id', (int) $scope['study_id']);
            };

            if ($table === 'instrument_sittings') {
                return $ids !== [] ? $db->table($table)->whereIn('participant_id', $ids) : $db->table($table)->where('study_id', (int) $scope['study_id']);
            }

            return $db->table($table)->whereIn('sitting_id', $sittings);
        }

        // Part 4 (30 §11): a closed field-mode study's cohort, by student
        if (isset($scope['participants']) && in_array($table, self::STUDENT_TABLES, true)) {
            return $participantIds === [] ? null : $db->table($table)->whereIn($table === 'participants' ? 'id' : 'participant_id', $participantIds);
        }

        if ($table === 'participant_consents') {
            return isset($scope['participant_id']) ? $db->table($table)->where('participant_id', (int) $scope['participant_id']) : null;
        }

        // Rows per student and study (28 §14): a student's own, or a whole study without a date range
        if (in_array($table, ['participant_progress', 'participant_seats', 'field_session_members'], true)) {
            if (isset($scope['participant_id'])) {
                return $db->table($table)->where('participant_id', (int) $scope['participant_id']);
            }

            if ($table !== 'field_session_members' && isset($scope['study_id']) && ! isset($scope['date_from']) && ! isset($scope['date_to'])) {
                return $db->table($table)->where('study_id', (int) $scope['study_id']);
            }

            return null;
        }

        if (in_array($table, $bySession, true)) {
            return $sessionIds === [] ? null : $db->table($table)->whereIn('session_id', $sessionIds);
        }

        // Part 7 (33 §9): segments of the scope's attempts, and those written in its sessions (an attempt moved on, K3)
        if ($table === 'challenge_attempt_segments') {
            if ($sessionIds === []) {
                return null;
            }

            return $db->table($table)->groupStart()
                ->whereIn('challenge_attempt_id', static fn (BaseBuilder $sub): BaseBuilder => $sub->select('id')->from('challenge_attempts')->whereIn('session_id', $sessionIds))
                ->orWhereIn('session_id', $sessionIds)
                ->groupEnd();
        }

        if ($table === 'item_responses' || $table === 'item_tries') {
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
            'participants' => $db->table($table)->where('id', (int) $scope['participant_id']),
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
