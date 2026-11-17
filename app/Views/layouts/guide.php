<?php
/**
 * Layout of the guides (staff guide /admin/panduan-staf, validator guide
 * /admin/panduan-validator), for every reader: a
 * documentation site in the panel's colours. Top bar (GELITA mark, the way
 * back to the panel or to the login page), the guide's menu on the left
 * (admin/guide/_nav), the page in the middle. Loads admin.css only.
 *
 * Sections: `content` (the page), `popovers` (glossary popovers, outside
 * the text so they never land inside a paragraph).
 *
 * @var CodeIgniter\View\View $this
 * @var bool   $isStaff
 * @var string $roleName
 * @var string $pageTitle
 * @var class-string<\App\Libraries\GuideContent> $guide
 * @var array<string, mixed> $site
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <title><?= esc($pageTitle) ?> · GELITA</title>
  <link rel="icon" href="<?= esc(app_icon_src('app.favicon', 'favicon.ico'), 'attr') ?>">
  <link rel="stylesheet" href="<?= asset_url_versioned('css/admin.css') ?>">
</head>
<body class="admin guide-site" data-locale="id" data-base="<?= esc(base_url()) ?>">
  <a class="skip-link" href="#admin-main"><?= esc(lang('Admin.skipToContent')) ?></a>

  <header class="gd-bar">
    <button type="button" class="gd-menu-btn btn btn-ghost" aria-controls="gd-nav" aria-expanded="false">
      <?= icon('menu') ?><span><?= esc(lang('Admin.guideMenu')) ?></span>
    </button>
    <a class="gd-brand" href="<?= esc($guide::url() . $query, 'attr') ?>">
      <span class="sidebar-mark" aria-hidden="true">G</span>
      <span>GELITA <small><?= esc($site['title']) ?></small></span>
    </a>
    <div class="gd-bar-end">
      <?php if ($isStaff): ?>
        <?php if ($site['signIn']): ?><span class="gd-role"><?= esc(lang('Admin.guideReadingAs', [$roleName])) ?></span><?php endif ?>
        <a class="btn" href="<?= base_url('admin') ?>"><?= icon('left') ?> <?= esc(lang('Admin.guideBackToPanel')) ?></a>
      <?php elseif ($site['signIn']): ?>
        <a class="btn btn-primary" href="<?= base_url('admin/login') ?>"><?= icon('login') ?> <?= esc(lang('Admin.guideSignIn')) ?></a>
      <?php endif ?>
    </div>
  </header>

  <div class="gd-shell">
    <?= $this->include('admin/guide/_nav') ?>
    <main class="gd-main" id="admin-main" tabindex="-1">
      <?= $this->renderSection('content') ?>
    </main>
  </div>

  <?= $this->renderSection('popovers') ?>

  <script type="module" src="<?= asset_url_versioned('js/admin/guide.js') ?>"></script>
</body>
</html>
