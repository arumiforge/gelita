<?php

namespace App\Services;

use App\Entities\GameSession;
use App\Entities\Participant;
use App\Models\AuditLogModel;
use App\Models\Concerns\ConditionalUpdate;
use App\Models\FieldSessionMemberModel;
use App\Models\FieldSessionModel;
use App\Models\FieldSessionTokenModel;
use App\Models\GameSessionModel;
use App\Models\ResearchStudyModel;
use App\Models\SchoolModel;
use CodeIgniter\Database\BaseBuilder;

/**
 * Field sessions and their tokens (research revision 10, part 2:
 * docs/revisi-10-analitik/28_IMPLEMENTASI_SESI_LAPANGAN.md §5–§7, §9).
 *
 * A field session is one meeting of one class group, opened by a researcher.
 * Its state is derived from its hours (pending → open → closed) and never
 * stored. Everything here only matters while the active study has
 * `field_mode = 1` (off by default, 28 K1, K19).
 *
 * Writes that can race (closing, pausing game sessions) are conditional
 * UPDATEs (ConditionalUpdate), never `SELECT … FOR UPDATE`.
 */
class FieldSessionService
{
    use ConditionalUpdate;

    /** Token letters and digits: no O, 0, I, 1, L (28 K5). */
    public const TOKEN_ALPHABET = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    public const TOKEN_LENGTH = 6;

    /** Tries to find an unused token before giving up. */
    private const TOKEN_TRIES = 5;

    public const STATE_PENDING = 'pending';
    public const STATE_OPEN    = 'open';
    public const STATE_CLOSED  = 'closed';

    /** Token refusal reasons (28 §6.3). */
    public const REASONS = ['token_unknown', 'not_open_yet', 'closed', 'other_class', 'not_member', 'other_study', 'inactive', 'too_many'];

    /** @var \Closure(): string|null test hook: makes the next candidate token */
    private ?\Closure $tokenSource;

    /** @var array<int, list<string>|null> open regions per game session id, for this request */
    private array $regionCache = [];

    private bool $studyLoaded = false;

    /** @var array<string, mixed>|null */
    private ?array $study = null;

    public function __construct(?\Closure $tokenSource = null)
    {
        $this->tokenSource = $tokenSource;
    }

    // ------------------------------------------------------------ field mode

    /**
     * The active study when field mode is on, else null (read once per request).
     *
     * @return array<string, mixed>|null
     */
    public function fieldModeStudy(): ?array
    {
        if (! $this->studyLoaded) {
            $this->study       = model(ResearchStudyModel::class)->fieldModeStudy();
            $this->studyLoaded = true;
        }

        return $this->study;
    }

    public function fieldModeOn(): bool
    {
        return $this->fieldModeStudy() !== null;
    }

    /** Forgets what this request read (after a study or session changed). */
    public function forget(): void
    {
        $this->studyLoaded = false;
        $this->study       = null;
        $this->regionCache = [];
    }

    /** True while a field session of the study is open or not yet open (the field mode switch is locked). */
    public function hasOpenSessions(int $studyId): bool
    {
        $now = $this->now();

        return model(FieldSessionModel::class)
            ->where('study_id', $studyId)
            ->where('closed_at', null)
            ->where('closes_at >', $now)
            ->countAllResults() > 0;
    }

    // ------------------------------------------------------------ plan

