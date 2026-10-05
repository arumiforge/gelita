<?php

use App\Entities\ChallengeAttempt;
use App\Entities\ChallengeItem;
use App\Entities\ChallengeNode;
use App\Libraries\ClozeReading;
use App\Libraries\DifficultyRules;
use App\Models\ChallengeItemModel;
use App\Services\ChallengeService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Isi Rumpang kelas 5 sebagai satu bacaan: potongan soal disambung menjadi
 * paragraf, semua soal tampil berurutan, bank kata berisi semua jawaban +
 * pengecoh yang tetap, dan aturan per tingkat (Temanggung 3 rumpang tanpa
 * pengecoh, Magelang 4–5, Wonosobo 6–7; Bloom C1–C2 / C2–C3 / C3–C4).
 *
 * @internal
 */
final class ClozeReadingTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper(['gelita', 'content']);
    }

    public function testChunksJoinIntoParagraphsWithInlineBlanks(): void
    {
        $paragraphs = ClozeReading::paragraphs([
            ['id' => 11, 'prompt' => 'Temanggung diapit Gunung Sindoro dan Gunung ___.'],
            ['id' => 12, 'prompt' => 'Keduanya disebut gunung ____.' . "\n\n" . 'Tanah di lerengnya subur.'],
            ['id' => 13, 'prompt' => 'Petani menanam ___ dan sayuran.'],
        ]);

        $this->assertCount(2, $paragraphs, 'baris kosong di tengah potongan membuka paragraf baru');
        $this->assertSame([
            ['type' => 'text', 'text' => 'Temanggung diapit Gunung Sindoro dan Gunung '],
            ['type' => 'blank', 'id' => 11, 'index' => 1],
            ['type' => 'text', 'text' => '.'],
            ['type' => 'text', 'text' => ' Keduanya disebut gunung '],
            ['type' => 'blank', 'id' => 12, 'index' => 2],
            ['type' => 'text', 'text' => '.'],
        ], $paragraphs[0]);
        $this->assertSame([
            ['type' => 'text', 'text' => 'Tanah di lerengnya subur.'],
            ['type' => 'text', 'text' => ' Petani menanam '],
            ['type' => 'blank', 'id' => 13, 'index' => 3],
            ['type' => 'text', 'text' => ' dan sayuran.'],
        ], $paragraphs[1]);
    }

    public function testRulesFollowRegionDifficulty(): void
    {
        $this->assertSame([3, 3], DifficultyRules::reading('mudah')['blanks']);
        $this->assertSame([4, 5], DifficultyRules::reading('sedang')['blanks']);
        $this->assertSame([6, 7], DifficultyRules::reading('sulit')['blanks']);
        $this->assertSame(['C1', 'C2'], DifficultyRules::bloomRange('mudah'));
        $this->assertSame(['C2', 'C3'], DifficultyRules::bloomRange('sedang'));
        $this->assertSame(['C3', 'C4'], DifficultyRules::bloomRange('sulit'));
        $this->assertSame('C2 · Memahami', DifficultyRules::bloomLabel('c2'));
    }

    public function testCleanReadingHasNoProblems(): void
    {
        $result = ClozeReading::problems($this->blanks(3, ['C1', 'C2', 'C2']), [], 0, true, 'mudah');

        $this->assertSame(['errors' => [], 'warnings' => []], $result);
    }

    public function testBrokenReadingsAreErrors(): void
    {
        $blanks                 = $this->blanks(3, ['C1', 'C2', 'C2']);
        $blanks[1]['prompt_id'] = 'Tanpa tempat kosong.';
        $blanks[2]['answer_id'] = 'kata1';   // sama dengan jawaban soal pertama

        $result = ClozeReading::problems($blanks, [['id' => 'kata2', 'en' => 'x']], 2, true, 'mudah');

        $this->assertCount(4, $result['errors']);
        $this->assertStringContainsString('tepat satu ___', $result['errors'][0]);
        $this->assertStringContainsString('ambigu', $result['errors'][1]);
        $this->assertStringContainsString("pengecoh 'kata2'", $result['errors'][2]);
        $this->assertStringContainsString('kurang dari', $result['errors'][3]);
    }

    public function testDeviationsFromTheGradeFiveRulesAreWarnings(): void
    {
        $result = ClozeReading::problems($this->blanks(3, ['C1', null, 'C4']), [['id' => 'lain', 'en' => 'other']], 1, false, 'sulit');

        $this->assertSame([], $result['errors']);
        // Bloom kosong, C1 di luar C3–C4, tanpa bank kata, 3 rumpang; 1 pengecoh masih dalam aturan
        $this->assertCount(4, $result['warnings']);
        $text = implode(' | ', $result['warnings']);
        $this->assertStringContainsString('belum diisi', $text);
        $this->assertStringContainsString('C3–C4', $text);
        $this->assertStringContainsString('bank kata', $text);
        $this->assertStringContainsString('6–7 rumpang', $text);
    }

    public function testReadingNodeUsesEveryActiveItemInBankOrder(): void
    {
        $bank = [$this->item(31, 'mgl-2-11'), $this->item(32, 'mgl-2-12'), $this->item(33, 'mgl-2-13')];

        $picked = model(ChallengeItemModel::class)->pickForAttempt(7, 1, 'all', 'seed', $bank);

        $this->assertSame([31, 32, 33], array_map(static fn (ChallengeItem $i): int => $i->id, $picked), 'semua soal, urutan bank, abaikan items_per_round');

        $node = $this->node(['as_reading' => true, 'use_word_bank' => true]);
        $this->assertTrue($node->isReading());
        $this->assertFalse($this->node(['use_word_bank' => true])->isReading());
    }

    public function testReadingWordBankHasEveryAnswerAndFixedDistractorsInStableOrder(): void
    {
        $node = $this->node([
            'as_reading'       => true,
            'use_word_bank'    => true,
            'distractor_count' => 2,
            'distractors'      => [['id' => 'enam', 'en' => 'six'], ['id' => 'arca', 'en' => 'statues'], ['id' => 'lebih', 'en' => 'more']],
        ]);
        $items = [$this->item(31, 'mgl-2-11', 'sembilan'), $this->item(32, 'mgl-2-12', 'stupa'), $this->item(33, 'mgl-2-13', 'relief')];

        $bank = $this->call('wordBank', $node, $items, 'id', 901);

        $this->assertEqualsCanonicalizing(['sembilan', 'stupa', 'relief', 'enam', 'arca'], $bank, 'dua pengecoh pertama, bukan acak');
        $this->assertSame($bank, $this->call('wordBank', $node, $items, 'id', 901), 'urutan tetap saat dimuat ulang');
    }

    /** @return list<array<string, mixed>> */
    private function blanks(int $count, array $blooms): array
    {
        $out = [];

        for ($i = 1; $i <= $count; $i++) {
            $out[] = [
                'key'       => 'tmg-2-1' . $i,
                'prompt_id' => "Kalimat {$i} dengan ___.",
                'prompt_en' => "Sentence {$i} with ___.",
                'answer_id' => 'kata' . $i,
                'answer_en' => 'word' . $i,
                'bloom'     => $blooms[$i - 1] ?? null,
            ];
        }

        return $out;
    }

    private function item(int $id, string $key, string $answer = 'kata'): ChallengeItem
    {
        $item = new ChallengeItem();
        $item->injectRawData([
            'id'               => $id,
            'item_key'         => $key,
            'interaction_type' => 'fill_blank_bank',
            'prompt_id'        => 'Potongan ___.',
            'answer_key_json'  => json_encode(['text_id' => $answer, 'text_en' => $answer]),
            'config_json'      => null,
            'scorable'         => 1,
            'is_active'        => 1,
        ]);

        return $item;
    }

    private function node(array $config): ChallengeNode
    {
        $node = new ChallengeNode();
        $node->injectRawData([
            'id'          => 7,
            'level_id'    => 2,
            'sequence'    => 2,
            'engine_type' => 'rumpang',
            'config_json' => json_encode($config),
        ]);

        return $node;
    }

    private function call(string $method, mixed ...$args): mixed
    {
        return (new ReflectionMethod(ChallengeService::class, $method))->invoke(new ChallengeService(), ...$args);
    }
}
