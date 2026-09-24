<?php

/**
 * Penyusun workbook bank soal produksi GELITA.
 *
 *   php docs/bank-soal/build-workbook.php [keluaran.xlsx]
 *
 * Sumber isi ada di docs/bank-soal/data/{temanggung,magelang,wonosobo}.php
 * dalam bentuk bertingkat yang mudah dibaca dan ditinjau lewat git (opsi,
 * potongan, sumber, dan petunjuk ditulis di dalam butirnya). Skrip ini
 * meratakannya menjadi sheet impor (ContentImportService::SHEETS), memberi
 * kode butir/opsi otomatis, memeriksa aturan dasar, lalu menulis workbook
 * berpanduan lewat App\Libraries\BankWorkbookGuide (sheet PETUNJUK,
 * KAMUS_KOLOM, catatan header, daftar pilihan) plus lampiran DAFTAR_MEDIA.
 *
 * Mengubah isi: sunting berkas data lalu jalankan ulang skrip — atau sunting
 * workbook langsung di Excel; keduanya diterima importer.
 */

declare(strict_types=1);

use App\Libraries\BankWorkbookGuide;

require __DIR__ . '/../../vendor/autoload.php';

$output = $argv[1] ?? __DIR__ . '/gelita-bank-soal-produksi.xlsx';
$data   = [
    'temanggung' => require __DIR__ . '/data/temanggung.php',
    'magelang'   => require __DIR__ . '/data/magelang.php',
    'wonosobo'   => require __DIR__ . '/data/wonosobo.php',
];

$sheets = array_fill_keys(array_keys(BankWorkbookGuide::COLUMNS), []);
$media  = [];   // asset_key => [prioritas, dipakai di, isi gambar, ukuran, saran sumber]
$errors = [];

$bi = static fn (array|string|null $pair, int $i): string => is_array($pair) ? (string) ($pair[$i] ?? '') : ($i === 0 ? (string) $pair : '');

