<?php
/**
 * Kerangka dialog tengah layar — satu markup untuk semua keperluan: benar,
 * salah, konfirmasi keluar, petunjuk, sesi berakhir.
 *
 * Layout menaruh markup ini di dalam <template id="tpl-modal">; core/modal.js
 * (tahap 6) menyalinnya ke #modal-layer, mengganti kelas `modal-{type}`, dan
 * mengisi judul/teks/tombol dengan textContent. Ikon tersedia sebagai
 * <template> per jenis agar JavaScript tidak merakit SVG dari string.
 *
 * The round icon sits before the card, not inside it: the card scrolls when
 * its text is long, and a scroll box clips anything that sticks out of it.
 * The game stage shows the icon half above the card; the panel places it in
 * the card's top-left corner (admin.css). `data-type` on the dialog carries
 * the kind for the icon colour.
 */
?>
<div class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-title" aria-describedby="modal-text" data-type="info">
  <?php // Outside the card: the card scrolls (overflow) and would clip the icon that rises above it ?>
  <div class="modal-icon" aria-hidden="true"><?= icon('info') ?></div>
  <div class="modal-card modal-info">
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
  <template data-modal-icon="install"><?= icon('download') ?></template>
</div>
