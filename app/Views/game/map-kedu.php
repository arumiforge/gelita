<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?>Peta Kedu · GELITA<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="map map-kedu" data-screen="map-kedu" data-unlock-mode="<?= esc($unlockMode) ?>">
  <h1>Peta Kedu</h1>
  <?= $this->include('partials/flash') ?>

  <p class="hud-shards"><?= esc(lang('Game.shards')) ?>:
    <b><?= esc($progress['shards']) ?></b> / <?= esc($progress['shards_total']) ?></p>

  <ul class="level-list">
    <?php foreach ($levels as $level): ?>
      <li class="level-card is-<?= esc($level['status']) ?>">
        <h2><?= esc($level['name']) ?></h2>
        <p><?= esc($level['completed_nodes']) ?> / <?= esc($level['total_nodes']) ?> tantangan ·
           <?= esc(lang('Game.score')) ?> <?= esc($level['score']) ?></p>
        <?= stars_html((int) $level['stars']) ?>

        <?php if ($level['status'] === 'locked'): ?>
          <span class="badge"><?= esc(lang('Game.locked')) ?></span>
        <?php else: ?>
          <a class="btn btn-primary" href="<?= base_url('dialog/' . $level['code']) ?>"><?= esc(lang('Game.start')) ?></a>
          <a class="btn btn-ghost" href="<?= base_url('wilayah/' . $level['code']) ?>">Peta wilayah</a>
        <?php endif ?>
      </li>
    <?php endforeach ?>
  </ul>

  <nav class="map-nav">
    <a class="btn btn-quiet" href="<?= base_url('profil') ?>">Profil</a>
    <a class="btn btn-quiet" href="<?= base_url('keluar') ?>"><?= esc(lang('Game.logout')) ?></a>
  </nav>
</section>
<?= $this->endSection() ?>
