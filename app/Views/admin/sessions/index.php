<?php
/**
 * Daftar sesi — `/admin/sesi` → SessionController::index
 *
 * @var list<array<string, mixed>>   $rows
 * @var CodeIgniter\Pager\Pager|null  $pager
 * @var array<string, mixed>          $filters
 */
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title'   => 'Sesi permainan',
    'eyebrow' => 'Data penelitian',
    'lead'    => 'Satu sesi = satu peserta pada satu fase. Buka sesi untuk melihat percobaan per tantangan dan linimasa event.',
]) ?>
<?= $this->include('partials/flash') ?>
<?= component('admin-filter-bar', ['filters' => $filters, 'only' => ['study_id', 'phase_code', 'school_id', 'class_level', 'date_from', 'locale']]) ?>

<?= component('admin-table', [
    'rows'         => $rows,
    'caption'      => 'Daftar sesi permainan',
    'emptyMessage' => $filters !== []
        ? 'Tidak ada sesi untuk filter ini. Longgarkan rentang tanggal atau atur ulang filter.'
        : 'Belum ada sesi permainan. Sesi dibuat otomatis saat siswa mendaftar atau masuk.',
    'columns' => [
        'session_code'     => ['label' => 'Kode', 'render' => static fn (array $r): string => '<a href="' . base_url('admin/sesi/' . $r['id']) . '"><code>' . esc(substr((string) $r['session_code'], 0, 10)) . '</code></a>'],
        'participant_code' => ['label' => 'Peserta', 'format' => 'code'],
        'study_code'       => 'Studi',
        'phase_code'       => 'Fase',
        'locale'           => 'Bahasa',
        'status'           => ['label' => 'Status', 'format' => 'badge'],
        'started_at'       => ['label' => 'Mulai', 'format' => 'datetime'],
        'duration_ms'      => ['label' => 'Durasi', 'format' => 'ms'],
        'completed_nodes'  => ['label' => 'Serpihan', 'format' => 'num'],
        'total_score'      => ['label' => 'Skor', 'format' => 'num', 'decimals' => 1],
        'device_type'      => ['label' => 'Perangkat', 'render' => static fn (array $r): string => esc(trim(($r['device_type'] ?? '') . ' ' . ($r['browser_name'] ?? '')) ?: '—')],
        'id'               => ['label' => '', 'render' => static fn (array $r): string => '<a class="btn btn-quiet btn-sm" href="' . base_url('admin/sesi/' . $r['id']) . '">Buka</a>'],
    ],
]) ?>

<?= component('admin-pagination', ['pager' => $pager]) ?>
<?= $this->endSection() ?>
