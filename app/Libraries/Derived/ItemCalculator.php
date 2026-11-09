<?php

namespace App\Libraries\Derived;

use App\Libraries\HuntGeometry;

/**
 * `derived_items`, one row per student × scored item of the student's
 * variant, also without tries (35 §6.2; 11 → Analitik 2, 3, 4, 6, 9, 10,
 * Data hilang). Only the scored tries of the first attempt (`scored_try_no`).
 * Pure.
 */
final class ItemCalculator
{
    /** Reason codes per Skenario Digital pattern at action score 2 (11 → Analitik 10). */
    public const SAFE_PATTERNS = [
        'R-RISK' => 'safe_expected', 'R-ETIK' => 'safe_expected',
        'R-ATUR' => 'safe_rule',
        'R-SOS'  => 'safe_wrong_reason', 'R-ACAK' => 'safe_wrong_reason',
    ];

    /**
     * @param array<string, mixed>       $item    RunInput item
     * @param list<array<string, mixed>> $tries   scored tries in order: RunInput try + `row` (TryCalculator::row())
     * @param string|null                $missing locked, not_started, no_try (used without tries)
     * @param list<array<string, mixed>>|null $taps empty_tap payloads of this clue in the scored attempts (Cari Objek), else null
     * @param array{0: ?int, 1: ?int}    $timing  item_total_ms, item_active_ms
     *
     * @return array<string, mixed>
     */
    public static function row(array $item, array $tries, ?string $missing, ?array $taps, array $timing): array
    {
        $d2  = $item['interaction_type'] === 'scenario_choice';
        $max = $d2 ? 2 : 1;
        $row = array_fill_keys([
            'first_try_score', 'first_try_correct', 'missing_reason', 'final_correct', 'final_score', 'tries_used', 'solved_on_try',
            'censored', 'censor_reason', 'first_error_code', 'first_error_code_analysis', 'change_after_feedback', 'change_after_feedback_2',
            'answer_unchanged', 'score_change', 'near_miss_count', 'empty_tap_count', 'item_total_ms', 'item_active_ms',
            'first_input_ms', 'last_input_ms', 'first_action_key', 'first_action_score', 'first_action_code', 'first_reason_code',
            'action_reason_pattern', 'reason_level', 'shared_device', 'helped_content', 'rapid_guess',
        ], null);
        $row['challenge_item_id'] = (int) $item['id'];

        if ($tries === []) {
            $row['missing_reason'] = $missing ?? 'no_try';

            return $row;
        }

        $first  = $tries[0];
        $score  = static fn (array $t): int => $d2 ? (int) ($t['action_score'] ?? 0) : (int) $t['is_correct'];
        $solved = null;

        foreach ($tries as $i => $try) {
            if ((int) $try['is_correct'] === 1) {
                $solved = $i + 1;
                break;
            }
        }

        $used = count($tries);
        $last = $tries[$used - 1];

        $row['first_try_score']   = $score($first);
        $row['first_try_correct'] = $score($first) === $max ? 1 : 0;
        $row['final_correct']     = $solved !== null ? 1 : 0;
        $row['final_score']       = $d2 ? $score($last) : $row['final_correct'];
        $row['tries_used']        = $used;
        $row['solved_on_try']     = $solved;
        $row['censored']          = $solved === null ? 1 : 0;
        $row['censor_reason']     = $solved === null ? ($used >= TryCalculator::MAX_TRIES ? 'exhausted' : 'left') : null;

        if ((int) $first['is_correct'] !== 1) {
            $row['first_error_code']          = $first['row']['error_code'];
            $row['first_error_code_analysis'] = $first['row']['error_code_analysis'];
            $row['change_after_feedback']     = self::change($first, $tries[1] ?? null, $d2);
        }

        if (isset($tries[1]) && (int) $tries[1]['is_correct'] !== 1) {
            $row['change_after_feedback_2'] = self::change($tries[1], $tries[2] ?? null, $d2);
        }

        if (isset($tries[1])) {
            $row['answer_unchanged'] = ($first['answer_hash'] ?? null) !== null && $first['answer_hash'] === ($tries[1]['answer_hash'] ?? null) ? 1 : 0;
            $row['score_change']     = $d2 ? $score($tries[1]) - $score($first) : null;
        }

        if ($taps !== null && ($first['ready_ms'] ?? null) !== null) {
            [$near, $far] = self::taps($taps, $item['key_box'] ?? null);
            $row['near_miss_count'] = $near;
            $row['empty_tap_count'] = $far;
        }

        [$row['item_total_ms'], $row['item_active_ms']] = $timing;
        $row['first_input_ms'] = isset($first['first_input_ms']) ? (int) $first['first_input_ms'] : null;
        $row['last_input_ms']  = isset($first['last_input_ms']) ? (int) $first['last_input_ms'] : null;

        if ($d2) {
            $row['first_action_key']      = TryCalculator::blank($first['action_key'] ?? null);
            $row['first_action_score']    = $score($first);
            $row['first_action_code']     = $first['row']['error_code'];
            $row['first_reason_code']     = TryCalculator::blank($first['reason_code'] ?? null);
            $row['action_reason_pattern'] = match ($score($first)) {
                2       => self::SAFE_PATTERNS[(string) $row['first_reason_code']] ?? null,
                1       => 'partial',
                default => 'risky',
            };
        }

        $row['shared_device']  = $first['row']['shared_device'];
        $row['helped_content'] = $first['row']['helped_content'];
        $row['rapid_guess']    = $first['row']['rapid_guess'];

        return $row;
    }

