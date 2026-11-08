<?php
/**
 * Log teknis — `/admin/log-teknis/{tab}` → TechLogController::table
 * (revision 10, part 3: 29 §5.6). Admin only; students as codes, no names,
 * no answers or research events.
 *
 * @var string                $tab
 * @var array<string, string> $tabs
 * @var array<string, mixed>  $table   TechLogService::page()
 * @var array<string, string> $filters
 * @var array<string, mixed>  $summary TechLogService::summary()
 */
$spread = implode(', ', array_map(static fn (array $r): string => $r['os'] . ' / ' . $r['browser'] . ' (' . $r['n'] . ')', $summary['os_browser'])) ?: '—';
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title' => lang('Admin.menuTechLog'),
    'lead'  => lang('Admin.techLogIntro'),
]) ?>
<?= $this->include('partials/flash') ?>

<ul class="stat-list">
  <li><b><?= esc(fmt_num($summary['active_sessions'], 0, 'id')) ?></b> <?= esc(lang('Admin.techLogSessionsActive')) ?></li>
  <li><b><?= esc(fmt_num($summary['dropped_7d'], 0, 'id')) ?></b> <?= esc(lang('Admin.techLogDropped7d')) ?></li>
  <li><?= esc(lang('Admin.techLogOsBrowser')) ?>: <?= esc($spread) ?></li>
</ul>

<nav aria-label="<?= esc(lang('Admin.menuTechLog'), 'attr') ?>">
  <ul class="tabs">
    <?php foreach ($tabs as $key => $label): ?>
      <li><a href="<?= base_url('admin/log-teknis/' . $key) ?>"<?= $key === $tab ? ' aria-current="page"' : '' ?>><?= esc($label) ?></a></li>
    <?php endforeach ?>
  </ul>
</nav>

<form method="get" class="filter-bar" role="search">
  <label class="label" for="tech-code"><?= esc(lang('Admin.techLogSearch')) ?></label>
  <input type="search" id="tech-code" name="participant_code" value="<?= esc($filters['participant_code'] ?? '', 'attr') ?>" placeholder="mis. GLT-000001" autocomplete="off">
  <button class="btn" type="submit"><?= icon('search') ?> Cari</button>
</form>

<?= component('data-table', [
    'table'        => $table,
    'id'           => 'log-teknis-' . $tab,
    'title'        => $tabs[$tab],
    'csv'          => false,
    'tools'        => '<a class="btn btn-sm" href="' . esc(base_url('admin/log-teknis/' . $tab . '/csv' . ($filters === [] ? '' : '?' . http_build_query($filters))), 'attr') . '">' . icon('download') . ' CSV</a>',
    'emptyMessage' => 'Belum ada catatan.',
]) ?>
<?= $this->endSection() ?>
