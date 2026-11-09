<?php
/**
 * Satu sesi lapangan — `/admin/sesi-lapangan/{id}` (28 §6.1–§6.3, §6.7).
 *
 * @var array<string, mixed>       $fieldSession + research_code
 * @var string                     $state pending | open | closed
 * @var string|null                $token
 * @var list<array<string, mixed>> $tokens history, oldest first
 * @var array<string, mixed>       $plan   FieldSessionService::planEntry()
 * @var int                        $rosterCount
 * @var int                        $enteredCount
 * @var int                        $codesUsed
 * @var int                        $codesMax
 * @var list<array<string, mixed>> $makeups
 */
$id   = (int) $fieldSession['id'];
$base = 'admin/sesi-lapangan/' . $id;
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', ['title' => (string) $fieldSession['code'], 'lead' => lang('Admin.fieldSessionsLead')]) ?>
<?= $this->include('partials/flash') ?>

<?php if ($codesUsed >= $codesMax): ?>
  <div class="alert alert-warn" role="status"><?= icon('warn') ?><p><?= esc(lang('Admin.codesFullWarn')) ?></p></div>
<?php endif ?>

<div class="kpi-grid">
  <?= component('stat-tile', ['label' => lang('Admin.fieldSessionState'), 'value' => lang('Admin.fieldSessionState_' . $state), 'icon' => 'clock']) ?>
  <?= component('stat-tile', ['label' => lang('Admin.fieldSessionToken'), 'value' => $state === 'closed' ? '—' : (string) $token, 'icon' => 'key']) ?>
  <?= component('stat-tile', ['label' => lang('Admin.monitoringEntered'), 'value' => fmt_num($enteredCount, 0, 'id') . ' / ' . fmt_num($rosterCount, 0, 'id'), 'icon' => 'users']) ?>
</div>

<dl class="detail-list">
  <dt><?= esc(lang('Admin.fieldSessionDate')) ?></dt><dd><?= esc(fmt_date((string) $fieldSession['session_date'])) ?></dd>
  <dt><?= esc(lang('Admin.fieldSessionOpens')) ?></dt><dd><?= esc(fmt_date((string) $fieldSession['opens_at'])) ?></dd>
  <dt><?= esc(lang('Admin.fieldSessionCloses')) ?></dt><dd><?= esc(fmt_date((string) $fieldSession['closes_at'])) ?></dd>
  <?php if (! empty($fieldSession['closed_at'])): ?>
    <dt><?= esc(lang('Admin.fieldSessionClosedAt')) ?></dt><dd><?= esc(fmt_date((string) $fieldSession['closed_at'])) ?></dd>
  <?php endif ?>
  <dt><?= esc(lang('Admin.fieldSessionRegion')) ?></dt><dd><?= esc($plan['regions'] === [] ? '—' : implode(', ', $plan['regions'])) ?></dd>
</dl>

<?php if ($state !== 'closed'): ?>
  <section class="form-section">
    <form method="post" action="<?= base_url($base . '/jam') ?>" class="filter-bar">
      <?= csrf_field() ?>
      <?php if ($state === 'pending'): ?>
        <label class="check"><input type="checkbox" name="open_now" value="1"> <span><?= esc(lang('Admin.fieldSessionOpenNow')) ?></span></label>
      <?php endif ?>
      <div class="field">
        <label for="fs-closes"><?= esc(lang('Admin.fieldSessionCloses')) ?></label>
        <input type="time" id="fs-closes" name="closes" value="<?= esc(substr((string) $fieldSession['closes_at'], 11, 5), 'attr') ?>">
      </div>
      <button class="btn btn-sm" type="submit"><?= esc(lang('Admin.fieldSessionSaveHours')) ?></button>
    </form>
    <div class="form-actions">
      <form method="post" action="<?= base_url($base . '/token-baru') ?>" class="inline-form">
        <?= csrf_field() ?>
        <button class="btn btn-sm" type="submit"><?= icon('key') ?> <?= esc(lang('Admin.fieldSessionTokenNew')) ?></button>
      </form>
      <form method="post" action="<?= base_url($base . '/tutup') ?>" class="inline-form"
            data-confirm="<?= esc(lang('Admin.fieldSessionCloseNow'), 'attr') ?>?" data-confirm-style="danger">
        <?= csrf_field() ?>
        <button class="btn btn-danger btn-sm" type="submit"><?= esc(lang('Admin.fieldSessionCloseNow')) ?></button>
      </form>
    </div>
  </section>
<?php endif ?>

