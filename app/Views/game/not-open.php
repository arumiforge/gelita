<?php
/**
 * "The game is not open" — shown by `/masuk` and `/daftar` (every step)
 * while no research study is `active` (owner, 20 November 2026). The newest
 * study closed → thank-you text; no study or a draft → "ask your teacher".
 * Same frame as `/sampai-jumpa`; no form, no HUD shards.
 *
 * @var string $reason `ended` or `not_open` (ResearchStudyModel::gameClosedReason())
 */
$ended = ($reason ?? '') === 'ended';
$title = lang($ended ? 'Game.studyEndedTitle' : 'Game.gameNotOpenTitle');
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('music') ?>welcome<?= $this->endSection() ?>
<?= $this->section('title') ?><?= esc($title) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= asset_url_versioned('ui/bg-auth-login.webp') ?><?= $this->endSection() ?>
<?= $this->section('bodyClass') ?>is-auth-art<?= $this->endSection() ?>
<?= $this->section('hudLayout') ?>logo-centre<?= $this->endSection() ?>
<?= $this->section('hudShards') ?>off<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="change-password session-closed game-not-open" data-game-closed="<?= esc($ended ? 'ended' : 'not_open', 'attr') ?>">
  <div class="password-panel ornate-panel stage-box">
    <?= $this->include('partials/ornate-frame') ?>
    <div class="ornate-body stage-scroll">
      <header class="start-head">
        <span class="eyebrow"><?= esc(lang('Game.appName')) ?></span>
        <h1><?= esc($title) ?></h1>
      </header>
      <span class="ornate-divider" aria-hidden="true"></span>
      <p role="status"><?= esc(lang($ended ? 'Game.studyEnded' : 'Game.gameNotOpen')) ?></p>
      <div class="form-actions">
        <a class="btn btn-quiet btn-lg btn-block" href="<?= base_url('/') ?>"><?= esc(lang('Game.backToStart')) ?></a>
      </div>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
