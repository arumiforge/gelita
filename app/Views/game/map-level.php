<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc($level->text('name', $locale)) ?> · GELITA<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="map map-level" data-screen="map-level" data-level="<?= esc($level->code) ?>">
  <h1><?= esc($level->text('name', $locale)) ?></h1>
  <?= $this->include('partials/flash') ?>

  <p><?= esc(lang('Game.score')) ?>: <b><?= esc($levelScore['score']) ?></b> ·
     <?= esc($levelScore['completed_nodes']) ?> / <?= esc($levelScore['total_nodes']) ?> selesai</p>

  <ol class="node-list">
    <?php foreach ($nodes as $node): ?>
      <li class="node-card is-<?= esc($node['status']) ?>">
        <h2><?= esc($node['sequence']) ?>. <?= esc($node['title']) ?></h2>
        <?php if ($node['status'] === 'locked'): ?>
          <span class="badge"><?= esc(lang('Game.locked')) ?></span>
        <?php else: ?>
          <a class="btn btn-primary" href="<?= base_url('misi/' . $level->code . '/' . $node['sequence']) ?>">
            <?= $node['status'] === 'completed' ? esc(lang('Game.continue')) : esc(lang('Game.start')) ?>
          </a>
          <?php if ($node['status'] === 'completed'): ?>
            <?= stars_html((int) $node['stars']) ?>
            <a class="btn btn-quiet" href="<?= base_url('hasil/' . $level->code . '/' . $node['sequence']) ?>">Hasil</a>
          <?php endif ?>
        <?php endif ?>
      </li>
    <?php endforeach ?>
  </ol>

  <nav class="map-nav">
    <?php if ($hasLibrary): ?>
      <a class="btn btn-ghost" href="<?= base_url('pustaka/' . $level->code) ?>"><?= esc(lang('Game.library')) ?></a>
    <?php endif ?>
    <a class="btn btn-quiet" href="<?= base_url('peta') ?>"><?= esc(lang('Game.back')) ?></a>
  </nav>
</section>
<?= $this->endSection() ?>
