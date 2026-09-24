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
 * - `region`    "Menuju {wilayah}…" (Tahap 3): jejak kaki kiri–kanan
 *               melintasi layar, tagline, chip tingkat kesulitan; ±1,8 detik
 *               tanpa ketukan. Dirender sekali di <template id="tpl-curtain-region">;
 *               teksnya diisi dari tautan (`data-curtain-text`).
 * - `challenge` sebelum /tantangan (Tahap 3): judul node, label engine,
 *               ajakan singkat, dan visual per engine (`data-engine`);
 *               ≤1,5 detik, ketukan melewatinya.
 *
 * @var string              $kind      `map` | `region` | `challenge`
 * @var list<string>        $preload   URL aset yang dipramuat (progres nyata)
 * @var bool|null           $tap       ketukan penutup (bawaan menurut jenis)
 * @var string|null         $title     bawaan menurut jenis
 * @var list<string>|null   $statuses  baris status bergilir
 * @var string|null         $tapLabel  label tombol ketuk
 * @var string|null         $eyebrow   baris kecil di atas judul (`challenge`: label engine)
 * @var string|null         $tagline   `region`: tagline wilayah
 * @var string|null         $chip      `region`: tingkat kesulitan
 * @var string|null         $lead      `challenge`: ajakan singkat per engine
 * @var string|null         $engine    `challenge`: puzzle | rumpang | boleh | pilihan | cari
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
        'timing'   => ['min' => 1800, 'max' => 2400, 'skip' => false],
    ],
    'challenge' => [
        'title'    => '',
        'statuses' => [],
        'tapLabel' => '',
        'tap'      => false,
        'timing'   => ['min' => 1200, 'max' => 1500, 'skip' => true],
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
?>
<div class="curtain curtain-<?= esc($kind, 'attr') ?>" data-curtain-layer="<?= esc($kind, 'attr') ?>"
     data-tap="<?= $tap ? '1' : '0' ?>"
     <?php if ($timing !== []): ?>data-min-ms="<?= (int) $timing['min'] ?>" data-max-ms="<?= (int) $timing['max'] ?>" data-skip="<?= $timing['skip'] ? '1' : '0' ?>"<?php endif ?>
     <?php if ($kind === 'challenge'): ?>data-engine="<?= esc($engine, 'attr') ?>"<?php endif ?>
     data-preload="<?= esc(json_encode($preload, JSON_UNESCAPED_SLASHES), 'attr') ?>"
     data-statuses="<?= esc(json_encode($statuses, JSON_UNESCAPED_UNICODE), 'attr') ?>"
     aria-busy="true">
  <?php if ($bgSrc !== null): ?>
    <img class="curtain-bg" src="<?= esc($bgSrc, 'attr') ?>" alt="" fetchpriority="high">
  <?php endif ?>

  <?php if ($kind === 'region'): ?>
    <?= component('partials/footsteps', ['steps' => 8, 'class' => 'curtain-trail']) ?>
  <?php endif ?>

  <div class="curtain-core">
    <?php if ($kind === 'map'): ?>
      <div class="curtain-lantern" aria-hidden="true">
        <span class="curtain-light"></span>
        <span class="curtain-orbit">
          <i class="curtain-shard"></i><i class="curtain-shard"></i><i class="curtain-shard"></i>
        </span>
        <span class="curtain-icon"><?= icon('lantern') ?></span>
      </div>
    <?php elseif ($kind === 'challenge'): ?>
      <?= component('partials/challenge-art', ['engine' => $engine]) ?>
      <p class="curtain-eyebrow"><?= esc($eyebrow ?? '') ?></p>
    <?php endif ?>

    <h2 class="curtain-title" <?= $kind === 'region' ? 'data-curtain-text="title"' : '' ?>><?= esc($title) ?></h2>

    <?php if ($kind === 'region'): ?>
      <p class="curtain-tagline" data-curtain-text="tagline"<?= ($tagline ?? '') === '' ? ' hidden' : '' ?>><?= esc($tagline ?? '') ?></p>
      <span class="chip curtain-chip" data-curtain-text="chip"<?= ($chip ?? '') === '' ? ' hidden' : '' ?>><?= esc($chip ?? '') ?></span>
    <?php elseif ($kind === 'challenge'): ?>
      <p class="curtain-lead"><?= esc($lead ?? '') ?></p>
    <?php endif ?>

    <?php if ($statuses !== []): ?>
      <p class="curtain-status" role="status" aria-live="polite" data-curtain-status><?= esc($statuses[0]) ?></p>
    <?php endif ?>
    <div class="curtain-beam" role="progressbar" aria-label="<?= esc(lang('Game.curtainProgress'), 'attr') ?>"
         aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" data-curtain-beam><i></i></div>
    <?php if ($tap): ?>
      <button type="button" class="btn btn-primary btn-lg curtain-tap" data-curtain-tap hidden><?= icon('lantern') ?> <?= esc($tapLabel) ?></button>
    <?php elseif ($timing['skip'] ?? false): ?>
      <p class="curtain-skip"><?= esc(lang('Game.curtainSkip')) ?></p>
    <?php endif ?>
  </div>
</div>
