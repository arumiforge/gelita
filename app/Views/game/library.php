<?php
/**
 * 14. Pustaka {wilayah} — `/pustaka/{code}` → LibraryController::show
 *
 * Hanya dirender bila semua tantangan wilayah sudah selesai pada sesi ini;
 * sebelum itu controller merender game/library-locked. Di dalam wilayah
 * namanya "Pustaka {wilayah}" (Game.libraryRegion); rak semua wilayah
 * adalah Pustaka Kedu di `/pustaka`.
 *
 * Tata letak buku dua halaman: kiri galeri media (gambar, video berkas, dan
 * video YouTube/Vimeo/Drive — sebanyak apa pun per halaman, dari tabel
 * library_media), kanan judul + teks berformat ringan (rich_text()).
 * Media yang berkasnya tidak ada disembunyikan beserta bingkainya, bukan
 * menampilkan kotak rusak. Perpindahan halaman memakai jangkar #page-n + CSS
 * :target (tanpa JavaScript); library.js menambah event `library_page_viewed`,
 * perbesar gambar, pemutar video tertanam, gambar yang memudar masuk, dan
 * panel kredit yang saling menutup.
 *
 * Setiap media berada di bingkai berasio tetap (`.book-frame`) yang
 * menampilkan skeleton perkamen selama gambar dimuat. Kredit/sumber ada di
 * `<details class="media-credit">` (ikon ⓘ di pojok kanan atas bingkai,
 * SAUDARA tombol perbesar — bukan di dalamnya), jadi `figcaption` hanya
 * berisi keterangan. `<details>` berjalan tanpa JavaScript.
 *
 * Pemutar pihak ketiga TIDAK dimuat saat halaman dibuka: yang tampil hanya
 * facade — poster (thumbnail yang sudah diunduh server, VideoThumbnail),
 * tombol Putar, dan lencana penyedia; tautan ke videonya bila JavaScript
 * mati. Iframe baru dibuat setelah siswa menekannya, jadi membuka Pustaka
 * tidak menghubungi domain luar.
 *
 * Membuka pustaka tidak memengaruhi bintang atau status node.
 *
 * Redesign (owner, 19 November 2026; decision log *Home page, menus and maps
 * polish*): a reading room instead of a two-page book. Left, the table of
 * contents (`.library-toc`, every page title, the open one marked; library.js
 * keeps the mark in step); right, one page card: the media column (the first
 * picture large with its caption, the others as small tiles that open in the
 * picture viewer) and the text column (page n of N, title, body, and a foot
 * row with Kembali, the count, and Lanjut, on the last page Selesai membaca
 * back to the region map). No page dots, no book spine, no bottom nav bar:
 * Back in the HUD returns to the region map (its name as the label), Pustaka
 * Kedu is in the menu, and the shard plate stays off.
 *
 * @var App\Entities\Level             $level
 * @var list<App\Entities\LibraryPage> $pages
 * @var string                         $locale
 */
