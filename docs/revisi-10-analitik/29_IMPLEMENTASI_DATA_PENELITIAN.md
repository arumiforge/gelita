# Dokumen Implementasi Bagian 3 — Pembersihan Log, Unduhan Data Penelitian, Kamus Data, dan Lembar Lapangan

Per 2 November 2026 · Bagian dari [revisi 10 analitik](01_REVISI_10_ANALITIK.md) · perubahan sistem no. 7, 14, dan 15 di [01 → Catatan perubahan sistem](01_REVISI_10_ANALITIK.md#catatan-perubahan-sistem-untuk-tahap-nanti)

> **Status: `disetujui pengembang`, `asal: pengembang`** (disusun dengan bantuan AI, 30 Oktober 2026; dicek kemutakhirannya dan ditinjau pengembang 2 November 2026, [§19](#19-keputusan-pengembang) no. 5–12). Ini dokumen, bukan perubahan sistem.
>
> **Kapan dibangun** (aturan baru, [04](04_CATATAN_KEPUTUSAN.md), 1 November 2026, menggantikan "diubah di tahap 3, setelah G2"): kode bagian ini dibangun sekarang di gelombang kode E, setelah bagian 1 dan 2, di cabang pendek dari `main`. Perilaku penelitian ada di balik sakelar studi yang mati secara bawaan (`field_mode`, [28 §5.1](28_IMPLEMENTASI_SESI_LAPANGAN.md#51-sakelar-field_mode)), jadi `main` tetap bisa dirilis. Sistem baru tidak dipakai dengan anak sebelum persetujuan etik terbit; G3 tetap dinilai setelah G2, dan versi dikunci di G4. Instalasi uji validator tetap memakai *commit* yang dibekukan di [20](20_PAKET_VALIDASI.md) prasyarat no. 4. Pengerjaan ulang setelah hasil validasi (matriks revisi induk di 20, keputusan komite etik) diterima. Bagian 4 ([30](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md)) dibangun paralel dan digabung **sesudah** bagian ini; baris milik bagian ini di berkas bersama (`field_sheet_rows` di `RetentionService::HARD_ORDER`, `participant_count` dan daftar lembar di `ExportService`, `REQUIRED_MIGRATION` `003700`) ditulis di sini dan dibiarkan bagian 4 ([30 §0](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md#0-cara-memakai-dokumen-ini), keputusan no. 7).
>
> **Migrasi `003600` menghapus data secara permanen** ([§5.8](#58-data-yang-sudah-tersimpan)). Migrasi ini, seperti `003400` dari bagian 1, baru dijalankan di server produksi bila pengembang memutuskannya.
>
> **Bagian 3 dari 4.** Bagian ini memakai peran dan peta kemampuan dari [27](27_IMPLEMENTASI_PERAN_PENELITI.md) (`research.export`, `tech_logs.view`, `field_sessions.manage`), serta sesi lapangan, kode peserta, dan kemajuan per siswa dari [28](28_IMPLEMENTASI_SESI_LAPANGAN.md). Bagian 4 (`30`) memakai riwayat unduhan dan tabel lembar lapangan dari sini untuk penghapusan dan permintaan orang tua.

---

## Daftar isi

0. [Cara memakai dokumen ini](#0-cara-memakai-dokumen-ini)
1. [Kebutuhan, sumber, dan tempatnya](#1-kebutuhan-sumber-dan-tempatnya)
2. [Keputusan yang dikunci](#2-keputusan-yang-dikunci)
3. [Yang tidak berubah](#3-yang-tidak-berubah)
4. [Katalog event](#4-katalog-event)
5. [Pembersihan log (perubahan no. 7)](#5-pembersihan-log-perubahan-no-7)
6. [Data mentah baru dari kamus (perubahan no. 14)](#6-data-mentah-baru-dari-kamus-perubahan-no-14)
7. [Unduhan data penelitian (perubahan no. 14)](#7-unduhan-data-penelitian-perubahan-no-14)
8. [Lembar *Kamus Data* dan `dictionary_version`](#8-lembar-kamus-data-dan-dictionary_version)
9. [Riwayat unduhan](#9-riwayat-unduhan)
10. [Templat dan impor lembar lapangan (perubahan no. 15)](#10-templat-dan-impor-lembar-lapangan-perubahan-no-15)
11. [Hak akses dan menu](#11-hak-akses-dan-menu)
12. [Skema database](#12-skema-database)
13. [Rute dan perintah spark](#13-rute-dan-perintah-spark)
14. [Teks panel](#14-teks-panel)
15. [Titik sambung ke perubahan lain](#15-titik-sambung-ke-perubahan-lain)
16. [Pengujian dan kriteria selesai](#16-pengujian-dan-kriteria-selesai)
17. [Dokumen yang ikut diperbarui saat implementasi](#17-dokumen-yang-ikut-diperbarui-saat-implementasi)
18. [Pertanyaan terbuka](#18-pertanyaan-terbuka)
19. [Keputusan pengembang](#19-keputusan-pengembang)
20. [Akibat ke dokumen lain](#20-akibat-ke-dokumen-lain)

---

## 0. Cara memakai dokumen ini

**Urutan.** Implementasinya dikerjakan setelah bagian 1 ([27](27_IMPLEMENTASI_PERAN_PENELITI.md)) dan bagian 2 ([28](28_IMPLEMENTASI_SESI_LAPANGAN.md)):
- unduhan hanya untuk `ketua` dan cakupan `research()` berasal dari bagian 1;
- kode sesi lapangan, kode sekolah penelitian, rombel, buklet, dan `participant_progress` berasal dari bagian 2;
- templat dan impor lembar lapangan menempel di halaman sesi lapangan bagian 2.

**Batas.** Dokumen ini mengatur:
- pembersihan log: event, kolom, dan rincian perangkat yang dihapus atau dipindah ke admin;
- data mentah baru yang diminta kamus untuk perubahan no. 14;
- isi unduhan data penelitian dan halaman log teknis admin;
- lembar *Kamus Data* dan versinya;
- riwayat unduhan;
- templat dan impor lembar lapangan L1–L6, dan templat L7 yang tetap di luar server.

Lapis data turunan (no. 9), tantangan Skenario Digital (no. 2), waktu aktif (no. 5), narasi instruksi (no. 6), skala sikap (no. 21), dan modul tes (no. 22) hanya disebut sebagai **titik sambung** ([§15](#15-titik-sambung-ke-perubahan-lain)).

**Saat implementasi:**
- Satu cabang pendek dari `main`, commit berbahasa Inggris.
- `vendor/bin/phpunit --no-coverage` hijau sebelum push.
- Jangan menjalankan migrasi, `gelita:reset`, atau skrip rilis di server produksi. Migrasi `003600` menghapus data secara permanen ([§5.8](#58-data-yang-sudah-tersimpan)): di server produksi migrasi ini hanya dijalankan bila pengembang memutuskannya, oleh pengembang sendiri, setelah `gelita:log:hitung`.
- Setiap tulisan yang bisa bersaing (penandaan sesi diam, impor yang menggantikan impor lama) memakai UPDATE bersyarat, bukan `SELECT … FOR UPDATE` (aturan kerja repo, bagian *Concurrency*).

**Istilah.** *Sesi game* di dokumen ini adalah satu baris `game_sessions`, yang di panel dan di 27 disebut *sesi bermain* (glosarium: `game session`). *Sesi lapangan* adalah `field_sessions` dari bagian 2.

**Rujukan kode.** Ditulis per berkas dan metode, dengan nomor baris per 30 Oktober 2026, dicek ulang 2 November 2026 (kode `app/` dan `public/assets/js/` tidak berubah sejak 26 Oktober 2026; nomor yang meleset dibetulkan). Nomor baris bisa bergeser.

---

## 1. Kebutuhan, sumber, dan tempatnya

| # | Kebutuhan | Sumber | Di sini |
| ---: | --- | --- | --- |
| 1 | Log yang tidak menjawab 10 analitik dihapus, bukan disembunyikan; log untuk menjaga sistem hanya dilihat admin; sisanya untuk peneliti | [01 → Log: dihapus atau dipisah](01_REVISI_10_ANALITIK.md#log-dihapus-atau-dipisah); 01 no. 7 | §4, §5 |
| 2 | Rincian perangkat (merek, model, user agent, client hints, rasio piksel, titik sentuh, zona waktu, bahasa peramban) dihapus; nama OS dan peramban dengan versinya hanya untuk admin; jenis perangkat dan ukuran layar untuk peneliti | 01 → Log | §5.5 |
| 3 | Admin tidak mengunduh data penelitian; cukup log teknis | [01 → Admin dan peneliti](01_REVISI_10_ANALITIK.md#admin-dan-peneliti-dua-peran-berbeda); [27 → Hak akses per peran](27_IMPLEMENTASI_PERAN_PENELITI.md#4-hak-akses-per-peran) | §5.6, §11 |
| 4 | Unduhan hanya berisi data bertanda "Peneliti", selalu berkode, hanya lewat akun `ketua` | 01 no. 14; 27 K14 | §7 |
| 5 | Lembar *Kamus Data* di unduhan diturunkan dari `kamus-1.0`; setiap angka bisa ditelusuri ke versi kamus | [11 → Versi dan cara berubah](11_KAMUS_DATA.md#versi-dan-cara-berubah); [11 → Yang harus disediakan sistem di tahap 3](11_KAMUS_DATA.md#yang-harus-disediakan-sistem-di-tahap-3) | §8 |
| 6 | Data mentah baru untuk kamus: saat butir bisa dijawab, ketukan di luar bagian ketuk, membuka lagi bacaan | 11 → Yang harus disediakan sistem (baris no. 14) | §6 |
| 7 | Kunci gabung antartabel dan dengan lembar lapangan: kode peserta, kode sesi lapangan, kode sesi, `attempt_id`, `item_key`, `try_no`, jam WIB terkoreksi | [11 → Kunci penggabungan dan waktu](11_KAMUS_DATA.md#kunci-penggabungan-dan-waktu) | §7.3, §7.4 |
| 8 | Riwayat unduhan: siapa mengunduh apa; ketua melihat riwayatnya sendiri, admin melihat semua di log audit | 01 → Log (baris log audit); 27 §4; [26 → S2](26_PENILAIAN_DAMPAK_PELINDUNGAN_DATA.md) | §9 |
| 9 | Butir risiko daring hanya sebagai angka gabungan untuk peneliti | [26 → P8](26_PENILAIAN_DAMPAK_PELINDUNGAN_DATA.md); 27 §16 no. 5 | §7.6 |
| 10 | Templat spreadsheet lembar L1–L7 dengan kolom yang sama dengan 02; kuncinya kode peserta, jam WIB, dan kode sesi | 01 no. 15; [02](02_LEMBAR_LAPANGAN.md); [11 → Aturan gabung lembar lapangan](11_KAMUS_DATA.md#aturan-gabung-lembar-lapangan) | §10 |
| 11 | L7 satu-satunya lembar bernama; tidak pernah di server | [02 → L7](02_LEMBAR_LAPANGAN.md#l7-izin-orang-tua-dan-status-peserta); 26 → A8 | §10.6 |
| 12 | Lembar diketik paling lambat 24 jam setelah sesi; 10% baris diketik ulang orang lain | [02 → Cara mencetak dan mengisi](02_LEMBAR_LAPANGAN.md#cara-mencetak-dan-mengisi) | §10.4, §10.5 |
| 13 | `markStale()` mengubah sesi menjadi `paused` tanpa event | [28 → Pertanyaan terbuka](28_IMPLEMENTASI_SESI_LAPANGAN.md#21-pertanyaan-terbuka) no. 5 | §5.7 |
| 14 | `ketua` mendapat lembar `event` dan kolom `answer_key` atau tidak | [27 → Data yang terlihat per peran](27_IMPLEMENTASI_PERAN_PENELITI.md#9-data-yang-terlihat-per-peran) | §7.5; keputusan no. 1 |

---

## 2. Keputusan yang dikunci

K1–K4, K16, dan K17 diputuskan pengembang ([§19](#19-keputusan-pengembang)). K9 menurunkan penilaian dampak yang sudah disetujui pengembang (26). K5, K6, dan K7 berasal dari pencarian kode dan dicatat di [04](04_CATATAN_KEPUTUSAN.md) untuk ditinjau. Sisanya menurunkan 01, 02, 11, 27, dan 28.

| Kode | Keputusan |
| --- | --- |
| K1 | **`ketua` mendapat lembar `event` dan kolom `answer_key`** di unduhan berkode. Lembar `event` hanya memuat event bertanda Peneliti, dengan kode sebagai pengganti id internal. `answer_key` ada di lembar `butir` dan `jawaban`. Admin tidak mendapat keduanya. Kunci tetap tidak pernah dikirim ke peramban siswa. |
| K2 | **`answer_changed` digabung ke `answer_submitted`.** Server selalu menulis `answer_submitted`, dengan `changed` di payload. Baris `answer_changed` yang sudah tersimpan diganti namanya di migrasi. `answer_selected` dihapus seperti di 01. |
| K3 | **Data yang sudah tersimpan ikut dihapus di migrasi**: baris event yang dihapus dan kolom perangkat yang dihapus. Sebelumnya `gelita:log:hitung` mencetak jumlahnya. Migrasi mencatat satu baris audit. Tidak bisa dibatalkan. |
| K4 | **Templat lembar lapangan terisi per sesi lapangan, ditambah impor.** Peneliti mengunduh satu berkas XLSX per sesi lapangan dari halaman sesinya, sudah berisi kode sesi, kode peserta rombel, dan buklet. Lembar L1–L6 yang sudah diketik diunggah kembali ke sistem. L7 tidak pernah ada di server. |
| K5 | **`dialogue_advanced` diganti `dialogue_completed`.** Event per slide dihapus seperti di 01, tetapi lencana "Belum didengar" di peta wilayah membacanya (`GameEventLogModel::heardRegionIntros()`). Penggantinya satu event saat slide terakhir sebuah cerita tercapai. Migrasi membuat event pengganti dari baris lama sebelum menghapusnya. |
| K6 | **Event mentah `audio_pause` tidak ditulis lagi, tetapi baris `pause` di `audio_usage_events` tetap.** Lama mendengar (`listened_ms`) pemutaran yang tidak selesai hanya terkirim lewat aksi itu (`core/audio.js:413-419`). Klien juga mengirim lama mendengar saat pemutaran berhenti tanpa jeda (ulang, narasi lain, halaman ditinggal). |
| K7 | **Satu katalog event** (`App\Libraries\EventCatalog`) menentukan setiap jenis event: untuk peneliti, untuk admin, atau dihapus. `Config\Gelita::$eventTypes`, `RawDataService`, halaman log teknis, dan lembar `event` membaca katalog yang sama. |
| K8 | **Waktu di unduhan peneliti adalah waktu terkoreksi** `occurred_at_corrected = occurred_at + clock_offset_ms`, dengan `offset_known` (1/0). `clock_offset_ms` dan `server_received_at` hanya di log teknis admin. |
| K9 | **Jawaban butir risiko daring (ang-21–ang-23) tidak pernah per baris di unduhan.** Unduhan hanya memuat lembar angka gabungan, dengan sel di bawah 5 siswa ditulis `<5`. Penanda tindak lanjut ada di bagian 4 dan tidak diekspor. |
| K10 | **Lembar *Kamus Data* membaca `App\Libraries\ResearchDictionary`**, salinan terstruktur `kamus-1.0`. Versinya tertulis di setiap unduhan dan di `data_exports.dictionary_version`. Tes memastikan setiap kolom unduhan peneliti ada di kamus. |
| K11 | **Kolom lembar lapangan diambil dari tabel bertanda `<!-- lembar: … -->` di 02**, lewat `App\Libraries\FieldSheetSpec`. Tes membandingkan spesifikasi dengan 02, sehingga 02, templat, impor, dan situs statis memakai kolom yang sama. |
| K12 | **Yang diimpor:** L1 kondisi, L1 penyimpangan, L2, L3, L4 pengamatan, L5 wawancara (tanpa kolom kutipan), dan L6 catatan sesi tes. **Tidak diimpor:** L7, L8, pelatihan pengamat L4, status tes L6 (sumbernya modul tes), kolom token L1, dan kutipan ucapan siswa L5. |
| K13 | **Impor memeriksa nama.** Isian teks yang memuat kata yang sama dengan nama siswa rombel itu memberi peringatan per baris dan kolom, tanpa menampilkan namanya. Pengunggah memeriksa lalu mencentang konfirmasi sebelum menyimpan. |
| K14 | **`markStale()` menulis `session_paused` dengan `via: idle`**, satu per sesi game yang benar-benar diubahnya. |
| K15 | **Halaman *Log teknis* baru untuk admin** (`/admin/log-teknis`, kemampuan `tech_logs.view`): perangkat, event teknis, kolom teknis sesi, dan waktu mentah. Tanpa jawaban, skor, atau event penelitian. |
| K16 | **Unduhan selalu terikat satu studi** (`study_id` wajib), mode penelitian menyala atau mati. Satu baris `research_studies` per uji (KG3a, KG3b, KG5; [04](04_CATATAN_KEPUTUSAN.md), 1 November 2026), jadi data uji coba dan data utama tidak pernah ada di satu berkas ([§7.1](#71-siapa-cakupan-dan-format)). |
| K17 | **Versi kamus dan spesifikasi lembar dikunci di G4**, bersama versi game. `kamus-1.1` dan `lembar-*` selesai sebelum G4; selama KG5 keduanya tidak berubah ([§8](#8-lembar-kamus-data-dan-dictionary_version), [§10.1](#101-satu-sumber-kolom-tabel-bertanda-di-02)). |

---

## 3. Yang tidak berubah

| Bagian | Aturan |
| --- | --- |
| Penulisan event | `EventService::ingest()`, `record()`, `recordDropped()`, `recordAudioBatch()`: idempoten per `client_event_id`, `sequence_no`, `clock_offset_ms` dari `client_sent_at`, prinsip event yang hanya boleh ditulis server (`SERVER_ONLY`) |
| Antrean event di perangkat | `core/events.js`, `events_dropped`, batas `maxEventsPerBatch` |
| Tabel jawaban | `challenge_attempts`, `item_responses`, `item_tries` tetap sumber analisis jawaban dan percobaan. Event `answer_submitted` dan `wrong_target_clicked` hanya penanda di urutan kejadian ([§4](#4-katalog-event)) |
| Pembuatan berkas unduhan | `ExportService::build()`: antre, `running`, `done` atau `failed`; XLSX lewat `ExcelWriter`; CSV-ZIP UTF-8 dengan BOM; SHA-256; berkas di `writable/exports/` dihapus setelah 7 hari (`exportRetentionDays`); batas baris `exportMaxRawEvents` |
| Mode berlabel dan berkode | `ExportLabels`; waktu ISO 8601 WIB dengan milidetik |
| Laporan PDF satu siswa | `ReportService`; pemakainya diatur bagian 4 |
| Data mentah di layar | `/admin/data/{dimensi}` untuk `ketua` (27), dengan kolom yang sama dengan unduhan |
| Kunci jawaban | Tidak pernah dikirim ke peramban siswa |
| Bintang dan Serpihan Cahaya | Tetap disimpan dan diunduh, tetapi tidak dianalisis sebagai prestasi (01) |

---

## 4. Katalog event

Kelas baru `App\Libraries\EventCatalog` menggantikan daftar `Config\Gelita::$eventTypes` (`app/Config/Gelita.php:96-112`) dan dua daftar di `RawDataService` (`NAVIGATION_EVENTS`, `COMPLETION_EVENTS`, baris 86–95). Setiap jenis event punya satu pemilik.

| Event | Pemilik | Ditulis | Dipakai |
| --- | --- | --- | --- |
| `session_started`, `session_resumed`, `session_paused`, `session_abandoned`, `session_completed` | Peneliti | Server | Analitik 1, 5 |
| `level_opened`, `level_completed`, `intro_completed` | Peneliti | Server dan klien | Analitik 1 |
| `challenge_opened`, `challenge_checked`, `challenge_completed`, `challenge_abandoned` | Peneliti | Server dan klien | Analitik 1, 3, 4 |
| `challenge_tries_exhausted`, `clue_failed` | Peneliti | Hanya server | Analitik 1 |
| `answer_submitted`, `wrong_target_clicked` | Peneliti | Server | Urutan kejadian; analisis memakai `item_tries` |
| `hint_opened`, `audio_play`, `audio_autoplay`, `audio_replay`, `audio_completed` | Peneliti | Klien | Analitik 7 |
| `screen_viewed` | Peneliti | Hanya server | Analitik 8 |
| `overlay_opened`, `library_opened`, `library_page_viewed`, `reading_progress` | Peneliti | Klien | Analitik 8 |
| `page_hidden`, `page_visible` | Peneliti | Klien | Analitik 5 |
| `locale_changed`, `feedback_submitted` | Peneliti | Server | Kovariat bahasa; pelengkap kualitatif |
| **`dialogue_completed`** (baru, K5) | Peneliti | Klien | Lencana "Belum didengar"; analitik 8 |
| **`passage_reopened`** (baru, [§6.3](#63-membuka-lagi-bacaan-passage_reopened)) | Peneliti | Klien | Analitik 8 (`material_returns` bagian b) |
| **`empty_tap`** (baru, [§6.2](#62-ketukan-di-luar-bagian-ketuk-empty_tap)) | Peneliti | Klien | Analitik 6 (NON-SEN) |
| **`input_idle`** (baru, [33 §5.2](33_IMPLEMENTASI_MODE_PENELITIAN.md#52-jeda-masukan-event-input_idle)) | Peneliti | Klien | Analitik 5 (jeda masukan ≥ 20 detik) |
| **`scenario_failed`** (baru, [32 §5.6](32_IMPLEMENTASI_SKENARIO_DIGITAL.md#56-event-scenario_failed)) | Peneliti | Hanya server | Analitik 1 (`skipped_failed_count`) |
| `password_changed`, `viewport_changed`, `package_changed` | Admin | Server dan klien | Log teknis |
| `events_dropped` | Admin | Hanya server | Log teknis; jumlahnya di panel pemantauan (28) |
| `answer_selected`, `audio_pause` | Dihapus | — | — |
| `dialogue_advanced` | Dihapus, diganti `dialogue_completed` | — | — |
| `answer_changed` | Dihapus, digabung ke `answer_submitted` | — | — |

**Bentuk kelas:**

```php
// Owner of every event type (revision 10, part 3). The only list of event types.
public const RESEARCH = [/* … */];
public const ADMIN    = ['password_changed', 'viewport_changed', 'package_changed', 'events_dropped'];
public const SERVER_ONLY = ['screen_viewed', 'challenge_tries_exhausted', 'clue_failed', 'scenario_failed', 'events_dropped'];

public static function accepted(): array;         // RESEARCH + ADMIN, read by EventService
public static function isResearch(string $type): bool;
public static function isAdmin(string $type): bool;
```

- `EventService::SERVER_ONLY` pindah ke katalog; `EventService` membacanya dari sana.
- Kiriman klien lama yang masih membawa jenis yang dihapus (antrean perangkat sebelum pembaruan) ditolak dengan alasan `event_type_unknown`, seperti jenis tak dikenal sekarang. Penolakan ini tidak dihitung sebagai `events_dropped`.
- `Admin.php` (label jenis event, baris 360–380: `dialogue_advanced` 360, `answer_selected` 368, `audio_pause` 380) mengikuti katalog: label jenis yang dihapus dibuang, label tiga jenis baru ditambah.

---

## 5. Pembersihan log (perubahan no. 7)

### 5.1 Event yang dihapus dan penggantinya

| Event | Yang mengirim sekarang | Sesudah |
| --- | --- | --- |
| `answer_selected` | `game/challenge.js:88-95` (`selected()`), dipanggil `engines/puzzle.js`, `rumpang.js`, `boleh.js` | `selected()` tetap mencatat waktu isian per butir (`first_input_ms`, `last_input_ms`, `ready_ms`) tetapi tidak mengirim event |
| `answer_changed` | Server, `ChallengeService::submitAnswer()` baris 227–242 | [§5.2](#52-answer_changed-digabung-ke-answer_submitted) |
| `dialogue_advanced` | `game/narrator.js:104`, juga `game/dialogue.js`, `game/intro.js`, `game/map.js` | [§5.3](#53-dialogue_advanced-diganti-dialogue_completed) |
| `audio_pause` | `EventService::AUDIO_EVENT_TYPES` dari aksi `pause` (`core/audio.js:419`) | [§5.4](#54-audio_pause-dan-lama-mendengar) |

Hitungan perubahan jawaban per butir tetap ada di `item_responses.change_count` dan `challenge_attempts.answer_change_count` (01: "cukup hitungan perubahan per butir").

`Simulation\Simulator` (baris 36, 467 untuk `answer_selected`, dan 995–1010) berhenti membuat `answer_selected` dan `dialogue_advanced`. Simulator membuat `dialogue_completed` di slide terakhir.

### 5.2 `answer_changed` digabung ke `answer_submitted`

Temuan kode: 01 menulis `answer_changed` sebagai "setiap kali siswa mengganti pilihan sebelum Cek". Di kode, event itu ditulis **server** di Cari Objek, menggantikan `answer_submitted` bila jawaban petunjuk berbeda dari kiriman sebelumnya (`ChallengeService.php:229`). Jadi event itu adalah percobaan yang dinilai, bukan perubahan sebelum Cek. Perubahan sebelum Cek adalah `answer_selected`.

Sesudah (K2):
- `submitAnswer()` selalu menulis `answer_submitted`. Payload: `try_no`, `correct`, `first_pass`, `change_count`, dan **`changed`** (benar/salah).
- Migrasi mengganti `event_type` baris `answer_changed` menjadi `answer_submitted` dan menambah `changed: true` ke payload ([§5.8](#58-data-yang-sudah-tersimpan)).

### 5.3 `dialogue_advanced` diganti `dialogue_completed`

Temuan kode: lencana "Belum didengar" di peta wilayah dihitung dari `dialogue_advanced` dengan `context = region_intro` dan `index >= total` (`GameEventLogModel::heardRegionIntros()` baris 116–129, `reachedLastSlide()`; dipakai `MapController::heardRegionIntros()` baris 92). Menghapus event per slide tanpa pengganti membuat lencana itu tidak pernah hilang.

Sesudah (K5):
- **Event baru `dialogue_completed`**, dikirim klien **sekali** saat slide terakhir sebuah cerita tercapai. Payload: `context` (kode konteks dialog, misalnya `region_intro`, `ending`, `map_done`) dan `total` (banyak slide). `level_id` diisi bila ceritanya milik satu wilayah.
- Pengirimnya satu tempat, `game/narrator.js` (baris 104), yang sekarang mengirim `dialogue_advanced` di setiap perpindahan slide. Yang hanya diperbarui komentar atau labelnya (dicek 2 November 2026): `game/dialogue.js`, `game/intro.js`, `game/map.js`, `core/events.js` (baris 19), `core/curtain.js` (baris 353), `Game\DialogueController`, `Game\MapController` (baris 87), `partials/dialogue-scene.php`, `game/ending.php`, dan deskripsi `event_type` di `ExportLabels` (baris 200).
- `heardRegionIntros()` membaca `dialogue_completed` dengan `context = region_intro`. `reachedLastSlide()` dihapus, karena event baru sudah berarti "sampai slide terakhir".
- Kembali ke slide sebelumnya atau membuka cerita lagi tidak mengirim apa pun sampai slide terakhir tercapai lagi. Membuka cerita yang sama sampai akhir dua kali menghasilkan dua event; analitik 8 boleh menghitungnya.
- Migrasi membuat satu `dialogue_completed` untuk setiap pasangan sesi game, konteks, dan wilayah yang pernah mencapai slide terakhir, sebelum menghapus `dialogue_advanced` ([§5.8](#58-data-yang-sudah-tersimpan)).

### 5.4 `audio_pause` dan lama mendengar

Temuan kode: lama mendengar pemutaran yang belum selesai hanya terkirim lewat aksi `pause` (`core/audio.js:414-419`). Pemutaran yang selesai mengirim `complete`. Pemutaran yang dihentikan tanpa jeda, yaitu karena diulang (`restart()`, baris 427–437, mengosongkan `listenedMs` sebelum terkirim) atau karena halaman ditinggal, tidak mengirim lama mendengarnya sama sekali. Alasan di 01 ("lama mendengar sudah tercatat di `listened_ms`") hanya benar kalau baris `pause` tetap ada.

Dibetulkan 2 November 2026: saat narasi lain mulai, `stopAllNarration()` (baris 228–232) memanggil `pause()` pada pemutar lain, jadi lama mendengarnya **sudah** terkirim. Draf 30 Oktober 2026 keliru memasukkannya ke celah. Temuan dan K6 tetap berlaku.

Sesudah (K6):
- `EventService::AUDIO_EVENT_TYPES` (baris 35–38) tidak lagi memetakan `pause` ke event mentah. Aksi `pause` tetap menulis baris `audio_usage_events` dengan `listened_ms`, tanpa baris `game_event_logs`.
- `audio_usage_events.action` tetap memakai lima nilai yang tersimpan sekarang; tidak ada nilai baru.
- Klien mengirim `pause` dengan lama mendengar yang sudah terkumpul juga saat:
  - pemutaran diulang, sebelum `listenedMs` dikosongkan;
  - halaman disembunyikan atau ditinggal (`pagehide`), lewat antrean yang sama (`core/events.js:413` sudah mengirim antrean di `pagehide`).
- Narasi lain mulai: tidak perlu diubah (lihat di atas).
- Lembar unduhan `bantuan` menampilkan **satu baris per pemutaran** (`play_index`): aksi pembuka (`play`, `autoplay`, `replay`), `listened_ms` terbesar, dan `completed`. Baris `pause` tidak tampil sendiri.
- Perubahan no. 5 (waktu aktif) membutuhkan selang narasi diputar. Baris `audio_usage_events` itu sumbernya; rancangannya di [33 §5.3](33_IMPLEMENTASI_MODE_PENELITIAN.md#53-selang-narasi-audio_usage_eventsclock_offset_ms): kolom baru `audio_usage_events.clock_offset_ms` (33 K10), diisi dengan offset batch yang sama dengan event mentah, supaya jam akhir pemutaran tetap bisa dikoreksi walaupun `pause` tidak lagi punya event mentah. `occurred_at` tetap jam perangkat; lembar `bantuan` tidak mendapat kolom offset.

### 5.5 Rincian perangkat

Data perangkat ditulis di dua tempat: `device_logs` (satu baris per daftar, masuk, dan lanjut; `DeviceProfile::capture()`) dan ringkasannya di `game_sessions` (`DeviceProfile::sessionSummary()`).

**`device_logs`**

| Kolom | Sesudah | Alasan |
| --- | --- | --- |
| `user_agent`, `client_hints_json` | Dibuang | 01: dihapus. Keduanya tetap dibaca saat mendaftar atau masuk untuk mengurai OS dan peramban, lalu tidak disimpan |
| `device_brand`, `device_model` | Dibuang | 01: dihapus |
| `pixel_ratio`, `touch_points` | Dibuang | 01: dihapus. `touch_points` tetap dipakai sesaat untuk koreksi iPad dan `is_touch` |
| `timezone`, `language` | Dibuang | 01: dihapus |
| `os_platform` (arsitektur) | Dibuang | Tafsiran: termasuk rincian perangkat, tidak dipakai |
| `os_name`, `os_version`, `browser_name`, `browser_version`, `browser_engine` | Admin | 01: nama OS dan peramban dengan versinya |
| `device_type`, `screen_w`, `screen_h`, `viewport_w`, `viewport_h`, `orientation`, `is_standalone`, `is_bot`, `corrected`, `detector`, `via`, `created_at` | Admin | Teknis, untuk memeriksa masalah. Ukuran layar untuk peneliti diambil dari `game_sessions.screen_size` |
| `ip_hash` | Admin | Tidak diubah; bagian 4 mengatur IP di sesi peramban |

**`game_sessions`**

| Kolom | Sesudah |
| --- | --- |
| `device_type`, `screen_size` | Peneliti (kovariat, 11) |
| `os_name`, `browser_name`, `is_touch`, `app_client_version`, `package_code`, `package_mode` | Admin |
| `ip_hash` | Admin, tidak pernah dibaca layanan data (seperti sekarang) |

**Client Hints yang diminta.** `Config\Gelita::$clientHints` (baris 29–32) dikurangi menjadi `Sec-CH-UA-Platform-Version` dan `Sec-CH-UA-Full-Version-List`, yang dibutuhkan untuk versi OS dan peramban. `Sec-CH-UA-Model`, `Sec-CH-UA-Arch`, dan `Sec-CH-UA-Bitness` tidak diminta lagi (`App\Filters\ClientHintsFilter`). `DeviceProfile::UA_DATA_KEYS` hanya meneruskan `platform`, `platformVersion`, `fullVersionList`, `mobile`, dan `brands`.

**Ikut diperbarui:** `DeviceProfile::LENGTHS` dan `fromParts()`, `DeviceLogModel::$allowedFields`, `RawDataService::COLUMNS['perangkat']` (pindah ke layanan log teknis), dan formulir yang mengirim `device_json` (`game/register/step-3.php`, `game/login.php`) supaya tidak mengirim isian yang tidak disimpan lagi.

### 5.6 Data bertanda Admin dan halaman *Log teknis*

**Halaman** `/admin/log-teknis` (kemampuan `tech_logs.view`, hanya admin, K15), layanan baru `App\Services\TechLogService`. Tampilannya tabel admin yang sama dengan data mentah (`AdminTable`), dengan tiga tab:

| Tab | Isi | Sumber |
| --- | --- | --- |
| Perangkat | Kolom Admin `device_logs` di [§5.5](#55-rincian-perangkat), dengan kode sesi dan kode peserta | `device_logs` |
| Event teknis | `password_changed`, `viewport_changed`, `package_changed`, `events_dropped`, dengan `occurred_at`, `server_received_at`, `clock_offset_ms`, dan payload | `game_event_logs`, jenis Admin di katalog |
| Sesi | Kode sesi, kode peserta, status, mulai dan terakhir aktif, `os_name`, `browser_name`, `is_touch`, `app_client_version`, `package_code`, `package_mode`, rilis | `game_sessions` |

- Tanpa jawaban, skor, percobaan, bantuan, navigasi, atau event penelitian.
- Pencarian dengan kode peserta. Nama tidak tampil di halaman ini; admin membuka halaman akun siswa bila perlu.
- Setiap tab bisa diunduh sebagai CSV, dicatat di log audit sebagai `tech_log_csv` dengan nama tab. Ini bukan data penelitian, jadi tidak melanggar aturan "admin tidak mengunduh data penelitian".
- Kartu ringkasan admin (27 §8) mendapat angka teknis dari layanan ini: sesi aktif, event hilang 7 hari terakhir, sebaran OS dan peramban.

### 5.7 Sesi yang diam (`markStale`)

`GameSessionModel::markStale()` (baris 68–78, dipanggil `RetentionService` baris 310) mengubah semua sesi yang diam lebih dari `sessionIdleMinutes` menjadi `paused` dalam satu UPDATE, tanpa event. Analitik 1 (ditinggal) dan 5 (potongan pengerjaan) membutuhkan saat sesi berhenti.

Sesudah (K14):
1. Ambil id sesi `active` yang `last_active_at` lebih lama dari batas.
2. Untuk setiap id, UPDATE bersyarat `status = 'paused' WHERE id = ? AND status = 'active' AND last_active_at < ?`.
3. Hanya bila satu baris berubah, tulis `session_paused` dengan payload `{via: 'idle', last_active_at}`. Waktu event = `last_active_at` sesi itu, bukan waktu pembersihan, supaya garis waktu tidak bergeser ke jam perintah dijalankan.

Dua pemanggil bersamaan tidak menulis event ganda, karena hanya satu yang mengubah barisnya. Dalam mode penelitian, penutupan sesi lapangan tetap memakai `via: field_session_closed` dari [28 §6.6](28_IMPLEMENTASI_SESI_LAPANGAN.md#66-sesi-ditutup-saat-siswa-sedang-bermain).

### 5.8 Data yang sudah tersimpan

**Sebelum migrasi:** `php spark gelita:log:hitung` mencetak, tanpa mengubah apa pun:
- banyak baris `game_event_logs` per jenis yang dihapus atau diganti;
- banyak `dialogue_completed` yang akan dibuat;
- banyak baris `device_logs` yang kolom dibuangnya berisi.

Pengembang mencatat angkanya, lalu menjalankan migrasi.

**Server produksi.** Migrasi ini menghapus data secara permanen dan tidak bisa dibatalkan. Di server produksi migrasi ini **hanya dijalankan bila pengembang memutuskannya** ([04](04_CATATAN_KEPUTUSAN.md), 1 November 2026), sama dengan `003400`. Karena `003400` ada lebih dulu di urutan migrasi, server produksi memang tidak bisa menerima kode bagian 2–4 tanpa keputusan itu; penghapusan tetap di migrasi, tidak dipisah menjadi perintah sendiri ([§19](#19-keputusan-pengembang) no. 5). Arsip di luar sistem sebelum migrasi tidak dibuat: server produksi belum memuat data studi, karena belum ada kegiatan dengan anak sebelum persetujuan etik (no. 10). Jumlahnya tercatat lewat `gelita:log:hitung` dan baris audit.

**Migrasi `2026-01-01-003600_CleanResearchLogs`** (K3), dalam potongan 2.000 baris per id supaya tabel tidak terkunci lama:
1. Buat `dialogue_completed` dari `dialogue_advanced` yang mencapai slide terakhir: satu baris per sesi game, `context`, dan `level_id`. `occurred_at` dan `sequence_no` dari baris slide terakhir yang pertama, `client_event_id` = `mig-dc-{session_id}-{context}-{level_id}`, payload `{context, total, via: 'migration'}`.
2. Ganti `answer_changed` menjadi `answer_submitted` dan tambahkan `changed: true` ke payload. Payload diubah di PHP, bukan dengan fungsi JSON SQL, supaya sama di MariaDB dan SQLite.
3. Hapus baris `answer_selected`, `dialogue_advanced`, dan `audio_pause`.
4. Buang kolom `device_logs` yang ditandai "Dibuang" di [§5.5](#55-rincian-perangkat).
5. Tambahkan kolom baru bagian ini ([§12](#12-skema-database)).
6. Tulis satu baris audit `research_log_cleanup` dengan pelaku kosong (`via: migration`) dan jumlah per langkah.

**`down()`** mengembalikan kolom yang dibuang (kosong), membuang kolom baru, dan mengganti `dialogue_completed` hasil migrasi kembali menjadi satu `dialogue_advanced` per baris. Event per slide, `answer_selected`, `audio_pause`, dan isi kolom perangkat tidak kembali; ini ditulis di komentar migrasi.

**SQLite (tes).** Membuang kolom membangun ulang tabel `device_logs`, jadi kunci asingnya dibuang dulu (pola migrasi bagian 1). Perilaku di MariaDB dicek sekali di mesin pengembangan dengan salinan database.

---

## 6. Data mentah baru dari kamus (perubahan no. 14)

Kamus menempatkan tiga syarat data di no. 14 ([11 → Yang harus disediakan sistem di tahap 3](11_KAMUS_DATA.md#yang-harus-disediakan-sistem-di-tahap-3)). Ketiganya disimpan mentah. Angka turunannya (`decision_ms`, `rapid_guess`, `near_miss_count`, `material_returns`) dihitung lapis turunan (no. 9).

### 6.1 Saat butir bisa dijawab (`ready_ms`)

Kolom baru **`item_tries.ready_ms`**: milidetik sejak tantangan dibuka (`performance.now()` di `game/challenge.js`), saat butir itu bisa dijawab untuk percobaan ini. Rujukan waktunya sama dengan `first_input_ms` dan `last_input_ms`, sehingga `decision_ms = last_input_ms − ready_ms`.

| Jenis | `ready_ms` | Dikirim lewat |
| --- | --- | --- |
| Benar atau Salah, kartu jangkar | Masukan terakhir di pengerjaan itu **sebelum** pilihan terakhir di kartu ini; atau layar tampil (0); atau umpan balik Cek sebelumnya selesai, mana yang paling akhir (11 → Analitik 6, waktu putus perkiraan) | `/check`, per butir, di samping `first_input_ms` dan `last_input_ms` |
| Cari Objek, kedua babak | Petunjuk aktif tampil, atau umpan balik ketukan sebelumnya selesai | `/responses` (`submit`) dan percobaan dari ketukan salah |
| Isi Rumpang | Kosong; NON-TEB tidak dihitung untuk Isi Rumpang (11) | — |
| Susun Gambar | Kosong; tidak dinilai | — |
| Skenario Digital | `ready_ms` = pilihan tindakan tampil; `reason_ready_ms` = pilihan alasan tampil ([32 §6.4](32_IMPLEMENTASI_SKENARIO_DIGITAL.md#64-waktu)) | Kiriman satu percobaan skenario (`submitScenario()`, 32 §5.4) |

**Klien.**
- `selected()` di `game/challenge.js` menyimpan jam masukan terakhir pengerjaan itu. Saat kartu dipilih, jam itu menjadi calon `ready_ms` kartu; pilihan berikutnya di kartu yang sama memperbaruinya.
- `engines/cari.js` sudah mengukur dari petunjuk tampil (`shownAt`, baris 118 dan 178), tetapi mengirim **selisihnya** sebagai `duration_ms`, yang disimpan di `last_input_ms` (`ChallengeService.php:224`). Akibatnya `last_input_ms` Cari Objek memakai rujukan waktu yang berbeda dari tantangan lain.
- Sesudah, `cari.js` mengirim `ready_ms` dan `last_input_ms`, keduanya sejak tantangan dibuka. Kamus mencatat bahwa `last_input_ms` Cari Objek sebelum perubahan ini memakai rujukan lama ([§20](#20-akibat-ke-dokumen-lain), untuk `kamus-1.1`).

**Server.** `ChallengeService::submitCheck()` (baris 371–377) dan jalur `submitAnswer()` meneruskan `ready_ms` ke `recordTry()`. Nilai dijepit seperti `clientMs()`: angka bulat 0 sampai batas yang sama, selain itu kosong. `ready_ms` lebih besar dari `last_input_ms` disimpan kosong.

### 6.2 Ketukan di luar bagian ketuk (`empty_tap`)

Sekarang ketukan di adegan Cari Objek hanya diteruskan bila mengenai benda (`engines/cari.js:298-301`). Ketukan di tempat kosong tidak tercatat.

**Event baru `empty_tap`** dari klien, lewat antrean event biasa (tetap tercatat saat jaringan putus):
- referensi: `attempt_id`, `item_id` petunjuk yang aktif, `node_id`;
- payload: `x_pct`, `y_pct` (posisi ketukan dalam persen adegan, sama dengan sistem koordinat benda, sehingga perbesaran tidak mengubahnya), `round` (1 atau 2), `t_ms` (sejak tantangan dibuka).

Yang **tidak** dilakukan klien:
- menghitung jarak ke kunci, karena letak kunci tidak pernah dikirim ke peramban;
- memakai kesempatan atau memberi umpan balik; game tetap diam seperti sekarang (11, penegasan).

Jarak ke bagian ketuk kunci petunjuk aktif (24 dp) dihitung lapis turunan dari data adegan di bank (`near_miss_count`, `empty_tap_count`). Ini tafsiran kalimat 11 "dicatat dengan jaraknya": yang dicatat koordinatnya, jaraknya diturunkan.

Batas: paling banyak 30 `empty_tap` per petunjuk per pengerjaan dikirim (angka di konfigurasi, dicek di KG3a dan KG3b dan ditetapkan sebelum versi dikunci di G4; [§19](#19-keputusan-pengembang) no. 9); selebihnya dihitung di klien dan dikirim sebagai satu `empty_tap` dengan `overflow` = banyaknya. Ini mencegah antrean penuh karena anak mengetuk berulang-ulang.

### 6.3 Membuka lagi bacaan (`passage_reopened`)

**Event baru `passage_reopened`** dari klien, untuk bagian (b) `material_returns` di analitik 8:
- dikirim saat panel bacaan (`details.passage`) dibuka, atau label "Berdasarkan Teks" diketuk (`engines/boleh.js:29-44`);
- hanya **setelah masukan pertama** di pengerjaan itu (kamus menghitung yang sesudahnya saja);
- payload: `via` (`panel` atau `label`), `passage` (urutan bacaan di layar), `t_ms`.

Isi Rumpang menampilkan bacaannya terus di layar, jadi tidak mengirim event ini. Bagian (a), membuka Pustaka wilayah saat pengerjaan belum tertutup, sudah tercatat lewat `library_opened`.

### 6.4 Yang dirancang di perubahan lain

| Syarat kamus | Perubahan |
| --- | --- |
| Setiap jeda masukan 20 detik atau lebih dicatat; selang narasi diputar | No. 5, [33](33_IMPLEMENTASI_MODE_PENELITIAN.md) §5.2–5.3 |
| `error_code`, `reason_code`, skor tindakan di setiap percobaan | No. 1, [31](31_IMPLEMENTASI_BANK_SOAL_BARU.md) §10; no. 2, [32](32_IMPLEMENTASI_SKENARIO_DIGITAL.md) §10 |
| Penutupan skenario setelah kesempatan ketiga | No. 2, [32](32_IMPLEMENTASI_SKENARIO_DIGITAL.md) §5.6 (`scenario_failed`) |
| Kode konteks narasi instruksi | No. 6, [32](32_IMPLEMENTASI_SKENARIO_DIGITAL.md) §8 |
| Pengerjaan pertama yang terputus dilanjutkan | No. 16, [33](33_IMPLEMENTASI_MODE_PENELITIAN.md) §7.3 |
| Ambang T_idle dan T_teb di konfigurasi studi; lapis turunan berversi | No. 9, [35](35_IMPLEMENTASI_LAPIS_DATA_TURUNAN.md) §5; no. 16, [33](33_IMPLEMENTASI_MODE_PENELITIAN.md) §6 |

---

## 7. Unduhan data penelitian (perubahan no. 14)

### 7.1 Siapa, cakupan, dan format

- **Hanya `ketua`** (`research.export`, 27 K14), selalu berkode, dengan cakupan `StaffScope::research()`.
- **Format** tetap XLSX dan CSV-ZIP, berlabel atau berkode, dengan filter studi, wilayah, tantangan, sekolah, bahasa, dan tanggal (`ExportService::FILTER_KEYS`). Filter `province_code` dibuang, karena provinsi adalah data admin.
- **Filter baru:** `field_session_code` dan `meeting_no` (28).
- **Studi (K16).** Unduhan selalu terikat satu studi (`study_id` wajib), mode penelitian menyala atau mati. Setiap uji punya baris `research_studies` sendiri (uji kelompok kecil KG3a, uji kelompok menengah KG3b, pengambilan data utama KG5), jadi data uji coba di sekolah `U` dan data utama tidak pernah ada di satu berkas. Bila belum ada studi, halaman unduh menampilkan pesan untuk membuat studi dulu (`exportNeedsStudy`, [§14](#14-teks-panel)). Data akun uji `uji.*` dan simulasi `sim_*` tidak pernah ikut (sekolah `V01` dan nama pengguna berawalan `sim_` dikeluarkan di `research()`).

### 7.2 Lembar

Urutan resmi sesudah bagian ini, termasuk lembar dari bagian 4–9 (urutan baris tabel ini, [§19](#19-keputusan-pengembang) no. 13). `ExportService::SHEETS` dan `RawDataService::DIMENSIONS` membaca satu daftar.

| Lembar | Isi | Sumber | Status |
| --- | --- | --- | --- |
| `kamus` | Kamus data | `ResearchDictionary`, `ExportLabels` ([§8](#8-lembar-kamus-data-dan-dictionary_version)) | Diubah |
| `peserta` | Satu baris per siswa | `participants`, `participant_seats` (28) | Diubah |
| `sesi_lapangan` | Satu baris per sesi lapangan | `field_sessions` (28) | **Baru** |
| `sesi` | Satu baris per sesi game | `game_sessions` | Diubah |
| `progres` | Kemajuan per siswa per studi | `participant_progress` (28) | Diubah sumbernya |
| `pengerjaan`, `jawaban`, `percobaan` | Pengerjaan, jawaban per butir, percobaan | Tabel jawaban | Diubah |
| `navigasi` | Event navigasi bertanda Peneliti | `game_event_logs` | Diubah |
| `bantuan` | Bantuan dan narasi, satu baris per pemutaran | `audio_usage_events`, `hint_opened` | Diubah |
| `selesai` | Event penyelesaian | `game_event_logs` | Diubah |
| `event` | Semua event bertanda Peneliti (K1) | `game_event_logs` | Diubah: sekarang untuk `ketua` |
| `masukan` | Kritik dan saran | `participant_feedback` | Tetap |
| `butir` | Satu baris per butir | `challenge_items` | Diubah |
| `kode_pilihan` | Kode kesalahan bank per pilihan (rumpang × kata, petunjuk × benda, petunjuk × bagian, pilihan skenario), satu baris per `item_error_codes` dan per pilihan skenario, yang berlaku saat diunduh; hanya `ketua`, seperti kunci | `item_error_codes`, `scenario_options` ([31 §13.3](31_IMPLEMENTASI_BANK_SOAL_BARU.md#133-lembar-butir-di-unduhan)) | **Baru** (31) |
| `turunan_peserta` | Lapis turunan, satu baris per peserta; hanya studi `field_mode = 1` | `derived_participants` ([35 §10.1](35_IMPLEMENTASI_LAPIS_DATA_TURUNAN.md#101-lembar-baru)) | **Baru** (35) |
| `turunan_sesi` | Peserta × sesi lapangan | `derived_field_sessions` (35) | **Baru** (35) |
| `turunan_tantangan` | Peserta × tantangan | `derived_challenges` (35) | **Baru** (35) |
| `turunan_pengerjaan` | Pengerjaan | `derived_attempts` (35) | **Baru** (35) |
| `turunan_butir` | Peserta × butir | `derived_items` (35) | **Baru** (35) |
| `turunan_percobaan` | Percobaan | `derived_tries` (35) | **Baru** (35) |
| `turunan_pengamatan` | Pengamatan L4 | `derived_observations` (35) | **Baru** (35) |
| `turunan_tes` | Jawaban tes dengan `rapid_guess` | `derived_test_responses` ([35 §6.8](35_IMPLEMENTASI_LAPIS_DATA_TURUNAN.md#68-derived_test_responses-satu-baris-per-jawaban-tes)) | **Baru** (35) |
| `tes_jawaban` | Satu baris per satuan jawaban per siswa per tes (`pre`/`post`): jawaban mentah, skor, kode kesalahan, `not_reached`, waktu tampil, penggantian jawaban, versi isi | `instrument_responses` ([34 §11.4](34_IMPLEMENTASI_MODUL_TES_DAN_SKALA_SIKAP.md#114-data-untuk-kamus-dan-unduhan)) | **Baru** (34) |
| `tes_bagian` | Satu baris per bagian tes per siswa per kesempatan: jam mulai dan kirim, ditutup batas waktu, menit tambahan, skor mentah | `instrument_parts`, `instrument_blocks` (34) | **Baru** (34) |
| `angket` | Satu baris per siswa (16): status pengisian dan 41 kolom ang-01–ang-17 | Tabel modul (`instrument_sittings`, `instrument_responses`), 34 | **Baru** (34) |
| `sikap` | Satu baris per siswa (17): `skp_*`, waktu per butir, `feedback_rating` | Tabel modul (`instrument_sittings`, `instrument_responses`), 34; `participant_feedback` | **Baru** (34) |
| `risiko_gabungan` | Angka gabungan butir risiko daring ([§7.6](#76-butir-risiko-daring)) | Sesi jenis `risk` di tabel modul (34 §8.3) | **Baru** |
| `izin` | Satu baris per siswa di unduhan itu: kolom daftar izin berkode ([30 §10.1](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md#101-daftar-izin-berkode)), disaring ke siswa unduhan itu; hanya studi `field_mode = 1` | `participant_consents` (30) | **Baru** (30, keputusan no. 9); kodenya ditulis bagian 4 |
| `l1_kondisi`, `l1_penyimpangan`, `l2_kursi`, `l3_bantuan`, `l4_bromp`, `l5_wawancara`, `l6_sesi` | Lembar lapangan yang diimpor ([§10](#10-templat-dan-impor-lembar-lapangan-perubahan-no-15)) | `field_sheet_rows` | **Baru** |
| `perangkat` | — | — | **Keluar** dari unduhan; pindah ke log teknis ([§5.6](#56-data-bertanda-admin-dan-halaman-log-teknis)) |

Lembar dari perubahan lain sudah masuk daftar ini: `kode_pilihan` ([31](31_IMPLEMENTASI_BANK_SOAL_BARU.md) §13.3), delapan lembar turunan ([35](35_IMPLEMENTASI_LAPIS_DATA_TURUNAN.md) §10.1, termasuk `turunan_tes`), empat lembar tes, angket, dan skala sikap (no. 21, 22; [34 → Data untuk kamus dan unduhan](34_IMPLEMENTASI_MODUL_TES_DAN_SKALA_SIKAP.md#114-data-untuk-kamus-dan-unduhan)) beserta kolom `peserta` tambahan `pre_form` … `post_capped` dari 13 ([§7.3](#73-kolom-yang-berubah-per-lembar)), dan `risiko_gabungan` ([§7.6](#76-butir-risiko-daring); sumbernya sesi jenis `risk`). Urutannya tetap: … `butir`, `kode_pilihan`, lembar `turunan_*`, `tes_jawaban`, `tes_bagian`, `angket`, `sikap`, `risiko_gabungan`, `izin`, lalu lembar lapangan `l1_…`–`l6_…` ([§19](#19-keputusan-pengembang) no. 13). Sesi modul `is_preview = 1` (sesi pratinjau validator, 34 §5.5) tidak pernah masuk unduhan, sesi jenis `risk` tidak pernah masuk lembar per siswa (hanya angka gabungan di `risiko_gabungan`), dan contoh soal tes (`tes-ct-*`) tidak masuk `tes_jawaban`.

### 7.3 Kolom yang berubah per lembar

Hanya yang berubah. Kolom lain tetap seperti `RawDataService::COLUMNS` (baris 107–240).

| Lembar | Keluar | Masuk |
| --- | --- | --- |
| `peserta` | `display_name`, `username`, `school_name`, `school_npsn` (sudah tidak untuk `research()`, 27); `country`, `province`, `district` (01: admin); `school_ref` | `school_code`, `rombel`, `booklet` (28); dari modul tes (34 §11.4): `pre_form`, `post_form`, `pre_date`, `post_date`, `pre_makeup`, `post_makeup`, `pre_d1`–`post_d3` (dari `raw_score`), `pre_minutes_p1` … `post_minutes_p23`, `pre_capped`, `post_capped` |
| `sesi_lapangan` | — | `field_session_code`, `school_code`, `rombel`, `meeting_no`, `makeup_no`, `main_session_code`, `region_opened`, `session_date`, `opens_at`, `closes_at`, `closed_at` (28 §17). Riwayat token tidak ikut |
| `sesi` | `os_name`, `browser_name`, `is_touch`, `app_client_version`, `package_code`, `package_mode` | `field_session_code`, `meeting_no` |
| `progres` | `session_code` | `study_code`; kolom lain dari `participant_progress` |
| `pengerjaan` | — | `field_session_code`, `meeting_no`; `variant_source`, `booklet_at_open` ([31 §11.4](31_IMPLEMENTASI_BANK_SOAL_BARU.md#114-kolom-pengerjaan)); `after_withdrawal` (35 §7) |
| `jawaban` | — | `answer_key` (K1), `field_session_code`; `after_withdrawal` |
| `percobaan` | — | `ready_ms` ([§6.1](#61-saat-butir-bisa-dijawab-ready_ms)), `field_session_code`; `error_code` (31 §10), `manual_code` (diisi impor L5, [§10.4](#104-mengunggah-lembar-yang-sudah-diketik)); `action_key`, `action_score`, `reason_code`, `reason_ready_ms`, `action_last_input_ms`, `reason_last_input_ms` (32 §6.4, §10; kosong untuk engine lain, `reason_key` tidak diekspor); `after_withdrawal` |
| `navigasi` | Jenis `dialogue_advanced`, `viewport_changed`, `package_changed`, `events_dropped`; kolom `occurred_at`, `server_received_at`, `clock_offset_ms` | Jenis `dialogue_completed`, `passage_reopened`; kolom `occurred_at_corrected`, `offset_known`, `field_session_code`, `after_withdrawal` |
| `bantuan` | Baris per aksi | Baris per pemutaran ([§5.4](#54-audio_pause-dan-lama-mendengar)); `occurred_at_corrected`; `audio_context` dari `audio_assets.context_code` (32 §10: `challenge_instruction`, `scenario_situation`, `post_clue`, `hunt_clue` terbedakan); `after_withdrawal` |
| `selesai` | `occurred_at` | `occurred_at_corrected`, `offset_known`, `field_session_code`, `after_withdrawal` |
| `event` | `id`, `event_uuid`, `client_event_id`, `session_id`, `participant_id`, `level_id`, `challenge_node_id`, `challenge_item_id`, `occurred_at`, `server_received_at`, `clock_offset_ms`, `payload_json` | `field_session_code`, `level`, `node_ref`, `item_key`, `occurred_at_corrected`, `offset_known`, `payload` ([§7.5](#75-lembar-event-dan-kunci-jawaban)), `after_withdrawal` |
| `butir` | — | `answer_key` (K1); kolom 31 §13.3: `round`, `variant` (nama di 11 untuk `variant_group`), `booklet_shared` (1 untuk `x`), `dimension`, `subaspect`, `akm_level`, `akm_level_source`, `pirls_process`, `bloom_level_source`, `text_genre`, `testlet_key`, `d2_topic`, `d3_clue_type`, kolom beban permukaan (31 §13.2: `gap_count`, `distractor_word_count`, `passage_words`, `decoy_count`, `option_count`, `reason_option_count`, `puzzle_grid`, `tap_min_dp`), `review_status`, `reference_source` |

**`after_withdrawal`** (1/0, [35](35_IMPLEMENTASI_LAPIS_DATA_TURUNAN.md) §7) bukan kolom tabel: dihitung saat unduhan dari saat mundur (`withdrawn_at`) putaran turunan yang dipakai. Baris sesudah saat mundur tetap di lembar mentah dengan nilai 1 dan tidak masuk lembar turunan.

`attempt_id` tetap di semua lembar yang memuatnya, karena kamus memakainya sebagai kunci antartabel. Nama kolom yang sudah ada tidak diganti, walaupun kamus memakai nama lain (misalnya `hint_opened_before` untuk `hint_before`, `rating` untuk `feedback_rating`). Padanannya ditulis di lembar *Kamus Data* ([§8](#8-lembar-kamus-data-dan-dictionary_version)).

### 7.4 Waktu di unduhan

- Semua waktu event di unduhan peneliti adalah **waktu terkoreksi** (K8): `occurred_at_corrected = occurred_at + clock_offset_ms`, sesuai 11 → *Kunci penggabungan dan waktu*.
- Bila `clock_offset_ms` kosong (perangkat tidak mengirim jamnya), `occurred_at_corrected = occurred_at` dan `offset_known = 0`. Event yang ditulis server punya selisih 0 dan `offset_known = 1`.
- Waktu percobaan (`item_tries.occurred_at`) dan waktu di tabel jawaban adalah waktu server, tanpa koreksi.
- Format tetap ISO 8601 WIB dengan milidetik (`ExportLabels::UNIT_TIME`).

### 7.5 Lembar `event` dan kunci jawaban

**Lembar `event` (K1).** Sekarang lembar ini hanya untuk admin (`RawDataService::ADMIN_ONLY_DIMENSIONS`, baris 67; `ExportService::ADMIN_ONLY_SHEETS`, baris 33, membacanya). Sesudah bagian 1, admin tidak melihat data penelitian, jadi lembar ini pindah ke `ketua`:
- hanya jenis bertanda Peneliti di katalog ([§4](#4-katalog-event));
- id internal diganti kode: `session_code`, `participant_code`, `field_session_code`, `level` (kode wilayah), `node_ref`, `item_key`; `attempt_id` tetap;
- `event_uuid` dan `client_event_id` tidak ikut, karena hanya untuk mencegah event ganda;
- `payload` tetap utuh. Payload event peneliti tidak memuat data perangkat atau identitas (dicek di [§16](#16-pengujian-dan-kriteria-selesai));
- baris yang sudah dihapus lewat permintaan hapus data (`deleted_at` terisi) tidak pernah ikut, seperti sekarang.

`ADMIN_ONLY_DIMENSIONS` dan `ADMIN_ONLY_SHEETS` dihapus. Admin tidak punya dimensi data mentah lagi; log teknisnya di [§5.6](#56-data-bertanda-admin-dan-halaman-log-teknis).

**Kunci jawaban (K1).** `answer_key` ada di lembar `butir` dan `jawaban` untuk `ketua`, juga di layar `/admin/data`. Untuk Skenario Digital, kunci jawaban memuat skor dan kode setiap pilihan tindakan dan kode setiap alasan ([32](32_IMPLEMENTASI_SKENARIO_DIGITAL.md) §10); kode bank per pilihan ada di lembar `kode_pilihan` ([31](31_IMPLEMENTASI_BANK_SOAL_BARU.md) §13.3). `RawDataService::ADMIN_ONLY_COLUMNS` (baris 76) dihapus. Kunci tetap hanya dibaca server dan tidak pernah dikirim ke peramban siswa (aturan kerja repo).

### 7.6 Butir risiko daring

Penilaian dampak menetapkan bahwa peneliti hanya melihat angka gabungan butir risiko daring ([26 → P8](26_PENILAIAN_DAMPAK_PELINDUNGAN_DATA.md), A7, R3). Ini menjawab [27 → Pertanyaan terbuka](27_IMPLEMENTASI_PERAN_PENELITI.md#16-pertanyaan-terbuka) no. 5 (K9):
- Jawaban ang-21–ang-23 **tidak pernah** masuk lembar per siswa, lembar `event`, atau layar data mentah.
- Unduhan memuat lembar **`risiko_gabungan`**: satu baris per butir dan pilihan jawaban, dengan banyak siswa untuk seluruh studi, lalu per kode sekolah. Sel dengan kurang dari 5 siswa ditulis `<5`, mengikuti batas laporan di 26 → P9. *Tidak mau menjawab* dan *tidak diberi butir* (tanpa izin) dihitung sebagai pilihan sendiri.
- Penanda tindak lanjut hanya di bagian 4, untuk `ketua`, dan tidak diekspor.
- Isi lembarnya baru ada setelah modul no. 22 menyimpan jawaban. Bagian ini hanya menetapkan bentuknya. Sumbernya sesi modul jenis `risk` (`instrument_sittings.kind = 'risk'`, tanpa pratinjau): `risk_offered = 0` dihitung *tidak diberi butir*, `risk_reconsent = 0` *tidak mau mengisi*, kode `7` *tidak mau menjawab* ([34 → Data](34_IMPLEMENTASI_MODUL_TES_DAN_SKALA_SIKAP.md#83-data)).
- Aturan ini hanya untuk unduhan peneliti. Salinan data satu anak untuk orang tua adalah jalur lain di bagian 4: jawaban butir risiko diberikan bila orang tua tetap meminta setelah anak ditanya, kecuali membahayakan anak ([30 → Salinan data satu anak](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md#8-salinan-data-satu-anak); putaran kedua 30–31 Oktober 2026 di 04).

---

## 8. Lembar *Kamus Data* dan `dictionary_version`

Sekarang lembar `kamus` dibangun dari `ExportLabels::dictionary()` (baris 427–465): judul, arti, satuan, dan nilai per kolom. Lembar itu belum menyebut kamus penelitian, analitik, atau versinya.

**Kelas baru `App\Libraries\ResearchDictionary`** (K10):

```php
// Structured copy of the research data dictionary (docs/revisi-10-analitik/11_KAMUS_DATA.md).
public const VERSION = 'kamus-1.0';

// sheet => column => entry. Meaning and labels stay in ExportLabels (one place for wording).
private const ENTRIES = [
    'percobaan' => [
        'ready_ms' => ['kamus' => 'ready_ms', 'analytics' => [5, 6], 'ref' => 'analitik-6-pola-kesalahan', 'status' => 'baru'],
        'hint_opened_before' => ['kamus' => 'hint_before', 'analytics' => [7], 'ref' => 'analitik-7-penggunaan-bantuan', 'status' => 'ada'],
        // …
    ],
];
```

- **Satu tempat untuk kata-kata.** Judul, arti, satuan, dan label nilai tetap di `ExportLabels`. `ResearchDictionary` menambah nama di kamus, nomor analitik, jangkar bagian di 11, dan status `ada` atau `baru`.
- **Lembar `kamus`** mendapat kolom `nama_kamus`, `analitik`, dan `rujukan_kamus` (jangkar bagian di 11) di samping enam kolom sekarang.
- **Baris kepala berkas** di awal lembar `kamus` (dan di `kamus.csv`): versi kamus, tanggal dan jam unduhan WIB, kode studi, ringkasan filter, dan kalimat "Definisi lengkap: 11_KAMUS_DATA.md, versi yang sama".
- **`data_exports.dictionary_version`** (kolom baru) menyimpan versi yang dipakai setiap unduhan.
- **Tes** ([§16](#16-pengujian-dan-kriteria-selesai)): setiap kolom setiap lembar peneliti punya entri di `ResearchDictionary` dan di `ExportLabels`; tidak ada entri untuk kolom yang tidak diunduh; jangkar `ref` ada di 11.
- **Berubah ke `kamus-1.1`.** Setelah uji kelompok kecil dan menengah (KG3a, KG3b), `VERSION` dan entri diubah di cabang yang sama dengan perubahan 11, dengan entri di 04. Lapis turunan (no. 9, [35](35_IMPLEMENTASI_LAPIS_DATA_TURUNAN.md)) menyimpan `dictionary_version` di setiap tabelnya dan membaca konstanta yang sama.
- **Versi antara dan lapis turunan ([35](35_IMPLEMENTASI_LAPIS_DATA_TURUNAN.md) §8.1, §10.2).** Setelah bagian 9 digabung dan sebelum uji coba, `VERSION` = `kamus-1.1-usulan`: entri baru berstatus `usulan`, dan baris kepala menulis "usulan, belum disetujui". Baris kepala juga membawa `derived_run_id`, jam putaran, `dictionary_version` putaran, `idle_threshold_ms`, dan `rapid_guess_ms`. Kolom baru dari 31, 32, dan 34 masuk `ResearchDictionary` dengan status `baru` untuk `kamus-1.1`.
- **Dikunci di G4 (K17).** Versi kamus dikunci bersama versi game di G4. Selama pengambilan data utama (KG5) `VERSION` dan entri tidak berubah; temuan sesudahnya masuk laporan. Studi KG5 dan setiap unduhannya mencatat versi yang dipakai (`data_exports.dictionary_version`).

---

## 9. Riwayat unduhan

**Yang dicatat sekarang.** Setiap unduhan sudah punya baris `data_exports` (pemohon, format, `scope_json` dengan lembar dan filter, status, jumlah baris, SHA-256, kedaluwarsa) dan baris audit `export` saat dibuat dan `export_download` saat diunduh (`ExportController.php:95, 157`).

**Tambahan:**
- `data_exports.dictionary_version` ([§8](#8-lembar-kamus-data-dan-dictionary_version)).
- `data_exports.participant_count`: banyak siswa berbeda di unduhan itu, dihitung dari lembar `peserta` dengan filter yang sama. Bagian 4 memakainya untuk mencari unduhan yang mungkin memuat data seorang anak saat permintaan hapus.

**Halaman unduh data (`/admin/ekspor`) untuk `ketua`.** Bagian *Riwayat unduhan Anda* menampilkan unduhan milik sendiri saja (27 §5.6): tanggal, format, lembar, filter, jumlah baris, jumlah siswa, versi kamus, 12 karakter awal SHA-256, berapa kali diunduh (dari audit `export_download`), dan status. Berkas yang sudah lewat 7 hari tertulis "Berkas sudah dihapus otomatis".

**Admin** melihat semua unduhan di log audit (`/admin/tata-kelola/audit`, filter aksi `export`, `export_download`, `export_purge`), tanpa isi berkasnya.

---

## 10. Templat dan impor lembar lapangan (perubahan no. 15)

Lembar tetap diisi di kertas selama sesi (02). Sistem menyediakan berkas ketik yang sudah terisi kode, lalu menerima kembali lembar yang sudah diketik (K4). Lembar yang diimpor masuk unduhan `ketua`, sehingga penggabungan di lapis 3 tidak bergantung pada berkas yang tersebar. Penanda `helped_content`, `deviation_code`, `shared_device`, dan lainnya tetap dihitung lapis turunan (no. 9) dengan aturan 11.

### 10.1 Satu sumber kolom: tabel bertanda di 02

02 sudah menandai setiap tabel lembar dengan komentar `<!-- lembar: … -->` (11 penanda, dari `L1-kondisi` sampai `L8-profil-sekolah`). Situs statis revisi memakai tabel yang sama.

**Kelas baru `App\Libraries\FieldSheetSpec`** (K11):
- Memuat, per lembar: kode lembar, judul, kolom (kode Inggris, judul persis seperti 02, jenis isian, daftar kode yang boleh), kunci baris, dan apakah lembar diimpor.
- **Versi spesifikasi** `lembar-1.0`, dari 02 yang disetujui 26 Oktober 2026. Setiap perubahan tabel 02 menaikkan versi. Versinya dikunci di G4 bersama kamus (K17): perubahan dari KG3a dan KG3b masuk sebelum G4, selama KG5 tidak ada.
- **Situs statis** (02) memakai `kolom.json` dari spesifikasi yang sama. Bila situs dibuat di luar repo ini, pengembang mencocokkannya sekali dengan spesifikasi sebelum KG3a ([§19](#19-keputusan-pengembang) no. 6).
- **Tes `FieldSheetSpecTest`** membaca `docs/revisi-10-analitik/02_LEMBAR_LAPANGAN.md`, mengambil baris judul tabel di bawah setiap penanda, dan membandingkannya dengan judul kolom di spesifikasi. Bila 02 berubah tanpa spesifikasi (atau sebaliknya), tes gagal.
- `php spark gelita:lembar:templat --kolom=…` menulis spesifikasi sebagai JSON, supaya situs statis bisa memakai kode kolom yang sama.

### 10.2 Lembar yang diimpor dan yang tidak

| Penanda di 02 | Lembar unduhan | Diimpor | Catatan |
| --- | --- | --- | --- |
| `L1-kondisi` | `l1_kondisi` | Ya | Satu baris per sesi lapangan; kolom token **tidak** diimpor (riwayat token sudah di sistem, 28) |
| `L1-penyimpangan` | `l1_penyimpangan` | Ya | |
| `L2-kursi` | `l2_kursi` | Ya | Buklet dan bentuk tes dari panel tetap yang berlaku; perbedaan diberi peringatan |
| `L3-bantuan` | `l3_bantuan` | Ya | |
| `L4-bromp` | `l4_bromp` | Ya | |
| `L4-pelatihan` | — | Tidak | Data pengamat, bukan data anak; tidak digabung dengan log |
| `L5-wawancara` | `l5_wawancara` | Ya, **tanpa kolom *Kutipan ucapan siswa*** | Kutipan tetap di folder transkrip terenkripsi (26 → A10) |
| `L6-sesi` | `l6_sesi` | Ya | |
| `L6-tes` | — | Tidak | Sumbernya modul tes (no. 22, [34](34_IMPLEMENTASI_MODUL_TES_DAN_SKALA_SIKAP.md)); templat mengisinya dari sistem bila modul sudah ada |
| `L7-izin` | — | **Tidak pernah** | [§10.6](#106-l7-di-luar-server) |
| `L8-profil-sekolah` | — | Tidak | Data sekolah, dipegang di luar sistem bersama pemetaan NPSN |

### 10.3 Templat terisi per sesi lapangan

**Di halaman sesi lapangan** (`/admin/sesi-lapangan/{id}`, 28), tombol **Unduh templat lembar** memberi satu berkas XLSX, `lembar-{kode sesi}.xlsx`:

| Pertemuan | Lembar di berkas |
| --- | --- |
| P1 | L1 kondisi, L1 penyimpangan, L2, L3, L6 sesi, L6 tes (bila modul no. 22 sudah ada) |
| P2–P4 | L1 kondisi, L1 penyimpangan, L2, L3, L4, tiga lembar L5 |
| P5 | L1 kondisi, L1 penyimpangan, L2, L3, L4, L6 sesi, L6 tes (bila ada) |
| Susulan | L1 kondisi, L1 penyimpangan, L2, L3, dan L6 sesi bila susulan tes |

**Yang sudah terisi:**
- L1 kondisi: kode sesi lapangan, isi pertemuan (dari rencana pertemuan, 28 §5.2), tanggal, jam buka.
- L2: satu baris per siswa rombel itu (sesi susulan: anggotanya), urut kode peserta, dengan kode peserta dan buklet. Di P1 juga baris, kursi, dan bentuk tes awal dari panel. Di P2–P5 baris dan kursi dikosongkan, karena siswa bisa duduk di tempat lain.
- L3, L4: kode sesi; daftar kode peserta di lembar `Kode` untuk pilihan.
- Setiap lembar: kepala halaman seperti 02 (kode sesi, tanggal, inisial pengisi kosong).

**Lembar `Kode`** berisi daftar kode yang boleh (DEV-*, TMN/GRU/PNL/LN, ISI/STR/BHS/TKN/SMG/GG-*, ON/ONK/OFF/TBK, FOK/BNG/FRS/BSN/SNG/NTR, kode peserta rombel). Kolom berkode memakai validasi daftar dari lembar ini (PhpSpreadsheet sudah ada di `composer.json`). Berkas ini juga bisa dicetak sebagai lembar kosong.

**Tanpa nama.** Berkas hanya memuat kode. Tes memastikan nama, nama pengguna, nama sekolah, dan NPSN tidak ada di berkas.

**Siapa:** `peneliti` dan `ketua` (`field_sessions.manage`). Unduhan templat dicatat di audit sebagai `field_sheet_template` (tanpa salinan berkas di server; berkas dibuat langsung saat diminta).

### 10.4 Mengunggah lembar yang sudah diketik

**Alur**, mengikuti pola impor bank soal (`ContentImportService`: pratinjau, lalu simpan):
1. Di halaman sesi lapangan, P1 (atau peneliti lain) memilih **Unggah lembar yang sudah diketik**, lalu berkas XLSX (templat tadi) atau CSV per lembar (dari situs statis). Judul kolom boleh kode kolom atau judul 02.
2. **Pratinjau:** sistem membaca setiap lembar yang dikenal, memeriksa setiap baris, dan menampilkan ringkasan per lembar (banyak baris, baris bermasalah dengan nomor baris dan kolom, peringatan).
3. Bila tidak ada kesalahan, pengunggah menekan **Simpan**. Peringatan tidak menahan, tetapi peringatan nama (K13, di bawah) harus dikonfirmasi dulu.

**Pemeriksaan (kesalahan menahan simpan):**
- kode sesi di berkas sama dengan sesi halaman itu;
- jam berbentuk `JJ:MM:SS` (WIB), jam selesai tidak lebih awal dari jam mulai;
- kode peserta milik rombel sesi itu (susulan: anggotanya);
- kode (DEV-*, pemberi, jenis, perilaku, emosi) ada di daftar 02;
- nomor kursi di L1, L3, L4 ada di L2 sesi itu, atau "semua", atau rentang (`05–08`);
- isian "—" dibaca sebagai kosong, sesuai aturan menulis di 02;
- L5: `manual_code` (bila diisi) harus salah satu kode kesalahan butir itu di `item_error_codes` ([31 §10](31_IMPLEMENTASI_BANK_SOAL_BARU.md#10-kode-kesalahan-di-setiap-percobaan-salah-perubahan-no-1)), dan percobaan yang ditunjuk (`item_key`, `try_no` di pengerjaan pertama siswa itu) harus ada dan salah.

**Peringatan (tidak menahan):**
- buklet atau bentuk tes di L2 berbeda dari panel (panel yang berlaku);
- siswa rombel yang tidak ada di L2;
- lembar diunggah lebih dari 24 jam setelah sesi tutup (02);
- **kata yang sama dengan nama siswa rombel itu** di isian teks bebas (*Kejadian*, *Tindakan*, *Uraian singkat*, *Catatan*, pertanyaan siswa di L6). Server membandingkan kata isian dengan kata dari `display_name` dan `username` siswa rombel itu (tanpa huruf besar-kecil, kata utuh, minimal 3 huruf). Pesan hanya menyebut nomor baris dan kolom, tidak menyebut namanya. Banyak nama juga kata biasa (Bintang, Cahaya), jadi ini peringatan, bukan penolakan. Nama di luar rombel itu (guru, siswa kelas lain, panggilan) tidak diperiksa; itu dijaga aturan menulis di 02 dan pelatihan tim, tanpa daftar kata tambahan ([§19](#19-keputusan-pengembang) no. 7). Pengunggah memeriksa, mengganti nama dengan kode peserta bila perlu, lalu mencentang *Sudah saya periksa, tidak ada nama siswa* (K13).

**Penyimpanan:**
- Satu baris `field_sheet_imports` per lembar per unggahan, dan barisnya di `field_sheet_rows` ([§12](#12-skema-database)).
- Kunci gabung disimpan sebagai kolom: `participant_id` (dari kode peserta), `seat_no`, `started_at`, `ended_at` (tanggal sesi + jam). Isian lain di `values_json` dengan kode kolom dari spesifikasi.
- **Unggah ulang menggantikan.** Lembar yang sama untuk sesi yang sama diganti seluruhnya: impor lama diberi `superseded_at` lewat UPDATE bersyarat (`WHERE superseded_at IS NULL`), sehingga dua unggahan bersamaan tidak sama-sama berlaku. Impor lama tetap ada sebagai riwayat dan tidak ikut unduhan.
- **L5 → `item_tries.manual_code`.** Untuk setiap baris L5 dengan `manual_code` terisi, impor utama menulis kodenya ke `item_tries.manual_code` percobaan yang ditunjuk, di transaksi yang sama; kode bank di `error_code` tidak berubah (11 → Analitik 6). Unggah ulang yang menggantikan mengosongkan dulu `manual_code` yang ditulis impor lama, lalu menulis yang baru. Impor cek (§10.5) tidak menulis apa pun ke `item_tries`.
- Audit `field_sheet_import` dengan kode sesi, lembar, banyak baris, dan banyak peringatan.

**Halaman sesi lapangan** menampilkan status per lembar yang diharapkan pertemuan itu: *belum diunggah*, *diunggah* (tanggal, pengunggah), *dicek* (persentase baris ketikan kedua). Setelah 24 jam, lembar yang belum diunggah ditandai.

**Siapa melihat isinya.** Pengunggah melihat pratinjau dan ringkasan. Isi lembar yang tersimpan hanya ada di unduhan `ketua`. Admin tidak melihatnya.

### 10.5 Pengecekan ketikan kedua

02 meminta 10% baris diketik ulang orang lain.
- Di halaman unggah ada pilihan **Ketikan kedua (cek)**. Berkasnya boleh berisi sebagian baris.
- Server mencocokkan baris dengan impor utama lewat kunci baris spesifikasi (misalnya L2: kode peserta; L3: jam mulai dan kursi; L4: putaran dan kursi; L1 penyimpangan: jam mulai; L5: kode peserta, butir, dan `try_no`).
- Hasilnya: banyak baris dicek dari seluruh baris, dan daftar sel yang berbeda (baris, kolom, ketikan pertama, ketikan kedua) untuk diperbaiki di impor utama.
- Impor cek disimpan dengan `purpose = check` dan tidak pernah masuk unduhan.

### 10.6 L7 di luar server

- Templat L7 **tidak ada** di panel dan tidak pernah dibuat oleh server web.
- `php spark gelita:lembar:templat --l7 --keluar=<folder>` menulis templat L7 kosong di mesin yang menjalankan perintah (laptop P1 atau pengembang), bersama templat kosong L0–L8 untuk dicetak. Perintah menolak berjalan bila `ENVIRONMENT === 'production'`.
- L7 yang terisi disimpan seperti di 02: kertas di lemari terkunci, salinan digital terenkripsi, hanya P1 dan ketua peneliti.
- `consent_ok` dihitung lapis turunan di server dari `participant_consents` ([35 §7](35_IMPLEMENTASI_LAPIS_DATA_TURUNAN.md#7-consent_ok-dan-data-sampai-tanggal-mundur)), bukan dari lembar unduhan. Lembar `izin` di unduhan utama ([§7.2](#72-lembar); 30 keputusan no. 9) membawa kolom sumbernya (izin berkode yang diisi P1 dari L7), supaya `ketua` bisa mencocokkannya dengan L7 sampai studi ditutup dan lapis 3 bisa memeriksa `consent_ok`. Daftar izin berkode untuk penutupan studi tetap unduhan terpisah di bagian 4 ([30 §10.1](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md#101-daftar-izin-berkode)).

### 10.7 Kode kolom per lembar

Judul kolom persis seperti 02; kode di bawah dipakai di `values_json`, CSV, dan unduhan.

| Lembar | Kode kolom (urut seperti 02) |
| --- | --- |
| `l1_kondisi` | `field_session_code`, `meeting_content`, `p5_date`, `session_date`, `start_time`, `end_time`, `researchers`, `present_count`, `consented_count`, `playing_count`, `room_devices`, `internet`, `headset_count`, `headset_of`, `noise_level`, `teacher_presence`, `minutes_available`, `minutes_used`, `breaks` |
| `l1_penyimpangan` | `start_time`, `end_time`, `deviation_code`, `description`, `affected_seats`, `action` |
| `l2_kursi` | `seat_no`, `row_no`, `seat_in_row`, `device_no`, `participant_code`, `booklet`, `pre_form`, `entry_time`, `shared_device`, `shared_with`, `switch_time`, `switch_device`, `note` |
| `l3_bantuan` | `start_time`, `end_time`, `seats`, `participant_code`, `screen`, `helper`, `help_type`, `asked`, `description` |
| `l4_bromp` | `round_no`, `obs_time`, `seat_no`, `participant_code`, `screen`, `behavior`, `emotion`, `note` |
| `l5_wawancara` | Kepala: `participant_code`, `field_session_code`, `pretest_group`, `interviewer`, `start_time`, `end_time`, `recorded`. Baris: `item_key`, `try_no`, `first_answer`, `game_code`, `coder_1`, `coder_2`, `agree`, `manual_code` |
| `l6_sesi` | `field_session_code`, `test`, `part1_start`, `part23_start`, `last_finish`, `spare_devices`, `frequent_questions` |

**`try_no` dan `manual_code` L5** adalah kolom templat dan impor ([§19](#19-keputusan-pengembang) no. 14): satu baris per percobaan butir yang dikode manual (`try_no` kosong dibaca 1), dan `manual_code` adalah kode hasil pengodean manual (`*-LAIN`, transkrip). Di `FieldSheetSpec` keduanya ditandai kolom tambahan, sehingga `FieldSheetSpecTest` tidak mencarinya di tabel 02 sampai tabel L5 di 02 diperbarui.

Isian gabungan di 02 dipecah saat impor: *Headset* "24 dari 26" → `headset_count`, `headset_of`; *Pakai bareng* "Ya, S03-A16" → `shared_device` 1 dan `shared_with`; *Pindah perangkat* "08:40:00 ke P15 (baterai habis)" → `switch_time`, `switch_device`, sisanya ke `note`; *Menit tersedia dan dipakai* "90 / 84" → dua kolom. Di templat, isian itu sudah dua kolom; di kertas tetap seperti 02.

---

## 11. Hak akses dan menu

Semua kemampuan sudah ada di peta [27 §5.1](27_IMPLEMENTASI_PERAN_PENELITI.md#51-peta-kemampuan); bagian ini tidak menambah kemampuan baru.

| Halaman atau tindakan | Admin | Peneliti | Ketua | Kemampuan |
| --- | --- | --- | --- | --- |
| Log teknis dan CSV-nya | Ya | — | — | `tech_logs.view` |
| Unduh data, lembar *Kamus Data*, riwayat unduhan sendiri | — | — | Ya | `research.export` |
| Data mentah di layar, termasuk `event` dan `answer_key` | — | — | Ya | `research.browse` |
| Unduh templat lembar per sesi | — | Ya | Ya | `field_sessions.manage` |
| Unggah lembar, ketikan kedua, status lembar | — | Ya | Ya | `field_sessions.manage` |
| Riwayat semua unduhan (log audit) | Ya | — | — | `audit.view` |

**Menu.** "Log teknis" di kelompok Data untuk admin, di slot yang sudah dipesan di [27 → Panel peneliti](27_IMPLEMENTASI_PERAN_PENELITI.md#8-panel-peneliti). Templat dan unggah lembar ada di halaman sesi lapangan, tanpa menu sendiri.

---

## 12. Skema database

**Migrasi `2026-01-01-003600_CleanResearchLogs`** ([§5.8](#58-data-yang-sudah-tersimpan)) dan **`2026-01-01-003700_CreateFieldSheets`**. Nomor sesudah bagian 2 (`003500`); bagian 4 memakai `003800`. Urutan akhir migrasi bagian 1–4 ada di [27 → Migrasi](27_IMPLEMENTASI_PERAN_PENELITI.md#61-migrasi-2026-01-01-003400_replaceteacherwithresearcherroles).

| Tabel atau kolom | Isi | Migrasi |
| --- | --- | --- |
| `game_event_logs` | Baris tiga jenis dihapus; `answer_changed` diganti; `dialogue_completed` dibuat | `003600` |
| `device_logs` | Buang `user_agent`, `client_hints_json`, `device_brand`, `device_model`, `pixel_ratio`, `touch_points`, `timezone`, `language`, `os_platform` | `003600` |
| `item_tries` | Kolom baru `ready_ms` INT UNSIGNED (null) | `003600` |
| `data_exports` | Kolom baru `dictionary_version` VARCHAR(20) (null), `participant_count` INT UNSIGNED (null) | `003600` |
| `field_sheet_imports` (baru) | `id`, `field_session_id` FK, `sheet` VARCHAR(30), `spec_version` VARCHAR(20), `purpose` VARCHAR(10) (`main`, `check`), `file_sha256` CHAR(64), `row_count`, `warning_count`, `names_confirmed` TINYINT(1), `uploaded_by` FK staf, `created_at`, `superseded_at` (null), `superseded_by` FK diri sendiri (null). Indeks (`field_session_id`, `sheet`, `purpose`, `superseded_at`) | `003700` |
| `field_sheet_rows` (baru) | `id`, `import_id` FK (`ON DELETE CASCADE`), `row_no`, `participant_id` FK (null, `ON DELETE CASCADE`), `seat_no` TINYINT (null), `started_at` DATETIME(3) (null), `ended_at` DATETIME(3) (null), `values_json`. Indeks (`import_id`, `row_no`), (`participant_id`, `started_at`) | `003700` |

Semua kunci asing ke `staff_users` memakai `ON DELETE SET NULL`. Kunci asing `field_sheet_rows.participant_id` memakai `ON DELETE CASCADE`, sesuai aturan hapus bagian 4 ([30 → Yang dihapus sistem](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md#73-yang-dihapus-sistem)); `RetentionService::HARD_ORDER` tetap menghapus barisnya lebih dulu, supaya jumlahnya masuk pratinjau. Bagian 4 juga menambah `data_exports.purged_by_request_id` di migrasi `003800` ([30 → Skema database](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md#15-skema-database)). Berkas yang diunggah tidak disimpan; hanya SHA-256-nya.

**Ikut diperbarui:**
- `tests/_support/Database/ResetTables.php` (skema SQLite).
- `SystemResetService`: `REQUIRED_MIGRATION` menjadi `003700` (nilai akhir setelah bagian 4: `003800`); tabel baru di daftar yang dikosongkan.
- `RetentionService`: `HARD_ORDER` mendapat `field_sheet_rows` (per `participant_id`, cakupan hapus satu siswa, [30 §7.3](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md#73-yang-dihapus-sistem)); `field_sheet_imports` di pembersihan studi.
- Model baru: `FieldSheetImportModel`, `FieldSheetRowModel`. `ItemTryModel`, `DataExportModel`, `DeviceLogModel`: `allowedFields`.

**Kolom yang dibaca unduhan tetapi dibuat bagian lain:** `item_tries.error_code`, `manual_code`, `challenge_attempts.variant_source`, `booklet_at_open`, `item_error_codes` (31 §15); kolom skenario di `item_tries` (32 §11); `audio_usage_events.clock_offset_ms` dan `challenge_attempt_segments` (33 §9, K10); tabel modul `instrument_*` (34 §11); tabel `derived_*` dan `data_exports.derived_run_id` (35 §12).

**Layanan dan kelas baru:** `EventCatalog`, `ResearchDictionary`, `FieldSheetSpec` (di `app/Libraries/`); `TechLogService`, `FieldSheetService` (templat, pratinjau, simpan, cek) di `app/Services/`, didaftarkan di `app/Config/Services.php`.

---

## 13. Rute dan perintah spark

**Panel** (grup `staffAuth`, kemampuan dari 27):

| Rute | Controller::method | Kemampuan |
| --- | --- | --- |
| `GET /admin/log-teknis` | `TechLogController::index` | `tech_logs.view` |
| `GET /admin/log-teknis/(perangkat\|event\|sesi)` | `::table` | `tech_logs.view` |
| `GET /admin/log-teknis/(perangkat\|event\|sesi)/csv` | `::csv` | `tech_logs.view` |
| `GET /admin/sesi-lapangan/{id}/lembar` | `FieldSheetController::template` | `field_sessions.manage` |
| `GET`, `POST /admin/sesi-lapangan/{id}/lembar/unggah` | `::uploadForm`, `::preview` | `field_sessions.manage` |
| `POST /admin/sesi-lapangan/{id}/lembar/simpan` | `::save` | `field_sessions.manage` |
| `/admin/ekspor…` | Tetap (27) | `research.export` |
| `/admin/data…` | Tetap (27) | `research.browse` |

Pratinjau disimpan di sesi staf (berkas tidak disimpan) sampai *Simpan* atau 30 menit. `save` menolak bila pratinjau sudah tidak ada atau milik sesi lapangan lain.

**Game dan API.** Tidak ada rute baru. `/api/events` menerima `dialogue_completed`, `passage_reopened`, dan `empty_tap` lewat katalog. `/api/attempts/{id}/check` dan `/responses` menerima `ready_ms`.

**Perintah spark:**

```text
php spark gelita:log:hitung                       # count rows the cleanup migration will change (read only)
php spark gelita:lembar:templat --keluar=lembar/  # blank L0–L8 templates, including L7, for printing
php spark gelita:lembar:templat --l7 --keluar=…   # blank L7 only
php spark gelita:lembar:templat --kolom=kolom.json  # column spec as JSON for the static site
```

`gelita:lembar:templat` menolak berjalan di produksi. Kedua perintah masuk daftar perintah spark di aturan kerja repo.

---

## 14. Teks panel

Semua di `app/Language/id/Admin.php` (panel hanya berbahasa Indonesia). Game tidak mendapat teks baru. Mengikuti `.claude/rules/ui-copy.md`.

| Kunci | Usulan |
| --- | --- |
| `menuTechLog` | Log teknis |
| `techLogIntro` | Catatan teknis untuk memeriksa masalah perangkat dan aplikasi. Jawaban dan data penelitian tidak ada di sini. |
| `techLogTabDevices`, `techLogTabEvents`, `techLogTabSessions` | Perangkat · Event teknis · Sesi |
| `exportHistoryMine` | Riwayat unduhan Anda |
| `exportDictionaryVersion` | Versi kamus data |
| `exportParticipantCount` | Jumlah siswa |
| `exportFileGone` | Berkas sudah dihapus otomatis. Buat unduhan baru bila masih diperlukan. |
| `exportNeedsStudy` | Belum ada studi. Buat studi dulu di halaman Studi, lalu kembali ke sini untuk mengunduh. |
| `fieldSheetTemplate` | Unduh templat lembar |
| `fieldSheetUpload` | Unggah lembar yang sudah diketik |
| `fieldSheetUploadHelp` | Pilih berkas templat yang sudah diisi, atau berkas CSV dari situs lembar lapangan. L7 tidak pernah diunggah. |
| `fieldSheetCheckUpload` | Ketikan kedua (cek) |
| `fieldSheetPreviewTitle` | Periksa sebelum disimpan |
| `fieldSheetRowError` | Baris {0}, kolom {1}: {2} |
| `fieldSheetWrongSession` | Berkas ini untuk sesi {0}, bukan sesi ini. Pilih berkas yang sesuai. |
| `fieldSheetCodeOtherClass` | Kode peserta {0} bukan dari rombel sesi ini. Periksa lagi L2. |
| `fieldSheetNameWarn` | Baris {0}, kolom {1} memuat kata yang sama dengan nama siswa di rombel ini. Periksa lagi; kalau itu nama, ganti dengan kode peserta. |
| `fieldSheetNameConfirm` | Sudah saya periksa, tidak ada nama siswa. |
| `fieldSheetReplaced` | Lembar {0} sesi ini sudah diganti dengan unggahan baru. |
| `fieldSheetCheckResult` | {0} dari {1} baris sudah dicek; {2} isian berbeda dari ketikan pertama. |
| `fieldSheetLate` | Lembar ini diunggah lebih dari 24 jam setelah sesi ditutup. |
| `fieldSheetStatusMissing`, `fieldSheetStatusUploaded`, `fieldSheetStatusChecked` | Belum diunggah · Diunggah · Dicek |

Pesan pemeriksaan per kolom ("jam harus berbentuk JJ:MM:SS", "kode tidak dikenal") ditulis dari daftar kunci yang sama, dengan contoh isian yang benar.

---

## 15. Titik sambung ke perubahan lain

| Perubahan | Yang disediakan bagian ini | Yang dirancang di sana |
| --- | --- | --- |
| No. 1, 3, 4, 8, 20 bank baru (bagian 5, [31](31_IMPLEMENTASI_BANK_SOAL_BARU.md)) | Lembar `percobaan`, `pengerjaan`, `butir`, dan `kode_pilihan`; kamus berversi; impor L5 sebagai sumber `manual_code` | Kolom `item_tries.error_code` dan `manual_code`, `challenge_attempts.variant_source` dan `booklet_at_open`, label dan beban permukaan di `challenge_items`, tabel `item_error_codes` (31 §10, §11.4, §13, §15) |
| No. 1 kode kesalahan ([31](31_IMPLEMENTASI_BANK_SOAL_BARU.md)) | Lembar `percobaan` dan `jawaban`, kamus berversi | Dirancang di 31: `item_tries.error_code`, `manual_code` (31 §10, §15); kolom lembar `butir` (31 §13.3); lembar `kode_pilihan`; `variant_source`, `booklet_at_open` di lembar `pengerjaan` (31 §11.4) |
| No. 2 Skenario Digital ([32](32_IMPLEMENTASI_SKENARIO_DIGITAL.md)) | `ready_ms`, katalog event | Dirancang di 32: `reason_ready_ms` (32 §6.4), event `scenario_failed` (32 §5.6), kolom lembar `percobaan` dan `bantuan` (32 §10) |
| No. 5 waktu aktif ([33](33_IMPLEMENTASI_MODE_PENELITIAN.md)) | Baris `pause` dan lama mendengar yang lengkap ([§5.4](#54-audio_pause-dan-lama-mendengar)), `page_hidden`/`page_visible`, waktu terkoreksi | Dirancang di 33: event `input_idle` (33 §5.2), `audio_usage_events.clock_offset_ms` (33 §5.3, K10), potongan pengerjaan `challenge_attempt_segments` (33 §5.4) |
| No. 6 narasi instruksi ([32](32_IMPLEMENTASI_SKENARIO_DIGITAL.md) §8) | Katalog event, lembar `bantuan` per pemutaran | Kode konteks `challenge_instruction` (32 §8); kolom `audio_context` lembar `bantuan` (32 §10) |
| No. 9 lapis turunan ([35](35_IMPLEMENTASI_LAPIS_DATA_TURUNAN.md)) | Data mentah §6, lembar lapangan yang diimpor, `ResearchDictionary::VERSION` | `decision_ms`, `rapid_guess`, `near_miss_count`, `material_returns`, penanda dari L1–L4, `outside_session`; delapan lembar `turunan_*` (35 §10.1, termasuk `turunan_tes`); baris kepala *Kamus Data* dan `kamus-1.1-usulan` (35 §8, §10.2); `consent_ok` dan `after_withdrawal` (35 §7) |
| No. 21 skala sikap ([34](34_IMPLEMENTASI_MODUL_TES_DAN_SKALA_SIKAP.md)) | Daftar lembar dan kamus | Lembar `sikap`, `feedback_rating` (34 §11.4) |
| No. 22 modul tes ([34](34_IMPLEMENTASI_MODUL_TES_DAN_SKALA_SIKAP.md)) | Lembar `risiko_gabungan` ([§7.6](#76-butir-risiko-daring)), templat L6 tes | Lembar `tes_jawaban`, `tes_bagian`, `angket`, kolom `peserta` tambahan (34 §11.4); sumber `risiko_gabungan` = sesi jenis `risk` (34 §8.3); status L6 |
| Bagian 4 ([30](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md)) | `data_exports.participant_count`, riwayat unduhan, `field_sheet_rows.participant_id` (`ON DELETE CASCADE`) | Penghapusan termasuk ekspor dan lembar, `data_exports.purged_by_request_id`, ambil data satu anak, daftar izin berkode sebagai unduhan terpisah (`/admin/ekspor/izin`), penanda tindak lanjut; lembar `izin` di unduhan utama (30 keputusan no. 9, kodenya di bagian 4); pemeriksaan nama K13 dipakai ulang untuk kritik dan saran sebelum studi ditutup (30 §10.2, keputusan no. 10). Bagian 4 digabung sesudah bagian ini (keputusan no. 7) |

---

## 16. Pengujian dan kriteria selesai

### 16.1 Tes baru

| Tes | Yang diperiksa |
| --- | --- |
| `EventCatalogTest` | Setiap jenis punya tepat satu pemilik; `accepted()` = Peneliti + Admin; jenis yang dihapus ditolak `event_type_unknown`; `SERVER_ONLY` bagian dari yang diterima |
| `AnswerSubmittedMergeTest` | Ketukan Cari Objek yang berbeda dari kiriman sebelumnya menulis `answer_submitted {changed: true}`; tidak ada `answer_changed` |
| `DialogueCompletedTest` | Slide terakhir mengirim satu `dialogue_completed`; lencana "Belum didengar" hilang setelahnya; slide lain tidak mengirim apa pun |
| `ResearchLogCleanupMigrationTest` | Baris tiga jenis terhapus; `answer_changed` menjadi `answer_submitted` dengan `changed`; `dialogue_completed` dibuat sekali per sesi, konteks, wilayah; kolom perangkat hilang; satu baris audit; `down()` berjalan |
| `LogCountCommandTest` | Angka sama dengan yang diubah migrasi; tidak mengubah apa pun |
| `DeviceMinimisationTest` | `device_logs` baru tanpa kolom yang dibuang; koreksi iPad tetap; `Accept-CH` tanpa Model, Arch, Bitness |
| `AudioPlaybackSheetTest` | `pause` tidak menulis event mentah tetapi menulis baris audio; lembar `bantuan` satu baris per pemutaran dengan `listened_ms` terbesar |
| `MarkStaleEventTest` | Satu `session_paused {via: idle}` per sesi yang diubah, dengan waktu `last_active_at`; dua pemanggil tidak menulis ganda (pola `Factories::injectMock`) |
| `ReadyMsTest` | `/check` dan `/responses` menyimpan `ready_ms`; Isi Rumpang kosong; nilai di luar batas atau lebih besar dari `last_input_ms` disimpan kosong |
| `NewClientEventsTest` | `empty_tap` dan `passage_reopened` diterima dengan referensi dan payloadnya |
| `TechLogPageTest` | Admin melihat tiga tab dan CSV-nya dengan audit; peneliti dan ketua 404; tidak ada event penelitian atau jawaban |
| `ResearchExportColumnsTest` | Tidak ada kolom Admin atau dibuang di lembar peneliti; tanpa `perangkat`; `event` hanya jenis Peneliti tanpa id internal; `answer_key` ada untuk ketua; akun `uji.*` dan `sim_*` tidak ikut; unduhan tanpa `study_id` ditolak, juga saat mode penelitian mati (K16) |
| `ExportTimeCorrectionTest` | `occurred_at_corrected` = waktu + selisih; `offset_known` 0 bila selisih kosong |
| `DictionarySheetTest` | Setiap kolom peneliti punya entri `ResearchDictionary` dan `ExportLabels`; jangkar ada di 11; versi di lembar dan di `data_exports` |
| `ExportHistoryTest` | Ketua melihat unduhannya sendiri dengan versi kamus, jumlah siswa, dan jumlah unduh; tidak melihat unduhan staf lain |
| `RiskAggregateSheetTest` | Bentuk lembar `risiko_gabungan`; sel di bawah 5 menjadi `<5`; tidak ada jawaban per siswa di lembar mana pun (diaktifkan setelah no. 22) |
| `FieldSheetSpecTest` | Judul kolom spesifikasi sama dengan tabel bertanda di 02 untuk setiap lembar yang diimpor |
| `FieldSheetTemplateTest` | Berkas per jenis pertemuan berisi lembar yang benar, terisi kode dan buklet; tidak ada nama, nama pengguna, sekolah, NPSN, token, atau L7 |
| `FieldSheetImportTest` | Pratinjau lalu simpan; kesalahan menahan dengan nomor baris dan kolom; kode rombel lain ditolak; kutipan L5 dan token L1 diabaikan; unggah ulang menggantikan (dua unggahan bersamaan, satu yang berlaku); peringatan nama wajib dikonfirmasi; admin 404 |
| `FieldSheetCheckTest` | Ketikan kedua dicocokkan per kunci baris; selisih dilaporkan; impor cek tidak masuk unduhan |
| `FieldSheetExportTest` | Lembar `l1_kondisi` … `l6_sesi` di unduhan ketua, hanya impor utama yang berlaku |

### 16.2 Tes yang ditulis ulang

- Tes yang memakai jenis event yang dihapus sebagai contoh: `TelemetryBatchTest`, `EventOrderingTest`, `SimulationTest` (dan `Simulator`), `HeardRegionIntrosTest`, `RegionStoryTest`, `StartGateTest` (`grep -rn "answer_selected\|dialogue_advanced\|audio_pause\|answer_changed" tests/`).
- Tes unduhan dan data mentah: `RawExportTest`, `ExportRulesTest`, `AdminTableViewTest`, dan tes `ExportLabels`.
- Tes yang membaca kolom perangkat yang dibuang (`grep -rn "device_brand\|user_agent\|client_hints" tests/`).
- `RouteWiringTest` untuk rute baru.

### 16.3 Cek manual

Di mesin pengembangan, dengan salinan database berisi data simulasi (`gelita:simulasi`):
1. Jalankan `gelita:log:hitung`, catat angkanya, lalu migrasi. Pastikan lencana "Belum didengar" di peta wilayah sama dengan sebelum migrasi.
2. Mainkan satu tantangan Benar atau Salah dan satu Cari Objek (dengan ketukan di tempat kosong dan membuka bacaan lagi). Periksa `ready_ms`, `empty_tap`, dan `passage_reopened`.
3. Masuk sebagai admin: buka log teknis dan unduh CSV-nya.
4. Masuk sebagai ketua: buat unduhan XLSX dan CSV-ZIP; periksa lembar *Kamus Data*, waktu terkoreksi, dan riwayat unduhan.
5. Masuk sebagai peneliti: unduh templat sesi P2, isi beberapa baris (satu dengan kata nama siswa), unggah, lalu unggah ketikan kedua.

### 16.4 Selesai bila

- `vendor/bin/phpunit --no-coverage` hijau, termasuk tes di 16.1.
- Cek manual 16.3 lolos.
- `grep -rn "answer_selected\|dialogue_advanced\|'audio_pause'\|answer_changed" app/ public/assets/js/` hanya menemukan migrasi `003600` dan katalog (daftar yang dihapus).
- Dokumen di [§17](#17-dokumen-yang-ikut-diperbarui-saat-implementasi) diperbarui di cabang yang sama.

---

## 17. Dokumen yang ikut diperbarui saat implementasi

- `docs/07_FEATURE_INTEGRATION.md`: fitur telemetri, ekspor, data mentah, peta wilayah (lencana), narasi, perangkat; fitur baru log teknis dan lembar lapangan.
- `docs/01_DATABASE.md`, `docs/03_MODEL_ENTITY.md`, `docs/04_CONTROLLER_ROUTE.md`, `docs/05_VIEW_UI.md`, `docs/08_DEPLOYMENT.md` (urutan `gelita:log:hitung` lalu migrasi), `README.md`.
- `.claude/rules/glossary.md`: baris baru untuk log teknis (`tech log`), katalog event (`event catalog`), kamus data (`data dictionary`, `dictionary_version`), lembar lapangan (`field sheet`, `field sheet import`), ketikan kedua (`check entry`).
- Aturan kerja repo: daftar perintah spark (`gelita:log:hitung`, `gelita:lembar:templat`); bagian *Research events* (`dialogue_completed` menggantikan `dialogue_advanced`).
- `docs/PROGRESS.md` dan catatan keputusan implementasi.

---

## 18. Pertanyaan terbuka

Semua dijawab pengembang 2 November 2026 ([§19](#19-keputusan-pengembang) no. 6–10). Tidak ada pertanyaan terbuka yang tersisa.

1. ~~**Situs statis revisi.** 02 menyebut situs statis tempat lembar bisa diisi dan diunduh sebagai CSV. Situs itu tidak ada di repo ini. Usulan: situs memakai `kolom.json` dari `gelita:lembar:templat --kolom` supaya kode kolomnya sama. Bila situs dibuat terpisah dari repo, pengembang mencocokkannya sekali sebelum uji coba.~~ Dijawab: usulan dipakai, dicocokkan sebelum KG3a (§19 no. 6).
2. ~~**Isian teks bebas yang menyebut anak lain.** Pemeriksaan nama hanya membandingkan dengan siswa rombel itu. Nama guru, teman dari kelas lain, atau panggilan tidak terdeteksi. Cukup aturan menulis di 02 dan pelatihan tim, atau perlu daftar kata tambahan? Usulan: cukup aturan dan pelatihan.~~ Dijawab: cukup aturan dan pelatihan (§19 no. 7).
3. ~~**`is_touch`** dipindah ke admin sebagai tafsiran, karena 01 tidak menyebutnya; 11 hanya menyebut jenis perangkat dan ukuran layar. Bila peneliti membutuhkannya sebagai kovariat (misalnya laptop layar sentuh), dikembalikan ke lembar `sesi` lewat `kamus-1.1`.~~ Dijawab: tetap admin; dikembalikan lewat `kamus-1.1` bila peneliti memintanya (§19 no. 8).
4. ~~**`empty_tap` per petunjuk paling banyak 30.** Angka ini usulan; uji coba memeriksa apakah ada anak yang melampauinya.~~ Dijawab: tetap 30, dicek di KG3a dan KG3b (§19 no. 9).
5. ~~**Data lama di server produksi.** Migrasi `003600` menghapus data yang sudah tersimpan (K3). Bila server produksi masih memuat data dari sebelum studi yang ingin disimpan sebagai arsip, arsipnya dibuat pengembang sebelum migrasi, di luar sistem, dengan aturan simpan 26.~~ Dijawab: tanpa arsip (§19 no. 10).

---

## 19. Keputusan pengembang

Satu putaran tanya, empat pertanyaan (30 Oktober 2026). Pengembang memilih usulan di tiga pertanyaan pertama dan pilihan lain di pertanyaan keempat. `asal: pengembang`.

1. **`ketua` mendapat lembar `event` dan kolom `answer_key`** (K1). Lembar `event` hanya berisi event bertanda Peneliti dengan kode sebagai pengganti id internal; `answer_key` di lembar `butir` dan `jawaban`. Ini menjawab [27 → Data yang terlihat per peran](27_IMPLEMENTASI_PERAN_PENELITI.md#9-data-yang-terlihat-per-peran). Tidak dipilih: lembar `event` saja; tidak keduanya.
2. **`answer_changed` digabung ke `answer_submitted`** (K2), dengan `changed` di payload; baris lama diganti namanya. Tidak dipilih: dihapus seperti tertulis di 01, yang membuat urutan kejadian kehilangan sebagian kiriman Cari Objek.
3. **Data lama dihapus di migrasi** (K3), setelah `gelita:log:hitung`, dengan satu baris audit. Tidak dipilih: hanya berhenti menulis dan menunggu `gelita:reset` atau retensi.
4. **Templat terisi per sesi lapangan, ditambah impor ke sistem sekarang** (K4). Usulan AI adalah templat terisi tanpa impor. Pengembang memilih impor, sehingga penggabungan lembar dengan log bisa dikerjakan di server. Akibatnya:
   - lembar L1–L6 yang diketik kini juga tersimpan di server, berkode dan tanpa nama, dan masuk unduhan `ketua`;
   - 22 dan 26 perlu disesuaikan: lembar A8 tidak lagi hanya "kertas, lalu templat" ([§20](#20-akibat-ke-dokumen-lain));
   - pengaman tambahan K12 (kutipan L5 dan token L1 tidak diimpor) dan K13 (peringatan nama) diturunkan dari 02 dan 26.

   Tidak dipilih: satu templat kosong tanpa isian otomatis; templat terisi tanpa impor.

**Temuan kode untuk ditinjau pengembang** (bukan pertanyaan baru; dicatat di 04):
- `dialogue_advanced` dipakai game untuk lencana "Belum didengar" (K5).
- `audio_pause` adalah satu-satunya pembawa lama mendengar yang belum selesai (K6).
- `last_input_ms` Cari Objek memakai rujukan waktu berbeda dari tantangan lain ([§6.1](#61-saat-butir-bisa-dijawab-ready_ms)).

**Cek kemutakhiran dan tinjauan pengembang, 2 November 2026** (gelombang A, sesi 5; `asal: pengembang`, pemeriksaan berbantuan AI). Dicek terhadap entri 04 sesudah 30 Oktober 2026 (putaran kedua 30–31 Oktober; 1 November: ADDIE dengan KG3a dan KG3b, satu studi per uji, versi dikunci di G4, aturan "dibangun sekarang"), terhadap 11, 26, 27, 28, dan 30, dan terhadap kode. Urutan migrasi `003600` dan `003700` cocok dengan [27 §6.1](27_IMPLEMENTASI_PERAN_PENELITI.md#61-migrasi-2026-01-01-003400_replaceteacherwithresearcherroles). Temuan K5 tetap benar. Temuan K6 tetap benar, tetapi satu celah dicoret: narasi lain yang mulai sudah mengirim `pause` ([§5.4](#54-audio_pause-dan-lama-mendengar)). Nomor baris yang meleset dibetulkan (`RawDataService` baris 67, 86–95, 107–240; `core/audio.js` 414–419, 427–437), dan rujukan yang terlewat ditambah (`ExportService::ADMIN_ONLY_SHEETS`, `Simulator` baris 467, komentar `dialogue_advanced` di tujuh berkas lain).

Dua putaran tanya, delapan pertanyaan; pengembang memilih usulan di semuanya:

5. **Penghapusan tetap di migrasi `003600`** (K3 tetap), dan di server produksi migrasi itu hanya dijalankan bila pengembang memutuskannya. Karena `003400` sudah lebih dulu menahan server produksi, memisah penghapusan tidak memberi apa-apa. Tidak dipilih: `003600` hanya menambah kolom, dan penghapusan menjadi perintah spark terpisah.
6. **Situs statis memakai `kolom.json`** dari `gelita:lembar:templat --kolom`; bila dibuat di luar repo, pengembang mencocokkannya sekali sebelum KG3a (§18 no. 1). Tidak dipilih: situs statis dihapus dan hanya templat XLSX yang dipakai.
7. **Pemeriksaan nama cukup untuk rombel itu**; nama lain dijaga aturan menulis di 02 dan pelatihan tim (§18 no. 2). Tidak dipilih: daftar kata tambahan di halaman studi (data pribadi di server, harus diisi terus).
8. **`is_touch` tetap untuk admin**; dikembalikan ke lembar `sesi` lewat `kamus-1.1` bila peneliti memintanya (§18 no. 3). Tidak dipilih: masuk lembar peneliti sejak awal.
9. **Batas `empty_tap` tetap 30** per petunjuk per pengerjaan, sebagai angka konfigurasi yang dicek di KG3a dan KG3b dan ditetapkan sebelum G4 (§18 no. 4). Tidak dipilih: tanpa batas.
10. **Tanpa arsip data lama** sebelum `003600` dijalankan di server produksi, karena server belum memuat data studi; jumlahnya tercatat lewat `gelita:log:hitung` dan baris audit (§18 no. 5). Tidak dipilih: pengembang membuat salinan database di luar sistem dulu.
11. **Unduhan selalu terikat satu studi** (K16), mode penelitian menyala atau mati, mengikuti "satu baris `research_studies` per uji" (04, 1 November 2026). Tidak dipilih: hanya dalam mode penelitian, sehingga satu berkas bisa mencampur studi.
12. **Versi kamus dan spesifikasi lembar dikunci di G4** bersama versi game (K17). Tidak dipilih: kamus masih boleh naik versi selama KG5.

Dengan keputusan ini 29 `disetujui pengembang` (2 November 2026).

**Sesi koordinator 31–35, 3 November 2026** (cabang `r10-cek-30`, `asal: pengembang`, `disetujui pengembang`):

13. **Urutan lembar di §7.2 menjadi resmi**: … `butir`, `kode_pilihan`, lembar `turunan_*` (termasuk `turunan_tes` dari 35), `tes_jawaban`, `tes_bagian`, `angket`, `sikap`, `risiko_gabungan`, `izin`, lalu lembar lapangan `l1_…`–`l6_…`. Tidak dipilih: urutan dibiarkan terbuka sampai implementasi.
14. **`manual_code` diisi lewat impor L5** (§10.4, §10.7): templat dan impor L5 mendapat kolom `manual_code` per percobaan butir (dengan `try_no`), diperiksa terhadap kode kesalahan butir itu di `item_error_codes` (31), lalu ditulis ke `item_tries.manual_code`. Tidak dipilih: `manual_code` dibuang.

---

## 20. Akibat ke dokumen lain

Dicatat di sini dan dibawa sesi koordinator setelah bagian 4 selesai.

**Status per 30 Oktober 2026:** semua baris sudah dibawa sesi koordinator (PR #285, #286, #287), kecuali yang menunggu `kamus-1.1` atau implementasi. Baris bertanggal 2 November 2026 (cek kemutakhiran) belum dibawa.

| Dokumen | Bagian | Perubahan | Status |
| --- | --- | --- | --- |
| [01](01_REVISI_10_ANALITIK.md) | *Log: dihapus atau dipisah* | Baris `answer_changed, answer_selected`: `answer_changed` adalah kiriman Cari Objek yang dinilai dan digabung ke `answer_submitted`; `dialogue_advanced` diganti `dialogue_completed`; `audio_pause` tidak lagi event mentah, baris lama mendengarnya tetap; tiga event baru (`dialogue_completed`, `passage_reopened`, `empty_tap`); baris `answer_submitted, wrong_target_clicked`: duplikasi diputuskan (tabel percobaan sumber analisis) | Sudah (sesi koordinator, 30 Oktober 2026, PR #285) |
| [01](01_REVISI_10_ANALITIK.md) | *Olah data*; no. 15 | Lembar diunggah ke sistem (bukan lagi "opsional dan bisa nanti"); no. 15 menyebut templat terisi per sesi dan impor | Sudah (sesi koordinator, 30 Oktober 2026, PR #285) |
| [02](02_LEMBAR_LAPANGAN.md) | *Cara mencetak dan mengisi* | Templat diunduh per sesi dari panel; lembar diunggah paling lambat 24 jam; ketikan kedua lewat unggah cek; kutipan L5 dan token L1 tidak diunggah; L1 *Headset*, L2 *Pakai bareng* dan *Pindah perangkat* menjadi dua kolom di templat | Sudah (sesi koordinator, 30 Oktober 2026, PR #285) |
| [03](03_KODE_DAN_ARTI.md) | Daftar event, bila ada | `dialogue_completed`, `passage_reopened`, `empty_tap`; jenis yang dihapus | Sudah (sesi koordinator, 30 Oktober 2026, PR #285) |
| [11](11_KAMUS_DATA.md) | Analitik 5, 6, 8; *Kunci penggabungan dan waktu*; *Yang harus disediakan sistem* | `ready_ms` sebagai sumber waktu putus; `last_input_ms` Cari Objek sebelum perubahan memakai rujukan lama; `empty_tap` mencatat koordinat, jaraknya diturunkan; `passage_reopened` untuk bagian (b); `offset_known`; lembar lapangan yang diimpor sebagai sumber penanda; `is_touch` (pertanyaan no. 3) | **Sudah**: `kamus-1.1` draf (3 November 2026) |
| [22](22_PERMOHONAN_IZIN_ETIK.md) | *Kerahasiaan dan pengelolaan data*, tabel data | Lembar L1–L6 yang diketik disimpan di server, berkode dan tanpa nama, dan diunduh lewat akun `ketua`; kutipan L5 tetap di folder transkrip; rincian perangkat dikurangi (tanpa user agent, merek, model) | Sudah (sesi koordinator, 30 Oktober 2026, PR #286) |
| [26](26_PENILAIAN_DAMPAK_PELINDUNGAN_DATA.md) | A8, *Aliran data* baris 5, L1 log teknis, inventaris perangkat | A8: "Kertas, lalu templat dalam 24 jam, lalu diunggah ke server"; tempat penyimpanan server; log teknis tanpa rincian perangkat yang dihapus; peringatan nama di impor sebagai langkah perlindungan untuk R2 | Sudah (sesi koordinator, 30 Oktober 2026, PR #286) |
| [27](27_IMPLEMENTASI_PERAN_PENELITI.md) | §9 (lembar `event`, `answer_key`), §16 no. 5 | Ketua mendapat keduanya; butir risiko daring sebagai angka gabungan | Sudah (sesi koordinator, 30 Oktober 2026) |
| [27](27_IMPLEMENTASI_PERAN_PENELITI.md) | §4 titik sambung | Templat dan unggah lembar di bawah `field_sessions.manage` | Sudah (sesi koordinator, 30 Oktober 2026) |
| [28](28_IMPLEMENTASI_SESI_LAPANGAN.md) | §15 rute, §21 no. 5 | Rute lembar di bawah `/admin/sesi-lapangan/{id}/lembar`; `markStale` menulis `session_paused {via: idle}` | Sudah (sesi koordinator, 30 Oktober 2026) |
| [00](00_INDEKS.md) | *Tahap dokumen 3* | Baris bagian 3 | Sudah (cabang ini) |
| [11](11_KAMUS_DATA.md) | *Versi dan cara berubah* | `kamus-1.1` selesai sebelum G4 dan dikunci bersama versi game; selama KG5 versi tidak berubah (K17). Catatan "setelah uji coba" berarti setelah KG3a dan KG3b | **Sudah**: `kamus-1.1` draf (3 November 2026) |
| [02](02_LEMBAR_LAPANGAN.md) | *Cara mencetak dan mengisi* | Situs statis memakai `kolom.json` dari spesifikasi; spesifikasi lembar dikunci di G4 (K17) | Sudah (sesi tindak lanjut dokumen, 3 November 2026) |
| [27](27_IMPLEMENTASI_PERAN_PENELITI.md) | §9 atau halaman studi | Unduhan `ketua` selalu memerlukan satu studi (K16), juga saat mode penelitian mati; tanpa studi, halaman unduh meminta studi dibuat dulu | Sudah (sesi tindak lanjut dokumen, 3 November 2026) |
| `35_IMPLEMENTASI_LAPIS_DATA_TURUNAN.md` (gelombang B, sesi 9) | Seluruhnya | Yang perlu diketahui dari bagian ini: (1) data mentah §6: `item_tries.ready_ms` dengan rujukan waktu sejak tantangan dibuka, kosong untuk Isi Rumpang dan Susun Gambar dan bila lebih besar dari `last_input_ms`; `last_input_ms` Cari Objek sebelum perubahan ini memakai rujukan lama (sejak petunjuk tampil); `empty_tap` mencatat `x_pct`, `y_pct`, `round`, `t_ms`, dan `overflow` (batas 30), jaraknya diturunkan dari data adegan di bank; `passage_reopened` hanya setelah masukan pertama. (2) Lembar *Kamus Data* (§8): `ResearchDictionary::VERSION` adalah satu-satunya konstanta versi; lapis turunan menyimpan `dictionary_version` di setiap tabelnya, dan versi dikunci di G4 (K17). (3) Titik sambung no. 9 (§15): lembar lapangan yang diimpor ada di `field_sheet_rows` (hanya impor `purpose = main` yang belum `superseded_at`), dengan `participant_id`, `seat_no`, `started_at`, `ended_at` sebagai kolom dan isian lain di `values_json` dengan kode kolom §10.7; penanda `helped_content`, `deviation_code`, `shared_device` dihitung lapis turunan. (4) Waktu event di unduhan adalah `occurred_at_corrected` dengan `offset_known` (§7.4); waktu `item_tries` adalah waktu server. (5) Unduhan selalu satu studi (K16), jadi tabel turunan juga dihitung per studi | Untuk sesi 9 (2 November 2026) |
