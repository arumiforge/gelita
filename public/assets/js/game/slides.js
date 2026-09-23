/**
 * Slide bersama untuk intro, dialog, dan buku Pustaka.
 *
 * Tanpa JavaScript, slide berpindah lewat jangkar #prefix-n + CSS :target.
 * Di sini perpindahan tetap memakai hash yang sama, tetapi lewat
 * location.replace('#…'): :target ikut berubah (CSS tidak perlu aturan kedua)
 * dan riwayat browser tidak bertambah satu entri per slide — tombol Back
 * kembali ke halaman sebelumnya, bukan ke slide sebelumnya.
 *
 * Spasi dan panah kanan = lanjut, panah kiri = kembali (cara tercepat di
 * papan tulis interaktif). Slide terakhir: lanjut mengikuti tautan akhirnya.
 */
import { $, $$ } from '../core/dom.js';
import { pauseNarration } from '../core/audio.js';

const INTERACTIVE = 'input, textarea, select, button, summary, video, audio, [contenteditable="true"]';

/**
 * @param {{ list: HTMLElement, prefix: string, onChange?: (index: number, slide: HTMLElement, total: number) => void }} options
 */
export function initSlides({ list, prefix, onChange = null }) {
  if (!list) return null;

  const slides = $$(':scope > .slide', list);
  const total = slides.length;
  if (!total) return null;

  const indexFromHash = () => {
    const match = window.location.hash.match(new RegExp(`^#${prefix}(\\d+)$`));
    const n = match ? Number(match[1]) : 1;
    return Math.min(total, Math.max(1, n));
  };

  let current = indexFromHash();

  const show = (n, { notify = true } = {}) => {
    const target = Math.min(total, Math.max(1, n));
    const changed = target !== current;
    current = target;

    if (window.location.hash !== `#${prefix}${target}`) {
      window.location.replace(`#${prefix}${target}`);
    }

    slides.forEach((slide, i) => slide.classList.toggle('is-current', i + 1 === target));
    if (changed) pauseNarration();

    const slide = slides[target - 1];
    // Fokus ke isi slide agar pembaca layar membaca slide baru
    const focusTarget = $('h2, .slide-text, .dialogue-text', slide) || slide;
    if (changed && focusTarget) {
      focusTarget.setAttribute('tabindex', '-1');
      focusTarget.focus({ preventScroll: true });
    }

    if (notify && onChange) onChange(target, slide, total);
  };

  // Jangkar #prefix-n di dalam navigasi slide
  list.addEventListener('click', (event) => {
    const link = event.target.closest(`a[href^="#${prefix}"]`);
    if (!link) return;
    const n = Number(link.getAttribute('href').slice(prefix.length + 1));
    if (!Number.isFinite(n)) return;
    event.preventDefault();
    show(n);
  });

  document.addEventListener('keydown', (event) => {
    if (event.defaultPrevented || event.altKey || event.ctrlKey || event.metaKey) return;
    if (document.body.classList.contains('has-modal')) return;
    if (event.target instanceof Element && event.target.closest(INTERACTIVE)) return;

    if (event.key === 'ArrowRight' || event.key === ' ' || event.key === 'PageDown') {
      event.preventDefault();
      if (current < total) {
        show(current + 1);
      } else {
        // Slide terakhir: ikuti tautan akhirnya (peta / wilayah / tutup)
        const final = $('.slide-nav a.btn-primary:not([href^="#"])', slides[total - 1]);
        if (final) final.click();
      }
    } else if (event.key === 'ArrowLeft' || event.key === 'PageUp') {
      event.preventDefault();
      if (current > 1) show(current - 1);
    }
  });

  // Tombol Back/Forward browser di dalam hash yang sama
  window.addEventListener('hashchange', () => {
    const n = indexFromHash();
    if (n !== current) show(n);
  });

  // Keadaan awal: tanpa mengubah URL (dan tanpa menggulir melewati kartu sambutan)
  list.dataset.managed = '1';
  slides.forEach((slide, i) => slide.classList.toggle('is-current', i + 1 === current));

  return { show, get current() { return current; }, total };
}
