/**
 * Tirai — layar pemuatan layar penuh (components/curtain.php).
 *
 *   playCurtain(kind, opts) → Promise<{ loaded, failed, timedOut, skipped }>
 *
 * 1. Memakai tirai [data-curtain-layer="kind"] yang sudah ada di halaman
 *    (tampil sejak paint pertama), atau membuatnya dari
 *    <template id="tpl-curtain-{kind}">. Bila keduanya tidak ada, langsung
 *    selesai ({ skipped: true }) — pemanggil tetap berjalan tanpa tirai.
 * 2. Membatalkan animasi pengaman CSS (tirai memudar sendiri setelah ±10
 *    detik bila modul ini gagal dimuat).
 * 3. Memuat daftar URL (`opts.urls`, bawaannya data-preload) dengan progres
 *    NYATA: berkas cahaya mengikuti jumlah aset yang sudah selesai dimuat,
 *    berhasil atau gagal.
 * 4. Tampil minimal `minMs` (2,5 detik) dan maksimal `maxMs` (8 detik),
 *    dihitung sejak halaman dibuka untuk tirai bawaan halaman. Lewat
 *    `maxMs`, tirai tetap lanjut walau masih ada aset yang belum/gagal.
 * 5. Bila `tap`, menunggu ketukan penutup. Ketukan itu memanggil
 *    Sfx.unlock(), jadi audio halaman (musik, narasi) boleh langsung
 *    berbunyi setelahnya.
 *
 * Tirai dibersihkan saat halaman dipulihkan dari bfcache (`pageshow`
 * persisted), agar tombol Back tidak menampilkan tirai yang macet.
 *
 * Jenis baru (Tahap 3: `region`, `challenge`) cukup menambah markup/template
 * dan gaya `.curtain-{kind}`; tautan `a[data-curtain="{kind}"]` memutar
 * tirainya sebelum berpindah halaman (initCurtains()).
 */
import { $, $$ } from './dom.js';
import { Sfx } from './audio.js';

const MIN_MS = 2500;
const MAX_MS = 8000;
const STATUS_MS = 1800;
const FADE_MS = 600;

const reducedMotion = () => window.matchMedia?.('(prefers-reduced-motion: reduce)').matches ?? false;
const wait = (ms) => new Promise((resolve) => { setTimeout(resolve, Math.max(0, ms)); });

function parseList(value) {
  try {
    const list = JSON.parse(value || '[]');
    return Array.isArray(list) ? list.filter((item) => typeof item === 'string' && item !== '') : [];
  } catch {
    return [];
  }
}

function findLayer(kind) {
  const existing = $(`[data-curtain-layer="${CSS.escape(kind)}"]`);
  if (existing) return { layer: existing, fromPage: true };

  const template = document.getElementById(`tpl-curtain-${kind}`);
  const layer = template?.content?.firstElementChild?.cloneNode(true);
  if (!layer) return { layer: null, fromPage: false };

  layer.dataset.curtainTemp = '1';
  document.body.append(layer);
  return { layer, fromPage: false };
}

