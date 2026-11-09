<?php

namespace App\Libraries\Derived;

use App\Libraries\BookletRules;

/**
 * Every derived row of one student (35 §4.1, K5): the order is try → item
 * → attempt → challenge → participant → field session, with the
 * field-sheet markers first. Takes the raw data of one student (RunInput)
 * and the run's thresholds; returns rows per derived table, without
 * `run_id` and `dictionary_version` (DerivedDataService adds them).
 *
 * Filters of 35 §6: data after the withdrawal cutoff never enters (K4); the
 * loader already left out other studies, test and simulated accounts,
 * preview sittings, and deleted rows. Tries outside an open field session
 * keep their row with `outside_session = 1`.
 *
 * Pure: no database, clock, configuration, or services (DerivedPurityTest).
 */
final class ParticipantRun
{
    /** L4: one observation covers 20 seconds from its time (11 → Aturan gabung). */
    public const OBSERVATION_MS = 20_000;

    /** Item types scored per dimension (31; 11 → Butir dan skor per dimensi). */
    public const LITERACY_DIMENSIONS = ['D1', 'D2', 'D3'];

    /** @var array<string, list<array<string, mixed>>> */
    private array $rows = [];

    private ?int $limit;

    /** @var array<int, int> game session id => last activity (ms) */
    private array $lastAt = [];

    /** @var array<int, array{hidden: list, pauses: list, narration: list}> */
    private array $sessionTime = [];

    private function __construct(private RunInput $in, private Thresholds $t)
    {
    }

    /** @return array<string, list<array<string, mixed>>> table => rows */
    public static function compute(RunInput $in, Thresholds $t): array
    {
        return (new self($in, $t))->run();
    }

