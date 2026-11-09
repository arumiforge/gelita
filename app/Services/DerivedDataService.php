<?php

namespace App\Services;

use App\Libraries\BankRules;
use App\Libraries\Derived\Clock;
use App\Libraries\Derived\ParticipantRun;
use App\Libraries\Derived\RunInput;
use App\Libraries\Derived\Thresholds;
use App\Libraries\ErrorCodeResolver;
use App\Libraries\ResearchDictionary;
use App\Libraries\Simulation\Simulator;
use App\Models\AuditLogModel;
use App\Models\ChallengeItemModel;
use App\Models\DerivedRunModel;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\BaseConnection;

/**
 * The derived data layer (research revision 10, part 9:
 * docs/revisi-10-analitik/35_IMPLEMENTASI_LAPIS_DATA_TURUNAN.md).
 *
 * Loads the raw data of one student at a time (RunInput), lets the pure
 * calculators (App\Libraries\Derived) compute every derived row, writes the
 * rows of a new run (`derived_runs`, `derived_*`), and then switches
 * `research_studies.derived_run_id` with a conditional UPDATE on the old
 * value (K7), so readers never see a half-written run.
 *
 * - Only studies with field mode on (K9); test accounts (`uji.*`, school
 *   V01), simulated students (`sim_*`), deleted students, and
 *   `withdrawn_delete` never enter (K13, §7).
 * - One run at a time per study: the run claims the study through a
 *   conditional UPDATE of `derived_running_since` (empty or older than
 *   CLAIM_MINUTES); the loser does not compute (§9).
 * - A run is stale when the input fingerprint (§4.3) changed; a download
 *   recomputes a stale study first (`ensureFresh()`, K3).
 * - A failed run keeps the old run current and says why (`failed`).
 */
class DerivedDataService
{
    public const CLAIM_MINUTES = 30;

    /** Seconds a download waits for a run another request is computing (§9). */
    public const WAIT_SECONDS = 120;

    /** Rows per insert batch. */
    public const BATCH = 500;

    /** Derived tables, children of a run (RetentionService::DERIVED_TABLES). */
    public const TABLES = RetentionService::DERIVED_TABLES;

    /** @var array<string, mixed>|null content shared by every student of a run */
    private ?array $content = null;

    /** @var array<int, \App\Entities\ChallengeItem|null> */
    private array $entities = [];

    /** Test hook: called after each student's rows are written (e.g. to simulate a failure). */
    public ?\Closure $afterParticipant = null;

    // ------------------------------------------------------------ status

    /**
     * What the panel shows (§10.4, §14): field mode, the current and newest
     * run, whether the current run is stale, and whether a run is computing.
     *
     * @return array{field_mode: bool, current: ?array, latest: ?array, stale: bool, running: bool, draft: bool}
     */
    public function status(int $studyId): array
    {
        $study   = $this->study($studyId);
        $current = $study !== null && $study['derived_run_id'] !== null ? model(DerivedRunModel::class)->find((int) $study['derived_run_id']) : null;
        $latest  = model(DerivedRunModel::class)->latestFor($studyId);
        $field   = $study !== null && (int) $study['field_mode'] === 1;

        return [
            'field_mode' => $field,
            'current'    => $current,
            'latest'     => $latest,
            'stale'      => $field && ($current === null || (string) $current['input_fingerprint'] !== $this->fingerprint($studyId)),
            'running'    => $study !== null && $this->claimHeld($study),
            'draft'      => ResearchDictionary::DRAFT,
        ];
    }

    public function isStale(int $studyId): bool
    {
        return $this->status($studyId)['stale'];
    }

