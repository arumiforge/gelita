<?php

/**
 * Penyusun daftar sekolah resmi Jawa Tengah untuk pendaftaran siswa (NPSN).
 *
 *   php docs/sekolah/build-sekolah-jateng.php [--source=URL_ATAU_FOLDER] [--out=FOLDER]
 *
 * Sumber bawaan: cermin Data Induk Pendidikan di github.com/bahrye/api-sekolah
 * (MIT; disinkronkan otomatis dari portal data induk pendidikan nasional, memuat
 * satuan pendidikan Kemendikdasmen dan Kemenag). `index.json` di sana menyebut
 * berkas tiap provinsi beserta jumlahnya; skrip ini mengunduh berkas Jawa
 * Tengah, menyaring satuan pendidikan formal SD/SMP sederajat dan SLB, lalu
 * menulis:
 *
 *   app/Database/Seeds/data/sekolah-jateng.csv        satu sekolah per baris
 *   app/Database/Seeds/data/sekolah-jateng.meta.json  sumber, tanggal, jumlah
 *
 * Kabupaten/kota dipetakan ke kode Kemendagri di public/assets/data/wilayah-id.json.
 * Berkas TIDAK ditulis bila ada kab/kota yang tak terpetakan, NPSN yang bukan
 * 8 angka, NPSN ganda, atau jumlah unduhan tidak sama dengan index.json.
 *
 * `--source` boleh berupa folder berisi index.json dan berkas provinsi yang
 * sudah diunduh sendiri (mis. dari jaringan yang memblokir GitHub).
 * Setelah berkas diperbarui, jalankan `php spark gelita:schools:import`.
 */

declare(strict_types=1);

const PROVINCE_NAME  = 'PROV. JAWA TENGAH';
const PROVINCE_CODE  = '33';
const DEFAULT_SOURCE = 'https://raw.githubusercontent.com/bahrye/api-sekolah/main/data_provinsi';
const SOURCE_LABEL   = 'Data Induk Pendidikan (Kemendikdasmen dan Kemenag) lewat cermin github.com/bahrye/api-sekolah (MIT)';

/** Grup bentuk pendidikan di sumber → jenjang GELITA. */
const STAGE_BY_GROUP = ['SD SEDERAJAT' => 'sd', 'SMP SEDERAJAT' => 'smp', 'SLB' => 'slb'];

/** Bentuk setara SMP yang di sumber kadang tercatat di grupnya sendiri. */
const STAGE_BY_FORM = ['SPM WUSTHA' => 'smp', 'PDF WUSTHA' => 'smp'];

const CSV_COLUMNS = ['npsn', 'nama', 'bentuk', 'jenjang', 'status', 'kode_kabupaten', 'kecamatan', 'desa'];

ini_set('memory_limit', '1G');

$options = getopt('', ['source:', 'out:']);
$source  = rtrim((string) ($options['source'] ?? DEFAULT_SOURCE), '/');
$outDir  = rtrim((string) ($options['out'] ?? dirname(__DIR__, 2) . '/app/Database/Seeds/data'), '/');

$fetch = static function (string $name) use ($source): string {
    $location = $source . '/' . $name;

    if (preg_match('#^https?://#', $source) !== 1) {
        $raw = is_file($location) ? file_get_contents($location) : false;

        if ($raw === false) {
            throw new RuntimeException("Tidak bisa membaca {$location}");
        }

        return $raw;
    }

    // curl mengikuti HTTPS_PROXY dan sertifikat sistem
    $handle = curl_init($location);
    curl_setopt_array($handle, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_FAILONERROR    => true,
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_TIMEOUT        => 600,
        CURLOPT_USERAGENT      => 'gelita-build-sekolah',
    ]);
    $raw = curl_exec($handle);

    if (! is_string($raw)) {
        throw new RuntimeException("Unduhan {$location} gagal: " . curl_error($handle));
    }

    return $raw;
};

$json = static fn (string $raw): array => json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

// "KAB. KUDUS", "Kabupaten Kudus", "KOTA SEMARANG" → "KAB|KUDUS", "KOTA|SEMARANG"
$districtKey = static function (string $label): string {
    $label = mb_strtoupper(trim((string) preg_replace('/\s+/u', ' ', $label)));

    if (preg_match('/^(KABUPATEN|KAB\.?)\s+(.+)$/u', $label, $m) === 1) {
        return 'KAB|' . $m[2];
    }

    if (preg_match('/^KOTA\s+(.+)$/u', $label, $m) === 1) {
        return 'KOTA|' . $m[1];
    }

    return $label;
};

$clean = static fn (mixed $value): string => trim((string) preg_replace('/\s+/u', ' ', (string) $value));

// ------------------------------------------------------------------ wilayah
$wilayah   = $json((string) file_get_contents(dirname(__DIR__, 2) . '/public/assets/data/wilayah-id.json'));
$districts = [];

foreach ($wilayah['provinces'] as $province) {
    if ($province['code'] !== PROVINCE_CODE) {
        continue;
    }

    foreach ($province['districts'] as $district) {
        $districts[$districtKey($district['name'])] = $district['code'];
    }
}

