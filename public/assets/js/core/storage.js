/**
 * localStorage HANYA untuk antrean event sementara dan preferensi tampilan.
 * Bukan tempat catatan penelitian — semua yang bernilai sudah ada di server.
 */
const QUEUE_KEY = 'gelita.eventQueue';
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
    localStorage.setItem(key, JSON.stringify(value));
  } catch {
    /* penyimpanan penuh / diblokir: aplikasi tetap berjalan */
  }
}

export const Storage = {
  /** Gabung batch ke antrean, simpan hanya MAX event terbaru */
  queueEvents(batch) {
    const queue = read(QUEUE_KEY, []).concat(batch);
    write(QUEUE_KEY, queue.slice(-MAX));
  },

  /** Ambil semua event tertunda lalu kosongkan antrean */
  takeAll() {
    const queue = read(QUEUE_KEY, []);
    try {
      localStorage.removeItem(QUEUE_KEY);
    } catch {
      /* abaikan */
    }
    return Array.isArray(queue) ? queue : [];
  },

  size() {
    const queue = read(QUEUE_KEY, []);
    return Array.isArray(queue) ? queue.length : 0;
  },

  getPref(name, fallback = null) {
    const prefs = read(PREF_KEY, {});
    return Object.hasOwn(prefs, name) ? prefs[name] : fallback;
  },

  setPref(name, value) {
    const prefs = read(PREF_KEY, {});
    prefs[name] = value;
    write(PREF_KEY, prefs);
  },
};
