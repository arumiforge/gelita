<?php

namespace App\Libraries;

/**
 * Data files of the grade 5 research bank (docs/bank-soal/data/{wilayah}.php,
 * shape of 31 §6.4) → rows of the import workbook, and workbook rows → the
 * node shape BankRules checks.
 *
 * Used by docs/bank-soal/build-workbook.php, ContentImportService (rows →
 * nodes), and the bank tests, so the builder and the importer check the same
 * thing in the same way. Pure (no framework), like DifficultyRules.
 *
 * What the builder derives (31 K6, §7.2), so the data files stay short:
 * - item codes `{node}-{variant}{nn}` when a puzzle item has no `key`;
 * - `sequence` = play position in the challenge;
 * - `testlet_key`: reading `{node}-{variant}`, anchor card `{node}-x`, round 1
 *   `{node}-x`, round-2 post `{node}-{variant}`, scenario = its item key;
 * - the full error-code matrices `cloze_matrix`, `object_codes`, and
 *   `part_codes` from the three fixed rules of 08 → Cara membaca;
 * - scenario option keys A1… (actions) and R1… (reasons).
 */
final class BankSource
{
    /** Wilayah → awalan kode tantangan (sama dengan Config\Gelita::$levelPrefixes). */
    public const REGIONS = ['temanggung' => 'tmg', 'magelang' => 'mgl', 'wonosobo' => 'wnb'];

    /** Default review of an item without its own `review` (31 §7.3: the new bank stays needs_verification). */
    public const REVIEW_STATUS = 'needs_verification';

    public const REVIEW_NOTE = 'Bank baru dari 08 (revisi 10): menunggu validasi ahli.';

    /** Retired items get sequence 91, 92, … so they sort after every active item. */
    public const RETIRED_SEQUENCE = 90;

    /** Ukuran & format gambar per jenis slot (sheet DAFTAR_MEDIA). */
    public const MEDIA_SIZES = [
        'puzzle' => 'persegi tepat 1200 × 1200 px, WebP, latar penuh (tidak transparan)',
        'scene'  => '1280 × 720 px (16:9), JPG/WebP',
        'object' => '240 × 240 px, PNG/WebP berlatar transparan',
        'card'   => '480 × 360 px, JPG/WebP',
        'post'   => 'foto di dalam unggahan, 960 × 720 px (4:3) atau lebih besar, JPG/WebP, tanpa tulisan',
    ];

    /** Interaction type → engine, for nodes rebuilt from workbook rows. */
    public const ENGINE_OF_TYPE = [
        'puzzle_arrange'  => 'puzzle',
        'fill_blank_bank' => 'rumpang',
        'verdict_card'    => 'boleh',
        'find_object'     => 'cari',
        'find_part'       => 'cari',
        'scenario_choice' => 'skenario',
    ];

    /**
     * The five dimension indicators (sheet `indicators`, table
     * `learning_indicators`; 06 → Pemetaan dimensi ke kerangka, 31 §13.1):
     * code → [name id, name en, description id, description en, domain].
     */
    public const INDICATORS = [
        'D1a' => [
            'Memahami informasi', 'Understanding information',
            'Menemukan, menafsirkan, dan memadukan informasi dalam teks digital. DigComp 3.0 area 1, kompetensi 1.1; pilar Cakap Bermedia Digital (Komdigi); AKM L1–L2; PIRLS 2026 retrieve, infer, integrate.',
            'Finding, interpreting, and integrating information in digital texts. DigComp 3.0 area 1, competence 1.1; Komdigi pillar Digital Skills; AKM L1–L2; PIRLS 2026 retrieve, infer, integrate.',
            'literasi',
        ],
        'D1b' => [
            'Menilai informasi dan sumber', 'Evaluating information and sources',
            'Menilai isi dan sumber teks digital: penanda sumber, fakta dan pendapat, iklan, kredibilitas. DigComp 3.0 kompetensi 1.2; pilar Cakap Bermedia Digital; AKM L3 (L1 untuk penanda sumber di Temanggung); PIRLS 2026 evaluate.',
            'Evaluating the content and source of digital texts: source markers, fact and opinion, ads, credibility. DigComp 3.0 competence 1.2; Komdigi pillar Digital Skills; AKM L3 (L1 for source markers in Temanggung); PIRLS 2026 evaluate.',
            'literasi',
        ],
        'D2' => [
            'Aman dan etis', 'Safe and ethical',
            'Melindungi data pribadi dan kata sandi, meminta izin, menanggapi orang asing dan kabar bohong dengan aman. DigComp 3.0 kompetensi 4.2, 4.3, 2.2; pilar Aman dan Etis Bermedia Digital; padanan AKM dari kerumitan skenario.',
            'Protecting personal data and passwords, asking permission, responding safely to strangers and false news. DigComp 3.0 competences 4.2, 4.3, 2.2; Komdigi pillars Digital Safety and Ethics; AKM equivalent from scenario complexity.',
            'digital',
        ],
        'D3' => [
            'Partisipasi dan representasi budaya', 'Cultural participation and representation',
            'Menghargai pembuat karya budaya, membetulkan keterangan budaya yang keliru, meminta izin, dan menanggapi tradisi dengan hormat. DigComp 3.0 kompetensi 2.2, 2.3, 2.5, 3.3; pilar Budaya Bermedia Digital; padanan AKM dari kerumitan adegan.',
            'Crediting the makers of cultural works, correcting wrong cultural information, asking permission, and responding to tradition with respect. DigComp 3.0 competences 2.2, 2.3, 2.5, 3.3; Komdigi pillar Digital Culture; AKM equivalent from scene complexity.',
            'budaya',
        ],
        'PBK' => [
            'Pengetahuan budaya Kedu (pendamping)', 'Kedu cultural knowledge (companion)',
            'Mengenali benda budaya khas wilayah Kedu (Cari Objek babak 1). Pendamping, bukan dimensi literasi digital.',
            'Recognising typical cultural objects of the Kedu area (Cari Objek round 1). A companion measure, not a digital literacy dimension.',
            'budaya',
        ],
    ];

