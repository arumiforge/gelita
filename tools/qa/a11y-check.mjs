#!/usr/bin/env node
/**
 * Keyboard and screen-reader check for the challenge screens (Dok 3 §12.3).
 *
 * Runs Chromium (Playwright) against a running GELITA, opens every challenge
 * in the selected regions, and checks with the keyboard only:
 *
 *   - Tab reaches every control the engine needs (blanks and words, verdict
 *     radios, puzzle pieces, hunt objects, "Cek jawabanku", hint button);
 *   - every focused control has an accessible name and a visible focus ring;
 *   - the engine can be operated by keyboard (Enter/Space/arrows);
 *   - blanks, statements, pieces, and objects carry labels;
 *   - the hint and result dialogs take focus, are labelled, and (hint) close
 *     with Escape, returning focus to the button that opened them;
 *   - the Cari Objek clue is announced (aria-live);
 *   - optional: axe-core (WCAG 2.1 A/AA) on #stage, when --axe=path/to/axe.min.js
 *     is given or `axe-core` resolves from node_modules.
 *
 * Setup (same as tools/qa/stage-check.mjs):
 *   php spark serve                     (or another server; QA_BASE_URL)
 *   a test database after gelita:reset, with the study's unlock mode set to
 *   "bebas" (admin panel → Penelitian) so all three regions can be opened.
 *
 * Usage:
 *   node tools/qa/a11y-check.mjs [--base=http://localhost:8080/] [--user=… --pass=…]
 *                                [--regions=temanggung,magelang,wonosobo] [--axe=…]
 *
 * Without --user the script registers a new test student through /daftar.
 * Checking answers and opening hints spend tries and are logged, so run it
 * only against a test database, then `gelita:reset`.
 * Chromium is preinstalled (PLAYWRIGHT_BROWSERS_PATH); never `playwright install`.
 *
 * Report: writable/qa/a11y/report.json. Exit code 0 when every check passes, 1 otherwise.
 */
import { createRequire } from 'node:module';
import { execSync } from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const require = createRequire(import.meta.url);
const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');

function loadPlaywright() {
  try { return require('playwright'); } catch { /* fall back to the global install */ }
  const globalRoot = execSync('npm root -g').toString().trim();
  return require(path.join(globalRoot, 'playwright'));
}
const { chromium } = loadPlaywright();

// ------------------------------------------------------------------ options
const args = Object.fromEntries(process.argv.slice(2).map((arg) => {
  const [key, ...rest] = arg.replace(/^--/, '').split('=');
  return [key, rest.length ? rest.join('=') : true];
}));
const BASE = String(args.base || process.env.QA_BASE_URL || 'http://localhost:8080/').replace(/\/?$/, '/');
const OUT = path.join(root, 'writable/qa/a11y');
const REGIONS = String(args.regions || 'temanggung,magelang,wonosobo').split(',').filter(Boolean);
const PASS = String(args.pass || 'Lentera!Kedu2026');

function axeSource() {
  if (args.axe) return fs.readFileSync(String(args.axe), 'utf8');
  try { return fs.readFileSync(require.resolve('axe-core/axe.min.js'), 'utf8'); } catch { return null; }
}
const AXE = axeSource();

fs.mkdirSync(OUT, { recursive: true });

const failures = [];
const report = [];
const fail = (screen, message) => {
  failures.push(`${screen}: ${message}`);
  console.log(`  ✗ ${message}`);
};
const pass = (message) => console.log(`  ✓ ${message}`);

// ------------------------------------------------------------------ in-page helpers
/** Runs in the browser: describes document.activeElement. */
function describeFocus() {
  const el = document.activeElement;
  if (!el || el === document.body) return null;
  const byIds = (ids) => ids.split(/\s+/).map((id) => document.getElementById(id)?.textContent || '').join(' ');
  const labelFor = el.id ? document.querySelector(`label[for="${CSS.escape(el.id)}"]`) : null;
  const name = (el.getAttribute('aria-label')
    || (el.getAttribute('aria-labelledby') && byIds(el.getAttribute('aria-labelledby')))
    || el.closest('label')?.textContent
    || labelFor?.textContent
    || el.textContent
    || el.getAttribute('alt')
    || el.getAttribute('title')
    || '').replace(/\s+/g, ' ').trim();
  const st = getComputedStyle(el);
  // A ring is an outline or box-shadow, or (puzzle pieces) a glow on the piece's own artwork.
  const glow = [el, ...el.querySelectorAll('*')].some((node) => /drop-shadow/.test(getComputedStyle(node).filter));
  const ring = (st.outlineStyle !== 'none' && parseFloat(st.outlineWidth) > 0) || st.boxShadow !== 'none' || glow;
  const tag = el.tagName.toLowerCase();
  // Repeated controls (e.g. one passage chip per card) look alike, so each stop gets a unique number.
  if (!el.dataset.qaStop) el.dataset.qaStop = String((window.qaStops = (window.qaStops || 0) + 1));
  return {
    key: `${tag}${el.id ? `#${el.id}` : ''}.${[...el.classList].join('.')}[${el.dataset.item || el.dataset.object || el.dataset.slot || el.dataset.wordId || el.name || ''}]`,
    stop: el.dataset.qaStop,
    tag,
    type: el.type || null,
    classes: [...el.classList],
    name,
    ring,
    inStage: Boolean(el.closest('#stage')),
    inModal: Boolean(el.closest('.modal, [role=dialog]')),
  };
}

