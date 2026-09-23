<?php
/**
 * Wadah chart ECharts.
 *
 * admin/charts.js mengambil data dari `data-endpoint` (/api/admin/*, kunci
 * `chart` pada respons) dengan filter halaman, lalu menggambar chart
 * `data-chart` di dalam `.chart-canvas`. Chart tanpa endpoint (profil peserta,
 * drilldown node) membawa datanya sendiri lewat $data — bentuk
 * App\Libraries\ChartData — di <script type="application/json">. Sampai
 * ECharts siap — dan setiap kali JavaScript gagal dimuat — `.chart-canvas`
 * berisi visual cadangan yang dirender server dari data yang sama
 * ($fallback), sehingga informasinya tetap terbaca (aturan 9).
 *
 * @var string      $id
 * @var string      $title
 * @var string      $type        line | bar | stacked-bar | heatmap | scatter | matrix | gauge
 * @var string|null $endpoint    path relatif /api/admin/…
 * @var string|null $description ringkasan untuk pembaca layar
 * @var string|null $fallback    HTML cadangan yang SUDAH di-escape pemanggil
 * @var string|null $size        `sm` | `lg`
 * @var array<string, mixed>|null $data  data chart tertanam (tanpa endpoint)
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
  <?php if (isset($data) && is_array($data)): ?>
    <script type="application/json" class="chart-data"><?= json_encode(
        $data,
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT,
    ) ?></script>
  <?php endif ?>
</figure>