    /** Sheets a bank workbook carries, in writing order. */
    public const BANK_SHEETS = [
        'nodes', 'variants', 'distractors', 'passages', 'items', 'hints', 'cloze_matrix', 'object_codes',
        'post_scenes', 'post_parts', 'part_codes', 'scenarios', 'scenario_options', 'indicators',
    ];

    /**
     * @return array<string, array<string, mixed>> wilayah → isi berkas data
     */
    public static function load(string $dir): array
    {
        $out = [];

        foreach (array_keys(self::REGIONS) as $region) {
            $out[$region] = (static fn (string $path): array => require $path)(rtrim($dir, '/') . '/' . $region . '.php');
        }

        return $out;
    }

    /**
     * Flatten every region into workbook rows.
     *
     * @param array<string, array<string, mixed>> $regions hasil load()
     *
     * @return array{
     *     sheets: array<string, list<array<string, string>>>,
     *     difficulty: array<string, string>,
     *     nodes: array<string, array<string, mixed>>,
     *     passages: array<string, array<string, string>>,
     *     regions: array<string, list<array{sequence: int, engine: string}>>,
     *     media: array<string, array<string, mixed>>,
     *     keys: list<string>,
     *     problems: list<array{level: string, node: string, variant: ?string, item: ?string, message: string}>
     * }
     */
    public static function flatten(array $regions): array
    {
        $sheets     = array_fill_keys(self::BANK_SHEETS, []);
        $media      = [];
        $keys       = [];
        $problems   = [];
        $difficulty = [];

        foreach (self::INDICATORS as $code => [$nameId, $nameEn, $descId, $descEn, $domain]) {
            $sheets['indicators'][] = [
                'code' => $code, 'name_id' => $nameId, 'name_en' => $nameEn,
                'description_id' => $descId, 'description_en' => $descEn, 'domain' => $domain,
            ];
        }

        foreach ($regions as $region => $data) {
            $difficulty[self::REGIONS[$region] ?? $region] = DifficultyRules::normalize((string) ($data['difficulty'] ?? ''));

            foreach ((array) $data['nodes'] as $ref => $node) {
                $ref    = (string) $ref;
                $engine = (string) $node['engine'];
                $ctx    = new BankRowContext($ref, $region, $engine, $node);

                $sheets['nodes'][] = [
                    'node_ref'             => $ref,
                    'title_id'             => self::bi($node['title'] ?? null, 0),
                    'title_en'             => self::bi($node['title'] ?? null, 1),
                    'instruction_id'       => self::bi($node['instruction'] ?? null, 0),
                    'instruction_en'       => self::bi($node['instruction'] ?? null, 1),
                    'description_id'       => self::bi($node['description'] ?? null, 0),
                    'description_en'       => self::bi($node['description'] ?? null, 1),
                    'distractor_count'     => $engine === 'rumpang' ? (string) (int) ($node['distractor_count'] ?? 0) : '',
                    'scene_media_key'      => (string) ($node['scene']['key'] ?? ''),
                    'scene_media_label'    => (string) ($node['scene']['label'] ?? ''),
                    'scene_media_note'     => (string) ($node['scene']['note'] ?? ''),
                    'background_media_key' => '',
                    'variant_code'         => (string) ($node['variant'] ?? ''),
                    'indicator'            => (string) ($node['indicator'] ?? ''),
                ];

                if (isset($node['scene']['key'])) {
                    self::addMedia($media, $node['scene'], 'scene', $region, $ref, $ref . ' (adegan)');
                }

                foreach ((array) ($node['hints'] ?? []) as $index => $hint) {
                    $sheets['hints'][] = ['node_ref' => $ref, 'item_key' => '', 'sequence' => (string) ($index + 1), 'text_id' => self::bi($hint, 0), 'text_en' => self::bi($hint, 1)];
                }

                // Groups in play order: variants and then the anchor card
                // (boleh), round 1 first (cari), scenarios per variant (skenario)
                $groups = match ($engine) {
                    'cari'     => array_filter(['x' => $node['round1'] ?? null]) + (array) ($node['round2'] ?? []),
                    'skenario' => array_map(static fn (array $list): array => ['items' => $list], (array) ($node['scenarios'] ?? [])),
                    default    => (array) ($node['variants'] ?? []),
                };

                if ($engine === 'boleh' && isset($node['anchor'])) {
                    $groups['x'] = $node['anchor'];
                }

                foreach ($groups as $group => $variant) {
                    self::flattenVariant($sheets, $media, $keys, $problems, $ctx, (string) $group, (array) $variant);
                }
            }
        }

        ksort($media);
        $normal = self::nodesFromSheets($sheets, $difficulty, static fn (array $row): bool => trim((string) $row['media_asset_key']) !== '');

        return [
            'sheets'     => $sheets,
            'difficulty' => $difficulty,
            'nodes'      => $normal['nodes'],
            'passages'   => $normal['passages'],
            'regions'    => $normal['regions'],
            'media'      => $media,
            'keys'       => $keys,
            'problems'   => $problems,
        ];
    }

