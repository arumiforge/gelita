#!/usr/bin/env node
/**
 * Uji peramban perbaikan PR #42 (balapan permintaan dan perilaku klien
 * tantangan), dengan Chromium sungguhan lewat Playwright.
 *
 *   php spark serve                                   # aplikasi lokal, database MariaDB/MySQL
 *   node tools/qa/challenge-races.mjs                 # semua skenario
 *   node tools/qa/challenge-races.mjs --only=freeze   # satu kelompok: freeze, timing, cari, reload, race
 *
 * Lingkungan:
 *   QA_BASE_URL   alamat aplikasi (bawaan http://localhost:8080)
 *   QA_MYSQL      perintah klien database (bawaan "mysql -uroot gelita"); skrip membaca
 *                 item_tries / game_event_logs dan menyetel unlock_mode studi aktif
 *                 menjadi `free` selama uji (dipulihkan di akhir)
 *   QA_RACE_ROUNDS  jumlah ulangan balapan dua tab (bawaan 5)
 *   PLAYWRIGHT_BROWSERS_PATH  Chromium terpasang (/opt/pw-browsers); jangan `playwright install`
 *
 * Skenario (bagian "QA PR #42 dan data simulasi" di docs/arsip/CATATAN_IMPLEMENTASI-2026-10.md):
 *   1. freeze  — selama "Cek jawabanku" berjalan (respons /check diperlambat), isian Susun
 *                Gambar, Isi Rumpang, dan Benar atau Salah tidak bisa diubah; jawaban di layar
 *                sesudah hasil = jawaban yang dinilai server (item_tries.answer_json).
 *   2. timing  — first_input_ms / last_input_ms terkirim dan tersimpan di item_tries.
 *   3. cari    — selama ketukan menunggu server, tombol lompat petunjuk diabaikan; hasil
 *                ketukan tercatat ke petunjuk yang aktif saat diketuk.
 *   4. reload  — muat ulang karena TRIES_EXHAUSTED, ATTEMPT_CHANGED, CLUE_CLOSED, ATTEMPT_CLOSED
 *                (respons tiruan dan dua tab sungguhan) tidak menulis challenge_abandoned.
 *   5. race    — dua tab mengirim cek bersamaan: satu berlaku, yang lain ATTEMPT_CHANGED /
 *                TRIES_EXHAUSTED; tanpa try_no 4, tanpa penutupan ganda.
 *
 * Keluar dengan kode 1 bila ada pemeriksaan yang gagal.
 */
import { execFileSync } from 'node:child_process';
import { createRequire } from 'node:module';

const BASE = (process.env.QA_BASE_URL || 'http://localhost:8080').replace(/\/$/, '');
const MYSQL = (process.env.QA_MYSQL || 'mysql -uroot gelita').split(/\s+/);
const ROUNDS = Number(process.env.QA_RACE_ROUNDS || 5);
const ONLY = (process.argv.find((a) => a.startsWith('--only=')) || '').slice(7) || null;
const PASSWORD = 'Qa#Gelita2026';

// Playwright dari node_modules proyek atau instalasi global
async function loadPlaywright() {
  try {
    return await import('playwright');
  } catch {
    const require = createRequire(import.meta.url);
    const root = execFileSync('npm', ['root', '-g']).toString().trim();
    return require(`${root}/playwright`);
  }
}

// ------------------------------------------------------------------ hasil

const results = [];
function check(group, name, ok, detail = '') {
  results.push({ group, name, ok: Boolean(ok), detail });
  const mark = ok ? '\x1b[32m✓\x1b[0m' : '\x1b[31m✗\x1b[0m';
  console.log(`  ${mark} ${name}${detail ? ` — ${detail}` : ''}`);
}

// --------------------------------------------------------------- database

function sql(query) {
  const out = execFileSync(MYSQL[0], [...MYSQL.slice(1), '-N', '-B', '-e', query]).toString();
  return out.trim() === '' ? [] : out.trim().split('\n').map((line) => line.split('\t'));
}
const one = (query) => sql(query)[0]?.[0] ?? null;
const q = (value) => `'${String(value).replace(/\\/g, '\\\\').replace(/'/g, "\\'")}'`;

// ---------------------------------------------------------------- bantuan

