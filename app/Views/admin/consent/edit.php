<?php
/**
 * Izin per siswa — `/admin/sesi-lapangan/{id}/izin` (30 §5.2). Codes only.
 * Each row carries the `updated_at` it was read with, so a save is
 * conditional on it.
 *
 * @var array<string, mixed>       $fieldSession
 * @var list<array<string, mixed>> $roster  id, participant_code, consent
 * @var int                        $missing students without a parent consent date
 */
$statuses = \App\Services\ConsentService::STATUSES;
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', ['title' => lang('Admin.consentTitle') . ' · ' . $fieldSession['code'], 'lead' => lang('Admin.consentHelp')]) ?>
<?= $this->include('partials/flash') ?>

<?php if ($missing > 0): ?>
  <div class="alert alert-warn" role="status"><?= icon('warn') ?><p><?= esc(lang('Admin.consentMissing', [$missing])) ?></p></div>
<?php endif ?>

<?php if ($roster === []): ?>
  <p class="muted"><?= esc(lang('Admin.seatsNone')) ?></p>
<?php else: ?>
  <form method="post" action="<?= base_url('admin/sesi-lapangan/' . $fieldSession['id'] . '/izin') ?>" class="form">
    <?= csrf_field() ?>
    <table class="data-table">
      <thead><tr>
        <th>Kode peserta</th><th><?= esc(lang('Admin.consentParentDate')) ?></th>
        <th><?= esc(lang('Admin.consentRecording')) ?></th><th><?= esc(lang('Admin.consentWithdrawnOn')) ?></th>
        <th><?= esc(lang('Admin.consentRiskItems')) ?></th><th><?= esc(lang('Admin.consentWithdrawnOn')) ?></th>
        <th><?= esc(lang('Admin.consentStatus')) ?></th><th><?= esc(lang('Admin.consentStatusDate')) ?></th>
      </tr></thead>
      <tbody>
        <?php foreach ($roster as $student): ?>
          <?php $c = $student['consent'] ?? []; $n = 'consent[' . (int) $student['id'] . ']'; $code = $student['participant_code']; ?>
          <tr>
            <td class="mono"><?= esc($code) ?><input type="hidden" name="<?= $n ?>[stamp]" value="<?= esc((string) ($c['updated_at'] ?? ''), 'attr') ?>"></td>
            <td><input type="date" name="<?= $n ?>[parent_consent_on]" value="<?= esc((string) ($c['parent_consent_on'] ?? ''), 'attr') ?>" aria-label="<?= esc(lang('Admin.consentParentDate') . ' ' . $code, 'attr') ?>"></td>
            <?php foreach (['recording', 'risk_items'] as $kind): ?>
              <td><select name="<?= $n ?>[<?= $kind ?>_consent]" aria-label="<?= esc(lang($kind === 'recording' ? 'Admin.consentRecording' : 'Admin.consentRiskItems') . ' ' . $code, 'attr') ?>">
                <option value="0"><?= esc(lang('Admin.no')) ?></option>
                <option value="1" <?= (int) ($c[$kind . '_consent'] ?? 0) === 1 ? 'selected' : '' ?>><?= esc(lang('Admin.yes')) ?></option>
              </select></td>
              <td><input type="date" name="<?= $n ?>[<?= $kind ?>_withdrawn_on]" value="<?= esc((string) ($c[$kind . '_withdrawn_on'] ?? ''), 'attr') ?>" aria-label="<?= esc(lang('Admin.consentWithdrawnOn') . ' ' . $code, 'attr') ?>"></td>
            <?php endforeach ?>
            <td><select name="<?= $n ?>[status]" aria-label="<?= esc(lang('Admin.consentStatus') . ' ' . $code, 'attr') ?>">
              <?php foreach ($statuses as $status): ?>
                <option value="<?= $status ?>" <?= ($c['status'] ?? 'active') === $status ? 'selected' : '' ?>><?= esc(\App\Services\ConsentService::statusLabel($status)) ?></option>
              <?php endforeach ?>
            </select></td>
            <td><input type="date" name="<?= $n ?>[status_on]" value="<?= esc((string) ($c['status_on'] ?? ''), 'attr') ?>" aria-label="<?= esc(lang('Admin.consentStatusDate') . ' ' . $code, 'attr') ?>"></td>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>
    <div class="form-actions"><button class="btn btn-primary" type="submit"><?= icon('check') ?> <?= esc(lang('Admin.save')) ?></button></div>
  </form>
<?php endif ?>
<?= $this->endSection() ?>
