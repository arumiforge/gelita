<?php

namespace App\Services;

use App\Models\AuditLogModel;
use App\Models\ResearchStudyModel;

/**
 * Follow-up flags of the online-risk items (research revision 10, part 4:
 * 30 §6, K7). Computed when the head researcher opens the page, from the
 * answers to ang-21 (`risk_upset`) and ang-23 (`support_none`); never stored,
 * never exported, never on the monitoring panel. The audit log records the
 * count only, without participant codes.
 *
 * The answers live in the test module's tables (part 8, 34 §8.3). Until they
 * exist the list is empty.
 */
class RiskFollowUpService
{
    public const RULE_VERSION = 'tl-1';

    /** ang-21 codes (16): 1 = once or twice, 2 = several times or more. */
    private const UPSET_FLAG = ['1', '2'];

    private const UPSET_PRIORITY = '2';

    /**
     * Rule `tl-1`: null when not flagged, else whether it comes first.
     *
     * @return array{priority: bool}|null
     */
    public static function evaluate(?string $upset, bool $supportNone): ?array
    {
        if (! in_array((string) $upset, self::UPSET_FLAG, true)) {
            return null;
        }

        return ['priority' => (string) $upset === self::UPSET_PRIORITY || $supportNone];
    }

    /** True when the study is closed: the page shows no flags any more (26 → A7). */
    public function studyClosed(int $studyId): bool
    {
        $study = model(ResearchStudyModel::class)->find($studyId);

        return $study === null || $study['status'] === 'closed';
    }

    /**
     * Flags per field session (P5 and its make-ups), newest first: code,
     * priority, time of the last answered item. The answers themselves are
     * never returned.
     *
     * @return list<array{field_session: string, rows: list<array{participant_code: string, priority: bool, last_answered_at: ?string}>}>
     */
    public function flags(int $studyId, ?int $fieldSessionId = null): array
    {
        $db = db_connect();

        if ($this->studyClosed($studyId) || ! $db->tableExists('instrument_sittings') || ! $db->tableExists('instrument_responses')) {
            return [];
        }

        $builder = $db->table('instrument_responses r')
            ->select('s.id AS sitting_id, p.participant_code, fs.code AS field_session_code, fs.id AS field_session_id, r.unit, r.response, r.answered_at')
            ->join('instrument_sittings s', 's.id = r.sitting_id')
            ->join('participants p', 'p.id = s.participant_id')
            ->join('field_sessions fs', 'fs.id = s.field_session_id', 'left')
            ->where('s.study_id', $studyId)->where('s.kind', 'risk')->where('s.is_preview', 0)
            ->whereIn('r.unit', ['risk_upset', 'support_none']);

        if ($fieldSessionId !== null) {
            $builder->where('s.field_session_id', $fieldSessionId);
        }

        $sittings = [];

        foreach ($builder->get()->getResultArray() as $row) {
            $id                  = (int) $row['sitting_id'];
            $sittings[$id]     ??= ['code' => $row['participant_code'], 'session' => (string) ($row['field_session_code'] ?? '—'), 'order' => (int) ($row['field_session_id'] ?? 0), 'upset' => null, 'none' => false, 'last' => null];
            $sittings[$id]['upset'] = $row['unit'] === 'risk_upset' ? $row['response'] : $sittings[$id]['upset'];
            $sittings[$id]['none']  = $sittings[$id]['none'] || ($row['unit'] === 'support_none' && (string) $row['response'] === '1');
            $sittings[$id]['last']  = max((string) $sittings[$id]['last'], (string) $row['answered_at']) ?: null;
        }

        $groups = [];

        foreach ($sittings as $sitting) {
            $flag = self::evaluate($sitting['upset'], $sitting['none']);

            if ($flag === null) {
                continue;
            }

            $groups[$sitting['order']] ??= ['field_session' => $sitting['session'], 'rows' => []];
            $groups[$sitting['order']]['rows'][] = ['participant_code' => $sitting['code'], 'priority' => $flag['priority'], 'last_answered_at' => $sitting['last']];
        }

        krsort($groups);

        return array_values($groups);
    }

    /** Ringkasan card: flags from today's P5 sessions. */
    public function todayCount(int $studyId): int
    {
        $today = date('Y-m-d');
        $count = 0;

        foreach ($this->flags($studyId) as $group) {
            $count += count(array_filter($group['rows'], static fn (array $r): bool => str_starts_with((string) $r['last_answered_at'], $today)));
        }

        return $count;
    }

    /** `risk_flags_viewed` with the count only (30 §6). */
    public function auditView(array $groups, int $staffId): void
    {
        foreach ($groups === [] ? [['field_session' => null, 'rows' => []]] : $groups as $group) {
            model(AuditLogModel::class)->record('risk_flags_viewed', [
                'staff_user_id' => $staffId,
                'target_type'   => 'field_session',
                'metadata'      => ['field_session_code' => $group['field_session'], 'count' => count($group['rows']), 'rule' => self::RULE_VERSION],
            ]);
        }
    }
}
