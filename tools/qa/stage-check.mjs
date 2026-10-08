#!/usr/bin/env node
/**
 * Uji tampilan panggung 16:9 (Dok 3 §12.2).
 *
 * Menjalankan peramban Chromium (Playwright) terhadap GELITA yang sedang
 * berjalan, mengunjungi semua layar game §4 dan keempat tantangan di ketiga
 * wilayah dalam beberapa ukuran jendela, lalu memeriksa:
 *
 *   - #stage berasio 16:9 (±1 px) dan berada di tengah;
 *   - halaman (document.scrollingElement) tidak pernah bergulir;
 *   - tidak ada elemen yang keluar dari #stage (kecuali layar putar);
 *   - isi yang melebihi wadahnya hanya ada di panel .stage-scroll;
 *   - posisi relatif pin peta, benda Cari Objek, dan panel berkoordinat
 *     (persen terhadap panggung) sama di semua ukuran (selisih < 0,5 %);
 *   - 390 × 844 dengan sentuh menampilkan layar putar.
 *
 * Tangkapan layar: writable/qa/stage/{layar}-{ukuran}.png.
 *
 * Persiapan (lihat docs/05_VIEW_UI.md §Panggung 16:9):
 *   php spark serve                      (atau server lain; QA_BASE_URL)
 *   database hasil instalasi baru / gelita:reset, studi berjalan dengan
 *   "Cara wilayah terbuka" = bebas (panel admin → Penelitian), agar ketiga
 *   wilayah dapat dikunjungi tanpa menyelesaikan wilayah sebelumnya.
 *
 * Pemakaian:
 *   node tools/qa/stage-check.mjs [--base=http://localhost:8080/] [--user=… --pass=…]
 *                                 [--sizes=1280x720,1920x1080] [--only=peta,tantangan]
 *
 * Tanpa --user, skrip mendaftarkan siswa uji baru lewat /daftar (3 langkah).
 * Skrip lalu menghabiskan kesempatan setiap tantangan (jawaban asal) agar
 * layar selesai, hasil, wilayah tuntas, dan Pustaka yang terbuka ikut
 * diperiksa — jalankan hanya pada database uji, lalu `gelita:reset`.
 * Chromium sudah terpasang (PLAYWRIGHT_BROWSERS_PATH); jangan `playwright install`.
 *
 * Kode keluar 0 bila semua pemeriksaan lolos, 1 bila ada yang gagal.
 */
import { createRequire } from 'node:module';
import { execSync } from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const require = createRequire(import.meta.url);
const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');

function loadPlaywright() {
  try { return require('playwright'); } catch { /* lanjut ke instalasi global */ }
  const globalRoot = execSync('npm root -g').toString().trim();
  return require(path.join(globalRoot, 'playwright'));
}
const { chromium } = loadPlaywright();

// ------------------------------------------------------------------ opsi
const args = Object.fromEntries(process.argv.slice(2).map((arg) => {
  const [key, ...rest] = arg.replace(/^--/, '').split('=');
  return [key, rest.length ? rest.join('=') : true];
}));
const BASE = String(args.base || process.env.QA_BASE_URL || 'http://localhost:8080/').replace(/\/?$/, '/');
const OUT = path.join(root, 'writable/qa/stage');
const SIZES = String(args.sizes || '1280x720,1920x1080,1366x657,1024x768,2560x1080,844x390')
  .split(',').map((s) => s.trim()).filter(Boolean)
  .map((s) => { const [w, h] = s.split('x').map(Number); return { w, h, name: `${w}x${h}` }; });
const ONLY = args.only ? String(args.only).split(',') : null;
const REGIONS = ['temanggung', 'magelang', 'wonosobo'];
const PASS = String(args.pass || 'Lentera!Kedu2026');

fs.mkdirSync(OUT, { recursive: true });

const failures = [];
const fail = (screen, size, message) => {
  failures.push(`${screen} @ ${size}: ${message}`);
  console.log(`  ✗ ${message}`);
};

