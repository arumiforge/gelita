<?php
/**
 * Ekspor data — `/admin/ekspor` → ExportController::index
 *
 * Filter sama dengan halaman analitik (nama field identik), tetapi dikirim
 * lewat POST. Guru: sekolah terkunci, mode anonim dipaksa server, dan sheet
 * Raw Events tidak tersedia — form hanya mencerminkan aturan itu.
 * Baris dengan status queued/running diperbarui admin/export.js
 * lewat data-export-id; tanpa JavaScript, muat ulang halaman.
 *
 * Nama sheet (Participants, Sessions, …) dan nama kolom di berkas Excel
 * sengaja tetap berbahasa Inggris untuk program analisis data; di formulir
 * setiap sheet diberi nama Indonesia (admin_label('exportSheet')).
 *
 * @var array<string, mixed>        $filters   filter dari query string (dari halaman analitik)
 * @var list<string>                $sheets
 * @var list<string>                $adminOnly
 * @var bool                        $isAdmin
 * @var list<array<string, mixed>>  $recent
 * @var int                         $retention hari
 * @var int                         $rawEventLimit ambang baris Raw Events
 */
$sheetNotes = [
    'Participants'        => 'Kode peserta, kelas, sekolah, dan daerah',
    'Sessions'            => 'Satu baris untuk tiap sesi bermain',
    'Levels'              => 'Skor dan bintang di tiap wilayah',
    'Challenge Summary'   => 'Hasil tiap kali siswa mengerjakan tantangan',
    'Item Responses'      => 'Jawaban tiap soal, termasuk perubahan jawaban',
    'Raw Events'          => 'Semua klik dan aktivitas siswa — berkasnya besar, khusus admin',
    'Audio Usage'         => 'Kapan siswa memutar suara dan narasi',
    'Indicators'          => 'Capaian tiap indikator pembelajaran',
    'Demographic Summary' => 'Jumlah siswa per kelompok (kelas, jenis kelamin, …)',
    'Feedback'            => 'Kritik, saran, dan bintang dari siswa',
];
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title'   => 'Unduh data',
    'eyebrow' => 'Laporan',
    'lead'    => 'Unduh data hasil permainan sebagai berkas Excel untuk diolah, atau sebagai ringkasan PDF yang siap dibaca. Setiap unduhan tercatat di riwayat aktivitas.',
]) ?>
<?= $this->include('partials/flash') ?>

<form method="post" action="<?= base_url('admin/ekspor/xlsx') ?>" class="form-section">
  <?= csrf_field() ?>
  <h2><?= icon('search') ?> Data mana yang diunduh?</h2>
  <?= component('components/admin-filter-bar', [
      'filters'       => $filters,
      'filterOptions' => $filterOptions,
      'bare'          => true,
  ]) ?>

  <fieldset class="repeat-row">
    <legend><?= icon('list') ?> Isi berkas Excel (satu lembar untuk tiap pilihan)</legend>
    <div class="check-grid">
      <?php foreach ($sheets as $sheet): ?>
        <?php $onlyAdmin = in_array($sheet, $adminOnly, true); ?>
        <?php if ($onlyAdmin && ! $isAdmin) {
            continue;
        } ?>
        <label class="check">
          <input type="checkbox" name="sheets[]" value="<?= esc($sheet, 'attr') ?>" <?= $onlyAdmin ? '' : 'checked' ?>>
          <span><b><?= esc(admin_label('exportSheet', $sheet)) ?></b><span class="cell-sub"><?= esc($sheetNotes[$sheet] ?? '') ?> · lembar “<?= esc($sheet) ?>”</span></span>
        </label>
      <?php endforeach ?>
    </div>
    <p class="field-help">Bila tidak ada yang dicentang, semua lembar yang boleh Anda terima ikut diunduh.<?php if ($isAdmin): ?> Catatan aktivitas lengkap hanya bisa diunduh bila jumlahnya tidak lebih dari <?= esc(fmt_num($rawEventLimit)) ?> baris; bila lebih, persempit rentang tanggal.<?php endif ?></p>
  </fieldset>

  <fieldset class="repeat-row">
    <legend><?= icon('shield') ?> Nama siswa</legend>
    <?php if ($isAdmin): ?>
      <label class="check">
        <input type="checkbox" name="anonymized" value="1" checked>
        <span><b>Sembunyikan nama siswa</b><span class="cell-sub">Nama lengkap dan nama pengguna diganti kode peserta. Hilangkan centang hanya bila penelitian benar-benar membutuhkan nama siswa.</span></span>
      </label>
    <?php else: ?>
      <p class="filter-locked"><?= icon('lock') ?> Unduhan dari akun guru selalu tanpa nama siswa, dan hanya berisi siswa dari sekolah Anda.</p>
    <?php endif ?>
  </fieldset>

  <div class="form-actions">
    <button class="btn btn-primary" type="submit" formaction="<?= base_url('admin/ekspor/xlsx') ?>"><?= icon('download') ?> Buat berkas Excel</button>
    <button class="btn btn-ghost" type="submit" formaction="<?= base_url('admin/ekspor/pdf') ?>"><?= icon('download') ?> Buat ringkasan PDF</button>
  </div>
  <p class="field-help">Ringkasan PDF memakai pilihan data di atas dan selalu tanpa nama siswa. Laporan untuk satu siswa dibuat dari halaman profil peserta.</p>
</form>

<section class="panel">
  <h2 class="panel-title"><?= icon('clock') ?> Unduhan terakhir</h2>
  <p class="muted">Berkas dihapus otomatis <?= esc($retention) ?> hari setelah dibuat; buat ulang bila masih diperlukan.<?= $isAdmin ? '' : ' Hanya unduhan milik Anda yang tampil.' ?></p>
  <?= component('components/admin-table', [
      'caption'      => 'Daftar unduhan terakhir',
      'emptyMessage' => 'Belum ada data yang diunduh.',
      'rows'         => $recent,
      'rowClass'     => static fn (array $row): string => $row['status'] === 'failed' ? 'is-bad' : '',
      'columns'      => [
          'id'         => ['label' => 'No.', 'format' => 'num'],
          'created_at' => ['label' => 'Dibuat', 'format' => 'datetime'],
          'format'     => ['label' => 'Jenis', 'render' => static fn (array $row): string => '<span class="badge">' . esc($row['format'] === 'xlsx' ? 'Excel' : strtoupper((string) $row['format'])) . '</span>' . ($row['anonymized'] ? '<span class="cell-sub">tanpa nama</span>' : '<span class="cell-sub">dengan nama siswa</span>')],
          'status'     => ['label' => 'Status', 'render' => static fn (array $row): string => '<span class="badge is-' . esc($row['status'], 'attr') . '" data-export-id="' . (int) $row['id'] . '" data-status="' . esc($row['status'], 'attr') . '">' . esc(admin_label('exportStatus', (string) $row['status'])) . '</span>' . ($row['error_message'] ? '<span class="cell-sub">' . esc($row['error_message']) . '</span>' : '')],
          'row_count'  => ['label' => 'Jumlah baris', 'format' => 'num'],
          'file_sha256' => ['label' => 'Kode cek berkas', 'render' => static fn (array $row): string => $row['file_sha256'] ? '<code title="' . esc('Kode unik isi berkas (SHA-256), untuk memastikan berkas tidak berubah: ' . $row['file_sha256'], 'attr') . '">' . esc(substr((string) $row['file_sha256'], 0, 12)) . '…</code>' : '—'],
          'expires_at' => ['label' => 'Dihapus pada', 'format' => 'date'],
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