const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

/** Satu janji yang bisa dilepas dari luar. */
function gate() {
  let open;
  const promise = new Promise((resolve) => { open = resolve; });
  return { promise, open };
}

/** Siswa baru lewat pendaftaran 3 langkah, sampai peta (cerita pembuka dilewati lewat /intro/selesai). */
async function register(context, tag) {
  const page = await context.newPage();
  const username = `qa_${tag}_${Date.now().toString(36)}`.slice(0, 30);

  await page.goto(`${BASE}/daftar/1`);
  await page.fill('#display_name', `QA ${tag}`);
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

  await page.goto(`${BASE}/intro/selesai`);
  const participantId = Number(one(`SELECT id FROM participants WHERE username = ${q(username)}`));
  return { page, username, participantId };
}

/** Membuka tantangan; dialog pembuka wilayah dibuka dulu bila diminta. */
async function openChallenge(page, level, seq) {
  await page.goto(`${BASE}/tantangan/${level}/${seq}`);
  if (/\/dialog\//.test(page.url())) {
    await page.goto(`${BASE}/tantangan/${level}/${seq}`);
  }
  await page.waitForSelector('[data-screen="challenge"]');
  await page.waitForFunction(() => document.querySelector('[data-screen="challenge"]')?.dataset.ready === '1'
    || document.querySelector('#btn-check, #hunt-scene'));
  await sleep(400); // engine dimuat dinamis (import())
  return page.evaluate(() => JSON.parse(document.getElementById('challenge-data').textContent));
}

/** Tutup modal yang terbuka dengan tombol utamanya. */
async function closeModal(page) {
  const button = page.locator('.modal .modal-actions button').first();
  await button.waitFor({ timeout: 10_000 });
  await button.click();
  await page.waitForSelector('.modal', { state: 'detached', timeout: 10_000 }).catch(() => {});
}

/**
 * Menahan request ke $pattern sampai `release()`; `seen` terisi body request
 * pertama. Sesudah dilepas, request berikutnya lewat biasa.
 */
async function hold(page, pattern) {
  const held = gate();
  const arrived = gate();
  let body = null;
  let first = true;
  await page.route(pattern, async (route) => {
    if (!first) return route.continue();
    first = false;
    body = route.request().postDataJSON();
    arrived.open();
    await held.promise;
    await route.continue();
  });
  return {
    arrived: arrived.promise,
    release: held.open,
    body: () => body,
    done: () => page.unroute(pattern),
  };
}

/** Respons tiruan galat aturan untuk request pertama ke $pattern. */
async function fakeError(page, pattern, code, status = 409) {
  let first = true;
  await page.route(pattern, async (route) => {
    if (!first) return route.continue();
    first = false;
    await route.fulfill({
      status,
      contentType: 'application/json',
      body: JSON.stringify({ success: false, code, message: `tiruan ${code}` }),
    });
  });
}

function abandonedEvents(attemptId) {
  return Number(one(`SELECT COUNT(*) FROM game_event_logs WHERE event_type = 'challenge_abandoned' AND challenge_attempt_id = ${Number(attemptId)}`));
}

function tries(attemptId) {
  return sql(`SELECT challenge_item_id, try_no, check_no, is_correct, answer_json, IFNULL(first_input_ms, 'NULL'), IFNULL(last_input_ms, 'NULL')
              FROM item_tries WHERE challenge_attempt_id = ${Number(attemptId)} ORDER BY id`)
    .map(([item, tryNo, checkNo, ok, answer, first, last]) => ({
      item: Number(item), tryNo: Number(tryNo), checkNo: checkNo === 'NULL' ? null : Number(checkNo), ok: ok === '1',
      answer: JSON.parse(answer), first: first === 'NULL' ? null : Number(first), last: last === 'NULL' ? null : Number(last),
    }));
}

/** Kunci jawaban dari database (alat QA saja; klien tidak pernah menerimanya). */
function keyOf(itemId) {
  return JSON.parse(one(`SELECT answer_key_json FROM challenge_items WHERE id = ${Number(itemId)}`) || '{}');
}


// ------------------------------------------------------------- skenario 1–2

