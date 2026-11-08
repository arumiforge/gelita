<?php
/**
 * Permintaan data — `/admin/permintaan-data` (30 §7.1, §7.7). Codes only.
 *
 * @var list<array<string, mixed>> $requests data_deletion_requests rows + deadline
 */
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', ['title' => lang('Admin.menuDataRequests'), 'lead' => lang('Admin.requestsLead')]) ?>
<?= $this->include('partials/flash') ?>

<p class="form-actions">
  <?php if (staff_can('deletion.request')): ?><a class="btn btn-primary btn-sm" href="<?= base_url('admin/permintaan-data/baru') ?>"><?= esc(lang('Admin.requestNew')) ?></a><?php endif ?>
  <?php if (staff_can('research.export')): ?><a class="btn btn-sm" href="<?= base_url('admin/peserta/salinan') ?>"><?= esc(lang('Admin.copyTitle')) ?></a><?php endif ?>
</p>

<?php if ($requests === []): ?>
  <p class="muted"><?= esc(lang('Admin.requestsNone')) ?></p>
<?php else: ?>
  <table class="data-table">
    <thead><tr><th>No.</th><th>Kode peserta</th><th><?= esc(lang('Admin.requestKind')) ?></th><th>Status</th><th><?= esc(lang('Admin.requestReceived')) ?></th><th><?= esc(lang('Admin.requestDeadline')) ?></th></tr></thead>
    <tbody>
      <?php foreach ($requests as $row): ?>
        <?= view('admin/data-requests/_row', ['row' => $row], ['saveData' => false]) ?>
      <?php endforeach ?>
    </tbody>
  </table>
<?php endif ?>
<?= $this->endSection() ?>