    /**
     * What a field session opens, from its study's meeting plan (28 §5.2).
     *
     * @param array<string, mixed>      $fieldSession
     * @param array<string, mixed>|null $study        the field session's study; read when null
     *
     * @return array{register: bool, intro: bool, regions: list<string>, test: ?string, leftovers: bool, ending: bool}
     */
    public function planEntry(array $fieldSession, ?array $study = null): array
    {
        $study ??= model(ResearchStudyModel::class)->find((int) $fieldSession['study_id']);
        $plans   = config('Gelita')->meetingPlans;
        $plan    = $plans[(string) ($study['meeting_plan'] ?? 'lima_pertemuan')] ?? $plans['lima_pertemuan'];
        $entry   = $plan[(int) $fieldSession['meeting_no']] ?? [];
        $regions = $entry['regions'] ?? [];

        if ($regions === 'chosen') {
            $regions = ($fieldSession['region_code'] ?? '') === '' ? [] : [(string) $fieldSession['region_code']];
        }

        return [
            'register'  => ! empty($entry['register']),
            'intro'     => ! empty($entry['intro']),
            'regions'   => array_values(array_map('strval', (array) $regions)),
            'test'      => isset($entry['test']) ? (string) $entry['test'] : null,
            'leftovers' => ! empty($entry['leftovers']),
            'ending'    => ! empty($entry['ending']),
        ];
    }

    /** @return list<int> meeting numbers of a plan */
    public function planMeetings(string $plan): array
    {
        return array_keys(config('Gelita')->meetingPlans[$plan] ?? []);
    }

    public function planChoosesRegion(string $plan): bool
    {
        return (config('Gelita')->meetingPlans[$plan][1]['regions'] ?? null) === 'chosen';
    }

    /**
     * Region codes this game session may enter (28 §9), or null when field
     * mode is off and the usual unlock rules apply. A session without a
     * field session opens nothing.
     *
     * @return list<string>|null
     */
    public function openRegions(GameSession $session): ?array
    {
        $study = $this->fieldModeStudy();

        if ($study === null) {
            return null;
        }

        if (array_key_exists($session->id, $this->regionCache)) {
            return $this->regionCache[$session->id];
        }

        $regions = [];

        if ($session->field_session_id !== null) {
            $fieldSession = model(FieldSessionModel::class)->find($session->field_session_id);

            if ($fieldSession !== null) {
                $regions = $this->planEntry($fieldSession)['regions'];
            }
        }

        return $this->regionCache[$session->id] = $regions;
    }

    // ------------------------------------------------------------ state

    /** @param array<string, mixed> $fieldSession */
    public function state(array $fieldSession, ?string $now = null): string
    {
        $now ??= $this->now();

        if (! empty($fieldSession['closed_at']) || $now >= (string) $fieldSession['closes_at']) {
            return self::STATE_CLOSED;
        }

        return $now < (string) $fieldSession['opens_at'] ? self::STATE_PENDING : self::STATE_OPEN;
    }

    /** @param array<string, mixed> $fieldSession */
    public function isOpen(array $fieldSession): bool
    {
        return $this->state($fieldSession) === self::STATE_OPEN;
    }

    /** True when the game session belongs to a field session that is open now. */
    public function sessionIsOpen(object $session): bool
    {
        $fieldSessionId = (int) ($session->field_session_id ?? 0);

        if ($fieldSessionId <= 0) {
            return false;
        }

        $fieldSession = model(FieldSessionModel::class)->find($fieldSessionId);

        return $fieldSession !== null && $this->isOpen($fieldSession);
    }

    // ------------------------------------------------------------ create

    /**
     * Code of a field session: `S03-A-P2`, make-ups `-S`, `-S2`, … (28 K4).
     */
    public static function sessionCode(string $schoolCode, string $rombel, int $meetingNo, int $makeupNo = 0): string
    {
        $code = sprintf('%s-%s-P%d', $schoolCode, $rombel, $meetingNo);

        return match (true) {
            $makeupNo <= 0 => $code,
            $makeupNo === 1 => $code . '-S',
            default         => $code . '-S' . $makeupNo,
        };
    }

