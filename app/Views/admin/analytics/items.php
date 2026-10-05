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
  <h2 id="explain-title"><?= icon('info') ?> Cara membaca tabel ini</h2>
  <p><b>Persentase benar</b> = bagian siswa yang langsung menjawab benar. Contoh: 80% berarti 8 dari 10 siswa benar sejak awal.
    Di bawah 30% <b>sulit</b> · 30–70% <b>sedang</b> · di atas 70% <b>mudah</b>. Soal yang baik untuk tes umumnya berada di tingkat sedang.</p>
  <p><b>Daya pembeda</b> = selisih persentase benar antara siswa yang nilainya tinggi (27% teratas) dan siswa yang nilainya rendah (27% terbawah).
    Di bawah 0 <b>buruk</b> — siswa yang nilainya rendah justru lebih sering benar, periksa kunci jawabannya · 0–19 <b>lemah</b> · 20–39 <b>cukup</b> · 40 ke atas <b>baik</b>.
    Daya pembeda baru dihitung bila soal itu sudah dijawab sedikitnya 4 siswa.</p>
  <p><b>Tingkat berpikir</b> mengikuti Taksonomi Bloom: C1 mengingat, C2 memahami, C3 menerapkan, C4 menganalisis.</p>
  <p>Baris merah: kurang dari separuh siswa menjawab benar. Baris hijau: lebih dari 85% siswa menjawab benar.</p>
</aside>

<?= component('partials/item-analysis-table', ['rows' => $rows, 'showLocation' => true]) ?>
<?= $this->endSection() ?>
