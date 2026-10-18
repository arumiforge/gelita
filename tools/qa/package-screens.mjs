#!/usr/bin/env node
/**
 * Asset package Stage 3 check: the "Siapkan GELITA" download screen, the
 * Mulai gate, and the teacher's "Periksa paket" screen
 * (docs/rencana-paket-aset.md §5.1, §5.5, §5.6, §8.1–§8.7, P7, P11, §11.3),
 * with real Chromium (Playwright) and real clicks, not module calls:
 *
 *   A  package on, fresh profile: welcome Mulai leads to /siapkan; /masuk is
 *      replaced by /siapkan?lanjut=masuk; Mulai stays disabled until every part
 *      is verified; progress per part; the server goes away mid-download (the
 *      screen says so, keeps finished files, and resumes by itself once the
 *      server is back, with no file downloaded twice); Mulai gets focus and
 *      continues to /masuk; no online choice offered; sign-up → `lengkap`
 *   F  Periksa paket on that device: full check OK; a damaged cache entry is
 *      named; "Unduh yang kurang" fetches only that file; "Hapus paket di
 *      perangkat ini" (confirmation) empties the device; the welcome gate holds again
 *   B  private mode (whole storage quota smaller than the package): private-mode
 *      text, no download, "Main daring di perangkat ini" → sign-up `daring`
 *   C  storage too small: "kurang sekitar … MB", Coba lagi and online choice;
 *      storage running full mid-download (QuotaExceededError) keeps the
 *      finished files; online choice → sign-up `daring`
 *   D  lab device: no lab choice while the admin setting is off; with
 *      `online_lab_allowed` the teacher's choice under "Untuk guru" → sign-up `daring`
 *   E  package mode off (release without a package): no gate, /siapkan
 *      redirects to /mulai, sign-up `nonaktif`
 *   G  no service worker: no download screen, the gate lets Mulai through
 *
 * Usage (local development database only, never production):
 *   php spark gelita:paket:bangun --tanpa-pustaka     # then make the active release name the package
 *   node tools/qa/package-screens.mjs --serve --sqlite=/path/to/dev.db [--prefix=db_]
 *   node tools/qa/package-screens.mjs --base=http://localhost:8080/ --mysql="mysql -uroot gelita"
 *
 * Options:
 *   --base=URL     app URL (QA_BASE_URL, default http://localhost:8080/)
 *   --serve        start PHP's built-in server (as `php spark serve`) on the base URL's port;
 *                  needed for the network drop in A (the server is stopped and restarted)
 *   --sqlite=FILE  SQLite development database (read/written through php PDO); with --mysql
 *   --mysql="CMD"  instead, a MySQL/MariaDB client command. Without either, the sign-up
 *                  checks (package_mode in game_sessions) and E are skipped
 *   --prefix=db_   table prefix of that database (default: none)
 *
 * The script edits `game_releases.asset_version` of the active release for E and
 * writes `writable/paket/pengaturan.json` for D; both are restored at the end. It
 * signs up one test student per case (`qa_scr_*`). Chromium is preinstalled
 * (PLAYWRIGHT_BROWSERS_PATH); never `playwright install`. Exit code 0 when every
 * check passes, 1 otherwise.
 */
import { createRequire } from 'node:module';
import { execFileSync, execSync, spawn } from 'node:child_process';
import { existsSync, readFileSync, unlinkSync, writeFileSync } from 'node:fs';
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
const SETTINGS = path.join(ROOT, 'writable/paket/pengaturan.json');
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
const sessionOf = (username) => sql(`SELECT s.package_code, s.package_mode FROM ${t('game_sessions')} s JOIN ${t('participants')} p ON p.id = s.participant_id WHERE p.username = ${q(username)} ORDER BY s.id DESC LIMIT 1`)?.[0] ?? null;

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
/** The same server `php spark serve` starts (built-in server + CodeIgniter's rewrite.php). */
async function startServer() {
  if (await fetch(BASE).then(() => true, () => false)) throw new Error(`something already answers on ${BASE}; pick a free port with --base`);
  const host = `${new URL(BASE).hostname}:${PORT}`;
  const router = path.join(ROOT, 'vendor/codeigniter4/framework/system/rewrite.php');
  server = spawn('php', ['-S', host, '-t', path.join(ROOT, 'public'), router], { cwd: ROOT, detached: true, stdio: 'ignore' });
  await waitFor(true);
}
async function stopServer() {
  if (!server) return;
  try { process.kill(-server.pid, 'SIGTERM'); } catch { /* already gone */ }
  server = null;
  await waitFor(false);
}

