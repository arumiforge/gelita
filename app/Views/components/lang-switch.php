<?php
/**
 * Pemilih bahasa ID / EN.
 *
 * Form POST biasa, bukan AJAX: halaman dimuat ulang pada URL yang sama,
 * server merender ulang dengan locale baru, dan progres tetap utuh karena
 * seluruh state ada di database. `redirect_to` divalidasi server lewat
 * safe_internal_url().
 */
$current = service('request')->getLocale();
?>
<form class="lang-switch" method="post" action="<?= base_url('bahasa') ?>"
      aria-label="<?= esc(lang('Game.language'), 'attr') ?>">
  <?= csrf_field() ?>
  <input type="hidden" name="redirect_to" value="<?= esc('/' . uri_string(), 'attr') ?>">
  <?php foreach (config('Gelita')->locales as $code): ?>
    <button type="submit" name="locale" value="<?= esc($code, 'attr') ?>" lang="<?= esc($code, 'attr') ?>"
            class="lang-opt<?= $code === $current ? ' is-active' : '' ?>"
            aria-pressed="<?= $code === $current ? 'true' : 'false' ?>"><?= esc(strtoupper($code)) ?></button>
  <?php endforeach ?>
</form>
