/**
 * Event queue with batched sending and idempotency (client_event_id).
 *
 * Two channels, one transport (rencana-log-asinkron §5.1):
 *   emit()       → channel `events` (game_event_logs)
 *   emitAudio()  → channel `audio`  (audio_usage_events)
 * Both go out together in one `POST /api/events`
 * `{ events, audio?, client_sent_at, dropped?, dropped_id?, audio_dropped?, audio_dropped_id? }`.
 * A server that answers without an `audio` key does not know the combined
 * request yet: the audio batch then goes to `POST /api/audio-events` as
 * before, and so does all later audio of this page.
 *
 * Rhythm (R1): a batch leaves FLUSH_MS after the first waiting event, or at
 * once when a channel holds FLUSH_AT events; at most BATCH_MAX per channel
 * per request (= Config\Gelita::$maxEventsPerBatch). Both values come from
 * `CONFIG.eventFlush` (Gelita::$eventFlush) and fall back to 30 s / 40 events.
 * Forced sends stay immediate: tab hidden and page closed beacon whatever is
 * waiting, and the curtain (core/curtain.js) and the narrator's last slide
 * await flush() before the next screen, so `dialogue_completed` is on the
 * server when the map renders its "Belum didengar" badge.
 *
 * Persist-on-push (R1): every event is written to the offline queue
 * (core/storage.js) when it is created and removed only after the server
 * accepted the batch that carried it. A browser crash between two batches
 * loses nothing; the next page (restoreQueued, R4) sends it with its first
 * batch, and the server answers `duplicate` for anything that had arrived.
 *
 * A batch sent with fetch is cancelled by the browser when the page changes
 * in the middle, so it is remembered (`inflight`) until the server answers
 * and goes out by beacon too when the page closes.
 *
 * Research data integrity (rencana-paket-aset §7.3):
 *  - `sequence_no` continues across pages of one session (Storage.nextSequence,
 *    never below CONFIG.sequenceFloor);
 *  - every request carries `client_sent_at`, from which the server stores
 *    `clock_offset_ms` per event;
 *  - a beacon can be aborted or lost silently, so beaconed events stay in the
 *    offline queue (marked as beaconed) until a later fetch is acknowledged;
 *  - events the offline queue cannot keep are counted, a warning toast shows,
 *    and the count goes to the server with the next fetch batch (`dropped`
 *    for events, `audio_dropped` for audio), never with a beacon, which gets
 *    no answer.
 *
 * The "connection lost" toast (`savedOffline`) only shows for a failure that
 * holds: the browser reports offline, the server stays rate-limited after
 * apiRequest's own retries, or a quiet retry RETRY_MS later fails too. Moving
 * to the next screen cancels a batch still on its way (net::ERR_ABORTED),
 * which looks like a network error; the page is gone before the retry, so
 * the student no longer sees "Koneksi terputus" while fully online.
 */
import { CONFIG, t } from './config.js';
import { apiRequest, ApiError } from './api.js';
import { Storage } from './storage.js';
import { toast } from './toast.js';

const BATCH_MAX = 50;
const positive = (value) => (Number.isFinite(Number(value)) && Number(value) > 0 ? Math.floor(Number(value)) : 0);
const FLUSH_MS = positive(CONFIG.eventFlush?.intervalMs) || 30_000;
const FLUSH_AT = Math.min(BATCH_MAX, positive(CONFIG.eventFlush?.maxEvents) || 40);
/** Quiet retry after a single failed batch while the browser says online. */
const RETRY_MS = 5000;

let offlineNoticeShown = false;
let queueFullNoticeShown = false;

/** UUID v4; crypto.randomUUID only exists in a secure context (HTTPS/localhost). */
export function uuid() {
  if (window.crypto?.randomUUID) return window.crypto.randomUUID();
  const bytes = window.crypto.getRandomValues(new Uint8Array(16));
  bytes[6] = (bytes[6] & 0x0f) | 0x40;
  bytes[8] = (bytes[8] & 0x3f) | 0x80;
  const hex = Array.from(bytes, (b) => b.toString(16).padStart(2, '0')).join('');
  return `${hex.slice(0, 8)}-${hex.slice(8, 12)}-${hex.slice(12, 16)}-${hex.slice(16, 20)}-${hex.slice(20)}`;
}

