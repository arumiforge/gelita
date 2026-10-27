<?php

/**
 * Builds the validator workbooks for revision 10 stage 2 (document 21).
 *
 *   php docs/revisi-10-analitik/dokumen-pdf/src/berkas-validator.php [--no-pdf]
 *
 * Reads the item documents (03, 08, 14–18) and the three instrument HTML
 * files (read-only: column headers and fixed rows), runs the checks, and
 * writes everything to docs/revisi-10-analitik/dokumen-pdf/berkas-validator/.
 * Any failed check stops the run with exit code 1 before files are written.
 * Needs `composer install` (PhpSpreadsheet); the PDF booklet needs the
 * pre-installed Playwright Chromium (never run `playwright install`).
 */

declare(strict_types=1);

namespace BerkasValidator;

use PhpOffice\PhpSpreadsheet\IOFactory;

$root = dirname(__DIR__, 4);
require $root . '/vendor/autoload.php';
require __DIR__ . '/berkas-validator/Markdown.php';
require __DIR__ . '/berkas-validator/Instrument.php';
require __DIR__ . '/berkas-validator/Sources.php';
require __DIR__ . '/berkas-validator/Book.php';

const SEED     = 20261027;        // shuffle seed for B.5a; changing it changes every neutral ID
const VERSION  = 'berkas-1.0';
const MATERI   = ['Ahli Bahasa Anak 1', 'Ahli Bahasa Anak 2', 'Ahli Literasi Digital 1', 'Ahli Literasi Digital 2', 'Ahli Pengukuran'];
const BUDAYA   = ['Ahli Budaya Kedu 1', 'Ahli Budaya Kedu 2', 'Ahli Budaya Kedu 3'];
const MEDIA    = ['Ahli Media Pembelajaran 1', 'Ahli Media Pembelajaran 2', 'Ahli Media Pembelajaran 3'];
const SIKAP    = ['Ahli Budaya Kedu 1', 'Ahli Pengukuran', 'Ahli Bahasa Anak 1', 'Ahli Bahasa Anak 2', 'Ahli Literasi Digital 1', 'Ahli Literasi Digital 2'];
const SCORE    = ['1', '2', '3', '4', 'TD'];
const YT       = ['Y', 'T'];
const NA       = '—';

$docs   = dirname(__DIR__, 2);
$out    = dirname(__DIR__) . '/berkas-validator';
$noPdf  = in_array('--no-pdf', $argv, true);

$src     = new Sources($docs);
$materi  = new Instrument(__DIR__ . '/instrumen-validasi-ahli-materi-v2.1.html');
$budaya  = new Instrument(__DIR__ . '/instrumen-validasi-pengamat-budaya-v2.1.html');
$media   = new Instrument(__DIR__ . '/instrumen-validasi-ahli-media-v1.1.html');
$IM      = 'Instrumen Validasi Ahli Materi v2.1';
$IB      = 'Instrumen Validasi Pengamat Budaya v2.1';
$IMD     = 'Instrumen Validasi Ahli Media v1.1';

$hashInput = '';
foreach (array_merge($src->files(), [$materi->path, $budaya->path, $media->path]) as $f) {
    $hashInput .= basename($f) . "\0" . file_get_contents($f);
}
$version = VERSION . ' · sumber ' . substr(sha1($hashInput), 0, 8);

$errors = [];   // failed checks: the run stops
$notes  = [];   // findings for the owner: reported, the run goes on
$report = [];   // check name => result line for LAPORAN.md
$check  = function (string $name, bool $ok, string $detail) use (&$errors, &$report) {
    $report[] = [$name, $ok ? 'lolos' : 'GAGAL', $detail];
    if (! $ok) {
        $errors[] = "{$name}: {$detail}";
    }
};

// ------------------------------------------------------------ shared data

$regionOrder = ['tmg' => 0, 'mgl' => 1, 'wnb' => 2];
$bank = $src->itemsOf('bank');
usort($bank, fn ($a, $b) => [$regionOrder[substr($a['code'], 0, 3)], $a['code']] <=> [$regionOrder[substr($b['code'], 0, 3)], $b['code']]);
$tests = $src->itemsOf('tes');
usort($tests, fn ($a, $b) => [$a['type'], substr($a['code'], 7, 1), $a['code']] <=> [$b['type'], substr($b['code'], 7, 1), $b['code']]);
$tests = array_merge(
    array_values(array_filter($tests, fn ($i) => $i['type'] === 'tes-mb')),
    array_values(array_filter($tests, fn ($i) => $i['type'] === 'tes-ae')),
    array_values(array_filter($tests, fn ($i) => $i['type'] === 'tes-bd')),
);
$labelled = array_merge($bank, $tests);   // B.5a and B.5b

// Neutral IDs: blocks (stimulus with its items) in a seeded shuffle.
$blockKeys = array_values(array_unique(array_column($labelled, 'block')));
mt_srand(SEED, MT_RAND_MT19937);
for ($i = count($blockKeys) - 1; $i > 0; $i--) {
    $j = mt_rand(0, $i);
    [$blockKeys[$i], $blockKeys[$j]] = [$blockKeys[$j], $blockKeys[$i]];
}
$byBlock = [];
foreach ($labelled as $it) {
    $byBlock[$it['block']][] = $it;
}
// items keep their document order inside a block
foreach ($byBlock as &$list) {
    usort($list, fn ($a, $b) => strcmp($a['code'], $b['code']));
}
unset($list);
$blind = [];        // list of [neutralItem, neutralBlock, item]
$blockIds = [];
$n = 0;
foreach ($blockKeys as $bi => $key) {
    $blockIds[$key] = sprintf('BH-%02d', $bi + 1);
    foreach ($byBlock[$key] as $it) {
        $blind[] = [sprintf('BT-%03d', ++$n), $blockIds[$key], $it];
    }
}

// ------------------------------------------------------------ checks: counts

$count = fn (string $type) => count(array_filter($src->items, fn ($i) => $i['type'] === $type));
$bankCount = count($bank);
$testCount = count($tests);
preg_match('/Yang dilabeli: (\d+) butir bank soal .*?(\d+) butir jangkar, (\d+) petunjuk Periksa Unggahan, (\d+) Skenario Digital\) dan (\d+) butir tes awal–akhir \((\d+) per bentuk\)/u', $materi->text(), $m);
$check('B.5a jumlah butir bank', $bankCount === (int) ($m[1] ?? -1), "{$bankCount} dari 08, instrumen menyebut " . ($m[1] ?? '?'));
$check('B.5a jumlah butir tes', $testCount === (int) ($m[5] ?? -1), "{$testCount} dari 14–15, instrumen menyebut " . ($m[5] ?? '?'));
$check('B.5a butir per bentuk tes', count(array_filter($tests, fn ($i) => str_contains($i['code'], '-A'))) === (int) ($m[6] ?? -1)
    && count(array_filter($tests, fn ($i) => str_contains($i['code'], '-B'))) === (int) ($m[6] ?? -1), 'bentuk A dan B masing-masing ' . ($m[6] ?? '?'));
$check('B.5a rincian bank (jangkar, Periksa Unggahan, Skenario)', [$count('jangkar'), $count('unggahan'), $count('skenario')] === [(int) ($m[2] ?? -1), (int) ($m[3] ?? -1), (int) ($m[4] ?? -1)],
    "{$count('jangkar')} jangkar, {$count('unggahan')} petunjuk, {$count('skenario')} skenario");
$t08 = $src->bankTotals;
$check('08 tabel Jumlah', [$count('rumpang'), $count('boleh'), $count('jangkar'), $count('unggahan'), $count('skenario'), $bankCount, count($src->pbk)]
    === [$t08['Isi Rumpang (per variasi × 2)'] ?? -1, $t08['Benar atau Salah (per variasi × 2)'] ?? -1, $t08['Kartu jangkar'] ?? -1, $t08['Periksa Unggahan (D3)'] ?? -1, $t08['Skenario Digital (D2)'] ?? -1, $t08['Butir literasi digital'] ?? -1, $t08['Temukan Benda (PBK, dari bank lama)'] ?? -1],
    "Isi Rumpang {$count('rumpang')}, Benar atau Salah {$count('boleh')}, jangkar {$count('jangkar')}, Periksa Unggahan {$count('unggahan')}, Skenario {$count('skenario')}, PBK " . count($src->pbk));
preg_match('/Tiap bentuk punya (\d+) butir dalam tiga bagian: Bagian 1 Membaca \(D1\) (\d+) butir .*?Bagian 2 Aman dan etis \(D2\) (\d+) skenario .*?Bagian 3 Budaya digital \(D3\) (\d+) butir/u', $materi->text(), $m);
$per = fn ($t) => count(array_filter($tests, fn ($i) => $i['type'] === $t)) / 2;
$check('B.9 susunan tes per bentuk', [$per('tes-mb'), $per('tes-ae'), $per('tes-bd')] == [(int) ($m[2] ?? -1), (int) ($m[3] ?? -1), (int) ($m[4] ?? -1)],
    "Membaca {$per('tes-mb')}, Aman dan etis {$per('tes-ae')}, Budaya digital {$per('tes-bd')} per bentuk");