/** Susun Gambar: urutan keping di layar (nomor keping per slot). */
const puzzleOrder = (page) => page.$$eval('#puzzle-board .piece', (pieces) => pieces
  .slice().sort((a, b) => Number(a.dataset.slot) - Number(b.dataset.slot)).map((p) => Number(p.dataset.piece)));

async function freezePuzzle(page) {
  const data = await openChallenge(page, 'temanggung', 1);
  const pieces = page.locator('#puzzle-board .piece');
  // Isian: dua tukar keping (answer_selected + waktu isian)
  await pieces.nth(0).click(); await pieces.nth(1).click();
  await sleep(300);
  await pieces.nth(2).click(); await pieces.nth(3).click();
  const before = await puzzleOrder(page);

  const held = await hold(page, '**/api/attempts/*/check');
  await page.click('#btn-check');
  await held.arrived;
  // Selama request: ketuk dua keping, seret satu keping ke keping lain
  await pieces.nth(4).click({ force: true }); await pieces.nth(5).click({ force: true });
  await pieces.nth(6).dragTo(pieces.nth(7), { force: true }).catch(() => {});
  const during = await puzzleOrder(page);
  held.release();
  await page.waitForSelector('.modal');
  const after = await puzzleOrder(page);
  await held.done();

  const sent = held.body().answers[0].answer.order;
  const graded = tries(data.attempt_id).at(-1);
  check('freeze', 'Susun Gambar: tukar/seret selama /check diabaikan', JSON.stringify(during) === JSON.stringify(before), `${before} → ${during}`);
  check('freeze', 'Susun Gambar: urutan terkirim = urutan di layar sebelum cek', JSON.stringify(sent) === JSON.stringify(before));
  check('freeze', 'Susun Gambar: layar sesudah hasil = jawaban yang dinilai server',
    graded && JSON.stringify(graded.answer.order) === JSON.stringify(after) || (await page.$('#puzzle-board.is-solved')) !== null,
    `server ${JSON.stringify(graded?.answer.order)} · layar ${JSON.stringify(after)}`);
  await closeModal(page);
  return { data, body: held.body() };
}

const blanksOf = (page) => page.$$eval('.rumpang .blank', (blanks) => blanks.map((b) => [Number(b.dataset.item), b.textContent.trim()]));

async function freezeRumpang(page) {
  const data = await openChallenge(page, 'temanggung', 2);
  const blanks = page.locator('.rumpang .blank');
  const count = await blanks.count();
  for (let i = 0; i < count; i++) {
    await blanks.nth(i).click();
    await page.locator('.rumpang .word:not([disabled])').first().click();
    await sleep(150);
  }
  const before = await blanksOf(page);

  const held = await hold(page, '**/api/attempts/*/check');
  await page.click('#btn-check');
  await held.arrived;
  // Selama request: ketuk kotak terisi (melepas kata) lalu chip kata lain
  await blanks.nth(0).click({ force: true });
  await page.locator('.rumpang .word').last().click({ force: true });
  const during = await blanksOf(page);
  held.release();
  await page.waitForSelector('.modal');
  const after = await blanksOf(page);
  await held.done();

  const sent = Object.fromEntries(held.body().answers.map((a) => [a.item_id, a.answer.text]));
  const graded = Object.fromEntries(tries(data.attempt_id).filter((t) => t.checkNo === 1).map((t) => [t.item, t.answer.text]));
  check('freeze', 'Isi Rumpang: kotak dan chip selama /check diabaikan', JSON.stringify(during) === JSON.stringify(before));
  check('freeze', 'Isi Rumpang: isian terkirim = isian di layar', before.every(([id, text]) => sent[id] === text));
  check('freeze', 'Isi Rumpang: layar sesudah hasil = jawaban yang dinilai server', after.every(([id, text]) => graded[id] === text),
    JSON.stringify(after));
  await closeModal(page);
  return { data, body: held.body() };
}

const verdictsOf = (page) => page.$$eval('.verdict-card', (cards) => cards.map((c) => [Number(c.dataset.item), c.querySelector('input[type="radio"]:checked')?.value ?? null]));

