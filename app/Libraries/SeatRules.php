<?php

namespace App\Libraries;

/**
 * Booklet and test form from a student's row and seat (research revision 10,
 * part 2: 28 §11, K10; L2 sheet in 02, forms in 13). Rows are counted from the
 * front and seats from the left, seen from the board.
 */
final class SeatRules
{
    public const BOOKLETS = ['B1', 'B2', 'B3', 'B4'];

    /** Highest row and seat number the panel accepts. */
    public const MAX_NO = 30;

    /** odd/odd = B1, odd/even = B2, even/odd = B3, even/even = B4 */
    public static function booklet(int $row, int $seat): string
    {
        $rowEven  = $row % 2 === 0;
        $seatEven = $seat % 2 === 0;

        return match (true) {
            ! $rowEven && ! $seatEven => 'B1',
            ! $rowEven && $seatEven   => 'B2',
            $rowEven && ! $seatEven   => 'B3',
            default                   => 'B4',
        };
    }

    /**
     * Row + seat even: pre-test A, post-test B; odd: pre-test B, post-test A.
     *
     * @return array{pre: string, post: string}
     */
    public static function forms(int $row, int $seat): array
    {
        return ($row + $seat) % 2 === 0 ? ['pre' => 'A', 'post' => 'B'] : ['pre' => 'B', 'post' => 'A'];
    }

    /**
     * Booklet counts of one class group and whether they are balanced (the
     * largest and smallest count differ by at most one).
     *
     * @param list<string> $booklets
     *
     * @return array{counts: array<string, int>, balanced: bool}
     */
    public static function balance(array $booklets): array
    {
        $counts = array_fill_keys(self::BOOKLETS, 0);

        foreach ($booklets as $booklet) {
            if (isset($counts[$booklet])) {
                $counts[$booklet]++;
            }
        }

        $balanced = $booklets === [] || max($counts) - min($counts) <= 1;

        return ['counts' => $counts, 'balanced' => $balanced];
    }

    public static function validNo(mixed $value): bool
    {
        return is_numeric($value) && (int) $value == $value && (int) $value >= 1 && (int) $value <= self::MAX_NO;
    }
}
