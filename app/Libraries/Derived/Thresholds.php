<?php

namespace App\Libraries\Derived;

/**
 * Thresholds of one derived run (35 §5). The idle and rapid-guess thresholds
 * come from the study (part 7's columns, K2); the others are frozen by 11
 * and live here by name so `kamus-1.1` can change them in one place.
 */
final class Thresholds
{
    /** NON-SEN: an empty tap at most this far from the key part is a near miss (11 → Analitik 6). */
    public const NEAR_MISS_DP = 24;

    /** Input pauses the client logs (part 7, `input_idle`), 20 s. */
    public const INPUT_IDLE_LOG_MS = 20000;

    /** `analysable`: first tries on at least 49 of the 61 digital-literacy items (80 %, 11). */
    public const ANALYSABLE_MIN_ITEMS = 49;

    public const LITERACY_ITEMS = 61;

    /** Default study values (part 7, 33 §9), used when a study row has none. */
    public const DEFAULT_IDLE_MS = 180000;

    public const DEFAULT_RAPID_MS = 3000;

    public function __construct(
        public readonly int $idleMs = self::DEFAULT_IDLE_MS,
        public readonly int $rapidMs = self::DEFAULT_RAPID_MS,
        public readonly int $testRapidMs = self::DEFAULT_RAPID_MS,
    ) {
    }

    /** @param array<string, mixed> $study a research_studies row */
    public static function fromStudy(array $study): self
    {
        return new self(
            (int) ($study['idle_threshold_ms'] ?? self::DEFAULT_IDLE_MS),
            (int) ($study['rapid_guess_ms'] ?? self::DEFAULT_RAPID_MS),
            (int) ($study['test_rapid_guess_ms'] ?? $study['rapid_guess_ms'] ?? self::DEFAULT_RAPID_MS),
        );
    }
}
