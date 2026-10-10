#!/usr/bin/env node
/**
 * Screen-switch and language-switch timing on the reference network
 * (docs/rencana-paket-aset.md §1.2, §10 Stage 0, §11.3).
 *
 * Chromium (Playwright) throttled through CDP to 1 Mbps down/up and 150 ms
 * RTT visits every game screen of Dok 3 §4 and measures, per screen:
 *
 *   - cold: HTTP cache cleared right before the navigation (first screen on
 *     a fresh device); warm: the same screen again right after (every asset
 *     is in the HTTP cache, files without ?v= are revalidated, 304);
 *   - navigation start → first contentful paint (FCP, S1: no blank screen);
 *   - navigation start → stage ready: the page module is initialised
 *     (body[data-page-ready]) and no curtain covers the stage any more, or
 *     the stage waits for the "tap to start" card (S2 without the decode);
 *   - navigation start → every <img> on the stage loaded and decoded (S2),
 *     and the number of those images still loading when the stage was
 *     ready (S3: images that appear late);
 *   - language switch (S4): tap ID/EN → the reloaded screen ready and its
 *     images decoded (form POST /bahasa, redirect, full reload);
 *   - requests and bytes per navigation, split HTML / API / assets, with
 *     how many came from the cache or were revalidated (304).
 *
 * It also clicks through the main path peta → wilayah → misi → tantangan
 * (warm) and times tap → stage ready, including the curtains that links
 * play before they navigate.
 *
 * Output: writable/qa/nav/nav-{timestamp}.json (every sample) and
 * nav-{timestamp}-summary.json (p50/p95 per screen), plus a table on stdout.
 * Not committed (writable/ is ignored).
 *
 * Setup (once, local development database only — never production):
 *   composer install; cp env .env; php spark key:generate
 *   database + php spark migrate + db:seed DatabaseSeeder (README §Instalasi)
 *   php spark gelita:narration:import, then approve the narration
 *   apt install nginx php8.3-fpm   (only for --serve)
 *
 * Usage:
 *   node tools/qa/nav-timing.mjs --serve [--runs=5] [--only=peta,misi] [--region=temanggung]
 *   node tools/qa/nav-timing.mjs --base=http://localhost:8080/ …
 *
 * --serve starts a private nginx + php-fpm built from
 * deploy/linux/nginx/gelita-https.conf (HTTPS + HTTP/2, the production
 * cache headers, CI_ENVIRONMENT=production, self-signed certificate) on
 * https://localhost:8443/ and stops it at the end. This is the reference
 * setup: `php spark serve` is single-threaded HTTP/1.1 without cache
 * validators, so its numbers are not comparable.
 *
 * Without --user the script registers a test student through /daftar and
 * plays every challenge with throwaway answers until its tries run out (so
 * the finished, result, region-complete, library, ending and reflection
 * screens exist), and registers a second student for the locked library.
 * Run it only against a development database. Chromium is preinstalled
 * (PLAYWRIGHT_BROWSERS_PATH); never run `playwright install`.
 *
 * Other options: --runs=N (samples per screen and cache state, default 5;
 * the plan's targets use p95 of ≥ 20), --no-lang, --no-path, --user=… --pass=…
 * (an existing student who has finished every challenge), --down-kbps,
 * --up-kbps, --rtt.
 */
import { createRequire } from 'node:module';
import { execSync, spawn } from 'node:child_process';
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
const SERVE_PORT = Number(args.port || 8443);
const BASE = String(args.base || (args.serve ? `https://localhost:${SERVE_PORT}/` : process.env.QA_BASE_URL || 'http://localhost:8080/')).replace(/\/?$/, '/');
const RUNS = Math.max(1, Number(args.runs || 5));
const ONLY = args.only ? String(args.only).split(',') : null;
const REGION = String(args.region || 'temanggung');
const REGIONS = ['temanggung', 'magelang', 'wonosobo'];
const PASS = String(args.pass || 'Lentera!Kedu2026');
const NETWORK = {
  offline: false,
  latency: Number(args.rtt ?? 150),
  downloadThroughput: Number(args['down-kbps'] ?? 1000) * 1000 / 8,
  uploadThroughput: Number(args['up-kbps'] ?? 1000) * 1000 / 8,
};
const OUT = path.join(root, 'writable/qa/nav');
const STAMP = new Date().toISOString().replace(/[:.]/g, '-').slice(0, 19);
const TIMEOUT = 60_000;

fs.mkdirSync(OUT, { recursive: true });

// ------------------------------------------------------------------ private nginx + php-fpm (--serve)
const children = [];

function which(cmd) {
  try { return execSync(`command -v ${cmd}`, { stdio: ['ignore', 'pipe', 'ignore'] }).toString().trim(); } catch { return null; }
}