    /**
     * Fingerprint of the raw data a run reads (§4.3, §19 no. 10): counts and
     * newest ids of the rows only added, `MAX(updated_at)` of the rows that
     * change, the field-sheet imports, the thresholds, and the dictionary
     * version. `challenge_attempts` has no `updated_at`: its newest
     * `completed_at`, id, and count stand in. A deletion changes a count, so a
     * run after a deletion is always stale.
     */
    public function fingerprint(int $studyId): string
    {
        $db       = $this->db();
        $study    = $this->study($studyId) ?? [];
        $sessions = static fn (BaseBuilder $sub): BaseBuilder => $sub->select('id')->from('game_sessions')->where('study_id', $studyId);
        $attempts = static fn (BaseBuilder $sub): BaseBuilder => $sub->select('id')->from('challenge_attempts')->whereIn('session_id', $sessions);
        $people   = $this->participantIds($studyId);
        $one      = static fn (BaseBuilder $b, string $select): array => array_values((array) $b->select($select, false)->get()->getRowArray());
        $parts    = [];

        $parts[] = $one($db->table('item_tries')->whereIn('challenge_attempt_id', $attempts), 'COUNT(*) AS n, MAX(id) AS m');
        $parts[] = $one($db->table('game_event_logs')->whereIn('session_id', $sessions)->where('deleted_at', null), 'COUNT(*) AS n, MAX(id) AS m');
        $parts[] = $one($db->table('audio_usage_events')->whereIn('session_id', $sessions), 'COUNT(*) AS n, MAX(id) AS m');
        $parts[] = $one($db->table('challenge_attempts')->whereIn('session_id', $sessions), 'COUNT(*) AS n, MAX(id) AS m, MAX(completed_at) AS c, SUM(is_retake) AS r');
        $parts[] = $one($db->table('challenge_attempt_segments')->whereIn('session_id', $sessions), 'COUNT(*) AS n, MAX(id) AS m, MAX(left_at) AS l');
        $parts[] = $one($db->table('game_sessions')->where('study_id', $studyId), 'COUNT(*) AS n, MAX(id) AS m, MAX(field_session_id) AS f');
        $parts[] = $one($db->table('participant_consents')->where('study_id', $studyId), 'COUNT(*) AS n, MAX(updated_at) AS u');
        $parts[] = $one($db->table('participant_seats')->where('study_id', $studyId), 'COUNT(*) AS n, MAX(updated_at) AS u');
        $parts[] = $people === [] ? [0] : $one($db->table('participants')->whereIn('id', $people), 'COUNT(*) AS n, MAX(updated_at) AS u, MAX(deactivated_at) AS d, MAX(corrected_at) AS c');
        $parts[] = $one($db->table('instrument_sittings')->where('study_id', $studyId), 'COUNT(*) AS n, MAX(updated_at) AS u');
        $parts[] = $one($db->table('instrument_responses')->whereIn('sitting_id', static fn (BaseBuilder $sub): BaseBuilder => $sub->select('id')->from('instrument_sittings')->where('study_id', $studyId)), 'COUNT(*) AS n, MAX(updated_at) AS u');
        $parts[] = $one($db->table('field_sessions')->where('study_id', $studyId), 'COUNT(*) AS n, MAX(updated_at) AS u, MAX(closed_at) AS c');
        $parts[] = $one($db->table('field_sheet_imports')->whereIn('field_session_id', static fn (BaseBuilder $sub): BaseBuilder => $sub->select('id')->from('field_sessions')->where('study_id', $studyId)), 'COUNT(*) AS n, MAX(id) AS m, MAX(superseded_at) AS s');
        // The latest bank codes are used (35 §19 no. 5): a new bank import makes the run stale
        $parts[] = $one($db->table('item_error_codes'), 'COUNT(*) AS n, MAX(id) AS m');
        $parts[] = [(int) ($study['idle_threshold_ms'] ?? 0), (int) ($study['rapid_guess_ms'] ?? 0), (int) ($study['test_rapid_guess_ms'] ?? 0), ResearchDictionary::VERSION];

        return hash('sha256', json_encode($parts));
    }

    // ------------------------------------------------------------ runs

    /**
     * A current run for a download (K3): null when field mode is off; the
     * current run when fresh; else a new run, waiting for one another request
     * is computing.
     *
     * @throws \DomainException when the run fails or another one keeps running
     */
    public function ensureFresh(int $studyId, ?int $staffId = null, string $via = 'export'): ?int
    {
        $status = $this->status($studyId);

        if (! $status['field_mode']) {
            return null;
        }

        if (! $status['stale'] && $status['current'] !== null) {
            return (int) $status['current']['id'];
        }

        $waited = 0;

        do {
            $result = $this->compute($studyId, $staffId, $via);

            if ($result['status'] === 'done') {
                return (int) $result['run']['id'];
            }

            if ($result['status'] === 'failed') {
                throw new \DomainException(lang('Admin.derivedFailed', [$result['run']['error'] ?? '']));
            }

            // Busy: another request computes; wait for it, then check again (§9)
            sleep(2);
            $waited += 2;
            $status = $this->status($studyId);

            if (! $status['running'] && ! $status['stale'] && $status['current'] !== null) {
                return (int) $status['current']['id'];
            }
        } while ($waited < self::WAIT_SECONDS);

        throw new \DomainException(lang('Admin.derivedRunning'));
    }