    /**
     * Creates a main field session and its first token (28 §6.1–§6.3).
     *
     * $input: school_id, rombel, meeting_no, session_date (Y-m-d), opens (H:i),
     * closes (H:i), region_code (only for the `satu_sesi` plan).
     *
     * @param array<string, mixed> $input
     *
     * @return array{field_session: array<string, mixed>, token: string}
     *
     * @throws \InvalidArgumentException with a panel message
     */
    public function create(array $input, ?int $staffId): array
    {
        $study  = $this->requireFieldStudy();
        $plan   = (string) ($study['meeting_plan'] ?? 'lima_pertemuan');
        $school = $this->researchSchool((int) ($input['school_id'] ?? 0));
        $rombel = strtoupper(trim((string) ($input['rombel'] ?? '')));
        $no     = (int) ($input['meeting_no'] ?? 0);

        if (preg_match('/^[A-Z]$/', $rombel) !== 1) {
            throw new \InvalidArgumentException(lang('Admin.fieldSessionRombelInvalid'));
        }

        if (! in_array($no, $this->planMeetings($plan), true)) {
            throw new \InvalidArgumentException(lang('Admin.fieldSessionMeetingInvalid'));
        }

        $region = null;

        if ($this->planChoosesRegion($plan)) {
            $region = trim((string) ($input['region_code'] ?? ''));

            if (service('contentRepository')->level($region) === null) {
                throw new \InvalidArgumentException(lang('Admin.fieldSessionRegionRequired'));
            }
        }

        [$opensAt, $closesAt, $date] = $this->hoursFrom($input);
        $code                        = self::sessionCode((string) $school['research_code'], $rombel, $no);
        $sessions                    = model(FieldSessionModel::class);

        if ($sessions->where('study_id', (int) $study['id'])->where('code', $code)->countAllResults() > 0) {
            throw new \InvalidArgumentException(lang('Admin.fieldSessionDuplicate'));
        }

        $db = db_connect();
        $db->transBegin();

        try {
            $id = $sessions->insert([
                'study_id'     => (int) $study['id'],
                'code'         => $code,
                'school_id'    => (int) $school['id'],
                'rombel'       => $rombel,
                'meeting_no'   => $no,
                'makeup_no'    => 0,
                'session_date' => $date,
                'opens_at'     => $opensAt,
                'closes_at'    => $closesAt,
                'region_code'  => $region,
                'created_by'   => $staffId,
            ], true);

            if ($id === false) {
                throw new \InvalidArgumentException(lang('Admin.fieldSessionDuplicate'));
            }

            // The counter row exists before anyone registers, so registrations only UPDATE it (28 §10.2)
            service('participantCodes')->ensureCounter((int) $school['id'], $rombel);

            $token = $this->insertToken((int) $id, $staffId);

            $this->audit('field_session_created', (int) $id, $staffId, [
                'code' => $code, 'opens_at' => $opensAt, 'closes_at' => $closesAt, 'region_code' => $region,
            ]);

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();

            throw $e;
        }

        return ['field_session' => $sessions->find((int) $id), 'token' => $token];
    }

