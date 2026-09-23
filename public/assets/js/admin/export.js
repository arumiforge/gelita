/**
 * Status ekspor.
 *
 * Baris berstatus queued/running dipantau lewat
 * GET /api/admin/exports/{id}/status tiap 2 detik, maksimum 5 menit, dengan
 * jeda lebih panjang bila permintaan gagal. Begitu statusnya done/failed,
 * tabel ekspor dimuat ulang dari server (tanpa memuat ulang halaman), sehingga
 * tombol Unduh, jumlah baris, dan SHA-256 berkasnya tampil persis seperti
 * yang dirender server.
 */
import { $$ } from '../core/dom.js';
import { apiRequest } from '../core/api.js';
import { toast } from '../core/toast.js';

const INTERVAL = 2000;
const MAX_MS = 5 * 60 * 1000;
const LABELS = { queued: 'antre', running: 'diproses', done: 'siap', failed: 'gagal' };

async function refreshTable(badge) {
  const panel = badge.closest('section.panel');
  try {
    const res = await fetch(window.location.href, { credentials: 'same-origin', headers: { Accept: 'text/html' } });
    const doc = new DOMParser().parseFromString(await res.text(), 'text/html');
    const index = $$('section.panel', document).indexOf(panel);
    const fresh = $$('section.panel', doc)[index];
    if (panel && fresh) panel.replaceWith(document.importNode(fresh, true));
    else window.location.reload();
  } catch {
    window.location.reload();
  }
}

function watch(badge) {
  const id = badge.dataset.exportId;
  const started = Date.now();
  let delay = INTERVAL;

  badge.classList.add('is-running');
  badge.setAttribute('aria-live', 'polite');

  const tick = async () => {
    if (Date.now() - started > MAX_MS) {
      badge.textContent = `${LABELS[badge.dataset.status] || badge.dataset.status} · muat ulang untuk memeriksa`;
      return;
    }
    try {
      const status = await apiRequest(`/admin/exports/${id}/status`, { retry: false });
      delay = INTERVAL;
      if (status.status === 'done' || status.status === 'failed') {
        toast(status.status === 'done' ? `Ekspor #${id} siap diunduh.` : `Ekspor #${id} gagal: ${status.error || 'lihat log'}`, status.status === 'done' ? 'ok' : 'bad');
        await refreshTable(badge);
        return;
      }
      badge.textContent = LABELS[status.status] || status.status;
    } catch {
      delay = Math.min(delay * 2, 30000);
    }
    setTimeout(tick, delay);
  };

  setTimeout(tick, INTERVAL);
}

export function initExport(root = document) {
  for (const badge of $$('[data-export-id]', root)) {
    if (['queued', 'running'].includes(badge.dataset.status)) watch(badge);
  }
}
