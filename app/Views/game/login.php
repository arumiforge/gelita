<?php
/**
 * 5. Masuk — `/masuk` → LoginController::form
 *
 * Satu pesan galat netral (Auth.loginFailed) atau Auth.locked dengan sisa
 * menit — keduanya datang dari server lewat $errors['password'].
 * login.js (tahap 6): lihat/sembunyikan sandi, peringatan Caps Lock, dan
 * mencegah kirim ganda.
 *
 * @var bool                  $allowPhase
 * @var list<string>          $phases
 * @var array<string, string> $errors
 */
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc(lang('Game.loginTitle')) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= media_key_src('bg.welcome') ?? '' ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="screen screen-narrow login">
  <div class="panel-parchment">
    <header class="login-head">
      <?= component('character', ['character' => 'jaka', 'pose' => 'bow', 'class' => 'login-jaka']) ?>
      <div>
        <span class="eyebrow"><?= esc(lang('Game.appName')) ?></span>
        <h1><?= esc(lang('Game.loginTitle')) ?></h1>
        <p class="muted"><?= esc(lang('Game.loginLead')) ?></p>
      </div>
    </header>

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

      <?php if ($allowPhase): ?>
        <div class="field">
          <label for="phase"><?= esc(lang('Game.phase')) ?></label>
          <select id="phase" name="phase">
            <?php foreach ($phases as $phase): ?>
              <option value="<?= esc($phase, 'attr') ?>" <?= old('phase') === $phase ? 'selected' : '' ?>><?= esc(lang_or('Game.phase_' . $phase, $phase)) ?></option>
            <?php endforeach ?>
          </select>
        </div>
      <?php endif ?>

      <div class="form-actions">
        <button class="btn btn-primary btn-lg btn-block" type="submit"><?= esc(lang('Game.login')) ?> <?= icon('right') ?></button>
      </div>
    </form>

    <div class="login-foot">
      <p><?= esc(lang('Game.noAccount')) ?> <a href="<?= base_url('persetujuan') ?>"><?= esc(lang('Game.registerLink')) ?></a></p>
      <p class="muted"><?= icon('info') ?> <?= esc(lang('Game.forgotPassword')) ?></p>
    </div>
  </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('nav') ?>
<?= component('nav-bar', ['nav' => [
    ['label' => lang('Game.back'), 'href' => base_url('mulai'), 'style' => 'quiet', 'arrow' => 'left'],
]]) ?>
<?= $this->endSection() ?>