// ------------------------------------------------------------------ flow
const browser = await chromium.launch();

/** Registers a test student (unless --user) and returns the username. */
async function signUp() {
  if (args.user) return String(args.user);
  const ctx = await browser.newContext({ viewport: { width: 1280, height: 720 } });
  const page = await ctx.newPage();
  const user = `qa${Date.now().toString(36)}`;
  await page.goto(`${BASE}daftar/1`);
  await page.fill('#display_name', 'Siswa QA Aksesibilitas');
  await page.fill('#age', '10');
  await page.check('input[name=gender]');
  await page.click('main form button[type=submit]');
  await page.waitForURL(/daftar\/2/, { waitUntil: 'domcontentloaded' });
  await page.selectOption('#province_code', { index: 1 });
  const options = await page.$$eval('#district_code option', (o) => o.filter((x) => x.value).map((x) => x.value));
  await page.selectOption('#district_code', options[0]);
  if (await page.isVisible('#school_name')) await page.fill('#school_name', 'SD Uji Aksesibilitas');
  await page.click('main form button[type=submit]');
  await page.waitForURL(/daftar\/3/, { waitUntil: 'domcontentloaded' });
  await page.fill('#username', user);
  await page.fill('#password', PASS);
  await page.fill('#password_confirm', PASS);
  await page.check('input[name=assent]');
  await page.click('main form button[type=submit]');
  await page.waitForURL(/intro|gerbang|peta/, { waitUntil: 'domcontentloaded' });
  await page.goto(`${BASE}intro/selesai`).catch(() => {});
  await ctx.close();
  console.log(`New test student: ${user}`);
  return user;
}

async function logIn(ctx, user) {
  const page = await ctx.newPage();
  await page.goto(`${BASE}masuk`);
  await page.fill('#username', user);
  await page.fill('#password', PASS);
  await page.click('main form button[type=submit]');
  await page.waitForLoadState('domcontentloaded');
  return page;
}

/** Presses Tab from the top of the page and returns the distinct stops inside #stage. */
async function tabWalk(page, limit = 120) {
  await page.evaluate(() => { document.activeElement?.blur(); window.scrollTo(0, 0); });
  const stops = [];
  const seen = new Set();
  for (let i = 0; i < limit; i++) {
    await page.keyboard.press('Tab');
    const f = await page.evaluate(describeFocus);
    if (!f) continue;
    if (seen.has(f.stop)) break; // wrapped around
    seen.add(f.stop);
    stops.push(f);
  }
  return stops;
}

const visibleCount = (page, selector) => page.locator(`${selector}:visible`).count();

async function openModalCheck(page, screen, label) {
  await page.waitForTimeout(900);
  const modal = await page.evaluate(() => {
    const m = document.querySelector('#modal-layer:not([hidden]) [role=dialog], dialog[open]');
    if (!m) return null;
    const titleId = m.getAttribute('aria-labelledby');
    return {
      modal: m.getAttribute('aria-modal'),
      title: titleId ? (document.getElementById(titleId)?.textContent || '').trim() : '',
      focusInside: m.contains(document.activeElement),
    };
  });
  if (!modal) { fail(screen, `${label}: no dialog opened`); return false; }
  if (!modal.title) fail(screen, `${label}: dialog has no title (aria-labelledby)`);
  if (!modal.focusInside) fail(screen, `${label}: focus did not move into the dialog`);
  if (modal.title && modal.focusInside) pass(`${label}: dialog "${modal.title}" took focus`);
  return true;
}

