# GELITA

**GELITA** (Game Edukasi Literasi dan Etnopedagogi Kedu) adalah game edukasi berbasis web untuk siswa SD/SMP sekaligus instrumen penelitian. Pemain berperan sebagai Jaka, dibimbing Mbah Kedu, menjelajahi tiga wilayah eks-Karesidenan Kedu — **Temanggung → Magelang → Wonosobo**, masing-masing lima tantangan — sambil belajar literasi membaca, budaya lokal, dan literasi keamanan digital. Setiap jawaban, perubahan jawaban, petunjuk, dan pemutaran audio dicatat sebagai data penelitian.

Aplikasi dibangun dengan **CodeIgniter 4.7** (PHP 8.2+, MySQL 8 / MariaDB 10.6+), dirender server dengan HTML5 + CSS3; JavaScript (ES modules, tanpa framework) hanya menambah perilaku. Area game dwibahasa (ID/EN), panel admin berbahasa Indonesia.

## Status pengembangan

**Tahap 1–7 dari 8 selesai; tahap berikutnya adalah 8 (deployment & operasional).**

| Tahap | Isi | Status |
|---:|---|---|
| 1 | Database: 29 tabel domain + `ci_sessions`, 34 migration, 9 seeder | selesai |
| 2 | Fondasi: konfigurasi, filter, helper, berkas bahasa, `PasswordPolicy`, kerangka command | selesai |
| 3 | Model (29), Entity (11), Service (8) — skor, sesi, tantangan, event, analitik, impor bank soal | selesai |
| 4 | Route, controller, autentikasi siswa & staf, otorisasi berlapis tiga, API JSON seragam | selesai |
| 5 | View, UI, CSS: 3 layout, 17 komponen, 17 halaman game + 5 arena, 33 view admin, 6 berkas CSS | selesai |
| 6 | Perilaku JavaScript: lima mesin arena, antrean event offline, narasi + telemetry audio, chart ECharts, editor konten terpandu | selesai |
| 7 | Integrasi fitur: `ExportService` (XLSX streaming), `ReportService` (PDF), `RetentionService`, lima command `gelita:*` | selesai |
| 8 | Deployment & operasional | berikutnya |

Kelima arena kini dapat dimainkan penuh di browser — Periksa, petunjuk, keluar berkonfirmasi, narasi audio, dan lentera yang bertambah dari respons server — dengan seluruh event gameplay dan telemetry audio tercatat, termasuk saat koneksi sempat putus. Panel admin menggambar seluruh chart dengan ECharts dari `/api/admin/*`, dan filter mengubah isi halaman tanpa memuat ulang.

Yang sudah berfungsi penuh lewat HTTP: persetujuan dan registrasi dengan kata sandi kuat (termasuk dua metrik literasi keamanan digital), masuk/keluar, ganti sandi dan reset sandi oleh guru, peta Kedu dan peta wilayah dengan kunci berurutan, dialog pembuka wilayah, kelima arena beserta seluruh API penilaiannya, layar selesai dan riwayat hasil, Pustaka Kedu, profil, Balai Refleksi, pergantian bahasa tanpa kehilangan progres, serta seluruh halaman panel admin (dasbor, peserta, sesi, analitik, masukan, konten, impor bank soal, media & audio, studi & rilis, tata kelola, akun staf).

### Tahap 7 — Integrasi fitur

