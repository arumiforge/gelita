<?php
/**
 * Kepala panel pendaftaran: judul, stepper, ringkasan galat.
 *
 * @var int                   $step
 * @var list<int>             $done
 * @var array<string, string> $errors
 */
?>
<header class="register-head">
  <span class="eyebrow"><?= esc(lang('Game.appName')) ?> · <?= esc(lang('Game.regStepOf', [$step, 3])) ?></span>
  <h1><?= esc(lang('Game.regStep' . $step)) ?></h1>
  <?= view('game/register/_stepper', ['step' => $step, 'done' => $done]) ?>
</header>
<?= view('partials/form-errors', ['errors' => $errors]) ?>
