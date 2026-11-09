<?php

namespace App\Services;

use App\Exceptions\ChallengeRuleException;
use App\Libraries\InstrumentImporter;
use App\Models\AuditLogModel;
use App\Models\Concerns\ConditionalUpdate;
use App\Models\InstrumentBlockModel;
use App\Models\InstrumentPartModel;
use App\Models\InstrumentResponseModel;
use App\Models\InstrumentSittingModel;
use App\Models\ParticipantSeatModel;

/**
 * The test module's sittings, parts, time blocks, and answers (research
 * revision 10, part 8: 34 §7–§10, K3, K5–K11). One engine for the four
 * instruments: the pre/post test, the questionnaire, the online-risk items,
 * and the attitude scale.
 *
 * - A sitting is one student × study × occasion × kind (× preview), made by an
 *   INSERT guarded by its unique key; the loser reads the row that exists (§7.2).
 * - Answer units (K7) are made for a whole part when it opens, empty. Showing
 *   a unit stamps `shown_at`; saving adds the server time since then to
 *   `time_on_item_ms` (capped at the block's limit) and keeps it as
 *   `last_view_ms` (§7.5).
 * - Every write that can race is a conditional UPDATE (ConditionalUpdate):
 *   answers on `revision`, parts on `submitted_at IS NULL AND closed_at IS NULL`,
 *   blocks on `closed_at IS NULL`, sittings on `submitted_at IS NULL`, the
 *   seat lock on `locked_at IS NULL`. The loser is rolled back and answered
 *   from the newest state.
 * - Hidden limits (§7.4): a block closes at `started_at + limit + extra`, on
 *   the next module request or by `gelita:retention:run`; its open parts close
 *   with `closed_by_limit = 1` and are scored (§7.6).
 * - `updated_at` of sittings and answers is written on every change (34 §18 no. 6).
 *
 * No game events are written (K14); staff actions go to the audit log.
 */
class InstrumentService
{
    use ConditionalUpdate;

    public const KINDS = ['test', 'questionnaire', 'risk', 'attitude'];

    /** Test parts in order (13 → Kode butir). */
    public const TEST_PARTS = ['mb', 'ae', 'bd'];

    /** Longest single view counted without a time limit (questionnaire, risk items, attitude scale). */
    public const UNTIMED_VIEW_CAP_MS = 3_600_000;

    /** Part of the examples of a test sitting (not scored, not timed). */
    public const EXAMPLE_PART = 'ct';

    /** @var array<string, array<string, mixed>|null> sitting cache of this request */
    private array $sittings = [];

    // ------------------------------------------------------------ settings

    /**
     * Hidden limits and windows of a study (§7.4): Config\Gelita::$instrumentDefaults
     * overridden by `research_studies.instrument_settings`.
     *
     * @param array<string, mixed> $study
     *
     * @return array<string, int>
     */
    public function settings(array $study): array
    {
        $defaults = config('Gelita')->instrumentDefaults;
        $stored   = json_decode((string) ($study['instrument_settings'] ?? ''), true);
        $out      = [];

        foreach ($defaults as $key => $value) {
            $out[$key] = is_array($stored) && isset($stored[$key]) && is_numeric($stored[$key]) ? (int) $stored[$key] : (int) $value;
        }

        return $out;
    }

    /** Minute limit of a time block from the study's settings. */
    public function blockLimit(array $study, string $block): int
    {
        return $this->settings($study)['limit_' . $block] ?? 40;
    }

    // ------------------------------------------------------------ sittings

    /** @return array<string, mixed>|null */
    public function sitting(int $participantId, int $studyId, string $occasion, string $kind, bool $preview = false): ?array
    {
        $key = implode(':', [$participantId, $studyId, $occasion, $kind, (int) $preview]);

        if (! array_key_exists($key, $this->sittings)) {
            $this->sittings[$key] = model(InstrumentSittingModel::class)
                ->where(['participant_id' => $participantId, 'study_id' => $studyId, 'occasion' => $occasion, 'kind' => $kind, 'is_preview' => (int) $preview])
                ->first();
        }

        return $this->sittings[$key];
    }

    /** @return array<string, mixed>|null */
    public function sittingById(int $id): ?array
    {
        return model(InstrumentSittingModel::class)->find($id);
    }

