<?php
/**
 * Guide menu (layouts/guide; staff and validator guides): search box, a guest's role picker, the
 * first page, then one group per chapter with its topics. The reader's own
 * and shared chapters start open, other roles' chapters folded, and the
 * chapter of the current page always open. Each topic carries its plain
 * text in data-search for the search box (js/admin/guide.js).
 *
 * @var list<array<string, mixed>> $chapters StaffGuideController::order()
 * @var string $current section id, '' on the first page
 * @var string $role  the reader's role (staff guide) or panel (validator guide)
 * @var array<string, mixed> $site  the guide's settings (see the controllers' site())
 * @var string $query
 * @var class-string<\App\Libraries\GuideContent> $guide
 * @var bool   $isStaff
 */
?>
<nav class="gd-nav" id="gd-nav" aria-label="<?= esc(lang('Admin.guideNav'), 'attr') ?>">
  <div class="gd-search">
    <label class="visually-hidden" for="gd-search"><?= esc(lang('Admin.guideSearch')) ?></label>
    <?= icon('search') ?>
    <input type="search" id="gd-search" placeholder="<?= esc(lang('Admin.guideSearchPlaceholder'), 'attr') ?>" autocomplete="off">
  </div>

  <?php if ($site['pickAlways'] || ! $isStaff): ?>
    <div class="gd-roles" role="group" aria-label="<?= esc($site['pickLabel'], 'attr') ?>">
      <span><?= esc($site['pickLabel']) ?></span>
      <?php foreach ($site['options'] as $option => $optionLabel): ?>
        <a href="<?= esc($guide::url($current) . '?' . $site['param'] . '=' . $option, 'attr') ?>"<?= $option === $role ? ' aria-current="page"' : '' ?>><?= esc($optionLabel) ?></a>
      <?php endforeach ?>
    </div>
  <?php endif ?>

  <a class="gd-nav-home" href="<?= esc($guide::url() . $query, 'attr') ?>"<?= $current === '' ? ' aria-current="page"' : '' ?>><?= icon('home') ?> <?= esc(lang('Admin.guideHome')) ?></a>

  <?php foreach ($chapters as $chapter): ?>
    <?php $here = in_array($current, array_column($chapter['sections'], 'id'), true) ?>
    <details class="gd-group" data-chapter="<?= esc($chapter['id'], 'attr') ?>"<?= $chapter['open'] || $here ? ' open' : '' ?>>
      <summary>
        <span><?= esc($chapter['title']) ?></span>
        <?php if ($chapter['mine']): ?>
          <span class="badge is-ok"><?= esc($site['mineBadge']) ?></span>
        <?php elseif (! $chapter['open']): ?>
          <span class="badge"><?= esc(lang('Admin.guideForRoles', [$chapter['for']])) ?></span>
        <?php endif ?>
        <?= icon('down', 'gd-chevron') ?>
      </summary>
      <ul>
        <?php foreach ($chapter['sections'] as $section): ?>
          <li><a href="<?= esc($guide::url($section['id']) . $query, 'attr') ?>" data-search="<?= esc($guide::plainText($section), 'attr') ?>"<?= $section['id'] === $current ? ' aria-current="page"' : '' ?>><?= esc($section['title']) ?></a></li>
        <?php endforeach ?>
      </ul>
    </details>
  <?php endforeach ?>

  <p class="gd-search-empty" hidden><?= esc(lang('Admin.guideSearchEmpty')) ?></p>
</nav>
