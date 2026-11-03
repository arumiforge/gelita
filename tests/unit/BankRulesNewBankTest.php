<?php

use App\Libraries\BankRules;
use App\Libraries\BankSource;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Every error and warning of 31 §8.2 on a copy of the real bank: the copy
 * passes, and each rule rejects one change that breaks it.
 *
 * @internal
 */
final class BankRulesNewBankTest extends CIUnitTestCase
{
    /** @var array<string, mixed>|null */
    private static ?array $flat = null;

    public function testRealNodesHaveNoErrors(): void
    {
        foreach ($this->flat()['nodes'] as $ref => $node) {
            $errors = array_filter(BankRules::node($node, $this->flat()['passages']), static fn (array $p): bool => $p['level'] === 'error');
            $this->assertSame([], array_map([BankRules::class, 'format'], $errors), $ref);
        }
    }

    public function testCountsPerVariantAndAnchor(): void
    {
        $node = $this->node('tmg-2');
        array_pop($node['variants']['a']['items']);
        $this->assertProblem($node, 'error', 'tepat 4 rumpang');

        $node = $this->node('mgl-3');
        array_pop($node['variants']['x']['items']);
        $this->assertProblem($node, 'error', 'Kartu jangkar punya 1 pernyataan');

        $node = $this->node('wnb-3');
        unset($node['variants']['x']);
        $this->assertProblem($node, 'error', 'ditambah kartu jangkar x');

        $node = $this->node('tmg-3');
        $node['variants']['a']['items'][0]['sub'] = 'D1b';
        $node['variants']['a']['items'][0]['error_code'] = 'INF-SUM';
        $this->assertProblem($node, 'error', 'sub-aspek 2 D1a + 2 D1b; aturan 3 D1a + 1 D1b');

        $node = $this->node('tmg-1');
        unset($node['variants']['b']);
        $this->assertProblem($node, 'error', 'Variasi b belum ada');
    }

    public function testTrueFalseKeysAreBalanced(): void
    {
        $node = $this->node('mgl-3');
        $node['variants']['b']['items'][1]['answer_id'] = 'benar';
        $this->assertProblem($node, 'error', 'berkunci 4 benar : 2 salah; aturan 3 : 3');

        $node = $this->node('tmg-3');
        $node['variants']['x']['items'][0]['answer_id'] = 'benar';
        $this->assertProblem($node, 'error', 'Kartu jangkar berkunci 2 benar : 0 salah');

        // 08 shows tmg-3-b as S B B S (fixed 3 November 2026); S B S B warns
        $this->assertNoProblem($this->node('tmg-3'), 'berselang-seling penuh');
        $node  = $this->node('tmg-3');
        $items = &$node['variants']['b']['items'];
        [$items[2], $items[3]] = [$items[3], $items[2]];
        unset($items);
        $this->assertProblem($node, 'warning', 'berselang-seling penuh (S B S B)');
    }

    public function testExactlyOneCodePerWrongChoiceOfTheRightKind(): void
    {
        $node = $this->node('tmg-2');
        array_pop($node['codes']['tmg-2-a01']);
        $this->assertProblem($node, 'error', 'Pilihan salah tanpa kode kesalahan');

        $node = $this->node('tmg-2');
        $node['codes']['tmg-2-a01'][] = $node['codes']['tmg-2-a01'][0];
        $this->assertProblem($node, 'error', 'punya lebih dari satu kode kesalahan');

        $node = $this->node('tmg-2');
        $node['codes']['tmg-2-a01'][0]['code'] = 'BUD-ATR';
        $this->assertProblem($node, 'error', 'tidak cocok dengan jenis pilihannya');

        $node = $this->node('tmg-4');
        $node['codes']['tmg-4-x01'][0]['ref'] = 'tmg-4-x01';
        $this->assertProblem($node, 'error', 'bukan pilihan salah soal ini');

        $node = $this->node('mgl-3');
        $node['variants']['a']['items'][3]['error_code'] = 'INF-LIT';
        $this->assertProblem($node, 'error', 'Kode INF-LIT tidak cocok untuk pernyataan D1b');

        $node = $this->node('tmg-4');
        $node['variants']['x']['items'][5]['error_code'] = 'BUD-ATR';
        $this->assertProblem($node, 'error', 'Benda pengecoh wajib punya kode kesalahan INF-BUD atau INF-LIT');
    }

