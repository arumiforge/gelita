# 08_DEPLOYMENT.md — Pemasangan Server & Operasional

Dokumen ini adalah panduan memasang GELITA di server **dari keadaan kosong** (instalasi baru) dan merawatnya sesudah itu: pembaruan kode, backup, tugas terjadwal, dan pemeriksaan harian. Tidak ada jalur pembaruan dari instalasi lama; server baru selalu dipasang mengikuti [Instalasi baru](#instalasi-baru).

Berkas operasional ada di folder [`deploy/`](../deploy/). Bila isi dokumen ini berbeda dengan berkas di sana, **berkas di repositori yang berlaku**.

---

## Tujuan

Menjalankan GELITA sebagai sistem production untuk penelitian siswa kelas 5 SD/MI. Dokumen ini mencakup:

- menyiapkan server Linux atau Windows;
- mengonfigurasi environment, database, web server, dan HTTPS;
- instalasi baru sampai aplikasi siap dipakai kelas, termasuk persiapan sebelum pengambilan data penelitian;
- pembaruan kode dan rollback;
- penataan aset, backup, dan tugas terjadwal;
- langkah operasional harian.

---

## Konteks

GELITA adalah satu instansi server. Siswa, guru, dan peneliti mengaksesnya lewat internet dengan HTTPS di satu nama domain, misalnya `gelita.sekolah.sch.id`. Satu instansi dapat dipakai banyak kelas dan sekolah. Guru membuka panel dari mana saja.

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

Yang **sama** di kedua jalur: kode dan `.env`, skema tiga akun database, konfigurasi Nginx (hanya path dan `fastcgi_pass` yang berbeda), mode pemeliharaan, urutan instalasi dan pembaruan, serta daftar verifikasi.

Syarat bersama:

1. **Nama domain** yang menunjuk ke IP publik server.
2. **Port 80 dan 443 terbuka dari internet.** Untuk server di jaringan sekolah, ini berarti *port forwarding* di router sekolah. Port 80 tetap diperlukan untuk validasi dan perpanjangan sertifikat.
3. Server menyala selama jam sekolah, dengan jam sistem di WIB.

Bila server Windows sekolah tidak dapat diberi IP publik atau *port forwarding*, gunakan Jalur L di VPS.

Yang **tidak** didukung:

- Instalasi tanpa HTTPS di jaringan lokal.
- Apache. `public/.htaccess` dibiarkan untuk pengembangan lokal, tetapi aturan cache, header, dan mode pemeliharaan ditulis untuk Nginx.
- `php spark serve` di production.

Catatan Laragon: Laragon adalah lingkungan pengembangan. Jalur W menutup kekurangannya untuk production: vhost otomatis yang ditulis ulang saat start, TLS lemah, `autoindex`, dan autostart. Periksa ketentuan lisensi di laragon.org sebelum memasangnya di institusi. Laragon 7 ke atas berlisensi berbayar, meski pemakaian non-komersial tanpa lisensi diizinkan dengan pengingat. Laragon 6 gratis, tetapi membawa PHP 8.1, jadi PHP 8.3 x64 harus ditambahkan manual ke `C:\laragon\bin\php\`.

---

## Production Environment

### Spesifikasi minimum

| Komponen | Minimum | Disarankan |
|---|---|---|
| CPU | 2 core | 4 core |
| RAM — Jalur L | 2 GB | 4 GB (unduhan XLSX besar butuh ruang) |
| RAM — Jalur W | 4 GB | 8 GB (Windows sendiri memakai 2–3 GB) |
| Disk | 20 GB | 50 GB (gambar/video + raw event + backup lokal) |
| OS — Jalur L | Ubuntu 22.04 LTS / Debian 12 | Ubuntu 24.04 LTS |
| OS — Jalur W | Windows 10/11 Pro 64-bit | Windows Server 2019+ atau Windows 11 Pro |

Ubuntu 22.04 hanya membawa PHP 8.1. Tambahkan PPA `ondrej/php` atau pakai Ubuntu 24.04, yang membawa PHP 8.3. Debian 12 membawa PHP 8.2 (didukung; `composer.json` meminta `php ^8.2`); ganti `8.3` menjadi `8.2` di seluruh nama paket, path, dan socket.

**Kapasitas** ditentukan jumlah proses PHP, bukan CPU. Setiap request memakai satu proses sampai selesai, dan pembuatan unduhan data dapat memegangnya sampai 300 detik. Karena itu `pm.max_children` (Jalur L) dan `PHP_FCGI_CHILDREN` (Jalur W) wajib dinaikkan dari bawaannya (lihat di bawah). Nginx di Windows memakai satu worker dengan `worker_connections 1024` (bawaan templat Laragon). Batas ini cukup untuk beberapa kelas bersamaan. Untuk banyak sekolah sekaligus, gunakan Jalur L.

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

Untuk MariaDB, ganti `mysql-server` dengan `mariadb-server`.

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

Bila salah satu tidak ada, hentikan di sini. PhpSpreadsheet (impor workbook Pustaka dan bank soal saat instalasi, unduhan data) gagal tanpa `zip` dan `xmlwriter`; mPDF gagal tanpa `mbstring`; CodeIgniter menolak jalan tanpa `intl`.

### Pengaturan PHP

**Jalur L** — `/etc/php/8.3/fpm/conf.d/99-gelita.ini`:

```ini
memory_limit = 512M
max_execution_time = 300
upload_max_filesize = 64M
post_max_size = 72M
max_file_uploads = 100
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

- `opcache.validate_timestamps = 0` mempercepat production, tetapi PHP-FPM **harus di-reload setiap pembaruan kode**. `deploy.sh` sudah melakukannya.
- `max_file_uploads = 100` (bawaan PHP 20) agar satu unggahan rekaman narasi di panel dapat memuat banyak berkas. Halaman **Rekaman narasi → Unggah** menampilkan batas yang berlaku.

Ubah juga pool `/etc/php/8.3/fpm/pool.d/www.conf`. Bawaan Ubuntu `pm.max_children = 5` berarti request keenam mengantre, dan satu unduhan data sudah memegang satu proses:

```ini
pm = dynamic
pm.max_children = 20              ; ±50 MB per proses: 20 untuk RAM 4 GB, 10 untuk 2 GB
pm.start_servers = 4
pm.min_spare_servers = 2
pm.max_spare_servers = 6
request_terminate_timeout = 330s  ; jaring pengaman di atas batas 300 detik
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

- **Opcache.** Tanpa perintah reload, `validate_timestamps = 0` akan membuat php-cgi terus menjalankan kode lama setelah pembaruan. Biaya pemeriksaan stempel waktu tiap 2 detik tidak berarti pada skala sekolah.
- **Batas waktu.** Di Windows, `max_execution_time` dihitung dengan jam dinding (termasuk menunggu database), bukan waktu CPU seperti di Linux. Karena itu unduhan besar lebih cepat menyentuh batas 300 detik; persempit rentang tanggal bila terjadi.
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

- `innodb_flush_log_at_trx_commit = 1` memang lebih lambat, tetapi data penelitian yang hilang karena listrik padam tidak dapat diulang.
- `default-time-zone` memakai offset, bukan `Asia/Jakarta`, supaya jalan tanpa tabel zona waktu (tidak ada di MySQL Windows). Aplikasi sendiri menyetel `time_zone` tiap koneksi lewat `db_sync_timezone()`. Nilai server ini dipakai klien `mysql` dan kueri pemeriksaan di skrip deploy.
- `sql_mode` tidak perlu disetel. `Config\Database::$default['strictOn'] = false` membuat CodeIgniter membuang `STRICT_*` dari `sql_mode` setiap koneksinya, jadi nilai global server tidak berpengaruh pada aplikasi. Mengaktifkan mode strict adalah keputusan kode (`strictOn`), bukan konfigurasi server.

Skema GELITA memakai InnoDB dan `utf8mb4_unicode_ci` di setiap tabel (`app/Database/GelitaMigration.php`), sama di MySQL dan MariaDB.

**Jalur W:** MySQL Laragon terpasang dengan `root` **tanpa sandi**. Setel sandinya sebelum langkah lain:

```powershell
mysql -u root -e "ALTER USER 'root'@'localhost' IDENTIFIED BY 'SANDI_ROOT_KUAT';"
```

### Akun database

Tiga akun, masing-masing dengan hak seperlunya:

| Akun | Hak | Dipakai oleh | Disimpan di |
|---|---|---|---|
| `gelita_app` | `SELECT, INSERT, UPDATE, DELETE` | aplikasi, seeder, semua `php spark` selain migration dan reset | `.env` |
| `gelita_migrate` | `ALL` pada `gelita.*` | **hanya** `php spark migrate` / `migrate:rollback` dan `php spark gelita:reset` | `migrate.dsn` (di luar repo) |
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

**Jalur W:** ganti `'localhost'` menjadi `'127.0.0.1'` di keenam baris. Di Windows, PHP dan klien `mysql` tersambung lewat TCP, dan `localhost` dapat diterjemahkan ke `::1` lebih dulu. Akun `@'127.0.0.1'` selalu cocok, apa pun setelan `skip-name-resolve`. Di `.env`, `backup.cnf`, dan `migrate.dsn`, tulis host `127.0.0.1` pula.

Pemisahan hak ini berarti bila aplikasi diretas lewat SQL injection, skema database tidak ikut menjadi korban. Seeder cukup memakai `gelita_app` karena hanya menulis data. `gelita:reset` butuh akun migrasi karena mengosongkan tabel dengan `TRUNCATE` (hak `DROP`); dengan akun aplikasi, reset ditolak sebelum ada tabel yang dikosongkan.

### Model pengguna dan izin

**Jalur L.** Dua pengguna sistem dengan peran terpisah:

| Pengguna | Memiliki | Menjalankan |
|---|---|---|
| `deploy` | kode (`git pull`, `composer`), `.env`, `/home/deploy/.gelita/`, `/var/backups/gelita/` | `deploy.sh`, `backup.sh` (cron) |
| `www-data` | `writable/`, `public/assets/uploads/`, `public/assets/min/` | PHP-FPM, Nginx, **semua `php spark`**, cron retensi |

```bash
sudo adduser --disabled-password --gecos '' deploy
sudo usermod -aG www-data deploy          # wajib — lihat butir 2 di bawah

cd /var/www/gelita
sudo mkdir -p public/assets/min           # salinan ringkas JS/CSS (gelita:aset:minify), tidak ada di git
sudo chown -R deploy:www-data .
sudo chown -R www-data:www-data writable public/assets/uploads public/assets/min
sudo find writable public/assets/uploads public/assets/min -type d -exec chmod 2775 {} +
sudo chown deploy:www-data .env && sudo chmod 640 .env
```

`/etc/sudoers.d/gelita-deploy` (buat dengan `sudo visudo -f /etc/sudoers.d/gelita-deploy`):

```
deploy ALL=(www-data) NOPASSWD: ALL
deploy ALL=(root) NOPASSWD: /usr/bin/systemctl reload php8.3-fpm, /usr/bin/systemctl reload nginx
```

`deploy` sudah menguasai kode yang dijalankan `www-data`, jadi hak menjalankan perintah sebagai `www-data` tidak menambah kuasa.

Alasan tiap aturan (semuanya terbukti saat skrip diuji, lihat [Catatan Implementasi Tahap 8](#catatan-implementasi-tahap-8)):

1. **`.env` = `640 deploy:www-data`, bukan `600 deploy:deploy`.** PHP-FPM berjalan sebagai `www-data`. Dengan mode 600, CodeIgniter 4.7 melempar galat "`.env` not readable" dan **setiap halaman menjadi HTTP 500**.
2. **`deploy` masuk grup `www-data`.** `sed -i` di `deploy.sh` menulis `.env` sebagai berkas baru. Bila pemanggilnya bukan anggota grup `www-data`, grup berkas berubah menjadi `deploy`, dan situs menjadi HTTP 500 setelah **setiap** pembaruan.
3. **Semua `php spark` dijalankan sebagai `www-data`** (`sudo -u www-data php spark …`). Cache ditulis mode `0640` dan log mode `0644`. Berkas yang dibuat `deploy` lewat CLI tidak dapat ditimpa PHP-FPM, sehingga cache gagal diam-diam dan log harian hilang. Ini berlaku juga untuk `db:seed`, `gelita:narration:import`, `gelita:mockups`, dan `gelita:reset`: semuanya menulis ke `public/assets/uploads/` (gambar contoh, poster video Pustaka, salinan rekaman), yang harus tetap dapat ditimpa panel.
4. **Jangan `chmod -R 775 writable`.** Perintah itu memberi bit eksekusi ke berkas yang dilacak git (`writable/index.html` dan lainnya). Working tree menjadi "kotor" dan `git pull` bisa menolak. Cukup direktori `2775` (setgid menjaga grup `www-data` untuk berkas baru).

**Jalur W.** Satu akun Windows standar (misalnya `gelita`) menjalankan Laragon, `php spark`, deploy, dan Task Scheduler, jadi tidak ada konflik izin antarpengguna. Batasi siapa yang dapat membaca folder rahasia dan backup. `*S-1-5-32-544` adalah grup Administrators, ditulis sebagai SID agar tidak bergantung pada bahasa Windows:

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
database.default.database = gelita
database.default.username = gelita_app
database.default.password = 'SANDI_APP'

encryption.key = hex2bin:…                     # diisi php spark key:generate

cookie.secure = true
logger.threshold = 4

gelita.ipSalt = '…'                            # php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
```

Catatan:

1. `app.baseURL` harus sama persis dengan domain HTTPS. Redirect login memakai nilai ini.
2. `encryption.key` **tidak pernah dibangkitkan ulang** setelah production berjalan. Token objek Cari Objek dan penanda antrean offline (`sessionTag`) diturunkan darinya. Kuncinya ikut backup lewat salinan `.env`.
3. `gelita.ipSalt` wajib acak. Bila bocor, `ip_hash` dapat dibalik dengan pencarian menyeluruh karena ruang alamat IPv4 kecil. Perintah `php -r` di atas bekerja di kedua jalur.
4. `gelita.assetVersion` dibiarkan ada di `.env` (templat: `'1'`). Skrip deploy menaikkan nilainya setiap pembaruan; baris yang hilang tidak akan ditambahkan skrip.
5. **Jangan menambahkan `database.default.DSN` ke `.env`.** Kunci itu disisihkan untuk akun migrasi (bagian berikut).
6. *(New, English.)* Optional: `gelita.eventFlush.intervalMs` (default `30000`) and `gelita.eventFlush.maxEvents` (default `40`, at most `maxEventsPerBatch` = 50) set how often the game sends its event batches (`Config\Gelita::$eventFlush`, rencana-log-asinkron §5.1). Lowering the interval makes Ringkasan's "Aktivitas terbaru" fresher at the cost of more requests; the note under that list follows the value. Leave both out to keep the defaults.
7. *(New, English.)* Optional: `gelita.screenFx = false` turns off the WebGL screen effects of the narrated scenes (three.js fog, sparks, light rays, bloom, and parallax; on by default). The scenes then play the CSS effects only. Use it if a class's devices stutter on the story screens; the browser already falls back by itself without WebGL 2, under reduced motion, and when frames stay slow. With package mode on, rebuild the package after changing it (**Bangun ulang paket**) only if the three.js files changed; the switch itself needs no rebuild.
8. Perubahan `.env` langsung berlaku di request berikutnya. Jangan menjalankan `php spark optimize`: perintah itu mengubah `app/Config/Optimize.php` (berkas yang dilacak git) dan mengaktifkan cache konfigurasi yang membekukan nilai `.env`, termasuk `gelita.assetVersion`.

### Kredensial migration lewat DSN

Akun di `.env` (`gelita_app`) tidak punya hak DDL. Untuk `migrate`, `migrate:rollback`, dan `gelita:reset`, akun migrasi dipasang lewat variabel environment `database_default_DSN`:

- Variabel environment **tidak dapat** menimpa kunci yang sudah ada di `.env`. `DotEnv` mengisi `$_ENV` dari `.env`, dan `BaseConfig::getEnvValue()` memeriksa `$_ENV` lebih dulu. Karena itu `database_default_username=…` maupun `env "database.default.username=…"` tetap menghasilkan `gelita_app`, dan `DB_USER` tidak pernah dibaca CodeIgniter. Tanpa DSN, migration gagal dengan `CREATE command denied to user 'gelita_app'@'localhost'`.
- Yang bekerja adalah kunci yang **tidak** ada di `.env`: `database_default_DSN`. CodeIgniter mengurai DSN dan menimpa host, pengguna, sandi, serta nama database.

Isi `migrate.dsn` (satu baris, mode 600, di luar repositori):

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

Cara yang sama berlaku untuk `migrate:rollback`, `gelita:reset`, dan database scratch saat pengembangan (ganti nama database di akhir DSN).

### Berkas operasional di luar repositori

Rahasia dan nilai khusus server tidak pernah masuk repositori. Skrip di `deploy/` juga tidak diedit di server, karena editan lokal membuat `git pull` bentrok.

| Berkas | Jalur L | Jalur W | Isi |
|---|---|---|---|
| folder | `/home/deploy/.gelita/` (700) | `C:\gelita-ops\` (ACL di atas) | |
| `backup.cnf` | ✓ (600) | ✓ | opsi klien MySQL untuk `gelita_backup` |
| `migrate.dsn` | ✓ (600) | ✓ | DSN `gelita_migrate` |
| `ops.conf` | opsional | — | menimpa bawaan skrip, mis. `KEDUA=/mnt/usb/gelita` |
| `config.psd1` | — | ✓ | path aplikasi, PHP, MySQL, folder backup, branch |

`backup.cnf`:

```ini
[client]
user=gelita_backup
password="SANDI_BACKUP"
host=localhost
```

Untuk Jalur W, tulis `host=127.0.0.1`. `--defaults-extra-file` harus menjadi opsi **pertama** `mysql`/`mysqldump`; skrip sudah mematuhinya. Opsi ini juga menghindari sandi di baris perintah, yang terlihat di daftar proses.

`ops.conf` (Jalur L) di-*source* oleh `deploy.sh` dan `backup.sh`, sehingga dapat menimpa variabel bawaan skrip: `APP`, `BRANCH`, `FPM` (deploy), `DIR`, `KEDUA` (backup).

`config.psd1` (salin dari [`deploy/windows/config.example.psd1`](../deploy/windows/config.example.psd1)), dibaca `deploy.ps1`, `backup.ps1`, dan `retensi.ps1`:

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

Konfigurasi dibagi dua berkas: port 80 dan aplikasi (443). Port 80 diaktifkan lebih dulu. Blok 443 menunjuk berkas sertifikat, dan `nginx -t` gagal ("cannot load certificate") selama berkas itu belum ada. Urutannya selalu: port 80 → terbitkan sertifikat → 443.

| Berkas di repositori | Tujuan |
|---|---|
| `deploy/linux/nginx/gelita-http.conf` | `/etc/nginx/sites-available/gelita-http` |
| `deploy/linux/nginx/gelita-https.conf` | `/etc/nginx/sites-available/gelita-https` |
| `deploy/windows/nginx/gelita-http.conf` | `C:\laragon\etc\nginx\sites-enabled\gelita-http.conf` |
| `deploy/windows/nginx/gelita-https.conf` | `C:\laragon\etc\nginx\sites-enabled\gelita-https.conf` |

Ganti `gelita.sekolah.sch.id` dengan domain server di setiap berkas.

#### Jalur L

`gelita-http.conf` hanya melayani validasi Let's Encrypt dan mengalihkan sisanya ke HTTPS:

```nginx
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

Aturan di `gelita-https.conf`:

| Aturan | Isi di berkas | Alasan |
|---|---|---|
| TLS | `listen 443 ssl http2`, `ssl_protocols TLSv1.2 TLSv1.3`, sertifikat dari `/etc/letsencrypt/live/{domain}/` | Nginx ≥ 1.25.1: tulis `listen 443 ssl;` + `http2 on;` |
| Header keamanan | `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `Strict-Transport-Security`, semuanya `always`, **hanya** di blok `server` | `add_header` di dalam `location` menghapus semua header warisan `server` |
| Header ganda | `fastcgi_hide_header` untuk `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy` | filter `secureheaders` CodeIgniter mengirim header yang sama; setiap header muncul tepat sekali |
| Cache aset | `map` `$gelita_is_asset` + `$arg_v` → `$gelita_cache`: URL aset ber-`?v=` mendapat `public, max-age=31536000, immutable`, aset lain `no-cache`; asset package URLs (`/assets/p/`, `/assets/k/`, `/paket/pkt-*.json`) are immutable without `?v=` | lihat [Cache-busting](#cache-busting) dan [Asset package](#asset-package-download-once-mode) |
| PHP | hanya `location = /index.php` yang diteruskan ke `unix:/run/php/php8.3-fpm.sock` (`fastcgi_read_timeout 300`); `.php` lain → 404 | tidak ada skrip lain yang dapat dieksekusi |
| Routes without a file | `location /` uses `try_files $uri $uri/ /index.php$is_args$args`; only `/assets/` answers 404 for a missing file | `/manifest.json` has no file in `public/` (it is made by `AppController`), so it reaches `index.php`. Never put a static `public/manifest.json` back: it would shadow the route. Apache does the same through `public/.htaccess` (`!-f`, `!-d` → `index.php`) |
| Berkas tersembunyi | `location ~ /\.` → 404, juga di dalam `/assets/` | `.env`, `.git`, `.htaccess`, `.gitkeep` tidak pernah dilayani |
| Mode pemeliharaan | `if (-f /var/www/gelita/writable/pemeliharaan.flag) { return 503; }` + halaman 503 dwibahasa | lihat [Mode pemeliharaan](#mode-pemeliharaan) |
| Unggahan | `client_max_body_size 72M` | sejalan dengan `post_max_size` |
| Kompresi | `gzip` untuk CSS, JavaScript, JSON, SVG ≥ 1024 byte | |

Bila server punya IPv6, tambahkan `listen [::]:80;` dan `listen [::]:443 ssl http2;`. Pada mesin tanpa IPv6, baris itu membuat `nginx -t` gagal.

#### Jalur W — Laragon

Laragon membuat vhost `auto.<folder>.test.conf` untuk setiap folder di `C:\laragon\www`. Vhost itu **tidak** layak untuk production: `autoindex on`, `TLSv1 TLSv1.1` dengan cipher `RC4`/`EXP`, tanpa header keamanan, setiap `.php` dieksekusi, dan ditulis ulang setiap Laragon start.

Langkahnya:

1. Preferences → *General*: matikan **Auto virtual hosts**. Hapus `C:\laragon\etc\nginx\sites-enabled\auto.gelita.test.conf` bila sudah terbentuk.
2. Preferences → *Services & Ports*: aktifkan **Nginx** dan **MySQL**, matikan **Apache** (keduanya berebut port 80).
3. Salin `deploy\windows\nginx\gelita-http.conf` ke `C:\laragon\etc\nginx\sites-enabled\`. Nama berkas **tidak boleh** berawalan `auto.`.
4. Menu → *Nginx* → *Reload*. Setelah sertifikat terbit, salin juga `gelita-https.conf`, lalu reload lagi.

Kedua berkas Windows sama dengan Jalur L kecuali baris khusus platform (`tests/unit/DeployFilesTest.php` menguncinya). Path Windows ditulis dengan garis miring biasa:

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
        …
    }
```

`php_upstream` menunjuk proses php-cgi yang dijalankan Laragon (port FastCGI bawaan Laragon 8.6+: 10987). Direktif `http2 on;` dan path Windows belum diuji di mesin Windows sungguhan; jalankan [Verifikasi Setelah Deploy](#verifikasi-setelah-deploy) sesudah pemasangan.

### HTTPS

**Jalur L** — Certbot dengan validasi webroot. Webroot dilayani blok port 80, jadi perpanjangan otomatis tidak perlu menghentikan Nginx:

```bash
sudo cp /var/www/gelita/deploy/linux/nginx/gelita-http.conf  /etc/nginx/sites-available/gelita-http
sudo cp /var/www/gelita/deploy/linux/nginx/gelita-https.conf /etc/nginx/sites-available/gelita-https
sudo nano /etc/nginx/sites-available/gelita-http /etc/nginx/sites-available/gelita-https   # domain

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

### Akses keluar server (thumbnail video Pustaka)

Pustaka Kedu bawaan memuat 48 media: 43 gambar Wikimedia Commons dan 5 video YouTube. Poster kartu video YouTube/Vimeo/Google Drive diunduh **oleh server**, sekali, lalu disimpan di `public/assets/uploads/library.thumb.*` (`App\Libraries\VideoThumbnail`). Pengunduhan pertama terjadi saat instalasi, ketika `db:seed DatabaseSeeder` mengimpor workbook Pustaka. Karena itu **server** butuh HTTPS keluar (port 443) ke:

| Host | Untuk |
|---|---|
| `i.ytimg.com` | thumbnail YouTube (`maxresdefault.jpg`, cadangan `hqdefault.jpg`) |
| `vimeo.com` | oEmbed Vimeo (`/api/oembed.json`) |
| `i.vimeocdn.com` | berkas thumbnail Vimeo dari oEmbed |
| `drive.google.com` | thumbnail Google Drive (`/thumbnail?id=…`) |
| `*.googleusercontent.com` | tujuan pengalihan thumbnail Drive |

Proxy/firewall yang menolak host itu tidak merusak apa pun: impor tetap berhasil dengan peringatan "Gambar sampul video belum berhasil diambil", dan kartu video tampil dengan latar gradien. Setelah akses dibuka, isi poster yang masih kosong:

```bash
sudo -u www-data php spark gelita:library:thumbnails          # Jalur L
php spark gelita:library:thumbnails                           # Jalur W (PowerShell)
php spark gelita:library:thumbnails --force                   # unduh ulang semua, mis. setelah video diganti
```

Ringkasannya "n terisi, n dilewati, n gagal"; kode keluar 1 bila ada yang gagal. Server tanpa internet sama sekali dapat mengisi poster secara manual di `/admin/konten/pustaka/{level_id}` (kolom poster tiap video).

**Perangkat siswa** tidak pernah meminta thumbnail ke domain luar. Mereka memuat gambar Pustaka dari tautan langsung dari domain asalnya (`commons.wikimedia.org`, yang mengalihkan ke berkas aslinya), dan baru menghubungi penyedia video (YouTube lewat `youtube-nocookie.com`) setelah menekan Putar. Jaringan kelas yang memblokir domain itu membuat gambar/video Pustaka tidak tampil; unggah salinan gambarnya lewat panel bila perlu.

### Mode pemeliharaan

CodeIgniter 4.7 tidak punya `php spark down`/`up` (keduanya "Command not found", kode keluar 1). Mode pemeliharaan GELITA bekerja di tingkat Nginx, sama di kedua jalur, tanpa kode aplikasi:

| | Jalur L | Jalur W |
|---|---|---|
| aktifkan | `touch /var/www/gelita/writable/pemeliharaan.flag` | `New-Item -ItemType File -Force C:\laragon\www\gelita\writable\pemeliharaan.flag` |
| matikan | `rm /var/www/gelita/writable/pemeliharaan.flag` | `Remove-Item C:\laragon\www\gelita\writable\pemeliharaan.flag` |

Selama berkas ada, semua halaman dan API menjawab **503** dengan pesan dwibahasa, sedangkan `php spark` tetap berjalan. Berkas penanda dipasang oleh `deploy.sh`/`deploy.ps1` dan oleh `gelita:reset --pemeliharaan`, dan diabaikan git (`.gitignore`). `api.js` memperlakukan 503 non-JSON sebagai galat jaringan, sehingga `events.js` menyimpan event ke antrean lokal dan mengirimnya lagi setelah situs kembali. Event dari tab yang ditutup selama pemeliharaan tetap dapat hilang (`sendBeacon` tidak tahu hasilnya), sehingga aturan "jangan memperbarui saat kelas berjalan" tetap berlaku.

---

## Instalasi baru

### Berkas operasional di repositori

| Berkas | Isi |
|---|---|
| `deploy/linux/deploy.sh` | pembaruan kode: cek kelas aktif → backup → pemeliharaan → pull → composer → migrate (DSN) → daftar sekolah → cache → salinan ringkas JS/CSS → `assetVersion` → reload FPM → verifikasi → buka |
| `deploy/linux/backup.sh` | backup database (tanpa isi `ci_sessions`), unggahan, `.env`; simpan 30 hari; salinan kedua |
| `deploy/linux/nginx/gelita-http.conf`, `gelita-https.conf` | konfigurasi Nginx Jalur L |
| `deploy/windows/deploy.ps1`, `backup.ps1`, `retensi.ps1` | padanan PowerShell (kompatibel Windows PowerShell 5.1; **hanya ASCII**) |
| `deploy/windows/config.example.psd1` | contoh `config.psd1` |
| `deploy/windows/nginx/gelita-http.conf`, `gelita-https.conf` | konfigurasi Nginx Jalur W |
| `.gitignore` | `/public/assets/uploads/*`, `!/public/assets/uploads/.gitkeep`, `/writable/pemeliharaan.flag` |
| `tests/unit/DeployFilesTest.php` | mengunci cacat yang ditemukan saat skrip dijalankan (lihat [Catatan Implementasi Tahap 8](#catatan-implementasi-tahap-8)) |

Instalasi juga membaca isi bawaan dari repositori, jadi folder `docs/` dan `app/Database/Seeds/data/` **tidak boleh** dibuang dari server:

| Berkas | Dibaca oleh | Isi |
|---|---|---|
| `app/Database/Seeds/data/sekolah-jateng.csv` | `SchoolSeeder`, `gelita:schools:import` | 28.487 sekolah resmi Jawa Tengah (NPSN), dari [`docs/sekolah/`](sekolah/README.md) |
| `app/Database/Seeds/data/story.php` | `ContentSeeder` | 88 baris cerita (salinan [`docs/naskah-cerita.md`](naskah-cerita.md)) |
| `docs/pustaka/gelita-pustaka.xlsx` | `KontenKelas5Seeder` | Pustaka Kedu: 30 halaman, 48 media ([`docs/pustaka/`](pustaka/README.md)) |
| `docs/bank-soal/gelita-bank-soal-kelas5.xlsx` | `KontenKelas5Seeder`, `gelita:reset` | bank soal kelas 5: 12 tantangan, 147 soal aktif ([`docs/bank-soal/`](bank-soal/README.md)) |
| `public/assets/audio/narasi/{id,en}/` (kosong di repositori) | `gelita:narration:import` | folder impor rekaman pengisi suara, 100 per bahasa ([`docs/audio.md`](audio.md)) |

### Ringkasan alur

Instalasi baru adalah satu-satunya cara memasang GELITA. Urutannya sama di kedua jalur:

| # | Langkah | Perintah | Akun database | Akun sistem (Jalur L) |
|---:|---|---|---|---|
| 1 | Kode dan `.env` | `composer install --no-dev --optimize-autoloader`, `cp env .env`, `php spark key:generate` | — | `deploy` |
| 2 | Skema | `php spark migrate` | `gelita_migrate` (DSN) | `www-data` |
| 3 | Isi awal | `GELITA_ADMIN_PASSWORD='…' php spark db:seed DatabaseSeeder` | `gelita_app` | `www-data` |
| 4 | Narasi | `php spark gelita:narration:import`, lalu setujui di panel | `gelita_app` | `www-data` |
| 5 | Verifikasi konten | `php spark gelita:content:verify` | `gelita_app` | `www-data` |
| 6 | Gambar asli | ganti 66 gambar contoh lewat panel, sampai daftar siap penelitian hijau | — | — |
| 7 | Uji asap | [Verifikasi Setelah Deploy](#verifikasi-setelah-deploy) | — | — |
| 8 | Sebelum pengambilan data | `php spark gelita:reset`, lalu `php spark gelita:content:verify --siap-penelitian` | `gelita_migrate` (DSN) | `www-data` |

`php spark migrate` membuat **29 tabel** dari 29 migration (`app/Database/Migrations/2026-01-01-000100_CreateSchools.php` … `2026-01-01-002900_CreateCiSessions.php`, satu tabel per berkas), ditambah tabel `migrations` milik CodeIgniter: 30 tabel. Tabel sesi `ci_sessions` termasuk di dalamnya; **jangan** menjalankan `php spark session:migration` (tidak ada di CodeIgniter 4.7.4).

`db:seed DatabaseSeeder` mengisi, berurutan:

| Seeder | Hasil |
|---|---|
| `SchoolSeeder` | 28.487 sekolah resmi Jawa Tengah (sama dengan `gelita:schools:import`) |
| `StaffUserSeeder` | akun `admin` dengan sandi dari `GELITA_ADMIN_PASSWORD`; berhenti dengan galat bila variabel itu kosong |
| `LearningIndicatorSeeder` | indikator pembelajaran |
| `GameReleaseSeeder` | versi permainan aktif `GELITA-KELAS5-2026` |
| `ResearchStudySeeder` | studi aktif `STUDI-KEDU-KELAS5-2026` (`retention_days` 1825) |
| `LevelSeeder` | tiga wilayah: Temanggung (mudah), Magelang (sedang), Wonosobo (sulit) |
| `MediaAssetSeeder` | slot media bawaan (sama dengan `gelita:media:scan`) |
| `ContentSeeder` | 12 tantangan (4 per wilayah: Susun Gambar, Isi Rumpang, Benar atau Salah, Cari Objek) dan 88 baris cerita |
| `KontenKelas5Seeder` | impor Pustaka Kedu (30 halaman, 48 media), impor bank soal kelas 5 (126 soal: 108 dinilai + 18 pengecoh, 30 variasi, 9 bacaan Benar atau Salah, 36 petunjuk), lalu 66 gambar contoh (mockup) |

Semua seeder aman dijalankan ulang: baris yang sudah ada tidak ditimpa, dan sandi `admin` yang sudah ada tidak diubah ("Akun admin sudah ada — kata sandi tidak diubah"). Kedua impor dicatat di audit log atas nama akun admin pertama. Gambar contoh ditulis ke `public/assets/uploads/mockup/` dan poster video Pustaka ke `public/assets/uploads/` (lihat [Akses keluar server](#akses-keluar-server-thumbnail-video-pustaka)); karena itu seeder dijalankan sebagai `www-data` di Jalur L.

### Instalasi — Jalur L

Sebelum perintah di bawah: pasang paket ([Ekstensi PHP wajib](#ekstensi-php-wajib)), atur MySQL/MariaDB dan buat database serta tiga akun ([Pengaturan MySQL](#pengaturan-mysql), [Akun database](#akun-database)).

```bash
# 0. Sistem — cron dan nama log harian mengikuti zona waktu ini (bawaan VPS: UTC)
sudo timedatectl set-timezone Asia/Jakarta
sudo adduser --disabled-password --gecos '' deploy
sudo usermod -aG www-data deploy

# 1. Kode dan .env (sebagai deploy)
sudo mkdir -p /var/www/gelita && sudo chown deploy:deploy /var/www/gelita
sudo -iu deploy
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
cd /var/www/gelita

# 4. Skema — akun migrasi lewat DSN
export database_default_DSN="$(cat ~/.gelita/migrate.dsn)"
sudo --preserve-env=database_default_DSN -u www-data php spark migrate
unset database_default_DSN

# 5. Isi awal — akun aplikasi, sebagai www-data
read -rsp 'Sandi admin pertama: ' GELITA_ADMIN_PASSWORD && echo && export GELITA_ADMIN_PASSWORD
sudo --preserve-env=GELITA_ADMIN_PASSWORD -u www-data php spark db:seed DatabaseSeeder
unset GELITA_ADMIN_PASSWORD

# 6. Rekaman narasi pengisi suara di public/assets/audio/narasi/{id,en}/ → audio draft (folder kosong: dilewati; rekaman juga dapat diunggah di panel)
sudo -u www-data php spark gelita:narration:import --dry-run
sudo -u www-data php spark gelita:narration:import

# 7. Verifikasi konten — harus 0 galat (peringatan gambar contoh boleh)
sudo -u www-data php spark gelita:content:verify
exit

# 8. PHP-FPM, Nginx + HTTPS: lihat "Pengaturan PHP", "Nginx", "HTTPS"
# 9. Cron: lihat "Tugas Terjadwal"; lalu uji pulih: lihat "Backup"
# 10. Panel: lihat "Langkah terakhir"
```

- `read -rs` membuat sandi admin tidak tercatat di riwayat shell. Bentuk singkat `GELITA_ADMIN_PASSWORD='…' php spark db:seed DatabaseSeeder` hanya untuk mesin pengembangan.
- Keluaran langkah 5 menyebut jumlah sekolah resmi, "Akun admin dibuat (username: admin)", hasil impor Pustaka Kedu dan bank soal kelas 5, lalu "Gambar contoh: 66 dibuat, …". Peringatan berwarna kuning (berkas workbook tidak ditemukan, impor gagal, gambar contoh gagal) harus diselesaikan sebelum lanjut; seeder aman diulang.
- Langkah 6 memetakan 100 rekaman per bahasa (88 baris cerita + 12 petunjuk Cari Objek) ke baris yang dibuat langkah 5, jadi urutannya wajib. Rekaman masuk sebagai **draft** dan belum terdengar pemain sampai disetujui di panel (Langkah terakhir).

### Instalasi — Jalur W

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
- Atur PHP dan MySQL, lalu buat database dan tiga akun `@'127.0.0.1'` seperti di [Production Environment](#production-environment).

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

**4–7. Skema, isi awal, narasi, verifikasi:**

```powershell
$env:database_default_DSN = (Get-Content C:\gelita-ops\migrate.dsn -Raw).Trim()
php spark migrate
Remove-Item Env:\database_default_DSN

$s = Read-Host 'Sandi admin pertama' -AsSecureString
$env:GELITA_ADMIN_PASSWORD = [Runtime.InteropServices.Marshal]::PtrToStringBSTR(
    [Runtime.InteropServices.Marshal]::SecureStringToBSTR($s))
php spark db:seed DatabaseSeeder
Remove-Item Env:\GELITA_ADMIN_PASSWORD

php spark gelita:narration:import --dry-run
php spark gelita:narration:import
php spark gelita:content:verify
```

Sintaks `GELITA_ADMIN_PASSWORD='…' php spark …` adalah sintaks Bash; di PowerShell maupun cmd sintaks itu galat. Jalankan perintah di atas di **PowerShell**, bukan di terminal Cmder bawaan Laragon.

**8. Nginx dan HTTPS:** lihat [Nginx → Jalur W](#jalur-w--laragon) dan [HTTPS](#https).

**9. Tugas terjadwal dan uji pulih:** lihat [Tugas Terjadwal](#tugas-terjadwal) dan [Backup](#backup).

### Langkah terakhir (kedua jalur)

Setelah situs terbuka lewat HTTPS:

1. **Ganti sandi admin.** Sandi awal pernah diketik di terminal. Masuk di `/admin/login` sebagai `admin`, buka **Ganti kata sandi** di menu akun kanan atas (`/admin/akun/sandi`), dan ganti dengan sandi baru minimal 12 karakter yang disimpan di pengelola sandi.
2. **Rekam dan setujui narasi.** Buka **Rekaman narasi** (`/admin/konten/narasi`), unduh naskah PDF untuk pengisi suara, unggah rekamannya, dengarkan, lalu tekan **Setujui semua rekaman …** untuk bahasa Indonesia dan Inggris. Server yang masih menyimpan rekaman TTS lama: tekan dulu **Batalkan semua rekaman …** per bahasa ([`docs/audio.md`](audio.md) → *Voice-over narration*). Rekaman draft tidak pernah terdengar pemain. Rekaman satu per satu juga dapat disetujui di **Gambar & mockup → Rekaman suara** (`/admin/media/audio`).
3. **Buat akun guru.** Satu akun `guru` per guru di **Staf** (`/admin/staf`), dengan **NPSN sekolahnya** (cari di **Sekolah**, `/admin/sekolah`). Guru dan siswanya lalu menunjuk baris sekolah resmi yang sama, sehingga cakupan data guru pasti lengkap. Sandi awal yang Anda pilih wajib diganti guru saat pertama masuk.
4. Jalankan [Verifikasi Setelah Deploy](#verifikasi-setelah-deploy).

Sesudah itu aplikasi siap untuk **uji coba**. Ke-66 gambar tantangan masih gambar contoh bertanda "GAMBAR CONTOH"; permainan tetap berjalan dengan gambar itu.

### Installing the Katalog Aset images

*(New section, English.)* The team catalogue `gelita-katalog/` (in the repo, not under `public/`) holds the current art: logo, MULAI/START buttons, app icons, backgrounds, story slide backgrounds, maps, badges, pins, lanterns, avatars, character frames, the 66 challenge images, and Pustaka illustrations. One command installs all of it into the game's image slots:

```bash
sudo -u www-data php spark gelita:katalog:impor --dry-run   # Jalur L (Jalur W: without sudo -u www-data); lists what would be installed
sudo -u www-data php spark gelita:katalog:impor             # install
```

- Run it after the installation above (the story slides, Pustaka pages, and challenge slots it links to must exist). It is safe to run again: images already installed from the catalogue and images an admin uploaded are skipped. `--timpa` replaces them too; `--sumber=FOLDER` reads another copy of the catalogue.
- Every image is re-encoded as lossy WebP (about 250 KB per background instead of the catalogue's 2 MB lossless files) and stored in `public/assets/uploads/` under its official name, so it is in the backup and can be replaced in **Gambar & mockup** at any time. App icons are stored as PNG. Installed rows carry the credit "Katalog Aset GELITA (gambar sementara)", which disappears once an admin uploads a new file.
- Story slide backgrounds are linked to their slide, Pustaka illustrations replace the Commons links (re-importing `docs/pustaka/gelita-pustaka.xlsx` restores the links), video covers already downloaded by `gelita:library:thumbnails` are kept, and each region's four challenge buttons are moved onto the clearings of the catalogue's region map.
- The catalogue has 24 frames per pose for all ten poses (240, red-scarf Jaka and Mbah Kedu, added 12 October 2026). The Cari Objek objects sit on the tables and shelves of the catalogue scenes (question bank layout of 12 October 2026). On a server whose bank still has the old positions, the command moves every object that is still at its old position, as long as the installed scene is the catalogue one; an object an admin placed elsewhere stays (`--timpa` moves it too). Re-importing the bank (`gelita:bank:import`) gives the same positions, but resets every admin placement to the bank.
- **After a new catalogue edition** (pull the repo first), run the command again. An image installed from an earlier edition is reinstalled when its catalogue file changed and skipped when it is the same; admin uploads stay. The 12 October 2026 edition replaces the 30 old frames and adds 210 (about 15 MB in the game).
- The two round language icons of the HUD switch come from the catalogue references `reference.20` (ID) and `reference.19` (EN) and are stored as `ui.lang.id`/`ui.lang.en`. Until both exist the switch shows the text ID / EN.
- **Old art kept as an admin upload** (e.g. the lurik Jaka frames or an older start button uploaded in the panel before the catalogue existed) is never replaced by a plain run. The report says "sudah ada gambar unggahan admin" for such a slot, and a pose whose frames now come from both sources is listed as **perlu dicek**, because its loop would switch between two drawings. Replace only those slots with `--kunci` (comma-separated, `*` allowed), dry run first:

  ```bash
  sudo -u www-data php spark gelita:katalog:impor --timpa --kunci="ui.btn-start*,ui.lang.*,char.jaka.*" --dry-run
  sudo -u www-data php spark gelita:katalog:impor --timpa --kunci="ui.btn-start*,ui.lang.*,char.jaka.*"
  ```

- **Susun Gambar edition of 14 October 2026.** The catalogue has all 30 puzzle images (variants a–j, 1200 × 1200, full-bleed daylight scenes): 21 new d–j images and four redrawn ones (`challenge.item.tmg-1-01`, `tmg-1-03`, `mgl-1-01`, `wnb-1-07`; the old night or white-background versions failed or nearly failed the tile check). A plain run fills the d–j slots and replaces the four when the old version came from the catalogue. If an admin uploaded one of the four by hand, the report says "sudah ada gambar unggahan admin"; replace just those four:

  ```bash
  sudo -u www-data php spark gelita:katalog:impor --timpa --kunci="challenge.item.tmg-1-01,challenge.item.tmg-1-03,challenge.item.mgl-1-01,challenge.item.wnb-1-07" --dry-run
  sudo -u www-data php spark gelita:katalog:impor --timpa --kunci="challenge.item.tmg-1-01,challenge.item.tmg-1-03,challenge.item.mgl-1-01,challenge.item.wnb-1-07"
  ```

  The slot notes (`media_note`) of the four redrawn slots are not changed by either command; edit them in **Gambar & mockup** if the bank's note differs.
- When a release uses an asset package, build a new package afterwards (`gelita:paket:bangun`) so devices get the new pictures.

### Sebelum pengambilan data penelitian

Urutan wajib sebelum siswa penelitian pertama mendaftar: narasi disetujui ([Langkah terakhir](#langkah-terakhir-kedua-jalur) butir 2) → gambar asli → daftar siap penelitian hijau → uji asap → `gelita:reset`.

**1. Ganti gambar contoh dengan gambar asli.** Ada 66 slot gambar tantangan: 30 gambar Susun Gambar (variasi a–j), 3 gambar pernyataan Benar atau Salah, 3 adegan Cari Objek, dan 30 benda Cari Objek. Unggah gambar asli lewat **Gambar & mockup** (`/admin/media`; saring status "Gambar contoh", lalu **Unggah gambar asli** per slot) atau editor tantangan (`/admin/konten/node/{id}`, bagian "Gambar & suara tantangan"); daftar yang belum beres ada di **Kelengkapan aset** (`/admin/media/kelengkapan`). Ukuran wajib tiap slot ada di `Config\Gelita::$assetSizes` dan ditegakkan saat unggah. Gambar yang diunggah otomatis tercatat sebagai gambar asli (`media_assets.is_placeholder = 0`) dan tidak pernah ditimpa `gelita:mockups` maupun `gelita:reset`. Status per slot:

```bash
sudo -u www-data php spark gelita:mockups --dry-run            # Jalur L (Jalur W: tanpa sudo -u www-data)
sudo -u www-data php spark gelita:mockups --dry-run --node=tmg-4
```

**2. Daftar siap penelitian hijau.** Buka **Penelitian** (`/admin/studi`). Kotak **Daftar siap penelitian** (`App\Libraries\ResearchReadiness`) harus berlabel "siap": tidak ada gambar contoh aktif, semua rekaman narasi disetujui, dan verifikasi konten siap penelitian tanpa galat. Selama belum hijau, **Ringkasan** (`/admin`) menampilkan peringatan yang sama. Studi bawaan `STUDI-KEDU-KELAS5-2026` sudah berstatus Berjalan sejak instalasi (dan sesudah `gelita:reset`), jadi daftar ini **tidak** memblokir pendaftaran siswa; panel hanya menolak *mengubah* status studi menjadi Berjalan selama daftar belum hijau, kecuali "Tetap jalankan" dicentang. Periksa daftarnya sendiri.

**3. Uji asap.** Jalankan seluruh [Verifikasi Setelah Deploy](#verifikasi-setelah-deploy) (pemeriksaan otomatis dan uji manual) pada konten akhir: gambar asli, narasi disetujui. Siswa dan data uji yang dibuat di sini dikosongkan pada langkah berikutnya.

**4. Reset data uji coba.** Setelah uji coba dan uji asap selesai, kosongkan semua data uji coba dengan `gelita:reset`, yang dijalankan dengan akun migrasi:

```bash
# Jalur L — sebagai deploy
cd /var/www/gelita
bash deploy/linux/backup.sh                                     # data yang dikosongkan tidak dapat kembali
export database_default_DSN="$(cat ~/.gelita/migrate.dsn)"
sudo --preserve-env=database_default_DSN -u www-data php spark gelita:reset --dry-run
sudo --preserve-env=database_default_DSN -u www-data php spark gelita:reset --pemeliharaan
unset database_default_DSN
sudo -u www-data php spark gelita:content:verify --siap-penelitian
```

```powershell
# Jalur W — sebagai akun gelita
Set-Location C:\laragon\www\gelita
Start-ScheduledTask -TaskPath '\GELITA\' -TaskName 'Backup'     # tunggu selesai, periksa folder backup
$env:database_default_DSN = (Get-Content C:\gelita-ops\migrate.dsn -Raw).Trim()
php spark gelita:reset --dry-run
php spark gelita:reset --pemeliharaan
Remove-Item Env:\database_default_DSN
php spark gelita:content:verify --siap-penelitian
```

Yang dilakukan `gelita:reset`:

| Kelompok | Tabel | Perlakuan |
|---|---|---|
| Data penelitian & uji coba | `participants`, `game_sessions`, `session_progress`, `challenge_attempts`, `item_responses`, `item_tries`, `audio_usage_events`, `game_event_logs`, `device_logs`, `participant_feedback`, `data_exports`, `data_deletion_requests`, `audit_logs`, `ci_sessions` | dikosongkan (`TRUNCATE`; berkas di `writable/exports/` ikut dihapus) |
| Konten tantangan | `challenge_nodes`, `challenge_items`, `reading_passages`, `hints` | dikosongkan, lalu 12 tantangan dibangun ulang dan bank soal kelas 5 diimpor ulang |
| Pengaturan | `research_studies`, `game_releases`, `learning_indicators` | dikosongkan, lalu diisi nilai bawaan |
| Sebagian | `schools`, `staff_users` | sekolah ketikan yang belum terverifikasi dihapus; akun staf tetap dan dibuka kuncinya |
| Tetap | `levels`, `dialogues`, `library_pages`, `library_media`, `media_assets`, `audio_assets` | tidak disentuh: Pustaka, cerita, wilayah, gambar, dan rekaman (termasuk persetujuannya) tetap |

- `--dry-run` hanya menampilkan rencana dan jumlah baris. Tanpa `--ya`, reset meminta kata konfirmasi `RESET-KELAS5`.
- `--pemeliharaan` memasang `writable/pemeliharaan.flag` selama reset (situs 503) dan melepasnya lagi.
- Reset menolak berjalan bila ada sesi bermain aktif dalam 10 menit terakhir (`--paksa` untuk mengabaikannya), bila migration belum lengkap, atau bila akun database tidak punya hak `TRUNCATE` ("Jalankan reset dengan akun migrasi"). Penolakan terjadi sebelum ada tabel yang dikosongkan.
- Gambar contoh yang hilang dibuat ulang (kecuali `--tanpa-mockup`); gambar asli dipertahankan.
- Rekaman petunjuk Cari Objek yang sudah terdaftar ditautkan lagi ke butir soal yang baru diimpor, dengan status persetujuan tetap. `gelita:narration:import` tidak perlu diulang sesudah reset.
- Opsi lain: `--tanpa-bank` (12 tantangan tanpa soal), `--bank=JALUR` (workbook lain), `--hapus-guru` (hapus semua akun guru; akun admin selalu tetap).
- Ringkasan akhir harus berbunyi "Bank soal kelas 5: 126 soal (108 dinilai + 18 pengecoh), 30 variasi, 9 bacaan, 24 tautan rekaman petunjuk" (0 tautan bila narasi belum diimpor), "Verifikasi konten: 0 galat", dan "Kode siswa berikutnya: GLT-000001".

`gelita:content:verify --siap-penelitian` harus berakhir "Konten lengkap: 3 wilayah × 4 tantangan, tanpa temuan; siap untuk penelitian." Selama masih ada gambar contoh yang dipakai tantangan, perintah ini keluar dengan kode 1 dan "Belum siap untuk pengambilan data penelitian." Reset aman diulang, tetapi setiap kali diulang data siswa ikut hilang: jalankan hanya sebelum pengambilan data dimulai.

---

## Pemeliharaan

### Pembaruan kode

Kode baru dibawa ke server hanya lewat `deploy/linux/deploy.sh` (Jalur L) atau `deploy\windows\deploy.ps1` (Jalur W). Jangan pernah memperbarui saat ada kelas berjalan. Kedua skrip memeriksa sesi `active` dalam 10 menit terakhir dan **membatalkan diri** bila ada; `--paksa` / `-Paksa` hanya untuk keadaan darurat.

```bash
bash /var/www/gelita/deploy/linux/deploy.sh                    # Jalur L, sebagai deploy
```

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File C:\laragon\www\gelita\deploy\windows\deploy.ps1
```

Langkah skrip:

| # | Langkah | Jalur L (`deploy.sh`) | Jalur W (`deploy.ps1`) |
|---:|---|---|---|
| 1 | Periksa kelas aktif | `mysql --defaults-extra-file=backup.cnf`: `game_sessions` `status = 'active'` dengan `last_active_at` ≤ 10 menit → BATAL, kode 1 | sama, lewat `mysql.exe` dari `config.psd1` |
| 2 | Backup | `backup.sh`; kode 2 (hanya salinan kedua gagal) tidak menghentikan deploy | `backup.ps1`; kode 2 → peringatan |
| 3 | Mode pemeliharaan | `touch writable/pemeliharaan.flag`; `trap ERR` mencetak pesan pemulihan | `New-Item`; blok `try/catch` |
| 4 | Ambil kode | `git pull --ff-only origin $BRANCH` | `git pull --ff-only origin $c.Branch` |
| 5 | Dependency | `composer install --no-dev --optimize-autoloader --no-interaction` | sama |
| 6 | Migration | `php spark migrate` sebagai `www-data` dengan `database_default_DSN` dari `migrate.dsn` | `php spark migrate` dengan `$env:database_default_DSN`, dihapus di `finally` |
| 7 | Daftar sekolah resmi | `php spark gelita:schools:import` (hanya baris yang berubah) | sama |
| 8 | Cache | `php spark cache:clear` | sama |
| 9 | Salinan ringkas JS/CSS | `php spark gelita:aset:minify` (see [Minified JS/CSS](#minified-jscss)); a failure only prints a warning, the site keeps the original files | same, inside `try/catch` with `Write-Warning` |
| 10 | Versi aset | `sed -i` menaikkan `gelita.assetVersion` di `.env`, lalu `chgrp www-data .env && chmod 640 .env` | `[IO.File]::WriteAllText` (UTF-8 tanpa BOM) |
| 11 | Reload PHP | `sudo systemctl reload php8.3-fpm` (`FPM` dapat diganti di `ops.conf`) | — (opcache memeriksa stempel waktu) |
| 12 | Verifikasi konten | `php spark gelita:content:verify` | sama |
| 13 | Buka kembali | hapus `pemeliharaan.flag` | sama |

Semua `php spark` di `deploy.sh` berjalan sebagai `www-data` (fungsi `spark`). Bila langkah apa pun gagal, termasuk verifikasi konten, situs **tetap** dalam mode pemeliharaan dan skrip mencetak "DEPLOY GAGAL — situs tetap dalam mode pemeliharaan": lebih baik 503 yang jelas daripada kelas yang membuka konten rusak. Perbaiki penyebabnya, lalu hapus berkas penanda. Verifikasi di sini tanpa `--siap-penelitian`, jadi gambar contoh tidak menghalangi pembaruan.

Catatan skrip:

- `set -Eeuo pipefail` di `deploy.sh`: `-E` diperlukan agar `trap ERR` juga terpicu oleh galat di dalam fungsi `spark`.
- `.env` di Jalur W ditulis `[IO.File]::WriteAllText` (UTF-8 tanpa BOM, CRLF dipertahankan), bukan `Set-Content`, yang di PowerShell 5.1 menulis ANSI atau ber-BOM.
- **Skrip `.ps1` dan `.psd1` hanya berisi ASCII** (`->` dan `-`, bukan `→` dan `—`). Windows PowerShell 5.1 membaca skrip tanpa BOM sebagai ANSI (cp1252); byte UTF-8 tanda panah dan tanda pisah menjadi tanda kutip tipografis yang memutus string, dan skrip gagal di-parse. `DeployFilesTest` menolak karakter non-ASCII.
- Daftar sekolah resmi diperbarui di mesin pengembang (`php docs/sekolah/build-sekolah-jateng.php`, commit CSV yang berubah), lalu masuk server lewat langkah 7. Impor hanya menulis sekolah yang berubah dan tidak pernah menghapus sekolah yang sudah dipakai siswa.

Pembaruan **tidak** menjalankan `gelita:reset`, `db:seed`, atau impor ulang bank soal; isi konten di server dikelola lewat panel. Migration yang menghapus kolom atau tabel **tidak pernah digabung** dalam pembaruan yang sama dengan perubahan lain, dan selalu didahului satu rilis yang berhenti memakai kolom itu.

### Story script changed (`gelita:story:sync`)

`ContentSeeder` only inserts missing story lines, and `gelita:reset` keeps `dialogues`, so a revised script (`app/Database/Seeds/data/story.php`, e.g. the 32 lines of draft 2, October 2026) does not reach a server that already has content. After the code update, run as `www-data`:

```bash
sudo -u www-data php spark gelita:story:sync --dry-run   # what would change
sudo -u www-data php spark gelita:story:sync
```

- A line is replaced only when it still holds the current script or an earlier version listed in `app/Database/Seeds/data/story-previous.php`. A line whose text was edited in the panel is listed as "Disunting di panel" and kept; `--timpa` replaces it too.
- Missing lines are added. Lines of a region that is not installed are skipped.
- Only titles and text change. Character, pose, effect, background, active flag, and linked recordings stay as they are.
- The command lists the replaced lines that still have a recording: that audio speaks the old text. Approve narration line by line, skipping those codes, until they are re-recorded; do not use **Setujui semua rekaman**.
- A run that writes something is recorded in the audit log as `story_sync`. Running it again changes nothing.

### Rollback

Urutannya penting: **rollback migration dulu, baru kembalikan kode.** `migrate:rollback` menjalankan `down()` dari berkas migration rilis baru. Setelah `git reset`, berkas itu sudah tidak ada; CodeIgniter lalu berhenti dengan `There is a gap in the migration sequence near version number …` tanpa mengubah apa pun, tetapi kode keluarnya tetap **0**.

Nomor batch terlihat di kolom `Batch` pada `php spark migrate:status`. `<batch-sebelumnya>` adalah batch terakhir **sebelum** pembaruan yang dibatalkan. Di `CI_ENVIRONMENT = production`, `migrate:rollback` meminta konfirmasi `[y, n]`; jawab `y`.

```bash
# Jalur L — sebagai deploy
cd /var/www/gelita
touch writable/pemeliharaan.flag

# 1. Bila pembaruan menyertakan migration — selagi kode rilis baru masih terpasang:
sudo -u www-data php spark migrate:status                # catat batch sebelum pembaruan
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

# 1. Bila pembaruan menyertakan migration — selagi kode rilis baru masih terpasang:
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

---

## Assets

### Lokasi

| Jenis | Path | Asal di server | Cache browser |
|---|---|---|---|
| CSS (8 berkas) | `public/assets/css/` | git | `?v=` → immutable |
| JavaScript entry | `public/assets/js/game.js`, `admin.js` | git | `?v=` → immutable |
| Modul JavaScript | `public/assets/js/{core,game,engines,admin}/` | git | tanpa `?v` → revalidasi |
| Vendor JS | `public/assets/vendor/` (Howler.js) | git | `?v=` → immutable |
| Font | `public/assets/fonts/` (Marcellus, Plus Jakarta Sans, IBM Plex Mono) | git | revalidasi |
| Ikon aplikasi | bawaan `public/assets/app/`, `public/favicon.ico`, `public/sw.js` (git); ikon pengganti dari panel (slot `app.*`) di `public/assets/uploads/` | git / panel | revalidasi; `/manifest.json` dibuat PHP (`AppController`), `no-cache` |
| Data referensi | `public/assets/data/wilayah-id.json` | git | revalidasi |
| Aset resmi | `public/assets/{ui,char,bg,map,challenge,library,reward,audio}/` | **git**; gambar: baru `ui/placeholder.svg` (view memakai pengganti); audio: musik dan efek suara; narasi pengisi suara tidak ikut git ([`docs/audio.md`](audio.md)) | revalidasi |
| Rekaman narasi (folder) | `public/assets/audio/narasi/{id,en}/{kode}.mp3` | salin ke server, lalu `gelita:narration:import` | revalidasi |
| Musik & efek suara | `public/assets/audio/{music,sfx}/{nama}.mp3` | git (dibaca langsung `core/audio.js`, bukan `media_assets`) | revalidasi |
| Unggahan dan berkas bangkitan | `public/assets/uploads/` (`{asset_key}.{ext}`, `mockup/`, `library.thumb.*`) | panel `/admin/media`, editor konten, unggah narasi, gambar contoh, poster video — **hanya di server** | revalidasi |
| Asset package: media | `public/assets/p/{sha256[0:16]}.{ext}` | `gelita:paket:bangun` — **only on the server**, back it up with `uploads/` | immutable without `?v=` |
| Asset package: code | `public/assets/k/{code_version}/{css,js,vendor,fonts}/…`, `offline.html` | `gelita:paket:bangun` — only on the server | immutable without `?v=` |
| Asset package: manifests | `public/paket/pkt-*.json`; URL maps and build state in `writable/paket/` | `gelita:paket:bangun` — only on the server | manifest immutable; `writable/` not public |
| Berkas unduhan data | `writable/exports/` | aplikasi | tidak publik; dilayani controller berotorisasi |
| Log / cache | `writable/logs/`, `writable/cache/` | aplikasi | tidak publik |

Aset hanya punya tiga jalan masuk ke server: **commit ke repositori** (folder aset resmi, didaftarkan `gelita:media:scan`), **unggah lewat panel** (folder `uploads/`, ikut backup), atau **`gelita:katalog:impor`** dari Katalog Aset `gelita-katalog/` (juga ke `uploads/`, lihat [Installing the Katalog Aset images](#installing-the-katalog-aset-images)). Jangan menyalin berkas langsung ke folder aset resmi di server: berkas itu tidak ada di git, tidak ikut backup, dan hilang saat server dibangun ulang.

### Cache-busting

`asset_url_versioned()` menambahkan `?v={gelita.assetVersion}` pada CSS, JavaScript entry, dan vendor. Setiap pembaruan menaikkan nilai itu, jadi browser mengambil versi baru tanpa hard-refresh. Ini penting karena komputer sekolah jarang dibersihkan cache-nya.

**Automatic file version (18 October 2026).** While package mode is off, `?v=` is `{gelita.assetVersion}.{fingerprint}`, where the fingerprint comes from the file's modification time and size (`asset_file_version()`). A changed CSS/JS file therefore gets a new URL even when the server was updated without `deploy.sh`/`deploy.ps1` (git pull, copied files) and `assetVersion` stayed the same, so there is no need to build a package and turn it off again just to push new code to browsers. Modules imported from the entry keep relying on revalidation (`no-cache` in the nginx config above). With package mode on, code still comes from the package snapshot and needs **Bangun ulang paket**.

#### Minified JS/CSS

*(New, English; owner, 19 October 2026.)* Browsers get minified copies of the code, while the files in git stay readable and unminified.

- **What.** `php spark gelita:aset:minify` (`App\Libraries\AssetMinifier`, library `wikimedia/minify`) writes every `public/assets/js/**/*.js` and `public/assets/css/*.css` in minified form, plus a plain copy of `public/assets/fonts/*.woff2`, to `public/assets/min/{version}/` with the same names and folders. Relative module imports and the CSS `url('../fonts/…')` therefore keep working. `{version}` is a hash of the minified bytes, so every build also gives the imported modules new URLs. `public/assets/min/sources.json` names the current build. The previous build is kept for pages that are still open; older ones are removed. JavaScript loses only comments and whitespace (no renaming), CSS likewise. Measured on 19 October 2026: 709 KB of JS/CSS becomes 466 KB, and what nginx sends gzipped per file drops from about 221 KB to 137 KB.
- **When it is used.** `asset_url_versioned()` and `game_asset_url()` point to `assets/min/{version}/…` only while the mirror is **fresh**: every source still has the modification time and size recorded at build time, and no source was added or removed. One changed file without a new run (a `git pull` by hand, a copied file) makes the whole site fall back to the original files until the command runs again. A missed run is slower, never broken. Switch: `Config\Gelita::$minifyAssets` (on; off in tests).
- **Package mode.** **Bangun ulang paket** / `gelita:paket:bangun` refreshes the mirror first and puts the minified bytes in `assets/k/{code_version}/` under the usual keys, so the manifest hashes and the device-side sha256 check describe the minified files. The first package built with minification gets a new `code_version`, so devices download the code group (about 0.5 MB) once more. If minifying fails, the package carries the original files and the build reports the warning `minify_failed`.
- **Not minified:** `public/sw.js`, `sw-kosong.js`, `offline.html`, `public/assets/vendor/` (Howler is already minified), images, and audio.
- **Permissions (Path L).** `public/assets/min/` must belong to `www-data` (see the `chown` lines in the server setup above). On a server installed before this change: `sudo mkdir -p public/assets/min && sudo chown www-data:www-data public/assets/min && sudo chmod 2775 public/assets/min`, then `sudo -u www-data php spark gelita:aset:minify`.
- **Checks.** `php spark gelita:aset:minify --periksa` reports whether the mirror is fresh (exit code 1 when it is not). On a developer machine, `node tools/qa/minify-check.mjs` compares the syntax trees of every source file and its minified copy. `AssetMinifierTest` runs the same comparison whenever Node and acorn are available.

Tidak semua URL ber-`?v=`:

- **Modul JavaScript** di-`import` relatif dari entry (`import … from '../core/dom.js'`). URL-nya tidak mewarisi query string entry.
- **Gambar dan audio** dari `media_assets` dirender `base_url($path)` tanpa `?v`. Nama berkas unggahan tetap `{asset_key}.{ext}` walau isinya diganti.

Karena itu Nginx memberi `immutable` **hanya** pada URL ber-`?v=`, dan `no-cache` (revalidasi dengan `Last-Modified`/`ETag`, dijawab 304 bila tidak berubah) pada aset lain. Modul lama dan gambar lama tidak pernah tertinggal di browser setelah pembaruan.

### Asset package (download-once mode)

*(New section, English; plan: `docs/rencana-paket-aset.md`.)* Package mode is off until a release names a package. Stage 1 adds the server side:

```bash
php spark gelita:paket:bangun                  # build pkt-YYYY-MM-DD-NN from all active media, approved narration, and game code
php spark gelita:paket:bangun --tanpa-pustaka  # same, without copying the library's Wikimedia Commons images
php spark gelita:paket:periksa [kode]          # re-hash every package file against its manifest (exit 1 on any problem)
php spark gelita:paket:bersihkan [--coba]      # delete package files no package refers to; --hapus kode removes a package not used by any release
```

- Building changes nothing for students. Package mode switches on when the active release's **Versi gambar & suara** (`game_releases.asset_version`) is the package code. Admins switch it on **Rilis → Paket aset** (see *Admin switch* below); a release with that value, created and activated by hand, works too. Any other value, such as the default `1`, means package mode is off.
- Emergency kill switch: `gelita.paket.aktif = false` in `.env` forces normal URLs on every device, whatever the release says. `GET /api/paket/aktif` then answers `package_code: null` with `disabled_by_server: true`.
- The build copies the library's Commons images to `public/assets/uploads/library.commons.*` (owner decision P4a). The server needs outbound HTTPS to `commons.wikimedia.org` and `upload.wikimedia.org`; without it the build only warns and those images stay external links.
- `backup.sh`/`backup.ps1` archive `public/assets/p/`, `public/assets/k/`, `public/paket/`, and `writable/paket/` next to the uploads (see [Backup](#backup)). A package referenced by a release must stay on the server even after newer packages exist (devices may still request its files).
- Nginx: the `$gelita_is_asset` map gives `/assets/p/`, `/assets/k/`, and `/paket/pkt-*.json` the value `2`, which `$gelita_cache` turns into `public, max-age=31536000, immutable` without `?v=`. These URLs never change content: media are named by their sha256, code lives in a per-version folder (`{app_version}-{hash of the code files}`), and a package code is never reused (deleted codes leave a marker in `writable/paket/`).

```nginx
map $uri $gelita_is_asset {
    default 0;
    ~^/(?:assets/[pk]/|paket/pkt-[^/]+\.json$) 2;
    ~*\.(?:css|js|json|jpe?g|png|gif|webp|svg|ico|woff2?|ttf|mp3|ogg|wav|m4a|mp4|webm)$ 1;
}
map "$gelita_is_asset:$arg_v" $gelita_cache {
    default "";
    "1:"    "no-cache";
    ~^1:.   "public, max-age=31536000, immutable";
    ~^2:    "public, max-age=31536000, immutable";
}
```

Check after deploying (with a built package):

```bash
curl -sI "$U/assets/p/$(ls /var/www/gelita/public/assets/p | head -1)" | grep -i cache-control   # public, max-age=31536000, immutable
curl -s "$U/api/paket/aktif"                                                                     # {"success":true,"data":{"package_code":…}}
```

#### Service worker and package on devices (Stage 2)

*(New, English.)* With package mode on, devices keep the package in Cache Storage and `public/sw.js` serves it (details: `docs/06_JAVASCRIPT.md` §Asset package — service worker and downloader). Deployment notes:

- **HTTPS is required** for the service worker and for the sha256 check (`crypto.subtle`); `http://localhost` works for development only.
- **`sw.js` and `sw-kosong.js` must never be cached long.** The Nginx config already gives `.js` without `?v=` the value `no-cache`; browsers also re-check the worker script on navigations. Do not put either file behind `immutable` or a CDN.
- **Code updates while package mode is on.** Pages then load CSS and JavaScript from the package copy `assets/k/{code_version}/`, a snapshot taken when the package was built (minified, see [Minified JS/CSS](#minified-jscss)). After a deploy that changes game code, press **Bangun ulang paket** on Rilis → Paket aset and publish it (or `php spark gelita:paket:bangun` plus a release that names the package), or students keep running the old code. Media files are content-addressed, so devices download only the changed code files. The deploy scripts do not do this yet (plan §5.4, §6.3).
- **Package copy missing on disk** (for example a restore without `public/assets/k/`): pages fall back to the normal `assets/…?v=` URLs for code files that are not on disk, so the game still loads; run `php spark gelita:paket:periksa` and rebuild.
- `X-Gelita-Paket: {package_code}` is sent on API responses while package mode is on (no student data).

**Kill switch for service worker trouble** (plan §11.1). Two levels, from mild to drastic:

1. **`.env`: `gelita.paket.aktif = false`** (no restart needed; `.env` is read on every request). Every device gets normal asset URLs on its next page load, `/api/paket/aktif` answers `package_code: null` with `disabled_by_server: true`, and game pages register the empty worker `public/sw-kosong.js` instead of `sw.js`. The empty worker has no fetch handler, so every request goes to the network as without a service worker; it removes the device's package marker (`gelita-meta`) and keeps the downloaded files (`gelita-aset`, public assets only) for later. The offline page is not shown while it runs. Undo: remove the line (or set `true`); the next page load registers `sw.js` again, and devices download the package again before Mulai (only what is missing).
2. **When pages do not load at all** (a worker bug that breaks navigations, so no page can register anything): serve the empty worker at the worker's own URL. Browsers fetch the worker script themselves on navigation, so this reaches every device. Remove the rule once `sw.js` is fixed:

```nginx
# Both paths (Nginx), inside the HTTPS server block — temporary
location = /sw.js {
    default_type application/javascript;
    add_header Cache-Control "no-cache" always;
    try_files /sw-kosong.js =404;
}
```

Reload Nginx after adding and after removing it. Copying `public/sw-kosong.js` over `public/sw.js` works too, but the next deploy (`git pull`) then stops on the changed file; restore it with `git checkout -- public/sw.js`. Devices pick up the empty worker on their next navigation (browsers re-check the worker script at least once a day).

Check after deploying:

```bash
curl -sI "$U/sw.js" | grep -i cache-control                     # no-cache
curl -sI "$U/api/paket/aktif" | grep -i x-gelita-paket           # the package code while package mode is on
```

#### Admin switch: Rilis → Paket aset

*(New subsection, English; asset package Stage 6, plan §6.3.)* Admins switch package mode on the panel page **Rilis → Paket aset** (`/admin/studi/rilis/paket`); the CLI commands above stay available.

- **Nyalakan** builds a package in batches driven by the open page (one POST every few seconds, each at most ~4 s of work; 20 s without JavaScript), so the default PHP `max_execution_time` (30 s) and Nginx `fastcgi_read_timeout` (60 s) are enough. The summary is then confirmed with **Terbitkan dan nyalakan**, which creates and activates a release with the same versions and the new package code. **Matikan** activates a release without a package.
- During a running study every package change asks for a tick and a reason (audit `release_activate` with `package_from`, `package_to`, `reason`).
- When `.env` sets `gelita.paket.aktif = false`, the page shows "Mode paket dimatikan dari server" and students get normal URLs whatever the switch says.
- The setting **Izinkan mode daring untuk perangkat lab** lives in `writable/paket/pengaturan.json`, which the backup scripts already archive with `writable/paket/`.
- **After a code deploy while package mode is on**, press **Bangun ulang paket** on Paket aset and publish it, so devices get the new `kode` group (only changed files are downloaded). The deploy scripts do not rebuild the package automatically yet.
- Before data collection, `php spark gelita:content:verify --siap-penelitian` also re-hashes the active package and fails while package mode is off.

#### Teacher routine before class: Siapkan GELITA and Periksa paket

*(New subsection, English; asset package Stage 3, plan §8.1, §8.2, §11.1.)* With package mode on, every device must hold the full package (both languages) before **Mulai** works; there is no skip button.

- **The day before, or an hour before class** (30 devices downloading at once can fill the school Wi-Fi): on each device open GELITA and press **Mulai**. The device shows **Siapkan GELITA** and downloads the package (about the size shown on **Rilis → Paket aset**); when it says "GELITA siap dimainkan!", the device is ready for every student who uses it. A dropped connection only pauses the download; it continues by itself, from where it stopped.
- **Right before class**, on each device open `/periksa-paket` (also linked under "Untuk guru" on Siapkan GELITA) and press **Periksa semua berkas**. Anything missing or damaged → **Unduh yang kurang**. **Simpan permanen** asks the browser to keep the package (Firefox shows a question). No staff login is needed, so the teacher never leaves a staff session on a student device; the page only touches this browser's copy of public images and sounds.
- **iPad:** add GELITA to the Home Screen (Bagikan → Tambah ke Layar Utama). In a normal Safari tab the package can be removed after 7 days without a visit (school holidays).
- **Devices that cannot hold the package** get **Main daring di perangkat ini** for the current browser session (recorded as `package_mode = daring`): private windows, too little storage (free space and press **Coba lagi** instead where possible), and, only when **Izinkan mode daring untuk perangkat lab** is on, lab profiles that are wiped at logout (the choice is under "Untuk guru"). Prefer normal device accounts for GELITA labs.
- **Hapus paket di perangkat ini** on Periksa paket frees the space (for example after package mode is switched off). While package mode is on, the device must then download again before Mulai.
- After a code deploy or a rebuild, devices download only the changed files the next time someone presses Mulai.

### Ukuran aset

Aset gambar dan video mendominasi ukuran instalasi. Anjuran:

* Latar dan adegan: WebP (atau JPEG) kualitas 80, maksimum 1920×1080. Since 11 October 2026 WebP is the default raster format (owner decision); `app.*` icons stay PNG.
* Objek dan benda: WebP atau PNG dengan transparansi, maksimum 400×400.
* Frame tokoh: sampai 24 gambar per pose (diputar berulang), WebP ± 60 KB per gambar.
* Video Pustaka: MP4 H.264, 720p, bitrate ≤ 1.5 Mbps, durasi ≤ 60 detik.
* Audio narasi: MP3 96–128 kbps mono.

Ukuran piksel resmi tiap slot ada di `Config\Gelita::$assetSizes` dan ditegakkan saat unggah. Batas unggah 64 MB (`Config\Gelita::$maxUploadBytes`, `upload_max_filesize`, `client_max_body_size 72M`).

---

## Tugas Terjadwal

Jam sistem wajib WIB (langkah 0 kedua jalur). Di VPS berzona UTC, jadwal "02.00" akan berjalan pukul 09.00 WIB.

| Tugas | Waktu | Jalur L | Jalur W |
|---|---|---|---|
| Backup | 01.00 setiap hari; 08.00, 12.00, 16.00 hari sekolah | cron `deploy` | Task Scheduler `\GELITA\Backup` |
| Retensi (`gelita:retention:run`) | 02.00 setiap hari | cron `www-data` | Task Scheduler `\GELITA\Retensi` |
| Rotasi log > 30 hari | Minggu 03.00 (L) / bersama retensi (W) | cron `www-data` | di dalam `retensi.ps1` |

Backup lebih sering pada jam sekolah disengaja: sesi kelas berlangsung pada jam itu, dan kehilangan satu sesi berarti mengulang satu kelas. Banyak SD masuk hari Sabtu. Bila demikian, ganti `1-5` menjadi `1-6` dan tambahkan `Saturday`.

`gelita:retention:run` menandai sesi dan pengerjaan yang menganggur (`paused`/`abandoned`), menghapus berkas unduhan data yang kedaluwarsa (`gelita.exportRetentionDays`, bawaan 7 hari), dan membuat **pratinjau** penghapusan untuk data yang melewati `research_studies.retention_days`. Data penelitian tidak pernah dihapus tanpa persetujuan manusia di **Tata kelola** (`/admin/tata-kelola`); pratinjau yang menunggu tampil sebagai peringatan di **Ringkasan**.

**Jalur L** — `crontab -e` sebagai `deploy`:

```cron
0 1 * * *          bash /var/www/gelita/deploy/linux/backup.sh >> /var/backups/gelita/backup.log 2>&1
0 8,12,16 * * 1-5  bash /var/www/gelita/deploy/linux/backup.sh >> /var/backups/gelita/backup.log 2>&1
```

Log backup ditulis di folder backup milik `deploy`. Berkas log di tempat yang tidak dapat ditulis `deploy` (mis. `/var/log/…`) membuat shell gagal membukanya, dan perintah backup tidak pernah dijalankan.

`sudo crontab -u www-data -e`:

```cron
0 2 * * * cd /var/www/gelita && php spark gelita:retention:run >> writable/logs/cron.log 2>&1
0 3 * * 0 find /var/www/gelita/writable/logs -name 'log-*.log' -mtime +30 -delete
```

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

`deploy\windows\retensi.ps1` menjalankan `php spark gelita:retention:run` dan menambahkan keluarannya ke `writable\logs\cron.log` (UTF-8), lalu menghapus `log-*.log` yang lebih tua dari 30 hari. Kode keluarnya mengikuti `gelita:retention:run`. Dua hal di skrip itu disengaja:

- `[Console]::OutputEncoding` disetel ke UTF-8 (dibungkus `try`, karena proses tanpa konsol dapat menolaknya). Tanpa itu, PowerShell membaca keluaran `php.exe` dengan code page OEM, dan tanda panah di ringkasan retensi menjadi karakter rusak di `cron.log`.
- `$ErrorActionPreference = 'Continue'`: di PowerShell 5.1, stderr program yang dialihkan `2>&1` menjadi galat yang menghentikan skrip bila preferensinya `'Stop'`.

Hasil setiap tugas terlihat di Task Scheduler (*Last Run Result*: `0x0` berhasil, `0x1` gagal, `0x2` salinan kedua backup gagal).

---

## Backup

Isi setiap backup:

1. **Database, tanpa isi `ci_sessions`.** Tabel sesi memuat IP mentah dan data sesi siswa (lihat [Tata kelola data anak](#tata-kelola-data-anak)) dan tidak berguna setelah dipulihkan. Strukturnya tetap ikut, sehingga hasil pulih berisi 31 tabel.
2. **`public/assets/uploads/`**: gambar dan rekaman yang diunggah lewat panel, gambar contoh, dan poster video Pustaka; satu-satunya aset yang tidak ada di git.
3. **Asset packages** (`public/assets/p/`, `public/assets/k/`, `public/paket/`, `writable/paket/`), built on the server by `gelita:paket:bangun`; skipped while no package has been built.
4. **Salinan `.env`**, yang memuat `encryption.key`.

Backup disimpan 30 hari di lokasi pertama lalu disalin ke lokasi kedua. **Backup yang hanya ada di mesin yang sama dengan datanya bukan backup.** Bila salinan kedua gagal, skrip keluar dengan kode **2** dan mencetak peringatan, sehingga kegagalan terlihat oleh cron dan Task Scheduler.

Backup memuat data pribadi anak dan kredensial. Folder backup hanya dapat dibaca pemiliknya (`umask 077` / ACL). Drive eksternal sebaiknya dienkripsi (LUKS / BitLocker To Go).

| Langkah | Jalur L (`deploy/linux/backup.sh`) | Jalur W (`deploy\windows\backup.ps1`) |
|---|---|---|
| Database | `mysqldump --defaults-extra-file=backup.cnf --single-transaction --quick --no-tablespaces --default-character-set=utf8mb4 --ignore-table=gelita.ci_sessions`, ditambah struktur `ci_sessions` (`--no-data`), di-gzip ke `gelita-db-{stamp}.sql.gz.part`, diuji `gzip -t`, baru diganti nama | dua `mysqldump` dengan `--result-file` (pengalihan `>` di PowerShell 5.1 menulis UTF-16), dikemas ke `gelita-db-{stamp}.zip`; berkas `.sql` sementara selalu dihapus |
| Unggahan | `tar czf gelita-uploads-{stamp}.tar.gz` | `Compress-Archive` → `gelita-uploads-{stamp}.zip` |
| Asset packages | `tar czf gelita-paket-{stamp}.tar.gz -C $APP` with the four folders as relative paths (missing ones skipped) | `gelita-paket-{stamp}.zip` (`p`, `k`, `paket` from `public/`) and `gelita-paket-peta-{stamp}.zip` (`writable/paket`); two archives because both `paket` folders would collide in one zip |
| `.env` | `cp` → `gelita-env-{stamp}` | `Copy-Item` → `gelita-env-{stamp}.txt`, lalu `LastWriteTime` disetel ke waktu backup |
| Simpan 30 hari | `find … -mtime +30 -delete` | `Get-ChildItem` + `Remove-Item` |
| Salinan kedua | `rsync -a "$DIR/" "$KEDUA/"`; gagal → kode 2 | salin berkas yang belum ada ke `Kedua`; gagal → kode 2 |
| Lokasi bawaan | `DIR=/var/backups/gelita`, `KEDUA=/mnt/backup-eksternal/gelita` (timpa di `ops.conf`) | `Dir`, `Kedua` di `config.psd1` |

- `--no-tablespaces` menghindari tuntutan hak global `PROCESS` di MySQL 8.0.21+ (juga didukung MariaDB). Skema GELITA tidak punya routine atau trigger, jadi `--routines --triggers` tidak diperlukan.
- `Copy-Item` mewarisi stempel waktu `.env`. Tanpa penyegaran `LastWriteTime`, salinan `.env` yang tidak berubah lebih dari 30 hari langsung terhapus langkah simpan 30 hari dan tidak pernah sampai ke lokasi kedua. Jalur L tidak terdampak: `cp` tanpa `-p` memberi stempel waktu baru.
- Salinan kedua di kedua jalur hanya **menambah** berkas (tanpa `--delete` / `/MIR`). Dengan begitu, lokasi pertama yang rusak atau kosong tidak ikut menghapus salinan kedua.

### Uji pemulihan

Lakukan minimal sekali sebelum penelitian dimulai, dan setelah setiap perubahan skrip backup. Backup yang belum pernah dipulihkan statusnya belum diketahui.

```bash
# Jalur L
mysql -u root -p -e "CREATE DATABASE gelita_uji;"
gunzip -c /var/backups/gelita/gelita-db-YYYYmmdd-HHMM.sql.gz | mysql -u root -p gelita_uji
mysql -u root -p gelita_uji -e "
  SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'gelita_uji';  -- 30
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

Di PowerShell, operator `<` tidak ada, jadi pemulihan memakai `source`. Kedua berkas `.sql` tidak saling bergantung, jadi urutannya bebas. Pemulihan ke database production (`gelita`) mengikuti langkah yang sama dengan mode pemeliharaan aktif; pulihkan juga `public/assets/uploads/` dari arsip unggahan dengan stempel waktu yang sama. Restore the asset packages from the package archive of the same stamp as well: Jalur L `tar xzf gelita-paket-{stamp}.tar.gz -C /var/www/gelita`; Jalur W unpack `gelita-paket-{stamp}.zip` into `public\assets\` (`p`, `k`) and `public\` (`paket`), and `gelita-paket-peta-{stamp}.zip` into `writable\`. Then run `php spark gelita:paket:periksa`. Without them the game falls back to normal asset URLs on its own (the release's package has no URL map). Setelah itu, semua pengguna perlu login ulang karena `ci_sessions` kosong.

---

## Verifikasi Setelah Deploy

Jalankan seluruh daftar ini setelah instalasi baru dan setiap pembaruan kode. Contoh memakai Jalur L. Padanan Jalur W: jalankan tanpa `sudo -u www-data`, pakai `C:\gelita-ops\backup.cnf`, dan tulis `curl.exe` (di Windows PowerShell 5.1, `curl` adalah alias `Invoke-WebRequest`) dengan `-o NUL` pengganti `-o /dev/null`.

```bash
# 1. Struktur konten — harus 0 galat (peringatan gambar contoh boleh sampai gambar asli diunggah)
sudo -u www-data php spark gelita:content:verify

# 2. Database — angka yang diharapkan di komentar
mysql --defaults-extra-file=/home/deploy/.gelita/backup.cnf gelita -e "
  SELECT COUNT(*) AS wilayah FROM levels WHERE is_active = 1;                               -- 3
  SELECT l.code, COUNT(*) AS tantangan FROM challenge_nodes n JOIN levels l ON l.id = n.level_id
   WHERE n.is_active = 1 GROUP BY l.code;                                                   -- 4 per wilayah
  SELECT COUNT(*) AS soal, SUM(scorable) AS dinilai FROM challenge_items WHERE is_active = 1; -- 126, 108
  SELECT COUNT(*) AS dialog FROM dialogues;                                                 -- 88
  SELECT COUNT(*) AS pustaka FROM library_pages WHERE is_active = 1;                        -- 30
  SELECT COUNT(*) AS gambar_contoh FROM media_assets WHERE is_placeholder = 1 AND is_active = 1; -- 66, lalu 0
  SELECT locale, approval_status, COUNT(*) FROM audio_assets GROUP BY locale, approval_status; -- 100 approved per bahasa
  SELECT release_code FROM game_releases WHERE is_active = 1;                               -- GELITA-KELAS5-2026"

# 3. writable dapat ditulis runtime (Jalur L)
sudo -u www-data touch /var/www/gelita/writable/uji && \
  rm /var/www/gelita/writable/uji && echo "writable OK"

# 4. HTTPS & header keamanan — pada halaman DAN aset, masing-masing tepat satu kali
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

Jumlah rekaman narasi (pemeriksaan 2) baru 100 `approved` per bahasa setelah persetujuan di panel; sebelum itu `draft`. `HEAD` dilayani rute GET dengan header yang sama tanpa isi (`App\Libraries\HeadAsGetRouteCollection`), jadi `curl -I` dan pemantau uptime berbasis HEAD bekerja.

Uji manual:

1. Buka `/` — halaman awal tampil, gambar termuat.
2. Daftar siswa uji di `/daftar` (tiga langkah, kelas 5, NPSN sekolah SD/MI Jawa Tengah, centang persetujuan): coba sandi `kedu2026` — ditolak dengan keterangan syarat yang belum terpenuhi; lalu `Kedu#2026` — diterima. Keluar, lalu masuk kembali di `/masuk` — progres tetap.
3. Reset sandi siswa uji dari `/admin/peserta/{id}` — login dengan sandi sementara langsung diarahkan ke `/ganti-sandi`.
4. Kerjakan satu tantangan sampai selesai — penanda kesempatan berkurang saat jawaban salah, layar selesai menampilkan bintang.
5. Ganti bahasa ke EN di tengah permainan — progres tetap, teks dan narasi berganti.
6. Masuk `/admin/login` — **Ringkasan** panel tampil.
7. Buat unduhan data XLSX kecil (**Unduh data**) — berkas terunduh dan terbuka di Excel.
8. Buka sesi uji di **Sesi bermain** (`/admin/sesi/{id}`) — linimasa event sesi itu terlihat di bagian bawah (`#linimasa`); **Data mentah** (`/admin/data`) menampilkan percobaan siswa uji di tab Riwayat percobaan.
9. Hapus peserta uji lewat **Tata kelola** (`/admin/tata-kelola`) dengan pratinjau dan konfirmasi.
10. Buka **Tantangan** (`/admin/konten`) — 12 tantangan, 4 per wilayah; **Rekaman narasi** (`/admin/konten/narasi`) — tidak ada rekaman yang belum disetujui.

Baru setelah **delapan pemeriksaan otomatis dan sepuluh uji manual** ini lolos, aplikasi dinyatakan siap dipakai kelas. Data siswa uji yang tersisa ikut dikosongkan `gelita:reset` sebelum pengambilan data penelitian.

---

## Operational Notes

### Sebelum sesi kelas

1. **Sebelum pengambilan data pertama:** gambar contoh sudah diganti, daftar siap penelitian di **Penelitian** (`/admin/studi`) hijau, dan `gelita:reset` sudah dijalankan (lihat [Sebelum pengambilan data penelitian](#sebelum-pengambilan-data-penelitian)); `gelita:content:verify --siap-penelitian` lolos.
2. Buka **Kelengkapan aset** (`/admin/media/kelengkapan`): aset yang belum ada tidak menghentikan permainan (dipakai pengganti), tetapi rekaman narasi yang masih draft tidak terdengar siswa.
3. Pastikan ruang disk cukup: `df -h` (L) / `Get-PSDrive C, D` (W).
4. **Siapkan NPSN sekolah.** Siswa Jawa Tengah wajib mengisi NPSN saat mendaftar, dan pendaftaran hanya menerima kelas 5 SD/MI. Siswa SD jarang hafal NPSN: cari di **Sekolah** (`/admin/sekolah`, ketik nama atau desa), lalu tuliskan di papan bersama nama sekolahnya. Saat 8 angka diketik, nama resmi sekolah muncul untuk dicocokkan anak.
5. Jalankan backup manual: `bash /var/www/gelita/deploy/linux/backup.sh` (L) / `Start-ScheduledTask -TaskPath '\GELITA\' -TaskName 'Backup'` (W).
6. Jalur W: pastikan Laragon menyala (Nginx + MySQL hijau) dan tidak ada restart Windows Update yang tertunda.
7. Sediakan **10–15 menit** di awal pertemuan pertama untuk pendaftaran tiga langkah. Membuat kata sandi kuat adalah bagian pembelajaran literasi keamanan digital, bukan hambatan teknis: biarkan anak membaca syaratnya dan memperbaiki sendiri sandi yang ditolak.
8. Pastikan guru tahu cara mereset sandi di `/admin/peserta/{id}`. Jangan meminta anak menuliskan sandinya di daftar kelas — bila lupa, reset.
9. Untuk anak yang kesulitan mengetik simbol, tunjukkan letak tombol simbol di papan ketik atau papan ketik layar tablet sebelum mulai.
10. **HP/tablet kelas: pasang GELITA di layar utama** sekali per perangkat. Buka alamat GELITA di Chrome (Android), lalu ketuk **Pasang sekarang** di jendela *Pasang GELITA dulu, yuk!* yang muncul di halaman awal, atau tombol **Pasang GELITA** (atau menu ⋮ → *Instal aplikasi* / *Tambahkan ke layar utama*). Di iPhone/iPad buka di Safari, ketuk **Bagikan → Tambah ke Layar Utama**. Setelah itu anak membuka GELITA dari ikonnya. Aplikasi terpasang tampil tanpa bilah alamat; rotasi tidak terkunci (sejak 18 Oktober 2026, agar ikon tetap bisa dibuka di HP Xiaomi), jadi nyalakan *Putar otomatis*. Di iPhone/iPad, login aplikasi terpasang terpisah dari login di Safari.

### Setelah sesi kelas

1. Unduh data XLSX (**Unduh data**) sebagai salinan kerja.
2. Periksa **Sesi bermain** (`/admin/sesi`) — sesi yang masih `active` padahal kelas sudah selesai menandakan tab yang tidak ditutup; biarkan, retensi akan menandainya `paused`.
3. Catat hal tak biasa (anak yang berhenti di tengah, perangkat bermasalah) di catatan penelitian, bukan di aplikasi.
4. Buka **Sekolah** (`/admin/sekolah`): bila ada nama sekolah "belum terverifikasi" (siswa di luar Jawa Tengah menulis nama sekolah sendiri), gabungkan atau sahkan sebelum menarik data per sekolah.

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

`game_event_logs` akan menjadi tabel terbesar dan itu normal — ia memang catatan proses penelitian. Perkiraan kasar: satu peserta yang menyelesaikan 12 tantangan menghasilkan beberapa ratus event, kurang dari 1 MB termasuk index; seratus peserta sekitar 100 MB. `item_tries` (satu baris per percobaan) dan `device_logs` tumbuh jauh lebih lambat. Jangan menghapus raw event hanya karena ringkasannya sudah tampil di panel.

Bulanan: periksa tanggal kedaluwarsa sertifikat (pemeriksaan 8) dan jalankan satu uji pemulihan.

### Masalah umum

| Gejala | Penyebab biasa | Tindakan |
|---|---|---|
| Halaman putih / HTTP 500 di semua halaman | L: `.env` tidak terbaca `www-data` (mode 600, atau grup berubah setelah `sed -i`) | `sudo chown deploy:www-data .env && sudo chmod 640 .env`; pastikan `deploy` anggota grup `www-data` |
| Halaman putih pada halaman tertentu | galat PHP, `display_errors` mati | baca `writable/logs/log-*.log` |
| Situs terus menjawab 503 | pembaruan gagal dan mode pemeliharaan sengaja dibiarkan, atau `gelita:reset --pemeliharaan` terhenti paksa | baca keluaran skrip, perbaiki, hapus `writable/pemeliharaan.flag` |
| "Unable to connect to database" | kredensial `.env` salah atau MySQL mati; W: Laragon belum menyala setelah restart | L: `systemctl status mysql` (MariaDB: `mariadb`); W: buka Laragon → *Start All* |
| `CREATE command denied … gelita_app` saat migrate | migration berjalan tanpa DSN | jalankan lewat `database_default_DSN` (lihat [Kredensial migration lewat DSN](#kredensial-migration-lewat-dsn)) |
| `gelita:reset`: "Akun database tidak punya hak TRUNCATE (DROP)" | reset dijalankan dengan akun aplikasi | ulangi dengan `database_default_DSN` akun `gelita_migrate`; belum ada tabel yang dikosongkan |
| `db:seed`: "GELITA_ADMIN_PASSWORD kosong" | variabel tidak diteruskan ke `www-data` | `export` lalu `sudo --preserve-env=GELITA_ADMIN_PASSWORD -u www-data …` |
| `db:seed`: "berkas … tidak ditemukan" untuk Pustaka atau bank soal | folder `docs/` dibuang dari server | pulihkan dari git (`git checkout -- docs`), jalankan ulang seeder |
| `content:verify --siap-penelitian` gagal | masih ada gambar contoh yang dipakai tantangan | unggah gambar asli; `gelita:mockups --dry-run` menampilkan slot yang tersisa |
| Kartu video Pustaka tanpa gambar | server tidak dapat menghubungi penyedia thumbnail | buka akses keluar, lalu `gelita:library:thumbnails` |
| Gambar Pustaka tidak tampil di perangkat siswa | jaringan kelas memblokir `commons.wikimedia.org` | buka akses, atau unggah salinan gambarnya lewat panel |
| Halaman lambat saat kelas penuh | proses PHP habis | naikkan `pm.max_children` (L) / `PHP_FCGI_CHILDREN` (W) sesuai RAM |
| Unduhan data gagal di tengah | `memory_limit` atau batas 300 detik (W: dihitung jam dinding) | persempit rentang tanggal; periksa batas PHP |
| W: Nginx tidak mau start | port 80/443 dipakai Apache Laragon, IIS, atau aplikasi lain | matikan Apache di Laragon; `Get-NetTCPConnection -LocalPort 80` untuk mencari pemakainya |
| W: sertifikat kedaluwarsa padahal sudah diperpanjang | Nginx belum di-reload | jalankan `reload-nginx.cmd`; periksa langkah instalasi simple-acme |
| Tugas terjadwal W `0x1` | Laragon/MySQL mati saat tugas berjalan, atau path PHP berubah setelah ganti versi | nyalakan Laragon; perbarui `Php`/`MySql` di `config.psd1` |
| Gambar tidak muncul | aset belum diunggah atau `is_active = 0` | **Gambar & mockup** (`/admin/media`); `gelita:media:scan` |
| Audio tidak berbunyi | rekaman masih `draft` | **Rekaman narasi** → setujui |
| CSRF "action not allowed" | tab dibiarkan terbuka semalam | muat ulang halaman; `security.expires` 2 jam |
| Anak keluar / pindah komputer | sesi berakhir, tab tertutup | masuk lagi di `/masuk`; progres tetap |
| Staf selalu dikembalikan ke Ganti kata sandi | sandi sementara dari admin (reset / akun baru) belum diganti | ganti sandi di halaman itu; kewajiban hilang otomatis |
| Admin lupa kata sandi | tidak ada admin lain yang dapat mereset di `/admin/staf` | L: `sudo -u www-data php spark gelita:staff:password admin`; W: `php spark gelita:staff:password admin` di PowerShell. Masuk dengan sandi sementara yang dicetak, lalu buat sandi baru (reset juga membuka kunci) |
| Anak lupa kata sandi | — | guru mereset di `/admin/peserta/{id}`; anak membuat sandi baru saat masuk |
| Anak lupa nama pengguna | — | admin mencarinya di `/admin/peserta` berdasarkan nama & sekolah (guru tidak melihat nama maupun nama pengguna) |
| "Terlalu banyak percobaan" | siswa 8 kali salah sandi (staf 5 kali) | tunggu 5 menit (staf 15 menit), atau reset sandi (reset juga membuka kunci) |
| Pendaftaran terus ditolak | sandi belum memenuhi 5 syarat atau memuat nama pengguna | baca daftar syarat yang masih bertanda ✗ bersama anak |
| Impor workbook di panel gagal | galat baris di workbook | buka pratinjau, perbaiki sheet & baris yang disebut, unggah ulang |
| Event tertahan | koneksi putus sesaat, atau situs dalam pemeliharaan | otomatis terkirim saat aplikasi dibuka lagi di perangkat itu |
| Disk penuh | unduhan lama + backup + video | jalankan retensi; pindahkan backup lama |

### Keamanan operasional

1. Ganti sandi admin awal **sebelum** aplikasi dipakai di sekolah (lihat [Langkah terakhir](#langkah-terakhir-kedua-jalur)). Staf yang menerima sandi sementara (reset atau akun baru) dipaksa menggantinya lewat **Ganti kata sandi** saat masuk (`staff_users.must_change_password`).
2. Buat akun `guru` terpisah untuk tiap guru, dengan sekolah terisi. Jangan membagikan akun admin.
3. Nonaktifkan akun guru yang tidak lagi terlibat lewat `/admin/staf` (tombol *Nonaktifkan*, `is_active = 0`); jangan hapus — audit log-nya masih diperlukan.
4. Tinjau riwayat di `/admin/tata-kelola/audit` secara berkala, terutama aksi `export` dan `delete_execute`.
5. **Pembaruan sistem.**
   - L: `sudo apt update && sudo apt upgrade`.
   - W: Windows Update di luar jam sekolah, dan rilis patch PHP 8.3 lewat pembaruan Laragon. Ganti versi PHP berarti mengulang pengaturan `php.ini` (lihat [Pengaturan PHP](#pengaturan-php)).
6. **Dependency Composer** diperbarui di lingkungan pengembangan (`composer update`, uji, commit `composer.lock`), lalu dibawa ke server lewat pembaruan biasa. Di server hanya `composer install` dan `composer audit`. `composer update` di server membuat `composer.lock` berbeda dari repositori dan membuat `git pull` berikutnya bentrok.
7. `.env`, `migrate.dsn`, dan `backup.cnf` tidak pernah masuk repositori. `.gitignore` memuat `.env`, isi `writable/*` per subfolder, `public/assets/uploads/*`, dan `writable/pemeliharaan.flag`.
8. Port database (3306) tidak pernah dibuka ke jaringan (`bind-address = 127.0.0.1`; firewall Windows hanya membuka 80 dan 443).

### Tata kelola data anak

Dataset ini memuat data pribadi anak. Yang harus disiapkan bersama institusi sebelum pengambilan data:

1. **Persetujuan.** Aplikasi mencatat centang persetujuan siswa saat mendaftar (`participants.assented_at`). Persetujuan orang tua/wali dan buktinya dikelola institusi di luar aplikasi.
2. **Retensi.** Tetapkan masa simpan studi (`research_studies.retention_days`) sesuai kesepakatan dengan sekolah dan institusi. Nilai bawaan 1825 hari adalah titik awal, bukan keputusan.
3. **Akses terbatas.** Guru hanya melihat sekolahnya, unduhan guru selalu anonim, akun admin sesedikit mungkin.
4. **Unduhan pseudonim sebagai bawaan.** Analisis dilakukan dengan kode peserta; nama hanya dibuka bila memang diperlukan dan tercatat di audit log.
5. **Penghapusan yang dapat ditelusuri.** Selalu lewat pratinjau, konfirmasi, dan audit di **Tata kelola**. Bila orang tua menarik persetujuan, buat permintaan penghapusan untuk peserta itu.
6. **Kata sandi anak** disimpan hanya sebagai hash; guru dan admin tidak dapat melihatnya. Sandi sementara hasil reset hanya tampil sekali. Nama pengguna diperlakukan sebagai data identitas dan tidak ikut unduhan anonim.
7. **IP mentah di `ci_sessions`.** Sesi memakai `DatabaseHandler` bawaan CodeIgniter; `App\Libraries\HashedIpSessionHandler` belum dipasang (lihat [02_PROJECT_FOUNDATION.md](02_PROJECT_FOUNDATION.md)). Karena itu kolom `ci_sessions.ip_address` menyimpan IP **mentah** selama sesi hidup, sampai dibersihkan garbage collector sesi. Tabel lain hanya menyimpan `ip_hash`, dan backup tidak membawa isi `ci_sessions`. Nyatakan hal ini dalam dokumen perlindungan data sampai handler hash dipasang.
8. **Backup** memuat data pribadi anak: batasi aksesnya dan enkripsi media salinan kedua.

Persetujuan etik dan penafsiran hukumnya tetap urusan institusi. Aplikasi menyediakan mekanismenya; kebijakannya ditetapkan manusia.

---

## Aturan Sistem

1. GELITA production selalu satu instansi server ber-HTTPS di domain sendiri, di Jalur L (Linux) atau Jalur W (Windows + Laragon). Tidak ada instalasi tanpa HTTPS di jaringan lokal.
2. Server baru selalu dipasang lewat [Instalasi baru](#instalasi-baru): `migrate` → `db:seed DatabaseSeeder` → `gelita:narration:import` dan persetujuan narasi → `gelita:content:verify`.
3. Web server production adalah Nginx di kedua jalur. Hanya `index.php` yang dieksekusi PHP, dan berkas tersembunyi tidak pernah dilayani.
4. `.env` tidak pernah masuk repositori. Jalur L: `640 deploy:www-data`, dan `deploy` anggota grup `www-data`.
5. Akun MySQL aplikasi tidak punya hak DDL. Migration dan `gelita:reset` memakai `gelita_migrate` lewat `database_default_DSN` (tanpa port), backup memakai `gelita_backup` yang hanya membaca. Rahasia keduanya di luar repositori.
6. `database.default.DSN` tidak pernah ditulis di `.env`.
7. Jalur L: semua `php spark` dijalankan sebagai `www-data`.
8. `writable/exports/` tidak pernah dapat diakses langsung lewat URL.
9. Pembaruan kode selalu lewat `deploy.sh`/`deploy.ps1`: didahului backup, dibatalkan otomatis bila ada sesi kelas aktif, berjalan di balik mode pemeliharaan, dan hanya membuka situs kembali bila `gelita:content:verify` lolos.
10. Setiap pembaruan menaikkan `gelita.assetVersion`. Jalur L juga me-reload PHP-FPM; Jalur W mengandalkan `opcache.validate_timestamps = 1`.
11. Hanya URL ber-`?v=` yang boleh di-cache `immutable`; aset lain wajib revalidasi.
12. Aset masuk server hanya lewat git (folder aset resmi) atau panel (`uploads/`). Tidak ada salinan manual ke folder aset.
13. Pengambilan data penelitian baru dimulai setelah narasi disetujui, gambar contoh diganti, daftar siap penelitian di **Penelitian** hijau, uji asap lolos, `gelita:reset` dijalankan, dan `gelita:content:verify --siap-penelitian` lolos. `gelita:reset` tidak dijalankan lagi setelah pengambilan data dimulai.
14. Backup disalin ke lokasi kedua dan hanya menambah berkas; kegagalan salinan kedua terlihat (kode 2). Backup yang belum pernah diuji pulih dianggap belum ada.
15. Jam sistem server WIB. Jadwal backup dan retensi mengikuti jam itu.
16. Retensi tidak menghapus data penelitian secara otomatis — ia hanya membuat permintaan berstatus pratinjau untuk disetujui manusia.
17. Sandi admin awal diganti sebelum pemakaian nyata, dan tiap guru punya akun sendiri dengan sekolahnya.
18. Raw event tidak dihapus hanya karena ringkasannya sudah ada di panel.
19. Bank soal dan Pustaka dimuat lewat impor workbook dengan pratinjau; tidak ada penyuntingan langsung ke database.
20. Lupa sandi siswa diselesaikan dengan reset oleh guru, tidak pernah dengan mencatat sandi anak.
21. Skrip di `deploy/` tidak diedit di server; nilai khusus server ditulis di `ops.conf` / `config.psd1`. `composer update` dan `php spark optimize` tidak dijalankan di server.
22. Rollback selalu dimulai dari migration (selagi kode rilis baru terpasang), baru kemudian kode.
23. Skrip `.ps1` dan `.psd1` hanya berisi ASCII, agar Windows PowerShell 5.1 membacanya sama dengan PowerShell 7.
24. `gelita:simulasi` (siswa simulasi) hanya untuk mesin pengembangan dan tidak dijalankan di server; command itu menolak berjalan bila `CI_ENVIRONMENT = production`.

---

## Keputusan yang tercatat

- **Unduhan data tetap sinkron**, dibangun di request yang sama. Batas `gelita.exportMaxRawEvents` (`Config\Gelita::$exportMaxRawEvents`, 200.000 baris) dan 300 detik sudah menjaga satu request tetap terkendali, sedangkan antrean kerja butuh proses latar yang harus dijaga di dua platform. Ditinjau ulang bila unduhan nyata di data penelitian mendekati 120 detik.
- **Mode pemeliharaan di Nginx, bukan filter CodeIgniter**: tanpa kode tambahan, `php spark` tetap bisa dipakai selama pemeliharaan, dan perilakunya sama di kedua jalur.
- **Tiga akun database**: cron hanya memegang kredensial baca, dan kredensial DDL hanya dibaca saat migration atau reset.
- **Isi awal lewat seeder dengan akun aplikasi**: `DatabaseSeeder` hanya menulis data, jadi tidak memerlukan akun migrasi. Hanya `gelita:reset` yang butuh hak `TRUNCATE`.

---

## Catatan Implementasi Tahap 8

Berkas di `deploy/` dijalankan di server uji, bukan hanya dibaca: dari server kosong, lewat pembaruan, sampai rollback, untuk jalur sukses, jalur gagal, dan penolakan saat ada kelas aktif. Aturan di dokumen ini yang diberi alasan "terbukti" berasal dari uji itu. `tests/unit/DeployFilesTest.php` mengunci cacat yang ditemukan agar tidak kembali.

Lingkungan uji:

- Ubuntu 24.04 berzona waktu Asia/Jakarta, MariaDB 10.11.14, Nginx 1.24.0, PHP-FPM 8.3.6 untuk web, PHP 8.4 CLI, CodeIgniter 4.7.4 dari `composer.lock`;
- user `deploy` + `www-data`, sudoers, `.env` 640, dan tiga akun database persis seperti di [Model pengguna dan izin](#model-pengguna-dan-izin) dan [Akun database](#akun-database);
- repositori *origin* lokal, supaya `git pull` di skrip deploy menarik commit pembaruan sungguhan;
- sertifikat self-signed di path Let's Encrypt, sebagai pengganti certbot;
- PowerShell 7.4 untuk skrip `.ps1` (cara Windows PowerShell 5.1 membacanya disimulasikan), dengan `config.psd1` berisi path Linux.

### Yang diuji

| Skenario | Hasil |
|---|---|
| `nginx -t` dengan blok 443 sebelum sertifikat ada | gagal (`cannot load certificate`), sesuai urutan port 80 → sertifikat → 443 |
| Delapan [pemeriksaan otomatis](#verifikasi-setelah-deploy) | lolos. Empat header keamanan muncul tepat sekali di halaman, aset, dan 404. Login admin lewat HTTPS (CSRF, sesi database, cookie `Secure`) berhasil |
| `backup.sh` dengan environment kosong seperti cron | tanpa lokasi kedua: kode 2 dan backup lokal tetap jadi. Dengan `ops.conf`: kode 0. Semua berkas bermode 600 |
| Uji pemulihan kedua jalur | semua tabel pulih, `ci_sessions` kosong; IP yang ditanam di `ci_sessions` tidak ada di dump |
| `deploy.sh` / `deploy.ps1` saat ada sesi aktif ≤ 10 menit | BATAL, kode 1, tanpa backup dan tanpa pemeliharaan. Sesi yang menganggur 11 menit tidak dihitung. `--paksa` melanjutkan |
| `deploy.sh` jalur sukses | 503 selama pembaruan, 200 sesudahnya. Migration berjalan dengan `gelita_migrate`. Kode baru terlayani setelah reload FPM. `assetVersion` naik dan `?v=` di halaman ikut berubah. `.env` tetap `640 deploy:www-data` |
| `deploy.sh` / `deploy.ps1` saat `content:verify` gagal | situs tetap 503, pesan pemulihan dicetak, kode 1 |
| Baris crontab di dokumen, dijalankan `/bin/sh` dengan environment kosong | backup tercatat di `backup.log`, retensi di `cron.log`, rotasi menghapus log > 30 hari |
| Rollback dengan urutan migration → kode | migration di-rollback, kode kembali ke rilis lama, situs 200 |
| `backup.ps1` | kode 0; kode 2 bila lokasi kedua tak dapat dibuat; kode 1 bila `mysqldump` gagal, tanpa `.sql` tertinggal |
| `retensi.ps1` | kode 0; kode 1 bila database tidak terjangkau; log > 30 hari terhapus |
| Konfigurasi Nginx Jalur W di Nginx Linux (path dipetakan, `http2 on;` dilepas, `upstream php_upstream` → FPM) | `nginx -t` lolos; halaman 200, header tepat sekali, cache sesuai aturan, berkas sensitif 404, pemeliharaan 503 |

### Cacat yang ditemukan dan diperbaiki

| # | Cacat | Akibat | Perbaikan (di skrip dan dokumen ini) |
|---:|---|---|---|
| 1 | Migration dengan `DB_USER=gelita_migrate` atau variabel `database_default_username` | CodeIgniter tetap memakai `gelita_app` dari `.env` → `CREATE command denied` | `database_default_DSN`, kunci yang tidak ada di `.env`, tanpa port |
| 2 | `.env` `600 deploy:deploy` | PHP-FPM tidak dapat membaca `.env` → semua halaman HTTP 500 | `640 deploy:www-data` |
| 3 | `sed -i … .env` di `deploy.sh` | grup `.env` berubah → HTTP 500 setelah setiap pembaruan | `deploy` masuk grup `www-data` + `chgrp`/`chmod` sesudah `sed` |
| 4 | `php spark down` / `up`, `php spark session:migration` | tidak ada di CodeIgniter 4.7.4 | mode pemeliharaan Nginx (`writable/pemeliharaan.flag`); `ci_sessions` dibuat migration |
| 5 | Sandi backup lewat `-p"$MYSQL_PASS"` dengan `set -u`, dump langsung ke nama akhir | di cron tidak ada backup, tetapi berkas `.sql.gz` kosong tertinggal; sandi terlihat di daftar proses | option file `gelita_backup`; tulis `.part` → `gzip -t` → `mv` |
| 6 | Log cron backup di `/var/log/` | berkas tidak dapat dibuka `deploy` → backup tidak pernah dijalankan | log di `/var/backups/gelita/` |
| 7 | Crontab tanpa menyetel zona waktu | jadwal bergeser 7 jam di VPS berzona UTC | `timedatectl set-timezone Asia/Jakarta` |
| 8 | `immutable` untuk semua aset, `add_header` di dalam `location` | modul dan media lama bertahan setelah pembaruan; header keamanan hilang dari aset; header ganda di halaman | `immutable` hanya untuk `?v=`, `add_header` hanya di `server`, `fastcgi_hide_header` |
| 9 | `chmod -R 775 writable`, `php spark` sebagai `deploy` | working tree kotor; cache dan log tidak dapat ditulis PHP-FPM | direktori `2775`; semua `php spark` sebagai `www-data` |
| 10 | Isi `ci_sessions` ikut backup | IP mentah siswa tersalin ke dua lokasi selama 30 hari | hanya struktur tabel |
| 11 | Skrip `.ps1` memuat `→`, `—`, `·` sebagai UTF-8 tanpa BOM | Windows PowerShell 5.1 (cp1252) gagal mem-parse `deploy.ps1`; PowerShell 7.4 lolos, sehingga tidak terlihat tanpa simulasi | skrip `.ps1`/`.psd1` hanya ASCII |
| 12 | `backup.ps1`: `Copy-Item .env` tanpa penyegaran stempel waktu | `.env` berumur > 30 hari langsung terhapus retensi backup; backup Windows tanpa `encryption.key` | `LastWriteTime` salinan disetel ke waktu backup |
| 13 | Rollback: `git reset` lalu `migrate:rollback` | "gap in the migration sequence", tidak ada yang di-rollback, kode keluar 0 | [Rollback](#rollback): migration dulu, kode kemudian |
| 14 | `retensi.ps1` tanpa `[Console]::OutputEncoding` | ringkasan retensi rusak di `cron.log` | UTF-8 disetel di awal skrip |

Kedua skrip Bash lolos `shellcheck` tanpa temuan (`# shellcheck source=/dev/null` sebelum `. "$OPS/ops.conf"`).

`tests/unit/DeployFilesTest.php` mengunci:

- skrip Windows (`*.ps1`, `*.psd1`) hanya ASCII;
- skrip Bash ber-LF, `deploy.sh` memakai `set -Eeuo pipefail` dan `backup.sh` `set -euo pipefail`;
- konfigurasi Nginx kedua jalur hanya berbeda di baris khusus platform;
- tidak ada `spark down`/`up`/`optimize`/`session:migration`, `composer update`, atau `DB_USER` di skrip; kedua skrip deploy memakai `database_default_DSN`;
- stempel waktu salinan `.env` di `backup.ps1` disegarkan;
- tiga aturan `.gitignore` ada.

### Masih belum teruji

Hal berikut butuh mesin Windows atau domain sungguhan, dan tetap masuk daftar verifikasi saat pemasangan:

- Windows PowerShell 5.1 sungguhan (cara 5.1 membaca skrip sudah disimulasikan, tetapi skripnya belum pernah dijalankan di 5.1);
- GUI dan menu Laragon, `nginx -s reload` Laragon, dan php-cgi Laragon di balik `php_upstream`;
- direktif `http2 on;` (butuh Nginx ≥ 1.25; Nginx uji 1.24);
- penerbitan sertifikat nyata (Certbot/simple-acme);
- pendaftaran Task Scheduler, termasuk `[Console]::OutputEncoding` pada tugas yang berjalan tanpa login.

---

## Dependency

- **[01_DATABASE.md](01_DATABASE.md)**: skema 29 migration, seeder, daftar tabel dan perkiraan pertumbuhannya.
- **[02_PROJECT_FOUNDATION.md](02_PROJECT_FOUNDATION.md)**: ekstensi PHP, isi `.env`, struktur folder, daftar command `php spark`, status `HashedIpSessionHandler`.
- **03–06**: kode aplikasi yang dipasang; `asset_url_versioned()` dan pola `import` modul (06) menentukan aturan cache Nginx; perilaku antrean event saat 503 (06) menentukan mode pemeliharaan.
- **[07_FEATURE_INTEGRATION.md](07_FEATURE_INTEGRATION.md)**: unduhan data, retensi, format workbook, dan perilaku command `gelita:retention:run`, `gelita:content:verify`, `gelita:media:scan`, `gelita:bank:import`.
- **[docs/bank-soal/](bank-soal/README.md)**, **[docs/pustaka/](pustaka/README.md)**, **[docs/sekolah/](sekolah/README.md)**, **[docs/audio.md](audio.md)**: isi bawaan yang dimuat saat instalasi.

---

## Hasil Akhir

Setelah panduan ini dijalankan:

* GELITA berjalan sebagai satu instansi server lewat HTTPS, di Linux (Nginx + PHP-FPM) atau Windows + Laragon (Nginx + php-cgi), dengan header keamanan di setiap respons dan berkas sensitif tidak terlayani.
* Instalasi baru mengisi 28.487 sekolah resmi, akun admin, tiga wilayah dengan 12 tantangan, 126 soal kelas 5, 88 baris cerita, 30 halaman Pustaka, 66 gambar contoh, dan 100 rekaman narasi per bahasa yang disetujui, lalu lolos verifikasi konten.
* Sebelum pengambilan data, gambar contoh diganti gambar asli, daftar siap penelitian hijau, uji asap lolos, data uji coba dikosongkan dengan `gelita:reset`, dan `gelita:content:verify --siap-penelitian` lolos.
* Pembaruan kode berjalan dengan satu skrip (`deploy.sh` / `deploy.ps1`) yang menolak berjalan saat kelas aktif, membuat backup, memakai mode pemeliharaan, dan hanya membuka situs bila konten lolos verifikasi. Rollback terdokumentasi untuk kedua jalur.
* Migration dan reset memakai akun DDL terpisah lewat DSN; aplikasi dan backup memakai akun tanpa hak DDL.
* Browser selalu mendapat aset terbaru setelah pembaruan, sementara aset ber-versi tetap di-cache lama.
* Backup berjalan otomatis ke dua lokasi tanpa IP mentah siswa, kegagalannya terlihat, dan prosedur pemulihannya sudah pernah diuji.
* Retensi harian berjalan pada jam WIB yang benar; data penelitian tidak pernah terhapus tanpa persetujuan manusia.
* Delapan pemeriksaan otomatis dan sepuluh uji manual lolos, dan aplikasi dinyatakan siap dipakai kelas.
