<?php
/**
 * Part 7 (33 §6, §10): "Pengaturan pengambilan data" of one study, below its
 * form on `/admin/studi`; POST /admin/studi/{id}/pengaturan
 * (StudyController::updateSettings, `studies.manage`). Seconds and pixels,
 * never milliseconds. Once part 9 locks the thresholds
 * (`thresholds_locked_at`) they are read-only and only the stage width
 * stays editable. The admin sees the values with the form locked.
 *
 * @var array<string, mixed> $study
 */
$sid      = (int) $study['id'];
$settings = \App\Services\GameContext::studySettings($study);
$locked   = ! empty($study['thresholds_locked_at']);
$canEdit  = staff_can('studies.manage');
$seconds  = static fn (int $ms): string => rtrim(rtrim(number_format($ms / 1000, 1, '.', ''), '0'), '.');
$fields   = [
    ['idle_threshold_s', 'Admin.studyIdleThreshold', 'Admin.studyIdleThresholdHelp', $seconds($settings['idle_threshold_ms']), '30', '1800', '1', $locked],
    ['rapid_guess_s', 'Admin.studyRapidGuess', 'Admin.studyRapidGuessHelp', $seconds($settings['rapid_guess_ms']), '0.5', '10', '0.1', $locked],
    ['test_rapid_guess_s', 'Admin.studyTestRapidGuess', 'Admin.studyTestRapidGuessHelp', $seconds($settings['test_rapid_guess_ms']), '0.5', '10', '0.1', $locked],
    ['min_stage_px', 'Admin.studyMinStage', 'Admin.studyMinStageHelp', (string) $settings['min_stage_px'], '640', '1280', '1', false],
];
?>
<form method="post" action="<?= base_url('admin/studi/' . $sid . '/pengaturan') ?>" class="form-section" data-study-settings="<?= $sid ?>">
  <?= csrf_field() ?>
  <h3><?= esc(lang('Admin.studySettingsTitle')) ?></h3>
  <p class="muted"><?= esc(lang('Admin.studySettingsLead')) ?></p>
  <?php if ($locked): ?>
    <p class="filter-locked"><?= icon('lock') ?> <?= esc(lang('Admin.studyThresholdsLocked')) ?></p>
  <?php endif ?>
  <fieldset class="form-grid"<?= $canEdit ? '' : ' disabled' ?>>
    <?php foreach ($fields as [$name, $label, $help, $value, $min, $max, $step, $readonly]): ?>
      <div class="field">
        <label for="s<?= $sid ?>-<?= $name ?>"><?= esc(lang($label)) ?></label>
        <input type="number" id="s<?= $sid ?>-<?= $name ?>" name="<?= $readonly ? '' : $name ?>" value="<?= esc($value, 'attr') ?>"
               min="<?= $min ?>" max="<?= $max ?>" step="<?= $step ?>"<?= $readonly ? ' readonly aria-readonly="true"' : '' ?>>
        <p class="field-help"><?= esc(lang($help)) ?></p>
      </div>
    <?php endforeach ?>
  </fieldset>
  <?php if ($canEdit): ?>
    <div class="form-actions">
      <button class="btn btn-primary" type="submit"><?= icon('check') ?> <?= esc(lang('Admin.studySettingsSave')) ?></button>
    </div>
  <?php endif ?>
</form>
