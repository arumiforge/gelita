<?php
/**
 * Masuk panel staf — `/admin/login` → AuthController::loginForm
 *
 * Akun tidak ada, sandi salah, dan akun nonaktif dibalas pesan yang sama.
 *
 * @var string                $redirectTo
 * @var array<string, string> $errors
 */
?>
<?= $this->extend('layouts/auth') ?>

<?= $this->section('title') ?><?= esc(lang('Admin.loginTitle')) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<h1 class="auth-title"><?= esc(lang('Admin.loginTitle')) ?></h1>
<?= $this->include('partials/flash') ?>

<form method="post" action="<?= base_url('admin/login') ?>" class="form">
  <?= csrf_field() ?>
  <input type="hidden" name="redirect_to" value="<?= esc($redirectTo, 'attr') ?>">

  <div class="field<?= isset($errors['username']) ? ' has-error' : '' ?>">
    <label for="username">Nama pengguna</label>
    <input type="text" id="username" name="username" required autocomplete="username"
           autocapitalize="none" spellcheck="false" value="<?= esc(old('username'), 'attr') ?>">
  </div>

  <div class="field<?= isset($errors['password']) ? ' has-error' : '' ?>">
    <label for="password">Kata sandi</label>
    <input type="password" id="password" name="password" required autocomplete="current-password">
  </div>

  <button class="btn btn-primary btn-lg btn-block" type="submit"><?= icon('key') ?> Masuk</button>
</form>

<p class="auth-foot">Panel ini khusus guru dan admin penelitian.<br>
  <a href="<?= base_url() ?>">Kembali ke permainan</a></p>
<?= $this->endSection() ?>
