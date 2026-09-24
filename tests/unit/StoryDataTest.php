<?php

use App\Libraries\StorySync;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * app/Database/Seeds/data/story.php harus salinan persis docs/naskah-cerita.md:
 * jumlah baris per konteks dan per wilayah, tokoh, pose, efek, judul, dan
 * teks dwibahasa. Naskah adalah sumber tunggal untuk rekaman audio, jadi teks
 * di permainan tidak boleh menyimpang darinya.
 *
 * @internal
 */
final class StoryDataTest extends CIUnitTestCase
{
    /** Jumlah per konteks, dari tabel "Konvensi berkas audio" di naskah */
    private const CONTEXT_COUNTS = [
        'intro'        => 9,
        'map_intro'    => 3,
        'region_intro' => 12,
        'level_open'   => 47,
        'level_done'   => 12,
        'ending'       => 5,
    ];

    /** Jumlah per wilayah dan konteks */
    private const REGION_COUNTS = [
        'temanggung' => ['region_intro' => 4, 'level_open' => 15, 'level_done' => 4],
        'magelang'   => ['region_intro' => 4, 'level_open' => 16, 'level_done' => 4],
        'wonosobo'   => ['region_intro' => 4, 'level_open' => 16, 'level_done' => 4],
    ];

    public function testCountsPerContextMatchTheScript(): void
    {
        $story  = StorySync::storyData();
        $counts = array_count_values(array_column($story, 'context'));

        $this->assertCount(88, $story);
        $this->assertSame(self::CONTEXT_COUNTS, $counts);
        $this->assertSame($this->scriptCounts(), $counts, 'tabel jumlah di naskah');
    }

    public function testCountsPerRegionMatchTheScript(): void
    {
        $counts = [];

        foreach (StorySync::storyData() as $line) {
            if ($line['level'] !== null) {
                $counts[$line['level']][$line['context']] = ($counts[$line['level']][$line['context']] ?? 0) + 1;
            }
        }

        $this->assertSame(self::REGION_COUNTS, $counts);
    }

    public function testEveryLineMatchesTheScriptExactly(): void
    {
        $script = $this->parseScript();
        $story  = StorySync::storyData();

        $this->assertCount(count($script), $story);

        foreach ($script as $i => $expected) {
            $this->assertSame($expected, $story[$i], 'baris ' . $expected['code']);
        }
    }

    public function testSequencesStartAtOneAndHaveNoGaps(): void
    {
        $groups = [];

        foreach (StorySync::storyData() as $line) {
            $groups[($line['level'] ?? '_') . '|' . $line['context']][] = $line['sequence'];
        }

        foreach ($groups as $group => $sequences) {
            $this->assertSame(range(1, count($sequences)), $sequences, $group);
        }
    }

    public function testGlobalAndRegionContextsFollowConfig(): void
    {
        $contexts = config('Gelita')->dialogueContexts;

        foreach (StorySync::storyData() as $line) {
            $scope = $line['level'] === null ? 'global' : 'level';
            $this->assertContains($line['context'], $contexts[$scope], $line['code']);
        }
    }

    public function testPosesAndEffectsAreInTheDictionary(): void
    {
        $config = config('Gelita');

        foreach (StorySync::storyData() as $line) {
            if ($line['character'] === 'narator') {
                $this->assertNull($line['pose'], $line['code'] . ': narator tanpa gambar');
            } else {
                $this->assertContains($line['pose'], $config->characterPoses[$line['character']], $line['code']);
            }

            if ($line['effect'] !== null) {
                $this->assertContains($line['effect'], $config->dialogueEffects, $line['code']);
            }
        }
    }

    /** Setiap pose tokoh punya slot frame media, agar dapat diunggah dari /admin/media. */
    public function testEveryPoseHasAnimationSlots(): void
    {
        $config = config('Gelita');
        $slugs  = ['jaka' => 'jaka', 'mbah_kedu' => 'kedu'];

        foreach ($slugs as $character => $slug) {
            foreach ($config->characterPoses[$character] as $pose) {
                $this->assertArrayHasKey("{$slug}.{$pose}", $config->characterAnimations);
            }
        }

        $defaults = (new ReflectionClassConstant(App\Database\Seeds\MediaAssetSeeder::class, 'DEFAULT_ANIMATIONS'))->getValue();
        $this->assertSame(array_keys($config->characterAnimations), array_keys($defaults), 'cadangan seeder sama dengan Config');
    }

