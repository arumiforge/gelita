#!/bin/bash
# Development install on MariaDB in one command, for cloud sessions and Linux:
# database, .env, migrations, seed on an empty database, then the dev server.
# Never run this against a production server.
set -euo pipefail
cd "$(dirname "$0")/../.."

[ -d vendor ] || composer install --no-interaction --no-progress
pgrep -x mariadbd >/dev/null || service mariadb start

# The env template signs in as gelita_app with an empty password.
mariadb -e "CREATE DATABASE IF NOT EXISTS gelita CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
            CREATE USER IF NOT EXISTS 'gelita_app'@'localhost' IDENTIFIED BY '';
            GRANT ALL ON gelita.* TO 'gelita_app'@'localhost';"
fresh="$(mariadb -N -e "SELECT COUNT(*) = 0 FROM information_schema.tables WHERE table_schema = 'gelita'")"

if [ ! -f .env ]; then
    cp env .env
    php spark key:generate
fi

php spark migrate
if [ "$fresh" = 1 ]; then
    GELITA_ADMIN_PASSWORD="${GELITA_ADMIN_PASSWORD:-SandiKuatAnda123!}" php spark db:seed DatabaseSeeder
fi

exec php spark serve --host 0.0.0.0 --port "${PORT:-8080}"