async function serve() {
  const dir = path.join(OUT, 'server');
  fs.mkdirSync(dir, { recursive: true });
  const nginx = which('nginx') || (fs.existsSync('/usr/sbin/nginx') ? '/usr/sbin/nginx' : null);
  const fpm = ['php-fpm8.3', 'php-fpm', '/usr/sbin/php-fpm8.3'].map((c) => (c.startsWith('/') ? (fs.existsSync(c) ? c : null) : which(c))).find(Boolean);
  if (!nginx || !fpm) throw new Error('--serve needs nginx and php-fpm (apt install nginx php8.3-fpm)');

  const asRoot = process.getuid?.() === 0;
  const sock = path.join(dir, 'php-fpm.sock');
  fs.rmSync(sock, { force: true });
  if (!fs.existsSync(path.join(dir, 'cert.pem'))) {
    execSync(`openssl req -x509 -newkey rsa:2048 -nodes -days 30 -subj /CN=localhost -keyout "${dir}/key.pem" -out "${dir}/cert.pem"`, { stdio: 'ignore' });
  }

  fs.writeFileSync(path.join(dir, 'php-fpm.conf'), [
    '[global]',
    `pid = ${dir}/php-fpm.pid`,
    `error_log = ${dir}/php-fpm.log`,
    'daemonize = no',
    '[gelita]',
    asRoot ? 'user = root' : '',
    `listen = ${sock}`,
    'listen.mode = 0666',
    'pm = static',
    'pm.max_children = 8',
    'clear_env = no',
    `php_admin_value[auto_prepend_file] = ${dir}/env.php`,
    'catch_workers_output = yes',
  ].filter(Boolean).join('\n'));
  // .env never overrides variables that already exist (DotEnv::setVariable), so
  // set them before it loads: this base URL, and production (no debug toolbar)
  const env = { 'app.baseURL': BASE, CI_ENVIRONMENT: 'production' };
  fs.writeFileSync(path.join(dir, 'env.php'), `<?php\nforeach (json_decode('${JSON.stringify(env)}', true) as $k => $v) { putenv("$k=$v"); $_ENV[$k] = $_SERVER[$k] = $v; }\n`);

  // The production site config, pointed at this checkout, the private socket and certificate
  const site = fs.readFileSync(path.join(root, 'deploy/linux/nginx/gelita-https.conf'), 'utf8')
    .replaceAll('/var/www/gelita', root)
    .replace(/listen 443 ssl http2;/, `listen ${SERVE_PORT} ssl http2;`)
    .replace(/server_name [^;]+;/, 'server_name localhost;')
    .replace(/ssl_certificate +[^;]+;/, `ssl_certificate ${dir}/cert.pem;`)
    .replace(/ssl_certificate_key +[^;]+;/, `ssl_certificate_key ${dir}/key.pem;`)
    .replace(/include snippets\/fastcgi-php\.conf;/, 'include /etc/nginx/fastcgi_params; fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;')
    .replace(/fastcgi_pass [^;]+;/, `fastcgi_pass unix:${sock};`);
  fs.writeFileSync(path.join(dir, 'nginx.conf'), [
    asRoot ? 'user root;' : '',
    'worker_processes 2;',
    `pid ${dir}/nginx.pid;`,
    `error_log ${dir}/nginx-error.log;`,
    'events { worker_connections 256; }',
    'http {',
    '  include /etc/nginx/mime.types;',
    '  default_type application/octet-stream;',
    '  sendfile on;',
    `  access_log ${dir}/access.log;`,
    ...['client_body', 'proxy', 'fastcgi', 'uwsgi', 'scgi'].map((t) => `  ${t}_temp_path ${dir}/${t};`),
    site,
    '}',
  ].filter(Boolean).join('\n'));

  const start = (cmd, argv) => {
    const child = spawn(cmd, argv, { stdio: ['ignore', 'ignore', 'pipe'] });
    child.stderr.on('data', (d) => process.stderr.write(`[${path.basename(cmd)}] ${d}`));
    children.push(child);
  };
  start(fpm, ['-y', path.join(dir, 'php-fpm.conf'), '-F', ...(asRoot ? ['-R'] : [])]);
  start(nginx, ['-p', dir, '-c', path.join(dir, 'nginx.conf'), '-g', 'daemon off;']);

  for (let i = 0; i < 50; i++) {
    await new Promise((r) => { setTimeout(r, 200); });
    try {
      execSync(`curl -sk -o /dev/null -w "%{http_code}" ${BASE}`, { stdio: 'pipe' });
      if (fs.existsSync(sock)) return;
    } catch { /* not up yet */ }
  }
  throw new Error(`server did not start, see ${path.relative(root, dir)}/*.log`);
}

