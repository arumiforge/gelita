<?php
/**
 * One staff guide topic — /admin/panduan-staf/{id} → StaffGuideController::page.
 *
 * The chapter as eyebrow, the topic as title, its lead, then its parts in a
 * fixed order (steps, warning, table, list, note; the glossary entries on
 * the glossary page), and previous/next cards. A step's page link shows
 * only when the reader may open that page; a guest sees the page name.
 * Glossary terms in the text open a popover (admin/guide/_terms).
 *
 * @var array<string, mixed>      $section  with chapter, chapterTitle
 * @var array<string, mixed>|null $previous
 * @var array<string, mixed>|null $next
 * @var list<string>              $terms    term keys used on this page
 * @var string                    $query
 * @var bool                      $isStaff
 */
use App\Libraries\StaffGuideContent as Guide;

$inline   = static fn (string $text): string => Guide::inline($text, $query);
$pageLink = static function (array $link) use ($isStaff): string {
    [$href, $label, $capability] = $link;

    if (! $isStaff) {
        return '<span class="guide-page-link is-off">' . esc(lang('Admin.guideGuestPage', [$label])) . '</span>';
    }

    if (staff_can($capability)) {
        return '<a class="guide-page-link" href="' . esc(base_url($href), 'attr') . '">' . icon('arrow-right') . esc(lang('Admin.guideOpenPage', [$label])) . '</a>';
    }

    return '<span class="guide-page-link is-off">' . esc(lang('Admin.guideNoAccess', [$label])) . '</span>';
};
$titles = [];

foreach ($chapters as $chapter) {
    foreach ($chapter['sections'] as $s) {
        $titles[$s['id']] = $s['title'];
    }
}
?>
<?= $this->extend('layouts/guide') ?>

<?= $this->section('content') ?>
<article class="gd-article" id="<?= esc($section['id'], 'attr') ?>" aria-labelledby="gd-title">
  <p class="gd-eyebrow"><?= esc($section['chapterTitle']) ?></p>
  <h1 class="gd-title" id="gd-title"><?= esc($section['title']) ?></h1>

  <?php if (! empty($section['lead'])): ?>
    <p class="gd-lead"><?= $inline($section['lead']) ?></p>
  <?php endif ?>

  <?php if (! empty($section['steps'])): ?>
    <ol class="gd-steps">
      <?php foreach ($section['steps'] as $step): ?>
        <?php [$text, $link] = is_array($step) ? $step : [$step, null] ?>
        <li><span><?= $inline($text) ?></span><?= $link !== null ? $pageLink($link) : '' ?></li>
      <?php endforeach ?>
    </ol>
  <?php endif ?>

  <?php if (! empty($section['warn'])): ?>
    <div class="gd-callout is-warn" role="note"><?= icon('warn') ?><p><?= $inline($section['warn']) ?></p></div>
  <?php endif ?>

  <?php if (! empty($section['table'])): ?>
    <?php [$head, $rows] = $section['table'] ?>
    <div class="gd-table">
      <table>
        <thead><tr><?php foreach ($head as $cell): ?><th scope="col"><?= esc($cell) ?></th><?php endforeach ?></tr></thead>
        <tbody>
          <?php foreach ($rows as $row): ?>
            <tr><?php foreach ($row as $i => $cell): ?><<?= $i === 0 ? 'th scope="row"' : 'td' ?>><?= $inline($cell) ?></<?= $i === 0 ? 'th' : 'td' ?>><?php endforeach ?></tr>
          <?php endforeach ?>
        </tbody>
      </table>
    </div>
  <?php endif ?>

  <?php if (! empty($section['list'])): ?>
    <ul class="gd-list">
      <?php foreach ($section['list'] as $item): ?>
        <li><?= $inline($item) ?></li>
      <?php endforeach ?>
    </ul>
  <?php endif ?>

  <?php if (! empty($section['note'])): ?>
    <div class="gd-callout is-info" role="note"><?= icon('info') ?><p><?= $inline($section['note']) ?></p></div>
  <?php endif ?>

  <?php if (! empty($section['glossary'])): ?>
    <dl class="gd-glossary">
      <?php foreach (Guide::terms() as $key => $term): ?>
        <div class="gd-term" id="istilah-<?= esc($key, 'attr') ?>">
          <dt><?= esc($term['term']) ?></dt>
          <dd>
            <p><?= $inline($term['def']) ?></p>
            <p class="gd-example"><span><?= esc(lang('Admin.guideExample')) ?></span> <?= $inline($term['example']) ?></p>
            <?php if ($term['more'] !== null): ?>
              <a class="gd-term-more" href="<?= esc(Guide::url($term['more']) . $query, 'attr') ?>"><?= esc(lang('Admin.guideTermMore', [$titles[$term['more']] ?? ''])) ?> <?= icon('arrow-right') ?></a>
            <?php endif ?>
          </dd>
        </div>
      <?php endforeach ?>
    </dl>
  <?php endif ?>

  <?php if ($previous !== null || $next !== null): ?>
    <nav class="gd-pager" aria-label="<?= esc(lang('Admin.guidePrevious') . ' / ' . lang('Admin.guideNext'), 'attr') ?>">
      <?php if ($previous !== null): ?>
        <a class="gd-pager-link is-prev" href="<?= esc(Guide::url($previous['id']) . $query, 'attr') ?>" rel="prev">
          <span class="gd-pager-dir"><?= icon('left') ?> <?= esc(lang('Admin.guidePrevious')) ?></span>
          <span class="gd-pager-title"><?= esc($previous['title']) ?></span>
        </a>
      <?php endif ?>
      <?php if ($next !== null): ?>
        <a class="gd-pager-link is-next" href="<?= esc(Guide::url($next['id']) . $query, 'attr') ?>" rel="next">
          <span class="gd-pager-dir"><?= esc(lang('Admin.guideNext')) ?> <?= icon('right') ?></span>
          <span class="gd-pager-title"><?= esc($next['title']) ?></span>
        </a>
      <?php endif ?>
    </nav>
  <?php endif ?>
</article>
<?= $this->endSection() ?>

<?= $this->section('popovers') ?>
<?= $this->include('admin/guide/_terms') ?>
<?= $this->endSection() ?>
