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

    public function testScenarioInstructionBandHoldsTwoLinesAboveTheSteps(): void
    {
        $aside = $this->box('.challenge-skenario .challenge-aside');
        $steps = $this->box('.challenge-skenario .scenario-steps');
        $bar   = $this->box('.challenge-skenario .arena-actions');

        // Two lines at the reading size (22 dp on the 1280 dp stage) × line-height 1.3 = 57.2 dp
        $this->assertGreaterThanOrEqual(58, $aside['h']);
        $this->assertMatchesRegularExpression('~\.challenge-skenario \.instruction\s*\{\s*line-height:\s*1\.3;~', $this->css('game'));
        // The steps panel starts below the band and ends above the action bar
        $this->assertGreaterThanOrEqual($aside['y'] + $aside['h'], $steps['y']);
        $this->assertLessThanOrEqual($bar['y'], $steps['y'] + $steps['h']);
    }

    public function testInstructionPlayButtonSitsBesideTheTextOnBandAsides(): void
    {
        $css = $this->css('game');

        $this->assertMatchesRegularExpression(
            '~:is\(\.challenge-rumpang, \.challenge-boleh, \.challenge-skenario\) \.challenge-aside:has\(> \.instruction-audio\)\s*\{[^}]*grid-template-columns:\s*minmax\(0, 1fr\) auto;~',
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
