<?php
/**
 * Pendaftaran langkah 3 — Akun rahasiamu (`/daftar/3`).
 *
 * Nama pengguna (cek ketersediaan langsung), kata sandi + ulangi (pengukur
 * kekuatan), dan centang persetujuan siswa. Tombol Daftar TIDAK dinonaktifkan
 * walau sandi belum kuat, dan kolom sandi sengaja tanpa `minlength`:
 * penolakan dari server adalah umpan balik belajar dan dicatat sebagai
 * `pw_weak_submit_count`.
 *
 * `device_json` diisi core/device.js saat dikirim (DeviceProfile); tanpa
 * JavaScript kosong.
 *
 * @var int                   $step
 * @var list<int>             $done
 * @var string                $locale
 * @var array<string, mixed>  $passwordPolicy
 * @var array<string, string> $errors
 */
$err     = static fn (string $f): ?string => $errors[$f] ?? null;
$invalid = static fn (string $f): string => isset($errors[$f]) ? ' has-error' : '';
$first   = array_key_first($errors);
$focus   = static fn (string $f): string => $first === $f ? ' autofocus' : '';
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc(lang('Game.registerTitle')) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= media_key_src('bg.welcome') ?? '' ?><?= $this->endSection() ?>
<?= $this->section('hudBack') ?><?= base_url('daftar/2') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="register" data-step="3">
  <?= component('character', ['character' => 'jaka', 'pose' => 'bow', 'class' => 'start-jaka stage-box']) ?>

  <div class="register-panel ornate-panel stage-box">
    <?= $this->include('partials/ornate-frame') ?>
    <?= $this->include('game/register/_head') ?>

    <form method="post" action="<?= base_url('daftar/3') ?>" class="form register-form" data-step="3" autocomplete="off">
      <?= csrf_field() ?>
      <div class="register-fields stage-scroll">
      <?= view('partials/form-errors', ['errors' => $errors]) ?>

      <aside class="why-strong" aria-labelledby="why-strong-title">
        <div class="why-strong-body">
          <h2 class="why-strong-title" id="why-strong-title"><?= icon('key') ?> <?= esc(lang('Game.whyStrongTitle')) ?></h2>
          <p><?= esc(lang('Auth.whyStrong')) ?></p>
          <ul class="why-strong-tips">
            <li><?= esc(lang('Auth.tip1')) ?></li>
            <li><?= esc(lang('Auth.tip2')) ?></li>
            <li><?= esc(lang('Auth.tip3')) ?></li>
            <li><?= esc(lang('Auth.tip4')) ?></li>
          </ul>
        </div>
      </aside>

      <div class="field<?= $invalid('username') ?>">
        <label for="username"><?= esc(lang('Auth.username')) ?> <span class="req" aria-hidden="true">*</span></label>
        <input type="text" id="username" name="username" required maxlength="30"
               autocomplete="username" autocapitalize="none" spellcheck="false"
               aria-describedby="username-help username-status" data-username-check
               data-available="<?= esc(lang('Auth.usernameAvailable'), 'attr') ?>"
               data-unavailable="<?= esc(lang('Auth.usernameUnavailable'), 'attr') ?>"
               data-format="<?= esc(lang('Auth.usernameFormat'), 'attr') ?>"
               value="<?= esc(old('username'), 'attr') ?>"<?= $focus('username') ?>>
        <p class="field-help" id="username-help"><?= esc(lang('Auth.usernameHelp')) ?></p>
        <p class="username-status" id="username-status" role="status" aria-live="polite"></p>
        <?php if ($err('username')): ?><p class="field-error"><?= esc($err('username')) ?></p><?php endif ?>
      </div>

      <?= component('password-field', [
          'policy'        => $passwordPolicy,
          'errors'        => $errors,
          'usernameField' => 'username',
      ]) ?>

      <div class="field assent-field<?= $invalid('assent') ?>">
        <label class="check assent-check">
          <input type="checkbox" name="assent" value="1" required <?= old('assent') ? 'checked' : '' ?><?= $focus('assent') ?>>
          <span><?= esc(lang('Game.assentLabel')) ?></span>
        </label>
        <?php if ($err('assent')): ?><p class="field-error"><?= esc($err('assent')) ?></p><?php endif ?>
      </div>

      <input type="hidden" name="locale" value="<?= esc($locale, 'attr') ?>">
      <input type="hidden" name="device_json" value="">

      </div>

      <div class="form-actions register-actions">
        <button class="btn btn-primary btn-xl" type="submit"><?= esc(lang('Game.registerSubmit')) ?> <?= icon('right') ?></button>
      </div>
    </form>
  </div>
</section>
<?= $this->endSection() ?>
