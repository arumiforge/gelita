<?php
/**
 * Salinan data satu anak — `/admin/peserta/salinan` (30 §8, K2). The choice
 * for the ang-21–ang-23 answers shows only when the child has them.
 *
 * @var string                    $code
 * @var array<string, mixed>|null $found
 * @var bool                      $hasRisk
 */
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', ['title' => lang('Admin.copyTitle'), 'lead' => lang('Admin.copyLead')]) ?>
<?= $this->include('partials/flash') ?>

<form method="get" action="<?= base_url('admin/peserta/salinan') ?>" class="form form-inline">
  <div class="field"><label for="copy-code">Kode peserta</label> <input type="text" id="copy-code" name="kode" value="<?= esc($code, 'attr') ?>" required></div>
  <button class="btn btn-sm" type="submit"><?= esc(lang('Admin.requestLookup')) ?></button>
</form>

<?php if ($code !== '' && $found === null): ?>
  <div class="alert alert-error" role="alert"><?= icon('warn') ?><p><?= esc(lang('Admin.resetCodeNotFound')) ?></p></div>
<?php elseif ($found !== null): ?>
  <form method="post" action="<?= base_url('admin/peserta/salinan') ?>" class="form">
    <?= csrf_field() ?>
    <input type="hidden" name="participant_code" value="<?= esc($found['participant_code'], 'attr') ?>">
    <p>Kode peserta <b class="mono"><?= esc($found['participant_code']) ?></b></p>
    <fieldset class="field"><legend><?= esc(lang('Admin.copyPurpose')) ?></legend>
      <label class="check"><input type="radio" name="purpose" value="view" checked> <span><?= esc(lang('Admin.copyPurposeView')) ?></span></label>
      <label class="check"><input type="radio" name="purpose" value="copy"> <span><?= esc(lang('Admin.copyPurposeCopy')) ?></span></label>
    </fieldset>
    <?php if ($hasRisk): ?>
      <fieldset class="field"><legend><?= esc(lang('Admin.copyRiskLegend')) ?></legend>
        <p class="field-help"><?= esc(lang('Admin.copyRiskHelp')) ?></p>
        <?php foreach (['none' => 'copyRiskNone', 'child_agreed' => 'copyRiskChildAgreed', 'parent_insisted' => 'copyRiskParentInsisted'] as $value => $key): ?>
          <label class="check"><input type="radio" name="risk_answers" value="<?= $value ?>" <?= $value === 'none' ? 'checked' : '' ?>> <span><?= esc(lang('Admin.' . $key)) ?></span></label>
        <?php endforeach ?>
      </fieldset>
    <?php endif ?>
    <div class="form-actions"><button class="btn btn-primary" type="submit"><?= icon('download') ?> <?= esc(lang('Admin.copyDownload')) ?></button></div>
  </form>
<?php endif ?>
<?= $this->endSection() ?>