    /**
     * One new run of a study (§9): `done` (with the run), `failed` (the old run
     * stays current), `busy` (another run holds the claim), or `off` (field
     * mode is off).
     *
     * @return array{status: string, run: ?array}
     */
    public function compute(int $studyId, ?int $staffId = null, string $via = 'command'): array
    {
        $study = $this->study($studyId);

        if ($study === null || (int) $study['field_mode'] !== 1) {
            return ['status' => 'off', 'run' => null];
        }

        $claim = $this->now();

        if (! $this->claim($studyId, $claim)) {
            return ['status' => 'busy', 'run' => null];
        }

        $runs  = model(DerivedRunModel::class);
        $limit = Thresholds::fromStudy($study);
        $runId = (int) $runs->insert([
            'study_id'            => $studyId,
            'status'              => 'running',
            'dictionary_version'  => ResearchDictionary::VERSION,
            'idle_threshold_ms'   => $limit->idleMs,
            'rapid_guess_ms'      => $limit->rapidMs,
            'test_rapid_guess_ms' => $limit->testRapidMs,
            'input_fingerprint'   => $this->fingerprint($studyId),
            'started_by'          => $staffId,
            'via'                 => in_array($via, DerivedRunModel::VIA, true) ? $via : 'command',
            'started_at'          => $claim,
        ], true);

        $clock = microtime(true);
        $notes = ['offset_unknown_events' => 0];

        try {
            $this->content = null;
            $people        = $this->participantIds($studyId);

            foreach ($people as $participantId) {
                $input = $this->load($studyId, $participantId, $notes);
                $this->write($runId, ParticipantRun::compute($input, $limit));

                if ($this->afterParticipant !== null) {
                    ($this->afterParticipant)($participantId);
                }
            }

            $notes['duration_ms'] = (int) round((microtime(true) - $clock) * 1000);
            $runs->update($runId, ['status' => 'done', 'finished_at' => $this->now(), 'participant_count' => count($people), 'notes_json' => json_encode($notes)]);

            if (! $this->switchRun($studyId, $study['derived_run_id'] === null ? null : (int) $study['derived_run_id'], $runId)) {
                throw new \RuntimeException('The current run changed while computing.');
            }
        } catch (\Throwable $e) {
            log_message('error', 'Derived run {run} of study {study} failed: {msg}', ['run' => $runId, 'study' => $studyId, 'msg' => $e->getMessage()]);
            $runs->update($runId, ['status' => 'failed', 'finished_at' => $this->now(), 'error' => mb_substr($e->getMessage(), 0, 1000)]);
            $this->release($studyId, $claim);

            return ['status' => 'failed', 'run' => $runs->find($runId)];
        }

        $this->release($studyId, $claim);
        $this->pruneRuns($studyId);

        return ['status' => 'done', 'run' => $runs->find($runId)];
    }

    /**
     * Claim a study for one run (§9): a conditional UPDATE that only wins
     * while nobody holds the claim or the holder is older than CLAIM_MINUTES.
     */
    public function claim(int $studyId, string $at): bool
    {
        $stale = date('Y-m-d H:i:s', time() - self::CLAIM_MINUTES * 60);

        $this->db()->table('research_studies')->where('id', $studyId)
            ->groupStart()->where('derived_running_since', null)->orWhere('derived_running_since <', $stale)->groupEnd()
            ->update(['derived_running_since' => substr($at, 0, 19)]);

        return $this->db()->affectedRows() === 1;
    }

    /** The current run becomes $new, only while it is still $expected (K7). */
    public function switchRun(int $studyId, ?int $expected, int $new): bool
    {
        $this->db()->table('research_studies')->where('id', $studyId)->where('derived_run_id', $expected)->update(['derived_run_id' => $new]);

        return $this->db()->affectedRows() === 1;
    }

    private function release(int $studyId, string $at): void
    {
        $this->db()->table('research_studies')->where('id', $studyId)->where('derived_running_since', substr($at, 0, 19))->update(['derived_running_since' => null]);
    }

    private function claimHeld(array $study): bool
    {
        return $study['derived_running_since'] !== null && (string) $study['derived_running_since'] >= date('Y-m-d H:i:s', time() - self::CLAIM_MINUTES * 60);
    }

    /**
     * Removes runs nobody reads (§9): not current, not used by a download
     * whose file is still there, not running, and not the study's newest run
     * (kept for its status). Their rows go first (the FK cascades on MySQL too).
     *
     * @return int runs removed
     */
    public function pruneRuns(?int $studyId = null): int
    {
        $db   = $this->db();
        $keep = array_map('intval', array_filter([
            ...array_column($db->table('research_studies')->select('derived_run_id')->where('derived_run_id IS NOT NULL')->get()->getResultArray(), 'derived_run_id'),
            ...array_column($db->table('data_exports')->select('derived_run_id')->where('derived_run_id IS NOT NULL')->where('file_path IS NOT NULL')->get()->getResultArray(), 'derived_run_id'),
            ...array_column($db->table('derived_runs')->select('MAX(id) AS id', false)->groupBy('study_id')->get()->getResultArray(), 'id'),
        ]));

        $builder = $db->table('derived_runs')->select('id')->where('status !=', 'running');

        if ($studyId !== null) {
            $builder->where('study_id', $studyId);
        }

        if ($keep !== []) {
            $builder->whereNotIn('id', $keep);
        }

        $ids = array_map('intval', array_column($builder->get()->getResultArray(), 'id'));

        foreach (array_chunk($ids, 200) as $chunk) {
            foreach (self::TABLES as $table) {
                $db->table($table)->whereIn('run_id', $chunk)->delete();
            }

            $db->table('derived_runs')->whereIn('id', $chunk)->delete();
        }

        return count($ids);
    }

