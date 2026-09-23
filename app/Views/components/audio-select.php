<?php
/**
 * Pemilih audio narasi (audio_assets) untuk slide dialog dan kartu misi.
 * Audio berstatus selain `disetujui` boleh dipasang lebih dulu; pemain baru
 * mendengarnya setelah disetujui di halaman Audio.
 *
 * @var string      $id
 * @var string      $name
 * @var string      $label
 * @var int|null    $audioId  audio terpasang
 * @var string|null $locale   id | en — audio berbahasa ini didahulukan
 */
$locale ??= null;
$status = ['draft' => 'draf', 'review' => 'ditinjau', 'approved' => 'disetujui', 'rejected' => 'ditolak'];
$rows   = audio_catalog();

if ($locale !== null) {
    uasort($rows, static fn (array $a, array $b): int => [$a['locale'] !== $locale, $a['context_code']] <=> [$b['locale'] !== $locale, $b['context_code']]);
}
?>
<div class="field">
  <label for="<?= esc($id, 'attr') ?>"><?= esc($label) ?></label>
  <select id="<?= esc($id, 'attr') ?>" name="<?= esc($name, 'attr') ?>">
    <option value="">— tanpa audio —</option>
    <?php foreach ($rows as $audio): ?>
      <option value="<?= esc($audio['id'], 'attr') ?>" <?= (int) $audioId === (int) $audio['id'] ? 'selected' : '' ?>>
        <?= esc(strtoupper((string) $audio['locale']) . ' · ' . $audio['context_code'] . ' · ' . $audio['asset_key'] . ' (' . ($status[$audio['approval_status']] ?? $audio['approval_status']) . ')') ?>
      </option>
    <?php endforeach ?>
  </select>
</div>
