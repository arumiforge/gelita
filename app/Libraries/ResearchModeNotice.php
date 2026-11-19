<?php

namespace App\Libraries;

use App\Services\GameContext;

/**
 * The research-mode notice on every panel page (owner, 19 November 2026):
 * whether the study switch `field_mode` is on for the running study, and
 * what the game records and does differently in each mode, so staff see at
 * once whether students are playing for the study or only trying the game.
 *
 * Facts come from parts 2, 7, 8 and 9 (28, 33, 34, 35): raw data is recorded
 * the same in both modes (33 K2); only entry, the game rules and the study
 * layers follow the switch.
 */
final class ResearchModeNotice
{
    /**
     * @param array<string, mixed>|null $activeStudy the running study (`status` active), or null
     *
     * @return array{on: bool, study: array<string, mixed>|null, rows: list<array{0: string, 1: string, 2: string}>}
     */
    public static function build(?array $activeStudy): array
    {
        $on = $activeStudy !== null && (int) ($activeStudy['field_mode'] ?? 0) === 1;

        return ['on' => $on, 'study' => $activeStudy, 'rows' => self::rows($activeStudy)];
    }

    /**
     * What differs: [aspect, field mode off, field mode on].
     *
     * @param array<string, mixed>|null $study
     *
     * @return list<array{0: string, 1: string, 2: string}>
     */
    public static function rows(?array $study): array
    {
        $minPx = GameContext::studySettings($study)['min_stage_px'];
        $row   = static fn (string $key, array $args = []): array => [
            lang("Admin.researchModeRow{$key}"),
            lang("Admin.researchModeRow{$key}Off", $args),
            lang("Admin.researchModeRow{$key}On", $args),
        ];

        return [
            $row('Entry'),
            $row('Code'),
            $row('Raw'),
            $row('Clock'),
            $row('Screen', [fmt_num($minPx)]),
            $row('Retake'),
            $row('Kenali'),
            $row('Sound'),
            $row('Tests'),
            $row('Derived'),
        ];
    }

    /** The notice for the current request: reads the running study once. */
    public static function current(): array
    {
        return self::build(model(\App\Models\ResearchStudyModel::class)->activeStudy());
    }
}
