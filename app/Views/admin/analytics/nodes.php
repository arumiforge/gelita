<?php
/**
 * Analitik tantangan — `/admin/analitik/node` → AnalyticsController::nodes
 *
 * Heatmap 3×5 kesulitan node + tabel diurutkan dari yang tersulit.
 * Indeks kesulitan (01_DATABASE.md): 40% ketepatan awal, 20% ulang,
 * 20% petunjuk, 10% durasi, 10% ditinggalkan.
 *
 * @var array<int, array<string, mixed>> $rows        AnalyticsService::nodeDifficulty()
 * @var list<App\Entities\Level>          $levels
 * @var array<int, ?string>               $indicators id node → kode indikator
 * @var array<string, mixed>              $filters
 */
$sorted = array_values($rows);
usort($sorted, static fn (array $a, array $b): int => [$b['attempts'] > 0, $b['difficulty_index']] <=> [$a['attempts'] > 0, $a['difficulty_index']]);
$levelNames = [];
foreach ($levels as $level) {
    $levelNames[$level->id] = $level->text('name', 'id');
}
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('charts') ?>1<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title'   => 'Hasil per tantangan',
    'eyebrow' => 'Hasil belajar',
    'lead'    => 'Tingkat kesulitan 0–100 (makin tinggi makin sulit) dihitung dari seberapa sering siswa salah pada percobaan pertama, mengulang, membuka petunjuk, lamanya mengerjakan, dan meninggalkan tantangan.',
]) ?>
<?= $this->include('partials/flash') ?>
<?= component('admin-filter-bar', ['filters' => $filters, 'only' => ['study_id', 'phase_code', 'school_id', 'class_level', 'province_code', 'date_from', 'locale']]) ?>

<?= component('admin-chart', [
    'id'       => 'chart-difficulty',
    'title'    => 'Peta warna kesulitan (wilayah × urutan tantangan)',
    'type'     => 'heatmap',
    'endpoint' => 'api/admin/nodes',
    'size'     => 'lg',
    'fallback' => component('partials/node-heatmap', ['levels' => $levels, 'nodes' => $rows, 'links' => true]),
]) ?>

<?= component('admin-table', [
    'rows'         => $sorted,
    'caption'      => 'Kesulitan tiap tantangan, yang tersulit di atas',
    'emptyMessage' => 'Belum ada tantangan yang aktif.',
    'rowClass'     => static fn (array $r): string => (int) $r['attempts'] === 0 ? 'is-muted' : ($r['difficulty_index'] >= 60 ? 'is-bad' : ''),
    'columns'      => [
        'title'              => ['label' => 'Tantangan', 'render' => static fn (array $r): string => '<a href="' . base_url('admin/analitik/node/' . $r['node_id']) . '">' . esc($r['title']) . '</a>'
            . '<span class="cell-sub">' . esc(($levelNames[$r['level_id']] ?? '') . ' · tantangan ke-' . $r['sequence']) . '</span>'],
        'engine_type'        => ['label' => 'Jenis', 'render' => static fn (array $r): string => esc(engine_label((string) $r['engine_type']))],
        'node_id'            => ['label' => 'Indikator', 'render' => static fn (array $r): string => esc($indicators[$r['node_id']] ?? '—')],
        'attempts'           => ['label' => 'Dicoba', 'format' => 'num'],
        'mean_first_pass'    => ['label' => 'Tepat sejak awal', 'format' => 'pct'],
        'mean_final'         => ['label' => 'Benar di akhir', 'format' => 'pct'],
        'mean_retry'         => ['label' => 'Rata-rata mengulang', 'format' => 'num', 'decimals' => 2],
        'mean_hint'          => ['label' => 'Rata-rata petunjuk', 'format' => 'num', 'decimals' => 2],
        'median_duration_ms' => ['label' => 'Lama mengerjakan (nilai tengah)', 'format' => 'ms'],
        'skip_rate'          => ['label' => 'Ditinggalkan', 'format' => 'ratio'],
        'difficulty_index'   => ['label' => 'Kesulitan (0–100)', 'format' => 'num', 'decimals' => 1],
    ],
]) ?>
<?= $this->endSection() ?>
