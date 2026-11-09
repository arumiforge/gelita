<?php
/**
 * Check before sending a part (34 §7.3 step 4; mk-mt-04).
 *
 * @var string               $part
 * @var array<string, mixed> $partMeta
 * @var list<int>            $missing
 * @var int|null             $remindIn
 * @var int|null             $closeIn
 */
?>
<?= $this->extend('layouts/instrument') ?>
<?= $this->section('content') ?>
<div class="mx-auto" style="max-width: 44rem"<?= $closeIn !== null ? ' data-close-in="' . $closeIn . '" data-remind-in="' . (int) $remindIn . '"' : '' ?>>
  <div class="alert alert-warning instrument-reminder" role="alert" hidden><?= esc(lang('Instrument.reminder')) ?> <button type="button" class="btn btn-sm btn-outline-dark ms-2"><?= esc(lang('Instrument.reminderOk')) ?></button></div>
  <h1 class="h3"><?= esc(lang('Instrument.reviewTitle')) ?></h1>
  <p><?= esc($missing === [] ? lang('Instrument.reviewAll') : lang('Instrument.reviewMissing', [implode(', ', $missing)])) ?></p>
  <p class="fw-bold"><?= esc(lang('Instrument.reviewWarn')) ?></p>
  <div class="d-flex flex-wrap gap-2">
    <a class="btn btn-outline-primary btn-lg" href="<?= esc(site_url('tes/bagian/' . $part . '/' . ($missing[0] ?? 1)), 'attr') ?>"><?= esc(lang('Instrument.backToItems')) ?></a>
    <form method="post" action="<?= esc(site_url('tes/bagian/' . $part . '/kirim'), 'attr') ?>">
      <?= csrf_field() ?>
      <button type="submit" class="btn btn-primary btn-lg"><?= esc(lang('Instrument.sendPart', [$partMeta['no']])) ?></button>
    </form>
  </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<?php if ($closeIn !== null): ?><script src="<?= asset_url_versioned('js/instrument/timer.js') ?>" defer></script><?php endif ?>
<?= $this->endSection() ?>
