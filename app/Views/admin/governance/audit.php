<?php
/**
 * Audit log — `/admin/tata-kelola/audit` → GovernanceController::audit
 *
 * Catatan tindakan staf, 50 baris per halaman, terbaru di atas. Filter lewat
 * query string (GET) sehingga tautannya dapat dibagikan. Metadata JSON
 * dibuka per baris dengan <details>. Kata sandi mentah tidak pernah tercatat.
 *
 * @var list<array<string, mixed>>   $rows
 * @var CodeIgniter\Pager\Pager|null $pager
 * @var string                       $action
 * @var string                       $from
 * @var string                       $to
 * @var list<string>                 $actions
 * @var array<int|string, string>    $staff  id → username
 */
$filtered = $action !== '' || $from !== '' || $to !== '';
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title'   => 'Audit log',
    'eyebrow' => 'Pengelolaan · data penelitian',
    'lead'    => 'Setiap ekspor, unduhan, perubahan konten, reset kata sandi, dan penghapusan data tercatat di sini. Alamat IP disimpan sebagai hash.',
]) ?>
<ul class="sub-nav">
  <li><a href="<?= base_url('admin/tata-kelola') ?>"><?= icon('shield') ?> Penghapusan & retensi</a></li>
  <li><a href="<?= base_url('admin/tata-kelola/audit') ?>" aria-current="page"><?= icon('list') ?> Audit log</a></li>
</ul>
<?= $this->include('partials/flash') ?>

<form method="get" class="filter-bar" aria-label="Filter audit log">
  <div class="field">
    <label for="f-action">Aksi</label>
    <select id="f-action" name="action">
      <option value="">Semua aksi</option>
      <?php foreach ($actions as $option): ?>
        <option value="<?= esc($option, 'attr') ?>" <?= $action === $option ? 'selected' : '' ?>><?= esc($option) ?></option>
      <?php endforeach ?>
    </select>
  </div>
  <div class="field">
    <label for="f-from">Dari tanggal</label>
    <input type="date" id="f-from" name="date_from" value="<?= esc($from, 'attr') ?>">
  </div>
  <div class="field">
    <label for="f-to">Sampai tanggal</label>
    <input type="date" id="f-to" name="date_to" value="<?= esc($to, 'attr') ?>">
  </div>
  <div class="filter-actions">
    <button class="btn btn-primary btn-sm" type="submit"><?= icon('search') ?> Terapkan</button>
    <?php if ($filtered): ?>
      <a class="btn btn-quiet btn-sm" href="<?= base_url('admin/tata-kelola/audit') ?>">Atur ulang</a>
    <?php endif ?>
  </div>
</form>

<section class="panel">
  <?= component('components/admin-table', [
      'caption'      => 'Catatan audit',
      'emptyMessage' => $filtered ? 'Tidak ada catatan yang cocok dengan filter ini.' : 'Belum ada catatan audit.',
      'rows'         => $rows,
      'rowClass'     => static fn (array $row): string => str_starts_with((string) $row['action'], 'delete_') ? 'is-warn' : '',
      'columns'      => [
          'occurred_at'   => ['label' => 'Waktu', 'render' => static fn (array $row): string => '<span class="timeline-time">' . esc(fmt_date($row['occurred_at'], true, 'id')) . '</span>'],
          'action'        => ['label' => 'Aksi', 'format' => 'code'],
          'staff_user_id' => ['label' => 'Staf', 'render' => static fn (array $row): string => $row['staff_user_id'] === null
              ? '<span class="muted">sistem</span>'
              : esc($staff[$row['staff_user_id']] ?? '#' . $row['staff_user_id'])],
          'target'        => ['label' => 'Target', 'render' => static fn (array $row): string => $row['target_type'] === null
              ? '—'
              : esc($row['target_type']) . ($row['target_id'] !== null ? ' <code>' . esc($row['target_id']) . '</code>' : '')],
          'metadata_json' => ['label' => 'Metadata', 'render' => static function (array $row): string {
              $raw = (string) ($row['metadata_json'] ?? '');

              if ($raw === '' || $raw === '[]' || $raw === '{}') {
                  return '—';
              }

              $decoded = json_decode($raw, true);
              $pretty  = is_array($decoded) ? json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : $raw;

              return '<details class="row-details"><summary><code class="json-inline">' . esc($raw) . '</code></summary><pre class="code-block">' . esc((string) $pretty) . '</pre></details>';
          }],
      ],
  ]) ?>
  <?= component('components/admin-pagination', ['pager' => $pager]) ?>
</section>
<?= $this->endSection() ?>
