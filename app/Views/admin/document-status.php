<?php
/**
 * Status dokumen — `/admin/status-dokumen` → DocumentStatusController::index
 * (head researcher only; owner, 17 November 2026).
 *
 * @var string|null $content cleaned <main> of docs/revisi-10-analitik/status-dokumen.html
 *                           (DocumentStatusPage; the repository's own file, links removed)
 */
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', ['title' => lang('Admin.menuDocStatus'), 'lead' => lang('Admin.docStatusLead')]) ?>

<?php if ($content === null): ?>
  <p class="empty-state"><?= esc(lang('Admin.docStatusMissing')) ?></p>
<?php else: ?>
  <div class="doc-status">
    <?= $content /* the repository's own file, cleaned by DocumentStatusPage */ ?>
  </div>
<?php endif ?>
<?= $this->endSection() ?>
