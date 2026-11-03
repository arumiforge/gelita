<?php

/**
 * Penyusun workbook bank soal kelas 5 GELITA (Dok 2 §7; bank baru revisi 10, 31 §7.2).
 *
 *   php docs/bank-soal/build-workbook.php [keluaran.xlsx]
 *
 * Sumber isi: docs/bank-soal/data/{temanggung,magelang,wonosobo}.php (format
 * 31 §6.4, isi dari 08). Skrip ini meratakannya menjadi sheet impor
 * (ContentImportService::SHEETS) lewat App\Libraries\BankSource, menurunkan
 * matriks kode kesalahan lengkap (31 K6), memeriksa seluruh aturan 31 §8.2
 * (App\Libraries\BankRules — aturan yang sama dengan importer dan
 * gelita:content:verify), lalu menulis
 * workbook berpanduan lewat App\Libraries\BankWorkbookGuide (sheet PETUNJUK,
 * KAMUS_KOLOM, catatan header, daftar pilihan) plus lampiran DAFTAR_MEDIA
 * (slot gambar tantangan).
 *
 * Galat → workbook TIDAK ditulis (kode keluar 1). Peringatan (mis. Bloom di
 * luar rentang tingkat) dicetak, workbook tetap ditulis.
 *
 * Workbook kelas 5 sengaja TANPA sheet library/library_media: gelita:reset
 * dan impor bank soal tidak pernah menyentuh Pustaka (Dok 1 §2). Isi
 * Pustaka untuk pemasangan baru ada di docs/pustaka/.
 */

declare(strict_types=1);

use App\Libraries\BankRules;
use App\Libraries\BankSource;
use App\Libraries\BankWorkbookGuide;

require __DIR__ . '/../../vendor/autoload.php';

$output = $argv[1] ?? __DIR__ . '/gelita-bank-soal-kelas5.xlsx';
$flat   = BankSource::flatten(BankSource::load(__DIR__ . '/data'));

$errors   = [];
$warnings = [];

foreach (BankSource::problems($flat) as $problem) {
    if ($problem['level'] === 'error') {
        $errors[] = BankRules::format($problem);
    } else {
        $warnings[] = BankRules::format($problem);
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Workbook TIDAK ditulis — perbaiki data:\n - " . implode("\n - ", $errors) . "\n");
    exit(1);
}

if ($warnings !== []) {
    fwrite(STDERR, "Peringatan (workbook tetap ditulis):\n - " . implode("\n - ", $warnings) . "\n");
}

// Baris asosiatif → urutan header resmi
$rows = [];

foreach ($flat['sheets'] as $name => $list) {
    $headers     = BankWorkbookGuide::headers($name);
    $rows[$name] = array_map(static function (array $row) use ($headers, $name): array {
        $unknown = array_diff(array_keys($row), $headers);

        if ($unknown !== []) {
            throw new RuntimeException("Kolom tidak dikenal di {$name}: " . implode(', ', $unknown));
        }

        return array_map(static fn (string $h): ?string => ($row[$h] ?? '') === '' ? null : (string) $row[$h], $headers);
    }, $list);
}

$items    = $flat['sheets']['items'];
$active   = array_filter($items, static fn (array $row): bool => $row['is_active'] === '1');
$scored   = array_filter($active, static fn (array $row): bool => $row['scorable'] === '1' && $row['interaction_type'] !== 'puzzle_arrange');
$literacy = count(array_filter($scored, static fn (array $row): bool => $row['dimension'] !== 'PBK'));
$puzzle   = array_count_values(array_column(array_filter($flat['sheets']['variants'], static fn (array $row): bool => str_ends_with($row['node_ref'], '-1')), 'node_ref'));

(new BankWorkbookGuide())->write($rows, $output, [
    'title'    => 'Bank Soal GELITA — Kelas 5',
    'subtitle' => sprintf(
        'Bank soal penelitian kelas 5 SD/MI, revisi 10 (dari draf 08; semua butir needs_verification sampai validasi ahli; teks Inggris draf AI dari 36): 15 tantangan (5 per wilayah), %d butir literasi digital dinilai (D1, D2, D3) + %d butir PBK, %d benda pengecoh, %d variasi Susun Gambar (paling banyak %d per wilayah), %d bacaan, %d kode kesalahan pilihan, %d bagian ketuk unggahan, %d pilihan skenario. Pengerjaan pertama tantangan 2–5 mengikuti buklet siswa selama penelitian. Gambar di sheet DAFTAR_MEDIA mula-mula tampil sebagai gambar contoh; unggah gambar asli dengan kode berkas yang sama di menu Gambar & mockup.',
        $literacy,
        count($scored) - $literacy,
        count($active) - count($scored) - array_sum($puzzle),
        array_sum($puzzle),
        max($puzzle ?: [0]),
        count($rows['passages']),
        count($rows['cloze_matrix']) + count($rows['object_codes']) + count($rows['part_codes']),
        count($rows['post_parts']),
        count($rows['scenario_options']),
    ),
    'sheets' => array_keys($flat['sheets']),
], [
    'DAFTAR_MEDIA' => [
        'note'    => 'Gambar tantangan yang perlu disiapkan admin (Dok 2 §10.4, 08 → Gambar yang dibutuhkan). Saat berkas ini diimpor, slotnya dibuat dengan nama dan isi gambar yang diharapkan; php spark gelita:mockups lalu memasang gambar contoh. Unggah gambar asli di Panel GELITA → menu Gambar & mockup dengan kode berkas persis seperti kolom pertama: gambar asli langsung tampil dan tidak pernah ditimpa gambar contoh. Pakai foto/ilustrasi sendiri atau berlisensi bebas, dan catat pemiliknya. Gambar Susun Gambar: persegi tepat 1200 × 1200 px, WebP; prompt dan uji kepingnya ada di panduan gambar puzzle.',
        'headers' => ['Kode berkas', 'Nama gambar', 'Wilayah · tantangan', 'Dipakai di', 'Isi gambar asli', 'Ukuran & format', 'Saran sumber / catatan'],
        'rows'    => BankSource::mediaRows($flat['media']),
        'widths'  => [30, 26, 26, 24, 50, 30, 50],
    ],
]);

printf(
    "Workbook ditulis: %s\n  %d tantangan · %d butir literasi digital + %d PBK · %d pengecoh · %d Susun Gambar · %d bacaan · %d kode pilihan · %d bagian ketuk · %d pilihan skenario · %d slot gambar · %d peringatan\n",
    $output,
    count($rows['nodes']),
    $literacy,
    count($scored) - $literacy,
    count($active) - count($scored) - array_sum($puzzle),
    array_sum($puzzle),
    count($rows['passages']),
    count($rows['cloze_matrix']) + count($rows['object_codes']) + count($rows['part_codes']),
    count($rows['post_parts']),
    count($rows['scenario_options']),
    count($flat['media']),
    count($warnings),
);
