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