    /**
     * Change after feedback from one wrong try to the next (11 → Analitik 9):
     * D2 by the action key, the others by the raw error code.
     */
    public static function change(array $wrong, ?array $next, bool $d2): string
    {
        if ($next === null) {
            return 'no_second_try';
        }

        if ((int) $next['is_correct'] === 1) {
            return 'fixed';
        }

        $same = $d2
            ? (string) ($wrong['action_key'] ?? '') === (string) ($next['action_key'] ?? '')
            : (string) ($wrong['row']['error_code_at_try'] ?? '') === (string) ($next['row']['error_code_at_try'] ?? '');

        return $same ? 'same_error' : 'other_error';
    }

    /**
     * Near misses and other empty taps (K11): distance from the tap to the
     * key part in dp on the 864 dp scene; `overflow` adds to the far count.
     *
     * @param list<array<string, mixed>> $taps
     * @param array{0: float, 1: float, 2: float, 3: float}|null $box percent
     *
     * @return array{0: int, 1: int}
     */
    public static function taps(array $taps, ?array $box): array
    {
        $near = 0;
        $far  = 0;

        foreach ($taps as $tap) {
            if (isset($tap['overflow'])) {
                $far += max(0, (int) $tap['overflow']);

                continue;
            }

            if (! isset($tap['x_pct'], $tap['y_pct']) || $box === null) {
                $far++;

                continue;
            }

            $distance = self::distanceDp((float) $tap['x_pct'], (float) $tap['y_pct'], $box);
            $distance <= Thresholds::NEAR_MISS_DP + 1e-9 ? $near++ : $far++;
        }

        return [$near, $far];
    }

    /** Distance in dp from a point (percent of the scene) to a box (percent). */
    public static function distanceDp(float $x, float $y, array $box): float
    {
        [$bx, $by, $bw, $bh] = HuntGeometry::toDp(array_map('floatval', $box));
        [$px, $py]           = HuntGeometry::toDp([$x, $y, 0.0, 0.0]);
        $dx                  = max($bx - $px, 0.0, $px - ($bx + $bw));
        $dy                  = max($by - $py, 0.0, $py - ($by + $bh));

        return sqrt($dx * $dx + $dy * $dy);
    }
}
