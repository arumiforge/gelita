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
 * Owner request (21 October 2026): no number on the open medallion (pins and
 * cards) and no lock icon on Terkunci; the region name on the owner's ornate
 * navy plate (`assets/ui/map-head-plate.webp`) and the progress on the
 * bronze track (`assets/ui/ui-track.webp`, U-05, since 19 November 2026); the map picture
 * has no frame and its edges fade into the page background; the card pictures
 * are bright sepia when done, darker when open, grey when locked. Later the
 * same day: no region name in the HUD (section `hudRegion` = `off`), a locked
 * pin explains itself in a modal instead of a toast, and a soft parchment veil
 * behind the title of a done card.
 *
 * Owner request (19 November 2026): every pin sits on its stop's clearing in front of the
 * drawn prop (map_x / map_y are the pin's centre since then, CatalogImporter::MAP_STOPS) and
 * is one row, the status medallion left of the name and the status or stars. A pin no longer
 * leaves the page: like Peta Kedu it selects its challenge ([data-node-select], game/map.js),
 * and the side column shows that one challenge (eyebrow, picture card, name, status, stars,
 * button) instead of a scrolling list of five. Without JavaScript the pin's #node-{n} link
 * shows the card through :target. The challenge to play is selected when the page opens.
 *
 * The owner's mockup of the same day (second round): the map in a thin card with the
 * progress under it (the count and one segment per challenge, `.map-foot`), pins as the
 * challenge's number in a ring with its name and status beside it (the selected one in cream),
 * and the right side one calm card: the region name and its literacy focus, a hairline,
 * "Tantangan n dari 5" with a status chip, the challenge's picture in full colour in a thin
 * frame, its name, stars when done, a wide quiet Mulai (or Lihat hasil), and the Pustaka row at
 * the foot. The head plate, the bronze track, the scarf frame and the status medallions left
 * this screen. The paragraphs above describe the earlier layouts.
 *
 * @var App\Entities\Level         $level
 * @var array<string, mixed>       $levelSummary
 * @var list<array<string, mixed>> $nodes
 * @var bool                       $hasLibrary
 * @var array<string, mixed>       $library    GameProgress::libraryAccess() wilayah ini
 * @var bool                       $bridgeSeen part 6: the bridge story may be watched again
 * @var string                     $locale
 */
$region = $level->text('name', $locale);
$mapSrc = media_exists($level->map_media_id) ? media_src($level->map_media_id) : null;
$bg     = media_exists($level->background_media_id) ? media_src($level->background_media_id) : '';
$href   = static fn (array $n): string => base_url((node_done($n) ? 'hasil/' : 'misi/') . $level->code . '/' . $n['sequence']);
// The challenge selected when the page opens: the one to play, else the first
$selectedSeq = (int) ($nodes[0]['sequence'] ?? 1);
foreach ($nodes as $row) {
    if ($row['status'] === 'open') {
        $selectedSeq = (int) $row['sequence'];
        break;
    }
}
$statusText = static fn (array $n): string => lang(node_done($n) ? 'Game.shardDone' : ($n['status'] === 'locked' ? 'Game.shardLocked' : 'Game.shardOpen'));
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
$nodeName = static fn (array $n): string => lang_or('Game.engine_' . $n['engine_type'], $n['title']);
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc($region) ?><?= $this->endSection() ?>
<?php // A taller HUD with larger controls; the map and the side card sit lower (owner, 20 October 2026) ?>
<?= $this->section('hudStyle') ?>tall<?= $this->endSection() ?>
<?php // The region name heads the side card, not again in the HUD (owner, 21 October 2026) ?>
<?= $this->section('hudRegion') ?>off<?= $this->endSection() ?>
<?= $this->section('background') ?><?= $bg ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="map-screen map-level" data-screen="map-level"<?= next_assets_attr($nextAssets) ?>
         data-level="<?= esc($level->code, 'attr') ?>" data-level-id="<?= esc($level->id, 'attr') ?>">
  <div class="map-frame stage-box">
    <div class="map-canvas<?= $mapSrc === null ? ' is-drawn' : '' ?>" style="--map-ratio: 1400 / 900">
      <?php if ($mapSrc !== null): ?>
        <img class="map-img" src="<?= esc($mapSrc, 'attr') ?>" alt="<?= esc(lang('Game.mapImageAlt', [$region]), 'attr') ?>">
      <?php endif ?>
      <ol class="map-points">
        <?php foreach ($nodes as $node): ?>
          <?php $isSelected = (int) $node['sequence'] === $selectedSeq; ?>
          <li class="map-point node-point <?= esc(node_status_class($node), 'attr') ?>"
              style="left: <?= (float) $node['map_x'] ?>%; top: <?= (float) $node['map_y'] ?>%">
            <?php // Selects the challenge (game/map.js); without JavaScript the card shows through :target ?>
            <a class="map-pin" href="#node-<?= esc($node['sequence'], 'attr') ?>" data-node-select="<?= esc($node['sequence'], 'attr') ?>"
               aria-controls="node-<?= esc($node['sequence'], 'attr') ?>"<?= $isSelected ? ' aria-current="true"' : '' ?>>
              <span class="pin-no" aria-hidden="true"><?= esc($node['sequence']) ?></span>
              <span class="pin-label">
                <?php // Part 7 (33 §8.4, M-07): the kind's name, as on the card and the challenge header ?>
                <b><span class="visually-hidden"><?= esc($node['sequence']) ?>. </span><?= esc($nodeName($node)) ?></b>
                <small class="pin-status">
                  <?php if ($node['status'] === 'locked'): ?><?= icon('lock') ?><?php endif ?>
                  <span><?= esc($statusText($node)) ?></span>
                  <?php if (node_done($node)): ?><?= stars_html((int) $node['stars']) ?><?php endif ?>
                </small>
              </span>
            </a>
          </li>
        <?php endforeach ?>
      </ol>
    </div>
    <?php // The region's progress under the map: the count and one segment per challenge (owner's mockup, 19 November 2026) ?>
    <div class="map-foot">
      <p><?= esc(lang('Game.nodesDoneOf', [$done, $total])) ?></p>
      <ol class="map-steps" aria-hidden="true">
        <?php foreach ($nodes as $node): ?>
          <li class="<?= node_done($node) ? 'is-done' : ($node['status'] === 'open' ? 'is-open' : 'is-locked') ?>"></li>
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

    <?php // One challenge at a time, the one selected on the map (owner, 19 November 2026); no scrolling ?>
    <ol class="node-list node-panels" aria-live="polite">
      <?php foreach ($nodes as $node): ?>
        <?php
        $art = is_file(FCPATH . 'assets/ui/node-card-' . $node['engine_type'] . '.webp') ? asset_url_versioned('ui/node-card-' . $node['engine_type'] . '.webp') : null;
        $seq = (int) $node['sequence'];
        ?>
        <li class="node-card <?= esc(node_status_class($node), 'attr') ?><?= $seq === $selectedSeq ? ' is-selected' : '' ?>"
            id="node-<?= $seq ?>" aria-labelledby="node-<?= $seq ?>-title">
          <div class="node-card-row">
            <span class="node-card-eyebrow"><?= esc(lang('Game.challengeOf', [$seq, $total])) ?></span>
            <span class="node-chip is-<?= esc(node_done($node) ? 'done' : $node['status'], 'attr') ?>"><?= icon(node_done($node) ? 'check' : ($node['status'] === 'locked' ? 'lock' : 'sparkle')) ?> <?= esc($statusText($node)) ?></span>
          </div>
          <div class="node-card-figure">
            <?php if ($art !== null): ?>
              <img class="node-card-art" src="<?= esc($art, 'attr') ?>" alt="" decoding="async" draggable="false">
            <?php endif ?>
          </div>
          <h2 class="node-card-title" id="node-<?= $seq ?>-title"><?= esc($nodeName($node)) ?></h2>
          <?php if (node_done($node)): ?>
            <p class="node-card-stars" role="img" aria-label="<?= esc(lang('Game.resultStarsOf', [(int) $node['stars'], 3]), 'attr') ?>"><?= stars_html((int) $node['stars']) ?></p>
          <?php endif ?>
          <?php if ($node['status'] === 'locked'): ?>
            <p class="node-card-note"><?= esc(lang('Game.nodeLocked')) ?></p>
          <?php elseif ($node['status'] === 'done_locked'): ?>
            <?php // Part 7 (33 §7.4): research mode, the region is not finished yet ?>
            <p class="node-card-note node-retake-later"><?= esc(lang('Game.retakeLater')) ?></p>
          <?php endif ?>
          <div class="node-card-actions">
            <?php if (node_done($node)): ?>
              <a class="btn btn-lg node-action is-green" href="<?= esc($href($node), 'attr') ?>"><span><?= esc(lang('Game.viewResult')) ?></span> <?= icon('right') ?></a>
            <?php elseif ($node['status'] === 'open'): ?>
              <a class="btn btn-primary btn-lg node-action" href="<?= esc($href($node), 'attr') ?>"><span><?= esc(lang('Game.start')) ?></span> <?= icon('right') ?></a>
            <?php endif ?>
            <?php // A locked challenge has no button: its status and note say why ?>
            <?php if ($node['engine_type'] === 'skenario' && ! empty($bridgeSeen) && $node['status'] !== 'locked'): ?>
              <?php // Part 6 (32 §7.2): watch the bridge story again; no data depends on it ?>
              <a class="node-bridge" href="<?= esc(base_url('penghubung/' . $level->code), 'attr') ?>"><?= icon('play') ?> <?= esc(lang('Game.watchBridgeStory')) ?></a>
            <?php endif ?>
          </div>
        </li>
      <?php endforeach ?>
    </ol>

    <?php if ($hasLibrary): ?>
      <?php
      // "Pustaka {wilayah}" terkunci sampai kelima tantangan selesai: map.js
      // menampilkan penjelasannya di modal; tanpa JavaScript tautannya membuka
      // halaman terkunci /pustaka/{code} yang berisi penjelasan yang sama.
      $libraryOpen = $library['status'] === 'open';
      ?>
      <div class="map-side-foot">
        <a class="map-library<?= $libraryOpen ? ' is-open' : ' is-locked' ?>" href="<?= esc(base_url('pustaka/' . $level->code), 'attr') ?>"<?php if (! $libraryOpen): ?>
           aria-disabled="true" data-library-locked="<?= esc($level->code, 'attr') ?>"
           data-locked-title="<?= esc(lang('Game.libraryLockedTitle', [$region]), 'attr') ?>"
           data-locked-message="<?= esc(lang('Game.libraryLockedText', [$region, $total]), 'attr') ?>"
           data-locked-progress="<?= esc(lang('Game.nodesProgress', [$done, $total]), 'attr') ?>"
           data-locked-ok="<?= esc(lang('Game.libraryUnderstood'), 'attr') ?>"
           data-index-label="<?= esc(lang('Game.library'), 'attr') ?>"
           data-index-href="<?= esc(base_url('pustaka'), 'attr') ?>"<?php endif ?>>
          <?= $libraryOpen ? library_icon() : icon('lock') ?>
          <span class="map-library-name"><?= esc(lang('Game.libraryRegion', [$region])) ?></span>
          <span class="map-library-state"><?= esc(lang($libraryOpen ? 'Game.libraryRead' : 'Game.shardLocked')) ?></span>
          <?php if ($libraryOpen): ?><?= icon('right') ?><?php endif ?>
        </a>
      </div>
    <?php endif ?>
  </aside>
</section>
<?= $this->endSection() ?>
