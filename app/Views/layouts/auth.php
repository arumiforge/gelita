<?php
/**
 * Layout login staf — satu panel di tengah layar, latar `bg-auth`, tanpa sidebar.
 *
 * @var CodeIgniter\View\View $this
 */
$title      = trim($this->renderSection('title'));
$background = media_key_src('bg.auth');
$logo       = media_key_src('ui.logo');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <title><?= esc($title !== '' ? $title : lang('Admin.loginTitle')) ?> · GELITA</title>
  <link rel="icon" href="<?= base_url('favicon.ico') ?>">
  <link rel="stylesheet" href="<?= asset_url_versioned('css/tokens.css') ?>">
  <link rel="stylesheet" href="<?= asset_url_versioned('css/base.css') ?>">
  <link rel="stylesheet" href="<?= asset_url_versioned('css/layout.css') ?>">
  <link rel="stylesheet" href="<?= asset_url_versioned('css/components.css') ?>">
</head>
<body class="auth">
  <?php if ($background !== null): ?>
    <img class="auth-bg" src="<?= esc($background, 'attr') ?>" alt="">
  <?php endif ?>
  <main class="auth-shell">
    <section class="auth-panel panel">
      <div class="auth-brand">
        <?php if ($logo !== null): ?>
          <img src="<?= esc($logo, 'attr') ?>" alt="" width="120">
        <?php endif ?>
        <span>GELITA</span>
        <small><?= esc(lang('Admin.panelTitle')) ?></small>
      </div>
      <?= $this->renderSection('content') ?>
    </section>
  </main>
</body>
</html>
