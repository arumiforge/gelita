# Dokumen Implementasi Bagian 7 — Waktu Aktif, Mode Penelitian, dan Perbaikan Media M-04–M-11

Per 2 November 2026 · Bagian dari [revisi 10 analitik](01_REVISI_10_ANALITIK.md) · perubahan sistem no. 5, 16, dan 19 di [01 → Catatan perubahan sistem](01_REVISI_10_ANALITIK.md#catatan-perubahan-sistem-untuk-tahap-nanti)

> **Status: `draf pengembang`, `asal: pengembang`** (disusun dengan bantuan AI, 1–2 November 2026). Empat pilihan rancangan diputuskan pengembang ([§17](#17-keputusan-pengembang)).
>
> Sejak 1 November 2026 kode dibangun sekarang ([04](04_CATATAN_KEPUTUSAN.md), entri 1 November 2026), tetapi tidak dipakai dengan anak sebelum persetujuan etik. Semua perilaku penelitian di sini ada di balik sakelar studi `field_mode` ([28 → K1](28_IMPLEMENTASI_SESI_LAPANGAN.md#2-keputusan-yang-dikunci)), yang mati secara bawaan, jadi `main` tetap bisa dirilis. Setelah hasil validasi kembali, dokumen ini dicek ulang terhadap matriks revisi induk ([20](20_PAKET_VALIDASI.md)) dan keputusan komite etik.
>
> **Bagian 7 dari 9.** Kode bagian ini dikerjakan di gelombang F, bersamaan dengan bagian 8 (`34`, modul tes dan skala sikap), lalu bagian 9 (`35`, lapis data turunan) terakhir. Bagian ini memakai sakelar `field_mode`, rencana pertemuan, sesi lapangan, dan `participant_progress` dari bagian 2 ([28](28_IMPLEMENTASI_SESI_LAPANGAN.md)), katalog event dan lama mendengar dari bagian 3 ([29](29_IMPLEMENTASI_DATA_PENELITIAN.md)).

---

## Daftar isi

0. [Cara memakai dokumen ini](#0-cara-memakai-dokumen-ini)
1. [Kebutuhan, sumber, dan tempatnya](#1-kebutuhan-sumber-dan-tempatnya)
2. [Keputusan yang dikunci](#2-keputusan-yang-dikunci)
3. [Yang tidak berubah](#3-yang-tidak-berubah)
4. [Batas dengan lapis data turunan (bagian 9)](#4-batas-dengan-lapis-data-turunan-bagian-9)
5. [Data mentah untuk waktu aktif (perubahan no. 5)](#5-data-mentah-untuk-waktu-aktif-perubahan-no-5)
6. [Pengaturan studi](#6-pengaturan-studi)
7. [Mode penelitian di game (perubahan no. 16)](#7-mode-penelitian-di-game-perubahan-no-16)
8. [Perbaikan media M-04–M-11 (perubahan no. 19)](#8-perbaikan-media-m-04m-11-perubahan-no-19)
9. [Skema database](#9-skema-database)
10. [Rute](#10-rute)
11. [Teks antarmuka](#11-teks-antarmuka)
12. [Titik sambung ke perubahan lain](#12-titik-sambung-ke-perubahan-lain)
13. [Pengujian dan kriteria selesai](#13-pengujian-dan-kriteria-selesai)
14. [Dokumen yang ikut diperbarui saat implementasi](#14-dokumen-yang-ikut-diperbarui-saat-implementasi)
15. [Pertanyaan terbuka](#15-pertanyaan-terbuka)
16. [Akibat ke dokumen lain](#16-akibat-ke-dokumen-lain)
17. [Keputusan pengembang](#17-keputusan-pengembang)

---

## 0. Cara memakai dokumen ini

**Urutan.** Kode bagian ini dikerjakan setelah bagian 1–4 ada di `main` (gelombang C–E), karena memakai `field_mode`, `participant_progress`, `EventCatalog`, dan baris `pause` audio yang lengkap. Bagian 8 boleh dikerjakan bersamaan; kedua bagian tidak memiliki berkas yang sama kecuali yang disebut di [§12](#12-titik-sambung-ke-perubahan-lain).

**Batas.** Dokumen ini mengatur:
- **data mentah** untuk waktu aktif: selang layar tersembunyi, jeda masukan 20 detik atau lebih, selang narasi diputar, dan potongan pengerjaan lintas sesi;
- **mode penelitian di game**: jam disembunyikan, layar kecil ditolak, pengerjaan pertama dilanjutkan lintas sesi, pengerjaan ulang dan Kenali yang terbuka belakangan;
- **perbaikan media** M-04 sampai M-11 dari telaah pendahuluan ([10 → Catatan temuan](10_TELAAH_PENDAHULUAN_MEDIA.md#catatan-temuan-b6)).

Angka turunan (`active_ms`, `hidden_ms`, `idle_excess_ms`, "dilewati", tersensor, tebakan cepat, `is_retake` di lembar turunan) **tidak dihitung di sini**; itu bagian 9 ([§4](#4-batas-dengan-lapis-data-turunan-bagian-9)).

**Saat implementasi:**
- Satu cabang pendek dari `main`, commit berbahasa Inggris.
- `vendor/bin/phpunit --no-coverage` hijau sebelum push.
- Jangan menjalankan migrasi, `gelita:reset`, atau skrip rilis di server produksi.
- Tulisan ke `challenge_attempts` dan `item_responses` yang bisa bersaing memakai UPDATE bersyarat (`ChallengeService::updateIf()`, `updateOrStale()`), bukan `SELECT … FOR UPDATE`.
- Isi (teks layar, pesan) di berkas bahasa; mekanisme di kode.

**Rujukan kode.** Ditulis per berkas dan metode, dengan nomor baris per 2 November 2026 (`main` di *commit* `97f82d5`). Nomor baris bisa bergeser, terutama setelah bagian 1–4 dikerjakan; cari nama metodenya.

---

## 1. Kebutuhan, sumber, dan tempatnya

| # | Kebutuhan | Sumber | Di sini |
| ---: | --- | --- | --- |
| 1 | Waktu aktif dihitung dari selang layar tersembunyi dan jeda tanpa masukan; ambangnya pengaturan studi (sementara 3 menit) | 01 no. 5; [11 → Analitik 5](11_KAMUS_DATA.md#analitik-5-waktu-pengerjaan) | §5, §6 |
| 2 | Setiap jeda masukan 20 detik atau lebih dicatat dengan jam mulai dan jam akhir; selang narasi diputar bisa dibaca dari log | [11 → Yang harus disediakan sistem di tahap 3](11_KAMUS_DATA.md#yang-harus-disediakan-sistem-di-tahap-3) | §5.2, §5.3 |
| 3 | Masukan = sentuh/klik, tombol keyboard, gulir, seret; gerakan tetikus tidak dihitung | 11 → Analitik 5, *Aturan jeda* | §5.2 |
| 4 | Pengerjaan yang dilanjutkan di sesi lain dihitung per potongan; selang di antaranya tidak dihitung | 11 → Analitik 5, *Garis waktu* | §5.4, §7.3 |
| 5 | T_idle dan T_teb disimpan di konfigurasi studi, bisa diubah tanpa mengubah kode | 11 → *Yang harus disediakan sistem* | §6 |
| 6 | Jam disembunyikan selama penelitian, waktu tetap direkam server; hitungan benar/salah boleh tetap tampil | 01 no. 16; [10 → M-03](10_TELAAH_PENDAHULUAN_MEDIA.md#catatan-temuan-b6) | §7.1 |
| 7 | Sesi penelitian di layar kecil ditolak dengan pesan ramah; di luar studi cukup peringatan; ambang dari ukuran panggung | 01 no. 16; 10 → M-01, *Uji per perangkat (B.5)* | §7.2 |
| 8 | Pengerjaan pertama yang terputus dilanjutkan, bukan diulang | [11 → Pengerjaan pertama](11_KAMUS_DATA.md#pengerjaan-pertama) | §7.3 |
| 9 | Pengerjaan ulang baru terbuka setelah kelima tantangan wilayah pertemuan itu tuntas, hanya di dalam sesi; disimpan `is_retake = 1`, tidak dinilai; waktu tetap direkam server | 01 no. 16; 11 → Pengerjaan pertama | §7.4 |
| 10 | Kenali wilayah baru terbuka setelah wilayahnya tuntas selama studi | [12 → Keputusan pengembang](12_CERITA_PENGHUBUNG_DAN_NARASI.md#keputusan-pengembang) no. 2; 12 → *Yang perlu disiapkan di tahap 3* no. 7 | §7.5 |
| 11 | Wilayah per pertemuan dari rencana pertemuan | [28 §5](28_IMPLEMENTASI_SESI_LAPANGAN.md#5-mode-penelitian-dan-rencana-pertemuan), [§9](28_IMPLEMENTASI_SESI_LAPANGAN.md#9-wilayah-yang-dibuka-per-pertemuan) | Dipakai, tidak diubah |
| 12 | Selang narasi butuh baris `pause` dengan lama mendengar yang lengkap | [29 §5.4](29_IMPLEMENTASI_DATA_PENELITIAN.md#54-audio_pause-dan-lama-mendengar), [§15](29_IMPLEMENTASI_DATA_PENELITIAN.md#15-titik-sambung-ke-perubahan-lain) | §5.3 |
| 13 | M-04 nama berkas gambar benda Cari Objek memuat kode butir | 10 → M-04 | §8.1 |
| 14 | M-05 antrean event di perangkat bersama setelah keluar | 10 → M-05 | §8.2 |
| 15 | M-06 kalimat petunjuk tantangan lebih kecil dari bacaan | 10 → M-06 | §8.3 |
| 16 | M-07 satu tantangan punya dua nama | 10 → M-07 | §8.4 |
| 17 | M-08 dua pelanggaran axe (kontras judul kartu Gerbang, `.table-wrap` Profil) | 10 → M-08 | §8.5 |
| 18 | M-09 galat transisi saat pindah layar sangat cepat | 10 → M-09 | §8.6 |
| 19 | M-10 kode penelitian di Profil kecil dan abu-abu | 10 → M-10 | §8.7 |
| 20 | M-11 data pendaftaran (provinsi, kabupaten, sekolah, `device_json`) | 10 → M-11 | §8.8 |

---

## 2. Keputusan yang dikunci

K3, K5, K7, dan K11 diputuskan pengembang ([§17](#17-keputusan-pengembang)). K10 dan K12 berasal dari pencarian kode dan dicatat di [04](04_CATATAN_KEPUTUSAN.md) untuk ditinjau. Sisanya menurunkan 01, 10, 11, 12, 28, dan 29.

| Kode | Keputusan |
| --- | --- |
| K1 | **Satu sakelar.** Semua perilaku no. 16 di game berlaku bila `field_mode` menyala dan studi `active` ([28 → K1](28_IMPLEMENTASI_SESI_LAPANGAN.md#2-keputusan-yang-dikunci)). Tidak ada sakelar kedua. Fungsi bantu `GameContext::researchMode(GameSession)` menjadi satu-satunya tempat pemeriksaannya. |
| K2 | **Data mentah selalu direkam, juga di luar mode penelitian.** Jeda masukan, potongan pengerjaan, `is_retake`, dan offset jam audio tidak bergantung pada sakelar, supaya instalasi uji dan uji coba menghasilkan data yang sama bentuknya. Yang bergantung pada sakelar hanya tampilan dan aturan buka. |
| K3 | **Pengerjaan pertama yang terputus pindah ke sesi game baru dan setiap potongannya dicatat.** Saat siswa membuka lagi tantangan yang pengerjaannya masih `in_progress` di sesi game lain milik siswa yang sama, `challenge_attempts.session_id` dipindah ke sesi baru lewat UPDATE bersyarat, dan tabel baru `challenge_attempt_segments` mencatat setiap potongan (sesi, sesi lapangan, jam buka, jam tinggal). |
| K4 | **Event jeda baru `input_idle`** dari klien: satu event untuk setiap jeda masukan 20 detik atau lebih, dengan jam mulai dan akhir jeda. Batas 20 detik adalah konstanta pencatatan di `Config\Gelita`, bukan ambang analisis. |
| K5 | **Pengerjaan ulang tetap memberi bintang seperti sekarang** (pengerjaan terbaik). Analisis penelitian mengabaikannya lewat `is_retake`. |
| K6 | **`challenge_attempts.is_retake`** = 1 bila siswa sudah punya pengerjaan `completed` di tantangan itu saat pengerjaan dibuat. Diisi server di `openNode()`, di semua mode. |
| K7 | **Layar kecil: lebar panggung di bawah 960 px.** Dalam mode penelitian layar ditolak dengan pesan ramah; di luar itu tampil peringatan yang bisa ditutup. Ambangnya pengaturan studi (`min_stage_px`). |
| K8 | **Jam disembunyikan di semua layar siswa** selama mode penelitian: kepala tantangan, layar hasil (*Waktu* dan kolom *Lama*), dan Profil (*Total waktu*). Hitungan ✓/✗ tetap tampil. Server tetap merekam semua waktu. |
| K9 | **Kenali wilayah selama mode penelitian** hanya bisa dibuka setelah wilayahnya tuntas. Lencana "Belum didengar" tidak berdenyut untuk Kenali yang masih terkunci. |
| K10 | **Offset jam disimpan juga di `audio_usage_events`** (`clock_offset_ms`). Temuan kode: tabel itu menyimpan `occurred_at` dari perangkat tanpa offset (`EventService::recordAudioBatch()`, baris 484–496), dan sejak 29 K6 baris `pause` tidak lagi punya event mentah yang membawa offset. Tanpa kolom ini, selang narasi tidak bisa dibaca dengan waktu terkoreksi (11 → *Kunci penggabungan dan waktu*). |
| K11 | **Keluar menunggu antrean terkirim** paling lama 5 detik, lalu keluar. Sisa antrean sesi itu tetap di perangkat dengan penanda sesinya dan terkirim bila siswa yang sama masuk lagi di perangkat itu; sisa yang berumur lebih dari 14 hari dibuang dan dihitung. |
| K12 | **M-11 sudah diatur bagian 2 dan 3.** Pilihan provinsi, kabupaten, dan sekolah dilewati selama mode penelitian ([28 → K9](28_IMPLEMENTASI_SESI_LAPANGAN.md#2-keputusan-yang-dikunci)); rincian perangkat dikurangi ([29 §5.5](29_IMPLEMENTASI_DATA_PENELITIAN.md#55-rincian-perangkat)). Bagian ini hanya memeriksa bahwa keduanya menutup temuan, dan menambah satu hal: formulir tidak mengirim `device_json` lebih dari yang disimpan ([§8.8](#88-m-11-data-pendaftaran)). |
| K13 | **Aturan 3 kesempatan dan `ScoringService` tidak berubah.** Pengerjaan ulang dan pengerjaan yang dilanjutkan memakai aturan yang sama. |

---

## 3. Yang tidak berubah

| Bagian | Aturan |
| --- | --- |
| Penilaian | `ChallengeService::submitCheck()`, `submitAnswer()`, `closeAttempt()`, `closingReason()`; `ScoringService` (aturan `bintang-1`); kode galat di `ChallengeRuleException` |
| Waktu server | `challenge_attempts.started_at`, `completed_at`, `duration_ms`; `item_tries` dengan waktu server; `game_event_logs.occurred_at` dan `clock_offset_ms` |
| Antrean event | `core/events.js` dan `core/storage.js`: antrean per kanal dengan penanda `sessionTag`, `persist-on-push`, `events_dropped` |
| `page_hidden`, `page_visible` | Tetap dari `initVisibilityTracking()` (`core/events.js:426-430`), pemilik Peneliti di katalog 29 |
| Kunci jawaban | Tidak pernah dikirim ke peramban. Token objek Cari Objek tetap HMAC per pengerjaan (`ChallengeService::objectRef()`, baris 1295–1304) |
| Di luar mode penelitian | Jam tampil, Kenali terbuka sebelum wilayah, pengerjaan ulang terbuka kapan saja setelah tantangan tuntas, layar kecil hanya diberi peringatan |
| Layar putar | `game/rotate-gate.js` tetap menahan ponsel tegak; layar kecil adalah pemeriksaan terpisah |

---

## 4. Batas dengan lapis data turunan (bagian 9)

Bagian ini menyimpan **apa yang terjadi**; bagian 9 ([`35`](00_INDEKS.md), ditulis paralel) menghitung **artinya** dengan ambang yang berlaku.

| Data | Bagian 7 (di sini) menyimpan | Bagian 9 menghitung |
| --- | --- | --- |
| Layar tersembunyi | `page_hidden`, `page_visible` (sudah ada), `challenge_abandoned {via: pagehide}` | `hidden_ms` per pengerjaan dan sesi |
| Jeda masukan | `input_idle` per jeda ≥ 20 detik (§5.2) | `idle_excess_ms` dengan T_idle; bagian yang tumpang tindih dengan narasi atau layar tersembunyi dibuang |
| Narasi diputar | Baris `audio_usage_events` dengan `clock_offset_ms` (§5.3) | Selang narasi per pemutaran |
| Potongan pengerjaan | `challenge_attempt_segments` (§5.4) | `total_ms` = jumlah potongan; `active_ms` |
| Waktu sesi lapangan | `game_sessions.field_session_id` (28) | `session_active_ms`, `cumulative_active_ms` |
| Pengerjaan ulang | `challenge_attempts.is_retake` (K6) | Penanda `is_retake` di lembar turunan; pengerjaan pertama |
| Ambang | `research_studies.idle_threshold_ms`, `rapid_guess_ms` (§6) | Dibaca saat menghitung; nilai yang dipakai dicatat bersama `dictionary_version` |
| Tebakan cepat | `ready_ms`, `last_input_ms` (29 §6.1) | `decision_ms`, `rapid_guess` |
| Tersensor, dilewati | `item_tries`, `clue_failed`, `challenge_abandoned`, potongan | `censored`, `censor_reason`, `skipped_*` |

Aturan untuk kedua bagian: data mentah tidak pernah diubah oleh perhitungan turunan, dan ambang tidak pernah dipakai di klien atau saat menyimpan.

---

## 5. Data mentah untuk waktu aktif (perubahan no. 5)

### 5.1 Yang sudah ada

- `page_hidden` dan `page_visible` dengan `screen` (`core/events.js:426-430`), dikirim klien.
- `challenge_opened` (server, dan klien saat dilanjutkan, `game/challenge.js:504-507`), `challenge_abandoned {via: pagehide}` (`game/challenge.js:496-500`), `session_paused` (28 §6.6, 29 K14).
- `game_event_logs.clock_offset_ms` untuk setiap event.
- Detak sesi (`game/hud.js`) hanya untuk `last_active_at`; tidak dipakai untuk waktu aktif (11 → *Sesi*).

### 5.2 Jeda masukan: event `input_idle`

**Modul baru `public/assets/js/core/activity.js`**, dimuat `game.js` bila ada sesi (`CONFIG.sessionTag`), di semua layar game (tantangan, cerita, peta, Pustaka, Profil), karena 11 menghitung waktu aktif sesi juga di luar tantangan.

**Masukan** (11): `pointerdown`, `keydown`, `wheel`, `scroll` (dengan `capture`, karena panel bacaan bergulir di dalam elemen), dan `pointermove` **selama tombol ditekan** (seret). `mousemove` tanpa klik tidak dihitung. Semua pendengar `passive`.

**Aturan pencatatan:**
- Jam masukan terakhir disimpan sebagai `performance.now()`.
- Saat masukan berikutnya datang dan selangnya **≥ `inputIdleLogMs`** (20.000 ms, `Config\Gelita`, dikirim lewat `CONFIG`), klien menulis satu `input_idle`.
- Jeda **dipotong** oleh layar tersembunyi: saat `visibilitychange` ke `hidden`, jeda yang sedang berjalan ditutup (`end: hidden`) bila sudah ≥ 20 detik, dan jam masukan terakhir dimulai ulang saat layar terlihat lagi. Dengan begitu `input_idle` tidak pernah tumpang tindih dengan selang tersembunyi.
- Saat halaman ditinggal (`pagehide`), jeda yang ≥ 20 detik ditutup (`end: pagehide`) dan ikut antrean yang di-*beacon*.
- Narasi **tidak** memotong jeda di klien. Selang narasi dibuang bagian 9 dari data audio (§5.3), supaya klien tidak menerapkan aturan analisis.
- Halaman baru dimulai dengan jam masukan = saat modul dimuat; selang antarhalaman sudah tercatat sebagai `screen_viewed` di server.

**Bentuk event:**

| Bidang | Isi |
| --- | --- |
| `event_type` | `input_idle` |
| `occurred_at` | Jam perangkat saat jeda berakhir (seperti event lain; dikoreksi `clock_offset_ms`) |
| referensi | `level_id`, `node_id`, `attempt_id` bila di layar tantangan; selain itu kosong |
| payload `idle_ms` | Lama jeda (bilangan bulat, ms) |
| payload `started_at` | Jam perangkat saat jeda mulai (ISO 8601 ms), supaya jam mulai dan akhir bisa dibaca tanpa menghitung mundur |
| payload `end` | `input`, `hidden`, atau `pagehide` |
| payload `screen` | Kode layar (`screenName()` yang sama dengan `page_hidden`) |

**Katalog event (29 K7):** `input_idle` pemilik Peneliti, ditulis klien, dipakai Analitik 5. Tidak termasuk `SERVER_ONLY`.

**Batas volume.** Paling banyak satu event per jeda; jeda 20 detik di sesi 90 menit menghasilkan paling banyak ± 270 event, di bawah batas antrean (`core/storage.js`, `QUEUE_MAX`).

**Engine.** Tidak ada perubahan engine: seret keping (`core/drag.js`) dan ketukan benda sudah memicu `pointerdown`.

### 5.3 Selang narasi: `audio_usage_events.clock_offset_ms`

Sumber selang narasi adalah baris `audio_usage_events` (29 §5.4): aksi pembuka (`play`, `autoplay`, `replay`) memberi jam mulai, `pause` atau `complete` dengan `listened_ms` memberi jam akhir.

**Temuan kode (K10).** `EventService::recordAudioBatch()` menulis `occurred_at` dari perangkat apa adanya (baris 495) dan offset jam hanya ke event mentah (`buildRow()`, baris 381–394). Sejak 29 K6, `pause` tidak lagi punya event mentah, jadi jam akhirnya tidak bisa dikoreksi.

**Sesudah:**
- Kolom baru `audio_usage_events.clock_offset_ms` INT (null), diisi dengan `$offset` batch yang sama dengan event mentah (`clockOffsetMs($clientSentAt, $receivedAt)`).
- `occurred_at` tetap jam perangkat (sama dengan `game_event_logs`); waktu terkoreksi dihitung saat unduhan dan di lapis turunan (29 K8).
- Lembar unduhan `bantuan` (29) tidak berubah; kolom offset hanya di log teknis admin, seperti `game_event_logs.clock_offset_ms`.

Klien sudah mengirim `pause` saat narasi diulang, diganti narasi lain, atau halaman ditinggal (29 K6). Bagian ini tidak menambah aksi audio.

### 5.4 Potongan pengerjaan: `challenge_attempt_segments`

Setiap kali sebuah pengerjaan dibuka atau dilanjutkan di layar tantangan, server menulis satu potongan. Ini sumber garis waktu "setiap potongan dihitung sendiri" di 11.

| Saat | Yang ditulis |
| --- | --- |
| `openNode()` membuat pengerjaan baru | Potongan 1: `session_id`, `field_session_id` sesi game itu, `opened_at` = `started_at` |
| `openNode()` melanjutkan pengerjaan di sesi game yang sama | Potongan baru hanya bila potongan terakhir sudah punya `left_at` (siswa sempat keluar); selain itu tidak ada (muat ulang halaman) |
| `openNode()` memindah pengerjaan dari sesi game lain (§7.3) | Potongan lama ditutup bila belum (`left_at` = `last_active_at` sesi lama, `left_via = session_ended`), lalu potongan baru di sesi baru |
| `abandonAttempt()` (`ChallengeService`, baris 714) | `left_at` = sekarang, `left_via` = `via` payload (`user_exit`, `pagehide`, `content_changed`) |
| `closeAttempt()` (baris 639) | `left_at` = `completed_at`, `left_via = closed` |
| Sesi lapangan ditutup (28 §6.6) atau `markStale()` (29 K14) | `left_at` = jam event `session_paused`, `left_via = session_paused` untuk potongan terbuka sesi itu |

Semua penutupan potongan memakai UPDATE bersyarat `WHERE id = ? AND left_at IS NULL`, jadi dua permintaan bersamaan tidak saling menimpa. `challenge_abandoned` dari *beacon* `pagehide` (klien) tidak menulis potongan; yang menulis hanya jalur server. Potongan yang tetap terbuka (perangkat mati) ditutup bagian 9 dengan event terakhir pengerjaan itu, sesuai 11.

**`duration_ms`** tetap ditulis `closeAttempt()` (`time() − started_at`, baris 650), tetapi untuk pengerjaan berpotongan nilainya memuat selang antarpertemuan. Kamus `kamus-1.1` mencatat bahwa analisis memakai potongan, bukan `duration_ms` ([§16](#16-akibat-ke-dokumen-lain)).

---

## 6. Pengaturan studi

Ambang adalah pengaturan, bukan angka di kode (11). Disimpan sebagai kolom `research_studies`, diubah `ketua` di halaman studi (kemampuan `studies.manage`, 27), dengan audit `study_settings_changed` (nilai lama dan baru).

| Kolom | Bawaan | Arti | Dipakai |
| --- | ---: | --- | --- |
| `idle_threshold_ms` | 180.000 | T_idle, jeda yang melebihinya dipotong dari waktu aktif | Bagian 9 |
| `rapid_guess_ms` | 3.000 | T_teb, tebakan cepat | Bagian 9 |
| `min_stage_px` | 960 | Lebar panggung terkecil untuk sesi penelitian (K7) | §7.2 |

- Ketiganya bisa diubah selama studi `active`; mengubah ambang tidak mengubah data mentah. Bagian 9 mencatat nilai yang dipakai bersama `dictionary_version`.
- Batas isian: `idle_threshold_ms` 30.000–1.800.000, `rapid_guess_ms` 500–10.000, `min_stage_px` 640–1.280. Panel menampilkan detik dan piksel, bukan milidetik.
- `inputIdleLogMs` (20.000) **tidak** pengaturan studi: ia batas pencatatan, harus di bawah ambang mana pun. Panel menolak `idle_threshold_ms` di bawah `inputIdleLogMs`.
- Kalau bagian 9 menempatkan T_idle dan T_teb di tempat lain, satu tempat dipilih saat kedua dokumen digabung ([§15](#15-pertanyaan-terbuka) no. 1).

---

## 7. Mode penelitian di game (perubahan no. 16)

### 7.1 Jam disembunyikan

| Tempat | Sekarang | Mode penelitian |
| --- | --- | --- |
| Kepala tantangan | `#timer` (`app/Views/game/challenge/_frame.php:73`) | Tidak dirender. `startTimer()` sudah aman tanpa elemen (`core/timer.js`, `if (!el)`), jadi `game/challenge.js:80` tidak berubah |
| Hitungan ✓/✗ | `#count-correct`, `#count-wrong` (baris 76–78) | Tetap (10 → M-03) |
| Layar hasil | *Waktu* (`challenge-result.php:52`), kolom *Lama* (baris 68, 80) | Disembunyikan |
| Profil | Ubin *Total waktu* (`profile.php:70`) | Disembunyikan |
| Payload | `elapsed_ms` di `buildPayload()` | Tidak dikirim; jam tidak dirender |

View membaca `$researchMode` dari `BaseGameController::hudData()` (satu tempat, K1).

### 7.2 Layar kecil

**Ukuran.** Panggung 16:9 berukuran `min(lebar, tinggi × 16/9)` (`core/stage.js`, `stageRect()`). Di ponsel mendatar 844 × 390 panggungnya ± 693 px, dengan teks 6–9 px (10 → B.5); di tablet 1024 × 768 panggungnya 1024 px. Ambang 960 px meloloskan laptop, Chromebook, dan tablet ≥ 10 inci, dan menolak ponsel (K7).

**Pemeriksaan.**
- **Klien:** modul baru `game/screen-check.js` mengukur `stageRect().width` saat dimuat dan saat `resize` (diredam 500 ms).
  - Mode penelitian dan lebar < `min_stage_px`: tampil layar penghalang `components/small-screen.php` (pola `rotate-gate.js`: isi lain `inert`, fokus ke penghalang). Penghalang hilang sendiri bila jendela dibesarkan.
  - Di luar mode penelitian: peringatan yang bisa ditutup, sekali per sesi peramban (`sessionStorage`).
- **Server:** layar token (`/token`, 28) menerima `stage_w` dari formulir. Dalam mode penelitian, nilai di bawah `min_stage_px` ditolak dengan pesan yang sama sebelum sesi game dibuat, supaya tidak ada sesi lapangan yang tercatat dari ponsel. Nilai kosong (JavaScript mati) diterima; klien tetap menahan.
- **Data:** event `viewport_changed` (admin, 29) sudah membawa ukuran. Penolakan dicatat sebagai audit sesi lapangan `small_screen_refused` (jumlah per sesi di panel pemantauan 28), tanpa data siswa, karena siswa belum masuk sesi.

L0 dan L1 menyebut perangkat yang dipakai ([§14](#14-dokumen-yang-ikut-diperbarui-saat-implementasi)).

### 7.3 Pengerjaan pertama dilanjutkan lintas sesi

**Masalah.** `ChallengeService::openNode()` mencari pengerjaan terbuka di sesi game itu saja (`ChallengeAttemptModel::currentInProgress($session->id, $nodeId)`, `ChallengeService.php:66`; model baris 49–56). Di mode penelitian setiap pertemuan membuat sesi game baru (28 K6), jadi tantangan yang ditinggal di P2 akan dimulai ulang di P5, dan percobaan pertamanya sudah tercampur umpan balik (11).

**Sesudah (K3):**
1. Metode baru `ChallengeAttemptModel::openForParticipant(int $participantId, int $nodeId, ?int $studyId)`: pengerjaan `in_progress` terbaru siswa itu di tantangan itu, lewat `game_sessions.participant_id` (pola `variantsServed()`, baris 88–100), di studi yang sama.
2. `openNode()` memakainya **sebelum** `currentInProgress()`. Bila hasilnya ada di sesi game lain:
   - `updateIf(ChallengeAttemptModel, id, ['session_id' => lama, 'status' => 'in_progress'], ['session_id' => baru])`;
   - kalah balapan (`false`) → muat ulang pengerjaan dan lanjutkan dengan keadaan terbaru, seperti pola `rollback()`;
   - menulis potongan (§5.4) dan event server `challenge_opened {resumed: true, moved_from_session: lama}`.
3. Butir yang sudah tertutup tetap tertutup; nomor percobaan berlanjut, karena `item_responses` dan `item_tries` ikut pengerjaan, bukan sesi.
4. Berlaku di semua mode (K2). Di luar mode penelitian, siswa yang masuk lagi juga melanjutkan pengerjaannya, sejalan dengan 28 §8.

**Yang ikut diperiksa:** pemeriksaan milik sesi (`ChallengeApiController`, `EventService::recordAudioBatch()` `attempt_not_in_session`) tetap memakai `session_id` pengerjaan, yang kini sesi baru; event yang tertunda dari sesi lama dengan `attempt_id` itu diterima tanpa rujukan pengerjaan (`attempt_id` dikosongkan, alasan `attempt_moved` di log teknis), bukan ditolak. `nextAttemptNo()` (baris 37–47) dihitung per siswa, bukan per sesi, supaya nomor pengerjaan tidak berulang setelah pindah.

**Aturan cadangan** "dua pengerjaan sebelum tertutup" (11) tetap di bagian 9 untuk data yang terlanjur terpecah.

### 7.4 Pengerjaan ulang

**Sekarang.** Tantangan yang sudah tuntas bisa dibuka lagi kapan saja: `ChallengeController::play()` (baris 41–84) memanggil `openNode()`, yang membuat pengerjaan baru karena tidak ada yang terbuka. Layar hasil punya tombol *Ulangi*.

**Mode penelitian:**
- Pengerjaan ulang hanya terbuka bila **kelima tantangan wilayah itu tuntas** dan wilayah itu **termasuk rencana pertemuan sesi lapangan yang sedang buka** (28 §5.2, §9). Di P5 ketiga wilayah termasuk rencana, jadi siswa yang wilayahnya tuntas boleh mengulang setelah susulan.
- Pemeriksaan di satu tempat: `ChallengeService::assertRetakeAllowed(GameSession, ChallengeNode)`, dipanggil `openNode()` bila siswa sudah punya pengerjaan `completed` di tantangan itu dan tidak ada yang terbuka. Galat baru `ChallengeRuleException::RETAKE_LOCKED` (409), sehingga API tidak bisa melompati aturan.
- `GameProgress::nodeOverview()` memberi status `done_locked` untuk tantangan tuntas yang belum boleh diulang; peta wilayah dan layar hasil menyembunyikan *Ulangi* dan menampilkan `Game.retakeLater`.
- Pengerjaan ulang: `is_retake = 1` (K6), aturan 3 kesempatan sama, bintang seperti sekarang (K5), waktu direkam server seperti biasa. Cerita penghubung tidak diputar lagi (12, keputusan 1; bagian 6).
- Variasi pengerjaan ulang tetap dari `pickVariant()` (variasi yang belum didapat); buklet hanya menentukan pengerjaan pertama (bagian 5).

### 7.5 Kenali setelah wilayah tuntas

**Sekarang.** `MapController::regionIntros()` (baris 74–84) mengirim narasi Kenali semua wilayah, termasuk yang terkunci ("boleh dikenali sebelum dimasuki"); lencana "Belum didengar" membaca `heardRegionIntros()`.

**Mode penelitian:**
- `regionIntros()` hanya mengisi wilayah yang tuntas (`regionStatus() = done`, 28 §8). Wilayah lain mendapat daftar kosong, jadi teks dan audio Kenali tidak ada di sumber halaman.
- Tombol Kenali di panel wilayah (`partials/region-intro`) tampil terkunci dengan `Game.kenaliAfterRegion`, dan lencana "Belum didengar" hanya untuk Kenali yang sudah terbuka.
- `/dialog/{wilayah}` memakai judul slide Kenali pertama sebagai *tagline* (`DialogueController::show()`, baris 39–47). Judul itu hanya nama bab ("Mengenal …"), bukan fakta, jadi tetap.
- `heardRegionIntros()` diganti pembaca `dialogue_completed` oleh bagian 3 (29 K5); bagian ini hanya memakai hasilnya.

---

## 8. Perbaikan media M-04–M-11 (perubahan no. 19)

### 8.1 M-04 nama berkas gambar benda

**Temuan kode.** Token objek sudah acak per pengerjaan (`objectRef()`), tetapi gambar benda dikirim lewat `media_src($item->media_asset_id)` (`ChallengeService::huntPayload()`, baris 1255; `content_helper.php:138-149`), yang memuat jalur berkas, misalnya `…/wnb-4-a01.webp`. Sasaran dan pengecoh bisa dibedakan dari nama.

**Sesudah:**
- Rute baru `GET /api/attempts/{id}/objects/{ref}` (`ChallengeApiController::objectImage`) mengirim gambar benda untuk token objek pengerjaan itu, dengan `Cache-Control: private, max-age=86400` dan tanpa nama berkas di header.
- `huntPayload()` mengisi `media` dengan URL itu, bukan `media_src()`. Jalur berkas tidak pernah sampai ke peramban.
- Hanya pemilik pengerjaan yang mendapat gambar (filter `apiSession`, pengerjaan milik sesi). Token yang tidak dikenal → 404.
- Mode paket (`asset_packages`): gambar benda Cari Objek dikeluarkan dari daftar paket yang di-*cache* dengan nama asli. Paket menyimpan berkasnya dengan nama hash isi (`sha256` 16 karakter) yang dipetakan di server; `AssetPackageBuilder` diubah untuk jenis media `cari_object`. Ini juga berlaku untuk *Periksa Unggahan* (10, kebutuhan no. 3).
- Atribut lain juga diperiksa: `alt` benda tetap kosong seperti sekarang, urutan benda diurutkan menurut posisi (baris 1270), `data-object` berisi token.

### 8.2 M-05 antrean event di perangkat bersama

**Temuan kode.** Antrean sudah diberi penanda sesi (`core/storage.js`, baris 5–9; `pendingEvents(tag)` hanya membaca sesi yang berjalan), jadi event siswa A tidak pernah tercatat atas nama siswa B. Yang tersisa:
- *Keluar* adalah tautan biasa ke `/keluar` (`components/hud-menu.php:219`), dan `LoginController::logout()` (`app/Controllers/Game/LoginController.php:156-161`) langsung menghapus sesi. Event terakhir dikirim lewat *beacon* `pagehide` (`core/events.js:413`) yang bisa tiba **setelah** sesi dihapus, lalu ditolak dan tertinggal di `localStorage`.
- Sisa antrean siswa yang tidak kembali ke perangkat itu tinggal tanpa batas waktu.

**Sesudah (K11):**
- `game/hud.js` menangkap klik *Keluar* (`.hud-leave`): `await flush()` dengan batas 5 detik, lalu pindah ke `/keluar`. Tanpa JavaScript, tautan tetap jalan.
- `Storage` menyimpan `at` (jam simpan) di setiap baris antrean. Saat halaman dimuat, baris sesi lain yang `at`-nya lebih dari 14 hari dibuang dan dihitung di `gelita.dropped.{channel}` sesi itu; laporan itu hanya terkirim bila sesi itu kembali, jadi angkanya dicatat juga di `console` saja, tanpa data siswa.
- `sequence` di `sessionStorage` (`gelita.seq.{tag}`) hilang sendiri saat tab ditutup; tidak diubah.
- L0 ditambah "tutup peramban di antara siswa" ([§14](#14-dokumen-yang-ikut-diperbarui-saat-implementasi)).

### 8.3 M-06 ukuran kalimat petunjuk tantangan

`.instruction` (`public/assets/css/game.css:1987-1997`) memakai `1.0625rem`, sedangkan bacaan memakai `var(--fs-reading)` (`.passage-body`, baris 2361). Sesudah: `.instruction` memakai `font-size: max(var(--fs-reading), 1.0625rem)`, dan di Isi Rumpang serta Benar atau Salah petunjuk tampil sebagai kartu di atas bacaan (`.instruction.is-card`, latar papan biru `--plate-navy`). Tidak ada perubahan teks.

### 8.4 M-07 satu nama per jenis tantangan

Pin peta memakai nama jenis ("Isi Rumpang"), sedangkan kartu dan judul memakai judul variasi ("Lengkapi Bacaan Temanggung", `ChallengeService::variantTitle()`, baris 1201). Sesudah: **nama jenis** (glosarium: Susun Gambar, Isi Rumpang, Benar atau Salah, Cari Objek; Skenario Digital dari bagian 6) tampil di pin, kartu tantangan, dan cincin nomor tantangan; judul variasi menjadi subjudul kecil di kepala tantangan saja. Nama jenis dibaca dari berkas bahasa (`Game.engine{Puzzle,Rumpang,Boleh,Cari}`), bukan dari bank.

### 8.5 M-08 dua pelanggaran axe

- **Kontras judul kartu Gerbang** (`a[href$="intro"] .start-text h2`): warna teks dan bayangannya diubah sampai rasio kontras ≥ 4,5 : 1 terhadap latar kartu terang; diperiksa ulang dengan axe.
- **`.table-wrap` di Profil** (`app/Views/game/profile.php`): wilayah gulir diberi `tabindex="0"`, `role="region"`, dan `aria-label` (`Game.profileTableLabel`), supaya terjangkau keyboard.
- Tes Playwright yang sudah ada untuk axe (bila ada) atau cek manual dicatat di §13.3. Kontras ± 350 elemen di atas gambar tetap dinilai ahli media.

### 8.6 M-09 galat transisi

Transisi lintas halaman memakai `@view-transition { navigation: auto; }` (`public/assets/css/stage.css:1036`). Pindah layar sangat cepat membatalkan transisi dan menulis galat di konsol. Sesudah: `core/curtain.js` mengabaikan klik navigasi kedua selama 400 ms setelah klik pertama, dan `pagereveal`/`pageswap` menangkap `ViewTransition.finished` yang ditolak supaya tidak tercatat sebagai galat. Diuji dengan ketukan cepat di tablet (§13.3).

### 8.7 M-10 kode penelitian di Profil

Sekarang kode tampil kecil di `<small>` (`profile.php:39`) dengan label `Game.researchCode` ("kode penelitianmu"). Sesudah:
- Kartu `.profile-research-code` di atas statistik: label "Kode penelitian" (`Game.researchCodeLabel`, dari 28 §16) dan kode dalam huruf besar berkontras tinggi, dengan pemisah yang jelas (`S03 – A07`, ditampilkan dengan sela tipis, disalin tanpa sela).
- Tampil di semua mode; di luar mode penelitian kodenya `GLT-…` seperti sekarang.
- Kunci lama `Game.researchCode` dibuang setelah tidak dipakai (id dan en).

### 8.8 M-11 data pendaftaran

Sudah diatur (K12): pendaftaran dengan token melewati provinsi, kabupaten, dan sekolah dalam mode penelitian (28 §6.5, K9), dan rincian perangkat yang disimpan dikurangi (29 §5.5). Tambahan di sini: `game/register.js` dan `game/login.js` hanya mengisi `device_json` dengan bidang yang masih disimpan (`DeviceProfile::UA_DATA_KEYS` versi 29, ukuran layar, `stage_w`), sehingga data yang tidak disimpan juga tidak dikirim. Tes memastikan kunci lain di `device_json` diabaikan server.

---

## 9. Skema database

**Migrasi `2026-01-01-004100_AddResearchModeData`** (blok `0041xx`; nomor sementara, disusun ulang menurut urutan digabung). Tidak menghapus data.

| Tabel atau kolom | Isi |
| --- | --- |
| `research_studies` | Kolom baru `idle_threshold_ms` INT UNSIGNED bawaan 180000, `rapid_guess_ms` INT UNSIGNED bawaan 3000, `min_stage_px` SMALLINT UNSIGNED bawaan 960 |
| `challenge_attempts` | Kolom baru `is_retake` TINYINT(1) bawaan 0. Pengisian data lama: 1 untuk setiap pengerjaan yang punya pengerjaan `completed` lebih awal dari siswa yang sama di tantangan yang sama |
| `audio_usage_events` | Kolom baru `clock_offset_ms` INT (null). Data lama tetap kosong |
| `challenge_attempt_segments` (baru) | `id`, `challenge_attempt_id` FK (`ON DELETE CASCADE`), `session_id` FK `game_sessions` (`ON DELETE CASCADE`), `field_session_id` FK (null, `ON DELETE SET NULL`), `segment_no` SMALLINT, `opened_at` DATETIME(3), `left_at` DATETIME(3) (null), `left_via` VARCHAR(30) (null). Unik (`challenge_attempt_id`, `segment_no`); indeks (`session_id`, `left_at`) |

Pengisian potongan untuk pengerjaan lama: satu potongan per pengerjaan (`started_at` sampai `completed_at`, atau terbuka), `left_via = migration`.

**Ikut diperbarui:**
- `tests/_support/Database/ResetTables.php` (skema SQLite).
- `SystemResetService`: `REQUIRED_MIGRATION` menjadi `004100`, tabel baru di daftar yang dikosongkan.
- `RetentionService::HARD_ORDER`: `challenge_attempt_segments` sebelum `challenge_attempts`, supaya jumlahnya masuk pratinjau (30 §7.3).
- Model baru `ChallengeAttemptSegmentModel`; `ChallengeAttemptModel`, `ResearchStudyModel`, `AudioUsageEventModel`: `allowedFields`.
- `Config\Gelita`: `$inputIdleLogMs = 20000`; daftar event menurut `EventCatalog` (29) mendapat `input_idle`.

---

## 10. Rute

| Metode dan jalur | Target | Filter | Catatan |
| --- | --- | --- | --- |
| `GET /api/attempts/{id}/objects/{ref}` | `ChallengeApiController::objectImage` | `apiSession` | M-04; jalur `/api` berbahasa Inggris |
| `POST /admin/studi/{id}/pengaturan` | `StudyController::updateSettings` | `staffCan:studies.manage` | Ambang dan `min_stage_px` (§6) |

Tidak ada rute siswa baru. Penghalang layar kecil, Kenali terkunci, dan *Ulangi* yang terkunci tampil di halaman yang sudah ada. Layar token (`/token`, 28) hanya menerima bidang tambahan `stage_w`.

---

## 11. Teks antarmuka

Teks siswa mengikuti `.claude/rules/ui-copy.md`; setiap kunci id punya pasangan en. Kalimat akhir mengikuti telaah ahli media dan uji keterbacaan ([24](24_UJI_KETERBACAAN.md)).

**Game (`app/Language/id/Game.php` dan `en/Game.php`; kunci JS di `Js.php`)**

| Kunci | Usulan (id) | Usulan (en) |
| --- | --- | --- |
| `smallScreenTitle` | Layarnya terlalu kecil | This screen is too small |
| `smallScreenBody` | GELITA perlu layar yang lebih besar, seperti laptop atau tablet. Bilang ke kakak peneliti, ya. | GELITA needs a bigger screen, like a laptop or tablet. Please tell the research team. |
| `smallScreenWarn` | Layarmu agak kecil, jadi tulisannya kecil juga. Lebih nyaman main di laptop atau tablet, ya. | Your screen is a bit small, so the text is small too. A laptop or tablet works better. |
| `smallScreenDismiss` | Tetap main | Play anyway |
| `retakeLater` | Kamu bisa mengulang tantangan ini setelah kelima tantangan di wilayah ini selesai, ya. | You can replay this challenge after you finish all five challenges in this region. |
| `kenaliAfterRegion` | Kisah wilayah ini terbuka setelah kamu menyelesaikan wilayahnya, ya. | This region's story opens after you finish the region. |
| `profileTableLabel` | Daftar hasil tantangan | Challenge results list |
| `engineCari` (dan tiga lainnya, bila belum ada) | Cari Objek | Find the Objects |

`researchCodeLabel` sudah ditetapkan di 28 §16. `Game.researchCode` dibuang (§8.7).

**Panel (`app/Language/id/Admin.php`, hanya Indonesia)**

| Kunci | Usulan |
| --- | --- |
| `studySettingsTitle` | Pengaturan pengambilan data |
| `studyIdleThreshold` | Batas jeda tanpa sentuhan (detik) |
| `studyIdleThresholdHelp` | Jeda yang lebih lama dari batas ini dipotong dari waktu aktif. Sementara 180 detik, ditetapkan ulang setelah uji coba. |
| `studyRapidGuess` | Batas tebakan cepat (detik) |
| `studyMinStage` | Lebar layar permainan terkecil (piksel) |
| `studyMinStageHelp` | Perangkat dengan layar permainan lebih kecil dari ini tidak bisa masuk sesi penelitian. |
| `studyIdleTooLow` | Batas jeda minimal 30 detik. |
| `smallScreenRefusedCount` | Perangkat layar kecil ditolak |

---

## 12. Titik sambung ke perubahan lain

| Perubahan | Yang disediakan bagian ini | Yang dirancang di sana |
| --- | --- | --- |
| Bagian 2 ([28](28_IMPLEMENTASI_SESI_LAPANGAN.md)) | Memakai `field_mode`, rencana pertemuan, `participant_progress`, layar token | — (bagian ini menambah `stage_w` di `/token` dan angka penolakan layar kecil di pemantauan) |
| Bagian 3 ([29](29_IMPLEMENTASI_DATA_PENELITIAN.md)) | `input_idle` untuk katalog, `audio_usage_events.clock_offset_ms`, potongan pengerjaan | Katalog event, `dialogue_completed`, lama mendengar lengkap, lembar unduhan |
| Bagian 4 ([30](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md)) | Tabel potongan untuk aturan hapus | Penghapusan per siswa (potongan ikut `ON DELETE CASCADE`) |
| Bagian 5 (`31`) | `is_retake`; pengerjaan pertama dilanjutkan | Buklet menentukan variasi pengerjaan pertama |
| Bagian 6 (`32`) | Pengerjaan ulang tanpa cerita penghubung; `input_idle` di layar Skenario Digital | Engine Skenario Digital, `scenario_open`, nama jenis "Skenario Digital" untuk M-07 |
| Bagian 8 (`34`) | Jam tersembunyi tidak berlaku untuk modul tes (tampilan Bootstrap tanpa jam); `input_idle` tidak dimuat di modul tes | Batas waktu tes tersembunyi |
| Bagian 9 (`35`) | Semua data mentah di §4, ambang di §6 | `active_ms`, `hidden_ms`, `idle_excess_ms`, `session_active_ms`, tersensor, dilewati, tebakan cepat, `kamus-1.1` |

---

## 13. Pengujian dan kriteria selesai

### 13.1 Tes baru

| Tes | Yang diperiksa |
| --- | --- |
| `AttemptResumeAcrossSessionsTest` | Pengerjaan terbuka di sesi A dilanjutkan di sesi B milik siswa yang sama: `session_id` pindah, butir tertutup tetap tertutup, `try_no` berlanjut, `challenge_opened {moved_from_session}`; dua permintaan bersamaan hanya memindah sekali (`Factories::injectMock` dengan entitas usang); siswa lain tidak bisa mengambilnya; `attempt_no` tidak berulang |
| `AttemptSegmentsTest` | Potongan dibuka dan ditutup di `openNode`, `abandonAttempt`, `closeAttempt`, `session_paused`; muat ulang tidak menambah potongan; penutupan bersamaan hanya sekali |
| `RetakeRuleTest` | Mode penelitian: tantangan tuntas tidak bisa diulang sebelum kelima tantangan wilayah tuntas (`RETAKE_LOCKED`, juga lewat API), bisa sesudahnya bila wilayah ada di rencana pertemuan; `is_retake` di semua mode; bintang terbaik seperti sekarang |
| `KenaliGateTest` | Mode penelitian: narasi Kenali wilayah yang belum tuntas tidak ada di halaman peta; ada setelah tuntas; di luar mode semua ada |
| `ResearchClockTest` | Mode penelitian: `#timer`, *Waktu* layar hasil, dan *Total waktu* Profil tidak dirender; ✓/✗ tetap; di luar mode semuanya tampil |
| `SmallScreenTokenTest` | `/token` menolak `stage_w` < `min_stage_px` dalam mode penelitian, menerima nilai kosong, dan mencatat audit tanpa data siswa |
| `StudySettingsTest` | Hanya `ketua` yang mengubah; batas isian; audit nilai lama dan baru; `idle_threshold_ms` di bawah 20 detik ditolak |
| `AudioOffsetTest` | `audio_usage_events.clock_offset_ms` sama dengan offset event mentah batch itu, juga untuk `pause` |
| `InputIdleEventTest` | `input_idle` diterima dengan payload yang dijepit (`idle_ms` bulat ≥ 0, `end` dari tiga nilai); bukan `SERVER_ONLY` |
| `HuntObjectImageTest` | Payload Cari Objek tidak memuat jalur berkas atau kode butir; `/api/attempts/{id}/objects/{ref}` mengirim gambar untuk pemiliknya, 404 untuk token asing dan pengerjaan siswa lain |
| `test/js/activity.test.mjs` (Node, bila ada kerangka tes JS) atau tes Playwright | Jeda ≥ 20 detik menghasilkan satu `input_idle`; gerakan tetikus tanpa klik tidak memutus jeda; layar tersembunyi memotong jeda |

### 13.2 Tes yang ditulis ulang

- Tes yang membuka lagi tantangan tuntas (`GradeFiveWorld`, mode mati) tetap lulus; yang memeriksa `attempt_no` per sesi disesuaikan ke per siswa.
- Tes payload Cari Objek yang membaca `media` disesuaikan ke URL token.
- `LanguageFilesTest` dengan kunci baru; `Game.researchCode` dibuang dari kedua bahasa.

### 13.3 Cek manual

- Dua siswa bergantian di satu perangkat: siswa A keluar saat jaringan putus, siswa B masuk; event A tidak tercatat atas nama B, dan terkirim saat A masuk lagi (M-05).
- Laptop 1366 × 768, tablet 1024 × 768, ponsel 844 × 390 mendatar: hanya ponsel yang ditolak dalam mode penelitian; peringatan di luar mode.
- Sumber halaman Cari Objek dan tab jaringan tidak memuat kode butir (M-04), juga di mode paket.
- axe di Gerbang dan Profil tanpa dua pelanggaran M-08.
- Ketukan cepat Lanjut di tablet: tidak ada galat transisi di konsol (M-09).
- Pengerjaan ditinggal di P2 (sesi lapangan ditutup), dilanjutkan di P5: dua potongan, percobaan berlanjut.

### 13.4 Selesai bila

- Semua tes di §13.1 dan §13.2 hijau; `vendor/bin/phpunit --no-coverage` hijau.
- Dengan `field_mode` mati, perilaku game sama dengan sebelum bagian ini, kecuali pengerjaan yang dilanjutkan lintas sesi (K2) dan perbaikan M-04–M-10 yang berlaku untuk semua.
- Data mentah di §4 tersedia untuk bagian 9, tanpa angka turunan yang dihitung di bagian ini.
- Glosarium dan dokumen di §14 diperbarui di cabang yang sama.

---

## 14. Dokumen yang ikut diperbarui saat implementasi

| Dokumen | Perubahan |
| --- | --- |
| `.claude/rules/glossary.md` | Baris baru: jeda masukan (`input_idle`), potongan pengerjaan (`attempt segment`), pengerjaan ulang (`retake`, `is_retake`), layar kecil (`small-screen gate`), pengaturan studi (`study settings`) |
| `docs/07` (fitur) dan `docs/08` (teknis) | Mode penelitian di game; `core/activity.js`; rute gambar benda |
| `docs/PROGRESS.md` | Baris kode bagian 7 |
| [02 → L0](02_LEMBAR_LAPANGAN.md#l0-daftar-periksa-persiapan-sesi) | "Perangkat: laptop, Chromebook, atau tablet mendatar ≥ 10 inci; ponsel ditolak sistem" dan "tutup peramban di antara siswa" |

---

## 15. Pertanyaan terbuka

Tidak menahan draf ini.

1. **Tempat T_idle dan T_teb.** Draf ini menaruhnya di `research_studies` (§6). Bagian 9 (`35`) ditulis paralel dan mungkin memilih tempat lain (misalnya tabel versi kamus). Dipilih satu saat sesi koordinator menyelaraskan 33 dan 35.
2. **Layar dengan bacaan panjang** butuh ambang jeda lain? (11 → *Aturan jeda*). Data `input_idle` membawa `screen`, jadi bisa diputuskan setelah uji coba tanpa perubahan sistem.
3. **Ambang 960 px** diperiksa di uji keterbacaan dan KG3a dengan perangkat sekolah yang sebenarnya; bila tablet 10 inci dengan bilah peramban lebar ternyata < 960, ambangnya diturunkan lewat pengaturan.
4. **Pengerjaan ulang di P2–P4 setelah wilayah tuntas** memakai sisa waktu pertemuan dan bisa menambah waktu bermain. Bila peneliti ingin pengerjaan ulang hanya di P5, cukup ubah syarat "wilayah di rencana pertemuan" menjadi "pertemuan 5"; tidak ada perubahan data.

---

## 16. Akibat ke dokumen lain

Dicatat di sini dan dibawa sesi koordinator setelah gelombang B digabung.

| Dokumen | Bagian | Perubahan |
| --- | --- | --- |
| [01](01_REVISI_10_ANALITIK.md) | *Catatan perubahan sistem* no. 5, 16, 19 | Tautan ke 33; no. 5 menyebut `input_idle` dan potongan pengerjaan; no. 16 menyebut ambang 960 px dan pengerjaan ulang yang tetap berbintang; no. 19 menyebut M-11 diatur di 28 dan 29 |
| [11](11_KAMUS_DATA.md) | Analitik 5, *Variabel*, *Yang harus disediakan sistem* | `input_idle` sebagai sumber jeda; `challenge_attempt_segments` sebagai sumber potongan; `duration_ms` tidak dipakai untuk pengerjaan berpotongan; `audio_usage_events.clock_offset_ms`; T_idle dan T_teb di `research_studies` (atau tempat yang dipilih, §15 no. 1); kalimat lama "tahap 3, setelah G2". Masuk `kamus-1.1` lewat bagian 9 |
| [12](12_CERITA_PENGHUBUNG_DAN_NARASI.md) | *Yang perlu disiapkan di tahap 3* no. 7 | Kenali selama studi dirancang di 33 §7.5 |
| [28](28_IMPLEMENTASI_SESI_LAPANGAN.md) | §6.4 layar token, §12 pemantauan, §18 | `/token` menerima `stage_w` dan menolak layar kecil; angka "perangkat layar kecil ditolak" di pemantauan; titik sambung no. 16 menunjuk 33 |
| [29](29_IMPLEMENTASI_DATA_PENELITIAN.md) | §4 katalog, §5.4, §12, §15 | `input_idle` (Peneliti, klien) di katalog; `audio_usage_events.clock_offset_ms` (K10); titik sambung no. 5 menunjuk 33 |
| [30](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md) | §7.3 yang dihapus sistem | `challenge_attempt_segments` ikut hapus per siswa |
| `35` (bagian 9) | Seluruh | Batas di 33 §4; membaca `input_idle`, potongan, offset audio, dan ambang di §6 |
| `32` (bagian 6) | Nama jenis | "Skenario Digital" sebagai nama jenis di pin dan kartu (M-07) |
| [10](10_TELAAH_PENDAHULUAN_MEDIA.md) | *Catatan temuan* | Tidak diubah (telaah selesai); tindak lanjut M-04–M-11 ada di 33 §8 |
| `.claude/rules/glossary.md` | — | Istilah baru di §14, diperbarui saat implementasi |

---

## 17. Keputusan pengembang

Satu putaran tanya, empat pertanyaan (1–2 November 2026). Pengembang memilih usulan di keempatnya. `asal: pengembang`.

1. **Pengerjaan pertama yang terputus pindah ke sesi game baru, dengan catatan potongan** (K3). Tidak dipilih:
   - pindah tanpa tabel potongan (potongan dibangun ulang dari event);
   - pengerjaan tetap di sesi lama dan semua pemeriksaan kepemilikan dilebarkan ke "siswa yang sama".
2. **Pengerjaan ulang tetap memberi bintang seperti sekarang**; analisis mengabaikannya lewat `is_retake` (K5). Tidak dipilih: pengerjaan ulang tidak mengubah bintang dan serpihan selama studi.
3. **Layar kecil: lebar panggung di bawah 960 px**, disimpan sebagai pengaturan studi (K7). Tidak dipilih: di bawah 1024 px; ponsel menurut jenis perangkat.
4. **Keluar mengirim antrean dulu** (paling lama 5 detik), sisa antrean tetap berpenanda sesi dan dibuang setelah 14 hari (K11). Tidak dipilih: kirim lalu hapus semua sisa; tetap seperti sekarang dan hanya menambah L0.
