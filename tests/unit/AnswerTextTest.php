<?php

use App\Entities\ChallengeItem;
use App\Libraries\AnswerText;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Jawaban siswa, kunci, dan isi catatan aktivitas ditulis dalam kalimat
 * biasa untuk guru (tabel panel, berkas Excel). JSON mentah dan kode tidak
 * boleh sampai ke pembaca.
 *
 * @internal
 */
final class AnswerTextTest extends CIUnitTestCase
{
    public function testPuzzleAnswersCountPiecesInPlace(): void
    {
        $item = $this->item('puzzle_arrange', ['order' => range(0, 8)]);

        $this->assertSame('gambar utuh (9 keping tepat)', AnswerText::response($item, '{"order":[0,1,2,3,4,5,6,7,8]}'));
        $this->assertSame('7 dari 9 keping di tempat yang benar', AnswerText::response($item, ['order' => [1, 0, 2, 3, 4, 5, 6, 7, 8]]));
        $this->assertSame('gambar utuh (9 keping)', AnswerText::key($item));

        $jigsaw = $this->item('puzzle_arrange', ['order' => range(0, 15)]);
        $this->assertSame('gambar utuh (16 keping)', AnswerText::key($jigsaw));
    }

    public function testClozeAndVerdictAnswers(): void
    {
        $cloze = $this->item('fill_blank_bank', ['text_id' => 'sembilan', 'text_en' => 'nine']);

        $this->assertSame('sembilan', AnswerText::key($cloze), 'kunci rumpang bank kata dibaca dari text_id');
        $this->assertSame('enam', AnswerText::response($cloze, '{"text":"enam"}'));

        $verdict = $this->item('verdict_card', ['verdict' => 'benar']);
        $this->assertSame('Benar', AnswerText::key($verdict));
        $this->assertSame('Salah', AnswerText::response($verdict, ['verdict' => 'salah']));
    }

    public function testFindObjectAnswers(): void
    {
        $target = $this->item('find_object', ['target' => true]);

        $this->assertSame('benda yang dicari', AnswerText::key($target));
        $this->assertSame('benda yang dicari', AnswerText::response($target, ['item_id' => 7]));
        $this->assertSame('salah pilih benda', AnswerText::response($target, ['item_id' => 9]));
    }

    public function testActivityDetailsBecomeSentences(): void
    {
        $this->assertSame(
            'lewat: isi tantangan diperbarui · pengerjaan ke: 1',
            AnswerText::fromData('{"via":"content_changed","attempt_no":1}'),
        );
        $this->assertSame(
            'cek ke: 2 · percobaan ke: 2 · benar: 3 · dari: 4 · kesempatan habis: tidak',
            AnswerText::fromData(['check_no' => 2, 'try_no' => 2, 'correct_count' => 3, 'total' => 4, 'exhausted' => false]),
        );
        $this->assertSame('lama didengar: 1 menit 5 detik', AnswerText::fromData(['listened_ms' => 65_000]));
        $this->assertSame('ya', AnswerText::fromData(true));
        $this->assertSame('', AnswerText::fromData(['skipped' => null]), 'isian kosong dilewati');
        $this->assertSame('4,2 detik', AnswerText::seconds(4_200));
        $this->assertSame('13 detik', AnswerText::seconds(12_500), '10 detik ke atas dibulatkan');
    }

    private function item(string $type, array $key, string $itemKey = 'tmg-2-11'): ChallengeItem
    {
        $item = new ChallengeItem();
        $item->injectRawData([
            'id'               => 7,
            'item_key'         => $itemKey,
            'interaction_type' => $type,
            'answer_key_json'  => json_encode($key),
            'config_json'      => null,
        ]);

        return $item;
    }
}
