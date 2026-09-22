<?php
/**
 * 12. Selesai — `/selesai/{attemptId}` → ChallengeController::finished
 *
 * Orb cahaya, bintang 0–3, tiga statistik (Waktu, Tepat sejak awal, Skor).
 * Bila wilayah tuntas: panel Mbah Kedu bangga. Bila semua node tuntas: tombol
 * Balai Refleksi. Konfeti CSS mati otomatis pada prefers-reduced-motion.
 *
 * @var App\Entities\ChallengeAttempt    $attempt
 * @var App\Entities\ChallengeNode|null  $node
 * @var App\Entities\Level|null          $level
 * @var array<string, mixed>             $levelScore
 * @var bool                             $levelCompleted
 * @var bool                             $allCompleted
 * @var array<string, mixed>             $progress
 * @var array<string, mixed>|null        $nextRegion baris levelOverview wilayah sesudahnya
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

$checks = (int) ($attempt->check_count ?? 0);
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc(lang('Game.finishedTitle')) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= $level !== null ? media_first($level->background_media_id) : '' ?><?= $this->endSection() ?>
<?= $this->section('bodyClass') ?>is-celebrate<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="confetti" aria-hidden="true">
  <?php for ($i = 0; $i < 24; $i++): ?><i style="--i: <?= $i ?>"></i><?php endfor ?>
</div>

<section class="screen screen-medium finished" data-screen="finished" data-attempt="<?= esc($attempt->id, 'attr') ?>">
  <div class="finished-layout">
    <?= component('character', ['character' => 'jaka', 'pose' => 'happy', 'class' => 'finished-jaka']) ?>

    <article class="panel-carved finished-card">
      <div class="orb" aria-hidden="true"><span class="orb-core"><?= icon('lantern') ?></span></div>
      <?php if ($node !== null): ?>
        <span class="eyebrow"><?= esc($region) ?> · <?= esc($node->text('title', $locale)) ?></span>
      <?php endif ?>
      <h1><?= esc(lang('Game.finishedTitle')) ?></h1>

      <?= component('star-rating', ['stars' => (int) $attempt->stars, 'size' => 'lg']) ?>

      <div class="stat-grid finished-stats">
        <?= component('stat-tile', ['label' => lang('Game.timeSpent'), 'value' => ms_to_human((int) $attempt->duration_ms), 'icon' => 'clock']) ?>
        <?= component('stat-tile', ['label' => lang('Game.accuracyFirst'), 'value' => fmt_pct($attempt->first_pass_accuracy, false, 0), 'icon' => 'target']) ?>
        <?= component('stat-tile', ['label' => lang('Game.score'), 'value' => fmt_num($attempt->score, 0, $locale), 'icon' => 'star']) ?>
      </div>

      <?php if ($checks > 1): ?>
        <p class="finished-note"><?= icon('info') ?> <?= esc(lang('Game.multiCheckNote', [$checks])) ?></p>
      <?php endif ?>

      <p class="finished-shards">
        <?= icon('lantern') ?> <?= esc(lang('Game.shardEarned', [$progress['shards'], $progress['shards_total']])) ?>
      </p>

      <?php if ($levelCompleted && $level !== null): ?>
        <div class="finished-kedu">
          <?= component('character', ['character' => 'mbah_kedu', 'pose' => 'happy']) ?>
          <?= component('narration', [
              'speaker' => 'mbah_kedu',
              'text'    => lang('Game.regionDone', [$region]) . ' '
                  . ($allCompleted ? lang('Game.allShardsDone', [$progress['shards_total']]) : lang('Game.nextRegionOpen')),
          ]) ?>
        </div>
      <?php endif ?>

      <div class="finished-actions">
        <?php if ($allCompleted): ?>
          <a class="btn btn-primary btn-xl" href="<?= base_url('refleksi') ?>"><?= icon('sparkle') ?> <?= esc(lang('Game.reflection')) ?></a>
        <?php elseif ($next !== null): ?>
          <a class="btn btn-primary btn-lg" href="<?= base_url('misi/' . $level->code . '/' . $next->sequence) ?>"><?= esc(lang('Game.next')) ?> <?= icon('right') ?></a>
        <?php elseif ($levelCompleted && $nextRegion !== null && $nextRegion['status'] !== 'locked'): ?>
          <?php // `entry`: wilayah yang baru terbuka → dialog pembukanya, selain itu peta wilayah ?>
          <a class="btn btn-primary btn-lg" href="<?= base_url($nextRegion['entry']) ?>"><?= esc(lang('Game.goToRegion', [$nextRegion['name']])) ?> <?= icon('right') ?></a>
        <?php elseif ($levelCompleted): ?>
          <a class="btn btn-primary btn-lg" href="<?= base_url('peta') ?>"><?= icon('map') ?> <?= esc(lang('Game.mapKedu')) ?></a>
        <?php endif ?>
      </div>
    </article>
  </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('nav') ?>
<?php
$nav = [];
if ($level !== null) {
    $nav[] = ['label' => lang('Game.backToRegion'), 'href' => base_url('wilayah/' . $level->code), 'style' => 'quiet', 'arrow' => 'left'];
}
$nav[] = ['label' => lang('Game.mapKedu'), 'href' => base_url('peta'), 'style' => 'quiet', 'icon' => 'map'];
?>
<?= component('nav-bar', ['nav' => $nav]) ?>
<?= $this->endSection() ?>
