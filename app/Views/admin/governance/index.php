<?php
/**
 * Tata kelola data — `/admin/tata-kelola` → GovernanceController::index
 *
 * Penghapusan selalu dua langkah: (1) pratinjau menghitung baris terdampak
 * per tabel tanpa menghapus apa pun; (2) eksekusi hanya setelah admin
 * mengetik kata konfirmasi. Pratinjau yang menunggu tampil sebagai kartu di
 * atas riwayat. `?participant_id=` mengisi awal cakupan dari halaman peserta.
 *
 * Di layar, "retensi" disebut perawatan data otomatis dan tabel database
 * disebut jenis data (admin_label('dataTable')).
 *
 * @var list<array<string, mixed>>  $requests
 * @var string                      $confirmWord
 * @var int                         $idleMinutes
 * @var int                         $attemptHours
 * @var int                         $sessionDays
 * @var int                         $exportRetention
 * @var list<string>                $phases
 * @var array<string, mixed>|null   $retention hasil retensi terakhir (flashdata)
 */
$prefill = [
    'participant_id' => (int) (service('request')->getGet('participant_id') ?? 0),
    'session_id'     => (int) (service('request')->getGet('session_id') ?? 0),
    'study_id'       => (int) (service('request')->getGet('study_id') ?? 0),
];
$scopeNames  = ['participant_id' => 'Peserta', 'session_id' => 'Sesi', 'study_id' => 'Studi'];
$scopeHelp   = [
    'participant_id' => 'Satu siswa beserta semua sesi dan jawabannya.',
    'session_id'     => 'Satu sesi bermain saja.',
    'study_id'       => 'Seluruh data satu studi (bisa dipersempit di bawah).',
];
$decode      = static function (array $request): array {
    $data = json_decode((string) ($request['scope_json'] ?? ''), true);

    return is_array($data) ? $data : [];
};
$scopeText = static function (array $scope) use ($scopeNames): string {
    $parts = [];

    foreach ($scopeNames as $key => $label) {
        if (isset($scope[$key])) {
            $parts[] = $label . ' nomor ' . (int) $scope[$key];
        }
    }

    if (isset($scope['phase_code'])) {
        $parts[] = 'fase ' . admin_label('phase', (string) $scope['phase_code']);
    }

    if (isset($scope['date_from']) || isset($scope['date_to'])) {
        $parts[] = 'sesi ' . ($scope['date_from'] ?? 'awal') . ' s.d. ' . ($scope['date_to'] ?? 'sekarang');
    }

    return $parts === [] ? '—' : implode(', ', $parts);
};
$pending = array_values(array_filter($requests, static fn (array $row): bool => $row['status'] === 'preview'));
$history = array_values(array_filter($requests, static fn (array $row): bool => $row['status'] !== 'preview'));
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title'   => 'Hapus data',
    'eyebrow' => 'Pengelolaan · data penelitian',
    'lead'    => 'Satu-satunya tempat untuk menghapus data siswa. Penghapusan selalu dua langkah — hitung dulu, baru hapus — dan setiap langkahnya tercatat di riwayat aktivitas.',
]) ?>
<ul class="sub-nav">
  <li><a href="<?= base_url('admin/tata-kelola') ?>" aria-current="page"><?= icon('shield') ?> Hapus data</a></li>
  <li><a href="<?= base_url('admin/tata-kelola/audit') ?>"><?= icon('list') ?> Riwayat aktivitas</a></li>
</ul>
<?= $this->include('partials/flash') ?>

