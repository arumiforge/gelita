<?php
/**
 * Pretest & posttest — `/admin/analitik/prepost` → AnalyticsController::prePost
 *
 * Hanya pasangan sesi dengan rilis yang sama yang dibandingkan. Pasangan yang
 * `release_id`-nya berbeda ditampilkan terpisah dengan penjelasan, tidak ikut
 * dihitung ke dalam selisih.
 *
 * @var array{pretest: float, posttest: float, delta: ?float, pairs: int, incompatible_pairs: int} $result
 * @var array<string, mixed> $filters
 */
$delta = $result['delta'];
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('charts') ?>1<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title'   => 'Pretest & posttest',
    'eyebrow' => 'Analitik',
    'lead'    => 'Perbandingan skor sesi pretest dan posttest yang sudah selesai, per peserta yang punya keduanya.',
]) ?>
<?= $this->include('partials/flash') ?>
<?= component('admin-filter-bar', ['filters' => $filters, 'only' => ['study_id', 'school_id', 'class_level', 'province_code', 'date_from', 'locale']]) ?>

<section class="kpi-grid" aria-label="Ringkasan pretest dan posttest">
  <?= component('stat-tile', ['label' => 'Rata-rata pretest', 'value' => $result['pairs'] > 0 ? fmt_num($result['pretest'], 1, 'id') : '—', 'icon' => 'flask']) ?>
  <?= component('stat-tile', ['label' => 'Rata-rata posttest', 'value' => $result['pairs'] > 0 ? fmt_num($result['posttest'], 1, 'id') : '—', 'icon' => 'flask']) ?>
  <?= component('stat-tile', ['label' => 'Selisih', 'value' => $delta === null ? '—' : ($delta >= 0 ? '+' : '') . fmt_num($delta, 2, 'id'), 'icon' => 'trend']) ?>
  <?= component('stat-tile', ['label' => 'Pasangan dibandingkan', 'value' => fmt_num($result['pairs'], 0, 'id'), 'icon' => 'users']) ?>
</section>

<?= component('admin-chart', [
    'id'       => 'chart-prepost',
    'title'    => 'Rata-rata skor pretest → posttest',
    'type'     => 'line',
    'endpoint' => 'api/admin/prepost',
    'size'     => 'lg',
    'fallback' => $result['pairs'] === 0 ? null : component('partials/bar-list', ['max' => 100, 'rows' => [
        ['label' => 'Pretest', 'value' => $result['pretest'], 'display' => fmt_num($result['pretest'], 1, 'id')],
        ['label' => 'Posttest', 'value' => $result['posttest'], 'display' => fmt_num($result['posttest'], 1, 'id')],
    ]]),
]) ?>

<?php if ($result['pairs'] === 0): ?>
  <div class="empty-state"><?= icon('info') ?>
    <p>Belum ada peserta yang menyelesaikan sesi pretest dan posttest pada rilis yang sama. Pastikan fase aktif studi sudah diganti ke posttest setelah pretest selesai.</p>
  </div>
<?php endif ?>

<section class="panel">
  <h2 class="panel-title"><?= icon('warn') ?> Pasangan tidak kompatibel <span class="chip num"><?= esc($result['incompatible_pairs']) ?></span></h2>
  <?php if ($result['incompatible_pairs'] === 0): ?>
    <p class="muted">Tidak ada. Semua pasangan pretest–posttest memakai rilis konten dan versi skoring yang sama.</p>
  <?php else: ?>
    <p class="muted"><?= esc($result['incompatible_pairs']) ?> peserta punya pretest dan posttest pada rilis konten yang berbeda
      (soal, bobot, atau versi skoring dapat berubah di antara keduanya). Pasangan ini <b>tidak</b> dihitung dalam rata-rata dan selisih di atas.
      Analisis mereka secara terpisah atau ulangi posttest pada rilis yang sama.</p>
  <?php endif ?>
</section>
<?= $this->endSection() ?>
