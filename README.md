# GELITA

**GELITA** (Game Edukasi Literasi dan Etnopedagogi Kedu) adalah aplikasi web edukasi berbasis CodeIgniter 4. Aplikasi ini dirancang sebagai game pembelajaran server-rendered, dengan dukungan antarmuka berbahasa Indonesia dan Inggris.

## Status pengembangan

Proyek berada pada **tahap 2 dari 8: Fondasi Proyek**.

Tahap ini menyiapkan environment, konfigurasi, struktur aplikasi, helper, filter, layout, localization, dan fondasi keamanan. Tahap berikutnya berfokus pada model dan entity.

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

2. Siapkan berkas `.env` lokal di root proyek. Berkas ini tidak disediakan atau dilacak Git. Isi minimal konfigurasi environment, URL aplikasi, koneksi database, encryption key, dan IP salt sesuai lingkungan lokal Anda.

3. Buat database bila belum ada, lalu jalankan migration:

   ```bash
   php spark migrate
   ```

4. Jalankan server pengembangan:

   ```bash
   php spark serve
   ```

## Pengujian

Jalankan test suite tanpa laporan coverage:

```bash
vendor/bin/phpunit --no-coverage
```

## Dokumentasi

Dokumen spesifikasi dan panduan rinci tersedia di folder [`docs`](docs/):

- [Struktur database](docs/01_DATABASE.md)
- [Fondasi proyek](docs/02_PROJECT_FOUNDATION.md)
- [Deployment dan operasional](docs/08_DEPLOYMENT.md)

## Catatan keamanan

- Jangan pernah commit atau membagikan `.env`; berkas ini memuat kredensial dan material rahasia aplikasi.
- Gunakan akun database dengan hak akses seperlunya, terutama di production.
- Konfigurasi web server harus selalu mengarah ke folder [`public`](public/), bukan root proyek.
- Untuk deployment, gunakan dependensi yang terkunci melalui `composer install --no-dev --optimize-autoloader`.
