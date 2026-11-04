<?php
/**
 * Penutupan studi — `/admin/studi/{id}/penutupan` (30 §10.2). The four
 * conditions as ticks; *Hitung* only when all hold; then `GANTI`.
 *
 * @var array<string, mixed>                          $study
 * @var array<string, bool>                           $conditions
 * @var array{participants: int, schools: int}|null   $preview
 * @var string                                        $confirm
 */
$ready = ! in_array(false, $conditions, true);
$base  = 'admin/studi/' . $study['id'] . '/penutupan';
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', ['title' => lang('Admin.closureTitle') . ' · ' . $study['code'], 'lead' => lang('Admin.closureLead')]) ?>
<?= $this->include('partials/flash') ?>

<?php if ($study['identities_replaced_at'] !== null): ?>
  <p class="alert alert-ok"><?= icon('check') ?> <?= esc(lang('Admin.closureReplaced')) ?></p>
<?php else: ?>
  <ul class="checklist">
    <?php foreach ($conditions as $key => $ok): ?>
      <li><?= icon($ok ? 'check' : 'warn') ?> <?= esc(lang('Admin.closureCondition_' . $key)) ?></li>
    <?php endforeach ?>
  </ul>
  <p class="form-actions">
    <?php if (staff_can('research.export')): ?>
      <a class="btn btn-sm" href="<?= base_url('admin/ekspor/izin?studi=' . $study['id']) ?>"><?= esc(lang('Admin.consentRegisterDownload')) ?></a>
      <a class="btn btn-sm" href="<?= base_url($base . '/nama') ?>"><?= esc(lang('Admin.closureFeedbackTitle')) ?></a>
    <?php endif ?>
  </p>

  <?php if (staff_can('identities.replace')): ?>
    <form method="post" action="<?= base_url($base . '/hitung') ?>" class="form-inline"><?= csrf_field() ?>
      <button class="btn" type="submit" <?= $ready ? '' : 'disabled' ?>><?= esc(lang('Admin.closureCount')) ?></button>
    </form>
    <?php if ($preview !== null): ?>
      <section class="form-section danger-zone">
        <p><?= esc(lang('Admin.closurePreview', [$preview['participants'], $preview['schools']])) ?></p>
        <form method="post" action="<?= base_url($base . '/jalankan') ?>" class="form"><?= csrf_field() ?>
          <div class="field"><label for="closure-confirm"><?= esc(lang('Admin.closureTypeWord', [$confirm])) ?></label>
            <input type="text" id="closure-confirm" name="confirm" required pattern="<?= esc($confirm, 'attr') ?>" autocomplete="off"></div>
          <div class="form-actions"><button class="btn btn-danger" type="submit"><?= esc(lang('Admin.closureRun')) ?></button></div>
        </form>
      </section>
    <?php endif ?>
  <?php endif ?>
<?php endif ?>
<?= $this->endSection() ?>