    private function run(): array
    {
        $in  = $this->in;
        $pid = (int) $in->participant['id'];
        $cut = ParticipantCalculator::cutoff($in->participant, $in->consent);

        $this->limit = $cut['withdrawn_at'];
        $this->cut();
        $this->sessionClock();

        // ------------------------------------------------------------ attempts and tries
        $classes = AttemptCalculator::classify($in->attempts);
        $scored  = array_map(static fn (array $c): bool => $c['is_scored_attempt'] === 1, $classes);
        $numbers = TryCalculator::number($in->tries, $scored);
        $windows = [];

        foreach ($in->attempts as $id => $attempt) {
            $segments = array_values(array_filter($in->segments, static fn (array $s): bool => (int) $s['attempt_id'] === (int) $id));

            if ($segments === []) {
                $segments = [['attempt_id' => $id, 'session_id' => $attempt['session_id'], 'field_session_id' => $in->sessions[(int) $attempt['session_id']]['field_session_id'] ?? null, 'segment_no' => 1, 'opened_at' => $attempt['started_at'], 'left_at' => $attempt['completed_at']]];
            }

            $windows[(int) $id] = AttemptCalculator::windows($segments, $this->attemptLastAt((int) $id), $this->limit);
        }

        $flags   = new FieldSheetFlags($this->mineRows(), $in->importedSheets, $in->seats, array_map(static fn (array $f): ?string => $f['date'] ?? null, $in->fieldSessions));
        $tryRows = [];

        foreach ($in->tries as $id => $try) {
            $attempt = $in->attempts[(int) $try['attempt_id']] ?? null;

            if ($attempt === null) {
                continue;
            }

            $engine  = (string) ($in->nodes[(int) $attempt['node_id']]['engine'] ?? '');
            $session = $this->sessionAt($windows[(int) $attempt['id']], (int) $try['occurred_at'], (int) $attempt['session_id']);
            $fs      = $in->sessions[$session]['field_session_id'] ?? null;
            $fs      = $fs === null ? null : (int) $fs;

            $tryRows[(int) $id] = TryCalculator::row($try, $engine, $numbers[(int) $id], [
                'field_session_id'      => $fs,
                'is_scored_attempt'     => $classes[(int) $attempt['id']]['is_scored_attempt'],
                'is_retake'             => empty($attempt['is_retake']) ? 0 : 1,
                'rapid_ms'              => $this->t->rapidMs,
                'item_hint_before'      => $this->itemHintBefore($try),
                'item_narration_before' => $this->itemNarrationBefore($try),
                'outside_session'       => $this->outside($fs, (int) $try['occurred_at']) ? 1 : 0,
                'flags'                 => $flags->at($fs, (int) $try['occurred_at']),
            ]);
        }

        $this->rows['derived_tries'] = array_values($tryRows);

        // ------------------------------------------------------------ challenges in order, items
        $nodes = $in->nodes;
        uasort($nodes, static fn (array $a, array $b): int => [$a['level_seq'], $a['sequence']] <=> [$b['level_seq'], $b['sequence']]);
        $opened     = $this->openedRegions();
        $statuses   = [];
        $itemRows   = [];
        $itemTimes  = [];
        $challenges = [];

        foreach ($nodes as $nodeId => $node) {
            $nodeAttempts = array_filter($in->attempts, static fn (array $a): bool => (int) $a['node_id'] === (int) $nodeId);
            $scoredOnes   = array_values(array_filter($nodeAttempts, static fn (array $a): bool => $classes[(int) $a['id']]['is_scored_attempt'] === 1));
            usort($scoredOnes, static fn (array $a, array $b): int => [$a['started_at'], $a['id']] <=> [$b['started_at'], $b['id']]);
            $previous = $statuses[$node['level_seq'] . ':' . ((int) $node['sequence'] - 1)] ?? null;
            $open     = in_array((int) $node['level_seq'], $opened, true) && ((int) $node['sequence'] === 1 || in_array($previous, ['solved', 'exhausted'], true));
            $variant  = $scoredOnes[0]['variant_group'] ?? $this->bookletVariant((int) $node['level_seq']);
            $rows     = [];
            $missing  = $scoredOnes === [] ? ($open ? 'not_started' : 'locked') : 'no_try';
            $scoredIds = array_map(static fn (array $a): int => (int) $a['id'], $scoredOnes);

            foreach ($in->items as $itemId => $item) {
                if ((int) $item['node_id'] !== (int) $nodeId || $variant === null || ! in_array((string) $item['variant_group'], [$variant, 'x'], true)) {
                    continue;
                }

                $tries = [];

                foreach ($in->tries as $tid => $try) {
                    if ((int) $try['item_id'] === (int) $itemId && in_array((int) $try['attempt_id'], $scoredIds, true) && $numbers[(int) $tid]['scored_try_no'] !== null) {
                        $tries[$numbers[(int) $tid]['scored_try_no']] = $try + ['row' => $tryRows[(int) $tid]];
                    }
                }

                ksort($tries);
                $tries  = array_values($tries);
                $timing = [null, null];

                if (in_array($item['interaction_type'], ['find_object', 'find_part', 'scenario_choice'], true) && $tries !== []) {
                    [$timing, $itemTimes[(int) $itemId]] = $this->itemTiming($tries, $windows);
                }

                $taps = in_array($item['interaction_type'], ['find_object', 'find_part'], true)
                    ? array_values(array_map(static fn (array $e): array => (array) $e['payload'], array_filter($in->events, static fn (array $e): bool => $e['type'] === 'empty_tap' && (int) ($e['item_id'] ?? 0) === (int) $itemId && in_array((int) ($e['attempt_id'] ?? 0), $scoredIds, true))))
                    : null;

                $rows[] = ItemCalculator::row($item, $tries, $missing, $taps, $timing);
            }

            $status = ChallengeCalculator::status($scoredOnes, $rows, $open);
            $statuses[$node['level_seq'] . ':' . $node['sequence']] = $status;
            $itemRows = [...$itemRows, ...$rows];

            $failed = count(array_filter($in->events, static fn (array $e): bool => in_array($e['type'], ['clue_failed', 'scenario_failed'], true) && in_array((int) ($e['attempt_id'] ?? 0), $scoredIds, true)));
            $left   = 0;

            foreach ($scoredOnes as $attempt) {
                $left += $this->leftCount($attempt, $windows[(int) $attempt['id']]);
            }

            $challenges[] = ChallengeCalculator::row((int) $nodeId, $status, $failed, $left, $this->closedMeeting($scoredOnes, $windows));
        }

        $this->rows['derived_items']      = $itemRows;
        $this->rows['derived_challenges'] = $challenges;

        // ------------------------------------------------------------ attempt rows
        $attemptRows = [];

        foreach ($in->attempts as $id => $attempt) {
            $win     = $windows[(int) $id];
            $first   = $win[0] ?? null;
            $session = $first['session_id'] ?? (int) $attempt['session_id'];

            $attemptRows[] = AttemptCalculator::row($attempt, [
                'is_scored_attempt'    => $classes[(int) $id]['is_scored_attempt'],
                'windows'              => $win,
                'timeline'             => $this->timeline($win),
                'cumulative_active_ms' => $this->cumulative((int) $attempt['started_at']),
                'events'               => array_values(array_filter($in->events, static fn (array $e): bool => (int) ($e['attempt_id'] ?? 0) === (int) $id)),
                'audio'                => array_values(array_filter($in->audio, static fn (array $a): bool => (int) ($a['attempt_id'] ?? 0) === (int) $id)),
                'meeting_no'           => isset($in->sessions[$session]['meeting_no']) ? (int) $in->sessions[$session]['meeting_no'] : null,
                'field_session_id'     => $first['field_session_id'] ?? (isset($in->sessions[$session]['field_session_id']) ? (int) $in->sessions[$session]['field_session_id'] : null),
            ]);
        }

        $this->rows['derived_attempts'] = $attemptRows;

        // ------------------------------------------------------------ observations, field sessions
        $this->rows['derived_observations'] = $this->observations($windows, $itemTimes);
        $this->rows['derived_field_sessions'] = $this->fieldSessions();

        // ------------------------------------------------------------ tests, participant
        $tests = [];

        foreach ($in->sittings as $sitting) {
            if ($sitting['kind'] !== 'test') {
                continue;
            }

            foreach ((array) $sitting['responses'] as $response) {
                $tests[] = TestResponseCalculator::row($response, $this->t->testRapidMs);
            }
        }

        $this->rows['derived_test_responses'] = $tests;
        $this->rows['derived_participants']   = [$this->participantRow($cut, $itemRows, $challenges)];

        foreach ($this->rows as $table => $rows) {
            $this->rows[$table] = array_map(static fn (array $row): array => ['participant_id' => $pid] + $row, $rows);
        }

        return $this->rows;
    }

