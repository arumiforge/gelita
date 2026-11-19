<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Manual check 32 §15.3 no. 4–5 (14 November 2026): on the one-band
 * instruction asides the instruction ▶ (32 §8.2, K10) was a grid row of its
 * own below the band, so `.challenge-aside` (overflow hidden) cut it off and
 * a student could not reach it; on Skenario Digital the 56 dp band also cut
 * the second line of the instruction on every screen.
 *
 * @internal
 */
final class InstructionBandCssTest extends CIUnitTestCase
{
    /** CSS without comments. */
    private function css(string $name): string
    {
        return (string) preg_replace('~/\*.*?\*/~s', '', (string) file_get_contents(FCPATH . 'assets/css/' . $name . '.css'));
    }

    /** @return array{x: int, y: int, w: int, h: int} stage box of one selector in stage.css */
    private function box(string $selector): array
    {
        $pattern = '~' . preg_quote($selector, '~') . '\s*\{\s*--x:\s*(\d+);\s*--y:\s*(\d+);\s*--w:\s*(\d+);\s*--h:\s*(\d+);~';
        $this->assertMatchesRegularExpression($pattern, $this->css('stage'), "{$selector} has no stage box");
        preg_match($pattern, $this->css('stage'), $m);

        return ['x' => (int) $m[1], 'y' => (int) $m[2], 'w' => (int) $m[3], 'h' => (int) $m[4]];
    }

    /**
     * Skenario Digital since 41 step D (19 November 2026): no instruction band; the two sides
     * of the approved mockup, 584 dp high below the header, with the instruction and the
     * buttons inside the right side (41 §3, decisions 5, 33).
     */
    public function testScenarioSidesFillTheArenaBelowTheHeader(): void
    {
        $head  = $this->box('.challenge-head');
        $left  = $this->box('.challenge-skenario .scenario-situation');
        $right = $this->box('.challenge-skenario .scenario-steps');

        $this->assertSame(['x' => 32, 'y' => 104, 'w' => 600, 'h' => 584], $left);
        $this->assertSame(['x' => 664, 'y' => 104, 'w' => 584, 'h' => 584], $right);
        $this->assertGreaterThanOrEqual($head['y'] + $head['h'], $left['y']);
        $this->assertMatchesRegularExpression('~\.challenge-skenario \.challenge-aside\s*\{\s*display:\s*none;~', $this->css('game'));
        $this->assertStringNotContainsString('.challenge-skenario .arena-actions', $this->css('stage'));
    }

    public function testInstructionPlayButtonSitsBesideTheTextOnBandAsides(): void
    {
        $css = $this->css('game');

        $this->assertMatchesRegularExpression(
            '~:is\(\.challenge-rumpang, \.challenge-boleh\) \.challenge-aside:has\(> \.instruction-audio\)\s*\{[^}]*grid-template-columns:\s*minmax\(0, 1fr\) auto;~',
            $css,
        );
        // The button box (48 dp + padding) fits the 60 dp band
        $this->assertMatchesRegularExpression('~\.challenge-aside > \.instruction-audio\s*\{[^}]*padding:\s*\.125rem;~', $css);
    }

    public function testIsiRumpangInstructionBandHoldsTwoLinesAboveTheReading(): void
    {
        $head    = $this->box('.challenge-head');
        $aside   = $this->box('.challenge-rumpang .challenge-aside');
        $bank    = $this->box('.challenge-rumpang .word-bank');
        $pattern = '~\.challenge-rumpang \.sentences\s*\{\s*--x:\s*\d+;\s*--y:\s*(\d+);\s*--w:\s*\d+;\s*--h:\s*(\d+);~';
        $this->assertSame(1, preg_match($pattern, $this->css('stage'), $reading), 'the reading has no stage box');
        $media = $this->box('.challenge-rumpang .rumpang-media');

        // Two lines at the reading size (22 dp) × line-height 1.25 = 55 dp, + .25rem padding twice + border = 65 dp
        $this->assertGreaterThanOrEqual(65, $aside['h']);
        $this->assertMatchesRegularExpression('~\.challenge-rumpang \.instruction\.is-card\s*\{\s*padding-block:\s*\.25rem;\s*line-height:\s*1\.25;~', $this->css('game'));
        // Below the header, above the reading; the reading and its picture share one box height and end above the word bank
        $this->assertGreaterThanOrEqual($head['y'] + $head['h'], $aside['y']);
        $this->assertGreaterThanOrEqual($aside['y'] + $aside['h'], (int) $reading[1]);
        $this->assertLessThanOrEqual($bank['y'], (int) $reading[1] + (int) $reading[2]);
        $this->assertSame([(int) $reading[1], (int) $reading[2]], [$media['y'], $media['h']]);
    }
}
