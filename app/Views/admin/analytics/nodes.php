<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= esc($pageTitle) ?> · Panel GELITA<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h1><?= esc($pageTitle) ?></h1>
<?= $this->include('partials/flash') ?>

<form method="get" class="filter-bar">
  <label for="study_id">Studi</label>
  <input type="number" id="study_id" name="study_id" value="<?= esc($filters['study_id'] ?? '') ?>">
  <label for="phase_code">Fase</label>
  <input type="text" id="phase_code" name="phase_code" value="<?= esc($filters['phase_code'] ?? '') ?>">
  <label for="class_level">Kelas</label>
  <input type="text" id="class_level" name="class_level" value="<?= esc($filters['class_level'] ?? '') ?>">
  <label for="date_from">Dari</label>
  <input type="date" id="date_from" name="date_from" value="<?= esc($filters['date_from'] ?? '') ?>">
  <label for="date_to">Sampai</label>
  <input type="date" id="date_to" name="date_to" value="<?= esc($filters['date_to'] ?? '') ?>">
  <button class="btn btn-primary" type="submit">Terapkan</button>
</form>

<table class="data-table">
  <thead>
    <tr>
      <th scope="col">Tantangan</th><th scope="col">Engine</th><th scope="col">Indeks kesulitan</th>
      <th scope="col">Percobaan</th><th scope="col">Tepat awal</th><th scope="col"></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($rows as $row): ?>
      <tr>
        <td><?= esc($row['title'] ?? ($row['node_id'] ?? '—')) ?></td>
        <td><?= esc($row['engine_type'] ?? '—') ?></td>
        <td><?= esc($row['difficulty_index'] ?? '—') ?></td>
        <td><?= esc($row['attempts'] ?? 0) ?></td>
        <td><?= esc($row['mean_first_pass'] ?? 0) ?>%</td>
        <td><a class="btn btn-quiet" href="<?= base_url('admin/analitik/node/' . ($row['node_id'] ?? 0)) ?>">Drilldown</a></td>
      </tr>
    <?php endforeach ?>
    <?php if ($rows === []): ?>
      <tr><td colspan="6"><?= esc(lang('Admin.emptyDefault')) ?></td></tr>
    <?php endif ?>
  </tbody>
</table>

<div id="chart-nodes" class="admin-chart" data-endpoint="<?= base_url('api/admin/nodes') ?>"></div>
<?= $this->endSection() ?>