    // ------------------------------------------------------------ filters

    /** Data after the withdrawal cutoff is left out (K4); attempts and segments are cut at it. */
    private function cut(): void
    {
        $limit = $this->limit;

        if ($limit === null) {
            return;
        }

        $in   = $this->in;
        $keep = static fn (mixed $at): bool => $at === null || (int) $at <= $limit;

        $in->tries    = array_filter($in->tries, static fn (array $t): bool => $keep($t['occurred_at']));
        $in->events   = array_values(array_filter($in->events, static fn (array $e): bool => $keep($e['at'])));
        $in->audio    = array_values(array_filter($in->audio, static fn (array $a): bool => $keep($a['at'])));
        $in->sessions = array_filter($in->sessions, static fn (array $s): bool => $keep($s['started_at']));
        $in->fieldRows = array_values(array_filter($in->fieldRows, static fn (array $r): bool => $keep($r['started_at'] ?? null)));

        foreach ($in->attempts as $id => $attempt) {
            if (! $keep($attempt['started_at'])) {
                unset($in->attempts[$id]);
            } elseif ($attempt['completed_at'] !== null && $attempt['completed_at'] > $limit) {
                $in->attempts[$id]['status']       = 'in_progress';
                $in->attempts[$id]['completed_at'] = null;
            }
        }

        $in->segments = array_values(array_filter(array_map(static function (array $s) use ($limit): array {
            if ($s['left_at'] === null || $s['left_at'] > $limit) {
                $s['left_at'] = max((int) $s['opened_at'], $limit);
            }

            return $s;
        }, $in->segments), static fn (array $s): bool => $keep($s['opened_at'])));

        foreach ($in->sittings as $i => $sitting) {
            if (! $keep($sitting['started_at'] ?? null)) {
                unset($in->sittings[$i]);

                continue;
            }

            if (! $keep($sitting['submitted_at'] ?? null)) {
                $in->sittings[$i]['submitted_at'] = null;
            }

            foreach ((array) ($sitting['parts'] ?? []) as $part => $row) {
                foreach (['submitted_at', 'closed_at'] as $column) {
                    if (! $keep($row[$column] ?? null)) {
                        $in->sittings[$i]['parts'][$part][$column] = null;
                    }
                }
            }

            $in->sittings[$i]['responses'] = array_values(array_filter((array) $sitting['responses'], static fn (array $r): bool => $keep($r['first_shown_at'] ?? null)));
        }
    }

    // ------------------------------------------------------------ time

