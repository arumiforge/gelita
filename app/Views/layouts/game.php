<?php
/**
 * Layout area game — panggung 16:9 tetap (Dok 3 §1–§3), latar bergambar,
 * HUD lentera, dwibahasa.
 *
 * Semua isi game digambar di `#stage`, kotak logis 1280 × 720 dp (80 × 45 rem)
 * yang diskalakan seragam dan diletakkan di tengah layar; sisa layar menjadi
 * bingkai gelap. Hanya layar putar yang berada di luar panggung.
 *
 * Halaman mengisi section:
 *   title       judul tab
 *   background  URL latar (opsional; tanpa gambar dipakai gradien CSS)
 *   bodyClass   kelas tambahan <body>, mis. `is-challenge`
 *   overlay     lapisan selebar panggung di luar <main>, mis. tirai (components/curtain);
 *               dirender paling awal agar tampil sejak paint pertama
 *   content     isi halaman
 *   nav         komponen nav-bar (opsional)
 *   hudStyle    `menu` for the full-size HUD (120 dp; components/hud-menu),
 *               `tall` for the 84 dp one (region map); any other page gets
 *               the same HUD compact in the 56 dp strip
 *   hudTitle, hudBack, hudShards, hudLayout  see components/hud-menu
 *   music       background track of the page (`<body data-music>`, file
 *               assets/audio/music/{name}.mp3, started by game.js after the
 *               first tap); pages that switch tracks themselves leave it empty
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
$music      = trim($this->renderSection('music'));
$request    = service('request');
// Kode layar Dok 1 §12.4 (sama dengan screen_viewed di server) untuk event page_hidden/visible
$screenCode = method_exists($request, 'getPath') ? (App\Filters\ScreenViewFilter::resolve(trim((string) $request->getPath(), '/'))[0] ?? null) : null;
?>
<!DOCTYPE html>
<html class="game-stage" lang="<?= esc($locale) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <?php /* Window size for the 16:9 stage before CSS loads (Dok 3 §2.1); core/stage.js takes over. A file, not inline, so it passes the CSP */ ?>
  <script src="<?= asset_url_versioned('js/core/viewport.js') ?>"></script>
  <?php /* Asset package boot (Stage 2): local-assets hook and package mode before game.js; a file for the CSP */ ?>
  <script src="<?= asset_url_versioned('js/core/package-boot.js') ?>"<?= asset_package_attrs() ?>></script>
  <?php /* Holds Chrome's install offer until game/install.js is ready; before the manifest link, so it listens first (a file, for the CSP) */ ?>
  <script src="<?= asset_url_versioned('js/core/install-boot.js') ?>"></script>
  <meta name="theme-color" content="#0B1320">
  <title><?= $title !== '' ? $title . ' · GELITA' : 'GELITA' ?></title>
  <meta name="csrf-token" content="<?= csrf_hash() ?>">
  <meta name="csrf-name" content="<?= csrf_token() ?>">
  <link rel="icon" href="<?= esc(app_icon_src('app.favicon', 'favicon.ico'), 'attr') ?>">
  <?php /* Installed app: standalone, no fixed orientation; the rotate screen asks for landscape (docs/05 §Panggung 16:9) */ ?>
  <link rel="manifest" href="<?= base_url('manifest.json') ?>">
  <link rel="apple-touch-icon" href="<?= esc(app_icon_src('app.apple-touch', 'assets/app/apple-touch-icon.png'), 'attr') ?>">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-title" content="GELITA">
  <meta name="apple-mobile-web-app-status-bar-style" content="black">
  <link rel="preload" href="<?= game_asset_url('fonts/marcellus-latin-400-normal.woff2') ?>" as="font" type="font/woff2" crossorigin>
  <link rel="stylesheet" href="<?= asset_url_versioned('css/tokens.css') ?>">
  <link rel="stylesheet" href="<?= asset_url_versioned('css/base.css') ?>">
  <link rel="stylesheet" href="<?= asset_url_versioned('css/stage.css') ?>">
  <link rel="stylesheet" href="<?= asset_url_versioned('css/components.css') ?>">
  <link rel="stylesheet" href="<?= asset_url_versioned('css/game.css') ?>">
  <link rel="stylesheet" href="<?= asset_url_versioned('css/cinematic.css') ?>">
  <?php /* Tanpa JavaScript: tirai tidak pernah menutupi halaman (docs/05 §Tirai) */ ?>
  <noscript><link rel="stylesheet" href="<?= asset_url_versioned('css/noscript.css') ?>"></noscript>
  <?= $this->renderSection('head') ?>
</head>
<body class="game <?= esc($bodyClass) ?>"
      data-locale="<?= esc($locale) ?>"
      data-base="<?= esc(base_url()) ?>"<?= $music !== '' ? ' data-music="' . esc($music, 'attr') . '"' : '' ?><?= $screenCode !== null ? ' data-screen-code="' . esc($screenCode, 'attr') . '"' : '' ?>>
  <?php /* Di luar panggung: layar putar menutup seluruh layar */ ?>
  <?= $this->include('components/rotate-gate') ?>

  <div class="stage-frame">
    <?php // The round HUD skin for the story narrator controls, the owner's neutral round button behind a letter avatar (21 October 2026),
          // and the gold pills, dialog frames, dropdown arrow of 22 October 2026 (stage_picture_urls()) ?>
    <div class="stage" id="stage"<?= skin_style_attr(['skin-hud' => 'ui.result-hud-control'], ['round-neutral' => round_icon_src('neutral')] + stage_picture_urls()) ?>>
      <a class="skip-link" href="#app"><?= esc(lang('Game.skipToContent')) ?></a>

      <div class="scene" aria-hidden="true">
        <img class="scene-img<?= $background !== '' ? ' is-visible' : '' ?>" id="scene-a"
             <?= $background !== '' ? 'src="' . esc($background, 'attr') . '"' : '' ?> alt="">
        <img class="scene-img" id="scene-b" alt="">
      </div>

      <?= $this->renderSection('overlay') ?>

      <?= $this->include('components/hud-menu') ?>

      <main id="app" class="stage-main" tabindex="-1">
        <?= $this->renderSection('content') ?>
      </main>

      <?= $this->renderSection('nav') ?>

      <div id="modal-layer" class="modal-layer" hidden></div>
      <?= component('toast', ['withErrors' => true]) ?>
    </div>
  </div>
  <template id="tpl-modal"><?= view('components/modal', ['roundArt' => true]) ?></template>

  <script type="application/json" id="app-config"><?= json_encode(
      js_config($locale),
      JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT,
  ) ?></script>

  <script defer src="<?= asset_url_versioned('vendor/howler.min.js') ?>"></script>
  <script type="module" src="<?= asset_url_versioned('js/game.js') ?>"></script>
  <?= $this->renderSection('scripts') ?>
</body>
</html>
