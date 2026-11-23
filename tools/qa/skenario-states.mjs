#!/usr/bin/env node
/**
 * Screenshots of the four Skenario Digital states (41 §3, step D): action,
 * reason, feedback, explained, with one chosen scenario on screen (default
 * the longest one, wnb-5-a04: four actions, five reasons), at each size.
 *
 * Development install only (it signs up students and spends their tries).
 * Needs the study's "Cara wilayah terbuka" = bebas and the MariaDB CLI
 * (`QA_MYSQL`, default `mysql -u root gelita`) to read which action of the
 * chosen scenario is the safe one: the page never knows (32 §5.2).
 *
 *   node tools/qa/skenario-states.mjs [--base=http://localhost:8080/]
 *        [--item=wnb-5-a04] [--sizes=1366x768,1366x657] [--lang=id|en]
 *
 * For each size a new student is signed up until the attempt holds the item
 * (variants are random outside research mode); the scenarios before it are
 * closed with the first action and reason. Each state also reports any part
 * of the two sides that does not fit (scrollHeight > clientHeight).
 * Screenshots: writable/qa/skenario/{n}-{state}-{size}.png. Exit 1 on a
 * page error or a side that does not fit.
 */
import { createRequire } from 'node:module';
import { execSync } from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const require = createRequire(import.meta.url);
const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');

function loadPlaywright() {
  try { return require('playwright'); } catch { /* the global install */ }
  return require(path.join(execSync('npm root -g').toString().trim(), 'playwright'));
}
const { chromium } = loadPlaywright();

const args = Object.fromEntries(process.argv.slice(2).map((arg) => {
  const [key, ...rest] = arg.replace(/^--/, '').split('=');
  return [key, rest.length ? rest.join('=') : true];
}));
const BASE = String(args.base || process.env.QA_BASE_URL || 'http://localhost:8080/').replace(/\/?$/, '/');
const ITEM = String(args.item || 'wnb-5-a04');
const LANG = String(args.lang || 'id');
const SIZES = String(args.sizes || '1366x768,1366x657').split(',').map((s) => {
  const [w, h] = s.split('x').map(Number);
  return { name: s, w, h };
});
const MYSQL = process.env.QA_MYSQL || 'mysql -u root gelita';
const PASS = 'Lentera!Kedu2026';
const OUT = path.join(root, 'writable/qa/skenario');
fs.mkdirSync(OUT, { recursive: true });

const REGION = { tmg: 'temanggung', mgl: 'magelang', wnb: 'wonosobo' }[ITEM.slice(0, 3)];
const sql = (query) => execSync(`${MYSQL} -N -B -e "${query}"`).toString().trim();
const itemId = Number(sql(`select id from challenge_items where item_key = '${ITEM}'`));
const actions = sql(`select score, text_${LANG} from scenario_options where challenge_item_id = ${itemId} and kind = 'action' order by sequence`)
  .split('\n').map((line) => { const [score, ...text] = line.split('\t'); return { score: Number(score), text: text.join('\t') }; });
const risky = actions.find((a) => a.score === 0)?.text;
if (!itemId || !risky) throw new Error(`${ITEM}: no item or no risky action in the database`);

const failures = [];
const browser = await chromium.launch();

async function signUp(page) {
  const user = `sd${Date.now().toString(36)}`;
  await page.goto(`${BASE}daftar/1`);
  await page.fill('#display_name', 'Siswa QA Skenario');
  await page.fill('#age', '10');
  await page.check('input[name=gender]');
  await page.click('main form button[type=submit]');
  await page.waitForURL(/daftar\/2/);
  await page.selectOption('#province_code', { index: 1 });
  const options = await page.$$eval('#district_code option', (o) => o.filter((x) => x.value).map((x) => x.value));
  await page.selectOption('#district_code', options[0]);
  if (await page.isVisible('#school_name')) await page.fill('#school_name', 'SD Uji Skenario');
  await page.click('main form button[type=submit]');
  await page.waitForURL(/daftar\/3/);
  await page.fill('#username', user);
  await page.fill('#password', PASS);
  await page.fill('#password_confirm', PASS);
  await page.check('input[name=assent]');
  await page.click('main form button[type=submit]');
  await page.waitForURL(/intro|gerbang|peta/);
  await page.goto(`${BASE}intro/selesai`).catch(() => {});
  if (LANG === 'en') {
    // Only POST /bahasa exists (the HUD's language popover); a GET left the page in Indonesian.
    await page.goto(`${BASE}peta`);
    await page.locator('details.hud-lang summary').click();
    await Promise.all([page.waitForNavigation(), page.locator('form.lang-switch button[value="en"]').click()]);
    if ((await page.evaluate(() => document.documentElement.lang)) !== 'en') throw new Error('could not switch the game to English');
  }
  return user;
}

