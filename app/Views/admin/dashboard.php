<?php
/**
 * Beranda panel — `/admin/dashboard` → DashboardController::index
 *
 * Hitungan data mentah (perombakan kelas 5, Dok 1 §12 / K7): jumlah siswa,
 * sesi, pengerjaan, percobaan, petunjuk, dan suara; sebaran siswa; 10 sesi
 * terakhir. Tanpa skor, persentase, indeks, atau chart — pengolahan dilakukan
 * peneliti dari ekspor. Tampilan dasbor baru menyusul di Dok 3.
 *
 * @var array<string, mixed>              $filters
 * @var array<string, mixed>              $summary
 * @var array<string, mixed>              $cohort
 * @var array<string, mixed>              $digitalSecurity
 * @var list<array<string, mixed>>        $recentSessions
 * @var list<array<string, mixed>>        $retentionAlerts pratinjau penghapusan dari retensi (admin)
 */
$password = $digitalSecurity['password'];
$audio    = $summary['audio_usage'];
$bucket   = [
    'by_gender'      => static fn (string $value): string => admin_label('gender', $value),
    'by_class_level' => static fn (string $value): string => class_label($value),
    'by_province'    => static fn (string $value): string => $value,
];
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title'   => 'Beranda',
    'eyebrow' => 'Ringkasan',
    'lead'    => 'Jumlah data mentah yang sudah tercatat. Angka di halaman ini mengikuti pilihan filter di bawah. Akun guru hanya melihat siswa dari sekolahnya sendiri.',
]) ?>
<?= $this->include('partials/flash') ?>
<?php if ($retentionAlerts !== []): ?>
  <div class="alert alert-warn" role="status">
    <?= icon('warn') ?>
    <p>
      Ada data dari <?= count($retentionAlerts) ?> penelitian yang sudah melewati batas lama penyimpanan (<?= esc(fmt_num(array_sum(array_map(static fn (array $r): int => (int) $r['affected_count'], $retentionAlerts)))) ?> catatan).
      Data ini <b>belum</b> dihapus — <a href="<?= base_url('admin/tata-kelola') ?>">buka menu Hapus data &amp; riwayat</a> untuk memeriksa lalu memutuskannya.
    </p>
  </div>
<?php endif ?>
<?= component('admin-filter-bar', ['filters' => $filters, 'only' => ['study_id', 'school_id', 'class_level', 'province_code', 'date_from', 'locale']]) ?>

<section class="kpi-grid" aria-label="Angka utama">
  <?= component('stat-tile', ['label' => 'Siswa', 'value' => fmt_num($summary['participant_count'], 0, 'id'), 'icon' => 'users', 'hint' => 'siswa yang sudah bermain']) ?>
  <?= component('stat-tile', ['label' => 'Sesi bermain', 'value' => fmt_num($summary['session_count'], 0, 'id'), 'icon' => 'clock', 'hint' => fmt_num($summary['completed_sessions'], 0, 'id') . ' sesi tuntas']) ?>
  <?= component('stat-tile', ['label' => 'Pengerjaan selesai', 'value' => fmt_num($summary['completed_attempts'], 0, 'id'), 'icon' => 'check', 'hint' => fmt_num($summary['abandoned_attempts'], 0, 'id') . ' ditinggalkan']) ?>
  <?= component('stat-tile', ['label' => 'Percobaan soal', 'value' => fmt_num($summary['item_tries'], 0, 'id'), 'icon' => 'target', 'hint' => 'baris item_tries']) ?>
  <?= component('stat-tile', ['label' => 'Petunjuk dibuka', 'value' => fmt_num($summary['hint_count'], 0, 'id'), 'icon' => 'info']) ?>
  <?= component('stat-tile', ['label' => 'Suara diputar', 'value' => fmt_num($audio['total_plays'], 0, 'id'), 'icon' => 'sound', 'hint' => fmt_num($audio['total_autoplays'], 0, 'id') . ' kali otomatis']) ?>
  <?= component('stat-tile', ['label' => 'Sandi kuat sejak kiriman pertama', 'value' => fmt_num($password['strong_first_try'] ?? 0, 0, 'id'), 'icon' => 'key', 'hint' => 'dari ' . fmt_num($password['participants'] ?? 0, 0, 'id') . ' siswa']) ?>
</section>

<section class="panel">
  <h2 class="panel-title"><?= icon('users') ?> Sebaran siswa <span class="chip num"><?= esc(fmt_num($cohort['total'], 0, 'id')) ?></span></h2>
  <div class="cohort-grid">
    <?php foreach (['by_gender' => 'Jenis kelamin', 'by_class_level' => 'Kelas', 'by_province' => 'Provinsi'] as $key => $label): ?>
      <div>
        <h3 class="panel-subtitle"><?= esc($label) ?></h3>
        <?php if ($cohort[$key] === []): ?>
          <p class="muted">—</p>
        <?php else: ?>
          <ul class="chip-list">
            <?php foreach ($cohort[$key] as $group => $total): ?>
              <li class="chip"><?= esc((string) $group === '' ? 'tidak diisi' : $bucket[$key]((string) $group)) ?> <b class="num"><?= esc($total) ?></b></li>
            <?php endforeach ?>
          </ul>
        <?php endif ?>
      </div>
    <?php endforeach ?>
  </div>
</section>

<section class="panel">
  <h2 class="panel-title"><?= icon('clock') ?> 10 sesi bermain terakhir</h2>
  <?= component('admin-table', [
      'rows'         => $recentSessions,
      'emptyMessage' => 'Belum ada sesi bermain. Sesi muncul setelah siswa mendaftar dan mulai bermain.',
      'columns'      => [
          'participant_code' => ['label' => 'Kode siswa', 'format' => 'code'],
          'status'           => ['label' => 'Status', 'format' => 'badge'],
          'locale'           => ['label' => 'Bahasa', 'format' => 'label', 'group' => 'locale'],
          'started_at'       => ['label' => 'Mulai', 'format' => 'datetime'],
          'duration_ms'      => ['label' => 'Lama bermain', 'format' => 'ms'],
          'completed_nodes'  => ['label' => 'Tantangan selesai', 'format' => 'num'],
          'total_stars'      => ['label' => 'Bintang', 'format' => 'num'],
          'id'               => ['label' => '', 'render' => static fn (array $r): string => '<a class="btn btn-quiet btn-sm" href="' . base_url('admin/sesi/' . $r['id']) . '">Lihat</a>'],
      ],
  ]) ?>
</section>
<?= $this->endSection() ?>
