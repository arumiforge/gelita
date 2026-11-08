<?php

use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\ScenarioPlay;

/**
 * The scenario payload (32 §5.2, §15.1): no score, code, feedback, or raw
 * option key before a scenario is closed; the option order is the same on a
 * reload and on every try, and differs between attempts (K7).
 *
 * @internal
 */
final class ScenarioPayloadTest extends CIUnitTestCase
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

    public function testPayloadCarriesNoKeysScoresCodesOrFeedback(): void
    {
        ['attempt' => $attempt, 'payload' => $payload] = $this->openScenario('wnb-5');

        $this->assertArrayNotHasKey('items', $payload);
        $this->assertCount(4, $payload['scenarios']);

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE);

        foreach (['score', 'error_code', 'reason_code', 'feedback', 'explanation', 'safe_action', 'option_key', 'topic', 'd2_topic'] as $field) {
            $this->assertStringNotContainsString('"' . $field . '"', $json, $field);
        }

        $this->assertDoesNotMatchRegularExpression('/DIG-|R-(RISK|ETIK|ATUR|SOS|ACAK)|"[AR]\d"/', $json);

        foreach ($payload['scenarios'] as $scenario) {
            $this->assertSame(['id', 'prompt', 'media', 'steps', 'audio', 'audio_id', 'actions', 'reasons', 'state'], array_keys($scenario));
            $this->assertSame(['tries' => 0, 'closed' => false], $scenario['state']);
            $this->assertSame(2, $scenario['steps'], 'Wonosobo situations grow in two steps');
            $this->assertCount(4, $scenario['actions']);
            $this->assertCount(5, $scenario['reasons']);

            foreach ([...$scenario['actions'], ...$scenario['reasons']] as $option) {
                $this->assertSame(['ref', 'text'], array_keys($option));
                $this->assertSame(20, strlen($option['ref']));
            }
        }

        // The HTML of the challenge screen embeds the same payload
        $this->assertSame([], array_diff($this->scenarioIds($attempt), array_column($payload['scenarios'], 'id')));
    }

    public function testOrderStaysOnReloadAndTriesAndDiffersBetweenAttempts(): void
    {
        ['attempt' => $attempt, 'payload' => $first] = $this->openScenario('wnb-5');
        $item                                         = $this->scenarioIds($attempt)[0];

        $this->send($attempt, $item, 0);
        $again = $this->openScenario('wnb-5')['payload'];

        $this->assertSame(array_column($first['scenarios'][0]['actions'], 'text'), array_column($again['scenarios'][0]['actions'], 'text'));
        $this->assertSame(array_column($first['scenarios'][0]['reasons'], 'ref'), array_column($again['scenarios'][0]['reasons'], 'ref'));
        $this->assertSame(['tries' => 1, 'closed' => false], $again['scenarios'][0]['state'], 'an open scenario shows only its tries');

        // The order shown is stored on every try (answer_json.order)
        $order = json_decode((string) $this->tries($attempt->id)[0]['answer_json'], true)['order'];
        $texts = [];
        foreach (service('contentRepository')->scenarioOptions($item) as $option) {
            $texts[$option['option_key']] = $option['text_id'];
        }
        $this->assertSame(array_column($first['scenarios'][0]['actions'], 'text'), array_map(static fn (string $k): string => $texts[$k], $order['actions']));

        // Another attempt: other refs, and over 24 scenario lists at least one other order
        $orders = static fn (array $payload): array => array_map(static fn (array $s): array => array_column($s['actions'], 'text'), $payload['scenarios']);
        $this->conn->table('challenge_attempts')->where('id', $attempt->id)->update(['status' => 'abandoned']);
        $other = $this->openScenario('wnb-5');

        $this->assertNotSame($attempt->id, $other['attempt']->id);
        $this->assertNotSame(array_column($first['scenarios'][0]['actions'], 'ref'), array_column($other['payload']['scenarios'][0]['actions'], 'ref'));

        $changed = false;
        foreach (['tmg-5', 'mgl-5'] as $ref) {
            $one = $this->openScenario($ref);
            $this->conn->table('challenge_attempts')->where('id', $one['attempt']->id)->update(['status' => 'abandoned']);
            $two = $this->openScenario($ref);
            if ($one['attempt']->variant_group === $two['attempt']->variant_group && $orders($one['payload']) !== $orders($two['payload'])) {
                $changed = true;
            }
        }
        $this->assertTrue($changed || $orders($first) !== $orders($other['payload']) || $first['variant_group'] !== $other['payload']['variant_group'], 'the order follows the attempt');
    }

    public function testClosedScenarioCarriesItsOutcome(): void
    {
        $attempt = $this->openScenario()['attempt'];
        [$safe, $failed] = $this->scenarioIds($attempt);

        $this->send($attempt, $safe, 2);
        foreach ([0, 1, 0] as $score) {
            $this->send($attempt, $failed, $score);
        }

        $states = array_column($this->openScenario()['payload']['scenarios'], 'state', 'id');

        $this->assertSame(['safe', true], [$states[$safe]['outcome'], $states[$safe]['closed']]);
        $this->assertArrayNotHasKey('safe_action', $states[$safe]);
        $this->assertSame('exhausted', $states[$failed]['outcome']);
        $this->assertNotSame('', (string) $states[$failed]['safe_action']);
        $this->assertNotSame('', (string) $states[$failed]['explanation']);
        $this->assertSame($this->answer($attempt, $safe, 2), $states[$safe]['chosen']);
    }
}
