<?php
/**
 * Daftar batang horizontal — visual cadangan chart batang yang terbaca tanpa
 * JavaScript dan oleh pembaca layar (label + angka selalu tertulis).
 *
 * @var list<array{label: string, value: float|int|null, display?: string}> $rows
 * @var float|int|null $max  nilai 100% (bawaan: nilai terbesar)
 */
$rows = $rows ?? [];
$max  = (float) ($max ?? max(array_map(static fn (array $r): float => (float) ($r['value'] ?? 0), $rows ?: [['value' => 0]])));
?>
<?php if ($rows === []): ?>
  <p class="chart-empty"><?= icon('chart') ?> Belum ada data untuk filter ini.</p>
<?php else: ?>
  <ul class="bar-list">
    <?php foreach ($rows as $row): ?>
      <?php $width = $max > 0 ? max(0, min(100, (float) ($row['value'] ?? 0) / $max * 100)) : 0; ?>
      <li class="bar-row">
        <span class="bar-label"><?= esc($row['label']) ?></span>
        <span class="progress" aria-hidden="true"><i style="width: <?= round($width, 2) ?>%"></i></span>
        <span class="bar-value"><?= esc($row['display'] ?? fmt_num($row['value'], 1, 'id')) ?></span>
      </li>
    <?php endforeach ?>
  </ul>
<?php endif ?>
