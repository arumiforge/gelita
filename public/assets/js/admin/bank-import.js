/**
 * Impor workbook bank soal.
 *
 * - Sebelum Pratinjau: ekstensi .xlsx dan ukuran ≤ 20 MB diperiksa di client
 *   (umpan balik cepat saja — server memvalidasi ulang `ext_in` & `max_size`).
 * - Tabel galat/peringatan dapat difilter per sheet; klik baris menyorot
 *   nomor barisnya agar mudah dicari di workbook.
 * - Tombol "Simpan soal ke permainan" (hanya dirender server bila pratinjau
 *   tanpa galat) meminta konfirmasi berisi jumlah soal dan tantangan.
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
    if (chosen && !/\.xlsx$/i.test(chosen.name)) message = `Berkas "${chosen.name}" bukan berkas Excel .xlsx. Bila berkas Anda .xls atau .csv, buka di Excel lalu simpan ulang sebagai "Excel Workbook (.xlsx)".`;
    else if (chosen && chosen.size > MAX_BYTES) message = `Berkas ini ${(chosen.size / 1048576).toFixed(1).replace('.', ',')} MB, lebih besar dari batas 20 MB.`;
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

    // Sel pertama: kode sheet + nama Indonesianya (data-sheet), mis. "items · Soal"
    const sheetOf = (row) => (row.cells[0]?.dataset.sheet ?? row.cells[0]?.textContent ?? '').trim();
    const sheets = [...new Set(rows.map(sheetOf).filter(Boolean))];
    if (sheets.length > 1) {
      const select = el('select', { 'aria-label': 'Tampilkan sheet' },
        el('option', { value: '' }, `Semua sheet (${rows.length})`),
        sheets.map((sheet) => el('option', { value: sheet }, `${sheet} (${rows.filter((r) => sheetOf(r) === sheet).length})`)));
      select.addEventListener('change', () => {
        rows.forEach((row) => { row.hidden = select.value !== '' && sheetOf(row) !== select.value; });
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
    const items = Number(form.dataset.items || 0);
    const ok = await confirmDialog({
      title: 'Simpan soal ke permainan?',
      text: `${items > 0 ? `${items} soal untuk ${form.dataset.nodes} tantangan` : 'Isi berkas ini'} akan disimpan dan langsung dipakai siswa yang mulai bermain setelahnya. Bila ada satu baris yang gagal, tidak ada yang disimpan.`,
      confirmLabel: 'Ya, simpan',
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
