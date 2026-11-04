<?php

namespace App\Services;

use App\Libraries\StaffAccess;
use App\Models\AuditLogModel;
use App\Models\DataDeletionRequestModel;
use App\Models\DataExportModel;
use App\Models\ParticipantModel;
use App\Models\ResearchStudyModel;
use App\Models\StaffUserModel;

/**
 * Parents' data requests (research revision 10, part 4: 30 §7): file,
 * confirm, reject, cancel, checklist, deadlines, and the minutes.
 *
 * Flow (K1): a `peneliti` files → `requested`; `ketua` confirms → `preview`
 * (hard mode, counted by RetentionService); the admin runs it in Tata kelola
 * → `executed`; every checklist item ticked → `completed`. A request filed by
 * `ketua` goes straight to `preview`. Every status move is conditional on the
 * status read (repo rule, Concurrency). RetentionService stays the only path
 * that deletes research data (K9).
 */
class DataRequestService
{
    public const KINDS = ['all', 'risk_items', 'recording'];

    public const VIA = ['paper', 'whatsapp', 'email', 'oral'];

    /** Checklist item => kinds it applies to (30 §7.6). */
    public const CHECKLIST = [
        'system'            => ['all', 'risk_items'],
        'server_exports'    => ['all', 'risk_items'],
        'researcher_copies' => ['all', 'risk_items'],
        'backups'           => ['all', 'risk_items'],
        'recordings'        => ['all', 'recording'],
        'paper_sheets'      => ['all'],
        'parent_notified'   => ['all', 'risk_items', 'recording'],
    ];

    /** Who ticks an item (30 §14); null = ticked by the system when the request runs. */
    public const TICK_CAPABILITY = [
        'system'            => null,
        'server_exports'    => null,
        'researcher_copies' => 'deletion.confirm',
        'backups'           => 'deletion.execute',
        'recordings'        => 'deletion.request',
        'paper_sheets'      => 'deletion.request',
        'parent_notified'   => 'deletion.confirm',
    ];

    /** @return list<string> checklist items of a kind */
    public static function itemsFor(string $kind): array
    {
        return array_keys(array_filter(self::CHECKLIST, static fn (array $kinds): bool => in_array($kind, $kinds, true)));
    }

    /**
     * What the form shows after the code is typed (30 §7.1): school code,
     * class group, consent status. Never a name.
     *
     * @return array{participant_id: int, participant_code: string, school_ref: string, rombel: ?string, status: string, study_id: ?int}|null
     */
    public function lookup(string $code): ?array
    {
        $code = strtoupper(trim($code));
        $row  = $code === '' ? null : db_connect()->table('participants p')
            ->select('p.id, p.participant_code, p.rombel, s.research_code, c.status, c.study_id')
            ->join('schools s', 's.id = p.school_id', 'left')
            ->join('participant_consents c', 'c.participant_id = p.id', 'left')
            ->where('p.participant_code', $code)->where('p.deleted_at', null)
            ->get()->getRowArray();

        if ($row === null) {
            return null;
        }

        $studyId = $row['study_id'] ?? db_connect()->table('game_sessions')->selectMax('study_id', 'study_id')
            ->where('participant_id', (int) $row['id'])->get()->getRow('study_id');

        return [
            'participant_id'   => (int) $row['id'],
            'participant_code' => (string) $row['participant_code'],
            'school_ref'       => (string) ($row['research_code'] ?? '—'),
            'rombel'           => $row['rombel'],
            'status'           => (string) ($row['status'] ?? 'active'),
            'study_id'         => $studyId === null ? null : (int) $studyId,
        ];
    }

