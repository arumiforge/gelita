<?php
/**
 * 1. Welcome — `/` → HomeController::index
 *
 * Home redesign (owner, 19 November 2026; decision log *Home page, menus and maps polish*):
 * a two-sided 16:9 stage. Left column (x 72–648): the GELITA logo, the tagline, a short
 * invitation (`Game.welcomeLead`), the three region chips (each region's map medallion
 * `map.pin.{code}` and its name) and one Mulai button; right: Jaka (`assets/ui/jaka-home.webp`
 * once the lantern pose is accepted, else the waving gate picture `jaka-gate.webp`). The
 * picture `bg.welcome` stays the background; a soft dark wash on the left keeps the text
 * readable (`.welcome::before`). Pasang GELITA sits under Mulai in the left column.
 *
 * Mulai is lettered by the page, not by a picture (owner, 19 November 2026): the gold plate
 * (U-01, `.btn-primary` on `#stage`) at 80 dp with the word and an arrow, so it reads in both
 * languages; the old lettered slot `ui.btn-start` is no longer shown.
 *
 * Logo and ONE Mulai button → `/mulai`, the same for students signed in or not (`/mulai`
 * sorts them). The HUD is drawn without the brand (`hideBrand`). Staff sign in through the
 * HUD menu's Masuk staf row.
 *
 * Cinematic entrance (game/cine.js, `data-cine="welcome"`): letterbox bars, the background
 * pushing in from dark, the logo coming into focus behind a flare, then the text, the chips
 * and Mulai; Jaka walks in from the right. Afterwards a light sweep across the logo
 * (`.logo-sheen`), a slow drift of the background, and the WebGL sparks and light rays
 * (`screen_fx_attr()`).
 *
 * Asset package (Stage 3): `data-package-gate="link"` + `data-package-start` let
 * game/package-gate.js point Mulai at `/siapkan` while package mode is on and this device
 * does not hold the package yet (P11).
 *
 * Logo : `ui.logo-hero` (landscape 1600×600) → `ui.logo` → text title.
 *
 * @var string                                   $locale
 * @var list<array{code: string, name: string}>|null $regions
 */
$logoHero = media_key_src('ui.logo-hero');
$logo     = $logoHero ?? media_key_src('ui.logo');
$regions  = $regions ?? [];
$jakaFile = is_file(FCPATH . 'assets/ui/jaka-home.webp') ? 'jaka-home.webp' : 'jaka-gate.webp';
[$jakaW, $jakaH] = $jakaFile === 'jaka-home.webp' ? [900, 1400] : [605, 1400];
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('music') ?>welcome<?= $this->endSection() ?>
<?= $this->section('background') ?><?= media_key_src('bg.welcome') ?? '' ?><?= $this->endSection() ?>
<?= $this->section('bodyClass') ?>is-welcome<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="welcome" data-package-gate="link" data-cine="welcome"<?= screen_fx_attr() ?>>
  <img class="welcome-jaka stage-box" src="<?= esc(asset_url_versioned('ui/' . $jakaFile), 'attr') ?>" alt="" width="<?= $jakaW ?>" height="<?= $jakaH ?>" decoding="async" draggable="false">

  <header class="welcome-hero stage-box">
    <?php if ($logo !== null): ?>
      <?php // --logo-src: the light sweep across the logo is masked by the logo itself (cinematic.css) ?>
      <h1 class="welcome-logo<?= $logoHero !== null ? ' is-hero' : '' ?>" style="--logo-src: url(&quot;<?= esc($logo, 'attr') ?>&quot;)">
        <img src="<?= esc($logo, 'attr') ?>" alt="<?= esc(lang('Game.appName'), 'attr') ?>"
             <?= $logoHero !== null ? 'width="1600" height="600"' : 'width="560"' ?> fetchpriority="high">
        <?php if ($logoHero !== null): ?><span class="logo-sheen" aria-hidden="true"></span><?php endif ?>
      </h1>
    <?php else: ?>
      <h1 class="welcome-title"><?= esc(lang('Game.appName')) ?></h1>
    <?php endif ?>
    <p class="welcome-tagline"><?= esc(lang('Game.tagline')) ?></p>
    <span class="welcome-rule" aria-hidden="true"></span>
    <p class="welcome-lead"><?= esc(lang('Game.welcomeLead')) ?></p>
    <?php if ($regions !== []): ?>
      <ul class="welcome-regions" aria-label="<?= esc(lang('Game.welcomeRegions'), 'attr') ?>">
        <?php foreach ($regions as $region): ?>
          <?php $pin = region_art_src('pin', $region['code']) ?>
          <li class="welcome-region">
            <?php if ($pin !== null): ?><img src="<?= esc($pin, 'attr') ?>" alt="" decoding="async" draggable="false"><?php endif ?>
            <span><?= esc($region['name']) ?></span>
          </li>
        <?php endforeach ?>
      </ul>
    <?php endif ?>
  </header>

  <div class="welcome-actions stage-box">
    <a class="btn-start btn btn-primary btn-xl" href="<?= base_url('mulai') ?>" data-package-start><span><?= esc(lang('Game.start')) ?></span> <?= icon('right') ?></a>
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
