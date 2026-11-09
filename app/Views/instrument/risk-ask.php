<?php
/**
 * T11 (30 §5.4, 23 → T11): two buttons of the same size and colour.
 */
?>
<?= $this->extend('layouts/instrument') ?>
<?= $this->section('content') ?>
<div class="mx-auto" style="max-width: 44rem">
  <h1 class="h3 mb-3"><?= esc(lang('Game.riskAskTitle')) ?></h1>
  <?php foreach (['riskAskP1', 'riskAskP2', 'riskAskP3', 'riskAskP4'] as $key): ?><p><?= esc(lang('Game.' . $key)) ?></p><?php endforeach ?>
  <p class="fw-bold"><?= esc(lang('Game.riskAskQuestion')) ?></p>
  <form method="post" action="<?= esc(site_url('tes/tambahan'), 'attr') ?>" class="d-flex flex-wrap gap-2">
    <?= csrf_field() ?>
    <button type="submit" name="jawab" value="ya" class="btn btn-secondary btn-lg flex-fill"><?= esc(lang('Game.riskAskYes')) ?></button>
    <button type="submit" name="jawab" value="tidak" class="btn btn-secondary btn-lg flex-fill"><?= esc(lang('Game.riskAskNo')) ?></button>
  </form>
</div>
<?= $this->endSection() ?>
