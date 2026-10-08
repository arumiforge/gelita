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
 * @var bool                                 $canReflect
 * @var string                               $locale
 */
$name     = (string) ($participant['display_name'] ?: $participant['username']);
$gender   = (string) ($participant['gender'] ?? '');
$classTxt = class_label((string) ($participant['class_level'] ?? ''), $locale);
$contentLevels = [];
foreach (service('contentRepository')->levels() as $entity) {
    $contentLevels[$entity->code] = $entity;
}
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc(lang('Game.profileTitle')) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= media_key_src('bg.map') ?? '' ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="profile" data-screen="profile">
  <?php // Kiri 32, 72, 400 × 576: identitas + empat ubin; kanan 456, 72, 792 × 576: lencana + riwayat (Dok 3 §4) ?>
  <aside class="panel profile-side stage-box stage-scroll">
  <div class="profile-card">
    <span class="profile-avatar" aria-hidden="true"><?= esc(mb_strtoupper(mb_substr($name, 0, 1))) ?></span>
    <div class="profile-id">
      <h1><?= esc($name) ?></h1>
      <p class="profile-username">@<?= esc($participant['username']) ?></p>
      <p class="profile-code"><small><span class="num"><?= esc($participant['participant_code']) ?></span> · <?= esc(lang('Game.researchCode')) ?></small></p>
    </div>
  </div>

  <dl class="profile-facts">
    <div><dt><?= esc(lang('Game.age')) ?></dt><dd><?= esc($participant['age'] === null ? '—' : $participant['age'] . ' ' . lang('Game.ageUnit')) ?></dd></div>
    <div><dt><?= esc(lang('Game.gender')) ?></dt><dd><?= esc($gender === '' ? '—' : lang_or('Game.gender_' . $gender, $gender)) ?></dd></div>
    <div><dt><?= esc(lang('Game.classLevel')) ?></dt><dd><?= esc($classTxt) ?></dd></div>
    <div><dt><?= esc(lang('Game.school')) ?></dt><dd><?= esc($participant['school_name'] ?? '—') ?></dd></div>
  </dl>

  <div class="stat-grid profile-stats">
    <?= component('stat-tile', ['label' => lang('Game.shards'), 'value' => $progress['shards'] . '/' . $progress['shards_total'], 'icon' => 'lantern']) ?>
    <?= component('stat-tile', ['label' => lang('Game.challengesDone'), 'value' => $progress['completed_nodes'] . '/' . $progress['shards_total'], 'icon' => 'check']) ?>
    <?= component('stat-tile', ['label' => lang('Game.stars'), 'value' => (string) $progress['total_stars'], 'icon' => 'star']) ?>
    <?= component('stat-tile', ['label' => lang('Game.totalTime'), 'value' => ms_to_human($durationMs), 'icon' => 'clock']) ?>
  </div>

  <a class="btn btn-ghost btn-sm profile-password" href="<?= base_url('ganti-sandi') ?>"><?= icon('key') ?> <?= esc(lang('Game.changePassword')) ?></a>
  </aside>

  <div class="panel profile-main stage-box">
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
    <div class="table-wrap stage-scroll">
      <table class="data-table">
        <caption class="visually-hidden"><?= esc(lang('Game.history')) ?></caption>
        <thead>
          <tr>
            <th scope="col"><?= esc(lang('Game.challenge')) ?></th>
            <th scope="col"><?= esc(lang('Game.attempt')) ?></th>
            <th scope="col"><?= esc(lang('Game.status')) ?></th>
            <th scope="col"><?= esc(lang('Game.firstPass')) ?></th>
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
              <td><?= $attempt->isCompleted() ? esc(first_pass_text($attempt, $locale)) : '—' ?></td>
              <td><?= $attempt->isCompleted() ? stars_html((int) $attempt->stars) : '—' ?></td>
              <td><?= esc(fmt_date($attempt->started_at, false, $locale)) ?></td>
            </tr>
          <?php endforeach ?>
        </tbody>
      </table>
    </div>
  <?php endif ?>
  </div>
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
