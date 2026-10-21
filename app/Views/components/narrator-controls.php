<?php
/**
 * Kendali pemutar narasi (game/narrator.js): ◀ · ⏸/▶ · ↻ · ▶ · Otomatis.
 *
 * Tersembunyi sampai narrator.js aktif: tanpa JavaScript, slide berpindah
 * lewat navigasi slide biasa (partials/slide-nav) dan audio lewat
 * <audio controls> bawaan. Sakelar Otomatis menyimpan pilihannya sebagai
 * preferensi perangkat (Storage `narrationAuto`, bawaan menyala).
 *
 * `$nextLabel` (the cinematic intro and ending): ▶ reads "Lanjut" while there
 * is a next slide; it replaces the caption's Lanjut button there.
 *
 * The owner's round gold buttons (21 October 2026, round_icon_src()) draw ◀, ▶
 * (without its "Lanjut" label), ↻, and ⏸ while the narration plays: each
 * button gets class `has-round-art` and its picture as `--round-art`
 * (cinematic.css). ▶ play has no picture and keeps its line icon.
 *
 * @var bool|null $nextLabel
 */
$nextLabel = ! empty($nextLabel);
// ' class="… has-round-art" style="--round-art: url(…)"' for a button, or '' without the picture
$art = static function (string $name, string $class): string {
    $src = round_icon_src($name);

    return $src === null ? ' class="' . $class . '"'
        : ' class="' . $class . ' has-round-art" style="' . esc('--round-art: url("' . str_replace(['"', '\\', "\n"], ['%22', '%5C', ''], $src) . '")', 'attr') . '"';
};
?>
<div class="narrator-controls" data-narrator-controls role="group" aria-label="<?= esc(lang('Game.narratorControls'), 'attr') ?>" hidden>
  <button type="button"<?= $art('back', 'icon-btn') ?> data-narrator="prev" aria-label="<?= esc(lang('Game.narratorPrev'), 'attr') ?>"><?= icon('left') ?></button>
  <button type="button"<?= $art('pause', 'icon-btn narrator-toggle') ?> data-narrator="toggle" aria-pressed="false"
          data-label-play="<?= esc(lang('Game.narratorPlay'), 'attr') ?>"
          data-label-pause="<?= esc(lang('Game.narratorPause'), 'attr') ?>"
          aria-label="<?= esc(lang('Game.narratorPlay'), 'attr') ?>"><?= icon('play', 'icon-play') ?><?= icon('pause', 'icon-pause') ?></button>
  <button type="button"<?= $art('replay', 'icon-btn') ?> data-narrator="replay" aria-label="<?= esc(lang('Game.narratorReplay'), 'attr') ?>" hidden><?= icon('replay') ?></button>
  <?php if ($nextLabel): ?>
    <button type="button" class="icon-btn narrator-next has-label" data-narrator="next" aria-label="<?= esc(lang('Game.continue'), 'attr') ?>"><span class="narrator-next-label" aria-hidden="true"><?= esc(lang('Game.continue')) ?></span><?= icon('right') ?></button>
  <?php else: ?>
    <button type="button"<?= $art('next', 'icon-btn') ?> data-narrator="next" aria-label="<?= esc(lang('Game.narratorNext'), 'attr') ?>"><?= icon('right') ?></button>
  <?php endif ?>
  <button type="button" class="narrator-auto" data-narrator="auto" aria-pressed="true" title="<?= esc(lang('Game.narratorAutoHint'), 'attr') ?>">
    <span class="narrator-auto-track" aria-hidden="true"><i></i></span><?= esc(lang('Game.narratorAuto')) ?>
  </button>
</div>
