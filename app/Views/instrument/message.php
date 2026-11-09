<?php
/**
 * A plain module screen: closed, waiting, done, T12 (34 §5.1, §8.1).
 *
 * @var string                    $title
 * @var list<string>              $paragraphs
 * @var array{0: string, 1: string}|null $link label, path
 */
?>
<?= $this->extend('layouts/instrument') ?>
<?= $this->section('title') ?><?= esc($title) ?><?= $this->endSection() ?>
<?= $this->section('content') ?>
<div class="mx-auto" style="max-width: 44rem">
  <h1 class="h3 mb-3"><?= esc($title) ?></h1>
  <?php foreach ($paragraphs as $paragraph): ?><p><?= esc($paragraph) ?></p><?php endforeach ?>
  <?php if (! empty($link)): ?>
    <a class="btn btn-primary btn-lg" href="<?= esc(site_url($link[1]), 'attr') ?>"><?= esc($link[0]) ?></a>
  <?php endif ?>
</div>
<?= $this->endSection() ?>
