<?php

namespace App\Libraries;

/**
 * Checks of the grade 5 research bank (31 §8.2; first set: Dok 2 §7.5).
 *
 * One source of rules for every user: docs/bank-soal/build-workbook.php,
 * the importer (ContentImportService), ContentVerifier
 * (`gelita:content:verify` and the panel), and the bank tests. Pure (no
 * framework). Every user first turns its source (data files, workbook, or
 * database) into the node shape of BankSource::nodesFromSheets():
 *
 *     [
 *         'ref' => 'tmg-2', 'level' => 'temanggung', 'sequence' => 2,
 *         'engine' => 'rumpang', 'difficulty' => 'mudah', 'distractor_count' => 2,
 *         'variants'  => ['a' => ['distractors' => [['id' => …, 'en' => …]], 'items' => [BankSource::itemFromRow() …]]],
 *         'codes'     => ['tmg-2-a01' => [['type' => 'word', 'ref' => 'pasir', 'code' => 'INF-HUB', 'main' => false], …]],
 *         'posts'     => ['a' => ['scene' => [row of post_scenes], 'parts' => [['key' => 'A1', 'kind' => …, 'x' => …], …]]],
 *         'scenarios' => ['tmg-5-a01' => ['topic' => 'PRI', 'steps' => 1, 'actions' => […], 'reasons' => […]]],
 *     ]
 *
 * Passages: passage_key → BankSource::passageFromRow().
 *
 * Findings are `['level' => 'error'|'warning', 'node', 'variant', 'item',
 * 'message']`. An error rejects the import and stops the builder; a warning
 * needs a look and becomes an error with `--siap-penelitian`.
 */
final class BankRules
{
    /** Challenge order of every region; `5 => 'skenario'` since part 6 (32 K1). region() follows its length. */
    public const ENGINES = [1 => 'puzzle', 2 => 'rumpang', 3 => 'boleh', 4 => 'cari', 5 => 'skenario'];

    /** Item types per engine (`find_part` and `scenario_choice` since 31 §5). */
    public const INTERACTION = [
        'puzzle'   => ['puzzle_arrange'],
        'rumpang'  => ['fill_blank_bank'],
        'boleh'    => ['verdict_card'],
        'cari'     => ['find_object', 'find_part'],
        'skenario' => ['scenario_choice'],
    ];

    /** Dimension each scored item type measures. */
    public const DIMENSION_OF_TYPE = [
        'fill_blank_bank' => 'D1',
        'verdict_card'    => 'D1',
        'find_object'     => 'PBK',
        'find_part'       => 'D3',
        'scenario_choice' => 'D2',
    ];

    /** Active variants per engine: [min, max]. Exact sets are checked in node(). */
    public const VARIANTS = ['puzzle' => [2, 11], 'rumpang' => [2, 2], 'boleh' => [2, 2], 'cari' => [2, 2], 'skenario' => [2, 2]];

    /** A first attempt draws from these variants only (booklet or random, 31 §11.2). */
    public const FIRST_ATTEMPT_GROUPS = ['a', 'b'];

    /** Items every attempt plays besides its variant: anchor cards, Cari Objek round 1 (K11). */
    public const SHARED_GROUP = 'x';

    /** Smallest gap between two tap areas, and the scene width it is measured on (§9). */
    public const MIN_GAP_DP = HuntGeometry::MIN_GAP_DP;

    public const HUNT_SCENE_DP = HuntGeometry::HUNT_SCENE_DP;

    /** Height of a round-1 object in percent of the scene height = width × 16/9 (square object, 16:9 scene). */
    public const OBJECT_ASPECT = 16 / 9;

    /** Largest a/b length difference before the equivalence warning (06 rule no. 6). */
    public const MAX_LENGTH_GAP = 0.15;

    /** Kinds of a round-2 tap part (K8). */
    public const PART_KINDS = ['account', 'credit', 'caption', 'board', 'comment', 'button', 'meta'];

    /**
     * Every check of one challenge.
     *
     * @param array<string, mixed>                $node
     * @param array<string, array<string, mixed>> $passages passage_key → passage
     *
     * @return list<array{level: string, node: string, variant: ?string, item: ?string, message: string}>
     */
    public static function node(array $node, array $passages = []): array
    {
        $engine   = (string) ($node['engine'] ?? '');
        $ref      = (string) ($node['ref'] ?? '');
        $variants = (array) ($node['variants'] ?? []);

        if (! isset(self::INTERACTION[$engine])) {
            return [self::problem('error', $ref, null, null, "Jenis tantangan '{$engine}' tidak dikenali.")];
        }

        $have = array_map('strval', array_keys(array_filter($variants, static fn ($v): bool => ((array) ($v['items'] ?? [])) !== [])));
        $out  = self::variantSet($engine, $ref, $have);

        foreach ($variants as $group => $variant) {
            $items = array_values((array) ($variant['items'] ?? []));

            if ($items === []) {
                continue;
            }

            $group = (string) $group;
            $out   = [...$out, ...self::commonItemProblems($node, $group, $items)];
            $out   = [...$out, ...match ($engine) {
                'puzzle'   => self::puzzle($ref, $group, $items),
                'rumpang'  => self::reading($node, $group, (array) $variant, $items),
                'boleh'    => self::trueFalse($node, $group, $items, $passages),
                'cari'     => $group === self::SHARED_GROUP ? self::huntRoundOne($node, $items) : self::huntRoundTwo($node, $group, $items),
                'skenario' => self::scenarios($node, $group, $items),
            }];
        }

        $out = [...$out, ...self::equivalence($node, $passages)];
        $out = [...$out, ...self::textRules($node, $passages)];

        return self::unique($out);
    }

