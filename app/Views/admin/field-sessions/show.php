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

<?php // The token the class copies from the board: big, with a full-screen view for a projector ?>
<section class="panel token-hero" aria-labelledby="fs-token-label">
  <div>
    <p class="token-hero-label" id="fs-token-label"><?= icon('key') ?> <?= esc(lang('Admin.fieldSessionToken')) ?></p>
    <?php if ($state === 'closed'): ?>
      <p class="token-big is-none"><?= esc(lang('Admin.fieldSessionTokenNone')) ?></p>
    <?php else: ?>
      <p class="token-big"><?= esc((string) $token) ?></p>
    <?php endif ?>
    <p class="token-hero-meta">
      <span class="badge<?= $state === 'open' ? ' is-ok' : ($state === 'pending' ? ' is-warn' : '') ?>"><?= esc(lang('Admin.fieldSessionState_' . $state)) ?></span>
      <span><?= esc(lang('Admin.monitoringEntered')) ?>: <b><?= esc(fmt_num($enteredCount, 0, 'id') . ' / ' . fmt_num($rosterCount, 0, 'id')) ?></b></span>
    </p>
    <?php if ($state !== 'closed'): ?><p class="field-help"><?= esc(lang('Admin.fieldSessionTokenHint')) ?></p><?php endif ?>
  </div>
  <?php if ($state !== 'closed'): ?>
    <div class="btn-row">
      <a class="btn btn-primary" href="#token-layar"><?= icon('eye') ?> <?= esc(lang('Admin.fieldSessionTokenShow')) ?></a>
      <form method="post" action="<?= base_url($base . '/token-baru') ?>" class="inline-form">
        <?= csrf_field() ?>
        <button class="btn" type="submit"><?= icon('key') ?> <?= esc(lang('Admin.fieldSessionTokenNew')) ?></button>
      </form>
    </div>
  <?php endif ?>
</section>

<?php if ($state !== 'closed'): ?>
  <div class="token-screen" id="token-layar" role="dialog" aria-modal="true" aria-labelledby="token-layar-label">
    <p class="token-screen-label" id="token-layar-label"><?= esc(lang('Admin.fieldSessionTokenScreen')) ?></p>
    <p class="token-screen-token"><?= esc((string) $token) ?></p>
    <p class="token-screen-code"><?= esc((string) $fieldSession['code']) ?></p>
    <a class="btn btn-lg" href="#"><?= icon('cross') ?> <?= esc(lang('Admin.fieldSessionTokenScreenClose')) ?></a>
  </div>
<?php endif ?>

<dl class="detail-list">
  <dt><?= esc(lang('Admin.fieldSessionDate')) ?></dt><dd><?= esc(fmt_date((string) $fieldSession['session_date'])) ?></dd>
  <dt><?= esc(lang('Admin.fieldSessionHours')) ?></dt>
  <dd class="mono"><?= esc(substr((string) $fieldSession['opens_at'], 11, 5)) ?>–<?= esc(substr((string) $fieldSession['closes_at'], 11, 5)) ?></dd>
  <?php if (! empty($fieldSession['closed_at'])): ?>
    <dt><?= esc(lang('Admin.fieldSessionClosedAt')) ?></dt><dd><?= esc(fmt_date((string) $fieldSession['closed_at'])) ?></dd>
  <?php endif ?>
  <dt><?= esc(lang('Admin.fieldSessionRegion')) ?></dt><dd><?= esc($plan['regions'] === [] ? '—' : implode(', ', $plan['regions'])) ?></dd>
</dl>

