<?php
/**
 * 2b. Pilihan pemain lama — `/gerbang` → GateController::index
 *
 * Tujuan tombol Mulai bagi siswa yang sudah login dan sudah pernah menonton
 * cerita pembuka (yang belum pernah langsung diarahkan ke `/intro`).
 * Dua kartu: "Lihat cerita pembuka" → /intro; "Langsung ke peta" →
 * /gerbang/peta, yang men-set flash `curtain=map` lalu membuka /peta.
 *
 * @var string $name display_name ?: username
 */
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc(lang('Game.startChoiceTitle')) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= media_key_src('bg.welcome') ?? '' ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="start start-choice">
  <?= component('character', ['character' => 'jaka', 'pose' => 'happy', 'class' => 'start-jaka stage-box']) ?>

  <div class="start-panel ornate-panel stage-box">
    <?= $this->include('partials/ornate-frame') ?>
    <div class="ornate-body stage-scroll">
      <header class="start-head">
        <h1><?= esc(lang('Game.welcomeBack', [$name])) ?></h1>
        <p><?= esc(lang('Game.startChoiceLead')) ?></p>
      </header>
      <span class="ornate-divider" aria-hidden="true"></span>

      <div class="start-cards">
        <a class="start-card card-link" href="<?= base_url('intro') ?>">
          <span class="start-icon" aria-hidden="true"><?= icon('book') ?></span>
          <div class="start-text">
            <h2><?= esc(lang('Game.watchIntro')) ?></h2>
            <p><?= esc(lang('Game.watchIntroText')) ?></p>
          </div>
          <span class="start-go" aria-hidden="true"><?= icon('right') ?></span>
        </a>
        <a class="start-card is-primary card-link" href="<?= base_url('gerbang/peta') ?>">
          <span class="start-icon" aria-hidden="true"><?= icon('map') ?></span>
          <div class="start-text">
            <h2><?= esc(lang('Game.goToMap')) ?></h2>
            <p><?= esc(lang('Game.goToMapText')) ?></p>
          </div>
          <span class="start-go" aria-hidden="true"><?= icon('right') ?></span>
        </a>
      </div>
    </div>
  </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('nav') ?>
<?= component('nav-bar', ['nav' => [
    ['label' => lang('Game.back'), 'href' => base_url(), 'style' => 'quiet', 'arrow' => 'left'],
]]) ?>
<?= $this->endSection() ?>
