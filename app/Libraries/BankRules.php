<?php

namespace App\Libraries;

/**
 * Pemeriksaan bank soal kelas 5 (perombakan kelas 5, Dok 2 §7.5).
 *
 * Satu sumber aturan untuk keempat pemakainya: skrip
 * docs/bank-soal/build-workbook.php, importer (ContentImportService),
 * ContentVerifier (`gelita:content:verify` dan panel), dan uji
 * BankKelas5DataTest. Kelas ini murni (tanpa framework) karena skrip
 * penyusun workbook berjalan tanpa CodeIgniter; setiap pemakai lebih dulu
 * mengubah sumbernya (berkas data, workbook, atau database) menjadi bentuk
 * node yang sama:
 *
 *     [
 *         'ref'              => 'tmg-2',
 *         'engine'           => 'rumpang',            // puzzle | rumpang | boleh | cari
 *         'difficulty'       => 'mudah',
 *         'distractor_count' => 0,                    // rumpang
 *         'variants'         => [
 *             'a' => [
 *                 'distractors' => [['id' => 'enam', 'en' => 'six']],   // rumpang
 *                 'items'       => [[
 *                     'key' => 'tmg-2-a01', 'type' => 'fill_blank_bank',
 *                     'prompt_id' => '…', 'prompt_en' => '…', 'answer_id' => '…', 'answer_en' => '…',
 *                     'bloom' => 'C1', 'skill' => null, 'passage' => null,
 *                     'x' => null, 'y' => null, 'w' => null, 'decoy' => false,
 *                     'wrong_id' => null, 'media' => false,
 *                 ]],
 *             ],
 *         ],
 *     ]
 *
 * Bacaan Benar atau Salah: `passage` = kode bacaan; isinya dari $passages
 * (kode → ['body_id' => …, 'body_en' => …]).
 *
 * Hasil berupa daftar temuan `['level' => 'error'|'warning', 'node', 'variant',
 * 'item', 'message']`. Galat = impor ditolak / verifikasi gagal; peringatan =
 * perlu diperiksa (Bloom di luar rentang tingkat, variasi tanpa pernyataan
 * benar atau salah).
 */
final class BankRules
{
    /** Urutan tantangan setiap wilayah (K1). */
    public const ENGINES = [1 => 'puzzle', 2 => 'rumpang', 3 => 'boleh', 4 => 'cari'];

    /** Satu jenis soal per tantangan. */
    public const INTERACTION = [
        'puzzle'  => 'puzzle_arrange',
        'rumpang' => 'fill_blank_bank',
        'boleh'   => 'verdict_card',
        'cari'    => 'find_object',
    ];

    /** Jenis soal yang wajib bertanda Bloom → rentang aturannya (DifficultyRules::bloomRange()). */
    private const BLOOM_KIND = ['fill_blank_bank' => 'reading', 'verdict_card' => 'truefalse', 'find_object' => 'hunt'];

    /** Variasi aktif yang wajib ada; Cari Objek cukup satu (satu adegan tetap). */
    public const VARIANTS = ['puzzle' => 3, 'rumpang' => 3, 'boleh' => 3, 'cari' => 1];

    /** Batas tumpang tindih dua benda Cari Objek: bagian dari luas kotak yang lebih kecil. */
    public const MAX_OVERLAP = 0.20;

    /** Tinggi benda Cari Objek dalam persen tinggi adegan = lebar × 16/9 (benda persegi, adegan 16:9). */
    public const OBJECT_ASPECT = 16 / 9;

