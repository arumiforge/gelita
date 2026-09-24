# 08_DEPLOYMENT.md — Deployment & Operasional

> **Revisi 3 (23 September 2026).** Skenario "laptop guru di jaringan lokal tanpa internet" dihapus: akses internet di wilayah penelitian kini memadai, jadi GELITA selalu berjalan sebagai **satu instansi server ber-HTTPS**. Instalasi kini punya dua jalur setara, **Linux** (Nginx + PHP-FPM) dan **Windows + Laragon** (Nginx + php-cgi). Revisi ini juga memperbaiki cacat yang muncul ketika Revisi 2 dijalankan apa adanya di atas MariaDB 10.11, Nginx, dan PHP-FPM 8.3. Di antaranya:
>
> - migration ditolak database;
> - izin `.env` membuat seluruh situs HTTP 500, termasuk setelah setiap deploy;
> - `php spark down`/`up` tidak ada di CodeIgniter 4.7;
> - backup dari cron selalu gagal;
> - cache aset 30 hari membekukan modul JavaScript lama.
>
> Rinciannya ada di [Catatan Revisi 3](#catatan-revisi-3--hasil-audit).
>
> *Revisi 2 (21 September 2026):* impor workbook bank soal saat deployment awal, uji registrasi/login berkata sandi, prosedur reset sandi oleh guru.

---

## Tujuan

Menjalankan GELITA sebagai sistem production. Dokumen ini mencakup:

- menyiapkan server Linux atau Windows;
- mengonfigurasi environment;
- deployment dari server kosong sampai aplikasi siap dipakai kelas;
- pembaruan dan rollback;
- penataan aset, backup, dan tugas terjadwal;
- langkah operasional harian.

Tahap 8 **tidak mengubah kode aplikasi**. Hasilnya berupa berkas operasional di folder `deploy/`, tiga aturan `.gitignore`, dan satu test yang mengunci berkas tersebut (lihat [Berkas yang dihasilkan tahap 8](#berkas-yang-dihasilkan-tahap-8)).

Status: **selesai**. Semua skrip dijalankan di server uji. Tiga cacat yang baru terlihat saat dijalankan sudah diperbaiki, baik di berkas maupun di dokumen ini. Rinciannya di [Catatan Implementasi Tahap 8](#catatan-implementasi-tahap-8).

---

## Konteks

GELITA adalah satu instansi server. Siswa, guru, dan peneliti mengaksesnya lewat internet dengan HTTPS di satu nama domain, misalnya `gelita.sekolah.sch.id`. Satu instansi dapat dipakai banyak kelas dan sekolah. Guru membuka dasbor dari mana saja.

| | **Jalur L — Linux** | **Jalur W — Windows + Laragon** |
|---|---|---|
| Cocok untuk | VPS atau server sekolah Linux; **disarankan** untuk server publik | server/PC sekolah Windows yang sudah ada |
| Web server | Nginx | Nginx bawaan Laragon |
| PHP | PHP-FPM 8.3 | php-cgi 8.3, dijalankan Laragon di belakang `upstream php_upstream` |
| Database | MySQL 8.0+ / MariaDB 10.6+ | MySQL bawaan Laragon |
| HTTPS | Certbot (Let's Encrypt), validasi webroot | simple-acme / win-acme (Let's Encrypt), berkas PEM |
| Tugas terjadwal | cron | Task Scheduler |
| Skrip operasional | `deploy/linux/*.sh` | `deploy/windows/*.ps1` |
| Akun sistem | `deploy` (kode) + `www-data` (runtime) | satu akun Windows yang menjalankan Laragon |

Yang **sama** di kedua jalur:

- kode dan `.env`;
- skema tiga akun database;
- konfigurasi Nginx (hanya path dan `fastcgi_pass` yang berbeda);
- mode pemeliharaan;
- urutan deploy;
- daftar verifikasi.

Syarat bersama:

1. **Nama domain** yang menunjuk ke IP publik server.
2. **Port 80 dan 443 terbuka dari internet.** Untuk server di jaringan sekolah, ini berarti *port forwarding* di router sekolah. Port 80 tetap diperlukan untuk validasi dan perpanjangan sertifikat.
3. Server menyala selama jam sekolah, dengan jam sistem di WIB.

Bila server Windows sekolah tidak dapat diberi IP publik atau *port forwarding*, gunakan Jalur L di VPS.

Yang **tidak** didukung tahap ini:

- Aplikasi dari laptop di jaringan lokal tanpa HTTPS (skenario Revisi 2, dihapus).
- Apache. `public/.htaccess` dibiarkan untuk pengembangan lokal, tetapi aturan cache, header, dan mode pemeliharaan tahap 8 ditulis untuk Nginx.
- `php spark serve` di production.

Catatan Laragon: Laragon adalah lingkungan pengembangan. Jalur W menutup kekurangannya untuk production:

- vhost otomatis yang ditulis ulang saat start;
- TLS lemah;
- `autoindex`;
- autostart.

Periksa ketentuan lisensi di laragon.org sebelum memasangnya di institusi. Laragon 7 ke atas berlisensi berbayar, meski pemakaian non-komersial tanpa lisensi diizinkan dengan pengingat. Laragon 6 gratis, tetapi membawa PHP 8.1, jadi PHP 8.3 x64 harus ditambahkan manual ke `C:\laragon\bin\php\`.

---

## Production Environment

### Spesifikasi minimum

| Komponen | Minimum | Disarankan |
|---|---|---|
| CPU | 2 core | 4 core |
| RAM — Jalur L | 2 GB | 4 GB (export XLSX besar butuh ruang) |
| RAM — Jalur W | 4 GB | 8 GB (Windows sendiri memakai 2–3 GB) |
| Disk | 20 GB | 50 GB (aset gambar/video + raw event + backup lokal) |
| OS — Jalur L | Ubuntu 22.04 LTS / Debian 12 | Ubuntu 24.04 LTS |
| OS — Jalur W | Windows 10/11 Pro 64-bit | Windows Server 2019+ atau Windows 11 Pro |

Ubuntu 22.04 hanya membawa PHP 8.1. Tambahkan PPA `ondrej/php` atau pakai Ubuntu 24.04, yang membawa PHP 8.3. Debian 12 membawa PHP 8.2 (didukung); ganti `8.3` menjadi `8.2` di seluruh nama paket, path, dan socket.

**Kapasitas** ditentukan jumlah proses PHP, bukan CPU. Setiap request memakai satu proses sampai selesai, dan export dapat memegangnya sampai 300 detik. Karena itu `pm.max_children` (Jalur L) dan `PHP_FCGI_CHILDREN` (Jalur W) wajib dinaikkan dari bawaannya (lihat di bawah). Nginx di Windows memakai satu worker dengan `worker_connections 1024` (bawaan templat Laragon). Batas ini cukup untuk beberapa kelas bersamaan. Untuk banyak sekolah sekaligus, gunakan Jalur L.

### Perangkat lunak

| Komponen | Jalur L | Jalur W |
|---|---|---|
| PHP | 8.2 atau 8.3, **FPM** | 8.3 x64 (bawaan Laragon 8.x), **php-cgi** |
| CodeIgniter | 4.7.x — terkunci di `composer.lock` (4.7.4) | sama |
| Database | MySQL 8.0+ **atau** MariaDB 10.6+ | MySQL bawaan Laragon (8.x) |
| Web server | Nginx 1.22+ (Ubuntu 24.04: 1.24) | Nginx bawaan Laragon (8.6+: 1.28 ke atas) |
| Composer | 2.6+ (`apt install composer` di 24.04) | bawaan Laragon |
| Git | paket distro | bawaan Laragon Full, atau Git for Windows |
| HTTPS | `certbot` | simple-acme (penerus win-acme) |

### Ekstensi PHP wajib

```
intl mbstring json mysqlnd curl gd zip fileinfo openssl iconv
dom xml xmlwriter simplexml
```

**Jalur L** — pemasangan di Ubuntu 24.04:

```bash
sudo apt update
sudo apt install -y nginx mysql-server composer certbot rsync unzip git \
  php8.3-fpm php8.3-cli php8.3-mysql php8.3-intl php8.3-mbstring php8.3-curl \
  php8.3-gd php8.3-zip php8.3-xml php8.3-bcmath
```

**Jalur W** — Menu Laragon → *PHP* → *Extensions*, centang `intl`, `mbstring`, `gd`, `zip`, `fileinfo`, `openssl`, `curl`, `mysqli`. `json`, `dom`, `xml`, `xmlwriter`, `simplexml`, dan `iconv` sudah terpasang di build PHP Windows. Laragon memakai satu `php.ini` per versi PHP untuk CLI maupun php-cgi.

Verifikasi:

```bash
php -v
php -m | grep -E '^(intl|mbstring|gd|zip|xmlwriter|mysqlnd|fileinfo)$'
```

```powershell
php -v
php -m | Select-String -Pattern '^(intl|mbstring|gd|zip|xmlwriter|mysqlnd|fileinfo)$'
```

Bila salah satu tidak ada, hentikan di sini. PhpSpreadsheet gagal tanpa `zip` dan `xmlwriter`; mPDF gagal tanpa `mbstring`; CodeIgniter menolak jalan tanpa `intl`.

### Pengaturan PHP

**Jalur L** — `/etc/php/8.3/fpm/conf.d/99-gelita.ini`:

```ini
memory_limit = 512M
max_execution_time = 300
upload_max_filesize = 64M
post_max_size = 72M
date.timezone = Asia/Jakarta
display_errors = Off
display_startup_errors = Off
log_errors = On
expose_php = Off
session.cookie_httponly = 1
session.use_strict_mode = 1
opcache.enable = 1
opcache.memory_consumption = 192
opcache.max_accelerated_files = 20000
opcache.validate_timestamps = 0
```

`opcache.validate_timestamps = 0` mempercepat production, tetapi PHP-FPM **harus di-reload setiap deploy**. `deploy.sh` sudah melakukannya.

Ubah juga pool `/etc/php/8.3/fpm/pool.d/www.conf`. Bawaan Ubuntu `pm.max_children = 5` berarti request keenam mengantre, dan satu export sudah memegang satu proses:

```ini
pm = dynamic
pm.max_children = 20              ; ±50 MB per proses: 20 untuk RAM 4 GB, 10 untuk 2 GB
pm.start_servers = 4
pm.min_spare_servers = 2
pm.max_spare_servers = 6
request_terminate_timeout = 330s  ; jaring pengaman di atas batas export 300 detik
```

```bash
sudo php-fpm8.3 -t && sudo systemctl restart php8.3-fpm
```

**Jalur W** — Menu Laragon → *PHP* → *php.ini*. Isi nilainya sama dengan di atas, dengan tiga perbedaan:

```ini
zend_extension = opcache
opcache.validate_timestamps = 1   ; php-cgi Laragon tidak punya "reload" seperti FPM
opcache.revalidate_freq = 2
```

- **Opcache.** Tanpa perintah reload, `validate_timestamps = 0` akan membuat php-cgi terus menjalankan kode lama setelah deploy. Biaya pemeriksaan stempel waktu tiap 2 detik tidak berarti pada skala sekolah.
- **Batas waktu.** Di Windows, `max_execution_time` dihitung dengan jam dinding (termasuk menunggu database), bukan waktu CPU seperti di Linux. Karena itu export besar lebih cepat menyentuh batas 300 detik; persempit rentang tanggal bila terjadi.
- **Jumlah proses PHP.** Atur di Menu Laragon → *Laragon* → *laragon.ini*, bagian `[nginx]`, lalu *Stop* dan *Start All*:

```ini
[nginx]
PHP_FCGI_CHILDREN=16
```

Mengganti versi PHP di Laragon berarti memakai `php.ini` lain. Ulangi pengaturan ekstensi dan `php.ini`, lalu perbarui `Php` di `config.psd1` dan tugas terjadwal.

### Pengaturan MySQL

**Jalur L:** MySQL memakai `/etc/mysql/mysql.conf.d/99-gelita.cnf`, MariaDB memakai `/etc/mysql/mariadb.conf.d/99-gelita.cnf`. **Jalur W:** Menu Laragon → *MySQL* → *my.ini*, di bawah `[mysqld]`.

```ini
[mysqld]
character-set-server = utf8mb4
collation-server     = utf8mb4_unicode_ci
default-time-zone    = '+07:00'
bind-address         = 127.0.0.1         # database tidak pernah dibuka ke jaringan
innodb_buffer_pool_size = 1G             # RAM 4 GB: 1G · RAM 2 GB: 512M
innodb_flush_log_at_trx_commit = 1       # jangan dikendurkan; ini data penelitian
max_connections = 100
```

`innodb_flush_log_at_trx_commit = 1` memang lebih lambat, tetapi data penelitian yang hilang karena listrik padam tidak dapat diulang. `default-time-zone` memakai offset, bukan `Asia/Jakarta`, supaya jalan tanpa tabel zona waktu (tidak ada di MySQL Windows). Aplikasi sendiri menyetel `time_zone` tiap koneksi lewat `db_sync_timezone()`. Nilai server ini dipakai klien `mysql` dan kueri pemeriksaan di skrip deploy.

Revisi 2 menyetel `sql_mode = STRICT_TRANS_TABLES,…`, tetapi baris itu tidak berpengaruh pada aplikasi. `Config\Database::$default['strictOn'] = false` membuat CodeIgniter **membuang** `STRICT_*` dari `sql_mode` setiap koneksinya. Hasil ukur di MariaDB 10.11: sesi aplikasi `ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION` (tanpa STRICT), global `STRICT_TRANS_TABLES,…`. Barisnya dihapus agar tidak memberi rasa aman palsu. Mengaktifkan mode strict adalah keputusan kode (`strictOn`), bukan konfigurasi server.

**Jalur W:** MySQL Laragon terpasang dengan `root` **tanpa sandi**. Setel sandinya sebelum langkah lain:

```powershell
mysql -u root -e "ALTER USER 'root'@'localhost' IDENTIFIED BY 'SANDI_ROOT_KUAT';"
```

### Akun database

Tiga akun, masing-masing dengan hak seperlunya:

| Akun | Hak | Dipakai oleh | Disimpan di |
|---|---|---|---|
| `gelita_app` | `SELECT, INSERT, UPDATE, DELETE` | aplikasi, semua `php spark` selain migration, seeder | `.env` |
| `gelita_migrate` | `ALL` pada `gelita.*` | **hanya** `php spark migrate` / `migrate:rollback` saat deploy | `migrate.dsn` (di luar repo) |
| `gelita_backup` | `SELECT, SHOW VIEW, TRIGGER, LOCK TABLES` | backup, pemeriksaan sesi aktif, pemantauan | `backup.cnf` (di luar repo) |

```sql
CREATE DATABASE gelita CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER 'gelita_app'@'localhost' IDENTIFIED BY 'SANDI_APP';
GRANT SELECT, INSERT, UPDATE, DELETE ON gelita.* TO 'gelita_app'@'localhost';

CREATE USER 'gelita_migrate'@'localhost' IDENTIFIED BY 'SANDI_MIGRATE';
GRANT ALL PRIVILEGES ON gelita.* TO 'gelita_migrate'@'localhost';

CREATE USER 'gelita_backup'@'localhost' IDENTIFIED BY 'SANDI_BACKUP';
GRANT SELECT, SHOW VIEW, TRIGGER, LOCK TABLES ON gelita.* TO 'gelita_backup'@'localhost';

FLUSH PRIVILEGES;
```

**Jalur W:** ganti `'localhost'` menjadi `'127.0.0.1'` di keenam baris. Di Windows, PHP dan klien `mysql` tersambung lewat TCP. Akun `@'127.0.0.1'` selalu cocok, apa pun setelan `skip-name-resolve`. Di `.env`, `backup.cnf`, dan `migrate.dsn`, tulis host `127.0.0.1` pula. Di Windows, `localhost` dapat diterjemahkan ke `::1` lebih dulu.

Pemisahan hak ini berarti bila aplikasi diretas lewat SQL injection, skema database tidak ikut menjadi korban. Seeder cukup memakai `gelita_app` karena hanya menulis data. Ini sudah diuji: `DatabaseSeeder` lengkap berjalan dengan akun DML saja.

### Model pengguna dan izin

**Jalur L.** Dua pengguna sistem dengan peran terpisah:

| Pengguna | Memiliki | Menjalankan |
|---|---|---|
| `deploy` | kode (`git pull`, `composer`), `.env`, `/home/deploy/.gelita/`, `/var/backups/gelita/` | `deploy.sh`, `backup.sh` (cron) |
| `www-data` | `writable/`, `public/assets/uploads/` | PHP-FPM, Nginx, **semua `php spark`**, cron retensi |

```bash
sudo adduser --disabled-password --gecos '' deploy
sudo usermod -aG www-data deploy          # wajib — lihat butir 2 di bawah

cd /var/www/gelita
sudo chown -R deploy:www-data .
sudo chown -R www-data:www-data writable public/assets/uploads
sudo find writable public/assets/uploads -type d -exec chmod 2775 {} +
sudo chown deploy:www-data .env && sudo chmod 640 .env
```

`/etc/sudoers.d/gelita-deploy` (buat dengan `sudo visudo -f /etc/sudoers.d/gelita-deploy`):

```
deploy ALL=(www-data) NOPASSWD: ALL
deploy ALL=(root) NOPASSWD: /usr/bin/systemctl reload php8.3-fpm, /usr/bin/systemctl reload nginx
```

`deploy` sudah menguasai kode yang dijalankan `www-data`, jadi hak menjalankan perintah sebagai `www-data` tidak menambah kuasa.

Alasan tiap aturan (semuanya terbukti saat audit):

1. **`.env` = `640 deploy:www-data`, bukan `600 deploy:deploy`.** PHP-FPM berjalan sebagai `www-data`. Dengan mode 600, CodeIgniter 4.7 melempar galat "`.env` not readable" dan **setiap halaman menjadi HTTP 500**.
2. **`deploy` masuk grup `www-data`.** `sed -i` di `deploy.sh` menulis `.env` sebagai berkas baru. Bila pemanggilnya bukan anggota grup `www-data`, grup berkas berubah menjadi `deploy`, dan situs kembali HTTP 500 setelah **setiap** deploy.
3. **Semua `php spark` dijalankan sebagai `www-data`** (`sudo -u www-data php spark …`). Cache ditulis mode `0640` dan log mode `0644`. Berkas yang dibuat `deploy` lewat CLI tidak dapat ditimpa PHP-FPM, sehingga cache gagal diam-diam dan log harian hilang.
4. **Jangan `chmod -R 775 writable`.** Perintah itu memberi bit eksekusi ke berkas yang dilacak git (`writable/index.html` dan lainnya). Working tree menjadi "kotor" dan `git pull` bisa menolak. Cukup direktori `2775` (setgid menjaga grup `www-data` untuk berkas baru).

**Jalur W.** Satu akun Windows standar (misalnya `gelita`) menjalankan Laragon, deploy, dan Task Scheduler, jadi tidak ada konflik izin antarpengguna. Batasi siapa yang dapat membaca folder rahasia dan backup. `*S-1-5-32-544` adalah grup Administrators, ditulis sebagai SID agar tidak bergantung pada bahasa Windows:

```powershell
New-Item -ItemType Directory -Force C:\gelita-ops, D:\gelita-backup | Out-Null
foreach ($d in 'C:\gelita-ops', 'D:\gelita-backup') {
    icacls $d /inheritance:r /grant:r "*S-1-5-32-544:(OI)(CI)F" "${env:USERNAME}:(OI)(CI)M"
}
```

---

## Configuration

### `.env` production

Mulai dari templat (`cp env .env` / `Copy-Item env .env`). Templat sudah memuat nilai sesi, CSRF, dan cookie yang benar. Yang **wajib** diubah:

```ini
CI_ENVIRONMENT = production

app.baseURL = 'https://gelita.sekolah.sch.id/'
app.forceGlobalSecureRequests = true

database.default.hostname = localhost          # Jalur W: 127.0.0.1
database.default.username = gelita_app
database.default.password = 'SANDI_APP'

encryption.key = hex2bin:…                     # diisi php spark key:generate

cookie.secure = true
logger.threshold = 4

gelita.ipSalt = '…'                            # php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
```

Catatan:

1. `app.baseURL` harus sama persis dengan domain HTTPS. Redirect login memakai nilai ini.
2. `encryption.key` **tidak pernah dibangkitkan ulang** setelah production berjalan. Token objek arena `cari` dan penanda antrean offline (`sessionTag`) diturunkan darinya. Kuncinya ikut backup lewat salinan `.env`.
3. `gelita.ipSalt` wajib acak. Bila bocor, `ip_hash` dapat dibalik dengan pencarian menyeluruh karena ruang alamat IPv4 kecil. Perintah `php -r` di atas bekerja di kedua jalur.
4. **Jangan menambahkan `database.default.DSN` ke `.env`.** Kunci itu disisihkan untuk migration (bagian berikut).
5. Perubahan `.env` langsung berlaku di request berikutnya. Jangan menjalankan `php spark optimize`: perintah itu mengubah `app/Config/Optimize.php` (berkas yang dilacak git) dan mengaktifkan cache konfigurasi yang membekukan nilai `.env`, termasuk `gelita.assetVersion`.

### Kredensial migration lewat DSN

Revisi 2 menjalankan `DB_USER=gelita_migrate php spark migrate`. CodeIgniter tidak pernah membaca `DB_USER`, jadi migration berjalan dengan `gelita_app` dan gagal:

```
CREATE command denied to user 'gelita_app'@'localhost' for table `gelita`.`migrations`
```

Variabel environment juga **tidak dapat** menimpa kunci yang sudah ada di `.env`. `DotEnv` mengisi `$_ENV` dari `.env`, dan `BaseConfig::getEnvValue()` memeriksa `$_ENV` lebih dulu. Ini berlaku untuk `database_default_username=…` maupun `env "database.default.username=…"`; keduanya diuji dan tetap menghasilkan `gelita_app`.

Yang bekerja adalah kunci yang **tidak** ada di `.env`: `database_default_DSN`. CodeIgniter mengurai DSN dan menimpa host, pengguna, sandi, serta nama database. Isi `migrate.dsn` (satu baris, mode 600, di luar repositori):

```
MySQLi://gelita_migrate:SANDI_URL_ENCODED@localhost/gelita
```

- **Sandi di-URL-encode** lebih dulu: `php -r "echo rawurlencode('Migr@te/2026'), PHP_EOL;"` menghasilkan `Migr%40te%2F2026`.
- **Tanpa port.** CodeIgniter 4.7.4 meneruskan port dari DSN sebagai string, dan `mysqli::real_connect()` menolaknya dengan `TypeError`. Tanpa port, 3306 dipakai.
- **Jalur W:** host `127.0.0.1`.

Pemakaian (skrip deploy melakukannya otomatis):

```bash
# Jalur L — sebagai user deploy
export database_default_DSN="$(cat /home/deploy/.gelita/migrate.dsn)"
sudo --preserve-env=database_default_DSN -u www-data php spark migrate
unset database_default_DSN
```

```powershell
# Jalur W
$env:database_default_DSN = (Get-Content C:\gelita-ops\migrate.dsn -Raw).Trim()
php spark migrate
Remove-Item Env:\database_default_DSN
```

Cara yang sama berlaku untuk `migrate:rollback` dan untuk database scratch saat pengembangan: ganti nama database di akhir DSN.

### Berkas operasional di luar repositori

Rahasia dan nilai khusus server tidak pernah masuk repositori. Skrip di `deploy/` juga tidak diedit di server, karena editan lokal membuat `git pull` bentrok.

| Berkas | Jalur L | Jalur W | Isi |
|---|---|---|---|
| folder | `/home/deploy/.gelita/` (700) | `C:\gelita-ops\` (ACL di atas) | |
| `backup.cnf` | ✓ (600) | ✓ | opsi klien MySQL untuk `gelita_backup` |
| `migrate.dsn` | ✓ (600) | ✓ | DSN `gelita_migrate` |
| `ops.conf` | opsional | — | menimpa bawaan skrip, mis. `KEDUA=/mnt/usb/gelita` |
| `config.psd1` | — | ✓ | path Laragon, PHP, MySQL, folder backup |

`backup.cnf`:

```ini
[client]
user=gelita_backup
password="SANDI_BACKUP"
host=localhost
```

Untuk Jalur W, tulis `host=127.0.0.1`. `--defaults-extra-file` harus menjadi opsi **pertama** `mysql`/`mysqldump`; skrip sudah mematuhinya. Opsi ini juga menghindari sandi di baris perintah, yang terlihat di daftar proses.

`config.psd1` (salin dari `deploy\windows\config.example.psd1`):

```powershell
@{
    App    = 'C:\laragon\www\gelita'
    Php    = 'C:\laragon\bin\php\php-8.3.33-Win32-vs16-x64\php.exe'   # sesuaikan versi
    MySql  = 'C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin'            # sesuaikan versi
    Dir    = 'D:\gelita-backup'                                        # backup lokal
    Kedua  = 'E:\gelita-backup'                                        # drive eksternal / \\nas\backup\gelita
    Branch = 'main'
}
```

### Nginx

Konfigurasi dibagi dua berkas: port 80 dan aplikasi (443). Port 80 diaktifkan lebih dulu. Blok 443 menunjuk berkas sertifikat, dan `nginx -t` gagal ("cannot load certificate") selama berkas itu belum ada. Ini sebabnya urutan Revisi 2 (konfigurasi 443, `nginx -t`, lalu `certbot`) tidak dapat dijalankan.

#### Jalur L

`/etc/nginx/sites-available/gelita-http` (sumber: `deploy/linux/nginx/gelita-http.conf`):

```nginx
# port 80: validasi Let's Encrypt + alihkan ke HTTPS
server {
    listen 80;
    server_name gelita.sekolah.sch.id;

    location ^~ /.well-known/acme-challenge/ {
        root /var/www/gelita/public;
        default_type text/plain;
    }

    location / {
        return 301 https://$host$request_uri;
    }
}
```

`/etc/nginx/sites-available/gelita-https` (sumber: `deploy/linux/nginx/gelita-https.conf`):

```nginx
# Cache aset. Hanya URL ber-?v= (CSS, JS entry, vendor dari asset_url_versioned())
# yang boleh immutable. Modul JS yang di-import, font, gambar, dan audio unggahan
# tidak ber-?v=, jadi browser wajib bertanya ulang (304 bila berkas tidak berubah).
map $uri $gelita_is_asset {
    default 0;
    ~*\.(?:css|js|json|jpe?g|png|gif|webp|svg|ico|woff2?|ttf|mp3|ogg|wav|m4a|mp4|webm)$ 1;
}
map "$gelita_is_asset:$arg_v" $gelita_cache {
    default "";
    "1:"    "no-cache";
    ~^1:.   "public, max-age=31536000, immutable";
}

server {
    listen 443 ssl http2;          # Nginx >= 1.25.1: "listen 443 ssl;" + "http2 on;"
    server_name gelita.sekolah.sch.id;

    root /var/www/gelita/public;
    index index.php;
    charset utf-8;
    server_tokens off;

    ssl_certificate     /etc/letsencrypt/live/gelita.sekolah.sch.id/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/gelita.sekolah.sch.id/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;

    client_max_body_size 72M;

    # Satu-satunya tempat add_header. add_header di dalam location menghapus
    # SEMUA header warisan server, jadi jangan menambahkannya di location mana pun.
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header Referrer-Policy "same-origin" always;
    add_header Strict-Transport-Security "max-age=31536000" always;
    add_header Cache-Control $gelita_cache;          # nilai kosong = tidak dikirim

    # Mode pemeliharaan: selama berkas penanda ada, semua permintaan dijawab 503.
    if (-f /var/www/gelita/writable/pemeliharaan.flag) {
        return 503;
    }
    error_page 503 @pemeliharaan;
    location @pemeliharaan {
        default_type "text/html; charset=utf-8";
        return 503 '<!doctype html><html lang="id"><meta charset="utf-8"><meta name="viewport" content="width=device-width"><title>GELITA</title><body style="font-family:sans-serif;text-align:center;padding:3rem">GELITA sedang diperbarui. Coba lagi beberapa menit lagi.<br><i>GELITA is being updated. Please try again in a few minutes.</i></body></html>';
    }

    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }

    # Hanya front controller yang dieksekusi PHP
    location = /index.php {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_read_timeout 300;
        # Filter secureheaders CodeIgniter mengirim header yang sama; buang
        # salinan dari PHP agar setiap header hanya muncul sekali.
        fastcgi_hide_header X-Frame-Options;
        fastcgi_hide_header X-Content-Type-Options;
        fastcgi_hide_header Referrer-Policy;
    }
    location ~ \.php$ {
        return 404;
    }

    location ^~ /assets/ {
        access_log off;
        try_files $uri =404;
        location ~ /\. { return 404; }
    }

    # Berkas tersembunyi (.htaccess, .gitkeep, .env, .git) tidak pernah dilayani
    location ~ /\. {
        return 404;
    }

    gzip on;
    gzip_types text/css application/javascript application/json image/svg+xml;
    gzip_min_length 1024;
}
```

Bila server punya IPv6, tambahkan `listen [::]:80;` dan `listen [::]:443 ssl http2;`. Pada mesin tanpa IPv6, baris itu membuat `nginx -t` gagal.

Yang diperbaiki terhadap Revisi 2, dan diuji dengan `curl` terhadap Nginx 1.24 + PHP-FPM 8.3:

| Perilaku | Revisi 2 | Revisi 3 |
|---|---|---|
| `/assets/js/core/dom.js` (di-import, tanpa `?v`) | `public, immutable` 30 hari: modul lama bertahan sebulan setelah pembaruan | `no-cache`, revalidasi menghasilkan 304 |
| `/assets/js/game.js?v=…` | `max-age` + `immutable` dalam **dua** header `Cache-Control` | satu header, `immutable` 1 tahun |
| Header keamanan pada aset statis | **hilang**: `add_header` di `location` aset menimpa header `server` | ada di semua respons |
| Header keamanan pada halaman aplikasi | **ganda**: filter `secureheaders` CodeIgniter + `add_header` Nginx | tepat satu (`fastcgi_hide_header`) |
| `/.htaccess` | 200 (terlayani) | 404 |
| `.php` selain `index.php` | dieksekusi bila ada | 404 |
| Mode pemeliharaan | `php spark down` (tidak ada) | berkas penanda → 503 |

#### Jalur W — Laragon

Laragon membuat vhost `auto.<folder>.test.conf` untuk setiap folder di `C:\laragon\www`. Vhost itu **tidak** layak untuk production:

- `autoindex on`;
- `TLSv1 TLSv1.1` dengan cipher `RC4`/`EXP`;
- tanpa header keamanan;
- setiap `.php` dieksekusi;
- ditulis ulang setiap Laragon start.

Langkahnya:

1. Preferences → *General*: matikan **Auto virtual hosts**. Hapus `C:\laragon\etc\nginx\sites-enabled\auto.gelita.test.conf` bila sudah terbentuk.
2. Preferences → *Services & Ports*: aktifkan **Nginx** dan **MySQL**, matikan **Apache** (keduanya berebut port 80).
3. Salin `deploy\windows\nginx\gelita-http.conf` ke `C:\laragon\etc\nginx\sites-enabled\`. Nama berkas **tidak boleh** berawalan `auto.`.
4. Menu → *Nginx* → *Reload*. Setelah sertifikat terbit, salin juga `gelita-https.conf`, lalu reload lagi.

`gelita-http.conf` sama dengan Jalur L, dengan `root "C:/laragon/www/gelita/public";`. `gelita-https.conf` sama dengan Jalur L kecuali baris berikut (path Windows ditulis dengan garis miring biasa):

```nginx
    listen 443 ssl;
    http2 on;                                   # Nginx Laragon >= 1.25.1

    root "C:/laragon/www/gelita/public";

    ssl_certificate     "C:/laragon/etc/ssl/gelita/gelita.sekolah.sch.id-chain.pem";
    ssl_certificate_key "C:/laragon/etc/ssl/gelita/gelita.sekolah.sch.id-key.pem";

    if (-f "C:/laragon/www/gelita/writable/pemeliharaan.flag") {
        return 503;
    }

    location = /index.php {
        include snippets/fastcgi-php.conf;      # tersedia di Nginx Laragon
        fastcgi_pass php_upstream;              # didefinisikan Laragon (etc/nginx/php_upstream.conf)
        fastcgi_read_timeout 300;
        fastcgi_hide_header X-Frame-Options;
        fastcgi_hide_header X-Content-Type-Options;
        fastcgi_hide_header Referrer-Policy;
    }
```

`php_upstream` menunjuk proses php-cgi yang dijalankan Laragon (port FastCGI bawaan Laragon 8.6+: 10987). Kombinasi `fastcgi_pass php_upstream` → php-cgi dengan blok `server` di atas diuji dengan php-cgi 8.3 di belakang `upstream`: halaman, aset, login admin (POST + CSRF + sesi database), dan 404 untuk berkas sensitif semuanya sesuai. Direktif `http2 on;` dan path Windows belum diuji di mesin Windows sungguhan. Jalankan pemeriksaan di bagian [Verifikasi](#verifikasi-setelah-deploy) sesudah pemasangan pertama.

### HTTPS

**Jalur L** — Certbot dengan validasi webroot. Webroot dilayani blok port 80, jadi perpanjangan otomatis tidak perlu menghentikan Nginx:

```bash
sudo rm -f /etc/nginx/sites-enabled/default
sudo ln -s /etc/nginx/sites-available/gelita-http /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx

sudo certbot certonly --webroot -w /var/www/gelita/public -d gelita.sekolah.sch.id \
     --deploy-hook "systemctl reload nginx"

sudo ln -s /etc/nginx/sites-available/gelita-https /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
sudo certbot renew --dry-run                # pastikan perpanjangan otomatis berjalan
```

**Jalur W** — Certbot menghentikan dukungan Windows sejak Februari 2024. Pakai **simple-acme** (penerus win-acme; win-acme masih dapat dipakai). Nama menu dapat sedikit berbeda antarversi, tetapi pilihannya:

1. Ekstrak rilis x64 ke `C:\gelita-ops\acme\`, jalankan `wacs.exe` sebagai Administrator.
2. *Create certificate (full options)* → sumber *Manual input* → `gelita.sekolah.sch.id`.
3. Validasi HTTP-01 *save verification files on (network) path* → `C:\laragon\www\gelita\public`.
4. Penyimpanan *PEM encoded files* → `C:\laragon\etc\ssl\gelita`. Terbentuk `{domain}-chain.pem` (sertifikat + rantai) dan `{domain}-key.pem`.
5. Instalasi *Start external script or program* → `C:\gelita-ops\reload-nginx.cmd`:

   ```bat
   @echo off
   cd /d C:\laragon\bin\nginx\nginx-1.30.4
   nginx.exe -s reload
   ```

   Sesuaikan folder versi Nginx. Uji skrip ini sekali secara manual. Tanpa reload, Nginx terus memakai sertifikat lama sampai kedaluwarsa.
6. Klien mendaftarkan tugas perpanjangan di Task Scheduler. Pastikan tugas itu ada.

### Mode pemeliharaan

CodeIgniter 4.7 tidak punya `php spark down`/`up` (keduanya "Command not found", kode keluar 1). Di Revisi 2 perintah itu menghentikan `deploy.sh` tepat setelah backup. Pengganti tahap 8 bekerja di tingkat Nginx, sama di kedua jalur, tanpa kode aplikasi:

| | Jalur L | Jalur W |
|---|---|---|
| aktifkan | `touch /var/www/gelita/writable/pemeliharaan.flag` | `New-Item -ItemType File -Force C:\laragon\www\gelita\writable\pemeliharaan.flag` |
| matikan | `rm /var/www/gelita/writable/pemeliharaan.flag` | `Remove-Item C:\laragon\www\gelita\writable\pemeliharaan.flag` |

Selama berkas ada, semua halaman dan API menjawab **503** dengan pesan dwibahasa, sedangkan `php spark` tetap berjalan. `api.js` memperlakukan 503 non-JSON sebagai galat jaringan, sehingga `events.js` menyimpan event ke antrean lokal dan mengirimnya lagi setelah situs kembali (diuji: `POST /api/events` → 503). Event dari tab yang ditutup selama pemeliharaan tetap dapat hilang (`sendBeacon` tidak tahu hasilnya), sehingga aturan "jangan deploy saat kelas berjalan" tetap berlaku.

---

## Deployment

### Berkas yang dihasilkan tahap 8

| Berkas | Isi |
|---|---|
| `deploy/linux/deploy.sh` | pembaruan: cek kelas aktif → backup → pemeliharaan → pull → composer → migrate (DSN) → cache → `assetVersion` → reload FPM → verifikasi → buka |
| `deploy/linux/backup.sh` | backup database (tanpa isi `ci_sessions`), unggahan, `.env`; retensi 30 hari; salinan kedua |
| `deploy/linux/nginx/gelita-http.conf`, `gelita-https.conf` | konfigurasi Nginx Jalur L |
| `deploy/windows/deploy.ps1`, `backup.ps1`, `retensi.ps1` | padanan PowerShell (kompatibel Windows PowerShell 5.1; **hanya ASCII**) |
| `deploy/windows/config.example.psd1` | contoh `config.psd1` |
| `deploy/windows/nginx/gelita-http.conf`, `gelita-https.conf` | konfigurasi Nginx Jalur W |
| `.gitignore` | tambah `/public/assets/uploads/*`, `!/public/assets/uploads/.gitkeep`, `/writable/pemeliharaan.flag` |
| `tests/unit/DeployFilesTest.php` | mengunci cacat yang ditemukan saat skrip dijalankan (lihat [Catatan Implementasi Tahap 8](#catatan-implementasi-tahap-8)) |

Isi skrip ditulis lengkap di dokumen ini dan sama dengan berkas di `deploy/`. Bila keduanya berbeda, berkas di repositori yang berlaku. Skrip Bash dijalankan dengan environment kosong seperti cron di atas Ubuntu 24.04, MariaDB 10.11, Nginx 1.24, dan PHP-FPM 8.3. Skrip PowerShell dijalankan dengan PowerShell 7.4, sedangkan cara Windows PowerShell 5.1 membacanya disimulasikan. Setiap skrip diuji lewat jalur sukses, jalur gagal, dan penolakan saat ada kelas aktif.

### Deployment pertama — Jalur L

```text
Zona waktu WIB, paket, user deploy
   ↓
MySQL: 99-gelita.cnf + database + tiga akun
   ↓
git clone → composer install --no-dev → .env → key:generate
   ↓
izin (deploy / www-data), sudoers, /home/deploy/.gelita/
   ↓
migrate lewat DSN gelita_migrate            (35 migration, 31 tabel termasuk ci_sessions;
                                              JANGAN php spark session:migration)
   ↓
db:seed DatabaseSeeder → media:scan → bank:import (--dry-run lalu sungguhan) → content:verify
   ↓
PHP-FPM: 99-gelita.ini + pool → restart
   ↓
Nginx port 80 → certbot → Nginx 443
   ↓
cron (deploy: backup · www-data: retensi) → uji pulih backup
   ↓
putar sandi admin → verifikasi → siap dipakai kelas
```

```bash
# 0. Sistem — cron dan nama log harian mengikuti zona waktu ini (bawaan VPS: UTC)
sudo timedatectl set-timezone Asia/Jakarta
sudo adduser --disabled-password --gecos '' deploy
sudo usermod -aG www-data deploy
# paket: lihat "Ekstensi PHP wajib"; database & akun: lihat "Pengaturan MySQL" dan "Akun database"

# 1. Kode
sudo mkdir -p /var/www/gelita && sudo chown deploy:deploy /var/www/gelita
sudo -iu deploy                              # langkah 1–4 sebagai deploy
git clone <repo-url> /var/www/gelita
cd /var/www/gelita
composer install --no-dev --optimize-autoloader
cp env .env && nano .env                     # nilai wajib: lihat ".env production"
php spark key:generate
exit

# 2. Izin + sudoers: lihat "Model pengguna dan izin"

# 3. Berkas operasional
sudo install -d -m 700 -o deploy -g deploy /home/deploy/.gelita /var/backups/gelita
sudo -iu deploy
nano ~/.gelita/backup.cnf && nano ~/.gelita/migrate.dsn && chmod 600 ~/.gelita/*

# 4. Database
cd /var/www/gelita
export database_default_DSN="$(cat ~/.gelita/migrate.dsn)"
sudo --preserve-env=database_default_DSN -u www-data php spark migrate
unset database_default_DSN

read -rsp 'Sandi admin pertama: ' GELITA_ADMIN_PASSWORD && echo && export GELITA_ADMIN_PASSWORD
sudo --preserve-env=GELITA_ADMIN_PASSWORD -u www-data php spark db:seed DatabaseSeeder
unset GELITA_ADMIN_PASSWORD

sudo -u www-data php spark gelita:media:scan

# Bank soal: workbook disusun dari Dokumen Bank Soal GELITA (format di 07, FITUR 12a).
# Bisa juga lewat panel: /admin/konten/impor-bank
cp ~/bank-soal.xlsx writable/uploads/
sudo -u www-data php spark gelita:bank:import writable/uploads/bank-soal.xlsx --dry-run
sudo -u www-data php spark gelita:bank:import writable/uploads/bank-soal.xlsx
sudo -u www-data php spark gelita:content:verify
exit

# 5. PHP-FPM dan Nginx + HTTPS: lihat "Pengaturan PHP", "Nginx", "HTTPS"
# 6. Cron: lihat "Tugas Terjadwal"; lalu uji pulih: lihat "Backup"
```

`read -rs` membuat sandi admin tidak tercatat di riwayat shell. Di `CI_ENVIRONMENT = production`, `DatabaseSeeder` **tidak** menjalankan `SampleItemSeeder`, jadi `gelita:content:verify` baru lolos setelah bank soal diimpor.

### Deployment pertama — Jalur W

```text
Windows: zona waktu, daya, firewall, jam aktif Windows Update, akun gelita
   ↓
Laragon: Nginx + MySQL aktif, Apache mati, Auto virtual hosts mati, autostart, PATH
   ↓
PHP: ekstensi + php.ini + PHP_FCGI_CHILDREN    MySQL: sandi root + my.ini + database + tiga akun @'127.0.0.1'
   ↓
git clone (autocrlf=false) → composer install --no-dev → .env → key:generate
   ↓
C:\gelita-ops\ (config.psd1, backup.cnf, migrate.dsn) + ACL
   ↓
migrate lewat DSN → db:seed → media:scan → bank:import → content:verify
   ↓
Nginx gelita-http.conf → simple-acme → gelita-https.conf
   ↓
Task Scheduler (backup, retensi) → uji pulih backup → putar sandi admin → verifikasi
```

**0. Windows** (PowerShell sebagai Administrator):

```powershell
Set-TimeZone -Id 'SE Asia Standard Time'          # (UTC+07:00) Bangkok, Hanoi, Jakarta
powercfg /change standby-timeout-ac 0
powercfg /change hibernate-timeout-ac 0
New-NetFirewallRule -DisplayName 'GELITA HTTP/HTTPS' -Direction Inbound `
    -Protocol TCP -LocalPort 80,443 -Action Allow
```

- **Windows Update.** Setel jam aktif (*Settings → Windows Update → Advanced options → Active hours*) mencakup jam sekolah, supaya restart otomatis tidak memutus kelas.
- **Akun `gelita`.** Buat akun lokal standar untuk menjalankan Laragon. Laragon adalah aplikasi desktop, bukan layanan Windows: ia hanya berjalan bila akun itu sudah login.
- **Setelah restart.** Aktifkan login otomatis untuk akun `gelita` (misalnya Sysinternals *Autologon*, yang menyimpan sandi terenkripsi) dan kunci layar. Kalau tidak, pastikan ada orang yang login setelah setiap restart.

**1. Laragon.**

- Pasang Laragon Full di `C:\laragon`.
- Preferences → *General*: centang *Run Laragon when Windows starts* dan *Start All automatically*; matikan *Auto virtual hosts*.
- Preferences → *Services & Ports*: Nginx dan MySQL aktif, Apache mati.
- Menu → *Tools* → *Path* → *Add Laragon to Path*, supaya `php`, `composer`, `git`, dan `mysql` dikenal PowerShell.
- Atur PHP dan MySQL seperti di bagian [Production Environment](#production-environment).

**2. Kode dan `.env`** (PowerShell sebagai akun `gelita`):

```powershell
git clone -c core.autocrlf=false <repo-url> C:\laragon\www\gelita
Set-Location C:\laragon\www\gelita
composer install --no-dev --optimize-autoloader
Copy-Item env .env
notepad .env                                      # nilai wajib: lihat ".env production"
php spark key:generate
```

`core.autocrlf=false` membuat berkas identik dengan repositori, sehingga checksum aset vendor di `public/assets/vendor/README.md` tetap cocok.

**3. Berkas operasional:** buat `C:\gelita-ops\config.psd1`, `backup.cnf`, `migrate.dsn`, lalu pasang ACL (lihat [Model pengguna dan izin](#model-pengguna-dan-izin)).

**4. Database:**

```powershell
$env:database_default_DSN = (Get-Content C:\gelita-ops\migrate.dsn -Raw).Trim()
php spark migrate
Remove-Item Env:\database_default_DSN

$s = Read-Host 'Sandi admin pertama' -AsSecureString
$env:GELITA_ADMIN_PASSWORD = [Runtime.InteropServices.Marshal]::PtrToStringBSTR(
    [Runtime.InteropServices.Marshal]::SecureStringToBSTR($s))
php spark db:seed DatabaseSeeder
Remove-Item Env:\GELITA_ADMIN_PASSWORD

php spark gelita:media:scan
Copy-Item $HOME\Downloads\bank-soal.xlsx writable\uploads\
php spark gelita:bank:import writable/uploads/bank-soal.xlsx --dry-run
php spark gelita:bank:import writable/uploads/bank-soal.xlsx
php spark gelita:content:verify
```

Sintaks `GELITA_ADMIN_PASSWORD='…' php spark …` di Revisi 2 adalah sintaks Bash; di PowerShell maupun cmd sintaks itu galat. Jalankan perintah di atas di **PowerShell**, bukan di terminal Cmder bawaan Laragon.

**5. Nginx dan HTTPS:** lihat [Nginx → Jalur W](#jalur-w--laragon) dan [HTTPS](#https).

**6. Tugas terjadwal dan uji pulih:** lihat [Tugas Terjadwal](#tugas-terjadwal) dan [Backup](#backup).

### Langkah terakhir (kedua jalur)

1. **Ganti sandi admin.** Sandi awal pernah diketik di terminal. Masuk sebagai `admin`, buka **Ubah sandi** di kepala panel (`/admin/akun/sandi`), dan ganti dengan sandi baru minimal 12 karakter yang disimpan di pengelola sandi.
2. Buat akun `guru` terpisah per guru dengan `school_id` terisi. Sandi awal yang Anda pilih wajib diganti guru saat pertama masuk; panel baru terbuka setelah itu.
3. Jalankan [Verifikasi Setelah Deploy](#verifikasi-setelah-deploy).

### Deployment pembaruan

Jangan pernah melakukan deploy saat ada kelas berjalan. Kedua skrip memeriksa sesi `active` dalam 10 menit terakhir dan **membatalkan diri** bila ada. `--paksa` / `-Paksa` hanya untuk keadaan darurat.

Bila verifikasi konten gagal, situs **tetap** dalam mode pemeliharaan: lebih baik 503 yang jelas daripada kelas yang membuka konten rusak. Perbaiki penyebabnya, lalu hapus berkas penanda.

**Jalur L** — `deploy/linux/deploy.sh`, dijalankan sebagai `deploy` (`bash /var/www/gelita/deploy/linux/deploy.sh`):

```bash
#!/usr/bin/env bash
# deploy/linux/deploy.sh — pembaruan GELITA. Jalankan sebagai user deploy:
#   bash /var/www/gelita/deploy/linux/deploy.sh [--paksa]
set -Eeuo pipefail

OPS=/home/deploy/.gelita          # backup.cnf, migrate.dsn, ops.conf (chmod 600)
APP=/var/www/gelita
BRANCH=main
FPM=php8.3-fpm
# shellcheck source=/dev/null
[ -r "$OPS/ops.conf" ] && . "$OPS/ops.conf"
FLAG="$APP/writable/pemeliharaan.flag"

spark() { sudo -u www-data php "$APP/spark" "$@"; }

cd "$APP"

echo "→ Periksa kelas yang sedang berjalan"
AKTIF=$(mysql --defaults-extra-file="$OPS/backup.cnf" -N gelita -e "
  SET time_zone = '+07:00';
  SELECT COUNT(*) FROM game_sessions
  WHERE status = 'active' AND last_active_at > NOW() - INTERVAL 10 MINUTE;")
if [ "$AKTIF" != "0" ] && [ "${1:-}" != "--paksa" ]; then
  echo "BATAL: $AKTIF sesi permainan aktif dalam 10 menit terakhir. Tunda deploy." >&2
  exit 1
fi

echo "→ Backup database"
bash "$APP/deploy/linux/backup.sh" || [ $? -eq 2 ]   # 2 = hanya salinan kedua gagal

echo "→ Mode pemeliharaan"
touch "$FLAG"
trap 'echo "DEPLOY GAGAL — situs tetap dalam mode pemeliharaan. Perbaiki, lalu: rm $FLAG" >&2' ERR

echo "→ Ambil kode terbaru"
git pull --ff-only origin "$BRANCH"

echo "→ Dependency"
composer install --no-dev --optimize-autoloader --no-interaction

echo "→ Migration (akun gelita_migrate lewat DSN; bukan akun aplikasi)"
export database_default_DSN
database_default_DSN=$(cat "$OPS/migrate.dsn")
sudo --preserve-env=database_default_DSN -u www-data php "$APP/spark" migrate
unset database_default_DSN

echo "→ Bersihkan cache"
spark cache:clear

echo "→ Naikkan versi aset"
sed -i "s/^gelita.assetVersion.*/gelita.assetVersion = '$(date +%s)'/" .env
chgrp www-data .env && chmod 640 .env      # sed -i membuat berkas baru; jaga grupnya

echo "→ Reload PHP-FPM (opcache.validate_timestamps = 0)"
sudo systemctl reload "$FPM"

echo "→ Verifikasi konten (gagal = situs tetap dalam pemeliharaan)"
spark gelita:content:verify

echo "→ Aktifkan kembali"
rm -f "$FLAG"
echo "Deploy selesai."
```

`set -E` diperlukan agar `trap ERR` juga terpicu oleh galat di dalam fungsi `spark`. Tanpanya, skrip berhenti tanpa pesan pemulihan (terbukti saat uji).

**Jalur W** — `deploy\windows\deploy.ps1`, dijalankan sebagai akun `gelita`:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File C:\laragon\www\gelita\deploy\windows\deploy.ps1
```

```powershell
<#
  deploy\windows\deploy.ps1 - pembaruan GELITA di Windows + Laragon.
  Jalankan di PowerShell dengan akun Laragon:
    powershell -NoProfile -ExecutionPolicy Bypass -File C:\laragon\www\gelita\deploy\windows\deploy.ps1 [-Paksa]

  Berkas .ps1 di folder ini sengaja hanya berisi ASCII: Windows PowerShell 5.1
  membaca skrip tanpa BOM sebagai ANSI, dan byte UTF-8 dari tanda panah atau
  tanda pisah menjadi tanda kutip tipografis yang memutus string.
#>
param(
    [string]$Config = 'C:\gelita-ops\config.psd1',
    [switch]$Paksa
)

$ErrorActionPreference = 'Stop'
$c     = Import-PowerShellDataFile $Config
$ops   = Split-Path -Parent $Config
$exe   = if ($env:OS -eq 'Windows_NT') { '.exe' } else { '' }
$spark = Join-Path $c.App 'spark'
$flag  = [IO.Path]::Combine($c.App, 'writable', 'pemeliharaan.flag')

function Langkah([string]$Nama, [scriptblock]$Blok) {
    Write-Output "-> $Nama"
    $global:LASTEXITCODE = 0
    & $Blok
    if ($LASTEXITCODE -ne 0) { throw "$Nama gagal (kode $LASTEXITCODE)" }
}

Set-Location $c.App

Write-Output '-> Periksa kelas yang sedang berjalan'
$aktif = & (Join-Path $c.MySql "mysql$exe") "--defaults-extra-file=$(Join-Path $ops 'backup.cnf')" -N gelita -e "SET time_zone = '+07:00'; SELECT COUNT(*) FROM game_sessions WHERE status = 'active' AND last_active_at > NOW() - INTERVAL 10 MINUTE;"
if ($LASTEXITCODE -ne 0) { throw 'Tidak dapat memeriksa sesi aktif' }
if ([int]$aktif -gt 0 -and -not $Paksa) {
    Write-Host "BATAL: $aktif sesi permainan aktif dalam 10 menit terakhir. Tunda deploy." -ForegroundColor Red
    exit 1
}

Write-Output '-> Backup database'
& (Join-Path $PSScriptRoot 'backup.ps1') -Config $Config
if ($LASTEXITCODE -eq 2) { Write-Warning 'Salinan kedua backup gagal; deploy dilanjutkan dengan backup lokal.' }

Write-Output '-> Mode pemeliharaan'
New-Item -ItemType File -Force -Path $flag | Out-Null

try {
    Langkah 'Ambil kode terbaru' { git pull --ff-only origin $c.Branch }
    Langkah 'Dependency' { composer install --no-dev --optimize-autoloader --no-interaction }

    # akun gelita_migrate lewat DSN - akun aplikasi tidak punya hak DDL
    $env:database_default_DSN = (Get-Content (Join-Path $ops 'migrate.dsn') -Raw).Trim()
    try {
        Langkah 'Migration (akun gelita_migrate)' { & $c.Php $spark migrate }
    } finally {
        Remove-Item Env:\database_default_DSN -ErrorAction SilentlyContinue
    }

    Langkah 'Bersihkan cache' { & $c.Php $spark cache:clear }

    Write-Output '-> Naikkan versi aset'
    $envFile = Join-Path $c.App '.env'
    $versi   = [DateTimeOffset]::UtcNow.ToUnixTimeSeconds()
    $isi     = [IO.File]::ReadAllText($envFile)
    $isi     = [regex]::Replace($isi, '(?m)^gelita\.assetVersion[^\r\n]*', "gelita.assetVersion = '$versi'")
    [IO.File]::WriteAllText($envFile, $isi)          # UTF-8 tanpa BOM

    Langkah 'Verifikasi konten (gagal = situs tetap dalam pemeliharaan)' { & $c.Php $spark gelita:content:verify }
} catch {
    Write-Host "DEPLOY GAGAL - situs tetap dalam mode pemeliharaan. Perbaiki, lalu hapus $flag" -ForegroundColor Red
    throw
}

Write-Output '-> Aktifkan kembali'
Remove-Item $flag
Write-Output 'Deploy selesai.'
```

Beda dengan Jalur L:

- **Tidak ada reload PHP.** Di Jalur W, opcache memeriksa stempel waktu berkas.
- **`.env` ditulis `[IO.File]::WriteAllText`** (UTF-8 tanpa BOM, CRLF dipertahankan), bukan `Set-Content`, yang di PowerShell 5.1 menulis ANSI atau ber-BOM.
- **Skrip `.ps1` hanya berisi ASCII** (`->` dan `-`, bukan `→` dan `—`). Windows PowerShell 5.1 membaca skrip tanpa BOM sebagai ANSI (cp1252). Byte terakhir `—` dalam UTF-8 (`0x94`) menjadi kutip ganda tipografis, dan byte terakhir `→` (`0x92`) menjadi kutip tunggal tipografis. PowerShell memperlakukan keduanya sebagai tanda kutip, jadi string terputus dan skrip gagal di-parse sebelum baris pertamanya dijalankan. Jangan menambahkan karakter non-ASCII saat menyunting; `DeployFilesTest` menolaknya.

### Rollback

Urutannya penting: **rollback migration dulu, baru kembalikan kode.** `migrate:rollback` menjalankan `down()` dari berkas migration rilis baru. Setelah `git reset`, berkas itu sudah tidak ada. CodeIgniter lalu berhenti dengan `There is a gap in the migration sequence near version number …` tanpa mengubah apa pun, tetapi kode keluarnya tetap **0**. Revisi 3 menulis urutan terbalik (terbukti di server uji).

Nomor batch terlihat di kolom `Batch` pada `php spark migrate:status`. `<batch-sebelumnya>` adalah batch terakhir **sebelum** deploy yang dibatalkan. Di `CI_ENVIRONMENT = production`, `migrate:rollback` meminta konfirmasi `[y, n]`; jawab `y`.

```bash
# Jalur L — sebagai deploy
cd /var/www/gelita
touch writable/pemeliharaan.flag

# 1. Bila deploy menyertakan migration — selagi kode rilis baru masih terpasang:
sudo -u www-data php spark migrate:status                # catat batch sebelum deploy
export database_default_DSN="$(cat ~/.gelita/migrate.dsn)"
sudo --preserve-env=database_default_DSN -u www-data php spark migrate:rollback -b <batch-sebelumnya>
unset database_default_DSN
sudo -u www-data php spark migrate:status                # batch rilis baru harus sudah hilang
# atau pulihkan dari backup bila migration tidak reversible (lihat "Backup")

# 2. Baru kembalikan kode
git reset --hard <commit-sebelumnya>
composer install --no-dev --optimize-autoloader
sudo -u www-data php spark cache:clear
sudo systemctl reload php8.3-fpm
rm writable/pemeliharaan.flag
```

```powershell
# Jalur W — sebagai akun gelita
Set-Location C:\laragon\www\gelita
New-Item -ItemType File -Force writable\pemeliharaan.flag | Out-Null

# 1. Bila deploy menyertakan migration — selagi kode rilis baru masih terpasang:
php spark migrate:status
$env:database_default_DSN = (Get-Content C:\gelita-ops\migrate.dsn -Raw).Trim()
php spark migrate:rollback -b <batch-sebelumnya>
Remove-Item Env:\database_default_DSN
php spark migrate:status

# 2. Baru kembalikan kode
git reset --hard <commit-sebelumnya>
composer install --no-dev --optimize-autoloader
php spark cache:clear
Remove-Item writable\pemeliharaan.flag
```

Bila kode sudah terlanjur di-reset, pasang lagi commit rilis yang gagal (`git reset --hard <commit-rilis-gagal>`), jalankan langkah 1, lalu langkah 2.

Migration yang menghapus kolom sulit di-rollback tanpa kehilangan data. Aturannya: migration yang menghapus kolom atau tabel **tidak pernah digabung** dalam deploy yang sama dengan perubahan lain, dan selalu didahului satu rilis yang berhenti memakai kolom itu.

---

## Assets

### Lokasi

| Jenis | Path | Asal di server | Cache browser |
|---|---|---|---|
| CSS (6 berkas) | `public/assets/css/` | git | `?v=` → immutable |
| JavaScript entry | `public/assets/js/game.js`, `admin.js` | git | `?v=` → immutable |
| Modul JavaScript | `public/assets/js/{core,game,engines,admin}/` | git | tanpa `?v` → revalidasi |
| Vendor JS | `public/assets/vendor/` (ECharts, Howler.js) | git | `?v=` → immutable |
| Font | `public/assets/fonts/` (Cinzel, Plus Jakarta Sans, IBM Plex Mono) | git | revalidasi |
| Data referensi | `public/assets/data/wilayah-id.json` | git | revalidasi |
| Aset resmi | `public/assets/{ui,char,bg,map,challenge,library,reward,audio}/` | **git** — saat ini baru `ui/placeholder.svg`; view memakai pengganti | revalidasi |
| Unggahan admin | `public/assets/uploads/{asset_key}.{ext}` | panel `/admin/media` — **hanya di server** | revalidasi |
| Berkas export | `writable/exports/` | aplikasi | tidak publik; dilayani controller berotorisasi |
| Log / cache | `writable/logs/`, `writable/cache/` | aplikasi | tidak publik |

Aset hanya punya dua jalan masuk ke server: **commit ke repositori** (folder aset resmi, didaftarkan `gelita:media:scan`) atau **unggah lewat panel** (folder `uploads/`, ikut backup). Jangan menyalin berkas langsung ke folder aset resmi di server: berkas itu tidak ada di git, tidak ikut backup, dan hilang saat server dibangun ulang.

### Cache-busting

`asset_url_versioned()` menambahkan `?v={gelita.assetVersion}` pada CSS, JavaScript entry, dan vendor. Setiap deploy menaikkan nilai itu, jadi browser mengambil versi baru tanpa hard-refresh. Ini penting karena komputer sekolah jarang dibersihkan cache-nya.

Tidak semua URL ber-`?v=`. Revisi 2 keliru mengira begitu:

- **Modul JavaScript** di-`import` relatif dari entry (`import … from '../core/dom.js'`). URL-nya tidak mewarisi query string entry.
- **Gambar dan audio** dari `media_assets` dirender `base_url($path)` tanpa `?v`. Nama berkas unggahan tetap `{asset_key}.{ext}` walau isinya diganti. Klaim Revisi 2 bahwa pratinjau memakai `?v={sha256}` tidak ada di kode.

Karena itu Nginx memberi `immutable` **hanya** pada URL ber-`?v=`, dan `no-cache` (revalidasi dengan `Last-Modified`/`ETag`, dijawab 304 bila tidak berubah) pada aset lain. Dengan aturan Revisi 2, modul lama dan gambar lama bertahan di browser sampai 30 hari setelah pembaruan.

### Ukuran aset

Aset gambar dan video mendominasi ukuran instalasi. Anjuran:

* Latar dan adegan: JPEG kualitas 80, maksimum 1920×1080.
* Objek dan opsi: PNG dengan transparansi, maksimum 400×400.
* Video pustaka: MP4 H.264, 720p, bitrate ≤ 1.5 Mbps, durasi ≤ 60 detik.
* Audio narasi: MP3 96–128 kbps mono.

Ukuran piksel resmi tiap slot ada di `Config\Gelita::$assetSizes` dan ditegakkan saat unggah. Batas unggah 64 MB (`Config\Gelita::$maxUploadBytes`, `upload_max_filesize`, `client_max_body_size 72M`).

---

## Tugas Terjadwal

Jam sistem wajib WIB (lihat langkah 0 kedua jalur). Revisi 2 menulis "02:00 WIB" di crontab tanpa menyetel zona waktu. Di VPS berzona UTC, retensi berjalan pukul 09.00 WIB, dan backup "jam sekolah" pukul 15, 19, dan 23 WIB.

| Tugas | Waktu | Jalur L | Jalur W |
|---|---|---|---|
| Backup | 01.00 setiap hari; 08.00, 12.00, 16.00 hari sekolah | cron `deploy` | Task Scheduler `\GELITA\Backup` |
| Retensi (`gelita:retention:run`) | 02.00 setiap hari | cron `www-data` | Task Scheduler `\GELITA\Retensi` |
| Rotasi log > 30 hari | Minggu 03.00 (L) / bersama retensi (W) | cron `www-data` | di dalam `retensi.ps1` |

Backup lebih sering pada jam sekolah disengaja: sesi kelas berlangsung pada jam itu, dan kehilangan satu sesi berarti mengulang satu kelas. Banyak SD masuk hari Sabtu. Bila demikian, ganti `1-5` menjadi `1-6` dan tambahkan `Saturday`.

**Jalur L** — `crontab -e` sebagai `deploy`:

```cron
0 1 * * *          bash /var/www/gelita/deploy/linux/backup.sh >> /var/backups/gelita/backup.log 2>&1
0 8,12,16 * * 1-5  bash /var/www/gelita/deploy/linux/backup.sh >> /var/backups/gelita/backup.log 2>&1
```

`sudo crontab -u www-data -e`:

```cron
0 2 * * * cd /var/www/gelita && php spark gelita:retention:run >> writable/logs/cron.log 2>&1
0 3 * * 0 find /var/www/gelita/writable/logs -name 'log-*.log' -mtime +30 -delete
```

Revisi 2 mengarahkan log backup ke `/var/log/gelita-backup.log`, yang tidak dapat ditulis `deploy`. Shell gagal membuka berkas itu sehingga backup **tidak pernah dijalankan**. Log kini di folder backup milik `deploy`.

**Jalur W** — PowerShell sebagai Administrator. Tugas berjalan dengan akun `gelita`, "baik login maupun tidak", tetapi tetap butuh Laragon (MySQL) menyala:

```powershell
$akun  = "$env:COMPUTERNAME\gelita"
$sandi = (Get-Credential $akun).GetNetworkCredential().Password
$dir   = 'C:\laragon\www\gelita\deploy\windows'
$cfg   = 'C:\gelita-ops\config.psd1'
$arg   = { param($f) "-NoProfile -ExecutionPolicy Bypass -File `"$dir\$f`" -Config `"$cfg`"" }

$hariSekolah = 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'
$pemicuBackup = @(
    New-ScheduledTaskTrigger -Daily -At '01:00'
    New-ScheduledTaskTrigger -Weekly -DaysOfWeek $hariSekolah -At '08:00'
    New-ScheduledTaskTrigger -Weekly -DaysOfWeek $hariSekolah -At '12:00'
    New-ScheduledTaskTrigger -Weekly -DaysOfWeek $hariSekolah -At '16:00'
)
Register-ScheduledTask -TaskPath '\GELITA\' -TaskName 'Backup' -User $akun -Password $sandi `
    -Action (New-ScheduledTaskAction -Execute 'powershell.exe' -Argument (& $arg 'backup.ps1')) `
    -Trigger $pemicuBackup
Register-ScheduledTask -TaskPath '\GELITA\' -TaskName 'Retensi' -User $akun -Password $sandi `
    -Action (New-ScheduledTaskAction -Execute 'powershell.exe' -Argument (& $arg 'retensi.ps1')) `
    -Trigger (New-ScheduledTaskTrigger -Daily -At '02:00')

Start-ScheduledTask -TaskPath '\GELITA\' -TaskName 'Backup'     # uji sekali, lalu periksa D:\gelita-backup
```

`deploy\windows\retensi.ps1`:

```powershell
<#
  deploy\windows\retensi.ps1 - retensi harian + rotasi log. Dijalankan Task Scheduler.
#>
param([string]$Config = 'C:\gelita-ops\config.psd1')

$c    = Import-PowerShellDataFile $Config
$logs = [IO.Path]::Combine($c.App, 'writable', 'logs')

# Keluaran php.exe adalah UTF-8. Tanpa baris ini PowerShell membacanya dengan
# code page OEM, dan tanda panah di ringkasan retensi menjadi karakter rusak
# di cron.log. Dibungkus try: proses tanpa konsol menolak pengaturan ini.
try { [Console]::OutputEncoding = [Text.Encoding]::UTF8 } catch { }

# 'Continue': di PowerShell 5.1, stderr program yang dialihkan 2>&1 menjadi galat
# yang menghentikan skrip bila preferensinya 'Stop'.
$ErrorActionPreference = 'Continue'
& $c.Php ([IO.Path]::Combine($c.App, 'spark')) gelita:retention:run 2>&1 |
    Out-File -FilePath (Join-Path $logs 'cron.log') -Append -Encoding utf8
$kode = $LASTEXITCODE

Get-ChildItem $logs -Filter 'log-*.log' |
    Where-Object { $_.LastWriteTime -lt (Get-Date).AddDays(-30) } |
    Remove-Item

exit $kode
```

Hasil setiap tugas terlihat di Task Scheduler (*Last Run Result*: `0x0` berhasil, `0x1` gagal, `0x2` salinan kedua backup gagal).

---

## Backup

Isi setiap backup:

1. **Database, tanpa isi `ci_sessions`.** Tabel sesi memuat IP mentah dan data sesi siswa (lihat [Tata kelola data anak](#tata-kelola-data-anak)) dan tidak berguna setelah dipulihkan. Strukturnya tetap ikut, sehingga hasil pulih tetap berisi 31 tabel.
2. **`public/assets/uploads/`**, satu-satunya aset yang tidak ada di git.
3. **Salinan `.env`**, yang memuat `encryption.key`.

Backup disimpan 30 hari di lokasi pertama lalu disalin ke lokasi kedua. **Backup yang hanya ada di mesin yang sama dengan datanya bukan backup.** Bila salinan kedua gagal, skrip keluar dengan kode **2** dan mencetak peringatan. Revisi 2 hanya mencetak `echo` lalu keluar 0, sehingga kegagalan tidak terlihat oleh cron.

Backup memuat data pribadi anak dan kredensial. Folder backup hanya dapat dibaca pemiliknya (`umask 077` / ACL). Drive eksternal sebaiknya dienkripsi (LUKS / BitLocker To Go).

**Jalur L** — `deploy/linux/backup.sh`:

```bash
#!/usr/bin/env bash
# deploy/linux/backup.sh — backup GELITA. Dijalankan cron milik user deploy.
set -euo pipefail
umask 077

OPS=/home/deploy/.gelita              # backup.cnf, migrate.dsn, ops.conf (chmod 600)
APP=/var/www/gelita
DIR=/var/backups/gelita               # milik deploy, chmod 700
KEDUA=/mnt/backup-eksternal/gelita    # drive eksternal / NAS / mesin lain
# Nilai khusus server ditulis di ops.conf, bukan di sini: skrip ini dilacak git.
# shellcheck source=/dev/null
[ -r "$OPS/ops.conf" ] && . "$OPS/ops.conf"
STAMP=$(date +%Y%m%d-%H%M)
DB="$DIR/gelita-db-$STAMP.sql.gz"
trap 'rm -f "$DB.part"' EXIT

mkdir -p "$DIR"

# 1. Database. Isi ci_sessions (IP mentah + data sesi) tidak ikut; strukturnya tetap.
{
  mysqldump --defaults-extra-file="$OPS/backup.cnf" --single-transaction --quick \
    --no-tablespaces --default-character-set=utf8mb4 \
    --ignore-table=gelita.ci_sessions gelita
  mysqldump --defaults-extra-file="$OPS/backup.cnf" --no-data --no-tablespaces \
    gelita ci_sessions
} | gzip > "$DB.part"
gzip -t "$DB.part"
mv "$DB.part" "$DB"

# 2. Aset yang diunggah admin (tidak ada di git)
tar czf "$DIR/gelita-uploads-$STAMP.tar.gz" -C "$APP/public/assets" uploads

# 3. Konfigurasi — memuat encryption.key dan kredensial; ikut dilindungi umask 077
cp "$APP/.env" "$DIR/gelita-env-$STAMP"

# 4. Simpan 30 hari di lokasi pertama
find "$DIR" -name 'gelita-*' -mtime +30 -delete

echo "$STAMP backup lokal selesai ($(du -h "$DB" | cut -f1))"

# 5. Salinan kedua di luar mesin ini. Gagal = kode keluar 2, bukan diam.
if ! rsync -a "$DIR/" "$KEDUA/"; then
  echo "$STAMP PERINGATAN: salinan kedua ke $KEDUA GAGAL" >&2
  exit 2
fi
```

Yang diperbaiki terhadap `backup.sh` Revisi 2:

| Masalah | Akibat | Perbaikan |
|---|---|---|
| `-p"$MYSQL_PASS"` dengan `set -u`, variabel tidak pernah diisi | di cron: `MYSQL_PASS: unbound variable`; **tidak ada backup**, tetapi berkas `.sql.gz` kosong tertinggal dan terlihat seperti backup | `--defaults-extra-file` akun `gelita_backup` |
| sandi di baris perintah | terlihat di daftar proses | option file mode 600 |
| `gzip >` langsung ke nama akhir | dump gagal di tengah tetap meninggalkan berkas bernama backup | tulis `.part`, `gzip -t`, baru `mv` |
| tanpa `--no-tablespaces` | MySQL 8.0.21+ menuntut hak global `PROCESS` | ditambahkan (juga didukung MariaDB) |
| `--routines --triggers` | skema GELITA tidak punya routine/trigger | dihapus |
| isi `ci_sessions` ikut | IP mentah siswa tersimpan 30 hari × dua lokasi | hanya struktur |

**Jalur W** — `deploy\windows\backup.ps1`. Hasilnya berupa `gelita-db-{stamp}.zip` berisi dua berkas `.sql`, `gelita-uploads-{stamp}.zip`, dan `gelita-env-{stamp}.txt`:

```powershell
<#
  deploy\windows\backup.ps1 - backup GELITA di Windows + Laragon.
  Dijalankan Task Scheduler (akun yang sama dengan Laragon) dan oleh deploy.ps1.
  Kode keluar: 0 berhasil; 1 gagal; 2 backup lokal berhasil, salinan kedua gagal.
#>
param([string]$Config = 'C:\gelita-ops\config.psd1')

$ErrorActionPreference = 'Stop'
$c     = Import-PowerShellDataFile $Config
$ops   = Split-Path -Parent $Config
$exe   = if ($env:OS -eq 'Windows_NT') { '.exe' } else { '' }
$dump  = Join-Path $c.MySql "mysqldump$exe"
$cnf   = Join-Path $ops 'backup.cnf'
$stamp = Get-Date -Format 'yyyyMMdd-HHmm'

New-Item -ItemType Directory -Force -Path $c.Dir | Out-Null
$sqlData = Join-Path $c.Dir "gelita-db-$stamp.sql"
$sqlSesi = Join-Path $c.Dir "gelita-db-$stamp-ci_sessions.sql"

# 1. Database. --result-file, bukan ">": pengalihan PowerShell 5.1 menulis UTF-16.
#    Isi ci_sessions (IP mentah + data sesi) tidak ikut; strukturnya tetap.
try {
    & $dump "--defaults-extra-file=$cnf" --single-transaction --quick --no-tablespaces `
        --default-character-set=utf8mb4 --ignore-table=gelita.ci_sessions `
        "--result-file=$sqlData" gelita
    if ($LASTEXITCODE -ne 0) { throw "mysqldump gagal (kode $LASTEXITCODE)" }

    & $dump "--defaults-extra-file=$cnf" --no-data --no-tablespaces `
        "--result-file=$sqlSesi" gelita ci_sessions
    if ($LASTEXITCODE -ne 0) { throw "mysqldump ci_sessions gagal (kode $LASTEXITCODE)" }

    Compress-Archive -Path $sqlData, $sqlSesi -DestinationPath (Join-Path $c.Dir "gelita-db-$stamp.zip") -Force
} finally {
    Remove-Item $sqlData, $sqlSesi -ErrorAction SilentlyContinue
}

# 2. Aset yang diunggah admin (tidak ada di git)
Compress-Archive -Path ([IO.Path]::Combine($c.App, 'public', 'assets', 'uploads')) `
    -DestinationPath (Join-Path $c.Dir "gelita-uploads-$stamp.zip") -Force

# 3. Konfigurasi - memuat encryption.key dan kredensial. Copy-Item mewarisi
#    stempel waktu .env; tanpa baris kedua, salinan dari .env yang tidak berubah
#    lebih dari 30 hari langsung terhapus langkah 4 dan tidak pernah disalin.
$salinanEnv = Join-Path $c.Dir "gelita-env-$stamp.txt"
Copy-Item (Join-Path $c.App '.env') $salinanEnv
(Get-Item $salinanEnv).LastWriteTime = Get-Date

# 4. Simpan 30 hari di lokasi pertama
Get-ChildItem $c.Dir -Filter 'gelita-*' |
    Where-Object { $_.LastWriteTime -lt (Get-Date).AddDays(-30) } |
    Remove-Item

Write-Output "$stamp backup lokal selesai"

# 5. Salinan kedua di luar mesin ini. Gagal = kode keluar 2, bukan diam.
try {
    New-Item -ItemType Directory -Force -Path $c.Kedua | Out-Null
    Get-ChildItem $c.Dir -Filter 'gelita-*' |
        Where-Object { -not (Test-Path (Join-Path $c.Kedua $_.Name)) } |
        Copy-Item -Destination $c.Kedua
} catch {
    Write-Warning "$stamp salinan kedua ke $($c.Kedua) GAGAL: $($_.Exception.Message)"
    exit 2
}
```

Salinan kedua di kedua jalur hanya **menambah** berkas (tanpa `--delete` / `/MIR`). Dengan begitu, lokasi pertama yang rusak atau kosong tidak ikut menghapus salinan kedua.

### Uji pemulihan

Lakukan minimal sekali sebelum penelitian dimulai, dan setelah setiap perubahan skrip backup. Backup yang belum pernah dipulihkan statusnya belum diketahui.

```bash
# Jalur L
mysql -u root -p -e "CREATE DATABASE gelita_uji;"
gunzip -c /var/backups/gelita/gelita-db-YYYYmmdd-HHMM.sql.gz | mysql -u root -p gelita_uji
mysql -u root -p gelita_uji -e "
  SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'gelita_uji';  -- 31
  SELECT COUNT(*) FROM participants; SELECT COUNT(*) FROM game_event_logs;"
mysql -u root -p -e "DROP DATABASE gelita_uji;"
```

```powershell
# Jalur W
$d = "$env:TEMP\gelita-pulih"
Expand-Archive D:\gelita-backup\gelita-db-YYYYmmdd-HHMM.zip -DestinationPath $d -Force
mysql -u root -p -e "CREATE DATABASE gelita_uji;"
Get-ChildItem "$d\*.sql" | ForEach-Object {
    mysql -u root -p gelita_uji -e "source $($_.FullName -replace '\\', '/')"
}
mysql -u root -p gelita_uji -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'gelita_uji'; SELECT COUNT(*) FROM participants;"
mysql -u root -p -e "DROP DATABASE gelita_uji;"
Remove-Item $d -Recurse
```

Di PowerShell, operator `<` tidak ada, jadi pemulihan memakai `source`. Kedua berkas `.sql` tidak saling bergantung, jadi urutannya bebas. Pemulihan ke database production (`gelita`) mengikuti langkah yang sama dengan mode pemeliharaan aktif. Setelah itu, semua pengguna perlu login ulang karena `ci_sessions` kosong.

---

## Verifikasi Setelah Deploy

Jalankan seluruh daftar ini setelah deploy pertama dan setiap deploy pembaruan. Contoh memakai Jalur L. Padanan Jalur W: jalankan tanpa `sudo -u www-data`, pakai `C:\gelita-ops\backup.cnf`, dan tulis `curl.exe` (di Windows PowerShell 5.1, `curl` adalah alias `Invoke-WebRequest`) dengan `-o NUL` pengganti `-o /dev/null`.

```bash
# 1. Struktur konten — harus: 3 level, 5 node per level, semua engine valid, bank item cukup
sudo -u www-data php spark gelita:content:verify

# 2. Database — harus: 3, 15, satu rilis aktif, satu profil aktif
mysql --defaults-extra-file=/home/deploy/.gelita/backup.cnf gelita -e "
  SELECT COUNT(*) AS levels FROM levels WHERE is_active=1;
  SELECT COUNT(*) AS nodes  FROM challenge_nodes WHERE is_active=1;
  SELECT release_code FROM game_releases WHERE is_active=1;
  SELECT code, version FROM scoring_profiles WHERE is_active=1;"

# 3. writable dapat ditulis runtime (Jalur L)
sudo -u www-data touch /var/www/gelita/writable/uji && \
  rm /var/www/gelita/writable/uji && echo "writable OK"

# 4. HTTPS & header keamanan — pada halaman DAN aset, masing-masing tepat satu kali.
#    GET (-D - -o /dev/null) menampilkan header yang sama dengan HEAD (-I).
U=https://gelita.sekolah.sch.id
curl -s -D - -o /dev/null $U/ | grep -iE 'HTTP/|strict-transport|x-frame|x-content-type'
curl -s -D - -o /dev/null "$U/assets/css/game.css?v=1" | grep -iE 'strict-transport|x-content-type|cache-control'

# 5. Cache aset — harus: entry ber-?v immutable, modul tanpa ?v no-cache
curl -sI "$U/assets/js/game.js?v=1" | grep -i cache-control     # public, max-age=31536000, immutable
curl -sI "$U/assets/js/core/dom.js"  | grep -i cache-control     # no-cache

# 6. Berkas sensitif tidak terlayani — semuanya harus 404
for p in .env .htaccess .git/config app/Config/App.php writable/logs/ vendor/autoload.php info.php; do
  echo "$p → $(curl -s -o /dev/null -w '%{http_code}' "$U/$p")"
done

# 7. Mode pemeliharaan — harus 503, lalu 200
touch /var/www/gelita/writable/pemeliharaan.flag
curl -s -o /dev/null -w '%{http_code}\n' $U/
rm /var/www/gelita/writable/pemeliharaan.flag
curl -s -o /dev/null -w '%{http_code}\n' $U/

# 8. Sertifikat — tanggal kedaluwarsa dan perpanjangan
echo | openssl s_client -connect gelita.sekolah.sch.id:443 -servername gelita.sekolah.sch.id 2>/dev/null \
  | openssl x509 -noout -enddate
```

Uji manual:

1. Buka `/` — layar welcome tampil, gambar termuat.
2. Daftar siswa uji: coba sandi `kedu2026` — ditolak dengan keterangan syarat yang belum terpenuhi; lalu `Kedu#2026` — diterima. Keluar, lalu masuk kembali di `/masuk` — progres tetap.
3. Reset sandi siswa uji dari `/admin/peserta/{id}` — login dengan sandi sementara langsung diarahkan ke `/ganti-sandi`.
4. Kerjakan satu tantangan sampai selesai — bintang dan skor muncul.
5. Ganti bahasa ke EN di tengah permainan — progres tetap, teks berubah.
6. Masuk `/admin/login` — dashboard tampil, chart tergambar.
7. Buat export XLSX kecil — berkas terunduh dan terbuka di Excel.
8. Buka `/admin/sesi/{id}/event` — linimasa event sesi uji terlihat.
9. Hapus peserta uji lewat `/admin/tata-kelola` dengan pratinjau dan konfirmasi.
10. Buka `/admin/konten` — 15 node tampil dengan jumlah bank sesuai workbook; item `needs_verification` tertandai kuning.

Baru setelah **delapan pemeriksaan otomatis dan sepuluh uji manual** ini lolos, aplikasi dinyatakan siap dipakai kelas.

---

## Operational Notes

### Sebelum sesi kelas

1. Tetapkan **fase penelitian** yang benar di `/admin/studi` (`active_phase_code` = `umum` / `pretest` / `posttest`) sebelum anak mulai. Fase sebuah sesi tidak dapat diubah setelah ada event gameplay.
2. Untuk desain pretest–posttest, pastikan `item_selection_mode = fixed`. Tanpa ini, butir soal diacak dan kedua sesi tidak setara.
3. Periksa `gelita:content:verify` bersih.
4. Pastikan ruang disk cukup: `df -h` (L) / `Get-PSDrive C, D` (W).
5. Jalankan backup manual: `bash /var/www/gelita/deploy/linux/backup.sh` (L) / `Start-ScheduledTask -TaskPath '\GELITA\' -TaskName 'Backup'` (W).
6. Jalur W: pastikan Laragon menyala (Nginx + MySQL hijau) dan tidak ada restart Windows Update yang tertunda.
7. Sediakan **10–15 menit** di awal pertemuan pertama untuk registrasi. Membuat kata sandi kuat adalah bagian pembelajaran literasi keamanan digital, bukan hambatan teknis: biarkan anak membaca syaratnya dan memperbaiki sendiri sandi yang ditolak.
8. Pastikan guru tahu cara mereset sandi di `/admin/peserta/{id}`. Jangan meminta anak menuliskan sandinya di daftar kelas — bila lupa, reset.
9. Untuk anak kelas bawah yang kesulitan mengetik simbol, tunjukkan letak tombol simbol di papan ketik atau papan ketik layar tablet sebelum mulai.
10. **HP/tablet kelas: pasang GELITA di layar utama** sekali per perangkat. Buka alamat GELITA di Chrome (Android), lalu ketuk **Pasang GELITA** di halaman awal (atau menu ⋮ → *Instal aplikasi* / *Tambahkan ke layar utama*). Di iPhone/iPad buka di Safari, ketuk **Bagikan → Tambah ke Layar Utama**. Setelah itu anak membuka GELITA dari ikon lentera. Di Android layar terkunci mendatar dan tampil penuh tanpa bilah alamat; di iPhone/iPad rotasi tidak terkunci, jadi nyalakan *Putar otomatis*. Catatan: di iPhone/iPad, login aplikasi terpasang terpisah dari login di Safari.

### Setelah sesi kelas

1. Unduh export XLSX sebagai salinan kerja.
2. Periksa `/admin/sesi` — sesi yang masih `active` padahal kelas sudah selesai menandakan tab yang tidak ditutup; biarkan, retention akan menandainya `paused`.
3. Catat hal tak biasa (anak yang berhenti di tengah, perangkat bermasalah) di catatan penelitian, bukan di aplikasi.

### Pemantauan harian

```bash
# Jalur L
grep -c 'ERROR' /var/www/gelita/writable/logs/log-$(date +%Y-%m-%d).log     # galat hari ini
tail -3 /var/backups/gelita/backup.log                                      # backup terakhir + peringatan
M="mysql --defaults-extra-file=/home/deploy/.gelita/backup.cnf gelita"
$M -e "SELECT DATE(server_received_at) d, COUNT(*) n FROM game_event_logs
       WHERE server_received_at > NOW() - INTERVAL 7 DAY GROUP BY d ORDER BY d DESC;"
$M -e "SELECT table_name, ROUND(data_length/1048576) data_mb, ROUND(index_length/1048576) idx_mb
       FROM information_schema.tables WHERE table_schema='gelita' ORDER BY data_length DESC LIMIT 5;"
df -h /var/www /var/backups
```

```powershell
# Jalur W
Set-Location C:\laragon\www\gelita
(Select-String -Path "writable\logs\log-$(Get-Date -Format yyyy-MM-dd).log" -Pattern 'ERROR').Count
Get-ScheduledTaskInfo -TaskPath '\GELITA\' -TaskName 'Backup' | Select-Object LastRunTime, LastTaskResult
mysql --defaults-extra-file=C:\gelita-ops\backup.cnf gelita -e "SELECT DATE(server_received_at) d, COUNT(*) n FROM game_event_logs WHERE server_received_at > NOW() - INTERVAL 7 DAY GROUP BY d ORDER BY d DESC;"
Get-PSDrive C, D
```

`game_event_logs` akan menjadi tabel terbesar dan itu normal — ia memang catatan proses penelitian. Perkiraan kasar: satu peserta menyelesaikan 15 node menghasilkan sekitar 400–800 event, sekitar 0,5–1 MB termasuk index. Seratus peserta ≈ 100 MB. Jangan menghapusnya hanya karena agregat dashboard sudah jadi.

Bulanan: periksa tanggal kedaluwarsa sertifikat (pemeriksaan 8) dan jalankan satu uji pemulihan.

Pemantau uptime eksternal (bila dipakai) boleh memakai GET maupun HEAD ke `/`. HEAD dilayani rute GET dengan header yang sama tanpa isi (`App\Libraries\HeadAsGetRouteCollection`); sebelum perbaikan itu, HEAD dijawab 404.

### Masalah umum

| Gejala | Penyebab biasa | Tindakan |
|---|---|---|
| Halaman putih / HTTP 500 di semua halaman | L: `.env` tidak terbaca `www-data` (mode 600, atau grup berubah setelah `sed -i`) | `sudo chown deploy:www-data .env && sudo chmod 640 .env`; pastikan `deploy` anggota grup `www-data` |
| Halaman putih pada halaman tertentu | galat PHP, `display_errors` mati | baca `writable/logs/log-*.log` |
| Situs terus menjawab 503 | deploy gagal dan mode pemeliharaan sengaja dibiarkan | baca keluaran deploy, perbaiki, hapus `writable/pemeliharaan.flag` |
| "Unable to connect to database" | kredensial `.env` salah atau MySQL mati; W: Laragon belum menyala setelah restart | L: `systemctl status mysql`; W: buka Laragon → *Start All* |
| `CREATE command denied … gelita_app` saat migrate | migration berjalan tanpa DSN | jalankan lewat `database_default_DSN` (lihat Configuration) |
| Tampilan campur lama/baru setelah pembaruan | konfigurasi Nginx Revisi 2 (`immutable` pada semua aset) | pasang konfigurasi Revisi 3; minta browser *hard refresh* sekali |
| Halaman lambat saat kelas penuh | proses PHP habis | naikkan `pm.max_children` (L) / `PHP_FCGI_CHILDREN` (W) sesuai RAM |
| Export gagal di tengah | `memory_limit` atau batas 300 detik (W: dihitung jam dinding) | persempit rentang tanggal; periksa batas PHP |
| W: Nginx tidak mau start | port 80/443 dipakai Apache Laragon, IIS, atau aplikasi lain | matikan Apache di Laragon; `Get-NetTCPConnection -LocalPort 80` untuk mencari pemakainya |
| W: sertifikat kedaluwarsa padahal sudah diperpanjang | Nginx belum di-reload | jalankan `reload-nginx.cmd`; periksa langkah instalasi simple-acme |
| Tugas terjadwal W `0x1` | Laragon/MySQL mati saat tugas berjalan, atau path PHP berubah setelah ganti versi | nyalakan Laragon; perbarui `Php`/`MySql` di `config.psd1` |
| Gambar tidak muncul | aset belum diunggah atau `is_active = 0` | `/admin/media` → status hilang; `gelita:media:scan` |
| Audio tidak berbunyi | status masih `draft` | `/admin/media/audio` → Setujui |
| CSRF "action not allowed" | tab dibiarkan terbuka semalam | muat ulang halaman; `security.expires` 2 jam |
| Anak keluar / pindah komputer | session berakhir, tab tertutup | masuk lagi di `/masuk`; progres tetap |
| Staf selalu dikembalikan ke Ubah sandi | sandi sementara dari admin (reset / akun baru) belum diganti | ganti sandi di halaman itu; kewajiban hilang otomatis |
| Admin lupa kata sandi | tidak ada admin lain yang dapat mereset di `/admin/staf` | L: `sudo -u www-data php spark gelita:staff:password admin`; W: `php spark gelita:staff:password admin` di PowerShell. Masuk dengan sandi sementara yang dicetak, lalu buat sandi baru (reset juga membuka kunci) |
| Anak lupa kata sandi | — | guru mereset di `/admin/peserta/{id}`; anak membuat sandi baru saat masuk |
| Anak lupa nama pengguna | — | guru mencarinya di `/admin/peserta` berdasarkan nama & sekolah |
| "Terlalu banyak percobaan" | 8 kali salah sandi | tunggu 5 menit, atau guru mereset sandi (reset juga membuka kunci) |
| Registrasi terus ditolak | sandi belum memenuhi 5 syarat atau memuat nama pengguna | baca daftar syarat yang masih bertanda ✗ bersama anak |
| Impor bank soal gagal | galat baris di workbook | buka pratinjau, perbaiki sheet & baris yang disebut, unggah ulang |
| Event tertahan | koneksi putus sesaat, atau situs dalam pemeliharaan | otomatis terkirim saat aplikasi dibuka lagi di perangkat itu |
| Disk penuh | export lama + backup + video | jalankan retention; pindahkan backup lama |

### Keamanan operasional

1. Ganti sandi admin bawaan **sebelum** aplikasi dipakai di sekolah (lihat [Langkah terakhir](#langkah-terakhir-kedua-jalur)). Staf yang menerima sandi sementara (reset atau akun baru) dipaksa menggantinya lewat **Ubah sandi** saat masuk (`staff_users.must_change_password`).
2. Buat akun `guru` terpisah untuk tiap guru, dengan `school_id` terisi. Jangan membagikan akun admin.
3. Nonaktifkan akun guru yang tidak lagi terlibat lewat `/admin/staf` (tombol *Nonaktifkan*, `is_active = 0`); jangan hapus — audit log-nya masih diperlukan.
4. Tinjau `/admin/tata-kelola/audit` secara berkala, terutama aksi `export` dan `delete_execute`.
5. **Pembaruan sistem.**
   - L: `sudo apt update && sudo apt upgrade`.
   - W: Windows Update di luar jam sekolah, dan rilis patch PHP 8.3 lewat pembaruan Laragon. Ganti versi PHP berarti mengulang pengaturan `php.ini` (lihat *Pengaturan PHP*).
6. **Dependency Composer** diperbarui di lingkungan pengembangan (`composer update`, uji, commit `composer.lock`), lalu dibawa ke server lewat deploy biasa. Di server hanya `composer install` dan `composer audit`. `composer update` di server membuat `composer.lock` berbeda dari repositori dan membuat `git pull` berikutnya bentrok.
7. `.env`, `migrate.dsn`, dan `backup.cnf` tidak pernah masuk repositori. `.gitignore` memuat `.env`, isi `writable/*` per subfolder, dan, mulai tahap 8, `public/assets/uploads/*` dan `writable/pemeliharaan.flag`.
8. Port database (3306) tidak pernah dibuka ke jaringan (`bind-address = 127.0.0.1`; firewall Windows hanya membuka 80 dan 443).

### Tata kelola data anak

Dataset ini memuat data pribadi anak. Yang harus disiapkan bersama institusi sebelum pengambilan data:

1. **Persetujuan** — teks consent versi final, dengan persetujuan orang tua/wali. Versinya disimpan bersama buktinya di `participant_consents`.
2. **Retensi** — tetapkan `research_studies.retention_days` sesuai kesepakatan dengan sekolah dan institusi. Nilai bawaan 1825 hari adalah titik awal, bukan keputusan.
3. **Akses terbatas** — guru hanya sekolahnya, ekspor guru selalu anonim, akun admin sesedikit mungkin.
4. **Ekspor pseudonim sebagai default** — analisis dilakukan dengan kode peserta; nama hanya dibuka bila memang diperlukan dan dicatat di audit log.
5. **Penghapusan yang dapat ditelusuri** — selalu lewat pratinjau, konfirmasi, dan audit.
6. **Penarikan persetujuan** — bila orang tua menarik persetujuan, isi `participant_consents.withdrawn_at` lalu buat permintaan penghapusan untuk peserta itu.
7. **Kata sandi anak** — disimpan hanya sebagai hash; guru dan admin tidak dapat melihatnya. Sandi sementara hasil reset hanya tampil sekali. Nama pengguna diperlakukan sebagai data identitas dan tidak ikut export anonim. Yang dianalisis hanya jumlah syarat sandi terpenuhi pada percobaan pertama dan jumlah penolakan sandi lemah.
8. **IP mentah di `ci_sessions`.**
   - Karena `HashedIpSessionHandler` belum dipasang (lihat `02_PROJECT_FOUNDATION.md` § 3b), kolom `ci_sessions.ip_address` menyimpan IP **mentah** selama sesi hidup, sampai dibersihkan garbage collector sesi.
   - Tabel lain hanya menyimpan `ip_hash`.
   - Backup tahap 8 sengaja tidak membawa isi tabel ini.
   - Nyatakan hal ini dalam dokumen perlindungan data sampai handler hash dipasang.
9. **Backup** memuat data pribadi anak: batasi aksesnya dan enkripsi media salinan kedua.

Persetujuan etik dan penafsiran hukumnya tetap urusan institusi. Aplikasi menyediakan mekanismenya; kebijakannya ditetapkan manusia.

---

## Aturan Sistem

1. GELITA production selalu satu instansi server ber-HTTPS di domain sendiri, di Jalur L (Linux) atau Jalur W (Windows + Laragon). Tidak ada instalasi tanpa HTTPS di jaringan lokal.
2. Web server production adalah Nginx di kedua jalur. Hanya `index.php` yang dieksekusi PHP, dan berkas tersembunyi tidak pernah dilayani.
3. `.env` tidak pernah masuk repositori. Jalur L: `640 deploy:www-data`, dan `deploy` anggota grup `www-data`.
4. Akun MySQL aplikasi tidak punya hak DDL. Migration memakai `gelita_migrate` lewat `database_default_DSN`, backup memakai `gelita_backup` yang hanya membaca. Rahasia keduanya di luar repositori.
5. `database.default.DSN` tidak pernah ditulis di `.env`.
6. Jalur L: semua `php spark` dijalankan sebagai `www-data`.
7. `writable/exports/` tidak pernah dapat diakses langsung lewat URL.
8. Deploy selalu didahului backup, dibatalkan otomatis bila ada sesi kelas aktif, berjalan di balik mode pemeliharaan, dan hanya membuka situs kembali bila `gelita:content:verify` lolos.
9. Setiap deploy menaikkan `gelita.assetVersion`. Jalur L juga me-reload PHP-FPM; Jalur W mengandalkan `opcache.validate_timestamps = 1`.
10. Hanya URL ber-`?v=` yang boleh di-cache `immutable`; aset lain wajib revalidasi.
11. Aset masuk server hanya lewat git (folder aset resmi) atau panel (`uploads/`). Tidak ada salinan manual ke folder aset.
12. Backup disalin ke lokasi kedua dan hanya menambah berkas; kegagalan salinan kedua terlihat (kode 2). Backup yang belum pernah diuji pulih dianggap belum ada.
13. Jam sistem server WIB. Jadwal backup dan retensi mengikuti jam itu.
14. Retensi tidak menghapus data penelitian secara otomatis — ia hanya membuat permintaan berstatus pratinjau untuk disetujui manusia.
15. Sandi admin awal diputar sebelum pemakaian nyata, dan tiap guru punya akun sendiri dengan `school_id`.
16. Raw event tidak dihapus karena dashboard sudah punya agregat.
17. Fase penelitian dan mode pemilihan butir disetel sebelum kelas dimulai, bukan sesudahnya.
18. Bank soal production dimuat lewat impor workbook dengan pratinjau; tidak ada penyuntingan langsung ke database.
19. Lupa sandi siswa diselesaikan dengan reset oleh guru, tidak pernah dengan mencatat sandi anak.
20. Skrip di `deploy/` tidak diedit di server; nilai khusus server ditulis di `ops.conf` / `config.psd1`. `composer update` dan `php spark optimize` tidak dijalankan di server.
21. Rollback selalu dimulai dari migration (selagi kode rilis baru terpasang), baru kemudian kode.
22. Skrip `.ps1` dan `.psd1` hanya berisi ASCII, agar Windows PowerShell 5.1 membacanya sama dengan PowerShell 7.

---

## Catatan Revisi 3 — Hasil Audit

Revisi 2 dijalankan **apa adanya** di lingkungan uji. Setiap cacat di bawah punya bukti dari lingkungan itu, bukan dari membaca kode saja.

Lingkungan uji:

- Ubuntu 24.04, PHP 8.3.6 (FPM dan php-cgi) dan PHP 8.4 CLI, MariaDB 10.11.14, Nginx 1.24.0;
- CodeIgniter 4.7.4 dari `composer.lock`;
- PowerShell 7.4 untuk skrip `.ps1`.

### Cacat yang menghalangi deployment

| # | Revisi 2 | Yang terjadi | Perbaikan |
|---:|---|---|---|
| 1 | `DB_USER=gelita_migrate php spark migrate` | `DB_USER` tidak dibaca CodeIgniter; migrate berjalan sebagai `gelita_app` → `CREATE command denied`. Variabel `database_default_username` / `env "database.default.username=…"` juga kalah dari `.env` | `database_default_DSN` (kunci yang tidak ada di `.env`); terbukti: 34 migration, 31 tabel |
| 2 | `.env` `600 deploy:deploy` | PHP-FPM (`www-data`) tidak dapat membaca `.env` → **semua halaman HTTP 500** | `640 deploy:www-data` |
| 3 | `sed -i … .env` di `deploy.sh` | grup `.env` berubah menjadi `deploy` → HTTP 500 setelah setiap deploy | `deploy` masuk grup `www-data` + `chgrp`/`chmod` setelah `sed` |
| 4 | `php spark down` / `php spark up` | "Command not found", kode keluar 1 → `deploy.sh` berhenti setelah backup | mode pemeliharaan Nginx lewat `writable/pemeliharaan.flag` |
| 5 | `php spark session:migration` di blok perintah (bertentangan dengan diagram) | perintah tidak ada di 4.7.4 | dihapus |
| 6 | `backup.sh`: `-p"$MYSQL_PASS"` + `set -u` | di cron: `unbound variable`, tidak ada backup, tertinggal `.sql.gz` kosong | option file `gelita_backup`, tulis `.part` → `gzip -t` → `mv` |
| 7 | cron `>> /var/log/gelita-backup.log` sebagai `deploy` | berkas tidak dapat dibuka → perintah backup tidak dijalankan | log di `/var/backups/gelita/` |
| 8 | Nginx 443 dikonfigurasi, `nginx -t`, baru `certbot` | `nginx -t` gagal: sertifikat belum ada | dua berkas: port 80 dulu → `certbot certonly --webroot` → 443 |
| 9 | Crontab "02:00 WIB" tanpa menyetel zona waktu | zona waktu bawaan VPS UTC → retensi 09.00 WIB, backup jam sekolah bergeser 7 jam | `timedatectl set-timezone Asia/Jakarta` |

### Cacat perilaku dan keamanan

| # | Revisi 2 | Yang terjadi | Perbaikan |
|---:|---|---|---|
| 10 | `immutable` 30 hari untuk semua aset | modul JS yang di-import dan media unggahan tidak ber-`?v` → versi lama bertahan sampai 30 hari setelah pembaruan | `immutable` hanya untuk URL ber-`?v=`, lainnya `no-cache` (304) |
| 11 | `add_header` di `location` aset | header HSTS, nosniff, X-Frame hilang dari semua aset statis; dua header `Cache-Control` | `add_header` hanya di `server`, cache lewat `map` |
| 12 | `location ~ /\.(env\|git)` | `/.htaccess` terlayani (200) | semua berkas titik → 404; `.php` selain `index.php` → 404 |
| 13 | `chmod -R 775 writable` | berkas yang dilacak git mendapat bit eksekusi → working tree kotor | direktori `2775` saja |
| 14 | `php spark` sebagai `deploy` | cache `0640` dan log milik `deploy` tidak dapat ditulis PHP-FPM | semua `php spark` sebagai `www-data` |
| 15 | `pm.max_children` tidak disinggung | bawaan Ubuntu 5 proses untuk satu kelas penuh + export 300 detik | pool diatur (20 untuk RAM 4 GB) |
| 16 | `sql_mode = STRICT_TRANS_TABLES` | tidak berlaku bagi aplikasi (`strictOn = false` membuang STRICT per koneksi) | baris dihapus, alasannya dicatat |
| 17 | "pratinjau memakai `?v={sha256}`", "setiap URL aset memakai `asset_url_versioned()`" | tidak ada di kode | bagian Assets ditulis ulang sesuai kode |
| 18 | `composer update --no-dev` di server | `composer.lock` menyimpang dari repositori | hanya di pengembangan |
| 19 | isi `ci_sessions` ikut backup | IP mentah siswa tersalin ke dua lokasi selama 30 hari | hanya struktur tabel |
| 20 | `.gitignore` disebut memuat `public/assets/uploads/` | tidak demikian | tahap 8 menambahkannya |
| 21 | "ganti kata sandi admin lewat panel" | panel belum punya fitur ganti sandi staf | fitur **Ubah sandi** (`/admin/akun/sandi`, `AccountController`) ditambahkan setelah Revisi 3; sejak migration `003400` sandi sementara dari admin wajib diganti |
| 22 | "Delapan langkah verifikasi" | daftarnya berisi 10 butir (1, 2, 2a, 3–9) | 8 pemeriksaan otomatis + 10 uji manual, bernomor ulang |
| 23 | `add_header` Nginx untuk semua respons | halaman aplikasi mengirim `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy` **dua kali** (filter `secureheaders` CodeIgniter juga mengirimnya) | `fastcgi_hide_header` untuk ketiganya |
| 24 | pemeriksaan header dengan `curl -sI` | rute aplikasi hanya terdaftar untuk GET: `HEAD /` → 404, sehingga pemeriksaan tampak gagal | pemeriksaan memakai GET; setelah Revisi 3, HEAD dilayani rute GET beserta filternya (`HeadAsGetRouteCollection`) |

### Skenario dan platform

- Skenario laptop/jaringan lokal (tabel Konteks, `.env` tanpa HTTPS, bagian "Pemakaian di jaringan lokal") dihapus.
- Jalur W ditambahkan: Laragon + Nginx + php-cgi, PowerShell, Task Scheduler, simple-acme.
- Jebakan Windows yang ditangani:
  - vhost `auto.*` Laragon (`autoindex`, TLS 1.0, ditulis ulang);
  - `root` MySQL tanpa sandi;
  - `max_execution_time` jam dinding;
  - pengalihan `>` PowerShell 5.1 yang menulis UTF-16;
  - `Set-Content` ber-BOM;
  - `curl` sebagai alias;
  - sintaks `VAR=… cmd` yang hanya ada di Bash;
  - `localhost` → `::1`;
  - Laragon yang hanya berjalan saat akun login.
- Apache tidak lagi didokumentasikan untuk production.

### Keputusan yang tercatat

- **Export tetap sinkron** di tahap 8 (README tahap 7 menyerahkan keputusan ini ke sini). Alasannya:
  - batas `gelita.exportMaxRawEvents` (200.000 baris, ±18 MB memori) dan 300 detik sudah menjaga satu request tetap terkendali;
  - antrean kerja butuh proses latar yang harus dijaga di dua platform.
  - Ditinjau ulang bila export nyata di data penelitian mendekati 120 detik.
- **Mode pemeliharaan di Nginx, bukan filter CodeIgniter**: tanpa kode baru, `php spark` tetap bisa dipakai selama pemeliharaan, dan perilakunya sama di kedua jalur.
- **Tiga akun database** (ditambah `gelita_backup`): cron hanya memegang kredensial baca, kredensial DDL hanya dibaca saat deploy.

### Belum teruji di lingkungan audit

Hal berikut butuh mesin Windows atau domain sungguhan. Karena itu semuanya masuk daftar verifikasi dan harus diperiksa saat pemasangan pertama:

- GUI dan menu Laragon;
- direktif `http2 on;` (Nginx ≥ 1.25);
- `nginx -s reload` Laragon;
- penerbitan sertifikat nyata (Certbot/simple-acme);
- pendaftaran Task Scheduler;
- Windows PowerShell 5.1 itu sendiri (skrip ditulis tanpa fitur khusus PowerShell 7).

Tahap 8 mempersempit daftar ini. Daftar terkini ada di [Catatan Implementasi Tahap 8 → Masih belum teruji](#masih-belum-teruji).

---

## Catatan Implementasi Tahap 8

Tahap 8 memasang berkas `deploy/` sesuai Revisi 3, lalu menjalankannya di server uji: dari server kosong, lewat pembaruan, sampai rollback. Tiga cacat baru terlihat saat skrip benar-benar dijalankan. Ketiganya diperbaiki di berkas dan di dokumen ini.

Lingkungan uji:

- Ubuntu 24.04 berzona waktu Asia/Jakarta, MariaDB 10.11.14, Nginx 1.24.0, PHP-FPM 8.3.6 untuk web, PHP 8.4 CLI, dan CodeIgniter 4.7.4 dari `composer.lock`;
- user `deploy` + `www-data`, sudoers, `.env` 640, dan tiga akun database, persis seperti di [Model pengguna dan izin](#model-pengguna-dan-izin) dan [Akun database](#akun-database);
- repositori *origin* lokal, supaya `git pull` di skrip deploy menarik commit pembaruan sungguhan (perubahan view + satu migration uji);
- sertifikat self-signed di path Let's Encrypt, sebagai pengganti certbot;
- PowerShell 7.4 untuk skrip `.ps1`, dengan `config.psd1` berisi path Linux.

Bank soal uji memakai `SampleItemSeeder`, dengan `items_per_round` diturunkan **hanya di database uji**. Dengan begitu `gelita:content:verify` dapat dibuat lolos atau gagal sesuai skenario.

### Yang diuji

| Skenario | Hasil |
|---|---|
| Deployment pertama Jalur L: migrate lewat DSN → seed → `media:scan` | 35 migration, 31 tabel. `.env` 640 tidak memutus situs. Working tree tetap bersih setelah `chown`/`chmod` |
| `nginx -t` dengan blok 443 sebelum sertifikat ada | gagal (`cannot load certificate`), sesuai alasan urutan port 80 → sertifikat → 443 |
| Delapan [pemeriksaan otomatis](#verifikasi-setelah-deploy) | semua lolos. Empat header keamanan muncul tepat sekali di halaman, aset, dan 404. Login admin lewat HTTPS (CSRF, sesi database, cookie `Secure`) berhasil |
| `backup.sh` dengan environment kosong seperti cron | tanpa lokasi kedua: kode 2 dan backup lokal tetap jadi. Dengan `ops.conf`: kode 0. Semua berkas bermode 600 |
| Uji pemulihan Jalur L | 31 tabel. `ci_sessions` kosong. IP yang ditanam di `ci_sessions` tidak ada di dump |
| `deploy.sh` saat ada sesi aktif ≤ 10 menit | BATAL, kode 1, tanpa backup dan tanpa pemeliharaan. Sesi yang menganggur 11 menit tidak dihitung. `--paksa` melanjutkan |
| `deploy.sh` jalur sukses | 503 selama deploy, 200 sesudahnya. Migration uji berjalan dengan `gelita_migrate`. View baru terlayani setelah reload FPM. `assetVersion` naik dan `?v=` di halaman ikut berubah. `.env` tetap `640 deploy:www-data` |
| `deploy.sh` saat `content:verify` gagal | situs tetap 503. Pesan pemulihan dicetak `trap ERR` dari dalam fungsi `spark`. Kode 1 |
| Baris crontab di dokumen, dijalankan `/bin/sh` dengan environment kosong | backup tercatat di `backup.log`, retensi di `cron.log`, dan rotasi menghapus log > 30 hari |
| Rollback dengan urutan yang diperbaiki | migration uji di-rollback, kode dan view kembali ke rilis lama, situs 200 |
| `backup.ps1` | kode 0; kode 2 bila lokasi kedua tak dapat dibuat; kode 1 bila `mysqldump` gagal, tanpa `.sql` tertinggal |
| `deploy.ps1` | BATAL saat ada sesi aktif. Sukses: pull, migrate, `.env` tanpa BOM, penanda dihapus. Verifikasi gagal: penanda tetap |
| `retensi.ps1` | kode 0; kode 1 bila database tidak terjangkau; log > 30 hari terhapus |
| Uji pemulihan Jalur W (`Expand-Archive` + `source`) | semua tabel pulih, `ci_sessions` kosong |
| Konfigurasi Nginx Jalur W di Nginx Linux (path dipetakan, `http2 on;` dilepas, `upstream php_upstream` → FPM) | `nginx -t` lolos. Halaman 200, header tepat sekali, cache sesuai aturan, berkas sensitif 404, pemeliharaan 503 |

### Cacat yang ditemukan dan diperbaiki

Penomoran melanjutkan tabel [Catatan Revisi 3](#catatan-revisi-3--hasil-audit).

| # | Revisi 3 | Yang terjadi | Perbaikan |
|---:|---|---|---|
| 25 | Skrip `.ps1` memuat `→`, `—`, `·` dan disimpan sebagai UTF-8 tanpa BOM | Windows PowerShell 5.1 membacanya sebagai cp1252. Byte terakhir `—` menjadi kutip ganda tipografis, dan `deploy.ps1` gagal di-parse (3 galat di baris `"DEPLOY GAGAL — …"`). Di PowerShell 7.4 skrip yang sama lolos, sehingga cacat ini tidak terlihat saat audit | Skrip `.ps1`/`.psd1` hanya berisi ASCII. `DeployFilesTest` menolak karakter non-ASCII |
| 26 | Langkah 3 `backup.ps1`: `Copy-Item .env` | `Copy-Item` mewarisi stempel waktu `.env`. Bila `.env` tidak berubah lebih dari 30 hari, salinannya langsung dihapus langkah 4 dan tidak pernah sampai ke lokasi kedua. Akibatnya backup Windows tidak memuat `encryption.key` (terbukti dengan `.env` berumur 40 hari) | `LastWriteTime` salinan disetel ke waktu backup. Jalur L tidak terdampak: `cp` tanpa `-p` memberi stempel waktu baru |
| 27 | Rollback: `git reset` lalu `migrate:rollback` | Berkas migration rilis baru sudah hilang, sehingga muncul `There is a gap in the migration sequence`, tidak ada yang di-rollback, dan kode keluar tetap 0. Di production perintah itu juga menunggu konfirmasi `[y, n]` | [Rollback](#rollback): migration dulu, kode kemudian; `migrate:status` sebelum dan sesudah |

Perbaikan kecil lain:

- `retensi.ps1` menyetel `[Console]::OutputEncoding` ke UTF-8. Tanpa itu, PowerShell membaca keluaran `php.exe` dengan code page OEM, dan tanda panah di ringkasan `gelita:retention:run` menjadi karakter rusak di `cron.log`.
- `# shellcheck source=/dev/null` ditambahkan sebelum `. "$OPS/ops.conf"`. Kedua skrip Bash kini lolos `shellcheck` tanpa temuan.

`tests/unit/DeployFilesTest.php` mengunci hal-hal berikut:

- skrip Windows hanya ASCII;
- skrip Bash ber-LF dan memakai `set -E`;
- konfigurasi Nginx kedua jalur hanya berbeda di baris khusus platform;
- tidak ada `spark down`, `spark optimize`, `composer update`, atau `DB_USER`;
- stempel waktu salinan `.env` disegarkan;
- tiga aturan `.gitignore` ada.

Setiap aturan diuji balik dengan menyuntikkan kembali cacatnya.

### Masih belum teruji

Hal berikut butuh mesin Windows atau domain sungguhan, dan tetap masuk daftar verifikasi saat pemasangan pertama:

- Windows PowerShell 5.1 sungguhan. Cara 5.1 membaca skrip (cp1252) sudah disimulasikan dengan parser PowerShell, tetapi skripnya belum pernah dijalankan di 5.1;
- GUI dan menu Laragon, `nginx -s reload` Laragon, dan php-cgi Laragon di balik `php_upstream`;
- direktif `http2 on;` (butuh Nginx ≥ 1.25; Nginx uji 1.24);
- penerbitan sertifikat nyata (Certbot/simple-acme);
- pendaftaran Task Scheduler, termasuk `[Console]::OutputEncoding` pada tugas yang berjalan tanpa login.

---

## Dependency

Dari **01_DATABASE.md**: skema, migration, seeder, daftar tabel dan perkiraan pertumbuhannya.

Dari **02_PROJECT_FOUNDATION.md**: daftar ekstensi PHP, isi `.env`, struktur folder, daftar spark command, status `HashedIpSessionHandler` (§ 3b).

Dari **03–06**: seluruh kode aplikasi yang di-deploy; `asset_url_versioned()` dan pola `import` modul (06) menentukan aturan cache Nginx; perilaku antrean event saat 503 (06) menentukan mode pemeliharaan.

Dari **07_FEATURE_INTEGRATION.md**: `ExportService`, `ReportService`, `RetentionService`, format workbook bank soal, dan perilaku command `gelita:retention:run`, `gelita:content:verify`, `gelita:media:scan`, `gelita:score:recompute`, `gelita:bank:import`.

Dari **Dokumen Bank Soal GELITA**: isi workbook `bank-soal.xlsx` untuk deployment awal.

---

## Hasil Akhir

Setelah tahap ini selesai:

* GELITA berjalan sebagai satu instansi server lewat HTTPS, di Linux (Nginx + PHP-FPM) atau Windows + Laragon (Nginx + php-cgi), dengan header keamanan di setiap respons dan berkas sensitif tidak terlayani.
* Deployment pertama terdokumentasi langkah demi langkah untuk kedua jalur. Deployment pembaruan berjalan dengan satu skrip (`deploy.sh` / `deploy.ps1`) yang menolak berjalan saat kelas aktif, membuat backup, memakai mode pemeliharaan, dan hanya membuka situs bila konten lolos verifikasi. Rollback terdokumentasi untuk kedua jalur.
* Migration memakai akun DDL terpisah lewat DSN; aplikasi dan backup memakai akun tanpa hak DDL.
* Browser selalu mendapat aset terbaru setelah pembaruan, sementara aset ber-versi tetap di-cache lama.
* Backup berjalan otomatis ke dua lokasi tanpa IP mentah siswa, kegagalannya terlihat, dan prosedur pemulihannya sudah pernah diuji.
* Retensi harian berjalan pada jam WIB yang benar; data penelitian tidak pernah terhapus tanpa persetujuan manusia.
* Guru punya akun sendiri dengan cakupan sekolahnya dan dapat mereset sandi siswanya; admin dapat mengelola konten, ekspor, dan tata kelola data dengan jejak audit lengkap.
* Siswa registrasi dan masuk dengan nama pengguna serta kata sandi kuat; bank soal 15 node sudah dimuat dan lolos verifikasi konten.
* Delapan pemeriksaan otomatis dan sepuluh uji manual pasca-deploy lolos, dan aplikasi dinyatakan siap dipakai kelas.
