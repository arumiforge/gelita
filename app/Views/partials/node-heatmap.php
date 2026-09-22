<?php
/**
 * Heatmap kesulitan node (baris = wilayah, kolom = urutan node) — visual
 * cadangan chart `heatmap`. Warna diperkuat angka indeks di tiap sel, jadi
 * warna bukan satu-satunya penanda.
 *
 * @var list<App\Entities\Level>          $levels
 * @var array<int, array<string, mixed>>  $nodes   AnalyticsService::nodeDifficulty()
 * @var bool|null                         $links   sel menjadi tautan drilldown
 */
$byLevel = [];
$maxSeq  = 0;
foreach ($nodes as $row) {
    $byLevel[(int) $row['level_id']][(int) $row['sequence']] = $row;
    $maxSeq = max($maxSeq, (int) $row['sequence']);
}
?>
<?php if ($nodes === []): ?>
  <p class="chart-empty"><?= icon('chart') ?> Belum ada data untuk filter ini.</p>
<?php else: ?>
  <table class="heatmap" style="--cols: <?= $maxSeq ?>">
    <caption class="visually-hidden">Indeks kesulitan per node (0 = mudah, 100 = sulit)</caption>
    <thead>
      <tr>
        <th scope="col"><span class="visually-hidden">Wilayah</span></th>
        <?php for ($seq = 1; $seq <= $maxSeq; $seq++): ?>
          <th scope="col">Node <?= $seq ?></th>
        <?php endfor ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($levels as $level): ?>
        <tr>
          <th scope="row"><?= esc($level->text('name', 'id')) ?></th>
          <?php for ($seq = 1; $seq <= $maxSeq; $seq++): ?>
            <?php $cell = $byLevel[$level->id][$seq] ?? null; ?>
            <?php if ($cell === null): ?>
              <td class="heat-cell is-empty">—</td>
            <?php else: ?>
              <?php $empty = (int) $cell['attempts'] === 0; ?>
              <td class="heat-cell<?= $empty ? ' is-empty' : '' ?>" style="--heat: <?= $empty ? 0 : round((float) $cell['difficulty_index'] / 100, 3) ?>"
                  title="<?= esc($cell['title'] . ' — indeks ' . fmt_num($cell['difficulty_index'], 1, 'id') . ', ' . $cell['attempts'] . ' percobaan', 'attr') ?>">
                <?php if (! empty($links)): ?>
                  <a href="<?= base_url('admin/analitik/node/' . $cell['node_id']) ?>"><?= $empty ? '—' : esc(fmt_num($cell['difficulty_index'], 0, 'id')) ?></a>
                <?php else: ?>
                  <?= $empty ? '—' : esc(fmt_num($cell['difficulty_index'], 0, 'id')) ?>
                <?php endif ?>
              </td>
            <?php endif ?>
          <?php endfor ?>
        </tr>
      <?php endforeach ?>
    </tbody>
  </table>
  <p class="heat-legend" aria-hidden="true"><span>mudah</span><i></i><span>sulit</span></p>
<?php endif ?>
