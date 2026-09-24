<?php
/**
 * Daftar asset_key untuk kotak kunci components/media-field. Satu per jenis
 * per halaman: `media-keys-image`, `media-keys-video`, `media-keys-image-video`.
 * Slot yang berkasnya belum diunggah tetap tercantum (bertanda) agar dapat
 * dipasang lebih dulu dan diunggah belakangan.
 *
 * @var list<string> $types
 */
$types ??= ['image'];
$want  = array_map(static fn (string $type): string => $type, $types);
?>
<datalist id="media-keys-<?= esc(implode('-', $types), 'attr') ?>">
  <?php foreach (media_catalog() as $row): ?>
    <?php $type = $row['asset_type'] === 'sprite_frame' ? 'image' : $row['asset_type']; ?>
    <?php if (in_array($type, $want, true)): ?>
      <option value="<?= esc($row['asset_key'], 'attr') ?>"><?= $row['is_active'] ? esc($type) : 'belum ada berkas' ?></option>
    <?php endif ?>
  <?php endforeach ?>
</datalist>
