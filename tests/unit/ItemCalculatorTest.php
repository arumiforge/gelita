<?php

use App\Libraries\Derived\ItemCalculator;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Part 9 (35 §6.2; 11 → Analitik 2–4, 9, 10, Data hilang).
 *
 * @internal
 */
final class ItemCalculatorTest extends CIUnitTestCase
{
    public function testAnItemWithoutTriesIsMissingNotWrong(): void
    {
        $row = ItemCalculator::row($this->item('verdict_card'), [], 'not_started', null, [null, null]);

        $this->assertSame('not_started', $row['missing_reason']);
        $this->assertNull($row['first_try_score']);
        $this->assertNull($row['final_correct']);
        $this->assertNull($row['censored']);
    }

    public function testCensoredIsAFlagNeverThreeOrFour(): void
    {
        $three = ItemCalculator::row($this->item('verdict_card'), [$this->try(0, 'INF-LIT'), $this->try(0, 'INF-LIT'), $this->try(0, 'INF-KAT')], 'no_try', null, [null, null]);
        $left  = ItemCalculator::row($this->item('verdict_card'), [$this->try(0, 'INF-LIT')], 'no_try', null, [null, null]);

        $this->assertSame([1, 'exhausted', 3, null, 0], [$three['censored'], $three['censor_reason'], $three['tries_used'], $three['solved_on_try'], $three['final_correct']]);
        $this->assertSame([1, 'left'], [$left['censored'], $left['censor_reason']]);
        $this->assertSame('same_error', $three['change_after_feedback']);
        $this->assertSame('other_error', $three['change_after_feedback_2']);
        $this->assertSame('no_second_try', $left['change_after_feedback']);
    }

    public function testSolvedOnTheSecondTry(): void
    {
        $row = ItemCalculator::row($this->item('verdict_card'), [$this->try(0, 'INF-LIT'), $this->try(1)], 'no_try', null, [null, null]);

        $this->assertSame([0, 0, 1, 2, 0, 'fixed', 'INF-LIT'], [$row['first_try_score'], $row['first_try_correct'], $row['final_correct'], $row['solved_on_try'], $row['censored'], $row['change_after_feedback'], $row['first_error_code']]);
        $this->assertNull($row['missing_reason']);
    }

    public function testScenarioChangeIsJudgedByTheActionKeyAndScoreChangeIsKept(): void
    {
        // Two different actions share one code in the bank (11 → Analitik 9): judged by the key
        $first  = $this->try(0, 'DIG-IZN', ['action_key' => 't1', 'action_score' => 0, 'reason_code' => 'R-ACAK']);
        $second = $this->try(0, 'DIG-IZN', ['action_key' => 't3', 'action_score' => 1]);
        $row    = ItemCalculator::row($this->item('scenario_choice'), [$first, $second], 'no_try', null, [null, null]);

        $this->assertSame('other_error', $row['change_after_feedback']);
        $this->assertSame(1, $row['score_change']);
        $this->assertSame(['t1', 0, 'DIG-IZN', 'R-ACAK', 'risky'], [$row['first_action_key'], $row['first_action_score'], $row['first_action_code'], $row['first_reason_code'], $row['action_reason_pattern']]);
        $this->assertSame(1, $row['final_score'], 'D2 final score = the last try');
        $this->assertNull($row['reason_level'], 'empty until the reason order is set');

        $safe = ItemCalculator::row($this->item('scenario_choice'), [$this->try(1, null, ['action_key' => 't2', 'action_score' => 2, 'reason_code' => 'R-ATUR'])], 'no_try', null, [null, null]);
        $this->assertSame([2, 1, 'safe_rule'], [$safe['first_try_score'], $safe['first_try_correct'], $safe['action_reason_pattern']]);
    }

    public function testNearMissAt24Dp(): void
    {
        // Key box x 50 %, 8.64 dp per % across: 2 % = 17.28 dp (near), 3 % = 25.92 dp (far)
        $box  = [50.0, 50.0, 10.0, 10.0];
        $taps = [['x_pct' => 48.0, 'y_pct' => 55.0], ['x_pct' => 47.0, 'y_pct' => 55.0], ['overflow' => 4]];

        $this->assertSame([1, 5], ItemCalculator::taps($taps, $box));
        $this->assertEqualsWithDelta(24.0, ItemCalculator::distanceDp(50 - 24 / 8.64, 55.0, $box), 1e-6);

        $row = ItemCalculator::row(['key_box' => $box] + $this->item('find_object'), [$this->try(1, null, ['ready_ms' => 900])], 'no_try', $taps, [1000, 900]);
        $this->assertSame([1, 5, 1000, 900], [$row['near_miss_count'], $row['empty_tap_count'], $row['item_total_ms'], $row['item_active_ms']]);

        $old = ItemCalculator::row(['key_box' => $box] + $this->item('find_object'), [$this->try(1)], 'no_try', $taps, [null, null]);
        $this->assertNull($old['near_miss_count'], 'old data without ready_ms stays empty (35 §18 no. 5)');
    }

    private function item(string $type): array
    {
        return ['id' => 9, 'interaction_type' => $type, 'key_box' => null];
    }

    private function try(int $correct, ?string $code = null, array $extra = []): array
    {
        static $n = 0;

        return $extra + [
            'id' => ++$n, 'is_correct' => $correct, 'answer_hash' => 'h' . $n, 'ready_ms' => null, 'first_input_ms' => 100, 'last_input_ms' => 200,
            'row' => ['error_code' => $code, 'error_code_at_try' => $code, 'error_code_analysis' => $code, 'shared_device' => 0, 'helped_content' => 0, 'rapid_guess' => null],
        ];
    }
}