    /** Last activity per game session, and its hidden, pause, and narration stretches. */
    private function sessionClock(): void
    {
        $in   = $this->in;
        $last = [];
        $note = static function (int $session, mixed $at) use (&$last): void {
            if ($at !== null) {
                $last[$session] = max($last[$session] ?? (int) $at, (int) $at);
            }
        };

        foreach ($in->sessions as $id => $session) {
            $note((int) $id, $session['started_at']);
        }

        foreach ($in->events as $event) {
            $note((int) $event['session_id'], $event['at']);
        }

        foreach ($in->audio as $audio) {
            $note((int) $audio['session_id'], $audio['at']);
        }

        foreach ($in->tries as $try) {
            $attempt = $in->attempts[(int) $try['attempt_id']] ?? null;

            if ($attempt !== null) {
                $session = $this->sessionOfTime((int) $try['attempt_id'], (int) $try['occurred_at']) ?? (int) $attempt['session_id'];
                $note($session, $try['occurred_at']);
            }
        }

        foreach ($in->segments as $segment) {
            $note((int) $segment['session_id'], $segment['left_at']);
        }

        $this->lastAt = $last;

        foreach ($last as $session => $end) {
            $events = array_values(array_filter($in->events, static fn (array $e): bool => (int) $e['session_id'] === $session));
            $plays  = [];

            foreach ($in->audio as $audio) {
                if ((int) $audio['session_id'] !== $session || ! in_array($audio['action'], ['play', 'replay', 'autoplay', 'progress', 'completed', 'ended'], true)) {
                    continue;
                }

                $key           = ($audio['audio_asset_id'] ?? 0) . ':' . ($audio['play_index'] ?? 1);
                $plays[$key] ??= [PHP_INT_MAX, 0];
                $plays[$key][0] = min($plays[$key][0], (int) $audio['at']);
                $plays[$key][1] = max($plays[$key][1], (int) ($audio['listened_ms'] ?? 0));
            }

            $this->sessionTime[$session] = [
                'hidden'    => Timeline::hiddenStretches(array_map(static fn (array $e): array => ['type' => $e['type'], 'at' => (int) $e['at']], array_filter($events, static fn (array $e): bool => in_array($e['type'], ['page_hidden', 'page_visible'], true))), $end),
                'pauses'    => array_values(array_map(static fn (array $e): array => [(int) $e['at'] - (int) ($e['payload']['idle_ms'] ?? 0), (int) $e['at']], array_filter($events, static fn (array $e): bool => $e['type'] === 'input_idle'))),
                'narration' => array_values(array_map(static fn (array $p): array => [$p[0], $p[0] + $p[1]], $plays)),
            ];
        }
    }

    /** The game session whose segment of this attempt holds $at (null when none). */
    private function sessionOfTime(int $attemptId, int $at): ?int
    {
        $best = null;

        foreach ($this->in->segments as $segment) {
            if ((int) $segment['attempt_id'] === $attemptId && (int) $segment['opened_at'] <= $at && ($segment['left_at'] === null || $at <= (int) $segment['left_at'])) {
                $best = (int) $segment['session_id'];
            }
        }

        return $best;
    }

    /** @param list<array> $windows */
    private function sessionAt(array $windows, int $at, int $fallback): int
    {
        foreach ($windows as $window) {
            if ($window[0] <= $at && $at <= $window[1]) {
                return $window['session_id'];
            }
        }

        foreach (array_reverse($windows) as $window) {
            if ($window[0] <= $at) {
                return $window['session_id'];
            }
        }

        return $fallback;
    }

    /** @return array<int, int> game session id => the attempt's last activity there */
    private function attemptLastAt(int $attemptId): array
    {
        $in   = $this->in;
        $last = [];
        $note = static function (int $session, int $at) use (&$last): void {
            $last[$session] = max($last[$session] ?? $at, $at);
        };

        foreach ($in->events as $event) {
            if ((int) ($event['attempt_id'] ?? 0) === $attemptId) {
                $note((int) $event['session_id'], (int) $event['at']);
            }
        }

        foreach ($in->audio as $audio) {
            if ((int) ($audio['attempt_id'] ?? 0) === $attemptId) {
                $note((int) $audio['session_id'], (int) $audio['at']);
            }
        }

        foreach ($in->tries as $try) {
            if ((int) $try['attempt_id'] === $attemptId) {
                $session = $this->sessionOfTime($attemptId, (int) $try['occurred_at']) ?? (int) $in->attempts[$attemptId]['session_id'];
                $note($session, (int) $try['occurred_at']);
            }
        }

        return $last;
    }

    /** Timeline::active() over windows of known game sessions. @param list<array> $windows */
    private function timeline(array $windows): array
    {
        $hidden = $pauses = $narration = [];

        foreach (array_unique(array_map(static fn (array $w): int => (int) $w['session_id'], $windows)) as $session) {
            $time      = $this->sessionTime[$session] ?? ['hidden' => [], 'pauses' => [], 'narration' => []];
            $hidden    = [...$hidden, ...$time['hidden']];
            $pauses    = [...$pauses, ...$time['pauses']];
            $narration = [...$narration, ...$time['narration']];
        }

        return Timeline::active(array_map(static fn (array $w): array => [$w[0], $w[1]], $windows), $hidden, $pauses, $narration, $this->t->idleMs);
    }