    public function testManualAndNonCodesNeverStandInTheBank(): void
    {
        $node = $this->node('wnb-3');
        $node['variants']['a']['items'][0]['error_code'] = 'INF-LAIN';
        $this->assertProblem($node, 'error', "'INF-LAIN' tidak boleh ada di bank");

        $node = $this->node('mgl-4');
        $node['codes']['mgl-4-a01'][0]['code'] = 'NON-SEN';
        $this->assertProblem($node, 'error', 'tidak boleh ada di bank');
    }

    public function testScenarioScoresAndReasons(): void
    {
        $node = $this->node('tmg-5');
        $node['scenarios']['tmg-5-a01']['actions'][0]['score'] = 2;
        $this->assertProblem($node, 'error', 'tepat satu tindakan skor 2');

        $node = $this->node('tmg-5');
        $node['scenarios']['tmg-5-a01']['actions'][1]['code'] = 'DIG-PSF';
        $this->assertProblem($node, 'error', 'tidak diberi kode kesalahan');

        $node = $this->node('mgl-5');
        $node['scenarios']['mgl-5-a01']['actions'][0]['code'] = '';
        $this->assertProblem($node, 'error', 'wajib punya kode DIG-*');

        $node = $this->node('tmg-5');
        $node['scenarios']['tmg-5-a01']['reasons'][1]['code'] = 'R-RISK';
        $this->assertProblem($node, 'error', 'R-RISK atau R-ETIK, R-ATUR, dan R-SOS atau R-ACAK');

        $node = $this->node('wnb-5');
        $node['scenarios']['wnb-5-a01']['reasons'][4]['code'] = 'R-SOS';
        $this->assertProblem($node, 'error', 'kelima kode R-*');

        $node = $this->node('wnb-5');
        $node['scenarios']['wnb-5-b02']['steps'] = 1;
        $this->assertProblem($node, 'error', 'Skenario 1 langkah; aturan wilayah ini 2');
    }

    public function testRoundTwoHasEveryClueTypeAndKeysInThePost(): void
    {
        $node = $this->node('tmg-4');
        $node['variants']['a']['items'][1]['clue_type'] = 'ATR';
        $this->assertProblem($node, 'error', 'jenis REP ada 0 kali');

        $node = $this->node('mgl-4');
        $node['variants']['b']['items'][0]['answer_id'] = 'Z9';
        $this->assertProblem($node, 'error', "Kunci petunjuk 'Z9' bukan bagian ketuk");

        $node = $this->node('wnb-4');
        $node['codes']['wnb-4-a01'][0]['main'] = false;
        $this->assertProblem($node, 'error', 'pengecoh utama; aturan wilayah ini 3');

        $node = $this->node('wnb-4');
        array_pop($node['posts']['a']['parts']);
        $this->assertProblem($node, 'error', 'aturan wilayah ini 12');
    }

    public function testTapAreasInsideTheSceneAndTwentyFourDpApart(): void
    {
        $node = $this->node('mgl-4');
        $node['variants']['x']['items'][1]['x'] = $node['variants']['x']['items'][0]['x'] + 7;
        $node['variants']['x']['items'][1]['y'] = $node['variants']['x']['items'][0]['y'];
        $this->assertProblem($node, 'error', 'hanya berjarak');

        $node = $this->node('tmg-4');
        $node['variants']['x']['items'][0]['x'] = 95;
        $this->assertProblem($node, 'error', 'keluar dari adegan');

        $node = $this->node('tmg-4');
        $node['posts']['b']['parts'][1]['y'] = $node['posts']['b']['parts'][0]['y'];
        $this->assertProblem($node, 'error', 'Bagian B1 dan B2 bertumpuk');
    }

