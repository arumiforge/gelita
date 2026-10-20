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
 * Layout (owner, 19 October 2026), with the same day's follow-ups: the
 * full-size HUD with the shard pill under the logo, the region name (proper
 * case since 20 October), and the difficulty badge left of the language control (section
 * `hudBadge`); Lewati under the menu button; no Peta Kedu button (the menu
 * has it); the parchment box with the speaker's name, the page dots, and
 * the narrator controls in its first row; the two characters at the stage's
 * edges; the last line's main button centred on the page.
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

<?= $this->section('music') ?><?= esc('dialog-' . $level->code) ?><?= $this->endSection() ?>
<?= $this->section('title') ?><?= esc($region) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= $bg ?><?= $this->endSection() ?>
<?= $this->section('bodyClass') ?>is-cinematic is-region-dialogue<?= $this->endSection() ?>
<?php /* Full-size HUD; the shard pill sits under the logo (cinematic.css) */ ?>
<?= $this->section('hudStyle') ?>menu<?= $this->endSection() ?>
<?= $this->section('hudBadge') ?><?= component('partials/region-difficulty', ['level' => $level]) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= component('partials/dialogue-scene', [
    'level'   => $level,
    'slides'  => $slides,
    'locale'  => $locale,
    'context' => 'level_open',
    'screen'  => 'dialogue',
    'cinematic' => true,
    // Next screen: the region map (/wilayah/{code})
    'nextAssets' => [media_exists($level->map_media_id) ? media_src($level->map_media_id) : null],
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
    ['label' => lang('Game.skip'), 'href' => base_url('wilayah/' . $level->code), 'style' => 'quiet', 'arrow' => 'right'],
]]) ?>
<?= $this->endSection() ?>
