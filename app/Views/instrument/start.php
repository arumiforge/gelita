<?php
/**
 * Opening screen of the test (34 §5.3, §7.3 step 1; 13 → Petunjuk di layar pembuka).
 *
 * @var array<string, mixed> $meta    test version meta (intro)
 * @var bool                 $ready   the researcher started the test and the student has a form
 * @var bool                 $started the researcher started the test
 * @var bool                 $preview validators' preview: choose occasion and form
 * @var string               $occasion
 * @var bool                 $missing no test content imported yet (gelita:instrumen:impor)
 */
?>
<?= $this->extend('layouts/instrument') ?>
<?= $this->section('content') ?>
<div class="mx-auto" style="max-width: 46rem" data-status-url="<?= esc(site_url('api/instrument/status'), 'attr') ?>">
  <h1 class="h3 mb-3"><?= esc(lang('Instrument.introTitle')) ?></h1>
  <?php if (! empty($missing)): ?>
    <p class="alert alert-info" role="status"><?= esc(lang('Instrument.notReady')) ?></p>
  <?php else: ?>
  <p><?= esc((string) ($meta['intro']['lead'] ?? '')) ?></p>
  <ol>
    <?php foreach ((array) ($meta['intro']['steps'] ?? []) as $step): ?><li class="mb-1"><?= esc((string) $step) ?></li><?php endforeach ?>
  </ol>
  <form method="post" action="<?= esc(site_url('tes/mulai'), 'attr') ?>" class="mt-4">
    <?= csrf_field() ?>
    <?php if ($preview): ?>
      <div class="alert alert-secondary"><?= esc(lang('Instrument.previewLead')) ?></div>
      <div class="row g-3 mb-3">
        <div class="col-sm-6">
          <label class="form-label" for="preview-occasion"><?= esc(lang('Instrument.previewOccasion')) ?></label>
          <select class="form-select" id="preview-occasion" name="occasion">
            <option value="pre"<?= $occasion === 'pre' ? ' selected' : '' ?>><?= esc(lang('Instrument.barPre')) ?></option>
            <option value="post"<?= $occasion === 'post' ? ' selected' : '' ?>><?= esc(lang('Instrument.barPost')) ?></option>
          </select>
        </div>
        <div class="col-sm-6">
          <label class="form-label" for="preview-form"><?= esc(lang('Instrument.previewForm')) ?></label>
          <select class="form-select" id="preview-form" name="form">
            <option value="A"><?= esc(lang('Instrument.formA')) ?></option>
            <option value="B"><?= esc(lang('Instrument.formB')) ?></option>
          </select>
        </div>
      </div>
    <?php elseif (! $ready): ?>
      <p class="alert alert-warning" role="status"><?= esc(lang($started ? 'Instrument.waitForm' : 'Instrument.waitStart')) ?></p>
    <?php endif ?>
    <button type="submit" class="btn btn-primary btn-lg"<?= $ready || $preview ? '' : ' disabled' ?>><?= esc(lang('Instrument.start')) ?></button>
    <?php if (! $ready && ! $preview): ?>
      <a class="btn btn-outline-secondary btn-lg ms-2" href="<?= esc(site_url('tes'), 'attr') ?>"><?= esc(lang('Instrument.reload')) ?></a>
    <?php endif ?>
  </form>
  <?php endif ?>
</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<?php if (! $ready && ! $preview && empty($missing)): ?><script src="<?= asset_url_versioned('js/instrument/status.js') ?>" defer></script><?php endif ?>
<?= $this->endSection() ?>