// ------------------------------------------------------------ page helpers

async function gotoReady(page, url) {
  await page.goto(url, { waitUntil: 'load' });
  await page.waitForFunction(() => document.body.dataset.pageReady === '1', null, { timeout: 15000 }).catch(() => {});
}
const prepareState = (page) => page.evaluate(() => document.querySelector('[data-screen="prepare"]')?.dataset.state ?? null);
const waitState = (page, states, timeout = 120000) => page.waitForFunction(
  (list) => list.includes(document.querySelector('[data-screen="prepare"]')?.dataset.state), states, { timeout },
).then(() => true, () => false);
const screenText = (page) => page.evaluate(() => {
  const root = document.querySelector('[data-screen="prepare"]');
  const visible = (sel) => { const el = root?.querySelector(sel); return Boolean(el && !el.hidden && !el.closest('[hidden]')); };
  return {
    state: root?.dataset.state ?? null,
    status: root?.querySelector('[data-prepare-status]')?.textContent.trim() ?? '',
    startDisabled: root?.querySelector('[data-prepare-start]')?.disabled ?? null,
    retry: visible('[data-prepare-retry]'),
    storage: visible('[data-prepare-online="storage"]'),
    lab: visible('[data-prepare-lab]'),
    pct: Number(root?.querySelector('[data-prepare-bar]')?.getAttribute('aria-valuenow') || 0),
    valuetext: root?.querySelector('[data-prepare-bar]')?.getAttribute('aria-valuetext') ?? '',
  };
});
const gateOf = (page) => page.waitForFunction(() => document.body.dataset.packageGate, null, { timeout: 10000 })
  .then(() => page.evaluate(() => document.body.dataset.packageGate), () => null);

const PASSWORD = 'Qa#Gelita2026';
let signups = 0;
/** Sign up a new student through /daftar/1–3; returns the username. */
async function signUp(page) {
  const username = `qa_scr_${Date.now().toString(36)}${signups++}`.slice(0, 30);
  await gotoReady(page, `${BASE}daftar/1`);
  await page.waitForURL(/daftar\/1/);
  await page.fill('#display_name', 'QA layar paket');
  await page.fill('#age', '11');
  await page.check('input[name="gender"][value="perempuan"]');
  await Promise.all([page.waitForURL(/daftar\/2/), page.click('form[action*="daftar"] button[type="submit"]')]);
  await page.selectOption('#country_code', 'ID');
  await page.selectOption('#province_code', '34');
  await page.waitForSelector('#district_code option[value="34.71"]', { state: 'attached' });
  await page.selectOption('#district_code', '34.71');
  await page.fill('#school_name', 'Sekolah Uji Paket');
  await Promise.all([page.waitForURL(/daftar\/3/), page.click('form[action*="daftar"] button[type="submit"]')]);
  await page.fill('#username', username);
  await page.fill('#password', PASSWORD);
  await page.fill('#password_confirm', PASSWORD);
  await page.check('input[name="assent"]');
  await Promise.all([page.waitForURL(/intro/), page.click('form[action*="daftar"] button[type="submit"]')]);
  return username;
}
async function signUpAndCheck(page, mode, label) {
  if (!hasDb) {
    skip(`${label}: sign-up records ${mode}`, 'needs --sqlite or --mysql');
    return;
  }
  const username = await signUp(page);
  const session = sessionOf(username);
  check(session?.package_mode === mode, `${label}: sign-up records package_mode ${mode}`, JSON.stringify(session));
  await gotoReady(page, `${BASE}keluar`);
}

/** Fresh browser profile with an optional init script (storage stubs). */
async function freshPage(browser, init = null, initArg = null) {
  const context = await browser.newContext({ viewport: { width: 1280, height: 720 } });
  if (init) await context.addInitScript(init, initArg);
  const page = await context.newPage();
  const errors = [];
  page.on('pageerror', (error) => errors.push(error.message));
  return { context, page, errors };
}

