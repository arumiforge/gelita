# GELITA

**GELITA** (Game Edukasi Literasi dan Etnopedagogi Kedu) adalah game literasi membaca berbasis budaya Kedu untuk siswa **kelas 5 SD/MI**, sekaligus instrumen penelitian. Pemain berperan sebagai Jaka, dibimbing Mbah Kedu, memulihkan **12 Serpihan Cahaya** di tiga wilayah eks-Karesidenan Kedu. Setiap jawaban, percobaan, petunjuk, pemutaran audio, dan perpindahan layar dicatat sebagai data mentah penelitian.

Aplikasi dibangun dengan **CodeIgniter 4.7** (PHP 8.2+, MariaDB 10.6+ / MySQL 8), dirender server dengan HTML5 + CSS3; JavaScript (ES modules tanpa bundler dan tanpa framework) hanya menambah perilaku. Area game dwibahasa (Indonesia/Inggris), panel admin berbahasa Indonesia.

## Fitur utama

**Permainan**

- Tiga wilayah yang terbuka berurutan: **Temanggung** (mudah) → **Magelang** (sedang) → **Wonosobo** (sulit). Tiap wilayah punya 4 tantangan berurutan:

  | Urutan | Tantangan | Engine | Temanggung | Magelang | Wonosobo |
  |---:|---|---|---|---|---|
  | 1 | Susun Gambar | `puzzle` | 3 × 3 kotak | 4 × 4 kotak | 4 × 4 jigsaw |
  | 2 | Isi Rumpang | `rumpang` | 3 rumpang | 5 rumpang + 2 pengecoh | 7 rumpang + 2 pengecoh |
  | 3 | Benar atau Salah | `boleh` | 3 pernyataan | 5 pernyataan | 6 pernyataan |
  | 4 | Cari Objek | `cari` | 4 dicari + 4 pengecoh | 4 dicari + 6 pengecoh | 4 dicari + 8 pengecoh |

