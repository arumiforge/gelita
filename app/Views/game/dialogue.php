<?php
/**
 * 8. Dialog — `/dialog/{code}` → DialogueController::show
 *
 * Dialog pembuka wilayah (`level_open`, 15–16 baris) sebagai adegan
 * dramatis (Tahap 3, partials/dialogue-scene): kartu bab "Bab {n} ·
 * {wilayah}" + tagline + "Ketuk untuk mendengar kisahnya" dengan jejak kaki
 * yang melanjutkan tirai wilayah; panggung dua tokoh; gambar tokoh yang
 * berbicara berganti sesuai pose baris; efek, audio, dan maju otomatis lewat
 * game/narrator.js. Slide akhir "Masuk ke {wilayah}" → `/wilayah/{code}`.
 * Nav "Lewati" dan gerbang dialog (dialogueGate()/markDialogueShown()) tetap.
 *
 * @var App\Entities\Level         $level
 * @var list<array<string, mixed>> $slides
 * @var string                     $tagline judul slide pertama region_intro wilayah ini
 * @var string                     $locale
 */
$bg     = media_exists($level->background_media_id) ? media_src($level->background_media_id) : '';
$region = $level->text('name', $locale);
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc($region) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= $bg ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= component('partials/dialogue-scene', [
    'level'   => $level,
    'slides'  => $slides,
    'locale'  => $locale,
    'context' => 'level_open',
    'screen'  => 'dialogue',
    'eyebrow' => lang('Game.levelOrder', [$level->sequence]),
    'tap'     => [
        'variant' => 'chapter',
        'trail'   => true,
        'eyebrow' => lang('Game.chapterOf', [$level->sequence, $region]),
        'title'   => $tagline ?? '',
        'label'   => lang('Game.chapterTap'),
    ],
    'final' => [
        'url'   => base_url('wilayah/' . $level->code),
        'label' => lang('Game.enterRegionName', [$region]),
    ],
]) ?>
<?= $this->endSection() ?>

<?= $this->section('nav') ?>
<?= component('nav-bar', ['nav' => [
    ['label' => lang('Game.mapKedu'), 'href' => base_url('peta'), 'style' => 'quiet', 'arrow' => 'left'],
    ['label' => lang('Game.skip'), 'href' => base_url('wilayah/' . $level->code), 'style' => 'quiet', 'arrow' => 'right'],
]]) ?>
<?= $this->endSection() ?>
