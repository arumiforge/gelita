/**
 * Touch and mouse gestures on pointer events (display plan Phase F3.1, F3.3).
 *
 *   initDrag(container, options) → drag an element onto another one (puzzle
 *                                   pieces). Returns a detach function.
 *   onSwipe(element, options)    → horizontal swipe with a finger or pen
 *                                   (story slides, dialogue, Kenali, Pustaka).
 *                                   Returns a detach function.
 *
 * Both work in screen (CSS) px from the pointer events. The 16:9 stage is
 * sized in rem, not with a transform, so these are also the stage's own px;
 * dragLocalScale() still converts for an element that sits inside a
 * transform, should one ever be added.
 */

const DRAG_THRESHOLD = 8;   // px of movement before a press becomes a drag
const SWIPE_THRESHOLD = 50; // px of horizontal movement for a swipe
const CLICK_GUARD_MS = 400; // a click on the dragged node this soon after the drag belongs to it

/** Controls a swipe never starts on: their own tap or drag wins. */
const SWIPE_CONTROLS = 'button, a, input, select, textarea, label, summary, [role="button"], [contenteditable="true"], .audio-player';

/**
 * Ratio between an element's on-screen size and its own CSS size: 1 unless
 * an ancestor is transformed (scale). Divide screen px by it to get the px of
 * an inline `transform: translate()` on that element.
 */
export function dragLocalScale(node) {
  const width = node?.offsetWidth || 0;
  if (!width) return 1;
  const ratio = node.getBoundingClientRect().width / width;
  return ratio > 0 ? ratio : 1;
}

/**
 * Pointer drag for mouse, touch, and pen alike.
 *
 * A press on an element matching `selector` inside `container` becomes a
 * drag only after the pointer moves `threshold` px; below that the press is
 * a plain tap and its click fires as usual. After a real drag the click that
 * the browser may send right after it on the dragged element is swallowed,
 * so a drag never also counts as a tap.
 * Only the primary pointer (and the main mouse button) drags; a second
 * finger, pointercancel, or a lost pointer capture cancels the drag.
 *
 * `target` is the element matching `selector` under the pointer, found with
 * document.elementFromPoint while the dragged element ignores the pointer
 * (its art follows the finger and would otherwise always be on top); null
 * when the pointer is over nothing that matches, or over the dragged element.
 *
 * @param {HTMLElement} container
 * @param {{
 *   selector: string,
 *   threshold?: number,
 *   canStart?: (node: HTMLElement, event: PointerEvent) => boolean,
 *   onStart?: (node: HTMLElement, event: PointerEvent) => void,
 *   onMove?: (node: HTMLElement, move: { dx: number, dy: number, event: PointerEvent, target: HTMLElement|null }) => void,
 *   onDrop?: (node: HTMLElement, target: HTMLElement|null, event: PointerEvent) => void,
 *   onCancel?: (node: HTMLElement) => void,
 * }} options
 * @returns {() => void} detach
 */
export function initDrag(container, {
  selector,
  threshold = DRAG_THRESHOLD,
  canStart = null,
  onStart = null,
  onMove = null,
  onDrop = null,
  onCancel = null,
} = {}) {
  if (!container || !selector) return () => {};

  let press = null;        // { node, id, x, y, dragging }
  let guard = null;        // { node, until }: swallow the click that follows a drag

  const targetAt = (node, x, y) => {
    const previous = node.style.pointerEvents;
    node.style.pointerEvents = 'none';
    const hit = document.elementFromPoint(x, y);
    node.style.pointerEvents = previous;
    const match = hit instanceof Element ? hit.closest(selector) : null;
    return match && match !== node && container.contains(match) ? match : null;
  };

  const release = () => {
    if (!press) return;
    const { node, id } = press;
    node.removeEventListener('pointermove', onPointerMove);
    node.removeEventListener('pointerup', onPointerUp);
    node.removeEventListener('pointercancel', onPointerCancel);
    node.removeEventListener('lostpointercapture', onPointerCancel);
    try {
      if (node.hasPointerCapture?.(id)) node.releasePointerCapture(id);
    } catch { /* the pointer is already gone */ }
    press = null;
  };

  function onPointerMove(event) {
    if (!press || event.pointerId !== press.id) return;
    const dx = event.clientX - press.x;
    const dy = event.clientY - press.y;
    if (!press.dragging) {
      if (Math.hypot(dx, dy) < threshold) return;
      press.dragging = true;
      onStart?.(press.node, event);
    }
    event.preventDefault();
    onMove?.(press.node, { dx, dy, event, target: targetAt(press.node, event.clientX, event.clientY) });
  }

  function onPointerUp(event) {
    if (!press || event.pointerId !== press.id) return;
    const { node, dragging } = press;
    release();
    if (!dragging) return; // a tap: its click fires normally
    guard = { node, until: performance.now() + CLICK_GUARD_MS };
    onDrop?.(node, targetAt(node, event.clientX, event.clientY), event);
  }

  function onPointerCancel(event) {
    if (!press || event.pointerId !== press.id) return;
    const { node, dragging } = press;
    release();
    if (!dragging) return;
    guard = { node, until: performance.now() + CLICK_GUARD_MS };
    onCancel?.(node);
  }

  const onPointerDown = (event) => {
    if (!event.isPrimary) {
      // A second finger: drop the drag in progress
      if (press) onPointerCancel({ pointerId: press.id });
      return;
    }
    if (press || (event.pointerType === 'mouse' && event.button !== 0)) return;
    const node = event.target instanceof Element ? event.target.closest(selector) : null;
    if (!node || !container.contains(node)) return;
    if (canStart && !canStart(node, event)) return;

    press = { node, id: event.pointerId, x: event.clientX, y: event.clientY, dragging: false };
    try {
      node.setPointerCapture(event.pointerId);
    } catch { /* not capturable (synthetic event): moves still reach the node */ }
    node.addEventListener('pointermove', onPointerMove);
    node.addEventListener('pointerup', onPointerUp);
    node.addEventListener('pointercancel', onPointerCancel);
    node.addEventListener('lostpointercapture', onPointerCancel);
  };

  // The click after a drag would otherwise count as a tap on the dragged node
  const onClick = (event) => {
    if (!guard) return;
    const { node, until } = guard;
    guard = null;
    if (performance.now() > until || !(event.target instanceof Node) || !node.contains(event.target)) return;
    event.preventDefault();
    event.stopImmediatePropagation();
  };

  // No native HTML5 drag (ghost image) on top of the pointer drag
  const onNativeDrag = (event) => {
    if (event.target instanceof Element && event.target.closest(selector)) event.preventDefault();
  };

  container.addEventListener('pointerdown', onPointerDown);
  container.addEventListener('click', onClick, true);
  container.addEventListener('dragstart', onNativeDrag);

  return () => {
    if (press?.dragging) onCancel?.(press.node);
    release();
    container.removeEventListener('pointerdown', onPointerDown);
    container.removeEventListener('click', onClick, true);
    container.removeEventListener('dragstart', onNativeDrag);
  };
}

