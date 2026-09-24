<?php
/**
 * 14. Pustaka Kedu — `/pustaka/{code}` → LibraryController::show
 *
 * Tata letak buku dua halaman: kiri galeri media (gambar, video berkas, dan
 * video YouTube/Vimeo/Drive — sebanyak apa pun per halaman, dari tabel
 * library_media), kanan judul + teks berformat ringan (rich_text()).
 * Media yang berkasnya tidak ada disembunyikan beserta bingkainya, bukan
 * menampilkan kotak rusak. Perpindahan halaman memakai jangkar #page-n + CSS
 * :target (tanpa JavaScript); library.js menambah event `library_page_viewed`,
 * perbesar gambar, dan pemutar video tertanam.
 *
 * Pemutar pihak ketiga TIDAK dimuat saat halaman dibuka: yang tampil hanya
 * tombol Putar (tautan ke videonya bila JavaScript mati). Iframe baru dibuat
 * setelah siswa menekannya, jadi membuka Pustaka tidak menghubungi domain luar.
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
<section class="screen library" data-screen="library" data-level="<?= esc($level->code, 'attr') ?>" data-level-id="<?= esc($level->id, 'attr') ?>"
         data-label-close="<?= esc(lang('Game.close'), 'attr') ?>">
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
        $n       = $index + 1;
        $gallery = $page->gallery($locale);
        ?>
        <li class="slide book-spread<?= $gallery === [] ? ' is-text-only' : '' ?>" id="page-<?= $n ?>" data-page="<?= esc($page->id, 'attr') ?>">
          <?php if ($gallery !== []): ?>
            <div class="book-page book-media">
              <div class="book-gallery<?= count($gallery) === 1 ? ' is-single' : '' ?>" role="group" aria-label="<?= esc(lang('Game.libraryGallery'), 'attr') ?>">
                <?php foreach ($gallery as $media): ?>
                  <?php $caption = trim((string) $media['caption']); ?>
                  <figure class="book-figure is-<?= esc($media['kind'], 'attr') ?> via-<?= esc($media['provider'], 'attr') ?>">
                    <?php if ($media['kind'] === 'image' && $media['src'] !== null): ?>
                      <button type="button" class="book-zoom" data-zoom="<?= esc($media['src'], 'attr') ?>"
                              aria-label="<?= esc(lang('Game.libraryZoom') . ($caption !== '' ? ': ' . $caption : ''), 'attr') ?>">
                        <img src="<?= esc($media['src'], 'attr') ?>" alt="<?= esc($caption, 'attr') ?>" loading="lazy" decoding="async" referrerpolicy="no-referrer">
                      </button>
                    <?php elseif ($media['kind'] === 'video' && $media['src'] !== null): ?>
                      <video controls preload="none" playsinline <?= $media['poster'] !== null ? 'poster="' . esc($media['poster'], 'attr') . '"' : '' ?>>
                        <source src="<?= esc($media['src'], 'attr') ?>">
                      </video>
                    <?php elseif ($media['embed'] !== null): ?>
                      <div class="book-embed" data-embed="<?= esc($media['embed'], 'attr') ?>" data-provider="<?= esc($media['provider'], 'attr') ?>"
                           data-title="<?= esc($caption !== '' ? $caption : lang('Game.libraryPlayVideo'), 'attr') ?>">
                        <a class="embed-play" href="<?= esc($media['href'], 'attr') ?>" target="_blank" rel="noopener noreferrer">
                          <span class="embed-play-icon"><?= icon('play') ?></span>
                          <span><b><?= esc(lang('Game.libraryPlayVideo')) ?></b><small><?= esc(lang('Game.libraryEmbedNote', [$media['label']])) ?></small></span>
                        </a>
                      </div>
                    <?php else: ?>
                      <a class="book-link" href="<?= esc($media['href'], 'attr') ?>" target="_blank" rel="noopener noreferrer">
                        <?= icon($media['kind'] === 'video' ? 'video' : 'link') ?> <?= esc(lang('Game.libraryOpenLink', [$media['label']])) ?>
                      </a>
                    <?php endif ?>
                    <?php if ($caption !== '' || $media['credit'] !== ''): ?>
                      <figcaption>
                        <?= esc($caption) ?>
                        <?php if ($media['credit'] !== '' && $media['provider'] !== 'upload'): ?>
                          <?php // Atribusi lisensi (CC BY-SA dsb.): kredit menaut ke halaman sumber berkas ?>
                          <small class="book-credit"><a href="<?= esc($media['href'], 'attr') ?>" target="_blank" rel="noopener noreferrer"><?= esc($media['credit']) ?></a></small>
                        <?php elseif ($media['credit'] !== ''): ?>
                          <small class="book-credit"><?= esc($media['credit']) ?></small>
                        <?php endif ?>
                      </figcaption>
                    <?php endif ?>
                  </figure>
                <?php endforeach ?>
              </div>
            </div>
          <?php endif ?>
          <div class="book-page book-text">
            <span class="eyebrow"><?= esc(lang('Game.pageOf', [$n, $total])) ?></span>
            <h2><?= esc($page->text('title', $locale)) ?></h2>
            <div class="book-body"><?= rich_text($page->text('body', $locale)) ?></div>
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
