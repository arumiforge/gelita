<?php

use App\Libraries\Derived\TestResponseCalculator;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Part 9 (35 §6.8, §19 no. 9): rapid guesses of test answers.
 *
 * @internal
 */
final class TestResponseCalculatorTest extends CIUnitTestCase
{
    public function testRapidGuessAtTheThresholdFromLastViewMs(): void
    {
        $this->assertSame(['instrument_response_id' => 1, 'decision_ms' => 3000, 'rapid_guess' => 0], TestResponseCalculator::row($this->r(3000), 3000));
        $this->assertSame(1, TestResponseCalculator::row($this->r(2999), 3000)['rapid_guess']);
    }

    public function testEmptyForNotReachedEmptyAnswersAndTheFirstItemAfterAPassage(): void
    {
        $this->assertSame([null, null], array_values(array_slice(TestResponseCalculator::row($this->r(500, ['not_reached' => 1]), 3000), 1)));
        $this->assertSame([null, null], array_values(array_slice(TestResponseCalculator::row($this->r(500, ['response' => '-']), 3000), 1)));

        $first = TestResponseCalculator::row($this->r(500, ['after_stimulus' => true]), 3000);
        $this->assertSame(500, $first['decision_ms']);
        $this->assertNull($first['rapid_guess'], 'reading time falls on the first item after a passage (owner, 4 Nov 2026)');
    }

    private function r(int $ms, array $extra = []): array
    {
        return $extra + ['id' => 1, 'response' => 'B', 'not_reached' => 0, 'last_view_ms' => $ms, 'after_stimulus' => false];
    }
}
