<?php
/**
 * HUD — bar atas permainan, selalu terlihat.
 *
 * Kiri : tombol suara, tombol beranda (peta Kedu), lentera serpihan.
 * Kanan: chip peserta (nama + nama pengguna), tombol keluar, pemilih bahasa.
 *
 * Bagian peserta hanya dirender bila controller mengirim data HUD
 * (BaseGameController::hudData()); layar sebelum login cukup merek + bahasa.
 * Halaman awal mengirim `hideBrand`: logonya sudah besar, merek tidak diulang.
 * Nama pengguna di sini hanya terlihat oleh siswa itu sendiri (aturan 12).
 *
 * @var array<string, mixed>|null $participant bentuk aman (toSafeArray), tanpa password_hash
 * @var array<string, mixed>|null $progress
 * @var bool|null                 $hideBrand
 */
$inGame = isset($participant, $progress) && is_array($participant) && is_array($progress);
?>
<header class="hud">
  <div class="hud-left">
    <div class="hud-icons">
      <button type="button" class="icon-btn" id="btn-sound" aria-pressed="true"
              aria-label="<?= esc(lang('Game.sound'), 'attr') ?>">
        <?= icon('sound', 'icon-on') ?><?= icon('sound-off', 'icon-off') ?>
      </button>
      <?php if ($inGame): ?>
        <a class="icon-btn hud-leave" href="<?= base_url('peta') ?>"
           aria-label="<?= esc(lang('Game.mapKedu'), 'attr') ?>" title="<?= esc(lang('Game.mapKedu'), 'attr') ?>"><?= icon('home') ?></a>
      <?php endif ?>
    </div>

    <?php if ($inGame): ?>
      <?= $this->include('components/lantern') ?>
    <?php elseif (empty($hideBrand)): ?>
      <a class="hud-brand" href="<?= base_url() ?>"><?= esc(lang('Game.appName')) ?></a>
    <?php endif ?>
  </div>

  <div class="hud-right">
    <?php if ($inGame): ?>
      <a class="user-chip hud-leave" href="<?= base_url('profil') ?>"
         aria-label="<?= esc(lang('Game.profile') . ': ' . ($participant['display_name'] ?: $participant['username']), 'attr') ?>">
        <span class="user-avatar" aria-hidden="true"><?= esc(mb_strtoupper(mb_substr((string) ($participant['display_name'] ?: $participant['username']), 0, 1))) ?></span>
        <span class="user-text">
          <span class="user-name"><?= esc($participant['display_name'] ?: $participant['username']) ?></span>
          <small class="user-code">@<?= esc($participant['username']) ?></small>
        </span>
      </a>
      <a class="icon-btn hud-leave" href="<?= base_url('keluar') ?>"
         aria-label="<?= esc(lang('Game.logout'), 'attr') ?>" title="<?= esc(lang('Game.logout'), 'attr') ?>"><?= icon('logout') ?></a>
    <?php endif ?>
    <?= $this->include('components/lang-switch') ?>
  </div>
</header>
