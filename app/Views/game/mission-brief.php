<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc($node->text('title', $locale)) ?> · GELITA<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="mission-brief" data-screen="mission-brief">
  <h1><?= esc($node->text('title', $locale)) ?></h1>
  <?= $this->include('partials/flash') ?>

  <p class="mission-description"><?= esc($node->text('description', $locale)) ?></p>
  <p class="mission-instruction"><?= esc($node->text('instruction', $locale)) ?></p>

  <?php if ($best !== null): ?>
    <p>Hasil terbaikmu: <?= esc($best->score) ?> · <?= stars_html((int) $best->stars) ?></p>
  <?php endif ?>

  <a class="btn btn-primary btn-lg"
     href="<?= base_url('tantangan/' . $level->code . '/' . $sequence) ?>"><?= esc(lang('Game.start')) ?></a>
  <a class="btn btn-quiet" href="<?= base_url('wilayah/' . $level->code) ?>"><?= esc(lang('Game.back')) ?></a>
</section>
<?= $this->endSection() ?>
