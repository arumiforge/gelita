<?php

use App\Services\ChallengeService;
use CodeIgniter\Config\Factories;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Encryption;
use Tests\Support\Database\BankPlay;

/**
 * Every scored wrong try gets its error code from the bank, derived by the
 * server and written into the same item_tries INSERT (31 K5, §10.2); a
 * correct try keeps error_code null. Isi Rumpang, Benar atau Salah with its
 * anchor card, Cari Objek round 1 and round 2.
 *
 * @internal
 */
final class ErrorCodeOnTryTest extends CIUnitTestCase
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

    public function testClozeTryCarriesTheCodeOfTheWordPlaced(): void
    {
        $attempt = $this->open('tmg-2');
        $items   = $this->itemsOfType($attempt, 'fill_blank_bank');
        $group   = $attempt->variant_group;
        $answers = [];

        foreach ($items as $item) {
            $answers[$item->id] = ['text' => (string) $item->answerKey('text_id')];
        }

        // Swap the first two answers: another blank's answer → INF-REL (08 rule 1)
        [$first, $second] = $items;
        $answers[$first->id]  = ['text' => (string) $second->answerKey('text_id')];
        $answers[$second->id] = ['text' => (string) $first->answerKey('text_id')];

        $this->service->submitCheck($attempt, $answers);
        $tries = array_column($this->triesOf($attempt), 'error_code', 'challenge_item_id');

        $this->assertSame('INF-REL', $tries[$first->id], "variant {$group}");
        $this->assertSame('INF-REL', $tries[$second->id]);
        $this->assertNull($tries[$items[2]->id], 'a correct try has no code');
    }

    public function testDecoyAtItsTargetBlankGetsTheTrapCode(): void
    {
        $attempt = $this->open('tmg-2');
        $trap    = $attempt->variant_group === 'a' ? ['tmg-2-a02', 'pasir'] : ['tmg-2-b01', 'hujan'];
        $answers = [];

        foreach ($this->itemsOfType($attempt, 'fill_blank_bank') as $item) {
            $answers[$item->id] = ['text' => $item->item_key === $trap[0] ? $trap[1] : (string) $item->answerKey('text_id')];
        }

        $this->service->submitCheck($attempt, $answers);

        $this->assertSame([$this->item($trap[0])->id => 'INF-HUB'], array_filter(array_column($this->triesOf($attempt), 'error_code', 'challenge_item_id')));
    }

    public function testVerdictTriesCarryTheStatementCodeOnBothCards(): void
    {
        $attempt = $this->open('mgl-3');
        $answers = [];
        $codes   = [];

        foreach ($this->itemsOfType($attempt, 'verdict_card') as $item) {
            $answers[$item->id] = ['verdict' => $item->verdict() === 'benar' ? 'salah' : 'benar'];
            $codes[$item->id]   = $item->error_code;
        }

        $this->service->submitCheck($attempt, $answers);

        $this->assertCount(8, $codes, 'six statements and the two anchor statements');
        $this->assertSame($codes, array_column($this->triesOf($attempt), 'error_code', 'challenge_item_id'));
        $this->assertContains('INF-SUM', $codes, 'mgl-3-x02 on the anchor card');
    }

    public function testHuntTapsCarryTheCodeOfWhatWasTapped(): void
    {
        $attempt = $this->open('tmg-4');
        [$clue, $other] = $this->itemsOfType($attempt, 'find_object');
        $decoy   = $this->itemsOfType($attempt, 'find_object', true)[0];

        $this->tapObject($attempt, $clue, $decoy);
        $this->tapObject($attempt, $clue, $other);
        $this->tapObject($attempt, $clue, $clue);

        $this->assertSame(['INF-BUD', 'INF-LIT', null], array_column($this->triesOf($attempt), 'error_code'));

        // Round 2: the main lure of 08 and any other part (the code of the clue type)
        foreach ($this->itemsOfType($attempt, 'find_object') as $item) {
            if ($item->id !== $clue->id) {
                $this->tapObject($attempt, $item, $item);
            }
        }

        $clues = [];

        foreach ($this->itemsOfType($attempt, 'find_part') as $item) {
            $clues[$item->d3_clue_type] = $item;
        }

        $letter = strtoupper((string) $attempt->variant_group) === 'A' ? 'A' : 'B';
        $rep    = $clues['REP'];
        $this->tapPart($attempt, $rep, $letter . '6');   // main lure → BUD-ETK
        $this->tapPart($attempt, $rep, $letter . '8');   // a button → the clue type's code
        $this->tapPart($attempt, $rep, (string) $rep->answerKey('part'));

        $round2 = array_slice($this->triesOf($attempt), -3);
        $this->assertSame(['BUD-ETK', 'BUD-REP', null], array_column($round2, 'error_code'));
        $this->assertSame(['part' => $letter . '6'], json_decode((string) $round2[0]['answer_json'], true));
    }

    public function testEnglishWordsAreMappedToTheirIndonesianPair(): void
    {
        $this->conn->table('game_sessions')->where('id', 1)->update(['locale' => 'en']);
        $attempt = $this->open('tmg-2');
        $trap    = $attempt->variant_group === 'a' ? ['tmg-2-a02', 'sand'] : ['tmg-2-b01', 'rainy'];
        $answers = [];

        foreach ($this->itemsOfType($attempt, 'fill_blank_bank') as $item) {
            $answers[$item->id] = ['text' => $item->item_key === $trap[0] ? $trap[1] : (string) $item->answerKey('text_en')];
        }

        $this->service->submitCheck($attempt, $answers);

        $this->assertSame([$this->item($trap[0])->id => 'INF-HUB'], array_filter(array_column($this->triesOf($attempt), 'error_code', 'challenge_item_id')));
    }
}
