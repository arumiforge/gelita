/**
 * Smoke test of research data collection (owner, 20 November 2026), with
 * real Chromium through the screens staff and students use.
 *
 * Two modes:
 *
 *   QA_MODE=local (default): a development install on MariaDB
 *   (tools/dev/serve.sh) with `gelita.researchPrivacy = true` in .env. The
 *   script WRITES data and moves timestamps back, so it refuses any base URL
 *   that is not localhost/127.0.0.1. Run it on a fresh database
 *   (php spark gelita:reset, then gelita:instrumen:impor if needed).
 *     1. ketua ticks research mode on /admin/studi;
 *     2. peneliti opens P1 for two schools (S01-A, S02-B);
 *     3. three students register with the session tokens and answer the
 *        sound check; peneliti fills the seats and starts the pre-test;
 *        one student goes through the test module;
 *     4. P1 closes (game sessions paused), P2 opens; a student opens
 *        Temanggung challenge 1, sends one wrong check and stays unfinished;
 *     5. P2 closes, the attempt is moved two days back, the nightly
 *        retention runs: the attempt must stay open;
 *     6. peneliti opens a make-up session (P2-S); the student continues the
 *        SAME attempt there and completes it;
 *     7. a student of the second school leaves an attempt unfinished; ketua
 *        downloads the XLSX (sheets with rows) and closes the study: the open
 *        attempt is abandoned with reason study_closed and /masuk says the
 *        research has ended.
 *
 *   QA_MODE=live: an installed server (e.g. QA_BASE_URL=https://gelita.id).
 *   Public pages must answer; with GELITA_UJI_USER / GELITA_UJI_PASS (a
 *   `uji.*` test account, set as environment variables, never typed in chat)
 *   the student signs in, opens the map and one mission. No database access,
 *   no answers sent. With GELITA_UJI_STAFF_USER / GELITA_UJI_STAFF_PASS a
 *   staff account signs in and opens Ringkasan.
 *
 *   node tools/qa/field-smoke.mjs
 *   QA_MODE=live QA_BASE_URL=https://gelita.id node tools/qa/field-smoke.mjs
 *
 * Environment (local mode):
 *   QA_BASE_URL   app URL (default http://localhost:8080)
 *   QA_MYSQL      database client command (default "sudo mariadb gelita")
 *   QA_SHOTS      folder for screenshots (default: none)
 *
 * The script creates staff accounts qa.ketua and qa.peneliti (password
 * Qa#Gelita2026) when they are missing. Exits with code 1 when a check fails.
 * Never run local mode against production.
 */
import { execFileSync } from 'node:child_process';
import { createRequire } from 'node:module';

const MODE = process.env.QA_MODE || 'local';
const BASE = (process.env.QA_BASE_URL || 'http://localhost:8080').replace(/\/$/, '');
const MYSQL = (process.env.QA_MYSQL || 'sudo mariadb gelita').split(/\s+/);
const SHOTS = process.env.QA_SHOTS || null;
const STAFF_PASSWORD = 'Qa#Gelita2026';
const STUDENT_PASSWORD = 'Lentera#Kedu7';

const require = createRequire(import.meta.url);
let chromium;
try {
  ({ chromium } = require('playwright'));
} catch {
  ({ chromium } = require(`${execFileSync('npm', ['root', '-g']).toString().trim()}/playwright`));
}

let failures = 0;
function check(ok, label, detail = '') {
  console.log(`${ok ? 'OK  ' : 'FAIL'} ${label}${detail ? ` (${detail})` : ''}`);
  if (!ok) failures++;
}

const sql = (query) => execFileSync(MYSQL[0], [...MYSQL.slice(1), '-N', '-B', '-e', query]).toString().trim();
const one = (query) => sql(query).split('\n')[0] ?? '';

// ------------------------------------------------------------------ browser helpers

let browser;

async function newPage(storageState) {
  const context = await browser.newContext({ viewport: { width: 1366, height: 768 }, acceptDownloads: true, ...(storageState ? { storageState } : {}) });
  return context.newPage();
}

/** Submits the form that holds `selector` the way pressing Enter would (the debug toolbar has forms too). */
async function submitFormOf(page, selector) {
  await Promise.all([page.waitForNavigation({ timeout: 20_000 }), page.locator(selector).first().evaluate((el) => el.form.requestSubmit())]);
}

async function shot(page, name) {
  if (SHOTS) await page.screenshot({ path: `${SHOTS}/${name}.png`, fullPage: true });
}