    /**
     * Every check of 31 §8.2 on a flatten() result: per challenge, per
     * region, across regions, and unique item codes.
     *
     * @param array<string, mixed> $flat
     *
     * @return list<array{level: string, node: string, variant: ?string, item: ?string, message: string}>
     */
    public static function problems(array $flat): array
    {
        $out = $flat['problems'] ?? [];

        foreach ($flat['regions'] as $region => $layout) {
            $out = [...$out, ...BankRules::region($region, $layout)];
        }

        foreach ($flat['nodes'] as $node) {
            $out = [...$out, ...BankRules::node($node, $flat['passages'])];
        }

        $out = [...$out, ...BankRules::bank($flat['nodes'], $flat['passages'])];

        foreach (BankRules::duplicateKeys($flat['keys']) as $key) {
            $out[] = self::problem('error', implode('-', array_slice(explode('-', $key), 0, 2)), null, $key, 'Kode soal ini dipakai lebih dari sekali.');
        }

        return $out;
    }

    // ------------------------------------------------------- data → rows

    /**
     * @param array<string, list<array<string, string>>> $sheets
     * @param array<string, mixed>                       $variant
     */
    private static function flattenVariant(array &$sheets, array &$media, array &$keys, array &$problems, BankRowContext $ctx, string $group, array $variant): void
    {
        $ref        = $ctx->ref;
        $engine     = $ctx->engine;
        $passageKey = null;
        $title      = $variant['title'] ?? null;

        if (isset($variant['passage'])) {
            $passageKey = $ref . '-' . $group;
            $passage    = (array) $variant['passage'];
            $title      = self::bi($passage['title'] ?? null, 0) !== '' ? $passage['title'] : $title;

            $sheets['passages'][] = [
                'passage_key'      => $passageKey,
                'level_code'       => $ctx->region,
                'title_id'         => self::bi($passage['title'] ?? null, 0),
                'title_en'         => self::bi($passage['title'] ?? null, 1),
                'body_id'          => self::bi($passage['body'] ?? null, 0),
                'body_en'          => self::bi($passage['body'] ?? null, 1),
                'media_asset_key'  => '',
                'reference_source' => (string) ($passage['ref'] ?? $variant['ref'] ?? $ctx->node['ref'] ?? ''),
                'text_genre'       => (string) ($passage['genre'] ?? ''),
                'layout'           => (string) ($passage['layout'] ?? ''),
            ];
        }

        if (isset($variant['scene'])) {
            $scene = (array) $variant['scene'];
            $title = $scene['title'] ?? $title;

            $sheets['post_scenes'][] = [
                'node_ref'         => $ref,
                'variant_group'    => $group,
                'title_id'         => self::bi($scene['title'] ?? null, 0),
                'title_en'         => self::bi($scene['title'] ?? null, 1),
                'text_genre'       => (string) ($scene['genre'] ?? ''),
                'media_asset_key'  => (string) ($scene['media']['key'] ?? ''),
                'media_label'      => (string) ($scene['media']['label'] ?? ''),
                'media_note'       => (string) ($scene['media']['note'] ?? ''),
                'reference_source' => (string) ($scene['ref'] ?? ''),
            ];

            if (isset($scene['media']['key'])) {
                self::addMedia($media, $scene['media'], 'post', $ctx->region, $ref, $ref . '-' . $group . ' (unggahan)');
            }

            foreach ((array) ($variant['parts'] ?? []) as $part) {
                $sheets['post_parts'][] = [
                    'node_ref'      => $ref,
                    'variant_group' => $group,
                    'part_key'      => (string) $part['key'],
                    'kind'          => (string) $part['kind'],
                    'label_id'      => self::bi($part['label'] ?? null, 0),
                    'label_en'      => self::bi($part['label'] ?? null, 1),
                    'text_id'       => self::bi($part['text'] ?? null, 0),
                    'text_en'       => self::bi($part['text'] ?? null, 1),
                    'x'             => self::number($part['x'] ?? 0),
                    'y'             => self::number($part['y'] ?? 0),
                    'w'             => self::number($part['w'] ?? 0),
                    'h'             => self::number($part['h'] ?? 0),
                ];
            }
        }

        // The anchor card has no title of its own: the game shows "Bacaan kedua"
        if (! ($engine === 'boleh' && $group === 'x') && $engine !== 'skenario') {
            $sheets['variants'][] = ['node_ref' => $ref, 'variant_group' => $group, 'title_id' => self::bi($title, 0), 'title_en' => self::bi($title, 1)];
        }

        foreach ((array) ($variant['distractors'] ?? []) as $distractor) {
            $sheets['distractors'][] = ['node_ref' => $ref, 'variant_group' => $group, 'text_id' => self::bi($distractor, 0), 'text_en' => self::bi($distractor, 1)];
        }

        $round  = $engine === 'cari' ? ($group === 'x' ? '1' : '2') : '';
        $rowsOf = [];

        foreach (array_values((array) ($variant['items'] ?? [])) as $index => $item) {
            $active   = ($item['active'] ?? true) !== false;
            $sequence = $active ? $ctx->nextSequence() : self::RETIRED_SEQUENCE + $ctx->nextRetired();
            $key      = isset($item['key']) ? (string) $item['key'] : sprintf('%s-%s%02d', $ref, $group, $index + 1);
            $type     = (string) ($item['type'] ?? ($engine === 'skenario' ? 'scenario_choice' : ''));
            $decoy    = ! empty($item['decoy']);
            $plainRow = $decoy || $type === 'puzzle_arrange';
            $keys[]   = $key;

            if (! str_starts_with($key, $ref . '-' . $group)) {
                $problems[] = self::problem('error', $ref, $group, $key, "Kode soal harus diawali {$ref}-{$group}.");
            }

            $answerId = match ($type) {
                'fill_blank_bank' => self::bi($item['answer'] ?? null, 0),
                'verdict_card'    => mb_strtolower(trim((string) ($item['answer'] ?? ''))),
                'find_object'     => $decoy ? 'decoy' : 'target',
                'find_part'       => (string) ($item['answer'] ?? ''),
                default           => '',
            };
            $prompt = $type === 'scenario_choice' ? ($item['situation'] ?? null) : ($item['prompt'] ?? null);

            $row = [
                'item_key'          => $key,
                'node_ref'          => $ref,
                'variant_group'     => $group,
                'sequence'          => (string) $sequence,
                'interaction_type'  => $type,
                'indicator'         => $plainRow ? '' : (string) ($item['sub'] ?? $item['dim'] ?? ''),
                'bloom_level'       => (string) ($item['bloom'] ?? ''),
                'skill'             => (string) ($item['skill'] ?? ''),
                'prompt_id'         => self::bi($prompt, 0),
                'prompt_en'         => self::bi($prompt, 1),
                'passage_key'       => $passageKey ?? '',
                'answer_id'         => $answerId,
                'answer_en'         => $type === 'fill_blank_bank' ? self::bi($item['answer'] ?? null, 1) : '',
                'x'                 => isset($item['x']) ? self::number($item['x']) : '',
                'y'                 => isset($item['y']) ? self::number($item['y']) : '',
                'w'                 => isset($item['w']) ? self::number($item['w']) : '',
                'decoy'             => $type === 'find_object' ? ($decoy ? '1' : '0') : '',
                'wrong_feedback_id' => self::bi($item['wrong'] ?? null, 0),
                'wrong_feedback_en' => self::bi($item['wrong'] ?? null, 1),
                'media_asset_key'   => (string) ($item['media']['key'] ?? ''),
                'media_label'       => (string) ($item['media']['label'] ?? ''),
                'media_note'        => (string) ($item['media']['note'] ?? ''),
                'scorable'          => $decoy ? '0' : '1',
                'is_active'         => $active ? '1' : '0',
                'review_status'     => (string) ($item['review']['status'] ?? self::REVIEW_STATUS),
                'review_note'       => (string) ($item['review']['note'] ?? self::REVIEW_NOTE),
                'reference_source'  => (string) ($item['ref'] ?? $variant['ref'] ?? $variant['scene']['ref'] ?? $variant['passage']['ref'] ?? $ctx->node['ref'] ?? ''),
                'round'             => $round,
                'dimension'         => $decoy ? '' : (string) ($item['dim'] ?? ''),
                'subaspect'         => (string) ($item['sub'] ?? ''),
                'akm_level'         => (string) ($item['akm'] ?? ''),
                'pirls_process'     => (string) ($item['pirls'] ?? ''),
                'label_source'      => $plainRow ? '' : self::labelSource($item['labels'] ?? null),
                'testlet_key'       => $plainRow ? '' : ($type === 'scenario_choice' ? $key : $ref . '-' . $group),
                'error_code'        => (string) ($item['code'] ?? ''),
                'd3_clue_type'      => (string) ($item['clue_type'] ?? ''),
                'd2_topic'          => (string) ($item['topic'] ?? ''),
            ];
            $sheets['items'][] = $row;

            if (isset($item['media']['key'])) {
                self::addMedia($media, $item['media'], match ($engine) {
                    'puzzle' => 'puzzle',
                    'cari'   => 'object',
                    default  => 'card',
                }, $ctx->region, $ref, $key . ($decoy ? ' (pengecoh)' : ''));
            }

            if ($type === 'scenario_choice') {
                self::flattenScenario($sheets, $key, $item);
            }

            if ($active) {
                $rowsOf[] = ['row' => $row, 'item' => $item];
            }
        }

        if ($engine === 'rumpang') {
            self::clozeMatrix($sheets, $problems, $ref, $group, $rowsOf, (array) ($variant['distractors'] ?? []), (array) ($variant['special'] ?? []));
        } elseif ($engine === 'cari' && $group === 'x') {
            self::objectCodes($sheets, $rowsOf);
        } elseif ($engine === 'cari') {
            self::partCodes($sheets, $problems, $ref, $group, $rowsOf, (array) ($variant['parts'] ?? []));
        }
    }

