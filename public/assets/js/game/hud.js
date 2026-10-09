/**
 * HUD: tombol suara, lentera, heartbeat sesi.
 *
 * - Tombol suara → Sfx.setEnabled(); status tersimpan di localStorage
 *   (preferensi tampilan, bukan data penelitian).
 * - Lentera (<details>) dapat dibuka tanpa JavaScript; di sini ditambah
 *   penutupan dengan Escape / klik di luar, dan updateLantern() untuk angka
 *   baru dari respons server (`shards` di /check, /responses, /complete) —
 *   bukan dihitung sendiri.
 * - Heartbeat POST /api/session/heartbeat tiap 60 detik selama tab terlihat.
 * - Language switch (asset package Stage 4, §4.4 L1): the form still posts
 *   to /bahasa and the page reloads under a view transition, but JS texts
 *   switch at once from the two-language config (setLocale()), so a toast or
 *   modal built during the reload is already in the new language. Tapping the
 *   active language does nothing (no request).
 */
import { $ } from '../core/dom.js';
import { Sfx } from '../core/audio.js';
import { apiRequest } from '../core/api.js';
import { CONFIG, t, currentLocale, setLocale } from '../core/config.js';

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
  document.addEventListener('gelita:locale', render);
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

/** Angka serpihan baru dari server (`shards` di respons tantangan). */
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

function initLanguageSwitch() {
  const form = $('form.lang-switch');
  if (!form) return;

  form.addEventListener('submit', (event) => {
    const code = event.submitter?.value;
    if (!code) return;
    if (code === currentLocale()) {
      event.preventDefault();
      return;
    }
    setLocale(code);
    for (const button of form.querySelectorAll('button[name="locale"]')) {
      const active = button.value === code;
      button.classList.toggle('is-active', active);
      button.setAttribute('aria-pressed', active ? 'true' : 'false');
    }
  });
}

export function initHud() {
  initLanguageSwitch();
  initSound();
  initLantern();
  initHeartbeat();
}