/** Keyboard operation per engine; returns nothing, records failures. */
async function operate(page, screen, engine) {
  const focus = (selector) => page.locator(`${selector}:visible`).first().focus();
  const active = () => page.evaluate(describeFocus);

  if (engine === 'rumpang') {
    await focus('.blank:not([disabled])');
    await page.keyboard.press('Enter');
    const word = page.locator('.word:visible:not([disabled]):not(.is-used)').first();
    const text = (await word.textContent()).trim();
    await word.focus();
    await page.keyboard.press('Enter');
    await page.waitForTimeout(300);
    const label = await page.locator('.blank:visible').first().getAttribute('aria-label');
    if (label?.includes(text)) pass(`rumpang: Enter on blank + Enter on word fills it (label "${label}")`);
    else fail(screen, `rumpang: keyboard fill did not reach the blank's label (got "${label}", word "${text}")`);
  } else if (engine === 'boleh') {
    await focus('.verdict-card input[type=radio]:not([disabled])');
    await page.keyboard.press('Space');
    const checked = await page.locator('.verdict-card input[type=radio]:checked').count();
    if (checked > 0) pass('boleh: Space selects a verdict');
    else fail(screen, 'boleh: Space did not select a verdict');
  } else if (engine === 'puzzle') {
    await focus('.piece');
    const first = await active();
    await page.keyboard.press('Enter');
    const pressed = await page.evaluate(() => document.activeElement?.getAttribute('aria-pressed'));
    await page.keyboard.press('ArrowRight');
    const second = await active();
    await page.keyboard.press('Enter');
    await page.waitForTimeout(400);
    const after = await page.locator('.piece[aria-pressed=true]').count();
    if (pressed === 'true' && second && second.stop !== first.stop && after === 0) pass('puzzle: Enter selects, arrow moves, Enter swaps');
    else fail(screen, `puzzle: keyboard swap failed (pressed=${pressed}, moved=${second?.stop !== first?.stop}, still selected=${after})`);
  } else if (engine === 'cari') {
    const live = await page.evaluate(() => {
      const clue = document.getElementById('clue-text');
      return clue ? (clue.getAttribute('aria-live') || clue.closest('[aria-live]')?.getAttribute('aria-live')) : null;
    });
    if (live) pass(`cari: clue is announced (aria-live=${live})`);
    else fail(screen, 'cari: clue text is not in an aria-live region');
    await focus('.object:not([disabled])');
    const before = page.url();
    await page.keyboard.press('Enter');
    await page.waitForTimeout(1200);
    const reacted = await page.evaluate(() => Boolean(document.querySelector('.object.is-found, .object.is-wrong, #toast-layer *, #modal-layer:not([hidden])')));
    if (reacted || page.url() !== before) pass('cari: Enter on an object submits a tap');
    else fail(screen, 'cari: Enter on an object had no visible effect');
  }
}

/** Taps through a narrated screen (intro, region dialog) until the server moves on. */
async function playThrough(page, route) {
  await page.goto(BASE + route);
  for (let i = 0; i < 40 && /\/(intro|dialog)/.test(page.url()); i++) {
    await page.click('.narrator-tap-btn', { timeout: 2000 }).catch(() => {});
    await page.waitForTimeout(400);
  }
}

const user = await signUp();
const ctx = await browser.newContext({ viewport: { width: 1280, height: 720 } });
const page = await logIn(ctx, user);
page.on('pageerror', (e) => failures.push(`js: ${e.message}`));
await playThrough(page, 'intro');

