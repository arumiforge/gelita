# 02_PROJECT_FOUNDATION.md — Fondasi Proyek

Dokumen ini menggambarkan fondasi teknis GELITA seperti yang ada di repositori sekarang: tujuan dan cakupan, tumpukan teknologi, struktur proyek, konfigurasi, service, helper, filter, konvensi, pengujian, dan alat pendukung.

---

## 1. Tujuan dan cakupan

GELITA (*Game Edukasi Literasi dan Etnopedagogi Kedu*) adalah permainan edukasi berbasis web untuk penelitian literasi membaca siswa **kelas 5 SD/MI**. Siswa menjelajahi tiga wilayah Kedu secara berurutan: Temanggung (mudah), Magelang (sedang), dan Wonosobo (sulit). Setiap wilayah berisi empat tantangan berurutan:

| Urutan | Tantangan | Engine |
|---:|---|---|
| 1 | Susun Gambar | `puzzle` |
| 2 | Isi Rumpang | `rumpang` |
| 3 | Benar atau Salah | `boleh` |
| 4 | Cari Objek | `cari` |

Dua belas tantangan memberi dua belas **Serpihan Cahaya**. Permainan juga memuat cerita bertokoh Jaka dan Mbah Kedu serta **Pustaka Kedu**, buku bacaan per wilayah. Semua teks pemain tersedia dalam bahasa Indonesia dan Inggris.

Sistem mencatat **data mentah** penelitian: percobaan per soal (`item_tries`), event permainan (`game_event_logs`), dan perangkat (`device_logs`). Sistem tidak menghitung skor atau indeks. Bintang hanya hadiah di layar siswa, dan pengolahan data dilakukan peneliti dari berkas ekspor.

Aplikasi punya tiga area:

| Area | Prefix URL | Pengguna | Tampilan |
|---|---|---|---|
| **Game** | `/` | siswa (daftar, lalu masuk dengan nama pengguna dan kata sandi) | `layouts/game.php`, dwibahasa |
| **Admin** | `/admin` | guru dan admin | `layouts/admin.php` (halaman masuk: `layouts/auth.php`), hanya bahasa Indonesia |
| **API** | `/api` (permainan), `/api/admin` (panel) | JavaScript halaman | JSON, tanpa view |

Dokumen terkait:

| Dokumen | Isi |
|---|---|
| [01_DATABASE.md](01_DATABASE.md) | skema, migration, seeder |
| [03_MODEL_ENTITY.md](03_MODEL_ENTITY.md) | model, entity, service |
| [04_CONTROLLER_ROUTE.md](04_CONTROLLER_ROUTE.md) | route, controller, kontrak API |
| [05_VIEW_UI.md](05_VIEW_UI.md) | view, komponen, CSS |
| [06_JAVASCRIPT.md](06_JAVASCRIPT.md) | modul JavaScript dan engine tantangan |
| [07_FEATURE_INTEGRATION.md](07_FEATURE_INTEGRATION.md) | alur fitur ujung ke ujung |
| [08_DEPLOYMENT.md](08_DEPLOYMENT.md) | instalasi server, backup, pemeliharaan |
| [perombakan-kelas5/](perombakan-kelas5/) | rencana perombakan kelas 5 dan catatan implementasinya |

---

## 2. Tumpukan teknologi

| Komponen | Versi | Catatan |
|---|---|---|
| PHP | 8.2 atau 8.3 (`"php": "^8.2"`) | pengembangan memakai 8.3 |
| CodeIgniter | 4.7.x, terkunci 4.7.4 di `composer.lock` | dipasang Composer ke `vendor/codeigniter4/framework`; `Config\Paths::$systemDirectory` menunjuk ke sana |
| Database | MySQL 8.0+ atau MariaDB 10.6+ | pengembangan memakai MariaDB 10.11; uji memakai SQLite3 di memori |
| Web server | Nginx (contoh konfigurasi di `deploy/`) atau Apache 2.4 + mod_rewrite (`public/.htaccess`) | `php spark serve` untuk pengembangan |
| Frontend | CodeIgniter View + HTML5 + CSS + JavaScript ES modules | tanpa SPA framework, tanpa langkah build, tanpa CDN |

Halaman dirender server. JavaScript hanya menambah perilaku, misalnya engine tantangan, pengiriman event, dan narasi. AJAX dipakai untuk interaksi permainan dan panel, bukan untuk merender seluruh halaman.

### Paket Composer

Daftar paket produksi dibatasi isi `composer.json`. Alasan setiap paket ditulis di `extra.gelita-dependency-notes`, dan paket baru wajib diberi alasan di sana.

| Paket | Dipakai untuk |
|---|---|
| `codeigniter4/framework` | framework aplikasi |
| `phpoffice/phpspreadsheet` | membaca workbook impor (`ContentImportService`) dan menulis workbook berpanduan (`BankWorkbookGuide`: templat impor, `docs/bank-soal/` dan `docs/pustaka/`). Uji memakainya untuk membaca ulang berkas ekspor |
| `mpdf/mpdf` | laporan PDF satu siswa (`ReportService`, view `pdf/`) |
| `matomo/device-detector` | mengurai User-Agent dan Client Hints menjadi jenis, merek, model, OS, dan peramban perangkat (`DeviceProfile`) |

Ekspor data penelitian ke XLSX tidak memakai PhpSpreadsheet. `App\Libraries\ExcelWriter` menulis setiap sheet sebagai XML bertahap ke berkas sementara lalu merangkainya dengan `ZipArchive`, sehingga ratusan ribu baris event tidak pernah dimuat utuh di memori.

Paket pengembangan: `phpunit/phpunit` (uji), serta `fakerphp/faker` dan `mikey179/vfsstream` bawaan kerangka CodeIgniter.

Yang sengaja tidak dipakai:

* `codeigniter4/shield`: login staf dengan dua role (`admin`, `guru`) dan login siswa cukup ditangani kode sendiri.
* Library UUID: `uuid4()` di `gelita_helper` cukup.
* Library antrean/job: ekspor berjalan sinkron dengan penulis bertahap; pekerjaan berkala memakai `php spark` lewat cron atau Task Scheduler.
* Library grafik: panel admin hanya menampilkan tabel dan hitungan data mentah.

### Library frontend dan aset bawaan

