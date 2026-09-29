<?php
/**
 * Impor bank soal — `/admin/konten/impor-bank` → ContentController::importForm
 *
 * Alur dua langkah: unggah workbook → pratinjau (tidak menulis apa pun) →
 * jalankan impor dalam satu transaksi. Tombol "Simpan soal ke permainan"
 * hanya muncul bila pratinjau tanpa galat. Hasil impor terakhir datang lewat
 * flashdata `import_result`.
 *
 * Di layar, pratinjau disebut "pemeriksaan", galat disebut "kesalahan", dan
 * nama sheet berkode ditemani nama Indonesianya (BankWorkbookGuide::SHEETS)
 * agar guru mudah menemukan tab yang dimaksud di Excel.
 *
 * @var array<string, mixed>|null      $preview
 * @var list<array<string, mixed>>     $history baris audit_logs action=content_import
 */
use App\Libraries\BankWorkbookGuide;

$result = session('import_result');
$labels = [
    'nodes'         => 'Tantangan',
    'passages'      => 'Teks bacaan',
    'items'         => 'Soal',
    'options'       => 'Pilihan jawaban',
    'pieces'        => 'Kartu urutan',
    'sources'       => 'Kartu sumber',
    'hints'         => 'Petunjuk',
    'distractors'   => 'Kata pengecoh',
    'library'       => 'Halaman Pustaka',
    'library_media' => 'Gambar & video Pustaka',
    'media_slots'   => 'Tempat gambar baru',
];
$issueTable = static function (array $rows, string $level): string {
    ob_start(); ?>
  <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th scope="col">Sheet</th><th scope="col" class="is-num">Baris</th><th scope="col">Keterangan</th></tr></thead>
      <tbody>
        <?php foreach ($rows as $row): ?>
          <?php
          $sheet = (string) ($row['sheet'] ?? '-');
          $name  = BankWorkbookGuide::SHEETS[$sheet][0] ?? null;
          ?>
          <tr class="is-<?= $level ?>">
            <td data-sheet="<?= esc($name === null ? 'umum' : $sheet . ' · ' . $name, 'attr') ?>"><?php if ($name !== null): ?><code><?= esc($sheet) ?></code><span class="cell-sub"><?= esc($name) ?></span><?php else: ?><span class="muted">—</span><?php endif ?></td>
            <td class="is-num"><?= (int) ($row['row'] ?? 0) > 0 ? esc($row['row']) : '—' ?></td>
            <td><?= esc($row['message'] ?? '') ?></td>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
    <?php return (string) ob_get_clean();
};
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php ob_start() ?>
<a class="btn btn-ghost btn-sm" href="<?= base_url('admin/konten/impor-bank/templat') ?>"><?= icon('download') ?> Unduh templat Excel</a>
<?php $actions = ob_get_clean() ?>
<?= component('partials/admin-head', [
    'title'   => 'Impor soal dari Excel',
    'eyebrow' => 'Pengelolaan · konten permainan',
    'lead'    => 'Tambah atau ubah banyak soal sekaligus lewat Excel: unduh templat, isi sesuai petunjuk di dalamnya, lalu unggah di sini. Berkas diperiksa dulu tanpa mengubah apa pun. Bila ada satu baris yang gagal, tidak ada yang disimpan sama sekali. Kunci jawaban soal yang sudah pernah dijawab siswa tidak dapat diubah lewat impor.',
    'actions' => $actions,
]) ?>
<?= $this->include('partials/flash') ?>

<section class="panel">
  <h2 class="panel-title"><?= icon('info') ?> Langkahnya</h2>
  <ol class="plain-list">
    <li><b>Unduh templat Excel</b> lewat tombol di atas. Sheet PETUNJUK di dalamnya menjelaskan cara mengisi setiap kolom.</li>
    <li><b>Isi dan simpan</b> sebagai berkas Excel (.xlsx). Jangan mengubah baris pertama (judul kolom) dan nama sheet.</li>
    <li><b>Unggah dan periksa</b> di bawah ini. Pemeriksaan belum menyimpan apa pun.</li>
    <li>Bila tidak ada kesalahan, tekan <b>Simpan soal ke permainan</b>. Soal langsung dipakai siswa yang mulai bermain setelahnya.</li>
  </ol>
