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
    'title'   => 'Hasil per soal',
    'eyebrow' => 'Hasil belajar',
    'lead'    => 'Seberapa sulit tiap soal, dan seberapa baik soal itu membedakan siswa yang sudah paham dari yang belum. Dihitung dari jawaban pertama siswa.',
]) ?>
<?= $this->include('partials/flash') ?>
<?= component('admin-filter-bar', ['filters' => $filters]) ?>

<aside class="explain" aria-labelledby="explain-title">
  <h2 id="explain-title"><?= icon('info') ?> Cara membaca angka p dan D</h2>
  <p><b>p (tingkat kesukaran)</b> = bagian siswa yang langsung menjawab benar, dari 0 sampai 1. Contoh: p = 0,80 berarti 8 dari 10 siswa benar.
    p di bawah 0,30 <b>sukar</b> · 0,30–0,70 <b>sedang</b> · di atas 0,70 <b>mudah</b>. Soal yang baik untuk tes umumnya berada di tingkat sedang.</p>
  <p><b>D (daya beda)</b> = seberapa jauh siswa yang nilainya tinggi lebih sering benar dibanding siswa yang nilainya rendah
    (27% siswa teratas dibanding 27% terbawah).
    D di bawah 0 <b>buruk</b> — siswa yang lemah justru lebih sering benar, periksa kunci jawabannya · 0–0,19 <b>lemah</b> · 0,20–0,39 <b>cukup</b> · 0,40 ke atas <b>baik</b>.
    D baru dihitung bila soal itu sudah dijawab sedikitnya 4 siswa.</p>
  <p>Baris merah: kurang dari separuh siswa menjawab benar. Baris hijau: lebih dari 85% siswa menjawab benar.</p>
</aside>

<?= component('partials/item-analysis-table', ['rows' => $rows, 'showLocation' => true]) ?>
<?= $this->endSection() ?>
