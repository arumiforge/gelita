/**
 * Antrean event dengan pengiriman batch dan idempotensi (client_event_id).
 * Dikirim tiap 3 detik atau saat antrean mencapai 20 event; saat tab
 * disembunyikan/ditutup dikirim lewat sendBeacon / fetch keepalive.
 */
import { CONFIG } from './config.js';
import { apiRequest } from './api.js';
import { Storage } from './storage.js';

const FLUSH_MS = 3000;
const FLUSH_AT = 20;
const BATCH_MAX = 50; // = Config\Gelita::$maxEventsPerBatch

let queue = [];
let sequence = 0;
let flushTimer = null;

export function emit(type, { levelId, nodeId, attemptId, itemId, payload } = {}) {
  queue.push({
    client_event_id: crypto.randomUUID(),
    event_type: type,
    occurred_at: new Date().toISOString(),
    sequence_no: ++sequence,
    level_id: levelId ?? null,
    node_id: nodeId ?? null,
    attempt_id: attemptId ?? null,
    item_id: itemId ?? null,
    payload: payload ?? null,
  });
  scheduleFlush();
}

function scheduleFlush() {
  if (queue.length >= FLUSH_AT) {
    flush();
    return;
  }
  if (!flushTimer) flushTimer = setTimeout(() => flush(), FLUSH_MS);
}

export async function flush(force = false) {
  clearTimeout(flushTimer);
  flushTimer = null;

  while (queue.length) {
    const batch = queue.splice(0, BATCH_MAX);

    if (force) {
      beacon(batch);
      continue;
    }

    try {
      await apiRequest('/events', { method: 'POST', body: { events: batch } });
    } catch {
      Storage.queueEvents(batch); // simpan, kirim ulang saat online / halaman berikutnya
      return;
    }
  }
}

/** Kirim ulang antrean offline. Server membalas "duplicate" untuk yang sudah masuk. */
export async function resendQueued() {
  const pending = Storage.takeAll();
  if (!pending.length) return;
  queue = pending.concat(queue);
  await flush();
}

function beacon(batch) {
  const body = JSON.stringify({ events: batch, [CONFIG.csrfName]: CONFIG.csrfHash });
  const url = `${CONFIG.apiBase}/events`;

  if (navigator.sendBeacon && navigator.sendBeacon(url, new Blob([body], { type: 'application/json' }))) {
    return;
  }

  fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CONFIG.csrfHash },
    credentials: 'same-origin',
    body,
    keepalive: true,
  }).catch(() => Storage.queueEvents(batch));
}

document.addEventListener('visibilitychange', () => {
  if (document.visibilityState === 'hidden') flush(true);
});
window.addEventListener('pagehide', () => flush(true));
