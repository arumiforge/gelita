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

/**
 * "Perlu perhatian": the codes behind each amber count (not signed in, lost
 * records with their number, no seat); an empty group hides, and a calm
 * note shows when nothing needs attention.
 */
function attention(card, students) {
  const box = $('[data-attention]', card);
  if (!box) return;
  const groups = {
    'not-entered': students.filter((x) => !x.entered).map((x) => x.participant_code),
    dropped: students.filter((x) => Number(x.dropped) > 0).map((x) => `${x.participant_code} (${fmt(x.dropped)})`),
    'no-seat': students.filter((x) => !x.has_seat).map((x) => x.participant_code),
  };
  let calm = true;
  for (const [key, list] of Object.entries(groups)) {
    const item = $(`[data-attention-item="${key}"]`, box);
    if (!item) continue;
    item.hidden = list.length === 0;
    calm &&= list.length === 0;
    const count = $('[data-attention-count]', item);
    if (count) count.textContent = fmt(list.length);
    const dd = $(`[data-m="list-${key}"]`, item);
    if (dd) dd.textContent = list.join(', ') || '—';
  }
  box.classList.toggle('is-calm', calm);
  const none = $('[data-attention-none]', box);
  if (none) none.hidden = !calm;
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
      if (!el) continue;
      el.textContent = fmt(s[key]);
      // Counts that need attention (not signed in, lost records, no seat, small screen) light up above zero
      const tile = el.closest('[data-alert]');
      if (tile) tile.classList.toggle('is-alert', Number(s[key]) > 0);
    }
    const token = $('[data-m="token"]', card);
    if (token && s.token) token.textContent = s.token;
    if (!Array.isArray(s.students)) continue;   // admin: numbers only (K2)

    const list = (key, keep) => {
      const el = $(`[data-m="${key}"]`, card);
      if (el) el.textContent = codes(s.students, keep);
    };
    list('list-active', (x) => x.active);
    attention(card, s.students);

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
      if (status) {
        const time = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        status.textContent = `${status.dataset.ok} ${(status.dataset.updatedAt || '').replace('{time}', time)}`.trim();
      }
    } catch {
      delay = Math.min(delay * 2, MAX_DELAY);
      if (status) status.textContent = status.dataset.stale;
    }
    setTimeout(tick, delay);
  };

  setTimeout(tick, INTERVAL);
}
