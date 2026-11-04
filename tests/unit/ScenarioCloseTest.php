<?php

use App\Models\ChallengeAttemptModel;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\ScenarioPlay;

/**
 * Closing a Skenario Digital attempt (32 §4 step 6, §5.5): `solved` when all
 * four scenarios are correct, `tries_exhausted` otherwise; stars follow
 * `bintang-1`; /check refuses the engine.
 *
 * @internal
 */
final class ScenarioCloseTest extends CIUnitTestCase
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

    public function testAllSafeOnTheFirstTryIsSolvedWithThreeStars(): void
    {
        $attempt = $this->openScenario()['attempt'];
        $ids     = $this->scenarioIds($attempt);

        foreach ($ids as $n => $item) {
            $result = $this->send($attempt, $item, 2);
            $this->assertSame($n === 3, $result['completed']);
        }

        $this->assertSame(['solved', 3], [$result['completion_reason'], $result['stars']]);
        $this->assertStringEndsWith('selesai/' . $attempt->id, (string) $result['redirect']);
        $row = model(ChallengeAttemptModel::class)->find($attempt->id);
        $this->assertSame(['completed', 0], [$row->status, (int) $row->check_count]);
    }

    public function testOneScenarioClosedAfterThreeTriesIsTriesExhausted(): void
    {
        $attempt = $this->openScenario()['attempt'];
        [$a, $b, $c, $d] = $this->scenarioIds($attempt);

        foreach ([0, 0, 1] as $score) {
            $result = $this->send($attempt, $a, $score);
        }
        $this->assertFalse($result['completed']);

        $this->send($attempt, $b, 2);
        $this->send($attempt, $c, 1);
        $this->send($attempt, $c, 2);
        $result = $this->send($attempt, $d, 2);

        // bintang-1: 3 of 4 correct at the end, 2 of 4 on the first pass
        $scored = \App\Services\ScoringService::starsFromCounts(true, 4, 2, 3);
        $this->assertSame(['tries_exhausted', $scored], [$result['completion_reason'], $result['stars']]);
        $this->assertSame(1, (int) $this->conn->table('participant_progress')->where('participant_id', 1)->get()->getRow('completed_nodes'), 'the shard is earned');
    }

    public function testCheckRefusesTheScenarioEngine(): void
    {
        $attempt = $this->openScenario()['attempt'];

        $this->expectException(InvalidArgumentException::class);
        $this->service->submitCheck($attempt, []);
    }
}
