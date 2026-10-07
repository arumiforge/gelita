<?php
/**
 * Kepala panel pendaftaran (0–64 dp di dalam panel, Dok 3 §4): judul dan
 * stepper. Ringkasan galat ada di awal isian bergulir (step-*.php).
 *
 * @var int                   $step
 * @var list<int>             $done
 */
?>
<header class="register-head">
  <span class="eyebrow"><?= esc(lang('Game.appName')) ?> · <?= esc(lang('Game.regStepOf', [$step, 3])) ?></span>
  <h1><?= esc(lang('Game.regStep' . $step)) ?></h1>
  <?= view('game/register/_stepper', ['step' => $step, 'done' => $done]) ?>
</header>
