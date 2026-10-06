<?php

namespace App\Libraries;

/**
 * Berkas data bank soal kelas 5 (docs/bank-soal/data/{wilayah}.php, Dok 2
 * §7.2) → baris sheet workbook impor dan node untuk BankRules.
 *
 * Dipakai docs/bank-soal/build-workbook.php dan BankKelas5DataTest, jadi
 * kode soal, urutan, dan isi workbook selalu sama dengan yang diperiksa uji.
 * Kelas ini murni (tanpa framework) seperti DifficultyRules.
 *
 * Aturan kode otomatis (§7.2):
 * - kode soal `{node}-{variasi}{nn}` (nn dua digit per variasi, urut berkas);
 * - kode bacaan Benar atau Salah `{node}-{variasi}`;
 * - `sequence` soal = urutan global di tantangan (a01…, b01…, c01…);
 * - judul variasi Benar atau Salah = judul bacaannya.
 */
final class BankSource
{
    /** Wilayah → awalan kode tantangan (sama dengan Config\Gelita::$levelPrefixes). */
    public const REGIONS = ['temanggung' => 'tmg', 'magelang' => 'mgl', 'wonosobo' => 'wnb'];

    public const REVIEW_STATUS = 'verified';

    public const REVIEW_NOTE = 'Disetujui peneliti sebagai bank soal final penelitian kelas 5 (Oktober 2026).';