    /**
     * Cloze matrix (08 → Cara membaca, K6): every blank × every wrong word.
     * 1. another blank's answer → INF-REL; 2. a decoy at its target blank →
     * the `trap` code; 3. a decoy at another blank → INF-LIT. `special`
     * (word → item key → code) overrides any of them.
     *
     * @param list<array{row: array<string, string>, item: array<string, mixed>}> $rows
     */
    private static function clozeMatrix(array &$sheets, array &$problems, string $ref, string $group, array $rows, array $distractors, array $special): void
    {
        $answers = array_map(static fn (array $r): string => $r['row']['answer_id'], $rows);
        $decoys  = array_map(static fn ($d): string => self::bi($d, 0), $distractors);
        $isDecoy = array_flip(array_map(self::wordRef(...), $decoys));
        $special = array_change_key_case($special, CASE_LOWER);

        foreach ($rows as $r) {
            $key   = $r['row']['item_key'];
            $traps = array_change_key_case((array) ($r['item']['trap'] ?? []), CASE_LOWER);

            foreach (array_keys($traps) as $word) {
                if (! isset($isDecoy[$word])) {
                    $problems[] = self::problem('error', $ref, $group, $key, "Kata jebakan '{$word}' bukan kata pengecoh variasi ini.");
                }
            }

            foreach ([...$answers, ...$decoys] as $word) {
                if ($word === $r['row']['answer_id']) {
                    continue;
                }

                $lower = self::wordRef($word);
                $code  = $special[$lower][$key] ?? (isset($isDecoy[$lower]) ? ($traps[$lower] ?? 'INF-LIT') : 'INF-REL');

                $sheets['cloze_matrix'][] = ['node_ref' => $ref, 'variant_group' => $group, 'item_key' => $key, 'word_id' => $word, 'error_code' => $code];
            }
        }
    }

