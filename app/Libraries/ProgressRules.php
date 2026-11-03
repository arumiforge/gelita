<?php

namespace App\Libraries;

/**
 * The one rule that turns completed challenges into a student's progress row
 * (research revision 10, part 2: docs/revisi-10-analitik/28_IMPLEMENTASI_SESI_LAPANGAN.md §8).
 *
 * Used by ChallengeService::refreshProgress() and by migration 003500, which
 * fills `participant_progress` from existing completed attempts, so both give
 * the same numbers.
 */
final class ProgressRules
{
    /**
     * @param array<int, int>                             $bestStars node id => best stars of a completed attempt
     * @param list<array{sequence: int, node_ids: list<int>}> $levels    active levels in order, with their active nodes
     * @param bool                                        $free      study `unlock_mode = free`
     *
     * @return array{completed_nodes: int, completed_levels: int, unlocked_level_sequence: int, total_stars: int}
     */
    public static function summarize(array $bestStars, array $levels, bool $free): array
    {
        $completedLevels  = 0;
        $unlockedSequence = 1;

        foreach ($levels as $level) {
            $ids = $level['node_ids'];

            if ($ids !== [] && array_diff($ids, array_keys($bestStars)) === []) {
                $completedLevels++;
                $unlockedSequence = max($unlockedSequence, $level['sequence'] + 1);
            }
        }

        if ($free) {
            $unlockedSequence = max($unlockedSequence, count($levels));
        }

        return [
            'completed_nodes'         => count($bestStars),
            'completed_levels'        => $completedLevels,
            'unlocked_level_sequence' => min(255, $unlockedSequence),
            'total_stars'             => array_sum($bestStars),
        ];
    }
}
