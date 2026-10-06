<?php

/**
 * Penyusun workbook Pustaka Kedu GELITA.
 *
 *   php docs/pustaka/build-workbook.php [keluaran.xlsx]
 *
 * Sumber isi: docs/pustaka/data/{temanggung,magelang,wonosobo}.php — array
 * `library` yang dipindahkan apa adanya dari berkas data bank soal lama saat
 * perombakan kelas 5 (Dok 2 §7.1). Workbook ini (sheet library dan
 * library_media saja) dipakai HANYA untuk pemasangan baru: impor lewat
 * Panel GELITA → Impor soal dari Excel atau `php spark gelita:bank:import`.
 * Server yang sudah berjalan tidak mengimpornya lagi, karena impor menimpa
 * halaman Pustaka bernomor sama (termasuk suntingan admin).
 */

declare(strict_types=1);

use App\Libraries\BankWorkbookGuide;

require __DIR__ . '/../../vendor/autoload.php';

$output  = $argv[1] ?? __DIR__ . '/gelita-pustaka.xlsx';
$regions = ['temanggung', 'magelang', 'wonosobo'];
$sheets  = ['library' => [], 'library_media' => []];

$bi = static fn (array|string|null $pair, int $i): string => is_array($pair) ? (string) ($pair[$i] ?? '') : ($i === 0 ? (string) $pair : '');

foreach ($regions as $levelCode) {
    $data = require __DIR__ . '/data/' . $levelCode . '.php';

    foreach ($data['library'] as $index => $page) {
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

$rows = [];

foreach ($sheets as $name => $list) {
    $headers     = BankWorkbookGuide::headers($name);
    $rows[$name] = array_map(
        static fn (array $row): array => array_map(static fn (string $h): ?string => ($row[$h] ?? '') === '' ? null : (string) $row[$h], $headers),
        $list,
    );
}

(new BankWorkbookGuide())->write($rows, $output, [
    'title'    => 'Pustaka Kedu GELITA',
    'subtitle' => sprintf(
        'Isi Pustaka Kedu untuk Temanggung, Magelang, dan Wonosobo (%d halaman, %d gambar/video). Hanya untuk pemasangan baru: server yang sudah berjalan tidak mengimpor berkas ini lagi, karena impor menimpa halaman Pustaka bernomor sama.',
        count($rows['library']),
        count($rows['library_media']),
    ),
    'sheets' => array_keys($sheets),
]);

printf("Workbook ditulis: %s\n  %d halaman pustaka · %d media pustaka\n", $output, count($rows['library']), count($rows['library_media']));
