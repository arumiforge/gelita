#!/usr/bin/env node
/**
 * Staff panel browser check (Dok 3 §12.4; staff roles from research revision
 * 10, part 1: docs/revisi-10-analitik/27_IMPLEMENTASI_PERAN_PENELITI.md §4, §8,
 * §9, §14.2).
 *
 * Signs in as each staff role and opens the pages that role may open, at
 * 1280 × 800, 1440 × 900, and 1920 × 1080:
 *
 *   - admin: accounts (student list and student page without research tabs),
 *     content, settings (study read-only, releases, governance, schools, staff);
 *   - peneliti: Ringkasan, student list and student page with the research
 *     tabs, reset password by code, content read-only;
 *   - ketua: everything peneliti opens, plus play sessions, raw data,
 *     feedback, export, and the study page.
 *
 * For each page it checks:
 *
 *   - HTTP 200, no console errors, no uncaught page errors, no failed
 *     same-origin requests;
 *   - no horizontal page scroll (wide tables scroll inside .table-wrap);
 *   - a skip link to the main content;
 *   - for peneliti and ketua: no student name, username, school name, or
 *     NPSN anywhere on the page (27 §9).
 *
 * Pages a role may not open must answer 404 (27 §4):
 *
 *   - admin: sessions, raw data, feedback, export, the student-list CSV (K17);
 *   - peneliti: sessions, raw data, feedback, export, study, bank import,
 *     releases, governance, schools, staff;
 *   - ketua: bank import, releases, governance, schools, staff.
 *
 * Then, once:
 *
 *   1. the Jawaban tab shows every try of the student (--student, default the
 *      one with the most tries), not only the first 25 (as peneliti, else ketua);
 *   2. peneliti and ketua search the student list by code only (name and
 *      username find nothing, the code finds the student); admin finds by name;
 *   3. the list-table CSVs (Siswa, Sesi, timeline on the session page,
 *      Kritik & saran) each write a `list_csv` audit row (as ketua);
 *   4. the admin student list renders no CSV button;
 *   6. the quick search placeholder matches the role (code only for
 *      peneliti and ketua, name for admin).
 *
 * Screenshots: writable/qa/admin/{role}-{page}-{size}.png.
 *
 * Setup: a test database (never production) with simulated students, e.g.
 *   php spark gelita:reset --ya && php spark gelita:simulasi --siswa=30
 * plus one `peneliti` and one `ketua` staff account.
 *
 * Usage:
 *   node tools/qa/admin-check.mjs --admin=admin --admin-pass=…
 *        [--peneliti=… --peneliti-pass=…] [--ketua=… --ketua-pass=…]
 *        [--student=<participant id>]
 *        [--base=http://localhost:8080/] [--sizes=1280x800,1440x900,1920x1080]
 *        [--mysql="mysql -uroot gelita_uji"]
 *
 * --peneliti / --ketua: usernames of the researcher and head researcher
 * accounts; each role's part is skipped when its option is missing. A missing
 * --peneliti-pass or --ketua-pass falls back to --admin-pass.
 * --mysql is used to read ids, counts, and the identities to look for.
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

/**
 * Identity values researchers must never see (27 §9): name, username, school
 * name, and NPSN (schools.code) of the chosen student and the 30 newest
 * students. Very short values are skipped to avoid matching ordinary words.
 */
const identities = [...new Set(sql(
  'SELECT p.display_name, p.username, COALESCE(s.name, p.school_name_snapshot), s.code '
  + 'FROM participants p LEFT JOIN schools s ON s.id = p.school_id '
  + `WHERE p.id = ${studentId} OR p.id IN (SELECT id FROM (SELECT id FROM participants ORDER BY id DESC LIMIT 30) t)`,
).split('\n').flatMap((line) => line.split('\t')))]
  .map((v) => v.trim())
  .filter((v) => v.length >= 4 && v !== 'NULL');

const STUDENT_TABS = ['profil', 'sesi', 'progres', 'pengerjaan', 'jawaban', 'navigasi', 'bantuan', 'masukan'];
const DIMENSIONS = ['jawaban', 'percobaan', 'pengerjaan', 'progres', 'selesai', 'navigasi', 'bantuan', 'perangkat'];