    /** Game-session windows, optionally cut at $before. @return list<array> */
    private function sessionWindows(?int $before = null, ?int $fieldSession = null): array
    {
        $out = [];

        foreach ($this->in->sessions as $id => $session) {
            if ($fieldSession !== null && (int) ($session['field_session_id'] ?? 0) !== $fieldSession) {
                continue;
            }

            $start = (int) $session['started_at'];
            $end   = $this->lastAt[(int) $id] ?? $start;

            if ($before !== null) {
                $end = min($end, $before);
            }

            if ($end > $start) {
                $out[] = [$start, $end, 'session_id' => (int) $id, 'field_session_id' => $session['field_session_id'] ?? null];
            }
        }

        return $out;
    }

    /**
     * Stretches of one attempt where $itemId was on screen (35 §6.2; owner,
     * 9 November 2026): from each `item_shown` of the item to the next
     * `item_shown` of the attempt, or the end of the window it falls in.
     * Null when the attempt has no `item_shown` (data from before the event).
     *
     * @param list<array> $win the attempt's windows
     *
     * @return list<array{0: int, 1: int}>|null
     */
    private function shownSpans(int $attemptId, int $itemId, array $win): ?array
    {
        $shown = array_values(array_filter($this->in->events, static fn (array $e): bool => $e['type'] === 'item_shown' && (int) ($e['attempt_id'] ?? 0) === $attemptId && $e['at'] !== null));

        if ($shown === []) {
            return null;
        }

        usort($shown, static fn (array $a, array $b): int => [(int) $a['at'], (int) $a['id']] <=> [(int) $b['at'], (int) $b['id']]);
        $out = [];

        foreach ($shown as $i => $event) {
            if ((int) ($event['item_id'] ?? 0) !== $itemId) {
                continue;
            }

            $from = (int) $event['at'];
            $to   = null;

            foreach ($win as $w) {
                if ($w[0] <= $from && $from <= $w[1]) {
                    $to = $w[1];
                }
            }

            $next = $shown[$i + 1]['at'] ?? null;
            $to   = $next !== null ? min($to ?? (int) $next, (int) $next) : $to;

            if ($to !== null && $to > $from) {
                $out[] = [$from, $to];
            }
        }

        return $out;
    }

    /** Active time of every game session before $start (covariate, 11 → Analitik 5). */
    private function cumulative(int $start): int
    {
        return $this->timeline($this->sessionWindows($start))['active_ms'];
    }

    /**
     * Item time of Cari Objek and Skenario Digital (35 §6.2): from the first
     * try's ready time (in its segment) to the try that closes the item, or
     * the end of the attempt's windows; only inside the attempt's windows.
     *
     * @return array{0: array{0: ?int, 1: ?int}, 1: ?array{0: int, 1: int, attempt_id: int}}
     */
    private function itemTiming(array $tries, array $windows): array
    {
        $first = $tries[0];
        $win   = $windows[(int) $first['attempt_id']] ?? [];

        if ($win === [] || ($first['ready_ms'] ?? null) === null) {
            return [[null, null], null];
        }

        $start = null;

        foreach ($win as $w) {
            if ($w[0] <= (int) $first['occurred_at'] && (int) $first['occurred_at'] <= $w[1]) {
                $start = $w[0] + (int) $first['ready_ms'];
            }
        }

        $start ??= $win[0][0] + (int) $first['ready_ms'];
        $end     = null;

        foreach ($tries as $try) {
            if ((int) $try['is_correct'] === 1 || $try === end($tries) && count($tries) >= TryCalculator::MAX_TRIES) {
                $end = (int) $try['occurred_at'];
                break;
            }
        }

        $end ??= max(array_map(static fn (array $w): int => $w[1], $win));
        $parts = [];
        // Coordinator follow-up (owner, 9 November 2026): with `item_shown` events only the stretches where
        // this item was on screen count; without them (older data) the whole span, as before
        $span  = $this->shownSpans((int) $first['attempt_id'], (int) $first['item_id'], $win) ?? [[$start, max($start, $end)]];
        $span  = Timeline::intersect([[$start, max($start, $end)]], $span);

        foreach (Timeline::intersect($span, array_map(static fn (array $w): array => [$w[0], $w[1]], $win)) as $part) {
            foreach ($win as $w) {
                if ($part[0] >= $w[0] && $part[1] <= $w[1]) {
                    $parts[] = [$part[0], $part[1], 'session_id' => $w['session_id']];
                    break;
                }
            }
        }

        $time = $this->timeline($parts);

        return [[$time['total_ms'], $time['active_ms']], [$start, max($start, $end), 'attempt_id' => (int) $first['attempt_id']]];
    }

