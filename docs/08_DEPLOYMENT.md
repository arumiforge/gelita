# 08_DEPLOYMENT.md — Deployment & Operasional

> **Revisi 2 (21 September 2026).** Ditambahkan: langkah impor workbook bank soal saat deployment awal, uji registrasi/login siswa berkata sandi dalam verifikasi pasca-deploy, prosedur kelas untuk pembuatan kata sandi dan reset sandi oleh guru, serta pembaruan tabel masalah umum (sandi lupa, akun terkunci) menggantikan alur kode peserta.

---

## Tujuan

Menjalankan GELITA sebagai sistem production: menyiapkan server, mengonfigurasi environment, melakukan deployment dari server kosong sampai aplikasi siap dipakai kelas, menata aset, dan menetapkan langkah operasional harian.

---

## Konteks

GELITA dipakai di dua situasi yang berbeda, dan keduanya harus didukung:

| Skenario | Gambaran | Catatan |
|---|---|---|
| **Server sekolah / VPS** | satu instansi dipakai banyak kelas, guru mengakses dashboard dari mana saja | konfigurasi utama dokumen ini |
| **Laptop guru / jaringan lokal** | satu laptop menjalankan aplikasi, siswa mengakses lewat WiFi lokal | tidak ada HTTPS publik, tetapi tetap butuh sesi dan backup |

Skenario kedua penting: penelitian pendidikan sering berlangsung di sekolah yang internetnya tidak dapat diandalkan. Aplikasi ini **tidak** dirancang untuk berjalan dari `file://` seperti prototipe statis — ia memerlukan PHP dan MySQL — tetapi harus dapat berjalan dari satu laptop di jaringan lokal.

---

## Production Environment

### Spesifikasi minimum

| Komponen | Minimum | Disarankan |
|---|---|---|
| CPU | 2 core | 4 core |
| RAM | 2 GB | 4 GB (export XLSX besar butuh ruang) |
| Disk | 20 GB | 50 GB (aset gambar/video + raw event) |
| OS | Ubuntu 22.04 LTS / Debian 12 | Ubuntu 24.04 LTS |

### Perangkat lunak

| Komponen | Versi |
|---|---|
| PHP | 8.2 atau 8.3 (FPM) |
| CodeIgniter | 4.7.x |
| MySQL | 8.0+ **atau** MariaDB 10.6+ |
| Web server | Nginx 1.22+ (disarankan) atau Apache 2.4 + mod_rewrite |
| Composer | 2.6+ |

### Ekstensi PHP wajib

```
intl mbstring json mysqlnd curl gd zip fileinfo openssl iconv
dom xml xmlwriter simplexml
```

Pemasangan di Ubuntu:

```bash
sudo apt update
sudo apt install -y nginx mysql-server \
  php8.3-fpm php8.3-mysql php8.3-intl php8.3-mbstring php8.3-curl \
  php8.3-gd php8.3-zip php8.3-xml php8.3-bcmath \
  unzip git
```

Verifikasi:

```bash
php -v
php -m | grep -E 'intl|mbstring|gd|zip|xmlwriter|mysqlnd'
```

Bila salah satu tidak ada, hentikan di sini. PhpSpreadsheet gagal tanpa `zip` dan `xmlwriter`; mPDF gagal tanpa `mbstring`; CodeIgniter menolak jalan tanpa `intl`.

### Pengaturan PHP production

`/etc/php/8.3/fpm/conf.d/99-gelita.ini`:

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

`opcache.validate_timestamps = 0` mempercepat produksi tetapi berarti **PHP-FPM harus di-reload setiap deploy**. Ini sudah masuk skrip deploy di bawah.

### Pengaturan MySQL

`/etc/mysql/mysql.conf.d/99-gelita.cnf`:

```ini
[mysqld]
character-set-server = utf8mb4
collation-server     = utf8mb4_unicode_ci
default-time-zone    = '+07:00'
innodb_buffer_pool_size = 1G          # sesuaikan: ~50% RAM
innodb_flush_log_at_trx_commit = 1    # jangan dikendurkan; ini data penelitian
max_connections = 100
sql_mode = STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION
```

`innodb_flush_log_at_trx_commit = 1` memang lebih lambat, tetapi data penelitian yang hilang karena listrik padam tidak dapat diulang.

