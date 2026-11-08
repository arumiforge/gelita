<?php

use App\Entities\ItemResponse;
use App\Exceptions\ChallengeRuleException;
use App\Models\ItemResponseModel;
use CodeIgniter\Config\Factories;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\ScenarioPlay;

/**
 * Two tabs send the same scenario try (32 §5.4): the stale send carries the
 * old `try_count`, loses in updateOrStale(), and is answered from the latest
 * state; no two item_tries share a try number. SQLite has no row locks, so
 * the stale read is simulated with a mock model (pattern of TryLimitTest).
 *
 * @internal
 */
final class ScenarioRaceTest extends CIUnitTestCase
{
    use ScenarioPlay;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareScenarioPlay();
    }

    protected function tearDown(): void
    {
        Factories::reset('models');
        $this->finishScenarioPlay();
        parent::tearDown();
    }

    public function testStaleSendLosesAndIsAnsweredFromTheLatestState(): void
    {
        $attempt = $this->openScenario()['attempt'];
        [$a, $b] = $this->scenarioIds($attempt);

        // A try that leaves the scenario open → ATTEMPT_CHANGED
        $stale = $this->staleResponses($attempt->id, $a);
        $this->send($attempt, $a, 0);
        Factories::injectMock('models', ItemResponseModel::class, $stale);

        try {
            $this->send($attempt, $a, 1);
            $this->fail('A stale send loses.');
        } catch (ChallengeRuleException $e) {
            $this->assertSame(['ATTEMPT_CHANGED', 409], [$e->errorCode, $e->status]);
        }

        Factories::reset('models');
        $this->assertSame([1], array_map('intval', array_column($this->tries($attempt->id, $a), 'try_no')));

        // A try that closed the scenario → CLUE_CLOSED
        $stale = $this->staleResponses($attempt->id, $b);
        $this->send($attempt, $b, 2);
        Factories::injectMock('models', ItemResponseModel::class, $stale);

        try {
            $this->send($attempt, $b, 0);
            $this->fail('A send to a scenario closed in another tab loses.');
        } catch (ChallengeRuleException $e) {
            $this->assertSame('CLUE_CLOSED', $e->errorCode);
        }

        Factories::reset('models');
        $this->assertCount(1, $this->tries($attempt->id, $b));
        $this->assertSame([], $this->events('scenario_failed'));
    }

    private function staleResponses(int $attemptId, int $itemId): ItemResponseModel
    {
        $model = new class () extends ItemResponseModel {
            public ?ItemResponse $stale = null;
            public int $reads           = 1;

            public function findOne(int $attemptId, int $itemId): ?ItemResponse
            {
                if ($this->stale !== null && $this->reads > 0 && $this->stale->challenge_item_id === $itemId) {
                    $this->reads--;

                    return clone $this->stale;
                }

                return parent::findOne($attemptId, $itemId);
            }
        };
        $model->stale = $model->findOne($attemptId, $itemId);

        return $model;
    }
}
