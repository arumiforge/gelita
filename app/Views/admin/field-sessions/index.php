<?php
/**
 * Sesi lapangan — `/admin/sesi-lapangan` → FieldSessionController::index
 * (research revision 10, part 2: 28 §6). Codes only: school research codes,
 * never names or NPSN.
 *
 * @var array<string, mixed>|null  $study
 * @var bool                       $fieldMode
 * @var list<array<string, mixed>> $sessions + state, token
 */
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title' => lang('Admin.menuFieldSessions'),
    'lead'  => lang('Admin.fieldSessionsLead'),
]) ?>
<?= $this->include('partials/flash') ?>

<?php if (! $fieldMode): ?>
  <div class="alert alert-warn" role="status"><?= icon('warn') ?><p><?= esc(lang('Admin.fieldModeOff')) ?></p></div>
<?php else: ?>
  <p class="form-actions"><a class="btn btn-primary" href="<?= base_url('admin/sesi-lapangan/baru') ?>"><?= icon('edit') ?> <?= esc(lang('Admin.fieldSessionNew')) ?></a></p>
<?php endif ?>

<?php if ($sessions === []): ?>
  <p class="muted"><?= esc(lang('Admin.fieldSessionNone')) ?></p>
<?php else: ?>
  <table class="data-table">
    <thead><tr>
      <th><?= esc(lang('Admin.fieldSessionCode')) ?></th>
      <th><?= esc(lang('Admin.fieldSessionDate')) ?></th>
      <th><?= esc(lang('Admin.fieldSessionOpens')) ?></th>
      <th><?= esc(lang('Admin.fieldSessionCloses')) ?></th>
      <th><?= esc(lang('Admin.fieldSessionState')) ?></th>
      <th><?= esc(lang('Admin.fieldSessionToken')) ?></th>
    </tr></thead>
    <tbody>
      <?php foreach ($sessions as $row): ?>
        <tr>
          <td><a href="<?= base_url('admin/sesi-lapangan/' . $row['id']) ?>"><b class="mono"><?= esc($row['code']) ?></b></a></td>
          <td><?= esc(fmt_date((string) $row['session_date'])) ?></td>
          <td><?= esc(substr((string) $row['opens_at'], 11, 5)) ?></td>
          <td><?= esc(substr((string) ($row['closed_at'] ?? $row['closes_at']), 11, 5)) ?></td>
          <td><span class="badge"><?= esc(lang('Admin.fieldSessionState_' . $row['state'])) ?></span></td>
          <td class="mono"><?= $row['state'] === 'closed' ? '—' : esc((string) $row['token']) ?></td>
        </tr>
      <?php endforeach ?>
    </tbody>
  </table>
<?php endif ?>
<?= $this->endSection() ?>