    /**
     * Unlocks the thresholds after G4 (35 §5, §13: `gelita:turunan:ambang
     * --buka`), only from the command line with a reason; audit
     * `study_thresholds_unlocked`.
     */
    public function unlockThresholds(int $studyId, string $reason): bool
    {
        $this->db()->table('research_studies')->where('id', $studyId)->where('thresholds_locked_at IS NOT NULL')->update(['thresholds_locked_at' => null]);

        if ($this->db()->affectedRows() !== 1) {
            return false;
        }

        model(AuditLogModel::class)->record('study_thresholds_unlocked', [
            'target_type' => 'research_study',
            'target_id'   => (string) $studyId,
            'metadata'    => ['reason' => $reason, 'via' => 'command'],
        ]);

        return true;
    }

    // ------------------------------------------------------------ students

    /**
     * Students of a study (K13): with a game session, a consent row, or a
     * seat in it; never deleted, test or simulated accounts, or `withdrawn_delete`.
     *
     * @return list<int>
     */
    public function participantIds(int $studyId): array
    {
        $db  = $this->db();
        $ids = [];

        foreach ([['game_sessions', 'participant_id'], ['participant_consents', 'participant_id'], ['participant_seats', 'participant_id']] as [$table, $column]) {
            foreach ($db->table($table)->select($column)->distinct()->where('study_id', $studyId)->get()->getResultArray() as $row) {
                $ids[(int) $row[$column]] = true;
            }
        }

        if ($ids === []) {
            return [];
        }

        $test = array_map('intval', array_column($db->table('schools')->select('id')->where('research_code', TestAccountService::SCHOOL_CODE)->get()->getResultArray(), 'id')) ?: [0];

        $rows = $db->table('participants')
            ->select('participants.id')
            ->join('participant_consents', 'participant_consents.participant_id = participants.id', 'left')
            ->whereIn('participants.id', array_keys($ids))
            ->where('participants.deleted_at', null)
            ->notLike('participants.username', Simulator::USERNAME_PREFIX, 'after')
            ->notLike('participants.username', TestAccountService::PREFIX, 'after')
            ->groupStart()->where('participants.school_id', null)->orWhereNotIn('participants.school_id', $test)->groupEnd()
            ->groupStart()->where('participant_consents.status', null)->orWhere('participant_consents.status !=', 'withdrawn_delete')->groupEnd()
            ->orderBy('participants.id', 'ASC')
            ->get()->getResultArray();

        return array_map('intval', array_column($rows, 'id'));
    }

    // ------------------------------------------------------------ loading

