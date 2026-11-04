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
  <?php // Part 4 (30 §5.2, §14): the consent page next to the seats ?>
  <a class="btn btn-sm" href="<?= base_url($base . '/izin') ?>"><?= esc(lang('Admin.consentTitle')) ?></a>
  <?php if ((int) $fieldSession['makeup_no'] === 0 && $state === 'closed'): ?>
    <a class="btn btn-sm" href="<?= base_url($base . '/susulan') ?>"><?= esc(lang('Admin.fieldSessionMakeup')) ?></a>
  <?php endif ?>
  <a class="btn btn-sm btn-quiet" href="<?= base_url('admin/pemantauan') ?>"><?= esc(lang('Admin.menuMonitoring')) ?></a>
</p>

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
