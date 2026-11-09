<?php
/**
 * Running-study package lock (docs/rencana-paket-aset.md §7.2; owner,
 * 9 November 2026): on Rilis and Rilis → Paket aset, which package the
 * running study locked when it started, and a warning while students get
 * another one. Nothing without a running study.
 *
 * @var array<string, mixed>|null $study       the running research study
 * @var string|null               $packageCode package students get now (the active release's)
 */
if (($study ?? null) === null) {
    return;
}

$locked = $study['package_code'] ?? null;
?>
<div class="alert <?= $locked === $packageCode ? 'alert-info' : 'alert-warn' ?>" role="status" data-study-package-lock="<?= esc($locked ?? '', 'attr') ?>">
  <?= icon($locked === $packageCode ? 'lock' : 'warn') ?>
  <div class="alert-body">
    <p>
      <?= esc(lang('Admin.studyPackageRunning', [$study['name']])) ?>
      <?= $locked === null ? esc(lang('Admin.studyPackageNone')) : '<code>' . esc($locked) . '</code>' ?>.
    </p>
    <?php if ($locked !== $packageCode): ?>
      <p><?= esc(lang('Admin.studyPackageDiffers', [$packageCode ?? lang('Admin.releaseNoPackage')])) ?> <?= esc(lang('Admin.studyPackageReturnFree')) ?></p>
    <?php endif ?>
  </div>
</div>
