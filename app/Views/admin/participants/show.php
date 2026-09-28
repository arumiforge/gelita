<?php
/**
 * Profil peserta — `/admin/peserta/{id}` → ParticipantController::show
 *
 * Demografi, nama pengguna, persetujuan, literasi keamanan digital, sesi per
 * fase, skor per wilayah, penguasaan indikator, node tersulit, dan tautan ke
 * linimasa event tiap sesi. Reset sandi wajib diketik `RESET`.
 *
 * @var array<string, mixed>              $participant bentuk aman, tanpa password_hash
 * @var array<string, mixed>              $profile     AnalyticsService::participantProfile()
 * @var array<string, mixed>|null         $consent
 * @var list<array<string, mixed>>        $sessions
 * @var list<App\Entities\Level>          $levels
 * @var array<int, string>                $nodeTitles
 */
$criteria = $participant['pw_first_submit_criteria'];
$region   = implode(', ', array_filter([$participant['district_name'], $participant['province_name'], $participant['country_name']]));
$hardest  = $profile['hardest_node'];
$prePost  = $profile['pre_post'];
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('charts') ?>1<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title'   => $participant['display_name'] ?: $participant['username'],
    'eyebrow' => 'Profil peserta · ' . $participant['participant_code'],
    'lead'    => 'Nama pengguna: ' . $participant['username'] . ' · mendaftar ' . fmt_date($participant['created_at'], false, 'id'),
    'actions' => '<a class="btn btn-quiet btn-sm" href="' . base_url('admin/peserta') . '">' . icon('left') . ' Daftar peserta</a>',
]) ?>
<?= $this->include('partials/flash') ?>

