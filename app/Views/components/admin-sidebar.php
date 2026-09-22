<?php
/**
 * Menu panel. Menu yang tidak berhak diakses TIDAK dirender untuk guru.
 *
 * ≤ 1024px sidebar menyempit menjadi ikon (label tetap terbaca pembaca layar);
 * ≤ 640px menjadi drawer yang dibuka tautan #admin-menu (CSS :target, tanpa
 * JavaScript).
 */
$isAdmin = session('staff_role') === 'admin';
$path    = trim(service('request')->getPath(), '/');

// [href, label, ikon, adminOnly, grup]
$menu = [
    ['admin/dashboard',          'Admin.menuDashboard',    'grid',     false, null],
    ['admin/peserta',            'Admin.menuParticipants', 'users',    false, 'Admin.groupData'],
    ['admin/sesi',               'Admin.menuSessions',     'clock',    false, 'Admin.groupData'],
    ['admin/analitik/level',     'Admin.menuAnLevel',      'chart',    false, 'Admin.menuAnalytics'],
    ['admin/analitik/node',      'Admin.menuAnNode',       'puzzle',   false, 'Admin.menuAnalytics'],
    ['admin/analitik/butir',     'Admin.menuAnItem',       'list',     false, 'Admin.menuAnalytics'],
    ['admin/analitik/indikator', 'Admin.menuAnIndicator',  'target',   false, 'Admin.menuAnalytics'],
    ['admin/analitik/prepost',   'Admin.menuAnPrePost',    'trend',    false, 'Admin.menuAnalytics'],
    ['admin/masukan',            'Admin.menuFeedback',     'message',  false, 'Admin.groupReport'],
    ['admin/ekspor',             'Admin.menuExport',       'download', false, 'Admin.groupReport'],
    ['admin/konten',             'Admin.menuContent',      'book',     true,  'Admin.groupManage'],
    ['admin/konten/impor-bank',  'Admin.menuBankImport',   'upload',   true,  'Admin.groupManage'],
    ['admin/media',              'Admin.menuMedia',        'image',    true,  'Admin.groupManage'],
    ['admin/studi',              'Admin.menuStudy',        'flask',    true,  'Admin.groupManage'],
    ['admin/tata-kelola',        'Admin.menuGovernance',   'shield',   true,  'Admin.groupManage'],
    ['admin/staf',               'Admin.menuStaff',        'key',      true,  'Admin.groupManage'],
];

// Hanya menu dengan awalan terpanjang yang aktif (konten vs konten/impor-bank)
$active = '';
foreach ($menu as [$href]) {
    if (($path === $href || str_starts_with($path, $href . '/')) && strlen($href) > strlen($active)) {
        $active = $href;
    }
}
if ($active === '' && $path === 'admin') {
    $active = 'admin/dashboard';
}

$group = null;
?>
<nav class="admin-sidebar" id="admin-menu" aria-label="<?= esc(lang('Admin.mainMenu'), 'attr') ?>">
  <div class="menu-close">
    <a class="btn btn-quiet btn-sm" href="#"><?= icon('cross') ?> <?= esc(lang('Admin.closeMenu')) ?></a>
  </div>
  <ul>
    <?php foreach ($menu as [$href, $label, $iconName, $adminOnly, $menuGroup]): ?>
      <?php if ($adminOnly && ! $isAdmin) {
          continue;
      } ?>
      <?php if ($menuGroup !== $group): $group = $menuGroup; ?>
        <?php if ($group !== null): ?>
          <li class="menu-group" role="presentation"><?= esc(lang($group)) ?></li>
        <?php endif ?>
      <?php endif ?>
      <li class="<?= $href === 'admin/konten/impor-bank' ? 'menu-sub' : '' ?>">
        <a href="<?= base_url($href) ?>" class="menu-link<?= $active === $href ? ' is-active' : '' ?>"
           title="<?= esc(lang($label), 'attr') ?>"
           <?= $active === $href ? 'aria-current="page"' : '' ?>>
          <?= icon($iconName) ?>
          <span class="menu-label"><?= esc(lang($label)) ?></span>
        </a>
      </li>
    <?php endforeach ?>
  </ul>
</nav>
