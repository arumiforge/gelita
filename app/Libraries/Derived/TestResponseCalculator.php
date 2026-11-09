<?php

namespace App\Libraries\Derived;

/**
 * `derived_test_responses`, one row per answer of a pre/post test (35 §6.8,
 * §19 no. 9). The decision time is `last_view_ms`; it is empty for an empty
 * answer or an item not reached. The rapid-guess threshold of the tests is
 * the study's `test_rapid_guess_ms` (owner, 4 November 2026); the first item
 * after a passage gets an empty `rapid_guess` (reading time falls on it, 13).
 * Preview sittings and the examples `tes-ct-*` are left out by the loader. Pure.
 */
final class TestResponseCalculator
{
    public const EMPTY = '-';

    /** @return array{instrument_response_id: int, decision_ms: ?int, rapid_guess: ?int} */
    public static function row(array $response, int $threshold): array
    {
        $answer   = trim((string) ($response['response'] ?? ''));
        $decision = $answer === '' || $answer === self::EMPTY || ! empty($response['not_reached']) || ($response['last_view_ms'] ?? null) === null
            ? null
            : max(0, (int) $response['last_view_ms']);

        return [
            'instrument_response_id' => (int) $response['id'],
            'decision_ms'            => $decision,
            'rapid_guess'            => ! empty($response['after_stimulus']) ? null : TryCalculator::rapid($decision, $threshold),
        ];
    }
}
