<?php

use App\Exceptions\ChallengeRuleException;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\ScenarioPlay;

/**
 * Skenario Digital tries (32 §5.4, §15.1): the safe action closes a scenario
 * as correct; a score-1 action uses one try; three tries without it close the
 * scenario as not correct with `scenario_failed`; a fourth send is CLUE_CLOSED;
 * a missing reason is INCOMPLETE; an unknown ref is refused.
 *
 * @internal
 */
final class ScenarioEngineTest extends CIUnitTestCase
{
    use ScenarioPlay;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareScenarioPlay();
    }

    protected function tearDown(): void
    {
        $this->finishScenarioPlay();
        parent::tearDown();
    }

    public function testSafeActionClosesTheScenarioOnTheFirstTry(): void
    {
        $attempt = $this->openScenario()['attempt'];
        $item    = $this->scenarioIds($attempt)[0];
        $result  = $this->send($attempt, $item, 2);

        $this->assertSame([true, 'safe', 1, 2, true, false], [$result['correct'], $result['score_band'], $result['try_no'], $result['tries_left'], $result['scenario_closed'], $result['completed']]);
        $this->assertNotSame('', (string) $result['feedback'], 'feedback of the score-2 action, as written (32 §18 no. 8)');
        $this->assertArrayNotHasKey('safe_action', $result);

        $row = $this->conn->table('item_responses')->where('challenge_attempt_id', $attempt->id)->where('challenge_item_id', $item)->get()->getRowArray();
        $this->assertSame(['answered', 1, 1, 1], [$row['status'], (int) $row['is_correct'], (int) $row['first_pass_correct'], (int) $row['solved_on_try']]);
    }

    public function testPartialThenSafeIsCorrectOnTheSecondTry(): void
    {
        $attempt = $this->openScenario()['attempt'];
        $item    = $this->scenarioIds($attempt)[0];

        $first = $this->send($attempt, $item, 1);
        $this->assertSame(['partial', false, 2], [$first['score_band'], $first['scenario_closed'], $first['tries_left']]);

        $second = $this->send($attempt, $item, 2);
        $this->assertSame(['safe', true, 2], [$second['score_band'], $second['scenario_closed'], $second['try_no']]);

        $row = $this->conn->table('item_responses')->where('challenge_attempt_id', $attempt->id)->where('challenge_item_id', $item)->get()->getRowArray();
        $this->assertSame([1, 0, 2], [(int) $row['is_correct'], (int) $row['first_pass_correct'], (int) $row['solved_on_try']]);
        $this->assertSame([], $this->events('scenario_failed'));
    }

    public function testThreeTriesWithoutTheSafeActionCloseTheScenario(): void
    {
        $attempt = $this->openScenario()['attempt'];
        $item    = $this->scenarioIds($attempt)[0];

        $this->send($attempt, $item, 0);
        $this->send($attempt, $item, 1);
        $last = $this->send($attempt, $item, 0);

        $this->assertSame(['risky', true, 0, 3], [$last['score_band'], $last['scenario_closed'], $last['tries_left'], $last['try_no']]);
        $this->assertNotSame('', (string) $last['safe_action'], 'the safe action is shown after the third try');
        $this->assertNotSame('', (string) $last['explanation']);
        $this->assertSame([['try_count' => 3]], $this->events('scenario_failed'));

        $row = $this->conn->table('item_responses')->where('challenge_attempt_id', $attempt->id)->where('challenge_item_id', $item)->get()->getRowArray();
        $this->assertSame(['answered', 0, 3], [$row['status'], (int) $row['is_correct'], (int) $row['try_count']]);

        // A fourth send for the closed scenario
        try {
            $this->send($attempt, $item, 2);
            $this->fail('A closed scenario takes no more tries.');
        } catch (ChallengeRuleException $e) {
            $this->assertSame(['CLUE_CLOSED', 409], [$e->errorCode, $e->status]);
        }

        $this->assertCount(3, $this->tries($attempt->id, $item));
    }

    public function testMissingReasonIsIncompleteAndUnknownRefIsRefused(): void
    {
        $attempt = $this->openScenario()['attempt'];
        $item    = $this->scenarioIds($attempt)[0];
        $answer  = $this->answer($attempt, $item, 2);

        try {
            $this->service->submitAnswer($attempt, $item, ['action' => $answer['action']]);
            $this->fail('A try needs a reason.');
        } catch (ChallengeRuleException $e) {
            $this->assertSame(['INCOMPLETE', 422], [$e->errorCode, $e->status]);
        }

        $this->expectException(InvalidArgumentException::class);

        try {
            $this->service->submitAnswer($attempt, $item, ['action' => 'A1', 'reason' => $answer['reason']]);
        } finally {
            $this->assertSame([], $this->tries($attempt->id), 'nothing written');
        }
    }

    public function testUnknownRefOverHttpIsA400(): void
    {
        $attempt = $this->openScenario()['attempt'];
        $item    = $this->scenarioIds($attempt)[0];

        try {
            $this->service->submitAnswer($attempt, $item, ['action' => str_repeat('0', 20), 'reason' => str_repeat('1', 20)]);
            $this->fail('An unknown ref is refused.');
        } catch (InvalidArgumentException $e) {
            // ChallengeApiController::respond() answers InvalidArgumentException with 400 INVALID_RESPONSE
            $this->assertStringContainsString('tidak dikenal', $e->getMessage());
        }
    }
}