async function staffLogin(username) {
  const page = await newPage();
  await page.goto(`${BASE}/admin/login`);
  await page.fill('#username', username);
  await page.fill('#password', STAFF_PASSWORD);
  await submitFormOf(page, '#password');
  return page;
}

async function flash(page) {
  return (await page.locator('.alert-ok, .alert-error').allInnerTexts()).map((s) => s.replace(/\s+/g, ' ').slice(0, 160));
}

/** Today in WIB, and an opening time a few minutes ago; closes at 23:59 (the script must not run around midnight). */
function sessionTimes() {
  const now = new Date(Date.now() + 7 * 3600e3);
  return { date: now.toISOString().slice(0, 10), opens: new Date(+now - 10 * 60e3).toISOString().slice(11, 16), closes: '23:59' };
}

async function createFieldSession(peneliti, schoolId, rombel, meeting) {
  const { date, opens, closes } = sessionTimes();
  await peneliti.goto(`${BASE}/admin/sesi-lapangan/baru`);
  await peneliti.selectOption('#fs-school', String(schoolId));
  await peneliti.fill('#fs-rombel', rombel);
  await peneliti.selectOption('#fs-meeting', String(meeting));
  await peneliti.fill('#fs-date', date);
  await peneliti.fill('#fs-opens', opens);
  await peneliti.fill('#fs-closes', closes);
  await submitFormOf(peneliti, '#fs-closes');
  const id = Number(peneliti.url().match(/sesi-lapangan\/(\d+)$/)?.[1] || 0);
  const token = id ? one(`SELECT token FROM field_session_tokens WHERE field_session_id = ${id} ORDER BY id DESC LIMIT 1`) : '';
  return { id, token };
}

async function closeFieldSession(peneliti, id) {
  await peneliti.goto(`${BASE}/admin/sesi-lapangan/${id}`);
  // The panel's confirm modal stands in front of the form; form.submit() is the confirmed click
  await Promise.all([peneliti.waitForNavigation(), peneliti.locator(`form[action$="/${id}/tutup"]`).evaluate((f) => f.submit())]);
}

async function soundCheck(page) {
  if (!page.url().endsWith('/cek-suara')) return;
  await page.getByText('Putar suara', { exact: true }).click();
  await page.waitForTimeout(4000);
  await Promise.all([page.waitForNavigation(), page.getByText('Ya, terdengar', { exact: true }).click()]);
}

async function register(token, username, name) {
  const page = await newPage();
  await page.goto(`${BASE}/daftar`);
  await page.fill('#token', token);
  await submitFormOf(page, '#token');
  await page.fill('input[name=display_name]', name);
  await page.fill('input[name=age]', '10');
  await page.locator('input[name=gender]').first().check();
  await submitFormOf(page, 'input[name=age]');
  await page.fill('#username', username);
  await page.fill('input[name=password]', STUDENT_PASSWORD);
  await page.fill('input[name=password_confirm]', STUDENT_PASSWORD);
  await page.locator('input[name=assent]').check();
  await submitFormOf(page, '#username');
  await soundCheck(page);
  return page;
}

async function studentLogin(username, password, token = null) {
  const page = await newPage();
  await page.goto(`${BASE}/masuk`);
  await page.fill('#username', username);
  await page.fill('#password', password);
  await Promise.all([page.waitForURL((u) => !u.pathname.endsWith('/masuk'), { timeout: 20_000 }), page.locator('#password').evaluate((el) => el.form.requestSubmit())]);
  if (page.url().endsWith('/token') && token) {
    await page.fill('#token', token);
    await submitFormOf(page, '#token');
  }
  await soundCheck(page);
  return page;
}

/** Goes through the instrument module, taking the first choice of every question. */
async function answerTests(page) {
  await page.goto(`${BASE}/tes`);
  for (let i = 0; i < 300 && /\/(tes|refleksi)/.test(page.url()); i++) {
    const names = await page.locator('main input[type=radio]:visible').evaluateAll((rs) => [...new Set(rs.map((r) => r.name))]);
    for (const name of names) await page.locator(`main input[type=radio][name="${name}"]:visible`).first().check();
    for (const text of await page.locator('main textarea:visible, main input[type=text]:visible').all()) await text.fill('jawaban uji');
    const button = page.locator('main form[method=post] button[type=submit]:visible, main a.btn-primary:visible').last();
    if (!(await button.count())) break;
    await Promise.all([page.waitForLoadState('load'), button.click()]);
    await page.waitForTimeout(200);
  }
}

