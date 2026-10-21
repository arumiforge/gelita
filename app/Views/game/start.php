<?php
/**
 * 2. Mulai — `/mulai` → HomeController::start
 *
 * Dua kartu: "Saya baru" → /daftar; "Saya sudah punya akun" → /masuk.
 *
 * Layout (owner, 21 October 2026): the title and the two row cards alone in the
 * middle of the stage, with no panel fill, border, shadow, corner ornaments,
 * compass crest, or Jaka; the gate picture (`assets/ui/bg-mulai.webp`) under a
 * deep dark layer (`body.is-dim-art`) keeps the text readable. The GELITA logo
 * in the HUD centre and Back on the navy plate in its top-left corner.
 * The cards' symbols are the owner's round gold buttons (round_icon(); 21
 * October 2026): register, login, and the next arrow.
 */
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('music') ?>welcome<?= $this->endSection() ?>
<?= $this->section('title') ?><?= esc(lang('Game.startTitle')) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= asset_url_versioned('ui/bg-mulai.webp') ?><?= $this->endSection() ?>
<?= $this->section('bodyClass') ?>is-dim-art<?= $this->endSection() ?>

<?= $this->section('hudBack') ?><?= base_url() ?><?= $this->endSection() ?>
<?= $this->section('hudLayout') ?>logo-centre<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="start" data-package-gate="page" data-package-next="mulai" data-cine="account">
  <div class="start-panel ornate-panel is-bare stage-box">
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
