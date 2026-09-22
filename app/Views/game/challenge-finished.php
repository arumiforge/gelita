<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?>Selesai · GELITA<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="challenge-finished" data-screen="finished">
  <h1><?= esc(lang('Game.completed')) ?></h1>

  <?= stars_html((int) $attempt->stars) ?>

  <dl class="result-stats">
    <dt><?= esc(lang('Game.score')) ?></dt><dd><?= esc($attempt->score) ?></dd>
    <dt><?= esc(lang('Game.accuracyFirst')) ?></dt><dd><?= esc($attempt->first_pass_accuracy) ?>%</dd>
    <dt><?= esc(lang('Game.timeSpent')) ?></dt><dd><?= esc(ms_to_human((int) $attempt->duration_ms)) ?></dd>
    <dt><?= esc(lang('Game.shards')) ?></dt><dd><?= esc($progress['shards']) ?> / <?= esc($progress['shards_total']) ?></dd>
  </dl>

  <?php if ($levelCompleted && $level !== null): ?>
    <p class="mbah-kedu">Mbah Kedu tersenyum: wilayah <?= esc($level->text('name', $locale)) ?> sudah terang kembali.</p>
  <?php endif ?>

  <?php if ($allCompleted): ?>
    <a class="btn btn-primary btn-lg" href="<?= base_url('refleksi') ?>"><?= esc(lang('Game.reflection')) ?></a>
  <?php endif ?>

  <?php if ($level !== null): ?>
    <a class="btn btn-ghost" href="<?= base_url('wilayah/' . $level->code) ?>">Peta wilayah</a>
  <?php endif ?>
  <a class="btn btn-quiet" href="<?= base_url('peta') ?>">Peta Kedu</a>
</section>
<?= $this->endSection() ?>
