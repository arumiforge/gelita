/**
 * Short labels for icon buttons (owner, 16 November 2026): a grade 5 student
 * sees what ◀, ⏸/▶, ↻, the sound button, or the menu does before pressing it.
 *
 * Any element with `data-tip` gets one: its text, or the element's
 * aria-label when `data-tip` is empty (so a label that changes, such as the
 * narration toggle's Putar/Jeda, is always current). Shown
 * - on mouse or pen hover, after a short wait;
 * - on keyboard focus (:focus-visible);
 * - on touch by a long press (LONG_PRESS_MS). The press that showed the label
 *   does not also click the button, so a student can look without acting.
 *
 * Visual only: the bubble is aria-hidden, because the buttons already carry
 * their aria-label. Nothing is logged (the label is not research data).
 * One bubble for the page, placed above the element (below when there is no
 * room) and kept inside the viewport.
 */

const HOVER_MS = 350;
const LONG_PRESS_MS = 450;
const TOUCH_HOLD_MS = 1600;
const GAP = 8;

let bubble = null;
let owner = null;
let showTimer = 0;
let hideTimer = 0;
let pressTimer = 0;
let swallowClick = null;

const tipOf = (el) => (el.dataset.tip || el.getAttribute('aria-label') || '').trim();
const targetOf = (event) => (event.target instanceof Element ? event.target.closest('[data-tip]') : null);

function ensureBubble() {
  if (bubble) return bubble;
  bubble = document.createElement('div');
  bubble.className = 'ui-tip';
  bubble.setAttribute('aria-hidden', 'true');
  bubble.hidden = true;
  document.body.append(bubble);
  return bubble;
}

function place(el) {
  const tip = ensureBubble();
  const rect = el.getBoundingClientRect();
  const width = tip.offsetWidth;
  const height = tip.offsetHeight;
  const left = Math.min(Math.max(GAP, rect.left + rect.width / 2 - width / 2), window.innerWidth - width - GAP);
  const above = rect.top - height - GAP;
  const below = rect.bottom + GAP;
  const top = above >= GAP || below + height > window.innerHeight - GAP ? Math.max(GAP, above) : below;
  tip.classList.toggle('is-below', top === below);
  tip.style.left = `${Math.round(left)}px`;
  tip.style.top = `${Math.round(top)}px`;
  tip.style.setProperty('--tip-arrow', `${Math.round(rect.left + rect.width / 2 - left)}px`);
}

function show(el) {
  clearTimeout(showTimer);
  clearTimeout(hideTimer);
  const text = tipOf(el);
  if (!text || el.hidden || el.closest('[hidden]')) return;
  const tip = ensureBubble();
  owner = el;
  tip.textContent = text;
  tip.hidden = false;
  place(el);
  tip.classList.add('is-shown');
}

function hide() {
  clearTimeout(showTimer);
  clearTimeout(pressTimer);
  owner = null;
  if (!bubble) return;
  bubble.classList.remove('is-shown');
  bubble.hidden = true;
}

export function initTips() {
  if (document.documentElement.dataset.tips === '1') return;
  document.documentElement.dataset.tips = '1';

  document.addEventListener('pointerover', (event) => {
    if (event.pointerType === 'touch') return;
    const el = targetOf(event);
    if (!el || el === owner) return;
    clearTimeout(showTimer);
    showTimer = setTimeout(() => show(el), HOVER_MS);
  });
  document.addEventListener('pointerout', (event) => {
    if (event.pointerType === 'touch') return;
    const el = targetOf(event);
    if (!el || (event.relatedTarget instanceof Node && el.contains(event.relatedTarget))) return;
    if (el === owner || !owner) hide();
  });

  document.addEventListener('focusin', (event) => {
    const el = targetOf(event);
    if (el && el.matches(':focus-visible')) show(el);
  });
  document.addEventListener('focusout', (event) => {
    if (targetOf(event) === owner) hide();
  });

  // Touch: a long press shows the label and eats the click that follows it
  document.addEventListener('pointerdown', (event) => {
    swallowClick = null;
    if (event.pointerType !== 'touch') {
      hide();
      return;
    }
    const el = targetOf(event);
    clearTimeout(pressTimer);
    if (!el) return;
    pressTimer = setTimeout(() => {
      show(el);
      swallowClick = el;
    }, LONG_PRESS_MS);
  });
  const release = () => {
    clearTimeout(pressTimer);
    if (swallowClick) hideTimer = setTimeout(hide, TOUCH_HOLD_MS);
  };
  document.addEventListener('pointerup', release);
  document.addEventListener('pointercancel', release);
  document.addEventListener('click', (event) => {
    if (!swallowClick) return;
    const el = targetOf(event);
    if (el === swallowClick) {
      event.preventDefault();
      event.stopImmediatePropagation();
    }
    swallowClick = null;
  }, true);
  document.addEventListener('contextmenu', (event) => {
    if (targetOf(event)) event.preventDefault();
  });

  window.addEventListener('scroll', hide, { passive: true, capture: true });
  window.addEventListener('resize', hide);
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') hide();
  });
}
