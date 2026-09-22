<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc(lang('Game.start')) ?> · GELITA<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="start">
  <h1><?= esc(lang('Game.start')) ?></h1>
  <?= $this->include('partials/flash') ?>

  <div class="start-actions">
    <a class="btn btn-primary btn-lg" href="<?= base_url('persetujuan') ?>">Saya baru di sini</a>
    <a class="btn btn-ghost btn-lg" href="<?= base_url('masuk') ?>">Saya sudah punya akun</a>
  </div>
</section>
<?= $this->endSection() ?>
