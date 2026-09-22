<?php
/**
 * Layout login staf — satu panel di tengah, tanpa sidebar.
 *
 * @var CodeIgniter\View\View $this
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $this->renderSection('title') ?: lang('Admin.loginTitle') . ' · GELITA' ?></title>
  <link rel="icon" href="<?= base_url('favicon.ico') ?>">
  <link rel="stylesheet" href="<?= asset_url_versioned('css/base.css') ?>">
  <link rel="stylesheet" href="<?= asset_url_versioned('css/layout.css') ?>">
  <link rel="stylesheet" href="<?= asset_url_versioned('css/components.css') ?>">
</head>
<body class="auth">
  <main class="auth-shell">
    <section class="auth-panel panel">
      <div class="auth-brand">GELITA</div>
      <?= $this->renderSection('content') ?>
    </section>
  </main>
</body>
</html>
