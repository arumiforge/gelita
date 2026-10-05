# Perombakan Kelas 5 · Dokumen 1 — Fondasi Data, Reset Total, Satu Tahap, Pendaftaran Bertahap, Perangkat, dan Data Mentah

> **Status: rencana siap dikerjakan** (disusun 5 Oktober 2026). Dokumen ini adalah **dokumen 1 dari 3** perombakan GELITA untuk penelitian **kelas 5 SD/MI**. Kerjakan berurutan:
>
> 1. **Dokumen 1** (berkas ini) — skema database, perintah reset total, satu tahap bermain, pendaftaran 3 langkah, pelacakan perangkat, data mentah, bintang dan istilah baru.
> 2. [**Dokumen 2**](02_TANTANGAN_DAN_BANK_SOAL.md) — empat tantangan, aturan 3 kesempatan, variasi acak, **bank soal final** (sudah disetujui peneliti), gambar mockup, angka serpihan di naskah cerita, teks UI yang lebih ramah.
> 3. [**Dokumen 3**](03_TAMPILAN_16x9_DAN_ADMIN.md) — panggung 16:9 tetap untuk seluruh layar game, tantangan lebar penuh tanpa gambar Jaka, panel admin SaaS dengan tabel formal, ekspor data mentah, dokumentasi, dan **prosedur rilis ke server** (termasuk menjalankan reset total satu kali).
>
> Penelitian belum dimulai. Data yang ada di server hanyalah akun dan permainan uji coba admin, jadi **tidak ada dukungan untuk data lama**: migration boleh membuang kolom dan tabel tanpa memindahkan isinya, lalu `php spark gelita:reset` membersihkan sisanya.

---

## Daftar isi