    public function testExampleDomainsAndMaskedPhones(): void
    {
        $passages = $this->flat()['passages'];
        $passages['wnb-3-a']['body_id'] .= ' Cek di cekfakta.com/telaga sekarang.';
        $this->assertProblem($this->node('wnb-3'), 'error', "Alamat 'cekfakta.com' bukan domain .example", $passages);

        $node = $this->node('mgl-4');
        $node['posts']['a']['parts'][0]['text_id'] = 'Hubungi 0812-3456-7890';
        $this->assertProblem($node, 'error', "Nomor telepon '0812-3456-7890'");

        $this->assertSame([], BankRules::badAddresses('🌐 dieng-wisata.example › Artikel · @pengelola.candi · Kab. Temanggung · 2.300'));
        $this->assertSame([], BankRules::badPhones('Hubungi 08xx-xxxx-xxxx'));
    }

    public function testReferencesAndLabelsAreRequired(): void
    {
        $passages = $this->flat()['passages'];
        $passages['tmg-3-x']['ref'] = '';
        $this->assertProblem($this->node('tmg-3'), 'error', 'Bacaan tmg-3-x belum punya sumber rujukan', $passages);

        $node = $this->node('tmg-4');
        $node['posts']['a']['scene']['reference_source'] = '';
        $this->assertProblem($node, 'error', 'Unggahan babak 2 belum punya sumber rujukan');

        $node = $this->node('tmg-2');
        $node['variants']['a']['items'][0]['dim'] = '';
        $this->assertProblem($node, 'error', 'Dimensi (dimension) wajib diisi');

        $node = $this->node('mgl-2');
        $node['variants']['a']['items'][0]['pirls'] = '';
        $this->assertProblem($node, 'error', 'Proses PIRLS');

        $node = $this->node('wnb-4');
        $node['variants']['a']['items'][0]['akm'] = '';
        $this->assertProblem($node, 'error', 'Level AKM (akm_level)');

        $node = $this->node('tmg-5');
        $node['variants']['a']['items'][0]['bloom'] = '';
        $this->assertProblem($node, 'error', 'Tingkat berpikir (Bloom) wajib');

        $node = $this->node('tmg-4');
        $node['variants']['x']['items'][0]['dim'] = 'D3';
        $this->assertProblem($node, 'error', 'mengukur dimensi PBK');
    }

    public function testRoundsBelongToCariAndXToAnchorsAndRoundOne(): void
    {
        $node = $this->node('tmg-2');
        $node['variants']['a']['items'][0]['round'] = 1;
        $this->assertProblem($node, 'error', 'hanya untuk Cari Objek');

        $node = $this->node('tmg-4');
        $node['variants']['a']['items'][0]['round'] = 1;
        $this->assertProblem($node, 'error', 'berisi babak 2 (find_part)');

        $node = $this->node('tmg-2');
        $node['variants']['x'] = $node['variants']['b'];
        $this->assertProblem($node, 'error', 'Variasi x hanya untuk kartu jangkar');
    }

    public function testWarningsForTheExperts(): void
    {
        $node = $this->node('tmg-2');
        $node['variants']['a']['items'][0]['akm'] = 'L3';
        $this->assertProblem($node, 'warning', 'Level AKM L3 di luar yang diharapkan');

        $node = $this->node('tmg-2');
        $node['variants']['a']['items'][0]['bloom'] = 'C6';
        $this->assertProblem($node, 'warning', 'di luar rentang wilayah ini (C1–C3');

        $passages = $this->flat()['passages'];
        $passages['mgl-3-b']['genre'] = 'artikel_majalah';
        $this->assertProblem($this->node('mgl-3'), 'warning', 'Genre teks variasi a (unggahan_resmi) dan b (artikel_majalah)', $passages);

        $node = $this->node('tmg-3');
        $node['variants']['a']['items'][0]['prompt_en'] = $node['variants']['a']['items'][0]['prompt_id'];
        $this->assertProblem($node, 'warning', 'Teks bahasa Inggris sama dengan teks Indonesia');

        // Variants a and b share one code spread in the data (evened out 3 November 2026)
        foreach (['mgl-2', 'mgl-3', 'wnb-3'] as $ref) {
            $this->assertNoProblem($this->node($ref), 'sebaran kode kesalahan berbeda');
        }

        $node = $this->node('mgl-2');
        $node['codes']['mgl-2-a01'][0]['code'] = 'INF-GEN';
        $this->assertProblem($node, 'warning', 'sebaran kode kesalahan berbeda');
    }

