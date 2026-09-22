<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Pratinjau layout · Panel GELITA<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="panel">
  <h1 class="page-title">Pratinjau layout admin</h1>
  <p>Halaman ini hanya ada di <code>development</code>. Dashboard sungguhan dibuat pada tahap 4–5.</p>
  <div class="empty-state"><?= esc(lang('Admin.emptyDefault')) ?></div>
  <p><button type="button" class="btn btn-primary" data-demo-toast>Uji toast</button></p>
</section>
<?= $this->endSection() ?>