<?php // The three steps of a visit, in the order the researcher meets them ?>
<div class="session-steps">
  <section class="panel session-step">
    <h2 class="panel-title"><span class="step-no" aria-hidden="true">1</span> <?= esc(lang('Admin.fieldSessionStepBefore')) ?></h2>
    <p><?= esc(lang('Admin.fieldSessionStepBeforeHelp')) ?></p>
    <div class="stack-actions">
      <?php // Part 4 (30 §5.2, §14): the consent page next to the seats ?>
      <a class="btn" href="<?= base_url($base . '/izin') ?>"><?= icon('shield') ?> <?= esc(lang('Admin.consentTitle')) ?></a>
      <a class="btn" href="<?= base_url($base . '/kursi') ?>"><?= icon('users') ?> <?= esc(lang('Admin.fieldSessionSeats')) ?></a>
    </div>
  </section>

  <section class="panel session-step">
    <h2 class="panel-title"><span class="step-no" aria-hidden="true">2</span> <?= esc(lang('Admin.fieldSessionStepDuring')) ?></h2>
    <p><?= esc(lang('Admin.fieldSessionStepDuringHelp')) ?></p>
    <div class="stack-actions">
      <a class="btn btn-primary" href="<?= base_url('admin/pemantauan') ?>"><?= icon('eye') ?> <?= esc(lang('Admin.menuMonitoring')) ?></a>
    </div>
    <?php if ($state !== 'closed'): ?>
      <form method="post" action="<?= base_url($base . '/jam') ?>" class="form-inline">
        <?= csrf_field() ?>
        <?php if ($state === 'pending'): ?>
          <label class="check"><input type="checkbox" name="open_now" value="1"> <span><?= esc(lang('Admin.fieldSessionOpenNow')) ?></span></label>
        <?php endif ?>
        <div class="field">
          <label for="fs-closes"><?= esc(lang('Admin.fieldSessionCloses')) ?></label>
          <input type="time" id="fs-closes" name="closes" value="<?= esc(substr((string) $fieldSession['closes_at'], 11, 5), 'attr') ?>">
        </div>
        <button class="btn" type="submit"><?= esc(lang('Admin.fieldSessionSaveHours')) ?></button>
      </form>
    <?php endif ?>
  </section>

  <section class="panel session-step">
    <h2 class="panel-title"><span class="step-no" aria-hidden="true">3</span> <?= esc(lang('Admin.fieldSessionStepAfter')) ?></h2>
    <p><?= esc(lang('Admin.fieldSessionStepAfterHelp')) ?></p>
    <div class="stack-actions">
      <?php if ($state !== 'closed'): ?>
        <form method="post" action="<?= base_url($base . '/tutup') ?>" class="inline-form"
              data-confirm="<?= esc(lang('Admin.fieldSessionCloseNow'), 'attr') ?>?" data-confirm-style="danger">
          <?= csrf_field() ?>
          <button class="btn btn-danger-soft" type="submit"><?= icon('cross') ?> <?= esc(lang('Admin.fieldSessionCloseNow')) ?></button>
        </form>
      <?php endif ?>
      <?php // Part 3 (29 §10.3–§10.4): field sheets ?>
      <a class="btn" href="<?= base_url($base . '/lembar') ?>"><?= icon('download') ?> <?= esc(lang('Admin.fieldSheetTemplate')) ?></a>
      <a class="btn" href="<?= base_url($base . '/lembar/unggah') ?>"><?= icon('upload') ?> <?= esc(lang('Admin.fieldSheetUpload')) ?></a>
      <?php if ((int) $fieldSession['makeup_no'] === 0 && $state === 'closed'): ?>
        <a class="btn" href="<?= base_url($base . '/susulan') ?>"><?= icon('clock') ?> <?= esc(lang('Admin.fieldSessionMakeup')) ?></a>
      <?php endif ?>
    </div>
  </section>
</div>

