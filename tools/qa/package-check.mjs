#!/usr/bin/env node
/**
 * Asset package Stage 2 check: service worker and in-page downloader
 * (docs/rencana-paket-aset.md §4.1, §5.5, §5.6, §6.2, §8.3–§8.7, §9.1, §11.3),
 * with real Chromium (Playwright). Covers what works without the Stage 3
 * download screen, by calling core/package.js from the page:
 *
 *   A  package on: first visit (no package) → interrupted download → resume
 *      without re-downloading finished files → switch; cache holds only
 *      manifest URLs + the offline page; reload serves every package URL
 *      from the service worker with data-assets-local and package_mode
 *      lengkap; /api never from the worker; sha256: verify() finds a broken
 *      cache entry, a broken server file fails after one retry naming its
 *      key; audio Range → 206/416; network failure → `network` error;
 *      offline navigation → offline page
 *   B  storage too small (navigator.storage.estimate stubbed): no download,
 *      `quota` error, online mode for this session → package_mode daring;
 *      `lab` refused while the server does not allow it
 *   C  no service worker: no download screen needed (status unsupported, daring)
 *   D  (needs --sqlite or --mysql) sign-up stores package_mode lengkap; a new
 *      package published mid-session is downloaded but not switched to until
 *      the session ends; the switch downloads no unchanged file (S8, S9)
 *   E  (needs --serve) server kill switch: pages register sw-kosong.js, no
 *      response comes from the worker, the device marker is dropped, files kept
 *   F  (needs --serve and a database) package mode off by release: normal
 *      URLs, nothing served or cached by the worker, status off
 *
 * Usage (local development database only, never production):
 *   php spark gelita:paket:bangun --tanpa-pustaka     # then make the active release name the package
 *   node tools/qa/package-check.mjs --serve --sqlite=/path/to/dev.db [--prefix=db_]
 *   node tools/qa/package-check.mjs --base=http://localhost:8080/ --mysql="mysql -uroot gelita"
 *
 * Options:
 *   --base=URL       app URL (QA_BASE_URL, default http://localhost:8080/)
 *   --serve          start PHP's built-in server (as `php spark serve`) on the base URL's
 *                    port; stop it for the network-failure check and restart it with
 *                    `gelita.paket.aktif=false` for E (uses the local .env)
 *   --sqlite=FILE    SQLite development database (read/written through php PDO)
 *   --prefix=db_     table prefix of that database (default: none)
 *   --mysql="CMD"    MySQL/MariaDB client command instead of --sqlite
 *   --keep           leave the second package of D active (default: restore the release)
 *
 * The script builds a second package for D (`gelita:paket:bangun`, same files, new code)
 * and edits `game_releases.asset_version` of the active release for D and F; the original
 * value is restored at the end. Chromium is preinstalled (PLAYWRIGHT_BROWSERS_PATH);
 * never `playwright install`. Exit code 0 when every check passes, 1 otherwise.
 */
