<?php
/**
 * Sesi bermain — `/admin/sesi` → SessionController::index (Dok 3 §8.3)
 *
 * @var array<string, mixed> $table   AdminTable::page()
 * @var array<string, mixed> $filters
 */
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title' => 'Sesi bermain',
    'lead'  => 'Satu sesi = satu kali siswa bermain dari masuk sampai keluar. Buka sesi untuk melihat linimasa event lengkap.',
]) ?>
<?= $this->include('partials/flash') ?>
<?= component('admin-filter-bar', ['filters' => $filters, 'only' => ['participant_code', 'school_id', 'study_id', 'locale', 'date_from']]) ?>

<?= component('data-table', [
    'table'        => $table,
    'id'           => 'sesi',
    'emptyMessage' => $filters !== [] ? 'Tidak ada sesi yang cocok dengan filter ini.' : 'Belum ada sesi bermain.',
    'render'       => [
        'session_code'     => static fn (array $r): string => '<a href="' . base_url('admin/sesi/' . $r['id']) . '">' . esc($r['session_code']) . '</a>',
        'participant_code' => static fn (array $r): string => '<a href="' . base_url('admin/peserta/' . $r['participant_id']) . '">' . esc($r['participant_code']) . '</a>',
    ],
]) ?>
<?= $this->endSection() ?>