<div class="split-grid">
  <section class="panel">
    <h2 class="panel-title"><?= icon('user') ?> Data diri</h2>
    <dl class="detail-grid">
      <div><dt>Kode peserta</dt><dd><code><?= esc($participant['participant_code']) ?></code></dd></div>
      <div><dt>Nama pengguna</dt><dd><?= esc($participant['username']) ?></dd></div>
      <div><dt>Nama</dt><dd><?= esc($participant['display_name'] ?? '—') ?></dd></div>
      <div><dt>Umur</dt><dd><?= esc($participant['age'] ?? '—') ?></dd></div>
      <div><dt>Jenis kelamin</dt><dd><?= esc(admin_label('gender', $participant['gender'] ?? null)) ?></dd></div>
      <div><dt>Kelas</dt><dd><?= esc($participant['class_level'] ?? '—') ?></dd></div>
      <div><dt>Sekolah</dt><dd><?= esc($participant['school_name'] ?? '—') ?></dd></div>
      <div><dt>Daerah</dt><dd><?= esc($region !== '' ? $region : '—') ?></dd></div>
    </dl>
  </section>

  <section class="panel">
    <h2 class="panel-title"><?= icon('shield') ?> Persetujuan ikut penelitian</h2>
    <?php if ($consent === null): ?>
      <div class="empty-state"><?= icon('warn') ?><p>Tidak ada catatan persetujuan untuk peserta ini.</p></div>
    <?php else: ?>
      <dl class="detail-grid">
        <div><dt>Versi naskah persetujuan</dt><dd><?= esc($consent['consent_version']) ?></dd></div>
        <div><dt>Waktu</dt><dd><?= esc(fmt_date($consent['consented_at'], false, 'id')) ?></dd></div>
        <div><dt>Siswa setuju</dt><dd><?= $consent['participant_consented'] ? 'ya' : 'tidak' ?></dd></div>
        <div><dt>Izin orang tua/wali</dt><dd><?= $consent['parent_guardian_consented'] ? 'ya' : 'tidak' ?></dd></div>
        <div><dt>Nama orang tua/wali</dt><dd><?= esc($consent['guardian_name'] ?? '—') ?></dd></div>
        <div><dt>Persetujuan dicabut</dt><dd><?= esc(fmt_date($consent['withdrawn_at'], false, 'id')) ?></dd></div>
      </dl>
    <?php endif ?>
  </section>

  <section class="panel">
    <h2 class="panel-title"><?= icon('key') ?> Literasi keamanan digital</h2>
    <dl class="detail-grid">
      <div><dt>Syarat sandi yang langsung dipenuhi</dt><dd class="num"><?= $criteria === null ? '—' : esc($criteria) . ' dari 5' ?></dd></div>
      <div><dt>Mencoba sandi yang terlalu lemah</dt><dd class="num"><?= esc($participant['pw_weak_submit_count']) ?> kali</dd></div>
      <div><dt>Terakhir ganti sandi</dt><dd><?= esc(fmt_date($participant['password_changed_at'], false, 'id')) ?></dd></div>
      <div><dt>Harus membuat sandi baru</dt><dd><?= $participant['must_change_password'] ? 'ya (setelah guru membuat sandi sementara)' : 'tidak' ?></dd></div>
    </dl>
  </section>

  <section class="panel">
    <h2 class="panel-title"><?= icon('chart') ?> Capaian</h2>
    <dl class="detail-grid">
      <div><dt>Tantangan selesai</dt><dd class="num"><?= esc($profile['completed_nodes']) ?></dd></div>
      <div><dt>Rata-rata skor wilayah</dt><dd class="num"><?= esc(fmt_num($profile['total_score'], 1, 'id')) ?></dd></div>
      <div><dt>Total lama bermain</dt><dd class="num"><?= esc(ms_to_human($profile['total_duration_ms'])) ?></dd></div>
      <div><dt>Mengubah jawaban</dt><dd class="num"><?= esc($profile['answer_changes']) ?> kali</dd></div>
      <div><dt>Petunjuk dibuka</dt><dd class="num"><?= esc($profile['hint_uses']) ?></dd></div>
      <div><dt>Tantangan tersulit</dt><dd><?= $hardest === null ? '—' : esc(($nodeTitles[$hardest['node_id']] ?? '#' . $hardest['node_id']) . ' (tepat sejak awal ' . fmt_pct($hardest['first_pass_accuracy'], false, 0) . ')') ?></dd></div>
      <div><dt>Pretest → posttest</dt><dd class="num"><?= $prePost['delta'] === null ? '—' : esc(fmt_num($prePost['pretest'], 1, 'id') . ' → ' . fmt_num($prePost['posttest'], 1, 'id')) ?></dd></div>
      <div><dt>Suara diputar</dt><dd class="num"><?= esc($profile['audio']['total_plays']) ?> kali<?php if (! empty($profile['audio']['total_autoplays'])): ?> <small class="muted">+ <?= esc($profile['audio']['total_autoplays']) ?> kali otomatis</small><?php endif ?></dd></div>
    </dl>
  </section>
</div>

<div class="chart-grid">
  <?= component('admin-chart', [
      'id'       => 'chart-participant-levels',
      'title'    => 'Skor per wilayah',
      'type'     => 'bar',
      'data'     => \App\Libraries\ChartData::bars(array_map(static fn ($level): array => [
          'label' => $level->text('name', 'id'),
          'value' => $profile['level_scores'][$level->id] ?? null,
      ], $levels), 'Skor', 100),
      'fallback' => component('partials/bar-list', ['max' => 100, 'rows' => array_map(
          static fn ($level): array => [
              'label'   => $level->text('name', 'id'),
              'value'   => $profile['level_scores'][$level->id] ?? 0,
              'display' => isset($profile['level_scores'][$level->id]) ? fmt_num($profile['level_scores'][$level->id], 1, 'id') : '—',
          ],
          $levels,
      )]),
  ]) ?>
  <?= component('admin-chart', [
      'id'       => 'chart-participant-indicators',
      'title'    => 'Persentase benar per indikator',
      'type'     => 'bar',
      'data'     => \App\Libraries\ChartData::bars(array_map(static fn (array $row): array => [
          'label' => (string) ($row['name'] ?? $row['code']),
          'value' => $row['evidence_count'] > 0 ? $row['mastery_ratio'] * 100 : null,
      ], array_values($profile['indicators'])), 'Persentase benar (%)', 100),
      'fallback' => component('partials/bar-list', ['max' => 1, 'rows' => array_map(
          static fn (array $row): array => [
              'label'   => (string) ($row['name'] ?? $row['code']),
              'value'   => $row['mastery_ratio'],
              'display' => fmt_pct($row['mastery_ratio'], true) . ' · ' . $row['correct_count'] . ' dari ' . $row['evidence_count'] . ' jawaban',
          ],
          array_values($profile['indicators']),
      )]),
  ]) ?>
