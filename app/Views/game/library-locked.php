<?php
/**
 * 14b. Pustaka terkunci — `/pustaka/{code}` → LibraryController::show
 *
 * Wilayah sudah terbuka tetapi tantangannya belum semua selesai. Halaman ini
 * menggantikan buku (bukan redirect), sehingga penjelasannya tetap terbaca
 * tanpa JavaScript — mis. saat tombol "Pustaka {wilayah}" yang bergembok di
 * peta wilayah diketuk sebelum JavaScript termuat. `library_opened` TIDAK
 * dicatat di sini.
 *
 * @var App\Entities\Level                                                          $level
 * @var list<App\Entities\LibraryPage>                                              $pages
 * @var array{status: string, entry: string, completed_nodes: int, total_nodes: int} $access
 * @var string                                                                      $locale
 */
$region = $level->text('name', $locale);
$title  = lang('Game.libraryRegion', [$region]);
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc($title) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= media_first($level->background_media_id) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="screen screen-medium library library-locked" data-screen="library-locked" data-level="<?= esc($level->code, 'attr') ?>">
  <article class="panel-parchment library-locked-card">
    <span class="library-locked-icon" aria-hidden="true"><?= icon('lock') ?></span>
    <span class="eyebrow"><?= icon('book') ?> <?= esc($title) ?></span>
    <h1><?= esc(lang('Game.libraryLockedTitle', [$region])) ?></h1>

    <?= component('partials/library-lock', [
        'region' => $region,
        'done'   => (int) $access['completed_nodes'],
        'total'  => (int) $access['total_nodes'],
    ]) ?>

    <div class="library-locked-actions">
      <a class="btn btn-primary" href="<?= esc(base_url($access['entry']), 'attr') ?>"><?= esc(lang('Game.libraryContinue')) ?> <?= icon('right') ?></a>
      <a class="btn btn-quiet" href="<?= base_url('pustaka') ?>"><?= icon('book') ?> <?= esc(lang('Game.library')) ?></a>
    </div>
  </article>
</section>
<?= $this->endSection() ?>

<?= $this->section('nav') ?>
<?= component('nav-bar', ['nav' => [
    ['label' => $region, 'href' => base_url('wilayah/' . $level->code), 'style' => 'quiet', 'arrow' => 'left'],
    ['label' => lang('Game.library'), 'href' => base_url('pustaka'), 'style' => 'quiet', 'icon' => 'book'],
]]) ?>
<?= $this->endSection() ?>