    /**
     * The raw data of one student in one study (35 §4.2).
     *
     * @param array<string, int> $notes run notes (events without a known offset are counted)
     */
    public function load(int $studyId, int $participantId, array &$notes = []): RunInput
    {
        $db      = $this->db();
        $content = $this->content($studyId);
        $p       = $db->table('participants')
            ->select('participants.*, schools.research_code AS school_code, participant_seats.booklet, participant_seats.seat_no AS study_seat')
            ->join('schools', 'schools.id = participants.school_id', 'left')
            ->join('participant_seats', 'participant_seats.participant_id = participants.id AND participant_seats.study_id = ' . $studyId, 'left')
            ->where('participants.id', $participantId)->get()->getRowArray();
        $consent = $db->table('participant_consents')->where('participant_id', $participantId)->where('study_id', $studyId)->get()->getRowArray();

        $sessions = [];

        foreach ($db->table('game_sessions')->where('participant_id', $participantId)->where('study_id', $studyId)->get()->getResultArray() as $s) {
            $sessions[(int) $s['id']] = [
                'id' => (int) $s['id'], 'field_session_id' => $s['field_session_id'] === null ? null : (int) $s['field_session_id'],
                'meeting_no' => $s['meeting_no'] === null ? null : (int) $s['meeting_no'], 'started_at' => $s['started_at'],
                'last_active_at' => $s['last_active_at'], 'ended_at' => $s['ended_at'], 'locale' => (string) $s['locale'],
            ];
        }

        $sessionIds = array_keys($sessions) ?: [0];
        $attempts   = [];

        foreach ($db->table('challenge_attempts')->whereIn('session_id', $sessionIds)->get()->getResultArray() as $a) {
            $attempts[(int) $a['id']] = [
                'id' => (int) $a['id'], 'session_id' => (int) $a['session_id'], 'node_id' => (int) $a['challenge_node_id'],
                'attempt_no' => (int) $a['attempt_no'], 'variant_group' => $a['variant_group'], 'status' => (string) $a['status'],
                'started_at' => $a['started_at'], 'completed_at' => $a['completed_at'], 'is_retake' => (int) ($a['is_retake'] ?? 0),
                'hint_count' => (int) $a['hint_count'],
            ];
        }

        $attemptIds = array_keys($attempts) ?: [0];
        $segments   = [];

        foreach ($db->table('challenge_attempt_segments')->whereIn('challenge_attempt_id', $attemptIds)->get()->getResultArray() as $s) {
            $segments[] = [
                'attempt_id' => (int) $s['challenge_attempt_id'], 'session_id' => (int) $s['session_id'],
                'field_session_id' => $s['field_session_id'] === null ? null : (int) $s['field_session_id'],
                'segment_no' => (int) $s['segment_no'], 'opened_at' => $s['opened_at'], 'left_at' => $s['left_at'],
            ];
        }

        $tries = [];

        foreach ($db->table('item_tries')->whereIn('challenge_attempt_id', $attemptIds)->get()->getResultArray() as $t) {
            $attempt = $attempts[(int) $t['challenge_attempt_id']];
            $answer  = json_decode((string) $t['answer_json'], true) ?: [];
            $tries[(int) $t['id']] = [
                'id' => (int) $t['id'], 'attempt_id' => (int) $t['challenge_attempt_id'], 'item_id' => (int) $t['challenge_item_id'],
                'try_no' => (int) $t['try_no'], 'is_correct' => (int) $t['is_correct'], 'error_code' => $t['error_code'],
                'error_code_latest' => (int) $t['is_correct'] === 1 ? null : $this->latestCode((int) $t['challenge_item_id'], $answer, $sessions[$attempt['session_id']]['locale'] ?? 'id', $t),
                'answer_hash' => self::answerHash($answer),
                'ready_ms' => $t['ready_ms'], 'first_input_ms' => $t['first_input_ms'], 'last_input_ms' => $t['last_input_ms'],
                'occurred_at' => $t['occurred_at'], 'hint_opened_before' => (int) $t['hint_opened_before'],
                'action_key' => $t['action_key'], 'action_score' => $t['action_score'], 'reason_code' => $t['reason_code'],
                'reason_ready_ms' => $t['reason_ready_ms'], 'action_last_input_ms' => $t['action_last_input_ms'], 'reason_last_input_ms' => $t['reason_last_input_ms'],
            ];
        }

        $events = [];

        foreach ($db->table('game_event_logs')->whereIn('session_id', $sessionIds)->where('deleted_at', null)->orderBy('id', 'ASC')->get()->getResultArray() as $e) {
            $time = RawDataService::correctedTime($e);
            $notes['offset_unknown_events'] = ($notes['offset_unknown_events'] ?? 0) + ($time['offset_known'] === 1 ? 0 : 1);
            $events[] = [
                'id' => (int) $e['id'], 'session_id' => (int) $e['session_id'], 'type' => (string) $e['event_type'], 'at' => $time['occurred_at_corrected'],
                'offset_known' => $time['offset_known'], 'attempt_id' => $e['challenge_attempt_id'] === null ? null : (int) $e['challenge_attempt_id'],
                'item_id' => $e['challenge_item_id'] === null ? null : (int) $e['challenge_item_id'],
                'payload' => json_decode((string) $e['payload_json'], true) ?: [],
            ];
        }

        $audio = [];

        foreach ($db->table('audio_usage_events')->select('audio_usage_events.*, audio_assets.context_code')
            ->join('audio_assets', 'audio_assets.id = audio_usage_events.audio_asset_id', 'left')
            ->whereIn('session_id', $sessionIds)->get()->getResultArray() as $a) {
            $audio[] = [
                'session_id' => (int) $a['session_id'], 'attempt_id' => $a['challenge_attempt_id'] === null ? null : (int) $a['challenge_attempt_id'],
                'audio_asset_id' => (int) $a['audio_asset_id'], 'action' => (string) $a['action'], 'play_index' => (int) $a['play_index'],
                'at' => RawDataService::correctedTime($a)['occurred_at_corrected'], 'listened_ms' => $a['listened_ms'], 'context' => $a['context_code'],
            ];
        }

        // Field sessions of the student: their class group's main sessions, make-ups they belong to, and every one attended
        $attended = array_values(array_filter(array_map(static fn (array $s): ?int => $s['field_session_id'], $sessions)));
        $members  = array_map('intval', array_column($db->table('field_session_members')->select('field_session_id')->where('participant_id', $participantId)->get()->getResultArray(), 'field_session_id'));
        $fsRows   = $db->table('field_sessions')->where('study_id', $studyId)
            ->groupStart()
            ->groupStart()->where('school_id', $p['school_id'] ?? 0)->where('rombel', $p['rombel'] ?? '')->where('makeup_no', 0)->groupEnd()
            ->orWhereIn('id', [...$attended, ...$members, 0])
            ->groupEnd()->get()->getResultArray();
        $fieldSessions = [];

        foreach ($fsRows as $fs) {
            $regions = service('fieldSessions')->planEntry($fs, $content['study']);
            $fieldSessions[(int) $fs['id']] = [
                'id' => (int) $fs['id'], 'meeting_no' => (int) $fs['meeting_no'], 'makeup_no' => (int) $fs['makeup_no'], 'date' => substr((string) $fs['session_date'], 0, 10),
                'opens_at' => $fs['opens_at'], 'closes_at' => $fs['closes_at'], 'closed_at' => $fs['closed_at'],
                'regions' => array_values(array_filter(array_map(static fn (string $code): ?int => $content['levels'][$code] ?? null, $regions['regions']))),
            ];
        }

        // Field sheets (29 §10.4): main imports not superseded
        $fsIds    = array_keys($fieldSessions) ?: [0];
        $imported = [];
        $rows     = [];
        $seats    = [];

        foreach ($db->table('field_sheet_rows')->select('field_sheet_rows.*, field_sheet_imports.sheet, field_sheet_imports.field_session_id')
            ->join('field_sheet_imports', 'field_sheet_imports.id = field_sheet_rows.import_id')
            ->whereIn('field_sheet_imports.field_session_id', $fsIds)->where('field_sheet_imports.purpose', 'main')->where('field_sheet_imports.superseded_at', null)
            ->get()->getResultArray() as $r) {
            $mine = (int) ($r['participant_id'] ?? 0) === $participantId;

            if ($r['sheet'] === 'l2_kursi' && ! $mine) {
                continue;
            }

            if ($r['sheet'] === 'l2_kursi' && $r['seat_no'] !== null) {
                $seats[(int) $r['field_session_id']] = (int) $r['seat_no'];
            }

            $rows[] = [
                'id' => (int) $r['id'], 'field_session_id' => (int) $r['field_session_id'], 'sheet' => (string) $r['sheet'], 'mine' => $mine,
                'seat_no' => $r['seat_no'] === null ? null : (int) $r['seat_no'], 'started_at' => $r['started_at'], 'ended_at' => $r['ended_at'],
                'values' => json_decode((string) $r['values_json'], true) ?: [],
            ];
        }

        foreach ($db->table('field_sheet_imports')->select('field_session_id, sheet')->whereIn('field_session_id', $fsIds)->where('purpose', 'main')->where('superseded_at', null)->get()->getResultArray() as $i) {
            $imported[(int) $i['field_session_id']][] = (string) $i['sheet'];
        }

        foreach (array_keys($fieldSessions) as $fsId) {
            if (! isset($seats[$fsId]) && ($p['study_seat'] ?? null) !== null) {
                $seats[$fsId] = (int) $p['study_seat'];
            }
        }

        return RunInput::fromArray([
            'participant' => [
                'id' => $participantId, 'code' => (string) $p['participant_code'], 'school_code' => $p['school_code'], 'booklet' => $p['booklet'],
                'deactivated_at' => $p['deactivated_at'], 'assented_at' => $p['assented_at'], 'corrected_at' => $p['corrected_at'],
            ],
            'consent'        => $consent === null ? null : ['parent_consent_on' => $consent['parent_consent_on'], 'status' => (string) $consent['status'], 'status_on' => $consent['status_on']],
            'nodes'          => $content['nodes'],
            'items'          => $content['items'],
            'itemAudio'      => $content['itemAudio'],
            'hintItems'      => $content['hintItems'],
            'fieldSessions'  => $fieldSessions,
            'sessions'       => $sessions,
            'attempts'       => $attempts,
            'segments'       => $segments,
            'tries'          => $tries,
            'events'         => $events,
            'audio'          => $audio,
            'fieldRows'      => $rows,
            'importedSheets' => $imported,
            'seats'          => $seats,
            'sittings'       => $this->sittings($studyId, $participantId, $content),
            'questionnaireItems' => $content['questionnaire'],
            'asOf'           => Clock::ms(date('Y-m-d H:i:s')),
        ]);
    }

