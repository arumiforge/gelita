#!/usr/bin/env node
/**
 * Load test of docs/rencana-log-asinkron.md §7: N students play at once over
 * plain HTTP (no browser), and the script measures what the server feels.
 *
 *   php tools/qa/load-students.php 90 beban      # once per test database, as www-data
 *   QA_CLIENT=old node tools/qa/load-test.mjs     # request rhythm of the client before PR #123
 *   QA_CLIENT=new node tools/qa/load-test.mjs     # request rhythm of the client since PR #123
 *
 * Environment:
 *   QA_BASE_URL     app URL (default http://127.0.0.1:8080), served by Nginx + PHP-FPM
 *   QA_STUDENTS     number of students (default 30); usernames {QA_PREFIX}001…
 *   QA_PREFIX       username prefix (default beban); password Load#Gelita2026
 *   QA_MINUTES      measured play time after the ramp (default 5)
 *   QA_RAMP_SECONDS students log in spread over this many seconds (default 60)
 *   QA_CLIENT       old | new (default new)
 *   QA_AUDIO_ASSET  audio_assets.id used for audio telemetry (required)
 *   QA_FPM_STATUS   PHP-FPM status URL (default http://127.0.0.1:8090/fpm-status)
 *   QA_MYSQL        database client command for SHOW GLOBAL STATUS (default "mysql -uroot")
 *   QA_OUT          write the result as JSON to this file
 *
 * Each student logs in, opens the intro, then loops through
 * peta → wilayah → dialog (first visit) → misi → tantangan (Benar atau Salah
 * node of temanggung, magelang, wonosobo in turn). On the challenge page it
 * sends three checks (30 s, 60 s, 85 s), each answering every item. During the
 * whole loop it emits one gameplay event every 2 s and one audio event every
 * 6 s, the same rhythm as tools/qa/event-requests.mjs. Page changes and
 * telemetry follow the client rules:
 *
 *   old (core/events.js before PR #123): per channel, a batch 3 s after the
 *   first waiting event or at 20 events, events to /api/events and audio to
 *   /api/audio-events; heartbeat every 60 s; before navigation the curtain
 *   flushes, pagehide beacons what is left and keeps a copy, and the next page
 *   re-sends that copy at once.
 *
 *   new (since PR #123): one batch carries both channels, 30 s after the
 *   first waiting event or at 40 (CONFIG.eventFlush); heartbeat only after
 *   60 s without a batch; the curtain flush and the pagehide beacons stay; the
 *   kept copy rides the next page's first batch instead of its own request.
 *
 * The study must use unlock_mode `free` so every region is open. Never run
 * against production: it writes hundreds of thousands of rows.
 */
import { execFileSync } from 'node:child_process';
import { randomUUID } from 'node:crypto';
import { writeFileSync } from 'node:fs';

const BASE = (process.env.QA_BASE_URL || 'http://127.0.0.1:8080').replace(/\/$/, '');
const STUDENTS = Number(process.env.QA_STUDENTS || 30);
const PREFIX = process.env.QA_PREFIX || 'beban';
const PASSWORD = 'Load#Gelita2026';
const MINUTES = Number(process.env.QA_MINUTES || 5);
const RAMP_MS = Number(process.env.QA_RAMP_SECONDS || 60) * 1000;
const CLIENT = process.env.QA_CLIENT === 'old' ? 'old' : 'new';
const AUDIO_ASSET = Number(process.env.QA_AUDIO_ASSET || 0);
const FPM_STATUS = process.env.QA_FPM_STATUS || 'http://127.0.0.1:8090/fpm-status';
const MYSQL = (process.env.QA_MYSQL || 'mysql -uroot').split(/\s+/);

if (!AUDIO_ASSET) throw new Error('QA_AUDIO_ASSET is required (an audio_assets.id of the test database)');

const RULES = CLIENT === 'old'
  ? { flushMs: 3_000, flushAt: 20, combined: false, heartbeatMs: 60_000, heartbeatWhenIdleOnly: false, resendOwnRequest: true }
  : { flushMs: 30_000, flushAt: 40, combined: true, heartbeatMs: 60_000, heartbeatWhenIdleOnly: true, resendOwnRequest: false };
const REGIONS = [['temanggung', 3], ['magelang', 3], ['wonosobo', 3]];
const EVENT_MS = 2_000;
const AUDIO_MS = 6_000;
const STAY_MS = { peta: 20_000, wilayah: 10_000, dialog: 15_000, misi: 10_000 };
const CHECK_AT_MS = [30_000, 60_000, 85_000];
const CHALLENGE_MS = 90_000;

const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));
const now = () => new Date().toISOString();

// ------------------------------------------------------------------ measurement

