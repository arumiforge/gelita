/**
 * Monitoring panel (research revision 10, part 2: 28 §12).
 *
 * Polls GET /api/admin/monitoring every 30 seconds, with a longer pause after
 * a failure (the export.js pattern), and updates the numbers and code lists
 * the server rendered. The request carries `X-Gelita-Background: 1`, so it
 * is not counted as staff activity. When a field session appears or leaves,
 * the page reloads so the server renders its card.
 */
import { $, $$ } from '../core/dom.js';

const INTERVAL = 30000;
const MAX_DELAY = 5 * 60 * 1000;
const NUMBERS = ['entered', 'active', 'not_entered', 'all_done', 'dropped', 'no_seat', 'small_screen'];

const fmt = (n) => Number(n).toLocaleString('id-ID');
const codes = (students, keep) => students.filter(keep).map((s) => s.participant_code).join(', ') || '—';

function cell(text, className = '') {
  const td = document.createElement('td');
  td.textContent = text;
  if (className) td.className = className;
  return td;
}

function render(root, sessions) {
  const shown = $$('[data-field-session]', root).map((c) => c.dataset.fieldSession).sort().join(',');
  if (shown !== sessions.map((s) => String(s.id)).sort().join(',')) {
    window.location.reload();
    return;
  }

  for (const s of sessions) {
    const card = $(`[data-field-session="${s.id}"]`, root);
    if (!card) continue;
    for (const key of NUMBERS) {
      const el = $(`[data-m="${key}"]`, card);
      if (el) el.textContent = fmt(s[key]);
    }
    const token = $('[data-m="token"]', card);
    if (token && s.token) token.textContent = s.token;
    if (!Array.isArray(s.students)) continue;   // admin: numbers only (K2)

    const list = (key, keep) => {
      const el = $(`[data-m="${key}"]`, card);
      if (el) el.textContent = codes(s.students, keep);
    };
    list('list-active', (x) => x.active);
    list('list-not-entered', (x) => !x.entered);
    list('list-no-seat', (x) => !x.has_seat);

    const tbody = $('[data-m="rows"]', card);
    if (tbody) {
      tbody.replaceChildren(...s.students.map((x) => {
        const tr = document.createElement('tr');
        tr.append(cell(x.participant_code, 'mono'), cell(`${x.done}/${x.total}`), cell(String(x.leftovers)), cell(String(x.dropped)));
        return tr;
      }));
    }
  }
}

export function initMonitoring(root = document.querySelector('[data-monitoring]')) {
  if (!root) return;
  const status = $('[data-monitoring-status]', root);
  let delay = INTERVAL;

  const tick = async () => {
    try {
      const res = await fetch(root.dataset.endpoint, {
        credentials: 'same-origin',
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-Gelita-Background': '1' },
      });
      if (!res.ok) throw new Error(String(res.status));
      const json = await res.json();
      render(root, (json.data ?? json).sessions ?? []);
      delay = INTERVAL;
      if (status) status.textContent = status.dataset.ok;
    } catch {
      delay = Math.min(delay * 2, MAX_DELAY);
      if (status) status.textContent = status.dataset.stale;
    }
    setTimeout(tick, delay);
  };

  setTimeout(tick, INTERVAL);
}
