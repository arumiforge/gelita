<?php
/**
 * Kendali pemutar narasi (game/narrator.js): ◀ · ⏸/▶ · ↻ · ▶ · Otomatis.
 *
 * Tersembunyi sampai narrator.js aktif: tanpa JavaScript, slide berpindah
 * lewat navigasi slide biasa (partials/slide-nav) dan audio lewat
 * <audio controls> bawaan. Sakelar Otomatis menyimpan pilihannya sebagai
 * preferensi perangkat (Storage `narrationAuto`, bawaan menyala).
 */
?>
<div class="narrator-controls" data-narrator-controls role="group" aria-label="<?= esc(lang('Game.narratorControls'), 'attr') ?>" hidden>
  <button type="button" class="icon-btn" data-narrator="prev" aria-label="<?= esc(lang('Game.narratorPrev'), 'attr') ?>"><?= icon('left') ?></button>
  <button type="button" class="icon-btn narrator-toggle" data-narrator="toggle" aria-pressed="false"
          data-label-play="<?= esc(lang('Game.narratorPlay'), 'attr') ?>"
          data-label-pause="<?= esc(lang('Game.narratorPause'), 'attr') ?>"
          aria-label="<?= esc(lang('Game.narratorPlay'), 'attr') ?>"><?= icon('play', 'icon-play') ?><?= icon('pause', 'icon-pause') ?></button>
  <button type="button" class="icon-btn" data-narrator="replay" aria-label="<?= esc(lang('Game.narratorReplay'), 'attr') ?>" hidden><?= icon('replay') ?></button>
  <button type="button" class="icon-btn" data-narrator="next" aria-label="<?= esc(lang('Game.narratorNext'), 'attr') ?>"><?= icon('right') ?></button>
  <button type="button" class="narrator-auto" data-narrator="auto" aria-pressed="true" title="<?= esc(lang('Game.narratorAutoHint'), 'attr') ?>">
    <span class="narrator-auto-track" aria-hidden="true"><i></i></span><?= esc(lang('Game.narratorAuto')) ?>
  </button>
</div>
