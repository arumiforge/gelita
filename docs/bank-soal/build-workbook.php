<?php

/**
 * Penyusun workbook bank soal kelas 5 GELITA (perombakan kelas 5, Dok 2 §7).
 *
 *   php docs/bank-soal/build-workbook.php [keluaran.xlsx]
 *
 * Sumber isi: docs/bank-soal/data/{temanggung,magelang,wonosobo}.php (format
 * Dok 2 §7.2, isi final §9). Skrip ini meratakannya menjadi sheet impor
 * (ContentImportService::SHEETS) lewat App\Libraries\BankSource, memberi kode
 * soal/bacaan otomatis, memeriksa seluruh aturan §7.5 (App\Libraries\BankRules —
 * aturan yang sama dengan importer dan gelita:content:verify), lalu menulis
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

$active   = array_filter($flat['sheets']['items'], static fn (array $row): bool => $row['is_active'] === '1');
$scorable = count(array_filter($active, static fn (array $row): bool => $row['scorable'] === '1'));
$puzzle   = array_count_values(array_column(array_filter($flat['sheets']['variants'], static fn (array $row): bool => str_ends_with($row['node_ref'], '-1')), 'node_ref'));

(new BankWorkbookGuide())->write($rows, $output, [
    'title'    => 'Bank Soal GELITA — Kelas 5',
    'subtitle' => sprintf(
        'Bank soal kelas 5 SD/MI, revisi Oktober 2026 (soal yang diubah atau ditambahkan berstatus needs_verification sampai dicek peneliti): 12 tantangan (4 per wilayah), %d baris soal — %d aktif (%d dinilai + %d benda pengecoh) dan %d diistirahatkan — %d variasi (Susun Gambar %d variasi per wilayah), %d bacaan Benar atau Salah, %d petunjuk, dan %d kata pengecoh. Setiap pengerjaan memakai satu variasi acak. Gambar yang tercantum di sheet DAFTAR_MEDIA mula-mula tampil sebagai gambar contoh; unggah gambar asli dengan kode berkas yang sama di menu Gambar & mockup.',
        count($rows['items']),
        count($active),
        $scorable,
        count($active) - $scorable,
        count($rows['items']) - count($active),
        count($rows['variants']),
        max($puzzle ?: [0]),
        count($rows['passages']),
        count($rows['hints']),
        count($rows['distractors']),
    ),
    'sheets' => array_keys($flat['sheets']),
], [
    'DAFTAR_MEDIA' => [
        'note'    => 'Gambar tantangan yang perlu disiapkan admin (Dok 2 §10.4). Saat berkas ini diimpor, slotnya dibuat dengan nama dan isi gambar yang diharapkan; php spark gelita:mockups lalu memasang gambar contoh. Unggah gambar asli di Panel GELITA → menu Gambar & mockup dengan kode berkas persis seperti kolom pertama: gambar asli langsung tampil dan tidak pernah ditimpa gambar contoh. Pakai foto/ilustrasi sendiri atau berlisensi bebas, dan catat pemiliknya. Gambar Susun Gambar: persegi tepat 1200 × 1200 px, WebP; prompt dan uji kepingnya ada di panduan gambar puzzle.',
        'headers' => ['Kode berkas', 'Nama gambar', 'Wilayah · tantangan', 'Dipakai di', 'Isi gambar asli', 'Ukuran & format', 'Saran sumber / catatan'],
        'rows'    => BankSource::mediaRows($flat['media']),
        'widths'  => [30, 26, 26, 24, 50, 30, 50],
    ],
]);

printf(
    "Workbook ditulis: %s\n  %d tantangan · %d variasi · %d bacaan · %d soal aktif (%d dinilai + %d pengecoh) + %d diistirahatkan · %d petunjuk · %d kata pengecoh · %d slot gambar · %d peringatan\n",
    $output,
    count($rows['nodes']),
    count($rows['variants']),
    count($rows['passages']),
    count($active),
    $scorable,
    count($active) - $scorable,
    count($rows['items']) - count($active),
    count($rows['hints']),
    count($rows['distractors']),
    count($flat['media']),
    count($warnings),
);