$d23 = array_values(array_filter($tests, fn ($i) => in_array($i['type'], ['tes-ae', 'tes-bd'], true)));
$single = array_filter($d23, fn ($i) => in_array($i['level'], ['L1', 'L2', 'L3'], true));
$pairOk = true;
foreach ($d23 as $i) {
    $twin = substr_replace($i['code'], substr($i['code'], 7, 1) === 'A' ? 'B' : 'A', 7, 1);
    $pairOk = $pairOk && ($src->items[$twin]['level'] ?? null) === $i['level'];
}
$check('Level pengembang butir tes D2 dan D3', count($d23) === 32 && count($single) === 32 && $pairOk,
    count($single) . ' dari ' . count($d23) . ' butir punya satu level (L1–L3); pasangan A dan B ' . ($pairOk ? 'sama' : 'BERBEDA'));

// ------------------------------------------------------------ B.5a blind workbook

$b5aHead = $materi->table('B.5a', 1)['head'];
$check('B.5a tata letak instrumen', $b5aHead === ['No', 'Kode butir', 'Dimensi', 'Sub-aspek D1', 'Level', 'Bloom', 'Catatan (mis. butir sulit dilabel, ragu antara dua label)'], implode(' | ', $b5aHead));
$bloomRef = $materi->table('B.5a', 0);

$labelGuide = [
    'Dimensi' => 'Satu dimensi utama saja: D1, D2, atau D3 (model multidimensi between-item).',
    'Sub-aspek D1' => 'Hanya untuk butir D1: D1a memahami informasi (menemukan informasi; interpretasi dan integrasi) atau D1b menilai informasi dan sumber (fakta dan pendapat, kredibilitas sumber, kabar yang bertentangan).',
    'Level' => 'Butir D1: L1 menemukan informasi (MI) · L2 interpretasi dan integrasi (II) · L3 evaluasi dan refleksi (ER). Butir D2 dan D3: kerumitan situasi atau adegan dengan padanan L1–L3 (L1 tanda yang jelas, L2 tanda yang samar, L3 nilai yang bertentangan atau dua langkah).',
    'Bloom' => 'C1–C6 (Anderson & Krathwohl, 2001), menurut acuan di kanan. Butir tes tidak diberi label Bloom.',
];
foreach ($labelGuide as $k => $v) {
    $plain = preg_replace('/\s+/u', ' ', $v);
    $check("B.5a definisi {$k} sama dengan instrumen", str_contains($materi->text(), $plain), $k);
}

$b5aGuide = [
    '## Lembar pelabelan buta (B.5a)',
    'Diisi dua penilai yang ditunjuk peneliti, sebelum melihat label pengembang, kisi-kisi, atau daftar B.5b. Isi kolom Dimensi, Sub-aspek D1, Level, dan Bloom untuk setiap butir di lembar "B.5a", memakai daftar pilihan di setiap sel.',
    'Setiap butir punya ID netral (BT-…) dan merujuk ke satu bahan (BH-…) di lembar "Bahan": bacaan, layar, unggahan, atau cerita yang dibaca siswa. Urutan bahan diacak, jadi urutannya tidak menunjukkan wilayah, tantangan, atau bagian tes.',
    'Kolom Kunci memuat jawaban yang benar, supaya proses yang dilakukan siswa bisa dinilai. Kode kesalahan pengembang tidak dicantumkan.',
    'Butir tes tidak diberi label Bloom; selnya sudah berisi "—".',
    'Nama bagian ketuk di unggahan ditulis "Bagian 1", "Bagian 2", dan seterusnya, bukan huruf di dokumen pengembang.',
    '## Arti label',
];
foreach ($labelGuide as $k => $v) {
    $b5aGuide[] = [$k, $v];
}
$b5aGuide[] = '## Acuan proses kognitif untuk label Bloom (dari instrumen)';
$b5aGuide[] = $bloomRef['head'];
foreach ($bloomRef['rows'] as $r) {
    $b5aGuide[] = $r;
}
$b5aGuide[] = 'Tiga prinsip: (1) beri label menurut proses yang benar-benar dilakukan siswa, bukan kata kerja di instruksi; (2) pilihan tertutup biasanya paling tinggi C4, dan C5 hanya untuk memeriksa simpulan terhadap bukti atau menimbang dua nilai yang bertentangan; (3) Bloom bukan tingkat kesulitan.';
$b5aGuide[] = '## Cara mengembalikan';
$b5aGuide[] = 'Simpan berkas dengan nama samaran Anda (mis. "B5a – Ahli Pengukuran.xlsx") dan kirim ke peneliti. Bila mengisi buklet PDF di atas kertas, tulis label di kolom kosong buklet.';

$b5a = new Book('Daftar butir tanpa label untuk pelabelan buta (B.5a)', $version);
$b5a->guide('Petunjuk', $b5aGuide);
$rows = [];
foreach ($blockKeys as $key) {
    $rows[] = [$blockIds[$key], $src->blocks[$key]['text']];
}
$b5a->sheet('Bahan', 'Bahan bacaan, layar, dan adegan', $IM, MATERI, [
    ['head' => 'ID bahan', 'width' => 10, 'ref' => true],
    ['head' => 'Teks bahan (seperti dibaca siswa)', 'width' => 120, 'ref' => true],
], $rows);
$rows = [];
foreach ($blind as $k => [$id, $bid, $it]) {
    $rows[] = [(string) ($k + 1), $id, $bid, $it['text'], $it['options'], $it['key'], '', '', '', $it['part'] === 'tes' ? NA . ' (butir tes)' : '', ''];
}
$b5aCols = [
    ['head' => 'No', 'width' => 5],
    ['head' => 'ID butir', 'width' => 9, 'ref' => true],
    ['head' => 'ID bahan', 'width' => 9, 'ref' => true],
    ['head' => 'Butir (teks untuk siswa)', 'width' => 48, 'ref' => true],
    ['head' => 'Pilihan atau bank kata', 'width' => 42, 'ref' => true],
    ['head' => 'Kunci', 'width' => 26, 'ref' => true],
    ['head' => 'Dimensi', 'width' => 10, 'list' => ['D1', 'D2', 'D3']],
    ['head' => 'Sub-aspek D1', 'width' => 11, 'list' => ['D1a', 'D1b', NA]],
    ['head' => 'Level', 'width' => 8, 'list' => ['L1', 'L2', 'L3']],
    ['head' => 'Bloom', 'width' => 10, 'list' => ['C1', 'C2', 'C3', 'C4', 'C5', 'C6']],
    ['head' => 'Catatan (mis. butir sulit dilabel, ragu antara dua label)', 'width' => 36],
];
$b5a->sheet('B.5a', 'B.5a Lembar pelabelan buta per butir', $IM, MATERI, $b5aCols, $rows);
$files['materi-b5a-daftar-butir-buta.xlsx'] = $b5a;

// researcher-only key
$key = new Book('Kunci B.5a: ID netral ke kode butir dan label pengembang (KHUSUS PENELITI)', $version);
$key->guide('Petunjuk', [
    '## Khusus peneliti. Jangan dikirim ke validator.',
    'Berkas ini memetakan ID netral di daftar buta B.5a (BT-…, BH-…) ke kode butir dan label pengembang, untuk menghitung κ di C.4 setelah kedua lembar B.5a kembali.',
    "Pengacakan: urutan bahan diacak dengan benih tetap " . SEED . " (PHP mt_rand, MT19937); butir di dalam satu bahan tetap urut. Benih yang sama dan sumber yang sama selalu menghasilkan ID yang sama.",
    'Level butir Periksa Unggahan dan Skenario Digital diturunkan dari baris "Tingkat" 08 per wilayah (jelas = L1, samar = L2, nilai bertentangan atau dua langkah = L3), mengikuti arti Level di B.5a. Level butir tes D2 dan D3 diambil dari 15 → Level pengembang: satu level per nomor, sama di bentuk A dan B.',
]);
$rows = [];
foreach ($blind as [$id, $bid, $it]) {
    $rows[] = [$id, $bid, $it['code'], basename($it['source'], '.md') . ' baris ' . $it['line'], $it['dim'], $it['sub'], $it['level'], $it['levelNote'], $it['bloom']];
}
$key->sheet('Kunci B.5a', 'Kunci ID netral B.5a', $IM, ['Peneliti'], [
    ['head' => 'ID butir', 'width' => 9], ['head' => 'ID bahan', 'width' => 9], ['head' => 'Kode butir', 'width' => 13],
    ['head' => 'Sumber', 'width' => 30], ['head' => 'Dimensi', 'width' => 9], ['head' => 'Sub-aspek D1', 'width' => 11],
    ['head' => 'Level', 'width' => 9], ['head' => 'Asal level', 'width' => 50], ['head' => 'Bloom', 'width' => 8],
], $rows);
$rows = [];
foreach ($blockKeys as $k) {
    $rows[] = [$blockIds[$k], $k, $src->blocks[$k]['title'], basename($src->blocks[$k]['source'], '.md')];
}
$key->sheet('Kunci bahan', 'Kunci ID bahan', $IM, ['Peneliti'], [
    ['head' => 'ID bahan', 'width' => 9], ['head' => 'Kode bahan', 'width' => 14], ['head' => 'Judul', 'width' => 40], ['head' => 'Sumber', 'width' => 30],
], $rows);
$files['khusus-peneliti/materi-b5a-kunci-peneliti.xlsx'] = $key;

