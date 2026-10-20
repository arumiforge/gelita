<?php
/**
 * 9. Peta Wilayah — `/wilayah/{code}` → MapController::level
 *
 * Empat pos pada `challenge_nodes.map_x` / `map_y`. Pos selesai → hasil,
 * pos terbuka → kartu misi, pos terkunci → server menolak dengan toast.
 *
 * @var App\Entities\Level         $level
 * @var array<string, mixed>       $levelSummary
 * @var list<array<string, mixed>> $nodes
 * @var bool                       $hasLibrary
 * @var array<string, mixed>       $library    GameProgress::libraryAccess() wilayah ini
 * @var string                     $locale
 */
$region = $level->text('name', $locale);
$mapSrc = media_exists($level->map_media_id) ? media_src($level->map_media_id) : null;
$bg     = media_exists($level->background_media_id) ? media_src($level->background_media_id) : '';
$icons  = ['completed' => 'check', 'open' => 'right', 'locked' => 'lock'];
$href   = static fn (array $n): string => base_url(($n['status'] === 'completed' ? 'hasil/' : 'misi/') . $level->code . '/' . $n['sequence']);
$done   = (int) $levelSummary['completed_nodes'];
$total  = (int) $levelSummary['total_nodes'];
// Next screen: mission brief of the first open node (its background, Jaka)
$nextNode = null;
foreach ($nodes as $row) {
    if ($row['status'] === 'open') {
        foreach (service('contentRepository')->nodesForLevel($level->id) as $entity) {
            if ((int) $entity->id === (int) $row['id']) {
                $nextNode = $entity;
                break;
            }
        }
        break;
    }
}
$nextAssets = $nextNode === null ? [] : [
    media_exists($nextNode->background_media_id) ? media_src($nextNode->background_media_id) : null,
    character_frame_src('jaka'),
];
$points = implode(' ', array_map(static fn (array $n): string => (float) $n['map_x'] . ',' . (float) $n['map_y'], $nodes));
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc($region) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= $bg ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="map-screen map-level" data-screen="map-level"<?= next_assets_attr($nextAssets) ?>
         data-level="<?= esc($level->code, 'attr') ?>" data-level-id="<?= esc($level->id, 'attr') ?>">
  <div class="map-frame stage-box">
    <div class="map-canvas<?= $mapSrc === null ? ' is-drawn' : '' ?>" style="--map-ratio: 1400 / 900">
      <?php if ($mapSrc !== null): ?>
        <img class="map-img" src="<?= esc($mapSrc, 'attr') ?>" alt="<?= esc(lang('Game.mapImageAlt', [$region]), 'attr') ?>">
      <?php endif ?>
      <svg class="map-route" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
        <polyline points="<?= esc($points, 'attr') ?>"/>
      </svg>
      <ol class="map-points">
        <?php foreach ($nodes as $node): ?>
          <li class="map-point node-point is-<?= esc($node['status'], 'attr') ?>"
              style="left: <?= (float) $node['map_x'] ?>%; top: <?= (float) $node['map_y'] ?>%">
            <a class="map-pin" href="<?= esc($href($node), 'attr') ?>"
               <?= $node['status'] === 'locked' ? 'aria-disabled="true" data-locked-message="' . esc(lang('Game.nodeLocked'), 'attr') . '"' : '' ?>>
              <?php // The owner's status medallion when installed (20 October 2026); a locked pin keeps its lock ?>
              <?php $badge = status_badge_src($node); ?>
              <span class="pin-icon<?= $badge !== null ? ' has-badge' : '' ?><?= ! empty($node['started']) ? ' is-started' : '' ?>" aria-hidden="true">
                <?php if ($badge !== null): ?>
                  <img class="pin-badge" src="<?= esc($badge, 'attr') ?>" alt="" decoding="async" draggable="false">
                <?php endif ?>
                <?php // The gold "started" medallion has its own arc in the middle: no number on it ?>
                <?php if ($node['status'] === 'open' && ($badge === null || empty($node['started']))): ?>
                  <b class="num"><?= esc($node['sequence']) ?></b>
                <?php elseif ($badge === null): ?>
                  <?= icon($icons[$node['status']]) ?>
                <?php endif ?>
              </span>
              <span class="pin-label">
                <b><?= esc($node['sequence']) ?>. <?= esc(engine_label($node['engine_type'], $node['variant'])) ?></b>
                <?php if ($node['status'] === 'completed'): ?>
                  <?= stars_html((int) $node['stars']) ?>
                <?php else: ?>
                  <small><?= esc(lang($node['status'] === 'locked' ? 'Game.shardLocked' : (! empty($node['started']) ? 'Game.shardStarted' : 'Game.shardOpen'))) ?></small>
                <?php endif ?>
              </span>
            </a>
          </li>
        <?php endforeach ?>
      </ol>
    </div>
  </div>

  <aside class="map-side stage-box stage-scroll" aria-label="<?= esc($region, 'attr') ?>">
    <header class="map-head">
      <span class="eyebrow"><?= esc(lang('Game.levelOrder', [$level->sequence])) ?> · <?= esc(lang_or('Game.difficulty_' . $level->difficulty, (string) $level->difficulty)) ?></span>
      <h1><?= esc($region) ?></h1>
      <?php if ($level->text('focus', $locale) !== ''): ?>
        <p><?= esc($level->text('focus', $locale)) ?></p>
      <?php endif ?>
    </header>

    <div class="map-progress">
      <div class="progress" aria-hidden="true"><i style="width: <?= $total > 0 ? (int) round($done / $total * 100) : 0 ?>%"></i></div>
      <p><?= esc(lang('Game.nodesDoneOf', [$done, $total])) ?></p>
    </div>

    <ol class="node-list">
      <?php foreach ($nodes as $node): ?>
        <li class="node-card panel is-<?= esc($node['status'], 'attr') ?>">
          <span class="node-no num" aria-hidden="true">
            <?= $node['status'] === 'completed' ? icon('check') : ($node['status'] === 'locked' ? icon('lock') : esc($node['sequence'])) ?>
          </span>
          <div class="node-card-body">
            <span class="eyebrow"><?= esc(lang('Game.challengeOf', [$node['sequence'], $total])) ?> · <?= esc(engine_label($node['engine_type'], $node['variant'])) ?></span>
            <h2><?= esc($node['title']) ?></h2>
            <?php if ($node['status'] === 'completed'): ?>
              <?= stars_html((int) $node['stars']) ?>
            <?php endif ?>
          </div>
          <?php if ($node['status'] === 'completed'): ?>
            <a class="btn btn-ghost btn-sm" href="<?= esc($href($node), 'attr') ?>"><?= esc(lang('Game.viewResult')) ?></a>
          <?php elseif ($node['status'] === 'open'): ?>
            <a class="btn btn-primary" href="<?= esc($href($node), 'attr') ?>"><?= esc(lang('Game.start')) ?> <?= icon('right') ?></a>
          <?php else: ?>
            <span class="badge is-locked"><?= icon('lock') ?> <?= esc(lang('Game.shardLocked')) ?></span>
          <?php endif ?>
        </li>
      <?php endforeach ?>
    </ol>
  </aside>