    /**
     * Checks across challenges and regions (31 §8.2 warnings): code coverage
     * per region, and Susun Gambar captions a/b that mention a key of this
     * region or a later one.
     *
     * @param array<string, array<string, mixed>> $nodes ref → node
     *
     * @return list<array{level: string, node: string, variant: ?string, item: ?string, message: string}>
     */
    public static function bank(array $nodes, array $passages = []): array
    {
        $out     = [];
        $byLevel = [];

        foreach ($nodes as $node) {
            $byLevel[(string) $node['level']][] = $node;
        }

        foreach ($byLevel as $level => $list) {
            $used = [];

            foreach ($list as $node) {
                foreach (self::activeItems($node) as $item) {
                    if (($item['error_code'] ?? '') !== '') {
                        $used[$item['error_code']] = true;
                    }
                }

                foreach ((array) ($node['codes'] ?? []) as $codes) {
                    foreach ($codes as $code) {
                        $used[$code['code']] = true;
                    }
                }

                foreach ((array) ($node['scenarios'] ?? []) as $scenario) {
                    foreach ((array) $scenario['actions'] as $action) {
                        if (($action['code'] ?? '') !== '') {
                            $used[$action['code']] = true;
                        }
                    }
                }
            }

            $missing = array_values(array_diff(DifficultyRules::expectedCodes($list[0]['difficulty'] ?? null), array_keys($used)));

            if ($missing !== []) {
                $out[] = self::problem('warning', $level, null, null, 'Kode kesalahan yang diharapkan belum muncul di wilayah ini: ' . implode(', ', $missing) . '.');
            }
        }

        // Leaks: captions a/b of Susun Gambar vs keys of this region and later ones
        $order = array_flip(array_keys(BankSource::REGIONS));

        foreach ($nodes as $node) {
            if (($node['engine'] ?? '') !== 'puzzle') {
                continue;
            }

            $keywords = [];

            foreach ($nodes as $other) {
                if (($order[(string) $other['level']] ?? 0) >= ($order[(string) $node['level']] ?? 0)) {
                    $keywords = [...$keywords, ...self::keywords($other)];
                }
            }

            foreach (self::FIRST_ATTEMPT_GROUPS as $group) {
                foreach ((array) ($node['variants'][$group]['items'] ?? []) as $item) {
                    $caption = mb_strtolower((string) $item['prompt_id']);

                    foreach (array_unique($keywords) as $word) {
                        if (preg_match('/(?<![\p{L}\d])' . preg_quote($word, '/') . '(?![\p{L}\d])/u', $caption) === 1) {
                            $out[] = self::problem('warning', (string) $node['ref'], $group, (string) $item['key'], "Keterangan memuat kata kunci butir yang dinilai ('{$word}'); periksa kebocoran dengan telaah 08.");
                        }
                    }
                }
            }
        }

        return $out;
    }

    /** The variant rule of one engine, for messages. */
    public static function variantRuleLabel(string $engine): string
    {
        return match ($engine) {
            'cari'   => 'babak 1 variasi x, babak 2 tepat variasi a dan b',
            'boleh'  => 'tepat variasi a dan b, ditambah kartu jangkar x',
            'puzzle' => 'paling sedikit 2 variasi (a dan b wajib) dan paling banyak 11 (a sampai k)',
            default  => 'tepat 2 variasi (a, b)',
        };
    }

    /**
     * Region: active challenges in the order of ENGINES.
     *
     * @param list<array{sequence: int, engine: string}> $nodes active challenges of the region
     *
     * @return list<array{level: string, node: string, variant: ?string, item: ?string, message: string}>
     */
    public static function region(string $scope, array $nodes): array
    {
        usort($nodes, static fn (array $a, array $b): int => (int) $a['sequence'] <=> (int) $b['sequence']);

        $engines = array_map(static fn (array $n): string => (string) $n['engine'], $nodes);

        // A bank without Skenario Digital keeps the first four in order (32 §15.4: main stays releasable)
        if ($engines === array_values(self::ENGINES) || $engines === array_slice(array_values(self::ENGINES), 0, 4)) {
            return [];
        }

        return [self::problem('error', $scope, null, null, sprintf(
            'Wilayah ini punya %d tantangan aktif (%s); seharusnya %d berurutan: %s.',
            count($engines),
            $engines === [] ? '—' : implode(', ', $engines),
            count(self::ENGINES),
            implode(', ', self::ENGINES),
        ))];
    }

    /**
     * Item codes that occur more than once (item_key is unique across regions).
     *
     * @param list<string> $keys
     *
     * @return list<string>
     */
    public static function duplicateKeys(array $keys): array
    {
        $count = array_count_values(array_map(static fn ($k): string => trim((string) $k), $keys));

        return array_keys(array_filter($count, static fn (int $n, string $key): bool => $n > 1 && $key !== '', ARRAY_FILTER_USE_BOTH));
    }

    /** Paragraphs of a text: separated by a blank line. */
    public static function paragraphCount(string $text): int
    {
        $text = trim(str_replace("\r\n", "\n", $text));

        return $text === '' ? 0 : count(preg_split('/\n[ \t]*\n\s*/u', $text) ?: [$text]);
    }

    /**
     * Box of a round-1 object in percent: x, y, w, w × 16/9.
     *
     * @return array{0: float, 1: float, 2: float, 3: float}
     */
    public static function box(array $item): array
    {
        return HuntGeometry::objectBox((float) ($item['x'] ?? 0), (float) ($item['y'] ?? 0), (float) ($item['w'] ?? 0));
    }

    /** @return list<string> host names in the text that are not `.example` */
    public static function badAddresses(string $text): array
    {
        preg_match_all('/(?<![@\w.-])((?:[a-z0-9-]+\.)+[a-z]{2,})(?:\/\S*)?/iu', $text, $m);
        $out = [];

        foreach ($m[1] as $host) {
            $host = rtrim(mb_strtolower($host), '.');

            // Account names (@a.b), numbers (2.300), and abbreviations (Kab.) are not addresses
            if (str_ends_with($host, '.example') || preg_match('/^\d/', $host) === 1 || preg_match('/\.(com|net|org|id|co|io|info|me|app|tv|xyz|edu|gov|my|sch)$/', $host) !== 1) {
                continue;
            }

            $out[] = $host;
        }

        return $out;
    }

    /** @return list<string> phone numbers that are not the masked form 08xx-xxxx-xxxx */
    public static function badPhones(string $text): array
    {
        preg_match_all('/(?<![\dx])(?:\+62|0)8[\dx][\dx\- ]{6,14}[\dx](?![\dx])/i', $text, $m);

        return array_values(array_filter($m[0], static fn (string $p): bool => preg_match('/^08xx-xxxx-xxxx$/i', $p) !== 1));
    }

    // ---------------------------------------------------------- variants

    /** @return list<array<string, mixed>> */
    private static function variantSet(string $engine, string $ref, array $have): array
    {
        if ($have === []) {
            return [self::problem('error', $ref, null, null, 'Belum ada soal aktif.')];
        }

        if ($engine !== 'puzzle') {
            $want = match ($engine) {
                'boleh', 'cari' => ['a', 'b', self::SHARED_GROUP],
                default         => ['a', 'b'],
            };
            $got = $have;
            sort($want);
            sort($got);

            return $got === $want ? [] : [self::problem('error', $ref, null, null, sprintf(
                'Ada variasi aktif %s; aturan bank baru: %s.',
                implode(', ', $have),
                self::variantRuleLabel($engine),
            ))];
        }

        [$min, $max] = self::VARIANTS['puzzle'];
        $out         = [];

        if (count($have) < $min || count($have) > $max || in_array(self::SHARED_GROUP, $have, true)) {
            $out[] = self::problem('error', $ref, null, null, sprintf('Ada %d variasi aktif (%s); aturan: %s.', count($have), implode(', ', $have), self::variantRuleLabel($engine)));
        }

        if (($missing = array_diff(self::FIRST_ATTEMPT_GROUPS, $have)) !== []) {
            $out[] = self::problem('error', $ref, null, null, 'Variasi ' . implode(', ', $missing) . ' belum ada; Susun Gambar wajib punya variasi a dan b untuk pengerjaan pertama.');
        }

        return $out;
    }

