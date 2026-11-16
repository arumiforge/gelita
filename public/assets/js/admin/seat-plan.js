/**
 * Seat plan on /admin/sesi-lapangan/{id}/kursi (research revision 10,
 * part 2: 28 §11; 02 → L2). Redraws the plan as seen from the board while
 * the researcher types rows and seats into the form below it. The booklet
 * shown follows the seat's position (odd/even row and seat, as
 * App\Libraries\SeatRules); a locked seat keeps its stored booklet. The
 * server computes and stores the real booklet on save; this is a preview.
 */
import { $, $$ } from '../core/dom.js';

const MAX_NO = 30;

/** odd/odd = B1, odd/even = B2, even/odd = B3, even/even = B4 (SeatRules::booklet) */
export const booklet = (row, seat) => `B${(row % 2 === 0 ? 2 : 0) + (seat % 2 === 0 ? 2 : 1)}`;

const valid = (n) => Number.isInteger(n) && n >= 1 && n <= MAX_NO;

function el(tag, className, text) {
  const node = document.createElement(tag);
  if (className) node.className = className;
  if (text !== undefined) node.textContent = text;
  return node;
}

function read(form) {
  const seated = new Map();
  const unseated = [];
  for (const tr of $$('[data-seat-row]', form)) {
    const row = Number($('[data-seat-input="row"]', tr)?.value || NaN);
    const seat = Number($('[data-seat-input="seat"]', tr)?.value || NaN);
    if (!valid(row) || !valid(seat)) {
      unseated.push(tr.dataset.code);
      continue;
    }
    const key = `${row}:${seat}`;
    if (!seated.has(key)) seated.set(key, { row, seat, codes: [], booklet: tr.dataset.booklet || booklet(row, seat) });
    seated.get(key).codes.push(tr.dataset.code);
  }
  return { seated, unseated };
}

function draw(root, form) {
  const grid = $('[data-seat-grid]', root);
  const { seated, unseated } = read(form);
  const cells = [...seated.values()];
  const rows = Math.max(0, ...cells.map((c) => c.row));
  const seats = Math.max(0, ...cells.map((c) => c.seat));
  const { labelRow, labelSeat } = root.dataset;

  const parts = [el('span', 'seat-axis')];
  for (let n = 1; n <= seats; n++) parts.push(el('span', 'seat-axis', `${labelSeat} ${n}`));
  for (let r = 1; r <= rows; r++) {
    parts.push(el('span', 'seat-axis is-row', `${labelRow} ${r}`));
    for (let n = 1; n <= seats; n++) {
      const cell = seated.get(`${r}:${n}`);
      const b = cell ? cell.booklet : booklet(r, n);
      const shared = cell && cell.codes.length > 1 ? ' is-shared' : '';
      const div = el('div', `seat-cell is-${b.toLowerCase()}${cell ? '' : ' is-empty'}${shared}`);
      div.append(el('b', 'seat-cell-booklet', b), ...(cell ? cell.codes.map((c) => el('span', 'mono', c)) : []));
      parts.push(div);
    }
  }
  grid.replaceChildren(...parts);
  grid.style.setProperty('--seats', String(Math.max(1, seats)));
  grid.hidden = rows === 0;
  $('[data-seat-empty]', root).hidden = rows !== 0;

  const shared = cells.filter((c) => c.codes.length > 1).map((c) => c.codes.join(' & '));
  const clash = $('[data-seat-clash]', root);
  clash.hidden = shared.length === 0;
  $('span', clash).textContent = (root.dataset.labelClash || '').replace('{codes}', shared.join(', '));

  const rest = $('[data-seat-unseated]', root);
  rest.hidden = unseated.length === 0;
  $('.mono', rest).textContent = unseated.join(', ');
}

export function initSeatPlan(root = document.querySelector('[data-seat-plan]')) {
  const form = $$('form').find((f) => $('[data-seat-row]', f));
  if (!root || !form) return;
  let frame = 0;
  form.addEventListener('input', (event) => {
    if (!event.target.matches('[data-seat-input]')) return;
    cancelAnimationFrame(frame);
    frame = requestAnimationFrame(() => draw(root, form));
  });
}