$region = $level->text('name', $locale);
$title  = lang('Game.libraryRegion', [$region]);
$total  = count($pages);
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc($title) ?><?= $this->endSection() ?>
<?= $this->section('hudTitle') ?><?= esc($title) ?><?= $this->endSection() ?>
<?= $this->section('hudShards') ?>off<?= $this->endSection() ?>
<?= $this->section('hudBack') ?><?= base_url('wilayah/' . $level->code) ?><?= $this->endSection() ?>
<?= $this->section('hudBackLabel') ?><?= esc($region) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= media_first($level->background_media_id) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="library" data-screen="library" data-level="<?= esc($level->code, 'attr') ?>" data-level-id="<?= esc($level->id, 'attr') ?>"
         data-label-close="<?= esc(lang('Game.close'), 'attr') ?>" data-label-loading="<?= esc(lang('Game.libraryLoading'), 'attr') ?>">
  <header class="library-head visually-hidden">
    <div>
      <span class="eyebrow"><?= icon('book') ?> <?= esc($title) ?></span>
      <h1><?= esc($region) ?></h1>
    </div>
    <a class="icon-btn library-close" href="<?= base_url('wilayah/' . $level->code) ?>"
       aria-label="<?= esc(lang('Game.close'), 'attr') ?>" title="<?= esc(lang('Game.close'), 'attr') ?>"><?= icon('cross') ?></a>
  </header>

  <?php if ($pages === []): ?>
    <div class="empty-state library-empty stage-box"><?= icon('book') ?><p><?= esc(lang('Game.libraryEmpty')) ?></p></div>
  <?php else: ?>
    <nav class="library-toc stage-box" aria-labelledby="library-toc-title" data-library-toc>
      <h2 class="library-toc-title" id="library-toc-title"><?= library_icon('library-toc-icon') ?> <span><?= esc(lang('Game.libraryContents')) ?></span></h2>
      <ol class="library-toc-list stage-scroll">
        <?php foreach ($pages as $index => $page): ?>
          <li>
            <a class="library-toc-link" href="#page-<?= $index + 1 ?>"<?= $index === 0 ? ' aria-current="page"' : '' ?>>
              <span class="library-toc-no"><?= $index + 1 ?></span>
              <span class="library-toc-text"><?= esc($page->text('title', $locale)) ?></span>
            </a>
          </li>
        <?php endforeach ?>
      </ol>
    </nav>

    <ol class="slides book stage-box">
      <?php foreach ($pages as $index => $page): ?>
        <?php
        $n       = $index + 1;
        $gallery = $page->gallery($locale);
        ?>
        <li class="slide book-spread<?= $gallery === [] ? ' is-text-only' : '' ?>" id="page-<?= $n ?>" data-page="<?= esc($page->id, 'attr') ?>">
          <?php if ($gallery !== []): ?>
            <div class="book-page book-media stage-scroll">
              <div class="book-gallery<?= count($gallery) === 1 ? ' is-single' : '' ?>" role="group" aria-label="<?= esc(lang('Game.libraryGallery'), 'attr') ?>">
                <?php foreach ($gallery as $media): ?>
                  <?php
                  $caption = trim((string) $media['caption']);
                  $credit  = trim((string) $media['credit']);
                  // Tautan sumber untuk media pihak lain (atribusi CC BY-SA dsb.)
                  $source    = ! in_array($media['provider'], ['upload', 'link'], true) ? $media['href'] : null;
                  $hasCredit = $credit !== '' || $source !== null;
                  $isEmbed   = $media['src'] === null && $media['embed'] !== null;
                  ?>
                  <figure class="book-figure is-<?= esc($media['kind'], 'attr') ?> via-<?= esc($media['provider'], 'attr') ?>">
                    <?php if ($media['kind'] === 'image' && $media['src'] !== null): ?>
                      <div class="book-frame">
                        <button type="button" class="book-zoom" data-zoom="<?= esc($media['src'], 'attr') ?>"
                                aria-label="<?= esc(lang('Game.libraryZoom') . ($caption !== '' ? ': ' . $caption : ''), 'attr') ?>">
                          <img src="<?= esc($media['src'], 'attr') ?>" alt="<?= esc($caption, 'attr') ?>" loading="lazy" decoding="async" referrerpolicy="no-referrer">
                        </button>
                        <?= $hasCredit ? component('partials/media-credit', ['credit' => $credit, 'source' => $source]) : '' ?>
                      </div>
                    <?php elseif ($media['kind'] === 'video' && $media['src'] !== null): ?>
                      <div class="book-frame is-video">
                        <video controls preload="none" playsinline <?= $media['poster'] !== null ? 'poster="' . esc($media['poster'], 'attr') . '"' : '' ?>>
                          <source src="<?= esc($media['src'], 'attr') ?>">
                        </video>
                        <?= $hasCredit ? component('partials/media-credit', ['credit' => $credit, 'source' => $source]) : '' ?>
                      </div>
                    <?php elseif ($isEmbed): ?>
                      <div class="book-frame is-embed">
                        <div class="book-embed<?= $media['poster'] !== null ? ' has-poster' : '' ?>" data-embed="<?= esc($media['embed'], 'attr') ?>" data-provider="<?= esc($media['provider'], 'attr') ?>"
                             data-title="<?= esc($caption !== '' ? $caption : lang('Game.libraryPlayVideo'), 'attr') ?>">
                          <a class="embed-play" href="<?= esc($media['href'], 'attr') ?>" target="_blank" rel="noopener noreferrer">
                            <?php if ($media['poster'] !== null): ?>
                              <?php // Poster dari server GELITA sendiri (thumbnail yang diunduh server), bukan dari penyedia ?>
                              <img class="embed-poster" src="<?= esc($media['poster'], 'attr') ?>" alt="" loading="lazy" decoding="async">
                            <?php endif ?>
                            <span class="embed-provider"><?= icon('video') ?> <?= esc($media['label']) ?></span>
                            <span class="embed-play-icon"><?= icon('play') ?></span>
                            <span class="embed-text"><b><?= esc(lang('Game.libraryPlayVideo')) ?></b><small><?= esc(lang('Game.libraryEmbedNote', [$media['label']])) ?></small></span>
                          </a>
                        </div>
                        <?= $hasCredit ? component('partials/media-credit', ['credit' => $credit, 'source' => $source]) : '' ?>
                      </div>
                    <?php else: ?>
                      <a class="book-link" href="<?= esc($media['href'], 'attr') ?>" target="_blank" rel="noopener noreferrer">
                        <?= icon($media['kind'] === 'video' ? 'video' : 'link') ?> <?= esc(lang('Game.libraryOpenLink', [$media['label']])) ?>
                      </a>
                      <?php if ($credit !== ''): ?>
                        <p class="book-credit"><?= esc($credit) ?></p>
                      <?php endif ?>
                    <?php endif ?>
                    <?php if ($caption !== ''): ?>
                      <figcaption><?= esc($caption) ?></figcaption>
                    <?php endif ?>
                  </figure>
                <?php endforeach ?>
              </div>
            </div>
          <?php endif ?>
          <div class="book-page book-text">
            <span class="eyebrow"><?= esc(lang('Game.pageOf', [$n, $total])) ?></span>
            <h2><?= esc($page->text('title', $locale)) ?></h2>
            <div class="book-body stage-scroll"><?= rich_text($page->text('body', $locale)) ?></div>
            <nav class="book-foot slide-nav" aria-label="<?= esc(lang('Game.slideOf', [$n, $total]), 'attr') ?>">
              <?php if ($n > 1): ?>
                <a class="btn btn-quiet book-prev" href="#page-<?= $n - 1 ?>"><?= icon('left') ?> <span><?= esc(lang('Game.back')) ?></span></a>
              <?php else: ?>
                <span class="slide-nav-spacer"></span>
              <?php endif ?>
              <span class="book-count num" aria-hidden="true"><?= $n ?> / <?= $total ?></span>
              <?php if ($n < $total): ?>
                <a class="btn btn-primary book-next" href="#page-<?= $n + 1 ?>"><?= esc(lang('Game.continue')) ?> <?= icon('right') ?></a>
              <?php else: ?>
                <a class="btn btn-primary book-next" href="<?= esc(base_url('wilayah/' . $level->code), 'attr') ?>" data-slide-final><?= esc(lang('Game.libraryFinish')) ?> <?= icon('check') ?></a>
              <?php endif ?>
            </nav>
          </div>
        </li>
      <?php endforeach ?>
    </ol>
  <?php endif ?>
</section>
<?= $this->endSection() ?>