/** Walks to a challenge the way a student does and returns its page data. */
async function openChallenge(page, region, seq) {
  for (const path of [`/wilayah/${region}`, `/dialog/${region}`, `/misi/${region}/${seq}`, `/tantangan/${region}/${seq}`]) {
    await page.goto(BASE + path);
  }
  const raw = (await page.locator('#challenge-data').count()) ? await page.locator('#challenge-data').textContent() : null;
  return raw ? JSON.parse(raw) : null;
}

/** One check through the game API, as the challenge frame sends it. */
async function sendCheck(page, data, answer) {
  return page.evaluate(async ({ data, answer }) => {
    const cfg = JSON.parse(document.getElementById('app-config').textContent);
    const base = (cfg.baseUrl || '/').replace(/\/?$/, '/');
    const res = await fetch(`${base}api/attempts/${data.attempt_id}/check`, {
      method: 'POST',
      headers: { 'content-type': 'application/json', 'X-CSRF-TOKEN': cfg.csrfHash, 'X-Requested-With': 'XMLHttpRequest' },
      body: JSON.stringify({ answers: data.items.map((item) => ({ item_id: item.id, answer })), check_no: (data.check_count || 0) + 1, client_event_id: crypto.randomUUID(), occurred_at: new Date().toISOString() }),
    });
    return res.status;
  }, { data, answer });
}

// ------------------------------------------------------------------ local mode

