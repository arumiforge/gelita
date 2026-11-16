<?php
/**
 * Public layout of the staff guide (/admin/panduan-staf) for a reader who
 * has not signed in: the panel's look without the sidebar, a top bar with
 * the GELITA mark and the way to the login page. Loads admin.css only.
 *
 * @var CodeIgniter\View\View $this
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <title><?= esc(lang('Admin.guideTitle')) ?> · GELITA</title>
  <link rel="icon" href="<?= esc(app_icon_src('app.favicon', 'favicon.ico'), 'attr') ?>">
  <link rel="stylesheet" href="<?= asset_url_versioned('css/admin.css') ?>">
</head>
<body class="admin guide-public" data-locale="id" data-base="<?= esc(base_url()) ?>">
  <a class="skip-link" href="#admin-main"><?= esc(lang('Admin.skipToContent')) ?></a>

  <header class="guide-public-bar">
    <a class="guide-public-brand" href="<?= base_url('admin/panduan-staf') ?>">
      <span class="sidebar-mark" aria-hidden="true">G</span>
      <span>GELITA <small><?= esc(lang('Admin.guideTitle')) ?></small></span>
    </a>
    <a class="btn btn-primary" href="<?= base_url('admin/login') ?>"><?= icon('key') ?> <?= esc(lang('Admin.guideSignIn')) ?></a>
  </header>

  <main class="admin-main guide-public-main" id="admin-main" tabindex="-1">
    <?= $this->renderSection('content') ?>
  </main>

  <script type="module" src="<?= asset_url_versioned('js/admin/guide-public.js') ?>"></script>
</body>
</html>
