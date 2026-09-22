<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?>GELITA<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="welcome">
  <h1 class="welcome-title"><?= esc(lang('Game.appName')) ?></h1>
  <p class="welcome-tagline"><?= esc(lang('Game.tagline')) ?></p>

  <?php if ($levels !== []): ?>
    <p class="welcome-levels">
      <?= esc(implode(' · ', array_map(static fn ($level): string => $level->text('name', $locale), $levels))) ?>
    </p>
  <?php endif ?>

  <div class="welcome-actions">
    <?php if ($isLoggedIn): ?>
      <a class="btn btn-primary btn-lg" href="<?= base_url('peta') ?>"><?= esc(lang('Game.continueJourney')) ?></a>
    <?php else: ?>
      <a class="btn btn-primary btn-lg" href="<?= base_url('mulai') ?>"><?= esc(lang('Game.start')) ?></a>
      <a class="btn btn-ghost btn-lg" href="<?= base_url('masuk') ?>"><?= esc(lang('Game.login')) ?></a>
    <?php endif ?>
  </div>
</section>
<?= $this->endSection() ?>