Buat database dan pengguna dengan hak terbatas:

```sql
CREATE DATABASE gelita CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'gelita_app'@'localhost' IDENTIFIED BY 'SANDI_KUAT_DI_SINI';
GRANT SELECT, INSERT, UPDATE, DELETE ON gelita.* TO 'gelita_app'@'localhost';

-- pengguna terpisah untuk migration; dipakai hanya saat deploy
CREATE USER 'gelita_migrate'@'localhost' IDENTIFIED BY 'SANDI_LAIN';
GRANT ALL PRIVILEGES ON gelita.* TO 'gelita_migrate'@'localhost';
FLUSH PRIVILEGES;
```

Akun aplikasi sengaja tidak punya `DROP`, `ALTER`, atau `CREATE`. Bila aplikasi diretas, skema database tidak ikut menjadi korban.

### Permissions

```bash
sudo chown -R deploy:www-data /var/www/gelita
sudo find /var/www/gelita -type d -exec chmod 755 {} \;
sudo find /var/www/gelita -type f -exec chmod 644 {} \;

# hanya writable yang boleh ditulis web server
sudo chmod -R 775 /var/www/gelita/writable
sudo chown -R deploy:www-data /var/www/gelita/writable

# folder unggahan aset
sudo chmod -R 775 /var/www/gelita/public/assets/uploads
sudo chown -R deploy:www-data /var/www/gelita/public/assets/uploads

# .env tidak boleh terbaca siapa pun selain pemilik
sudo chmod 600 /var/www/gelita/.env
sudo chown deploy:deploy /var/www/gelita/.env
```

`app/`, `system/`, dan `vendor/` **tidak** perlu dapat ditulis web server.

---

## Configuration

### `.env` production

```ini
CI_ENVIRONMENT = production

app.baseURL = 'https://gelita.sekolah.sch.id/'
app.forceGlobalSecureRequests = true
app.appTimezone = 'Asia/Jakarta'
app.defaultLocale = 'id'
app.indexPage = ''

database.default.hostname = localhost
database.default.database = gelita
database.default.username = gelita_app
database.default.password = 'SANDI_KUAT_DI_SINI'
database.default.DBDriver = MySQLi
database.default.port = 3306
database.default.charset = utf8mb4
database.default.DBCollat = utf8mb4_unicode_ci

encryption.key = hex2bin:ISI_DENGAN_HASIL_key:generate

session.driver = 'CodeIgniter\Session\Handlers\DatabaseHandler'
session.savePath = 'ci_sessions'
session.cookieName = 'gelita_session'
session.expiration = 14400
session.regenerateDestroy = true

security.csrfProtection = 'session'
security.tokenRandomize = true
security.tokenName = 'gelita_csrf'
security.headerName = 'X-CSRF-TOKEN'
security.expires = 7200
security.regenerate = false

cookie.samesite = 'Lax'
cookie.secure = true
cookie.httponly = true

logger.threshold = 4

gelita.ipSalt = 'ISI_DENGAN_32_BYTE_ACAK'
gelita.exportRetentionDays = 7
gelita.maxEventsPerBatch = 50
gelita.assetVersion = '1'
```

Yang **wajib** diubah sebelum dipakai:

1. `app.baseURL` — sesuai domain atau IP sungguhan.
2. `database.default.password`.
3. `encryption.key` — hasil `php spark key:generate`.
4. `gelita.ipSalt` — hasil `openssl rand -hex 32`. Bila ini bocor, `ip_hash` dapat dibalik dengan brute force karena ruang alamat IP kecil.
5. Kata sandi akun admin pertama.

`app.forceGlobalSecureRequests = true` hanya bila HTTPS benar-benar aktif. Pada jaringan lokal tanpa sertifikat, setel `false` dan `cookie.secure = false`, lalu batasi akses ke jaringan itu saja.

### Nginx

`/etc/nginx/sites-available/gelita`:

