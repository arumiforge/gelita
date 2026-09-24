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
 * Jenis (`kind`) menentukan isi bawaan. Tahap 2: `map` ("Membuka Peta
 * Kedu"). Jenis baru cukup menambah bawaan di $defaults dan, bila perlu,
 * gaya `.curtain-{kind}` di cinematic.css; API JavaScript-nya sama.
 *
 * @var string              $kind      `map`
 * @var list<string>        $preload   URL aset yang dipramuat (progres nyata)
 * @var bool|null           $tap       ketukan penutup (bawaan true)
 * @var string|null         $title     bawaan menurut jenis
 * @var list<string>|null   $statuses  baris status bergilir
 * @var string|null         $tapLabel  label tombol ketuk
 */
$kind     = $kind ?? 'map';
$defaults = [
    'map' => [
        'title'    => lang('Game.curtainMapTitle'),
        'statuses' => [lang('Game.curtainMapStatus1'), lang('Game.curtainMapStatus2'), lang('Game.curtainMapStatus3')],
        'tapLabel' => lang('Game.curtainMapTap'),
    ],
];
$preset   = $defaults[$kind] ?? $defaults['map'];
$title    = $title ?? $preset['title'];
$statuses = array_values($statuses ?? $preset['statuses']);
$tapLabel = $tapLabel ?? $preset['tapLabel'];
$tap      = $tap ?? true;
$preload  = array_values(array_unique(array_filter($preload ?? [], static fn ($url): bool => is_string($url) && $url !== '')));
$bgSrc    = media_key_src('bg.loading');
?>
<div class="curtain curtain-<?= esc($kind, 'attr') ?>" data-curtain-layer="<?= esc($kind, 'attr') ?>"
     data-tap="<?= $tap ? '1' : '0' ?>"
     data-preload="<?= esc(json_encode($preload, JSON_UNESCAPED_SLASHES), 'attr') ?>"
     data-statuses="<?= esc(json_encode($statuses, JSON_UNESCAPED_UNICODE), 'attr') ?>"
     aria-busy="true">
  <?php if ($bgSrc !== null): ?>
    <img class="curtain-bg" src="<?= esc($bgSrc, 'attr') ?>" alt="" fetchpriority="high">
  <?php endif ?>
  <div class="curtain-core">
    <div class="curtain-lantern" aria-hidden="true">
      <span class="curtain-light"></span>
      <span class="curtain-orbit">
        <i class="curtain-shard"></i><i class="curtain-shard"></i><i class="curtain-shard"></i>
      </span>
      <span class="curtain-icon"><?= icon('lantern') ?></span>
    </div>
    <h2 class="curtain-title"><?= esc($title) ?></h2>
    <p class="curtain-status" role="status" aria-live="polite" data-curtain-status><?= esc($statuses[0] ?? '') ?></p>
    <div class="curtain-beam" role="progressbar" aria-label="<?= esc(lang('Game.curtainProgress'), 'attr') ?>"
         aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" data-curtain-beam><i></i></div>
    <?php if ($tap): ?>
      <button type="button" class="btn btn-primary btn-lg curtain-tap" data-curtain-tap hidden><?= icon('lantern') ?> <?= esc($tapLabel) ?></button>
    <?php endif ?>
  </div>
</div>