// ------------------------------------------------------------ B.5b

$b5bHead = $materi->table('B.5b', 0)['head'];
$check('B.5b tata letak instrumen', $b5bHead === ['No', 'Kode butir', 'Dimensi', 'Relevansi', 'Kejelasan', 'Kunci', 'Tanpa teks?', 'Bocor?', 'Temuan / usulan perbaikan'], implode(' | ', $b5bHead));
$b5b = new Book('Daftar B.5b: relevansi dan kejelasan per butir', $version);
$b5b->guide('Petunjuk', [
    '## Lembar relevansi dan kejelasan (B.5b)',
    'Semua ahli materi menilai semua butir yang dilabeli di B.5a (116 butir bank dan 64 butir tes), terhadap dimensi utama dan definisinya yang tercantum di daftar. Penilai B.5a mengisi lembar ini setelah B.5a dikumpulkan.',
    'Kolom abu-abu adalah rujukan (dimensi, definisinya, bahan, teks butir, dan kuncinya). Kolom putih diisi validator lewat daftar pilihan.',
    ['Relevansi (1–4)', 'Jangkar Tabel A3: 4 sangat relevan · 3 relevan · 2 kurang relevan (bisa dijawab tanpa teks atau tanpa kompetensi itu) · 1 tidak relevan. TD bila tidak dapat dinilai.'],
    ['Kejelasan (1–4)', '4 jelas · 3 jelas dengan perbaikan kecil · 2 bisa dibaca dua arti atau sulit dipahami anak · 1 tidak dapat dipahami.'],
    ['Kunci (B/S)', 'B = benar dan tunggal; S = salah, ganda, atau dapat diperdebatkan. Untuk Skenario Digital: tepat satu tindakan tepat dan satu kurang tepat.'],
    ['Tanpa teks? (Y/T)', 'Y bila butir D1 atau D3 bisa dijawab tanpa membaca teks atau unggahannya (pengetahuan umum, norma, bentuk kata). Untuk D2 sudah diisi "—".'],
    ['Bocor? (Y/T)', 'Y bila jawaban bocor dari judul, gambar, cerita, kartu misi, atau butir lain.'],
    'Lembar "Pertanyaan khusus" memuat R1–R5 dari instrumen.',
]);
$rows = [];
foreach ($labelled as $k => $it) {
    $rows[] = [(string) ($k + 1), $it['code'], $it['dim'], $src->dimensions[$it['dim']], $it['block'],
        trim($it['text'] . ($it['options'] !== '' ? "\n" . $it['options'] : '')), $it['key'],
        '', '', '', $it['dim'] === 'D2' ? NA : '', '', ''];   // "Tanpa teks?" is "—" for D2 (instrument B.5b)
}
$b5b->sheet('B.5b', 'B.5b Lembar relevansi dan kejelasan per butir', $IM, MATERI, [
    ['head' => 'No', 'width' => 5], ['head' => 'Kode butir', 'width' => 12], ['head' => 'Dimensi', 'width' => 8],
    ['head' => 'Definisi dimensi (rujukan)', 'width' => 36, 'ref' => true], ['head' => 'Bahan (rujukan)', 'width' => 12, 'ref' => true],
    ['head' => 'Butir (rujukan)', 'width' => 48, 'ref' => true], ['head' => 'Kunci butir (rujukan)', 'width' => 26, 'ref' => true],
    ['head' => 'Relevansi', 'width' => 10, 'list' => SCORE], ['head' => 'Kejelasan', 'width' => 10, 'list' => SCORE],
    ['head' => 'Kunci', 'width' => 8, 'list' => ['B', 'S']], ['head' => 'Tanpa teks?', 'width' => 9, 'list' => YT],
    ['head' => 'Bocor?', 'width' => 8, 'list' => YT], ['head' => 'Temuan / usulan perbaikan', 'width' => 36],
], $rows);
$rows = [];
foreach (array_merge($bank, $tests) as $it) {
    $rows[$it['block']] = [$it['block'], $src->blocks[$it['block']]['title'], $src->blocks[$it['block']]['text']];
}
$b5b->sheet('Bahan', 'Bahan per kode', $IM, MATERI, [
    ['head' => 'Kode bahan', 'width' => 14, 'ref' => true], ['head' => 'Judul', 'width' => 28, 'ref' => true], ['head' => 'Teks bahan', 'width' => 110, 'ref' => true],
], array_values($rows));
$r15 = $materi->table('Pertanyaan khusus dari', 0);
$b5b->sheet('Pertanyaan khusus', 'Pertanyaan khusus dari telaah pendahuluan (R1–R5)', $IM, MATERI, [
    ['head' => $r15['head'][0], 'width' => 5, 'ref' => true], ['head' => $r15['head'][1], 'width' => 26, 'ref' => true],
    ['head' => $r15['head'][2], 'width' => 70, 'ref' => true], ['head' => $r15['head'][3], 'width' => 24, 'ref' => true],
    ['head' => $r15['head'][4], 'width' => 10, 'list' => YT], ['head' => $r15['head'][5], 'width' => 40],
], array_map(fn ($r) => [$r[0], $r[1], $r[2], $r[3], '', ''], $r15['rows']));
$files['materi-b5b-relevansi-kejelasan.xlsx'] = $b5b;

// ------------------------------------------------------------ B.6–B.9 (ahli materi)

$b6 = $materi->table('B.6 Lembar', 0);
$b6codes = array_map(fn ($r) => explode(' · ', $r[1])[0], $b6['rows']);
$sorted6 = $b6codes;
sort($sorted6);
$scenarioCodes = array_keys($src->scenarios);
sort($scenarioCodes);
$check('B.6 kode skenario sama dengan 08', $sorted6 === $scenarioCodes, count($b6['rows']) . ' baris di instrumen, ' . count($src->scenarios) . ' skenario di 08');
$mb = new Book('Lembar kerja ahli materi: B.6 Skenario Digital, B.7 kode kesalahan, B.8 skala sikap, B.9 tes dan angket', $version);
$mb->guide('Petunjuk', [
    '## Lembar kerja ahli materi',
    'Setiap lembar mengikuti tabel di instrumen. Kolom abu-abu adalah rujukan yang diisi dari dokumen sumber; kolom putih diisi validator lewat daftar pilihan. Skor 1–4 memakai rubrik A.5; TD bila tidak dapat dinilai.',
    ['B.6 Skenario', '24 Skenario Digital; naskah lengkapnya (situasi, tindakan dengan skor, kode, dan umpan balik, alasan berkode, kalimat penjelasan) ada di lembar "B.6 Naskah".'],
    ['B.7 Kode', 'Semua sel kode: setiap pasangan rumpang × kata salah, setiap pernyataan Benar atau Salah dan kartu jangkar, setiap bagian ketuk yang bukan kunci di Periksa Unggahan, dan setiap tindakan dan alasan Skenario Digital. Kolom "Asal kode" menunjukkan aturan 08 yang memberi kode itu. Wajib untuk Ahli Literasi Digital 1 dan 2.'],
    ['B.8a, B.8b Sikap', 'Skala sikap menjaga warisan (17). Lihat tabel penilai di instrumen B.8 untuk kolom yang wajib bagi Anda.'],
    ['B.9a Kesetaraan', 'Satu baris per pasangan nomor bentuk A dan B: teks M1–M4, butir membaca 01–16, skenario 01–08, adegan G1–G2, dan butir budaya digital 01–08. Keakraban diisi untuk teks, skenario, dan adegan saja.'],
    ['B.9a Pertanyaan T', 'Pertanyaan khusus T1–T5 tentang tes.'],
    ['B.9b Angket', 'Butir angket akses digital ang-01–ang-17 dan butir risiko daring ang-21–ang-23 (16).'],
]);
$byCode = [];
foreach ($b6['rows'] as $r) {
    $byCode[] = [$r[0], $r[1], '', '', '', '', '', '', '', '', '', ''];
}
$h = $b6['head'];
$mb->sheet('B.6 Skenario', 'B.6 Lembar telaah Skenario Digital', $IM, MATERI, [
    ['head' => $h[0], 'width' => 5], ['head' => $h[1], 'width' => 30, 'ref' => true],
    ['head' => $h[2], 'width' => 10, 'list' => SCORE], ['head' => $h[3], 'width' => 10, 'list' => SCORE],
    ['head' => $h[4], 'width' => 9, 'list' => YT], ['head' => $h[5], 'width' => 9, 'list' => YT], ['head' => $h[6], 'width' => 8, 'list' => YT],
    ['head' => $h[7], 'width' => 9, 'list' => SCORE], ['head' => $h[8], 'width' => 9, 'list' => YT], ['head' => $h[9], 'width' => 10, 'list' => YT],
    ['head' => $h[10], 'width' => 11, 'list' => YT], ['head' => $h[11], 'width' => 36],
], $byCode);
$rows = [];
foreach ($b6codes as $c) {
    $s = $src->scenarios[$c];
    $acts = implode("\n", array_map(fn ($a) => "• {$a['text']} — skor {$a['score']}, {$a['code']}. Umpan balik: {$a['feedback']}", $s['actions']));
    $reas = implode("\n", array_map(fn ($a) => "• {$a['text']} — {$a['code']}", $s['reasons']));
    $rows[] = [$c, $s['topic'], $s['situation'], $acts, $reas, $s['explain']];
}
$mb->sheet('B.6 Naskah', 'Naskah 24 Skenario Digital (dari 08)', $IM, MATERI, [
    ['head' => 'Kode', 'width' => 11, 'ref' => true], ['head' => 'Topik', 'width' => 16, 'ref' => true], ['head' => 'Situasi', 'width' => 44, 'ref' => true],
    ['head' => 'Tindakan (skor, kode, umpan balik)', 'width' => 60, 'ref' => true], ['head' => 'Alasan (kode)', 'width' => 40, 'ref' => true],
    ['head' => 'Penjelasan setelah kesempatan habis', 'width' => 40, 'ref' => true],
], $rows);

