/**
 * Cinematic entrances for the start screens (owner, 20 October 2026).
 *
 * A page marks its root with `data-cine="welcome|gate|account"`. Each name has
 * a short cue sheet: which part enters, when, for how long, and how it moves
 * (the background pushes in from dark, the logo comes into focus behind a
 * flare, the cards slide in one after another). The cues run on the browser's
 * own Web Animations API, so nothing is downloaded for them; every cue animates
 * only opacity and the individual translate/scale/rotate properties, which the
 * compositor runs without repainting (no `filter`: an animated blur or
 * brightness on the full-screen picture re-renders it every frame), with
 * `fill: backwards`, so once it ends the page's own CSS is back in charge.
 *
 * - It waits until the background picture and the logo are decoded (at most
 *   1.5 s), so the picture never pops in halfway through its push-in.
 * - It plays only while the page is young: once the CSS fallback below has
 *   shown the parts (a slow connection), it does not hide them again.
 * - First visit of a page in this browser session: the full sheet. Later
 *   visits (Back to the welcome page, a form shown again with an error): a
 *   quick fade, so nobody waits through the film twice.
 * - A tap or a key during the entrance jumps to its end.
 * - After it, on `welcome` and `gate`, the WebGL screen effects of the story
 *   scenes (game/screen-fx.js: sparks, thin mist, light rays, bloom) and the
 *   parallax depth start, when the root carries `data-fx3d` (Config\Gelita::
 *   $screenFx), one quality step below the story scenes and fading in over
 *   2.5 s (cinematic.css). On the welcome page the rays come from the light of
 *   the background picture itself (findLight(): its sun, or the brightest glow
 *   of the sky), whatever picture the admin uploaded. Without WebGL 2 the page
 *   simply stays as it is.
 *
 * Before the module runs, core/viewport.js sets `html.cine-ok` (motion
 * allowed), and cinematic.css holds the parts hidden until the root gets
 * `.is-cine-live`, so the page never flashes in its final state first; if the
 * module never runs, a CSS fallback shows them after 4 s. Under
 * prefers-reduced-motion nothing is held and nothing plays.
 */
import { reducedMotion } from '../core/dom.js';
import { acquireParallax } from './parallax.js';

// Gentle ease-out: the movement is spread over the whole cue instead of being
// mostly done in its first quarter, which read as a snap rather than a glide
const OUT = 'cubic-bezier(.25, .46, .45, .94)';
const BACK = 'cubic-bezier(.34, 1.3, .64, 1)';  // a small overshoot
const SEEN_KEY = 'gelita.cine.seen';
const LATE_MS = 3500;     // after this the CSS fallback (4 s) is about to show the parts: no entrance
const DECODE_MS = 1500;   // longest wait for the pictures before the entrance starts anyway

/**
 * Cue: [selector (inside #stage), keyframes, start ms, duration ms, easing?, stagger ms?].
 * Lengths are rem: the stage is 80 × 45 rem, so 1 rem = 16 dp at every size.
 * @type {Record<string, { cues: Array<[string, Keyframe[] | PropertyIndexedKeyframes, number, number, string?, number?]>, fx?: string | null, light?: boolean, flare?: string, bars?: boolean }>}
 */
const SHEETS = {
  welcome: {
    bars: true,
    flare: '.welcome-hero',
    fx: 'sun',
    light: true,
    // The picture rises out of the dark stage (opacity over the stage's own dark
    // background does what a brightness filter did, without re-rendering it)
    cues: [
      ['.scene', { opacity: [0, 1], scale: [1.1, 1] }, 0, 3600],
      ['.cine-bar', { scale: ['1 1', '1 0'] }, 1600, 1400, 'cubic-bezier(.45, 0, .25, 1)'],
      ['.cine-flare', { opacity: [0, 0.85, 0], scale: [0.4, 1.15, 1.45] }, 900, 2200, 'ease-in-out'],
      ['.welcome-logo, .welcome-title', { opacity: [0, 1], scale: [1.08, 1] }, 900, 2200],
      ['.welcome-tagline', { opacity: [0, 1], translate: ['0 .5rem', '0 0'] }, 2100, 1100],
      ['.welcome-actions', { opacity: [0, 1], translate: ['0 1rem', '0 0'], scale: [0.95, 1] }, 2600, 1100, BACK],
      ['.hud', { opacity: [0, 1] }, 3000, 900],
      ['.welcome-staff', { opacity: [0, 1] }, 3200, 900],
    ],
  },
  gate: {
    fx: null,
    cues: [
      ['.scene', { opacity: [0, 1], scale: [1.08, 1] }, 0, 2600],
      ['.gate-jaka', { opacity: [0, 1], translate: ['-2rem .75rem', '0 0'] }, 400, 1600],
      ['.gate-crest', { opacity: [0, 1], scale: [0.6, 1], rotate: ['-20deg', '0deg'] }, 800, 1300, BACK],
      ['.gate-panel .start-head', { opacity: [0, 1], translate: ['0 .75rem', '0 0'] }, 1000, 1100],
      ['.gate-panel .ornate-divider', { opacity: [0, 1], scale: ['0 1', '1 1'] }, 1250, 1000],
      ['.gate-panel .start-card', { opacity: [0, 1], translate: ['2rem 0', '0 0'] }, 1350, 1100, OUT, 200],
      ['.hud', { opacity: [0, 1] }, 1600, 900],
    ],
  },
  account: {
    cues: [
      ['.scene', { opacity: [0, 1], scale: [1.05, 1] }, 0, 1900],
      [':is(.auth-jaka, .start-jaka)', { opacity: [0, 1], translate: ['0 1rem', '0 0'] }, 300, 1300],
      [':is(.ornate-panel, .login-panel)', { opacity: [0, 1], translate: ['0 .75rem', '0 0'] }, 400, 1100],
      ['.ornate-crest', { opacity: [0, 1], scale: [0.6, 1], rotate: ['-20deg', '0deg'] }, 900, 1100, BACK],
      ['.hud', { opacity: [0, 1] }, 700, 900],
    ],
  },
};

