<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc(lang('Game.library')) ?> · GELITA<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="library" data-screen="library" data-level="<?= esc($level->code) ?>">
  <h1><?= esc(lang('Game.library')) ?> · <?= esc($level->text('name', $locale)) ?></h1>

  <ol class="library-pages">
    <?php foreach ($pages as $page): ?>
      <li class="library-page" data-page="<?= esc($page->id) ?>">
        <h2><?= esc($page->text('title', $locale)) ?></h2>
        <div class="library-body"><?= esc($page->text('body', $locale)) ?></div>
      </li>
    <?php endforeach ?>
    <?php if ($pages === []): ?>
      <li>Belum ada halaman pustaka untuk wilayah ini.</li>
    <?php endif ?>
  </ol>

  <a class="btn btn-quiet" href="<?= base_url('wilayah/' . $level->code) ?>"><?= esc(lang('Game.back')) ?></a>
</section>
<?= $this->endSection() ?>
