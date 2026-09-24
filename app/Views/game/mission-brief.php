<?php
/**
 * 10. Kartu Misi — `/misi/{code}/{seq}` → ChallengeController::brief
 *
 * Tahap 3: "Mulai tantangan" memutar tirai tantangan (≤1,5 detik, ketukan
 * melewatinya) SEBELUM berpindah ke /tantangan. Attempt baru dibuka server
 * saat halaman tantangan dirender (ChallengeService::openNode()), jadi tirai
 * tidak menambah waktu attempt yang dicatat untuk penelitian. Tanpa
 * JavaScript tautannya langsung ke /tantangan.
 *
 * @var App\Entities\Level                  $level
 * @var App\Entities\ChallengeNode          $node
 * @var int                                 $sequence
 * @var App\Entities\ChallengeAttempt|null  $best
 * @var string                              $locale
 */
$region      = $level->text('name', $locale);
$totalNodes  = count(service('contentRepository')->nodesForLevel($level->id));
$description = $node->text('description', $locale);
$instruction = $node->text('instruction', $locale);
$audioId     = (int) ($locale === 'en' ? $node->audio_intro_en_id : $node->audio_intro_id);
$bg          = media_exists($node->background_media_id) ? media_src($node->background_media_id)
    : (media_exists($level->background_media_id) ? media_src($level->background_media_id) : '');
$engine      = (string) $node->engine_type;
$engineName  = engine_label($engine, $node->variant_code);
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc($node->text('title', $locale)) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= $bg ?><?= $this->endSection() ?>

<?= $this->section('overlay') ?>
<template id="tpl-curtain-challenge"><?= component('curtain', [
    'kind'    => 'challenge',
    'engine'  => $engine,
    'eyebrow' => $engineName,
    'title'   => $node->text('title', $locale),
    'lead'    => lang_or('Game.challengeLead_' . ($node->variant_code ?? ''), lang_or('Game.challengeLead_' . $engine, '')),
]) ?></template>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="screen screen-narrow mission" data-screen="mission-brief">
  <article class="panel-parchment mission-card">
    <span class="mission-seal num" aria-hidden="true"><?= esc($sequence) ?></span>
    <span class="eyebrow"><?= esc(lang('Game.challengeOf', [$sequence, $totalNodes])) ?> · <?= esc($region) ?></span>
    <h1><?= esc($node->text('title', $locale)) ?></h1>
    <p class="mission-engine"><span class="chip"><?= icon('puzzle') ?> <?= esc($engineName) ?></span></p>

    <?php if ($description !== ''): ?>
      <p class="mission-description"><?= esc($description) ?></p>
    <?php endif ?>
    <?php if ($instruction !== ''): ?>
      <p class="mission-instruction"><?= icon('info') ?> <span><?= esc($instruction) ?></span></p>
    <?php endif ?>

    <?php if (($audioSrc = audio_src($audioId ?: null)) !== null): ?>
      <?= component('audio-player', [
          'audioId'    => $audioId,
          'audioSrc'   => $audioSrc,
          'transcript' => $description !== '' ? $description : $instruction,
      ]) ?>
    <?php endif ?>

    <?php if ($best !== null): ?>
      <div class="mission-best">
        <span><?= esc(lang('Game.bestResult')) ?></span>
        <?= stars_html((int) $best->stars) ?>
        <b class="num"><?= esc(fmt_num($best->score, 0, $locale)) ?></b>
      </div>
    <?php endif ?>

    <div class="mission-actions">
      <a class="btn btn-primary btn-xl" href="<?= base_url('tantangan/' . $level->code . '/' . $sequence) ?>"<?= curtain_attrs('challenge', [], [$bg, character_frame_src('jaka')]) ?>>
        <?= esc(lang('Game.startChallenge')) ?> <?= icon('right') ?>
      </a>
    </div>
  </article>
</section>
<?= $this->endSection() ?>

<?= $this->section('nav') ?>
<?= component('nav-bar', ['nav' => [
    ['label' => $region, 'href' => base_url('wilayah/' . $level->code), 'style' => 'quiet', 'arrow' => 'left'],
]]) ?>
<?= $this->endSection() ?>
