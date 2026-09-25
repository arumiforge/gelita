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
 *   `dialogue_advanced` (context `region_intro`) pada kunjungan berikutnya.
 *   Tanpa JavaScript overlay adalah target :target #kenal-{code}. Hash
 *   #kenal-{code}[-n] yang sudah ada saat modul siap membuka overlay itu
 *   tanpa suara (▶ menunggu ketukan), lalu hash dibersihkan.
 * - Tautan pin/kartu wilayah memutar tirai wilayah (core/curtain.js).
 */
import { $, $$, on } from '../core/dom.js';
import { toast } from '../core/toast.js';
import { showModal } from '../core/modal.js';
import { emit, flush } from '../core/events.js';
import { Sfx } from '../core/audio.js';
import { playCurtain } from '../core/curtain.js';
import { initNarrator } from './narrator.js';

/** Lapisan halaman yang dinonaktifkan selama overlay Kenali terbuka. */
const PAGE_LAYERS = 'body > .skip-link, body > .hud, body > main, body > .nav-bar';

/**
 * Overlay "Kenali wilayah". Semua pemutar berbagi halaman peta, jadi slide
 * tanpa hash (data-hash="0") dan tanpa pintasan global (data-keyboard="0");
 * tombol panah/Spasi/Esc ditangani di sini hanya selama overlay terbuka.
 *
 * @param {{ stop: () => void } | null} mapStory narasi peta, dihentikan saat overlay dibuka
 * @returns {() => boolean} apakah sebuah overlay Kenali sedang terbuka
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
    flush(); // status "sudah didengar" segera sampai di server, tanpa menunggu antrean 3 detik
    root.classList.remove('is-open');
    document.body.classList.remove('has-kenal');
    for (const node of $$(PAGE_LAYERS)) node.removeAttribute('inert');
    trigger?.focus({ preventScroll: true });
  };

  /** gesture: dibuka ketukan tombol Kenali (bukan dari hash URL saat halaman siap). */
  const show = (code, trigger, { slide = 1, gesture = true } = {}) => {
    const entry = intros.get(code);
    if (!entry) return;
    if (open) close();

    if (gesture) Sfx.unlock(); // di dalam ketukan: elemen narasi bersama terbuka (Safari iPad)
    mapStory?.stop();
    open = { ...entry, trigger };
    entry.root.classList.add('is-open');
    document.body.classList.add('has-kenal');
    for (const node of $$(PAGE_LAYERS)) node.setAttribute('inert', '');

    entry.story.show(slide, { notify: false, focus: false });
    // Tanpa ketukan di halaman ini narasi tidak boleh berbunyi: teks + tombol ▶
    entry.story.start({ userInitiated: gesture || Sfx.isUnlocked() });
    $('.kenal-close', entry.root)?.focus({ preventScroll: true });
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

  // Tombol Kenali diketuk sebelum modul ini siap (perangkat lambat): tautannya
  // sempat berjalan biasa dan hanya mengubah hash #kenal-{code}, sementara
  // html.js menyembunyikan overlay :target. Buka overlay itu sekarang, lalu
  // bersihkan hash. Berlaku juga untuk tautan langsung /peta#kenal-{code}[-n].
  const target = window.location.hash.slice(1);
  for (const code of intros.keys()) {
    const match = target.match(new RegExp(`^kenal-${code}(?:-(\\d+))?$`));
    if (!match) continue;
    window.history.replaceState(window.history.state, '', window.location.pathname + window.location.search);
    show(code, $(`[data-kenal-open="${CSS.escape(code)}"]`), { slide: Number(match[1]) || 1, gesture: false });
    break;
  }

  // Kembali ke peta lewat tombol Back (bfcache): overlay tidak dibiarkan terbuka
  window.addEventListener('pageshow', (event) => { if (event.persisted) close(); });

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

  return () => Boolean(open);
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

  on(screen, 'click', 'a[aria-disabled="true"]', (event, link) => {
    event.preventDefault();
    Sfx.play('lock');
    toast(link.dataset.lockedMessage || link.textContent.trim(), 'warn');
  });

  // Tombol Pustaka ada di nav-bar, di luar <section> layar
  on(document, 'click', 'a[data-library-locked]', (event, link) => {
    event.preventDefault();
    Sfx.play('lock');
    const { lockedTitle, lockedMessage, lockedProgress, lockedOk, indexLabel, indexHref } = link.dataset;
    const buttons = [{ label: lockedOk || 'OK', style: 'primary', value: 'ok' }];
    if (indexHref) buttons.push({ label: indexLabel || indexHref, style: 'quiet', href: indexHref });
    showModal({ type: 'info', icon: 'lock', title: lockedTitle || '', text: [lockedMessage, lockedProgress], buttons });
  });

  preload(screen);

  if (screen.dataset.screen === 'map-level') {
    emit('level_opened', { levelId: Number(screen.dataset.levelId) || null, payload: { code: screen.dataset.level || null } });
    Sfx.music('region');
    return;
  }

  const guide = $('[data-narrator]', screen);
  const story = guide ? initNarrator(guide) : null;

  const introOpen = initRegionIntros(story);

  if ($('[data-curtain-layer="map"]')) {
    playCurtain('map').then(() => {
      Sfx.music('map');
      if (!introOpen()) story?.start(); // overlay Kenali yang terbuka memegang narasi
    });
  } else {
    Sfx.music('map');
  }
}
