---
name: dev-install
description: Start GELITA on MariaDB inside the session and run the browser checks of tools/qa. Use when a change has to be seen working in the real app, for checks SQLite cannot do (row locks, migrations, real requests), or when the owner asks to run, open or screenshot the app.
---

# Development install and browser checks

Never against a production server. This is the session's own database.

## Start

1. Run `tools/dev/serve.sh` in the background. It starts MariaDB, creates the database `gelita`, writes `.env`, migrates, seeds an empty database, and serves on port 8080 (`PORT=…` for another port).
2. Wait until `curl -s -o /dev/null -w '%{http_code}' localhost:8080/masuk` prints `200`.
3. Staff sign in at `/admin/login`; the seeded admin password is the `GELITA_ADMIN_PASSWORD` default in `tools/dev/serve.sh`. Students for a check: `php spark gelita:simulasi` or `php spark gelita:uji:akun --buat`.

## Pick the check

Each script's header comment lists its options and environment variables (`QA_BASE_URL`, `QA_MYSQL`, …); read it before running. Run with `node tools/qa/<script>`.

| The change touches | Script |
|---|---|
| Stage layout, 16:9, CSS of game screens | `stage-check.mjs` |
| Challenge engines: keyboard, focus, labels, dialogs | `a11y-check.mjs` |
| Challenge client and server under racing requests | `challenge-races.mjs` |
| Staff panel pages and what each role may open | `admin-check.mjs` |
| Research events, offline logging | `event-integrity.mjs`, `event-requests.mjs` |
| Asset package, service worker, download screen | `package-check.mjs`, `package-screens.mjs` |
| Screen transitions, language switch, timing | `transition-check.mjs`, `nav-timing.mjs` |
| Background music (`core/audio.js`) | `music-check.mjs` |
| Minified JS (`php spark gelita:aset:minify` first) | `minify-check.mjs` |

`load-test.mjs` with `load-students.php` measures a server behind Nginx and PHP-FPM; run it only when the owner asks.

Chromium and Playwright are installed (`/opt/pw-browsers`); never run `playwright install`. For a one-off look, a few lines of Playwright that open the page and take a screenshot are enough.

## Stop

1. `pkill -f '0.0.0.0:8080'` (the server runs as a child process that `spark serve` leaves behind), then `service mariadb stop`.
2. `rm .env` before running the test suite, so results match CI.
3. In the report: which script ran, against which screens, what passed, and what was not looked at.
