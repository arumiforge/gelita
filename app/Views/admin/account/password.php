<?php
/**
 * Ubah sandi sendiri — `/admin/akun/sandi` → AccountController (admin & guru)
 *
 * Kolom sandi tidak pernah diisi ulang dari old(). Aturan 12–72 karakter sama
 * dengan pembuatan akun di /admin/staf; server tetap memeriksanya. Kolom nama
 * pengguna tersembunyi (tanpa `name`) hanya membantu pengelola sandi browser
 * menyimpan sandi baru pada akun yang benar.
 *
 * @var string $username
 */
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title'   => 'Ubah sandi',
    'eyebrow' => 'Akun Anda',
    'lead'    => 'Setelah akun dibuat atau sandinya diatur ulang admin, segera ganti sandi sementara di sini.',
]) ?>
<?= $this->include('partials/flash') ?>

<section class="panel">
  <form method="post" action="<?= base_url('admin/akun/sandi') ?>" class="stack staff-form">
    <?= csrf_field() ?>
    <input type="text" value="<?= esc($username, 'attr') ?>" autocomplete="username" hidden>

    <div class="field<?= isset($errors['current_password']) ? ' has-error' : '' ?>">
      <label for="current-password">Kata sandi saat ini <span class="req">*</span></label>
      <input type="password" id="current-password" name="current_password" required maxlength="72"
             autocomplete="current-password" <?= isset($errors['current_password']) ? 'aria-invalid="true"' : '' ?>>
    </div>

    <div class="field<?= isset($errors['password']) ? ' has-error' : '' ?>">
      <label for="new-password">Kata sandi baru <span class="req">*</span></label>
      <input type="password" id="new-password" name="password" required minlength="12" maxlength="72"
             autocomplete="new-password" aria-describedby="new-password-help"
             <?= isset($errors['password']) ? 'aria-invalid="true"' : '' ?>>
      <p class="field-help" id="new-password-help">Minimal 12 karakter dan berbeda dari sandi saat ini. Frasa beberapa kata lebih mudah diingat dan tetap sulit ditebak.</p>
    </div>

    <div class="field<?= isset($errors['password_confirm']) ? ' has-error' : '' ?>">
      <label for="confirm-password">Ulangi kata sandi baru <span class="req">*</span></label>
      <input type="password" id="confirm-password" name="password_confirm" required minlength="12" maxlength="72"
             autocomplete="new-password" <?= isset($errors['password_confirm']) ? 'aria-invalid="true"' : '' ?>>
    </div>

    <div class="form-actions">
      <button class="btn btn-primary" type="submit"><?= icon('key') ?> Simpan sandi baru</button>
    </div>
  </form>
</section>
<?= $this->endSection() ?>