0. [Cara memakai ketiga dokumen](#0-cara-memakai-ketiga-dokumen)
1. [Permintaan → keputusan → tempat pengerjaan](#1-permintaan--keputusan--tempat-pengerjaan)
2. [Yang tidak boleh berubah](#2-yang-tidak-boleh-berubah)
3. [Keputusan yang dikunci](#3-keputusan-yang-dikunci)
4. [Kamus istilah](#4-kamus-istilah)
5. [Skema database baru (migration `004100`–`004600`)](#5-skema-database-baru-migration-004100004600)
6. [Konfigurasi `Config\Gelita`](#6-konfigurasi-configgelita)
7. [Perintah reset total `gelita:reset`](#7-perintah-reset-total-gelitareset)
8. [Seeder](#8-seeder)
9. [Satu tahap bermain dan pemilihan soal acak](#9-satu-tahap-bermain-dan-pemilihan-soal-acak)
10. [Pendaftaran bertahap tiga langkah](#10-pendaftaran-bertahap-tiga-langkah)
11. [Pelacakan perangkat](#11-pelacakan-perangkat)
12. [Data mentah: tujuh dimensi analitik](#12-data-mentah-tujuh-dimensi-analitik)
13. [Bintang dan istilah "Berhasil pada Percobaan Pertama"](#13-bintang-dan-istilah-berhasil-pada-percobaan-pertama)
14. [Kode yang dihapus](#14-kode-yang-dihapus)
15. [Pengujian dan kriteria selesai Dokumen 1](#15-pengujian-dan-kriteria-selesai-dokumen-1)

---

## 0. Cara memakai ketiga dokumen

- **Urutan wajib:** Dokumen 1 → 2 → 3. Dokumen 2 memakai kolom dan tabel dari Dokumen 1; Dokumen 3 memakai payload dan data dari keduanya.
- **Aplikasi tetap dapat dijalankan di akhir setiap dokumen.** Setiap dokumen ditutup dengan daftar uji dan kriteria selesai. Jalankan `vendor/bin/phpunit --no-coverage` dan uji manual di browser sebelum lanjut ke dokumen berikutnya.
- **Satu commit per bagian bernomor** (mis. "Dok1 §5: migration 004100–004600"), di branch kerja yang ditentukan saat pengerjaan. Pesan commit berbahasa Indonesia seperti riwayat repositori.
- **Jangan menjalankan `gelita:reset` di server produksi** sebelum ketiga dokumen selesai. Prosedurnya ada di Dokumen 3 §13 (rilis). Selama pengerjaan, reset hanya dijalankan di mesin pengembangan.
- **Rencana lama yang ditunda.** [`docs/rencana-perbaikan-tampilan.md`](../rencana-perbaikan-tampilan.md) dan [`docs/rencana-katalog-aset.md`](../rencana-katalog-aset.md) belum dikerjakan. Bagian yang bersinggungan (tata letak, toast, ramah sentuh, gambar tantangan §11.8 katalog) **digantikan** ketiga dokumen ini. Bagian lain (avatar, pin per kota, animasi frame, katalog aset statis) tetap ditunda dan **jangan** dikerjakan bersamaan dengan perombakan ini.
- **Prinsip yang tetap berlaku** (README → *Keputusan penting*): server satu-satunya sumber kebenaran; kunci jawaban tidak pernah dikirim ke browser; operasi multi-tabel dalam satu transaction; setiap tindakan penting menulis raw event atau audit log; dwibahasa ID/EN; tanpa CDN (semua aset di-host sendiri); halaman tetap terbaca tanpa JavaScript sejauh mungkin.

---

## 1. Permintaan → keputusan → tempat pengerjaan

| # | Permintaan peneliti | Keputusan | Dikerjakan di |
|---:|---|---|---|
| 1 | Fokus hanya kelas 5 SD, penelitian belum mulai | Kelas dikunci 5 (sudah), semua sisa uji coba dibersihkan | Dok 1 §7, Dok 3 §13 |
| 2 | Reset total data dan pengaturan lewat `php spark` | Perintah baru `gelita:reset` | Dok 1 §7 |
| 3 | Tidak perlu dukungan data lama | Migration membuang kolom/tabel tanpa backfill | Dok 1 §5 |
| 4 | Hanya 4 tantangan di semua wilayah: Susun Gambar, Isi Rumpang, Benar atau Salah, Cari Objek | 4 node per wilayah, 12 serpihan cahaya | Dok 1 §8, Dok 2 §1 |
| 5 | Isi Rumpang berbasis Bloom, 3 variasi per wilayah, pilihan jawaban selalu diacak (termasuk level mudah) | Kelompok variasi `a/b/c`, bank kata diacak dengan jaminan tidak sama dengan urutan rumpang | Dok 2 §3–§4, §6.2 |
| 6 | Benar atau Salah: 1 bacaan; mudah 1 paragraf pendek 3 soal, sedang 2 paragraf 5 soal, sulit 3 paragraf 6 soal; 3 bacaan acak per wilayah; keterampilan per level | Satu `reading_passages` per variasi, pernyataan `verdict_card` | Dok 2 §6.3, §9 |
| 7 | Cari Objek: 4 benda dicari di semua level; pengecoh 4 / 6 / 8 (total 8 / 10 / 12) | Aturan per tingkat di `DifficultyRules` | Dok 2 §6.4, §9 |
| 8 | "Tepat sejak awal" → **Berhasil pada Percobaan Pertama** | Istilah baru di game, admin, ekspor | Dok 1 §13, Dok 2 §12 |
| 9 | Maksimal 3 kesempatan bila salah; lewat dari itu jawaban disimpan apa adanya | `Config\Gelita::$maxTries = 3`, ditegakkan server | Dok 2 §5 |
| 10 | Kata "pemeriksaan" di UI diganti bahasa yang lebih ramah | "Cek jawabanku", "kesempatan", "percobaan" | Dok 2 §12 |
| 11 | Analisa bank soal lama; pakai yang cocok, buat yang baru, hapus yang tidak dipakai; bank soal buatan Claude disetujui sebagai versi final | Analisa per butir + bank soal final lengkap ID/EN | Dok 2 §8–§9 |
| 12 | Pustaka Temanggung tidak berubah sama sekali; Kenali wilayah tidak berubah | Pustaka (semua wilayah) dan Kenali tidak disentuh reset maupun impor | Dok 1 §2 |
| 13 | Kuis pilihan, rumpang ketik sendiri, dan benar–salah beralasan dihilangkan | Engine `pilihan`, `fill_blank_free`, `verdict_reason`, `ordering`, `source_trust` dihapus | Dok 1 §14, Dok 2 §6.5 |
| 14 | Gambar Jaka kecil di tantangan & layar selesai dihilangkan; konten tantangan lebar penuh, di tengah | Kerangka tantangan baru | Dok 3 §5 |
| 15 | Pendaftaran: hapus izin orang tua/wali, nama wali, versi teks persetujuan (UI & laporan); jenis kelamin 2; wizard 3 tahap | `participant_consents` dihapus; centang persetujuan siswa saja; 3 langkah server-side | Dok 1 §5, §10 |
| 16 | Semua tampilan game dikunci 16:9, tidak responsif, putar layar tetap wajib, kelebihan isi digulir tanpa mengubah aset | Panggung 1280 × 720 dp yang diskalakan; area gulir di dalam panggung | Dok 3 §1–§6 |
| 17 | Admin: dasbor SaaS premium, tabel formal, tidak bertema GELITA; tanpa tes awal/akhir | Sistem desain admin terpisah; halaman analitik olahan dihapus | Dok 3 §8 |
| 18 | Mockup gambar untuk semua tantangan, nanti diganti admin | Perintah `gelita:mockups` + penanda `is_placeholder` | Dok 2 §10 |
| 19 | Tanpa pretest/posttest → hanya 1 tahap; pemilihan soal selalu acak | Tabel fase dihapus; satu variasi dipilih acak per pengerjaan | Dok 1 §9 |
| 20 | Analitik = data mentah: correctness, attempts, time, progress, completion, navigation, help use | Tabel/kolom/event baru, ekspor mentah + kamus data | Dok 1 §12, Dok 3 §9 |
| 21 | Kritik & saran dipertahankan | Balai Refleksi & tabel `participant_feedback` tetap | Dok 1 §2 |
| 22 | Pelacakan perangkat lebih tepat dengan library tambahan | `matomo/device-detector` + Client Hints + sinyal browser → `device_logs` | Dok 1 §11 |
| 23 | Tiga dokumen implementasi | Berkas-berkas di folder ini | — |

---

## 2. Yang tidak boleh berubah

| Bagian | Aturan |
|---|---|
| **Pustaka** (`library_pages`, `library_media`, berkas gambar/video yang dipakainya) | Isi, gambar, teks, dan video **tidak diubah**. Peneliti menyebut Pustaka Temanggung secara khusus; perombakan ini juga tidak menyentuh Pustaka Magelang dan Wonosobo. `gelita:reset` tidak menghapus tabel ini, dan workbook bank soal kelas 5 **tidak memuat** sheet `library`/`library_media`, sehingga impor tidak dapat menimpanya. Data Pustaka di repositori dipindahkan apa adanya ke `docs/pustaka/` (Dok 2 §7.1) hanya untuk pemasangan baru. Tampilan Pustaka hanya dibungkus panggung 16:9 (Dok 3) tanpa mengubah isinya. |
| **Kenali wilayah** (`dialogues` konteks `region_intro`, 12 baris + audio) | Teks, audio, pose, efek, urutan slide, dan lencana "Belum didengar" tidak berubah. Overlay-nya hanya dipindah ke dalam panggung 16:9. |
| **Cerita lain** (`intro`, `map_intro`, `level_open`, `level_done`, `ending`) | Tidak berubah, **kecuali** 11 baris yang menyebut jumlah serpihan (15 → 12, 5 → 4 per wilayah). Daftar dan teks barunya di Dok 2 §11. |
| **Akun siswa** | Nama pengguna + kata sandi kuat (literasi keamanan digital), metrik `pw_first_submit_criteria` / `pw_weak_submit_count`, ganti sandi, reset sandi oleh guru — tetap. |
| **Sekolah** | Direktori resmi NPSN Jawa Tengah (`schools` terverifikasi) dan alur NPSN tetap. |
| **Kritik & saran** | Balai Refleksi (`/refleksi`), tabel `participant_feedback`, dan daftarnya di admin tetap. |
| **Audio** | Musik gamelan, efek suara, narasi, telemetry audio (`audio_usage_events`) tetap. |
| **Levels** | Baris `levels` (urutan, kode, nama, peta, latar, lencana, posisi pin) tidak dihapus; `dialogues` dan `library_pages` bergantung pada id-nya. |
| **Keamanan** | CSRF, otorisasi berlapis tiga, guru hanya melihat sekolahnya, ekspor guru selalu anonim, kunci jawaban tidak pernah ke browser, token objek `cari` (HMAC). |

---

## 3. Keputusan yang dikunci

Keputusan ini tidak perlu ditanyakan ulang saat implementasi.

| Kode | Keputusan |
|---|---|
| K1 | **4 tantangan per wilayah**, urutan sama di ketiga wilayah: 1 Susun Gambar (`puzzle`), 2 Isi Rumpang (`rumpang`), 3 Benar atau Salah (`boleh`), 4 Cari Objek (`cari`). Total **12 node = 12 serpihan cahaya**. Node di dalam wilayah tetap terbuka berurutan. |
| K2 | **Jenis interaksi tinggal empat:** `puzzle_arrange`, `fill_blank_bank`, `verdict_card`, `find_object`. Engine `pilihan` dan interaksi `single_choice`, `source_trust`, `ordering`, `fill_blank_free`, `verdict_reason` dihapus beserta kodenya. Pilihan verdict hanya **Benar / Salah** (tanpa "Pendapat"). |
| K3 | **Satu tahap bermain.** Konsep fase (`umum/pretest/posttest`) dihapus dari database, kode, UI, dan ekspor. |
| K4 | **Pemilihan soal selalu acak**: setiap pengerjaan memakai **satu variasi** (`a`, `b`, atau `c`) yang dipilih acak; pengerjaan ulang memilih variasi yang belum pernah didapat siswa itu bila masih ada. Mode `fixed` dihapus. |
| K5 | **Maksimal 3 kesempatan** per soal. Bila kesempatan habis, jawaban terakhir disimpan apa adanya dan tantangan selesai (tetap mendapat serpihan). Ditegakkan server. |
| K6 | **Skor 0–100 dan profil skor dihapus.** Pemain tetap mendapat **bintang** dengan aturan sederhana (§13). Istilah resmi: **Berhasil pada Percobaan Pertama**. |
| K7 | **Analitik = data mentah.** Halaman analitik olahan (indeks kesulitan, daya pembeda, capaian indikator, pretest–posttest, chart) dihapus. Admin menampilkan tabel data mentah dan ekspor lengkap dengan kamus data (Dok 3 §8–§9). |
| K8 | **Persetujuan.** Izin orang tua/wali, nama wali, teks dan versi persetujuan dihapus (persetujuan diurus guru secara luring). Tersisa satu centang persetujuan siswa di langkah 3 pendaftaran, disimpan sebagai `participants.assented_at`. |
| K9 | **Jenis kelamin** hanya `laki-laki` dan `perempuan`. |
| K10 | **Pendaftaran 3 langkah server-side** (`/daftar/1`, `/2`, `/3`); berjalan juga tanpa JavaScript. Halaman `/persetujuan` dihapus. |
| K11 | **Perangkat**: library `matomo/device-detector` + User-Agent Client Hints + sinyal browser, dicatat per masuk/daftar di tabel baru `device_logs`. |
| K12 | **Reset total** lewat `php spark gelita:reset`. Pustaka, Kenali/cerita, level, media, audio, direktori sekolah resmi, dan akun staf tetap. |
| K13 | **Panggung 16:9** hanya untuk area game (siswa). Panel admin adalah dasbor SaaS yang mengikuti lebar layar (desktop/laptop/tablet), karena tabel data membutuhkan lebar dan tidak memuat aset visual game. |
| K14 | **Gambar mockup** dibangkitkan sistem (SVG) untuk setiap slot gambar tantangan yang belum berisi gambar asli, ditandai `media_assets.is_placeholder = 1`. |
| K15 | Kode butir baru `{node}-{variasi}{nn}` (mis. `tmg-3-b02`), kode bacaan `{node}-{variasi}` (mis. `mgl-3-a`). Kunci media boleh memakai ulang kunci lama agar gambar asli yang mungkin sudah diunggah admin tetap terpakai (Dok 2 §9). |

---

## 4. Kamus istilah

Istilah ini dipakai konsisten di kode (komentar), UI, admin, dan ekspor.

| Istilah | Arti | Tabel/kolom |
|---|---|---|
| **Tantangan** | Satu dari 4 pos di peta wilayah (node). | `challenge_nodes` |
| **Soal** / butir | Unit yang dinilai: satu rumpang, satu pernyataan, satu benda yang dicari, satu gambar puzzle. | `challenge_items` |
| **Variasi** | Kelompok soal yang tampil bersama dalam satu pengerjaan (`a`, `b`, `c`). Susun Gambar: satu gambar; Isi Rumpang: satu bacaan rumpang; Benar atau Salah: satu bacaan + pernyataannya; Cari Objek: satu adegan + 4 benda + pengecoh. | `challenge_items.variant_group`, `challenge_attempts.variant_group` |
| **Pengerjaan** | Satu kali siswa memainkan sebuah tantangan dari awal sampai selesai/ditinggalkan. Siswa boleh mengulang tantangan (pengerjaan ke-2, ke-3, …). Di kode tetap bernama *attempt*. | `challenge_attempts` (`attempt_no`) |
| **Percobaan** | Satu kali siswa mengirim jawaban sebuah soal untuk dicek: menekan **Cek jawabanku** (Susun Gambar, Isi Rumpang, Benar atau Salah) atau mengetuk satu benda (Cari Objek). Maksimal 3 per soal. | `item_tries.try_no`, `item_responses.try_count` |
| **Kesempatan** | Sisa percobaan yang masih boleh dipakai (3 − percobaan terpakai). Kata ini yang tampil di UI siswa. | dihitung dari `try_count` / `check_count` |
| **Berhasil pada Percobaan Pertama** | Soal yang benar pada percobaan ke-1. Untuk satu pengerjaan: jumlahnya (`x dari y soal`). | `item_responses.first_pass_correct`, `challenge_attempts.first_pass_correct` |
| **Benar di akhir** | Soal yang benar pada percobaan terakhirnya (ke-1, ke-2, atau ke-3). | `item_responses.is_correct`, `challenge_attempts.final_correct` |
| **Kesempatan habis** | Pengerjaan ditutup otomatis setelah percobaan ke-3 masih ada yang salah; jawaban disimpan apa adanya. | `challenge_attempts.completion_reason = 'tries_exhausted'` |
| **Bantuan** | Petunjuk yang dibuka siswa (💡) dan narasi suara yang diputar. | `hint_opened`, `audio_usage_events` |

---

## 5. Skema database baru (migration `004100`–`004600`)

### 5.1 Ringkasan

| Migration | Isi |
|---|---|
| `2026-01-01-004100_RemovePhasesAndConsent` | Hapus fase penelitian, kolom pengaturan fase/consent di studi, tabel `participant_consents`; tambah `participants.assented_at` |
| `2026-01-01-004200_SimplifyAttemptScoring` | Hapus skor & profil skor; tambah variasi, alasan selesai, batas percobaan di attempt; hitungan percobaan di jawaban; hapus kolom alasan |
| `2026-01-01-004300_CreateItemTries` | Tabel baru `item_tries` (setiap percobaan per soal) |
| `2026-01-01-004400_AddVariantGroupsDropOptions` | `challenge_items.variant_group`; hapus tabel `challenge_options` |
| `2026-01-01-004500_CreateDeviceLogs` | Tabel baru `device_logs` |
| `2026-01-01-004600_AddMediaPlaceholderFlag` | `media_assets.is_placeholder`, `media_assets.label`, `media_assets.description` |

Hasil: **28 tabel domain** + `ci_sessions` + `migrations` (dihapus 4: `research_phases`, `participant_consents`, `scoring_profiles`, `challenge_options`; ditambah 2: `item_tries`, `device_logs`).

Aturan pengerjaan migration:

- Ikuti pola `App\Database\GelitaMigration` (InnoDB, `utf8mb4_unicode_ci`, `BIGINT UNSIGNED`, `DATETIME(6)`).
- **Nama FK dan index dibaca dari `information_schema`**, bukan ditebak. Tambahkan dua helper ke `GelitaMigration`:

  ```php
  /** Hapus FK pada (tabel, kolom) bila ada; nama constraint dibaca dari information_schema. */
  protected function dropForeignKeyOn(string $table, string $column): void;
  /** Hapus index bernama $name bila ada (SHOW INDEX). */
  protected function dropIndexIfExists(string $table, string $name): void;
  ```

  Forge CodeIgniter biasanya menamai FK `{tabel}_{kolom}_foreign` dan index gabungan dengan menyambung nama kolom (`participant_id_study_id_phase_id_status`), tetapi jangan bergantung pada itu.
- Setiap `up()` memeriksa keberadaan kolom/tabel (`fieldExists`, `tableExists`) agar aman bila dijalankan pada database yang sebagian sudah berubah. Panggil `$this->db->resetDataCache()` sebelum pemeriksaan (pelajaran migration `003200`).
- `down()` mengembalikan **struktur** (kolom/tabel dengan default), bukan data.
- Diuji di MariaDB 10.11 dan MySQL 8: `php spark migrate` dari database lama yang berisi data uji coba, lalu `migrate:rollback` sampai `004000`, lalu `migrate` lagi.

### 5.2 `004100_RemovePhasesAndConsent`

`up()`:

1. `game_sessions`
   - hapus FK `phase_id` → `research_phases`;
   - hapus index `phase_id` dan index gabungan `(participant_id, study_id, phase_id, status)`;
   - hapus kolom `phase_id`;
   - tambah index `(participant_id, study_id, status)` — dipakai `GameSessionModel::resumable()`.
2. Hapus tabel `research_phases`.
3. `research_studies`: hapus kolom `active_phase_code`, `allow_phase_choice`, `item_selection_mode`, `require_consent`.
4. Hapus tabel `participant_consents`.
5. `participants`: tambah `assented_at DATETIME(6) NULL` sesudah `intro_seen_at` — waktu siswa mencentang "Aku mau ikut bermain…" di langkah 3 pendaftaran.

`down()`: buat ulang `research_phases` (struktur 01_DATABASE §3) dan `participant_consents` (§6); tambah `game_sessions.phase_id BIGINT UNSIGNED NULL` (+FK `SET NULL`); tambah kolom studi dengan default lama (`umum`, 0, `fixed`, 1); hapus `assented_at`.

### 5.3 `004200_SimplifyAttemptScoring`

`up()`:

1. `challenge_attempts`
   - hapus FK + kolom `scoring_profile_id`;
   - hapus kolom `first_pass_accuracy`, `final_accuracy`, `retry_count`, `independence`, `score`, `scoring_version`;
   - tambah `variant_group VARCHAR(10) NULL` sesudah `attempt_no` — variasi yang dimainkan;
   - tambah `completion_reason VARCHAR(20) NULL` sesudah `status` — `solved` (semua soal benar) | `tries_exhausted` (kesempatan habis); `NULL` selama berjalan atau bila ditinggalkan;
   - tambah `max_tries TINYINT UNSIGNED NOT NULL DEFAULT 3` sesudah `check_count` — salinan aturan saat pengerjaan dibuat, agar data lama tetap terbaca bila aturan berubah.
2. `challenge_nodes`: hapus FK + kolom `scoring_profile_id`.
3. Hapus tabel `scoring_profiles`.
4. `session_progress`: hapus `total_score` (`total_stars` tetap).
5. `item_responses`
   - hapus `reason_text`, `reason_review_status`;
   - tambah `try_count SMALLINT UNSIGNED NOT NULL DEFAULT 0` sesudah `change_count` — jumlah percobaan soal ini;
   - tambah `solved_on_try TINYINT UNSIGNED NULL` sesudah `try_count` — percobaan ke berapa soal ini menjadi benar; `NULL` bila tidak pernah benar.

`game_releases.scoring_version` **tidak dihapus**; isinya kini kode aturan bintang (`bintang-1`).

`down()`: tambah kembali kolom yang dihapus dengan default lama (`0.00`, `100.00`, dst.), buat ulang `scoring_profiles` kosong, hapus kolom baru.

### 5.4 `004300_CreateItemTries`

Satu baris = satu percobaan satu soal. Inilah data mentah utama untuk dimensi *response/correctness*, *attempts*, dan *time*.

| Kolom | Tipe | Null | Default | Keterangan |
|---|---|---|---|---|
| id | BIGINT UNSIGNED | NO | AUTO | PK |
| challenge_attempt_id | BIGINT UNSIGNED | NO | — | FK `challenge_attempts.id` CASCADE |
| challenge_item_id | BIGINT UNSIGNED | NO | — | FK `challenge_items.id` RESTRICT |
| try_no | TINYINT UNSIGNED | NO | 1 | 1..`max_tries` |
| check_no | SMALLINT UNSIGNED | YES | NULL | nomor tekan **Cek jawabanku** pada pengerjaan itu (engine batch); NULL untuk `cari` |
| answer_json | JSON | NO | — | jawaban persis yang dikirim pada percobaan ini (`{text}`, `{verdict}`, `{order}`, atau `{item_id}` hasil terjemahan token objek) |
| is_correct | TINYINT(1) | NO | 0 | dinilai server |
| hint_opened_before | TINYINT(1) | NO | 0 | 1 bila petunjuk sudah pernah dibuka di pengerjaan ini sebelum percobaan ini |
| first_input_ms | INT UNSIGNED | YES | NULL | ms sejak tantangan dibuka sampai siswa pertama kali mengisi/mengubah soal ini (dari klien) |
| last_input_ms | INT UNSIGNED | YES | NULL | ms sejak tantangan dibuka sampai perubahan terakhir sebelum percobaan ini (dari klien) |
| occurred_at | DATETIME(6) | NO | CURRENT | waktu server menerima percobaan |

UNIQUE `(challenge_attempt_id, challenge_item_id, try_no)`; INDEX `(challenge_item_id, is_correct)`.

Model: `App\Models\ItemTryModel` (insert-only; tidak ada update). Entity tidak diperlukan.

### 5.5 `004400_AddVariantGroupsDropOptions`

`up()`:

1. `challenge_items`: tambah `variant_group VARCHAR(10) NULL` sesudah `sequence`; index `(challenge_node_id, variant_group, is_active)`.
2. Hapus tabel `challenge_options` (hanya dipakai `single_choice`/`source_trust` yang dihapus).

`down()`: hapus index + kolom; buat ulang `challenge_options` (struktur 01_DATABASE §15).

Isi `challenge_nodes.config_json` sejak perombakan (tidak perlu migration karena kolom JSON):

```jsonc
{
  "selection": "variant",          // satu-satunya mode: pilih satu variant_group acak, pakai semua soal aktifnya
  "variants": {                     // judul tampil per variasi + pengecoh Isi Rumpang per variasi
    "a": { "title_id": "Gunung Kembar", "title_en": "The Twin Mountains", "distractors": [] },
    "b": { "title_id": "Umbul Jumprit", "title_en": "Umbul Jumprit", "distractors": [] },
    "c": { "title_id": "Kuda Lumping",  "title_en": "Kuda Lumping",  "distractors": [] }
  },
  "as_reading": true,              // rumpang
  "use_word_bank": true,           // rumpang (selalu true)
  "distractor_count": 0,           // rumpang: jumlah pengecoh yang dipakai dari daftar variasi
  "verdict_options": ["benar", "salah"]   // boleh (selalu dua ini)
}
```

Kunci lama `items_per_round`, `allow_retry`, `require_reason`, `shuffle_options`, `show_decoys`, `distractors` (tingkat node) tidak dibaca lagi; `ContentVerifier` memberi peringatan bila masih ada.

### 5.6 `004500_CreateDeviceLogs`

Satu baris setiap siswa **mendaftar atau masuk** (termasuk masuk yang melanjutkan sesi lama). Isinya hasil `App\Libraries\DeviceProfile` (§11).

| Kolom | Tipe | Null | Keterangan |
|---|---|---|---|
| id | BIGINT UNSIGNED | NO | PK |
| session_id | BIGINT UNSIGNED | NO | FK `game_sessions.id` CASCADE |
| participant_id | BIGINT UNSIGNED | NO | INDEX, tanpa FK (pola `game_event_logs`) |
| via | VARCHAR(20) | NO | `register` \| `login` \| `resume` |
| user_agent | VARCHAR(512) | YES | User-Agent mentah (untuk diurai ulang peneliti) |
| device_type | VARCHAR(30) | YES | dari library: `desktop`, `smartphone`, `tablet`, `phablet`, `tv`, `console`, `car browser`, `smart display`, `portable media player`, `wearable`, `peripheral`, … |
| device_brand | VARCHAR(60) | YES | mis. `Samsung`, `Apple`, `Lenovo` |
| device_model | VARCHAR(100) | YES | mis. `Galaxy Tab A7`, `iPad` |
| os_name | VARCHAR(60) | YES | `Android`, `Windows`, `iPadOS`, `Chrome OS`, … |
| os_version | VARCHAR(40) | YES | |
| os_platform | VARCHAR(20) | YES | `x64`, `ARM`, … |
| browser_name | VARCHAR(60) | YES | |
| browser_version | VARCHAR(40) | YES | |
| browser_engine | VARCHAR(40) | YES | `Blink`, `WebKit`, `Gecko` |
| is_bot | TINYINT(1) | NO DEFAULT 0 | |
| client_hints_json | JSON | YES | nilai Client Hints mentah (header `Sec-CH-UA-*` dan/atau `navigator.userAgentData`) |
| screen_w, screen_h | SMALLINT UNSIGNED | YES | `screen.width/height` (px CSS) |
| viewport_w, viewport_h | SMALLINT UNSIGNED | YES | `innerWidth/innerHeight` saat formulir dikirim |
| pixel_ratio | DECIMAL(4,2) | YES | `devicePixelRatio` |
| touch_points | TINYINT UNSIGNED | YES | `navigator.maxTouchPoints` |
| orientation | VARCHAR(20) | YES | `landscape-primary`, … |
| is_standalone | TINYINT(1) | YES | 1 bila dibuka sebagai aplikasi terpasang (PWA) |
| language | VARCHAR(20) | YES | `navigator.language` |
| timezone | VARCHAR(60) | YES | `Intl…timeZone` |
| corrected | VARCHAR(40) | YES | koreksi yang diterapkan, mis. `ipad_desktop_ua` |
| detector | VARCHAR(40) | YES | mis. `matomo/device-detector 6.4.x` |
| ip_hash | CHAR(64) | YES | SHA-256 IP + `gelita.ipSalt` (IP mentah tidak pernah disimpan) |
| created_at | DATETIME(6) | NO | CURRENT |

INDEX `(session_id)`, `(participant_id, created_at)`.

Kolom ringkas di `game_sessions` (`device_type`, `os_name`, `browser_name`, `screen_size`, `is_touch`, `app_client_version`) **tetap** dan diisi dari baris `device_logs` pertama sesi itu.

### 5.7 `004600_AddMediaPlaceholderFlag`

`media_assets`:

- `is_placeholder TINYINT(1) NOT NULL DEFAULT 0` sesudah `is_active` (+ index) — 1 = berkas adalah gambar contoh (mockup) buatan sistem, belum gambar asli.
- `label VARCHAR(120) NULL` sesudah `credit` — nama pendek benda/gambar (mis. "Kuda kepang bambu"), dari `media.label` bank soal; dipakai judul kartu di halaman Media dan tulisan di mockup.
- `description VARCHAR(500) NULL` sesudah `label` — isi gambar yang diharapkan (dari kolom `note` bank soal), tampil di halaman Media agar admin tahu gambar apa yang harus diunggah.

`MediaStore::store()` (unggahan admin) selalu menulis `is_placeholder = 0`.

### 5.8 Model, entity, dan aturan validasi yang ikut berubah

| Berkas | Perubahan |
|---|---|
| `Entities/GameSession.php`, `Models/GameSessionModel.php` | buang `phase_id`; `resumable(int $participantId, int $studyId)` |
| `Models/ResearchStudyModel.php` | buang kolom fase/consent/selection dari `allowedFields` & validasi |
| `Models/ParticipantModel.php` | `gender` → `permit_empty\|in_list[laki-laki,perempuan]`; `assented_at` di `allowedFields` |
| `Entities/ChallengeAttempt.php`, `Models/ChallengeAttemptModel.php` | buang kolom skor; tambah `variant_group`, `completion_reason`, `max_tries`; method `triesLeft()`, `isExhausted()` |
| `Entities/ItemResponse.php`, `Models/ItemResponseModel.php` | buang `reason_*`; tambah `try_count`, `solved_on_try`; `countCorrect()` tetap |
| `Entities/ChallengeItem.php`, `Models/ChallengeItemModel.php` | tambah `variant_group`; buang `loadedOptions()`, `correctOptionKey()`, `pieces`, `sources`, `acceptedAnswers()`; `bankForNode()` mengurutkan `variant_group, sequence` |
| `Entities/ChallengeNode.php` | `variants()`, `variantTitle($group, $locale)`, `distractors($locale, $group)`; buang `allowsRetry()` (selalu true), `requiresReason()`, `itemsPerRound()` |
| `Models/SessionProgressModel.php` | buang `total_score` |
| `Models/MediaAssetModel.php` | `is_placeholder`, `label`, `description`; `placeholders()` |
| Baru: `Models/ItemTryModel.php`, `Models/DeviceLogModel.php` | — |
| Hapus: `ResearchPhaseModel`, `ParticipantConsentModel`, `ScoringProfileModel`, `ChallengeOptionModel`, `Entities/ChallengeOption.php` | — |

---

## 6. Konfigurasi `Config\Gelita`

```php
/** Engine tantangan (urutan = urutan node di setiap wilayah) */
public array $engineTypes = ['puzzle', 'rumpang', 'boleh', 'cari'];

/** Jenis interaksi soal yang dikenali sistem */
public array $interactionTypes = ['puzzle_arrange', 'fill_blank_bank', 'verdict_card', 'find_object'];

/** Batas percobaan per soal; lewat dari ini jawaban disimpan apa adanya (Dok 2 §5) */
public int $maxTries = 3;

/** Nilai jenis kelamin yang diterima pendaftaran */
public array $genders = ['laki-laki', 'perempuan'];

/** Rentang umur pendaftaran siswa kelas 5 (inklusif) */
public array $registrationAge = [8, 15];

/** Client Hints yang diminta lewat header Accept-CH (§11) */
public array $clientHints = [
    'Sec-CH-UA-Model', 'Sec-CH-UA-Platform-Version', 'Sec-CH-UA-Full-Version-List',
    'Sec-CH-UA-Arch', 'Sec-CH-UA-Bitness',
];
```

- **Hapus** `$phases`.
- `$eventTypes` diganti daftar lengkap §12.3.
- `$assetSizes`: ganti pola tantangan menjadi `'challenge.*.scene' => [1280, 720]` (adegan cari), `'challenge.item.*-1-*' => [1200, 1200]` (puzzle, minimal), `'challenge.item.*-4-*' => [240, 240]` (benda cari), `'challenge.item.*-3-*' => [480, 360]` (gambar pernyataan) — dicocokkan `fnmatch()` seperti sekarang; dipakai validasi unggah, halaman Media, dan generator mockup (Dok 2 §10). Pola lama `challenge.scene`, `challenge.card`, `challenge.option` dihapus.
- `js_config()` (`app/Helpers/gelita_helper.php`) menambah `maxTries` dan `stage` (`{w: 1280, h: 720}`, Dok 3).

---

## 7. Perintah reset total `gelita:reset`

### 7.1 Tujuan

Mengosongkan **semua data uji coba dan pengaturan** lalu menyiapkan GELITA bersih untuk penelitian kelas 5, dalam satu perintah yang aman diulang. Pustaka, cerita (termasuk Kenali wilayah), level, media, audio, direktori sekolah resmi, dan akun staf tidak ikut terhapus.

Berkas:

- `app/Services/SystemResetService.php` — logika (dapat diuji);
- `app/Commands/SystemReset.php` — command `gelita:reset` (grup `GELITA`).

### 7.2 Apa yang dilakukan pada setiap tabel

| Kelompok | Tabel | Tindakan |
|---|---|---|
| **A. Data penelitian & uji coba** | `participants`, `game_sessions`, `session_progress`, `challenge_attempts`, `item_responses`, `item_tries`, `audio_usage_events`, `game_event_logs`, `device_logs`, `participant_feedback`, `data_exports`, `data_deletion_requests`, `audit_logs`, `ci_sessions` | **dikosongkan** (semua siswa dan staf ter-logout). Berkas `writable/exports/*` ikut dihapus. |
| **B. Konten tantangan** | `hints`, `challenge_items`, `reading_passages`, `challenge_nodes` | dikosongkan, lalu **dibangun ulang**: 12 node dari `ContentSeeder`, isi dari workbook bank soal kelas 5 (Dok 2) |
| **C. Pengaturan** | `research_studies`, `game_releases`, `learning_indicators` | dikosongkan, lalu diisi **nilai bawaan** seeder (§8) |
| **D. Dibersihkan sebagian** | `schools` | hapus baris `is_verified = 0` (nama sekolah ketikan siswa uji coba dan aliasnya). Sebelumnya `staff_users.school_id` yang menunjuk baris itu di-`NULL`-kan. Direktori resmi (`is_verified = 1`) tetap. |
| | `staff_users` | akun **tetap**; `failed_login_count = 0`, `locked_until = NULL`. Opsi `--hapus-guru` menghapus semua akun `guru`. Akun `admin` tidak pernah dihapus. |
| **E. Tidak disentuh** | `levels`, `dialogues`, `library_pages`, `library_media`, `media_assets`, `audio_assets`, `migrations`, semua berkas di `public/assets/` | — (kecuali opsi `--bersihkan-media-tantangan`, §7.4) |

Urutan pengosongan mengikuti FK (anak dulu): `item_tries` → `item_responses` → `challenge_attempts` → `audio_usage_events` → `game_event_logs` → `device_logs` → `session_progress` → `participant_feedback` → `game_sessions` → `participants` → `data_exports` → `data_deletion_requests` → `audit_logs` → `hints` → `challenge_items` → `reading_passages` → `challenge_nodes` → `research_studies` → `game_releases` → `learning_indicators` → `ci_sessions`.

### 7.3 Langkah

1. **Pra-pemeriksaan.**
   - Koneksi database dan migration terakhir harus ≥ `004600` (bila belum: berhenti, "Jalankan `php spark migrate` dulu").
   - Tolak bila ada `game_sessions` berstatus `active` dengan `last_active_at` dalam 10 menit terakhir (pola `deploy.sh`), kecuali `--paksa`.
   - Hitung baris setiap tabel kelompok A–D dan tampilkan tabelnya.
2. **Konfirmasi.** Tanpa `--ya`, pengguna wajib mengetik persis `RESET-KELAS5`. Tampilkan peringatan: "Data ini tidak dapat dikembalikan. Pastikan backup sudah dibuat (`deploy/linux/backup.sh` atau `deploy/windows/backup.ps1`)."
3. **Mode pemeliharaan** (opsi `--pemeliharaan`): buat `writable/pemeliharaan.flag` selama reset, hapus di akhir walau gagal (Nginx membalas 503 selama berkas ada).
4. **Kosongkan** kelompok A–C. Dengan `SET FOREIGN_KEY_CHECKS = 0`, pakai `TRUNCATE` agar `AUTO_INCREMENT` kembali ke 1 (kode siswa mulai lagi dari `GLT-000001`). Bila akun database tidak punya hak `DROP` (akun aplikasi `gelita_app` hanya DML), **jatuh ke `DELETE`** dengan urutan FK di atas, lalu cetak peringatan bahwa nomor id dan kode siswa melanjutkan nomor lama. Di SQLite (uji), pakai `$db->table($t)->truncate()`.
5. **Bersihkan sebagian** kelompok D.
6. **Isi ulang bawaan** (§8): `LearningIndicatorSeeder`, `GameReleaseSeeder`, `ResearchStudySeeder`, `ContentSeeder` (membuat 12 node; dialog & Pustaka tidak disentuh karena `insertIfMissing`).
7. **Impor bank soal kelas 5** dari `docs/bank-soal/gelita-bank-soal-kelas5.xlsx` (Dok 2 §7) lewat `ContentImportService::import()`, kecuali `--tanpa-bank`. Lokasi lain: `--bank=jalur.xlsx`. Bila berkas tidak ada (sebelum Dok 2 selesai): di `development` jalankan `SampleItemSeeder`, di lingkungan lain lewati dengan peringatan.
8. **Gambar mockup** untuk slot gambar tantangan yang masih kosong (`MockupGenerator`, Dok 2 §10), kecuali `--tanpa-mockup`.
9. **Bersihkan cache**: `cache()->clean()`, `service('contentRepository')->flush()`.
10. **Verifikasi** dengan `ContentVerifier` (sama dengan `gelita:content:verify`). Galat → kode keluar 1 dengan pesan, tetapi data yang sudah dikosongkan tidak dapat kembali; pesan menyebut langkah perbaikan (impor ulang bank soal lalu `gelita:content:verify`).
11. **Audit**: tulis satu baris `audit_logs` `action = system_reset`, `staff_user_id = NULL`, `metadata_json` = jumlah baris per tabel yang dikosongkan, opsi yang dipakai, nama host, dan sha256 workbook.
12. **Ringkasan** di layar (contoh di §7.5).

### 7.4 Opsi

| Opsi | Arti |
|---|---|
| `--dry-run` | hanya hitung dan tampilkan rencana; tidak menulis apa pun |
| `--ya` | lewati ketik konfirmasi (untuk skrip rilis) |
| `--paksa` | abaikan penjaga "ada kelas aktif dalam 10 menit" |
| `--pemeliharaan` | aktifkan mode pemeliharaan selama reset |
| `--tanpa-bank` | jangan impor bank soal (12 node kosong) |
| `--bank=JALUR` | workbook bank soal lain |
| `--tanpa-mockup` | jangan buat gambar mockup |
| `--hapus-guru` | hapus juga semua akun guru (admin tetap) |
| `--bersihkan-media-tantangan` | setelah impor, hapus baris `media_assets` (beserta berkasnya di `public/assets/uploads/`, termasuk `uploads/mockup/`) yang `asset_key`-nya berawalan `challenge.` dan **tidak dipakai** soal/node mana pun. Tampilkan daftarnya dulu; tetap minta konfirmasi kecuali `--ya`. |

### 7.5 Cara menjalankan

Pengembangan (akun database lokal punya hak penuh):

```bash
php spark gelita:reset --dry-run
php spark gelita:reset
```

Server produksi — jalankan dengan akun `gelita_migrate` lewat DSN, seperti migration di `deploy.sh`, agar `TRUNCATE` diizinkan:

```bash
export database_default_DSN=$(cat /home/deploy/.gelita/migrate.dsn)
sudo --preserve-env=database_default_DSN -u www-data php /var/www/gelita/spark gelita:reset --pemeliharaan
unset database_default_DSN
```

PowerShell (Laragon):

```powershell
$env:database_default_DSN = Get-Content "$HOME\.gelita\migrate.dsn"
php spark gelita:reset --pemeliharaan
Remove-Item Env:\database_default_DSN
```

Contoh keluaran:

```text
GELITA · Reset total untuk penelitian kelas 5
Database: gelita @ localhost (MariaDB 10.11) · migration terakhir 2026-01-01-004600

Akan DIKOSONGKAN                       baris
  participants                            37
  game_sessions                           52
  challenge_attempts                     411
  game_event_logs                     18 230
  … (14 tabel)
Akan DIBANGUN ULANG: 15 node → 12 node, 152 soal → 126 soal
Akan DIHAPUS: 9 sekolah belum terverifikasi
TETAP: Pustaka 30 halaman / 48 media · cerita 88 baris · 3 wilayah · 211 media · 4 akun staf

Data ini tidak dapat dikembalikan. Sudah membuat backup?
Ketik RESET-KELAS5 untuk melanjutkan: RESET-KELAS5

✓ Dikosongkan (TRUNCATE) · ✓ Pengaturan bawaan · ✓ 12 node
✓ Bank soal kelas 5: 126 soal (108 dinilai + 18 pengecoh), 30 variasi, 9 bacaan · ✓ Mockup: 45 gambar contoh
✓ Verifikasi konten: 0 galat, 1 peringatan (45 gambar masih mockup)
Selesai dalam 6,4 detik. Kode siswa berikutnya: GLT-000001
```

### 7.6 Uji

`tests/unit/SystemResetTest.php` (SQLite, tabel minimal di `tests/_support/Database/ResetTables.php`):

- `--dry-run` tidak mengubah jumlah baris apa pun;
- tanpa konfirmasi → berhenti dengan kode 1;
- tabel kelompok A kosong; `levels`, `dialogues`, `library_pages`, `library_media`, `media_assets` sama persis (bandingkan hash isi) sebelum dan sesudah;
- sekolah terverifikasi tetap, yang belum terverifikasi hilang, `staff_users.school_id` yang menunjuk ke sana menjadi NULL;
- akun admin tetap; `--hapus-guru` menghapus guru saja;
- setelah reset dengan workbook uji kecil: 12 node aktif, `research_studies` satu baris berstatus `active`;
- satu baris `audit_logs` `system_reset`.

---

## 8. Seeder

| Seeder | Perubahan |
|---|---|
| `StaffUserSeeder` | tetap |
| `LearningIndicatorSeeder` | tetap (`literasi`, `budaya`, `sikap`) |
| `ScoringProfileSeeder` | **dihapus** (dan dari `DatabaseSeeder`) |
| `GameReleaseSeeder` | `release_code = GELITA-KELAS5-2026`, `app_version = 2.0.0`, `content_version = kelas5-1`, `asset_version = 1`, `scoring_version = bintang-1` |
| `ResearchStudySeeder` | satu studi: `code = STUDI-KEDU-KELAS5-2026`, `name = Penelitian GELITA Kelas 5 SD/MI 2026`, `status = active`, `retention_days = 1825`, `default_locale = id`, `unlock_mode = sequential`. Tidak ada fase. |
| `LevelSeeder` | tetap (hanya `insertIfMissing`) |
| `MediaAssetSeeder` | tetap |
| `ContentSeeder` | **4 node per wilayah** (tabel di bawah). Dialog dan Pustaka tetap `insertIfMissing`, jadi tidak menimpa apa pun. Judul, instruksi, deskripsi, dan konfigurasi akhir datang dari workbook bank soal (Dok 2 §9); seeder hanya membuat struktur dengan judul sementara. |
| `SampleItemSeeder` | **diubah** ke struktur baru: 12 node, satu variasi `a` berisi 2 soal contoh per node (kode berakhiran `-a91`, `-a92`), agar alur dapat dimainkan di mesin pengembangan sebelum Dok 2 selesai. Seeder ini **dihapus di Dok 2** (§7.1) setelah workbook kelas 5 tersedia; sejak itu `DatabaseSeeder` di `CI_ENVIRONMENT = development` mengimpor `docs/bank-soal/gelita-bank-soal-kelas5.xlsx`. |

`ContentSeeder` — struktur node baru:

| seq | engine | variant_code | indikator | posisi peta (`map_x`, `map_y` %) |
|---:|---|---|---|---|
| 1 | `puzzle` | `susun_gambar` | budaya | 18, 70 |
| 2 | `rumpang` | `rumpang_bacaan` | literasi | 40, 46 |
| 3 | `boleh` | `benar_salah_bacaan` | literasi | 62, 68 |
| 4 | `cari` | `cari_objek` | budaya | 82, 44 |

`config_json` bawaan per engine: lihat Dok 2 §2 (aturan per tingkat dibaca dari `levels.difficulty` lewat `DifficultyRules`). Posisi pin dapat diubah admin di editor wilayah; reset mengembalikannya ke nilai di atas.

---

## 9. Satu tahap bermain dan pemilihan soal acak

### 9.1 Fase dihapus dari alur

| Tempat | Perubahan |
|---|---|
| `SessionService::registerAndStart()` | tanpa `phase_code`, tanpa cek `require_consent`, tanpa `participant_consents`; menulis `participants.assented_at`; menulis `device_logs` (`via = register`) |
| `SessionService::login()` | signature `login(string $username, string $password, string $locale, array $device)`; `resumable()` per (peserta, studi); `device_logs` (`via = resume` bila melanjutkan sesi, `login` bila sesi baru) |
| `SessionService::startNewSession()`, `insertSession()` | tanpa `phase_id`; perangkat dari `DeviceProfile` |
| `SessionService::resolvePhaseCode()` | dihapus |
| `Controllers/Game/LoginController.php`, `game/login.php` | pilihan fase dihapus |
| `Controllers/Admin/StudyController.php`, `admin/study/index.php` | form studi hanya: kode, nama, deskripsi, tahun ajaran, status, retensi (hari), bahasa bawaan, mode buka wilayah. Bagian fase, consent, dan mode pemilihan soal dihapus |
| `Controllers/Concerns/StaffScope.php`, `admin-filter-bar`, `admin.js`, `admin/filters.js` | filter `phase_code` dihapus |
| `layouts/admin.php` | teks "· tahap …" di kepala panel dihapus |
| `ExportService`, `ExportLabels`, `RetentionService` (`normalizeScope`, `sessionIds`), `GovernanceController` | kolom/filter `phase_code` dihapus |
| `Language/*/Game.php`, `Admin.php` | kunci `phase*`, `consent*` dihapus |

### 9.2 Pemilihan soal: satu variasi acak per pengerjaan

Di `ChallengeService::openNode()` (rinciannya Dok 2 §3):

1. Kelompokkan soal aktif node menurut `variant_group`.
2. Bila siswa ini pernah mengerjakan node itu, buang variasi yang sudah pernah ia dapat (`challenge_attempts.variant_group` pada semua sesinya). Bila semua sudah pernah, pakai semua variasi.
3. Pilih satu dengan `random_int()` — **acak sungguhan, tidak berbenih** (mode `fixed` dihapus bersama pretest/posttest).
4. Tulis `challenge_attempts.variant_group`; semua soal aktif variasi itu masuk `selected_item_ids_json` (urutan `sequence`).
5. Muat ulang halaman memakai pengerjaan yang sama (resume), jadi variasi tidak berubah di tengah jalan.

`ChallengeItemModel::pickForAttempt()` diganti `pickVariant(int $nodeId, array $excludeGroups): array{group: string, items: list<ChallengeItem>}`. `seeded_shuffle()` tetap dipakai untuk urutan yang harus stabil saat muat ulang (bank kata, urutan petunjuk `cari`, acakan keping puzzle), dengan benih id pengerjaan.

---

## 10. Pendaftaran bertahap tiga langkah

### 10.1 Alur

```
/mulai → "Saya baru" → /daftar/1 Tentang kamu → /daftar/2 Sekolah dan daerah → /daftar/3 Akun rahasiamu → (akun dibuat) → /intro
```

- `GET /persetujuan` → `301` ke `/daftar/1`; `POST /persetujuan` dihapus. View `game/consent.php` dihapus.
- `GET /daftar` → `302` ke langkah pertama yang belum lengkap.
- `GET /daftar/{1|2|3}` → `RegisterController::step($n)`; `POST /daftar/{1|2|3}` → `RegisterController::saveStep($n)`.
- Isian langkah 1–2 disimpan di session PHP `reg_wizard` (`['1' => [...], '2' => [...]]`). **Kata sandi tidak pernah disimpan** di session, flash, `old()`, log, atau event.
- Membuka langkah `n` sebelum langkah sebelumnya lengkap → dialihkan ke langkah pertama yang belum lengkap.
- Tombol **Kembali** di langkah 2 dan 3 adalah tautan `GET` ke langkah sebelumnya; isian tetap terisi dari session.
- Langkah 3 memvalidasi ulang isian langkah 1–2 dari session sebelum membuat akun (pertahanan bila session diubah).
- Session wizard kedaluwarsa bersama session PHP (4 jam); tidak ada data peserta yang ditulis ke database sebelum langkah 3 berhasil.

### 10.2 Isian dan aturan per langkah

**Langkah 1 — Tentang kamu**

| Isian | Aturan | Catatan UI |
|---|---|---|
| Nama lengkap `display_name` | wajib, 2–150 | "Tulis nama lengkapmu seperti di daftar kelas." |
| Umur `age` | wajib, bilangan bulat `8`–`15` (`Config\Gelita::$registrationAge`) | input angka besar dengan satuan "tahun" |
| Jenis kelamin `gender` | wajib, `laki-laki` \| `perempuan` | dua tombol pilihan besar; **tidak ada "Lainnya"** |
| Kelas | tidak diisi; server memaksa `5` | teks tetap "Kelas 5 SD/MI" dengan ikon kunci kecil |

**Langkah 2 — Sekolah dan daerah**

Sama dengan formulir sekarang: negara (Indonesia / negara lain), provinsi, kabupaten/kota, lalu NPSN (provinsi berdirektori, kartu "Benar ini sekolahmu?") atau nama sekolah. Aturan `known_npsn` dan `allowed_school_stage` tetap.

**Langkah 3 — Akun rahasiamu**

| Isian | Aturan |
|---|---|
| Nama pengguna `username` | sama seperti sekarang (`valid_username`, `not_reserved_username`, unik) + cek ketersediaan langsung |
| Kata sandi + ulangi | sama seperti sekarang (`strong_password[username]`, `matches`) + pengukur kekuatan; tombol Daftar **tidak** dinonaktifkan (metrik `pw_weak_submit_count` tetap tercatat) |
| Persetujuan siswa `assent` | wajib dicentang: **"Aku mau ikut bermain GELITA. Aku tahu jawaban dan langkahku di dalam permainan dicatat untuk penelitian."** (EN: *"I want to play GELITA. I know my answers and steps in the game are recorded for research."*) |
| tersembunyi | `locale`, `device_json` (§11.3) |

Metrik literasi keamanan digital tetap dihitung di POST langkah 3: `reg_first_criteria` disimpan pada kiriman pertama langkah 3, `reg_weak_count` bertambah setiap kiriman dengan sandi belum kuat.

Tidak ada lagi: kotak izin orang tua/wali, nama orang tua/wali, teks persetujuan panjang, versi teks persetujuan.

### 10.3 Tampilan (rincian tata letak di Dok 3 §4)

- Satu panel di tengah panggung 16:9 dengan **stepper** di atas: `① Tentang kamu — ② Sekolah — ③ Akun`. Langkah aktif bersorot; langkah selesai bertanda ✓ dan dapat diklik untuk kembali.
- Tombol **Lanjut** (langkah 1–2) dan **Daftar** (langkah 3) di kanan bawah panel; **Kembali** di kiri bawah.
- Galat validasi tampil di bawah isian masing-masing dan di ringkasan atas panel; fokus pindah ke isian galat pertama.
- Jaka (`why-strong-jaka`) di kotak "Mengapa kata sandi harus kuat?" **tetap boleh** di langkah 3 (yang dihapus hanya Jaka di layar tantangan dan layar selesai).

### 10.4 Data dan tampilan admin

- `participants.assented_at` diisi waktu server saat akun dibuat.
- Detail siswa di admin: baris "Setuju ikut bermain: ya · {tanggal}". Bagian "Persetujuan" lama (versi naskah, izin wali, nama wali, dicabut) dihapus.
- Ekspor lembar Peserta: kolom `assented_at` ("Setuju ikut bermain"); kolom `consent_*`/`guardian_*` dihapus (Dok 3 §9).

### 10.5 Berkas

| Berkas | Perubahan |
|---|---|
| `app/Config/Routes.php` | rute §10.1 |
| `app/Controllers/Game/RegisterController.php` | `step()`, `saveStep()`, `rulesFor($n)`, `wizard()` (baca/tulis session), hapus `consent()`, `storeConsent()`, `consentText()`, `CONSENT_VERSION` |
| `app/Views/game/register.php` | dipecah: `register/step-1.php`, `step-2.php`, `step-3.php` + `register/_stepper.php` |
| `public/assets/js/game/register.js` | dipecah per langkah (filter wilayah, NPSN — langkah 2; cek nama pengguna, pengukur sandi, `device_json` — langkah 3) |
| `app/Language/{id,en}/Game.php` | kunci baru `regStepN*`, `assentLabel`, `next`, `back`; kunci `consent*`, `gender_lainnya`, `phase*` dihapus |
| `tests/unit/RegisterWizardTest.php` (baru) | urutan langkah, penjaga lompat langkah, sandi tidak pernah di session, gender 2, umur 8–15, centang wajib, metrik sandi |

---

## 11. Pelacakan perangkat

### 11.1 Library

```bash
composer require matomo/device-detector:^6.4
```

- **Matomo Device Detector** mengurai User-Agent dan Client Hints menjadi jenis perangkat (desktop, smartphone, tablet, phablet, TV, …), merek, model, sistem operasi + versi, peramban + versi + mesin, dan penanda bot. Diperbarui rutin oleh tim Matomo. Lisensi **LGPL-3.0-or-later** — boleh dipakai sebagai library oleh proyek berlisensi tertutup.
- Tambahkan alasannya ke `composer.json` → `extra.gelita-dependency-notes`: `"matomo/device-detector": "pelacakan perangkat siswa yang tepat (jenis, merek, model, OS, peramban) dari User-Agent + Client Hints untuk data penelitian"`.
- Ketergantungannya (`mustangostang/spyc`) ikut terpasang; tidak ada akses jaringan saat runtime.

### 11.2 `App\Libraries\DeviceProfile`

```php
final class DeviceProfile
{
    /**
     * @param array<string, mixed> $client hasil decode `device_json` dari formulir (boleh kosong)
     * @return array<string, mixed> baris siap ditulis ke device_logs (tanpa session_id/participant_id/via)
     */
    public static function capture(IncomingRequest $request, array $client): array;
}
```

Langkah:

1. `DeviceDetector($userAgent, ClientHints::factory($_SERVER))` → `parse()`; ambil `getDeviceName()`, `getBrandName()`, `getModel()`, `getOs()`, `getClient()`, `isBot()`.
2. Bila klien mengirim `uaData` (dari `navigator.userAgentData.getHighEntropyValues`), pakai untuk melengkapi model, versi platform, dan arsitektur yang kosong.
3. **Koreksi iPad:** iPadOS 13+ Safari mengaku "Macintosh". Bila `os = Mac` **dan** `touch_points > 1` → `device_type = tablet`, `device_brand = Apple`, `device_model = iPad`, `os_name = iPadOS`, `corrected = ipad_desktop_ua`.
4. Isi angka layar dari `$client` (`screen_w/h`, `viewport_w/h`, `pixel_ratio`, `touch_points`, `orientation`, `is_standalone`, `language`, `timezone`), dipangkas ke batas kolom; nilai yang bukan angka/teks wajar dibuang.
5. `ip_hash` = `hash('sha256', ip . config('Gelita')->ipSalt)` (helper yang sudah ada).
6. `detector` = nama paket + versi dari `Composer\InstalledVersions`.

Ringkasan untuk `game_sessions` (sesi baru): `device_type`, `os_name`, `browser_name`, `screen_size = "{screen_w}x{screen_h}"`, `is_touch = touch_points > 0`.

### 11.3 Sinyal dari browser

Modul baru `public/assets/js/core/device.js`:

```js
export async function deviceSignals() {
  const ua = navigator.userAgentData;
  let uaData = null;
  try {
    uaData = ua ? await ua.getHighEntropyValues(
      ['platform', 'platformVersion', 'model', 'architecture', 'bitness', 'fullVersionList', 'mobile']) : null;
  } catch { /* tidak didukung: biarkan null */ }
  return {
    screen_w: screen.width, screen_h: screen.height,
    viewport_w: innerWidth, viewport_h: innerHeight,
    pixel_ratio: Math.round((devicePixelRatio || 1) * 100) / 100,
    touch_points: navigator.maxTouchPoints || 0,
    orientation: screen.orientation?.type || null,
    is_standalone: matchMedia('(display-mode: standalone)').matches || navigator.standalone === true,
    language: navigator.language || null,
    timezone: Intl.DateTimeFormat().resolvedOptions().timeZone || null,
    ua_data: uaData,
  };
}
```

- Formulir **masuk** (`/masuk`) dan **langkah 3 pendaftaran** punya `<input type="hidden" name="device_json">` yang diisi saat `submit` (JSON, maks 4 KB; server menolak diam-diam bila lebih atau rusak, lalu memakai UA saja).
- Tanpa JavaScript: `device_json` kosong, `DeviceProfile` tetap mengurai User-Agent dan Client Hints header.
- Input lama `screen_size`/`is_touch` di formulir dihapus (digantikan `device_json`).

### 11.4 Client Hints dari header

Filter baru `App\Filters\ClientHintsFilter` (after) pada `GET /`, `/mulai`, `/masuk`, `/daftar/*`: tambahkan header

```
Accept-CH: Sec-CH-UA-Model, Sec-CH-UA-Platform-Version, Sec-CH-UA-Full-Version-List, Sec-CH-UA-Arch, Sec-CH-UA-Bitness
```

Peramban berbasis Chromium lalu mengirim nilai tinggi itu pada permintaan berikutnya (termasuk `POST /masuk` dan `POST /daftar/3`). Peramban lain mengabaikannya tanpa efek samping. Daftar header dibaca dari `Config\Gelita::$clientHints`.

### 11.5 Perubahan tampilan layar selama bermain

Event klien `viewport_changed` (§12.3) dikirim saat halaman game dibuka dan setiap kali ukuran/orientasi berubah (debounce 1 detik, paling sering satu event per 5 detik), termasuk saat layar "Putar perangkatmu" muncul atau hilang. Di dokumen ini event dipasang di `core/device.js` dengan `w`, `h`, `orientation`, `rotate_gate`; Dok 3 §7 menambahkan `stage_w`, `stage_h`, `scale` setelah panggung 16:9 ada. Dengan begitu peneliti tahu ukuran tampilan yang benar-benar dilihat siswa.

### 11.6 Privasi

- IP mentah tidak pernah disimpan; hanya `ip_hash`.
- Tidak ada sidik jari peramban (canvas, font, audio).
- Data perangkat tidak dianggap identitas: tetap ada di ekspor anonim dan ekspor guru.

### 11.7 Uji

`tests/unit/DeviceProfileTest.php` dengan contoh User-Agent nyata: Windows 11 Chrome, Android tablet Samsung Chrome, iPhone Safari, iPad Safari ber-UA Macintosh + `touch_points = 5` (harus menjadi tablet/iPadOS), Chromebook, dan bot. Plus: `device_json` rusak/terlalu besar diabaikan; panjang kolom dipangkas.

---

## 12. Data mentah: tujuh dimensi analitik

Semua data di bawah ditulis apa adanya oleh sistem. **Tidak ada skor, persentase, indeks, atau peringkat yang dihitung** untuk peneliti — pengolahan dilakukan peneliti dari ekspor (Dok 3 §9).

Dikerjakan di dokumen ini: tabel dan kolom (§5), model, `EventService` + daftar event, `ScreenViewFilter`, penulisan `item_tries`/`try_count`/`solved_on_try` di jalur cek yang sekarang, dan event klien (`answer_selected`, `reading_progress`, `overlay_opened`, `page_hidden`/`page_visible`, `viewport_changed`) pada engine yang ada. Batas 3 kesempatan dan penutupan otomatis ada di Dok 2 §5; engine yang ditulis ulang di Dok 2 tetap memancarkan event yang sama.

### 12.1 Peta dimensi → sumber data

| Dimensi | Pertanyaan | Tabel & kolom | Event |
|---|---|---|---|
| **Response / correctness** | Apakah jawaban siswa benar atau salah? | `item_tries` (`answer_json`, `is_correct` setiap percobaan); `item_responses` (`first_answer_json`, `final_answer_json`, `first_pass_correct`, `is_correct`, `solved_on_try`); `challenge_attempts` (`first_pass_correct`, `final_correct`, `scorable_items`) | `challenge_checked`, `answer_submitted`, `answer_changed`, `wrong_target_clicked`, `answer_selected` |
| **Attempts** | Berapa kali siswa mencoba? | `item_tries.try_no`; `item_responses.try_count`, `change_count`, `wrong_click_count`; `challenge_attempts` (`attempt_no`, `check_count`, `max_tries`, `completion_reason`) | `challenge_checked` (`try_no`), `challenge_tries_exhausted`, `clue_failed` |
| **Time** | Berapa lama menyelesaikan soal/tantangan? | `item_tries` (`occurred_at`, `first_input_ms`, `last_input_ms`); `item_responses` (`started_at`, `answered_at`, `duration_ms`); `challenge_attempts` (`started_at`, `completed_at`, `duration_ms`); `game_sessions` (`started_at`, `last_active_at`, `ended_at`, `duration_ms`) | semua event ber-`occurred_at` (milidetik); `page_hidden`/`page_visible` |
| **Progress** | Sudah sampai wilayah/tantangan mana? | `session_progress` (`current_level_id`, `current_node_id`, `unlocked_level_sequence`, `completed_nodes`, `completed_levels`, `total_stars`) | `level_opened`, `challenge_opened`, `level_completed` |
| **Completion** | Apakah tantangan/wilayah/permainan selesai? | `challenge_attempts` (`status`, `completion_reason`, `completed_at`); `game_sessions.status`; `participants.intro_seen_at` | `challenge_completed`, `challenge_abandoned`, `level_completed`, `session_completed`, `intro_completed`, `feedback_submitted` |
| **Navigation** | Bagaimana siswa bergerak di dalam game? | — | `screen_viewed`, `overlay_opened`, `dialogue_advanced`, `library_opened`, `library_page_viewed`, `locale_changed`, `page_hidden`, `page_visible`, `viewport_changed`, `reading_progress` |
| **Help use** | Apakah siswa memakai bantuan/petunjuk? | `challenge_attempts.hint_count`, `item_responses.hint_used`, `item_tries.hint_opened_before`; `audio_usage_events` (+ `challenge_attempts.audio_use_count`) | `hint_opened`, `audio_play`, `audio_autoplay`, `audio_pause`, `audio_replay`, `audio_completed` |

### 12.2 Yang ditulis baru oleh server

| Saat | Tulisan |
|---|---|
| Setiap percobaan soal (Cek jawabanku untuk engine batch; setiap ketuk benda pada `cari`) | satu baris `item_tries`; `item_responses.try_count++`; `solved_on_try` diisi saat pertama kali benar |
| Percobaan ke-3 masih salah (batch) | pengerjaan ditutup otomatis: `completion_reason = tries_exhausted`; event `challenge_tries_exhausted` |
| Ketuk salah ke-3 untuk satu petunjuk `cari` | soal ditutup salah; event `clue_failed`; petunjuk berikutnya |
| Semua soal benar | `completion_reason = solved` |
| Setiap halaman game dirender untuk siswa yang punya sesi | event `screen_viewed` (`App\Filters\ScreenViewFilter`, §12.4) |
| Masuk/daftar | baris `device_logs` |

### 12.3 Daftar `event_type` (pengganti `Config\Gelita::$eventTypes`)

Event lama yang **tetap**: `session_started`, `session_resumed`, `session_paused`, `session_abandoned`, `session_completed`, `locale_changed`, `level_opened`, `level_completed`, `library_opened`, `library_page_viewed`, `dialogue_advanced`, `challenge_opened`, `challenge_checked`, `challenge_completed`, `challenge_abandoned`, `answer_submitted`, `answer_changed`, `wrong_target_clicked`, `hint_opened`, `audio_play`, `audio_autoplay`, `audio_pause`, `audio_replay`, `audio_completed`, `feedback_submitted`, `password_changed`, `intro_completed`.

Event lama yang **dihapus**: `challenge_skipped` (tidak pernah ditulis).

Event **baru**:

| event_type | Sumber | Kapan | `payload_json` | Kolom referensi |
|---|---|---|---|---|
| `screen_viewed` | server | halaman game (HTML 200) dirender untuk siswa bersesi | `{screen, path, from, params}` — `screen` kode layar (§12.4), `from` kode layar sebelumnya di session PHP, `params` mis. `{level: "magelang", seq: 2}` | `level_id`, `challenge_node_id` bila relevan |
| `overlay_opened` | klien | overlay layar penuh dibuka: Kenali wilayah, modal petunjuk, modal keluar, lightbox Pustaka | `{overlay: "kenal" \| "hint" \| "exit" \| "lightbox", level?}` | `level_id` |
| `answer_selected` | klien | siswa mengisi/mengubah satu soal sebelum mengecek: memilih kata ke rumpang / mengosongkan rumpang, memilih Benar/Salah, menukar dua keping puzzle | `{action: "set" \| "clear" \| "swap", value, from?, to?, t_ms}` — `t_ms` ms sejak tantangan dibuka | `challenge_attempt_id`, `challenge_item_id` |
| `challenge_tries_exhausted` | server | percobaan ke-`max_tries` masih ada yang salah (batch) | `{check_count, correct_count, total}` | node, attempt |
| `clue_failed` | server | ketuk salah ke-`max_tries` untuk satu petunjuk `cari` | `{try_count}` | node, attempt, item |
| `reading_progress` | klien | panel bacaan (rumpang/benar-salah) digulir melewati 25 / 50 / 75 / 100 % (masing-masing sekali per pengerjaan) | `{passage_id?, percent}` | attempt |
| `page_hidden` / `page_visible` | klien | `visibilitychange` (pindah tab/aplikasi, layar mati) | `{screen}` | — |
| `viewport_changed` | klien | §11.5 | `{w, h, orientation, stage_w, stage_h, scale, rotate_gate}` | — |

`challenge_checked` kini memuat `{check_no, try_no, correct_count, total, exhausted}`. `answer_submitted`/`answer_changed` (`cari`) memuat `{try_no, correct}`.

Batas: event klien tetap lewat antrean `core/events.js` (batch 50, offline, `client_event_id` idempoten). `answer_selected` dapat banyak pada puzzle (setiap tukar keping) — tetap dikirim; volume aman (puluhan per pengerjaan).

### 12.4 Kode layar untuk `screen_viewed`

`App\Filters\ScreenViewFilter` (after, pada grup rute `gameSession`) memetakan rute ke kode:

| Rute | `screen` |
|---|---|
| `gerbang` | `gate` |
| `intro` | `intro` |
| `peta` | `map` |
| `wilayah/{code}` | `region` |
| `dialog/{code}` | `dialogue` |
| `misi/{code}/{n}` | `brief` |
| `tantangan/{code}/{n}` | `challenge` |
| `selesai/{id}` | `finished` |
| `hasil/{code}/{n}` | `result` |
| `tuntas/{code}` | `region_done` |
| `penutup` | `ending` |
| `pustaka` | `library_index` |
| `pustaka/{code}` | `library` atau `library_locked` |
| `profil` | `profile` |
| `refleksi` | `reflection` |
| `ganti-sandi` | `change_password` |

Hanya respons `200 text/html` yang dicatat (pengalihan tidak). Kode layar sebelumnya disimpan di session PHP `last_screen` untuk `from`. Lama tinggal di sebuah layar **tidak dihitung** sistem — peneliti menghitungnya dari selisih `occurred_at` dengan event navigasi berikutnya dan pasangan `page_hidden/visible`.

### 12.5 Waktu per soal

- Engine batch mengirim, untuk setiap soal di `POST /check`, `{first_input_ms, last_input_ms}` yang diukur `performance.now()` sejak tantangan dibuka (pengukuran mulai setelah tirai tantangan). Server menyimpannya di `item_tries` dan menyalin `last_input_ms` percobaan terakhir ke `item_responses.duration_ms`.
- `cari` mengirim `duration_ms` sejak petunjuk itu tampil (sudah ada) → `item_tries.last_input_ms` dan `item_responses.duration_ms`.
- Nilai klien hanya catatan proses; urutan dan waktu resmi tetap `occurred_at` server.

---

## 13. Bintang dan istilah "Berhasil pada Percobaan Pertama"

### 13.1 Aturan bintang (`ScoringService`, versi `bintang-1`)

Rumus berbobot (70/20/10, penalti petunjuk dan pengulangan) **dihapus**. Bintang hanya hadiah di layar siswa:

| Bintang | Syarat (dibaca dari `item_responses` soal yang dinilai) |
|---|---|
| ★★★ | **semua** soal berhasil pada percobaan pertama |
| ★★ | semua soal benar di akhir (sebagian butuh percobaan ke-2/ke-3) |
| ★ | pengerjaan selesai, tetapi ada soal yang tetap salah setelah kesempatan habis |
| — (0) | pengerjaan belum selesai atau ditinggalkan |

`ScoringService` menjadi kecil: `starsFor(ChallengeAttempt $attempt): int`, `finalize(ChallengeAttempt $attempt): array` (isi `first_pass_correct`, `final_correct`, `scorable_items`, `stars`), `totalStars(int $sessionId): int` (jumlah bintang terbaik per node), `levelSummary(int $sessionId, int $levelId): array{completed_nodes, total_nodes, stars}`. Petunjuk dan suara **tidak** mengurangi bintang (tetap tercatat sebagai data bantuan).

Command `gelita:score:recompute` dihapus.

### 13.2 Istilah di layar

| Lama | Baru (ID) | Baru (EN) |
|---|---|---|
| Tepat sejak awal / Benar sejak awal | **Berhasil pada Percobaan Pertama** | **Success on First Try** |
| Benar di akhir | Benar di akhir | Correct in the end |
| Skor / Total skor | (dihapus) | (removed) |
| Pemeriksaan / Periksa jawaban | Percobaan / **Cek jawabanku** | Tries / **Check my answer** |
| Percobaan (riwayat) | Pengerjaan | Play |

Bentuk tampil: "Berhasil pada Percobaan Pertama: **2 dari 3 soal**" (puzzle: "Ya" / "Belum"). Daftar lengkap teks UI ada di Dok 2 §12.

---

## 14. Kode yang dihapus

Hapus berkas/bagian berikut (bersama ujinya) agar tidak ada jalur mati:

| Kelompok | Berkas / bagian |
|---|---|
| Fase & consent | `Models/ResearchPhaseModel.php`, `Models/ParticipantConsentModel.php`, `Views/game/consent.php`, rute `persetujuan` |
| Skor | `Models/ScoringProfileModel.php`, `Database/Seeds/ScoringProfileSeeder.php`, `Commands/ScoreRecompute.php`, `Views/admin/study/scoring.php`, rute `studi/skoring` |
| Kuis pilihan & interaksi lama | `Views/game/challenge/pilihan.php`, `public/assets/js/engines/pilihan.js`, mode `ordering` di `puzzle.php`/`puzzle.js`, input ketik di `rumpang.php`/`rumpang.js`, kolom alasan & pilihan "Pendapat" di `boleh.php`/`boleh.js`, `Models/ChallengeOptionModel.php`, `Entities/ChallengeOption.php`, rute `konten/item/(:num)/opsi` dan editor opsi, jalur `single_choice`/`source_trust`/`ordering`/`fill_blank_free`/`verdict_reason` di `ChallengeService`, `ContentImportService`, `ContentVerifier`, `AnswerText`, `BankWorkbookGuide`, `content-editor.js` |
| Analitik olahan | `Controllers/Admin/AnalyticsController.php`, `Views/admin/analytics/*`, `Views/partials/{item-analysis-table,node-heatmap,bar-list}.php`, `Views/components/admin-chart.php`, `public/assets/js/admin/charts.js`, `Libraries/ChartData.php`, endpoint `api/admin/{levels,nodes,items,indicators,prepost}`, method `AnalyticsService::{levelBreakdown,nodeDifficulty,itemAnalysis,indicatorMastery,prePostComparison}`, laporan PDF studi (`Views/pdf/report-study.php`, `_bars.php`), `public/assets/vendor/echarts.min.js` (+ lisensinya; perbarui `vendor/README.md`) |
| Lain-lain | kunci bahasa yang tidak dipakai lagi (dicek `LanguageFilesTest`). `SampleItemSeeder` baru dihapus di Dok 2. |

Uji yang dihapus/ditulis ulang: `ChartDataTest` (hapus), `ScoringServiceTest` (tulis ulang untuk bintang), `AnswerTextTest`, `ChallengeEntityTest`, `DataLayerWiringTest`, `ExportLabelsTest`, `ExportRulesTest`, `HuntClueAudioTest`, `LibraryLockTest`, `RegionStoryTest`, `StartGateTest`, `ViewHelperTest`, `DialogueGateTest`, `RouteWiringTest`, `BankReadingDataTest`, `BankWorkbookGuideTest`, `ClozeReadingTest` (sesuaikan).

---

## 15. Pengujian dan kriteria selesai Dokumen 1

### 15.1 Otomatis

- `vendor/bin/phpunit --no-coverage` hijau, termasuk uji baru: `SystemResetTest`, `RegisterWizardTest`, `DeviceProfileTest`, `ItemTryWritesTest` (percobaan menulis `item_tries` dan `try_count`), `ScreenViewFilterTest`, `StarsTest`.
- `RouteWiringTest`: tidak ada rute ke controller/view yang dihapus; `persetujuan` → 301.
- `LanguageFilesTest`: kunci ID dan EN sama; tidak ada kunci `phase*`/`consent*`/`gender_lainnya`.

### 15.2 Manual (MariaDB lokal)

1. Salin database berisi data uji coba lama → `php spark migrate` berhasil tanpa galat → `migrate:rollback` ke `004000` → `migrate` lagi berhasil.
2. `php spark gelita:reset --dry-run` menampilkan jumlah yang benar dan tidak mengubah apa pun.
3. `php spark gelita:reset` → Pustaka, Kenali wilayah, dialog, media tetap (cek di admin dan di game); 12 node; kode siswa baru `GLT-000001`.
4. Daftar lewat 3 langkah (dengan dan tanpa JavaScript), termasuk kembali ke langkah sebelumnya, NPSN, sandi lemah lalu kuat. Baris `device_logs` terisi (coba dari Chrome desktop, Chrome Android, Safari iPad).
5. Masuk ulang → `device_logs` `via = resume`; tidak ada pilihan fase.
6. Mainkan satu tantangan soal contoh → `item_tries`, `screen_viewed`, `answer_selected`, `viewport_changed` tercatat.

### 15.3 Kriteria selesai

- [ ] Migration `004100`–`004600` berjalan maju-mundur di MariaDB 10.11 dan MySQL 8.
- [ ] `gelita:reset` sesuai §7, termasuk jatuh ke `DELETE` dengan akun DML.
- [ ] Tidak ada lagi fase, consent wali, versi persetujuan, skor 0–100, gender "lainnya" di kode, UI, dan ekspor.
- [ ] Pendaftaran 3 langkah berjalan dengan dan tanpa JavaScript.
- [ ] `device_logs` terisi dengan hasil `matomo/device-detector`, termasuk koreksi iPad.
- [ ] Event baru §12.3 tercatat dengan payload sesuai tabel.
- [ ] Bintang memakai aturan §13.1.
- [ ] README dan `docs/01_DATABASE.md` diberi catatan "Perombakan kelas 5 — Dokumen 1" (ringkas: tabel dihapus/ditambah, migration baru, command baru). Penulisan ulang dokumentasi lengkap dilakukan di Dok 3 §11.