```nginx
server {
    listen 80;
    server_name gelita.sekolah.sch.id;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name gelita.sekolah.sch.id;

    root /var/www/gelita/public;
    index index.php;

    ssl_certificate     /etc/letsencrypt/live/gelita.sekolah.sch.id/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/gelita.sekolah.sch.id/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_prefer_server_ciphers off;

    client_max_body_size 72M;

    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header Referrer-Policy "same-origin" always;
    add_header Strict-Transport-Security "max-age=31536000" always;

    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_read_timeout 300;
    }

    # Aset statis: cache panjang, di-bust lewat ?v=
    location ~* \.(css|js|jpg|jpeg|png|gif|webp|svg|woff2|woff|ttf|mp3|mp4)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    # Jangan pernah melayani berkas ini
    location ~ /\.(env|git) { deny all; return 404; }
    location ~ ^/(app|system|writable|tests|vendor)/ { deny all; return 404; }

    gzip on;
    gzip_types text/css application/javascript application/json image/svg+xml;
    gzip_min_length 1024;
}
```

Seluruh `writable/` berada di luar document root karena `root` menunjuk ke `public/`. Blok `deny` di atas adalah lapisan kedua untuk berjaga-jaga bila suatu saat ada yang salah menyetel `root`.

### Apache

Bila memakai Apache, `DocumentRoot` menunjuk `public/`, `AllowOverride All` aktif, dan `.htaccess` bawaan CodeIgniter dipakai apa adanya. Tambahkan di VirtualHost:

```apache
<Directory /var/www/gelita/public>
    AllowOverride All
    Require all granted
</Directory>
<Directory /var/www/gelita/app>
    Require all denied
</Directory>
<Directory /var/www/gelita/writable>
    Require all denied
</Directory>
```

---

## Deployment

### Deployment pertama (server kosong)

```text
Siapkan server (PHP, MySQL, Nginx)
   ↓
Buat database + dua akun MySQL
   ↓
Ambil source ke /var/www/gelita
   ↓
composer install --no-dev --optimize-autoloader
   ↓
cp env .env  →  isi .env production
   ↓
php spark key:generate
   ↓
php spark migrate                      (34 migration: 29 tabel domain + ci_sessions;
                                        tabel sesi sudah ada di migration 003000 —
                                        JANGAN menjalankan php spark session:migration)
   ↓
GELITA_ADMIN_PASSWORD='…' php spark db:seed DatabaseSeeder
   ↓
php spark gelita:media:scan            (daftarkan aset yang ada)
   ↓
php spark gelita:bank:import bank-soal.xlsx --dry-run   (pratinjau galat)
   ↓
php spark gelita:bank:import bank-soal.xlsx             (muat 119 butir)
   ↓
php spark gelita:content:verify        (pastikan 3 level × 5 node, bank cukup)
   ↓
set permissions writable/ dan public/assets/uploads/
   ↓
konfigurasi Nginx + reload
   ↓
aktifkan HTTPS (certbot)
   ↓
pasang cron retention + backup
   ↓
ganti kata sandi admin lewat panel
   ↓
aplikasi siap
```

Perintah lengkap:

```bash
cd /var/www
sudo -u deploy git clone <repo-url> gelita
cd gelita

composer install --no-dev --optimize-autoloader

cp env .env
sudo nano .env                      # isi baseURL, database, key, ipSalt
php spark key:generate

# Migration memakai akun ber-hak penuh
DB_USER=gelita_migrate php spark session:migration
DB_USER=gelita_migrate php spark migrate
GELITA_ADMIN_PASSWORD='SandiAdminPertama!2026' \
  DB_USER=gelita_migrate php spark db:seed DatabaseSeeder

php spark gelita:media:scan

# Bank soal: workbook disusun dari Dokumen Bank Soal GELITA (format di 07, FITUR 12a).
# Bisa juga lewat panel: /admin/konten/impor-bank
php spark gelita:bank:import writable/uploads/bank-soal.xlsx --dry-run
php spark gelita:bank:import writable/uploads/bank-soal.xlsx
php spark gelita:content:verify

sudo chown -R deploy:www-data writable public/assets/uploads
sudo chmod -R 775 writable public/assets/uploads
sudo chmod 600 .env

sudo ln -s /etc/nginx/sites-available/gelita /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
sudo certbot --nginx -d gelita.sekolah.sch.id
```

### Deployment pembaruan