$codeList = [];
foreach (array_keys($src->codes03) as $c) {
    foreach (preg_split('/,\s*/u', (string) $c) as $cc) {
        if (preg_match('/^(INF|DIG|BUD|R)-[A-Z]+$/u', $cc)) {
            $codeList[] = $cc;
        }
    }
}
$codeList[] = NA;
$b7 = $materi->table('B.7 Lembar', 0);
$check('B.7 tata letak instrumen', $b7['head'] === ['No', 'Kode butir / pilihan', 'Teks pengecoh atau pilihan', 'Kode pengembang', 'Setuju', 'Kode usulan', 'Alasan'], implode(' | ', $b7['head']));
$b7cols = fn (bool $blind) => array_values(array_filter([
    ['head' => 'No', 'width' => 6], ['head' => 'Kode butir / pilihan', 'width' => 26, 'ref' => true],
    ['head' => 'Teks pengecoh atau pilihan', 'width' => 60, 'ref' => true],
    $blind ? null : ['head' => 'Kode pengembang', 'width' => 12, 'ref' => true],
    $blind ? null : ['head' => 'Asal kode (rujukan)', 'width' => 24, 'ref' => true],
    $blind ? null : ['head' => 'Setuju', 'width' => 8, 'list' => YT],
    ['head' => 'Kode usulan', 'width' => 12, 'list' => $codeList], ['head' => 'Alasan', 'width' => 36],
]));
$cells = $src->errorCells;
usort($cells, fn ($a, $b) => [$regionOrder[substr($a['item'], 0, 3)], $a['item']] <=> [$regionOrder[substr($b['item'], 0, 3)], $b['item']]);
$rows = [];
$rowsBlind = [];
foreach ($cells as $k => $c) {
    $rows[] = [(string) ($k + 1), $c['ref'], $c['text'], $c['code'], $c['origin'], '', '', ''];
    $rowsBlind[] = [(string) ($k + 1), $c['ref'], $c['text'], '', ''];
}
$mb->sheet('B.7 Kode', 'B.7 Lembar telaah kode kesalahan', $IM, MATERI, $b7cols(false), $rows);

[$b8aRows, $b8aCols] = (function (Instrument $ins, array $att) use ($check) {
    $t = $ins->table('B.8a', 0);
    $rows = [];
    foreach ($t['rows'] as $r) {
        $a = $att[$r[0]] ?? null;
        $check('B.8a ' . basename($ins->path, '.html') . " {$r[0]} sama dengan 17", $a !== null && $a['text'] === $r[1] && $a['indicator'] === $r[2] && str_replace('−', '-', $a['direction']) === str_replace('−', '-', $r[3]),
            $a === null ? 'tidak ada di 17' : 'pernyataan, indikator, dan arah');
        $rows[] = $r;
    }
    $check('B.8a ' . basename($ins->path, '.html') . ' jumlah pernyataan', count($t['rows']) === count($att), count($t['rows']) . ' di instrumen, ' . count($att) . ' di 17');
    $cols = [];
    foreach ($t['head'] as $i => $hd) {
        $c = ['head' => $hd, 'width' => $i === 1 ? 44 : 12];
        if ($i < 4) {
            $c['ref'] = true;
        } elseif (str_starts_with($hd, 'Relevansi')) {
            $c['list'] = SCORE;
        } elseif (str_ends_with($hd, '?')) {
            $c['list'] = YT;
        } else {
            $c['width'] = 30;
        }
        $cols[] = $c;
    }

    return [$rows, $cols];
})($materi, $src->attitude);
$mb->sheet('B.8a Sikap', 'B.8a Telaah per pernyataan', $IM, SIKAP, $b8aCols, $b8aRows);
$b8b = $materi->table('B.8b', 0);
$mb->sheet('B.8b Sikap', 'B.8b Penilaian skala secara keseluruhan', $IM, SIKAP, [
    ['head' => 'No', 'width' => 5, 'ref' => true], ['head' => 'Pertanyaan', 'width' => 70, 'ref' => true],
    ['head' => 'Ya/Tidak', 'width' => 10, 'list' => ['Ya', 'Tidak']], ['head' => 'Catatan', 'width' => 40],
], array_map(fn ($r) => [$r[0], $r[1], '', $r[3]], $b8b['rows']));

