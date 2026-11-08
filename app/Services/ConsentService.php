<?php

namespace App\Services;

use App\Models\AuditLogModel;
use App\Models\ParticipantConsentModel;
use App\Models\ParticipantModel;

/**
 * Coded consent per student, withdrawal, and deactivated accounts (research
 * revision 10, part 4: 30 §5.2, §5.3, §10.1).
 *
 * Every write is conditional on the `updated_at` the caller read (repo rule,
 * Concurrency): two researchers saving the same row at once → one wins, the
 * other is told to reload. A withdrawal (`withdrawn_keep`, `withdrawn_delete`)
 * fills `participants.deactivated_at` in the same transaction (K5).
 */
class ConsentService
{
    public const STATUSES = ['active', 'left', 'withdrawn_keep', 'withdrawn_delete'];

    public const WITHDRAWN = ['withdrawn_keep', 'withdrawn_delete'];

    /** `participants.assent_version`: research text T4 and the general text (30 §5.1). */
    public const ASSENT_STUDY = 't4-1';

    public const ASSENT_GENERAL = 'umum-1';

    /** Fields of one row that the panel writes, in audit order. */
    private const FIELDS = [
        'parent_consent_on', 'recording_consent', 'recording_withdrawn_on',
        'risk_items_consent', 'risk_items_withdrawn_on', 'status', 'status_on',
    ];

    /** Label of a stored status (Admin.php is Indonesian only). */
    public static function statusLabel(string $status): string
    {
        return lang('Admin.consentStatus' . str_replace(' ', '', ucwords(str_replace('_', ' ', $status))));
    }

    /** Microsecond stamp, so two saves in the same second still differ. */
    public static function stamp(): string
    {
        $t = microtime(true);

        return date('Y-m-d H:i:s', (int) $t) . sprintf('.%06d', (int) (($t - floor($t)) * 1_000_000));
    }

    /**
     * The row made with the defaults at sign-up (30 §5.2): consents No (K6),
     * status active. A second call changes nothing.
     *
     * @param array<string, mixed> $values
     */
    public function createRow(int $participantId, int $studyId, array $values = []): void
    {
        $now = self::stamp();

        db_connect()->table('participant_consents')->ignore(true)->insert($values + [
            'participant_id' => $participantId,
            'study_id'       => $studyId,
            'status'         => 'active',
            'created_at'     => $now,
            'updated_at'     => $now,
        ]);
    }

    /** @return array<string, mixed>|null */
    public function forParticipant(int $participantId): ?array
    {
        return model(ParticipantConsentModel::class)->find($participantId);
    }

    /**
     * T11 is offered only with risk-items consent Yes and status active (30 §5.4),
     * read when the test module reaches that point. Part 8 (34 §8) calls this.
     */
    public function riskItemsAllowed(int $participantId): bool
    {
        $row = $this->forParticipant($participantId);

        return $row !== null && (int) $row['risk_items_consent'] === 1 && $row['status'] === 'active';
    }

    /**
     * Rows of the consent page: every student of the field session's roster with
     * their consent row (null when none yet).
     *
     * @param array<string, mixed> $fieldSession
     *
     * @return list<array{id: int, participant_code: string, consent: array<string, mixed>|null}>
     */
    public function roster(array $fieldSession): array
    {
        $students = service('fieldSessions')->roster($fieldSession);
        $rows     = model(ParticipantConsentModel::class)->forParticipants(array_column($students, 'id'));

        return array_map(static fn (array $s): array => $s + ['consent' => $rows[$s['id']] ?? null], $students);
    }

    /**
     * Students among $participantIds whose parent consent date is not filled yet.
     *
     * @param list<int> $participantIds
     */
    public function missingCount(array $participantIds): int
    {
        $filled = model(ParticipantConsentModel::class)->forParticipants($participantIds);

        return count(array_filter($participantIds, static fn (int $id): bool => ($filled[$id]['parent_consent_on'] ?? null) === null));
    }

    /** missingCount() for students given by participant code (the monitoring roster). */
    public function missingForCodes(array $codes): int
    {
        if ($codes === []) {
            return 0;
        }

        $ids = db_connect()->table('participants')->select('id')->whereIn('participant_code', $codes)->get()->getResultArray();

        return $this->missingCount(array_map('intval', array_column($ids, 'id')));
    }

