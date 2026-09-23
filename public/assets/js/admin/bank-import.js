/**
 * Impor workbook bank soal.
 *
 * - Sebelum Pratinjau: ekstensi .xlsx dan ukuran ≤ 20 MB diperiksa di client
 *   (umpan balik cepat saja — server memvalidasi ulang `ext_in` & `max_size`).
 * - Tabel galat/peringatan dapat difilter per sheet; klik baris menyorot
 *   nomor barisnya agar mudah dicari di workbook.
 * - Tombol Jalankan impor (hanya dirender server bila pratinjau tanpa galat)
 *   meminta konfirmasi "Impor N butir ke M node?".
 */
import { $, $$, el } from '../core/dom.js';
import { confirmDialog } from '../core/modal.js';

const MAX_BYTES = 20 * 1024 * 1024;

function guardUpload(form) {
  const file = $('input[type="file"]', form);
  if (!file) return;

  const error = el('p', { class: 'field-error', role: 'alert', hidden: true });
  file.after(error);

  const check = () => {
    const chosen = file.files?.[0];
    let message = '';
    if (chosen && !/\.xlsx$/i.test(chosen.name)) message = `Berkas "${chosen.name}" bukan workbook .xlsx.`;
    else if (chosen && chosen.size > MAX_BYTES) message = `Berkas ${(chosen.size / 1048576).toFixed(1)} MB melebihi batas 20 MB.`;
    error.textContent = message;
    error.hidden = message === '';
    file.closest('.field')?.classList.toggle('has-error', message !== '');
    return message === '';
  };

  file.addEventListener('change', check);
  form.addEventListener('submit', (event) => {
    if (!check()) {
      event.preventDefault();
      file.focus();
    }
  });
}

function sheetFilters(root) {
  for (const table of $$('table.data-table', root)) {
    const rows = $$('tbody tr', table).filter((row) => row.classList.contains('is-error') || row.classList.contains('is-warning'));
    if (rows.length < 2) continue;

    const sheets = [...new Set(rows.map((row) => row.cells[0]?.textContent.trim()).filter(Boolean))];
    if (sheets.length > 1) {
      const select = el('select', { 'aria-label': 'Tampilkan sheet' },
        el('option', { value: '' }, `Semua sheet (${rows.length})`),
        sheets.map((sheet) => el('option', { value: sheet }, `${sheet} (${rows.filter((r) => r.cells[0]?.textContent.trim() === sheet).length})`)));
      select.addEventListener('change', () => {
        rows.forEach((row) => { row.hidden = select.value !== '' && row.cells[0]?.textContent.trim() !== select.value; });
      });
      table.closest('.table-wrap')?.before(el('div', { class: 'table-filter' }, el('label', {}, 'Sheet ', select)));
    }

    table.addEventListener('click', (event) => {
      const row = event.target.closest('tbody tr');
      if (!row) return;
      rows.forEach((r) => r.classList.toggle('is-selected', r === row && !r.classList.contains('is-selected')));
    });
  }
}

function confirmRun(root) {
  const form = $('form[data-import-run]', root);
  if (!form) return;

  form.addEventListener('submit', async (event) => {
    if (form.dataset.confirmed) return;
    event.preventDefault();
    const ok = await confirmDialog({
      title: 'Jalankan impor?',
      text: `Impor ${form.dataset.items} butir ke ${form.dataset.nodes} node? Impor berjalan dalam satu transaksi.`,
      confirmLabel: 'Ya, impor',
      cancelLabel: 'Batal',
    });
    if (!ok) return;
    form.dataset.confirmed = '1';
    $$('button[type="submit"]', form).forEach((button) => { button.disabled = true; });
    form.submit();
  });
}

export function initBankImport(root = document) {
  const upload = $('form[action$="impor-bank/pratinjau"]', root);
  if (!upload) return;
  guardUpload(upload);
  sheetFilters(root);
  confirmRun(root);
}
