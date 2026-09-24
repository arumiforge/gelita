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
        'attrs' => isset($nextRegion['curtain']) ? curtain_attrs('region', $nextRegion['curtain']['text'], $nextRegion['curtain']['preload']) : '',
    ];
} elseif ($nextRegion === null && $allCompleted) {
    $final = ['url' => base_url('penutup'), 'label' => lang('Game.continue')];
} else {
    $final = ['url' => base_url('peta'), 'label' => lang('Game.mapKedu'), 'icon' => 'map'];
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

<?= $this->section('title') ?><?= esc(lang('Game.doneTitle', [$region])) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= $bg ?><?= $this->endSection() ?>

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

<?= $this->section('nav') ?>
<?= component('nav-bar', ['nav' => [
    ['label' => lang('Game.backToRegion'), 'href' => base_url('wilayah/' . $level->code), 'style' => 'quiet', 'arrow' => 'left'],
    ['label' => lang('Game.mapKedu'), 'href' => base_url('peta'), 'style' => 'quiet', 'icon' => 'map'],
]]) ?>
<?= $this->endSection() ?>