/** Parts the CSS holds hidden until `.is-cine-live` (cinematic.css): the quick fade brings them in. */
const HELD = '.scene, .hud, [data-cine] > :is(.welcome-hero, .welcome-actions, .welcome-staff, .gate-jaka, .gate-panel, .start-jaka, .auth-jaka, .ornate-panel, .login-panel)';

function seenBefore(key) {
  try {
    const seen = JSON.parse(sessionStorage.getItem(SEEN_KEY) || '[]');
    if (seen.includes(key)) return true;
    sessionStorage.setItem(SEEN_KEY, JSON.stringify([...seen, key]));
  } catch {
    // Private mode or blocked storage: play the full entrance every time
  }
  return false;
}

/** Letterbox bars and the flare behind the logo: only while the entrance runs. */
function addProps(stage, root, sheet) {
  const props = [];
  if (sheet.bars) {
    for (const side of ['is-top', 'is-bottom']) {
      const bar = document.createElement('span');
      bar.className = `cine-bar ${side}`;
      bar.setAttribute('aria-hidden', 'true');
      stage.append(bar);
      props.push(bar);
    }
  }
  const host = sheet.flare && root.querySelector(sheet.flare);
  if (host) {
    const flare = document.createElement('span');
    flare.className = 'cine-flare';
    flare.setAttribute('aria-hidden', 'true');
    host.prepend(flare);
    props.push(flare);
  }
  return props;
}

function play(stage, cues) {
  const animations = [];
  for (const [selector, keyframes, at, duration, easing = OUT, stagger = 0] of cues) {
    stage.querySelectorAll(selector).forEach((el, i) => {
      animations.push(el.animate(keyframes, { delay: at + i * stagger, duration, easing, fill: 'backwards' }));
    });
  }
  return animations;
}

/** Resolves once the background picture and the logo can be drawn, or after DECODE_MS. */
function picturesReady(stage) {
  const pictures = [...stage.querySelectorAll('.scene-img.is-visible, .welcome-logo img')]
    .filter((img) => img.getAttribute('src'))
    .map((img) => (img.decode ? img.decode() : Promise.resolve()).catch(() => {}));
  return Promise.race([Promise.all(pictures), new Promise((resolve) => setTimeout(resolve, DECODE_MS))]);
}

/**
 * Where the background picture's light is: the brightest warm patch of its upper
 * part (a sun, or the glow of the sky where it set), on a 96 × 54 copy smoothed
 * so that a single lamp in the village does not win. In stage fractions, or null
 * (no clear light, or a picture the canvas may not read): then the rays keep
 * their default source above the top edge.
 * @param {HTMLImageElement | null} img
 * @returns {{ x: number, y: number } | null}
 */