    /** Test, questionnaire, and attitude sittings (34 §11.1); never preview or risk sittings. */
    private function sittings(int $studyId, int $participantId, array $content): array
    {
        $db  = $this->db();
        $out = [];

        foreach ($db->table('instrument_sittings')->where('participant_id', $participantId)->where('study_id', $studyId)
            ->where('is_preview', 0)->whereIn('kind', ['test', 'questionnaire', 'attitude'])->orderBy('id', 'ASC')->get()->getResultArray() as $s) {
            $parts = [];

            foreach ($db->table('instrument_parts')->where('sitting_id', (int) $s['id'])->get()->getResultArray() as $part) {
                $parts[(string) $part['part']] = ['raw_score' => $part['raw_score'], 'submitted_at' => $part['submitted_at'], 'closed_at' => $part['closed_at']];
            }

            $blocks    = array_column($db->table('instrument_blocks')->select('block, closed_reason')->where('sitting_id', (int) $s['id'])->get()->getResultArray(), 'closed_reason', 'block');
            $responses = [];

            foreach ($db->table('instrument_responses')->where('sitting_id', (int) $s['id'])->orderBy('id', 'ASC')->get()->getResultArray() as $r) {
                $item = $content['instrumentItems'][(int) $r['item_id']] ?? null;

                // The examples (tes-ct-*, skp-ct-01) are never analysed (34 §11.4)
                if ($s['kind'] === 'test' && ($item === null || $item['part'] === 'ct')) {
                    continue;
                }

                $responses[] = [
                    'id' => (int) $r['id'], 'unit' => (string) $r['unit'], 'item_code' => $item['code'] ?? null, 'response' => $r['response'],
                    'not_reached' => (int) $r['not_reached'], 'last_view_ms' => $r['last_view_ms'], 'first_shown_at' => $r['first_shown_at'],
                    'after_stimulus' => $item !== null && $item['first_after_stimulus'],
                ];
            }

            $out[] = [
                'id' => (int) $s['id'], 'occasion' => (string) $s['occasion'], 'kind' => (string) $s['kind'], 'started_at' => $s['started_at'],
                'submitted_at' => $s['submitted_at'], 'field_session_id' => $s['field_session_id'], 'parts' => $parts, 'blocks' => $blocks, 'responses' => $responses,
            ];
        }

        return $out;
    }

