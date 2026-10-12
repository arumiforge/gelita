<?php
/**
 * Running-study confirmation for a package change (docs/rencana-paket-aset.md
 * §7.2, owner decision P10): a tick and a reason, both required by the
 * server (ReleaseActivation) and recorded in the audit log. Rendered inside
 * the form that switches package mode or activates a release.
 *
 * @var array<string, mixed> $study the running research study
 * @var string               $idPrefix unique prefix for the field ids
 */
$p = esc($idPrefix ?? 'confirm', 'attr');
?>
<div class="alert alert-warn study-confirm">
  <?= icon('warn') ?>
  <div class="alert-body">
    <p><?= esc(lang('Admin.packageRunningStudy', [$study['name']])) ?></p>
    <label class="check" for="<?= $p ?>-confirm">
      <input type="checkbox" id="<?= $p ?>-confirm" name="confirm_running_study" value="1" required>
      <span><?= esc(lang('Admin.packageConfirmStudy')) ?></span>
    </label>
    <div class="field">
      <label for="<?= $p ?>-reason"><?= esc(lang('Admin.packageReason')) ?> <span class="req">*</span></label>
      <textarea id="<?= $p ?>-reason" name="reason" rows="2" maxlength="500" required placeholder="<?= esc(lang('Admin.packageReasonHint'), 'attr') ?>"></textarea>
    </div>
  </div>
</div>
