<?php
/**
 * Cek suara — `/cek-suara` → SoundCheckController (research mode only; owner,
 * 15 November 2026, decision 4 of 04; built 16 November 2026).
 *
 * The first page of every research-mode game session (filter `soundCheck`).
 * Laid out like Mulai (`/mulai`): the title and the cards alone in the middle
 * of the stage on the dark gate picture. Putar suara plays one sound
 * (game/sound-check.js: `shard`, with the game sound switched on); the two
 * answers stay disabled until it has played once (without JavaScript they are
 * always enabled). After "Tidak terdengar" the page shows what to do and
 * offers "Lanjut tanpa suara".
 *
 * @var bool $help the student answered "Tidak terdengar"
 */
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc(lang('Game.soundCheckTitle')) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= asset_url_versioned('ui/bg-mulai.webp') ?><?= $this->endSection() ?>
<?= $this->section('bodyClass') ?>is-dim-art<?= $this->endSection() ?>
<?= $this->section('hudLayout') ?>logo-centre<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="start sound-check<?= $help ? ' is-help' : '' ?>" data-screen="sound-check">
  <div class="start-panel ornate-panel is-bare stage-box">
    <div class="ornate-body stage-scroll">
      <header class="start-head">
        <h1><?= esc(lang('Game.soundCheckTitle')) ?></h1>
        <p><?= esc(lang('Game.soundCheckLead')) ?></p>
      </header>
      <span class="ornate-divider" aria-hidden="true"></span>

      <?php if ($help): ?>
        <p class="sound-check-help" role="status"><?= esc(lang('Game.soundCheckHelp')) ?></p>
      <?php endif ?>

      <button type="button" class="start-card is-primary sound-check-play" data-sound-play>
        <span class="start-icon" aria-hidden="true"><?= round_icon('play', 'start-round', 'play') ?></span>
        <span class="start-text">
          <span class="sound-check-play-title"><?= esc(lang('Game.soundCheckPlay')) ?></span>
          <span class="sound-check-play-text"><?= esc(lang('Game.soundCheckPlayText')) ?></span>
        </span>
      </button>

      <form class="sound-check-answer" method="post" action="<?= base_url('cek-suara') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="plays" value="0" data-sound-plays>
        <p class="sound-check-question" id="sound-check-question"><?= esc(lang('Game.soundCheckQuestion')) ?></p>
        <div class="sound-check-choices" role="group" aria-labelledby="sound-check-question">
          <button type="submit" name="heard" value="ya" class="btn btn-primary btn-lg" data-sound-answer><?= esc(lang('Game.soundCheckYes')) ?></button>
          <button type="submit" name="heard" value="tidak" class="btn btn-quiet btn-lg" data-sound-answer><?= esc(lang('Game.soundCheckNo')) ?></button>
        </div>
        <p class="sound-check-first" data-sound-first hidden><?= esc(lang('Game.soundCheckFirst')) ?></p>
        <?php if ($help): ?>
          <button type="submit" name="lanjut" value="1" class="btn btn-quiet sound-check-skip"><?= esc(lang('Game.soundCheckSkip')) ?></button>
        <?php endif ?>
      </form>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