    // ------------------------------------------------------------ helpers per row kind

    private function itemHintBefore(array $try): int
    {
        foreach ($this->in->events as $event) {
            if ($event['type'] !== 'hint_opened' || (int) ($event['attempt_id'] ?? 0) !== (int) $try['attempt_id'] || $event['at'] >= $try['occurred_at']) {
                continue;
            }

            $item = $event['item_id'] ?? ($this->in->hintItems[(int) ($event['payload']['hint_id'] ?? 0)] ?? null);

            if ((int) $item === (int) $try['item_id']) {
                return 1;
            }
        }

        return 0;
    }

    private function itemNarrationBefore(array $try): int
    {
        foreach ($this->in->audio as $audio) {
            if ((int) ($audio['attempt_id'] ?? 0) === (int) $try['attempt_id'] && in_array($audio['action'], ['play', 'replay'], true)
                && (int) ($this->in->itemAudio[(int) ($audio['audio_asset_id'] ?? 0)] ?? 0) === (int) $try['item_id'] && $audio['at'] < $try['occurred_at']) {
                return 1;
            }
        }

        return 0;
    }

    /** A try outside an open field session: no field session, or outside its hours (35 §6 filter 4). */
    private function outside(?int $fieldSession, int $at): bool
    {
        $fs = $fieldSession === null ? null : ($this->in->fieldSessions[$fieldSession] ?? null);

        if ($fs === null) {
            return true;
        }

        $end = $fs['closed_at'] !== null && $fs['closed_at'] !== '' ? min((int) $fs['closes_at'], (int) $fs['closed_at']) : (int) $fs['closes_at'];

        return $at < (int) $fs['opens_at'] || $at > $end;
    }

    /** Regions opened for the student: the plan regions of the field sessions attended (28 §9). @return list<int> */
    private function openedRegions(): array
    {
        $out = [];

        foreach ($this->in->sessions as $session) {
            $fs = $this->in->fieldSessions[(int) ($session['field_session_id'] ?? 0)] ?? null;

            foreach ((array) ($fs['regions'] ?? []) as $region) {
                $out[(int) $region] = (int) $region;
            }
        }

        return array_values($out);
    }

    private function bookletVariant(int $levelSeq): ?string
    {
        $booklet = (string) ($this->in->participant['booklet'] ?? '');

        return BookletRules::isBooklet($booklet) ? BookletRules::variantFor($booklet, $levelSeq) : null;
    }

    /** Skipped (b) of one scored attempt (ChallengeCalculator::leftCount()). */
    private function leftCount(array $attempt, array $windows): int
    {
        $in      = $this->in;
        $id      = (int) $attempt['id'];
        $close   = $attempt['status'] === 'completed' ? $attempt['completed_at'] : null;
        $fsOf    = static fn (int $session): ?int => isset($in->sessions[$session]['field_session_id']) ? (int) $in->sessions[$session]['field_session_id'] : null;
        $abandon = [];
        $active  = [];

        foreach ($in->events as $event) {
            if ((int) ($event['attempt_id'] ?? 0) !== $id) {
                continue;
            }

            if ($event['type'] === 'challenge_abandoned') {
                if ($close === null || $event['at'] <= $close) {
                    $abandon[] = ['at' => (int) $event['at'], 'field_session_id' => $fsOf((int) $event['session_id'])];
                }
            } else {
                $active[] = [(int) $event['at'], $fsOf((int) $event['session_id'])];
            }
        }

        foreach ($in->tries as $try) {
            if ((int) $try['attempt_id'] === $id) {
                $active[] = [(int) $try['occurred_at'], $fsOf($this->sessionAt($windows, (int) $try['occurred_at'], (int) $attempt['session_id']))];
            }
        }

        foreach ($windows as $window) {
            $active[] = [$window[0], $window['field_session_id'] ?? $fsOf($window['session_id'])];
        }

        return ChallengeCalculator::leftCount($abandon, $active);
    }

