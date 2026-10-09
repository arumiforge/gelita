/**
 * Antrean event dengan pengiriman batch dan idempotensi (client_event_id).
 *
 * Dua kanal dengan amplop yang sama `{ events: [...] }`:
 *   emit()       → POST /api/events        (game_event_logs)
 *   emitAudio()  → POST /api/audio-events  (audio_usage_events)
 *
 * Dikirim tiap 3 detik atau saat antrean mencapai 20 event, maksimum 50 per
 * request (= Config\Gelita::$maxEventsPerBatch). Saat tab disembunyikan atau
 * ditutup, dikirim lewat sendBeacon (fallback fetch keepalive).
 *
 * `client_event_id` dibuat sekali saat event lahir dan ikut tersimpan di
 * antrean offline, jadi pengiriman ulang dibalas `duplicate` oleh server —
 * tidak pernah menggandakan data.
 *
 * Batch yang sedang dikirim (fetch biasa) dibatalkan browser bila halaman
 * berpindah di tengah jalan — makin mungkin sejak tirai (Tahap 3) menunda
 * perpindahan ±1,8 detik. Karena itu batch itu diingat sampai server
 * menjawab, dan ikut dikirim lewat sendBeacon saat halaman ditutup;
 * bila ternyata sudah sampai, server membalasnya `duplicate`.
 *
 * Research data integrity (rencana-paket-aset §7.3):
 *  - `sequence_no` continues across pages of one session (Storage.nextSequence,
 *    never below CONFIG.sequenceFloor);
 *  - every request carries `client_sent_at`, from which the server stores
 *    `clock_offset_ms` per event;
 *  - a forced flush (tab hidden or closed) beacons the events AND keeps a copy
 *    in the offline queue, because a beacon can be aborted or lost silently;
 *  - events the offline queue cannot keep are counted, a warning toast shows,
 *    and the count goes to the server as `dropped` + `dropped_id` with the
 *    next fetch batch (never with a beacon, which gets no answer).
 */
import { CONFIG, t } from './config.js';
import { apiRequest, ApiError } from './api.js';
import { Storage } from './storage.js';
import { toast } from './toast.js';

const FLUSH_MS = 3000;
const FLUSH_AT = 20;
const BATCH_MAX = 50;

let offlineNoticeShown = false;
let queueFullNoticeShown = false;

/** UUID v4; crypto.randomUUID hanya ada di konteks aman (HTTPS/localhost). */
export function uuid() {
  if (window.crypto?.randomUUID) return window.crypto.randomUUID();
  const bytes = window.crypto.getRandomValues(new Uint8Array(16));
  bytes[6] = (bytes[6] & 0x0f) | 0x40;
  bytes[8] = (bytes[8] & 0x3f) | 0x80;
  const hex = Array.from(bytes, (b) => b.toString(16).padStart(2, '0')).join('');
  return `${hex.slice(0, 8)}-${hex.slice(8, 12)}-${hex.slice(12, 16)}-${hex.slice(16, 20)}-${hex.slice(20)}`;
}

/** Waktu klien ISO-8601 dengan milidetik, mis. 2026-09-21T02:14:22.481Z */
export const now = () => new Date().toISOString();

class EventQueue {
  constructor(channel, path) {
    this.channel = channel;
    this.path = path;
    this.queue = [];
    this.timer = null;
    this.sending = null;  // Promise pengiriman yang sedang berjalan
    this.inflight = [];   // batch yang dikirim fetch dan belum dijawab server
  }

  push(event) {
    // Tanpa sesi permainan (layar sebelum login) server pasti menolak: jangan antre.
    if (!CONFIG.sessionTag) return;
    this.queue.push(event);
    if (this.queue.length >= FLUSH_AT) this.flush();
    else if (!this.timer) this.timer = setTimeout(() => this.flush(), FLUSH_MS);
  }

  async flush(force = false) {
    clearTimeout(this.timer);
    this.timer = null;

    if (force) {
      // Batch yang masih di jalan ikut dikirim: fetch-nya batal bila halaman ditutup
      const pending = this.inflight.concat(this.queue);
      this.queue = [];
      if (!pending.length) return;
      // A beacon can be aborted by the navigation or lost offline without any
      // error, so a copy also waits in the offline queue; the next page
      // re-sends it and the server answers `duplicate` for what arrived (§7.3).
      // Offline no beacon is tried at all.
      const online = navigator.onLine !== false;
      this.store(pending, online);
      if (online) while (pending.length) this.beacon(pending.splice(0, BATCH_MAX));
      return;
    }

    // Pengiriman yang sedang berjalan ikut mengambil event yang masuk sesudahnya;
    // pemanggil yang menunggu (tirai sebelum pindah halaman) menunggu yang sama
    if (!this.sending) this.sending = this.drain().finally(() => { this.sending = null; });
    await this.sending;
  }

