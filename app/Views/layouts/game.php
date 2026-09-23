<?php
/**
 * Layout area game — layar penuh, latar bergambar, HUD lentera, dwibahasa.
 *
 * Halaman mengisi section:
 *   title       judul tab
 *   background  URL latar (opsional; tanpa gambar dipakai gradien CSS)
 *   bodyClass   kelas tambahan <body>, mis. `is-challenge`
 *   content     isi halaman
 *   nav         komponen nav-bar (opsional)
 *   scripts     skrip khusus halaman (tahap 6)
 *
 * Data untuk JavaScript dikirim lewat <script type="application/json">,
 * tidak pernah diinterpolasi ke dalam string JavaScript.
 *
 * @var CodeIgniter\View\View $this
 */
$locale     = service('request')->getLocale();
$background = trim($this->renderSection('background'));
$bodyClass  = trim($this->renderSection('bodyClass'));
$title      = trim($this->renderSection('title'));
?>
<!DOCTYPE html>
<html lang="<?= esc($locale) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#0B1320">
  <title><?= $title !== '' ? $title . ' · GELITA' : 'GELITA' ?></title>
  <meta name="csrf-token" content="<?= csrf_hash() ?>">
  <meta name="csrf-name" content="<?= csrf_token() ?>">
  <link rel="icon" href="<?= base_url('favicon.ico') ?>">
  <link rel="preload" href="<?= base_url('assets/fonts/plus-jakarta-sans-latin-400-normal.woff2') ?>" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="<?= base_url('assets/fonts/cinzel-latin-700-normal.woff2') ?>" as="font" type="font/woff2" crossorigin>
  <link rel="stylesheet" href="<?= asset_url_versioned('css/tokens.css') ?>">
  <link rel="stylesheet" href="<?= asset_url_versioned('css/base.css') ?>">
  <link rel="stylesheet" href="<?= asset_url_versioned('css/layout.css') ?>">
  <link rel="stylesheet" href="<?= asset_url_versioned('css/components.css') ?>">
  <link rel="stylesheet" href="<?= asset_url_versioned('css/game.css') ?>">
  <?= $this->renderSection('head') ?>
</head>
<body class="game <?= esc($bodyClass) ?>"
      data-locale="<?= esc($locale) ?>"
      data-base="<?= esc(base_url()) ?>">
  <a class="skip-link" href="#app"><?= esc(lang('Game.skipToContent')) ?></a>

  <?= $this->include('components/hud') ?>

  <div class="scene" aria-hidden="true">
    <img class="scene-img<?= $background !== '' ? ' is-visible' : '' ?>" id="scene-a"
         <?= $background !== '' ? 'src="' . esc($background, 'attr') . '"' : '' ?> alt="">
    <img class="scene-img" id="scene-b" alt="">
  </div>

  <main id="app" class="app" tabindex="-1">
    <?= $this->renderSection('content') ?>
  </main>

  <?= $this->renderSection('nav') ?>

  <div id="modal-layer" class="modal-layer" hidden></div>
  <template id="tpl-modal"><?= $this->include('components/modal') ?></template>
  <?= component('toast', ['withErrors' => true]) ?>

  <script type="application/json" id="app-config"><?= json_encode(
      js_config($locale),
      JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT,
  ) ?></script>

  <script defer src="<?= asset_url_versioned('vendor/howler.min.js') ?>"></script>
  <script type="module" src="<?= asset_url_versioned('js/game.js') ?>"></script>
  <?= $this->renderSection('scripts') ?>
</body>
</html>
