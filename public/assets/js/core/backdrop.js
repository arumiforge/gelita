/**
 * Per-slide backdrop of the narrated scenes. Two stacked <img> hold the
 * picture on screen and the next one; a slide's own picture replaces the one
 * before it without any other picture in between.
 *
 * Two ways to change (owner, 19 October 2026):
 *   crossfade  (region intro overlay, region dialogue, region done) the new
 *              picture fades in over the old one (CSS `.is-visible`, opacity
 *              transition)
 *   dip        (opening and ending story, `veil` given) like a film cut: the
 *              veil darkens the pictures to black, the pictures swap under
 *              it, and the veil lifts again. Only the background goes dark;
 *              the caption, the characters, and the HUD stay as they are
 *
 * The default picture (`fallback`, else the `src` the first <img> had when the
 * page loaded) shows for a slide without a picture of its own, or nothing
 * when it is empty (the CSS gradient shows through).
 *
 * Classes set here, styled in cinematic.css:
 *   .is-visible  the picture on screen
 *   .is-own      that picture is a slide's own (not the default)
 *   .is-leaving  the picture fading out; keeps its slow zoom (Ken Burns) so
 *                it does not snap back to full size mid-fade
 *   .is-dark     on the veil: black over the pictures (0.35 s in, 0.5 s out)
 */

const FADE_MS = 700;
const DARK_MS = 350;      // fade to black (cinematic.css .is-dark)
const HOLD_MS = 100;      // black before the new picture fades in
const WAIT_MAX_MS = 2500; // a picture still loading after this stays out; the current one comes back

const wait = (ms) => new Promise((resolve) => { setTimeout(resolve, ms); });
const reducedMotion = () => window.matchMedia?.('(prefers-reduced-motion: reduce)').matches ?? false;

/**
 * @param {[HTMLImageElement, HTMLImageElement]} pair
 * @param {{ fallback?: string, veil?: HTMLElement | null }} [options]
 * @returns {{ show: (src?: string | null, opts?: { always?: boolean }) => void,
 *             preload: (src?: string | null) => void }}
 */
export function createBackdrop([a, b], { fallback = a.getAttribute('src') || '', veil = null } = {}) {
  const first = a.getAttribute('src') || '';
  let shown = first && a.classList.contains('is-visible') ? a : null;
  let wanted = shown ? first : '';
  let ticket = 0;

  /** Resolves once `img` can be painted with `src` (eager even if the markup said lazy). */
  const load = (img, src) => {
    img.loading = 'eager';
    if (img.getAttribute('src') !== src) img.src = src;
    if (typeof img.decode === 'function') return img.decode();
    return img.complete ? Promise.resolve() : new Promise((resolve, reject) => {
      img.addEventListener('load', resolve, { once: true });
      img.addEventListener('error', reject, { once: true });
    });
  };

  const swap = (next, own) => {
    const old = shown;
    if (old && old !== next) {
      old.classList.add('is-leaving');
      old.classList.remove('is-visible');
      setTimeout(() => old.classList.remove('is-leaving'), FADE_MS);
    }
    if (next) {
      next.classList.toggle('is-own', own);
      next.classList.remove('is-leaving');
      next.classList.add('is-visible');
    }
    shown = next;
  };

  /** Crossfade: the new picture fades in once it can be painted. */
  function crossfade(target, own, mine) {
    if (!target) {
      swap(null, false);
      return;
    }
    const next = shown === a ? b : a;
    // A picture that fails to load leaves the current one on screen
    load(next, target).then(() => { if (mine === ticket) swap(next, own); }, () => {
      if (mine === ticket) wanted = shown?.getAttribute('src') || '';
    });
  }

  /**
   * Dip: black, swap under it, lift. A newer call takes over at any step and
   * continues from black; it compares with the picture actually on screen,
   * since a call it overtook never swapped.
   */
  async function dip(target, own, mine) {
    const onScreen = shown?.getAttribute('src') || '';
    const change = target !== onScreen;
    const next = change && target ? (shown === a ? b : a) : null;
    // Load while the picture fades out, so the black lasts no longer than it must
    const ready = next
      ? load(next, target).then(() => 'ready', () => 'failed')
      : Promise.resolve('ready');

    if (reducedMotion()) {
      // No fade and no black: the picture changes when it is ready
      if ((await ready) === 'ready' && mine === ticket && change) swap(next, own);
      return;
    }

    veil.classList.add('is-dark');
    const [state] = await Promise.all([Promise.race([ready, wait(WAIT_MAX_MS).then(() => 'slow')]), wait(DARK_MS)]);
    if (mine !== ticket) return;

    if (change) {
      if (state === 'ready') swap(next, own);
      else wanted = onScreen; // keep the current picture rather than a half-loaded one
    }
    await wait(HOLD_MS);
    if (mine !== ticket) return;
    veil.classList.remove('is-dark');
  }

  return {
    /** `always`: dip to black even when the picture stays the same (a new story slide). */
    show(src, { always = false } = {}) {
      const target = src || fallback;
      if (target === wanted && !(always && veil)) return;
      wanted = target;
      const mine = ++ticket;
      const own = Boolean(src);

      if (veil) dip(target, own, mine);
      else crossfade(target, own, mine);
    },

    preload(src) {
      if (!src) return;
      const img = new Image();
      img.decoding = 'async';
      img.src = src;
    },
  };
}
