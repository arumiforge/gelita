<?php
/**
 * 9. Peta Wilayah — `/wilayah/{code}` → MapController::level
 *
 * Empat pos pada `challenge_nodes.map_x` / `map_y`. Pos selesai → hasil,
 * pos terbuka → kartu misi, pos terkunci → server menolak dengan toast.
 *
 * Owner request (20 October 2026): pins and the challenge list wear the
 * same status medallion (status_badge_src(): green ✓ done, gold arc with
 * the number open, navy with a lock locked); each challenge card shows its
 * engine's picture (`assets/ui/node-card-{engine}.webp`); the side column
 * keeps the region name and progress at the top and Pustaka {wilayah} at
 * the bottom while only the list scrolls. No bottom nav bar: Peta Kedu is
 * in the HUD menu.
 *
 * Owner request (20 October 2026, later): a taller HUD (section hudStyle
 * `tall`) with the map and the side column lower; each card is the owner's
 * green scarf frame (`assets/ui/node-card-frame.webp`) with the engine
 * picture in sepia, like a drawing in an old manuscript, in its window;
 * Lihat hasil on the green plate, Terkunci on the navy plate, Pustaka
 * {wilayah} on the wide gold plate.
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
// Status medallion with its mark: the number on open, a lock on locked, none on done (the
// picture has its check). Without the pictures the old marks show.
$medal = static function (array $n, string $class) use ($icons): string {
    $badge = status_badge_src($n);

    if ($badge === null) {
        return $n['status'] === 'open' ? '<b class="num">' . esc($n['sequence']) . '</b>' : icon($icons[$n['status']]);
    }

    return '<img class="' . $class . '" src="' . esc($badge, 'attr') . '" alt="" decoding="async" draggable="false">'
        . match ($n['status']) {
            'open'   => '<b class="num">' . esc($n['sequence']) . '</b>',
            'locked' => icon('lock', 'badge-lock'),
            default  => '',
        };
};
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
// The owner's pictures for the side column (20 October 2026): the green scarf frame of each
// card, the green plate (Lihat hasil), the navy plate (Terkunci), the wide gold plate (Pustaka)
$plates = [];
foreach (['card-frame' => 'node-card-frame', 'plate-green' => 'plate-green', 'plate-navy' => 'kenal-plate-navy', 'plate-gold' => 'kenal-plate-gold'] as $var => $file) {
    if (is_file(FCPATH . 'assets/ui/' . $file . '.webp')) {
        $plates[] = '--' . $var . ': url(&quot;' . esc(asset_url_versioned('ui/' . $file . '.webp'), 'attr') . '&quot;)';
    }
}
$hasFrame = is_file(FCPATH . 'assets/ui/node-card-frame.webp');
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc($region) ?><?= $this->endSection() ?>
<?php // A taller HUD with larger controls; the map and the side column sit lower (owner, 20 October 2026) ?>
<?= $this->section('hudStyle') ?>tall<?= $this->endSection() ?>
<?= $this->section('background') ?><?= $bg ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="map-screen map-level" data-screen="map-level"<?= next_assets_attr($nextAssets) ?><?= $plates !== [] ? ' style="' . implode('; ', $plates) . '"' : '' ?>
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
              <span class="pin-icon<?= status_badge_src($node) !== null ? ' has-badge' : '' ?>" aria-hidden="true"><?= $medal($node, 'pin-badge') ?></span>
              <span class="pin-label">
                <b><?= esc($node['sequence']) ?>. <?= esc(engine_label($node['engine_type'], $node['variant'])) ?></b>
                <?php if ($node['status'] === 'completed'): ?>
                  <?= stars_html((int) $node['stars']) ?>
                <?php else: ?>
                  <small><?= esc(lang($node['status'] === 'locked' ? 'Game.shardLocked' : 'Game.shardOpen')) ?></small>
                <?php endif ?>
              </span>
            </a>
          </li>
        <?php endforeach ?>
      </ol>
    </div>
  </div>

  <aside class="map-side stage-box" aria-label="<?= esc($region, 'attr') ?>">
    <header class="map-head">
      <h1><?= esc($region) ?></h1>
      <?php if ($level->text('focus', $locale) !== ''): ?>
        <p><?= esc($level->text('focus', $locale)) ?></p>
      <?php endif ?>
    </header>

    <div class="map-progress">
      <div class="progress" aria-hidden="true"><i style="width: <?= $total > 0 ? (int) round($done / $total * 100) : 0 ?>%"></i></div>
      <p><?= esc(lang('Game.nodesDoneOf', [$done, $total])) ?></p>
    </div>

    <?php // Only the list scrolls; the head, the progress, and Pustaka stay in place ?>
    <ol class="node-list stage-scroll">
      <?php foreach ($nodes as $node): ?>
        <?php $art = is_file(FCPATH . 'assets/ui/node-card-' . $node['engine_type'] . '.webp') ? asset_url_versioned('ui/node-card-' . $node['engine_type'] . '.webp') : null; ?>
        <li class="node-card panel is-<?= esc($node['status'], 'attr') ?><?= $art !== null ? ' has-art' : '' ?><?= $hasFrame ? ' has-frame' : '' ?>">
          <?php if ($art !== null): ?>
            <img class="node-card-art" src="<?= esc($art, 'attr') ?>" alt="" decoding="async" draggable="false">
          <?php endif ?>
          <span class="node-no<?= status_badge_src($node) !== null ? ' has-badge' : '' ?>" aria-hidden="true"><?= $medal($node, 'node-badge') ?></span>
          <div class="node-card-body">
            <span class="visually-hidden"><?= esc(lang('Game.challengeOf', [$node['sequence'], $total])) ?>:</span>
            <span class="eyebrow"><?= esc($node['title']) ?></span>
            <?php if ($node['status'] === 'completed'): ?>
              <?= stars_html((int) $node['stars']) ?>
            <?php endif ?>
          </div>
          <?php if ($node['status'] === 'completed'): ?>
            <a class="btn btn-ghost btn-sm node-action is-green" href="<?= esc($href($node), 'attr') ?>"><?= esc(lang('Game.viewResult')) ?></a>
          <?php elseif ($node['status'] === 'open'): ?>
            <a class="btn btn-primary node-action" href="<?= esc($href($node), 'attr') ?>"><?= esc(lang('Game.start')) ?> <?= icon('right') ?></a>
          <?php else: ?>
            <span class="badge is-locked node-action is-navy"><?= icon('lock') ?> <?= esc(lang('Game.shardLocked')) ?></span>
          <?php endif ?>
        </li>
      <?php endforeach ?>
    </ol>

    <?php if ($hasLibrary): ?>
      <?php
      // "Pustaka {wilayah}" terkunci sampai keempat tantangan selesai: map.js
      // menampilkan penjelasannya di modal; tanpa JavaScript tautannya membuka
      // halaman terkunci /pustaka/{code} yang berisi penjelasan yang sama.
      $libraryOpen = $library['status'] === 'open';
      ?>
      <div class="map-side-foot">
        <a class="btn btn-ghost btn-block map-library is-gold-plate" href="<?= esc(base_url('pustaka/' . $level->code), 'attr') ?>"<?php if (! $libraryOpen): ?>
           aria-disabled="true" data-library-locked="<?= esc($level->code, 'attr') ?>"
           data-locked-title="<?= esc(lang('Game.libraryLockedTitle', [$region]), 'attr') ?>"
           data-locked-message="<?= esc(lang('Game.libraryLockedText', [$region, $total]), 'attr') ?>"
           data-locked-progress="<?= esc(lang('Game.nodesProgress', [$done, $total]), 'attr') ?>"
           data-locked-ok="<?= esc(lang('Game.libraryUnderstood'), 'attr') ?>"
           data-index-label="<?= esc(lang('Game.library'), 'attr') ?>"
           data-index-href="<?= esc(base_url('pustaka'), 'attr') ?>"<?php endif ?>><?= $libraryOpen ? library_icon() : icon('lock') ?> <span><?= esc(lang('Game.libraryRegion', [$region])) ?></span></a>
      </div>
    <?php endif ?>
  </aside>
</section>
<?= $this->endSection() ?>
