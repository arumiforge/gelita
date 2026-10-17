<?php

use App\Libraries\BankRules;
use App\Libraries\BankSource;
use App\Libraries\DifficultyRules;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * The grade-5 bank (docs/bank-soal/data/*.php, revision of October 2026 =
 * draft 2) passes every §7.5 check without errors or warnings, is complete
 * (151 item rows: 147 active + 4 retired; 51 variants, 9 passages, 36 hints,
 * 15 distractors, 66 image slots), and every §7.5 rule really rejects a bank
 * that breaks it.
 *
 * @internal
 */
final class BankKelas5DataTest extends CIUnitTestCase
{
    /** Dok 2 §10.4: 45 challenge image slots, plus 21 for Susun Gambar variants d–j (draft 2). */
    private const MEDIA_SLOTS = [
        'challenge.item.tmg-1-01', 'challenge.item.tmg-1-03', 'challenge.item.tmg-1-04',
        'challenge.item.tmg-3-a03', 'challenge.item.tmg-3-b03', 'challenge.item.tmg-3-c03',
        'challenge.tmg-4.scene',
        'challenge.item.tmg-4-03', 'challenge.item.tmg-4-02', 'challenge.item.tmg-4-06', 'challenge.item.tmg-4-04',
        'challenge.item.tmg-4-09', 'challenge.item.tmg-4-10', 'challenge.item.tmg-4-11', 'challenge.item.tmg-4-12',
        'challenge.item.mgl-1-01', 'challenge.item.mgl-1-02', 'challenge.item.mgl-1-03',
        'challenge.mgl-4.scene',
        'challenge.item.mgl-4-a01', 'challenge.item.mgl-4-a02', 'challenge.item.mgl-4-a03', 'challenge.item.mgl-4-a04',
        'challenge.item.mgl-4-a05', 'challenge.item.mgl-4-a06', 'challenge.item.mgl-4-a07', 'challenge.item.mgl-4-a08',
        'challenge.item.mgl-4-a09', 'challenge.item.mgl-4-a10',
        'challenge.item.wnb-1-05', 'challenge.item.wnb-1-06', 'challenge.item.wnb-1-07',
        'challenge.wnb-4.scene',
        'challenge.item.wnb-4-a01', 'challenge.item.wnb-4-a02', 'challenge.item.wnb-4-a03', 'challenge.item.wnb-4-a04',
        'challenge.item.wnb-4-a05', 'challenge.item.wnb-4-a06', 'challenge.item.wnb-4-a07', 'challenge.item.wnb-4-a08',
        'challenge.item.wnb-4-a09', 'challenge.item.wnb-4-a10', 'challenge.item.wnb-4-a11', 'challenge.item.wnb-4-a12',
        'challenge.item.tmg-1-d01', 'challenge.item.tmg-1-e01', 'challenge.item.tmg-1-f01', 'challenge.item.tmg-1-g01',
        'challenge.item.tmg-1-h01', 'challenge.item.tmg-1-i01', 'challenge.item.tmg-1-j01',
        'challenge.item.mgl-1-d01', 'challenge.item.mgl-1-e01', 'challenge.item.mgl-1-f01', 'challenge.item.mgl-1-g01',
        'challenge.item.mgl-1-h01', 'challenge.item.mgl-1-i01', 'challenge.item.mgl-1-j01',
        'challenge.item.wnb-1-d01', 'challenge.item.wnb-1-e01', 'challenge.item.wnb-1-f01', 'challenge.item.wnb-1-g01',
        'challenge.item.wnb-1-h01', 'challenge.item.wnb-1-i01', 'challenge.item.wnb-1-j01',
    ];

    /** Retired items (is_active 0): kept so stored answers keep their item. */
    private const RETIRED = ['mgl-2-a04', 'mgl-2-c04', 'wnb-2-c05', 'wnb-2-c07'];

    /** @var array<string, mixed>|null */
    private static ?array $flat = null;

    public function testDataFilesPassEveryCheckWithoutWarnings(): void
    {
        $problems = array_map([BankRules::class, 'format'], BankSource::problems($this->flat()));

        $this->assertSame([], $problems);
    }

    public function testBankIsCompleteAsAgreed(): void
    {
        $sheets = $this->flat()['sheets'];
        $items  = array_filter($sheets['items'], static fn (array $row): bool => $row['is_active'] === '1');

        $this->assertCount(12, $sheets['nodes']);
        $this->assertCount(151, $sheets['items']);
        $this->assertCount(147, $items);
        $this->assertSame(self::RETIRED, array_values(array_column(array_filter($sheets['items'], static fn (array $row): bool => $row['is_active'] === '0'), 'item_key')));
        $this->assertCount(129, array_filter($items, static fn (array $row): bool => $row['scorable'] === '1'));
        $this->assertCount(18, array_filter($items, static fn (array $row): bool => $row['decoy'] === '1'));
        $this->assertCount(51, $sheets['variants']);
        $this->assertCount(9, $sheets['passages']);
        $this->assertCount(36, $sheets['hints']);
        $this->assertCount(15, $sheets['distractors']);
        $this->assertCount(66, $this->flat()['media']);

        // Ringkasan §8: rumpang 9 bacaan (3 per wilayah), B/S 9 bacaan
        $this->assertCount(9, array_unique(array_map(
            static fn (array $row): string => $row['node_ref'] . $row['variant_group'],
            array_filter($items, static fn (array $row): bool => $row['interaction_type'] === 'fill_blank_bank'),
        )));
    }

    public function testRegionsFollowTheChallengeOrderAndLevelSeederDifficulty(): void
    {
        $seeder   = (string) file_get_contents(APPPATH . 'Database/Seeds/LevelSeeder.php');
        $expected = [
            'temanggung' => ['mudah', ['tmg-1' => array_fill(0, 10, 1), 'tmg-2' => [3, 3, 3], 'tmg-3' => [3, 3, 3], 'tmg-4' => [8]]],
            'magelang'   => ['sedang', ['mgl-1' => array_fill(0, 10, 1), 'mgl-2' => [5, 5, 5], 'mgl-3' => [5, 5, 5], 'mgl-4' => [10]]],
            'wonosobo'   => ['sulit', ['wnb-1' => array_fill(0, 10, 1), 'wnb-2' => [7, 7, 7], 'wnb-3' => [6, 6, 6], 'wnb-4' => [12]]],
        ];

        foreach (BankSource::load(ROOTPATH . 'docs/bank-soal/data') as $region => $data) {
            [$difficulty, $nodes] = $expected[$region];

            $this->assertSame($difficulty, $data['difficulty'], $region);
            $this->assertMatchesRegularExpression("/'code'\\s*=>\\s*'{$region}'.*?'difficulty'\\s*=>\\s*'{$difficulty}'/s", $seeder, $region);
            $this->assertSame(array_keys($nodes), array_keys($data['nodes']), "{$region}: urutan tantangan");
            $this->assertSame(['puzzle', 'rumpang', 'boleh', 'cari'], array_values(array_column($data['nodes'], 'engine')), $region);

            foreach ($nodes as $ref => $counts) {
                $active = static fn (array $v): int => count(array_filter($v['items'], static fn (array $item): bool => ($item['active'] ?? true) !== false));

                $this->assertSame($counts, array_values(array_map($active, $data['nodes'][$ref]['variants'])), "{$ref}: active items per variant");
            }
        }
    }

    public function testCodesSequencesAndReviewStatusFollowTheRules(): void
    {
        $perNode = [];

        $retired = [];

        foreach ($this->flat()['sheets']['items'] as $row) {
            $this->assertMatchesRegularExpression('/^' . preg_quote($row['node_ref'], '/') . '-' . $row['variant_group'] . '\d{2}$/', $row['item_key']);

            if ($row['is_active'] === '1') {
                $perNode[$row['node_ref']][] = (int) $row['sequence'];
            } else {
                $retired[$row['node_ref']][] = (int) $row['sequence'];
            }

            // Revised items wait for the researcher's check; the rest stay as approved.
            $this->assertContains($row['review_status'], ['verified', 'needs_verification'], $row['item_key']);

            if ($row['review_status'] === 'verified' && ! str_starts_with($row['review_note'], 'Revisi')) {
                $this->assertSame(BankSource::REVIEW_NOTE, $row['review_note'], $row['item_key']);
            } else {
                $this->assertNotSame('', $row['review_note'], "{$row['item_key']}: reason for the revision");
            }

            $this->assertNotSame('', $row['reference_source'], "{$row['item_key']}: rujukan §9.4");

            if ($row['interaction_type'] === 'verdict_card') {
                $this->assertSame(substr($row['item_key'], 0, -2), $row['passage_key'], 'kode bacaan {node}-{variasi}');
            }
        }

        foreach ($perNode as $ref => $sequence) {
            $this->assertSame(range(1, count($sequence)), $sequence, "{$ref}: sequence = urutan global di tantangan");
        }

        $this->assertSame(['mgl-2' => [91, 92], 'wnb-2' => [91, 92]], $retired, 'retired items sort after every active item');

        // Explicit keys: inserted items keep the numbering of the items around them
        $sequence = array_column($this->flat()['sheets']['items'], 'sequence', 'item_key');
        $this->assertSame(['3', '4', '91'], [$sequence['mgl-2-a06'], $sequence['mgl-2-a03'], $sequence['mgl-2-a04']]);
        $this->assertSame(['13', '19', '21'], [$sequence['wnb-2-b02'], $sequence['wnb-2-c08'], $sequence['wnb-2-c09']]);

        $this->assertSame(['tmg-2-a01', 'tmg-3-b03', 'tmg-4-a08', 'mgl-4-a10', 'wnb-4-a12'], array_values(array_intersect(
            ['tmg-2-a01', 'tmg-3-b03', 'tmg-4-a08', 'mgl-4-a10', 'wnb-4-a12'],
            array_column($this->flat()['sheets']['items'], 'item_key'),
        )));
    }

    public function testMediaSlotsMatchTheAgreedList(): void
    {
        $media = $this->flat()['media'];

        $this->assertEqualsCanonicalizing(self::MEDIA_SLOTS, array_keys($media));

        foreach ($media as $key => $slot) {
            $this->assertNotSame('', $slot['label'], $key);
            $this->assertLessThanOrEqual(120, mb_strlen($slot['label']), $key);
            $this->assertNotSame('', $slot['note'], "{$key}: isi gambar asli §10.4");
        }

        $this->assertSame('Kuda kepang bambu', $media['challenge.item.tmg-4-03']['label']);
        $this->assertSame('puzzle', $media['challenge.item.wnb-1-j01']['kind']);
        $this->assertSame(['wnb-1-j01'], $media['challenge.item.wnb-1-j01']['used_by']);
        $this->assertSame('Pasar Budaya Temanggung', $media['challenge.tmg-4.scene']['label']);
        $this->assertSame('arca Buddha duduk dari batu (sengaja tidak sesuai bacaan)', $media['challenge.item.tmg-3-b03']['note']);
    }

    public function testReadingChunksKeepParagraphBreaksAfterTheBlank(): void
    {
        foreach ($this->flat()['sheets']['items'] as $row) {
            if ($row['interaction_type'] !== 'fill_blank_bank') {
                continue;
            }

            foreach ([$row['prompt_id'], $row['prompt_en']] as $text) {
                // importer memangkas tepi sel: jeda paragraf di tepi akan hilang
                $this->assertSame(trim($text), $text, "{$row['item_key']}: jeda paragraf harus di tengah potongan");
                $this->assertStringNotContainsString('⏎', $text);

                if (str_contains($text, "\n")) {
                    $this->assertStringContainsString("\n\n", $text);
                    $this->assertLessThan(strpos($text, "\n"), strpos($text, '___'), "{$row['item_key']}: jeda paragraf sesudah rumpang");
                }
            }
        }
    }

    /**
     * Fact/opinion in Magelang Benar atau Salah (draft 2): two items per
     * variant, and the answer keys approved in the review (catatan-revisi §7.2)
     * so the true/false pattern cannot be guessed.
     */
    public function testTrueFalseKeysAndFactOpinionItems(): void
    {
        $keys   = [];
        $skills = [];

        foreach ($this->flat()['sheets']['items'] as $row) {
            if ($row['interaction_type'] === 'verdict_card' && $row['is_active'] === '1') {
                $keys[$row['node_ref']][$row['variant_group']]   = ($keys[$row['node_ref']][$row['variant_group']] ?? '') . strtoupper($row['answer_id'][0]);
                $skills[$row['node_ref']][$row['variant_group']][] = $row['skill'];
            }
        }

        $this->assertSame(['a' => 'BBSBS', 'b' => 'SBBSB', 'c' => 'BSBBS'], $keys['mgl-3']);
        $this->assertSame(['a' => 'BBSBSS', 'b' => 'SBBSSB', 'c' => 'BSBBSS'], $keys['wnb-3']);

        foreach ($skills['mgl-3'] as $group => $list) {
            $this->assertSame(
                ['gagasan_pokok' => 1, 'membedakan_fakta_pendapat' => 2, 'menghubungkan_informasi' => 1, 'mengklasifikasikan_tindakan' => 1],
                (static function (array $count): array { ksort($count); return $count; })(array_count_values($list)),
                "mgl-3-{$group}",
            );
        }

        $this->assertSame('Membedakan fakta dan pendapat', DifficultyRules::skillLabel('membedakan_fakta_pendapat'));
    }

    // ------------------------------------------------- aturan §7.5 menolak

    public function testVariantAndPuzzleRules(): void
    {
        $node = $this->node('tmg-2');
        unset($node['variants']['c']);
        $this->assertProblem($node, 'error', 'tepat 3 variasi');

        // Susun Gambar: 3–10 variants, and a–c (first attempt) must exist
        $node = $this->node('tmg-1');
        unset($node['variants']['c']);
        $this->assertProblem($node, 'error', 'Variasi c belum ada');

        $node             = $this->node('tmg-1');
        $node['variants'] = ['a' => $node['variants']['a'], 'b' => $node['variants']['b']];
        $this->assertProblem($node, 'error', 'paling sedikit 3 variasi');

        $node                     = $this->node('tmg-1');
        $node['variants']['k']    = $node['variants']['a'];
        $this->assertProblem($node, 'error', 'paling banyak 10');

        $node                                   = $this->node('mgl-1');
        $node['variants']['b']['items'][]        = $node['variants']['b']['items'][0];
        $node['variants']['a']['items'][0]['media'] = false;
        $this->assertProblem($node, 'error', 'tepat satu gambar per variasi');
        $this->assertProblem($node, 'error', 'Gambar Susun Gambar belum ditentukan');

        $node                                           = $this->node('wnb-1');
        $node['variants']['a']['items'][0]['type']      = 'single_choice';
        $this->assertProblem($node, 'error', "Jenis soal 'single_choice' tidak cocok");
    }

    public function testReadingRules(): void
    {
        $node = $this->node('mgl-2');
        array_pop($node['variants']['a']['items']);
        $this->assertProblem($node, 'error', 'tepat 5 rumpang');

        $node = $this->node('mgl-2');
        $node['variants']['b']['items'][1]['prompt_id'] = '___ dimulai dengan rumpang.';
        $node['variants']['b']['items'][2]['prompt_en'] = 'Two ___ blanks ___.';
        $node['variants']['b']['items'][3]['answer_id'] = 'Borobudur';
        $node['variants']['b']['items'][4]['bloom']     = '';
        $this->assertProblem($node, 'error', 'tidak boleh diawali ___');
        $this->assertProblem($node, 'error', 'bahasa Inggris harus punya tepat satu ___');
        $this->assertProblem($node, 'error', "Jawaban 'borobudur' sama dengan jawaban soal mgl-2-b01");
        $this->assertProblem($node, 'error', 'Bloom) wajib diisi');

        $node = $this->node('wnb-2');
        $node['variants']['c']['distractors'][0]['id'] = 'karena';   // sama dengan jawaban c01
        $node['variants']['a']['distractors'][]         = ['id' => 'embun', 'en' => 'dew'];
        $this->assertProblem($node, 'error', "Kata pengecoh 'karena' sama dengan jawaban soal wnb-2-c01");
        $this->assertProblem($node, 'error', 'Variasi ini punya 3 kata pengecoh; harus tepat 2');

        $node                     = $this->node('tmg-2');
        $node['distractor_count'] = 0;
        $this->assertProblem($node, 'error', 'aturan tingkat mudah adalah 1');

        $node = $this->node('tmg-2');
        $node['variants']['a']['items'][0]['bloom'] = 'C4';
        $this->assertProblem($node, 'warning', 'C4 di luar anjuran tingkat mudah (C1–C2');
    }

    public function testTrueFalseRules(): void
    {
        $node     = $this->node('mgl-3');
        $passages = $this->flat()['passages'];
        array_pop($node['variants']['a']['items']);
        $node['variants']['b']['items'][0]['passage']   = 'mgl-3-c';
        $node['variants']['c']['items'][0]['answer_id'] = 'pendapat';
        $node['variants']['c']['items'][1]['skill']     = '';
        $node['variants']['c']['items'][2]['skill']     = 'menilai_pilihan';
        $this->assertProblem($node, 'error', 'tepat 5 pernyataan', $passages);
        $this->assertProblem($node, 'error', 'tepat satu bacaan', $passages);
        $this->assertProblem($node, 'error', 'Kunci jawaban harus benar atau salah', $passages);
        $this->assertProblem($node, 'error', 'Keterampilan (skill) pernyataan belum diisi', $passages);
        $this->assertProblem($node, 'warning', "Keterampilan 'menilai_pilihan' bukan keterampilan tingkat sedang", $passages);

        $node                               = $this->node('wnb-3');
        $passages['wnb-3-a']['body_id']     = 'Satu paragraf saja.';
        foreach ($node['variants']['b']['items'] as $i => $item) {
            $node['variants']['b']['items'][$i]['answer_id'] = 'benar';
        }
        $node['variants']['c']['items'][0]['bloom'] = 'C2';
        $this->assertProblem($node, 'error', 'punya 1 paragraf; aturan tingkat sulit adalah 3 paragraf', $passages);
        $this->assertProblem($node, 'warning', 'Tidak ada pernyataan berkunci salah', $passages);
        $this->assertProblem($node, 'warning', 'C2 di luar anjuran tingkat sulit (C4–C5', $passages);
    }

    public function testHuntRules(): void
    {
        $node  = $this->node('mgl-4');
        $items = &$node['variants']['a']['items'];
        array_pop($items);                       // satu pengecoh hilang → 4 + 5
        $items[4]['wrong_id'] = '';              // pengecoh tanpa umpan balik
        $items[0]['media']    = false;           // benda tanpa gambar
        $items[1]['x']        = 95;              // keluar adegan (95 + 12 > 100)
        $items[2]['x']        = $items[3]['x'];  // menumpuk penuh dengan benda lain
        $items[2]['y']        = $items[3]['y'];
        unset($items);

        $this->assertProblem($node, 'error', '4 benda dicari dan 5 pengecoh; aturan tingkat sedang adalah 4 dicari + 6 pengecoh');
        $this->assertProblem($node, 'error', 'wajib punya umpan balik bahasa Indonesia');
        $this->assertProblem($node, 'error', 'Gambar benda belum ditentukan');
        $this->assertProblem($node, 'error', 'Posisi benda keluar dari adegan');
        $this->assertProblem($node, 'error', 'menumpuk 100 % luasnya');

        // Batas 20 %: geser setengah lebar → 50 % menumpuk (galat); geser 90 % → 10 % (boleh)
        $this->assertEqualsWithDelta(0.5, BankRules::overlap(['x' => 10, 'y' => 10, 'w' => 12], ['x' => 16, 'y' => 10, 'w' => 12]), 0.0001);
        $this->assertEqualsWithDelta(0.1, BankRules::overlap(['x' => 10, 'y' => 10, 'w' => 10], ['x' => 19, 'y' => 10, 'w' => 10]), 0.0001);
        $this->assertSame(0.0, BankRules::overlap(['x' => 10, 'y' => 10, 'w' => 10], ['x' => 30, 'y' => 10, 'w' => 10]));
    }

    /** Explicit item keys must belong to their own challenge and variant, and stay unique. */
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
        $this->assertStringContainsString('seharusnya 4 berurutan', $wrong[0]['message']);

        $this->assertSame(['tmg-2-a01'], BankRules::duplicateKeys(['tmg-2-a01', 'tmg-2-a02', 'tmg-2-a01']));
        $this->assertSame(3, BankRules::paragraphCount("Satu.\n\nDua.\n\n  \nTiga."));
    }

    // ------------------------------------------------------------- bantu

    /** @return array<string, mixed> */
    private function flat(): array
    {
        return self::$flat ??= BankSource::flatten(BankSource::load(ROOTPATH . 'docs/bank-soal/data'));
    }

    /** @return array<string, mixed> salinan node bentuk BankRules */
    private function node(string $ref): array
    {
        foreach ($this->flat()['nodes'] as $node) {
            if ($node['ref'] === $ref) {
                $this->assertSame([], BankRules::node($node, $this->flat()['passages']), "{$ref} awalnya bersih");

                return $node;
            }
        }

        $this->fail("Tantangan {$ref} tidak ada.");
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

        $this->fail("Tidak ada {$level} berisi '{$needle}'. Temuan: " . implode(' | ', $found));
    }
}