let measuring = false;
const stats = new Map();      // label → { count, errors, statuses, ms: [] }
function record(label, status, ms) {
  if (!measuring) return;
  // Only graded checks count as `check`; a refused one (409, 429) is reported apart
  if (label === 'check' && status !== 200) label = 'check_refused';
  const s = stats.get(label) ?? { count: 0, errors: 0, statuses: {}, ms: [] };
  s.count++;
  s.statuses[status] = (s.statuses[status] ?? 0) + 1;
  if (status === 0 || status >= 400) s.errors++;
  s.ms.push(ms);
  stats.set(label, s);
}
const pct = (sorted, p) => (sorted.length ? sorted[Math.min(sorted.length - 1, Math.ceil((p / 100) * sorted.length) - 1)] : null);

// ------------------------------------------------------------------ one student

class Student {
  constructor(n) {
    this.username = `${PREFIX}${String(n).padStart(3, '0')}`;
    this.jar = new Map();
    this.csrf = null;
    this.seq = 0;
    this.queues = { events: [], audio: [] };
    this.timers = { events: null, audio: null, both: null };
    this.kept = { events: [], audio: [] };   // pagehide copies (offline queue)
    this.lastBatchAt = Date.now();
    this.dialogSeen = new Set();
    this.refs = {};
    this.stopped = false;
  }

  async request(label, method, path, { json, form, headers = {} } = {}) {
    const h = { cookie: [...this.jar].map(([k, v]) => `${k}=${v}`).join('; '), ...headers };
    let body;
    if (json !== undefined) {
      h['content-type'] = 'application/json';
      if (this.csrf) h['X-CSRF-TOKEN'] = this.csrf;
      body = JSON.stringify(json);
    } else if (form) {
      h['content-type'] = 'application/x-www-form-urlencoded';
      body = new URLSearchParams(form).toString();
    }
    const started = performance.now();
    let status = 0;
    let text = '';
    let location = null;
    try {
      const res = await fetch(BASE + path, { method, headers: h, body, redirect: 'manual' });
      status = res.status;
      location = res.headers.get('location');
      for (const cookie of res.headers.getSetCookie()) {
        const [pair] = cookie.split(';');
        const i = pair.indexOf('=');
        this.jar.set(pair.slice(0, i), pair.slice(i + 1));
      }
      text = await res.text();
    } catch {
      status = 0;
    }
    record(label, status, performance.now() - started);
    return { status, text, location };
  }

  async page(path) {
    const res = await this.request('page', 'GET', path);
    if (res.status === 302 && res.location) return this.page(new URL(res.location).pathname);
    const config = res.text.match(/id="app-config">([\s\S]*?)<\/script>/);
    if (config) {
      const cfg = JSON.parse(config[1]);
      this.csrf = cfg.csrfHash;
      this.seq = Math.max(this.seq, Number(cfg.sequenceFloor || 0) - 1);
    }
    return res;
  }

  // ------------------------------------------------------------ telemetry

  emit(type, payload) {
    this.push('events', {
      client_event_id: randomUUID(), event_type: type, occurred_at: now(), sequence_no: ++this.seq,
      level_id: null, node_id: this.refs.nodeId ?? null, attempt_id: this.refs.attemptId ?? null, item_id: null, payload,
    });
  }

  emitAudio(action) {
    this.push('audio', {
      client_event_id: randomUUID(), occurred_at: now(), audio_asset_id: AUDIO_ASSET, action,
      listened_ms: action === 'complete' ? 4200 : 0, completed: action === 'complete', attempt_id: this.refs.attemptId ?? null,
    });
  }

  push(channel, event) {
    this.queues[channel].push(event);
    const key = RULES.combined ? 'both' : channel;
    const waiting = RULES.combined ? Math.max(this.queues.events.length, this.queues.audio.length) : this.queues[channel].length;
    if (waiting >= RULES.flushAt) this.flush(channel);
    else if (!this.timers[key]) this.timers[key] = setTimeout(() => this.flush(channel), RULES.flushMs);
  }

  async flush(channel = null) {
    if (RULES.combined) {
      clearTimeout(this.timers.both);
      this.timers.both = null;
      const events = this.queues.events.splice(0, 50);
      const audio = this.queues.audio.splice(0, 50);
      if (!events.length && !audio.length) return;
      this.lastBatchAt = Date.now();
      await this.request('telemetry', 'POST', '/api/events', { json: { events, ...(audio.length ? { audio } : {}), client_sent_at: now() } });
      return;
    }
    const channels = channel ? [channel] : ['events', 'audio'];
    await Promise.all(channels.map(async (c) => {
      clearTimeout(this.timers[c]);
      this.timers[c] = null;
      const batch = this.queues[c].splice(0, 50);
      if (!batch.length) return;
      this.lastBatchAt = Date.now();
      await this.request('telemetry', 'POST', c === 'events' ? '/api/events' : '/api/audio-events', { json: { events: batch, client_sent_at: now() } });
    }));
  }