// B.9a: one row per pair number
$b9a = $materi->table('B.9a', 0);
$check('B.9a tata letak instrumen', $b9a['head'] === ['Pasangan (A · B)', 'Sel sama? (Y/T)', 'Setara (1–4)', 'Lebih mudah (A/B/–)', 'Keakraban A (1–4)', 'Keakraban B (1–4)', 'Catatan'], implode(' | ', $b9a['head']));
$pairs = [];
$pt = $src->testPairs;
$addPair = function (string $label, string $a, string $b, bool $familiar) use (&$pairs, $src) {
    foreach ([$a, $b] as $code) {
        if (! isset($src->items[$code]) && ! isset($src->blocks[$code])) {
            throw new \RuntimeException("B.9a: {$code} not found in 14–15");
        }
    }
    $pairs[] = [$label, '', '', '', $familiar ? '' : NA, $familiar ? '' : NA, ''];
};
for ($m = 1; $m <= 4; $m++) {
    $pairs[] = ['group' => "Bagian 1 · teks M{$m}"];
    $addPair("tes-mb-A-M{$m} · B-M{$m} ({$pt["M{$m}"]['A']} · {$pt["M{$m}"]['B']})", "tes-mb-A-M{$m}", "tes-mb-B-M{$m}", true);
    for ($i = ($m - 1) * 4 + 1; $i <= $m * 4; $i++) {
        $no = sprintf('%02d', $i);
        $addPair("tes-mb-A{$no} · B{$no}", "tes-mb-A{$no}", "tes-mb-B{$no}", false);
    }
}
$pairs[] = ['group' => 'Bagian 2 · skenario'];
for ($i = 1; $i <= 8; $i++) {
    $no = sprintf('%02d', $i);
    $addPair("tes-ae-A{$no} · B{$no} ({$pt["ae{$no}"]['A']})", "tes-ae-A{$no}", "tes-ae-B{$no}", true);
}
for ($g = 1; $g <= 2; $g++) {
    $pairs[] = ['group' => "Bagian 3 · adegan G{$g}"];
    $addPair("tes-bd-A-G{$g} · B-G{$g} ({$pt["G{$g}"]['A']} · {$pt["G{$g}"]['B']})", "tes-bd-A-G{$g}", "tes-bd-B-G{$g}", true);
    for ($i = ($g - 1) * 4 + 1; $i <= $g * 4; $i++) {
        $no = sprintf('%02d', $i);
        $addPair("tes-bd-A{$no} · B{$no}", "tes-bd-A{$no}", "tes-bd-B{$no}", false);
    }
}
$h = $b9a['head'];
$mb->sheet('B.9a Kesetaraan', 'B.9a Kesetaraan pasangan bentuk A dan B', $IM, MATERI, [
    ['head' => $h[0], 'width' => 44, 'ref' => true], ['head' => $h[1], 'width' => 10, 'list' => YT], ['head' => $h[2], 'width' => 10, 'list' => SCORE],
    ['head' => $h[3], 'width' => 11, 'list' => ['A', 'B', '–']], ['head' => $h[4], 'width' => 11, 'list' => SCORE], ['head' => $h[5], 'width' => 11, 'list' => SCORE],
    ['head' => $h[6], 'width' => 40],
], $pairs);
$tq = $materi->table('B.9a', 1);
$mb->sheet('B.9a Pertanyaan T', 'Pertanyaan khusus tentang tes (T1–T5)', $IM, MATERI, [
    ['head' => $tq['head'][0], 'width' => 5, 'ref' => true], ['head' => $tq['head'][1], 'width' => 26, 'ref' => true],
    ['head' => $tq['head'][2], 'width' => 70, 'ref' => true], ['head' => $tq['head'][3], 'width' => 10, 'list' => YT], ['head' => $tq['head'][4], 'width' => 40],
], array_map(fn ($r) => [$r[0], $r[1], $r[2], '', ''], $tq['rows']));
$b9b = $materi->table('B.9b', 0);
$b9bCodes = array_column($b9b['rows'], 0);
$angRange = array_merge($src->range03('ang-01'), $src->range03('ang-21'));
$check('B.9b butir angket', $b9bCodes === array_keys($src->survey) && $b9bCodes === $angRange, count($b9bCodes) . ' di instrumen, ' . count($src->survey) . ' di 16, ' . count($angRange) . ' di 03');
preg_match('/Angket berisi (\d+) butir/u', $materi->text(), $m);
$check('B.9b jumlah butir menurut teks instrumen', count(array_filter($b9bCodes, fn ($c) => $c < 'ang-20')) === (int) ($m[1] ?? -1), 'ang-01–ang-17 = ' . ($m[1] ?? '?'));
$h = $b9b['head'];
$cols = [['head' => $h[0], 'width' => 8, 'ref' => true], ['head' => 'Pertanyaan di layar (rujukan, 16)', 'width' => 50, 'ref' => true]];
foreach (array_slice($h, 1) as $hd) {
    $cols[] = ['head' => $hd, 'width' => str_starts_with($hd, 'Kata sulit') || str_starts_with($hd, 'Usulan') ? 30 : 12,
        'list' => str_starts_with($hd, 'Relevan') ? SCORE : (str_contains($hd, '(Y/T)') ? YT : null)];
}
$cols = array_map(fn ($c) => array_filter($c, fn ($v) => $v !== null), $cols);
$mb->sheet('B.9b Angket', 'B.9b Telaah angket akses digital', $IM, MATERI, $cols,
    array_map(fn ($c) => array_merge([$c, $src->survey[$c]], array_fill(0, count($h) - 1, '')), $b9bCodes));
$files['materi-b6-b9-lembar-kerja.xlsx'] = $mb;

$b7blind = new Book('B.7 kode kesalahan, versi pengodean buta', $version);
$b7blind->guide('Petunjuk', [
    '## B.7 dengan pengodean buta',
    'Dipakai hanya bila peneliti meminta pengodean buta (instrumen B.7): kolom "Kode pengembang" dikosongkan dan validator mengisi "Kode usulan" saja. Kesepakatan dengan kode pengembang lalu dihitung dengan κ (C.4). Kode yang tersedia ada di daftar pilihan; definisinya di 03.',
]);
$b7blind->sheet('B.7 Kode', 'B.7 Lembar telaah kode kesalahan (pengodean buta)', $IM, MATERI, $b7cols(true), $rowsBlind);
$files['materi-b7-kode-kesalahan-buta.xlsx'] = $b7blind;

// ------------------------------------------------------------ pengamat budaya

$bb = new Book('Lembar kerja pengamat budaya: B.5 matriks objek, B.6 Periksa Unggahan dan PBK, B.7 cek fakta, B.8 skala sikap', $version);
$bb->guide('Petunjuk', [
    '## Lembar kerja pengamat budaya',
    'Setiap lembar mengikuti tabel di instrumen. Kolom abu-abu adalah rujukan; kolom putih diisi lewat daftar pilihan. Skor 1–4 memakai rubrik Tabel A1; isi TD untuk objek atau butir yang tidak Anda kuasai.',
    ['B.5 Matriks', 'Objek budaya per wilayah; tiga baris "(usulan validator)" untuk objek yang belum ada.'],
    ['B.6a Periksa Unggahan', '24 petunjuk Periksa Unggahan; kolom rujukan memuat petunjuk dan bagian kuncinya dari 08.'],
    ['B.6b PBK', '12 butir Temukan Benda; kolom rujukan memuat petunjuknya dari 08.'],
    ['B.7 Cek fakta', 'Konfirmasi hasil cek fakta tim.'],
    ['B.8a, B.8b Sikap', 'Skala sikap menjaga warisan; Ahli Budaya Kedu 1 mengisi relevansi dan indikator, ketiga pengamat mengisi "Contoh dikenal di wilayah saya?".'],
]);
$b5 = $budaya->table('B.5 Matriks', 0);
$rows = [];
$objects = 0;
$known = array_merge(array_keys($src->items), array_keys($src->pbk));
$bad = [];
foreach ($b5['rows'] as $r) {
    if (count($r) === 1) {
        $rows[] = ['group' => $r[0]];
        continue;
    }
    if (! str_starts_with($r[1], '(usulan')) {
        $objects++;
        $prev = '';
        preg_match_all('/((?:tmg|mgl|wnb)-\d(?:-[abx](?:\d\d)?)?)|(?<=, )([abx]\d\d)\b|(mk-[a-z]{2}-\d\d)/u', $r[2], $mm, PREG_SET_ORDER);
        foreach ($mm as $x) {
            if (($x[3] ?? '') !== '') {
                $ok = isset($src->mockups[$x[3]]);
                $c = $x[3];
            } else {
                $c = ($x[1] ?? '') !== '' ? $x[1] : substr($prev, 0, 6) . $x[2];
                $prev = $c;
                $ok = (bool) array_filter($known, fn ($k) => str_starts_with($k, $c));
            }
            if (! $ok) {
                $bad[] = "{$r[0]}: {$c}";
            }
        }
    }
    $rows[] = [$r[0], $r[1], $r[2], '', '', '', '', '', ''];
}
preg_match('/Matriks (\d+) objek budaya/u', $budaya->text(), $m);
$check('B.5 jumlah objek budaya', $objects === (int) ($m[1] ?? -1), "{$objects} baris objek, instrumen menyebut " . ($m[1] ?? '?'));
$check('B.5 kode "Muncul di" ada di 08 atau 18', $bad === [], $bad === [] ? 'semua kode dan awalan kode ditemukan' : implode('; ', $bad));
$h = $b5['head'];
$bb->sheet('B.5 Matriks', 'B.5 Matriks telaah objek budaya', $IB, BUDAYA, [
    ['head' => $h[0], 'width' => 5, 'ref' => true], ['head' => $h[1], 'width' => 44, 'ref' => true], ['head' => $h[2], 'width' => 28, 'ref' => true],
    ['head' => $h[3], 'width' => 9, 'list' => SCORE], ['head' => $h[4], 'width' => 10, 'list' => SCORE], ['head' => $h[5], 'width' => 10, 'list' => SCORE],
    ['head' => $h[6], 'width' => 9, 'list' => YT], ['head' => $h[7], 'width' => 30], ['head' => $h[8], 'width' => 30],
], $rows);

