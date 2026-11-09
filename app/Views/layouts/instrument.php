<?php
/**
 * Layout of the test module, questionnaire, and risk items (research
 * revision 10, part 8: 34 §7.1). Bootstrap 5.3 CSS from the local copy
 * (`public/assets/vendor/bootstrap-5.3.8/`), no CDN, no Bootstrap JavaScript.
 * No HUD, music, narration, screen effects, parallax, Howler, or cine.js.
 *
 * Sections: title, content, scripts.
 *
 * @var CodeIgniter\View\View $this
 * @var string|null           $bar          top bar label (Tes awal, Tes akhir, Angket, …)
 * @var string|null           $researchCode the student's code; never the name
 */
$title = trim($this->renderSection('title'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <title><?= esc($title !== '' ? $title : lang('Instrument.moduleTitle')) ?> · GELITA</title>
  <link rel="stylesheet" href="<?= asset_url_versioned('vendor/bootstrap-5.3.8/bootstrap.min.css') ?>">
  <link rel="stylesheet" href="<?= asset_url_versioned('css/instrument.css') ?>">
</head>
<body class="instrument">
  <header class="instrument-bar">
    <div class="container d-flex flex-wrap justify-content-between align-items-center py-2 gap-2">
      <strong><?= esc($bar ?? lang('Instrument.moduleTitle')) ?></strong>
      <?php if (! empty($researchCode)): ?>
        <span><?= esc(lang('Instrument.researchCode', [$researchCode])) ?></span>
      <?php endif ?>
    </div>
  </header>
  <main class="container py-4">
    <?php if (session('instrument_notice')): ?>
      <div class="alert alert-info" role="status"><?= esc((string) session('instrument_notice')) ?></div>
    <?php endif ?>
    <?= $this->renderSection('content') ?>
  </main>
  <?= $this->renderSection('scripts') ?>
</body>
</html>
