<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc(lang('Game.consentTitle')) ?> · GELITA<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="consent">
  <h1><?= esc(lang('Game.consentTitle')) ?></h1>
  <?= $this->include('partials/flash') ?>

  <p class="consent-text"><?= esc($consentText) ?></p>

  <form method="post" action="<?= base_url('persetujuan') ?>" class="form">
    <?= csrf_field() ?>

    <label class="check">
      <input type="checkbox" name="participant_agree" value="1" required>
      <?= esc(lang('Game.consentParticipant')) ?>
    </label>

    <label class="check">
      <input type="checkbox" name="guardian_agree" value="1" <?= $requireConsent ? 'required' : '' ?>>
      <?= esc(lang('Game.consentGuardian')) ?>
    </label>

    <div class="field">
      <label for="guardian_name"><?= esc(lang('Game.consentGuardianName')) ?></label>
      <input type="text" id="guardian_name" name="guardian_name" maxlength="150" value="<?= esc(old('guardian_name')) ?>">
    </div>

    <button class="btn btn-primary" type="submit"><?= esc(lang('Game.continue')) ?></button>
  </form>

  <p><small>Versi teks persetujuan: <?= esc($consentVersion) ?></small></p>
</section>
<?= $this->endSection() ?>
