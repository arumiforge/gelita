<?php
/**
 * 2b. Pilihan pemain lama — `/gerbang` → GateController::index
 *
 * Tujuan tombol Mulai bagi siswa yang sudah login dan sudah pernah menonton
 * cerita pembuka (yang belum pernah langsung diarahkan ke `/intro`).
 * Dua kartu: "Lihat cerita pembuka" → /intro; "Langsung ke peta" →
 * /gerbang/peta, yang men-set flash `curtain=map` lalu membuka /peta.
 *
 * Owner redesign (20 October 2026): the owner's picture (a vine-covered arch
 * at sunset on the left, plain dark teal on the right, `assets/ui/bg-gate.webp`)
 * fills the stage; Jaka waves from the path as one tall still picture
 * (`assets/ui/jaka-gate.webp`, no animation frames, legs and feet darkened in
 * the file so they sink into the path). The cards sit straight on the dark
 * right side: no panel fill, no corner ornaments, only the compass emblem
 * above the title. The two card icons are the owner's emblems (book, map
 * scroll). HUD: the GELITA logo in the middle instead of the shard plate,
 * Back in the top-right corner. Cinematic entrance (game/cine.js,
 * `data-cine="gate"`): the picture pushes in from dark, Jaka walks in, the
 * crest turns into place, the cards slide in one after the other; then the
 * WebGL sparks and mist (`screen_fx_attr()`) and parallax.
 *
 * @var string $name display_name ?: username
 */
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('music') ?>welcome<?= $this->endSection() ?>
<?= $this->section('title') ?><?= esc(lang('Game.startChoiceTitle')) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= asset_url_versioned('ui/bg-gate.webp') ?><?= $this->endSection() ?>
<?= $this->section('bodyClass') ?>is-gate-art<?= $this->endSection() ?>

<?= $this->section('hudBack') ?><?= base_url() ?><?= $this->endSection() ?>
<?= $this->section('hudLayout') ?>logo-centre<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="start start-choice" data-cine="gate"<?= screen_fx_attr() ?>>
  <img class="gate-jaka stage-box" src="<?= esc(asset_url_versioned('ui/jaka-gate.webp'), 'attr') ?>" alt="" width="605" height="1400" decoding="async" draggable="false">

  <div class="gate-panel stage-box stage-scroll">
    <img class="gate-crest" src="<?= esc(asset_url_versioned('ui/ornate-crest.webp'), 'attr') ?>" alt="" width="274" height="320" decoding="async" draggable="false">
    <header class="start-head">
      <h1><?= esc(lang('Game.welcomeBack', [$name])) ?></h1>
      <p><?= esc(lang('Game.startChoiceLead')) ?></p>
    </header>
    <span class="ornate-divider" aria-hidden="true"></span>

    <div class="start-cards">
      <a class="start-card card-link" href="<?= base_url('intro') ?>">
        <img class="start-emblem" src="<?= esc(asset_url_versioned('ui/gate-icon-intro.webp'), 'attr') ?>" alt="" width="220" height="256" decoding="async" draggable="false">
        <div class="start-text">
          <h2><?= esc(lang('Game.watchIntro')) ?></h2>
          <p><?= esc(lang('Game.watchIntroText')) ?></p>
        </div>
        <span class="start-go" aria-hidden="true"><?= icon('right') ?></span>
      </a>
      <a class="start-card is-primary card-link" href="<?= base_url('gerbang/peta') ?>">
        <img class="start-emblem" src="<?= esc(asset_url_versioned('ui/gate-icon-map.webp'), 'attr') ?>" alt="" width="219" height="256" decoding="async" draggable="false">
        <div class="start-text">
          <h2><?= esc(lang('Game.goToMap')) ?></h2>
          <p><?= esc(lang('Game.goToMapText')) ?></p>
        </div>
        <span class="start-go" aria-hidden="true"><?= icon('right') ?></span>
      </a>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