/** Client time, ISO-8601 with milliseconds, e.g. 2026-09-21T02:14:22.481Z */
export const now = () => new Date().toISOString();

const idOf = (event) => event.client_event_id;

function warnQueueFull(lost) {
  if (lost > 0 && !queueFullNoticeShown) {
    queueFullNoticeShown = true;
    toast(t('eventQueueFull'), 'warn');
  }
}

/**
 * One channel: events waiting in memory (`queue`), the batch on its way
 * (`inflight`), and their copies in the offline queue. `held` holds the ids
 * of both, so restoring the offline queue never queues an event twice.
 */
class Channel {
  constructor(name, path) {
    this.name = name;
    this.path = path;          // own endpoint: beacons and the old-server fallback
    this.queue = [];
    this.inflight = [];
    this.held = new Set();
    this.unstored = new Set(); // ids the browser refused to persist
    this.beaconed = new Set(); // ids already beaconed by this page
  }

  add(event) {
    const { stored, lost } = Storage.persistEvents([event], CONFIG.sessionTag, this.name);
    if (!stored) this.unstored.add(idOf(event));
    warnQueueFull(lost);
    this.queue.push(event);
    this.held.add(idOf(event));
  }

  take() {
    this.inflight = this.queue.splice(0, BATCH_MAX);
    return this.inflight;
  }

  /** The server took the batch (or refused it for good): forget it everywhere. */
  settle(batch) {
    this.inflight = [];
    if (!batch.length) return;
    const ids = new Set(batch.map(idOf));
    Storage.removeEvents(CONFIG.sessionTag, this.name, ids);
    for (const id of ids) {
      this.held.delete(id);
      this.unstored.delete(id);
      this.beaconed.delete(id);
    }
  }

  /** The batch may succeed later: it leaves memory and waits in the offline queue. */
  fail(batch) {
    this.inflight = [];
    const unsaved = [];
    for (const event of batch) {
      this.held.delete(idOf(event));
      if (this.unstored.delete(idOf(event))) unsaved.push(event);
    }
    if (unsaved.length) warnQueueFull(Storage.queueEvents(unsaved, CONFIG.sessionTag, this.name));
  }

  /** Moves stored events of this session that are not in memory yet to the front of the queue. */
  restore() {
    const pending = Storage.pendingEvents(CONFIG.sessionTag, this.name).filter((event) => !this.held.has(idOf(event)));
    for (const event of pending) this.held.add(idOf(event));
    this.queue = pending.concat(this.queue);
  }

  /**
   * Forced send (tab hidden or page closed): returns what to beacon. The
   * events stay in the offline queue, marked as beaconed; the next page sends
   * them again and the server answers `duplicate` for what arrived.
   */
  takeForBeacon(online) {
    const pending = this.inflight.concat(this.queue).filter((event) => !this.beaconed.has(idOf(event)));
    for (const event of this.queue) this.held.delete(idOf(event));
    this.queue = [];
    if (!pending.length) return [];

    const unsaved = pending.filter((event) => this.unstored.has(idOf(event)));
    for (const event of unsaved) this.unstored.delete(idOf(event));
    if (unsaved.length) warnQueueFull(Storage.queueEvents(unsaved, CONFIG.sessionTag, this.name, online));
    if (!online) return [];

    const ids = new Set(pending.map(idOf));
    Storage.markBeaconed(CONFIG.sessionTag, this.name, ids);
    for (const id of ids) this.beaconed.add(id);
    return pending;
  }
}

const gameEvents = new Channel('events', '/events');
const audioEvents = new Channel('audio', '/audio-events');
const channels = [gameEvents, audioEvents];

/**
 * The shared sender of both channels: timer, the running send, failure
 * count, and whether the server needs the old audio endpoint.
 */