    /**
     * Round-1 codes: every clue × every other object; a decoy carries its own
     * code, another clue's target is INF-LIT (31 §6.3).
     *
     * @param list<array{row: array<string, string>, item: array<string, mixed>}> $rows
     */
    private static function objectCodes(array &$sheets, array $rows): void
    {
        foreach ($rows as $target) {
            if ($target['row']['decoy'] === '1') {
                continue;
            }

            foreach ($rows as $other) {
                if ($other['row']['item_key'] === $target['row']['item_key']) {
                    continue;
                }

                $sheets['object_codes'][] = [
                    'item_key'   => $target['row']['item_key'],
                    'object_key' => $other['row']['item_key'],
                    'error_code' => $other['row']['decoy'] === '1' ? $other['row']['error_code'] : 'INF-LIT',
                ];
            }
        }
    }

    /**
     * Round-2 codes: every clue × every part except its key; main lures carry
     * their own code (`main` = 1), the rest the code of the clue type.
     *
     * @param list<array{row: array<string, string>, item: array<string, mixed>}> $rows
     * @param list<array<string, mixed>>                                         $parts
     */
    private static function partCodes(array &$sheets, array &$problems, string $ref, string $group, array $rows, array $parts): void
    {
        $partKeys = array_map(static fn (array $p): string => (string) $p['key'], $parts);

        foreach ($rows as $r) {
            $key  = $r['row']['item_key'];
            $main = (array) ($r['item']['main'] ?? []);
            $type = ErrorCodes::CLUE_TYPE_CODES[$r['row']['d3_clue_type']] ?? '';

            foreach (array_keys($main) as $part) {
                if (! in_array((string) $part, $partKeys, true) || (string) $part === $r['row']['answer_id']) {
                    $problems[] = self::problem('error', $ref, $group, $key, "Pengecoh utama {$part} bukan bagian unggahan ini, atau sama dengan kuncinya.");
                }
            }

            foreach ($partKeys as $part) {
                if ($part === $r['row']['answer_id']) {
                    continue;
                }

                $sheets['part_codes'][] = [
                    'item_key'   => $key,
                    'part_key'   => $part,
                    'error_code' => (string) ($main[$part] ?? $type),
                    'main'       => isset($main[$part]) ? '1' : '0',
                ];
            }
        }
    }

    /** @param array<string, mixed> $item */
    private static function flattenScenario(array &$sheets, string $key, array $item): void
    {
        $sheets['scenarios'][] = [
            'item_key'       => $key,
            'd2_topic'       => (string) ($item['topic'] ?? ''),
            'steps'          => (string) (int) ($item['steps'] ?? 1),
            'explanation_id' => self::bi($item['explanation'] ?? null, 0),
            'explanation_en' => self::bi($item['explanation'] ?? null, 1),
        ];

        foreach (['action' => 'actions', 'reason' => 'reasons'] as $kind => $list) {
            foreach (array_values((array) ($item[$list] ?? [])) as $n => $option) {
                $sheets['scenario_options'][] = [
                    'item_key'    => $key,
                    'kind'        => $kind,
                    'option_key'  => ($kind === 'action' ? 'A' : 'R') . ($n + 1),
                    'sequence'    => (string) ($n + 1),
                    'text_id'     => self::bi($option['text'] ?? null, 0),
                    'text_en'     => self::bi($option['text'] ?? null, 1),
                    'score'       => $kind === 'action' ? (string) (int) ($option['score'] ?? 0) : '',
                    'code'        => (string) ($option['code'] ?? ''),
                    'feedback_id' => self::bi($option['feedback'] ?? null, 0),
                    'feedback_en' => self::bi($option['feedback'] ?? null, 1),
                ];
            }
        }
    }

