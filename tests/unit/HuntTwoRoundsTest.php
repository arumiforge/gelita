<?php

use App\Services\ChallengeService;
use CodeIgniter\Config\Factories;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Encryption;
use Tests\Support\Database\BankPlay;

/**
 * Cari Objek in two rounds (31 K7, §12.1): round 2 opens only once every
 * round-1 clue is closed, tries stay per clue, and the challenge closes
 * after both rounds.
 *
 * @internal
 */
final class HuntTwoRoundsTest extends CIUnitTestCase
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

    public function testRoundTwoOpensAfterRoundOne(): void
    {
        $attempt = $this->open('mgl-4');
        $clue    = $this->itemsOfType($attempt, 'find_part')[0];

        try {
            $this->tapPart($attempt, $clue, (string) $clue->answerKey('part'));
            $this->fail('Round 2 should wait for round 1.');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('Babak 2 belum terbuka', $e->getMessage());
        }

        $this->assertSame([], $this->triesOf($attempt));
        $this->solveRoundOne($attempt);
        $this->assertTrue($this->tapPart($attempt, $clue, (string) $clue->answerKey('part'))['correct']);
    }

    public function testTriesArePerClueAndTheChallengeClosesAfterBothRounds(): void
    {
        $attempt = $this->open('tmg-4');
        $this->solveRoundOne($attempt);
        $this->assertSame('in_progress', $this->reload($attempt)->status, 'round 2 is still open');

        $clues  = $this->itemsOfType($attempt, 'find_part');
        $first  = $clues[0];
        $wrong  = $this->wrongPart($attempt, $first);

        foreach ([1, 2, 3] as $n) {
            $result = $this->tapPart($attempt, $first, $wrong);
            $this->assertSame([$n, 3 - $n, $n === 3], [$result['try_no'], $result['tries_left'], $result['clue_closed']]);
        }

        foreach (array_slice($clues, 1) as $n => $clue) {
            $result = $this->tapPart($attempt, $clue, (string) $clue->answerKey('part'));
            $this->assertSame($n === 2, $result['completed']);
        }

        $row = $this->reload($attempt);
        $this->assertSame(['completed', 'tries_exhausted', 8, 7, 7], [$row->status, $row->completion_reason, (int) $row->scorable_items, (int) $row->final_correct, (int) $row->first_pass_correct]);
    }

    public function testUnknownPartRefIsRefused(): void
    {
        $attempt = $this->open('wnb-4');
        $this->solveRoundOne($attempt);

        $this->expectException(\InvalidArgumentException::class);
        $this->service->submitAnswer($this->reload($attempt), $this->itemsOfType($attempt, 'find_part')[0]->id, ['object' => str_repeat('0', 20)]);
    }

    private function wrongPart($attempt, $clue): string
    {
        foreach (service('contentRepository')->postScene($attempt->challenge_node_id, (string) $attempt->variant_group)['parts'] as $part) {
            if ($part['part_key'] !== $clue->answerKey('part')) {
                return (string) $part['part_key'];
            }
        }

        $this->fail('No wrong part.');
    }
}
