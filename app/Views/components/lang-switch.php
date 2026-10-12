<?php
/**
 * Pemilih bahasa ID / EN.
 *
 * Form POST biasa, bukan AJAX: halaman dimuat ulang pada URL yang sama,
 * server merender ulang dengan locale baru, dan progres tetap utuh karena
 * seluruh state ada di database. `redirect_to` divalidasi server lewat
 * safe_internal_url().
 *
 * Each option shows its round icon `ui.lang.{code}` from the Katalog Aset
 * (reference.20 ID, reference.19 EN) once both are uploaded; otherwise the
 * text pill ID / EN. The language name stays the button's accessible name.
 * The <img> carries its 32 px display size, so it stays small even while a
 * browser or asset package still serves an older stylesheet.
 */
$current = service('request')->getLocale();
$locales = config('Gelita')->locales;
$slots   = ['id' => media_key_src('ui.lang.id'), 'en' => media_key_src('ui.lang.en')];
$icons   = array_map(static fn (string $code): ?string => $slots[$code] ?? null, array_combine($locales, $locales));

// One missing icon would mix an image with a text pill: use text for both
$withIcons = ! in_array(null, $icons, true);
?>
<form class="lang-switch<?= $withIcons ? ' is-image' : '' ?>" method="post" action="<?= base_url('bahasa') ?>"
      aria-label="<?= esc(lang('Game.language'), 'attr') ?>">
  <?= csrf_field() ?>
  <input type="hidden" name="redirect_to" value="<?= esc('/' . uri_string(), 'attr') ?>">
  <?php foreach ($locales as $code): ?>
    <?php
    $active = $code === $current;
    $name   = lang_or('Game.lang_' . $code, strtoupper($code));
    ?>
    <?php if ($withIcons): ?>
      <button type="submit" name="locale" value="<?= esc($code, 'attr') ?>" lang="<?= esc($code, 'attr') ?>"
              class="lang-opt<?= $active ? ' is-active' : '' ?>" aria-pressed="<?= $active ? 'true' : 'false' ?>"
              aria-label="<?= esc($name, 'attr') ?>" title="<?= esc($name, 'attr') ?>">
        <img src="<?= esc($icons[$code], 'attr') ?>" alt="" width="32" height="32" draggable="false">
      </button>
    <?php else: ?>
      <button type="submit" name="locale" value="<?= esc($code, 'attr') ?>" lang="<?= esc($code, 'attr') ?>"
              class="lang-opt<?= $active ? ' is-active' : '' ?>"
              aria-pressed="<?= $active ? 'true' : 'false' ?>"><?= esc(strtoupper($code)) ?></button>
    <?php endif ?>
  <?php endforeach ?>
</form>