    /**
     * Saves the panel's rows (participant id => fields + `stamp`, the
     * `updated_at` the page read). Untouched default rows are skipped.
     *
     * @param array<string, mixed>            $fieldSession
     * @param array<int|string, array<mixed>> $input
     *
     * @return array{saved: int, stale: list<string>, errors: array<string, string>, deactivated: list<string>}
     */
    public function save(array $fieldSession, array $input, ?int $staffId): array
    {
        $result = ['saved' => 0, 'stale' => [], 'errors' => [], 'deactivated' => []];

        foreach ($this->roster($fieldSession) as $student) {
            $typed = $input[$student['id']] ?? $input[(string) $student['id']] ?? null;

            if (! is_array($typed)) {
                continue;
            }

            $outcome = $this->apply($student['id'], (int) $fieldSession['study_id'], $typed, $staffId);

            match ($outcome['status']) {
                'saved'   => $result['saved']++,
                'stale'   => $result['stale'][] = $student['participant_code'],
                'invalid' => $result['errors'][$student['participant_code']] = $outcome['message'],
                default   => null,
            };

            if ($outcome['deactivated'] ?? false) {
                $result['deactivated'][] = $student['participant_code'];
            }
        }

        return $result;
    }

    /**
     * Changes one student's row from a parent's request (30 §7.2): `all` →
     * status `withdrawn_delete`; `risk_items`/`recording` → that consent No,
     * withdrawn on $on. The parent consent date is not required here.
     *
     * @return array{status: string, message?: string, deactivated?: bool}
     */
    public function applyRequest(int $participantId, int $studyId, string $kind, string $on, ?int $staffId): array
    {
        $old   = $this->forParticipant($participantId);
        $typed = match ($kind) {
            'all'        => in_array($old['status'] ?? 'active', self::WITHDRAWN, true) ? [] : ['status' => 'withdrawn_delete', 'status_on' => $on],
            'risk_items' => ['risk_items_consent' => '0', 'risk_items_withdrawn_on' => (int) ($old['risk_items_consent'] ?? 0) === 1 ? $on : ($old['risk_items_withdrawn_on'] ?? $on)],
            'recording'  => ['recording_consent' => '0', 'recording_withdrawn_on' => (int) ($old['recording_consent'] ?? 0) === 1 ? $on : ($old['recording_withdrawn_on'] ?? $on)],
            default      => [],
        };

        if ($typed === []) {
            return ['status' => 'unchanged'];
        }

        return $this->apply($participantId, $studyId, $typed + ['stamp' => $old['updated_at'] ?? null], $staffId, false);
    }