    /**
     * Creates the next make-up session of a closed main session, with its
     * members (28 §6.7, K12).
     *
     * @param array<string, mixed> $input     session_date, opens, closes
     * @param list<int>            $memberIds students allowed in
     *
     * @return array{field_session: array<string, mixed>, token: string}
     */
    public function createMakeup(int $mainId, array $input, array $memberIds, ?int $staffId): array
    {
        $study    = $this->requireFieldStudy();
        $sessions = model(FieldSessionModel::class);
        $main     = $sessions->find($mainId);

        if ($main === null || (int) $main['makeup_no'] !== 0 || (int) $main['study_id'] !== (int) $study['id']) {
            throw new \InvalidArgumentException(lang('Admin.fieldSessionNotFound'));
        }

        if ($this->planChoosesRegion((string) ($study['meeting_plan'] ?? ''))) {
            throw new \InvalidArgumentException(lang('Admin.fieldSessionNoMakeup'));
        }

        if ($this->state($main) !== self::STATE_CLOSED) {
            throw new \InvalidArgumentException(lang('Admin.fieldSessionMakeupNeedsClosed'));
        }

        [$opensAt, $closesAt, $date] = $this->hoursFrom($input);

        $last     = $sessions->selectMax('makeup_no', 'last_no')->where('main_session_id', $mainId)->first();
        $makeupNo = (int) ($last['last_no'] ?? 0) + 1;
        $school   = model(SchoolModel::class)->find((int) $main['school_id']);
        $code     = self::sessionCode((string) $school['research_code'], (string) $main['rombel'], (int) $main['meeting_no'], $makeupNo);

        $db = db_connect();
        $db->transBegin();

        try {
            $id = $sessions->insert([
                'study_id'        => (int) $main['study_id'],
                'code'            => $code,
                'school_id'       => (int) $main['school_id'],
                'rombel'          => (string) $main['rombel'],
                'meeting_no'      => (int) $main['meeting_no'],
                'makeup_no'       => $makeupNo,
                'main_session_id' => $mainId,
                'session_date'    => $date,
                'opens_at'        => $opensAt,
                'closes_at'       => $closesAt,
                'region_code'     => $main['region_code'],
                'created_by'      => $staffId,
            ], true);

            if ($id === false) {
                throw new \InvalidArgumentException(lang('Admin.fieldSessionDuplicate'));
            }

            $token = $this->insertToken((int) $id, $staffId);

            $this->audit('field_session_created', (int) $id, $staffId, [
                'code' => $code, 'main_session_id' => $mainId, 'opens_at' => $opensAt, 'closes_at' => $closesAt,
            ]);

            $this->saveMembers((int) $id, $memberIds, $staffId);

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();

            throw $e;
        }

        return ['field_session' => $sessions->find((int) $id), 'token' => $token];
    }

    /**
     * Replaces the member list of a make-up session; only students of its
     * class group count. Changes go to the audit log.
     *
     * @param list<int> $memberIds
     */
    public function saveMembers(int $fieldSessionId, array $memberIds, ?int $staffId): void
    {
        $fieldSession = model(FieldSessionModel::class)->find($fieldSessionId);

        if ($fieldSession === null || (int) $fieldSession['makeup_no'] === 0) {
            return;
        }

        $allowed = array_column($this->roster($fieldSession), 'id');
        $wanted  = array_values(array_intersect(array_unique(array_map('intval', $memberIds)), $allowed));
        $members = model(FieldSessionMemberModel::class);
        $current = $members->memberIds($fieldSessionId);
        $added   = array_values(array_diff($wanted, $current));
        $removed = array_values(array_diff($current, $wanted));

        if ($removed !== []) {
            $members->where('field_session_id', $fieldSessionId)->whereIn('participant_id', $removed)->delete();
        }

        foreach ($added as $participantId) {
            $members->insert([
                'field_session_id' => $fieldSessionId,
                'participant_id'   => $participantId,
                'added_by'         => $staffId,
                'created_at'       => $this->now(),
            ]);
        }

        if ($added !== [] || $removed !== []) {
            $this->audit('field_session_members_changed', $fieldSessionId, $staffId, [
                'added'   => $this->codesOf($added),
                'removed' => $this->codesOf($removed),
            ]);
        }
    }

    /**
     * Students of the class group a make-up session would start with: every
     * student who has no game session in the main session (28 §6.7).
     *
     * @param array<string, mixed> $main
     *
     * @return list<int>
     */
    public function defaultMakeupMembers(array $main): array
    {
        $entered = $this->enteredIds((int) $main['id']);

        return array_values(array_diff(array_column($this->roster($main), 'id'), $entered));
    }

    // ------------------------------------------------------------ hours and closing