async function freezeBoleh(page) {
  const data = await openChallenge(page, 'temanggung', 3);
  const cards = page.locator('.verdict-card');
  const count = await cards.count();
  for (let i = 0; i < count; i++) {
    await cards.nth(i).locator('input[type="radio"]').first().evaluate((input) => input.click());
    await sleep(120);
  }
  const before = await verdictsOf(page);

  const held = await hold(page, '**/api/attempts/*/check');
  await page.click('#btn-check');
  await held.arrived;
  // Selama request: klik radio lain dan tombol panah pada radio pertama
  await cards.nth(0).locator('input[type="radio"]').last().click({ force: true }).catch(() => {});
  await cards.nth(1).locator('input[type="radio"]').first().focus().catch(() => {});
  await page.keyboard.press('ArrowDown');
  const disabled = await page.$$eval('.verdict-card input[type="radio"]', (inputs) => inputs.every((i) => i.disabled));
  const during = await verdictsOf(page);
  held.release();
  await page.waitForSelector('.modal');
  const after = await verdictsOf(page);
  await held.done();

  const graded = Object.fromEntries(tries(data.attempt_id).filter((t) => t.checkNo === 1).map((t) => [t.item, t.answer.verdict]));
  check('freeze', 'Benar atau Salah: semua radio nonaktif selama /check', disabled);
  check('freeze', 'Benar atau Salah: klik dan tombol panah selama /check diabaikan', JSON.stringify(during) === JSON.stringify(before));
  check('freeze', 'Benar atau Salah: layar sesudah hasil = jawaban yang dinilai server', after.every(([id, v]) => graded[id] === v), JSON.stringify(after));
  await closeModal(page);
  return { data, body: held.body() };
}

function timing(runs) {
  for (const { label, data, body } of runs) {
    const rows = tries(data.attempt_id).filter((t) => t.checkNo === 1);
    const sent = Object.fromEntries(body.answers.map((a) => [a.item_id, a]));
    const touched = rows.filter((t) => sent[t.item]?.first_input_ms !== undefined);
    check('timing', `${label}: first_input_ms/last_input_ms terkirim`, touched.length > 0, `${touched.length}/${rows.length} soal`);
    check('timing', `${label}: tersimpan di item_tries sama dengan kiriman`,
      touched.length > 0 && touched.every((t) => t.first === Math.round(sent[t.item].first_input_ms) && t.last === Math.round(sent[t.item].last_input_ms) && t.first <= t.last),
      touched.map((t) => `${t.item}:${t.first}/${t.last}`).join(' '));
  }
}

// ---------------------------------------------------------------- skenario 3

async function cari(page) {
  const data = await openChallenge(page, 'temanggung', 4);
  const clueText = () => page.textContent('#clue-text');
  const firstClue = await clueText();
  const firstItem = data.clues.find((c) => c.text === firstClue)?.item_id;

  const held = await hold(page, '**/api/attempts/*/responses');
  await page.locator('#hunt-scene .object').first().click();
  await held.arrived;
  // Selama ketukan menunggu: lompat ke petunjuk lain lewat daftar sisa
  const jumps = page.locator('.hunt-jump:not([disabled])');
  for (let i = 0; i < await jumps.count(); i++) await jumps.nth(i).click({ force: true });
  const duringClue = await clueText();
  held.release();
  await page.waitForResponse('**/api/attempts/*/responses').catch(() => {});
  await sleep(500);
  await held.done();

  const sentItem = held.body().item_id;
  const row = tries(data.attempt_id).at(-1);
  check('cari', 'Lompat petunjuk selama ketukan menunggu server diabaikan', duringClue === firstClue, `"${firstClue}" → "${duringClue}"`);
  check('cari', 'Ketukan dikirim untuk petunjuk yang aktif saat diketuk', sentItem === firstItem, `item ${sentItem} / ${firstItem}`);
  check('cari', 'item_tries mencatat ketukan ke petunjuk itu', row && row.item === firstItem && row.tryNo === 1, JSON.stringify(row));
  if (await page.$('.modal')) await closeModal(page);

  // Sesudah respons, lompat kembali berfungsi
  const jump = page.locator('.hunt-jump:not([disabled])').last();
  if (await jump.count()) {
    await jump.click();
    check('cari', 'Lompat petunjuk berfungsi lagi sesudah respons', (await clueText()) !== firstClue || (await page.locator('.hunt-jump:not([disabled])').count()) === 1);
  }
  return data;
}

