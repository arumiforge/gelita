<?php
/**
 * 14a. Pustaka Kedu (rak) — `/pustaka` → LibraryController::index
 *
 * Satu kartu per wilayah: sampul (gambar pertama galeri halaman pertama,
 * latar wilayah, atau gradien), "Pustaka {wilayah}", jumlah halaman, dan
 * status Pustaka (GameProgress::libraryStatus()):
 *
 * - `open`        wilayah tuntas → tombol Baca;
 * - `locked`      wilayah terbuka, belum tuntas → gembok, progres x/4,
 *                 penjelasan, dan "Lanjutkan tantangan" ke `entry` wilayah
 *                 (aturan dialog pembuka tetap berlaku);
 * - `unavailable` wilayah belum terbuka → keterangan saja.
 *
 * Sampul memakai sumber yang sama dengan buku; sampul yang gagal dimuat
 * disembunyikan library.js sehingga gradiennya yang tampil.
 *
 * Redesign (owner, 21 October 2026): the Peta Kedu HUD (shard plate left,
 * title centred, no logo), dark navy-and-gold cards with the book emblem on
 * the cover, the bronze track for x/4, buttons on the gold plate, and one way
 * back: Peta Kedu on the navy plate at the bottom left (no close button).
 *
 * @var list<array<string, mixed>> $regions baris levelOverview + library, pages, cover
 * @var string                     $locale
 */
$badges = ['open' => 'book', 'locked' => 'lock', 'unavailable' => 'lock'];
// The owner's pictures, passed to the page as CSS variables (absent files keep the plain look)
$skins = [];
foreach (['plate-gold' => 'kenal-plate-gold', 'plate-navy' => 'kenal-plate-navy', 'loading-track' => 'loading-track'] as $var => $file) {
    if (is_file(FCPATH . 'assets/ui/' . $file . '.webp')) {
        $skins[] = '--' . $var . ': url(&quot;' . esc(asset_url_versioned('ui/' . $file . '.webp'), 'attr') . '&quot;)';
    }
}
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc(lang('Game.library')) ?><?= $this->endSection() ?>
<?= $this->section('hudTitle') ?><?= esc(lang('Game.library')) ?><?= $this->endSection() ?>
<?php // The Peta Kedu HUD: no logo, shard plate on the left, the title in the middle ?>
<?= $this->section('hudStyle') ?>menu<?= $this->endSection() ?>
<?= $this->section('hudLayout') ?>title-centre<?= $this->endSection() ?>
<?= $this->section('background') ?><?= media_key_src('bg.map') ?? '' ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="library library-index" data-screen="library-index"<?= $skins !== [] ? ' style="' . implode('; ', $skins) . '"' : '' ?>>
  <header class="library-head shelf-head stage-box">
    <h1 class="visually-hidden"><?= esc(lang('Game.library')) ?></h1>
    <p class="library-lead"><?= esc(lang('Game.libraryIndexLead')) ?></p>
  </header>

  <ol class="library-shelf">
    <?php foreach ($regions as $region): ?>
      <?php
      $state = (string) $region['library'];
      $title = lang('Game.libraryRegion', [$region['name']]);
      ?>
      <li class="shelf-card is-<?= esc($state, 'attr') ?> stage-box">
        <div class="shelf-cover" aria-hidden="true">
          <?php if (! empty($region['cover'])): ?>
            <img src="<?= esc($region['cover'], 'attr') ?>" alt="" loading="lazy" decoding="async" referrerpolicy="no-referrer">
          <?php endif ?>
          <span class="shelf-badge is-<?= esc($state, 'attr') ?>"><?= icon($badges[$state] ?? 'lock') ?> <?= esc(lang('Game.libraryStatus_' . $state)) ?></span>
          <span class="shelf-emblem"><?= library_icon() ?><?php if ($state !== 'open'): ?><span class="shelf-emblem-lock"><?= icon('lock') ?></span><?php endif ?></span>
        </div>

        <div class="shelf-body stage-scroll">
          <span class="eyebrow"><?= esc(lang('Game.levelOrder', [$region['sequence']])) ?><?php if ($region['pages'] > 0): ?> · <?= esc(lang('Game.libraryPageCount', [$region['pages']])) ?><?php endif ?></span>
          <h2><?= esc($title) ?></h2>

          <?php if ($state === 'open'): ?>
            <?php if ($region['pages'] > 0): ?>
              <a class="btn shelf-btn" href="<?= base_url('pustaka/' . $region['code']) ?>"><span><?= esc(lang('Game.libraryRead')) ?></span> <?= icon('right') ?></a>
            <?php else: ?>
              <p class="shelf-note"><?= esc(lang('Game.libraryEmpty')) ?></p>
            <?php endif ?>
          <?php elseif ($state === 'locked'): ?>
            <?= component('partials/library-lock', [
                'region' => $region['name'],
                'done'   => (int) $region['completed_nodes'],
                'total'  => (int) $region['total_nodes'],
            ]) ?>
            <a class="btn shelf-btn" href="<?= esc(base_url($region['entry']), 'attr') ?>"><span><?= esc(lang('Game.libraryContinue')) ?></span> <?= icon('right') ?></a>
          <?php else: ?>
            <p class="shelf-note"><?= icon('lock') ?> <?= esc(lang('Game.libraryUnavailable', [$region['name']])) ?></p>
          <?php endif ?>
        </div>
      </li>
    <?php endforeach ?>
  </ol>

  <?php // One way back (owner, 21 October 2026): Peta Kedu on the navy plate, bottom left ?>
  <nav class="shelf-nav stage-box" aria-label="<?= esc(lang('Game.continue'), 'attr') ?>">
    <a class="shelf-back" href="<?= base_url('peta') ?>"><?= icon('left') ?> <span><?= esc(lang('Game.mapKedu')) ?></span></a>
  </nav>
</section>
<?= $this->endSection() ?>
