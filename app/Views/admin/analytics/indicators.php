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
    'title'   => 'Penguasaan indikator',
    'eyebrow' => 'Analitik',
    'lead'    => 'Rasio jawaban benar pada percobaan pertama per indikator pembelajaran. Angka kecil di tiap sel adalah jumlah bukti (jawaban) yang mendasarinya.',
]) ?>
<?= $this->include('partials/flash') ?>
<?= component('admin-filter-bar', ['filters' => $filters, 'only' => ['study_id', 'phase_code', 'school_id', 'class_level', 'province_code', 'date_from', 'locale']]) ?>

<?php if ($rows === []): ?>
  <div class="empty-state"><?= icon('target') ?><p>Belum ada jawaban yang terkait indikator pada filter ini. Matriks terisi setelah siswa menjawab butir yang punya indikator.</p></div>
<?php else: ?>
  <?php ob_start() ?>
  <div class="table-wrap">
    <table class="heatmap matrix">
      <caption class="visually-hidden">Rasio penguasaan indikator per wilayah</caption>
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
                <td class="heat-cell is-empty">—<small>0 bukti</small></td>
              <?php else: ?>
                <td class="heat-cell" style="--heat: <?= round((float) $cell['mastery_ratio'], 3) ?>">
                  <?= esc(fmt_pct($cell['mastery_ratio'], true, 0)) ?>
                  <small><?= esc($cell['correct_count'] . '/' . $cell['evidence_count']) ?> bukti</small>
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
      'title'    => 'Matriks indikator × wilayah',
      'type'     => 'matrix',
      'endpoint' => 'api/admin/indicators',
      'size'     => 'lg',
      'fallback' => $matrix,
  ]) ?>

  <?= component('admin-table', [
      'rows'    => array_values($rows),
      'caption' => 'Penguasaan indikator keseluruhan',
      'columns' => [
          'code'             => ['label' => 'Kode', 'format' => 'code'],
          'name'             => 'Indikator',
          'evidence_count'   => ['label' => 'Bukti', 'format' => 'num'],
          'correct_count'    => ['label' => 'Benar awal', 'format' => 'num'],
          'mastery_ratio'    => ['label' => 'Rasio', 'format' => 'ratio'],
          'mean_response_ms' => ['label' => 'Rata-rata waktu', 'format' => 'ms'],
      ],
  ]) ?>
<?php endif ?>
<?= $this->endSection() ?>
