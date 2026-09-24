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
<section class="screen screen-medium start start-choice">
  <header class="screen-head">
    <h1><?= esc(lang('Game.welcomeBack', [$name])) ?></h1>
    <p><?= esc(lang('Game.startChoiceLead')) ?></p>
  </header>

  <div class="start-cards">
    <a class="panel start-card card-link" href="<?= base_url('intro') ?>">
      <span class="start-icon" aria-hidden="true"><?= icon('book') ?></span>
      <h2><?= esc(lang('Game.watchIntro')) ?></h2>
      <p><?= esc(lang('Game.watchIntroText')) ?></p>
      <span class="start-go" aria-hidden="true"><?= icon('right') ?></span>
    </a>
    <a class="panel start-card card-link" href="<?= base_url('gerbang/peta') ?>">
      <span class="start-icon" aria-hidden="true"><?= icon('map') ?></span>
      <h2><?= esc(lang('Game.goToMap')) ?></h2>
      <p><?= esc(lang('Game.goToMapText')) ?></p>
      <span class="start-go" aria-hidden="true"><?= icon('right') ?></span>
    </a>
  </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('nav') ?>
<?= component('nav-bar', ['nav' => [
    ['label' => lang('Game.back'), 'href' => base_url(), 'style' => 'quiet', 'arrow' => 'left'],
]]) ?>
<?= $this->endSection() ?>
