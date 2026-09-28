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
  <button class="btn btn-primary btn-sm" type="submit"><?= icon('check') ?> Periksa kelengkapan</button>
</form>
<a class="btn btn-ghost btn-sm" href="<?= base_url('admin/konten/impor-bank') ?>"><?= icon('upload') ?> Impor soal dari Excel</a>
<a class="btn btn-ghost btn-sm" href="<?= base_url('admin/konten/dialog/0') ?>"><?= icon('message') ?> Cerita pembuka &amp; penutup</a>
<a class="btn btn-ghost btn-sm" href="<?= base_url('admin/konten/narasi') ?>"><?= icon('sound') ?> Rekaman narasi</a>
<a class="btn btn-ghost btn-sm" href="<?= base_url('admin/media') ?>"><?= icon('image') ?> Gambar &amp; video</a>
<?php $actions = ob_get_clean() ?>
<?= component('partials/admin-head', [
    'title'   => 'Konten permainan',
    'eyebrow' => 'Pengelolaan · versi konten ' . $version,
    'lead'    => 'Isi permainan: wilayah, tantangan, soal, bacaan, Pustaka, dan cerita. Perubahan langsung dipakai siswa yang mulai bermain setelahnya. Soal yang sudah pernah dijawab siswa tidak dapat dihapus, hanya dinonaktifkan, supaya data penelitian tetap utuh.',
    'actions' => $actions,
]) ?>
<?= $this->include('partials/flash') ?>

<?php if ($levels === []): ?>
  <div class="empty-state"><?= icon('book') ?><p>Belum ada wilayah yang aktif. Impor soal dari Excel, atau minta petugas teknis mengisi konten awal.</p></div>
<?php else: ?>
  <div class="level-cards">
    <?php foreach ($levels as $entry): ?>
      <?php $level = $entry['level']; ?>
      <section class="panel level-card">
        <header class="level-card-head">
          <div>
            <span class="eyebrow">Wilayah <?= esc($level->sequence) ?> · tingkat <?= esc($level->difficulty) ?></span>
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
                    <span><?= esc(engine_label((string) $node->engine_type, $node->variant_code)) ?></span>
                    <span class="bank-count<?= $bank < $need ? ' is-short' : '' ?>"><?= $bank < $need
                        ? $bank . ' soal — kurang, perlu minimal ' . $need . ' ✗'
                        : $bank . ' soal · ' . $need . ' dipakai tiap bermain' ?></span>
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
          <li><a href="<?= base_url('admin/konten/dialog/' . $level->id) ?>"><?= icon('message') ?> Cerita &amp; dialog</a></li>
        </ul>
      </section>
    <?php endforeach ?>
  </div>
<?php endif ?>
<?= $this->endSection() ?>
