/**
 * Peta Kedu & peta wilayah.
 *
 * - Titik terkunci → toast, bukan navigasi (server tetap menolak bila tautan
 *   dibuka langsung).
 * - Pra-muat gambar latar wilayah/pos yang terbuka ([data-preload]) agar
 *   perpindahan halaman mulus.
 * - Peta wilayah: event `level_opened` { levelId }.
 * - Peta wilayah: tombol "Pustaka {wilayah}" yang masih terkunci
 *   ([data-library-locked]) membuka modal penjelasan + progres, bukan
 *   berpindah halaman. Tanpa JavaScript tautannya menuju halaman terkunci.
 */
import { $, $$, on } from '../core/dom.js';
import { toast } from '../core/toast.js';
import { showModal } from '../core/modal.js';
import { emit } from '../core/events.js';
import { Sfx } from '../core/audio.js';

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
  } else {
    Sfx.music('map');
  }
}
