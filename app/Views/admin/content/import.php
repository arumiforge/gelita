<?php
/**
 * Impor bank soal — `/admin/konten/impor-bank` → ContentController::importForm
 *
 * Alur dua langkah: unggah workbook → pratinjau (tidak menulis apa pun) →
 * jalankan impor dalam satu transaksi. Tombol "Jalankan impor" hanya muncul
 * bila pratinjau tanpa galat. Hasil impor terakhir datang lewat flashdata
 * `import_result`.
 *
 * @var array<string, mixed>|null      $preview
 * @var list<array<string, mixed>>     $history baris audit_logs action=content_import
 */
$result = session('import_result');
$labels = [
    'nodes'       => 'Tantangan',
    'passages'    => 'Teks bacaan',
    'items'       => 'Butir soal',
    'options'     => 'Opsi',
    'pieces'      => 'Potongan urutan',
    'sources'     => 'Sumber',
    'hints'       => 'Petunjuk',
    'distractors' => 'Pengecoh',
];
$issueTable = static function (array $rows, string $level): string {
    ob_start(); ?>
  <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th scope="col">Sheet</th><th scope="col" class="is-num">Baris</th><th scope="col">Pesan</th></tr></thead>
      <tbody>
        <?php foreach ($rows as $row): ?>
          <tr class="is-<?= $level ?>">
            <td><code><?= esc($row['sheet'] ?? '-') ?></code></td>
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
<a class="btn btn-ghost btn-sm" href="<?= base_url('admin/konten/impor-bank/templat') ?>"><?= icon('download') ?> Unduh templat kosong</a>
<?php $actions = ob_get_clean() ?>
<?= component('partials/admin-head', [
    'title'   => 'Impor bank soal',
    'eyebrow' => 'Pengelolaan · konten',
    'lead'    => 'Unggah workbook .xlsx, periksa pratinjaunya, lalu jalankan. Impor berjalan dalam satu transaksi: satu baris gagal membatalkan semuanya. Kunci jawaban butir yang sudah pernah dijawab tidak ikut diubah.',
    'actions' => $actions,
]) ?>
<?= $this->include('partials/flash') ?>

<?php if (is_array($result)): ?>
  <section class="panel">
    <h2 class="panel-title"><?= icon($result['ok'] ? 'check' : 'warn') ?> Hasil impor terakhir</h2>
    <?php if ($result['ok']): ?>
      <div class="summary-tiles">
        <?php foreach ($result['written'] ?? [] as $key => $count): ?>
          <?= component('components/stat-tile', ['label' => $labels[$key] ?? $key, 'value' => fmt_num($count)]) ?>
        <?php endforeach ?>
      </div>
      <p class="muted">Angka di atas adalah baris yang ditulis (baru atau diperbarui).</p>
    <?php else: ?>
      <p class="alert alert-error" role="alert">Tidak ada yang ditulis. Perbaiki galat berikut lalu unggah ulang.</p>
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
    <label for="file"><?= icon('upload') ?> Workbook bank soal <span class="req">*</span></label>
    <input type="file" id="file" name="file" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
    <p class="field-help">Format .xlsx sesuai templat, maksimal 20 MB. Sheet: nodes, passages, items, options, pieces, sources, hints, distractors.</p>
  </div>
  <div class="form-actions">
    <button class="btn btn-primary" type="submit"><?= icon('eye') ?> Pratinjau</button>
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
    <h2 class="panel-title"><?= icon('list') ?> Pratinjau <span class="muted"><?= esc(fmt_date($preview['at'] ?? null, true)) ?></span></h2>

    <div class="findings-summary">
      <span class="badge <?= $errorCount > 0 ? 'is-error' : 'is-ok' ?>"><?= icon($errorCount > 0 ? 'cross' : 'check') ?> <?= $errorCount ?> galat</span>
      <span class="badge <?= $warningCount > 0 ? 'is-warn' : 'is-muted' ?>"><?= icon('warn') ?> <?= $warningCount ?> peringatan</span>
    </div>

    <div class="summary-tiles">
      <?php foreach (['nodes', 'passages', 'items', 'options', 'hints', 'distractors'] as $key): ?>
        <?= component('components/stat-tile', ['label' => $labels[$key], 'value' => fmt_num($summary[$key] ?? 0)]) ?>
      <?php endforeach ?>
    </div>

    <?php if ($perNode !== []): ?>
      <h3>Per tantangan</h3>
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th scope="col">Node</th>
              <th scope="col" class="is-num">Butir</th>
              <th scope="col" class="is-num">Opsi</th>
              <th scope="col" class="is-num">Petunjuk</th>
              <th scope="col" class="is-num">Pengecoh</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($perNode as $ref => $counts): ?>
              <tr>
                <th scope="row"><code><?= esc($ref) ?></code></th>
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
      <h3>Galat</h3>
      <?= $issueTable($preview['errors'], 'error') ?>
    <?php endif ?>

    <?php if ($warningCount > 0): ?>
      <h3>Peringatan</h3>
      <?= $issueTable($preview['warnings'], 'warning') ?>
    <?php endif ?>

    <?php if (! empty($preview['ok'])): ?>
      <form method="post" action="<?= base_url('admin/konten/impor-bank/jalankan') ?>" class="form-actions"
            data-import-run data-items="<?= (int) ($summary['items'] ?? 0) ?>" data-nodes="<?= count($perNode) ?>">
        <?= csrf_field() ?>
        <button class="btn btn-primary btn-lg" type="submit"><?= icon('play') ?> Jalankan impor</button>
        <span class="muted">Berkas: <code><?= esc($preview['file'] ?? '') ?></code></span>
      </form>
    <?php else: ?>
      <p class="alert alert-error" role="alert">Perbaiki galat di workbook lalu unggah ulang. Impor tidak dapat dijalankan dari pratinjau ini.</p>
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
          'written'     => 'Baris ditulis',
          'sha'         => ['label' => 'SHA-256', 'render' => static fn (array $row): string => $row['sha'] === '' ? '—' : '<code title="' . esc($row['sha'], 'attr') . '">' . esc(substr($row['sha'], 0, 12)) . '…</code>'],
      ],
      'rows'    => $historyRows,
      'emptyMessage' => 'Belum pernah ada impor bank soal.',
  ]) ?>
</section>
<?= $this->endSection() ?>
