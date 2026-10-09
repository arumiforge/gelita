<?php
/**
 * Gold ornaments for a dark account panel (`.ornate-panel`): four corner
 * flourishes and the compass crest on the top edge. Purely decorative inline
 * SVG, so it stays sharp at every stage scale and needs no image file.
 */
$corner = '<svg class="ornate-corner %s" viewBox="0 0 48 48" aria-hidden="true" focusable="false">'
    . '<path d="M4 44V12a8 8 0 0 1 8-8h32"/><path d="M10 38V16a6 6 0 0 1 6-6h22"/>'
    . '<path d="M10 22c4 0 6-2 6-6M22 10c0 4-2 6-6 6"/><circle cx="16" cy="16" r="1.6"/></svg>';
?>
<span class="ornate" aria-hidden="true">
  <?php foreach (['is-tl', 'is-tr', 'is-bl', 'is-br'] as $pos): ?>
    <?= sprintf($corner, $pos) ?>
  <?php endforeach ?>
  <svg class="ornate-crest" viewBox="0 0 64 64" focusable="false">
    <circle cx="32" cy="32" r="22"/>
    <circle cx="32" cy="32" r="17"/>
    <path class="is-fill" d="M32 8l4 20-4 4-4-4zM32 56l-4-20 4-4 4 4zM8 32l20-4 4 4-4 4zM56 32l-20 4-4-4 4-4z"/>
    <path d="M32 18l2 12-2 2-2-2zM46 32l-12 2-2-2 2-2zM32 46l-2-12 2-2 2 2zM18 32l12-2 2 2-2 2z" transform="rotate(45 32 32)"/>
  </svg>
</span>