</section>

<?php if (is_array($result)): ?>
  <section class="panel">
    <h2 class="panel-title"><?= icon($result['ok'] ? 'check' : 'warn') ?> Hasil impor terakhir</h2>
    <?php if ($result['ok']): ?>
      <div class="summary-tiles">
        <?php foreach ($result['written'] ?? [] as $key => $count): ?>
          <?= component('components/stat-tile', ['label' => $labels[$key] ?? $key, 'value' => fmt_num($count)]) ?>
        <?php endforeach ?>
      </div>
      <p class="muted">Angka di atas adalah jumlah baris yang disimpan (baru atau diperbarui).<?php if (($result['written']['media_slots'] ?? 0) > 0): ?> “Tempat gambar baru” adalah gambar yang disebut di Excel tetapi belum diunggah; unggah gambarnya di menu <a href="<?= base_url('admin/media/kelengkapan') ?>">Gambar &amp; suara</a> dengan kode berkas yang sama.<?php endif ?></p>
    <?php else: ?>
      <p class="alert alert-error" role="alert">Tidak ada yang disimpan. Perbaiki kesalahan berikut di Excel, lalu unggah ulang.</p>
      <?= $issueTable($result['errors'] ?? [], 'error') ?>
    <?php endif ?>
    <?php if (! empty($result['warnings'])): ?>
      <details class="row-details">
        <summary><?= count($result['warnings']) ?> peringatan</summary>
        <?= $issueTable($result['warnings'], 'warning') ?>
      </details>
    <?php endif ?>
  </section>
<?php endif ?>

<form method="post" action="<?= base_url('admin/konten/impor-bank/pratinjau') ?>" class="upload-box" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <div class="field">
    <label for="file"><?= icon('upload') ?> Berkas Excel bank soal <span class="req">*</span></label>
    <input type="file" id="file" name="file" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
    <p class="field-help">Berkas Excel (.xlsx) yang dibuat dari templat, paling besar 20 MB. Yang dibaca hanya sheet nodes, distractors, passages, items, options, pieces, sources, hints, serta library dan library_media (untuk Pustaka Kedu, boleh tidak ada). Sheet PETUNJUK, KAMUS_KOLOM, dan sheet lain tidak dibaca.</p>
  </div>
  <div class="form-actions">
    <button class="btn btn-primary" type="submit"><?= icon('eye') ?> Periksa berkas</button>
  </div>
</form>

