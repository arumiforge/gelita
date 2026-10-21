<?php
/**
 * 2. Mulai — `/mulai` → HomeController::start
 *
 * Dua kartu: "Saya baru" → /daftar; "Saya sudah punya akun" → /masuk.
 *
 * Layout (owner mockup, 9 Oct 2026): Jaka on the left as his own image, one
 * ornate dark panel holding the title and two row cards; the GELITA logo in the
 * HUD centre and Back in its top-right corner (owner, 20 October 2026).
 * The cards' symbols are the owner's round gold buttons (round_icon(); 21
 * October 2026): register, login, and the next arrow.
 */
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('music') ?>welcome<?= $this->endSection() ?>
<?= $this->section('title') ?><?= esc(lang('Game.startTitle')) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= media_key_src('bg.welcome') ?? '' ?><?= $this->endSection() ?>

<?= $this->section('hudBack') ?><?= base_url() ?><?= $this->endSection() ?>
<?= $this->section('hudLayout') ?>logo-centre<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="start" data-package-gate="page" data-package-next="mulai" data-cine="account">
  <?= component('character', ['character' => 'jaka', 'pose' => 'happy', 'class' => 'start-jaka stage-box']) ?>

  <div class="start-panel ornate-panel stage-box">
    <?= $this->include('partials/ornate-frame') ?>
    <div class="ornate-body stage-scroll">
      <header class="start-head">
        <h1><?= esc(lang('Game.startTitle')) ?></h1>
      </header>
      <span class="ornate-divider" aria-hidden="true"></span>

      <div class="start-cards">
        <a class="start-card is-primary card-link" href="<?= base_url('daftar') ?>">
          <span class="start-icon" aria-hidden="true"><?= round_icon('register', 'start-round', 'user-plus') ?></span>
          <div class="start-text">
            <h2><?= esc(lang('Game.startNew')) ?></h2>
            <p><?= esc(lang('Game.startNewText')) ?></p>
          </div>
          <span class="start-go" aria-hidden="true"><?= round_icon('next', 'start-round', 'right') ?></span>
        </a>
        <a class="start-card card-link" href="<?= base_url('masuk') ?>">
          <span class="start-icon" aria-hidden="true"><?= round_icon('login', 'start-round', 'login') ?></span>
          <div class="start-text">
            <h2><?= esc(lang('Game.startReturning')) ?></h2>
            <p><?= esc(lang('Game.startReturningText')) ?></p>
          </div>
          <span class="start-go" aria-hidden="true"><?= round_icon('next', 'start-round', 'right') ?></span>
        </a>
      </div>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