/** Every staff role. */
const commonPages = [
  ['ringkasan', 'admin'],
  ['siswa', 'admin/peserta'],
  ['sandi', 'admin/akun/sandi'],
];
/** content.view: admin, peneliti, ketua (GET only, read-only for researchers). */
const contentViewPages = [
  ['konten', 'admin/konten'],
  ['konten-wilayah', `admin/konten/level/${levelId}`],
  ['konten-tantangan', `admin/konten/node/${nodeId}`],
  ['konten-bacaan', `admin/konten/bacaan/${passageId}`],
  ['konten-pustaka', `admin/konten/pustaka/${levelId}`],
  ['konten-dialog', `admin/konten/dialog/${levelId}`],
  ['narasi', 'admin/konten/narasi'],
  ['media', 'admin/media'],
  ['media-kelengkapan', 'admin/media/kelengkapan'],
  ['media-audio', 'admin/media/audio'],
];
/** content.edit: admin only. */
const contentEditPages = [
  ['konten-impor', 'admin/konten/impor-bank'],
];
/** releases, governance, schools, staff: admin only. */
const settingsPages = [
  ['rilis', 'admin/studi/rilis'],
  ['rilis-paket', 'admin/studi/rilis/paket'],
  ['tata-kelola', 'admin/tata-kelola'],
  ['audit', 'admin/tata-kelola/audit'],
  ['sekolah', 'admin/sekolah'],
  ['staf', 'admin/staf'],
];
/** participants.research + participants.reset_by_code: peneliti, ketua. */
const researchStudentPages = [
  ...STUDENT_TABS.map((tab) => [`siswa-${tab}`, `admin/peserta/${studentId}?tab=${tab}`]),
  ['reset-sandi', 'admin/peserta/reset-sandi'],
];
/** research.browse + research.export: ketua only. */
const researchDataPages = [
  ['sesi', 'admin/sesi'],
  ['sesi-detail', `admin/sesi/${sessionId}`],
  ['sesi-linimasa', `admin/sesi/${sessionId}/event`], // legacy address, redirects to the session page
  ['masukan', 'admin/masukan'],
  ...DIMENSIONS.map((d) => [`data-${d}`, `admin/data/${d}`]),
  ['ekspor', 'admin/ekspor'],
];
/** studies.view: admin (read-only), ketua. */
const studyPages = [['studi', 'admin/studi']];

const ROLES = {
  admin: {
    open: [
      ...commonPages,
      ['siswa-akun', `admin/peserta/${studentId}`],
      ...contentViewPages, ...contentEditPages,
      ['narasi-unggah', 'admin/konten/narasi/unggah'],
      ...studyPages, ...settingsPages,
    ],
    denied: [
      ...researchDataPages,
      ['siswa-csv', 'admin/peserta?format=csv'], // K17
      ['reset-sandi', 'admin/peserta/reset-sandi'],
    ],
  },
  peneliti: {
    open: [...commonPages, ...researchStudentPages, ...contentViewPages],
    denied: [...researchDataPages, ...studyPages, ...contentEditPages, ...settingsPages],
  },
  ketua: {
    open: [...commonPages, ...researchStudentPages, ...contentViewPages, ...researchDataPages, ...studyPages],
    denied: [...contentEditPages, ...settingsPages],
  },
};
const RESEARCH_ROLES = ['peneliti', 'ketua'];

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

/** Identity values found in the page's text or attributes (inputs, titles, links). */
async function leakedIdentities(page) {
  const html = await page.content();
  const text = await page.evaluate(() => document.body.innerText);
  const haystack = `${text}\n${html}`.toLowerCase();
  return identities.filter((v) => haystack.includes(v.toLowerCase()));
}

async function visitAll(role, page, pages) {
  const consoleErrors = [];
  const badRequests = [];
  page.on('console', (m) => { if (m.type() === 'error') consoleErrors.push(m.text()); });
  page.on('pageerror', (e) => consoleErrors.push(String(e)));
  page.on('response', (r) => {
    if (r.url().startsWith(BASE) && r.status() >= 400 && !r.url().endsWith('favicon.ico')) badRequests.push(`${r.status()} ${r.url()}`);
  });
  const research = RESEARCH_ROLES.includes(role);

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
      if (research && size === SIZES[0]) {
        const leaks = await leakedIdentities(page);
        check(leaks.length === 0, `${label}: shows student identity ${leaks.join(', ')}`);
      }
      await page.screenshot({ path: path.join(OUT, `${role}-${name}-${size.name}.png`), fullPage: true });
    }
  }
  page.removeAllListeners('console');
  page.removeAllListeners('pageerror');
  page.removeAllListeners('response');
}

/** Pages the role may not open answer 404 (27 §4). */
async function checkDenied(role, page, pages) {
  for (const [name, url] of pages) {
    const response = await page.request.get(BASE + url, { maxRedirects: 0 });
    check(response.status() === 404, `${role} ${name}: expected 404, got ${response.status()}`);
  }
}

