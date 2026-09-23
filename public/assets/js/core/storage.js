/**
 * localStorage HANYA untuk antrean event sementara dan preferensi tampilan.
 * Bukan tempat catatan penelitian — semua yang bernilai sudah ada di server.
 *
 * Antrean dipisah per kanal (`events` → /api/events, `audio` →
 * /api/audio-events) dan setiap event ditandai `sessionTag` sesi permainan
 * pengirimnya. takeAll(tag) hanya mengambil milik sesi yang sedang berjalan:
 * komputer kelas dipakai bergantian, dan event siswa A tidak boleh masuk ke
 * sesi siswa B. Total dibatasi MAX entri terbaru agar tidak menggelembung.
 */
const QUEUE_PREFIX = 'gelita.queue.';
const PREF_KEY = 'gelita.prefs';
const MAX = 500;

function read(key, fallback) {
  try {
    const raw = localStorage.getItem(key);
    return raw ? JSON.parse(raw) : fallback;
  } catch {
    return fallback;
  }
}

function write(key, value) {
  try {
    if (Array.isArray(value) && value.length === 0) localStorage.removeItem(key);
    else localStorage.setItem(key, JSON.stringify(value));
  } catch {
    /* penyimpanan penuh / diblokir: aplikasi tetap berjalan */
  }
}

const list = (channel) => {
  const queue = read(QUEUE_PREFIX + channel, []);
  return Array.isArray(queue) ? queue : [];
};

export const Storage = {
  /** Simpan batch yang gagal terkirim, bertanda sesi pengirimnya. */
  queueEvents(batch, tag, channel = 'events') {
    if (!tag || !batch.length) return;
    const stamped = batch.map((event) => ({ tag, event }));
    write(QUEUE_PREFIX + channel, list(channel).concat(stamped).slice(-MAX));
  },

  /** Ambil event tertunda milik `tag`; milik sesi lain dibiarkan tersimpan. */
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

// Antrean format tahap 2 (tanpa penanda sesi) tidak dapat dipastikan pemiliknya: buang.
try {
  localStorage.removeItem('gelita.eventQueue');
} catch {
  /* abaikan */
}