    /**
     * "Buka sekarang" (opening time moves to now) and/or a new closing time,
     * while the session is not closed (28 §6.1). Audited with old and new hours.
     *
     * @param array<string, mixed> $input open_now (bool), closes (H:i)
     */
    public function updateHours(int $id, array $input, ?int $staffId): array
    {
        $sessions     = model(FieldSessionModel::class);
        $fieldSession = $sessions->find($id);

        if ($fieldSession === null) {
            throw new \InvalidArgumentException(lang('Admin.fieldSessionNotFound'));
        }

        if ($this->state($fieldSession) === self::STATE_CLOSED) {
            throw new \InvalidArgumentException(lang('Admin.fieldSessionAlreadyClosed'));
        }

        $now     = $this->now();
        $opensAt = (string) $fieldSession['opens_at'];

        if (! empty($input['open_now']) && $now < $opensAt) {
            $opensAt = $now;
        }

        $closesAt = (string) $fieldSession['closes_at'];
        $closes   = trim((string) ($input['closes'] ?? ''));

        if ($closes !== '') {
            $closesAt = $this->dateTimeOn(substr($opensAt, 0, 10), $closes);
        }

        if ($closesAt <= $opensAt || $closesAt <= $now) {
            throw new \InvalidArgumentException(lang('Admin.fieldSessionHoursOrder'));
        }

        if (strtotime($closesAt) - strtotime($opensAt) > config('Gelita')->fieldSessionMaxHours * 3600) {
            throw new \InvalidArgumentException(lang('Admin.fieldSessionHoursTooLong', [config('Gelita')->fieldSessionMaxHours]));
        }

        if ($opensAt === (string) $fieldSession['opens_at'] && $closesAt === (string) $fieldSession['closes_at']) {
            return $fieldSession;
        }

        $changed = $this->updateIf($sessions, $id, ['closed_at' => null, 'opens_at' => $fieldSession['opens_at'], 'closes_at' => $fieldSession['closes_at']], [
            'opens_at'  => $opensAt,
            'closes_at' => $closesAt,
        ]);

        if ($changed) {
            $this->audit('field_session_hours_changed', $id, $staffId, [
                'code' => $fieldSession['code'],
                'from' => ['opens_at' => $fieldSession['opens_at'], 'closes_at' => $fieldSession['closes_at']],
                'to'   => ['opens_at' => $opensAt, 'closes_at' => $closesAt],
            ]);
        }

        return $sessions->find($id);
    }

    /**
     * "Tutup sesi sekarang": closes the session once and pauses every game
     * session bound to it (28 §6.6). A second click changes nothing.
     */
    public function close(int $id, ?int $staffId): bool
    {
        $sessions     = model(FieldSessionModel::class);
        $fieldSession = $sessions->find($id);

        if ($fieldSession === null || $this->state($fieldSession) === self::STATE_CLOSED) {
            return false;
        }

        $now = $this->now();

        if (! $this->updateIf($sessions, $id, ['closed_at' => null], ['closed_at' => $now, 'closed_by' => $staffId])) {
            return false;
        }

        $this->audit('field_session_closed', $id, $staffId, ['code' => $fieldSession['code'], 'closed_at' => $now]);
        $this->pauseBoundSessions($id);

        return true;
    }

    /**
     * Pauses every game session of a closed field session that is still
     * running; one `session_paused {via: field_session_closed}` each.
     *
     * @return int game sessions paused by this call
     */
    public function pauseBoundSessions(int $fieldSessionId): int
    {
        $count = 0;

        foreach (model(GameSessionModel::class)->where('field_session_id', $fieldSessionId)->whereIn('status', ['active', 'paused'])->where('ended_at', null)->findAll() as $session) {
            $count += $this->pauseForClosedSession($session) ? 1 : 0;
        }

        return $count;
    }

    /**
     * Writes the close of one game session exactly once (28 §6.6): status
     * `paused`, `ended_at` set, event `session_paused {via: field_session_closed}`,
     * through a conditional UPDATE so two requests at the same moment write
     * one event. Open attempts are left as they are (K14).
     */
    public function pauseForClosedSession(GameSession $session): bool
    {
        $db = db_connect();

        $db->table('game_sessions')
            ->where('id', $session->id)
            ->whereIn('status', ['active', 'paused'])
            ->where('ended_at', null)
            ->update(['status' => 'paused', 'ended_at' => $this->now()]);

        if ($db->affectedRows() !== 1) {
            return false;
        }

        service('eventService')->record($session, 'session_paused', ['via' => 'field_session_closed']);

        return true;
    }