    /** Content every student of the run shares: nodes, scored items, narration and hint links, instrument items. */
    private function content(int $studyId): array
    {
        if ($this->content !== null) {
            return $this->content;
        }

        $db     = $this->db();
        $levels = array_map('intval', array_column($db->table('levels')->select('code, sequence')->get()->getResultArray(), 'sequence', 'code'));
        $nodes  = [];

        foreach ($db->table('challenge_nodes')->select('challenge_nodes.*, levels.sequence AS level_seq')->join('levels', 'levels.id = challenge_nodes.level_id')
            ->where('challenge_nodes.is_active', 1)->get()->getResultArray() as $n) {
            $nodes[(int) $n['id']] = ['id' => (int) $n['id'], 'level_seq' => (int) $n['level_seq'], 'sequence' => (int) $n['sequence'], 'engine' => (string) $n['engine_type']];
        }

        $items     = [];
        $itemAudio = [];
        $parts     = [];

        foreach ($db->table('post_parts')->select('post_parts.*, post_scenes.challenge_node_id, post_scenes.variant_group')->join('post_scenes', 'post_scenes.id = post_parts.post_scene_id')->get()->getResultArray() as $part) {
            $parts[$part['challenge_node_id'] . ':' . $part['variant_group'] . ':' . strtoupper((string) $part['part_key'])] = [(float) $part['x'], (float) $part['y'], (float) $part['w'], (float) $part['h']];
        }

        foreach ($db->table('challenge_items')->where('is_active', 1)->whereIn('challenge_node_id', array_keys($nodes) ?: [0])->get()->getResultArray() as $i) {
            $config = json_decode((string) $i['config_json'], true) ?: [];
            $key    = json_decode((string) $i['answer_key_json'], true) ?: [];

            foreach (['audio_prompt_id', 'audio_prompt_en_id'] as $column) {
                if ($i[$column] !== null) {
                    $itemAudio[(int) $i[$column]] = (int) $i['id'];
                }
            }

            // Scored items only (11 → Butir dan skor per dimensi): no Susun Gambar, no decoy objects
            if ((int) $i['scorable'] !== 1 || ! isset(BankRules::DIMENSION_OF_TYPE[$i['interaction_type']]) || ! empty($config['decoy'])) {
                continue;
            }

            $box = match ($i['interaction_type']) {
                'find_object' => isset($config['x'], $config['y'], $config['w']) ? BankRules::box($config) : null,
                'find_part'   => $parts[$i['challenge_node_id'] . ':' . $i['variant_group'] . ':' . strtoupper((string) ($key['part'] ?? ''))] ?? null,
                default       => null,
            };

            $items[(int) $i['id']] = [
                'id' => (int) $i['id'], 'item_key' => (string) $i['item_key'], 'node_id' => (int) $i['challenge_node_id'],
                'variant_group' => (string) $i['variant_group'], 'interaction_type' => (string) $i['interaction_type'],
                'dimension' => $i['dimension'], 'scorable' => 1, 'key_box' => $box,
            ];
        }

        $hintItems = array_map('intval', array_column($db->table('hints')->select('id, challenge_item_id')->where('challenge_item_id IS NOT NULL')->get()->getResultArray(), 'challenge_item_id', 'id'));

        // Instrument items: code, part, and whether the item is the first one after its passage (35 §6.8)
        $instrumentItems = [];
        $firsts          = [];

        foreach ($db->table('instrument_items')->orderBy('position', 'ASC')->get()->getResultArray() as $item) {
            $group = $item['stimulus_id'] === null ? null : $item['version_id'] . ':' . $item['form'] . ':' . $item['stimulus_id'];
            $first = $group !== null && ! isset($firsts[$group]);

            if ($group !== null) {
                $firsts[$group] = true;
            }

            $instrumentItems[(int) $item['id']] = ['code' => (string) $item['code'], 'part' => (string) $item['part'], 'first_after_stimulus' => $first, 'units' => json_decode((string) $item['units_json'], true) ?: [], 'version_id' => (int) $item['version_id']];
        }

        $questionnaire = [];
        $version       = $db->table('instrument_versions')->where('instrument', 'angket')->where('is_active', 1)->orderBy('id', 'DESC')->get(1)->getRowArray();

        foreach ($instrumentItems as $item) {
            if ($version !== null && $item['version_id'] === (int) $version['id'] && $item['part'] !== 'risk') {
                foreach ($item['units'] as $unit) {
                    $questionnaire[(string) $unit] = $item['code'];
                }
            }
        }

        return $this->content = [
            'study' => $this->study($studyId), 'levels' => $levels, 'nodes' => $nodes, 'items' => $items, 'itemAudio' => $itemAudio,
            'hintItems' => $hintItems, 'instrumentItems' => $instrumentItems, 'questionnaire' => $questionnaire,
        ];
    }

