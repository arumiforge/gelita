#!/usr/bin/env bash
# deploy/linux/backup.sh — backup GELITA. Dijalankan cron milik user deploy.
set -euo pipefail
umask 077

OPS=/home/deploy/.gelita              # backup.cnf, migrate.dsn, ops.conf (chmod 600)
APP=/var/www/gelita
DIR=/var/backups/gelita               # milik deploy, chmod 700
KEDUA=/mnt/backup-eksternal/gelita    # drive eksternal / NAS / mesin lain
# Part 4 (30 §7.5, §0.1): days kept at the second location; empty = kept forever (as before).
# Research installs set KEDUA_HARI=30 in ops.conf.
KEDUA_HARI=
# --ganti: after a deletion or a study closure, a fresh backup replaces every older one at both locations (K3)
GANTI=0
[ "${1:-}" = "--ganti" ] && GANTI=1
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

# 2b. Asset packages from gelita:paket:bangun (docs/rencana-paket-aset.md §5.2),
#     only on the server: content-addressed media, per-version code, manifests,
#     URL maps. Folders that do not exist yet (no package built) are skipped.
PAKET=()
for p in public/assets/p public/assets/k public/paket writable/paket; do
  [ -d "$APP/$p" ] && PAKET+=("$p")
done
if [ ${#PAKET[@]} -gt 0 ]; then
  tar czf "$DIR/gelita-paket-$STAMP.tar.gz" -C "$APP" "${PAKET[@]}"
fi

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

# 6. Part 4 (30 §7.5): second location kept KEDUA_HARI days when set
if [ -n "$KEDUA_HARI" ]; then
  find "$KEDUA" -name 'gelita-*' -mtime +"$KEDUA_HARI" -delete
fi

# 7. --ganti: the fresh backup is tested above (gzip -t); now every older file goes at both locations.
#    rsync already reached the second location, so it is reachable; step 5 stops with code 2 otherwise.
if [ "$GANTI" = 1 ]; then
  removed=0
  for loc in "$DIR" "$KEDUA"; do
    while IFS= read -r -d '' f; do
      case "$(basename "$f")" in *"$STAMP"*) continue ;; esac
      rm -f -- "$f"; removed=$((removed + 1))
    done < <(find "$loc" -maxdepth 1 -name 'gelita-*' -print0)
  done
  echo "$STAMP ganti: $removed berkas cadangan lama dihapus; tersisa stempel $STAMP"
fi

# 8. Status for the governance page warning (30 §0.1)
printf '{"ran_at":"%s","kedua_hari":"%s","ganti":%s}\n' "$(date -Iseconds)" "$KEDUA_HARI" "$GANTI" > "$APP/writable/backup-status.json" || true
