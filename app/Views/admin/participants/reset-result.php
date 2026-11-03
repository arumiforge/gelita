<?php
/**
 * Sandi sementara — respons POST `/admin/peserta/{id}/reset-sandi` (admin)
 * or `/admin/peserta/reset-sandi` (researchers by code: the participant
 * array then holds the code only, 27 §10).
 *
 * Ditampilkan SEKALI: tidak disimpan, tidak lewat flash, tidak masuk log.
 * Controller menandai respons `Cache-Control: no-store`. Tombol salin diberi
 * perilaku pada tahap 6; teksnya juga dapat dipilih dengan satu klik
 * (user-select: all).
 *
 * @var array<string, mixed> $participant bentuk aman
 * @var string               $temporaryPassword
 * @var string|null          $doneUrl     where "Selesai" leads (by-code flow)
 */
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title' => 'Kata sandi sementara',
    'lead'  => 'Siswa ' . $participant['participant_code'],
]) ?>

<section class="panel temp-password-card">
  <?php if (isset($participant['username'])): ?>
    <p>Kata sandi untuk nama pengguna <b><?= esc($participant['username']) ?></b> sudah diganti dengan sandi sementara berikut:</p>
  <?php else: ?>
    <p>Kata sandi siswa <b class="mono"><?= esc($participant['participant_code']) ?></b> sudah diganti dengan sandi sementara berikut:</p>
  <?php endif ?>
  <p class="temp-password" id="temp-password"><?= esc($temporaryPassword) ?></p>
  <button type="button" class="btn" data-copy="#temp-password"><?= icon('text') ?> Salin</button>
  <div class="alert alert-warn"><?= icon('warn') ?>
    <p><b>Berikan sandi ini kepada siswa. Saat masuk, siswa akan diminta membuat sandi baru.</b><br>
      Sandi ini hanya muncul sekali dan tidak disimpan di mana pun — catat sekarang, dan jangan memuat ulang halaman ini.</p>
  </div>
  <?php if (isset($doneUrl)): ?>
    <a class="btn btn-primary" href="<?= base_url($doneUrl) ?>"><?= icon('check') ?> Selesai</a>
  <?php else: ?>
    <a class="btn btn-primary" href="<?= base_url('admin/peserta/' . $participant['id']) ?>"><?= icon('left') ?> Kembali ke profil</a>
  <?php endif ?>
</section>
<?= $this->endSection() ?>