<p class="form-actions">
  <a class="btn btn-sm" href="<?= base_url($base . '/kursi') ?>"><?= esc(lang('Admin.fieldSessionSeats')) ?></a>
  <?php // Part 3 (29 §10.3–§10.4): field sheets ?>
  <a class="btn btn-sm" href="<?= base_url($base . '/lembar') ?>"><?= icon('download') ?> <?= esc(lang('Admin.fieldSheetTemplate')) ?></a>
  <a class="btn btn-sm" href="<?= base_url($base . '/lembar/unggah') ?>"><?= icon('upload') ?> <?= esc(lang('Admin.fieldSheetUpload')) ?></a>
  <?php // Part 4 (30 §5.2, §14): the consent page next to the seats ?>
  <a class="btn btn-sm" href="<?= base_url($base . '/izin') ?>"><?= esc(lang('Admin.consentTitle')) ?></a>
  <?php if ((int) $fieldSession['makeup_no'] === 0 && $state === 'closed'): ?>
    <a class="btn btn-sm" href="<?= base_url($base . '/susulan') ?>"><?= esc(lang('Admin.fieldSessionMakeup')) ?></a>
  <?php endif ?>
  <a class="btn btn-sm btn-quiet" href="<?= base_url('admin/pemantauan') ?>"><?= esc(lang('Admin.menuMonitoring')) ?></a>
</p>

<?php // Part 8 (34 §5.3, §7.4, §14): the test module of this meeting ?>
<?php if (($testPanel['occasion'] ?? null) !== null): ?>
  <section class="card">
    <h2><?= esc(lang('Admin.fieldTestTitle')) ?></h2>
    <?php if ($testPanel['makeup_late']): ?>
      <p class="alert alert-warn"><?= esc(lang('Admin.fieldTestMakeupLate')) ?></p>
    <?php elseif ($testPanel['started_at'] !== null): ?>
      <p><?= esc(lang('Admin.fieldTestStarted', [fmt_date((string) $testPanel['started_at'])])) ?></p>
    <?php else: ?>
      <?php if ($testPanel['need_seats']): ?><p class="muted"><?= esc(lang('Admin.fieldTestNeedSeats')) ?></p><?php endif ?>
      <?php if ($testPanel['occasion'] === 'post'): ?><p class="muted"><?= esc(lang('Admin.fieldTestStartPostHint')) ?></p><?php endif ?>
      <form method="post" action="<?= base_url($base . '/mulai-tes') ?>" class="inline-form">
        <?= csrf_field() ?>
        <button class="btn btn-primary btn-sm" type="submit"<?= $testPanel['can_start'] ? '' : ' disabled' ?>><?= esc(lang($testPanel['occasion'] === 'pre' ? 'Admin.fieldTestStartPre' : 'Admin.fieldTestStartPost')) ?></button>
      </form>
    <?php endif ?>
    <table class="data-table">
      <thead><tr><th><?= esc(lang('Admin.instrumentExtendCode')) ?></th><th title="<?= esc(lang('Admin.testStatusHelp'), 'attr') ?>"><?= esc(lang('Admin.testStatus')) ?></th><th><?= esc(lang('Admin.moduleDone')) ?></th></tr></thead>
      <tbody>
        <?php foreach ($testPanel['students'] as $student): ?>
          <tr><td class="mono"><?= esc($student['participant_code']) ?></td><td><?= esc(lang('Admin.testStatus' . ucfirst($student['status']))) ?></td><td><?= $student['module_done'] ? icon('check') : '' ?></td></tr>
        <?php endforeach ?>
      </tbody>
    </table>
    <form method="post" action="<?= base_url($base . '/tambah-waktu') ?>" class="form-grid">
      <?= csrf_field() ?>
      <h3><?= esc(lang('Admin.instrumentExtend')) ?></h3>
      <label><?= esc(lang('Admin.instrumentExtendCode')) ?> <input type="text" name="participant_code" required></label>
      <label><?= esc(lang('Admin.instrumentExtendBlock')) ?>
        <select name="block"><option value="p1"><?= esc(lang('Admin.instrumentExtendBlockP1')) ?></option><option value="p23"><?= esc(lang('Admin.instrumentExtendBlockP23')) ?></option></select>
      </label>
      <label><?= esc(lang('Admin.instrumentExtendMinutes')) ?> <input type="number" name="minutes" min="1" max="60" required></label>
      <label><?= esc(lang('Admin.instrumentExtendReason')) ?> <input type="text" name="reason" maxlength="200" required></label>
      <button class="btn btn-sm" type="submit"><?= esc(lang('Admin.instrumentExtend')) ?></button>
    </form>
  </section>
<?php endif ?>

<?php if ($makeups !== []): ?>
  <h2><?= esc(lang('Admin.fieldSessionMakeup')) ?></h2>
  <ul><?php foreach ($makeups as $makeup): ?>
    <li><a class="mono" href="<?= base_url('admin/sesi-lapangan/' . $makeup['id']) ?>"><?= esc($makeup['code']) ?></a></li>
  <?php endforeach ?></ul>
<?php endif ?>

<h2><?= esc(lang('Admin.fieldSessionTokenHistory')) ?></h2>
<table class="data-table">
  <tbody>
    <?php foreach ($tokens as $row): ?>
      <tr><td class="mono"><?= esc($row['token']) ?></td><td><?= esc(fmt_date((string) $row['created_at'])) ?></td><td><?= $row['revoked_at'] === null ? '' : esc(fmt_date((string) $row['revoked_at'])) ?></td></tr>
    <?php endforeach ?>
  </tbody>
</table>
<?= $this->endSection() ?>
