# 08_DEPLOYMENT.md — Pemasangan Server & Operasional

Dokumen ini adalah panduan memasang GELITA di server **dari keadaan kosong** (instalasi baru) dan merawatnya sesudah itu: pembaruan kode, backup, tugas terjadwal, dan pemeriksaan harian. Tidak ada jalur pembaruan dari instalasi lama; server baru selalu dipasang mengikuti [Instalasi baru](#instalasi-baru).

Berkas operasional ada di folder [`deploy/`](../deploy/). Bila isi dokumen ini berbeda dengan berkas di sana, **berkas di repositori yang berlaku**.

---

## Tujuan

Menjalankan GELITA sebagai sistem production untuk penelitian siswa kelas 5 SD/MI. Dokumen ini mencakup:

- menyiapkan server Windows;
- mengonfigurasi environment, database, web server, dan HTTPS;
- instalasi baru sampai aplikasi siap dipakai kelas, termasuk persiapan sebelum pengambilan data penelitian;
- pembaruan kode dan rollback;
- penataan aset, backup, dan tugas terjadwal;
- langkah operasional harian.

---

## Konteks

GELITA adalah satu instansi server. Siswa, guru, dan peneliti mengaksesnya lewat internet dengan HTTPS di satu nama domain, `gelita.id` (the repository's files are written for this server, not as a template for other installs; owner, 21 November 2026). The validators' test install is planned on the subdomain `uji.gelita.id`, with its own content and database, separate from this production install (owner, same day); its site file and install steps are not written yet. Satu instansi dapat dipakai banyak kelas dan sekolah. Guru membuka panel dari mana saja.

The server is a Windows machine that runs Laragon behind a Cloudflare Tunnel (`cloudflared`). This is the only install path (owner, 21 November 2026).

| Bagian | Di server ini |
|---|---|
| Web server | Nginx bawaan Laragon |
| PHP | php-cgi 8.3, dijalankan Laragon di belakang `upstream php_upstream` |
| Database | MySQL bawaan Laragon |
| HTTPS | Cloudflare ends HTTPS, and `cloudflared` passes each request to Nginx on `127.0.0.1:80`. No certificate on the server and no port open to the network (see [HTTPS](#https)) |
| Tugas terjadwal | Task Scheduler |
| Skrip operasional | `deploy/windows/*.ps1` |
| Akun sistem | satu akun Windows yang menjalankan Laragon |

Syarat:

1. The domain `gelita.id` is managed in Cloudflare, and a Cloudflare Tunnel (`cloudflared`) runs on the server. The server needs no public IP address and no port forwarding.
2. Server menyala selama jam sekolah, dengan jam sistem di WIB.

Yang **tidak** didukung:

- Instalasi tanpa HTTPS di jaringan lokal.
- Apache. `public/.htaccess` dibiarkan untuk pengembangan lokal, tetapi aturan cache, header, dan mode pemeliharaan ditulis untuk Nginx.
- `php spark serve` di production.

Catatan Laragon: Laragon adalah lingkungan pengembangan. Panduan ini menutup kekurangannya untuk production: vhost otomatis yang ditulis ulang saat start, TLS lemah, `autoindex`, dan autostart. Periksa ketentuan lisensi di laragon.org sebelum memasangnya di institusi. Laragon 7 ke atas berlisensi berbayar, meski pemakaian non-komersial tanpa lisensi diizinkan dengan pengingat. Laragon 6 gratis, tetapi membawa PHP 8.1, jadi PHP 8.3 x64 harus ditambahkan manual ke `C:\laragon\bin\php\`.

---

## Production Environment

### Spesifikasi minimum

| Komponen | Minimum | Disarankan |
|---|---|---|
| CPU | 2 core | 4 core |
| RAM | 4 GB | 8 GB (Windows sendiri memakai 2–3 GB) |
| Disk | 20 GB | 50 GB (gambar/video + raw event + backup lokal) |
| OS | Windows 10/11 Pro 64-bit | Windows Server 2019+ atau Windows 11 Pro |

**Kapasitas** ditentukan jumlah proses PHP, bukan CPU. Setiap request memakai satu proses sampai selesai, dan pembuatan unduhan data dapat memegangnya sampai 300 detik. Karena itu `PHP_FCGI_CHILDREN` wajib dinaikkan dari bawaannya (lihat di bawah). Nginx di Windows memakai satu worker dengan `worker_connections 1024` (bawaan templat Laragon). Batas ini cukup untuk beberapa kelas bersamaan.

**Measured capacity (load test of 20–21 November 2026).** A Windows server with 4 logical CPUs and 12 GB RAM (Laragon, MySQL 8.4, behind a Cloudflare Tunnel) was loaded with `tools/qa/load-test.mjs` from another machine, 30 to 200 simulated students at once. With the settings of [Pengaturan PHP](#pengaturan-php) and [Pengaturan MySQL](#pengaturan-mysql) for that machine (64 PHP processes, 3 GB buffer pool), about **100 students at once** is comfortable (answer check p95 0.65 s, pages 1.1 s, both including about 1 s of network between the test machine and the server), and **150 at once works** (checks 1.2 s, pages 2.7 s at p95 and 6.3 s at p99). These are the rounds run after the event-write and session fixes of 21 November 2026; before them the same server took 8 s per page at 150 students, with the four cores 90–99 % busy, and answered some requests with HTTP 500 or 401. 200 students were only run before the fixes (pages 14 s at p95). The simulated students are heavier than real ones (an event every 2 s). Before the settings were changed (Laragon's defaults) the same server already stumbled at 30. More students at once need more cores, not more PHP processes.

### Perangkat lunak

| Komponen | Versi |
|---|---|
| PHP | 8.3 x64 (bawaan Laragon 8.x), **php-cgi** |
| CodeIgniter | 4.7.x — terkunci di `composer.lock` (4.7.4) |
| Database | MySQL bawaan Laragon (8.x) |
| Web server | Nginx bawaan Laragon (8.6+: 1.28 ke atas) |
| Composer | bawaan Laragon |
| Git | bawaan Laragon Full, atau Git for Windows |
| HTTPS | Cloudflare Tunnel (`cloudflared`); no certificate software on the server |

### Ekstensi PHP wajib

```
intl mbstring json mysqlnd curl gd zip fileinfo openssl iconv
dom xml xmlwriter simplexml
```

Menu Laragon → *PHP* → *Extensions*, centang `intl`, `mbstring`, `gd`, `zip`, `fileinfo`, `openssl`, `curl`, `mysqli`. `json`, `dom`, `xml`, `xmlwriter`, `simplexml`, dan `iconv` sudah terpasang di build PHP Windows. Laragon memakai satu `php.ini` per versi PHP untuk CLI maupun php-cgi.

Verifikasi:

```powershell
php -v
php -m | Select-String -Pattern '^(intl|mbstring|gd|zip|xmlwriter|mysqlnd|fileinfo)$'
```

Bila salah satu tidak ada, hentikan di sini. PhpSpreadsheet (impor workbook Pustaka dan bank soal saat instalasi, unduhan data) gagal tanpa `zip` dan `xmlwriter`; mPDF gagal tanpa `mbstring`; CodeIgniter menolak jalan tanpa `intl`.

### Pengaturan PHP

Menu Laragon → *PHP* → *php.ini*:

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
zend_extension = opcache
opcache.enable = 1
opcache.memory_consumption = 192
opcache.max_accelerated_files = 20000
opcache.validate_timestamps = 1   ; php-cgi Laragon tidak punya "reload"
opcache.revalidate_freq = 2
```

- **Opcache.** Tanpa perintah reload, `validate_timestamps = 0` akan membuat php-cgi terus menjalankan kode lama setelah pembaruan. Biaya pemeriksaan stempel waktu tiap 2 detik tidak berarti pada skala sekolah.
- `max_file_uploads = 100` (bawaan PHP 20) agar satu unggahan rekaman narasi di panel dapat memuat banyak berkas. Halaman **Rekaman narasi → Unggah** menampilkan batas yang berlaku.
- **Batas waktu.** Di Windows, `max_execution_time` dihitung dengan jam dinding (termasuk menunggu database). Karena itu unduhan besar lebih cepat menyentuh batas 300 detik; persempit rentang tanggal bila terjadi.
- **Jumlah proses PHP.** Atur di Menu Laragon → *Laragon* → *laragon.ini*, bagian `[nginx]`, lalu *Stop* dan *Start All*:

```ini
[nginx]
PHP_FCGI_CHILDREN=16
```

*(New, English; production load test of 20–21 November 2026.)* `16` is the least for a class. Size it from the machine:

| Server | `PHP_FCGI_CHILDREN` | `max_connections` (MySQL) |
|---|---|---|
| 2 cores, 4 GB RAM | 16 | 100 |
| 4 cores, 8 GB RAM | 32 | 150 |
| 4 cores, 12 GB RAM (the measured server) | 64 | 200 |

- Laragon starts one php-cgi parent that keeps this many children on one port (10987). Each child holds one database connection and about 50 MB under load: keep `max_connections` well above the number (staff downloads, scheduled tasks and backups connect too) and the total inside the RAM that MySQL's buffer pool and Windows leave.
- More processes than the cores can serve only makes every request slower: with 4 cores, 64 was enough and 200 was not tried on purpose.
- Do not build a second php-cgi pool by hand (other ports, a scheduled task): it was tried on the measured server, the processes never listened, and the site answered 502 until it was removed.
- After changing it: *Stop*, then *Start All*, and check in Task Manager that the php-cgi processes are there.

Mengganti versi PHP di Laragon berarti memakai `php.ini` lain. Ulangi pengaturan ekstensi dan `php.ini`, lalu perbarui `Php` di `config.psd1` dan tugas terjadwal.

### Pengaturan MySQL

Menu Laragon → *MySQL* → *my.ini*, di bawah `[mysqld]`.

**Replace Laragon's defaults** (production load test of 20–21 November 2026). Laragon's `my.ini` is written for small MyISAM development databases: a 128 MB InnoDB buffer pool and the binary log switched on. With it the measured server (4 cores, 12 GB RAM) was slow at 30 students; with the block below it served 100. After changing it, *Stop* and *Start All*:

```ini
[mysqld]
character-set-server = utf8mb4
collation-server     = utf8mb4_unicode_ci
default-time-zone    = '+07:00'
bind-address         = 127.0.0.1         # the database is never opened to the network

innodb_buffer_pool_size = 3G             # about a quarter of the RAM: 12 GB: 3G · 8 GB: 2G · 4 GB: 1G
innodb_log_buffer_size  = 32M
innodb_redo_log_capacity = 1G            # MySQL 8.0.30+; replaces innodb_log_file_size
innodb_flush_log_at_trx_commit = 1       # never loosen this; it is research data
skip-log-bin                             # no replica: the binary log only costs a second disk write per commit

max_connections   = 200                  # above PHP_FCGI_CHILDREN, see "Pengaturan PHP"
thread_cache_size = 64
table_open_cache  = 2000

sort_buffer_size     = 2M                # per connection: keep these small
read_buffer_size     = 1M
read_rnd_buffer_size = 2M
key_buffer_size      = 32M               # MyISAM only (system tables); GELITA's tables are InnoDB
```

- Remove or comment out Laragon's own lines for the same settings further down in `my.ini`: the last one read wins.
- `innodb_flush_log_at_trx_commit = 1` memang lebih lambat, tetapi data penelitian yang hilang karena listrik padam tidak dapat diulang.
- `skip-log-bin` switches off point-in-time recovery from the binary log. GELITA's recovery is the daily dump of [Backup](#backup), which does not use it.
- **`bind-address = 127.0.0.1` needs `database.default.hostname = 127.0.0.1` in `.env`.** With `localhost`, PHP on Windows tries the IPv6 address `::1` first, nobody listens there, and every request waited about 2.6 s before it fell back to `127.0.0.1` (measured on the server; the site looked slow with an idle CPU). The same goes for `backup.cnf`, `migrate.dsn` and the three database accounts (`@'127.0.0.1'`, below).
- `default-time-zone` memakai offset, bukan `Asia/Jakarta`, supaya jalan tanpa tabel zona waktu (tidak ada di MySQL Windows). Aplikasi sendiri menyetel `time_zone` tiap koneksi lewat `db_sync_timezone()`. Nilai server ini dipakai klien `mysql` dan kueri pemeriksaan di skrip deploy.
- `sql_mode` tidak perlu disetel. `Config\Database::$default['strictOn'] = false` membuat CodeIgniter membuang `STRICT_*` dari `sql_mode` setiap koneksinya, jadi nilai global server tidak berpengaruh pada aplikasi. Mengaktifkan mode strict adalah keputusan kode (`strictOn`), bukan konfigurasi server.
- Check after the restart: `mysql -u root -p -e "SHOW VARIABLES WHERE Variable_name IN ('innodb_buffer_pool_size','max_connections','log_bin','bind_address')"`.

Skema GELITA memakai InnoDB dan `utf8mb4_unicode_ci` di setiap tabel (`app/Database/GelitaMigration.php`).

MySQL Laragon terpasang dengan `root` **tanpa sandi**. Setel sandinya sebelum langkah lain:

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

CREATE USER 'gelita_app'@'127.0.0.1' IDENTIFIED BY 'SANDI_APP';
GRANT SELECT, INSERT, UPDATE, DELETE ON gelita.* TO 'gelita_app'@'127.0.0.1';

CREATE USER 'gelita_migrate'@'127.0.0.1' IDENTIFIED BY 'SANDI_MIGRATE';
GRANT ALL PRIVILEGES ON gelita.* TO 'gelita_migrate'@'127.0.0.1';

CREATE USER 'gelita_backup'@'127.0.0.1' IDENTIFIED BY 'SANDI_BACKUP';
GRANT SELECT, SHOW VIEW, TRIGGER, LOCK TABLES ON gelita.* TO 'gelita_backup'@'127.0.0.1';

FLUSH PRIVILEGES;
```

The accounts use the host `'127.0.0.1'`, not `'localhost'`. Di Windows, PHP dan klien `mysql` tersambung lewat TCP, dan `localhost` dapat diterjemahkan ke `::1` lebih dulu. Akun `@'127.0.0.1'` selalu cocok, apa pun setelan `skip-name-resolve`. Di `.env`, `backup.cnf`, dan `migrate.dsn`, tulis host `127.0.0.1` pula.

Pemisahan hak ini berarti bila aplikasi diretas lewat SQL injection, skema database tidak ikut menjadi korban. Seeder cukup memakai `gelita_app` karena hanya menulis data. `gelita:reset` butuh akun migrasi karena mengosongkan tabel dengan `TRUNCATE` (hak `DROP`); dengan akun aplikasi, reset ditolak sebelum ada tabel yang dikosongkan.

### Model pengguna dan izin

Satu akun Windows standar (misalnya `gelita`) menjalankan Laragon, `php spark`, deploy, dan Task Scheduler, jadi tidak ada konflik izin antarpengguna. Batasi siapa yang dapat membaca folder rahasia dan backup. `*S-1-5-32-544` adalah grup Administrators, ditulis sebagai SID agar tidak bergantung pada bahasa Windows:

```powershell
New-Item -ItemType Directory -Force C:\gelita-ops, D:\gelita-backup | Out-Null
foreach ($d in 'C:\gelita-ops', 'D:\gelita-backup') {
    icacls $d /inheritance:r /grant:r "*S-1-5-32-544:(OI)(CI)F" "${env:USERNAME}:(OI)(CI)M"
}
```

---

## Configuration

### `.env` production

Mulai dari templat (`Copy-Item env .env`). Templat sudah memuat nilai sesi, CSRF, dan cookie yang benar. Yang **wajib** diubah:

```ini
CI_ENVIRONMENT = production

app.baseURL = 'https://gelita.id/'
app.forceGlobalSecureRequests = true
app.CSPEnabled = true                          # Content Security Policy (Config\ContentSecurityPolicy)

database.default.hostname = 127.0.0.1          # wajib; `localhost` = ±2,6 detik per request
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
3. `gelita.ipSalt` wajib acak. Bila bocor, `ip_hash` dapat dibalik dengan pencarian menyeluruh karena ruang alamat IPv4 kecil.
4. `gelita.assetVersion` dibiarkan ada di `.env` (templat: `'1'`). Skrip deploy menaikkan nilainya setiap pembaruan; baris yang hilang tidak akan ditambahkan skrip.
5. **Jangan menambahkan `database.default.DSN` ke `.env`.** Kunci itu disisihkan untuk akun migrasi (bagian berikut).
5b. **`database.default.hostname = 127.0.0.1`, never `localhost`** (21 November 2026). On Windows `localhost` is tried as IPv6 `::1` first; MySQL listens on `127.0.0.1` only (`bind-address`), so every request waits about 2.6 s for the fallback. Change the value if the template says `localhost`.
5c. *(New, English; 21 November 2026.)* `session.regenerateDestroy` is no longer a setting. A `.env` made from an older template still has `session.regenerateDestroy = true`; the line is ignored (`Config\Session` keeps it false) and may be deleted. With `true`, a student was sent to `/masuk` or a batch of events got 401 whenever the session ID was replaced (every 5 minutes) while a second request was on its way (`docs/02` §7.2).
5a. *(New, English.)* Every production difference from the repository (`app.forceGlobalSecureRequests`, `app.CSPEnabled`, `cookie.secure`) is set in `.env`. Do not edit `app/Config/App.php` or `app/Config/Cookie.php` on the server: `deploy.ps1` runs `git pull --ff-only`, which stops on a locally edited tracked file that the update also changes. `env-example-production` is the gelita.id copy of these values.
6. *(New, English.)* Optional: `gelita.eventFlush.intervalMs` (default `30000`) and `gelita.eventFlush.maxEvents` (default `40`, at most `maxEventsPerBatch` = 50) set how often the game sends its event batches (`Config\Gelita::$eventFlush`, rencana-log-asinkron §5.1). Lowering the interval makes Ringkasan's "Aktivitas terbaru" fresher at the cost of more requests; the note under that list follows the value. Leave both out to keep the defaults.
7. *(New, English.)* Optional: `gelita.screenFx = false` turns off the WebGL screen effects of the narrated scenes (three.js fog, sparks, light rays, bloom, and parallax; on by default). The scenes then play the CSS effects only. Use it if a class's devices stutter on the story screens; the browser already falls back by itself without WebGL 2, under reduced motion, and when frames stay slow. With package mode on, rebuild the package after changing it (**Bangun ulang paket**) only if the three.js files changed; the switch itself needs no rebuild.
8. Perubahan `.env` langsung berlaku di request berikutnya. Jangan menjalankan `php spark optimize`: perintah itu mengubah `app/Config/Optimize.php` (berkas yang dilacak git) dan mengaktifkan cache konfigurasi yang membekukan nilai `.env`, termasuk `gelita.assetVersion`.

### Kredensial migration lewat DSN

Akun di `.env` (`gelita_app`) tidak punya hak DDL. Untuk `migrate`, `migrate:rollback`, dan `gelita:reset`, akun migrasi dipasang lewat variabel environment `database_default_DSN`:

- Variabel environment **tidak dapat** menimpa kunci yang sudah ada di `.env`. `DotEnv` mengisi `$_ENV` dari `.env`, dan `BaseConfig::getEnvValue()` memeriksa `$_ENV` lebih dulu. Karena itu `database_default_username=…` maupun `env "database.default.username=…"` tetap menghasilkan `gelita_app`, dan `DB_USER` tidak pernah dibaca CodeIgniter. Tanpa DSN, migration gagal dengan `CREATE command denied to user 'gelita_app'@'localhost'`.
- Yang bekerja adalah kunci yang **tidak** ada di `.env`: `database_default_DSN`. CodeIgniter mengurai DSN dan menimpa host, pengguna, sandi, serta nama database.

Isi `migrate.dsn` (satu baris, di luar repositori):

```
MySQLi://gelita_migrate:SANDI_URL_ENCODED@127.0.0.1/gelita
```

- **Sandi di-URL-encode** lebih dulu: `php -r "echo rawurlencode('Migr@te/2026'), PHP_EOL;"` menghasilkan `Migr%40te%2F2026`.
- **Tanpa port.** CodeIgniter 4.7.4 meneruskan port dari DSN sebagai string, dan `mysqli::real_connect()` menolaknya dengan `TypeError`. Tanpa port, 3306 dipakai.
- **Host `127.0.0.1`.** Never `localhost` (see [Akun database](#akun-database)).

Pemakaian (skrip deploy melakukannya otomatis):

```powershell
$env:database_default_DSN = (Get-Content C:\gelita-ops\migrate.dsn -Raw).Trim()
php spark migrate
Remove-Item Env:\database_default_DSN
```

Cara yang sama berlaku untuk `migrate:rollback`, `gelita:reset`, dan database scratch saat pengembangan (ganti nama database di akhir DSN).

### Berkas operasional di luar repositori

Rahasia dan nilai khusus server tidak pernah masuk repositori. Skrip di `deploy/` juga tidak diedit di server, karena editan lokal membuat `git pull` bentrok.

The files live in `C:\gelita-ops\` (ACL in [Model pengguna dan izin](#model-pengguna-dan-izin)):

| Berkas | Isi |
|---|---|
| `backup.cnf` | opsi klien MySQL untuk `gelita_backup` |
| `migrate.dsn` | DSN `gelita_migrate` |
| `config.psd1` | path aplikasi, PHP, MySQL, folder backup, branch |

`backup.cnf`:

```ini
[client]
user=gelita_backup
password="SANDI_BACKUP"
host=127.0.0.1
```

`--defaults-extra-file` harus menjadi opsi **pertama** `mysql`/`mysqldump`; skrip sudah mematuhinya. Opsi ini juga menghindari sandi di baris perintah, yang terlihat di daftar proses.

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

Optional: `KeduaHari = 30` makes each backup run delete the files older than 30 days at the second location (`Kedua`). Without it they are kept forever (see [Research install checklist](#research-install-checklist-before-children-first-use-their-accounts)).

### Nginx

The site has one Nginx file, [`deploy/windows/nginx/gelita.id.conf`](../deploy/windows/nginx/gelita.id.conf). It goes to `C:\laragon\etc\nginx\sites-enabled\gelita.id.conf` and already names the domain, `gelita.id`; nothing to replace (owner, 21 November 2026). Nginx listens on port 80 of `127.0.0.1` only and serves no TLS: Cloudflare ends HTTPS, and `cloudflared` on the same machine passes each request on (see [HTTPS](#https)).

Laragon membuat vhost `auto.<folder>.test.conf` untuk setiap folder di `C:\laragon\www`. Vhost itu **tidak** layak untuk production: `autoindex on`, `TLSv1 TLSv1.1` dengan cipher `RC4`/`EXP`, tanpa header keamanan, setiap `.php` dieksekusi, dan ditulis ulang setiap Laragon start.

Langkahnya:

1. Preferences → *General*: matikan **Auto virtual hosts**. Hapus `C:\laragon\etc\nginx\sites-enabled\auto.gelita.test.conf` bila sudah terbentuk.
2. Preferences → *Services & Ports*: aktifkan **Nginx** dan **MySQL**, matikan **Apache** (keduanya berebut port 80).
3. Salin `deploy\windows\nginx\gelita.id.conf` ke `C:\laragon\etc\nginx\sites-enabled\`. Nama berkas **tidak boleh** berawalan `auto.`. Copy it as it is, and remove any other GELITA site file there.
4. Test the configuration first, in PowerShell: `cd (Get-ChildItem C:\laragon\bin\nginx -Directory | Select-Object -Last 1).FullName; .\nginx.exe -t`. It must end with `test is successful`; otherwise it names the file and line that fail. While the test fails, Nginx does not start and Cloudflare answers every page with 502. Then Menu → *Nginx* → *Reload*, and check on the server that Nginx answers: `curl.exe -sI -H "Host: gelita.id" http://127.0.0.1/` shows a status line and `Strict-Transport-Security`.

Aturan di `gelita.id.conf`:

| Aturan | Isi di berkas | Alasan |
|---|---|---|
| Listen | `listen 127.0.0.1:80`, `server_name gelita.id` | only `cloudflared` on this machine connects. Other machines cannot reach Nginx directly, where they could claim any address in `CF-Connecting-IP` |
| Visitor address | `set_real_ip_from 127.0.0.1`, `real_ip_header CF-Connecting-IP` | without it every student seems to come from `127.0.0.1`: login throttling and the hashed address in the logs would treat a whole class as one visitor |
| Header keamanan | `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `Strict-Transport-Security`, semuanya `always`, **hanya** di blok `server` | `add_header` di dalam `location` menghapus semua header warisan `server` |
| Header ganda | `fastcgi_hide_header` untuk `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy` | filter `secureheaders` CodeIgniter mengirim header yang sama; setiap header muncul tepat sekali |
| Cache aset | `map` `$gelita_is_asset` + `$arg_v` → `$gelita_cache`: URL aset ber-`?v=` mendapat `public, max-age=31536000, immutable`, aset lain `no-cache`; asset package URLs (`/assets/p/`, `/assets/k/`, `/paket/pkt-*.json`) are immutable without `?v=` | lihat [Cache-busting](#cache-busting) dan [Asset package](#asset-package-download-once-mode) |
| PHP | hanya `location = /index.php` yang diteruskan ke `php_upstream` (`fastcgi_read_timeout 300`); `.php` lain → 404 | tidak ada skrip lain yang dapat dieksekusi |
| FastCGI parameters | `include fastcgi.conf` (Nginx's own, in `conf\` of every Nginx build), not Laragon's `snippets/fastcgi-php.conf` | that snippet sets `fastcgi_read_timeout` itself, and a second one stops Nginx from starting (`"fastcgi_read_timeout" directive is duplicate`, Laragon Nginx 1.30.4, 21 November 2026). Dropping our line instead would leave data downloads to Laragon's value, below the 300 s they may need |
| HTTPS for PHP | `fastcgi_param HTTPS on` | Nginx receives plain HTTP, but the visitor is on HTTPS: secure cookies and `https://` links depend on it |
| Routes without a file | `location /` uses `try_files $uri $uri/ /index.php$is_args$args`; only `/assets/` answers 404 for a missing file | `/manifest.json` has no file in `public/` (it is made by `AppController`), so it reaches `index.php`. Never put a static `public/manifest.json` back: it would shadow the route. Apache does the same through `public/.htaccess` (`!-f`, `!-d` → `index.php`) |
| Berkas tersembunyi | `location ~ /\.` → 404, juga di dalam `/assets/` | `.env`, `.git`, `.htaccess`, `.gitkeep` tidak pernah dilayani |
| Mode pemeliharaan | `if (-f "C:/laragon/www/gelita/writable/pemeliharaan.flag") { return 503; }` + halaman 503 dwibahasa | lihat [Mode pemeliharaan](#mode-pemeliharaan) |
| Unggahan | `client_max_body_size 72M` | sejalan dengan `post_max_size` |
| Kompresi | `gzip` untuk CSS, JavaScript, JSON, SVG ≥ 1024 byte | |

Path Windows ditulis dengan garis miring biasa. The lines that belong to the tunnel setup and to Laragon:

```nginx
server {
    listen 127.0.0.1:80;                        # only cloudflared on this machine connects
    server_name gelita.id;

    set_real_ip_from 127.0.0.1;                 # the visitor's address, from cloudflared's header
    real_ip_header CF-Connecting-IP;

    root "C:/laragon/www/gelita/public";

    if (-f "C:/laragon/www/gelita/writable/pemeliharaan.flag") {
        return 503;
    }

    location = /index.php {
        include fastcgi.conf;                   # Nginx's own, not Laragon's snippets/fastcgi-php.conf
        fastcgi_pass php_upstream;              # didefinisikan Laragon (etc/nginx/php_upstream.conf)
        fastcgi_read_timeout 300;               # data downloads run up to 300 s
        fastcgi_param HTTPS on;                 # the visitor is on HTTPS (secure cookies, https:// links)
        …
    }
}
```

`php_upstream` menunjuk proses php-cgi yang dijalankan Laragon (port FastCGI bawaan Laragon 8.6+: 10987).

- **Cache rules matter behind Cloudflare.** The two `map` blocks above `server {` and the line `add_header Cache-Control $gelita_cache;` set the cache header of every asset. A hand-written tunnel site file without them (the measured server had none) lets Cloudflare give `/assets/p/`, `/assets/k/` and `/paket/pkt-*.json` its own default (4 hours in the browser) instead of `public, max-age=31536000, immutable`, so devices fetch the asset package again and again. A server that still runs such a file only needs those two parts added: the two `map` blocks above `server {`, and the `add_header Cache-Control $gelita_cache;` line next to the other `add_header` lines.
- Check from another machine after the reload: ask for one asset package file twice with `curl -sI` (a URL under `/assets/p/` from the package manifest). It must answer `cache-control: public, max-age=31536000, immutable`, and `cf-cache-status: HIT` the second time. Step 4 of [Verifikasi Setelah Deploy](#verifikasi-setelah-deploy) checks the other asset kinds.
- Loaded once into nginx 1.24 on Linux, with only the root path and the port changed (21 November 2026): the syntax is accepted, versioned assets answer `immutable`, unversioned ones `no-cache`, asset package paths `immutable`, the four security headers are sent, hidden files and stray `.php` files answer 404, and the maintenance flag gives the 503 page. On Laragon's Nginx 1.30.4 on Windows the first version of this file failed `nginx -t` because it included Laragon's `snippets/fastcgi-php.conf` (see the *FastCGI parameters* row above); Nginx did not start and Cloudflare answered every page with 502 (21 November 2026). The fixed `location = /index.php` block (`include fastcgi.conf`) was run on nginx 1.24 on Linux with `php-cgi` behind `php_upstream`: `index.php` gets `HTTPS=on`, the right `SCRIPT_FILENAME`, the query string and the visitor's address from `CF-Connecting-IP`, and `nginx -t` passes with a snippet that sets its own timeout. Still checked only on the server: the drive-letter paths and `php_upstream`. After copying it, run `.\nginx.exe -t` in Laragon's Nginx folder (step 4 above) before you reload, then run the verification list.

### HTTPS

Cloudflare ends HTTPS. `cloudflared` on the server passes each request to Nginx on port 80 without TLS. The server holds no certificate and opens no port to the network, so there is nothing to issue or renew on it.

1. In the tunnel's settings (Cloudflare Zero Trust → *Networks* → *Tunnels* → *Public hostname*, or `config.yml`) the service is `http://127.0.0.1:80`. Not `localhost`: Windows tries `::1` first, as with the database host.
2. Cloudflare → *SSL/TLS*: mode **Full (strict)** works with a tunnel.
3. `.env` keeps `app.baseURL = 'https://gelita.id/'`, `app.forceGlobalSecureRequests = true` and `cookie.secure = true` (see [`.env` production](#env-production)).
4. Nginx tells PHP that the visitor is on HTTPS (`fastcgi_param HTTPS on`) and sends the `Strict-Transport-Security` header itself (see [Nginx](#nginx)).

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

```powershell
php spark gelita:library:thumbnails
php spark gelita:library:thumbnails --force                   # unduh ulang semua, mis. setelah video diganti
```

Ringkasannya "n terisi, n dilewati, n gagal"; kode keluar 1 bila ada yang gagal. Server tanpa internet sama sekali dapat mengisi poster secara manual di `/admin/konten/pustaka/{level_id}` (kolom poster tiap video).

**Perangkat siswa** tidak pernah meminta thumbnail ke domain luar. Mereka memuat gambar Pustaka dari tautan langsung dari domain asalnya (`commons.wikimedia.org`, yang mengalihkan ke berkas aslinya), dan baru menghubungi penyedia video (YouTube lewat `youtube-nocookie.com`) setelah menekan Putar. Jaringan kelas yang memblokir domain itu membuat gambar/video Pustaka tidak tampil; unggah salinan gambarnya lewat panel bila perlu.

### Mode pemeliharaan

CodeIgniter 4.7 tidak punya `php spark down`/`up` (keduanya "Command not found", kode keluar 1). Mode pemeliharaan GELITA bekerja di tingkat Nginx, tanpa kode aplikasi:

| | PowerShell |
|---|---|
| aktifkan | `New-Item -ItemType File -Force C:\laragon\www\gelita\writable\pemeliharaan.flag` |
| matikan | `Remove-Item C:\laragon\www\gelita\writable\pemeliharaan.flag` |

Selama berkas ada, semua halaman dan API menjawab **503** dengan pesan dwibahasa, sedangkan `php spark` tetap berjalan. Berkas penanda dipasang oleh `deploy.ps1` dan oleh `gelita:reset --pemeliharaan`, dan diabaikan git (`.gitignore`). `api.js` memperlakukan 503 non-JSON sebagai galat jaringan, sehingga `events.js` menyimpan event ke antrean lokal dan mengirimnya lagi setelah situs kembali. Event dari tab yang ditutup selama pemeliharaan tetap dapat hilang (`sendBeacon` tidak tahu hasilnya), sehingga aturan "jangan memperbarui saat kelas berjalan" tetap berlaku.

---

## Instalasi baru

### Berkas operasional di repositori

| Berkas | Isi |
|---|---|
| `deploy/windows/deploy.ps1` | pembaruan kode: cek kelas aktif → backup → pemeliharaan → pull → composer → migrate (DSN) → daftar sekolah → cache → salinan ringkas JS/CSS → `assetVersion` → verifikasi → buka → paket aset |
| `deploy/windows/backup.ps1` | backup database (tanpa isi `ci_sessions`), unggahan, paket aset, `.env`; simpan 30 hari; salinan kedua |
| `deploy/windows/retensi.ps1` | daily retention run (`gelita:retention:run`) and log rotation |
| `deploy/windows/config.example.psd1` | contoh `config.psd1` |
| `deploy/windows/nginx/gelita.id.conf` | the one Nginx site file (port 80 on `127.0.0.1`, no certificate) |
| `.gitignore` | `/public/assets/uploads/*`, `!/public/assets/uploads/.gitkeep`, `/writable/pemeliharaan.flag` |
| `tests/unit/DeployFilesTest.php` | mengunci cacat yang ditemukan saat skrip dijalankan (lihat [Catatan Implementasi Tahap 8](#catatan-implementasi-tahap-8)) |

The `.ps1` scripts run on Windows PowerShell 5.1 and hold **ASCII only**.

Instalasi juga membaca isi bawaan dari repositori, jadi folder `docs/` dan `app/Database/Seeds/data/` **tidak boleh** dibuang dari server:

| Berkas | Dibaca oleh | Isi |
|---|---|---|
| `app/Database/Seeds/data/sekolah-jateng.csv` | `SchoolSeeder`, `gelita:schools:import` | 28.487 sekolah resmi Jawa Tengah (NPSN), dari [`docs/sekolah/`](sekolah/README.md) |
| `app/Database/Seeds/data/story.php` | `ContentSeeder` | 91 baris cerita (salinan [`docs/naskah-cerita.md`](naskah-cerita.md)) |
| `docs/pustaka/gelita-pustaka.xlsx` | `KontenKelas5Seeder` | Pustaka Kedu: 30 halaman, 48 media ([`docs/pustaka/`](pustaka/README.md)) |
| `docs/bank-soal/gelita-bank-soal-kelas5.xlsx` | `KontenKelas5Seeder`, `gelita:reset` | bank soal kelas 5: 12 tantangan, 147 soal aktif ([`docs/bank-soal/`](bank-soal/README.md)) |
| `public/assets/audio/narasi/{id,en}/` (kosong di repositori) | `gelita:narration:import` | folder impor rekaman pengisi suara, 103 per bahasa ([`docs/audio.md`](audio.md)) |

### Ringkasan alur

Instalasi baru adalah satu-satunya cara memasang GELITA. Urutannya:

| # | Langkah | Perintah | Akun database |
|---:|---|---|---|
| 1 | Kode dan `.env` | `composer install --no-dev --optimize-autoloader`, `Copy-Item env .env`, `php spark key:generate` | — |
| 2 | Skema | `php spark migrate` | `gelita_migrate` (DSN) |
| 3 | Isi awal | `php spark db:seed DatabaseSeeder`, with `$env:GELITA_ADMIN_PASSWORD` set | `gelita_app` |
| 3a | Test module content *(new, English)* | `php spark gelita:instrumen:impor` (tests, questionnaire, attitude scale; `db:seed` does not load them, and `/tes` stays unusable without them) | `gelita_app` |
| 4 | Narasi | `php spark gelita:narration:install` (narasi cadangan ElevenLabs dari `ai-studio/elevenlabs-voice/out/`), `php spark gelita:narration:import`, lalu setujui di panel | `gelita_app` |
| 4a | Team pictures *(new, English)* | `php spark gelita:katalog:impor` (see [Installing the Katalog Aset images](#installing-the-katalog-aset-images)) | `gelita_app` |
| 5 | Verifikasi konten | `php spark gelita:content:verify` | `gelita_app` |
| 6 | Gambar asli | ganti 66 gambar contoh lewat panel, sampai daftar siap penelitian hijau | — |
| 7 | Uji asap | [Verifikasi Setelah Deploy](#verifikasi-setelah-deploy) | — |
| 8 | Sebelum pengambilan data | `php spark gelita:reset`, lalu `php spark gelita:content:verify --siap-penelitian` | `gelita_migrate` (DSN) |

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
| `ContentSeeder` | 12 tantangan (4 per wilayah: Susun Gambar, Isi Rumpang, Benar atau Salah, Cari Objek) dan 91 baris cerita |
| `KontenKelas5Seeder` | impor Pustaka Kedu (30 halaman, 48 media), impor bank soal kelas 5 (126 soal: 108 dinilai + 18 pengecoh, 30 variasi, 9 bacaan Benar atau Salah, 36 petunjuk), lalu 66 gambar contoh (mockup) |

Semua seeder aman dijalankan ulang: baris yang sudah ada tidak ditimpa, dan sandi `admin` yang sudah ada tidak diubah ("Akun admin sudah ada — kata sandi tidak diubah"). Kedua impor dicatat di audit log atas nama akun admin pertama. Gambar contoh ditulis ke `public/assets/uploads/mockup/` dan poster video Pustaka ke `public/assets/uploads/` (lihat [Akses keluar server](#akses-keluar-server-thumbnail-video-pustaka)).

### Langkah instalasi

**0. Windows** (PowerShell sebagai Administrator):

```powershell
Set-TimeZone -Id 'SE Asia Standard Time'          # (UTC+07:00) Bangkok, Hanoi, Jakarta
powercfg /change standby-timeout-ac 0
powercfg /change hibernate-timeout-ac 0
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
# 4. Skema — akun migrasi lewat DSN
$env:database_default_DSN = (Get-Content C:\gelita-ops\migrate.dsn -Raw).Trim()
php spark migrate
Remove-Item Env:\database_default_DSN

# 5. Isi awal — akun aplikasi
$s = Read-Host 'Sandi admin pertama' -AsSecureString
$env:GELITA_ADMIN_PASSWORD = [Runtime.InteropServices.Marshal]::PtrToStringBSTR(
    [Runtime.InteropServices.Marshal]::SecureStringToBSTR($s))
php spark db:seed DatabaseSeeder
Remove-Item Env:\GELITA_ADMIN_PASSWORD

# 5a. Test module content (tests, questionnaire, attitude scale); not part of db:seed
php spark gelita:instrumen:impor

# 6a. Narasi cadangan ElevenLabs (ai-studio/elevenlabs-voice/out/) → folder narasi + produksi.json (tercatat sintetis; rekaman pengisi suara yang sudah ada tidak ditimpa)
php spark gelita:narration:install --dry-run
php spark gelita:narration:install

# 6b. Rekaman narasi di public/assets/audio/narasi/{id,en}/ → audio draft (folder kosong: dilewati; rekaman juga dapat diunggah di panel)
php spark gelita:narration:import --dry-run
php spark gelita:narration:import

# 6c. Team pictures from ai-studio/game-images/ (see "Installing the Katalog Aset images")
php spark gelita:katalog:impor

# 7. Verifikasi konten — harus 0 galat (peringatan gambar contoh boleh)
php spark gelita:content:verify
```

Sintaks `GELITA_ADMIN_PASSWORD='…' php spark …` adalah sintaks Bash; di PowerShell maupun cmd sintaks itu galat. Jalankan perintah di atas di **PowerShell**, bukan di terminal Cmder bawaan Laragon.

- `Read-Host -AsSecureString` membuat sandi admin tidak tercatat di riwayat shell.
- Keluaran langkah 5 menyebut jumlah sekolah resmi, "Akun admin dibuat (username: admin)", hasil impor Pustaka Kedu dan bank soal kelas 5, lalu "Gambar contoh: 66 dibuat, …". Peringatan berwarna kuning (berkas workbook tidak ditemukan, impor gagal, gambar contoh gagal) harus diselesaikan sebelum lanjut; seeder aman diulang.
- Langkah 6 memetakan 103 rekaman per bahasa (91 baris cerita + 12 petunjuk Cari Objek) ke baris yang dibuat langkah 5, jadi urutannya wajib. Rekaman masuk sebagai **draft** dan belum terdengar pemain sampai disetujui di panel (Langkah terakhir).

**8. Nginx dan HTTPS:** lihat [Nginx](#nginx) dan [HTTPS](#https).

**9. Tugas terjadwal dan uji pulih:** lihat [Tugas Terjadwal](#tugas-terjadwal) dan [Backup](#backup).

**10. Panel:** lihat [Langkah terakhir](#langkah-terakhir).

### Langkah terakhir

Setelah situs terbuka lewat HTTPS:

1. **Ganti sandi admin.** Sandi awal pernah diketik di terminal. Masuk di `/admin/login` sebagai `admin`, buka **Ganti kata sandi** di menu akun kanan atas (`/admin/akun/sandi`), dan ganti dengan sandi baru minimal 12 karakter yang disimpan di pengelola sandi.
2. **Rekam dan setujui narasi.** Buka **Rekaman narasi** (`/admin/konten/narasi`), unduh naskah PDF untuk pengisi suara, unggah rekamannya, dengarkan, lalu tekan **Setujui semua rekaman …** untuk bahasa Indonesia dan Inggris. Server yang masih menyimpan rekaman TTS lama: tekan dulu **Batalkan semua rekaman …** per bahasa ([`docs/audio.md`](audio.md) → *Voice-over narration*). Rekaman draft tidak pernah terdengar pemain. Rekaman satu per satu juga dapat disetujui di **Gambar & mockup → Rekaman suara** (`/admin/media/audio`).
3. **Buat akun guru.** Satu akun `guru` per guru di **Staf** (`/admin/staf`), dengan **NPSN sekolahnya** (cari di **Sekolah**, `/admin/sekolah`). Guru dan siswanya lalu menunjuk baris sekolah resmi yang sama, sehingga cakupan data guru pasti lengkap. Sandi awal yang Anda pilih wajib diganti guru saat pertama masuk.
4. Jalankan [Verifikasi Setelah Deploy](#verifikasi-setelah-deploy).

Sesudah itu aplikasi siap untuk **uji coba**. Ke-66 gambar tantangan masih gambar contoh bertanda "GAMBAR CONTOH"; permainan tetap berjalan dengan gambar itu.

### Installing the Katalog Aset images

*(New section, English.)* The team catalogue (`ai-studio/game-images/` since 15 November 2026, before that `gelita-katalog/`; in the repo, not under `public/`) holds the current art: logo, MULAI/START buttons, app icons, backgrounds, story slide backgrounds, maps, badges, pins, lanterns, avatars, character poses, the 66 challenge images, and Pustaka illustrations. One command installs all of it into the game's image slots:

```powershell
php spark gelita:katalog:impor --dry-run   # lists what would be installed
php spark gelita:katalog:impor             # install
```

- Run it after the installation above (the story slides, Pustaka pages, and challenge slots it links to must exist). It is safe to run again: images already installed from the catalogue and images an admin uploaded are skipped. `--timpa` replaces them too; `--sumber=FOLDER` reads another copy of the catalogue.
- Every image is re-encoded as lossy WebP (about 250 KB per background instead of the catalogue's 2 MB lossless files) and stored in `public/assets/uploads/` under its official name, so it is in the backup and can be replaced in **Gambar & mockup** at any time. App icons are stored as PNG. Installed rows carry the credit "Katalog Aset GELITA (gambar sementara)", which disappears once an admin uploads a new file.
- Story slide backgrounds are linked to their slide, Pustaka illustrations replace the Commons links (re-importing `docs/pustaka/gelita-pustaka.xlsx` restores the links), video covers already downloaded by `gelita:library:thumbnails` are kept, and each region's four challenge buttons are moved onto the clearings of the catalogue's region map.
- The catalogue has one still picture for each of the ten character poses (red-scarf Jaka and Mbah Kedu; the 24 frames per pose of 12 October 2026 were cut to frame 1 on 20 November 2026, owner: still pictures only). Every run removes the old frames 2 to 24 of a pose from the server (rows and uploaded files; *dihapus* in the report, *akan dihapus* in a dry run), whoever installed them. The Cari Objek objects sit on the tables and shelves of the catalogue scenes (question bank layout of 12 October 2026). On a server whose bank still has the old positions, the command moves every object that is still at its old position, as long as the installed scene is the catalogue one; an object an admin placed elsewhere stays (`--timpa` moves it too). Re-importing the bank (`gelita:bank:import`) gives the same positions, but resets every admin placement to the bank.
- **After a new catalogue edition** (pull the repo first), run the command again. An image installed from an earlier edition is reinstalled when its catalogue file changed and skipped when it is the same; admin uploads stay.
- The two round language icons of the HUD switch come from the catalogue references `reference.20` (ID) and `reference.19` (EN) and are stored as `ui.lang.id`/`ui.lang.en`. Until both exist the switch shows the text ID / EN.
- **Old art kept as an admin upload** (e.g. an old lurik Jaka pose or an older language icon uploaded in the panel before the catalogue existed) is never replaced by a plain run. The report says "sudah ada gambar unggahan admin" for such a slot. Replace only those slots with `--kunci` (comma-separated, `*` allowed), dry run first:

  ```powershell
  php spark gelita:katalog:impor --timpa --kunci="ui.lang.*,char.jaka.*" --dry-run
  php spark gelita:katalog:impor --timpa --kunci="ui.lang.*,char.jaka.*"
  ```

- **Retired slots** (`CatalogImporter::RETIRED_SLOTS`, since 20 November 2026 the lettered start-button pictures `ui.btn-start` / `ui.btn-start.en`): every run removes their `media_assets` rows and their files in the upload folder, admin uploads included, so the Media page no longer lists them and the next asset package no longer carries them. A dry run lists them as *akan dihapus*. When the active release uses an asset package, build and publish a new one afterwards.

- **Susun Gambar edition of 14 October 2026.** The catalogue has all 30 puzzle images (variants a–j, 1200 × 1200, full-bleed daylight scenes): 21 new d–j images and four redrawn ones (`challenge.item.tmg-1-01`, `tmg-1-03`, `mgl-1-01`, `wnb-1-07`; the old night or white-background versions failed or nearly failed the tile check). A plain run fills the d–j slots and replaces the four when the old version came from the catalogue. If an admin uploaded one of the four by hand, the report says "sudah ada gambar unggahan admin"; replace just those four:

  ```powershell
  php spark gelita:katalog:impor --timpa --kunci="challenge.item.tmg-1-01,challenge.item.tmg-1-03,challenge.item.mgl-1-01,challenge.item.wnb-1-07" --dry-run
  php spark gelita:katalog:impor --timpa --kunci="challenge.item.tmg-1-01,challenge.item.tmg-1-03,challenge.item.mgl-1-01,challenge.item.wnb-1-07"
  ```

  The slot notes (`media_note`) of the four redrawn slots are not changed by either command; edit them in **Gambar & mockup** if the bank's note differs.
- When a release uses an asset package, build a new package afterwards (`gelita:paket:bangun`) so devices get the new pictures.

### The public Katalog Aset site (katalog.gelita.id)

*(New section, English, 17 November 2026.)* <https://katalog.gelita.id/> shows every picture, music track, sound effect and narration of the game with where it is used and its review status. It is a static page: the folder `gelita-katalog/` of a repository checkout (about 300 KB), generated by `php spark gelita:katalog:halaman` in a development session and committed (`gelita-katalog/README.md`). It holds no picture or sound: every entry is loaded from where the game serves it on <https://gelita.id/>, so what the catalogue can show depends on what that install has (`gelita:katalog:impor`, `gelita:narration:install` and `gelita:narration:import`, `gelita:sound:install`, a deploy of `public/assets/`). Missing files show *Belum ada di gelita.id*; `php spark gelita:katalog:halaman --cek-tautan` lists them. The server never builds the page and needs no PHP, database or `spark` for it.

On the server that serves it:

1. Update the checkout the web server points at: `git pull` in that checkout (the site has no other step; `gelita:setelah-pull` does not list it).
2. nginx: the `server` block of `katalog.gelita.id` serves the folder itself, with `index.html` as its index. A 403 on `/` means the index line is missing or the folder is empty:

   ```nginx
   server {
       server_name katalog.gelita.id;
       root /path/to/checkout/gelita-katalog;
       index index.html;
       location / { try_files $uri $uri/ =404; }
   }
   ```

   Then reload Nginx (Menu → *Nginx* → *Reload*).
3. Cloudflare: purge the cache of `katalog.gelita.id` (or *Purge Everything* for the zone) after each pull that changed the folder, so visitors do not keep the old `data.js`. Nothing on gelita.id blocks the page: images and audio need no CORS, and the page sets no Content Security Policy.

On 17 November 2026: 400 pictures, 15 music tracks, 6 sound effects, 187 narration lines; 757 of the 762 linked files were on gelita.id (the 5 missing are Pustaka video covers not installed there).

Limits: a slot re-uploaded in the panel as PNG or JPG, or a narration take uploaded in the panel, is not the file the catalogue links (it links the importer's `.webp` and the installed take), and links to `assets/uploads/` and `assets/audio/` carry no `?v=`, so purge the Cloudflare cache of gelita.id after replacing a picture or take there.

### Sebelum pengambilan data penelitian

Urutan wajib sebelum siswa penelitian pertama mendaftar: narasi disetujui ([Langkah terakhir](#langkah-terakhir) butir 2) → gambar asli → daftar siap penelitian hijau → uji asap → `gelita:reset`.

**1. Ganti gambar contoh dengan gambar asli.** Ada 66 slot gambar tantangan: 30 gambar Susun Gambar (variasi a–j), 3 gambar pernyataan Benar atau Salah, 3 adegan Cari Objek, dan 30 benda Cari Objek. Unggah gambar asli lewat **Gambar & mockup** (`/admin/media`; saring status "Gambar contoh", lalu **Unggah gambar asli** per slot) atau editor tantangan (`/admin/konten/node/{id}`, bagian "Gambar & suara tantangan"); daftar yang belum beres ada di **Kelengkapan aset** (`/admin/media/kelengkapan`). Ukuran wajib tiap slot ada di `Config\Gelita::$assetSizes` dan ditegakkan saat unggah. Gambar yang diunggah otomatis tercatat sebagai gambar asli (`media_assets.is_placeholder = 0`) dan tidak pernah ditimpa `gelita:mockups` maupun `gelita:reset`. Status per slot:

```powershell
php spark gelita:mockups --dry-run
php spark gelita:mockups --dry-run --node=tmg-4
```

**2. Daftar siap penelitian hijau.** Buka **Penelitian** (`/admin/studi`). Kotak **Daftar siap penelitian** (`App\Libraries\ResearchReadiness`) harus berlabel "siap": tidak ada gambar contoh aktif, semua rekaman narasi disetujui, dan verifikasi konten siap penelitian tanpa galat. Selama belum hijau, **Ringkasan** (`/admin`) menampilkan peringatan yang sama. Studi bawaan `STUDI-KEDU-KELAS5-2026` sudah berstatus Berjalan sejak instalasi (dan sesudah `gelita:reset`), jadi daftar ini **tidak** memblokir pendaftaran siswa; panel hanya menolak *mengubah* status studi menjadi Berjalan selama daftar belum hijau, kecuali "Tetap jalankan" dicentang. Periksa daftarnya sendiri.

**3. Uji asap.** Jalankan seluruh [Verifikasi Setelah Deploy](#verifikasi-setelah-deploy) (pemeriksaan otomatis dan uji manual) pada konten akhir: gambar asli, narasi disetujui. Siswa dan data uji yang dibuat di sini dikosongkan pada langkah berikutnya.

**4. Reset data uji coba.** Setelah uji coba dan uji asap selesai, kosongkan semua data uji coba dengan `gelita:reset`, yang dijalankan dengan akun migrasi:

```powershell
# sebagai akun gelita
Set-Location C:\laragon\www\gelita
# data yang dikosongkan tidak dapat kembali
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
| Data penelitian & uji coba | `participants`, `game_sessions`, `participant_progress`, `challenge_attempts`, `item_responses`, `item_tries`, `audio_usage_events`, `game_event_logs`, `device_logs`, `participant_feedback`, `data_exports`, `data_deletion_requests`, `audit_logs`, `ci_sessions` | dikosongkan (`TRUNCATE`; berkas di `writable/exports/` ikut dihapus) |
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

Kode baru dibawa ke server hanya lewat `deploy\windows\deploy.ps1`. Jangan pernah memperbarui saat ada kelas berjalan. Skrip memeriksa sesi `active` dalam 10 menit terakhir dan **membatalkan diri** bila ada; `-Paksa` hanya untuk keadaan darurat.

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File C:\laragon\www\gelita\deploy\windows\deploy.ps1
```

Langkah skrip:

| # | Langkah | `deploy.ps1` |
|---:|---|---|
| 1 | Periksa kelas aktif | `mysql.exe` dari `config.psd1` dengan `--defaults-extra-file=backup.cnf`: `game_sessions` `status = 'active'` dengan `last_active_at` ≤ 10 menit → BATAL, kode 1 |
| 2 | Backup | `backup.ps1`; kode 2 (hanya salinan kedua gagal) → peringatan, deploy dilanjutkan |
| 3 | Mode pemeliharaan | `New-Item` memasang `writable\pemeliharaan.flag`; blok `try/catch` mencetak pesan pemulihan |
| 4 | Ambil kode | `git pull --ff-only origin $c.Branch` |
| 5 | Dependency | `composer install --no-dev --optimize-autoloader --no-interaction` |
| 6 | Migration | `php spark migrate` dengan `$env:database_default_DSN` dari `migrate.dsn`, dihapus di `finally` |
| 7 | Daftar sekolah resmi | `php spark gelita:schools:import` (hanya baris yang berubah) |
| 8 | Cache | `php spark cache:clear` |
| 9 | Salinan ringkas JS/CSS | `php spark gelita:aset:minify` (see [Minified JS/CSS](#minified-jscss)), inside `try/catch`; a failure only prints a warning, the site keeps the original files |
| 10 | Versi aset | `[IO.File]::WriteAllText` menaikkan `gelita.assetVersion` di `.env` (UTF-8 tanpa BOM) |
| 11 | Verifikasi konten | `php spark gelita:content:verify` |
| 12 | Buka kembali | hapus `pemeliharaan.flag` |
| 13 | Asset package (new, 9 November 2026) | `php spark gelita:paket:bangun --deploy`, inside `try/catch`: nothing while package mode is off; otherwise build and publish, or only build while a study runs (see [Admin switch](#admin-switch-rilis--paket-aset)); a failure only prints a warning, the site is already open |

The script has no PHP reload step: opcache checks the file timestamps (`opcache.validate_timestamps = 1`, see [Pengaturan PHP](#pengaturan-php)).

Bila langkah apa pun gagal, termasuk verifikasi konten, situs **tetap** dalam mode pemeliharaan dan skrip mencetak "DEPLOY GAGAL - situs tetap dalam mode pemeliharaan": lebih baik 503 yang jelas daripada kelas yang membuka konten rusak. Perbaiki penyebabnya, lalu hapus berkas penanda. Verifikasi di sini tanpa `--siap-penelitian`, jadi gambar contoh tidak menghalangi pembaruan.

Catatan skrip:

- `.env` ditulis `[IO.File]::WriteAllText` (UTF-8 tanpa BOM, CRLF dipertahankan), bukan `Set-Content`, yang di PowerShell 5.1 menulis ANSI atau ber-BOM.
- **Skrip `.ps1` dan `.psd1` hanya berisi ASCII** (`->` dan `-`, bukan `→` dan `—`). Windows PowerShell 5.1 membaca skrip tanpa BOM sebagai ANSI (cp1252); byte UTF-8 tanda panah dan tanda pisah menjadi tanda kutip tipografis yang memutus string, dan skrip gagal di-parse. `DeployFilesTest` menolak karakter non-ASCII.
- Daftar sekolah resmi diperbarui di mesin pengembang (`php docs/sekolah/build-sekolah-jateng.php`, commit CSV yang berubah), lalu masuk server lewat langkah 7. Impor hanya menulis sekolah yang berubah dan tidak pernah menghapus sekolah yang sudah dipakai siswa.

Pembaruan **tidak** menjalankan `gelita:reset`, `db:seed`, atau impor ulang bank soal; isi konten di server dikelola lewat panel. Migration yang menghapus kolom atau tabel **tidak pernah digabung** dalam pembaruan yang sama dengan perubahan lain, dan selalu didahului satu rilis yang berhenti memakai kolom itu.

#### Migration `003400` always deletes `guru` accounts (staff roles, research revision 10 part 1)

`2026-01-01-003400_ReplaceTeacherWithResearcherRoles` replaces the staff role `guru` with `peneliti` and `ketua` and deletes every `guru` account together with its exports (files under `writable/` too) and deletion requests (`docs/revisi-10-analitik/27_IMPLEMENTASI_PERAN_PENELITI.md` §6.1, K16). Step 6 runs `php spark migrate` automatically, and the migration has **no guard**: the owner decided on 3 November 2026 that `guru` accounts are always deleted whenever it runs, on any server, because teachers no longer use the system. The earlier `gelita.allowTeacherRemoval` setting is gone; remove it from `.env` if it is still there.

Before the first update past part 1, ask every `guru` account holder whether they still need an export, and create the replacement accounts (`peneliti`, `ketua`) under *Staf* after the migration.

### Story script changed (`gelita:story:sync`)

`ContentSeeder` only inserts missing story lines, and `gelita:reset` keeps `dialogues`, so a revised script (`app/Database/Seeds/data/story.php`, e.g. the 32 lines of draft 2, October 2026) does not reach a server that already has content. After the code update, run:

```powershell
php spark gelita:story:sync --dry-run   # what would change
php spark gelita:story:sync
```

- A line is replaced only when it still holds the current script or an earlier version listed in `app/Database/Seeds/data/story-previous.php`. A line whose text was edited in the panel is listed as "Disunting di panel" and kept; `--timpa` replaces it too.
- Missing lines are added. Lines of a region that is not installed are skipped.
- Only titles and text change. Character, pose, effect, background, active flag, and linked recordings stay as they are.
- The command lists the replaced lines that still have a recording: that audio speaks the old text. Approve narration line by line, skipping those codes, until they are re-recorded; do not use **Setujui semua rekaman**.
- A run that writes something is recorded in the audit log as `story_sync`. Running it again changes nothing.

### After `git pull`: which commands are due (`gelita:setelah-pull`)

New 17 November 2026 (owner). After pulling, a developer or test install asks one command which spark commands the new code needs, instead of remembering them:

```bash
php spark gelita:setelah-pull              # report only; changes nothing
php spark gelita:setelah-pull --jalankan   # run the due steps in order after one confirmation (--ya skips it)
php spark gelita:setelah-pull --tandai     # this install is current: record the import sources as done
```

| Step | Due when | Command |
|---|---|---|
| Composer packages | `vendor/composer/installed.json` differs from `composer.lock` | `composer install` (never run by `--jalankan`: the command itself runs on `vendor/`) |
| Migrations | a migration file of `App` is missing from the `migrations` table | `php spark migrate` |
| School list, instrument content, question bank, story, Katalog Aset, narration install and import, music and sound effects | the input files changed since the step last ran on this install | `gelita:schools:import`, `gelita:instrumen:impor`, `gelita:bank:import docs/bank-soal/gelita-bank-soal-kelas5.xlsx`, `gelita:story:sync`, `gelita:katalog:impor`, `gelita:narration:install` then `gelita:narration:import`, `gelita:sound:install` |
| Minified JS/CSS | the mirror is older than its sources | `gelita:aset:minify` |
| Cache and content check | any of the above is due | `cache:clear`, `gelita:content:verify` |
| Validator guide, GELITA guide | a document section behind `/admin/panduan-validator` or `/admin/panduan-gelita` changed | `gelita:panduan:cek` (a person reads the page, then `--panduan=validator --tandai` or `--panduan=gelita --tandai`) |

- **How an import step knows.** `App\Libraries\DevStepLedger` keeps, per install, the fingerprint of each step's input files in `writable/gelita-langkah.json` (ignored in `.gitignore`). The import commands record themselves when they finish without errors on their default source (not with `--dry-run`, `--sumber`, `--dir`, `--file`, `--kunci`, `--berkas` or `--locale`); `gelita:reset` records the bank and the instrument content. A step never recorded on this install shows `belum-tercatat` and is not run: on an install known to be current, run `--tandai` once.
- **Servers.** A production server is still updated with the deploy script above; `--jalankan` refuses under `CI_ENVIRONMENT = production`. The report alone is safe there and lists the content commands the deploy script does not run (catalogue, narration, sound, story).
- **Validator guide.** `/admin/panduan-validator` (public, linked from the login page) is written from `docs/revisi-10-analitik/20` and `21`. Each page names its source sections; `app/Libraries/GuideSources/validator.json` holds their fingerprints. When a source section changes, `ValidatorGuideTest` fails, the page shows signed-in staff a note, and `gelita:panduan:cek` lists the pages to check. After checking (and editing `app/Libraries/ValidatorGuideContent.php` where needed), `php spark gelita:panduan:cek --panduan=validator --tandai` records the new fingerprints; commit the lock file with the change. `--tandai` refuses to run without `--panduan`, so marking one guide never marks the other.
- **GELITA guide.** `/admin/panduan-gelita` (*Mengenal GELITA*, public, linked from the login page) works the same way with sources anywhere under `docs/`, lock `app/Libraries/GuideSources/gelita.json`, test `GelitaGuideTest`, text `app/Libraries/GelitaGuideContent.php`; after checking, `php spark gelita:panduan:cek --panduan=gelita --tandai`. Facts that follow the game (regions, challenges, tries, unlock mode, research mode, version, sign-up rules) are read from the install when the page opens (`ValidatorGuideFacts`), so they need no edit.

### Rollback

Urutannya penting: **rollback migration dulu, baru kembalikan kode.** `migrate:rollback` menjalankan `down()` dari berkas migration rilis baru. Setelah `git reset`, berkas itu sudah tidak ada; CodeIgniter lalu berhenti dengan `There is a gap in the migration sequence near version number …` tanpa mengubah apa pun, tetapi kode keluarnya tetap **0**.

Nomor batch terlihat di kolom `Batch` pada `php spark migrate:status`. `<batch-sebelumnya>` adalah batch terakhir **sebelum** pembaruan yang dibatalkan. Di `CI_ENVIRONMENT = production`, `migrate:rollback` meminta konfirmasi `[y, n]`; jawab `y`.

```powershell
# sebagai akun gelita
Set-Location C:\laragon\www\gelita
New-Item -ItemType File -Force writable\pemeliharaan.flag | Out-Null

# 1. Bila pembaruan menyertakan migration — selagi kode rilis baru masih terpasang:
php spark migrate:status                                 # catat batch sebelum pembaruan
$env:database_default_DSN = (Get-Content C:\gelita-ops\migrate.dsn -Raw).Trim()
php spark migrate:rollback -b <batch-sebelumnya>
Remove-Item Env:\database_default_DSN
php spark migrate:status                                 # batch rilis baru harus sudah hilang
# atau pulihkan dari backup bila migration tidak reversible (lihat "Backup")

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
| Rekaman narasi (folder) | `public/assets/audio/narasi/{id,en}/{kode}.mp3` | pengisi suara: salin ke server; narasi cadangan ElevenLabs: ikut git di `ai-studio/elevenlabs-voice/out/`, dipasang dengan `gelita:narration:install`; lalu `gelita:narration:import` | revalidasi |
| Musik & efek suara | `public/assets/audio/{music,sfx}/{nama}.mp3` | git (dibaca langsung `core/audio.js`, bukan `media_assets`); penggantinya dari ElevenLabs dipasang di cabang kerja dengan `gelita:sound:install`, yang menulis `public/assets/audio/produksi.json`, lalu ikut git | revalidasi; `?v=` dari `produksi.json` (`CONFIG.audioVersion`) |
| Unggahan dan berkas bangkitan | `public/assets/uploads/` (`{asset_key}.{ext}`, `mockup/`, `library.thumb.*`) | panel `/admin/media`, editor konten, unggah narasi, gambar contoh, poster video — **hanya di server** | revalidasi |
| Asset package: media | `public/assets/p/{sha256[0:16]}.{ext}` | `gelita:paket:bangun` — **only on the server**, back it up with `uploads/` | immutable without `?v=` |
| Asset package: code | `public/assets/k/{code_version}/{css,js,vendor,fonts}/…`, `offline.html` | `gelita:paket:bangun` — only on the server | immutable without `?v=` |
| Asset package: manifests | `public/paket/pkt-*.json`; URL maps and build state in `writable/paket/` | `gelita:paket:bangun` — only on the server | manifest immutable; `writable/` not public |
| Berkas unduhan data | `writable/exports/` | aplikasi | tidak publik; dilayani controller berotorisasi |
| Log / cache | `writable/logs/`, `writable/cache/` | aplikasi | tidak publik |

Aset hanya punya tiga jalan masuk ke server: **commit ke repositori** (folder aset resmi, didaftarkan `gelita:media:scan`), **unggah lewat panel** (folder `uploads/`, ikut backup), atau **`gelita:katalog:impor`** dari Katalog Aset `ai-studio/game-images/` (juga ke `uploads/`, lihat [Installing the Katalog Aset images](#installing-the-katalog-aset-images)). Jangan menyalin berkas langsung ke folder aset resmi di server: berkas itu tidak ada di git, tidak ikut backup, dan hilang saat server dibangun ulang.

### Cache-busting

`asset_url_versioned()` menambahkan `?v={gelita.assetVersion}` pada CSS, JavaScript entry, dan vendor. Setiap pembaruan menaikkan nilai itu, jadi browser mengambil versi baru tanpa hard-refresh. Ini penting karena komputer sekolah jarang dibersihkan cache-nya.

**Automatic file version (18 October 2026).** While package mode is off, `?v=` is `{gelita.assetVersion}.{fingerprint}`, where the fingerprint comes from the file's modification time and size (`asset_file_version()`). A changed CSS/JS file therefore gets a new URL even when the server was updated without `deploy.ps1` (git pull, copied files) and `assetVersion` stayed the same, so there is no need to build a package and turn it off again just to push new code to browsers. Modules imported from the entry keep relying on revalidation (`no-cache` in the nginx config above). With package mode on, code still comes from the package snapshot and needs **Bangun ulang paket**.

#### Minified JS/CSS

*(New, English; owner, 19 October 2026.)* Browsers get minified copies of the code, while the files in git stay readable and unminified.

- **What.** `php spark gelita:aset:minify` (`App\Libraries\AssetMinifier`, library `wikimedia/minify`) writes every `public/assets/js/**/*.js` and `public/assets/css/*.css` in minified form, plus a plain copy of `public/assets/fonts/*.woff2`, to `public/assets/min/{version}/` with the same names and folders. Relative module imports and the CSS `url('../fonts/…')` therefore keep working. `{version}` is a hash of the minified bytes, so every build also gives the imported modules new URLs. `public/assets/min/sources.json` names the current build. The previous build is kept for pages that are still open; older ones are removed. JavaScript loses only comments and whitespace (no renaming), CSS likewise. Measured on 19 October 2026: 709 KB of JS/CSS becomes 466 KB, and what nginx sends gzipped per file drops from about 221 KB to 137 KB.
- **When it is used.** `asset_url_versioned()` and `game_asset_url()` point to `assets/min/{version}/…` only while the mirror is **fresh**: every source still has the modification time and size recorded at build time, and no source was added or removed. One changed file without a new run (a `git pull` by hand, a copied file) makes the whole site fall back to the original files until the command runs again. A missed run is slower, never broken. Switch: `Config\Gelita::$minifyAssets` (on; off in tests).
- **Package mode.** **Bangun ulang paket** / `gelita:paket:bangun` refreshes the mirror first and puts the minified bytes in `assets/k/{code_version}/` under the usual keys, so the manifest hashes and the device-side sha256 check describe the minified files. The first package built with minification gets a new `code_version`, so devices download the code group (about 0.5 MB) once more. If minifying fails, the package carries the original files and the build reports the warning `minify_failed`.
- **Not minified:** `public/sw.js`, `sw-kosong.js`, `offline.html`, `public/assets/vendor/` (Howler is already minified), images, and audio.
- **Checks.** `php spark gelita:aset:minify --periksa` reports whether the mirror is fresh (exit code 1 when it is not). On a developer machine, `node tools/qa/minify-check.mjs` compares the syntax trees of every source file and its minified copy. `AssetMinifierTest` runs the same comparison whenever Node and acorn are available.

Tidak semua URL ber-`?v=`:

- **Modul JavaScript** di-`import` relatif dari entry (`import … from '../core/dom.js'`). URL-nya tidak mewarisi query string entry.
- **Gambar dan audio** dari `media_assets` dirender `base_url($path)` tanpa `?v`. Nama berkas unggahan tetap `{asset_key}.{ext}` walau isinya diganti.

Karena itu Nginx memberi `immutable` **hanya** pada URL ber-`?v=`, dan `no-cache` (revalidasi dengan `Last-Modified`/`ETag`, dijawab 304 bila tidak berubah) pada aset lain. Modul lama dan gambar lama tidak pernah tertinggal di browser setelah pembaruan.

### Asset package (download-once mode)

*(New section, English; plan: `docs/rencana-paket-aset.md`.)* Package mode is off until a release names a package. Stage 1 adds the server side:

```powershell
php spark gelita:paket:bangun                  # build pkt-YYYY-MM-DD-NN from all active media, approved narration, and game code
php spark gelita:paket:bangun --tanpa-pustaka  # same, without copying the library's Wikimedia Commons images
php spark gelita:paket:periksa [kode]          # re-hash every package file against its manifest (exit 1 on any problem)
php spark gelita:paket:bersihkan [--coba]      # delete package files no package refers to; --hapus kode removes a package not used by any release
```

- Building changes nothing for students. Package mode switches on when the active release's **Versi gambar & suara** (`game_releases.asset_version`) is the package code. Admins switch it on **Rilis → Paket aset** (see *Admin switch* below); a release with that value, created and activated by hand, works too. Any other value, such as the default `1`, means package mode is off.
- Emergency kill switch: `gelita.paket.aktif = false` in `.env` forces normal URLs on every device, whatever the release says. `GET /api/paket/aktif` then answers `package_code: null` with `disabled_by_server: true`.
- The build copies the library's Commons images to `public/assets/uploads/library.commons.*` (owner decision P4a). The server needs outbound HTTPS to `commons.wikimedia.org` and `upload.wikimedia.org`; without it the build only warns and those images stay external links.
- `backup.ps1` archives `public/assets/p/`, `public/assets/k/`, `public/paket/`, and `writable/paket/` next to the uploads (see [Backup](#backup)). A package referenced by a release must stay on the server even after newer packages exist (devices may still request its files).
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

```powershell
$U = 'https://gelita.id'
$f = (Get-ChildItem C:\laragon\www\gelita\public\assets\p | Select-Object -First 1).Name
curl.exe -sI "$U/assets/p/$f" | Select-String -Pattern 'cache-control'   # public, max-age=31536000, immutable
curl.exe -s "$U/api/paket/aktif"                                         # {"success":true,"data":{"package_code":…}}
```

#### Service worker and package on devices (Stage 2)

*(New, English.)* With package mode on, devices keep the package in Cache Storage and `public/sw.js` serves it (details: `docs/06_JAVASCRIPT.md` §Asset package — service worker and downloader). Deployment notes:

- **HTTPS is required** for the service worker and for the sha256 check (`crypto.subtle`); `http://localhost` works for development only.
- **`sw.js` and `sw-kosong.js` must never be cached long.** The Nginx config already gives `.js` without `?v=` the value `no-cache`; browsers also re-check the worker script on navigations. Do not put either file behind `immutable` or a CDN.
- **Code updates while package mode is on.** Pages then load CSS and JavaScript from the package copy `assets/k/{code_version}/`, a snapshot taken when the package was built (minified, see [Minified JS/CSS](#minified-jscss)). After a deploy that changes game code, press **Bangun ulang paket** on Rilis → Paket aset and publish it (or `php spark gelita:paket:bangun` plus a release that names the package), or students keep running the old code. Media files are content-addressed, so devices download only the changed code files. Since 9 November 2026 `deploy.ps1` does this itself (see *Admin switch* below); by hand only after a deploy without them (git pull, copied files) or while a study runs.
- **Package copy missing on disk** (for example a restore without `public/assets/k/`): pages fall back to the normal `assets/…?v=` URLs for code files that are not on disk, so the game still loads; run `php spark gelita:paket:periksa` and rebuild.
- `X-Gelita-Paket: {package_code}` is sent on API responses while package mode is on (no student data).
- **A running game session keeps its package (S8, since 9 November 2026).** Pages inside a game session that is still open (`active` or `paused`) name the package the session started with (`game_sessions.package_code`), or no package when it started with package mode off, until the session ends. A publish, a rebuild, or the switch therefore reaches a student at the start of their next session. `/api/paket/aktif` and `X-Gelita-Paket` keep reporting the active package so devices can fetch it in the background; the `.env` kill switch still overrides every session at once. Keep a package that a session still uses on the server (`gelita:paket:bersihkan` only removes packages no release names); if its files are gone, the session falls back to the active package.

**Kill switch for service worker trouble** (plan §11.1). Two levels, from mild to drastic:

1. **`.env`: `gelita.paket.aktif = false`** (no restart needed; `.env` is read on every request). Every device gets normal asset URLs on its next page load, `/api/paket/aktif` answers `package_code: null` with `disabled_by_server: true`, and game pages register the empty worker `public/sw-kosong.js` instead of `sw.js`. The empty worker has no fetch handler, so every request goes to the network as without a service worker; it removes the device's package marker (`gelita-meta`) and keeps the downloaded files (`gelita-aset`, public assets only) for later. The offline page is not shown while it runs. Undo: remove the line (or set `true`); the next page load registers `sw.js` again, and devices download the package again before Mulai (only what is missing).
2. **When pages do not load at all** (a worker bug that breaks navigations, so no page can register anything): serve the empty worker at the worker's own URL. Browsers fetch the worker script themselves on navigation, so this reaches every device. Remove the rule once `sw.js` is fixed:

```nginx
# Inside the server block of gelita.id.conf, temporary
location = /sw.js {
    default_type application/javascript;
    add_header Cache-Control "no-cache" always;
    try_files /sw-kosong.js =404;
}
```

Reload Nginx after adding and after removing it. Copying `public/sw-kosong.js` over `public/sw.js` works too, but the next deploy (`git pull`) then stops on the changed file; restore it with `git checkout -- public/sw.js`. Devices pick up the empty worker on their next navigation (browsers re-check the worker script at least once a day).

Check after deploying:

```powershell
curl.exe -sI "$U/sw.js" | Select-String -Pattern 'cache-control'              # no-cache
curl.exe -sI "$U/api/paket/aktif" | Select-String -Pattern 'x-gelita-paket'   # the package code while package mode is on
```

#### Admin switch: Rilis → Paket aset

*(New subsection, English; asset package Stage 6, plan §6.3.)* Admins switch package mode on the panel page **Rilis → Paket aset** (`/admin/studi/rilis/paket`); the CLI commands above stay available.

- **Nyalakan** builds a package in batches driven by the open page (one POST every few seconds, each at most ~4 s of work; 20 s without JavaScript), so the default PHP `max_execution_time` (30 s) and Nginx `fastcgi_read_timeout` (60 s) are enough. The summary is then confirmed with **Terbitkan dan nyalakan**, which creates and activates a release with the same versions and the new package code. **Matikan** activates a release without a package.
- During a running study every package change asks for a tick and a reason (audit `release_activate` with `package_from`, `package_to`, `reason`), except going back to the package the study locked when it started (`research_studies.package_code`; owner, 9 November 2026). Rilis and Paket aset show the locked package and warn while students get another one; the research-ready checklist and `gelita:content:verify --siap-penelitian` fail until the locked package is active again.
- When `.env` sets `gelita.paket.aktif = false`, the page shows "Mode paket dimatikan dari server" and students get normal URLs whatever the switch says.
- The setting **Izinkan mode daring untuk perangkat lab** lives in `writable/paket/pengaturan.json`, which `backup.ps1` already archives with `writable/paket/`.
- **Deploys while package mode is on (9 November 2026).** `deploy.ps1` ends with `php spark gelita:paket:bangun --deploy`, after maintenance mode is lifted. With package mode off it does nothing. With it on, it builds a package from the new code and the active media, then creates and activates a release with it (audit `release_activate`, `via: deploy`), so devices get the new `kode` group (only changed files are downloaded). **While a study is running** it only builds the package and prints a warning: an admin publishes it on Paket aset with a tick and a reason, or leaves students on the locked package. A failed build is a warning, not a failed deploy; students keep the old package until **Bangun ulang paket**. The build takes a few minutes (hashing and copying up to ~50 MB).
- **Staff preview.** `?paket={package_code}` on any game page (the **Pratinjau** links on Paket aset) shows that package to the signed-in admin only, with a pill in the bottom-left corner, "Pratinjau paket … Hanya terlihat oleh Anda.", and **Akhiri pratinjau** (`?paket=mati`).
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
* Pose tokoh: satu gambar diam per pose (no frame loop since 20 November 2026), WebP ± 60 KB.
* Video Pustaka: MP4 H.264, 720p, bitrate ≤ 1.5 Mbps, durasi ≤ 60 detik.
* Audio narasi: MP3 96–128 kbps mono.

Ukuran piksel resmi tiap slot ada di `Config\Gelita::$assetSizes` dan ditegakkan saat unggah. Batas unggah 64 MB (`Config\Gelita::$maxUploadBytes`, `upload_max_filesize`, `client_max_body_size 72M`).

---

## Tugas Terjadwal

Jam sistem wajib WIB (langkah 0 di [Langkah instalasi](#langkah-instalasi)). Di VPS berzona UTC, jadwal "02.00" akan berjalan pukul 09.00 WIB.

| Tugas | Waktu | Task Scheduler |
|---|---|---|
| Backup | 01.00 setiap hari; 08.00, 12.00, 16.00 hari sekolah | `\GELITA\Backup` (`backup.ps1`) |
| Retensi (`gelita:retention:run`) | 02.00 setiap hari | `\GELITA\Retensi` (`retensi.ps1`) |
| Rotasi log > 30 hari | bersama retensi | di dalam `retensi.ps1` |

Backup lebih sering pada jam sekolah disengaja: sesi kelas berlangsung pada jam itu, dan kehilangan satu sesi berarti mengulang satu kelas. Banyak SD masuk hari Sabtu. Bila demikian, tambahkan `Saturday`.

`gelita:retention:run` menandai sesi dan pengerjaan yang menganggur (`paused`/`abandoned`), menghapus berkas unduhan data yang kedaluwarsa (`gelita.exportRetentionDays`, bawaan 7 hari), dan membuat **pratinjau** penghapusan untuk data yang melewati `research_studies.retention_days`. Data penelitian tidak pernah dihapus tanpa persetujuan manusia di **Tata kelola** (`/admin/tata-kelola`); pratinjau yang menunggu tampil sebagai peringatan di **Ringkasan**.

Open attempts in a game session of a field session are not abandoned by the nightly run while their study is *Berjalan* (owner, 20 November 2026): field sessions leave them open so the student continues them at the next meeting or make-up session, often days later. They are closed (`challenge_abandoned {via: retention, reason: study_closed}`) when the head researcher sets the study to *Ditutup*. Keep the daily schedule; postponing the run is not needed and leaves expired data downloads on the server.

PowerShell sebagai Administrator. Tugas berjalan dengan akun `gelita`, "baik login maupun tidak", tetapi tetap butuh Laragon (MySQL) menyala:

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

Backup disimpan 30 hari di lokasi pertama lalu disalin ke lokasi kedua. **Backup yang hanya ada di mesin yang sama dengan datanya bukan backup.** Bila salinan kedua gagal, skrip keluar dengan kode **2** dan mencetak peringatan, sehingga kegagalan terlihat di Task Scheduler.

Backup memuat data pribadi anak dan kredensial. Folder backup hanya dapat dibaca pemiliknya (ACL). Drive eksternal sebaiknya dienkripsi (BitLocker To Go).

| Langkah | `deploy\windows\backup.ps1` |
|---|---|
| Database | dua `mysqldump` dengan `--defaults-extra-file=backup.cnf` dan `--result-file` (pengalihan `>` di PowerShell 5.1 menulis UTF-16): data dengan `--single-transaction --quick --no-tablespaces --default-character-set=utf8mb4 --ignore-table=gelita.ci_sessions`, lalu struktur `ci_sessions` (`--no-data`); dikemas ke `gelita-db-{stamp}.zip`; berkas `.sql` sementara selalu dihapus |
| Unggahan | `Compress-Archive` → `gelita-uploads-{stamp}.zip` |
| Asset packages | `gelita-paket-{stamp}.zip` (`p`, `k`, `paket` from `public/`) and `gelita-paket-peta-{stamp}.zip` (`writable/paket`); two archives because both `paket` folders would collide in one zip; missing folders are skipped |
| `.env` | `Copy-Item` → `gelita-env-{stamp}.txt`, lalu `LastWriteTime` disetel ke waktu backup |
| Simpan 30 hari | `Get-ChildItem` + `Remove-Item` |
| Salinan kedua | salin berkas yang belum ada ke `Kedua`; gagal → kode 2 |
| Lokasi | `Dir`, `Kedua` di `config.psd1` |
| Second location limit | with `KeduaHari` in `config.psd1`, files older than that many days are deleted at the second location |
| Status | `writable/backup-status.json` (`ran_at`, `kedua_hari`, `ganti`), read by the backup warning on Tata kelola |

- `--no-tablespaces` menghindari tuntutan hak global `PROCESS` di MySQL 8.0.21+. Skema GELITA tidak punya routine atau trigger, jadi `--routines --triggers` tidak diperlukan.
- `Copy-Item` mewarisi stempel waktu `.env`. Tanpa penyegaran `LastWriteTime`, salinan `.env` yang tidak berubah lebih dari 30 hari langsung terhapus langkah simpan 30 hari dan tidak pernah sampai ke lokasi kedua.
- Salinan kedua hanya **menambah** berkas (tanpa `/MIR`). Dengan begitu, lokasi pertama yang rusak atau kosong tidak ikut menghapus salinan kedua.
- `-Ganti` makes a fresh backup, tests the zip, then deletes every older `gelita-*` file at both locations. Use it only for [Backup replacement after a deletion or the study closure](#backup-replacement-after-a-deletion-or-the-study-closure).

### Uji pemulihan

Lakukan minimal sekali sebelum penelitian dimulai, dan setelah setiap perubahan skrip backup. Backup yang belum pernah dipulihkan statusnya belum diketahui.

```powershell
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

Di PowerShell, operator `<` tidak ada, jadi pemulihan memakai `source`. Kedua berkas `.sql` tidak saling bergantung, jadi urutannya bebas. Pemulihan ke database production (`gelita`) mengikuti langkah yang sama dengan mode pemeliharaan aktif; pulihkan juga `public/assets/uploads/` dari arsip unggahan dengan stempel waktu yang sama. Restore the asset packages from the package archive of the same stamp as well: unpack `gelita-paket-{stamp}.zip` into `public\assets\` (`p`, `k`) and `public\` (`paket`), and `gelita-paket-peta-{stamp}.zip` into `writable\`. Then run `php spark gelita:paket:periksa`. Without them the game falls back to normal asset URLs on its own (the release's package has no URL map). Setelah itu, semua pengguna perlu login ulang karena `ci_sessions` kosong.

---

## Verifikasi Setelah Deploy

Jalankan seluruh daftar ini setelah instalasi baru dan setiap pembaruan kode. Run it in PowerShell on the server, and write `curl.exe`: in Windows PowerShell 5.1 `curl` is an alias of `Invoke-WebRequest`.

```powershell
Set-Location C:\laragon\www\gelita

# 1. Struktur konten — harus 0 galat (peringatan gambar contoh boleh sampai gambar asli diunggah)
php spark gelita:content:verify

# 2. Database — angka yang diharapkan di komentar
mysql --defaults-extra-file=C:\gelita-ops\backup.cnf gelita -e "
  SELECT COUNT(*) AS wilayah FROM levels WHERE is_active = 1;                               -- 3
  SELECT l.code, COUNT(*) AS tantangan FROM challenge_nodes n JOIN levels l ON l.id = n.level_id
   WHERE n.is_active = 1 GROUP BY l.code;                                                   -- 4 per wilayah
  SELECT COUNT(*) AS soal, SUM(scorable) AS dinilai FROM challenge_items WHERE is_active = 1; -- 126, 108
  SELECT COUNT(*) AS dialog FROM dialogues;                                                 -- 91
  SELECT COUNT(*) AS pustaka FROM library_pages WHERE is_active = 1;                        -- 30
  SELECT COUNT(*) AS gambar_contoh FROM media_assets WHERE is_placeholder = 1 AND is_active = 1; -- 66, lalu 0
  SELECT locale, approval_status, COUNT(*) FROM audio_assets GROUP BY locale, approval_status; -- 100 approved per bahasa
  SELECT release_code FROM game_releases WHERE is_active = 1;                               -- GELITA-KELAS5-2026"

# 3. HTTPS & header keamanan — pada halaman DAN aset, masing-masing tepat satu kali
$U = 'https://gelita.id'
curl.exe -s -D - -o NUL "$U/" | Select-String -Pattern 'HTTP/|strict-transport|x-frame|x-content-type'
curl.exe -s -D - -o NUL "$U/assets/css/game.css?v=1" | Select-String -Pattern 'strict-transport|x-content-type|cache-control'

# 4. Cache aset — harus: entry ber-?v immutable, modul tanpa ?v no-cache
curl.exe -sI "$U/assets/js/game.js?v=1" | Select-String -Pattern 'cache-control'     # public, max-age=31536000, immutable
curl.exe -sI "$U/assets/js/core/dom.js" | Select-String -Pattern 'cache-control'     # no-cache

# 5. Berkas sensitif tidak terlayani — semuanya harus 404
foreach ($p in '.env', '.htaccess', '.git/config', 'app/Config/App.php', 'writable/logs/', 'vendor/autoload.php', 'info.php') {
    $kode = curl.exe -s -o NUL -w '%{http_code}' "$U/$p"
    "$p -> $kode"
}

# 6. Mode pemeliharaan — harus 503, lalu 200
New-Item -ItemType File -Force writable\pemeliharaan.flag | Out-Null
curl.exe -s -o NUL -w '%{http_code}\n' "$U/"
Remove-Item writable\pemeliharaan.flag
curl.exe -s -o NUL -w '%{http_code}\n' "$U/"
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

Baru setelah **enam pemeriksaan otomatis dan sepuluh uji manual** ini lolos, aplikasi dinyatakan siap dipakai kelas. Data siswa uji yang tersisa ikut dikosongkan `gelita:reset` sebelum pengambilan data penelitian.

---

## Operational Notes

### Sebelum sesi kelas

1. **Sebelum pengambilan data pertama:** gambar contoh sudah diganti, daftar siap penelitian di **Penelitian** (`/admin/studi`) hijau, dan `gelita:reset` sudah dijalankan (lihat [Sebelum pengambilan data penelitian](#sebelum-pengambilan-data-penelitian)); `gelita:content:verify --siap-penelitian` lolos.
2. Buka **Kelengkapan aset** (`/admin/media/kelengkapan`): aset yang belum ada tidak menghentikan permainan (dipakai pengganti), tetapi rekaman narasi yang masih draft tidak terdengar siswa.
3. Pastikan ruang disk cukup: `Get-PSDrive C, D`.
4. **Siapkan NPSN sekolah.** Siswa Jawa Tengah wajib mengisi NPSN saat mendaftar, dan pendaftaran hanya menerima kelas 5 SD/MI. Siswa SD jarang hafal NPSN: cari di **Sekolah** (`/admin/sekolah`, ketik nama atau desa), lalu tuliskan di papan bersama nama sekolahnya. Saat 8 angka diketik, nama resmi sekolah muncul untuk dicocokkan anak.
5. Jalankan backup manual: `Start-ScheduledTask -TaskPath '\GELITA\' -TaskName 'Backup'`.
6. Pastikan Laragon menyala (Nginx + MySQL hijau) dan tidak ada restart Windows Update yang tertunda.
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

```powershell
Set-Location C:\laragon\www\gelita
(Select-String -Path "writable\logs\log-$(Get-Date -Format yyyy-MM-dd).log" -Pattern 'ERROR').Count     # galat hari ini
Get-ScheduledTaskInfo -TaskPath '\GELITA\' -TaskName 'Backup' | Select-Object LastRunTime, LastTaskResult   # backup terakhir
mysql --defaults-extra-file=C:\gelita-ops\backup.cnf gelita -e "SELECT DATE(server_received_at) d, COUNT(*) n FROM game_event_logs WHERE server_received_at > NOW() - INTERVAL 7 DAY GROUP BY d ORDER BY d DESC;"
mysql --defaults-extra-file=C:\gelita-ops\backup.cnf gelita -e "SELECT table_name, ROUND(data_length/1048576) data_mb, ROUND(index_length/1048576) idx_mb FROM information_schema.tables WHERE table_schema='gelita' ORDER BY data_length DESC LIMIT 5;"
Get-PSDrive C, D
```

`game_event_logs` akan menjadi tabel terbesar dan itu normal — ia memang catatan proses penelitian. Perkiraan kasar: satu peserta yang menyelesaikan 12 tantangan menghasilkan beberapa ratus event, kurang dari 1 MB termasuk index; seratus peserta sekitar 100 MB. `item_tries` (satu baris per percobaan) dan `device_logs` tumbuh jauh lebih lambat. Jangan menghapus raw event hanya karena ringkasannya sudah tampil di panel.

Bulanan: jalankan satu uji pemulihan.

### Masalah umum

| Gejala | Penyebab biasa | Tindakan |
|---|---|---|
| Halaman putih pada halaman tertentu | galat PHP, `display_errors` mati | baca `writable/logs/log-*.log` |
| Situs terus menjawab 503 | pembaruan gagal dan mode pemeliharaan sengaja dibiarkan, atau `gelita:reset --pemeliharaan` terhenti paksa | baca keluaran skrip, perbaiki, hapus `writable/pemeliharaan.flag` |
| "Unable to connect to database" | kredensial `.env` salah atau MySQL mati; Laragon belum menyala setelah restart | buka Laragon → *Start All* |
| `CREATE command denied … gelita_app` saat migrate | migration berjalan tanpa DSN | jalankan lewat `database_default_DSN` (lihat [Kredensial migration lewat DSN](#kredensial-migration-lewat-dsn)) |
| `gelita:reset`: "Akun database tidak punya hak TRUNCATE (DROP)" | reset dijalankan dengan akun aplikasi | ulangi dengan `database_default_DSN` akun `gelita_migrate`; belum ada tabel yang dikosongkan |
| `db:seed`: "GELITA_ADMIN_PASSWORD kosong" | the variable is not set in that PowerShell session | set `$env:GELITA_ADMIN_PASSWORD` in the same session, then run the seeder again (see [Langkah instalasi](#langkah-instalasi)) |
| `db:seed`: "berkas … tidak ditemukan" untuk Pustaka atau bank soal | folder `docs/` dibuang dari server | pulihkan dari git (`git checkout -- docs`), jalankan ulang seeder |
| `content:verify --siap-penelitian` gagal | masih ada gambar contoh yang dipakai tantangan | unggah gambar asli; `gelita:mockups --dry-run` menampilkan slot yang tersisa |
| Kartu video Pustaka tanpa gambar | server tidak dapat menghubungi penyedia thumbnail | buka akses keluar, lalu `gelita:library:thumbnails` |
| Gambar Pustaka tidak tampil di perangkat siswa | jaringan kelas memblokir `commons.wikimedia.org` | buka akses, atau unggah salinan gambarnya lewat panel |
| Halaman lambat saat kelas penuh | proses PHP habis | naikkan `PHP_FCGI_CHILDREN` sesuai RAM |
| Every page takes 2–3 s although the CPU is idle | `.env` says `database.default.hostname = localhost`: Windows tries IPv6 `::1` first | set `127.0.0.1` ([`.env` production](#env-production), note 5b) |
| Slow from about 30 students on, disk busy | Laragon's default `my.ini` (128 MB buffer pool, binary log on) | the block of [Pengaturan MySQL](#pengaturan-mysql) |
| `Deadlock found when trying to get lock` in `writable/logs` | two event batches met on MySQL 8; since 21 November 2026 the write is run again by itself (`DeadlockRetry`) and re-sent events no longer cause it | nothing while requests still answer 200: the `ERROR` line is the database driver's, written before the write is run again. A `CRITICAL` line right after it means every run failed: look at the load, and keep the log for the developer |
| Students sent to `/masuk` now and then, events answered 401 | code from before 21 November 2026 with `session.regenerateDestroy = true` | update the code ([Pembaruan kode](#pembaruan-kode)); the `.env` line is ignored afterwards |
| The asset package downloads again every few hours | the Nginx site file on the server has no cache rules (a hand-written file instead of `gelita.id.conf`) | [Nginx](#nginx) |
| Unduhan data gagal di tengah | `memory_limit` atau batas 300 detik (dihitung jam dinding) | persempit rentang tanggal; periksa batas PHP |
| Nginx tidak mau start | port 80 dipakai Apache Laragon, IIS, atau aplikasi lain | matikan Apache di Laragon; `Get-NetTCPConnection -LocalPort 80` untuk mencari pemakainya |
| Every page of gelita.id, katalog.gelita.id and pictures too answer `error code: 502` from Cloudflare, within a second | `cloudflared` runs but nothing answers on `127.0.0.1:80`: Nginx is not running, usually because its configuration fails. (A stopped `cloudflared` gives error 1033 instead.) | `Get-NetTCPConnection -LocalPort 80 -State Listen` finds nothing; `.\nginx.exe -t` in Laragon's Nginx folder names the failing line ([Nginx](#nginx), step 4); fix it and start Nginx in Laragon. If Nginx does answer `curl.exe -sI http://127.0.0.1/`, the tunnel's service is not `http://127.0.0.1:80` ([HTTPS](#https)) |
| `nginx -t`: `"fastcgi_read_timeout" directive is duplicate in …gelita.id.conf` | the first `gelita.id.conf` (21 November 2026) included Laragon's `snippets/fastcgi-php.conf`, which sets that directive too | copy the current `deploy\windows\nginx\gelita.id.conf` (`include fastcgi.conf;`) |
| Tugas terjadwal `0x1` | Laragon/MySQL mati saat tugas berjalan, atau path PHP berubah setelah ganti versi | nyalakan Laragon; perbarui `Php`/`MySql` di `config.psd1` |
| Gambar tidak muncul | aset belum diunggah atau `is_active = 0` | **Gambar & mockup** (`/admin/media`); `gelita:media:scan` |
| Audio tidak berbunyi | rekaman masih `draft` | **Rekaman narasi** → setujui |
| CSRF "action not allowed" | tab dibiarkan terbuka semalam | muat ulang halaman; `security.expires` 2 jam |
| Anak keluar / pindah komputer | sesi berakhir, tab tertutup | masuk lagi di `/masuk`; progres tetap |
| Staf selalu dikembalikan ke Ganti kata sandi | sandi sementara dari admin (reset / akun baru) belum diganti | ganti sandi di halaman itu; kewajiban hilang otomatis |
| Admin lupa kata sandi | tidak ada admin lain yang dapat mereset di `/admin/staf` | `php spark gelita:staff:password admin` di PowerShell. Masuk dengan sandi sementara yang dicetak, lalu buat sandi baru (reset juga membuka kunci) |
| Anak lupa kata sandi | — | guru mereset di `/admin/peserta/{id}`; anak membuat sandi baru saat masuk |
| Anak lupa nama pengguna | — | admin mencarinya di `/admin/peserta` berdasarkan nama & sekolah (guru tidak melihat nama maupun nama pengguna) |
| "Terlalu banyak percobaan" | siswa 8 kali salah sandi (staf 5 kali) | tunggu 5 menit (staf 15 menit), atau reset sandi (reset juga membuka kunci) |
| Pendaftaran terus ditolak | sandi belum memenuhi 5 syarat atau memuat nama pengguna | baca daftar syarat yang masih bertanda ✗ bersama anak |
| Impor workbook di panel gagal | galat baris di workbook | buka pratinjau, perbaiki sheet & baris yang disebut, unggah ulang |
| Event tertahan | koneksi putus sesaat, atau situs dalam pemeliharaan | otomatis terkirim saat aplikasi dibuka lagi di perangkat itu |
| Disk penuh | unduhan lama + backup + video | jalankan retensi; pindahkan backup lama |

### Keamanan operasional

1. Ganti sandi admin awal **sebelum** aplikasi dipakai di sekolah (lihat [Langkah terakhir](#langkah-terakhir)). Staf yang menerima sandi sementara (reset atau akun baru) dipaksa menggantinya lewat **Ganti kata sandi** saat masuk (`staff_users.must_change_password`).
2. Buat akun `guru` terpisah untuk tiap guru, dengan sekolah terisi. Jangan membagikan akun admin.
3. Nonaktifkan akun guru yang tidak lagi terlibat lewat `/admin/staf` (tombol *Nonaktifkan*, `is_active = 0`); jangan hapus — audit log-nya masih diperlukan.
4. Tinjau riwayat di `/admin/tata-kelola/audit` secara berkala, terutama aksi `export` dan `delete_execute`.
5. **Pembaruan sistem.** Windows Update di luar jam sekolah, dan rilis patch PHP 8.3 lewat pembaruan Laragon. Ganti versi PHP berarti mengulang pengaturan `php.ini` (lihat [Pengaturan PHP](#pengaturan-php)).
6. **Dependency Composer** diperbarui di lingkungan pengembangan (`composer update`, uji, commit `composer.lock`), lalu dibawa ke server lewat pembaruan biasa. Di server hanya `composer install` dan `composer audit`. `composer update` di server membuat `composer.lock` berbeda dari repositori dan membuat `git pull` berikutnya bentrok.
7. `.env`, `migrate.dsn`, dan `backup.cnf` tidak pernah masuk repositori. `.gitignore` memuat `.env`, isi `writable/*` per subfolder, `public/assets/uploads/*`, dan `writable/pemeliharaan.flag`.
8. Port database (3306) tidak pernah dibuka ke jaringan (`bind-address = 127.0.0.1`). Nginx listens on `127.0.0.1` only, and the Windows firewall opens no port for GELITA.

### Tata kelola data anak

Dataset ini memuat data pribadi anak. Yang harus disiapkan bersama institusi sebelum pengambilan data:

1. **Persetujuan.** Aplikasi mencatat centang persetujuan siswa saat mendaftar (`participants.assented_at`, dengan versi teks di `participants.assent_version`). Dalam mode penelitian, izin orang tua berkode dari L7 (tanggal, izin rekaman, izin pertanyaan internet, status) diisi peneliti di panel per siswa, tanpa nama; formulir T2 tetap di luar aplikasi. Lihat [bagian 4](#research-revision-10-part-4-consent-data-rights-and-research-privacy).
2. **Retensi.** Masa simpan studi penelitian ditetapkan oleh izin etik: studi `field_mode = 1` paling singkat 7305 hari (20 tahun), dihitung sejak studi ditutup (`research_studies.closed_at`). Studi lain tetap memakai `retention_days` sejak mulai sesi; nilai bawaan 1825 hari.
3. **Akses terbatas.** Guru hanya melihat sekolahnya, unduhan guru selalu anonim, akun admin sesedikit mungkin.
4. **Unduhan pseudonim sebagai bawaan.** Analisis dilakukan dengan kode peserta; nama hanya dibuka bila memang diperlukan dan tercatat di audit log.
5. **Penghapusan yang dapat ditelusuri.** Selalu lewat pratinjau, konfirmasi, dan audit di **Tata kelola**. Permintaan orang tua diajukan peneliti di **Permintaan data** (`/admin/permintaan-data`), dikonfirmasi ketua peneliti, dijalankan admin di Tata kelola, lalu diselesaikan dengan daftar periksa dan berita acara.
6. **Kata sandi anak** disimpan hanya sebagai hash; guru dan admin tidak dapat melihatnya. Sandi sementara hasil reset hanya tampil sekali. Nama pengguna diperlakukan sebagai data identitas dan tidak ikut unduhan anonim.
7. **IP di `ci_sessions`.** Dengan `gelita.researchPrivacy = true` sesi memakai `App\Libraries\HashedIpSessionHandler`, yang menyimpan hash IP (64 karakter), dan `gelita:retention:run` menghapus baris sesi yang kedaluwarsa atau masih ber-IP mentah. Dengan nilai bawaan (`false`) sesi tetap memakai `DatabaseHandler`, sehingga `ci_sessions.ip_address` menyimpan IP **mentah** selama sesi hidup. Tabel lain hanya menyimpan `ip_hash`, dan backup tidak membawa isi `ci_sessions`.
8. **Backup** memuat data pribadi anak: batasi aksesnya dan enkripsi media salinan kedua.

Persetujuan etik dan penafsiran hukumnya tetap urusan institusi. Aplikasi menyediakan mekanismenya; kebijakannya ditetapkan manusia.

---

## Aturan Sistem

1. GELITA production selalu satu instansi server ber-HTTPS di domain sendiri (`gelita.id`), di Windows + Laragon. HTTPS ends at Cloudflare, and the server sits behind a Cloudflare Tunnel. Tidak ada instalasi tanpa HTTPS di jaringan lokal.
2. Server baru selalu dipasang lewat [Instalasi baru](#instalasi-baru): `migrate` → `db:seed DatabaseSeeder` → `gelita:narration:import` dan persetujuan narasi → `gelita:content:verify`.
3. Web server production adalah Nginx. Hanya `index.php` yang dieksekusi PHP, dan berkas tersembunyi tidak pernah dilayani.
4. `.env` tidak pernah masuk repositori.
5. Akun MySQL aplikasi tidak punya hak DDL. Migration dan `gelita:reset` memakai `gelita_migrate` lewat `database_default_DSN` (tanpa port), backup memakai `gelita_backup` yang hanya membaca. Rahasia keduanya di luar repositori.
6. `database.default.DSN` tidak pernah ditulis di `.env`.
7. One Windows account (`gelita`) runs Laragon, every `php spark` command, the deploys and the scheduled tasks.
8. `writable/exports/` tidak pernah dapat diakses langsung lewat URL.
9. Pembaruan kode selalu lewat `deploy.ps1`: didahului backup, dibatalkan otomatis bila ada sesi kelas aktif, berjalan di balik mode pemeliharaan, dan hanya membuka situs kembali bila `gelita:content:verify` lolos.
10. Setiap pembaruan menaikkan `gelita.assetVersion`. PHP is not reloaded: `opcache.validate_timestamps = 1` stays on.
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
21. Skrip di `deploy/` tidak diedit di server; nilai khusus server ditulis di `config.psd1`. `composer update` dan `php spark optimize` tidak dijalankan di server.
22. Rollback selalu dimulai dari migration (selagi kode rilis baru terpasang), baru kemudian kode.
23. Skrip `.ps1` dan `.psd1` hanya berisi ASCII, agar Windows PowerShell 5.1 membacanya sama dengan PowerShell 7.
24. `gelita:simulasi` (siswa simulasi) hanya untuk mesin pengembangan dan tidak dijalankan di server; command itu menolak berjalan bila `CI_ENVIRONMENT = production`.

---

## Keputusan yang tercatat

- **Unduhan data tetap sinkron**, dibangun di request yang sama. Batas `gelita.exportMaxRawEvents` (`Config\Gelita::$exportMaxRawEvents`, 200.000 baris) dan 300 detik sudah menjaga satu request tetap terkendali, sedangkan antrean kerja butuh proses latar yang harus dijaga. Ditinjau ulang bila unduhan nyata di data penelitian mendekati 120 detik.
- **Mode pemeliharaan di Nginx, bukan filter CodeIgniter**: tanpa kode tambahan, `php spark` tetap bisa dipakai selama pemeliharaan.
- **Tiga akun database**: tugas terjadwal hanya memegang kredensial baca, dan kredensial DDL hanya dibaca saat migration atau reset.
- **Isi awal lewat seeder dengan akun aplikasi**: `DatabaseSeeder` hanya menulis data, jadi tidak memerlukan akun migrasi. Hanya `gelita:reset` yang butuh hak `TRUNCATE`.

---

## Catatan Implementasi Tahap 8

The Linux path (Jalur L) and its scripts (`deploy/linux/`), together with the Nginx files for a certificate on the server, were removed from the repository on 21 November 2026. GELITA has one server: Windows + Laragon behind a Cloudflare Tunnel. This section and its three parts (*Yang diuji*, *Cacat yang ditemukan dan diperbaiki*, *Masih belum teruji*) are kept as they were written, as history of the tests run in 2026. Where they name Jalur L, a Linux command, cron or a certificate, only the Windows form in the sections above applies. The sections after them describe the server as it is now.

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

* GELITA berjalan sebagai satu instansi server lewat HTTPS, di Windows + Laragon (Nginx + php-cgi) di belakang Cloudflare Tunnel, dengan header keamanan di setiap respons dan berkas sensitif tidak terlayani.
* Instalasi baru mengisi 28.487 sekolah resmi, akun admin, tiga wilayah dengan 12 tantangan, 126 soal kelas 5, 91 baris cerita, 30 halaman Pustaka, 66 gambar contoh, dan 103 rekaman narasi per bahasa yang disetujui, lalu lolos verifikasi konten.
* Sebelum pengambilan data, gambar contoh diganti gambar asli, daftar siap penelitian hijau, uji asap lolos, data uji coba dikosongkan dengan `gelita:reset`, dan `gelita:content:verify --siap-penelitian` lolos.
* Pembaruan kode berjalan dengan satu skrip (`deploy.ps1`) yang menolak berjalan saat kelas aktif, membuat backup, memakai mode pemeliharaan, dan hanya membuka situs bila konten lolos verifikasi. Rollback terdokumentasi.
* Migration dan reset memakai akun DDL terpisah lewat DSN; aplikasi dan backup memakai akun tanpa hak DDL.
* Browser selalu mendapat aset terbaru setelah pembaruan, sementara aset ber-versi tetap di-cache lama.
* Backup berjalan otomatis ke dua lokasi tanpa IP mentah siswa, kegagalannya terlihat, dan prosedur pemulihannya sudah pernah diuji.
* Retensi harian berjalan pada jam WIB yang benar; data penelitian tidak pernah terhapus tanpa persetujuan manusia.
* Enam pemeriksaan otomatis dan sepuluh uji manual lolos, dan aplikasi dinyatakan siap dipakai kelas.

## Research revision 10, part 2: migration `003500` and the validators' test install

- **Migration `2026-01-01-003500_CreateFieldSessions`** adds the field session tables and columns and moves progress to `participant_progress`: it fills that table from the completed attempts already stored (one row per student and study) before it drops `session_progress`, so no progress is lost. `SystemResetService::REQUIRED_MIGRATION` is `2026-01-01-003500`. Field mode stays off on existing installs, so the game behaves as before. Never run migrations or `gelita:reset` against the production server from a development session.
- **Validators' test install (G3).** After `php spark migrate` and `gelita:reset` on the test install: `php spark gelita:uji:akun --buat` (passwords are shown once), then turn on Mode penelitian for the study, and create one field session `V01-A-P2` (or the meeting under review) of at most 4 hours per day. `php spark gelita:uji:akun --reset=uji.bahasa1` gives one validator a fresh start. The tool refuses in production and when any non-`uji.*` student exists. Since 21 November 2026 the test install is planned on `uji.gelita.id`, with its own content and database, separate from production (owner; `docs/revisi-10-analitik/20_PAKET_VALIDASI.md` → *Prasyarat no. 8*). The tool refuses when `CI_ENVIRONMENT = production`, so the test install's `.env` must not be a copy of `env-example-production` as it is.

## Research revision 10, part 3: migrations `003600` and `003700`

- **Before migrating:** `php spark gelita:log:hitung` prints, without changing anything, the event rows the cleanup deletes or renames, the `dialogue_completed` rows it creates, and the device rows whose dropped columns hold data. Note the numbers, then `php spark migrate`. Migration `2026-01-01-003600_CleanResearchLogs` always runs and **deletes data for good** (owner, 3 November 2026: the production server holds no study data yet); an audit row `research_log_cleanup` keeps the counts. `2026-01-01-003700_CreateFieldSheets` adds the field sheet tables. Check `003600` once on MariaDB with a copy of the database (SQLite tests rebuild `device_logs` when columns are dropped).
- `SystemResetService::REQUIRED_MIGRATION` = `2026-01-01-003700` (coordinator sets the final value after part 4).
- `php spark gelita:lembar:templat --keluar=lembar/` (blank L1–L8, L7 included), `--l7 --keluar=…` (L7 only), `--kolom=kolom.json` (column spec for the static site). Refuses in production.
- `Config\Gelita::$emptyTapLimit` (30) is the cap of `empty_tap` events per clue per attempt; check it in KG3a and KG3b and fix it before G4.

## Research revision 10, part 4: consent, data rights, and research privacy

Part 4 (`docs/revisi-10-analitik/30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md`) adds migration `2026-01-01-003800_AddConsentAndDataRights` (new table `participant_consents`; new columns on `participants`, `research_studies`, `data_deletion_requests`, `data_exports`). It removes no data and does not empty `ci_sessions`; run it outside class hours like any migration. Everything in part 4 stays off until a study has field mode on.

### Research install checklist (before children first use their accounts)

1. **`gelita.researchPrivacy = true`** in `.env`. The study form refuses to save or start a field-mode study while it is off. Turn it on outside class hours (students with an old raw-IP session sign in again), then run `php spark gelita:retention:run` once: it removes expired `ci_sessions` rows and every row whose `ip_address` is not a 64-character hash. Since 21 November 2026 `Config\Events` switches PHP's session garbage collector on for every web request; the daily retention task stays as the second cleaner.
2. **Second backup location 30 days.** Set `KeduaHari = 30` in `config.psd1`. Each run then deletes files older than that at the second location, and writes `writable/backup-status.json` (`ran_at`, `kedua_hari`). Tata kelola warns while a field-mode study exists and the status file reports no limit.
3. **Retention.** A field-mode study keeps at least 7305 days, counted from `closed_at` (filled when the study status becomes *Selesai*, emptied when it is opened again before the identities are replaced). Until it is closed it never gets a retention preview; afterwards the preview covers the whole cohort, students included.
4. Sign in once and check that `ci_sessions.ip_address` holds a 64-character hash.

### Backup replacement after a deletion or the study closure

After the admin has run a parent's request (or replaced the identities at study closure), and before the parents are told:

```powershell
powershell -File deploy\windows\backup.ps1 -Config C:\gelita-ops\config.psd1 -Ganti
```

The script makes a fresh backup as usual and tests it (opening the zip). It copies it to the second location; if that location cannot be reached it stops with code 2 and deletes nothing. `backup.ps1` creates the second-location folder when it is missing, so another drive that sits on the same drive letter is taken for it without a warning (noted in 30 §7.5, not changed): check that the right drive is connected before running it. Then it deletes every older `gelita-*` file at both locations and prints how many went. Snapshots the hosting provider makes are removed in the provider's panel. Tick *Cadangan diganti* on the request afterwards.

### Scheduled tasks

`gelita:retention:run` gains one step with the switch on: `expired_browser_sessions` in the `retention_run` audit summary.


## Research revision 10, part 7: migration `004100`

- `2026-01-01-004100_AddResearchModeData` adds `research_studies.idle_threshold_ms` (180000), `rapid_guess_ms` (3000), `min_stage_px` (960), `test_rapid_guess_ms` (3000); `challenge_attempts.is_retake` (filled for old retakes); `audio_usage_events.clock_offset_ms` (old rows empty); and the table `challenge_attempt_segments` (FKs `ON DELETE CASCADE` to attempts and game sessions, `SET NULL` to field sessions), with one segment per existing attempt (`left_via = migration`). Nothing is deleted. `gelita:reset` needs this migration in the history.
- New config `gelita.inputIdleLogMs` (20000): the logging limit of `input_idle`, not an analysis threshold.
- The JSON after-filter of `/api` leaves `image/*` responses alone (Cari Objek pictures). nginx needs nothing new: the pictures are served by PHP with `Cache-Control: private, max-age=86400`.
- Asset packages: the public manifest names Cari Objek pictures (find_object items, post scenes) `cari.{sha256[0:16]}` instead of their asset key; rebuild the package after the upgrade.
## Research revision 10, part 8: migration `004200` and the instrument content

Migration `2026-01-01-004200_CreateInstrumentModule` adds seven `instrument_*` tables, `field_sessions.test_started_at`/`test_started_by`, `research_studies.instrument_settings`, and makes `participant_feedback.rating` nullable (`down()` writes 0 into empty ratings). After migrating, load the content once with `php spark gelita:instrumen:impor` (a reset does it itself) and install the 24 test pictures with `php spark gelita:katalog:impor` (`instr.*` slots; without them the module shows text). `gelita:retention:run` also closes test blocks past their hidden limit (`closed_test_blocks`). The validators' test install may set `gelita.instrumentPreview = true` in `.env`; never on a research install. The Bootstrap copy is CSS only (`public/assets/vendor/bootstrap-5.3.8/`).

## Research revision 10, part 9: migration `004300` and the derived data layer

Migration `2026-01-01-004300_CreateDerivedLayer` adds `derived_runs`, the eight `derived_*` tables (cascading from their run and from `participants`), `research_studies.thresholds_locked_at`/`derived_run_id`/`derived_running_since`, and `data_exports.derived_run_id`. It deletes nothing; `SystemResetService::REQUIRED_MIGRATION` is `2026-01-01-004300`. Nothing needs to be computed after migrating: the first download of a field-mode study (or *Hitung ulang*, or `php spark gelita:turunan:hitung --studi=KODE`) computes the first run. `--cek` only reports whether the current run is stale. The run is synchronous; measured on simulated data it takes about 9 ms per student (SQLite, 120 students, ~180 events each), so a study of ~360 children with ~2000 events each is expected to stay well under the 10-minute limit of 35 §18 no. 4 (check on the real server, 35 §16.3).
