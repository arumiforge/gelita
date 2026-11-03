/**
 * Titik masuk panel admin.
 *
 * Perilaku umum (tabel, salin) dipasang ulang setiap kali filters.js
 * mengganti isi halaman untuk filter baru; perilaku halaman tertentu
 * (editor konten, impor bank soal, unggah media, unggah narasi, status ekspor) dipasang
 * sekali. Semua halaman tetap berfungsi tanpa JavaScript.
 */
import { $$ } from './core/dom.js';
import { toast } from './core/toast.js';
import { showModal } from './core/modal.js';
import { initTables } from './admin/tables.js';
import { initFilters } from './admin/filters.js';
import { initTabs } from './admin/tabs.js';

document.documentElement.classList.add('js');

/** Tombol [data-copy="#selector"]: salin teks target (mis. sandi sementara). */
function initCopy(root) {
  for (const button of $$('[data-copy]', root)) {
    if (button.dataset.ready) continue;
    button.dataset.ready = '1';
    button.addEventListener('click', async () => {
      const target = document.querySelector(button.dataset.copy);
      if (!target) return;
      const text = target.textContent.trim();
      try {
        await navigator.clipboard.writeText(text);
        toast('Tersalin ke papan klip.', 'ok');
      } catch {
        // Tanpa izin papan klip: pilih teksnya agar dapat disalin manual
        const range = document.createRange();
        range.selectNodeContents(target);
        const selection = window.getSelection();
        selection.removeAllRanges();
        selection.addRange(range);
        toast('Teks sudah dipilih — tekan Ctrl+C untuk menyalin.', 'info');
      }
    });
  }
}

/**
 * Modal konfirmasi (§8.2) untuk form[data-confirm]: judul dari
 * data-confirm, teks dari data-confirm-text, tombol dari data-confirm-ok.
 * Tanpa JavaScript form langsung terkirim (server tetap memvalidasi).
 */
function initConfirm(root) {
  for (const form of $$('form[data-confirm]', root)) {
    if (form.dataset.confirmReady) continue;
    form.dataset.confirmReady = '1';
    form.addEventListener('submit', async (event) => {
      if (form.dataset.confirmed === '1') return;
      event.preventDefault();
      const submitter = event.submitter;
      const answer = await showModal({
        type: 'confirm',
        title: form.dataset.confirm,
        text: form.dataset.confirmText || '',
        buttons: [
          { label: 'Batal', style: 'quiet', value: 'cancel' },
          { label: form.dataset.confirmOk || 'Lanjutkan', style: form.dataset.confirmStyle || 'primary', value: 'ok' },
        ],
      });
      if (answer !== 'ok') return;
      form.dataset.confirmed = '1';
      form.requestSubmit(submitter && submitter.form === form ? submitter : undefined);
    });
  }
}

/**
 * Image zoom: a[data-zoom] opens the image in the shared lightbox
 * (core/lightbox.js) instead of a new tab. Delegated, so rows that
 * filters.js swaps in work too; modifier clicks keep the browser's tab.
 */
let openZoom;
document.addEventListener('click', async (event) => {
  const link = event.target.closest?.('a[data-zoom]');
  if (!link || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
  event.preventDefault();
  openZoom ??= (await import('./core/lightbox.js')).createLightbox({ closeLabel: 'Tutup' });
  if (!openZoom) {
    window.open(link.href, '_blank', 'noopener');
    return;
  }
  openZoom(link.dataset.zoom, '', link.dataset.zoomCaption || '');
});

function initPage(root) {
  initTables(root);
  initCopy(root);
  initConfirm(root);
  initTabs(root);
}

initPage(document);
initFilters((stage, root) => {
  if (stage !== 'dispose') initPage(root);
});

// Modul khusus halaman — dimuat hanya bila markup-nya ada
if (document.querySelector('form.item-form, [name="engine_type"]')) {
  import('./admin/content-editor.js').then((m) => m.initContentEditor());
}
if (document.querySelector('form[action$="impor-bank/pratinjau"]')) {
  import('./admin/bank-import.js').then((m) => m.initBankImport());
}
if (document.querySelector('form.upload-box[action$="media/unggah"]')) {
  import('./admin/media-upload.js').then((m) => m.initMediaUpload());
}
if (document.querySelector('form[data-narration-upload]')) {
  import('./admin/narration-upload.js').then((m) => m.initNarrationUpload());
}
// Character pose animation on the slot page (same player as the game)
if (document.querySelector('.media-sprite .character[data-frames]')) {
  import('./core/sprite.js').then((m) => {
    m.initSprites();
    const figure = document.querySelector('.media-sprite .character[data-frames]');
    const replay = document.querySelector('[data-sprite-replay]');
    if (!replay || figure.dataset.loop === '1') return;
    replay.hidden = false;
    replay.addEventListener('click', () => m.setFrames(figure, m.parseFrames(figure.dataset.frames), { fps: figure.dataset.fps, loop: false }));
  });
}
if (document.querySelector('[data-export-id]')) {
  import('./admin/export.js').then((m) => m.initExport());
}
// Monitoring panel (research revision 10, part 2: 28 §12)
if (document.querySelector('[data-monitoring]')) {
  import('./admin/monitoring.js').then((m) => m.initMonitoring());
}

document.body.dataset.ready = 'id';
