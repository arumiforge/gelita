<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= esc($pageTitle) ?> · Panel GELITA<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h1><?= esc($pageTitle) ?></h1>
<?= $this->include('partials/flash') ?>

<p>Rilis aktif: <code><?= esc($active['release_code'] ?? '—') ?></code></p>

<table class="data-table">
  <thead>
    <tr><th scope="col">Kode</th><th scope="col">Aplikasi</th><th scope="col">Konten</th>
        <th scope="col">Skoring</th><th scope="col">Aktif</th><th scope="col"></th></tr>
  </thead>
  <tbody>
    <?php foreach ($releases as $release): ?>
      <tr>
        <td><code><?= esc($release['release_code']) ?></code></td>
        <td><?= esc($release['app_version']) ?></td>
        <td><?= esc($release['content_version']) ?></td>
        <td><?= esc($release['scoring_version']) ?></td>
        <td><?= $release['is_active'] ? 'ya' : 'tidak' ?></td>
        <td>
          <?php if (! $release['is_active']): ?>
            <form method="post" action="<?= base_url('admin/studi/rilis/' . $release['id'] . '/aktifkan') ?>">
              <?= csrf_field() ?>
              <button class="btn btn-primary btn-sm" type="submit">Aktifkan</button>
            </form>
          <?php endif ?>
        </td>
      </tr>
    <?php endforeach ?>
    <?php if ($releases === []): ?>
      <tr><td colspan="6"><?= esc(lang('Admin.emptyDefault')) ?></td></tr>
    <?php endif ?>
  </tbody>
</table>

<h2>Rilis baru</h2>
<form method="post" action="<?= base_url('admin/studi/rilis') ?>" class="form">
  <?= csrf_field() ?>
  <div class="field"><label for="release_code">Kode rilis</label><input type="text" id="release_code" name="release_code" required></div>
  <div class="field"><label for="app_version">Versi aplikasi</label><input type="text" id="app_version" name="app_version" required></div>
  <div class="field"><label for="content_version">Versi konten</label><input type="text" id="content_version" name="content_version" required></div>
  <div class="field"><label for="asset_version">Versi aset</label><input type="text" id="asset_version" name="asset_version" value="1"></div>
  <div class="field"><label for="scoring_version">Versi skoring</label><input type="text" id="scoring_version" name="scoring_version" required></div>
  <div class="field"><label for="notes">Catatan</label><textarea id="notes" name="notes" rows="2"></textarea></div>
  <button class="btn btn-primary" type="submit">Buat rilis</button>
</form>

<a class="btn btn-quiet" href="<?= base_url('admin/studi') ?>">Kembali</a>
<?= $this->endSection() ?>
