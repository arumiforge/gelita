<?php
/**
 * Wadah chart ECharts.
 *
 * charts.js (tahap 6) mengambil data dari `data-endpoint` (/api/admin/*)
 * dengan filter halaman, lalu menggambar chart `data-chart` di dalam
 * `.chart-canvas`. Sampai saat itu — dan setiap kali JavaScript gagal dimuat —
 * `.chart-canvas` berisi visual cadangan yang dirender server dari data yang
 * sama ($fallback), sehingga informasinya tetap terbaca (aturan 9).
 *
 * @var string      $id
 * @var string      $title
 * @var string      $type        line | bar | stacked-bar | heatmap | scatter | matrix | gauge
 * @var string|null $endpoint    path relatif /api/admin/…
 * @var string|null $description ringkasan untuk pembaca layar
 * @var string|null $fallback    HTML cadangan yang SUDAH di-escape pemanggil
 * @var string|null $size        `sm` | `lg`
 */
$filtersQuery = http_build_query(array_filter((array) ($filters ?? []), static fn ($v): bool => $v !== null && $v !== ''));
?>
<figure class="admin-chart<?= isset($size) ? ' is-' . esc($size, 'attr') : '' ?>">
  <figcaption class="chart-title"><?= esc($title) ?></figcaption>
  <div class="chart-canvas" id="<?= esc($id, 'attr') ?>"
       data-chart="<?= esc($type, 'attr') ?>"
       <?php if (! empty($endpoint)): ?>data-endpoint="<?= esc(base_url($endpoint) . ($filtersQuery !== '' ? '?' . $filtersQuery : ''), 'attr') ?>"<?php endif ?>
       data-empty="Belum ada data untuk filter ini."
       data-description="<?= esc($description ?? $title, 'attr') ?>">
    <?php if (! empty($fallback)): ?>
      <?= $fallback ?>
    <?php else: ?>
      <p class="chart-empty"><?= icon('chart') ?> Belum ada data untuk filter ini.</p>
    <?php endif ?>
  </div>
</figure>
