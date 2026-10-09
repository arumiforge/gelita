#!/usr/bin/env node
/**
 * Browser check of asset package Stage 5, research data integrity
 * (docs/rencana-paket-aset.md §7.1, §7.3, §11.2), with real Chromium.
 *
 *   php spark serve                          # local app on a MariaDB/MySQL test database
 *   node tools/qa/event-integrity.mjs        # full run (2 minutes offline)
 *   QA_OFFLINE_SECONDS=20 node tools/qa/event-integrity.mjs   # shorter offline period
 *
 * Environment:
 *   QA_BASE_URL         app URL (default http://localhost:8080)
 *   QA_MYSQL            database client command (default "mysql -uroot gelita"); the
 *                       script reads game_sessions and game_event_logs only
 *   QA_OFFLINE_SECONDS  offline period (default 120, the §11.2 criterion)
 *   PLAYWRIGHT_BROWSERS_PATH  installed Chromium (/opt/pw-browsers); never `playwright install`
 *
 * Scenario: the device clock runs 5 minutes behind and the package hook
 * reports `{ package_code: 'pkt-qa-01', package_mode: 'daring' }`. A new
 * student signs up, plays on two pages, loses the network for the offline
 * period while events keep coming (one per second, flushes fail), comes back
 * and opens another page. Checks:
 *   1. the session has app_client_version = {app_version}+pkt-qa-01 and package_mode daring;
 *   2. every emitted event is stored exactly once;
 *   3. sequence_no increases in emission order across the three pages;
 *   4. clock_offset_ms is set on every client event and close to +300000;
 *   5. each page's events sit between its screen_viewed and the next one.
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

const require = createRequire(import.meta.url);
let chromium;
try {
  ({ chromium } = require('playwright'));
} catch {
  ({ chromium } = require(`${execFileSync('npm', ['root', '-g']).toString().trim()}/playwright`));
}

const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));
const q = (value) => `'${String(value).replace(/'/g, "''")}'`;
const rows = (query) => execFileSync(MYSQL[0], [...MYSQL.slice(1), '-N', '-B', '-e', query]).toString().trim()
  .split('\n').filter(Boolean).map((line) => line.split('\t'));

let failures = 0;
function check(ok, label, detail = '') {
  console.log(`${ok ? 'PASS' : 'FAIL'}  ${label}${ok || !detail ? '' : ` — ${detail}`}`);
  if (!ok) failures++;
}

/** Emits `count` marked events through the page's own core/events.js. */
async function emitMarked(page, from, count, everyMs = 0) {
  for (let i = 0; i < count; i++) {
    await page.evaluate(async (n) => {
      const events = await import(new URL('assets/js/core/events.js', document.baseURI || location.href).href.replace(/\?.*$/, ''));
      events.emit('page_visible', { payload: { screen: document.body.dataset.screenCode || null, qa: n } });
    }, from + i);
    if (everyMs) await sleep(everyMs);
  }
}

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
  window.GELITA_PACKAGE = { package_code: 'pkt-qa-01', package_mode: 'daring' };
}, { skew: SKEW_MS });

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

// Page 1 and page 2, online
await page.goto(`${BASE}/intro/selesai`);
await page.waitForLoadState('networkidle');
await emitMarked(page, 1, 5);
await page.goto(`${BASE}/profil`);
await page.waitForLoadState('networkidle');
await emitMarked(page, 6, 5);
await sleep(3_500);

// Offline period on the same page: events keep coming, flushes fail
console.log(`… offline for ${OFFLINE_SECONDS} s`);
await context.setOffline(true);
await emitMarked(page, 11, OFFLINE_SECONDS, 1_000);
const stored = await page.evaluate(() => Object.keys(localStorage).filter((k) => k.startsWith('gelita.queue.'))
  .reduce((n, k) => n + JSON.parse(localStorage.getItem(k) || '[]').length, 0));
check(stored > 0, 'events kept in the offline queue', String(stored));

// Back online: the next page re-sends the queue
await context.setOffline(false);
await page.goto(`${BASE}/peta`);
await page.waitForLoadState('networkidle');
await emitMarked(page, 11 + OFFLINE_SECONDS, 3);
await sleep(4_000);

const total = 10 + OFFLINE_SECONDS + 3;
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

await browser.close();
console.log(failures ? `\n${failures} check(s) failed` : '\nAll checks passed');
process.exit(failures ? 1 : 0);
