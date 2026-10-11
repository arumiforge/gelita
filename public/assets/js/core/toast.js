/**
 * Short-lived notices. type: info | ok | bad | warn
 *
 * Game (body.game, display plan Phase E): the layer is #toast-layer inside
 * #stage (x 856, y 72, w 400 dp).
 * - One toast at a time, at most 3 waiting; a message identical to the one
 *   showing or the last one waiting is merged into it.
 * - Duration follows text length (1.8–5 s).
 * - Tap the card, or press Esc while no modal is open, to close it.
 * - Toasts do not wait for modals: the layer sits above the modal veil.
 * - `bad` uses role=alert, the others a polite status.
 * - Server flash toasts (components/toast.php) join the same queue on load.
 *
 * Admin panel: up to 3 stacked toasts, each removed after 4 seconds.
 *
 * `requestId` is shown small so a teacher can quote it when reporting a
 * problem; there is never a stack trace or a raw response body.
 */
import { t } from './config.js';

const ADMIN_MAX = 3;
const ADMIN_TTL = 4000;

const QUEUE_MAX = 3;
const MIN_TTL = 1800;
const MAX_TTL = 5000;
const LEAVE_FALLBACK = 600;

let current = null;
const queue = [];

function layer() {
  return document.getElementById('toast-layer');
}

function isGame() {
  return document.body?.classList.contains('game') ?? false;
}

export function toast(message, type = 'info', requestId = null) {
  const host = layer();
  if (!host || !message) return;

  if (!isGame()) {
    adminToast(host, message, type, requestId);
    return;
  }

  const entry = { message: String(message), type, requestId };
  if (current && sameAs(current, entry)) {
    restartTimer(current);
    return;
  }
  if (queue.length && sameAs(queue[queue.length - 1], entry)) return;

  queue.push(entry);
  if (queue.length > QUEUE_MAX) queue.shift();
  if (!current) showNext();
}

/** Time a toast stays: about 60 ms per character, clamped to 1.8–5 s. */
export function toastDuration(message) {
  return Math.min(MAX_TTL, Math.max(MIN_TTL, 1200 + String(message).length * 60));
}

function sameAs(a, b) {
  return a.message === b.message && a.type === b.type;
}

function showNext() {
  const host = layer();
  const entry = queue.shift();
  if (!host || !entry) {
    current = null;
    return;
  }

  const card = buildCard(entry);
  host.append(card);
  current = { ...entry, card, timer: null };
  restartTimer(current);
}

function buildCard({ message, type, requestId }) {
  const card = document.createElement('div');
  card.className = `toast is-${type}`;
  card.title = t('toastClose');
  if (type === 'bad') {
    card.setAttribute('role', 'alert');
  } else {
    card.setAttribute('role', 'status');
    card.setAttribute('aria-live', 'polite');
  }

  const icon = layer()?.querySelector(`template[data-toast-icon="${type}"]`)
    ?? layer()?.querySelector('template[data-toast-icon="info"]');
  if (icon) card.append(icon.content.cloneNode(true));

  const text = document.createElement('span');
  text.className = 'toast-text';
  text.textContent = message;
  if (requestId) {
    const small = document.createElement('small');
    small.textContent = `request_id: ${requestId}`;
    text.append(small);
  }
  card.append(text);

  card.addEventListener('click', () => dismissToast());
  return card;
}

function restartTimer(entry) {
  clearTimeout(entry.timer);
  entry.timer = setTimeout(() => dismissToast(), toastDuration(entry.message));
}

/** Close the toast that is showing (fade upward) and show the next one. */
export function dismissToast() {
  if (!current) return;
  const { card, timer } = current;
  clearTimeout(timer);
  current = null;

  let done = false;
  const finish = () => {
    if (done) return;
    done = true;
    card.remove();
    if (!current) showNext();
  };
  card.classList.add('is-leaving');
  card.addEventListener('animationend', (event) => {
    if (event.target === card) finish();
  });
  setTimeout(finish, LEAVE_FALLBACK);
}

function modalOpen() {
  const modals = document.getElementById('modal-layer');
  return Boolean(modals && !modals.hidden && modals.childElementCount);
}

function onKey(event) {
  // A modal that closed on this Esc has already called preventDefault()
  if (event.key !== 'Escape' || event.defaultPrevented || !current || modalOpen()) return;
  dismissToast();
}

/** Server flash toasts leave the DOM and join the queue, in page order. */
function adoptFlash(host) {
  host.querySelectorAll('.toast.is-flash').forEach((node) => {
    const type = node.dataset.toastType || 'info';
    const message = node.querySelector('.toast-text')?.textContent.trim() ?? node.textContent.trim();
    node.remove();
    toast(message, type);
  });
}

function initGameToasts() {
  const host = layer();
  if (!host || !isGame()) return;
  // Each card carries its own role, so `bad` can be an alert
  host.removeAttribute('role');
  host.removeAttribute('aria-live');
  document.addEventListener('keydown', onKey);
  adoptFlash(host);
}

function adminToast(host, message, type, requestId) {
  // Server flash toasts that have faded are not counted
  const live = Array.from(host.children).filter((node) => !node.classList.contains('is-flash'));
  for (let i = 0; i <= live.length - ADMIN_MAX; i++) live[i].remove();

  const item = document.createElement('div');
  item.className = `toast is-${type}`;

  const text = document.createElement('span');
  text.textContent = message;
  if (requestId) {
    const small = document.createElement('small');
    small.textContent = `request_id: ${requestId}`;
    text.append(small);
  }
  item.append(text);

  host.append(item);
  setTimeout(() => item.remove(), ADMIN_TTL);
}

if (typeof document !== 'undefined') {
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initGameToasts, { once: true });
  } else {
    initGameToasts();
  }
}
