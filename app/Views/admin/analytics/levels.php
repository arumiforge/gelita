<?php
/**
 * Analitik level — `/admin/analitik/level` → AnalyticsController::levels
 *
 * @var array<int, array<string, mixed>> $rows    AnalyticsService::levelBreakdown()
 * @var array<string, mixed>             $filters
 */
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('charts') ?>1<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title'   => 'Analitik wilayah',
    'eyebrow' => 'Analitik',
    'lead'    => 'Rata-rata dari percobaan yang selesai pada setiap wilayah.',
]) ?>
<?= $this->include('partials/flash') ?>
<?= component('admin-filter-bar', ['filters' => $filters]) ?>

<?= component('admin-chart', [
    'id'       => 'chart-levels',
    'title'    => 'Rata-rata skor & tepat sejak awal per wilayah',
    'type'     => 'bar',
    'endpoint' => 'api/admin/levels',
    'size'     => 'lg',
    'fallback' => component('partials/bar-list', ['max' => 100, 'rows' => array_values(array_map(
        static fn (array $l): array => ['label' => $l['name'], 'value' => $l['avg_score'], 'display' => fmt_num($l['avg_score'], 1, 'id') . ' · ' . fmt_pct($l['avg_first_pass'], false, 0)],
        $rows,
    ))]),
]) ?>

<?= component('admin-table', [
    'rows'         => array_values($rows),
    'caption'      => 'Ringkasan per wilayah',
    'emptyMessage' => 'Belum ada wilayah aktif.',
    'rowClass'     => static fn (array $r): string => (int) $r['completed_attempts'] === 0 ? 'is-muted' : '',
    'columns'      => [
        'name'               => 'Wilayah',
        'completed_attempts' => ['label' => 'Percobaan selesai', 'format' => 'num'],
        'avg_score'          => ['label' => 'Rata-rata skor', 'format' => 'num', 'decimals' => 1],
        'avg_first_pass'     => ['label' => 'Tepat sejak awal', 'format' => 'pct'],
        'avg_duration_ms'    => ['label' => 'Rata-rata durasi', 'format' => 'ms'],
        'level_id'           => ['label' => '', 'render' => static fn (array $r): string => '<a class="btn btn-quiet btn-sm" href="' . base_url('admin/analitik/node?level_id=' . $r['level_id']) . '">Tantangan</a>'],
    ],
]) ?>
<?= $this->endSection() ?>
