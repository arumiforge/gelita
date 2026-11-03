<?php

namespace App\Libraries;

/**
 * Booklet table of 06 → Desain buklet (31 §11.1): which variant of the scored
 * challenges a student plays on the first attempt, per region. The booklet
 * itself is computed and stored by part 2 (`participant_seats.booklet`).
 *
 * Pure (no framework).
 */
final class BookletRules
{
    public const BOOKLETS = ['B1', 'B2', 'B3', 'B4'];

    /** Booklet → variant per region sequence (1 Temanggung, 2 Magelang, 3 Wonosobo). */
    public const TABLE = [
        'B1' => [1 => 'a', 2 => 'a', 3 => 'a'],
        'B2' => [1 => 'b', 2 => 'b', 3 => 'b'],
        'B3' => [1 => 'a', 2 => 'b', 3 => 'a'],
        'B4' => [1 => 'b', 2 => 'a', 3 => 'b'],
    ];

    public static function isBooklet(?string $booklet): bool
    {
        return isset(self::TABLE[strtoupper(trim((string) $booklet))]);
    }

    /**
     * Variant of one region for one booklet.
     *
     * @throws \InvalidArgumentException unknown booklet or region sequence
     */
    public static function variantFor(string $booklet, int $levelSequence): string
    {
        $booklet = strtoupper(trim($booklet));

        if (! isset(self::TABLE[$booklet][$levelSequence])) {
            throw new \InvalidArgumentException("Booklet '{$booklet}' has no variant for region {$levelSequence}.");
        }

        return self::TABLE[$booklet][$levelSequence];
    }
}
