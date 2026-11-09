<?php
/**
 * Part 9 (35 §10.4, §11, §14): the derived data layer of one study: run
 * time, dictionary version, thresholds, stale or failed, *Hitung ulang*
 * (`research.export`), and *Kunci ambang* (`studies.manage`). Shown on
 * `/admin/studi` and above the derived tabs of `/admin/data`.
 *
 * @var array<string, mixed> $study
 * @var string               $back  path to return to after a button
 */
$sid    = (int) $study['id'];
$status = service('derivedData')->status($sid);
$run    = $status['current'];
$latest = $status['latest'];
$sec    = static fn ($ms): string => rtrim(rtrim(number_format(((int) $ms) / 1000, 1, ',', ''), '0'), ',');
$back ??= 'admin/studi';
?>
<section class="form-section" data-derived-status="<?= $sid ?>">
  <h3><?= esc(lang('Admin.derivedTitle')) ?></h3>
  <?php if (! $status['field_mode']): ?>
    <p class="muted"><?= esc(lang('Admin.derivedOffForStudy')) ?></p>
  <?php else: ?>
    <?php if (staff_can('research.export')): ?>
      <?php if ($run === null): ?>
        <p class="muted"><?= esc(lang('Admin.derivedNone')) ?></p>
      <?php else: ?>
        <p><?= esc(lang('Admin.derivedLastRun', [fmt_date($run['finished_at'] ?? $run['started_at']), $run['dictionary_version'], $sec($run['idle_threshold_ms']), $sec($run['rapid_guess_ms'])])) ?></p>
      <?php endif ?>
      <?php if ($status['draft']): ?>
        <p class="muted"><?= esc(lang('Admin.derivedDraftVersion')) ?></p>
      <?php endif ?>
      <?php if ($status['running']): ?>
        <p class="alert alert-info"><?= esc(lang('Admin.derivedRunning')) ?></p>
      <?php elseif ($latest !== null && $latest['status'] === 'failed' && ($run === null || (int) $latest['id'] > (int) $run['id'])): ?>
        <p class="alert alert-danger"><?= esc(lang('Admin.derivedFailed', [(string) $latest['error']])) ?></p>
      <?php elseif ($status['stale'] && $run !== null): ?>
        <p class="alert alert-warning" data-derived-stale><?= esc(lang('Admin.derivedStale')) ?></p>
      <?php endif ?>
      <form method="post" action="<?= base_url('admin/studi/' . $sid . '/hitung-ulang') ?>" class="form-actions">
        <?= csrf_field() ?>
        <input type="hidden" name="back" value="<?= esc($back, 'attr') ?>">
        <button class="btn" type="submit" data-derived-recompute><?= icon('replay') ?> <?= esc(lang('Admin.derivedRecompute')) ?></button>
      </form>
    <?php endif ?>
    <?php if (staff_can('studies.manage') && $back === 'admin/studi'): ?>
      <h4><?= esc(lang('Admin.studyThresholds')) ?></h4>
      <?php if (! empty($study['thresholds_locked_at'])): ?>
        <p class="filter-locked"><?= icon('lock') ?> <?= esc(lang('Admin.studyThresholdsLockedOn', [fmt_date($study['thresholds_locked_at'])])) ?></p>
      <?php else: ?>
        <p class="muted"><?= esc(lang('Admin.studyThresholdsLockLead')) ?></p>
        <form method="post" action="<?= base_url('admin/studi/' . $sid . '/ambang/kunci') ?>" class="form-actions">
          <?= csrf_field() ?>
          <button class="btn" type="submit" data-thresholds-lock
                  data-confirm="<?= esc(lang('Admin.studyThresholdsLock'), 'attr') ?>?" data-confirm-text="<?= esc(lang('Admin.studyThresholdsLockConfirm'), 'attr') ?>" data-confirm-ok="<?= esc(lang('Admin.studyThresholdsLock'), 'attr') ?>"><?= icon('lock') ?> <?= esc(lang('Admin.studyThresholdsLock')) ?></button>
        </form>
      <?php endif ?>
    <?php endif ?>
  <?php endif ?>
</section>
