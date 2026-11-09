<?php
/**
 * A passage, mock screen, or scene (34 §6.1): the `instr.*` picture from the
 * Katalog Aset when installed, else the same text laid out plainly.
 *
 * @var array<string, mixed>|null $stimulus decoded stimulus (show, image_slot)
 * @var string|null               $lead     sentence above the text
 */
if ($stimulus === null) {
    return;
}

$show = $stimulus['show'];
$src  = $stimulus['image_slot'] === null ? null : media_key_src((string) $stimulus['image_slot']);
?>
<section class="instrument-stimulus card card-body mb-3">
  <?php if (! empty($lead)): ?><p class="text-body-secondary"><?= esc($lead) ?></p><?php endif ?>
  <?php if (! empty($show['intro'])): ?><p><?= esc((string) $show['intro']) ?></p><?php endif ?>
  <?php if ($src !== null): ?>
    <img src="<?= esc($src, 'attr') ?>" alt="<?= esc(lang('Instrument.stimulusPicture'), 'attr') ?>">
  <?php else: ?>
    <?= instrument_text((string) ($show['body'] ?? '')) ?>
    <?php if (! empty($show['rows'])): ?>
      <dl class="mb-0">
        <?php foreach ((array) $show['rows'] as [$label, $text]): ?>
          <dt><?= esc((string) $label) ?></dt><dd><?= instrument_inline((string) $text) ?></dd>
        <?php endforeach ?>
      </dl>
    <?php endif ?>
  <?php endif ?>
</section>
