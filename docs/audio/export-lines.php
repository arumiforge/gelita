<?php

/**
 * Daftar baris narasi yang harus direkam, sebagai JSON ke stdout — masukan
 * build_narration.py. Tanpa framework dan basis data: teks dibaca langsung
 * dari sumber tunggalnya di repositori, sama seperti yang diimpor ke
 * permainan.
 *
 *   php docs/audio/export-lines.php > baris.json
 *
 * Tiga kelompok, urutan dan kode berkas sama dengan
 * App\Libraries\NarrationImporter::lines():
 *
 * 1. naskah cerita (88 baris)   app/Database/Seeds/data/story.php
 * 2. kartu misi (15 baris)      docs/bank-soal/data/{wilayah}.php → `misi-{node}`;
 *                               teks = deskripsi, atau instruksi bila deskripsi kosong
 * 3. petunjuk arena cari (8)    butir find_object bukan jebakan node `cari` → `petunjuk-{node}-NN`
 *
 * Teks yang sudah disunting admin di panel tidak ikut: bila bank soal atau
 * naskah di basis data berbeda, perbarui berkas sumbernya dulu (lihat
 * docs/naskah-cerita.md), lalu buat ulang rekamannya.
 */

$root  = dirname(__DIR__, 2);
$story = require $root . '/app/Database/Seeds/data/story.php';
$lines = [];

$trimmed = static fn (?string $text): string => trim((string) $text);

foreach ($story as $row) {
    $lines[] = [
        'code'      => $row['code'],
        'context'   => $row['context'],
        'level'     => $row['level'],
        'character' => $row['character'],
        'pose'      => $row['pose'],
        'effect'    => $row['effect'],
        'text'      => ['id' => $row['text_id'], 'en' => $row['text_en']],
    ];
}

$missions = [];
$clues    = [];

foreach (['temanggung', 'magelang', 'wonosobo'] as $level) {
    $bank = require $root . '/docs/bank-soal/data/' . $level . '.php';

    foreach ($bank['nodes'] as $ref => $node) {
        // Sama dengan game/mission-brief.php: deskripsi, atau instruksi bila kosong; EN kosong → Indonesia
        $text = [];

        foreach (['id' => 0, 'en' => 1] as $locale => $i) {
            $description = $trimmed($node['description'][$i] ?? null) !== '' ? $node['description'][$i] : ($node['description'][0] ?? '');
            $instruction = $trimmed($node['instruction'][$i] ?? null) !== '' ? $node['instruction'][$i] : ($node['instruction'][0] ?? '');
            $text[$locale] = $description !== '' ? $description : $instruction;
        }

        if ($trimmed($text['id']) !== '') {
            $missions[] = [
                'code'      => 'misi-' . $ref,
                'context'   => 'mission_brief',
                'level'     => $level,
                'character' => 'narator',
                'pose'      => null,
                'effect'    => null,
                'text'      => $text,
            ];
        }

        if ($node['engine'] !== 'cari') {
            continue;
        }

        $number = 0;

        foreach ($node['items'] ?? [] as $item) {
            if (($item['type'] ?? null) !== 'find_object' || ! empty($item['decoy'])) {
                continue;
            }

            $clues[] = [
                'code'      => sprintf('petunjuk-%s-%02d', $ref, ++$number),
                'context'   => 'hunt_clue',
                'level'     => $level,
                'character' => 'mbah_kedu',
                'pose'      => null,
                'effect'    => null,
                'text'      => ['id' => $item['prompt'][0], 'en' => $trimmed($item['prompt'][1] ?? null) !== '' ? $item['prompt'][1] : $item['prompt'][0]],
            ];
        }
    }
}

echo json_encode(array_merge($lines, $missions, $clues), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), "\n";
