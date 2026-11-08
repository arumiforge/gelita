# GELITA

**GELITA** (Game Edukasi Literasi dan Etnopedagogi Kedu) adalah game literasi membaca berbasis budaya Kedu untuk siswa **kelas 5 SD/MI**, sekaligus instrumen penelitian. Pemain berperan sebagai Jaka, dibimbing Mbah Kedu, memulihkan **12 Serpihan Cahaya** di tiga wilayah eks-Karesidenan Kedu. Setiap jawaban, percobaan, petunjuk, pemutaran audio, dan perpindahan layar dicatat sebagai data mentah penelitian.

Aplikasi dibangun dengan **CodeIgniter 4.7** (PHP 8.2+, MariaDB 10.6+ / MySQL 8), dirender server dengan HTML5 + CSS3; JavaScript (ES modules tanpa bundler dan tanpa framework) hanya menambah perilaku. Area game dwibahasa (Indonesia/Inggris), panel admin berbahasa Indonesia.

Sekilas (keadaan sesudah perombakan kelas 5, Dok 1–3):

| | |
|---|---|
| Pemain | siswa kelas 5 SD/MI, satu tahap bermain (tanpa fase *pretest/posttest*) |
| Permainan | 3 wilayah × 4 tantangan (Susun Gambar, Isi Rumpang, Benar atau Salah, Cari Objek) = 12 tantangan = 12 Serpihan Cahaya; satu variasi soal acak per pengerjaan; paling banyak 3 kesempatan per soal |
| Tampilan game | panggung 16:9 tetap (1280 × 720 dp) yang diskalakan utuh ke layar, tanpa gulir halaman; tombol layar penuh di HUD |
| Data | hanya data mentah (percobaan, jawaban, event, perangkat), tanpa skor olahan; dibaca di **Data mentah** atau diunduh XLSX/CSV-ZIP, plus laporan PDF per siswa |
| Panel admin | dasbor netral bersidebar dengan tabel server-side, filter, dan Unduh CSV per tabel |
| Command utama | `gelita:reset` (kosongkan data uji coba), `gelita:mockups` (gambar contoh), `gelita:simulasi` (siswa simulasi untuk pengembangan) |
| Ukuran | 29 migration → 29 tabel + `migrations`; 487 uji PHPUnit |

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
- Setiap soal paling banyak **3 percobaan** (`Config\Gelita::$maxTries`). Setiap percobaan tercatat satu baris di `item_tries`; pengerjaan ditutup sebagai `solved` atau `tries_exhausted`, lalu diberi bintang. Klien mengirim nomor cek (`check_no`), sehingga cek dari tab lama atau kiriman ulang ditolak `ATTEMPT_CHANGED` tanpa memakan kesempatan.
- Alur bercerita dari awal sampai akhir: halaman awal → `/mulai` → pendaftaran → cerita pembuka → Peta Kedu → Kenali wilayah → dialog pembuka wilayah → tantangan → wilayah tuntas → Pustaka → … → penutup → Balai Refleksi. Naskahnya 91 baris dwibahasa ([`docs/naskah-cerita.md`](docs/naskah-cerita.md)).
- **Pustaka Kedu**: 30 halaman bacaan dwibahasa dengan 48 gambar/video, terbuka per wilayah setelah wilayahnya tuntas ([`docs/pustaka/README.md`](docs/pustaka/README.md)).
- Pendaftaran tiga langkah (`/daftar/1`–`3`) dengan centang persetujuan siswa dan kata sandi kuat (bagian dari literasi keamanan digital). Siswa dari Jawa Tengah memilih sekolah lewat **NPSN** dari daftar sekolah resmi ([`docs/sekolah/README.md`](docs/sekolah/README.md)).
- Semua layar game berada di **panggung 16:9** (`#stage`, 80 × 45 rem = 1280 × 720 dp) yang diperbesar atau diperkecil utuh mengikuti layar, dengan bingkai gelap di sisa layar; teks panjang bergulir di dalam panelnya, halaman tidak pernah bergulir. HUD punya tombol layar penuh. Tata letaknya di [`docs/05_VIEW_UI.md`](docs/05_VIEW_UI.md).
- Game hanya dimainkan mendatar; ponsel/tablet yang dipegang tegak melihat layar "Putar perangkatmu". Dapat dipasang di layar utama (manifest + service worker yang hanya menampilkan halaman offline).
- Audio bawaan lengkap: musik gamelan, efek suara, dan narasi ID/EN (lihat [Audio bawaan](#audio-bawaan)).

**Data penelitian**

- Server adalah sumber kebenaran: benar/salah, bintang, serpihan, dan status buka/kunci dihitung server; kunci jawaban tidak pernah dikirim ke browser.
- Data mentah: percobaan per soal (`item_tries`), jawaban, petunjuk, event permainan (`game_event_logs`, mis. `screen_viewed`, `answer_selected`, `viewport_changed`), pemakaian audio (`audio_usage_events`), dan perangkat siswa (`device_logs`, dari User-Agent + Client Hints). Event yang gagal terkirim disimpan sementara di browser dan dikirim ulang ke sesi yang sama.
- Tidak ada skor olahan atau chart di panel; pengolahan dilakukan peneliti dari ekspor.

**Panel admin** (`/admin`)

- Dasbor SaaS netral (tidak bertema permainan, tanpa chart): sidebar 248 px yang menyempit jadi ikon di bawah 1280 px, topbar dengan breadcrumb, cari siswa, dan menu akun.
- Tabel formal server-side: header dan kolom pertama lengket, urut per kolom, 25/50/100 baris + total, filter dengan chip, dan **Unduh CSV** per tabel (nilai mentah, filter yang sama).
- Ringkasan (KPI hitungan + aktivitas dan progres terbaru), Siswa + detail bertab (profil, sesi & perangkat, progres 12 tantangan, pengerjaan, jawaban dengan riwayat percobaan, navigasi, bantuan, kritik & saran), Sesi bermain + linimasa event, **Data mentah** `/admin/data/{dimensi}`, dan Kritik & saran.
- Ekspor data mentah XLSX atau CSV-ZIP dengan Kamus Data, berlabel atau berkode (guru: selalu anonim, hanya sekolahnya, tanpa Event Mentah dan kunci jawaban), ekspor per siswa, dan laporan PDF satu siswa.
- Khusus admin: Tantangan (tabel 12 tantangan + editor bertab Info/Variasi/Posisi benda), impor bank soal, narasi, **Gambar & mockup** (status Asli/Gambar contoh/Kosong, unggah per slot dengan jenis berkas yang diizinkan, pratinjau diperbesar dan gerakan tokoh; ikon aplikasi, latar halaman akun, dan latar slide cerita juga diganti dari panel), Kelengkapan aset, Penelitian dengan **daftar siap penelitian**, Rilis, Sekolah, Staf, dan Tata kelola (penghapusan berpratinjau, audit, retensi).
- Guru hanya melihat menu dan data sekolahnya, tanpa kolom identitas siswa.
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
   - `DatabaseSeeder` mengisi daftar sekolah resmi, akun `admin`, pengaturan bawaan (indikator, rilis, studi), tiga wilayah, slot media, 12 tantangan dan 91 baris cerita, lalu `KontenKelas5Seeder` mengimpor Pustaka Kedu, bank soal kelas 5, dan 45 gambar contoh.
   - `gelita:narration:import` memasang 103 rekaman narasi per bahasa sebagai audio **draft**. Setujui di panel: **Rekaman narasi → Setujui semua rekaman Indonesia / Inggris**.

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
4. `php spark gelita:narration:import`, lalu setujui narasi draft di **Rekaman narasi**.
5. `php spark gelita:content:verify`.
6. Ganti 45 gambar contoh dengan gambar asli di panel (**Gambar & mockup**), sampai **daftar siap penelitian** di **Penelitian** (`/admin/studi`) hijau: tanpa gambar contoh, narasi disetujui, dan `gelita:content:verify --siap-penelitian` lolos.
7. Uji asap (daftar "Uji manual" di `08_DEPLOYMENT.md`), lalu sebelum pengambilan data penelitian: buat backup, lalu `php spark gelita:reset` dengan akun migrasi.

Tidak ada prosedur pembaruan dari instalasi lama: setiap rilis dipasang sebagai instalasi baru. Pembaruan kode berikutnya pada server yang sudah terpasang memakai `deploy/linux/deploy.sh` atau `deploy/windows/deploy.ps1` (menolak berjalan bila ada kelas aktif, backup, mode pemeliharaan, pull, composer, migrate, impor sekolah, bersihkan cache, naikkan versi aset, lalu membuka situs hanya bila `gelita:content:verify` lolos). Backup dan `gelita:retention:run` dijadwalkan harian.

## Command `gelita:*`

Semua command mengembalikan kode keluar 1 saat gagal, sehingga dapat dipakai di skrip. Opsi bernilai menerima `--opsi nilai` maupun `--opsi=nilai`. Bantuan per command: `php spark help gelita:reset`.

| Command | Fungsi |
|---|---|
| `gelita:bank:import <file> [--dry-run] [--staff <username>]` | Mengimpor workbook XLSX (bank soal atau Pustaka) dalam satu transaction; `--dry-run` hanya memvalidasi. Sama dengan **Konten → Impor soal dari Excel**. |
| `gelita:content:verify [--strict] [--siap-penelitian]` | Memeriksa 3 wilayah × 4 tantangan, variasi, aturan bank soal, kunci, dan media. `--strict`: peringatan juga gagal; `--siap-penelitian`: gambar contoh yang masih dipakai menjadi galat. |
| `gelita:katalog:impor [--sumber=FOLDER] [--dry-run] [--timpa] [--kunci=POLA,POLA]` | Installs the images of the Katalog Aset (`gelita-katalog/`) into every image slot: logo, buttons, language icons, app icons, backgrounds, story slides, maps, badges, pins, lanterns, avatars, character frames, challenge images, and Pustaka illustrations (lossy WebP, `app.*` as PNG). Images an admin uploaded are kept unless `--timpa`; `--kunci` limits a run (and `--timpa`) to matching slots, e.g. `char.jaka.*`. Safe to run again. See `docs/08_DEPLOYMENT.md`. |
| `gelita:library:thumbnails [--force]` | Mengunduh thumbnail video luar Pustaka (YouTube, Vimeo, Google Drive) dan memasangnya sebagai poster; butuh HTTPS keluar. |
| `gelita:media:scan [--check-only]` | Menyinkronkan `public/assets` dengan tabel `media_assets` dan melaporkan berkas yang hilang atau berubah. |
| `gelita:mockups [--dry-run] [--paksa] [--node=tmg-4]` | Membuat gambar contoh untuk slot gambar tantangan yang belum berisi gambar asli; gambar asli tidak pernah ditimpa. |
| `gelita:narration:import [--locale=id\|en] [--dry-run]` | Mengimpor rekaman di `public/assets/audio/narasi/{id,en}/` sebagai audio draft; berkas yang tidak berubah dilewati. |
| `gelita:reset [--dry-run] [--ya] [--paksa] [--pemeliharaan] [--tanpa-bank] [--bank=JALUR] [--tanpa-mockup] [--hapus-guru]` | Mengosongkan data uji coba dan pengaturan, membangun ulang 12 tantangan, dan mengimpor ulang bank soal; Pustaka, cerita, media, audio, sekolah resmi, dan akun staf tetap. Butuh akun berhak TRUNCATE. Data tidak dapat dikembalikan: selalu `--dry-run` dan backup dulu. |
| `gelita:retention:run` | Retensi harian: sesi menganggur → `paused`, pengerjaan menggantung → `abandoned`, berkas ekspor kedaluwarsa dibuang, data yang melewati masa simpan hanya dibuatkan pratinjau penghapusan. |
| `gelita:simulasi [--siswa=N] [--seed=N] [--mulai=YYYY-MM-DD]` | Membuat siswa simulasi `sim_NNN` (bawaan 12, paling banyak 500) yang bermain lewat layanan permainan sungguhan, untuk mengembangkan dan menguji panel, Data mentah, dan ekspor. Ditolak di production, tanpa studi aktif, atau bila studi aktif sudah berisi siswa sungguhan. Benih (`--seed`) yang sama pada database awal yang sama memberi isi yang sama. |
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
| `docs/` | dokumentasi, sumber bank soal, Pustaka, dan daftar sekolah; rencana yang sudah selesai di `docs/arsip/` |
| `gelita-katalog/` | Katalog Aset tim: gambar, gambar rujukan, prompt, dan arahan gaya (buka `index.html`); dipasang ke permainan dengan `gelita:katalog:impor`. Internal: memuat tanda benda pengecoh, jadi tidak pernah di bawah `public/` |
| `tests/` | suite PHPUnit |
| `tools/audio/` | generator musik, efek suara, dan narasi |
| `tools/qa/` | skrip uji peramban Playwright: `stage-check.mjs` (panggung 16:9), `challenge-races.mjs` (aturan kesempatan dan cek bersamaan), `a11y-check.mjs` (keyboard dan pembaca layar di layar tantangan), dan `nav-timing.mjs` (waktu pindah layar dan ganti bahasa di jaringan acuan; `node tools/qa/nav-timing.mjs --serve`, rencana paket aset Tahap 0) |

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
| [`docs/audio.md`](docs/audio.md) | musik, efek suara, narasi pengisi suara |
| [`docs/naskah-cerita.md`](docs/naskah-cerita.md) | naskah cerita dwibahasa dan konvensi rekaman |
| [`docs/bank-soal/README.md`](docs/bank-soal/README.md) | bank soal kelas 5, aturan, 45 slot gambar tantangan |
| [`docs/pustaka/README.md`](docs/pustaka/README.md) | isi Pustaka Kedu |
| [`docs/sekolah/README.md`](docs/sekolah/README.md) | daftar sekolah resmi Jawa Tengah |
| [`docs/perombakan-kelas5/`](docs/perombakan-kelas5/) | rencana perombakan kelas 5 (Dok 1–3: fondasi data, tantangan dan bank soal, panggung 16:9 dan panel admin) dan [catatan implementasinya](docs/perombakan-kelas5/CATATAN_IMPLEMENTASI.md) |
| [`docs/PROGRESS.md`](docs/PROGRESS.md) | daftar pekerjaan selesai dan terbuka, serta branch yang sedang berjalan |
| [`docs/rencana-paket-aset.md`](docs/rencana-paket-aset.md) | rencana paket aset (unduh sekali), masih berjalan |
| [`docs/arsip/`](docs/arsip/README.md) | rencana yang sudah selesai (perbaikan tampilan, katalog aset), disimpan sebagai catatan |
| [`gelita-katalog/README.md`](gelita-katalog/README.md) | Katalog Aset: gambar, rujukan, dan prompt |

## Ukuran aset wajib

Ukuran gambar per slot ditetapkan di `Config\Gelita::$assetSizes`. Slot bertanda "ya" ditolak saat diunggah bila ukurannya tidak **tepat** sama; slot lain hanya ditandai "ukuran lain" di **Konten → Kelengkapan aset** (`/admin/media/kelengkapan`), yang juga mendaftar slot yang masih kosong. Permainan tetap berjalan tanpa gambar-gambar ini (gradien, monogram, gambar contoh).

| Slot | Ukuran (px) | Ditolak bila lain |
|---|---|---|
| Latar layar `bg.*` (`bg.loading`, `bg.welcome`, `bg.auth`, `bg.intro`, `bg.map`, `bg.reflection`, `bg.{wilayah}.region`) | 1920 × 1080 | ya |
| Frame pose tokoh `char.jaka.{pose}.{1–24}`, `char.kedu.{pose}.{1–24}` (WebP/PNG transparan; sampai 24 gambar per pose, diputar berulang) | 700 × 900 | ya |
| Logo halaman awal `ui.logo-hero` | 1600 × 600 | ya |
| Tombol Mulai `ui.btn-start`, `ui.btn-start.en` | 720 × 360 | ya |
| Adegan Cari Objek `challenge.{tmg,mgl,wnb}-4.scene` | 1280 × 720 | ya |
| Gambar Susun Gambar `challenge.item.{tmg,mgl,wnb}-1-…` | 1200 × 1200 | ya |
| Gambar pernyataan Benar atau Salah `challenge.item.{tmg,mgl,wnb}-3-…` | 480 × 360 | ya |
| Benda Cari Objek `challenge.item.{tmg,mgl,wnb}-4-…` (latar transparan) | 240 × 240 | ya |
| Peta wilayah `map.region.{wilayah}` | 1400 × 900 | tidak |
| Lencana wilayah `reward.badge.{wilayah}` | 320 × 320 | tidak |
| Gambar sampul video Pustaka | 960 × 640 (anjuran) | tidak |

`ui.logo` dan `map.kedu` berukuran bebas. Ukuran **tampil** tiap slot di panggung 16:9 (dalam dp pada kanvas 1280 × 720) ada di `Config\Gelita::$assetDisplaySizes` dan ikut ditampilkan di **Gambar & mockup** (`/admin/media`); angka itu keterangan untuk ilustrator, bukan aturan unggah. Daftar 45 gambar tantangan beserta isinya ada di [`docs/bank-soal/README.md`](docs/bank-soal/README.md#gambar-tantangan-45-slot).

## Audio bawaan

Seluruh audio ikut di repositori dan dibangkitkan oleh [`tools/audio/`](tools/audio/README.md):

- **Musik gamelan** (`public/assets/audio/music/`): `map`, `region`, `challenge`, komposisi dan sintesis orisinal.
- **Efek suara** (`public/assets/audio/sfx/`): `click`, `correct`, `wrong`, `lock`, `shard`, `region-done`.
- **Narasi**: 103 rekaman per bahasa (91 baris naskah + 12 petunjuk Cari Objek), direkam pengisi suara. Tidak ikut di repositori (rekaman TTS lama dihapus 17 Oktober 2026). Naskahnya diunduh sebagai PDF di **Rekaman narasi**, rekamannya diunggah di sana (atau diletakkan di `public/assets/audio/narasi/{id,en}/` lalu `php spark gelita:narration:import`) dan disetujui.

Rincian, spesifikasi berkas, dan lisensi model suara ada di [`docs/audio.md`](docs/audio.md).

## Catatan keamanan

- Jangan pernah commit atau membagikan `.env`; berkas ini memuat kredensial dan `encryption.key`.
- `gelita.ipSalt` wajib diisi string acak panjang per pemasangan; IP mentah tidak disimpan di data penelitian.
- Server sekolah wajib memakai `CI_ENVIRONMENT = production`. Di `development`, Debug Toolbar menyimpan potret setiap request ke `writable/debugbar/` (field kata sandi disamarkan `App\Filters\DebugToolbar`).
- Web server harus mengarah ke folder `public/`, bukan root proyek.
- Sesi memakai `DatabaseHandler` (tabel `ci_sessions`). `App\Libraries\HashedIpSessionHandler` (penyimpan hash IP) **belum dipasang**: kelas itu mewarisi `MySQLiHandler`, sedangkan `Services::session()` hanya memilih handler per platform bila driver persis `DatabaseHandler`, sehingga driver kustom gagal di database uji SQLite. Karena itu `ci_sessions.ip_address` masih berisi IP mentah; isi tabel ini tidak ikut backup.

## Lisensi dan kredit

- Daftar sekolah resmi berasal dari Data Induk Pendidikan lewat cermin [`bahrye/api-sekolah`](https://github.com/bahrye/api-sekolah) (MIT); lihat [`docs/sekolah/README.md`](docs/sekolah/README.md).
- Howler.js 2.2.4 (MIT) dan font Marcellus, Plus Jakarta Sans, IBM Plex Mono (SIL OFL 1.1) di-host sendiri; versi dan lisensinya di [`public/assets/vendor/README.md`](public/assets/vendor/README.md).
- Model suara narasi (Mimic 3 `jv_ID`, CC BY-SA 4.0; suara Piper) beserta syarat atribusinya tercantum di [`docs/audio.md`](docs/audio.md#lisensi-dan-kredit).
- Kredit gambar dan video Pustaka Kedu tercantum per media di [`docs/pustaka/data/`](docs/pustaka/data/).

## Field sessions (research revision 10, part 2)

With Mode penelitian on (per study, off by default), the game opens only inside a field session: students type the session token, get participant codes like `S03-A07`, and see only the regions of that meeting; researchers run sessions on `/admin/sesi-lapangan` and watch them on `/admin/pemantauan`. Progress belongs to the student across sessions (`participant_progress`). Test accounts for validators: `php spark gelita:uji:akun`. Details: `docs/07_FEATURE_INTEGRATION.md` and `docs/revisi-10-analitik/28_IMPLEMENTASI_SESI_LAPANGAN.md`.

## Research data (research revision 10, part 3)

Event types live in `App\Libraries\EventCatalog`. The ketua's download (`/admin/ekspor`) always belongs to one study and carries the Kamus Data sheet (`ResearchDictionary`, `kamus-1.0`); the admin's technical log is `/admin/log-teknis`. Field sheets L1–L6 are typed into the template of each field session and uploaded back (`FieldSheetSpec`, `lembar-1.0`); L7 never reaches the server. Before migrating a server with this code: `php spark gelita:log:hitung` (see `docs/08_DEPLOYMENT.md`).
