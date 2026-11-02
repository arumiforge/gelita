# Dokumen Implementasi Bagian 9 — Lapis Data Turunan dan `kamus-1.1`

Per 2 November 2026 · Bagian dari [revisi 10 analitik](01_REVISI_10_ANALITIK.md) · perubahan sistem no. 9 di [01 → Catatan perubahan sistem](01_REVISI_10_ANALITIK.md#catatan-perubahan-sistem-untuk-tahap-nanti), ditambah usulan `kamus-1.1`

> **Status: `draf pengembang`, `asal: pengembang`** (disusun dengan bantuan AI, 1–2 November 2026). Ini dokumen, bukan perubahan sistem. Sejak 1 November 2026 sistem dibangun sekarang ([04](04_CATATAN_KEPUTUSAN.md), entri 1 November 2026), tetapi tidak dipakai dengan anak sebelum persetujuan etik terbit. Perilaku penelitian ada di balik sakelar studi yang mati secara bawaan, jadi `main` tetap bisa dirilis.
>
> **Bagian 9 dari 9, dikerjakan paling akhir** (gelombang kode F, setelah bagian 7 dan 8). Lapis turunan menghitung dari data mentah semua bagian lain: peran dan kemampuan dari [27](27_IMPLEMENTASI_PERAN_PENELITI.md), sesi lapangan dan kemajuan per siswa dari [28](28_IMPLEMENTASI_SESI_LAPANGAN.md), data mentah baru, lembar lapangan, dan `ResearchDictionary` dari [29](29_IMPLEMENTASI_DATA_PENELITIAN.md), izin dan status mundur dari [30](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md), serta kode kesalahan (bagian 5), Skenario Digital (bagian 6), waktu aktif mentah (bagian 7), dan modul tes, angket, dan skala sikap (bagian 8). Bagian 5–8 (`31`–`34`) ditulis paralel dengan dokumen ini; datanya disebut sebagai titik sambung dan anggapannya didaftar di [§20](#20-akibat-ke-dokumen-lain).
>
> **`kamus-1.0` tidak diubah.** [11](11_KAMUS_DATA.md) tetap kamus yang berlaku. `kamus-1.1` di dokumen ini adalah **usulan versi**: 11 sendiri menulis bahwa variabel tes dan angket, dan ambang yang ditetapkan ulang, masuk `kamus-1.1` setelah uji coba. Sampai 11 diubah, sistem menandai hasilnya `kamus-1.1-usulan` ([§8](#8-kamus-11-usulan)).

---

## Daftar isi

0. [Cara memakai dokumen ini](#0-cara-memakai-dokumen-ini)
1. [Kebutuhan, sumber, dan tempatnya](#1-kebutuhan-sumber-dan-tempatnya)
2. [Keputusan yang dikunci](#2-keputusan-yang-dikunci)
3. [Yang tidak berubah](#3-yang-tidak-berubah)
4. [Bentuk lapis turunan](#4-bentuk-lapis-turunan)
5. [Ambang di konfigurasi studi](#5-ambang-di-konfigurasi-studi)
6. [Aturan hitung per tabel](#6-aturan-hitung-per-tabel)
7. [`consent_ok` dan data sampai tanggal mundur](#7-consent_ok-dan-data-sampai-tanggal-mundur)
8. [`kamus-1.1` usulan](#8-kamus-11-usulan)
9. [Kapan dihitung](#9-kapan-dihitung)
10. [Unduhan dan layar data](#10-unduhan-dan-layar-data)
11. [Hak akses dan menu](#11-hak-akses-dan-menu)
12. [Skema database](#12-skema-database)
13. [Rute dan perintah spark](#13-rute-dan-perintah-spark)
14. [Teks antarmuka](#14-teks-antarmuka)
15. [Titik sambung ke perubahan lain](#15-titik-sambung-ke-perubahan-lain)
16. [Pengujian dan kriteria selesai](#16-pengujian-dan-kriteria-selesai)
17. [Dokumen yang ikut diperbarui saat implementasi](#17-dokumen-yang-ikut-diperbarui-saat-implementasi)
18. [Pertanyaan terbuka](#18-pertanyaan-terbuka)
19. [Keputusan pengembang](#19-keputusan-pengembang)
20. [Akibat ke dokumen lain](#20-akibat-ke-dokumen-lain)

---

## 0. Cara memakai dokumen ini

**Urutan.** Kode bagian ini dikerjakan paling akhir, di gelombang F setelah bagian 7 (`33`) dan 8 (`34`) ([00 → Tahap dokumen 3](00_INDEKS.md)). Sebelum mulai, cocokkan setiap baris [§15](#15-titik-sambung-ke-perubahan-lain) dengan kode yang sudah digabung: nama kolom dan event dari bagian 5–8 di sini masih anggapan.

**Batas.** Dokumen ini mengatur:
- satu tempat yang menghitung semua data turunan di 11 (kode kesalahan per percobaan, waktu aktif, tiga jenis "dilewati", tersensor, tebakan cepat, dan penanda untuk analisis), berversi dan diuji otomatis;
- ambang jeda dan tebakan cepat sebagai pengaturan studi;
- `consent_ok` dan pemotongan data pada tanggal mundur;
- usulan `kamus-1.1`: variabel tes, angket, skala sikap, izin, dan pembetulan data, ditambah penegasan yang tertunda dari 29 dan 30;
- lembar turunan di unduhan `ketua` dan di layar data.

Yang **tidak** diatur: cara data mentah direkam (bagian 3, 5–8), analisis statistik (lapis 3, di luar sistem: Rasch, model campuran, κ), dan isi teks kamus 11 itu sendiri.

**Saat implementasi:**
- Satu cabang pendek dari `main`, commit berbahasa Inggris.
- `vendor/bin/phpunit --no-coverage` hijau sebelum push.
- Jangan menjalankan migrasi, `gelita:reset`, atau skrip rilis di server produksi. Migrasi bagian ini hanya menambah tabel dan kolom; tidak ada data yang dihapus.
- Tulisan yang bisa bersaing (dua hitung ulang bersamaan, penggantian hasil yang berlaku) memakai UPDATE bersyarat, bukan `SELECT … FOR UPDATE` (aturan kerja repo, bagian *Concurrency*).

**Istilah.** *Lapis turunan* = lapis 2 di [01 → Olah data](01_REVISI_10_ANALITIK.md#olah-data-di-sistem-atau-di-alat-lain). *Putaran hitung* = satu kali menghitung seluruh lapis turunan satu studi. *Sesi game* = satu baris `game_sessions` (panel: *sesi bermain*).

**Rujukan kode.** Ditulis per berkas dan metode, dengan nomor baris per 2 November 2026 (`main` di `97f82d5`). Nomor baris bisa bergeser, terutama setelah bagian 1–8 digabung, jadi cari nama metodenya.

---

## 1. Kebutuhan, sumber, dan tempatnya

| # | Kebutuhan | Sumber | Di sini |
| ---: | --- | --- | --- |
| 1 | Satu tempat menghitung kode kesalahan, waktu aktif, "dilewati", tersensor, tebakan cepat; berversi dan diuji otomatis | 01 no. 9; [01 → Olah data](01_REVISI_10_ANALITIK.md#olah-data-di-sistem-atau-di-alat-lain) lapis 2 | §4, §6, §16 |
| 2 | Definisi beku 10 analitik, data yang dinilai, data hilang, penanda | [11 → Data yang dinilai](11_KAMUS_DATA.md#data-yang-dinilai), [Sepuluh analitik](11_KAMUS_DATA.md#sepuluh-analitik) | §6 |
| 3 | Ambang T_idle (sementara 3 menit) dan T_teb (sementara 3 detik) adalah pengaturan di konfigurasi studi; mengubahnya menghitung ulang dari data mentah, dan nilainya tercatat bersama versi kamus | [11 → Analitik 5](11_KAMUS_DATA.md#analitik-5-waktu-pengerjaan); 11 → *Yang harus disediakan sistem* | §5, §9 |
| 4 | Setiap tabel turunan menyimpan `dictionary_version`; diuji dengan contoh data yang hasilnya dihitung tangan dari kamus | [11 → Versi dan cara berubah](11_KAMUS_DATA.md#versi-dan-cara-berubah); 11 → *Yang harus disediakan sistem* | §4, §16 |
| 5 | Penanda dari lembar lapangan (L1–L4) digabung dengan aturan jendela waktu | [11 → Aturan gabung lembar lapangan](11_KAMUS_DATA.md#aturan-gabung-lembar-lapangan); [29 → §10](29_IMPLEMENTASI_DATA_PENELITIAN.md#10-templat-dan-impor-lembar-lapangan-perubahan-no-15) | §6.1, §6.6 |
| 6 | Titik sambung bagian 3: `decision_ms`, `rapid_guess`, `near_miss_count`, `material_returns`, penanda L1–L4, `outside_session` | [29 → §15](29_IMPLEMENTASI_DATA_PENELITIAN.md#15-titik-sambung-ke-perubahan-lain) | §6 |
| 7 | Titik sambung bagian 4: `consent_ok` dari daftar izin berkode, data sampai tanggal mundur, `status_on`, `registration_corrected`, `risk_reconsent` | [30 → §18](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md#18-titik-sambung-ke-perubahan-lain), §5.3, §9, §10.1 | §7, §8 |
| 8 | Variabel tes dan angket masuk `kamus-1.1` setelah uji coba | [13 → Penskoran](13_TES_AWAL_AKHIR.md#penskoran-dan-pemasukan-data); [16 → Variabel untuk kamus data](16_ANGKET_AKSES_DIGITAL.md#variabel-untuk-kamus-data) | §6.5, §8 |
| 9 | Variabel skala sikap masuk `kamus-1.1` | [17 → Variabel untuk kamus data](17_SKALA_SIKAP.md#variabel-untuk-kamus-data) | §6.5, §8 |
| 10 | Kunci jawaban dan jawaban butir risiko per baris tidak pernah keluar dari server | [29 → §7.6](29_IMPLEMENTASI_DATA_PENELITIAN.md#76-butir-risiko-daring) (K9); aturan kerja repo | §10.3 |
| 11 | Penegasan kamus yang ditunda ke `kamus-1.1` oleh bagian 3 (`ready_ms`, rujukan lama `last_input_ms` Cari Objek, `empty_tap` berupa koordinat, `passage_reopened`, `offset_known`, `is_touch`) | [29 → §20](29_IMPLEMENTASI_DATA_PENELITIAN.md#20-akibat-ke-dokumen-lain), baris 11 | §8 |

---

## 2. Keputusan yang dikunci

K1–K4 diputuskan pengembang ([§19](#19-keputusan-pengembang)). Sisanya menurunkan 01, 11, 13, 16, 17, 29, dan 30, atau berasal dari pencarian kode, dan dicatat di [04](04_CATATAN_KEPUTUSAN.md) untuk ditinjau.

| Kode | Keputusan |
| --- | --- |
| K1 | **Lapis turunan disimpan di tabel** (`derived_*`), diisi satu kalkulator berversi per putaran hitung satu studi. Unduhan dan layar data membaca tabel itu. Tidak dipilih: dihitung saat unduhan dibuat. |
| K2 | **Ambang di kolom studi**: `research_studies.idle_threshold_ms` dan `rapid_guess_ms`, bawaan dari `Config\Gelita`, diubah `ketua` di halaman studi dengan log audit, dan terkunci setelah versi dikunci di G4. |
| K3 | **Dihitung ulang saat unduhan bila basi, ditambah tombol dan perintah.** Unduhan menghitung ulang studi lebih dulu bila data mentah, lembar lapangan yang diimpor, izin, atau ambang berubah sejak putaran terakhir. `ketua` juga punya tombol *Hitung ulang*, dan ada perintah spark. |
| K4 | **Data mentah tetap, lapis turunan dipotong pada saat mundur.** Untuk status `withdrawn_keep`, semua yang terjadi sesudah saat mundur tidak masuk tabel turunan mana pun; peserta tetap `consent_ok = 1` dengan `withdrawn_on` terisi. |
| K5 | **Satu kalkulator, fungsi murni.** `App\Libraries\Derived\*` menerima larik data mentah yang sudah dimuat dan ambang, lalu mengembalikan baris turunan, tanpa membaca database atau jam sendiri. `DerivedDataService` yang memuat, memanggil, dan menyimpan. Ini yang membuat contoh data hitungan tangan bisa diuji persis. |
| K6 | **Setiap baris turunan membawa `run_id`**, dan setiap putaran mencatat `dictionary_version`, ambang, jam mulai dan selesai, dan sidik jari data masukan di `derived_runs`. Kolom `dictionary_version` juga ada di setiap tabel turunan, seperti diminta 11. |
| K7 | **Hasil yang berlaku diganti sekaligus.** Putaran baru menulis baris dengan `run_id` baru; setelah selesai, `research_studies.derived_run_id` diganti lewat UPDATE bersyarat. Pembaca selalu memakai `derived_run_id`, jadi tidak pernah membaca putaran setengah jadi. |
| K8 | **Versi kamus satu konstanta.** `ResearchDictionary::VERSION` (29 K10) dibaca kalkulator dan lembar *Kamus Data*. Selama 11 masih `kamus-1.0`, entri baru dari dokumen ini ditandai `usulan` dan putaran mencatat `kamus-1.1-usulan` ([§8](#8-kamus-11-usulan)). |
| K9 | **Lapis turunan hanya untuk studi dengan `field_mode` menyala.** Di luar mode penelitian (sakelar mati, bawaan) tidak ada yang dihitung, tombol tidak tampil, dan unduhan tidak memuat lembar turunan. |
| K10 | **Variabel turunan angket dan skala sikap dihitung di sini**, bukan di modul bagian 8. Modul menyimpan jawaban mentah dan skor tes; rumus 16 dan 17 (`access_index`, `sikap_score`, dan lainnya) dihitung lapis turunan, supaya ada satu tempat yang berversi. Kemampuan Rasch tes (`*_theta`) tidak dihitung sistem; kolomnya hanya ada di kamus sebagai variabel lapis 3. |
| K11 | **Jarak ketukan kosong dihitung di server.** `near_miss_count` dan `empty_tap_count` memakai koordinat `empty_tap` (29 §6.2) dan bagian ketuk kunci petunjuk dari bank; ambang 24 dp diubah ke persen adegan dengan ukuran panggung yang sama dengan pemeriksaan area ketuk (perubahan no. 17, bagian 5). |
| K12 | **Lembar turunan tidak memuat jawaban.** Tabel dan lembar turunan memuat kode, skor, dan penanda, tidak pernah teks jawaban, kunci, atau jawaban butir risiko daring. `first_action_key` D2 adalah kunci pilihan, bukan teks. |
| K13 | **Data uji dan simulasi tidak dihitung.** Sekolah `V01` (akun `uji.*`) dan nama pengguna berawalan `sim_` dikeluarkan, sama seperti `StaffScope::research()` di 29 §7.1. Sekolah berawalan `U` dihitung dengan `is_pilot = 1`. |
| K14 | **Tidak ada kemampuan baru.** Hitung ulang dan lembar turunan memakai `research.export` dan `research.browse`; ambang memakai `studies.manage` (semuanya `ketua`, [27 §5.1](27_IMPLEMENTASI_PERAN_PENELITI.md#51-peta-kemampuan)). |

---

## 3. Yang tidak berubah

| Bagian | Aturan |
| --- | --- |
| Data mentah | `challenge_attempts`, `item_responses`, `item_tries`, `game_event_logs`, `audio_usage_events`, `field_sheet_rows`, tabel modul tes: hanya ditambah, tidak pernah diubah lapis turunan (01, lapis 1). Lapis turunan hanya membaca |
| Penilaian di game | Benar atau salah, bintang, dan Serpihan Cahaya tetap dihitung `ChallengeService` dan `ScoringService` saat bermain. Lapis turunan tidak mengubah apa yang dilihat siswa |
| Kode kesalahan mentah | `error_code` disimpan bagian 5 di setiap percobaan saat dinilai dan tidak ditulis ulang. Untuk analisis, lapis turunan memakai kode bank terbaru: bila matriks kode pilihan di bank berubah setelah percobaan, kode diturunkan ulang dari pilihan yang tersimpan dengan `ErrorCodeResolver` bagian 5 ([§19](#19-keputusan-pengembang) no. 5, 2 November 2026) |
| Pembuatan berkas unduhan | `ExportService::build()` (`app/Services/ExportService.php:58`), XLSX dan CSV-ZIP, SHA-256, retensi 7 hari, riwayat unduhan 29 §9 |
| Lembar mentah | Lembar `peserta` … `butir` dan lembar lapangan 29 §7.2 tetap ada dan tetap mentah |
| Kamus | `kamus-1.0` di 11 tidak diubah dokumen ini |
| `game_sessions.duration_ms` | Tetap diisi `SessionService::heartbeat()` (`app/Services/SessionService.php:397-416`) dengan aturan denyut sendiri (`sessionIdleMinutes` 45, `Config/Gelita.php:350`); tidak dipakai untuk analisis (11 → Analitik 5) |

---

## 4. Bentuk lapis turunan

### 4.1 Kelas

```text
app/Libraries/Derived/
  Thresholds.php          // value object: idle_ms, rapid_ms, near_miss_dp, input_gap_min_ms
  RunInput.php            // raw rows of one study, loaded once, ordered
  Timeline.php            // active-time arithmetic (segments, hidden, idle excess, narration)
  TryCalculator.php       // per try: decision_ms, rapid_guess, error_code_analysis, flags
  ItemCalculator.php      // per participant x item: first try, final, censored, missing, change after feedback
  AttemptCalculator.php   // per attempt: scored or retake, active time, returns, exits
  ChallengeCalculator.php // per participant x challenge: completion status, skipped (a) and (b)
  ParticipantCalculator.php // consent_ok, analysable, complete, rapid_share, questionnaire and attitude derivations
  FieldSheetFlags.php     // L1-L4 window joins (shared_device, helped_*, deviation_code, observation codes)
app/Services/DerivedDataService.php   // load, call calculators, write rows, switch the current run
app/Commands/DerivedCompute.php       // gelita:turunan:hitung
```

- **Fungsi murni** (K5). Kalkulator tidak memanggil `db_connect()`, `time()`, `date()`, `config()`, atau `service()`. Semua masukan lewat `RunInput` dan `Thresholds`; jam putaran diberikan dari luar. Tes memanggil kalkulator langsung dengan larik contoh.
- **Urutan hitung:** percobaan → butir → pengerjaan → tantangan → peserta → sesi lapangan, karena setiap tingkat merangkum tingkat di bawahnya. `FieldSheetFlags` dipanggil sebelum percobaan.
- **Memuat per peserta.** `DerivedDataService` memuat dan menghitung satu peserta sekali jalan (semua sesi game, pengerjaan, percobaan, event, dan baris lembar lapangannya), lalu menulis barisnya dalam satu transaksi. Ini menjaga memori tetap kecil untuk ± 465 anak di semua kegiatan (26) dan sejalan dengan penghapusan per peserta di bagian 4.
- **Tanpa bagian yang berbeda untuk MySQL dan SQLite.** Pemuatan memakai query builder biasa; semua hitungan di PHP. Tes SQLite dan server MariaDB menghasilkan baris yang sama.

### 4.2 Masukan satu putaran

| Masukan | Tabel | Dari bagian |
| --- | --- | --- |
| Peserta, sekolah, rombel, kode | `participants`, `schools.research_code` | 28 |
| Izin dan status | `participant_consents`, `participants.deactivated_at`, `participants.corrected_at` | 30 |
| Sesi lapangan, pertemuan, wilayah yang dibuka | `field_sessions`, rencana pertemuan, `game_sessions.field_session_id` | 28 |
| Pengerjaan, jawaban, percobaan | `challenge_attempts`, `item_responses`, `item_tries` (termasuk `ready_ms` 29, `error_code` dan kolom D2 dari 31/32) | 29, 31, 32 |
| Event peneliti | `game_event_logs` jenis Peneliti di `EventCatalog` (29 K7), dengan `occurred_at_corrected` | 29 |
| Narasi diputar | `audio_usage_events` per pemutaran | 29 §5.4 |
| Jeda masukan ≥ 20 detik | Event mentah dari bagian 7 (anggapan: `input_gap`) | 33 |
| Butir dan adegan | `challenge_items` (dimensi, sub-aspek, level AKM, babak, `testlet_key`), bagian ketuk Cari Objek | 31 |
| Lembar lapangan | `field_sheet_rows` impor utama yang berlaku | 29 |
| Tes, angket, skala sikap | Tabel jawaban modul (anggapan nama di §15) | 34 |

### 4.3 Sidik jari masukan

Setiap putaran menyimpan `input_fingerprint`: SHA-256 dari daftar angka yang cukup untuk tahu apakah data mentah berubah, tanpa membaca semuanya:
- `MAX(id)` dan `COUNT(*)` `item_tries`, `game_event_logs`, `audio_usage_events` untuk studi itu;
- `MAX(updated_at)` `challenge_attempts`, `participant_consents`, `participants`;
- `MAX(id)` `field_sheet_imports` dan `MAX(superseded_at)`;
- `MAX(id)` dan `MAX(updated_at)` tabel modul tes;
- `idle_threshold_ms`, `rapid_guess_ms`, dan `ResearchDictionary::VERSION`.

Putaran **basi** bila sidik jari sekarang berbeda dari sidik jari putaran yang berlaku ([§9](#9-kapan-dihitung)). Penghapusan data (bagian 4) mengubah `COUNT(*)`, jadi putaran sesudah penghapusan selalu basi.

---

## 5. Ambang di konfigurasi studi

| Kolom `research_studies` | Bawaan | Arti | Dari |
| --- | --- | --- | --- |
| `idle_threshold_ms` | 180000 | T_idle: jeda tanpa masukan yang lebih panjang dari ini dipotong bagian lebihnya | 11 → Analitik 5 |
| `rapid_guess_ms` | 3000 | T_teb: `decision_ms` di bawah ini = tebakan cepat | 11 → Analitik 6 |
| `thresholds_locked_at` | null | Diisi saat versi dikunci di G4; sesudahnya kedua ambang tidak bisa diubah | 01, gerbang G4 |

- **Bawaan dari `Config\Gelita`**: properti baru `$derivedIdleThresholdMs = 180000` dan `$derivedRapidGuessMs = 3000`, dipakai saat studi dibuat. Angka di kode hanya bawaan; nilai yang berlaku selalu dari studi.
- **Halaman studi** (`studies.manage`, `ketua`): dua isian dalam detik ("Ambang jeda" 30–1800, "Ambang tebakan cepat" 1–30), dengan catatan "Sementara. Ditetapkan ulang setelah uji coba." Disimpan dengan UPDATE bersyarat pada `updated_at` dan log audit `study_thresholds_changed` `{from, to}`.
- **Mengunci.** Tombol *Kunci ambang* (`ketua`) mengisi `thresholds_locked_at` dengan log audit `study_thresholds_locked`. Tidak ada tombol membuka kunci di panel; membukanya hanya lewat perintah spark dengan alasan, untuk kesalahan yang dicatat sebagai penyimpangan (11 → *Versi dan cara berubah*, baris Beku).
- **Mengubah ambang tidak mengubah data mentah**: putaran berikutnya basi dan dihitung ulang (K3), dan nilai ambang yang dipakai tercatat di `derived_runs` dan di lembar *Kamus Data*.
- **Ambang lain tetap konstanta kode**, karena 11 membekukannya: 24 dp untuk NON-SEN, 20 detik untuk jeda mentah yang dicatat (bagian 7), dan 80% (49 dari 61) untuk `analysable`. Ketiganya ada di `Thresholds` dengan nama, supaya `kamus-1.1` bisa mengubahnya di satu tempat.
- **Tes awal–akhir.** 13 menyebut ambang tebakan cepat tes ditinjau tersendiri. Sampai ada keputusan, `rapid_guess` jawaban tes memakai `rapid_guess_ms` yang sama ([§18](#18-pertanyaan-terbuka) no. 2).

---

## 6. Aturan hitung per tabel

Setiap aturan di bawah adalah aturan di 11; kolom kanan menyebut bagian 11 yang diturunkan. Bila aturan di sini dan 11 berbeda, 11 yang benar dan dokumen ini dibetulkan. Kosong selalu berarti "tidak ada data", tidak pernah 0 (11 → *Data hilang*).

**Penyaring yang berlaku di semua tabel:**
1. Hanya peserta studi itu, tanpa `V01` dan `sim_` (K13).
2. Data sesudah saat mundur dibuang (K4, [§7](#7-consent_ok-dan-data-sampai-tanggal-mundur)).
3. Data yang sudah dihapus lewat permintaan hapus (`deleted_at` terisi, atau baris yang sudah tidak ada) tidak dihitung.
4. Event dan percobaan di luar sesi lapangan (`game_sessions.field_session_id` kosong atau di luar jam buka–tutup sesi lapangannya) tetap dihitung barisnya dengan `outside_session = 1`, supaya jumlahnya terlihat, lalu dikeluarkan dari semua ringkasan (11 → *Kunci penggabungan dan waktu*).

### 6.1 `derived_tries`: satu baris per percobaan

| Kolom | Aturan | 11 |
| --- | --- | --- |
| `try_id`, `attempt_id`, `participant_code`, `field_session_code`, `item_key`, `try_no` | Kunci, dari data mentah | Kunci penggabungan |
| `is_scored_attempt`, `is_retake` | Dari `derived_attempts` (§6.3) | Pengerjaan pertama |
| `scored_try_no` | Nomor percobaan butir itu di pengerjaan pertama setelah aturan cadangan: percobaan butir yang sama diurutkan menurut waktu lintas pengerjaan sebelum tantangan tertutup pertama kali; hanya tiga yang pertama diberi nomor | Pengerjaan pertama, aturan cadangan |
| `anomaly` | `extra_try` untuk percobaan sesudah yang ketiga; selain itu kosong | Sama |
| `error_code` | Kode dari bank terbaru untuk pilihan percobaan itu (`ErrorCodeResolver` bagian 5); kosong untuk percobaan benar dan Susun Gambar. Kode saat percobaan tetap di `item_tries.error_code` dan masuk lembar sebagai `error_code_at_try` | Analitik 6 |
| `decision_ms` | `last_input_ms − ready_ms` (29 §6.1) untuk Benar atau Salah, kartu jangkar, dan Cari Objek; untuk tindakan Skenario Digital dari kolom bagian 6. Kosong bila salah satu kosong, bila hasilnya negatif, atau untuk Isi Rumpang dan Susun Gambar | Analitik 6, waktu putus |
| `rapid_guess` | 1 bila `decision_ms < rapid_guess_ms`, 0 bila tidak, kosong bila `decision_ms` kosong | Analitik 6, NON-TEB |
| `reason_decision_ms`, `reason_rapid` | Sama untuk alasan Skenario Digital | Analitik 6 |
| `error_code_analysis` | `error_code`, kecuali percobaan salah dengan `rapid_guess = 1` diberi `NON-TEB` | Analitik 6 |
| `last_try_exhausted` | 1 pada percobaan salah yang menghabiskan kesempatan terakhir butir itu (NON-HABIS) | Analitik 6 |
| `hint_before` | `item_tries.hint_opened_before` | Analitik 7 |
| `item_hint_before`, `item_narration_before` | 1 bila Bantuan khusus butir itu dibuka, atau narasi butir itu diputar siswa (bukan *autoplay*), sebelum `occurred_at` percobaan | Analitik 7 |
| `reason_code`, `action_score` | Disalin dari kolom Skenario Digital bagian 6 | Analitik 10 |
| `shared_device` | 1 bila L2 sesi itu mencatat *Pakai bareng: Ya* untuk kursi siswa, dalam jendela sampai jam pindah perangkat | Penanda, L2 |
| `helped_content`, `helped_technical`, `encouraged`, `disturbed` | 1 bila ada baris L3 (ISI/STR/BHS, TKN, SMG, GG-*) untuk siswa atau kursinya, dari 1 menit sebelum jam kejadian sampai 2 menit sesudahnya (atau sesudah jam selesai) | Penanda, L3 |
| `deviation_code` | Kode DEV-* L1 yang mengenai siswa dari jam kejadian sampai 5 menit sesudahnya, atau sampai akhir lamanya; beberapa kode dipisah titik koma | Penanda, L1 |
| `outside_session` | Lihat penyaring no. 4 | Kunci penggabungan |

Jendela lembar lapangan memakai jam WIB lembar dan `occurred_at` percobaan (waktu server). Kursi dicocokkan ke kode peserta lewat L2 (`field_sheet_rows.seat_no`).

### 6.2 `derived_items`: satu baris per peserta × butir

Satu baris untuk **setiap butir yang dinilai** di variasi buklet siswa, juga yang tanpa percobaan, supaya data hilang terlihat. Hanya pengerjaan pertama dan percobaan dengan `scored_try_no`.

| Kolom | Aturan | 11 |
| --- | --- | --- |
| `participant_code`, `item_key`, `node_ref`, `round`, `dimension`, `subaspect`, `akm_level`, `testlet_key` | Kunci dan label butir (bagian 5) | Variabel butir |
| `first_try_score` | Skor percobaan pertama: 0–1, atau 0–2 untuk D2 | Analitik 3 |
| `first_try_correct` | 1 bila `first_try_score` maksimal | Analitik 3 |
| `missing_reason` | `locked`, `not_started`, `no_try`, atau kosong bila ada percobaan | Data hilang |
| `final_correct`, `final_score` | Tertutup benar (1), tertutup tidak benar (0), kosong bila tanpa percobaan; D2 `final_score` = skor tindakan percobaan terakhir | Analitik 2 |
| `tries_used`, `solved_on_try` | Banyak percobaan (1–3); percobaan benar pertama | Analitik 4 |
| `censored`, `censor_reason` | 1 bila tidak pernah benar; `exhausted` (tiga dipakai) atau `left` (kurang dari tiga, butir tidak tertutup). Tidak pernah diisi 3 atau 4 | Analitik 4 |
| `first_error_code`, `first_error_code_analysis` | Kode percobaan pertama yang salah | Analitik 6, ringkasan utama |
| `change_after_feedback`, `change_after_feedback_2` | `fixed`, `same_error`, `other_error`, `no_second_try` untuk percobaan 1→2 dan 2→3; D2 dinilai dari kunci pilihan tindakan, butir lain dari `error_code` mentah | Analitik 9 |
| `answer_unchanged`, `score_change` | Jawaban percobaan kedua sama persis (dibandingkan di server, jawaban tidak ditulis); perubahan skor D2 | Analitik 9 |
| `near_miss_count`, `empty_tap_count` | Cari Objek: `empty_tap` saat petunjuk ini aktif, dengan jarak ke bagian ketuk kunci ≤ 24 dp atau lebih jauh; `overflow` dijumlahkan ke `empty_tap_count` | Analitik 6, NON-SEN (K11) |
| `item_total_ms`, `item_active_ms` | Cari Objek: jumlah selang saat petunjuk ini aktif sampai tertutup; Skenario Digital: situasi tampil sampai skenario tertutup. Aturan aktif §6.7. Kosong untuk Isi Rumpang dan Benar atau Salah | Analitik 5 |
| `first_input_ms`, `last_input_ms` | Disalin dari percobaan pertama (perkiraan untuk Isi Rumpang dan Benar atau Salah) | Analitik 5 |
| `first_action_key`, `first_action_score`, `first_action_code`, `first_reason_code`, `action_reason_pattern` | D2 saja; pola `safe_expected`, `safe_rule`, `safe_wrong_reason`, `partial`, `risky` | Analitik 10 |
| `shared_device`, `helped_content`, `rapid_guess` | Dari percobaan pertama | Penanda |

`reason_level` tidak diisi sampai urutan alasan ditetapkan setelah uji coba (11 → Analitik 10); kolomnya disiapkan di `kamus-1.1` sebagai kosong.

### 6.3 `derived_attempts`: satu baris per pengerjaan

| Kolom | Aturan | 11 |
| --- | --- | --- |
| `attempt_id`, `participant_code`, `node_ref`, `attempt_no` | Kunci | — |
| `is_scored_attempt` | 1 untuk pengerjaan dengan variasi buklet siswa sampai tantangan tertutup pertama kali; bila ada dua sebelum tertutup (gangguan), keduanya 1 dan aturan cadangan §6.1 berlaku | Pengerjaan pertama |
| `is_retake` | 1 untuk pengerjaan sesudah tantangan tertutup | Pengerjaan ulang |
| `segments` | Banyak potongan (sesi game berbeda) | Analitik 5 |
| `total_ms`, `hidden_ms`, `idle_excess_ms`, `active_ms` | §6.7 | Analitik 5 |
| `cumulative_active_ms` | Jumlah `session_active_ms` peserta sebelum pengerjaan ini dimulai | Analitik 5, kovariat |
| `hint_count` | Disalin `challenge_attempts.hint_count` | Analitik 7 |
| `instruction_replays` | Banyak pemutaran ulang narasi instruksi oleh siswa (kode konteks bagian 7 atau 6, anggapan di §15) | Analitik 7 |
| `material_returns` | (a) `library_opened` saat pengerjaan belum tertutup + (b) `passage_reopened` (29 §6.3) | Analitik 8 |
| `exit_count` | Banyak `challenge_abandoned` pengerjaan ini sebelum tertutup | Analitik 8 |
| `reading_max_pct` | `reading_progress` terbesar | Analitik 8 |
| `meeting_no`, `field_session_code` | Dari sesi game potongan pertama | Kunci |

### 6.4 `derived_challenges`: satu baris per peserta × tantangan (15)

| Kolom | Aturan | 11 |
| --- | --- | --- |
| `completion_status` | `solved`, `exhausted`, `unfinished`, `not_started`, `locked`, dinilai saat putaran dari pengerjaan pertama | Analitik 1 |
| `skipped_failed_count` | Jenis (a): banyak petunjuk Cari Objek (`clue_failed`, ditulis `ChallengeService.php:1009`) dan skenario Skenario Digital (event bagian 6) yang ditutup setelah kesempatan ketiga | Analitik 1 |
| `skipped_left_count` | Jenis (b): banyak `challenge_abandoned` (ditulis `ChallengeService.php:732`, juga `RetentionService` `via: retention`) di pengerjaan pertama yang **tidak** diikuti aktivitas pengerjaan itu di sesi lapangan yang sama | Analitik 1 |
| `closed_meeting_no` | Pertemuan saat tantangan tuntas | Analitik 1 |

`not_started` = tantangan terbuka untuk siswa itu (wilayahnya dibuka rencana pertemuan sesi lapangan yang dihadirinya, dan tantangan sebelumnya di wilayah itu tuntas) tetapi tanpa pengerjaan. Bila wilayah tidak pernah dibuka untuk siswa itu atau tantangan sebelumnya belum tuntas: `locked`. Keterbukaan dibaca dari `participant_progress` dan rencana pertemuan (28), bukan dari tampilan.

**"Pengambilan data selesai"** di 11 dibaca sebagai saat putaran dihitung. Selama studi masih berjalan, `not_started` dan `unfinished` bisa berubah; lembar *Kamus Data* menulis jam putaran ([§18](#18-pertanyaan-terbuka) no. 3).

### 6.5 `derived_participants`: satu baris per peserta

| Kelompok | Kolom | Aturan | Sumber |
| --- | --- | --- | --- |
| Identitas berkode | `participant_code`, `school_code`, `rombel`, `booklet` | Dari 28 | 11 |
| Izin | `consent_ok`, `withdrawn_on`, `withdrawn_at`, `cutoff_basis` | §7 | 30 |
| Uji coba | `is_pilot` | 1 bila kode sekolah berawalan `U` | 11 |
| Kelengkapan | `analysable`, `complete` | 11 → *Data siswa yang bisa dianalisis*; hitungan butir dari `derived_items` | 11 |
| Kesungguhan | `rapid_share` | Proporsi percobaan pertama bertanda `rapid_guess = 1` di antara yang punya `rapid_guess` | 11 |
| Penyelesaian | `regions_completed`, `challenges_completed`, `scored_challenges_completed` | Dari `derived_challenges` | 11 → Analitik 1 |
| Pendaftaran | `registration_corrected` | 1 bila `participants.corrected_at` terisi | 30 §9 |
| Angket | `questionnaire_status`, `own_phone`, `device_count`, `access_index`, `activity_breadth`, `own_accounts`, `social_media_own`, `social_media_use`, `parent_active`, `parent_rules`, `safety_info_sources`, `game_time_group`, `q_flags` | Rumus [16 → Variabel turunan](16_ANGKET_AKSES_DIGITAL.md#variabel-turunan); `-`, `8`, `9` hilang; indeks hilang bila satu komponen hilang (K10) | 16, jawaban mentah 34 |
| Tes | `test_status`, `pre_d1` … `post_d3`, `pre_capped`, `post_capped` | `test_status` dari ada atau tidaknya tes awal dan akhir yang dikirim; skor per bagian disalin dari modul | 13, 34 |
| Skala sikap | `sikap_score`, `sikap_sk1` … `sikap_sk4`, `sikap_n_answered`, `sikap_flag_straight`, `sikap_missing_reason` | Rumus [17 → Variabel untuk kamus data](17_SKALA_SIKAP.md#variabel-untuk-kamus-data); butir negatif dibalik menurut daftar di 17; skor kosong bila kurang dari 10 butir dijawab | 17, 34 |

`sikap_missing_reason`: `absen` bila tidak ada jawaban skala dan tidak hadir di sesi lapangan P5 maupun susulannya; `belum_tuntas` bila hadir tetapi tantangan belum tuntas saat sesi P5 terakhir ditutup; `tanpa_izin` tidak pernah terjadi di sistem, karena anak tanpa izin tidak punya akun (30 §5.2), jadi nilai itu hanya dari L7 di lapis 3 ([§20](#20-akibat-ke-dokumen-lain)).

**Yang tidak dihitung di sini:** `*_theta` tes (lapis 3), dan semua variabel butir risiko daring (`risk_*`, `support_*`): jawaban per siswa tidak pernah keluar dari server (29 K9), jadi hanya muncul sebagai angka gabungan di lembar `risiko_gabungan` 29 §7.6.

### 6.6 `derived_field_sessions`: satu baris per peserta × sesi lapangan

| Kolom | Aturan | 11 |
| --- | --- | --- |
| `participant_code`, `field_session_code`, `meeting_no` | Kunci | — |
| `attended` | 1 bila ada sesi game peserta di sesi lapangan itu | — |
| `session_total_ms`, `session_active_ms` | Jumlah semua sesi game peserta di sesi lapangan itu: pengerjaan ditambah cerita, peta, dan Pustaka, dengan aturan aktif §6.7 | Analitik 5, sesi |
| `library_pages`, `library_ms` | `library_page_viewed` dan waktu aktifnya | Analitik 8 |
| `headset_share`, `internet`, `noise`, `teacher_present`, `minutes_available`, `minutes_used` | Disalin dari L1 kondisi sesi itu | Variabel sesi |
| `l4_observations` | Banyak pengamatan L4 untuk siswa itu di sesi ini; kode perilaku dan emosi per pengamatan tetap di lembar `l4_bromp` 29, dengan `attempt_id` dan `item_key` yang aktif ditambahkan lapis turunan (`derived_observations`, satu baris per pengamatan) | Aturan gabung L4 |

### 6.7 Waktu aktif

Satu fungsi, `Timeline::active()`, dipakai untuk pengerjaan, butir, dan sesi:

1. **Potongan.** Satu potongan = selang di dalam satu sesi game, dari mulai (`challenge_opened`, atau tampilnya petunjuk atau situasi untuk butir, atau awal sesi game) sampai selesai (percobaan yang menutup, atau event terakhir potongan itu). Selang di antara potongan tidak dihitung. `total_ms` = jumlah potongan.
2. **Tersembunyi.** Selang `page_hidden` sampai `page_visible` berikutnya (atau sampai akhir potongan) dipotong seluruhnya → `hidden_ms`.
3. **Jeda masukan.** Dari sisa yang tampil, setiap jeda tanpa masukan yang dicatat bagian 7 (≥ 20 detik) dikurangi selang narasi yang sedang diputar dan selang yang sudah tersembunyi. Bila sisanya lebih dari T_idle, bagian yang melebihi T_idle masuk `idle_excess_ms`.
4. `active_ms = total_ms − hidden_ms − idle_excess_ms`.

Semua jam memakai waktu terkoreksi (`occurred_at_corrected`, 29 K8). Event dengan `offset_known = 0` tetap dipakai dengan waktu mentahnya; banyaknya dicatat di `derived_runs.notes` untuk dilaporkan.

**Masukan** adalah sentuhan atau klik, tombol keyboard, gulir, dan seret; gerakan tetikus tanpa klik tidak (11). Itu aturan perekaman bagian 7; lapis turunan hanya membaca jeda yang dicatat.

---

## 7. `consent_ok` dan data sampai tanggal mundur

**Sumber.** Daftar izin berkode di sistem (`participant_consents`, 30 §5.2 dan §10.1), dicocokkan `ketua` dengan L7 sampai studi ditutup (30 §10.1). L7 tidak pernah masuk server; kecocokannya tanggung jawab `ketua`, bukan sistem.

| Keadaan di `participant_consents` | `consent_ok` | Data yang masuk lapis turunan |
| --- | --- | --- |
| `parent_consent_on` terisi, status `active` atau `left` | 1 | Semua |
| `parent_consent_on` kosong (izin belum diisi) | 0 | Baris tetap dihitung supaya jumlahnya terlihat, tetapi `analysable = 0` |
| Status `withdrawn_keep` | 1 | Hanya sebelum saat mundur (K4) |
| Status `withdrawn_delete` | — | Tidak ada: datanya dihapus bagian 4 dan barisnya tidak ada lagi |
| Tidak ada baris izin (akun dari sebelum bagian 4) | 0 | Seperti izin belum diisi |

**Saat mundur** (`withdrawn_at`):
- `participants.deactivated_at` bila terisi (30 §5.3 mengisinya di transaksi yang sama dengan perubahan status), `cutoff_basis = 'deactivated_at'`;
- selain itu akhir hari `status_on` (23:59:59.999 WIB), `cutoff_basis = 'status_on'`.

Semua percobaan, event, jawaban modul, dan baris lembar lapangan dengan waktu **sesudah** saat mundur tidak masuk tabel turunan. Pengerjaan yang terpotong dinilai dari yang tersisa (misalnya `unfinished`, `censor_reason = 'left'`). Data mentahnya tidak diubah dan tetap di lembar mentah dengan kolom baru `after_withdrawal` (1/0), supaya analisis bisa memeriksa jumlahnya ([§10](#10-unduhan-dan-layar-data)).

**Persetujuan anak (T4).** 11 menulis `consent_ok` = izin orang tua **dan** persetujuan siswa. Di sistem, centang T4 wajib saat mendaftar dalam mode penelitian (30 §5.1), jadi setiap akun penelitian sudah punya `assented_at`. Lapis turunan tetap memeriksanya: `assented_at` kosong → `consent_ok = 0`.

**Izin terpisah** (rekaman suara, butir risiko daring) tidak mengubah `consent_ok`; keduanya mengatur data lain (rekaman di luar sistem, butir risiko sebagai angka gabungan).

---

## 8. `kamus-1.1` usulan

### 8.1 Cara versinya berjalan

| Tahap | `ResearchDictionary::VERSION` | Yang terjadi |
| --- | --- | --- |
| Sekarang sampai bagian 9 digabung | `kamus-1.0` | Lembar *Kamus Data* 29 §8 tanpa lembar turunan |
| Setelah bagian 9 digabung, sebelum uji coba | `kamus-1.1-usulan` | Entri baru di §8.2 ada di `ResearchDictionary` dengan status `usulan`; lembar *Kamus Data* menulis "usulan, belum disetujui" di baris kepala |
| Setelah uji coba (tahap 4), sebelum G4 | `kamus-1.1` | 11 diubah (cabang sendiri, entri 04), ambang ditetapkan ulang, urutan alasan ditetapkan; entri menjadi `ada` |
| G4 sampai analisis selesai | `kamus-1.1` beku | Ambang dikunci (§5); kesalahan dicatat sebagai penyimpangan |

Mengubah `VERSION` membuat semua putaran basi (§4.3), jadi unduhan berikutnya otomatis memakai versi baru.

### 8.2 Isi yang diusulkan

Yang **tidak** berubah dari `kamus-1.0`: peta bukti, istilah, butir yang dinilai, definisi 10 analitik, data hilang, dan aturan gabung lembar. Usulan hanya menambah dan menegaskan.

| No | Bagian di 11 | Usulan | Asal |
| ---: | --- | --- | --- |
| 1 | *Penanda untuk analisis*, `consent_ok` | Sumbernya daftar izin berkode di sistem, dicocokkan dengan L7; data peserta yang mundur dengan data tetap dihitung sampai saat mundur (§7); kolom baru `withdrawn_on`, `cutoff_basis` | 30 §18, 13 → *Analisis* |
| 2 | *Variabel peserta dan sesi* | Variabel tes 13 → *Penskoran* (kecuali `*_theta`, ditandai lapis 3) | 13 |
| 3 | Sama | Variabel angket 16 → *Variabel untuk kamus data*, **tanpa** `risk_*` dan `support_*` per siswa; butir risiko daring hanya lembar `risiko_gabungan` | 16, 29 K9 |
| 4 | Sama | Variabel skala sikap 17 → *Variabel untuk kamus data*; `feedback_rating` = `participant_feedback.rating` | 17 |
| 5 | Sama | `registration_corrected`; `risk_reconsent` hanya sebagai angka gabungan (berapa siswa memilih Ya di T11) | 30 §5.4, §9 |
| 6 | Baru: *Jawaban tes* | Satu baris per butir per siswa per tes (13), dengan `rapid_guess` memakai T_teb studi | 13 |
| 7 | *Kunci penggabungan dan waktu* | `offset_known` (1/0); `after_withdrawal` di lembar mentah | 29 K8, §7 |
| 8 | Analitik 5 dan 6 | Waktu putus = `last_input_ms − ready_ms`; `last_input_ms` Cari Objek sebelum bagian 3 memakai rujukan lama (selisih dari petunjuk tampil), jadi `decision_ms` data lama kosong | 29 §6.1 |
| 9 | Analitik 6, NON-SEN | `empty_tap` mencatat koordinat; jarak ke kunci diturunkan di server (K11) | 29 §6.2 |
| 10 | Analitik 8 | Bagian (b) `material_returns` dari `passage_reopened` | 29 §6.3 |
| 11 | *Versi dan cara berubah* | Setiap tabel turunan menyimpan `dictionary_version` **dan** `run_id`; ambang yang dipakai tercatat per putaran; versi antara `kamus-1.1-usulan` | §4, §8.1 |
| 12 | Analitik 1 | "Pengambilan data selesai" = jam putaran yang dipakai unduhan akhir; data yang diunduh sebelum studi ditutup menyebut jam putarannya | §6.4 |
| 13 | *Aturan gabung lembar lapangan*, L4 | Pengamatan L4 diberi `attempt_id` dan `item_key` yang aktif (`derived_observations`) | §6.6 |
| 14 | Analitik 7, 10 | `instruction_replays` memakai kode konteks narasi instruksi (bagian 6 atau 7); `reason_level` tetap kosong sampai urutan alasan ditetapkan | 01 no. 6, 11 |
| 15 | *Variabel peserta dan sesi*, sesi game | `is_touch` tetap di log teknis admin kecuali peneliti memintanya (29 §18 no. 3) | 29 |

Setelah uji coba, angka yang ditetapkan ulang (T_idle, T_teb, batas `game_time_group`, ambang tebakan cepat tes) ditulis ke 11 bersama nomor di atas.

---

## 9. Kapan dihitung

| Pemicu | Yang terjadi |
| --- | --- |
| `ketua` membuat unduhan studi dengan `field_mode` menyala | `ExportService::build()` memanggil `DerivedDataService::ensureFresh($studyId)` sebelum menulis lembar. Bila basi, putaran baru dihitung dulu; unduhan mencatat `derived_run_id` yang dipakai |
| Tombol *Hitung ulang* di halaman studi | Menghitung ulang walaupun tidak basi (misalnya setelah memperbaiki kode). Antre seperti unduhan |
| `php spark gelita:turunan:hitung --studi=KODE` | Sama, dari baris perintah; `--cek` hanya mencetak apakah basi dan sidik jarinya |
| Ambang diubah, data dihapus, lembar diimpor | Tidak menghitung langsung; putaran menjadi basi dan dihitung di pemicu berikutnya |

**Satu putaran sekali jalan per studi.** Putaran baru disisipkan di `derived_runs` dengan status `running`; sebelum itu `DerivedDataService` mengklaim studi lewat UPDATE bersyarat `research_studies.derived_running_since` (kosong atau lebih tua dari 30 menit → diisi). Yang kalah tidak menghitung: unduhan menunggu putaran yang sedang berjalan (polling seperti status unduhan sekarang), tombol mendapat pesan "sedang dihitung".

**Selesai.** Setelah semua peserta ditulis: `derived_runs.status = 'done'`, lalu `research_studies.derived_run_id` diganti dengan UPDATE bersyarat pada nilai lama (K7) dan `derived_running_since` dikosongkan. Bila gagal: `failed` dengan pesan, putaran lama tetap berlaku, dan unduhan gagal dengan pesan yang sama (tidak diam-diam memakai putaran basi).

**Membersihkan putaran lama.** Putaran yang bukan `derived_run_id` dan tidak dipakai unduhan yang berkasnya masih ada (`data_exports.derived_run_id`, retensi 7 hari) dihapus di akhir putaran berikutnya dan oleh `gelita:retention:run`.

**Penghapusan data (bagian 4).** `RetentionService` menghapus baris turunan peserta itu dari semua putaran (`ON DELETE CASCADE` lewat `participant_id`), sehingga data anak yang dihapus tidak tertinggal di putaran lama.

**Lama hitung.** Perkiraan untuk studi utama (± 360 anak, ± 70 percobaan dan ± 2.000 event per anak): beberapa menit. Ini diukur di cek manual §16.3 dengan data `gelita:simulasi`; bila lebih dari 10 menit, putaran dijalankan sebagai pekerjaan latar seperti unduhan besar.

---

## 10. Unduhan dan layar data

### 10.1 Lembar baru

Ditambahkan ke daftar lembar 29 §7.2, sesudah `butir` dan sebelum lembar lapangan, hanya bila studi `field_mode` menyala:

| Lembar | Tabel | Baris |
| --- | --- | --- |
| `turunan_peserta` | `derived_participants` | Peserta |
| `turunan_sesi` | `derived_field_sessions` | Peserta × sesi lapangan |
| `turunan_tantangan` | `derived_challenges` | Peserta × tantangan |
| `turunan_pengerjaan` | `derived_attempts` | Pengerjaan |
| `turunan_butir` | `derived_items` | Peserta × butir |
| `turunan_percobaan` | `derived_tries` | Percobaan |
| `turunan_pengamatan` | `derived_observations` | Pengamatan L4 |

Filter unduhan (wilayah, tantangan, sekolah, `field_session_code`, `meeting_no`, tanggal) berlaku juga untuk lembar turunan. Filter tidak mengubah hitungan; hanya memilih baris.

### 10.2 Lembar *Kamus Data*

Baris kepala lembar `kamus` (29 §8) ditambah: `derived_run_id`, jam putaran, `dictionary_version` putaran, `idle_threshold_ms`, `rapid_guess_ms`, dan "usulan, belum disetujui" selama versinya `kamus-1.1-usulan`. Setiap kolom lembar turunan punya entri `ResearchDictionary` (analitik, jangkar 11, status) dan `ExportLabels` (judul, arti, satuan, nilai), sama dengan aturan tes 29 `DictionarySheetTest`.

### 10.3 Yang tidak pernah ada di lembar turunan (K12)

- Teks jawaban, `answer_key`, atau isian bebas. Kunci tetap hanya di lembar `butir` dan `jawaban` untuk `ketua` (29 K1).
- Jawaban butir risiko daring per siswa, `risk_reconsent` per siswa, dan penanda tindak lanjut (29 K9, 30 §6).
- Nama, nama pengguna, nama sekolah, NPSN (11 → *Variabel peserta dan sesi*).
- `participant_id` atau id internal lain selain `attempt_id` dan `try_id`, yang dipakai sebagai kunci antartabel.

### 10.4 Layar data

`/admin/data/{dimensi}` (`research.browse`) mendapat ketujuh lembar turunan sebagai dimensi, membaca putaran yang berlaku. Di atas tabel: jam putaran, versi kamus, dan "Data mentah sudah berubah sejak dihitung" bila basi, dengan tombol *Hitung ulang*.

---

## 11. Hak akses dan menu

Tidak ada kemampuan baru (K14).

| Halaman atau tindakan | Admin | Peneliti | Ketua | Kemampuan |
| --- | --- | --- | --- | --- |
| Lembar turunan di unduhan | — | — | Ya | `research.export` |
| Lembar turunan di layar data | — | — | Ya | `research.browse` |
| Tombol *Hitung ulang*, status putaran | — | — | Ya | `research.export` |
| Ambang di halaman studi, *Kunci ambang* | — | — | Ya | `studies.manage` |
| `gelita:turunan:hitung` | Pengembang di server | | | Baris perintah |

**Menu.** Tidak ada menu baru. Status putaran dan tombol ada di halaman studi dan di atas layar data.

---

## 12. Skema database

**Migrasi `2026-01-01-004300_CreateDerivedLayer`.** Nomor sementara dari blok `0043xx`; diberi nomor ulang menurut urutan digabung bila perlu (gelombang B mulai `003900`). Hanya menambah; tidak ada data yang dihapus. Dijalankan di server produksi hanya bila pengembang memutuskannya.

| Tabel atau kolom | Isi |
| --- | --- |
| `research_studies` | Kolom baru `idle_threshold_ms` INT UNSIGNED bawaan 180000, `rapid_guess_ms` INT UNSIGNED bawaan 3000, `thresholds_locked_at` DATETIME (null), `derived_run_id` FK `derived_runs` (null, `ON DELETE SET NULL`), `derived_running_since` DATETIME (null) |
| `derived_runs` (baru) | `id`, `study_id` FK (`ON DELETE CASCADE`), `status` VARCHAR(10) (`running`, `done`, `failed`), `dictionary_version` VARCHAR(30), `idle_threshold_ms`, `rapid_guess_ms` INT UNSIGNED, `input_fingerprint` CHAR(64), `participant_count` INT UNSIGNED, `notes_json` JSON (null), `error` TEXT (null), `started_by` FK staf (null, `ON DELETE SET NULL`), `via` VARCHAR(10) (`export`, `button`, `command`), `started_at`, `finished_at` DATETIME(3) (null). Indeks (`study_id`, `status`) |
| `derived_participants` (baru) | `run_id` FK (`ON DELETE CASCADE`), `participant_id` FK (`ON DELETE CASCADE`), `dictionary_version`, kolom §6.5. Kunci utama (`run_id`, `participant_id`) |
| `derived_field_sessions` (baru) | `run_id`, `participant_id`, `field_session_id`, `dictionary_version`, kolom §6.6. Kunci utama (`run_id`, `participant_id`, `field_session_id`) |
| `derived_challenges` (baru) | `run_id`, `participant_id`, `challenge_node_id`, `dictionary_version`, kolom §6.4. Kunci utama (`run_id`, `participant_id`, `challenge_node_id`) |
| `derived_attempts` (baru) | `run_id`, `attempt_id`, `participant_id`, `dictionary_version`, kolom §6.3. Kunci utama (`run_id`, `attempt_id`) |
| `derived_items` (baru) | `run_id`, `participant_id`, `challenge_item_id`, `dictionary_version`, kolom §6.2. Kunci utama (`run_id`, `participant_id`, `challenge_item_id`) |
| `derived_tries` (baru) | `run_id`, `try_id` (`item_tries.id`), `participant_id`, `dictionary_version`, kolom §6.1. Kunci utama (`run_id`, `try_id`) |
| `derived_observations` (baru) | `run_id`, `field_sheet_row_id`, `participant_id`, `attempt_id` (null), `challenge_item_id` (null), `dictionary_version`. Kunci utama (`run_id`, `field_sheet_row_id`) |
| `data_exports` | Kolom baru `derived_run_id` FK `derived_runs` (null, `ON DELETE SET NULL`) |
| `game_event_logs`, `item_tries` dan tabel mentah lain | Tidak berubah. `after_withdrawal` di lembar mentah dihitung saat unduhan dari `withdrawn_at` putaran, bukan kolom |

Semua kunci asing `participant_id` di tabel turunan memakai `ON DELETE CASCADE`, sesuai aturan hapus bagian 4 ([30 §7.3](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md#73-yang-dihapus-sistem)). Kolom kode (`participant_code`, `item_key`, `node_ref`, `field_session_code`) tidak disimpan di tabel turunan; lembar unduhan menggabungnya dari tabel asal, supaya penggantian identitas saat studi ditutup (30 §10.2) tidak perlu menyentuh tabel turunan.

**Ikut diperbarui:**
- `tests/_support/Database/ResetTables.php` (skema SQLite).
- `SystemResetService`: `REQUIRED_MIGRATION` menjadi `004300` (atau nomor akhirnya); tabel turunan di daftar yang dikosongkan.
- `RetentionService`: `HARD_ORDER` mendapat tabel turunan sebelum `participants` (untuk pratinjau jumlah); pembersihan putaran lama.
- `ResearchStudySeeder`: ambang bawaan.
- Model baru `DerivedRunModel` (tabel turunan ditulis lewat query builder per batch, tanpa entity). `ResearchStudyModel`, `DataExportModel`: `allowedFields`.
- `Config\Gelita`: `$derivedIdleThresholdMs`, `$derivedRapidGuessMs`.
- `app/Config/Services.php`: `derivedData`.

---

## 13. Rute dan perintah spark

**Panel** (grup `staffAuth`, kemampuan dari 27):

| Rute | Controller::method | Kemampuan |
| --- | --- | --- |
| `POST /admin/studi/{id}/ambang` | `StudyController::saveThresholds` | `studies.manage` |
| `POST /admin/studi/{id}/ambang/kunci` | `StudyController::lockThresholds` | `studies.manage` |
| `POST /admin/studi/{id}/hitung-ulang` | `DerivedDataController::recompute` | `research.export` |
| `GET /admin/studi/{id}/hitung-ulang/status` | `DerivedDataController::status` (JSON untuk polling panel) | `research.export` |
| `/admin/ekspor…`, `/admin/data…` | Tetap (27, 29) | `research.export`, `research.browse` |

Prefiks `/admin/studi` mengikuti rute studi bagian 1; bila 27 memakai slug lain, ikuti 27. Tidak ada rute game atau `/api` baru.

**Perintah spark:**

```text
php spark gelita:turunan:hitung --studi=KODE         # compute a new run now (waits for a running one)
php spark gelita:turunan:hitung --studi=KODE --cek   # print whether the current run is stale, no writes
php spark gelita:turunan:ambang --studi=KODE --buka --alasan="…"  # unlock thresholds after G4 (deviation, audited)
```

`--buka` menulis log audit `study_thresholds_unlocked` dengan alasannya. Kedua perintah masuk daftar perintah spark di aturan kerja repo.

---

## 14. Teks antarmuka

Semua teks baru ada di panel, jadi masuk `app/Language/id/Admin.php` (hanya bahasa Indonesia; tidak butuh pasangan `en`). Game tidak mendapat teks baru. Mengikuti `.claude/rules/ui-copy.md`.

| Kunci | Usulan |
| --- | --- |
| `studyThresholds` | Ambang data turunan |
| `studyIdleThreshold` | Ambang jeda (detik) |
| `studyIdleThresholdHelp` | Jeda tanpa sentuhan yang lebih lama dari ini tidak dihitung waktu aktif. Sementara 180 detik; ditetapkan ulang setelah uji coba. |
| `studyRapidThreshold` | Ambang tebakan cepat (detik) |
| `studyRapidThresholdHelp` | Jawaban yang diputuskan lebih cepat dari ini ditandai tebakan cepat. Sementara 3 detik; ditetapkan ulang setelah uji coba. |
| `studyThresholdsSaved` | Ambang disimpan. Data turunan akan dihitung ulang saat diunduh. |
| `studyThresholdsLock` | Kunci ambang |
| `studyThresholdsLockConfirm` | Setelah dikunci, ambang tidak bisa diubah dari panel. Lanjutkan? |
| `studyThresholdsLocked` | Ambang sudah dikunci {0}. |
| `derivedRecompute` | Hitung ulang |
| `derivedRunning` | Data turunan sedang dihitung. Tunggu sebentar. |
| `derivedLastRun` | Dihitung {0} · kamus {1} · ambang jeda {2} dtk, tebakan cepat {3} dtk |
| `derivedStale` | Data mentah sudah berubah sejak dihitung. Tekan *Hitung ulang*, atau langsung unduh (dihitung otomatis). |
| `derivedFailed` | Penghitungan gagal: {0}. Data turunan sebelumnya tetap dipakai. |
| `derivedNone` | Belum pernah dihitung. |
| `derivedDraftVersion` | Versi kamus ini masih usulan dan belum disetujui. |
| `derivedOffForStudy` | Data turunan hanya dihitung untuk studi dengan mode penelitian menyala. |

---

## 15. Titik sambung ke perubahan lain

Nama kolom dan event dari bagian 5–8 di bawah adalah **anggapan** dokumen ini; masing-masing dicocokkan dengan dokumennya di sesi koordinator ([§20](#20-akibat-ke-dokumen-lain)).

| Perubahan | Yang dibutuhkan bagian ini | Anggapan nama |
| --- | --- | --- |
| Bagian 1 (`27`) | `research.export`, `research.browse`, `studies.manage`; halaman studi `ketua` | Sesuai 27 §5.1 |
| Bagian 2 (`28`) | `field_mode`, `field_sessions`, rencana pertemuan, `participant_progress`, `game_sessions.field_session_id`, `schools.research_code`, rombel, buklet | Sesuai 28 §17 |
| Bagian 3 (`29`) | `ready_ms`, `empty_tap` (koordinat, `overflow`), `passage_reopened`, `occurred_at_corrected`, `offset_known`, `EventCatalog`, `field_sheet_rows`, `ResearchDictionary`, daftar lembar | Sesuai 29 §6, §8, §12 |
| Bagian 4 (`30`) | `participant_consents` (`parent_consent_on`, `status`, `status_on`), `participants.deactivated_at`, `assented_at`, `corrected_at`, cakupan hapus | Sesuai 30 §15 |
| Bagian 5 (`31`): kode kesalahan, label, beban permukaan, bank baru | `item_tries.error_code`; `challenge_items` `dimension`, `subaspect`, `akm_level`, `round`, `testlet_key`; bagian ketuk Cari Objek dengan koordinat persen dan ukuran panggung; butir yang dinilai per buklet | `error_code` VARCHAR; label di `challenge_items` atau tabel label butir |
| Bagian 6 (`32`): Skenario Digital | Per percobaan: kunci pilihan tindakan, `action_score`, `reason_code`, waktu tampil pilihan tindakan dan alasan; event penutupan skenario setelah kesempatan ketiga | `item_tries.action_key`, `action_score`, `reason_code`, `action_ready_ms`, `reason_ready_ms`, `reason_last_input_ms`; event `scenario_failed` |
| Bagian 7 (`33`): waktu aktif mentah, mode penelitian | Jeda masukan ≥ 20 detik dengan jam mulai dan akhir; selang narasi diputar; pengerjaan pertama yang terputus dilanjutkan; `is_retake` dapat dibaca | Event `input_gap {from_ms, to_ms}` atau tabel jeda; narasi dari `audio_usage_events` |
| Bagian 6 atau 7: narasi instruksi | Kode konteks narasi instruksi | Konteks `instruction` di `audio_usage_events` |
| Bagian 8 (`34`): modul tes, angket, skala sikap | Jawaban mentah angket per variabel; skor tes per bagian, `capped`, tanggal, susulan; jawaban tes per butir dengan waktu tampil; jawaban skala per butir, `sikap_makeup`; `risk_reconsent` | Tabel jawaban modul per siswa per kegiatan, dengan kode variabel 16 dan 17 |

**Yang disediakan bagian ini untuk yang lain:** `derived_*` sebagai satu-satunya sumber angka turunan; tidak ada bagian lain yang menghitung ulang `rapid_guess`, waktu aktif, atau `completion_status` sendiri. Panel pemantauan bagian 2 tetap menghitung angka operasionalnya sendiri (siapa belum selesai), karena itu bukan hasil penelitian (01 → *Olah data*).

---

## 16. Pengujian dan kriteria selesai

### 16.1 Tes baru

**Contoh data hitungan tangan.** `tests/_support/Derived/` berisi satu "kelas contoh" dalam berkas PHP berlarik (bukan database): tiga siswa, satu tantangan per jenis (Isi Rumpang, Benar atau Salah dengan kartu jangkar, Cari Objek dua babak, Skenario Digital, Susun Gambar), satu sesi lapangan P2 dan satu susulan P5, lembar L1–L4, satu siswa mundur dengan data tetap. Hasil yang diharapkan ada di berkas CSV di sebelahnya, dihitung tangan dari 11 dan dokumen ini, dengan komentar per baris yang menyebut aturan di 11. Hasil yang diharapkan tidak pernah dibuat dengan menjalankan kalkulator.

| Tes | Yang diperiksa |
| --- | --- |
| `TimelineTest` | Potongan, tersembunyi, jeda > T_idle hanya bagian lebihnya, narasi tidak dihitung jeda, tersembunyi tidak dihitung dua kali; contoh 11 (jeda 5 menit dengan T_idle 3 menit menyumbang 3 menit aktif) |
| `TryCalculatorTest` | `decision_ms`, `rapid_guess` tepat di ambang (3000 bukan tebakan cepat, 2999 ya), kosong untuk Isi Rumpang, `error_code_analysis` NON-TEB, NON-HABIS, aturan cadangan `extra_try` |
| `ItemCalculatorTest` | Butir tanpa percobaan kosong dengan `missing_reason`; `censored` tidak pernah 3; `change_after_feedback` D2 dari kunci pilihan; `near_miss_count` di 24 dp |
| `ChallengeCalculatorTest` | Lima nilai `completion_status`; jenis (a) dan (b) tidak saling meniadakan; (b) tetap terhitung bila tuntas di P5 |
| `ParticipantCalculatorTest` | `analysable` di 49 dan 48 butir; `rapid_share`; rumus 16 dan 17 dengan `-`, `8`, `9`; skor sikap kosong di bawah 10 butir |
| `FieldSheetFlagsTest` | Jendela L3 −1/+2 menit dan jam selesai; L1 5 menit atau lamanya; kursi "semua"; jam pindah perangkat L2 |
| `WithdrawalCutoffTest` | Data sesudah `deactivated_at` (atau akhir `status_on`) tidak di tabel turunan; data mentah tetap dengan `after_withdrawal = 1` di lembar mentah |
| `DerivedGoldenTest` | Seluruh kelas contoh → setiap tabel turunan sama persis dengan CSV yang diharapkan |
| `DerivedRunTest` | Putaran mencatat versi, ambang, sidik jari; basi setelah ambang, impor lembar, izin, atau penghapusan berubah; dua hitung ulang bersamaan, satu yang menghitung (pola `Factories::injectMock`); gagal → putaran lama tetap |
| `DerivedExportTest` | Lembar turunan hanya untuk ketua dan studi `field_mode`; unduhan basi menghitung ulang dulu; `data_exports.derived_run_id`; tidak ada jawaban, kunci, butir risiko per siswa, nama, atau `participant_id` |
| `DerivedDictionaryTest` | Setiap kolom lembar turunan punya entri `ResearchDictionary` dan `ExportLabels`; versi `kamus-1.1-usulan` tertulis di baris kepala |
| `StudyThresholdsTest` | Ketua menyimpan dalam batas; di luar batas ditolak; audit; terkunci tidak bisa diubah; peneliti dan admin 404 |
| `DerivedPurityTest` | Kalkulator tidak memanggil `db_connect`, `config`, `service`, `time`, atau `date` (pemeriksaan sumber berkas) |
| `DerivedRetentionTest` | Menghapus satu siswa menghapus barisnya di semua putaran; putaran lama yang tidak dipakai dibersihkan |
| `DerivedCommandTest` | `gelita:turunan:hitung --cek` tidak menulis apa pun; `--buka` butuh alasan dan menulis audit |

### 16.2 Tes yang ditulis ulang

- `RawExportTest`, `ExportRulesTest`, `AdminTableViewTest`: lembar dan dimensi baru, `after_withdrawal`.
- Tes `ExportLabels` dan `DictionarySheetTest` (29): entri lembar turunan.
- `RouteWiringTest`: rute baru.
- `SystemResetTest`, tes retensi: tabel baru di daftar.

### 16.3 Cek manual

Di mesin pengembangan, dengan `gelita:simulasi` di studi `field_mode` menyala:
1. Unduh sebagai ketua; pastikan putaran dihitung sekali, lembar turunan terisi, dan baris kepala *Kamus Data* menyebut versi dan ambang. Catat lama hitung.
2. Ubah ambang jeda; unduh lagi; pastikan `active_ms` berubah dan data mentah tidak.
3. Pilih tiga baris `turunan_butir` dan satu `turunan_pengerjaan` secara acak; hitung tangan dari lembar mentah dan cocokkan.
4. Ubah status satu siswa menjadi mundur (data tetap) di tengah sesi; pastikan data sesudahnya tidak ada di lembar turunan.
5. Masuk sebagai peneliti dan admin; pastikan tombol, ambang, dan lembar turunan tidak terlihat.

### 16.4 Selesai bila

- `vendor/bin/phpunit --no-coverage` hijau, termasuk tes di 16.1.
- Cek manual 16.3 lolos, dengan lama hitung tercatat di PR.
- Setiap baris [§15](#15-titik-sambung-ke-perubahan-lain) sudah dicocokkan dengan kode bagian 1–8 yang digabung, dan selisihnya dibetulkan di dokumen ini.
- Dokumen di [§17](#17-dokumen-yang-ikut-diperbarui-saat-implementasi) diperbarui di cabang yang sama.

---

## 17. Dokumen yang ikut diperbarui saat implementasi

- `docs/07_FEATURE_INTEGRATION.md`: fitur baru data turunan; fitur ekspor dan studi.
- `docs/01_DATABASE.md`, `docs/03_MODEL_ENTITY.md`, `docs/04_CONTROLLER_ROUTE.md`, `docs/05_VIEW_UI.md`, `docs/08_DEPLOYMENT.md` (hitung ulang setelah migrasi), `README.md`.
- `.claude/rules/glossary.md`: baris baru di [§20](#20-akibat-ke-dokumen-lain).
- Aturan kerja repo: daftar perintah spark (`gelita:turunan:hitung`, `gelita:turunan:ambang`).
- `docs/PROGRESS.md` dan catatan keputusan implementasi.

---

## 18. Pertanyaan terbuka

Tidak menahan draf ini.

1. ~~**Kapan 11 diubah ke `kamus-1.1`.**~~ Usulan: satu sesi setelah uji coba KG3b, bersama penetapan ulang ambang, dengan `asal: peneliti` bila peneliti yang menetapkan angkanya. Sampai itu, unduhan menulis `kamus-1.1-usulan`. **Diputuskan pengembang 2 November 2026** (sesi koordinator, [04](04_CATATAN_KEPUTUSAN.md)): `kamus-1.1` ditulis ke 11 sekarang sebagai `draf pengembang` ([§19](#19-keputusan-pengembang) no. 6); angka ambang ditetapkan ulang setelah KG3b.
2. **Ambang tebakan cepat tes.** 13 menulis ambang untuk tes ditinjau tersendiri karena waktu membaca teks jatuh ke butir pertama. Sementara memakai T_teb studi. Bila uji coba menunjukkan perlu, ditambah kolom studi `test_rapid_guess_ms`.
3. ~~**"Pengambilan data selesai" sebelum studi ditutup.**~~ Dibaca sebagai jam putaran (§6.4). Alternatif: tanggal akhir pengambilan data di studi, sehingga `not_started` tidak berubah setelah tanggal itu. Usulan: cukup jam putaran, karena game tertutup di luar sesi lapangan. **Diputuskan pengembang 2 November 2026** (sesi koordinator, [04](04_CATATAN_KEPUTUSAN.md)): jam putaran.
4. **Lama hitung.** Bila cek manual menunjukkan lebih dari 10 menit untuk ± 360 anak, putaran pindah ke pekerjaan latar dan tombol menjadi antrean. Diputuskan saat implementasi dari angka cek manual.
5. **Data lama tanpa `ready_ms` atau `empty_tap`.** Pengerjaan dari sebelum bagian 3 tidak punya keduanya; `decision_ms`, `rapid_guess`, dan `near_miss_count` kosong, bukan 0. Data itu memang tidak masuk studi (diuji di instalasi uji), jadi tidak ada aturan khusus.

---

## 19. Keputusan pengembang

Satu putaran tanya, empat pertanyaan (2 November 2026). Pengembang memilih usulan di keempatnya. `asal: pengembang`.

1. **Lapis turunan disimpan di tabel** (K1), diisi satu kalkulator berversi per putaran hitung, dengan migrasi `004300`. Tidak dipilih: dihitung saat unduhan dibuat, tanpa tabel atau migrasi, yang membuat unduhan besar lambat dan layar data tidak bisa menampilkan angka turunan tanpa menghitung ulang.
2. **Ambang di kolom studi** (K2): `idle_threshold_ms` dan `rapid_guess_ms`, bawaan dari `Config\Gelita`, diubah `ketua` dengan log audit, dan terkunci di G4. Tidak dipilih: hanya di `Config\Gelita`, yang tidak bisa diubah peneliti dan sama untuk semua studi.
3. **Dihitung ulang saat unduhan bila basi, ditambah tombol dan perintah** (K3). Tidak dipilih: hanya manual (unduhan bisa membawa putaran lama); dijalankan setiap malam.
4. **Data mentah tetap, lapis turunan dipotong pada saat mundur** (K4), dengan `consent_ok = 1` dan `withdrawn_on` terisi. Tidak dipilih: hanya penanda `after_withdrawal` di lembar turunan, dibuang skrip analisis.

Putaran kedua, 2 November 2026 (sesi koordinator, `asal: pengembang`, [04](04_CATATAN_KEPUTUSAN.md)):

5. **Analisis memakai kode bank terbaru** (dari [31 → Pertanyaan terbuka](31_IMPLEMENTASI_BANK_SOAL_BARU.md#21-pertanyaan-terbuka) no. 3): `error_code` di lembar turunan diturunkan dari matriks kode bank yang berlaku saat putaran hitung; kode saat percobaan tetap di data mentah dan di kolom `error_code_at_try`. Tidak dipilih: kode saat percobaan.
6. **`kamus-1.1` ditulis ke 11 sekarang sebagai `draf pengembang`**, lalu direvisi setelah uji coba (angka ambang, `asal: peneliti` bila peneliti yang menetapkannya), dan dikunci di G4. Tidak dipilih: satu sesi setelah KG3b bersama peneliti (usulan AI).
7. **"Pengambilan data selesai" = jam putaran hitung**, tanpa kolom tanggal akhir di studi. Tidak dipilih: tanggal akhir pengambilan data yang diisi `ketua`.

---

## 20. Akibat ke dokumen lain

Dicatat di sini dan dibawa sesi koordinator setelah dokumen ini dan 31–34 digabung.

| Dokumen | Bagian | Perubahan | Status |
| --- | --- | --- | --- |
| [11](11_KAMUS_DATA.md) | Seluruh usulan §8.2 | Masuk `kamus-1.1` setelah uji coba; termasuk baris 11 dari 29 §20 dan baris 11 dari 30 §23 | Saat `kamus-1.1` |
| [11](11_KAMUS_DATA.md) | *Yang harus disediakan sistem di tahap 3* | Kalimat "dikerjakan di tahap 3, setelah G2" sudah diketahui basi (04, 1 November 2026); diganti saat 11 disentuh | Saat `kamus-1.1` |
| [16](16_ANGKET_AKSES_DIGITAL.md) | *Variabel untuk kamus data* | Baris `risk_status` … `support_na` sebagai variabel peserta bertentangan dengan 29 K9 (jawaban butir risiko hanya angka gabungan). Usulan: baris itu menunjuk lembar `risiko_gabungan` | Untuk sesi koordinator |
| [16](16_ANGKET_AKSES_DIGITAL.md), [17](17_SKALA_SIKAP.md) | Variabel turunan | Dihitung lapis turunan (K10), bukan modul; rumusnya tetap di 16 dan 17 | Untuk sesi koordinator |
| [17](17_SKALA_SIKAP.md) | `sikap_missing_reason = tanpa_izin` | Tidak pernah terjadi di sistem (anak tanpa izin tidak punya akun, 30 §5.2); nilai ini hanya dari L7 di lapis 3 | Untuk sesi koordinator |
| [13](13_TES_AWAL_AKHIR.md) | *Penskoran*, `*_theta` | Dihitung di lapis 3, tidak oleh sistem; ambang tebakan cepat tes (§18 no. 2) | Untuk sesi koordinator |
| [29](29_IMPLEMENTASI_DATA_PENELITIAN.md) | §7.2 daftar lembar; §8 | Tujuh lembar `turunan_*`; baris kepala *Kamus Data* membawa `derived_run_id` dan ambang; versi antara `kamus-1.1-usulan` | Untuk sesi koordinator |
| [29](29_IMPLEMENTASI_DATA_PENELITIAN.md) | §7.3 | Kolom `after_withdrawal` di lembar mentah `pengerjaan`, `jawaban`, `percobaan`, `navigasi`, `bantuan`, `selesai`, `event` | Untuk sesi koordinator |
| [30](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md) | §7.3, §15 | Tabel `derived_*` di cakupan hapus satu siswa (`ON DELETE CASCADE`); §21 no. 4 (lembar `izin` di unduhan utama) tidak dibutuhkan lapis turunan, karena `consent_ok` dihitung di server | Untuk sesi koordinator |
| [28](28_IMPLEMENTASI_SESI_LAPANGAN.md) | §17 skema | `game_sessions.field_session_id` dan rencana pertemuan dibaca lapis turunan untuk `locked`/`not_started` | Untuk sesi koordinator |
| [27](27_IMPLEMENTASI_PERAN_PENELITI.md) | §7 rute studi | Rute `/admin/studi/{id}/ambang` dan `/hitung-ulang`; tanpa kemampuan baru | Untuk sesi koordinator |
| `31` (bagian 5) | Skema | Anggapan §15: `item_tries.error_code`, label butir, bagian ketuk dengan koordinat persen dan ukuran panggung | Dicocokkan sesi koordinator |
| `32` (bagian 6) | Skema dan event | Anggapan §15: kunci pilihan tindakan, `action_score`, `reason_code`, waktu tampil pilihan, event penutupan skenario | Dicocokkan sesi koordinator |
| `33` (bagian 7) | Data mentah waktu aktif | Anggapan §15: jeda masukan ≥ 20 detik dengan jam mulai dan akhir; kode konteks narasi instruksi; ambang **tidak** disimpan bagian 7, tetapi di `research_studies` dari dokumen ini | Dicocokkan sesi koordinator |
| `34` (bagian 8) | Tabel jawaban modul | Anggapan §15: jawaban angket per kode variabel 16, skor tes per bagian, jawaban skala per butir, `risk_reconsent`; variabel turunan tidak dihitung modul | Dicocokkan sesi koordinator |
| [01](01_REVISI_10_ANALITIK.md) | No. 9 | Catatan: dokumen implementasi `35`; tabel turunan berversi, ambang di studi, hitung ulang saat unduhan | Untuk sesi koordinator |
| `.claude/rules/glossary.md` | Baris baru | `lapis data turunan` = `derived data layer`, `derived_*`; `putaran hitung` = `derived run`, `derived_runs`; `ambang jeda` / `ambang tebakan cepat` = `idle threshold` / `rapid-guess threshold`, `research_studies.idle_threshold_ms` / `rapid_guess_ms`; `Hitung ulang` = `recompute`; `saat mundur` = `withdrawal cutoff` | Saat implementasi |
| Aturan kerja repo | Perintah spark | `gelita:turunan:hitung`, `gelita:turunan:ambang` | Saat implementasi |
| [00](00_INDEKS.md) | *Daftar dokumen*, *Tahap dokumen 3* | Baris 35 | Sudah (cabang ini) |