```bash
#!/usr/bin/env bash
# deploy.sh — jalankan sebagai user deploy
set -euo pipefail
cd /var/www/gelita

echo "→ Backup database"
./backup.sh

echo "→ Mode pemeliharaan"
php spark down --message "GELITA sedang diperbarui. Coba lagi beberapa menit lagi."

echo "→ Ambil kode terbaru"
git pull --ff-only origin main

echo "→ Dependency"
composer install --no-dev --optimize-autoloader

echo "→ Migration"
DB_USER=gelita_migrate php spark migrate

echo "→ Bersihkan cache"
php spark cache:clear

echo "→ Naikkan versi aset"
# menaikkan gelita.assetVersion agar browser mengambil CSS/JS baru
sed -i "s/^gelita.assetVersion.*/gelita.assetVersion = '$(date +%s)'/" .env

echo "→ Reload PHP-FPM"
sudo systemctl reload php8.3-fpm

echo "→ Aktifkan kembali"
php spark up

echo "→ Verifikasi konten"
php spark gelita:content:verify
```

**Jangan pernah melakukan deploy saat ada kelas berjalan.** Periksa lebih dulu:

```sql
SELECT COUNT(*) FROM game_sessions
WHERE status = 'active' AND last_active_at > NOW() - INTERVAL 10 MINUTE;
```

Bila hasilnya bukan nol, tunda.

### Rollback

```bash
cd /var/www/gelita
php spark down
git reset --hard <commit-sebelumnya>
composer install --no-dev --optimize-autoloader

# Bila deploy menyertakan migration:
DB_USER=gelita_migrate php spark migrate:rollback -b <batch-sebelumnya>
# atau pulihkan dari backup bila migration tidak reversible:
# gunzip -c backup/gelita-YYYYmmdd-HHMM.sql.gz | mysql -u gelita_migrate -p gelita

sudo systemctl reload php8.3-fpm
php spark up
```

Migration yang menghapus kolom sulit di-rollback tanpa kehilangan data. Aturannya: migration yang menghapus kolom atau tabel **tidak pernah digabung** dalam deploy yang sama dengan perubahan lain, dan selalu didahului satu rilis yang berhenti memakai kolom itu.

---

## Assets

### Lokasi

| Jenis | Path | Dilayani | Keterangan |
|---|---|---|---|
| CSS | `public/assets/css/` | publik | 6 berkas, di-bust `?v=` |
| JavaScript | `public/assets/js/` | publik | ES modules, `core/`, `game/`, `engines/`, `admin/` |
| Vendor JS | `public/assets/vendor/` | publik | `echarts.min.js`, `howler.min.js` |
| Font | `public/assets/fonts/` | publik | Cinzel, Plus Jakarta Sans, IBM Plex Mono — di-host sendiri |
| Data referensi | `public/assets/data/wilayah-id.json` | publik | provinsi & kabupaten |
| Gambar bawaan | `public/assets/{ui,char,bg,map,challenge,library,reward}/` | publik | dikirim bersama source |
| Audio | `public/assets/audio/` | publik | narasi, efek, musik |
| Unggahan admin | `public/assets/uploads/` | publik | nama berkas resmi dari `asset_key` |
| Berkas export | `writable/exports/` | **tidak publik** | dilayani controller berotorisasi |
| Log | `writable/logs/` | tidak publik | |
| Cache | `writable/cache/` | tidak publik | |

### Cache-busting

Setiap URL aset memakai `asset_url_versioned()` yang menambahkan `?v={gelita.assetVersion}`. Skrip deploy menaikkan nilai itu, sehingga browser mengambil versi baru tanpa perlu hard-refresh — penting karena komputer sekolah jarang dibersihkan cache-nya.

Untuk aset yang diunggah admin, pratinjau memakai `?v={sha256 8 karakter pertama}` agar berubah begitu berkasnya diganti.

### Ukuran aset

Aset gambar dan video mendominasi ukuran instalasi. Anjuran:

* Latar dan adegan: JPEG kualitas 80, maksimum 1920×1080.
* Objek dan opsi: PNG dengan transparansi, maksimum 400×400.
* Video pustaka: MP4 H.264, 720p, bitrate ≤ 1.5 Mbps, durasi ≤ 60 detik.
* Audio narasi: MP3 96–128 kbps mono.

Ukuran piksel resmi tiap slot ada di `Config\Gelita::$assetSizes` dan ditegakkan saat unggah.

---

## Cron

