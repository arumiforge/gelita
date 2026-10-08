/**
 * Tabel data panel (Dok 3 §8.2).
 *
 * Tabel server (components/data-table.php, data-sortable="server"): urut,
 * halaman, dan CSV sudah berupa tautan biasa. Di sini hanya kenyamanan:
 * - pilihan "Baris per halaman" langsung diterapkan;
 * - bayangan kolom pertama lengket saat tabel digulir ke samping;
 * - baris rincian (riwayat percobaan) disembunyikan dan dibuka tombol.
 *
 * Tabel kecil tanpa pagination (components/admin-table.php,
 * data-sortable="client", ≤ 500 baris): urut kolom di client dan kotak
 * "Saring baris" (≥ 10 baris). Angka dibaca dalam format Indonesia
 * (1.234,5 · 85,0% · 2:05 · 23 Sep 2026 10:14); sel kosong "—" selalu di
 * akhir.
 */
import { $$, el } from '../core/dom.js';

const MONTHS = { jan: 0, feb: 1, mar: 2, apr: 3, mei: 4, may: 4, jun: 5, jul: 6, agu: 7, ags: 7, aug: 7, sep: 8, okt: 9, oct: 9, nov: 10, des: 11, dec: 11 };
const FILTER_MIN_ROWS = 10;

/** Nilai urut sebuah sel: number, atau string huruf kecil; null = kosong. */
export function sortValue(cell) {
  if (!cell) return null;
  if (cell.dataset.sort !== undefined) {
    const n = Number(cell.dataset.sort);
    return Number.isNaN(n) ? cell.dataset.sort.toLowerCase() : n;
  }

  const text = cell.textContent.replace(/\s+/g, ' ').trim();
  if (text === '' || text === '—') return null;

  const date = text.match(/^(\d{1,2}) ([A-Za-z]{3}) (\d{4})(?: (\d{2}):(\d{2})(?::(\d{2}))?)?/);
  if (date && MONTHS[date[2].toLowerCase()] !== undefined) {
    return new Date(Number(date[3]), MONTHS[date[2].toLowerCase()], Number(date[1]),
      Number(date[4] || 0), Number(date[5] || 0), Number(date[6] || 0)).getTime();
  }

  const clock = text.match(/^(\d+):(\d{2})(?::(\d{2}))?$/);
  if (clock) {
    return clock[3] !== undefined
      ? Number(clock[1]) * 3600 + Number(clock[2]) * 60 + Number(clock[3])
      : Number(clock[1]) * 60 + Number(clock[2]);
  }

  // "1.234,5" · "85,0%" · "512 KB" · "12 tahun" · "3 kali" · "4 siswa"
  const bare = text.replace(/\s*(%|KB|th|tahun|kali|detik|menit|percobaan|bukti|jawaban|siswa|soal)$/i, '');
  if (/^[+-]?(\d{1,3}(\.\d{3})+|\d+)(,\d+)?$/.test(bare)) {
    return Number(bare.replace(/\./g, '').replace(',', '.'));
  }

  return text.toLowerCase();
}

function compare(a, b) {
  if (a === null && b === null) return 0;
  if (a === null) return 1;
  if (b === null) return -1;
  if (typeof a === 'number' && typeof b === 'number') return a - b;
  return String(a).localeCompare(String(b), 'id', { numeric: true });
}

function enhanceSort(table) {
  const headers = $$('thead th', table);
  const body = table.tBodies[0];
  if (!body || body.rows.length < 2) return;

  headers.forEach((th, index) => {
    if (th.classList.contains('is-actions') || th.textContent.trim() === '') return;

    const button = el('button', { type: 'button', class: 'th-sort' });
    button.append(...Array.from(th.childNodes));
    button.append(el('span', { class: 'th-sort-icon', 'aria-hidden': 'true' }));
    th.append(button);

    button.addEventListener('click', () => {
      const ascending = th.getAttribute('aria-sort') !== 'ascending';
      headers.forEach((other) => other.removeAttribute('aria-sort'));
      th.setAttribute('aria-sort', ascending ? 'ascending' : 'descending');

      const rows = Array.from(body.rows).map((row, position) => ({ row, position, value: sortValue(row.cells[index]) }));
      rows.sort((a, b) => {
        const result = compare(a.value, b.value);
        // kosong selalu di akhir; baris setara mempertahankan urutan server
        if (a.value === null || b.value === null) return result || a.position - b.position;
        return (ascending ? result : -result) || a.position - b.position;
      });
      body.append(...rows.map((entry) => entry.row));
    });
  });
}

function enhanceFilter(table) {
  const body = table.tBodies[0];
  const wrap = table.closest('.table-wrap');
  if (!body || !wrap || body.rows.length < FILTER_MIN_ROWS || wrap.previousElementSibling?.classList.contains('table-filter')) return;

  const total = body.rows.length;
  const count = el('span', { class: 'table-filter-count', 'aria-live': 'polite' });
  const input = el('input', { type: 'search', placeholder: 'Saring baris di tabel ini…', 'aria-label': 'Saring baris tabel' });
  wrap.before(el('div', { class: 'table-filter' }, input, count));

  input.addEventListener('input', () => {
    const needle = input.value.trim().toLowerCase();
    let shown = 0;
    for (const row of body.rows) {
      const match = needle === '' || row.textContent.toLowerCase().includes(needle);
      row.hidden = !match;
      if (match) shown++;
    }
    count.textContent = needle === '' ? '' : `${shown} dari ${total} baris`;
  });
}

function enhanceServer(table) {
  const wrap = table.closest('.table-wrap');
  if (wrap) {
    const update = () => wrap.classList.toggle('is-scrolled', wrap.scrollLeft > 0);
    wrap.addEventListener('scroll', update, { passive: true });
    update();
  }

  for (const row of table.querySelectorAll('tr.row-expand')) {
    const owner = row.previousElementSibling;
    const cell = owner?.cells[0];
    if (!cell) continue;
    row.hidden = true;
    const button = el('button', { type: 'button', class: 'expand-toggle', 'aria-expanded': 'false', 'aria-controls': row.id });
    button.append(el('span', {}, row.dataset.label || 'Rincian'), el('span', { class: 'expand-caret', 'aria-hidden': 'true' }));
    cell.append(' ', button);
    button.addEventListener('click', () => {
      const open = row.hidden;
      row.hidden = !open;
      button.setAttribute('aria-expanded', String(open));
    });
  }
}

function enhancePageSize(root) {
  for (const select of root.querySelectorAll('select[data-auto-submit]')) {
    if (select.dataset.ready) continue;
    select.dataset.ready = '1';
    select.addEventListener('change', () => select.form?.requestSubmit());
  }
}

export function initTables(root = document) {
  for (const table of root.querySelectorAll('table.data-table')) {
    if (table.dataset.enhanced) continue;
    table.dataset.enhanced = '1';
    if (table.dataset.sortable === 'server') {
      enhanceServer(table);
      continue;
    }
    if (table.dataset.sortable === 'client') enhanceSort(table);
    enhanceFilter(table);
  }
  enhancePageSize(root);
}
