<?php

use App\Entities\ChallengeItem;
use App\Entities\ChallengeOption;
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

    public function testChoiceAnswersShowLetterAndLabel(): void
    {
        $item = $this->item('single_choice', ['option_key' => 'mgl-4-01-b'], 'mgl-4-01');
        $item->setLoadedOptions([$this->option('mgl-4-01-a', 'Bali'), $this->option('mgl-4-01-b', 'Candi Mendut', true)]);

        $this->assertSame('B · Candi Mendut', AnswerText::key($item));
        $this->assertSame('A · Bali', AnswerText::response($item, '{"option_key":"mgl-4-01-a"}'));
        $this->assertSame('pilihan C', AnswerText::response($item, ['option_key' => 'mgl-4-01-c']), 'pilihan yang sudah dihapus tetap terbaca');
    }

    public function testClozeAndVerdictAnswers(): void
    {
        $cloze = $this->item('fill_blank_bank', ['text_id' => 'sembilan', 'text_en' => 'nine']);

        $this->assertSame('sembilan', AnswerText::key($cloze), 'kunci rumpang bank kata dibaca dari text_id');
        $this->assertSame('enam', AnswerText::response($cloze, '{"text":"enam"}'));

        $verdict = $this->item('verdict_reason', ['verdict' => 'pendapat']);
        $this->assertSame('Pendapat', AnswerText::key($verdict));
        $this->assertSame('Salah · alasan: candi dilarang dipanjat', AnswerText::response($verdict, ['verdict' => 'salah', 'reason_text' => 'candi dilarang dipanjat']));

        $free = $this->item('fill_blank_free', ['accept_id' => ['Syailendra', 'Sailendra']]);
        $this->assertSame('Syailendra / Sailendra', AnswerText::key($free));
    }

    public function testOrderingShowsCardsInPlace(): void
    {
        $item = $this->item('ordering', ['order' => ['c', 'a', 'b']]);

        $this->assertSame('c, b, a (1 dari 3 kartu tepat)', AnswerText::response($item, ['order' => ['c', 'b', 'a']]));
        $this->assertSame('c, a, b', AnswerText::key($item));
    }

    public function testActivityDetailsBecomeSentences(): void
    {
        $this->assertSame(
            'lewat: isi tantangan diperbarui · percobaan ke: 1',
            AnswerText::fromData('{"via":"content_changed","attempt_no":1}'),
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

    private function option(string $key, string $label, bool $correct = false): ChallengeOption
    {
        $option = new ChallengeOption();
        $option->injectRawData(['option_key' => $key, 'label_id' => $label, 'label_en' => $label, 'is_correct' => $correct ? 1 : 0]);

        return $option;
    }
}