    // ------------------------------------------------------------ shared

    /**
     * @param list<array<string, mixed>> $items
     *
     * @return list<array<string, mixed>>
     */
    private static function commonItemProblems(array $node, string $group, array $items): array
    {
        $engine     = (string) $node['engine'];
        $ref        = (string) $node['ref'];
        $difficulty = (string) ($node['difficulty'] ?? '');
        $out        = [];

        foreach ($items as $item) {
            $key  = (string) ($item['key'] ?? '');
            $type = (string) ($item['type'] ?? '');

            if (! in_array($type, self::INTERACTION[$engine], true)) {
                $out[] = self::problem('error', $ref, $group, $key, "Jenis soal '{$type}' tidak cocok dengan tantangan ini (" . implode(', ', self::INTERACTION[$engine]) . ').');

                continue;
            }

            // Rounds belong to Cari Objek only; x only to anchors and round 1 (K7, K11)
            $round = $item['round'] ?? null;

            if ($engine === 'cari') {
                $want = $group === self::SHARED_GROUP ? [1, 'find_object'] : [2, 'find_part'];

                if ((int) $round !== $want[0] || $type !== $want[1]) {
                    $out[] = self::problem('error', $ref, $group, $key, "Variasi {$group} Cari Objek berisi babak {$want[0]} ({$want[1]}); soal ini babak " . ($round ?? '—') . " ({$type}).");
                }
            } elseif ($round !== null) {
                $out[] = self::problem('error', $ref, $group, $key, 'Kolom round (babak) hanya untuk Cari Objek.');
            }

            if ($group === self::SHARED_GROUP && ! in_array($engine, ['boleh', 'cari'], true)) {
                $out[] = self::problem('error', $ref, $group, $key, 'Variasi x hanya untuk kartu jangkar Benar atau Salah dan babak 1 Cari Objek.');
            }

            $code = (string) ($item['error_code'] ?? '');

            if ($code !== '' && ErrorCodes::forbiddenInBank($code)) {
                $out[] = self::problem('error', $ref, $group, $key, "Kode kesalahan '{$code}' tidak boleh ada di bank (kode tak dikenal, *-LAIN, atau NON-*).");
            }

            if ($type === 'puzzle_arrange' || ! empty($item['decoy'])) {
                continue;
            }

            $out = [...$out, ...self::labels($ref, $group, $item, $difficulty)];
        }

        return $out;
    }

    /** Labels of one scored item (31 §8.2, 06, 19). */
    private static function labels(string $ref, string $group, array $item, string $difficulty): array
    {
        $key  = (string) $item['key'];
        $type = (string) $item['type'];
        $dim  = (string) ($item['dim'] ?? '');
        $want = self::DIMENSION_OF_TYPE[$type] ?? null;
        $out  = [];

        if ($dim === '' || ! in_array($dim, DifficultyRules::DIMENSIONS, true)) {
            $out[] = self::problem('error', $ref, $group, $key, $dim === '' ? 'Dimensi (dimension) wajib diisi untuk butir yang dinilai.' : "Dimensi '{$dim}' tidak dikenal (" . implode(', ', DifficultyRules::DIMENSIONS) . ').');
        } elseif ($want !== null && $dim !== $want) {
            $out[] = self::problem('error', $ref, $group, $key, "Butir {$type} mengukur dimensi {$want}, bukan {$dim}.");
        }

        $akm = (string) ($item['akm'] ?? '');

        if ($dim === 'D1') {
            if (! in_array((string) ($item['sub'] ?? ''), DifficultyRules::SUBASPECTS, true)) {
                $out[] = self::problem('error', $ref, $group, $key, 'Sub-aspek (subaspect) D1a atau D1b wajib diisi untuk butir D1.');
            }

            if (! in_array((string) ($item['pirls'] ?? ''), DifficultyRules::PIRLS, true)) {
                $out[] = self::problem('error', $ref, $group, $key, 'Proses PIRLS (pirls_process) wajib diisi untuk butir D1: ' . implode(', ', DifficultyRules::PIRLS) . '.');
            }
        }

        if (in_array($dim, ['D1', 'D2', 'D3'], true)) {
            if (! in_array($akm, DifficultyRules::AKM_LEVELS, true)) {
                $out[] = self::problem('error', $ref, $group, $key, 'Level AKM (akm_level) L1, L2, atau L3 wajib diisi untuk butir D1, D2, dan D3.');
            } else {
                $scope    = $dim === 'D1' ? (string) ($item['sub'] ?? '') : $dim;
                $expected = DifficultyRules::akm($difficulty, $scope);

                if ($expected !== [] && ! in_array($akm, $expected, true)) {
                    $out[] = self::problem('warning', $ref, $group, $key, "Level AKM {$akm} di luar yang diharapkan wilayah ini untuk {$scope} (" . implode(', ', $expected) . ').');
                }
            }
        }

        $bloom = (string) ($item['bloom'] ?? '');

        if ($bloom === '') {
            $out[] = self::problem('error', $ref, $group, $key, 'Tingkat berpikir (Bloom) wajib diisi.');
        } elseif (! DifficultyRules::isBloom($bloom)) {
            $out[] = self::problem('error', $ref, $group, $key, "Tingkat berpikir '{$bloom}' tidak dikenal (C1–C6).");
        } elseif (! DifficultyRules::bloomInRange($bloom, $difficulty)) {
            $out[] = self::problem('warning', $ref, $group, $key, sprintf('Tingkat berpikir %s di luar rentang wilayah ini (%s).', $bloom, DifficultyRules::bloomRangeLabel($difficulty)));
        }

        if (trim((string) ($item['testlet'] ?? '')) === '') {
            $out[] = self::problem('error', $ref, $group, $key, 'Kunci testlet (testlet_key) wajib diisi untuk butir yang dinilai.');
        }

        $skill = trim((string) ($item['skill'] ?? ''));

        if ($skill !== '' && ! DifficultyRules::isSkill($skill)) {
            $out[] = self::problem('error', $ref, $group, $key, "Keterampilan '{$skill}' tidak dikenal. Pakai " . implode(', ', array_keys(DifficultyRules::SKILLS)) . '.');
        }

        if (in_array($type, ['fill_blank_bank', 'verdict_card', 'find_object', 'find_part'], true) && trim((string) ($item['ref'] ?? '')) === '') {
            $out[] = self::problem('error', $ref, $group, $key, 'Sumber rujukan (reference_source) wajib diisi.');
        }

        return $out;
    }