// ---------------------------------------------------------------- skenario 4

/** Muat ulang karena galat aturan: tidak boleh ada challenge_abandoned. */
async function reloadWithout(page, label, { level, seq, pattern, code, trigger }) {
  const data = await openChallenge(page, level, seq);
  await sleep(1500); // beacon halaman sebelumnya (pindah halaman lewat goto) sudah sampai
  const before = abandonedEvents(data.attempt_id);
  await fakeError(page, pattern, code);
  const reloaded = page.waitForEvent('load', { timeout: 15_000 }).then(() => true).catch(() => false);
  await trigger(page, data);
  const ok = await reloaded;
  await page.unroute(pattern);
  await sleep(1500); // beacon pagehide (bila ada) sempat sampai
  check('reload', `${label}: halaman dimuat ulang`, ok);
  check('reload', `${label}: tanpa challenge_abandoned`, abandonedEvents(data.attempt_id) === before, `sebelum ${before}, sesudah ${abandonedEvents(data.attempt_id)}`);
}

async function fillBoleh(page, verdicts = null) {
  const cards = page.locator('.verdict-card');
  for (let i = 0; i < await cards.count(); i++) {
    const card = cards.nth(i);
    const id = Number(await card.getAttribute('data-item'));
    const want = verdicts?.[id];
    const radio = want ? card.locator(`input[type="radio"][value="${want}"]`) : card.locator('input[type="radio"]').first();
    if (await radio.isDisabled() || await radio.isChecked()) continue;
    // Input radio disembunyikan di balik label bergaya: klik lewat DOM (memicu change seperti ketukan)
    await radio.evaluate((input) => input.click());
  }
}

