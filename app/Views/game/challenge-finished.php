<?php
/**
 * 12. Selesai — `/selesai/{attemptId}` → ChallengeController::finished
 *
 * Result screen redesign of 18 October 2026 (mockup 01 in
 * gelita-katalog/mockup/gambar-mockup/tantangan-selesai-temanggung): one
 * shared layout for every region and challenge kind, on the redesigned HUD
 * (section hudStyle `menu`). Temanggung and "Susun Gambar" in the mockup are
 * only sample data.
 *
 * Panel (skin `ui.result-panel`, plain navy and gold while empty):
 * - challenge kind, title, stars 0–3 (`star_src()`: `ui.result-star` or the shipped star, grey and faded when empty);
 * - shard medal (`ui.result-medal`) with the shard count and a segment bar;
 * - two cards: Waktu and Percobaan (Ketukan salah for Cari Objek);
 * - finish reason badge (Semua benar / Disimpan apa adanya) and one message:
 *   tries exhausted, several checks, or right on the first try;
 * - buttons: Peta {wilayah}, Detail hasil (first-try result, closed at
 *   first), and the main button, whose target is unchanged: "Lanjut" to
 *   /tuntas/{code} when this attempt completes the region, Balai Refleksi when
 *   every challenge is done, Berikutnya to the next challenge, "Lanjut ke
 *   {wilayah}" with the region curtain, Peta Kedu, or else the region map.
 * When the region is complete, a card over the panel keeps Mbah Kedu's words
 * and, when this attempt opened it, the Pustaka highlight (owner decision
 * 18 October 2026). With JavaScript it is a modal over a blurred, dimmed
 * stage and can be closed; without it the card stays and the buttons stay free. CSS confetti stops on
 * prefers-reduced-motion.
 *
 * @var App\Entities\ChallengeAttempt    $attempt
 * @var App\Entities\ChallengeNode|null  $node
 * @var App\Entities\Level|null          $level
 * @var array<string, mixed>             $levelSummary
 * @var bool                             $levelCompleted
 * @var bool|null                        $closesRegion    this attempt completed its region
 * @var bool                             $libraryUnlocked this attempt opened the region's Pustaka
 * @var bool                             $allCompleted
 * @var array<string, mixed>             $progress
 * @var array<string, mixed>|null        $nextRegion levelOverview row of the region after it (+ `curtain`)
 * @var int|null                         $wrongTaps  Cari Objek: number of wrong taps
 * @var string                           $locale
 */
$region = $level?->text('name', $locale) ?? '';
$next   = null;

if ($level !== null && $node !== null && ! $levelCompleted) {
    foreach (service('contentRepository')->nodesForLevel($level->id) as $candidate) {
        if ($candidate->sequence === $node->sequence + 1) {
            $next = $candidate;
            break;
        }
    }
}

$checks       = (int) ($attempt->check_count ?? 0);
$maxTries     = (int) ($attempt->max_tries ?? config('Gelita')->maxTries);
$stars        = max(0, min(3, (int) $attempt->stars));
$shards       = (int) ($progress['shards'] ?? 0);
$shardsTotal  = (int) ($progress['shards_total'] ?? 0);
$solved       = $attempt->completion_reason === 'solved';
$exhausted    = $attempt->completion_reason === 'tries_exhausted';
$closesRegion = ! empty($closesRegion);
// "Lanjut ke {wilayah berikutnya}" (replaying a challenge in a completed region)
$toNext = ! $closesRegion && ! $allCompleted && $next === null && $levelCompleted
    && $nextRegion !== null && $nextRegion['status'] !== 'locked';

$message = match (true) {
    $exhausted              => lang('Game.exhaustedNote'),
    $checks > 1             => lang('Game.multiCheckNote', [$checks]),
    $solved && $checks === 1 => lang('Game.resultFirstTry'),
    default                 => null,
};

$starSrc  = star_src();
$medalSrc = media_key_src('ui.result-medal');
$bgResult = $level !== null ? media_key_src('bg.result.' . $level->code) : null;
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc(lang('Game.finishedTitle')) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= $bgResult ?? ($level !== null ? media_first($level->background_media_id) : '') ?><?= $this->endSection() ?>
<?= $this->section('bodyClass') ?>is-celebrate<?= $this->endSection() ?>
<?= $this->section('hudStyle') ?>menu<?= $this->endSection() ?>
<?= $this->section('hudShards') ?>off<?= $this->endSection() ?>

<?php if ($toNext): ?>
<?= $this->section('overlay') ?>
<template id="tpl-curtain-region"><?= component('curtain', ['kind' => 'region']) ?></template>
<?= $this->endSection() ?>
<?php endif ?>

