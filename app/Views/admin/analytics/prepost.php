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
    'eyebrow' => 'Hasil belajar',
    'lead'    => 'Membandingkan skor pretest (tes awal) dan posttest (tes akhir) dari siswa yang sudah menyelesaikan keduanya.',
]) ?>
<?= $this->include('partials/flash') ?>
<?= component('admin-filter-bar', ['filters' => $filters, 'only' => ['study_id', 'school_id', 'class_level', 'province_code', 'date_from', 'locale']]) ?>

<section class="kpi-grid" aria-label="Ringkasan pretest dan posttest">
  <?= component('stat-tile', ['label' => 'Rata-rata pretest', 'value' => $result['pairs'] > 0 ? fmt_num($result['pretest'], 1, 'id') : '—', 'icon' => 'flask']) ?>
  <?= component('stat-tile', ['label' => 'Rata-rata posttest', 'value' => $result['pairs'] > 0 ? fmt_num($result['posttest'], 1, 'id') : '—', 'icon' => 'flask']) ?>
  <?= component('stat-tile', ['label' => 'Perubahan skor', 'value' => $delta === null ? '—' : ($delta >= 0 ? '+' : '') . fmt_num($delta, 2, 'id'), 'icon' => 'trend', 'hint' => 'posttest dikurangi pretest']) ?>
  <?= component('stat-tile', ['label' => 'Siswa yang dibandingkan', 'value' => fmt_num($result['pairs'], 0, 'id'), 'icon' => 'users']) ?>
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
    <p>Belum ada siswa yang menyelesaikan pretest dan posttest dengan versi permainan yang sama. Setelah pretest selesai, ganti fase studi ke Posttest di menu Pengaturan penelitian.</p>
  </div>
<?php endif ?>

<section class="panel">
  <h2 class="panel-title"><?= icon('warn') ?> Tidak dapat dibandingkan <span class="chip num"><?= esc($result['incompatible_pairs']) ?></span></h2>
  <?php if ($result['incompatible_pairs'] === 0): ?>
    <p class="muted">Tidak ada. Semua siswa mengerjakan pretest dan posttest dengan versi permainan yang sama.</p>
  <?php else: ?>
    <p class="muted"><?= esc($result['incompatible_pairs']) ?> siswa mengerjakan pretest dan posttest dengan versi permainan yang berbeda
      (soal atau cara penilaiannya bisa berubah di antara keduanya), jadi <b>tidak</b> ikut dihitung dalam rata-rata dan perubahan skor di atas.
      Bandingkan mereka secara terpisah, atau minta mereka mengulang posttest dengan versi yang sama.</p>
  <?php endif ?>
</section>
<?= $this->endSection() ?>
