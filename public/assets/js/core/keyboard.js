/**
 * On-screen keyboard on form screens (display plan Phase F3.4).
 *
 * The stage never scrolls, so a field in the lower half of the stage can end
 * up under a phone's on-screen keyboard. While a text field inside #stage has
 * focus and `visualViewport` shows that the keyboard covers it, the stage
 * frame is shifted up just enough to show the field (plus a small margin), and
 * shifted back on blur or when the keyboard closes.
 *
 * Works whether the browser shrinks only the visual viewport (iOS Safari,
 * recent Chrome on Android) or the whole layout viewport (the stage then
 * rescales by itself and no shift is needed). Without `visualViewport` this
 * does nothing.
 */
const FIELDS = 'input:not([type="hidden"], [type="checkbox"], [type="radio"], [type="submit"], [type="button"], [type="range"], [type="file"]), textarea';
const MARGIN = 12; // CSS px kept between the field and the keyboard

let frame = null;
let field = null;
let shift = 0;
let blurTimer = null;

function apply(next) {
  const value = Math.round(next);
  if (value === shift) return;
  shift = value;
  frame.style.transform = shift ? `translateY(${-shift}px)` : '';
}

function update() {
  const vv = window.visualViewport;
  if (!vv || !frame) return;
  if (!field || !document.contains(field)) {
    apply(0);
    return;
  }

  // Field position without the current shift, in layout viewport px
  const rect = field.getBoundingClientRect();
  const top = rect.top + shift;
  const bottom = rect.bottom + shift;
  const visibleTop = vv.offsetTop + MARGIN;
  const visibleBottom = vv.offsetTop + vv.height - MARGIN;

  // Just enough to show the bottom edge, never pushing the top edge out of view
  const needed = Math.max(0, bottom - visibleBottom);
  apply(Math.min(needed, Math.max(0, top - visibleTop)));
}

function onFocusIn(event) {
  const target = event.target;
  if (!(target instanceof Element) || !target.matches(FIELDS)) return;
  clearTimeout(blurTimer);
  field = target;
  update();
  // The keyboard animates in; visualViewport resize follows, this is a backstop
  setTimeout(update, 350);
}

function onFocusOut() {
  // Focus moving to the next field fires focusin right after; wait for it
  clearTimeout(blurTimer);
  blurTimer = setTimeout(() => {
    if (field && document.activeElement === field) return;
    field = null;
    update();
  }, 60);
}

export function initKeyboardShift() {
  const vv = window.visualViewport;
  const stage = document.getElementById('stage');
  frame = document.querySelector('.stage-frame');
  if (!vv || !stage || !frame) return;

  if (!window.matchMedia?.('(prefers-reduced-motion: reduce)').matches) {
    frame.style.transition = 'transform .2s ease-out';
  }

  stage.addEventListener('focusin', onFocusIn);
  stage.addEventListener('focusout', onFocusOut);
  vv.addEventListener('resize', update);
  vv.addEventListener('scroll', update);
  // bfcache: come back without a stale shift
  window.addEventListener('pageshow', () => {
    field = null;
    apply(0);
  });
}