    /** Forgets the sittings read in this request. */
    public function forget(): void
    {
        $this->sittings = [];
        service('instrumentGate')->forget();
    }

    /** True when the student has a challenge attempt in the study (K11). */
    public function hasPlayed(int $participantId, int $studyId): bool
    {
        return db_connect()->table('challenge_attempts ca')->join('game_sessions gs', 'gs.id = ca.session_id')
            ->where('gs.participant_id', $participantId)->where('gs.study_id', $studyId)->countAllResults() > 0;
    }

    /**
     * Makes the test sitting (§7.2, K10, K11): the form is copied from
     * `participant_seats` (preview: the validator's choice), the content
     * version is the active one, and the pre-test locks the seat.
     *
     * @param array<string, mixed> $context InstrumentGate::context()
     *
     * @return array<string, mixed>
     *
     * @throws \DomainException INSTRUMENT_NOT_STARTED, INSTRUMENT_NO_FORM, INSTRUMENT_PRE_AFTER_PLAY, INSTRUMENT_NO_CONTENT
     */
    public function startTest(array $context, ?string $previewForm = null): array
    {
        $pid      = $context['participant']->id;
        $study    = $context['study'];
        $occasion = $context['occasion'];
        $existing = $this->sitting($pid, (int) $study['id'], $occasion, 'test', $context['preview']);

        if ($existing !== null) {
            return $existing;
        }

        if (! $context['started']) {
            throw new \DomainException('INSTRUMENT_NOT_STARTED');
        }

        if ($occasion === 'pre' && ! $context['preview'] && $this->hasPlayed($pid, (int) $study['id'])) {
            throw new \DomainException('INSTRUMENT_PRE_AFTER_PLAY');
        }

        $seat = $context['preview'] ? null : model(ParticipantSeatModel::class)->findFor($pid, (int) $study['id']);
        $form = $context['preview']
            ? (in_array($previewForm, InstrumentImporter::TEST_FORMS, true) ? $previewForm : null)
            : ($seat[$occasion . '_form'] ?? null);

        if (! in_array($form, InstrumentImporter::TEST_FORMS, true)) {
            throw new \DomainException('INSTRUMENT_NO_FORM');
        }

        $sitting = $this->insertSitting($context, 'test', ['form' => $form]);

        // K10: the seat is locked when the student's pre-test sitting is made (once)
        if ($occasion === 'pre' && $seat !== null && $seat['locked_at'] === null) {
            $this->updateIf(model(ParticipantSeatModel::class), (int) $seat['id'], ['locked_at' => null], ['locked_at' => $this->now()]);
        }

        $this->openPart($sitting, self::EXAMPLE_PART, null);

        return $sitting;
    }

    /**
     * The questionnaire sitting (§7.7), with `late = 1` when the student has
     * played already (16 no. 9, §10.2). All eight sections open at once.
     *
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public function openQuestionnaire(array $context): array
    {
        $sitting = $this->sitting($context['participant']->id, (int) $context['study']['id'], 'pre', 'questionnaire', $context['preview'])
            ?? $this->insertSitting($context + ['occasion' => 'pre'], 'questionnaire', [
                'late' => ! $context['preview'] && $this->hasPlayed($context['participant']->id, (int) $context['study']['id']) ? 1 : 0,
            ]);

        foreach (InstrumentImporter::QUESTIONNAIRE_SECTIONS as $section) {
            $this->openPart($sitting, $section, null);
        }

        return $sitting;
    }

    /**
     * The `risk` sitting of every student after the post-test (§8.1), with
     * `risk_offered` from ConsentService::riskItemsAllowed() read now. Preview
     * sittings follow the test account's consent row (30 §5.2).
     *
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public function openRisk(array $context): array
    {
        return $this->sitting($context['participant']->id, (int) $context['study']['id'], 'post', 'risk', $context['preview'])
            ?? $this->insertSitting($context + ['occasion' => 'post'], 'risk', [
                'risk_offered' => service('consentService')->riskItemsAllowed($context['participant']->id) ? 1 : 0,
            ]);
    }

    /**
     * The attitude sitting in Balai Refleksi (§9), occasion `p5`.
     *
     * @param array<string, mixed> $context participant, session, study, field_session, preview, makeup
     *
     * @return array<string, mixed>
     */
    public function openAttitude(array $context): array
    {
        $sitting = $this->sitting($context['participant']->id, (int) $context['study']['id'], 'p5', 'attitude', $context['preview'])
            ?? $this->insertSitting($context + ['occasion' => 'p5'], 'attitude', []);

        $this->openPart($sitting, 'sikap', null);

        return $sitting;
    }