| Berkas | Isi |
|---|---|
| `public/assets/vendor/howler.min.js` | Howler.js 2.2.4 untuk audio permainan, dimuat `layouts/game.php`. Versi, SHA-256, lisensi, dan cara memperbarui ada di [`public/assets/vendor/README.md`](../public/assets/vendor/README.md) |
| `public/assets/fonts/` | Cinzel, Plus Jakarta Sans, IBM Plex Mono (`.woff2` subset latin, lisensi OFL), dimuat lewat `@font-face` |
| `public/assets/data/wilayah-id.json` | daftar provinsi dan kabupaten/kota Kemendagri (`RegionDirectory`) |
| `public/assets/audio/` | musik, efek suara, dan rekaman narasi bawaan (lihat [§ 11](#11-alat-dan-sumber-pendukung)) |

### Aplikasi terpasang (PWA)

Area game dapat dipasang di layar utama perangkat:

| Berkas | Fungsi |
|---|---|
| `public/manifest.json` | nama, ikon (`public/assets/app/`), tampilan `fullscreen`, orientasi `landscape`; ditautkan `layouts/game.php` |
| `public/sw.js` | service worker yang hanya menangani navigasi GET: bila jaringan putus, `offline.html` ditampilkan. API, event, POST formulir, dan aset tidak disentuh, sehingga tidak ada data penelitian di cache |
| `public/offline.html` | halaman mandiri dwibahasa tanpa CSS/JS luar |
| `public/assets/js/game/install.js` | mendaftarkan service worker |

---

## 3. Instalasi singkat

GELITA hanya mengenal instalasi baru. Langkah lengkap untuk server sekolah (akun database, Nginx, HTTPS, izin folder, cron, backup) ada di [08_DEPLOYMENT.md](08_DEPLOYMENT.md). Ringkasan untuk mesin pengembangan:

```bash
composer install                       # server: composer install --no-dev
cp env .env                            # isi database, app.baseURL, gelita.ipSalt
php spark key:generate                 # mengisi encryption.key
php spark migrate                      # 29 migration
GELITA_ADMIN_PASSWORD='SandiKuatAnda123!' php spark db:seed DatabaseSeeder
php spark gelita:narration:import      # rekaman narasi bawaan sebagai draft
php spark gelita:content:verify
php spark serve
```

`DatabaseSeeder` memasang sekolah resmi, akun `admin`, pengaturan studi dan rilis, tiga wilayah, slot media, 12 tantangan dengan naskah cerita, Pustaka Kedu, bank soal kelas 5, dan gambar contoh (mockup) untuk 45 slot gambar tantangan. Sesudah impor narasi, setujui rekaman di panel **Rekaman narasi** (`/admin/konten/narasi`) untuk kedua bahasa. Gambar contoh harus diganti gambar asli di panel **Gambar & mockup** (`/admin/media`) sebelum pengambilan data; `php spark gelita:content:verify --siap-penelitian` gagal selama masih ada mockup, dan **daftar siap penelitian** di halaman **Penelitian** (`/admin/studi`) baru hijau sesudah gambar, narasi, dan verifikasi beres.

Untuk mengisi panel dengan data yang realistis di mesin pengembangan: `php spark gelita:simulasi --siswa=30` (§ 9).

---

## 4. Struktur proyek

```text
gelita/
├── app/
│   ├── Commands/            spark command gelita:* (+ Concerns/CliOptions.php)
│   ├── Config/              konfigurasi CodeIgniter + Gelita.php (domain GELITA)
│   ├── Controllers/
│   │   ├── BaseController.php
│   │   ├── Game/            area siswa (BaseGameController + 10 controller)
│   │   ├── Admin/           panel staf (BaseAdminController + 15 controller)
│   │   ├── Api/             endpoint JSON (BaseApiController + 9 controller)
│   │   └── Concerns/        trait GameProgress, StaffScope
│   ├── Database/
│   │   ├── GelitaMigration.php   pola bersama migration (di luar Migrations/)
│   │   ├── Migrations/      29 berkas, satu tabel per berkas
│   │   └── Seeds/           DatabaseSeeder dan seeder bagiannya; data/ (story.php, sekolah-jateng.csv)
│   ├── Entities/            entity + Traits/Bilingual.php
│   ├── Exceptions/          ChallengeRuleException.php
│   ├── Filters/             filter GELITA (lihat § 6.2)
│   ├── Helpers/             gelita_helper.php, content_helper.php, ui_helper.php
│   ├── Language/
│   │   ├── id/              Admin.php, Auth.php, Game.php, Js.php, Validation.php
│   │   └── en/              Auth.php, Game.php, Js.php, Validation.php
│   ├── Libraries/           kelas pendukung (lihat § 6.5); Simulation/ untuk gelita:simulasi
│   ├── Models/              model + Traits/EncodesJson.php
│   ├── Services/            logika bisnis (lihat § 6.3)
│   ├── Validation/          GelitaRules.php
│   └── Views/
│       ├── layouts/         game.php, admin.php, auth.php
│       ├── components/      komponen yang dirender lewat component()
│       ├── partials/        potongan view bersama
│       ├── game/            halaman siswa (challenge/, register/)
│       ├── admin/           halaman panel per menu
│       ├── pdf/             laporan PDF satu siswa (report-participant.php, _style.php)
│       └── errors/          halaman galat bergaya GELITA
├── public/                  web root (index.php, .htaccess, manifest.json, sw.js, offline.html)
│   └── assets/              css/, js/, vendor/, fonts/, data/, audio/, app/, ui/, uploads/, …
├── writable/                cache/, logs/, session/, uploads/, debugbar/, exports/
├── tests/                   unit/, database/, session/, _support/
├── tools/audio/             generator audio bawaan (tidak dipakai server)
├── tools/qa/                skrip uji peramban Playwright (tidak dipakai server)
├── deploy/                  skrip dan konfigurasi Nginx untuk Linux dan Windows
├── docs/                    dokumentasi, bank-soal/, pustaka/, sekolah/, perombakan-kelas5/
├── env                      templat konfigurasi, dilacak Git
├── .env                     salinan lokal hasil `cp env .env`, tidak pernah di-commit
├── composer.json            paket dan alasannya
├── phpunit.dist.xml         konfigurasi uji
└── spark                    CLI CodeIgniter
```

### `public/assets/`

| Folder | Isi |
|---|---|
| `css/` | game: `tokens.css`, `base.css`, `stage.css` (panggung 16:9: bingkai, pita HUD/isi/nav bar, layar putar, dan semua koordinat layar), `components.css`, `game.css`, `cinematic.css`, `noscript.css`; panel: `admin.css` (berdiri sendiri: font, token, reset, dan semua komponen panel, tanpa CSS game) |
| `js/` | entri `game.js` dan `admin.js`; modul `core/`, `game/`, `engines/` (`puzzle.js`, `rumpang.js`, `boleh.js`, `cari.js`), `admin/` |
| `vendor/` | library frontend yang di-host sendiri (lihat di bawah) |
| `fonts/` | font lokal dan lisensinya |
| `data/` | `wilayah-id.json` |
| `audio/` | `music/`, `sfx/`, `narasi/{id,en}/` + `narasi/produksi.json` |
| `app/` | ikon aplikasi terpasang |
| `ui/` | `placeholder.svg` (pengganti media yang belum ada) |
| `bg/`, `char/`, `map/`, `challenge/`, `library/`, `reward/`, `images/` | folder aset bawaan per jenis (saat ini kosong, hanya `.gitkeep`) |
| `uploads/` | berkas yang diunggah lewat panel dan gambar contoh (`uploads/mockup/`). Hanya ada di server dan ikut backup, tidak di Git |

Aturan folder aset:

* Berkas unggahan disimpan `MediaStore` dengan nama resmi turunan `media_assets.asset_key`, bukan nama asli berkas pengguna.
* Tidak ada CDN. Semua CSS, JS, dan font dimuat dari domain sendiri.
* `writable/exports/` **tidak** berada di bawah `public/`. Unduhan dilayani controller yang memeriksa otorisasi lebih dulu.

#### `public/assets/vendor/`

Berisi `howler.min.js` (Howler.js 2.2.4), lisensinya `LICENSE-howler.md`, dan [`README.md`](../public/assets/vendor/README.md) dengan versi, SHA-256, dan langkah memperbarui. Library diambil dari paket rilis resmi dan di-commit apa adanya.

`.gitignore` sengaja memakai pola `/vendor/` (khusus folder Composer di root). Pola `vendor/` tanpa garis miring depan juga cocok dengan `public/assets/vendor/` dan membuat berkasnya tidak ikut ter-commit.

### `writable/`

| Folder | Isi |
|---|---|
| `cache/` | cache berkas CodeIgniter (`Config\Cache`, handler `file`) dan folder sementara mPDF (`cache/mpdf`) |
| `logs/` | log aplikasi |
| `uploads/` | workbook yang diunggah untuk impor bank soal dan templat impor sementara |
| `exports/` | berkas ekspor XLSX, CSV-ZIP (`.zip`), dan PDF serta daftar rekaman narasi; dihapus `gelita:retention:run` setelah `Config\Gelita::$exportRetentionDays` |
| `debugbar/` | riwayat Debug Toolbar (hanya development) |
| `session/` | tidak dipakai; sesi disimpan di tabel `ci_sessions` |

`writable/pemeliharaan.flag` adalah penanda mode pemeliharaan yang dibuat `gelita:reset --pemeliharaan` dan skrip `deploy/`, lalu dibaca konfigurasi Nginx (semua permintaan dijawab 503 selama berkas itu ada).

---

## 5. Konfigurasi

### 5.1 `.env`

Templat seluruh kunci ada di berkas `env` di root proyek dan dilacak Git. Salin dengan `cp env .env`, lalu isi nilainya. `.env` tidak pernah di-commit. Komentar di `env` menjelaskan setiap kelompok kunci.

| Kelompok | Kunci penting | Catatan |
|---|---|---|
| Environment | `CI_ENVIRONMENT` | `development` untuk kerja lokal, `production` untuk server sekolah |
| App | `app.baseURL`, `app.forceGlobalSecureRequests`, `app.appTimezone`, `app.defaultLocale`, `app.indexPage` | `app.supportedLocales` bernilai array sehingga tidak dapat diisi lewat `.env`; nilainya `['id', 'en']` di `app/Config/App.php` |
| Database | `database.default.*` | driver `MySQLi`, `utf8mb4` / `utf8mb4_unicode_ci`. Server dapat memakai akun migrasi terpisah lewat `database.default.DSN` (lihat 08_DEPLOYMENT.md) |
| Encryption | `encryption.key` | dibangkitkan `php spark key:generate` |
| Session | `session.driver`, `session.savePath`, `session.cookieName`, `session.expiration`, `session.regenerateDestroy`, `session.matchIP` | lihat [§ 7.2](#72-sesi) |
| Security | `security.csrfProtection`, `security.tokenRandomize`, `security.tokenName`, `security.headerName`, `security.cookieName`, `security.expires`, `security.regenerate`, `security.redirect` | lihat [§ 7.1](#71-csrf) |
| Cookie | `cookie.samesite`, `cookie.secure`, `cookie.httponly` | `cookie.secure = true` bila situs dilayani lewat HTTPS |
| Logger | `logger.threshold` | production 4, development 9 |
| Debug Toolbar | `toolbar.collectVarData = false` | data view (profil siswa) tidak disimpan ke `writable/debugbar` |
| GELITA | `gelita.ipSalt`, `gelita.exportRetentionDays`, `gelita.maxEventsPerBatch`, `gelita.assetVersion` | `gelita.ipSalt` **wajib** diganti string acak panjang per pemasangan |

Properti skalar `Config\Gelita` lain juga dapat ditimpa dengan awalan `gelita.`. Properti array tidak dapat diisi lewat `.env`.

### 5.2 Berkas `app/Config/` yang disesuaikan

| Berkas | Pengaturan GELITA |
|---|---|
| `App.php` | `defaultLocale = 'id'`, `supportedLocales = ['id', 'en']`, `negotiateLocale = false`, `appTimezone = 'Asia/Jakarta'`, `CSPEnabled = false` |
| `Autoload.php` | helper global `$helpers = ['url', 'gelita', 'content', 'ui']` |
| `Database.php` | grup `default`: MySQLi, `utf8mb4_unicode_ci`, `numberNative = true`, `foreignKeys = true`. Grup `tests`: SQLite3 `:memory:`, dipakai otomatis saat `ENVIRONMENT === 'testing'` |
| `Session.php` | `DatabaseHandler` ke tabel `ci_sessions`, cookie `gelita_session`, `expiration = 14400`, `matchIP = false` |
| `Security.php` | CSRF berbasis session, token `gelita_csrf`, header `X-CSRF-TOKEN`, `regenerate = false` |
| `Cookie.php` | `httponly = true`, `samesite = 'Lax'` |
| `Cache.php` | handler `file`, prefix `gelita_`, `ttl = 300` |
| `Logger.php` | `threshold` 4 di production, 9 di lainnya; `dateFormat = 'Y-m-d H:i:s.u'` |
| `Toolbar.php` | `collectVarData = false` |
| `Filters.php` | alias dan pemasangan filter (lihat [§ 6.2](#62-filter)) |
| `Validation.php` | ruleset bawaan + `App\Validation\GelitaRules` |
| `Exceptions.php` | `handler()` mengembalikan `App\Libraries\GelitaExceptionHandler` |
| `Routing.php` / `Routes.php` | `autoRoute = false`; setiap URL ditulis di `Routes.php` |
| `Services.php` | service bersama (lihat [§ 6.3](#63-service)) |
| `Events.php` | `pre_system` memanggil `db_sync_timezone()` (lihat [§ 5.4](#54-zona-waktu)) |
| `Paths.php` | `systemDirectory` = `vendor/codeigniter4/framework/system` |
| `Gelita.php` | konstanta domain GELITA (§ 5.3) |

Cache hanya dipakai untuk konten yang jarang berubah (`ContentRepository`: wilayah, tantangan, soal, peta media). Kunci cache menyertakan `content_version` rilis aktif. Data penelitian tidak pernah di-cache.

### 5.3 `app/Config/Gelita.php`

Satu-satunya tempat konstanta domain yang tidak perlu masuk database.

**Permainan dan konten**

| Properti | Nilai | Dipakai untuk |
|---|---|---|
| `$engineTypes` | `['puzzle', 'rumpang', 'boleh', 'cari']` | engine tantangan; urutannya = urutan tantangan di setiap wilayah |
| `$interactionTypes` | `['puzzle_arrange', 'fill_blank_bank', 'verdict_card', 'find_object']` | jenis interaksi soal |
| `$maxTries` | `3` | batas percobaan per soal; disalin ke `challenge_attempts.max_tries` dan dikirim ke JavaScript lewat `js_config()` |
| `$levelPrefixes` | `['tmg' => 'temanggung', 'mgl' => 'magelang', 'wnb' => 'wonosobo']` | awalan kode tantangan/soal (`tmg-1` … `wnb-4`); dipakai impor workbook, editor konten, kunci aset, dan `node_ref()` |
| `$locales` | `['id', 'en']` | bahasa konten permainan |
| `$characters` | `['jaka', 'mbah_kedu']` | tokoh bersuara dan bergambar |
| `$characterPoses` | pose per tokoh (`narator` tanpa pose) | nilai sah `dialogues.pose` |
| `$dialogueEffects` | `['fog', 'fog-lift', 'glow', 'flash', 'shake', 'dim']` | nilai sah `dialogues.effect` |
| `$dialogueContexts` | `global`: `intro`, `map_intro`, `ending`; `level`: `region_intro`, `level_open`, `level_done` | nilai sah `dialogues.context_code` |
| `$characterAnimations` | `jaka.*` dan `kedu.*`, masing-masing 3 frame | animasi tokoh dari frame `char.{jaka\|kedu}.{pose}.{n}` di `media_assets` |
| `$assetSizes` | ukuran piksel wajib per pola `asset_key` | validasi unggahan (`MediaStore`), daftar kelengkapan aset, dan ukuran gambar contoh |
| `$assetDisplaySizes` | ukuran tampil per pola `asset_key`, dalam dp pada panggung 1280 × 720 (benda Cari Objek `[0, 0]`: lebarnya persen adegan) | keterangan di halaman **Gambar & mockup**; tidak dipakai validasi |
| `$maxUploadBytes` | 64 MB | batas unggahan media |

Aturan per tingkat kesulitan (ukuran papan Susun Gambar, jumlah rumpang dan pengecoh, rentang Bloom) tidak ada di sini, tetapi di `App\Libraries\DifficultyRules`, karena dibaca juga oleh `docs/bank-soal/build-workbook.php` yang berjalan tanpa framework.

**Pendaftaran dan akun**

| Properti | Nilai | Dipakai untuk |
|---|---|---|
| `$registrationClass` | `'5'` | pendaftaran hanya menerima kelas 5 |
| `$registrationAge` | `[8, 15]` | rentang umur yang diterima (inklusif) |
| `$genders` | `['laki-laki', 'perempuan']` | nilai jenis kelamin yang diterima |
| `$schoolDirectoryProvinces` | `['33']` | provinsi (Jawa Tengah) yang wajib mengisi NPSN dari tabel `schools` |
| `$registrationSchoolStages` | `['sd', 'slb']` | jenjang sekolah ber-NPSN yang boleh dipakai mendaftar |
| `$reservedUsernames` | `admin`, `guru`, `gelita`, `root`, `system`, `test` | nama pengguna siswa yang ditolak |
| `$passwordPolicy` | 8–64 karakter, huruf besar, huruf kecil, angka, simbol, tidak memuat nama pengguna; level `weak`/`medium`/`strong`; hanya `strong` diterima | satu-satunya sumber aturan sandi siswa (§ 7.4) |
| `$loginThrottle` | siswa 8 gagal → kunci 5 menit; staf 5 gagal → kunci 15 menit | pembatasan percobaan masuk |

**Data penelitian, retensi, dan ekspor**

| Properti | Nilai | Dipakai untuk |
|---|---|---|
| `$eventTypes` | daftar event sesi/navigasi, tantangan, audio, dan lain-lain | satu-satunya daftar event yang boleh ditulis ke `game_event_logs` (aturan validasi `valid_event_type`) |
| `$clientHints` | `Sec-CH-UA-Model`, `Sec-CH-UA-Platform-Version`, `Sec-CH-UA-Full-Version-List`, `Sec-CH-UA-Arch`, `Sec-CH-UA-Bitness` | header `Accept-CH` dari `ClientHintsFilter` |
| `$maxEventsPerBatch` | `50` | batas event per kiriman API |
| `$sessionIdleMinutes` | `45` | sesi permainan menganggur lewat batas ini berstatus `paused` |
| `$attemptAbandonHours` | `24` | pengerjaan `in_progress` yang tak tersentuh menjadi `abandoned` (`gelita:retention:run`) |
| `$sessionAbandonDays` | `30` | sesi `paused` yang tak tersentuh menjadi `abandoned` (`gelita:retention:run`) |
| `$exportRetentionDays` | `7` | masa simpan berkas ekspor (`data_exports.expires_at`) |
| `$exportMaxRawEvents` | `200000` | batas perkiraan baris sheet event mentah; di atasnya ekspor ditolak dan rentang tanggal harus dipersempit |
| `$deletionDriftMinRows`, `$deletionDriftFraction` | `10`, `0.10` | penghapusan data dibatalkan bila jumlah baris saat eksekusi menyimpang dari pratinjau lebih dari `max(minimum, persen × pratinjau)` |
| `$ipSalt` | `''` (isi lewat `gelita.ipSalt`) | garam `hash_ip()` |
| `$assetVersion` | `'1'` | penanda `?v=` untuk cache-busting (`asset_url_versioned()`); naikkan setelah CSS/JS berubah |

### 5.4 Zona waktu

Zona waktu aplikasi `Asia/Jakarta` (`Config\App::$appTimezone`). Kolom waktu `DATETIME(6)` menyimpan waktu setempat (WIB), bukan UTC, karena penelitian berlangsung di satu zona waktu dan guru membaca stempel waktu apa adanya.

Agar `DEFAULT CURRENT_TIMESTAMP(6)` di database sezona dengan waktu yang ditulis PHP, helper `db_sync_timezone()` menjalankan `SET time_zone = '+07:00'` (offset dihitung dari `appTimezone`) pada koneksi:

* request web: lewat event `pre_system` di `app/Config/Events.php` (tidak di environment `testing`);
* spark command: event `pre_system` tidak terpicu pada `php spark`, jadi setiap command GELITA yang menulis waktu memanggil `db_sync_timezone()` sendiri di awal `run()`.

### 5.5 Bahasa

* Bahasa bawaan `id`, bahasa kedua `en`. `negotiateLocale = false`: bahasa ditentukan pilihan eksplisit siswa (tombol ID/EN), bukan ditebak dari header peramban, sehingga bahasa yang dipakai selalu tercatat.
* `LocaleFilter` (global) menentukan bahasa setiap request dengan urutan: `?lang=id|en` (disimpan ke session, lalu dialihkan tanpa query) → `session('locale')` → `game_sessions.locale` sesi aktif → `id`.
* Panel admin (`/admin`, `/api/admin`) selalu berbahasa Indonesia.
* Teks antarmuka memakai berkas bahasa (§ 6.7). Konten permainan disimpan berpasangan di kolom `*_id` dan `*_en` lalu dibaca dengan `tr()`; bila kolom `*_en` kosong, teks Indonesia dipakai.

---

## 6. Komponen bersama

### 6.1 Controller dasar

| Kelas | Isi |
|---|---|
| `App\Controllers\BaseController` | helper `url`, `form`, `text`, `gelita`, `content`, `ui`; `$this->locale` dari request; `ok()` dan `fail()` untuk respons JSON seragam; `jsonBody()` (JSON rusak → array kosong); `deviceInfo()` untuk profil perangkat (`DeviceProfile`) |
| `Game\BaseGameController` | akses sesi dan peserta request ini serta data HUD; route grup `gameSession` dijamin sudah memuat `GameContext` |
| `Admin\BaseAdminController` | dasar controller panel: `panel()` merender halaman dengan menu `AdminMenu` dan breadcrumb; `schoolScope()`, `readFilters()`, dan `dataScope()` dari trait `StaffScope`; `rawService()`, `rawPage()`, `rawCsv()` untuk tabel Data mentah; `csvResponse()` untuk Unduh CSV per tabel |
| `Api\BaseApiController` | dasar endpoint JSON permainan: sesi sah → pengerjaan/tantangan milik sesi itu → soal milik pengerjaan itu |

Bentuk respons JSON selalu sama:

```json
{ "success": true,  "data": { … }, "request_id": "…" }
{ "success": false, "code": "INVALID_SESSION", "message": "…", "request_id": "…" }
```

Filter di luar controller memakai helper `api_error()` dengan bentuk yang sama.

### 6.2 Filter

| Alias | Kelas | Tugas |
|---|---|---|
| `locale` | `App\Filters\LocaleFilter` | global *before*: menentukan bahasa request (§ 5.5) |
| `secureheaders` | `CodeIgniter\Filters\SecureHeaders` | global *after*: `X-Content-Type-Options`, `X-Frame-Options: SAMEORIGIN`, `Referrer-Policy: same-origin`, dan header bawaan lainnya |
| `toolbar` | `App\Filters\DebugToolbar` | Debug Toolbar bawaan (wajib, `$required`) yang menyamarkan field kredensial sebelum isi request disimpan (§ 7.4) |
| `csrf` | `CodeIgniter\Filters\CSRF` | semua POST, PUT, PATCH, DELETE (`$methods`), termasuk `/api` |
| `clientHints` | `App\Filters\ClientHintsFilter` | *after* pada `/`, `mulai`, `masuk`, `daftar`, `daftar/*`: mengirim `Accept-CH` sesuai `Config\Gelita::$clientHints` |
| `participantAuth` | `App\Filters\ParticipantAuthFilter` | siswa sudah masuk dan akunnya masih ada; dipakai `/ganti-sandi` dan `/keluar` |
| `gameSession` | `App\Filters\GameSessionFilter` | siswa masuk, sandi tidak wajib diganti, dan sesi permainan aktif (sesi `completed` tetap lolos); lalu memuat `GameContext` |
| `apiSession` | `App\Filters\ApiSessionFilter` | sama dengan `gameSession` untuk `/api`, tetapi membalas JSON `401 INVALID_SESSION` atau `403 PASSWORD_CHANGE_REQUIRED` |
| `screenView` | `App\Filters\ScreenViewFilter` | mencatat event `screen_viewed` untuk setiap halaman game yang dirender (200 `text/html`) bagi siswa bersesi; pengalihan tidak dicatat |
| `staffAuth` | `App\Filters\StaffAuthFilter` | `/admin` dan `/api/admin`: staf masuk dan aktif; role dan sekolah disinkronkan dari database setiap request; sandi sementara wajib diganti dulu |
| `staffRole` | `App\Filters\StaffRoleFilter` | argumen role, mis. `staffRole:admin`; halaman tanpa hak → 404, API → `403 FORBIDDEN` |
| `jsonResponse` | `App\Filters\JsonResponseFilter` | *after* grup `/api` dan `/api/admin`: `Content-Type` JSON dan header `X-Request-Id` |

Ketiga filter siswa memakai trait `App\Filters\ParticipantCheck`, sehingga aturannya hanya ditulis sekali. Alias bawaan CodeIgniter lain (`honeypot`, `invalidchars`, `cors`, `forcehttps`, `pagecache`, `performance`) tetap terdaftar; `forcehttps`, `pagecache`, dan `performance` ada di `$required` seperti bawaan framework.

Pemasangan di `app/Config/Routes.php`:

| Grup route | Filter |
|---|---|
| `/mulai`, `/daftar/*`, `/masuk`, `/bahasa` | tanpa filter login |
| `/ganti-sandi`, `/keluar` | `participantAuth`, `screenView` |
| halaman permainan lain | `gameSession`, `screenView` |
| `/api/*` | `jsonResponse`; endpoint bersesi ditambah `apiSession` |
| `/admin/*` (kecuali masuk/keluar) | `staffAuth`; menu khusus admin ditambah `staffRole:admin` |
| `/api/admin/*` | `jsonResponse`, `staffAuth` |

### 6.3 Service

Service didaftarkan di `app/Config/Services.php`, sehingga controller cukup memanggil `service('…')`.

| Service | Kelas | Tugas |
|---|---|---|
| `gelitaRequestId` | `App\Libraries\RequestId` | id acak satu request; muncul di setiap respons JSON dan log |
| `routes` | `App\Libraries\HeadAsGetRouteCollection` | menimpa service bawaan: request HEAD dilayani rute GET beserta filternya (§ 7.6) |
| `gameContext` | `App\Services\GameContext` | sesi, progres, dan peserta request ini; tanpa aturan bisnis |
| `staffContext` | — (baris `staff_users` atau `null`) | staf yang sedang masuk, dibaca sekali per request |
| `contentRepository` | `App\Services\ContentRepository` | pembaca konten ber-cache |
| `sessionService` | `App\Services\SessionService` | akun siswa dan siklus hidup sesi permainan |
| `schoolDirectory` | `App\Services\SchoolDirectory` | sekolah resmi ber-NPSN, pencocokan nama ketikan, penggabungan |
| `challengeService` | `App\Services\ChallengeService` | membuka tantangan, memilih variasi acak, menerima jawaban, menegakkan aturan 3 kesempatan, menulis `item_tries`, menutup pengerjaan (`solved` / `tries_exhausted`) |
| `scoringService` | `App\Services\ScoringService` | satu-satunya tempat aturan bintang (`bintang-1`) |
| `eventService` | `App\Services\EventService` | penulis event mentah, idempoten terhadap `client_event_id` |
| `analyticsService` | `App\Services\AnalyticsService` | hitungan untuk Ringkasan dan profil siswa (`summary()`, `participantProfile()`); menerima cakupan sekolah pemanggil dan mode anonim |
| `rawDataService` | `App\Services\RawDataService` | satu query per dimensi data mentah (`peserta`, `sesi`, `perangkat`, … `butir`) untuk tabel `/admin/data/*`, tab detail siswa, dan ekspor; `service('rawDataService', $scope)` mengikat cakupan `App\Services\StaffScope`, tanpa cakupan menolak |
| `contentImportService` | `App\Services\ContentImportService` | memuat workbook bank soal dan Pustaka dalam satu transaction |
| `exportService` | `App\Services\ExportService` | berkas ekspor XLSX, CSV-ZIP, dan PDF dari `RawDataService` dengan Kamus Data, berlabel atau berkode; hak pemohon dibaca ulang dari `staff_users` |
| `reportService` | `App\Services\ReportService` | laporan PDF satu siswa (mPDF) |
| `retentionService` | `App\Services\RetentionService` | penghapusan data dua langkah dan retensi terjadwal |
| `videoThumbnail` | `App\Libraries\VideoThumbnail` | mengunduh thumbnail video Pustaka di sisi server |

`App\Services\SystemResetService` (dipakai `gelita:reset`) dibuat langsung oleh command-nya, tidak lewat container. `App\Services\StaffScope` adalah objek nilai hak baca (`admin()`, `teacher()`, `fromStaff()`), bukan service.

### 6.4 Helper

Helper `gelita`, `content`, dan `ui` dimuat global lewat `Config\Autoload::$helpers`. Setiap fungsi dibungkus `function_exists()`.

**`app/Helpers/gelita_helper.php`**

| Fungsi | Tugas |
|---|---|
| `uuid4()` | UUID versi 4 dari `random_bytes(16)` |
| `random_code(int $bytes = 16)` | heksadesimal acak (pengenal internal, bukan token login) |
| `participant_code(int $sequence)` | `1` → `GLT-000001` |
| `hash_ip(?string $ip)` | SHA-256 dari IP + `Config\Gelita::$ipSalt`; `null` untuk IP kosong |
| `clamp()`, `ms_to_human()` | pembatas angka; milidetik → `2:05` / `1:02:05` |
| `asset_url_versioned(string $path)` | `base_url('assets/…')` + `?v=assetVersion` |
| `session_tag(?int $gameSessionId)` | HMAC buram sesi untuk antrean event offline di peramban, agar event siswa A tidak terkirim ke sesi siswa B di komputer yang sama |
| `js_config(string $locale)` | isi `<script type="application/json" id="app-config">`: locale, token CSRF, alamat API, `sessionTag`, `maxTries`, ukuran panggung, dan teks `Js.*` |
| `component(string $name, array $data = [])` | merender komponen view hanya dengan data yang dioper (tidak bocor variabel halaman) |
| `seeded_shuffle()`, `seeded_derangement()` | acakan deterministik per pengerjaan (bank kata, keping Susun Gambar tanpa keping di tempatnya) memakai `Random\Randomizer` tersendiri |
| `apply_research_filters()` | filter penelitian baku pada query builder panel dan ekspor |
| `safe_internal_url()` | tujuan redirect dari input pengguna; hanya path internal |
| `is_api_path()`, `api_error()` | pengenal area `/api` dan respons gagal JSON seragam untuk filter |
| `db_sync_timezone()` | menyamakan `time_zone` koneksi database dengan `appTimezone` (§ 5.4) |

**`app/Helpers/content_helper.php`**

| Fungsi | Tugas |
|---|---|
| `tr($row, string $field, ?string $locale = null)` | kolom `{field}_{locale}`, jatuh ke `{field}_id` bila kosong |
| `node_ref(string $levelCode, int $sequence)` | `('magelang', 4)` → `mgl-4` |
| `rich_text(string $text)` | format ringan teks Pustaka (`## subjudul`, `- butir`, `> Tahukah kamu?`, `Sumber:`, `**tebal**`, `*miring*`); seluruh teks di-escape lebih dulu |
| `media_src()`, `media_exists()`, `media_first()` | URL media aktif menurut id, dengan pengganti `assets/ui/placeholder.svg` |
| `media_key_src()`, `media_key_src_locale()` | URL media menurut `asset_key`, termasuk varian bahasa (`ui.btn-start.en`); `null` bila belum diunggah |
| `character_frame_src()` | frame pertama tokoh dalam pose tertentu, cadangan pose `idle` |
| `media_catalog()`, `audio_catalog()` | daftar media dan audio untuk pemilih di panel |
| `audio_src(?int $audioAssetId)` | URL audio; `null` bila belum `approved` atau medianya nonaktif |
| `stars_html()` | bintang sebagai ikon + teks alternatif `2 / 3` |
| `first_pass_text()` | "Berhasil pada Percobaan Pertama" satu pengerjaan untuk layar siswa |

**`app/Helpers/ui_helper.php`**

| Fungsi | Tugas |
|---|---|
| `icon(string $name, string $class = '')` | ikon SVG inline bergaris (`currentColor`, `aria-hidden`) |
| `curtain_attrs()` | atribut tautan bertirai untuk `core/curtain.js` |
| `lang_or()` | `lang()` dengan teks cadangan bila kunci belum ada |
| `engine_label()`, `admin_label()`, `class_label()` | label jenis tantangan, label kode database di panel, dan kelas siswa |
| `fmt_num()`, `fmt_pct()`, `fmt_date()` | angka, persen, dan tanggal sesuai bahasa |

### 6.5 Library

`app/Libraries/` berisi kelas pendukung yang dipakai controller, service, command, dan skrip.

| Kelas | Tugas |
|---|---|
| `PasswordPolicy` | penilaian kekuatan sandi siswa (§ 7.4) |
| `RequestId`, `HeadAsGetRouteCollection`, `GelitaExceptionHandler` | id request, rute HEAD, dan penanganan galat (§ 6.8) |
| `HashedIpSessionHandler` | handler sesi yang menyimpan hash IP; belum dipasang (§ 7.3) |
| `DeviceProfile` | profil perangkat untuk `device_logs` dari User-Agent, Client Hints, dan sinyal formulir |
| `DifficultyRules` | aturan per tingkat kesulitan wilayah (papan Susun Gambar, rumpang dan pengecoh, pernyataan, benda, rentang Bloom) |
| `BankRules`, `BankSource`, `BankState` | pemeriksaan bank soal kelas 5 dengan satu aturan untuk skrip workbook, importer, dan verifikasi; sumber dari berkas data; bank yang tersimpan di database |
| `BankWorkbookGuide` | workbook berpanduan (sheet PETUNJUK dan KAMUS_KOLOM, catatan header, daftar pilihan) |
| `ClozeReading` | Isi Rumpang sebagai satu bacaan utuh |
| `PuzzleCut` | potongan keping Susun Gambar (kotak atau jigsaw) sebagai path SVG, tanpa membuat berkas baru |
| `ContentVerifier` | pemeriksaan kelengkapan konten untuk panel, `gelita:content:verify`, dan `gelita:reset` |
| `MediaStore` | penyimpanan berkas unggahan dengan nama resmi, pemeriksaan dimensi `getimagesize()` terhadap `$assetSizes` |
| `MediaIntegrity`, `MediaUsage`, `AssetChecklist` | integritas `media_assets` ↔ berkas, tempat setiap aset dipakai, dan daftar kelengkapan aset |
| `MediaLink`, `VideoThumbnail` | tautan media luar Pustaka (YouTube, Drive, Vimeo, Wikimedia Commons, berkas langsung) dan poster videonya |
| `MockupGenerator` | gambar contoh SVG untuk slot gambar tantangan (`media_assets.is_placeholder = 1`); gambar asli tidak pernah ditimpa |
| `NarrationCatalog`, `NarrationImporter` | status rekaman narasi dan impor rekaman dari `public/assets/audio/narasi/{id,en}/` |
| `ExcelWriter`, `ExportLabels`, `AnswerText` | penulis XLSX bertahap, label berbahasa Indonesia untuk berkas ekspor, dan jawaban dalam kalimat biasa untuk guru |
| `RegionDirectory`, `SchoolImporter`, `SchoolName` | daftar wilayah Kemendagri, impor sekolah resmi, dan kunci pencocokan nama sekolah |
| `AdminMenu` | satu sumber menu panel untuk sidebar dan breadcrumb topbar; menu khusus admin tidak dirender untuk guru |
| `AdminTable` | tabel panel server-side: urut per kolom, 25/50/100 baris, total, CSV mentah; bentuk hasil sama dengan `RawDataService::page()` (dirender `components/data-table`) |
| `ResearchReadiness` | daftar siap penelitian (0 gambar contoh aktif, narasi disetujui, verifikasi `--siap-penelitian` tanpa galat) untuk Ringkasan dan Penelitian |
| `Simulation\Simulator`, `SimClock`, `SeededItemModel` | siswa simulasi `gelita:simulasi`: memanggil layanan permainan sungguhan, menggeser waktu ke jam simulasi, dan memilih variasi berbenih |

`DifficultyRules`, `BankRules`, `BankSource`, dan `ClozeReading` murni (tanpa framework) agar dapat dipakai skrip `docs/bank-soal/build-workbook.php`.

Kelas pendukung lain: `App\Database\GelitaMigration` (pola bersama migration: InnoDB, `utf8mb4_unicode_ci`, PK `BIGINT UNSIGNED`, `DATETIME(6)`), `App\Exceptions\ChallengeRuleException` (pelanggaran aturan permainan dengan kode galat API), trait `App\Entities\Traits\Bilingual`, `App\Models\Traits\EncodesJson`, `App\Controllers\Concerns\GameProgress`, `App\Controllers\Concerns\StaffScope`, dan `App\Commands\Concerns\CliOptions`.

### 6.6 Layout dan view

| Layout | Dipakai | Isi |
|---|---|---|
| `layouts/game.php` | seluruh halaman siswa | `<html class="game-stage">` › `.stage-frame` › `#stage` (panggung 16:9; modal dan toast di dalamnya, hanya layar putar di luar); skrip kecil di `<head>` mengisi `--app-w`/`--app-h` sebelum CSS; CSS `tokens`, `base`, `stage`, `components`, `game`, `cinematic` (+ `noscript.css` dalam `<noscript>`); manifest PWA; `#app-config`; `vendor/howler.min.js`; modul `js/game.js` |
| `layouts/admin.php` | halaman panel | sidebar dari `AdminMenu`, topbar (breadcrumb, cari siswa, menu akun), hanya CSS `admin.css`, `#app-config`, modul `js/admin.js` |
| `layouts/auth.php` | halaman masuk staf | kerangka minimal, CSS `admin.css` |

Halaman memakai View Layouts CodeIgniter (`$this->extend()`, `$this->section()`). Komponen di `Views/components/` dirender lewat `component()`. Data untuk JavaScript dikirim lewat `<script type="application/json">` (`#app-config`, `#password-policy`, `#challenge-data`, `#asset-sizes`), bukan interpolasi string ke kode. Rincian view ada di [05_VIEW_UI.md](05_VIEW_UI.md).

### 6.7 Berkas bahasa

| Berkas | Bahasa | Isi |
|---|---|---|
| `Game.php` | id, en | teks halaman siswa |
| `Auth.php` | id, en | pendaftaran, masuk, dan kata sandi |
| `Js.php` | id, en | teks yang ditulis JavaScript; dikirim lewat `js_config()` |
| `Validation.php` | id, en | pesan aturan validasi (en hanya menambah pesan aturan GELITA; pesan bawaan disediakan framework) |
| `Admin.php` | id | label panel admin |

`Game`, `Auth`, dan `Js` wajib punya kunci yang sama di kedua bahasa (dikunci `tests/unit/LanguageFilesTest.php`). Pemakaian: `lang('Game.check')`, dengan placeholder `lang('Game.firstTryCount', [$first, $total])`.

### 6.8 Penanganan galat

`Config\Exceptions::handler()` mengembalikan `App\Libraries\GelitaExceptionHandler`:

* `/api/*`: JSON seragam `{success: false, code, message, request_id}`; di production tanpa pesan exception, kelas, atau stack trace. Token CSRF yang ditolak dijawab `401 INVALID_SESSION` (tanpa login) atau `403 CSRF_EXPIRED` (halaman terlalu lama terbuka).
* Halaman HTML: `errors/html/error_404.php` dan `error_500.php` bergaya GELITA; development tetap menampilkan halaman detail CodeIgniter.
* CLI dan request lain: handler bawaan CodeIgniter.

Stack trace hanya masuk `writable/logs/`.

---

## 7. Keamanan dan privasi

### 7.1 CSRF

* Filter `csrf` berjalan pada semua POST, PUT, PATCH, dan DELETE, termasuk `/api`. Ini aman karena routing otomatis mati (`Config\Routing::$autoRoute = false`).
* Token disimpan di session (`security.csrfProtection = 'session'`). JavaScript membaca token dari `#app-config` dan mengirimnya lewat header `X-CSRF-TOKEN`.
* `security.regenerate = false`: permainan mengirim banyak request AJAX beruntun, dan token yang berganti setiap request membuat request paralel gagal. Token tetap diacak per respons (`tokenRandomize = true`) sehingga tetap aman terhadap BREACH.

### 7.2 Sesi

* Satu mekanisme sesi untuk siswa dan staf: session CodeIgniter berbasis database (`DatabaseHandler`, tabel `ci_sessions` dari migration `2026-01-01-002900_CreateCiSessions`). Jangan menjalankan `php spark session:migration`; perintah itu membuat migration `ci_sessions` kedua.
* Siswa: `session('participant_id')` + `session('game_session_id')`. Staf: `session('staff_id')` + `session('staff_role')` + `session('staff_school_id')`.
* Cookie `gelita_session`: `HttpOnly`, `SameSite=Lax`, `Secure` bila `cookie.secure = true` (HTTPS). Masa berlaku 4 jam (`expiration = 14400`), ID diganti dan sesi lama dihapus saat regenerasi (`regenerateDestroy = true`).
* Tidak ada cookie "ingat saya", karena komputer kelas dipakai bergantian.
* `matchIP = false`: jaringan sekolah sering ber-NAT/DHCP, dan PK `ci_sessions` hanya `id` (CodeIgniter mensyaratkan `ip_address` ikut PK bila `matchIP` aktif).

<a id="3b-library-hashedipsessionhandler--belum-dipasang"></a>

### 7.3 `HashedIpSessionHandler` (belum dipasang)

`App\Libraries\HashedIpSessionHandler` (turunan `MySQLiHandler` yang menulis `hash_ip()` ke `ci_sessions.ip_address`) ada di repositori, tetapi **tidak dipakai**: `session.driver` tetap `CodeIgniter\Session\Handlers\DatabaseHandler`. Akibatnya `ci_sessions.ip_address` (`VARCHAR(64)`, cukup untuk SHA-256 heksadesimal) berisi IP **mentah** selama sesi hidup, sampai dibersihkan garbage collector sesi. Tabel GELITA lain hanya menyimpan `ip_hash`.

Yang harus diselesaikan sebelum handler ini boleh dipasang:

| # | Masalah | Perubahan yang diperlukan |
|---|---|---|
| 1 | Kelas mewarisi `MySQLiHandler`, yang mengunci sesi dengan `GET_LOCK` (khusus MySQL/MariaDB), sehingga gagal di grup `tests` (SQLite3) | kunci yang sadar platform, atau penghashan IP sebagai dekorator di atas handler yang dipilih `Services::session()` |
| 2 | `Services::session()` bawaan hanya memilih handler per platform bila driver **persis** `DatabaseHandler::class`; driver kustom melewati pemilihan itu | timpa `session()` di `app/Config/Services.php`, atau pertahankan `DatabaseHandler` dan hash IP sebelum sampai ke handler |
| 3 | Uji memakai SQLite3 `:memory:`; saat ini aman hanya karena `CIUnitTestCase` menyuntikkan `MockSession` ber-`ArrayHandler` | sesi jatuh ke `ArrayHandler`/`FileHandler` saat `testing`, atau selesaikan butir 1–2 |
| 4 | `hash_ip()` mengembalikan `null` untuk IP kosong, sehingga yang tersimpan `''` | tetapkan penanda eksplisit, atau catat `''` sebagai keputusan |
| 5 | `Config\Gelita::$ipSalt` bawaannya `''`; tanpa garam, hash IPv4 dapat dibalik dengan pencarian menyeluruh | gagal cepat bila garam kosong |
| 6 | `session.matchIP` harus tetap `false` | — (keputusan, bukan cacat) |

Butir 1–3 menghalangi pemasangan, butir 5 prasyarat keamanan, butir 4 dan 6 keputusan yang perlu dicatat.

### 7.4 Kata sandi

* Staf dan siswa: `password_hash($p, PASSWORD_DEFAULT)` + `password_verify()`.
* Sandi siswa wajib lolos `App\Libraries\PasswordPolicy`, yang membaca `Config\Gelita::$passwordPolicy`. `check()` mengembalikan hasil per syarat (panjang, huruf besar, huruf kecil, angka, simbol), level, dan apakah sandi memuat nama pengguna, tanpa pernah mengembalikan isi sandi. Panjang juga dibatasi 72 byte (batas bcrypt). Hanya level `strong` tanpa nama pengguna yang diterima (aturan validasi `strong_password`).
* JavaScript halaman pendaftaran dan ganti sandi menerima salinan aturan lewat `PasswordPolicy::toClient()` di `<script type="application/json" id="password-policy">` untuk umpan balik langsung; keputusan akhir selalu dari server.
* Throttle masuk menurut `Config\Gelita::$loginThrottle`. Endpoint publik `/api/auth/username-available` dan `/api/schools/lookup` dibatasi per hash IP dengan throttler CodeIgniter.
* Sandi sementara (reset oleh guru/admin, `gelita:staff:password`) wajib diganti saat masuk; filter mengalihkan ke halaman ganti sandi.
* Sandi mentah tidak pernah dicatat di log, event, riwayat aktivitas, maupun `writable/debugbar`. Debug Toolbar bawaan menyalin seluruh isi POST ke `writable/debugbar/*.json` (`toolbar.collectVarData` hanya mengatur data view), sehingga alias `toolbar` menunjuk `App\Filters\DebugToolbar` yang menyamarkan field berpola `pass|sandi|secret|token` sebelum toolbar mengambil potret request. Dikunci `tests/unit/DebugToolbarRedactionTest.php`.

### 7.5 IP dan data pribadi

* IP mentah tidak disimpan di tabel GELITA; `game_sessions`, `device_logs`, dan `audit_logs` hanya menyimpan `ip_hash` (`hash_ip()`, SHA-256 ber-garam `gelita.ipSalt`). Pengecualian: `ci_sessions.ip_address` selama § 7.3 belum selesai.
* `DeviceProfile` tidak membuat sidik jari perangkat; ia hanya mencatat jenis, merek, model, OS, peramban, dan sinyal layar.
* Pemutar video pihak ketiga di Pustaka baru dimuat setelah siswa menekan Putar (YouTube memakai `youtube-nocookie.com`), dan poster videonya diunduh server lebih dulu (`gelita:library:thumbnails`).
* Ekspor guru selalu anonim dan dibatasi sekolahnya; ekspor ditentukan ulang di `ExportService` dari data `staff_users`, bukan dari isi formulir.

### 7.6 Header, CSP, dan rute HEAD

* Filter `secureheaders` (global *after*) mengirim antara lain `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`, dan `Referrer-Policy: same-origin`. Konfigurasi Nginx di `deploy/` juga mengirim header tersebut (dan HSTS pada HTTPS) serta membuang salinan dari PHP.
* Content Security Policy bawaan CodeIgniter **tidak diaktifkan** (`Config\App::$CSPEnabled = false`), dan konfigurasi Nginx tidak mengirim header CSP. View tidak memakai skrip inline yang dieksekusi: kode dimuat dari berkas, data dikirim lewat `<script type="application/json">`. Beberapa view masih memakai atribut `style` inline (posisi dan variabel CSS), yang perlu diperhitungkan bila CSP kelak diaktifkan.
* `HeadAsGetRouteCollection` membuat request HEAD dilayani rute GET beserta filternya (`staffAuth`, `staffRole`, `gameSession`, …). Tanpa itu CodeIgniter menjawab HEAD dengan 404 sehingga pemantau uptime dan `curl -I` melaporkan situs mati. Memetakan handler tanpa opsi rute akan menjalankan controller admin tanpa filter autentikasi. Uji: `tests/unit/HeadRouteTest.php`.

### 7.7 Unggahan, SQL, XSS, dan otorisasi

| Kontrol | Implementasi |
|---|---|
| Unggahan | `MediaStore`: daftar MIME yang diizinkan (gambar PNG/JPEG/WebP/GIF/SVG, audio, video), dimensi dibaca ulang server dengan `getimagesize()` dan dicocokkan dengan `$assetSizes`, nama berkas resmi dari `asset_key`, SHA-256 dicatat |
| SQL | Query Builder / prepared statement; tidak ada SQL yang dirangkai dari input |
| XSS | `esc()` pada semua output; `rich_text()` meng-escape seluruh teks sebelum menyusun tag; tautan media luar diterjemahkan server (`MediaLink`) sehingga `javascript:`/`data:` tidak pernah lolos |
| Redirect | tujuan dari input pengguna lewat `safe_internal_url()` |
| Otorisasi | tiga lapis: filter route (`staffAuth`, `staffRole`) → controller (`StaffScope::schoolScope()`/`dataScope()`) → service (`AnalyticsService`, `RawDataService`, `ExportService`, `ReportService` membatasi `participants.school_id`). Guru tanpa sekolah mendapat cakupan 0, yaitu tidak melihat apa pun |
| Permainan | jawaban diperiksa dan pengerjaan ditutup di server (`ChallengeService`); setiap endpoint memeriksa sesi → pengerjaan → soal milik pengerjaan itu |

---

## 8. Konvensi

### Bahasa

Aturan lengkapnya ada di `.claude/rules/language.md` (berlaku sejak 9 Oktober 2026) dan padanan istilah di `.claude/rules/glossary.md`.

* **Bahasa Inggris** untuk identifier baru (kelas, method, variabel, tabel, kolom), komentar kode, pesan commit, judul dan isi PR, log, pesan galat untuk pengembang, dokumen teknis baru, dan entri baru di catatan implementasi.
* **Bahasa Indonesia yang ramah** untuk teks yang dibaca siswa, guru, dan admin (lewat berkas bahasa, lihat `.claude/rules/ui-copy.md`), dan untuk path URL publik berupa slug pendek (`/daftar`, `/masuk`, `/admin/peserta`, `/admin/data`). Controller dan method di balik route tetap berbahasa Inggris. Path JSON di bawah `/api` berbahasa Inggris.
* **Nama Indonesia yang sudah ada tetap**: identifier, kode tersimpan (`rumpang`, `boleh`, `cari`, `bintang-1`, kode dimensi `peserta`, `sesi`, …), slug route, nama event, nama command (`gelita:simulasi`), opsi command (`--paksa`, `--tanpa-bank`), dan kolom workbook tidak diganti hanya untuk diterjemahkan.
* Dokumen yang sudah berbahasa Indonesia (`docs/01`–`08`, `docs/perombakan-kelas5/*`) tetap berbahasa Indonesia; bagian baru di dalamnya ditulis dalam bahasa Inggris.
* Padanan Inggris teks pemain ada di `app/Language/en/`. Panel admin hanya berbahasa Indonesia, jadi tidak ada `en/Admin.php`.
* Konten permainan dwibahasa disimpan berpasangan `*_id` / `*_en`.

### Kode

* PSR-4: `App\` → `app/`, `Config\` → `app/Config/`.
* Controller tipis: aturan bisnis ada di `app/Services/`; controller memanggil `service('…')`.
* Konten permainan dibaca dari database lewat `ContentRepository`, tidak dari array PHP di kode. Berkas data di `app/Database/Seeds/data/` dan `docs/*/data/` hanya sumber seeder, impor, dan skrip workbook.
* Satu aturan, satu tempat: `Config\Gelita` untuk konstanta domain, `DifficultyRules`/`BankRules` untuk aturan bank soal, `ScoringService` untuk bintang, `PasswordPolicy` untuk sandi, `ParticipantCheck` untuk pemeriksaan siswa, `StaffScope` untuk cakupan sekolah.
* Migration memperluas `App\Database\GelitaMigration`, satu tabel per berkas (rincian di [01_DATABASE.md](01_DATABASE.md)).
* Seeder bagian memperluas `GelitaSeeder` dan idempoten: baris yang sudah ada tidak ditimpa. `DatabaseSeeder` memanggil semuanya berurutan.
* Command GELITA bernama `gelita:*`, membaca opsi lewat trait `CliOptions` (menerima `--opsi nilai` maupun `--opsi=nilai`), dan memanggil `db_sync_timezone()` bila menulis waktu.
* Fungsi helper dibungkus `if (! function_exists(…))`.
* Respons JSON selalu `{success, data | code + message, request_id}`.
* Aset media dirujuk lewat `asset_key` resmi (`bg.welcome`, `char.jaka.idle.1`, `challenge.item.…`) atau FK ke `media_assets`, bukan path berkas.
* JavaScript: ES modules tanpa bundler; teks UI dari `lang('Js.*')` lewat `js_config()`, tidak ditulis di berkas JS.

### Repositori

* Satu branch tetap, `main`. Pekerjaan dilakukan di branch sementara dari `main` dan masuk lewat PR.
* Semua uji harus hijau sebelum push (§ 10).
* Setiap keputusan atau penyimpangan dari rencana perombakan dicatat di [`perombakan-kelas5/CATATAN_IMPLEMENTASI.md`](perombakan-kelas5/CATATAN_IMPLEMENTASI.md).

---

## 9. Spark command

Command GELITA ada di `app/Commands/`. Daftar lengkap: `php spark list`. Perilaku tiap command dirinci di [07_FEATURE_INTEGRATION.md](07_FEATURE_INTEGRATION.md) dan [08_DEPLOYMENT.md](08_DEPLOYMENT.md).

| Command | Fungsi |
|---|---|
| `gelita:bank:import <file> [--dry-run] [--staff <username>]` | memuat workbook bank soal atau Pustaka (XLSX) dalam satu transaction |
| `gelita:content:verify [--strict] [--siap-penelitian]` | memeriksa 3 wilayah × 4 tantangan, variasi, bank soal, kunci, dan media; kode keluar 1 bila ada galat. `--strict` membuat peringatan juga menghasilkan kode keluar 1; `--siap-penelitian` juga gagal selama masih ada gambar contoh |
| `gelita:library:thumbnails [--force]` | mengunduh thumbnail video luar Pustaka dan memasangnya sebagai poster |
| `gelita:media:scan [--check-only]` | menyinkronkan folder `public/assets` dengan `media_assets` dan melaporkan integritas |
| `gelita:mockups [--dry-run] [--paksa] [--node=tmg-4]` | membuat gambar contoh untuk slot gambar tantangan yang belum berisi gambar asli |
| `gelita:narration:import [--locale=id\|en] [--dry-run]` | mengimpor rekaman `public/assets/audio/narasi/{id,en}/` sebagai audio draft |
| `gelita:reset [--dry-run] [--ya] [--paksa] [--pemeliharaan] [--tanpa-bank] [--bank=JALUR] [--tanpa-mockup] [--hapus-guru]` | mengosongkan data uji coba sebelum pengambilan data penelitian, membangun ulang 12 tantangan dan bank soal; Pustaka, cerita, media, audio, sekolah resmi, dan staf tetap |
| `gelita:retention:run` | retensi harian (cron): sesi dan pengerjaan menganggur, berkas ekspor kedaluwarsa, pratinjau penghapusan data yang melewati masa simpan |
| `gelita:schools:import [--file=PATH] [--dry-run] [--link-existing]` | memasang daftar sekolah resmi dari `app/Database/Seeds/data/sekolah-jateng.csv` |
| `gelita:simulasi [--siswa=N] [--seed=N] [--mulai=YYYY-MM-DD]` | siswa simulasi `sim_NNN` untuk pengembangan (bawaan 12, paling banyak 500): mendaftar, bermain, dan mengisi kritik & saran lewat layanan yang sama dengan controller, pada jam simulasi hari sekolah. Ditolak di production, tanpa studi aktif, atau bila studi aktif sudah berisi sesi siswa selain `sim_*`. Rincian di FITUR 19 [07_FEATURE_INTEGRATION.md](07_FEATURE_INTEGRATION.md) |
| `gelita:staff:password <username>` | sandi sementara untuk staf; wajib diganti saat masuk |

Semua opsi bernilai menerima `--opsi nilai` maupun `--opsi=nilai`. `spark` berpindah ke folder `public/` sebelum command berjalan; jalur berkas relatif pada `gelita:bank:import` dicari dari direktori kerja shell lalu dari root proyek.

---

## 10. Pengujian

```bash
composer install                   # termasuk paket pengembangan (PHPUnit)
vendor/bin/phpunit --no-coverage   # 487 uji
```

Semua uji harus hijau sebelum push. Uji tidak membutuhkan MySQL/MariaDB: grup database `tests` memakai SQLite3 `:memory:` (`Config\Database::$tests`), sehingga mesin pengembangan butuh ekstensi PHP `sqlite3`. Server produksi yang dipasang dengan `composer install --no-dev` tidak membawa PHPUnit.

| Folder | Isi |
|---|---|
| `tests/unit/` | seluruh uji GELITA: aturan bank soal (`BankKelas5DataTest`), aturan 3 kesempatan dan cek bersamaan (`TryLimitTest`), bintang (`StarsTest`), pemilihan variasi (`VariantSelectionTest`), reset (`SystemResetTest`), pendaftaran (`RegisterWizardTest`), panggung 16:9 (`StageCssTest`, `ChallengeFrameTest`, `RotateGateTest`), tabel panel dan Data mentah (`AdminTableTest`, `AdminTableViewTest`), ekspor (`RawExportTest`), simulasi (`SimulationTest`), narasi, sekolah, media, helper, berkas bahasa, route, dan berkas deploy |
| `tests/_support/Database/` | skema SQLite bersama: `ResetTables` (skema lengkap yang sama dengan 29 migration), `GradeFiveWorld` (dunia kelas 5 siap bermain, termasuk `gelita:reset` sungguhan yang mengimpor workbook bank soal), `RawDataWorld` (fixture kecil untuk Data mentah dan ekspor), `NarrationTables`, `SchoolTables` |
| `tests/database/`, `tests/session/` | contoh bawaan kerangka CodeIgniter |

`phpunit.dist.xml` memakai bootstrap `vendor/codeigniter4/framework/system/Test/bootstrap.php`, menganggap peringatan dan uji berisiko sebagai gagal, dan menulis cache serta laporan ke `build/` (diabaikan Git). Laporan cakupan butuh Xdebug atau PCOV; tanpa `--no-coverage`, mesin tanpa keduanya mendapat peringatan "No code coverage driver available" yang membuat suite keluar dengan kode 1.

SQLite tidak meniru kunci baris MySQL: uji balapan mensimulasikan data usang (entity lama, atau model tiruan lewat `Factories::injectMock`). Perilaku bersamaan sungguhan diuji di MariaDB/MySQL dengan `tools/qa/challenge-races.mjs` (§ 11).

Uji tertentu mengunci keputusan desain agar tidak hilang diam-diam, misalnya `DebugToolbarRedactionTest` (penyamaran sandi), `HeadRouteTest` (rute HEAD), `JsConfigTest` (isi `#app-config`), `LanguageFilesTest` (kunci ID = EN), `InstallableAppTest` (PWA), dan `DeployFilesTest` (skrip `deploy/`).

---

## 11. Alat dan sumber pendukung

Bahan berikut ada di repositori tetapi tidak dijalankan server saat permainan berlangsung.

| Lokasi | Isi | Cara memakai |
|---|---|---|
| [`docs/bank-soal/`](bank-soal/README.md) | bank soal final kelas 5: `data/{temanggung,magelang,wonosobo}.php` → `build-workbook.php` → `gelita-bank-soal-kelas5.xlsx` (126 soal: 108 dinilai + 18 pengecoh, 30 variasi, 45 slot gambar tantangan di sheet DAFTAR_MEDIA) | `php docs/bank-soal/build-workbook.php [keluaran.xlsx]`; workbook diimpor `KontenKelas5Seeder`, `gelita:reset`, atau `gelita:bank:import` |
| [`docs/pustaka/`](pustaka/README.md) | Pustaka Kedu: 30 halaman dan 48 gambar/video, `data/*.php` → `build-workbook.php` → `gelita-pustaka.xlsx` | `php docs/pustaka/build-workbook.php [keluaran.xlsx]`; diimpor `KontenKelas5Seeder` saat instalasi |
| [`docs/sekolah/`](sekolah/README.md) | penyusun daftar sekolah resmi Jawa Tengah (`build-sekolah-jateng.php`) → `app/Database/Seeds/data/sekolah-jateng.csv` + `sekolah-jateng.meta.json` | `php docs/sekolah/build-sekolah-jateng.php [--source=…] [--out=…]`, lalu `php spark gelita:schools:import` |
| [`docs/naskah-cerita.md`](naskah-cerita.md) | naskah cerita; salinan datanya `app/Database/Seeds/data/story.php` (88 baris `dialogues`) | disemai `ContentSeeder` |
| [`tools/audio/`](../tools/audio/README.md) | generator musik gamelan, efek suara, dan narasi TTS Indonesia–Inggris (Python 3.11+, Node 20+, ffmpeg; model diunduh `setup-models.sh`, tidak di-commit) | menghasilkan `public/assets/audio/music/`, `sfx/`, dan `narasi/{id,en}/` (100 rekaman per bahasa: 88 baris cerita + 12 petunjuk Cari Objek) beserta `narasi/produksi.json`; lalu `gelita:narration:import`. Penjelasan desain di [audio.md](audio.md) |
| `tools/qa/` | uji peramban Playwright (Chromium terpasang di `/opt/pw-browsers`): `stage-check.mjs` memeriksa panggung 16:9 di beberapa ukuran jendela dan menyimpan tangkapan layar ke `writable/qa/stage/`; `challenge-races.mjs` memeriksa isian beku selama cek, waktu isian, Cari Objek, muat ulang tanpa `challenge_abandoned` palsu, dan cek bersamaan dari dua tab/perangkat; `a11y-check.mjs` memeriksa layar tantangan dengan keyboard saja (urutan Tab, nama aksesibel, cincin fokus, dialog petunjuk/hasil, `aria-live` petunjuk Cari Objek, dan axe-core bila tersedia) | terhadap `php spark serve` (untuk balapan: `PHP_CLI_SERVER_WORKERS=4`) dengan database MariaDB/MySQL; pemakaian di kepala tiap skrip |
| `deploy/linux/`, `deploy/windows/` | `deploy.sh` / `deploy.ps1`, `backup.sh` / `backup.ps1`, `retensi.ps1`, contoh konfigurasi Nginx HTTP/HTTPS | lihat [08_DEPLOYMENT.md](08_DEPLOYMENT.md) |

Ketiga skrip penyusun berjalan tanpa framework CodeIgniter. Kedua `build-workbook.php` memakai autoload Composer (PhpSpreadsheet) dan kelas murni di `app/Libraries/`; `build-sekolah-jateng.php` berdiri sendiri. Skrip bank soal memeriksa seluruh aturan `BankRules` dan tidak menulis workbook bila ada galat.

---

## 12. Aturan sistem

1. Tidak ada SPA framework. Seluruh halaman dirender server; JavaScript hanya menambah perilaku.
2. Paket Composer produksi dibatasi daftar di `composer.json`, dan setiap paket diberi alasan di `extra.gelita-dependency-notes`. Library frontend di-host sendiri; tidak ada CDN.
3. Semua konten permainan dibaca dari database lewat `ContentRepository`, tidak pernah dari array PHP yang di-hard-code.
4. Bahasa disimpan di session dan `game_sessions.locale`. Mengganti bahasa tidak membuat sesi baru dan tidak mereset progres.
5. `writable/exports/` di luar `public/`. Tidak ada berkas data penelitian yang dapat diakses tanpa melewati controller berotorisasi.
6. IP mentah tidak disimpan di tabel GELITA; hanya `ip_hash`. Pengecualian yang diketahui: `ci_sessions.ip_address` selama `HashedIpSessionHandler` belum dipasang (§ 7.3).
7. Zona waktu `Asia/Jakarta` dipakai PHP dan koneksi database (`db_sync_timezone()`).
8. Semua respons API memakai bentuk seragam `{success, data | code + message, request_id}`.
9. Aturan kata sandi siswa hanya ditulis di `Config\Gelita::$passwordPolicy`. JavaScript menerima salinannya dari server, tidak menulis ulang angka-angkanya sendiri.
10. Kata sandi siswa maupun staf tidak pernah muncul di log, event, riwayat aktivitas, atau `writable/debugbar`. `log_message()` tidak pernah dipanggil dengan data formulir masuk/daftar, dan `App\Filters\DebugToolbar` menyamarkan field kredensial.
11. Sistem hanya mencatat data mentah. Tidak ada skor, indeks kesulitan, atau capaian yang dihitung sistem; bintang hanya hadiah di layar siswa (`ScoringService`).
12. Pemeriksaan jawaban, aturan 3 kesempatan, dan penutupan pengerjaan berlangsung di server (`ChallengeService`); nilai dari browser tidak dipercaya.
13. Layar game hanya ditata di dalam panggung 16:9 (`#stage`). CSS game memakai rem (1 rem = 16 dp) tanpa `vw`/`vh` (kecuali cadangan `--app-*` di `tokens.css`), tanpa media query lebar/tinggi, dan tanpa ukuran `px` selain garis 1px; dikunci `tests/unit/StageCssTest.php`.
14. CSS panel (`admin.css`) dan CSS game tidak saling memuat: perubahan panggung tidak memengaruhi panel, dan sebaliknya.