    /**
     * Seluruh pemeriksaan satu tantangan.
     *
     * @param array<string, mixed>                $node
     * @param array<string, array<string, mixed>> $passages kode bacaan → body_id/body_en
     *
     * @return list<array{level: string, node: string, variant: ?string, item: ?string, message: string}>
     */
    public static function node(array $node, array $passages = []): array
    {
        $engine   = (string) ($node['engine'] ?? '');
        $ref      = (string) ($node['ref'] ?? '');
        $variants = (array) ($node['variants'] ?? []);
        $out      = [];

        if (! isset(self::INTERACTION[$engine])) {
            return [self::problem('error', $ref, null, null, "Jenis tantangan '{$engine}' tidak dikenali.")];
        }

        $want = self::VARIANTS[$engine];
        $have = array_keys(array_filter($variants, static fn ($v): bool => ((array) ($v['items'] ?? [])) !== []));

        if ($have === []) {
            $out[] = self::problem('error', $ref, null, null, 'Belum ada soal aktif.');
        } elseif ($engine === 'cari' ? count($have) < $want : count($have) !== $want) {
            $out[] = self::problem('error', $ref, null, null, sprintf(
                'Ada %d variasi aktif (%s); aturan kelas 5: %s.',
                count($have),
                implode(', ', $have),
                $engine === 'cari' ? 'paling sedikit 1 variasi' : 'tepat 3 variasi (a, b, c)',
            ));
        }

        foreach ($variants as $group => $variant) {
            $items = array_values((array) ($variant['items'] ?? []));

            if ($items === []) {
                continue;
            }

            $group = (string) $group;
            $out   = [...$out, ...self::commonItemProblems($node, $group, $items)];
            $out   = [...$out, ...match ($engine) {
                'puzzle'  => self::puzzle($ref, $group, $items),
                'rumpang' => self::reading($node, $group, $variant, $items),
                'boleh'   => self::trueFalse($node, $group, $items, $passages),
                'cari'    => self::hunt($node, $group, $items),
            }];
        }

        return $out;
    }

    /**
     * Wilayah: tepat 4 tantangan aktif berurutan Susun Gambar, Isi Rumpang,
     * Benar atau Salah, Cari Objek.
     *
     * @param list<array{sequence: int, engine: string}> $nodes tantangan aktif wilayah itu
     *
     * @return list<array{level: string, node: string, variant: ?string, item: ?string, message: string}>
     */
    public static function region(string $scope, array $nodes): array
    {
        usort($nodes, static fn (array $a, array $b): int => (int) $a['sequence'] <=> (int) $b['sequence']);

        $engines = array_map(static fn (array $n): string => (string) $n['engine'], $nodes);

        if ($engines === array_values(self::ENGINES)) {
            return [];
        }

        return [self::problem('error', $scope, null, null, sprintf(
            'Wilayah ini punya %d tantangan aktif (%s); seharusnya 4 berurutan: %s.',
            count($engines),
            $engines === [] ? '—' : implode(', ', $engines),
            implode(', ', self::ENGINES),
        ))];
    }

    /**
     * Kode soal yang muncul lebih dari sekali (item_key unik lintas wilayah).
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

    /**
     * Jumlah paragraf sebuah bacaan: dipisah baris kosong.
     */
    public static function paragraphCount(string $text): int
    {
        $text = trim(str_replace("\r\n", "\n", $text));

        return $text === '' ? 0 : count(preg_split('/\n[ \t]*\n\s*/u', $text) ?: [$text]);
    }

    /**
     * Bagian luas yang tumpang tindih dari dua benda Cari Objek (0–1),
     * dihitung dari kotak x, y, w, w × 16/9 terhadap kotak yang lebih kecil.
     *
     * @param array{x: float|int, y: float|int, w: float|int} $a
     * @param array{x: float|int, y: float|int, w: float|int} $b
     */
    public static function overlap(array $a, array $b): float
    {
        [$ax, $ay, $aw, $ah] = self::box($a);
        [$bx, $by, $bw, $bh] = self::box($b);

        $w = min($ax + $aw, $bx + $bw) - max($ax, $bx);
        $h = min($ay + $ah, $by + $bh) - max($ay, $by);

        if ($w <= 0 || $h <= 0) {
            return 0.0;
        }

        $smaller = min($aw * $ah, $bw * $bh);

        return $smaller > 0 ? ($w * $h) / $smaller : 0.0;
    }

    // ------------------------------------------------------------ bersama