    /**
     * INSERT guarded by the unique key; the loser reads the existing row (§7.2).
     *
     * @param array<string, mixed> $context
     * @param array<string, mixed> $extra
     *
     * @return array<string, mixed>
     */
    private function insertSitting(array $context, string $kind, array $extra): array
    {
        $instrument = ['test' => 'tes', 'questionnaire' => 'angket', 'risk' => 'angket', 'attitude' => 'sikap'][$kind];
        $version    = service('instrumentRepository')->activeVersion($instrument);

        if ($version === null) {
            throw new \DomainException('INSTRUMENT_NO_CONTENT');
        }

        $now = $this->now();
        $pid = $context['participant']->id;
        $sid = (int) $context['study']['id'];

        db_connect()->table('instrument_sittings')->ignore(true)->insert($extra + [
            'participant_id'   => $pid,
            'study_id'         => $sid,
            'field_session_id' => $context['field_session']['id'] ?? null,
            'occasion'         => $context['occasion'],
            'kind'             => $kind,
            'version_id'       => $version['id'],
            'is_makeup'        => $context['makeup'] ? 1 : 0,
            'is_preview'       => $context['preview'] ? 1 : 0,
            'started_at'       => $now,
            'updated_at'       => $now,
        ]);

        $this->forget();

        return $this->sitting($pid, $sid, $context['occasion'], $kind, $context['preview'])
            ?? throw new \RuntimeException('Sesi instrumen tidak dapat dibuat.');
    }

    // ------------------------------------------------------------ parts

    /** @return array<string, mixed>|null */
    public function part(int $sittingId, string $part): ?array
    {
        return model(InstrumentPartModel::class)->where('sitting_id', $sittingId)->where('part', $part)->first();
    }

    /** @return array<string, array<string, mixed>> part code → row */
    public function parts(int $sittingId): array
    {
        $out = [];

        foreach (model(InstrumentPartModel::class)->where('sitting_id', $sittingId)->findAll() as $row) {
            $out[(string) $row['part']] = $row;
        }

        return $out;
    }

    /** @param array<string, mixed>|null $part */
    public static function isClosed(?array $part): bool
    {
        return $part !== null && ($part['submitted_at'] !== null || $part['closed_at'] !== null);
    }

    /**
     * Items of a part of a sitting, by position (test parts: the sitting's form).
     *
     * @return list<array<string, mixed>>
     */
    public function items(array $sitting, string $part): array
    {
        $repo = service('instrumentRepository');

        if ($part === self::EXAMPLE_PART && $sitting['kind'] === 'test') {
            return $repo->items((int) $sitting['version_id'], 'ct');
        }

        return $repo->items((int) $sitting['version_id'], $part, $sitting['kind'] === 'test' ? (string) $sitting['form'] : null);
    }

    /**
     * Opens a part with one empty answer row per unit (INSERT IGNORE; a second
     * call changes nothing).
     *
     * @param array<string, mixed> $sitting
     *
     * @return array<string, mixed>
     */
    public function openPart(array $sitting, string $part, ?int $blockId): array
    {
        $existing = $this->part((int) $sitting['id'], $part);

        if ($existing !== null) {
            return $existing;
        }

        $db  = db_connect();
        $now = $this->now();
        $db->table('instrument_parts')->ignore(true)->insert([
            'sitting_id' => (int) $sitting['id'], 'part' => $part, 'block_id' => $blockId, 'opened_at' => $now,
        ]);
        $row = $this->part((int) $sitting['id'], $part) ?? throw new \RuntimeException('Bagian instrumen tidak dapat dibuka.');

        foreach ($this->items($sitting, $part) as $item) {
            foreach ($item['units'] as $unit) {
                $db->table('instrument_responses')->ignore(true)->insert([
                    'part_id' => (int) $row['id'], 'sitting_id' => (int) $sitting['id'], 'item_id' => $item['id'],
                    'unit' => $unit, 'updated_at' => $now,
                ]);
            }
        }

        return $row;
    }

