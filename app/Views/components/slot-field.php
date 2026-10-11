<?php
/**
 * Upload field for an image slot the game reads by a fixed key (display plan
 * Phase C: map.pin.{wilayah}, map.lantern.{wilayah}, bg.loading.{wilayah}).
 * Unlike media-field there is no code box: the key cannot change, otherwise
 * the game would not find the picture. Removing it is done in Gambar & mockup
 * (Nonaktifkan). The parent form must be multipart.
 *
 * @var string      $id
 * @var string      $label
 * @var string      $assetKey
 * @var string      $fileName  name of the file input
 * @var string|null $help
 */
$help ??= null;
$row  = model(App\Models\MediaAssetModel::class)->findByKey($assetKey);
$src  = $row !== null && (int) $row['is_active'] === 1
    ? base_url(App\Libraries\MediaStore::versionedPath((string) $row['storage_path'], $row['sha256'] ?? null)) : null;
$size = (new App\Libraries\MediaStore())->requiredSize($assetKey);
$types = App\Libraries\ImageSlots::types($assetKey) ?? ['png', 'jpg', 'webp'];
?>
<div class="field media-field">
  <span class="label" id="<?= esc($id, 'attr') ?>-label"><?= esc($label) ?></span>
  <div class="media-field-body">
    <?php if ($src !== null): ?>
      <a class="media-thumb" href="<?= esc($src, 'attr') ?>" target="_blank" rel="noopener" data-zoom="<?= esc($src, 'attr') ?>" data-zoom-caption="<?= esc($assetKey, 'attr') ?>" aria-label="<?= esc('Perbesar ' . $label, 'attr') ?>">
        <img src="<?= esc($src, 'attr') ?>" alt="" loading="lazy" decoding="async">
      </a>
    <?php else: ?>
      <span class="media-thumb is-empty"><?= icon('image') ?></span>
    <?php endif ?>
    <div class="media-field-inputs">
      <code><?= esc($assetKey) ?></code>
      <input type="file" id="<?= esc($id, 'attr') ?>-file" name="<?= esc($fileName, 'attr') ?>"
             accept="<?= esc(implode(',', array_map(static fn (string $t): string => App\Libraries\ImageSlots::TYPE_MIMES[$t], $types)), 'attr') ?>"
             aria-labelledby="<?= esc($id, 'attr') ?>-label">
    </div>
  </div>
  <p class="field-help">
    <?= $src === null ? '<b>Belum ada gambar</b>; permainan memakai gambar pengganti.' : 'Pilih berkas baru untuk mengganti gambar ini.' ?>
    <?= esc(App\Libraries\ImageSlots::typesText($types)) ?><?= $size !== null ? ', paling kecil ' . $size[0] . ' × ' . $size[1] . ' px' : '' ?>.
    <?= $help !== null ? esc($help) : '' ?>
  </p>
</div>
