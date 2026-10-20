<?php
/**
 * 5. Masuk — `/masuk` → LoginController::form
 *
 * Satu pesan galat netral (Auth.loginFailed) atau Auth.locked dengan sisa
 * menit — keduanya datang dari server lewat $errors['password'].
 * login.js: lihat/sembunyikan sandi, peringatan Caps Lock, and the login sent
 * without a reload: an empty field or a wrong password opens a modal and the
 * page stays (LoginController answers JSON to that request). Without
 * JavaScript the form posts and these errors show below as before.
 *
 * `device_json` diisi core/device.js saat dikirim (DeviceProfile); tanpa
 * JavaScript kosong, server memakai User-Agent + Client Hints header.
 *
 * Owner redesign (18 October 2026): the owner's picture (lantern tree above a
 * lit village, `assets/ui/bg-auth-login.webp`, shipped with the game) fills the
 * stage; the form sits in its empty right side and Jaka waves from the stone
 * path (`assets/ui/jaka-wave.webp`, one still picture, no animation frames).
 * Since 20 October 2026 (owner) the form stands straight on the picture's dark
 * side: no panel fill, no extra border, no corner ornaments or compass crest.
 * The GELITA logo in the HUD centre, Back on the navy plate in its top-left
 * corner.
 *
 * @var array<string, string> $errors
 */
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('music') ?>welcome<?= $this->endSection() ?>
<?= $this->section('title') ?><?= esc(lang('Game.loginTitle')) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= asset_url_versioned('ui/bg-auth-login.webp') ?><?= $this->endSection() ?>
<?= $this->section('bodyClass') ?>is-auth-art<?= $this->endSection() ?>

<?= $this->section('hudBack') ?><?= base_url('mulai') ?><?= $this->endSection() ?>
<?= $this->section('hudLayout') ?>logo-centre<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="login" data-package-gate="page" data-package-next="masuk" data-cine="account">
  <?php /* Jaka waving, one still picture (owner, 18 October 2026), standing on the picture's path */ ?>
  <img class="auth-jaka stage-box" src="<?= esc(asset_url_versioned('ui/jaka-wave.webp'), 'attr') ?>" alt="" width="658" height="1490" decoding="async" draggable="false">

  <div class="login-panel stage-box stage-scroll">
    <header class="login-head">
      <h1><?= esc(lang('Game.loginTitle')) ?></h1>
      <p><?= esc(lang('Game.loginLead')) ?></p>
    </header>

    <?= $this->include('partials/form-errors') ?>

    <form method="post" action="<?= base_url('masuk') ?>" class="form login-form" data-login-form>
      <?= csrf_field() ?>

      <div class="field<?= isset($errors['username']) ? ' has-error' : '' ?>">
        <label for="username"><?= esc(lang('Auth.username')) ?></label>
        <div class="input-lead">
          <?= icon('user', 'input-lead-icon') ?>
          <input type="text" id="username" name="username" required autocomplete="username"
                 autocapitalize="none" autocorrect="off" spellcheck="false" enterkeyhint="next" value="<?= esc(old('username'), 'attr') ?>">
        </div>
      </div>

      <div class="field<?= isset($errors['password']) ? ' has-error' : '' ?>"
           data-show-label="<?= esc(lang('Auth.showPassword'), 'attr') ?>"
           data-hide-label="<?= esc(lang('Auth.hidePassword'), 'attr') ?>">
        <label for="password"><?= esc(lang('Auth.password')) ?></label>
        <div class="password-input input-lead">
          <?= icon('key', 'input-lead-icon') ?>
          <input type="password" id="password" name="password" required autocomplete="current-password" enterkeyhint="done">
          <button type="button" class="pw-toggle" aria-pressed="false" aria-controls="password"
                  aria-label="<?= esc(lang('Auth.showPassword'), 'attr') ?>"><?= icon('eye') ?></button>
        </div>
        <p class="pw-caps" hidden><?= icon('warn') ?> <?= esc(lang('Auth.capsLock')) ?></p>
      </div>

      <input type="hidden" name="device_json" value="">

      <div class="form-actions">
        <?php // The owner's lettered MASUK / LOG IN plate (20 October 2026) ?>
        <?= picture_button('login', lang('Game.login'), 'btn btn-primary btn-lg btn-block') ?>
      </div>
    </form>

    <div class="login-foot">
      <p><?= esc(lang('Game.noAccount')) ?> <a href="<?= base_url('daftar') ?>"><?= esc(lang('Game.registerLink')) ?></a></p>
      <p class="muted"><?= icon('info') ?> <?= esc(lang('Game.forgotPassword')) ?></p>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