foreach ($data as $levelCode => $region) {
    foreach ($region['passages'] as $key => $passage) {
        $sheets['passages'][] = [
            'passage_key'      => $key,
            'level_code'       => $levelCode,
            'title_id'         => $bi($passage['title'], 0),
            'title_en'         => $bi($passage['title'], 1),
            'body_id'          => $bi($passage['body'], 0),
            'body_en'          => $bi($passage['body'], 1),
            'media_asset_key'  => '',
            'reference_source' => $passage['ref'],
        ];
    }

    foreach ($region['nodes'] as $ref => $node) {
        $sheets['nodes'][] = [
            'node_ref'             => $ref,
            'title_id'             => $bi($node['title'], 0),
            'title_en'             => $bi($node['title'], 1),
            'instruction_id'       => $bi($node['instruction'], 0),
            'instruction_en'       => $bi($node['instruction'], 1),
            'description_id'       => $bi($node['description'], 0),
            'description_en'       => $bi($node['description'], 1),
            'items_per_round'      => (string) $node['items_per_round'],
            'verdict_options'      => $node['verdict_options'] ?? '',
            'require_reason'       => isset($node['require_reason']) ? (string) (int) $node['require_reason'] : '',
            'use_word_bank'        => isset($node['use_word_bank']) ? (string) (int) $node['use_word_bank'] : '',
            'distractor_count'     => isset($node['distractor_count']) ? (string) $node['distractor_count'] : '',
            'scene_media_key'      => $node['scene']['key'] ?? '',
            'background_media_key' => '',
        ];

        if (isset($node['scene'])) {
            $media[$node['scene']['key']] = ['WAJIB', "Adegan tantangan {$ref}", $node['scene']['note'], '1280 × 720 px (16:9), JPG/WebP', $node['scene']['source'] ?? ''];
        }

        foreach ($node['distractors'] ?? [] as [$id, $en]) {
            $sheets['distractors'][] = ['node_ref' => $ref, 'text_id' => $id, 'text_en' => $en];
        }

        foreach ($node['hints'] as $index => [$id, $en]) {
            $sheets['hints'][] = ['node_ref' => $ref, 'item_key' => '', 'sequence' => (string) ($index + 1), 'text_id' => $id, 'text_en' => $en];
        }

        $answers = [];

        foreach ($node['items'] as $index => $item) {
            $key  = sprintf('%s-%02d', $ref, $index + 1);
            $type = $item['type'];

            $answer = match ($type) {
                'ordering'     => $item['order'],
                'find_object'  => ! empty($item['decoy']) ? 'decoy' : 'target',
                default        => $bi($item['answer'] ?? null, 0),
            };

            $sheets['items'][] = [
                'item_key'          => $key,
                'node_ref'          => $ref,
                'sequence'          => (string) ($index + 1),
                'interaction_type'  => $type,
                'indicator'         => $item['indicator'] ?? $node['indicator'],
                'prompt_id'         => $bi($item['prompt'], 0),
                'prompt_en'         => $bi($item['prompt'], 1),
                'source_text_id'    => $bi($item['source'] ?? null, 0),
                'source_text_en'    => $bi($item['source'] ?? null, 1),
                'passage_key'       => $item['passage'] ?? '',
                'answer_id'         => $answer,
                'answer_en'         => in_array($type, ['fill_blank_bank', 'fill_blank_free'], true) ? $bi($item['answer'], 1) : '',
                'sample_reason_id'  => $bi($item['reason'] ?? null, 0),
                'sample_reason_en'  => $bi($item['reason'] ?? null, 1),
                'x'                 => isset($item['x']) ? (string) $item['x'] : '',
                'y'                 => isset($item['y']) ? (string) $item['y'] : '',
                'w'                 => isset($item['w']) ? (string) $item['w'] : '',
                'decoy'             => $type === 'find_object' ? (string) (int) ! empty($item['decoy']) : '',
                'wrong_feedback_id' => $bi($item['wrong'] ?? null, 0),
                'wrong_feedback_en' => $bi($item['wrong'] ?? null, 1),
                'digital_pillar'    => $item['pillar'] ?? '',
                'media_asset_key'   => isset($item['media']) ? $item['media']['key'] : '',
                'scorable'          => $type === 'find_object' && ! empty($item['decoy']) ? '0' : '1',
                'review_status'     => $item['status'] ?? 'draft',   // verified hanya setelah ditinjau guru/ahli
                'review_note'       => $item['note'] ?? $node['note'],
                'reference_source'  => $item['ref'] ?? $node['ref'],
            ];

            if (isset($item['media'])) {
                $media[$item['media']['key']] = [
                    'WAJIB',
                    ($type === 'find_object' ? 'Objek cari ' : 'Gambar puzzle ') . $key,
                    $item['media']['note'],
                    $type === 'find_object' ? '240 × 240 px, PNG/WebP latar transparan' : '900 × 900 px (persegi), JPG/WebP',
                    $item['media']['source'] ?? '',
                ];
            }

            foreach ($item['options'] ?? [] as $o => $option) {
                [$labelId, $labelEn, $correct] = $option;
                $sheets['options'][] = [
                    'option_key'      => $key . '-' . chr(97 + $o),
                    'item_key'        => $key,
                    'label_id'        => $labelId,
                    'label_en'        => $labelEn,
                    'is_correct'      => $correct ? '1' : '0',
                    'feedback_id'     => $option[3] ?? '',
                    'feedback_en'     => $option[4] ?? '',
                    'media_asset_key' => '',
                    'display_order'   => (string) ($o + 1),
                ];
            }

            foreach ($item['pieces'] ?? [] as [$pieceKey, $id, $en]) {
                $sheets['pieces'][] = ['item_key' => $key, 'piece_key' => $pieceKey, 'text_id' => $id, 'text_en' => $en];
            }

            foreach ($item['sources'] ?? [] as [$labelId, $labelEn, $kind, $id, $en]) {
                $sheets['sources'][] = ['item_key' => $key, 'label_id' => $labelId, 'label_en' => $labelEn, 'kind' => $kind, 'text_id' => $id, 'text_en' => $en];
            }

            foreach ($item['hints'] ?? [] as $h => [$id, $en]) {
                $sheets['hints'][] = ['node_ref' => '', 'item_key' => $key, 'sequence' => (string) ($h + 1), 'text_id' => $id, 'text_en' => $en];
            }

            // ------------------------------------------------ pemeriksaan
            $where = "{$key} ({$type})";

            if (in_array($type, ['single_choice', 'source_trust'], true)) {
                $correct = count(array_filter($item['options'] ?? [], static fn ($o): bool => (bool) $o[2]));

                if ($correct !== 1 || count($item['options']) !== 4) {
                    $errors[] = "{$where}: wajib 4 opsi dengan tepat 1 benar.";
                }
            }

            if (in_array($type, ['fill_blank_bank', 'fill_blank_free'], true)
                && (! str_contains($bi($item['prompt'], 0), '___') || ! str_contains($bi($item['prompt'], 1), '___'))) {
                $errors[] = "{$where}: kalimat rumpang ID dan EN wajib memuat ___.";
            }

            if ($type === 'fill_blank_bank') {
                $word = mb_strtolower($bi($item['answer'], 0));

                if (isset($answers[$word])) {
                    $errors[] = "{$where}: jawaban '{$word}' kembar dengan {$answers[$word]} — bank kata jadi ambigu.";
                }

                $answers[$word] = $key;
            }

            if (in_array($type, ['verdict_card', 'verdict_reason'], true)
                && ! in_array($answer, explode(',', (string) ($node['verdict_options'] ?? '')), true)) {
                $errors[] = "{$where}: kunci '{$answer}' di luar verdict_options.";
            }

            if ($type === 'find_object' && ! empty($item['decoy']) && empty($item['wrong'])) {
                $errors[] = "{$where}: objek jebakan wajib punya wrong_feedback.";
            }

            if ($type === 'ordering') {
                $keys = array_map(static fn ($p): string => (string) $p[0], $item['pieces']);
                $order = explode(',', $item['order']);
                sort($keys);
                sort($order);

                if ($keys !== $order) {
                    $errors[] = "{$where}: answer (urutan) harus memuat semua kode potongan tepat sekali.";
                }
            }
        }

        if ($node['engine'] === 'rumpang' && ! empty($node['use_word_bank'])) {
            foreach ($node['distractors'] as [$id]) {
                if (isset($answers[mb_strtolower($id)])) {
                    $errors[] = "{$ref}: pengecoh '{$id}' sama dengan jawaban {$answers[mb_strtolower($id)]}.";
                }
            }

            if (count($node['distractors']) < (int) $node['distractor_count']) {
                $errors[] = "{$ref}: pengecoh kurang dari distractor_count.";
            }
        }

        $targets = count(array_filter($node['items'], static fn ($i): bool => empty($i['decoy'])));

        if ($targets < $node['items_per_round']) {
            $errors[] = "{$ref}: bank {$targets} butir < items_per_round {$node['items_per_round']}.";
        }
    }

    foreach ($region['library'] as $index => $page) {
        $sequence = $index + 1;
        $sheets['library'][] = [
            'level_code' => $levelCode,
            'sequence'   => (string) $sequence,
            'title_id'   => $bi($page['title'], 0),
            'title_en'   => $bi($page['title'], 1),
            'body_id'    => $bi($page['body'], 0),
            'body_en'    => $bi($page['body'], 1),
            'is_active'  => '1',
        ];

        foreach ($page['media'] ?? [] as $m => $item) {
            $sheets['library_media'][] = [
                'level_code'       => $levelCode,
                'page_sequence'    => (string) $sequence,
                'sequence'         => (string) ($m + 1),
                'media_kind'       => $item['kind'] ?? 'image',
                'media_asset_key'  => '',
                'external_url'     => $item['url'],
                'poster_media_key' => '',
                'caption_id'       => $bi($item['caption'], 0),
                'caption_en'       => $bi($item['caption'], 1),
                'credit'           => $item['credit'],
            ];
        }
    }
}

