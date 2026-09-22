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

<pre class="json-block"><?= esc(json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
<div id="chart-items" class="admin-chart" data-endpoint="<?= base_url('api/admin/items') ?>"></div>
<?= $this->include('partials/stage-note') ?>
<?= $this->endSection() ?>
