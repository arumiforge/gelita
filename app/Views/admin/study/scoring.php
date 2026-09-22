<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= esc($pageTitle) ?> · Panel GELITA<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h1><?= esc($pageTitle) ?></h1>
<?= $this->include('partials/flash') ?>

<p>Profil aktif: <code><?= esc($active['code'] ?? '—') ?> v<?= esc($active['version'] ?? '—') ?></code></p>

<?= $this->include('components/admin-table', [
    'columns' => [
        'code'                => 'Kode',
        'version'             => 'Versi',
        'first_pass_weight'   => 'Bobot tepat-awal',
        'final_weight'        => 'Bobot akhir',
        'independence_weight' => 'Bobot kemandirian',
        'is_active'           => 'Aktif',
    ],
    'rows' => $profiles,
]) ?>

<h2>Profil baru</h2>
<form method="post" action="<?= base_url('admin/studi/skoring') ?>" class="form">
  <?= csrf_field() ?>
  <div class="field"><label for="code">Kode</label><input type="text" id="code" name="code" required></div>
  <div class="field"><label for="version">Versi</label><input type="text" id="version" name="version" required></div>
  <div class="field"><label for="first_pass_weight">Bobot tepat sejak awal</label>
    <input type="number" step="0.01" id="first_pass_weight" name="first_pass_weight" required></div>
  <div class="field"><label for="final_weight">Bobot ketepatan akhir</label>
    <input type="number" step="0.01" id="final_weight" name="final_weight" required></div>
  <div class="field"><label for="independence_weight">Bobot kemandirian</label>
    <input type="number" step="0.01" id="independence_weight" name="independence_weight" required></div>
  <label class="check"><input type="checkbox" name="activate" value="1"> Langsung aktifkan</label>
  <button class="btn btn-primary" type="submit">Simpan profil</button>
</form>

<a class="btn btn-quiet" href="<?= base_url('admin/studi') ?>">Kembali</a>
<?= $this->endSection() ?>