<?php if (is_array($preview)): ?>
  <?php
  $summary = $preview['summary'] ?? [];
  $perNode = $summary['per_node'] ?? [];
  ksort($perNode);
  $errorCount   = count($preview['errors'] ?? []);
  $warningCount = count($preview['warnings'] ?? []);
  ?>
  <section class="panel stack">
    <h2 class="panel-title"><?= icon('list') ?> Hasil pemeriksaan <span class="muted"><?= esc(fmt_date($preview['at'] ?? null, true)) ?></span></h2>

    <div class="findings-summary">
      <span class="badge <?= $errorCount > 0 ? 'is-error' : 'is-ok' ?>"><?= icon($errorCount > 0 ? 'cross' : 'check') ?> <?= $errorCount ?> kesalahan</span>
      <span class="badge <?= $warningCount > 0 ? 'is-warn' : 'is-muted' ?>"><?= icon('warn') ?> <?= $warningCount ?> peringatan</span>
    </div>

    <div class="summary-tiles">
      <?php foreach (['nodes', 'passages', 'items', 'options', 'hints', 'distractors', 'library', 'library_media'] as $key): ?>
        <?= component('components/stat-tile', ['label' => $labels[$key], 'value' => fmt_num($summary[$key] ?? 0)]) ?>
      <?php endforeach ?>
    </div>

    <?php if ($perNode !== []): ?>
      <h3>Per tantangan</h3>
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th scope="col">Tantangan</th>
              <th scope="col" class="is-num">Soal</th>
              <th scope="col" class="is-num">Pilihan jawaban</th>
              <th scope="col" class="is-num">Petunjuk</th>
              <th scope="col" class="is-num">Kata pengecoh</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($perNode as $ref => $counts): ?>
              <tr>
                <th scope="row"><?= $ref === '(item)' ? 'Petunjuk khusus soal' : '<code>' . esc($ref) . '</code>' ?></th>
                <td class="is-num"><?= (int) ($counts['items'] ?? 0) ?></td>
                <td class="is-num"><?= (int) ($counts['options'] ?? 0) ?></td>
                <td class="is-num"><?= (int) ($counts['hints'] ?? 0) ?></td>
                <td class="is-num"><?= (int) ($counts['distractors'] ?? 0) ?></td>
              </tr>
            <?php endforeach ?>
          </tbody>
        </table>
      </div>
    <?php endif ?>

    <?php if ($errorCount > 0): ?>
      <h3>Kesalahan (harus diperbaiki)</h3>
      <p class="field-help">Buka sheet dan nomor baris yang disebut di Excel, perbaiki isinya, simpan, lalu unggah ulang.</p>
      <?= $issueTable($preview['errors'], 'error') ?>
    <?php endif ?>

    <?php if ($warningCount > 0): ?>
      <h3>Peringatan (boleh dilanjutkan)</h3>
      <p class="field-help">Peringatan tidak menghalangi penyimpanan, tetapi sebaiknya dibaca dulu.</p>
      <?= $issueTable($preview['warnings'], 'warning') ?>
    <?php endif ?>

    <?php if (! empty($preview['ok'])): ?>
      <form method="post" action="<?= base_url('admin/konten/impor-bank/jalankan') ?>" class="form-actions"
            data-import-run data-items="<?= (int) ($summary['items'] ?? 0) ?>" data-nodes="<?= count(array_diff_key($perNode, ['(item)' => true])) ?>">
        <?= csrf_field() ?>
        <button class="btn btn-primary btn-lg" type="submit"><?= icon('play') ?> Simpan soal ke permainan</button>
        <span class="muted">Berkas: <code><?= esc($preview['file'] ?? '') ?></code></span>
      </form>
    <?php else: ?>
      <p class="alert alert-error" role="alert">Berkas ini belum dapat disimpan. Perbaiki kesalahan di Excel, lalu unggah dan periksa ulang.</p>
    <?php endif ?>
  </section>
<?php endif ?>

<section class="panel">
  <h2 class="panel-title"><?= icon('clock') ?> Riwayat impor</h2>
  <?php
  $historyRows = array_map(static function (array $row) use ($labels): array {
      $meta = json_decode((string) ($row['metadata_json'] ?? ''), true) ?: [];
      $written = [];

      foreach ((array) ($meta['written'] ?? []) as $key => $count) {
          $written[] = ($labels[$key] ?? $key) . ' ' . $count;
      }

      return [
          'occurred_at' => $row['occurred_at'],
          'file'        => $row['target_id'],
          'written'     => implode(' · ', $written),
          'sha'         => (string) ($meta['file_sha256'] ?? ''),
      ];
  }, $history);
  ?>
  <?= component('components/admin-table', [
      'caption' => '10 impor terakhir',
      'columns' => [
          'occurred_at' => ['label' => 'Waktu', 'format' => 'datetime'],
          'file'        => ['label' => 'Berkas', 'format' => 'code'],
          'written'     => 'Yang disimpan',
          'sha'         => ['label' => 'Sidik berkas', 'render' => static fn (array $row): string => $row['sha'] === '' ? '—' : '<code title="' . esc('Kode unik isi berkas (SHA-256): berkas yang isinya sama selalu punya kode yang sama. ' . $row['sha'], 'attr') . '">' . esc(substr($row['sha'], 0, 12)) . '…</code>'],
      ],
      'rows'    => $historyRows,
      'emptyMessage' => 'Belum pernah ada impor soal dari Excel.',
  ]) ?>
</section>
<?= $this->endSection() ?>
