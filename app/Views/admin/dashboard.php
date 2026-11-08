<?php
/**
 * Ringkasan — `/admin` → DashboardController::index (Dok 3 §8.3)
 *
 * KPI hitungan saja (siswa terdaftar · sesi aktif sekarang · pengerjaan
 * selesai hari ini · kritik & saran), peringatan kesiapan konten untuk admin
 * (gambar contoh, narasi draft, verifikasi konten), tabel "Aktivitas
 * terbaru" (20 event) dan "Progres siswa" (10 terbaru). Tanpa skor dan chart.
 *
 * @var array{participants: int, active_sessions: int, completed_today: int, feedback: int} $kpi
 * @var array<string, mixed>|null  $events      bentuk tabel data-table (peneliti, ketua)
 * @var array<string, mixed>|null  $progress    bentuk tabel data-table (peneliti, ketua)
 * @var array<string, mixed>|null  $readiness   ResearchReadiness::check() (admin)
 * @var list<array<string, mixed>> $retentionAlerts
 * @var bool                       $research    the caller sees coded research data
 */
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title' => 'Ringkasan',
    'lead'  => $research
        ? 'Hitungan data mentah yang sudah tercatat di semua sekolah. Siswa dan sekolah tampil sebagai kode.'
        : 'Hitungan yang sudah tercatat di semua sekolah, kesiapan konten, dan peringatan sistem.',
]) ?>
<?= $this->include('partials/flash') ?>

<section class="kpi-grid" aria-label="Angka utama">
  <?= component('stat-tile', ['label' => 'Siswa terdaftar', 'value' => fmt_num($kpi['participants'], 0, 'id'), 'icon' => 'users']) ?>
  <?= component('stat-tile', ['label' => 'Sesi aktif sekarang', 'value' => fmt_num($kpi['active_sessions'], 0, 'id'), 'icon' => 'clock', 'hint' => 'ada aktivitas ≤ 10 menit terakhir']) ?>
  <?= component('stat-tile', ['label' => 'Pengerjaan selesai hari ini', 'value' => fmt_num($kpi['completed_today'], 0, 'id'), 'icon' => 'check', 'hint' => 'sejak pukul 00.00']) ?>
  <?= component('stat-tile', ['label' => 'Kritik & saran', 'value' => fmt_num($kpi['feedback'], 0, 'id'), 'icon' => 'message']) ?>
</section>

<?php if (($techLog ?? null) !== null): // part 3 (29 §5.6) ?>
  <section class="kpi-grid" aria-label="<?= esc(lang('Admin.menuTechLog'), 'attr') ?>">
    <?= component('stat-tile', ['label' => lang('Admin.techLogDropped7d'), 'value' => fmt_num($techLog['dropped_7d'], 0, 'id'), 'icon' => 'warn']) ?>
    <?= component('stat-tile', ['label' => lang('Admin.techLogOsBrowser'), 'value' => $techLog['os_browser'] === [] ? '—' : $techLog['os_browser'][0]['os'] . ' / ' . $techLog['os_browser'][0]['browser'], 'icon' => 'database', 'hint' => lang('Admin.menuTechLog')]) ?>
  </section>
  <p><a href="<?= base_url('admin/log-teknis') ?>"><?= esc(lang('Admin.menuTechLog')) ?> <?= icon('right') ?></a></p>
<?php endif ?>

<?php if (($fieldSessions ?? []) !== []): ?>
  <section class="form-section" aria-labelledby="dash-field-title">
    <h2 id="dash-field-title"><?= icon('clock') ?> <?= esc(lang('Admin.dashboardFieldSessions')) ?></h2>
    <ul>
      <?php foreach ($fieldSessions as $row): ?>
        <li><b class="mono"><?= esc($row['code']) ?></b> · <?= esc(lang('Admin.monitoringEntered')) ?> <?= esc(fmt_num($row['entered'], 0, 'id')) ?> · <?= esc(lang('Admin.monitoringActive')) ?> <?= esc(fmt_num($row['active'], 0, 'id')) ?></li>
      <?php endforeach ?>
    </ul>
    <p><a href="<?= base_url('admin/pemantauan') ?>"><?= esc(lang('Admin.menuMonitoring')) ?> <?= icon('right') ?></a></p>
  </section>
<?php endif ?>

<?php if ($retentionAlerts !== [] || ($readiness !== null && ! $readiness['ready'])): ?>
  <section class="stack" aria-label="Peringatan">
    <?php if ($retentionAlerts !== []): ?>
      <div class="alert alert-warn" role="status">
        <?= icon('warn') ?>
        <div class="alert-body">
          <b>Data melewati batas lama penyimpanan</b>
          <p>Data dari <?= count($retentionAlerts) ?> penelitian (<?= esc(fmt_num(array_sum(array_map(static fn (array $r): int => (int) $r['affected_count'], $retentionAlerts)), 0, 'id')) ?> catatan) menunggu keputusan. Data ini <b>belum</b> dihapus.</p>
        </div>
        <a class="btn btn-sm" href="<?= base_url('admin/tata-kelola') ?>">Buka Tata kelola</a>
      </div>
    <?php endif ?>
    <?php if ($readiness !== null): ?>
      <?php foreach ($readiness['items'] as $item): ?>
        <?php if ($item['ok']) continue; ?>
        <div class="alert alert-warn" role="status">
          <?= icon('warn') ?>
          <div class="alert-body">
            <b><?= esc(['mockup' => 'Gambar contoh tersisa', 'narration' => 'Narasi belum disetujui', 'verify' => 'Verifikasi konten belum lolos'][$item['key']] ?? $item['label']) ?></b>
            <p><?= esc($item['detail']) ?></p>
          </div>
          <a class="btn btn-sm" href="<?= base_url($item['link']) ?>">Periksa</a>
        </div>
      <?php endforeach ?>
    <?php endif ?>
  </section>
<?php endif ?>

<?php if ($research): ?>
<div class="stack">
  <?= component('data-table', [
      'table'        => $events,
      'id'           => 'aktivitas',
      'title'        => 'Aktivitas terbaru',
      'csv'          => false,
      'paginate'     => false,
      'stickyFirst'  => false,
      'emptyMessage' => 'Belum ada aktivitas. Aktivitas muncul setelah siswa mulai bermain.',
      // The game sends its events in batches (Config\Gelita::$eventFlush), so the list may lag one interval
      'note'         => lang('Admin.recentEventsLag', [fmt_num(round(event_flush_config()['intervalMs'] / 1000), 0, 'id')]),
      'tools'        => staff_can('research.browse') ? '<a class="btn btn-sm btn-quiet" href="' . base_url('admin/sesi') . '">Semua sesi</a>' : '',
  ]) ?>
  <?= component('data-table', [
      'table'        => $progress,
      'id'           => 'progres',
      'title'        => 'Progres siswa',
      'csv'          => false,
      'paginate'     => false,
      'stickyFirst'  => false,
      'emptyMessage' => 'Belum ada siswa terdaftar.',
      'tools'        => '<a class="btn btn-sm btn-quiet" href="' . base_url('admin/peserta') . '">Semua siswa</a>',
      'render'       => [
          'participant_code' => static fn (array $r): string => '<a href="' . base_url('admin/peserta/' . $r['id']) . '">' . esc($r['participant_code']) . '</a>',
          'done'             => static fn (array $r): string => '<span class="num">' . esc($r['done']) . '</span>',
      ],
  ]) ?>
</div>
<?php endif ?>
<?= $this->endSection() ?>
