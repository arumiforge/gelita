<?php

use App\Libraries\Derived\TryCalculator;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Part 9 (35 §6.1; 11 → Analitik 6, Pengerjaan pertama).
 *
 * @internal
 */
final class TryCalculatorTest extends CIUnitTestCase
{
    public function testRapidGuessExactlyAtTheThreshold(): void
    {
        $this->assertSame(0, TryCalculator::rapid(3000, 3000), '3000 is not a rapid guess');
        $this->assertSame(1, TryCalculator::rapid(2999, 3000));
        $this->assertNull(TryCalculator::rapid(null, 3000), 'empty, never 0');
    }

    public function testDecisionTimeIsEmptyWhenAPartIsMissingOrNegative(): void
    {
        $this->assertSame(2500, TryCalculator::decision(1000, 3500));
        $this->assertNull(TryCalculator::decision(null, 3500), 'old data without ready_ms (35 §18 no. 5)');
        $this->assertNull(TryCalculator::decision(4000, 3500));
    }

    public function testClozeHasNoDecisionTimeAndNoRapidGuess(): void
    {
        $row = TryCalculator::row($this->try(['is_correct' => 0, 'ready_ms' => 0, 'last_input_ms' => 500, 'error_code_latest' => 'INF-LIT']), 'rumpang', ['scored_try_no' => 1, 'anomaly' => null], $this->context());

        $this->assertNull($row['decision_ms']);
        $this->assertNull($row['rapid_guess']);
        $this->assertSame('INF-LIT', $row['error_code_analysis'], 'no NON-TEB without a rapid guess');
    }

    public function testRapidWrongTryBecomesNonTebAndTheBankCodeIsTheLatest(): void
    {
        $row = TryCalculator::row($this->try(['is_correct' => 0, 'ready_ms' => 1000, 'last_input_ms' => 2000, 'error_code' => 'INF-KAT', 'error_code_latest' => 'INF-REL']), 'boleh', ['scored_try_no' => 1, 'anomaly' => null], $this->context());

        $this->assertSame(1000, $row['decision_ms']);
        $this->assertSame(1, $row['rapid_guess']);
        $this->assertSame('INF-REL', $row['error_code'], 'latest bank code (35 §19 no. 5)');
        $this->assertSame('INF-KAT', $row['error_code_at_try']);
        $this->assertSame('NON-TEB', $row['error_code_analysis']);
    }

    public function testScenarioUsesTheActionAndReasonTimes(): void
    {
        $row = TryCalculator::row($this->try([
            'is_correct' => 0, 'ready_ms' => 1000, 'last_input_ms' => 99999, 'action_last_input_ms' => 6000, 'reason_ready_ms' => 7000,
            'reason_last_input_ms' => 8500, 'action_score' => 1, 'reason_code' => 'R-SOS', 'error_code_latest' => 'DIG-PRI',
        ]), 'skenario', ['scored_try_no' => 1, 'anomaly' => null], $this->context());

        $this->assertSame([5000, 0, 1500, 1], [$row['decision_ms'], $row['rapid_guess'], $row['reason_decision_ms'], $row['reason_rapid']]);
        $this->assertSame([1, 'R-SOS', 'DIG-PRI'], [$row['action_score'], $row['reason_code'], $row['error_code']]);
    }

    public function testNonHabisOnTheThirdWrongTry(): void
    {
        $third = TryCalculator::row($this->try(['is_correct' => 0, 'try_no' => 3, 'error_code_latest' => 'BUD-ATR']), 'cari', ['scored_try_no' => 3, 'anomaly' => null], $this->context());
        $right = TryCalculator::row($this->try(['is_correct' => 1, 'try_no' => 3]), 'cari', ['scored_try_no' => 3, 'anomaly' => null], $this->context());

        $this->assertSame(1, $third['last_try_exhausted']);
        $this->assertSame(0, $right['last_try_exhausted']);
        $this->assertNull($right['error_code']);
    }

    public function testFallbackRuleNumbersTriesAcrossAttemptsAndFlagsExtraTries(): void
    {
        $tries = [];

        foreach ([[1, 10, '2026-11-10 08:00:01'], [2, 10, '2026-11-10 08:00:02'], [3, 11, '2026-11-10 08:30:00'], [4, 11, '2026-11-10 08:31:00'], [5, 12, '2026-11-10 09:00:00']] as [$id, $attempt, $at]) {
            $tries[$id] = ['id' => $id, 'attempt_id' => $attempt, 'item_id' => 7, 'occurred_at' => strtotime($at) * 1000];
        }

        $n = TryCalculator::number($tries, [10 => true, 11 => true, 12 => false]);

        $this->assertSame([1, 2, 3, null], [$n[1]['scored_try_no'], $n[2]['scored_try_no'], $n[3]['scored_try_no'], $n[4]['scored_try_no']]);
        $this->assertSame('extra_try', $n[4]['anomaly']);
        $this->assertSame(['scored_try_no' => null, 'anomaly' => null], $n[5], 'a retake is never numbered');
    }

    private function try(array $values): array
    {
        return $values + ['id' => 1, 'attempt_id' => 1, 'item_id' => 1, 'try_no' => 1, 'occurred_at' => 0, 'hint_opened_before' => 0];
    }

    private function context(): array
    {
        return [
            'field_session_id' => 1, 'is_scored_attempt' => 1, 'is_retake' => 0, 'rapid_ms' => 3000, 'item_hint_before' => 0, 'item_narration_before' => 0, 'outside_session' => 0,
            'flags' => ['shared_device' => null, 'helped_content' => null, 'helped_technical' => null, 'encouraged' => null, 'disturbed' => null, 'deviation_code' => null],
        ];
    }
}