</section>
<?= $this->endSection() ?>

<?= $this->section('nav') ?>
<?php
$nav = [['label' => lang('Game.mapKedu'), 'href' => base_url('peta'), 'style' => 'quiet', 'arrow' => 'left']];
if ($hasLibrary) {
    // "Pustaka {wilayah}" terkunci sampai keempat tantangan selesai: map.js
    // menampilkan penjelasannya di modal; tanpa JavaScript tautannya membuka
    // halaman terkunci /pustaka/{code} yang berisi penjelasan yang sama.
    $book = ['label' => lang('Game.libraryRegion', [$region]), 'href' => base_url('pustaka/' . $level->code), 'style' => 'ghost', 'icon' => 'book'];

    if ($library['status'] !== 'open') {
        $book['icon']  = 'lock';
        $book['attrs'] = [
            'aria-disabled'        => 'true',
            'data-library-locked'  => (string) $level->code,
            'data-locked-title'    => lang('Game.libraryLockedTitle', [$region]),
            'data-locked-message'  => lang('Game.libraryLockedText', [$region, $total]),
            'data-locked-progress' => lang('Game.nodesProgress', [$done, $total]),
            'data-locked-ok'       => lang('Game.libraryUnderstood'),
            'data-index-label'     => lang('Game.library'),
            'data-index-href'      => base_url('pustaka'),
        ];
    }

    $nav[] = $book;
}
?>
<?= component('nav-bar', ['nav' => $nav]) ?>
<?= $this->endSection() ?>
