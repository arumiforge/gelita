<?php
/**
 * Staff package preview pill (docs/rencana-paket-aset.md §6.1 step 3, §9.2;
 * owner, 9 November 2026): shown only while the signed-in staff member
 * previews a package with `?paket={code}` (AssetPackageService::previewCode()),
 * never to students. "Akhiri pratinjau" reloads the page with `?paket=mati`.
 */
try {
    $previewCode = service('assetPackages')->previewCode();
} catch (Throwable) {
    $previewCode = null;
}

if ($previewCode === null) {
    return;
}
?>
<div class="package-preview" role="status" data-package-preview="<?= esc($previewCode, 'attr') ?>">
  <p><?= esc(lang('Game.packagePreview', [$previewCode])) ?></p>
  <a href="<?= esc(current_url() . '?paket=mati', 'attr') ?>"><?= esc(lang('Game.packagePreviewEnd')) ?></a>
</div>