if ($districts === []) {
    fwrite(STDERR, 'Provinsi ' . PROVINCE_CODE . " tidak ada di wilayah-id.json.\n");
    exit(1);
}

// ------------------------------------------------------------------ unduh
$index = $json($fetch('index.json'));
$entry = null;

foreach ($index['provinsi'] ?? [] as $row) {
    if (($row['nama'] ?? null) === PROVINCE_NAME) {
        $entry = $row;
        break;
    }
}

if ($entry === null || ($entry['files'] ?? []) === []) {
    fwrite(STDERR, PROVINCE_NAME . " tidak ada di index.json sumber.\n");
    exit(1);
}

$records = [];

foreach ($entry['files'] as $file) {
    $part = $json($fetch($file));
    fwrite(STDOUT, sprintf("  %s: %d lembaga\n", $file, count($part)));
    array_push($records, ...$part);
}

$errors = [];

if (count($records) !== (int) $entry['total']) {
    $errors[] = sprintf('jumlah unduhan %d, index.json menyebut %d', count($records), $entry['total']);
}

// ------------------------------------------------------------------ saring
$rows = [];
$seen = [];

foreach ($records as $record) {
    if (($record['jalur_pendidikan'] ?? null) !== 'FORMAL') {
        continue;
    }

    $form  = $clean($record['bentuk_pendidikan'] ?? '');
    $stage = STAGE_BY_GROUP[$record['bentuk_pendidikan_group'] ?? ''] ?? STAGE_BY_FORM[$form] ?? null;

    if ($stage === null) {
        continue;
    }

    $npsn = $clean($record['npsn'] ?? '');
    $name = $clean($record['nama'] ?? '');

    if (($record['nama_provinsi'] ?? null) !== PROVINCE_NAME) {
        $errors[] = "NPSN {$npsn}: provinsi {$record['nama_provinsi']}";

        continue;
    }

    if (preg_match('/^\d{8}$/', $npsn) !== 1) {
        $errors[] = "NPSN tidak 8 angka: '{$npsn}' ({$name})";

        continue;
    }

    if (isset($seen[$npsn])) {
        $errors[] = "NPSN ganda: {$npsn}";

        continue;
    }

    $districtCode = $districts[$districtKey((string) ($record['nama_kabupaten'] ?? ''))] ?? null;

    if ($districtCode === null) {
        $errors[] = "kab/kota tak terpetakan: '{$record['nama_kabupaten']}' (NPSN {$npsn})";

        continue;
    }

    if ($name === '') {
        $errors[] = "NPSN {$npsn} tanpa nama";

        continue;
    }

    $seen[$npsn] = true;
    $rows[]      = [
        'npsn'           => $npsn,
        'nama'           => $name,
        'bentuk'         => $form,
        'jenjang'        => $stage,
        'status'         => $clean($record['status_satuan_pendidikan'] ?? ''),
        'kode_kabupaten' => $districtCode,
        'kecamatan'      => (string) preg_replace('/^KEC\.?\s+/u', '', $clean($record['nama_kecamatan'] ?? '')),
        'desa'           => $clean($record['nama_desa'] ?? ''),
    ];
}

if ($errors !== []) {
    fwrite(STDERR, "Berkas TIDAK ditulis — periksa sumber:\n - " . implode("\n - ", array_slice($errors, 0, 50)) . "\n");
    exit(1);
}

usort($rows, static fn (array $a, array $b): int => [$a['kode_kabupaten'], $a['kecamatan'], $a['nama'], $a['npsn']]
    <=> [$b['kode_kabupaten'], $b['kecamatan'], $b['nama'], $b['npsn']]);

// ------------------------------------------------------------------ tulis
if (! is_dir($outDir) && ! mkdir($outDir, 0775, true)) {
    fwrite(STDERR, "Tidak bisa membuat folder {$outDir}\n");
    exit(1);
}

$csv = fopen($outDir . '/sekolah-jateng.csv', 'wb');
fputcsv($csv, CSV_COLUMNS, ',', '"', '');

foreach ($rows as $row) {
    fputcsv($csv, array_values($row), ',', '"', '');
}

fclose($csv);

$count = static function (string $column) use ($rows): array {
    $counts = array_count_values(array_column($rows, $column));
    arsort($counts);

    return $counts;
};

$meta = [
    'source'           => SOURCE_LABEL,
    'source_url'       => $source,
    'upstream_updated' => $index['last_updated'] ?? null,
    'generated'        => date('Y-m-d'),
    'province'         => ['code' => PROVINCE_CODE, 'name' => 'Jawa Tengah'],
    'total'            => count($rows),
    'by_stage'         => $count('jenjang'),
    'by_form'          => $count('bentuk'),
];

file_put_contents(
    $outDir . '/sekolah-jateng.meta.json',
    json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n",
);

printf(
    "Selesai: %d sekolah (%s) dari %d lembaga, %d kab/kota.\n  %s\n",
    count($rows),
    implode(', ', array_map(static fn ($k, $v) => "{$k} {$v}", array_keys($meta['by_stage']), $meta['by_stage'])),
    count($records),
    count(array_unique(array_column($rows, 'kode_kabupaten'))),
    $outDir . '/sekolah-jateng.csv',
);