    /**
     * Starts a test part after its examples (§7.3): `mb` starts block `p1`,
     * `ae` block `p23`, `bd` joins `p23`. Example answers are saved first
     * (not scored, not timed).
     *
     * @param array<string, mixed>     $sitting
     * @param array<string, ?string>   $exampleAnswers unit → raw response
     *
     * @return array<string, mixed>|null the part, or null when it cannot start (an earlier part is open, or the block closed)
     */
    public function startPart(array $sitting, string $part, array $exampleAnswers = []): ?array
    {
        if (! in_array($part, self::TEST_PARTS, true) || $sitting['kind'] !== 'test' || $sitting['submitted_at'] !== null) {
            return null;
        }

        $examples = $this->part((int) $sitting['id'], self::EXAMPLE_PART);

        if ($examples !== null && $exampleAnswers !== []) {
            $this->save($sitting, $examples, $exampleAnswers);
        }

        $index = array_search($part, self::TEST_PARTS, true);
        $parts = $this->parts((int) $sitting['id']);

        if (isset($parts[$part])) {
            return $parts[$part];
        }

        if ($index > 0 && ! self::isClosed($parts[self::TEST_PARTS[$index - 1]] ?? null)) {
            return null;
        }

        $blockCode = InstrumentImporter::TEST_PARTS[$part]['block'];
        $block     = $this->block((int) $sitting['id'], $blockCode) ?? $this->openBlock($sitting, $blockCode);

        if ($block['closed_at'] !== null) {
            return null;
        }

        return $this->openPart($sitting, $part, (int) $block['id']);
    }

    /**
     * Sends a part (§7.3 step 5): the conditional UPDATE, then scoring, the
     * block (when all its parts are sent), and the sitting (when all parts are closed).
     *
     * @param array<string, mixed> $sitting
     */
    public function submitPart(array $sitting, string $part): bool
    {
        $sitting = $this->sync($sitting);
        $row     = $this->part((int) $sitting['id'], $part);

        if ($row === null || self::isClosed($row)) {
            return false;
        }

        $sent = $this->updateIf(model(InstrumentPartModel::class), (int) $row['id'], ['submitted_at' => null, 'closed_at' => null], ['submitted_at' => $this->now()]);

        if (! $sent) {
            return false;
        }

        $this->scorePart((int) $row['id']);

        if ($row['block_id'] !== null) {
            $this->closeBlockWhenSent($sitting, (int) $row['block_id']);
        }

        $this->closeSittingWhenDone($sitting);

        return true;
    }

    /**
     * Sends a whole sitting without a time limit (questionnaire Selesai, risk
     * items at T12, attitude Kirim): its open parts are sent, then the sitting.
     *
     * @param array<string, mixed> $sitting
     */
    public function submitSitting(array $sitting): bool
    {
        $now = $this->now();

        foreach ($this->parts((int) $sitting['id']) as $row) {
            if (! self::isClosed($row) && $this->updateIf(model(InstrumentPartModel::class), (int) $row['id'], ['submitted_at' => null, 'closed_at' => null], ['submitted_at' => $now])) {
                $this->scorePart((int) $row['id']);
            }
        }

        $done = $this->updateIf(model(InstrumentSittingModel::class), (int) $sitting['id'], ['submitted_at' => null], ['submitted_at' => $now, 'updated_at' => $now]);
        $this->forget();

        return $done;
    }

    /**
     * T11 answer (30 §5.4): `risk_reconsent` once, with its time; Ya opens the risk part.
     *
     * @param array<string, mixed> $sitting
     */
    public function reconsent(array $sitting, bool $yes): bool
    {
        if ((int) $sitting['risk_offered'] !== 1) {
            return false;
        }

        $now     = $this->now();
        $changed = $this->updateIf(model(InstrumentSittingModel::class), (int) $sitting['id'], ['risk_reconsent' => null], [
            'risk_reconsent' => $yes ? 1 : 0, 'risk_reconsent_at' => $now, 'updated_at' => $now,
        ]);

        if ($changed && $yes) {
            $this->openPart($sitting, 'risk', null);
        }

        $this->forget();

        return $changed;
    }

    // ------------------------------------------------------------ answers