    // ------------------------------------------------------- per engine

    /** @param list<array<string, mixed>> $items */
    private static function puzzle(string $ref, string $group, array $items): array
    {
        $out = [];

        if (count($items) !== 1) {
            $out[] = self::problem('error', $ref, $group, null, 'Ada ' . count($items) . ' gambar dalam satu variasi; Susun Gambar memakai tepat satu gambar per variasi.');
        }

        foreach ($items as $item) {
            if (empty($item['media'])) {
                $out[] = self::problem('error', $ref, $group, (string) $item['key'], 'Gambar Susun Gambar belum ditentukan (kode berkas gambar wajib).');
            }
        }

        return $out;
    }

    /**
     * Isi Rumpang, one variant.
     *
     * @param array<string, mixed>       $variant
     * @param list<array<string, mixed>> $items
     */
    private static function reading(array $node, string $group, array $variant, array $items): array
    {
        $ref        = (string) $node['ref'];
        $difficulty = (string) ($node['difficulty'] ?? '');
        $rule       = DifficultyRules::reading($difficulty);
        $level      = DifficultyRules::normalize($difficulty);
        $out        = [];

        if (count($items) !== $rule['blanks'][0]) {
            $out[] = self::problem('error', $ref, $group, null, 'Bacaan punya ' . count($items) . " rumpang; aturan tingkat {$level} adalah tepat {$rule['blanks'][0]} rumpang.");
        }

        $words = ['id' => [], 'en' => []];

        foreach ($items as $item) {
            $key = (string) $item['key'];

            foreach (['id' => 'Indonesia', 'en' => 'Inggris'] as $lang => $name) {
                $prompt = trim((string) ($item['prompt_' . $lang] ?? ''));

                if ($prompt === '' && $lang === 'en') {
                    continue;
                }

                if (ClozeReading::blankCount($prompt) !== 1) {
                    $out[] = self::problem('error', $ref, $group, $key, "Potongan bacaan bahasa {$name} harus punya tepat satu ___.");
                } elseif (preg_match('/^' . trim(ClozeReading::BLANK, '/') . '/', $prompt) === 1) {
                    $out[] = self::problem('error', $ref, $group, $key, "Potongan bacaan bahasa {$name} tidak boleh diawali ___ (spasi sebelum rumpang hilang saat potongan disambung).");
                }

                $answer = mb_strtolower(trim((string) ($item['answer_' . $lang] ?? '')));

                if ($answer === '') {
                    if ($lang === 'id') {
                        $out[] = self::problem('error', $ref, $group, $key, 'Kunci jawaban (kata untuk rumpang) belum diisi.');
                    }

                    continue;
                }

                if (isset($words[$lang][$answer])) {
                    $out[] = self::problem('error', $ref, $group, $key, "Jawaban '{$answer}' sama dengan jawaban soal {$words[$lang][$answer]}; jawaban dalam satu variasi harus berbeda.");
                }

                $words[$lang][$answer] ??= $key;
            }
        }

        $distractors = array_values((array) ($variant['distractors'] ?? []));
        $count       = (int) ($node['distractor_count'] ?? 0);

        if ($count !== $rule['distractors'][0]) {
            $out[] = self::problem('error', $ref, null, null, "Jumlah kata pengecoh yang tampil {$count}; aturan tingkat {$level} adalah {$rule['distractors'][0]}.");
        }

        if (count($distractors) !== $count) {
            $out[] = self::problem('error', $ref, $group, null, 'Variasi ini punya ' . count($distractors) . " kata pengecoh; harus tepat {$count} (distractor_count).");
        }

        $seen = $words;

        foreach ($distractors as $distractor) {
            foreach (['id', 'en'] as $lang) {
                $word = mb_strtolower(trim((string) ($distractor[$lang] ?? '')));

                if ($word === '') {
                    continue;
                }

                if (isset($words[$lang][$word])) {
                    $out[] = self::problem('error', $ref, $group, null, "Kata pengecoh '{$word}' sama dengan jawaban soal {$words[$lang][$word]}.");
                } elseif (isset($seen[$lang][$word])) {
                    $out[] = self::problem('error', $ref, $group, null, "Kata '{$word}' muncul dua kali di bank kata.");
                }

                $seen[$lang][$word] = 'pengecoh';
            }
        }

        // Exactly one code per blank × wrong word (K6)
        $bank = array_map(static fn (array $i): string => BankSource::wordRef((string) $i['answer_id']), $items);
        $bank = [...$bank, ...array_map(static fn (array $d): string => BankSource::wordRef((string) ($d['id'] ?? '')), $distractors)];

        foreach ($items as $item) {
            $wrong = array_values(array_diff($bank, [BankSource::wordRef((string) $item['answer_id'])]));
            $out   = [...$out, ...self::codeMatrix($node, $group, (string) $item['key'], 'word', $wrong, 'word')];
        }

        return $out;
    }

