<?php
/**
 * 13. Hasil Node — `/hasil/{code}/{seq}` → ChallengeController::result
 *
 * Statistik terbaik + tabel seluruh percobaan selesai. Kembali (to the region
 * map) and Ulangi sit under the heading; the page has no nav bar. Mengulang tidak
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
<?php /* Next screen: the region map */ ?>
<?php
// Redesign (owner, 21 October 2026): no panel box, Kembali and Ulangi right under the
// heading (Kembali left, Ulangi right), the best play as a slim centred band, no nav bar
?>
<section class="result result-list" data-screen="result"<?= next_assets_attr([media_exists($level->map_media_id) ? media_src($level->map_media_id) : null]) ?>>
  <div class="result-panel stage-box">
  <header class="screen-head result-head">
    <span class="eyebrow"><?= esc($region) ?> · <?= esc(lang('Game.challengeOf', [$sequence, $totalNodes])) ?></span>
    <h1><?= esc($node->text('title', $locale)) ?></h1>
  </header>

  <nav class="result-list-actions" aria-label="<?= esc(lang('Game.continue'), 'attr') ?>">
    <a class="btn btn-quiet" href="<?= base_url('wilayah/' . $level->code) ?>"><?= icon('left') ?><span><?= esc($region) ?></span></a>
    <?php // Ulangi on the owner's curved-arrow plate (21 October 2026) ?>
    <?php // Part 7 (33 §7.4): no Ulangi while research mode keeps the retake closed ?>
    <?php if (empty($retakeLocked)): ?>
    <a <?= plate_attrs('back') ?> href="<?= base_url('misi/' . $level->code . '/' . $sequence) ?>"><span><?= esc(lang('Game.retry')) ?></span></a>
    <?php endif ?>
  </nav>
  <?php if (! empty($retakeLocked)): ?>
    <p class="result-note result-retake-later" role="status"><?= icon('info') ?> <?= esc(lang('Game.retakeLater')) ?></p>
  <?php endif ?>

  <?php if ($best !== null): ?>
    <section class="result-best" aria-labelledby="result-best-title">
      <div class="result-best-crown">
        <h2 class="result-best-title" id="result-best-title"><?= esc(lang('Game.bestResult')) ?></h2>
        <?= component('star-rating', ['stars' => (int) $best->stars, 'size' => 'lg']) ?>
      </div>
      <dl class="result-best-stats">
        <div><dt><?= esc(lang('Game.firstPass')) ?></dt><dd><?= esc(first_pass_text($best, $locale, $node->engine_type)) ?></dd></div>
        <?php if (empty($researchMode)): // Part 7 (33 §7.1): no time in research mode ?>
        <div><dt><?= esc(lang('Game.timeSpent')) ?></dt><dd class="num"><?= esc(ms_to_human((int) $best->duration_ms)) ?></dd></div>
        <?php endif ?>
        <div><dt><?= esc(lang('Game.checks')) ?></dt><dd class="num"><?= esc((string) (int) $best->check_count) ?></dd></div>
      </dl>
    </section>
  <?php endif ?>

  <?php if ($attempts === []): ?>
    <div class="empty-state"><?= icon('info') ?><p><?= esc(lang('Game.noAttempts')) ?></p></div>
  <?php else: ?>
    <div class="table-wrap stage-scroll">
      <table class="data-table result-table">
        <caption class="visually-hidden"><?= esc(lang('Game.resultTitle')) ?></caption>
        <thead>
          <tr>
            <th scope="col"><?= esc(lang('Game.attempt')) ?></th>
            <th scope="col"><?= esc(lang('Game.startedAt')) ?></th>
            <?php if (empty($researchMode)): ?><th scope="col" class="is-num"><?= esc(lang('Game.duration')) ?></th><?php endif ?>
            <th scope="col" class="is-num"><?= esc(lang('Game.checks')) ?></th>
            <th scope="col"><?= esc(lang('Game.firstPass')) ?></th>
            <th scope="col"><?= esc(lang('Game.stars')) ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($attempts as $attempt): ?>
            <?php $isBest = $best !== null && $best->id === $attempt->id; ?>
            <tr<?= $isBest ? ' class="is-best"' : '' ?>>
              <th scope="row">#<?= esc($attempt->attempt_no) ?><?php if ($isBest): ?> <span class="badge is-completed"><?= esc(lang('Game.best')) ?></span><?php endif ?></th>
              <td><?= esc(fmt_date($attempt->started_at, false, $locale)) ?></td>
              <?php if (empty($researchMode)): ?><td class="is-num"><?= esc(ms_to_human((int) $attempt->duration_ms)) ?></td><?php endif ?>
              <td class="is-num"><?= esc((int) $attempt->check_count) ?></td>
              <td><?= $attempt->isCompleted() ? esc(first_pass_text($attempt, $locale, $node->engine_type)) : '—' ?></td>
              <td><?= stars_html((int) $attempt->stars) ?></td>
            </tr>
          <?php endforeach ?>
        </tbody>
      </table>
    </div>
  <?php endif ?>

  <p class="result-note muted"><?= icon('info') ?> <?= esc(lang('Game.retryNote')) ?></p>
  </div>
</section>
<?= $this->endSection() ?>