/** Item 1: every try of the student is listed under Jawaban, across all pages. */
async function checkTryHistory(role, page) {
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
  console.log(`  item 1 (${role}): student ${studentId} has ${expected} tries, Jawaban lists ${shown}`);
  check(expected > 25 && shown === expected, `item 1 (${role}): Jawaban lists ${shown} of ${expected} tries`);
}

const studentCount = async (page, q) => {
  await page.goto(`${BASE}admin/peserta?q=${encodeURIComponent(q)}`);
  return page.$$eval('[data-table-card] table > tbody > tr', (r) => r.length);
};
const placeholder = (page) => page.getAttribute('#topbar-q', 'placeholder');

/** Items 2 and 6 for a research role: search by code only, placeholder says code only. */
async function checkResearchSearch(role, page) {
  const [name, username, code] = sql(
    `SELECT display_name, username, participant_code FROM participants WHERE id = ${studentId}`,
  ).split('\t');
  check(await studentCount(page, name) === 0, `item 2: ${role} search by name finds nothing`);
  check(await studentCount(page, username) === 0, `item 2: ${role} search by username finds nothing`);
  check(await studentCount(page, code) === 1, `item 2: ${role} search by code finds the student`);
  const text = String(await placeholder(page) || '');
  check(/kode/i.test(text) && !/nama/i.test(text), `item 6: ${role} quick search mentions code only ("${text}")`);
}

/** Items 2, 4, and 6 for admin: name search works, no CSV button, no research tabs. */
async function checkAdminStudents(page) {
  const name = sql(`SELECT display_name FROM participants WHERE id = ${studentId}`);
  check(await studentCount(page, name) >= 1, 'item 2: admin search by name finds the student');
  check(/nama/i.test(String(await placeholder(page) || '')), 'item 6: admin quick search mentions name');
  await page.goto(`${BASE}admin/peserta`);
  check(await page.locator('a[href*="format=csv"]').count() === 0, 'item 4: admin student list has no CSV button');
  await page.goto(`${BASE}admin/peserta/${studentId}`);
  const researchTabs = await page.locator(STUDENT_TABS.filter((t) => t !== 'profil').map((t) => `a[href*="tab=${t}"]`).join(', ')).count();
  check(researchTabs === 0, `admin student page has no research tabs (${researchTabs} found)`);
}

/** Item 3: each list-table CSV writes one list_csv audit row. */
async function checkListCsv(role, page) {
  const before = Number(sql("SELECT COUNT(*) FROM audit_logs WHERE action = 'list_csv'"));
  const lists = ['admin/peserta', 'admin/sesi', `admin/sesi/${sessionId}`, 'admin/masukan'];
  for (const url of lists) {
    const response = await page.request.get(`${BASE}${url}${url.includes('?') ? '&' : '?'}format=csv`);
    check(response.status() === 200 && /text\/csv/.test(response.headers()['content-type'] || ''), `item 3 (${role}): ${url} CSV download`);
    const body = (await response.text()).toLowerCase();
    const leaks = identities.filter((v) => body.includes(v.toLowerCase()));
    check(leaks.length === 0, `item 3 (${role}): ${url} CSV shows student identity ${leaks.join(', ')}`);
  }
  const after = Number(sql("SELECT COUNT(*) FROM audit_logs WHERE action = 'list_csv'"));
  check(after - before === lists.length, `item 3 (${role}): ${after - before} list_csv audit rows for ${lists.length} CSVs`);
}

const admin = await login(String(args.admin || 'admin'), String(args['admin-pass'] || ''));
await visitAll('admin', admin.page, ROLES.admin.open);
await checkDenied('admin', admin.page, ROLES.admin.denied);
await checkAdminStudents(admin.page);

const research = {};
for (const role of RESEARCH_ROLES) {
  if (!args[role]) { console.log(`== ${role}: skipped (no --${role})`); continue; }
  const session = await login(String(args[role]), String(args[`${role}-pass`] || args['admin-pass'] || ''));
  research[role] = session;
  await visitAll(role, session.page, ROLES[role].open);
  await checkDenied(role, session.page, ROLES[role].denied);
  await checkResearchSearch(role, session.page);
}

const historyRole = research.peneliti ? 'peneliti' : (research.ketua ? 'ketua' : null);
if (historyRole) await checkTryHistory(historyRole, research[historyRole].page);
if (research.ketua) await checkListCsv('ketua', research.ketua.page);

await browser.close();
console.log(`\n${passed} checks pass, ${failures.length} fail`);
if (failures.length) console.log(failures.map((f) => `  - ${f}`).join('\n'));
process.exit(failures.length ? 1 : 0);
