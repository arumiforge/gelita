<?php
/**
 * "See you" — `/sampai-jumpa` → FieldTokenController::closed (research
 * revision 10, part 2: 28 §7, mock-up `mk-tk-03`). Shown when the field
 * session is over or the game is closed outside a session. No shard HUD and
 * no links to Pustaka or Profil.
 *
 * @var bool $fieldMode
 */
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('music') ?>welcome<?= $this->endSection() ?>
<?= $this->section('title') ?><?= esc(lang('Game.sessionClosedTitle')) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= asset_url_versioned('ui/bg-auth-login.webp') ?><?= $this->endSection() ?>
<?= $this->section('bodyClass') ?>is-auth-art<?= $this->endSection() ?>
<?= $this->section('hudLayout') ?>logo-centre<?= $this->endSection() ?>
<?= $this->section('hudShards') ?>off<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="change-password session-closed">
  <div class="password-panel ornate-panel stage-box">
    <?= $this->include('partials/ornate-frame') ?>
    <div class="ornate-body stage-scroll">
      <header class="start-head">
        <span class="eyebrow"><?= esc(lang('Game.appName')) ?></span>
        <h1><?= esc(lang('Game.sessionClosedTitle')) ?></h1>
      </header>
      <span class="ornate-divider" aria-hidden="true"></span>
      <p><?= esc(lang('Game.sessionClosedBody')) ?></p>
      <div class="form-actions">
        <?php if ($fieldMode): ?>
          <a class="btn btn-primary btn-lg btn-block" href="<?= base_url('token') ?>"><?= esc(lang('Game.tokenEnter')) ?></a>
        <?php endif ?>
        <a class="btn btn-quiet btn-lg btn-block" href="<?= base_url('keluar') ?>"><?= esc(lang('Game.logout')) ?></a>
      </div>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
