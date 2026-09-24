# GELITA

**GELITA** (Game Edukasi Literasi dan Etnopedagogi Kedu) adalah game edukasi berbasis web untuk siswa SD/SMP sekaligus instrumen penelitian. Pemain berperan sebagai Jaka, dibimbing Mbah Kedu, menjelajahi tiga wilayah eks-Karesidenan Kedu — **Temanggung → Magelang → Wonosobo**, masing-masing lima tantangan — sambil belajar literasi membaca, budaya lokal, dan literasi keamanan digital. Setiap jawaban, perubahan jawaban, petunjuk, dan pemutaran audio dicatat sebagai data penelitian.

Aplikasi dibangun dengan **CodeIgniter 4.7** (PHP 8.2+, MySQL 8 / MariaDB 10.6+), dirender server dengan HTML5 + CSS3; JavaScript (ES modules, tanpa framework) hanya menambah perilaku. Area game dwibahasa (ID/EN), panel admin berbahasa Indonesia.

## Status pengembangan

**Kedelapan tahap selesai.** Aplikasi siap dipasang di server production mengikuti [`docs/08_DEPLOYMENT.md`](docs/08_DEPLOYMENT.md).

| Tahap | Isi | Status |
|---:|---|---|
| 1 | Database: 30 tabel domain + `ci_sessions`, 36 migration, 9 seeder | selesai |
| 2 | Fondasi: konfigurasi, filter, helper, berkas bahasa, `PasswordPolicy`, kerangka command | selesai |
| 3 | Model (30), Entity (12), Service (8) — skor, sesi, tantangan, event, analitik, impor bank soal | selesai |
| 4 | Route, controller, autentikasi siswa & staf, otorisasi berlapis tiga, API JSON seragam | selesai |
| 5 | View, UI, CSS: 3 layout, 20 komponen, 17 halaman game + 5 arena, 34 view admin, 6 berkas CSS | selesai |
| 6 | Perilaku JavaScript: lima mesin arena, antrean event offline, narasi + telemetry audio, chart ECharts, editor konten terpandu | selesai |
| 7 | Integrasi fitur: `ExportService` (XLSX streaming), `ReportService` (PDF), `RetentionService`, lima command `gelita:*` | selesai |
| 8 | Deployment & operasional: skrip deploy/backup/retensi untuk Linux (Bash) dan Windows + Laragon (PowerShell), konfigurasi Nginx, mode pemeliharaan | selesai |

Kelima arena kini dapat dimainkan penuh di browser — Periksa, petunjuk, keluar berkonfirmasi, narasi audio, dan lentera yang bertambah dari respons server — dengan seluruh event gameplay dan telemetry audio tercatat, termasuk saat koneksi sempat putus. Panel admin menggambar seluruh chart dengan ECharts dari `/api/admin/*`, dan filter mengubah isi halaman tanpa memuat ulang.

Yang sudah berfungsi penuh lewat HTTP: persetujuan dan registrasi dengan kata sandi kuat (termasuk dua metrik literasi keamanan digital), masuk/keluar, ganti sandi dan reset sandi oleh guru, ganti sandi sendiri untuk staf (`/admin/akun/sandi`, wajib setelah reset atau pembuatan akun oleh admin), peta Kedu dan peta wilayah dengan kunci berurutan, dialog pembuka wilayah, kelima arena beserta seluruh API penilaiannya, layar selesai dan riwayat hasil, Pustaka Kedu, profil, Balai Refleksi, pergantian bahasa tanpa kehilangan progres, serta seluruh halaman panel admin (dasbor, peserta, sesi, analitik, masukan, konten, impor bank soal, media & audio, studi & rilis, tata kelola, akun staf).

### Pembaruan media, Pustaka Kedu, dan bank soal produksi

