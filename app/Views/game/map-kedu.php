<?php
/**
 * 7. Peta Kedu — `/peta` → MapController::kedu
 *
 * Page redesign 2 (18 October 2026, catalogue mockup Peta Kedu candidate 04
 * with the owner's changes; decision log *Page redesign 2*):
 * - redesigned HUD (hudStyle `menu`) with the title and the shard lantern;
 *   profile, Peta Kedu, fullscreen, and logout are in its menu;
 * - map: the three-region diorama `map.kedu-diorama` cut into one slice per
 *   region (Config\Gelita::$mapDiorama; locked slices dimmed, trails gold or
 *   grey), else the old `map.kedu` picture. Each region is one large pin with
 *   its name and status; tapping it selects the region (no navigation);
 * - one region panel (the selected region; without JavaScript the panel is
 *   chosen with :target) with Kenali and Lanjut;
 * - Jaka's story as a bar under the map, as wide as the map (portrait,
 *   dots, icon controls, narration unchanged). The portrait is Jaka's bust
 *   avatar `ui.jaka-avatar`, never a pose frame (owner, 18 October 2026);
 * - the page buttons (Pustaka Kedu, and once every challenge is done Tonton
 *   penutup and Balai Refleksi) sit right of the story, under the region
 *   panel, instead of the bottom nav bar (owner, 18 October 2026).
 *
 * Owner follow-up (18 October 2026): the region panel shows the city's
 * loading picture (`bg.loading.{code}`, else its background) fading to the
 * left, "Wilayah dipilih" between gold diamonds, a status pill with its own
 * icon and colour, a wider progress bar with "n/N tantangan", and Lanjut as
 * tall as Kenali; the city captions wear the panel skin; the story controls
 * are small in the bar's top-right corner, so the bar is lower and the map
 * shows the whole diorama.
 *
 * Earlier notes (still true unless replaced above):
 *
 * Tiga titik pada posisi `levels.map_x` / `map_y` (persen). Tujuan tautan
 * datang dari `entry` (GameProgress::regionEntryPath()): wilayah yang baru
 * terbuka selalu menuju dialog pembuka, wilayah yang sedang dijelajahi atau
 * tuntas langsung ke peta wilayah. Titik terkunci tetap berupa tautan: server
 * menolaknya dengan toast "Selesaikan wilayah sebelumnya dulu" (map.js tahap 6
 * menampilkan toast yang sama tanpa berpindah halaman).
 *
 * Di bawah peta ada daftar kartu wilayah yang sama — terbaca di ponsel,
 * pembaca layar, dan saat gambar peta belum diunggah.
 *
 * Panduan peta berisi narasi Jaka (dialogues `map_intro`, game/narrator.js).
 * Datang dari gerbang atau akhir cerita pembuka (flash `curtain=map`): tirai
 * "Membuka Peta Kedu" memuat aset peta, lalu ketukannya memulai musik peta
 * dan narasi otomatis (mode `external`). Kunjungan lain: teks utuh dengan
 * tombol ▶ (mode `manual`). Tanpa slide `map_intro`: balon Game.mapLead.
 *
 * Tahap 3:
 * - Tombol lentera "Kenali {wilayah}" pada setiap pin dan kartu wilayah
 *   membuka overlay "Mengenal {wilayah}" (partials/region-intro, narasi
 *   `region_intro`); lencana "Belum didengar" berdenyut sampai narasi wilayah
 *   itu pernah didengar sampai slide terakhir ($heard, dari server).
 * - Pin dan kartu wilayah yang terbuka memutar tirai wilayah "Menuju
 *   {wilayah}…" (<template id="tpl-curtain-region">) sebelum berpindah.
 * - Semua tantangan tuntas: "Tonton penutup" di samping Balai Refleksi.
 *
 * @var list<array<string, mixed>>                 $levels         + `curtain` (GameProgress::regionCurtain())
 * @var array<string, mixed>                       $progress
 * @var string                                     $unlockMode
 * @var string                                     $locale
 * @var list<array<string, mixed>>                 $story          dialogues map_intro
 * @var bool                                       $curtain        tampilkan tirai peta
 * @var list<string>                               $curtainAssets  URL yang dimuat tirai
 * @var array<string, list<array<string, mixed>>>  $regionIntros   dialogues region_intro per kode wilayah
 * @var list<int>                                  $heard          level_id yang Kenali-nya sudah didengar
 */