    public function testBankWideCoverageAndCaptionLeaks(): void
    {
        $nodes = $this->flat()['nodes'];
        $nodes['tmg-1']['variants']['a']['items'][0]['prompt_id'] .= ' Di sana dijual kuda kepang bambu.';
        $found = array_map([BankRules::class, 'format'], BankRules::bank($nodes, $this->flat()['passages']));

        $this->assertContains("tmg-1 · variasi a · tmg-1-a01: Keterangan memuat kata kunci butir yang dinilai ('kuda kepang bambu'); periksa kebocoran dengan telaah 08.", $found);
        $this->assertNotContains('wonosobo: Kode kesalahan yang diharapkan belum muncul di wilayah ini: INF-KAT.', $found, '08 has INF-KAT in Wonosobo since 3 November 2026');

        $withoutKat = $nodes;

        foreach (['wnb-2', 'wnb-3', 'wnb-4', 'wnb-5'] as $ref) {
            foreach ($withoutKat[$ref]['codes'] as $key => $codes) {
                foreach ($codes as $i => $code) {
                    if ($code['code'] === 'INF-KAT') {
                        $withoutKat[$ref]['codes'][$key][$i]['code'] = 'INF-LIT';
                    }
                }
            }
        }

        $found = array_map([BankRules::class, 'format'], BankRules::bank($withoutKat, $this->flat()['passages']));
        $this->assertContains('wonosobo: Kode kesalahan yang diharapkan belum muncul di wilayah ini: INF-KAT.', $found);

        foreach (['tmg-2', 'tmg-3', 'tmg-4', 'tmg-5'] as $ref) {
            $nodes[$ref]['codes'] = [];
            $nodes[$ref]['scenarios'] = [];

            foreach ($nodes[$ref]['variants'] as $group => $variant) {
                foreach ($variant['items'] as $i => $item) {
                    $nodes[$ref]['variants'][$group]['items'][$i]['error_code'] = '';
                }
            }
        }

        $temanggung = array_values(array_filter(BankRules::bank($nodes), static fn (array $p): bool => $p['node'] === 'temanggung'));
        $this->assertStringContainsString('BUD-ATR', $temanggung[0]['message']);
    }

    // ------------------------------------------------------------- bantu

    /** @return array<string, mixed> */
    private function flat(): array
    {
        return self::$flat ??= BankSource::flatten(BankSource::load(ROOTPATH . 'docs/bank-soal/data'));
    }

    /** @return array<string, mixed> a copy of one node */
    private function node(string $ref): array
    {
        return $this->flat()['nodes'][$ref];
    }

    private function assertNoProblem(array $node, string $needle): void
    {
        foreach (BankRules::node($node, $this->flat()['passages']) as $problem) {
            $this->assertStringNotContainsString($needle, BankRules::format($problem));
        }
    }

    /** @param array<string, array<string, mixed>>|null $passages */
    private function assertProblem(array $node, string $level, string $needle, ?array $passages = null): void
    {
        $found = array_map(
            [BankRules::class, 'format'],
            array_filter(BankRules::node($node, $passages ?? $this->flat()['passages']), static fn (array $p): bool => $p['level'] === $level),
        );

        foreach ($found as $text) {
            if (str_contains($text, $needle)) {
                $this->addToAssertionCount(1);

                return;
            }
        }

        $this->fail("No {$level} with '{$needle}'. Found: " . implode(' | ', $found));
    }
}
