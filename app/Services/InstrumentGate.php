<?php

namespace App\Services;

use App\Entities\GameSession;
use App\Entities\Participant;
use App\Models\FieldSessionModel;
use App\Models\GameSessionModel;
use App\Models\ParticipantModel;
use App\Models\ResearchStudyModel;

/**
 * When the test module is open, and which step a student is on (research
 * revision 10, part 8: 34 §5, §7.7, §10, K4, K11).
 *
 * The module opens only when all of these hold (§5.1):
 * 1. the active study has `field_mode = 1`, or the validators' preview is on
 *    (`Config\Gelita::$instrumentPreview`) for a `uji.*` account (§5.5);
 * 2. the game session belongs to an open field session whose meeting has the
 *    `test` key of the study's meeting plan (`pre` at P1, `post` at P5), make-ups
 *    included; plan `satu_sesi` has none, so the module never opens there;
 * 3. the student is active (the login filters refuse a deactivated one);
 * 4. a step of the module is still open.
 * The one exception: a questionnaire left open at P1 continues in a later
 * meeting until the main P5 opens (§7.7, §10.2).
 *
 * Steps per occasion (§4): `pre` = test → questionnaire; `post` = test → risk
 * items (T11/T12). `done` when every step is closed; `closed` outside the module.
 */
class InstrumentGate
{
    public const STEP_TEST          = 'test';
    public const STEP_QUESTIONNAIRE = 'questionnaire';
    public const STEP_RISK          = 'risk';
    public const STEP_DONE          = 'done';
    public const STEP_CLOSED        = 'closed';

    /** PHP session key of the validators' preview choice (occasion, form). */
    public const PREVIEW_KEY = 'gelita.instrument_preview';

    /** @var array<string, array<string, mixed>|null> context per participant and game session, this request */
    private array $cache = [];

    /**
     * The module context of a student in a game session, or null when the
     * module is closed for them.
     *
     * @return array{participant: Participant, session: ?GameSession, study: array<string, mixed>, field_session: ?array<string, mixed>,
     *               occasion: string, preview: bool, makeup: bool, test_allowed: bool, questionnaire_allowed: bool,
     *               carry_over: bool, started: bool, step: string}|null
     */
    public function context(Participant $participant, ?GameSession $session): ?array
    {
        $key = $participant->id . ':' . ($session?->id ?? 0);

        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }

        $context = $this->previewContext($participant, $session) ?? $this->fieldContext($participant, $session);

        if ($context !== null) {
            $context['step'] = $this->step($context);

            if ($context['carry_over'] && $context['step'] !== self::STEP_QUESTIONNAIRE) {
                $context = null;
            }
        }

