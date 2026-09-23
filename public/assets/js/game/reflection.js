/**
 * Balai Refleksi — formulir kritik & saran.
 *
 * Bintang 1–5 memakai radio bawaan (klik + panah keyboard). Di sini hanya
 * ditambah pemeriksaan "minimal dua pertanyaan terisi" sebelum kirim; server
 * memeriksa ulang aturan yang sama.
 */
import { $, $$, el, scrollToCenter } from '../core/dom.js';
import { t } from '../core/config.js';
import { preventDoubleSubmit } from './register.js';

export function initReflection() {
  const form = $('[data-screen="reflection"] form[data-min-answers]');
  if (!form) return;

  const min = Number(form.dataset.minAnswers) || 2;
  const areas = $$('textarea', form);
  const notice = el('p', { class: 'field-error reflection-need', role: 'alert', hidden: true }, t('needTwo'));
  $('.form-actions', form)?.before(notice);

  const filled = () => areas.filter((area) => area.value.trim() !== '').length;

  form.addEventListener('input', () => {
    if (filled() >= min) {
      notice.hidden = true;
      areas.forEach((area) => area.closest('.field')?.classList.remove('has-error'));
    }
  });

  // Dipasang sebelum preventDoubleSubmit agar kiriman yang ditahan tidak mengunci tombol
  form.addEventListener('submit', (event) => {
    if (filled() >= min) return;
    event.preventDefault();
    event.stopImmediatePropagation();
    notice.hidden = false;
    const empty = areas.filter((area) => area.value.trim() === '');
    empty.forEach((area) => area.closest('.field')?.classList.add('has-error'));
    scrollToCenter(empty[0]);
    empty[0]?.focus({ preventScroll: true });
  });

  preventDoubleSubmit(form);
}
