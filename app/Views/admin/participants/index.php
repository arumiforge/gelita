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
$genders = ['laki-laki' => 'L', 'perempuan' => 'P', 'lainnya' => 'Lainnya'];
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title'   => 'Peserta',
    'eyebrow' => 'Data penelitian',
    'lead'    => 'Nama pengguna hanya untuk keperluan guru; ekspor anonim memakai kode peserta.',
]) ?>
<?= $this->include('partials/flash') ?>
<?= component('admin-filter-bar', [
    'filters' => $filters,
    'only'    => ['school_id', 'class_level', 'province_code'],
    'extra'   => ['q' => $search],
]) ?>

<?= component('admin-table', [
    'rows'         => $rows,
    'caption'      => 'Daftar peserta',
    'emptyMessage' => $search !== '' || $filters !== []
        ? 'Tidak ada peserta yang cocok dengan pencarian atau filter ini. Coba kata kunci lain atau atur ulang filter.'
        : 'Belum ada peserta. Peserta muncul setelah siswa mendaftar di halaman permainan.',
    'columns' => [
        'participant_code' => ['label' => 'Kode', 'render' => static fn (array $r): string => '<a href="' . base_url('admin/peserta/' . $r['id']) . '"><code>' . esc($r['participant_code']) . '</code></a>'],
        'username'         => 'Nama pengguna',
        'display_name'     => 'Nama',
        'age'              => ['label' => 'Umur', 'format' => 'num'],
        'gender'           => ['label' => 'JK', 'render' => static fn (array $r): string => esc($genders[$r['gender']] ?? ($r['gender'] ?? '—'))],
        'class_level'      => 'Kelas',
        'school_name'      => 'Sekolah',
        'province_name'    => 'Provinsi',
        'session_count'    => ['label' => 'Sesi', 'format' => 'num'],
        'shards'           => ['label' => 'Serpihan', 'format' => 'num'],
        'mean_first_pass'  => ['label' => 'Tepat awal', 'format' => 'pct'],
        'last_active_at'   => ['label' => 'Terakhir aktif', 'format' => 'datetime'],
        'id'               => ['label' => '', 'render' => static fn (array $r): string => '<a class="btn btn-quiet btn-sm" href="' . base_url('admin/peserta/' . $r['id']) . '">Profil</a>'],
    ],
]) ?>

<?= component('admin-pagination', ['pager' => $pager]) ?>
<?= $this->endSection() ?>