    /**
     * Files a request. Returns `created` (with id), `nothing` (risk items
     * without answers: only the consent changed, 30 §7.2), or `invalid`.
     *
     * @param array<string, mixed> $input participant_code, kind, received_at, received_via, verified, verified_at
     *
     * @return array{status: string, id?: int, message?: string}
     */
    public function create(array $input, int $staffId, string $role): array
    {
        $kind     = (string) ($input['kind'] ?? '');
        $via      = (string) ($input['received_via'] ?? '');
        $received = self::dateTime($input['received_at'] ?? null);
        $verified = self::dateTime($input['verified_at'] ?? null);
        $found    = $this->lookup((string) ($input['participant_code'] ?? ''));

        $error = match (true) {
            $found === null                                  => lang('Admin.resetCodeNotFound'),
            ! in_array($kind, self::KINDS, true)             => lang('Admin.requestBadKind'),
            ! in_array($via, self::VIA, true)                => lang('Admin.requestBadVia'),
            $received === null || $received > date('Y-m-d H:i:s') => lang('Admin.requestBadReceived'),
            (string) ($input['verified'] ?? '') !== '1' || $verified === null || $verified > date('Y-m-d H:i:s') => lang('Admin.requestNotVerified'),
            $found['study_id'] === null                      => lang('Admin.requestNoStudy'),
            service('studyClosure')->identitiesReplaced($found['study_id']) => lang('Admin.closureReplaced'),
            default                                          => null,
        };

        if ($error !== null) {
            return ['status' => 'invalid', 'message' => $error];
        }

        $participantId = $found['participant_id'];
        $consent       = service('consentService')->applyRequest($participantId, $found['study_id'], $kind, substr($received, 0, 10), $staffId);

        if ($consent['status'] === 'invalid' || $consent['status'] === 'stale') {
            return ['status' => 'invalid', 'message' => $consent['message'] ?? lang('Admin.consentStale')];
        }

        if ($kind === 'risk_items' && ! $this->hasRiskAnswers($participantId)) {
            return ['status' => 'nothing'];
        }

        $participant = model(ParticipantModel::class)->find($participantId);
        $scope       = $kind === 'risk_items' ? ['participant_id' => $participantId, 'kind' => 'risk_items'] : ['participant_id' => $participantId];
        $requests    = model(DataDeletionRequestModel::class);
        $id          = $requests->insert([
            'requested_by'     => $staffId,
            'scope_json'       => ['scope' => $scope, 'since' => (string) $participant->created_at, 'origin' => 'parent'],
            'reason'           => lang('Admin.requestFromParents'),
            'mode'             => 'hard',   // K8: a parent's request is always hard
            'status'           => 'requested',
            'affected_count'   => 0,
            'kind'             => $kind,
            'participant_code' => $found['participant_code'],
            'received_at'      => $received,
            'verified_at'      => $verified,
            'received_via'     => $via,
            'checklist_json'   => [],
        ], true);

        if ($id === false) {
            throw new \RuntimeException('Permintaan gagal disimpan: ' . implode(' ', $requests->errors()));
        }

        $this->audit('data_request_created', (int) $id, $staffId, ['participant_code' => $found['participant_code'], 'kind' => $kind]);

        // K1: the head researcher's own request needs no second look
        if (StaffAccess::allows($role, 'deletion.confirm')) {
            $this->moveOn((int) $id, $staffId);
        }

        return ['status' => 'created', 'id' => (int) $id];
    }

    /** `ketua` confirms a researcher's request (30 §7.1 step 2). */
    public function confirm(int $id, int $staffId): void
    {
        $this->moveOn($id, $staffId);
    }

    public function reject(int $id, int $staffId, string $reason): void
    {
        $reason = trim($reason);

        if ($reason === '') {
            throw new \DomainException(lang('Admin.reasonRequired'));
        }

        $row = $this->find($id);
        $this->move($id, 'requested', 'cancelled', ['reason' => mb_substr(lang('Admin.requestFromParents') . ' · ditolak: ' . $reason, 0, 1000)], lang('Admin.requestAlreadyConfirmed'));
        $this->audit('data_request_rejected', $id, $staffId, ['participant_code' => $row['participant_code'], 'reason' => $reason]);
    }

