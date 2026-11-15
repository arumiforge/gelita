# GELITA

**GELITA** (Game Edukasi Literasi dan Etnopedagogi Kedu) adalah game literasi membaca berbasis budaya Kedu untuk siswa **kelas 5 SD/MI**, sekaligus instrumen penelitian. Pemain berperan sebagai Jaka, dibimbing Mbah Kedu, memulihkan **15 Serpihan Cahaya** di tiga wilayah eks-Karesidenan Kedu. Setiap jawaban, percobaan, petunjuk, pemutaran audio, dan perpindahan layar dicatat sebagai data mentah penelitian.

Aplikasi dibangun dengan **CodeIgniter 4.7** (PHP 8.2+, MariaDB 10.6+ / MySQL 8), dirender server dengan HTML5 + CSS3; JavaScript (ES modules tanpa bundler dan tanpa framework) hanya menambah perilaku. Area game dwibahasa (Indonesia/Inggris), panel admin berbahasa Indonesia.

Sekilas (keadaan 15 November 2026, sesudah perombakan kelas 5 dan revisi penelitian 10):

| | |
|---|---|
| Pemain | siswa kelas 5 SD/MI; dalam mode penelitian bermain di dalam sesi lapangan, dengan tes awal, tes akhir, angket, dan skala sikap di modul tes (`/tes`) |
| Permainan | 3 wilayah × 5 tantangan (Susun Gambar, Isi Rumpang, Benar atau Salah, Cari Objek, Skenario Digital) = 15 tantangan = 15 Serpihan Cahaya; variasi `a`/`b` ditambah butir bersama `x`; paling banyak 3 kesempatan |
| Tampilan game | panggung 16:9 tetap (1280 × 720 dp) yang diskalakan utuh ke layar, tanpa gulir halaman; tombol layar penuh di HUD |
| Data | data mentah (percobaan, jawaban, event, perangkat) dibaca di **Data mentah** atau diunduh per studi; nilai turunan untuk analisis (waktu aktif, tebakan cepat, skor, indeks) dihitung terpisah di lapis data turunan dan ikut unduhan sebagai lembar `turunan_*` |
| Panel admin | dasbor netral bersidebar dengan tabel server-side, filter, dan Unduh CSV per tabel |
| Command utama | `gelita:reset` (kosongkan data uji coba), `gelita:mockups` (gambar contoh), `gelita:simulasi` (siswa simulasi untuk pengembangan) |
| Ukuran | 44 migration, 60 tabel; 1266 uji PHPUnit |

## Fitur utama

**Permainan**

- Tiga wilayah yang terbuka berurutan: **Temanggung** (mudah) → **Magelang** (sedang) → **Wonosobo** (sulit). Tiap wilayah punya 5 tantangan berurutan:

  | Urutan | Tantangan | Engine | Temanggung | Magelang | Wonosobo |
  |---:|---|---|---|---|---|
  | 1 | Susun Gambar | `puzzle` | 3 × 3 kotak | 4 × 4 kotak | 4 × 4 jigsaw |
  | 2 | Isi Rumpang | `rumpang` | 4 rumpang + 2 pengecoh | 5 rumpang + 2 pengecoh | 6 rumpang + 2 pengecoh |
  | 3 | Benar atau Salah | `boleh` | 4 pernyataan + kartu jangkar (2) | 6 pernyataan + kartu jangkar (2) | 6 pernyataan + kartu jangkar (2) |
  | 4 | Cari Objek | `cari` | babak 1: 4 dicari + 4 pengecoh; babak 2 (Periksa Unggahan): 4 petunjuk, 8 bagian ketuk | 4 + 6; 4 petunjuk, 10 bagian | 4 + 8; 4 petunjuk, 12 bagian |
  | 5 | Skenario Digital | `skenario` | 4 skenario, 3 tindakan, 3 alasan | 4 skenario, 4 tindakan, 4 alasan | 4 skenario, 4 tindakan, 5 alasan, dua langkah |

