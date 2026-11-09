<?php
/**
 * Examples before a part (34 §7.3 step 2): marked Contoh, no question list,
 * answers saved but not scored. The button starts the part (and its time block).
 *
 * @var string                     $part     mb, ae, bd
 * @var array<string, mixed>       $partMeta no, title, intro
 * @var list<array<string, mixed>> $examples items with their stimulus
 * @var array<string, ?string>     $values
 * @var array<string, mixed>       $meta
 */
?>
<?= $this->extend('layouts/instrument') ?>
<?= $this->section('content') ?>
<h1 class="h3"><?= esc((string) $partMeta['title']) ?></h1>
<?php if (! empty($partMeta['intro'])): ?><p><?= instrument_inline((string) $partMeta['intro']) ?></p><?php endif ?>
<p class="text-body-secondary"><?= esc(lang('Instrument.exampleLead')) ?></p>
<form method="post" action="<?= esc(site_url('tes/bagian/' . $part . '/mulai'), 'attr') ?>">
  <?= csrf_field() ?>
  <?php $shown = [] ?>
  <?php foreach ($examples as $example): ?>
    <?php if ($example['stimulus'] !== null && ! in_array($example['stimulus']['id'], $shown, true)): ?>
      <?php $shown[] = $example['stimulus']['id'] ?>
      <?= component('instrument/_stimulus', ['stimulus' => $example['stimulus'], 'lead' => null]) ?>
    <?php endif ?>
    <div class="card card-body mb-3">
      <span class="badge text-bg-secondary align-self-start mb-2"><?= esc(lang('Instrument.example')) ?></span>
      <?= component('instrument/_question', ['item' => $example['item'], 'values' => $values, 'meta' => $meta, 'hint' => true]) ?>
    </div>
  <?php endforeach ?>
  <button type="submit" class="btn btn-primary btn-lg"><?= esc(lang('Instrument.startPart', [$partMeta['no']])) ?></button>
</form>
<?= $this->endSection() ?>