    /**
     * @param list<array<string, mixed>> $items
     *
     * @return list<array<string, mixed>>
     */
    private static function commonItemProblems(array $node, string $group, array $items): array
    {
        $engine = (string) $node['engine'];
        $ref    = (string) $node['ref'];
        $out    = [];

        foreach ($items as $item) {
            $key  = (string) ($item['key'] ?? '');
            $type = (string) ($item['type'] ?? '');

            if ($type !== self::INTERACTION[$engine]) {
                $out[] = self::problem('error', $ref, $group, $key, "Jenis soal '{$type}' tidak cocok dengan tantangan ini (" . self::INTERACTION[$engine] . ').');

                continue;
            }

            if (! isset(self::BLOOM_KIND[$type]) || ! empty($item['decoy'])) {
                continue;
            }

            $bloom = strtoupper(trim((string) ($item['bloom'] ?? '')));
            $kind  = self::BLOOM_KIND[$type];

            if ($bloom === '') {
                $out[] = self::problem('error', $ref, $group, $key, 'Tingkat berpikir (Bloom) wajib diisi.');
            } elseif (! DifficultyRules::isBloom($bloom)) {
                $out[] = self::problem('error', $ref, $group, $key, "Tingkat berpikir '{$bloom}' tidak dikenal (C1–C6).");
            } elseif (! DifficultyRules::bloomInRange($bloom, (string) ($node['difficulty'] ?? ''), $kind)) {
                $out[] = self::problem('warning', $ref, $group, $key, sprintf(
                    'Tingkat berpikir %s di luar anjuran tingkat %s (%s).',
                    $bloom,
                    DifficultyRules::normalize((string) ($node['difficulty'] ?? '')),
                    DifficultyRules::bloomRangeLabel((string) ($node['difficulty'] ?? ''), $kind),
                ));
            }
        }

        return $out;
    }

    // ------------------------------------------------------- per tantangan

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
     * Isi Rumpang satu variasi.
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

