<?php

use App\Libraries\HuntGeometry;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * 24 dp between tap areas (31 §9): conversion with HUNT_SCENE_DP, nearest
 * edge distance, and the 23 dp / 24 dp boundary.
 *
 * @internal
 */
final class HuntGeometryTest extends CIUnitTestCase
{
    public function testTwentyFourDpInPercentOfTheScene(): void
    {
        $this->assertSame(864, HuntGeometry::HUNT_SCENE_DP);
        $this->assertEqualsWithDelta(486.0, HuntGeometry::sceneHeightDp(), 1e-9);
        $this->assertEqualsWithDelta([86.4, 48.6, 86.4, 48.6], HuntGeometry::toDp([10, 10, 10, 10]), 1e-9);
        $this->assertEqualsWithDelta([10.0, 10.0, 9.0, 16.0], HuntGeometry::objectBox(10, 10, 9), 1e-9, 'objects are square on screen');
    }

    public function testNearestEdgeDistance(): void
    {
        $a = [0.0, 0.0, 10.0, 10.0];

        $this->assertSame(0.0, HuntGeometry::gapDp($a, [5.0, 5.0, 10.0, 10.0]), 'overlap');
        $this->assertSame(0.0, HuntGeometry::gapDp($a, [10.0, 0.0, 10.0, 10.0]), 'edges touch');
        $this->assertEqualsWithDelta(86.4, HuntGeometry::gapDp($a, [20.0, 0.0, 5.0, 5.0]), 1e-9, 'side by side: horizontal gap');
        $this->assertEqualsWithDelta(48.6, HuntGeometry::gapDp($a, [0.0, 20.0, 5.0, 5.0]), 1e-9, 'stacked: vertical gap');
        $this->assertEqualsWithDelta(sqrt(86.4 ** 2 + 48.6 ** 2), HuntGeometry::gapDp($a, [20.0, 20.0, 5.0, 5.0]), 1e-9, 'diagonal: corner to corner');
    }

    public function testTouchingBelowTwentyFourDp(): void
    {
        $a  = [0.0, 0.0, 10.0, 10.0];
        $at = static fn (float $dp): array => [10 + $dp / 8.64, 0.0, 10.0, 10.0];

        $this->assertTrue(HuntGeometry::touching($a, $at(23)), '23 dp');
        $this->assertFalse(HuntGeometry::touching($a, $at(24)), '24 dp');
        $this->assertSame('23', HuntGeometry::fmtDp(23.99), 'never rounded up to the limit');
        $this->assertTrue(HuntGeometry::inside([0, 0, 100, 100]));
        $this->assertFalse(HuntGeometry::inside([95, 0, 10, 10]));
        $this->assertSame(11.0, round(HuntGeometry::nearestGapDp($a, [$at(11), [50.0, 50.0, 1.0, 1.0]]), 6));
    }
}
