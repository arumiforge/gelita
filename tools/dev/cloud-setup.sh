#!/bin/bash
# Master copy of the setup script of the "Gelita" cloud environment (claude.ai/code →
# environment → Edit → Setup script). Runs once as root, one directory above the
# clone; the result is snapshotted. Paste changes made here into the environment too.
export DEBIAN_FRONTEND=noninteractive COMPOSER_ALLOW_SUPERUSER=1

# MariaDB for the checks SQLite cannot do (row locks, migrations). Not started here:
# the snapshot keeps files, not processes. Per session: service mariadb start
(apt-get update -qq && apt-get install -y -qq mariadb-server) >/dev/null 2>&1 || true

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