    /**
     * Answer rows of a sitting, by unit.
     *
     * @return array<string, array<string, mixed>>
     */
    public function responses(int $sittingId, ?int $partId = null): array
    {
        $model = model(InstrumentResponseModel::class)->where('sitting_id', $sittingId);

        if ($partId !== null) {
            $model->where('part_id', $partId);
        }

        $out = [];

        foreach ($model->orderBy('id', 'ASC')->findAll() as $row) {
            $out[(string) $row['unit']] = $row;
        }

        return $out;
    }

    /**
     * A page shows these units (§7.5): `shown_at` (server time), `view_count`
     * + 1, and `first_shown_at` the first time; the sitting remembers the item.
     *
     * @param array<string, mixed> $sitting
     * @param list<string>         $units
     */
    public function markShown(array $sitting, array $units, ?int $itemId = null): void
    {
        if ($units === []) {
            return;
        }

        $now = $this->now();
        $db  = db_connect();

        $db->table('instrument_responses')->where('sitting_id', (int) $sitting['id'])->whereIn('unit', $units)
            ->set('shown_at', $now)->set('view_count', 'view_count + 1', false)->set('updated_at', $now)->update();
        $db->table('instrument_responses')->where('sitting_id', (int) $sitting['id'])->whereIn('unit', $units)->where('first_shown_at', null)
            ->update(['first_shown_at' => $now]);

        if ($itemId !== null) {
            $db->table('instrument_sittings')->where('id', (int) $sitting['id'])->update(['last_item_id' => $itemId, 'updated_at' => $now]);
        }
    }

    /**
     * Saves the answers of one page (§7.5): the view time since `shown_at`
     * (capped), `answer_changes` + 1 when a stored answer changes, and the
     * conditional UPDATE on `revision`. False when the part is closed or
     * another tab won the race: nothing changed, the caller shows the newest state.
     *
     * @param array<string, mixed>   $sitting
     * @param array<string, mixed>   $part
     * @param array<string, ?string> $values unit → raw response (null = empty)
     */
    public function save(array $sitting, array $part, array $values): bool
    {
        if ($values === []) {
            return true;
        }

        $cap = $this->viewCapMs($sitting, $part);
        $db  = db_connect();
        $db->transBegin();

        try {
            $rows = $this->responses((int) $sitting['id'], (int) $part['id']);
            $now  = $this->now();

            foreach ($values as $unit => $value) {
                $row = $rows[$unit] ?? null;

                if ($row === null) {
                    continue;
                }

                $value   = $value === null || $value === '' ? null : (string) $value;
                $old     = $row['response'] === null ? null : (string) $row['response'];
                $elapsed = $row['shown_at'] === null ? null : min($cap, max(0, self::ms($now) - self::ms((string) $row['shown_at'])));
                $data    = [
                    'response'        => $value,
                    'shown_at'        => null,
                    'time_on_item_ms' => (int) $row['time_on_item_ms'] + (int) $elapsed,
                    'answer_changes'  => (int) $row['answer_changes'] + ($old !== null && $old !== $value ? 1 : 0),
                    'revision'        => (int) $row['revision'] + 1,
                    'updated_at'      => $now,
                ];

                if ($elapsed !== null) {
                    $data['last_view_ms'] = $elapsed;
                }

                if ($value !== null && $value !== $old) {
                    $data['answered_at'] = $now;
                }

                $this->updateOrStale(model(InstrumentResponseModel::class), (int) $row['id'], ['revision' => (int) $row['revision']], $data);
            }

            // The part must still be open after the writes (two tabs, or the limit just passed)
            $fresh = $this->part((int) $sitting['id'], (string) $part['part']);

            if (self::isClosed($fresh) || $this->blockOverdue($fresh)) {
                throw ChallengeRuleException::attemptChanged();
            }

            $db->table('instrument_sittings')->where('id', (int) $sitting['id'])->update(['updated_at' => $now]);
            $db->transCommit();

            return true;
        } catch (ChallengeRuleException) {
            $db->transRollback();

            return false;
        } catch (\Throwable $e) {
            $db->transRollback();

            throw $e;
        }
    }