// ------------------------------------------------------------------ pemeriksaan di halaman
/** Dijalankan di peramban: pemeriksaan §12.2 butir 3 + posisi relatif elemen penanda. */
function inspect() {
  const stage = document.getElementById('stage');
  const out = { errors: [], marks: {} };
  if (!stage) {
    out.errors.push('#stage tidak ada');
    return out;
  }
  const s = stage.getBoundingClientRect();
  out.stage = { x: s.x, y: s.y, w: s.width, h: s.height };
  if (Math.abs(s.width * 9 / 16 - s.height) > 1) out.errors.push(`rasio #stage ${s.width.toFixed(1)}×${s.height.toFixed(1)} bukan 16:9`);
  const cx = s.x + s.width / 2 - innerWidth / 2;
  const cy = s.y + s.height / 2 - innerHeight / 2;
  if (Math.abs(cx) > 1 || Math.abs(cy) > 1) out.errors.push('#stage tidak di tengah layar');

  const se = document.scrollingElement;
  if (se.scrollHeight > se.clientHeight + 1 || se.scrollWidth > se.clientWidth + 1 || se.scrollTop || se.scrollLeft) {
    out.errors.push(`halaman bergulir (${se.scrollWidth}×${se.scrollHeight} > ${se.clientWidth}×${se.clientHeight})`);
  }

  const visible = (el) => {
    const st = getComputedStyle(el);
    if (st.display === 'none' || st.visibility === 'hidden' || Number(st.opacity) === 0) return false;
    const r = el.getBoundingClientRect();
    return r.width > 0 && r.height > 0;
  };
  const describe = (el) => `${el.tagName.toLowerCase()}${el.id ? `#${el.id}` : ''}${el.classList.length ? `.${[...el.classList].slice(0, 3).join('.')}` : ''}`;
  // Latar beranimasi Ken Burns sengaja sedikit lebih besar dan dipotong panggung
  const ANIMATED = '.scene, .scene *, .cine-bg, .curtain-bg, .kenal-bg, .confetti, .confetti *, .narrator-fx, .visually-hidden, .skip-link, .rotate-gate, .rotate-gate *, dialog:not([open]), template *';
  const clippedBy = (el) => {
    for (let p = el.parentElement; p && p !== stage; p = p.parentElement) {
      const ov = getComputedStyle(p);
      if (/(auto|scroll|hidden|clip)/.test(ov.overflowY + ov.overflowX)) return p;
    }
    return null;
  };

  const outside = [];
  const overflowing = [];
  for (const el of document.querySelectorAll('body *')) {
    if (el.matches(ANIMATED) || !visible(el)) continue;
    const r = el.getBoundingClientRect();
    // Di luar panggung: hanya dihitung bila tidak dipotong wadah bergulir/terpotong di dalamnya
    if ((r.left < s.left - 1 || r.top < s.top - 1 || r.right > s.right + 1 || r.bottom > s.bottom + 1) && !clippedBy(el)) {
      if (!el.closest('#stage') || el.closest('dialog[open]') === null) outside.push(describe(el));
    }
    // Isi melebihi wadahnya: hanya boleh di .stage-scroll
    const st = getComputedStyle(el);
    const scroller = /(auto|scroll)/.test(st.overflowY);
    const hidden = /(hidden|clip)/.test(st.overflowY);
    if ((scroller || hidden) && el.scrollHeight > el.clientHeight + 2 && !el.matches('.stage-scroll, .stage, .stage-main, .stage-frame, textarea, select, svg, svg *, .map-canvas, .hunt-scene, .clue-text, .puzzle-board, .piece, .book-frame, .shelf-cover, .character, .hunt-guide')) {
      overflowing.push(`${describe(el)} (${el.scrollHeight}>${el.clientHeight})`);
    }
  }
  if (outside.length) out.errors.push(`keluar dari #stage: ${[...new Set(outside)].slice(0, 6).join(', ')}`);
  if (overflowing.length) out.errors.push(`isi terpotong di luar .stage-scroll: ${[...new Set(overflowing)].slice(0, 6).join(', ')}`);

  // Posisi relatif (persen panggung) elemen penanda
  const marks = document.querySelectorAll('.map-point, .object, .stage-box, .challenge-head, .puzzle-board, .hunt-scene, .rumpang-reading, .boleh-passages, .boleh-cards, .cine-caption, .dialogue-box');
  marks.forEach((el, i) => {
    if (!visible(el)) return;
    const r = el.getBoundingClientRect();
    const key = `${describe(el)}#${i}`;
    out.marks[key] = [(r.left - s.left) / s.width * 100, (r.top - s.top) / s.height * 100, r.width / s.width * 100, r.height / s.height * 100];
  });
  return out;
}

// ------------------------------------------------------------------ alur
const browser = await chromium.launch();
const shotStyle = '#debug-icon, #toolbarContainer { display: none !important; }';

async function settle(page, ms = 900) {
  await page.waitForLoadState('domcontentloaded').catch(() => {});
  await page.addStyleTag({ content: shotStyle }).catch(() => {});
  await page.waitForTimeout(ms);
}

/** Masuk dengan siswa uji di konteks ini (sesi diregenerasi server, jadi tiap ukuran masuk sendiri). */
async function logIn(ctx, user) {
  const page = await ctx.newPage();
  await page.goto(`${BASE}masuk`);
  await page.fill('#username', user);
  await page.fill('#password', PASS);
  await page.click('main form button[type=submit]');
  await page.waitForLoadState('domcontentloaded');
  await page.waitForTimeout(500);
  await page.close();
}

