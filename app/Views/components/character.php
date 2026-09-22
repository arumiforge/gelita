<?php
/**
 * Gambar tokoh: Jaka atau Mbah Kedu.
 *
 * Frame diambil dari slot media resmi `char.{animasi}.{n}` (lihat
 * Config\Gelita::$characterAnimations). Bila berkas belum diunggah, tokoh
 * tampil sebagai monogram bundar — halaman tetap utuh tanpa kotak rusak.
 *
 * @var string      $character  `jaka` | `mbah_kedu`
 * @var string|null $pose       `idle` (bawaan), `happy`, `bow`
 * @var bool|null   $showName   tampilkan papan nama
 * @var string|null $class      kelas tambahan
 */
$character = $character ?? 'jaka';
$pose      = $pose ?? 'idle';
$slug      = $character === 'mbah_kedu' ? 'kedu' : $character;
$name      = lang_or('Game.char_' . $character, ucfirst(str_replace('_', ' ', $character)));
$src       = media_key_src("char.{$slug}.{$pose}.1") ?? media_key_src("char.{$slug}.idle.1");
$initials  = $character === 'mbah_kedu' ? 'MK' : mb_strtoupper(mb_substr($name, 0, 1));
?>
<figure class="character character-<?= esc($character, 'attr') ?> <?= esc($class ?? '', 'attr') ?>"
        data-character="<?= esc($character, 'attr') ?>" data-pose="<?= esc($pose, 'attr') ?>">
  <?php if ($src !== null): ?>
    <img class="character-img" src="<?= esc($src, 'attr') ?>" alt="<?= esc($name, 'attr') ?>" width="700" height="900">
  <?php else: ?>
    <span class="character-fallback" role="img" aria-label="<?= esc($name, 'attr') ?>"><?= esc($initials) ?></span>
  <?php endif ?>
  <?php if (! empty($showName)): ?>
    <figcaption class="character-name"><?= esc($name) ?></figcaption>
  <?php endif ?>
</figure>
