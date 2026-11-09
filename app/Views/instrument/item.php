<?php
/**
 * One test item per page (34 §7.1, §7.3 step 3, K6). Every move (previous,
 * next, a number, check) posts the same form, which saves first. The page
 * carries the hidden limit in seconds for js/instrument/timer.js; no clock.
 *
 * @var string                     $part
 * @var array<string, mixed>       $partMeta
 * @var array<string, mixed>       $item
 * @var array<string, mixed>|null  $stimulus
 * @var string|null                $stimulusLead
 * @var array<string, ?string>     $values
 * @var list<array{no: int, answered: bool}> $nav
 * @var int                        $count
 * @var array<string, mixed>       $meta
 * @var int|null                   $remindIn
 * @var int|null                   $closeIn
 */
$no = (int) $item['position'];
?>
<?= $this->extend('layouts/instrument') ?>
<?= $this->section('content') ?>
<div<?= $closeIn !== null ? ' data-close-in="' . $closeIn . '" data-remind-in="' . (int) $remindIn . '"' : '' ?>>
  <div class="alert alert-warning instrument-reminder" role="alert" hidden>
    <?= esc(lang('Instrument.reminder')) ?>
    <button type="button" class="btn btn-sm btn-outline-dark ms-2"><?= esc(lang('Instrument.reminderOk')) ?></button>
  </div>
  <h1 class="h5 text-body-secondary"><?= esc((string) $partMeta['title']) ?> · <?= esc(lang('Instrument.itemNumber', [$no])) ?></h1>
  <form method="post" action="<?= esc(site_url('tes/bagian/' . $part . '/' . $no), 'attr') ?>">
    <?= csrf_field() ?>
    <div class="row g-4">
      <?php if ($stimulus !== null): ?>
        <div class="col-lg-6"><?= $this->include('instrument/_stimulus', ['stimulus' => $stimulus, 'lead' => $stimulusLead]) ?></div>
      <?php endif ?>
      <div class="<?= $stimulus !== null ? 'col-lg-6' : 'col-12' ?>">
        <?= $this->include('instrument/_question', ['item' => $item, 'values' => $values, 'meta' => $meta, 'hint' => true]) ?>
        <div class="d-flex flex-wrap gap-2">
          <?php if ($no > 1): ?><button type="submit" name="tujuan" value="sebelumnya" class="btn btn-outline-primary btn-lg"><?= esc(lang('Instrument.previous')) ?></button><?php endif ?>
          <?php if ($no < $count): ?>
            <button type="submit" name="tujuan" value="berikutnya" class="btn btn-primary btn-lg"><?= esc(lang('Instrument.next')) ?></button>
          <?php else: ?>
            <button type="submit" name="tujuan" value="periksa" class="btn btn-primary btn-lg"><?= esc(lang('Instrument.checkAnswers')) ?></button>
          <?php endif ?>
        </div>
      </div>
    </div>
    <nav class="instrument-nav mt-4" aria-label="<?= esc(lang('Instrument.itemList'), 'attr') ?>">
      <div class="d-flex flex-wrap gap-2">
        <?php foreach ($nav as $entry): ?>
          <button type="submit" name="tujuan" value="<?= $entry['no'] ?>"
                  class="btn btn-lg <?= $entry['answered'] ? 'btn-outline-secondary' : 'btn-outline-danger is-open' ?><?= $entry['no'] === $no ? ' active' : '' ?>"
                  aria-label="<?= esc(lang('Instrument.itemNumber', [$entry['no']]) . ', ' . lang($entry['answered'] ? 'Instrument.answered' : 'Instrument.unanswered'), 'attr') ?>">
            <?= $entry['no'] ?><?= $entry['answered'] ? '' : ' <small>(' . esc(lang('Instrument.unanswered')) . ')</small>' ?>
          </button>
        <?php endforeach ?>
        <button type="submit" name="tujuan" value="periksa" class="btn btn-lg btn-outline-primary"><?= esc(lang('Instrument.checkAnswers')) ?></button>
      </div>
    </nav>
  </form>
</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<?php if ($closeIn !== null): ?><script src="<?= asset_url_versioned('js/instrument/timer.js') ?>" defer></script><?php endif ?>
<?= $this->endSection() ?>
