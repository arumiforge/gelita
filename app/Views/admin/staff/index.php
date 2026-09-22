<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= esc($pageTitle) ?> · Panel GELITA<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h1><?= esc($pageTitle) ?></h1>
<?= $this->include('partials/flash') ?>

<?php if ($temporary !== null): ?>
  <p class="alert alert-ok" role="status">
    Kata sandi <b><?= esc($temporary['staff']->username) ?></b> sudah diatur ulang.
  </p>
  <p class="temp-password">Sandi sementara: <code><?= esc($temporary['password']) ?></code></p>
  <p><b>Sampaikan langsung kepada yang bersangkutan.</b> Sandi ini hanya ditampilkan sekali.</p>
<?php endif ?>

<table class="data-table">
  <thead>
    <tr><th scope="col">Nama pengguna</th><th scope="col">Nama</th><th scope="col">Role</th>
        <th scope="col">Sekolah</th><th scope="col">Aktif</th><th scope="col"></th></tr>
  </thead>
  <tbody>
    <?php foreach ($rows as $staff): ?>
      <tr>
        <td><?= esc($staff->username) ?></td>
        <td><?= esc($staff->display_name) ?></td>
        <td><?= esc($staff->role) ?></td>
        <td><?= esc($staff->school_id ?? '—') ?></td>
        <td><?= $staff->is_active ? 'ya' : 'tidak' ?></td>
        <td>
          <form method="post" action="<?= base_url('admin/staf/' . $staff->id . '/sandi') ?>" class="inline-form">
            <?= csrf_field() ?>
            <input type="text" name="confirm" placeholder="RESET" required pattern="RESET" aria-label="Konfirmasi reset">
            <button class="btn btn-quiet btn-sm" type="submit">Reset sandi</button>
          </form>
          <?php if ($staff->is_active): ?>
            <form method="post" action="<?= base_url('admin/staf/' . $staff->id . '/nonaktif') ?>" class="inline-form">
              <?= csrf_field() ?>
              <button class="btn btn-danger btn-sm" type="submit">Nonaktifkan</button>
            </form>
          <?php endif ?>
        </td>
      </tr>
    <?php endforeach ?>
    <?php if ($rows === []): ?>
      <tr><td colspan="6"><?= esc(lang('Admin.emptyDefault')) ?></td></tr>
    <?php endif ?>
  </tbody>
</table>

<h2>Akun baru</h2>
<form method="post" action="<?= base_url('admin/staf') ?>" class="form" autocomplete="off">
  <?= csrf_field() ?>
  <div class="field"><label for="username">Nama pengguna</label><input type="text" id="username" name="username" required></div>
  <div class="field"><label for="display_name">Nama</label><input type="text" id="display_name" name="display_name" required></div>
  <div class="field"><label for="email">Email</label><input type="email" id="email" name="email"></div>
  <div class="field">
    <label for="role">Role</label>
    <select id="role" name="role" required>
      <option value="guru">Guru</option>
      <option value="admin">Admin</option>
    </select>
  </div>
  <div class="field">
    <label for="school_id">Sekolah (wajib untuk guru)</label>
    <select id="school_id" name="school_id">
      <option value="">— tanpa sekolah —</option>
      <?php foreach ($schools as $school): ?>
        <option value="<?= esc($school['id']) ?>"><?= esc($school['name']) ?></option>
      <?php endforeach ?>
    </select>
  </div>
  <div class="field">
    <label for="password">Kata sandi awal (minimal 12 karakter)</label>
    <input type="password" id="password" name="password" required minlength="12" autocomplete="new-password">
  </div>
  <button class="btn btn-primary" type="submit">Buat akun</button>
</form>
<?= $this->endSection() ?>