async function reload(context, page) {
  const checkTrigger = async (p) => { await fillBoleh(p); await p.click('#btn-check'); };
  for (const code of ['TRIES_EXHAUSTED', 'ATTEMPT_CHANGED', 'ATTEMPT_CLOSED']) {
    await reloadWithout(page, `/check ${code} (tiruan)`, { level: 'magelang', seq: 3, pattern: '**/api/attempts/*/check', code, trigger: checkTrigger });
  }
  for (const code of ['CLUE_CLOSED', 'ATTEMPT_CHANGED']) {
    await reloadWithout(page, `/responses ${code} (tiruan)`, {
      level: 'magelang', seq: 4, pattern: '**/api/attempts/*/responses', code,
      trigger: (p) => p.locator('#hunt-scene .object').first().click(),
    });
  }

  // Dua tab sungguhan: tab B menghabiskan kesempatan (lalu Lanjut ke layar hasil), tab A lalu mengecek
  const data = await openChallenge(page, 'wonosobo', 3);
  const other = await context.newPage();
  await openChallenge(other, 'wonosobo', 3);
  const wrong = wrongVerdicts(data);
  for (let i = 0; i < 3; i++) {
    await fillBoleh(other, wrong);
    await other.click('#btn-check');
    await other.waitForSelector('.modal');
    await closeModal(other);
  }
  await other.waitForURL(/selesai\//, { timeout: 15_000 }).catch(() => {});
  await sleep(1500);
  const before = abandonedEvents(data.attempt_id);
  const response = page.waitForResponse('**/api/attempts/*/check');
  const left = page.waitForURL(/selesai\//, { timeout: 15_000 }).then(() => true).catch(() => false);
  await fillBoleh(page, wrong);
  await page.click('#btn-check');
  const res = await (await response).json();
  const toResult = await left;
  await sleep(1500);
  await other.close();
  check('reload', `dua tab: cek di tab lama dijawab ${res.code}`, res.code === 'TRIES_EXHAUSTED', JSON.stringify(res).slice(0, 120));
  check('reload', 'dua tab: tab lama pindah ke layar hasil', toResult, page.url());
  check('reload', 'dua tab: tanpa challenge_abandoned', abandonedEvents(data.attempt_id) === before, `sebelum ${before}, sesudah ${abandonedEvents(data.attempt_id)}`);

  // Tab ditutup saat modal "Kesempatanmu sudah habis" masih tampil: pengerjaan sudah ditutup server
  const last = await context.newPage();
  const lastData = await openChallenge(last, 'wonosobo', 1);
  for (let i = 0; i < 3; i++) {
    await last.click('#btn-check');
    await last.waitForSelector('.modal');
    if (i < 2) await closeModal(last);
  }
  await sleep(800);
  await last.close({ runBeforeUnload: true });
  await sleep(2000);
  const status = one(`SELECT status FROM challenge_attempts WHERE id = ${Number(lastData.attempt_id)}`);
  check('reload', `tab ditutup pada modal hasil akhir (pengerjaan ${status}) → tanpa challenge_abandoned`,
    abandonedEvents(lastData.attempt_id) === 0, `${abandonedEvents(lastData.attempt_id)} event`);

  // Kontrol: menutup tab di tengah tantangan TETAP mencatat challenge_abandoned (pagehide)
  const ctl = await context.newPage();
  const ctlData = await openChallenge(ctl, 'wonosobo', 2);
  await ctl.close({ runBeforeUnload: true });
  await sleep(2000);
  check('reload', 'kontrol: tab ditutup → challenge_abandoned (pagehide) tetap tercatat', abandonedEvents(ctlData.attempt_id) === 1);
}

function wrongVerdicts(data) {
  return Object.fromEntries(data.items.map((item) => {
    const right = String(keyOf(item.id).verdict ?? '').toLowerCase();
    return [item.id, right === 'benar' ? 'salah' : 'benar'];
  }));
}

// ---------------------------------------------------------------- skenario 5

/**
 * Dua halaman menekan "Cek jawabanku" bersamaan: request ditahan sampai
 * keduanya tiba, lalu dilepas serentak. $rewrite (opsional) mengubah body
 * request (mis. menambah `check_no` seperti klien yang sudah diperbaiki).
 */
async function concurrentCheck(pages, verdicts, rewrite = null) {
  const release = gate();
  const arrivals = [];
  const answered = [];
  for (const p of pages) {
    const arrived = gate();
    const done = gate();
    arrivals.push(arrived.promise);
    answered.push(done.promise);
    let first = true;
    await p.route('**/api/attempts/*/check', async (route) => {
      if (!first) return route.continue();
      first = false;
      arrived.open();
      await release.promise;
      const body = route.request().postDataJSON();
      // Respons dibaca di sini: halaman yang kalah langsung memuat ulang
      const response = await route.fetch(rewrite ? { postData: JSON.stringify(await rewrite(p, body)) } : undefined);
      done.open(await response.json());
      await route.fulfill({ response });
    });
  }
  for (const p of pages) { await fillBoleh(p, verdicts); await p.click('#btn-check'); }
  await Promise.all(arrivals);
  release.open();
  const bodies = await Promise.all(answered);
  await sleep(500);
  for (const p of pages) await p.unroute('**/api/attempts/*/check');
  return bodies;
}

/** Masuk sebagai siswa yang sama di perangkat lain (konteks peramban sendiri = session PHP sendiri). */
async function login(context, username) {
  const page = await context.newPage();
  await page.goto(`${BASE}/masuk`);
  await page.fill('#username', username);
  await page.fill('#password', PASSWORD);
  await Promise.all([page.waitForURL((url) => !/\/masuk/.test(url.pathname)), page.click('form[action*="masuk"] button[type="submit"]')]);
  return page;
}

/** Keadaan database satu pengerjaan sesudah balapan. */
function attemptState(id) {
  const maxTry = Number(one(`SELECT IFNULL(MAX(try_no), 0) FROM item_tries WHERE challenge_attempt_id = ${id}`));
  const checks = sql(`SELECT JSON_EXTRACT(payload_json, '$.check_no'), COUNT(*) FROM game_event_logs WHERE event_type = 'challenge_checked' AND challenge_attempt_id = ${id} GROUP BY 1 ORDER BY 1`);
  const completed = Number(one(`SELECT COUNT(*) FROM game_event_logs WHERE event_type = 'challenge_completed' AND challenge_attempt_id = ${id}`));
  const row = sql(`SELECT status, check_count FROM challenge_attempts WHERE id = ${id}`)[0];
  return { maxTry, checks, completed, status: row?.[0], checkCount: Number(row?.[1]) };
}

const outcome = (r) => (r.success === false ? r.code : `ok cek ${r.data?.check_count}${r.data?.completed ? ' (ditutup)' : ''}`);

/**
 * Satu putaran: cek ke-1 bersamaan, cek ke-2 dari satu halaman, cek ke-3
 * bersamaan. $pages = dua halaman pada pengerjaan yang sama.
 */
async function raceRound(label, pages, rewrite = null) {
  const [a, b] = pages;
  const data = await openChallenge(a, 'temanggung', 3);
  await openChallenge(b, 'temanggung', 3);
  const wrong = wrongVerdicts(data);
  const id = Number(data.attempt_id);

  const first = await concurrentCheck([a, b], wrong, rewrite);
  await sleep(800);
  await openChallenge(a, 'temanggung', 3);
  const afterFirst = attemptState(id).checkCount;
  if (afterFirst < 2) {
    await fillBoleh(a, wrong);
    await a.click('#btn-check');
    await a.waitForSelector('.modal');
    await closeModal(a);
  }
  await openChallenge(a, 'temanggung', 3);
  await openChallenge(b, 'temanggung', 3);
  const state2 = attemptState(id);
  const third = state2.status === 'in_progress' ? await concurrentCheck([a, b], wrong, rewrite) : [];
  await sleep(800);
  return { id, first, third, state: attemptState(id) };
}

async function race(browser) {
  // 5a. Dua perangkat: dua session PHP, request benar-benar bersamaan di database
  const deviceA = await browser.newContext({ viewport: { width: 1280, height: 720 } });
  const { page: a, username } = await register(deviceA, 'balap');
  const deviceB = await browser.newContext({ viewport: { width: 1280, height: 720 } });
  const b = await login(deviceB, username);

  for (let round = 1; round <= ROUNDS; round++) {
    const { first, third, state } = await raceRound(`perangkat ${round}`, [a, b]);
    const won1 = first.filter((r) => r.success !== false);
    const lost1 = first.filter((r) => r.success === false);
    const won3 = third.filter((r) => r.success !== false);
    const lost3 = third.filter((r) => r.success === false);
    check('race', `dua perangkat, putaran ${round}: cek ke-1 bersamaan → satu berlaku, yang lain ATTEMPT_CHANGED`,
      won1.length === 1 && lost1[0]?.code === 'ATTEMPT_CHANGED', first.map(outcome).join(' | '));
    check('race', `dua perangkat, putaran ${round}: cek ke-3 bersamaan → satu menutup, yang lain TRIES_EXHAUSTED/ATTEMPT_CHANGED`,
      won3.length === 1 && won3[0].data?.completed === true && ['TRIES_EXHAUSTED', 'ATTEMPT_CHANGED'].includes(lost3[0]?.code), third.map(outcome).join(' | '));
    check('race', `dua perangkat, putaran ${round}: tanpa try_no 4, satu challenge_checked per cek, satu challenge_completed`,
      state.maxTry === 3 && state.checks.length === 3 && state.checks.every(([, n]) => n === '1') && state.completed === 1 && state.status === 'completed' && state.checkCount === 3,
      `max try_no ${state.maxTry}, cek ${JSON.stringify(state.checks)}, completed ${state.completed}, ${state.status}/${state.checkCount}`);
  }
  await deviceA.close();
  await deviceB.close();

  // 5b. Dua tab di peramban yang sama: satu session PHP, kunci session (GET_LOCK) membuat request berurutan
  const browserCtx = await browser.newContext({ viewport: { width: 1280, height: 720 } });
  const { page: tab1 } = await register(browserCtx, 'tab');
  const tab2 = await browserCtx.newPage();
  const plain = await raceRound('dua tab', [tab1, tab2]);
  check('race', 'dua tab: data tetap utuh (tanpa try_no 4, tanpa penutupan ganda)',
    plain.state.maxTry <= 3 && plain.state.completed <= 1 && plain.state.checks.every(([, n]) => n === '1'),
    `cek-1: ${plain.first.map(outcome).join(' | ')}; max try_no ${plain.state.maxTry}`);
  check('race', 'dua tab: cek dari tab lama ditolak, tidak dihitung sebagai cek baru (klien mengirim check_no)',
    plain.first.filter((r) => r.success === false).length === 1, plain.first.map(outcome).join(' | '));

  // check_no ditulis ulang di request dari tampilan tab itu sendiri: memastikan penolakan berasal dari server
  const withCheckNo = async (page, body) => {
    const data = await page.evaluate(() => JSON.parse(document.getElementById('challenge-data').textContent));
    return { ...body, check_no: (Number(data.tries_used) || 0) + 1 };
  };
  const patched = await raceRound('dua tab + check_no', [tab1, tab2], withCheckNo);
  check('race', 'dua tab + check_no: cek ke-1 → satu berlaku, tab lama ATTEMPT_CHANGED',
    patched.first.filter((r) => r.success !== false).length === 1 && patched.first.some((r) => r.code === 'ATTEMPT_CHANGED'), patched.first.map(outcome).join(' | '));
  check('race', 'dua tab + check_no: cek ke-3 → satu menutup, tab lama TRIES_EXHAUSTED/ATTEMPT_CHANGED',
    patched.third.filter((r) => r.success !== false).length === 1 && patched.third.some((r) => ['TRIES_EXHAUSTED', 'ATTEMPT_CHANGED'].includes(r.code)), patched.third.map(outcome).join(' | '));
  check('race', 'dua tab + check_no: tanpa try_no 4, satu penutupan',
    patched.state.maxTry === 3 && patched.state.completed === 1 && patched.state.checkCount === 3, JSON.stringify(patched.state));
  await browserCtx.close();
}

// ------------------------------------------------------------------- utama

async function main() {
  const driver = sql('SELECT VERSION()')[0][0];
  console.log(`GELITA QA PR #42 — ${BASE}, database ${driver}`);
  const studyId = one("SELECT id FROM research_studies WHERE status = 'active' ORDER BY id DESC LIMIT 1");
  const unlock = one(`SELECT unlock_mode FROM research_studies WHERE id = ${Number(studyId)}`);
  sql(`UPDATE research_studies SET unlock_mode = 'free' WHERE id = ${Number(studyId)}`);

  const { chromium } = await loadPlaywright();
  const browser = await chromium.launch();
  const want = (name) => !ONLY || ONLY.split(',').includes(name);

  try {
    if (want('freeze') || want('timing')) {
      console.log('\n1–2. Isian dibekukan selama "Cek jawabanku" · waktu isian');
      const context = await browser.newContext({ viewport: { width: 1280, height: 720 } });
      const { page } = await register(context, 'beku');
      const runs = [
        { label: 'Susun Gambar', ...(await freezePuzzle(page)) },
        { label: 'Isi Rumpang', ...(await freezeRumpang(page)) },
        { label: 'Benar atau Salah', ...(await freezeBoleh(page)) },
      ];
      timing(runs);
      await context.close();
    }

    if (want('cari')) {
      console.log('\n3. Cari Objek: lompat petunjuk selama ketukan menunggu server');
      const context = await browser.newContext({ viewport: { width: 1280, height: 720 } });
      const { page } = await register(context, 'cari');
      await cari(page);
      await context.close();
    }

    if (want('reload')) {
      console.log('\n4. Muat ulang karena galat aturan tanpa challenge_abandoned palsu');
      const context = await browser.newContext({ viewport: { width: 1280, height: 720 } });
      const { page } = await register(context, 'muat');
      await reload(context, page);
      await context.close();
    }

    if (want('race')) {
      console.log(`\n5. Dua tab/perangkat mengirim cek bersamaan (${ROUNDS} putaran, ${driver})`);
      await race(browser);
    }
  } finally {
    await browser.close();
    sql(`UPDATE research_studies SET unlock_mode = ${q(unlock)} WHERE id = ${Number(studyId)}`);
  }

  const failed = results.filter((r) => !r.ok);
  console.log(`\n${results.length - failed.length}/${results.length} pemeriksaan lolos.`);
  if (failed.length) {
    for (const f of failed) console.log(`  ✗ [${f.group}] ${f.name}${f.detail ? ` — ${f.detail}` : ''}`);
    process.exitCode = 1;
  }
}

main().catch((error) => {
  console.error(error);
  process.exitCode = 1;
});
