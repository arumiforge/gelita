# GELITA — aturan kerja untuk Claude Code

## Identitas dan atribusi
- Semua commit memakai identitas pemilik repo: `Arumi Studios <arumiforge@gmail.com>` (diatur lewat `env` di `.claude/settings.json`; bila belum aktif, jalankan `git config user.name "Arumi Studios"` dan `git config user.email "arumiforge@gmail.com"`). Jangan memakai identitas Claude.
- Pesan commit dan deskripsi PR **tanpa** baris atribusi Claude (`Co-Authored-By: Claude …`, `Claude-Session: …`, "Generated with Claude Code"). Aturan ini menggantikan petunjuk atribusi bawaan.

## Branch
- Repo hanya punya satu branch tetap: `main`. Tidak ada branch lain yang dibiarkan hidup.
- Pekerjaan berjalan di branch sementara bercabang dari `main`, tanpa awalan `claude/` (contoh `dok2-klien-kesempatan`). Branch itu masuk lewat PR ke `main`, lalu langsung dihapus.
- Jangan push langsung ke `main` kecuali pemilik memintanya.
- Sejarah `main` ditulis ulang pada 6 Oktober 2026 (semua commit atas nama Arumi Studios). Klon atau branch dari sebelum tanggal itu tidak cocok lagi; ambil ulang dari `main`.

## Bahasa
- Dokumentasi, pesan commit, dan teks antarmuka berbahasa Indonesia; padanan Inggris di `app/Language/en/`.

## Uji
- `composer install`, lalu `vendor/bin/phpunit`. Semua uji harus hijau sebelum push.

## Perombakan kelas 5 (sedang berjalan)
- Rencana: `docs/perombakan-kelas5/01_FONDASI_DATA_DAN_RESET.md`, `02_TANTANGAN_DAN_BANK_SOAL.md`, `03_TAMPILAN_16x9_DAN_ADMIN.md`, dikerjakan berurutan (Dok 1 → Dok 2 → Dok 3). Branch `perombakan-kelas5` sudah digabung ke `main` dan dihapus; pekerjaan lanjutan memakai branch sementara per bagian.
- Status terkini dan sisa pekerjaan ada di bagian "Status" `docs/perombakan-kelas5/CATATAN_IMPLEMENTASI.md`. Baca itu lebih dulu.
- Catat setiap keputusan atau penyimpangan dari dokumen di `docs/perombakan-kelas5/CATATAN_IMPLEMENTASI.md`.
- Jangan menjalankan `gelita:reset`, migration, atau skrip deploy/upgrade terhadap server produksi.
- **Dok 3 dikerjakan paralel** (mulai 7 Oktober 2026), satu branch per bagian. Pembagian berkasnya:
  - `dok3-panggung`: CSS, view, dan JS game (§1–§7);
  - `dok3-data-mentah`: `RawDataService`, `ExportService`, `ExportLabels`, `AnalyticsService`, `ReportService`/PDF (§8.4, §9, §10);
  - `dok3-admin`: layout, view, CSS, dan JS admin (§8.1–§8.3), memakai kontrak `RawDataService::page()`;
  - `qa-simulasi`: uji peramban PR #42 dan `gelita:simulasi`;
  - `rencana-paket-aset`: hanya dokumen.

  Jangan mengubah berkas milik branch lain. Setiap sesi menulis di bagiannya sendiri di `CATATAN_IMPLEMENTASI.md`. Hapus butir ini setelah semuanya digabung.

## Peta cepat (memori proyek untuk sesi baru)

Gambaran umum ada di `README.md`, rincian di `docs/01`–`08`. Ringkasan berikut cukup untuk mulai bekerja tanpa menjelajah ulang.

