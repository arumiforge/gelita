<?php
/**
 * 5. Masuk — `/masuk` → LoginController::form
 *
 * Satu pesan galat netral (Auth.loginFailed) atau Auth.locked dengan sisa
 * menit — keduanya datang dari server lewat $errors['password'].
 * login.js (tahap 6): lihat/sembunyikan sandi, peringatan Caps Lock, dan
 * mencegah kirim ganda.
 *
 * `device_json` diisi core/device.js saat dikirim (DeviceProfile); tanpa
 * JavaScript kosong, server memakai User-Agent + Client Hints header.
 *
 * Same look as `/mulai` (9 Oct 2026): Jaka on the left, the ornate dark panel
 * with the form scrolling inside it, Back in the HUD top-left, logo centred.
 *
 * @var array<string, string> $errors
 */
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc(lang('Game.loginTitle')) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= media_key_src('bg.auth') ?? media_key_src('bg.welcome') ?? '' ?><?= $this->endSection() ?>

<?= $this->section('hudBack') ?><?= base_url('mulai') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="login">
  <?= component('character', ['character' => 'jaka', 'pose' => 'bow', 'class' => 'start-jaka stage-box']) ?>

  <div class="login-panel ornate-panel stage-box">
    <?= $this->include('partials/ornate-frame') ?>
    <div class="ornate-body stage-scroll">
      <header class="start-head">
        <h1><?= esc(lang('Game.loginTitle')) ?></h1>
        <p><?= esc(lang('Game.loginLead')) ?></p>
      </header>
      <span class="ornate-divider" aria-hidden="true"></span>

      <?= $this->include('partials/form-errors') ?>

      <form method="post" action="<?= base_url('masuk') ?>" class="form login-form" data-login-form>
        <?= csrf_field() ?>

        <div class="field<?= isset($errors['username']) ? ' has-error' : '' ?>">
          <label for="username"><?= esc(lang('Auth.username')) ?></label>
          <input type="text" id="username" name="username" required autocomplete="username"
                 autocapitalize="none" spellcheck="false" value="<?= esc(old('username'), 'attr') ?>">
        </div>

        <div class="field<?= isset($errors['password']) ? ' has-error' : '' ?>"
             data-show-label="<?= esc(lang('Auth.showPassword'), 'attr') ?>"
             data-hide-label="<?= esc(lang('Auth.hidePassword'), 'attr') ?>">
          <label for="password"><?= esc(lang('Auth.password')) ?></label>
          <div class="password-input">
            <input type="password" id="password" name="password" required autocomplete="current-password">
            <button type="button" class="pw-toggle" aria-pressed="false" aria-controls="password"
                    aria-label="<?= esc(lang('Auth.showPassword'), 'attr') ?>"><?= icon('eye') ?></button>
          </div>
          <p class="pw-caps" hidden><?= icon('warn') ?> <?= esc(lang('Auth.capsLock')) ?></p>
        </div>

        <input type="hidden" name="device_json" value="">

        <div class="form-actions">
          <button class="btn btn-primary btn-lg btn-block" type="submit"><?= esc(lang('Game.login')) ?> <?= icon('right') ?></button>
        </div>
      </form>

      <div class="login-foot">
        <p><?= esc(lang('Game.noAccount')) ?> <a href="<?= base_url('daftar') ?>"><?= esc(lang('Game.registerLink')) ?></a></p>
        <p class="muted"><?= icon('info') ?> <?= esc(lang('Game.forgotPassword')) ?></p>
      </div>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
