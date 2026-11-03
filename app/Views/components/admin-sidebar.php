<?php
/**
 * Sidebar panel (Dok 3 §8.1): 248 px, menyempit menjadi kolom ikon 72 px di
 * bawah 1280 px (label tetap terbaca pembaca layar dan muncul sebagai
 * tooltip). Di bawah 768 px menjadi laci yang dibuka tautan #admin-menu
 * (CSS :target, tanpa JavaScript).
 *
 * Daftar menu dari App\Libraries\AdminMenu; items the role holds no
 * capability for are not rendered.
 */
use App\Libraries\AdminMenu;

$items = AdminMenu::items(static fn (string $capability): bool => staff_can($capability), service('request')->getPath());
$group = null;
?>
<nav class="admin-sidebar" id="admin-menu" aria-label="<?= esc(lang('Admin.mainMenu'), 'attr') ?>">
  <a class="sidebar-brand" href="<?= base_url('admin') ?>" aria-label="<?= esc(lang('Admin.panelTitle'), 'attr') ?> — <?= esc(lang('Admin.menuDashboard'), 'attr') ?>">
    <span class="sidebar-mark" aria-hidden="true">G</span>
    <span class="sidebar-brand-text">GELITA <small>Panel penelitian</small></span>
  </a>
  <a class="sidebar-close" href="#"><?= icon('cross') ?> <span><?= esc(lang('Admin.closeMenu')) ?></span></a>
  <ul class="sidebar-list">
    <?php foreach ($items as $item): ?>
      <?php if ($item['group'] !== $group): $group = $item['group']; ?>
        <li class="sidebar-group" role="presentation"><span><?= esc($group) ?></span></li>
      <?php endif ?>
      <li>
        <a href="<?= base_url($item['href']) ?>" class="sidebar-link<?= $item['active'] ? ' is-active' : '' ?>"
           title="<?= esc($item['label'], 'attr') ?>"<?= $item['active'] ? ' aria-current="page"' : '' ?>>
          <?= icon($item['icon']) ?>
          <span class="sidebar-label"><?= esc($item['label']) ?></span>
        </a>
      </li>
    <?php endforeach ?>
  </ul>
</nav>
