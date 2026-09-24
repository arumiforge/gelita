/**
 * Pemutar narasi sinematik — dipakai cerita pembuka (/intro), narasi peta
 * (map_intro), dan layar bernarasi Tahap 3: Kenali wilayah (region_intro),
 * dialog wilayah (level_open), wilayah tuntas (level_done), penutup (ending).
 *
 * Markup (lihat game/intro.php dan game/map-kedu.php):
 *
 *   [data-narrator][data-context][data-prefix][data-mode][data-level-id?]
 *                  [data-keyboard="0"?][data-hash="0"?]
 *     ol.slides > li.slide#{prefix}{n}[data-effect][data-character]
 *       .slide-text            teks yang diketik mesin ketik
 *       .audio-player[data-src] narasi slide (tidak ada = teks saja)
 *     [data-narrator-tap]      kartu "Ketuk untuk mulai" (hidden)
 *     [data-narrator-controls] ◀ ⏸/▶ ↻ ▶ + sakelar Otomatis (hidden)
 *     [data-narrator-fx]       lapisan efek layar
 *
 * Mode (`data-mode`):
 *   tap       kartu "Ketuk untuk mulai" menutupi layar; ketukannya membuka
 *             kunci audio (Sfx.unlock) lalu narasi diputar otomatis
 *   external  dimulai pemanggil lewat start() (mis. setelah tirai peta)
 *   manual    teks tampil utuh; tombol ▶ memulai narasi (action `play`)
 *
 * Setelah mulai: tiap slide diketik (ketukan pertama menampilkan teks penuh,
 * ketukan kedua lanjut), audionya diputar otomatis (action `autoplay`), dan
 * bila sakelar Otomatis menyala (preferensi Storage `narrationAuto`, bawaan
 * aktif) slide maju sendiri 1,2 detik setelah audio selesai. Slide tanpa
 * audio yang tersedia/disetujui tetap tampil sebagai teks dan dilanjutkan
 * manual. Setiap perpindahan mengirim `dialogue_advanced` { index, total,
 * context, character } — hanya di sini, jadi modul halaman (dialogue.js,
 * map.js) cukup memakai `onSlide` tanpa mengirim event sendiri.
 *
 * `data-hash="0"`: slide tidak memakai hash URL (slides.js `hash: false`),
 * untuk beberapa pemutar dalam satu halaman (overlay Kenali di peta).
 * Pemutar yang sudah `stop()` boleh `start()` lagi (overlay dibuka ulang).
 *
 * Tanpa JavaScript slide tetap berpindah lewat #{prefix}{n} + CSS :target;
 * kartu ketuk dan kontrol tetap tersembunyi. Dengan prefers-reduced-motion:
 * tanpa mesin ketik, tanpa efek layar, tanpa Ken Burns (CSS).
 */
import { $, $$ } from '../core/dom.js';
import { emit, flush } from '../core/events.js';
import { Sfx, narrationPlayer, pauseNarration } from '../core/audio.js';
import { Storage } from '../core/storage.js';
import { initSlides } from './slides.js';

const ADVANCE_MS = 1200;
const TYPE_MS = 28;           // per karakter: ±35 karakter/detik, lebih cepat dari suara narasi
const EFFECTS = ['fog', 'fog-lift', 'glow', 'flash', 'shake', 'dim'];
const AUTO_PREF = 'narrationAuto';

const reducedMotion = () => window.matchMedia?.('(prefers-reduced-motion: reduce)').matches ?? false;

/**
 * @param {HTMLElement} root [data-narrator]
 * @param {{ onFinish?: () => void,
 *           onSlide?: (index: number, slide: HTMLElement, total: number) => void }} [options]
 *   onFinish: pemain meminta lanjut di slide terakhir;
 *   onSlide: setiap perpindahan slide (sesudah event dikirim), mis. menukar pose tokoh
 * @returns {{ start: (opts?: { userInitiated?: boolean }) => void, stop: () => void,
 *             show: (n: number, opts?: { notify?: boolean, focus?: boolean }) => void,
 *             next: () => void, prev: () => void, advance: () => void,
 *             readonly current: number, readonly total: number, readonly started: boolean } | null}
 */
