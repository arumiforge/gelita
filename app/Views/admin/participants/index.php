<?php
/**
 * Siswa — `/admin/peserta` → ParticipantController::index (Dok 3 §8.3)
 *
 * Tabel server-side + filter (cari, sekolah, kab/kota, jenis kelamin, umur,
 * status progres, tanggal daftar). Kolom Nama dan Nama pengguna hanya untuk
 * admin; guru terkunci pada sekolahnya.
 *
 * @var array<string, mixed> $table   AdminTable::page()
 * @var array<string, mixed> $filters
 * @var bool                 $isAdmin
 */
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title' => 'Siswa',
    'lead'  => $isAdmin
        ? 'Siswa yang sudah mendaftar. Data yang diunduh tanpa nama memakai kode siswa.'
        : 'Siswa dari sekolah Anda yang sudah mendaftar. Siswa dikenali dengan kode siswa.',
]) ?>
<?= $this->include('partials/flash') ?>
<?= component('admin-filter-bar', [
    'filters' => $filters,
    'only'    => ['q', 'school_id', 'district_code', 'gender', 'age', 'progress', 'date_from'],
    'extra'   => ['q_placeholder' => $isAdmin ? 'kode, nama pengguna, atau nama' : 'kode siswa'],
    'labels'  => ['date_from' => 'Terdaftar dari', 'date_to' => 'Terdaftar sampai'],
]) ?>

<?= component('data-table', [
    'table'        => $table,
    'id'           => 'siswa',
    'emptyMessage' => $filters !== []
        ? 'Tidak ada siswa yang cocok dengan pencarian atau filter ini.'
        : 'Belum ada siswa. Siswa muncul di sini setelah mendaftar di halaman permainan.',
    'render'       => [
        'participant_code' => static fn (array $r): string => '<a href="' . base_url('admin/peserta/' . $r['id']) . '">' . esc($r['participant_code']) . '</a>',
    ],
]) ?>
<?= $this->endSection() ?>
