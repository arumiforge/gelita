<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= esc($pageTitle) ?> · Panel GELITA<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h1><?= esc($pageTitle) ?></h1>
<?= $this->include('partials/flash') ?>

<section class="kpi-grid">
  <?php
  $tiles = [
      'Peserta'            => $summary['participant_count'],
      'Sesi'               => $summary['session_count'],
      'Sesi selesai'       => round($summary['completion_rate'] * 100, 1) . '%',
      'Rata-rata skor'     => $summary['avg_score'],
      'Tepat sejak awal'   => $summary['avg_first_pass_accuracy'] . '%',
      'Rata-rata durasi'   => ms_to_human((int) $summary['avg_duration_ms']),
      'Rata-rata petunjuk' => $summary['hint_usage'],
  ];
  ?>
  <?php foreach ($tiles as $label => $value): ?>
    <article class="stat-tile">
      <h2><?= esc($label) ?></h2>
      <p class="stat-value"><?= esc($value) ?></p>
    </article>
  <?php endforeach ?>
</section>

<h2>Skor per wilayah</h2>
<?= $this->include('components/admin-table', [
    'columns' => [
        'name'               => 'Wilayah',
        'avg_score'          => 'Rata-rata skor',
        'avg_first_pass'     => 'Tepat sejak awal',
        'completed_attempts' => 'Percobaan selesai',
    ],
    'rows' => $levels,
]) ?>

<h2>Sebaran peserta</h2>
<dl class="cohort">
  <dt>Total</dt><dd><?= esc($cohort['total']) ?></dd>
  <?php foreach (['by_gender' => 'Jenis kelamin', 'by_class_level' => 'Kelas', 'by_province' => 'Provinsi'] as $key => $label): ?>
    <dt><?= esc($label) ?></dt>
    <dd>
      <?php foreach ($cohort[$key] as $bucket => $total): ?>
        <span class="chip"><?= esc($bucket === '' ? '—' : $bucket) ?>: <?= esc($total) ?></span>
      <?php endforeach ?>
    </dd>
  <?php endforeach ?>
</dl>

<h2>Literasi keamanan digital</h2>
<pre class="json-block"><?= esc(json_encode($digitalSecurity, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>

<div id="chart-dashboard" class="admin-chart" data-endpoint="<?= base_url('api/admin/summary') ?>"></div>
<?= $this->include('partials/stage-note') ?>
<?= $this->endSection() ?>
