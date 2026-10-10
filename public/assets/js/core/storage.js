/**
 * localStorage is ONLY for the temporary event queue and display preferences.
 * It is not a research record: everything of value is already on the server.
 *
 * The queue is split per channel (`events` → /api/events, `audio` →
 * /api/audio-events) and every event is stamped with the `sessionTag` of the
 * game session that produced it. takeAll(tag) only takes the running
 * session's events: classroom computers are shared, and student A's events
 * must never land in student B's session.
 *
 * No silent loss (rencana-paket-aset §7.3): the queue holds up to MAX entries
 * (±300 B each, ±1.5 MB). When it is full, or the browser refuses to store it,
 * the events that could not be kept are counted per session and channel
 * (`gelita.dropped.{channel}`). events.js sends that count with the next batch
 * and shows a warning toast.
 *
 * `sequence_no` continues across page loads per session in sessionStorage
 * (`gelita.seq.{tag}`), so events of one tab can be ordered by
 * (session, sequence_no) without relying on the device clock. The last number
 * is mirrored in the cookie `gelita_seq={tag}.{n}`: the server numbers its own
 * events (the next page's `screen_viewed`) above it, even while the previous
 * page's last events are still on their way in a beacon.
 */
const QUEUE_PREFIX = 'gelita.queue.';
const DROPPED_PREFIX = 'gelita.dropped.';
const SEQUENCE_PREFIX = 'gelita.seq.';
const PREF_KEY = 'gelita.prefs';
export const QUEUE_MAX = 5000;

/** Fallbacks when web storage is blocked (private mode, full quota). */
const memoryDropped = {};
const memorySequence = {};

/** Even touching window.localStorage can throw when storage is blocked. */
const storeOf = (kind) => (kind === 'session' ? window.sessionStorage : window.localStorage);

function read(key, fallback, kind = 'local') {
  try {
    const raw = storeOf(kind).getItem(key);
    return raw ? JSON.parse(raw) : fallback;
  } catch {
    return fallback;
  }
}

/** @returns {boolean} false when the browser refused to store the value */
function write(key, value, kind = 'local') {
  try {
    const store = storeOf(kind);
    if ((Array.isArray(value) && value.length === 0) || value === null) store.removeItem(key);
    else store.setItem(key, JSON.stringify(value));
    return true;
  } catch {
    return false;
  }
}

const list = (channel) => {
  const queue = read(QUEUE_PREFIX + channel, []);
  return Array.isArray(queue) ? queue : [];
};

const reportId = () => window.crypto?.randomUUID?.()
  ?? `${Date.now().toString(36)}-${Math.random().toString(36).slice(2, 12)}`;

function droppedMap(channel) {
  const stored = read(DROPPED_PREFIX + channel, null);
  const map = stored && typeof stored === 'object' && !Array.isArray(stored) ? stored : {};
  for (const [tag, report] of Object.entries(memoryDropped[channel] || {})) {
    if (!map[tag] || map[tag].count < report.count) map[tag] = report;
  }
  return map;
}

function saveDropped(channel, map) {
  memoryDropped[channel] = map;
  write(DROPPED_PREFIX + channel, Object.keys(map).length ? map : null);
}

export const Storage = {
  /**
   * Stores a batch that could not be sent, stamped with its session.
   * Over QUEUE_MAX the oldest entries make room (usually stale events of a
   * session that never came back); every entry that is let go, and the whole
   * batch when the browser refuses to store it, is counted for its own session.
   * `beaconed` entries are copies of events also sent by beacon: they most
   * likely arrived, so letting them go is not counted.
   *
   * @returns {number} events of `tag` that could not be kept
   */
  queueEvents(batch, tag, channel = 'events', beaconed = false) {
    if (!tag || !batch.length) return 0;
    const stamped = batch.map((event) => (beaconed ? { tag, event, b: 1 } : { tag, event }));
    const all = list(channel).concat(stamped);
    const overflow = all.length > QUEUE_MAX ? all.splice(0, all.length - QUEUE_MAX) : [];

    if (!write(QUEUE_PREFIX + channel, all)) {
      // Nothing new was stored (quota full or storage blocked): the batch is lost
      if (beaconed) return 0;
      Storage.addDropped(tag, channel, batch.length);
      return batch.length;
    }

    let mine = 0;
    const lost = {};
    for (const row of overflow) {
      if (!row?.tag || row.b) continue;
      lost[row.tag] = (lost[row.tag] || 0) + 1;
      if (row.tag === tag) mine++;
    }
    for (const [owner, count] of Object.entries(lost)) Storage.addDropped(owner, channel, count);
    return mine;
  },

  /** Takes the pending events of `tag`; other sessions' events stay stored. */
  takeAll(tag, channel = 'events') {
    if (!tag) return [];
    const mine = [];
    const others = [];
    for (const row of list(channel)) {
      if (row && row.tag === tag && row.event) mine.push(row.event);
      else if (row && row.tag && row.event) others.push(row);
    }
    write(QUEUE_PREFIX + channel, others);
    return mine;
  },

  size(tag, channel = 'events') {
    return list(channel).filter((row) => row && row.tag === tag).length;
  },

  /** Counts events of `tag` that were lost; reported with the next batch. */
  addDropped(tag, channel, count) {
    if (!tag || !(count > 0)) return;
    const map = droppedMap(channel);
    const report = map[tag] || { count: 0, id: reportId() };
    map[tag] = { count: report.count + count, id: report.id };
    saveDropped(channel, map);
  },

  /** @returns {{count: number, id: string}|null} the unreported loss of `tag` */
  droppedReport(tag, channel = 'events') {
    if (!tag) return null;
    const report = droppedMap(channel)[tag];
    return report && report.count > 0 ? { count: report.count, id: report.id } : null;
  },

  /**
   * The server accepted `report`. Losses counted after it was taken stay,
   * under a new id, so they are reported once more and never twice.
   */
  clearDropped(tag, channel, report) {
    if (!tag || !report) return;
    const map = droppedMap(channel);
    const current = map[tag];
    if (!current) return;
    const rest = current.id === report.id ? current.count - report.count : current.count;
    if (rest > 0) map[tag] = { count: rest, id: current.id === report.id ? reportId() : current.id };
    else delete map[tag];
    saveDropped(channel, map);
  },

  /**
   * Next `sequence_no` of session `tag`: continues across page loads of this
   * tab and never goes below `floor` (the server's next number for the
   * session when the page was rendered, so a new tab or browser continues
   * after the events already on the server).
   */
  nextSequence(tag, floor = 0) {
    const key = SEQUENCE_PREFIX + (tag || 'none');
    const stored = Number(read(key, 0, 'session')) || 0;
    const last = Math.max(stored, memorySequence[key] || 0, Number(floor) || 0);
    const next = last + 1;
    memorySequence[key] = next;
    write(key, next, 'session');
    if (tag) {
      try {
        const secure = window.location?.protocol === 'https:' ? '; Secure' : '';
        document.cookie = `gelita_seq=${tag}.${next}; path=/; SameSite=Lax${secure}`;
      } catch { /* cookies blocked: the server falls back to its own count */ }
    }
    return next;
  },

  getPref(name, fallback = null) {
    const prefs = read(PREF_KEY, {});
    return prefs && Object.hasOwn(prefs, name) ? prefs[name] : fallback;
  },

  setPref(name, value) {
    const prefs = read(PREF_KEY, {}) || {};
    prefs[name] = value;
    write(PREF_KEY, prefs);
  },
};
