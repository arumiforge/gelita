<?php
/**
 * Drilldown tantangan — `/admin/analitik/node/{id}` → AnalyticsController::node
 *
 * Sebaran skor, scatter durasi vs ketepatan, daftar butir dengan p dan D,
 * dan tautan ke sesi (lalu linimasa event) tiap percobaan.
 *
 * @var App\Entities\ChallengeNode        $node
 * @var App\Entities\Level|null           $level
 * @var array<int, array<string, mixed>>  $rows      nodeDifficulty() dengan filter node
 * @var list<array<string, mixed>>        $items
 * @var list<array<string, mixed>>        $attempts
 * @var array<string, mixed>              $filters
 */
$stats = $rows[$node->id] ?? null;

// Sebaran skor dalam 5 rentang
$bins = ['0–20' => 0, '20–40' => 0, '40–60' => 0, '60–80' => 0, '80–100' => 0];
$maxDuration = 1;
foreach ($attempts as $attempt) {
    $index = min(4, (int) floor(((float) $attempt['score']) / 20));
    $bins[array_keys($bins)[$index]]++;
    $maxDuration = max($maxDuration, (int) $attempt['duration_ms']);
}
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('charts') ?>1<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title'   => $node->text('title', 'id'),
    'eyebrow' => ($level?->text('name', 'id') ?? '') . ' · node ' . $node->sequence . ' · ' . $node->engine_type,
    'actions' => '<a class="btn btn-quiet btn-sm" href="' . base_url('admin/analitik/node') . '">' . icon('left') . ' Semua tantangan</a>'
        . (session('staff_role') === 'admin' ? '<a class="btn btn-quiet btn-sm" href="' . base_url('admin/konten/node/' . $node->id) . '">' . icon('edit') . ' Sunting konten</a>' : ''),
]) ?>
<?= $this->include('partials/flash') ?>

<?php if ($stats !== null): ?>
  <section class="kpi-grid" aria-label="Ringkasan tantangan">
    <?= component('stat-tile', ['label' => 'Percobaan', 'value' => fmt_num($stats['attempts'], 0, 'id')]) ?>
    <?= component('stat-tile', ['label' => 'Tepat sejak awal', 'value' => fmt_pct($stats['mean_first_pass'])]) ?>
    <?= component('stat-tile', ['label' => 'Ketepatan akhir', 'value' => fmt_pct($stats['mean_final'])]) ?>
    <?= component('stat-tile', ['label' => 'Petunjuk / percobaan', 'value' => fmt_num($stats['mean_hint'], 2, 'id')]) ?>
    <?= component('stat-tile', ['label' => 'Durasi median', 'value' => ms_to_human((int) $stats['median_duration_ms'])]) ?>
    <?= component('stat-tile', ['label' => 'Indeks kesulitan', 'value' => fmt_num($stats['difficulty_index'], 1, 'id')]) ?>
  </section>
<?php endif ?>

<div class="chart-grid">
  <?= component('admin-chart', [
      'id'       => 'chart-node-scores',
      'title'    => 'Sebaran skor',
      'type'     => 'bar',
      'fallback' => $attempts === [] ? null : component('partials/bar-list', ['rows' => array_map(
          static fn (string $range, int $total): array => ['label' => $range, 'value' => $total, 'display' => (string) $total],
          array_keys($bins),
          array_values($bins),
      )]),
  ]) ?>
  <?php
  $scatter = '';
  if ($attempts !== []) {
      $scatter = '<div class="scatter" aria-hidden="true">';
      foreach ($attempts as $attempt) {
          $x = round((int) $attempt['duration_ms'] / $maxDuration * 100, 2);
          $y = round(100 - (float) $attempt['first_pass_accuracy'], 2);
          $scatter .= '<i style="left: ' . $x . '%; top: ' . $y . '%" title="' . esc(ms_to_human((int) $attempt['duration_ms']) . ' · ' . fmt_pct($attempt['first_pass_accuracy'], false, 0), 'attr') . '"></i>';
      }
      $scatter .= '<span class="scatter-x">durasi →</span><span class="scatter-y">tepat awal →</span></div>';
  }
  ?>
  <?= component('admin-chart', [
      'id'          => 'chart-node-scatter',
      'title'       => 'Durasi vs ketepatan awal (per percobaan)',
      'type'        => 'scatter',
      'description' => count($attempts) . ' percobaan selesai',
      'fallback'    => $scatter,
  ]) ?>
</div>

<section class="panel">
  <h2 class="panel-title"><?= icon('list') ?> Butir pada tantangan ini</h2>
  <?= component('partials/item-analysis-table', ['rows' => $items]) ?>
</section>

<section class="panel">
  <h2 class="panel-title"><?= icon('clock') ?> Percobaan terbaru</h2>
  <?= component('admin-table', [
      'rows'         => array_slice($attempts, 0, 20),
      'emptyMessage' => 'Belum ada percobaan selesai pada filter ini.',
      'columns'      => [
          'id'                  => ['label' => '#', 'format' => 'num'],
          'score'               => ['label' => 'Skor', 'format' => 'num', 'decimals' => 1],
          'stars'               => ['label' => 'Bintang', 'render' => static fn (array $r): string => stars_html((int) $r['stars'])],
          'first_pass_accuracy' => ['label' => 'Tepat awal', 'format' => 'pct'],
          'duration_ms'         => ['label' => 'Durasi', 'format' => 'ms'],
          'session_id'          => ['label' => '', 'render' => static fn (array $r): string => '<span class="actions-cell">'
              . '<a class="btn btn-quiet btn-sm" href="' . base_url('admin/sesi/' . $r['session_id']) . '">Sesi</a>'
              . '<a class="btn btn-quiet btn-sm" href="' . base_url('admin/sesi/' . $r['session_id'] . '/event') . '">Event</a></span>'],
      ],
  ]) ?>
</section>
<?= $this->endSection() ?>
