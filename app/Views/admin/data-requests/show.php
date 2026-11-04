<?php
/**
 * One parent's request — `/admin/permintaan-data/{id}` (30 §7.1, §7.6, §7.7).
 *
 * @var array<string, mixed>                       $request
 * @var array{received_hours: ?int, notify_left: ?int, level: string} $deadline
 * @var array<string, array{at: string, by: ?int}> $checklist
 * @var list<string>                               $items
 * @var list<array<string, mixed>>                 $copies  head researcher's downloads since the student registered
 * @var array<string, int>                         $counts
 * @var string                                     $role
 * @var bool                                       $mine
 */
use App\Libraries\StaffAccess;
use App\Services\DataRequestService;

$base    = 'admin/permintaan-data/' . $request['id'];
$pending = array_diff($items, ['parent_notified'], array_keys($checklist));
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', ['title' => lang('Admin.requestFromParents') . ' #' . $request['id'], 'lead' => lang('Admin.requestKind_' . $request['kind'])]) ?>
<?= $this->include('partials/flash') ?>

<dl class="detail-list">
  <dt>Kode peserta</dt><dd class="mono"><?= esc((string) $request['participant_code']) ?></dd>
  <dt>Status</dt><dd><?= esc(lang('Admin.requestStatus_' . $request['status'])) ?></dd>
  <dt><?= esc(lang('Admin.requestReceived')) ?></dt><dd><?= esc(fmt_date((string) $request['received_at'], true, 'id')) ?> · <?= esc(lang('Admin.requestVia_' . $request['received_via'])) ?><?php if ($deadline['received_hours'] !== null): ?> · <?= esc(lang('Admin.requestReceivedAgo', [$deadline['received_hours']])) ?><?php endif ?></dd>
  <dt><?= esc(lang('Admin.requestVerified')) ?></dt><dd><?= esc(fmt_date((string) $request['verified_at'], true, 'id')) ?></dd>
  <?php if ($request['executed_at'] !== null): ?>
    <dt><?= esc(lang('Admin.requestExecutedAt')) ?></dt><dd><?= esc(fmt_date((string) $request['executed_at'], true, 'id')) ?></dd>
  <?php endif ?>
  <?php if ($deadline['notify_left'] !== null): ?>
    <dt><?= esc(lang('Admin.requestDeadline')) ?></dt>
    <dd class="deadline is-<?= esc($deadline['level'], 'attr') ?>"><?= esc($deadline['notify_left'] >= 0 ? lang('Admin.requestNotifyLeft', [$deadline['notify_left']]) : lang('Admin.requestNotifyLate', [-$deadline['notify_left']])) ?></dd>
  <?php endif ?>
</dl>

<?php if ($request['status'] === 'requested'): ?>
  <p class="alert alert-warn"><?= icon('clock') ?> <?= esc(lang('Admin.requestWaitConfirm')) ?></p>
  <?php if (StaffAccess::allows($role, 'deletion.confirm')): ?>
    <p class="muted"><?= esc(lang('Admin.requestConfirmHelp')) ?></p>
    <form method="post" action="<?= base_url($base . '/konfirmasi') ?>" class="form-inline"><?= csrf_field() ?><button class="btn btn-primary btn-sm" type="submit"><?= esc(lang('Admin.requestConfirm')) ?></button></form>
    <form method="post" action="<?= base_url($base . '/tolak') ?>" class="form form-inline"><?= csrf_field() ?>
      <label for="req-reason"><?= esc(lang('Admin.correctReason')) ?></label> <input type="text" id="req-reason" name="reason" maxlength="200" required>
      <button class="btn btn-danger btn-sm" type="submit"><?= esc(lang('Admin.requestReject')) ?></button>
    </form>
  <?php endif ?>
  <?php if ($mine): ?>
    <form method="post" action="<?= base_url($base . '/batal') ?>" class="form-inline"><?= csrf_field() ?><button class="btn btn-sm btn-quiet" type="submit"><?= esc(lang('Admin.requestCancel')) ?></button></form>
  <?php endif ?>
<?php elseif ($request['status'] === 'preview'): ?>
  <p class="alert alert-warn"><?= icon('clock') ?> <?= esc(lang('Admin.requestWaitRun', [fmt_num((int) $request['affected_count'], 0, 'id')])) ?></p>
  <?php if (StaffAccess::allows($role, 'deletion.execute')): ?><p><a class="btn btn-sm" href="<?= base_url('admin/tata-kelola') ?>"><?= esc(lang('Admin.menuGovernance')) ?></a></p><?php endif ?>
<?php endif ?>

<?php if (in_array($request['status'], ['executed', 'completed'], true)): ?>
  <section class="form-section">
    <h2><?= esc(lang('Admin.requestChecklist')) ?></h2>
    <table class="data-table">
      <tbody>
        <?php foreach ($items as $item): ?>
          <?php $cap = DataRequestService::TICK_CAPABILITY[$item]; $blocked = $item === 'parent_notified' && $pending !== []; ?>
          <tr>
            <td><?= esc(lang('Admin.requestItem_' . $item)) ?></td>
            <td>
              <?php if (isset($checklist[$item])): ?>
                <?= icon('check') ?> <?= esc(fmt_date((string) $checklist[$item]['at'], true, 'id')) ?>
              <?php elseif ($cap !== null && StaffAccess::allows($role, $cap) && $request['status'] === 'executed'): ?>
                <?php if ($blocked): ?>
                  <span class="muted"><?= esc(lang('Admin.requestChecklistBlocked')) ?></span>
                <?php else: ?>
                  <form method="post" action="<?= base_url($base . '/centang') ?>" class="form-inline"><?= csrf_field() ?><input type="hidden" name="item" value="<?= $item ?>"><button class="btn btn-sm" type="submit"><?= esc(lang('Admin.requestTick')) ?></button></form>
                <?php endif ?>
              <?php else: ?>—<?php endif ?>
            </td>
          </tr>
          <?php if ($item === 'backups' && StaffAccess::allows($role, 'deletion.execute')): ?><tr><td colspan="2" class="muted small"><?= esc(lang('Admin.requestBackupsHelp')) ?></td></tr><?php endif ?>
        <?php endforeach ?>
      </tbody>
    </table>
  </section>
<?php endif ?>

<?php if ($copies !== [] && in_array('researcher_copies', $items, true)): ?>
  <section class="form-section">
    <h2><?= esc(lang('Admin.requestItem_researcher_copies')) ?></h2>
    <p class="muted"><?= esc(lang('Admin.requestCopiesHelp')) ?></p>
    <ul><?php foreach ($copies as $copy): ?><li>#<?= (int) $copy['id'] ?> · <?= esc(strtoupper((string) $copy['format'])) ?> · <?= esc(fmt_date((string) $copy['created_at'], true, 'id')) ?></li><?php endforeach ?></ul>
  </section>
<?php endif ?>

<?php if (StaffAccess::allowsAny($role, ['deletion.confirm', 'deletion.execute'])): ?>
  <p><a class="btn btn-sm" href="<?= base_url($base . '/berita-acara') ?>"><?= esc(lang('Admin.requestMinutes')) ?></a></p>
<?php endif ?>
<?= $this->endSection() ?>