/** True when `node` scrolls by itself (overflow auto/scroll with more content than room). */
function scrolls(node) {
  const style = getComputedStyle(node);
  const y = /(auto|scroll)/.test(style.overflowY) && node.scrollHeight > node.clientHeight + 1;
  const x = /(auto|scroll)/.test(style.overflowX) && node.scrollWidth > node.clientWidth + 1;
  return x || y;
}

/** A swipe that starts in a scrolling panel or on a control is left to that element. */
function swipeBlocked(start, element, ignore) {
  if (!(start instanceof Element)) return true;
  if (start.closest(SWIPE_CONTROLS)) return true;
  if (ignore && start.closest(ignore)) return true;
  for (let node = start; node && node !== element.parentElement; node = node.parentElement) {
    if (scrolls(node)) return true;
  }
  return false;
}

/**
 * Horizontal swipe with a finger or pen (the mouse keeps using the buttons).
 *
 * `left` runs for a swipe to the left (next), `right` for a swipe to the
 * right (previous). A swipe needs `threshold` px of horizontal movement that
 * is larger than the vertical movement. Ignored: touches that start on a
 * control, inside an element that actually scrolls, inside `ignore`, while a
 * modal is open, or with more than one finger.
 *
 * The browser may take over a gesture (pointercancel, e.g. for a pinch or a
 * scroll); a matching `touchend` then still finishes the swipe from the
 * finger's last position.
 *
 * @param {HTMLElement} element
 * @param {{ left?: () => void, right?: () => void, threshold?: number, ignore?: string }} options
 * @returns {() => void} detach
 */
export function onSwipe(element, { left = null, right = null, threshold = SWIPE_THRESHOLD, ignore = '' } = {}) {
  if (!element) return () => {};

  let start = null; // { id, x, y, multi }

  const finish = (x, y) => {
    if (!start) return;
    const { multi } = start;
    const dx = x - start.x;
    const dy = y - start.y;
    start = null;
    if (multi || Math.abs(dx) < threshold || Math.abs(dx) <= Math.abs(dy)) return;
    if (document.body.classList.contains('has-modal')) return;
    if (dx < 0) left?.();
    else right?.();
  };

  const onDown = (event) => {
    if (event.pointerType !== 'touch' && event.pointerType !== 'pen') return;
    if (!event.isPrimary) {
      if (start) start.multi = true;
      return;
    }
    start = null;
    if (document.body.classList.contains('has-modal')) return;
    if (swipeBlocked(event.target, element, ignore)) return;
    start = { id: event.pointerId, x: event.clientX, y: event.clientY, multi: false, cancelled: false };
  };

  const onUp = (event) => {
    if (start && event.pointerId === start.id && !start.cancelled) finish(event.clientX, event.clientY);
  };

  // The browser took the gesture over: wait for the touch to end instead
  const onCancel = (event) => {
    if (start && event.pointerId === start.id) start.cancelled = true;
  };

  const onTouchEnd = (event) => {
    if (!start?.cancelled) return;
    if (event.touches.length) {
      start.multi = true;
      return;
    }
    const touch = event.changedTouches[0];
    if (touch) finish(touch.clientX, touch.clientY);
    else start = null;
  };

  const onTouchCancel = () => { start = null; };

  element.addEventListener('pointerdown', onDown);
  element.addEventListener('pointerup', onUp);
  element.addEventListener('pointercancel', onCancel);
  element.addEventListener('touchend', onTouchEnd, { passive: true });
  element.addEventListener('touchcancel', onTouchCancel, { passive: true });

  return () => {
    element.removeEventListener('pointerdown', onDown);
    element.removeEventListener('pointerup', onUp);
    element.removeEventListener('pointercancel', onCancel);
    element.removeEventListener('touchend', onTouchEnd);
    element.removeEventListener('touchcancel', onTouchCancel);
  };
}
