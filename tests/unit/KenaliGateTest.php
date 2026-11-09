<?php

use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\ResearchModeWorld;

/**
 * Kenali after the region is finished (research revision 10, part 7: 33
 * §7.5, K9, §13.1 `KenaliGateTest`): in research mode the Kenali slides of
 * an unfinished region are not in the Peta Kedu page at all; they are once
 * the region is finished; outside research mode every region has them.
 *
 * @internal
 */
final class KenaliGateTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use ResearchModeWorld;

    private const SECRET = 'Candi di lereng Sumbing';

    private int $sessionId;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['gelita', 'content']);
        $this->sessionId = $this->createResearchModeWorld(2)['session']->id;

        foreach ($this->conn->table('levels')->get()->getResultArray() as $level) {
            $this->conn->table('dialogues')->insert([
                'level_id' => $level['id'], 'context_code' => 'region_intro', 'sequence' => 1, 'character_code' => 'mbah_kedu',
                'title_id' => 'Mengenal ' . $level['name_id'], 'title_en' => 'Meet ' . $level['name_en'],
                'text_id' => self::SECRET . ' ' . $level['code'], 'text_en' => 'Facts ' . $level['code'],
            ]);
        }

        service('contentRepository')->flush();
        service('cache')->clean();
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testKenaliOfAnUnfinishedRegionIsNotInThePage(): void
    {
        $this->signIn(1, $this->sessionId);
        $page = (string) $this->send(Method::GET, 'peta')->getBody();

        $this->assertStringNotContainsString(self::SECRET, $page);
        $this->assertStringNotContainsString('id="kenal-temanggung"', $page);
        $this->assertStringContainsString(lang('Game.kenaliAfterRegion'), $page);
        $this->assertStringNotContainsString(lang('Game.kenalUnheard'), $page, 'no "Belum didengar" badge on a locked Kenali');
    }

    public function testKenaliOpensOnceTheRegionIsFinished(): void
    {
        $this->markCompleted($this->sessionId, ['tmg-1', 'tmg-2', 'tmg-3', 'tmg-4', 'tmg-5']);
        $this->refreshProgress();
        $this->signIn(1, $this->sessionId);
        $page = (string) $this->send(Method::GET, 'peta')->getBody();

        $this->assertStringContainsString(self::SECRET . ' temanggung', $page);
        $this->assertStringNotContainsString(self::SECRET . ' magelang', $page);
    }

    public function testOutsideResearchModeEveryRegionHasKenali(): void
    {
        $this->conn->table('research_studies')->update(['field_mode' => 0]);
        $this->signIn(1, 1);
        $page = (string) $this->send(Method::GET, 'peta')->getBody();

        foreach (['temanggung', 'magelang', 'wonosobo'] as $code) {
            $this->assertStringContainsString(self::SECRET . ' ' . $code, $page);
        }

        $this->assertStringNotContainsString(lang('Game.kenaliAfterRegion'), $page);
    }

    /** The progress row follows the inserted attempts (levelOverview reads it). */
    private function refreshProgress(): void
    {
        $method = new ReflectionMethod(App\Services\ChallengeService::class, 'refreshProgress');
        $method->invoke(service('challengeService'), $this->gameSession($this->sessionId), service('contentRepository')->node($this->nodeId($this->conn, 'tmg-5')));
    }
}
