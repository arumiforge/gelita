<?php
/**
 * A questionnaire section (one of A–H per screen, 34 §7.7) or one risk item
 * (34 §8.1 step 3), no time limit.
 *
 * @var string                     $action     form action path
 * @var string                     $heading
 * @var string|null                $progress   "Bagian 3 dari 8" / "Pertanyaan 1 dari 3"
 * @var string|null                $note       sentence above the items
 * @var string|null                $footnote
 * @var list<array<string, mixed>> $items
 * @var array<string, ?string>     $values
 * @var array<string, mixed>       $meta
 * @var bool                       $hasPrevious
 * @var bool                       $last       the last screen: Selesai
 * @var string|null                $finishAction
 */
?>
<?= $this->extend('layouts/instrument') ?>
<?= $this->section('content') ?>
<div class="mx-auto" style="max-width: 50rem">
  <?php if ($progress !== null): ?><p class="text-body-secondary mb-1"><?= esc($progress) ?></p><?php endif ?>
  <h1 class="h3 mb-3"><?= esc($heading) ?></h1>
  <?php if (! empty($note)): ?><p class="fst-italic"><?= esc($note) ?></p><?php endif ?>
  <form method="post" action="<?= esc(site_url($action), 'attr') ?>">
    <?= csrf_field() ?>
    <?php foreach ($items as $item): ?>
      <?= $this->include('instrument/_question', ['item' => $item, 'values' => $values, 'meta' => $meta, 'hint' => false]) ?>
    <?php endforeach ?>
    <?php if (! empty($footnote)): ?><p class="small text-body-secondary"><?= esc($footnote) ?></p><?php endif ?>
    <div class="d-flex flex-wrap gap-2">
      <?php if ($hasPrevious): ?><button type="submit" name="tujuan" value="sebelumnya" class="btn btn-outline-primary btn-lg"><?= esc(lang('Instrument.previous')) ?></button><?php endif ?>
      <?php if ($last): ?>
        <button type="submit" name="tujuan" value="selesai" class="btn btn-primary btn-lg"<?= ! empty($finishAction) ? ' formaction="' . esc(site_url($finishAction), 'attr') . '"' : '' ?>><?= esc(lang('Instrument.finish')) ?></button>
      <?php else: ?>
        <button type="submit" name="tujuan" value="berikutnya" class="btn btn-primary btn-lg"><?= esc(lang('Instrument.next')) ?></button>
      <?php endif ?>
    </div>
  </form>
</div>
<?= $this->endSection() ?>
