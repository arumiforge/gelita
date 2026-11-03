<?php
/**
 * Buat sesi lapangan — `/admin/sesi-lapangan/baru` (28 §6.1). The school is
 * picked by its research code only; the content opened comes from the
 * study's meeting plan (one region only for the `satu_sesi` plan).
 *
 * @var bool                            $fieldMode
 * @var list<array<string, mixed>>      $schools id, research_code
 * @var list<int>                       $meetings
 * @var bool                            $chooseRegion
 * @var list<\App\Entities\Level>       $regions
 */
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', ['title' => lang('Admin.fieldSessionNew'), 'lead' => lang('Admin.fieldSessionsLead')]) ?>
<?= $this->include('partials/flash') ?>

<?php if (! $fieldMode): ?>
  <div class="alert alert-warn" role="status"><?= icon('warn') ?><p><?= esc(lang('Admin.fieldModeOff')) ?></p></div>
<?php elseif ($schools === []): ?>
  <div class="alert alert-warn" role="status"><?= icon('warn') ?><p><?= esc(lang('Admin.fieldSessionNoSchools')) ?></p></div>
<?php else: ?>
  <form method="post" action="<?= base_url('admin/sesi-lapangan/baru') ?>" class="form form-grid">
    <?= csrf_field() ?>
    <div class="field">
      <label for="fs-school"><?= esc(lang('Admin.fieldSessionSchool')) ?></label>
      <select id="fs-school" name="school_id" required>
        <?php foreach ($schools as $school): ?>
          <option value="<?= (int) $school['id'] ?>" <?= (string) old('school_id') === (string) $school['id'] ? 'selected' : '' ?>><?= esc($school['research_code']) ?></option>
        <?php endforeach ?>
      </select>
    </div>
    <div class="field">
      <label for="fs-rombel"><?= esc(lang('Admin.fieldSessionRombel')) ?></label>
      <input type="text" id="fs-rombel" name="rombel" maxlength="1" pattern="[A-Za-z]" required value="<?= esc((string) (old('rombel') ?? 'A'), 'attr') ?>" class="mono">
    </div>
    <div class="field">
      <label for="fs-meeting"><?= esc(lang('Admin.fieldSessionMeeting')) ?></label>
      <select id="fs-meeting" name="meeting_no" required>
        <?php foreach ($meetings as $no): ?>
          <option value="<?= $no ?>" <?= (string) old('meeting_no') === (string) $no ? 'selected' : '' ?>>P<?= $no ?></option>
        <?php endforeach ?>
      </select>
    </div>
    <?php if ($chooseRegion): ?>
      <div class="field">
        <label for="fs-region"><?= esc(lang('Admin.fieldSessionRegion')) ?></label>
        <select id="fs-region" name="region_code" required>
          <?php foreach ($regions as $level): ?>
            <option value="<?= esc((string) $level->code, 'attr') ?>"><?= esc($level->text('name', 'id')) ?></option>
          <?php endforeach ?>
        </select>
      </div>
    <?php endif ?>
    <div class="field">
      <label for="fs-date"><?= esc(lang('Admin.fieldSessionDate')) ?></label>
      <input type="date" id="fs-date" name="session_date" required value="<?= esc((string) (old('session_date') ?? date('Y-m-d')), 'attr') ?>">
    </div>
    <div class="field">
      <label for="fs-opens"><?= esc(lang('Admin.fieldSessionOpens')) ?></label>
      <input type="time" id="fs-opens" name="opens" required value="<?= esc((string) (old('opens') ?? '07:30'), 'attr') ?>">
    </div>
    <div class="field">
      <label for="fs-closes"><?= esc(lang('Admin.fieldSessionCloses')) ?></label>
      <input type="time" id="fs-closes" name="closes" required value="<?= esc((string) (old('closes') ?? '09:30'), 'attr') ?>">
    </div>
    <div class="form-actions span-all">
      <button class="btn btn-primary" type="submit"><?= icon('check') ?> <?= esc(lang('Admin.fieldSessionNew')) ?></button>
    </div>
  </form>
<?php endif ?>
<?= $this->endSection() ?>
