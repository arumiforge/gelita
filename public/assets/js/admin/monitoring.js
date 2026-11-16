/**
 * Monitoring panel (research revision 10, part 2: 28 §12).
 *
 * Polls GET /api/admin/monitoring every 30 seconds, with a longer pause after
 * a failure (the export.js pattern), and updates the numbers and code lists
 * the server rendered. The request carries `X-Gelita-Background: 1`, so it
 * is not counted as staff activity. When a field session appears or leaves,
 * the page reloads so the server renders its card.
 *
 * "Bunyikan peringatan" (opt-in, off by default, remembered per device):
 * after a refresh, when a count that needs attention rises or a new code
 * joins a "Perlu perhatian" list, a short Web Audio tone plays, the device
 * vibrates where it can, and the card pulses (no pulse with reduced
 * motion). Never on the first load: the page's own numbers are the start.
 */
import { $, $$ } from '../core/dom.js';

const INTERVAL = 30000;
const MAX_DELAY = 5 * 60 * 1000;
const NUMBERS = ['entered', 'active', 'not_entered', 'all_done', 'dropped', 'no_seat', 'small_screen'];
const ALERTS = ['not_entered', 'dropped', 'no_seat', 'small_screen'];
const SOUND_KEY = 'gelita.monitoring.sound';

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

const store = {
  get() { try { return window.localStorage.getItem(SOUND_KEY) === '1'; } catch { return false; } },
  set(on) { try { window.localStorage.setItem(SOUND_KEY, on ? '1' : '0'); } catch { /* private window: not remembered */ } },
};

/** What one card needs attention for: counts and the codes behind them. */
function snapshot(s) {
  const codes = new Set();
  if (Array.isArray(s.students)) {
    for (const x of s.students) {
      if (!x.entered) codes.add(`n:${x.participant_code}`);
      if (Number(x.dropped) > 0) codes.add(`d:${x.participant_code}`);
      if (!x.has_seat) codes.add(`s:${x.participant_code}`);
    }
  }
  return { counts: Object.fromEntries(ALERTS.map((k) => [k, Number(s[k]) || 0])), codes };
}

/** True when $now shows a problem $before did not: a count rose or a code joined a list. */
function worse(before, now) {
  if (!before) return false;
  if (ALERTS.some((k) => now.counts[k] > before.counts[k])) return true;
  for (const c of now.codes) if (!before.codes.has(c)) return true;
  return false;
}

function createSignal(root) {
  const toggle = $('[data-alert-sound]', root);
  const live = $('[data-alert-live]', root);
  let audio = null;
  let on = false;
  if (toggle) {
    on = store.get();
    toggle.checked = on;
    toggle.disabled = false;
    toggle.addEventListener('change', () => {
      on = toggle.checked;
      store.set(on);
      // Browsers start audio only from a gesture: create (or wake) it on this click
      if (on) {
        try {
          audio ??= new (window.AudioContext || window.webkitAudioContext)();
          audio.resume?.();
        } catch { audio = null; }
      }
    });
  }

  const tone = () => {
    if (!audio) return;
    try {
      const t = audio.currentTime;
      for (const [start, freq] of [[0, 880], [0.18, 1175]]) {
        const osc = audio.createOscillator();
        const gain = audio.createGain();
        osc.type = 'sine';
        osc.frequency.value = freq;
        gain.gain.setValueAtTime(0.0001, t + start);
        gain.gain.exponentialRampToValueAtTime(0.25, t + start + 0.02);
        gain.gain.exponentialRampToValueAtTime(0.0001, t + start + 0.16);
        osc.connect(gain).connect(audio.destination);
        osc.start(t + start);
        osc.stop(t + start + 0.18);
      }
    } catch { /* no sound on this device */ }
  };

  return (cards) => {
    for (const card of cards) {
      card.classList.remove('is-new');
      void card.offsetWidth;   // restart the pulse
      card.classList.add('is-new');
    }
    if (live) live.textContent = live.dataset.text || '';
    if (!on) return;
    // A page opened with the switch already on has no gesture yet: create it now; it may stay silent until a click
    if (!audio) {
      try { audio = new (window.AudioContext || window.webkitAudioContext)(); } catch { audio = null; }
    }
    audio?.resume?.();
    tone();
    if (typeof navigator.vibrate === 'function') navigator.vibrate([180, 90, 180]);
  };
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
  const signal = createSignal(root);
  let delay = INTERVAL;
  // Baseline: the numbers the server rendered with the page (never a signal on the first load)
  let last = new Map();
  try {
    const initial = JSON.parse($('[data-monitoring-initial]', root)?.textContent || '[]');
    last = new Map(initial.map((s) => [String(s.id), snapshot(s)]));
  } catch { /* no baseline: the first refresh becomes it */ }

  const tick = async () => {
    try {
      const res = await fetch(root.dataset.endpoint, {
        credentials: 'same-origin',
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-Gelita-Background': '1' },
      });
      if (!res.ok) throw new Error(String(res.status));
      const json = await res.json();
      const sessions = (json.data ?? json).sessions ?? [];
      render(root, sessions);
      const next = new Map(sessions.map((s) => [String(s.id), snapshot(s)]));
      const raised = sessions.filter((s) => worse(last.get(String(s.id)), next.get(String(s.id))))
        .map((s) => $(`[data-field-session="${s.id}"]`, root)).filter(Boolean);
      if (raised.length) signal(raised);
      last = next;
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