// ------------------------------------------------------------ run

if (args.serve) await startServer();
const originalRelease = hasDb ? sql(`SELECT asset_version FROM ${t('game_releases')} WHERE is_active = 1`)?.[0]?.asset_version : null;
const originalSettings = existsSync(SETTINGS) ? readFileSync(SETTINGS) : null;
const setLab = (allowed) => writeFileSync(SETTINGS, `${JSON.stringify({ online_lab_allowed: allowed })}\n`);
const restoreLab = () => { if (originalSettings) writeFileSync(SETTINGS, originalSettings); else if (existsSync(SETTINGS)) unlinkSync(SETTINGS); };

const active = (await (await fetch(`${BASE}api/paket/aktif`)).json()).data;
if (!active.package_code) {
  console.error('No active package: build one (php spark gelita:paket:bangun) and name it in the active release first.');
  if (args.serve) await stopServer();
  process.exit(1);
}
const CODE = active.package_code;
const manifest = await (await fetch(`${BASE}${active.manifest_url}`)).json();
const groupsWithFiles = Object.keys(manifest.groups).filter((g) => manifest.groups[g] > 0);
console.log(`package ${CODE}: ${manifest.files.length} files, ${(manifest.total_bytes / 1048576).toFixed(1)} MB, parts ${groupsWithFiles.join(', ')}`);
if (originalSettings && JSON.parse(originalSettings).online_lab_allowed === true) setLab(false);