// Semua butir: kunci unik lintas wilayah
$seen = [];

foreach ($sheets['items'] as $row) {
    if (isset($seen[$row['item_key']])) {
        $errors[] = "item_key kembar: {$row['item_key']}";
    }

    $seen[$row['item_key']] = true;
}

if ($errors !== []) {
    fwrite(STDERR, "Workbook TIDAK ditulis — perbaiki data:\n - " . implode("\n - ", $errors) . "\n");
    exit(1);
}

// Baris asosiatif → urutan header resmi
$rows = [];

foreach ($sheets as $name => $list) {
    $headers = BankWorkbookGuide::headers($name);
    $rows[$name] = array_map(static function (array $row) use ($headers, $name): array {
        $unknown = array_diff(array_keys($row), $headers);

        if ($unknown !== []) {
            throw new RuntimeException("Kolom tidak dikenal di {$name}: " . implode(', ', $unknown));
        }

        return array_map(static fn (string $h): ?string => ($row[$h] ?? '') === '' ? null : (string) $row[$h], $headers);
    }, $list);
}

ksort($media);
$mediaRows = [];

foreach ($media as $key => [$priority, $usedBy, $note, $size, $source]) {
    $mediaRows[] = [$key, $priority, $usedBy, $note, $size, $source];
}

(new BankWorkbookGuide())->write($rows, $output, [
    'title'    => 'Bank Soal GELITA — Produksi',
    'subtitle' => sprintf(
        'Isi lengkap 15 tantangan (%d butir, %d opsi, %d bacaan, %d petunjuk) dan Pustaka Kedu (%d halaman, %d media) untuk Temanggung, Magelang, dan Wonosobo. Profil skoring: bawaan studi (tidak diatur di workbook). Unggah gambar di sheet DAFTAR_MEDIA agar tantangan puzzle dan cari objek tampil utuh.',
        count($rows['items']),
        count($rows['options']),
        count($rows['passages']),
        count($rows['hints']),
        count($rows['library']),
        count($rows['library_media']),
    ),
], [
    'DAFTAR_MEDIA' => [
        'note'    => 'Gambar yang harus diunggah admin di Panel → Media dengan asset_key persis seperti kolom pertama. Impor workbook membuat slot kosongnya; begitu berkas diunggah, gambar langsung tampil. Pakai foto sendiri atau berlisensi bebas dan catat kreditnya.',
        'headers' => ['asset_key', 'Prioritas', 'Dipakai di', 'Isi gambar yang dibutuhkan', 'Ukuran & format', 'Saran sumber (lisensi bebas) / catatan'],
        'rows'    => $mediaRows,
        'widths'  => [30, 11, 22, 60, 26, 60],
    ],
]);

printf(
    "Workbook ditulis: %s\n  %d node · %d bacaan · %d butir · %d opsi · %d potongan · %d sumber · %d petunjuk · %d pengecoh · %d halaman pustaka · %d media pustaka · %d slot media\n",
    $output,
    count($rows['nodes']),
    count($rows['passages']),
    count($rows['items']),
    count($rows['options']),
    count($rows['pieces']),
    count($rows['sources']),
    count($rows['hints']),
    count($rows['distractors']),
    count($rows['library']),
    count($rows['library_media']),
    count($mediaRows),
);