    /**
     * The error code of a wrong try from the bank as it stands now (35 §19
     * no. 5), through part 5's ErrorCodeResolver; the code stored at the try
     * stays as `error_code_at_try`.
     *
     * @param array<string, mixed> $answer
     * @param array<string, mixed> $try    item_tries row
     */
    private function latestCode(int $itemId, array $answer, string $locale, array $try): ?string
    {
        $item = $this->entities[$itemId] ??= model(ChallengeItemModel::class)->find($itemId);

        if ($item === null || $item->interaction_type === 'puzzle_arrange') {
            return null;
        }

        $clicked = null;

        if ($item->interaction_type === 'find_part') {
            $clicked = isset($answer['part']) ? (string) $answer['part'] : null;
        } elseif ($item->interaction_type === 'find_object') {
            $other   = (int) ($answer['item_id'] ?? 0);
            $clicked = $other > 0 ? (($this->entities[$other] ??= model(ChallengeItemModel::class)->find($other))?->item_key) : null;
        } elseif ($item->interaction_type === 'scenario_choice') {
            $clicked = $try['action_key'] ?? ($answer['action'] ?? null);
        }

        try {
            return (new ErrorCodeResolver())->resolve($item, $answer, $locale, $clicked);
        } catch (\Throwable) {
            return $try['error_code'];
        }
    }

    /** Hash of an answer without its timing and display order, to compare two tries on the server (Analitik 9). */
    public static function answerHash(array $answer): string
    {
        unset($answer['order'], $answer['first_input_ms'], $answer['last_input_ms'], $answer['ready_ms'], $answer['duration_ms']);
        ksort($answer);

        return hash('sha256', (string) json_encode($answer));
    }

    // ------------------------------------------------------------ writing

    /** @param array<string, list<array<string, mixed>>> $rows */
    private function write(int $runId, array $rows): void
    {
        $db = $this->db();
        $db->transBegin();

        try {
            foreach ($rows as $table => $list) {
                $list = array_map(static fn (array $row): array => ['run_id' => $runId, 'dictionary_version' => ResearchDictionary::VERSION] + $row, $list);

                foreach (array_chunk($list, self::BATCH) as $chunk) {
                    $db->table($table)->insertBatch($chunk);
                }
            }

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();

            throw $e;
        }
    }

    // ------------------------------------------------------------ basics

    /** @return array<string, mixed>|null */
    private function study(int $studyId): ?array
    {
        return $this->db()->table('research_studies')->where('id', $studyId)->get()->getRowArray();
    }

    /** Now in WIB with milliseconds (the stored form). */
    private function now(): string
    {
        return (new \DateTimeImmutable('now', new \DateTimeZone(Clock::ZONE)))->format('Y-m-d H:i:s.v');
    }

    private function db(): BaseConnection
    {
        return db_connect();
    }
}
