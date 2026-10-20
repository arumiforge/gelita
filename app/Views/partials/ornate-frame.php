<?php
/**
 * Gold ornaments for a dark account panel (`.ornate-panel`): four corner
 * flourishes (inline SVG, sharp at every stage scale) and the compass crest on
 * the top edge, the owner's emblem picture `assets/ui/ornate-crest.webp`
 * (20 October 2026).
 */
$corner = '<svg class="ornate-corner %s" viewBox="0 0 48 48" aria-hidden="true" focusable="false">'
    . '<path d="M4 44V12a8 8 0 0 1 8-8h32"/><path d="M10 38V16a6 6 0 0 1 6-6h22"/>'
    . '<path d="M10 22c4 0 6-2 6-6M22 10c0 4-2 6-6 6"/><circle cx="16" cy="16" r="1.6"/></svg>';
?>
<span class="ornate" aria-hidden="true">
  <?php foreach (['is-tl', 'is-tr', 'is-bl', 'is-br'] as $pos): ?>
    <?= sprintf($corner, $pos) ?>
  <?php endforeach ?>
  <?php // The owner's compass emblem (20 October 2026) replaces the drawn SVG crest ?>
  <img class="ornate-crest" src="<?= esc(asset_url_versioned('ui/ornate-crest.webp'), 'attr') ?>" alt="" width="274" height="320" decoding="async" draggable="false">
</span>
