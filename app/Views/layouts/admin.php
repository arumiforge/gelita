<?php
/**
 * Layout panel admin (Dok 3 §8.1) — dasbor SaaS netral, tidak bertema
 * GELITA: sidebar 248 px (ikon 72 px di bawah 1280 px), topbar 56 px
 * (breadcrumb, pencarian cepat siswa, menu akun), isi selebar layar dengan
 * padding 24 px. Hanya memuat admin.css; CSS game tidak ikut.
 *
 * Section: title, head, content, scripts.
 *
 * @var CodeIgniter\View\View          $this
 * @var string|null                    $pageTitle
 * @var array<string, string|null>|null $breadcrumb langkah tambahan [label => href|null]
 * @var array<string, mixed>|null      $activeStudy BaseAdminController::panel()
 */
use App\Libraries\AdminMenu;

$title   = trim($this->renderSection('title'));
$path    = service('request')->getPath();
$crumbs  = AdminMenu::crumbs($path, $pageTitle ?? null, $breadcrumb ?? []);
$isStaff = (bool) session('staff_id');
$role    = session('staff_role') === 'admin' ? lang('Admin.roleAdmin') : lang('Admin.roleGuru');
$name    = (string) (session('staff_name') ?? '');
$initial = mb_strtoupper(mb_substr($name !== '' ? $name : 'A', 0, 1));
$search  = lang(session('staff_role') === 'admin' ? 'Admin.quickSearch' : 'Admin.quickSearchTeacher');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <title><?= esc($title !== '' ? $title : ($pageTitle ?? lang('Admin.panelTitle'))) ?> · <?= esc(lang('Admin.panelTitle')) ?></title>
  <meta name="csrf-token" content="<?= csrf_hash() ?>">
  <meta name="csrf-name" content="<?= csrf_token() ?>">
  <link rel="icon" href="<?= esc(app_icon_src('app.favicon', 'favicon.ico'), 'attr') ?>">
  <link rel="stylesheet" href="<?= asset_url_versioned('css/admin.css') ?>">
  <?= $this->renderSection('head') ?>
</head>
<body class="admin" data-locale="id" data-base="<?= esc(base_url()) ?>">
  <a class="skip-link" href="#admin-main"><?= esc(lang('Admin.skipToContent')) ?></a>

  <div class="admin-shell">
    <?= $this->include('components/admin-sidebar') ?>

    <div class="admin-column">
      <header class="admin-topbar">
        <a class="topbar-menu" href="#admin-menu" aria-label="<?= esc(lang('Admin.openMenu'), 'attr') ?>"><?= icon('menu') ?></a>
        <nav class="breadcrumb" aria-label="<?= esc(lang('Admin.breadcrumbLabel'), 'attr') ?>">
          <ol>
            <?php foreach ($crumbs as $crumb): ?>
              <li>
                <?php if ($crumb['href'] !== null): ?>
                  <a href="<?= esc(base_url($crumb['href']), 'attr') ?>"><?= esc($crumb['label']) ?></a>
                <?php else: ?>
                  <span<?= $crumb === end($crumbs) ? ' aria-current="page"' : '' ?>><?= esc($crumb['label']) ?></span>
                <?php endif ?>
              </li>
            <?php endforeach ?>
          </ol>
        </nav>

        <?php if ($isStaff): ?>
          <form class="topbar-search" method="get" action="<?= base_url('admin/peserta') ?>" role="search">
            <?= icon('search') ?>
            <label class="visually-hidden" for="topbar-q"><?= esc($search) ?></label>
            <input type="search" id="topbar-q" name="q" placeholder="<?= esc($search, 'attr') ?>"
                   autocomplete="off" spellcheck="false" value="<?= esc(str_starts_with($path, 'admin/peserta') ? (string) (service('request')->getGet('q') ?? '') : '', 'attr') ?>">
          </form>

          <?php if (! empty($activeStudy)): ?>
            <span class="topbar-study" title="<?= esc(lang('Admin.activeStudyTitle'), 'attr') ?>">
              <i class="dot is-ok" aria-hidden="true"></i><?= esc($activeStudy['code']) ?>
            </span>
          <?php endif ?>

          <details class="account-menu">
            <summary aria-label="<?= esc(lang('Admin.accountMenu'), 'attr') ?>">
              <span class="avatar" aria-hidden="true"><?= esc($initial) ?></span>
              <span class="account-name"><b><?= esc($name) ?></b><small><?= esc($role) ?></small></span>
              <?= icon('down') ?>
            </summary>
            <div class="account-pop">
              <p class="account-who"><b><?= esc($name) ?></b><small><?= esc($role) ?></small></p>
              <a href="<?= base_url('admin/akun/sandi') ?>"><?= icon('key') ?> <?= esc(lang('Admin.changePassword')) ?></a>
              <a href="<?= base_url('admin/logout') ?>"><?= icon('logout') ?> <?= esc(lang('Admin.logout')) ?></a>
            </div>
          </details>
        <?php endif ?>
      </header>

      <main class="admin-main" id="admin-main" tabindex="-1">
        <?= $this->renderSection('content') ?>
      </main>
    </div>
  </div>

  <div id="modal-layer" class="modal-layer" hidden></div>
  <template id="tpl-modal"><?= $this->include('components/modal') ?></template>
  <?= component('toast', ['withErrors' => false]) ?>

  <script type="application/json" id="app-config"><?= json_encode(
      js_config('id'),
      JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT,
  ) ?></script>

  <script type="module" src="<?= asset_url_versioned('js/admin.js') ?>"></script>
  <?= $this->renderSection('scripts') ?>
</body>
</html>
