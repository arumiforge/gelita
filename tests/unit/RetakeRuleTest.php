<?php

use App\Exceptions\ChallengeRuleException;
use App\Models\ChallengeAttemptModel;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Encryption;
use Tests\Support\Database\ResearchModeWorld;

/**
 * Retakes (research revision 10, part 7: 33 §7.4, K5, K6, §13.1
 * `RetakeRuleTest`): in research mode a finished challenge opens again only
 * when all five challenges of its region are finished in a meeting that
 * opens the region; `is_retake` is written in every mode; stars stay the best.
 *
 * @internal
 */
final class RetakeRuleTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use ResearchModeWorld;

    private string $originalKey;

    private int $sessionId;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['gelita', 'content']);
        $this->originalKey             = (string) config(Encryption::class)->key;
        config(Encryption::class)->key = 'kunci-uji-ulang';
        $this->sessionId               = $this->createResearchModeWorld(2)['session']->id;
    }

    protected function tearDown(): void
    {
        $_SESSION                      = [];
        config(Encryption::class)->key = $this->originalKey;
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testRetakeWaitsForTheWholeRegion(): void
    {
        $this->markCompleted($this->sessionId, ['tmg-1', 'tmg-2']);
        $first = $this->openIn($this->sessionId, 'tmg-3');
        $this->assertSame(0, (int) $first->is_retake);
        $this->assertTrue($this->solveBatch($first)['completed']);

        $node = $this->nodeId($this->conn, 'tmg-3');

        try {
            service('challengeService')->openNode($this->gameSession($this->sessionId), $node);
            $this->fail('retake opened before the region was finished');
        } catch (ChallengeRuleException $e) {
            $this->assertSame(['RETAKE_LOCKED', 409], [$e->errorCode, $e->status]);
        }

        // The API cannot skip the rule
        $response = $this->api('api/nodes/' . $node . '/attempts');
        $response->assertStatus(409);
        $this->assertSame('RETAKE_LOCKED', json_decode((string) $response->getJSON(), true)['code']);

        // The HTML screen goes back to the region map with the friendly text
        $this->signIn(1, $this->sessionId);
        $page = $this->send(Method::GET, 'tantangan/temanggung/3');
        $this->assertRedirectsTo('wilayah/temanggung', $page);

        // Region finished: the retake opens, is marked, and keeps the best stars
        $this->markCompleted($this->sessionId, ['tmg-4', 'tmg-5']);
        $retake = $this->openIn($this->sessionId, 'tmg-3');
        $this->assertNotSame($first->id, $retake->id);
        $this->assertSame(1, (int) $retake->is_retake);

        service('challengeService')->abandonAttempt($retake);
        $best = model(ChallengeAttemptModel::class)->completedFor($this->gameSession($this->sessionId))[$node];
        $this->assertSame($first->id, $best->id, 'the best attempt stays');
    }

    public function testRegionOutsideTheMeetingPlanStaysClosed(): void
    {
        $this->markCompleted($this->sessionId, ['tmg-1', 'tmg-2', 'tmg-3', 'tmg-4', 'tmg-5']);
        $this->resetFieldServices();
        $session = $this->gameSession($this->sessionId);
        $node    = service('contentRepository')->node($this->nodeId($this->conn, 'tmg-3'));

        $this->assertTrue(service('challengeService')->retakeAllowed($session, $node));

        // The field session of this game session closes: no retakes any more
        $this->conn->table('field_sessions')->update(['closed_at' => date('Y-m-d H:i:s')]);
        $this->resetFieldServices();
        $this->assertFalse(service('challengeService')->retakeAllowed($this->gameSession($this->sessionId), $node));
    }

    public function testOutsideResearchModeRetakesOpenAnyTimeAndAreMarked(): void
    {
        $this->conn->table('research_studies')->update(['field_mode' => 0, 'unlock_mode' => 'free']);
        $this->resetFieldServices();

        $first = $this->openIn(1, 'tmg-3');
        $this->solveBatch($first);
        $retake = $this->openIn(1, 'tmg-3');

        $this->assertSame([0, 1], [(int) $first->is_retake, (int) $retake->is_retake]);
    }

    private function api(string $path): \CodeIgniter\Test\TestResponse
    {
        $this->resetFieldServices();

        return $this->withSession(['participant_id' => 1, 'game_session_id' => $this->sessionId, 'gelita_csrf' => $this->csrfHash])
            ->withHeaders(['X-CSRF-TOKEN' => $this->csrfHash . str_repeat('0', 32)])
            ->withBodyFormat('json')
            ->call(Method::POST, $path, []);
    }
}
