<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
<h1><?= esc(lang('Admin.loginTitle')) ?></h1>
<?= $this->include('partials/flash') ?>

<form method="post" action="<?= base_url('admin/login') ?>" class="form">
  <?= csrf_field() ?>
  <input type="hidden" name="redirect_to" value="<?= esc($redirectTo) ?>">

  <div class="field">
    <label for="username">Nama pengguna</label>
    <input type="text" id="username" name="username" required autocomplete="username" value="<?= esc(old('username')) ?>">
  </div>

  <div class="field">
    <label for="password">Kata sandi</label>
    <input type="password" id="password" name="password" required autocomplete="current-password">
  </div>

  <button class="btn btn-primary btn-lg" type="submit">Masuk</button>
</form>
<?= $this->endSection() ?>
