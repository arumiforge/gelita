<?php
/**
 * Kelengkapan aset — `/admin/media/kelengkapan` → MediaController::checklist
 *
 * Apa yang belum diunggah, dengan ukuran wajib dan tautan ke tempat
 * mengunggahnya (App\Libraries\AssetChecklist). Status ditulis sebagai teks
 * dan ikon, bukan warna saja.
 *
 * @var list<array{title: string, help: string, items: list<array<string, string>>}> $groups
 * @var array{missing: int, wrong: int, ok: int} $summary
 */
$statusText = ['missing' => 'belum ada', 'wrong' => 'perlu diperiksa', 'ok' => 'lengkap'];
$statusIcon = ['missing' => 'cross', 'wrong' => 'warn', 'ok' => 'check'];
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php ob_start() ?>
<a class="btn btn-ghost btn-sm" href="<?= base_url('admin/media') ?>"><?= icon('image') ?> Media</a>
<a class="btn btn-ghost btn-sm" href="<?= base_url('admin/konten/narasi') ?>"><?= icon('sound') ?> Narasi</a>
<?php $actions = ob_get_clean() ?>
<?= component('partials/admin-head', [
    'title'   => 'Kelengkapan aset',
    'eyebrow' => 'Pengelolaan · sebelum sesi kelas',
    'lead'    => 'Permainan tetap berjalan tanpa aset ini (memakai pengganti), tetapi tampilannya belum utuh. Setiap butir menyebut ukuran wajib dan tempat mengunggahnya.',
    'actions' => $actions,
]) ?>
<?= $this->include('partials/flash') ?>

<div class="kpi-grid">
  <?= component('components/stat-tile', ['label' => 'Belum ada', 'value' => fmt_num($summary['missing']), 'icon' => 'cross']) ?>
  <?= component('components/stat-tile', ['label' => 'Perlu diperiksa', 'value' => fmt_num($summary['wrong']), 'icon' => 'warn', 'hint' => 'ukuran salah / narasi draft']) ?>
  <?= component('components/stat-tile', ['label' => 'Lengkap', 'value' => fmt_num($summary['ok']), 'icon' => 'check']) ?>
</div>

<?php foreach ($groups as $group): ?>
  <?php $open = array_filter($group['items'], static fn (array $item): bool => $item['status'] !== 'ok'); ?>
  <section class="panel checklist-group">
    <h2 class="panel-title"><?= esc($group['title']) ?> <span class="muted"><?= count($open) ?> perlu tindakan</span></h2>
    <p class="muted"><?= esc($group['help']) ?></p>
    <?= component('components/admin-table', [
        'caption'      => $group['title'],
        'emptyMessage' => 'Semua lengkap.',
        'rows'         => $group['items'],
        'rowClass'     => static fn (array $item): string => $item['status'] === 'missing' ? 'is-error' : ($item['status'] === 'wrong' ? 'is-warn' : ''),
        'columns'      => [
            'status' => ['label' => 'Status', 'render' => static fn (array $item): string => '<span class="checklist-status is-' . esc($item['status'], 'attr') . '">'
                . icon($statusIcon[$item['status']]) . ' ' . esc($statusText[$item['status']]) . '</span>'],
            'label' => ['label' => 'Aset', 'render' => static fn (array $item): string => esc($item['label']) . '<span class="cell-sub"><code>' . esc($item['key']) . '</code></span>'],
            'size'  => 'Ukuran wajib',
            'note'  => ['label' => 'Keterangan', 'render' => static fn (array $item): string => $item['note'] === '' ? '<span class="muted">—</span>' : esc($item['note'])],
            'link'  => ['label' => '', 'render' => static fn (array $item): string => $item['status'] === 'ok' ? '' : '<a class="btn btn-quiet btn-sm" href="' . esc($item['link'], 'attr') . '">' . icon('upload') . ' ' . esc($item['link_label']) . '</a>'],
        ],
    ]) ?>
  </section>
<?php endforeach ?>
<?= $this->endSection() ?>
