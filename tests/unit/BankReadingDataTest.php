<?php

use App\Libraries\ClozeReading;
use App\Libraries\DifficultyRules;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Isi bank soal kelas 5 (docs/bank-soal/data/*.php) memenuhi aturan tingkat
 * wilayahnya tanpa galat maupun peringatan: tiga gambar Susun Gambar aktif
 * per wilayah, dan satu bacaan Isi Rumpang dengan jumlah rumpang, kata
 * pengecoh, dan tingkat Bloom yang sesuai (Temanggung 3 rumpang tanpa
 * pengecoh, C1–C2; Magelang 4–5, C2–C3; Wonosobo 6–7, C3–C4).
 *
 * @internal
 */
final class BankReadingDataTest extends CIUnitTestCase
{
    private const REGIONS = [
        'temanggung' => ['difficulty' => 'mudah', 'puzzle' => 'tmg-1', 'reading' => 'tmg-2'],
        'magelang'   => ['difficulty' => 'sedang', 'puzzle' => 'mgl-1', 'reading' => 'mgl-2'],
        'wonosobo'   => ['difficulty' => 'sulit', 'puzzle' => 'wnb-1', 'reading' => 'wnb-2'],
    ];

    public function testRegionDifficultyMatchesTheLevelSeeder(): void
    {
        $seeder = file_get_contents(APPPATH . 'Database/Seeds/LevelSeeder.php');

        foreach (self::REGIONS as $file => $expect) {
            $this->assertSame($expect['difficulty'], $this->region($file)['difficulty'], $file);
            $this->assertMatchesRegularExpression("/'code'\\s*=>\\s*'{$file}'.*?'difficulty'\\s*=>\\s*'{$expect['difficulty']}'/s", $seeder, $file);
        }
    }

    public function testEachPuzzleNodeHasThreeActiveImages(): void
    {
        foreach (self::REGIONS as $file => $expect) {
            $node   = $this->region($file)['nodes'][$expect['puzzle']];
            $active = $this->activeItems($expect['puzzle'], $node);

            $this->assertSame('puzzle', $node['engine'], $expect['puzzle']);
            $this->assertSame(1, (int) $node['items_per_round'], "{$expect['puzzle']}: satu gambar per permainan");
            $this->assertCount(3, $active, "{$expect['puzzle']}: tiga variasi gambar aktif");

            foreach ($active as $key => $item) {
                $this->assertSame('puzzle_arrange', $item['type'], $key);
                $this->assertNotEmpty($item['media']['key'] ?? null, "{$key}: gambar utuh wajib");
                $this->assertArrayNotHasKey('answer', $item, "{$key}: kunci dibuat importer dari ukuran papan wilayah");
            }

            $media = array_map(static fn (array $item): string => $item['media']['key'], $active);
            $this->assertSame($media, array_unique($media), "{$expect['puzzle']}: tiga gambar berbeda");
        }
    }

    public function testEachReadingFollowsItsRegionRulesWithoutWarnings(): void
    {
        foreach (self::REGIONS as $file => $expect) {
            $node   = $this->region($file)['nodes'][$expect['reading']];
            $blanks = [];

            foreach ($this->activeItems($expect['reading'], $node) as $key => $item) {
                $this->assertSame('fill_blank_bank', $item['type'], $key);
                $this->assertSame('needs_verification', $item['status'] ?? null, "{$key}: draf bacaan perlu ditinjau guru");

                $blanks[] = [
                    'key'       => $key,
                    'prompt_id' => $item['prompt'][0],
                    'prompt_en' => $item['prompt'][1],
                    'answer_id' => $item['answer'][0],
                    'answer_en' => $item['answer'][1],
                    'bloom'     => $item['bloom'] ?? null,
                ];
            }

            $this->assertTrue($node['reading'] ?? false, "{$expect['reading']}: tampil sebagai satu bacaan");
            $this->assertTrue($node['use_word_bank'] ?? false, "{$expect['reading']}: memakai bank kata");
            $this->assertSame(count($blanks), (int) $node['items_per_round'], "{$expect['reading']}: semua rumpang tampil");

            $problems = ClozeReading::problems(
                $blanks,
                array_map(static fn (array $d): array => ['id' => $d[0], 'en' => $d[1]], $node['distractors']),
                (int) $node['distractor_count'],
                true,
                $expect['difficulty'],
            );

            $this->assertSame(['errors' => [], 'warnings' => []], $problems, $expect['reading']);
        }
    }

    public function testWordBankSizesMatchTheAgreedDesign(): void
    {
        // Temanggung 3 kata; Magelang 5 jawaban + 2 pengecoh; Wonosobo 7 + 2
        $expected = ['temanggung' => [3, 0], 'magelang' => [5, 2], 'wonosobo' => [7, 2]];

        foreach (self::REGIONS as $file => $expect) {
            $node = $this->region($file)['nodes'][$expect['reading']];

            $this->assertSame($expected[$file], [count($this->activeItems($expect['reading'], $node)), (int) $node['distractor_count']], $file);
        }
    }

    public function testReadingChunksKeepParagraphBreaksAfterTheBlank(): void
    {
        foreach (self::REGIONS as $file => $expect) {
            foreach ($this->activeItems($expect['reading'], $this->region($file)['nodes'][$expect['reading']]) as $key => $item) {
                foreach ($item['prompt'] as $text) {
                    // importer memangkas tepi sel: jeda paragraf di tepi akan hilang
                    $this->assertSame(trim($text), $text, "{$key}: jeda paragraf harus di tengah potongan");

                    if (str_contains($text, "\n")) {
                        $this->assertLessThan(strpos($text, "\n"), strpos($text, '___'), "{$key}: jeda paragraf sesudah rumpang");
                    }
                }
            }
        }
    }

    public function testItemsAreRetiredNotDeleted(): void
    {
        // Kode butir mengikuti posisi di berkas data. Menghapus butir lama
        // menggeser kode butir sesudahnya dan memutus jawaban siswa yang
        // sudah tersimpan, jadi jumlah butir per tantangan tidak boleh turun.
        $minimum = ['tmg-1' => 4, 'tmg-2' => 13, 'mgl-1' => 7, 'mgl-2' => 15, 'wnb-1' => 7, 'wnb-2' => 17];

        foreach (self::REGIONS as $file => $expect) {
            foreach ([$expect['puzzle'], $expect['reading']] as $ref) {
                $this->assertGreaterThanOrEqual($minimum[$ref], count($this->region($file)['nodes'][$ref]['items']), "{$ref}: istirahatkan soal lama ('active' => false), jangan dihapus");
            }
        }
    }

    /** @return array<string, mixed> */
    private function region(string $file): array
    {
        static $cache = [];

        return $cache[$file] ??= (static fn (string $path): array => require $path)(ROOTPATH . "docs/bank-soal/data/{$file}.php");
    }

    /**
     * Soal aktif dengan kode butirnya (posisi di berkas, mulai 01).
     *
     * @return array<string, array<string, mixed>>
     */
    private function activeItems(string $ref, array $node): array
    {
        $out = [];

        foreach ($node['items'] as $index => $item) {
            if ($item['active'] ?? true) {
                $out[sprintf('%s-%02d', $ref, $index + 1)] = $item;
            }
        }

        return $out;
    }
}
