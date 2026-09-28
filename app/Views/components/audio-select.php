<?php
/**
 * Pemilih audio narasi (audio_assets) untuk slide dialog dan kartu misi.
 * Audio berstatus selain `disetujui` boleh dipasang lebih dulu; pemain baru
 * mendengarnya setelah disetujui di halaman Audio.
 *
 * Setiap pilihan bertuliskan nama rekaman (asset_key tanpa awalan
 * `audio.narasi.{locale}.` untuk narasi naskah), bahasa, dan status
 * persetujuannya.
 *
 * @var string      $id
 * @var string      $name
 * @var string      $label
 * @var int|null    $audioId  audio terpasang
 * @var string|null $locale   id | en — audio berbahasa ini didahulukan
 */
$locale ??= null;
$rows   = audio_catalog();
$title  = static function (array $audio): string {
    $key    = (string) $audio['asset_key'];
    $prefix = 'audio.narasi.' . $audio['locale'] . '.';

    return str_starts_with($key, $prefix) ? substr($key, strlen($prefix)) : $key;
};

if ($locale !== null) {
    uasort($rows, static fn (array $a, array $b): int => [$a['locale'] !== $locale, $a['context_code']] <=> [$b['locale'] !== $locale, $b['context_code']]);
}
?>
<div class="field">
  <label for="<?= esc($id, 'attr') ?>"><?= esc($label) ?></label>
  <select id="<?= esc($id, 'attr') ?>" name="<?= esc($name, 'attr') ?>">
    <option value="">— tanpa rekaman —</option>
    <?php foreach ($rows as $audio): ?>
      <option value="<?= esc($audio['id'], 'attr') ?>" <?= (int) $audioId === (int) $audio['id'] ? 'selected' : '' ?>>
        <?= esc($title($audio) . ' · ' . admin_label('locale', (string) $audio['locale']) . ' (' . admin_label('approval', (string) $audio['approval_status']) . ')') ?>
      </option>
    <?php endforeach ?>
  </select>
</div>
