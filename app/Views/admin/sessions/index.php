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
    'title'   => 'Sesi bermain',
    'eyebrow' => 'Data siswa',
    'lead'    => 'Satu sesi berisi permainan satu siswa pada satu fase (misalnya pretest). Buka sesi untuk melihat hasil tiap tantangan dan catatan aktivitasnya.',
]) ?>
<?= $this->include('partials/flash') ?>
<?= component('admin-filter-bar', ['filters' => $filters, 'only' => ['study_id', 'phase_code', 'school_id', 'class_level', 'date_from', 'locale']]) ?>

<?= component('admin-table', [
    'rows'         => $rows,
    'caption'      => 'Daftar sesi bermain',
    'emptyMessage' => $filters !== []
        ? 'Tidak ada sesi untuk pilihan filter ini. Coba perlebar rentang tanggal atau tekan Atur ulang.'
        : 'Belum ada sesi bermain. Sesi dibuat otomatis saat siswa mendaftar atau masuk ke permainan.',
    'columns' => [
        'session_code'     => ['label' => 'Kode sesi', 'render' => static fn (array $r): string => '<a href="' . base_url('admin/sesi/' . $r['id']) . '"><code>' . esc(substr((string) $r['session_code'], 0, 10)) . '</code></a>'],
        'participant_code' => ['label' => 'Kode peserta', 'format' => 'code'],
        'study_code'       => 'Studi',
        'phase_code'       => ['label' => 'Fase', 'format' => 'label', 'group' => 'phase'],
        'locale'           => ['label' => 'Bahasa', 'format' => 'label', 'group' => 'locale'],
        'status'           => ['label' => 'Status', 'format' => 'badge'],
        'started_at'       => ['label' => 'Mulai', 'format' => 'datetime'],
        'duration_ms'      => ['label' => 'Lama bermain', 'format' => 'ms'],
        'completed_nodes'  => ['label' => 'Tantangan selesai', 'format' => 'num'],
        'total_score'      => ['label' => 'Skor', 'format' => 'num', 'decimals' => 1],
        'device_type'      => ['label' => 'Perangkat', 'render' => static fn (array $r): string => esc(trim((($r['device_type'] ?? '') === '' ? '' : admin_label('device', (string) $r['device_type'])) . ' ' . ($r['browser_name'] ?? '')) ?: '—')],
        'id'               => ['label' => '', 'render' => static fn (array $r): string => '<a class="btn btn-quiet btn-sm" href="' . base_url('admin/sesi/' . $r['id']) . '">Lihat</a>'],
    ],
]) ?>

<?= component('admin-pagination', ['pager' => $pager]) ?>
<?= $this->endSection() ?>