$b6a = $budaya->table('B.6 Telaah per butir', 0);
$rows = [];
$mism = [];
foreach ($b6a['rows'] as $r) {
    [$c, $type] = array_map('trim', explode(' · ', $r[1]));
    $clue = $src->clues[$c] ?? null;
    if ($clue === null || mb_strtolower($clue['type']) !== mb_strtolower(rtrim($type, '*'))) {
        $mism[] = $r[1];
    }
    $rows[] = [$r[0], $r[1], $clue['clue'] ?? '', $clue['keyText'] ?? '', '', '', '', '', ''];
}
$check('B.6a petunjuk dan jenisnya sama dengan 08', $mism === [] && count($b6a['rows']) === count($src->clues), count($b6a['rows']) . ' baris di instrumen, ' . count($src->clues) . ' petunjuk di 08' . ($mism ? '; beda: ' . implode(', ', $mism) : ''));
$h = $b6a['head'];
$bb->sheet('B.6a Periksa Unggahan', 'B.6a Petunjuk Periksa Unggahan (D3)', $IB, BUDAYA, [
    ['head' => $h[0], 'width' => 5, 'ref' => true], ['head' => $h[1], 'width' => 24, 'ref' => true],
    ['head' => 'Petunjuk (rujukan, 08)', 'width' => 44, 'ref' => true], ['head' => 'Bagian kunci (rujukan, 08)', 'width' => 36, 'ref' => true],
    ['head' => $h[2], 'width' => 10, 'list' => SCORE], ['head' => $h[3], 'width' => 10, 'list' => YT], ['head' => $h[4], 'width' => 10, 'list' => YT],
    ['head' => $h[5], 'width' => 10, 'list' => YT], ['head' => $h[6], 'width' => 34],
], $rows);
preg_match('/telaah (\d+) petunjuk Periksa Unggahan dan (\d+) butir/u', $budaya->text(), $m);
$check('B.6a/B.6b jumlah menurut teks instrumen', count($src->clues) === (int) ($m[1] ?? -1) && count($src->pbk) === (int) ($m[2] ?? -1), count($src->clues) . ' petunjuk, ' . count($src->pbk) . ' butir PBK; instrumen menyebut ' . ($m[1] ?? '?') . ' dan ' . ($m[2] ?? '?'));

$b6b = $budaya->table('B.6b', 0);
$rows = [];
$mism = [];
foreach ($b6b['rows'] as $r) {
    $p = $src->pbk[$r[0]] ?? null;
    if ($p === null) {
        $mism[] = $r[0];
    } elseif (mb_strtolower($p['key']) !== mb_strtolower($r[1])) {
        $notes[] = "B.6b {$r[0]}: benda sasaran di instrumen \"{$r[1]}\", kunci di 08 \"{$p['key']}\" (beda kata, isi sama).";
    }
    $rows[] = [$r[0], $r[1], $p['clue'] ?? '', '', '', '', '', ''];
}
$check('B.6b butir PBK sama dengan 08', $mism === [] && count($b6b['rows']) === count($src->pbk), count($b6b['rows']) . ' baris di instrumen, ' . count($src->pbk) . ' di 08');
$h = $b6b['head'];
$bb->sheet('B.6b PBK', 'B.6b Butir Pengetahuan Budaya Kedu (Temukan Benda)', $IB, BUDAYA, [
    ['head' => $h[0], 'width' => 11, 'ref' => true], ['head' => $h[1], 'width' => 24, 'ref' => true], ['head' => 'Petunjuk (rujukan, 08)', 'width' => 48, 'ref' => true],
    ['head' => $h[2], 'width' => 10, 'list' => SCORE], ['head' => $h[3], 'width' => 10, 'list' => YT], ['head' => $h[4], 'width' => 12, 'list' => YT],
    ['head' => $h[5], 'width' => 10, 'list' => YT], ['head' => $h[6], 'width' => 34],
], $rows);
$b7f = $budaya->table('B.7 Konfirmasi', 0);
$bb->sheet('B.7 Cek fakta', 'B.7 Konfirmasi cek fakta budaya', $IB, BUDAYA, [
    ['head' => $b7f['head'][0], 'width' => 60, 'ref' => true], ['head' => $b7f['head'][1], 'width' => 40, 'ref' => true],
    ['head' => $b7f['head'][2], 'width' => 12, 'list' => ['Ya', 'Tidak']], ['head' => $b7f['head'][3], 'width' => 40],
], array_map(fn ($r) => [$r[0], $r[1], '', $r[3]], $b7f['rows']));
[$rows8, $cols8] = (function (Instrument $ins, array $att) use ($check) {
    $t = $ins->table('B.8a', 0);
    foreach ($t['rows'] as $r) {
        $a = $att[$r[0]] ?? null;
        $check('B.8a ' . basename($ins->path, '.html') . " {$r[0]} sama dengan 17", $a !== null && $a['text'] === $r[1] && $a['indicator'] === $r[2], $a === null ? 'tidak ada di 17' : 'pernyataan dan indikator');
    }
    $cols = [];
    foreach ($t['head'] as $i => $hd) {
        $cols[] = $i < 4 ? ['head' => $hd, 'width' => $i === 1 ? 44 : 10, 'ref' => true]
            : ['head' => $hd, 'width' => 14] + (str_starts_with($hd, 'Relevansi') ? ['list' => SCORE] : (str_ends_with($hd, '?') ? ['list' => YT] : []));
    }

    return [$t['rows'], $cols];
})($budaya, $src->attitude);
$bb->sheet('B.8a Sikap', 'B.8a Telaah per pernyataan', $IB, BUDAYA, $cols8, $rows8);
$b8bb = $budaya->table('B.8b', 0);
$bb->sheet('B.8b Sikap', 'B.8b Penilaian skala secara keseluruhan', $IB, BUDAYA, [
    ['head' => 'No', 'width' => 5, 'ref' => true], ['head' => 'Pertanyaan', 'width' => 70, 'ref' => true],
    ['head' => 'Ya/Tidak', 'width' => 10, 'list' => ['Ya', 'Tidak']], ['head' => 'Catatan', 'width' => 40],
], array_map(fn ($r) => [$r[0], $r[1], '', $r[3]], $b8bb['rows']));
$files['budaya-b5-b8-lembar-kerja.xlsx'] = $bb;

// ------------------------------------------------------------ ahli media

$b8m = $media->table('B.8 Telaah mock-up', 0);
$covered = [];
foreach ($b8m['rows'] as $r) {
    if ($r[0] === '7') {
        continue;   // row 7 (digital passages) refers to mk-pu-* again
    }
    preg_match_all('/mk-([a-z]{2})-(\d\d)(?:–(\d\d))?((?:, \d\d)*)/u', $r[1], $mm, PREG_SET_ORDER);
    foreach ($mm as $x) {
        $to = ($x[3] ?? '') !== '' ? (int) $x[3] : (int) $x[2];
        for ($i = (int) $x[2]; $i <= $to; $i++) {
            $covered[] = sprintf('mk-%s-%02d', $x[1], $i);
        }
        foreach (array_filter(explode(', ', $x[4] ?? '')) as $more) {
            $covered[] = sprintf('mk-%s-%02d', $x[1], (int) $more);
        }
    }
}
sort($covered);
$mk18 = array_keys($src->mockups);
sort($mk18);
$mk03 = $src->range03('mk-');
sort($mk03);
preg_match('/Lembar ini menilai (\d+) gambar mock-up/u', $media->text(), $m);
$check('Media B.8 gambar mock-up', $covered === $mk18 && $mk18 === $mk03 && count($mk18) === (int) ($m[1] ?? -1),
    count($covered) . ' gambar di baris 1–6 instrumen, ' . count($mk18) . ' di 18, ' . count($mk03) . ' di 03; instrumen menyebut ' . ($m[1] ?? '?'));
$bm = new Book('Lembar kerja ahli media: B.8 telaah mock-up layar baru', $version);
$bm->guide('Petunjuk', [
    '## Lembar kerja ahli media (langkah 1)',
    'Lembar "B.8 Mock-up" mengikuti tabel B.8 di instrumen: skor 1–4 (rubrik A.5) pada lima kolom, satu baris per layar. Isi TD untuk yang tidak bisa dinilai dari gambar statis (gerak, suara, waktu muat, papan ketik sentuh); "—" berarti aspek itu tidak berlaku untuk baris tersebut.',
    'Lembar "B.8 Per gambar" opsional: catatan per gambar (mk-…), dengan keadaan yang ditampilkan setiap gambar menurut 18.',
]);
$h = $b8m['head'];
$bm->sheet('B.8 Mock-up', 'B.8 Telaah mock-up layar baru', $IMD, MEDIA, [
    ['head' => $h[0], 'width' => 5, 'ref' => true], ['head' => $h[1], 'width' => 50, 'ref' => true],
    ['head' => $h[2], 'width' => 11, 'list' => SCORE], ['head' => $h[3], 'width' => 12, 'list' => SCORE], ['head' => $h[4], 'width' => 11, 'list' => SCORE],
    ['head' => $h[5], 'width' => 9, 'list' => SCORE], ['head' => $h[6], 'width' => 11, 'list' => SCORE], ['head' => 'Catatan', 'width' => 40],
], array_map(fn ($r) => array_merge($r, ['']), $b8m['rows']));
$rows = [];
foreach ($mk18 as $c) {
    $rows[] = [$c, $src->mockups[$c], ''];
}
$bm->sheet('B.8 Per gambar', 'Catatan per gambar mock-up (opsional)', $IMD, MEDIA, [
    ['head' => 'Gambar', 'width' => 11, 'ref' => true], ['head' => 'Keadaan yang ditampilkan (18)', 'width' => 80, 'ref' => true], ['head' => 'Catatan', 'width' => 50],
], $rows);
$files['media-b8-mockup.xlsx'] = $bm;

