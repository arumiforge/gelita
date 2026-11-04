/**
 * Peta Kedu & peta wilayah.
 *
 * - Titik terkunci → toast, bukan navigasi (server tetap menolak bila tautan
 *   dibuka langsung).
 * - Pra-muat gambar latar wilayah/pos yang terbuka ([data-preload]) agar
 *   perpindahan halaman mulus.
 * - Peta wilayah: event `level_opened` { levelId }.
 * - Peta Kedu: narasi Jaka `map_intro` di panduan peta (game/narrator.js).
 *   Bila halaman membawa tirai peta (flash `curtain=map`), tirai memuat aset
 *   peta dengan progres nyata; ketukan penutupnya membuka kunci audio, lalu
 *   musik peta dan narasi dimulai otomatis. Tanpa tirai, narasi menunggu ▶.
 * - Peta wilayah: tombol "Pustaka {wilayah}" yang masih terkunci
 *   ([data-library-locked]) membuka modal penjelasan + progres, bukan
 *   berpindah halaman. Tanpa JavaScript tautannya menuju halaman terkunci.
 * - Peta Kedu (Tahap 3): tombol lentera "Kenali {wilayah}"
 *   ([data-kenal-open]) membuka overlay "Mengenal {wilayah}" ([data-kenal]):
 *   narrator mode `external` yang langsung diputar (`userInitiated`, ketukan
 *   tombol itu interaksinya). Esc atau Tutup menghentikan narasi dan
 *   mengembalikan fokus. Mencapai slide terakhir menghapus lencana "Belum
 *   didengar" wilayah itu; server menghitung ulang dari event
 *   `dialogue_completed` (context `region_intro`) pada kunjungan berikutnya.
 *   Tanpa JavaScript overlay adalah target :target #kenal-{code}.
 *   Tombol yang diketuk sebelum modul ini siap hanya sempat mengubah hash
 *   (#kenal-{code} atau #kenal-{code}-{n}); saat siap, hash itu membuka
 *   overlay di slide n tanpa memutar narasi otomatis (belum ada ketukan untuk
 *   membuka kunci audio), lalu hash dibersihkan. Narasi peta tidak dimulai
 *   selama overlay Kenali terbuka.
 * - Tautan pin/kartu wilayah memutar tirai wilayah (core/curtain.js).
 * - Peta Kedu redesign (18 October 2026): a city pin ([data-region-select])
 *   selects its region instead of leaving the page: one region panel
 *   (#region-{code}) is shown and the pin is marked aria-current. Without
 *   JavaScript the pin's #region-{code} link shows the panel through :target.
 *   Lanjut in the panel enters the region (curtain); a locked region's Lanjut
 *   only shows the toast.
 */
import { $, $$, on } from '../core/dom.js';
import { toast } from '../core/toast.js';
import { showModal } from '../core/modal.js';
import { emit, flush } from '../core/events.js';
import { Sfx } from '../core/audio.js';
import { playCurtain } from '../core/curtain.js';
import { initNarrator } from './narrator.js';
import { overlayOpened } from '../core/device.js';

/** Lapisan halaman yang dinonaktifkan selama overlay Kenali terbuka. */
const PAGE_LAYERS = '#stage > .skip-link, #stage > .hud, #stage > main, #stage > .nav-bar';

/**
 * Overlay "Kenali wilayah". Semua pemutar berbagi halaman peta, jadi slide
 * tanpa hash (data-hash="0") dan tanpa pintasan global (data-keyboard="0");
 * tombol panah/Spasi/Esc ditangani di sini hanya selama overlay terbuka.
 *
 * @param {{ stop: () => void } | null} mapStory narasi peta, dihentikan saat overlay dibuka
 * @returns {() => boolean} apakah overlay Kenali sedang terbuka
 */
