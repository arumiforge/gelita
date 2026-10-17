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
 * pengecoh yang tetap, dan aturan per tingkat (Dok 2 §2: Temanggung 3 rumpang
 * tanpa pengecoh, Magelang 5 + 2 pengecoh, Wonosobo 7 + 2 pengecoh; Bloom
 * C1–C2 / C2–C3 / C3–C4).
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
        // Dok 2 §2: jumlah rumpang & pengecoh tepat, bukan rentang
        $this->assertSame([3, 3], DifficultyRules::reading('mudah')['blanks']);
        $this->assertSame([5, 5], DifficultyRules::reading('sedang')['blanks']);
        $this->assertSame([7, 7], DifficultyRules::reading('sulit')['blanks']);
        $this->assertSame([[1, 1], [2, 2], [2, 2]], array_map(static fn (string $d): array => DifficultyRules::reading($d)['distractors'], ['mudah', 'sedang', 'sulit']));
        $this->assertSame(['C1', 'C2'], DifficultyRules::bloomRange('mudah'));
        $this->assertSame(['C2', 'C3'], DifficultyRules::bloomRange('sedang'));
        $this->assertSame(['C3', 'C4'], DifficultyRules::bloomRange('sulit'));
        $this->assertSame('C2 · Memahami', DifficultyRules::bloomLabel('c2'));
    }

    /** Dok 2 §2: Benar atau Salah dan Cari Objek punya aturan dan rentang Bloom sendiri. */
    public function testTrueFalseAndHuntRulesFollowRegionDifficulty(): void
    {
        $this->assertSame([1, 3], [DifficultyRules::trueFalse('mudah')['paragraphs'], DifficultyRules::trueFalse('mudah')['statements']]);
        $this->assertSame([2, 5], [DifficultyRules::trueFalse('sedang')['paragraphs'], DifficultyRules::trueFalse('sedang')['statements']]);
        $this->assertSame([3, 6], [DifficultyRules::trueFalse('sulit')['paragraphs'], DifficultyRules::trueFalse('sulit')['statements']]);

        $this->assertSame(['C1', 'C2'], DifficultyRules::bloomRange('mudah', 'truefalse'));
        $this->assertSame(['C2', 'C3', 'C4'], DifficultyRules::bloomRange('sedang', 'truefalse'));
        $this->assertSame(['C4', 'C5'], DifficultyRules::bloomRange('sulit', 'truefalse'));
        $this->assertTrue(DifficultyRules::bloomInRange('C5', 'sulit', 'truefalse'));
        $this->assertFalse(DifficultyRules::bloomInRange('C5', 'sulit'), 'Isi Rumpang sulit tetap C3–C4');

        $this->assertSame([4, 4], [DifficultyRules::hunt('mudah')['targets'], DifficultyRules::hunt('mudah')['decoys']]);
        $this->assertSame([4, 6], [DifficultyRules::hunt('sedang')['targets'], DifficultyRules::hunt('sedang')['decoys']]);
        $this->assertSame([4, 8], [DifficultyRules::hunt('sulit')['targets'], DifficultyRules::hunt('sulit')['decoys']]);
        $this->assertSame(['C3', 'C4'], DifficultyRules::bloomRange('sulit', 'hunt'));
        $this->assertSame('C2–C3 (Memahami, Menerapkan)', DifficultyRules::bloomRangeLabel('sedang', 'hunt'));

        // Keterampilan Benar atau Salah: kode per tingkat, semuanya berlabel
        foreach (['mudah', 'sedang', 'sulit'] as $difficulty) {
            foreach (DifficultyRules::trueFalse($difficulty)['skills'] as $skill) {
                $this->assertTrue(DifficultyRules::isSkill($skill), $skill);
            }
        }

        $this->assertSame('Menentukan solusi berdasarkan bukti', DifficultyRules::skillLabel('menentukan_solusi'));
        $this->assertSame('kode_lain', DifficultyRules::skillLabel('kode_lain'));
        $this->assertSame('', DifficultyRules::skillLabel(null));
    }

    public function testReadingNodeUsesEveryActiveItemOfOneVariantInSequence(): void
    {
        $bank = [
            $this->item(31, 'mgl-2-a02', 'kata', 'a', 2),
            $this->item(32, 'mgl-2-a01', 'kata', 'a', 1),
            $this->item(33, 'mgl-2-b01', 'kata', 'b', 1),
            $this->item(34, 'mgl-2-a03', 'kata', 'a', 3),
        ];

        // variasi b sudah pernah didapat siswa → tinggal variasi a
        $picked = model(ChallengeItemModel::class)->pickVariant(7, ['b'], $bank);

        $this->assertSame('a', $picked['group']);
        $this->assertSame([32, 31, 34], array_map(static fn (ChallengeItem $i): int => $i->id, $picked['items']), 'semua soal variasi, urut sequence');

        // semua variasi sudah pernah didapat → semua boleh lagi
        $again = model(ChallengeItemModel::class)->pickVariant(7, ['a', 'b'], $bank);
        $this->assertContains($again['group'], ['a', 'b']);

        $this->assertSame(['group' => '', 'items' => []], model(ChallengeItemModel::class)->pickVariant(7, [], []), 'bank kosong');

        $node = $this->node(['as_reading' => true, 'use_word_bank' => true]);
        $this->assertTrue($node->isReading());
        $this->assertTrue($this->node(['use_word_bank' => true])->isReading(), 'rumpang kelas 5 selalu berupa bacaan (bawaan)');
        $this->assertFalse($this->node(['as_reading' => false])->isReading());
    }

    public function testReadingWordBankHasEveryAnswerAndFixedDistractorsInStableOrder(): void
    {
        $node = $this->node([
            'as_reading'       => true,
            'use_word_bank'    => true,
            'distractor_count' => 2,
            'variants'         => [
                'a' => ['distractors' => [['id' => 'enam', 'en' => 'six'], ['id' => 'arca', 'en' => 'statues'], ['id' => 'lebih', 'en' => 'more']]],
                'b' => ['distractors' => [['id' => 'salju', 'en' => 'snow']]],
            ],
        ]);
        $items = [$this->item(31, 'mgl-2-a01', 'sembilan'), $this->item(32, 'mgl-2-a02', 'stupa'), $this->item(33, 'mgl-2-a03', 'relief')];

        $bank = $this->call('wordBank', $node, $items, 'id', 901, 'a');

        $this->assertEqualsCanonicalizing(['sembilan', 'stupa', 'relief', 'enam', 'arca'], $bank, 'dua pengecoh pertama variasi a, bukan acak');
        $this->assertSame($bank, $this->call('wordBank', $node, $items, 'id', 901, 'a'), 'urutan tetap saat dimuat ulang');
        $this->assertEqualsCanonicalizing(['sembilan', 'stupa', 'relief', 'salju'], $this->call('wordBank', $node, $items, 'id', 901, 'b'), 'pengecoh mengikuti variasinya');
    }

    private function item(int $id, string $key, string $answer = 'kata', string $group = 'a', int $sequence = 1): ChallengeItem
    {
        $item = new ChallengeItem();
        $item->injectRawData([
            'id'               => $id,
            'item_key'         => $key,
            'variant_group'    => $group,
            'sequence'         => $sequence,
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
