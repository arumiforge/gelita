<?php

use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Encryption;
use Tests\Support\Database\ResearchModeWorld;

/**
 * Research mode hides the clock (research revision 10, part 7: 33 §7.1, K8,
 * §13.1 `ResearchClockTest`) and locks Kenali until the region is finished
 * (§7.5, K9, `KenaliGateTest` is its own class). ✓/✗ stay; outside research
 * mode everything shows as before.
 *
 * @internal
 */
final class ResearchClockTest extends CIUnitTestCase
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
        config(Encryption::class)->key = 'kunci-uji-jam';
        $this->sessionId               = $this->createResearchModeWorld(2)['session']->id;
        $this->markCompleted($this->sessionId, ['tmg-1']);
    }

    protected function tearDown(): void
    {
        $_SESSION                      = [];
        config(Encryption::class)->key = $this->originalKey;
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testResearchModeRendersNoClock(): void
    {
        $this->signIn(1, $this->sessionId);

        $play = (string) $this->send(Method::GET, 'tantangan/temanggung/2')->getBody();
        $this->assertStringContainsString('id="count-correct"', $play);
        $this->assertStringContainsString('id="count-wrong"', $play);
        $this->assertStringNotContainsString('id="timer"', $play);
        $this->assertStringNotContainsString('elapsed_ms', $play);

        $result = (string) $this->send(Method::GET, 'hasil/temanggung/1')->getBody();
        $this->assertStringNotContainsString(lang('Game.timeSpent'), $result);
        $this->assertStringNotContainsString(lang('Game.duration'), $result);

        $profile = (string) $this->send(Method::GET, 'profil')->getBody();
        $this->assertStringNotContainsString(lang('Game.totalTime'), $profile);
        $this->assertStringContainsString('profile-research-code', $profile);
        $this->assertStringContainsString('role="region"', $profile);
    }

    public function testOutsideResearchModeTheClockShows(): void
    {
        $this->conn->table('research_studies')->update(['field_mode' => 0, 'unlock_mode' => 'free']);
        $this->signIn(1, 1);

        $play = (string) $this->send(Method::GET, 'tantangan/temanggung/2')->getBody();
        $this->assertStringContainsString('id="timer"', $play);
        $this->assertStringContainsString('elapsed_ms', $play);

        $this->markCompleted(1, ['tmg-3']);
        $this->assertStringContainsString(lang('Game.timeSpent'), (string) $this->send(Method::GET, 'hasil/temanggung/3')->getBody());
        $this->assertStringContainsString(lang('Game.totalTime'), (string) $this->send(Method::GET, 'profil')->getBody());
    }

    public function testFinishedScreenHasNoTimeCardInResearchMode(): void
    {
        $attempt = $this->openIn($this->sessionId, 'tmg-2');
        $answers = [];

        foreach (service('contentRepository')->itemBank($attempt->challenge_node_id) as $item) {
            if ($attempt->hasItem($item->id) && ! $item->isDecoy() && $item->scorable) {
                $answers[$item->id] = ['text' => (string) $item->answerKey('text_id')];
            }
        }

        $this->assertTrue(service('challengeService')->submitCheck(model(\App\Models\ChallengeAttemptModel::class)->find($attempt->id), $answers)['completed']);

        $this->signIn(1, $this->sessionId);
        $page = (string) $this->send(Method::GET, 'selesai/' . $attempt->id)->getBody();
        $this->assertStringContainsString('result-stats', $page);
        $this->assertStringNotContainsString(lang('Game.timeSpent'), $page);
    }
}
