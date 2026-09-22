# GELITA

**GELITA** (Game Edukasi Literasi dan Etnopedagogi Kedu) adalah aplikasi web edukasi berbasis CodeIgniter 4. Aplikasi ini dirancang sebagai game pembelajaran server-rendered, dengan dukungan antarmuka berbahasa Indonesia dan Inggris.

## Status pengembangan

Proyek berada pada **tahap 3 dari 8: Model, Entity, dan Service**.

Tahap ini membangun seluruh data layer: 29 Model CodeIgniter, 11 Entity dengan casting dan aksesor dwibahasa, serta 8 Service (pembaca konten ber-cache, konteks permainan per request, sesi & akun siswa, tantangan, skor, event, analitik, dan impor bank soal). Tahap berikutnya berfokus pada controller dan route.

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

Library frontend di-host sendiri di [`public/assets/vendor/`](public/assets/vendor/); tidak ada CDN. Dua berkas yang diperlukan — `echarts.min.js` (ECharts 6.x) dan `howler.min.js` (Howler.js 2.2.x) — ditambahkan pada tahap view/JavaScript. Folder ini sempat terabaikan Git karena pola `vendor/` yang terlalu luas; pola tersebut sudah dipersempit.

## Sesi dan CSRF

- Sesi memakai `DatabaseHandler` dengan tabel `ci_sessions` (migration `003000`), cookie `gelita_session`, masa berlaku 4 jam, `regenerateDestroy` aktif, dan `matchIP` nonaktif.
- CSRF memakai mode `session` dengan token acak per-request (`tokenRandomize`) tetapi **tanpa** regenerasi per-submit (`regenerate = false`). Game mengirim banyak request AJAX beruntun; token yang berubah setiap request membuat request paralel gagal.
- `App\Libraries\HashedIpSessionHandler` (penyimpan hash IP, bukan IP mentah) **belum dipasang**. Kelas itu mewarisi `MySQLiHandler` yang mengunci sesi dengan `GET_LOCK`, dan `Services::session()` hanya memetakan handler per-platform ketika driver persis `DatabaseHandler::class` — sehingga driver kustom melewati pemilihan platform dan gagal pada database uji SQLite. Perubahan yang diperlukan dicatat terpisah sebelum handler ini diaktifkan.

## Pengujian

Jalankan test suite tanpa laporan coverage:

```bash
vendor/bin/phpunit --no-coverage
```

Suite memakai grup database `tests` (SQLite3 in-memory) dan hanya menjalankan migration bernamespace `Tests\Support`, bukan migration aplikasi. Sesi pada pengujian di-mock dengan `ArrayHandler` oleh `CIUnitTestCase`, jadi konfigurasi sesi produksi tidak ikut dijalankan.

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
- [Deployment dan operasional](docs/08_DEPLOYMENT.md)

## Catatan keamanan

- Jangan pernah commit atau membagikan `.env`; berkas ini memuat kredensial dan material rahasia aplikasi. Yang dilacak Git hanyalah templat `env` tanpa nilai rahasia.
- `gelita.ipSalt` wajib diisi string acak panjang per pemasangan. Salt kosong membuat hash IP berupa SHA-256 tanpa garam yang dapat dibalik dengan pencarian menyeluruh ruang IPv4.
- Gunakan akun database dengan hak akses seperlunya, terutama di production.
- Konfigurasi web server harus selalu mengarah ke folder [`public`](public/), bukan root proyek.
- Untuk deployment, gunakan dependensi yang terkunci melalui `composer install --no-dev --optimize-autoloader`.