- Bank soal penelitian (revisi 10): setiap tantangan bernilai punya variasi `a` dan `b` ditambah butir bersama `x` (kartu jangkar Benar atau Salah, Cari Objek babak 1), kode kesalahan untuk setiap pilihan salah, dan label berikut sumbernya. Di luar mode penelitian satu variasi dipilih acak; dalam mode penelitian pengerjaan pertama mengikuti buklet siswa (B1–B4). Jumlah per tingkat ditetapkan di `App\Libraries\DifficultyRules`; rinciannya di [`docs/bank-soal/README.md`](docs/bank-soal/README.md).
- Setiap soal paling banyak **3 percobaan** (`Config\Gelita::$maxTries`). Setiap percobaan tercatat satu baris di `item_tries`; pengerjaan ditutup sebagai `solved` atau `tries_exhausted`, lalu diberi bintang. Klien mengirim nomor cek (`check_no`), sehingga cek dari tab lama atau kiriman ulang ditolak `ATTEMPT_CHANGED` tanpa memakan kesempatan.
- Alur bercerita dari awal sampai akhir: halaman awal → `/mulai` → pendaftaran → cerita pembuka → Peta Kedu → Kenali wilayah → dialog pembuka wilayah → tantangan → wilayah tuntas → Pustaka → … → penutup → Balai Refleksi. Naskahnya dwibahasa ([`docs/naskah-cerita.md`](docs/naskah-cerita.md)).
- **Pustaka Kedu**: 30 halaman bacaan dwibahasa dengan 48 gambar/video, terbuka per wilayah setelah wilayahnya tuntas ([`docs/pustaka/README.md`](docs/pustaka/README.md)).
- Pendaftaran tiga langkah (`/daftar/1`–`3`) dengan centang persetujuan siswa dan kata sandi kuat (bagian dari literasi keamanan digital). Siswa dari Jawa Tengah memilih sekolah lewat **NPSN** dari daftar sekolah resmi ([`docs/sekolah/README.md`](docs/sekolah/README.md)).
- Semua layar game berada di **panggung 16:9** (`#stage`, 80 × 45 rem = 1280 × 720 dp) yang diperbesar atau diperkecil utuh mengikuti layar, dengan bingkai gelap di sisa layar; teks panjang bergulir di dalam panelnya, halaman tidak pernah bergulir. HUD punya tombol layar penuh. Tata letaknya di [`docs/05_VIEW_UI.md`](docs/05_VIEW_UI.md).
- Game hanya dimainkan mendatar; ponsel/tablet yang dipegang tegak melihat layar "Putar perangkatmu". Dapat dipasang di layar utama (manifest + service worker). Dengan paket aset menyala, gambar, suara, dan kode game diunduh sekali lalu disajikan dari perangkat ([`docs/rencana-paket-aset.md`](docs/rencana-paket-aset.md)).
- Audio bawaan lengkap: musik gamelan, efek suara, dan narasi ID/EN (lihat [Audio bawaan](#audio-bawaan)).

**Data penelitian**

- Server adalah sumber kebenaran: benar/salah, bintang, serpihan, dan status buka/kunci dihitung server; kunci jawaban tidak pernah dikirim ke browser.
- Data mentah: percobaan per soal (`item_tries`), jawaban, petunjuk, event permainan (`game_event_logs`; daftar jenisnya di `App\Libraries\EventCatalog`), pemakaian audio (`audio_usage_events`), dan perangkat siswa (`device_logs`, dari User-Agent + Client Hints). Event yang gagal terkirim disimpan sementara di browser dan dikirim ulang ke sesi yang sama.
- Permainan tidak menghitung nilai analisis. Nilai turunan dihitung kalkulator murni di `app/Libraries/Derived/` ke tabel `derived_*` per putaran hitung, dan ikut unduhan sebagai lembar `turunan_*`. Panel tidak menampilkan chart.

**Panel admin** (`/admin`)

- Dasbor SaaS netral (tidak bertema permainan, tanpa chart): sidebar 248 px yang menyempit jadi ikon di bawah 1280 px, topbar dengan breadcrumb, cari siswa, dan menu akun.
- Tabel formal server-side: header dan kolom pertama lengket, urut per kolom, 25/50/100 baris + total, filter dengan chip, dan **Unduh CSV** per tabel (nilai mentah, filter yang sama).
- Ringkasan (KPI hitungan + aktivitas dan progres terbaru), Siswa + detail bertab (profil, sesi & perangkat, progres 15 tantangan, pengerjaan, jawaban dengan riwayat percobaan, navigasi, bantuan, kritik & saran), Sesi bermain + linimasa event, **Data mentah** `/admin/data/{dimensi}`, dan Kritik & saran.
- **Unduh data** (`/admin/ekspor`, ketua peneliti): selalu milik satu studi, dengan lembar Kamus Data dan lembar `turunan_*`.
- Khusus admin: Tantangan (tabel 15 tantangan + editor bertab Info/Variasi/Posisi benda), impor bank soal, narasi, **Gambar & mockup** (status Asli/Gambar contoh/Kosong, unggah per slot dengan jenis berkas yang diizinkan, pratinjau diperbesar dan gerakan tokoh; ikon aplikasi, latar halaman akun, dan latar slide cerita juga diganti dari panel), Kelengkapan aset, Penelitian dengan **daftar siap penelitian**, Rilis, Sekolah, Staf, dan Tata kelola (penghapusan berpratinjau, audit, retensi).
- Tiga peran staf (`App\Libraries\StaffAccess`): **admin** (akun siswa, konten, rilis, sekolah, staf, audit, retensi, log teknis), **peneliti** (data penelitian siswa, sesi lapangan, pemantauan, atur ulang kata sandi lewat kode), dan **ketua peneliti** (semua hak peneliti, ditambah Data mentah, unduhan, pengaturan studi, dan konfirmasi permintaan data). Peran guru dihapus pada revisi 10.
- Otorisasi berlapis tiga (route → controller → service); halaman yang tidak menjadi hak suatu peran membalas `404`.

## Kebutuhan sistem

- PHP 8.2 atau lebih baru (PHP 8.3 disarankan) dengan ekstensi `intl`, `mbstring`, `json`, `mysqli`/`mysqlnd`, `curl`, `gd`, `zip`, `fileinfo`, `openssl`, `iconv`, `dom`, `xml`, `xmlwriter`, dan `simplexml`
- Composer 2.6 atau lebih baru
- MariaDB 10.6+ atau MySQL 8.0+
- Web server (Nginx + PHP-FPM di server; `php spark serve` untuk pengembangan)

Di Windows, Laragon (PHP 8.3 + MySQL) memenuhi semua kebutuhan ini. Library frontend (Howler.js) dan font di-host sendiri di `public/assets/`, tanpa CDN. Pengecualiannya hanya media **tautan** Pustaka (YouTube, Vimeo, Google Drive, Wikimedia Commons), yang dimuat dari domain asalnya.

## Mulai cepat (pengembangan)

Di Linux atau sesi awan, `tools/dev/serve.sh` mengerjakan langkah 1–4 sekaligus di MariaDB (tanpa impor narasi).

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

   - `migrate` menjalankan 44 migration (termasuk `ci_sessions`); jangan menjalankan `php spark session:migration`.
   - `DatabaseSeeder` mengisi daftar sekolah resmi, akun `admin`, pengaturan bawaan (indikator, rilis, studi), tiga wilayah, slot media, tantangan dan cerita, lalu `KontenKelas5Seeder` mengimpor Pustaka Kedu, bank soal kelas 5, dan gambar contoh.
   - `gelita:narration:import` mendaftarkan rekaman yang ada di `public/assets/audio/narasi/{id,en}/` sebagai audio **draft** (repositori tidak membawa rekaman narasi; lihat [Audio bawaan](#audio-bawaan)). Setujui di panel: **Rekaman narasi → Setujui semua rekaman Indonesia / Inggris**.

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
6. Ganti gambar contoh dengan gambar asli di panel (**Gambar & mockup**), sampai **daftar siap penelitian** di **Penelitian** (`/admin/studi`) hijau: tanpa gambar contoh, narasi disetujui, dan `gelita:content:verify --siap-penelitian` lolos.
7. Uji asap (daftar "Uji manual" di `08_DEPLOYMENT.md`), lalu sebelum pengambilan data penelitian: buat backup, lalu `php spark gelita:reset` dengan akun migrasi.

Tidak ada prosedur pembaruan dari instalasi lama: setiap rilis dipasang sebagai instalasi baru. Pembaruan kode berikutnya pada server yang sudah terpasang memakai `deploy/linux/deploy.sh` atau `deploy/windows/deploy.ps1` (menolak berjalan bila ada kelas aktif, backup, mode pemeliharaan, pull, composer, migrate, impor sekolah, bersihkan cache, naikkan versi aset, lalu membuka situs hanya bila `gelita:content:verify` lolos). Backup dan `gelita:retention:run` dijadwalkan harian.

## Command `gelita:*`

Semua command mengembalikan kode keluar 1 saat gagal, sehingga dapat dipakai di skrip. Opsi bernilai menerima `--opsi nilai` maupun `--opsi=nilai`. Bantuan per command: `php spark help gelita:reset`.

| Command | Fungsi |
|---|---|
| `gelita:bank:import <file> [--dry-run] [--staff <username>]` | Mengimpor workbook XLSX (bank soal atau Pustaka) dalam satu transaction; `--dry-run` hanya memvalidasi. Sama dengan **Konten → Impor soal dari Excel**. |
| `gelita:content:verify [--strict] [--siap-penelitian]` | Memeriksa tantangan setiap wilayah, variasi, aturan bank soal, kunci, dan media. `--strict`: peringatan juga gagal; `--siap-penelitian`: gambar contoh yang masih dipakai menjadi galat. |
| `gelita:katalog:impor [--sumber=FOLDER] [--dry-run] [--timpa] [--kunci=POLA,POLA]` | Installs the images of the Katalog Aset (`gelita-katalog/`) into every image slot: logo, buttons, language icons, app icons, backgrounds, story slides, maps, badges, pins, lanterns, avatars, character frames, challenge images, and Pustaka illustrations (lossy WebP, `app.*` as PNG). Images an admin uploaded are kept unless `--timpa`; `--kunci` limits a run (and `--timpa`) to matching slots, e.g. `char.jaka.*`. Safe to run again. See `docs/08_DEPLOYMENT.md`. |
| `gelita:library:thumbnails [--force]` | Mengunduh thumbnail video luar Pustaka (YouTube, Vimeo, Google Drive) dan memasangnya sebagai poster; butuh HTTPS keluar. |
| `gelita:media:scan [--check-only]` | Menyinkronkan `public/assets` dengan tabel `media_assets` dan melaporkan berkas yang hilang atau berubah. |
| `gelita:mockups [--dry-run] [--paksa] [--node=tmg-4]` | Membuat gambar contoh untuk slot gambar tantangan yang belum berisi gambar asli; gambar asli tidak pernah ditimpa. |
| `gelita:narration:import [--locale=id\|en] [--dry-run]` | Mengimpor rekaman di `public/assets/audio/narasi/{id,en}/` sebagai audio draft; berkas yang tidak berubah dilewati. |
| `gelita:narration:install [--dir=FOLDER] [--dry-run] [--timpa]` | Memasang rekaman narasi cadangan ElevenLabs dari `ai-studio/elevenlabs-voice/out/{id,en}/` ke `public/assets/audio/narasi/{id,en}/` dan mencatatnya di `produksi.json` (sha256 dan profil suara dari `voices.md`), supaya impor menyimpannya sebagai suara sintetis. Rekaman suara asli yang sudah ada dipertahankan kecuali `--timpa`. Lanjutkan dengan `gelita:narration:import`. |
| `gelita:narration:list --csv=BERKAS [--dir=FOLDER]` | Menulis daftar rekaman narasi (kode berkas, konteks, tokoh, teks Indonesia dan Inggris) sebagai CSV. Dengan `--dir`, dua kolom tambahan menandai rekaman yang sudah ada di `FOLDER/{id,en}/` (`ada`/`belum`). Dipakai untuk `ai-studio/elevenlabs-voice/lines.csv`; tidak mengubah basis data. |
| `gelita:sound:install [--dir=FOLDER] [--dry-run]` | Memasang musik dan efek suara buatan ElevenLabs dari `ai-studio/elevenlabs-sound/out/{music,sfx}/` ke `public/assets/audio/{music,sfx}/` dan mencatatnya di `public/assets/audio/produksi.json` (sha256 dan model dari `settings.md`). Nama yang tidak ada di `sounds.csv` tidak disalin. Lihat `ai-studio/elevenlabs-sound/README.md`. |
| `gelita:reset [--dry-run] [--ya] [--paksa] [--pemeliharaan] [--tanpa-bank] [--bank=JALUR] [--tanpa-mockup] [--hapus-guru]` | Mengosongkan data uji coba dan pengaturan, membangun ulang tantangan, dan mengimpor ulang bank soal; Pustaka, cerita, media, audio, sekolah resmi, dan akun staf tetap. Butuh akun berhak TRUNCATE. Data tidak dapat dikembalikan: selalu `--dry-run` dan backup dulu. |
| `gelita:retention:run` | Retensi harian: sesi menganggur → `paused`, pengerjaan menggantung → `abandoned`, berkas ekspor kedaluwarsa dibuang, data yang melewati masa simpan hanya dibuatkan pratinjau penghapusan. |
| `gelita:simulasi [--siswa=N] [--seed=N] [--mulai=YYYY-MM-DD]` | Membuat siswa simulasi `sim_NNN` (bawaan 12, paling banyak 500) yang bermain lewat layanan permainan sungguhan, untuk mengembangkan dan menguji panel, Data mentah, dan ekspor. Ditolak di production, tanpa studi aktif, atau bila studi aktif sudah berisi siswa sungguhan. Benih (`--seed`) yang sama pada database awal yang sama memberi isi yang sama. |
| `gelita:schools:import [--file=PATH] [--dry-run] [--link-existing]` | Memasang daftar sekolah resmi (NPSN) dari `app/Database/Seeds/data/sekolah-jateng.csv`; hanya menulis yang berubah, tidak pernah menghapus. |
| `gelita:staff:password <username>` | Mengatur ulang sandi staf ke sandi sementara yang wajib diganti saat masuk. |
| `gelita:aset:minify [--periksa] [--paksa]` | Builds the minified JS/CSS mirror in `public/assets/min/`; the sources stay unminified. `--periksa` only reports whether the mirror is fresh. |
| `gelita:instrumen:impor [--berkas tes\|angket\|sikap] [--dry-run]` | Loads the test, questionnaire and attitude-scale content from `app/Database/Seeds/data/instrumen/` (versioned, all-or-nothing checks); also run by `gelita:reset`. |
| `gelita:lembar:templat [--keluar=<folder>] [--l7] [--kolom=<file.json>]` | Writes blank field-sheet templates, or the column spec as JSON. Refuses to run in production. |
| `gelita:log:hitung` | Counts, without changing anything, the event and device rows migration `003600` deletes or renames. |
| `gelita:paket:bangun [--catatan "teks"] [--versi-aplikasi VERSI] [--tanpa-pustaka] [--deploy]` | Builds a new asset package (pictures, sound, game code) from every active asset. |
| `gelita:paket:periksa [kode-paket]` | Re-checks an asset package's files against its manifest. |
| `gelita:paket:bersihkan [--hapus kode[,kode]] [--coba]` | Removes asset-package files that no package uses. |
| `gelita:story:sync [--dry-run] [--timpa]` | Updates the story and dialogue lines of an installed server to `data/story.php`. |
| `gelita:turunan:hitung --studi=KODE [--cek]` | Computes a new derived run for a field-mode study; `--cek` only reports. |
| `gelita:turunan:ambang --studi=KODE --buka --alasan="…"` | Unlocks a study's locked thresholds and writes an audit row. |
| `gelita:uji:akun --buat [--nama=uji.x] \| --reset=uji.x \| --daftar` | Test accounts `uji.*` for the validators' test install; not for the research server. |

Jangan menjalankan `gelita:reset` di server tanpa backup dan tanpa keputusan peneliti.

## Uji

```bash
vendor/bin/phpunit --no-coverage
```

Suite penuh berisi 1266 uji, berjalan sekitar 6 sampai 7,5 menit, dan memakai lebih dari 7 GB memori dalam satu proses. Saat bekerja, jalankan uji perubahan saja (`--filter`, atau satu berkas uji) dan suite penuh sekali sebelum push. GitHub Actions (`.github/workflows/tests.yml`) menjalankan setiap berkas uji di prosesnya sendiri pada setiap pull request.

`phpunit.dist.xml` meminta laporan coverage; tanpa Xdebug/PCOV, PHPUnit memberi peringatan "No code coverage driver available" yang membuat kode keluar 1. Pakai `--no-coverage` bila driver coverage tidak terpasang.

Suite memakai SQLite in-memory (grup database `tests`) dengan skema tiruan di `tests/_support/`, bukan migration aplikasi, karena skema memakai fitur MariaDB/MySQL (`DATETIME(6)`). Alur HTTP ujung ke ujung diuji di atas MariaDB/MySQL sungguhan dengan database terpisah; kredensial CLI dapat dioper lewat `database_default_DSN='MySQLi://user:sandi@localhost/gelita_uji' php spark migrate`.

## Struktur repositori

| Folder | Isi |
|---|---|
| `.claude/` | aturan kerja, skill, subagen peninjau, dan hook Claude Code (lihat [Claude Code](#claude-code)) |
| `app/Commands/` | command `gelita:*` |
| `app/Config/` | konfigurasi; konstanta domain di `Gelita.php`, route di `Routes.php` |
| `app/Controllers/` | `Game/`, `Admin/`, `Api/` |
| `app/Database/` | 44 migration, seeder, data naskah (`Seeds/data/story.php`) dan sekolah |
| `app/Libraries/`, `app/Services/` | aturan bank soal (`BankRules`, `DifficultyRules`), impor, penilaian, ekspor, reset |
| `app/Models/`, `app/Entities/` | model per tabel |
| `ai-studio/` | ruang kerja alat AI lain, satu folder per alat: `chatgpt-images/` (gambar), `elevenlabs-voice/` (narasi cadangan), dan `elevenlabs-sound/` (musik dan efek suara). Tiap alat hanya menulis di foldernya; hasil yang diterima dipasang ke permainan oleh sesi Claude Code. Tidak dimuat permainan. Lihat `ai-studio/README.md` dan `AGENTS.md` |
| `app/Views/`, `app/Language/{id,en}/` | tampilan dan teks dwibahasa |
| `public/assets/` | CSS, JavaScript (`core/`, `game/`, `engines/`, `admin/`), audio, font, vendor; unggahan admin di `uploads/` (tidak dilacak Git) |
| `deploy/` | skrip deploy, backup, retensi, dan konfigurasi Nginx untuk Linux dan Windows |
| `docs/` | dokumentasi, sumber bank soal, Pustaka, dan daftar sekolah; rencana yang sudah selesai di `docs/arsip/` |
| `gelita-katalog/` | Katalog Aset tim: gambar, gambar rujukan, prompt, dan arahan gaya (buka `index.html`); dipasang ke permainan dengan `gelita:katalog:impor`. Internal: memuat tanda benda pengecoh, jadi tidak pernah di bawah `public/` |
| `tests/` | suite PHPUnit |
| `tools/dev/` | `serve.sh` (instalasi pengembangan di MariaDB dalam satu perintah: basis data, `.env`, migrasi, data awal, `php spark serve`) dan `cloud-setup.sh` (salinan induk skrip penyiapan lingkungan sesi awan) |
| `tools/qa/` | skrip uji peramban Playwright: `stage-check.mjs` (panggung 16:9), `challenge-races.mjs` (aturan kesempatan dan cek bersamaan), `a11y-check.mjs` (keyboard dan pembaca layar di layar tantangan), dan `nav-timing.mjs` (waktu pindah layar dan ganti bahasa di jaringan acuan; `node tools/qa/nav-timing.mjs --serve`, rencana paket aset Tahap 0); juga `admin-check.mjs` (panel staf per peran), `event-integrity.mjs` dan `event-requests.mjs` (data penelitian dan log), `package-check.mjs`, `package-screens.mjs`, dan `transition-check.mjs` (paket aset), `music-check.mjs`, `minify-check.mjs`, serta `load-test.mjs` dengan `load-students.php` (uji beban) |

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
| [`docs/bank-soal/README.md`](docs/bank-soal/README.md) | bank soal penelitian kelas 5, aturan per tingkat, variasi dan buklet |
| [`docs/pustaka/README.md`](docs/pustaka/README.md) | isi Pustaka Kedu |
| [`docs/sekolah/README.md`](docs/sekolah/README.md) | daftar sekolah resmi Jawa Tengah |
| [`docs/perombakan-kelas5/`](docs/perombakan-kelas5/) | rencana perombakan kelas 5 (Dok 1–3: fondasi data, tantangan dan bank soal, panggung 16:9 dan panel admin) dan [catatan implementasinya](docs/perombakan-kelas5/CATATAN_IMPLEMENTASI.md) |
| [`docs/revisi-10-analitik/00_INDEKS.md`](docs/revisi-10-analitik/00_INDEKS.md) | revisi penelitian 10: urutan baca, status dokumen 01–38, dan [catatan keputusannya](docs/revisi-10-analitik/04_CATATAN_KEPUTUSAN.md) |
| [`docs/PROGRESS.md`](docs/PROGRESS.md) | daftar pekerjaan selesai dan terbuka, serta branch yang sedang berjalan |
| [`docs/cloud-sessions.md`](docs/cloud-sessions.md) | sesi awan Claude Code dengan beberapa akun: penyiapan per akun dan aturan serah terima |
| [`ai-studio/README.md`](ai-studio/README.md) | ruang kerja alat AI lain (gambar ChatGPT, narasi cadangan ElevenLabs) dan cara hasilnya dipasang |
| [`docs/rencana-paket-aset.md`](docs/rencana-paket-aset.md) | rencana paket aset (unduh sekali), masih berjalan |
| [`docs/rencana-log-asinkron.md`](docs/rencana-log-asinkron.md) | rencana pencatatan event asinkron dan uji bebannya |
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

`ui.logo` dan `map.kedu` berukuran bebas. Ukuran **tampil** tiap slot di panggung 16:9 (dalam dp pada kanvas 1280 × 720) ada di `Config\Gelita::$assetDisplaySizes` dan ikut ditampilkan di **Gambar & mockup** (`/admin/media`); angka itu keterangan untuk ilustrator, bukan aturan unggah. Gambar bank penelitian tercantum di sheet DAFTAR_MEDIA workbook bank soal; daftar gambar bank lama ada di [`docs/bank-soal/README.md`](docs/bank-soal/README.md#gambar-tantangan-daftar-bank-lama).

## Audio bawaan

Musik dan efek suara ikut di repositori sebagai berkas jadi. Generatornya (`tools/audio/`) dihapus 14 November 2026: audio kini dibuat di luar repositori.

- **Musik gamelan** (`public/assets/audio/music/`): `map`, `region`, `challenge`, komposisi dan sintesis orisinal.
- **Efek suara** (`public/assets/audio/sfx/`): `click`, `correct`, `wrong`, `lock`, `shard`, `region-done`.
- **Pengganti musik dan efek suara** (sejak 15 November 2026): dibuat dengan ElevenLabs seperti narasi cadangan, di `ai-studio/elevenlabs-sound/` (daftar `sounds.csv`), lalu dipasang dengan `php spark gelita:sound:install`, yang menulis `public/assets/audio/produksi.json`. Berkas lama tetap dipakai sampai penggantinya dipasang.
- **Narasi**: daftar rekamannya dibuat `php spark gelita:narration:list` (187 rekaman per bahasa di `ai-studio/elevenlabs-voice/lines.csv`, 15 November 2026); pengisi suara tetap pilihan pertama. Tidak ikut di repositori (rekaman TTS lama dihapus 17 Oktober 2026). Naskahnya diunduh sebagai PDF di **Rekaman narasi**, rekamannya diunggah di sana (atau diletakkan di `public/assets/audio/narasi/{id,en}/` lalu `php spark gelita:narration:import`) dan disetujui. Cadangannya suara sintetis ElevenLabs (keputusan 14 November 2026; tidak ada suara sintetis lain), dikerjakan di `ai-studio/elevenlabs-voice/` dan dipasang dengan `php spark gelita:narration:install`, yang menulis `produksi.json` supaya rekamannya tercatat sebagai suara sintetis. Sejak 15 November 2026 narasi cadangan itu dipakai karena pengisi suara tidak tersedia (Mbah Kedu sudah diterima; Narator dan Jaka menyusul).

Rincian, spesifikasi berkas, dan lisensi model suara ada di [`docs/audio.md`](docs/audio.md).

## Catatan keamanan

- Jangan pernah commit atau membagikan `.env`; berkas ini memuat kredensial dan `encryption.key`.
- `gelita.ipSalt` wajib diisi string acak panjang per pemasangan; IP mentah tidak disimpan di data penelitian.
- Server sekolah wajib memakai `CI_ENVIRONMENT = production`. Di `development`, Debug Toolbar menyimpan potret setiap request ke `writable/debugbar/` (field kata sandi disamarkan `App\Filters\DebugToolbar`).
- Web server harus mengarah ke folder `public/`, bukan root proyek.
- Sesi memakai `DatabaseHandler` (tabel `ci_sessions`). Dengan `gelita.researchPrivacy` menyala (bawaan mati), `App\Libraries\HashedIpSessionHandler` menyimpan hash IP sebagai ganti IP mentah di `ci_sessions.ip_address`; selama mati, kolom itu berisi IP mentah. Isi tabel ini tidak ikut backup.

## Lisensi dan kredit

- Daftar sekolah resmi berasal dari Data Induk Pendidikan lewat cermin [`bahrye/api-sekolah`](https://github.com/bahrye/api-sekolah) (MIT); lihat [`docs/sekolah/README.md`](docs/sekolah/README.md).
- Howler.js 2.2.4 (MIT) dan font Marcellus, Plus Jakarta Sans, IBM Plex Mono (SIL OFL 1.1) di-host sendiri; versi dan lisensinya di [`public/assets/vendor/README.md`](public/assets/vendor/README.md).
- Model suara narasi (Mimic 3 `jv_ID`, CC BY-SA 4.0; suara Piper) beserta syarat atribusinya tercantum di [`docs/audio.md`](docs/audio.md#lisensi-dan-kredit).
- Narasi cadangan Narator berbahasa Indonesia dibuat dengan suara sintetis ElevenLabs (elevenlabs.io) pada paket Free, yang hanya untuk penggunaan nonkomersial dan mewajibkan kredit kepada ElevenLabs; rinciannya di [`docs/audio.md`](docs/audio.md#lisensi-dan-kredit).
- Kredit gambar dan video Pustaka Kedu tercantum per media di [`docs/pustaka/data/`](docs/pustaka/data/).

## Field sessions (research revision 10, part 2)

With Mode penelitian on (per study, off by default), the game opens only inside a field session: students type the session token, get participant codes like `S03-A07`, and see only the regions of that meeting; researchers run sessions on `/admin/sesi-lapangan` and watch them on `/admin/pemantauan`. Progress belongs to the student across sessions (`participant_progress`). Test accounts for validators: `php spark gelita:uji:akun`. Details: `docs/07_FEATURE_INTEGRATION.md` and `docs/revisi-10-analitik/28_IMPLEMENTASI_SESI_LAPANGAN.md`.

## Research data (research revision 10, part 3)

Event types live in `App\Libraries\EventCatalog`. The ketua's download (`/admin/ekspor`) always belongs to one study and carries the Kamus Data sheet (`ResearchDictionary`, `kamus-1.1`); the admin's technical log is `/admin/log-teknis`. Field sheets L1–L6 are typed into the template of each field session and uploaded back (`FieldSheetSpec`, `lembar-1.0`); L7 never reaches the server. Before migrating a server with this code: `php spark gelita:log:hitung` (see `docs/08_DEPLOYMENT.md`).

## Research revision 10, part 4 (consent and data rights)

Coded consent per student, withdrawal with immediate deactivation, parents' data requests (file, confirm, run, checklist, minutes), one child's data copy, registration correction, study closure with identity replacement, retention from the study's closing, and hashed browser-session IPs behind `gelita.researchPrivacy`. All of it applies only to a study in field mode. Plan: `docs/revisi-10-analitik/30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md`; deployment checklist in `docs/08_DEPLOYMENT.md`.

## Research revision 10, parts 5 to 9

- **Part 5, question bank** (`31`): the research bank described under *Fitur utama*, built from `docs/bank-soal/data/` into the workbook and imported by `gelita:bank:import` or `gelita:reset`.
- **Part 6, Skenario Digital** (`32`): the fifth challenge of each region (`/tantangan/{wilayah}/5`, engine `skenario`), with three tries per scenario.
- **Part 7, research mode** (`33`): behind the study switch `field_mode`: no challenge clock on screen, a small-screen gate, first attempts resumed in a later game session, retakes only after the region is done, and input pauses logged. Thresholds are set per study on `/admin/studi/{id}/pengaturan`.
- **Part 8, test module** (`34`): `/tes` (pre-test, post-test, questionnaire, risk items) and `/refleksi/sikap` (attitude scale). Scores are stored raw; answer keys never reach the page.
- **Part 9, derived data layer** (`35`): analysis values are computed outside the game into `derived_*` tables per versioned run (`php spark gelita:turunan:hitung --studi=KODE`); a download recomputes when the raw data changed.

Reading order and the status of every document: `docs/revisi-10-analitik/00_INDEKS.md`.

## Claude Code

The repository is developed in Claude Code sessions, from more than one Claude account, and carries what those sessions need, so an account needs no plugin:

- `CLAUDE.md` and `.claude/rules/`: working rules, language rules, glossary.
- `.claude/skills/`: `account-setup` (first session of a new account), `continue-work` and `finish-step` (hand-over between sessions), `dev-install` (development server and browser checks), `new-migration`.
- `.claude/agents/gelita-reviewer.md`: a read-only reviewer for a diff.
- `.claude/hooks/`: a syntax check after a PHP file is edited, the language-file test after a language file is edited, and a guard against two full test runs at once.

Per account only the GitHub connection and the cloud environment are set up; which account plugins to turn off, and Claude Desktop on Windows, are in [`docs/cloud-sessions.md`](docs/cloud-sessions.md).

