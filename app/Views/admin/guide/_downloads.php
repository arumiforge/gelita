<?php
/**
 * "Dokumen untuk diunduh" box of the validator and staff guides (owner,
 * 20 November 2026): one row per research PDF the guide offers, with its
 * label, what is in it, kind and size, linking to BASE/unduh/{key}. Rows come
 * from GuideDownloads::rows(), so only files present here are listed.
 *
 * @var list<array{key: string, label: string, text: string, url: string, meta: string, panels: string}> $downloads
 * @var bool   $showPanels name each row's panel (validator guide with no panel picked)
 * @var string $downloadsLead
 */
?>
<section class="gd-downloads" aria-labelledby="gd-dl-title">
  <h2 class="gd-h3" id="gd-dl-title"><?= icon('download') ?> <?= esc(lang('Admin.guideDownloadsTitle')) ?></h2>
  <?php if (($downloadsLead ?? '') !== ''): ?>
    <p class="gd-dl-lead"><?= esc($downloadsLead) ?></p>
  <?php endif ?>
  <ul class="gd-dl-list">
    <?php foreach ($downloads as $row): ?>
      <li>
        <a class="gd-dl" href="<?= esc($row['url'], 'attr') ?>" download data-download="<?= esc($row['key'], 'attr') ?>">
          <span class="gd-dl-icon" aria-hidden="true"><?= icon('download') ?></span>
          <span class="gd-dl-body">
            <?php if ($showPanels && $row['panels'] !== ''): ?><span class="gd-dl-eyebrow"><?= esc($row['panels']) ?></span><?php endif ?>
            <span class="gd-dl-title"><?= esc($row['label']) ?></span>
            <span class="gd-dl-text"><?= esc($row['text']) ?></span>
          </span>
          <span class="gd-dl-meta"><?= esc($row['meta']) ?></span>
        </a>
      </li>
    <?php endforeach ?>
  </ul>
</section>