export function findLight(img) {
  if (!img?.naturalWidth) return null;
  const W = 96;
  const H = 54;
  try {
    const canvas = document.createElement('canvas');
    canvas.width = W;
    canvas.height = H;
    const ctx = canvas.getContext('2d', { willReadFrequently: true });
    // object-fit: cover on a 16:9 stage: the same crop as the scene
    const scale = Math.max(W / img.naturalWidth, H / img.naturalHeight);
    const w = img.naturalWidth * scale;
    const h = img.naturalHeight * scale;
    ctx.drawImage(img, (W - w) / 2, (H - h) / 2, w, h);
    const data = ctx.getImageData(0, 0, W, H).data;
    const rows = Math.round(H * 0.7);
    const score = new Float32Array(W * rows);
    for (let p = 0; p < score.length; p += 1) {
      const [r, g, b] = [data[p * 4] / 255, data[p * 4 + 1] / 255, data[p * 4 + 2] / 255];
      // Bright and warm or white (a sun), not a cold blue sky
      score[p] = (0.2126 * r + 0.7152 * g + 0.0722 * b) * (0.7 + 0.3 * Math.max(0, r - b));
    }
    // Mean over a 5 × 5 neighbourhood, by hand (Safari ignores ctx.filter)
    let best = 0;
    let at = null;
    for (let y = 2; y < rows - 2; y += 1) {
      for (let x = 2; x < W - 2; x += 1) {
        let sum = 0;
        for (let dy = -2; dy <= 2; dy += 1) {
          for (let dx = -2; dx <= 2; dx += 1) sum += score[(y + dy) * W + x + dx];
        }
        if (sum / 25 > best) {
          best = sum / 25;
          at = { x: (x + 0.5) / W, y: (y + 0.5) / H };
        }
      }
    }
    return best > 0.5 ? at : null;
  } catch {
    return null; // a picture from another origin taints the canvas
  }
}

/**
 * Starts downloading the effect modules (three.js is 2.1 MB) while the entrance
 * plays: its cues run on the compositor, so the parse on the main thread does
 * not stall them, and the effects are ready when it ends. Only with WebGL 2,
 * as in screen-fx.js, so a device that cannot use them downloads nothing.
 */
function prefetchScreenFx(root) {
  const threeUrl = root.dataset.fx3d || '';
  if (!threeUrl) return null;
  const probe = document.createElement('canvas').getContext('webgl2');
  if (!probe) return null;
  probe.getExtension('WEBGL_lose_context')?.loseContext();
  return Promise.all([import('./screen-fx.js'), import(threeUrl)]).catch(() => null);
}

async function startScreenFx(root, stage, sheet, prefetch) {
  const threeUrl = root.dataset.fx3d || '';
  if (!threeUrl) return;
  acquireParallax(); // CSS depth, also on a device without WebGL 2
  if (!prefetch) return;
  try {
    const light = sheet.light ? findLight(stage.querySelector('.scene-img.is-visible')) : null;
    const [{ createScreenFx }] = (await prefetch) ?? [await import('./screen-fx.js')];
    const fx = await createScreenFx(root, { threeUrl, light, quality: 1 });
    if (!fx) return;
    fx.setEffect(sheet.fx);
    fx.resume();
    document.addEventListener('visibilitychange', () => (document.hidden ? fx.pause() : fx.resume()));
  } catch {
    // No WebGL 2 or a failed download: the page stays as it is
  }
}

/** @param {HTMLElement} root element with `data-cine` */
export async function initCine(root) {
  const stage = document.getElementById('stage');
  const sheet = SHEETS[root.dataset.cine];
  if (!stage || !sheet || reducedMotion() || !document.documentElement.classList.contains('cine-ok')) {
    root.classList.add('is-cine-live');
    return;
  }

  await picturesReady(stage);
  // The pictures first, then the effect modules on the same connection
  const prefetch = 'fx' in sheet ? prefetchScreenFx(root) : null;
  if (performance.now() > LATE_MS) {
    // A slow connection: the CSS fallback shows (or just showed) the page; never hide it again
    root.classList.add('is-cine-live', 'is-cine-done');
    startScreenFx(root, stage, sheet, prefetch);
    return;
  }

  const full = !seenBefore(`${root.dataset.cine}:${location.pathname}`);
  const props = full ? addProps(stage, root, sheet) : [];
  const animations = full
    ? play(stage, sheet.cues)
    : play(stage, [[HELD, { opacity: [0, 1] }, 0, 450, 'ease-out']]);
  // Same task as the first frames above, so the held parts never flash in between
  root.classList.add('is-cine-live');
  stage.classList.add('is-cine-playing');

  const skip = () => animations.forEach((a) => a.finish());
  stage.addEventListener('pointerdown', skip, { once: true });
  document.addEventListener('keydown', skip, { once: true });

  Promise.all(animations.map((a) => a.finished.catch(() => {}))).then(() => {
    stage.removeEventListener('pointerdown', skip);
    document.removeEventListener('keydown', skip);
    props.forEach((el) => el.remove());
    stage.classList.remove('is-cine-playing');
    root.classList.add('is-cine-done');
    startScreenFx(root, stage, sheet, prefetch);
  });
}
