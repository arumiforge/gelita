<?php
/**
 * Tirai — layar pemuatan layar penuh (docs/05_VIEW_UI.md §Tirai).
 *
 * Menutupi halaman sejak paint pertama sambil core/curtain.js memuat aset
 * halaman itu dengan progres nyata, lalu (bila `tap`) menunggu ketukan
 * penutup yang sekaligus membuka kunci audio. Tanpa JavaScript tirai tidak
 * pernah menutupi halaman: css/noscript.css menyembunyikannya, dan animasi
 * pengaman di cinematic.css memudarkannya sendiri setelah ±10 detik bila
 * modul JavaScript gagal dimuat (curtain.js membatalkan animasi itu).
 *
 * Jenis (`kind`) menentukan isi bawaan dan gaya `.curtain-{kind}`:
 *
 * - `map`       "Membuka Peta Kedu" (Tahap 2): lentera, serpihan mengorbit,
 *               status bergilir, ketukan penutup.
 * - `region`    "Menuju {wilayah}…" (Tahap 3): the region's picture full
 *               bleed, the name on the ornate navy head plate, tagline and
 *               the difficulty badge of the region dialogue under it (meter
 *               bars, "Tingkat kesulitan"; owner, 21 October 2026; no footprints);
 *               ±1,8 detik tanpa ketukan. Dirender sekali di <template id="tpl-curtain-region">;
 *               teksnya diisi dari tautan (`data-curtain-text`).
 *
 * Every kind shows its progress on the owner's bronze loading track
 * (`assets/ui/loading-track.webp`, `.has-track`) when the picture is
 * installed; the `map` tap prompt is small plain text (21 October 2026).
 * - `challenge` sebelum /tantangan (Tahap 3): judul node, label engine,
 *               ajakan singkat, dan visual per engine (`data-engine`);
 *               ≤1,5 detik, ketukan melewatinya.
 *
 * Between-screen curtains (`region`, `challenge`) carry `data-local-min-ms`
 * (600 ms): core/curtain.js uses it when assets are local (asset package
 * Stage 4, P8). The `map` curtain that opens the game keeps 2.5 s.
 *
 * @var string              $kind      `map` | `region` | `challenge`
 * @var list<string>        $preload   URL aset yang dipramuat (progres nyata)
 * @var bool|null           $tap       ketukan penutup (bawaan menurut jenis)
 * @var string|null         $title     bawaan menurut jenis
 * @var list<string>|null   $statuses  baris status bergilir
 * @var string|null         $tapLabel  label tombol ketuk
 * @var string|null         $eyebrow   baris kecil di atas judul (`challenge`: label engine)
 * @var string|null         $tagline   `region`: tagline wilayah
 * @var string|null         $chip      `region`: difficulty name (Mudah, Sedang, Sulit)
 * @var string|null         $difficulty `region`: stored difficulty code (mudah, sedang, sulit)
 * @var string|null         $lead      `challenge`: ajakan singkat per engine
 * @var string|null         $engine    `challenge`: puzzle | rumpang | boleh | cari
 */
