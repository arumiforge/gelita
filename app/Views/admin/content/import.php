<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= esc($pageTitle) ?> · Panel GELITA<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h1><?= esc($pageTitle) ?></h1>
<?= $this->include('partials/flash') ?>

<form method="post" action="<?= base_url('admin/konten/impor-bank/pratinjau') ?>"
      class="form" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <div class="field">
    <label for="file">Workbook bank soal (.xlsx, maks 20 MB)</label>
    <input type="file" id="file" name="file" accept=".xlsx" required>
  </div>
  <button class="btn btn-primary" type="submit">Pratinjau</button>
</form>

<p><a class="btn btn-quiet" href="<?= base_url('admin/konten/impor-bank/templat') ?>">Unduh templat kosong</a></p>

<?php $result = session('import_result'); ?>
<?php if ($result !== null): ?>
  <h2>Hasil impor</h2>
  <pre class="json-block"><?= esc(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
<?php endif ?>

<?php if (is_array($preview)): ?>
  <h2>Pratinjau <?= esc($preview['at'] ?? '') ?></h2>

  <?php if ($preview['errors'] !== []): ?>
    <h3>Galat (<?= count($preview['errors']) ?>)</h3>
    <table class="data-table">
      <thead><tr><th scope="col">Sheet</th><th scope="col">Baris</th><th scope="col">Pesan</th></tr></thead>
      <tbody>
        <?php foreach ($preview['errors'] as $row): ?>
          <tr><td><?= esc($row['sheet']) ?></td><td><?= esc($row['row']) ?></td><td><?= esc($row['message']) ?></td></tr>
        <?php endforeach ?>
      </tbody>
    </table>
  <?php endif ?>

  <?php if ($preview['warnings'] !== []): ?>
    <h3>Peringatan (<?= count($preview['warnings']) ?>)</h3>
    <table class="data-table">
      <thead><tr><th scope="col">Sheet</th><th scope="col">Baris</th><th scope="col">Pesan</th></tr></thead>
      <tbody>
        <?php foreach ($preview['warnings'] as $row): ?>
          <tr><td><?= esc($row['sheet']) ?></td><td><?= esc($row['row']) ?></td><td><?= esc($row['message']) ?></td></tr>
        <?php endforeach ?>
      </tbody>
    </table>
  <?php endif ?>

  <h3>Ringkasan</h3>
  <pre class="json-block"><?= esc(json_encode($preview['summary'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>

  <?php if (! empty($preview['ok'])): ?>
    <form method="post" action="<?= base_url('admin/konten/impor-bank/jalankan') ?>">
      <?= csrf_field() ?>
      <button class="btn btn-primary btn-lg" type="submit">Jalankan impor</button>
    </form>
  <?php else: ?>
    <p class="alert alert-error" role="alert">Perbaiki galat di workbook lalu unggah ulang.</p>
  <?php endif ?>
<?php endif ?>

<h2>Riwayat impor</h2>
<?= $this->include('components/admin-table', [
    'columns' => ['occurred_at' => 'Waktu', 'target_id' => 'Berkas', 'metadata_json' => 'Metadata'],
    'rows'    => $history,
]) ?>
<?= $this->endSection() ?>
