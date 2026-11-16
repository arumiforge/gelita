<?php
/**
 * Staff guide — /admin/panduan-staf → StaffGuideController::index (every staff role).
 *
 * A table of contents beside the chapters (above them on narrow screens).
 * Chapters are <details>: the reader's own and the shared ones start open,
 * the other roles' chapters start folded. A step's page link shows only
 * when the reader may open that page.
 *
 * @var list<array<string, mixed>> $chapters StaffGuideController::order()
 * @var string                     $role
 * @var string                     $roleName
 */
use App\Libraries\StaffAccess;
use App\Libraries\StaffGuideContent as Guide;

$roleNames = static fn (array $roles): string => implode(', ', array_map(static fn (string $r): string => StaffAccess::label($r), $roles));

$pageLink = static function (array $link): string {
    [$href, $label, $capability] = $link;

    if (staff_can($capability)) {
        return '<a class="guide-page-link" href="' . esc(base_url($href), 'attr') . '">' . icon('arrow-right') . esc(lang('Admin.guideOpenPage', [$label])) . '</a>';
    }

    return '<span class="guide-page-link is-off">' . esc(lang('Admin.guideNoAccess', [$label])) . '</span>';
};
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title'   => lang('Admin.guideTitle'),
    'eyebrow' => lang('Admin.groupHelp'),
    'lead'    => lang('Admin.guideLead', [$roleName]),
]) ?>

<div class="guide" id="isi">
  <nav class="guide-toc" aria-labelledby="guide-toc-title">
    <h2 class="guide-toc-title" id="guide-toc-title"><?= esc(lang('Admin.guideToc')) ?></h2>
    <ol>
      <?php foreach ($chapters as $chapter): ?>
        <li>
          <a href="#<?= esc($chapter['id'], 'attr') ?>"><?= esc($chapter['title']) ?></a>
          <?php if ($chapter['mine']): ?><span class="badge is-ok"><?= esc(lang('Admin.guideYourRole')) ?></span><?php endif ?>
          <ol>
            <?php foreach ($chapter['sections'] as $section): ?>
              <li><a href="#<?= esc($section['id'], 'attr') ?>"><?= esc($section['title']) ?></a></li>
            <?php endforeach ?>
          </ol>
        </li>
      <?php endforeach ?>
    </ol>
  </nav>

  <div class="guide-body">
    <?php foreach ($chapters as $chapter): ?>
      <details class="guide-chapter" id="<?= esc($chapter['id'], 'attr') ?>"<?= $chapter['open'] ? ' open' : '' ?>>
        <summary>
          <span class="guide-chapter-title"><?= esc($chapter['title']) ?></span>
          <span class="guide-chapter-meta">
            <?php if ($chapter['mine']): ?>
              <span class="badge is-ok"><?= esc(lang('Admin.guideYourRole')) ?></span>
            <?php elseif (! $chapter['open']): ?>
              <span class="badge"><?= esc(lang('Admin.guideForRoles', [$roleNames($chapter['roles'])])) ?></span>
            <?php endif ?>
            <?= icon('down', 'guide-chevron') ?>
          </span>
        </summary>
        <p class="guide-chapter-lead"><?= Guide::inline($chapter['lead']) ?></p>

        <?php foreach ($chapter['sections'] as $section): ?>
          <section class="panel guide-section" id="<?= esc($section['id'], 'attr') ?>" aria-labelledby="<?= esc($section['id'], 'attr') ?>-t">
            <h3 class="panel-title" id="<?= esc($section['id'], 'attr') ?>-t"><?= esc($section['title']) ?></h3>

            <?php if (! empty($section['lead'])): ?>
              <p><?= Guide::inline($section['lead']) ?></p>
            <?php endif ?>

            <?php if (! empty($section['steps'])): ?>
              <ol class="guide-steps">
                <?php foreach ($section['steps'] as $step): ?>
                  <?php [$text, $link] = is_array($step) ? $step : [$step, null] ?>
                  <li><span><?= Guide::inline($text) ?></span><?= $link !== null ? $pageLink($link) : '' ?></li>
                <?php endforeach ?>
              </ol>
            <?php endif ?>

            <?php if (! empty($section['warn'])): ?>
              <div class="alert alert-warn" role="note"><?= icon('warn') ?><div class="alert-body"><p><?= Guide::inline($section['warn']) ?></p></div></div>
            <?php endif ?>

            <?php if (! empty($section['table'])): ?>
              <?php [$head, $rows] = $section['table'] ?>
              <div class="table-wrap is-short guide-table">
                <table class="data-table">
                  <thead><tr><?php foreach ($head as $cell): ?><th scope="col"><?= esc($cell) ?></th><?php endforeach ?></tr></thead>
                  <tbody>
                    <?php foreach ($rows as $row): ?>
                      <tr><?php foreach ($row as $i => $cell): ?><<?= $i === 0 ? 'th scope="row"' : 'td' ?>><?= Guide::inline($cell) ?></<?= $i === 0 ? 'th' : 'td' ?>><?php endforeach ?></tr>
                    <?php endforeach ?>
                  </tbody>
                </table>
              </div>
            <?php endif ?>

            <?php if (! empty($section['list'])): ?>
              <ul class="guide-list">
                <?php foreach ($section['list'] as $item): ?>
                  <li><?= Guide::inline($item) ?></li>
                <?php endforeach ?>
              </ul>
            <?php endif ?>

            <?php if (! empty($section['note'])): ?>
              <div class="alert alert-info" role="note"><?= icon('info') ?><div class="alert-body"><p><?= Guide::inline($section['note']) ?></p></div></div>
            <?php endif ?>

            <p class="guide-top"><a href="#isi"><?= icon('up') ?> <?= esc(lang('Admin.guideBackToTop')) ?></a></p>
          </section>
        <?php endforeach ?>
      </details>
    <?php endforeach ?>
  </div>
</div>
<?= $this->endSection() ?>
