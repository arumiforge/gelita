#!/usr/bin/env node
/**
 * Asset package Stage 4 check (docs/rencana-paket-aset.md §4.2, §4.4, §4.5, P8).
 *
 * Runs Chromium (Playwright) against a running GELITA and checks, on the
 * public game screens (no login needed):
 *
 *   - a same-origin link navigation starts a cross-document view transition
 *     (`pagereveal` event carries `viewTransition`);
 *   - with `prefers-reduced-motion: reduce` there is no transition;
 *   - JS texts come in both languages: setLocale('en') switches t() with no
 *     request, and the language button still reloads the page in English,
 *     under a view transition;
 *   - assetsLocal() is false by default, setAssetsLocal(true) switches it,
 *     and the curtain minimum follows it (600 ms between screens, 2.5 s for
 *     the opening map curtain);
 *   - [data-next-assets] images are decoded (img.decode()) and capped.
 *
 * Usage:
 *   php spark serve                    (or another server; QA_BASE_URL)
 *   node tools/qa/transition-check.mjs [--base=http://localhost:8080/]
 *
 * Chromium is preinstalled (PLAYWRIGHT_BROWSERS_PATH); never `playwright install`.
 * Exit code 0 when every check passes, 1 otherwise.
 */
import { createRequire } from 'node:module';
import { execSync } from 'node:child_process';

const require = createRequire(import.meta.url);

function loadPlaywright() {
  try { return require('playwright'); } catch { /* fall back to the global install */ }
  const globalRoot = execSync('npm root -g').toString().trim();
  return require(`${globalRoot}/playwright`);
}
const { chromium } = loadPlaywright();

const args = Object.fromEntries(process.argv.slice(2).map((arg) => {
  const [key, ...rest] = arg.replace(/^--/, '').split('=');
  return [key, rest.length ? rest.join('=') : true];
}));
const BASE = String(args.base || process.env.QA_BASE_URL || 'http://localhost:8080/').replace(/\/?$/, '/');

const failures = [];
const check = (ok, label, detail = '') => {
  console.log(`${ok ? 'ok  ' : 'FAIL'} ${label}${detail ? ` — ${detail}` : ''}`);
  if (!ok) failures.push(label);
};

/** Records whether each new document was revealed with a view transition. */
const REVEAL_PROBE = () => {
  window.addEventListener('pagereveal', (event) => {
    sessionStorage.setItem('qa.vt', event.viewTransition ? '1' : '0');
  });
};

async function transitionOnClick(context) {
  const page = await context.newPage();
  await page.addInitScript(REVEAL_PROBE);
  await page.goto(BASE, { waitUntil: 'networkidle' });
  const link = page.locator(`a[href="${BASE}mulai"]`).first();
  await Promise.all([page.waitForURL(`${BASE}mulai`), link.click()]);
  await page.waitForLoadState('networkidle');
  const vt = await page.evaluate(() => sessionStorage.getItem('qa.vt'));
  await page.close();
  return vt;
}

