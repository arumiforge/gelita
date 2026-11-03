<?php

namespace App\Libraries;

/**
 * Tap-area geometry of Cari Objek (31 §9). Every box is in percent of the
 * 16:9 scene: x, y, w, h. Round-1 objects are square on screen, so their
 * height is w × 16/9; round-2 post parts carry their own h.
 *
 * HUNT_SCENE_DP is the width of `.hunt-scene` in the challenge layout on the
 * 1280 dp stage without zoom (`public/assets/css/stage.css`,
 * `.challenge-cari .hunt-scene { --w: 864 }`; HuntSceneWidthTest keeps them
 * equal). Zooming only makes gaps bigger, so the rule is checked at this size.
 *
 * Pure (no framework): used by BankRules (builder, importer, verifier),
 * SurfaceLoad (`tap_min_dp`), and later by the derived layer of part 9
 * (`near_miss_count`).
 */
final class HuntGeometry
{
    /** Width of `.hunt-scene` in dp on the 1280 dp stage. */
    public const HUNT_SCENE_DP = 864;

    /** Scene aspect: width / height. */
    public const SCENE_ASPECT = 16 / 9;

    /** Smallest allowed gap between two tap areas (K9). */
    public const MIN_GAP_DP = 24;

    /** Smallest tap target the game already uses (`--tap`, 48 dp). */
    public const MIN_TAP_DP = 48;

    public static function sceneHeightDp(): float
    {
        return self::HUNT_SCENE_DP / self::SCENE_ASPECT;
    }

    /**
     * Box of a round-1 object: [x, y, w, w × 16/9].
     *
     * @return array{0: float, 1: float, 2: float, 3: float}
     */
    public static function objectBox(float $x, float $y, float $w): array
    {
        return [$x, $y, $w, $w * self::SCENE_ASPECT];
    }

    /**
     * Box in dp: [left, top, width, height].
     *
     * @param array{0: float, 1: float, 2: float, 3: float} $box percent
     *
     * @return array{0: float, 1: float, 2: float, 3: float}
     */
    public static function toDp(array $box): array
    {
        $w = self::HUNT_SCENE_DP / 100;
        $h = self::sceneHeightDp() / 100;

        return [$box[0] * $w, $box[1] * $h, $box[2] * $w, $box[3] * $h];
    }

    /**
     * Nearest distance between the edges of two boxes in dp; 0 when they
     * touch or overlap.
     *
     * @param array{0: float, 1: float, 2: float, 3: float} $a percent
     * @param array{0: float, 1: float, 2: float, 3: float} $b percent
     */
    public static function gapDp(array $a, array $b): float
    {
        [$ax, $ay, $aw, $ah] = self::toDp($a);
        [$bx, $by, $bw, $bh] = self::toDp($b);

        $dx = max(0.0, max($ax, $bx) - min($ax + $aw, $bx + $bw));
        $dy = max(0.0, max($ay, $by) - min($ay + $ah, $by + $bh));

        return sqrt($dx * $dx + $dy * $dy);
    }

    /** Two boxes touch when they overlap or their edges are less than 24 dp apart (K9). */
    public static function touching(array $a, array $b): bool
    {
        return self::gapDp($a, $b) < self::MIN_GAP_DP - 1e-6;
    }

    /** The box lies inside the scene (0–100 % on both axes) and has a size. */
    public static function inside(array $box): bool
    {
        [$x, $y, $w, $h] = $box;

        return $w > 0 && $h > 0 && $x >= 0 && $y >= 0 && $x + $w <= 100 + 1e-6 && $y + $h <= 100 + 1e-6;
    }

    /** Smaller side of the box in dp. */
    public static function minSideDp(array $box): float
    {
        [, , $w, $h] = self::toDp($box);

        return min($w, $h);
    }

    /**
     * Smallest gap in dp between $box and the other boxes; null without others.
     *
     * @param list<array{0: float, 1: float, 2: float, 3: float}> $others
     */
    public static function nearestGapDp(array $box, array $others): ?float
    {
        $min = null;

        foreach ($others as $other) {
            $gap = self::gapDp($box, $other);
            $min = $min === null ? $gap : min($min, $gap);
        }

        return $min;
    }

    /** "11" for messages: whole dp, rounded down so 23.9 never reads as 24. */
    public static function fmtDp(float $dp): string
    {
        return (string) (int) floor($dp + 1e-6);
    }
}
