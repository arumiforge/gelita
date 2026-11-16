<?php
/**
 * Panel sidebar (Dok 3 §8.1): 248 px, narrowing to a 72 px icon rail below
 * 1280 px (labels stay readable for screen readers and show as tooltips).
 * Below 768 px it becomes a drawer opened by the #admin-menu link (CSS
 * :target, no JavaScript).
 *
 * Groups are <details> elements, so they fold and unfold without
 * JavaScript; admin/sidebar.js only remembers what the reader folded. The
 * group with the open page always starts open. The open item may carry a
 * sub-menu (the pages of one field session). Items come from
 * App\Libraries\AdminMenu; items the role holds no capability for are not
 * rendered.
 */
use App\Libraries\AdminMenu;

$groups = AdminMenu::groups(static fn (string $capability): bool => staff_can($capability), service('request')->getPath());

$link = static function (array $item, string $class = 'sidebar-link'): string {
    // One aria-current="page" per page: a parent whose sub-page is open is only "true"
    $current = in_array(true, array_column($item['children'] ?? [], 'active'), true) ? 'true' : 'page';

    return '<a href="' . base_url($item['href']) . '" class="' . $class . ($item['active'] ? ' is-active' : '') . '"'
        . ' title="' . esc($item['label'], 'attr') . '"' . ($item['active'] ? ' aria-current="' . $current . '"' : '') . '>'
        . icon($item['icon'])
        . '<span class="sidebar-label">' . esc($item['label']) . '</span></a>';
};

$list = static function (array $items) use ($link): string {
    $html = '';

    foreach ($items as $item) {
        $html .= '<li>' . $link($item);

        if ($item['children'] !== []) {
            $html .= '<ul class="sidebar-sub" aria-label="' . esc($item['label'], 'attr') . '">';

            foreach ($item['children'] as $child) {
                $html .= '<li>' . $link($child, 'sidebar-link sidebar-sublink') . '</li>';
            }

            $html .= '</ul>';
        }

        $html .= '</li>';
    }

    return $html;
};
?>
<nav class="admin-sidebar" id="admin-menu" aria-label="<?= esc(lang('Admin.mainMenu'), 'attr') ?>">
  <a class="sidebar-brand" href="<?= base_url('admin') ?>" aria-label="<?= esc(lang('Admin.panelTitle'), 'attr') ?> — <?= esc(lang('Admin.menuDashboard'), 'attr') ?>">
    <span class="sidebar-mark" aria-hidden="true">G</span>
    <span class="sidebar-brand-text">GELITA <small>Panel penelitian</small></span>
  </a>
  <a class="sidebar-close" href="#"><?= icon('cross') ?> <span><?= esc(lang('Admin.closeMenu')) ?></span></a>
  <ul class="sidebar-list">
    <?php foreach ($groups as $group): ?>
      <?php if ($group['key'] === null): ?>
        <?= $list($group['items']) ?>
      <?php else: ?>
        <li class="sidebar-section">
          <details class="sidebar-group" data-group="<?= esc($group['key'], 'attr') ?>"<?= $group['open'] ? ' open' : '' ?>>
            <summary><span class="sidebar-group-label"><?= esc($group['label']) ?></span><?= icon('down', 'sidebar-group-chevron') ?></summary>
            <ul class="sidebar-group-list"><?= $list($group['items']) ?></ul>
          </details>
        </li>
      <?php endif ?>
    <?php endforeach ?>
  </ul>
</nav>
