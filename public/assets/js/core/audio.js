/**
 * Efek suara & musik (Howler, vendor/howler.min.js).
 * Berkas suara opsional: bila Howler/berkas tidak ada, aplikasi tetap berjalan tanpa bunyi.
 * Tidak ada autoplay sebelum interaksi pengguna (unlock()).
 * Pemutar narasi <audio> + telemetry ditambahkan pada tahap 6.
 */
import { Storage } from './storage.js';

const SFX_BASE = `${(document.body.dataset.base || '/').replace(/\/?$/, '/')}assets/audio/`;
const sounds = new Map();
let music = null;
let unlocked = false;
let enabled = Storage.getPref('sound', true);
let volume = Storage.getPref('volume', 0.8);

const hasHowler = () => typeof window.Howl === 'function';

function load(name, { loop = false, folder = 'sfx' } = {}) {
  if (!hasHowler()) return null;
  const key = `${folder}/${name}`;
  if (!sounds.has(key)) {
    sounds.set(key, new window.Howl({
      src: [`${SFX_BASE}${folder}/${name}.mp3`],
      loop,
      volume,
      preload: true,
      onloaderror: () => sounds.set(key, null), // gagal dimuat → diam
    }));
  }
  return sounds.get(key);
}

export const Sfx = {
  /** Dipanggil pada interaksi pertama pengguna */
  unlock() {
    unlocked = true;
    if (hasHowler()) window.Howler.volume(volume);
  },

  /** correct, wrong, click, shard, region-done, page, lock */
  play(name) {
    if (!unlocked || !enabled) return;
    load(name)?.play();
  },

  /** map, region, challenge, library */
  music(name) {
    if (!unlocked || !enabled) return;
    const next = load(name, { loop: true, folder: 'music' });
    if (next === music) return;
    music?.stop();
    music = next;
    music?.play();
  },

  stopMusic() {
    music?.stop();
    music = null;
  },

  setVolume(v) {
    volume = Math.min(1, Math.max(0, Number(v) || 0));
    Storage.setPref('volume', volume);
    if (hasHowler()) window.Howler.volume(volume);
  },

  setEnabled(on) {
    enabled = Boolean(on);
    Storage.setPref('sound', enabled);
    if (!enabled) this.stopMusic();
  },

  isEnabled() {
    return enabled;
  },
};