- **Unggah media langsung dari editor konten.** Gambar adegan & latar tantangan, gambar butir (keping puzzle, objek cari), gambar opsi, gambar bacaan, peta & latar & lencana wilayah, serta audio dialog dan narasi pembuka tantangan dipilih atau diunggah di formulir masing-masing (`App\Libraries\MediaStore`, komponen `media-field` / `audio-select`). Halaman Media menampilkan di mana setiap aset dipakai dan slot mana yang masih kosong (`App\Libraries\MediaUsage`).
- **Pustaka Kedu multi-media.** Setiap halaman boleh memuat banyak gambar/video (tabel `library_media`, migration `003500`): berkas unggahan atau tautan YouTube, Google Drive, Vimeo, Wikimedia Commons, dan berkas gambar/video langsung (`App\Libraries\MediaLink`). Isi halaman mendukung subjudul, daftar, kotak fakta, catatan sumber, tebal, dan miring (`rich_text()`).
- **Bank soal produksi siap impor**: [`docs/bank-soal/gelita-bank-soal-produksi.xlsx`](docs/bank-soal/gelita-bank-soal-produksi.xlsx) — 15 tantangan, 131 butir, 11 bacaan, 30 halaman Pustaka dwibahasa beserta 48 media dan rujukannya. Workbook dibangun dari data PHP yang dapat ditinjau; cara membangun ulang dan daftar gambar yang masih perlu diunggah ada di [`docs/bank-soal/README.md`](docs/bank-soal/README.md).
- **Templat impor berpanduan.** Workbook templat dan workbook produksi memuat sheet PETUNJUK dan KAMUS_KOLOM (arti setiap kolom dalam bahasa Indonesia), header berwarna menurut status wajib/bersyarat/opsional dengan catatan per kolom, serta dropdown untuk kolom berkode (`App\Libraries\BankWorkbookGuide`). asset_key media yang belum terdaftar dibuatkan slot kosong saat impor.
- Isi panel admin kini selebar layar.

### Tahap 8 — Deployment & operasional

