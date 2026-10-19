/**
 * Per-slide backdrop of the narrated region scenes (region intro overlay,
 * region dialogue, region done). Two stacked <img> crossfade through the CSS
 * class `.is-visible` (opacity transition), so a slide's own picture replaces
 * the one before it without a flash.
 *
 * The default picture is the `src` the first <img> had when the page loaded
 * (the region background); a slide without a picture of its own shows it
 * again, or nothing when it was empty (the CSS gradient shows through).
 *
 * Classes set here, styled in cinematic.css:
 *   .is-visible  the picture on screen
 *   .is-own      that picture is a slide's own (not the default)
 *   .is-leaving  the picture fading out; keeps its slow zoom (Ken Burns) so
 *                it does not snap back to full size mid-fade
 */

const FADE_MS = 700;

/**
 * @param {[HTMLImageElement, HTMLImageElement]} pair
 * @returns {{ show: (src?: string | null) => void, preload: (src?: string | null) => void }}
 */
export function createBackdrop([a, b]) {
  const fallback = a.getAttribute('src') || '';
  let shown = fallback && a.classList.contains('is-visible') ? a : null;
  let wanted = shown ? fallback : '';
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

  return {
    show(src) {
      const target = src || fallback;
      if (target === wanted) return;
      wanted = target;
      const mine = ++ticket;

      if (!target) {
        swap(null, false);
        return;
      }

      const next = shown === a ? b : a;
      // A picture that fails to load leaves the current one on screen
      load(next, target).then(() => { if (mine === ticket) swap(next, Boolean(src)); }, () => {
        if (mine === ticket) wanted = shown?.getAttribute('src') || '';
      });
    },

    preload(src) {
      if (!src) return;
      const img = new Image();
      img.decoding = 'async';
      img.src = src;
    },
  };
}