$kind     = in_array($kind ?? 'map', ['map', 'region', 'challenge'], true) ? ($kind ?? 'map') : 'map';
$defaults = [
    'map' => [
        'title'    => lang('Game.curtainMapTitle'),
        'statuses' => [lang('Game.curtainMapStatus1'), lang('Game.curtainMapStatus2'), lang('Game.curtainMapStatus3')],
        'tapLabel' => lang('Game.curtainMapTap'),
        'tap'      => true,
        'timing'   => [],
    ],
    'region' => [
        'title'    => '',
        'statuses' => [],
        'tapLabel' => '',
        'tap'      => false,
        'timing'   => ['min' => 1800, 'max' => 2400, 'skip' => false, 'localMin' => 600],
    ],
    'challenge' => [
        'title'    => '',
        'statuses' => [],
        'tapLabel' => '',
        'tap'      => false,
        'timing'   => ['min' => 1200, 'max' => 1500, 'skip' => true, 'localMin' => 600],
    ],
];
$preset   = $defaults[$kind];
$title    = $title ?? $preset['title'];
$statuses = array_values($statuses ?? $preset['statuses']);
$tapLabel = $tapLabel ?? $preset['tapLabel'];
$tap      = $tap ?? $preset['tap'];
$timing   = $preset['timing'];
$preload  = array_values(array_unique(array_filter($preload ?? [], static fn ($url): bool => is_string($url) && $url !== '')));
$bgSrc    = $kind === 'map' ? media_key_src('bg.loading') : null;
$engine   = in_array($engine ?? '', config('Gelita')->engineTypes, true) ? $engine : 'puzzle';
// The owner's pictures: the bronze loading track (every kind) and the navy head plate (`region`)
$skins = [];
foreach (['loading-track' => 'loading-track', 'head-plate' => 'map-head-plate'] as $var => $file) {
    if (is_file(FCPATH . 'assets/ui/' . $file . '.webp')) {
        $skins[$var] = '--' . $var . ': url(&quot;' . esc(asset_url_versioned('ui/' . $file . '.webp'), 'attr') . '&quot;)';
    }
}
$plated = $kind === 'region' && isset($skins['head-plate']);
?>
<div class="curtain curtain-<?= esc($kind, 'attr') ?>" data-curtain-layer="<?= esc($kind, 'attr') ?>"
     data-tap="<?= $tap ? '1' : '0' ?>"
     <?php if ($timing !== []): ?>data-min-ms="<?= (int) $timing['min'] ?>" data-max-ms="<?= (int) $timing['max'] ?>" data-skip="<?= $timing['skip'] ? '1' : '0' ?>"<?php endif ?>
     <?php if ($kind === 'challenge'): ?>data-engine="<?= esc($engine, 'attr') ?>"<?php endif ?>
     <?php if (isset($timing['localMin'])): ?>data-local-min-ms="<?= (int) $timing['localMin'] ?>"<?php endif ?>
     data-preload="<?= esc(json_encode($preload, JSON_UNESCAPED_SLASHES), 'attr') ?>"
     data-statuses="<?= esc(json_encode($statuses, JSON_UNESCAPED_UNICODE), 'attr') ?>"
     <?= $skins !== [] ? 'style="' . implode('; ', $skins) . '"' : '' ?>
     aria-busy="true">
  <?php if ($bgSrc !== null): ?>
    <img class="curtain-bg" src="<?= esc($bgSrc, 'attr') ?>" alt="" fetchpriority="high">
  <?php elseif ($kind !== 'map'): ?>
    <?php // Filled by core/curtain.js from the link's data-curtain-bg (display plan Phase C) ?>
    <img class="curtain-bg is-deferred" alt="" hidden>
  <?php endif ?>

  <div class="curtain-core">
    <?php if ($kind === 'map'): ?>
      <div class="curtain-lantern" aria-hidden="true">
        <span class="curtain-light"></span>
        <span class="curtain-orbit">
          <i class="curtain-shard"></i><i class="curtain-shard"></i><i class="curtain-shard"></i>
        </span>
        <span class="curtain-icon"><?= art_or_icon(region_art_src('lantern'), 'lantern') ?></span>
      </div>
    <?php elseif ($kind === 'challenge'): ?>
      <?= component('partials/challenge-art', ['engine' => $engine]) ?>
      <p class="curtain-eyebrow"><?= esc($eyebrow ?? '') ?></p>
    <?php endif ?>

    <h2 class="curtain-title<?= $plated ? ' has-plate' : '' ?>" <?= $kind === 'region' ? 'data-curtain-text="title"' : '' ?>><?= esc($title) ?></h2>

    <?php if ($kind === 'region'): ?>
      <p class="curtain-tagline" data-curtain-text="tagline"<?= ($tagline ?? '') === '' ? ' hidden' : '' ?>><?= esc($tagline ?? '') ?></p>
      <?php // The region dialogue's difficulty badge (meter + "Tingkat kesulitan", owner 21 October 2026); core/curtain.js lights the bars from `difficulty` ?>
      <?php $steps = ['mudah' => 1, 'sedang' => 2, 'sulit' => 3][$difficulty ?? ''] ?? 0; ?>
      <div class="region-difficulty curtain-difficulty<?= $steps > 0 ? ' is-' . esc($difficulty, 'attr') : '' ?>" data-curtain-difficulty<?= ($chip ?? '') === '' ? ' hidden' : '' ?>>
        <span class="region-difficulty-meter" aria-hidden="true">
          <?php for ($i = 1; $i <= 3; $i++): ?>
            <i<?= $i <= $steps ? ' class="is-on"' : '' ?> style="--i: <?= $i ?>"></i>
          <?php endfor ?>
        </span>
        <span class="region-difficulty-text">
          <span class="region-difficulty-label"><?= esc(lang('Game.difficultyLabel')) ?></span>
          <span class="region-difficulty-level" data-curtain-text="chip"><?= esc($chip ?? '') ?></span>
        </span>
      </div>
    <?php elseif ($kind === 'challenge'): ?>
      <p class="curtain-lead"><?= esc($lead ?? '') ?></p>
    <?php endif ?>

    <?php if ($statuses !== []): ?>
      <p class="curtain-status" role="status" aria-live="polite" data-curtain-status><?= esc($statuses[0]) ?></p>
    <?php endif ?>
    <div class="curtain-beam<?= isset($skins['loading-track']) ? ' has-track' : '' ?>" role="progressbar" aria-label="<?= esc(lang('Game.curtainProgress'), 'attr') ?>"
         aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" data-curtain-beam><i></i></div>
    <?php if ($tap): ?>
      <?php // Small plain text, no button picture (owner, 21 October 2026); the whole curtain takes the tap too ?>
      <button type="button" class="curtain-tap" data-curtain-tap hidden><?= esc($tapLabel) ?></button>
    <?php elseif ($timing['skip'] ?? false): ?>
      <p class="curtain-skip"><?= esc(lang('Game.curtainSkip')) ?></p>
    <?php endif ?>
  </div>
</div>