    /** The filer withdraws a request before it is confirmed. */
    public function cancel(int $id, int $staffId): void
    {
        $row = $this->find($id);

        if ((int) $row['requested_by'] !== $staffId) {
            throw new \DomainException(lang('Admin.requestNotYours'));
        }

        $this->move($id, 'requested', 'cancelled', [], lang('Admin.requestAlreadyConfirmed'));
        $this->audit('delete_cancel', $id, $staffId, ['participant_code' => $row['participant_code']]);
    }

    /**
     * After RetentionService::execute(): purge the server downloads made since
     * the student registered and tick `system` and `server_exports` (30 §7.4, §7.6).
     */
    public function afterExecute(int $id, ?int $staffId): void
    {
        $row   = $this->find($id);
        $since = (string) (service('retentionService')->decode($row)['since'] ?? '1970-01-01 00:00:00');

        service('exportService')->purgeForRequest($id, $since, $staffId);

        foreach (['system', 'server_exports'] as $item) {
            $this->writeTick($id, $item, $staffId);
        }
    }

    /**
     * Ticks one checklist item. $role must hold the item's capability;
     * `parent_notified` waits for every other item of the kind.
     */
    public function tick(int $id, string $item, int $staffId, string $role): void
    {
        $row = $this->find($id);

        if (! in_array($item, self::itemsFor((string) $row['kind']), true) || self::TICK_CAPABILITY[$item] === null) {
            throw new \DomainException(lang('Admin.requestBadItem'));
        }

        if (! StaffAccess::allows($role, (string) self::TICK_CAPABILITY[$item])) {
            throw new \DomainException(lang('Admin.requestTickNotAllowed'));
        }

        if ($row['status'] !== 'executed') {
            throw new \DomainException(lang('Admin.requestNotRunYet'));
        }

        if ($item === 'parent_notified' && array_diff(self::itemsFor((string) $row['kind']), ['parent_notified'], array_keys($this->checklist($row))) !== []) {
            throw new \DomainException(lang('Admin.requestChecklistBlocked'));
        }

        $this->writeTick($id, $item, $staffId);
    }

    /**
     * Requests the caller may see (30 §7.1): `peneliti` their own, `ketua`
     * and the admin all. Parents' requests only (admin and retention
     * deletions stay in Tata kelola).
     *
     * @return list<array<string, mixed>>
     */
    public function visibleTo(string $role, int $staffId): array
    {
        $requests = model(DataDeletionRequestModel::class)->where('kind IS NOT NULL', null, false);

        if (! StaffAccess::allowsAny($role, ['deletion.confirm', 'deletion.execute'])) {
            $requests->where('requested_by', $staffId);
        }

        return $requests->orderBy('id', 'DESC')->findAll(100);
    }

    /** True when $role/$staffId may open request $row. */
    public function canSee(array $row, string $role, int $staffId): bool
    {
        return $row['kind'] !== null
            && (StaffAccess::allowsAny($role, ['deletion.confirm', 'deletion.execute']) || (int) $row['requested_by'] === $staffId);
    }

    /** @return array<string, array{at: string, by: ?int}> */
    public function checklist(array $row): array
    {
        $raw  = $row['checklist_json'] ?? null;
        $data = is_array($raw) ? $raw : json_decode((string) $raw, true);

        return is_array($data) ? $data : [];
    }

