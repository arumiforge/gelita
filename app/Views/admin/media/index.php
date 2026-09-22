<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= esc($pageTitle) ?> · Panel GELITA<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h1><?= esc($pageTitle) ?></h1>
<?= $this->include('partials/flash') ?>

<?php $scan = $scan ?? session('media_scan'); ?>
<?php if (is_array($scan)): ?>
  <h2>Hasil pindai <?= esc($scan['at']) ?></h2>
  <?php if ($scan['findings'] === []): ?>
    <p class="alert alert-ok" role="status">Semua berkas aset cocok dengan catatan database.</p>
  <?php else: ?>
    <ul class="alert alert-error" role="alert">
      <?php foreach ($scan['findings'] as $finding): ?>
        <li><?= esc($finding['asset_key']) ?>: <?= esc($finding['issue']) ?></li>
      <?php endforeach ?>
    </ul>
  <?php endif ?>
<?php endif ?>

<form method="post" action="<?= base_url('admin/media/unggah') ?>" class="form" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <div class="field">
    <label for="asset_key">asset_key</label>
    <input type="text" id="asset_key" name="asset_key" required maxlength="160" placeholder="bg.temanggung">
  </div>
  <div class="field">
    <label for="file">Berkas</label>
    <input type="file" id="file" name="file" required>
  </div>
  <button class="btn btn-primary" type="submit">Unggah</button>
</form>

<h2>Ukuran wajib</h2>
<ul class="chips">
  <?php foreach ($assetSizes as $pattern => $size): ?>
    <li class="chip"><code><?= esc($pattern) ?></code> <?= esc($size[0]) ?>×<?= esc($size[1]) ?></li>
  <?php endforeach ?>
</ul>

<h2>Aset</h2>
<table class="data-table">
  <thead>
    <tr><th scope="col">asset_key</th><th scope="col">Jenis</th><th scope="col">Ukuran</th>
        <th scope="col">Berkas</th><th scope="col">Aktif</th><th scope="col"></th></tr>
  </thead>
  <tbody>
    <?php foreach ($assets as $asset): ?>
      <tr>
        <td><code><?= esc($asset['asset_key']) ?></code></td>
        <td><?= esc($asset['asset_type']) ?></td>
        <td><?= esc($asset['width_px'] ?? '—') ?>×<?= esc($asset['height_px'] ?? '—') ?></td>
        <td><?= esc($asset['storage_path']) ?></td>
        <td><?= $asset['is_active'] ? 'ya' : 'tidak' ?></td>
        <td>
          <?php if ($asset['is_active']): ?>
            <form method="post" action="<?= base_url('admin/media/' . $asset['id'] . '/nonaktif') ?>">
              <?= csrf_field() ?>
              <button class="btn btn-quiet btn-sm" type="submit">Nonaktifkan</button>
            </form>
          <?php endif ?>
        </td>
      </tr>
    <?php endforeach ?>
    <?php if ($assets === []): ?>
      <tr><td colspan="6"><?= esc(lang('Admin.emptyDefault')) ?></td></tr>
    <?php endif ?>
  </tbody>
</table>

<form method="post" action="<?= base_url('admin/media/pindai') ?>">
  <?= csrf_field() ?>
  <button class="btn btn-ghost" type="submit">Pindai berkas aset</button>
</form>

<a class="btn btn-quiet" href="<?= base_url('admin/media/audio') ?>">Aset audio</a>
<?= $this->endSection() ?>