class Transport {
  constructor(path) {
    this.path = path;
    this.timer = null;
    this.sending = null;     // the running send; callers that wait (curtain) await the same one
    this.failures = 0;       // failed requests in a row; reset by any accepted request
    this.retryTimer = null;
    this.legacyAudio = false;
    this.lastAccepted = -Infinity;
  }

  /** Starts the timer, or sends at once when a channel reached FLUSH_AT. */
  schedule() {
    if (channels.some((channel) => channel.queue.length >= FLUSH_AT)) {
      this.flush();
    } else if (!this.timer && channels.some((channel) => channel.queue.length)) {
      this.timer = setTimeout(() => this.flush(), FLUSH_MS);
    }
  }

  async flush(force = false) {
    clearTimeout(this.timer);
    this.timer = null;

    if (force) {
      // Offline no beacon is tried at all; the events wait in the offline queue
      const online = navigator.onLine !== false;
      for (const channel of channels) {
        const batch = channel.takeForBeacon(online);
        while (batch.length) beacon(channel.path, batch.splice(0, BATCH_MAX));
      }
      return;
    }

    if (!this.sending) this.sending = this.drain().finally(() => { this.sending = null; });
    await this.sending;
  }

  async drain() {
    while (gameEvents.queue.length || audioEvents.queue.length) {
      const events = gameEvents.take();
      const audio = audioEvents.take();
      let audioDone = !audio.length;

      if (events.length || (audio.length && !this.legacyAudio)) {
        const body = { events, client_sent_at: now() };
        const report = dropped('events');
        if (report) Object.assign(body, { dropped: report.count, dropped_id: report.id });
        const combined = audio.length > 0 && !this.legacyAudio;
        let audioReport = null;
        if (combined) {
          body.audio = audio;
          audioReport = dropped('audio');
          if (audioReport) Object.assign(body, { audio_dropped: audioReport.count, audio_dropped_id: audioReport.id });
        }

        let answer;
        try {
          answer = await apiRequest(this.path, { method: 'POST', body });
          this.failures = 0;
        } catch (error) {
          this.keepForLater([[gameEvents, events], [audioEvents, audio]], error);
          return;
        }
        this.lastAccepted = performance.now();
        gameEvents.settle(events);
        clearDropped('events', report);

        if (combined) {
          if (answer && typeof answer === 'object' && Object.hasOwn(answer, 'audio')) {
            audioEvents.settle(audio);
            clearDropped('audio', audioReport);
            audioDone = true;
          } else {
            // A server without the combined request ignored the audio: use its own endpoint
            this.legacyAudio = true;
          }
        }
      }

      if (!audioDone) {
        const body = { events: audio, client_sent_at: now() };
        const report = dropped('audio');
        if (report) Object.assign(body, { dropped: report.count, dropped_id: report.id });
        try {
          await apiRequest(audioEvents.path, { method: 'POST', body });
          this.failures = 0;
        } catch (error) {
          this.keepForLater([[audioEvents, audio]], error);
          return;
        }
        this.lastAccepted = performance.now();
        audioEvents.settle(audio);
        clearDropped('audio', report);
      }
    }
  }

  /**
   * Only recoverable failures (network, busy server) keep the events for a
   * re-send. `batch` lists the request's [channel, events] pairs.
   */
  keepForLater(batch, error) {
    const recoverable = !(error instanceof ApiError) || error.isNetwork || error.code === 'RATE_LIMITED';
    if (!recoverable) {
      // Refused for good (401, 403, 422): it would never be accepted
      for (const [channel, events] of batch) channel.settle(events);
      return;
    }

    this.store(batch);
    this.failures++;

    const lasting = navigator.onLine === false || error?.code === 'RATE_LIMITED' || this.failures > 1;
    if (!lasting) {
      // One failure while online is often a batch cancelled by leaving the
      // page; retry once quietly before telling the student anything.
      clearTimeout(this.retryTimer);
      this.retryTimer = setTimeout(() => { this.retryTimer = null; this.resend(); }, RETRY_MS);
      return;
    }
    if (!offlineNoticeShown) {
      offlineNoticeShown = true;
      toast(t('savedOffline'), 'warn');
    }
  }

