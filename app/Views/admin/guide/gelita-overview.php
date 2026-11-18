<?php
/**
 * First page of the GELITA guide — /admin/panduan-gelita →
 * GelitaGuideController::index.
 *
 * Lead, where the other guides and the asset catalogue take over, one card
 * per chapter in reading order, then cards for the topics readers look for
 * most (GelitaGuideContent::POPULAR).
 *
 * @var list<array<string, mixed>> $chapters GelitaGuideContent::chapters()
 * @var list<array<string, mixed>> $popular  sections with chapterTitle
 * @var string                     $query
 * @var class-string<\App\Libraries\GuideContent> $guide
 * @var array<string, mixed>       $site
 */
$icons = ['mulai' => 'compass', 'asal' => 'flask', 'model' => 'target', 'konstruk' => 'chart', 'permainan' => 'puzzle', 'kelas5' => 'grid', 'revisi10' => 'shield', 'lapangan' => 'map', 'teknologi' => 'database', 'menyatu' => 'link'];
?>
<?= $this->extend('layouts/guide') ?>

<?= $this->section('content') ?>
<article class="gd-article gd-home" aria-labelledby="gd-title">
  <p class="gd-eyebrow"><?= esc($site['title']) ?></p>
  <h1 class="gd-title" id="gd-title"><?= esc(lang('Admin.gguideHomeTitle')) ?></h1>
  <p class="gd-lead"><?= esc(lang('Admin.gguideLead')) ?></p>

  <div class="gd-callout is-info" role="note"><?= icon('info') ?><p><?= $guide::inline(lang('Admin.gguideElsewhere', [$guide::CATALOGUE])) ?></p></div>

  <h2 class="gd-h2"><?= esc(lang('Admin.guideChapters')) ?></h2>
  <ul class="gd-cards">
    <?php foreach ($chapters as $chapter): ?>
      <li>
        <a class="gd-card" href="<?= esc($guide::url($chapter['sections'][0]['id']) . $query, 'attr') ?>">
          <span class="gd-card-icon" aria-hidden="true"><?= icon($icons[$chapter['id']] ?? 'book') ?></span>
          <span class="gd-card-title"><?= esc($chapter['title']) ?></span>
          <span class="gd-card-text"><?= $guide::inline($chapter['lead'], $query) ?></span>
          <span class="gd-card-meta"><?= esc(lang('Admin.guideTopics', [count($chapter['sections'])])) ?></span>
        </a>
      </li>
    <?php endforeach ?>
  </ul>

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
</article>
<?= $this->endSection() ?>