    /**
     * Scores one part (§7.6, InstrumentScorer): `score`, `error_codes`, and
     * `not_reached` per unit, then `raw_score` and `answered_count`.
     */
    public function scorePart(int $partId): void
    {
        $part = model(InstrumentPartModel::class)->find($partId);

        if ($part === null) {
            return;
        }

        $sitting  = $this->sittingById((int) $part['sitting_id']);
        $content  = service('instrumentRepository')->content((int) $sitting['version_id']);
        $scored   = $sitting['kind'] === 'test' && in_array($part['part'], self::TEST_PARTS, true);
        $limited  = (int) $part['closed_by_limit'] === 1;
        $raw      = 0;
        $answered = 0;
        $now      = $this->now();
        $db       = db_connect();

        foreach ($this->responses((int) $sitting['id'], $partId) as $row) {
            $notReached = $limited && $row['first_shown_at'] === null;
            $result     = $scored ? InstrumentScorer::score($content['byId'][(int) $row['item_id']], $row['response'] === null ? null : (string) $row['response']) : ['score' => null, 'error_codes' => null];

            if ($notReached) {
                $result['score'] = null;   // missing in the Rasch analysis, not wrong (13)
            }

            $raw      += (int) $result['score'];
            $answered += $row['response'] === null ? 0 : 1;

            $db->table('instrument_responses')->where('id', (int) $row['id'])->update([
                'score' => $result['score'], 'error_codes' => $result['error_codes'], 'not_reached' => $notReached ? 1 : 0,
                'revision' => (int) $row['revision'] + 1, 'updated_at' => $now,
            ]);
        }

        $db->table('instrument_parts')->where('id', $partId)->update([
            'raw_score' => $scored ? $raw : null, 'answered_count' => $answered,
        ]);
    }

    // ------------------------------------------------------------ time limits

    /** @return array<string, mixed>|null */
    public function block(int $sittingId, string $block): ?array
    {
        return model(InstrumentBlockModel::class)->where('sitting_id', $sittingId)->where('block', $block)->first();
    }

    /** @param array<string, mixed> $sitting */
    private function openBlock(array $sitting, string $block): array
    {
        $study = model(\App\Models\ResearchStudyModel::class)->find((int) $sitting['study_id']);

        db_connect()->table('instrument_blocks')->ignore(true)->insert([
            'sitting_id'    => (int) $sitting['id'],
            'block'         => $block,
            'started_at'    => $this->now(),
            // A copy of the setting when the block starts; later changes apply to later blocks (§7.4)
            'limit_minutes' => $this->blockLimit($study ?? [], $block),
            'extra_minutes' => 0,
        ]);

        return $this->block((int) $sitting['id'], $block) ?? throw new \RuntimeException('Blok waktu tidak dapat dibuat.');
    }

    /** Unix milliseconds when the block closes: started_at + limit + extra (§7.4). */
    public static function deadlineMs(array $block): int
    {
        return self::ms((string) $block['started_at']) + ((int) $block['limit_minutes'] + (int) $block['extra_minutes']) * 60_000;
    }

    /** Seconds until the block closes (never below 0). */
    public function secondsLeft(array $block): int
    {
        return max(0, (int) floor((self::deadlineMs($block) - self::ms($this->now())) / 1000));
    }

    /**
     * Closes the sitting's blocks that passed their limit (§7.4). Preview
     * sittings have no limit (§5.5). Returns the sitting as it is now.
     *
     * @param array<string, mixed> $sitting
     *
     * @return array<string, mixed>
     */
    public function sync(array $sitting): array
    {
        if ($sitting['kind'] !== 'test' || $sitting['submitted_at'] !== null || (int) $sitting['is_preview'] === 1) {
            return $sitting;
        }

        $now     = self::ms($this->now());
        $changed = false;

        foreach (model(InstrumentBlockModel::class)->where('sitting_id', (int) $sitting['id'])->where('closed_at', null)->findAll() as $block) {
            if ($now >= self::deadlineMs($block)) {
                $changed = $this->closeBlockByLimit($sitting, $block) || $changed;
            }
        }

        return $changed ? ($this->sittingById((int) $sitting['id']) ?? $sitting) : $sitting;
    }

