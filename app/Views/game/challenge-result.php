<?php
/**
 * 13. Hasil Node — `/hasil/{code}/{seq}` → ChallengeController::result
 *
 * Statistik terbaik + tabel seluruh percobaan selesai. Mengulang tidak
 * menghapus catatan lama: setiap percobaan tetap tersimpan sebagai data
 * penelitian.
 *
 * @var App\Entities\Level                   $level
 * @var App\Entities\ChallengeNode           $node
 * @var int                                  $sequence
 * @var list<App\Entities\ChallengeAttempt>  $attempts
 * @var App\Entities\ChallengeAttempt|null   $best
 * @var string                               $locale
 */
$region     = $level->text('name', $locale);
$totalNodes = count(service('contentRepository')->nodesForLevel($level->id));
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc(lang('Game.resultTitle')) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= media_first($node->background_media_id, $level->background_media_id) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="screen screen-medium result" data-screen="result">
  <header class="screen-head">
    <span class="eyebrow"><?= esc($region) ?> · <?= esc(lang('Game.challengeOf', [$sequence, $totalNodes])) ?></span>
    <h1><?= esc($node->text('title', $locale)) ?></h1>
  </header>

  <?php if ($best !== null): ?>
    <div class="panel result-best">
      <div class="result-best-head">
        <span class="badge is-completed"><?= icon('star') ?> <?= esc(lang('Game.best')) ?></span>
        <?= component('star-rating', ['stars' => (int) $best->stars, 'size' => 'lg']) ?>
      </div>
      <div class="stat-grid">
        <?= component('stat-tile', ['label' => lang('Game.score'), 'value' => fmt_num($best->score, 0, $locale), 'icon' => 'star']) ?>
        <?= component('stat-tile', ['label' => lang('Game.accuracyFirst'), 'value' => fmt_pct($best->first_pass_accuracy, false, 0), 'icon' => 'target']) ?>
        <?= component('stat-tile', ['label' => lang('Game.timeSpent'), 'value' => ms_to_human((int) $best->duration_ms), 'icon' => 'clock']) ?>
        <?= component('stat-tile', ['label' => lang('Game.checks'), 'value' => (string) (int) $best->check_count, 'icon' => 'check']) ?>
      </div>
    </div>
  <?php endif ?>

  <?php if ($attempts === []): ?>
    <div class="empty-state"><?= icon('info') ?><p><?= esc(lang('Game.noAttempts')) ?></p></div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="data-table result-table">
        <caption class="visually-hidden"><?= esc(lang('Game.resultTitle')) ?></caption>
        <thead>
          <tr>
            <th scope="col"><?= esc(lang('Game.attempt')) ?></th>
            <th scope="col"><?= esc(lang('Game.startedAt')) ?></th>
            <th scope="col" class="is-num"><?= esc(lang('Game.duration')) ?></th>
            <th scope="col" class="is-num"><?= esc(lang('Game.accuracyFirst')) ?></th>
            <th scope="col" class="is-num"><?= esc(lang('Game.checks')) ?></th>
            <th scope="col" class="is-num"><?= esc(lang('Game.score')) ?></th>
            <th scope="col"><?= esc(lang('Game.stars')) ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($attempts as $attempt): ?>
            <?php $isBest = $best !== null && $best->id === $attempt->id; ?>
            <tr<?= $isBest ? ' class="is-best"' : '' ?>>
              <th scope="row">#<?= esc($attempt->attempt_no) ?><?php if ($isBest): ?> <span class="badge is-completed"><?= esc(lang('Game.best')) ?></span><?php endif ?></th>
              <td><?= esc(fmt_date($attempt->started_at, false, $locale)) ?></td>
              <td class="is-num"><?= esc(ms_to_human((int) $attempt->duration_ms)) ?></td>
              <td class="is-num"><?= esc(fmt_pct($attempt->first_pass_accuracy, false, 0)) ?></td>
              <td class="is-num"><?= esc((int) $attempt->check_count) ?></td>
              <td class="is-num"><?= esc(fmt_num($attempt->score, 0, $locale)) ?></td>
              <td><?= stars_html((int) $attempt->stars) ?></td>
            </tr>
          <?php endforeach ?>
        </tbody>
      </table>
    </div>
  <?php endif ?>

  <p class="result-note muted"><?= icon('info') ?> <?= esc(lang('Game.retryNote')) ?></p>

  <div class="btn-row btn-row-center">
    <a class="btn btn-primary btn-lg" href="<?= base_url('misi/' . $level->code . '/' . $sequence) ?>"><?= icon('replay') ?> <?= esc(lang('Game.retry')) ?></a>
  </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('nav') ?>
<?= component('nav-bar', ['nav' => [
    ['label' => $region, 'href' => base_url('wilayah/' . $level->code), 'style' => 'quiet', 'arrow' => 'left'],
]]) ?>
<?= $this->endSection() ?>
