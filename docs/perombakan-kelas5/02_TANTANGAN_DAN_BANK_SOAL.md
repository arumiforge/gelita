# Perombakan Kelas 5 · Dokumen 2 — Empat Tantangan, Aturan 3 Kesempatan, Variasi Acak, Bank Soal Final, dan Gambar Mockup

> **Status: rencana siap dikerjakan** (disusun 5 Oktober 2026). Dokumen **2 dari 3**. Kerjakan setelah [Dokumen 1](01_FONDASI_DATA_DAN_RESET.md) selesai (skema `004100`–`004600`, satu tahap, `item_tries`, `variant_group`, bintang). Lanjutkan ke [Dokumen 3](03_TAMPILAN_16x9_DAN_ADMIN.md) setelah kriteria §13 terpenuhi.
>
> **Bank soal di §9 adalah versi final yang sudah disetujui peneliti** ("apa pun yang dibuatkan disetujui sebagai bank soal"). Saat implementasi, isinya disalin apa adanya ke berkas data (§7); jangan diubah kecuali untuk memperbaiki salah ketik yang jelas.

---

## Daftar isi

1. [Peta tantangan baru](#1-peta-tantangan-baru)
2. [Aturan per tingkat kesulitan](#2-aturan-per-tingkat-kesulitan)
3. [Variasi dan pemilihan acak](#3-variasi-dan-pemilihan-acak)
4. [Pengacakan urutan](#4-pengacakan-urutan)
5. [Aturan 3 kesempatan](#5-aturan-3-kesempatan)
6. [Spesifikasi keempat tantangan](#6-spesifikasi-keempat-tantangan)
7. [Format bank soal, workbook, impor, dan verifikasi](#7-format-bank-soal-workbook-impor-dan-verifikasi)
8. [Analisa bank soal lama](#8-analisa-bank-soal-lama)
9. [Bank soal final kelas 5](#9-bank-soal-final-kelas-5)
10. [Gambar mockup](#10-gambar-mockup)
11. [Naskah cerita: jumlah serpihan dan narasi petunjuk](#11-naskah-cerita-jumlah-serpihan-dan-narasi-petunjuk)
12. [Teks UI yang lebih ramah](#12-teks-ui-yang-lebih-ramah)
13. [Pengujian dan kriteria selesai Dokumen 2](#13-pengujian-dan-kriteria-selesai-dokumen-2)

---

## 1. Peta tantangan baru

Setiap wilayah punya 4 tantangan dengan urutan yang sama. Node terbuka berurutan di dalam wilayah; wilayah terbuka berurutan (Temanggung → Magelang → Wonosobo).

| seq | Tantangan | engine | Interaksi | Temanggung (mudah) | Magelang (sedang) | Wonosobo (sulit) |
|---:|---|---|---|---|---|---|
| 1 | **Susun Gambar** | `puzzle` | `puzzle_arrange` | `tmg-1` · 3 × 3 kotak | `mgl-1` · 4 × 4 kotak | `wnb-1` · 4 × 4 jigsaw |
| 2 | **Isi Rumpang** | `rumpang` | `fill_blank_bank` | `tmg-2` · 3 rumpang, 0 pengecoh | `mgl-2` · 5 rumpang, 2 pengecoh | `wnb-2` · 7 rumpang, 2 pengecoh |
| 3 | **Benar atau Salah** | `boleh` | `verdict_card` | `tmg-3` · 1 paragraf, 3 pernyataan | `mgl-3` · 2 paragraf, 5 pernyataan | `wnb-3` · 3 paragraf, 6 pernyataan |
| 4 | **Cari Objek** | `cari` | `find_object` | `tmg-4` · 4 dicari + 4 pengecoh | `mgl-4` · 4 dicari + 6 pengecoh | `wnb-4` · 4 dicari + 8 pengecoh |

Jumlah: 12 node = **12 serpihan cahaya** (lentera HUD, profil, layar selesai membaca `COUNT(node aktif)`, jadi otomatis 12).

---

## 2. Aturan per tingkat kesulitan

`App\Libraries\DifficultyRules` tetap satu-satunya sumber aturan (dipakai importer, verifikator, editor, skrip `build-workbook.php`, dan generator mockup). Ganti `BY_DIFFICULTY` menjadi:

```php
public const BY_DIFFICULTY = [
    'mudah' => [
        'puzzle'    => ['grid' => 3, 'shape' => 'square'],
        'reading'   => ['blanks' => [3, 3], 'distractors' => [0, 0], 'default_distractors' => 0],
        'bloom'     => ['C1', 'C2'],                       // Isi Rumpang
        'truefalse' => ['paragraphs' => 1, 'statements' => 3, 'bloom' => ['C1', 'C2'],
                        'skills' => ['informasi_tersurat', 'mengenali_fakta', 'mengenali_gambar']],
        'hunt'      => ['targets' => 4, 'decoys' => 4, 'bloom' => ['C1', 'C2']],
    ],
    'sedang' => [
        'puzzle'    => ['grid' => 4, 'shape' => 'square'],
        'reading'   => ['blanks' => [5, 5], 'distractors' => [2, 2], 'default_distractors' => 2],
        'bloom'     => ['C2', 'C3'],
        'truefalse' => ['paragraphs' => 2, 'statements' => 5, 'bloom' => ['C2', 'C4'],
                        'skills' => ['gagasan_pokok', 'menghubungkan_informasi', 'mengklasifikasikan_tindakan']],
        'hunt'      => ['targets' => 4, 'decoys' => 6, 'bloom' => ['C2', 'C3']],
    ],
    'sulit' => [
        'puzzle'    => ['grid' => 4, 'shape' => 'jigsaw'],
        'reading'   => ['blanks' => [7, 7], 'distractors' => [2, 2], 'default_distractors' => 2],
        'bloom'     => ['C3', 'C4'],
        'truefalse' => ['paragraphs' => 3, 'statements' => 6, 'bloom' => ['C4', 'C5'],
                        'skills' => ['membandingkan_informasi', 'menilai_pilihan', 'menentukan_solusi']],
        'hunt'      => ['targets' => 4, 'decoys' => 8, 'bloom' => ['C3', 'C4']],
    ],
];

/** Kode keterampilan Benar atau Salah → label Indonesia (ditampilkan admin & ekspor). */
public const SKILLS = [
    'informasi_tersurat'          => 'Menemukan informasi tersurat',
    'mengenali_fakta'             => 'Mengenali fakta yang sesuai',
    'mengenali_gambar'            => 'Mengenali gambar yang sesuai',
    'gagasan_pokok'               => 'Menentukan gagasan pokok',
    'menghubungkan_informasi'     => 'Menghubungkan informasi',
    'mengklasifikasikan_tindakan' => 'Mengklasifikasikan tindakan',
    'membandingkan_informasi'     => 'Membandingkan informasi',
    'menilai_pilihan'             => 'Menilai pilihan',
    'menentukan_solusi'           => 'Menentukan solusi berdasarkan bukti',
];
```

- Jumlah rumpang dan pengecoh kini **tepat** (bukan rentang) agar ketiga variasi satu wilayah setara.
- Tambah method `trueFalse()`, `hunt()`, `skillLabel()`; `bloomRange()` menerima jenis (`reading` | `truefalse` | `hunt`).
- Taksonomi Bloom revisi (Anderson & Krathwohl): C1 Mengingat, C2 Memahami, C3 Menerapkan, C4 Menganalisis, C5 Mengevaluasi, C6 Mencipta. Penanda Bloom per soal **wajib** untuk Isi Rumpang, Benar atau Salah, dan petunjuk Cari Objek; Susun Gambar tanpa Bloom.

---

## 3. Variasi dan pemilihan acak

### 3.1 Konsep

Satu **variasi** = satu kelompok soal yang dimainkan utuh dalam satu pengerjaan.

| Tantangan | Isi satu variasi | Variasi per wilayah |
|---|---|---|
| Susun Gambar | 1 gambar (`puzzle_arrange`) + keterangannya | 3 (`a`, `b`, `c`) |
| Isi Rumpang | 1 bacaan rumpang: 3 / 5 / 7 potongan `fill_blank_bank` + pengecoh variasi itu | 3 |
| Benar atau Salah | 1 bacaan (`reading_passages`, kode `{node}-{variasi}`) + 3 / 5 / 6 pernyataan `verdict_card` | 3 |
| Cari Objek | 1 adegan + 4 benda dicari + 4 / 6 / 8 pengecoh (`find_object`) | 1 (`a`) |

Cari Objek memakai satu adegan tetap per wilayah karena posisi setiap benda diatur sekali dan tidak berubah (permintaan tata letak tetap, Dok 3). Yang diacak adalah urutan petunjuknya (§4).

### 3.2 Pemilihan (server)

`ChallengeService::openNode()`:

```php
$served   = model(ChallengeAttemptModel::class)->variantsServed($participantId, $nodeId); // semua sesi siswa itu
$picked   = model(ChallengeItemModel::class)->pickVariant($nodeId, $served);
// pickVariant: kelompokkan soal aktif per variant_group; buang yang ada di $served
// (bila semuanya sudah pernah, pakai semua); pilih satu dengan random_int().
```

- Ditulis ke `challenge_attempts.variant_group`; `selected_item_ids_json` = semua soal aktif variasi itu, urut `sequence`.
- Muat ulang halaman memakai pengerjaan yang sama (resume), jadi variasi tidak berubah.
- Bila soal variasi pengerjaan yang sedang berjalan semuanya sudah dinonaktifkan admin, pengerjaan lama ditinggalkan (`via: content_changed`, perilaku yang sudah ada) dan variasi baru dipilih.

### 3.3 Payload

`buildPayload()` menambah:

```jsonc
{
  "variant": { "group": "b", "title": "Umbul Jumprit" },   // judul dari config_json.variants (rumpang, puzzle) atau judul bacaan (boleh)
  "max_tries": 3,
  "tries_used": 1,          // = check_count (engine batch)
  "items": [ { "id": 12, "...": "...", "state": { "locked": true, "answer": {"text": "kemarau"}, "tries": 1 } } ]
}
```

`state` dikirim per soal agar muat ulang memulihkan tampilan:

- `locked: true` + `answer` **hanya** untuk soal yang sudah benar (jawaban siswa sendiri, bukan kunci soal lain);
- soal yang belum benar hanya membawa `tries` (berapa percobaan sudah dipakai); jawabannya yang salah tidak dikembalikan.

Untuk `cari`, `clues[i]` membawa `state: {found: bool, closed: bool, tries: n}`; objek yang sudah ditemukan diberi `found: true` di `objects` (aman: siswa sendiri yang menemukannya).

Kunci jawaban, pemetaan bank kata, penanda pengecoh, dan umpan balik pengecoh tetap **tidak pernah** ada di payload (`HuntPayloadTest` diperluas).

---

## 4. Pengacakan urutan

| Yang diacak | Cara | Alasan |
|---|---|---|
| **Variasi** (semua tantangan) | `random_int` per pengerjaan (§3.2) | "pemilihan soal selalu diacak" |
| **Bank kata Isi Rumpang** (semua tingkat, termasuk mudah) | `seeded_shuffle(kata, 'bank|' . attemptId . '|' . k)` dengan `k = 0, 1, 2, …` sampai lolos aturan di bawah (paling banyak 50 kali; urutan tetap saat dimuat ulang) | permintaan "pilihan jawaban selalu diacak, meski level mudah" |
| **Urutan petunjuk Cari Objek** | `seeded_shuffle(target, 'clue|' . attemptId)` | variasi tunggal tetap terasa berbeda; stabil saat muat ulang |
| **Keping Susun Gambar** | acakan keping yang sudah ada, dengan jaminan tidak ada keping yang sudah di tempatnya | sudah berjalan |
| Pernyataan Benar atau Salah | **tidak diacak** — mengikuti urutan bacaan dan naik tingkat Bloom | pernyataan disusun mengikuti alur bacaan; keacakan sudah dari pemilihan bacaan |
| Tombol Benar / Salah | **tidak diacak** — selalu "Benar" lalu "Salah" | konvensi UI anak; mengacak tombol hanya menambah salah tekan |
| Posisi benda di adegan Cari Objek | **tidak diacak** | posisi diatur sekali, tidak berubah (Dok 3) |

**Aturan bank kata teracak** (`ChallengeService::wordBank()`):

1. `A` = jawaban dalam urutan rumpang; `S` = hasil acak jawaban + pengecoh variasi.
2. `S` hanya diterima bila **urutan jawaban di dalam `S` tidak sama dengan `A`**.
3. Untuk 3 kata tanpa pengecoh (Temanggung) syaratnya lebih ketat: **tidak ada kata yang berada di posisi rumpangnya sendiri** (derangement; hanya 2 dari 6 susunan yang lolos), sehingga bank kata selalu tampak teracak.
4. Pengecoh variasi dibaca dari `config_json.variants.{grup}.distractors`, sebanyak `distractor_count`.

---

## 5. Aturan 3 kesempatan

### 5.1 Aturan

- Setiap soal paling banyak **3 percobaan** (`Config\Gelita::$maxTries`, disalin ke `challenge_attempts.max_tries`).
- **Susun Gambar, Isi Rumpang, Benar atau Salah** (engine batch): satu tekan **Cek jawabanku** = satu percobaan untuk semua soal yang belum benar. Soal yang sudah benar terkunci dan tidak dinilai ulang. Bila setelah percobaan ke-3 masih ada yang salah, pengerjaan **ditutup otomatis** dan jawaban terakhir disimpan apa adanya.
- **Cari Objek**: satu ketukan benda = satu percobaan untuk petunjuk yang sedang aktif. Ketukan salah ke-3 menutup petunjuk itu sebagai salah ("dilewati") dan petunjuk berikutnya muncul. Setelah keempat petunjuk tertutup (ditemukan atau dilewati), pengerjaan selesai.
- Pengerjaan yang selesai karena kesempatan habis **tetap selesai** (serpihan didapat, wilayah tetap dapat dilanjutkan); `completion_reason = tries_exhausted`, bintang ★ (Dok 1 §13).
- Tombol lama "Selesaikan dengan jawaban ini" (`FINISH_OFFER_AFTER`) dihapus.

### 5.2 Server (`ChallengeService`)

`submitCheck(attempt, answers)`:

1. `check_count >= max_tries` → `409 TRIES_EXHAUSTED` tanpa perubahan apa pun.
2. Setiap soal yang diharapkan dan **belum benar** wajib ada di `answers`; bila ada yang kosong → `422 INCOMPLETE` (klien sudah mencegahnya).
3. `check_count++`.
4. Untuk setiap soal belum benar: nilai, `applyAnswer()` (first-pass pada percobaan pertama), `try_count++`, tulis `item_tries` (`try_no = try_count`, `check_no = check_count`, `first_input_ms`/`last_input_ms` dari klien, `hint_opened_before`), isi `solved_on_try` bila benar.
5. Event `challenge_checked` `{check_no, try_no, correct_count, total, exhausted}`.
6. Bila semua benar → `completeAttempt(reason: solved)`; bila `check_count == max_tries` dan masih ada yang salah → event `challenge_tries_exhausted` lalu `completeAttempt(reason: tries_exhausted)`. Keduanya di **transaction yang sama** dengan percobaan, sehingga hasil tidak bergantung pada klien memanggil `/complete`.
7. Respons:

```jsonc
{
  "results": { "12": true, "13": false },
  "correct_count": 1, "total": 2,
  "check_count": 2, "tries_left": 1,
  "detail": { "14": { "pieces_correct": 13, "misplaced": [3, 7, 11] } },   // puzzle
  "completed": false,                     // true bila selesai (solved / tries_exhausted)
  "completion_reason": null,              // "solved" | "tries_exhausted"
  "redirect": null                        // "/selesai/{attempt}" bila completed
}
```

`submitAnswer(attempt, clueItemId, answer)` (Cari Objek):

1. Petunjuk sudah tertutup (ditemukan/dilewati) → `409 CLUE_CLOSED`.
2. `try_count++` pada petunjuk itu; tulis `item_tries` (`check_no = NULL`).
3. Benar → soal `answered`, `is_correct = 1`, `solved_on_try`. Salah → `wrong_click_count++`, event `wrong_target_clicked` (sudah ada); ketukan salah pertama menutup first-pass sebagai salah (sudah ada).
4. Salah dan `try_count == max_tries` → soal ditutup: `status = answered`, `is_correct = 0`, `final_answer_json` = ketukan terakhir; event `clue_failed`; respons `clue_closed: true`.
5. Semua petunjuk tertutup → `completeAttempt(reason: semua benar ? solved : tries_exhausted)` di transaction yang sama; respons `completed: true, redirect`.

`POST /api/attempts/{id}/complete` tetap ada untuk kompatibilitas dan idempoten: pengerjaan yang sudah selesai dibalas `redirect` yang sama.

### 5.3 Klien

- `game/challenge.js` menampilkan **penanda kesempatan** (3 bulatan; terisi = sisa) di kepala tantangan untuk engine batch, dan per petunjuk di Cari Objek. Nilainya dari server (`tries_left`, `state.tries`).
- Setelah percobaan salah dan masih ada kesempatan → modal "Belum semuanya tepat" + "Kesempatanmu tinggal {n} lagi" (atau "Tinggal satu kesempatan lagi. Baca pelan-pelan, ya!") + tombol **Perbaiki**.
- `completed: true` karena `tries_exhausted` → modal "Kesempatanmu sudah habis" (teks §12) dengan satu tombol **Lanjut** → `redirect`.
  - Susun Gambar: sebelum modal, keping disusun rapi ke posisi utuh dengan animasi 600 ms (susunan benar memang publik: keping 0..n−1) lalu keterangan gambar tampil.
  - Isi Rumpang & Benar atau Salah: soal yang salah tetap bertanda merah; kunci **tidak** ditampilkan.
- `completed: true` karena `solved` → konfeti + modal sukses (sudah ada) → `redirect`.
- `clue_closed: true` (Cari Objek) → modal "Petunjuk ini dilewati" → petunjuk berikutnya; benda petunjuk itu **tidak** ditandai/diungkap.
- Klien mengirim per soal `{first_input_ms, last_input_ms}` (Dok 1 §12.5) dan event `answer_selected` setiap perubahan jawaban.

---

## 6. Spesifikasi keempat tantangan

Tata letak di panggung 16:9 ada di Dok 3 §5; bagian ini mengatur perilaku dan data.

### 6.1 Susun Gambar (`puzzle`)

- Variasi = satu gambar persegi (minimal 1200 × 1200 px) yang dipotong browser (`App\Libraries\PuzzleCut`): Temanggung 3 × 3 kotak, Magelang 4 × 4 kotak, Wonosobo 4 × 4 jigsaw. Ukuran papan dibekukan per soal saat impor (perilaku sekarang).
- Keping tidak bernomor; ketuk dua keping untuk menukar (seret-lepas tambahan). Setiap tukar = event `answer_selected` `{action: "swap", from, to}`.
- **Cek gambarku** = percobaan. Keping yang salah bertanda ✗ (`misplaced`), jumlah keping keliru disebut di modal.
- Keterangan gambar (`prompt`) tampil setelah gambar utuh atau setelah kesempatan habis.
- Mode `ordering` (urutan kartu) **dihapus** dari `puzzle.php` dan `puzzle.js`.

### 6.2 Isi Rumpang (`rumpang`)

- Selalu bacaan dengan bank kata (`as_reading = true`, `use_word_bank = true`). Varian ketik sendiri (`fill_blank_free`, input teks) **dihapus**.
- Setiap soal = satu potongan bacaan dengan tepat satu `___`; potongan disambung menjadi paragraf (`App\Libraries\ClozeReading`, tidak berubah). **Jeda paragraf** ditulis `\n\n` di tengah potongan sesudah `___`; potongan tidak boleh diawali `___` (spasi sebelum rumpang hilang saat disambung).
- Judul variasi tampil di atas bacaan (`variant.title`).
- Bank kata = jawaban variasi + `distractor_count` pengecoh variasi, diacak (§4). Ketuk rumpang lalu ketuk kata; ketuk rumpang terisi mengembalikan katanya. Setiap isi/kosongkan = event `answer_selected`.
- Klien menahan **Cek jawabanku** bila ada rumpang kosong (modal "Masih ada yang kosong", tidak dihitung percobaan).
- Penilaian: teks dibandingkan setelah `mb_strtolower(trim())` (sudah ada).

### 6.3 Benar atau Salah (`boleh`)

- Satu bacaan per pengerjaan, tampil berdampingan dengan daftar pernyataan. Bacaan 1 / 2 / 3 paragraf; pernyataan 3 / 5 / 6.
- Pilihan hanya **Benar** dan **Salah** (`verdict_options = ["benar", "salah"]`). Varian beralasan (`verdict_reason`, kolom alasan) dan pilihan **Pendapat** **dihapus**; varian dua sumber (`config_json.sources`) juga dihapus — perbandingan sumber kini ditulis di dalam bacaan (Wonosobo).
- Pernyataan bergambar (Temanggung, keterampilan "mengenali gambar yang sesuai") memakai `challenge_items.media_asset_id`; gambar tampil di kartu pernyataan, bisa diperbesar.
- Setiap pernyataan menyimpan `bloom_level` dan `config_json.skill` (kode §2).
- Event `reading_progress` saat panel bacaan digulir (Dok 1 §12.3).

### 6.4 Cari Objek (`cari`)

- Satu adegan 16:9 (`challenge.{node}.scene`, 1280 × 720 px) dengan **4 benda dicari + 4 / 6 / 8 pengecoh** (`decoy = 1`, `scorable = 0`). Semua benda ditempel terpisah (PNG/SVG berlatar transparan) pada posisi persen tetap (`x`, `y`, `w` di `config_json`); adegan tidak menggambar bendanya.
- Payload `objects` (semua benda, bentuk identik, token HMAC per pengerjaan) dan `clues` (hanya benda dicari, urutan teracak §4) — perilaku keamanan sekarang tetap.
- Petunjuk dibacakan Mbah Kedu bila rekaman disetujui (narasi `petunjuk-{node}-NN`, §11.2); tombol ▶ di samping teks petunjuk.
- Ketuk benda = percobaan (§5). Pengecoh membawa umpan balik sendiri (`wrong_feedback_*`) yang dikirim sesudah diketuk.
- Daftar sisa petunjuk dapat dipakai untuk melompat ke petunjuk lain (sudah ada); melompat tidak menghitung percobaan.

### 6.5 Yang dihapus dari engine

| Dihapus | Tempat |
|---|---|
| Engine `pilihan` (kuis pilihan) | `Views/game/challenge/pilihan.php`, `js/engines/pilihan.js`, `ENGINES.pilihan` di `challenge.js`, `engine_label('pilihan')` |
| `single_choice`, `source_trust` | grader, `revealedOptionKey()`, `wrongFeedbackFor()` opsi, `toPlayerArray()` opsi |
| `ordering` | `puzzle.php`/`puzzle.js` mode kartu, `orderDetail()` cabang teks |
| `fill_blank_free` | `rumpang.php` input, `rumpang.js` cabang tanpa bank, `gradeFillFree()` |
| `verdict_reason`, "Pendapat", dua sumber | `boleh.php` textarea/sources/fact-opinion, `boleh.js` alasan, `applyAnswer()` alasan |

`ContentImportService` menolak jenis interaksi di luar `Config\Gelita::$interactionTypes` dengan pesan "Jenis soal ini sudah tidak dipakai di GELITA kelas 5."

---

## 7. Format bank soal, workbook, impor, dan verifikasi

### 7.1 Berkas

| Berkas | Isi |
|---|---|
| `docs/bank-soal/data/temanggung.php`, `magelang.php`, `wonosobo.php` | **ditulis ulang** dengan isi §9 dalam format §7.2 |
| `docs/bank-soal/build-workbook.php` | ditulis ulang: meratakan data → sheet impor, memberi kode, memeriksa aturan §7.5, menulis `gelita-bank-soal-kelas5.xlsx` |
| `docs/bank-soal/gelita-bank-soal-kelas5.xlsx` | workbook final (dibangun ulang dan di-commit) |
| `docs/bank-soal/gelita-bank-soal-produksi.xlsx` | **dihapus** (bank lama) |
| `docs/bank-soal/README.md` | ditulis ulang untuk bank kelas 5 (isi ringkas §1, §2, §7, daftar media §10.4) |
| `docs/pustaka/data/{temanggung,magelang,wonosobo}.php`, `docs/pustaka/build-workbook.php`, `docs/pustaka/gelita-pustaka.xlsx`, `docs/pustaka/README.md` | array `library` dari berkas data lama **dipindahkan byte demi byte** (tanpa perubahan isi); dipakai hanya untuk pemasangan baru. Server yang sudah berjalan **tidak** mengimpornya lagi. |
| `tools/audio/export_lines.php` | membaca petunjuk Cari Objek dari format baru (ketiga wilayah) |
| `app/Database/Seeds/SampleItemSeeder.php` | **dihapus** (soal contoh Dok 1 §8 tidak diperlukan lagi); `DatabaseSeeder` di `CI_ENVIRONMENT = development` mengimpor `gelita-bank-soal-kelas5.xlsx`; `gelita:reset` selalu mengimpornya |

### 7.2 Bentuk berkas data

```php
<?php
$ref = ['geo' => 'Pemkab Temanggung; BPS; PVMBG', /* … */];

return [
    'difficulty' => 'mudah',                         // sama dengan LevelSeeder
    'nodes' => [
        'tmg-1' => [
            'engine' => 'puzzle', 'variant' => 'susun_gambar', 'indicator' => 'budaya',
            'title'       => ['Susun Gambar Temanggung', 'Temanggung Picture Puzzle'],
            'instruction' => ['…', '…'],
            'description' => ['…', '…'],             // teks kartu misi
            'hints'       => [['…', '…'], ['…', '…'], ['…', '…']],
            'variants' => [
                'a' => [
                    'title' => ['Gunung Sindoro dan Sumbing', 'Mount Sindoro and Mount Sumbing'],
                    'items' => [[
                        'type'   => 'puzzle_arrange',
                        'prompt' => ['keterangan ID', 'caption EN'],
                        'media'  => ['key' => 'challenge.item.tmg-1-01', 'label' => 'Gunung Sindoro dan Sumbing', 'note' => 'isi gambar', 'source' => 'saran sumber'],
                        'ref'    => $ref['geo'],
                    ]],
                ],
                // 'b' => …, 'c' => …
            ],
        ],
        'tmg-2' => [
            'engine' => 'rumpang', 'variant' => 'rumpang_bacaan', 'indicator' => 'literasi',
            'distractor_count' => 0,
            /* title, instruction, description, hints */
            'variants' => [
                'a' => [
                    'title'       => ['Gunung Kembar', 'The Twin Mountains'],
                    'distractors' => [],                                  // [['enam', 'six'], …]
                    'items' => [
                        ['type' => 'fill_blank_bank', 'bloom' => 'C1',
                         'prompt' => ['… Gunung ___.', '… Mount ___.'], 'answer' => ['Sumbing', 'Sumbing']],
                    ],
                ],
            ],
        ],
        'tmg-3' => [
            'engine' => 'boleh', 'variant' => 'benar_salah_bacaan', 'indicator' => 'literasi',
            'variants' => [
                'a' => [
                    'passage' => ['title' => ['Situs Liyangan', 'The Liyangan Site'], 'body' => ['…', '…'], 'ref' => $ref['history']],
                    'items' => [
                        ['type' => 'verdict_card', 'bloom' => 'C1', 'skill' => 'informasi_tersurat', 'answer' => 'benar', 'prompt' => ['…', '…']],
                        ['type' => 'verdict_card', 'bloom' => 'C2', 'skill' => 'mengenali_gambar', 'answer' => 'benar', 'prompt' => ['…', '…'],
                         'media' => ['key' => 'challenge.item.tmg-3-a03', 'label' => 'Butir padi hangus', 'note' => '…']],
                    ],
                ],
            ],
        ],
        'tmg-4' => [
            'engine' => 'cari', 'variant' => 'cari_objek', 'indicator' => 'budaya',
            'scene'  => ['key' => 'challenge.tmg-4.scene', 'label' => 'Pasar budaya Temanggung', 'note' => '…'],
            'variants' => [
                'a' => [
                    'title' => ['Pasar Budaya Temanggung', 'Temanggung Culture Fair'],
                    'items' => [
                        ['type' => 'find_object', 'bloom' => 'C1', 'x' => 84, 'y' => 6, 'w' => 12,
                         'prompt' => ['Temukan …', 'Find …'], 'media' => ['key' => '…', 'label' => 'Kuda kepang bambu', 'note' => '…']],
                        ['type' => 'find_object', 'decoy' => true, 'x' => 74, 'y' => 40, 'w' => 12,
                         'prompt' => ['Angklung (pengecoh)', 'Angklung (decoy)'], 'wrong' => ['…', '…'], 'media' => ['…']],
                    ],
                ],
            ],
        ],
    ],
];
```

Aturan kode otomatis (`build-workbook.php`):

- **Kode soal** `{node}-{variasi}{nn}`, `nn` dua digit per variasi sesuai urutan di berkas: `tmg-2-a01`, `tmg-2-a02`, `tmg-2-b01`, …
- **Kode bacaan** Benar atau Salah: `{node}-{variasi}` (`tmg-3-a`).
- `sequence` soal = urutan global di node (a01…, b01…, c01…).
- `media.label` (nama pendek berbahasa Indonesia, ≤ 120 karakter) → `media_assets.label`; `media.note` → `media_assets.description` (Dok 1 §5.7). Keduanya dipakai halaman Media dan generator mockup. Di tabel §9, label adalah bagian sebelum garis miring (mis. *Kuda kepang bambu* / *Bamboo hobby horse* → `Kuda kepang bambu`).

### 7.3 Sheet workbook kelas 5

| Sheet | Kolom | Catatan |
|---|---|---|
| `nodes` | `node_ref, title_id, title_en, instruction_id, instruction_en, description_id, description_en, distractor_count, scene_media_key, background_media_key, variant_code, indicator` | kolom `items_per_round`, `verdict_options`, `require_reason`, `use_word_bank`, `as_reading` dihapus (nilainya tetap dari engine) |
| `variants` (**baru**) | `node_ref, variant_group, title_id, title_en` | satu baris per variasi |
| `distractors` | `node_ref, variant_group, text_id, text_en` | `variant_group` baru, wajib |
| `passages` | `passage_key, level_code, title_id, title_en, body_id, body_en, media_asset_key, reference_source` | tetap |
| `items` | `item_key, node_ref, variant_group, sequence, interaction_type, indicator, bloom_level, skill, prompt_id, prompt_en, passage_key, answer_id, answer_en, x, y, w, decoy, wrong_feedback_id, wrong_feedback_en, media_asset_key, media_label, media_note, scorable, is_active, review_status, review_note, reference_source` | kolom opsi/potongan/sumber/alasan/pilar dihapus |
| `hints` | `node_ref, item_key, sequence, text_id, text_en` | tetap |
| `options`, `pieces`, `sources`, `library`, `library_media` | — | **tidak ada** di workbook kelas 5 |
| `PETUNJUK`, `KAMUS_KOLOM`, `DAFTAR_MEDIA` | — | dibangun `BankWorkbookGuide` (diperbarui) |

`review_status` semua soal **`verified`** (bank disetujui peneliti), `review_note` = "Disetujui peneliti sebagai bank soal final penelitian kelas 5 (Oktober 2026)."

### 7.4 Impor (`ContentImportService`)

- Membaca sheet `variants` → `challenge_nodes.config_json.variants.{grup}.title_*`; `distractors` → `config_json.variants.{grup}.distractors`.
- Menulis `challenge_items.variant_group`, `bloom_level`, `config_json.skill`, `config_json.{x,y,w,decoy,wrong_feedback_*}`, `config_json.{grid,shape}` (puzzle, dibekukan).
- Slot media yang belum terdaftar dibuat **nonaktif** dengan `label` dan `description` dari kolom media; slot yang sudah ada (mis. gambar asli yang sudah diunggah admin dengan kunci lama) **tidak diubah** selain mengisi `label`/`description` yang masih kosong.
- Sheet `library`/`library_media` tetap didukung (untuk `docs/pustaka/gelita-pustaka.xlsx`), tetapi workbook kelas 5 tidak memilikinya.
- Kunci jawaban soal yang sudah dijawab siswa tetap tidak boleh berubah (aturan lama tetap).

### 7.5 Pemeriksaan (build script, importer, `ContentVerifier`)

Galat (impor ditolak / verifikasi gagal):

- tiap wilayah tepat 4 node aktif berurutan `puzzle, rumpang, boleh, cari`;
- Susun Gambar, Isi Rumpang, Benar atau Salah: **tepat 3 variasi** aktif; Cari Objek: ≥ 1 variasi;
- Susun Gambar: 1 soal per variasi, punya media;
- Isi Rumpang: jumlah rumpang = aturan tingkat (3 / 5 / 7); tepat satu `___` per potongan di ID dan EN; potongan tidak diawali `___`; jawaban unik dalam variasi; jumlah pengecoh variasi = `distractor_count` (0 / 2 / 2); pengecoh ≠ jawaban mana pun; tidak ada kata dobel di bank;
- Benar atau Salah: tepat satu bacaan per variasi, semua pernyataan variasi merujuk bacaan itu; jumlah paragraf bacaan (dipisah baris kosong) = 1 / 2 / 3; jumlah pernyataan = 3 / 5 / 6; kunci hanya `benar`/`salah`; setiap pernyataan punya `skill` sah;
- Cari Objek: tepat 4 target dan 4 / 6 / 8 pengecoh per variasi; setiap pengecoh punya umpan balik ID; semua benda punya media; posisi di dalam 0–100 % dan tidak saling tumpuk lebih dari 20 % luasnya (dihitung dari kotak `x, y, w, w×16/9`);
- `bloom_level` wajib untuk rumpang, pernyataan, dan target cari;
- `item_key` unik lintas wilayah.

Peringatan:

- Bloom di luar rentang tingkat (`DifficultyRules`);
- tidak ada pernyataan `benar` atau tidak ada `salah` dalam satu variasi;
- media masih gambar contoh (`is_placeholder = 1`) — menjadi **galat** bila `gelita:content:verify --siap-penelitian`.

`tests/unit/BankKelas5DataTest.php` menjalankan seluruh pemeriksaan ini terhadap berkas data; `BankWorkbookGuideTest` memastikan workbook sudah dibangun ulang setelah data/kolom berubah.

---

## 8. Analisa bank soal lama

Bank lama: 15 tantangan, 152 soal (113 aktif, 39 diistirahatkan), 152 opsi, 20 potongan urutan, 24 kartu sumber, 55 petunjuk, 4 kata pengecoh, 11 bacaan. Bank itu disusun untuk SD/SMP dengan 5 tantangan per wilayah. Penilaian kecocokannya untuk kelas 5 dan format baru:

| Tantangan lama | Isi | Keputusan | Alasan |
|---|---|---|---|
| `tmg-1` Susun Gambar | 4 gambar (01 Sindoro–Sumbing, 02 kebun tembakau, 03 Candi Pringapus, 04 kuda lumping) | **01, 03, 04 dipakai utuh** sebagai variasi a/b/c (gambar, kunci media, keterangan). **02 dihapus.** | Keterangan pendek dan cocok untuk kelas 5. Tembakau tidak sesuai untuk anak (sudah diistirahatkan). |
| `tmg-2` Isi Rumpang | 10 kalimat lepas (diistirahatkan) + bacaan "Gunung Kembar" 3 rumpang | **Bacaan dipakai utuh** sebagai variasi a. **10 kalimat lepas dihapus.** | Bacaan sudah sesuai Bloom C1–C2. Kalimat lepas tidak lagi dipakai; faktanya (Jumprit, kuda lumping) dipakai ulang di variasi b dan c. |
| `tmg-3` Benar atau Salah | 16 pernyataan dari 5 bacaan, 8 per permainan | **Diadaptasi.** Fakta Liyangan, Pringapus–Gondosuli, dan kopi lereng gunung ditulis ulang menjadi 3 bacaan satu paragraf. **16 pernyataan lama dihapus.** | Format baru: satu bacaan per pengerjaan. Pernyataan tembakau tidak dipakai. |
| `tmg-4` Cari Objek | 8 target + 3 jebakan | **Diadaptasi.** Kuda kepang, biji kopi, arca Nandi, ikan uceng (target) dan angklung, ondel-ondel, pinisi (pengecoh) dipakai dengan **kunci media dan posisi yang sama** serta petunjuk baru; ditambah pengecoh sasando di tempat bekas bambu runcing. **Daun tembakau, rigen, bambu runcing, kupat tahu dihapus.** | Aturan baru 4 + 4. Tembakau/rigen terkait rokok; bambu runcing senjata; kupat tahu juga khas Magelang sehingga ambigu. |
| `tmg-5` Kuis pilihan | 8 soal pilihan ganda | **Dihapus.** | Kuis pilihan dihilangkan. |
| Bacaan `tmg-teks-a`…`f` | 6 teks | **Dihapus dari bank.** Isinya menjadi bahan bacaan baru (a → rumpang a; c → B/S a & b; b (bagian kopi) → B/S c; d → rumpang b; e → rumpang c). | Pustaka Temanggung tetap memuat isi lengkapnya. |
| `mgl-1` Susun Gambar | 7 gambar (01–04 papan 3 × 3 diistirahatkan; 05–07 4 × 4) | **05, 06, 07 dipakai utuh** (a/b/c, kunci media `mgl-1-01`…`03`). **01–04 dihapus.** | Sudah 4 × 4 sesuai tingkat sedang. |
| `mgl-2` Isi Rumpang | 10 isian ketik (diistirahatkan) + bacaan "Candi Borobudur" 5 rumpang | **Bacaan dipakai utuh** (variasi a, pengecoh *enam*, *arca*). **10 isian ketik dihapus.** | Isian ketik dihilangkan; bacaan sudah C2–C3. |
| `mgl-3` Benar, Salah, atau Pendapat (beralasan) | 12 pernyataan + alasan | **Dihapus.** Fakta Borobudur, Waisak, dan Gunung Tidar dipakai di 3 bacaan baru. | Format beralasan dan pilihan "Pendapat" dihilangkan. |
| `mgl-4`, `mgl-5` Kuis pilihan | 8 + 6 soal | **Dihapus.** Fakta lonceng Topeng Ireng, gerabah Klipoh, dan getuk dipakai di rumpang b/c dan Cari Objek. | Kuis pilihan dihilangkan. |
| Bacaan `mgl-teks-a`…`e` | 5 teks | **Dihapus dari bank** (a, b, d → B/S a, b, c; c, e → rumpang b, c). | — |
| `wnb-1` Susun Gambar | 4 urutan kartu (diistirahatkan) + 3 jigsaw | **Jigsaw dipakai utuh** (a/b/c, kunci `wnb-1-05`…`07`). **4 urutan kartu dihapus.** | Urutan kartu dihilangkan. |
| `wnb-2` Isi Rumpang | 10 kesimpulan (diistirahatkan) + bacaan "Negeri di Atas Awan" 7 rumpang | **Bacaan dipakai utuh** (variasi a, pengecoh *panas*, *agar*). **10 kesimpulan dihapus** (ide *alat* ongklok dan carica dipakai ulang). | Sudah C3–C4. |
| `wnb-3` Sumber Mana yang Benar | 12 kartu dua sumber | **Diadaptasi.** Pola "sumber resmi vs pesan berantai" ditulis ulang menjadi 3 bacaan tiga paragraf (Telaga Warna, Sikunir, kawah) dengan keterampilan membandingkan, menilai pilihan, dan menentukan solusi. **12 kartu lama dihapus.** | Format baru satu bacaan; keterampilan sulit sesuai permintaan. |
| `wnb-4` Sumber terpercaya, `wnb-5` Tindakan | 8 + 8 soal pilihan | **Dihapus.** | Kuis pilihan dihilangkan. |
| Petunjuk | 55 | **Diganti** 36 petunjuk baru (3 per tantangan, berlaku untuk semua variasinya). | Petunjuk lama terkait soal yang dihapus. |

Ringkasan bank baru: **126 soal** (108 dinilai + 18 benda pengecoh), 30 variasi, 9 bacaan Benar atau Salah, 9 bacaan Isi Rumpang, 36 petunjuk, 12 kata pengecoh bank kata (2 per variasi Magelang dan Wonosobo). Dipakai utuh dari bank lama: 24 soal (9 gambar + 15 rumpang); dipakai dengan teks baru: 7 benda Cari Objek; baru: 95 soal.

---

## 9. Bank soal final kelas 5

Format penulisan:

- Rumpang ditulis per **potongan** persis seperti akan disimpan; `___` = tempat kosong; `⏎⏎` = baris kosong (`\n\n`) jeda paragraf di tengah potongan.
- Jawaban dan pengecoh huruf kecil kecuali nama diri.
- Kolom **B/S** = kunci Benar atau Salah.
- 🖼 = penanda pernyataan bergambar di dokumen ini saja; **tidak** ikut disalin ke teks pernyataan. Gambarnya disebut tepat di bawah tabel bacaan itu.
- Label media (`media.label`): Susun Gambar = judul variasi; pernyataan bergambar = nama sebelum garis miring di baris *Gambar …*; target Cari Objek = nama sebelum garis miring di kolom Media; pengecoh = isi kolom **Benda**; adegan = judul variasi. Keterangan gambar (`media.note`) = kolom *Isi gambar asli* di §10.4.
- Semua soal `review_status = verified`.
- Angka desimal posisi benda di tabel memakai koma (23,5); di berkas data ditulis dengan titik (`23.5`).

### 9.1 Temanggung (mudah)

#### `tmg-1` Susun Gambar Temanggung — 3 × 3 kotak, indikator budaya

| | ID | EN |
|---|---|---|
| Judul | Susun Gambar Temanggung | Temanggung Picture Puzzle |
| Instruksi | Ketuk dua keping untuk menukar tempatnya. Susun sampai gambarnya utuh, lalu baca keterangannya. | Tap two pieces to swap them. Keep going until the picture is complete, then read the caption. |
| Kartu misi | Angin gunung mengacak sebuah lukisan tentang Temanggung. Bantu Jaka menyusunnya kembali, lalu baca keterangannya. | A mountain wind has scrambled a painting of Temanggung. Help Jaka put it back together, then read its caption. |

| Variasi · kode | Judul | Keterangan (ID) | Caption (EN) | Media (kunci lama dipakai ulang) |
|---|---|---|---|---|
| a · `tmg-1-a01` | Gunung Sindoro dan Sumbing | Gunung Sindoro (3.136 m) dan Gunung Sumbing (3.371 m) disebut gunung kembar. Keduanya mengapit Temanggung dan membuat tanahnya subur. | Mount Sindoro (3,136 m) and Mount Sumbing (3,371 m) are called the twin mountains. They stand on either side of Temanggung and make its soil fertile. | `challenge.item.tmg-1-01` — foto lanskap Sindoro–Sumbing dari arah Temanggung/Kledung, langit cerah |
| b · `tmg-1-b01` | Candi Pringapus | Candi Pringapus adalah candi Hindu dari abad ke-9. Di dalam ruang candinya tersimpan arca Nandi, arca lembu yang dihormati umat Hindu. | Pringapus Temple is a Hindu temple from the 9th century. Its chamber holds a statue of Nandi, the bull honoured by Hindus. | `challenge.item.tmg-1-03` — foto Candi Pringapus tampak depan |
| c · `tmg-1-c01` | Kuda Lumping | Kuda lumping atau jaran kepang memakai kuda-kudaan dari anyaman bambu. Di Temanggung ada lebih dari 1.500 kelompok kuda lumping. | Kuda lumping, or jaran kepang, uses horses made of woven bamboo. Temanggung has more than 1,500 kuda lumping groups. | `challenge.item.tmg-1-04` — penari kuda lumping dengan kuda anyaman bambu berwarna |

Petunjuk:

1. Mulailah dari kepingan sudut: sudut punya dua sisi yang menyentuh bingkai. / *Start with the corner pieces: a corner touches the frame on two sides.*
2. Cocokkan warna dan garis pada kepingan yang bersebelahan. / *Match the colours and lines on neighbouring pieces.*
3. Selesaikan baris paling atas dulu, lalu turun baris demi baris. / *Finish the top row first, then work down row by row.*

#### `tmg-2` Lengkapi Bacaan Temanggung — 3 rumpang, 0 pengecoh, Bloom C1–C2, indikator literasi

| | ID | EN |
|---|---|---|
| Judul | Lengkapi Bacaan Temanggung | Complete the Temanggung Reading |
| Instruksi | Baca paragrafnya. Ketuk kotak kosong, lalu ketuk kata yang tepat. Setiap kata dipakai satu kali. | Read the paragraph. Tap an empty box, then tap the right word. Each word is used once. |
| Kartu misi | Catatan Jaka tentang Temanggung ada yang terhapus hujan. Lengkapi bacaannya supaya bisa dibaca lagi. | Some of Jaka's notes about Temanggung were washed away by the rain. Complete the reading so it can be read again. |

**Variasi a — Gunung Kembar / *The Twin Mountains*** (dipakai utuh dari bank lama)

| Kode | Potongan (ID) | Jawaban | Potongan (EN) | Answer | Bloom | Alasan Bloom |
|---|---|---|---|---|---|---|
| `tmg-2-a01` | Kabupaten Temanggung berada di Provinsi Jawa Tengah. Wilayahnya diapit dua gunung besar, yaitu Gunung Sindoro dan Gunung ___. | Sumbing | Temanggung Regency is in the province of Central Java. It lies between two large mountains, Mount Sindoro and Mount ___. | Sumbing | C1 | Mengingat nama gunung yang dikenalkan Mbah Kedu di Kenali Temanggung. |
| `tmg-2-a02` | Karena bentuknya mirip, kedua gunung itu sering disebut gunung ___. | kembar | Because they look alike, the two mountains are often called the ___ mountains. | twin | C2 | Memahami: disimpulkan dari "bentuknya mirip". |
| `tmg-2-a03` | Tanah di lerengnya ___ karena berasal dari abu gunung api. Petani pun dapat menanam kopi dan sayuran di sana. | subur | The soil on their slopes is ___ because it comes from volcanic ash, so farmers can grow coffee and vegetables there. | fertile | C2 | Memahami sebab (abu gunung api) dan akibat (dapat menanam). |

**Variasi b — Umbul Jumprit / *Umbul Jumprit***

| Kode | Potongan (ID) | Jawaban | Potongan (EN) | Answer | Bloom | Alasan Bloom |
|---|---|---|---|---|---|---|
| `tmg-2-b01` | Di lereng Gunung Sindoro ada mata air bernama Umbul Jumprit. Airnya jernih dan tidak pernah kering, bahkan pada musim ___. | kemarau | On the slopes of Mount Sindoro there is a spring called Umbul Jumprit. Its water is clear and never dries up, even in the ___ season. | dry | C2 | Memahami: "tidak pernah kering, bahkan pada musim …" menunjuk musim yang biasanya kering. |
| `tmg-2-b02` | Air dari Umbul ___ mengalir menjadi hulu Sungai Progo. | Jumprit | The water from Umbul ___ flows on to become the source of the Progo River. | Jumprit | C1 | Mengenali kembali nama yang sudah disebut di kalimat pertama. |
| `tmg-2-b03` | Warga menjaga mata air itu dengan tidak membuang ___ di sekitarnya. | sampah | People look after the spring by never throwing ___ around it. | rubbish | C2 | Memahami cara menjaga mata air. |

**Variasi c — Kuda Lumping / *Kuda Lumping***

| Kode | Potongan (ID) | Jawaban | Potongan (EN) | Answer | Bloom | Alasan Bloom |
|---|---|---|---|---|---|---|
| `tmg-2-c01` | Kuda lumping disebut juga jaran kepang. Penarinya menari sambil menunggang kuda-kudaan dari anyaman bambu. Kuda-kudaan ___ itu dicat dan dihias dengan kain warna-warni. | bambu | Kuda lumping is also called jaran kepang. The dancers dance while riding horses made of woven bamboo. The ___ horses are painted and decorated with colourful cloth. | bamboo | C1 | Mengenali kata yang baru saja disebut ("anyaman bambu"). |
| `tmg-2-c02` | Di Temanggung ada lebih dari 1.500 kelompok kuda lumping, jadi kesenian ini sangat ___. | populer | Temanggung has more than 1,500 kuda lumping groups, so this art is very ___. | popular | C2 | Memahami: banyaknya kelompok menunjukkan kesenian itu digemari. |
| `tmg-2-c03` | Agar kesenian ini tidak hilang, anak-anak muda diajak ___ menari kuda lumping. | berlatih | So that this art does not disappear, young people are invited to ___ the kuda lumping dance. | practise | C2 | Memahami tujuan ("agar tidak hilang"). |

Petunjuk `tmg-2`:

1. Baca seluruh paragraf sampai habis dulu, baru isi kotaknya. / *Read the whole paragraph first, then fill in the boxes.*
2. Perhatikan kata-kata di sekitar kotak kosong. Kadang jawabannya sudah disebut di kalimat sebelumnya. / *Look at the words around each box. Sometimes the answer was already mentioned in an earlier sentence.*
3. Setiap kata di bank kata dipakai satu kali. / *Each word in the word bank is used once.*

#### `tmg-3` Benar atau Salah? Temanggung — 1 paragraf, 3 pernyataan, Bloom C1–C2, indikator literasi

| | ID | EN |
|---|---|---|
| Judul | Benar atau Salah? Temanggung | True or False? Temanggung |
| Instruksi | Baca bacaannya, lalu pilih BENAR atau SALAH untuk setiap pernyataan. Ada pernyataan yang memakai gambar. | Read the text, then choose TRUE or FALSE for each statement. One statement uses a picture. |
| Kartu misi | Warga di pasar Temanggung bercerita macam-macam. Cocokkan setiap cerita dengan bacaan: benar atau salah? | People at the Temanggung market tell all sorts of stories. Check each one against the reading: true or false? |

**Bacaan a — `tmg-3-a` Situs Liyangan / *The Liyangan Site***

> **ID:** Situs Liyangan berada di Desa Purbosari, Kecamatan Ngadirejo, Temanggung. Situs ini ditemukan pada tahun 2008 ketika warga sedang menambang pasir. Di dalam tanah, para peneliti menemukan sisa rumah kayu, bangunan candi, jalan batu, dan butir padi yang hangus. Semua itu tertimbun material letusan Gunung Sindoro pada masa lalu.
>
> **EN:** The Liyangan Site is in Purbosari Village, Ngadirejo District, Temanggung. It was found in 2008 while villagers were digging for sand. Under the ground, researchers found the remains of wooden houses, temple buildings, stone paths, and burnt grains of rice. All of it had been buried by material from an eruption of Mount Sindoro long ago.

| Kode | Pernyataan (ID) | Statement (EN) | B/S | Bloom | Keterampilan |
|---|---|---|---|---|---|
| `tmg-3-a01` | Situs Liyangan ditemukan pada tahun 2008. | The Liyangan Site was found in 2008. | Benar | C1 | informasi_tersurat |
| `tmg-3-a02` | Situs Liyangan tertimbun material letusan Gunung Merapi. | The Liyangan Site was buried by material from an eruption of Mount Merapi. | Salah | C1 | mengenali_fakta |
| `tmg-3-a03` | 🖼 Gambar ini menunjukkan salah satu benda yang ditemukan di Situs Liyangan. | This picture shows one of the things found at the Liyangan Site. | Benar | C2 | mengenali_gambar |

Gambar `tmg-3-a03`: `challenge.item.tmg-3-a03` — *Butir padi hangus / Burnt rice grains*: foto dekat butir-butir padi yang hangus kehitaman (temuan arkeologi), 480 × 360 px.

**Bacaan b — `tmg-3-b` Candi Pringapus dan Prasasti Gondosuli / *Pringapus Temple and the Gondosuli Inscription***

> **ID:** Candi Pringapus adalah candi Hindu peninggalan abad ke-9 di Temanggung. Di dalam ruang candinya ada arca Nandi, yaitu arca lembu yang dihormati umat Hindu. Temanggung juga punya Prasasti Gondosuli yang dibuat pada tahun 832 Masehi. Prasasti adalah tulisan pada batu atau logam yang mencatat peristiwa penting.
>
> **EN:** Pringapus Temple is a Hindu temple from the 9th century in Temanggung. Inside its chamber is a statue of Nandi, the bull honoured by Hindus. Temanggung also has the Gondosuli Inscription, made in the year 832 CE. An inscription is writing on stone or metal that records an important event.

| Kode | Pernyataan (ID) | Statement (EN) | B/S | Bloom | Keterampilan |
|---|---|---|---|---|---|
| `tmg-3-b01` | Candi Pringapus adalah candi Buddha. | Pringapus Temple is a Buddhist temple. | Salah | C1 | mengenali_fakta |
| `tmg-3-b02` | Prasasti Gondosuli dibuat pada tahun 832 Masehi. | The Gondosuli Inscription was made in 832 CE. | Benar | C1 | informasi_tersurat |
| `tmg-3-b03` | 🖼 Gambar ini menunjukkan arca yang ada di dalam ruang Candi Pringapus. | This picture shows the statue inside the chamber of Pringapus Temple. | Salah | C2 | mengenali_gambar |

Gambar `tmg-3-b03`: `challenge.item.tmg-3-b03` — *Arca Buddha duduk / Seated Buddha statue*: foto arca Buddha duduk dari batu (mis. dari Candi Borobudur atau Mendut). Gambar sengaja **tidak sesuai** bacaan: arca di Candi Pringapus adalah arca Nandi (lembu).

**Bacaan c — `tmg-3-c` Kopi dari Lereng Gunung / *Coffee from the Mountain Slopes***

> **ID:** Petani di lereng Gunung Sindoro dan Gunung Sumbing menanam kopi. Kopi arabika dari lereng itu tumbuh di ketinggian lebih dari 900 meter. Setelah dipetik, buah kopi diolah dan dikeringkan. Bijinya lalu disangrai sampai berwarna cokelat dan harum. Kopi mengandung kafein, jadi kopi adalah minuman untuk orang dewasa, bukan untuk anak-anak.
>
> **EN:** Farmers on the slopes of Mount Sindoro and Mount Sumbing grow coffee. Arabica coffee from those slopes grows at more than 900 metres above sea level. After it is picked, the coffee fruit is processed and dried. The beans are then roasted until they are brown and fragrant. Coffee contains caffeine, so it is a drink for adults, not for children.

| Kode | Pernyataan (ID) | Statement (EN) | B/S | Bloom | Keterampilan |
|---|---|---|---|---|---|
| `tmg-3-c01` | Kopi arabika di lereng Sindoro dan Sumbing tumbuh di ketinggian lebih dari 900 meter. | Arabica coffee on the slopes of Sindoro and Sumbing grows at more than 900 metres. | Benar | C1 | informasi_tersurat |
| `tmg-3-c02` | Menurut bacaan, kopi adalah minuman yang cocok untuk anak-anak. | According to the text, coffee is a drink that suits children. | Salah | C1 | mengenali_fakta |
| `tmg-3-c03` | 🖼 Gambar ini menunjukkan biji kopi yang sudah disangrai. | This picture shows coffee beans that have been roasted. | Benar | C2 | mengenali_gambar |

Gambar `tmg-3-c03`: `challenge.item.tmg-3-c03` — *Biji kopi sangrai / Roasted coffee beans*: segenggam biji kopi sangrai berwarna cokelat, 480 × 360 px.

Petunjuk `tmg-3`:

1. Cari kata kunci pernyataan (nama, angka, tempat) di dalam bacaan. / *Find the key words of the statement (names, numbers, places) in the reading.*
2. Pernyataan BENAR bila isinya sama dengan bacaan. Satu kata atau angka yang berbeda membuatnya SALAH. / *A statement is TRUE when it says the same as the reading. One different word or number makes it FALSE.*
3. Untuk pernyataan bergambar, cocokkan isi gambar dengan benda yang disebut di bacaan. / *For a picture statement, match what the picture shows with the thing named in the reading.*

#### `tmg-4` Cari Objek Temanggung — 4 dicari + 4 pengecoh, Bloom C1–C2, indikator budaya

| | ID | EN |
|---|---|---|
| Judul | Cari Objek Temanggung | Find the Objects: Temanggung |
| Instruksi | Baca petunjuk Mbah Kedu, lalu ketuk benda yang tepat. Ada empat benda yang harus kamu temukan. | Read Mbah Kedu's clue, then tap the right object. There are four objects to find. |
| Kartu misi | Di pasar budaya, benda khas Temanggung tercampur dengan benda dari daerah lain. Temukan empat benda yang dimaksud Mbah Kedu. | At the culture fair, typical Temanggung objects are mixed with objects from other regions. Find the four objects Mbah Kedu describes. |
| Judul variasi a | Pasar Budaya Temanggung | Temanggung Culture Fair |
| Adegan | `challenge.tmg-4.scene` (kunci lama) — ilustrasi 16:9 halaman balai desa/pasar budaya di kaki Sindoro–Sumbing: tiga deret rak pajang kosong (permukaan rak ± 27 %, 61 %, 93 % tinggi gambar), gunung kembar di latar. Benda **tidak** digambar di adegan. Kedelapan benda memakai **posisi lama** (8 dari 11 tempat di tata letak lama), sehingga gambar adegan asli yang mungkin sudah diunggah tetap pas; tiga tempat lama yang tidak terpakai (4 · 6, 44 · 72, 84 · 72) cukup tampak sebagai rak kosong. | |

Benda dicari (target, `scorable = 1`):

| Kode | Petunjuk (ID) | Clue (EN) | Bloom | Posisi x · y · w (%) | Media |
|---|---|---|---|---|---|
| `tmg-4-a01` | Temukan kuda-kudaan dari anyaman bambu yang dipakai untuk menari kuda lumping. | Find the woven bamboo horse used for the kuda lumping dance. | C1 | 84 · 6 · 12 | `challenge.item.tmg-4-03` · *Kuda kepang bambu / Bamboo hobby horse* |
| `tmg-4-a02` | Temukan biji kopi yang tumbuh di lereng Gunung Sindoro dan Sumbing. | Find the coffee beans that grow on the slopes of Mount Sindoro and Mount Sumbing. | C1 | 44 · 6 · 12 | `challenge.item.tmg-4-02` · *Biji kopi / Coffee beans* |
| `tmg-4-a03` | Temukan arca lembu Nandi, seperti yang ada di dalam Candi Pringapus. | Find the statue of Nandi the bull, like the one inside Pringapus Temple. | C1 | 4 · 72 · 12 | `challenge.item.tmg-4-06` · *Arca Nandi / Nandi statue* |
| `tmg-4-a04` | Temukan ikan uceng, ikan sungai kecil yang menjadi makanan khas Temanggung. | Find the uceng fish, a small river fish that is a special food of Temanggung. | C2 | 14 · 40 · 12 | `challenge.item.tmg-4-04` · *Ikan uceng / Uceng fish* |

Pengecoh (`decoy = 1`, `scorable = 0`):

| Kode | Benda | Umpan balik bila diketuk (ID) | Feedback (EN) | Posisi | Media |
|---|---|---|---|---|---|
| `tmg-4-a05` | Angklung | Ini angklung, alat musik bambu dari Jawa Barat — bukan benda yang dimaksud petunjuk. | This is an angklung, a bamboo instrument from West Java — not the object in the clue. | 74 · 40 · 12 | `challenge.item.tmg-4-09` |
| `tmg-4-a06` | Ondel-ondel | Ini ondel-ondel, boneka raksasa dari budaya Betawi di Jakarta — bukan dari Temanggung. | This is an ondel-ondel, a giant puppet from Betawi culture in Jakarta — not from Temanggung. | 24 · 72 · 12 | `challenge.item.tmg-4-10` |
| `tmg-4-a07` | Perahu pinisi | Ini perahu pinisi dari Sulawesi Selatan. Temanggung ada di pegunungan, jauh dari laut. | This is a pinisi boat from South Sulawesi. Temanggung is in the mountains, far from the sea. | 64 · 6 · 12 | `challenge.item.tmg-4-11` |
| `tmg-4-a08` | Sasando | Ini sasando, alat musik petik dari Pulau Rote, Nusa Tenggara Timur — bukan dari Temanggung. | This is a sasando, a plucked instrument from Rote Island, East Nusa Tenggara — not from Temanggung. | 54 · 40 · 12 | `challenge.item.tmg-4-12` (**baru**) |

Petunjuk `tmg-4`:

1. Baca petunjuknya pelan-pelan: nama, bahan, atau kegunaan benda disebut di sana. / *Read the clue slowly: it names the object, its material, or its use.*
2. Ingat cerita Mbah Kedu di Kenali Temanggung. / *Remember Mbah Kedu's story in Get to Know Temanggung.*
3. Benda dari daerah lain juga berharga, tetapi bukan jawaban tantangan ini. / *Objects from other regions are valuable too, but they are not the answers in this challenge.*

### 9.2 Magelang (sedang)

#### `mgl-1` Susun Gambar Magelang — 4 × 4 kotak, indikator budaya

| | ID | EN |
|---|---|---|
| Judul | Susun Gambar Magelang | Magelang Picture Puzzle |
| Instruksi | Gambarnya terbagi menjadi 4 × 4 keping. Ketuk dua keping untuk menukar tempatnya sampai gambarnya utuh, lalu baca kisahnya. | The picture is cut into 4 × 4 pieces. Tap two pieces to swap them until the picture is complete, then read its story. |
| Kartu misi | Di Magelang, gambar-gambar warisan budaya berserakan seperti kepingan relief. Susun kembali, lalu hubungkan gambar itu dengan kisah di bawahnya. | In Magelang, pictures of cultural heritage are scattered like pieces of a relief. Put them back together, then connect the picture with the story below it. |

| Variasi · kode | Judul | Keterangan (ID) | Caption (EN) | Media |
|---|---|---|---|---|
| a · `mgl-1-a01` | Stupa Borobudur | Candi Borobudur dibangun pada abad ke-8 dan ke-9. Di teras bundarnya berdiri 72 stupa berlubang, masing-masing berisi arca Buddha. | Borobudur Temple was built in the 8th and 9th centuries. On its round terraces stand 72 perforated stupas, each holding a Buddha statue. | `challenge.item.mgl-1-01` — stupa berlubang di teras atas Borobudur, stupa induk di latar |
| b · `mgl-1-b01` | Penari Topeng Ireng | Penari Topeng Ireng memakai hiasan kepala dari bulu berwarna-warni dan lonceng kecil di kaki. Tarian ini tumbuh di Desa Tuksongo, Borobudur. | Topeng Ireng dancers wear colourful feather headdresses and small bells on their ankles. The dance grew in Tuksongo Village, Borobudur. | `challenge.item.mgl-1-02` — penari Topeng Ireng dengan kuluk bulu warna-warni |
| c · `mgl-1-c01` | Candi Mendut | Candi Mendut menyimpan arca Buddha setinggi sekitar tiga meter. Setiap Waisak, prosesi umat Buddha dimulai dari candi ini. | Mendut Temple holds a Buddha statue about three metres tall. Every Vesak, the Buddhist procession starts from this temple. | `challenge.item.mgl-1-03` — Candi Mendut tampak depan dengan tangga masuk |

Petunjuk:

1. Mulai dari kepingan yang punya garis langit atau garis tanah. / *Start with the pieces that show the sky line or the ground line.*
2. Kepingan dengan warna dan garis yang bersambung pasti bersebelahan. / *Pieces whose colours and lines continue must sit next to each other.*
3. Kerjakan baris demi baris: lengkapi dulu empat keping di baris paling atas. / *Work row by row: first complete the four pieces of the top row.*

#### `mgl-2` Lengkapi Bacaan Magelang — 5 rumpang + 2 pengecoh, Bloom C2–C3, indikator literasi

| | ID | EN |
|---|---|---|
| Judul | Lengkapi Bacaan Magelang | Complete the Magelang Reading |
| Instruksi | Baca bacaannya sampai habis. Pilih kata yang tepat untuk setiap kotak kosong. Ada dua kata yang tidak terpakai. | Read the whole text. Choose the right word for each empty box. Two words will not be used. |
| Kartu misi | Juru kunci candi menitipkan catatan tentang Magelang, tetapi beberapa katanya hilang. Hubungkan informasi di dalam bacaan, lalu lengkapi catatannya. | The temple keeper left notes about Magelang, but some words are missing. Connect the information in the reading, then complete the notes. |

**Variasi a — Candi Borobudur / *Borobudur Temple*** (dipakai utuh) · pengecoh: **enam** / *six*, **arca** / *statues*

| Kode | Potongan (ID) | Jawaban | Potongan (EN) | Answer | Bloom | Alasan Bloom |
|---|---|---|---|---|---|---|
| `mgl-2-a01` | Candi Borobudur berdiri di Kabupaten Magelang. Candi Buddha ini dibangun pada abad ke-8 dan ke-9. Bangunannya bertingkat-tingkat. Di bagian bawah ada enam teras persegi, lalu di atasnya ada tiga teras bundar. Jadi, Borobudur memiliki ___ teras. | sembilan | Borobudur Temple stands in Magelang Regency. This Buddhist temple was built in the 8th and 9th centuries. The building rises in levels. At the bottom there are six square terraces, and above them there are three round terraces. So Borobudur has ___ terraces. | nine | C3 | Menerapkan penjumlahan 6 + 3; pengecoh *enam* menjebak pembaca yang tergesa. |
| `mgl-2-a02` | Di teras bundar berdiri 72 stupa berlubang. Stupa adalah bangunan berbentuk lonceng. Bangunan berbentuk lonceng yang paling besar ada di puncak candi dan disebut ___ induk.⏎⏎Dinding candi dihiasi ribuan pahatan gambar yang menceritakan sebuah kisah. | stupa | On the round terraces stand 72 perforated stupas. A stupa is a bell-shaped structure. The largest bell-shaped structure is at the top of the temple and is called the main ___.⏎⏎The temple walls are decorated with thousands of carved pictures that tell a story. | stupa | C3 | Menerapkan definisi stupa pada bangunan di puncak. |
| `mgl-2-a03` | Pahatan bercerita seperti itu disebut ___. | relief | Carved story pictures like these are called ___. | reliefs | C2 | Memahami istilah dari penjelasannya. |
| `mgl-2-a04` | Bila ingin melihatnya, perhatikan bagian ___ candi. | dinding | To see them, look at the temple ___. | walls | C3 | Memakai informasi letak relief untuk tugas baru. |
| `mgl-2-a05` | Batu-batu candi bisa rusak bila diinjak dan dipanjat. Oleh karena itu, pengunjung ___ memanjat stupa. | dilarang | The temple stones can be damaged when people step or climb on them. That is why visitors are ___ to climb the stupas. | forbidden | C2 | Memahami hubungan sebab–akibat. |

**Variasi b — Tari Topeng Ireng / *The Topeng Ireng Dance*** · pengecoh: **nelayan** / *fishermen*, **diam** / *silent*

| Kode | Potongan (ID) | Jawaban | Potongan (EN) | Answer | Bloom | Alasan Bloom |
|---|---|---|---|---|---|---|
| `mgl-2-b01` | Topeng Ireng adalah tari rakyat dari Kabupaten Magelang. Tarian ini mulai tumbuh sekitar tahun 1950-an di Desa Tuksongo. Desa itu berada di Kecamatan Borobudur, tidak jauh dari Candi ___. | Borobudur | Topeng Ireng is a folk dance from Magelang Regency. It began to grow around the 1950s in Tuksongo Village. The village is in Borobudur District, not far from ___ Temple. | Borobudur | C2 | Memahami: menghubungkan nama kecamatan dengan candi terdekat. |
| `mgl-2-b02` | Penarinya memakai kuluk, yaitu hiasan kepala dari bulu yang berwarna-warni. Di pergelangan kaki mereka terpasang lonceng-lonceng kecil. Setiap kali kaki dihentakkan, lonceng itu ___.⏎⏎Satu kelompok Topeng Ireng bisa berisi puluhan penari yang bergerak serempak. | berbunyi | The dancers wear a kuluk, a head decoration made of colourful feathers. Small bells are tied around their ankles. Every time they stamp their feet, the bells ___.⏎⏎One Topeng Ireng group can have dozens of dancers moving at the same time. | ring | C2 | Memahami fungsi lonceng; pengecoh *diam* bertentangan dengan "dihentakkan". |
| `mgl-2-b03` | Karena itu, para penari harus berlatih bersama supaya ___. | kompak | That is why the dancers must practise together so that they move in ___. | harmony | C3 | Menerapkan: gerak serempak menuntut kekompakan. |
| `mgl-2-b04` | Tarian ini lahir di desa-desa lereng gunung yang warganya banyak bertani. Gerakannya yang tegas menggambarkan semangat para ___ itu. | petani | The dance was born in mountain-slope villages where many people farm. Its firm movements show the spirit of those ___. | farmers | C2 | Memahami rujukan "warganya banyak bertani"; pengecoh *nelayan* menguji ketelitian. |
| `mgl-2-b05` | Kalau kamu menonton pertunjukannya, duduklah dengan tenang dan jangan ___ para penari. | mengganggu | If you watch a performance, sit quietly and do not ___ the dancers. | disturb | C3 | Menerapkan sikap menonton pertunjukan. |

**Variasi c — Getuk dan Gerabah Klipoh / *Getuk and Klipoh Pottery*** · pengecoh: **beras** / *rice*, **sawah** / *field*

| Kode | Potongan (ID) | Jawaban | Potongan (EN) | Answer | Bloom | Alasan Bloom |
|---|---|---|---|---|---|---|
| `mgl-2-c01` | Magelang dijuluki Kota Getuk. Getuk dibuat dari singkong yang dikukus, ditumbuk halus, lalu diberi gula. Jadi, bahan utama getuk adalah ___. | singkong | Magelang is nicknamed the Getuk Town. Getuk is made from cassava that is steamed, pounded smooth, and then sweetened. So the main ingredient of getuk is ___. | cassava | C2 | Memahami bahan utama dari langkah pembuatan; pengecoh *beras* ada di kalimat lain. |
| `mgl-2-c02` | Julukan Kota Getuk bermula pada masa penjajahan Jepang. Saat itu beras sulit didapat, sehingga warga memakai singkong sebagai ___ beras.⏎⏎Sekitar tiga kilometer dari Candi Borobudur ada Dusun Klipoh. Warga Klipoh membuat kendil, cobek, dan kuali dari tanah liat. Tanah liat itu dibentuk, lalu dibakar agar keras. | pengganti | The nickname began during the Japanese occupation. Rice was hard to find then, so people used cassava as a ___ for rice.⏎⏎About three kilometres from Borobudur Temple lies Klipoh Hamlet. The people of Klipoh make cooking pots, grinding bowls, and woks from clay. The clay is shaped and then fired to make it hard. | substitute | C3 | Menerapkan hubungan sebab–akibat pada peristiwa sejarah. |
| `mgl-2-c03` | Benda-benda dari tanah liat yang dibakar seperti itu disebut ___. | gerabah | Objects made of fired clay like these are called ___. | pottery | C2 | Mengklasifikasikan benda menurut ciri; kata *gerabah* muncul lagi di kalimat terakhir. |
| `mgl-2-c04` | Kendil, cobek, dan kuali dipakai untuk memasak, jadi semuanya berguna di ___. | dapur | Cooking pots, grinding bowls, and woks are used for cooking, so they are all useful in the ___. | kitchen | C3 | Menerapkan kegunaan benda; pengecoh *sawah*. |
| `mgl-2-c05` | Keahlian membuat gerabah diwariskan turun-temurun, yaitu diajarkan orang tua kepada ___ mereka. | anak | The skill of making pottery is passed down: parents teach it to their ___. | children | C2 | Memahami arti "turun-temurun". |

Petunjuk `mgl-2`:

1. Jawabannya tidak selalu tertulis langsung. Hubungkan dua informasi di dalam bacaan. / *The answer is not always written directly. Connect two pieces of information in the reading.*
2. Baca kalimat sebelum dan sesudah kotak kosong, lalu pilih kata yang membuat kalimatnya masuk akal. / *Read the sentences before and after the box, then choose the word that makes sense.*
3. Ada dua kata pengecoh. Setiap kata hanya dipakai satu kali. / *There are two decoy words. Each word is used only once.*

#### `mgl-3` Benar atau Salah? Magelang — 2 paragraf, 5 pernyataan, Bloom C2–C4, indikator literasi

| | ID | EN |
|---|---|---|
| Judul | Benar atau Salah? Magelang | True or False? Magelang |
| Instruksi | Baca kedua paragraf. Hubungkan informasinya, lalu pilih BENAR atau SALAH untuk setiap pernyataan. | Read both paragraphs. Connect the information, then choose TRUE or FALSE for each statement. |
| Kartu misi | Pengunjung Magelang saling bercerita. Baca bacaannya, hubungkan informasinya, lalu tentukan mana yang benar dan mana yang salah. | Visitors in Magelang are telling each other stories. Read the text, connect the information, then decide which are true and which are false. |

**Bacaan a — `mgl-3-a` Menjaga Candi Borobudur / *Caring for Borobudur Temple***

> **ID:** Candi Borobudur adalah candi Buddha terbesar di dunia. Candi ini dibangun pada abad ke-8 dan ke-9 tanpa semen. Batu-batunya disusun saling mengunci. Dindingnya dihiasi 2.672 panel relief yang bercerita. Pada tahun 1991, UNESCO menetapkan Borobudur sebagai Warisan Dunia. Artinya, Borobudur dianggap berharga bagi seluruh umat manusia.
>
> Karena sangat berharga, Borobudur harus dijaga bersama. Pengunjung diminta mengikuti rute dan aturan dari pengelola. Pengunjung tidak boleh memanjat stupa atau menyentuh relief, sebab minyak dan keringat tangan dapat merusak batu. Pengunjung juga tidak boleh mencoret-coret dinding dan membuang sampah sembarangan.
>
> **EN:** Borobudur Temple is the largest Buddhist temple in the world. It was built in the 8th and 9th centuries without cement. Its stones are fitted together so that they lock. Its walls are decorated with 2,672 relief panels that tell stories. In 1991, UNESCO named Borobudur a World Heritage Site. This means it is considered precious for all of humanity.
>
> Because it is so precious, Borobudur must be looked after together. Visitors are asked to follow the routes and rules of the site managers. Visitors may not climb the stupas or touch the reliefs, because oil and sweat from hands can damage the stone. Visitors also may not scribble on the walls or drop litter.

| Kode | Pernyataan (ID) | Statement (EN) | B/S | Bloom | Keterampilan |
|---|---|---|---|---|---|
| `mgl-3-a01` | Gagasan pokok paragraf kedua adalah cara menjaga Candi Borobudur bersama-sama. | The main idea of the second paragraph is how to look after Borobudur Temple together. | Benar | C2 | gagasan_pokok |
| `mgl-3-a02` | Borobudur harus dijaga bersama karena dianggap berharga bagi seluruh umat manusia. | Borobudur must be looked after together because it is considered precious for all of humanity. | Benar | C4 | menghubungkan_informasi |
| `mgl-3-a03` | Menyentuh relief dengan tangan termasuk tindakan menjaga Borobudur. | Touching the reliefs with your hands is a way of looking after Borobudur. | Salah | C3 | mengklasifikasikan_tindakan |
| `mgl-3-a04` | Membawa pulang sampah sendiri termasuk tindakan menjaga Borobudur. | Taking your own rubbish home is a way of looking after Borobudur. | Benar | C3 | mengklasifikasikan_tindakan |
| `mgl-3-a05` | Batu-batu Borobudur direkatkan dengan semen. | The stones of Borobudur are stuck together with cement. | Salah | C2 | menghubungkan_informasi |

**Bacaan b — `mgl-3-b` Perjalanan Waisak / *The Vesak Walk***

> **ID:** Tidak jauh dari Candi Borobudur ada dua candi Buddha lain, yaitu Candi Pawon dan Candi Mendut. Bila ditarik garis dari timur ke barat, Candi Mendut, Candi Pawon, dan Candi Borobudur berada hampir pada satu garis lurus. Candi Pawon terletak di tengah. Candi Mendut diperkirakan lebih tua daripada Candi Borobudur.
>
> Setiap perayaan Waisak, ribuan umat Buddha berjalan kaki dari Candi Mendut ke Candi Pawon, lalu berakhir di Candi Borobudur. Mereka membawa api dan air suci. Air suci itu diambil dari Umbul Jumprit di Temanggung. Saat menyaksikan perayaan agama lain, kita menghormatinya dengan menjaga ketenangan, memberi jalan, dan tidak mengganggu umat yang beribadah.
>
> **EN:** Not far from Borobudur Temple there are two other Buddhist temples, Pawon Temple and Mendut Temple. If a line is drawn from east to west, Mendut, Pawon, and Borobudur lie almost on one straight line. Pawon Temple is in the middle. Mendut Temple is thought to be older than Borobudur.
>
> Every Vesak, thousands of Buddhists walk from Mendut Temple to Pawon Temple, ending at Borobudur Temple. They carry fire and holy water. The holy water is taken from Umbul Jumprit in Temanggung. When we watch another religion's celebration, we show respect by keeping quiet, making way, and not disturbing the people who are worshipping.

| Kode | Pernyataan (ID) | Statement (EN) | B/S | Bloom | Keterampilan |
|---|---|---|---|---|---|
| `mgl-3-b01` | Paragraf pertama terutama menjelaskan letak tiga candi yang hampir segaris. | The first paragraph mainly explains where the three temples lie in almost one line. | Benar | C2 | gagasan_pokok |
| `mgl-3-b02` | Dalam perjalanan Waisak, umat Buddha melewati Candi Pawon sebelum tiba di Candi Borobudur. | On the Vesak walk, Buddhists pass Pawon Temple before they reach Borobudur Temple. | Benar | C4 | menghubungkan_informasi |
| `mgl-3-b03` | Air suci untuk perayaan Waisak di Magelang diambil dari wilayah Wonosobo. | The holy water for the Vesak celebration in Magelang is taken from Wonosobo. | Salah | C4 | menghubungkan_informasi |
| `mgl-3-b04` | Memotret dari dekat sambil menghalangi jalan umat yang berjalan termasuk sikap menghormati perayaan. | Taking close-up photos while blocking the path of the worshippers is a way of showing respect for the celebration. | Salah | C3 | mengklasifikasikan_tindakan |
| `mgl-3-b05` | Berbicara pelan saat melihat umat beribadah termasuk sikap menghormati perayaan agama lain. | Speaking softly while watching people worship is a way of respecting another religion's celebration. | Benar | C3 | mengklasifikasikan_tindakan |

**Bacaan c — `mgl-3-c` Gunung Tidar / *Mount Tidar***

> **ID:** Di tengah Kota Magelang ada bukit hijau bernama Gunung Tidar. Tingginya sekitar 503 meter di atas permukaan laut. Banyak orang mendaki tangganya untuk berolahraga. Dalam cerita rakyat, Gunung Tidar disebut paku tanah Jawa. Konon, Pulau Jawa dulu bergoyang-goyang, lalu dipaku di Gunung Tidar agar tenang. Cerita seperti ini disebut legenda, bukan penjelasan ilmiah.
>
> Di lembah Gunung Tidar berdiri Akademi Militer sejak tahun 1957. Di sana para taruna dididik menjadi perwira TNI Angkatan Darat. Kota Magelang berulang tahun setiap tanggal 11 April. Tanggal itu diambil dari Prasasti Mantyasih yang dibuat pada tahun 907 Masehi.
>
> **EN:** In the middle of Magelang City is a green hill called Mount Tidar. It is about 503 metres above sea level. Many people climb its steps to exercise. In folklore, Mount Tidar is called the nail of Java. It is said that the island of Java once wobbled, and was nailed down at Mount Tidar to keep it still. A story like this is called a legend, not a scientific explanation.
>
> In the valley of Mount Tidar, the Military Academy has stood since 1957. There, cadets are trained to become officers of the Indonesian Army. Magelang City celebrates its birthday every 11 April. The date comes from the Mantyasih Inscription, which was made in 907 CE.

| Kode | Pernyataan (ID) | Statement (EN) | B/S | Bloom | Keterampilan |
|---|---|---|---|---|---|
| `mgl-3-c01` | Paragraf pertama terutama menceritakan Gunung Tidar dan legendanya. | The first paragraph mainly tells about Mount Tidar and its legend. | Benar | C2 | gagasan_pokok |
| `mgl-3-c02` | Cerita bahwa Pulau Jawa dipaku di Gunung Tidar adalah penjelasan ilmiah. | The story that Java was nailed down at Mount Tidar is a scientific explanation. | Salah | C2 | menghubungkan_informasi |
| `mgl-3-c03` | Mendaki tangga Gunung Tidar dapat digolongkan sebagai kegiatan berolahraga. | Climbing the steps of Mount Tidar can be grouped as exercise. | Benar | C2 | mengklasifikasikan_tindakan |
| `mgl-3-c04` | Hari ulang tahun Kota Magelang berasal dari tanggal Prasasti Mantyasih. | Magelang City's birthday comes from the date of the Mantyasih Inscription. | Benar | C4 | menghubungkan_informasi |
| `mgl-3-c05` | Prasasti Mantyasih dibuat sesudah Akademi Militer berdiri. | The Mantyasih Inscription was made after the Military Academy was built. | Salah | C4 | menghubungkan_informasi |

Petunjuk `mgl-3`:

1. Gagasan pokok adalah hal yang dibicarakan seluruh paragraf, bukan satu kalimat saja. / *The main idea is what the whole paragraph is about, not just one sentence.*
2. Hubungkan informasi dari paragraf pertama dan kedua. / *Connect information from the first and second paragraphs.*
3. Tindakan yang baik sesuai dengan aturan atau pesan di bacaan; tindakan yang melanggarnya tidak. / *A good action follows the rules or advice in the reading; an action that breaks them does not.*

#### `mgl-4` Cari Objek Magelang — 4 dicari + 6 pengecoh, Bloom C2–C3, indikator budaya

| | ID | EN |
|---|---|---|
| Judul | Cari Objek Magelang | Find the Objects: Magelang |
| Instruksi | Baca petunjuknya dengan teliti, lalu ketuk benda yang tepat. Hati-hati, ada benda yang mirip! | Read each clue carefully, then tap the right object. Careful, some objects look alike! |
| Kartu misi | Di galeri budaya Magelang, benda-benda khas tercampur dengan benda yang mirip. Baca petunjuknya dengan teliti dan temukan empat benda yang dimaksud. | In Magelang's culture gallery, special objects are mixed with look-alikes. Read the clues carefully and find the four objects. |
| Judul variasi a | Galeri Budaya Magelang | Magelang Culture Gallery |
| Adegan | `challenge.mgl-4.scene` (**baru**) — ilustrasi 16:9 ruang galeri/pendapa budaya dengan dua deret meja pajang kayu, siluet stupa Borobudur dan Gunung Tidar di jendela latar. Benda **tidak** digambar di adegan. | |

Benda dicari:

| Kode | Petunjuk (ID) | Clue (EN) | Bloom | Posisi x · y · w | Media |
|---|---|---|---|---|---|
| `mgl-4-a01` | Temukan bangunan kecil berbentuk lonceng yang berlubang-lubang. Di teras bundar Borobudur ada 72 bangunan seperti ini. | Find the small bell-shaped structure full of holes. Borobudur has 72 of these on its round terraces. | C2 | 23,5 · 17 · 12 | `challenge.item.mgl-4-a01` · *Stupa berlubang / Perforated stupa* |
| `mgl-4-a02` | Temukan hiasan kepala dari bulu warna-warni yang dipakai penari tari rakyat dari Desa Tuksongo, Magelang. | Find the colourful feather headdress worn by dancers of the folk dance from Tuksongo Village, Magelang. | C3 | 4 · 56 · 12 | `challenge.item.mgl-4-a02` · *Kuluk Topeng Ireng / Topeng Ireng headdress* |
| `mgl-4-a03` | Temukan jajanan dari singkong yang dikukus, ditumbuk, lalu diberi gula. Jajanan ini membuat Magelang punya julukan khusus. | Find the snack made of cassava that is steamed, pounded, and sweetened. This snack gave Magelang a special nickname. | C2 | 62,5 · 17 · 12 | `challenge.item.mgl-4-a03` · *Getuk / Getuk* |
| `mgl-4-a04` | Temukan benda dari tanah liat yang dibentuk lalu dibakar, buatan warga Dusun Klipoh. | Find the object made of clay that is shaped and then fired, made by the people of Klipoh Hamlet. | C3 | 62,5 · 56 · 12 | `challenge.item.mgl-4-a04` · *Kendil gerabah / Clay cooking pot* |

Pengecoh:

| Kode | Benda | Umpan balik (ID) | Feedback (EN) | Posisi | Media |
|---|---|---|---|---|---|
| `mgl-4-a05` | Kuda kepang bambu | Ini kuda kepang dari Temanggung, wilayah yang sudah kamu jelajahi. Baca lagi petunjuknya. | This is a kuda kepang from Temanggung, the region you have already explored. Read the clue again. | 4 · 17 · 12 | `challenge.item.mgl-4-a05` |
| `mgl-4-a06` | Manisan carica dalam stoples | Ini manisan carica, oleh-oleh khas Wonosobo. | This is sweet carica in syrup, a souvenir from Wonosobo. | 82 · 17 · 12 | `challenge.item.mgl-4-a06` |
| `mgl-4-a07` | Dadak merak Reog | Ini dadak merak dari Reog Ponorogo, Jawa Timur. Bulunya bulu merak, bukan kuluk penari dari Magelang. | This is the dadak merak from Reog Ponorogo in East Java. It is made of peacock feathers, not the headdress of the Magelang dancers. | 43 · 17 · 12 | `challenge.item.mgl-4-a07` |
| `mgl-4-a08` | Angklung | Ini angklung dari Jawa Barat — bukan benda yang dimaksud petunjuk. | This is an angklung from West Java — not the object in the clue. | 43 · 56 · 12 | `challenge.item.mgl-4-a08` |
| `mgl-4-a09` | Bakpia | Ini bakpia, jajanan dari Yogyakarta. Getuk dibuat dari singkong. | This is bakpia, a snack from Yogyakarta. Getuk is made from cassava. | 23,5 · 56 · 12 | `challenge.item.mgl-4-a09` |
| `mgl-4-a10` | Panci logam | Ini panci dari logam, bukan gerabah dari tanah liat. | This is a metal pot, not pottery made of clay. | 82 · 56 · 12 | `challenge.item.mgl-4-a10` |

Petunjuk `mgl-4`:

1. Petunjuknya tidak selalu menyebut nama benda. Cocokkan ciri-cirinya: bahan, bentuk, dan kegunaannya. / *The clue does not always name the object. Match its features: material, shape, and use.*
2. Hati-hati dengan benda yang mirip, misalnya hiasan dari bulu atau jajanan. / *Watch out for objects that look alike, such as feather decorations or snacks.*
3. Ada benda dari Temanggung, Wonosobo, dan daerah lain. Pilih yang sesuai petunjuk. / *There are objects from Temanggung, Wonosobo, and other places. Choose the one that matches the clue.*

### 9.3 Wonosobo (sulit)

#### `wnb-1` Susun Gambar Wonosobo — 4 × 4 jigsaw, indikator budaya

| | ID | EN |
|---|---|---|
| Judul | Susun Gambar Wonosobo | Wonosobo Picture Puzzle |
| Instruksi | Kepingannya berbentuk puzzle (4 × 4). Ketuk dua keping untuk menukar tempatnya sampai gambarnya utuh, lalu baca keterangannya. | The pieces are jigsaw-shaped (4 × 4). Tap two pieces to swap them until the picture is complete, then read the caption. |
| Kartu misi | Kabut tebal Dieng memotong-motong sebuah lukisan Wonosobo seperti kepingan puzzle. Susun kembali kepingnya, lalu kenali tempat di gambar itu. | The thick Dieng mist has cut a painting of Wonosobo into jigsaw pieces. Put the pieces back together, then get to know the place in the picture. |

| Variasi · kode | Judul | Keterangan (ID) | Caption (EN) | Media |
|---|---|---|---|---|
| a · `wnb-1-a01` | Telaga Warna | Air Telaga Warna di Dieng mengandung belerang. Saat terkena cahaya matahari, warnanya tampak berganti: hijau, kebiruan, atau putih. Airnya tidak untuk diminum. | The water of Telaga Warna in Dieng contains sulphur. In sunlight its colour seems to change: green, bluish, or white. The water is not for drinking. | `challenge.item.wnb-1-05` — Telaga Warna dari tepi/bukit, warna hijau kebiruan jelas |
| b · `wnb-1-b01` | Matahari Terbit di Sikunir | Bukit Sikunir ada di Desa Sembungan, desa tertinggi di Pulau Jawa (sekitar 2.300 meter). Wisatawan mendaki sebelum subuh untuk melihat matahari terbit di atas awan. | Sikunir Hill is in Sembungan Village, the highest village in Java (about 2,300 metres). Visitors climb before dawn to watch the sun rise above the clouds. | `challenge.item.wnb-1-06` — matahari terbit dari puncak Sikunir dengan lautan awan |
| c · `wnb-1-c01` | Mi Ongklok | Mi ongklok adalah makanan khas Wonosobo. Mi dan sayur direbus di keranjang bambu kecil bernama ongklok, lalu disiram kuah kental dan disajikan bersama sate sapi. | Ongklok noodles are Wonosobo's special dish. The noodles and vegetables are boiled in a small bamboo basket called an ongklok, then covered with a thick sauce and served with beef satay. | `challenge.item.wnb-1-07` — semangkuk mi ongklok dengan sate sapi, tampak atas |

Petunjuk:

1. Cari keping yang punya sisi lurus: keping itu ada di tepi gambar. / *Find the pieces with a straight side: they belong on the edge of the picture.*
2. Tonjolan sebuah keping harus pas masuk ke lekukan keping di sebelahnya. / *Each piece's tab must fit into the gap of the piece next to it.*
3. Cocokkan warna langit, air, atau tanah, lalu susun baris demi baris. / *Match the colours of the sky, water, or ground, then build the picture row by row.*

#### `wnb-2` Lengkapi Bacaan Wonosobo — 7 rumpang + 2 pengecoh, Bloom C3–C4, indikator literasi

| | ID | EN |
|---|---|---|
| Judul | Lengkapi Bacaan Wonosobo | Complete the Wonosobo Reading |
| Instruksi | Baca bacaannya sampai habis. Pikirkan hubungan antarkalimat, lalu pilih kata yang tepat. Ada dua kata yang tidak terpakai. | Read the whole text. Think about how the sentences are connected, then choose the right words. Two words will not be used. |
| Kartu misi | Mbah Kedu menyiapkan catatan tentang Wonosobo, tetapi beberapa katanya hilang. Pikirkan hubungan antarkalimat, lalu lengkapi catatannya. | Mbah Kedu prepared notes about Wonosobo, but some words are missing. Think about how the sentences are connected, then complete the notes. |

**Variasi a — Negeri di Atas Awan / *The Land Above the Clouds*** (dipakai utuh) · pengecoh: **panas** / *hot*, **agar** / *unless*

| Kode | Potongan (ID) | Jawaban | Potongan (EN) | Answer | Bloom | Alasan Bloom |
|---|---|---|---|---|---|---|
| `wnb-2-a01` | Sebagian Dataran Tinggi Dieng berada di Kabupaten Wonosobo. Dataran tinggi ini terletak lebih dari 2.000 meter di atas permukaan laut. Dieng sering tertutup kabut ___ dijuluki negeri di atas awan. | sehingga | Part of the Dieng Plateau lies in Wonosobo Regency. The plateau is more than 2,000 metres above sea level. Dieng is often covered in mist, ___ it is called the land above the clouds. | so | C4 | Menentukan arah hubungan sebab → akibat. |
| `wnb-2-a02` | Pada musim kemarau, embun pagi bisa membeku menjadi es ___ suhunya turun di bawah 0 °C. | karena | In the dry season, the morning dew can freeze into ice ___ the temperature drops below 0 °C. | because | C4 | Hubungan akibat ← sebab. |
| `wnb-2-a03` | Wisatawan yang datang pada musim itu sebaiknya membawa pakaian ___.⏎⏎Dieng juga cocok untuk carica, yaitu pepaya gunung. | hangat | Visitors who come in that season should bring ___ clothes.⏎⏎Dieng is also good for carica, a mountain papaya. | warm | C3 | Informasi suhu dipakai untuk keputusan baru. |
| `wnb-2-a04` | Carica tumbuh baik di ketinggian 1.400–2.400 meter, ___ sulit tumbuh di dataran rendah yang panas. | tetapi | Carica grows well at 1,400–2,400 metres, ___ it struggles to grow in hot lowlands. | but | C4 | Hubungan pertentangan dua keadaan. |
| `wnb-2-a05` | Desa Sembungan berada di ketinggian sekitar 2.300 meter, jadi carica ___ tumbuh di desa itu. | dapat | Sembungan Village is about 2,300 metres high, so carica ___ grow in that village. | can | C3 | Menerapkan rentang ketinggian pada kasus baru. |
| `wnb-2-a06` | Daerah pantai termasuk dataran rendah yang panas, jadi carica ___ tumbuh di sana. | sulit | Beaches are hot lowlands, so carica is ___ to grow there. | hard | C3 | Menerapkan aturan pada kasus baru. |
| `wnb-2-a07` | Dari semua keterangan itu, kita tahu bahwa carica membutuhkan udara yang ___. | sejuk | From all this information, we know that carica needs ___ air. | cool | C4 | Kesimpulan dari beberapa kalimat; pengecoh *panas* menguji ketelitian. |

**Variasi b — Mi Ongklok / *Ongklok Noodles*** · pengecoh: **agar** / *unless*, **panas** / *hot*

| Kode | Potongan (ID) | Jawaban | Potongan (EN) | Answer | Bloom | Alasan Bloom |
|---|---|---|---|---|---|---|
| `wnb-2-b01` | Mi ongklok adalah makanan khas Wonosobo. Mi kuning, kol, dan daun kucai dimasukkan ke dalam keranjang bambu kecil, ___ keranjang itu dicelupkan berulang kali ke air mendidih. | lalu | Ongklok noodles are a special dish from Wonosobo. Yellow noodles, cabbage, and chives are put into a small bamboo basket, ___ the basket is dipped again and again into boiling water. | then | C3 | Menerapkan urutan langkah memasak. |
| `wnb-2-b02` | Keranjang itu bernama ongklok. Jadi, nama mi ongklok berasal dari nama ___ untuk merebusnya. | alat | The basket is called an ongklok. So the name of the dish comes from the name of the ___ used to boil it. | tool | C4 | Menganalisis asal-usul nama. |
| `wnb-2-b03` | Setelah ditiriskan, mi disiram kuah kental. Kuahnya kental ___ dibuat dari tepung kanji.⏎⏎Mi ongklok biasanya dimakan bersama sate sapi dan tempe kemul. | karena | After draining, the noodles are covered with a thick sauce. The sauce is thick ___ it is made from tapioca starch.⏎⏎Ongklok noodles are usually eaten with beef satay and tempe kemul. | because | C4 | Hubungan akibat ← sebab. |
| `wnb-2-b04` | Udara Wonosobo dingin, ___ warga senang menyantap makanan berkuah yang hangat seperti mi ongklok. | sehingga | The air in Wonosobo is cold, ___ people love to eat warm dishes with sauce, like ongklok noodles. | so | C4 | Hubungan sebab → akibat. |
| `wnb-2-b05` | Jika ingin membuat mi ongklok sendiri, kamu perlu menyiapkan mi kuning, kol, dan daun ___. | kucai | If you want to make ongklok noodles yourself, you need yellow noodles, cabbage, and ___. | chives | C3 | Menerapkan daftar bahan pada situasi baru. |
| `wnb-2-b06` | Mi ongklok enak disantap selagi hangat, ___ kuahnya bisa sangat panas. Tiuplah dulu sebelum menyuapkannya. | tetapi | Ongklok noodles taste best while warm, ___ the sauce can be very hot. Blow on it before you take a bite. | but | C4 | Hubungan pertentangan. |
| `wnb-2-b07` | Dari semua keterangan itu, mi ongklok paling cocok disantap saat udara ___. | dingin | From all this information, ongklok noodles are best eaten when the air is ___. | cold | C4 | Kesimpulan dari beberapa kalimat; pengecoh *panas*. |

**Variasi c — Embun Upas / *Embun Upas (Mountain Frost)*** · pengecoh: **mencair** / *melts*, **siang** / *afternoon*

| Kode | Potongan (ID) | Jawaban | Potongan (EN) | Answer | Bloom | Alasan Bloom |
|---|---|---|---|---|---|---|
| `wnb-2-c01` | Pada musim kemarau, langit Dataran Tinggi Dieng sering sangat cerah. Pada malam hari, panas bumi cepat lepas ke angkasa ___ tidak ada awan yang menahannya. | karena | In the dry season, the sky over the Dieng Plateau is often very clear. At night, the earth's heat escapes quickly into space ___ there are no clouds to hold it in. | because | C4 | Hubungan akibat ← sebab. |
| `wnb-2-c02` | Udara dingin lalu turun dan terkumpul di cekungan dataran tinggi, ___ suhu di dekat tanah bisa turun di bawah 0 °C. | sehingga | Cold air then sinks and collects in the hollows of the plateau, ___ the temperature near the ground can drop below 0 °C. | so | C4 | Hubungan sebab → akibat. |
| `wnb-2-c03` | Saat suhu serendah itu, embun pagi ___ menjadi es tipis.⏎⏎Warga Dieng menyebut es tipis itu embun upas. | membeku | At such a low temperature, the morning dew ___ into thin ice.⏎⏎The people of Dieng call this thin ice embun upas. | freezes | C3 | Menerapkan pengetahuan suhu di bawah 0 °C; pengecoh *mencair*. |
| `wnb-2-c04` | Bagi wisatawan, embun upas tampak indah, ___ bagi petani es itu bisa merusak daun kentang. | tetapi | To visitors, embun upas looks beautiful, ___ for farmers the ice can damage potato leaves. | but | C4 | Hubungan pertentangan dua sudut pandang. |
| `wnb-2-c05` | Kata upas dalam bahasa Jawa berarti ___, sebab es itu seolah meracuni tanaman petani. | racun | In Javanese, the word upas means ___, because the ice seems to poison the farmers' plants. | poison | C3 | Menerapkan petunjuk konteks untuk arti kata. |
| `wnb-2-c06` | Wisatawan yang ingin melihat embun upas harus datang pada ___ hari, karena es itu cepat mencair saat matahari mulai terik. | pagi | Visitors who want to see embun upas must come in the ___, because the ice melts quickly when the sun gets strong. | morning | C3 | Menerapkan informasi pada rencana kunjungan; pengecoh *siang*. |
| `wnb-2-c07` | Mereka juga perlu membawa jaket tebal karena udaranya sangat ___. | dingin | They also need a thick jacket because the air is very ___. | cold | C3 | Menerapkan informasi suhu. |

Petunjuk `wnb-2` (dipakai utuh dari bank lama):

1. Baca kalimat sebelum dan sesudah kotak kosong. Apakah isinya sebab, akibat, atau pertentangan? / *Read the sentence before and after the empty box. Does it show a cause, a result, or a contrast?*
2. "Sehingga" menunjukkan akibat, "karena" menunjukkan sebab, dan "tetapi" menunjukkan pertentangan. / *"So" shows a result, "because" shows a cause, and "but" shows a contrast.*
3. Ada dua kata pengecoh. Setelah semua kotak terisi, baca lagi: apakah setiap kalimat masuk akal? / *There are two decoy words. When every box is filled, read it again: does each sentence make sense?*

#### `wnb-3` Benar atau Salah? Wonosobo — 3 paragraf, 6 pernyataan, Bloom C4–C5, indikator literasi

| | ID | EN |
|---|---|---|
| Judul | Benar atau Salah? Wonosobo | True or False? Wonosobo |
| Instruksi | Baca ketiga paragraf. Bandingkan informasinya, lalu pilih BENAR atau SALAH berdasarkan bukti di bacaan. | Read all three paragraphs. Compare the information, then choose TRUE or FALSE based on the evidence in the text. |
| Kartu misi | Kabar tentang Dieng beredar cepat, tetapi tidak semuanya benar. Bandingkan informasinya, nilai setiap pilihan, lalu putuskan berdasarkan bukti. | News about Dieng spreads fast, but not all of it is true. Compare the information, judge each choice, then decide based on the evidence. |

**Bacaan a — `wnb-3-a` Dua Kabar tentang Telaga Warna / *Two Pieces of News about Telaga Warna***

> **ID:** Raka membaca dua kabar tentang Telaga Warna di Dieng. Kabar pertama berasal dari papan informasi pengelola kawasan wisata. Isinya: air Telaga Warna mengandung belerang, sehingga warnanya bisa tampak hijau, kebiruan, atau putih. Air telaga tidak untuk diminum.
>
> Kabar kedua adalah pesan berantai di grup percakapan. Pesan itu tidak mencantumkan nama pembuat maupun tanggal. Isinya: "Minum air Telaga Warna bikin awet muda! Sebarkan ke semua grup!"
>
> Raka ingin tahu kabar mana yang dapat dipercaya. Ia memeriksa siapa pembuat setiap kabar, lalu bertanya kepada gurunya. Gurunya menjelaskan bahwa air yang mengandung belerang tidak aman untuk diminum.
>
> **EN:** Raka read two pieces of news about Telaga Warna in Dieng. The first came from an information board put up by the managers of the tourist area. It said: the water of Telaga Warna contains sulphur, so its colour can look green, bluish, or white. The lake water is not for drinking.
>
> The second was a chain message in a chat group. It did not show the name of whoever made it or a date. It said: "Drinking Telaga Warna water keeps you young! Share this with every group!"
>
> Raka wanted to know which news he could trust. He checked who had made each piece of news, then asked his teacher. His teacher explained that water containing sulphur is not safe to drink.

| Kode | Pernyataan (ID) | Statement (EN) | B/S | Bloom | Keterampilan |
|---|---|---|---|---|---|
| `wnb-3-a01` | Kabar pertama lebih dapat dipercaya daripada kabar kedua karena pembuatnya jelas. | The first piece of news is more trustworthy than the second because its maker is clear. | Benar | C5 | menilai_pilihan |
| `wnb-3-a02` | Kedua kabar sama-sama menyebutkan nama pembuat dan tanggalnya. | Both pieces of news show the name of their maker and a date. | Salah | C4 | membandingkan_informasi |
| `wnb-3-a03` | Kedua kabar sama-sama menyarankan pengunjung meminum air telaga. | Both pieces of news suggest that visitors drink the lake water. | Salah | C4 | membandingkan_informasi |
| `wnb-3-a04` | Langkah Raka memeriksa pembuat kabar lalu bertanya kepada guru adalah pilihan yang tepat. | Raka's steps of checking who made the news and then asking his teacher were the right choice. | Benar | C5 | menilai_pilihan |
| `wnb-3-a05` | Raka sebaiknya langsung menyebarkan pesan berantai itu agar teman-temannya ikut awet muda. | Raka should share the chain message straight away so that his friends stay young too. | Salah | C5 | menilai_pilihan |
| `wnb-3-a06` | Berdasarkan bukti dalam bacaan, pengunjung yang haus di Telaga Warna sebaiknya membawa air minum sendiri. | Based on the evidence in the text, visitors who get thirsty at Telaga Warna should bring their own drinking water. | Benar | C5 | menentukan_solusi |

**Bacaan b — `wnb-3-b` Berangkat ke Bukit Sikunir / *Off to Sikunir Hill***

> **ID:** Kelas Sinta akan berwisata ke Bukit Sikunir di Desa Sembungan, Wonosobo. Desa Sembungan berada di ketinggian sekitar 2.300 meter dan disebut desa tertinggi di Pulau Jawa. Wisatawan biasanya mendaki Bukit Sikunir sebelum subuh untuk melihat matahari terbit.
>
> Pengelola desa wisata memberi pesan: suhu subuh di puncak sangat dingin, jadi bawalah jaket, sarung tangan, dan senter. Setiap tahun sekitar 250.000 wisatawan datang ke Sembungan. Agar alamnya tetap indah, wisatawan diminta membawa pulang sampahnya dan tetap berjalan di jalur yang disediakan.
>
> Teman Sinta, Bayu, berkata, "Tidak usah bawa jaket. Di puncak pasti hangat seperti di kota." Sinta lalu membandingkan ucapan Bayu dengan pesan pengelola. Ia memutuskan tetap membawa jaket.
>
> **EN:** Sinta's class is going on a trip to Sikunir Hill in Sembungan Village, Wonosobo. Sembungan Village is about 2,300 metres high and is called the highest village in Java. Visitors usually climb Sikunir Hill before dawn to watch the sun rise.
>
> The managers of the tourist village give this advice: dawn at the top is very cold, so bring a jacket, gloves, and a torch. About 250,000 visitors come to Sembungan every year. To keep nature beautiful, visitors are asked to take their rubbish home and to stay on the paths provided.
>
> Sinta's friend Bayu said, "No need for a jacket. It must be warm at the top, just like in town." Sinta compared what Bayu said with the managers' advice. She decided to bring her jacket anyway.

| Kode | Pernyataan (ID) | Statement (EN) | B/S | Bloom | Keterampilan |
|---|---|---|---|---|---|
| `wnb-3-b01` | Ucapan Bayu dan pesan pengelola memberikan keterangan yang sama tentang suhu di puncak. | Bayu's words and the managers' advice say the same thing about the temperature at the top. | Salah | C4 | membandingkan_informasi |
| `wnb-3-b02` | Keputusan Sinta membawa jaket didukung oleh pesan pengelola desa wisata. | Sinta's decision to bring a jacket is supported by the tourist village managers' advice. | Benar | C5 | menilai_pilihan |
| `wnb-3-b03` | Ucapan Bayu adalah bukti yang kuat bahwa puncak Sikunir hangat. | Bayu's words are strong evidence that the top of Sikunir is warm. | Salah | C5 | menilai_pilihan |
| `wnb-3-b04` | Senter perlu dibawa karena pendakian dilakukan sebelum matahari terbit. | A torch is needed because the climb happens before sunrise. | Benar | C4 | membandingkan_informasi |
| `wnb-3-b05` | Membuat jalan pintas lewat ladang warga agar cepat sampai di puncak adalah pilihan yang baik. | Making a shortcut through the villagers' fields to reach the top faster is a good choice. | Salah | C5 | menilai_pilihan |
| `wnb-3-b06` | Karena wisatawannya sangat banyak, solusi yang tepat agar Sikunir tetap bersih adalah setiap wisatawan membawa pulang sampahnya sendiri. | Because there are so many visitors, the right solution to keep Sikunir clean is for every visitor to take their own rubbish home. | Benar | C5 | menentukan_solusi |

**Bacaan c — `wnb-3-c` Kabar tentang Kawah / *News about the Crater***

> **ID:** Dataran Tinggi Dieng terbentuk oleh kegiatan gunung api. Karena itu, di sana ada beberapa kawah. Di sekitar kawah dapat keluar gas yang berbahaya bagi kesehatan. Petugas memasang rambu dan pagar pembatas agar pengunjung tetap berada pada jarak aman.
>
> Suatu hari, keluarga Dimas hendak berwisata ke sebuah kawah di Dieng. Pagi itu, beredar pesan berantai tanpa nama pembuat: "Kawah aman kok, dekati saja biar fotonya bagus!" Pada saat yang sama, akun resmi badan gunung api (PVMBG) mengumumkan bahwa pengunjung harus mengikuti rambu dan menjaga jarak aman dari kawah.
>
> Dimas membandingkan kedua kabar itu. Ia melihat bahwa pengumuman resmi menyebutkan lembaga pembuatnya, sedangkan pesan berantai tidak. Keluarga Dimas akhirnya tetap berwisata, tetapi mereka berdiri di belakang pagar pembatas saat berfoto.
>
> **EN:** The Dieng Plateau was formed by volcanic activity. That is why it has several craters. Gas that is harmful to health can come out around the craters. Officers put up signs and safety fences so that visitors stay a safe distance away.
>
> One day, Dimas's family planned to visit a crater in Dieng. That morning, a chain message with no maker's name was spreading: "The crater is safe, just go close for a nice photo!" At the same time, the official account of the volcano agency (PVMBG) announced that visitors must follow the signs and keep a safe distance from the crater.
>
> Dimas compared the two pieces of news. He noticed that the official announcement named the agency that made it, but the chain message did not. In the end, Dimas's family still went on the trip, but they stood behind the safety fence when taking photos.

| Kode | Pernyataan (ID) | Statement (EN) | B/S | Bloom | Keterampilan |
|---|---|---|---|---|---|
| `wnb-3-c01` | Pesan berantai dan pengumuman resmi memberi saran yang berbeda tentang mendekati kawah. | The chain message and the official announcement give different advice about going near the crater. | Benar | C4 | membandingkan_informasi |
| `wnb-3-c02` | Pesan berantai lebih dapat dipercaya karena isinya membuat foto lebih bagus. | The chain message is more trustworthy because it helps you take nicer photos. | Salah | C5 | menilai_pilihan |
| `wnb-3-c03` | Pengumuman resmi dapat dipercaya karena lembaga pembuatnya jelas. | The official announcement can be trusted because the agency that made it is clear. | Benar | C5 | menilai_pilihan |
| `wnb-3-c04` | Rambu dan pagar pembatas dipasang karena gas di sekitar kawah dapat berbahaya. | The signs and safety fences are put up because the gas around the crater can be harmful. | Benar | C4 | membandingkan_informasi |
| `wnb-3-c05` | Berfoto di belakang pagar pembatas adalah pilihan yang tidak aman. | Taking photos behind the safety fence is an unsafe choice. | Salah | C5 | menilai_pilihan |
| `wnb-3-c06` | Berdasarkan bukti dalam bacaan, keluarga Dimas sebaiknya membatalkan wisata karena semua kawah pasti berbahaya. | Based on the evidence in the text, Dimas's family should cancel the trip because every crater is surely dangerous. | Salah | C5 | menentukan_solusi |

Petunjuk `wnb-3`:

1. Bandingkan: siapa yang membuat setiap kabar? Apakah nama dan tanggalnya jelas? / *Compare: who made each piece of news? Are the name and date clear?*
2. Pilihan yang baik didukung bukti di bacaan, bukan hanya karena terdengar menarik. / *A good choice is supported by evidence in the reading, not just because it sounds exciting.*
3. Waspadai kata seperti "pasti", "sebarkan!", atau "semua orang juga begitu". / *Watch out for words like "surely", "share this!", or "everyone does it".*

#### `wnb-4` Cari Objek Wonosobo — 4 dicari + 8 pengecoh, Bloom C3–C4, indikator budaya

| | ID | EN |
|---|---|---|
| Judul | Cari Objek Wonosobo | Find the Objects: Wonosobo |
| Instruksi | Petunjuk Mbah Kedu berupa teka-teki. Pikirkan ciri-ciri bendanya, lalu ketuk yang tepat. | Mbah Kedu's clues are riddles. Think about each object's features, then tap the right one. |
| Kartu misi | Kabut Dieng menyembunyikan benda-benda khas Wonosobo di antara benda yang mirip. Pecahkan teka-teki Mbah Kedu dan temukan keempatnya. | The Dieng mist hides Wonosobo's special objects among look-alikes. Solve Mbah Kedu's riddles and find all four. |
| Judul variasi a | Pasar Oleh-oleh Dieng | Dieng Souvenir Market |
| Adegan | `challenge.wnb-4.scene` (**baru**) — ilustrasi 16:9 pasar oleh-oleh di Dieng berkabut tipis: tiga deret rak bambu/meja kayu, kebun teh dan candi kecil di latar. Benda **tidak** digambar di adegan. | |

Benda dicari:

| Kode | Petunjuk (ID) | Clue (EN) | Bloom | Posisi x · y · w | Media |
|---|---|---|---|---|---|
| `wnb-4-a01` | Temukan alat musik berbentuk tudung lengkung dari anyaman bambu. Dulu tudung itu melindungi penggembala bebek dari panas dan hujan, lalu diberi senar untuk dipetik. | Find the musical instrument shaped like a curved hood of woven bamboo. The hood once protected duck herders from the sun and rain, and then strings were added to pluck. | C4 | 9 · 8 · 10 | `challenge.item.wnb-4-a01` · *Bundengan / Bundengan* |
| `wnb-4-a02` | Temukan buah pegunungan yang mirip pepaya, tetapi ukurannya kecil. Buah ini tumbuh baik di udara sejuk dan sering dibuat manisan. | Find the mountain fruit that looks like a papaya but is small. It grows well in cool air and is often made into sweets. | C4 | 81 · 39 · 10 | `challenge.item.wnb-4-a02` · *Buah carica / Carica fruit* |
| `wnb-4-a03` | Temukan keranjang bambu kecil yang dicelup-celupkan ke air mendidih. Nama alat ini dipakai untuk menamai mi khas Wonosobo. | Find the small bamboo basket that is dipped again and again into boiling water. Its name was used to name Wonosobo's special noodles. | C4 | 33 · 39 · 10 | `challenge.item.wnb-4-a03` · *Keranjang ongklok / Ongklok basket* |
| `wnb-4-a04` | Temukan daun yang diseduh menjadi minuman hangat. Daun ini dipetik di perkebunan Tambi yang sudah ada sejak tahun 1865. | Find the leaves that are brewed into a warm drink. They are picked at the Tambi estate, which has existed since 1865. | C3 | 57 · 70 · 10 | `challenge.item.wnb-4-a04` · *Daun teh / Tea leaves* |

Pengecoh:

| Kode | Benda | Umpan balik (ID) | Feedback (EN) | Posisi | Media |
|---|---|---|---|---|---|
| `wnb-4-a05` | Pepaya biasa | Ini pepaya biasa yang tumbuh di dataran rendah yang panas. Carica lebih kecil dan tumbuh di pegunungan yang sejuk. | This is an ordinary papaya that grows in hot lowlands. Carica is smaller and grows in cool mountains. | 57 · 8 · 10 | `challenge.item.wnb-4-a05` |
| `wnb-4-a06` | Rigen | Ini rigen dari Temanggung, anyaman bambu lebar untuk menjemur. Rigen tidak dipakai untuk merebus mi. | This is a rigen from Temanggung, a wide woven bamboo tray for drying. It is not used to boil noodles. | 9 · 70 · 10 | `challenge.item.wnb-4-a06` |
| `wnb-4-a07` | Semangkuk mi ongklok | Ini semangkuk mi ongklok. Petunjuk meminta alat yang namanya dipakai untuk menamai mi itu, bukan minya. | This is a bowl of ongklok noodles. The clue asks for the tool that gave the noodles their name, not the noodles themselves. | 33 · 70 · 10 | `challenge.item.wnb-4-a07` |
| `wnb-4-a08` | Angklung | Ini angklung dari Jawa Barat. Angklung digoyang, bukan dipetik, dan tidak berasal dari tudung penggembala. | This is an angklung from West Java. It is shaken, not plucked, and it did not come from a herder's hood. | 33 · 8 · 10 | `challenge.item.wnb-4-a08` |
| `wnb-4-a09` | Sasando | Ini sasando dari Pulau Rote, Nusa Tenggara Timur. Sasando juga dipetik, tetapi tidak berasal dari tudung penggembala bebek. | This is a sasando from Rote Island, East Nusa Tenggara. It is plucked too, but it did not come from a duck herder's hood. | 9 · 39 · 10 | `challenge.item.wnb-4-a09` |
| `wnb-4-a10` | Biji kopi | Ini biji kopi dari lereng Sindoro–Sumbing di Temanggung. Petunjuk meminta daun, bukan biji. | These are coffee beans from the Sindoro–Sumbing slopes in Temanggung. The clue asks for leaves, not beans. | 81 · 70 · 10 | `challenge.item.wnb-4-a10` |
| `wnb-4-a11` | Kuluk Topeng Ireng | Ini kuluk penari Topeng Ireng dari Magelang — bukan benda yang dimaksud petunjuk. | This is the headdress of Topeng Ireng dancers from Magelang — not the object in the clue. | 57 · 39 · 10 | `challenge.item.wnb-4-a11` |
| `wnb-4-a12` | Getuk | Ini getuk dari Magelang — bukan benda yang dimaksud petunjuk. | This is getuk from Magelang — not the object in the clue. | 81 · 8 · 10 | `challenge.item.wnb-4-a12` |

Petunjuk `wnb-4`:

1. Petunjuknya berupa teka-teki. Pikirkan bentuk, bahan, dan kegunaan benda. / *The clues are riddles. Think about each object's shape, material, and use.*
2. Ada benda yang mirip: sama-sama dari bambu, sama-sama buah, atau sama-sama alat musik. Baca setiap ciri di petunjuk. / *Some objects look alike: both made of bamboo, both fruit, or both instruments. Check every feature in the clue.*
3. Ingat cerita Mbah Kedu di Kenali Wonosobo. / *Remember Mbah Kedu's story in Get to Know Wonosobo.*

### 9.4 Catatan fakta dan rujukan

Semua fakta diambil dari bank lama dan Pustaka yang sudah dicocokkan sumbernya (Sep–Okt 2026). Kolom `reference_source` per tantangan:

| Node | Rujukan |
|---|---|
| tmg-1, tmg-2 a | Pemkab Temanggung (profil daerah); BPS Kab. Temanggung; data ketinggian gunung PVMBG |
| tmg-2 b | Wikipedia: Umbul Jumprit; Mongabay (2018) Berkah Air dari Umbul Jumprit; Perhutani |
| tmg-2 c, tmg-1 c | DPRD Jateng: Kuda Lumping, Kesenian Khas Temanggung; Pemprov Jateng: Nyadran Jaran Kepang |
| tmg-3 a, b, tmg-1 b | Pemkab Temanggung (Sejarah Temanggung); Kompas.com (2023) Situs Liyangan; BPK Wilayah X Jateng |
| tmg-3 c | Dinas Pertanian Kab. Temanggung; MPIG Kopi Java Arabika Sindoro Sumbing |
| tmg-4 | gabungan rujukan Temanggung di atas; DJKI (indikasi geografis ikan uceng) |
| mgl-1 a, mgl-2 a, mgl-3 a | UNESCO World Heritage Centre (No. 592); Kemenag Bimas Buddha; Kompas Travel (2025) |
| mgl-1 b, mgl-2 b | detikEdu (2022) Tari Topeng Ireng; Visit Jawa Tengah; Kompas Regional (2024) |
| mgl-1 c, mgl-3 b | BPK Wilayah X; visitmagelang.id; Liputan6 (Umbul Jumprit & Waisak) |
| mgl-2 c | Kompas Travel (2022) Desa Klipoh; Badan Otorita Borobudur; detikFood (2025) |
| mgl-3 c | Tirto.id (Sejarah Gunung Tidar); Kompas Regional (2024); Pemkot Magelang (Prasasti Mantyasih) |
| mgl-4 | gabungan rujukan Magelang di atas; Pemkab Ponorogo (Reog) untuk pengecoh dadak merak |
| wnb-1, wnb-2 a | Wikipedia: Dataran Tinggi Dieng; Tempo & Kompas Travel (Desa Sembungan); Indonesia Kaya (Mie Ongklok) |
| wnb-2 b | Indonesia Kaya: Mie Ongklok; Pemkab Wonosobo (kuliner) |
| wnb-2 c | BMKG via Kompas.com (Juli 2026) embun upas; detikEdu |
| wnb-3 a, c | Panduan cek fakta Siberkreasi & Mafindo; laman resmi PVMBG/MAGMA Indonesia; pengelola kawasan Dieng |
| wnb-3 b | Tempo (Desa Sembungan); Kompas Travel (2022); Indonesia Kaya |
| wnb-4 | Indonesia Kaya; BRIN Audiovisual (Bundengan); Kompas Travel (2023) Kebun Teh Tambi; Wikipedia (Pepaya gunung) |

Nama tokoh di bacaan Wonosobo (Raka, Sinta, Bayu, Dimas) adalah tokoh rekaan.

---

## 10. Gambar mockup

### 10.1 Tujuan

Semua slot gambar tantangan langsung berisi **gambar contoh** agar sistem lengkap dan dapat diuji ujung ke ujung. Admin kemudian mengganti satu per satu dengan gambar asli lewat panel Media; gambar asli yang sudah ada **tidak pernah ditimpa**.

### 10.2 Komponen

- `App\Libraries\MockupGenerator` — membuat SVG dari data slot (kunci, jenis, ukuran, `label`, `description`, posisi benda untuk adegan).
- `App\Commands\Mockups` — `php spark gelita:mockups [--dry-run] [--paksa] [--node=tmg-4]`. Dipanggil juga oleh `gelita:reset` (Dok 1 §7.3 langkah 8).
- Berkas ditulis ke `public/assets/uploads/mockup/{asset_key}.svg` (folder unggahan sudah dapat ditulis server dan ikut backup), lalu didaftarkan dengan `MediaStore::registerFile()`; baris `media_assets` diberi `is_placeholder = 1`, `is_active = 1`, `credit = "Gambar contoh GELITA (mockup)"`.
- Slot dilewati bila sudah berisi berkas aktif dengan `is_placeholder = 0` (gambar asli). `--paksa` hanya membuat ulang mockup yang sudah ada, tidak pernah menimpa gambar asli.

### 10.3 Rupa mockup per jenis

Semua SVG memakai `viewBox` dan atribut `width`/`height` sesuai ukuran slot, huruf `font-family="Plus Jakarta Sans, Arial, sans-serif"`, dan pita kecil **"GAMBAR CONTOH"** di pojok. Palet per wilayah: Temanggung hijau–teal, Magelang batu–amber, Wonosobo biru kabut–ungu.

| Jenis (pola kunci) | Ukuran | Isi |
|---|---|---|
| Susun Gambar `challenge.item.*-1-*` | 1200 × 1200 | lanskap bergaya datar dengan detail berbeda di tiap bagian agar keping dapat dibedakan: matahari kiri atas, awan kanan atas, dua–tiga bentuk gunung/candi di tengah, pita tanah berwarna di bawah, bingkai dengan empat sudut berwarna berbeda; kartu judul (`label`) di sepertiga bawah; kunci aset kecil di tepi bawah. **Tanpa angka urutan.** |
| Adegan Cari Objek `challenge.*.scene` | 1280 × 720 | latar langit + siluet gunung/candi wilayah; untuk **setiap posisi benda** (dibaca dari soal node itu) digambar alas/meja/rak di bawah kotaknya sehingga benda tampak "diletakkan"; spanduk judul variasi di atas. |
| Benda Cari Objek `challenge.item.*-4-*` | 240 × 240, latar transparan | ubin bulat putih 90 % dengan bayangan lembut, satu ikon geometris sederhana (dipilih dari hash kunci) dan **nama benda** (`label`) maks dua baris. Target dan pengecoh bergaya **identik**. |
| Gambar pernyataan `challenge.item.*-3-*` | 480 × 360 | bingkai foto, ikon kamera, `label`, dan satu baris `description` |

Karena mockup benda menuliskan nama benda, tantangan Cari Objek dan pernyataan bergambar **belum layak untuk pengambilan data** sebelum gambar asli dipasang. Penjaganya:

- `gelita:content:verify --siap-penelitian` gagal bila masih ada mockup;
- dasbor admin menampilkan peringatan "Masih ada N gambar contoh" (Dok 3 §8);
- mengubah status studi menjadi aktif untuk pengambilan data memerlukan centang konfirmasi bila mockup masih ada (Dok 3 §8).

### 10.4 Daftar 45 slot gambar tantangan

Ukuran dan isi gambar asli yang harus disiapkan admin (juga dicetak di sheet `DAFTAR_MEDIA` dan halaman Media).

| Kunci | Wilayah · tantangan | Isi gambar asli | Ukuran |
|---|---|---|---|
| `challenge.item.tmg-1-01` | TMG Susun Gambar a | lanskap Gunung Sindoro & Sumbing, langit cerah, kebun di kaki gunung | persegi ≥ 1200 |
| `challenge.item.tmg-1-03` | TMG Susun Gambar b | Candi Pringapus tampak depan | persegi ≥ 1200 |
| `challenge.item.tmg-1-04` | TMG Susun Gambar c | penari kuda lumping dengan kuda anyaman bambu berwarna | persegi ≥ 1200 |
| `challenge.item.tmg-3-a03` | TMG Benar/Salah a | butir padi hangus kehitaman (temuan arkeologi) | 480 × 360 |
| `challenge.item.tmg-3-b03` | TMG Benar/Salah b | arca Buddha duduk dari batu (sengaja tidak sesuai bacaan) | 480 × 360 |
| `challenge.item.tmg-3-c03` | TMG Benar/Salah c | biji kopi sangrai berwarna cokelat | 480 × 360 |
| `challenge.tmg-4.scene` | TMG Cari Objek | halaman balai desa/pasar budaya di kaki Sindoro–Sumbing, tiga deret rak kosong (tata letak lama) | 1280 × 720 |
| `challenge.item.tmg-4-03` | TMG Cari · target | kuda kepang anyaman bambu berwarna (tanpa penari) | 240 × 240 transparan |
| `challenge.item.tmg-4-02` | TMG Cari · target | segenggam biji kopi | 240 × 240 transparan |
| `challenge.item.tmg-4-06` | TMG Cari · target | arca lembu Nandi duduk dari batu | 240 × 240 transparan |
| `challenge.item.tmg-4-04` | TMG Cari · target | beberapa ikan uceng kecil memanjang | 240 × 240 transparan |
| `challenge.item.tmg-4-09` | TMG Cari · pengecoh | angklung bambu | 240 × 240 transparan |
| `challenge.item.tmg-4-10` | TMG Cari · pengecoh | ondel-ondel kecil | 240 × 240 transparan |
| `challenge.item.tmg-4-11` | TMG Cari · pengecoh | perahu pinisi berlayar | 240 × 240 transparan |
| `challenge.item.tmg-4-12` | TMG Cari · pengecoh | sasando | 240 × 240 transparan |
| `challenge.item.mgl-1-01` | MGL Susun Gambar a | stupa berlubang teras atas Borobudur, stupa induk di latar | persegi ≥ 1200 |
| `challenge.item.mgl-1-02` | MGL Susun Gambar b | penari Topeng Ireng dengan kuluk bulu warna-warni | persegi ≥ 1200 |
| `challenge.item.mgl-1-03` | MGL Susun Gambar c | Candi Mendut tampak depan dengan tangga masuk | persegi ≥ 1200 |
| `challenge.mgl-4.scene` | MGL Cari Objek | galeri/pendapa budaya, dua deret meja pajang kayu, stupa & Gunung Tidar di latar | 1280 × 720 |
| `challenge.item.mgl-4-a01` | MGL Cari · target | stupa kecil berlubang (miniatur) | 240 × 240 transparan |
| `challenge.item.mgl-4-a02` | MGL Cari · target | kuluk bulu warna-warni Topeng Ireng | 240 × 240 transparan |
| `challenge.item.mgl-4-a03` | MGL Cari · target | getuk tiga warna di atas daun pisang | 240 × 240 transparan |
| `challenge.item.mgl-4-a04` | MGL Cari · target | kendil gerabah cokelat kemerahan | 240 × 240 transparan |
| `challenge.item.mgl-4-a05` | MGL Cari · pengecoh | kuda kepang anyaman bambu | 240 × 240 transparan |
| `challenge.item.mgl-4-a06` | MGL Cari · pengecoh | stoples manisan carica | 240 × 240 transparan |
| `challenge.item.mgl-4-a07` | MGL Cari · pengecoh | dadak merak Reog Ponorogo | 240 × 240 transparan |
| `challenge.item.mgl-4-a08` | MGL Cari · pengecoh | angklung bambu | 240 × 240 transparan |
| `challenge.item.mgl-4-a09` | MGL Cari · pengecoh | beberapa bakpia | 240 × 240 transparan |
| `challenge.item.mgl-4-a10` | MGL Cari · pengecoh | panci logam | 240 × 240 transparan |
| `challenge.item.wnb-1-05` | WNB Susun Gambar a | Telaga Warna hijau kebiruan | persegi ≥ 1200 |
| `challenge.item.wnb-1-06` | WNB Susun Gambar b | matahari terbit dari puncak Sikunir, lautan awan | persegi ≥ 1200 |
| `challenge.item.wnb-1-07` | WNB Susun Gambar c | semangkuk mi ongklok dengan sate sapi, tampak atas | persegi ≥ 1200 |
| `challenge.wnb-4.scene` | WNB Cari Objek | pasar oleh-oleh Dieng berkabut tipis, tiga deret rak bambu/meja kayu, kebun teh & candi di latar | 1280 × 720 |
| `challenge.item.wnb-4-a01` | WNB Cari · target | bundengan (tudung bambu lengkung bersenar) | 240 × 240 transparan |
| `challenge.item.wnb-4-a02` | WNB Cari · target | beberapa buah carica kuning kecil | 240 × 240 transparan |
| `challenge.item.wnb-4-a03` | WNB Cari · target | keranjang ongklok bambu kecil bergagang | 240 × 240 transparan |
| `challenge.item.wnb-4-a04` | WNB Cari · target | daun teh segar (pucuk) | 240 × 240 transparan |
| `challenge.item.wnb-4-a05` | WNB Cari · pengecoh | pepaya biasa (besar) | 240 × 240 transparan |
| `challenge.item.wnb-4-a06` | WNB Cari · pengecoh | rigen (anyaman bambu persegi panjang, kosong) | 240 × 240 transparan |
| `challenge.item.wnb-4-a07` | WNB Cari · pengecoh | semangkuk mi ongklok | 240 × 240 transparan |
| `challenge.item.wnb-4-a08` | WNB Cari · pengecoh | angklung bambu | 240 × 240 transparan |
| `challenge.item.wnb-4-a09` | WNB Cari · pengecoh | sasando | 240 × 240 transparan |
| `challenge.item.wnb-4-a10` | WNB Cari · pengecoh | segenggam biji kopi | 240 × 240 transparan |
| `challenge.item.wnb-4-a11` | WNB Cari · pengecoh | kuluk bulu Topeng Ireng | 240 × 240 transparan |
| `challenge.item.wnb-4-a12` | WNB Cari · pengecoh | getuk | 240 × 240 transparan |

Kunci lama yang dipakai ulang (gambar asli mungkin sudah diunggah di server): `tmg-1-01/03/04`, `tmg-4-02/03/04/06/09/10/11`, `tmg-4.scene`, `mgl-1-01/02/03`, `wnb-1-05/06/07`. Kunci lama lain (`tmg-1-02`, `tmg-4-01/05/07/08`, `mgl-1-04`, gambar opsi `mgl-5`, dst.) tidak dipakai lagi; hapus lewat `gelita:reset --bersihkan-media-tantangan` bila diinginkan.

---

## 11. Naskah cerita: jumlah serpihan dan narasi petunjuk

### 11.1 Sebelas baris naskah

Hanya kata bilangan yang berubah. Ubah di `docs/naskah-cerita.md` (termasuk *Benang merah* "15 Serpihan Cahaya" → "12 Serpihan Cahaya" dan "tantangan kelima sebuah wilayah" → "tantangan keempat"), salin persis ke `app/Database/Seeds/data/story.php`, lalu tambahkan **teks lama** baris-baris ini ke `data/story-legacy.php` agar `gelita:story:update` mengenalinya sebagai teks bawaan (bukan suntingan admin).

| Kode | Teks baru (ID) | New text (EN) |
|---|---|---|
| `intro-04` | Pada suatu malam, Kabut Lupa membelit Lentera Kedu. Kacanya retak, apinya bergetar, lalu cahayanya pecah! **Dua belas** Serpihan Cahaya melesat ke langit dan jatuh di Temanggung, Magelang, dan Wonosobo. | One night, the Mist of Forgetting coiled around the Lantern of Kedu. Its glass cracked, its flame trembled, and then its light shattered! **Twelve** Shards of Light shot into the sky and fell across Temanggung, Magelang, and Wonosobo. |
| `intro-06` | Biar aku yang pergi, Mbah! Aku akan mencari **kedua belas** serpihan itu satu per satu, sampai Lentera Kedu menyala lagi. Aku janji! | Let me go, Mbah! I will find all **twelve** shards, one by one, until the Lantern of Kedu shines again. I promise! |
| `intro-07` | Setiap wilayah menyimpan **empat** serpihan, dan setiap serpihan dijaga oleh satu tantangan. Selesaikan tantangannya untuk mendapatkan serpihan dan bintang. Lihat lentera di pojok kiri atas: lentera itu makin terang setiap kali serpihan kembali. | Each region holds **four** shards, and each shard is guarded by a challenge. Complete the challenge to win its shard and stars. Watch the lantern in the top-left corner: it grows brighter every time a shard returns. |
| `peta-02` | Aku harus menjelajahi tiga wilayah untuk memulihkannya: Temanggung, lalu Magelang, dan terakhir Wonosobo. Di setiap wilayah, **empat** Serpihan Cahaya sedang menunggu. | I must explore three regions to bring it back: Temanggung, then Magelang, and finally Wonosobo. In each region, **four** Shards of Light are waiting. |
| `dialog-temanggung-14` | **Empat** serpihan menunggumu di Temanggung. Temukan informasi yang tertulis jelas, maka kabut ini akan mundur dengan sendirinya. | **Four** shards are waiting for you in Temanggung. Find the information that is clearly written, and this mist will retreat on its own. |
| `dialog-magelang-15` | Ingat pesan Mbah: temukan ide pokoknya, susun urutannya, dan pisahkan fakta dari pendapat. **Empat** serpihan Magelang menunggumu. | Remember my advice: find the main idea, put things in order, and separate facts from opinions. **Four** shards of Magelang are waiting for you. |
| `dialog-wonosobo-15` | Itu baru anak pemberani. Pergilah, Le. **Empat** serpihan terakhir menunggu di balik kabut. | Now that's a brave child. Go, my boy. The last **four** shards are waiting behind the mist. |
| `tuntas-temanggung-02` | **Empat** serpihan sudah kembali ke lentera! Rasanya hangat sekali, Mbah. | **Four** shards are back in the lantern! It feels so warm, Mbah. |
| `tuntas-magelang-03` | **Delapan** serpihan sudah kembali, dan tenaga Mbah ikut pulih. Pustaka Magelang kini terbuka untukmu. | **Eight** shards have returned, and my strength is coming back. The Magelang Library is now open to you. |
| `tuntas-wonosobo-03` | **Dua belas** serpihan... semuanya sudah kembali! | **Twelve** shards... every single one is back! |
| `penutup-01` | Di puncak bukit, Jaka mengangkat lenteranya tinggi-tinggi. **Kedua belas** serpihan menyatu, dan cahaya keemasan memancar ke seluruh Dataran Kedu. | On top of the hill, Jaka lifted his lantern high. All **twelve** shards joined together, and a golden light poured out across the whole Kedu Plain. |

Baris **Kenali wilayah** (`kenal-*`) tidak disentuh.

### 11.2 Rekaman

1. **Narasi naskah (11 baris × 2 bahasa = 22 berkas)** — bangkitkan ulang dengan generator TTS yang sama (`tools/audio`, pemeran dan efek per tokoh tetap):

   ```bash
   cd tools/audio && . .venv/bin/activate
   CODES=intro-04,intro-06,intro-07,peta-02,dialog-temanggung-14,dialog-magelang-15,dialog-wonosobo-15,tuntas-temanggung-02,tuntas-magelang-03,tuntas-wonosobo-03,penutup-01
   python narrate.py --locale id --codes "$CODES" --n 3
   python narrate.py --locale en --codes "$CODES" --n 3
   python publish.py
   ```

   Commit MP3 baru dan `narasi/produksi.json`. Perbarui `tools/audio/qa-narasi.json`.
2. **Narasi petunjuk Cari Objek (12 baris × 2 bahasa = 24 berkas)** — `petunjuk-tmg-4-01…04`, `petunjuk-mgl-4-01…04`, `petunjuk-wnb-4-01…04`; teks = petunjuk target §9 sesuai urutan soal; dibacakan Mbah Kedu. `export_lines.php` membaca format data baru. Rekaman lama `petunjuk-tmg-4-05…08` dihapus dari repositori.
3. Di server (Dok 3 §13): `php spark gelita:story:update`, `php spark gelita:narration:import`, lalu **Setujui semua narasi draft** ID dan EN di **Konten → Narasi**.
4. **Bila lingkungan pengerjaan tidak dapat menjalankan TTS** (model ±600 MB dan akses jaringan): tetap ubah teks, lalu jalankan `php spark gelita:story:update --lepas-audio-beda` (opsi baru `StorySync`: melepas tautan audio baris yang transkrip audionya tidak sama lagi dengan teksnya). Baris itu tampil sebagai teks dengan tombol Lanjut manual sampai rekaman baru disetujui. Petunjuk Cari Objek tanpa rekaman tampil sebagai teks saja (perilaku yang sudah ada).

`NarrationCatalog`/`NarrationImporter`: kelompok "Petunjuk arena cari" kini dibuat untuk **setiap** node `cari` (bukan hanya `tmg-4`); jumlah baris di halaman Narasi dan **Kelengkapan aset** menjadi 88 naskah + 12 petunjuk = **100 rekaman per bahasa**.

---

## 12. Teks UI yang lebih ramah

Ganti di `app/Language/id/*.php` dan padanannya di `en/` (kunci harus sama — `LanguageFilesTest`).

### 12.1 `Game.php`

| Kunci | ID lama | ID baru | EN baru |
|---|---|---|---|
| `check` | Periksa jawaban | **Cek jawabanku** | Check my answer |
| `puzzleCheck` | Periksa gambar | **Cek gambarku** | Check my picture |
| `accuracyFirst` | Tepat sejak awal | **Berhasil pada Percobaan Pertama** | Success on First Try |
| `checks` | Pemeriksaan | **Percobaan** | Tries |
| `attempt` | Percobaan | **Pengerjaan** | Play |
| `multiCheckNote` | Kamu memeriksa jawaban {0} kali. Tidak apa-apa — setiap pemeriksaan membantumu belajar. | Kamu mencoba {0} kali. Terus berusaha itu hebat! | You tried {0} times. Keeping at it is great! |
| `exitConfirmText` | Jawabanmu yang sudah diperiksa tetap tersimpan, … | Jawaban yang sudah kamu cek tetap tersimpan, tetapi tantangan ini belum selesai. | The answers you have checked are saved, but this challenge is not finished yet. |
| `schoolNpsnUnknown` | … Periksa lagi angkanya, … | NPSN {value} belum ada di daftar kami. Coba cek lagi angkanya, atau centang "Sekolahku tidak ada di daftar". | (padanan EN, "check the number again") |
| `schoolWarnStage` | … Periksa lagi NPSN-mu. | … Coba cek lagi NPSN-mu. | (padanan EN) |
| `cardN` | Kartu {0} | Pernyataan {0} | Statement {0} |
| `retryNote` | Mengulang tidak menghapus catatan lama, setiap percobaan tetap tersimpan … | Bermain lagi tidak menghapus catatan lama; setiap pengerjaan tetap tersimpan sebagai data penelitian. | Playing again does not erase your old record; every play is kept as research data. |
| baru `triesLabel` | — | Kesempatan | Chances |
| baru `triesOf` | — | {0} dari {1} | {0} of {1} |
| baru `firstTryCount` | — | {0} dari {1} soal | {0} of {1} questions |
| baru `firstTryYes` / `firstTryNo` | — | Ya / Belum | Yes / Not yet |
| baru `triesUsed` | — | Percobaan dipakai | Tries used |
| baru `wrongTaps` | — | Ketukan salah | Wrong taps |
| baru `completionSolved` | — | Semua benar | All correct |
| baru `completionExhausted` | — | Disimpan apa adanya | Saved as is |
| baru `readingLabel` | — | Bacaan | Reading |
| baru `statementsLabel` | — | Pernyataan | Statements |
| baru `assentLabel` | — | Aku mau ikut bermain GELITA. Aku tahu jawaban dan langkahku di dalam permainan dicatat untuk penelitian. | I want to play GELITA. I know my answers and steps in the game are recorded for research. |
| dihapus | `score`, `totalScore`, `meanFirstPass`, `engine_pilihan`, `variant_urutkan_informasi`, `verdictPendapat`, `factVsOpinion`, `factWord`, `opinionWord`, `reasonLabel`, `reasonPlaceholder`, `typeAnswer`, `orderHint`, `moveUp`, `moveDown`, `questionOf`, `sourceText`, `sourceOfficial`, `sourceAnonymous`, `phase*`, `consent*`, `gender_lainnya` | | |

Balai Refleksi: `perRegion` → "Bintang per wilayah" (*Stars per region*), `perEngine` → "Berhasil pada Percobaan Pertama per jenis tantangan" (*Success on First Try by challenge type*), `mostMissedText` → "{0} — berhasil pada percobaan pertama {1} dari {2} soal." (*"{0} — success on first try {1} of {2} questions."*).

### 12.2 `Js.php`

| Kunci | ID baru | EN baru |
|---|---|---|
| `errNetwork` | Koneksi ke server terputus. Coba cek internet sekolah, lalu ulangi. | The connection to the server was lost. Check the school internet, then try again. |
| `exitText` | Jawaban yang sudah kamu cek tetap tersimpan, tetapi tantangan ini belum selesai. | The answers you have checked are saved, but this challenge is not finished yet. |
| `hintPenalty` | Petunjuk bisa membantumu. Buka petunjuk sekarang? | A hint can help you. Open a hint now? |
| `blankWrongText` | {0} dari {1} sudah benar. Ketuk kotak merah untuk menggantinya. | {0} of {1} are correct. Tap a red box to change it. |
| `cardsWrongText` | {0} dari {1} pernyataan sudah tepat. Pikirkan lagi yang bergaris merah. | {0} of {1} statements are right. Think again about the ones with a red border. |
| `huntDecoyTitle` | Bukan yang ini | Not this one |
| `huntDecoyText` | Benda itu bukan yang dimaksud petunjuk. Baca lagi petunjuknya, ya. | That is not the object in the clue. Read the clue again. |
| baru `triesLeft` | Kesempatanmu tinggal {0} lagi. | You have {0} more chances. |
| baru `triesLast` | Tinggal satu kesempatan lagi. Baca pelan-pelan, ya! | Just one chance left. Read slowly! |
| baru `triesExhaustedTitle` | Kesempatanmu sudah habis | No more chances |
| baru `triesExhaustedText` | Tidak apa-apa! Jawabanmu disimpan apa adanya. Ayo lanjut. | That's okay! Your answers are saved as they are. Let's keep going. |
| baru `triesExhaustedPuzzle` | Tidak apa-apa! Ini gambar utuhnya. Jawabanmu disimpan apa adanya. | That's okay! Here is the whole picture. Your answer is saved as it is. |
| baru `clueFailedTitle` | Petunjuk ini dilewati | This clue is skipped |
| baru `clueFailedText` | Kesempatan untuk petunjuk ini sudah habis. Kita lanjut ke petunjuk berikutnya. | The chances for this clue are used up. Let's go to the next clue. |
| baru `clueTriesLeft` | Kesempatan untuk petunjuk ini: {0} | Chances for this clue: {0} |
| dihapus | `noRetry`, `finishAnyway`, `reasonSaved`, `cardMoved`, `orderOkTitle`, `orderOkText`, `orderWrongTitle`, `orderWrongText`, `orderWrongPlain` | |

### 12.3 `Admin.php`

Label event: `challenge_checked` → "Mengecek jawaban"; tambah `screen_viewed` "Membuka layar", `overlay_opened` "Membuka jendela", `answer_selected` "Memilih jawaban", `challenge_tries_exhausted` "Kesempatan habis", `clue_failed` "Petunjuk dilewati", `reading_progress` "Membaca bacaan", `page_hidden` "Meninggalkan layar", `page_visible` "Kembali ke layar", `viewport_changed` "Ukuran layar berubah". Label Bloom dan keterampilan dari `DifficultyRules`.

---

## 13. Pengujian dan kriteria selesai Dokumen 2

### 13.1 Otomatis

- `BankKelas5DataTest` — seluruh pemeriksaan §7.5 pada berkas data; isi §9 tersalin lengkap (126 soal, 30 variasi, 9 bacaan, 36 petunjuk, 12 kata pengecoh, 45 slot media).
- `VariantSelectionTest` — variasi acak; pengerjaan ulang memilih variasi yang belum pernah; resume tidak mengganti variasi; nonaktif semua → pengerjaan baru.
- `WordBankShuffleTest` — 1 000 benih: Temanggung selalu derangement; sedang/sulit urutan jawaban ≠ urutan rumpang; stabil per pengerjaan.
- `TryLimitTest` — percobaan ke-4 ditolak `409`; percobaan ke-3 salah menutup pengerjaan `tries_exhausted`; soal benar terkunci dan tidak menambah `try_count`; `item_tries` lengkap; Cari Objek: ketukan salah ke-3 → `clue_failed`, semua petunjuk tertutup → selesai.
- `HuntPayloadTest` (diperluas) — tidak ada penanda pengecoh/kunci/umpan balik di payload ketiga wilayah; urutan petunjuk teracak tetapi stabil.
- `MockupGeneratorTest` — SVG valid (DOMDocument), ukuran sesuai slot, gambar asli tidak pernah ditimpa, `is_placeholder` diset.
- `StoryDataTest` — `story.php` sama dengan `naskah-cerita.md` (11 baris baru).
- `LanguageFilesTest` — kunci ID = EN.

### 13.2 Manual

1. `php docs/bank-soal/build-workbook.php` → 0 galat, 0 peringatan Bloom.
2. `php spark gelita:reset` (dev) → `gelita:mockups` → mainkan ke-12 tantangan sampai penutup, termasuk: salah 3 kali di setiap jenis, benar pada percobaan pertama, muat ulang di tengah tantangan, mengulang tantangan (variasi berbeda), bahasa EN.
3. Ganti satu mockup dengan gambar asli di panel Media → gambar asli tampil; `gelita:mockups --paksa` tidak menimpanya.
4. Halaman Narasi menampilkan 100 baris per bahasa; rekaman baru dapat diimpor dan disetujui.

### 13.3 Kriteria selesai

- [ ] 12 node, 126 soal, 30 variasi, isi persis §9 (ID & EN).
- [ ] Pemilihan variasi acak dan pengacakan bank kata sesuai §3–§4.
- [ ] Aturan 3 kesempatan ditegakkan server di keempat tantangan; data `item_tries` lengkap.
- [ ] Engine dan jenis soal lama terhapus tanpa sisa rute/view/JS.
- [ ] 45 mockup terbentuk; penjaga "siap penelitian" bekerja.
- [ ] 11 baris naskah dan 24 + 22 rekaman diperbarui (atau dilepas dengan `--lepas-audio-beda` bila TTS tidak tersedia, dicatat di catatan rilis).
- [ ] `docs/bank-soal/README.md` ditulis ulang; workbook kelas 5 di-commit; workbook lama dihapus; Pustaka dipindah ke `docs/pustaka/` tanpa perubahan isi.
