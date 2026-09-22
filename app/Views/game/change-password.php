<?php
/**
 * 5a. Ganti Sandi — `/ganti-sandi` → LoginController::changePasswordForm
 *
 * Setelah berhasil server mengarahkan ke /peta dengan toast
 * "Kata sandi baru tersimpan".
 *
 * @var bool                  $mustChange  datang dari reset guru
 * @var string|null           $username    nama pengguna siswa ini (cek "memuat nama pengguna")
 * @var array<string, mixed>  $passwordPolicy
 * @var array<string, string> $errors
 */
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc(lang('Auth.changeTitle')) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= media_key_src('bg.welcome') ?? '' ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="screen screen-narrow change-password">
  <div class="panel-parchment">
    <span class="eyebrow"><?= esc(lang('Game.appName')) ?></span>
    <h1><?= esc(lang('Auth.changeTitle')) ?></h1>

    <?php if ($mustChange): ?>
      <div class="alert alert-info" role="status"><?= icon('key') ?><p><?= esc(lang('Auth.mustChange')) ?></p></div>
    <?php endif ?>

    <?= $this->include('partials/form-errors') ?>

    <form method="post" action="<?= base_url('ganti-sandi') ?>" class="form" autocomplete="off">
      <?= csrf_field() ?>

      <div class="field<?= isset($errors['current_password']) ? ' has-error' : '' ?>">
        <label for="current_password"><?= esc(lang('Auth.currentPassword')) ?> <span class="req" aria-hidden="true">*</span></label>
        <input type="password" id="current_password" name="current_password" required
               autocomplete="current-password" aria-describedby="current-help">
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
</section>
<?= $this->endSection() ?>

<?php if (! $mustChange): ?>
  <?= $this->section('nav') ?>
  <?= component('nav-bar', ['nav' => [
      ['label' => lang('Game.profile'), 'href' => base_url('profil'), 'style' => 'quiet', 'arrow' => 'left'],
  ]]) ?>
  <?= $this->endSection() ?>
<?php endif ?>
