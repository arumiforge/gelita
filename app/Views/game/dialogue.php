<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc($level->text('name', $locale)) ?> · GELITA<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="dialogue" data-screen="dialogue" data-level="<?= esc($level->code) ?>">
  <h1><?= esc($level->text('name', $locale)) ?></h1>

  <ol class="dialogue-slides">
    <?php foreach ($slides as $slide): ?>
      <li class="dialogue-slide" data-character="<?= esc($slide['character_code'] ?? 'jaka') ?>">
        <p><?= esc(tr($slide, 'text', $locale)) ?></p>
      </li>
    <?php endforeach ?>
  </ol>

  <a class="btn btn-primary btn-lg" href="<?= base_url('wilayah/' . $level->code) ?>"><?= esc(lang('Game.continue')) ?></a>
</section>
<?= $this->endSection() ?>
