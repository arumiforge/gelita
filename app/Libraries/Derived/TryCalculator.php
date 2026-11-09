<?php

namespace App\Libraries\Derived;

/**
 * `derived_tries`, one row per try (35 §6.1; 11 → Pengerjaan pertama,
 * Analitik 6 and 7). Pure.
 */
final class TryCalculator
{
    /** Engines whose tries have a decision time from `ready_ms` (11 → Analitik 6). */
    public const TIMED_ENGINES = ['boleh', 'cari'];

    public const MAX_TRIES = 3;

    /**
     * Scored try numbers of the first attempt (fallback rule): the tries of
     * one item in the scored attempts, ordered by time across attempts; the
     * first three are numbered 1–3, later ones get `extra_try`.
     *
     * @param array<int, array<string, mixed>> $tries      id => try
     * @param array<int, bool>                 $scoredAttempts attempt id => scored
     *
     * @return array<int, array{scored_try_no: ?int, anomaly: ?string}>
     */
    public static function number(array $tries, array $scoredAttempts): array
    {
        $byItem = [];
        $out    = [];

        foreach ($tries as $id => $try) {
            $out[$id] = ['scored_try_no' => null, 'anomaly' => null];

            if (! empty($scoredAttempts[(int) $try['attempt_id']])) {
                $byItem[(int) $try['item_id']][] = $try;
            }
        }

        foreach ($byItem as $list) {
            usort($list, static fn (array $a, array $b): int => [$a['occurred_at'], $a['id']] <=> [$b['occurred_at'], $b['id']]);

            foreach ($list as $i => $try) {
                $out[(int) $try['id']] = $i < self::MAX_TRIES
                    ? ['scored_try_no' => $i + 1, 'anomaly' => null]
                    : ['scored_try_no' => null, 'anomaly' => 'extra_try'];
            }
        }

        return $out;
    }

    /** Decision time: last input minus ready; empty when either is missing or the result is negative. */
    public static function decision(mixed $ready, mixed $last): ?int
    {
        if ($ready === null || $last === null || $ready === '' || $last === '') {
            return null;
        }

        $ms = (int) $last - (int) $ready;

        return $ms < 0 ? null : $ms;
    }

    /** 1 below the threshold, 0 at or above it, empty without a decision time (3000 is not a rapid guess, 2999 is). */
    public static function rapid(?int $decision, int $threshold): ?int
    {
        return $decision === null ? null : ($decision < $threshold ? 1 : 0);
    }

    /**
     * @param array<string, mixed> $try      a RunInput try
     * @param array{scored_try_no: ?int, anomaly: ?string} $number
     * @param array<string, mixed> $context  is_scored_attempt, is_retake, rapid_ms, item_hint_before, item_narration_before,
     *                                       flags (FieldSheetFlags::at()), outside_session, field_session_id
     *
     * @return array<string, mixed>
     */
    public static function row(array $try, string $engine, array $number, array $context): array
    {
        $correct = (int) $try['is_correct'] === 1;
        $puzzle  = $engine === 'puzzle';

        if ($engine === 'skenario') {
            $decision       = self::decision($try['ready_ms'] ?? null, $try['action_last_input_ms'] ?? null);
            $reasonDecision = self::decision($try['reason_ready_ms'] ?? null, $try['reason_last_input_ms'] ?? null);
        } else {
            $decision       = in_array($engine, self::TIMED_ENGINES, true) ? self::decision($try['ready_ms'] ?? null, $try['last_input_ms'] ?? null) : null;
            $reasonDecision = null;
        }

        $rapid     = self::rapid($decision, (int) $context['rapid_ms']);
        $errorCode = $correct || $puzzle ? null : self::blank($try['error_code_latest'] ?? null);
        $tryNo     = $number['scored_try_no'] ?? (int) $try['try_no'];

        return [
            'try_id'                => (int) $try['id'],
            'attempt_id'            => (int) $try['attempt_id'],
            'field_session_id'      => $context['field_session_id'],
            'is_scored_attempt'     => (int) $context['is_scored_attempt'],
            'is_retake'             => (int) $context['is_retake'],
            'scored_try_no'         => $number['scored_try_no'],
            'anomaly'               => $number['anomaly'],
            'error_code'            => $errorCode,
            'error_code_at_try'     => $puzzle ? null : self::blank($try['error_code'] ?? null),
            'decision_ms'           => $decision,
            'rapid_guess'           => $rapid,
            'reason_decision_ms'    => $reasonDecision,
            'reason_rapid'          => $engine === 'skenario' ? self::rapid($reasonDecision, (int) $context['rapid_ms']) : null,
            'error_code_analysis'   => ! $correct && ! $puzzle && $rapid === 1 ? 'NON-TEB' : $errorCode,
            'last_try_exhausted'    => ! $correct && ! $puzzle && $tryNo === self::MAX_TRIES ? 1 : 0,
            'hint_before'           => (int) ($try['hint_opened_before'] ?? 0),
            'item_hint_before'      => (int) $context['item_hint_before'],
            'item_narration_before' => (int) $context['item_narration_before'],
            'reason_code'           => self::blank($try['reason_code'] ?? null),
            'action_score'          => isset($try['action_score']) && $try['action_score'] !== '' ? (int) $try['action_score'] : null,
            'outside_session'       => (int) $context['outside_session'],
        ] + $context['flags'];
    }

    public static function blank(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