<?php foreach ($pending as $request): ?>
  <?php $data = $decode($request); $counts = array_filter((array) ($data['counts'] ?? []), static fn ($n): bool => (int) $n > 0); ?>
  <section class="form-section danger-zone" aria-labelledby="req-<?= (int) $request['id'] ?>">
    <header class="level-card-head">
      <div>
        <span class="eyebrow">Permintaan nomor <?= (int) $request['id'] ?> · <?= esc(fmt_date($request['created_at'], true, 'id')) ?><?= ($data['origin'] ?? null) === 'retention' ? ' · dibuat otomatis karena data melewati batas lama penyimpanan' : '' ?></span>
        <h2 id="req-<?= (int) $request['id'] ?>"><?= icon('warn') ?> <?= esc($scopeText((array) ($data['scope'] ?? []))) ?></h2>
      </div>
      <span class="badge <?= $request['mode'] === 'hard' ? 'is-bad' : 'is-warn' ?>"><?= $request['mode'] === 'hard' ? 'hapus selamanya' : 'sembunyikan' ?></span>
    </header>

    <?php if ($request['reason']): ?>
      <p><b>Alasan:</b> <?= esc($request['reason']) ?></p>
    <?php endif ?>

    <?php if ($counts === []): ?>
      <p class="alert alert-info"><?= icon('info') ?> Tidak ada data yang cocok dengan pilihan ini. Batalkan permintaan ini.</p>
    <?php else: ?>
      <div class="table-wrap">
        <table class="data-table">
          <caption class="visually-hidden">Jumlah catatan yang akan terdampak</caption>
          <thead><tr><th scope="col">Jenis data</th><th scope="col" class="is-num">Jumlah catatan</th></tr></thead>
          <tbody>
            <?php foreach ($counts as $table => $count): ?>
              <tr><td><?= esc(admin_label('dataTable', (string) $table)) ?></td><td class="is-num"><?= esc(fmt_num($count)) ?></td></tr>
            <?php endforeach ?>
          </tbody>
          <tfoot><tr><th scope="row">Total</th><td class="is-num"><b><?= esc(fmt_num($request['affected_count'] ?? 0)) ?></b></td></tr></tfoot>
        </table>
      </div>
      <p class="muted">
        <?php if ($request['mode'] === 'hard'): ?>
          <b>Hapus selamanya</b>: semua catatan di atas dihapus dan <b>tidak dapat dikembalikan</b>.
        <?php else: ?>
          <b>Sembunyikan</b>: siswa dan catatan aktivitasnya disembunyikan dari panel, tetapi jawaban dan skornya tetap dipakai untuk rekap penelitian. Jumlah yang benar-benar berubah bisa lebih sedikit dari tabel ini.
        <?php endif ?>
      </p>
    <?php endif ?>

    <div class="form-actions">
      <?php if ($counts !== []): ?>
        <form method="post" action="<?= base_url('admin/tata-kelola/hapus/' . $request['id'] . '/jalankan') ?>" class="inline-form">
          <?= csrf_field() ?>
          <label for="confirm-<?= (int) $request['id'] ?>">Ketik <code><?= esc($confirmWord) ?></code> untuk memastikan</label>
          <input type="text" id="confirm-<?= (int) $request['id'] ?>" name="confirm" class="confirm-input" required pattern="<?= esc($confirmWord, 'attr') ?>" autocomplete="off" spellcheck="false">
          <button class="btn btn-danger btn-sm" type="submit"><?= icon('trash') ?> Hapus sekarang</button>
        </form>
      <?php endif ?>
      <form method="post" action="<?= base_url('admin/tata-kelola/hapus/' . $request['id'] . '/batal') ?>" class="inline-form">
        <?= csrf_field() ?>
        <button class="btn btn-quiet btn-sm" type="submit">Batalkan permintaan ini</button>
      </form>
    </div>
  </section>
<?php endforeach ?>

<form method="post" action="<?= base_url('admin/tata-kelola/hapus/pratinjau') ?>" class="form-section" id="hapus">
  <?= csrf_field() ?>
  <h2><?= icon('search') ?> Langkah 1 — hitung data yang akan dihapus</h2>
  <p class="muted">Isi <b>salah satu</b> kotak di bawah. Langkah ini hanya menghitung; belum ada data yang dihapus. Bila lebih dari satu kotak terisi, yang dipakai berurutan: peserta, sesi, lalu studi.</p>

  <div class="form-grid">
    <?php foreach ($scopeNames as $key => $label) : ?>
      <div class="field">
        <label for="<?= $key ?>">Nomor <?= esc(strtolower($label)) ?></label>
        <input type="number" id="<?= $key ?>" name="<?= $key ?>" min="1" inputmode="numeric" value="<?= $prefill[$key] > 0 ? $prefill[$key] : '' ?>">
        <p class="field-help"><?= esc($scopeHelp[$key]) ?></p>
      </div>
    <?php endforeach ?>
  </div>
  <p class="field-help">Nomor peserta dan nomor sesi terlihat di alamat halamannya, misalnya /admin/peserta/<b>42</b>. Cara termudah: buka profil peserta, lalu tekan “Hapus data siswa ini”.</p>

  <fieldset class="repeat-row">
    <legend>Persempit data studi (tidak wajib)</legend>
    <p class="field-help">Hanya dipakai bila Anda mengisi nomor studi: batasi ke satu fase dan/atau sesi yang dimulai pada rentang tanggal tertentu.</p>
    <div class="form-grid">
      <div class="field">
        <label for="phase_code">Fase</label>
        <select id="phase_code" name="phase_code">
          <option value="">Semua fase</option>
          <?php foreach ($phases as $phase): ?>
            <option value="<?= esc($phase, 'attr') ?>"><?= esc(admin_label('phase', $phase)) ?></option>
          <?php endforeach ?>
        </select>
      </div>
      <div class="field">
        <label for="date_from">Sesi mulai dari tanggal</label>
        <input type="date" id="date_from" name="date_from">
      </div>
      <div class="field">
        <label for="date_to">Sampai tanggal</label>
        <input type="date" id="date_to" name="date_to">
      </div>
    </div>
  </fieldset>

  <fieldset class="repeat-row">
    <legend>Cara menghapus</legend>
    <label class="check">
      <input type="radio" name="mode" value="soft" checked>
      <span><b>Sembunyikan</b><span class="cell-sub">Siswa tidak lagi tampil di panel dan catatan aktivitasnya ditandai terhapus, tetapi jawaban dan skornya tetap dipakai untuk rekap penelitian.</span></span>
    </label>
    <label class="check">
      <input type="radio" name="mode" value="hard">
      <span><b>Hapus selamanya</b><span class="cell-sub">Semua data terkait dihapus dan tidak dapat dikembalikan. Pakai untuk permintaan penghapusan dari siswa atau orang tua/wali.</span></span>
    </label>
  </fieldset>

  <div class="field">
    <label for="reason">Alasan</label>
    <textarea id="reason" name="reason" rows="2" placeholder="Mis. permintaan orang tua/wali tanggal …, atau data uji coba"></textarea>
  </div>

  <div class="form-actions">
    <button class="btn btn-primary" type="submit"><?= icon('search') ?> Hitung data yang akan dihapus</button>
  </div>