    // ------------------------------------------------------------ tokens

    /** Normalizes what a student typed: capitals, no spaces. */
    public static function normalizeToken(string $typed): string
    {
        return strtoupper((string) preg_replace('/\s+/', '', $typed));
    }

    /** True when $token has the shape of a token (length and alphabet). */
    public static function tokenShape(string $token): bool
    {
        return strlen($token) === self::TOKEN_LENGTH && strspn($token, self::TOKEN_ALPHABET) === self::TOKEN_LENGTH;
    }

    /** "Buat token baru": the old token stops working at once; students already inside stay. */
    public function regenerateToken(int $fieldSessionId, ?int $staffId): string
    {
        $fieldSession = model(FieldSessionModel::class)->find($fieldSessionId);

        if ($fieldSession === null) {
            throw new \InvalidArgumentException(lang('Admin.fieldSessionNotFound'));
        }

        if ($this->state($fieldSession) === self::STATE_CLOSED) {
            throw new \InvalidArgumentException(lang('Admin.fieldSessionAlreadyClosed'));
        }

        $db = db_connect();
        $db->transBegin();

        try {
            model(FieldSessionTokenModel::class)->builder()
                ->where('field_session_id', $fieldSessionId)
                ->where('revoked_at', null)
                ->update(['revoked_at' => $this->now()]);

            $token = $this->insertToken($fieldSessionId, $staffId);

            $this->audit('field_session_token_regenerated', $fieldSessionId, $staffId, ['code' => $fieldSession['code']]);

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();

            throw $e;
        }

        return $token;
    }

    /** The valid token of a field session, or null. */
    public function currentToken(int $fieldSessionId): ?string
    {
        $row = model(FieldSessionTokenModel::class)->current($fieldSessionId);

        return $row === null ? null : (string) $row['token'];
    }

    public function randomToken(): string
    {
        if ($this->tokenSource !== null) {
            return ($this->tokenSource)();
        }

        $token = '';
        $max   = strlen(self::TOKEN_ALPHABET) - 1;

        for ($i = 0; $i < self::TOKEN_LENGTH; $i++) {
            $token .= self::TOKEN_ALPHABET[random_int(0, $max)];
        }

        return $token;
    }

