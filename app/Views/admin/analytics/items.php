<?php
/**
 * Analisis butir — `/admin/analitik/butir` → AnalyticsController::items
 *
 * @var list<array<string, mixed>> $rows
 * @var array<string, mixed>       $filters
 */
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title'   => 'Analisis butir soal',
    'eyebrow' => 'Analitik',
    'lead'    => 'Tingkat kesukaran dan daya beda tiap butir dari jawaban pertama siswa.',
]) ?>
<?= $this->include('partials/flash') ?>
<?= component('admin-filter-bar', ['filters' => $filters]) ?>

<aside class="explain" aria-labelledby="explain-title">
  <h2 id="explain-title"><?= icon('info') ?> Cara membaca p dan D</h2>
  <p><b>Kesukaran p</b> = proporsi siswa yang menjawab benar pada percobaan pertama (0–1).
    p &lt; 0,30 <b>sukar</b> · 0,30–0,70 <b>sedang</b> · p &gt; 0,70 <b>mudah</b>. Butir yang baik untuk tes umumnya berada di rentang sedang.</p>
  <p><b>Daya beda D</b> = ketepatan kelompok 27% teratas dikurangi 27% terbawah (menurut ketepatan keseluruhan).
    D &lt; 0 <b>buruk</b> (siswa lemah justru lebih sering benar — periksa kunci) · 0–0,19 <b>lemah</b> · 0,20–0,39 <b>cukup</b> · ≥ 0,40 <b>baik</b>.
    D baru dihitung bila ada sedikitnya 4 peserta.</p>
  <p>Baris merah: benar &lt; 50%. Baris hijau: benar &gt; 85%.</p>
</aside>

<?= component('partials/item-analysis-table', ['rows' => $rows, 'showLocation' => true]) ?>
<?= $this->endSection() ?>