import { createRequire } from 'node:module';
import { execFileSync, execSync, spawn } from 'node:child_process';
import { readFileSync, writeFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const require = createRequire(import.meta.url);
function loadPlaywright() {
  try { return require('playwright'); } catch { /* fall back to the global install */ }
  return require(`${execSync('npm root -g').toString().trim()}/playwright`);
}
const { chromium } = loadPlaywright();

const args = Object.fromEntries(process.argv.slice(2).map((arg) => {
  const [key, ...rest] = arg.replace(/^--/, '').split('=');
  return [key, rest.length ? rest.join('=') : true];
}));
const BASE = String(args.base || process.env.QA_BASE_URL || 'http://localhost:8080/').replace(/\/?$/, '/');
const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');
const PORT = new URL(BASE).port || '80';
const PREFIX = String(args.prefix || '');
const sleep = (ms) => new Promise((resolve) => { setTimeout(resolve, ms); });

const failures = [];
const check = (ok, label, detail = '') => {
  console.log(`${ok ? 'ok  ' : 'FAIL'} ${label}${detail !== '' ? ` — ${detail}` : ''}`);
  if (!ok) failures.push(label);
};
const skip = (label, why) => console.log(`skip ${label} — ${why}`);
const section = (title) => console.log(`\n== ${title}`);

// ------------------------------------------------------------ database

const t = (name) => `${PREFIX}${name}`;
function sql(query) {
  if (args.sqlite) {
    const code = `$p=new PDO('sqlite:'.$argv[1]);$s=$p->query($argv[2]);echo json_encode($s?$s->fetchAll(PDO::FETCH_ASSOC):[]);`;
    return JSON.parse(execFileSync('php', ['-r', code, String(args.sqlite), query]).toString() || '[]');
  }
  if (args.mysql) {
    const [cmd, ...rest] = String(args.mysql).split(/\s+/);
    const out = execFileSync(cmd, [...rest, '-B', '-e', query]).toString().trim().split('\n').filter(Boolean);
    const head = (out.shift() || '').split('\t');
    return out.map((line) => Object.fromEntries(line.split('\t').map((v, i) => [head[i], v])));
  }
  return null;
}
const hasDb = Boolean(args.sqlite || args.mysql);
const q = (value) => `'${String(value).replace(/'/g, "''")}'`;
const setRelease = (value) => sql(`UPDATE ${t('game_releases')} SET asset_version = ${q(value)} WHERE is_active = 1`);

// ------------------------------------------------------------ server

let server = null;
async function waitFor(up) {
  for (let i = 0; i < 100; i++) {
    const alive = await fetch(`${BASE}api/paket/aktif`).then(() => true, () => false);
    if (alive === up) return;
    await sleep(100);
  }
  throw new Error(`server did not ${up ? 'start' : 'stop'}`);
}
/**
 * The same server `php spark serve` starts (built-in server + CodeIgniter's
 * rewrite.php), but with $_ENV filled from the process environment, so
 * `gelita.paket.aktif=false` can be passed without touching .env.
 */
async function startServer(env = {}) {
  if (await fetch(BASE).then(() => true, () => false)) throw new Error(`something already answers on ${BASE}; pick a free port with --base`);
  const host = `${new URL(BASE).hostname}:${PORT}`;
  const router = path.join(ROOT, 'vendor/codeigniter4/framework/system/rewrite.php');
  server = spawn('php', ['-d', 'variables_order=EGPCS', '-S', host, '-t', path.join(ROOT, 'public'), router],
    { cwd: ROOT, env: { ...process.env, ...env }, detached: true, stdio: 'ignore' });
  await waitFor(true);
}
async function stopServer() {
  if (!server) return;
  try { process.kill(-server.pid, 'SIGTERM'); } catch { /* already gone */ }
  server = null;
  await waitFor(false);
}

// ------------------------------------------------------------ page helpers

/** Runs fn(pkg, arg) in the page with core/package.js from the page's own code folder. */
async function withPackage(page, fn, arg = null) {
  return page.evaluate(async ({ source, arg: value }) => {
    const entry = document.querySelector('script[type="module"][src*="/js/game.js"]').src;
    const pkg = await import(entry.replace(/js\/game\.js(\?.*)?$/, 'js/core/package.js$1'));
    // eslint-disable-next-line no-new-func
    return new Function('pkg', 'arg', `return (${source})(pkg, arg);`)(pkg, value);
  }, { source: fn.toString(), arg });
}

async function cacheReport(page) {
  return page.evaluate(async () => {
    const out = {};
    for (const name of await caches.keys()) {
      const cache = await caches.open(name);
      out[name] = (await cache.keys()).map((request) => request.url);
    }
    return out;
  });
}

async function gotoReady(page, url) {
  await page.goto(url, { waitUntil: 'load' });
  await page.waitForFunction(() => document.body.dataset.pageReady === '1', null, { timeout: 15000 }).catch(() => {});
}

/** Package URL responses of one navigation, with fromServiceWorker(). */
async function packageResponses(page, action) {
  const seen = [];
  const listener = (response) => {
    if (/\/assets\/[pk]\//.test(response.url())) seen.push({ url: response.url(), sw: response.fromServiceWorker(), status: response.status() });
  };
  page.on('response', listener);
  await action();
  await sleep(500);
  page.off('response', listener);
  return seen;
}

// ------------------------------------------------------------ run

if (args.serve) await startServer();
const original = hasDb ? sql(`SELECT asset_version FROM ${t('game_releases')} WHERE is_active = 1`)?.[0]?.asset_version : null;

const active = (await (await fetch(`${BASE}api/paket/aktif`)).json()).data;
if (!active.package_code) {
  console.error('No active package: build one (php spark gelita:paket:bangun) and name it in the active release first.');
  if (args.serve) await stopServer();
  process.exit(1);
}
const CODE = active.package_code;
const manifest = await (await fetch(`${BASE}${active.manifest_url}`)).json();
const manifestUrls = new Set([...manifest.files.map((file) => `${BASE}${file.url}`), `${BASE}${active.manifest_url}`]);
console.log(`package ${CODE}: ${manifest.files.length} files, ${(manifest.total_bytes / 1048576).toFixed(1)} MB`);

const browser = await chromium.launch();
try {
  // ========================================================== A
  section('A  package on: download, resume, serve');
  const context = await browser.newContext({ viewport: { width: 1280, height: 720 } });
  const page = await context.newPage();
  const pageErrors = [];
  page.on('pageerror', (error) => pageErrors.push(error.message));
  await gotoReady(page, BASE);

  const boot = await page.evaluate(() => ({ ...window.GELITA_PACKAGE_BOOT, local: document.documentElement.dataset.assetsLocal ?? null }));
  check(boot.code === CODE && boot.local === null, 'first visit: page names the package, assets not local', JSON.stringify(boot));
  await page.evaluate(() => navigator.serviceWorker.ready);
  const first = await withPackage(page, async (pkg) => ({ state: await pkg.check(), info: await pkg.packageInfo() }));
  check(first.state.status === 'download' && first.state.canStart === false, 'check(): download needed, Mulai not allowed', first.state.status);
  check(first.info.package_mode === 'daring', 'package_mode daring before the download', JSON.stringify(first.info));
  check(first.state.server.online_lab_allowed === false && !first.state.onlineReasons.includes('lab'), 'lab online mode not offered (missing online_lab_allowed = false)');
  check(await withPackage(page, (pkg, state) => pkg.chooseOnline('lab', state), first.state) === false, 'chooseOnline(lab) refused');

  // Interrupted download (abort at about half), then resume
  const interrupted = await withPackage(page, async (pkg) => {
    const controller = new AbortController();
    let at = 0;
    let error = null;
    try {
      await pkg.download({
        signal: controller.signal,
        onProgress: (p) => {
          if (!controller.signal.aborted && p.doneFiles >= p.totalFiles / 2) {
            at = p.doneFiles;
            controller.abort();
          }
        },
      });
    } catch (e) {
      error = e.code;
    }
    const cached = (await (await caches.open(pkg.ASSET_CACHE)).keys()).length;
    return { error, at, cached, marker: await pkg.activePackage() };
  });
  check(interrupted.error === 'aborted' && interrupted.at > 0, 'download aborted at about 50 %', `${interrupted.at} files`);
  check(interrupted.marker === null, 'no package active after an incomplete download (atomic switch)');

  const fetchedOnResume = [];
  const onRequest = (request) => { if (/\/assets\/[pk]\//.test(request.url())) fetchedOnResume.push(request.url()); };
  page.on('request', onRequest);
  const resumed = await withPackage(page, async (pkg) => {
    const groups = new Set();
    const result = await pkg.download({ onProgress: (p) => p.groups.forEach((g) => g.complete && groups.add(g.name)) });
    return { result, groups: [...groups] };
  });
  page.off('request', onRequest);
  const cachedFilesBefore = interrupted.cached - 1; // minus the manifest
  check(resumed.result.activated === true, 'resumed download switches to the package', JSON.stringify(resumed.result));
  check(resumed.result.skippedFiles >= cachedFilesBefore && resumed.result.downloadedFiles + resumed.result.skippedFiles === manifest.files.length,
    'resume skips every finished file (S7)', `${resumed.result.skippedFiles} skipped, ${resumed.result.downloadedFiles} downloaded`);
  check(fetchedOnResume.length === resumed.result.downloadedFiles && new Set(fetchedOnResume).size === fetchedOnResume.length,
    'resume requests only the missing files', `${fetchedOnResume.length} requests`);
  check(resumed.groups.length === Object.keys(manifest.groups).filter((g) => manifest.groups[g] > 0).length, 'progress reports every group complete', resumed.groups.join(','));

  const ready = await withPackage(page, async (pkg) => ({ state: await pkg.check(), mirror: localStorage.getItem('gelita.paket') }));
  check(ready.state.status === 'ready' && ready.state.mode === 'lengkap' && ready.state.canStart, 'check(): ready, lengkap, Mulai allowed', ready.state.status);
  check(JSON.parse(ready.mirror || '{}').package_code === CODE, 'boot mirror written');

  // Cache contents (§9.1)
  const caches1 = await cacheReport(page);
  const names = Object.keys(caches1).sort();
  check(names.join() === 'gelita-aset,gelita-meta,gelita-offline-v1', 'only the package, meta, and offline caches', names.join());
  const strays = (caches1['gelita-aset'] || []).filter((u) => !manifestUrls.has(u));
  check(strays.length === 0 && caches1['gelita-aset'].length === manifestUrls.size, 'gelita-aset holds exactly the manifest URLs', strays.slice(0, 3).join(' '));
  check(JSON.stringify(caches1['gelita-meta']) === JSON.stringify([`${BASE}gelita-meta/aktif.json`]), 'gelita-meta holds only aktif.json');
  check(JSON.stringify(caches1['gelita-offline-v1']) === JSON.stringify([`${BASE}offline.html`]), 'offline cache holds only offline.html');
  check(!Object.values(caches1).flat().some((u) => /\/(api|admin|masuk|daftar|keluar)(\/|$)/.test(new URL(u).pathname)), 'no API, admin, or login URL cached');

  // Reload: everything from the worker
  const served = await packageResponses(page, () => gotoReady(page, BASE));
  const notFromSw = served.filter((r) => !r.sw);
  check(served.length > 5 && notFromSw.length === 0, 'reload: every package URL comes from the service worker', `${served.length} responses, ${notFromSw.length} from the network`);
  const local = await page.evaluate(async () => ({
    attr: document.documentElement.dataset.assetsLocal ?? null,
    info: typeof window.GELITA_PACKAGE === 'function' ? await window.GELITA_PACKAGE() : window.GELITA_PACKAGE,
  }));
  check(local.attr === '1', '<html data-assets-local="1"> once the package is complete');
  check(local.info?.package_mode === 'lengkap' && local.info?.package_code === CODE, 'window.GELITA_PACKAGE reports lengkap', JSON.stringify(local.info));
  const apiFromSw = await (async () => {
    const [response] = await Promise.all([page.waitForResponse((r) => r.url().includes('/api/paket/aktif')), page.evaluate((b) => fetch(`${b}api/paket/aktif`), BASE)]);
    return { sw: response.fromServiceWorker(), header: response.headers()['x-gelita-paket'] };
  })();
  check(apiFromSw.sw === false, '/api goes straight to the network');
  check(apiFromSw.header === CODE, 'API response carries X-Gelita-Paket', String(apiFromSw.header));

  // sha256: broken cache entry found by verify(); re-download fetches only that file
  const sfx = manifest.files.find((file) => file.key === 'audio.sfx.click') || manifest.files.find((file) => file.type === 'audio/mpeg');
  const broken = await withPackage(page, async (pkg, file) => {
    const href = new URL(file.url, document.baseURI).href;
    // Deliberately bypasses the guard: simulates a damaged entry
    await (await caches.open(pkg.ASSET_CACHE)).put(href, new Response('broken', { headers: { 'Content-Type': file.type } }));
    const verify = await pkg.verify();
    const after = await pkg.activePackage();
    const again = await pkg.download();
    return { verify, after, again };
  }, sfx);
  check(broken.verify.ok === false && broken.verify.corrupt.includes(sfx.key), 'verify() names the broken file', JSON.stringify(broken.verify.corrupt));
  check(broken.after === null, 'a broken package is no longer active');
  check(broken.again.activated && broken.again.downloadedFiles === 1, 'download() fetches only the broken file', String(broken.again.downloadedFiles));

  // sha256: a broken file on the server fails after one retry, naming the key
  const serverFile = path.join(ROOT, 'public', sfx.url);
  const good = readFileSync(serverFile);
  writeFileSync(serverFile, Buffer.concat([good, Buffer.from('x')]));
  const tries = [];
  const onTry = (request) => { if (request.url().endsWith(sfx.url)) tries.push(request.url()); };
  page.on('request', onTry);
  const corrupt = await withPackage(page, async (pkg, file) => {
    await (await caches.open(pkg.ASSET_CACHE)).delete(new URL(file.url, document.baseURI).href);
    try {
      await pkg.download();
      return { code: null };
    } catch (e) {
      return { code: e.code, key: e.detail?.key, marker: await pkg.activePackage() };
    }
  }, sfx);
  page.off('request', onTry);
  writeFileSync(serverFile, good);
  check(corrupt.code === 'corrupt' && corrupt.key === sfx.key, 'sha256 mismatch → corrupt error naming the key', JSON.stringify(corrupt));
  check(tries.length === 2, 'one retry before giving up', `${tries.length} requests`);
  const repaired = await withPackage(page, (pkg) => pkg.download());
  check(repaired.activated && repaired.downloadedFiles === 1, 'after the server file is fixed, the download completes');

  // Audio Range from the cache (§4.1)
  const music = manifest.files.find((file) => file.key.startsWith('audio.music.')) || sfx;
  const range = await page.evaluate(async ({ file, base }) => {
    const href = `${base}${file.url}`;
    const get = async (header) => {
      const response = await fetch(href, header ? { headers: { Range: header } } : {});
      return { status: response.status, range: response.headers.get('Content-Range'), length: (await response.arrayBuffer()).byteLength };
    };
    return { head: await get('bytes=0-99'), suffix: await get('bytes=-10'), open: await get(`bytes=${file.bytes - 5}-`), beyond: await get(`bytes=${file.bytes}-`), full: await get(null) };
  }, { file: music, base: BASE });
  check(range.head.status === 206 && range.head.range === `bytes 0-99/${music.bytes}` && range.head.length === 100, 'Range bytes=0-99 → 206 with Content-Range', JSON.stringify(range.head));
  check(range.suffix.status === 206 && range.suffix.length === 10 && range.open.length === 5, 'suffix and open ranges', `${range.suffix.length}, ${range.open.length}`);
  check(range.beyond.status === 416, 'range past the end → 416', String(range.beyond.status));
  check(range.full.status === 200 && range.full.length === music.bytes, 'no Range → whole file 200');
  const audioResponses = await packageResponses(page, () => page.evaluate(async ({ file, base }) => {
    const audio = new Audio(`${base}${file.url}`);
    audio.preload = 'auto';
    await new Promise((resolve) => {
      audio.addEventListener('loadedmetadata', resolve, { once: true });
      audio.addEventListener('error', resolve, { once: true });
      setTimeout(resolve, 5000);
    });
    return audio.duration;
  }, { file: music, base: BASE }));
  const audio206 = audioResponses.filter((r) => r.url.endsWith(music.url));
  check(audio206.length > 0 && audio206.every((r) => r.sw && r.status === 206), '<audio> gets 206 from the service worker', JSON.stringify(audio206.map((r) => r.status)));

  // Network failure: retries, then a `network` error naming the key. Chromium's
  // offline emulation does not reach service worker fetches, so the server is stopped.
  await withPackage(page, async (pkg, file) => { await (await caches.open(pkg.ASSET_CACHE)).delete(new URL(file.url, document.baseURI).href); }, sfx);
  if (args.serve) {
    const server0 = await withPackage(page, (pkg) => pkg.serverPackage());
    await stopServer();
    const offline = await withPackage(page, async (pkg, { srv }) => {
      try {
        await pkg.download({ server: srv, retryDelays: [20, 20, 20] });
        return { code: null };
      } catch (e) {
        return { code: e.code, key: e.detail?.key ?? null, marker: Boolean(await pkg.activePackage()) };
      }
    }, { srv: server0 });
    await startServer();
    check(offline.code === 'network' && offline.key === sfx.key, 'server unreachable → network error after the retries, naming the key', JSON.stringify(offline));
  } else {
    skip('network error', 'needs --serve (the server is stopped during the download)');
  }

  await context.setOffline(true);
  const offlinePage = await page.goto(`${BASE}peta`).then(() => page.title(), (e) => `error ${e.message}`);
  check(/Tanpa koneksi/.test(offlinePage), 'offline navigation shows offline.html', offlinePage);
  await context.setOffline(false);
  const back = await (async () => { await gotoReady(page, BASE); return withPackage(page, (pkg) => pkg.download()); })();
  check(back.activated && back.downloadedFiles === 1, 'back online: Coba lagi completes the package');
  check(pageErrors.length === 0, 'no page errors', pageErrors.slice(0, 2).join(' | '));
  const contextA = context;

  // ========================================================== B
  section('B  storage too small (§8.4) and online mode (§8.7)');
  const small = await browser.newContext({ viewport: { width: 1280, height: 720 } });
  await small.addInitScript(() => {
    if (navigator.storage) navigator.storage.estimate = async () => ({ usage: 0, quota: 1024 * 1024 });
  });
  const pageB = await small.newPage();
  await gotoReady(pageB, BASE);
  await pageB.evaluate(() => navigator.serviceWorker.ready);
  const quota = await withPackage(pageB, async (pkg) => {
    const state = await pkg.check();
    let error = null;
    try { await pkg.download({ server: state.server }); } catch (e) { error = { code: e.code, detail: e.detail }; }
    const written = (await caches.has(pkg.ASSET_CACHE)) ? (await (await caches.open(pkg.ASSET_CACHE)).keys()).length : 0;
    const lab = pkg.chooseOnline('lab', state);
    const chosen = pkg.chooseOnline('storage', state);
    return { state, error, written, lab, chosen, stored: sessionStorage.getItem('gelita.paket.daring') };
  });
  check(quota.state.storage.enough === false && quota.state.onlineReasons.includes('storage'), 'check(): not enough storage, online mode offered');
  check(quota.error?.code === 'quota' && quota.error.detail.shortBytes > 0, 'download refused before writing: quota error with the shortfall', JSON.stringify(quota.error));
  check(quota.written === 0, 'nothing written');
  check(quota.lab === false && quota.chosen === true && quota.stored === 'storage', 'online mode chosen for this browser session only (sessionStorage)');
  await gotoReady(pageB, BASE);
  const onlineInfo = await pageB.evaluate(async () => ({
    boot: window.GELITA_PACKAGE_BOOT.online,
    info: typeof window.GELITA_PACKAGE === 'function' ? await window.GELITA_PACKAGE() : window.GELITA_PACKAGE,
  }));
  const onlineState = await withPackage(pageB, (pkg) => pkg.check());
  check(onlineInfo.boot === 'storage' && onlineInfo.info.package_mode === 'daring', 'online mode → package_mode daring', JSON.stringify(onlineInfo.info));
  check(onlineState.status === 'online' && onlineState.canStart, 'check(): online, Mulai allowed');
  await small.close();

  // ========================================================== C
  section('C  no service worker (§8.6)');
  const noSw = await browser.newContext({ viewport: { width: 1280, height: 720 } });
  await noSw.addInitScript(() => { delete Navigator.prototype.serviceWorker; });
  const pageC = await noSw.newPage();
  const errorsC = [];
  pageC.on('pageerror', (error) => errorsC.push(error.message));
  await gotoReady(pageC, BASE);
  const unsupported = await withPackage(pageC, async (pkg) => ({ state: await pkg.check(), info: await pkg.packageInfo(), ready: document.body.dataset.pageReady }));
  check(unsupported.state.status === 'unsupported' && unsupported.state.canStart, 'check(): unsupported, Mulai allowed without a download');
  check(unsupported.info.package_mode === 'daring', 'package_mode daring');
  check(unsupported.ready === '1' && errorsC.length === 0, 'page works without a service worker', errorsC.join(' | '));
  await noSw.close();

  // ========================================================== D
  section('D  sessions: package_mode at sign-up, no switch mid-session (S8, S9)');
  if (!hasDb) {
    skip('D', 'needs --sqlite or --mysql');
  } else {
    const pageD = page;
    const username = `qa_pkt_${Date.now().toString(36)}`.slice(0, 30);
    const PASSWORD = 'Qa#Gelita2026';
    await gotoReady(pageD, `${BASE}daftar/1`);
    await pageD.fill('#display_name', 'QA paket');
    await pageD.fill('#age', '11');
    await pageD.check('input[name="gender"][value="perempuan"]');
    await Promise.all([pageD.waitForURL(/daftar\/2/), pageD.click('form[action*="daftar"] button[type="submit"]')]);
    await pageD.selectOption('#country_code', 'ID');
    await pageD.selectOption('#province_code', '34');
    await pageD.waitForSelector('#district_code option[value="34.71"]', { state: 'attached' });
    await pageD.selectOption('#district_code', '34.71');
    await pageD.fill('#school_name', 'Sekolah Uji Paket');
    await Promise.all([pageD.waitForURL(/daftar\/3/), pageD.click('form[action*="daftar"] button[type="submit"]')]);
    await pageD.fill('#username', username);
    await pageD.fill('#password', PASSWORD);
    await pageD.fill('#password_confirm', PASSWORD);
    await pageD.check('input[name="assent"]');
    await Promise.all([pageD.waitForURL(/intro/), pageD.click('form[action*="daftar"] button[type="submit"]')]);
    const [session] = sql(`SELECT s.package_code, s.package_mode FROM ${t('game_sessions')} s JOIN ${t('participants')} p ON p.id = s.participant_id WHERE p.username = ${q(username)}`);
    check(session?.package_mode === 'lengkap' && session?.package_code === CODE, 'sign-up stores package_mode lengkap', JSON.stringify(session));

    // A new package with the same files is published while the session runs
    execFileSync('php', ['spark', 'gelita:paket:bangun', '--tanpa-pustaka'], { cwd: ROOT, stdio: 'ignore' });
    const [built] = sql(`SELECT package_code FROM ${t('asset_packages')} ORDER BY id DESC LIMIT 1`);
    const NEXT = built.package_code;
    setRelease(NEXT);
    await gotoReady(pageD, `${BASE}profil`);
    const midSession = await withPackage(pageD, async (pkg) => {
      const state = await pkg.check();
      const result = await pkg.download({ server: state.server });
      return { state, result, marker: (await pkg.activePackage())?.package_code };
    });
    check(NEXT !== CODE && midSession.state.status === 'download', 'new package seen mid-session', `${CODE} → ${NEXT}`);
    check(midSession.result.activated === false && midSession.marker === CODE, 'running session keeps its package (S8)', JSON.stringify(midSession.result));
    check(midSession.result.downloadedFiles === 0, 'same files: nothing downloaded again (S9)', String(midSession.result.downloadedFiles));

    await gotoReady(pageD, `${BASE}keluar`);
    await gotoReady(pageD, BASE);
    const next = await withPackage(pageD, async (pkg) => {
      const result = await pkg.download();
      await pkg.requestPrune();
      return { result, state: await pkg.check() };
    });
    check(next.result.activated && next.result.downloadedFiles === 0 && next.state.status === 'ready', 'next session start switches to the new package with no file download');
    const caches2 = await cacheReport(pageD);
    check(!caches2['gelita-aset'].includes(`${BASE}paket/${CODE}.json`) && caches2['gelita-aset'].includes(`${BASE}paket/${NEXT}.json`), 'old manifest pruned after the switch');
    if (!args.keep) setRelease(original);
  }

  // ========================================================== E
  section('E  server kill switch (gelita.paket.aktif = false)');
  if (!args.serve) {
    skip('E', 'needs --serve (restarts the server with the kill switch)');
  } else {
    await stopServer();
    await startServer({ 'gelita.paket.aktif': 'false' });
    const pageE = await contextA.newPage();
    await gotoReady(pageE, BASE);
    await pageE.waitForFunction(() => navigator.serviceWorker.controller?.scriptURL.endsWith('sw-kosong.js'), null, { timeout: 10000 }).catch(() => {});
    // The new worker's activate event runs a little after the controller switch
    for (let i = 0; i < 50; i++) {
      const marker = await pageE.evaluate(async () => Boolean(await caches.match(new URL('gelita-meta/aktif.json', document.baseURI).href)));
      if (!marker) break;
      await sleep(200);
    }
    const killed = await pageE.evaluate(async () => ({
      boot: window.GELITA_PACKAGE_BOOT,
      script: navigator.serviceWorker.controller?.scriptURL ?? null,
      meta: (await caches.has('gelita-meta')) ? (await (await caches.open('gelita-meta')).keys()).map((r) => r.url) : [],
      files: (await caches.has('gelita-aset')) ? (await (await caches.open('gelita-aset')).keys()).length : 0,
      css: document.querySelector('link[rel="stylesheet"]').href,
    }));
    check(killed.boot.killed === true && killed.boot.code === null, 'boot script reports the kill switch, no package');
    check(String(killed.script).endsWith('sw-kosong.js'), 'empty worker replaces sw.js', String(killed.script));
    check(killed.meta.length === 0 && killed.files > 0, 'device marker dropped, package files kept', `${killed.files} files, meta ${JSON.stringify(killed.meta)}`);
    check(/\/assets\/css\/tokens\.css\?v=/.test(killed.css), 'normal asset URLs', killed.css);
    const killedResponses = [];
    const onKilled = (response) => { if (response.fromServiceWorker()) killedResponses.push(response.url()); };
    pageE.on('response', onKilled);
    await gotoReady(pageE, `${BASE}mulai`);
    pageE.off('response', onKilled);
    check(killedResponses.length === 0, 'no response comes from a service worker', killedResponses.slice(0, 2).join(' '));
    await pageE.close();
    await stopServer();
    await startServer();
  }

  // ========================================================== F
  section('F  package mode off by release (§6.3)');
  if (!hasDb) {
    skip('F', 'needs --sqlite or --mysql');
  } else {
    setRelease('1');
    const pageF = await contextA.newPage();
    await gotoReady(pageF, BASE);
    await gotoReady(pageF, BASE);
    const before = (await cacheReport(pageF))['gelita-aset']?.length ?? 0;
    const fromSw = [];
    const onF = (response) => { if (response.fromServiceWorker() && response.request().resourceType() !== 'document') fromSw.push(response.url()); };
    pageF.on('response', onF);
    for (const target of ['', 'mulai', 'masuk']) await gotoReady(pageF, `${BASE}${target}`);
    pageF.off('response', onF);
    const off = await pageF.evaluate(async () => ({
      boot: window.GELITA_PACKAGE_BOOT,
      info: window.GELITA_PACKAGE,
      local: document.documentElement.dataset.assetsLocal ?? null,
      script: navigator.serviceWorker.controller?.scriptURL ?? null,
      css: document.querySelector('link[rel="stylesheet"]').href,
    }));
    const after = (await cacheReport(pageF))['gelita-aset']?.length ?? 0;
    const offState = await withPackage(pageF, (pkg) => pkg.check());
    check(off.boot.code === null && off.local === null && off.info.package_mode === 'nonaktif', 'no package: nonaktif, assets not local', JSON.stringify(off.info));
    check(String(off.script).endsWith('/sw.js'), 'sw.js registered again', String(off.script));
    check(/\/assets\/css\/tokens\.css\?v=/.test(off.css), 'normal asset URLs', off.css);
    check(fromSw.length === 0, 'no subresource answered by the worker', fromSw.slice(0, 2).join(' '));
    check(before === after, 'nothing added to the package cache', `${before} → ${after}`);
    check(offState.status === 'off' && offState.canStart && offState.mode === 'nonaktif', 'check(): off, Mulai allowed');
    setRelease(original);
    await pageF.close();
  }
  await contextA.close();
} finally {
  await browser.close();
  if (hasDb && original && !args.keep) setRelease(original);
  if (args.serve) await stopServer();
}

console.log(`\n${failures.length === 0 ? 'All checks passed.' : `${failures.length} check(s) failed.`}`);
process.exit(failures.length === 0 ? 0 : 1);