function stopServers() {
  for (const child of children) child.kill('SIGTERM');
}
process.on('exit', stopServers);
process.on('SIGINT', () => { stopServers(); process.exit(130); });

// ------------------------------------------------------------------ in-page probe
/**
 * Runs at document start of every page. Reports absolute times (ms since
 * the epoch, performance.timeOrigin + now) to Node through __navReport.
 */
function probe() {
  if (window.top !== window) return;
  const now = () => performance.timeOrigin + performance.now();
  const out = { navStart: performance.timeOrigin, url: location.pathname, fcp: null, paint: null, jsReady: null, ready: null, gate: null, decoded: null, images: 0, emptySlots: 0, pendingAtReady: 0, broken: 0 };
  try {
    new PerformanceObserver((list) => {
      for (const e of list.getEntries()) if (e.name === 'first-contentful-paint') out.fcp = performance.timeOrigin + e.startTime;
    }).observe({ type: 'paint', buffered: true });
  } catch { /* old engine */ }

  const shown = (el) => {
    if (!el || !el.isConnected || el.hidden || el.closest('[hidden]')) return false;
    const st = getComputedStyle(el);
    return st.display !== 'none' && st.visibility !== 'hidden' && Number(st.opacity) > 0.05 && el.getClientRects().length > 0;
  };
  const blockingCurtain = () => [...document.querySelectorAll('[data-curtain-layer]')]
    .find((l) => shown(l) && !l.classList.contains('is-closing'));
  // Images may sit at opacity 0 until they load (scene fade-in), so only display/visibility count
  const rendered = (img) => {
    if (!img.isConnected || img.closest('[hidden], [data-curtain-layer], template, dialog:not([open])')) return false;
    const st = getComputedStyle(img);
    return st.display !== 'none' && st.visibility !== 'hidden' && img.getClientRects().length > 0;
  };
  const emptySlots = () => [...(document.getElementById('stage')?.querySelectorAll('img') ?? [])]
    .filter((img) => rendered(img) && !img.getAttribute('src')).length;
  const stageImages = () => {
    const stage = document.getElementById('stage');
    if (!stage) return [];
    const vw = innerWidth; const vh = innerHeight;
    return [...stage.querySelectorAll('img')].filter((img) => {
      if (!img.getAttribute('src') || !rendered(img)) return false;
      if (img.loading === 'lazy') {
        const r = img.getBoundingClientRect();
        if (r.bottom < 0 || r.top > vh || r.right < 0 || r.left > vw) return false;
      }
      return true;
    });
  };
  // Pictures that are not <img>: SVG <image> (puzzle pieces) and CSS background images on the stage
  const otherPictures = () => {
    const stage = document.getElementById('stage');
    if (!stage) return [];
    const urls = new Set();
    for (const el of stage.querySelectorAll('image')) {
      const href = el.getAttribute('href') || el.getAttribute('xlink:href');
      if (href && rendered(el)) urls.add(new URL(href, location.href).href);
    }
    for (const el of stage.querySelectorAll('*')) {
      if (el.closest('[hidden], [data-curtain-layer], template')) continue;
      const bg = getComputedStyle(el).backgroundImage;
      if (!bg || bg === 'none') continue;
      for (const m of bg.matchAll(/url\(["']?([^"')]+)["']?\)/g)) if (!m[1].startsWith('data:')) urls.add(new URL(m[1], location.href).href);
    }
    return [...urls];
  };
  const fetched = (url) => performance.getEntriesByName(url).some((e) => e.responseEnd > 0);

  let decoding = false;
  const tick = () => {
    const t = now();
    if (document.readyState !== 'loading' && document.getElementById('stage')) {
      if (out.paint === null) out.paint = t;
      if (out.jsReady === null && document.body?.dataset.pageReady) out.jsReady = t;
      if (out.ready === null && out.jsReady !== null) {
        const curtain = blockingCurtain();
        const tapCard = document.querySelector('[data-narrator-tap]');
        if (curtain && shown(curtain.querySelector('[data-curtain-tap]'))) {
          out.ready = t; out.gate = 'curtain-tap';
        } else if (!curtain) {
          out.ready = t;
          if (shown(tapCard)) out.gate = 'narrator-tap';
        }
        if (out.ready !== null) out.pendingAtReady = stageImages().filter((img) => !img.complete).length + otherPictures().filter((u) => !fetched(u)).length;
      }
      if (out.ready !== null && !decoding) {
        const imgs = stageImages();
        const others = otherPictures();
        if (imgs.every((img) => img.complete) && others.every(fetched)) {
          decoding = true;
          const decodeUrl = (url) => { const i = new Image(); i.src = url; return i.decode().catch(() => null); };
          Promise.all([...imgs.map((img) => img.decode().catch(() => null)), ...others.map(decodeUrl)]).then(async () => {
            out.decoded = now();
            // FCP can follow the stage (content fading in from opacity 0): give it a moment
            for (let i = 0; i < 20 && !performance.getEntriesByName('first-contentful-paint').length; i++) await new Promise((r) => { setTimeout(r, 100); });
            const fcp = performance.getEntriesByName('first-contentful-paint')[0];
            if (fcp) out.fcp = performance.timeOrigin + fcp.startTime;
            out.images = imgs.length + others.length;
            out.broken = imgs.filter((img) => img.naturalWidth === 0).length;
            out.emptySlots = emptySlots();
            out.screen = document.querySelector('[data-screen]')?.dataset.screen || document.querySelector('main')?.className || '';
            out.locale = document.documentElement.lang;
            window.__navReport?.({ type: 'done', ...out });
          });
          return;
        }
      }
    }
    requestAnimationFrame(tick);
  };
  requestAnimationFrame(tick);
}