async function localMode() {
  if (!/^https?:\/\/(localhost|127\.0\.0\.1)(:\d+)?$/.test(BASE)) {
    console.error('Local mode writes data and moves time back: it only runs against localhost.');
    process.exit(2);
  }

  // Staff accounts and two research schools
  const hash = execFileSync('php', ['-r', `echo password_hash('${STAFF_PASSWORD}', PASSWORD_DEFAULT);`]).toString();
  for (const [user, role] of [['qa.ketua', 'ketua'], ['qa.peneliti', 'peneliti']]) {
    sql(`INSERT INTO staff_users (username, display_name, password_hash, role, is_active, created_at) VALUES ('${user}', 'QA ${role}', '${hash}', '${role}', 1, NOW()) ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), is_active = 1`);
  }
  const schools = sql('SELECT id FROM schools ORDER BY id LIMIT 2').split('\n').map(Number);
  sql(`UPDATE schools SET research_code = 'S01' WHERE id = ${schools[0]}`);
  sql(`UPDATE schools SET research_code = 'S02' WHERE id = ${schools[1]}`);
  check(one('SELECT COUNT(*) FROM participants') === '0', 'fresh database (no students yet)');

  // 1. Research mode through the panel
  const ketua = await staffLogin('qa.ketua');
  await ketua.goto(`${BASE}/admin/studi`);
  const studyId = one("SELECT id FROM research_studies WHERE status = 'active' ORDER BY id DESC LIMIT 1");
  const form = ketua.locator(`form[action$="/admin/studi/${studyId}"]`);
  await form.locator('input[name=field_mode]').check();
  if (await form.locator('input[name=confirm_not_ready]').count()) await form.locator('input[name=confirm_not_ready]').check();
  await submitFormOf(ketua, `form[action$="/admin/studi/${studyId}"] input[name=field_mode]`);
  check(one(`SELECT field_mode FROM research_studies WHERE id = ${studyId}`) === '1', '1. research mode saved from the panel', (await flash(ketua)).join(' | '));
  await shot(ketua, 'smoke-1-studi');

  // 2. P1 for two schools
  const peneliti = await staffLogin('qa.peneliti');
  const p1a = await createFieldSession(peneliti, schools[0], 'A', 1);
  const p1b = await createFieldSession(peneliti, schools[1], 'B', 1);
  check(p1a.token !== '' && p1b.token !== '', '2. P1 field sessions with tokens for two schools', `${p1a.token} ${p1b.token}`);

  // 3. Registration, seats, pre-test
  const sekar = await register(p1a.token, 'qa_sekar', 'Sekar');
  await register(p1a.token, 'qa_bayu', 'Bayu');
  const dewiFirst = await register(p1b.token, 'qa_dewi', 'Dewi');
  check(one("SELECT participant_code FROM participants WHERE username = 'qa_sekar'") === 'S01-A01', '3. research code from the token', one("SELECT participant_code FROM participants WHERE username = 'qa_sekar'"));
  check(one("SELECT COUNT(*) FROM participant_consents") === '3', '3. one consent row per student');
  for (const fs of [p1a, p1b]) {
    await peneliti.goto(`${BASE}/admin/sesi-lapangan/${fs.id}/kursi`);
    const rows = peneliti.locator('input[data-seat-input=row]');
    for (let i = 0; i < await rows.count(); i++) {
      await rows.nth(i).fill('1');
      await peneliti.locator('input[data-seat-input=seat]').nth(i).fill(String(i + 1));
    }
    await submitFormOf(peneliti, 'input[data-seat-input=row]');
    await peneliti.goto(`${BASE}/admin/sesi-lapangan/${fs.id}`);
    await Promise.all([peneliti.waitForNavigation(), peneliti.locator(`form[action$="/${fs.id}/mulai-tes"]`).evaluate((f) => f.submit())]);
  }
  check(one("SELECT COUNT(*) FROM participant_seats WHERE booklet IS NOT NULL") === '3', '3. seats with booklets');
  await answerTests(sekar);
  check(Number(one("SELECT COUNT(*) FROM instrument_sittings s JOIN participants p ON p.id = s.participant_id WHERE p.username = 'qa_sekar' AND s.submitted_at IS NOT NULL")) >= 1, '3. pre-test submitted and stored');

  // 4. P2: an unfinished attempt
  await closeFieldSession(peneliti, p1a.id);
  await closeFieldSession(peneliti, p1b.id);
  check(one(`SELECT COUNT(*) FROM game_sessions WHERE field_session_id IN (${p1a.id}, ${p1b.id}) AND status <> 'paused'`) === '0', '4. closing P1 pauses its game sessions');
  const p2a = await createFieldSession(peneliti, schools[0], 'A', 2);
  const p2b = await createFieldSession(peneliti, schools[1], 'B', 2);
  const sekar2 = await studentLogin('qa_sekar', STUDENT_PASSWORD, p2a.token);
  await sekar2.goto(`${BASE}/intro/selesai`);
  const first = await openChallenge(sekar2, 'temanggung', 1);
  check(first?.attempt_id > 0, '4. Temanggung challenge 1 opens in P2');
  check(await sendCheck(sekar2, first, { order: [1, 0, 2, 3, 4, 5, 6, 7, 8] }) === 200, '4. a wrong check is stored');

  // 5. Days pass; the nightly retention must keep it open
  await closeFieldSession(peneliti, p2a.id);
  const attempt = first.attempt_id;
  sql(`UPDATE challenge_attempts SET started_at = started_at - INTERVAL 2 DAY WHERE id = ${attempt}`);
  sql(`UPDATE item_responses SET answered_at = answered_at - INTERVAL 2 DAY WHERE challenge_attempt_id = ${attempt}`);
  sql(`UPDATE game_event_logs SET server_received_at = server_received_at - INTERVAL 2 DAY WHERE challenge_attempt_id = ${attempt}`);
  execFileSync('php', ['spark', 'gelita:retention:run'], { stdio: 'ignore' });
  check(one(`SELECT status FROM challenge_attempts WHERE id = ${attempt}`) === 'in_progress', '5. two days later the nightly retention keeps the field attempt open');

  // 6. Make-up session on another day: the same attempt continues
  await peneliti.goto(`${BASE}/admin/sesi-lapangan/${p2a.id}/susulan`);
  const makeup = peneliti.locator(`form[action$="/${p2a.id}/susulan"]`);
  for (const box of await makeup.locator('input[type=checkbox]').all()) await box.check();
  const times = sessionTimes();
  await makeup.locator('input[type=date]').fill(times.date);
  await makeup.locator('input[type=time]').nth(0).fill(times.opens);
  await makeup.locator('input[type=time]').nth(1).fill(times.closes);
  await submitFormOf(peneliti, `form[action$="/${p2a.id}/susulan"] input[type=date]`);
  const makeupId = Number(peneliti.url().match(/sesi-lapangan\/(\d+)$/)?.[1] || 0);
  const makeupToken = one(`SELECT token FROM field_session_tokens WHERE field_session_id = ${makeupId} ORDER BY id DESC LIMIT 1`);
  check(makeupId > 0 && makeupToken !== '', '6. make-up session P2-S with its own token', one(`SELECT code FROM field_sessions WHERE id = ${makeupId}`));
  const sekar3 = await studentLogin('qa_sekar', STUDENT_PASSWORD, makeupToken);
  const resumed = await openChallenge(sekar3, 'temanggung', 1);
  check(resumed?.attempt_id === attempt, '6. the student continues the same attempt', `attempt ${resumed?.attempt_id}`);
  check(one(`SELECT COUNT(*) FROM challenge_attempt_segments WHERE challenge_attempt_id = ${attempt}`) === '2', '6. a second attempt segment');
  await sendCheck(sekar3, resumed, { order: [0, 1, 2, 3, 4, 5, 6, 7, 8] });
  check(one(`SELECT status FROM challenge_attempts WHERE id = ${attempt}`) === 'completed', '6. completed on the second try', `stars ${one(`SELECT stars FROM challenge_attempts WHERE id = ${attempt}`)}`);

  // 7. Second school, download, closing the study
  await dewiFirst.context().close();
  const dewi = await studentLogin('qa_dewi', STUDENT_PASSWORD, p2b.token);
  await dewi.goto(`${BASE}/intro/selesai`);
  const open = await openChallenge(dewi, 'temanggung', 1);
  check(open?.attempt_id > 0, '7. second school plays in its own P2');

  await ketua.goto(`${BASE}/admin/ekspor`);
  await ketua.getByRole('button', { name: /Buat XLSX/ }).click();
  await ketua.waitForTimeout(8000);
  check(Number(one("SELECT COUNT(*) FROM data_exports WHERE file_path IS NOT NULL OR status = 'done'")) >= 1, '7. XLSX export built');
  for (const dimension of ['pengerjaan', 'percobaan']) {
    await ketua.goto(`${BASE}/admin/data/${dimension}`);
    check(await ketua.locator('.data-table tbody tr').count() > 0, `7. Data mentah: ${dimension} has rows`);
  }

  await ketua.goto(`${BASE}/admin/studi`);
  await ketua.locator(`form[action$="/admin/studi/${studyId}"] select[name=status]`).selectOption('closed');
  await submitFormOf(ketua, `form[action$="/admin/studi/${studyId}"] select[name=status]`);
  check(one(`SELECT status FROM challenge_attempts WHERE id = ${open.attempt_id}`) === 'abandoned', '7. closing the study closes the open field attempt');
  check(one(`SELECT COUNT(*) FROM game_event_logs WHERE challenge_attempt_id = ${open.attempt_id} AND event_type = 'challenge_abandoned' AND payload_json LIKE '%study_closed%'`) === '1', '7. with reason study_closed');
  const after = await newPage();
  await after.goto(`${BASE}/masuk`);
  check((await after.locator('body').innerText()).includes('Penelitian sudah selesai'), '7. /masuk says the research has ended');
  await shot(after, 'smoke-7-masuk');
}

