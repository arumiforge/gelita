<?php
/**
 * Beranda panel — `/admin/dashboard` → DashboardController::index
 *
 * Filter bar; 9 KPI; 4 chart (pretest→posttest, skor per wilayah, heatmap
 * kesulitan node, sebaran umur); 5 node tersulit; 10 sesi terakhir.
 * Chart digambar ECharts (admin/charts.js) dari /api/admin/*; tanpa
 * JavaScript wadahnya berisi visual cadangan dari data yang sama.
 *
 * @var array<string, mixed>              $filters
 * @var array<string, mixed>              $summary
 * @var array<int, array<string, mixed>>  $levels
 * @var array<int, array<string, mixed>>  $nodes
 * @var array<string, mixed>              $cohort
 * @var array<int, int>                   $ages
 * @var array<string, mixed>              $digitalSecurity
 * @var list<array<string, mixed>>        $recentSessions
 */
$password   = $digitalSecurity['password'];
$strongPct  = $password['participants'] > 0 ? $password['strong_first_try'] / $password['participants'] : null;
$audio      = $summary['audio_usage'];
$audioShare = $summary['session_count'] > 0 ? $audio['sessions_with_audio'] / $summary['session_count'] : null;
$hardest    = $nodes;
uasort($hardest, static fn (array $a, array $b): int => $b['difficulty_index'] <=> $a['difficulty_index']);
$hardest = array_slice(array_filter($hardest, static fn (array $n): bool => $n['attempts'] > 0), 0, 5);
$delta   = $summary['pretest_posttest_delta'];
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('charts') ?>1<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title'   => 'Beranda penelitian',
    'eyebrow' => 'Ringkasan',
    'lead'    => 'Angka di halaman ini mengikuti filter dan cakupan akun Anda.',
]) ?>
<?= $this->include('partials/flash') ?>
<?= component('admin-filter-bar', ['filters' => $filters, 'only' => ['study_id', 'phase_code', 'school_id', 'class_level', 'province_code', 'date_from', 'locale']]) ?>

<section class="kpi-grid" aria-label="Indikator utama">
  <?= component('stat-tile', ['label' => 'Peserta', 'value' => fmt_num($summary['participant_count'], 0, 'id'), 'icon' => 'users']) ?>
  <?= component('stat-tile', ['label' => 'Sesi', 'value' => fmt_num($summary['session_count'], 0, 'id'), 'icon' => 'clock']) ?>
  <?= component('stat-tile', ['label' => 'Tingkat penyelesaian', 'value' => fmt_pct($summary['completion_rate'], true), 'icon' => 'check', 'hint' => 'sesi berstatus selesai']) ?>
  <?= component('stat-tile', ['label' => 'Rata-rata skor', 'value' => fmt_num($summary['avg_score'], 1, 'id'), 'icon' => 'star', 'hint' => 'percobaan selesai']) ?>
  <?= component('stat-tile', ['label' => 'Tepat sejak awal', 'value' => fmt_pct($summary['avg_first_pass_accuracy']), 'icon' => 'target']) ?>
  <?= component('stat-tile', ['label' => 'Rata-rata durasi sesi', 'value' => ms_to_human($summary['avg_duration_ms']), 'icon' => 'clock']) ?>
  <?= component('stat-tile', ['label' => 'Pemakaian petunjuk', 'value' => fmt_num($summary['hint_usage'], 2, 'id'), 'icon' => 'hint', 'hint' => 'rata-rata per percobaan']) ?>
  <?= component('stat-tile', ['label' => 'Pemakaian audio', 'value' => fmt_pct($audioShare, true), 'icon' => 'sound', 'hint' => fmt_num($audio['total_plays'], 0, 'id') . ' kali diputar']) ?>
  <?= component('stat-tile', ['label' => 'Sandi kuat sejak awal', 'value' => fmt_pct($strongPct, true), 'icon' => 'key', 'hint' => 'siswa memenuhi 5 syarat pada percobaan pertama']) ?>
</section>

<div class="chart-grid">
  <?= component('admin-chart', [
      'id'          => 'chart-prepost',
      'title'       => 'Perubahan pretest → posttest',
      'type'        => 'line',
      'endpoint'    => 'api/admin/prepost',
      'description' => $delta === null ? 'Belum ada pasangan yang dapat dibandingkan.' : 'Selisih rata-rata posttest dan pretest: ' . fmt_num($delta, 2, 'id'),
      'fallback'    => $delta === null
          ? '<p class="chart-empty">' . icon('trend') . ' Belum ada pasangan pretest–posttest yang kompatibel.</p>'
          : '<p class="chart-big num">' . esc(($delta >= 0 ? '+' : '') . fmt_num($delta, 2, 'id')) . '</p><p class="chart-note">selisih rata-rata skor posttest − pretest</p>',
  ]) ?>
  <?= component('admin-chart', [
      'id'       => 'chart-levels',
      'title'    => 'Rata-rata skor per wilayah',
      'type'     => 'bar',
      'endpoint' => 'api/admin/levels',
      'fallback' => component('partials/bar-list', ['max' => 100, 'rows' => array_values(array_map(
          static fn (array $l): array => ['label' => $l['name'], 'value' => $l['avg_score'], 'display' => fmt_num($l['avg_score'], 1, 'id')],
          $levels,
      ))]),
  ]) ?>
  <?= component('admin-chart', [
      'id'       => 'chart-difficulty',
      'title'    => 'Kesulitan tantangan (3 wilayah × 5 node)',
      'type'     => 'heatmap',
      'endpoint' => 'api/admin/nodes',
      'fallback' => component('partials/node-heatmap', ['levels' => service('contentRepository')->levels(), 'nodes' => $nodes, 'links' => true]),
  ]) ?>
  <?= component('admin-chart', [
      'id'       => 'chart-ages',
      'title'    => 'Sebaran umur peserta',
      'type'     => 'bar',
      'endpoint' => 'api/admin/participants',
      'fallback' => component('partials/bar-list', ['rows' => array_map(
          static fn (int $age, int $total): array => ['label' => $age . ' tahun', 'value' => $total, 'display' => (string) $total],
          array_keys($ages),
          array_values($ages),
      )]),
  ]) ?>