`crontab -e` sebagai user `deploy`:

```cron
# Retensi & pembersihan, tiap hari 02:00 WIB
0 2 * * * cd /var/www/gelita && php spark gelita:retention:run >> writable/logs/cron.log 2>&1

# Backup database, tiap hari 01:00 dan setiap 4 jam pada jam kerja
0 1 * * *  /var/www/gelita/backup.sh >> /var/log/gelita-backup.log 2>&1
0 8,12,16 * * 1-5 /var/www/gelita/backup.sh >> /var/log/gelita-backup.log 2>&1

# Rotasi log aplikasi, tiap Minggu
0 3 * * 0 find /var/www/gelita/writable/logs -name "log-*.log" -mtime +30 -delete
```

Backup lebih sering pada jam sekolah disengaja: sesi kelas berlangsung pada jam itu, dan kehilangan satu sesi berarti mengulang satu kelas.

---

## Backup

`backup.sh`:

```bash
#!/usr/bin/env bash
set -euo pipefail

STAMP=$(date +%Y%m%d-%H%M)
DIR=/var/backups/gelita
mkdir -p "$DIR"

# 1. Database
mysqldump -u gelita_migrate -p"$MYSQL_PASS" \
  --single-transaction --quick --routines --triggers \
  gelita | gzip > "$DIR/gelita-db-$STAMP.sql.gz"

# 2. Aset yang diunggah admin (tidak ada di git)
tar czf "$DIR/gelita-uploads-$STAMP.tar.gz" \
  -C /var/www/gelita/public/assets uploads

# 3. Konfigurasi
cp /var/www/gelita/.env "$DIR/gelita-env-$STAMP"

# 4. Simpan 30 hari
find "$DIR" -name 'gelita-*' -mtime +30 -delete

# 5. Salin ke lokasi kedua (drive eksternal / rsync ke mesin lain)
rsync -a "$DIR/" /mnt/backup-eksternal/gelita/ || \
  echo "PERINGATAN: salinan kedua GAGAL pada $STAMP"
```

**Backup yang hanya ada di mesin yang sama dengan datanya bukan backup.** Langkah 5 wajib diarahkan ke drive eksternal, NAS sekolah, atau mesin lain.

Uji pemulihan minimal sekali sebelum penelitian dimulai:

```bash
mysql -u root -p -e "CREATE DATABASE gelita_uji;"
gunzip -c /var/backups/gelita/gelita-db-YYYYmmdd-HHMM.sql.gz | mysql -u root -p gelita_uji
mysql -u root -p gelita_uji -e "SELECT COUNT(*) FROM participants; SELECT COUNT(*) FROM game_event_logs;"
mysql -u root -p -e "DROP DATABASE gelita_uji;"
```

Backup yang belum pernah dipulihkan statusnya belum diketahui.

---

## Verifikasi Setelah Deploy

Jalankan seluruh daftar ini setiap kali deploy selesai:

```bash
# 1. Struktur konten
php spark gelita:content:verify
# harus: 3 level, 5 node per level, semua engine valid, bank item cukup

# 2. Database
mysql -u gelita_app -p gelita -e "
  SELECT COUNT(*) AS levels FROM levels WHERE is_active=1;
  SELECT COUNT(*) AS nodes  FROM challenge_nodes WHERE is_active=1;
  SELECT release_code FROM game_releases WHERE is_active=1;
  SELECT code, version FROM scoring_profiles WHERE is_active=1;"
# harus: 3, 15, satu rilis aktif, satu profil aktif

# 3. Tulis writable
sudo -u www-data touch /var/www/gelita/writable/uji && \
  rm /var/www/gelita/writable/uji && echo "writable OK"

# 4. HTTPS & header
curl -sI https://gelita.sekolah.sch.id/ | grep -E 'HTTP/|Strict-Transport|X-Frame'

# 5. Berkas sensitif tidak terlayani
for p in .env app/Config/App.php writable/logs/ vendor/autoload.php; do
  code=$(curl -s -o /dev/null -w '%{http_code}' "https://gelita.sekolah.sch.id/$p")
  echo "$p → $code"      # semuanya harus 403 atau 404
done
```

Uji manual:

