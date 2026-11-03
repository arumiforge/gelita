<?php

use App\Libraries\BankSource;
use App\Libraries\HuntGeometry;
use App\Libraries\SurfaceLoad;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Surface-load variables (31 §13.2): the word rule of 11 and the counts per
 * item type, on the real bank.
 *
 * @internal
 */
final class SurfaceLoadTest extends CIUnitTestCase
{
    public function testWordsFollowTheRuleOfTheDataDictionary(): void
    {
        $this->assertSame(0, SurfaceLoad::words(''));
        $this->assertSame(2, SurfaceLoad::words('Keren banget 😍 👏 !'), 'emoji and lone marks do not count');
        $this->assertSame(4, SurfaceLoad::words('>> @jalan2_bima ✓ akun · 3.150 meter'), 'account names and numbers are words; markers are not');
        $this->assertSame(2, SurfaceLoad::words("Satu\n\n:: dua"));
    }

    public function testCountsPerItemType(): void
    {
        $flat = BankSource::flatten(BankSource::load(ROOTPATH . 'docs/bank-soal/data'));
        $load = static fn (string $ref): array => SurfaceLoad::forNode($flat['nodes'][$ref], $flat['passages'], ['tmg-1-a01' => 3]);

        $cloze = $load('tmg-2')['tmg-2-a01'];
        $this->assertSame([4, 2], [$cloze['gap_count'], $cloze['distractor_word_count']]);
        $this->assertGreaterThan(70, $cloze['passage_words']);

        $this->assertGreaterThan(20, $load('tmg-3')['tmg-3-x01']['passage_words']);
        $this->assertSame(['puzzle_grid' => 3], $load('tmg-1')['tmg-1-a01']);

        $hunt = $load('wnb-4');
        $this->assertSame([8, 12], [$hunt['wnb-4-x01']['decoy_count'], $hunt['wnb-4-x01']['option_count']]);
        $this->assertGreaterThanOrEqual(HuntGeometry::MIN_GAP_DP, $hunt['wnb-4-x01']['tap_min_dp']);
        $this->assertSame([11, 12], [$hunt['wnb-4-a01']['decoy_count'], $hunt['wnb-4-a01']['option_count']]);
        $this->assertIsInt($hunt['wnb-4-a01']['tap_min_dp']);
        $this->assertArrayNotHasKey('wnb-4-x05', array_filter($hunt), 'decoys carry no load');

        $scenario = $load('wnb-5')['wnb-5-a01'];
        $this->assertSame([4, 5], [$scenario['option_count'], $scenario['reason_option_count']]);
    }
}
