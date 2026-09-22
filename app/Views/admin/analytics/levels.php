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

<?= $this->include('components/admin-table', [
    'columns' => [
        'name'               => 'Wilayah',
        'avg_score'          => 'Rata-rata skor',
        'avg_first_pass'     => 'Tepat sejak awal',
        'completed_attempts' => 'Percobaan selesai',
        'avg_duration_ms'    => 'Rata-rata durasi (ms)',
    ],
    'rows' => $rows,
]) ?>

<div id="chart-levels" class="admin-chart" data-endpoint="<?= base_url('api/admin/levels') ?>"></div>
<?= $this->endSection() ?>
