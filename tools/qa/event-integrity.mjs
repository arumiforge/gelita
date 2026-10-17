#!/usr/bin/env node
/**
 * Browser check of research data integrity with real Chromium: asset package
 * Stage 5 (docs/rencana-paket-aset.md §7.1, §7.3, §11.2) and asynchronous
 * logging Phase 1 (docs/rencana-log-asinkron.md §5.1, §6).
 *
 *   php spark serve                          # local app on a MariaDB/MySQL test database
 *   node tools/qa/event-integrity.mjs        # full run (2 minutes offline)
 *   QA_OFFLINE_SECONDS=20 node tools/qa/event-integrity.mjs   # shorter offline period
 *
 * Environment:
 *   QA_BASE_URL         app URL (default http://localhost:8080)
 *   QA_MYSQL            database client command (default "mysql -uroot gelita"); the
 *                       script reads game_sessions, game_event_logs and
 *                       audio_usage_events, and adds one placeholder audio asset
 *                       (`qa.audio.event-integrity`) when the database has none
 *   QA_OFFLINE_SECONDS  offline period (default 120, the §11.2 criterion)
 *   PLAYWRIGHT_BROWSERS_PATH  installed Chromium (/opt/pw-browsers); never `playwright install`
 *
 * Scenario: the device clock runs 5 minutes behind and the package hook
 * reports `{ package_code: 'pkt-qa-01', package_mode: 'daring' }`. A new
 * student signs up and, with gameplay and audio events through the page's own
 * core/events.js:
 *   a. plays on two pages online;
 *   b. navigates fast: three pages, leaving each right after its events;
 *   c. loses the network for the offline period while events keep coming
 *      (one per second, batches fail), comes back and opens another page;
 *   d. crashes mid-batch: a burst over the flush threshold sends one batch
 *      while more events wait in memory, and the renderer dies at once (no
 *      pagehide, no beacon); a new tab of the same browser opens the map.
 * Then it waits until the browser's offline queue is empty (the flush
 * interval is 30 s). Checks:
 *   1. the session has app_client_version = {app_version}+pkt-qa-01 and package_mode daring;
 *   2. every emitted event is stored exactly once (game_event_logs), and so is
 *      every audio event (audio_usage_events);
 *   3. sequence_no increases in emission order across all pages and the crash;
 *   4. clock_offset_ms is set on every client event and close to +300000;
 *   5. each page's events sit between its screen_viewed and the next one.
 * It also prints the telemetry requests per endpoint.
 *
 * Exits with code 1 when a check fails. Never run against production.
 */
import { execFileSync } from 'node:child_process';
import { createRequire } from 'node:module';

const BASE = (process.env.QA_BASE_URL || 'http://localhost:8080').replace(/\/$/, '');
const MYSQL = (process.env.QA_MYSQL || 'mysql -uroot gelita').split(/\s+/);
const OFFLINE_SECONDS = Number(process.env.QA_OFFLINE_SECONDS || 120);
const PASSWORD = 'Qa#Gelita2026';
const SKEW_MS = -300_000;
const DRAIN_TIMEOUT_MS = 90_000;
const CRASH_BURST = 45;   // over the default flush threshold (40): one batch leaves, the rest waits

const require = createRequire(import.meta.url);
let chromium;
try {
  ({ chromium } = require('playwright'));
} catch {
  ({ chromium } = require(`${execFileSync('npm', ['root', '-g']).toString().trim()}/playwright`));
}

const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));
const q = (value) => `'${String(value).replace(/'/g, "''")}'`;
const sql = (query) => execFileSync(MYSQL[0], [...MYSQL.slice(1), '-N', '-B', '-e', query]).toString().trim();
const rows = (query) => sql(query).split('\n').filter(Boolean).map((line) => line.split('\t'));

let failures = 0;
function check(ok, label, detail = '') {
  console.log(`${ok ? 'PASS' : 'FAIL'}  ${label}${ok || !detail ? '' : ` — ${detail}`}`);
  if (!ok) failures++;
}

/** An audio asset id for audio telemetry; adds a placeholder on a database without narration. */
function audioAssetId() {
  const [[found = '']] = rows('SELECT COALESCE(MIN(id), \'\') FROM audio_assets').concat([['']]);
  if (found) return Number(found);
  sql(`INSERT INTO media_assets (asset_key, asset_type, storage_path, mime_type, is_placeholder)
    VALUES ('qa.audio.event-integrity', 'audio', 'qa/event-integrity.mp3', 'audio/mpeg', 1)`);
  sql(`INSERT INTO audio_assets (media_asset_id, locale, context_code, transcript)
    SELECT id, 'id', 'qa_event_integrity', 'QA' FROM media_assets WHERE asset_key = 'qa.audio.event-integrity'`);
  return Number(rows('SELECT MIN(id) FROM audio_assets')[0][0]);
}

