<?php

use App\Controllers\Game\BaseGameController;
use App\Entities\GameSession;
use App\Entities\Level;
use App\Services\ScoringService;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * Wilayah yang baru terbuka tidak dapat dimasuki lewat URL yang diketik
 * langsung sebelum dialog pembukanya tampil.
 *
 * @internal
 */
final class DialogueGateTest extends CIUnitTestCase
{
    /** Skor wilayah tiruan yang dikembalikan ScoringService::levelScore() */
    private array $score = ['score' => 0.0, 'stars' => 0, 'completed_nodes' => 0, 'total_nodes' => 5];

    protected function setUp(): void
    {
        parent::setUp();
        helper('url');

        $test = $this;

        Services::injectMock('scoringService', new class ($test) extends ScoringService {
            public function __construct(private DialogueGateTest $test)
            {
            }

            public function levelScore(int $sessionId, int $levelId): array
            {
                return $this->test->currentScore();
            }
        });

        session()->remove('dialogues_shown');
    }

    protected function tearDown(): void
    {
        Services::resetSingle('scoringService');
        session()->remove('dialogues_shown');
        parent::tearDown();
    }

    public function currentScore(): array
    {
        return $this->score;
    }

    public function testNewRegionRedirectsToDialogue(): void
    {
        $gate = $this->probe()->gate($this->gameSession(7), $this->level(2, 'magelang'));

        $this->assertInstanceOf(RedirectResponse::class, $gate);
        $this->assertStringEndsWith('/dialog/magelang', $gate->getHeaderLine('Location'));
    }

    public function testShownDialogueOpensTheRegion(): void
    {
        $probe   = $this->probe();
        $session = $this->gameSession(7);
        $level   = $this->level(2, 'magelang');

        $probe->mark($session, $level);

        $this->assertNull($probe->gate($session, $level));
    }

    public function testMarkIsPerGameSessionAndRegion(): void
    {
        $probe = $this->probe();
        $probe->mark($this->gameSession(7), $this->level(2, 'magelang'));

        $this->assertNotNull($probe->gate($this->gameSession(8), $this->level(2, 'magelang')), 'sesi permainan lain');
        $this->assertNotNull($probe->gate($this->gameSession(7), $this->level(3, 'wonosobo')), 'wilayah lain');
    }

    public function testRegionInProgressOrCompletedNeedsNoDialogue(): void
    {
        $probe = $this->probe();

        $this->score['completed_nodes'] = 2;
        $this->assertNull($probe->gate($this->gameSession(7), $this->level(2, 'magelang')));

        $this->score['completed_nodes'] = 5;
        $this->assertNull($probe->gate($this->gameSession(7), $this->level(2, 'magelang')));
    }

    // ------------------------------------------------------------ bantuan

    private function probe(): object
    {
        return new class () extends BaseGameController {
            public function gate(GameSession $session, Level $level): ?RedirectResponse
            {
                return $this->dialogueGate($session, $level);
            }

            public function mark(GameSession $session, Level $level): void
            {
                $this->markDialogueShown($session, $level);
            }
        };
    }

    private function gameSession(int $id): GameSession
    {
        $session = new GameSession();
        $session->injectRawData(['id' => $id]);

        return $session;
    }

    private function level(int $id, string $code): Level
    {
        $level = new Level();
        $level->injectRawData(['id' => $id, 'code' => $code, 'sequence' => $id]);

        return $level;
    }
}