    /**
     * Deadlines in hours (3 × 24 calendar hours, 30 §7.7).
     *
     * @return array{received_hours: ?int, notify_left: ?int, level: string}
     */
    public function deadlines(array $row, ?int $now = null): array
    {
        $now      = $now ?? time();
        $limit    = config('Gelita')->dataRequestHours;
        $received = $row['received_at'] === null ? null : (int) floor(($now - strtotime((string) $row['received_at'])) / 3600);
        $left     = null;
        $level    = 'ok';

        if ($row['executed_at'] !== null && ! isset($this->checklist($row)['parent_notified'])) {
            $left  = (int) floor((strtotime((string) $row['executed_at']) + $limit * 3600 - $now) / 3600);
            $level = $left < 0 ? 'late' : ($left < 24 ? 'warn' : 'ok');
        }

        return ['received_hours' => $received, 'notify_left' => $left, 'level' => $level];
    }

    /**
     * Ringkasan cards (30 §7.7): admin "waiting to run", `ketua` "waiting for
     * confirmation" and "waiting for ticks".
     *
     * @return array{to_run: int, to_confirm: int, to_tick: int}
     */
    public function counts(): array
    {
        $count = static fn (string $status): int => model(DataDeletionRequestModel::class)->where('kind IS NOT NULL', null, false)->where('status', $status)->countAllResults();

        return ['to_run' => $count('preview'), 'to_confirm' => $count('requested'), 'to_tick' => $count('executed')];
    }

    /**
     * Downloads of the head researcher since the student registered (30 §7.4),
     * for the `researcher_copies` tick.
     *
     * WAVE-E seam (part 3): narrow to downloads that were fetched and hold
     * students (`data_exports.participant_count > 0`, download history 29 §9).
     *
     * @return list<array<string, mixed>>
     */
    public function researcherCopies(array $row): array
    {
        $since = (string) (service('retentionService')->decode($row)['since'] ?? '1970-01-01 00:00:00');

        return model(DataExportModel::class)->select('id, format, created_at, completed_at, purged_by_request_id')
            ->where('status', 'done')->where('created_at >=', $since)->orderBy('id', 'ASC')->findAll();
    }

    /** HTML of the minutes (30 §7.6): code, kind, dates, row counts, ticks; no child's or parent's name. */
    public function minutesHtml(array $row): string
    {
        $staff = array_column(model(StaffUserModel::class)->asArray()->select('id, username')->findAll(), 'username', 'id');
        $data  = service('retentionService')->decode($row);

        return view('pdf/deletion-minutes', [
            'request'   => $row,
            'counts'    => (array) ($data['affected'] ?? $data['counts'] ?? []),
            'checklist' => $this->checklist($row),
            'items'     => self::itemsFor((string) $row['kind']),
            'staff'     => $staff,
        ], ['saveData' => false]);
    }

    /** The minutes as a PDF file in writable/cache; the caller sends and removes it. */
    public function minutesPdf(array $row): string
    {
        $path = WRITEPATH . 'cache' . DIRECTORY_SEPARATOR . 'berita-acara-' . bin2hex(random_bytes(6)) . '.pdf';
        service('reportService')->writePdf($this->minutesHtml($row), $path, 'Berita acara permintaan hapus data');

        return $path;
    }

    /** @return array<string, mixed> */
    public function find(int $id): array
    {
        $row = model(DataDeletionRequestModel::class)->find($id);

        if ($row === null || $row['kind'] === null) {
            throw new \DomainException(lang('Admin.requestNotFound'));
        }

        return $row;
    }

    /** True when the student has an answered `risk` sitting (part 8 tables; none before part 8). */
    public function hasRiskAnswers(int $participantId): bool
    {
        $db = db_connect();

        return $db->tableExists('instrument_sittings')
            && $db->table('instrument_sittings')->where('participant_id', $participantId)->where('kind', 'risk')->countAllResults() > 0;
    }

    // ------------------------------------------------------------ internal