function initRegionIntros(mapStory) {
  const intros = new Map();
  let open = null;

  const markHeard = (code) => {
    for (const trigger of $$(`[data-kenal-open="${CSS.escape(code)}"]`)) {
      trigger.classList.remove('is-unheard');
      $('[data-kenal-badge]', trigger)?.remove();
    }
  };

  const close = () => {
    if (!open) return;
    const { root, story, trigger } = open;
    open = null;
    story.stop();
    Sfx.music('map');
    flush(); // "heard" status reaches the server now, without waiting for the event timer
    root.classList.remove('is-open');
    document.body.classList.remove('has-kenal');
    for (const node of $$(PAGE_LAYERS)) node.removeAttribute('inert');
    trigger?.focus({ preventScroll: true });
  };

  const show = (code, trigger, { slide = 1, autoplay = true } = {}) => {
    const entry = intros.get(code);
    if (!entry) return;
    if (open) close();
    overlayOpened('kenal', { level: code, levelId: Number(entry.root.dataset.levelId) || null });

    if (autoplay) Sfx.unlock(); // di dalam ketukan: elemen narasi bersama terbuka (Safari iPad)
    mapStory?.stop();
    Sfx.music(entry.root.dataset.music || 'map'); // the region's Kenali track while the overlay is open
    open = { ...entry, trigger };
    entry.root.classList.add('is-open');
    document.body.classList.add('has-kenal');
    for (const node of $$(PAGE_LAYERS)) node.setAttribute('inert', '');

    entry.story.show(slide, { notify: false, focus: false });
    if (autoplay) entry.story.start({ userInitiated: true });
    $('.kenal-back', entry.root)?.focus({ preventScroll: true });
  };

  for (const root of $$('[data-kenal]')) {
    const code = root.dataset.kenal;
    const story = initNarrator(root, {
      onSlide: (index, slide, total) => { if (index >= total) markHeard(code); },
      // ▶ di slide terakhir = tombol akhirnya: "Masuk ke {wilayah}" (tirai) atau Tutup
      onFinish: () => $('.slide.is-current [data-slide-final]', root)?.click(),
    });
    if (story) intros.set(code, { root, story });
  }
  if (!intros.size) return () => false;

  document.addEventListener('click', (event) => {
    const target = event.target instanceof Element ? event.target : null;
    const opener = target?.closest('[data-kenal-open]');
    if (opener) {
      event.preventDefault();
      show(opener.dataset.kenalOpen, opener);
      return;
    }
    if (open && target?.closest('[data-kenal-close]')) {
      event.preventDefault();
      close();
    }
  });

  // Kembali ke peta lewat tombol Back (bfcache): overlay tidak dibiarkan terbuka
  window.addEventListener('pageshow', (event) => { if (event.persisted) close(); });

  // Ketukan "Kenali" sebelum modul siap: buka dari hash, lalu bersihkan hash
  const early = /^#kenal-(.+?)(?:-(\d+))?$/.exec(decodeURIComponent(location.hash || ''));
  if (early) {
    let code = early[1];
    let slide = Number(early[2]) || 1;
    if (!intros.has(code) && intros.has(`${code}-${early[2]}`)) { code = `${code}-${early[2]}`; slide = 1; }
    if (intros.has(code)) {
      const trigger = $(`[data-kenal-open="${CSS.escape(code)}"]`);
      show(code, trigger, { slide, autoplay: false });
      history.replaceState(null, '', location.pathname + location.search);
    }
  }

  document.addEventListener('keydown', (event) => {
    if (!open || event.altKey || event.ctrlKey || event.metaKey) return;
    const inControl = event.target instanceof Element && event.target.closest('a, button, input, select, textarea, summary');

    if (event.key === 'Escape') {
      event.preventDefault();
      close();
    } else if ((event.key === 'ArrowRight' || (event.key === ' ' && !inControl)) && !event.defaultPrevented) {
      event.preventDefault();
      open.story.advance();
    } else if (event.key === 'ArrowLeft') {
      event.preventDefault();
      open.story.prev();
    }
  });

  return () => open !== null;
}

