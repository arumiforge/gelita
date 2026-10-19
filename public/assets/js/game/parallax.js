/**
 * Parallax depth for the narrated scenes (owner, 19 October 2026). One shared
 * value in -1…1 per axis, smoothed every frame, written to `#stage` as the CSS
 * variables `--plx`/`--ply` (class `.has-parallax` while it runs).
 * cinematic.css moves the background a little and the characters more;
 * game/screen-fx.js reads parallaxValue() to move its fog and sparks as the
 * nearest layers.
 *
 * Source, first that applies:
 *   pointer  a mouse or pen over the page (laptops, interactive whiteboards)
 *   tilt     device orientation where the browser sends it without asking
 *            (no permission prompt for the students: iOS asks, so iOS skips it)
 *   sway     otherwise a slow drift, so a touch-only tablet still shows depth
 *
 * Several narrators may share a page (the Kenali overlays on the map), so
 * acquireParallax()/releaseParallax() count users; the last release stops
 * the loop and puts every layer back. Callers skip it under reduced motion.
 */

const EASE = 0.06;
const IDLE_MS = 4000;

const target = { x: 0, y: 0 };
const value = { x: 0, y: 0 };
let users = 0;
let frame = 0;
let lastInput = 0;
let tiltBase = null;
let stage = null;

const clamp = (n) => Math.max(-1, Math.min(1, n));

function onPointer(event) {
  if (event.pointerType === 'touch' || !stage) return;
  const box = stage.getBoundingClientRect();
  target.x = clamp(((event.clientX - box.left) / box.width - 0.5) * 2);
  target.y = clamp(((event.clientY - box.top) / box.height - 0.5) * 2);
  lastInput = performance.now();
}

function onTilt(event) {
  if (event.beta === null || event.gamma === null) return;
  // Landscape (the stage is 16:9): the long side follows beta, not gamma
  const angle = screen.orientation?.angle ?? window.orientation ?? 0;
  const across = angle === 90 ? event.beta : angle === -90 || angle === 270 ? -event.beta : event.gamma;
  const along = angle === 90 || angle === -90 || angle === 270 ? event.gamma : event.beta;
  tiltBase ??= { across, along };
  target.x = clamp((across - tiltBase.across) / 20);
  target.y = clamp((along - tiltBase.along) / 20);
  lastInput = performance.now();
}

function tick(now) {
  frame = requestAnimationFrame(tick);
  if (now - lastInput > IDLE_MS) {
    target.x = Math.sin(now / 7600) * 0.45;
    target.y = Math.sin(now / 11100) * 0.3;
  }
  const dx = target.x - value.x;
  const dy = target.y - value.y;
  if (Math.abs(dx) < 0.0005 && Math.abs(dy) < 0.0005) return;
  value.x += dx * EASE;
  value.y += dy * EASE;
  stage.style.setProperty('--plx', value.x.toFixed(4));
  stage.style.setProperty('--ply', value.y.toFixed(4));
}

/** Current smoothed value; zero while nothing uses it. */
export function parallaxValue() {
  return value;
}

export function acquireParallax() {
  users += 1;
  if (users > 1) return;
  stage = document.getElementById('stage');
  if (!stage) return;
  stage.classList.add('has-parallax');
  window.addEventListener('pointermove', onPointer, { passive: true });
  if ('DeviceOrientationEvent' in window && typeof DeviceOrientationEvent.requestPermission !== 'function') {
    window.addEventListener('deviceorientation', onTilt, { passive: true });
  }
  lastInput = 0;
  frame = requestAnimationFrame(tick);
}

export function releaseParallax() {
  if (users === 0) return;
  users -= 1;
  if (users > 0 || !stage) return;
  cancelAnimationFrame(frame);
  window.removeEventListener('pointermove', onPointer);
  window.removeEventListener('deviceorientation', onTilt);
  stage.classList.remove('has-parallax');
  stage.style.removeProperty('--plx');
  stage.style.removeProperty('--ply');
  target.x = target.y = value.x = value.y = 0;
  tiltBase = null;
}
