<?php
/**
 * 14a. Pustaka Kedu (rak) — `/pustaka` → LibraryController::index
 *
 * Satu kartu per wilayah: sampul (gambar pertama galeri halaman pertama,
 * latar wilayah, atau gradien), "Pustaka {wilayah}", jumlah halaman, dan
 * status Pustaka (GameProgress::libraryStatus()):
 *
 * - `open`        wilayah tuntas → tombol Baca;
 * - `locked`      wilayah terbuka, belum tuntas → gembok, progres x/5,
 *                 penjelasan, dan "Lanjutkan tantangan" ke `entry` wilayah
 *                 (aturan dialog pembuka tetap berlaku);
 * - `unavailable` wilayah belum terbuka → keterangan saja.
 *
 * Sampul memakai sumber yang sama dengan buku; sampul yang gagal dimuat
 * disembunyikan library.js sehingga gradiennya yang tampil.
 *
 * @var list<array<string, mixed>> $regions baris levelOverview + library, pages, cover
 * @var string                     $locale
 */
$badges = ['open' => 'book', 'locked' => 'lock', 'unavailable' => 'lock'];
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc(lang('Game.library')) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= media_key_src('bg.map') ?? '' ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="screen library library-index" data-screen="library-index">
  <header class="library-head">
    <div>
      <span class="eyebrow"><?= icon('book') ?> <?= esc(lang('Game.library')) ?></span>
      <h1><?= esc(lang('Game.library')) ?></h1>
      <p class="library-lead"><?= esc(lang('Game.libraryIndexLead')) ?></p>
    </div>
    <a class="icon-btn library-close" href="<?= base_url('peta') ?>"
       aria-label="<?= esc(lang('Game.close'), 'attr') ?>" title="<?= esc(lang('Game.close'), 'attr') ?>"><?= icon('cross') ?></a>
  </header>

  <ol class="library-shelf">
    <?php foreach ($regions as $region): ?>
      <?php
      $state = (string) $region['library'];
      $title = lang('Game.libraryRegion', [$region['name']]);
      ?>
      <li class="shelf-card is-<?= esc($state, 'attr') ?>">
        <div class="shelf-cover" aria-hidden="true">
          <?php if (! empty($region['cover'])): ?>
            <img src="<?= esc($region['cover'], 'attr') ?>" alt="" loading="lazy" decoding="async" referrerpolicy="no-referrer">
          <?php endif ?>
          <span class="shelf-badge is-<?= esc($state, 'attr') ?>"><?= icon($badges[$state] ?? 'lock') ?> <?= esc(lang('Game.libraryStatus_' . $state)) ?></span>
        </div>

        <div class="shelf-body">
          <span class="eyebrow"><?= esc(lang('Game.levelOrder', [$region['sequence']])) ?><?php if ($region['pages'] > 0): ?> · <?= esc(lang('Game.libraryPageCount', [$region['pages']])) ?><?php endif ?></span>
          <h2><?= esc($title) ?></h2>

          <?php if ($state === 'open'): ?>
            <?php if ($region['pages'] > 0): ?>
              <a class="btn btn-primary" href="<?= base_url('pustaka/' . $region['code']) ?>"><?= icon('book') ?> <?= esc(lang('Game.libraryRead')) ?></a>
            <?php else: ?>
              <p class="muted"><?= esc(lang('Game.libraryEmpty')) ?></p>
            <?php endif ?>
          <?php elseif ($state === 'locked'): ?>
            <?= component('partials/library-lock', [
                'region' => $region['name'],
                'done'   => (int) $region['completed_nodes'],
                'total'  => (int) $region['total_nodes'],
            ]) ?>
            <a class="btn btn-primary" href="<?= esc(base_url($region['entry']), 'attr') ?>"><?= esc(lang('Game.libraryContinue')) ?> <?= icon('right') ?></a>
          <?php else: ?>
            <p class="shelf-note muted"><?= icon('lock') ?> <?= esc(lang('Game.libraryUnavailable', [$region['name']])) ?></p>
          <?php endif ?>
        </div>
      </li>
    <?php endforeach ?>
  </ol>
</section>
<?= $this->endSection() ?>

<?= $this->section('nav') ?>
<?= component('nav-bar', ['nav' => [
    ['label' => lang('Game.mapKedu'), 'href' => base_url('peta'), 'style' => 'quiet', 'arrow' => 'left'],
]]) ?>
<?= $this->endSection() ?>
