<?php
/**
 * Opening screen of the questionnaire (16 → Petunjuk untuk siswa).
 *
 * @var array<string, mixed> $meta
 * @var bool                 $preRefused the pre-test was refused after playing (K11)
 */
?>
<?= $this->extend('layouts/instrument') ?>
<?= $this->section('content') ?>
<div class="mx-auto" style="max-width: 46rem">
  <?php if ($preRefused): ?><p class="alert alert-info"><?= esc(lang('Instrument.noPreAfterPlay')) ?></p><?php endif ?>
  <h1 class="h3 mb-3"><?= esc(lang('Instrument.barQuestionnaire')) ?></h1>
  <?php foreach ((array) ($meta['intro']['paragraphs'] ?? []) as $paragraph): ?><p><?= esc((string) $paragraph) ?></p><?php endforeach ?>
  <ol><?php foreach ((array) ($meta['intro']['steps'] ?? []) as $step): ?><li><?= esc((string) $step) ?></li><?php endforeach ?></ol>
  <a class="btn btn-primary btn-lg" href="<?= esc(site_url('tes/angket/1'), 'attr') ?>"><?= esc(lang('Instrument.start')) ?></a>
</div>
<?= $this->endSection() ?>
