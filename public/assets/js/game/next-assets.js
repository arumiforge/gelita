/**
 * Decode the next screen's images ahead of time (asset package Stage 4,
 * docs/rencana-paket-aset.md §4.5).
 *
 * Screens that know where the student most likely goes next carry
 * `data-next-assets` (JSON list of image URLs, rendered by the PHP helper
 * next_assets_attr()). Once the screen is shown and the browser is idle, each
 * image is loaded with `new Image()` and `img.decode()`, and the decoded image
 * is held until the page is left. The next document then paints from the
 * memory cache without a decode flash, and with the asset package (Stage 2)
 * the file never touches the network.
 *
 * Limits (iPad memory): one screen ahead only, never more than MAX_IMAGES
 * images held, nothing on Save-Data. References are dropped on `pagehide`
 * and the work is redone when the page comes back from the bfcache.
 */
import { $$ } from '../core/dom.js';

const MAX_IMAGES = 6;
const IDLE_TIMEOUT_MS = 2000;
const IMAGE_URL = /\.(png|jpe?g|webp|gif|svg|avif)(\?|#|$)/i;

/** Decoded images of this page; released on pagehide. */
let held = [];
let generation = 0;

/** Image URLs from every [data-next-assets] in root, deduplicated, capped. */
export function nextAssetUrls(root = document) {
  const urls = [];
  for (const node of $$('[data-next-assets]', root)) {
    let list = [];
    try {
      list = JSON.parse(node.dataset.nextAssets || '[]');
    } catch {
      list = [];
    }
    if (!Array.isArray(list)) continue;
    for (const url of list) {
      if (typeof url === 'string' && url !== '' && IMAGE_URL.test(url) && !urls.includes(url)) urls.push(url);
    }
  }
  return urls.slice(0, MAX_IMAGES);
}

function decodeOne(url, run) {
  const img = new Image();
  img.decoding = 'async';
  img.src = url;
  held.push(img);
  const ready = typeof img.decode === 'function'
    ? img.decode()
    : new Promise((resolve, reject) => { img.onload = resolve; img.onerror = reject; });
  // A failed image (missing upload, offline) is skipped quietly; the next
  // screen shows its own fallback as before.
  return ready.then(() => run === generation).catch(() => false);
}

const whenIdle = (fn) => {
  if ('requestIdleCallback' in window) window.requestIdleCallback(fn, { timeout: IDLE_TIMEOUT_MS });
  else setTimeout(fn, 600);
};

/**
 * Start decoding (after `ready`, e.g. the page module and its curtain).
 * Resolves with the number of images decoded.
 *
 * @param {{ ready?: Promise<unknown>, root?: ParentNode }} [options]
 * @returns {Promise<number>}
 */
export function decodeNextAssets({ ready = Promise.resolve(), root = document } = {}) {
  if (navigator.connection?.saveData) return Promise.resolve(0);
  const urls = nextAssetUrls(root);
  if (!urls.length) return Promise.resolve(0);

  const run = ++generation;
  return Promise.resolve(ready)
    .catch(() => {})
    .then(() => new Promise((resolve) => { whenIdle(resolve); }))
    .then(() => (run === generation ? Promise.all(urls.map((url) => decodeOne(url, run))) : []))
    .then((results) => {
      const count = results.filter(Boolean).length;
      if (run === generation) document.documentElement.dataset.nextDecoded = String(count);
      return count;
    });
}

/** Called once by game.js. */
export function initNextAssets({ ready } = {}) {
  window.addEventListener('pagehide', () => {
    generation += 1;
    held = [];
  });
  window.addEventListener('pageshow', (event) => {
    if (event.persisted) decodeNextAssets();
  });
  return decodeNextAssets({ ready });
}

/** Number of decoded images currently held (for tests). */
export const heldCount = () => held.length;