    /**
     * Checks a typed token (28 §6.3–§6.5). With $participant (P2–P5 login)
     * the account's study (K20), class group (K8), and make-up membership
     * (K12) are checked too; for registration ($forRegistration) the session
     * must open registration (meeting 1 or its make-up), membership is not
     * checked.
     *
     * @return array{status: string, field_session: array<string, mixed>|null}
     */
    public function checkToken(string $typed, ?Participant $participant, bool $forRegistration = false): array
    {
        $refuse = static fn (string $reason, ?array $fieldSession = null): array => ['status' => $reason, 'field_session' => $fieldSession];
        $token  = self::normalizeToken($typed);
        $study  = $this->fieldModeStudy();

        if ($study === null || ! self::tokenShape($token)) {
            return $refuse('token_unknown');
        }

        $row = model(FieldSessionTokenModel::class)->findToken($token);

        if ($row === null || $row['revoked_at'] !== null) {
            return $refuse('token_unknown');
        }

        $fieldSession = model(FieldSessionModel::class)->find((int) $row['field_session_id']);

        if ($fieldSession === null || (int) $fieldSession['study_id'] !== (int) $study['id']) {
            return $refuse('token_unknown');
        }

        $state = $this->state($fieldSession);

        if ($state === self::STATE_PENDING) {
            return $refuse('not_open_yet', $fieldSession);
        }

        if ($state === self::STATE_CLOSED) {
            return $refuse('closed', $fieldSession);
        }

        if ($forRegistration) {
            return $this->planEntry($fieldSession, $study)['register']
                ? ['status' => 'ok', 'field_session' => $fieldSession]
                : $refuse('token_unknown', $fieldSession);
        }

        if ($participant === null) {
            return $refuse('token_unknown', $fieldSession);
        }

        // Part 4 (30 §5.3): a withdrawn student is refused
        if ($participant->isDeactivated()) {
            return $refuse('inactive', $fieldSession);
        }

        $otherStudy = model(GameSessionModel::class)
            ->where('participant_id', $participant->id)
            ->where('study_id !=', (int) $study['id'])
            ->countAllResults();

        if ($otherStudy > 0) {
            return $refuse('other_study', $fieldSession);
        }

        if ((int) $fieldSession['makeup_no'] > 0) {
            if (! model(FieldSessionMemberModel::class)->isMember((int) $fieldSession['id'], $participant->id)) {
                return $refuse('not_member', $fieldSession);
            }
        } elseif ((int) $participant->school_id !== (int) $fieldSession['school_id'] || (string) $participant->rombel !== (string) $fieldSession['rombel']) {
            return $refuse('other_class', $fieldSession);
        }

        return ['status' => 'ok', 'field_session' => $fieldSession];
    }

    // ------------------------------------------------------------ token throttle

    /** True while $key (a student or a hashed IP) is pausing after too many wrong tokens. */
    public function throttled(string $key): bool
    {
        return service('cache')->get($this->throttleKey($key, 'pause')) !== null;
    }

    /** Counts a wrong token; the tenth in the window starts a pause (28 §6.3). True when the pause starts. */
    public function registerFailure(string $key): bool
    {
        $config = config('Gelita')->tokenThrottle;
        $cache  = service('cache');
        $count  = $this->throttleKey($key, 'count');
        $fails  = (int) ($cache->get($count) ?? 0) + 1;

        if ($fails >= (int) $config['max_fail']) {
            $cache->save($this->throttleKey($key, 'pause'), time(), (int) $config['pause_minutes'] * 60);
            $cache->delete($count);

            return true;
        }

        $cache->save($count, $fails, (int) $config['window_minutes'] * 60);

        return false;
    }

    public function clearFailures(string $key): void
    {
        service('cache')->delete($this->throttleKey($key, 'count'));
    }

    private function throttleKey(string $key, string $kind): string
    {
        return 'gelita_token_' . $kind . '_' . hash('sha256', $key);
    }

    // ------------------------------------------------------------ class groups

    /**
     * Students of a field session's school and class group who play in its
     * study (codes only), ordered by code.
     *
     * @param array<string, mixed> $fieldSession
     *
     * @return list<array{id: int, participant_code: string}>
     */
    public function roster(array $fieldSession): array
    {
        $rows = db_connect()->table('participants p')
            ->select('p.id, p.participant_code')
            ->where('p.school_id', (int) $fieldSession['school_id'])
            ->where('p.rombel', (string) $fieldSession['rombel'])
            ->where('p.deleted_at', null)
            ->whereIn('p.id', static fn (BaseBuilder $sub): BaseBuilder => $sub->select('participant_id')
                ->from('game_sessions')
                ->where('study_id', (int) $fieldSession['study_id']))
            ->orderBy('p.participant_code', 'ASC')
            ->get()
            ->getResultArray();

        return array_map(static fn (array $r): array => ['id' => (int) $r['id'], 'participant_code' => (string) $r['participant_code']], $rows);
    }

    /** @return list<int> students with a game session in this field session */
    public function enteredIds(int $fieldSessionId): array
    {
        return array_map('intval', array_column(
            db_connect()->table('game_sessions')->distinct()->select('participant_id')
                ->where('field_session_id', $fieldSessionId)->get()->getResultArray(),
            'participant_id',
        ));
    }

