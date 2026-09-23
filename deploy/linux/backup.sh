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