const browser = await chromium.launch();
try {
  // ------------------------------------------------------------ transitions
  const motion = await browser.newContext({ viewport: { width: 1280, height: 720 } });
  check(await transitionOnClick(motion) === '1', 'link navigation runs a cross-document view transition');
  await motion.close();

  const reduced = await browser.newContext({ viewport: { width: 1280, height: 720 }, reducedMotion: 'reduce' });
  check(await transitionOnClick(reduced) === '0', 'reduced motion: no view transition');
  await reduced.close();

  // ------------------------------------------------------------ JS modules
  const context = await browser.newContext({ viewport: { width: 1280, height: 720 } });
  const page = await context.newPage();
  await page.addInitScript(REVEAL_PROBE);
  await page.goto(BASE, { waitUntil: 'networkidle' });

  const texts = await page.evaluate(async () => {
    const config = await import('./assets/js/core/config.js');
    const before = config.t('cancel');
    const locales = config.textLocales();
    let requests = 0;
    const observer = new PerformanceObserver((list) => { requests += list.getEntries().length; });
    observer.observe({ type: 'resource' });
    const switched = config.setLocale('en');
    const after = config.t('cancel');
    const lang = document.documentElement.lang;
    await new Promise((resolve) => { setTimeout(resolve, 200); });
    observer.disconnect();
    config.setLocale('id');
    return { before, after, locales, switched, lang, requests, unknown: config.setLocale('xx') };
  });
  check(texts.locales.join(',') === 'id,en', 'config carries texts in both languages', texts.locales.join(','));
  check(texts.switched && texts.before === 'Batal' && texts.after === 'Cancel', 'setLocale() switches t() at once', `${texts.before} → ${texts.after}`);
  check(texts.lang === 'en' && texts.requests === 0, 'switching JS texts needs no request', `lang=${texts.lang}, requests=${texts.requests}`);
  check(texts.unknown === false, 'setLocale() refuses a locale the page has no texts for');

  const curtain = await page.evaluate(async () => {
    const config = await import('./assets/js/core/config.js');
    const { curtainMinMs } = await import('./assets/js/core/curtain.js');
    const layer = (attrs) => {
      const node = document.createElement('div');
      Object.assign(node.dataset, attrs);
      return node;
    };
    const region = layer({ minMs: '1800', localMinMs: '600' });
    const map = layer({});
    const off = config.assetsLocal();
    const remote = [curtainMinMs(region), curtainMinMs(map)];
    config.setAssetsLocal(true);
    const on = config.assetsLocal();
    const local = [curtainMinMs(region), curtainMinMs(map)];
    config.setAssetsLocal(false);
    return { off, on, remote, local };
  });
  check(curtain.off === false, 'assetsLocal() is false by default');
  check(curtain.on === true, 'setAssetsLocal(true) switches the hook on');
  check(curtain.remote.join() === '1800,2500', 'curtain minimum without local assets', curtain.remote.join());
  check(curtain.local.join() === '600,2500', 'short between-screen curtain with local assets; map curtain keeps 2.5 s', curtain.local.join());

  const decoded = await page.evaluate(async (base) => {
    const { decodeNextAssets, nextAssetUrls } = await import('./assets/js/game/next-assets.js');
    const holder = document.createElement('div');
    const urls = ['icon-192.png', 'icon-512.png', 'icon-maskable-512.png', 'apple-touch-icon.png', 'icon.svg', 'missing.png', 'x1.png', 'x2.png']
      .map((name) => `${base}assets/app/${name}`);
    holder.dataset.nextAssets = JSON.stringify([...urls, urls[0], 'not-an-image.mp3']);
    document.body.append(holder);
    const capped = nextAssetUrls().length;
    const count = await decodeNextAssets();
    holder.remove();
    return { capped, count, flag: document.documentElement.dataset.nextDecoded };
  }, BASE);
  check(decoded.capped === 6, 'next assets are deduplicated, images only, capped at 6', String(decoded.capped));
  check(decoded.count === 5 && decoded.flag === '5', 'next-screen images are decoded; a missing one is skipped', `${decoded.count} decoded`);

  // ------------------------------------------------------------ language button
  await page.locator('form.lang-switch button[value="en"]').click();
  await page.waitForLoadState('networkidle');
  const lang = await page.evaluate(() => [document.documentElement.lang, document.body.dataset.ready, sessionStorage.getItem('qa.vt')]);
  check(lang[0] === 'en' && lang[1] === 'en', 'language button reloads the screen in English', lang.slice(0, 2).join(','));
  check(lang[2] === '1', 'the language reload runs a view transition');
  await page.locator('form.lang-switch button[value="id"]').click();
  await page.waitForLoadState('networkidle');

  // Active language: no request at all
  let posted = 0;
  page.on('request', (request) => { if (request.method() === 'POST') posted += 1; });
  await page.locator('form.lang-switch button[value="id"]').click();
  await page.waitForTimeout(400);
  check(posted === 0, 'tapping the active language sends nothing');
  await context.close();
} finally {
  await browser.close();
}

if (failures.length) {
  console.log(`\n${failures.length} check(s) failed.`);
  process.exit(1);
}
console.log('\nAll Stage 4 checks passed.');
