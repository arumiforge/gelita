<?php
/**
 * Konten — `/admin/konten` → ContentController::index
 *
 * Tiga kartu wilayah; tiap kartu memuat node dengan jenis, jumlah butir di
 * bank (merah bila kurang dari items_per_round), dan status aktif.
 *
 * @var list<array{level: App\Entities\Level, nodes: list<App\Entities\ChallengeNode>, bank: array<int, int>}> $levels
 * @var string $version
 */
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php ob_start() ?>
<form method="post" action="<?= base_url('admin/konten/verifikasi') ?>" class="inline-form">
  <?= csrf_field() ?>
  <button class="btn btn-primary btn-sm" type="submit"><?= icon('check') ?> Verifikasi konten</button>
</form>
<a class="btn btn-ghost btn-sm" href="<?= base_url('admin/konten/impor-bank') ?>"><?= icon('upload') ?> Impor bank soal</a>
<a class="btn btn-ghost btn-sm" href="<?= base_url('admin/konten/dialog/0') ?>"><?= icon('message') ?> Cerita &amp; narasi</a>
<a class="btn btn-ghost btn-sm" href="<?= base_url('admin/konten/narasi') ?>"><?= icon('sound') ?> Rekaman narasi</a>
<a class="btn btn-ghost btn-sm" href="<?= base_url('admin/media') ?>"><?= icon('image') ?> Media</a>
<?php $actions = ob_get_clean() ?>
<?= component('partials/admin-head', [
    'title'   => 'Konten permainan',
    'eyebrow' => 'Pengelolaan · versi konten ' . $version,
    'lead'    => 'Perubahan konten langsung dipakai sesi baru. Butir yang sudah pernah dijawab tidak dihapus, hanya dinonaktifkan.',
    'actions' => $actions,
]) ?>
<?= $this->include('partials/flash') ?>

<?php if ($levels === []): ?>
  <div class="empty-state"><?= icon('book') ?><p>Belum ada wilayah aktif. Jalankan seeder konten atau impor workbook bank soal.</p></div>
<?php else: ?>
  <div class="level-cards">
    <?php foreach ($levels as $entry): ?>
      <?php $level = $entry['level']; ?>
      <section class="panel level-card">
        <header class="level-card-head">
          <div>
            <span class="eyebrow">Wilayah <?= esc($level->sequence) ?> · <?= esc($level->difficulty) ?></span>
            <h2><?= esc($level->text('name', 'id')) ?></h2>
          </div>
          <span class="badge <?= $level->is_active ? 'is-active' : 'is-inactive' ?>"><?= $level->is_active ? 'aktif' : 'nonaktif' ?></span>
        </header>

        <ul class="node-rows">
          <?php foreach ($entry['nodes'] as $node): ?>
            <?php $bank = $entry['bank'][$node->id] ?? 0; $need = $node->itemsPerRound(); ?>
            <li>
              <a class="node-row" href="<?= base_url('admin/konten/node/' . $node->id) ?>">
                <span class="node-row-no"><?= esc($node->sequence) ?></span>
                <span>
                  <?= esc($node->text('title', 'id')) ?>
                  <span class="node-row-meta">
                    <span><?= esc($node->engine_type) ?></span>
                    <span class="bank-count<?= $bank < $need ? ' is-short' : '' ?>"><?= $bank ?> butir / perlu <?= $need ?><?= $bank < $need ? ' ✗' : '' ?></span>
                  </span>
                </span>
                <?= icon('edit') ?>
              </a>
            </li>
          <?php endforeach ?>
          <?php if ($entry['nodes'] === []): ?>
            <li class="muted">Belum ada tantangan di wilayah ini.</li>
          <?php endif ?>
        </ul>

        <ul class="sub-nav">
          <li><a href="<?= base_url('admin/konten/level/' . $level->id) ?>"><?= icon('map') ?> Wilayah</a></li>
          <li><a href="<?= base_url('admin/konten/bacaan/' . $level->id) ?>"><?= icon('text') ?> Bacaan</a></li>
          <li><a href="<?= base_url('admin/konten/pustaka/' . $level->id) ?>"><?= icon('book') ?> Pustaka</a></li>
          <li><a href="<?= base_url('admin/konten/dialog/' . $level->id) ?>"><?= icon('message') ?> Dialog</a></li>
        </ul>
      </section>
    <?php endforeach ?>
  </div>
<?php endif ?>
<?= $this->endSection() ?>
