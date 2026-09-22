# GELITA

**GELITA** (Game Edukasi Literasi dan Etnopedagogi Kedu) adalah aplikasi web edukasi berbasis CodeIgniter 4. Aplikasi ini dirancang sebagai game pembelajaran server-rendered, dengan dukungan antarmuka berbahasa Indonesia dan Inggris.

## Status pengembangan

Proyek berada pada **tahap 5 dari 8: View, UI, dan CSS**.

Tahap ini memberi wajah pada seluruh URL yang dibuka tahap 4, sesuai [`docs/05_VIEW_UI.md`](docs/05_VIEW_UI.md):

- **3 layout, 17 komponen bersama, 17 halaman game + 5 arena tantangan, dan 33 view admin** merender data sungguhan dari database. Alur siswa dapat ditelusuri dengan klik dari halaman sambutan, persetujuan, registrasi, intro, peta, dialog, kartu misi, kelima arena, sampai profil, pustaka, dan ganti sandi.
- **Enam berkas CSS** (`tokens`, `base`, `layout`, `components`, `game`, `admin`) tanpa framework CSS. Layar tantangan muat tanpa gulir pada 1366×768; seluruh halaman tetap terpakai pada tablet dan ponsel 390 px tanpa gulir horizontal.
- **Tanpa JavaScript tetap terbaca**: slide memakai `:target`, konfirmasi memakai `<details>`, panduan form memakai `:has()`, dan setiap chart admin punya visual cadangan dari server. Perilaku (AJAX, mesin arena, ECharts, Howler) menyusul pada tahap 6.
- **Aset di-host sendiri**: ECharts 6.1.0 dan Howler.js 2.2.4 di `public/assets/vendor/`, font Cinzel/Plus Jakarta Sans/IBM Plex Mono di `public/assets/fonts/` — tidak ada permintaan ke domain pihak ketiga. Selama gambar belum diunggah, view memakai pengganti (gradien, monogram) alih-alih gambar rusak.
- **`component()`** merender komponen dengan data yang dioper saja; lihat [*Catatan Implementasi Tahap 5*](docs/05_VIEW_UI.md#catatan-implementasi-tahap-5) untuk alasannya, penyesuaian controller yang dibutuhkan view, dan bug tahap 4 yang ikut diperbaiki.

Tetap berlaku dari tahap 4: registrasi dengan kata sandi kuat beserta dua metrik literasi keamanan digitalnya, otorisasi berlapis tiga (guru mendapat `404` untuk halaman khusus admin), bentuk respons API seragam, dan `safe_internal_url()` untuk setiap `redirect_to`.

Dua catatan ruang lingkup:

- **Tombol Periksa di arena belum berfungsi.** Markup, gaya, dan payload `<script type="application/json" id="challenge-data">` sudah lengkap; mesin arena dikerjakan tahap 6.
- **`ExportController` belum dapat membangun berkas.** `App\Services\ExportService` baru dipasang pada tahap 7. Sampai kelas itu ada, permintaan ekspor tetap tercatat di `data_exports` dan `audit_logs`, lalu langsung ditandai `failed` dengan alasan yang jelas — bukan dibiarkan menggantung di status `running`. `GovernanceController::runRetention()` sudah menjalankan dua pekerjaan yang penopangnya ada (menandai sesi menganggur `paused`, membuang berkas ekspor kedaluwarsa); pembersihan per `retention_days` menyusul bersama `RetentionService`.

### Perbaikan pasca-tahap 3

Tinjauan kompatibilitas sesudah tahap 3 menghasilkan beberapa koreksi. Semuanya bersifat perbaikan, bukan perubahan perilaku tahap 3:

- **Migration `003200`** menegakkan `NOT NULL` beserta default pada lima kolom metrik `challenge_attempts` (`first_pass_accuracy`, `final_accuracy`, `check_count`, `answer_change_count`, `audio_use_count`). `Forge` CodeIgniter hanya memaksakan `NOT NULL` saat `CREATE TABLE`; pada `ALTER` atribut `null` yang tidak disebut membuat kolom menjadi nullable, sehingga `003100` menghasilkan kelimanya sebagai `NULL`. `002100` dan `003100` tidak diubah — `003100` tetap koreksi kanonik untuk penamaan kolom.
- **`audio_src()`** tidak lagi menyaring `audio_assets.is_active`. Kolom itu tidak pernah ada (lihat migration `001100`); status tayang aset audio ditentukan `audio_assets.approval_status` dan `media_assets.is_active`.
- **`.gitignore`** mempersempit pola `vendor/` menjadi `/vendor/`. Pola lama ikut mengabaikan `public/assets/vendor/`, padahal ECharts dan Howler.js wajib di-host sendiri di repositori.
- **Templat `env`** kini dilacak Git, dan default sesi serta CSRF di `app/Config/Session.php` dan `app/Config/Security.php` disesuaikan dengan spesifikasi tahap 1–2.

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
   | `encryption.key` | bangkitkan dengan `php spark key:generate` |
   | `gelita.ipSalt` | string acak panjang; garam ini yang membuat hash IP tidak dapat dibalik. Jangan dibiarkan kosong |

3. Buat database bila belum ada, lalu jalankan migration:

   ```bash
   mysql -u root -p -e "CREATE DATABASE gelita CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   php spark migrate
   ```

4. Jalankan server pengembangan:

   ```bash
   php spark serve
   ```

## Migration

Migration dijalankan berurutan dari `000100` sampai `003200` dan menghasilkan 31 tabel (30 tabel domain + `migrations`).

Tiga migration terakhir perlu diperhatikan saat menelusuri riwayat skema `challenge_attempts`:

| Versi | Peran |
|---|---|
| `002100` | membuat tabel `challenge_attempts` dengan nama kolom awal `first_pass_rate` / `final_rate` |
| `003100` | koreksi kanonik: mengganti nama kedua kolom itu menjadi `first_pass_accuracy` / `final_accuracy` dan menambahkan `check_count`, `answer_change_count`, `audio_use_count` |
| `003200` | menegakkan `NOT NULL` + default pada kelima kolom di atas, termasuk mengisi baris lama yang masih `NULL` |

`003200` memanggil `resetDataCache()` sebelum memeriksa kolom. Tanpa itu, `BaseConnection` masih memakai daftar kolom yang di-cache dari sebelum `ALTER` milik `003100` pada proses `spark migrate` yang sama, sehingga seluruh pemeriksaan `fieldExists()` meleset dan migration berjalan tanpa efek.

`down()` milik `003200` mengembalikan kelima kolom ke keadaan nullable yang ditinggalkan `003100`, sehingga rollback berantai tetap dapat berjalan. Jangan melakukan rollback ke bawah `003100`: tahap 3 bergantung pada penamaan kolom hasil migration tersebut.

## Aset vendor

Library frontend di-host sendiri di [`public/assets/vendor/`](public/assets/vendor/); tidak ada CDN. Isinya `echarts.min.js` (ECharts 6.1.0) dan `howler.min.js` (Howler.js 2.2.4) dari paket rilis resmi, beserta lisensinya; versi, checksum, dan cara memperbaruinya ada di [`public/assets/vendor/README.md`](public/assets/vendor/README.md). Font lokal ada di `public/assets/fonts/`. Folder vendor sempat terabaikan Git karena pola `vendor/` yang terlalu luas; pola tersebut sudah dipersempit.

## Sesi dan CSRF

- Sesi memakai `DatabaseHandler` dengan tabel `ci_sessions` (migration `003000`), cookie `gelita_session`, masa berlaku 4 jam, `regenerateDestroy` aktif, dan `matchIP` nonaktif.
- CSRF memakai mode `session` dengan token acak per-request (`tokenRandomize`) tetapi **tanpa** regenerasi per-submit (`regenerate = false`). Game mengirim banyak request AJAX beruntun; token yang berubah setiap request membuat request paralel gagal.
- `App\Libraries\HashedIpSessionHandler` (penyimpan hash IP, bukan IP mentah) **belum dipasang**. Kelas itu mewarisi `MySQLiHandler` yang mengunci sesi dengan `GET_LOCK`, dan `Services::session()` hanya memetakan handler per-platform ketika driver persis `DatabaseHandler::class` — sehingga driver kustom melewati pemilihan platform dan gagal pada database uji SQLite. Enam perubahan yang diperlukan beserta buktinya dicatat di [`docs/02_PROJECT_FOUNDATION.md` § 3b](docs/02_PROJECT_FOUNDATION.md#3b-library-hashedipsessionhandler--belum-dipasang).

## Pengujian

Jalankan test suite tanpa laporan coverage:

```bash
vendor/bin/phpunit --no-coverage
```

Suite memakai grup database `tests` (SQLite3 in-memory) dan hanya menjalankan migration bernamespace `Tests\Support`, bukan migration aplikasi. Sesi pada pengujian di-mock dengan `ArrayHandler` oleh `CIUnitTestCase`, jadi konfigurasi sesi produksi tidak ikut dijalankan.

`ViewHelperTest` mengunci helper tampilan tahap 5: ikon SVG dekoratif, format angka/persen/tanggal, dan `component()` yang tidak mewarisi data halaman selain konteks filter.

`RouteWiringTest` memeriksa seluruh route tahap 4 tanpa database: auto-route tetap mati, setiap handler menunjuk kelas dan method publik yang benar-benar ada, dan setiap nama view yang disebut controller punya berkasnya. Karena tabel aplikasi tidak dibuat pada suite ini, **alur HTTP ujung-ke-ujung belum tercakup pengujian otomatis**; jalankan penelusuran manual di atas database MySQL/MariaDB sungguhan setelah `php spark migrate` dan `php spark db:seed DatabaseSeeder`.

Untuk memverifikasi migration terhadap MySQL/MariaDB sungguhan, jalankan `php spark migrate` pada database scratch terpisah dengan kredensial yang dioper lewat environment:

```bash
env "database.default.database=gelita_scratch" \
    "database.default.username=..." \
    "database.default.password=..." \
    php spark migrate
```

## Dokumentasi

Dokumen spesifikasi dan panduan rinci tersedia di folder [`docs`](docs/):

- [Struktur database](docs/01_DATABASE.md)
- [Fondasi proyek](docs/02_PROJECT_FOUNDATION.md)
- [Model, entity, dan service](docs/03_MODEL_ENTITY.md)
- [Route, controller, autentikasi, otorisasi](docs/04_CONTROLLER_ROUTE.md)
- [Deployment dan operasional](docs/08_DEPLOYMENT.md)

Daftar route lengkap dapat dilihat kapan saja tanpa membaca kode:

```bash
php spark routes
```

## Catatan keamanan

- Jangan pernah commit atau membagikan `.env`; berkas ini memuat kredensial dan material rahasia aplikasi. Yang dilacak Git hanyalah templat `env` tanpa nilai rahasia.
- `gelita.ipSalt` wajib diisi string acak panjang per pemasangan. Salt kosong membuat hash IP berupa SHA-256 tanpa garam yang dapat dibalik dengan pencarian menyeluruh ruang IPv4.
- Gunakan akun database dengan hak akses seperlunya, terutama di production.
- Konfigurasi web server harus selalu mengarah ke folder [`public`](public/), bukan root proyek.
- Untuk deployment, gunakan dependensi yang terkunci melalui `composer install --no-dev --optimize-autoloader`.