1. Buka `/` — layar welcome tampil, gambar termuat.
2. Daftar siswa uji: coba sandi `kedu2026` — ditolak dengan keterangan syarat yang belum terpenuhi; lalu `Kedu#2026` — diterima. Keluar, lalu masuk kembali di `/masuk` — progres tetap.
2a. Reset sandi siswa uji dari `/admin/peserta/{id}` — login dengan sandi sementara langsung diarahkan ke `/ganti-sandi`.
3. Kerjakan satu tantangan sampai selesai — bintang dan skor muncul.
4. Ganti bahasa ke EN di tengah permainan — progres tetap, teks berubah.
5. Masuk `/admin/login` — dashboard tampil, chart tergambar.
6. Buat export XLSX kecil — berkas terunduh dan terbuka di Excel.
7. Buka `/admin/sesi/{id}/event` — linimasa event sesi uji terlihat.
8. Hapus peserta uji lewat `/admin/tata-kelola` dengan pratinjau dan konfirmasi.
9. Buka `/admin/konten` — 15 node tampil dengan jumlah bank sesuai workbook; item `needs_verification` tertandai kuning.

Baru setelah seluruh langkah ini lolos, aplikasi dinyatakan siap dipakai kelas.

---

## Operational Notes

### Sebelum sesi kelas

1. Tetapkan **fase penelitian** yang benar di `/admin/studi` (`active_phase_code` = `umum` / `pretest` / `posttest`) sebelum anak mulai. Fase sebuah sesi tidak dapat diubah setelah ada event gameplay.
2. Untuk desain pretest–posttest, pastikan `item_selection_mode = fixed`. Tanpa ini, butir soal diacak dan kedua sesi tidak setara.
3. Periksa `php spark gelita:content:verify` bersih.
4. Pastikan ruang disk cukup: `df -h`.
5. Jalankan backup manual: `./backup.sh`.
6. Sediakan **10–15 menit** di awal pertemuan pertama untuk registrasi. Membuat kata sandi kuat adalah bagian pembelajaran literasi keamanan digital, bukan hambatan teknis: biarkan anak membaca syaratnya dan memperbaiki sendiri sandi yang ditolak.
7. Pastikan guru tahu cara mereset sandi di `/admin/peserta/{id}`. Jangan meminta anak menuliskan sandinya di daftar kelas — bila lupa, reset.
8. Untuk anak kelas bawah yang kesulitan mengetik simbol, tunjukkan letak tombol simbol di papan ketik atau papan ketik layar tablet sebelum mulai.

### Setelah sesi kelas

1. Unduh export XLSX sebagai salinan kerja.
2. Periksa `/admin/sesi` — sesi yang masih `active` padahal kelas sudah selesai menandakan tab yang tidak ditutup; biarkan, retention akan menandainya `paused`.
3. Catat hal tak biasa (anak yang berhenti di tengah, perangkat bermasalah) di catatan penelitian, bukan di aplikasi.

### Pemantauan harian

```bash
# Galat aplikasi hari ini
grep -c 'ERROR' /var/www/gelita/writable/logs/log-$(date +%Y-%m-%d).log

# Pertumbuhan event
mysql -u gelita_app -p gelita -e "
  SELECT DATE(server_received_at) d, COUNT(*) n
  FROM game_event_logs
  WHERE server_received_at > NOW() - INTERVAL 7 DAY
  GROUP BY d ORDER BY d DESC;"

# Ukuran tabel terbesar
mysql -u gelita_app -p gelita -e "
  SELECT table_name, ROUND(data_length/1048576) data_mb,
         ROUND(index_length/1048576) idx_mb
  FROM information_schema.tables
  WHERE table_schema='gelita'
  ORDER BY data_length DESC LIMIT 5;"

# Disk
df -h /var/www /var/backups
```

`game_event_logs` akan menjadi tabel terbesar dan itu normal — ia memang catatan proses penelitian. Perkiraan kasar: satu peserta menyelesaikan 15 node menghasilkan sekitar 400–800 event, sekitar 0,5–1 MB termasuk index. Seratus peserta ≈ 100 MB. Jangan menghapusnya hanya karena agregat dashboard sudah jadi.

### Masalah umum

