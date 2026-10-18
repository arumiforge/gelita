<?php
/**
 * 5a. Ganti Sandi — `/ganti-sandi` → LoginController::changePasswordForm
 *
 * After a successful change the server redirects to /gerbang (or /intro
 * when the opening story is still unseen) with the toast
 * "Kata sandi baru tersimpan".
 *
 * Owner redesign (18 October 2026): the same picture and right-side ornate
 * panel as `/masuk` (`assets/ui/bg-auth-login.webp`), with Jaka on the path.
 *
 * @var bool                  $mustChange  datang dari reset guru
 * @var string|null           $username    nama pengguna siswa ini (cek "memuat nama pengguna")
 * @var array<string, mixed>  $passwordPolicy
 * @var array<string, string> $errors
 */
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc(lang('Auth.changeTitle')) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= game_asset_url('ui/bg-auth-login.webp') ?><?= $this->endSection() ?>
<?= $this->section('bodyClass') ?>is-auth-art<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="change-password">
  <?php /* Jaka waving, one still picture (owner, 18 October 2026), standing on the picture's path */ ?>
  <img class="auth-jaka stage-box" src="<?= esc(game_asset_url('ui/jaka-wave.webp'), 'attr') ?>" alt="" width="658" height="1490" decoding="async" draggable="false">

  <div class="password-panel ornate-panel stage-box">
    <?= $this->include('partials/ornate-frame') ?>
    <div class="ornate-body stage-scroll">
      <header class="start-head">
        <span class="eyebrow"><?= esc(lang('Game.appName')) ?></span>
        <h1><?= esc(lang('Auth.changeTitle')) ?></h1>
      </header>
      <span class="ornate-divider" aria-hidden="true"></span>

      <?php if ($mustChange): ?>
        <div class="alert alert-info" role="status"><?= icon('key') ?><p><?= esc(lang('Auth.mustChange')) ?></p></div>
      <?php endif ?>

      <?= $this->include('partials/form-errors') ?>

      <form method="post" action="<?= base_url('ganti-sandi') ?>" class="form" autocomplete="off">
        <?= csrf_field() ?>

        <div class="field<?= isset($errors['current_password']) ? ' has-error' : '' ?>">
          <label for="current_password"><?= esc(lang('Auth.currentPassword')) ?> <span class="req" aria-hidden="true">*</span></label>
          <input type="password" id="current_password" name="current_password" required
                 autocomplete="current-password" enterkeyhint="next" aria-describedby="current-help">
          <p class="field-help" id="current-help"><?= esc(lang('Auth.currentHelp')) ?></p>
          <?php if (isset($errors['current_password'])): ?>
            <p class="field-error"><?= esc($errors['current_password']) ?></p>
          <?php endif ?>
        </div>

        <?= component('password-field', [
            'policy'   => $passwordPolicy,
            'errors'   => $errors,
            'label'    => lang('Auth.newPassword'),
            'username' => $username ?? '',
        ]) ?>

        <div class="form-actions">
          <button class="btn btn-primary btn-lg" type="submit"><?= icon('check') ?> <?= esc(lang('Auth.saveNew')) ?></button>
        </div>
      </form>
    </div>
  </div>
</section>
<?= $this->endSection() ?>

<?php if (! $mustChange): ?>
  <?= $this->section('nav') ?>
  <?= component('nav-bar', ['nav' => [
      ['label' => lang('Game.profile'), 'href' => base_url('profil'), 'style' => 'quiet', 'arrow' => 'left'],
  ]]) ?>
  <?= $this->endSection() ?>
<?php endif ?>
