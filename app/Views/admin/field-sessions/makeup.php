<?php
/**
 * Buat sesi susulan — `/admin/sesi-lapangan/{id}/susulan` (28 §6.7, K12).
 * Students without a game session in the main session start ticked.
 *
 * @var array<string, mixed>       $main
 * @var string                     $state
 * @var list<array<string, mixed>> $roster  id, participant_code
 * @var list<int>                  $checked
 */
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', ['title' => lang('Admin.fieldSessionMakeup') . ' · ' . $main['code'], 'lead' => lang('Admin.fieldSessionMakeupLead')]) ?>
<?= $this->include('partials/flash') ?>

<?php if ($state !== 'closed'): ?>
  <div class="alert alert-warn" role="status"><?= icon('warn') ?><p><?= esc(lang('Admin.fieldSessionMakeupNeedsClosed')) ?></p></div>
<?php else: ?>
  <form method="post" action="<?= base_url('admin/sesi-lapangan/' . $main['id'] . '/susulan') ?>" class="form form-grid">
    <?= csrf_field() ?>
    <div class="field">
      <label for="mk-date"><?= esc(lang('Admin.fieldSessionDate')) ?></label>
      <input type="date" id="mk-date" name="session_date" required value="<?= esc(date('Y-m-d'), 'attr') ?>">
    </div>
    <div class="field">
      <label for="mk-opens"><?= esc(lang('Admin.fieldSessionOpens')) ?></label>
      <input type="time" id="mk-opens" name="opens" required value="07:30">
    </div>
    <div class="field">
      <label for="mk-closes"><?= esc(lang('Admin.fieldSessionCloses')) ?></label>
      <input type="time" id="mk-closes" name="closes" required value="09:30">
    </div>
    <fieldset class="field span-all">
      <legend><?= esc(lang('Admin.fieldSessionMembers')) ?></legend>
      <?php foreach ($roster as $student): ?>
        <label class="check"><input type="checkbox" name="members[]" value="<?= (int) $student['id'] ?>" <?= in_array($student['id'], $checked, true) ? 'checked' : '' ?>>
          <span class="mono"><?= esc($student['participant_code']) ?></span></label>
      <?php endforeach ?>
    </fieldset>
    <div class="form-actions span-all"><button class="btn btn-primary" type="submit"><?= icon('check') ?> <?= esc(lang('Admin.fieldSessionMakeup')) ?></button></div>
  </form>
<?php endif ?>
<?= $this->endSection() ?>
