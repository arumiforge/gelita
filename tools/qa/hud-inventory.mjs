#!/usr/bin/env node
/**
 * HUD inventory (plan 41 §4 no. 1, uniform HUD).
 *
 * Visits the game screens of tools/qa/stage-check.mjs on a running GELITA and
 * records, per screen, the HUD as the student sees it: band height
 * (hudStyle), layout (hudLayout), the logo's size and place, and every round
 * control (language, sound, menu, Back, the challenge's Keluar) with its size
 * and centre, all in stage dp (1280 × 720). Prints a Markdown table and
 * writes it to writable/qa/hud-inventory.md, so a before/after pair shows
 * what the uniform HUD changed.
 *
 * Usage (development install only, like stage-check):
 *   node tools/qa/hud-inventory.mjs --user=NAME --pass=PASSWORD [--base=http://localhost:8080/] [--size=1366x768]
 * The student must have seen the opening story; the study's region mode
 * should be "free" so all three regions open.
 */
import { createRequire } from 'node:module';
import { execSync } from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';

// Playwright is installed globally in the cloud container; an ESM import cannot see it, so load
// it the way stage-check.mjs does: the local package first, else the global one.
const require = createRequire(import.meta.url);
function loadPlaywright() {
  try { return require('playwright'); } catch { /* fall back to the global install */ }
  const globalRoot = execSync('npm root -g').toString().trim();
  return require(path.join(globalRoot, 'playwright'));
}
const { chromium } = loadPlaywright();

const args = Object.fromEntries(process.argv.slice(2).map((a) => a.replace(/^--/, '').split('=')));
const BASE = (args.base || 'http://localhost:8080/').replace(/\/?$/, '/');
const [W, H] = String(args.size || '1366x768').split('x').map(Number);
if (!args.user || !args.pass) {
  console.error('Usage: node tools/qa/hud-inventory.mjs --user=NAME --pass=PASSWORD');
  process.exit(2);
}

const REGIONS = ['temanggung', 'magelang', 'wonosobo'];
const screens = [
  ['awal', '', true], ['mulai', 'mulai', true], ['masuk', 'masuk', true], ['daftar-1', 'daftar/1', true], ['tentang', 'tentang', true],
  ['gerbang', 'gerbang'], ['intro', 'intro'], ['peta', 'peta'], ['pustaka', 'pustaka'], ['profil', 'profil'], ['ganti-sandi', 'ganti-sandi'],
  ['refleksi', 'refleksi'], ['penutup', 'penutup'],
];
for (const region of REGIONS) {
  screens.push([`dialog-${region}`, `dialog/${region}`]);
  screens.push([`wilayah-${region}`, `wilayah/${region}`]);
  screens.push([`misi-${region}-1`, `misi/${region}/1`]);
  screens.push([`tantangan-${region}-2`, `tantangan/${region}/2`]);
}

const browser = await chromium.launch();
const guest = await browser.newContext({ viewport: { width: W, height: H } });
const ctx = await browser.newContext({ viewport: { width: W, height: H } });
const login = await ctx.newPage();
await login.goto(BASE + 'masuk');
await login.fill('#username', args.user);
await login.fill('#password', args.pass);
await login.press('#password', 'Enter');
await login.waitForLoadState('networkidle');

const rows = [];
for (const [name, route, asGuest] of screens) {
  const page = await (asGuest ? guest : ctx).newPage();
  await page.goto(BASE + route);
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(400);
  const r = await page.evaluate(() => {
    const stage = document.getElementById('stage');
    if (!stage) return null;
    const s = stage.getBoundingClientRect();
    const k = 1280 / s.width;
    const box = (el) => {
      if (!el) return null;
      const b = el.getBoundingClientRect();
      if (b.width === 0 && b.height === 0) return null;
      return { x: Math.round((b.left - s.left) * k), y: Math.round((b.top - s.top) * k), w: Math.round(b.width * k), h: Math.round(b.height * k), cx: Math.round((b.left + b.width / 2 - s.left) * k), cy: Math.round((b.top + b.height / 2 - s.top) * k) };
    };
    const hud = document.querySelector('header.hud');
    const head = document.querySelector('.challenge-head');
    const ctl = (sel) => { const el = document.querySelector(sel); const art = el?.querySelector('img.hud-ctl-art, .hud-ctl-flag') ?? el; return box(art); };
    return {
      cls: hud ? hud.className.replace('hud hud-menu-style', '').trim() || 'full' : (head ? 'challenge header' : 'none'),
      band: box(hud)?.h ?? (head ? box(head).h : 0),
      logo: box(document.querySelector('.hud-identity-logo')),
      lang: ctl('.hud-lang > .hud-ctl'),
      sound: ctl('#btn-sound'),
      menu: ctl('.hud-menu > .hud-ctl'),
      back: ctl('.hud .hud-back') ?? box(document.querySelector('.hud-back-plate')),
      exit: ctl('.challenge-exit > summary'),
      url: location.pathname,
    };
  });
  await page.close();
  if (!r) continue;
  rows.push([name, r]);
}
await browser.close();

const fmt = (b) => (b ? `${b.w}×${b.h} @${b.cx},${b.cy}` : '—');
const lines = [
  `| Screen | Path | HUD | Band | Logo | Language | Sound | Menu | Back | Keluar |`,
  `| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |`,
  ...rows.map(([name, r]) => `| ${name} | \`${r.url}\` | ${r.cls} | ${r.band} | ${fmt(r.logo)} | ${fmt(r.lang)} | ${fmt(r.sound)} | ${fmt(r.menu)} | ${fmt(r.back)} | ${fmt(r.exit)} |`),
];
const out = path.resolve('writable/qa/hud-inventory.md');
fs.mkdirSync(path.dirname(out), { recursive: true });
fs.writeFileSync(out, `HUD inventory at ${W} × ${H}, sizes and centres in stage dp (1280 × 720)\n\n${lines.join('\n')}\n`);
console.log(lines.join('\n'));
console.log(`\nWritten: ${out}`);
