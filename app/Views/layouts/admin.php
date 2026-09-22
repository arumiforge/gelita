<?php
/**
 * Layout panel admin — header, sidebar, konten. Satu bahasa (Indonesia).
 *
 * Section: title, head, content, scripts. ECharts hanya dimuat halaman yang
 * mengisi section `charts` (dashboard, analitik, profil peserta).
 *
 * @var CodeIgniter\View\View $this
 * @var string|null           $pageTitle
 * @var array<string, mixed>|null $activeStudy  BaseAdminController::panel()
 */
$title    = trim($this->renderSection('title'));
$hasChart = trim($this->renderSection('charts')) !== '';
$logo     = media_key_src('ui.logo');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <title><?= esc($title !== '' ? $title : ($pageTitle ?? lang('Admin.panelTitle'))) ?> · Panel GELITA</title>
  <meta name="csrf-token" content="<?= csrf_hash() ?>">
  <meta name="csrf-name" content="<?= csrf_token() ?>">
  <link rel="icon" href="<?= base_url('favicon.ico') ?>">
  <link rel="stylesheet" href="<?= asset_url_versioned('css/tokens.css') ?>">
  <link rel="stylesheet" href="<?= asset_url_versioned('css/base.css') ?>">
  <link rel="stylesheet" href="<?= asset_url_versioned('css/layout.css') ?>">
  <link rel="stylesheet" href="<?= asset_url_versioned('css/components.css') ?>">
  <link rel="stylesheet" href="<?= asset_url_versioned('css/admin.css') ?>">
  <?= $this->renderSection('head') ?>
</head>
<body class="admin" data-locale="id" data-base="<?= esc(base_url()) ?>">
  <a class="skip-link" href="#admin-main">Langsung ke isi halaman</a>

  <header class="admin-head">
    <a class="admin-brand" href="<?= base_url('admin/dashboard') ?>" aria-label="Panel GELITA — beranda">
      <?php if ($logo !== null): ?>
        <img class="admin-logo" src="<?= esc($logo, 'attr') ?>" alt="">
      <?php endif ?>
      <span class="admin-brand-text">GELITA</span>
    </a>
    <div class="admin-sub">
      <b><?= esc($pageTitle ?? lang('Admin.panelTitle')) ?></b>
      <?php if (! empty($activeStudy)): ?>
        · studi aktif <code><?= esc($activeStudy['code']) ?></code> · fase <?= esc($activeStudy['active_phase_code']) ?>
      <?php endif ?>
    </div>
    <div class="admin-user">
      <a class="btn btn-quiet btn-sm menu-toggle" href="#admin-menu"><?= icon('menu') ?> Menu</a>
      <?php if (session('staff_id')): ?>
        <span class="admin-user-name">
          <b><?= esc(session('staff_name') ?? '') ?></b>
          <small><?= esc(lang(session('staff_role') === 'admin' ? 'Admin.roleAdmin' : 'Admin.roleGuru')) ?></small>
        </span>
        <a class="btn btn-quiet btn-sm" href="<?= base_url('admin/logout') ?>"><?= icon('logout') ?> <?= esc(lang('Admin.logout')) ?></a>
      <?php endif ?>
    </div>
  </header>

  <div class="admin-body">
    <?= $this->include('components/admin-sidebar') ?>
    <main class="admin-main" id="admin-main" tabindex="-1">
      <?= $this->renderSection('content') ?>
    </main>
  </div>

  <div id="modal-layer" class="modal-layer" hidden></div>
  <template id="tpl-modal"><?= $this->include('components/modal') ?></template>
  <?= component('toast', ['withErrors' => false]) ?>

  <script type="application/json" id="app-config"><?= json_encode([
      'locale'   => 'id',
      'csrfName' => csrf_token(),
      'csrfHash' => csrf_hash(),
      'apiBase'  => base_url('api'),
  ], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>

  <?php if ($hasChart): ?>
    <script defer src="<?= asset_url_versioned('vendor/echarts.min.js') ?>"></script>
  <?php endif ?>
  <script type="module" src="<?= asset_url_versioned('js/admin.js') ?>"></script>
  <?= $this->renderSection('scripts') ?>
</body>
</html>
