<?php
/**
 * Penguasaan indikator — `/admin/analitik/indikator` → AnalyticsController::indicators
 *
 * Matriks indikator × wilayah berisi rasio jawaban benar pertama beserta
 * JUMLAH BUKTI — bukan label biner lulus/tidak.
 *
 * @var array<string, array<string, mixed>>               $rows      keseluruhan
 * @var list<App\Entities\Level>                          $levels
 * @var array<int, array<string, array<string, mixed>>>   $perLevel  level_id → indikator
 * @var array<string, mixed>                              $filters
 */
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('charts') ?>1<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title'   => 'Hasil per indikator',
    'eyebrow' => 'Hasil belajar',
    'lead'    => 'Persentase jawaban yang langsung benar untuk tiap indikator pembelajaran. Angka kecil di tiap kotak menunjukkan dari berapa jawaban persentase itu dihitung, misalnya 8/10 = 8 benar dari 10 jawaban.',
]) ?>
<?= $this->include('partials/flash') ?>
<?= component('admin-filter-bar', ['filters' => $filters, 'only' => ['study_id', 'phase_code', 'school_id', 'class_level', 'province_code', 'date_from', 'locale']]) ?>

<?php if ($rows === []): ?>
  <div class="empty-state"><?= icon('target') ?><p>Belum ada jawaban yang terkait indikator untuk pilihan filter ini. Tabel terisi setelah siswa menjawab soal yang punya indikator.</p></div>
<?php else: ?>
  <?php ob_start() ?>
  <div class="table-wrap">
    <table class="heatmap matrix">
      <caption class="visually-hidden">Persentase benar tiap indikator per wilayah</caption>
      <thead>
        <tr>
          <th scope="col">Indikator</th>
          <?php foreach ($levels as $level): ?>
            <th scope="col"><?= esc($level->text('name', 'id')) ?></th>
          <?php endforeach ?>
          <th scope="col">Keseluruhan</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $code => $row): ?>
          <tr>
            <th scope="row"><code><?= esc($code) ?></code><span class="cell-sub"><?= esc($row['name']) ?></span></th>
            <?php foreach (array_merge(array_map(static fn ($l) => $perLevel[$l->id][$code] ?? null, $levels), [$row]) as $cell): ?>
              <?php if ($cell === null || (int) $cell['evidence_count'] === 0): ?>
                <td class="heat-cell is-empty">—<small>0 jawaban</small></td>
              <?php else: ?>
                <td class="heat-cell" style="--heat: <?= round((float) $cell['mastery_ratio'], 3) ?>">
                  <?= esc(fmt_pct($cell['mastery_ratio'], true, 0)) ?>
                  <small><?= esc($cell['correct_count'] . '/' . $cell['evidence_count']) ?> jawaban</small>
                </td>
              <?php endif ?>
            <?php endforeach ?>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
  <?php $matrix = ob_get_clean() ?>

  <?= component('admin-chart', [
      'id'       => 'chart-indicators',
      'title'    => 'Persentase benar: indikator × wilayah',
      'type'     => 'matrix',
      'endpoint' => 'api/admin/indicators',
      'size'     => 'lg',
      'fallback' => $matrix,
  ]) ?>

  <?= component('admin-table', [
      'rows'    => array_values($rows),
      'caption' => 'Hasil per indikator secara keseluruhan',
      'columns' => [
          'code'             => ['label' => 'Kode', 'format' => 'code'],
          'name'             => 'Indikator',
          'evidence_count'   => ['label' => 'Jumlah jawaban', 'format' => 'num'],
          'correct_count'    => ['label' => 'Benar sejak awal', 'format' => 'num'],
          'mastery_ratio'    => ['label' => 'Persentase benar', 'format' => 'ratio'],
          'mean_response_ms' => ['label' => 'Rata-rata waktu menjawab', 'format' => 'ms'],
      ],
  ]) ?>
<?php endif ?>
<?= $this->endSection() ?>
