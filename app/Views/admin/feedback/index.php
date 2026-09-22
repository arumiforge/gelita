<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= esc($pageTitle) ?> · Panel GELITA<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h1><?= esc($pageTitle) ?></h1>
<?= $this->include('partials/flash') ?>

<dl class="detail">
  <dt>Jumlah masukan</dt><dd><?= esc($distribution['count']) ?></dd>
  <dt>Rata-rata bintang</dt><dd><?= esc($distribution['mean']) ?></dd>
</dl>

<table class="data-table">
  <thead>
    <tr>
      <th scope="col">Peserta</th><th scope="col">Bintang</th><th scope="col">Paling disukai</th>
      <th scope="col">Paling sulit</th><th scope="col">Hal baru</th><th scope="col">Saran</th>
      <th scope="col">Waktu</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($rows as $row): ?>
      <tr>
        <td><?= esc($row['participant_code'] ?? $row['participant_id']) ?></td>
        <td><?= esc($row['rating']) ?></td>
        <td><?= esc($row['liked_most'] ?? '—') ?></td>
        <td><?= esc($row['hardest_part'] ?? '—') ?></td>
        <td><?= esc($row['new_learning'] ?? '—') ?></td>
        <td><?= esc($row['suggestion'] ?? '—') ?></td>
        <td><?= esc($row['submitted_at']) ?></td>
      </tr>
    <?php endforeach ?>
    <?php if ($rows === []): ?>
      <tr><td colspan="7"><?= esc(lang('Admin.emptyDefault')) ?></td></tr>
    <?php endif ?>
  </tbody>
</table>

<?= $pager?->links() ?>
<?= $this->endSection() ?>
