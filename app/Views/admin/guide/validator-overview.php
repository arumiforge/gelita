<?php
/**
 * First page of the validator guide — /admin/panduan-validator →
 * ValidatorGuideController::index.
 *
 * Lead per picked panel, one card per chapter (the picked panel's marked),
 * then cards for the topics validators need most
 * (ValidatorGuideContent::POPULAR).
 *
 * @var list<array<string, mixed>> $chapters ValidatorGuideController::order()
 * @var list<array<string, mixed>> $popular  sections with chapterTitle
 * @var string                     $role     the picked panel, '' for none
 * @var string                     $roleName
 * @var string                     $query
 * @var class-string<\App\Libraries\GuideContent> $guide
 * @var array<string, mixed>       $site
 */
$icons = ['mulai' => 'compass', 'b5a' => 'eye', 'materi' => 'book', 'budaya' => 'map', 'media' => 'grid', 'bermain' => 'play', 'kembali' => 'upload', 'rambu' => 'warn'];
?>
<?= $this->extend('layouts/guide') ?>

<?= $this->section('content') ?>
<article class="gd-article gd-home" aria-labelledby="gd-title">
  <p class="gd-eyebrow"><?= esc($site['title']) ?></p>
  <h1 class="gd-title" id="gd-title"><?= esc(lang('Admin.vguideHomeTitle')) ?></h1>
  <p class="gd-lead"><?= esc($role !== '' ? lang('Admin.vguideLeadPicked', [$roleName]) : lang('Admin.vguideLead')) ?></p>

  <?php if ($role === 'b5a'): ?>
    <div class="gd-callout is-warn" role="note"><?= icon('warn') ?><p><?= esc(lang('Admin.vguideBlindFirst')) ?></p></div>
  <?php endif ?>

  <h2 class="gd-h2"><?= esc(lang('Admin.guideChapters')) ?></h2>
  <ul class="gd-cards">
    <?php foreach ($chapters as $chapter): ?>
      <li id="bab-<?= esc($chapter['id'], 'attr') ?>">
        <a class="gd-card<?= $chapter['mine'] ? ' is-mine' : '' ?>" href="<?= esc($guide::url($chapter['sections'][0]['id']) . $query, 'attr') ?>">
          <span class="gd-card-icon" aria-hidden="true"><?= icon($icons[$chapter['id']] ?? 'book') ?></span>
          <span class="gd-card-title"><?= esc($chapter['title']) ?></span>
          <span class="gd-card-text"><?= $guide::inline($chapter['lead'], $query) ?></span>
          <span class="gd-card-meta">
            <?= esc(lang('Admin.guideTopics', [count($chapter['sections'])])) ?>
            <?php if ($chapter['mine']): ?><span class="badge is-ok"><?= esc($site['mineBadge']) ?></span><?php endif ?>
          </span>
        </a>
      </li>
    <?php endforeach ?>
  </ul>

  <?php if ($role !== 'b5a'): ?>
    <h2 class="gd-h2"><?= esc(lang('Admin.guidePopular')) ?></h2>
    <ul class="gd-cards is-compact">
      <?php foreach ($popular as $section): ?>
        <li>
          <a class="gd-card" href="<?= esc($guide::url($section['id']) . $query, 'attr') ?>">
            <span class="gd-card-eyebrow"><?= esc($section['chapterTitle']) ?></span>
            <span class="gd-card-title"><?= esc($section['title']) ?></span>
          </a>
        </li>
      <?php endforeach ?>
    </ul>
  <?php endif ?>
</article>
<?= $this->endSection() ?>