    /**
     * Benar atau Salah, one variant or the anchor card (x).
     *
     * @param list<array<string, mixed>>          $items
     * @param array<string, array<string, mixed>> $passages
     */
    private static function trueFalse(array $node, string $group, array $items, array $passages): array
    {
        $ref        = (string) $node['ref'];
        $difficulty = (string) ($node['difficulty'] ?? '');
        $anchor     = $group === self::SHARED_GROUP;
        $rule       = $anchor ? DifficultyRules::anchor($difficulty) : DifficultyRules::trueFalse($difficulty);
        $what       = $anchor ? 'Kartu jangkar' : 'Variasi ini';
        $out        = [];

        if (count($items) !== $rule['statements']) {
            $out[] = self::problem('error', $ref, $group, null, "{$what} punya " . count($items) . " pernyataan; aturan wilayah ini tepat {$rule['statements']}.");
        }

        $keys = array_values(array_unique(array_map(static fn (array $i): string => trim((string) ($i['passage'] ?? '')), $items)));

        if (count($keys) !== 1 || $keys[0] === '') {
            $out[] = self::problem('error', $ref, $group, null, 'Semua pernyataan satu variasi harus merujuk tepat satu bacaan (passage_key yang sama).');
        } elseif (! isset($passages[$keys[0]])) {
            $out[] = self::problem('error', $ref, $group, null, "Bacaan '{$keys[0]}' tidak ditemukan.");
        } else {
            $passage = $passages[$keys[0]];
            $genre   = (string) ($passage['genre'] ?? '');
            $layout  = (string) ($passage['layout'] ?? '');

            if (trim((string) ($passage['ref'] ?? '')) === '') {
                $out[] = self::problem('error', $ref, $group, null, "Bacaan {$keys[0]} belum punya sumber rujukan (reference_source).");
            }

            if ($genre !== '' && ! in_array($genre, DifficultyRules::GENRES, true)) {
                $out[] = self::problem('error', $ref, $group, null, "Genre teks '{$genre}' tidak dikenal.");
            } elseif ($genre !== $rule['genre']) {
                $out[] = self::problem('warning', $ref, $group, null, "Genre teks {$keys[0]} '{$genre}', kisi-kisi wilayah ini '{$rule['genre']}'.");
            }

            if ($layout !== '' && ! in_array($layout, DifficultyRules::LAYOUTS, true)) {
                $out[] = self::problem('error', $ref, $group, null, "Bentuk tampilan '{$layout}' tidak dikenal (" . implode(', ', DifficultyRules::LAYOUTS) . ').');
            }
        }

        $verdicts = ['benar' => 0, 'salah' => 0];
        $subs     = [];

        foreach ($items as $item) {
            $key     = (string) $item['key'];
            $verdict = mb_strtolower(trim((string) ($item['answer_id'] ?? '')));
            $sub     = (string) ($item['sub'] ?? '');
            $code    = (string) ($item['error_code'] ?? '');

            if (! isset($verdicts[$verdict])) {
                $out[] = self::problem('error', $ref, $group, $key, 'Kunci jawaban harus benar atau salah.');
            } else {
                $verdicts[$verdict]++;
            }

            $subs[$sub] = ($subs[$sub] ?? 0) + 1;

            if ($code === '') {
                $out[] = self::problem('error', $ref, $group, $key, 'Kode kesalahan (error_code) pernyataan wajib diisi.');
            } elseif (in_array($sub, DifficultyRules::SUBASPECTS, true) && ! ErrorCodes::allowedFor('verdict_' . $sub, $code)) {
                $out[] = self::problem('error', $ref, $group, $key, "Kode {$code} tidak cocok untuk pernyataan {$sub} (" . implode(', ', ErrorCodes::BY_CHOICE['verdict_' . $sub]) . ').');
            }
        }

        if ([$verdicts['benar'], $verdicts['salah']] !== $rule['key_split']) {
            $out[] = self::problem('error', $ref, $group, null, sprintf('%s berkunci %d benar : %d salah; aturan %d : %d.', $what, $verdicts['benar'], $verdicts['salah'], ...$rule['key_split']));
        }

        ksort($subs);
        $wantSubs = $rule['subaspects'];
        ksort($wantSubs);

        if ($subs !== $wantSubs) {
            $out[] = self::problem('error', $ref, $group, null, sprintf('%s punya sub-aspek %s; aturan %s.', $what, self::countLabel($subs), self::countLabel($wantSubs)));
        }

        // Full alternation of the key order (B S B S …) gives the answers away
        $order = array_map(static fn (array $i): string => mb_strtolower((string) $i['answer_id']), $items);
        $runs  = array_filter(array_keys($order), static fn (int $i): bool => $i > 0 && $order[$i] === $order[$i - 1]);

        if (count($order) >= 4 && $runs === []) {
            $out[] = self::problem('warning', $ref, $group, null, 'Urutan kunci berselang-seling penuh (' . implode(' ', array_map(static fn (string $v): string => strtoupper(substr($v, 0, 1)), $order)) . '); acak ulang urutannya.');
        }

        return $out;
    }

    /**
     * Cari Objek round 1 (`x`): one scene for every booklet.
     *
     * @param list<array<string, mixed>> $items
     */
    private static function huntRoundOne(array $node, array $items): array
    {
        $ref     = (string) $node['ref'];
        $group   = self::SHARED_GROUP;
        $rule    = DifficultyRules::hunt((string) ($node['difficulty'] ?? ''));
        $out     = [];
        $targets = array_values(array_filter($items, static fn (array $i): bool => empty($i['decoy'])));
        $decoys  = count($items) - count($targets);

        if (count($targets) !== $rule['targets'] || $decoys !== $rule['decoys']) {
            $out[] = self::problem('error', $ref, $group, null, sprintf('Babak 1 punya %d benda dicari dan %d pengecoh; aturan wilayah ini %d + %d.', count($targets), $decoys, $rule['targets'], $rule['decoys']));
        }

        $boxes = [];

        foreach ($items as $item) {
            $key = (string) $item['key'];

            if (! empty($item['decoy'])) {
                if (trim((string) ($item['wrong_id'] ?? '')) === '') {
                    $out[] = self::problem('error', $ref, $group, $key, 'Benda pengecoh wajib punya umpan balik bahasa Indonesia (wrong_feedback_id).');
                }

                if (! ErrorCodes::allowedFor('object', (string) ($item['error_code'] ?? ''))) {
                    $out[] = self::problem('error', $ref, $group, $key, 'Benda pengecoh wajib punya kode kesalahan INF-BUD atau INF-LIT (error_code).');
                }
            }

            if (empty($item['media'])) {
                $out[] = self::problem('error', $ref, $group, $key, 'Gambar benda belum ditentukan (kode berkas gambar wajib).');
            }

            if (! is_numeric($item['x'] ?? null) || ! is_numeric($item['y'] ?? null) || ! is_numeric($item['w'] ?? null)) {
                $out[] = self::problem('error', $ref, $group, $key, 'Posisi benda (x, y, w) wajib diisi.');

                continue;
            }

            $boxes[$key] = self::box($item);
        }

        $out = [...$out, ...self::tapAreas($ref, $group, $boxes, 'Benda')];
        $all = array_map(static fn (array $i): string => (string) $i['key'], $items);

        foreach ($targets as $target) {
            $wrong = array_values(array_diff($all, [(string) $target['key']]));
            $out   = [...$out, ...self::codeMatrix($node, $group, (string) $target['key'], 'object', $wrong, 'object')];
        }

        return $out;
    }