/** Satu aset: gambar lewat Image(), selain itu fetch biasa (masuk cache HTTP). */
function loadOne(url) {
  if (/\.(png|jpe?g|webp|gif|svg|avif)(\?|#|$)/i.test(url)) {
    return new Promise((resolve) => {
      const img = new Image();
      img.decoding = 'async';
      img.onload = () => resolve(true);
      img.onerror = () => resolve(false);
      img.src = url;
    });
  }

  return fetch(url, { credentials: 'same-origin' })
    .then((response) => (response.ok ? response.blob().then(() => true) : false))
    .catch(() => false);
}

function preload(urls, onStep) {
  let done = 0;
  let failed = 0;
  return Promise.all(urls.map((url) => loadOne(url).then((ok) => {
    done += 1;
    if (!ok) failed += 1;
    onStep(done, urls.length);
  }))).then(() => ({ loaded: done - failed, failed }));
}

/** Lapisan sudah pudar oleh animasi pengaman CSS (JavaScript datang terlambat)? */
function alreadyFaded(layer) {
  const style = window.getComputedStyle(layer);
  return style.visibility === 'hidden' || Number(style.opacity) === 0;
}

function setInert(on) {
  for (const node of $$('body > .hud, body > main, body > .nav-bar')) {
    if (on) node.setAttribute('inert', '');
    else node.removeAttribute('inert');
  }
}

function dismiss(layer, { remove = false } = {}) {
  layer.classList.remove('is-closing');
  layer.hidden = true;
  layer.removeAttribute('aria-busy');
  delete layer.dataset.active;
  setInert(false);
  if (remove) layer.remove();
}

/**
 * @param {string} kind  jenis tirai, mis. 'map'
 * @param {{ urls?: string[], minMs?: number, maxMs?: number, tap?: boolean,
 *           keep?: boolean, onProgress?: (ratio: number) => void }} [opts]
 *   keep: biarkan tirai menutupi halaman saat selesai (sebelum berpindah
 *         halaman); pageshow dari bfcache membersihkannya.
 */
export async function playCurtain(kind, opts = {}) {
  const { layer, fromPage } = findLayer(kind);
  if (!layer) return { loaded: 0, failed: 0, timedOut: false, skipped: true };

  layer.classList.add('is-live'); // batalkan animasi pengaman CSS

  if (fromPage && alreadyFaded(layer)) {
    dismiss(layer);
    return { loaded: 0, failed: 0, timedOut: false, skipped: true };
  }

  const urls = [...new Set(opts.urls ?? parseList(layer.dataset.preload))];
  const minMs = opts.minMs ?? MIN_MS;
  const maxMs = opts.maxMs ?? MAX_MS;
  const tap = opts.tap ?? layer.dataset.tap !== '0';
  // Tirai bawaan halaman sudah tampil sejak halaman dibuka: hitung dari sana
  const origin = fromPage ? 0 : performance.now();
  const elapsed = () => performance.now() - origin;

  layer.hidden = false;
  layer.dataset.active = '1';
  setInert(true);

  const beam = $('[data-curtain-beam]', layer);
  const status = $('[data-curtain-status]', layer);
  const statuses = parseList(layer.dataset.statuses);

  const progress = (ratio) => {
    const value = Math.round(Math.min(1, Math.max(0, ratio)) * 100);
    layer.style.setProperty('--p', String(value / 100));
    beam?.setAttribute('aria-valuenow', String(value));
    opts.onProgress?.(value / 100);
  };
  progress(urls.length ? 0 : 1);

  let statusIndex = 0;
  const rotate = statuses.length > 1 && status
    ? setInterval(() => {
      statusIndex = (statusIndex + 1) % statuses.length;
      status.classList.add('is-swapping');
      setTimeout(() => {
        status.textContent = statuses[statusIndex];
        status.classList.remove('is-swapping');
      }, reducedMotion() ? 0 : 200);
    }, STATUS_MS)
    : null;

  let result = { loaded: 0, failed: 0 };
  const loading = preload(urls, (done, total) => progress(done / total)).then((r) => { result = r; return 'loaded'; });
  const outcome = await Promise.race([
    Promise.all([loading, wait(minMs - elapsed())]).then(() => 'loaded'),
    wait(maxMs - elapsed()).then(() => 'timeout'),
  ]);

  clearInterval(rotate);
  layer.classList.add('is-ready');
  layer.removeAttribute('aria-busy');

  if (tap) await waitForTap(layer);

  if (!opts.keep) await close(layer, { remove: !fromPage });

  return { ...result, timedOut: outcome === 'timeout', skipped: false };
}

function waitForTap(layer) {
  const button = $('[data-curtain-tap]', layer);

  return new Promise((resolve) => {
    const done = (event) => {
      // Tombol lain (bahasa, dsb.) tidak ada di atas tirai; seluruh lapisan boleh diketuk
      event?.preventDefault?.();
      Sfx.unlock(); // masih di dalam ketukan: musik & narasi boleh berbunyi
      layer.removeEventListener('click', done);
      resolve();
    };

    if (button) {
      button.hidden = false;
      button.focus({ preventScroll: true });
    }
    layer.addEventListener('click', done);
  });
}

function close(layer, { remove }) {
  if (reducedMotion()) {
    dismiss(layer, { remove });
    return Promise.resolve();
  }

  layer.classList.add('is-closing');
  return wait(FADE_MS).then(() => dismiss(layer, { remove }));
}

/**
 * Dipanggil sekali oleh game.js: pembersihan bfcache dan delegasi
 * a[data-curtain="{kind}"] (untuk Tahap 3). Tautan tanpa tirai untuk
 * jenisnya berpindah halaman seperti biasa.
 */
export function initCurtains() {
  window.addEventListener('pageshow', (event) => {
    if (!event.persisted) return;
    for (const layer of $$('[data-curtain-layer]')) {
      if (layer.dataset.active) dismiss(layer, { remove: Boolean(layer.dataset.curtainTemp) });
    }
    setInert(false);
  });

  document.addEventListener('click', (event) => {
    if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

    const link = event.target instanceof Element ? event.target.closest('a[data-curtain]') : null;
    if (!link || (link.target && link.target !== '_self')) return;

    const kind = link.dataset.curtain;
    if (!kind || !(document.getElementById(`tpl-curtain-${kind}`) || $(`[data-curtain-layer="${CSS.escape(kind)}"]`))) return;

    event.preventDefault();
    playCurtain(kind, { urls: parseList(link.dataset.curtainPreload), tap: false, keep: true })
      .then(() => { window.location.assign(link.href); });
  });
}
