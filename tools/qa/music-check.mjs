#!/usr/bin/env node
/**
 * Background music check (display plan Phase 0 item 1, docs/arsip/rencana-perbaikan-tampilan.md).
 *
 * Loads the real public/assets/js/core/audio.js in Chromium (Playwright) with
 * a stub `Howl`/`Howler`, and checks the music rules:
 *
 *   - a screen asks for its track before the first tap: nothing plays yet;
 *   - the first unlock() starts the requested track;
 *   - a second unlock() does not restart it;
 *   - setEnabled(false) stops the music;
 *   - a track asked for while sound is off is the one that plays when sound
 *     is turned back on;
 *   - stopMusic() clears the request (no music after sound off/on or unlock).
 *
 * No PHP server or database is needed: the page and the files under
 * public/assets/ are served from disk through Playwright request routing.
 *
 * Usage:
 *   node tools/qa/music-check.mjs
 *
 * Chromium is preinstalled (PLAYWRIGHT_BROWSERS_PATH); never `playwright install`.
 * Exit code 0 when every check passes, 1 otherwise.
 */
import { createRequire } from 'node:module';
import { execSync } from 'node:child_process';
import { readFile } from 'node:fs/promises';
import { dirname, extname, join, normalize, sep } from 'node:path';
import { fileURLToPath } from 'node:url';

const require = createRequire(import.meta.url);

function loadPlaywright() {
  try { return require('playwright'); } catch { /* fall back to the global install */ }
  const globalRoot = execSync('npm root -g').toString().trim();
  return require(`${globalRoot}/playwright`);
}
const { chromium } = loadPlaywright();

const PUBLIC = join(dirname(fileURLToPath(import.meta.url)), '..', '..', 'public');
const ORIGIN = 'http://music-check.test';
const TYPES = { '.js': 'text/javascript', '.mjs': 'text/javascript', '.json': 'application/json', '.css': 'text/css' };

const failures = [];
const check = (ok, label, detail = '') => {
  console.log(`${ok ? 'ok  ' : 'FAIL'} ${label}${detail ? ` — ${detail}` : ''}`);
  if (!ok) failures.push(label);
};

/**
 * Harness page: stub Howl records play/stop per sound, then the real
 * core/audio.js is imported and its Sfx exposed as window.Sfx.
 */
const HARNESS = `<!doctype html>
<html lang="id"><head><meta charset="utf-8"><title>music-check</title>
<script>
  window.qaLog = [];
  window.Howler = { volume() {} };
  window.Howl = class {
    constructor(options) {
      this.src = options.src[0].replace(/^.*assets\\/audio\\//, '').replace(/\\.mp3$/, '');
      this.playing = false;
      (window.qaSounds ||= []).push(this);
    }
    play() { this.playing = true; window.qaLog.push('play ' + this.src); }
    stop() { this.playing = false; window.qaLog.push('stop ' + this.src); }
  };
  // Which music tracks are sounding now
  window.qaPlaying = () => (window.qaSounds || []).filter((s) => s.playing && s.src.startsWith('music/')).map((s) => s.src);
</script>
</head><body data-base="/">
<script type="module">
  import { Sfx } from '/assets/js/core/audio.js';
  window.Sfx = Sfx;
  window.qaReady = true;
</script>
</body></html>`;

async function serve(route) {
  const url = new URL(route.request().url());

  if (url.pathname === '/') {
    return route.fulfill({ status: 200, contentType: 'text/html', body: HARNESS });
  }

  if (url.pathname.startsWith('/assets/')) {
    const file = normalize(join(PUBLIC, decodeURIComponent(url.pathname)));
    if (file.startsWith(PUBLIC + sep)) {
      try {
        const body = await readFile(file);
        return route.fulfill({ status: 200, contentType: TYPES[extname(file)] || 'application/octet-stream', body });
      } catch { /* missing file → 404 below */ }
    }
  }

  // API calls (event telemetry) and missing files
  return route.fulfill({ status: url.pathname.startsWith('/api/') ? 204 : 404, body: '' });
}

const browser = await chromium.launch();

try {
  const context = await browser.newContext();
  await context.route(`${ORIGIN}/**`, serve);

  const page = await context.newPage();
  page.on('pageerror', (error) => check(false, 'no page error', error.message));
  await page.goto(`${ORIGIN}/`);
  await page.waitForFunction(() => window.qaReady === true, null, { timeout: 10_000 });

  const playing = () => page.evaluate(() => window.qaPlaying());
  const plays = (track) => page.evaluate((t) => window.qaLog.filter((line) => line === `play music/${t}`).length, track);
  const same = (a, b) => JSON.stringify(a) === JSON.stringify(b);

  // 1. The screen asks for its track at load, before any tap
  await page.evaluate(() => window.Sfx.music('map'));
  let now = await playing();
  check(same(now, []), 'no music before the first unlock', `playing: ${JSON.stringify(now)}`);

  // 2. First tap
  await page.evaluate(() => window.Sfx.unlock());
  now = await playing();
  check(same(now, ['music/map']), 'the requested track starts on the first unlock', `playing: ${JSON.stringify(now)}`);

  // 3. Later taps (start card, curtain) unlock again
  await page.evaluate(() => window.Sfx.unlock());
  now = await playing();
  const mapPlays = await plays('map');
  check(same(now, ['music/map']) && mapPlays === 1, 'no restart on a second unlock', `playing: ${JSON.stringify(now)}, play calls: ${mapPlays}`);

  // 4. Sound switched off
  await page.evaluate(() => window.Sfx.setEnabled(false));
  now = await playing();
  check(same(now, []), 'music stops when sound is turned off', `playing: ${JSON.stringify(now)}`);

  // 5. A new screen asks for another track while sound is off, then sound comes back
  await page.evaluate(() => window.Sfx.music('region'));
  now = await playing();
  check(same(now, []), 'no music while sound is off', `playing: ${JSON.stringify(now)}`);
  await page.evaluate(() => window.Sfx.setEnabled(true));
  now = await playing();
  check(same(now, ['music/region']), 'the newest requested track plays when sound is turned back on', `playing: ${JSON.stringify(now)}`);

  // 6. stopMusic() clears the request
  await page.evaluate(() => window.Sfx.stopMusic());
  now = await playing();
  check(same(now, []), 'stopMusic() stops the music', `playing: ${JSON.stringify(now)}`);
  await page.evaluate(() => { window.Sfx.setEnabled(false); window.Sfx.setEnabled(true); window.Sfx.unlock(); });
  now = await playing();
  check(same(now, []), 'stopMusic() clears the request (nothing on sound on or unlock)', `playing: ${JSON.stringify(now)}`);

  await context.close();
} catch (error) {
  check(false, 'harness ran', error.message);
} finally {
  await browser.close();
}

if (failures.length) {
  console.log(`\n${failures.length} check(s) failed.`);
  process.exit(1);
}
console.log('\nAll music checks passed.');