    /**
     * One student's row: validate, write conditionally, deactivate on a
     * withdrawal, audit. Status `saved`, `unchanged`, `stale`, or `invalid`.
     *
     * @param array<string, mixed> $typed
     *
     * @return array{status: string, message?: string, deactivated?: bool}
     */
    public function apply(int $participantId, int $studyId, array $typed, ?int $staffId, bool $requireParent = true): array
    {
        $old = $this->forParticipant($participantId);
        $new = $this->merge($old, $typed);

        if ($old === null && $new === $this->merge(null, [])) {
            return ['status' => 'unchanged'];
        }

        $changes = [];

        foreach (self::FIELDS as $field) {
            $before = $old === null ? $this->merge(null, [])[$field] : $old[$field];

            if ((string) ($before ?? '') !== (string) ($new[$field] ?? '')) {
                $changes[$field] = [$before, $new[$field]];
            }
        }

        if ($changes === [] && $old !== null) {
            return ['status' => 'unchanged'];
        }

        $error = $this->check($old, $new, $requireParent);

        if ($error !== null) {
            return ['status' => 'invalid', 'message' => $error];
        }

        $db          = db_connect();
        $consents    = model(ParticipantConsentModel::class);
        $now         = self::stamp();
        $deactivated = false;
        $code        = (string) (db_connect()->table('participants')->select('participant_code')->where('id', $participantId)->get()->getRow('participant_code') ?? '');

        $db->transBegin();

        try {
            if ($old === null) {
                $db->table('participant_consents')->ignore(true)->insert($new + [
                    'participant_id' => $participantId, 'study_id' => $studyId,
                    'entered_by' => $staffId, 'updated_by' => $staffId, 'created_at' => $now, 'updated_at' => $now,
                ]);
            } else {
                $stamp = (string) ($typed['stamp'] ?? $old['updated_at']);
                $consents->where('updated_at', $stamp)->update($participantId, $new + [
                    'updated_by' => $staffId, 'updated_at' => $now,
                    'entered_by' => $old['entered_by'] ?? $staffId,
                ]);
            }

            if ($db->affectedRows() !== 1) {
                $db->transRollback();

                return ['status' => 'stale'];
            }

            if (in_array($new['status'], self::WITHDRAWN, true) && ! in_array($old['status'] ?? 'active', self::WITHDRAWN, true)) {
                $db->table('participants')->where('id', $participantId)->where('deactivated_at', null)->update(['deactivated_at' => date('Y-m-d H:i:s')]);

                if ($db->affectedRows() === 1) {
                    $deactivated = true;
                    $this->audit('participant_deactivated', $participantId, $staffId, ['participant_code' => $code, 'via' => 'consent_status']);
                }
            }

            // Every change after the first save is audited (30 §5.2); the first save is the one that sets the parent date
            if ($old !== null && $old['parent_consent_on'] !== null) {
                foreach ($changes as $field => [$from, $to]) {
                    $this->audit('consent_updated', $participantId, $staffId, [
                        'participant_code' => $code, 'field' => $field, 'from' => $from, 'to' => $to, 'on' => date('Y-m-d'),
                    ]);
                }
            }

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();

            throw $e;
        }

        return ['status' => 'saved', 'deactivated' => $deactivated];
    }

    /**
     * The coded consent register of a study (30 §10.1), one row per student
     * with a consent row in it. No name, school name, NPSN, or username.
     *
     * @param list<int>|null $participantIds narrows to these students (the `izin` sheet of a download)
     *
     * @return list<array<string, mixed>>
     */
    public function registerRows(int $studyId, ?array $participantIds = null): array
    {
        $builder = db_connect()->table('participant_consents c')
            ->select('p.participant_code, c.parent_consent_on, c.recording_consent, c.recording_withdrawn_on')
            ->select('c.risk_items_consent, c.risk_items_withdrawn_on, p.assented_at, p.assent_version, c.status, c.status_on')
            ->join('participants p', 'p.id = c.participant_id')
            ->where('c.study_id', $studyId)
            ->orderBy('p.participant_code', 'ASC');

        if ($participantIds !== null) {
            if ($participantIds === []) {
                return [];
            }

            $builder->whereIn('c.participant_id', $participantIds);
        }

        return $builder->get()->getResultArray();
    }

    /** Columns of the consent register, in order (30 §10.1). */
    /** Consent flags the register download shows as Ya/Tidak (owner, 8 November 2026); the `izin` research sheet keeps 1/0. */
    public const REGISTER_YES_NO = ['recording_consent', 'risk_items_consent'];

    public const REGISTER_COLUMNS = [
        'participant_code', 'parent_consent_on', 'recording_consent', 'recording_withdrawn_on',
        'risk_items_consent', 'risk_items_withdrawn_on', 'assented_at', 'assent_version', 'status', 'status_on',
    ];

    /** Newest consent change of a study, or null. */
    public function lastChange(int $studyId): ?string
    {
        $row = db_connect()->table('participant_consents')->selectMax('updated_at', 'last')->where('study_id', $studyId)->get()->getRowArray();

        return $row['last'] ?? null;
    }

    // ------------------------------------------------------------ internal

