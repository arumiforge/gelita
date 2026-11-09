/**
 * Small screens (research revision 10, part 7: 33 §7.2, K7).
 *
 * Measures the 16:9 stage (core/stage.js stageRect()) when the page loads and
 * on `resize` (500 ms debounce):
 * - research mode (CONFIG.researchMode) and a stage narrower than
 *   CONFIG.minStagePx (the study's `min_stage_px`, 960 by default): the gate
 *   components/small-screen.php covers the page like game/rotate-gate.js (the
 *   rest of <body> is `inert`, focus moves to it). It goes away by itself
 *   once the window is wide enough;
 * - otherwise a warning that can be closed, once per browser session.
 * Every form with an `input[name="stage_w"]` (/token, /daftar step 0) gets
 * the measured width, which the server checks too.
 */
import { CONFIG } from '../core/config.js';
import { stageRect } from '../core/stage.js';

const DISMISS_KEY = 'gelita.smallScreenDismissed';

export function isSmallStage(width, minPx) {
  return Number(minPx) > 0 && Number(width) > 0 && Number(width) < Number(minPx);
}

function dismissed() {
  try { return window.sessionStorage.getItem(DISMISS_KEY) === '1'; } catch { return false; }
}

export function initScreenCheck() {
  const gate = document.querySelector('[data-small-screen-gate]');
  const warn = document.querySelector('[data-small-screen-warn]');
  const minPx = Number(CONFIG.minStagePx) || 960;
  const research = Boolean(CONFIG.researchMode);
  let returnFocus = null;
  let blocked = false;

  const block = (on) => {
    if (!gate || on === blocked) return;
    blocked = on;
    gate.hidden = !on;
    for (const el of document.body.children) {
      if (el !== gate && !['SCRIPT', 'TEMPLATE'].includes(el.tagName)) el.inert = on;
    }
    if (on) {
      const active = document.activeElement;
      returnFocus = active && active !== document.body && active !== gate ? active : null;
      gate.focus({ preventScroll: true });
    } else if (returnFocus?.isConnected) {
      returnFocus.focus({ preventScroll: true });
      returnFocus = null;
    }
  };

  const check = () => {
    const width = Math.round(stageRect().width);
    document.querySelectorAll('input[name="stage_w"]').forEach((field) => { field.value = String(width); });
    const small = isSmallStage(width, minPx);
    if (research) {
      block(small);
    } else if (warn) {
      warn.hidden = !small || dismissed();
    }
  };

  warn?.querySelector('[data-small-screen-dismiss]')?.addEventListener('click', () => {
    try { window.sessionStorage.setItem(DISMISS_KEY, '1'); } catch { /* private mode: closes for this page only */ }
    warn.hidden = true;
  });

  let timer = 0;
  window.addEventListener('resize', () => {
    clearTimeout(timer);
    timer = setTimeout(check, 500);
  });
  check();
}
