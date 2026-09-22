<?php
/**
 * 2. Mulai — `/mulai` → HomeController::start
 *
 * Dua kartu: "Saya baru" → /persetujuan; "Saya sudah punya akun" → /masuk.
 */
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc(lang('Game.startTitle')) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= media_key_src('bg.welcome') ?? '' ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="screen screen-medium start">
  <header class="screen-head">
    <h1><?= esc(lang('Game.startTitle')) ?></h1>
  </header>

  <div class="start-cards">
    <a class="panel start-card card-link" href="<?= base_url('persetujuan') ?>">
      <span class="start-icon" aria-hidden="true"><?= icon('sparkle') ?></span>
      <h2><?= esc(lang('Game.startNew')) ?></h2>
      <p><?= esc(lang('Game.startNewText')) ?></p>
      <span class="start-go" aria-hidden="true"><?= icon('right') ?></span>
    </a>
    <a class="panel start-card card-link" href="<?= base_url('masuk') ?>">
      <span class="start-icon" aria-hidden="true"><?= icon('key') ?></span>
      <h2><?= esc(lang('Game.startReturning')) ?></h2>
      <p><?= esc(lang('Game.startReturningText')) ?></p>
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
