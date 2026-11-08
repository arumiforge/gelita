<?php
/**
 * Ekspor data mentah — `/admin/ekspor` → ExportController::index (Dok 3 §9)
 *
 * Filter sama dengan filter bar panel (nama field identik), tetapi dikirim
 * lewat POST. Only `ketua` reaches this page (27 K14): every download is
 * coded, belongs to one study (29 K16), and offers Event Mentah and the
 * answer key (29 K1). The history shows the caller's own downloads (29 §9).
 * Baris dengan status queued/running diperbarui admin/export.js
 * lewat data-export-id; tanpa JavaScript, muat ulang halaman.
 *
 * Nilai kotak centang adalah kode lembar §9.2 (`peserta`, …). Kamus Data
 * selalu ikut. Tata letak §8.3: formulir (format, isi nilai, anonim, filter sekolah,
 * wilayah, rentang tanggal, lembar) + tabel riwayat unduhan (status, baris,
 * ukuran, sha256, kedaluwarsa, Unduh).
 *
 * @var array<string, mixed>        $filters   filter dari query string (dari filter bar panel)
 * @var list<string>                $sheets    lembar yang boleh diterima pemanggil
 * @var array<string, string>       $sheetTitles
 * @var array<string, string>       $sheetNotes
 * @var list<array<string, mixed>>  $recent
 * @var array<int, int>             $downloads export id → times downloaded
 * @var list<array<string, mixed>>  $studies
 * @var int                         $retention hari
 * @var int                         $rawEventLimit ambang baris lembar event
 */
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title' => 'Unduh data',
    'lead'  => 'Data mentah sebagai satu workbook Excel atau CSV-ZIP (satu CSV per lembar, siap SPSS/R/Python), selalu dengan Kamus Data. Setiap unduhan tercatat di riwayat aktivitas.',
]) ?>
<?= $this->include('partials/flash') ?>

<?php if ($studies === []): ?>
<div class="alert alert-warn" role="status"><div class="alert-body"><?= esc(lang('Admin.exportNeedsStudy')) ?> <a href="<?= base_url('admin/studi') ?>">Buka halaman Studi</a></div></div>
<?php else: ?>
<form method="post" action="<?= base_url('admin/ekspor/xlsx') ?>" class="form-section">
  <?= csrf_field() ?>
  <h2><?= icon('filter') ?> Data mana yang diunduh?</h2>
  <div class="form-grid">
    <div class="field">
      <label class="label" for="export-study">Studi</label>
      <select id="export-study" name="study_id" required>
        <?php foreach ($studies as $study): ?>
          <option value="<?= (int) $study['id'] ?>" <?= (string) ($filters['study_id'] ?? '') === (string) $study['id'] ? 'selected' : '' ?>><?= esc($study['code'] . ' · ' . $study['name']) ?></option>
        <?php endforeach ?>
      </select>
      <p class="field-help"><?= esc(lang('Admin.exportPickStudy')) ?></p>
    </div>
    <div class="field">
      <label class="label" for="export-field-session">Kode sesi lapangan</label>
      <input type="text" id="export-field-session" name="field_session_code" value="<?= esc((string) ($filters['field_session_code'] ?? ''), 'attr') ?>" placeholder="mis. S03-A-P2" maxlength="20" autocomplete="off">
    </div>
    <div class="field">
      <label class="label" for="export-meeting">Pertemuan ke-</label>
      <input type="number" id="export-meeting" name="meeting_no" min="1" max="9" value="<?= esc((string) ($filters['meeting_no'] ?? ''), 'attr') ?>">
    </div>
  </div>
  <?= component('components/admin-filter-bar', [
      'filters'       => $filters,
      'filterOptions' => $filterOptions,
      'only'          => ['school_id', 'level_id', 'date_from'],
      'bare'          => true,
  ]) ?>

  <div class="form-grid">
    <fieldset class="field">
      <legend class="label">Isi nilai</legend>
      <label class="check"><input type="radio" name="labels" value="" checked> <span><b>Bawaan</b><span class="cell-sub">Excel berlabel, CSV berkode</span></span></label>
      <label class="check"><input type="radio" name="labels" value="label"> <span><b>Berlabel</b><span class="cell-sub">mis. "Kesempatan habis", "Laki-laki"; jawaban sebagai kalimat</span></span></label>
      <label class="check"><input type="radio" name="labels" value="code"> <span><b>Berkode</b><span class="cell-sub">mis. tries_exhausted, laki-laki; jawaban sebagai JSON</span></span></label>
    </fieldset>
    <fieldset class="field">
      <legend class="label">Identitas siswa</legend>
      <p class="filter-locked"><?= icon('lock') ?> Selalu berkode: tanpa nama, nama pengguna, nama sekolah, dan NPSN. Kode siswa dan kode sekolah tetap ada.</p>
    </fieldset>
  </div>

  <details class="disclosure">
    <summary>Pilih lembar (bawaan: semua lembar yang boleh Anda terima)</summary>
    <div class="check-grid" style="margin-top: 12px">
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
    <p class="field-help">Kamus Data selalu ikut. Waktu ISO 8601 (WIB, dengan milidetik), lama dalam milidetik. Lembar Navigasi, Penyelesaian, dan Event Mentah dibatasi <?= esc(fmt_num($rawEventLimit, 0, 'id')) ?> baris; bila lebih, persempit rentang tanggal.</p>
  </details>

  <div class="form-actions">
    <button class="btn btn-primary" type="submit" formaction="<?= base_url('admin/ekspor/xlsx') ?>"><?= icon('download') ?> Buat XLSX</button>
    <button class="btn" type="submit" formaction="<?= base_url('admin/ekspor/csv') ?>"><?= icon('download') ?> Buat CSV-ZIP</button>
    <span class="field-help">Data dan laporan PDF satu siswa dibuat dari halaman detail siswa.</span>
  </div>
