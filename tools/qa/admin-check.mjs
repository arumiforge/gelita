#!/usr/bin/env node
/**
 * Admin panel browser check (Dok 3 §12.4, re-run after the §11 audit fixes).
 *
 * Opens every admin page as an admin and the data pages as a teacher, at
 * 1280 × 800, 1440 × 900, and 1920 × 1080, and checks for each page:
 *
 *   - HTTP 200, no console errors, no uncaught page errors, no failed
 *     same-origin requests;
 *   - no horizontal page scroll (wide tables scroll inside .table-wrap);
 *   - a skip link to the main content.
 *
 * Then, once, the §11 audit fixes:
 *
 *   1. the Jawaban tab shows every try of the student (--student, default the
 *      one with the most tries), not only the first 25;
 *   2. teachers search the student list by code only (a name finds nothing);
 *   3. the list-table CSVs (Siswa, Sesi, timeline on the session page,
 *      Kritik & saran) each
 *      write a `list_csv` audit row;
 *   6. the quick search placeholder matches the role.
 *
 * Screenshots: writable/qa/admin/{role}-{page}-{size}.png.
 *
 * Setup: a test database (never production) with simulated students, e.g.
 *   php spark gelita:reset --ya && php spark gelita:simulasi --siswa=30
 * plus a teacher account whose school has simulated students.
 *
 * Usage:
 *   node tools/qa/admin-check.mjs --admin=admin --admin-pass=… --guru=… --guru-pass=…
 *        [--base=http://localhost:8080/] [--sizes=1280x800,1440x900,1920x1080]
 *        [--mysql="mysql -uroot gelita_uji"]
 *
 * --mysql is used to read ids and counts for the audit checks.
 * Exit code 0 when every check passes, 1 otherwise.
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
  return require(path.join(execSync('npm root -g').toString().trim(), 'playwright'));
}
const { chromium } = loadPlaywright();

const args = Object.fromEntries(process.argv.slice(2).map((arg) => {
  const [key, ...rest] = arg.replace(/^--/, '').split('=');
  return [key, rest.length ? rest.join('=') : true];
}));
const BASE = String(args.base || process.env.QA_BASE_URL || 'http://localhost:8080/').replace(/\/?$/, '/');
const MYSQL = String(args.mysql || process.env.QA_MYSQL || 'mysql -uroot gelita_uji');
const SIZES = String(args.sizes || '1280x800,1440x900,1920x1080').split(',')
  .map((s) => { const [w, h] = s.split('x').map(Number); return { w, h, name: `${w}x${h}` }; });
const OUT = path.join(root, 'writable/qa/admin');
fs.mkdirSync(OUT, { recursive: true });

const failures = [];
let passed = 0;
const check = (ok, label) => {
  if (ok) { passed++; return; }
  failures.push(label);
  console.log(`  ✗ ${label}`);
};

/** One value from the test database. */
const sql = (query) => execSync(`${MYSQL} -N -B -e ${JSON.stringify(query)}`).toString().trim();

const studentId = Number(args.student || sql(
  'SELECT gs.participant_id FROM item_tries it JOIN challenge_attempts ca ON ca.id = it.challenge_attempt_id '
  + 'JOIN game_sessions gs ON gs.id = ca.session_id GROUP BY gs.participant_id ORDER BY COUNT(*) DESC LIMIT 1',
));
const sessionId = Number(sql('SELECT MAX(id) FROM game_sessions'));
const levelId = Number(sql('SELECT MIN(id) FROM levels'));
const nodeId = Number(sql('SELECT MIN(id) FROM challenge_nodes'));
const passageId = Number(sql('SELECT COALESCE(MIN(id), 1) FROM reading_passages'));
const guruSchool = args.guru ? Number(sql(`SELECT school_id FROM staff_users WHERE username = '${String(args.guru).replace(/'/g, '')}'`)) : 0;
const guruStudent = guruSchool ? Number(sql(`SELECT MIN(id) FROM participants WHERE school_id = ${guruSchool}`)) : 0;
const guruSession = guruStudent ? Number(sql(`SELECT MAX(id) FROM game_sessions WHERE participant_id = ${guruStudent}`)) : 0;