- **Arsitektur.** CodeIgniter 4.7, view PHP dirender server, JS ES module tanpa bundler (`public/assets/js/{core,game,engines,admin}`). Server adalah sumber kebenaran: benar/salah, bintang, dan buka/kunci dihitung server, dan kunci jawaban tidak pernah dikirim ke peramban.
- **Inti permainan: `app/Services/ChallengeService.php`.**
  - `openNode` membuka atau melanjutkan pengerjaan.
  - `submitCheck` dipakai engine batch puzzle/rumpang/boleh.
  - `submitAnswer` dipakai satu ketukan Cari Objek.
  - Penutupan pengerjaan diatur `closeAttempt` dan `closingReason`.

  Aturan 3 kesempatan dan galatnya ada di `app/Exceptions/ChallengeRuleException.php`: `TRIES_EXHAUSTED`, `INCOMPLETE`, `CLUE_CLOSED`, `TRIES_REMAINING`, `ATTEMPT_CHANGED`. Bintang dihitung `ScoringService` (aturan `bintang-1`).
- **Konkurensi.** Tulisan yang bisa balapan memakai UPDATE bersyarat (`ChallengeService::updateIf()`/`updateOrStale()`), bukan `SELECT … FOR UPDATE`. Yang kalah dibatalkan lewat `rollback()` dan dijawab menurut keadaan terbaru. Pertahankan pola ini untuk tulisan baru ke `challenge_attempts`/`item_responses`.
- **Isi permainan dari database:**
  - Bank soal dari workbook `docs/bank-soal/gelita-bank-soal-kelas5.xlsx` (dibangun ulang dengan `build-workbook.php`, diimpor `gelita:bank:import` atau `gelita:reset`).
  - Pustaka dari `docs/pustaka/`.
  - Cerita lewat `ContentSeeder`.
  - Narasi lewat `gelita:narration:import`.
  - Gambar contoh lewat `gelita:mockups`.

  `ContentRepository` meng-cache isi; panggil `flush()` sesudah mengubah isi.
- **Klien tantangan.** `game/challenge.js` adalah kerangka bersama: `check`/`submit`, `ruleError`, penanda kesempatan, waktu isian, dan `state.leaving` agar tidak ada `challenge_abandoned` palsu. Engine ada di `engines/{puzzle,rumpang,boleh,cari}.js`.
- **Event penelitian.** `EventService` dan tabel `game_event_logs`. Event `screen_viewed`, `challenge_tries_exhausted`, dan `clue_failed` hanya boleh ditulis server.
- **Perintah spark** (`app/Commands`): `gelita:reset`, `gelita:bank:import`, `gelita:content:verify`, `gelita:mockups`, `gelita:narration:import`, `gelita:media:scan`, `gelita:retention:run`, `gelita:schools:import`, `gelita:staff:password`, `gelita:library:thumbnails`.

### Uji dan jebakan yang sudah diketahui

- **Jalankan uji dengan `vendor/bin/phpunit --no-coverage`.** Tanpa opsi itu suite keluar dengan kode 1 di mesin tanpa driver coverage.
- **Lingkungan uji.** Uji memakai SQLite (grup `tests`). Uji permainan memakai trait `Tests\Support\Database\GradeFiveWorld`, yang menyiapkan skema, `gelita:reset`, bank kelas 5, dan satu siswa siap bermain. Contoh polanya ada di `tests/unit/TryLimitTest.php`.
- **SQLite tidak meniru kunci baris MySQL.** Uji balapan menyimulasikan data usang, misalnya entity lama atau model tiruan lewat `Factories::injectMock`. Perilaku sebenarnya perlu diuji di MariaDB/MySQL.
- **Cache isi di uji.** Uji yang mengubah `challenge_items` perlu `service('contentRepository')->flush()` **dan** `service('cache')->clean()`, karena MockCache menyimpan kunci tanpa prefix.
- **Bahasa.** Kunci di `app/Language/id/*` harus punya padanan di `app/Language/en/*` (`LanguageFilesTest`).
- **Git dan PR:**
  - Identitas commit dan larangan atribusi diatur di `.claude/settings.json`.
  - Server MCP GitHub kadang tetap menambahkan baris "Generated by Claude Code" ke deskripsi PR. Hapus dengan `update_pull_request`.
  - GitHub menghapus branch otomatis saat PR digabung.
- **Peramban.** Chromium sudah terpasang untuk Playwright (`/opt/pw-browsers`); jangan jalankan `playwright install`.