<?php // Part 8 (34 §5.3, §7.4, §14): the test module of this meeting ?>
<?php if (($testPanel['occasion'] ?? null) !== null): ?>
  <section class="panel stack">
    <h2 class="panel-title"><?= icon('clipboard') ?> <?= esc(lang('Admin.fieldTestTitle')) ?></h2>
    <?php if ($testPanel['makeup_late']): ?>
      <p class="alert alert-warn"><?= esc(lang('Admin.fieldTestMakeupLate')) ?></p>
    <?php elseif ($testPanel['started_at'] !== null): ?>
      <p class="alert alert-ok"><?= icon('check') ?> <?= esc(lang('Admin.fieldTestStarted', [fmt_date((string) $testPanel['started_at'])])) ?></p>
    <?php else: ?>
      <?php if ($testPanel['need_seats']): ?><p class="alert alert-warn"><?= icon('warn') ?> <?= esc(lang('Admin.fieldTestNeedSeats')) ?></p><?php endif ?>
      <?php if ($testPanel['occasion'] === 'post'): ?><p class="muted"><?= esc(lang('Admin.fieldTestStartPostHint')) ?></p><?php endif ?>
      <form method="post" action="<?= base_url($base . '/mulai-tes') ?>" class="inline-form">
        <?= csrf_field() ?>
        <button class="btn btn-primary" type="submit"<?= $testPanel['can_start'] ? '' : ' disabled' ?>><?= esc(lang($testPanel['occasion'] === 'pre' ? 'Admin.fieldTestStartPre' : 'Admin.fieldTestStartPost')) ?></button>
      </form>
    <?php endif ?>
    <?php if ($testPanel['students'] === []): ?>
      <p class="muted"><?= esc(lang('Admin.fieldTestNoStudents')) ?></p>
    <?php else: ?>
      <div class="table-wrap is-short">
        <table class="data-table">
          <thead><tr><th><?= esc(lang('Admin.instrumentExtendCode')) ?></th><th title="<?= esc(lang('Admin.testStatusHelp'), 'attr') ?>"><?= esc(lang('Admin.testStatus')) ?></th><th><?= esc(lang('Admin.moduleDone')) ?></th></tr></thead>
          <tbody>
            <?php foreach ($testPanel['students'] as $student): ?>
              <tr><td class="mono"><?= esc($student['participant_code']) ?></td><td><?= esc(lang('Admin.testStatus' . ucfirst($student['status']))) ?></td><td><?= $student['module_done'] ? icon('check') : '' ?></td></tr>
            <?php endforeach ?>
          </tbody>
        </table>
      </div>
    <?php endif ?>
    <h3 class="panel-subtitle"><?= esc(lang('Admin.instrumentExtend')) ?></h3>
    <form method="post" action="<?= base_url($base . '/tambah-waktu') ?>" class="form-grid">
      <?= csrf_field() ?>
      <div class="field">
        <label for="fs-extend-code"><?= esc(lang('Admin.instrumentExtendCode')) ?></label>
        <input type="text" id="fs-extend-code" name="participant_code" class="mono" required>
      </div>
      <div class="field">
        <label for="fs-extend-block"><?= esc(lang('Admin.instrumentExtendBlock')) ?></label>
        <select id="fs-extend-block" name="block"><option value="p1"><?= esc(lang('Admin.instrumentExtendBlockP1')) ?></option><option value="p23"><?= esc(lang('Admin.instrumentExtendBlockP23')) ?></option></select>
      </div>
      <div class="field">
        <label for="fs-extend-minutes"><?= esc(lang('Admin.instrumentExtendMinutes')) ?></label>
        <input type="number" id="fs-extend-minutes" name="minutes" min="1" max="60" required>
      </div>
      <div class="field">
        <label for="fs-extend-reason"><?= esc(lang('Admin.instrumentExtendReason')) ?></label>
        <input type="text" id="fs-extend-reason" name="reason" maxlength="200" required>
      </div>
      <div class="span-all"><button class="btn" type="submit"><?= icon('clock') ?> <?= esc(lang('Admin.instrumentExtend')) ?></button></div>
    </form>
  </section>
<?php endif ?>

<?php if ($makeups !== []): ?>
  <section class="panel">
    <h2 class="panel-title"><?= esc(lang('Admin.fieldSessionMakeup')) ?></h2>
    <ul class="stat-list"><?php foreach ($makeups as $makeup): ?>
      <li><a class="mono" href="<?= base_url('admin/sesi-lapangan/' . $makeup['id']) ?>"><?= esc($makeup['code']) ?></a></li>
    <?php endforeach ?></ul>
  </section>
<?php endif ?>

<section class="panel">
  <h2 class="panel-title"><?= esc(lang('Admin.fieldSessionTokenHistory')) ?></h2>
  <div class="table-wrap is-short">
    <table class="data-table">
      <thead><tr><th><?= esc(lang('Admin.fieldSessionToken')) ?></th><th><?= esc(lang('Admin.fieldSessionTokenCreatedAt')) ?></th><th><?= esc(lang('Admin.fieldSessionTokenRevokedAt')) ?></th></tr></thead>
      <tbody>
        <?php foreach ($tokens as $row): ?>
          <tr><td class="mono"><?= esc($row['token']) ?></td><td><?= esc(fmt_date((string) $row['created_at'])) ?></td><td><?= $row['revoked_at'] === null ? '—' : esc(fmt_date((string) $row['revoked_at'])) ?></td></tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
</section>
<?= $this->endSection() ?>
