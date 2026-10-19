<?php
/**
 * The region's difficulty badge for the HUD (owner, 19 October 2026): a
 * three-bar meter with 1–3 bars lit, "Tingkat kesulitan", and the level
 * (`levels.difficulty`: mudah, sedang, sulit). game/dialogue.php puts it in
 * the HUD section `hudBadge`, left of the language control. Screen readers
 * hear one line, `Game.difficultyLevel`.
 *
 * @var App\Entities\Level $level
 */
$difficulty = (string) $level->difficulty;
// Lit bars of the three-step meter; an unknown level shows the text only
$steps = ['mudah' => 1, 'sedang' => 2, 'sulit' => 3][$difficulty] ?? 0;
?>
<?php if ($difficulty !== ''): ?>
  <?php $levelName = lang_or('Game.difficulty_' . $difficulty, $difficulty) ?>
  <div class="region-difficulty is-<?= esc($difficulty, 'attr') ?>">
    <span class="region-difficulty-meter" aria-hidden="true">
      <?php for ($i = 1; $i <= 3; $i++): ?>
        <i<?= $i <= $steps ? ' class="is-on"' : '' ?> style="--i: <?= $i ?>"></i>
      <?php endfor ?>
    </span>
    <span class="region-difficulty-text" aria-hidden="true">
      <span class="region-difficulty-label"><?= esc(lang('Game.difficultyLabel')) ?></span>
      <span class="region-difficulty-level"><?= esc($levelName) ?></span>
    </span>
    <span class="visually-hidden"><?= esc(lang('Game.difficultyLevel', [$levelName])) ?></span>
  </div>
<?php endif ?>
