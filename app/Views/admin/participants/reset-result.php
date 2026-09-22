<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= esc($pageTitle) ?> · Panel GELITA<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h1><?= esc($pageTitle) ?></h1>
<?= $this->include('partials/flash') ?>

<p class="alert alert-ok" role="status">
  Kata sandi <b><?= esc($participant->username) ?></b> sudah diatur ulang.
</p>

<p class="temp-password">
  Sandi sementara: <code><?= esc($temporaryPassword) ?></code>
</p>

<p><b>Berikan kepada siswa. Siswa wajib membuat sandi baru saat masuk.</b><br>
Sandi ini hanya ditampilkan sekali dan tidak disimpan di mana pun.</p>

<a class="btn btn-primary" href="<?= base_url('admin/peserta/' . $participant->id) ?>">Kembali ke profil</a>
<?= $this->endSection() ?>
