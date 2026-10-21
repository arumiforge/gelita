<?php
/**
 * 1. Welcome — `/` → HomeController::index
 *
 * Panggung 16:9 (Dok 3 §4): logo + tagline 240, 104, 800 × 336; tombol Mulai 400, 472,
 * 480 × 112 (the owner's long carved plate, 480 × 88 dp, 20 October 2026); Pasang GELITA
 * in the bottom-right corner, 960, 640, 296 × 64, on the owner's plate with the download
 * medallion (`assets/ui/btn-install.webp`, 21 October 2026), which reopens the install
 * dialog (game/install.js). Masuk staf moved into the HUD menu (owner, 21 October 2026).
 *
 * Logo dan SATU tombol Mulai → `/mulai`, sama untuk siswa yang sudah maupun
 * belum login (`/mulai` yang memilah). HUD dirender tanpa merek
 * (`hideBrand`): logonya sudah besar di sini. Staff sign in through the HUD
 * menu's Masuk staf row (`/admin/login`, before a student signs in). A
 * signed-in student finds Profil and Keluar in the HUD menu (HomeController::index).
 *
 * Cinematic entrance (game/cine.js, `data-cine="welcome"`, owner 20 October
 * 2026): letterbox bars, the background pushing in from dark, the logo coming
 * into focus behind a flare, then tagline and Mulai; afterwards a light sweep
 * across the logo (`.logo-sheen`), a slow drift of the background, and the
 * WebGL sparks and light rays of the story scenes (`screen_fx_attr()`), the
 * rays coming from the light of the background picture (its sun).
 *
 * Asset package (Stage 3): `data-package-gate="link"` + `data-package-start`
 * let game/package-gate.js point Mulai at `/siapkan` while package mode is on
 * and this device does not hold the package yet (P11).
 *
 * Logo : `ui.logo-hero` (landscape 1600×600) → `ui.logo` → judul teks.
 *        The tagline `Game.tagline` is always shown under the logo, in the
 *        page language (owner, 18 October 2026).
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

<?= $this->section('music') ?>welcome<?= $this->endSection() ?>
<?= $this->section('background') ?><?= media_key_src('bg.welcome') ?? '' ?><?= $this->endSection() ?>
<?= $this->section('bodyClass') ?>is-welcome<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="welcome" data-package-gate="link" data-cine="welcome"<?= screen_fx_attr() ?>>
  <header class="welcome-hero stage-box">
    <?php if ($logo !== null): ?>
      <?php // --logo-src: the light sweep across the logo is masked by the logo itself (cinematic.css) ?>
      <h1 class="welcome-logo<?= $logoHero !== null ? ' is-hero' : '' ?>" style="--logo-src: url(&quot;<?= esc($logo, 'attr') ?>&quot;)">
        <img src="<?= esc($logo, 'attr') ?>" alt="<?= esc(lang('Game.appName'), 'attr') ?>"
             <?= $logoHero !== null ? 'width="1600" height="600"' : 'width="560"' ?> fetchpriority="high">
        <?php if ($logoHero !== null): ?><span class="logo-sheen" aria-hidden="true"></span><?php endif ?>
      </h1>
      <p class="welcome-tagline"><?= esc(lang('Game.tagline')) ?></p>
    <?php else: ?>
      <h1 class="welcome-title"><?= esc(lang('Game.appName')) ?></h1>
      <p class="welcome-tagline"><?= esc(lang('Game.tagline')) ?></p>
    <?php endif ?>
  </header>

  <div class="welcome-actions stage-box">
    <?php if ($btnStart !== null): ?>
      <a class="btn-start is-image" href="<?= base_url('mulai') ?>" data-package-start>
        <img src="<?= esc($btnStart, 'attr') ?>" alt="<?= esc(lang('Game.start'), 'attr') ?>" width="1200" height="220">
      </a>
    <?php else: ?>
      <a class="btn-start btn btn-primary btn-xl" href="<?= base_url('mulai') ?>" data-package-start><?= esc(lang('Game.start')) ?> <?= icon('right') ?></a>
    <?php endif ?>
  </div>

  <?php /* Install offer (game/install.js): on phones and tablets a dialog over the blurred stage
           opens first, once per browser session; this button reopens it. The dialog text rides on
           data attributes, so it is only sent with this page. */ ?>
  <div class="welcome-install stage-box" data-install="modal" hidden
       data-install-title="<?= esc(lang('Game.installTitle'), 'attr') ?>"
       data-install-text="<?= esc(lang('Game.installText'), 'attr') ?>"
       data-install-now="<?= esc(lang('Game.installNow'), 'attr') ?>"
       data-install-later="<?= esc(lang('Game.installLater'), 'attr') ?>"
       data-install-ok="<?= esc(lang('Game.installOk'), 'attr') ?>"
       data-install-menu="<?= esc(lang('Game.installMenu'), 'attr') ?>"
       data-install-ios="<?= esc(lang('Game.installIos'), 'attr') ?>"
       data-install-already="<?= esc(lang('Game.installAlready'), 'attr') ?>">
    <?php if (is_file(FCPATH . 'assets/ui/btn-install.webp')): ?>
      <button type="button" class="btn-install-plate" data-install-button hidden style="--install-plate: url(&quot;<?= esc(asset_url_versioned('ui/btn-install.webp'), 'attr') ?>&quot;)"><span><?= esc(lang('Game.installApp')) ?></span></button>
    <?php else: ?>
      <button type="button" class="btn btn-ghost" data-install-button hidden><?= icon('download') ?> <?= esc(lang('Game.installApp')) ?></button>
    <?php endif ?>
  </div>
</section>
<?= $this->endSection() ?>