function initRegionSelect(screen) {
  const pins = $$('[data-region-select]', screen);
  const panels = $('.kedu-panels', screen);
  if (!pins.length || !panels) return;

  const select = (code, { sound = true } = {}) => {
    const panel = document.getElementById(`region-${code}`);
    if (!panel || !panels.contains(panel)) return;
    for (const node of $$('.kedu-panel', panels)) node.classList.toggle('is-selected', node === panel);
    for (const pin of pins) {
      if (pin.dataset.regionSelect === code) pin.setAttribute('aria-current', 'true');
      else pin.removeAttribute('aria-current');
    }
    if (sound) Sfx.play(panel.classList.contains('is-locked') ? 'lock' : 'click');
  };

  // From here the class decides, not :target (game.css .kedu-panels.is-live)
  panels.classList.add('is-live');
  on(screen, 'click', '[data-region-select]', (event, pin) => {
    event.preventDefault();
    select(pin.dataset.regionSelect);
  });

  // #region-{code} from a link without JavaScript (e.g. Tutup at the end of Kenali): select it, then drop the hash
  const hashed = /^#region-(.+)$/.exec(decodeURIComponent(location.hash || ''));
  if (hashed) {
    select(hashed[1], { sound: false });
    history.replaceState(null, '', location.pathname + location.search);
  }
}

function preload(root) {
  const urls = new Set($$('[data-preload]', root).map((node) => node.dataset.preload).filter(Boolean));
  const run = () => urls.forEach((src) => { const img = new Image(); img.decoding = 'async'; img.src = src; });
  if ('requestIdleCallback' in window) window.requestIdleCallback(run, { timeout: 2000 });
  else setTimeout(run, 600);
}

export function initMap() {
  const screen = $('[data-screen="map-kedu"], [data-screen="map-level"]');
  if (!screen) return;

  // A locked Pustaka link has its own modal below; no toast on top of it. On the region map
  // a locked pin explains itself in a modal too (owner, 21 October 2026), not a corner toast
  on(screen, 'click', 'a[aria-disabled="true"]:not([data-library-locked])', (event, link) => {
    event.preventDefault();
    Sfx.play('lock');
    const message = link.dataset.lockedMessage || link.textContent.trim();
    if (screen.dataset.screen === 'map-level') {
      const { lockedTitle, lockedOk } = link.dataset;
      showModal({ type: 'info', icon: 'lock', title: lockedTitle || '', text: message, buttons: [{ label: lockedOk || 'OK', style: 'primary', value: 'ok' }] });
      return;
    }
    toast(message, 'warn');
  });

  // Pustaka {wilayah} sits at the bottom of the side column (it used to be in the nav bar)
  on(document, 'click', 'a[data-library-locked]', (event, link) => {
    event.preventDefault();
    Sfx.play('lock');
    const { lockedTitle, lockedMessage, lockedProgress, lockedOk, indexLabel, indexHref } = link.dataset;
    const buttons = [{ label: lockedOk || 'OK', style: 'primary', value: 'ok' }];
    if (indexHref) buttons.push({ label: indexLabel || indexHref, style: 'quiet', href: indexHref });
    showModal({ type: 'info', icon: 'lock', title: lockedTitle || '', text: [lockedMessage, lockedProgress], buttons });
  });

  preload(screen);
  initRegionSelect(screen);

  if (screen.dataset.screen === 'map-level') {
    emit('level_opened', { levelId: Number(screen.dataset.levelId) || null, payload: { code: screen.dataset.level || null } });
    Sfx.music('region');
    return;
  }

  const guide = $('[data-narrator]', screen);
  const story = guide ? initNarrator(guide) : null;

  const kenalOpen = initRegionIntros(story);

  // An overlay opened from the address (#kenal-…) keeps its own track
  const mapMusic = () => { if (!document.body.classList.contains('has-kenal')) Sfx.music('map'); };
  if ($('[data-curtain-layer="map"]')) {
    playCurtain('map').then(() => {
      mapMusic();
      if (!kenalOpen()) story?.start();
    });
  } else {
    mapMusic();
  }
}