    /**
     * Cari Objek round 2 (*Periksa Unggahan*), one variant.
     *
     * @param list<array<string, mixed>> $items
     */
    private static function huntRoundTwo(array $node, string $group, array $items): array
    {
        $ref  = (string) $node['ref'];
        $rule = DifficultyRules::hunt((string) ($node['difficulty'] ?? ''));
        $post = $node['posts'][$group] ?? null;
        $out  = [];

        if (count($items) !== $rule['clues']) {
            $out[] = self::problem('error', $ref, $group, null, 'Babak 2 punya ' . count($items) . " petunjuk; aturan tepat {$rule['clues']}.");
        }

        if ($post === null || ($post['parts'] ?? []) === []) {
            return [...$out, self::problem('error', $ref, $group, null, 'Unggahan babak 2 (sheet post_scenes dan post_parts) belum ada untuk variasi ini.')];
        }

        if (trim((string) ($post['scene']['reference_source'] ?? '')) === '') {
            $out[] = self::problem('error', $ref, $group, null, 'Unggahan babak 2 belum punya sumber rujukan (reference_source).');
        }

        $parts = [];
        $boxes = [];

        foreach ($post['parts'] as $part) {
            $key = (string) $part['key'];

            if (isset($parts[$key])) {
                $out[] = self::problem('error', $ref, $group, null, "Bagian ketuk {$key} ditulis lebih dari sekali.");
            }

            if (! in_array((string) $part['kind'], self::PART_KINDS, true)) {
                $out[] = self::problem('error', $ref, $group, null, "Jenis bagian ketuk {$key} '{$part['kind']}' tidak dikenal (" . implode(', ', self::PART_KINDS) . ').');
            }

            if (trim((string) ($part['text_id'] ?? '')) === '') {
                $out[] = self::problem('error', $ref, $group, null, "Bagian ketuk {$key} belum punya teks.");
            }

            $parts[$key] = $part;
            $boxes[$key] = [(float) $part['x'], (float) $part['y'], (float) $part['w'], (float) $part['h']];
        }

        if (count($parts) !== $rule['parts']) {
            $out[] = self::problem('error', $ref, $group, null, 'Unggahan punya ' . count($parts) . " bagian ketuk; aturan wilayah ini {$rule['parts']}.");
        }

        $out   = [...$out, ...self::tapAreas($ref, $group, $boxes, 'Bagian')];
        $types = [];

        foreach ($items as $item) {
            $key    = (string) $item['key'];
            $type   = (string) ($item['clue_type'] ?? '');
            $answer = (string) ($item['answer_id'] ?? '');

            if (! isset(ErrorCodes::CLUE_TYPE_CODES[$type])) {
                $out[] = self::problem('error', $ref, $group, $key, 'Jenis petunjuk (d3_clue_type) wajib ATR, REP, IZN, atau ETK.');
            } else {
                $types[$type] = ($types[$type] ?? 0) + 1;
            }

            if (! isset($parts[$answer])) {
                $out[] = self::problem('error', $ref, $group, $key, "Kunci petunjuk '{$answer}' bukan bagian ketuk unggahan ini.");

                continue;
            }

            $wrong = array_map('strval', array_values(array_diff(array_keys($parts), [$answer])));
            $out   = [...$out, ...self::codeMatrix($node, $group, $key, 'part', $wrong, 'part')];
            $main  = count(array_filter((array) ($node['codes'][$key] ?? []), static fn (array $c): bool => $c['type'] === 'part' && $c['main']));

            if ($main !== $rule['main']) {
                $out[] = self::problem('error', $ref, $group, $key, "Petunjuk ini punya {$main} pengecoh utama; aturan wilayah ini {$rule['main']}.");
            }
        }

        foreach (array_keys(ErrorCodes::CLUE_TYPE_CODES) as $type) {
            if (($types[$type] ?? 0) !== 1) {
                $out[] = self::problem('error', $ref, $group, null, "Setiap unggahan memuat keempat jenis petunjuk tepat sekali; jenis {$type} ada " . ($types[$type] ?? 0) . ' kali.');
            }
        }

        return $out;
    }

    /**
     * Skenario Digital, one variant (data of 31 K4; the engine is part 6's).
     *
     * @param list<array<string, mixed>> $items
     */
    private static function scenarios(array $node, string $group, array $items): array
    {
        $ref  = (string) $node['ref'];
        $rule = DifficultyRules::scenario((string) ($node['difficulty'] ?? ''));
        $out  = [];

        if (count($items) !== $rule['count']) {
            $out[] = self::problem('error', $ref, $group, null, 'Variasi ini punya ' . count($items) . " skenario; aturan tepat {$rule['count']}.");
        }

        foreach ($items as $item) {
            $key      = (string) $item['key'];
            $scenario = $node['scenarios'][$key] ?? null;

            if ($scenario === null) {
                $out[] = self::problem('error', $ref, $group, $key, 'Skenario ini belum punya baris di sheet scenarios dan scenario_options.');

                continue;
            }

            $topic = (string) ($item['topic'] ?? '');

            if (! in_array($topic, ErrorCodes::D2_TOPICS, true)) {
                $out[] = self::problem('error', $ref, $group, $key, 'Topik skenario (d2_topic) wajib ' . implode(', ', ErrorCodes::D2_TOPICS) . '.');
            } elseif ($scenario['topic'] !== '' && $scenario['topic'] !== $topic) {
                $out[] = self::problem('error', $ref, $group, $key, "Topik di sheet scenarios ({$scenario['topic']}) berbeda dengan sheet items ({$topic}).");
            }

            if ((int) $scenario['steps'] !== $rule['steps']) {
                $out[] = self::problem('error', $ref, $group, $key, "Skenario {$scenario['steps']} langkah; aturan wilayah ini {$rule['steps']}.");
            }

            if (trim((string) $scenario['explanation']) === '') {
                $out[] = self::problem('error', $ref, $group, $key, 'Penjelasan setelah kesempatan habis (explanation_id) wajib diisi.');
            }

            $actions = (array) $scenario['actions'];
            $reasons = (array) $scenario['reasons'];

            if (count($actions) !== $rule['actions'] || count($reasons) !== $rule['reasons']) {
                $out[] = self::problem('error', $ref, $group, $key, sprintf('Skenario punya %d tindakan dan %d alasan; aturan wilayah ini %d dan %d.', count($actions), count($reasons), $rule['actions'], $rule['reasons']));
            }

            $scores = array_count_values(array_map(static fn (array $a): string => (string) ($a['score'] ?? ''), $actions));

            if (($scores['2'] ?? 0) !== 1 || ($scores['1'] ?? 0) !== 1 || ($scores['0'] ?? 0) + 2 !== count($actions)) {
                $out[] = self::problem('error', $ref, $group, $key, 'Setiap skenario punya tepat satu tindakan skor 2, tepat satu skor 1, dan sisanya skor 0.');
            }

            foreach ($actions as $action) {
                $code = (string) ($action['code'] ?? '');

                if ((int) ($action['score'] ?? 0) === 2) {
                    if ($code !== '') {
                        $out[] = self::problem('error', $ref, $group, $key, "Tindakan tepat ({$action['key']}) tidak diberi kode kesalahan.");
                    }
                } elseif (! ErrorCodes::allowedFor('action', $code)) {
                    $out[] = self::problem('error', $ref, $group, $key, "Tindakan {$action['key']} berskor " . (int) ($action['score'] ?? 0) . ' wajib punya kode DIG-* (' . implode(', ', ErrorCodes::BY_CHOICE['action']) . ').');
                }
            }

            $codes = [];

            foreach ($reasons as $reason) {
                $code = (string) ($reason['code'] ?? '');

                if (! ErrorCodes::allowedFor('reason', $code)) {
                    $out[] = self::problem('error', $ref, $group, $key, "Alasan {$reason['key']} wajib punya kode R-* (" . implode(', ', ErrorCodes::BY_CHOICE['reason']) . ').');
                }

                $codes[$code] = true;
            }

            $setOk = $rule['all_reasons']
                ? array_diff(ErrorCodes::BY_CHOICE['reason'], array_keys($codes)) === []
                : (isset($codes['R-RISK']) || isset($codes['R-ETIK'])) && isset($codes['R-ATUR']) && (isset($codes['R-SOS']) || isset($codes['R-ACAK']));

            if (! $setOk) {
                $out[] = self::problem('error', $ref, $group, $key, $rule['all_reasons']
                    ? 'Alasan di wilayah ini wajib memuat kelima kode R-*.'
                    : 'Alasan wajib memuat R-RISK atau R-ETIK, R-ATUR, dan R-SOS atau R-ACAK.');
            }
        }

        return $out;
    }