| Gejala | Penyebab biasa | Tindakan |
|---|---|---|
| Halaman putih | galat PHP, `display_errors` mati | baca `writable/logs/log-*.log` |
| "Unable to connect to database" | kredensial `.env` salah atau MySQL mati | `systemctl status mysql`, uji login manual |
| Export gagal di tengah | `memory_limit` atau `max_execution_time` | persempit rentang tanggal; naikkan batas untuk PHP-FPM |
| Gambar tidak muncul | aset belum diunggah atau `is_active = 0` | `/admin/media` → status hilang; `php spark gelita:media:scan` |
| Audio tidak berbunyi | status masih `draft` | `/admin/media/audio` → Setujui |
| CSRF "action not allowed" | tab dibiarkan terbuka semalam | muat ulang halaman; `security.expires` 2 jam |
| Anak keluar / pindah komputer | session berakhir, tab tertutup | masuk lagi di `/masuk`; progres tetap |
| Anak lupa kata sandi | — | guru mereset di `/admin/peserta/{id}`; anak membuat sandi baru saat masuk |
| Anak lupa nama pengguna | — | guru mencarinya di `/admin/peserta` berdasarkan nama & sekolah |
| "Terlalu banyak percobaan" | 8 kali salah sandi | tunggu 5 menit, atau guru mereset sandi (reset juga membuka kunci) |
| Registrasi terus ditolak | sandi belum memenuhi 5 syarat atau memuat nama pengguna | baca daftar syarat yang masih bertanda ✗ bersama anak |
| Impor bank soal gagal | galat baris di workbook | buka pratinjau, perbaiki sheet & baris yang disebut, unggah ulang |
| Event tertahan | koneksi putus saat sesi | otomatis terkirim saat aplikasi dibuka lagi di perangkat itu |
| Disk penuh | export lama + backup + video | jalankan retention; pindahkan backup lama |

### Keamanan operasional

1. Ganti kata sandi admin bawaan **sebelum** aplikasi dipakai di sekolah.
2. Buat akun `guru` terpisah untuk tiap guru, dengan `school_id` terisi. Jangan membagikan akun admin.
3. Nonaktifkan akun guru yang tidak lagi terlibat (`is_active = 0`), jangan hapus — audit log-nya masih diperlukan.
4. Tinjau `/admin/tata-kelola/audit` secara berkala, terutama aksi `export` dan `delete_execute`.
5. Perbarui PHP dan sistem operasi rutin: `sudo apt update && sudo apt upgrade`.
6. Perbarui dependency Composer dan periksa kerentanan:

```bash
composer update --no-dev
composer audit
```

7. Berkas `.env` tidak pernah masuk repositori. Pastikan `.gitignore` memuat `.env`, `writable/`, `public/assets/uploads/`.

### Tata kelola data anak

Dataset ini memuat data pribadi anak. Yang harus disiapkan bersama institusi sebelum pengambilan data:

1. **Persetujuan** — teks consent versi final, dengan persetujuan orang tua/wali. Versinya disimpan bersama buktinya di `participant_consents`.
2. **Retensi** — tetapkan `research_studies.retention_days` sesuai kesepakatan dengan sekolah dan institusi. Nilai bawaan 1825 hari adalah titik awal, bukan keputusan.
3. **Akses terbatas** — guru hanya sekolahnya, ekspor guru selalu anonim, akun admin sesedikit mungkin.
4. **Ekspor pseudonim sebagai default** — analisis dilakukan dengan kode peserta; nama hanya dibuka bila memang diperlukan dan dicatat di audit log.
5. **Penghapusan yang dapat ditelusuri** — selalu lewat pratinjau, konfirmasi, dan audit.
6. **Penarikan persetujuan** — bila orang tua menarik persetujuan, isi `participant_consents.withdrawn_at` lalu buat permintaan penghapusan untuk peserta itu.
7. **Kata sandi anak** — disimpan hanya sebagai hash; guru dan admin tidak dapat melihatnya. Sandi sementara hasil reset hanya tampil sekali. Nama pengguna diperlakukan sebagai data identitas dan tidak ikut export anonim. Yang dianalisis hanya jumlah syarat sandi terpenuhi pada percobaan pertama dan jumlah penolakan sandi lemah.

Persetujuan etik dan penafsiran hukumnya tetap urusan institusi. Aplikasi menyediakan mekanismenya; kebijakannya ditetapkan manusia.