    /** Meeting in which the challenge first closed. */
    private function closedMeeting(array $scored, array $windows): ?int
    {
        foreach ($scored as $attempt) {
            if ($attempt['status'] !== 'completed' || $attempt['completed_at'] === null) {
                continue;
            }

            $session = $this->sessionAt($windows[(int) $attempt['id']], (int) $attempt['completed_at'], (int) $attempt['session_id']);
            $meeting = $this->in->sessions[$session]['meeting_no'] ?? null;

            return $meeting === null ? null : (int) $meeting;
        }

        return null;
    }

    /** Field-sheet rows that concern the student (theirs, or naming their seat). */
    private function mineRows(): array
    {
        return $this->in->fieldRows;
    }

    /** derived_observations: L4 rows of the student, with the attempt and item active then. */
    private function observations(array $windows, array $itemTimes): array
    {
        $out = [];

        foreach ($this->in->fieldRows as $row) {
            if ($row['sheet'] !== 'l4_bromp' || ! $this->isMine($row) || $row['started_at'] === null) {
                continue;
            }

            $from    = (int) $row['started_at'];
            $to      = $from + self::OBSERVATION_MS;
            $attempt = null;
            $item    = null;

            foreach ($windows as $attemptId => $list) {
                foreach ($list as $w) {
                    if ($w[0] < $to && $w[1] > $from) {
                        $attempt = $attemptId;
                        break 2;
                    }
                }
            }

            foreach ($itemTimes as $itemId => $span) {
                if ($span !== null && $span['attempt_id'] === $attempt && $span[0] < $to && $span[1] > $from) {
                    $item = $itemId;
                    break;
                }
            }

            $out[] = ['field_sheet_row_id' => (int) $row['id'], 'attempt_id' => $attempt, 'challenge_item_id' => $item];
        }

        return $out;
    }

    private function isMine(array $row): bool
    {
        $seat = $this->in->seats[(int) $row['field_session_id']] ?? null;

        return ! empty($row['mine']) || ($seat !== null && $row['seat_no'] !== null && (int) $row['seat_no'] === (int) $seat);
    }

    /** derived_field_sessions: one row per field session of the student (35 §6.6). */
    private function fieldSessions(): array
    {
        $in  = $this->in;
        $out = [];

        foreach ($in->fieldSessions as $fsId => $fs) {
            $windows  = $this->sessionWindows(null, (int) $fsId);
            $attended = array_filter($in->sessions, static fn (array $s): bool => (int) ($s['field_session_id'] ?? 0) === (int) $fsId) !== [];
            $time     = $this->timeline($windows);
            $sessions = array_map(static fn (array $w): int => $w['session_id'], $windows);
            $library  = [];
            $pages    = 0;

            foreach ($sessions as $session) {
                $events = array_values(array_filter($in->events, static fn (array $e): bool => (int) $e['session_id'] === $session));
                usort($events, static fn (array $a, array $b): int => [$a['at'], $a['id'] ?? 0] <=> [$b['at'], $b['id'] ?? 0]);

                foreach ($events as $i => $event) {
                    if ($event['type'] === 'library_page_viewed') {
                        $pages++;
                        $library[] = [(int) $event['at'], (int) ($events[$i + 1]['at'] ?? $this->lastAt[$session] ?? $event['at']), 'session_id' => $session];
                    }
                }
            }

            $l1      = null;
            $l4count = 0;

            foreach ($in->fieldRows as $row) {
                if ((int) $row['field_session_id'] !== (int) $fsId) {
                    continue;
                }

                if ($row['sheet'] === 'l1_kondisi') {
                    $l1 = (array) $row['values'];
                }

                if ($row['sheet'] === 'l4_bromp' && $this->isMine($row)) {
                    $l4count++;
                }
            }

            $int = static fn (mixed $v): ?int => is_numeric($v) ? (int) $v : null;
            $of  = $int($l1['headset_of'] ?? null);

            $out[] = [
                'field_session_id'  => (int) $fsId,
                'attended'          => $attended ? 1 : 0,
                'session_total_ms'  => $attended ? $time['total_ms'] : null,
                'session_active_ms' => $attended ? $time['active_ms'] : null,
                'library_pages'     => $attended ? $pages : null,
                'library_ms'        => $attended ? $this->timeline($library)['active_ms'] : null,
                'headset_share'     => $of !== null && $of > 0 && $int($l1['headset_count'] ?? null) !== null ? round($int($l1['headset_count']) / $of, 4) : null,
                'internet'          => TryCalculator::blank($l1['internet'] ?? null),
                'noise'             => TryCalculator::blank($l1['noise_level'] ?? null),
                'teacher_present'   => TryCalculator::blank($l1['teacher_presence'] ?? null),
                'minutes_available' => $int($l1['minutes_available'] ?? null),
                'minutes_used'      => $int($l1['minutes_used'] ?? null),
                'l4_observations'   => in_array('l4_bromp', $in->importedSheets[(int) $fsId] ?? [], true) ? $l4count : null,
            ];
        }

        return $out;
    }

