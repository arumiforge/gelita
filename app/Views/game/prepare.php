<?php
/**
 * Siapkan GELITA — `/siapkan` → PackageController::prepare
 * (docs/rencana-paket-aset.md Stage 3, §5.1, §5.6, §8.1–§8.7, P7, P11).
 *
 * Shown only while package mode is on (the controller redirects otherwise).
 * game/prepare.js downloads the full package (every part, both languages)
 * and keeps Mulai disabled until it is verified on this device; there is no
 * skip button. "Main daring di perangkat ini" appears only where §8.7 allows
 * it: private mode or too little storage, or a lab device when the admin
 * allows online mode for lab devices (inside "Untuk guru").
 *
 * Stage 16:9: one full-width ornate panel 32, 88, 1216 × 600 (stage.css
 * .prepare-panel), no character, so the text and buttons have room (owner,
 * 18 October 2026): the head, the scrolling progress, and the button bar
 * (last 80 dp).
 *
 * `data-state` on the section (checking | downloading | error | ready |
 * online) is for styling and browser checks.
 *
 * @var string                                                $next   page Mulai continues to
 * @var list<array{name: string, label: string, bytes: int}> $groups parts in download order
 */
$mb = static fn (int $bytes): string => fmt_num($bytes / 1048576, 1) . ' MB';
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc(lang('Package.prepareTitle')) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= media_key_src('bg.welcome') ?? '' ?><?= $this->endSection() ?>
<?= $this->section('hudBack') ?><?= base_url() ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="prepare" data-screen="prepare" data-state="checking" data-next="<?= esc(base_url($next), 'attr') ?>">
  <div class="prepare-panel ornate-panel stage-box">
    <?= $this->include('partials/ornate-frame') ?>
    <header class="register-head prepare-head">
      <span class="eyebrow"><?= esc(lang('Package.prepareEyebrow')) ?></span>
      <h1 id="prepare-title"><?= esc(lang('Package.prepareTitle')) ?></h1>
    </header>

    <div class="prepare-body stage-scroll">
      <p class="prepare-lead"><?= esc(lang('Package.prepareLead')) ?></p>

      <div class="prepare-total">
        <div class="prepare-total-head">
          <span id="prepare-total-label"><?= esc(lang('Package.prepareTotal')) ?></span>
          <span class="prepare-size" data-prepare-size></span>
        </div>
        <div class="progress prepare-bar" role="progressbar" aria-labelledby="prepare-total-label"
             aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" data-prepare-bar><i></i></div>
      </div>

      <h2 class="visually-hidden"><?= esc(lang('Package.prepareParts')) ?></h2>
      <ol class="prepare-groups" data-prepare-groups>
        <?php foreach ($groups as $group): ?>
          <li class="prepare-group" data-group="<?= esc($group['name'], 'attr') ?>" data-bytes="<?= (int) $group['bytes'] ?>">
            <span class="prepare-group-name"><?= esc($group['label']) ?></span>
            <span class="progress prepare-group-bar" aria-hidden="true"><i></i></span>
            <span class="prepare-group-state" data-group-state><?= esc(lang('Package.prepareWaiting')) ?> · <?= esc($mb($group['bytes'])) ?></span>
          </li>
        <?php endforeach ?>
      </ol>

      <p class="prepare-status" role="status" aria-live="polite" data-prepare-status><?= esc(lang('Js.packageChecking')) ?></p>

      <noscript>
        <p class="prepare-status"><?= esc(lang('Package.prepareNoScript')) ?> <a href="<?= base_url($next) ?>"><?= esc(lang('Game.start')) ?></a></p>
      </noscript>

      <?php /* Install hint (game/install.js): iPad/iPhone keep the package far better from the Home Screen (§8.1, §8.5) */ ?>
      <div class="prepare-install" data-install hidden>
        <button type="button" class="btn btn-ghost" data-install-button hidden><?= icon('download') ?> <?= esc(lang('Game.installApp')) ?></button>
        <p class="welcome-install-hint" data-install-hint hidden><?= esc(lang('Game.installHint')) ?></p>
        <p class="welcome-install-hint" data-install-ios hidden><?= icon('info') ?> <span><?= esc(lang('Package.prepareInstallIos')) ?></span></p>
      </div>

      <details class="prepare-teacher">
        <summary><?= icon('key') ?> <?= esc(lang('Package.teacherTitle')) ?></summary>
        <div class="prepare-lab" data-prepare-lab hidden>
          <p><?= esc(lang('Package.teacherLab')) ?></p>
          <button type="button" class="btn btn-ghost" data-prepare-online="lab"><?= esc(lang('Package.playOnline')) ?></button>
        </div>
        <p><a href="<?= base_url('periksa-paket') ?>"><?= esc(lang('Package.teacherInspect')) ?> <?= icon('right') ?></a></p>
      </details>
    </div>

    <div class="register-actions prepare-actions">
      <button type="button" class="btn btn-ghost" data-prepare-online="storage" hidden><?= esc(lang('Package.playOnline')) ?></button>
      <button type="button" class="btn btn-ghost" data-prepare-retry hidden><?= icon('replay') ?> <?= esc(lang('Js.tryAgain')) ?></button>
      <button type="button" class="btn btn-primary btn-lg" data-prepare-start disabled><?= esc(lang('Game.start')) ?></button>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