// ------------------------------------------------------------ code checks (03)

$codes03 = [];
foreach (array_keys($src->codes03) as $c) {
    foreach (preg_split('/,\s*/u', (string) $c) as $cc) {
        $codes03[$cc] = true;
    }
}
$missing = [];
foreach ($src->errorCells as $c) {
    if ($c['code'] !== '—' && ! isset($codes03[$c['code']])) {
        $missing[] = "{$c['ref']} → {$c['code']}";
    }
}
$check('B.7 setiap kode kesalahan ada di 03', $missing === [], count($src->errorCells) . ' sel kode' . ($missing ? '; tidak ada di 03: ' . implode(', ', array_unique($missing)) : ''));
$badItems = [];
foreach ($src->items as $code => $it) {
    $ok = $it['part'] === 'bank'
        ? preg_match('/^(tmg|mgl|wnb)-([2-5])-[abx]\d\d$/u', $code, $mm) && isset($codes03[$mm[1]]) && isset($codes03["{$mm[1]}-{$mm[2]}"])
        : preg_match('/^tes-(mb|ae|bd)-[AB]\d\d$/u', $code) && str_contains($src->codes03['tes-mb-A07'] ?? '', '`' . substr($code, 4, 2) . '`');
    if (! $ok) {
        $badItems[] = $code;
    }
}
foreach (array_keys($src->pbk) as $code) {
    if (! preg_match('/^(tmg|mgl|wnb)-4-x\d\d$/u', $code)) {
        $badItems[] = $code;
    }
}
$check('Kode butir mengikuti pola 03', $badItems === [], count($src->items) + count($src->pbk) . ' kode' . ($badItems ? '; tidak cocok: ' . implode(', ', $badItems) : ''));
$skp = $src->range03('skp-0');   // skp-ct-01 is the unscored example
$check('Kode skala sikap ada di 03', $skp === array_keys($src->attitude), count($skp) . ' kode di 03, ' . count($src->attitude) . ' di 17');
$refs = [];
foreach (array_merge($r15['rows'], $tq['rows']) as $r) {
    preg_match_all('/(?:tmg|mgl|wnb)-\d-[abx]\d\d|tes-(?:mb|ae|bd)-[AB](?:\d\d|-[MG]\d)/u', $r[1] . ' ' . $r[2], $mm);
    foreach ($mm[0] as $c) {
        if (! isset($src->items[$c]) && ! isset($src->blocks[$c])) {
            $refs[] = "{$r[0]}: {$c}";
        }
    }
}
$check('Kode di R1–R5 dan T1–T5 ada di 08, 14, atau 15', $refs === [], $refs ? implode(', ', $refs) : 'semua ditemukan');
foreach (array_merge(MATERI, BUDAYA, MEDIA) as $p) {
    $base = preg_replace('/ \d$/u', '', $p);
    $check("Nama samaran {$p} ada di 03", (bool) array_filter(array_keys($src->codes03), fn ($k) => str_starts_with((string) $k, $base)), $p);
}

// ------------------------------------------------------------ booklet (HTML)

$esc = fn (string $s) => nl2br(htmlspecialchars($s, ENT_QUOTES, 'UTF-8'), false);
$html = '<!doctype html><html lang="id"><head><meta charset="utf-8"><title>Buklet pelabelan buta B.5a</title><style>'
    . '@page{size:A4 landscape;margin:12mm 12mm 14mm}body{font:9.5pt/1.4 Inter,"Liberation Sans",sans-serif;color:#1f2933}'
    . 'h1{font-size:15pt;margin:0 0 4pt}h2{font-size:11pt;margin:14pt 0 4pt;padding:3pt 6pt;background:#d9e2ec;break-after:avoid}'
    . '.bahan{white-space:normal;border:1px solid #bcccdc;padding:6pt 8pt;margin-bottom:6pt;break-inside:avoid-page}'
    . 'table{border-collapse:collapse;width:100%;break-inside:auto}th,td{border:1px solid #bcccdc;padding:3pt 4pt;vertical-align:top}'
    . 'th{background:#eef2f6;text-align:left}tr{break-inside:avoid}.isi{width:7%}.lbl{width:6%}.cat{width:11%}.meta td{border:0;padding:1pt 4pt}'
    . '.cover{break-after:page}</style></head><body>';
$html .= '<section class="cover"><h1>Buklet pelabelan buta (B.5a)</h1>'
    . '<p>' . $esc('Instrumen Validasi Ahli Materi v2.1 · ' . count($blind) . ' butir dalam ' . count($blockKeys) . ' bahan · Versi berkas: ' . $version) . '</p>'
    . '<table class="meta"><tr><td>Nama samaran validator:</td><td>______________________________</td></tr><tr><td>Tanggal pengisian:</td><td>______________________________</td></tr></table>'
    . '<p>' . $esc('Isinya sama dengan lembar kerja "materi-b5a-daftar-butir-buta.xlsx". Isi label di lembar kerja, atau di kolom kosong buklet ini bila mengisi di atas kertas. Urutan bahan diacak; ID butir (BT-…) dan ID bahan (BH-…) netral.') . '</p><table><tr><th>Label</th><th>Arti</th></tr>';
foreach ($labelGuide as $k => $v) {
    $html .= '<tr><td>' . $esc($k) . '</td><td>' . $esc($v) . '</td></tr>';
}
$html .= '</table><h2>Acuan proses kognitif untuk label Bloom</h2><table><tr>';
foreach ($bloomRef['head'] as $hd) {
    $html .= '<th>' . $esc($hd) . '</th>';
}
$html .= '</tr>';
foreach ($bloomRef['rows'] as $r) {
    $html .= '<tr><td>' . implode('</td><td>', array_map($esc, $r)) . '</td></tr>';
}
$html .= '</table></section>';
$cur = null;
foreach ($blind as [$id, $bid, $it]) {
    if ($bid !== $cur) {
        if ($cur !== null) {
            $html .= '</table>';
        }
        $cur = $bid;
        $html .= '<h2>Bahan ' . $bid . '</h2><div class="bahan">' . $esc($src->blocks[$it['block']]['text']) . '</div>'
            . '<table><tr><th class="isi">ID butir</th><th>Butir</th><th>Pilihan atau bank kata</th><th>Kunci</th>'
            . '<th class="lbl">Dimensi</th><th class="lbl">Sub-aspek D1</th><th class="lbl">Level</th><th class="lbl">Bloom</th><th class="cat">Catatan</th></tr>';
    }
    $html .= '<tr><td>' . $id . '</td><td>' . $esc($it['text']) . '</td><td>' . $esc($it['options']) . '</td><td>' . $esc($it['key']) . '</td>'
        . '<td></td><td></td><td></td><td>' . ($it['part'] === 'tes' ? NA : '') . '</td><td></td></tr>';
}
$html .= '</table></body></html>';

// ------------------------------------------------------------ blindness checks