    /** derived_participants (35 §6.5). */
    private function participantRow(array $cut, array $items, array $challenges): array
    {
        $in      = $this->in;
        $p       = $in->participant;
        $consent = ParticipantCalculator::consentOk($p, $in->consent);
        $pilot   = str_starts_with(strtoupper((string) ($p['school_code'] ?? '')), 'U') ? 1 : 0;
        $dims    = [];

        foreach ($in->items as $id => $item) {
            $dims[(int) $id] = (string) ($item['dimension'] ?? '');
        }

        $literacy = array_filter($items, static fn (array $r): bool => in_array($dims[(int) $r['challenge_item_id']] ?? '', self::LITERACY_DIMENSIONS, true) && $r['first_try_score'] !== null);
        $clean    = array_filter($literacy, static fn (array $r): bool => (int) ($r['shared_device'] ?? 0) !== 1);
        $flagged  = array_filter($items, static fn (array $r): bool => $r['rapid_guess'] !== null);
        $done     = static fn (array $c): bool => in_array($c['completion_status'], ['solved', 'exhausted'], true);
        $byLevel  = [];
        $scoredDone = 0;

        foreach ($challenges as $c) {
            $node = $in->nodes[(int) $c['challenge_node_id']];
            $byLevel[(int) $node['level_seq']][] = $done($c);

            if ($done($c) && $node['engine'] !== 'puzzle') {
                $scoredDone++;
            }
        }

        $answers = static function (array $sitting): array {
            $out = [];

            foreach ((array) $sitting['responses'] as $r) {
                $out[(string) $r['unit']] = (string) $r['response'];
            }

            return $out;
        };
        $questionnaire = null;
        $attitude      = null;
        $tests         = [];

        foreach ($in->sittings as $sitting) {
            match ($sitting['kind']) {
                'questionnaire' => $questionnaire = $answers($sitting),
                'attitude'      => $attitude = ($sitting['submitted_at'] ?? null) !== null ? $answers($sitting) : $attitude,
                'test'          => $tests[] = $sitting,
                default         => null,
            };
        }

        $p5 = false;

        foreach ($in->sessions as $session) {
            $p5 = $p5 || (int) ($in->fieldSessions[(int) ($session['field_session_id'] ?? 0)]['meeting_no'] ?? 0) === 5;
        }

        // Owner, 9 November 2026: `absen` only once a P5 meeting of the student (main or make-up) has opened by the run time; before that empty
        $p5Held = $p5;

        foreach ($in->fieldSessions as $fs) {
            $p5Held = $p5Held || ((int) $fs['meeting_no'] === 5 && $in->asOf !== null && $fs['opens_at'] !== null && (int) $fs['opens_at'] <= $in->asOf);
        }

        return [
            'consent_ok'                  => $consent,
            'withdrawn_on'                => $cut['withdrawn_on'],
            'withdrawn_at'                => Clock::stored($cut['withdrawn_at']),
            'cutoff_basis'                => $cut['cutoff_basis'],
            'is_pilot'                    => $pilot,
            'analysable'                  => $consent === 1 && $pilot === 0 && count($clean) >= Thresholds::ANALYSABLE_MIN_ITEMS ? 1 : 0,
            'complete'                    => count($literacy) >= Thresholds::LITERACY_ITEMS ? 1 : 0,
            'rapid_share'                 => $flagged === [] ? null : round(count(array_filter($flagged, static fn (array $r): bool => (int) $r['rapid_guess'] === 1)) / count($flagged), 4),
            'regions_completed'           => count(array_filter($byLevel, static fn (array $list): bool => ! in_array(false, $list, true))),
            'challenges_completed'        => count(array_filter($challenges, $done)),
            'scored_challenges_completed' => $scoredDone,
            'registration_corrected'      => ($p['corrected_at'] ?? null) !== null ? 1 : 0,
        ] + ParticipantCalculator::questionnaire($questionnaire, $in->questionnaireItems)
          + ParticipantCalculator::tests($tests)
          + ParticipantCalculator::attitude($attitude)
          + ['sikap_missing_reason' => $attitude !== null ? null : ($p5 ? 'belum_tuntas' : ($p5Held ? 'absen' : null))];
    }
}
