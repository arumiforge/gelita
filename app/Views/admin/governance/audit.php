<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= esc($pageTitle) ?> · Panel GELITA<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h1><?= esc($pageTitle) ?></h1>
<?= $this->include('partials/flash') ?>

<form method="get" class="filter-bar">
  <label for="action">Aksi</label>
  <select id="action" name="action">
    <option value="">— semua —</option>
    <?php foreach ($actions as $option): ?>
      <option value="<?= esc($option) ?>" <?= $action === $option ? 'selected' : '' ?>><?= esc($option) ?></option>
    <?php endforeach ?>
  </select>
  <label for="date_from">Dari</label>
  <input type="date" id="date_from" name="date_from" value="<?= esc($from) ?>">
  <label for="date_to">Sampai</label>
  <input type="date" id="date_to" name="date_to" value="<?= esc($to) ?>">
  <button class="btn btn-primary" type="submit">Terapkan</button>
</form>

<?= $this->include('components/admin-table', [
    'columns' => [
        'occurred_at'   => 'Waktu',
        'action'        => 'Aksi',
        'staff_user_id' => 'Staf',
        'target_type'   => 'Jenis target',
        'target_id'     => 'Target',
        'metadata_json' => 'Metadata',
    ],
    'rows' => $rows,
]) ?>

<?= $pager?->links() ?>
<a class="btn btn-quiet" href="<?= base_url('admin/tata-kelola') ?>">Kembali</a>
<?= $this->endSection() ?>