    // ------------------------------------------------- codes and geometry

    /**
     * Exactly one code per wrong choice of one item, of the kind that fits.
     *
     * @param list<string> $wrong every wrong choice ref
     */
    private static function codeMatrix(array $node, string $group, string $key, string $type, array $wrong, string $kind): array
    {
        $ref   = (string) $node['ref'];
        $codes = array_values(array_filter((array) ($node['codes'][$key] ?? []), static fn (array $c): bool => $c['type'] === $type));
        $out   = [];
        $seen  = [];

        foreach ($codes as $code) {
            if (isset($seen[$code['ref']])) {
                $out[] = self::problem('error', $ref, $group, $key, "Pilihan '{$code['ref']}' punya lebih dari satu kode kesalahan.");
            }

            $seen[$code['ref']] = true;

            if (! in_array($code['ref'], $wrong, true)) {
                $out[] = self::problem('error', $ref, $group, $key, "Ada kode kesalahan untuk '{$code['ref']}', padahal itu bukan pilihan salah soal ini.");
            } elseif (ErrorCodes::forbiddenInBank($code['code'])) {
                $out[] = self::problem('error', $ref, $group, $key, "Kode '{$code['code']}' untuk '{$code['ref']}' tidak boleh ada di bank (kode tak dikenal, *-LAIN, atau NON-*).");
            } elseif (! ErrorCodes::allowedFor($kind, $code['code'])) {
                $out[] = self::problem('error', $ref, $group, $key, "Kode {$code['code']} untuk '{$code['ref']}' tidak cocok dengan jenis pilihannya (" . implode(', ', ErrorCodes::BY_CHOICE[$kind]) . ').');
            }
        }

        $missing = array_values(array_diff($wrong, array_keys($seen)));

        if ($missing !== []) {
            $out[] = self::problem('error', $ref, $group, $key, 'Pilihan salah tanpa kode kesalahan: ' . implode(', ', $missing) . '.');
        }

        return $out;
    }

    /**
     * Tap areas inside the scene, not overlapping, at least 24 dp apart (K9).
     *
     * @param array<string, array{0: float, 1: float, 2: float, 3: float}> $boxes key → box in percent
     */
    private static function tapAreas(string $ref, string $group, array $boxes, string $noun): array
    {
        $out  = [];
        $keys = array_map('strval', array_keys($boxes));

        foreach ($keys as $i => $a) {
            if (! HuntGeometry::inside($boxes[$a])) {
                $out[] = self::problem('error', $ref, $group, $a, sprintf(
                    '%s %s keluar dari adegan: x %s, y %s, lebar %s, tinggi %s (semuanya harus di dalam 0–100 %%).',
                    $noun,
                    $a,
                    self::fmt($boxes[$a][0]),
                    self::fmt($boxes[$a][1]),
                    self::fmt($boxes[$a][2]),
                    self::fmt($boxes[$a][3]),
                ));
            }

            foreach (array_slice($keys, $i + 1) as $b) {
                $gap = HuntGeometry::gapDp($boxes[$a], $boxes[$b]);

                if (HuntGeometry::touching($boxes[$a], $boxes[$b])) {
                    $out[] = self::problem('error', $ref, $group, $a, $gap <= 0
                        ? sprintf('%s %s dan %s bertumpuk (paling sedikit %d dp).', $noun, $a, $b, self::MIN_GAP_DP)
                        : sprintf('%s %s dan %s hanya berjarak %s dp (paling sedikit %d dp).', $noun, $a, $b, HuntGeometry::fmtDp($gap), self::MIN_GAP_DP));
                }
            }
        }

        return $out;
    }

    // ------------------------------------------------------ equivalence

    /** Variants a and b are equivalent (06 rule no. 6): warnings only. */
    private static function equivalence(array $node, array $passages): array
    {
        $ref = (string) $node['ref'];
        $a   = (array) ($node['variants']['a']['items'] ?? []);
        $b   = (array) ($node['variants']['b']['items'] ?? []);

        if ($a === [] || $b === [] || $node['engine'] === 'puzzle') {
            return [];
        }

        $out     = [];
        $profile = static function (array $items) use ($node): array {
            $subs  = [];
            $akm   = [];
            $codes = [];

            foreach ($items as $item) {
                $subs[] = (string) ($item['sub'] ?? '');
                $akm[]  = (string) ($item['akm'] ?? '');

                if (($item['error_code'] ?? '') !== '') {
                    $codes[] = $item['error_code'];
                }

                foreach ((array) ($node['codes'][$item['key']] ?? []) as $code) {
                    $codes[] = $code['code'];
                }

                foreach ((array) ($node['scenarios'][$item['key']]['actions'] ?? []) as $action) {
                    if (($action['code'] ?? '') !== '') {
                        $codes[] = $action['code'];
                    }
                }
            }

            sort($subs);
            sort($akm);
            $codes = array_values(array_unique($codes));
            sort($codes);

            return ['subs' => $subs, 'akm' => $akm, 'codes' => $codes];
        };

        $pa = $profile($a);
        $pb = $profile($b);

        if ($pa['subs'] !== $pb['subs'] || $pa['akm'] !== $pb['akm']) {
            $out[] = self::problem('warning', $ref, null, null, 'Variasi a dan b belum setara: jumlah per sub-aspek atau level AKM berbeda.');
        }

        if ($pa['codes'] !== $pb['codes']) {
            $out[] = self::problem('warning', $ref, null, null, 'Variasi a dan b belum setara: sebaran kode kesalahan berbeda (a: ' . implode(', ', $pa['codes']) . '; b: ' . implode(', ', $pb['codes']) . ').');
        }

        $text = static function (array $items) use ($node, $passages): array {
            if ($node['engine'] === 'boleh') {
                $passage = $passages[(string) ($items[0]['passage'] ?? '')] ?? [];

                return [(string) ($passage['body_id'] ?? ''), (string) ($passage['genre'] ?? '')];
            }

            if ($node['engine'] === 'rumpang') {
                return [implode(' ', array_map(static fn (array $i): string => (string) $i['prompt_id'], $items)), ''];
            }

            return ['', ''];
        };

        [$ta, $ga] = $text($a);
        [$tb, $gb] = $text($b);
        $la        = SurfaceLoad::words($ta);
        $lb        = SurfaceLoad::words($tb);

        if ($la > 0 && $lb > 0 && abs($la - $lb) / max($la, $lb) > self::MAX_LENGTH_GAP) {
            $out[] = self::problem('warning', $ref, null, null, sprintf('Panjang bacaan variasi a (%d kata) dan b (%d kata) berselisih lebih dari %d %%.', $la, $lb, (int) (self::MAX_LENGTH_GAP * 100)));
        }

        if ($ga !== $gb) {
            $out[] = self::problem('warning', $ref, null, null, "Genre teks variasi a ({$ga}) dan b ({$gb}) berbeda.");
        }

        return $out;
    }