    /**
     * Closes a block at its limit: the conditional UPDATE, then every part of
     * the block that is not sent closes with `closed_by_limit = 1`, in the
     * same transaction. A part of the block that never opened (Bagian 3 when
     * Bagian 2 ran out) is made closed, so all its items read `not_reached`.
     *
     * @param array<string, mixed> $sitting
     * @param array<string, mixed> $block
     */
    private function closeBlockByLimit(array $sitting, array $block): bool
    {
        $db  = db_connect();
        $now = $this->now();
        $db->transStart();

        $closed = $this->updateIf(model(InstrumentBlockModel::class), (int) $block['id'], ['closed_at' => null], ['closed_at' => $now, 'closed_reason' => 'limit']);

        if ($closed) {
            foreach (InstrumentImporter::TEST_PARTS as $code => $rule) {
                if ($rule['block'] !== $block['block']) {
                    continue;
                }

                $part = $this->part((int) $sitting['id'], $code) ?? $this->openPart($sitting, $code, (int) $block['id']);

                if (! self::isClosed($part) && $this->updateIf(model(InstrumentPartModel::class), (int) $part['id'], ['submitted_at' => null, 'closed_at' => null], ['closed_at' => $now, 'closed_by_limit' => 1])) {
                    $this->scorePart((int) $part['id']);
                }
            }

            $this->closeSittingWhenDone($sitting);
        }

        $db->transComplete();
        $this->forget();

        return $closed;
    }

    /** A block whose parts are all sent closes with reason `submitted`. */
    private function closeBlockWhenSent(array $sitting, int $blockId): void
    {
        $block = model(InstrumentBlockModel::class)->find($blockId);

        if ($block === null || $block['closed_at'] !== null) {
            return;
        }

        $parts = $this->parts((int) $sitting['id']);

        foreach (InstrumentImporter::TEST_PARTS as $code => $rule) {
            if ($rule['block'] === $block['block'] && ! self::isClosed($parts[$code] ?? null)) {
                return;
            }
        }

        $this->updateIf(model(InstrumentBlockModel::class), $blockId, ['closed_at' => null], ['closed_at' => $this->now(), 'closed_reason' => 'submitted']);
    }

    /** The test sitting is sent once its three parts are closed. */
    private function closeSittingWhenDone(array $sitting): void
    {
        $parts = $this->parts((int) $sitting['id']);

        foreach (self::TEST_PARTS as $code) {
            if (! self::isClosed($parts[$code] ?? null)) {
                return;
            }
        }

        $now = $this->now();
        $this->updateIf(model(InstrumentSittingModel::class), (int) $sitting['id'], ['submitted_at' => null], ['submitted_at' => $now, 'updated_at' => $now]);
        $this->forget();
    }

    /** True when the part's block passed its limit but is not closed yet. */
    private function blockOverdue(?array $part): bool
    {
        if ($part === null || $part['block_id'] === null) {
            return false;
        }

        $block   = model(InstrumentBlockModel::class)->find((int) $part['block_id']);
        $sitting = $this->sittingById((int) $part['sitting_id']);

        return $block !== null && $block['closed_at'] === null && (int) ($sitting['is_preview'] ?? 0) === 0
            && self::ms($this->now()) >= self::deadlineMs($block);
    }

    /** Longest view counted on one save: the block's limit, or one hour without a limit. */
    private function viewCapMs(array $sitting, array $part): int
    {
        if ($part['block_id'] !== null) {
            $block = model(InstrumentBlockModel::class)->find((int) $part['block_id']);

            if ($block !== null) {
                return ((int) $block['limit_minutes'] + (int) $block['extra_minutes']) * 60_000;
            }
        }

        return self::UNTIMED_VIEW_CAP_MS;
    }

    /**
     * `gelita:retention:run` (§7.4): closes every block past its limit with no
     * request since, so no data hangs open.
     *
     * @return int blocks closed
     */
    public function closeOverdueBlocks(): int
    {
        $db = db_connect();

        if (! $db->tableExists('instrument_blocks')) {
            return 0;
        }

        $closed = 0;
        $now    = self::ms($this->now());

        foreach (model(InstrumentBlockModel::class)->where('closed_at', null)->findAll() as $block) {
            $sitting = $this->sittingById((int) $block['sitting_id']);

            if ($sitting !== null && (int) $sitting['is_preview'] === 0 && $now >= self::deadlineMs($block) && $this->closeBlockByLimit($sitting, $block)) {
                $closed++;
            }
        }

        return $closed;
    }