const STUDENT_TABS = ['profil', 'sesi', 'progres', 'pengerjaan', 'jawaban', 'navigasi', 'bantuan', 'masukan'];
const DIMENSIONS = ['jawaban', 'percobaan', 'pengerjaan', 'progres', 'selesai', 'navigasi', 'bantuan', 'perangkat'];

/** Pages both roles may open (teacher scope applies). */
const dataPages = (student, session) => [
  ['ringkasan', 'admin'],
  ['siswa', 'admin/peserta'],
  ...STUDENT_TABS.map((tab) => [`siswa-${tab}`, `admin/peserta/${student}?tab=${tab}`]),
  ['sesi', 'admin/sesi'],
  ['sesi-detail', `admin/sesi/${session}`],
  ['sesi-linimasa', `admin/sesi/${session}/event`], // legacy address, redirects to the session page
  ['masukan', 'admin/masukan'],
  ...DIMENSIONS.map((d) => [`data-${d}`, `admin/data/${d}`]),
  ['ekspor', 'admin/ekspor'],
  ['sandi', 'admin/akun/sandi'],
];
const adminPages = [
  ...dataPages(studentId, sessionId),
  ['konten', 'admin/konten'],
  ['konten-wilayah', `admin/konten/level/${levelId}`],
  ['konten-tantangan', `admin/konten/node/${nodeId}`],
  ['konten-impor', 'admin/konten/impor-bank'],
  ['konten-bacaan', `admin/konten/bacaan/${passageId}`],
  ['konten-pustaka', `admin/konten/pustaka/${levelId}`],
  ['konten-dialog', `admin/konten/dialog/${levelId}`],
  ['narasi', 'admin/konten/narasi'],
  ['narasi-unggah', 'admin/konten/narasi/unggah'],
  ['media', 'admin/media'],
  ['media-kelengkapan', 'admin/media/kelengkapan'],
  ['media-audio', 'admin/media/audio'],
  ['studi', 'admin/studi'],
  ['rilis', 'admin/studi/rilis'],
  ['rilis-paket', 'admin/studi/rilis/paket'],
  ['tata-kelola', 'admin/tata-kelola'],
  ['audit', 'admin/tata-kelola/audit'],
  ['sekolah', 'admin/sekolah'],
  ['staf', 'admin/staf'],
];

const browser = await chromium.launch();

async function login(username, password) {
  const ctx = await browser.newContext({ viewport: { width: 1280, height: 800 }, acceptDownloads: true });
  const page = await ctx.newPage();
  await page.goto(`${BASE}admin/login`);
  await page.fill('#username', username);
  await page.fill('#password', password);
  await Promise.all([page.waitForNavigation({ waitUntil: 'domcontentloaded' }), page.click('form button[type=submit]')]);
  check(!page.url().includes('/login'), `${username}: login`);
  return { ctx, page };
}

async function visitAll(role, page, pages) {
  const consoleErrors = [];
  const badRequests = [];
  page.on('console', (m) => { if (m.type() === 'error') consoleErrors.push(m.text()); });
  page.on('pageerror', (e) => consoleErrors.push(String(e)));
  page.on('response', (r) => {
    if (r.url().startsWith(BASE) && r.status() >= 400 && !r.url().endsWith('favicon.ico')) badRequests.push(`${r.status()} ${r.url()}`);
  });

  for (const size of SIZES) {
    await page.setViewportSize({ width: size.w, height: size.h });
    console.log(`== ${role} ${size.name}`);
    for (const [name, url] of pages) {
      consoleErrors.length = 0;
      badRequests.length = 0;
      const response = await page.goto(BASE + url, { waitUntil: 'networkidle' });
      const label = `${role} ${name} @ ${size.name}`;
      check(response?.status() === 200, `${label}: HTTP ${response?.status()}`);
      const layout = await page.evaluate(() => {
        const se = document.scrollingElement;
        return {
          hscroll: se.scrollWidth > se.clientWidth + 1,
          widths: `${se.scrollWidth} > ${se.clientWidth}`,
          skip: !!document.querySelector('a.skip-link[href^="#"]'),
        };
      });
      check(!layout.hscroll, `${label}: horizontal page scroll (${layout.widths})`);
      check(layout.skip, `${label}: skip link`);
      check(consoleErrors.length === 0, `${label}: console errors ${consoleErrors.join(' | ')}`);
      check(badRequests.length === 0, `${label}: failed requests ${badRequests.join(' | ')}`);
      await page.screenshot({ path: path.join(OUT, `${role}-${name}-${size.name}.png`), fullPage: true });
    }
  }
}

