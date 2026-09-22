<?php
/**
 * Layout area game — layar penuh, HUD, dwibahasa.
 *
 * @var CodeIgniter\View\View $this
 */
$locale = service('request')->getLocale();
?>
<!DOCTYPE html>
<html lang="<?= esc($locale) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title><?= $this->renderSection('title') ?: 'GELITA' ?></title>
  <meta name="csrf-token" content="<?= csrf_hash() ?>">
  <meta name="csrf-name" content="<?= csrf_token() ?>">
  <link rel="icon" href="<?= base_url('favicon.ico') ?>">
  <link rel="stylesheet" href="<?= asset_url_versioned('css/base.css') ?>">
  <link rel="stylesheet" href="<?= asset_url_versioned('css/layout.css') ?>">
  <link rel="stylesheet" href="<?= asset_url_versioned('css/components.css') ?>">
  <link rel="stylesheet" href="<?= asset_url_versioned('css/game.css') ?>">
  <?= $this->renderSection('head') ?>
</head>
<body class="game" data-locale="<?= esc($locale) ?>" data-base="<?= esc(base_url()) ?>">
  <?= $this->include('components/hud') ?>

  <main id="app" class="app <?= esc($mainClass ?? '') ?>">
    <?= $this->renderSection('content') ?>
  </main>

  <div id="modal-layer" class="modal-layer" hidden></div>
  <div id="toast-layer" class="toast-layer" role="status" aria-live="polite"></div>

  <script type="application/json" id="app-config"><?= json_encode([
      'locale'   => $locale,
      'csrfName' => csrf_token(),
      'csrfHash' => csrf_hash(),
      'apiBase'  => base_url('api'),
  ], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>

  <script defer src="<?= asset_url_versioned('vendor/howler.min.js') ?>"></script>
  <script type="module" src="<?= asset_url_versioned('js/game.js') ?>"></script>
  <?= $this->renderSection('scripts') ?>
</body>
</html>
