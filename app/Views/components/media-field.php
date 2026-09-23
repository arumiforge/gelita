<?php
/**
 * Pemilih media untuk editor konten: pratinjau aset terpasang, kotak
 * `asset_key` (dengan daftar aset terdaftar) dan input berkas baru.
 *
 * Aturannya dibaca server di App\Libraries\MediaStore::resolveField():
 * berkas baru disimpan sebagai asset_key yang diketik (atau $defaultKey bila
 * kotak kosong); kotak kunci yang dikosongkan melepas media. Form induknya
 * wajib `enctype="multipart/form-data"`.
 *
 * @var string       $id         awalan id elemen (unik di halaman)
 * @var string       $label
 * @var string       $keyName    nama field POST untuk asset_key
 * @var string       $fileName   nama field berkas
 * @var int|null     $mediaId    media terpasang saat ini
 * @var string       $defaultKey kunci yang dipakai bila berkas diunggah tanpa kunci
 * @var list<string> $types      image | video
 * @var string|null  $help       keterangan tambahan
 * @var string|null  $size       saran ukuran, mis. "1280 × 720 px"
 */
$types    ??= ['image'];
$help     ??= null;
$size     ??= null;
$row      = $mediaId ? (media_catalog()[$mediaId] ?? null) : null;
$accept   = implode(',', array_map(static fn (string $type): string => App\Libraries\MediaStore::ACCEPT[$type] ?? '', $types));
$listId   = 'media-keys-' . implode('-', $types);
$hasFile  = $row !== null && (bool) $row['is_active'];
$isVideo  = $row !== null && $row['asset_type'] === 'video';
?>
<div class="field media-field" data-media-field>
  <span class="label" id="<?= esc($id, 'attr') ?>-label"><?= esc($label) ?></span>
  <div class="media-field-body">
    <span class="media-thumb<?= $hasFile && ! $isVideo ? '' : ' is-empty' ?>" data-media-preview>
      <?php if ($hasFile && ! $isVideo): ?>
        <img src="<?= esc(base_url($row['storage_path']), 'attr') ?>" alt="" loading="lazy" decoding="async">
      <?php else: ?>
        <?= icon($isVideo ? 'video' : 'image') ?>
      <?php endif ?>
    </span>
    <div class="media-field-inputs">
      <input type="text" id="<?= esc($id, 'attr') ?>-key" name="<?= esc($keyName, 'attr') ?>"
             value="<?= esc($row['asset_key'] ?? '', 'attr') ?>" list="<?= esc($listId, 'attr') ?>"
             maxlength="160" spellcheck="false" placeholder="asset_key terdaftar (kosong = tanpa media)"
             aria-labelledby="<?= esc($id, 'attr') ?>-label">
      <input type="file" id="<?= esc($id, 'attr') ?>-file" name="<?= esc($fileName, 'attr') ?>"
             accept="<?= esc($accept, 'attr') ?>" aria-label="<?= esc($label . ' — berkas baru', 'attr') ?>">
    </div>
  </div>
  <p class="field-help">
    <?php if ($row !== null && ! $hasFile): ?><b class="size-bad">Berkas belum diunggah.</b><?php endif ?>
    Pilih aset terdaftar, atau unggah berkas baru<?= $defaultKey !== '' ? ' (tersimpan sebagai <code>' . esc($defaultKey) . '</code> bila kotak kunci kosong)' : '' ?>.
    <?php if ($size !== null): ?>Saran ukuran <?= esc($size) ?>.<?php endif ?>
    <?= $help !== null ? esc($help) : '' ?>
  </p>
</div>