    /** Ukuran & format gambar per jenis slot (sheet DAFTAR_MEDIA). */
    public const MEDIA_SIZES = [
        'puzzle' => 'persegi, minimal 1200 × 1200 px (JPG/WebP); gambar tidak persegi dipotong bagian tengahnya',
        'scene'  => '1280 × 720 px (16:9), JPG/WebP',
        'object' => '240 × 240 px, PNG/WebP berlatar transparan',
        'card'   => '480 × 360 px, JPG/WebP',
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
     * Ratakan seluruh wilayah.
     *
     * @param array<string, array<string, mixed>> $regions hasil load()
     *
     * @return array{
     *     sheets: array<string, list<array<string, string>>>,
     *     nodes: list<array<string, mixed>>,
     *     passages: array<string, array<string, string>>,
     *     regions: array<string, list<array{sequence: int, engine: string}>>,
     *     media: array<string, array<string, mixed>>,
     *     keys: list<string>
     * }
     */
    public static function flatten(array $regions): array
    {
        $sheets   = ['nodes' => [], 'variants' => [], 'distractors' => [], 'passages' => [], 'items' => [], 'hints' => []];
        $nodes    = [];
        $passages = [];
        $layout   = [];
        $media    = [];
        $keys     = [];

        foreach ($regions as $region => $data) {
            $difficulty = DifficultyRules::normalize((string) ($data['difficulty'] ?? ''));

            foreach ((array) $data['nodes'] as $ref => $node) {
                $ref      = (string) $ref;
                $engine   = (string) $node['engine'];
                $sequence = (int) substr($ref, strrpos($ref, '-') + 1);

                $layout[$region][] = ['sequence' => $sequence, 'engine' => $engine];

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
                    $sheets['hints'][] = [
                        'node_ref' => $ref,
                        'item_key' => '',
                        'sequence' => (string) ($index + 1),
                        'text_id'  => self::bi($hint, 0),
                        'text_en'  => self::bi($hint, 1),
                    ];
                }

                $normal = [
                    'ref'              => $ref,
                    'level'            => $region,
                    'engine'           => $engine,
                    'difficulty'       => $difficulty,
                    'distractor_count' => (int) ($node['distractor_count'] ?? 0),
                    'variants'         => [],
                ];
                $position = 0;

                foreach ((array) $node['variants'] as $group => $variant) {
                    $group      = (string) $group;
                    $passageKey = null;
                    $title      = $variant['title'] ?? null;

                    if (isset($variant['passage'])) {
                        $passageKey = $ref . '-' . $group;
                        $title      = $variant['passage']['title'] ?? $title;

                        $sheets['passages'][] = [
                            'passage_key'      => $passageKey,
                            'level_code'       => $region,
                            'title_id'         => self::bi($variant['passage']['title'] ?? null, 0),
                            'title_en'         => self::bi($variant['passage']['title'] ?? null, 1),
                            'body_id'          => self::bi($variant['passage']['body'] ?? null, 0),
                            'body_en'          => self::bi($variant['passage']['body'] ?? null, 1),
                            'media_asset_key'  => '',
                            'reference_source' => (string) ($variant['passage']['ref'] ?? $variant['ref'] ?? $node['ref'] ?? ''),
                        ];
                        $passages[$passageKey] = [
                            'level'   => $region,
                            'body_id' => self::bi($variant['passage']['body'] ?? null, 0),
                            'body_en' => self::bi($variant['passage']['body'] ?? null, 1),
                        ];
                    }

                    $sheets['variants'][] = [
                        'node_ref'      => $ref,
                        'variant_group' => $group,
                        'title_id'      => self::bi($title, 0),
                        'title_en'      => self::bi($title, 1),
                    ];

                    $distractors = [];

                    foreach ((array) ($variant['distractors'] ?? []) as $distractor) {
                        $sheets['distractors'][] = [
                            'node_ref'      => $ref,
                            'variant_group' => $group,
                            'text_id'       => self::bi($distractor, 0),
                            'text_en'       => self::bi($distractor, 1),
                        ];
                        $distractors[] = ['id' => self::bi($distractor, 0), 'en' => self::bi($distractor, 1)];
                    }

                    $items = [];

                    foreach (array_values((array) $variant['items']) as $index => $item) {
                        $position++;
                        $key    = sprintf('%s-%s%02d', $ref, $group, $index + 1);
                        $type   = (string) $item['type'];
                        $decoy  = ! empty($item['decoy']);
                        $keys[] = $key;

                        $answerId = match ($type) {
                            'fill_blank_bank' => self::bi($item['answer'] ?? null, 0),
                            'verdict_card'    => mb_strtolower(trim((string) ($item['answer'] ?? ''))),
                            'find_object'     => $decoy ? 'decoy' : 'target',
                            default           => '',
                        };

                        $sheets['items'][] = [
                            'item_key'          => $key,
                            'node_ref'          => $ref,
                            'variant_group'     => $group,
                            'sequence'          => (string) $position,
                            'interaction_type'  => $type,
                            'indicator'         => (string) ($item['indicator'] ?? $node['indicator'] ?? ''),
                            'bloom_level'       => (string) ($item['bloom'] ?? ''),
                            'skill'             => (string) ($item['skill'] ?? ''),
                            'prompt_id'         => self::bi($item['prompt'] ?? null, 0),
                            'prompt_en'         => self::bi($item['prompt'] ?? null, 1),
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
                            'is_active'         => '1',
                            'review_status'     => self::REVIEW_STATUS,
                            'review_note'       => self::REVIEW_NOTE,
                            'reference_source'  => (string) ($item['ref'] ?? $variant['ref'] ?? $variant['passage']['ref'] ?? $node['ref'] ?? ''),
                        ];

                        if (isset($item['media']['key'])) {
                            self::addMedia($media, $item['media'], match ($engine) {
                                'puzzle' => 'puzzle',
                                'cari'   => 'object',
                                default  => 'card',
                            }, $region, $ref, $key . ($decoy ? ' (pengecoh)' : ''));
                        }

                        $items[] = [
                            'key'       => $key,
                            'type'      => $type,
                            'prompt_id' => self::bi($item['prompt'] ?? null, 0),
                            'prompt_en' => self::bi($item['prompt'] ?? null, 1),
                            'answer_id' => $answerId,
                            'answer_en' => $type === 'fill_blank_bank' ? self::bi($item['answer'] ?? null, 1) : '',
                            'bloom'     => (string) ($item['bloom'] ?? ''),
                            'skill'     => (string) ($item['skill'] ?? ''),
                            'passage'   => $passageKey,
                            'x'         => $item['x'] ?? null,
                            'y'         => $item['y'] ?? null,
                            'w'         => $item['w'] ?? null,
                            'decoy'     => $decoy,
                            'wrong_id'  => self::bi($item['wrong'] ?? null, 0),
                            'media'     => isset($item['media']['key']),
                        ];
                    }

                    $normal['variants'][$group] = ['distractors' => $distractors, 'items' => $items];
                }

                $nodes[] = $normal;
            }
        }

        ksort($media);

        return [
            'sheets'   => $sheets,
            'nodes'    => $nodes,
            'passages' => $passages,
            'regions'  => $layout,
            'media'    => $media,
            'keys'     => $keys,
        ];
    }

    /**
     * Seluruh pemeriksaan §7.5 atas hasil flatten(): per tantangan, per
     * wilayah, dan kode soal unik lintas wilayah.
     *
     * @param array<string, mixed> $flat
     *
     * @return list<array{level: string, node: string, variant: ?string, item: ?string, message: string}>
     */
    public static function problems(array $flat): array
    {
        $out = [];

        foreach ($flat['regions'] as $region => $layout) {
            $out = [...$out, ...BankRules::region($region, $layout)];
        }

        foreach ($flat['nodes'] as $node) {
            $out = [...$out, ...BankRules::node($node, $flat['passages'])];
        }

        foreach (BankRules::duplicateKeys($flat['keys']) as $key) {
            $out[] = ['level' => 'error', 'node' => explode('-', $key)[0], 'variant' => null, 'item' => $key, 'message' => 'Kode soal ini dipakai lebih dari sekali.'];
        }

        return $out;
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
}
