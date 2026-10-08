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
 * @var array<string, mixed>       $events      bentuk tabel data-table
 * @var array<string, mixed>       $progress    bentuk tabel data-table
 * @var array<string, mixed>|null  $readiness   ResearchReadiness::check() (admin)
 * @var list<array<string, mixed>> $retentionAlerts
 * @var bool                       $isAdmin
 */
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title' => 'Ringkasan',
    'lead'  => $isAdmin
        ? 'Hitungan data mentah yang sudah tercatat di semua sekolah.'
        : 'Hitungan data mentah siswa dari sekolah Anda.',
]) ?>
<?= $this->include('partials/flash') ?>

<section class="kpi-grid" aria-label="Angka utama">
  <?= component('stat-tile', ['label' => 'Siswa terdaftar', 'value' => fmt_num($kpi['participants'], 0, 'id'), 'icon' => 'users']) ?>
  <?= component('stat-tile', ['label' => 'Sesi aktif sekarang', 'value' => fmt_num($kpi['active_sessions'], 0, 'id'), 'icon' => 'clock', 'hint' => 'ada aktivitas ≤ 10 menit terakhir']) ?>
  <?= component('stat-tile', ['label' => 'Pengerjaan selesai hari ini', 'value' => fmt_num($kpi['completed_today'], 0, 'id'), 'icon' => 'check', 'hint' => 'sejak pukul 00.00']) ?>
  <?= component('stat-tile', ['label' => 'Kritik & saran', 'value' => fmt_num($kpi['feedback'], 0, 'id'), 'icon' => 'message']) ?>
</section>

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

<div class="stack">
  <?= component('data-table', [
      'table'        => $events,
      'id'           => 'aktivitas',
      'title'        => 'Aktivitas terbaru',
      'csv'          => false,
      'paginate'     => false,
      'stickyFirst'  => false,
      'emptyMessage' => 'Belum ada aktivitas. Aktivitas muncul setelah siswa mulai bermain.',
      'tools'        => '<a class="btn btn-sm btn-quiet" href="' . base_url('admin/sesi') . '">Semua sesi</a>',
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
<?= $this->endSection() ?>