    /**
     * Texts of the challenge: addresses only `.example`, phone numbers only
     * as `08xx-xxxx-xxxx` (06 rule no. 10); English equal to Indonesian, or
     * still marked "terjemahan belum ada", is a warning (31 §6.4).
     */
    private static function textRules(array $node, array $passages): array
    {
        $ref   = (string) $node['ref'];
        $out   = [];
        $texts = [];
        $todo  = 0;

        foreach (self::activeItems($node) as $item) {
            $texts[(string) $item['key']] = [(string) $item['prompt_id'], (string) $item['prompt_en']];

            if (! empty($item['passage']) && isset($passages[$item['passage']])) {
                $texts[(string) $item['passage']] = [(string) $passages[$item['passage']]['body_id'], (string) $passages[$item['passage']]['body_en']];
            }

            if (str_contains(mb_strtolower((string) ($item['review_note'] ?? '')), 'terjemahan belum ada')) {
                $todo++;
            }
        }

        foreach ((array) ($node['posts'] ?? []) as $group => $post) {
            foreach ((array) $post['parts'] as $part) {
                $texts[$ref . '-' . $group . ' ' . $part['key']] = [(string) $part['text_id'], (string) $part['text_en']];
            }
        }

        foreach ((array) ($node['scenarios'] ?? []) as $key => $scenario) {
            foreach ([...(array) $scenario['actions'], ...(array) $scenario['reasons']] as $option) {
                $texts[$key . ' ' . $option['key']] = [(string) $option['text_id'], (string) $option['text_en']];
            }
        }

        foreach ($texts as $where => [$id, $en]) {
            foreach ([$id, $en] as $text) {
                foreach (self::badAddresses($text) as $bad) {
                    $out[] = self::problem('error', $ref, null, (string) $where, "Alamat '{$bad}' bukan domain .example (06 aturan no. 10).");
                }

                foreach (self::badPhones($text) as $bad) {
                    $out[] = self::problem('error', $ref, null, (string) $where, "Nomor telepon '{$bad}' bukan bentuk samaran 08xx-xxxx-xxxx.");
                }
            }

            // Proper names (Jumprit, Sumbing) and short labels are the same in both languages
            if (SurfaceLoad::words($id) > 3 && trim($id) === trim($en)) {
                $out[] = self::problem('warning', $ref, null, (string) $where, 'Teks bahasa Inggris sama dengan teks Indonesia (terjemahan belum ada).');
            }
        }

        if ($todo > 0) {
            $out[] = self::problem('warning', $ref, null, null, "{$todo} butir masih bercatatan \"terjemahan belum ada\": teks Inggrisnya belum diperiksa pengembang.");
        }

        return $out;
    }

    // ------------------------------------------------------------ helpers

    /** @return list<array<string, mixed>> */
    private static function activeItems(array $node): array
    {
        $out = [];

        foreach ((array) ($node['variants'] ?? []) as $variant) {
            $out = [...$out, ...(array) ($variant['items'] ?? [])];
        }

        return $out;
    }

    /**
     * Words a Susun Gambar caption must not contain: Isi Rumpang keys and
     * round-1 object names (simple word matching, 08 → Susun Gambar selama
     * studi; the final call stays a manual review).
     *
     * @return list<string>
     */
    private static function keywords(array $node): array
    {
        $out = [];

        foreach (self::activeItems($node) as $item) {
            if ($item['type'] === 'fill_blank_bank') {
                $word = mb_strtolower(trim((string) $item['answer_id']));

                if (mb_strlen($word) >= 5) {
                    $out[] = $word;
                }
            }

            if ($item['type'] === 'find_object' && empty($item['decoy']) && trim((string) ($item['media_label'] ?? '')) !== '') {
                $out[] = mb_strtolower(trim((string) $item['media_label']));
            }
        }

        return $out;
    }

    /** "2 D1a + 1 D1b" */
    private static function countLabel(array $counts): string
    {
        return $counts === [] ? '—' : implode(' + ', array_map(static fn ($k, $v): string => $v . ' ' . ($k === '' ? '(kosong)' : $k), array_keys($counts), $counts));
    }

    private static function fmt(float $value): string
    {
        return rtrim(rtrim(number_format($value, 1, '.', ''), '0'), '.');
    }

    /**
     * @param list<array<string, mixed>> $problems
     *
     * @return list<array<string, mixed>>
     */
    private static function unique(array $problems): array
    {
        $out = [];

        foreach ($problems as $problem) {
            $out[json_encode($problem)] = $problem;
        }

        return array_values($out);
    }

    /** @return array{level: string, node: string, variant: ?string, item: ?string, message: string} */
    private static function problem(string $level, string $node, ?string $variant, ?string $item, string $message): array
    {
        return ['level' => $level, 'node' => $node, 'variant' => $variant, 'item' => $item, 'message' => $message];
    }

    /**
     * One finding as a CLI line: "tmg-2 · variasi a · tmg-2-a01: pesan".
     *
     * @param array{node: string, variant: ?string, item: ?string, message: string} $problem
     */
    public static function format(array $problem): string
    {
        $scope = array_filter([
            $problem['node'] ?? '',
            ($problem['variant'] ?? null) !== null ? 'variasi ' . $problem['variant'] : '',
            $problem['item'] ?? '',
        ], static fn ($part): bool => $part !== '' && $part !== null);

        return implode(' · ', $scope) . ': ' . $problem['message'];
    }
}