// ------------------------------------------------------------------ network log per page (CDP)
async function instrument(ctx, page) {
  const cdp = await ctx.newCDPSession(page);
  const net = new Map();
  let log = [];
  await cdp.send('Network.enable');
  cdp.on('Network.requestWillBeSent', (e) => {
    // A redirect reuses the requestId: close the previous hop first
    if (e.redirectResponse && net.has(e.requestId)) {
      const prev = net.get(e.requestId);
      prev.status = e.redirectResponse.status;
      prev.bytes = e.redirectResponse.encodedDataLength || 0;
      prev.done = true;
      log.push(prev);
    }
    const r = { id: e.requestId, url: e.request.url, method: e.request.method, type: e.type, t: e.wallTime * 1000, status: null, bytes: 0, cache: null, done: false };
    net.set(e.requestId, r);
    log.push(r);
  });
  cdp.on('Network.requestServedFromCache', (e) => { const r = net.get(e.requestId); if (r) r.cache = 'memory'; });
  cdp.on('Network.responseReceived', (e) => {
    const r = net.get(e.requestId);
    if (!r) return;
    r.status = e.response.status;
    r.type = e.type || r.type;
    if (e.response.fromDiskCache) r.cache = r.cache || 'disk';
    if (e.response.fromServiceWorker) r.cache = 'sw';
    if (e.response.fromPrefetchCache) r.cache = 'prefetch';
  });
  cdp.on('Network.loadingFinished', (e) => { const r = net.get(e.requestId); if (r) { r.bytes = e.encodedDataLength; r.done = true; } });
  cdp.on('Network.loadingFailed', (e) => { const r = net.get(e.requestId); if (r) { r.failed = e.errorText; r.done = true; } });
  // Playwright reuses one list object; dedupe redirect hops by object identity
  return {
    cdp,
    reset() { log = []; },
    take() { const list = [...new Set(log)]; log = []; return list; },
    throttle: (on) => cdp.send('Network.emulateNetworkConditions', on ? NETWORK : { offline: false, latency: 0, downloadThroughput: -1, uploadThroughput: -1 }),
    clearCache: () => cdp.send('Network.clearBrowserCache'),
  };
}

function classify(requests) {
  const host = new URL(BASE).host;
  const out = { html: { n: 0, bytes: 0 }, api: { n: 0, bytes: 0 }, assets: { n: 0, bytes: 0 }, external: { n: 0, bytes: 0 }, cached: 0, revalidated: 0, byType: {} };
  for (const r of requests) {
    const u = new URL(r.url);
    if (u.protocol === 'data:' || u.protocol === 'blob:') continue;
    // External: Pustaka images from Wikimedia Commons, video embeds (§2.2)
    const kind = u.host !== host ? 'external' : r.type === 'Document' ? 'html' : u.pathname.includes('/api/') ? 'api' : 'assets';
    const bytes = r.cache ? 0 : r.bytes;
    out[kind].n++;
    out[kind].bytes += bytes;
    if (r.cache) out.cached++;
    if (r.status === 304) out.revalidated++;
    if (kind === 'assets' || kind === 'external') {
      const t = (r.type || 'Other').toLowerCase();
      out.byType[t] = out.byType[t] || { n: 0, bytes: 0 };
      out.byType[t].n++;
      out.byType[t].bytes += bytes;
    }
  }
  const kinds = [out.html, out.api, out.assets, out.external];
  out.total = { n: kinds.reduce((a, k) => a + k.n, 0), bytes: kinds.reduce((a, k) => a + k.bytes, 0) };
  return out;
}

// ------------------------------------------------------------------ browser
let waiter = null;
const responses = new Map(); // tap time → first frame with a link curtain

