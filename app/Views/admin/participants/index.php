<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= esc($pageTitle) ?> · Panel GELITA<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h1><?= esc($pageTitle) ?></h1>
<?= $this->include('partials/flash') ?>

<form method="get" class="filter-bar">
  <label for="q">Cari</label>
  <input type="search" id="q" name="q" value="<?= esc($search) ?>" placeholder="kode, nama pengguna, nama">
  <label for="class_level">Kelas</label>
  <input type="text" id="class_level" name="class_level" value="<?= esc($filters['class_level'] ?? '') ?>">
  <button class="btn btn-primary" type="submit">Terapkan</button>
</form>

<table class="data-table">
  <thead>
    <tr>
      <th scope="col">Kode</th><th scope="col">Nama pengguna</th><th scope="col">Nama</th>
      <th scope="col">Kelas</th><th scope="col">Sekolah</th><th scope="col">Terdaftar</th><th scope="col"></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($rows as $row): ?>
      <tr>
        <td><?= esc($row->participant_code) ?></td>
        <td><?= esc($row->username) ?></td>
        <td><?= esc($row->display_name ?? '—') ?></td>
        <td><?= esc($row->class_level ?? '—') ?></td>
        <td><?= esc($row->school_name_snapshot ?? '—') ?></td>
        <td><?= esc($row->created_at) ?></td>
        <td><a class="btn btn-quiet" href="<?= base_url('admin/peserta/' . $row->id) ?>">Buka</a></td>
      </tr>
    <?php endforeach ?>
    <?php if ($rows === []): ?>
      <tr><td colspan="7"><?= esc(lang('Admin.emptyDefault')) ?></td></tr>
    <?php endif ?>
  </tbody>
</table>

<?= $pager?->links() ?>
<?= $this->endSection() ?>