$forbidden = [
    'kode butir bank' => '/\b(tmg|mgl|wnb)-\d/u',
    'kode tes' => '/\btes-(mb|ae|bd|ct)\b/u',
    'kode mock-up' => '/\bmk-[a-z]{2}-\d/u',
    'kode kesalahan' => '/\b(INF|DIG|BUD|NON)-[A-Z]{3,5}\b/u',
    'kode alasan' => '/\bR-(RISK|ETIK|ATUR|SOS|ACAK)\b/u',
    'label dimensi' => '/\bD[123][ab]?\b/u',
    'label level' => '/\bL[123]\b/u',
    'label Bloom' => '/\bC[1-6]\b/u',
    'kata label' => '/\b(AKM|Bloom|PBK|sub-aspek|dimensi|kerumitan|padanan)\b/iu',
    'jenis D3' => '/\b(Atribusi|Representasi|Menghargai tradisi)\b/u',
    'tingkat wilayah' => '/(Level utama|Tingkat:|tanda (bahaya|keliru) (jelas|samar)|\((mudah|sedang|sulit)\))/iu',
    'variasi atau bentuk' => '/\b(Variasi [ab]|Bentuk [AB])\b/u',
];
$scan = function (string $where, string $text) use ($forbidden): array {
    $hits = [];
    foreach ($forbidden as $name => $re) {
        if (preg_match_all($re, $text, $mm)) {
            $hits[] = "{$where}: {$name} (" . implode(', ', array_unique($mm[0])) . ')';
        }
    }

    return $hits;
};
$leaks = [];
foreach ($blockKeys as $k) {
    $leaks = array_merge($leaks, $scan("bahan {$blockIds[$k]}", $src->blocks[$k]['text']));
}
foreach ($blind as [$id, , $it]) {
    $leaks = array_merge($leaks, $scan("butir {$id}", $it['text'] . "\n" . $it['options'] . "\n" . $it['key']));
}
$check('B.5a buta: tidak ada label atau kode di isi butir dan bahan', $leaks === [], $leaks === [] ? count($blind) . ' butir dan ' . count($blockKeys) . ' bahan diperiksa terhadap ' . count($forbidden) . ' pola' : implode('; ', $leaks));
$body = preg_replace('/<th[^>]*>.*?<\/th>/su', ' ', substr($html, strpos($html, '</section>')));   // column headers name the labels on purpose
$bodyText = html_entity_decode(strip_tags(str_replace('<', ' <', $body)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
$bookletLeaks = $scan('buklet', $bodyText);
$check('Buklet B.5a buta', $bookletLeaks === [], $bookletLeaks === [] ? 'isi buklet setelah sampul, tanpa judul kolom' : implode('; ', $bookletLeaks));

if ($errors !== []) {
    fwrite(STDERR, "Berkas TIDAK ditulis. Pemeriksaan gagal:\n - " . implode("\n - ", $errors) . "\n");
    exit(1);
}

// ------------------------------------------------------------ write

foreach ($files as $name => $book) {
    $book->save("{$out}/{$name}");
}
file_put_contents("{$out}/materi-b5a-buklet-buta.html", $html);

// Re-read the written blind workbook: label columns empty, no leak in any filled cell.
$wb = IOFactory::load("{$out}/materi-b5a-daftar-butir-buta.xlsx");
$filled = [];
$wbLeaks = [];
foreach (['Bahan', 'B.5a'] as $sheet) {
    $ws = $wb->getSheetByName($sheet);
    $hdr = $ws->rangeToArray('A' . Book::HEADER_ROW . ':' . $ws->getHighestColumn() . Book::HEADER_ROW)[0];
    foreach ($ws->getRowIterator(Book::HEADER_ROW + 1) as $row) {
        foreach ($row->getCellIterator() as $cell) {
            $v = (string) $cell->getValue();
            $col = $hdr[\PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($cell->getColumn()) - 1] ?? '';
            if ($sheet === 'B.5a' && in_array($col, ['Dimensi', 'Sub-aspek D1', 'Level', 'Bloom'], true) && $v !== '' && $v !== NA . ' (butir tes)') {
                $filled[] = $cell->getCoordinate();
            }
            if ($sheet === 'B.5a' && in_array($col, ['Dimensi', 'Sub-aspek D1', 'Level', 'Bloom', 'No', 'ID butir', 'ID bahan'], true)) {
                continue;
            }
            $wbLeaks = array_merge($wbLeaks, $scan("{$sheet}!{$cell->getCoordinate()}", $v));
        }
    }
}
$check('Lembar kerja B.5a yang ditulis: kolom label kosong', $filled === [], $filled === [] ? 'kolom Dimensi, Sub-aspek D1, Level, dan Bloom kosong (Bloom butir tes berisi "—")' : implode(', ', $filled));
$check('Lembar kerja B.5a yang ditulis: tidak ada label atau kode', $wbLeaks === [], $wbLeaks === [] ? 'semua sel isi di lembar Bahan dan B.5a' : implode('; ', array_slice($wbLeaks, 0, 10)));
$sheetNames = $wb->getSheetNames();
$check('Lembar kerja B.5a: tidak ada lembar kunci', ! array_filter($sheetNames, fn ($s) => stripos($s, 'kunci') !== false), implode(', ', $sheetNames));
if ($errors !== []) {
    foreach (array_keys($files) as $name) {
        @unlink("{$out}/{$name}");
    }
    fwrite(STDERR, "Berkas dihapus lagi. Pemeriksaan setelah menulis gagal:\n - " . implode("\n - ", $errors) . "\n");
    exit(1);
}

// ------------------------------------------------------------ report

$counts = [
    ['materi-b5a-daftar-butir-buta.xlsx', 'B.5a', count($blind) . ' butir (' . $bankCount . ' bank, ' . $testCount . ' tes) dalam ' . count($blockKeys) . ' bahan'],
    ['materi-b5a-buklet-buta.pdf', 'B.5a', 'isi yang sama, untuk dicetak'],
    ['khusus-peneliti/materi-b5a-kunci-peneliti.xlsx', 'B.5a', count($blind) . ' baris kunci, ' . count($blockKeys) . ' baris bahan'],
    ['materi-b5b-relevansi-kejelasan.xlsx', 'B.5b', count($labelled) . ' butir; R1–R5 ' . count($r15['rows']) . ' baris'],
    ['materi-b6-b9-lembar-kerja.xlsx', 'B.6', count($b6['rows']) . ' skenario'],
    ['', 'B.7', count($src->errorCells) . ' sel kode'],
    ['', 'B.8a, B.8b', count($b8aRows) . ' pernyataan, ' . count($b8b['rows']) . ' pertanyaan'],
    ['', 'B.9a', count(array_filter($pairs, fn ($p) => ! isset($p['group']))) . ' pasangan; T1–T5 ' . count($tq['rows']) . ' baris'],
    ['', 'B.9b', count($b9bCodes) . ' butir angket'],
    ['materi-b7-kode-kesalahan-buta.xlsx', 'B.7 (buta)', count($src->errorCells) . ' sel kode tanpa kode pengembang'],
    ['budaya-b5-b8-lembar-kerja.xlsx', 'B.5', $objects . ' objek + ' . (count($b5['rows']) - $objects - count(array_filter($b5['rows'], fn ($r) => count($r) === 1))) . ' baris usulan'],
    ['', 'B.6a, B.6b', count($src->clues) . ' petunjuk, ' . count($src->pbk) . ' butir PBK'],
    ['', 'B.7', count($b7f['rows']) . ' fakta'],
    ['', 'B.8a, B.8b', count($rows8) . ' pernyataan, ' . count($b8bb['rows']) . ' pertanyaan'],
    ['media-b8-mockup.xlsx', 'B.8', count($b8m['rows']) . ' baris layar, ' . count($mk18) . ' gambar'],
];
$byType = [];
foreach (['rumpang' => 'Isi Rumpang', 'boleh' => 'Benar atau Salah', 'jangkar' => 'Kartu jangkar', 'unggahan' => 'Periksa Unggahan', 'skenario' => 'Skenario Digital', 'tes-mb' => 'Tes Bagian 1 Membaca', 'tes-ae' => 'Tes Bagian 2 Aman dan etis', 'tes-bd' => 'Tes Bagian 3 Budaya digital'] as $t => $label) {
    $byType[] = "| {$label} | " . $count($t) . ' |';
}
$md = "# Laporan pemeriksaan berkas validator\n\nDibuat otomatis oleh `src/berkas-validator.php`. Jangan disunting; jalankan ulang skripnya.\n\n"
    . "Versi berkas: `{$version}` (hash 8 karakter dari dokumen 03, 08, 14–18 dan tiga instrumen HTML) · benih acak B.5a: " . SEED . "\n\n"
    . "## Berkas dan jumlah baris\n\n| Berkas | Lembar | Isi |\n| --- | --- | --- |\n"
    . implode("\n", array_map(fn ($r) => "| " . ($r[0] !== '' ? "`{$r[0]}`" : '') . " | {$r[1]} | {$r[2]} |", $counts))
    . "\n\n## Butir B.5a dan B.5b per jenis\n\n| Jenis | Butir |\n| --- | --- |\n" . implode("\n", $byType) . "\n| **Jumlah** | **" . count($labelled) . "** |\n"
    . "\n## Hasil pemeriksaan\n\n| Pemeriksaan | Hasil | Rincian |\n| --- | --- | --- |\n"
    . implode("\n", array_map(fn ($r) => "| {$r[0]} | {$r[1]} | " . str_replace('|', '\|', $r[2]) . ' |', $report))
    . "\n\n## Catatan untuk pemilik proyek\n\n" . ($notes === [] ? "Tidak ada.\n" : implode("\n", array_map(fn ($x) => "- {$x}", $notes)) . "\n");
file_put_contents("{$out}/LAPORAN.md", $md);

echo count($report) . " pemeriksaan lolos. Versi {$version}\n";
foreach ($counts as $r) {
    echo '  ' . str_pad($r[0], 50) . str_pad($r[1], 12) . $r[2] . "\n";
}
foreach ($notes as $x) {
    echo "  catatan: {$x}\n";
}

if (! $noPdf) {
    passthru('node ' . escapeshellarg(__DIR__ . '/berkas-validator-pdf.mjs') . ' ' . escapeshellarg("{$out}/materi-b5a-buklet-buta.html"), $rc);
    if ($rc !== 0) {
        fwrite(STDERR, "PDF buklet gagal dibuat.\n");
        exit(1);
    }
}