/**
 * Emits `count` marked events through the page's own core/events.js; every
 * `audioEvery`-th marker also emits an audio event (listened_ms = marker).
 * `burst` emits them all in one task, so none can leave before the last one.
 */
const audioMarkers = [];
async function emitMarked(page, from, count, { everyMs = 0, audioEvery = 0, burst = false } = {}) {
  const marks = Array.from({ length: count }, (_, i) => ({ n: from + i, audio: audioEvery > 0 && (from + i) % audioEvery === 0 }));
  for (const mark of marks) if (mark.audio) audioMarkers.push(mark.n);
  for (const group of burst ? [marks] : marks.map((mark) => [mark])) {
    await page.evaluate(async ({ group, assetId }) => {
      const events = await import(new URL('assets/js/core/events.js', document.baseURI || location.href).href.replace(/\?.*$/, ''));
      for (const { n, audio } of group) {
        events.emit('page_visible', { payload: { screen: document.body.dataset.screenCode || null, qa: n } });
        if (audio) events.emitAudio({ audio_asset_id: assetId, action: 'play', play_index: 1, listened_ms: n, completed: false });
      }
    }, { group, assetId: AUDIO_ASSET });
    if (everyMs) await sleep(everyMs);
  }
}

const queued = (page) => page.evaluate(() => Object.keys(localStorage).filter((k) => k.startsWith('gelita.queue.'))
  .reduce((n, k) => n + JSON.parse(localStorage.getItem(k) || '[]').length, 0));

async function open(page, path) {
  await page.goto(`${BASE}${path}`);
  await page.waitForLoadState('networkidle');
}

const AUDIO_ASSET = audioAssetId();
const browser = await chromium.launch({ executablePath: process.env.QA_CHROMIUM || undefined });
const context = await browser.newContext();

await context.addInitScript(({ skew }) => {
  // Device clock 5 minutes behind
  const RealDate = Date;
  class SkewedDate extends RealDate {
    constructor(...args) { super(...(args.length ? args : [RealDate.now() + skew])); }
    static now() { return RealDate.now() + skew; }
  }
  window.Date = SkewedDate;
  // core/package-boot.js rewrites the hook when package mode is off; keep the test's value
  const hook = { package_code: 'pkt-qa-01', package_mode: 'daring' };
  Object.defineProperty(window, 'GELITA_PACKAGE', { get: () => hook, set: () => {}, configurable: true });
}, { skew: SKEW_MS });

