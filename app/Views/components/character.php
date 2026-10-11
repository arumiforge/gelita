<?php
/**
 * Gambar tokoh: Jaka atau Mbah Kedu.
 *
 * Frames come from the official media slots `char.{animation}.{n}` (see
 * Config\Gelita::$characterAnimations and character_frames()). Frame 1 is
 * rendered; with 2 or more frames the figure carries data-frames/-fps/-loop
 * and core/sprite.js plays them. Without any upload the character shows as a
 * round monogram, so the page stays whole without a broken image.
 *
 * @var string      $character  `jaka` | `mbah_kedu`
 * @var string|null $pose       `idle` (default) or a pose from Config\Gelita::$characterPoses (Jaka `happy`, `bow`, …; Mbah Kedu `smile`, …)
 * @var bool|null   $showName   tampilkan papan nama
 * @var string|null $class      kelas tambahan
 */
$character = $character ?? 'jaka';
$pose      = $pose ?? 'idle';
$name      = lang_or('Game.char_' . $character, ucfirst(str_replace('_', ' ', $character)));
$animation = character_frames($character, $pose);
$src       = $animation['frames'][0] ?? null;
$initials  = $character === 'mbah_kedu' ? 'MK' : mb_strtoupper(mb_substr($name, 0, 1));
?>
<figure class="character character-<?= esc($character, 'attr') ?> <?= esc($class ?? '', 'attr') ?>"
        data-character="<?= esc($character, 'attr') ?>" data-pose="<?= esc($pose, 'attr') ?>"<?= character_frames_attr($animation) ?>>
  <?php if ($src !== null): ?>
    <img class="character-img" src="<?= esc($src, 'attr') ?>" alt="<?= esc($name, 'attr') ?>" width="700" height="900">
  <?php else: ?>
    <span class="character-fallback" role="img" aria-label="<?= esc($name, 'attr') ?>"><?= esc($initials) ?></span>
  <?php endif ?>
  <?php if (! empty($showName)): ?>
    <figcaption class="character-name"><?= esc($name) ?></figcaption>
  <?php endif ?>
</figure>
