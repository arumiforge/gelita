<?php
/**
 * 1. Welcome — `/` → HomeController::index
 *
 * Logo dan SATU tombol Mulai → `/mulai`, sama untuk siswa yang sudah maupun
 * belum login (`/mulai` yang memilah). HUD dirender tanpa merek
 * (`hideBrand`): logonya sudah besar di sini. Staf masuk lewat `/admin/login`.
 *
 * Logo : `ui.logo-hero` (landscape 1600×600) → `ui.logo` → judul teks.
 *        Bila gambar, tagline tetap ada sebagai teks tersembunyi.
 * Mulai: gambar `ui.btn-start` (bertulisan, jadi berlaku varian bahasa
 *        `ui.btn-start.en`) → tombol CSS emas-navy (05_VIEW_UI.md §Kondisi aset hilang).
 *
 * @var string $locale
 */
$logoHero = media_key_src('ui.logo-hero');
$logo     = $logoHero ?? media_key_src('ui.logo');
$btnStart = media_key_src_locale('ui.btn-start', $locale);
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('background') ?><?= media_key_src('bg.welcome') ?? '' ?><?= $this->endSection() ?>
<?= $this->section('bodyClass') ?>is-welcome<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="screen welcome">
  <header class="welcome-hero">
    <?php if ($logo !== null): ?>
      <h1 class="welcome-logo<?= $logoHero !== null ? ' is-hero' : '' ?>">
        <img src="<?= esc($logo, 'attr') ?>" alt="<?= esc(lang('Game.appName'), 'attr') ?>"
             <?= $logoHero !== null ? 'width="1600" height="600"' : 'width="560"' ?> fetchpriority="high">
      </h1>
      <p class="visually-hidden"><?= esc(lang('Game.tagline')) ?></p>
    <?php else: ?>
      <h1 class="welcome-title"><?= esc(lang('Game.appName')) ?></h1>
      <p class="welcome-tagline"><?= esc(lang('Game.tagline')) ?></p>
    <?php endif ?>
  </header>

  <div class="welcome-actions">
    <?php if ($btnStart !== null): ?>
      <a class="btn-start is-image" href="<?= base_url('mulai') ?>">
        <img src="<?= esc($btnStart, 'attr') ?>" alt="<?= esc(lang('Game.start'), 'attr') ?>" width="720" height="240">
      </a>
    <?php else: ?>
      <a class="btn-start btn btn-primary btn-xl" href="<?= base_url('mulai') ?>"><?= esc(lang('Game.start')) ?> <?= icon('right') ?></a>
    <?php endif ?>
  </div>

  <?php /* Tawaran memasang di layar utama; game/install.js yang memutuskan tampil atau tidak */ ?>
  <div class="welcome-install" data-install hidden>
    <button type="button" class="btn btn-ghost" data-install-button hidden><?= icon('download') ?> <?= esc(lang('Game.installApp')) ?></button>
    <p class="welcome-install-hint" data-install-hint hidden><?= esc(lang('Game.installHint')) ?></p>
    <p class="welcome-install-hint" data-install-ios hidden><?= icon('info') ?> <span><?= esc(lang('Game.installIos')) ?></span></p>
  </div>
</section>
<?= $this->endSection() ?>
