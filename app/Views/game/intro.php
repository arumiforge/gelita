<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?>Cerita pembuka · GELITA<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="intro" data-screen="intro">
  <h1>Cerita pembuka</h1>
  <?= $this->include('partials/flash') ?>

  <ol class="dialogue-slides">
    <?php foreach ($slides as $slide): ?>
      <li class="dialogue-slide" data-character="<?= esc($slide['character_code'] ?? 'jaka') ?>">
        <?php if (tr($slide, 'title', $locale) !== ''): ?>
          <h2><?= esc(tr($slide, 'title', $locale)) ?></h2>
        <?php endif ?>
        <p><?= esc(tr($slide, 'text', $locale)) ?></p>
      </li>
    <?php endforeach ?>
  </ol>

  <a class="btn btn-primary btn-lg" href="<?= base_url('peta') ?>"><?= esc(lang('Game.continue')) ?></a>
  <?= $this->include('partials/stage-note') ?>
</section>
<?= $this->endSection() ?>
