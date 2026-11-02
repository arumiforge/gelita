# Dokumen Implementasi Bagian 5 — Bank Soal Baru, Kode Kesalahan, Label, Jangkar, dan Area Ketuk

Per 2 November 2026 · Bagian dari [revisi 10 analitik](01_REVISI_10_ANALITIK.md) · perubahan sistem no. 1, 3, 4, 8, 17, dan 20 di [01 → Catatan perubahan sistem](01_REVISI_10_ANALITIK.md#catatan-perubahan-sistem-untuk-tahap-nanti)

> **Status: `draf pengembang`, `asal: pengembang`** (disusun dengan bantuan AI, 1–2 November 2026). Ini dokumen, bukan perubahan sistem. Sejak 1 November 2026 kodenya boleh dibangun sekarang ([04](04_CATATAN_KEPUTUSAN.md), entri 1 November 2026), di gelombang D bersama bagian 2 ([28](28_IMPLEMENTASI_SESI_LAPANGAN.md)). Sistem baru tidak dipakai dengan anak sebelum persetujuan etik terbit.
>
> **Isi bank masih `needs_verification`.** Butir di [08](08_DRAF_BANK_SOAL.md) akan berubah setelah validasi ahli. Karena itu dokumen ini merancang **mekanisme**: format workbook, impor, aturan bank, pemilihan variasi, dan penurunan kode. Revisi isi dari validasi cukup diubah di berkas data, dibangun ulang, lalu diimpor, tanpa mengubah kode ([K2](#2-keputusan-yang-dikunci)).
>
> **Bagian 5 dari 9.** Bagian ini memakai `participant_seats.booklet` dan sakelar `field_mode` dari bagian 2. Bagian 6 (`32`, Skenario Digital) memakai format data skenario dari sini. Bagian 9 (`35`, lapis data turunan) membaca kode, label, dan geometri adegan dari sini.

---

## Daftar isi

0. [Cara memakai dokumen ini](#0-cara-memakai-dokumen-ini)
1. [Kebutuhan, sumber, dan tempatnya](#1-kebutuhan-sumber-dan-tempatnya)
2. [Keputusan yang dikunci](#2-keputusan-yang-dikunci)
3. [Yang tidak berubah](#3-yang-tidak-berubah)
4. [Bank soal sekarang di kode](#4-bank-soal-sekarang-di-kode)
5. [Bentuk bank baru per tantangan](#5-bentuk-bank-baru-per-tantangan)
6. [Workbook: kolom dan sheet baru](#6-workbook-kolom-dan-sheet-baru)
7. [Impor dan berkas data](#7-impor-dan-berkas-data)
8. [Aturan bank: `DifficultyRules` dan `BankRules`](#8-aturan-bank-difficultyrules-dan-bankrules)
9. [Area ketuk tidak bersentuhan (perubahan no. 17)](#9-area-ketuk-tidak-bersentuhan-perubahan-no-17)
10. [Kode kesalahan di setiap percobaan salah (perubahan no. 1)](#10-kode-kesalahan-di-setiap-percobaan-salah-perubahan-no-1)
11. [Variasi dari buklet dan butir jangkar (perubahan no. 4, 20)](#11-variasi-dari-buklet-dan-butir-jangkar-perubahan-no-4-20)
12. [Engine yang berubah](#12-engine-yang-berubah)
13. [Label butir dan variabel beban permukaan (perubahan no. 3, 8)](#13-label-butir-dan-variabel-beban-permukaan-perubahan-no-3-8)
14. [Susun Gambar selama studi](#14-susun-gambar-selama-studi)
15. [Skema database](#15-skema-database)
16. [Rute](#16-rute)
17. [Teks antarmuka](#17-teks-antarmuka)
18. [Titik sambung ke perubahan lain](#18-titik-sambung-ke-perubahan-lain)
19. [Pengujian dan kriteria selesai](#19-pengujian-dan-kriteria-selesai)
20. [Dokumen yang ikut diperbarui saat implementasi](#20-dokumen-yang-ikut-diperbarui-saat-implementasi)
21. [Pertanyaan terbuka](#21-pertanyaan-terbuka)
22. [Keputusan pengembang](#22-keputusan-pengembang)
23. [Akibat ke dokumen lain](#23-akibat-ke-dokumen-lain)

---

## 0. Cara memakai dokumen ini

**Urutan.** Kode bagian ini dikerjakan di gelombang D, paralel dengan bagian 2 ([00 → Tahap dokumen 3](00_INDEKS.md)), setelah bagian 1 ([27](27_IMPLEMENTASI_PERAN_PENELITI.md)). Bagian ini hanya mengubah:
- berkas data dan pembangun workbook (`docs/bank-soal/`);
- impor (`ContentImportService`, `gelita:bank:import`);
- aturan bank (`DifficultyRules`, `BankRules`, `BankSource`, `ContentVerifier`);
- `ContentRepository` dan model butir;
- pemilihan variasi dan penilaian per engine (`ChallengeItemModel::pickVariant()`, metode `grade*` dan `recordTry()` di `ChallengeService`);
- engine klien `cari.js` dan `boleh.js`;
- lembar `butir` di unduhan.

Perubahan kemajuan di `ChallengeService` (tabel `participant_progress`, `refreshProgress()`) milik bagian 2 ([28 §8](28_IMPLEMENTASI_SESI_LAPANGAN.md#8-kemajuan-mengikuti-siswa-bukan-sesi-game)) dan tidak disentuh di sini. Kedua cabang menyentuh `ChallengeService` di metode yang berbeda; yang digabung belakangan menyelesaikan bentrokan ([§18](#18-titik-sambung-ke-perubahan-lain)).

**Batas.** Engine Skenario Digital, layarnya, skor tindakan, dan narasinya milik bagian 6 (`32`). Bagian ini hanya menyediakan **data** skenario: sheet workbook, tabel, impor, dan aturan bank ([K4](#2-keputusan-yang-dikunci)). Lapis turunan (`rapid_guess`, `error_code_analysis`, `near_miss_count`) milik bagian 9 (`35`).

**Saat implementasi:**
- Satu cabang pendek dari `main`, commit berbahasa Inggris.
- `vendor/bin/phpunit --no-coverage` hijau sebelum push.
- Jangan menjalankan migrasi, `gelita:reset`, atau impor bank di server produksi. Bank baru masuk server produksi hanya bila pengembang memutuskannya ([K3](#2-keputusan-yang-dikunci)).
- Tulisan ke `challenge_attempts` dan `item_responses` yang bisa bersaing memakai UPDATE bersyarat (`ChallengeService::updateIf()`/`updateOrStale()`), bukan `SELECT … FOR UPDATE`.

**Prinsip yang tetap berlaku:**
- Server satu-satunya sumber kebenaran. Kunci, kode kesalahan, skor, dan label pengembang tidak pernah dikirim ke peramban.
- Mekanisme di kode, isi di data.
- Aturan tiga kesempatan dan `ScoringService` (`bintang-1`) tetap.
- Label pengembang (level AKM, dimensi, Bloom) tidak pernah diperlihatkan ke ahli sebelum mereka menilai ([01 → Peran pengembang, peneliti, dan ahli](01_REVISI_10_ANALITIK.md#peran-pengembang-peneliti-dan-ahli)).

**Rujukan kode.** Ditulis per berkas dan metode, dengan nomor baris per 2 November 2026 (`main` di *commit* `97f82d5`). Nomor baris bisa bergeser, jadi cari nama metodenya.

**Nomor migrasi.** `2026-01-01-003900` dan `003910`, sementara. Nomor akhir mengikuti urutan digabung ([00 → Tahap dokumen 3](00_INDEKS.md)).

---

## 1. Kebutuhan, sumber, dan tempatnya

| # | Kebutuhan | Sumber | Di sini |
| ---: | --- | --- | --- |
| 1 | Kode kesalahan pada setiap kata pengecoh, benda pengecoh, pernyataan, dan pilihan skenario; diturunkan ke setiap percobaan salah (`item_tries`); tidak pernah dikirim ke peramban | 01 no. 1; [06 → Kode kesalahan di setiap pilihan](06_KISI_KISI_BANK_SOAL.md#kode-kesalahan-di-setiap-pilihan); [11 → Analitik 6](11_KAMUS_DATA.md#analitik-6-pola-kesalahan) | §10 |
| 2 | Tiga aturan tetap kode Isi Rumpang; kode Periksa Unggahan per jenis petunjuk; NON-SEN bukan percobaan | [08 → Cara membaca](08_DRAF_BANK_SOAL.md#cara-membaca) | §6.3, §10 |
| 3 | Label level AKM dan dimensi per butir; indikator per dimensi; label Bloom tetap | 01 no. 3; [06 → Pemetaan dimensi](06_KISI_KISI_BANK_SOAL.md#pemetaan-dimensi-ke-kerangka); [19](19_TELAAH_LABEL_BLOOM.md) | §6.1, §13 |
| 4 | Butir jangkar antarvariasi: kartu jangkar di Benar atau Salah, 2 pernyataan per wilayah, sama untuk a dan b, butir berkode `x` | 01 no. 4; [06 → Butir jangkar](06_KISI_KISI_BANK_SOAL.md#butir-jangkar-usulan) | §5, §11 |
| 5 | Variabel beban permukaan per tantangan di ekspor butir | 01 no. 8; [11 → Variabel butir](11_KAMUS_DATA.md#variabel-butir) | §13 |
| 6 | Area ketuk tidak bersentuhan (jarak minimal 24 dp) untuk babak 1 dan *Periksa Unggahan*; adegan Magelang dan Wonosobo ditata ulang | 01 no. 17; [18](18_MOCKUP_LAYAR_BARU.md) `mk-pu-*` | §9 |
| 7 | Cari Objek dua babak; babak 2 adegan per variasi; satu tantangan dua kelompok butir | 01 no. 20; [06 → Yang perlu disiapkan](06_KISI_KISI_BANK_SOAL.md#yang-perlu-disiapkan-sistem-nanti) no. 2 | §5, §12.1 |
| 8 | Variasi pengerjaan pertama dari buklet B1–B4, bukan acak; `FIRST_ATTEMPT_GROUPS` jadi `a`, `b` | 06 no. 4; [06 → Desain buklet](06_KISI_KISI_BANK_SOAL.md#desain-buklet-usulan) | §11 |
| 9 | `DifficultyRules` baru: 4/5/6 rumpang, 2 pengecoh; 4/6/6 pernyataan; babak 2; skenario; variasi tepat 2 | 06 no. 5 | §8.1 |
| 10 | Keterampilan D1b baru di Benar atau Salah | 06 no. 6 | §8.1 |
| 11 | Kolom dan sheet workbook baru | 06 no. 7, 8 | §6 |
| 12 | Pemeriksaan bank: keseimbangan kunci, setara a/b, satu kode per pilihan salah, domain `.example`, tanpa `*-LAIN` | 06 no. 9 | §8.2 |
| 13 | Ekspor butir: dimensi, sub-aspek, level, genre, testlet, buklet, beban permukaan | 06 no. 10; 11 → Variabel butir | §13 |
| 14 | Susun Gambar selama studi: pengerjaan pertama acak dari dua variasi bersih `a`, `b`, tidak dari buklet; gambar baru Wayang Kedu; gambar Candi Pawon diganti; pemetaan huruf lama ke baru | [08 → Susun Gambar selama studi](08_DRAF_BANK_SOAL.md#keputusan-pengembang-26-oktober-2026) | §14 |
| 15 | Perbaikan teks berkas data dari cek fakta budaya | [25 → Akibat ke dokumen lain](25_CEK_FAKTA_BUDAYA.md#akibat-ke-dokumen-lain) no. 4 | §7.3 |
| 16 | `testlet_key`; jarak ketukan ke kunci dihitung dari data adegan di bank | 11 → Data yang dinilai; [29 §6.2](29_IMPLEMENTASI_DATA_PENELITIAN.md#62-ketukan-di-luar-bagian-ketuk-empty_tap) | §6.1, §9.3 |
| 17 | Kunci jawaban ada di lembar `butir` untuk `ketua` | [29](29_IMPLEMENTASI_DATA_PENELITIAN.md) K1 | §13.3 |

---

## 2. Keputusan yang dikunci

K3, K4, K8, dan K10 diputuskan pengembang ([§22](#22-keputusan-pengembang)). Sisanya menurunkan 01, 06, 08, 11, 19, 25, 28, dan 29.

| Kode | Keputusan |
| --- | --- |
| K1 | **Satu format bank untuk semua isi.** Berkas data PHP (`docs/bank-soal/data/*.php`) tetap sumber yang disunting; `build-workbook.php` membangun workbook; impor membaca workbook. Tidak ada isi bank di kode. |
| K2 | **Revisi isi tidak mengubah kode.** Teks, kunci, kode kesalahan, label, posisi area ketuk, dan rujukan diubah di berkas data, lalu dibangun ulang dan diimpor. Yang tetap di kode hanya **jumlah dari kisi-kisi** (berapa rumpang, pernyataan, petunjuk, bagian ketuk, skenario, pilihan) di `DifficultyRules`, karena itu desain kisi-kisi, bukan isi. Bila matriks revisi mengubah kisi-kisi, satu konstanta diubah ([§21](#21-pertanyaan-terbuka) no. 1). |
| K3 | **Bank baru menggantikan bank lama di `main`.** Berkas data dan workbook ditulis ulang ke bank 08. Bank lama tetap ada di riwayat git dan sebagai salinan arsip `docs/arsip/bank-soal-lama/`. Server produksi mendapat bank baru hanya saat pengembang menjalankan `gelita:reset` atau impor. |
| K4 | **Bagian 5 pemilik semua data bank, termasuk skenario.** Sheet `scenarios` dan `scenario_options`, tabelnya, impornya, dan aturannya ditetapkan di sini. Bagian 6 merancang engine, layar, skor tindakan, dan narasi, dan membaca tabel dari sini. |
| K5 | **Kode kesalahan diturunkan server saat percobaan dinilai** dan disimpan di `item_tries.error_code`. Kode diambil dari tabel kode yang diimpor dari bank, bukan dihitung di klien dan bukan dihitung ulang belakangan. Percobaan benar memakai `error_code` null. |
| K6 | **Setiap pasangan pilihan salah punya baris kode yang eksplisit di bank.** Aturan tetap 08 (misalnya "kata pengecoh di rumpang lain → INF-LIT") dijalankan pembangun workbook, bukan importer. Workbook dan database memuat matriks lengkap, jadi aturan bank bisa memeriksa "tepat satu kode per pilihan salah". |
| K7 | **Cari Objek tetap satu tantangan dengan dua babak.** Butir babak 1 berkode `x` (satu adegan untuk semua), butir babak 2 berkode `a`/`b`. Kesempatan tetap per petunjuk. |
| K8 | **Bagian ketuk *Periksa Unggahan* punya kotak di workbook** (x, y, w, h dalam persen adegan 16:9). Unggahan digambar dari kotak itu sebagai teks biasa (bisa diperbesar dan dibaca), bukan gambar. Satu geometri untuk kedua babak: aturan jarak dan jarak ketukan ke kunci di lapis turunan memakai angka yang sama. |
| K9 | **Area ketuk tidak boleh bertumpuk dan berjarak paling sedikit 24 dp** di kedua babak. `MAX_OVERLAP` 20% diganti aturan jarak ini. Melanggar = galat; impor ditolak. |
| K10 | **Mode penelitian: tanpa buklet, tantangan yang dinilai tidak dibuka.** Bila `field_mode` menyala, studi `active`, dan siswa belum punya baris `participant_seats`, membuka tantangan 2–5 dijawab `BOOKLET_MISSING` dengan pesan ramah. Susun Gambar tetap terbuka. Di luar mode penelitian variasi diacak dari `a`/`b` seperti sekarang. |
| K11 | **Butir `x` selalu ikut variasi yang dimainkan.** Kartu jangkar Benar atau Salah dan babak 1 Cari Objek berkode `x` dimuat di setiap pengerjaan, sesudah butir variasi. `x` tidak pernah menjadi calon variasi. |
| K12 | **Susun Gambar tidak mengikuti buklet.** Pengerjaan pertama acak dari `a` dan `b`; pengerjaan ulang memakai huruf lain dulu. Variasi Temanggung menjadi 11 (`a`–`k`). |
| K13 | **Label pengembang disimpan bersama asalnya.** `akm_level_source` dan `bloom_level_source` bernilai `developer` sampai label hasil pelabelan buta (B.5a) dimasukkan peneliti lewat workbook, lalu `expert`. Label tidak pernah masuk muatan pemain. |
| K14 | **Variabel beban permukaan dihitung saat impor** dari isi bank, disimpan per butir, dan dibekukan bersama bank yang diimpor. |
| K15 | **Pengerjaan mencatat asal variasinya** (`challenge_attempts.variant_source`: `booklet`, `random`, `retake`) dan buklet saat dibuka (`booklet_at_open`), supaya lapis turunan tidak perlu menebak. |

---

## 3. Yang tidak berubah

| Hal | Tetap |
| --- | --- |
| Aturan tiga kesempatan | `Config\Gelita::$maxTries`, `ChallengeRuleException` (`TRIES_EXHAUSTED`, `INCOMPLETE`, `CLUE_CLOSED`, `TRIES_REMAINING`, `ATTEMPT_CHANGED`). Ditambah satu kode baru, `BOOKLET_MISSING` (K10) |
| Satu percobaan per jenis tantangan | Seperti [11 → Percobaan per jenis tantangan](11_KAMUS_DATA.md#percobaan-per-jenis-tantangan): Cek per tantangan untuk Isi Rumpang dan Benar atau Salah (termasuk kartu jangkar), ketukan per petunjuk untuk Cari Objek |
| Bintang dan Serpihan Cahaya | `ScoringService` (`bintang-1`) menghitung dari butir yang `scorable`, termasuk butir `x`. Serpihan tetap satu per tantangan |
| Kunci di server | `ChallengeItem::toPlayerArray()` (`app/Entities/ChallengeItem.php:126`) tetap tanpa `answer_key_json`; `SAFE_CONFIG_KEYS` (`:14`) tidak bertambah kunci rahasia |
| Ref benda yang disamarkan | `ChallengeService::objectRef()` (`:1295`) dan `resolveObjectAnswer()` (`:1315`) dipakai juga untuk bagian ketuk babak 2 |
| Penjaga kunci yang sudah dijawab | `ContentImportService::validateItem()` (`:492`): kunci butir yang sudah dijawab siswa tidak boleh berubah. Karena itu bank baru dipasang lewat `gelita:reset` (K3) |
| Hapus data dan retensi | Kolom baru di `item_tries` dan `challenge_attempts` ikut terhapus bersama barisnya. Tabel baru bagian ini berisi isi bank, bukan data siswa |
| Pustaka, cerita, narasi | Tidak diubah di sini. Teks cerita dari 25 no. 5 milik perubahan cerita ([12](12_CERITA_PENGHUBUNG_DAN_NARASI.md)) |

---

## 4. Bank soal sekarang di kode

Per 2 November 2026:

| Bagian | Di kode | Catatan |
| --- | --- | --- |
| Berkas data | `docs/bank-soal/data/{temanggung,magelang,wonosobo}.php`, dibaca `App\Libraries\BankSource` | Teks berpasangan `[ID, EN]`; kode butir otomatis menurut urutan, atau eksplisit (`'key'`); butir pensiun (`'active' => false`); `'review'` |
| Pembangun | `docs/bank-soal/build-workbook.php` (115 baris), berjalan tanpa CodeIgniter | Menjalankan `BankRules` sebelum menulis |
| Workbook | `docs/bank-soal/gelita-bank-soal-kelas5.xlsx`, sheet `nodes`, `variants`, `distractors`, `passages`, `items`, `hints` (+ `library`, `library_media`, KAMUS_KOLOM) | `BankWorkbookGuideTest` gagal bila workbook belum dibangun ulang |
| Impor | `ContentImportService` (`SHEETS` `:46`, `preview()` `:71`, `import()` `:91`, `write()` `:747`, `writeNodes()` `:797`, `writeItems()` `:910`, `bankProblems()` `:1417`, `normalItem()` `:1557`); perintah `gelita:bank:import` (`app/Commands/BankImport.php`, `--dry-run`, `--staff`); halaman `/admin/konten/impor-bank`; `KontenKelas5Seeder` | Upsert per kode; pengecoh Isi Rumpang disimpan di `challenge_nodes.config_json.variants.{grup}.distractors` |
| Aturan | `DifficultyRules` (`BY_DIFFICULTY` `:48`, `SKILLS` `:76`, `hunt()` `:121`); `BankRules` (`ENGINES` `:46`, `VARIANTS` `:64`, `FIRST_ATTEMPT_GROUPS` `:72` = `a`, `b`, `c`, `MAX_OVERLAP` `:75` = 0,20, `node()` `:88`, `overlap()` `:205`, `hunt()` `:453`, `box()` `:527`); `ContentVerifier` dan `gelita:content:verify` (`--siap-penelitian`) | `BankRules` dipakai pembangun, importer, verifikasi, dan `BankKelas5DataTest` |
| Penyimpanan | `challenge_items` (migrasi `001200`: `item_key`, `variant_group`, `interaction_type`, `answer_key_json`, `bloom_level`, `config_json`, `reference_source`, `review_status`, `scorable`, `is_active`); `reading_passages` (`001000`); `item_tries` (`002100`); `item_responses` (`002000`) | Posisi benda di `config_json` (`x`, `y`, `w`; tinggi = `w × 16/9`) |
| Cache | `ContentRepository::itemBank()` (`:91`), `flush()` (`:199`), TTL 1 jam | |
| Pemilihan variasi | `ChallengeService::openNode()` (`:55`) memanggil `ChallengeAttemptModel::variantsServed()` (`:88`) lalu `ChallengeItemModel::pickVariant()` (`:98`) | Pengerjaan pertama acak dari `a`–`c`; ulang memakai yang belum didapat |
| Penilaian | `grade()` `:749`, `gradeFillBank()` `:775`, `gradeVerdict()` `:787`, `gradeFindObject()` `:799`, `applyAnswer()` `:826`, `recordTry()` `:894`, `registerWrongClick()` `:943` | `recordTry()` menulis `answer_json`, `is_correct`, `first_input_ms`, `last_input_ms` tanpa kode |
| Muatan pemain | `buildPayload()` `:1106`, `huntPayload()` `:1241`, `wordBank()` `:1346` | Benda Cari Objek dikirim dengan ref samaran dan posisinya |
| Ekspor | `RawDataService::COLUMNS` lembar `butir` (`:231`) | `bloom_level`, `scorable`, `is_active`, tanpa label baru |
| Panggung | `public/assets/js/core/stage.js:17` `STAGE_W = 1280` (1 dp = 1 px pada skala 1); `.hunt-scene` 16:9 (`game.css:2503`) | Dasar konversi 24 dp ([§9](#9-area-ketuk-tidak-bersentuhan-perubahan-no-17)) |

---

## 5. Bentuk bank baru per tantangan

Sesuai [06 → Kisi-kisi per wilayah](06_KISI_KISI_BANK_SOAL.md#kisi-kisi-per-wilayah-dan-tantangan) dan [08](08_DRAF_BANK_SOAL.md). Angka per variasi.

| Tantangan | Engine | Variasi | Butir | Jenis soal (`interaction_type`) |
| --- | --- | --- | --- | --- |
| 1 Susun Gambar | `puzzle` | `a`, `b` (pertama) + `c`… (ulang); TMG 11, MGL dan WNB 10 | 1 papan | `puzzle_arrange` (tetap) |
| 2 Isi Rumpang | `rumpang` | `a`, `b` | 4 / 5 / 6 rumpang, bank kata + 2 pengecoh | `fill_blank_bank` (tetap) |
| 3 Benar atau Salah | `boleh` | `a`, `b` + kartu jangkar `x` | 4 / 6 / 6 pernyataan + 2 jangkar | `verdict_card` (tetap) |
| 4 Cari Objek babak 1 *Temukan Benda* | `cari` | `x` | 4 petunjuk + 4 / 6 / 8 benda pengecoh | `find_object` (tetap), `round` = 1 |
| 4 Cari Objek babak 2 *Periksa Unggahan* | `cari` | `a`, `b` | 4 petunjuk, 8 / 10 / 12 bagian ketuk | `find_part` (baru), `round` = 2 |
| 5 Skenario Digital | baru (bagian 6) | `a`, `b` | 4 skenario, tindakan 3 / 4 / 4, alasan 3 / 4 / 5 | `scenario_choice` (baru; nama engine di bagian 6) |

**Kode butir** mengikuti [06 → Kode butir](06_KISI_KISI_BANK_SOAL.md#kode-butir-usulan): `{tantangan}-{variasi}{nn}`, `x` untuk butir yang dipakai semua buklet. Kode bank lama dipakai ulang untuk isi baru (misalnya `tmg-2-a01`), karena belum ada data siswa sungguhan; itu sebabnya bank dipasang lewat `gelita:reset` (K3).

**Testlet.** Setiap butir mendapat `testlet_key`: bacaan Isi Rumpang (`tmg-2-a`), bacaan Benar atau Salah (`tmg-3-a`), kartu jangkar (`tmg-3-x`), adegan *Periksa Unggahan* (`tmg-4-a`). Babak 1 Cari Objek (`tmg-4-x`) dan skenario (kode butirnya sendiri) juga diberi kunci supaya kolomnya tidak pernah kosong. Super-item tidak dibuat sistem ([11 → Butir dan skor per dimensi](11_KAMUS_DATA.md#butir-dan-skor-per-dimensi)).

---

## 6. Workbook: kolom dan sheet baru

Sheet dan kolom lama tetap; yang di bawah ditambahkan. Arti setiap kolom ditulis di sheet KAMUS_KOLOM dan catatan header, seperti sekarang (`BankWorkbookGuide`).

### 6.1 Kolom baru di sheet `items`

| Kolom | Isi | Wajib untuk |
| --- | --- | --- |
| `round` | 1 atau 2 | Cari Objek |
| `dimension` | `D1`, `D2`, `D3`, `PBK` | Semua butir dinilai; kosong untuk Susun Gambar dan benda pengecoh |
| `subaspect` | `D1a`, `D1b` | Butir D1 |
| `akm_level` | `L1`, `L2`, `L3` (D2 dan D3: padanan dari kerumitan) | Butir D1, D2, D3 |
| `pirls_process` | `retrieve`, `infer`, `integrate`, `evaluate` | Butir D1 |
| `label_source` | `developer` (bawaan) atau `expert` | Semua butir berlabel; berlaku untuk AKM dan Bloom ([§13.1](#131-label-pengembang-dan-label-ahli)) |
| `testlet_key` | Lihat [§5](#5-bentuk-bank-baru-per-tantangan) | Butir dinilai |
| `error_code` | Kode jebakan pernyataan Benar atau Salah dan kartu jangkar; kode benda pengecoh babak 1 | `verdict_card`, benda pengecoh |
| `d3_clue_type` | `ATR`, `REP`, `IZN`, `ETK` | `find_part` |
| `d2_topic` | `PRI`, `SAN`, `IZN`, `ASG` | `scenario_choice` |
| `answer_id` | Dipakai juga untuk kunci `find_part` (kode bagian, misalnya `A2`) | `find_part` |

`indicator` dan `skill` tetap. Keterampilan D1b baru masuk daftar `skill` ([§8.1](#81-difficultyrules)).

### 6.2 Kolom baru di sheet `passages`

| Kolom | Isi |
| --- | --- |
| `text_genre` | Kode genre dari [06 → Genre teks digital](06_KISI_KISI_BANK_SOAL.md#genre-teks-digital): `artikel_pustaka`, `artikel_majalah`, `papan_info`, `unggahan_resmi`, `pesan_grup`, `hasil_pencarian`, `situs_wisata`, `dua_sumber`, `unggahan_bersponsor` |
| `layout` | Bentuk tampilan teks digital: `plain`, `info_screen`, `chat`, `search`, `post`. Hanya memilih templat tampilan di engine; isinya tetap teks di `body_id`/`body_en` dengan penanda baris sederhana (misalnya `>>` untuk kepala layar) yang ditetapkan saat implementasi |

Kartu jangkar adalah baris `passages` sendiri dengan kode `{tantangan}-x` (misalnya `tmg-3-x`).

### 6.3 Sheet baru

| Sheet | Kolom | Isi |
| --- | --- | --- |
| `cloze_matrix` | `node_ref`, `variant_group`, `item_key`, `word_id`, `error_code` | Satu baris per rumpang × kata salah (jawaban rumpang lain dan pengecoh). TMG 4 × 5 = 20, MGL 5 × 6 = 30, WNB 6 × 7 = 42 per variasi |
| `object_codes` | `item_key` (petunjuk babak 1), `object_key` (benda yang diketuk), `error_code` | Satu baris per petunjuk × benda selain kuncinya: benda pengecoh = kode bendanya (INF-BUD/INF-LIT), sasaran petunjuk lain = INF-LIT |
| `post_scenes` | `node_ref`, `variant_group`, `title_id`, `title_en`, `text_genre`, `media_asset_key`, `reference_source` | Satu baris per adegan *Periksa Unggahan* (6 adegan). `media_asset_key` = foto di dalam unggahan (08 → *Gambar yang dibutuhkan*) |
| `post_parts` | `node_ref`, `variant_group`, `part_key`, `kind`, `label_id`, `label_en`, `text_id`, `text_en`, `x`, `y`, `w`, `h` | Satu baris per bagian ketuk (`A1`…). `kind`: `account`, `credit`, `caption`, `board`, `comment`, `button`, `meta`. Kotak dalam persen adegan 16:9 (K8) |
| `part_codes` | `item_key` (petunjuk babak 2), `part_key`, `error_code`, `main` | Satu baris per petunjuk × bagian salah. `main` = 1 untuk pengecoh utama di tabel 08; sisanya kode jenis petunjuk (K6) |
| `scenarios` | `item_key`, `d2_topic`, `steps`, `explanation_id`, `explanation_en` | Situasi ada di `items.prompt_*`. `steps` 1 atau 2 (situasi berkembang Wonosobo). `explanation_*` = *penjelasan setelah kesempatan habis* (08) |
| `indicators` | `code`, `name_id`, `name_en`, `description_id`, `description_en`, `domain` | Lima indikator dimensi (D1a, D1b, D2, D3, PBK) dengan pemetaan kerangka dari 06 ([§13.1](#131-label-pengembang-dan-label-ahli)) |
| `scenario_options` | `item_key`, `kind` (`action`, `reason`), `option_key`, `sequence`, `text_id`, `text_en`, `score`, `code`, `feedback_id`, `feedback_en` | Tindakan: `score` 0/1/2, `code` DIG-* (kosong untuk skor 2), umpan balik. Alasan: `code` R-*, tanpa skor dan umpan balik |

Kalimat tanya alasan ("Yang paling penting kupikirkan di sini adalah …") sama untuk semua skenario, jadi ada di berkas bahasa bagian 6, bukan di workbook.

### 6.4 Bentuk berkas data

`BankSource` membaca bentuk baru di berkas data; README bank (`docs/bank-soal/README.md` → *Bentuk berkas data*) ditulis ulang. Inti perubahannya:

- Isi Rumpang: setiap rumpang menulis `'trap' => ['pasir' => 'INF-HUB']` untuk pengecoh sasarannya, dan variasi boleh menulis `'special' => [...]` (baris "Kode khusus" 08). Pembangun menurunkan matriks lengkap dengan tiga aturan tetap 08 (K6).
- Benar atau Salah: `'code' => 'INF-LIT'` per pernyataan; kartu jangkar di `'anchor' => ['passage' => …, 'items' => …]` tingkat tantangan, dibangun menjadi variasi `x`.
- Cari Objek: `'round1' => [...]` (bentuk lama ditambah `'code'` di benda pengecoh) dan `'round2' => ['a' => ['scene' => …, 'parts' => […], 'clues' => […]], 'b' => …]`. Setiap petunjuk menulis kunci, jenis, dan pengecoh utamanya (`'main' => ['A1' => 'BUD-ATR', 'A4' => 'BUD-ATR']`); bagian lain diberi kode jenis petunjuk oleh pembangun.
- Skenario: `'scenarios' => ['a' => [[ 'topic' => 'PRI', 'situation' => [ID, EN], 'actions' => […], 'reasons' => […], 'explanation' => [ID, EN] ], …]]`.
- Label per butir: `'dim'`, `'sub'`, `'akm'`, `'pirls'`, `'bloom'` (tetap), `'labels' => 'expert'` bila sudah dari ahli.

Teks berbahasa Inggris wajib untuk setiap teks yang tampil di game, seperti sekarang. Selama 08 belum punya terjemahan (08 → *Langkah berikutnya* no. 5), berkas data memakai teks Indonesia di kedua kolom dan butirnya diberi `review.note` "terjemahan belum ada"; `gelita:content:verify --siap-penelitian` menjadikannya galat ([§8.2](#82-bankrules)).

---

## 7. Impor dan berkas data

### 7.1 Importer

`ContentImportService` mendapat sheet baru di `SHEETS` dan tahap tulis baru, dalam satu transaksi seperti sekarang:

1. `writeNodes()`, `writePassages()` (kolom genre dan tampilan), `writeItems()` (kolom label, `round`, `error_code`, `testlet_key`, beban permukaan dari [§13.2](#132-variabel-beban-permukaan)).
2. **Baru:** `writePostScenes()` → `post_scenes`, `post_parts`.
3. **Baru:** `writeErrorCodes()` → `item_error_codes` dari `cloze_matrix`, `object_codes`, dan `part_codes`. Baris untuk butir yang ada di workbook diganti seluruhnya per butir (hapus lalu tulis), jadi kode yang direvisi ahli langsung berlaku.
4. **Baru:** `writeScenarios()` → `scenario_options` dan kolom skenario di `challenge_items.config_json` (`steps`, `topic`) dan kolom `explanation_*` (lihat [§15](#15-skema-database)).
5. `writeHints()` tetap.

**Penjaga kunci.** Penjaga yang sudah ada (kunci butir yang sudah dijawab tidak berubah) diperluas ke kunci `find_part`, skor tindakan skenario, dan pilihan yang diberi kode. **Kode kesalahan dan label boleh berubah** walau butir sudah dijawab, karena keduanya bukan kunci: kode lama di `item_tries` tidak ditulis ulang, dan perubahan dicatat di audit `content_import` (jumlah kode yang berubah). Lapis turunan (bagian 9) memutuskan apakah memakai kode tersimpan atau kode bank terbaru.

**Pratinjau** (`preview()` dan `--dry-run`) menampilkan jumlah per sheet baru, dan semua galat dan peringatan `BankRules` baru.

**Kode tidak dikenal ditolak.** Kode kesalahan harus ada di daftar `App\Libraries\ErrorCodes` (baru, dari [03](03_KODE_DAN_ARTI.md)) dan cocok dengan jenis pilihannya ([§8.2](#82-bankrules)).

### 7.2 Pembangun workbook

`build-workbook.php` tetap berjalan tanpa CodeIgniter dan tetap menolak menulis bila `BankRules` mengembalikan galat. Tambahannya:
- menurunkan `cloze_matrix`, `object_codes`, dan `part_codes` lengkap dari berkas data (K6);
- menghitung `testlet_key` dan nomor bagian;
- menulis sheet baru dan KAMUS_KOLOM.

### 7.3 Berkas data ditulis ulang

Ketiga berkas data ditulis ulang dari [08](08_DRAF_BANK_SOAL.md) (116 butir, 6 jangkar, 12 PBK), dengan semua butir `review.status` = `needs_verification`. Bersamaan:
- **Perbaikan dari [25 → Akibat ke dokumen lain](25_CEK_FAKTA_BUDAYA.md#akibat-ke-dokumen-lain) no. 4** diterapkan ke keterangan Susun Gambar dan rujukan (`tmg-1-a`, `-c`, `-e`, `-h`; `mgl-1-g`, `-i`; `wnb-1-b`, `-d`, `-h`, `-j`; rujukan `$ref['geo']`). Teks bacaan dan petunjuk `mgl-2-b`, `mgl-3-a`, `mgl-4-x03`, `wnb-2-a`, `wnb-2-b`, `wnb-4-x04` langsung mengikuti 08, yang sudah memuat perbaikan itu.
- **Huruf Susun Gambar** dipetakan ulang ([§14](#14-susun-gambar-selama-studi)).
- **Bank lama** disalin ke `docs/arsip/bank-soal-lama/` (tiga berkas data dan workbook) dengan README satu paragraf.
- Kalimat 08 yang masih usulan atau bertanda `needs_verification` tidak diubah isinya; yang dipindah hanya bentuknya.

### 7.4 Memasang bank baru

- **Instalasi pengembang dan tes:** `gelita:reset` (mengimpor workbook lewat `KontenKelas5Seeder`) lalu `gelita:mockups`.
- **Instalasi uji validator:** tetap *commit* yang dibekukan di [20](20_PAKET_VALIDASI.md) prasyarat no. 4. Bila pembekuan memakai *commit* sesudah bagian ini digabung, validator memainkan bank baru; itu keputusan pembekuan, bukan bagian ini.
- **Server produksi:** hanya atas keputusan pengembang ([K3](#2-keputusan-yang-dikunci)).
- **Revisi dari validasi:** sunting berkas data → `php docs/bank-soal/build-workbook.php` → `gelita:bank:import --dry-run` → impor → `gelita:content:verify`. Butir yang lolos validasi diberi `review.status` = `verified` dengan catatan asalnya (`asal: peneliti + ahli`).

---

## 8. Aturan bank: `DifficultyRules` dan `BankRules`

### 8.1 `DifficultyRules`

`BY_DIFFICULTY` (`app/Libraries/DifficultyRules.php:48`) diganti:

| Kunci | mudah (Temanggung) | sedang (Magelang) | sulit (Wonosobo) |
| --- | --- | --- | --- |
| `puzzle` | 3 × 3 persegi | 4 × 4 persegi | 4 × 4 jigsaw (tetap) |
| `reading.blanks` / `distractors` | 4 / 2 | 5 / 2 | 6 / 2 |
| `truefalse.statements` | 4 | 6 | 6 |
| `truefalse.key_split` | 2 : 2 | 3 : 3 | 3 : 3 |
| `truefalse.subaspects` | 3 D1a + 1 D1b | 3 D1a + 3 D1b | 2 D1a + 4 D1b |
| `anchor` | 2 pernyataan, 1 : 1, 2 D1a | 1 D1a + 1 D1b | 2 D1b |
| `hunt.round1` | 4 petunjuk + 4 pengecoh | 4 + 6 | 4 + 8 |
| `hunt.round2` | 4 petunjuk, 8 bagian, 2 pengecoh utama | 4, 10, 3 | 4, 12, 3 |
| `scenario` | 4 skenario; tindakan 3, alasan 3 | 4; 4, 4 | 4; 4, 5 |
| `bloom` (rentang wilayah, [19](19_TELAAH_LABEL_BLOOM.md)) | C1–C3 | C2–C4 | C2–C5 |
| `akm` (level yang diharapkan) | L1 (satu rumpang boleh L2) | L2; D1b L3 | D1a L2; D1b, jangkar, D3 L3 |
| `genre` per tantangan | `tmg-2` artikel Pustaka; `tmg-3` papan info; jangkar pesan grup | `mgl-2` artikel majalah; `mgl-3` unggahan resmi; jangkar hasil pencarian | `wnb-2` situs wisata; `wnb-3` dua sumber; jangkar bersponsor |

Rentang Bloom per jenis soal (`bloomRange()`) diganti satu rentang per wilayah, karena 19 menelaahnya per wilayah; jenis soal di luar rentang tetap hanya peringatan.

`SKILLS` (`:76`) ditambah keterampilan D1b (06 no. 6): `mengenali_penanda_sumber`, `menilai_kredibilitas`, `mengenali_iklan`, `membandingkan_sumber`. Label Indonesia di konstanta yang sama.

### 8.2 `BankRules`

**Konstanta:**
- `VARIANTS`: `rumpang`, `boleh` tepat `[2, 2]` (`a`, `b`) ditambah `x` untuk `boleh`; `puzzle` `[2, 11]` dengan `a`, `b` wajib; `cari` babak 1 tepat `x`, babak 2 tepat `a`, `b`.
- `FIRST_ATTEMPT_GROUPS` (`:72`) = `['a', 'b']`.
- `SHARED_GROUP` = `'x'` (baru).
- `MAX_OVERLAP` (`:75`) dihapus; diganti `MIN_GAP_DP = 24` dan `HUNT_SCENE_DP` ([§9](#9-area-ketuk-tidak-bersentuhan-perubahan-no-17)).
- `ENGINES` (`:46`) tetap empat di bagian ini; bagian 6 menambah `5 => '{engine skenario}'` ([§18](#18-titik-sambung-ke-perubahan-lain)). Aturan wilayah (`region()`) membaca panjang `ENGINES`, jadi wilayah dengan tantangan kelima lolos setelah bagian 6 tanpa perubahan lain.

**Galat baru** (impor ditolak, pembangun tidak menulis):

| Pemeriksaan | Sumber |
| --- | --- |
| Jumlah per variasi sesuai [§8.1](#81-difficultyrules), termasuk sub-aspek per variasi dan jangkar | 06, 08 |
| Kunci Benar atau Salah seimbang per variasi dan jangkar 1 : 1 | 06 aturan no. 3 |
| Setiap pilihan salah punya **tepat satu** kode: setiap sel rumpang × kata salah, setiap pernyataan, setiap petunjuk × benda selain kunci, setiap petunjuk × bagian selain kunci, setiap tindakan berskor 0 atau 1 | 06 → Kode kesalahan; K6 |
| Kode cocok dengan jenis pilihannya: Isi Rumpang INF-LIT/KAT/REL/HUB/GEN; pernyataan D1a INF-LIT/KAT/REL/HUB, D1b INF-SUM/FOP/GEN; babak 1 INF-BUD/INF-LIT; babak 2 BUD-*; tindakan DIG-*; alasan R-* | 06, 11 → Analitik 6 |
| Tidak ada kode `*-LAIN` dan tidak ada NON-* di bank | 06 |
| Setiap skenario: tepat satu tindakan skor 2, tepat satu skor 1; set alasan memuat R-RISK atau R-ETIK, R-ATUR, dan R-SOS atau R-ACAK; di Wonosobo kelima kode | 08 → Jumlah dan sebaran |
| Setiap adegan babak 2 memuat keempat jenis petunjuk; kunci setiap petunjuk adalah bagian adegan itu | 06 → Periksa Unggahan |
| Area ketuk di dalam adegan, tidak bertumpuk, berjarak ≥ 24 dp (kedua babak) | K9 |
| Tautan dan alamat di teks hanya `.example`; nomor telepon hanya bentuk samaran `08xx-xxxx-xxxx` | 06 aturan no. 10 |
| `reference_source` wajib untuk setiap bacaan, kartu, adegan babak 2, dan petunjuk | 06 aturan no. 7 |
| Label wajib: `dimension` untuk setiap butir dinilai; `subaspect`, `akm_level`, `pirls_process` untuk D1; `akm_level` untuk D2 dan D3; `bloom_level` untuk semua butir dinilai termasuk PBK dan skenario | 06, 19 |
| Butir dengan `round` hanya di `cari`; `x` hanya di `boleh` (jangkar) dan `cari` babak 1 | K7, K11 |

**Peringatan baru** (galat dengan `--siap-penelitian`):

| Pemeriksaan | Sumber |
| --- | --- |
| Variasi a dan b setara: genre sama, selisih panjang ≤ 15%, jumlah per sub-aspek dan level AKM sama, sebaran kode sama | 06 aturan no. 6 |
| Sebaran kode wilayah: setiap kode INF yang cocok muncul minimal sekali per wilayah; setiap BUD dan DIG berisiko utama di ketiga wilayah | 06 → Sebaran kode yang dituju |
| Bloom atau AKM di luar yang diharapkan wilayah | 19, 06 |
| Keterangan Susun Gambar `a`/`b` memuat kata kunci jawaban D1, kunci bagian D3, atau sasaran PBK wilayah itu atau wilayah sesudahnya (pencocokan kata sederhana; keputusan akhir tetap telaah manual 08) | 08 → Susun Gambar selama studi |
| Kode urut posisi kunci Benar atau Salah berpola selang-seling penuh | 06 aturan no. 3 |
| Teks EN sama dengan teks ID (terjemahan belum ada) | [§6.4](#64-bentuk-berkas-data) |

`ContentVerifier` dan `gelita:content:verify` memakai aturan yang sama terhadap isi database.

---

## 9. Area ketuk tidak bersentuhan (perubahan no. 17)

### 9.1 Ukuran yang dipakai

- Semua posisi dalam **persen adegan 16:9**, sama untuk babak 1 dan 2 dan sama dengan koordinat `empty_tap` ([29 §6.2](29_IMPLEMENTASI_DATA_PENELITIAN.md#62-ketukan-di-luar-bagian-ketuk-empty_tap)).
- Babak 1 tetap memakai kotak benda yang sekarang (`x`, `y`, `w`, tinggi `w × 16/9`, `BankRules::box()` `:527`). Babak 2 memakai kotak `x`, `y`, `w`, `h` dari `post_parts`.
- **24 dp dikonversi ke persen adegan** dengan lebar adegan pada panggung 1280 dp tanpa perbesaran: `HUNT_SCENE_DP` = lebar `.hunt-scene` di tata letak tantangan, diukur sekali saat implementasi dari `game.css` dan dikunci dengan tes. Jarak minimal = 24 / `HUNT_SCENE_DP` × 100 % lebar, dan 24 / (`HUNT_SCENE_DP` × 9/16) × 100 % tinggi. Perbesaran adegan hanya membesarkan jarak, jadi yang diperiksa ukuran terkecil.

### 9.2 Aturannya

Dua kotak *bersentuhan* bila jarak terdekat antartepinya (dalam dp) kurang dari 24, atau bila keduanya bertumpuk. Pemeriksaan per pasangan di setiap adegan. Pesan galat menyebut kedua kode dan jaraknya, misalnya "Benda mgl-4-x02 dan mgl-4-x07 hanya berjarak 11 dp (paling sedikit 24 dp)."

**Akibat ke isi:** adegan babak 1 Magelang dan Wonosobo ditata ulang (01 no. 17), yaitu posisi benda di berkas data diubah sampai aturannya lolos. Bila benda tidak muat lagi, ukurannya diperkecil sampai batas sasaran ketuk minimal yang sudah dipakai sekarang. Adegan Temanggung dicek dengan aturan yang sama.

### 9.3 Untuk lapis turunan

Karena geometri ada di bank, bagian 9 menghitung `near_miss_count` (≤ 24 dp dari kotak kunci petunjuk aktif) dan `empty_tap_count` dari `empty_tap` tanpa data tambahan. `App\Libraries\HuntGeometry` (baru) memegang konversi dp dan jarak tepi; dipakai `BankRules` dan nanti lapis turunan.

---

## 10. Kode kesalahan di setiap percobaan salah (perubahan no. 1)

### 10.1 Sumber kode per jenis

| Jenis | Pilihan siswa | Kode diambil dari |
| --- | --- | --- |
| Isi Rumpang (`fill_blank_bank`) | Kata yang ditaruh di rumpang | `item_error_codes` (`choice_type` = `word`) untuk rumpang × kata |
| Benar atau Salah dan jangkar (`verdict_card`) | Pilihan yang tidak sama dengan kunci | `challenge_items.error_code` pernyataan itu |
| Babak 1 (`find_object`) | Benda yang diketuk | `item_error_codes` (`object`) untuk petunjuk × benda |
| Babak 2 (`find_part`) | Bagian yang diketuk | `item_error_codes` (`part`) untuk petunjuk × bagian |
| Skenario, tindakan | Tindakan skor 0 atau 1 | `scenario_options.code` (dibaca bagian 6) |
| Susun Gambar | — | Tidak dikode |

### 10.2 Di mana diturunkan

- **Kelas baru `App\Libraries\ErrorCodeResolver`**: `resolve(ChallengeItem $item, array $answer, string $locale, ?string $clickedRef): ?string`. Membaca kode dari `ContentRepository` (cache yang sama dengan bank, `flush()` setelah impor).
- **`ChallengeService::recordTry()`** (`:894`) mendapat argumen `?string $errorCode` dan menulisnya di baris `item_tries` yang sama. Pemanggilnya:
  - `applyAnswer()` (`:826`) dan `submitCheck()` (`:302`) untuk Isi Rumpang dan Benar atau Salah, setelah `grade()`;
  - `registerWrongClick()` (`:943`) untuk Cari Objek (benda atau bagian yang diketuk diketahui dari `resolveObjectAnswer()`).
- Tidak ada tulisan baru yang bisa bersaing: kode ditulis dalam INSERT `item_tries` yang sudah ada (kunci unik `challenge_attempt_id`, `challenge_item_id`, `try_no`). Pola UPDATE bersyarat untuk `item_responses` tidak berubah.

**Isi Rumpang berbahasa Inggris.** Siswa yang bermain dalam bahasa Inggris menaruh kata EN. `ErrorCodeResolver` memetakan kata EN ke kata ID lewat pasangan di bank kata (`wordBank()` `:1346` sudah memegang pasangannya), lalu mencari kodenya. Kata yang tidak dikenal (tidak seharusnya terjadi) diberi `error_code` null dan dicatat di log teknis.

**Ketukan di luar bagian ketuk** bukan percobaan (11) dan tidak lewat `recordTry()`; dicatat sebagai `empty_tap` (bagian 3).

### 10.3 Yang tidak pernah dikirim ke peramban

Kode kesalahan, skor tindakan, kode alasan, `d3_clue_type`, `d2_topic`, label pengembang, dan tanda `main` pengecoh. Muatan pemain hanya membawa teks, ref samaran, dan kotak posisi. Tes memeriksa muatan setiap engine dengan daftar kunci terlarang ([§19.1](#191-tes-baru)).

### 10.4 `manual_code`

`item_tries.manual_code` (VARCHAR 12, null) disediakan untuk pengodean manual (`*-LAIN`, transkrip L5) supaya kode bank tidak tertimpa (11 → Analitik 6). Bagian ini hanya membuat kolomnya; pengisiannya lewat impor L5 milik bagian 3 ([29 §10](29_IMPLEMENTASI_DATA_PENELITIAN.md#10-templat-dan-impor-lembar-lapangan-perubahan-no-15)) atau lapis turunan.

---

## 11. Variasi dari buklet dan butir jangkar (perubahan no. 4, 20)

### 11.1 Tabel buklet

`App\Libraries\BookletRules` (baru) memegang tabel 06:

| Buklet | Temanggung | Magelang | Wonosobo |
| --- | --- | --- | --- |
| B1 | a | a | a |
| B2 | b | b | b |
| B3 | a | b | a |
| B4 | b | a | b |

`BookletRules::variantFor(string $booklet, int $levelSequence): string`. Buklet sendiri dihitung dan disimpan bagian 2 (`participant_seats.booklet`, [28 §11](28_IMPLEMENTASI_SESI_LAPANGAN.md#11-isian-l2-kursi-buklet-dan-bentuk-tes)).

### 11.2 Memilih variasi di `openNode()`

Di `ChallengeService::openNode()` (`:55`), baris pemilihan (`:100–101`) diganti pemanggilan `VariantPicker::pick()` (kelas baru, `app/Libraries/`), yang memanggil `ChallengeItemModel::pickVariant()` dengan grup yang sudah ditentukan:

| Keadaan | Variasi | `variant_source` |
| --- | --- | --- |
| Pengerjaan pertama, tantangan 2–5, `field_mode` menyala dan studi `active`, siswa punya buklet | Dari buklet (11.1) | `booklet` |
| Sama, tetapi siswa belum punya buklet | Tidak dibuka: `ChallengeRuleException::bookletMissing()` (K10) | — |
| Pengerjaan pertama, `field_mode` mati atau studi tidak `active` | Acak dari `a`, `b` | `random` |
| Pengerjaan pertama Susun Gambar (selalu) | Acak dari `a`, `b` (K12) | `random` |
| Pengerjaan ulang | Variasi yang belum didapat siswa dulu, seperti sekarang (`variantsServed()`) | `retake` |

"Pengerjaan pertama" di sini sama dengan sekarang (`variantsServed()` kosong). Melanjutkan pengerjaan pertama yang terputus dan membuka pengerjaan ulang hanya setelah wilayah tuntas adalah perubahan no. 16 (bagian 7); `VariantPicker` tidak mengubahnya.

**`BOOKLET_MISSING`.** Kode baru di `ChallengeRuleException`, dijawab API sebagai aturan (bukan galat) dan ditampilkan `game/challenge.js` lewat `ruleError` dengan pesan ramah ([§17](#17-teks-antarmuka)). Halaman misi (`/misi/…`) menampilkan pesan yang sama dan tombol kembali ke peta wilayah. Panel pemantauan bagian 2 menampilkan jumlah siswa sesi itu yang belum punya kursi ([§18](#18-titik-sambung-ke-perubahan-lain)).

### 11.3 Butir `x`

`ChallengeItemModel::groupByVariant()` memisahkan grup `x`. `pickVariant()`:
- tidak pernah memilih `x` sebagai variasi;
- menambahkan butir `x` aktif ke setiap pengerjaan: untuk `boleh` sesudah pernyataan variasi (kartu jangkar), untuk `cari` lebih dulu (babak 1), lalu butir babak 2 variasi itu.

`challenge_attempts.variant_group` tetap menyimpan huruf variasi (`a`/`b`); butir `x` dikenali dari `variant_group` butirnya.

### 11.4 Kolom pengerjaan

`challenge_attempts.variant_source` VARCHAR(10) dan `booklet_at_open` CHAR(2) null, diisi di INSERT pengerjaan yang sudah ada di `openNode()` (dalam transaksi yang sama). Tidak ada tulisan baru yang bisa bersaing.

---

## 12. Engine yang berubah

### 12.1 Cari Objek dua babak (`cari.js`, `huntPayload()`)

**Server.** `huntPayload()` (`:1241`) mengirim `rounds`:
- `round 1`: gambar adegan, benda (ref samaran, posisi), petunjuk babak 1 (teks, `item_id`);
- `round 2`: judul adegan, foto unggahan, bagian ketuk (ref samaran dengan `objectRef()`, kotak, `kind`, label, teks), petunjuk babak 2.

Urutan petunjuk diacak per pengerjaan di dalam babak, seperti sekarang. Babak 2 terbuka setelah keempat petunjuk babak 1 tertutup. Menutup pengerjaan (`closingReason()`, `closeAttempt()`) tetap: tantangan tertutup bila semua butir kedua babak tertutup.

**Penilaian.** `grade()` mendapat `find_part`: benar bila ref yang diketuk adalah bagian kunci petunjuk aktif (`answer_key_json.part`). `gradeFindObject()` tetap untuk babak 1. Ketukan salah lewat `registerWrongClick()` seperti sekarang, dengan kode dari [§10](#10-kode-kesalahan-di-setiap-percobaan-salah-perubahan-no-1).

**Klien.** `engines/cari.js` mendapat babak: setelah babak 1 tertutup tampil layar antara singkat (judul babak 2 dan petunjuk umum), lalu unggahan *Kedugram* digambar dari kotak bagian (elemen `button` berposisi absolut di dalam `.hunt-scene`, teks biasa, bisa diperbesar). Pesan ketukan keliru babak 2 memakai kalimat sendiri ([18](18_MOCKUP_LAYAR_BARU.md) layar 2). Bentuk tampilannya mengikuti mock-up `mk-pu-01` … `mk-pu-04`. Tombol ▶ petunjuk babak 2 dan aturan narasinya milik perubahan no. 18 (bagian 6).

### 12.2 Benar atau Salah dengan kartu jangkar (`boleh.js`)

- Muatan membawa dua bacaan (`passages`): bacaan variasi dan kartu jangkar, masing-masing dengan `layout` dan `text_genre` ([§6.2](#62-kolom-baru-di-sheet-passages)).
- Pernyataan dikelompokkan per bacaan. Kartu jangkar tampil sesudah pernyataan variasi sebagai kartu kedua dengan bacaannya sendiri ([06](06_KISI_KISI_BANK_SOAL.md#butir-jangkar-usulan)).
- Satu **Cek** menilai semua pernyataan kedua kartu; kesempatan tetap per tantangan ([11](11_KAMUS_DATA.md#percobaan-per-jenis-tantangan)).
- Tampilan teks digital (layar info, grup, hasil pencarian, unggahan) dibuat dari `layout`, sebagai teks, bukan gambar (08 → *Gambar yang dibutuhkan*).

Tata letak tepatnya (dua kartu bertumpuk, atau kartu kedua muncul setelah semua pernyataan pertama dijawab) belum ada di mock-up 18 ([§21](#21-pertanyaan-terbuka) no. 2).

### 12.3 Isi Rumpang (`rumpang.js`)

Tidak berubah selain jumlahnya. Bank kata tetap diacak dengan benih pengerjaan.

### 12.4 Susun Gambar (`puzzle.js`)

Tidak berubah. Hanya variasinya ([§14](#14-susun-gambar-selama-studi)).

---

## 13. Label butir dan variabel beban permukaan (perubahan no. 3, 8)

### 13.1 Label pengembang dan label ahli

- Label di workbook: `dimension`, `subaspect`, `akm_level`, `pirls_process`, `bloom_level`, `label_source`.
- Di database: kolom yang sama di `challenge_items`, dengan `akm_level_source` dan `bloom_level_source` (K13). `label_source` di workbook mengisi keduanya; bila hasil B.5a hanya mengganti salah satu, berkas data menulis `'labels' => ['akm' => 'expert']`.
- **Tidak diperlihatkan ke ahli sebelum menilai.** Label tidak masuk muatan pemain, jadi akun `uji.*` di instalasi uji tidak melihatnya. Berkas validator dibuat dari 08 oleh generator di [21](21_BERKAS_BUTIR_VALIDATOR.md), bukan dari workbook ini. Workbook dan panel konten tidak dikirim ke validator ([§23](#23-akibat-ke-dokumen-lain) no. 3).
- Panel konten (`/admin/konten`) menampilkan label hanya untuk staf yang punya `content.view` ([27 §5.1](27_IMPLEMENTASI_PERAN_PENELITI.md#51-peta-kemampuan)).

**Indikator per dimensi.** Kolom `indicator` sekarang (`budaya`, `literasi`) diganti kode dimensi; tabel `learning_indicators` (migrasi `000600`) diisi lima baris: D1a, D1b, D2, D3, PBK, dengan deskripsi dari [06 → Pemetaan dimensi](06_KISI_KISI_BANK_SOAL.md#pemetaan-dimensi-ke-kerangka) (DigComp 3.0, pilar Komdigi, level AKM, PIRLS 2026). Isinya dari sheet `indicators` baru di workbook, bukan kode.

### 13.2 Variabel beban permukaan

Dihitung `App\Libraries\SurfaceLoad` (baru) saat impor, per butir, dari isi bank (K14). Nama mengikuti [11 → Variabel butir](11_KAMUS_DATA.md#variabel-butir):

| Kolom | Isi |
| --- | --- |
| `gap_count` | Banyak rumpang di bacaan butir itu (Isi Rumpang) |
| `distractor_word_count` | Banyak kata pengecoh di bank kata (Isi Rumpang) |
| `passage_words` | Kata di bacaan atau kartu butir itu, dihitung dengan aturan 11 (potongan dipisah spasi dengan minimal satu huruf atau angka; emoji tidak dihitung) |
| `decoy_count` | Benda pengecoh (babak 1) atau bagian ketuk selain kunci (babak 2) |
| `option_count`, `reason_option_count` | Banyak tindakan dan alasan (skenario); bagian ketuk (Cari Objek) |
| `puzzle_grid` | Ukuran papan (Susun Gambar) |
| `tap_min_dp` | Jarak terkecil antara kotak kunci dan kotak lain di adegan itu, dalam dp (baru, dari §9) |

Disimpan di `challenge_items.surface_json` dan dibaca ekspor sebagai kolom.

### 13.3 Lembar `butir` di unduhan

`RawDataService::COLUMNS['butir']` (`:231`) ditambah kolom 11 → *Variabel butir*: `round`, `variant` (nama di 11 untuk `variant_group`), `booklet_shared` (1 untuk `x`), `dimension`, `subaspect`, `akm_level`, `akm_level_source`, `pirls_process`, `bloom_level_source`, `text_genre`, `testlet_key`, `d2_topic`, `d3_clue_type`, kolom beban permukaan, `review_status`, `reference_source`. `answer_key` untuk `ketua` sudah dirancang bagian 3 (29 K1). Kolom lama tidak diganti namanya (29 §7.3).

**Lembar baru `kode_pilihan`** (hanya `ketua`, seperti kunci): satu baris per `item_error_codes` dan per pilihan skenario, supaya analisis bisa memeriksa kode bank yang berlaku saat diunduh. Ditambahkan ke daftar lembar 29 §7.2 ([§23](#23-akibat-ke-dokumen-lain) no. 1).

---

## 14. Susun Gambar selama studi

Mengikuti [08 → Keputusan pengembang (26 Oktober 2026)](08_DRAF_BANK_SOAL.md#keputusan-pengembang-26-oktober-2026).

**Pemetaan huruf lama → baru** (kunci butir dan kunci slot gambar `challenge.item.*` tidak ikut berganti; yang berganti huruf variasi dan kode butir):

| Wilayah | Baru `a` | Baru `b` | `c` dan seterusnya (urutan lama) |
| --- | --- | --- | --- |
| Temanggung (11) | `i` Pasar Papringan | **baru** Wayang Kedu | `c`←`a`, `d`←`b`, `e`←`c`, `f`←`d`, `g`←`e`, `h`←`f`, `i`←`g`, `j`←`h`, `k`←`j` |
| Magelang (10) | `c` Candi Mendut | `e` Candi Pawon (gambar diganti) | `c`←`a`, `d`←`b`, `e`←`d`, `f`…`j` tetap |
| Wonosobo (10) | `d` Kompleks Candi Arjuna | `i` Tari Topeng Lengger | `c`←`a`, `d`←`b`, `e`←`c`, `f`←`e`, `g`←`f`, `h`←`g`, `i`←`h`, `j` tetap |

- `BankRules::VARIANTS['puzzle']` menjadi `[2, 11]`; `a` dan `b` wajib.
- Gambar baru Wayang Kedu (usulan prompt TMG-P11) dan pengganti Candi Pawon masuk Katalog Aset dan dipasang dengan `gelita:katalog:impor`. Sampai gambarnya ada, slotnya memakai gambar contoh (`gelita:mockups`), dan `--siap-penelitian` menjadikannya galat seperti sekarang.
- Keterangan Wayang Kedu dari 08 (tanpa status Warisan Budaya Takbenda); keterangan Candi Arjuna dari 25 (Desa Dieng Kulon, Banjarnegara).
- Peringatan kebocoran keterangan `a`/`b` di [§8.2](#82-bankrules).

---

## 15. Skema database

**Migrasi `2026-01-01-003900_AddBankLabelsAndErrorCodes`:**

| Tabel atau kolom | Isi |
| --- | --- |
| `challenge_items` | Kolom baru: `round` TINYINT (null), `dimension` VARCHAR(4) (null), `subaspect` VARCHAR(4) (null), `akm_level` VARCHAR(2) (null), `akm_level_source` VARCHAR(10) (null), `pirls_process` VARCHAR(20) (null), `bloom_level_source` VARCHAR(10) (null), `testlet_key` VARCHAR(40) (null), `error_code` VARCHAR(12) (null), `d2_topic` VARCHAR(4) (null), `d3_clue_type` VARCHAR(4) (null), `explanation_id` TEXT (null), `explanation_en` TEXT (null), `surface_json` JSON (null). Indeks (`challenge_node_id`, `variant_group`, `round`) |
| `reading_passages` | Kolom baru `text_genre` VARCHAR(30) (null), `layout` VARCHAR(20) (null) |
| `item_tries` | Kolom baru `error_code` VARCHAR(12) (null), `manual_code` VARCHAR(12) (null). Indeks (`error_code`) |
| `challenge_attempts` | Kolom baru `variant_source` VARCHAR(10) (null), `booklet_at_open` CHAR(2) (null) |

**Migrasi `2026-01-01-003910_CreateBankChoiceTables`:**

| Tabel | Isi |
| --- | --- |
| `item_error_codes` (baru) | `id`, `challenge_item_id` FK (`ON DELETE CASCADE`), `choice_type` VARCHAR(10) (`word`, `object`, `part`), `choice_ref` VARCHAR(100) (kata ID dinormalkan, `item_key` benda, atau `part_key`), `error_code` VARCHAR(12), `is_main` TINYINT(1) bawaan 0. Unik (`challenge_item_id`, `choice_type`, `choice_ref`) |
| `post_scenes` (baru) | `id`, `challenge_node_id` FK, `variant_group` VARCHAR(10), `title_id`, `title_en`, `text_genre` VARCHAR(30), `media_asset_id` FK (null), `reference_source` VARCHAR(500), `created_at`, `updated_at`. Unik (`challenge_node_id`, `variant_group`) |
| `post_parts` (baru) | `id`, `post_scene_id` FK (`ON DELETE CASCADE`), `part_key` VARCHAR(10), `kind` VARCHAR(20), `label_id`, `label_en`, `text_id` TEXT, `text_en` TEXT, `x`, `y`, `w`, `h` DECIMAL(5,2), `sequence`. Unik (`post_scene_id`, `part_key`) |
| `scenario_options` (baru) | `id`, `challenge_item_id` FK (`ON DELETE CASCADE`), `kind` VARCHAR(10) (`action`, `reason`), `option_key` VARCHAR(10), `sequence`, `text_id` TEXT, `text_en` TEXT, `score` TINYINT (null), `code` VARCHAR(12) (null), `feedback_id` TEXT (null), `feedback_en` TEXT (null). Unik (`challenge_item_id`, `kind`, `option_key`) |
| `learning_indicators` (ada) | Tanpa kolom baru. Baris D1a, D1b, D2, D3, PBK diisi dari sheet `indicators` saat impor; baris lama `budaya`, `literasi` dinonaktifkan (`is_active` = 0, tidak dihapus) |

**Ikut diperbarui:**
- `tests/_support/Database/ResetTables.php` (skema SQLite).
- `SystemResetService::REQUIRED_MIGRATION` (`:35`, sekarang `003300`) menjadi nomor migrasi terakhir yang digabung; tabel baru masuk daftar yang dikosongkan sebelum impor ulang.
- Model baru: `ItemErrorCodeModel`, `PostSceneModel`, `PostPartModel`, `ScenarioOptionModel`. `ChallengeItemModel`, `ItemTryModel`, `ChallengeAttemptModel`, `ReadingPassageModel`: `allowedFields`.
- `ContentRepository`: `errorCodes(int $itemId)`, `postScene(int $nodeId, string $group)`, `scenarioOptions(int $itemId)`, semuanya di cache yang sama dan ikut `flush()`.
- Layanan dan kelas baru di `app/Libraries/`: `ErrorCodes`, `ErrorCodeResolver`, `BookletRules`, `VariantPicker`, `HuntGeometry`, `SurfaceLoad`.

`RetentionService` dan aturan hapus bagian 4 tidak berubah: tabel baru berisi isi bank, dan kolom baru di `item_tries`/`challenge_attempts` ikut barisnya.

---

## 16. Rute

Tidak ada rute baru.

| Rute | Perubahan |
| --- | --- |
| `/admin/konten/impor-bank` (ada) | Pratinjau menampilkan sheet baru dan galat aturan baru. Kemampuan tetap `content.edit` ([27](27_IMPLEMENTASI_PERAN_PENELITI.md#51-peta-kemampuan)) |
| `POST /api/…/answer` (ada, `submitAnswer`) | Jawaban babak 2: `{ item_id, answer: { object: <ref> } }`, bentuk yang sama dengan babak 1 |
| Membuka tantangan (ada) | Bisa dijawab `BOOKLET_MISSING` (K10) |

---

## 17. Teks antarmuka

Kunci baru di `app/Language/id/Game.php` dengan pasangan di `en/Game.php`; teks panel di `Admin.php` saja. Draf salinan, diperiksa ahli bahasa anak bersama mock-up ([18](18_MOCKUP_LAYAR_BARU.md)).

| Kunci | id | en |
| --- | --- | --- |
| `Game.bookletMissing` | "Tunggu sebentar, ya. Kakak peneliti masih menyiapkan tempat dudukmu. Coba buka lagi setelah diberi tahu." | "Please wait a moment. The researcher is still setting up your seat. Try again when they tell you." |
| `Game.huntRoundTwoTitle` | "Babak 2: Periksa Unggahan" | "Round 2: Check the Post" |
| `Game.huntRoundTwoIntro` | "Sekarang periksa unggahan ini. Baca petunjuknya, lalu ketuk bagian unggahan yang tepat." | "Now check this post. Read the clue, then tap the right part of the post." |
| `Game.huntPartWrong` | "Bukan bagian ini. Bagian itu bukan yang dimaksud petunjuk. Baca lagi petunjuknya, ya." | "Not this part. That part isn't what the clue means. Read the clue again." |
| `Game.anchorCardTitle` | "Bacaan kedua" | "Second text" |
| `Admin.bankDimension`, `Admin.bankSubaspect`, `Admin.bankAkm`, `Admin.bankLabelSource` (`Label pengembang`, `Label ahli`), `Admin.bankErrorCodes` | Label kolom di panel konten dan lembar `butir` | — |

Instruksi tantangan dan petunjuk babak 2 dari 08 ada di workbook, bukan berkas bahasa. Kalimat tanya alasan skenario milik bagian 6. Pesan galat impor tetap ditulis di `ContentImportService` dan `BankRules` dalam bahasa Indonesia panel, seperti sekarang.

---

## 18. Titik sambung ke perubahan lain

| Perubahan | Yang disediakan bagian ini | Yang dirancang di sana |
| --- | --- | --- |
| Bagian 2 ([28](28_IMPLEMENTASI_SESI_LAPANGAN.md)), no. 11 | `VariantPicker` membaca `participant_seats.booklet` dan `research_studies.field_mode`; `BOOKLET_MISSING` | `participant_seats`, `field_mode`, `participant_progress`, `refreshProgress()`. Angka "siswa belum punya kursi" di panel pemantauan |
| Bagian 3 ([29](29_IMPLEMENTASI_DATA_PENELITIAN.md)), no. 7, 14 | `item_tries.error_code`, `manual_code`; kolom baru lembar `butir`; lembar `kode_pilihan`; geometri adegan untuk `empty_tap` | Daftar lembar unduhan, kamus berversi, `empty_tap` dan `ready_ms`, impor L5 ke `manual_code` |
| Bagian 6 (`32`), no. 2, 6, 18 | Sheet `scenarios`/`scenario_options`, tabel `scenario_options`, kolom skenario di `challenge_items`, aturan bank skenario, kode tindakan di `ErrorCodeResolver` | Engine skenario dan namanya, `BankRules::ENGINES[5]`, layar, skor tindakan, `reason_code` dan skor di `item_tries`, narasi instruksi, tombol ▶ petunjuk babak 2 |
| Bagian 7 (`33`), no. 16 | `variant_source`; butir `x` di setiap pengerjaan | Melanjutkan pengerjaan pertama yang terputus, pengerjaan ulang setelah wilayah tuntas, `is_scored_attempt` |
| Bagian 9 (`35`), no. 9 | Kode tersimpan, label, `testlet_key`, beban permukaan, `HuntGeometry` | `error_code_analysis`, `rapid_guess`, `near_miss_count`, penanganan kode bank yang direvisi |
| `ChallengeService` bersama bagian 2 | Bagian ini mengubah `openNode()` (baris pemilihan variasi), `grade()`, `recordTry()`, `registerWrongClick()`, `huntPayload()`, `buildPayload()` | Bagian 2 mengubah `refreshProgress()` dan pembaca kemajuan. Cabang yang digabung kedua menyelesaikan bentrokan |

---

## 19. Pengujian dan kriteria selesai

### 19.1 Tes baru

| Tes | Memeriksa |
| --- | --- |
| `BankRulesNewBankTest` | Setiap galat dan peringatan di §8.2 dengan bank kecil buatan (lolos satu, gagal satu per aturan) |
| `HuntGeometryTest` | Konversi 24 dp dengan `HUNT_SCENE_DP`; jarak tepi; dua kotak bersentuhan, berjarak 23 dp, dan 24 dp |
| `HuntSceneWidthTest` | `HUNT_SCENE_DP` sama dengan lebar `.hunt-scene` di `game.css` (tes membaca CSS), supaya perubahan tata letak memaksa angka diperbarui |
| `ErrorCodeResolverTest` | Satu kasus per jenis di §10.1, termasuk kata EN dan benda sasaran petunjuk lain |
| `ErrorCodeOnTryTest` (trait `GradeFiveWorld`) | Percobaan salah di Isi Rumpang, Benar atau Salah, jangkar, babak 1, dan babak 2 menulis kode yang benar; percobaan benar `error_code` null |
| `NoSecretsInPayloadTest` | Muatan `openNode()` setiap engine tidak memuat `answer_key`, `error_code`, `is_main`, `score`, `code`, `akm_level`, `dimension`, `bloom_level`, `d3_clue_type`, `d2_topic` |
| `BookletVariantTest` | Keempat buklet × tiga wilayah memberi variasi tabel 06; `field_mode` mati → acak `a`/`b`; tanpa kursi → `BOOKLET_MISSING`; Susun Gambar tidak mengikuti buklet; `variant_source` dan `booklet_at_open` terisi |
| `AnchorItemsTest` | Kartu jangkar ikut setiap pengerjaan `boleh`, satu Cek menilai kedua kartu, tiga kesempatan per tantangan tetap |
| `HuntTwoRoundsTest` | Babak 2 baru terbuka setelah babak 1 tertutup; kesempatan per petunjuk; tantangan tertutup setelah kedua babak |
| `SurfaceLoadTest` | `passage_words` dengan aturan 11 (emoji, angka, nama akun); jumlah per jenis |
| `BankImportNewSheetsTest` | Impor sheet baru, impor ulang mengganti kode per butir, kode boleh berubah setelah dijawab, kunci `find_part` tidak boleh |

### 19.2 Tes yang ditulis ulang

- `BankKelas5DataTest`: jumlah dan aturan bank baru.
- `BankWorkbookGuideTest`: sheet dan kolom baru.
- `BankImportCommandTest`: ringkasan dengan sheet baru.
- `WordBankShuffleTest`: 2 pengecoh di Temanggung.
- Tes yang memakai `FIRST_ATTEMPT_GROUPS` `a`–`c` atau `MAX_OVERLAP`, dan `Simulation\SeededItemModel` (pemilihan variasi simulasi).
- Tes yang memakai jumlah butir bank lama (misalnya 3 rumpang Temanggung, 7 rumpang Wonosobo).

### 19.3 Cek manual

- Mainkan ketiga wilayah di instalasi pengembang setelah `gelita:reset`: Cari Objek dua babak, kartu jangkar, Isi Rumpang 4/5/6; di layar 1280 × 720 dan layar terkecil yang diizinkan.
- Ketuk tepi setiap bagian ketuk babak 2 dengan perbesaran mati dan menyala.
- `gelita:content:verify --siap-penelitian` menyebut semua butir `needs_verification`, gambar contoh, dan terjemahan yang belum ada.
- Buka `/admin/konten/impor-bank` dengan workbook yang sengaja salah (kode `INF-LAIN`, dua kotak bersentuhan) dan baca pesannya.

### 19.4 Selesai bila

- Bank 08 terimpor dari berkas data tanpa galat; jumlah sama dengan [08 → Jumlah](08_DRAF_BANK_SOAL.md#jumlah).
- Setiap percobaan salah yang dinilai di keempat engine punya `error_code`, dan tidak ada kode, skor, atau label di muatan pemain.
- Variasi pengerjaan pertama mengikuti buklet di mode penelitian; tanpa buklet tantangan dinilai tidak terbuka.
- Area ketuk kedua babak lolos aturan 24 dp; adegan Magelang dan Wonosobo sudah ditata ulang.
- Lembar `butir` memuat label dan beban permukaan.
- Revisi isi (teks, kunci, kode, label, posisi) bisa diimpor ulang tanpa mengubah kode (diuji dengan satu workbook revisi buatan).
- `vendor/bin/phpunit --no-coverage` hijau.

---

## 20. Dokumen yang ikut diperbarui saat implementasi

| Dokumen | Yang diubah |
| --- | --- |
| `docs/bank-soal/README.md` | Tantangan, aturan per tingkat, variasi dan buklet, bentuk berkas data, sheet workbook, pemeriksaan |
| `docs/07_FEATURE_INTEGRATION.md` (pemetaan kunci workbook) | `find_part`, `scenario_choice`, kode kesalahan |
| `.claude/rules/glossary.md` | Istilah baru ([§23](#23-akibat-ke-dokumen-lain) no. 5) |
| `CLAUDE.md` → *Quick map* | Bank dari workbook kini dengan kode kesalahan dan buklet; `ErrorCodeResolver`, `VariantPicker` |
| `docs/PROGRESS.md`, `docs/perombakan-kelas5/CATATAN_IMPLEMENTASI.md` | Baris dan keputusan implementasi |
| `docs/arsip/bank-soal-lama/README.md` | Baru |

---

## 21. Pertanyaan terbuka

1. ~~**Jumlah kisi-kisi di kode atau di data?**~~ K2 menyimpan jumlah (rumpang, pernyataan, petunjuk, bagian, skenario, pilihan) di `DifficultyRules`. Bila matriks revisi mengubah kisi-kisi (misalnya petunjuk kelima per adegan bila reliabilitas D3 < 0,60, [06](06_KISI_KISI_BANK_SOAL.md#batas-reliabilitas-d2-dan-d3)), satu konstanta diubah. Pilihan lain: sheet `rules` di workbook. Usulan: tetap di kode, karena jumlah adalah desain yang diuji Rasch, bukan isi. **Diputuskan pengembang 2 November 2026** (sesi koordinator, [04](04_CATATAN_KEPUTUSAN.md)): tetap di kode (`DifficultyRules`), seperti K2.
2. ~~**Tata letak kartu jangkar.**~~ Dua kartu bertumpuk dengan satu Cek, atau kartu kedua muncul setelah semua pernyataan pertama dijawab? Belum ada mock-up di 18. Usulan: bertumpuk, satu Cek; diperiksa ahli media. **Diputuskan pengembang 2 November 2026** (sesi koordinator, [04](04_CATATAN_KEPUTUSAN.md)): bertumpuk dengan satu Cek; ahli media memeriksanya di langkah 2.
3. ~~**Kode bank yang direvisi setelah ada percobaan.**~~ Kode tersimpan tidak ditulis ulang (§7.1). Lapis turunan (bagian 9) memilih: kode saat percobaan atau kode bank terbaru. Usulan untuk bagian 9: kode bank terbaru, dengan kode saat percobaan tetap di data mentah. **Diputuskan pengembang 2 November 2026** (sesi koordinator, [04](04_CATATAN_KEPUTUSAN.md)): kode bank terbaru untuk analisis; `item_tries.error_code` tetap kode saat percobaan ([35 → Keputusan pengembang](35_IMPLEMENTASI_LAPIS_DATA_TURUNAN.md#19-keputusan-pengembang) no. 5).
4. ~~**Terjemahan bahasa Inggris** bank baru belum ada (08 → *Langkah berikutnya* no. 5).~~ **Diputuskan pengembang 2 November 2026** (sesi koordinator, [04](04_CATATAN_KEPUTUSAN.md)): AI mendraf terjemahan bahasa Inggris sekarang, disetujui pengembang, lalu butir yang direvisi dari validasi diterjemahkan ulang. Tidak dipilih: diterjemahkan sekali setelah matriks revisi, sebelum G3 (usulan AI); studi hanya berbahasa Indonesia.
5. **Penanda baris di bacaan digital** (`layout`): bentuk penanda di `body_id` ditetapkan saat implementasi, bersama mock-up.
6. **`HUNT_SCENE_DP`** diukur dari tata letak tantangan sekarang; bila tata letak tantangan berubah (misalnya dari bagian 7, layar kecil), angka dan adegan diperiksa ulang.

---

## 22. Keputusan pengembang

Wawancara 2 November 2026, satu putaran, empat pertanyaan · `asal: pengembang` · `draf pengembang`.

1. **Pemilik data skenario: bagian 5** (K4). Sheet `scenarios`/`scenario_options`, tabel, impor, dan aturan bank skenario dirancang di sini; bagian 6 merancang engine, layar, skor, dan narasi. Tidak dipilih: bagian 6 memegang data skenario sendiri; sheet di bagian 5 dan tabel di bagian 6.
2. **Bagian ketuk *Periksa Unggahan* berupa kotak di workbook** (K8): x, y, w, h dalam persen adegan; unggahan digambar dari kotak itu sebagai teks. Tidak dipilih: templat CSS tetap dengan pemeriksaan Playwright; gambar unggahan dengan titik ketuk.
3. **Bank baru menggantikan bank lama di `main`**, dipasang di server produksi hanya saat pengembang menjalankan impor (K3). Tidak dipilih: dua bank di balik sakelar; bank baru di cabang sampai G2.
4. **Tanpa buklet di mode penelitian, tantangan dinilai tidak dibuka** dan siswa melihat pesan ramah (K10). Usulan AI adalah variasi acak `a`/`b` dengan penanda supaya bermain tidak tertahan; pengembang memilih menahan supaya data pengerjaan pertama selalu mengikuti desain buklet. Tidak dipilih: acak dengan penanda (usulan AI); buklet B1 sebagai bawaan.


Putaran kedua, 2 November 2026 (sesi koordinator, `asal: pengembang`, [04](04_CATATAN_KEPUTUSAN.md)), menjawab [§21](#21-pertanyaan-terbuka) no. 1–4:

5. **Jumlah kisi-kisi tetap di kode** (`DifficultyRules`, K2). Tidak dipilih: sheet `rules` di workbook.
6. **Kartu jangkar bertumpuk dengan satu Cek.** Tidak dipilih: kartu kedua muncul setelah semua pernyataan pertama dijawab.
7. **Analisis memakai kode bank terbaru**; kode saat percobaan tetap di `item_tries.error_code`. Tidak dipilih: kode saat percobaan.
8. **Terjemahan bahasa Inggris bank baru didraf AI sekarang**, disetujui pengembang, dan butir yang direvisi dari validasi diterjemahkan ulang. Tidak dipilih: diterjemahkan sekali setelah matriks revisi (usulan AI); studi hanya berbahasa Indonesia.

---

## 23. Akibat ke dokumen lain

Dibawa sesi koordinator setelah digabung ke `main`.

1. **[29](29_IMPLEMENTASI_DATA_PENELITIAN.md) §7.2–7.3:** lembar `butir` mendapat kolom §13.3; lembar baru `kode_pilihan` (hanya `ketua`); lembar `percobaan` mendapat `error_code`, `manual_code`; lembar `pengerjaan` mendapat `variant_source`, `booklet_at_open`. §15 baris no. 1: kolomnya sekarang dirancang di 31.
2. **[28](28_IMPLEMENTASI_SESI_LAPANGAN.md) §12 dan §18:** panel pemantauan menampilkan jumlah siswa sesi itu yang belum punya kursi (karena tanpa kursi tantangan dinilai tidak terbuka, K10); baris no. 20 di §18 menunjuk 31 §11.
3. **[20](20_PAKET_VALIDASI.md) prasyarat no. 4 dan [21](21_BERKAS_BUTIR_VALIDATOR.md):** workbook, berkas data, dan halaman panel konten memuat label pengembang, jadi tidak ikut paket validator; berkas validator tetap dibuat dari 08. Bila *commit* yang dibekukan sudah memuat bank baru, validator memainkan bank baru.
4. **Bagian 6 (`32`):** membaca format data skenario dari §6.3 dan §15; menambah `BankRules::ENGINES[5]` dan `reason_code`/skor di `item_tries`.
5. **Glosarium** (saat implementasi): babak (`round`), *Periksa Unggahan* (`post check`, `find_part`, `post_scenes`, `post_parts`), bagian ketuk (`tap part`, `part_key`), kartu jangkar (`anchor card`, grup `x`), buklet (`booklet`, `participant_seats.booklet`, `variant_source`), kode kesalahan (`error_code`, `item_error_codes`, `ErrorCodeResolver`), beban permukaan (`surface load`, `surface_json`), label pengembang/ahli (`label source`).
6. **[03](03_KODE_DAN_ARTI.md):** kode `BOOKLET_MISSING`; nilai `variant_source` (`booklet`, `random`, `retake`); kode genre dan `layout` bacaan.
7. **[11](11_KAMUS_DATA.md) → *Variabel butir*** (`kamus-1.1`): `tap_min_dp`, `akm_level_source` berpasangan dengan `bloom_level_source`, `variant_source`, `booklet_at_open`; `variant` untuk Susun Gambar kini `c`–`k`.
8. **[08](08_DRAF_BANK_SOAL.md):** pemetaan huruf Susun Gambar di §14 menjawab "Pemetaan huruf lama ke huruf baru ditulis di dokumen implementasi".
9. **[01](01_REVISI_10_ANALITIK.md) → *Catatan perubahan sistem*:** baris no. 1, 3, 4, 8, 17, 20 menunjuk 31.
