<?php
/**
 * 1. Welcome — `/` → HomeController::index
 *
 * @var bool                          $isLoggedIn
 * @var list<App\Entities\Level>      $levels
 * @var string                        $locale
 */
$logo = media_key_src('ui.logo');
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('background') ?><?= media_key_src('bg.welcome') ?? '' ?><?= $this->endSection() ?>
<?= $this->section('bodyClass') ?>is-welcome<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="screen welcome">
  <header class="welcome-hero">
    <?php if ($logo !== null): ?>
      <h1 class="welcome-logo"><img src="<?= esc($logo, 'attr') ?>" alt="<?= esc(lang('Game.appName'), 'attr') ?>" width="560"></h1>
    <?php else: ?>
      <h1 class="welcome-title"><?= esc(lang('Game.appName')) ?></h1>
    <?php endif ?>
    <p class="welcome-tagline"><?= esc(lang('Game.tagline')) ?></p>
  </header>

  <div class="welcome-stage">
    <?= component('character', ['character' => 'jaka', 'pose' => 'idle', 'class' => 'welcome-jaka']) ?>
    <?= component('narration', ['text' => lang('Game.welcomeLead'), 'speaker' => 'jaka']) ?>
  </div>

  <div class="welcome-actions">
    <?php if ($isLoggedIn): ?>
      <a class="btn btn-primary btn-xl" href="<?= base_url('peta') ?>"><?= icon('map') ?> <?= esc(lang('Game.continueJourney')) ?></a>
    <?php else: ?>
      <a class="btn btn-primary btn-xl" href="<?= base_url('mulai') ?>"><?= esc(lang('Game.start')) ?> <?= icon('right') ?></a>
      <a class="btn btn-ghost btn-lg" href="<?= base_url('masuk') ?>"><?= esc(lang('Game.login')) ?></a>
    <?php endif ?>
  </div>

  <?php /* Tawaran memasang di layar utama; game/install.js yang memutuskan tampil atau tidak */ ?>
  <div class="welcome-install" data-install hidden>
    <button type="button" class="btn btn-ghost" data-install-button hidden><?= icon('download') ?> <?= esc(lang('Game.installApp')) ?></button>
    <p class="welcome-install-hint" data-install-hint hidden><?= esc(lang('Game.installHint')) ?></p>
    <p class="welcome-install-hint" data-install-ios hidden><?= icon('info') ?> <span><?= esc(lang('Game.installIos')) ?></span></p>
  </div>

  <?php if ($levels !== []): ?>
    <ol class="welcome-regions" aria-label="<?= esc(lang('Game.mapKedu'), 'attr') ?>">
      <?php foreach ($levels as $level): ?>
        <li><span class="region-seq num"><?= esc($level->sequence) ?></span> <?= esc($level->text('name', $locale)) ?></li>
      <?php endforeach ?>
    </ol>
  <?php endif ?>

  <a class="welcome-staff" href="<?= base_url('admin/login') ?>"><?= icon('key') ?> <?= esc(lang('Game.staffLink')) ?></a>
</section>
<?= $this->endSection() ?>
