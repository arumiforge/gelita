<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= esc($pageTitle) ?> · Panel GELITA<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h1><?= esc($pageTitle) ?></h1>
<?= $this->include('partials/flash') ?>

<form method="get" class="filter-bar">
  <label for="phase_code">Fase</label>
  <input type="text" id="phase_code" name="phase_code" value="<?= esc($filters['phase_code'] ?? '') ?>">
  <label for="date_from">Dari</label>
  <input type="date" id="date_from" name="date_from" value="<?= esc($filters['date_from'] ?? '') ?>">
  <label for="date_to">Sampai</label>
  <input type="date" id="date_to" name="date_to" value="<?= esc($filters['date_to'] ?? '') ?>">
  <button class="btn btn-primary" type="submit">Terapkan</button>
</form>

<table class="data-table">
  <thead>
    <tr>
      <th scope="col">Kode sesi</th><th scope="col">Peserta</th><th scope="col">Status</th>
      <th scope="col">Bahasa</th><th scope="col">Mulai</th><th scope="col">Durasi</th><th scope="col"></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($rows as $row): ?>
      <tr>
        <td><code><?= esc(substr((string) $row->session_code, 0, 12)) ?></code></td>
        <td><?= esc($row->participant_code ?? $row->participant_id) ?></td>
        <td><?= esc($row->status) ?></td>
        <td><?= esc($row->locale) ?></td>
        <td><?= esc($row->started_at) ?></td>
        <td><?= esc(ms_to_human((int) $row->duration_ms)) ?></td>
        <td><a class="btn btn-quiet" href="<?= base_url('admin/sesi/' . $row->id) ?>">Buka</a></td>
      </tr>
    <?php endforeach ?>
    <?php if ($rows === []): ?>
      <tr><td colspan="7"><?= esc(lang('Admin.emptyDefault')) ?></td></tr>
    <?php endif ?>
  </tbody>
</table>

<?= $pager?->links() ?>
<?= $this->endSection() ?>
