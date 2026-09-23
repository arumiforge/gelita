<?php
/**
 * Tata kelola data — `/admin/tata-kelola` → GovernanceController::index
 *
 * Penghapusan selalu dua langkah: (1) pratinjau menghitung baris terdampak
 * per tabel tanpa menghapus apa pun; (2) eksekusi hanya setelah admin
 * mengetik kata konfirmasi. Pratinjau yang menunggu tampil sebagai kartu di
 * atas riwayat. `?participant_id=` mengisi awal cakupan dari halaman peserta.
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
$tableNames = [
    'game_event_logs'      => 'Log peristiwa',
    'audio_usage_events'   => 'Pemakaian audio',
    'item_responses'       => 'Jawaban butir',
    'challenge_attempts'   => 'Percobaan tantangan',
    'session_progress'     => 'Progres sesi',
    'participant_feedback' => 'Refleksi',
    'game_sessions'        => 'Sesi',
    'participant_consents' => 'Persetujuan',
    'participants'         => 'Peserta',
];
$scopeNames  = ['participant_id' => 'Peserta', 'session_id' => 'Sesi', 'study_id' => 'Studi'];
$statusNames = ['preview' => 'pratinjau', 'executed' => 'dieksekusi', 'cancelled' => 'dibatalkan'];
$decode      = static function (array $request): array {
    $data = json_decode((string) ($request['scope_json'] ?? ''), true);

    return is_array($data) ? $data : [];
};
$scopeText = static function (array $scope) use ($scopeNames): string {
    $parts = [];

    foreach ($scopeNames as $key => $label) {
        if (isset($scope[$key])) {
            $parts[] = $label . ' #' . (int) $scope[$key];
        }
    }

    if (isset($scope['phase_code'])) {
        $parts[] = 'fase ' . $scope['phase_code'];
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
    'title'   => 'Tata kelola data',
    'eyebrow' => 'Pengelolaan · data penelitian',
    'lead'    => 'Satu-satunya tempat menghapus data penelitian. Setiap langkah — pratinjau, eksekusi, pembatalan — tercatat di audit log.',
]) ?>
<ul class="sub-nav">
  <li><a href="<?= base_url('admin/tata-kelola') ?>" aria-current="page"><?= icon('shield') ?> Penghapusan & retensi</a></li>
  <li><a href="<?= base_url('admin/tata-kelola/audit') ?>"><?= icon('list') ?> Audit log</a></li>
</ul>
<?= $this->include('partials/flash') ?>

<?php foreach ($pending as $request): ?>
  <?php $data = $decode($request); $counts = array_filter((array) ($data['counts'] ?? []), static fn ($n): bool => (int) $n > 0); ?>
  <section class="form-section danger-zone" aria-labelledby="req-<?= (int) $request['id'] ?>">
    <header class="level-card-head">
      <div>
        <span class="eyebrow">Pratinjau #<?= (int) $request['id'] ?> · <?= esc(fmt_date($request['created_at'], true, 'id')) ?><?= ($data['origin'] ?? null) === 'retention' ? ' · dibuat otomatis oleh retensi' : '' ?></span>
        <h2 id="req-<?= (int) $request['id'] ?>"><?= icon('warn') ?> <?= esc($scopeText((array) ($data['scope'] ?? []))) ?></h2>
      </div>
      <span class="badge <?= $request['mode'] === 'hard' ? 'is-bad' : 'is-warn' ?>"><?= $request['mode'] === 'hard' ? 'hapus permanen' : 'tandai terhapus' ?></span>
    </header>

    <?php if ($request['reason']): ?>
      <p><b>Alasan:</b> <?= esc($request['reason']) ?></p>
    <?php endif ?>

    <?php if ($counts === []): ?>
      <p class="alert alert-info"><?= icon('info') ?> Tidak ada baris yang cocok dengan cakupan ini. Batalkan pratinjau.</p>
    <?php else: ?>
      <div class="table-wrap">
        <table class="data-table">
          <caption class="visually-hidden">Baris terdampak per tabel</caption>
          <thead><tr><th scope="col">Data</th><th scope="col">Tabel</th><th scope="col" class="is-num">Baris</th></tr></thead>
          <tbody>
            <?php foreach ($counts as $table => $count): ?>
              <tr><td><?= esc($tableNames[$table] ?? $table) ?></td><td><code><?= esc($table) ?></code></td><td class="is-num"><?= esc(fmt_num($count)) ?></td></tr>
            <?php endforeach ?>
          </tbody>
          <tfoot><tr><th scope="row" colspan="2">Total</th><td class="is-num"><b><?= esc(fmt_num($request['affected_count'] ?? 0)) ?></b></td></tr></tfoot>
        </table>
      </div>
      <p class="muted">
        <?php if ($request['mode'] === 'hard'): ?>
          Mode permanen menghapus semua baris di atas, dari anak ke induk. Tidak dapat dikembalikan.
        <?php else: ?>
          Mode tandai-terhapus hanya menandai log peristiwa dan peserta; jawaban &amp; skor dipertahankan untuk agregat. Jumlah yang benar-benar berubah bisa lebih kecil dari tabel ini.
        <?php endif ?>
      </p>
    <?php endif ?>

    <div class="form-actions">
      <?php if ($counts !== []): ?>
        <form method="post" action="<?= base_url('admin/tata-kelola/hapus/' . $request['id'] . '/jalankan') ?>" class="inline-form">
          <?= csrf_field() ?>
          <label for="confirm-<?= (int) $request['id'] ?>">Ketik <code><?= esc($confirmWord) ?></code></label>
          <input type="text" id="confirm-<?= (int) $request['id'] ?>" name="confirm" class="confirm-input" required pattern="<?= esc($confirmWord, 'attr') ?>" autocomplete="off" spellcheck="false">
          <button class="btn btn-danger btn-sm" type="submit"><?= icon('trash') ?> Eksekusi penghapusan</button>
        </form>
      <?php endif ?>
      <form method="post" action="<?= base_url('admin/tata-kelola/hapus/' . $request['id'] . '/batal') ?>" class="inline-form">
        <?= csrf_field() ?>
        <button class="btn btn-quiet btn-sm" type="submit">Batalkan pratinjau</button>
      </form>
    </div>
  </section>
<?php endforeach ?>

<form method="post" action="<?= base_url('admin/tata-kelola/hapus/pratinjau') ?>" class="form-section" id="hapus">
  <?= csrf_field() ?>
  <h2><?= icon('search') ?> Langkah 1 — pratinjau penghapusan</h2>
  <p class="muted">Isi <b>satu</b> cakupan. Bila lebih dari satu terisi, yang dipakai: peserta, lalu sesi, lalu studi. Pratinjau tidak mengubah data apa pun.</p>

  <div class="form-grid">
    <?php foreach ($scopeNames as $key => $label): ?>
      <div class="field">
        <label for="<?= $key ?>">ID <?= esc(strtolower($label)) ?></label>
        <input type="number" id="<?= $key ?>" name="<?= $key ?>" min="1" inputmode="numeric" value="<?= $prefill[$key] > 0 ? $prefill[$key] : '' ?>">
      </div>
    <?php endforeach ?>
  </div>
  <p class="field-help">ID peserta dan ID sesi tertera di URL halaman detailnya (mis. /admin/peserta/<b>42</b>).</p>

  <fieldset class="repeat-row">
    <legend>Penyempit cakupan studi (opsional)</legend>
    <p class="field-help">Hanya dipakai bila cakupannya studi: batasi ke satu fase dan/atau sesi yang dimulai dalam rentang tanggal.</p>
    <div class="form-grid">
      <div class="field">
        <label for="phase_code">Fase</label>
        <select id="phase_code" name="phase_code">
          <option value="">Semua fase</option>
          <?php foreach ($phases as $phase): ?>
            <option value="<?= esc($phase, 'attr') ?>"><?= esc($phase) ?></option>
          <?php endforeach ?>
        </select>
      </div>
      <div class="field">
        <label for="date_from">Sesi mulai dari</label>
        <input type="date" id="date_from" name="date_from">
      </div>
      <div class="field">
        <label for="date_to">Sesi mulai sampai</label>
        <input type="date" id="date_to" name="date_to">
      </div>
    </div>
  </fieldset>

  <fieldset class="repeat-row">
    <legend>Mode penghapusan</legend>
    <label class="check">
      <input type="radio" name="mode" value="soft" checked>
      <span><b>Tandai terhapus</b><span class="cell-sub">Peserta disembunyikan dan log peristiwa ditandai; jawaban &amp; skor tetap untuk agregat penelitian.</span></span>
    </label>
    <label class="check">
      <input type="radio" name="mode" value="hard">
      <span><b>Hapus permanen</b><span class="cell-sub">Semua baris terkait dihapus dari database. Pakai untuk permintaan penghapusan dari siswa atau orang tua/wali.</span></span>
    </label>
  </fieldset>

  <div class="field">
    <label for="reason">Alasan</label>
    <textarea id="reason" name="reason" rows="2" placeholder="Mis. permintaan orang tua/wali tanggal …, data uji coba"></textarea>
  </div>

  <div class="form-actions">
    <button class="btn btn-primary" type="submit"><?= icon('search') ?> Hitung baris terdampak</button>
  </div>
</form>

<section class="panel">
  <h2 class="panel-title"><?= icon('clock') ?> Riwayat permintaan</h2>
  <?= component('components/admin-table', [
      'caption'      => 'Riwayat permintaan penghapusan',
      'emptyMessage' => 'Belum ada permintaan yang dieksekusi atau dibatalkan.',
      'rows'         => $history,
      'rowClass'     => static fn (array $row): string => $row['status'] === 'cancelled' ? 'is-muted' : '',
      'columns'      => [
          'id'             => ['label' => '#', 'format' => 'num'],
          'created_at'     => ['label' => 'Dibuat', 'format' => 'datetime'],
          'scope'          => ['label' => 'Cakupan', 'render' => static fn (array $row): string => esc($scopeText((array) ($decode($row)['scope'] ?? []))) . ($row['reason'] ? '<span class="cell-sub">' . esc($row['reason']) . '</span>' : '')],
          'mode'           => ['label' => 'Mode', 'render' => static fn (array $row): string => $row['mode'] === 'hard' ? 'permanen' : 'tandai'],
          'status'         => ['label' => 'Status', 'render' => static fn (array $row): string => '<span class="badge is-' . esc($row['status'], 'attr') . '">' . esc($statusNames[$row['status']] ?? $row['status']) . '</span>'],
          'affected_count' => ['label' => 'Baris', 'format' => 'num'],
          'executed_at'    => ['label' => 'Dieksekusi', 'format' => 'datetime'],
      ],
  ]) ?>
</section>

<section class="panel">
  <h2 class="panel-title"><?= icon('replay') ?> Retensi</h2>
  <?php if (is_array($retention)): ?>
    <div class="alert alert-ok" role="status">
      <p><?= icon('check') ?> Retensi dijalankan <?= esc(fmt_date($retention['at'], true, 'id')) ?>:</p>
      <ul>
        <li><?= esc(fmt_num($retention['stale_sessions'])) ?> sesi menganggur ditandai jeda</li>
        <li><?= esc(fmt_num($retention['abandoned_attempts'] ?? 0)) ?> tantangan menggantung ditandai ditinggalkan</li>
        <li><?= esc(fmt_num($retention['abandoned_sessions'] ?? 0)) ?> sesi jeda lama ditandai ditinggalkan</li>
        <li><?= esc(fmt_num($retention['expired_exports'])) ?> berkas ekspor kedaluwarsa dibuang</li>
        <li><?= esc(fmt_num(count($retention['retention_previews'] ?? []))) ?> pratinjau penghapusan karena masa simpan studi</li>
      </ul>
      <?php foreach ($retention['warnings'] ?? [] as $warning): ?>
        <p class="alert alert-warn"><?= icon('warn') ?> <?= esc($warning) ?></p>
      <?php endforeach ?>
    </div>
  <?php endif ?>
  <ul class="muted">
    <li>Sesi tanpa aktivitas lebih dari <?= esc($idleMinutes) ?> menit ditandai <b>jeda</b> (paused); peserta tetap dapat melanjutkan.</li>
    <li>Tantangan yang terbuka tanpa sentuhan lebih dari <?= esc($attemptHours) ?> jam ditandai <b>ditinggalkan</b> (skor 0); jawabannya tetap tersimpan.</li>
    <li>Sesi jeda yang tidak tersentuh lebih dari <?= esc($sessionDays) ?> hari ditandai <b>ditinggalkan</b>.</li>
    <li>Berkas ekspor yang lebih tua dari <?= esc($exportRetention) ?> hari dibuang dari disk.</li>
    <li>Data studi yang melewati masa simpan (<code>retention_days</code>) <b>tidak</b> dihapus otomatis: retensi membuat pratinjau di atas, dan penghapusan tetap menunggu keputusan Anda.</li>
    <li>Cron harian menjalankan <code>php spark gelita:retention:run</code>; tombol ini menjalankan hal yang sama sekarang.</li>
  </ul>
  <form method="post" action="<?= base_url('admin/tata-kelola/retensi') ?>" class="inline-form">
    <?= csrf_field() ?>
    <button class="btn btn-ghost btn-sm" type="submit"><?= icon('play') ?> Jalankan retensi sekarang</button>
  </form>
</section>
<?= $this->endSection() ?>