- Bank soal final kelas 5: **126 soal** (108 dinilai + 18 benda pengecoh), 30 variasi, 9 bacaan Benar atau Salah, 9 bacaan Isi Rumpang, 36 petunjuk, 12 kata pengecoh, dan 45 slot gambar tantangan. Satu variasi (`a`/`b`/`c`) dipilih acak setiap pengerjaan. Rinciannya di [`docs/bank-soal/README.md`](docs/bank-soal/README.md).
- Setiap soal paling banyak **3 percobaan** (`Config\Gelita::$maxTries`). Setiap percobaan tercatat satu baris di `item_tries`; pengerjaan ditutup sebagai `solved` atau `tries_exhausted`, lalu diberi bintang.
- Alur bercerita dari awal sampai akhir: halaman awal → `/mulai` → pendaftaran → cerita pembuka → Peta Kedu → Kenali wilayah → dialog pembuka wilayah → tantangan → wilayah tuntas → Pustaka → … → penutup → Balai Refleksi. Naskahnya 88 baris dwibahasa ([`docs/naskah-cerita.md`](docs/naskah-cerita.md)).
- **Pustaka Kedu**: 30 halaman bacaan dwibahasa dengan 48 gambar/video, terbuka per wilayah setelah wilayahnya tuntas ([`docs/pustaka/README.md`](docs/pustaka/README.md)).
- Pendaftaran tiga langkah (`/daftar/1`–`3`) dengan centang persetujuan siswa dan kata sandi kuat (bagian dari literasi keamanan digital). Siswa dari Jawa Tengah memilih sekolah lewat **NPSN** dari daftar sekolah resmi ([`docs/sekolah/README.md`](docs/sekolah/README.md)).
- Game hanya dimainkan mendatar; ponsel/tablet yang dipegang tegak melihat layar "Putar perangkatmu". Dapat dipasang di layar utama (manifest + service worker yang hanya menampilkan halaman offline).
- Audio bawaan lengkap: musik gamelan, efek suara, dan narasi ID/EN (lihat [Audio bawaan](#audio-bawaan)).

**Data penelitian**

- Server adalah sumber kebenaran: benar/salah, bintang, serpihan, dan status buka/kunci dihitung server; kunci jawaban tidak pernah dikirim ke browser.
- Data mentah: percobaan per soal (`item_tries`), jawaban, petunjuk, event permainan (`game_event_logs`, mis. `screen_viewed`, `answer_selected`, `viewport_changed`), pemakaian audio (`audio_usage_events`), dan perangkat siswa (`device_logs`, dari User-Agent + Client Hints). Event yang gagal terkirim disimpan sementara di browser dan dikirim ulang ke sesi yang sama.
- Tidak ada skor olahan atau chart di panel; pengolahan dilakukan peneliti dari ekspor.

**Panel admin** (`/admin`)

- Dasbor berisi hitungan mentah, daftar peserta, sesi beserta linimasa event, dan masukan siswa.
- Ekspor XLSX data mentah (guru: selalu anonim dan hanya sekolahnya) dan PDF data satu peserta.
- Khusus admin: editor konten (wilayah, tantangan, soal, bacaan, dialog, Pustaka, narasi), impor bank soal dari Excel, Media dan **Kelengkapan aset**, studi & rilis, tata kelola data (penghapusan berpratinjau, audit, retensi), direktori sekolah, dan akun staf.
- Otorisasi berlapis tiga (route → controller → service); halaman khusus admin membalas `404` untuk guru.

## Kebutuhan sistem

- PHP 8.2 atau lebih baru (PHP 8.3 disarankan) dengan ekstensi `intl`, `mbstring`, `json`, `mysqli`/`mysqlnd`, `curl`, `gd`, `zip`, `fileinfo`, `openssl`, `iconv`, `dom`, `xml`, `xmlwriter`, dan `simplexml`
- Composer 2.6 atau lebih baru
- MariaDB 10.6+ atau MySQL 8.0+
- Web server (Nginx + PHP-FPM di server; `php spark serve` untuk pengembangan)

Di Windows, Laragon (PHP 8.3 + MySQL) memenuhi semua kebutuhan ini. Library frontend (Howler.js) dan font di-host sendiri di `public/assets/`, tanpa CDN. Pengecualiannya hanya media **tautan** Pustaka (YouTube, Vimeo, Google Drive, Wikimedia Commons), yang dimuat dari domain asalnya.

## Mulai cepat (pengembangan)

1. Pasang dependensi:

   ```bash
   composer install
   ```

2. Salin templat environment, lalu isi:

   ```bash
   cp env .env                 # PowerShell: Copy-Item env .env
   php spark key:generate      # mengisi encryption.key
   ```

   | Kunci | Keterangan |
   |---|---|
   | `app.baseURL` | URL aplikasi lokal, mis. `http://localhost:8080/` |
   | `database.default.*` | kredensial MariaDB/MySQL |
   | `encryption.key` | wajib; token objek Cari Objek diturunkan darinya |
   | `gelita.ipSalt` | string acak panjang; garam hash IP. Jangan dibiarkan bawaan |

   `env` adalah templat yang dilacak Git; `.env` tidak pernah di-commit.

3. Buat database, jalankan migration, lalu isi data awal:

   ```bash
   mysql -u root -p -e "CREATE DATABASE gelita CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   php spark migrate
   GELITA_ADMIN_PASSWORD='SandiKuatAnda123!' php spark db:seed DatabaseSeeder
   php spark gelita:narration:import
   ```

   Di PowerShell:

   ```powershell
   $env:GELITA_ADMIN_PASSWORD = 'SandiKuatAnda123!'; php spark db:seed DatabaseSeeder; Remove-Item Env:\GELITA_ADMIN_PASSWORD
   ```

   - `migrate` menjalankan 29 migration (satu tabel per berkas, termasuk `ci_sessions`); jangan menjalankan `php spark session:migration`.
   - `DatabaseSeeder` mengisi daftar sekolah resmi, akun `admin`, pengaturan bawaan (indikator, rilis, studi), tiga wilayah, slot media, 12 tantangan dan 88 baris cerita, lalu `KontenKelas5Seeder` mengimpor Pustaka Kedu, bank soal kelas 5, dan 45 gambar contoh.
   - `gelita:narration:import` memasang 100 rekaman narasi per bahasa sebagai audio **draft**. Setujui di panel: **Konten → Narasi → Setujui semua narasi draft** (ID dan EN).

4. Jalankan server pengembangan:

   ```bash
   php spark serve
   ```

   Staf masuk di `/admin/login` (akun `admin`, sandi dari `GELITA_ADMIN_PASSWORD`). Siswa mendaftar sendiri lewat `/mulai`.

Periksa isinya dengan `php spark gelita:content:verify`. Konten dialog dan soal di-cache; setelah mengubah data langsung di database, jalankan `php spark cache:clear`.

## Instalasi di server

Langkah lengkap untuk kedua jalur (Linux: Nginx + PHP-FPM + cron; Windows: Laragon + Task Scheduler) ada di [`docs/08_DEPLOYMENT.md`](docs/08_DEPLOYMENT.md), dengan skrip di [`deploy/`](deploy/). Ringkasnya:

1. `composer install --no-dev --optimize-autoloader`, salin `env` → `.env` dengan `CI_ENVIRONMENT = production`, isi database, `encryption.key`, `gelita.ipSalt`, dan `app.baseURL`. Web server mengarah ke folder `public/`.
2. `php spark migrate` dengan akun migrasi `gelita_migrate` lewat `database_default_DSN` (DSN ditulis tanpa port); aplikasi sendiri memakai akun berhak DML saja.
3. `GELITA_ADMIN_PASSWORD='…' php spark db:seed DatabaseSeeder`, dijalankan sebagai pengguna web server (mis. `www-data`) agar `public/assets/uploads/` dapat ditulis.
4. `php spark gelita:narration:import`, lalu setujui narasi draft di **Konten → Narasi**.
5. `php spark gelita:content:verify`.
6. Ganti gambar contoh dengan gambar asli di panel (**Media**). `php spark gelita:content:verify --siap-penelitian` harus lolos sebelum pengambilan data.
7. Sesudah uji coba dan sebelum pengambilan data penelitian: buat backup, lalu `php spark gelita:reset` dengan akun migrasi.

Pembaruan kode berikutnya memakai `deploy/linux/deploy.sh` atau `deploy/windows/deploy.ps1` (menolak berjalan bila ada kelas aktif, backup, mode pemeliharaan, pull, composer, migrate, impor sekolah, bersihkan cache, naikkan versi aset, lalu membuka situs hanya bila `gelita:content:verify` lolos). Backup dan `gelita:retention:run` dijadwalkan harian.

## Command `gelita:*`

Semua command mengembalikan kode keluar 1 saat gagal, sehingga dapat dipakai di skrip. Opsi bernilai menerima `--opsi nilai` maupun `--opsi=nilai`. Bantuan per command: `php spark help gelita:reset`.

| Command | Fungsi |
|---|---|
| `gelita:bank:import <file> [--dry-run] [--staff <username>]` | Mengimpor workbook XLSX (bank soal atau Pustaka) dalam satu transaction; `--dry-run` hanya memvalidasi. Sama dengan **Konten → Impor soal dari Excel**. |
| `gelita:content:verify [--strict] [--siap-penelitian]` | Memeriksa 3 wilayah × 4 tantangan, variasi, aturan bank soal, kunci, dan media. `--strict`: peringatan juga gagal; `--siap-penelitian`: gambar contoh yang masih dipakai menjadi galat. |
| `gelita:library:thumbnails [--force]` | Mengunduh thumbnail video luar Pustaka (YouTube, Vimeo, Google Drive) dan memasangnya sebagai poster; butuh HTTPS keluar. |
| `gelita:media:scan [--check-only]` | Menyinkronkan `public/assets` dengan tabel `media_assets` dan melaporkan berkas yang hilang atau berubah. |
| `gelita:mockups [--dry-run] [--paksa] [--node=tmg-4]` | Membuat gambar contoh untuk slot gambar tantangan yang belum berisi gambar asli; gambar asli tidak pernah ditimpa. |
| `gelita:narration:import [--locale=id\|en] [--dry-run]` | Mengimpor rekaman di `public/assets/audio/narasi/{id,en}/` sebagai audio draft; berkas yang tidak berubah dilewati. |
| `gelita:reset [--dry-run] [--ya] [--paksa] [--pemeliharaan] [--tanpa-bank] [--bank=JALUR] [--tanpa-mockup] [--hapus-guru]` | Mengosongkan data uji coba dan pengaturan, membangun ulang 12 tantangan, dan mengimpor ulang bank soal; Pustaka, cerita, media, audio, sekolah resmi, dan akun staf tetap. Butuh akun berhak TRUNCATE. Data tidak dapat dikembalikan: selalu `--dry-run` dan backup dulu. |
| `gelita:retention:run` | Retensi harian: sesi menganggur → `paused`, pengerjaan menggantung → `abandoned`, berkas ekspor kedaluwarsa dibuang, data yang melewati masa simpan hanya dibuatkan pratinjau penghapusan. |
| `gelita:schools:import [--file=PATH] [--dry-run] [--link-existing]` | Memasang daftar sekolah resmi (NPSN) dari `app/Database/Seeds/data/sekolah-jateng.csv`; hanya menulis yang berubah, tidak pernah menghapus. |
| `gelita:staff:password <username>` | Mengatur ulang sandi staf ke sandi sementara yang wajib diganti saat masuk. |

Jangan menjalankan `gelita:reset` di server tanpa backup dan tanpa keputusan peneliti.

## Uji

```bash
vendor/bin/phpunit --no-coverage
```

`phpunit.dist.xml` meminta laporan coverage; tanpa Xdebug/PCOV, PHPUnit memberi peringatan "No code coverage driver available" yang membuat kode keluar 1. Pakai `--no-coverage` bila driver coverage tidak terpasang.

Suite memakai SQLite in-memory (grup database `tests`) dengan skema tiruan di `tests/_support/`, bukan migration aplikasi, karena skema memakai fitur MariaDB/MySQL (`DATETIME(6)`). Alur HTTP ujung ke ujung diuji di atas MariaDB/MySQL sungguhan dengan database terpisah; kredensial CLI dapat dioper lewat `database_default_DSN='MySQLi://user:sandi@localhost/gelita_uji' php spark migrate`.

## Struktur repositori

| Folder | Isi |
|---|---|
| `app/Commands/` | command `gelita:*` |
| `app/Config/` | konfigurasi; konstanta domain di `Gelita.php`, route di `Routes.php` |
| `app/Controllers/` | `Game/`, `Admin/`, `Api/` |
| `app/Database/` | 29 migration, seeder, data naskah (`Seeds/data/story.php`) dan sekolah |
| `app/Libraries/`, `app/Services/` | aturan bank soal (`BankRules`, `DifficultyRules`), impor, penilaian, ekspor, reset |
| `app/Models/`, `app/Entities/` | model per tabel |
| `app/Views/`, `app/Language/{id,en}/` | tampilan dan teks dwibahasa |
| `public/assets/` | CSS, JavaScript (`core/`, `game/`, `engines/`, `admin/`), audio, font, vendor; unggahan admin di `uploads/` (tidak dilacak Git) |
| `deploy/` | skrip deploy, backup, retensi, dan konfigurasi Nginx untuk Linux dan Windows |
| `docs/` | dokumentasi, sumber bank soal, Pustaka, dan daftar sekolah |
| `tests/` | suite PHPUnit |
| `tools/audio/` | generator musik, efek suara, dan narasi |

Daftar route lengkap: `php spark routes`.

## Dokumentasi

| Dokumen | Isi |
|---|---|
| [`docs/01_DATABASE.md`](docs/01_DATABASE.md) | skema database |
| [`docs/02_PROJECT_FOUNDATION.md`](docs/02_PROJECT_FOUNDATION.md) | fondasi proyek, konfigurasi, `.env` |
| [`docs/03_MODEL_ENTITY.md`](docs/03_MODEL_ENTITY.md) | model, entity, service |
| [`docs/04_CONTROLLER_ROUTE.md`](docs/04_CONTROLLER_ROUTE.md) | route, controller, autentikasi, otorisasi |
| [`docs/05_VIEW_UI.md`](docs/05_VIEW_UI.md) | view, UI, CSS |
| [`docs/06_JAVASCRIPT.md`](docs/06_JAVASCRIPT.md) | JavaScript |
| [`docs/07_FEATURE_INTEGRATION.md`](docs/07_FEATURE_INTEGRATION.md) | integrasi fitur |
| [`docs/08_DEPLOYMENT.md`](docs/08_DEPLOYMENT.md) | instalasi server dan operasional |
| [`docs/audio.md`](docs/audio.md) | musik, efek suara, narasi TTS |
| [`docs/naskah-cerita.md`](docs/naskah-cerita.md) | naskah cerita dwibahasa dan konvensi rekaman |
| [`docs/bank-soal/README.md`](docs/bank-soal/README.md) | bank soal kelas 5, aturan, 45 slot gambar tantangan |
| [`docs/pustaka/README.md`](docs/pustaka/README.md) | isi Pustaka Kedu |
| [`docs/sekolah/README.md`](docs/sekolah/README.md) | daftar sekolah resmi Jawa Tengah |
| [`docs/perombakan-kelas5/`](docs/perombakan-kelas5/) | rencana perombakan kelas 5 (Dok 1–3) dan [catatan implementasinya](docs/perombakan-kelas5/CATATAN_IMPLEMENTASI.md); tata letak 16:9 dan dasbor admin baru direncanakan di Dok 3 |

## Ukuran aset wajib

Ukuran gambar per slot ditetapkan di `Config\Gelita::$assetSizes`. Slot bertanda "ya" ditolak saat diunggah bila ukurannya tidak **tepat** sama; slot lain hanya ditandai "ukuran lain" di **Media → Kelengkapan aset** (`/admin/media/kelengkapan`), yang juga mendaftar slot yang masih kosong. Permainan tetap berjalan tanpa gambar-gambar ini (gradien, monogram, gambar contoh).

| Slot | Ukuran (px) | Ditolak bila lain |
|---|---|---|
| Latar layar `bg.*` (`bg.loading`, `bg.welcome`, `bg.auth`, `bg.intro`, `bg.map`, `bg.reflection`, `bg.{wilayah}.region`) | 1920 × 1080 | ya |
| Frame pose tokoh `char.jaka.{pose}.{1–3}`, `char.kedu.{pose}.{1–3}` (PNG transparan) | 700 × 900 | ya |
| Logo halaman awal `ui.logo-hero` | 1600 × 600 | ya |
| Tombol Mulai `ui.btn-start`, `ui.btn-start.en` | 720 × 240 | ya |
| Adegan Cari Objek `challenge.{tmg,mgl,wnb}-4.scene` | 1280 × 720 | ya |
| Gambar Susun Gambar `challenge.item.{tmg,mgl,wnb}-1-…` | 1200 × 1200 | ya |
| Gambar pernyataan Benar atau Salah `challenge.item.{tmg,mgl,wnb}-3-…` | 480 × 360 | ya |
| Benda Cari Objek `challenge.item.{tmg,mgl,wnb}-4-…` (latar transparan) | 240 × 240 | ya |
| Peta wilayah `map.region.{wilayah}` | 1400 × 900 | tidak |
| Lencana wilayah `reward.badge.{wilayah}` | 320 × 320 | tidak |
| Gambar sampul video Pustaka | 960 × 640 (anjuran) | tidak |

`ui.logo` dan `map.kedu` berukuran bebas. Daftar 45 gambar tantangan beserta isinya ada di [`docs/bank-soal/README.md`](docs/bank-soal/README.md#gambar-tantangan-45-slot).

## Audio bawaan

Seluruh audio ikut di repositori dan dibangkitkan oleh [`tools/audio/`](tools/audio/README.md):

- **Musik gamelan** (`public/assets/audio/music/`): `map`, `region`, `challenge`, komposisi dan sintesis orisinal.
- **Efek suara** (`public/assets/audio/sfx/`): `click`, `correct`, `wrong`, `lock`, `shard`, `region-done`.
- **Narasi** (`public/assets/audio/narasi/{id,en}/`): 100 rekaman per bahasa (88 baris naskah + 12 petunjuk Cari Objek), suara sintetis (TTS) yang tercatat sebagai `production_method = tts`. Dipasang dengan `php spark gelita:narration:import` dan disetujui di **Konten → Narasi**; rekaman pengisi suara dapat menggantikannya dengan nama berkas yang sama.

Rincian, spesifikasi berkas, dan lisensi model suara ada di [`docs/audio.md`](docs/audio.md).

## Catatan keamanan

- Jangan pernah commit atau membagikan `.env`; berkas ini memuat kredensial dan `encryption.key`.
- `gelita.ipSalt` wajib diisi string acak panjang per pemasangan; IP mentah tidak disimpan di data penelitian.
- Server sekolah wajib memakai `CI_ENVIRONMENT = production`. Di `development`, Debug Toolbar menyimpan potret setiap request ke `writable/debugbar/` (field kata sandi disamarkan `App\Filters\DebugToolbar`).
- Web server harus mengarah ke folder `public/`, bukan root proyek.
- Sesi memakai `DatabaseHandler` (tabel `ci_sessions`). `App\Libraries\HashedIpSessionHandler` (penyimpan hash IP) **belum dipasang**: kelas itu mewarisi `MySQLiHandler`, sedangkan `Services::session()` hanya memilih handler per platform bila driver persis `DatabaseHandler`, sehingga driver kustom gagal di database uji SQLite. Karena itu `ci_sessions.ip_address` masih berisi IP mentah; isi tabel ini tidak ikut backup.

## Lisensi dan kredit

- Daftar sekolah resmi berasal dari Data Induk Pendidikan lewat cermin [`bahrye/api-sekolah`](https://github.com/bahrye/api-sekolah) (MIT); lihat [`docs/sekolah/README.md`](docs/sekolah/README.md).
- Howler.js 2.2.4 (MIT) dan font Cinzel, Plus Jakarta Sans, IBM Plex Mono (SIL OFL 1.1) di-host sendiri; versi dan lisensinya di [`public/assets/vendor/README.md`](public/assets/vendor/README.md).
- Model suara narasi (Mimic 3 `jv_ID`, CC BY-SA 4.0; suara Piper) beserta syarat atribusinya tercantum di [`docs/audio.md`](docs/audio.md#lisensi-dan-kredit).
- Kredit gambar dan video Pustaka Kedu tercantum per media di [`docs/pustaka/data/`](docs/pustaka/data/).
