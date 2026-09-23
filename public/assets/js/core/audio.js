/**
 * Dua peran terpisah:
 *
 * 1. Sfx — efek suara & musik latar lewat Howler (vendor/howler.min.js).
 *    Berkas suara opsional: bila Howler/berkas tidak ada, aplikasi tetap
 *    berjalan tanpa bunyi — tidak ada nada sintetis pengganti.
 *
 * 2. Narasi — elemen <audio> bawaan per .audio-player + telemetry ke
 *    /api/audio-events (play, pause, replay, complete). Melanjutkan dari
 *    jeda tidak dikirim sebagai `play` baru: masih pemutaran yang sama, dan
 *    waktu dengarnya ikut terhitung pada pause/complete berikutnya. `listened_ms` adalah
 *    akumulasi waktu putar sungguhan (jam dinding selama benar-benar
 *    berbunyi), bukan `duration`; server tetap memakai duration_ms aset dari
 *    database sebagai pembagi.
 *
 * Kebijakan autoplay: tidak ada yang berbunyi sebelum interaksi pengguna.
 */
import { Storage } from './storage.js';
import { emitAudio } from './events.js';
import { t } from './config.js';
import { $, el } from './dom.js';

// ------------------------------------------------------------------ Sfx

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
    if (!enabled) {
      this.stopMusic();
      stopAllNarration();
    }
  },

  isEnabled() {
    return enabled;
  },
};

// ------------------------------------------------------------- Narasi

const players = new Set();

function stopAllNarration(except = null) {
  for (const player of players) {
    if (player !== except) player.pause();
  }
}

class NarrationPlayer {
  constructor(root, attemptId) {
    this.root = root;
    this.assetId = Number(root.dataset.audioId) || 0;
    this.src = root.dataset.src || '';
    this.attemptId = attemptId;
    this.audio = null;
    this.playIndex = 0;
    this.listenedMs = 0;
    this.playingSince = null;
    this.completed = false;

    this.btnPlay = $('[data-action="play"]', root);
    this.btnPause = $('[data-action="pause"]', root);
    this.btnReplay = $('[data-action="replay"]', root);
    this.bar = $('.audio-bar i', root);

    this.btnPlay?.addEventListener('click', () => this.play());
    this.btnPause?.addEventListener('click', () => this.pause());
    this.btnReplay?.addEventListener('click', () => this.replay());
  }

  element() {
    if (this.audio) return this.audio;

    const audio = new Audio();
    audio.preload = 'none';
    audio.src = this.src;

    audio.addEventListener('playing', () => {
      this.playingSince = performance.now();
      this.toggle(true);
    });
    audio.addEventListener('pause', () => {
      this.accumulate();
      this.toggle(false);
    });
    audio.addEventListener('waiting', () => this.accumulate());
    audio.addEventListener('timeupdate', () => this.progress());
    audio.addEventListener('ended', () => {
      this.accumulate();
      this.completed = true;
      this.toggle(false);
      this.progress(1);
      this.send('complete');
    });
    audio.addEventListener('error', () => this.fail());

    this.audio = audio;
    return audio;
  }

  accumulate() {
    if (this.playingSince !== null) {
      this.listenedMs += performance.now() - this.playingSince;
      this.playingSince = null;
    }
  }

  send(action) {
    if (!this.assetId) return;
    emitAudio({
      audio_asset_id: this.assetId,
      action,
      play_index: this.playIndex,
      listened_ms: Math.round(this.listenedMs),
      completed: this.completed,
      attempt_id: this.attemptId,
    });
  }

  /** @param {'play'|'replay'|null} action null = lanjut dari jeda (tidak dikirim: masih pemutaran yang sama) */
  async start(action) {
    if (!Sfx.isEnabled()) {
      this.root.querySelector('details.audio-transcript')?.setAttribute('open', '');
      return;
    }

    const audio = this.element();
    stopAllNarration(this);
    this.root.classList.remove('is-attention');

    try {
      await audio.play();
      if (action) this.send(action);
    } catch (error) {
      // NotAllowedError: kebijakan autoplay — tombol tetap dapat ditekan lagi
      if (error?.name !== 'NotAllowedError' && error?.name !== 'AbortError') this.fail();
    }
  }

  play() {
    const audio = this.element();
    const fresh = this.playIndex === 0 || audio.ended || this.completed;

    if (!fresh) return this.start(null); // lanjut dari jeda
    if (this.playIndex > 0) return this.replay();
    this.playIndex = 1;
    this.listenedMs = 0;
    this.completed = false;
    return this.start('play');
  }

  pause() {
    if (!this.audio || this.audio.paused) return;
    this.audio.pause();
    this.accumulate(); // event 'pause' browser datang belakangan; hitung waktu dengar sekarang
    this.send('pause');
  }

  replay() {
    const audio = this.element();
    this.accumulate();
    audio.pause();
    audio.currentTime = 0;
    this.playIndex += 1;
    this.listenedMs = 0;
    this.completed = false;
    this.progress(0);
    return this.start(this.playIndex === 1 ? 'play' : 'replay');
  }

  toggle(playing) {
    if (this.btnPlay) this.btnPlay.hidden = playing;
    if (this.btnPause) this.btnPause.hidden = !playing;
    this.root.classList.toggle('is-playing', playing);
    if (playing) this.btnPause?.focus({ preventScroll: true });
  }

  progress(ratio = null) {
    if (!this.bar) return;
    const audio = this.audio;
    const value = ratio ?? (audio && audio.duration > 0 ? audio.currentTime / audio.duration : 0);
    this.bar.style.width = `${Math.min(100, Math.max(0, value * 100))}%`;
  }

  /** Berkas gagal dimuat: sembunyikan tombol, buka transkrip, beri tahu dengan teks. */
  fail() {
    this.accumulate();
    this.root.classList.add('is-error');
    $('.audio-controls', this.root)?.setAttribute('hidden', '');
    const details = $('details.audio-transcript', this.root);
    details?.setAttribute('open', '');
    if (!$('.audio-error', this.root)) {
      this.root.prepend(el('p', { class: 'audio-error', role: 'status' }, t('audioNoSound')));
    }
  }
}

/**
 * Pasang perilaku pada setiap .audio-player[data-src] di dalam root.
 * `attemptId` ikut dikirim bila pemutar berada di layar tantangan.
 */
export function initAudioPlayers(root = document, { attemptId = null, attention = false } = {}) {
  for (const node of root.querySelectorAll('.audio-player[data-src]')) {
    if (node.dataset.ready) continue;
    node.dataset.ready = '1';
    const player = new NarrationPlayer(node, attemptId);
    players.add(player);
    if (attention) node.classList.add('is-attention');
  }
}

/** Hentikan narasi yang sedang berbunyi (mis. saat berganti slide). */
export function pauseNarration() {
  stopAllNarration();
}
