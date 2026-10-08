<?php
/**
 * Kritik & saran — `/admin/masukan` → FeedbackController::index (Dok 3 §8.3)
 *
 * Tabel: kode, sekolah, bintang (1–5), empat jawaban (dipotong, klik untuk
 * lengkap), waktu. Nama siswa tidak ditampilkan.
 *
 * @var array<string, mixed> $table
 * @var array<string, mixed> $filters
 */
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title' => 'Kritik & saran',
    'lead'  => 'Pendapat siswa yang ditulis di Balai Refleksi setelah menyelesaikan semua tantangan.',
]) ?>
<?= $this->include('partials/flash') ?>
<?= component('admin-filter-bar', ['filters' => $filters, 'only' => ['school_id', 'date_from']]) ?>

<?= component('data-table', [
    'table'        => $table,
    'id'           => 'masukan',
    'emptyMessage' => 'Belum ada masukan. Siswa dapat mengirim kritik & saran setelah menyelesaikan seluruh tantangan.',
    'render'       => [
        'participant_code' => static fn (array $r): string => '<a href="' . base_url('admin/peserta/' . $r['participant_id'] . '?tab=masukan') . '">' . esc($r['participant_code']) . '</a>',
    ],
]) ?>
<?= $this->endSection() ?>
