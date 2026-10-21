<?php
/**
 * 12a. Wilayah tuntas — `/tuntas/{code}` → DialogueController::done
 *
 * Adegan bernarasi bergaya dialog (Tahap 3, partials/dialogue-scene) dari
 * `level_done` (4 baris): kartu ketuk "Serpihan {wilayah} kembali!", lalu
 * Jaka & Mbah Kedu dengan pose, efek, dan audio. Dibuka tombol "Lanjut" di
 * layar selesai attempt yang menuntaskan wilayah; boleh ditonton ulang.
 *
 * Slide akhir: "Baca Pustaka {wilayah}" (`/pustaka/{code}`, bila wilayah
 * punya Pustaka) dan "Lanjut ke {wilayah berikutnya}" (`entry` wilayah itu,
 * dengan tirai wilayah). Wilayah terakhir: "Lanjut" ke `/penutup` bila semua
 * tantangan tuntas, selain itu (mode unlock `free`) kembali ke Peta Kedu.
 *
 * Owner request (21 October 2026): the GELITA logo alone in the middle of the
 * HUD (no shard plate, no region name), "Peta wilayah" on the navy plate in
 * the top-left corner, no nav bar (Peta Kedu is in the HUD menu), no names
 * under the two figures. Same day, later (owner: consistent with the region
 * dialogue): the dialogue's cinematic layout (`cinematic`), so the parchment
 * box holds the speaker, the page dots, and the narrator controls with
 * Lanjut, the figures stand at the stage's edges, and the last line's
 * buttons sit centred on the page; the HUD stays as above.
 *
 * @var App\Entities\Level         $level
 * @var list<array<string, mixed>> $slides
 * @var bool                       $hasLibrary
 * @var array<string, mixed>|null  $nextRegion baris levelOverview wilayah sesudahnya (+ `curtain`)
 * @var bool                       $allCompleted
 * @var string                     $locale
 */
$bg     = media_exists($level->background_media_id) ? media_src($level->background_media_id) : '';
$region = $level->text('name', $locale);

if ($nextRegion !== null && $nextRegion['status'] !== 'locked') {
    $final = [
        'url'   => base_url($nextRegion['entry']),
        'label' => lang('Game.goToRegion', [$nextRegion['name']]),
        'attrs' => region_curtain_attrs($nextRegion['curtain'] ?? null),
        'plate' => 'next',
    ];
} elseif ($nextRegion === null && $allCompleted) {
    $final = ['url' => base_url('penutup'), 'label' => lang('Game.continue'), 'plate' => 'next'];
} else {
    $final = ['url' => base_url('peta'), 'label' => lang('Game.mapKedu'), 'icon' => 'map', 'plate' => 'next'];
}

if ($hasLibrary) {
    $final['extra'] = [[
        'label' => lang('Game.readLibraryRegion', [$region]),
        'href'  => base_url('pustaka/' . $level->code),
        'icon'  => 'book',
    ]];
}
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('music') ?><?= esc('tuntas-' . $level->code) ?><?= $this->endSection() ?>
<?= $this->section('title') ?><?= esc(lang('Game.doneTitle', [$region])) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= $bg ?><?= $this->endSection() ?>
<?= $this->section('bodyClass') ?>is-cinematic<?= $this->endSection() ?>
<?php // Logo alone in the middle, "Peta wilayah" on the navy plate top left; Peta Kedu stays in the menu (owner, 21 October 2026) ?>
<?= $this->section('hudLayout') ?>logo-centre<?= $this->endSection() ?>
<?= $this->section('hudBack') ?><?= base_url('wilayah/' . $level->code) ?><?= $this->endSection() ?>
<?= $this->section('hudBackLabel') ?><?= esc(lang('Game.backToRegion')) ?><?= $this->endSection() ?>

<?php if (($final['attrs'] ?? '') !== ''): ?>
<?= $this->section('overlay') ?>
<template id="tpl-curtain-region"><?= component('curtain', ['kind' => 'region']) ?></template>
<?= $this->endSection() ?>
<?php endif ?>

<?= $this->section('content') ?>
<?= component('partials/dialogue-scene', [
    'level'   => $level,
    'slides'  => $slides,
    'locale'  => $locale,
    'context' => 'level_done',
    'screen'  => 'region-done',
    // The region dialogue's layout (owner, 21 October 2026): speaker, dots, and the narrator controls with Lanjut in the box
    'cinematic' => true,
    // Next screen: the next region's entry (same assets as its curtain), or the Kedu map
    'nextAssets' => isset($nextRegion['curtain']) ? $nextRegion['curtain']['preload'] : [media_key_src('map.kedu'), media_key_src('bg.map')],
    'eyebrow' => lang('Game.levelOrder', [$level->sequence]) . ' · ' . lang('Game.levelStatus_completed'),
    'tap'     => [
        'variant' => 'done',
        'eyebrow' => lang('Game.levelOrder', [$level->sequence]) . ' · ' . lang('Game.levelStatus_completed'),
        'title'   => lang('Game.doneTitle', [$region]),
        'label'   => lang('Game.chapterTap'),
    ],
    'final' => $final,
]) ?>
<?= $this->endSection() ?>
