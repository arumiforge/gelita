/**
 * HUD: tombol suara, lentera, heartbeat sesi.
 *
 * - Tombol suara → Sfx.setEnabled(); status tersimpan di localStorage
 *   (preferensi tampilan, bukan data penelitian).
 * - Lentera (<details>) dapat dibuka tanpa JavaScript; di sini ditambah
 *   penutupan dengan Escape / klik di luar, dan updateLantern() untuk angka
 *   baru dari respons server (`shards` di /check, /responses, /complete) —
 *   bukan dihitung sendiri.
 * - Popovers `details[data-popover]` (redesigned HUD language and menu, the
 *   result screen's Detail hasil) also open without JavaScript; here only one
 *   stays open, Escape / outside click / the close button shut it, and focus
 *   returns to its trigger.
 * - Heartbeat POST /api/session/heartbeat every 60 s while the tab is visible,
 *   but only when no event batch reached the server in the last 60 s: a
 *   server with `CONFIG.eventFlush` folds the heartbeat into every batch
 *   (rencana-log-asinkron R3), so only an idle open tab needs its own. A
 *   server without it gets the heartbeat every 60 s as before.
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
import { lastBatchAt, flush } from '../core/events.js';

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

function initPopovers() {
  const popovers = [...document.querySelectorAll('details[data-popover]')];
  if (!popovers.length) return;

  const close = (popover, focus = false) => {
    if (!popover.open) return;
    popover.open = false;
    if (focus) $('summary', popover)?.focus();
  };

  for (const popover of popovers) {
    popover.addEventListener('toggle', () => {
      if (popover.open) popovers.filter((other) => other !== popover).forEach((other) => close(other));
    });
    for (const button of popover.querySelectorAll('[data-popover-close]')) {
      button.hidden = false;
      button.addEventListener('click', () => close(popover, true));
    }
  }

  document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    const open = popovers.find((popover) => popover.open);
    if (open) close(open, true);
  });
  document.addEventListener('click', (event) => {
    for (const popover of popovers) {
      // A click on the menu's scrim lands on the <details> itself, outside its sheet
      const inside = popover.contains(event.target) && event.target !== popover;
      if (popover.open && !inside) close(popover);
    }
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
  // Only inside the game: the HUD carries data-in-game when there is a session
  if (!$('[data-in-game]') || !CONFIG.sessionTag) return;

  let timer = null;
  const folded = Boolean(CONFIG.eventFlush);
  const beat = () => {
    if (folded && performance.now() - lastBatchAt() < HEARTBEAT_MS) return;
    apiRequest('/session/heartbeat', { method: 'POST', body: {}, retry: false }).catch(() => {});
  };

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

// Part 7 (33 §8.2, K11, M-05): Keluar waits for the event queue, at most 5 s
const LOGOUT_FLUSH_MS = 5000;

/**
 * Keluar (`a[href$="/keluar"]`, components/hud-menu.php and the token
 * screen): the queue is sent first, so the last events of this student do
 * not arrive after the session is gone. Then the page moves to /keluar. A
 * remainder stays in this device's queue under this session's tag and goes
 * when the same student signs in here again (core/storage.js). Without
 * JavaScript the link works as before.
 */
function initLogout() {
  document.addEventListener('click', (event) => {
    const link = event.target instanceof Element ? event.target.closest('a[href$="/keluar"]') : null;
    if (!link || !CONFIG.sessionTag || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey) return;
    event.preventDefault();
    if (link.dataset.leaving) return;
    link.dataset.leaving = '1';
    const go = () => { window.location.href = link.href; };
    Promise.race([flush(), new Promise((resolve) => setTimeout(resolve, LOGOUT_FLUSH_MS))]).then(go, go);
  });
}

export function initHud() {
  initLanguageSwitch();
  initSound();
  initLantern();
  initPopovers();
  initHeartbeat();
  initLogout();
}