/** Daftarkan siswa uji (kecuali --user); mengembalikan nama penggunanya. */
async function signIn() {
  const ctx = await browser.newContext({ viewport: { width: 1280, height: 720 } });
  const page = await ctx.newPage();
  let user = args.user ? String(args.user) : null;

  if (!user) {
    user = `qa${Date.now().toString(36)}`;
    await page.goto(`${BASE}daftar/1`);
    await page.fill('#display_name', 'Siswa QA Panggung');
    await page.fill('#age', '10');
    await page.check('input[name=gender]');
    await page.click('main form button[type=submit]');
    await page.waitForURL(/daftar\/2/, { waitUntil: 'domcontentloaded' });
    await page.selectOption('#province_code', { index: 1 });
    const district = await page.$$eval('#district_code option', (o) => o.filter((x) => x.value && !x.disabled && x.offsetParent !== null).map((x) => x.value));
    const options = await page.$$eval('#district_code option', (o) => o.filter((x) => x.value).map((x) => x.value));
    await page.selectOption('#district_code', district[0] || options[0]);
    if (await page.isVisible('#school_name')) await page.fill('#school_name', 'SD Uji Panggung');
    await page.click('main form button[type=submit]');
    await page.waitForURL(/daftar\/3/, { waitUntil: 'domcontentloaded' });
    await page.fill('#username', user);
    await page.fill('#password', PASS);
    await page.fill('#password_confirm', PASS);
    await page.check('input[name=assent]');
    await page.click('main form button[type=submit]');
    await page.waitForURL(/intro|gerbang|peta/, { waitUntil: 'domcontentloaded' });
    console.log(`Siswa uji baru: ${user}`);
  } else {
    await ctx.close();
    return user;
  }
  await page.goto(`${BASE}intro/selesai`).catch(() => {});
  await ctx.close();
  return user;
}

/** Habiskan kesempatan tantangan yang sedang terbuka (jawaban asal) sampai layar selesai. */
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

const user = await signIn();

/**
 * Daftar layar: [nama, jalur | fungsi(page)]. Fungsi menyiapkan keadaan
 * (mis. membuka modal) sesudah jalur dibuka.
 */
const screens = [
  ['awal', '', { guest: true }],
  ['mulai', 'mulai', { guest: true }],
  ['masuk', 'masuk', { guest: true }],
  ['daftar-1', 'daftar/1', { guest: true }],
  ['gerbang', 'gerbang'],
  ['intro', 'intro', { tap: '.narrator-tap-btn' }],
  ['peta', 'peta'],
  ['kenali', 'peta', { click: '[data-kenal-open]' }],
  ['pustaka-rak', 'pustaka'],
  ['profil', 'profil'],
  ['ganti-sandi', 'ganti-sandi'],
];
for (const region of REGIONS) {
  screens.push([`dialog-${region}`, `dialog/${region}`, { tap: '.narrator-tap-btn' }]);
  screens.push([`wilayah-${region}`, `wilayah/${region}`]);
  for (let n = 1; n <= 4; n++) {
    screens.push([`misi-${region}-${n}`, `misi/${region}/${n}`]);
    screens.push([`tantangan-${region}-${n}`, `tantangan/${region}/${n}`]);
    // Modal hasil percobaan pertama (tantangan batch) — hanya di wilayah pertama
    if (region === 'temanggung' && n < 4) screens.push([`modal-${region}-${n}`, `tantangan/${region}/${n}`, { check: true }]);
  }
}

const selected = ONLY ? screens.filter(([name]) => ONLY.some((o) => name.startsWith(o))) : screens;
const baseline = new Map(); // layar → marks pada ukuran pertama

