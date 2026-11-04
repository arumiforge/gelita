<?php
/**
 * Pemeriksaan nama di kritik dan saran (30 §10.2). The words that match a
 * student's name are already `[nama]`; the head researcher may replace other
 * words, then saves. Names are never shown.
 *
 * @var array<string, mixed>       $study
 * @var list<array<string, mixed>> $rows
 * @var list<string>               $fields
 */
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', ['title' => lang('Admin.closureFeedbackTitle'), 'lead' => lang('Admin.closureFeedbackLead')]) ?>
<?= $this->include('partials/flash') ?>

<form method="post" action="<?= base_url('admin/studi/' . $study['id'] . '/penutupan/nama') ?>" class="form">
  <?= csrf_field() ?>
  <?php if ($rows === []): ?><p class="muted"><?= esc(lang('Admin.closureFeedbackNone')) ?></p><?php endif ?>
  <?php foreach ($rows as $row): ?>
    <fieldset class="form-section"><legend class="mono"><?= esc($row['participant_code']) ?></legend>
      <?php foreach ($fields as $field): ?>
        <?php if ($row[$field] === null) continue; ?>
        <div class="field"><label for="fb-<?= (int) $row['id'] ?>-<?= $field ?>"><?= esc(lang('Admin.feedbackField_' . $field)) ?></label>
          <textarea id="fb-<?= (int) $row['id'] ?>-<?= $field ?>" name="texts[<?= (int) $row['id'] ?>][<?= $field ?>]" rows="2"><?= esc($row[$field]) ?></textarea></div>
      <?php endforeach ?>
    </fieldset>
  <?php endforeach ?>
  <div class="form-actions"><button class="btn btn-primary" type="submit"><?= esc(lang('Admin.closureFeedbackSave')) ?></button></div>
</form>
<?= $this->endSection() ?>