</div>

<section class="panel">
  <h2 class="panel-title"><?= icon('clock') ?> Sesi bermain</h2>
  <?= component('admin-table', [
      'rows'         => $sessions,
      'emptyMessage' => 'Peserta ini belum pernah bermain.',
      'columns'      => [
          'phase_code'      => ['label' => 'Fase', 'format' => 'label', 'group' => 'phase'],
          'status'          => ['label' => 'Status', 'format' => 'badge'],
          'locale'          => ['label' => 'Bahasa', 'format' => 'label', 'group' => 'locale'],
          'started_at'      => ['label' => 'Mulai', 'format' => 'datetime'],
          'last_active_at'  => ['label' => 'Terakhir aktif', 'format' => 'datetime'],
          'duration_ms'     => ['label' => 'Lama bermain', 'format' => 'ms'],
          'completed_nodes' => ['label' => 'Tantangan selesai', 'format' => 'num'],
          'total_score'     => ['label' => 'Skor', 'format' => 'num', 'decimals' => 1],
          'id'              => ['label' => '', 'render' => static fn (array $r): string => '<span class="actions-cell">'
              . '<a class="btn btn-quiet btn-sm" href="' . base_url('admin/sesi/' . $r['id']) . '">Lihat</a>'
              . '<a class="btn btn-quiet btn-sm" href="' . base_url('admin/sesi/' . $r['id'] . '/event') . '">Catatan aktivitas</a></span>'],
      ],
  ]) ?>
</section>

<section class="panel">
  <h2 class="panel-title"><?= icon('download') ?> Laporan siswa (PDF)</h2>
  <p class="muted">Ringkasan satu halaman untuk siswa ini: skor per wilayah, capaian per indikator, dan cara membacanya. Laporan yang dibuat dari akun guru selalu tanpa nama (hanya kode peserta).</p>
  <form method="post" action="<?= base_url('admin/ekspor/pdf') ?>" class="inline-form">
    <?= csrf_field() ?>
    <input type="hidden" name="participant_id" value="<?= (int) $participant['id'] ?>">
    <?php if (session('staff_role') === 'admin'): ?>
      <label class="check"><input type="checkbox" name="anonymized" value="1" checked> <span>Sembunyikan nama siswa</span></label>
    <?php endif ?>
    <button class="btn btn-ghost btn-sm" type="submit"><?= icon('download') ?> Buat laporan PDF</button>
  </form>
</section>

<section class="form-section danger-zone">
  <h2><?= icon('key') ?> Siswa lupa kata sandi?</h2>
  <p class="muted">Buat sandi sementara untuk siswa ini. Sandi hanya ditampilkan sekali, dan siswa harus membuat sandi baru saat masuk berikutnya.</p>
  <form method="post" action="<?= base_url('admin/peserta/' . $participant['id'] . '/reset-sandi') ?>" class="form">
    <?= csrf_field() ?>
    <div class="field">
      <label for="confirm">Ketik <code>RESET</code> (huruf besar) untuk memastikan</label>
      <input type="text" id="confirm" name="confirm" class="confirm-input" required pattern="RESET" autocomplete="off" spellcheck="false">
    </div>
    <div class="form-actions">
      <button class="btn btn-danger" type="submit"><?= icon('key') ?> Buat sandi sementara</button>
    </div>
  </form>
  <?php if (session('staff_role') === 'admin'): ?>
    <p class="muted">
      Siswa atau orang tua/wali meminta datanya dihapus?
      <a href="<?= base_url('admin/tata-kelola?participant_id=' . $participant['id']) ?>#hapus"><?= icon('trash') ?> Hapus data siswa ini</a>
    </p>
  <?php endif ?>
</section>
<?= $this->endSection() ?>