// ------------------------------------------------------------------ live mode

async function liveMode() {
  for (const [path, want] of [['/', 200], ['/masuk', 200], ['/admin/login', 200], ['/admin/panduan-staf', 200], ['/halaman-tidak-ada-qa', 404]]) {
    const res = await fetch(BASE + path, { redirect: 'manual' });
    check(res.status === want, `GET ${path}`, String(res.status));
  }

  const user = process.env.GELITA_UJI_USER;
  const pass = process.env.GELITA_UJI_PASS;
  if (user && pass) {
    const page = await studentLogin(user, pass);
    check(!page.url().endsWith('/masuk'), 'test account signs in', page.url().replace(BASE, ''));
    await page.goto(`${BASE}/peta`);
    check(page.url().endsWith('/peta') || page.url().endsWith('/token'), 'map (or the token screen in research mode)', page.url().replace(BASE, ''));
    await shot(page, 'live-peta');
  } else {
    console.log('skip: GELITA_UJI_USER / GELITA_UJI_PASS not set');
  }

  const staffUser = process.env.GELITA_UJI_STAFF_USER;
  const staffPass = process.env.GELITA_UJI_STAFF_PASS;
  if (staffUser && staffPass) {
    const page = await newPage();
    await page.goto(`${BASE}/admin/login`);
    await page.fill('#username', staffUser);
    await page.fill('#password', staffPass);
    await submitFormOf(page, '#password');
    check(/\/admin(\/|$)/.test(new URL(page.url()).pathname) && !page.url().endsWith('/login'), 'staff account signs in', page.url().replace(BASE, ''));
  } else {
    console.log('skip: GELITA_UJI_STAFF_USER / GELITA_UJI_STAFF_PASS not set');
  }
}

// ------------------------------------------------------------------ run

browser = await chromium.launch();
try {
  await (MODE === 'live' ? liveMode() : localMode());
} catch (error) {
  console.error(error);
  failures++;
} finally {
  await browser.close();
}
console.log(failures === 0 ? '\nAll checks passed.' : `\n${failures} check(s) failed.`);
process.exit(failures === 0 ? 0 : 1);
