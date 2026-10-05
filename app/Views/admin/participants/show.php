<?php
/**
 * Profil peserta — `/admin/peserta/{id}` → ParticipantController::show
 *
 * Demografi, nama pengguna, persetujuan siswa (assented_at), literasi
 * keamanan digital, ringkasan data mentah pengerjaan, perangkat, sesi, dan
 * tautan ke linimasa event tiap sesi. Tanpa skor dan chart (perombakan
 * kelas 5). Reset sandi wajib diketik `RESET`.
 *
 * @var array<string, mixed>              $participant bentuk aman, tanpa password_hash
 * @var array<string, mixed>              $profile     AnalyticsService::participantProfile()
 * @var list<array<string, mixed>>        $devices     device_logs siswa ini
 * @var list<array<string, mixed>>        $sessions
 * @var list<App\Entities\Level>          $levels
 * @var array<int, string>                $nodeTitles
 */
$criteria = $participant['pw_first_submit_criteria'];
$region   = implode(', ', array_filter([$participant['district_name'], $participant['province_name'], $participant['country_name']]));
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title'   => $participant['display_name'] ?: $participant['username'],
    'eyebrow' => 'Profil siswa · ' . $participant['participant_code'],
    'lead'    => 'Nama pengguna: ' . $participant['username'] . ' · mendaftar ' . fmt_date($participant['created_at'], false, 'id'),
    'actions' => '<a class="btn btn-quiet btn-sm" href="' . base_url('admin/peserta') . '">' . icon('left') . ' Daftar siswa</a>',
]) ?>
<?= $this->include('partials/flash') ?>

<div class="split-grid">
  <section class="panel">
    <h2 class="panel-title"><?= icon('user') ?> Data diri</h2>
    <dl class="detail-grid">
      <div><dt>Kode siswa</dt><dd><code><?= esc($participant['participant_code']) ?></code></dd></div>
      <div><dt>Nama pengguna</dt><dd><?= esc($participant['username']) ?></dd></div>
      <div><dt>Nama</dt><dd><?= esc($participant['display_name'] ?? '—') ?></dd></div>
      <div><dt>Umur</dt><dd><?= esc($participant['age'] ?? '—') ?></dd></div>
      <div><dt>Jenis kelamin</dt><dd><?= esc(admin_label('gender', $participant['gender'] ?? null)) ?></dd></div>
      <div><dt>Kelas</dt><dd><?= esc(class_label($participant['class_level'] ?? null)) ?></dd></div>
      <div><dt>Sekolah</dt><dd><?= esc($participant['school_name'] ?? '—') ?></dd></div>
      <div><dt>Daerah</dt><dd><?= esc($region !== '' ? $region : '—') ?></dd></div>
    </dl>
  </section>

  <section class="panel">
    <h2 class="panel-title"><?= icon('shield') ?> Persetujuan ikut bermain</h2>
    <dl class="detail-grid">
      <div><dt>Setuju ikut bermain</dt><dd><?= empty($participant['assented_at']) ? 'tidak tercatat' : 'ya · ' . esc(fmt_date($participant['assented_at'], true, 'id')) ?></dd></div>
    </dl>
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
    <h2 class="panel-title"><?= icon('chart') ?> Ringkasan data mentah</h2>
    <dl class="detail-grid">
      <div><dt>Pengerjaan selesai</dt><dd class="num"><?= esc($profile['completed_attempts']) ?></dd></div>
      <div><dt>Berhasil pada Percobaan Pertama</dt><dd class="num"><?= esc($profile['first_pass_correct']) ?> dari <?= esc($profile['scorable_items']) ?> soal</dd></div>
      <div><dt>Benar di akhir</dt><dd class="num"><?= esc($profile['final_correct']) ?> dari <?= esc($profile['scorable_items']) ?> soal</dd></div>
      <div><dt>Bintang</dt><dd class="num"><?= esc($profile['stars']) ?></dd></div>
      <div><dt>Total lama bermain</dt><dd class="num"><?= esc(ms_to_human($profile['total_duration_ms'])) ?></dd></div>
      <div><dt>Mengubah jawaban</dt><dd class="num"><?= esc($profile['answer_changes']) ?> kali</dd></div>
      <div><dt>Petunjuk dibuka</dt><dd class="num"><?= esc($profile['hint_uses']) ?></dd></div>
      <div><dt>Suara diputar</dt><dd class="num"><?= esc($profile['audio']['total_plays']) ?> kali<?php if (! empty($profile['audio']['total_autoplays'])): ?> <small class="muted">+ <?= esc($profile['audio']['total_autoplays']) ?> kali otomatis</small><?php endif ?></dd></div>
    </dl>
  </section>
</div>

<?php if ($devices !== []): ?>
<section class="panel">
  <h2 class="panel-title"><?= icon('grid') ?> Perangkat</h2>
  <?= component('admin-table', [
      'rows'    => $devices,
      'columns' => [
          'created_at'      => ['label' => 'Waktu', 'format' => 'datetime'],
          'via'             => 'Lewat',
          'device_type'     => 'Jenis',
          'device_brand'    => 'Merek',
          'device_model'    => 'Model',
          'os_name'         => 'Sistem operasi',
          'browser_name'    => 'Peramban',
          'screen_w'        => ['label' => 'Layar', 'render' => static fn (array $r): string => $r['screen_w'] === null ? '—' : $r['screen_w'] . ' × ' . $r['screen_h']],
          'corrected'       => 'Koreksi',
      ],
  ]) ?>
</section>
<?php endif ?>

<section class="panel">
  <h2 class="panel-title"><?= icon('clock') ?> Sesi bermain</h2>
  <?= component('admin-table', [
      'rows'         => $sessions,
      'emptyMessage' => 'Siswa ini belum pernah bermain.',
      'columns'      => [
          'status'          => ['label' => 'Status', 'format' => 'badge'],
          'locale'          => ['label' => 'Bahasa', 'format' => 'label', 'group' => 'locale'],
          'started_at'      => ['label' => 'Mulai', 'format' => 'datetime'],
          'last_active_at'  => ['label' => 'Terakhir aktif', 'format' => 'datetime'],
          'duration_ms'     => ['label' => 'Lama bermain', 'format' => 'ms'],
          'completed_nodes' => ['label' => 'Tantangan selesai', 'format' => 'num'],
          'total_stars'     => ['label' => 'Bintang', 'format' => 'num'],
          'id'              => ['label' => '', 'render' => static fn (array $r): string => '<span class="actions-cell">'
              . '<a class="btn btn-quiet btn-sm" href="' . base_url('admin/sesi/' . $r['id']) . '">Lihat</a>'
              . '<a class="btn btn-quiet btn-sm" href="' . base_url('admin/sesi/' . $r['id'] . '/event') . '">Catatan aktivitas</a></span>'],
      ],
  ]) ?>
</section>

<section class="panel">
  <h2 class="panel-title"><?= icon('download') ?> Laporan siswa (PDF)</h2>
  <p class="muted">Ringkasan satu halaman untuk siswa ini: data mentah pengerjaan per tantangan. Laporan yang dibuat dari akun guru selalu tanpa nama (hanya kode siswa).</p>
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
