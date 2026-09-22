<?php
/**
 * Verifikasi konten — POST `/admin/konten/verifikasi` → ContentController::verify
 *
 * Pemeriksaan kelengkapan sebelum rilis: 3 wilayah × 5 tantangan, bank soal
 * ≥ items_per_round, pengecoh rumpang, kunci jawaban, satu opsi benar, verdict
 * sah, bacaan satu wilayah, dan butir yang belum diverifikasi.
 * Galat harus diperbaiki sebelum rilis; peringatan boleh diterima dengan sadar.
 *
 * @var list<array{level: string, scope: string, message: string}> $findings
 */
$errorsFound = array_values(array_filter($findings, static fn (array $row): bool => $row['level'] === 'error'));
$warnings    = array_values(array_filter($findings, static fn (array $row): bool => $row['level'] !== 'error'));
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php ob_start() ?>
<form method="post" action="<?= base_url('admin/konten/verifikasi') ?>" class="inline-form">
  <?= csrf_field() ?>
  <button class="btn btn-ghost btn-sm" type="submit"><?= icon('replay') ?> Periksa ulang</button>
</form>
<?php $actions = ob_get_clean() ?>
<?= component('partials/admin-head', [
    'title'   => 'Verifikasi konten',
    'eyebrow' => 'Pengelolaan · konten · diperiksa ' . fmt_date(date('Y-m-d H:i:s'), true),
    'lead'    => 'Galat membuat permainan tidak dapat dimainkan dengan benar dan harus diperbaiki sebelum rilis. Peringatan boleh diterima bila memang disengaja.',
    'actions' => $actions,
]) ?>
<?= $this->include('partials/flash') ?>

<div class="findings-summary">
  <span class="badge <?= $errorsFound === [] ? 'is-ok' : 'is-error' ?>"><?= icon($errorsFound === [] ? 'check' : 'cross') ?> <?= count($errorsFound) ?> galat</span>
  <span class="badge <?= $warnings === [] ? 'is-muted' : 'is-warn' ?>"><?= icon('warn') ?> <?= count($warnings) ?> peringatan</span>
</div>

<?php if ($findings === []): ?>
  <div class="empty-state">
    <?= icon('check') ?>
    <p>Semua pemeriksaan lolos. Konten siap dirilis.</p>
    <a class="btn btn-primary btn-sm" href="<?= base_url('admin/studi/rilis') ?>"><?= icon('flask') ?> Ke rilis konten</a>
  </div>
<?php else: ?>
  <?php foreach (['error' => [$errorsFound, 'Galat', 'cross'], 'warning' => [$warnings, 'Peringatan', 'warn']] as $level => [$rows, $title, $iconName]): ?>
    <?php if ($rows === []) {
        continue;
    } ?>
    <section class="panel">
      <h2 class="panel-title"><?= icon($iconName) ?> <?= esc($title) ?> (<?= count($rows) ?>)</h2>
      <div class="table-wrap">
        <table class="data-table">
          <caption class="visually-hidden"><?= esc($title) ?> verifikasi konten</caption>
          <thead><tr><th scope="col">Bagian</th><th scope="col">Temuan</th></tr></thead>
          <tbody>
            <?php foreach ($rows as $row): ?>
              <tr class="is-<?= esc($level, 'attr') ?>">
                <td><code><?= esc($row['scope']) ?></code></td>
                <td><?= esc($row['message']) ?></td>
              </tr>
            <?php endforeach ?>
          </tbody>
        </table>
      </div>
    </section>
  <?php endforeach ?>
<?php endif ?>

<p><a class="btn btn-quiet btn-sm" href="<?= base_url('admin/konten') ?>"><?= icon('left') ?> Kembali ke konten</a></p>
<?= $this->endSection() ?>