const requests = {};
context.on('request', (request) => {
  const path = new URL(request.url()).pathname;
  if (request.method() === 'POST' && /\/api\/(events|audio-events|session\/heartbeat)$/.test(path)) {
    const key = path.replace(/^.*\/api\//, '');
    requests[key] = (requests[key] || 0) + 1;
  }
});

const page = await context.newPage();
const username = `qa_seq_${Date.now().toString(36)}`.slice(0, 30);

await page.goto(`${BASE}/daftar/1`);
await page.fill('#display_name', 'QA urutan');
await page.fill('#age', '11');
await page.check('input[name="gender"][value="perempuan"]');
await Promise.all([page.waitForURL(/daftar\/2/), page.click('form[action*="daftar"] button[type="submit"]')]);
await page.selectOption('#country_code', 'ID');
await page.selectOption('#province_code', '34');
await page.waitForSelector('#district_code option[value="34.71"]', { state: 'attached' });
await page.selectOption('#district_code', '34.71');
await page.fill('#school_name', 'Sekolah Uji Peramban');
await Promise.all([page.waitForURL(/daftar\/3/), page.click('form[action*="daftar"] button[type="submit"]')]);
await page.fill('#username', username);
await page.fill('#password', PASSWORD);
await page.fill('#password_confirm', PASSWORD);
await page.check('input[name="assent"]');
await Promise.all([page.waitForURL(/intro/), page.click('form[action*="daftar"] button[type="submit"]')]);

const [session] = rows(`SELECT s.id, s.app_client_version, s.package_code, s.package_mode, r.app_version
  FROM game_sessions s JOIN participants p ON p.id = s.participant_id JOIN game_releases r ON r.id = s.release_id
  WHERE p.username = ${q(username)}`);
const [sessionId, appClientVersion, packageCode, packageMode, appVersion] = session;
check(appClientVersion === `${appVersion}+pkt-qa-01`, 'app_client_version = {app_version}+{package_code}', appClientVersion);
check(packageCode === 'pkt-qa-01' && packageMode === 'daring', 'package_code and package_mode from the hook', `${packageCode} ${packageMode}`);

// a. Page 1 and page 2, online
let marker = 1;
await open(page, '/intro/selesai');
await emitMarked(page, marker, 5, { audioEvery: 2 }); marker += 5;
await open(page, '/profil');
await emitMarked(page, marker, 5, { audioEvery: 2 }); marker += 5;

// b. Fast navigation: leave each page right after its events (pagehide beacon)
for (const path of ['/peta', '/pustaka', '/profil']) {
  await page.goto(`${BASE}${path}`);
  await page.waitForLoadState('domcontentloaded');
  await emitMarked(page, marker, 3, { audioEvery: 3 }); marker += 3;
}
await open(page, '/peta');

// c. Offline period on the same page: events keep coming, batches fail
console.log(`… offline for ${OFFLINE_SECONDS} s`);
await context.setOffline(true);
await emitMarked(page, marker, OFFLINE_SECONDS, { everyMs: 1_000, audioEvery: 10 }); marker += OFFLINE_SECONDS;
const stored = await queued(page);
check(stored > 0, 'events kept in the offline queue', String(stored));

// Back online: the next page sends the queue with its first batch
await context.setOffline(false);
await open(page, '/peta');
await emitMarked(page, marker, 3, { audioEvery: 3 }); marker += 3;

// d. Crash mid-batch: one batch leaves at the threshold, the rest dies with the renderer
await open(page, '/profil');
await emitMarked(page, marker, CRASH_BURST, { audioEvery: 5, burst: true }); marker += CRASH_BURST;
const cdp = await context.newCDPSession(page);
await Promise.race([cdp.send('Page.crash').catch(() => {}), sleep(2_000)]);
console.log('… renderer crashed mid-batch');

const after = await context.newPage();
await open(after, '/peta');
const started = Date.now();
while (await queued(after) > 0 && Date.now() - started < DRAIN_TIMEOUT_MS) await sleep(1_000);
check(await queued(after) === 0, 'offline queue empty after the crash (every event acknowledged)', String(await queued(after)));
await sleep(1_000);

const total = marker - 1;
const events = rows(`SELECT JSON_VALUE(payload_json, '$.qa'), sequence_no, clock_offset_ms, client_event_id
  FROM game_event_logs WHERE session_id = ${Number(sessionId)} AND JSON_VALUE(payload_json, '$.qa') IS NOT NULL
  ORDER BY sequence_no, id`);
const markers = events.map((row) => Number(row[0]));
check(events.length === total, 'every emitted event stored once', `${events.length} of ${total}`);
check(new Set(events.map((row) => row[3])).size === events.length, 'no duplicate client_event_id');
check(markers.every((m, i) => m === i + 1), 'sequence_no follows emission order across pages', markers.join(','));
const sequence = events.map((row) => Number(row[1]));
check(sequence.every((n, i) => i === 0 || n > sequence[i - 1]), 'sequence_no strictly increasing', sequence.join(','));
check(events.every((row) => row[2] !== 'NULL' && Math.abs(Number(row[2]) + SKEW_MS) < 10_000), 'clock_offset_ms ≈ +300000 on every event',
  events.map((row) => row[2]).slice(0, 5).join(','));

const audio = rows(`SELECT listened_ms FROM audio_usage_events WHERE session_id = ${Number(sessionId)}
  AND audio_asset_id = ${AUDIO_ASSET} ORDER BY listened_ms`).map((row) => Number(row[0]));
const audioOnce = audio.length === audioMarkers.length && audioMarkers.slice().sort((a, b) => a - b).every((m, i) => audio[i] === m);
check(audioOnce, 'every audio event stored once (audio_usage_events)', `${audio.length} of ${audioMarkers.length}`);

// Server events interleave correctly: each marked event follows its own page's screen_viewed
let screen = null;
let misplaced = 0;
for (const [type, payload] of rows(`SELECT event_type, payload_json FROM game_event_logs
  WHERE session_id = ${Number(sessionId)} ORDER BY sequence_no, id`)) {
  const data = JSON.parse(payload === 'NULL' ? '{}' : payload);
  if (type === 'screen_viewed') screen = data.screen;
  else if (data.qa !== undefined && data.screen !== screen) misplaced++;
}
check(misplaced === 0, 'every event sits after its page\'s screen_viewed in sequence_no order', `${misplaced} misplaced`);

const [[serverScreen]] = rows(`SELECT COUNT(*) FROM game_event_logs WHERE session_id = ${Number(sessionId)}
  AND event_type = 'screen_viewed' AND clock_offset_ms = 0`);
check(Number(serverScreen) > 0, 'server-written screen_viewed has clock_offset_ms 0', serverScreen);

console.log(`\nTelemetry requests (fetch and beacon): ${JSON.stringify(requests)}`);
await browser.close();
console.log(failures ? `\n${failures} check(s) failed` : '\nAll checks passed');
process.exit(failures ? 1 : 0);
