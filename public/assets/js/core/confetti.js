/**
 * Konfeti ringan di atas <canvas>: maksimum 60 partikel, ±2,5 detik.
 * `prefers-reduced-motion` → tidak digambar sama sekali (aturan 9).
 */
import { reducedMotion } from './dom.js';

const MAX = 60;
const DURATION = 2600;

function palette() {
  const style = getComputedStyle(document.documentElement);
  return ['--gold-500', '--gold-300', '--ok', '--warn']
    .map((name) => style.getPropertyValue(name).trim())
    .filter(Boolean);
}

export function confetti(count = MAX) {
  if (reducedMotion()) return;

  const canvas = document.createElement('canvas');
  canvas.className = 'confetti-canvas';
  canvas.setAttribute('aria-hidden', 'true');
  document.body.append(canvas);

  const ctx = canvas.getContext('2d');
  if (!ctx) {
    canvas.remove();
    return;
  }

  const ratio = window.devicePixelRatio || 1;
  const width = window.innerWidth;
  const height = window.innerHeight;
  canvas.width = width * ratio;
  canvas.height = height * ratio;
  ctx.scale(ratio, ratio);

  const colors = palette();
  const pieces = Array.from({ length: Math.min(MAX, count) }, () => ({
    x: width / 2 + (Math.random() - 0.5) * width * 0.4,
    y: height * 0.35,
    vx: (Math.random() - 0.5) * 9,
    vy: -Math.random() * 9 - 3,
    size: 6 + Math.random() * 6,
    rotation: Math.random() * Math.PI,
    spin: (Math.random() - 0.5) * 0.3,
    color: colors[Math.floor(Math.random() * colors.length)] || '#DFC087',
  }));

  const start = performance.now();

  const frame = (time) => {
    const progress = (time - start) / DURATION;
    ctx.clearRect(0, 0, width, height);

    for (const p of pieces) {
      p.vy += 0.25;
      p.x += p.vx;
      p.y += p.vy;
      p.rotation += p.spin;
      ctx.save();
      ctx.globalAlpha = Math.max(0, 1 - progress);
      ctx.translate(p.x, p.y);
      ctx.rotate(p.rotation);
      ctx.fillStyle = p.color;
      ctx.fillRect(-p.size / 2, -p.size / 4, p.size, p.size / 2);
      ctx.restore();
    }

    if (progress < 1) requestAnimationFrame(frame);
    else canvas.remove();
  };

  requestAnimationFrame(frame);
}
