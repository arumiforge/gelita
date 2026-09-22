<?php
/**
 * Ekspor data — `/admin/ekspor` → ExportController::index
 *
 * Filter sama dengan halaman analitik (nama field identik), tetapi dikirim
 * lewat POST. Guru: sekolah terkunci, mode anonim dipaksa server, dan sheet
 * Raw Events tidak tersedia — form hanya mencerminkan aturan itu.
 * Baris dengan status queued/running diperbarui export-status.js (tahap 6)
 * lewat data-export-id; tanpa JavaScript, muat ulang halaman.
 *
 * @var array<string, mixed>        $filters   filter dari query string (dari halaman analitik)
 * @var list<string>                $sheets
 * @var list<string>                $adminOnly
 * @var bool                        $isAdmin
 * @var list<array<string, mixed>>  $recent
 * @var int                         $retention hari
 */
$sheetNotes = [
    'Participants'        => 'Profil peserta (kode, kelas, sekolah, wilayah)',
    'Sessions'            => 'Satu baris per sesi bermain',
    'Levels'              => 'Skor & bintang per wilayah',
    'Challenge Summary'   => 'Ringkasan per percobaan tantangan',
    'Item Responses'      => 'Jawaban per butir, termasuk perubahan jawaban',
    'Raw Events'          => 'Log peristiwa mentah — besar, hanya admin',
    'Audio Usage'         => 'Pemakaian audio & transkrip',
    'Indicators'          => 'Capaian per indikator literasi',
    'Demographic Summary' => 'Ringkasan jumlah per kelompok',
    'Feedback'            => 'Refleksi & rating siswa',
];
$statusNames = ['queued' => 'antre', 'running' => 'diproses', 'done' => 'siap', 'failed' => 'gagal'];
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title'   => 'Ekspor data',
    'eyebrow' => 'Laporan',
    'lead'    => 'Unduh data penelitian sebagai workbook XLSX atau ringkasan PDF. Setiap permintaan dan unduhan tercatat di log audit.',
]) ?>
<?= $this->include('partials/flash') ?>

<form method="post" action="<?= base_url('admin/ekspor/xlsx') ?>" class="form-section">
  <?= csrf_field() ?>
  <h2><?= icon('search') ?> Cakupan data</h2>
  <?= component('components/admin-filter-bar', [
      'filters'       => $filters,
      'filterOptions' => $filterOptions,
      'bare'          => true,
  ]) ?>

  <fieldset class="repeat-row">
    <legend><?= icon('list') ?> Sheet yang disertakan</legend>
    <div class="check-grid">
      <?php foreach ($sheets as $sheet): ?>
        <?php $onlyAdmin = in_array($sheet, $adminOnly, true); ?>
        <?php if ($onlyAdmin && ! $isAdmin) {
            continue;
        } ?>
        <label class="check">
          <input type="checkbox" name="sheets[]" value="<?= esc($sheet, 'attr') ?>" <?= $onlyAdmin ? '' : 'checked' ?>>
          <span><b><?= esc($sheet) ?></b><span class="cell-sub"><?= esc($sheetNotes[$sheet] ?? '') ?></span></span>
        </label>
      <?php endforeach ?>
    </div>
    <p class="field-help">Tidak memilih apa pun berarti semua sheet yang Anda boleh terima.</p>
  </fieldset>

  <fieldset class="repeat-row">
    <legend><?= icon('shield') ?> Identitas peserta</legend>
    <?php if ($isAdmin): ?>
      <label class="check">
        <input type="checkbox" name="anonymized" value="1" checked>
        <span><b>Mode anonim</b><span class="cell-sub">Nama panggilan &amp; nama pengguna diganti kode peserta. Matikan hanya untuk kebutuhan penelitian yang sah.</span></span>
      </label>
    <?php else: ?>
      <p class="filter-locked"><?= icon('lock') ?> Mode anonim selalu aktif untuk akun guru, dan data terbatas pada sekolah Anda.</p>
    <?php endif ?>
  </fieldset>

  <div class="form-actions">
    <button class="btn btn-primary" type="submit" formaction="<?= base_url('admin/ekspor/xlsx') ?>"><?= icon('download') ?> Buat XLSX</button>
    <button class="btn btn-ghost" type="submit" formaction="<?= base_url('admin/ekspor/pdf') ?>"><?= icon('download') ?> Buat ringkasan PDF</button>
  </div>
</form>

<section class="panel">
  <h2 class="panel-title"><?= icon('clock') ?> Ekspor terakhir</h2>
  <p class="muted">Berkas dihapus otomatis <?= esc($retention) ?> hari setelah dibuat.<?= $isAdmin ? '' : ' Hanya ekspor milik Anda yang tampil.' ?></p>
  <?= component('components/admin-table', [
      'caption'      => 'Daftar ekspor terakhir',
      'emptyMessage' => 'Belum ada ekspor.',
      'rows'         => $recent,
      'rowClass'     => static fn (array $row): string => $row['status'] === 'failed' ? 'is-bad' : '',
      'columns'      => [
          'id'         => ['label' => '#', 'format' => 'num'],
          'created_at' => ['label' => 'Diminta', 'format' => 'datetime'],
          'format'     => ['label' => 'Format', 'render' => static fn (array $row): string => '<span class="badge">' . esc(strtoupper((string) $row['format'])) . '</span>' . ($row['anonymized'] ? '<span class="cell-sub">anonim</span>' : '<span class="cell-sub">beridentitas</span>')],
          'status'     => ['label' => 'Status', 'render' => static fn (array $row): string => '<span class="badge is-' . esc($row['status'], 'attr') . '" data-export-id="' . (int) $row['id'] . '" data-status="' . esc($row['status'], 'attr') . '">' . esc($statusNames[$row['status']] ?? $row['status']) . '</span>' . ($row['error_message'] ? '<span class="cell-sub">' . esc($row['error_message']) . '</span>' : '')],
          'row_count'  => ['label' => 'Baris', 'format' => 'num'],
          'file_sha256' => ['label' => 'SHA-256', 'render' => static fn (array $row): string => $row['file_sha256'] ? '<code title="' . esc($row['file_sha256'], 'attr') . '">' . esc(substr((string) $row['file_sha256'], 0, 12)) . '…</code>' : '—'],
          'expires_at' => ['label' => 'Kedaluwarsa', 'format' => 'date'],
          'actions'    => ['label' => '', 'render' => static function (array $row): string {
              $expired = $row['expires_at'] !== null && strtotime((string) $row['expires_at']) < time();

              if ($row['status'] !== 'done') {
                  return '';
              }

              if ($expired) {
                  return '<span class="cell-sub">sudah dihapus</span>';
              }

              return '<a class="btn btn-quiet btn-sm" href="' . esc(base_url('admin/ekspor/unduh/' . $row['id']), 'attr') . '">' . icon('download') . ' Unduh</a>';
          }],
      ],
  ]) ?>
</section>
<?= $this->endSection() ?>