    /** `requested` → `preview` (counted, hard), or straight to `executed` for `recording` (nothing on the server). */
    private function moveOn(int $id, int $staffId): void
    {
        $row       = $this->find($id);
        $retention = service('retentionService');
        $stored    = $retention->decode($row);
        $now       = date('Y-m-d H:i:s');
        $confirmed = ['confirmed_by' => $staffId, 'confirmed_at' => $now];

        if ($row['kind'] === 'recording') {
            $this->move($id, 'requested', 'executed', $confirmed + ['executed_at' => $now, 'affected_count' => 0], lang('Admin.requestAlreadyConfirmed'));
            $this->audit('data_request_confirmed', $id, $staffId, ['participant_code' => $row['participant_code'], 'kind' => 'recording']);

            return;
        }

        $scope  = $retention->normalizeScope((array) ($stored['scope'] ?? []));
        $counts = $retention->countAffected($scope);

        $this->move($id, 'requested', 'preview', $confirmed + [
            'scope_json'     => json_encode($stored + ['counts' => $counts], JSON_UNESCAPED_UNICODE),
            'affected_count' => array_sum($counts),
        ], lang('Admin.requestAlreadyConfirmed'));

        $this->audit('data_request_confirmed', $id, $staffId, ['participant_code' => $row['participant_code'], 'kind' => $row['kind']]);
        $this->audit('delete_preview', $id, $staffId, ['scope' => $scope, 'mode' => 'hard', 'counts' => $counts, 'origin' => 'parent']);
    }

    /**
     * Conditional status move: changes the row only while it still has
     * $from; the loser gets $lostMessage.
     *
     * @param array<string, mixed> $data
     */
    private function move(int $id, string $from, string $to, array $data, string $lostMessage): void
    {
        $db = db_connect();
        $db->table('data_deletion_requests')->where('id', $id)->where('status', $from)->update(['status' => $to] + $data);

        if ($db->affectedRows() !== 1) {
            throw new \DomainException($lostMessage);
        }
    }

    /** Writes one tick, conditional on the checklist read; all items done → `completed`. */
    private function writeTick(int $id, string $item, ?int $staffId): void
    {
        $db  = db_connect();
        $row = $this->find($id);
        $raw = $row['checklist_json'];
        $now = date('Y-m-d H:i:s');

        $list = $this->checklist($row);

        if (isset($list[$item])) {
            return;
        }

        $list[$item] = ['at' => $now, 'by' => $staffId];
        $builder     = $db->table('data_deletion_requests')->where('id', $id);
        $raw === null ? $builder->where('checklist_json', null) : $builder->where('checklist_json', $raw);
        $builder->update(['checklist_json' => json_encode($list, JSON_UNESCAPED_UNICODE)]);

        if ($db->affectedRows() !== 1) {
            throw new \DomainException(lang('Admin.consentStale'));
        }

        $this->audit('data_request_ticked', $id, $staffId, ['participant_code' => $row['participant_code'], 'item' => $item]);

        if (array_diff(self::itemsFor((string) $row['kind']), array_keys($list)) === []) {
            $db->table('data_deletion_requests')->where('id', $id)->where('status', 'executed')->update(['status' => 'completed', 'completed_at' => $now]);
        }
    }

    /** `Y-m-d H:i:s` from a form value (`datetime-local` or SQL), else null. */
    public static function dateTime(mixed $value): ?string
    {
        $value = trim(str_replace('T', ' ', (string) $value));

        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}(:\d{2})?$/', $value) !== 1 || strtotime($value) === false) {
            return null;
        }

        return date('Y-m-d H:i:s', strtotime($value));
    }

    /** @param array<string, mixed> $metadata */
    private function audit(string $action, int $id, ?int $staffId, array $metadata): void
    {
        model(AuditLogModel::class)->record($action, [
            'staff_user_id' => $staffId,
            'target_type'   => 'data_deletion_request',
            'target_id'     => (string) $id,
            'metadata'      => $metadata,
        ]);
    }

    /** True for an active field-mode study (the panel pages of this part show only then, 30 §0.1). */
    public static function fieldModeAnywhere(): bool
    {
        return model(ResearchStudyModel::class)->where('field_mode', 1)->countAllResults() > 0;
    }
}
