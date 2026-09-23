/**
 * Peta Kedu & peta wilayah.
 *
 * - Titik terkunci → toast, bukan navigasi (server tetap menolak bila tautan
 *   dibuka langsung).
 * - Pra-muat gambar latar wilayah/pos yang terbuka ([data-preload]) agar
 *   perpindahan halaman mulus.
 * - Peta wilayah: event `level_opened` { levelId }.
 */
import { $, $$, on } from '../core/dom.js';
import { toast } from '../core/toast.js';
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

  preload(screen);

  if (screen.dataset.screen === 'map-level') {
    emit('level_opened', { levelId: Number(screen.dataset.levelId) || null, payload: { code: screen.dataset.level || null } });
    Sfx.music('region');
  } else {
    Sfx.music('map');
  }
}