/** Item 1: every try of the student is listed under Jawaban, across all pages. */
async function checkTryHistory(page) {
  const expected = Number(sql(
    'SELECT COUNT(*) FROM item_tries it JOIN challenge_attempts ca ON ca.id = it.challenge_attempt_id '
    + `JOIN game_sessions gs ON gs.id = ca.session_id WHERE gs.participant_id = ${studentId}`,
  ));
  let shown = 0;
  let pages = 1;
  for (let p = 1; p <= pages; p++) {
    await page.goto(`${BASE}admin/peserta/${studentId}?tab=jawaban&per_page=100&page=${p}`);
    const { rows, total } = await page.evaluate(() => ({
      rows: document.querySelectorAll('tr.row-expand table tbody tr').length,
      // First card is the answers table; its count reads "1.234 baris".
      total: Number((document.querySelector('[data-table-card] .table-count')?.textContent || '0').replace(/\D/g, '')),
    }));
    shown += rows;
    pages = Math.ceil(total / 100);
  }
  console.log(`  item 1: student ${studentId} has ${expected} tries, Jawaban lists ${shown}`);
  check(expected > 25 && shown === expected, `item 1: Jawaban lists ${shown} of ${expected} tries`);
}

/** Item 2 and 6: teacher search is by code only, placeholders match the role. */
async function checkSearch(adminPage, guruPage) {
  const name = sql(`SELECT display_name FROM participants WHERE id = ${guruStudent}`);
  const code = sql(`SELECT participant_code FROM participants WHERE id = ${guruStudent}`);
  const count = async (page, q) => {
    await page.goto(`${BASE}admin/peserta?q=${encodeURIComponent(q)}`);
    return page.$$eval('[data-table-card] table > tbody > tr', (r) => r.length);
  };
  check(await count(guruPage, name) === 0, 'item 2: teacher search by name finds nothing');
  check(await count(guruPage, code) === 1, 'item 2: teacher search by code finds the student');
  check(await count(adminPage, name) >= 1, 'item 2: admin search by name finds the student');
  const placeholder = (page) => page.getAttribute('#topbar-q', 'placeholder');
  check(/kode siswa/i.test(await placeholder(guruPage)) && !/nama/i.test(await placeholder(guruPage)), 'item 6: teacher quick search mentions code only');
  check(/nama/i.test(await placeholder(adminPage)), 'item 6: admin quick search mentions name');
}

/** Item 3: each list-table CSV writes one list_csv audit row. */
async function checkListCsv(page) {
  const before = Number(sql("SELECT COUNT(*) FROM audit_logs WHERE action = 'list_csv'"));
  const lists = ['admin/peserta', 'admin/sesi', `admin/sesi/${sessionId}`, 'admin/masukan'];
  for (const url of lists) {
    const response = await page.request.get(`${BASE}${url}${url.includes('?') ? '&' : '?'}format=csv`);
    check(response.status() === 200 && /text\/csv/.test(response.headers()['content-type'] || ''), `item 3: ${url} CSV download`);
  }
  const after = Number(sql("SELECT COUNT(*) FROM audit_logs WHERE action = 'list_csv'"));
  check(after - before === lists.length, `item 3: ${after - before} list_csv audit rows for ${lists.length} CSVs`);
}

const admin = await login(String(args.admin || 'admin'), String(args['admin-pass'] || ''));
await visitAll('admin', admin.page, adminPages);
await checkTryHistory(admin.page);
await checkListCsv(admin.page);

if (args.guru) {
  const guru = await login(String(args.guru), String(args['guru-pass'] || args['admin-pass'] || ''));
  await visitAll('guru', guru.page, dataPages(guruStudent, guruSession));
  const denied = await guru.page.goto(`${BASE}admin/konten`);
  check(denied?.status() !== 200 || guru.page.url() !== `${BASE}admin/konten`, 'guru: content pages are admin only');
  await checkSearch(admin.page, guru.page);
}

await browser.close();
console.log(`\n${passed} checks pass, ${failures.length} fail`);
if (failures.length) console.log(failures.map((f) => `  - ${f}`).join('\n'));
process.exit(failures.length ? 1 : 0);