async function newClient(name) {
  const ctx = await browser.newContext({ viewport: { width: 1280, height: 720 }, ignoreHTTPSErrors: true, serviceWorkers: 'block', locale: 'id-ID' });
  await ctx.exposeFunction('__navReport', (r) => {
    if (r.type === 'response') responses.set(r.tap, r.t);
    else if (waiter && r.type === 'done' && waiter(r)) waiter = null;
  });
  await ctx.addInitScript(probe);
  const page = await ctx.newPage();
  page.on('pageerror', (e) => console.log(`  [${name}] page error: ${e.message}`));
  const net = await instrument(ctx, page);
  return { name, ctx, page, net };
}

/** The next stage report from a document that started at or after `after` (epoch ms). */
function nextDone(after) {
  return new Promise((resolve, reject) => {
    const timer = setTimeout(() => { waiter = null; reject(new Error('timeout waiting for the stage')); }, TIMEOUT);
    waiter = (r) => {
      if (r.navStart < after) return false;
      clearTimeout(timer);
      resolve(r);
      return true;
    };
  });
}

const settle = (ms) => new Promise((r) => { setTimeout(r, ms); });

/**
 * One measured navigation. `trigger` starts it and returns the tap time
 * (epoch ms) or null for a plain navigation (time origin = navigation start).
 */
async function measure(client, trigger) {
  await settle(300);
  client.net.reset();
  // A document that is still settling from the previous step must not answer
  const done = nextDone(Date.now() - 100);
  let tap;
  let r;
  try {
    tap = await trigger();
    r = await done;
  } catch (e) {
    waiter = null;
    done.catch(() => {});
    client.net.take();
    return { error: e.message, url: client.page.url() };
  }
  await settle(400); // late requests started by the page module
  const from = tap ?? r.navStart;
  const rel = (t) => (t == null ? null : Math.round(t - from));
  const curtainAt = tap ? responses.get(tap) : undefined;
  responses.clear();
  return {
    url: r.url,
    screen: r.screen,
    locale: r.locale,
    gate: r.gate,
    fcp: rel(r.fcp),
    // S1: tap → first visual change (a link curtain on the old page, else the new page's first paint)
    response: tap ? (([a, b]) => (a ?? b) == null ? null : Math.round(Math.min(a ?? b, b ?? a) - tap))([curtainAt, r.fcp]) : null,
    paint: rel(r.paint),
    jsReady: rel(r.jsReady),
    ready: rel(r.ready),
    decoded: rel(r.decoded),
    tapToNav: tap ? Math.round(r.navStart - tap) : null,
    images: r.images,
    emptySlots: r.emptySlots,
    pendingAtReady: r.pendingAtReady,
    broken: r.broken,
    net: classify(client.net.take()),
  };
}

const gotoTrigger = (client, url) => async () => {
  client.page.goto(url, { waitUntil: 'commit', timeout: TIMEOUT }).catch(() => {});
  return null;
};

/** Click inside the page and report the tap time from the page clock. */
const clickTrigger = (client, selector) => async () => client.page.evaluate((sel) => {
  const el = document.querySelector(sel);
  if (!el) throw new Error(`no ${sel}`);
  const t = performance.timeOrigin + performance.now();
  // S1 on the old page: the first frame that shows a curtain played by the link
  const watch = () => {
    const shown = [...document.querySelectorAll('[data-curtain-layer]')].some((l) => !l.hidden && getComputedStyle(l).display !== 'none' && Number(getComputedStyle(l).opacity) > 0.05);
    if (shown) window.__navReport({ type: 'response', t: performance.timeOrigin + performance.now(), tap: t });
    else if (performance.timeOrigin + performance.now() - t < 5000) requestAnimationFrame(watch);
  };
  requestAnimationFrame(watch);
  el.click();
  return t;
}, selector);

/** Close a "tap to start" gate so the HUD (language switch) is usable. */
async function openGate(page) {
  for (const sel of ['[data-curtain-tap]:not([hidden])', '[data-narrator-tap]:not([hidden]) .narrator-tap-btn']) {
    const el = await page.$(sel);
    if (el && await el.isVisible()) { await el.click().catch(() => {}); await settle(900); }
  }
}

