/**
 * Unggah narasi banyak berkas.
 *
 * PHP membuang diam-diam berkas di atas `max_file_uploads`, dan kiriman yang
 * melebihi `post_max_size` hilang seluruhnya (halaman lalu menolak
 * permintaan). Modul ini membaca batas itu dari atribut data formulir,
 * memeriksa pilihan sebelum dikirim, dan menahan kiriman yang pasti gagal.
 * Pencocokan nama berkas tetap dilakukan server (NarrationImporter).
 */
import { $, el } from '../core/dom.js';

const MB = 1048576;
const mb = (bytes) => `${(bytes / MB).toFixed(1).replace('.', ',')} MB`;

export function initNarrationUpload(root = document) {
  const form = $('form[data-narration-upload]', root);
  if (!form) return;

  const input = $('input[type="file"]', form);
  const maxFiles = Number(form.dataset.maxFiles) || 0;
  const maxFile = Number(form.dataset.maxFileBytes) || 0;
  const maxPost = Number(form.dataset.maxPostBytes) || 0;
  const message = el('p', { class: 'field-error', role: 'alert', hidden: true });
  const info = el('p', { class: 'field-help', 'aria-live': 'polite' });
  input.after(message, info);

  const problems = () => {
    const files = Array.from(input.files || []);
    const total = files.reduce((sum, file) => sum + file.size, 0);
    const out = [];
    if (maxFiles > 0 && files.length > maxFiles) out.push(`${files.length} berkas dipilih, server hanya menerima ${maxFiles} per unggahan. Bagi menjadi beberapa kali unggah.`);
    if (maxPost > 0 && total > maxPost) out.push(`Total ${mb(total)} melebihi batas kiriman ${mb(maxPost)}.`);
    const big = files.filter((file) => maxFile > 0 && file.size > maxFile).map((file) => file.name);
    if (big.length) out.push(`Melebihi ${mb(maxFile)} per berkas: ${big.join(', ')}.`);
    info.textContent = files.length ? `${files.length} berkas, total ${mb(total)}.` : '';
    return out;
  };

  const show = (list) => {
    message.textContent = list.join(' ');
    message.hidden = list.length === 0;
    input.closest('.field')?.classList.toggle('has-error', list.length > 0);
  };

  input.addEventListener('change', () => show(problems()));
  form.addEventListener('submit', (event) => {
    const list = problems();
    show(list);
    if (list.length) {
      event.preventDefault();
      input.focus();
    }
  });
}
