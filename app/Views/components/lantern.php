<?php
/**
 * Lentera — meter serpihan cahaya pada HUD (the shard pill in the middle of
 * components/hud-menu).
 *
 * `shards_total` selalu datang dari COUNT(challenge_nodes aktif) lewat
 * GameProgress::progressSummary(), tidak pernah ditulis sebagai angka tetap.
 * Nyala lentera punya 4 tahap sesuai jumlah wilayah tuntas (0–3).
 *
 * Rincian per wilayah memakai <details>, jadi tetap dapat dibuka tanpa
 * JavaScript; hud.js (tahap 6) hanya menambah animasi dan pembaruan angka.
 *
 * @var array<string, mixed>                  $progress
 * @var list<array<string, mixed>>|null       $lantern  wilayah → daftar status node (+ `started`)
 */
$shards = (int) ($progress['shards'] ?? 0);
$total  = (int) ($progress['shards_total'] ?? 0);
$stage  = max(0, min(3, (int) ($progress['completed_levels'] ?? 0)));
$fill   = $total > 0 ? (int) round($shards / $total * 100) : 0;
$status = ['completed' => 'Game.shardDone', 'open' => 'Game.shardOpen', 'locked' => 'Game.shardLocked'];
// Status word of one node: an open node with an unfinished attempt reads "Sedang dikerjakan"
$label = static fn (array $node): string => lang(($node['status'] ?? '') === 'open' && ! empty($node['started']) ? 'Game.shardStarted' : ($status[$node['status'] ?? ''] ?? 'Game.shardLocked'));
// One shard: the owner's status medallion when installed (status_badge_src()), else the CSS dot
$shard = static function (array $node, string $inner = ''): string {
    $badge = status_badge_src($node);
    $class = 'shard is-' . ($node['status'] ?? 'locked') . (! empty($node['started']) ? ' is-started' : '') . ($badge !== null ? ' has-badge' : '');

    return '<span class="' . esc($class) . '" aria-hidden="true">'
        . ($badge !== null ? '<img src="' . esc($badge, 'attr') . '" alt="" decoding="async" draggable="false">' : '')
        . '</span>' . $inner;
};
?>
<details class="lantern" data-shards="<?= $shards ?>" data-total="<?= $total ?>" data-stage="<?= $stage ?>">
  <summary class="lantern-toggle" aria-controls="lantern-detail"
           title="<?= esc(lang('Game.lanternToggle'), 'attr') ?>">
    <?php $art = region_art_src('lantern'); ?>
    <span class="flame flame-<?= $stage ?><?= $art !== null ? ' has-art' : '' ?>" aria-hidden="true"><?= art_or_icon($art, 'lantern') ?></span>
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
      <?= $shard(['status' => 'open', 'started' => true]) ?> <?= esc(lang('Game.shardStarted')) ?>
      <?= $shard(['status' => 'open']) ?> <?= esc(lang('Game.shardOpen')) ?>
      <?= $shard(['status' => 'locked']) ?> <?= esc(lang('Game.shardLocked')) ?>
    </p>
  </div>
</details>