const payload = (page) => page.evaluate(() => JSON.parse(document.getElementById('challenge-data').textContent));
const onScreen = (page) => page.evaluate(() => document.getElementById('scenario-prompt')?.textContent ?? '');

async function pick(page, text) {
  const option = page.locator('.scenario-option', { hasText: text }).first();
  await option.click();
}

async function fits(page, label) {
  const over = await page.evaluate(() => [...document.querySelectorAll('.scenario-situation, .scenario-steps')]
    .filter((side) => side.scrollHeight > side.clientHeight + 1).map((side) => side.className));
  if (over.length) failures.push(`${label}: does not fit: ${over.join(', ')}`);
}

async function shot(page, n, state, size) {
  await page.addStyleTag({ content: '#debug-icon, #toolbarContainer { display: none !important; }' }).catch(() => {});
  await page.mouse.move(0, 0);
  await page.waitForTimeout(400);
  await fits(page, `${state} ${size.name}`);
  await page.screenshot({ path: path.join(OUT, `${n}-${state}-${size.name}.png`) });
  console.log(`- ${n}-${state}-${size.name}`);
}

for (const size of SIZES) {
  console.log(`\n== ${size.name}`);
  let page;
  let ctx;
  for (let attempt = 0; attempt < 12; attempt++) {
    ctx = await browser.newContext({ viewport: { width: size.w, height: size.h } });
    page = await ctx.newPage();
    // View transitions aborted by a quick navigation are the browser's, not the page's
    page.on('pageerror', (e) => { if (!/Transition was aborted/.test(e.message)) failures.push(`js ${size.name}: ${e.message}`); });
    await signUp(page);
    await page.goto(`${BASE}penghubung/${REGION}`).catch(() => {});
    await page.goto(`${BASE}tantangan/${REGION}/5`);
    if (!/tantangan/.test(page.url())) await page.goto(`${BASE}tantangan/${REGION}/5`);
    const data = await payload(page).catch(() => null);
    if (data?.scenarios?.some((s) => Number(s.id) === itemId)) break;
    await ctx.close();
    page = null;
  }
  if (!page) { failures.push(`${size.name}: no attempt with ${ITEM}`); continue; }
  const data = await payload(page);
  const target = data.scenarios.find((s) => Number(s.id) === itemId);

  // Close the scenarios before the chosen one with the first action and reason
  for (let guard = 0; guard < 20 && (await onScreen(page)) !== target.prompt; guard++) {
    await page.locator('.scenario-option').first().click();
    await page.click('#btn-scenario-go');
    await page.locator('.scenario-option').first().click();
    await page.click('#btn-scenario-go');
    await page.waitForSelector('.scenario-feedback, .scenario-safe');
    await page.click('#btn-scenario-go');
    await page.waitForTimeout(300);
  }

  const second = target.actions[1].text;
  await pick(page, second);
  await shot(page, 1, 'tindakan', size);
  // The story pill while it plays: wider, with the wave and the duration (41 decision 37)
  const listen = page.locator('.sd-player.is-story:not([hidden]) [data-action="play"]');
  if (await listen.count()) {
    await listen.click();
    await page.waitForTimeout(1500);
    await shot(page, 0, 'dibacakan', size);
    await page.locator('.sd-player.is-story:not([hidden]) [data-action="pause"]').click();
  }
  await page.click('#btn-scenario-go');
  await page.locator('.scenario-option').nth(1).click();
  await shot(page, 2, 'alasan', size);
  // Three risky tries: feedback after the first, the safe action after the third
  for (let tries = 1; tries <= 3; tries++) {
    if (tries > 1) {
      await page.click('#btn-scenario-go'); // Coba lagi
      await pick(page, risky);
      await page.click('#btn-scenario-go');
      await page.locator('.scenario-option').last().click();
    } else {
      await page.click('#btn-scenario-back');
      await pick(page, risky);
      await page.click('#btn-scenario-go');
      await page.locator('.scenario-option').last().click();
    }
    await page.click('#btn-scenario-go');
    await page.waitForSelector(tries < 3 ? '.scenario-feedback' : '.scenario-safe');
    if (tries === 1) await shot(page, 3, 'umpan-balik', size);
  }
  await shot(page, 4, 'tindakan-aman', size);
  await ctx.close();
}

await browser.close();
console.log(`\nScreenshots: ${path.relative(root, OUT)}/`);
if (failures.length) {
  failures.forEach((f) => console.log(`  - ${f}`));
  process.exit(1);
}
console.log('All states fit.');