    public function testLegacyKeysPointAtStoryPositions(): void
    {
        $legacy    = require APPPATH . 'Database/Seeds/data/story-legacy.php';
        $positions = [];

        foreach (StorySync::storyData() as $line) {
            $positions[($line['level'] ?? '_') . '|' . $line['context'] . '|' . $line['sequence']] = true;
        }

        $this->assertCount(18, $legacy);

        foreach (array_keys($legacy) as $key) {
            $this->assertArrayHasKey($key, $positions, $key);
        }
    }

    public function testSeederReadsTheStoryFile(): void
    {
        $seeder = (string) file_get_contents(APPPATH . 'Database/Seeds/ContentSeeder.php');

        $this->assertStringContainsString("data/story.php", $seeder);
        $this->assertStringNotContainsString('Lentera yang Padam', $seeder, 'teks seeder lama hanya di story-legacy.php');
    }

    // ------------------------------------------------------------ bantuan

    /** @return array<string, int> jumlah per konteks dari tabel di naskah */
    private function scriptCounts(): array
    {
        preg_match_all('/^\| `([a-z_]+)` \| `[^`]+` \| (\d+) \|/m', $this->script(), $m, PREG_SET_ORDER);

        $counts = [];

        foreach ($m as [, $context, $count]) {
            $counts[$context] = (int) $count;
        }

        return $counts;
    }

    /**
     * Baris naskah dalam bentuk yang sama dengan data/story.php.
     *
     * @return list<array<string, mixed>>
     */
    private function parseScript(): array
    {
        $characters = ['Narator' => 'narator', 'Jaka' => 'jaka', 'Mbah Kedu' => 'mbah_kedu'];
        $rows       = [];
        $context    = null;
        $level      = null;
        $sequence   = [];
        $current    = null;

        foreach (preg_split('/\R/', $this->script()) as $line) {
            if (preg_match('/^## \d+\. .*\(`([a-z_]+)`\)\s*$/', $line, $m)) {
                $context = $m[1];
                $level   = null;
            } elseif (str_starts_with($line, '## ')) {
                $context = null;
            } elseif ($context !== null && preg_match('/^### ([A-Za-z]+)/', $line, $m)) {
                $level = strtolower($m[1]);
            } elseif ($context !== null && preg_match('/^#### `([a-z0-9-]+)` · ([^·]+?)(?: · pose `([a-z]+)`)?(?: · efek `([a-z-]+)`)?\s*$/', $line, $m)) {
                if ($current !== null) {
                    $rows[] = $current;
                }

                $group            = ($level ?? '_') . '|' . $context;
                $sequence[$group] = ($sequence[$group] ?? 0) + 1;
                $current          = [
                    'code'      => $m[1],
                    'level'     => $level,
                    'context'   => $context,
                    'sequence'  => $sequence[$group],
                    'character' => $characters[trim($m[2])],
                    'pose'      => ($m[3] ?? '') ?: null,
                    'effect'    => ($m[4] ?? '') ?: null,
                    'title_id'  => null,
                    'title_en'  => null,
                    'text_id'   => null,
                    'text_en'   => null,
                ];
            } elseif ($current !== null && preg_match('/^\*\*Judul:\*\* (.+) \/ \*(.+)\*\s*$/', $line, $m)) {
                [$current['title_id'], $current['title_en']] = [$m[1], $m[2]];
            } elseif ($current !== null && preg_match('/^\*\*(ID|EN):\*\* (.+)$/', $line, $m)) {
                $current['text_' . strtolower($m[1])] = $m[2];
            }
        }

        if ($current !== null) {
            $rows[] = $current;
        }

        return $rows;
    }

    private function script(): string
    {
        return (string) file_get_contents(ROOTPATH . 'docs/naskah-cerita.md');
    }
}
