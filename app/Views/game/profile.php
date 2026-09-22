<?php
/**
 * 15. Profil — `/profil` → ProfileController::index
 *
 * Kode peserta tampil kecil dengan keterangan "kode penelitianmu": kode itu
 * untuk penelitian, bukan untuk masuk.
 *
 * @var array<string, mixed>                 $participant  toSafeArray()
 * @var array<string, mixed>                 $progress
 * @var list<array<string, mixed>>           $levels
 * @var list<App\Entities\ChallengeAttempt>  $attempts
 * @var array<int, string>                   $nodes
 * @var int                                  $durationMs
 * @var float                                $meanFirstPass
 * @var bool                                 $canReflect
 * @var string                               $locale
 */
$name     = (string) ($participant['display_name'] ?: $participant['username']);
$gender   = (string) ($participant['gender'] ?? '');
$class    = (string) ($participant['class_level'] ?? '');
$classTxt = $class === '' ? '—' : ($class === 'lainnya' ? lang('Game.classOther')
    : (ctype_digit($class) ? lang((int) $class <= 6 ? 'Game.classSd' : 'Game.classSmp', [$class]) : $class));
$contentLevels = [];
foreach (service('contentRepository')->levels() as $entity) {
    $contentLevels[$entity->code] = $entity;
}
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc(lang('Game.profileTitle')) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= media_key_src('bg.map') ?? '' ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="screen screen-medium profile" data-screen="profile">
  <div class="panel profile-card">
    <span class="profile-avatar" aria-hidden="true"><?= esc(mb_strtoupper(mb_substr($name, 0, 1))) ?></span>
    <div class="profile-id">
      <h1><?= esc($name) ?></h1>
      <p class="profile-username">@<?= esc($participant['username']) ?></p>
      <p class="profile-code"><small><span class="num"><?= esc($participant['participant_code']) ?></span> · <?= esc(lang('Game.researchCode')) ?></small></p>
    </div>
    <a class="btn btn-ghost" href="<?= base_url('ganti-sandi') ?>"><?= icon('key') ?> <?= esc(lang('Game.changePassword')) ?></a>
  </div>

  <div class="stat-grid profile-stats">
    <?= component('stat-tile', ['label' => lang('Game.age'), 'value' => $participant['age'] === null ? '—' : $participant['age'] . ' ' . lang('Game.ageUnit')]) ?>
    <?= component('stat-tile', ['label' => lang('Game.gender'), 'value' => $gender === '' ? '—' : lang_or('Game.gender_' . $gender, $gender)]) ?>
    <?= component('stat-tile', ['label' => lang('Game.classLevel'), 'value' => $classTxt]) ?>
    <?= component('stat-tile', ['label' => lang('Game.school'), 'value' => $participant['school_name'] ?? '—']) ?>
    <?= component('stat-tile', ['label' => lang('Game.shards'), 'value' => $progress['shards'] . '/' . $progress['shards_total'], 'icon' => 'lantern']) ?>
    <?= component('stat-tile', ['label' => lang('Game.meanFirstPass'), 'value' => fmt_pct($meanFirstPass, false, 0), 'icon' => 'target']) ?>
    <?= component('stat-tile', ['label' => lang('Game.totalTime'), 'value' => ms_to_human($durationMs), 'icon' => 'clock']) ?>
    <?= component('stat-tile', ['label' => lang('Game.totalScore'), 'value' => fmt_num($progress['total_score'], 0, $locale), 'icon' => 'star']) ?>
    <?= component('stat-tile', ['label' => lang('Game.challengesDone'), 'value' => $progress['completed_nodes'] . '/' . $progress['shards_total'], 'icon' => 'check']) ?>
  </div>

  <h2><?= esc(lang('Game.badges')) ?></h2>
  <ul class="badge-shelf">
    <?php foreach ($levels as $level): ?>
      <?php
      $done  = $level['status'] === 'completed';
      $badge = isset($contentLevels[$level['code']]) && media_exists($contentLevels[$level['code']]->badge_media_id)
          ? media_src($contentLevels[$level['code']]->badge_media_id) : null;
      ?>
      <li class="region-badge<?= $done ? ' is-earned' : '' ?>">
        <span class="region-badge-art" aria-hidden="true">
          <?php if ($badge !== null && $done): ?>
            <img src="<?= esc($badge, 'attr') ?>" alt="">
          <?php else: ?>
            <?= icon($done ? 'star' : 'lock') ?>
          <?php endif ?>
        </span>
        <span class="region-badge-name"><?= esc($level['name']) ?></span>
        <small><?= $done ? stars_html((int) $level['stars']) : esc(lang('Game.badgeLocked')) ?></small>
      </li>
    <?php endforeach ?>
  </ul>

  <h2><?= esc(lang('Game.history')) ?></h2>
  <?php if ($attempts === []): ?>
    <div class="empty-state"><?= icon('info') ?><p><?= esc(lang('Game.noHistory')) ?></p></div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="data-table">
        <caption class="visually-hidden"><?= esc(lang('Game.history')) ?></caption>
        <thead>
          <tr>
            <th scope="col"><?= esc(lang('Game.challenge')) ?></th>
            <th scope="col"><?= esc(lang('Game.attempt')) ?></th>
            <th scope="col"><?= esc(lang('Game.status')) ?></th>
            <th scope="col" class="is-num"><?= esc(lang('Game.score')) ?></th>
            <th scope="col"><?= esc(lang('Game.stars')) ?></th>
            <th scope="col"><?= esc(lang('Game.startedAt')) ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($attempts as $attempt): ?>
            <tr>
              <td><?= esc($nodes[$attempt->challenge_node_id] ?? ('#' . $attempt->challenge_node_id)) ?></td>
              <td class="num">#<?= esc($attempt->attempt_no) ?></td>
              <td><span class="badge is-<?= esc($attempt->status, 'attr') ?>"><?= esc(lang_or('Game.status_' . $attempt->status, (string) $attempt->status)) ?></span></td>
              <td class="is-num"><?= $attempt->isCompleted() ? esc(fmt_num($attempt->score, 0, $locale)) : '—' ?></td>
              <td><?= $attempt->isCompleted() ? stars_html((int) $attempt->stars) : '—' ?></td>
              <td><?= esc(fmt_date($attempt->started_at, false, $locale)) ?></td>
            </tr>
          <?php endforeach ?>
        </tbody>
      </table>
    </div>
  <?php endif ?>
</section>
<?= $this->endSection() ?>

<?= $this->section('nav') ?>
<?php
$nav = [['label' => lang('Game.mapKedu'), 'href' => base_url('peta'), 'style' => 'quiet', 'arrow' => 'left']];
if ($canReflect) {
    $nav[] = ['label' => lang('Game.reflection'), 'href' => base_url('refleksi'), 'style' => 'primary', 'arrow' => 'right'];
}
?>
<?= component('nav-bar', ['nav' => $nav]) ?>
<?= $this->endSection() ?>