</div>

<div class="split-grid">
  <section class="panel">
    <h2 class="panel-title"><?= icon('warn') ?> 5 tantangan tersulit</h2>
    <?php if ($hardest === []): ?>
      <div class="empty-state"><?= icon('info') ?><p>Belum ada percobaan yang selesai. Data muncul setelah siswa menyelesaikan tantangan.</p></div>
    <?php else: ?>
      <ol class="rank-list">
        <?php foreach ($hardest as $node): ?>
          <li>
            <a href="<?= base_url('admin/analitik/node/' . $node['node_id']) ?>"><?= esc($node['title']) ?></a>
            <span class="rank-meta"><?= esc($node['engine_type']) ?> · <?= esc(fmt_num($node['attempts'], 0, 'id')) ?> percobaan</span>
            <span class="rank-value num"><?= esc(fmt_num($node['difficulty_index'], 1, 'id')) ?></span>
          </li>
        <?php endforeach ?>
      </ol>
    <?php endif ?>
  </section>

  <section class="panel">
    <h2 class="panel-title"><?= icon('key') ?> Literasi keamanan digital</h2>
    <p class="muted">Syarat sandi yang terpenuhi pada percobaan pertama saat mendaftar (dari 5).</p>
    <?= component('partials/bar-list', ['rows' => array_map(
        static fn (int $met, int $total): array => ['label' => $met . ' syarat', 'value' => $total, 'display' => (string) $total],
        array_keys($password['criteria_distribution']),
        array_values($password['criteria_distribution']),
    )]) ?>
    <p class="muted">Rata-rata penolakan sandi lemah: <b class="num"><?= esc(fmt_num($password['avg_weak_submit'], 2, 'id')) ?></b> kali per siswa.</p>
    <?php if (! empty($digitalSecurity['pillars'])): ?>
      <h3 class="panel-subtitle">Ketepatan per pilar digital</h3>
      <?= component('partials/bar-list', ['max' => 1, 'rows' => array_map(
          static fn (string $pillar, array $row): array => ['label' => $pillar, 'value' => $row['accuracy'], 'display' => fmt_pct($row['accuracy'], true)],
          array_keys($digitalSecurity['pillars']),
          array_values($digitalSecurity['pillars']),
      )]) ?>
    <?php endif ?>
  </section>
</div>

<section class="panel">
  <h2 class="panel-title"><?= icon('users') ?> Sebaran peserta <span class="chip num"><?= esc(fmt_num($cohort['total'], 0, 'id')) ?></span></h2>
  <div class="cohort-grid">
    <?php foreach (['by_gender' => 'Jenis kelamin', 'by_class_level' => 'Kelas', 'by_province' => 'Provinsi'] as $key => $label): ?>
      <div>
        <h3 class="panel-subtitle"><?= esc($label) ?></h3>
        <?php if ($cohort[$key] === []): ?>
          <p class="muted">—</p>
        <?php else: ?>
          <ul class="chip-list">
            <?php foreach ($cohort[$key] as $bucket => $total): ?>
              <li class="chip"><?= esc($bucket === '' ? 'tidak diisi' : $bucket) ?> <b class="num"><?= esc($total) ?></b></li>
            <?php endforeach ?>
          </ul>
        <?php endif ?>
      </div>
    <?php endforeach ?>
  </div>
</section>

<section class="panel">
  <h2 class="panel-title"><?= icon('clock') ?> 10 sesi terakhir</h2>
  <?= component('admin-table', [
      'rows'         => $recentSessions,
      'emptyMessage' => 'Belum ada sesi permainan. Sesi muncul setelah siswa mendaftar dan mulai bermain.',
      'columns'      => [
          'participant_code' => ['label' => 'Peserta', 'format' => 'code'],
          'phase_code'       => 'Fase',
          'status'           => ['label' => 'Status', 'format' => 'badge'],
          'locale'           => 'Bahasa',
          'started_at'       => ['label' => 'Mulai', 'format' => 'datetime'],
          'duration_ms'      => ['label' => 'Durasi', 'format' => 'ms'],
          'completed_nodes'  => ['label' => 'Serpihan', 'format' => 'num'],
          'total_score'      => ['label' => 'Skor', 'format' => 'num', 'decimals' => 1],
          'id'               => ['label' => '', 'render' => static fn (array $r): string => '<a class="btn btn-quiet btn-sm" href="' . base_url('admin/sesi/' . $r['id']) . '">Buka</a>'],
      ],
  ]) ?>
</section>
<?= $this->endSection() ?>
