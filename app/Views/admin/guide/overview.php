<?php
/**
 * First page of the staff guide — /admin/panduan-staf → StaffGuideController::index.
 *
 * Title and lead per reader, one card per chapter (the reader's own marked,
 * each leading to the chapter's first topic), then cards for the topics
 * staff look for most (StaffGuideContent::POPULAR).
 *
 * @var list<array<string, mixed>> $chapters StaffGuideController::order()
 * @var list<array<string, mixed>> $popular  sections with chapterTitle
 * @var string                     $role
 * @var string                     $roleName
 * @var string                     $query
 * @var bool                       $isStaff
 */
use App\Libraries\StaffGuideContent as Guide;

$icons = ['mulai' => 'compass', 'lapangan' => 'map', 'ketua' => 'chart', 'admin' => 'grid', 'larangan' => 'warn'];
?>
<?= $this->extend('layouts/guide') ?>

<?= $this->section('content') ?>
<article class="gd-article gd-home" aria-labelledby="gd-title">
  <p class="gd-eyebrow"><?= esc(lang('Admin.guideTitle')) ?></p>
  <h1 class="gd-title" id="gd-title"><?= esc(lang('Admin.guideHomeTitle')) ?></h1>
  <p class="gd-lead"><?= esc(match (true) {
      $isStaff     => lang('Admin.guideLead', [$roleName]),
      $role !== '' => lang('Admin.guideLeadPicked', [$roleName]),
      default      => lang('Admin.guideLeadGuest'),
  }) ?></p>

  <h2 class="gd-h2"><?= esc(lang('Admin.guideChapters')) ?></h2>
  <ul class="gd-cards">
    <?php foreach ($chapters as $chapter): ?>
      <li>
        <a class="gd-card<?= $chapter['mine'] ? ' is-mine' : '' ?>" href="<?= esc(Guide::url($chapter['sections'][0]['id']) . $query, 'attr') ?>">
          <span class="gd-card-icon" aria-hidden="true"><?= icon($icons[$chapter['id']] ?? 'book') ?></span>
          <span class="gd-card-title"><?= esc($chapter['title']) ?></span>
          <span class="gd-card-text"><?= Guide::inline($chapter['lead'], $query) ?></span>
          <span class="gd-card-meta">
            <?= esc(lang('Admin.guideTopics', [count($chapter['sections'])])) ?>
            <?php if ($chapter['mine']): ?><span class="badge is-ok"><?= esc(lang('Admin.guideYourRole')) ?></span><?php endif ?>
          </span>
        </a>
      </li>
    <?php endforeach ?>
  </ul>

  <h2 class="gd-h2"><?= esc(lang('Admin.guidePopular')) ?></h2>
  <ul class="gd-cards is-compact">
    <?php foreach ($popular as $section): ?>
      <li>
        <a class="gd-card" href="<?= esc(Guide::url($section['id']) . $query, 'attr') ?>">
          <span class="gd-card-eyebrow"><?= esc($section['chapterTitle']) ?></span>
          <span class="gd-card-title"><?= esc($section['title']) ?></span>
        </a>
      </li>
    <?php endforeach ?>
  </ul>
</article>
<?= $this->endSection() ?>
