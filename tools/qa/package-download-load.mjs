#!/usr/bin/env node
/**
 * Many devices download the asset package at the same time (owner, 20
 * November 2026): the first meeting of a class, when every device must have
 * the whole package before Mulai (docs/rencana-paket-aset.md P11).
 *
 *   QA_BASE_URL=https://gelita.id QA_DEVICES=1,10,50 node tools/qa/package-download-load.mjs
 *   (from a cloud session: NODE_USE_ENV_PROXY=1 node …)
 *
 * Each device does what core/package.js does: GET /api/paket/aktif, the
 * manifest, then every file of the manifest, 4 at a time. Bodies are counted
 * and thrown away (nothing is written to disk). Devices of one round start
 * within QA_START_SECONDS of each other. A round ends when every device has
 * finished; the next round waits QA_PAUSE_SECONDS.
 *
 * Environment:
 *   QA_BASE_URL        app URL (default http://localhost:8080)
 *   QA_DEVICES         comma list of devices per round (default 1,10)
 *   QA_PARALLEL        files in flight per device (default 4, as core/package.js)
 *   QA_START_SECONDS   spread of the start times in a round (default 5)
 *   QA_PAUSE_SECONDS   pause between rounds (default 60)
 *   QA_OUT             write the results as JSON to this file
 *
 * Reads only; each device moves the whole package, so 50 devices of a 110 MB
 * package move about 5.5 GB out of the server. Check the hosting quota first.
 */
import { writeFileSync } from 'node:fs';

const BASE = (process.env.QA_BASE_URL || 'http://localhost:8080').replace(/\/$/, '');
const ROUNDS = (process.env.QA_DEVICES || '1,10').split(',').map(Number).filter((n) => n > 0);
const PARALLEL = Number(process.env.QA_PARALLEL || 4);
const START_MS = Number(process.env.QA_START_SECONDS || 5) * 1000;
const PAUSE_MS = Number(process.env.QA_PAUSE_SECONDS || 60) * 1000;

const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));
const url = (path) => `${BASE}/${String(path).replace(/^\//, '')}`;
const pct = (sorted, p) => (sorted.length ? sorted[Math.min(sorted.length - 1, Math.ceil((p / 100) * sorted.length) - 1)] : null);
const mb = (bytes) => +(bytes / 1048576).toFixed(1);

const active = await (await fetch(url('api/paket/aktif'))).json();
const info = active?.data ?? {};
if (!info.package_code || !info.manifest_url) {
  console.error('No active asset package on this server (api/paket/aktif names none). Build and publish one first.');
  process.exit(2);
}
const manifest = await (await fetch(url(info.manifest_url))).json();
const files = (manifest.files || []).map((f) => ({ url: f.url, bytes: Number(f.bytes || 0) }));
const totalBytes = files.reduce((sum, f) => sum + f.bytes, 0);
console.log(`package ${info.package_code}: ${files.length} files, ${mb(totalBytes)} MB; rounds ${ROUNDS.join(', ')} devices, ${PARALLEL} files at a time each`);

/** One device: the status call, the manifest, then every file, PARALLEL at a time. */
async function device() {
  const started = performance.now();
  let bytes = 0;
  let errors = 0;
  const fileMs = [];
  const statuses = {};
  const get = async (path) => {
    const t = performance.now();
    try {
      const res = await fetch(url(path));
      statuses[res.status] = (statuses[res.status] ?? 0) + 1;
      if (!res.ok) { errors++; await res.arrayBuffer(); return; }
      for await (const chunk of res.body) bytes += chunk.length;
    } catch (error) {
      statuses[0] = (statuses[0] ?? 0) + 1;
      errors++;
    }
    fileMs.push(performance.now() - t);
  };

  await get('api/paket/aktif');
  await get(info.manifest_url);
  let next = 0;
  await Promise.all(Array.from({ length: PARALLEL }, async () => {
    while (next < files.length) await get(files[next++].url);
  }));

  return { seconds: (performance.now() - started) / 1000, bytes, errors, statuses, fileMs };
}

const results = [];
for (const [index, devices] of ROUNDS.entries()) {
  if (index > 0) await sleep(PAUSE_MS);
  const roundStart = performance.now();
  const runs = await Promise.all(Array.from({ length: devices }, () => sleep(Math.random() * START_MS).then(device)));
  const wall = (performance.now() - roundStart) / 1000;
  const seconds = runs.map((r) => r.seconds).sort((a, b) => a - b);
  const fileMs = runs.flatMap((r) => r.fileMs).sort((a, b) => a - b);
  const bytes = runs.reduce((sum, r) => sum + r.bytes, 0);
  const statuses = {};
  for (const r of runs) for (const [k, v] of Object.entries(r.statuses)) statuses[k] = (statuses[k] ?? 0) + v;
  const round = {
    devices,
    wallSeconds: +wall.toFixed(1),
    deviceSeconds: { min: +seconds[0].toFixed(1), p50: +pct(seconds, 50).toFixed(1), p95: +pct(seconds, 95).toFixed(1), max: +seconds.at(-1).toFixed(1) },
    totalMB: mb(bytes),
    throughputMBps: +(bytes / 1048576 / wall).toFixed(1),
    perDeviceMBps: +(totalBytes / 1048576 / pct(seconds, 50)).toFixed(2),
    complete: runs.filter((r) => r.errors === 0 && r.bytes >= totalBytes).length,
    errors: runs.reduce((sum, r) => sum + r.errors, 0),
    statuses,
    fileMs: { p50: +pct(fileMs, 50).toFixed(0), p95: +pct(fileMs, 95).toFixed(0), max: +fileMs.at(-1).toFixed(0) },
  };
  results.push(round);
  console.log(JSON.stringify(round));
}

const summary = { base: BASE, package: info.package_code, files: files.length, packageMB: mb(totalBytes), parallelPerDevice: PARALLEL, rounds: results };
if (process.env.QA_OUT) writeFileSync(process.env.QA_OUT, JSON.stringify(summary, null, 2));
process.exit(results.every((r) => r.errors === 0) ? 0 : 1);
