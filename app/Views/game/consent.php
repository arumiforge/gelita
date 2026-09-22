<?php
/**
 * 3. Persetujuan — `/persetujuan` → RegisterController::consent
 *
 * Teks yang ditampilkan sama persis dengan yang disimpan bersama peserta
 * (participant_consents.consent_text), beserta versinya. Tombol Lanjut tampak
 * nonaktif selama kotak wajib belum dicentang (:invalid, tanpa JavaScript);
 * atribut `required` dan validasi server tetap menjadi penjaganya.
 *
 * @var string                $consentText
 * @var string                $consentVersion
 * @var bool                  $requireConsent
 * @var array<string, string> $errors
 */
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc(lang('Game.consentTitle')) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= media_key_src('bg.welcome') ?? '' ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="screen screen-narrow consent">
  <div class="panel-parchment">
    <span class="eyebrow"><?= esc(lang('Game.appName')) ?></span>
    <h1><?= esc(lang('Game.consentTitle')) ?></h1>

    <?= $this->include('partials/form-errors') ?>

    <div class="consent-text">
      <p><?= esc($consentText) ?></p>
    </div>

    <form method="post" action="<?= base_url('persetujuan') ?>" class="form consent-form">
      <?= csrf_field() ?>

      <label class="check<?= isset($errors['participant_agree']) ? ' has-error' : '' ?>">
        <input type="checkbox" name="participant_agree" value="1" required>
        <span><?= esc(lang('Game.consentParticipant')) ?> <span class="req" aria-hidden="true">*</span></span>
      </label>

      <label class="check<?= isset($errors['guardian_agree']) ? ' has-error' : '' ?>">
        <input type="checkbox" name="guardian_agree" value="1" <?= $requireConsent ? 'required' : '' ?>>
        <span><?= esc(lang('Game.consentGuardian')) ?><?php if ($requireConsent): ?> <span class="req" aria-hidden="true">*</span><?php endif ?></span>
      </label>

      <div class="field">
        <label for="guardian_name"><?= esc(lang('Game.consentGuardianName')) ?></label>
        <input type="text" id="guardian_name" name="guardian_name" maxlength="150" autocomplete="off"
               value="<?= esc(old('guardian_name'), 'attr') ?>">
      </div>

      <p class="consent-hint field-help"><?= icon('info') ?> <?= esc(lang('Game.consentTickHint')) ?></p>

      <div class="form-actions">
        <button class="btn btn-primary btn-lg consent-submit" type="submit"><?= esc(lang('Game.continue')) ?> <?= icon('right') ?></button>
      </div>
    </form>

    <p class="consent-version"><small><?= esc(lang('Game.consentVersion', [$consentVersion])) ?></small></p>
  </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('nav') ?>
<?= component('nav-bar', ['nav' => [
    ['label' => lang('Game.back'), 'href' => base_url('mulai'), 'style' => 'quiet', 'arrow' => 'left'],
]]) ?>
<?= $this->endSection() ?>