    /**
     * Extra minutes for one student's running block (K3, §7.4): 1 to
     * `extra_max` minutes in all, with a reason, never on a closed block.
     * Audit `instrument_time_extended`.
     *
     * @throws \InvalidArgumentException with an Admin language text
     */
    public function extendTime(int $blockId, int $minutes, string $reason, ?int $staffId): void
    {
        $block   = model(InstrumentBlockModel::class)->find($blockId);
        $sitting = $block === null ? null : $this->sittingById((int) $block['sitting_id']);
        $reason  = trim($reason);

        if ($block === null || $sitting === null) {
            throw new \InvalidArgumentException(lang('Admin.instrumentExtendNoBlock'));
        }

        $sitting = $this->sync($sitting);
        $block   = model(InstrumentBlockModel::class)->find($blockId);
        $study   = model(\App\Models\ResearchStudyModel::class)->find((int) $sitting['study_id']) ?? [];
        $max     = $this->settings($study)['extra_max'];

        if ($block['closed_at'] !== null) {
            throw new \InvalidArgumentException(lang('Admin.instrumentExtendClosed'));
        }

        if ($minutes < 1 || (int) $block['extra_minutes'] + $minutes > $max) {
            throw new \InvalidArgumentException(lang('Admin.instrumentExtendRange', [$max]));
        }

        if ($reason === '') {
            throw new \InvalidArgumentException(lang('Admin.instrumentExtendNeedReason'));
        }

        $db = db_connect();
        $db->table('instrument_blocks')->where('id', $blockId)->where('closed_at', null)
            ->set('extra_minutes', 'extra_minutes + ' . $minutes, false)->update();

        if ($db->affectedRows() !== 1) {
            throw new \InvalidArgumentException(lang('Admin.instrumentExtendClosed'));
        }

        $code = (string) db_connect()->table('participants')->select('participant_code')->where('id', (int) $sitting['participant_id'])->get()->getRow('participant_code');

        model(AuditLogModel::class)->record('instrument_time_extended', [
            'staff_user_id' => $staffId,
            'target_type'   => 'instrument_block',
            'target_id'     => (string) $blockId,
            'metadata'      => ['participant_code' => $code, 'occasion' => $sitting['occasion'], 'block' => $block['block'], 'minutes' => $minutes, 'reason' => mb_substr($reason, 0, 200)],
        ]);
    }

    // ------------------------------------------------------------ monitoring

    /**
     * Test status of a student for the monitoring panel and the field sheet
     * template (28 §12, 34 §14): `belum`, `sedang`, `selesai`, `susulan`
     * (sent in a make-up), `hilang` (no test once the make-up window passed).
     * Never scores, answers, or anything about the risk items (§8.2).
     *
     * @param array<string, mixed> $mainSession the main field session of the occasion
     */
    public function testStatus(int $participantId, int $studyId, string $occasion, array $mainSession): string
    {
        $sitting = $this->sitting($participantId, $studyId, $occasion, 'test');

        if ($sitting !== null) {
            if ($sitting['submitted_at'] === null) {
                return 'sedang';
            }

            return (int) $sitting['is_makeup'] === 1 ? 'susulan' : 'selesai';
        }

        $study = model(\App\Models\ResearchStudyModel::class)->find($studyId) ?? [];
        $last  = date('Y-m-d', strtotime((string) $mainSession['session_date'] . ' +' . $this->settings($study)['makeup_days'] . ' days'));

        return date('Y-m-d') > $last ? 'hilang' : 'belum';
    }

    /** True once every step of the occasion's module is closed for the student (monitoring "modul selesai", §8.2). */
    public function moduleDone(int $participantId, int $studyId, string $occasion): bool
    {
        $kinds = $occasion === 'pre' ? ['test', 'questionnaire'] : ['test', 'risk'];

        foreach ($kinds as $kind) {
            $sitting = $this->sitting($participantId, $studyId, $occasion, $kind);

            if ($sitting === null || $sitting['submitted_at'] === null) {
                return false;
            }
        }

        return true;
    }

    // ------------------------------------------------------------ time

    /** Server time with milliseconds (Asia/Jakarta), the module's only clock (§7.5). */
    public function now(): string
    {
        $t = microtime(true);

        return date('Y-m-d H:i:s', (int) $t) . sprintf('.%03d', (int) (($t - floor($t)) * 1000));
    }

    /** Unix milliseconds of a stored time. */
    public static function ms(string $time): int
    {
        try {
            return (int) (new \DateTimeImmutable($time, new \DateTimeZone(config('App')->appTimezone)))->format('Uv');
        } catch (\Exception) {
            return 0;
        }
    }
}
