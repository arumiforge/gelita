<?php
/**
 * Character figure: Jaka or Mbah Kedu.
 *
 * A pose is one still picture, the official media slot
 * `char.{jaka|kedu}.{pose}.1` (character_frame_src(), falling back to `idle`;
 * owner: still pictures only, no frame loop). Without any upload the
 * character shows as a round monogram, so the page stays whole without a
 * broken image.
 *
 * @var string      $character  `jaka` | `mbah_kedu`
 * @var string|null $pose       `idle` (default) or a pose from Config\Gelita::$characterPoses (Jaka `happy`, `bow`, …; Mbah Kedu `smile`, …)
 * @var bool|null   $showName   tampilkan papan nama
 * @var string|null $class      kelas tambahan
 */
$character = $character ?? 'jaka';
$pose      = $pose ?? 'idle';
$name      = lang_or('Game.char_' . $character, ucfirst(str_replace('_', ' ', $character)));
$src       = character_frame_src($character, $pose);
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