// ------------------------------------------------------------------ test students
async function register(client, label) {
  const { page } = client;
  const user = `qanav${Date.now().toString(36)}`;
  await page.goto(`${BASE}daftar/1`);
  await page.fill('#display_name', label);
  await page.fill('#age', '10');
  await page.check('input[name=gender]');
  await page.click('main form button[type=submit]');
  await page.waitForURL(/daftar\/2/, { waitUntil: 'domcontentloaded' });
  await page.selectOption('#province_code', { index: 1 });
  const districts = await page.$$eval('#district_code option', (o) => o.filter((x) => x.value && !x.disabled).map((x) => x.value));
  await page.selectOption('#district_code', districts[0]);
  if (await page.isVisible('#school_name')) await page.fill('#school_name', 'SD Uji Waktu');
  await page.click('main form button[type=submit]');
  await page.waitForURL(/daftar\/3/, { waitUntil: 'domcontentloaded' });
  await page.fill('#username', user);
  await page.fill('#password', PASS);
  await page.fill('#password_confirm', PASS);
  await page.check('input[name=assent]');
  await page.click('main form button[type=submit]');
  await page.waitForURL(/intro|gerbang|peta/, { waitUntil: 'domcontentloaded' });
  // The new PHP session is occasionally lost right after registration (seen once:
  // a CSRF rejection of /api/events in the same second); log in again if so
  await page.goto(`${BASE}intro/selesai`).catch(() => {});
  if (/\/masuk/.test(page.url())) {
    console.log(`  ${user}: logged out after registration, logging in again`);
    await logIn(client, user);
    await page.goto(`${BASE}intro/selesai`).catch(() => {});
  }
  return user;
}

async function logIn(client, user) {
  const { page } = client;
  await page.goto(`${BASE}masuk`);
  await page.fill('#username', user);
  await page.fill('#password', PASS);
  await page.click('main form button[type=submit]');
  await page.waitForLoadState('domcontentloaded');
}

