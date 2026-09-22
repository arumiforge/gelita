<?php
/**
 * Sandi sementara — respons POST `/admin/peserta/{id}/reset-sandi`.
 *
 * Ditampilkan SEKALI: tidak disimpan, tidak lewat flash, tidak masuk log.
 * Controller menandai respons `Cache-Control: no-store`. Tombol salin diberi
 * perilaku pada tahap 6; teksnya juga dapat dipilih dengan satu klik
 * (user-select: all).
 *
 * @var array<string, mixed> $participant bentuk aman
 * @var string               $temporaryPassword
 */
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title'   => 'Kata sandi sementara',
    'eyebrow' => 'Reset sandi · ' . $participant['participant_code'],
]) ?>

<section class="panel temp-password-card">
  <p>Kata sandi <b><?= esc($participant['username']) ?></b> sudah diatur ulang.</p>
  <p class="temp-password" id="temp-password"><?= esc($temporaryPassword) ?></p>
  <button type="button" class="btn btn-ghost" data-copy="#temp-password"><?= icon('text') ?> Salin</button>
  <div class="alert alert-warn"><?= icon('warn') ?>
    <p><b>Berikan kepada siswa. Siswa wajib membuat sandi baru saat masuk.</b><br>
      Sandi ini hanya ditampilkan sekali dan tidak disimpan di mana pun — jangan memuat ulang halaman ini.</p>
  </div>
  <a class="btn btn-primary" href="<?= base_url('admin/peserta/' . $participant['id']) ?>"><?= icon('left') ?> Kembali ke profil</a>
</section>
<?= $this->endSection() ?>
