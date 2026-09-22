<?php
/**
 * HUD tahap 2: merek + pemilih bahasa (?lang=).
 * Lentera, suara, dan chip peserta ditambahkan pada tahap 5.
 */
$current = service('request')->getLocale();
?>
<header class="hud">
  <div class="hud-left">
    <a class="hud-brand" href="<?= base_url() ?>" aria-label="<?= esc(lang('Game.home')) ?>">GELITA</a>
  </div>
  <div class="hud-right">
    <nav class="lang-switch" aria-label="<?= esc(lang('Game.language')) ?>">
      <?php foreach (config('Gelita')->locales as $code): ?>
        <a class="lang-opt <?= $code === $current ? 'is-active' : '' ?>"
           href="<?= esc(current_url() . '?lang=' . $code) ?>"
           hreflang="<?= esc($code) ?>"
           <?= $code === $current ? 'aria-current="true"' : '' ?>><?= esc(strtoupper($code)) ?></a>
      <?php endforeach ?>
    </nav>
  </div>
</header>