    // ------------------------------------------------------- rows → nodes

    /**
     * Workbook rows → BankRules nodes. Shared by the builder (its own rows)
     * and the importer (rows of an uploaded workbook).
     *
     * @param array<string, list<array<string, string>>> $sheets
     * @param array<string, string>                      $difficulty node prefix → difficulty
     * @param callable(array<string, string>): bool      $hasMedia   does an item row have its image
     *
     * @return array{nodes: array<string, array<string, mixed>>, passages: array<string, array<string, string>>, regions: array<string, list<array{sequence: int, engine: string}>>}
     */
    public static function nodesFromSheets(array $sheets, array $difficulty, callable $hasMedia): array
    {
        $levels   = array_flip(self::REGIONS);
        $passages = [];

        foreach ($sheets['passages'] ?? [] as $row) {
            $passages[$row['passage_key']] = self::passageFromRow($row);
        }

        $codes     = self::codesFromSheets($sheets);
        $posts     = self::postsFromSheets($sheets);
        $scenarios = self::scenariosFromSheets($sheets);
        $nodes     = [];

        foreach ($sheets['nodes'] ?? [] as $row) {
            $nodes[$row['node_ref']] ??= self::emptyNode($row['node_ref'], $levels, $difficulty);

            if ($row['distractor_count'] !== '') {
                $nodes[$row['node_ref']]['distractor_count'] = (int) $row['distractor_count'];
            }
        }

        foreach ($sheets['distractors'] ?? [] as $row) {
            $nodes[$row['node_ref']] ??= self::emptyNode($row['node_ref'], $levels, $difficulty);
            $nodes[$row['node_ref']]['variants'][$row['variant_group']]['distractors'][] = ['id' => $row['text_id'], 'en' => $row['text_en'] !== '' ? $row['text_en'] : $row['text_id']];
        }

        foreach ($sheets['items'] ?? [] as $row) {
            if (($row['is_active'] ?? '') === '0') {
                continue;
            }

            $ref = $row['node_ref'];
            $nodes[$ref] ??= self::emptyNode($ref, $levels, $difficulty);
            $nodes[$ref]['engine'] = $nodes[$ref]['engine'] ?: (self::ENGINE_OF_TYPE[$row['interaction_type']] ?? '');
            $nodes[$ref]['variants'][$row['variant_group']]['items'][] = self::itemFromRow($row, $hasMedia($row));
        }

        foreach ($nodes as $ref => $node) {
            foreach (array_keys($node['variants']) as $group) {
                $nodes[$ref]['variants'][$group] += ['distractors' => [], 'items' => []];
                usort($nodes[$ref]['variants'][$group]['items'], static fn (array $a, array $b): int => [$a['sequence'], $a['key']] <=> [$b['sequence'], $b['key']]);

                foreach ($nodes[$ref]['variants'][$group]['items'] as $item) {
                    if (isset($codes[$item['key']])) {
                        $nodes[$ref]['codes'][$item['key']] = $codes[$item['key']];
                    }

                    if (isset($scenarios[$item['key']])) {
                        $nodes[$ref]['scenarios'][$item['key']] = $scenarios[$item['key']];
                    }
                }
            }

            ksort($nodes[$ref]['variants'], SORT_STRING);
            $nodes[$ref]['posts'] = $posts[$ref] ?? [];
        }

        $regions = [];

        foreach ($nodes as $node) {
            if (in_array($node['engine'], BankRules::ENGINES, true)) {
                $regions[$node['level']][] = ['sequence' => $node['sequence'], 'engine' => $node['engine']];
            }
        }

        return ['nodes' => $nodes, 'passages' => $passages, 'regions' => $regions];
    }