  /** The events leave memory and wait in the offline queue (persisted on push). */
  store(batch) {
    for (const [channel, events] of batch) channel.fail(events);
  }

  /** Moves this session's offline queue into memory and sends it now. */
  async resend() {
    for (const channel of channels) channel.restore();
    await this.flush();
  }
}

const transport = new Transport('/events');

function dropped(channel) {
  return Storage.droppedReport(CONFIG.sessionTag, channel);
}

function clearDropped(channel, report) {
  if (report) Storage.clearDropped(CONFIG.sessionTag, channel, report);
}

function beacon(path, batch) {
  const body = JSON.stringify({ events: batch, client_sent_at: now(), [CONFIG.csrfName]: CONFIG.csrfHash });
  const target = `${CONFIG.apiBase}${path}`;

  try {
    if (navigator.sendBeacon && navigator.sendBeacon(target, new Blob([body], { type: 'application/json' }))) return;
  } catch {
    /* some browsers refuse a JSON Blob: use fetch keepalive */
  }

  fetch(target, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CONFIG.csrfHash },
    credentials: 'same-origin',
    body,
    keepalive: true,
  }).catch(() => {});   // the copy already waits in the offline queue
}

/**
 * Raw gameplay event. `type` must be one of Config\Gelita::$eventTypes.
 */
export function emit(type, { levelId, nodeId, attemptId, itemId, payload } = {}) {
  // Without a game session (screens before login) the server always refuses: do not queue
  if (!CONFIG.sessionTag) return;
  gameEvents.add({
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
  transport.schedule();
}

/**
 * Structured audio telemetry:
 * { audio_asset_id, action: play|pause|replay|complete, play_index,
 *   listened_ms, completed, attempt_id? }
 */
export function emitAudio(event) {
  if (!CONFIG.sessionTag) return;
  audioEvents.add({
    client_event_id: uuid(),
    occurred_at: now(),
    ...event,
  });
  transport.schedule();
}

/**
 * Sends now. Without `force` it returns the running send (both channels in
 * one request); with `force` (tab hidden, page closed) it beacons each
 * channel to its own endpoint, so a server of any version keeps the audio.
 */
export async function flush(force = false) {
  await transport.flush(force);
}

/** Re-sends this session's offline queue now (back online, quiet retry). The server answers "duplicate" for what arrived. */
export async function resendQueued() {
  await transport.resend();
}

/**
 * Page load (R4): the offline queue (mostly copies of the previous page's
 * beacon) joins this page's queue and leaves with its first batch, on the
 * normal timer or at FLUSH_AT, instead of a request of its own.
 */
export function restoreQueued() {
  if (!CONFIG.sessionTag) return;
  for (const channel of channels) channel.restore();
  transport.schedule();
}

/** performance.now() of the last request the server accepted (-Infinity before any); game/hud.js skips its heartbeat after one. */
export const lastBatchAt = () => transport.lastAccepted;

document.addEventListener('visibilitychange', () => {
  if (document.visibilityState === 'hidden') flush(true);
});
window.addEventListener('pagehide', () => flush(true));

/**
 * Screen code (Dok 1 §12.4) from <body data-screen-code> (rendered by the
 * layout with the same resolver as the server's screen_viewed). The locked
 * library is recognised from its page, as in ScreenViewFilter.
 */
export const screenName = () => {
  const code = document.body?.dataset.screenCode || null;
  return code === 'library' && document.querySelector('[data-screen="library-locked"]') ? 'library_locked' : code;
};

/** `page_hidden` / `page_visible` from visibilitychange (Dok 3 §7). */
export function initVisibilityTracking() {
  document.addEventListener('visibilitychange', () => {
    emit(document.visibilityState === 'hidden' ? 'page_hidden' : 'page_visible', { payload: { screen: screenName() } });
  });
}