</form>

<section class="panel">
  <h2 class="panel-title"><?= icon('clock') ?> Riwayat permintaan penghapusan</h2>
  <?= component('admin-table', [
      'caption'      => 'Riwayat permintaan penghapusan',
      'emptyMessage' => 'Belum ada permintaan yang dijalankan atau dibatalkan.',
      'rows'         => $history,
      'rowClass'     => static fn (array $row): string => $row['status'] === 'cancelled' ? 'is-muted' : '',
      'columns'      => [
          'id'             => ['label' => 'No.', 'format' => 'num'],
          'created_at'     => ['label' => 'Dibuat', 'format' => 'datetime'],
          'scope'          => ['label' => 'Data', 'render' => static fn (array $row): string => esc($scopeText((array) ($decode($row)['scope'] ?? []))) . ($row['reason'] ? '<span class="cell-sub">' . esc($row['reason']) . '</span>' : '')],
          'mode'           => ['label' => 'Cara', 'render' => static fn (array $row): string => $row['mode'] === 'hard' ? 'hapus selamanya' : 'sembunyikan'],
          'status'         => ['label' => 'Status', 'render' => static fn (array $row): string => '<span class="badge is-' . esc($row['status'], 'attr') . '">' . esc(admin_label('deletionStatus', (string) $row['status'])) . '</span>'],
          'affected_count' => ['label' => 'Jumlah catatan', 'format' => 'num'],
          'executed_at'    => ['label' => 'Dijalankan', 'format' => 'datetime'],
      ],
  ]) ?>
</section>

<section class="panel">
  <h2 class="panel-title"><?= icon('replay') ?> Perawatan data otomatis</h2>
  <?php if (is_array($retention)): ?>
    <div class="alert alert-ok" role="status">
      <p><?= icon('check') ?> Perawatan data dijalankan <?= esc(fmt_date($retention['at'], true, 'id')) ?>:</p>
      <ul>
        <li><?= esc(fmt_num($retention['stale_sessions'])) ?> sesi yang lama tidak aktif ditandai jeda</li>
        <li><?= esc(fmt_num($retention['abandoned_attempts'] ?? 0)) ?> tantangan yang terbuka terlalu lama ditandai ditinggalkan</li>
        <li><?= esc(fmt_num($retention['abandoned_sessions'] ?? 0)) ?> sesi jeda yang sudah lama ditandai ditinggalkan</li>
        <li><?= esc(fmt_num($retention['expired_exports'])) ?> berkas unduhan lama dibuang</li>
        <li><?= esc(fmt_num(count($retention['retention_previews'] ?? []))) ?> permintaan penghapusan dibuat karena data melewati batas lama penyimpanan</li>
      </ul>
      <?php foreach ($retention['warnings'] ?? [] as $warning): ?>
        <p class="alert alert-warn"><?= icon('warn') ?> <?= esc($warning) ?></p>
      <?php endforeach ?>
    </div>
  <?php endif ?>
  <p class="muted">Sistem merapikan data secara otomatis setiap hari:</p>
  <ul class="muted">
    <li>Sesi yang tidak aktif lebih dari <?= esc($idleMinutes) ?> menit ditandai <b>jeda</b>; siswa tetap dapat melanjutkannya.</li>
    <li>Tantangan yang dibuka tetapi tidak disentuh lebih dari <?= esc($attemptHours) ?> jam ditandai <b>ditinggalkan</b> (skor 0); jawabannya tetap tersimpan.</li>
    <li>Sesi jeda yang tidak disentuh lebih dari <?= esc($sessionDays) ?> hari ditandai <b>ditinggalkan</b>.</li>
    <li>Berkas unduhan data yang lebih tua dari <?= esc($exportRetention) ?> hari dibuang.</li>
    <li>Data studi yang melewati batas lama penyimpanan (diatur di Pengaturan penelitian) <b>tidak</b> dihapus otomatis: sistem hanya membuat permintaan penghapusan di atas, dan Anda yang memutuskan.</li>
  </ul>
  <p class="muted">Tombol di bawah menjalankan perawatan ini sekarang juga, tanpa menunggu jadwal harian.</p>
  <form method="post" action="<?= base_url('admin/tata-kelola/retensi') ?>" class="inline-form">
    <?= csrf_field() ?>
    <button class="btn btn-ghost btn-sm" type="submit"><?= icon('play') ?> Jalankan perawatan data sekarang</button>
  </form>
</section>
<?= $this->endSection() ?>