  /** Leaving a page: curtain flush, then pagehide beacons and keeps a copy. */
  async leavePage() {
    await this.flush();
    this.emit('page_hidden', { screen: 'load' });
    for (const c of ['events', 'audio']) {
      clearTimeout(this.timers[c]);
      this.timers[c] = null;
      const batch = this.queues[c].splice(0, 50);
      if (!batch.length) continue;
      this.kept[c] = batch;
      // sendBeacon: one request per channel, no answer awaited
      this.request('telemetry', 'POST', c === 'events' ? '/api/events' : '/api/audio-events', { json: { events: batch, client_sent_at: now() } });
    }
    clearTimeout(this.timers.both);
    this.timers.both = null;
  }

  /** Arriving on a page: the kept copy is re-sent (old: at once, new: with the first batch). */
  async arrive() {
    const kept = this.kept;
    this.kept = { events: [], audio: [] };
    if (RULES.resendOwnRequest) {
      await Promise.all(['events', 'audio'].filter((c) => kept[c].length).map((c) =>
        this.request('telemetry', 'POST', c === 'events' ? '/api/events' : '/api/audio-events', { json: { events: kept[c], client_sent_at: now() } })));
    } else {
      for (const c of ['events', 'audio']) for (const e of kept[c]) this.push(c, e);
    }
    this.emit('viewport_changed', { w: 1280, h: 720 });
  }

  heartbeatLoop() {
    this.heartbeat = setInterval(() => {
      if (RULES.heartbeatWhenIdleOnly && Date.now() - this.lastBatchAt < RULES.heartbeatMs) return;
      this.request('heartbeat', 'POST', '/api/session/heartbeat', { json: {} });
    }, RULES.heartbeatMs);
  }

  activityLoops() {
    this.eventTimer = setInterval(() => this.emit('answer_selected', { t_ms: 1000 }), EVENT_MS);
    let n = 0;
    this.audioTimer = setInterval(() => this.emitAudio(['play', 'pause', 'complete'][n++ % 3]), AUDIO_MS);
  }

  // ------------------------------------------------------------ play

  async login() {
    const form = await this.request('login', 'GET', '/masuk');
    const token = form.text.match(/name="gelita_csrf" value="([^"]+)"/)?.[1];
    const res = await this.request('login', 'POST', '/masuk', {
      form: { gelita_csrf: token, username: this.username, password: PASSWORD },
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    });
    if (res.status !== 200 || !res.text.includes('"ok":true')) throw new Error(`${this.username}: login failed (${res.status})`);
    await this.page('/intro/selesai');
  }

  async visit(path, stayMs) {
    await this.arrive();
    await this.page(path);
    await sleep(stayMs);
    await this.leavePage();
  }

  async challenge(region, seq) {
    await this.arrive();
    const res = await this.page(`/tantangan/${region}/${seq}`);
    const raw = res.text.match(/id="challenge-data"[^>]*>([\s\S]*?)<\/script>/);
    const data = raw ? JSON.parse(raw[1]) : null;
    const started = Date.now();
    if (data?.attempt_id) {
      this.refs = { nodeId: data.node?.id, attemptId: data.attempt_id };
      const options = data.verdict_options?.length ? data.verdict_options : ['benar', 'salah'];
      for (const [checkNo, at] of CHECK_AT_MS.entries()) {
        await sleep(Math.max(0, started + at - Date.now()));
        if (this.stopped) break;
        const answers = (data.items || []).map((item) => ({ item_id: item.id, answer: { verdict: options[Math.floor(Math.random() * options.length)] } }));
        const res = await this.request('check', 'POST', `/api/attempts/${data.attempt_id}/check`, {
          json: { answers, check_no: checkNo + 1, client_event_id: randomUUID(), occurred_at: now() },
        });
        // A check that closes the attempt (all correct, or the third try) ends the challenge early
        if (res.status !== 200 || /"completed":true|"exhausted":true/.test(res.text)) break;
      }
    }
    await sleep(Math.max(0, started + CHALLENGE_MS - Date.now()));
    this.refs = {};
    await this.leavePage();
  }

  async run(until) {
    await this.login();
    this.heartbeatLoop();
    this.activityLoops();
    for (let i = 0; Date.now() < until; i++) {
      const [region, seq] = REGIONS[i % REGIONS.length];
      await this.visit('/peta', STAY_MS.peta);
      await this.visit(`/wilayah/${region}`, STAY_MS.wilayah);
      if (!this.dialogSeen.has(region)) {
        this.dialogSeen.add(region);
        await this.visit(`/dialog/${region}`, STAY_MS.dialog);
      }
      await this.visit(`/misi/${region}/${seq}`, STAY_MS.misi);
      await this.challenge(region, seq);
    }
    this.stop();
  }

  stop() {
    this.stopped = true;
    clearInterval(this.heartbeat);
    clearInterval(this.eventTimer);
    clearInterval(this.audioTimer);
    for (const t of Object.values(this.timers)) clearTimeout(t);
  }
}