export function initNarrator(root, { onFinish = null, onSlide = null } = {}) {
  const list = $('ol.slides', root);
  if (!root || !list) return null;

  const prefix = root.dataset.prefix || 'slide-';
  const context = root.dataset.context || 'intro';
  const mode = root.dataset.mode || 'tap';
  const levelId = Number(root.dataset.levelId) || null;
  const slidesEls = $$(':scope > .slide', list);
  const tapCard = $('[data-narrator-tap]', root);
  const controls = $('[data-narrator-controls]', root);
  const fx = $('[data-narrator-fx]', root);
  const btn = (name) => $(`[data-narrator="${name}"]`, root);

  let started = false;
  let paused = false;
  let auto = Storage.getPref(AUTO_PREF, true) !== false;
  let advanceTimer = null;
  let typing = null;       // { node, full, timer, index }

  // ------------------------------------------------------------- slide

  const slides = initSlides({
    list,
    prefix,
    keyboard: root.dataset.keyboard !== '0',
    hash: root.dataset.hash !== '0',
    onChange: (index, slide, total) => {
      emit('dialogue_advanced', {
        levelId,
        payload: { index, total, context, character: slide.dataset.character || null },
      });
      // Slide terakhir = narasi didengar sampai habis (lencana Kenali, analitik):
      // kirim sekarang, jangan menunggu antrean 3 detik atau beacon saat halaman ditutup
      if (index >= total) flush();
      enter(slide);
      onSlide?.(index, slide, total);
    },
  });
  if (!slides) return null;

  const current = () => slidesEls[slides.current - 1];
  const playerOf = (slide) => narrationPlayer($('.audio-player[data-src]', slide), {
    shared: true,
    onEnded: (player) => ended(slide, player),
    onError: () => refresh(),
    onState: () => refresh(),
  });

  root.classList.add('is-live');
  root.dataset.auto = auto ? '1' : '0';

  // ------------------------------------------------------- mesin ketik

  function finishTyping() {
    if (!typing) return false;
    clearInterval(typing.timer);
    typing.node.textContent = typing.full;
    typing.node.classList.remove('is-typing');
    typing = null;
    return true;
  }

  function typewrite(slide) {
    finishTyping();
    const node = $('.slide-text', slide);
    if (!node || reducedMotion()) return;

    const full = node.textContent;
    const shown = document.createElement('span');
    const rest = document.createElement('span');
    const hidden = document.createElement('span');
    shown.setAttribute('aria-hidden', 'true');
    rest.setAttribute('aria-hidden', 'true');
    rest.className = 'tw-rest';
    rest.textContent = full;                // sisa teks tetap memakan tempat → kotak tidak melompat
    hidden.className = 'visually-hidden';   // pembaca layar membaca teks utuh sekali
    hidden.textContent = full;
    node.replaceChildren(hidden, shown, rest);
    node.classList.add('is-typing');

    let index = 0;
    typing = {
      node,
      full,
      timer: setInterval(() => {
        index += 1;
        shown.textContent = full.slice(0, index);
        rest.textContent = full.slice(index);
        if (index >= full.length) finishTyping();
      }, TYPE_MS),
    };
  }

  // ------------------------------------------------------------- efek

  function applyEffect(slide) {
    if (!fx) return;
    fx.className = fx.className.replace(/\bfx-[a-z-]+\b/g, '').trim();
    const effect = slide.dataset.effect;
    if (!effect || !EFFECTS.includes(effect) || reducedMotion()) return;
    void fx.offsetWidth; // mulai ulang animasi CSS efek yang sama berturut-turut
    fx.classList.add(`fx-${effect}`);
  }

  /** Latar slide berikutnya dimuat lebih dulu agar pergantian tidak berkedip. */
  function preloadNext(slide) {
    const next = slide.nextElementSibling;
    const img = next ? $('img.cine-bg', next) : null;
    if (img && img.loading === 'lazy') img.loading = 'eager';
  }

  // ------------------------------------------------------ alur narasi

  function clearAdvance() {
    clearTimeout(advanceTimer);
    advanceTimer = null;
  }

  function enter(slide) {
    clearAdvance();
    finishTyping();
    applyEffect(slide);
    preloadNext(slide);
    root.classList.remove('is-finished');

    if (started) {
      typewrite(slide);
      if (!paused) play(slide, 'auto');
    }
    refresh();
  }

  /** @param {'auto'|'user'} how */
  function play(slide, how) {
    const player = playerOf(slide);
    if (!player) return;
    const attempt = how === 'user' ? player.play() : player.autoplay();
    Promise.resolve(attempt).then(() => refresh());
  }

  function ended(slide, player) {
    refresh();
    if (slide !== current() || paused || !started) return;
    finishTyping();
    if (!auto) return;

    clearAdvance();
    advanceTimer = setTimeout(() => {
      advanceTimer = null;
      if (slide !== current() || paused || player.isPlaying) return;
      if (slides.current < slides.total) slides.show(slides.current + 1, { focus: false });
      else root.classList.add('is-finished'); // slide terakhir: tunggu pemain menekan tombol akhirnya
    }, ADVANCE_MS);
  }

  function next() {
    clearAdvance();
    if (slides.current < slides.total) {
      slides.show(slides.current + 1);
      return;
    }
    if (onFinish) {
      onFinish();
      return;
    }
    const final = $('.slide-nav a.btn-primary:not([href^="#"])', current());
    if (final) final.click();
  }

  function prev() {
    clearAdvance();
    if (slides.current > 1) slides.show(slides.current - 1);
  }

  /** Ketukan/tombol lanjut: teks yang masih diketik ditampilkan penuh dulu, baru lanjut. */
  function advance() {
    if (finishTyping()) return;
    next();
  }

  function start({ userInitiated = false } = {}) {
    if (started) return;
    started = true;
    paused = false;
    root.classList.add('is-started');
    root.classList.remove('is-finished');
    if (tapCard) tapCard.hidden = true;
    const slide = current();
    applyEffect(slide);
    typewrite(slide);
    play(slide, userInitiated ? 'user' : 'auto');
    refresh();
  }

  /** Hentikan narasi; start() berikutnya memulai lagi (overlay yang dibuka ulang). */
  function stop() {
    clearAdvance();
    finishTyping();
    pauseNarration();
    started = false;
    paused = false;
    root.classList.remove('is-started', 'is-playing', 'is-finished');
    if (fx) fx.className = fx.className.replace(/\bfx-[a-z-]+\b/g, '').trim();
    refresh();
  }

  // ------------------------------------------------------------ kontrol

  function refresh() {
    const slide = current();
    const player = slide ? playerOf(slide) : null;
    const hasAudio = Boolean(player) && !$('.audio-player.is-error', slide) && Sfx.isEnabled();
    const playing = Boolean(player?.isPlaying);

    root.classList.toggle('is-playing', playing);
    root.classList.toggle('is-text-only', started && !hasAudio);

    const toggle = btn('toggle');
    if (toggle) {
      toggle.hidden = !hasAudio;
      toggle.setAttribute('aria-pressed', playing ? 'true' : 'false');
      toggle.setAttribute('aria-label', playing ? toggle.dataset.labelPause : toggle.dataset.labelPlay);
      toggle.classList.toggle('is-playing', playing);
    }
    const replay = btn('replay');
    if (replay) replay.hidden = !hasAudio || !started;
    const back = btn('prev');
    if (back) back.disabled = slides.current <= 1;
    const forward = btn('next');
    if (forward) forward.disabled = slides.current >= slides.total && !onFinish;
    const autoBtn = btn('auto');
    if (autoBtn) autoBtn.setAttribute('aria-pressed', auto ? 'true' : 'false');
  }

  btn('toggle')?.addEventListener('click', () => {
    const player = playerOf(current());
    if (!player) return;
    if (!started) {
      Sfx.unlock();
      start({ userInitiated: true });
      return;
    }
    if (player.isPlaying) {
      paused = true;
      clearAdvance();
      finishTyping();
      player.pause();
    } else {
      paused = false;
      play(current(), 'user');
    }
    refresh();
  });

  btn('replay')?.addEventListener('click', () => {
    const player = playerOf(current());
    if (!player) return;
    paused = false;
    clearAdvance();
    typewrite(current());
    Promise.resolve(player.replay()).then(() => refresh());
  });

  btn('prev')?.addEventListener('click', prev);
  btn('next')?.addEventListener('click', next);

  btn('auto')?.addEventListener('click', () => {
    auto = !auto;
    Storage.setPref(AUTO_PREF, auto);
    root.dataset.auto = auto ? '1' : '0';
    refresh();
    // Dinyalakan saat audio slide ini sudah selesai: langsung jadwalkan maju
    const player = playerOf(current());
    if (auto && started && !paused && player?.completed && !player.isPlaying) ended(current(), player);
    if (!auto) clearAdvance();
  });

  // Ketukan di kotak teks: pertama menampilkan teks penuh, kedua lanjut
  root.addEventListener('click', (event) => {
    const target = event.target instanceof Element ? event.target : null;
    if (!target || !started || !target.closest('[data-narrator-advance]')) return;
    if (target.closest('a, button, summary, input, select, textarea, details, .audio-player')) return;
    advance();
  });

  // Spasi/→ saat mengetik: tuntaskan teks dulu (slides.js mengabaikan event yang sudah ditangani)
  if (root.dataset.keyboard !== '0') {
    window.addEventListener('keydown', (event) => {
      if (!typing || event.altKey || event.ctrlKey || event.metaKey) return;
      if (event.target instanceof Element && event.target.closest('input, textarea, select, button, a, summary')) return;
      if (event.key === ' ' || event.key === 'ArrowRight' || event.key === 'Enter') {
        event.preventDefault();
        finishTyping();
      }
    }, true);
  }

  // ------------------------------------------------------------ mulai

  if (controls) controls.hidden = false;

  // Sakelar Otomatis tidak berarti apa-apa bila belum ada satu pun audio narasi
  const anyAudio = slidesEls.some((slide) => $('.audio-player[data-src]', slide));
  root.classList.toggle('has-audio', anyAudio);
  if (btn('auto')) btn('auto').hidden = !anyAudio;

  if (mode === 'tap' && tapCard) {
    tapCard.hidden = false;
    const welcome = $('.welcome-card', root);
    const slot = $('[data-narrator-welcome]', tapCard);
    if (welcome && slot) slot.append(welcome);
    $('button', tapCard)?.focus({ preventScroll: true });
    tapCard.addEventListener('click', () => {
      Sfx.unlock(); // di dalam ketukan: elemen narasi bersama terbuka (Safari iPad)
      start();
    }, { once: true });
  }

  refresh();

  return {
    start,
    stop,
    next,
    prev,
    advance,
    show: (n, opts) => slides.show(n, opts),
    get current() { return slides.current; },
    get total() { return slides.total; },
    get started() { return started; },
  };
}
