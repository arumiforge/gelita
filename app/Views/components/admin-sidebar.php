<?php
/**
 * Menu panel. Menu yang tidak berhak diakses tidak dirender untuk guru.
 */
$role    = session('staff_role');
$isAdmin = $role === 'admin';
$path    = service('request')->getPath();

$menu = [
    ['admin/dashboard',          'Admin.menuDashboard',    false],
    ['admin/peserta',            'Admin.menuParticipants', false],
    ['admin/sesi',               'Admin.menuSessions',     false],
    ['admin/analitik/level',     'Admin.menuAnLevel',      false, 'Admin.menuAnalytics'],
    ['admin/analitik/node',      'Admin.menuAnNode',       false, 'Admin.menuAnalytics'],
    ['admin/analitik/butir',     'Admin.menuAnItem',       false, 'Admin.menuAnalytics'],
    ['admin/analitik/indikator', 'Admin.menuAnIndicator',  false, 'Admin.menuAnalytics'],
    ['admin/analitik/prepost',   'Admin.menuAnPrePost',    false, 'Admin.menuAnalytics'],
    ['admin/masukan',            'Admin.menuFeedback',     false],
    ['admin/ekspor',             'Admin.menuExport',       false],
    ['admin/konten',             'Admin.menuContent',      true],
    ['admin/konten/impor-bank',  'Admin.menuBankImport',   true, 'Admin.menuContent'],
    ['admin/media',              'Admin.menuMedia',        true],
    ['admin/studi',              'Admin.menuStudy',        true],
    ['admin/tata-kelola',        'Admin.menuGovernance',   true],
    ['admin/staf',               'Admin.menuStaff',        true],
];
?>
<nav class="admin-sidebar" aria-label="<?= esc(lang('Admin.mainMenu')) ?>">
  <ul>
    <?php foreach ($menu as $entry): ?>
      <?php [$href, $label, $adminOnly] = $entry; ?>
      <?php if ($adminOnly && ! $isAdmin) {
          continue;
      } ?>
      <?php $active = $path === $href || str_starts_with($path, $href . '/'); ?>
      <li class="<?= isset($entry[3]) ? 'is-sub' : '' ?>">
        <a href="<?= base_url($href) ?>" class="<?= $active ? 'is-active' : '' ?>"
           <?= $active ? 'aria-current="page"' : '' ?>>
          <?php if (isset($entry[3])): ?><span class="menu-parent"><?= esc(lang($entry[3])) ?> ▸</span><?php endif ?>
          <?= esc(lang($label)) ?>
        </a>
      </li>
    <?php endforeach ?>
  </ul>
</nav>
