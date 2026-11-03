<?php

use App\Services\ChallengeService;
use CodeIgniter\Config\Factories;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Encryption;
use Tests\Support\Database\BankPlay;

/**
 * The anchor card (`x`, 31 K11, §12.2) joins every Benar atau Salah attempt
 * after the variant's statements; one Cek checks both cards, and the three
 * tries stay per challenge.
 *
 * @internal
 */
final class AnchorItemsTest extends CIUnitTestCase
{
    use BankPlay;

    private string $originalKey;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['gelita', 'content']);

        $this->originalKey           = (string) config(Encryption::class)->key;
        config(Encryption::class)->key = 'kunci-uji-bank-31';
        $this->conn                  = $this->createGradeFiveWorld();
        $this->service               = new ChallengeService();
    }

    protected function tearDown(): void
    {
        config(Encryption::class)->key = $this->originalKey;
        Factories::reset('libraries');
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testAnchorCardJoinsEveryAttemptAfterTheVariant(): void
    {
        foreach (['tmg-3', 'mgl-3', 'wnb-3'] as $ref) {
            $attempt = $this->open($ref);
            $groups  = array_map(static fn ($i): string => (string) $i->variant_group, $this->attemptItems($attempt));
            $payload = $this->service->openNode($this->session(), $this->nodeId($this->conn, $ref))['payload'];

            $this->assertSame([...array_fill(0, count($groups) - 2, $attempt->variant_group), 'x', 'x'], $groups, $ref);
            $this->assertCount(2, $payload['passages'], "{$ref}: the variant's text and the anchor card");
            $this->assertContains($ref . '-x', array_column($payload['passages'], 'key'));
            $this->assertNotSame('x', $attempt->variant_group, 'x is never a variant');
        }
    }

    public function testOneCheckGradesBothCardsAndTriesStayPerChallenge(): void
    {
        $attempt = $this->open('tmg-3');
        $items   = $this->itemsOfType($attempt, 'verdict_card');
        $wrong   = [];

        foreach ($items as $item) {
            $wrong[$item->id] = ['verdict' => $item->verdict() === 'benar' ? 'salah' : 'benar'];
        }

        foreach ([1, 2, 3] as $check) {
            $result = $this->service->submitCheck($this->reload($attempt), $wrong);
            $this->assertSame([6, $check, 3 - $check], [$result['total'], $result['check_count'], $result['tries_left']]);
        }

        $this->assertTrue($result['completed']);
        $this->assertSame('tries_exhausted', $result['completion_reason']);
        $this->assertCount(18, $this->triesOf($attempt), 'six statements × three checks');
    }

    public function testRenderedPageStacksTheAnchorCardUnderItsHeading(): void
    {
        $nodeId  = $this->nodeId($this->conn, 'wnb-3');
        $opened  = $this->service->openNode($this->session(), $nodeId);
        $node    = service('contentRepository')->node($nodeId);
        $level   = service('contentRepository')->levelById($node->level_id);
        $html    = view('game/challenge/boleh', ['level' => $level, 'node' => $node, 'sequence' => 3, 'attempt' => $opened['attempt'], 'payload' => $opened['payload'], 'locale' => 'id', 'hud' => []], ['saveData' => false]);

        $this->assertSame(1, substr_count($html, 'class="cards-subheading"'));
        $this->assertStringContainsString(lang('Game.anchorCardTitle'), $html);
        $this->assertStringContainsString('class="passage-body dt is-post"', $html);
        $this->assertStringContainsString('<p class="dt-head">@carica.mantap · Bersponsor</p>', $html);
        $this->assertSame(1, substr_count($html, 'id="btn-check"'), 'one Cek for both cards');
    }
}
