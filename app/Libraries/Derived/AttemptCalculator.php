<?php

namespace App\Libraries\Derived;

/**
 * `derived_attempts`, one row per attempt (35 §6.3; 11 → Pengerjaan
 * pertama, Analitik 5, 7, 8). Pure.
 */
final class AttemptCalculator
{
    /**
     * Scored attempts (11 → Pengerjaan pertama): per challenge, every attempt
     * that is not a retake and started before the challenge first closed
     * (`completed`). Two of them before the closing (a disturbance) are both
     * scored; the try fallback rule then numbers their tries.
     *
     * @param array<int, array<string, mixed>> $attempts id => RunInput attempt (cut at the withdrawal)
     *
     * @return array<int, array{is_scored_attempt: int, closed_at: ?int}> attempt id => …; closed_at = first closing of its challenge
     */
    public static function classify(array $attempts): array
    {
        $byNode = [];

        foreach ($attempts as $attempt) {
            $byNode[(int) $attempt['node_id']][] = $attempt;
        }

        $out = [];

        foreach ($byNode as $list) {
            $close = null;

            foreach ($list as $attempt) {
                if (empty($attempt['is_retake']) && $attempt['status'] === 'completed' && $attempt['completed_at'] !== null) {
                    $close = $close === null ? $attempt['completed_at'] : min($close, $attempt['completed_at']);
                }
            }

            foreach ($list as $attempt) {
                $scored = empty($attempt['is_retake']) && ($close === null || $attempt['started_at'] <= $close);
                $out[(int) $attempt['id']] = ['is_scored_attempt' => $scored ? 1 : 0, 'closed_at' => $close];
            }
        }

        return $out;
    }

    /**
     * Windows of one attempt: one per segment (33 §5.4), from `opened_at` to
     * `left_at`; a segment still open is closed at the attempt's last
     * activity in that game session (11), or kept empty without any.
     *
     * @param list<array<string, mixed>> $segments the attempt's segments
     * @param array<int, int>            $lastAt   game session id => the attempt's last activity there (ms)
     *
     * @return list<array{0: int, 1: int, session_id: int, field_session_id: ?int}>
     */
    public static function windows(array $segments, array $lastAt, ?int $cutoff = null): array
    {
        usort($segments, static fn (array $a, array $b): int => [$a['opened_at'], $a['segment_no'] ?? 0] <=> [$b['opened_at'], $b['segment_no'] ?? 0]);
        $out = [];

        foreach ($segments as $segment) {
            $start = (int) $segment['opened_at'];
            $end   = $segment['left_at'] ?? max($start, $lastAt[(int) $segment['session_id']] ?? $start);

            if ($cutoff !== null) {
                $end = min($end, $cutoff);
            }

            $out[] = [$start, max($start, (int) $end), 'session_id' => (int) $segment['session_id'], 'field_session_id' => isset($segment['field_session_id']) ? (int) $segment['field_session_id'] : null];
        }

        return $out;
    }

    /**
     * @param array<string, mixed> $attempt RunInput attempt
     * @param array<string, mixed> $context is_scored_attempt, windows, timeline (Timeline::active()), cumulative_active_ms,
     *                                      events (the attempt's events), audio (the attempt's playbacks), meeting_no, field_session_id
     *
     * @return array<string, mixed>
     */
    public static function row(array $attempt, array $context): array
    {
        $close   = $attempt['status'] === 'completed' ? $attempt['completed_at'] : null;
        $before  = static fn (array $e): bool => $close === null || $e['at'] <= $close;
        $count   = static fn (string $type): int => count(array_filter($context['events'], static fn (array $e): bool => $e['type'] === $type && $before($e)));
        $reading = null;

        foreach ($context['events'] as $event) {
            if ($event['type'] === 'reading_progress' && isset($event['payload']['percent'])) {
                $reading = max((int) $reading, (int) $event['payload']['percent']);
            }
        }

        $replays = count(array_filter($context['audio'], static fn (array $a): bool => ($a['context'] ?? '') === 'challenge_instruction' && in_array($a['action'], ['play', 'replay'], true)));

        return [
            'attempt_id'           => (int) $attempt['id'],
            'is_scored_attempt'    => (int) $context['is_scored_attempt'],
            'is_retake'            => empty($attempt['is_retake']) ? 0 : 1,
            'segments'             => count($context['windows']),
            'total_ms'             => $context['timeline']['total_ms'],
            'hidden_ms'            => $context['timeline']['hidden_ms'],
            'idle_excess_ms'       => $context['timeline']['idle_excess_ms'],
            'active_ms'            => $context['timeline']['active_ms'],
            'cumulative_active_ms' => $context['cumulative_active_ms'],
            'hint_count'           => (int) ($attempt['hint_count'] ?? 0),
            'instruction_replays'  => $replays,
            'material_returns'     => $count('library_opened') + $count('passage_reopened'),
            'exit_count'           => $count('challenge_abandoned'),
            'reading_max_pct'      => $reading,
            'meeting_no'           => $context['meeting_no'],
            'field_session_id'     => $context['field_session_id'],
        ];
    }
}
