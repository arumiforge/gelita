<?php
/**
 * Kerangka dialog tengah layar — satu markup untuk semua keperluan: benar,
 * salah, konfirmasi keluar, petunjuk, sesi berakhir.
 *
 * Layout menaruh markup ini di dalam <template id="tpl-modal">; core/modal.js
 * (tahap 6) menyalinnya ke #modal-layer, mengganti kelas `modal-{type}`, dan
 * mengisi judul/teks/tombol dengan textContent. Ikon tersedia sebagai
 * <template> per jenis agar JavaScript tidak merakit SVG dari string.
 */
?>
<div class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-title" aria-describedby="modal-text">
  <div class="modal-card modal-info">
    <div class="modal-icon" aria-hidden="true"><?= icon('info') ?></div>
    <img class="modal-character" alt="" hidden>
    <h3 class="modal-title" id="modal-title"></h3>
    <p class="modal-text" id="modal-text"></p>
    <div class="modal-actions"></div>
  </div>
  <template data-modal-icon="correct"><?= icon('check') ?></template>
  <template data-modal-icon="wrong"><?= icon('cross') ?></template>
  <template data-modal-icon="hint"><?= icon('hint') ?></template>
  <template data-modal-icon="confirm"><?= icon('question') ?></template>
  <template data-modal-icon="info"><?= icon('info') ?></template>
  <template data-modal-icon="lock"><?= icon('lock') ?></template>
</div>
