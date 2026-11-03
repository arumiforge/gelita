<?php
/**
 * Siswa — `/admin/peserta` → ParticipantController::index (Dok 3 §8.3)
 *
 * Tabel server-side + filter (cari, sekolah, kab/kota, jenis kelamin, umur,
 * status progres, tanggal daftar). Name and username only for the admin,
 * who has no CSV (27 K17); researchers see codes and download a coded CSV.
 *
 * @var array<string, mixed> $table    AdminTable::page()
 * @var array<string, mixed> $filters
 * @var bool                 $identity the caller sees names (admin)
 */
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title' => 'Siswa',
    'lead'  => $identity
        ? 'Akun siswa yang sudah mendaftar. Nama hanya tampil di layar ini dan tidak bisa diunduh.'
        : 'Siswa yang sudah mendaftar, dikenali dengan kode siswa dan kode sekolah.',
]) ?>
<?= $this->include('partials/flash') ?>
<?= component('admin-filter-bar', [
    'filters' => $filters,
    'only'    => ['q', 'school_id', 'district_code', 'gender', 'age', 'progress', 'date_from'],
    'extra'   => ['q_placeholder' => $identity ? 'kode, nama pengguna, atau nama' : 'kode siswa'],
    'labels'  => ['date_from' => 'Terdaftar dari', 'date_to' => 'Terdaftar sampai'],
]) ?>

<?= component('data-table', [
    'table'        => $table,
    'id'           => 'siswa',
    'csv'          => ! $identity,
    'emptyMessage' => $filters !== []
        ? 'Tidak ada siswa yang cocok dengan pencarian atau filter ini.'
        : 'Belum ada siswa. Siswa muncul di sini setelah mendaftar di halaman permainan.',
    'render'       => [
        'participant_code' => static fn (array $r): string => '<a href="' . base_url('admin/peserta/' . $r['id']) . '">' . esc($r['participant_code']) . '</a>',
    ],
]) ?>
<?= $this->endSection() ?>
