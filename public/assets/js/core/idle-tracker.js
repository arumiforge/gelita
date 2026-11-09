/**
 * Pure input-pause tracker of core/activity.js (research revision 10, part
 * 7: 33 §5.2), with no imports so tests can run it in Node
 * (tests/unit/InputIdleEventTest.php).
 */
export const IDLE_ENDS = ['input', 'hidden', 'pagehide'];

/**
 * Pure pause tracker (also used by tests): `input(t)`, `hide(t)`, `show(t)`,
 * `leave(t)` with t from performance.now(); each returns the pause to log
 * `{ idle_ms, started_ms, end }` or null.
 */
export function createIdleTracker(thresholdMs, start = 0) {
  let last = start;
  let paused = false;
  const close = (t, end) => {
    if (paused) return null;
    const idle = Math.round(t - last);
    return idle >= thresholdMs ? { idle_ms: idle, started_ms: last, end } : null;
  };
  return {
    input(t) { const out = close(t, 'input'); if (!paused) last = t; return out; },
    hide(t) { const out = close(t, 'hidden'); paused = true; return out; },
    show(t) { paused = false; last = t; return null; },
    leave(t) { const out = close(t, 'pagehide'); paused = true; return out; },
  };
}
