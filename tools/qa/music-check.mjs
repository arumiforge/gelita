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
 *   - stopMusic() clears the request (no music after sound off/on or unlock);
 *   - a new track crossfades in and the old one stops after its fade, and a
 *     track asked for again while it fades out is faded back up, not started twice;
 *   - while a narration voice plays the music is held at 20 % of its level and
 *     comes back after a pause longer than the gap between two slides, not
 *     between them (owner, 19 October 2026).
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
      this.on = false;
      this.level = options.volume ?? 1;
      this.handlers = {};
      (window.qaSounds ||= []).push(this);
    }
    play() { this.on = true; window.qaLog.push('play ' + this.src); }
    stop() { this.on = false; window.qaLog.push('stop ' + this.src); }
    playing() { return this.on; }
    volume(v) { if (v === undefined) return this.level; this.level = v; return this; }
    once(event, fn) { (this.handlers[event] ||= []).push(fn); return this; }
    // Like Howler: the level moves at once here, the 'fade' event comes after the duration
    fade(from, to, ms) {
      this.level = to;
      clearTimeout(this.fadeTimer);
      this.fadeTimer = setTimeout(() => (this.handlers.fade || []).splice(0).forEach((fn) => fn()), ms);
      return this;
    }
  };
  // Which music tracks are sounding now, and the level of one
  window.qaPlaying = () => (window.qaSounds || []).filter((s) => s.on && s.src.startsWith('music/')).map((s) => s.src);
  window.qaLevel = (src) => (window.qaSounds || []).find((s) => s.src === src)?.level;
</script>
</head><body data-base="/">
<div class="audio-player" id="voice" data-src="/qa/voice.wav"></div>
<script type="module">
  import { Sfx, narrationPlayer } from '/assets/js/core/audio.js';
  window.Sfx = Sfx;
  window.qaVoice = narrationPlayer(document.getElementById('voice'));
  window.qaReady = true;
</script>
</body></html>`;

async function serve(route) {
  const url = new URL(route.request().url());

  if (url.pathname === '/') {
    return route.fulfill({ status: 200, contentType: 'text/html', body: HARNESS });
  }

  if (url.pathname === '/qa/voice.wav') {
    return route.fulfill({ status: 200, contentType: 'audio/wav', body: silentWav(4) });
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

/** A silent mono WAV standing in for a narration line. */
function silentWav(seconds) {
  const rate = 8000;
  const data = Buffer.alloc(rate * seconds, 128);
  const head = Buffer.alloc(44);
  head.write('RIFF', 0); head.writeUInt32LE(36 + data.length, 4); head.write('WAVE', 8);
  head.write('fmt ', 12); head.writeUInt32LE(16, 16); head.writeUInt16LE(1, 20); head.writeUInt16LE(1, 22);
  head.writeUInt32LE(rate, 24); head.writeUInt32LE(rate, 28); head.writeUInt16LE(1, 32); head.writeUInt16LE(8, 34);
  head.write('data', 36); head.writeUInt32LE(data.length, 40);
  return Buffer.concat([head, data]);
}

// The voice stand-in plays without a real tap
const browser = await chromium.launch({ args: ['--autoplay-policy=no-user-gesture-required'] });

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

  // 7. Crossfade: both sound during the fade, then only the new one
  await page.evaluate(() => { window.Sfx.music('map'); window.Sfx.unlock(); });
  await page.waitForTimeout(1200);
  await page.evaluate(() => window.Sfx.music('kenal-magelang'));
  now = await playing();
  check(same(now.sort(), ['music/kenal-magelang', 'music/map']), 'both tracks sound during the crossfade', `playing: ${JSON.stringify(now)}`);
  await page.waitForTimeout(1200);
  now = await playing();
  check(same(now, ['music/kenal-magelang']), 'the old track stops after its fade', `playing: ${JSON.stringify(now)}`);

  // 8. Asked for again while it fades out: faded back up, not started a second time
  const before = await plays('kenal-magelang');
  await page.evaluate(() => { window.Sfx.music('map'); window.Sfx.music('kenal-magelang'); });
  await page.waitForTimeout(1200);
  now = await playing();
  const after = await plays('kenal-magelang');
  const full = await page.evaluate(() => window.qaLevel('music/kenal-magelang'));
  check(same(now, ['music/kenal-magelang']) && after === before && full > 0.5, 'a track asked for again during its fade-out comes back up', `playing: ${JSON.stringify(now)}, play calls ${before}→${after}, level ${full}`);

  // 9. Ducking under a narration voice
  const level = () => page.evaluate(() => window.qaLevel('music/kenal-magelang'));
  const base = await level();
  await page.evaluate(() => window.qaVoice.play());
  await page.waitForFunction(() => window.qaVoice.isPlaying, null, { timeout: 5_000 });
  const low = await level();
  check(await page.evaluate(() => window.Sfx.isDucked()) && Math.abs(low - base * 0.2) < 1e-6, 'music drops to 20 % while the voice plays', `level ${base} → ${low}`);
  // A slide change: the voice stops and the next one starts within the release time
  await page.evaluate(() => window.qaVoice.pause());
  await page.waitForTimeout(500);
  check(await page.evaluate(() => window.Sfx.isDucked()), 'the music stays down in the short gap between two slides');
  await page.evaluate(() => window.qaVoice.play());
  await page.waitForTimeout(900);
  check(await page.evaluate(() => window.Sfx.isDucked()), 'still down while the next line plays');
  await page.evaluate(() => window.qaVoice.pause());
  await page.waitForTimeout(1500);
  const back = await level();
  check(!(await page.evaluate(() => window.Sfx.isDucked())) && Math.abs(back - base) < 1e-6, 'the music comes back after a longer pause', `level ${back}`);

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
