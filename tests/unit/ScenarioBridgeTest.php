<?php

use App\Database\Seeds\ContentSeeder;
use CodeIgniter\Config\Factories;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\TestResponse;
use Config\Services;
use Tests\Support\Database\ScenarioPlay;

/**
 * The bridge story `scenario_open` (32 §7.2, K9): opening the fifth challenge
 * the first time leads to `/penghubung/{region}`; once watched, the mission
 * brief opens; once the student has an attempt there, the gate never shows
 * again, also in another game session (another meeting).
 *
 * @internal
 */
final class ScenarioBridgeTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use ScenarioPlay;

    private const CSRF_HASH = '0123456789abcdef0123456789abcdef';

    protected function setUp(): void
    {
        parent::setUp();
        helper(['url', 'ui']);
        $this->prepareScenarioPlay();

        (new ContentSeeder(config('Database'), $this->conn))->seedDialogues();
        $this->conn->table('participants')->update(['intro_seen_at' => date('Y-m-d H:i:s')]);
        service('contentRepository')->flush();
        service('cache')->clean();
        // The region dialogue of Temanggung was shown in this login (dialogueGate passes)
        $_SESSION = ['dialogues_shown' => ['1:1' => true, '2:1' => true]];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        Factories::reset('models');
        $this->finishScenarioPlay();
        parent::tearDown();
    }

    public function testFirstOpenGoesToTheBridgeStoryThenToTheBrief(): void
    {
        $this->assertCount(6, service('contentRepository')->dialogues(1, 'scenario_open'));

        $first = $this->send('misi/temanggung/5');
        $first->assertRedirect();
        $this->assertStringEndsWith('/penghubung/temanggung', $first->getRedirectUrl());

        $play = $this->send('tantangan/temanggung/5');
        $this->assertStringEndsWith('/penghubung/temanggung', $play->getRedirectUrl(), 'the challenge URL is gated too');

        $bridge = $this->send('penghubung/temanggung');
        $bridge->assertOK();
        $html = (string) $bridge->response()->getBody();
        $this->assertStringContainsString('data-context="scenario_open"', $html);
        $this->assertStringContainsString('Kabut Lupa bisa masuk ke dalam layar', $html);
        $this->assertStringContainsString(esc(base_url('misi/temanggung/5'), 'attr'), $html);

        $this->send('misi/temanggung/5')->assertOK();
    }

    public function testNoGateOnceAnAttemptExistsAlsoInAnotherGameSession(): void
    {
        $this->openScenario('tmg-5');

        // A new meeting: another game session, a fresh PHP session without the flag
        $this->conn->table('game_sessions')->insert([
            'session_code' => 'S-KELAS5-2', 'participant_id' => 1, 'study_id' => 1, 'release_id' => 1, 'locale' => 'id', 'status' => 'active',
        ]);
        $_SESSION = ['dialogues_shown' => ['2:1' => true]];

        $this->send('misi/temanggung/5', 2)->assertOK();

        // The region map offers the bridge story again
        $map = (string) $this->send('wilayah/temanggung', 2)->response()->getBody();
        $this->assertStringContainsString('class="node-bridge" href="' . esc(base_url('penghubung/temanggung'), 'attr') . '"', $map);
        $this->assertStringContainsString(lang('Game.watchBridgeStory'), $map);
    }

    public function testOtherChallengesAreNotGated(): void
    {
        $this->send('misi/temanggung/4')->assertOK();
        $this->assertStringNotContainsString('node-bridge', (string) $this->send('wilayah/temanggung')->response()->getBody(), 'not watched yet');
    }

    private function send(string $path, int $gameSession = 1): TestResponse
    {
        foreach (['security', 'gameContext', 'sessionService', 'eventService'] as $name) {
            Services::resetSingle($name);
        }
        unset($_SESSION['errors'], $_SESSION['__ci_vars'], $_SESSION['message'], $_SESSION['error']);

        return $this->withSession(['gelita_csrf' => self::CSRF_HASH, 'participant_id' => 1, 'game_session_id' => $gameSession] + $_SESSION)->call(Method::GET, $path);
    }
}
