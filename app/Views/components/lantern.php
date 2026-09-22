<?php
/**
 * Lentera — meter serpihan cahaya pada HUD.
 *
 * `shards_total` selalu datang dari COUNT(challenge_nodes aktif) lewat
 * GameProgress::progressSummary(), tidak pernah ditulis sebagai angka tetap.
 * Nyala lentera punya 4 tahap sesuai jumlah wilayah tuntas (0–3).
 *
 * Rincian per wilayah memakai <details>, jadi tetap dapat dibuka tanpa
 * JavaScript; hud.js (tahap 6) hanya menambah animasi dan pembaruan angka.
 *
 * @var array<string, mixed>                  $progress
 * @var list<array<string, mixed>>|null       $lantern  wilayah → daftar status node
 */
$shards = (int) ($progress['shards'] ?? 0);
$total  = (int) ($progress['shards_total'] ?? 0);
$stage  = max(0, min(3, (int) ($progress['completed_levels'] ?? 0)));
$fill   = $total > 0 ? (int) round($shards / $total * 100) : 0;
$status = ['completed' => 'Game.shardDone', 'open' => 'Game.shardOpen', 'locked' => 'Game.shardLocked'];
?>
<details class="lantern" data-shards="<?= $shards ?>" data-total="<?= $total ?>" data-stage="<?= $stage ?>">
  <summary class="lantern-toggle" aria-controls="lantern-detail"
           title="<?= esc(lang('Game.lanternToggle'), 'attr') ?>">
    <span class="flame flame-<?= $stage ?>" aria-hidden="true"><?= icon('lantern') ?></span>
    <span class="lantern-text">
      <span class="eyebrow"><?= esc(lang('Game.shards')) ?></span>
      <span class="lantern-count num"><b><?= $shards ?></b>/<?= $total ?></span>
    </span>
    <span class="lantern-bar" aria-hidden="true"><i style="width: <?= $fill ?>%"></i></span>
  </summary>

  <div class="lantern-detail" id="lantern-detail">
    <p class="lantern-detail-title"><?= esc(lang('Game.lanternDetail')) ?></p>
    <?php foreach ($lantern ?? [] as $region): ?>
      <div class="lantern-region">
        <span class="lantern-region-name"><?= esc($region['name']) ?></span>
        <ol class="shard-dots">
          <?php foreach ($region['nodes'] as $node): ?>
            <li class="shard is-<?= esc($node['status'], 'attr') ?>"
                title="<?= esc($node['sequence'] . '. ' . $node['title'] . ' — ' . lang($status[$node['status']] ?? 'Game.shardLocked'), 'attr') ?>">
              <span class="visually-hidden"><?= esc($node['sequence'] . '. ' . $node['title'] . ': ' . lang($status[$node['status']] ?? 'Game.shardLocked')) ?></span>
            </li>
          <?php endforeach ?>
        </ol>
      </div>
    <?php endforeach ?>
    <p class="lantern-legend" aria-hidden="true">
      <span class="shard is-completed"></span> <?= esc(lang('Game.shardDone')) ?>
      <span class="shard is-open"></span> <?= esc(lang('Game.shardOpen')) ?>
      <span class="shard is-locked"></span> <?= esc(lang('Game.shardLocked')) ?>
    </p>
  </div>
</details>
