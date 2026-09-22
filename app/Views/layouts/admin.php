<?php
/**
 * Layout panel admin — header, sidebar, konten. Satu bahasa (Indonesia).
 *
 * @var CodeIgniter\View\View $this
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $this->renderSection('title') ?: 'Panel GELITA' ?></title>
  <meta name="csrf-token" content="<?= csrf_hash() ?>">
  <meta name="csrf-name" content="<?= csrf_token() ?>">
  <link rel="icon" href="<?= base_url('favicon.ico') ?>">
  <link rel="stylesheet" href="<?= asset_url_versioned('css/base.css') ?>">
  <link rel="stylesheet" href="<?= asset_url_versioned('css/layout.css') ?>">
  <link rel="stylesheet" href="<?= asset_url_versioned('css/components.css') ?>">
  <link rel="stylesheet" href="<?= asset_url_versioned('css/admin.css') ?>">
  <?= $this->renderSection('head') ?>
</head>
<body class="admin" data-locale="id" data-base="<?= esc(base_url()) ?>">
  <header class="admin-head">
    <a class="admin-brand" href="<?= base_url('admin/dashboard') ?>">GELITA</a>
    <div class="admin-sub"><?= esc($pageTitle ?? lang('Admin.panelTitle')) ?></div>
    <div class="admin-user">
      <?php if (session('staff_id')): ?>
        <b><?= esc(session('staff_name') ?? '') ?></b>
        <small><?= esc(session('staff_role') ?? '') ?></small>
        <a class="btn btn-quiet" href="<?= base_url('admin/logout') ?>"><?= esc(lang('Admin.logout')) ?></a>
      <?php endif ?>
    </div>
  </header>

  <div class="admin-body">
    <?= $this->include('components/admin-sidebar') ?>
    <main class="admin-main" id="admin-main">
      <?= $this->renderSection('content') ?>
    </main>
  </div>

  <div id="modal-layer" class="modal-layer" hidden></div>
  <div id="toast-layer" class="toast-layer" role="status" aria-live="polite"></div>

  <script type="application/json" id="app-config"><?= json_encode([
      'locale'   => 'id',
      'csrfName' => csrf_token(),
      'csrfHash' => csrf_hash(),
      'apiBase'  => base_url('api'),
  ], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>

  <script defer src="<?= asset_url_versioned('vendor/echarts.min.js') ?>"></script>
  <script type="module" src="<?= asset_url_versioned('js/admin.js') ?>"></script>
  <?= $this->renderSection('scripts') ?>
</body>
</html>
