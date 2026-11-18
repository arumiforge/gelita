<?php
/**
 * Layout of the guides (staff guide /admin/panduan-staf, validator guide
 * /admin/panduan-validator, GELITA guide /admin/panduan-gelita), for every reader: a
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

$emblem = media_key_src('ui.logo') ?? media_key_src('ui.lantern');

// The title opens a menu to the other guides and pages (owner, 18 Nov 2026):
// [href, label, note, current, outside]; an empty href is a separator.
$base     = $guide::BASE;
$switchTo = [
    [base_url('admin/panduan-staf'), lang('Admin.guideTitle'), lang('Admin.guideSwitchStaffNote'), $base === 'admin/panduan-staf', false],
    [base_url('admin/panduan-validator'), lang('Admin.vguideTitle'), lang('Admin.guideSwitchValidatorNote'), $base === 'admin/panduan-validator', false],
    [base_url('admin/panduan-gelita'), lang('Admin.gguideTitle'), lang('Admin.guideSwitchGelitaNote'), $base === 'admin/panduan-gelita', false],
    ['', '', '', false, false],
    [$guide::CATALOGUE, lang('Admin.guideSwitchCatalogue'), lang('Admin.guideSwitchCatalogueNote'), false, true],
    $isStaff
        ? [base_url('admin'), lang('Admin.guideBackToPanel'), lang('Admin.guideSwitchPanelNote'), false, false]
        : [base_url('admin/login'), lang('Admin.guideSwitchLogin'), lang('Admin.guideSwitchLoginNote'), false, false],
    [base_url('/'), lang('Admin.guideSwitchGame'), lang('Admin.guideSwitchGameNote'), false, false],
];

// Validators have no staff account (they use test accounts on the test
// install), so their guide never offers the staff sign-in page
if (! $isStaff && $base === 'admin/panduan-validator') {
    $switchTo = array_values(array_filter($switchTo, static fn (array $row): bool => $row[0] !== base_url('admin/login')));
}
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
    <details class="gd-switch">
      <summary class="gd-brand" aria-label="<?= esc(lang('Admin.guideSwitch', [$site['title']]), 'attr') ?>">
        <img class="gd-emblem<?= $emblem === null ? ' is-icon' : '' ?>" src="<?= esc($emblem ?? base_url('assets/app/icon.svg'), 'attr') ?>" alt="" width="40" height="40" decoding="async">
        <span class="gd-brand-title"><?= esc($site['title']) ?></span>
        <?= icon('down', 'gd-switch-chevron') ?>
      </summary>
      <div class="gd-switch-menu">
        <ul>
          <?php foreach ($switchTo as [$href, $label, $note, $here, $outside]): ?>
            <?php if ($href === ''): ?>
              <li class="gd-switch-sep" role="separator"></li>
            <?php else: ?>
              <li>
                <a href="<?= esc($href, 'attr') ?>"<?= $here ? ' aria-current="page"' : '' ?><?= $outside ? ' target="_blank" rel="noopener"' : '' ?>>
                  <span class="gd-switch-label"><?= esc($label) ?></span>
                  <span class="gd-switch-note"><?= esc($note) ?></span>
                </a>
              </li>
            <?php endif ?>
          <?php endforeach ?>
        </ul>
      </div>
    </details>
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
