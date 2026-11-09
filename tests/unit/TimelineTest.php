<?php

use App\Libraries\Derived\Timeline;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Part 9 (35 §6.7; 11 → Analitik 5): active time from windows, hidden
 * stretches, input pauses, and narration.
 *
 * @internal
 */
final class TimelineTest extends CIUnitTestCase
{
    private const MIN = 60_000;

    public function testThe11ExampleAFiveMinutePauseWithAThreeMinuteThresholdGivesThreeActiveMinutes(): void
    {
        $t = Timeline::active([[0, 5 * self::MIN]], [], [[0, 5 * self::MIN]], [], 3 * self::MIN);

        $this->assertSame(['total_ms' => 300000, 'hidden_ms' => 0, 'idle_excess_ms' => 120000, 'active_ms' => 180000], $t);
    }

    public function testOnlyThePartBeyondTheThresholdIsCut(): void
    {
        $t = Timeline::active([[0, 10 * self::MIN]], [], [[0, 2 * self::MIN], [3 * self::MIN, 7 * self::MIN]], [], 3 * self::MIN);

        $this->assertSame(60000, $t['idle_excess_ms'], 'a 2-minute pause costs nothing, a 4-minute one costs 1');
        $this->assertSame(540000, $t['active_ms']);
    }

    public function testSegmentsAddAndTheGapBetweenThemNeverCounts(): void
    {
        $t = Timeline::active([[0, self::MIN], [10 * self::MIN, 12 * self::MIN]], [], [], [], 3 * self::MIN);

        $this->assertSame(180000, $t['total_ms']);
        $this->assertSame(180000, $t['active_ms']);
    }

    public function testHiddenIsCutWholeAndNotCountedAgainAsAPause(): void
    {
        // Pause of 5 minutes, 3 of them hidden: the visible rest is 2 minutes, under T_idle
        $t = Timeline::active([[0, 10 * self::MIN]], [[2 * self::MIN, 5 * self::MIN]], [[0, 5 * self::MIN]], [], 3 * self::MIN);

        $this->assertSame(180000, $t['hidden_ms']);
        $this->assertSame(0, $t['idle_excess_ms']);
        $this->assertSame(420000, $t['active_ms']);
    }

    public function testNarrationPlayingIsNeverAPause(): void
    {
        $t = Timeline::active([[0, 10 * self::MIN]], [], [[0, 5 * self::MIN]], [[0, 2 * self::MIN]], 3 * self::MIN);

        $this->assertSame(0, $t['idle_excess_ms'], 'the pause less the narration is 3 minutes, not over T_idle');
    }

    public function testHiddenStretchesFromEvents(): void
    {
        $events = [['type' => 'page_hidden', 'at' => 100], ['type' => 'page_visible', 'at' => 400], ['type' => 'page_hidden', 'at' => 900]];

        $this->assertSame([[100, 400], [900, 1000]], Timeline::hiddenStretches($events, 1000));
    }

    public function testIntervalArithmetic(): void
    {
        $this->assertSame([[0, 5], [6, 9]], Timeline::merge([[6, 9], [0, 3], [2, 5]]));
        $this->assertSame([[0, 2], [4, 10]], Timeline::subtract([[0, 10]], [[2, 4]]));
        $this->assertSame([[3, 5]], Timeline::intersect([[0, 5]], [[3, 8]]));
        $this->assertSame(8, Timeline::length([[0, 5], [3, 8]]));
    }
}