Berkas operasional ada di [`deploy/`](deploy/); kode aplikasi tidak berubah. Langkah pemasangan, alasan tiap aturan, dan hasil uji ada di [`docs/08_DEPLOYMENT.md`](docs/08_DEPLOYMENT.md), terutama [*Catatan Implementasi Tahap 8*](docs/08_DEPLOYMENT.md#catatan-implementasi-tahap-8). Yang perlu diketahui:

- **Dua jalur setara.** Jalur L memakai `deploy/linux/` (Bash, Nginx + PHP-FPM, cron). Jalur W memakai `deploy/windows/` (PowerShell, Nginx Laragon + php-cgi, Task Scheduler).
- **Deploy satu perintah** (`deploy.sh` / `deploy.ps1`). Urutannya:
  1. menolak berjalan bila ada sesi kelas aktif dalam 10 menit terakhir;
  2. membuat backup;
  3. menyalakan mode pemeliharaan (Nginx menjawab 503 selama `writable/pemeliharaan.flag` ada);
  4. menarik kode dan dependency;
  5. menjalankan migration dengan akun `gelita_migrate` lewat `database_default_DSN`;
  6. menaikkan `gelita.assetVersion`;
  7. membuka situs kembali hanya bila `gelita:content:verify` lolos.
- **Backup** (`backup.sh` / `backup.ps1`) memuat database tanpa isi `ci_sessions`, aset unggahan, dan `.env`. Backup disimpan 30 hari lalu disalin ke lokasi kedua. Kegagalan salinan kedua terlihat lewat kode keluar 2.
- **Rollback: migration dulu, kode kemudian.** Setelah `git reset`, berkas migration rilis baru hilang, dan `migrate:rollback` gagal diam-diam dengan kode keluar 0.
- **Skrip `.ps1` hanya ASCII.** Windows PowerShell 5.1 membaca skrip tanpa BOM sebagai cp1252, sehingga tanda pisah `—` memutus string.

Semua skrip dijalankan di server uji (Ubuntu 24.04, MariaDB 10.11, Nginx 1.24, PHP-FPM 8.3, PowerShell 7.4) lewat jalur sukses, jalur gagal, dan penolakan saat ada kelas aktif. Yang masih butuh mesin Windows atau domain sungguhan tercatat di [*Masih belum teruji*](docs/08_DEPLOYMENT.md#masih-belum-teruji).

### Tahap 7 — Integrasi fitur

Ekspor, laporan, retensi, dan command kini berfungsi penuh. Rincian dan keputusannya ada di [`docs/07_FEATURE_INTEGRATION.md` → *Catatan Implementasi Tahap 7*](docs/07_FEATURE_INTEGRATION.md#catatan-implementasi-tahap-7). Yang perlu diketahui:

- **Ekspor XLSX** (`/admin/ekspor`): sepuluh sheet, ditulis bertahap oleh `App\Libraries\ExcelWriter` (XML SpreadsheetML + ZipArchive) sehingga 150.000 baris Raw Events hanya memakai ±18 MB memori. Berkas di `writable/exports/`, ber-SHA-256, kedaluwarsa setelah `gelita.exportRetentionDays`. Raw Events di atas `gelita.exportMaxRawEvents` (200.000) ditolak dengan permintaan mempersempit rentang.
- **Hak dibaca ulang di service.** `ExportService` mengambil role dan sekolah pemohon dari `staff_users`: guru selalu anonim, hanya sekolahnya, tanpa Raw Events dan tanpa kunci jawaban — apa pun isi formulirnya. Mode anonim tidak membuat kolom nama/nama pengguna/nama sekolah sama sekali; `school_ref` (`SCH-000012`) tetap ada untuk analisis per sekolah.
- **Laporan PDF** (mPDF): ringkasan studi dari halaman ekspor, dan laporan satu peserta dari `/admin/peserta/{id}`.
- **Penghapusan** (`/admin/tata-kelola`) dipindah ke `RetentionService`: cakupan peserta, sesi, atau studi (dipersempit fase & rentang tanggal); eksekusi dibatalkan bila jumlah baris berubah jauh sejak pratinjau.
- **Retensi** (`php spark gelita:retention:run`, cron harian): sesi menganggur → `paused`, attempt menggantung > 24 jam → `abandoned`, sesi `paused` > 30 hari → `abandoned`, berkas ekspor kedaluwarsa dibuang, dan data yang melewati `retention_days` **hanya** dibuatkan pratinjau penghapusan + peringatan di dasbor admin.
- **Command**: `gelita:content:verify`, `gelita:media:scan`, `gelita:score:recompute`, `gelita:bank:import`, `gelita:retention:run`, `gelita:staff:password` — semuanya mengembalikan kode keluar 1 saat gagal sehingga dapat dipakai di skrip deploy.

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
- **Aset di-host sendiri**: ECharts 6.1.0, Howler.js 2.2.4, dan font Cinzel/Plus Jakarta Sans/IBM Plex Mono ada di repositori; tidak ada permintaan ke domain pihak ketiga. Selama gambar belum diunggah, view memakai pengganti (gradien, monogram). Satu-satunya pengecualian adalah media **tautan** di Pustaka Kedu: gambar Commons/Drive dimuat dari domain asalnya tanpa referrer, sedangkan pemutar YouTube/Vimeo/Drive baru dimuat setelah siswa menekan Putar. Media yang diunggah ke server tetap lokal.
- **Otorisasi berlapis tiga** (route → controller → service): guru hanya melihat sekolahnya, ekspor guru selalu anonim, dan halaman khusus admin membalas `404` untuk guru.
- **HEAD dilayani rute GET** dengan handler dan filter yang sama (`App\Libraries\HeadAsGetRouteCollection`), sehingga `curl -I` dan pemantau uptime tidak lagi mendapat 404, dan HEAD tidak pernah melewati filter login.

### Batas ruang lingkup saat ini

- **Narasi petunjuk arena `cari` belum bersuara**: payload `clues` belum membawa aset audio; teks petunjuk tampil dan diumumkan ke pembaca layar.
- **Ekspor dibangun sinkron** di request yang sama (batas 300 detik). Tahap 8 memutuskan tetap sinkron; ambang `gelita.exportMaxRawEvents` menjaga ukurannya. Alasannya ada di [`docs/08_DEPLOYMENT.md` → *Keputusan yang tercatat*](docs/08_DEPLOYMENT.md#keputusan-yang-tercatat).
- **Tugas terjadwal dipasang per server.** Baris cron (Linux) dan perintah pendaftaran Task Scheduler (Windows) untuk backup dan `gelita:retention:run` ada di [`docs/08_DEPLOYMENT.md` → *Tugas Terjadwal*](docs/08_DEPLOYMENT.md#tugas-terjadwal). Di mesin pengembangan, retensi dijalankan dari tombol di `/admin/tata-kelola`.
- `App\Libraries\HashedIpSessionHandler` **belum dipasang**; alasannya di bagian *Sesi dan CSRF* di bawah.

## Prasyarat lokal

- PHP 8.2 atau lebih baru (PHP 8.3 disarankan)
- Composer 2.6 atau lebih baru
- MySQL 8.0+ atau MariaDB 10.6+
- Web server lokal, atau server bawaan CodeIgniter untuk pengembangan
- Ekstensi PHP: `intl`, `mbstring`, `json`, `mysqli`/`mysqlnd`, `curl`, `gd`, `zip`, `fileinfo`, `openssl`, `iconv`, `dom`, `xml`, `xmlwriter`, dan `simplexml`

Di Windows, Laragon (PHP 8.3 + MySQL) memenuhi semua prasyarat. Pengaturan ekstensinya ada di [`docs/08_DEPLOYMENT.md` → Jalur W](docs/08_DEPLOYMENT.md#ekstensi-php-wajib). Perintah di bawah ditulis untuk Bash; padanan PowerShell diberikan bila sintaksnya berbeda.

## Menjalankan secara lokal

1. Pasang dependensi dari lockfile proyek:

   ```bash
   composer install
   ```

2. Salin templat environment, lalu sesuaikan isinya:

   ```bash
   cp env .env                 # PowerShell: Copy-Item env .env
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

   Di PowerShell, sintaks `VAR='…' perintah` tidak ada:

   ```powershell
   $env:GELITA_ADMIN_PASSWORD = 'SandiKuatAnda123!'; php spark db:seed DatabaseSeeder; Remove-Item Env:\GELITA_ADMIN_PASSWORD
   ```

   Tabel sesi `ci_sessions` sudah termasuk migration proyek — jangan menjalankan `php spark session:migration`. Pada `CI_ENVIRONMENT = development`, `DatabaseSeeder` juga menjalankan `SampleItemSeeder` (2 butir contoh per node) agar kelima belas tantangan dapat dimainkan sebelum bank soal diimpor.

4. Jalankan server pengembangan:

   ```bash
   php spark serve
   ```

   Akun staf awal: `admin` dengan sandi dari `GELITA_ADMIN_PASSWORD`, di `/admin/login`. Siswa mendaftar sendiri lewat `/mulai`.

## Migration

Migration dijalankan berurutan dari `000100` sampai `003500` (36 berkas) dan menghasilkan 32 tabel: 30 tabel domain, `ci_sessions`, dan `migrations`. `php spark migrate:rollback -b 0` mengembalikan database ke kosong.

`003100`–`003300` adalah koreksi, `003400` menambah kolom baru, `003500` menambah tabel baru. Perubahan skema selalu datang sebagai migration baru; migration lama tidak diubah.

| Versi | Peran |
|---|---|
| `002100` | membuat tabel `challenge_attempts` dengan nama kolom awal `first_pass_rate` / `final_rate` |
| `003100` | koreksi kanonik: mengganti nama kedua kolom itu menjadi `first_pass_accuracy` / `final_accuracy` dan menambahkan `check_count`, `answer_change_count`, `audio_use_count` |
| `003200` | menegakkan `NOT NULL` + default pada kelima kolom di atas (Forge membuatnya nullable saat `ALTER`), termasuk mengisi baris lama yang masih `NULL` |
| `003300` | menambahkan index wajib `game_event_logs (session_id, occurred_at)` yang tidak dibuat `002400` |
| `003400` | menambahkan `staff_users.must_change_password`: sandi sementara dari admin (reset / akun baru) wajib diganti sebelum panel terbuka |
| `003500` | membuat `library_media` (banyak gambar/video per halaman Pustaka Kedu, dari unggahan atau tautan YouTube/Drive/Vimeo/Commons) dan menyalin isi kolom lama `image_a`/`image_b`/`video` ke sana |

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

Suite (161 test) memakai grup database `tests` (SQLite3 in-memory) dan hanya menjalankan migration bernamespace `Tests\Support`, bukan migration aplikasi — skema aplikasi memakai fitur MySQL/MariaDB (`DATETIME(6)`, `ON UPDATE CURRENT_TIMESTAMP(6)`) yang tidak ada di SQLite. Sesi di-mock dengan `ArrayHandler` oleh `CIUnitTestCase`. Yang dikunci suite antara lain:

- `RouteWiringTest` — auto-route tetap mati, setiap handler menunjuk kelas/method yang ada, setiap view yang disebut controller punya berkasnya, dan ganti sandi staf hanya berfilter `staffAuth` (terbuka untuk guru).
- `HeadRouteTest` — HEAD memakai rute GET beserta filternya; HEAD ke halaman staf tanpa login dialihkan ke `/admin/login`.
- `StaffPasswordGateTest` — staf bersandi sementara hanya dapat membuka Ubah sandi: halaman lain dialihkan, API membalas 403 `PASSWORD_CHANGE_REQUIRED`.
- `HuntPayloadTest` — payload arena `cari` tidak membedakan jebakan, jawaban hanya lewat token objek, dan objek jebakan tidak pernah diminta dijawab.
- `RegionEntryTest`, `DialogueGateTest` — wilayah baru selalu lewat dialog pembukanya.
- `JsConfigTest` — penanda sesi antrean offline buram dan per sesi, `#app-config` tanpa identitas siswa, dan setiap `t('…')` di JavaScript punya kunci `Js.*`.
- `ChartDataTest` — bentuk data chart admin dan kondisi kosongnya.
- `ExcelWriterTest` — workbook terbaca ulang PhpSpreadsheet, teks berawalan `=` tidak pernah menjadi rumus, berkas sementara dibersihkan.
- `BankWorkbookGuideTest` — setiap kolom yang dibaca importer punya penjelasan Indonesia di templat, header sheet data sama persis dengan importer, dan workbook produksi `docs/bank-soal/` ikut dibangun ulang bila kolom impor berubah.
- `MediaLinkTest`, `RichTextTest` — tautan media Pustaka hanya http(s) dan YouTube lewat domain nocookie; format teks Pustaka selalu meng-escape HTML lebih dulu.
- `ExportRulesTest` — mode anonim tanpa kolom identitas, rahasia tidak pernah diekspor, kunci jawaban hanya admin, guru tanpa Raw Events, cakupan sekolah dipaksa service, cakupan & penjaga drift penghapusan.
- `Stage7WiringTest` — kelima command `gelita:*` aktif, service baru terdaftar, dan setiap keluaran templat PDF lewat `esc()`.
- `StaffPasswordCommandTest` — `gelita:staff:password` terdaftar, menolak tanpa nama pengguna, dan memakai jalur reset yang sama dengan `/admin/staf`; sandi sementaranya lolos `PasswordPolicy`.
- `DeployFilesTest` — skrip Windows hanya ASCII, skrip Bash ber-LF dengan `set -E`, konfigurasi Nginx Linux dan Windows hanya berbeda di baris platform, tidak ada perintah yang merusak production, salinan `.env` di backup Windows tidak langsung terhapus retensi.
- `ScoringServiceTest`, `PasswordPolicyTest`, `ChallengeEntityTest`, `ViewHelperTest`, `LanguageFilesTest`, `EventTimeTest`, `DebugToolbarRedactionTest`.

Karena tabel aplikasi tidak dibuat di suite ini, **alur HTTP ujung ke ujung diverifikasi di atas MySQL/MariaDB sungguhan**. Gunakan database scratch terpisah. Kredensial untuk perintah CLI dioper lewat `database_default_DSN`, satu-satunya cara yang dapat menimpa `.env`:

```bash
database_default_DSN='MySQLi://user:sandi-url-encoded@localhost/gelita_scratch' php spark migrate
```

`env "database.default.database=…"` atau `database_default_username=…` **tidak** berlaku bila kunci itu sudah ada di `.env`, karena nilai `.env` dibaca lebih dulu. DSN ditulis tanpa port: CodeIgniter 4.7.4 meneruskan port dari DSN sebagai string, dan `mysqli` menolaknya. Rinciannya di [`docs/08_DEPLOYMENT.md` → *Kredensial migration lewat DSN*](docs/08_DEPLOYMENT.md#kredensial-migration-lewat-dsn).

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
8. [Deployment dan operasional](docs/08_DEPLOYMENT.md) — Linux dan Windows + Laragon (Nginx); skripnya di [`deploy/`](deploy/)

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
