<?php
/**
 * HUD — pita atas panggung 0, 0, 1280 × 56 dp (Dok 3 §3.2), selalu terlihat.
 *
 * Kiri  : lentera serpihan (tetap di kiri atas; dirujuk naskah `intro-07`),
 *         atau merek sebelum login.
 * Tengah: section `hudTitle` (mis. Peta Kedu), atau nama wilayah bila halaman
 *         milik satu wilayah (`$level`).
 * Kanan : bahasa, suara, layar penuh (core/stage.js; tersembunyi bila tidak
 *         didukung), beranda (peta Kedu), chip peserta, keluar.
 *
 * Section `hudBack` (a URL) puts a Back button in the top-left corner and the
 * GELITA logo (`ui.logo`, or the text brand) in the centre instead; used by the
 * account screens before login (`/mulai`, `/daftar/1…3`).
 *
 * Bagian peserta hanya dirender bila controller mengirim data HUD
 * (BaseGameController::hudData()); layar sebelum login cukup merek + bahasa.
 * Halaman awal mengirim `hideBrand`: logonya sudah besar, merek tidak diulang.
 * Nama pengguna di sini hanya terlihat oleh siswa itu sendiri (aturan 12).
 *
 * @var array<string, mixed>|null $participant bentuk aman (toSafeArray), tanpa password_hash
 * @var array<string, mixed>|null $progress
 * @var bool|null                 $hideBrand
 * @var \App\Entities\Level|null   $level
 */
$inGame    = isset($participant, $progress) && is_array($participant) && is_array($progress);
$hudTitle  = trim($this->renderSection('hudTitle'));
$hudRegion = $hudTitle !== '' ? $hudTitle : (isset($level) && $level instanceof \App\Entities\Level
    ? $level->text('name', service('request')->getLocale())
    : null);
$hudBack = trim($this->renderSection('hudBack'));
$hudLogo = $hudBack !== '' ? media_key_src('ui.logo') : null;
?>
<header class="hud">
  <div class="hud-left">
    <?php if ($hudBack !== ''): ?>
      <a class="btn btn-quiet hud-back" href="<?= esc($hudBack, 'attr') ?>"><?= icon('left') ?> <span><?= esc(lang('Game.back')) ?></span></a>
    <?php elseif ($inGame): ?>
      <?= $this->include('components/lantern') ?>
      <span class="hud-brand is-small" aria-hidden="true"><?= esc(lang('Game.appName')) ?></span>
    <?php elseif (empty($hideBrand)): ?>
      <a class="hud-brand" href="<?= base_url() ?>"><?= esc(lang('Game.appName')) ?></a>
    <?php endif ?>
  </div>

  <?php if ($hudBack !== '' && $hudRegion === null): ?>
    <div class="hud-center hud-logo">
      <?php if ($hudLogo !== null): ?>
        <img src="<?= esc($hudLogo, 'attr') ?>" alt="<?= esc(lang('Game.appName'), 'attr') ?>" height="40">
      <?php else: ?>
        <span class="hud-brand"><?= esc(lang('Game.appName')) ?></span>
      <?php endif ?>
    </div>
  <?php else: ?>
    <div class="hud-center"><?= $hudRegion !== null ? esc($hudRegion) : '' ?></div>
  <?php endif ?>

  <div class="hud-right">
    <?= $this->include('components/lang-switch') ?>
    <div class="hud-icons">
      <button type="button" class="icon-btn" id="btn-sound" aria-pressed="true"
              aria-label="<?= esc(lang('Game.sound'), 'attr') ?>">
        <?= icon('sound', 'icon-on') ?><?= icon('sound-off', 'icon-off') ?>
      </button>
      <button type="button" class="icon-btn hud-fullscreen" id="btn-fullscreen" aria-pressed="false" hidden
              aria-label="<?= esc(lang('Game.fullscreen'), 'attr') ?>" title="<?= esc(lang('Game.fullscreen'), 'attr') ?>">
        <?= icon('fullscreen', 'icon-enter') ?><?= icon('fullscreen-exit', 'icon-exit') ?>
      </button>
      <?php if ($inGame): ?>
        <a class="icon-btn hud-leave" href="<?= base_url('peta') ?>"
           aria-label="<?= esc(lang('Game.mapKedu'), 'attr') ?>" title="<?= esc(lang('Game.mapKedu'), 'attr') ?>"><?= icon('home') ?></a>
      <?php endif ?>
    </div>
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
  </div>
</header>
