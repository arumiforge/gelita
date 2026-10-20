<?php
/**
 * Lantern: the Serpihan Cahaya (shard) meter in the HUD, the plate in the
 * middle of components/hud-menu.
 *
 * `shards_total` always comes from COUNT(active challenge_nodes) through
 * GameProgress::progressSummary(), never from a fixed number. The light has
 * 4 stages, one per completed region (0–3): the lantern icon's glow, or on
 * the owner's shard plate (20 October 2026) the crystal's glow
 * (`.lantern-gem`, see stage.css).
 *
 * The bar is drawn by the page from the real count (`.lantern-bar i` width);
 * game/hud.js updateLantern() moves it live after a challenge.
 *
 * The per-region breakdown is a <details>, so it opens without JavaScript;
 * hud.js only adds the animation and the live numbers.
 *
 * @var array<string, mixed>                  $progress
 * @var list<array<string, mixed>>|null       $lantern  region → list of node statuses
 */
$shards = (int) ($progress['shards'] ?? 0);
$total  = (int) ($progress['shards_total'] ?? 0);
$stage  = max(0, min(3, (int) ($progress['completed_levels'] ?? 0)));
$fill   = $total > 0 ? (int) round($shards / $total * 100) : 0;
$status = ['completed' => 'Game.shardDone', 'open' => 'Game.shardOpen', 'locked' => 'Game.shardLocked'];
// Three states only: done, open, locked (owner, 20 October 2026)
$label = static fn (array $node): string => lang($status[$node['status'] ?? ''] ?? 'Game.shardLocked');
// One shard: the owner's status medallion when installed (status_badge_src()), else the CSS dot;
// a locked medallion carries a small lock
$shard = static function (array $node, string $inner = ''): string {
    $badge = status_badge_src($node);
    $class = 'shard is-' . ($node['status'] ?? 'locked') . ($badge !== null ? ' has-badge' : '');

    return '<span class="' . esc($class) . '" aria-hidden="true">'
        . ($badge !== null ? '<img src="' . esc($badge, 'attr') . '" alt="" decoding="async" draggable="false">' : '')
        . ($badge !== null && ($node['status'] ?? '') === 'locked' ? icon('lock', 'shard-lock') : '')
        . '</span>' . $inner;
};
?>
<details class="lantern" data-shards="<?= $shards ?>" data-total="<?= $total ?>" data-stage="<?= $stage ?>">
  <summary class="lantern-toggle" aria-controls="lantern-detail"
           title="<?= esc(lang('Game.lanternToggle'), 'attr') ?>">
    <?php $art = region_art_src('lantern'); ?>
    <span class="flame flame-<?= $stage ?><?= $art !== null ? ' has-art' : '' ?>" aria-hidden="true"><?= art_or_icon($art, 'lantern') ?></span>
    <?php // The crystal's glow on the shard plate; hidden without the plate ?>
    <span class="lantern-gem" aria-hidden="true"></span>
    <span class="lantern-meter">
      <span class="lantern-text">
        <span class="eyebrow"><?= esc(lang('Game.shards')) ?></span>
        <?php // The bar sits on the count's row (owner, 18 October 2026) ?>
        <span class="lantern-row">
          <span class="lantern-count num"><b><?= $shards ?></b>/<?= $total ?></span>
          <span class="lantern-bar" aria-hidden="true"><i style="width: <?= $fill ?>%"></i></span>
        </span>
      </span>
    </span>
  </summary>

  <div class="lantern-detail" id="lantern-detail">
    <p class="lantern-detail-title"><?= esc(lang('Game.lanternDetail')) ?></p>
    <?php foreach ($lantern ?? [] as $region): ?>
      <div class="lantern-region">
        <span class="lantern-region-name"><?= esc($region['name']) ?></span>
        <ol class="shard-dots">
          <?php foreach ($region['nodes'] as $node): ?>
            <li class="shard-item" title="<?= esc($node['sequence'] . '. ' . $node['title'] . ' — ' . $label($node), 'attr') ?>">
              <?= $shard($node) ?><span class="visually-hidden"><?= esc($node['sequence'] . '. ' . $node['title'] . ': ' . $label($node)) ?></span>
            </li>
          <?php endforeach ?>
        </ol>
      </div>
    <?php endforeach ?>
    <p class="lantern-legend" aria-hidden="true">
      <?= $shard(['status' => 'completed']) ?> <?= esc(lang('Game.shardDone')) ?>
      <?= $shard(['status' => 'open']) ?> <?= esc(lang('Game.shardOpen')) ?>
      <?= $shard(['status' => 'locked']) ?> <?= esc(lang('Game.shardLocked')) ?>
    </p>
  </div>
</details>
