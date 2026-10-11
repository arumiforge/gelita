/**
 * Frame player for character figures (`.character[data-frames]`, rendered by
 * components/character.php from Config\Gelita::$characterAnimations).
 *
 * - The server renders frame 1 in `.character-img`; this module only swaps
 *   that image's `src`, never the node, so CSS entrance animations do not
 *   replay and the mirrored right-hand figure stays mirrored.
 * - Frames 2+ are decoded while idle, before the cycle starts, so the swap
 *   does not flicker. A frame that fails to load is skipped.
 * - `data-loop="1"` cycles; otherwise the pose plays once and holds its last
 *   frame. It replays from frame 1 whenever the figure comes back into view
 *   (a slide shown again, an overlay reopened).
 * - Paused while the tab is hidden and while the figure is not rendered
 *   (hidden slides are `display: none`, which IntersectionObserver reports
 *   as not intersecting).
 * - With reduced motion only frame 1 is shown.
 */
import { $, $$, reducedMotion } from './dom.js';

const players = new WeakMap();
/** Players that may be running; used to pause/resume on tab visibility. */
const active = new Set();
const decoded = new Map();
let observer;

/** JSON list of frame URLs from a data attribute; [] when absent or broken. */
export function parseFrames(json) {
  try {
    const list = JSON.parse(json || '[]');
    return Array.isArray(list) ? list.filter((src) => typeof src === 'string' && src !== '') : [];
  } catch {
    return [];
  }
}

const whenIdle = () => new Promise((resolve) => {
  if ('requestIdleCallback' in window) window.requestIdleCallback(resolve, { timeout: 1500 });
  else setTimeout(resolve, 200);
});

/** Decodes each URL once per page; resolves to the URLs that loaded, in order. */
function decodeAll(frames) {
  return Promise.all(frames.map((src) => {
    if (!decoded.has(src)) {
      const img = new Image();
      img.decoding = 'async';
      img.src = src;
      decoded.set(src, (img.decode ? img.decode() : Promise.resolve()).then(() => true, () => false));
    }
    return decoded.get(src).then((ok) => (ok ? src : null));
  })).then((list) => list.filter(Boolean));
}

function stopTimer(player) {
  clearTimeout(player.timer);
  player.timer = 0;
}

function show(player, index) {
  const img = $('.character-img', player.figure);
  const src = player.frames[index];
  player.index = index;
  if (img && src && img.getAttribute('src') !== src) img.src = src;
}

function schedule(player) {
  stopTimer(player);
  if (!player.running || !player.visible || document.hidden) return;

  player.timer = setTimeout(() => {
    player.timer = 0;
    const next = player.index + 1;
    if (next >= player.frames.length) {
      if (!player.loop) {
        player.running = false;
        active.delete(player);
        return;
      }
      show(player, 0);
    } else {
      show(player, next);
    }
    schedule(player);
  }, 1000 / player.fps);
}

/** (Re)starts the cycle from frame 1 once every frame is decoded. */
function start(player) {
  stopTimer(player);
  player.running = false;
  active.delete(player);
  show(player, 0);
  if (player.frames.length < 2 || reducedMotion()) return;

  const token = ++player.token;
  whenIdle()
    .then(() => decodeAll(player.frames))
    .then((frames) => {
      if (token !== player.token || frames.length < 2) return;
      player.frames = frames;
      player.running = true;
      active.add(player);
      schedule(player);
    });
}

function observe(player) {
  if (!('IntersectionObserver' in window)) {
    player.visible = true;
    return;
  }

  observer ??= new IntersectionObserver((entries) => {
    for (const entry of entries) {
      const target = players.get(entry.target);
      if (!target || target.visible === entry.isIntersecting) continue;
      target.visible = entry.isIntersecting;
      if (target.visible) start(target);
      else stopTimer(target);
    }
  });
  observer.observe(player.figure);
}

function bind(figure) {
  let player = players.get(figure);
  if (player) return player;

  player = { figure, frames: [], fps: 1, loop: false, index: 0, timer: 0, token: 0, running: false, visible: false };
  players.set(figure, player);
  observe(player);
  return player;
}

/**
 * Plays new frames on a figure (dialogue.js on every pose change). Fewer than
 * two frames stops the animation and leaves the image to the caller.
 */
export function setFrames(figure, frames, { fps = 1, loop = false } = {}) {
  if (!figure) return;
  const player = bind(figure);
  player.frames = frames;
  player.fps = Math.max(1, Number(fps) || 1);
  player.loop = Boolean(loop);

  if (frames.length < 2) {
    stopTimer(player);
    player.token++;
    player.running = false;
    active.delete(player);
    return;
  }
  // Not yet observed as visible: the observer starts it when it appears
  if (player.visible) start(player);
}

/** Binds every `.character[data-frames]` under root; safe to call twice. */
export function initSprites(root = document) {
  for (const figure of $$('.character[data-frames]', root)) {
    if (players.has(figure)) continue;
    setFrames(figure, parseFrames(figure.dataset.frames), {
      fps: figure.dataset.fps,
      loop: figure.dataset.loop === '1',
    });
  }
}

document.addEventListener('visibilitychange', () => {
  for (const player of active) {
    if (document.hidden) stopTimer(player);
    else schedule(player);
  }
});
