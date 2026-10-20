/**
 * Cinematic entrances for the start screens (owner, 20 October 2026).
 *
 * A page marks its root with `data-cine="welcome|gate|account"`. Each name has
 * a short cue sheet: which part enters, when, for how long, and how it moves
 * (the background pushes in from dark, the logo comes into focus behind a
 * flare, the cards slide in one after another). The cues run on the browser's
 * own Web Animations API, so nothing is downloaded for them; every cue animates
 * only opacity, filter, and the individual translate/scale/rotate properties,
 * with `fill: backwards`, so once it ends the page's own CSS is back in charge.
 *
 * - First visit of a page in this browser session: the full sheet. Later
 *   visits (Back to the welcome page, a form shown again with an error): a
 *   quick fade, so nobody waits through the film twice.
 * - A tap or a key during the entrance jumps to its end.
 * - After it, on `welcome` and `gate`, the WebGL screen effects of the story
 *   scenes (game/screen-fx.js: sparks, thin mist, light rays, bloom) and the
 *   parallax depth start, when the root carries `data-fx3d` (Config\Gelita::
 *   $screenFx). Without WebGL 2 the page simply stays as it is.
 *
 * Before the module runs, core/viewport.js sets `html.cine-ok` (motion
 * allowed), and cinematic.css holds the parts hidden until the root gets
 * `.is-cine-live`, so the page never flashes in its final state first; if the
 * module never runs, a CSS fallback shows them after 4 s. Under
 * prefers-reduced-motion nothing is held and nothing plays.
 */
import { reducedMotion } from '../core/dom.js';
import { acquireParallax } from './parallax.js';

const OUT = 'cubic-bezier(.16, 1, .3, 1)';      // quick start, long soft landing
const BACK = 'cubic-bezier(.34, 1.45, .64, 1)'; // a small overshoot
const SEEN_KEY = 'gelita.cine.seen';

/**
 * Cue: [selector (inside #stage), keyframes, start ms, duration ms, easing?, stagger ms?].
 * Lengths are rem: the stage is 80 × 45 rem, so 1 rem = 16 dp at every size.
 * @type {Record<string, { cues: Array<[string, Keyframe[] | PropertyIndexedKeyframes, number, number, string?, number?]>, fx?: string | null, flare?: string, bars?: boolean }>}
 */
const SHEETS = {
  welcome: {
    bars: true,
    flare: '.welcome-hero',
    fx: 'glow',
    cues: [
      ['.scene', { opacity: [0, 1], scale: [1.16, 1], filter: ['blur(.375rem) brightness(.3)', 'blur(0) brightness(1)'] }, 0, 2800],
      ['.cine-bar', { scale: ['1 1', '1 0'] }, 1300, 1100, 'cubic-bezier(.65, 0, .35, 1)'],
      ['.cine-flare', { opacity: [0, 0.95, 0], scale: [0.35, 1.25, 1.6] }, 650, 1700, 'ease-out'],
      ['.welcome-logo, .welcome-title', { opacity: [0, 1], scale: [1.22, 1], filter: ['blur(.875rem) brightness(1.6)', 'blur(0) brightness(1)'] }, 500, 1700],
      ['.welcome-tagline', { opacity: [0, 1], translate: ['0 .75rem', '0 0'] }, 1500, 900],
      ['.welcome-actions', { opacity: [0, 1], translate: ['0 1.5rem', '0 0'], scale: [0.9, 1] }, 1950, 900, BACK],
      ['.hud', { opacity: [0, 1] }, 2300, 700],
      ['.welcome-staff', { opacity: [0, 1] }, 2500, 700],
    ],
  },
  gate: {
    fx: null,
    cues: [
      ['.scene', { opacity: [0, 1], scale: [1.12, 1], filter: ['brightness(.35)', 'brightness(1)'] }, 0, 1900],
      ['.gate-jaka', { opacity: [0, 1], translate: ['-2.5rem 1rem', '0 0'] }, 350, 1300],
      ['.gate-crest', { opacity: [0, 1], scale: [0.5, 1], rotate: ['-30deg', '0deg'] }, 600, 1000, BACK],
      ['.gate-panel .start-head', { opacity: [0, 1], translate: ['0 1rem', '0 0'] }, 800, 900],
      ['.gate-panel .ornate-divider', { opacity: [0, 1], scale: ['0 1', '1 1'] }, 1000, 800],
      ['.gate-panel .start-card', { opacity: [0, 1], translate: ['3rem 0', '0 0'] }, 1100, 900, OUT, 160],
      ['.hud', { opacity: [0, 1] }, 1300, 700],
    ],
  },
  account: {
    cues: [
      ['.scene', { opacity: [0, 1], scale: [1.08, 1], filter: ['brightness(.4)', 'brightness(1)'] }, 0, 1500],
      [':is(.auth-jaka, .start-jaka)', { opacity: [0, 1], translate: ['0 1.5rem', '0 0'] }, 250, 1100],
      ['.ornate-panel', { opacity: [0, 1], translate: ['0 1rem', '0 0'] }, 350, 900],
      ['.ornate-crest', { opacity: [0, 1], scale: [0.5, 1], rotate: ['-30deg', '0deg'] }, 800, 900, BACK],
      ['.hud', { opacity: [0, 1] }, 600, 700],
    ],
  },
};

/** Parts the CSS holds hidden until `.is-cine-live` (cinematic.css): the quick fade brings them in. */
const HELD = '.scene, .hud, [data-cine] > :is(.welcome-hero, .welcome-actions, .welcome-staff, .gate-jaka, .gate-panel, .start-jaka, .auth-jaka, .ornate-panel)';

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

async function startScreenFx(root, effect) {
  const threeUrl = root.dataset.fx3d || '';
  if (!threeUrl) return;
  acquireParallax();
  try {
    const { createScreenFx } = await import('./screen-fx.js');
    const fx = await createScreenFx(root, { threeUrl });
    if (!fx) return;
    fx.setEffect(effect);
    fx.resume();
    document.addEventListener('visibilitychange', () => (document.hidden ? fx.pause() : fx.resume()));
  } catch {
    // No WebGL 2 or a failed download: the page stays as it is
  }
}

/** @param {HTMLElement} root element with `data-cine` */
export function initCine(root) {
  const stage = document.getElementById('stage');
  const sheet = SHEETS[root.dataset.cine];
  if (!stage || !sheet || reducedMotion() || !document.documentElement.classList.contains('cine-ok')) {
    root.classList.add('is-cine-live');
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
    if ('fx' in sheet) startScreenFx(root, sheet.fx);
  });
}