</form>
<?php endif ?>

<?php
$rows = array_map(static function (array $row) use ($downloads): array {
    $path        = (string) ($row['file_path'] ?? '');
    $row['size'] = $path !== '' && is_file($path) ? (int) filesize($path) : null;
    $scope       = is_array($row['scope_json']) ? $row['scope_json'] : (json_decode((string) $row['scope_json'], true) ?: []);
    $filters     = [];

    foreach ((array) ($scope['filters'] ?? []) as $key => $value) {
        $filters[] = $key . '=' . $value;
    }

    $row['sheets']    = implode(', ', (array) ($scope['sheets'] ?? [])) ?: 'semua';
    $row['filters']   = implode('; ', $filters) ?: '—';
    $row['downloads'] = $downloads[(int) $row['id']] ?? 0;

    return $row;
}, $recent);
?>
<?= component('data-table', [
    'table' => [
        'rows' => $rows, 'total' => count($rows), 'page' => 1, 'per_page' => max(1, count($rows)), 'sort' => null,
        'columns' => array_map(static fn (array $c): array => $c + ['sortable' => false], [
            ['key' => 'id', 'label' => 'No.', 'type' => 'int'],
            ['key' => 'created_at', 'label' => 'Dibuat', 'type' => 'datetime'],
            ['key' => 'format', 'label' => 'Format', 'type' => 'text'],
            ['key' => 'status', 'label' => 'Status', 'type' => 'text'],
            ['key' => 'sheets', 'label' => 'Lembar', 'type' => 'text'],
            ['key' => 'filters', 'label' => 'Filter', 'type' => 'text'],
            ['key' => 'row_count', 'label' => 'Baris', 'type' => 'int'],
            ['key' => 'participant_count', 'label' => lang('Admin.exportParticipantCount'), 'type' => 'int'],
            ['key' => 'dictionary_version', 'label' => lang('Admin.exportDictionaryVersion'), 'type' => 'code'],
            ['key' => 'downloads', 'label' => lang('Admin.exportDownloadCount'), 'type' => 'int'],
            ['key' => 'size', 'label' => 'Ukuran (KB)', 'type' => 'int'],
            ['key' => 'file_sha256', 'label' => 'SHA-256', 'type' => 'code'],
            ['key' => 'expires_at', 'label' => 'Kedaluwarsa', 'type' => 'datetime'],
            ['key' => 'actions', 'label' => '', 'type' => 'text'],
        ]),
    ],
    'id'           => 'riwayat-unduhan',
    'title'        => lang('Admin.exportHistoryMine'),
    'csv'          => false,
    'paginate'     => false,
    'stickyFirst'  => false,
    'emptyMessage' => 'Belum ada data yang diunduh.',
    'tools'        => '<span class="table-count">Berkas dihapus otomatis ' . (int) $retention . ' hari setelah dibuat; hanya unduhan milik Anda.</span>',
    'render'       => [
        'format' => static fn (array $row): string => '<span class="badge no-dot">' . esc(match ((string) $row['format']) { 'xlsx' => 'XLSX', 'csv' => 'CSV-ZIP', default => strtoupper((string) $row['format']) }) . '</span>'
            . '<span class="cell-sub">' . ($row['anonymized'] ? 'anonim' : 'beridentitas') . '</span>',
        'status' => static fn (array $row): string => '<span class="badge is-' . esc($row['status'], 'attr') . '" data-export-id="' . (int) $row['id'] . '" data-status="' . esc($row['status'], 'attr') . '">' . esc(admin_label('exportStatus', (string) $row['status'])) . '</span>'
            . ($row['error_message'] ? '<span class="cell-sub">' . esc($row['error_message']) . '</span>' : ''),
        'size' => static fn (array $row): string => $row['size'] === null ? '<span class="is-empty">—</span>' : esc(fmt_num($row['size'] / 1024, 0, 'id')),
        'file_sha256' => static fn (array $row): string => $row['file_sha256'] ? '<span title="' . esc($row['file_sha256'], 'attr') . '">' . esc(substr((string) $row['file_sha256'], 0, 12)) . '…</span>' : '<span class="is-empty">—</span>',
        'actions' => static function (array $row): string {
            if ($row['status'] !== 'done') {
                return '';
            }

            if ($row['expires_at'] !== null && strtotime((string) $row['expires_at']) < time()) {
                return '<span class="cell-sub">' . esc(lang('Admin.exportFileGone')) . '</span>';
            }

            return '<a class="btn btn-sm" href="' . esc(base_url('admin/ekspor/unduh/' . $row['id']), 'attr') . '">' . icon('download') . ' Unduh</a>';
        },
    ],
]) ?>
<?= $this->endSection() ?>
