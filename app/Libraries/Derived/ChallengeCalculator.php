<?php

namespace App\Libraries\Derived;

/**
 * `derived_challenges`, one row per student × challenge (35 §6.4;
 * 11 → Analitik 1). Pure.
 */
final class ChallengeCalculator
{
    public const STATUSES = ['solved', 'exhausted', 'unfinished', 'not_started', 'locked'];

    /**
     * Completion status from the first attempt, judged at run time ("data
     * collection finished" = the run, 35 §19 no. 7).
     *
     * @param list<array<string, mixed>> $scored  the challenge's scored attempts
     * @param list<array<string, mixed>> $items   its derived_items rows
     */
    public static function status(array $scored, array $items, bool $open): string
    {
        if ($scored === []) {
            return $open ? 'not_started' : 'locked';
        }

        $closed = array_filter($scored, static fn (array $a): bool => $a['status'] === 'completed' && $a['completed_at'] !== null);

        if ($closed === []) {
            return 'unfinished';
        }

        foreach ($items as $item) {
            if ((int) ($item['final_correct'] ?? 0) !== 1) {
                return 'exhausted';
            }
        }

        return 'solved';
    }

    /**
     * Skipped (b): `challenge_abandoned` of a scored attempt before its
     * closing, not followed by activity of that attempt in the same field
     * session. Counted even when the challenge is done later (e.g. at P5).
     *
     * @param list<array<string, mixed>>     $abandons   the attempt's challenge_abandoned events: at, field_session_id
     * @param list<array{0: int, 1: ?int}>   $activity   the attempt's activity: [at, field_session_id]
     */
    public static function leftCount(array $abandons, array $activity): int
    {
        $count = 0;

        foreach ($abandons as $abandon) {
            $resumed = false;

            foreach ($activity as [$at, $fieldSession]) {
                if ($at > $abandon['at'] && $fieldSession !== null && $fieldSession === $abandon['field_session_id']) {
                    $resumed = true;
                    break;
                }
            }

            $count += $resumed ? 0 : 1;
        }

        return $count;
    }

    /** @return array<string, mixed> */
    public static function row(int $nodeId, string $status, int $failed, int $left, ?int $closedMeeting): array
    {
        return [
            'challenge_node_id'    => $nodeId,
            'completion_status'    => $status,
            'skipped_failed_count' => $failed,
            'skipped_left_count'   => $left,
            'closed_meeting_no'    => in_array($status, ['solved', 'exhausted'], true) ? $closedMeeting : null,
        ];
    }
}
