/**
 * Perbesar gambar dalam <dialog> bawaan peramban (Esc menutup, fokus
 * terkunci). Dipakai Pustaka (galeri) dan Benar atau Salah (gambar pernyataan).
 *
 * Dialog modal dirender peramban di top layer, di luar #stage, tetapi
 * ukurannya dalam rem (= dp panggung) sehingga tetap di dalam panggung 16:9:
 * Pustaka selebar panggung, gambar pernyataan 640 × 480 dp (Dok 3 §6).
 */
import { el } from './dom.js';

/**
 * @param {{ closeLabel?: string, loadingLabel?: string, variant?: string }} options
 * @returns {((src: string, alt?: string, caption?: string) => void) | null}
 */
export function createLightbox({ closeLabel = 'Tutup', loadingLabel = '', variant = '' } = {}) {
  if (typeof HTMLDialogElement === 'undefined') return null;

  const image = el('img', { alt: '' });
  const spinner = el('span', { class: 'lightbox-spinner', role: 'status', 'aria-label': loadingLabel });
  const stage = el('div', { class: 'lightbox-stage' }, image, spinner);
  const caption = el('p', { class: 'lightbox-caption' });
  const close = el('button', { type: 'button', class: 'icon-btn lightbox-close', 'aria-label': closeLabel, title: closeLabel }, '×');
  const dialog = el('dialog', { class: `lightbox${variant ? ` is-${variant}` : ''}` }, close, stage, caption);
  (document.getElementById('stage') || document.body).append(dialog);

  // The owner's dialog frame (game.css): the wide one for a picture wider than 2 : 1
  const ready = () => {
    dialog.classList.remove('is-loading');
    if (image.naturalWidth > 0) dialog.dataset.frame = image.naturalWidth / image.naturalHeight > 2 ? 'wide' : 'compact';
  };
  image.addEventListener('load', ready);
  image.addEventListener('error', ready);

  close.addEventListener('click', () => dialog.close());
  dialog.addEventListener('click', (event) => { if (event.target === dialog) dialog.close(); });

  return (src, alt = '', text = '') => {
    dialog.classList.add('is-loading');
    image.src = src;
    image.alt = alt;
    // Gambar yang sama dengan sebelumnya tidak memicu `load` lagi
    if (image.complete && image.naturalWidth > 0) ready();
    caption.textContent = text;
    caption.hidden = text === '';
    dialog.showModal();
  };
}