for (const region of REGIONS) {
  // The first visit to a region opens its story dialog; play it through so the challenges open.
  await playThrough(page, `dialog/${region}`);
  for (let n = 1; n <= 4; n++) {
    const screen = `tantangan-${region}-${n}`;
    await page.goto(`${BASE}tantangan/${region}/${n}`);
    await page.waitForLoadState('domcontentloaded');
    await page.waitForTimeout(1200);
    const engine = await page.getAttribute('[data-screen="challenge"]', 'data-engine').catch(() => null);
    console.log(`\n- ${screen} (${engine || page.url().replace(BASE, '/')})`);
    if (!engine) { fail(screen, `not a challenge screen (${page.url()})`); continue; }
    const entry = { screen, engine, stops: [], axe: null };
    report.push(entry);

    // 1. Tab order: names, focus rings, coverage
    const stops = await tabWalk(page);
    entry.stops = stops;
    const inStage = stops.filter((s) => s.inStage);
    const nameless = inStage.filter((s) => !s.name);
    const ringless = inStage.filter((s) => !s.ring);
    if (nameless.length) fail(screen, `focusable without a name: ${nameless.map((s) => s.key).join(', ')}`);
    if (ringless.length) fail(screen, `no visible focus ring: ${ringless.map((s) => s.key).join(', ')}`);
    const reached = (cls) => inStage.filter((s) => s.classes.includes(cls)).length;
    const expect = {
      rumpang: [['blank', '.blank'], ['word', '.word']],
      puzzle: [['piece', '.piece']],
      cari: [['object', '.object']],
      boleh: [],
    }[engine] || [];
    for (const [cls, selector] of expect) {
      const total = await visibleCount(page, selector);
      const got = reached(cls);
      // Puzzle pieces may use a roving tabindex: one stop for the board is enough.
      const ok = engine === 'puzzle' ? got >= 1 : got === total;
      if (ok) pass(`Tab reaches ${selector}: ${got}/${total}`);
      else fail(screen, `Tab reaches only ${got} of ${total} ${selector}`);
    }
    if (engine === 'boleh') {
      const groups = await page.locator('.verdict-card:visible').count();
      const radioStops = inStage.filter((s) => s.type === 'radio').length;
      if (radioStops >= groups) pass(`Tab reaches all ${groups} verdict groups`);
      else fail(screen, `Tab reaches ${radioStops} of ${groups} verdict groups`);
      const unlabeled = await page.$$eval('.verdict-card', (cards) => cards.filter((c) => {
        const id = c.getAttribute('aria-labelledby');
        return !id || !(document.getElementById(id)?.textContent || '').trim();
      }).length);
      if (unlabeled) fail(screen, `${unlabeled} statement cards without a label`);
      else pass('every statement card is labelled by its statement');
    }
    if (await visibleCount(page, '#btn-check')) {
      if (inStage.some((s) => s.key.startsWith('button#btn-check'))) pass('Tab reaches "Cek jawabanku"');
      else fail(screen, 'Tab does not reach #btn-check');
    }

    // 2. Labels on the engine's own controls
    const unlabeled = await page.$$eval('.blank, .piece, .object', (els) => els.filter((e) => !(e.getAttribute('aria-label') || '').trim()).length);
    if (unlabeled) fail(screen, `${unlabeled} blanks/pieces/objects without aria-label`);

    // 3. axe-core, before the state changes
    if (AXE) {
      await page.addScriptTag({ content: AXE });
      const result = await page.evaluate(async () => window.axe.run('#stage', { runOnly: { type: 'tag', values: ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa'] } }));
      entry.axe = result.violations.map((v) => ({ id: v.id, impact: v.impact, help: v.help, nodes: v.nodes.slice(0, 5).map((n) => n.target.join(' ')) }));
      const serious = entry.axe.filter((v) => ['serious', 'critical'].includes(v.impact));
      for (const v of serious) fail(screen, `axe ${v.impact} ${v.id}: ${v.help} (${v.nodes.join(', ')})`);
      const minor = entry.axe.filter((v) => !['serious', 'critical'].includes(v.impact));
      if (minor.length) console.log(`  · axe minor/moderate: ${minor.map((v) => v.id).join(', ')}`);
      if (!serious.length) pass('axe: no serious or critical WCAG 2.1 A/AA violations');
    }

    // 4. Hint dialog by keyboard
    if (await visibleCount(page, '#btn-hint')) {
      await page.focus('#btn-hint');
      await page.keyboard.press('Enter');
      if (await openModalCheck(page, screen, 'hint')) {
        await page.keyboard.press('Escape');
        await page.waitForTimeout(500);
        const back = await page.evaluate(() => document.activeElement?.id);
        if (back === 'btn-hint') pass('hint: Escape closes and returns focus to the hint button');
        else fail(screen, `hint: after Escape focus is on "${back || 'body'}", not #btn-hint`);
      }
    }

    // 5. Keyboard operation, then the result dialog (batch engines)
    await operate(page, screen, engine);
    if (engine !== 'cari' && await visibleCount(page, '#btn-check')) {
      await page.focus('#btn-check');
      await page.keyboard.press('Enter');
      await openModalCheck(page, screen, 'result');
    }
  }
}

await browser.close();
fs.writeFileSync(path.join(OUT, 'report.json'), JSON.stringify({ base: BASE, axe: Boolean(AXE), report, failures }, null, 2));
console.log(`\nReport: ${path.relative(root, path.join(OUT, 'report.json'))}${AXE ? '' : ' (axe-core not found; WCAG scan skipped)'}`);
if (failures.length) {
  console.log(`\n${failures.length} checks failed:`);
  failures.forEach((f) => console.log(`  - ${f}`));
  process.exit(1);
}
console.log('\nAll checks passed.');
