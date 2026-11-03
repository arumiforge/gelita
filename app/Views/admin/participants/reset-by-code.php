<?php
/**
 * Atur ulang kata sandi siswa lewat kode peserta — `/admin/peserta/reset-sandi`
 * → ParticipantController::resetByCodeForm (revision 10, part 1, 27 §10).
 *
 * For `peneliti` and `ketua`: the coordinator types the participant code
 * from L2. No name or username is shown anywhere in this flow.
 *
 * @var string $code
 */
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title' => lang('Admin.menuResetByCode'),
    'lead'  => lang('Admin.resetByCodeHelp'),
]) ?>
<?= $this->include('partials/flash') ?>

<section class="form-section danger-zone" id="reset-sandi">
  <form method="post" action="<?= base_url('admin/peserta/reset-sandi') ?>" class="form"
        data-confirm="Buat kata sandi sementara?" data-confirm-text="Kata sandi lama siswa ini langsung tidak berlaku." data-confirm-ok="Buat kata sandi" data-confirm-style="danger">
    <?= csrf_field() ?>
    <div class="field">
      <label for="participant_code">Kode peserta</label>
      <input type="text" id="participant_code" name="participant_code" value="<?= esc($code, 'attr') ?>" required
             autocomplete="off" spellcheck="false" autocapitalize="characters" class="mono">
    </div>
    <div class="field">
      <label for="confirm">Ketik <code>RESET</code> (huruf besar) untuk memastikan</label>
      <input type="text" id="confirm" name="confirm" class="confirm-input" required pattern="RESET" autocomplete="off" spellcheck="false">
    </div>
    <div class="form-actions">
      <button class="btn btn-danger" type="submit"><?= icon('key') ?> Buat kata sandi sementara</button>
    </div>
  </form>
</section>
<?= $this->endSection() ?>
