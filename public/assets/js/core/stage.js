/**
 * Panggung 16:9 (Dok 3 §1–§2, §7).
 *
 *   initStage()   → mengisi --app-w/--app-h dari ukuran jendela (core/viewport.js
 *                   melakukannya lebih dulu sebelum CSS),
 *                   memasang tombol layar penuh di HUD, dan — bila ada sesi —
 *                   event `viewport_changed`.
 *   stageRect()   → kotak #stage di layar (CSS px).
 *   stageScale()  → lebar panggung ÷ 1280 (1 = 1 dp per px).
 *
 * Seluruh ukuran CSS game dalam rem dan 1rem = lebar panggung ÷ 80, jadi tata
 * letak tidak pernah dihitung di sini. Posisi pointer (seret keping, ketuk
 * benda) cukup memakai getBoundingClientRect(), yang sudah menyertakan skala.
 */
import { emit } from './events.js';

export const STAGE_W = 1280;
export const STAGE_H = 720;

const ROTATE_QUERY = '(orientation: portrait) and (pointer: coarse)';

const stageNode = () => document.getElementById('stage');

/** Kotak #stage di layar; cadangan dari ukuran jendela bila belum ada. */
export function stageRect() {
  const node = stageNode();
  if (node) return node.getBoundingClientRect();
  const w = Math.min(innerWidth, (innerHeight * 16) / 9);
  return { x: (innerWidth - w) / 2, y: (innerHeight - (w * 9) / 16) / 2, width: w, height: (w * 9) / 16 };
}

export const stageScale = () => stageRect().width / STAGE_W;

function updateAppSize() {
  const root = document.documentElement;
  root.style.setProperty('--app-w', `${innerWidth}px`);
  root.style.setProperty('--app-h', `${innerHeight}px`);
}

/**
 * `viewport_changed` (Dok 1 §11.5, Dok 3 §7): saat halaman dibuka dan setiap
 * ukuran/orientasi berubah, termasuk saat layar putar tampil/hilang;
 * debounce 1 detik, paling sering satu event per 5 detik. Payload kini juga
 * memuat ukuran panggung (`stage_w`, `stage_h`, CSS px) dan `scale`.
 */
function initViewportTracking() {
  const rotate = window.matchMedia ? window.matchMedia(ROTATE_QUERY) : null;
  let timer = null;
  let lastSent = 0;
  let lastKey = '';

  const send = () => {
    timer = null;
    const rect = stageRect();
    const payload = {
      w: innerWidth,
      h: innerHeight,
      orientation: screen.orientation?.type || (innerWidth >= innerHeight ? 'landscape' : 'portrait'),
      rotate_gate: Boolean(rotate?.matches),
      stage_w: Math.round(rect.width),
      stage_h: Math.round(rect.height),
      scale: Math.round((rect.width / STAGE_W) * 1000) / 1000,
    };
    const key = JSON.stringify(payload);
    if (key === lastKey) return;
    const wait = 5000 - (Date.now() - lastSent);
    if (lastSent && wait > 0) {
      timer = setTimeout(send, wait);
      return;
    }
    lastKey = key;
    lastSent = Date.now();
    emit('viewport_changed', { payload });
  };

  const schedule = () => {
    if (timer) clearTimeout(timer);
    timer = setTimeout(send, 1000);
  };

  send();
  window.addEventListener('resize', schedule);
  screen.orientation?.addEventListener?.('change', schedule);
  rotate?.addEventListener?.('change', schedule);
}

/** @param {{ track?: boolean }} options track = kirim viewport_changed (hanya bila ada sesi) */
export function initStage({ track = false } = {}) {
  updateAppSize();
  window.addEventListener('resize', updateAppSize);
  window.addEventListener('orientationchange', updateAppSize);
  if (track) initViewportTracking();
}
