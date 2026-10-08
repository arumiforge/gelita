<?php
/**
 * Ekspor data mentah — `/admin/ekspor` → ExportController::index (Dok 3 §9)
 *
 * Filter sama dengan filter bar panel (nama field identik), tetapi dikirim
 * lewat POST. Guru: sekolah terkunci, mode anonim dipaksa server, dan lembar
 * Event Mentah tidak tersedia — form hanya mencerminkan aturan itu.
 * Baris dengan status queued/running diperbarui admin/export.js
 * lewat data-export-id; tanpa JavaScript, muat ulang halaman.
 *
 * Nilai kotak centang adalah kode lembar §9.2 (`peserta`, …). Kamus Data
 * selalu ikut. Tampilan halaman ini ditata ulang sesi admin (§8.3).
 *
 * @var array<string, mixed>        $filters   filter dari query string (dari filter bar panel)
 * @var list<string>                $sheets    lembar yang boleh diterima pemanggil
 * @var array<string, string>       $sheetTitles
 * @var array<string, string>       $sheetNotes
 * @var bool                        $isAdmin
 * @var list<array<string, mixed>>  $recent
 * @var int                         $retention hari
 * @var int                         $rawEventLimit ambang baris lembar event
 */
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title'   => 'Unduh data',
    'lead'    => 'Unduh data mentah hasil permainan sebagai berkas Excel untuk diolah. Setiap unduhan tercatat di riwayat aktivitas.',
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
    <legend><?= icon('list') ?> Lembar (satu lembar atau satu berkas CSV untuk tiap pilihan)</legend>
    <div class="check-grid">
      <?php foreach ($sheets as $sheet): ?>
        <?php if ($sheet === 'kamus') {
            continue;
        } ?>
        <label class="check">
          <input type="checkbox" name="sheets[]" value="<?= esc($sheet, 'attr') ?>" <?= $sheet === 'event' ? '' : 'checked' ?>>
          <span><b><?= esc($sheetTitles[$sheet]) ?></b><span class="cell-sub"><?= esc($sheetNotes[$sheet]) ?></span></span>
        </label>
      <?php endforeach ?>
    </div>
    <p class="field-help">Lembar Kamus Data (arti, satuan, dan daftar nilai setiap kolom) selalu ikut. Bila tidak ada yang dicentang, semua lembar yang boleh Anda terima ikut diunduh. Waktu ditulis ISO 8601 (WIB, dengan milidetik), lama dalam milidetik. Lembar Navigasi, Penyelesaian, dan Event Mentah hanya bisa diunduh bila jumlahnya tidak lebih dari <?= esc(fmt_num($rawEventLimit, 0, 'id')) ?> baris; bila lebih, persempit rentang tanggal.</p>
  </fieldset>

  <fieldset class="repeat-row">
    <legend><?= icon('list') ?> Isi nilai</legend>
    <label class="check"><input type="radio" name="labels" value="" checked> <span><b>Bawaan</b><span class="cell-sub">Excel berlabel, CSV berkode</span></span></label>
    <label class="check"><input type="radio" name="labels" value="label"> <span><b>Berlabel</b><span class="cell-sub">mis. "Kesempatan habis", "Laki-laki", jawaban dalam kalimat</span></span></label>
    <label class="check"><input type="radio" name="labels" value="code"> <span><b>Berkode</b><span class="cell-sub">mis. tries_exhausted, laki-laki, jawaban JSON — siap SPSS/R/Python</span></span></label>
  </fieldset>

  <fieldset class="repeat-row">
    <legend><?= icon('shield') ?> Nama siswa</legend>
    <?php if ($isAdmin): ?>
      <label class="check">
        <input type="checkbox" name="anonymized" value="1" checked>
        <span><b>Anonim</b><span class="cell-sub">Nama, nama pengguna, nama sekolah, dan NPSN tidak ikut; kode siswa dan kode sekolah samaran tetap ada. Hilangkan centang hanya bila penelitian benar-benar membutuhkan identitas.</span></span>
      </label>
    <?php else: ?>
      <p class="filter-locked"><?= icon('lock') ?> Unduhan dari akun guru selalu anonim, tanpa Event Mentah dan kunci jawaban, dan hanya berisi siswa dari sekolah Anda.</p>
    <?php endif ?>
  </fieldset>

  <div class="form-actions">
    <button class="btn btn-primary" type="submit" formaction="<?= base_url('admin/ekspor/xlsx') ?>"><?= icon('download') ?> Buat berkas Excel (XLSX)</button>
    <button class="btn btn-ghost" type="submit" formaction="<?= base_url('admin/ekspor/csv') ?>"><?= icon('download') ?> Buat CSV-ZIP</button>
  </div>
  <p class="field-help">Data dan laporan PDF untuk satu siswa dibuat dari halaman profil siswa.</p>
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
          'format'     => ['label' => 'Jenis', 'render' => static fn (array $row): string => '<span class="badge">' . esc(match ((string) $row['format']) { 'xlsx' => 'Excel', 'csv' => 'CSV-ZIP', default => strtoupper((string) $row['format']) }) . '</span>' . ($row['anonymized'] ? '<span class="cell-sub">tanpa nama</span>' : '<span class="cell-sub">dengan nama siswa</span>')],
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
