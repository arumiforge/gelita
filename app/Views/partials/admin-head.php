<?php
/**
 * Kepala halaman panel: eyebrow, judul, penjelasan singkat, tombol aksi.
 *
 * @var string      $title
 * @var string|null $eyebrow
 * @var string|null $lead
 * @var string|null $actions HTML tombol yang SUDAH di-escape pemanggil
 */
?>
<header class="page-head">
  <div class="page-head-text">
    <?php if (! empty($eyebrow)): ?>
      <span class="eyebrow"><?= esc($eyebrow) ?></span>
    <?php endif ?>
    <h1 class="page-title"><?= esc($title) ?></h1>
    <?php if (! empty($lead)): ?>
      <p class="page-lead"><?= esc($lead) ?></p>
    <?php endif ?>
  </div>
  <?php if (! empty($actions)): ?>
    <div class="page-actions"><?= $actions ?></div>
  <?php endif ?>
</header>
