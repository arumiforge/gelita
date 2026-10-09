<?php
/**
 * Layout masuk staf — satu kartu di tengah layar bergaya panel admin
 * (Dok 3 §1.6, §8.1): latar netral, tanpa gambar tema permainan.
 *
 * @var CodeIgniter\View\View $this
 */
$title = trim($this->renderSection('title'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <title><?= esc($title !== '' ? $title : lang('Admin.loginTitle')) ?> · GELITA</title>
  <link rel="icon" href="<?= base_url('favicon.ico') ?>">
  <link rel="stylesheet" href="<?= asset_url_versioned('css/admin.css') ?>">
</head>
<body class="auth">
  <a class="skip-link" href="#auth-main"><?= esc(lang('Admin.skipToContent')) ?></a>

  <main class="auth-shell" id="auth-main" tabindex="-1">
    <section class="auth-card">
      <div class="auth-brand">
        <span class="sidebar-mark" aria-hidden="true">G</span>
        <span>GELITA <small><?= esc(lang('Admin.panelTitle')) ?></small></span>
      </div>
      <?= $this->renderSection('content') ?>
    </section>
  </main>
</body>
</html>