$diorama = media_key_src('map.kedu-diorama');
$mapSrc  = $diorama ?? media_key_src('map.kedu');
$shapes  = $diorama !== null ? config('Gelita')->mapDiorama : [];
$starSrc = star_src();
$jakaSrc = media_key_src('ui.jaka-avatar');
$icons   = ['open' => 'lantern', 'in_progress' => 'lantern', 'completed' => 'star', 'locked' => 'lock'];
// Status pill of the region panel: its own icon and colour per status (owner, 18 October 2026)
$statusIcons = ['open' => 'sparkle', 'in_progress' => 'compass', 'completed' => 'check', 'locked' => 'lock'];
$href    = static fn (array $l): string => base_url($l['entry']);
$allDone = $progress['shards_total'] > 0 && $progress['completed_nodes'] >= $progress['shards_total'];
$intros  = $regionIntros ?? [];
$heardIds = $heard ?? [];
// Atribut tirai wilayah pada tautan masuk wilayah (pin, kartu); wilayah terkunci tanpa tirai
$curtainOf = static fn (array $l): string => $l['status'] !== 'locked' && isset($l['curtain'])
    ? region_curtain_attrs($l['curtain'])
    : '';
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc(lang('Game.mapKedu')) ?><?= $this->endSection() ?>
<?= $this->section('hudTitle') ?><?= esc(lang('Game.mapKedu')) ?><?= $this->endSection() ?>
<?= $this->section('hudStyle') ?>menu<?= $this->endSection() ?>
<?= $this->section('background') ?><?= media_key_src('bg.map') ?? '' ?><?= $this->endSection() ?>

<?= $this->section('overlay') ?>
<?php if (! empty($curtain)): ?>
<?= component('curtain', ['kind' => 'map', 'preload' => $curtainAssets ?? []]) ?>
<?php endif ?>
<template id="tpl-curtain-region"><?= component('curtain', ['kind' => 'region']) ?></template>
<?php foreach ($levels as $level): ?>
  <?php if (($intros[$level['code']] ?? []) !== []): ?>
    <?= component('partials/region-intro', ['level' => $level, 'slides' => $intros[$level['code']], 'locale' => $locale]) ?>
  <?php endif ?>