for (const size of SIZES) {
  console.log(`\n== ${size.name}`);
  const ctx = await browser.newContext({ viewport: { width: size.w, height: size.h } });
  await logIn(ctx, user);
  const guest = await browser.newContext({ viewport: { width: size.w, height: size.h } });
  const page = await ctx.newPage();
  const guestPage = await guest.newPage();
  page.on('pageerror', (e) => fail('js', size.name, e.message));

  for (const [name, route, opts = {}] of selected) {
    const p = opts.guest ? guestPage : page;
    await p.goto(BASE + route).catch(() => {});
    await settle(p);
    if (opts.tap) { await p.click(opts.tap).catch(() => {}); await p.waitForTimeout(1200); }
    if (opts.click) { await p.click(opts.click).catch(() => {}); await p.waitForTimeout(1200); }
    if (opts.check) { await p.click('#btn-check').catch(() => {}); await p.waitForTimeout(1200); }
    console.log(`- ${name} (${p.url().replace(BASE, '/')})`);
    await check(p, name, size);
  }

  // Layar sesudah tantangan: selesai, hasil, wilayah tuntas, Pustaka (hanya sekali, di ukuran pertama)
  if (size === SIZES[0] && !ONLY) {
    for (const region of REGIONS) {
      for (let n = 1; n <= 4; n++) {
        await page.goto(`${BASE}tantangan/${region}/${n}`);
        await settle(page);
        if (await exhaust(page)) {
          await settle(page, 1200);
          console.log(`- selesai-${region}-${n} (${page.url().replace(BASE, '/')})`);
          await check(page, `selesai-${region}-${n}`, size);
        }
      }
    }
    for (const [name, route] of [['hasil', `hasil/${REGIONS[0]}/1`], ['tuntas', `tuntas/${REGIONS[0]}`], ['pustaka', `pustaka/${REGIONS[0]}`], ['penutup', 'penutup'], ['refleksi', 'refleksi']]) {
      await page.goto(BASE + route);
      await settle(page);
      if (name === 'tuntas' || name === 'penutup') { await page.click('.narrator-tap-btn').catch(() => {}); await page.waitForTimeout(1200); }
      console.log(`- ${name} (${page.url().replace(BASE, '/')})`);
      await check(page, name, size);
      const zoom = name === 'pustaka' ? page.locator('.slide:not([hidden]) .book-zoom:visible').first() : null;
      if (zoom && await zoom.count()) {
        await zoom.click().catch(() => {});
        await page.waitForTimeout(1000);
        await check(page, 'pustaka-lightbox', size);
        await page.keyboard.press('Escape');
      }
    }
  }
  await ctx.close();
  await guest.close();
}

// Layar putar: 390 × 844, sentuh
{
  console.log('\n== 390x844 (sentuh, tegak)');
  // Masuk saat mendatar (formulir tertutup layar putar ketika tegak), lalu putar ke tegak
  const ctx = await browser.newContext({ viewport: { width: 844, height: 390 }, hasTouch: true, isMobile: true });
  await logIn(ctx, user);
  const page = await ctx.newPage();
  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto(`${BASE}peta`);
  await settle(page);
  const gate = await page.evaluate(() => {
    const g = document.querySelector('.rotate-gate');
    const r = g?.getBoundingClientRect();
    return g ? { shown: getComputedStyle(g).display !== 'none', w: r.width, h: r.height, stageHidden: getComputedStyle(document.querySelector('.stage-frame')).visibility === 'hidden' } : null;
  });
  if (!gate?.shown || gate.w < 389 || gate.h < 843) fail('layar-putar', '390x844', 'layar putar tidak menutup seluruh layar');
  if (!gate?.stageHidden) fail('layar-putar', '390x844', 'panggung masih terlihat di belakang layar putar');
  await page.screenshot({ path: path.join(OUT, 'layar-putar-390x844.png') });
  console.log(`- layar-putar ${gate?.shown ? '✓' : '✗'}`);
  await ctx.close();
}

await browser.close();

console.log(`\nTangkapan layar: ${path.relative(root, OUT)}/`);
if (failures.length) {
  console.log(`\n${failures.length} pemeriksaan gagal:`);
  failures.forEach((f) => console.log(`  - ${f}`));
  process.exit(1);
}
console.log('\nSemua pemeriksaan lolos.');

// ------------------------------------------------------------------ pembantu
async function check(page, name, size) {
  // Penunjuk mouse yang tertinggal di atas benda memicu :hover (scale 1.05) dan menggeser kotaknya
  await page.mouse.move(0, 0);
  await page.waitForTimeout(250);
  await page.screenshot({ path: path.join(OUT, `${name}-${size.name}.png`) });
  const result = await page.evaluate(inspect).catch((e) => ({ errors: [`evaluate: ${e.message}`], marks: {} }));
  result.errors.forEach((e) => fail(name, size.name, e));

  // Pengerjaan baru (variasi acak lain) punya posisi benda lain: bandingkan per pengerjaan
  const attempt = await page.getAttribute('[data-screen="challenge"]', 'data-attempt').catch(() => null);
  const key = `${name}|${page.url().replace(/#.*$/, '')}|${attempt ?? ''}`;
  if (!baseline.has(key)) {
    baseline.set(key, result.marks);
    return;
  }
  const base = baseline.get(key);
  for (const [mark, pos] of Object.entries(result.marks)) {
    const ref = base[mark];
    if (!ref) continue;
    const diff = Math.max(...pos.map((v, i) => Math.abs(v - ref[i])));
    if (diff >= 0.5) fail(name, size.name, `posisi ${mark} bergeser ${diff.toFixed(2)} % dari ukuran pertama`);
  }
}
