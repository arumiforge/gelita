<?php
/**
 * Kepala panel pendaftaran (0–64 dp di dalam panel, Dok 3 §4): judul dan
 * stepper. Ringkasan galat ada di awal isian bergulir (step-*.php).
 * Since 21 October 2026 (owner) "Langkah n dari 3" stands centred under the
 * head, right above the form, without "GELITA ·".
 *
 * @var int                   $step
 * @var list<int>             $done
 */
?>
<header class="register-head">
  <h1><?= esc(lang('Game.regStep' . $step)) ?></h1>
  <?= view('game/register/_stepper', ['step' => $step, 'done' => $done]) ?>
</header>
<p class="eyebrow register-step"><?= esc(lang('Game.regStepOf', [$step, 3])) ?></p>
