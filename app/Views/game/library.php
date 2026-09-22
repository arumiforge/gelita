<?php
/**
 * 14. Pustaka Kedu — `/pustaka/{code}` → LibraryController::show
 *
 * Tata letak buku dua halaman: kiri media (2 gambar + 1 video berposter),
 * kanan judul + teks. Media yang berkasnya tidak ada disembunyikan beserta
 * bingkainya, bukan menampilkan kotak rusak. Perpindahan halaman memakai
 * jangkar #page-n + CSS :target (tanpa JavaScript); library.js (tahap 6)
 * menambah event `library_page_viewed`.
 *
 * Membuka pustaka tidak memengaruhi skor, bintang, atau status node.
 *
 * @var App\Entities\Level             $level
 * @var list<App\Entities\LibraryPage> $pages
 * @var string                         $locale
 */
$region = $level->text('name', $locale);
$total  = count($pages);
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc(lang('Game.library')) ?> · <?= esc($region) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= media_first($level->background_media_id) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="screen library" data-screen="library" data-level="<?= esc($level->code, 'attr') ?>" data-level-id="<?= esc($level->id, 'attr') ?>">
  <header class="library-head">
    <div>
      <span class="eyebrow"><?= icon('book') ?> <?= esc(lang('Game.library')) ?></span>
      <h1><?= esc($region) ?></h1>
    </div>
    <a class="icon-btn library-close" href="<?= base_url('wilayah/' . $level->code) ?>"
       aria-label="<?= esc(lang('Game.close'), 'attr') ?>" title="<?= esc(lang('Game.close'), 'attr') ?>"><?= icon('cross') ?></a>
  </header>

  <?php if ($pages === []): ?>
    <div class="empty-state"><?= icon('book') ?><p><?= esc(lang('Game.libraryEmpty')) ?></p></div>
  <?php else: ?>
    <ol class="slides book">
      <?php foreach ($pages as $index => $page): ?>
        <?php
        $n      = $index + 1;
        $images = array_values(array_filter([$page->image_a_media_id, $page->image_b_media_id], 'media_exists'));
        $video  = media_exists($page->video_media_id) ? media_src($page->video_media_id) : null;
        $poster = media_exists($page->poster_media_id) ? media_src($page->poster_media_id) : null;
        $hasMedia = $images !== [] || $video !== null;
        ?>
        <li class="slide book-spread<?= $hasMedia ? '' : ' is-text-only' ?>" id="page-<?= $n ?>" data-page="<?= esc($page->id, 'attr') ?>">
          <?php if ($hasMedia): ?>
            <div class="book-page book-media">
              <?php foreach ($images as $mediaId): ?>
                <figure class="book-figure"><img src="<?= esc(media_src($mediaId), 'attr') ?>" alt="" loading="lazy"></figure>
              <?php endforeach ?>
              <?php if ($video !== null): ?>
                <figure class="book-figure">
                  <video controls preload="none" <?= $poster !== null ? 'poster="' . esc($poster, 'attr') . '"' : '' ?>>
                    <source src="<?= esc($video, 'attr') ?>">
                  </video>
                </figure>
              <?php endif ?>
            </div>
          <?php endif ?>
          <div class="book-page book-text">
            <span class="eyebrow"><?= esc(lang('Game.pageOf', [$n, $total])) ?></span>
            <h2><?= esc($page->text('title', $locale)) ?></h2>
            <div class="book-body"><?= nl2br(esc($page->text('body', $locale))) ?></div>
            <?= component('partials/slide-nav', [
                'n'        => $n,
                'total'    => $total,
                'prefix'   => 'page-',
                'finalUrl' => base_url('wilayah/' . $level->code),
                'final'    => lang('Game.close'),
            ]) ?>
          </div>
        </li>
      <?php endforeach ?>
    </ol>
  <?php endif ?>
</section>
<?= $this->endSection() ?>

<?= $this->section('nav') ?>
<?= component('nav-bar', ['nav' => [
    ['label' => $region, 'href' => base_url('wilayah/' . $level->code), 'style' => 'quiet', 'arrow' => 'left'],
    ['label' => lang('Game.mapKedu'), 'href' => base_url('peta'), 'style' => 'quiet', 'icon' => 'map'],
]]) ?>
<?= $this->endSection() ?>