    // ------------------------------------------------------------ helpers

    /** @return array<string, mixed> */
    private function requireFieldStudy(): array
    {
        $study = model(ResearchStudyModel::class)->activeStudy();

        if ($study === null || (int) ($study['field_mode'] ?? 0) !== 1) {
            throw new \InvalidArgumentException(lang('Admin.fieldModeOff'));
        }

        return $study;
    }

    /** @return array<string, mixed> */
    private function researchSchool(int $schoolId): array
    {
        $school = $schoolId > 0 ? model(SchoolModel::class)->find($schoolId) : null;

        if ($school === null || ($school['research_code'] ?? '') === '') {
            throw new \InvalidArgumentException(lang('Admin.fieldSessionSchoolRequired'));
        }

        return $school;
    }

    /**
     * Opening and closing times from the form, checked: date, closing after
     * opening, at most fieldSessionMaxHours.
     *
     * @param array<string, mixed> $input
     *
     * @return array{0: string, 1: string, 2: string}
     */
    private function hoursFrom(array $input): array
    {
        $date   = trim((string) ($input['session_date'] ?? ''));
        $opens  = trim((string) ($input['opens'] ?? ''));
        $closes = trim((string) ($input['closes'] ?? ''));

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) !== 1 || strtotime($date) === false) {
            throw new \InvalidArgumentException(lang('Admin.fieldSessionDateInvalid'));
        }

        $opensAt  = $this->dateTimeOn($date, $opens);
        $closesAt = $this->dateTimeOn($date, $closes);

        if ($closesAt <= $opensAt) {
            throw new \InvalidArgumentException(lang('Admin.fieldSessionHoursOrder'));
        }

        if (strtotime($closesAt) - strtotime($opensAt) > config('Gelita')->fieldSessionMaxHours * 3600) {
            throw new \InvalidArgumentException(lang('Admin.fieldSessionHoursTooLong', [config('Gelita')->fieldSessionMaxHours]));
        }

        return [$opensAt, $closesAt, $date];
    }

    private function dateTimeOn(string $date, string $time): string
    {
        if (preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $time) !== 1) {
            throw new \InvalidArgumentException(lang('Admin.fieldSessionTimeInvalid'));
        }

        return $date . ' ' . $time . ':00';
    }

    /** Inserts a fresh token; a collision with any earlier token is retried (28 §6.3). */
    private function insertToken(int $fieldSessionId, ?int $staffId): string
    {
        $tokens = model(FieldSessionTokenModel::class);

        for ($try = 0; $try < self::TOKEN_TRIES; $try++) {
            $candidate = $this->randomToken();

            if (! self::tokenShape($candidate) || $tokens->findToken($candidate) !== null) {
                continue;
            }

            $inserted = $tokens->insert([
                'field_session_id' => $fieldSessionId,
                'token'            => $candidate,
                'created_by'       => $staffId,
                'created_at'       => $this->now(),
            ], false);

            if ($inserted !== false) {
                return $candidate;
            }
        }

        throw new \RuntimeException('Token sesi lapangan gagal dibuat.');
    }

    /** @param array<string, mixed> $metadata */
    private function audit(string $action, int $fieldSessionId, ?int $staffId, array $metadata): void
    {
        model(AuditLogModel::class)->record($action, [
            'staff_user_id' => $staffId,
            'target_type'   => 'field_session',
            'target_id'     => (string) $fieldSessionId,
            'metadata'      => $metadata,
        ]);
    }

    /**
     * @param list<int> $ids
     *
     * @return list<string>
     */
    private function codesOf(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        return array_column(
            db_connect()->table('participants')->select('participant_code')->whereIn('id', $ids)->orderBy('participant_code', 'ASC')->get()->getResultArray(),
            'participant_code',
        );
    }

    private function now(): string
    {
        return date('Y-m-d H:i:s');
    }
}
