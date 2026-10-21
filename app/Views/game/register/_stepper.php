<?php
/**
 * Stepper pendaftaran: ① Tentang kamu — ② Sekolah — ③ Akun. Langkah aktif
 * bersorot; langkah yang sudah lengkap berupa tautan untuk kembali (GET,
 * tetap berjalan tanpa JavaScript).
 *
 * Owner, 21 October 2026: every number stands on one of the owner's round
 * buttons (`--round-*`, round_style_attr()): the green ring once the step is
 * complete, the neutral face while it is not; the number stays (no ✓).
 *
 * @var int       $step langkah aktif
 * @var list<int> $done langkah yang isiannya sudah tersimpan
 */
$labels = [1 => lang('Game.regStep1Short'), 2 => lang('Game.regStep2Short'), 3 => lang('Game.regStep3Short')];
?>
<nav class="reg-stepper"<?= round_style_attr(['neutral', 'ring-green']) ?> aria-label="<?= esc(lang('Game.regStepsLabel'), 'attr') ?>">
  <ol>
    <?php foreach ($labels as $n => $label): ?>
      <?php
      $isDone   = in_array($n, $done, true) && $n !== $step;
      $isActive = $n === $step;
      $class    = 'reg-stepper-item' . ($isActive ? ' is-active' : '') . ($isDone ? ' is-done' : '');
      ?>
      <li class="<?= $class ?>"<?= $isActive ? ' aria-current="step"' : '' ?>>
        <?php if ($isDone): ?>
          <a href="<?= base_url('daftar/' . $n) ?>"><span class="reg-stepper-no" aria-hidden="true"><?= $n ?></span> <span><?= esc($label) ?></span><span class="visually-hidden"> (<?= esc(lang('Game.regStepDone')) ?>)</span></a>
        <?php else: ?>
          <span class="reg-stepper-no" aria-hidden="true"><?= $n ?></span> <span><?= esc($label) ?></span>
        <?php endif ?>
      </li>
    <?php endforeach ?>
  </ol>
</nav>
