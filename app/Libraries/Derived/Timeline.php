<?php

namespace App\Libraries\Derived;

/**
 * Active-time arithmetic (35 §6.7, 11 → Analitik 5), one function for
 * attempts, items, and sessions. Intervals are [start_ms, end_ms] pairs.
 *
 * 1. `total_ms` = the length of the windows (segments, an item's stretch, a
 *    game session); the gaps between windows never count.
 * 2. `hidden_ms` = the part of the windows covered by `page_hidden` →
 *    `page_visible` stretches, cut whole.
 * 3. Every logged input pause (`input_idle`, ≥ 20 s) inside a window, less
 *    the narration playing and what is already hidden: the part longer than
 *    T_idle is `idle_excess_ms`.
 * 4. `active_ms = total_ms − hidden_ms − idle_excess_ms`.
 *
 * Pure: no database, no clock, no configuration.
 */
final class Timeline
{
    /**
     * @param list<array{0: int, 1: int}> $windows
     * @param list<array{0: int, 1: int}> $hidden
     * @param list<array{0: int, 1: int}> $pauses    input pauses
     * @param list<array{0: int, 1: int}> $narration narration playing
     *
     * @return array{total_ms: int, hidden_ms: int, idle_excess_ms: int, active_ms: int}
     */
    public static function active(array $windows, array $hidden, array $pauses, array $narration, int $idleMs): array
    {
        $windows = self::merge($windows);
        $hidden  = self::merge($hidden);
        $sounds  = self::merge($narration);
        $total   = self::length($windows);
        $hid     = self::length(self::intersect($hidden, $windows));
        $excess  = 0;

        foreach ($pauses as $pause) {
            foreach ($windows as $window) {
                $part = self::intersect([$pause], [$window]);

                if ($part === []) {
                    continue;
                }

                $rest    = self::subtract(self::subtract($part, $hidden), $sounds);
                $excess += max(0, self::length($rest) - $idleMs);
            }
        }

        return ['total_ms' => $total, 'hidden_ms' => $hid, 'idle_excess_ms' => $excess, 'active_ms' => $total - $hid - $excess];
    }

    /**
     * Hidden stretches of one game session from its events (sorted or not):
     * each `page_hidden` until the next `page_visible`, or until $end.
     *
     * @param list<array{type: string, at: int}> $events
     *
     * @return list<array{0: int, 1: int}>
     */
    public static function hiddenStretches(array $events, int $end): array
    {
        usort($events, static fn (array $a, array $b): int => $a['at'] <=> $b['at']);
        $out   = [];
        $since = null;

        foreach ($events as $event) {
            if ($event['type'] === 'page_hidden' && $since === null) {
                $since = $event['at'];
            } elseif ($event['type'] === 'page_visible' && $since !== null) {
                $out[] = [$since, max($since, $event['at'])];
                $since = null;
            }
        }

        if ($since !== null) {
            $out[] = [$since, max($since, $end)];
        }

        return $out;
    }

    /**
     * @param list<array{0: int, 1: int}> $intervals
     *
     * @return list<array{0: int, 1: int}> sorted, without overlaps or empty ones
     */
    public static function merge(array $intervals): array
    {
        $intervals = array_values(array_filter($intervals, static fn (array $i): bool => $i[1] > $i[0]));
        usort($intervals, static fn (array $a, array $b): int => $a[0] <=> $b[0]);
        $out = [];

        foreach ($intervals as [$a, $b]) {
            $last = count($out) - 1;

            if ($last >= 0 && $a <= $out[$last][1]) {
                $out[$last][1] = max($out[$last][1], $b);
            } else {
                $out[] = [$a, $b];
            }
        }

        return $out;
    }

    /** @param list<array{0: int, 1: int}> $intervals */
    public static function length(array $intervals): int
    {
        return array_sum(array_map(static fn (array $i): int => max(0, $i[1] - $i[0]), self::merge($intervals)));
    }

    /**
     * @param list<array{0: int, 1: int}> $a
     * @param list<array{0: int, 1: int}> $b
     *
     * @return list<array{0: int, 1: int}>
     */
    public static function intersect(array $a, array $b): array
    {
        $out = [];

        foreach (self::merge($a) as [$a0, $a1]) {
            foreach (self::merge($b) as [$b0, $b1]) {
                $s = max($a0, $b0);
                $e = min($a1, $b1);

                if ($e > $s) {
                    $out[] = [$s, $e];
                }
            }
        }

        return self::merge($out);
    }

    /**
     * @param list<array{0: int, 1: int}> $a
     * @param list<array{0: int, 1: int}> $minus
     *
     * @return list<array{0: int, 1: int}>
     */
    public static function subtract(array $a, array $minus): array
    {
        $rest = self::merge($a);

        foreach (self::merge($minus) as [$m0, $m1]) {
            $next = [];

            foreach ($rest as [$s, $e]) {
                if ($m1 <= $s || $m0 >= $e) {
                    $next[] = [$s, $e];

                    continue;
                }

                if ($m0 > $s) {
                    $next[] = [$s, $m0];
                }

                if ($m1 < $e) {
                    $next[] = [$m1, $e];
                }
            }

            $rest = $next;
        }

        return $rest;
    }
}
