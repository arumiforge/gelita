#!/bin/bash
# Master copy of the setup script of the "Gelita" cloud environment (claude.ai/code →
# environment → Edit → Setup script). Runs once as root, one directory above the
# clone; the result is snapshotted. Paste changes made here into the environment too.
export DEBIAN_FRONTEND=noninteractive COMPOSER_ALLOW_SUPERUSER=1

# MariaDB for the checks SQLite cannot do (row locks, migrations). Not started here:
# the snapshot keeps files, not processes. Per session: service mariadb start
(apt-get update -qq && apt-get install -y -qq mariadb-server) >/dev/null 2>&1 || true

# PHP 8.5 and Node 24, the server's versions since 23 November 2026 (owner); the image
# brings PHP 8.3 and Node 22. PHP comes from the ondrej/php PPA, the usual source of
# current PHP on Ubuntu (GitHub Actions' setup-php uses it too). The CLI default moves
# to 8.5; 8.3 stays installed. php8.5-fpm is for tools/qa/nav-timing.mjs and load tests.
(
    curl -fsSL 'https://keyserver.ubuntu.com/pks/lookup?op=get&search=0x14AA40EC0831756756D7F66C4F4EA0AAE5267A6C' \
        | gpg --batch --yes --dearmor -o /usr/share/keyrings/ondrej-php.gpg \
        && echo 'deb [signed-by=/usr/share/keyrings/ondrej-php.gpg] https://ppa.launchpadcontent.net/ondrej/php/ubuntu noble main' \
            > /etc/apt/sources.list.d/ondrej-php.list \
        && apt-get update -qq \
        && apt-get install -y -qq php8.5-cli php8.5-fpm php8.5-curl php8.5-gd php8.5-intl php8.5-mbstring \
            php8.5-mysql php8.5-sqlite3 php8.5-xml php8.5-zip \
        && update-alternatives --set php /usr/bin/php8.5
) >/dev/null 2>&1 || true

# Node 24 from nodejs.org, checked against its SHASUMS256. It replaces the node binary
# where the image keeps it (/opt/node22/bin), so PATH, npm and the global Playwright
# stay where they are; the folder keeps its old name.
NODE_VERSION=24.22.3
if [ "$(node -v 2>/dev/null | cut -d. -f1)" != v24 ]; then
    bin="$(dirname "$(command -v node || echo /opt/node22/bin/node)")"
    (
        tmp="$(mktemp -d)" && cd "$tmp" \
            && curl -fsSLO "https://nodejs.org/dist/v$NODE_VERSION/node-v$NODE_VERSION-linux-x64.tar.xz" \
            && curl -fsSL "https://nodejs.org/dist/v$NODE_VERSION/SHASUMS256.txt" \
                | grep " node-v$NODE_VERSION-linux-x64.tar.xz\$" | sha256sum -c - \
            && tar -xJf "node-v$NODE_VERSION-linux-x64.tar.xz" \
            && install -m 755 "node-v$NODE_VERSION-linux-x64/bin/node" "$bin/node"
        rm -rf "$tmp"
    ) >/dev/null 2>&1 || true
fi

cd gelita || exit 0

# Composer's zipballs come from api.github.com, which the session's GitHub proxy
# refuses for repositories not attached to the session (403). The fallback is a
# full clone of every dependency: ~10 minutes and ~12 GB. Seed the dist cache
# with shallow fetches instead (~15 seconds, 135 MB).
cache="$(composer config --global cache-files-dir 2>/dev/null)"
php -r '
foreach (["packages", "packages-dev"] as $k)
    foreach (json_decode(file_get_contents("composer.lock"), true)[$k] as $p)
        echo $p["name"], " ", $p["source"]["url"], " ", $p["source"]["reference"], " ", sha1($p["dist"]["url"]), "\n";
' | {
    while read -r name url ref key; do
        zip="$cache/$name/$key.zip"
        [ -s "$zip" ] && continue
        (
            tmp="$(mktemp -d)" && mkdir -p "$(dirname "$zip")" && git init -q "$tmp" \
                && git -C "$tmp" fetch -q --depth 1 "$url" "$ref" \
                && git -C "$tmp" archive --format=zip --prefix=pkg/ -o "$zip" FETCH_HEAD
            rm -rf "$tmp"
        ) &
    done
    wait
}
# Cap the fallback so a failed seed cannot push the script past the ~5 minute cache limit.
timeout 200 composer install --no-interaction --no-progress -q || true
exit 0
