/**
 * HUD: tombol suara, lentera, heartbeat sesi.
 *
 * - Tombol suara → Sfx.setEnabled(); status tersimpan di localStorage
 *   (preferensi tampilan, bukan data penelitian).
 * - Lentera (<details>) dapat dibuka tanpa JavaScript; di sini ditambah
 *   penutupan dengan Escape / klik di luar, dan updateLantern() untuk angka
 *   baru dari respons complete() — bukan dihitung sendiri.
 * - Heartbeat POST /api/session/heartbeat tiap 60 detik selama tab terlihat.
 */
import { $ } from '../core/dom.js';
import { Sfx } from '../core/audio.js';
import { apiRequest } from '../core/api.js';
import { CONFIG, t } from '../core/config.js';

const HEARTBEAT_MS = 60_000;

function initSound() {
  const button = $('#btn-sound');
  if (!button) return;

  const render = () => {
    const on = Sfx.isEnabled();
    button.setAttribute('aria-pressed', on ? 'true' : 'false');
    button.classList.toggle('is-muted', !on);
    button.title = on ? t('soundOn') : t('soundOff');
  };

  button.addEventListener('click', () => {
    Sfx.setEnabled(!Sfx.isEnabled());
    render();
    if (Sfx.isEnabled()) Sfx.play('click');
  });
  render();
}

function initLantern() {
  const lantern = $('details.lantern');
  if (!lantern) return;

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && lantern.open) {
      lantern.open = false;
      $('summary', lantern)?.focus();
    }
  });
  document.addEventListener('click', (event) => {
    if (lantern.open && !lantern.contains(event.target)) lantern.open = false;
  });
}

/** Angka serpihan baru dari server (respons complete()). */
export function updateLantern(shards, total = null) {
  const lantern = $('.lantern');
  if (!lantern || shards === null || shards === undefined) return;

  const max = total ?? (Number(lantern.dataset.total) || 0);
  lantern.dataset.shards = String(shards);
  const count = $('.lantern-count b', lantern);
  if (count) count.textContent = String(shards);
  const bar = $('.lantern-bar i', lantern);
  if (bar && max > 0) bar.style.width = `${Math.round((shards / max) * 100)}%`;
  lantern.classList.add('is-updated');
}

function initHeartbeat() {
  // Hanya di dalam permainan (lentera dirender bila ada sesi)
  if (!$('.lantern') || !CONFIG.sessionTag) return;

  let timer = null;
  const beat = () => apiRequest('/session/heartbeat', { method: 'POST', body: {}, retry: false }).catch(() => {});

  const schedule = () => {
    clearInterval(timer);
    timer = null;
    if (document.visibilityState === 'visible') timer = setInterval(beat, HEARTBEAT_MS);
  };

  document.addEventListener('visibilitychange', schedule);
  schedule();
}

export function initHud() {
  initSound();
  initLantern();
  initHeartbeat();
}
