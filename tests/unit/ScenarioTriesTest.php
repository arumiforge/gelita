<?php

use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\ScenarioPlay;

/**
 * One scenario try in `item_tries` (32 §6.4, §10, §11): action key and
 * score, reason key and code, the order shown, and the client times; times
 * out of range or later than `last_input_ms` are stored empty.
 *
 * @internal
 */
final class ScenarioTriesTest extends CIUnitTestCase
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

    public function testTryStoresActionReasonOrderAndTimes(): void
    {
        $attempt = $this->openScenario()['attempt'];
        $item    = $this->scenarioIds($attempt)[0];
        $keys    = $this->keys($item, 0);

        $this->send($attempt, $item, 0, [
            'ready_ms' => 1200, 'reason_ready_ms' => 5400, 'first_input_ms' => 3000,
            'action_last_input_ms' => 4100, 'reason_last_input_ms' => 7000, 'last_input_ms' => 8200,
        ]);

        $try   = $this->tries($attempt->id)[0];
        $codes = [];
        foreach (service('contentRepository')->scenarioOptions($item) as $option) {
            $codes[$option['kind'] . $option['option_key']] = $option['code'];
        }

        $this->assertSame([$keys['action'], 0, $keys['reason'], $codes['reason' . $keys['reason']], $codes['action' . $keys['action']]], [
            $try['action_key'], (int) $try['action_score'], $try['reason_key'], $try['reason_code'], $try['error_code'],
        ]);
        $this->assertSame([5400, 3000, 4100, 7000, 8200], array_map('intval', [
            $try['reason_ready_ms'], $try['first_input_ms'], $try['action_last_input_ms'], $try['reason_last_input_ms'], $try['last_input_ms'],
        ]));
        $this->assertArrayNotHasKey('ready_ms', $try, 'WAVE-E seam: ready_ms is created by part 3');

        $answer = json_decode((string) $try['answer_json'], true);
        $this->assertSame([$keys['action'], $keys['reason']], [$answer['action'], $answer['reason']]);
        $this->assertSame(['actions', 'reasons'], array_keys($answer['order']));
        $this->assertCount(3, $answer['order']['actions'], 'Temanggung: 3 actions');

        // The safe try has no error code
        $this->send($attempt, $item, 2);
        $this->assertNull($this->tries($attempt->id)[1]['error_code']);
        $this->assertSame(2, (int) $this->tries($attempt->id)[1]['action_score']);
    }

    public function testTimesLaterThanTheSendOrOutOfRangeAreEmpty(): void
    {
        $attempt = $this->openScenario()['attempt'];
        $item    = $this->scenarioIds($attempt)[0];

        $this->send($attempt, $item, 1, [
            'reason_ready_ms' => 9000, 'first_input_ms' => -5, 'action_last_input_ms' => 'abc',
            'reason_last_input_ms' => 8001, 'last_input_ms' => 8000,
        ]);

        $try = $this->tries($attempt->id)[0];
        $this->assertSame([null, null, null, null, 8000], [
            $try['reason_ready_ms'], $try['first_input_ms'], $try['action_last_input_ms'], $try['reason_last_input_ms'], (int) $try['last_input_ms'],
        ]);
    }
}
