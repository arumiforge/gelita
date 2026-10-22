<?php

use App\Entities\ChallengeNode;
use App\Entities\Level;
use App\Services\ContentRepository;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * Owner request of 18 October 2026: the mission card (`/misi/{code}/{n}`)
 * after the owner's picture, and the gold main-button skin on every game
 * page. No database: ContentRepository is replaced by a stub.
 *
 * @internal
 */
final class MissionCardTest extends CIUnitTestCase
{
    /** asset_key → storage_path */
    private array $media = [];

    protected function setUp(): void
    {
        parent::setUp();
        helper(['url', 'gelita', 'content', 'ui']);

        $test = $this;

        Services::injectMock('contentRepository', new class ($test) extends ContentRepository {
            public function __construct(private MissionCardTest $test)
            {
            }

            public function nodesForLevel(int $levelId): array
            {
                return [MissionCardTest::node(1), MissionCardTest::node(2), MissionCardTest::node(3), MissionCardTest::node(4)];
            }

            public function mediaKeyMap(): array
            {
                return $this->test->media();
            }

            public function mediaMap(): array
            {
                return [];
            }
        });

        Services::resetSingle('renderer');
    }

    protected function tearDown(): void
    {
        Services::resetSingle('contentRepository');
        Services::resetSingle('renderer');
        parent::tearDown();
    }

    /** @return array<string, string> */
    public function media(): array
    {
        return $this->media;
    }

    public static function node(int $sequence): ChallengeNode
    {
        $node = new ChallengeNode();
        $node->injectRawData(['id' => 30 + $sequence, 'level_id' => 3, 'sequence' => $sequence, 'engine_type' => 'boleh',
            'title_id' => 'Benar atau Salah?', 'description_id' => 'Kabar tentang Dieng beredar cepat.',
            'instruction_id' => 'Setiap pilihan membawamu lebih dekat ke kebenaran.']);

        return $node;
    }

    public function testCardFollowsTheOwnersPicture(): void
    {
        $page = $this->brief();

        $this->assertStringContainsString('<span class="mission-seal num"><b>3</b><small>/4</small></span>', $page, 'medallion n/total');
        $this->assertMatchesRegularExpression('/<p class="mission-region">.*?<svg[^>]*>.*?<\/svg> <span>Wonosobo<\/span>/s', $page, 'pin and region');
        $this->assertStringContainsString(lang('Game.challengeOf', [3, 4]), $page, 'the position stays readable for screen readers');
        $this->assertStringContainsString('<h1 id="mission-title">Benar atau Salah?</h1>', $page);
        $this->assertStringContainsString('<p class="mission-description">Kabar tentang Dieng beredar cepat.</p>', $page);
        $this->assertStringContainsString('<p class="mission-instruction"><span class="mission-info" aria-hidden="true">i</span> <span>Setiap pilihan membawamu lebih dekat ke kebenaran.</span></p>', $page);
        $this->assertSame(4, substr_count($page, '<svg class="mission-corner is-'), 'gold corners');

        // The gold main button between two flourishes, still opening the challenge curtain
        $this->assertMatchesRegularExpression('/<a class="btn btn-primary btn-xl" href="[^"]*tantangan\/wonosobo\/3"[^>]*data-curtain="challenge"[^>]*>\s*<span>' . preg_quote(lang('Game.startChallenge'), '/') . '<\/span> <svg/', $page);
        $this->assertSame(2, substr_count(substr($page, (int) strpos($page, 'class="mission-actions"')), 'class="mission-wing'));
        $this->assertStringNotContainsString('class="mission-best"', $page, 'no best result before a first try');
    }

    public function testMainButtonsShareTheGoldPill(): void
    {
        // Owner, 22 October 2026: the shipped gold pills replace the ui.result-button-primary skin, even when that slot is filled
        $this->media = ['ui.result-button-primary' => 'assets/uploads/ui.result-button-primary.webp'];
        Services::resetSingle('renderer');
        $page = $this->brief();
        $this->assertMatchesRegularExpression('/<div class="stage" id="stage" style="--round-neutral: url\(&quot;[^"]*round-neutral\.webp[^"]*&quot;\); --gold-pill-1: url\(&quot;[^"]*gold-pill-1\.webp/', $page);
        $this->assertStringNotContainsString('--skin-primary', $page);
        foreach (['gold-pill-2', 'gold-pill-3', 'gold-pill-4', 'modal-frame-compact', 'modal-frame-wide', 'select-arrow'] as $var) {
            $this->assertStringContainsString('--' . $var . ': url(&quot;', $page, $var);
        }

        $css = (string) file_get_contents(FCPATH . 'assets/css/game.css');
        $this->assertStringContainsString('.stage[style*="--gold-pill-2"] .btn-primary {', $css, 'scoped to the stage: the admin panel keeps its buttons');
        $this->assertStringContainsString('border-image: var(--pill) 0 var(--pill-cut) fill / 0 var(--pill-end) stretch;', $css);
    }

    /** Owner, 21 October 2026: the logo alone in the HUD centre and Back to the region map on the plate top left. */
    public function testHudPutsTheLogoInTheMiddleAndBackInTheCorner(): void
    {
        $page = $this->brief();

        $this->assertStringContainsString('<header class="hud hud-menu-style is-compact is-logo-centre"', $page);
        $this->assertMatchesRegularExpression('~<a class="hud-back-plate" href="' . preg_quote(esc(base_url('wilayah/wonosobo'), 'attr'), '~') . '"[^>]*>.*?<span>' . preg_quote(esc(lang('Game.back')), '~') . '</span></a>~s', $page);
        $this->assertStringNotContainsString('hud-identity-region', $page);
        $this->assertStringNotContainsString('<nav class="nav-bar"', $page, 'no Back button at the foot');
    }

    /** Owner, 19 October 2026: the mission card plays the challenge music under its narrated line. */
    public function testCardPlaysTheChallengeMusic(): void
    {
        $this->assertStringContainsString('data-music="challenge"', $this->brief());
    }

    private function brief(): string
    {
        $level = new Level();
        $level->injectRawData(['id' => 3, 'code' => 'wonosobo', 'sequence' => 3, 'name_id' => 'Wonosobo', 'name_en' => 'Wonosobo',
            'difficulty' => 'sulit', 'background_media_id' => null, 'map_media_id' => null]);

        return view('game/mission-brief', [
            'level'    => $level,
            'node'     => self::node(3),
            'sequence' => 3,
            'best'     => null,
            'locale'   => 'id',
        ]);
    }
}
