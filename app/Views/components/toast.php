<?php
/**
 * Lapisan notifikasi sementara (#toast-layer).
 *
 * Pesan flash dari server ikut dirender sebagai toast yang memudar sendiri
 * lewat animasi CSS — tanpa JavaScript. core/toast.js (sudah ada sejak
 * tahap 2) menambahkan toast berikutnya ke lapisan yang sama.
 *
 * Game: `message` (berhasil) dan `error` (mis. "Wilayah ini belum terbuka")
 * tampil sebagai toast. Panel admin: hanya `message`; `error` dirender tetap
 * di atas halaman oleh partials/flash agar tidak terlewat.
 *
 * @var bool|null $withErrors sertakan flash `error`
 */
$message = session('message');
$error   = ! empty($withErrors) ? session('error') : null;
?>
<div id="toast-layer" class="toast-layer" role="status" aria-live="polite">
  <?php if (is_string($message) && $message !== ''): ?>
    <div class="toast is-ok is-flash"><?= icon('check') ?><span><?= esc($message) ?></span></div>
  <?php endif ?>
  <?php if (is_string($error) && $error !== ''): ?>
    <div class="toast is-bad is-flash"><?= icon('warn') ?><span><?= esc($error) ?></span></div>
  <?php endif ?>
</div>