### Pemakaian di jaringan lokal (tanpa internet)

Untuk skenario satu laptop guru:

```bash
# 1. Pasang PHP + MySQL di laptop (XAMPP/Laragon di Windows, paket di Linux)
# 2. Deploy seperti biasa, tetapi:
#    app.baseURL = 'http://192.168.1.10/'      (IP laptop di WiFi lokal)
#    app.forceGlobalSecureRequests = false
#    cookie.secure = false
# 3. Pastikan firewall mengizinkan port 80 dari jaringan lokal
# 4. Siswa membuka http://192.168.1.10/ di tablet/komputer kelas
```

Catatan untuk skenario ini:

* Matikan sleep/hibernate pada laptop selama sesi berlangsung.
* Jalankan `./backup.sh` **setelah setiap kelas**, ke flashdisk.
* IP laptop dapat berubah bila DHCP menyewakan ulang; gunakan IP statis atau reservasi DHCP.
* Tanpa HTTPS, jangan menjalankan panel admin dari jaringan yang dapat diakses orang di luar kelas.

---

## Aturan Sistem

1. `.env` tidak pernah masuk repositori dan permissionnya `600`.
2. Akun MySQL aplikasi tidak punya hak DDL; migration memakai akun terpisah.
3. `writable/exports/` tidak pernah dapat diakses langsung lewat URL.
4. Deploy selalu didahului backup database, dan tidak pernah dilakukan saat ada sesi kelas aktif.
5. Setiap deploy menaikkan `gelita.assetVersion` dan me-reload PHP-FPM.
6. Backup disalin ke lokasi kedua; backup yang belum pernah diuji pulih dianggap belum ada.
7. Retensi tidak menghapus data penelitian secara otomatis — ia hanya membuat permintaan berstatus pratinjau untuk disetujui manusia.
8. Kata sandi admin bawaan diganti sebelum pemakaian nyata, dan tiap guru punya akun sendiri dengan `school_id`.
9. Raw event tidak dihapus karena dashboard sudah punya agregat.
10. Fase penelitian dan mode pemilihan butir disetel sebelum kelas dimulai, bukan sesudahnya.
11. Bank soal production dimuat lewat impor workbook dengan pratinjau; tidak ada penyuntingan langsung ke database.
12. Lupa sandi siswa diselesaikan dengan reset oleh guru, tidak pernah dengan mencatat sandi anak.

---

## Dependency

Dari **01_DATABASE.md**: skema, migration, seeder, daftar tabel dan perkiraan pertumbuhannya.

Dari **02_PROJECT_FOUNDATION.md**: daftar ekstensi PHP, isi `.env`, struktur folder, daftar spark command.

Dari **03–06**: seluruh kode aplikasi yang di-deploy.

Dari **07_FEATURE_INTEGRATION.md**: `ExportService`, `ReportService`, `RetentionService`, format workbook bank soal, dan perilaku command `gelita:retention:run`, `gelita:content:verify`, `gelita:media:scan`, `gelita:score:recompute`, `gelita:bank:import`.

Dari **Dokumen Bank Soal GELITA**: isi workbook `bank-soal.xlsx` untuk deployment awal.

---

## Hasil Akhir

Setelah tahap ini selesai:

* GELITA berjalan di server production lewat HTTPS, dengan header keamanan aktif dan berkas sensitif tidak terlayani.
* Deployment pertama dan deployment pembaruan dapat dijalankan dengan satu skrip, lengkap dengan mode pemeliharaan dan rollback.
* Backup berjalan otomatis ke dua lokasi, dan prosedur pemulihannya sudah pernah diuji.
* Cron menjalankan retensi harian; data penelitian tidak pernah terhapus tanpa persetujuan manusia.
* Aplikasi juga dapat dijalankan dari satu laptop di jaringan lokal sekolah.
* Guru punya akun sendiri dengan cakupan sekolahnya dan dapat mereset sandi siswanya; admin dapat mengelola konten, ekspor, dan tata kelola data dengan jejak audit lengkap.
* Siswa registrasi dan masuk dengan nama pengguna serta kata sandi kuat; bank soal 15 node sudah dimuat dan lolos verifikasi konten.
* Delapan langkah verifikasi pasca-deploy lolos, dan aplikasi dinyatakan siap dipakai kelas.