/** Use up the tries of the open challenge with throwaway answers (as stage-check.mjs). */
async function exhaust(page) {
  for (let round = 0; round < 40; round++) {
    if (/\/selesai\//.test(page.url())) return true;
    const engine = await page.getAttribute('[data-screen="challenge"]', 'data-engine').catch(() => null);
    if (!engine) return false;
    if (engine === 'rumpang') {
      for (const blank of await page.$$('.blank:not([disabled])')) {
        await blank.click().catch(() => {});
        const word = await page.$('.word:not([disabled]):not(.is-used)');
        if (word) await word.click().catch(() => {});
      }
    } else if (engine === 'boleh') {
      for (const card of await page.$$('.verdict-card')) {
        const radio = await card.$('input[type=radio]:not([disabled])');
        if (radio) await radio.check({ force: true }).catch(() => {});
      }
    }
    if (engine === 'cari') {
      const object = await page.$('.object:not([disabled]):not(.is-found)');
      if (!object) return false;
      await object.click().catch(() => {});
    } else {
      await page.click('#btn-check').catch(() => {});
    }
    await page.waitForTimeout(700);
    const modalButton = await page.$('#modal-layer:not([hidden]) .modal-actions > :last-child');
    if (modalButton) {
      await modalButton.click().catch(() => {});
      await page.waitForTimeout(500);
    }
  }
  return /\/selesai\//.test(page.url());
}

/** Register a student and finish every challenge (unthrottled). Returns the finished-screen path of REGION node 1. */
async function prepare(client) {
  const user = await register(client, 'Siswa QA Waktu');
  console.log(`Test student: ${user}`);
  const { page } = client;
  let finished = null;
  for (const region of REGIONS) {
    // The region dialogue is shown once per PHP session before its first challenge
    await page.goto(`${BASE}dialog/${region}`);
    for (let n = 1; n <= 4; n++) {
      await page.goto(`${BASE}tantangan/${region}/${n}`);
      await page.waitForFunction(() => document.body?.dataset.pageReady, null, { timeout: TIMEOUT }).catch(() => {});
      await openGate(page);
      const ok = await exhaust(page);
      console.log(`  ${region} ${n}: ${ok ? 'done' : `not finished (${page.url().replace(BASE, '/')})`}`);
      if (/\/masuk/.test(page.url())) throw new Error('the test student was logged out during preparation');
      if (ok && region === REGION && n === 1) finished = new URL(page.url()).pathname.replace(new URL(BASE).pathname, '');
    }
    await page.goto(`${BASE}tuntas/${region}`);
  }
  return { user, finished };
}

// ------------------------------------------------------------------ screens (Dok 3 §4)
function screenList(finished) {
  const r = REGION;
  const list = [
    ['awal', '', 'guest'],
    ['mulai', 'mulai', 'guest'],
    ['masuk', 'masuk', 'guest'],
    ['daftar-1', 'daftar/1', 'guest'],
    ['gerbang', 'gerbang'],
    ['ganti-sandi', 'ganti-sandi'],
    ['intro', 'intro'],
    ['peta', 'peta'],
    [`dialog-${r}`, `dialog/${r}`],
    [`wilayah-${r}`, `wilayah/${r}`],
  ];
  for (let n = 1; n <= 4; n++) {
    list.push([`misi-${r}-${n}`, `misi/${r}/${n}`]);
    list.push([`tantangan-${r}-${n}`, `tantangan/${r}/${n}`]);
  }
  if (finished) list.push([`selesai-${r}-1`, finished]);
  list.push(
    [`hasil-${r}-1`, `hasil/${r}/1`],
    [`tuntas-${r}`, `tuntas/${r}`],
    ['pustaka-rak', 'pustaka'],
    [`pustaka-${r}`, `pustaka/${r}`],
    [`pustaka-terkunci-${r}`, `pustaka/${r}`, 'fresh'],
    ['profil', 'profil'],
    ['refleksi', 'refleksi'],
    ['penutup', 'penutup'],
  );
  return ONLY ? list.filter(([name]) => ONLY.some((o) => name.startsWith(o))) : list;
}

// ------------------------------------------------------------------ run
if (args.serve) {
  console.log(`Starting nginx + php-fpm on ${BASE}`);
  await serve();
}
const browser = await chromium.launch();
const samples = [];
const record = (s) => {
  samples.push(s);
  const m = s.error ? `ERROR ${s.error}` : `ready ${s.ready} ms, decoded ${s.decoded} ms, ${s.net.total.n} req, ${(s.net.total.bytes / 1024).toFixed(0)} KB`;
  console.log(`  ${s.screen_name} ${s.kind}${s.run ? ` #${s.run}` : ''}: ${m}`);
};

try {
  const student = await newClient('student');
  const guest = await newClient('guest');
  const fresh = await newClient('fresh');

  let user = args.user ? String(args.user) : null;
  let finished = args.finished ? String(args.finished) : null;
  if (user) await logIn(student, user);
  else ({ user, finished } = await prepare(student));
  await register(fresh, 'Siswa QA Baru');

  const clients = { student, guest, fresh };
  for (const c of Object.values(clients)) await c.net.throttle(true);

  console.log(`\nNetwork: ${NETWORK.downloadThroughput * 8 / 1000} kbps down, ${NETWORK.uploadThroughput * 8 / 1000} kbps up, ${NETWORK.latency} ms RTT; ${RUNS} run(s)\n`);

  for (const [name, route, who = 'student'] of screenList(finished)) {
    const client = clients[who];
    const url = BASE + route;
    console.log(`- ${name} (/${route})`);
    for (let run = 1; run <= RUNS; run++) {
      await client.net.clearCache();
      record({ screen_name: name, route, kind: 'cold', run, ...await measure(client, gotoTrigger(client, url)) });
      record({ screen_name: name, route, kind: 'warm', run, ...await measure(client, gotoTrigger(client, url)) });
      if (!args['no-lang'] && !name.startsWith('tantangan')) {
        // Language switch both ways: id → en, then en → id (warm cache)
        for (const dir of ['lang', 'lang']) {
          await openGate(client.page);
          const sel = '.lang-switch .lang-opt:not(.is-active)';
          if (!await client.page.$(sel)) break;
          record({ screen_name: name, route, kind: dir, run, ...await measure(client, clickTrigger(client, sel)) });
        }
      }
    }
  }

  // Language switch on a challenge screen: once per run, on a fresh attempt
  if (!args['no-lang'] && (!ONLY || ONLY.some((o) => 'tantangan'.startsWith(o) || o.startsWith('tantangan')))) {
    const client = student;
    console.log(`- tantangan-${REGION}-1 (language)`);
    for (let run = 1; run <= RUNS; run++) {
      await measure(client, gotoTrigger(client, `${BASE}tantangan/${REGION}/1`));
      for (const dir of ['lang', 'lang']) {
        await openGate(client.page);
        record({ screen_name: `tantangan-${REGION}-1`, route: `tantangan/${REGION}/1`, kind: dir, run, ...await measure(client, clickTrigger(client, '.lang-switch .lang-opt:not(.is-active)')) });
      }
    }
  }

  // Tap path (warm): peta → wilayah → misi → tantangan, tap → stage ready including link curtains
  if (!args['no-path'] && !ONLY) {
    const client = student;
    const steps = [
      ['tap peta→wilayah', 'peta', `.map-pin[href$="wilayah/${REGION}"], a.btn[href$="wilayah/${REGION}"]`],
      ['tap wilayah→misi', `wilayah/${REGION}`, `.map-pin[href*="misi/${REGION}/1"], .map-pin[href*="hasil/${REGION}/1"]`],
      ['tap misi→tantangan', `misi/${REGION}/1`, `a[href$="tantangan/${REGION}/1"]`],
    ];
    console.log('- tap path');
    for (let run = 1; run <= RUNS; run++) {
      for (const [name, from, sel] of steps) {
        await measure(client, gotoTrigger(client, BASE + from)); // warm the source screen
        await openGate(client.page);
        record({ screen_name: name, route: from, kind: 'tap', run, ...await measure(client, clickTrigger(client, sel)) });
      }
    }
  }
} finally {
  await browser.close();
  stopServers();
}

// ------------------------------------------------------------------ summary
const pct = (values, p) => {
  const v = values.filter((x) => typeof x === 'number').sort((a, b) => a - b);
  if (!v.length) return null;
  return v[Math.min(v.length - 1, Math.ceil(p / 100 * v.length) - 1)];
};
const groups = new Map();
for (const s of samples) {
  const key = `${s.screen_name}|${s.kind}`;
  if (!groups.has(key)) groups.set(key, []);
  groups.get(key).push(s);
}
const summary = [...groups].map(([key, list]) => {
  const [screen, kind] = key.split('|');
  const ok = list.filter((s) => !s.error);
  const stat = (f) => ({ p50: pct(ok.map(f), 50), p95: pct(ok.map(f), 95) });
  return {
    screen,
    kind,
    samples: list.length,
    errors: list.length - ok.length,
    gate: ok[0]?.gate ?? null,
    fcp: stat((s) => s.fcp),
    response: stat((s) => s.response),
    jsReady: stat((s) => s.jsReady),
    ready: stat((s) => s.ready),
    decoded: stat((s) => s.decoded),
    images: pct(ok.map((s) => s.images), 50),
    emptySlots: pct(ok.map((s) => s.emptySlots), 50),
    pendingAtReady: stat((s) => s.pendingAtReady),
    requests: {
      html: pct(ok.map((s) => s.net.html.n), 50),
      api: pct(ok.map((s) => s.net.api.n), 50),
      assets: pct(ok.map((s) => s.net.assets.n), 50),
      external: pct(ok.map((s) => s.net.external.n), 50),
      revalidated: pct(ok.map((s) => s.net.revalidated), 50),
      cached: pct(ok.map((s) => s.net.cached), 50),
    },
    kb: {
      html: Math.round(pct(ok.map((s) => s.net.html.bytes), 50) / 1024),
      api: Math.round(pct(ok.map((s) => s.net.api.bytes), 50) / 1024),
      assets: Math.round(pct(ok.map((s) => s.net.assets.bytes), 50) / 1024),
      external: Math.round(pct(ok.map((s) => s.net.external.bytes), 50) / 1024),
      total: Math.round(pct(ok.map((s) => s.net.total.bytes), 50) / 1024),
    },
  };
});

const meta = { base: BASE, served: Boolean(args.serve), network: { downKbps: NETWORK.downloadThroughput * 8 / 1000, upKbps: NETWORK.uploadThroughput * 8 / 1000, rttMs: NETWORK.latency }, runs: RUNS, region: REGION, browser: browser.version?.() ?? null, date: new Date().toISOString() };
fs.writeFileSync(path.join(OUT, `nav-${STAMP}.json`), JSON.stringify({ meta, samples }, null, 2));
fs.writeFileSync(path.join(OUT, `nav-${STAMP}-summary.json`), JSON.stringify({ meta, summary }, null, 2));

const f = (v) => (v == null ? '—' : String(v));
const rows = [['screen', 'kind', 'gate', 'S1 p50/p95', 'FCP p50/p95', 'ready p50/p95', 'decoded p50/p95', 'late/img/empty', 'req H/A/S/X (304)', 'KB H/A/S/X']];
for (const s of summary) {
  rows.push([s.screen, s.kind, s.gate || '', `${f(s.response.p50)}/${f(s.response.p95)}`, `${f(s.fcp.p50)}/${f(s.fcp.p95)}`, `${f(s.ready.p50)}/${f(s.ready.p95)}`, `${f(s.decoded.p50)}/${f(s.decoded.p95)}`,
    `${f(s.pendingAtReady.p50)}/${f(s.images)}/${f(s.emptySlots)}`, `${f(s.requests.html)}/${f(s.requests.api)}/${f(s.requests.assets)}/${f(s.requests.external)} (${f(s.requests.revalidated)})`,
    `${s.kb.html}/${s.kb.api}/${s.kb.assets}/${s.kb.external}${s.errors ? ` !${s.errors} err` : ''}`]);
}
const widths = rows[0].map((_, i) => Math.max(...rows.map((r) => r[i].length)));
console.log('\nTimes in ms, p50/p95 (cold/warm: from navigation start; lang/tap: from the tap; S1 = tap → first visual change). late/img/empty = images still loading when the stage was ready / images on the stage / empty image slots.');
console.log('Requests and KB (p50) as HTML / API / same-origin assets / external; (304) = revalidated.');
for (const r of rows) console.log(r.map((c, i) => (i < 3 ? c.padEnd(widths[i]) : c.padStart(widths[i]))).join('  '));
console.log(`\nJSON: ${path.relative(root, OUT)}/nav-${STAMP}.json, nav-${STAMP}-summary.json`);