<?= $this->section('content') ?>
<div class="confetti" aria-hidden="true">
  <?php for ($i = 0; $i < 24; $i++): ?><i style="--i: <?= $i ?>"></i><?php endfor ?>
</div>

<?php
// Next screen: the next region when this attempt completed the region, else the region map
$nextAssets = ! empty($levelCompleted) && isset($nextRegion['curtain'])
    ? $nextRegion['curtain']['preload']
    : [$level !== null && media_exists($level->map_media_id) ? media_src($level->map_media_id) : null];
?>
<section class="finished" data-screen="finished" data-attempt="<?= esc($attempt->id, 'attr') ?>"<?= $levelCompleted ? ' data-region-done' : '' ?><?= next_assets_attr($nextAssets) ?>>
  <article class="result-card stage-box" aria-labelledby="result-title"<?= skin_style_attr([
      'skin-panel'     => 'ui.result-panel',
      'skin-primary'   => 'ui.result-button-primary',
      'skin-secondary' => 'ui.result-button-secondary',
      'skin-detail'    => 'ui.result-button-detail',
  ]) ?>>
    <header class="result-heading">
      <?php if ($node !== null): ?>
        <p class="result-kind"><?= esc(lang_or('Game.engine_' . $node->engine_type, $node->text('title', $locale))) ?></p>
      <?php endif ?>
      <h1 id="result-title"><?= esc(lang('Game.finishedTitle')) ?></h1>
      <p class="result-stars" role="img" aria-label="<?= esc(lang('Game.resultStarsOf', [$stars, 3]), 'attr') ?>">
        <?php for ($i = 1; $i <= 3; $i++): ?>
          <img class="result-star<?= $i <= $stars ? ' is-on' : ' is-off' ?>" src="<?= esc($starSrc, 'attr') ?>" alt="" draggable="false">
        <?php endfor ?>
      </p>
    </header>

    <div class="result-shards">
      <div class="result-medal<?= $medalSrc !== null ? ' has-art' : '' ?>" aria-hidden="true">
        <?php if ($medalSrc !== null): ?>
          <img src="<?= esc($medalSrc, 'attr') ?>" alt="" draggable="false">
        <?php else: ?>
          <span class="orb"><span class="orb-core"><?= icon('lantern') ?></span></span>
        <?php endif ?>
      </div>
      <div class="result-shards-text">
        <h2><?= esc(lang('Game.resultShards')) ?></h2>
        <p class="result-shards-count num" aria-label="<?= esc(lang('Game.shardEarned', [$shards, $shardsTotal]), 'attr') ?>"><?= $shards ?> / <?= $shardsTotal ?></p>
        <?php if ($shardsTotal > 0): ?>
          <span class="result-shards-bar" aria-hidden="true">
            <?php for ($i = 1; $i <= $shardsTotal; $i++): ?><i<?= $i <= $shards ? ' class="is-on"' : '' ?>></i><?php endfor ?>
          </span>
        <?php endif ?>
      </div>
    </div>

    <div class="result-summary">
      <dl class="result-stats">
        <div class="result-stat">
          <dt><?= icon('clock') ?> <?= esc(lang('Game.timeSpent')) ?></dt>
          <dd class="num"><?= esc(ms_to_human((int) $attempt->duration_ms)) ?></dd>
        </div>
        <?php if (($wrongTaps ?? null) !== null): ?>
          <div class="result-stat">
            <dt><?= icon('cross') ?> <?= esc(lang('Game.wrongTaps')) ?></dt>
            <dd class="num"><?= (int) $wrongTaps ?></dd>
          </div>
        <?php else: ?>
          <div class="result-stat">
            <dt><?= icon('replay') ?> <?= esc(lang('Game.resultTries')) ?></dt>
            <dd class="num is-words"><?= esc(lang('Game.triesOf', [$checks, $maxTries])) ?></dd>
          </div>
        <?php endif ?>
      </dl>

      <?php if ($attempt->completion_reason !== null): ?>
        <p class="result-badge <?= $solved ? 'is-solved' : 'is-saved' ?>">
          <span class="result-badge-mark" aria-hidden="true"><?= icon($solved ? 'check' : 'info') ?></span>
          <?= esc(lang($solved ? 'Game.completionSolved' : 'Game.completionExhausted')) ?>
        </p>
      <?php endif ?>

      <?php if ($message !== null): ?>
        <p class="result-message"><?= esc($message) ?></p>
      <?php endif ?>
    </div>

    <div class="result-actions">
      <?php if ($level !== null): ?>
        <a class="result-btn is-secondary" href="<?= base_url('wilayah/' . $level->code) ?>"><?= icon('left') ?> <span><?= esc(lang('Game.resultRegionMap', [$region])) ?></span></a>
      <?php endif ?>

      <details class="result-detail" data-popover>
        <summary class="result-btn is-detail"><span><?= esc(lang('Game.resultDetail')) ?></span> <?= icon('down') ?></summary>
        <div class="result-sheet result-detail-sheet" role="group" aria-labelledby="result-detail-title">
          <div class="result-sheet-head">
            <h2 id="result-detail-title"><?= esc(lang('Game.resultDetail')) ?></h2>
            <button type="button" class="hud-sheet-close" data-popover-close hidden
                    aria-label="<?= esc(lang('Game.close'), 'attr') ?>"><?= icon('cross') ?></button>
          </div>
          <dl class="result-detail-list">
            <div>
              <dt><?= esc(lang('Game.firstPass')) ?></dt>
              <dd><?= esc(first_pass_text($attempt, $locale, $node?->engine_type)) ?></dd>
            </div>
          </dl>
        </div>
      </details>

      <?php if ($closesRegion && $level !== null): ?>
        <?php // Region just completed: the "Serpihan {wilayah} kembali!" scene first ?>
        <a class="result-btn is-primary" href="<?= base_url('tuntas/' . $level->code) ?>"><span><?= esc(lang('Game.continue')) ?></span> <?= icon('right') ?></a>
      <?php elseif ($allCompleted): ?>
        <a class="result-btn is-primary" href="<?= base_url('refleksi') ?>"><?= icon('sparkle') ?> <span><?= esc(lang('Game.reflection')) ?></span></a>
      <?php elseif ($next !== null): ?>
        <a class="result-btn is-primary" href="<?= base_url('misi/' . $level->code . '/' . $next->sequence) ?>"><span><?= esc(lang('Game.next')) ?></span> <?= icon('right') ?></a>
      <?php elseif ($toNext): ?>
        <?php // `entry`: a region just opened → its opening dialogue, else its region map ?>
        <a class="result-btn is-primary" href="<?= base_url($nextRegion['entry']) ?>"<?= region_curtain_attrs($nextRegion['curtain'] ?? null) ?>><span><?= esc(lang('Game.goToRegion', [$nextRegion['name']])) ?></span> <?= icon('right') ?></a>
      <?php elseif ($levelCompleted): ?>
        <a class="result-btn is-primary" href="<?= base_url('peta') ?>"><?= icon('map') ?> <span><?= esc(lang('Game.mapKedu')) ?></span></a>
      <?php elseif ($level !== null): ?>
        <?php // Last challenge done, an earlier one still open: pick it on the region map ?>
        <a class="result-btn is-primary" href="<?= base_url('wilayah/' . $level->code) ?>"><span><?= esc(lang('Game.next')) ?></span> <?= icon('right') ?></a>
      <?php endif ?>
    </div>
  </article>

  <?php if ($levelCompleted && $level !== null): ?>
    <?php // Blurred, dimmed layer under the card (owner, 18 October 2026); shown only with JavaScript, which can close it ?>
    <div class="result-scrim" aria-hidden="true"></div>
    <aside class="result-sheet result-kedu stage-box" role="dialog" aria-labelledby="result-kedu-title">
      <button type="button" class="hud-sheet-close" data-dismiss hidden
              aria-label="<?= esc(lang('Game.close'), 'attr') ?>"><?= icon('cross') ?></button>
      <h2 id="result-kedu-title" class="visually-hidden"><?= esc(lang('Game.regionDone', [$region])) ?></h2>
      <div class="finished-kedu">
        <?= component('character', ['character' => 'mbah_kedu', 'pose' => 'smile']) ?>
        <?= component('narration', [
            'speaker' => 'mbah_kedu',
            'text'    => lang('Game.regionDone', [$region]) . ' '
                . ($allCompleted ? lang('Game.allShardsDone', [$progress['shards_total']]) : lang('Game.nextRegionOpen')),
        ]) ?>
      </div>
      <?php if ($libraryUnlocked): ?>
        <div class="library-unlocked" role="status">
          <span class="library-unlocked-icon" aria-hidden="true"><?= icon('book') ?></span>
          <div>
            <h3><?= esc(lang('Game.libraryUnlockedTitle', [$region])) ?></h3>
            <p><?= esc(lang('Game.libraryUnlockedText', [$region])) ?></p>
          </div>
          <a class="btn btn-ghost" href="<?= base_url('pustaka/' . $level->code) ?>"><?= icon('book') ?> <?= esc(lang('Game.libraryReadNow')) ?></a>
        </div>
      <?php endif ?>
    </aside>
  <?php endif ?>
</section>
<?= $this->endSection() ?>