  async drain() {
    while (this.queue.length) {
      const batch = this.queue.splice(0, BATCH_MAX);
      const dropped = Storage.droppedReport(CONFIG.sessionTag, this.channel);
      const body = { events: batch, client_sent_at: now() };
      if (dropped) Object.assign(body, { dropped: dropped.count, dropped_id: dropped.id });
      this.inflight = batch;
      try {
        await apiRequest(this.path, { method: 'POST', body });
        if (dropped) Storage.clearDropped(CONFIG.sessionTag, this.channel, dropped);
      } catch (error) {
        this.keepForLater(batch, error);
        return;
      } finally {
        this.inflight = [];
      }
    }
  }

  /** Hanya galat yang bisa pulih (jaringan, server sibuk) yang disimpan untuk dikirim ulang. */
  keepForLater(batch, error) {
    const recoverable = !(error instanceof ApiError) || error.isNetwork || error.code === 'RATE_LIMITED';
    if (!recoverable) return;

    this.store(batch);
    if (!offlineNoticeShown) {
      offlineNoticeShown = true;
      toast(t('savedOffline'), 'warn');
    }
  }

  /** Offline queue; a loss is never silent (counted by Storage, toast here). */
  store(batch, beaconed = false) {
    const lost = Storage.queueEvents(batch, CONFIG.sessionTag, this.channel, beaconed);
    if (lost > 0 && !queueFullNoticeShown) {
      queueFullNoticeShown = true;
      toast(t('eventQueueFull'), 'warn');
    }
  }

  beacon(batch) {
    const body = JSON.stringify({ events: batch, client_sent_at: now(), [CONFIG.csrfName]: CONFIG.csrfHash });
    const target = `${CONFIG.apiBase}${this.path}`;

    try {
      if (navigator.sendBeacon && navigator.sendBeacon(target, new Blob([body], { type: 'application/json' }))) return;
    } catch {
      /* sebagian browser menolak Blob JSON: pakai fetch keepalive */
    }

    fetch(target, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CONFIG.csrfHash },
      credentials: 'same-origin',
      body,
      keepalive: true,
    }).catch(() => {});   // the copy already waits in the offline queue
  }

  /** Kirim ulang antrean offline milik sesi ini. */
  async resend() {
    const pending = Storage.takeAll(CONFIG.sessionTag, this.channel);
    if (!pending.length) return;
    this.queue = pending.concat(this.queue);
    await this.flush();
  }
}

const gameEvents = new EventQueue('events', '/events');
const audioEvents = new EventQueue('audio', '/audio-events');

/**
 * Event gameplay mentah. `type` wajib salah satu Config\Gelita::$eventTypes.
 */
export function emit(type, { levelId, nodeId, attemptId, itemId, payload } = {}) {
  gameEvents.push({
    client_event_id: uuid(),
    event_type: type,
    occurred_at: now(),
    sequence_no: Storage.nextSequence(CONFIG.sessionTag, CONFIG.sequenceFloor),
    level_id: levelId ?? null,
    node_id: nodeId ?? null,
    attempt_id: attemptId ?? null,
    item_id: itemId ?? null,
    payload: payload ?? null,
  });
}

/**
 * Telemetry audio terstruktur:
 * { audio_asset_id, action: play|pause|replay|complete, play_index,
 *   listened_ms, completed, attempt_id? }
 */
export function emitAudio(event) {
  audioEvents.push({
    client_event_id: uuid(),
    occurred_at: now(),
    ...event,
  });
}

export async function flush(force = false) {
  await Promise.all([gameEvents.flush(force), audioEvents.flush(force)]);
}

/** Kirim ulang antrean offline. Server membalas "duplicate" untuk yang sudah masuk. */
export async function resendQueued() {
  await Promise.all([gameEvents.resend(), audioEvents.resend()]);
}

document.addEventListener('visibilitychange', () => {
  if (document.visibilityState === 'hidden') flush(true);
});
window.addEventListener('pagehide', () => flush(true));

/**
 * Kode layar Dok 1 §12.4 dari <body data-screen-code> (dirender layout dengan
 * resolver yang sama dengan screen_viewed di server). Pustaka terkunci
 * dikenali dari halamannya, seperti di ScreenViewFilter.
 */
const screenName = () => {
  const code = document.body?.dataset.screenCode || null;
  return code === 'library' && document.querySelector('[data-screen="library-locked"]') ? 'library_locked' : code;
};

/** `page_hidden` / `page_visible` dari visibilitychange (Dok 3 §7). */
export function initVisibilityTracking() {
  document.addEventListener('visibilitychange', () => {
    emit(document.visibilityState === 'hidden' ? 'page_hidden' : 'page_visible', { payload: { screen: screenName() } });
  });
}