    /**
     * One item row → the item shape of BankRules.
     *
     * @param array<string, string> $row
     *
     * @return array<string, mixed>
     */
    public static function itemFromRow(array $row, bool $hasMedia): array
    {
        $type  = (string) $row['interaction_type'];
        $decoy = ($row['decoy'] ?? '') !== '' ? $row['decoy'] === '1' : mb_strtolower((string) $row['answer_id']) === 'decoy';

        return [
            'key'         => (string) $row['item_key'],
            'sequence'    => ($row['sequence'] ?? '') !== '' ? (int) $row['sequence'] : 1,
            'type'        => $type,
            'round'       => ($row['round'] ?? '') !== '' ? (int) $row['round'] : null,
            'dim'         => strtoupper(trim((string) ($row['dimension'] ?? ''))),
            'sub'         => trim((string) ($row['subaspect'] ?? '')),
            'akm'         => strtoupper(trim((string) ($row['akm_level'] ?? ''))),
            'pirls'       => trim((string) ($row['pirls_process'] ?? '')),
            'bloom'       => strtoupper(trim((string) $row['bloom_level'])),
            'skill'       => (string) $row['skill'],
            'error_code'  => strtoupper(trim((string) ($row['error_code'] ?? ''))),
            'clue_type'   => strtoupper(trim((string) ($row['d3_clue_type'] ?? ''))),
            'topic'       => strtoupper(trim((string) ($row['d2_topic'] ?? ''))),
            'testlet'     => (string) ($row['testlet_key'] ?? ''),
            'labels'      => (string) ($row['label_source'] ?? ''),
            'prompt_id'   => (string) $row['prompt_id'],
            'prompt_en'   => (string) $row['prompt_en'],
            'answer_id'   => match ($type) {
                'verdict_card' => mb_strtolower(trim((string) $row['answer_id'])),
                'find_object'  => $decoy ? 'decoy' : 'target',
                'find_part'    => strtoupper(trim((string) $row['answer_id'])),
                default        => (string) $row['answer_id'],
            },
            'answer_en'   => $type === 'fill_blank_bank' ? ((string) $row['answer_en'] !== '' ? (string) $row['answer_en'] : (string) $row['answer_id']) : '',
            'passage'     => ($row['passage_key'] ?? '') !== '' ? (string) $row['passage_key'] : null,
            'x'           => ($row['x'] ?? '') !== '' ? (float) $row['x'] : null,
            'y'           => ($row['y'] ?? '') !== '' ? (float) $row['y'] : null,
            'w'           => ($row['w'] ?? '') !== '' ? (float) $row['w'] : null,
            'decoy'       => $decoy,
            'wrong_id'    => (string) $row['wrong_feedback_id'],
            'media'       => $hasMedia,
            'media_label' => (string) ($row['media_label'] ?? ''),
            'ref'         => (string) ($row['reference_source'] ?? ''),
            'review'      => (string) ($row['review_status'] ?? ''),
            'review_note' => (string) ($row['review_note'] ?? ''),
        ];
    }

    /**
     * @param array<string, string> $row
     *
     * @return array<string, string>
     */
    public static function passageFromRow(array $row): array
    {
        return [
            'level'   => (string) $row['level_code'],
            'title'   => (string) ($row['title_id'] ?? ''),
            'body_id' => (string) $row['body_id'],
            'body_en' => (string) $row['body_en'],
            'genre'   => (string) ($row['text_genre'] ?? ''),
            'layout'  => (string) ($row['layout'] ?? ''),
            'ref'     => (string) ($row['reference_source'] ?? ''),
        ];
    }

    /**
     * Rows of `cloze_matrix`, `object_codes`, `part_codes` → item_key → codes.
     *
     * @param array<string, list<array<string, string>>> $sheets
     *
     * @return array<string, list<array{type: string, ref: string, code: string, main: bool}>>
     */
    public static function codesFromSheets(array $sheets): array
    {
        $out = [];

        foreach ($sheets['cloze_matrix'] ?? [] as $row) {
            $out[$row['item_key']][] = ['type' => 'word', 'ref' => self::wordRef($row['word_id']), 'code' => strtoupper(trim($row['error_code'])), 'main' => false];
        }

        foreach ($sheets['object_codes'] ?? [] as $row) {
            $out[$row['item_key']][] = ['type' => 'object', 'ref' => trim($row['object_key']), 'code' => strtoupper(trim($row['error_code'])), 'main' => false];
        }

        foreach ($sheets['part_codes'] ?? [] as $row) {
            $out[$row['item_key']][] = ['type' => 'part', 'ref' => strtoupper(trim($row['part_key'])), 'code' => strtoupper(trim($row['error_code'])), 'main' => ($row['main'] ?? '') === '1'];
        }

        return $out;
    }

    /**
     * Rows of `post_scenes` and `post_parts` → node_ref → group → post.
     *
     * @param array<string, list<array<string, string>>> $sheets
     *
     * @return array<string, array<string, array{scene: array<string, string>, parts: list<array<string, mixed>>}>>
     */
    public static function postsFromSheets(array $sheets): array
    {
        $out = [];

        foreach ($sheets['post_scenes'] ?? [] as $row) {
            $out[$row['node_ref']][$row['variant_group']] = ['scene' => $row, 'parts' => []];
        }

        foreach ($sheets['post_parts'] ?? [] as $row) {
            $out[$row['node_ref']][$row['variant_group']] ??= ['scene' => [], 'parts' => []];
            $out[$row['node_ref']][$row['variant_group']]['parts'][] = [
                'key'     => strtoupper(trim($row['part_key'])),
                'kind'    => trim($row['kind']),
                'text_id' => (string) $row['text_id'],
                'text_en' => (string) $row['text_en'],
                'x'       => (float) $row['x'],
                'y'       => (float) $row['y'],
                'w'       => (float) $row['w'],
                'h'       => (float) $row['h'],
            ];
        }

        return $out;
    }

