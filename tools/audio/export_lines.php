<?php

/**
 * Ekspor baris narasi yang direkam ke JSON untuk generator audio.
 *
 *   php tools/audio/export_lines.php > tools/audio/out/lines.json
 *
 * Sumbernya sama dengan permainan: naskah app/Database/Seeds/data/story.php
 * (88 baris) dan petunjuk target arena `cari` di bank soal produksi
 * (docs/bank-soal/data/*.php), dengan aturan penomoran NarrationImporter:
 * petunjuk-{node}-NN, NN = urutan butir find_object bukan jebakan, mulai 01.
 */
$root  = dirname(__DIR__, 2);
$lines = [];

foreach (require $root . '/app/Database/Seeds/data/story.php' as $row) {
    $lines[] = [
        'code'      => $row['code'],
        'context'   => $row['context'],
        'character' => $row['character'],
        'pose'      => $row['pose'],
        'effect'    => $row['effect'],
        'text_id'   => $row['text_id'],
        'text_en'   => $row['text_en'],
    ];
}

foreach (['temanggung', 'magelang', 'wonosobo'] as $region) {
    $bank = require $root . '/docs/bank-soal/data/' . $region . '.php';

    foreach ($bank['nodes'] as $nodeRef => $node) {
        if (($node['engine'] ?? null) !== 'cari') {
            continue;
        }

        $number = 0;

        foreach ($node['items'] as $item) {
            if (($item['type'] ?? null) !== 'find_object' || ! empty($item['decoy'])) {
                continue;
            }

            $lines[] = [
                'code'      => sprintf('petunjuk-%s-%02d', $nodeRef, ++$number),
                'context'   => 'hunt_clue',
                'character' => 'mbah_kedu',
                'pose'      => null,
                'effect'    => null,
                'text_id'   => $item['prompt'][0],
                'text_en'   => $item['prompt'][1],
            ];
        }
    }
}

echo json_encode($lines, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), "\n";