// ------------------------------------------------------------------ server probes

const sql = (query) => execFileSync(MYSQL[0], [...MYSQL.slice(1), '-N', '-B', '-e', query]).toString().trim();
const STATUS_KEYS = ['Questions', 'Com_select', 'Com_insert', 'Com_update', 'Handler_write', 'Innodb_data_fsyncs', 'Innodb_row_lock_waits', 'Innodb_row_lock_time'];
function dbStatus() {
  const rows = sql(`SHOW GLOBAL STATUS WHERE Variable_name IN (${STATUS_KEYS.map((k) => `'${k}'`).join(',')})`);
  return Object.fromEntries(rows.split('\n').map((line) => line.split('\t')).map(([k, v]) => [k, Number(v)]));
}

function cpuTimes() {
  const [, ...fields] = execFileSync('head', ['-1', '/proc/stat']).toString().trim().split(/\s+/);
  const n = fields.map(Number);
  return { busy: n[0] + n[1] + n[2] + n[5] + n[6] + n[7], total: n.reduce((a, b) => a + b, 0) };
}

const fpm = { samples: 0, activeSum: 0, activeMax: 0, queueMax: 0, atMax: 0, maxChildren: 0 };
async function sampleFpm() {
  try {
    const s = await (await fetch(`${FPM_STATUS}?json`)).json();
    const active = s['active processes'];
    fpm.samples++;
    fpm.activeSum += active;
    fpm.activeMax = Math.max(fpm.activeMax, active);
    fpm.queueMax = Math.max(fpm.queueMax, s['listen queue']);
    fpm.maxChildren = s['max children reached'];
    if (active >= Number(process.env.QA_FPM_MAX_CHILDREN || 20)) fpm.atMax++;
  } catch { /* status page optional */ }
}

// ------------------------------------------------------------------ run

const students = Array.from({ length: STUDENTS }, (_, i) => new Student(i + 1));
const start = Date.now();
const measureFrom = start + RAMP_MS;
const until = measureFrom + MINUTES * 60_000;

console.log(`${CLIENT} client, ${STUDENTS} students, ${BASE}, ramp ${RAMP_MS / 1000} s, measured ${MINUTES} min`);
const runs = students.map((s) => sleep(Math.random() * RAMP_MS).then(() => s.run(until)).catch((e) => { console.error(e.message); s.stop(); }));

await sleep(Math.max(0, measureFrom - Date.now()));
measuring = true;
const db0 = dbStatus();
const cpu0 = cpuTimes();
const fpmTimer = setInterval(sampleFpm, 1000);
await sleep(Math.max(0, until - Date.now()));
measuring = false;
clearInterval(fpmTimer);
const db1 = dbStatus();
const cpu1 = cpuTimes();
students.forEach((s) => s.stop());
await Promise.race([Promise.allSettled(runs), sleep(100_000)]);

const seconds = MINUTES * 60;
const summary = {
  client: CLIENT, students: STUDENTS, base: BASE, minutes: MINUTES,
  requests: Object.fromEntries([...stats].map(([label, s]) => {
    const sorted = [...s.ms].sort((a, b) => a - b);
    return [label, {
      count: s.count, perStudentPerMinute: +(s.count / STUDENTS / MINUTES).toFixed(2), errors: s.errors, statuses: s.statuses,
      p50: +pct(sorted, 50)?.toFixed(1), p95: +pct(sorted, 95)?.toFixed(1), p99: +pct(sorted, 99)?.toFixed(1), max: +sorted.at(-1)?.toFixed(1),
    }];
  })),
  db: Object.fromEntries(STATUS_KEYS.map((k) => [k, +((db1[k] - db0[k]) / seconds).toFixed(1)])),
  cpuBusyPercent: +((100 * (cpu1.busy - cpu0.busy)) / (cpu1.total - cpu0.total)).toFixed(1),
  fpm: { activeAvg: +(fpm.activeSum / Math.max(1, fpm.samples)).toFixed(1), activeMax: fpm.activeMax, secondsAtMaxChildren: fpm.atMax, listenQueueMax: fpm.queueMax, maxChildrenReached: fpm.maxChildren },
};
console.log(JSON.stringify(summary, null, 2));
if (process.env.QA_OUT) writeFileSync(process.env.QA_OUT, JSON.stringify(summary, null, 2));
process.exit(0);
