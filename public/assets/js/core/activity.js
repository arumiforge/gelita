/**
 * Input pauses for active time (research revision 10, part 7: 33 §5.2, K4).
 *
 * One `input_idle` event for every pause of `CONFIG.inputIdleLogMs` (20 s,
 * Config\Gelita::$inputIdleLogMs) or longer between two inputs, with the
 * device time the pause began and the reason it ended. Loaded by game.js on
 * every game screen with a session (not on the test module, 33 §12).
 *
 * - Input: pointerdown, keydown, wheel, scroll (capture: reading panels
 *   scroll inside an element), and pointermove only while a button is
 *   pressed (dragging). A mouse moving without a click is not input.
 * - A hidden page cuts the pause: on `hidden` a running pause of 20 s or
 *   more is closed (`end: hidden`), and the clock restarts when the page is
 *   visible again, so `input_idle` never overlaps a hidden stretch.
 * - Leaving the page (`pagehide`) closes a running pause (`end: pagehide`);
 *   it goes with the beaconed queue.
 * - Narration does not cut a pause here: part 9 removes narration time from
 *   the audio rows, so the client applies no analysis rule.
 * - A new page starts its clock when this module loads; the time between
 *   pages is the server's `screen_viewed`.
 *
 * Listeners on `window` with capture run before core/events.js flushes on
 * `visibilitychange` / `pagehide`, so the closing event leaves with that flush.
 */
import { CONFIG } from './config.js';
import { emit, screenName } from './events.js';
import { createIdleTracker } from './idle-tracker.js';

const INPUTS = ['pointerdown', 'keydown', 'wheel', 'scroll'];
/** Challenge references when the page is a challenge screen; otherwise none. */
function refs() {
  const root = document.querySelector('[data-screen="challenge"]');
  if (!root) return {};
  return {
    levelId: Number(root.dataset.levelId) || null,
    nodeId: Number(root.dataset.node) || null,
    attemptId: Number(root.dataset.attempt) || null,
  };
}

export function initActivity() {
  if (!CONFIG.sessionTag || initActivity.done) return;
  initActivity.done = true;

  const threshold = Number(CONFIG.inputIdleLogMs) || 20000;
  // Device clock at performance.now() = 0, to turn the pause start into ISO time
  const origin = Date.now() - performance.now();
  const tracker = createIdleTracker(threshold, performance.now());

  const log = (pause) => {
    if (!pause) return;
    emit('input_idle', {
      ...refs(),
      payload: {
        idle_ms: pause.idle_ms,
        started_at: new Date(origin + pause.started_ms).toISOString(),
        end: pause.end,
        screen: screenName(),
      },
    });
  };

  const onInput = () => log(tracker.input(performance.now()));
  const opts = { capture: true, passive: true };
  for (const type of INPUTS) window.addEventListener(type, onInput, opts);
  // Dragging counts; a mouse moving without a pressed button does not
  window.addEventListener('pointermove', (event) => { if (event.buttons) onInput(); }, opts);

  window.addEventListener('visibilitychange', () => {
    const t = performance.now();
    if (document.visibilityState === 'hidden') log(tracker.hide(t));
    else tracker.show(t);
  }, { capture: true });
  window.addEventListener('pagehide', () => log(tracker.leave(performance.now())), { capture: true });
  // Back from the back/forward cache: a new page clock
  window.addEventListener('pageshow', (event) => { if (event.persisted) tracker.show(performance.now()); });
}
