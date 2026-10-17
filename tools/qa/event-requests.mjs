#!/usr/bin/env node
/**
 * Telemetry requests per playing student (docs/rencana-log-asinkron.md §7,
 * §8): counts POST /api/events, /api/audio-events and /api/session/heartbeat
 * (fetch and beacon) while one student keeps playing in real Chromium.
 *
 *   php spark serve                          # local app on a MariaDB/MySQL test database
 *   node tools/qa/event-requests.mjs         # 4 minutes of play
 *   QA_MINUTES=2 node tools/qa/event-requests.mjs
 *
 * Environment:
 *   QA_BASE_URL   app URL (default http://localhost:8080)
 *   QA_MYSQL      database client command (default "mysql -uroot gelita"); used only
 *                 to find an audio asset id (or add the placeholder
 *                 `qa.audio.event-integrity`, as event-integrity.mjs does)
 *   QA_MINUTES    play time (default 4)
 *   PLAYWRIGHT_BROWSERS_PATH  installed Chromium (/opt/pw-browsers); never `playwright install`
 *
 * Scripted play: one gameplay event every 2 s, one audio event every 6 s
 * (a narrated screen), and a page change every 45 s through peta → profil →
 * pustaka. Prints the requests per endpoint and per minute. Run it once with
 * the old client and once with the new one to compare. Never run against
 * production.
 */
import { execFileSync } from 'node:child_process';
import { createRequire } from 'node:module';

const BASE = (process.env.QA_BASE_URL || 'http://localhost:8080').replace(/\/$/, '');
const MYSQL = (process.env.QA_MYSQL || 'mysql -uroot gelita').split(/\s+/);
const MINUTES = Number(process.env.QA_MINUTES || 4);
const PASSWORD = 'Qa#Gelita2026';
const EVENT_MS = 2_000;
const AUDIO_EVERY = 3;       // every third tick: one audio event per 6 s
const PAGE_MS = 45_000;
const PAGES = ['/peta', '/profil', '/pustaka'];

const require = createRequire(import.meta.url);
let chromium;
try {
  ({ chromium } = require('playwright'));
} catch {
  ({ chromium } = require(`${execFileSync('npm', ['root', '-g']).toString().trim()}/playwright`));
}

const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));
const sql = (query) => execFileSync(MYSQL[0], [...MYSQL.slice(1), '-N', '-B', '-e', query]).toString().trim();

function audioAssetId() {
  const found = sql('SELECT COALESCE(MIN(id), \'\') FROM audio_assets');
  if (found) return Number(found);
  sql(`INSERT INTO media_assets (asset_key, asset_type, storage_path, mime_type, is_placeholder)
    VALUES ('qa.audio.event-integrity', 'audio', 'qa/event-integrity.mp3', 'audio/mpeg', 1)`);
  sql(`INSERT INTO audio_assets (media_asset_id, locale, context_code, transcript)
    SELECT id, 'id', 'qa_event_integrity', 'QA' FROM media_assets WHERE asset_key = 'qa.audio.event-integrity'`);
  return Number(sql('SELECT MIN(id) FROM audio_assets'));
}

const AUDIO_ASSET = audioAssetId();
const browser = await chromium.launch({ executablePath: process.env.QA_CHROMIUM || undefined });
const context = await browser.newContext();
const page = await context.newPage();
const username = `qa_req_${Date.now().toString(36)}`.slice(0, 30);

await page.goto(`${BASE}/daftar/1`);
await page.fill('#display_name', 'QA permintaan');
await page.fill('#age', '11');
await page.check('input[name="gender"][value="laki-laki"]');
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

const counts = {};
let counting = false;
context.on('request', (request) => {
  const path = new URL(request.url()).pathname;
  if (counting && request.method() === 'POST' && /\/api\/(events|audio-events|session\/heartbeat)$/.test(path)) {
    const key = path.replace(/^.*\/api\//, '');
    counts[key] = (counts[key] || 0) + 1;
  }
});

console.log(`… playing for ${MINUTES} min`);
await page.goto(`${BASE}${PAGES[0]}`);
counting = true;
const started = Date.now();
let nextPage = started + PAGE_MS;
let pageIndex = 0;
for (let tick = 0; Date.now() - started < MINUTES * 60_000; tick++) {
  if (Date.now() >= nextPage) {
    pageIndex = (pageIndex + 1) % PAGES.length;
    await page.goto(`${BASE}${PAGES[pageIndex]}`);
    nextPage += PAGE_MS;
  }
  await page.evaluate(async ({ tick, audio, assetId }) => {
    const events = await import(new URL('assets/js/core/events.js', document.baseURI || location.href).href.replace(/\?.*$/, ''));
    events.emit('page_visible', { payload: { screen: document.body.dataset.screenCode || null, qa_tick: tick } });
    if (audio) events.emitAudio({ audio_asset_id: assetId, action: 'play', play_index: 1, listened_ms: tick, completed: false });
  }, { tick, audio: tick % AUDIO_EVERY === 0, assetId: AUDIO_ASSET });
  await sleep(EVENT_MS);
}
counting = false;

const minutes = (Date.now() - started) / 60_000;
const total = Object.values(counts).reduce((sum, n) => sum + n, 0);
console.log(JSON.stringify({ minutes: Number(minutes.toFixed(2)), requests: counts, total, per_minute: Number((total / minutes).toFixed(1)) }));
await browser.close();
