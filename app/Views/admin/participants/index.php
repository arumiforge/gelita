<?php
/**
 * Daftar peserta — `/admin/peserta` → ParticipantController::index
 *
 * Guru terkunci pada sekolahnya: kolom sekolah tetap tampil sebagai
 * keterangan, tetapi filter sekolah tidak dirender dan server memaksa cakupan.
 *
 * @var list<array<string, mixed>>      $rows    bentuk aman, tanpa password_hash
 * @var CodeIgniter\Pager\Pager|null     $pager
 * @var array<string, mixed>             $filters
 * @var string                           $search
 */
$genders = ['laki-laki' => 'L', 'perempuan' => 'P'];
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title'   => 'Siswa',
    'eyebrow' => 'Data siswa',
    'lead'    => 'Siswa yang sudah mendaftar di permainan. Nama pengguna dan nama lengkap hanya untuk keperluan guru; data yang diunduh tanpa nama memakai kode siswa.',
]) ?>
<?= $this->include('partials/flash') ?>
<?= component('admin-filter-bar', [
    'filters' => $filters,
    'only'    => ['school_id', 'class_level', 'province_code'],
    'extra'   => ['q' => $search],
]) ?>

<?= component('admin-table', [
    'rows'         => $rows,
    'caption'      => 'Daftar siswa',
    'emptyMessage' => $search !== '' || $filters !== []
        ? 'Tidak ada siswa yang cocok dengan pencarian atau filter ini. Coba kata lain atau tekan Atur ulang.'
        : 'Belum ada siswa. Siswa muncul di sini setelah mendaftar di halaman permainan.',
    'columns' => [
        'participant_code' => ['label' => 'Kode siswa', 'render' => static fn (array $r): string => '<a href="' . base_url('admin/peserta/' . $r['id']) . '"><code>' . esc($r['participant_code']) . '</code></a>'],
        'username'         => 'Nama pengguna',
        'display_name'     => 'Nama lengkap',
        'age'              => ['label' => 'Umur', 'format' => 'num'],
        'gender'           => ['label' => 'L/P', 'render' => static fn (array $r): string => esc($genders[$r['gender']] ?? ($r['gender'] ?? '—'))],
        'class_level'      => ['label' => 'Kelas', 'render' => static fn (array $r): string => esc(class_label($r['class_level'] ?? null))],
        'school_name'      => 'Sekolah',
        'province_name'    => 'Provinsi',
        'session_count'    => ['label' => 'Sesi', 'format' => 'num'],
        'shards'           => ['label' => 'Tantangan selesai', 'format' => 'num'],
        'last_active_at'   => ['label' => 'Terakhir aktif', 'format' => 'datetime'],
        'id'               => ['label' => '', 'render' => static fn (array $r): string => '<a class="btn btn-quiet btn-sm" href="' . base_url('admin/peserta/' . $r['id']) . '">Lihat profil</a>'],
    ],
]) ?>

<?= component('admin-pagination', ['pager' => $pager]) ?>
<?= $this->endSection() ?>