    /**
     * The row after $typed is applied to $old (defaults when null).
     *
     * @param array<string, mixed>|null $old
     * @param array<string, mixed>      $typed
     *
     * @return array<string, mixed>
     */
    private function merge(?array $old, array $typed): array
    {
        $date = static function (string $key) use ($old, $typed): ?string {
            $value = array_key_exists($key, $typed) ? trim((string) $typed[$key]) : (string) ($old[$key] ?? '');

            return $value === '' ? null : $value;
        };

        $flag = static fn (string $key): int => array_key_exists($key, $typed)
            ? ((string) $typed[$key] === '1' ? 1 : 0)
            : (int) ($old[$key] ?? 0);

        $row = [
            'parent_consent_on'       => $date('parent_consent_on'),
            'recording_consent'       => $flag('recording_consent'),
            'recording_withdrawn_on'  => $date('recording_withdrawn_on'),
            'risk_items_consent'      => $flag('risk_items_consent'),
            'risk_items_withdrawn_on' => $date('risk_items_withdrawn_on'),
            'status'                  => array_key_exists('status', $typed) ? (string) $typed['status'] : (string) ($old['status'] ?? 'active'),
            'status_on'               => $date('status_on'),
        ];

        // A consent that is Yes has no withdrawal date; an active status has no date
        foreach (['recording', 'risk_items'] as $kind) {
            if ($row[$kind . '_consent'] === 1) {
                $row[$kind . '_withdrawn_on'] = null;
            }
        }

        if ($row['status'] === 'active') {
            $row['status_on'] = null;
        }

        return $row;
    }

    /**
     * @param array<string, mixed>|null $old
     * @param array<string, mixed>      $new
     */
    private function check(?array $old, array $new, bool $requireParent): ?string
    {
        if (! in_array($new['status'], self::STATUSES, true)) {
            return lang('Admin.consentBadStatus');
        }

        if ($requireParent && $new['parent_consent_on'] === null) {
            return lang('Admin.consentParentRequired');
        }

        $today = date('Y-m-d');

        foreach (['parent_consent_on', 'recording_withdrawn_on', 'risk_items_withdrawn_on', 'status_on'] as $field) {
            $value = $new[$field];

            if ($value === null) {
                continue;
            }

            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) !== 1 || strtotime($value) === false || $value > $today
                || ($field !== 'parent_consent_on' && $new['parent_consent_on'] !== null && $value < $new['parent_consent_on'])) {
                return lang('Admin.consentBadDate');
            }
        }

        foreach (['recording', 'risk_items'] as $kind) {
            $was = (int) ($old[$kind . '_consent'] ?? 0);

            if ($was === 1 && $new[$kind . '_consent'] === 0 && $new[$kind . '_withdrawn_on'] === null) {
                return lang('Admin.consentWithdrawnDate');
            }
        }

        if ($new['status'] !== 'active' && $new['status_on'] === null) {
            return lang('Admin.consentStatusDateRequired');
        }

        return null;
    }

    /** @param array<string, mixed> $metadata */
    private function audit(string $action, int $participantId, ?int $staffId, array $metadata): void
    {
        model(AuditLogModel::class)->record($action, [
            'staff_user_id' => $staffId,
            'target_type'   => 'participant',
            'target_id'     => (string) $participantId,
            'metadata'      => $metadata,
        ]);
    }

    /** Admin turns a deactivated account on again after a wrong entry (30 §5.3). */
    public function reactivate(int $participantId, string $reason, int $staffId): string
    {
        $participant = model(ParticipantModel::class)->find($participantId);
        $reason      = trim($reason);

        if ($participant === null || $participant->deactivated_at === null) {
            return lang('Admin.reactivateNotNeeded');
        }

        if ($reason === '') {
            return lang('Admin.reasonRequired');
        }

        if (in_array($this->forParticipant($participantId)['status'] ?? 'active', self::WITHDRAWN, true)) {
            return lang('Admin.reactivateStatusFirst');
        }

        if (service('studyClosure')->identitiesReplacedFor($participantId)) {
            return lang('Admin.closureReplaced');
        }

        db_connect()->table('participants')->where('id', $participantId)->where('deactivated_at', $participant->deactivated_at)->update(['deactivated_at' => null]);

        if (db_connect()->affectedRows() !== 1) {
            return lang('Admin.consentStale');
        }

        $this->audit('participant_reactivated', $participantId, $staffId, ['participant_code' => $participant->participant_code, 'reason' => mb_substr($reason, 0, 255)]);

        return '';
    }
}
