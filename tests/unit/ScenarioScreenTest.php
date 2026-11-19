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
 * The Skenario Digital screen of the owner-approved mockup (41 step D, decisions
 * 17–43, 19 November 2026): the interface pictures SD-01 to SD-11 from
 * public/assets/ui/, the instruction in the right side instead of the frame's
 * aside, the buttons inside the right side, and the feedback headings in both
 * languages. The research rules of 41 §2 (no keys or scores in the page) stay
 * covered by ScenarioPayloadTest.
 *
 * @internal
 */
final class ScenarioScreenTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use ScenarioPlay;

    private const CSRF_HASH = '0123456789abcdef0123456789abcdef';

    private const PICTURES = ['sd-next', 'sd-next-long', 'sd-back', 'sd-step-1', 'sd-step-1-on', 'sd-step-2', 'sd-step-2-on', 'sd-chosen', 'sd-chosen-2', 'sd-listen', 'sd-listen-wide'];

    protected function setUp(): void
    {
        parent::setUp();
        helper(['url', 'ui']);
        $this->prepareScenarioPlay();

        (new ContentSeeder(config('Database'), $this->conn))->seedDialogues();
        $this->conn->table('participants')->update(['intro_seen_at' => date('Y-m-d H:i:s')]);
        service('contentRepository')->flush();
        service('cache')->clean();
        $_SESSION = ['dialogues_shown' => ['1:1' => true, '2:1' => true]];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        Factories::reset('models');
        $this->finishScenarioPlay();
        parent::tearDown();
    }

    public function testScreenDrawsTheApprovedMockupParts(): void
    {
        $this->openScenario('tmg-5');
        $html = (string) $this->page('tantangan/temanggung/5')->response()->getBody();

        // Each interface part is passed as a versioned URL (asset_url_versioned) on #scenario
        $this->assertSame(1, preg_match('~id="scenario"[^>]*style="([^"]*)"~', $html, $style));
        $style = html_entity_decode($style[1], ENT_QUOTES | ENT_HTML5);
        foreach (self::PICTURES as $name) {
            $this->assertFileExists(FCPATH . 'assets/ui/' . $name . '.webp');
            $this->assertMatchesRegularExpression('~--' . preg_quote($name, '~') . ': url\("[^"]*/assets/ui/' . preg_quote($name, '~') . '\.webp\?v=~', $style);
        }

        // The tabs, the instruction row of step 1, and the buttons are inside the right side
        $steps = substr($html, (int) strpos($html, 'class="scenario-steps'));
        $this->assertStringContainsString('<li data-step="action" class="is-current">' . lang('Js.scenarioTabAction') . '</li>', $steps);
        $this->assertStringContainsString('id="scenario-hint"', $steps);
        $this->assertStringContainsString('<p class="scenario-instruction">', $steps);
        foreach (['btn-scenario-back', 'btn-scenario-go', 'btn-next'] as $id) {
            $this->assertStringContainsString('id="' . $id . '"', $steps);
        }
        $this->assertStringNotContainsString('class="arena-actions"', $html);
        // The frame's aside no longer carries the instruction on this screen
        $this->assertStringNotContainsString('<p class="instruction', $html);
        // One bar per story above the picture
        $this->assertSame(4, substr_count(substr($html, (int) strpos($html, 'id="scenario-bars"'), 200), '<i'));
    }

    public function testFeedbackHeadingsAndTabWordsInBothLanguages(): void
    {
        $id = require APPPATH . 'Language/id/Js.php';
        $en = require APPPATH . 'Language/en/Js.php';

        // Owner, 19 November 2026 (41 §3)
        $this->assertSame(['Pilihan yang aman!', 'Ayo pikirkan lagi'], [$id['scenarioSafeTitle'], $id['scenarioThinkTitle']]);
        $this->assertSame(['A safe choice!', "Let's think again"], [$en['scenarioSafeTitle'], $en['scenarioThinkTitle']]);
        // The number is in the tab picture, so the word stands alone
        $this->assertSame(['Tindakan', 'Alasan'], [$id['scenarioTabAction'], $id['scenarioTabReason']]);

        $engine = (string) file_get_contents(FCPATH . 'assets/js/engines/skenario.js');
        $this->assertStringContainsString("t(safe ? 'scenarioSafeTitle' : 'scenarioThinkTitle')", $engine);
    }

    public function testScenarioBackgroundSlotPerRegion(): void
    {
        $slot = App\Libraries\ImageSlots::describe('bg.wonosobo.skenario');
        $this->assertNotNull($slot);
        $this->assertSame(['region', 'wonosobo', '/tantangan/wonosobo/5'], [$slot['group'], $slot['region'], $slot['screens'][0][1]]);
        $this->assertStringContainsString("\$scenarioBackground = 'bg.' . \$level->code . '.skenario'", (string) file_get_contents(APPPATH . 'Views/game/challenge/skenario.php'));
        foreach (['temanggung', 'magelang', 'wonosobo'] as $region) {
            $this->assertFileExists(ROOTPATH . 'ai-studio/game-images/assets/latar/bg.' . $region . '.skenario.webp');
        }
    }

    private function page(string $path): TestResponse
    {
        foreach (['security', 'gameContext', 'sessionService', 'eventService'] as $name) {
            Services::resetSingle($name);
        }

        return $this->withSession(['gelita_csrf' => self::CSRF_HASH, 'participant_id' => 1, 'game_session_id' => 1] + $_SESSION)->call(Method::GET, $path);
    }
}
