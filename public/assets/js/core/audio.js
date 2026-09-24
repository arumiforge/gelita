/**
 * Dua peran terpisah:
 *
 * 1. Sfx — efek suara & musik latar lewat Howler (vendor/howler.min.js).
 *    Berkas suara opsional: bila Howler/berkas tidak ada, aplikasi tetap
 *    berjalan tanpa bunyi — tidak ada nada sintetis pengganti.
 *
 * 2. Narasi — elemen <audio> bawaan per .audio-player + telemetry ke
 *    /api/audio-events (play, autoplay, pause, replay, complete). Melanjutkan
 *    dari jeda tidak dikirim sebagai `play` baru: masih pemutaran yang sama,
 *    dan waktu dengarnya ikut terhitung pada pause/complete berikutnya.
 *    `listened_ms` adalah akumulasi waktu putar sungguhan (jam dinding selama
 *    benar-benar berbunyi), bukan `duration`; server tetap memakai
 *    duration_ms aset dari database sebagai pembagi.
 *
 *    `play` = pemain menekan tombol putar. `autoplay` = layar bernarasi
 *    (game/narrator.js) memutar slide sendiri setelah kartu "Ketuk untuk
 *    mulai" atau saat maju otomatis. Server menghitung keduanya terpisah
 *    (audio_usage_events.action), agar peneliti dapat membedakannya.
 *
 * Kebijakan autoplay: tidak ada yang berbunyi sebelum interaksi pengguna.
 * NarrationPlayer#autoplay() menolak berbunyi sebelum Sfx.unlock().
 *
 * Narasi bersama (`shared`): layar bernarasi memakai SATU elemen <audio>
 * untuk semua slidenya. Safari iOS/iPadOS hanya mengizinkan play() tanpa
 * ketukan pada elemen yang pernah diputar di dalam ketukan; elemen baru per
 * slide akan ditolak. Sfx.unlock() "membuka" elemen bersama itu dengan
 * memutar hening sesaat, jadi harus dipanggil di dalam handler ketukan.
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

// WAV hening 10 ms (8 kHz, 8-bit mono): cukup untuk membuka elemen bersama di dalam ketukan
const SILENT = 'data:audio/wav;base64,UklGRnQAAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YVAAAACAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgA==';
let sharedAudio = null;
let sharedOwner = null;
let sharedPrimed = false;

function sharedElement() {
  if (!sharedAudio) {
    sharedAudio = new Audio();
    sharedAudio.preload = 'none';
  }
  return sharedAudio;
}

/** Putar hening pada elemen bersama selagi masih di dalam ketukan pengguna. */
function primeShared() {
  if (sharedPrimed || sharedOwner) return;
  const audio = sharedElement();
  audio.src = SILENT;
  const attempt = audio.play();
  sharedPrimed = true;
  attempt?.then?.(() => {
    if (!sharedOwner) audio.pause();
  }).catch(() => {
    // Ditolak (belum dalam ketukan) atau diganti narasi sungguhan: coba lagi pada ketukan berikutnya
    if (!sharedOwner) sharedPrimed = false;
  });
}
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
  /**
   * Dipanggil pada interaksi pengguna. Aman dipanggil berulang: kartu
   * "Ketuk untuk mulai" dan tirai memanggilnya lagi di dalam handler klik
   * agar elemen narasi bersama ikut terbuka (lihat primeShared()).
   */
  unlock() {
    unlocked = true;
    if (hasHowler()) window.Howler.volume(volume);
    primeShared();
  },

  isUnlocked() {
    return unlocked;
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
const playersByNode = new WeakMap();

function stopAllNarration(except = null) {
  for (const player of players) {
    if (player !== except) player.pause();
  }
}

/**
 * Opsi:
 *   autoplay  langsung memutar dari awal saat dibuat (action `autoplay`),
 *             hanya bila audio sudah dibuka oleh interaksi pengguna
 *   shared    memakai elemen <audio> bersama (layar bernarasi)
 *   onEnded   dipanggil setelah audio selesai (sesudah telemetry `complete`)
 *   onError   dipanggil bila berkas gagal dimuat
 *   onState   dipanggil (playing: boolean) setiap kali mulai/berhenti berbunyi
 */
class NarrationPlayer {
  constructor(root, attemptId, { autoplay = false, shared = false, onEnded = null, onError = null, onState = null } = {}) {
    this.root = root;
    this.assetId = Number(root.dataset.audioId) || 0;
    this.src = root.dataset.src || '';
    this.attemptId = attemptId;
    this.audio = null;
    this.playIndex = 0;
    this.listenedMs = 0;
    this.playingSince = null;
    this.completed = false;
    this.shared = shared;
    this.onEnded = onEnded;
    this.onError = onError;
    this.onState = onState;
    this.focusOnPlay = true;

    this.btnPlay = $('[data-action="play"]', root);
    this.btnPause = $('[data-action="pause"]', root);
    this.btnReplay = $('[data-action="replay"]', root);
    this.bar = $('.audio-bar i', root);

    this.btnPlay?.addEventListener('click', () => this.play());
    this.btnPause?.addEventListener('click', () => this.pause());
    this.btnReplay?.addEventListener('click', () => this.replay());

    if (autoplay) this.autoplay();
  }

  /** Elemen bersama sedang dipegang pemutar lain → event-nya bukan milik pemutar ini. */
  owns() {
    return !this.shared || sharedOwner === this;
  }

  get isPlaying() {
    return Boolean(this.audio && this.owns() && !this.audio.paused && !this.audio.ended);
  }

  element() {
    if (this.shared) return this.claimShared();
    if (this.audio) return this.audio;

    const audio = new Audio();
    audio.preload = 'none';
    audio.src = this.src;
    this.listen(audio);
    this.audio = audio;
    return audio;
  }

  /** Ambil alih elemen bersama; pemutar sebelumnya dijeda dulu (telemetry `pause`-nya tetap terkirim). */
  claimShared() {
    const audio = sharedElement();

    if (sharedOwner !== this) {
      sharedOwner?.pause();
      sharedOwner = this;
      audio.src = this.src;
    }

    if (!this.audio) {
      this.listen(audio);
      this.audio = audio;
    }

    return audio;
  }

  listen(audio) {
    audio.addEventListener('playing', () => {
      if (!this.owns()) return;
      this.playingSince = performance.now();
      this.toggle(true);
    });
    audio.addEventListener('pause', () => {
      if (!this.owns()) return;
      this.accumulate();
      this.toggle(false);
    });
    audio.addEventListener('waiting', () => {
      if (this.owns()) this.accumulate();
    });
    audio.addEventListener('timeupdate', () => {
      if (this.owns()) this.progress();
    });
    audio.addEventListener('ended', () => {
      if (!this.owns()) return;
      this.accumulate();
      this.completed = true;
      this.toggle(false);
      this.progress(1);
      this.send('complete');
      this.onEnded?.(this);
    });
    audio.addEventListener('error', () => {
      // src hening dari primeShared() bukan milik narasi mana pun
      if (this.owns() && audio.src && !audio.src.startsWith('data:')) this.fail();
    });
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

  /**
   * @param {'play'|'autoplay'|'replay'|null} action null = lanjut dari jeda (tidak dikirim: masih pemutaran yang sama)
   * @returns {Promise<boolean>} true bila audio benar-benar mulai berbunyi
   */
  async start(action) {
    if (!Sfx.isEnabled()) {
      this.root.querySelector('details.audio-transcript')?.setAttribute('open', '');
      return false;
    }

    stopAllNarration(this);
    const audio = this.element();
    this.root.classList.remove('is-attention');
    this.focusOnPlay = action !== 'autoplay';

    try {
      await audio.play();
      if (action) this.send(action);
      return true;
    } catch (error) {
      // NotAllowedError: kebijakan autoplay — tombol tetap dapat ditekan lagi
      if (error?.name !== 'NotAllowedError' && error?.name !== 'AbortError') this.fail();
      return false;
    }
  }

  /**
   * Putar dari awal tanpa tombol, untuk layar bernarasi. Tidak berbunyi
   * sebelum interaksi pengguna, saat suara dimatikan, atau tanpa berkas.
   *
   * @returns {Promise<boolean>}
   */
  autoplay() {
    if (!unlocked || !enabled || !this.src || this.root.classList.contains('is-error')) return Promise.resolve(false);
    return this.restart('autoplay');
  }

  play() {
    // Elemen bersama sempat dipakai slide lain: posisi lama hilang, mulai dari awal
    if (this.shared && sharedOwner !== this && this.playIndex > 0) return this.replay();

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
    if (!this.audio || !this.owns() || this.audio.paused) return;
    this.audio.pause();
    this.accumulate(); // event 'pause' browser datang belakangan; hitung waktu dengar sekarang
    this.toggle(false);
    this.send('pause');
  }

  replay() {
    return this.restart(this.playIndex === 0 ? 'play' : 'replay');
  }

  /** @param {'play'|'autoplay'|'replay'} action */
  restart(action) {
    stopAllNarration(this);
    const audio = this.element();
    this.accumulate();
    audio.pause();
    audio.currentTime = 0;
    this.playIndex += 1;
    this.listenedMs = 0;
    this.completed = false;
    this.progress(0);
    return this.start(action);
  }

  toggle(playing) {
    if (this.btnPlay) this.btnPlay.hidden = playing;
    if (this.btnPause) this.btnPause.hidden = !playing;
    this.root.classList.toggle('is-playing', playing);
    if (playing && this.focusOnPlay) this.btnPause?.focus({ preventScroll: true });
    this.onState?.(playing, this);
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
    this.onError?.(this);
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
    playersByNode.set(node, player);
    if (attention) node.classList.add('is-attention');
  }
}

/**
 * Pemutar milik satu .audio-player[data-src] (dibuat bila belum ada), dengan
 * opsi tambahan { shared, onEnded, onError, onState } untuk layar bernarasi. Opsi
 * dipasang sebelum audio pertama berbunyi, jadi aman diubah di sini.
 *
 * @returns {NarrationPlayer|null}
 */
export function narrationPlayer(node, { attemptId = null, ...options } = {}) {
  if (!node?.dataset?.src) return null;

  let player = playersByNode.get(node);
  if (!player) {
    node.dataset.ready = '1';
    player = new NarrationPlayer(node, attemptId);
    players.add(player);
    playersByNode.set(node, player);
  }

  for (const key of ['shared', 'onEnded', 'onError', 'onState']) {
    if (key in options) player[key] = options[key];
  }
  if (options.autoplay) player.autoplay();

  return player;
}

/** Hentikan narasi yang sedang berbunyi (mis. saat berganti slide). */
export function pauseNarration() {
  stopAllNarration();
}
