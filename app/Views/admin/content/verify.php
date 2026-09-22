<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= esc($pageTitle) ?> · Panel GELITA<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h1><?= esc($pageTitle) ?></h1>
<?= $this->include('partials/flash') ?>

<?php
$errorsFound = array_filter($findings, static fn (array $row): bool => $row['level'] === 'error');
$warnings    = array_filter($findings, static fn (array $row): bool => $row['level'] === 'warning');
?>

<?php if ($findings === []): ?>
  <p class="alert alert-ok" role="status">Semua pemeriksaan lolos.</p>
<?php else: ?>
  <p>Ditemukan <?= count($errorsFound) ?> galat dan <?= count($warnings) ?> peringatan.</p>
  <table class="data-table">
    <thead><tr><th scope="col">Tingkat</th><th scope="col">Bagian</th><th scope="col">Temuan</th></tr></thead>
    <tbody>
      <?php foreach ($findings as $row): ?>
        <tr class="is-<?= esc($row['level']) ?>">
          <td><?= esc($row['level']) ?></td>
          <td><?= esc($row['scope']) ?></td>
          <td><?= esc($row['message']) ?></td>
        </tr>
      <?php endforeach ?>
    </tbody>
  </table>
<?php endif ?>

<a class="btn btn-quiet" href="<?= base_url('admin/konten') ?>">Kembali</a>
<?= $this->endSection() ?>
