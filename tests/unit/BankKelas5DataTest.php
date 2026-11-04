<?php

use App\Libraries\BankRules;
use App\Libraries\BankSource;
use App\Libraries\HuntGeometry;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * The research bank of revision 10 (docs/bank-soal/data/*.php, from 08 with
 * the English draft of 36; 31 §7.3): no rule error, the counts of
 * 08 → Jumlah, Susun Gambar letters remapped (31 §14), the fact-check fixes
 * of 25, the error-code matrices derived by the fixed rules of 08 (K6), and
 * both Cari Objek rounds keep 24 dp between tap areas (K9). The per-rule
 * cases live in BankRulesNewBankTest.
 *
 * @internal
 */
final class BankKelas5DataTest extends CIUnitTestCase
{
    /** Warnings the data may carry: content flags for the experts, never rule errors. */
    private const KNOWN_WARNINGS = [
        'terjemahan belum ada', 'Level AKM', 'belum setara', 'berselang-seling', 'Kode kesalahan yang diharapkan',
    ];

    /** @var array<string, mixed>|null */
    private static ?array $flat = null;

    public function testDataFilesHaveNoRuleErrors(): void
    {
        $problems = BankSource::problems($this->flat());
        $errors   = array_map([BankRules::class, 'format'], array_filter($problems, static fn (array $p): bool => $p['level'] === 'error'));

        $this->assertSame([], $errors);

        foreach (array_filter($problems, static fn (array $p): bool => $p['level'] === 'warning') as $warning) {
            $known = array_filter(self::KNOWN_WARNINGS, static fn (string $needle): bool => str_contains($warning['message'], $needle));
            $this->assertNotSame([], $known, BankRules::format($warning));
        }
    }

    /** 08 → Jumlah: 116 digital literacy items (30 + 32 + 6 + 24 + 24) and 12 PBK clues. */
    public function testCountsMatchDraft08(): void
    {
        $sheets = $this->flat()['sheets'];
        $items  = array_filter($sheets['items'], static fn (array $row): bool => $row['is_active'] === '1');
        $count  = static fn (callable $test): int => count(array_filter($items, $test));

        $this->assertCount(15, $sheets['nodes']);
        $this->assertSame(30, $count(static fn (array $r): bool => $r['interaction_type'] === 'fill_blank_bank'));
        $this->assertSame(32, $count(static fn (array $r): bool => $r['interaction_type'] === 'verdict_card' && $r['variant_group'] !== 'x'));
        $this->assertSame(6, $count(static fn (array $r): bool => $r['interaction_type'] === 'verdict_card' && $r['variant_group'] === 'x'));
        $this->assertSame(24, $count(static fn (array $r): bool => $r['interaction_type'] === 'find_part'));
        $this->assertSame(24, $count(static fn (array $r): bool => $r['interaction_type'] === 'scenario_choice'));
        $this->assertSame(116, $count(static fn (array $r): bool => in_array($r['dimension'], ['D1', 'D2', 'D3'], true)));
        $this->assertSame(12, $count(static fn (array $r): bool => $r['dimension'] === 'PBK'));
        $this->assertSame(18, $count(static fn (array $r): bool => $r['decoy'] === '1'));
        $this->assertSame(31, $count(static fn (array $r): bool => $r['interaction_type'] === 'puzzle_arrange'));
        $this->assertCount(177, $items);

        $this->assertCount(9, $sheets['passages']);
        $this->assertCount(36, $sheets['hints']);
        $this->assertCount(12, $sheets['distractors']);
        $this->assertCount(184, $sheets['cloze_matrix'], 'TMG 4 × 5, MGL 5 × 6, WNB 6 × 7 per variant (31 §6.3)');
        $this->assertCount(108, $sheets['object_codes']);
        $this->assertCount(6, $sheets['post_scenes']);
        $this->assertCount(60, $sheets['post_parts'], '8 / 10 / 12 parts per post');
        $this->assertCount(216, $sheets['part_codes']);
        $this->assertCount(24, $sheets['scenarios']);
        $this->assertCount(184, $sheets['scenario_options']);
        $this->assertSame(['D1a', 'D1b', 'D2', 'D3', 'PBK'], array_column($sheets['indicators'], 'code'));
        $this->assertCount(67, $this->flat()['media']);
    }

    /** Every item of 08 stays needs_verification until expert validation (31 §7.3). */
    public function testNewItemsAreNeedsVerification(): void
    {
        foreach ($this->flat()['sheets']['items'] as $row) {
            if ($row['interaction_type'] !== 'puzzle_arrange') {
                $this->assertSame('needs_verification', $row['review_status'], $row['item_key']);
                $this->assertStringContainsString('Teks Inggris: dari 36, disetujui pengembang', $row['review_note'], $row['item_key']);
                $this->assertStringNotContainsString('terjemahan belum ada', $row['review_note'], $row['item_key']);
            }
        }
    }

    /** 31 §14: letters remapped; a and b are the clean variants, Temanggung has 11 (a–k). */
    public function testSusunGambarLettersAreRemapped(): void
    {
        $media = [];

        foreach ($this->flat()['sheets']['items'] as $row) {
            if ($row['interaction_type'] === 'puzzle_arrange') {
                $media[$row['node_ref']][$row['variant_group']] = $row['media_asset_key'];
            }
        }

        $this->assertSame(range('a', 'k'), array_keys($media['tmg-1']));
        $this->assertSame(range('a', 'j'), array_keys($media['mgl-1']));
        $this->assertSame(range('a', 'j'), array_keys($media['wnb-1']));
        $this->assertSame(['challenge.item.tmg-1-i01', 'challenge.item.tmg-1-k01', 'challenge.item.tmg-1-01'], [$media['tmg-1']['a'], $media['tmg-1']['b'], $media['tmg-1']['c']]);
        $this->assertSame('challenge.item.tmg-1-j01', $media['tmg-1']['k']);
        $this->assertSame(['challenge.item.mgl-1-03', 'challenge.item.mgl-1-e01', 'challenge.item.mgl-1-01', 'challenge.item.mgl-1-d01'], [$media['mgl-1']['a'], $media['mgl-1']['b'], $media['mgl-1']['c'], $media['mgl-1']['e']]);
        $this->assertSame(['challenge.item.wnb-1-d01', 'challenge.item.wnb-1-i01', 'challenge.item.wnb-1-05', 'challenge.item.wnb-1-e01'], [$media['wnb-1']['a'], $media['wnb-1']['b'], $media['wnb-1']['c'], $media['wnb-1']['f']]);
    }

    /** 25 → Akibat ke dokumen lain no. 4 on the Susun Gambar captions. */
    public function testCaptionFixesFromTheFactCheck(): void
    {
        $prompts = array_column($this->flat()['sheets']['items'], 'prompt_id', 'item_key');

        $this->assertStringContainsString('Wayang Kedu adalah wayang kulit asli wilayah Kedu yang berkembang di Temanggung.', $prompts['tmg-1-b01']);
        $this->assertStringNotContainsString('Warisan Budaya Takbenda', $prompts['tmg-1-b01']);
        $this->assertStringContainsString('sekitar 3.150 m', $prompts['tmg-1-c01']);
        $this->assertStringContainsString('ratusan kelompok kuda lumping', $prompts['tmg-1-e01']);
        $this->assertStringContainsString('diperkirakan dibuat sekitar tahun 832 Masehi', $prompts['tmg-1-g01']);
        $this->assertStringContainsString('Desa Dieng Kulon, Banjarnegara', $prompts['wnb-1-a01']);
        $this->assertStringContainsString('sejak zaman Belanda', $prompts['wnb-1-i01']);
        $this->assertStringContainsString('direbus atau dikukus', $prompts['mgl-1-g01']);
        $this->assertStringContainsString('prajurit', implode(' ', array_column(array_filter($this->flat()['sheets']['items'], static fn (array $r): bool => $r['item_key'] === 'mgl-2-b04'), 'answer_id')));
    }

    /** K6: the builder writes the full matrices from the three fixed rules of 08 and `special`. */
    public function testErrorCodeMatricesFollowTheFixedRules(): void
    {
        $cloze = [];

        foreach ($this->flat()['sheets']['cloze_matrix'] as $row) {
            $cloze[$row['item_key']][mb_strtolower($row['word_id'])] = $row['error_code'];
        }

        $this->assertSame('INF-REL', $cloze['tmg-2-a01']['abu'], 'another blank\'s answer');
        $this->assertSame('INF-HUB', $cloze['tmg-2-a02']['pasir'], 'the decoy at its target blank');
        $this->assertSame('INF-LIT', $cloze['tmg-2-a01']['pasir'], 'a decoy at another blank');
        $this->assertSame('INF-HUB', $cloze['wnb-2-a02']['agar'], 'Kode khusus of 08');
        $this->assertSame('INF-HUB', $cloze['wnb-2-a03']['agar']);
        $this->assertSame('INF-LIT', $cloze['wnb-2-a04']['agar']);
        $this->assertCount(5, $cloze['tmg-2-b03']);

        $objects = [];

        foreach ($this->flat()['sheets']['object_codes'] as $row) {
            $objects[$row['item_key']][$row['object_key']] = $row['error_code'];
        }

        $this->assertSame('INF-LIT', $objects['tmg-4-x01']['tmg-4-x02'], 'another clue\'s target');
        $this->assertSame('INF-BUD', $objects['tmg-4-x01']['tmg-4-x05'], 'a decoy');
        $this->assertCount(11, $objects['wnb-4-x01']);

        $parts = [];

        foreach ($this->flat()['sheets']['part_codes'] as $row) {
            $parts[$row['item_key']][$row['part_key']] = [$row['error_code'], $row['main']];
        }

        $this->assertSame(['BUD-ETK', '1'], $parts['tmg-4-a02']['A6'], 'main lure of 08');
        $this->assertSame(['BUD-REP', '0'], $parts['tmg-4-a02']['A8'], 'any other part: the code of the clue type');
        $this->assertArrayNotHasKey('A3', $parts['tmg-4-a02'], 'the key has no code');
        $this->assertCount(11, $parts['wnb-4-b04']);
    }

    /** K9: both rounds keep 24 dp between tap areas; Magelang and Wonosobo were re-laid (31 §9.2). */
    public function testTapAreasKeep24DpInBothRounds(): void
    {
        foreach ($this->flat()['nodes'] as $node) {
            if ($node['engine'] !== 'cari') {
                continue;
            }

            $boxes = array_map(BankRules::box(...), $node['variants']['x']['items']);
            $this->assertGreaterThanOrEqual(HuntGeometry::MIN_GAP_DP, $this->minGap($boxes), $node['ref'] . ' round 1');

            foreach (['a', 'b'] as $group) {
                $parts = array_map(static fn (array $p): array => [$p['x'], $p['y'], $p['w'], $p['h']], $node['posts'][$group]['parts']);
                $this->assertGreaterThanOrEqual(HuntGeometry::MIN_GAP_DP, $this->minGap($parts), "{$node['ref']} round 2 {$group}");
            }
        }
    }

    public function testExplicitItemKeys(): void
    {
        $regions = BankSource::load(ROOTPATH . 'docs/bank-soal/data');
        $regions['magelang']['nodes']['mgl-2']['variants']['a']['items'][0]['key'] = 'mgl-2-b01';

        $problems = array_map([BankRules::class, 'format'], BankSource::problems(BankSource::flatten($regions)));

        $this->assertContains('mgl-2 · variasi a · mgl-2-b01: Kode soal harus diawali mgl-2-a.', $problems);
        $this->assertContains('mgl-2 · mgl-2-b01: Kode soal ini dipakai lebih dari sekali.', $problems);
    }

    public function testRegionOrderAndUniqueKeys(): void
    {
        $this->assertSame([], BankRules::region('temanggung', [
            ['sequence' => 2, 'engine' => 'rumpang'], ['sequence' => 1, 'engine' => 'puzzle'],
            ['sequence' => 4, 'engine' => 'cari'], ['sequence' => 3, 'engine' => 'boleh'],
        ]));

        $wrong = BankRules::region('magelang', [['sequence' => 1, 'engine' => 'puzzle'], ['sequence' => 2, 'engine' => 'pilihan']]);
        $this->assertStringContainsString('seharusnya 5 berurutan', $wrong[0]['message']);

        // Skenario Digital is the fifth challenge since part 6 (32 K1); a bank without it keeps the first four
        $this->assertSame([], BankRules::region('wonosobo', [
            ['sequence' => 1, 'engine' => 'puzzle'], ['sequence' => 2, 'engine' => 'rumpang'], ['sequence' => 3, 'engine' => 'boleh'],
            ['sequence' => 4, 'engine' => 'cari'], ['sequence' => 5, 'engine' => 'skenario'],
        ]));
        $this->assertSame([], array_diff_key(array_flip(['temanggung', 'magelang', 'wonosobo']), $this->flat()['regions']));
        $this->assertSame(BankRules::ENGINES, array_combine(range(1, 5), array_column($this->flat()['regions']['temanggung'], 'engine')));

        $this->assertSame(['tmg-2-a01'], BankRules::duplicateKeys(['tmg-2-a01', 'tmg-2-a02', 'tmg-2-a01']));
        $this->assertSame(3, BankRules::paragraphCount("Satu.\n\nDua.\n\n  \nTiga."));
    }

    // ------------------------------------------------------------- bantu

    /** @return array<string, mixed> */
    private function flat(): array
    {
        return self::$flat ??= BankSource::flatten(BankSource::load(ROOTPATH . 'docs/bank-soal/data'));
    }

    /** @param list<array{0: float, 1: float, 2: float, 3: float}> $boxes */
    private function minGap(array $boxes): float
    {
        $min = PHP_FLOAT_MAX;

        foreach ($boxes as $i => $a) {
            foreach (array_slice($boxes, $i + 1) as $b) {
                $min = min($min, HuntGeometry::gapDp($a, $b));
            }
        }

        return $min;
    }
}
