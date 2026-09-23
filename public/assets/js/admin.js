/**
 * Titik masuk panel admin.
 *
 * Perilaku umum (chart, tabel, salin) dipasang ulang setiap kali filters.js
 * mengganti isi halaman untuk filter baru; perilaku halaman tertentu
 * (editor konten, impor bank soal, unggah media, status ekspor) dipasang
 * sekali. Semua halaman tetap berfungsi tanpa JavaScript.
 */
import { $$ } from './core/dom.js';
import { toast } from './core/toast.js';
import { initCharts, disposeCharts } from './admin/charts.js';
import { initTables } from './admin/tables.js';
import { initFilters } from './admin/filters.js';

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

function initPage(root) {
  initCharts(root);
  initTables(root);
  initCopy(root);
}

initPage(document);
initFilters((phase, root) => {
  if (phase === 'dispose') disposeCharts(root);
  else initPage(root);
});

// Modul khusus halaman — dimuat hanya bila markup-nya ada
if (document.querySelector('form.item-form, form.option-editor, [name="engine_type"]')) {
  import('./admin/content-editor.js').then((m) => m.initContentEditor());
}
if (document.querySelector('form[action$="impor-bank/pratinjau"]')) {
  import('./admin/bank-import.js').then((m) => m.initBankImport());
}
if (document.querySelector('form.upload-box[action$="media/unggah"]')) {
  import('./admin/media-upload.js').then((m) => m.initMediaUpload());
}
if (document.querySelector('[data-export-id]')) {
  import('./admin/export.js').then((m) => m.initExport());
}

document.body.dataset.ready = 'id';