const browser = await chromium.launch();
try {
  // ========================================================== A
  section('A  package on: Siapkan GELITA before Mulai (P11)');
  const a = await freshPage(browser);
  const page = a.page;
  await gotoReady(page, BASE);
  check(await gateOf(page) === 'hold', 'welcome: gate holds a device without the package');
  const startHref = await page.getAttribute('[data-package-start]', 'href');
  check(/\/siapkan$/.test(startHref), 'welcome Mulai leads to /siapkan', startHref);

  await page.goto(`${BASE}masuk`);
  await page.waitForURL(/\/siapkan\?lanjut=masuk/, { timeout: 10000 }).catch(() => {});
  check(/\/siapkan\?lanjut=masuk$/.test(page.url()), '/masuk is replaced by /siapkan?lanjut=masuk', page.url());

  const rows = await page.$$eval('[data-group]', (list) => list.map((li) => li.dataset.group));
  check(JSON.stringify(rows) === JSON.stringify(groupsWithFiles), 'one progress row per part, in download order', rows.join(','));

  const responses = new Map();
  const onResponse = (response) => {
    // Downloader fetches only: the page's own CSS/JS load before the worker controls it does not count
    if (response.request().resourceType() === 'fetch' && /\/assets\/[pk]\//.test(response.url()) && response.status() === 200) responses.set(response.url(), (responses.get(response.url()) || 0) + 1);
  };
  page.on('response', onResponse);

  // Watch the screen while it downloads: Mulai disabled, a part done while others are not
  let partial = null;
  let sawDisabled = true;
  let dropped = null;
  for (let i = 0; i < 1200; i++) {
    const snap = await page.evaluate(() => ({
      state: document.querySelector('[data-screen="prepare"]').dataset.state,
      disabled: document.querySelector('[data-prepare-start]').disabled,
      done: [...document.querySelectorAll('[data-group].is-done')].map((li) => li.dataset.group),
      pct: Number(document.querySelector('[data-prepare-bar]').getAttribute('aria-valuenow')),
    }));
    if (snap.state !== 'ready') sawDisabled &&= snap.disabled;
    if (!partial && snap.done.length > 0 && snap.done.length < rows.length) partial = snap;
    if (args.serve && !dropped && snap.state === 'downloading' && snap.pct >= 30) {
      await stopServer();
      dropped = { pct: snap.pct };
      break;
    }
    if (snap.state === 'ready' || snap.state === 'error') break;
    await sleep(100);
  }
  check(partial !== null, 'progress per part: parts finish one after another', partial ? `${partial.done.join(',')} at ${partial.pct} %` : 'none seen');
  const lab0 = await screenText(page);
  check(!lab0.storage && !lab0.lab, 'no online choice for a device that can hold the package (lab setting off)');

  if (dropped) {
    const errored = await waitState(page, ['error'], 60000);
    const down = await screenText(page);
    check(errored && /terputus|server/i.test(down.status) && down.retry && down.startDisabled, 'server gone mid-download: the screen says so, offers Coba lagi, Mulai stays disabled', down.status);
    const kept = await page.evaluate(async () => (await caches.has('gelita-aset')) ? (await (await caches.open('gelita-aset')).keys()).length : 0);
    check(kept > 1, 'finished files are kept', `${kept} cached`);
    await startServer();
  } else if (!args.serve) {
    skip('network drop', 'needs --serve (the server is stopped mid-download)');
  }
  sawDisabled &&= (await screenText(page)).startDisabled !== false || (await prepareState(page)) === 'ready';
  const ready = await waitState(page, ['ready'], 180000);
  page.off('response', onResponse);
  const done = await screenText(page);
  check(ready && done.status.length > 0 && done.startDisabled === false, 'every part verified → Mulai enabled', done.status);
  check(sawDisabled, 'Mulai disabled until then (no skip button)');
  if (dropped) check(true, 'resumed by itself once the server was back');
  const twice = [...responses.entries()].filter(([, n]) => n > 1);
  check(twice.length === 0, 'no file downloaded twice (resume per file)', `${responses.size} files, ${twice.length} twice ${twice.slice(0, 2).map(([u]) => u).join(' ')}`);
  check(done.pct === 100 && /dari|of/.test(done.valuetext), 'progress bar at 100 % with a size text', done.valuetext);
  const focus = await page.evaluate(() => document.activeElement?.matches('[data-prepare-start]'));
  check(focus === true, 'Mulai has the focus');
  await Promise.all([page.waitForURL(/\/masuk$/), page.keyboard.press('Enter')]);
  check(true, 'Enter on Mulai continues to /masuk');
  check(await gateOf(page) === 'pass', '/masuk: gate lets the device through');
  const info = await page.evaluate(async () => (typeof window.GELITA_PACKAGE === 'function' ? window.GELITA_PACKAGE() : window.GELITA_PACKAGE));
  check(info?.package_mode === 'lengkap' && info?.package_code === CODE, 'package_mode lengkap before sign-up', JSON.stringify(info));
  await signUpAndCheck(page, 'lengkap', 'A');
  await gotoReady(page, BASE);
  check(await gateOf(page) === 'pass' && /\/mulai$/.test(await page.getAttribute('[data-package-start]', 'href')), 'welcome: Mulai goes straight to /mulai once the package is on the device');
  check(a.errors.length === 0, 'no page errors', a.errors.slice(0, 2).join(' | '));

  // ========================================================== F
  section('F  Periksa paket (teacher)');
  const idle = () => page.waitForFunction(() => document.querySelector('[data-screen="package-inspect"]')?.dataset.state === 'idle', null, { timeout: 120000 });
  const inspectText = () => page.evaluate(() => {
    const root = document.querySelector('[data-screen="package-inspect"]');
    const value = (name) => root.querySelector(`[data-inspect="${name}"]`).textContent.trim();
    return {
      state: value('state'), device: value('device'), server: value('server'),
      status: root.querySelector('[data-inspect-status]').textContent.trim(),
      keys: root.querySelector('[data-inspect-keys]').textContent.trim(),
    };
  });
  await gotoReady(page, `${BASE}periksa-paket`);
  await idle();
  const first = await inspectText();
  check(/Lengkap|Complete/.test(first.state) && first.device.includes(CODE) && first.server.includes(CODE), 'shows a complete package on the device and the server', `${first.state} | ${first.device}`);
  await page.click('[data-inspect-action="verify"]');
  await idle();
  const verified = await inspectText();
  check(verified.status.includes(String(manifest.files.length)) && /cocok|match/.test(verified.status), 'Periksa semua berkas: every file matches', verified.status);

  const victim = manifest.files.find((file) => file.key === 'audio.sfx.click') || manifest.files.find((file) => file.group === 'inti');
  await page.evaluate(async (file) => {
    // Deliberately bypasses the guard: simulates a damaged entry
    await (await caches.open('gelita-aset')).put(new URL(file.url, document.baseURI).href, new Response('broken'));
  }, victim);
  await page.click('[data-inspect-action="verify"]');
  await idle();
  const broken = await inspectText();
  check(/rusak|damaged/.test(broken.status) && broken.keys.includes(victim.key), 'a damaged file is named', `${broken.status} | ${broken.keys}`);
  check(/Belum lengkap|Not complete/.test(broken.state), 'the device no longer counts as ready', broken.state);
  const repairs = [];
  const onRepair = (response) => { if (/\/assets\/[pk]\//.test(response.url()) && response.status() === 200) repairs.push(response.url()); };
  page.on('response', onRepair);
  await page.click('[data-inspect-action="repair"]');
  await idle();
  page.off('response', onRepair);
  const repaired = await inspectText();
  check(repairs.length === 1 && repairs[0].endsWith(victim.url) && /Lengkap|Complete/.test(repaired.state), 'Unduh yang kurang fetches only that file', `${repairs.length} file(s); ${repaired.status}`);

  await page.click('[data-inspect-action="clear"]');
  await page.waitForSelector('.modal-card button.btn-danger', { timeout: 5000 });
  await page.click('.modal-card button.btn-danger');
  await idle();
  const cleared = await inspectText();
  const left = await page.evaluate(async () => ({ keys: await caches.keys(), mirror: localStorage.getItem('gelita.paket') }));
  check(/dihapus|removed/.test(cleared.status) && /Belum ada|None/.test(cleared.device), 'Hapus paket di perangkat ini empties the device', cleared.status);
  check(!left.keys.includes('gelita-aset') && !left.keys.includes('gelita-meta') && left.mirror === null, 'package caches and boot mirror gone', left.keys.join(','));
  await gotoReady(page, BASE);
  check(await gateOf(page) === 'hold', 'welcome: the gate holds the device again');
  await a.context.close();

  // ========================================================== B
  section('B  private mode (§8.3)');
  const b = await freshPage(browser, () => {
    if (navigator.storage) navigator.storage.estimate = async () => ({ usage: 0, quota: 1024 * 1024 });
  });
  await gotoReady(b.page, `${BASE}siapkan`);
  await waitState(b.page, ['error'], 30000);
  const priv = await screenText(b.page);
  check(/privat|Private/.test(priv.status) && priv.storage && priv.startDisabled, 'private-mode text, online choice offered, Mulai disabled', priv.status);
  const privFiles = await b.page.evaluate(async () => (await caches.has('gelita-aset')) ? (await (await caches.open('gelita-aset')).keys()).length : 0);
  check(privFiles === 0, 'nothing downloaded');
  await b.page.click('[data-prepare-online="storage"]');
  const privOnline = await screenText(b.page);
  const privStored = await b.page.evaluate(() => sessionStorage.getItem('gelita.paket.daring'));
  check(privOnline.state === 'online' && privOnline.startDisabled === false && privStored === 'storage', 'Main daring di perangkat ini → Mulai enabled, choice in sessionStorage', privOnline.status);
  await Promise.all([b.page.waitForURL(/\/mulai$/), b.page.click('[data-prepare-start]')]);
  check(await gateOf(b.page) === 'skip', '/mulai: gate skipped after the online choice');
  await signUpAndCheck(b.page, 'daring', 'B');
  check(b.errors.length === 0, 'no page errors', b.errors.join(' | '));
  await b.context.close();

  // ========================================================== C
  section('C  storage too small (§8.4)');
  const c = await freshPage(browser, () => {
    if (navigator.storage) navigator.storage.estimate = async () => ({ usage: 10 * 1024 ** 3 - 5 * 1024 ** 2, quota: 10 * 1024 ** 3 });
  });
  await gotoReady(c.page, `${BASE}siapkan`);
  await waitState(c.page, ['error'], 30000);
  const short = await screenText(c.page);
  check(/kurang sekitar|short/.test(short.status) && /MB/.test(short.status) && short.retry && short.storage && short.startDisabled, 'shortfall in MB, Coba lagi, online choice, Mulai disabled', short.status);
  await c.context.close();

  // Storage runs full while writing (estimate was too optimistic)
  const c2 = await freshPage(browser, () => {
    const put = Cache.prototype.put;
    let n = 0;
    Cache.prototype.put = function guarded(...rest) {
      if (++n > 25) return Promise.reject(new DOMException('full', 'QuotaExceededError'));
      return put.apply(this, rest);
    };
  });
  await gotoReady(c2.page, `${BASE}siapkan`);
  await waitState(c2.page, ['error'], 60000);
  const full = await screenText(c2.page);
  const fullKept = await c2.page.evaluate(async () => (await (await caches.open('gelita-aset')).keys()).length);
  check(/penuh|full/.test(full.status) && full.storage && full.retry, 'QuotaExceededError mid-download: storage text, online choice', full.status);
  check(fullKept > 0, 'finished files kept for Coba lagi', `${fullKept} cached`);
  await c2.page.click('[data-prepare-online="storage"]');
  check((await screenText(c2.page)).state === 'online', 'online choice taken');
  await Promise.all([c2.page.waitForURL(/\/mulai$/), c2.page.click('[data-prepare-start]')]);
  await signUpAndCheck(c2.page, 'daring', 'C');
  await c2.context.close();

  // ========================================================== D
  section('D  lab device wiped on logout (§8.2, P7)');
  setLab(true);
  const d = await freshPage(browser);
  await gotoReady(d.page, `${BASE}siapkan`);
  await waitState(d.page, ['downloading'], 30000);
  await d.page.click('.prepare-teacher summary');
  const labOn = await screenText(d.page);
  check(labOn.lab && !labOn.storage && labOn.startDisabled, 'with the admin setting on: lab choice under "Untuk guru"; no storage choice');
  await d.page.click('[data-prepare-online="lab"]');
  const labChosen = await screenText(d.page);
  const labStored = await d.page.evaluate(() => sessionStorage.getItem('gelita.paket.daring'));
  check(labChosen.state === 'online' && labChosen.startDisabled === false && labStored === 'lab', 'lab choice → Mulai enabled, choice in sessionStorage');
  await Promise.all([d.page.waitForURL(/\/mulai$/), d.page.click('[data-prepare-start]')]);
  await signUpAndCheck(d.page, 'daring', 'D');
  check(d.errors.length === 0, 'no page errors', d.errors.join(' | '));
  await d.context.close();
  restoreLab();

  // ========================================================== E
  section('E  package mode off (§6.3)');
  if (!hasDb) {
    skip('E', 'needs --sqlite or --mysql');
  } else {
    setRelease('1');
    const e = await freshPage(browser);
    await gotoReady(e.page, BASE);
    await sleep(500);
    const off = await e.page.evaluate(() => ({ gate: document.body.dataset.packageGate ?? null, href: document.querySelector('[data-package-start]').href, code: window.GELITA_PACKAGE_BOOT.code }));
    check(off.code === null && off.gate === null && /\/mulai$/.test(off.href), 'no package: no gate, Mulai goes to /mulai', JSON.stringify(off));
    await e.page.goto(`${BASE}siapkan?lanjut=masuk`);
    check(/\/masuk$/.test(e.page.url()), '/siapkan redirects to its next page', e.page.url());
    await signUpAndCheck(e.page, 'nonaktif', 'E');
    await e.context.close();
    setRelease(originalRelease);
  }

  // ========================================================== G
  section('G  no service worker (§8.6)');
  const g = await freshPage(browser, () => { delete Navigator.prototype.serviceWorker; });
  await gotoReady(g.page, BASE);
  check(await gateOf(g.page) === 'skip' && /\/mulai$/.test(await g.page.getAttribute('[data-package-start]', 'href')), 'gate skipped: Mulai goes to /mulai without a download');
  await g.context.close();
} finally {
  await browser.close();
  if (hasDb && originalRelease) setRelease(originalRelease);
  restoreLab();
  if (args.serve) await stopServer();
}

console.log(`\n${failures.length === 0 ? 'All checks passed.' : `${failures.length} check(s) failed.`}`);
process.exit(failures.length === 0 ? 0 : 1);
