<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= esc($pageTitle) ?> · Panel GELITA<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h1><?= esc($pageTitle) ?></h1>
<?= $this->include('partials/flash') ?>

<h2>Pratinjau penghapusan</h2>
<p>Penghapusan selalu dua langkah: pratinjau jumlah baris, lalu eksekusi dengan
   konfirmasi teks <code><?= esc($confirmWord) ?></code>.</p>

<form method="post" action="<?= base_url('admin/tata-kelola/hapus/pratinjau') ?>" class="form">
  <?= csrf_field() ?>
  <div class="field"><label for="participant_id">ID peserta</label><input type="number" id="participant_id" name="participant_id"></div>
  <div class="field"><label for="session_id">ID sesi</label><input type="number" id="session_id" name="session_id"></div>
  <div class="field"><label for="study_id">ID studi</label><input type="number" id="study_id" name="study_id"></div>
  <div class="field">
    <label for="mode">Mode</label>
    <select id="mode" name="mode">
      <option value="soft">soft — tandai terhapus</option>
      <option value="hard">hard — hapus baris</option>
    </select>
  </div>
  <div class="field"><label for="reason">Alasan</label><textarea id="reason" name="reason" rows="2"></textarea></div>
  <button class="btn btn-primary" type="submit">Hitung baris terdampak</button>
</form>

<h2>Permintaan penghapusan</h2>
<table class="data-table">
  <thead>
    <tr><th scope="col">#</th><th scope="col">Mode</th><th scope="col">Status</th>
        <th scope="col">Baris</th><th scope="col">Cakupan</th><th scope="col">Dibuat</th><th scope="col"></th></tr>
  </thead>
  <tbody>
    <?php foreach ($requests as $request): ?>
      <tr>
        <td><?= esc($request['id']) ?></td>
        <td><?= esc($request['mode']) ?></td>
        <td><?= esc($request['status']) ?></td>
        <td><?= esc($request['affected_count'] ?? 0) ?></td>
        <td><code><?= esc(mb_substr((string) $request['scope_json'], 0, 120)) ?></code></td>
        <td><?= esc($request['created_at']) ?></td>
        <td>
          <?php if ($request['status'] === 'preview'): ?>
            <form method="post" action="<?= base_url('admin/tata-kelola/hapus/' . $request['id'] . '/jalankan') ?>" class="inline-form">
              <?= csrf_field() ?>
              <input type="text" name="confirm" placeholder="<?= esc($confirmWord) ?>" required
                     pattern="<?= esc($confirmWord) ?>" aria-label="Konfirmasi">
              <button class="btn btn-danger btn-sm" type="submit">Eksekusi</button>
            </form>
            <form method="post" action="<?= base_url('admin/tata-kelola/hapus/' . $request['id'] . '/batal') ?>" class="inline-form">
              <?= csrf_field() ?>
              <button class="btn btn-quiet btn-sm" type="submit">Batalkan</button>
            </form>
          <?php endif ?>
        </td>
      </tr>
    <?php endforeach ?>
    <?php if ($requests === []): ?>
      <tr><td colspan="7"><?= esc(lang('Admin.emptyDefault')) ?></td></tr>
    <?php endif ?>
  </tbody>
</table>

<h2>Retensi</h2>
<?php $retentionResult = $retention ?? session('retention_result'); ?>
<?php if (is_array($retentionResult)): ?>
  <p class="alert alert-ok" role="status">
    <?= esc($retentionResult['stale_sessions']) ?> sesi ditandai paused ·
    <?= esc($retentionResult['expired_exports']) ?> berkas ekspor kedaluwarsa dibuang
    (<?= esc($retentionResult['at']) ?>).
  </p>
<?php endif ?>
<p><small>Sesi menganggur lebih dari <?= esc($idleMinutes) ?> menit ditandai <code>paused</code>;
   berkas ekspor lebih tua dari <?= esc($exportRetention) ?> hari dibuang.</small></p>
<form method="post" action="<?= base_url('admin/tata-kelola/retensi') ?>">
  <?= csrf_field() ?>
  <button class="btn btn-ghost" type="submit">Jalankan retensi sekarang</button>
</form>

<a class="btn btn-quiet" href="<?= base_url('admin/tata-kelola/audit') ?>">Audit log</a>
<?= $this->endSection() ?>