<?php endforeach ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
// Next screen: entry of the first region still to finish (same assets as its curtain)
$nextLevel = null;
foreach ($levels as $candidate) {
    if (in_array($candidate['status'], ['open', 'in_progress'], true)) {
        $nextLevel = $candidate;
        break;
    }
}
// Selected at first: the region to play next, else the first one
$selected = $nextLevel['code'] ?? ($levels[0]['code'] ?? null);
$unheard  = static fn (array $l): bool => ($intros[$l['code']] ?? []) !== [] && ! in_array((int) $l['id'], $heardIds, true);
?>
<section class="map-screen map-kedu" data-screen="map-kedu" data-unlock-mode="<?= esc($unlockMode, 'attr') ?>"<?= next_assets_attr($nextLevel['curtain']['preload'] ?? []) ?>>
  <h1 class="visually-hidden"><?= esc(lang('Game.mapKedu')) ?></h1>

  <?php // Skins of the result screen redesign, shared here (ui.result-*); empty slots keep the plain look ?>
  <div class="kedu-skins"<?= skin_style_attr([
      'skin-panel'     => 'ui.result-panel',
      'skin-primary'   => 'ui.result-button-primary',
      'skin-secondary' => 'ui.result-button-secondary',
      'skin-detail'    => 'ui.result-button-detail',
      'skin-hud'       => 'ui.result-hud-control',
  ]) ?>>
  <div class="kedu-map stage-box">
    <div class="kedu-art<?= $diorama !== null ? ' is-diorama' : '' ?><?= $mapSrc === null ? ' is-drawn' : '' ?>">
      <?php if ($diorama !== null): ?>
        <?php foreach ($levels as $level): ?>
          <?php if (isset($shapes[$level['code']])): ?>
            <img class="kedu-slice<?= $level['status'] === 'locked' ? ' is-locked' : '' ?>" src="<?= esc($diorama, 'attr') ?>" alt=""
                 style="clip-path: polygon(<?= esc($shapes[$level['code']]['clip'], 'attr') ?>)" draggable="false">
          <?php endif ?>
        <?php endforeach ?>
        <span class="visually-hidden"><?= esc(lang('Game.mapImageAlt', [lang('Game.mapKedu')])) ?></span>
        <svg class="kedu-routes" viewBox="0 0 1280 640" aria-hidden="true">
          <?php foreach ($levels as $level): ?>
            <?php if (isset($shapes[$level['code']]['route'])): ?>
              <path class="kedu-route-shadow" d="<?= esc($shapes[$level['code']]['route'], 'attr') ?>"/>
              <path class="kedu-route<?= $level['status'] === 'locked' ? ' is-locked' : '' ?>" d="<?= esc($shapes[$level['code']]['route'], 'attr') ?>"/>
            <?php endif ?>
          <?php endforeach ?>
        </svg>
      <?php elseif ($mapSrc !== null): ?>
        <img class="map-img" src="<?= esc($mapSrc, 'attr') ?>" alt="<?= esc(lang('Game.mapImageAlt', [lang('Game.mapKedu')]), 'attr') ?>">
      <?php endif ?>

      <ol class="kedu-pins">
        <?php foreach ($levels as $level): ?>
          <?php
          [$x, $y] = $shapes[$level['code']]['pin'] ?? [(float) $level['map_x'], (float) $level['map_y']];
          $curtainBg = $level['status'] !== 'locked' ? ($level['curtain']['bg'] ?? null) : null;
          $preload   = array_values(array_unique(array_filter([
              $level['status'] !== 'locked' ? ($level['background'] ?? null) : null,
              $curtainBg,
          ])));
          $pinArt = region_art_src('pin', $level['code']);
          ?>
          <li class="kedu-pin is-<?= esc($level['status'], 'attr') ?>" style="left: <?= (float) $x ?>%; top: <?= (float) $y ?>%"<?= $preload !== [] ? ' data-preload="' . esc($preload[0], 'attr') . '"' : '' ?>>
            <?php // Selects the region (game/map.js); without JavaScript the panel shows through :target ?>
            <a class="kedu-city" href="#region-<?= esc($level['code'], 'attr') ?>" data-region-select="<?= esc($level['code'], 'attr') ?>"
               aria-controls="region-<?= esc($level['code'], 'attr') ?>"<?= $level['code'] === $selected ? ' aria-current="true"' : '' ?><?= isset($preload[1]) ? ' data-preload="' . esc($preload[1], 'attr') . '"' : '' ?>>
              <span class="kedu-medal<?= $pinArt !== null ? ' has-art' : '' ?>" aria-hidden="true">
                <?= art_or_icon($pinArt ?? ($level['status'] === 'completed' ? star_src() : null), $icons[$level['status']] ?? 'lantern') ?>
                <?php if ($level['status'] === 'locked'): ?><span class="kedu-lock"><?= icon('lock') ?></span><?php endif ?>
                <?php // Completed: a gold check badge in the lock badge's place (owner's picture, 18 October 2026) ?>
                <?php if ($level['status'] === 'completed'): ?><span class="kedu-lock kedu-done"><?= icon('check') ?></span><?php endif ?>
                <?php if ($unheard($level)): ?><span class="kedu-unheard"></span><?php endif ?>
              </span>
              <span class="kedu-city-caption">
                <b><?= esc($level['name']) ?></b>
                <small><?= esc(lang('Game.levelStatus_' . $level['status'])) ?> · <span class="num"><?= esc($level['completed_nodes']) ?>/<?= esc($level['total_nodes']) ?></span></small>
              </span>
            </a>
          </li>
        <?php endforeach ?>
      </ol>
    </div>
    <p class="kedu-hint" aria-hidden="true"><span class="kedu-hint-dot"></span><?= esc(lang('Game.mapTapHint')) ?></p>
  </div>

  <div class="kedu-panels stage-box">
    <?php foreach ($levels as $level): ?>
      <?php
      $locked = $level['status'] === 'locked';
      // The region's loading picture (bg.loading.{code}, else its background) fills the panel, fading to the left
      $panelArt = region_loading_bg($level['code'], $level['background'] ?? null);
      ?>
      <section class="kedu-panel is-<?= esc($level['status'], 'attr') ?><?= $level['code'] === $selected ? ' is-selected' : '' ?><?= $panelArt !== null ? ' has-art' : '' ?>"
               id="region-<?= esc($level['code'], 'attr') ?>" aria-labelledby="region-<?= esc($level['code'], 'attr') ?>-title">
        <?php if ($panelArt !== null): ?>
          <img class="kedu-panel-art" src="<?= esc($panelArt, 'attr') ?>" alt="" decoding="async" draggable="false"<?= $level['code'] === $selected ? '' : ' loading="lazy"' ?>>
        <?php endif ?>
        <p class="kedu-panel-eyebrow"><span><?= esc(lang('Game.mapSelected')) ?></span></p>
        <h2 id="region-<?= esc($level['code'], 'attr') ?>-title"><?= esc($level['name']) ?></h2>
        <p class="kedu-panel-meta"><?= esc(lang('Game.levelOrder', [$level['sequence']])) ?> · <?= esc(lang_or('Game.difficulty_' . $level['difficulty'], $level['difficulty'])) ?></p>
        <span class="kedu-panel-status is-<?= esc($level['status'], 'attr') ?>"><?= icon($statusIcons[$level['status']] ?? 'sparkle') ?> <span><?= esc(lang('Game.levelStatus_' . $level['status'])) ?></span></span>
        <?php if ($locked): ?>
          <p class="kedu-panel-hint"><?= icon('lock') ?> <?= esc(lang('Game.levelLocked')) ?></p>
        <?php else: ?>
          <div class="kedu-panel-progress">
            <span class="kedu-bar" aria-hidden="true"><i style="width: <?= $level['total_nodes'] > 0 ? (int) round($level['completed_nodes'] / $level['total_nodes'] * 100) : 0 ?>%"></i></span>
            <span class="num"><?= esc(lang('Game.nodesProgress', [$level['completed_nodes'], $level['total_nodes']])) ?></span>
          </div>
          <?php $stars = max(0, min(3, (int) $level['stars'])); ?>
          <p class="kedu-panel-stars" role="img" aria-label="<?= esc(lang('Game.resultStarsOf', [$stars, 3]), 'attr') ?>">
            <?php for ($i = 1; $i <= 3; $i++): ?>
              <img class="kedu-star<?= $i <= $stars ? ' is-on' : ' is-off' ?>" src="<?= esc($starSrc, 'attr') ?>" alt="" draggable="false">
            <?php endfor ?>
          </p>
        <?php endif ?>
        <div class="kedu-panel-actions">
          <?php if (($intros[$level['code']] ?? []) !== []): ?>
            <a class="kedu-btn is-secondary kenal-card-btn<?= $unheard($level) ? ' is-unheard' : '' ?>" href="#kenal-<?= esc($level['code'], 'attr') ?>"
               data-kenal-open="<?= esc($level['code'], 'attr') ?>" aria-label="<?= esc(lang('Game.kenalRegion', [$level['name']]), 'attr') ?>">
              <?php // The owner's Kenali picture (book, magnifier, bulb; 18 October 2026), shipped in assets/ui ?>
              <img class="kenali-art" src="<?= esc(game_asset_url('ui/icon-kenali.webp'), 'attr') ?>" alt="" width="40" height="40" draggable="false"> <span><?= esc(lang('Game.kenali')) ?></span>
              <?php if ($unheard($level)): ?>
                <span class="kenal-badge" data-kenal-badge><?= esc(lang('Game.kenalUnheard')) ?></span>
              <?php endif ?>
            </a>
          <?php endif ?>
          <?php if (! $locked): ?>
            <a class="kedu-btn is-primary" href="<?= esc($href($level), 'attr') ?>"<?= $curtainOf($level) ?>>
              <span><?= esc($level['status'] === 'open' ? lang('Game.start') : lang('Game.continue')) ?></span>
            </a>
          <?php else: ?>
            <a class="kedu-btn is-primary" href="<?= esc($href($level), 'attr') ?>" aria-disabled="true" data-locked-message="<?= esc(lang('Game.levelLocked'), 'attr') ?>">
              <?= icon('lock') ?> <span><?= esc(lang('Game.continue')) ?></span>
            </a>
          <?php endif ?>
        </div>
      </section>
    <?php endforeach ?>
  </div>

  <?php if (($story ?? []) === []): ?>
    <aside class="kedu-story kedu-story-plain stage-box">
      <?= component('partials/kedu-portrait', ['src' => $jakaSrc, 'name' => lang('Game.char_jaka')]) ?>
      <?= component('narration', ['text' => lang('Game.mapLead'), 'speaker' => 'jaka', 'tail' => 'down', 'class' => 'stage-scroll']) ?>
    </aside>
  <?php else: ?>
    <?php $storyTotal = count($story); ?>
    <aside class="map-story kedu-story narrator stage-box" aria-labelledby="map-story-title"
           data-narrator data-context="map_intro" data-prefix="peta-" data-keyboard="0"
           data-mode="<?= ! empty($curtain) ? 'external' : 'manual' ?>">
      <h2 class="visually-hidden" id="map-story-title"><?= esc(lang('Game.mapStoryTitle')) ?></h2>
      <div class="narrator-fx" data-narrator-fx aria-hidden="true"></div>
      <ol class="slides map-story-slides">
        <?php foreach ($story as $index => $line): ?>
          <?php
          $n         = $index + 1;
          $character = (string) ($line['character_code'] ?? 'jaka');
          $pose      = (string) ($line['pose'] ?? '') ?: 'idle';
          $effect    = (string) ($line['effect'] ?? '');
          $title     = tr($line, 'title', $locale);
          $text      = tr($line, 'text', $locale);
          $audioId   = (int) ($locale === 'en' ? ($line['audio_en_asset_id'] ?? 0) : ($line['audio_id_asset_id'] ?? 0));
          ?>
          <li class="slide map-story-slide" id="peta-<?= $n ?>" data-index="<?= $n ?>"
              data-character="<?= esc($character, 'attr') ?>" data-pose="<?= esc($pose, 'attr') ?>"
              <?= $effect !== '' ? 'data-effect="' . esc($effect, 'attr') . '"' : '' ?>
              aria-label="<?= esc(lang('Game.slideOf', [$n, $storyTotal]), 'attr') ?>">
            <?php if ($character === 'jaka'): ?>
              <?= component('partials/kedu-portrait', ['src' => $jakaSrc, 'name' => lang('Game.char_jaka')]) ?>
            <?php elseif ($character !== 'narator'): ?>
              <span class="kedu-portrait"><?= component('character', ['character' => $character, 'pose' => $pose, 'class' => 'pose-' . $pose]) ?></span>
            <?php endif ?>
            <blockquote class="narration narration-down map-story-line stage-scroll" data-narrator-advance>
              <cite><?= esc(lang_or('Game.char_' . $character, $character)) ?><?php if ($title !== ''): ?><span class="map-story-title">· <?= esc($title) ?></span><?php endif ?>
                <span class="kedu-dots" aria-hidden="true"><?php for ($d = 1; $d <= $storyTotal; $d++): ?><i<?= $d === $n ? ' class="is-on"' : '' ?>></i><?php endfor ?></span></cite>
              <p class="slide-text"><?= esc($text) ?></p>
            </blockquote>
            <?php if (($audioSrc = audio_src($audioId ?: null)) !== null): ?>
              <?= component('audio-player', ['audioId' => $audioId, 'audioSrc' => $audioSrc, 'transcript' => $text]) ?>
            <?php endif ?>
            <nav class="map-story-nav" aria-label="<?= esc(lang('Game.slideOf', [$n, $storyTotal]), 'attr') ?>">
              <?php if ($n > 1): ?>
                <a class="icon-btn" href="#peta-<?= $n - 1 ?>" aria-label="<?= esc(lang('Game.narratorPrev'), 'attr') ?>"><?= icon('left') ?></a>
              <?php endif ?>
              <span class="visually-hidden"><?= esc(lang('Game.slideOf', [$n, $storyTotal])) ?></span>
              <?php if ($n < $storyTotal): ?>
                <a class="icon-btn" href="#peta-<?= $n + 1 ?>" aria-label="<?= esc(lang('Game.narratorNext'), 'attr') ?>"><?= icon('right') ?></a>
              <?php endif ?>
            </nav>
          </li>
        <?php endforeach ?>
      </ol>
      <?= component('narrator-controls') ?>
    </aside>
  <?php endif ?>

  <?php // Page buttons right of the story, under the region panel (owner, 18 October 2026); no bottom nav bar on this page ?>
  <nav class="kedu-nav stage-box<?= $allDone ? ' is-all-done' : '' ?>" aria-label="<?= esc(lang('Game.continue'), 'attr') ?>">
    <a class="kedu-btn is-secondary" href="<?= esc(base_url('pustaka'), 'attr') ?>"><?= icon('book') ?> <span><?= esc(lang('Game.library')) ?></span></a>
    <?php if ($allDone): ?>
      <?php // The ending may be watched again any time once every region is done ?>
      <a class="kedu-btn is-secondary" href="<?= esc(base_url('penutup'), 'attr') ?>"><?= icon('sparkle') ?> <span><?= esc(lang('Game.watchEnding')) ?></span></a>
      <a class="kedu-btn is-primary" href="<?= esc(base_url('refleksi'), 'attr') ?>"><span><?= esc(lang('Game.reflection')) ?></span></a>
    <?php endif ?>
  </nav>
  </div>
</section>
<?= $this->endSection() ?>
