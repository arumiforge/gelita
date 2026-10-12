<?php
/**
 * Changed-asset notice (docs/rencana-paket-aset.md §6.3) on Rilis → Paket
 * aset, Gambar & mockup, and Rekaman narasi: images or audio changed since
 * the package students have, with a **Bangun ulang paket** button that starts
 * a build and opens the package page, which drives it.
 *
 * Shown only while a release names a package and something changed.
 *
 * @var array{count: int, package_code: string|null}|null $packageChanged AssetPackageService::changedSinceActive()
 */
$changed = $packageChanged ?? null;

if (! is_array($changed) || $changed['package_code'] === null || $changed['count'] === 0) {
    return;
}
?>
<div class="alert alert-warn package-notice" role="status" data-package-notice>
  <?= icon('warn') ?>
  <div class="alert-body">
    <p><b><?= esc(lang('Admin.packageChangedNotice', [fmt_num($changed['count'])])) ?></b></p>
    <p class="muted"><?= esc(lang('Admin.packageChangedHelp')) ?></p>
  </div>
  <form method="post" action="<?= base_url('admin/studi/rilis/paket/bangun') ?>" class="inline-form">
    <?= csrf_field() ?>
    <button class="btn btn-sm btn-primary" type="submit"><?= icon('replay') ?> <?= esc(lang('Admin.packageRebuild')) ?></button>
  </form>
</div>