        return self::unique($out);
    }

    /**
     * Benar atau Salah satu variasi.
     *
     * @param list<array<string, mixed>>          $items
     * @param array<string, array<string, mixed>> $passages
     */
    private static function trueFalse(array $node, string $group, array $items, array $passages): array
    {
        $ref        = (string) $node['ref'];
        $difficulty = (string) ($node['difficulty'] ?? '');
        $rule       = DifficultyRules::trueFalse($difficulty);
        $level      = DifficultyRules::normalize($difficulty);
        $out        = [];

        if (count($items) !== $rule['statements']) {
            $out[] = self::problem('error', $ref, $group, null, 'Ada ' . count($items) . " pernyataan; aturan tingkat {$level} adalah tepat {$rule['statements']} pernyataan.");
        }

        $keys = array_values(array_unique(array_map(static fn (array $i): string => trim((string) ($i['passage'] ?? '')), $items)));

        if (count($keys) !== 1 || $keys[0] === '') {
            $out[] = self::problem('error', $ref, $group, null, 'Semua pernyataan satu variasi harus merujuk tepat satu bacaan (passage_key yang sama).');
        } elseif (! isset($passages[$keys[0]])) {
            $out[] = self::problem('error', $ref, $group, null, "Bacaan '{$keys[0]}' tidak ditemukan.");
        } else {
            foreach (['id' => 'Indonesia', 'en' => 'Inggris'] as $lang => $name) {
                $body = (string) ($passages[$keys[0]]['body_' . $lang] ?? '');

                if ($body === '' && $lang === 'en') {
                    continue;
                }

                $paragraphs = self::paragraphCount($body);

                if ($paragraphs !== $rule['paragraphs']) {
                    $out[] = self::problem('error', $ref, $group, null, "Bacaan {$keys[0]} (bahasa {$name}) punya {$paragraphs} paragraf; aturan tingkat {$level} adalah {$rule['paragraphs']} paragraf (dipisah baris kosong).");
                }
            }
        }

        $verdicts = [];

        foreach ($items as $item) {
            $key     = (string) $item['key'];
            $verdict = mb_strtolower(trim((string) ($item['answer_id'] ?? '')));
            $skill   = trim((string) ($item['skill'] ?? ''));

            if (! in_array($verdict, ['benar', 'salah'], true)) {
                $out[] = self::problem('error', $ref, $group, $key, 'Kunci jawaban harus benar atau salah.');
            } else {
                $verdicts[$verdict] = true;
            }

            if (! DifficultyRules::isSkill($skill)) {
                $out[] = self::problem('error', $ref, $group, $key, $skill === ''
                    ? 'Keterampilan (skill) pernyataan belum diisi.'
                    : "Keterampilan '{$skill}' tidak dikenal. Pakai " . implode(', ', array_keys(DifficultyRules::SKILLS)) . '.');
            } elseif (! in_array($skill, $rule['skills'], true)) {
                $out[] = self::problem('warning', $ref, $group, $key, "Keterampilan '{$skill}' bukan keterampilan tingkat {$level} (" . implode(', ', $rule['skills']) . ').');
            }
        }

        foreach (['benar', 'salah'] as $verdict) {
            if (! isset($verdicts[$verdict]) && count($verdicts) > 0) {
                $out[] = self::problem('warning', $ref, $group, null, "Tidak ada pernyataan berkunci {$verdict} di variasi ini.");
            }
        }

        return $out;
    }

    /**
     * Cari Objek satu variasi.
     *
     * @param list<array<string, mixed>> $items
     */
    private static function hunt(array $node, string $group, array $items): array
    {
        $ref        = (string) $node['ref'];
        $difficulty = (string) ($node['difficulty'] ?? '');
        $rule       = DifficultyRules::hunt($difficulty);
        $level      = DifficultyRules::normalize($difficulty);
        $out        = [];
        $targets    = array_filter($items, static fn (array $i): bool => empty($i['decoy']));
        $decoys     = count($items) - count($targets);

        if (count($targets) !== $rule['targets'] || $decoys !== $rule['decoys']) {
            $out[] = self::problem('error', $ref, $group, null, sprintf(
                'Ada %d benda dicari dan %d pengecoh; aturan tingkat %s adalah %d dicari + %d pengecoh.',
                count($targets),
                $decoys,
                $level,
                $rule['targets'],
                $rule['decoys'],
            ));
        }

        $placed = [];

        foreach ($items as $item) {
            $key = (string) $item['key'];

            if (! empty($item['decoy']) && trim((string) ($item['wrong_id'] ?? '')) === '') {
                $out[] = self::problem('error', $ref, $group, $key, 'Benda pengecoh wajib punya umpan balik bahasa Indonesia (wrong_feedback_id).');
            }

            if (empty($item['media'])) {
                $out[] = self::problem('error', $ref, $group, $key, 'Gambar benda belum ditentukan (kode berkas gambar wajib).');
            }

            if (! is_numeric($item['x'] ?? null) || ! is_numeric($item['y'] ?? null) || ! is_numeric($item['w'] ?? null)) {
                $out[] = self::problem('error', $ref, $group, $key, 'Posisi benda (x, y, w) wajib diisi.');

                continue;
            }

            [$x, $y, $w, $h] = self::box($item);

            if ($w <= 0 || $x < 0 || $y < 0 || $x + $w > 100 || $y + $h > 100) {
                $out[] = self::problem('error', $ref, $group, $key, sprintf(
                    'Posisi benda keluar dari adegan: x %s, y %s, lebar %s, tinggi %s (semuanya harus di dalam 0–100 %%).',
                    self::fmt($x),
                    self::fmt($y),
                    self::fmt($w),
                    self::fmt($h),
                ));
            }

            foreach ($placed as $otherKey => $other) {
                $share = self::overlap($item, $other);

                if ($share > self::MAX_OVERLAP) {
                    $out[] = self::problem('error', $ref, $group, $key, sprintf(
                        'Benda ini menumpuk %d %% luasnya dengan %s (paling banyak %d %%).',
                        (int) round($share * 100),
                        $otherKey,
                        (int) round(self::MAX_OVERLAP * 100),
                    ));
                }
            }

            $placed[$key] = $item;
        }

        return $out;
    }

    // -------------------------------------------------------------- bantu

    /** @return array{0: float, 1: float, 2: float, 3: float} x, y, lebar, tinggi (persen) */
    private static function box(array $item): array
    {
        $w = (float) ($item['w'] ?? 0);

        return [(float) ($item['x'] ?? 0), (float) ($item['y'] ?? 0), $w, $w * self::OBJECT_ASPECT];
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
     * Teks satu temuan untuk keluaran CLI/skrip: "tmg-2 · variasi a · tmg-2-a01: pesan".
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