        return $this->cache[$key] = $context;
    }

    /**
     * Step of the module for the student (34 §5.1): `test`, `questionnaire`,
     * `risk`, `done`, or `closed`.
     */
    public function status(Participant $participant, ?GameSession $session): string
    {
        return $this->context($participant, $session)['step'] ?? self::STEP_CLOSED;
    }

    /** Context of the signed-in student of this request (PHP session), or null. */
    public function current(): ?array
    {
        $participant = model(ParticipantModel::class)->find((int) session('participant_id'));

        if ($participant === null || $participant->deactivated_at !== null) {
            return null;
        }

        $id      = (int) session('game_session_id');
        $session = $id > 0 ? model(GameSessionModel::class)->where('participant_id', $participant->id)->find($id) : null;

        return $this->context($participant, $session);
    }

    /** Forgets what this request read (after a write that changes the step). */
    public function forget(): void
    {
        $this->cache = [];
    }

    /**
     * Where a game page must go instead (filter `instrumentRedirect`, §5.2,
     * §5.4), or null. Challenge pages and their API close once the field
     * session's test started (K1) and during a questionnaire carried over to
     * a later meeting; the opening story, the ending, and Balai Refleksi
     * close while any step of the module is open.
     */
    public function redirectFor(string $path, bool $challengeRoute): ?string
    {
        $context = $this->current();

        if ($context === null || $context['preview'] || in_array($context['step'], [self::STEP_DONE, self::STEP_CLOSED], true)) {
            return null;
        }

        if (! $challengeRoute) {
            return 'tes';
        }

        return $context['started'] || $context['carry_over'] ? 'tes' : null;
    }

    // ------------------------------------------------------------ internal

    /** @return array<string, mixed>|null */
    private function previewContext(Participant $participant, ?GameSession $session): ?array
    {
        if (! self::previewAllowed($participant)) {
            return null;
        }

        $study = model(ResearchStudyModel::class)->activeStudy();

        if ($study === null) {
            return null;
        }

        $choice   = (array) (session(self::PREVIEW_KEY) ?? []);
        $occasion = in_array($choice['occasion'] ?? null, ['pre', 'post'], true) ? (string) $choice['occasion'] : 'pre';

        return [
            'participant' => $participant, 'session' => $session, 'study' => $study, 'field_session' => null,
            'occasion' => $occasion, 'preview' => true, 'makeup' => false, 'test_allowed' => true,
            'questionnaire_allowed' => true, 'carry_over' => false, 'started' => true,
        ];
    }

    /** True for a `uji.*` account while the preview switch is on and field mode is off (K4). */
    public static function previewAllowed(Participant $participant): bool
    {
        return self::previewFor((string) $participant->username);
    }

    /** previewAllowed() by username (the HUD menu row `Coba modul tes`). */
    public static function previewFor(string $username): bool
    {
        return config('Gelita')->instrumentPreview
            && str_starts_with($username, TestAccountService::PREFIX)
            && ! service('fieldSessions')->fieldModeOn();
    }

    /** @return array<string, mixed>|null */
    private function fieldContext(Participant $participant, ?GameSession $session): ?array
    {
        $fields = service('fieldSessions');
        $study  = $fields->fieldModeStudy();

        if ($study === null || $session === null || $session->field_session_id === null) {
            return null;
        }

        $fieldSession = model(FieldSessionModel::class)->find($session->field_session_id);

        if ($fieldSession === null || ! $fields->isOpen($fieldSession) || (int) $fieldSession['study_id'] !== (int) $study['id']) {
            return null;
        }

        $occasion = $fields->planEntry($fieldSession, $study)['test'];
        $makeup   = (int) $fieldSession['makeup_no'] > 0;
        $base     = [
            'participant' => $participant, 'session' => $session, 'study' => $study, 'field_session' => $fieldSession,
            'preview' => false, 'makeup' => $makeup, 'started' => $fieldSession['test_started_at'] !== null,
        ];

        if ($occasion === null) {
            // A questionnaire left open at P1 continues before the game (§7.7), until the main P5 opens (§10.2)
            if (! $this->p5Opened($study, $fieldSession) && $this->openSitting($participant->id, (int) $study['id'], 'pre', 'questionnaire') !== null) {
                return $base + ['occasion' => 'pre', 'test_allowed' => false, 'questionnaire_allowed' => true, 'carry_over' => true];
            }

            return null;
        }

        return $base + [
            'occasion'              => $occasion,
            'test_allowed'          => ! $makeup || $this->withinMakeupWindow($fieldSession, (int) service('instruments')->settings($study)['makeup_days']),
            'questionnaire_allowed' => $occasion === 'pre' && ! $this->p5Opened($study, $fieldSession),
            'carry_over'            => false,
        ];
    }

    /** @param array<string, mixed> $context */
    private function step(array $context): string
    {
        $instruments = service('instruments');
        $pid         = $context['participant']->id;
        $studyId     = (int) $context['study']['id'];
        $preview     = $context['preview'];
        $test        = $instruments->sitting($pid, $studyId, $context['occasion'], 'test', $preview);

        if ($test !== null && $test['submitted_at'] === null) {
            $test = $instruments->sync($test);
        }

        $testOpen = $context['test_allowed'] && ! $context['carry_over'] && ($test === null || $test['submitted_at'] === null);

        // K11: no pre-test after playing; the questionnaire still opens (late = 1)
        if ($testOpen && $test === null && $context['occasion'] === 'pre' && ! $preview && $instruments->hasPlayed($pid, $studyId)) {
            $testOpen = false;
        }

        if ($testOpen) {
            return self::STEP_TEST;
        }

        if ($context['occasion'] === 'pre') {
            $questionnaire = $instruments->sitting($pid, $studyId, 'pre', 'questionnaire', $preview);

            if ($context['questionnaire_allowed'] && ($questionnaire === null || $questionnaire['submitted_at'] === null)) {
                return self::STEP_QUESTIONNAIRE;
            }

            return self::STEP_DONE;
        }

        $risk = $instruments->sitting($pid, $studyId, 'post', 'risk', $preview);

        return $risk === null || $risk['submitted_at'] === null ? self::STEP_RISK : self::STEP_DONE;
    }

    /** @return array<string, mixed>|null an unsubmitted sitting */
    private function openSitting(int $participantId, int $studyId, string $occasion, string $kind): ?array
    {
        $sitting = service('instruments')->sitting($participantId, $studyId, $occasion, $kind, false);

        return $sitting !== null && $sitting['submitted_at'] === null ? $sitting : null;
    }

    /** True once the main P5 of the student's class group has opened (§10.2: the P1 questionnaire is no longer offered). */
    public function p5Opened(array $study, array $fieldSession): bool
    {
        $meeting = $this->postMeeting((string) ($study['meeting_plan'] ?? 'lima_pertemuan'));

        if ($meeting === null) {
            return false;
        }

        return model(FieldSessionModel::class)
            ->where('study_id', (int) $study['id'])->where('school_id', (int) $fieldSession['school_id'])->where('rombel', (string) $fieldSession['rombel'])
            ->where('meeting_no', $meeting)->where('makeup_no', 0)->where('opens_at <=', date('Y-m-d H:i:s'))
            ->countAllResults() > 0;
    }

    /** Meeting number with the `post` test in a plan, or null (satu_sesi). */
    public function postMeeting(string $plan): ?int
    {
        foreach (config('Gelita')->meetingPlans[$plan] ?? [] as $no => $entry) {
            if (($entry['test'] ?? null) === 'post') {
                return (int) $no;
            }
        }

        return null;
    }

    /** A make-up within $days days of its main session's date (§10.1, §10.3). */
    public function withinMakeupWindow(array $fieldSession, int $days): bool
    {
        $mainId = (int) ($fieldSession['main_session_id'] ?? 0);
        $main   = $mainId > 0 ? model(FieldSessionModel::class)->find($mainId) : null;

        if ($main === null) {
            return true;
        }

        return (string) $fieldSession['session_date'] <= date('Y-m-d', strtotime((string) $main['session_date'] . ' +' . $days . ' days'));
    }

    // ------------------------------------------------------------ Balai Refleksi (§9.1, K12)

    /**
     * Where Balai Refleksi stands for a student who completed every challenge
     * (34 §9.1). `mode` `off`: field mode is off and no preview, the page is as
     * before (§3). Otherwise `state`:
     * - `last_meeting`: not inside the main P5 or a make-up within `attitude_days` of it;
     * - `redirect`: a step comes first (`target` = `tes` or `penutup`);
     * - `scale`: the attitude scale is not sent yet;
     * - `open`: the scale is sent; praise, journey, and feedback show.
     *
     * @return array{mode: string, state?: string, target?: string, context?: array<string, mixed>}
     */
    public function reflectionState(Participant $participant, GameSession $session): array
    {
        $instruments = service('instruments');

        if (self::previewAllowed($participant)) {
            $study   = model(ResearchStudyModel::class)->activeStudy();
            $context = ['participant' => $participant, 'session' => $session, 'study' => $study, 'field_session' => null, 'preview' => true, 'makeup' => false];
            $sitting = $study === null ? null : $instruments->sitting($participant->id, (int) $study['id'], 'p5', 'attitude', true);

            return ['mode' => 'preview', 'state' => $sitting !== null && $sitting['submitted_at'] !== null ? 'open' : 'scale', 'context' => $context];
        }

        $fields = service('fieldSessions');
        $study  = $fields->fieldModeStudy();

        if ($study === null) {
            return ['mode' => 'off'];
        }

        $fieldSession = $session->field_session_id === null ? null : model(FieldSessionModel::class)->find($session->field_session_id);
        $days         = $instruments->settings($study)['attitude_days'];

        if ($fieldSession === null || ! $fields->isOpen($fieldSession) || ! $fields->planEntry($fieldSession, $study)['ending']
            || ((int) $fieldSession['makeup_no'] > 0 && ! $this->withinMakeupWindow($fieldSession, $days))) {
            return ['mode' => 'study', 'state' => 'last_meeting'];
        }

        $step = $this->status($participant, $session);

        if (! in_array($step, [self::STEP_DONE, self::STEP_CLOSED], true)) {
            return ['mode' => 'study', 'state' => 'redirect', 'target' => 'tes'];
        }

        if (! $this->endingSeen($session)) {
            return ['mode' => 'study', 'state' => 'redirect', 'target' => 'penutup'];
        }

        $context = ['participant' => $participant, 'session' => $session, 'study' => $study, 'field_session' => $fieldSession, 'preview' => false, 'makeup' => (int) $fieldSession['makeup_no'] > 0];
        $sitting = $instruments->sitting($participant->id, (int) $study['id'], 'p5', 'attitude');

        return ['mode' => 'study', 'state' => $sitting !== null && $sitting['submitted_at'] !== null ? 'open' : 'scale', 'context' => $context];
    }

    /** The closing story reached its last slide in this game session (`dialogue_completed`, context `ending`, 29 §5.3). */
    public function endingSeen(GameSession $session): bool
    {
        $rows = db_connect()->table('game_event_logs')->select('payload_json')
            ->where('session_id', $session->id)->where('event_type', 'dialogue_completed')->where('deleted_at', null)
            ->like('payload_json', 'ending')->get()->getResultArray();

        foreach ($rows as $row) {
            if ((json_decode((string) $row['payload_json'], true)['context'] ?? null) === 'ending') {
                return true;
            }
        }

        return false;
    }
}