    /**
     * Rows of `scenarios` and `scenario_options` → item_key → scenario.
     *
     * @param array<string, list<array<string, string>>> $sheets
     *
     * @return array<string, array<string, mixed>>
     */
    public static function scenariosFromSheets(array $sheets): array
    {
        $out = [];

        foreach ($sheets['scenarios'] ?? [] as $row) {
            $out[$row['item_key']] = [
                'topic'       => strtoupper(trim($row['d2_topic'])),
                'steps'       => (int) $row['steps'],
                'explanation' => (string) $row['explanation_id'],
                'actions'     => [],
                'reasons'     => [],
            ];
        }

        foreach ($sheets['scenario_options'] ?? [] as $row) {
            $out[$row['item_key']] ??= ['topic' => '', 'steps' => 1, 'explanation' => '', 'actions' => [], 'reasons' => []];
            $out[$row['item_key']][$row['kind'] === 'reason' ? 'reasons' : 'actions'][] = [
                'key'     => trim($row['option_key']),
                'text_id' => (string) $row['text_id'],
                'text_en' => (string) $row['text_en'],
                'score'   => $row['score'] !== '' ? (int) $row['score'] : null,
                'code'    => strtoupper(trim((string) $row['code'])),
            ];
        }

        return $out;
    }

    /** Stored form of a cloze word (`item_error_codes.choice_ref`): trimmed, single spaces, lower case. */
    public static function wordRef(string $word): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $word) ?? $word));
    }

    /**
     * Baris sheet DAFTAR_MEDIA (lampiran, tidak diimpor).
     *
     * @param array<string, array<string, mixed>> $media
     *
     * @return list<list<string>>
     */
    public static function mediaRows(array $media): array
    {
        $rows = [];

        foreach ($media as $key => $slot) {
            $rows[] = [
                $key,
                (string) $slot['label'],
                (string) $slot['where'],
                implode(', ', $slot['used_by']),
                (string) $slot['note'],
                self::MEDIA_SIZES[$slot['kind']] ?? '',
                (string) $slot['source'],
            ];
        }

        return $rows;
    }

    // -------------------------------------------------------------- bantu

    /** @return array<string, mixed> */
    private static function emptyNode(string $ref, array $levels, array $difficulty): array
    {
        [$prefix, $sequence] = array_pad(explode('-', $ref, 2), 2, '0');

        return [
            'ref'              => $ref,
            'level'            => (string) ($levels[$prefix] ?? $prefix),
            'sequence'         => (int) $sequence,
            'engine'           => '',
            'difficulty'       => DifficultyRules::normalize($difficulty[$prefix] ?? null),
            'distractor_count' => 0,
            'variants'         => [],
            'codes'            => [],
            'posts'            => [],
            'scenarios'        => [],
        ];
    }

    /** `labels` of a data-file item → workbook `label_source`: developer | expert | akm:expert,bloom:developer */
    private static function labelSource(mixed $labels): string
    {
        if (is_string($labels) && $labels !== '') {
            return $labels;
        }

        if (is_array($labels) && $labels !== []) {
            return implode(',', array_map(static fn ($k, $v): string => $k . ':' . $v, array_keys($labels), $labels));
        }

        return 'developer';
    }

    /**
     * @param array<string, array<string, mixed>> $media
     * @param array<string, mixed>                $slot  key, label, note, source
     */
    private static function addMedia(array &$media, array $slot, string $kind, string $region, string $ref, string $usedBy): void
    {
        $key = (string) $slot['key'];
        $tag = strtoupper(self::REGIONS[$region] ?? $region);

        $media[$key] ??= [
            'key'     => $key,
            'kind'    => $kind,
            'label'   => (string) ($slot['label'] ?? ''),
            'note'    => (string) ($slot['note'] ?? ''),
            'source'  => (string) ($slot['source'] ?? ''),
            'where'   => $tag . ' ' . match ($kind) {
                'puzzle' => 'Susun Gambar',
                'card'   => 'Benar atau Salah (gambar pernyataan)',
                'scene'  => 'Cari Objek (adegan)',
                'post'   => 'Periksa Unggahan (foto unggahan)',
                default  => 'Cari Objek (benda)',
            },
            'used_by' => [],
        ];
        $media[$key]['used_by'][] = $usedBy;
    }

    /** Teks satu bahasa dari pasangan [Indonesia, English]. */
    private static function bi(mixed $pair, int $index): string
    {
        if (is_array($pair)) {
            return (string) ($pair[$index] ?? '');
        }

        return $index === 0 ? (string) $pair : '';
    }

    /** 23.5 → "23.5", 12 → "12" */
    private static function number(mixed $value): string
    {
        $float = (float) $value;

        return $float === floor($float) ? (string) (int) $float : (string) $float;
    }

    /** @return array{level: string, node: string, variant: ?string, item: ?string, message: string} */
    private static function problem(string $level, string $node, ?string $variant, ?string $item, string $message): array
    {
        return ['level' => $level, 'node' => $node, 'variant' => $variant, 'item' => $item, 'message' => $message];
    }
}

/**
 * Running counters of one challenge while BankSource flattens it.
 *
 * @internal
 */
final class BankRowContext
{
    private int $sequence = 0;

    private int $retired = 0;

    /** @param array<string, mixed> $node */
    public function __construct(
        public readonly string $ref,
        public readonly string $region,
        public readonly string $engine,
        public readonly array $node,
    ) {
    }

    public function nextSequence(): int
    {
        return ++$this->sequence;
    }

    public function nextRetired(): int
    {
        return ++$this->retired;
    }
}
