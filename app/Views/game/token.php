<?php
/**
 * Session token — `/token` → FieldTokenController::form (research revision 10,
 * part 2: 28 §6.4, mock-up `mk-tk-01`/`mk-tk-02`), and step 0 of `/daftar`
 * in field mode (28 §6.5). A plain form, so it works without JavaScript.
 *
 * @var string|null           $researchCode the student's research code (P2–P5), shown above the box
 * @var string|null           $action       form target (default `token`)
 * @var array<string, string> $errors
 */
$action ??= 'token';
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('music') ?>welcome<?= $this->endSection() ?>
<?= $this->section('title') ?><?= esc(lang('Game.tokenTitle')) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= asset_url_versioned('ui/bg-auth-login.webp') ?><?= $this->endSection() ?>
<?= $this->section('bodyClass') ?>is-auth-art<?= $this->endSection() ?>
<?= $this->section('hudLayout') ?>logo-centre<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="change-password field-token">
  <div class="password-panel ornate-panel stage-box">
    <?= $this->include('partials/ornate-frame') ?>
    <div class="ornate-body stage-scroll">
      <header class="start-head">
        <span class="eyebrow"><?= esc(lang('Game.appName')) ?></span>
        <h1><?= esc(lang('Game.tokenTitle')) ?></h1>
      </header>
      <span class="ornate-divider" aria-hidden="true"></span>

      <?php if (! empty($researchCode)): ?>
        <p class="field-token-code"><?= esc(lang('Game.researchCodeLabel')) ?>: <strong><?= esc($researchCode) ?></strong></p>
      <?php endif ?>

      <form method="post" action="<?= base_url($action) ?>" class="form" autocomplete="off">
        <?= csrf_field() ?>
        <div class="field<?= isset($errors['token']) ? ' has-error' : '' ?>">
          <label for="token"><?= esc(lang('Game.tokenTitle')) ?></label>
          <input type="text" id="token" name="token" required maxlength="12" autocapitalize="characters"
                 autocomplete="off" spellcheck="false" enterkeyhint="go" inputmode="text" class="field-token-input"
                 aria-describedby="token-help">
          <p class="field-help" id="token-help"><?= esc(lang('Game.tokenHelp')) ?></p>
          <?php if (isset($errors['token'])): ?>
            <p class="field-error" role="alert"><?= esc($errors['token']) ?></p>
          <?php endif ?>
        </div>
        <input type="hidden" name="device_json" value="">
        <?php // Part 7 (33 §7.2): width of the 16:9 stage, filled by game/screen-check.js ?>
        <input type="hidden" name="stage_w" value="">
        <div class="form-actions">
          <?= gold_plate_button(lang('Game.next'), 'medium', 'btn btn-primary btn-lg btn-block') ?>
        </div>
      </form>

      <?php if ($action === 'token'): ?>
        <p class="field-token-leave"><a href="<?= base_url('keluar') ?>"><?= esc(lang('Game.logout')) ?></a></p>
      <?php endif ?>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
