<?php
/**
 * Cek server — `/admin/cek-server` → ServerCheckController::index
 * (admin only; owner, 18 November 2026).
 *
 * Every row of App\Libraries\ServerCheck with its state as text and icon,
 * not colour alone. Read only: nothing on this page changes the server.
 *
 * @var list<array{id: string, title: string, help: string, rows: list<array{label: string, value: string, expected: string, state: string, note: string}>}> $groups
 * @var array{fail: int, warn: int, ok: int} $summary
 */
$stateText = ['fail' => 'gagal', 'warn' => 'perlu dicek', 'ok' => 'sesuai', 'info' => 'info'];
$stateIcon = ['fail' => 'cross', 'warn' => 'warn', 'ok' => 'check', 'info' => 'info'];
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php ob_start() ?>
<a class="btn btn-ghost btn-sm" href="<?= base_url('admin/cek-server') ?>"><?= icon('replay') ?> Periksa lagi</a>
<?php $actions = ob_get_clean() ?>
<?= component('partials/admin-head', [
    'title'   => lang('Admin.menuServerCheck'),
    'lead'    => lang('Admin.serverCheckLead'),
    'actions' => $actions,
]) ?>

<?php if ($summary['fail'] > 0): ?>
  <p class="alert alert-error" role="status"><?= icon('cross') ?> <?= esc(lang('Admin.serverCheckFailures')) ?></p>
<?php elseif ($summary['warn'] === 0): ?>
  <p class="alert alert-ok" role="status"><?= icon('check') ?> <?= esc(lang('Admin.serverCheckAllOk')) ?></p>
<?php endif ?>

<div class="kpi-grid">
  <?= component('components/stat-tile', ['label' => 'Gagal', 'value' => fmt_num($summary['fail']), 'icon' => 'cross', 'hint' => 'bagian GELITA tidak berjalan']) ?>
  <?= component('components/stat-tile', ['label' => 'Perlu dicek', 'value' => fmt_num($summary['warn']), 'icon' => 'warn', 'hint' => 'berjalan, tetapi tidak sesuai panduan']) ?>
  <?= component('components/stat-tile', ['label' => 'Sesuai', 'value' => fmt_num($summary['ok']), 'icon' => 'check']) ?>
</div>

<?php foreach ($groups as $group): ?>
  <?php $open = array_filter($group['rows'], static fn (array $row): bool => in_array($row['state'], ['fail', 'warn'], true)); ?>
  <section class="panel checklist-group server-check" id="<?= esc($group['id'], 'attr') ?>">
    <h2 class="panel-title"><?= esc($group['title']) ?><?php if ($open !== []): ?> <span class="muted"><?= count($open) ?> perlu perhatian</span><?php endif ?></h2>
    <p class="muted"><?= esc($group['help']) ?></p>
    <?= component('components/admin-table', [
        'caption'      => $group['title'],
        'emptyMessage' => 'Tidak ada yang diperiksa.',
        'rows'         => $group['rows'],
        'rowClass'     => static fn (array $row): string => $row['state'] === 'fail' ? 'is-error' : ($row['state'] === 'warn' ? 'is-warn' : ''),
        'columns'      => [
            'state'    => ['label' => 'Status', 'render' => static fn (array $row): string => '<span class="checklist-status is-' . esc($row['state'], 'attr') . '">'
                . icon($stateIcon[$row['state']]) . ' ' . esc($stateText[$row['state']]) . '</span>'],
            'label'    => ['label' => 'Yang diperiksa', 'render' => static fn (array $row): string => esc($row['label'])],
            'value'    => ['label' => 'Di server ini', 'render' => static fn (array $row): string => '<span class="server-check-value">' . esc($row['value']) . '</span>'],
            'expected' => ['label' => 'Seharusnya', 'render' => static fn (array $row): string => $row['expected'] === '' ? '<span class="muted">—</span>' : esc($row['expected'])],
            'note'     => ['label' => 'Keterangan', 'render' => static fn (array $row): string => $row['note'] === '' ? '<span class="muted">—</span>' : esc($row['note'])],
        ],
    ]) ?>
  </section>
<?php endforeach ?>
<?= $this->endSection() ?>
