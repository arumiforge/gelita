<?php
/**
 * Field sheets of one field session — `/admin/sesi-lapangan/{id}/lembar/unggah`
 * (revision 10, part 3: 29 §10.3–§10.5): state per expected sheet, the
 * upload form (first or second typing), and the preview before saving.
 * Only codes are shown; the typed content of saved sheets is for the
 * `ketua` download only.
 *
 * @var array<string, mixed>      $fieldSession
 * @var list<array<string, mixed>> $status  FieldSheetService::status()
 * @var array<string, mixed>|null  $preview FieldSheetService::preview()
 */
$id   = (int) $fieldSession['id'];
$base = 'admin/sesi-lapangan/' . $id;
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title'   => (string) $fieldSession['code'] . ' · ' . lang('Admin.fieldSheetUpload'),
    'lead'    => lang('Admin.fieldSheetUploadHelp'),
    'actions' => '<a class="btn" href="' . base_url($base . '/lembar') . '">' . icon('download') . ' ' . esc(lang('Admin.fieldSheetTemplate')) . '</a>',
]) ?>
<?= $this->include('partials/flash') ?>

<table class="data-table">
  <tbody>
    <?php foreach ($status as $row): ?>
      <tr>
        <td><?= esc($row['title']) ?></td>
        <td>
          <span class="badge"><?= esc(lang('Admin.fieldSheetStatus' . ucfirst($row['state']))) ?></span>
          <?php if ($row['overdue']): ?><span class="badge is-failed"><?= esc(lang('Admin.fieldSheetStatusOverdue')) ?></span><?php endif ?>
        </td>
        <td><?= $row['main'] === null ? '' : esc(fmt_date((string) $row['main']['created_at'])) ?></td>
        <td><?= $row['share'] === null ? '' : esc($row['share'] . ' %') ?></td>
      </tr>
    <?php endforeach ?>
  </tbody>
</table>

<form method="post" action="<?= base_url($base . '/lembar/unggah') ?>" enctype="multipart/form-data" class="form-section">
  <?= csrf_field() ?>
  <div class="field">
    <label class="label" for="sheet-file"><?= esc(lang('Admin.fieldSheetUpload')) ?></label>
    <input type="file" id="sheet-file" name="sheet_file" accept=".xlsx,.csv" required>
  </div>
  <label class="check"><input type="checkbox" name="purpose" value="check"> <span><?= esc(lang('Admin.fieldSheetCheckUpload')) ?></span></label>
  <div class="form-actions"><button class="btn btn-primary" type="submit"><?= icon('upload') ?> <?= esc(lang('Admin.fieldSheetPreviewTitle')) ?></button></div>
</form>

<?php if ($preview !== null): ?>
  <section class="form-section" id="pratinjau">
    <h2><?= esc(lang('Admin.fieldSheetPreviewTitle')) ?></h2>
    <?php foreach ($preview['sheets'] as $sheet => $result): ?>
      <h3><?= esc(App\Libraries\FieldSheetSpec::title($sheet)) ?> · <?= esc(lang('Admin.fieldSheetRows', [count($result['rows'])])) ?></h3>
      <?php if ($result['errors'] !== []): ?>
        <p><b><?= esc(lang('Admin.fieldSheetErrors')) ?></b></p>
        <ul class="field-errors"><?php foreach ($result['errors'] as [$row, $column, $message]): ?>
          <li><?= esc(lang('Admin.fieldSheetRowError', [App\Services\FieldSheetService::rowLabel($row), $column, $message])) ?></li>
        <?php endforeach ?></ul>
      <?php endif ?>
      <?php if ($result['warnings'] !== [] || $result['names'] !== []): ?>
        <p><b><?= esc(lang('Admin.fieldSheetWarnings')) ?></b></p>
        <ul><?php foreach ($result['warnings'] as $warning): ?><li><?= esc($warning) ?></li><?php endforeach ?>
          <?php foreach ($result['names'] as [$row, $column]): ?><li><?= esc(lang('Admin.fieldSheetNameWarn', [App\Services\FieldSheetService::rowLabel($row), $column])) ?></li><?php endforeach ?></ul>
      <?php endif ?>
      <?php if (isset($result['compare'])): ?>
        <?php if ($result['compare']['missing_main']): ?>
          <p><?= esc(lang('Admin.fieldSheetNoMain', [App\Libraries\FieldSheetSpec::title($sheet)])) ?></p>
        <?php else: ?>
          <p><?= esc(lang('Admin.fieldSheetCheckResult', [$result['compare']['checked'], $result['compare']['total'], count($result['compare']['diffs'])])) ?></p>
          <ul><?php foreach ($result['compare']['diffs'] as [$row, $column, $first, $second]): ?>
            <li><?= esc(lang('Admin.fieldSheetDiff', [App\Services\FieldSheetService::rowLabel($row), $column, $first, $second])) ?></li>
          <?php endforeach ?></ul>
        <?php endif ?>
      <?php endif ?>
    <?php endforeach ?>

    <?php if ((int) $preview['errors'] === 0): ?>
      <form method="post" action="<?= base_url($base . '/lembar/simpan') ?>">
        <?= csrf_field() ?>
        <?php if ((int) $preview['names'] > 0): ?>
          <label class="check"><input type="checkbox" name="names_confirmed" value="1" required> <span><?= esc(lang('Admin.fieldSheetNameConfirm')) ?></span></label>
        <?php endif ?>
        <div class="form-actions"><button class="btn btn-primary" type="submit"><?= esc(lang($preview['purpose'] === 'check' ? 'Admin.fieldSheetSaveCheck' : 'Admin.fieldSheetSave')) ?></button></div>
      </form>
    <?php else: ?>
      <p class="field-error"><?= esc(lang('Admin.fieldSheetHasErrors')) ?></p>
    <?php endif ?>
  </section>
<?php endif ?>
<?= $this->endSection() ?>
