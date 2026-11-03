<?php
/**
 * 10. Kartu Misi — `/misi/{code}/{seq}` → ChallengeController::brief
 *
 * Card redesigned 18 October 2026 after the owner's picture: medallion
 * `{n}/{total}`, the region with a pin between two rules, the title, the
 * story, the instruction in a hint box, and the gold main button between two
 * flourishes; the best result (replays) sits under the hint box.
 *
 * HUD (owner, 21 October 2026): the logo alone in the middle (`logo-centre`,
 * no region name, no shard plate) and Back to the region map on the navy
 * plate in the top-left corner, in place of the old Back button at the foot.
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
$curtainBg   = media_key_src('bg.loading.' . $level->code);
$engine      = (string) $node->engine_type;
$engineName  = engine_label($engine, $node->variant_code);
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('music') ?>challenge<?= $this->endSection() ?>
<?= $this->section('title') ?><?= esc($node->text('title', $locale)) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= $bg ?><?= $this->endSection() ?>
<?php // Logo alone in the middle of the HUD, Back to the region map on the navy plate top left (owner, 21 October 2026) ?>
<?= $this->section('hudLayout') ?>logo-centre<?= $this->endSection() ?>
<?= $this->section('hudBack') ?><?= base_url('wilayah/' . $level->code) ?><?= $this->endSection() ?>

<?= $this->section('overlay') ?>
<template id="tpl-curtain-challenge"><?= component('curtain', [
    'kind'    => 'challenge',
    'engine'  => $engine,
    'eyebrow' => $engineName,
    'title'   => $node->text('title', $locale),
    'number'  => $sequence,
    'lead'    => lang_or('Game.challengeLead_' . ($node->variant_code ?? ''), lang_or('Game.challengeLead_' . $engine, '')),
]) ?></template>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php /* Next screen: the challenge (its scene image, when the node has one) */ ?>
<section class="mission" data-screen="mission-brief"<?= next_assets_attr([media_exists($node->scene_media_id) ? media_src($node->scene_media_id) : null]) ?>>
  <article class="panel-parchment mission-card stage-box" aria-labelledby="mission-title">
    <?php // Card after the owner's picture (18 October 2026): n/total medallion, region line, title, story, hint box, gold button ?>
    <svg class="mission-defs" aria-hidden="true" focusable="false">
      <defs>
        <symbol id="mission-corner" viewBox="0 0 48 48"><path d="M3 45V14C3 8 8 3 14 3h31M9 45V19c0-6 4-10 10-10h26M14 22c0-5 3-8 8-8M3 30c5 0 8 3 8 8"/><circle cx="22" cy="22" r="2.5"/></symbol>
        <symbol id="mission-flourish" viewBox="0 0 56 24"><path d="M54 12H30M30 12c-4-7-13-8-17-3-3 4 0 9 5 8 4-1 4-6 0-6M30 12c-4 7-13 8-17 3"/><path d="M8 12l-5-4v8z"/></symbol>
      </defs>
    </svg>
    <?php foreach (['tl', 'tr', 'bl', 'br'] as $corner): ?>
      <svg class="mission-corner is-<?= $corner ?>" aria-hidden="true" focusable="false"><use href="#mission-corner"/></svg>
    <?php endforeach ?>

    <div class="mission-crest" aria-hidden="true">
      <svg class="mission-wing is-left" focusable="false"><use href="#mission-flourish"/></svg>
      <span class="mission-seal num"><b><?= esc($sequence) ?></b><small>/<?= esc($totalNodes) ?></small></span>
      <svg class="mission-wing is-right" focusable="false"><use href="#mission-flourish"/></svg>
    </div>
    <p class="mission-region">
      <span class="visually-hidden"><?= esc(lang('Game.challengeOf', [$sequence, $totalNodes])) ?> · </span>
      <?= round_icon('map', 'mission-region-art', 'pin') ?> <span><?= esc($region) ?></span>
    </p>
    <h1 id="mission-title"><?= esc($node->text('title', $locale)) ?></h1>
    <p class="visually-hidden"><?= esc($engineName) ?></p>

    <div class="mission-body stage-scroll">
      <?php if ($description !== ''): ?>
        <p class="mission-description"><?= esc($description) ?></p>
      <?php endif ?>
      <?php if ($instruction !== ''): ?>
        <p class="mission-instruction"><?= round_icon('info', 'mission-info-art') ?: '<span class="mission-info" aria-hidden="true">i</span>' ?> <span><?= esc($instruction) ?></span></p>
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
          <?php if ((int) $best->scorable_items > 0): ?>
            <span class="mission-best-pass"><?= esc(lang('Game.firstPass')) ?>: <span class="num"><?= esc(lang('Game.firstTryCount', [(int) $best->first_pass_correct, (int) $best->scorable_items])) ?></span></span>
          <?php endif ?>
        </div>
      <?php endif ?>
    </div>

    <div class="mission-actions">
      <svg class="mission-wing is-left" aria-hidden="true" focusable="false"><use href="#mission-flourish"/></svg>
      <?php if (! empty($bookletMissing)): ?>
        <?php // Research mode without a booklet (31 K10): no start button, a way back to the region map ?>
        <p class="mission-wait" role="status"><?= esc(lang('Game.bookletMissing')) ?></p>
        <a class="btn btn-primary btn-xl" href="<?= base_url('wilayah/' . $level->code) ?>"><?= icon('arrow-left') ?> <span><?= esc(lang('Game.backToRegion')) ?></span></a>
      <?php else: ?>
      <a class="btn btn-primary btn-xl" href="<?= base_url('tantangan/' . $level->code . '/' . $sequence) ?>"<?= curtain_attrs('challenge', [], [$curtainBg, $bg, character_frame_src('jaka')], $curtainBg) ?>>
        <span><?= esc(lang('Game.startChallenge')) ?></span> <?= icon('arrow-right') ?>
      </a>
      <?php endif ?>
      <svg class="mission-wing is-right" aria-hidden="true" focusable="false"><use href="#mission-flourish"/></svg>
    </div>
  </article>
</section>
<?= $this->endSection() ?>