Ekspor, laporan, retensi, dan command kini berfungsi penuh. Rincian dan keputusannya ada di [`docs/07_FEATURE_INTEGRATION.md` → *Catatan Implementasi Tahap 7*](docs/07_FEATURE_INTEGRATION.md#catatan-implementasi-tahap-7). Yang perlu diketahui:

- **Ekspor XLSX** (`/admin/ekspor`): sepuluh sheet, ditulis bertahap oleh `App\Libraries\ExcelWriter` (XML SpreadsheetML + ZipArchive) sehingga 150.000 baris Raw Events hanya memakai ±18 MB memori. Berkas di `writable/exports/`, ber-SHA-256, kedaluwarsa setelah `gelita.exportRetentionDays`. Raw Events di atas `gelita.exportMaxRawEvents` (200.000) ditolak dengan permintaan mempersempit rentang.
- **Hak dibaca ulang di service.** `ExportService` mengambil role dan sekolah pemohon dari `staff_users`: guru selalu anonim, hanya sekolahnya, tanpa Raw Events dan tanpa kunci jawaban — apa pun isi formulirnya. Mode anonim tidak membuat kolom nama/nama pengguna/nama sekolah sama sekali; `school_ref` (`SCH-000012`) tetap ada untuk analisis per sekolah.
- **Laporan PDF** (mPDF): ringkasan studi dari halaman ekspor, dan laporan satu peserta dari `/admin/peserta/{id}`.
- **Penghapusan** (`/admin/tata-kelola`) dipindah ke `RetentionService`: cakupan peserta, sesi, atau studi (dipersempit fase & rentang tanggal); eksekusi dibatalkan bila jumlah baris berubah jauh sejak pratinjau.
- **Retensi** (`php spark gelita:retention:run`, cron harian): sesi menganggur → `paused`, attempt menggantung > 24 jam → `abandoned`, sesi `paused` > 30 hari → `abandoned`, berkas ekspor kedaluwarsa dibuang, dan data yang melewati `retention_days` **hanya** dibuatkan pratinjau penghapusan + peringatan di dasbor admin.
- **Command**: `gelita:content:verify`, `gelita:media:scan`, `gelita:score:recompute`, `gelita:bank:import`, `gelita:retention:run` — semuanya mengembalikan kode keluar 1 saat gagal sehingga dapat dipakai di skrip deploy.

### Tahap 6 — JavaScript

ES modules tanpa bundler di [`public/assets/js/`](public/assets/js/): lapisan inti (`core/`), perilaku halaman game (`game/`), lima mesin (`engines/`), dan panel admin (`admin/`). Rincian dan keputusannya ada di [`docs/06_JAVASCRIPT.md` → *Catatan Implementasi Tahap 6*](docs/06_JAVASCRIPT.md#catatan-implementasi-tahap-6). Yang perlu diketahui:

- **Client tidak pernah menilai.** Benar/salah, skor, bintang, serpihan, dan jumlah keping yang keliru datang dari respons server.
- **Antrean offline per sesi.** Event yang gagal terkirim disimpan di `localStorage` dengan penanda sesi buram (`sessionTag`, HMAC `game_session_id`) dan hanya dikirim ulang ke sesi yang sama — komputer kelas dipakai bergantian.
- **Kontrak server yang ditambahkan:** `elapsed_ms` di payload tantangan, `detail.pieces_correct` di respons `/check`, kode `CSRF_EXPIRED` / `INVALID_SESSION` saat token CSRF ditolak di API, kunci `chart` di API admin (`App\Libraries\ChartData`), dan `play_index` audio kini berarti "pemutaran ke-n".
- Teks yang ditulis JavaScript game ada di `app/Language/{id,en}/Js.php`.

### Audit pra-tahap 6

Sebelum tahap 6 dimulai, seluruh tahap 1–5 diaudit terhadap dokumen `docs/01`–`08`, dan alurnya ditelusuri ujung ke ujung di atas MariaDB 10.11 (registrasi → 15 node lintas lima engine → refleksi → reset sandi guru → seluruh halaman admin, 199 pemeriksaan). Cacat yang ditemukan dan diperbaiki:

- **Attempt `cari` tidak pernah dapat ditutup** — objek jebakan dihitung sebagai soal belum dijawab, sehingga node 4 Temanggung dan seluruh wilayah sesudahnya terkunci. Kini satu aturan (`ChallengeService::expectsAnswer()`) dipakai petunjuk, progres, dan syarat `/complete`.
- **Penilaian first-pass** — klik salah pertama di arena `cari` kini menutup first-pass petunjuk itu sebagai salah; `allow_retry = false` (arena `pilihan`) kini ditegakkan server sehingga jawaban kedua tidak menimpa yang pertama.
- **Kontrak data untuk tahap 6** — payload attempt memuat id petunjuk (`hints`); `/api/events` menerima `node_id`/`attempt_id`/`item_id`; `/api/audio-events` idempotent per `client_event_id`; waktu event klien tidak lagi kehilangan milidetik; linimasa diurutkan `occurred_at` lalu `sequence_no` dengan index `(session_id, occurred_at)` dari migration baru `003300`.
- **Keamanan** — Debug Toolbar di environment development menyimpan kata sandi mentah ke `writable/debugbar/`; `App\Filters\DebugToolbar` kini menyamarkan field kata sandi.
- Event `password_changed` tidak lagi hilang setelah reset guru; templat workbook bank soal lolos pratinjaunya sendiri; dokumentasi diselaraskan (mis. `unlock_mode = sequential | free`).

Rincian keputusan ada di dokumen tahap masing-masing; ringkasan UI di [`docs/05_VIEW_UI.md` → *Catatan Implementasi Tahap 5*](docs/05_VIEW_UI.md#catatan-implementasi-tahap-5).

### Keputusan penting

- **Server adalah sumber kebenaran.** Skor, bintang, status buka/kunci, dan serpihan dihitung server; kunci jawaban tidak pernah dikirim ke browser. Payload soal ditanam sebagai `<script type="application/json" id="challenge-data">`. Arena `cari` hanya mengirim token objek per attempt (HMAC dari `encryption.key`), sehingga objek jebakan tidak dapat dibedakan dari sumber halaman.
- **Wilayah yang baru terbuka selalu dibuka lewat dialog pembukanya**, juga bila URL di dalamnya diketik langsung; tanda "dialog sudah tampil" disimpan per sesi login.
- **Tanpa JavaScript tetap terbaca**: slide memakai `:target`, konfirmasi memakai `<details>`, setiap chart admin punya visual cadangan dari server.
- **Aset di-host sendiri**: ECharts 6.1.0, Howler.js 2.2.4, dan font Cinzel/Plus Jakarta Sans/IBM Plex Mono ada di repositori; tidak ada permintaan ke domain pihak ketiga. Selama gambar belum diunggah, view memakai pengganti (gradien, monogram).
- **Otorisasi berlapis tiga** (route → controller → service): guru hanya melihat sekolahnya, ekspor guru selalu anonim, dan halaman khusus admin membalas `404` untuk guru.

### Batas ruang lingkup saat ini

- **Narasi petunjuk arena `cari` belum bersuara**: payload `clues` belum membawa aset audio; teks petunjuk tampil dan diumumkan ke pembaca layar.
- **Ekspor dibangun sinkron** di request yang sama (batas 300 detik). Untuk dataset yang jauh lebih besar dari ambang Raw Events, pemindahan ke antrean kerja dibahas pada tahap 8.
- **Cron belum terpasang** — `gelita:retention:run` siap dipakai; pemasangan crontab adalah bagian tahap 8. Sampai itu, jalankan dari tombol di `/admin/tata-kelola`.
- `App\Libraries\HashedIpSessionHandler` **belum dipasang**; alasannya di bagian *Sesi dan CSRF* di bawah.

## Prasyarat lokal

- PHP 8.2 atau lebih baru (PHP 8.3 disarankan)
- Composer 2.6 atau lebih baru
- MySQL 8.0+ atau MariaDB 10.6+
- Web server lokal, atau server bawaan CodeIgniter untuk pengembangan
- Ekstensi PHP: `intl`, `mbstring`, `json`, `mysqli`/`mysqlnd`, `curl`, `gd`, `zip`, `fileinfo`, `openssl`, `iconv`, `dom`, `xml`, `xmlwriter`, dan `simplexml`

## Menjalankan secara lokal

1. Pasang dependensi dari lockfile proyek:

   ```bash
   composer install
   ```

2. Salin templat environment, lalu sesuaikan isinya:

   ```bash
   cp env .env
   ```

   Berkas `env` adalah templat yang dilacak Git; `.env` hasil salinannya **tidak pernah** di-commit. Yang wajib diisi sebelum aplikasi dijalankan:

   | Kunci | Keterangan |
   |---|---|
   | `app.baseURL` | URL aplikasi lokal Anda |
   | `database.default.*` | kredensial database |
   | `encryption.key` | bangkitkan dengan `php spark key:generate`; wajib — token objek arena `cari` diturunkan darinya |
   | `gelita.ipSalt` | string acak panjang; garam ini yang membuat hash IP tidak dapat dibalik. Jangan dibiarkan kosong |

3. Buat database bila belum ada, jalankan migration, lalu isi data awal:

   ```bash
   mysql -u root -p -e "CREATE DATABASE gelita CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   php spark migrate
   GELITA_ADMIN_PASSWORD='SandiKuatAnda123!' php spark db:seed DatabaseSeeder
   ```

   Tabel sesi `ci_sessions` sudah termasuk migration proyek — jangan menjalankan `php spark session:migration`. Pada `CI_ENVIRONMENT = development`, `DatabaseSeeder` juga menjalankan `SampleItemSeeder` (2 butir contoh per node) agar kelima belas tantangan dapat dimainkan sebelum bank soal diimpor.

4. Jalankan server pengembangan:

   ```bash
   php spark serve
   ```

   Akun staf awal: `admin` dengan sandi dari `GELITA_ADMIN_PASSWORD`, di `/admin/login`. Siswa mendaftar sendiri lewat `/mulai`.

## Migration

Migration dijalankan berurutan dari `000100` sampai `003300` (34 berkas) dan menghasilkan 31 tabel: 29 tabel domain, `ci_sessions`, dan `migrations`. `php spark migrate:rollback -b 0` mengembalikan database ke kosong.

Empat migration terakhir adalah koreksi. Perbaikan skema selalu datang sebagai migration baru; migration lama tidak diubah.

| Versi | Peran |
|---|---|
| `002100` | membuat tabel `challenge_attempts` dengan nama kolom awal `first_pass_rate` / `final_rate` |
| `003100` | koreksi kanonik: mengganti nama kedua kolom itu menjadi `first_pass_accuracy` / `final_accuracy` dan menambahkan `check_count`, `answer_change_count`, `audio_use_count` |
| `003200` | menegakkan `NOT NULL` + default pada kelima kolom di atas (Forge membuatnya nullable saat `ALTER`), termasuk mengisi baris lama yang masih `NULL` |
| `003300` | menambahkan index wajib `game_event_logs (session_id, occurred_at)` yang tidak dibuat `002400` |

`003200` memanggil `resetDataCache()` sebelum memeriksa kolom; tanpa itu seluruh pemeriksaan `fieldExists()` membaca daftar kolom versi sebelum `003100` pada proses `spark migrate` yang sama. Jangan melakukan rollback ke bawah `003100`: tahap 3 bergantung pada penamaan kolom hasil migration tersebut. Rincian lengkap di [`docs/01_DATABASE.md`](docs/01_DATABASE.md#koreksi-challenge_attempts-003100-dan-003200).

## Aset vendor

Library frontend di-host sendiri di [`public/assets/vendor/`](public/assets/vendor/); tidak ada CDN. Isinya `echarts.min.js` (ECharts 6.1.0) dan `howler.min.js` (Howler.js 2.2.4) dari paket rilis resmi, beserta lisensinya; versi, checksum, dan cara memperbaruinya ada di [`public/assets/vendor/README.md`](public/assets/vendor/README.md). Font lokal ada di `public/assets/fonts/`. Pola `.gitignore` sengaja `/vendor/` (khusus folder Composer di root) agar folder ini tetap dilacak Git.

## Sesi dan CSRF

- Sesi memakai `DatabaseHandler` dengan tabel `ci_sessions` (migration `003000`), cookie `gelita_session`, masa berlaku 4 jam, `regenerateDestroy` aktif, dan `matchIP` nonaktif.
- CSRF memakai mode `session` dengan token acak per-request (`tokenRandomize`) tetapi **tanpa** regenerasi per-submit (`regenerate = false`). Game mengirim banyak request AJAX beruntun; token yang berubah setiap request membuat request paralel gagal. JavaScript mengirim token lewat header `X-CSRF-TOKEN`.
- `App\Libraries\HashedIpSessionHandler` (penyimpan hash IP, bukan IP mentah) **belum dipasang**. Kelas itu mewarisi `MySQLiHandler` yang mengunci sesi dengan `GET_LOCK`, dan `Services::session()` hanya memetakan handler per-platform ketika driver persis `DatabaseHandler::class` — sehingga driver kustom melewati pemilihan platform dan gagal pada database uji SQLite. Perubahan yang diperlukan beserta buktinya dicatat di [`docs/02_PROJECT_FOUNDATION.md` § 3b](docs/02_PROJECT_FOUNDATION.md#3b-library-hashedipsessionhandler--belum-dipasang).

## Pengujian

Jalankan test suite tanpa laporan coverage:

```bash
vendor/bin/phpunit --no-coverage
```

Suite (123 test) memakai grup database `tests` (SQLite3 in-memory) dan hanya menjalankan migration bernamespace `Tests\Support`, bukan migration aplikasi — skema aplikasi memakai fitur MySQL/MariaDB (`DATETIME(6)`, `ON UPDATE CURRENT_TIMESTAMP(6)`) yang tidak ada di SQLite. Sesi di-mock dengan `ArrayHandler` oleh `CIUnitTestCase`. Yang dikunci suite antara lain:

- `RouteWiringTest` — auto-route tetap mati, setiap handler menunjuk kelas/method yang ada, dan setiap view yang disebut controller punya berkasnya.
- `HuntPayloadTest` — payload arena `cari` tidak membedakan jebakan, jawaban hanya lewat token objek, dan objek jebakan tidak pernah diminta dijawab.
- `RegionEntryTest`, `DialogueGateTest` — wilayah baru selalu lewat dialog pembukanya.
- `JsConfigTest` — penanda sesi antrean offline buram dan per sesi, `#app-config` tanpa identitas siswa, dan setiap `t('…')` di JavaScript punya kunci `Js.*`.
- `ChartDataTest` — bentuk data chart admin dan kondisi kosongnya.
- `ExcelWriterTest` — workbook terbaca ulang PhpSpreadsheet, teks berawalan `=` tidak pernah menjadi rumus, berkas sementara dibersihkan.
- `ExportRulesTest` — mode anonim tanpa kolom identitas, rahasia tidak pernah diekspor, kunci jawaban hanya admin, guru tanpa Raw Events, cakupan sekolah dipaksa service, cakupan & penjaga drift penghapusan.
- `Stage7WiringTest` — kelima command `gelita:*` aktif, service baru terdaftar, dan setiap keluaran templat PDF lewat `esc()`.
- `ScoringServiceTest`, `PasswordPolicyTest`, `ChallengeEntityTest`, `ViewHelperTest`, `LanguageFilesTest`, `EventTimeTest`, `DebugToolbarRedactionTest`.

Karena tabel aplikasi tidak dibuat di suite ini, **alur HTTP ujung ke ujung diverifikasi di atas MySQL/MariaDB sungguhan**. Gunakan database scratch terpisah (kredensial boleh dioper lewat environment untuk perintah CLI):

```bash
env "database.default.database=gelita_scratch" \
    "database.default.username=..." \
    "database.default.password=..." \
    php spark migrate
```

lalu seed, jalankan `php spark serve`, dan telusuri alur siswa (registrasi → tantangan → refleksi) serta halaman admin. `php spark serve` membaca `.env`, jadi untuk penelusuran HTTP isi `.env` lokal dengan database scratch tersebut.

Perilaku JavaScript tahap 6 diverifikasi dengan cara yang sama di browser sungguhan (Playwright + Chromium): alur siswa 15 node beserta jalur galatnya, antrean offline, telemetry audio, dan seluruh halaman admin ber-chart. Konten dialog dan butir di-cache `ContentRepository`; setelah mengubah data langsung di database, jalankan `php spark cache:clear`.

## Dokumentasi

Dokumen spesifikasi per tahap ada di folder [`docs`](docs/) dan mencerminkan implementasi terkini:

1. [Database](docs/01_DATABASE.md)
2. [Fondasi proyek](docs/02_PROJECT_FOUNDATION.md)
3. [Model, entity, dan service](docs/03_MODEL_ENTITY.md)
4. [Route, controller, autentikasi, otorisasi](docs/04_CONTROLLER_ROUTE.md)
5. [View, UI, dan CSS](docs/05_VIEW_UI.md)
6. [JavaScript](docs/06_JAVASCRIPT.md)
7. [Integrasi fitur](docs/07_FEATURE_INTEGRATION.md)
8. [Deployment dan operasional](docs/08_DEPLOYMENT.md) — tahap berikutnya

Daftar route lengkap dapat dilihat kapan saja tanpa membaca kode:

```bash
php spark routes
```

## Catatan keamanan

- Jangan pernah commit atau membagikan `.env`; berkas ini memuat kredensial dan material rahasia aplikasi. Yang dilacak Git hanyalah templat `env` tanpa nilai rahasia.
- `gelita.ipSalt` wajib diisi string acak panjang per pemasangan. Salt kosong membuat hash IP berupa SHA-256 tanpa garam yang dapat dibalik dengan pencarian menyeluruh ruang IPv4.
- Server sekolah wajib memakai `CI_ENVIRONMENT = production` (templat `env` berisi `development` untuk kerja lokal). Di development, Debug Toolbar menulis potret setiap request ke `writable/debugbar/`; field kata sandi disamarkan `App\Filters\DebugToolbar`, tetapi data lain tetap tersimpan.
- Gunakan akun database dengan hak akses seperlunya, terutama di production.
- Konfigurasi web server harus selalu mengarah ke folder [`public`](public/), bukan root proyek.
- Untuk deployment, gunakan dependensi yang terkunci melalui `composer install --no-dev --optimize-autoloader`.
