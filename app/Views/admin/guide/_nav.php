<?php
/**
 * Staff guide menu (layouts/guide): search box, a guest's role picker, the
 * first page, then one group per chapter with its topics. The reader's own
 * and shared chapters start open, other roles' chapters folded, and the
 * chapter of the current page always open. Each topic carries its plain
 * text in data-search for the search box (js/admin/guide.js).
 *
 * @var list<array<string, mixed>> $chapters StaffGuideController::order()
 * @var string $current section id, '' on the first page
 * @var string $role
 * @var string $query
 * @var bool   $isStaff
 */
use App\Libraries\StaffAccess;
use App\Libraries\StaffGuideContent as Guide;

$roleNames = static fn (array $roles): string => implode(', ', array_map(static fn (string $r): string => StaffAccess::label($r), $roles));
?>
<nav class="gd-nav" id="gd-nav" aria-label="<?= esc(lang('Admin.guideNav'), 'attr') ?>">
  <div class="gd-search">
    <label class="visually-hidden" for="gd-search"><?= esc(lang('Admin.guideSearch')) ?></label>
    <?= icon('search') ?>
    <input type="search" id="gd-search" placeholder="<?= esc(lang('Admin.guideSearchPlaceholder'), 'attr') ?>" autocomplete="off">
  </div>

  <?php if (! $isStaff): ?>
    <div class="gd-roles" role="group" aria-label="<?= esc(lang('Admin.guidePickRole'), 'attr') ?>">
      <span><?= esc(lang('Admin.guidePickRole')) ?></span>
      <?php foreach (StaffAccess::ROLES as $option): ?>
        <a href="<?= esc(Guide::url($current) . '?peran=' . $option, 'attr') ?>"<?= $option === $role ? ' aria-current="page"' : '' ?>><?= esc(StaffAccess::label($option)) ?></a>
      <?php endforeach ?>
    </div>
  <?php endif ?>

  <a class="gd-nav-home" href="<?= esc(Guide::url() . $query, 'attr') ?>"<?= $current === '' ? ' aria-current="page"' : '' ?>><?= icon('home') ?> <?= esc(lang('Admin.guideHome')) ?></a>

  <?php foreach ($chapters as $chapter): ?>
    <?php $here = in_array($current, array_column($chapter['sections'], 'id'), true) ?>
    <details class="gd-group" data-chapter="<?= esc($chapter['id'], 'attr') ?>"<?= $chapter['open'] || $here ? ' open' : '' ?>>
      <summary>
        <span><?= esc($chapter['title']) ?></span>
        <?php if ($chapter['mine']): ?>
          <span class="badge is-ok"><?= esc(lang('Admin.guideYourRole')) ?></span>
        <?php elseif (! $chapter['open']): ?>
          <span class="badge"><?= esc(lang('Admin.guideForRoles', [$roleNames($chapter['roles'])])) ?></span>
        <?php endif ?>
        <?= icon('down', 'gd-chevron') ?>
      </summary>
      <ul>
        <?php foreach ($chapter['sections'] as $section): ?>
          <li><a href="<?= esc(Guide::url($section['id']) . $query, 'attr') ?>" data-search="<?= esc(Guide::plainText($section), 'attr') ?>"<?= $section['id'] === $current ? ' aria-current="page"' : '' ?>><?= esc($section['title']) ?></a></li>
        <?php endforeach ?>
      </ul>
    </details>
  <?php endforeach ?>

  <p class="gd-search-empty" hidden><?= esc(lang('Admin.guideSearchEmpty')) ?></p>
</nav>
