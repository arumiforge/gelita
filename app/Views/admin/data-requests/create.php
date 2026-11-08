<?php
/**
 * Ajukan permintaan hapus — `/admin/permintaan-data/baru` (30 §7.1 step 1).
 * After the code is checked the form shows the school code, class group, and
 * status to compare with L7; never a name.
 *
 * @var string                    $code
 * @var array<string, mixed>|null $found DataRequestService::lookup()
 */
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', ['title' => lang('Admin.requestNew'), 'lead' => lang('Admin.requestNewLead')]) ?>
<?= $this->include('partials/flash') ?>

<form method="get" action="<?= base_url('admin/permintaan-data/baru') ?>" class="form form-inline">
  <div class="field">
    <label for="req-lookup">Kode peserta</label>
    <input type="text" id="req-lookup" name="kode" value="<?= esc($code, 'attr') ?>" autocomplete="off" required>
  </div>
  <button class="btn btn-sm" type="submit"><?= esc(lang('Admin.requestLookup')) ?></button>
</form>

<?php if ($code !== '' && $found === null): ?>
  <div class="alert alert-error" role="alert"><?= icon('warn') ?><p><?= esc(lang('Admin.resetCodeNotFound')) ?></p></div>
<?php elseif ($found !== null): ?>
  <dl class="detail-list">
    <dt>Kode peserta</dt><dd class="mono"><?= esc($found['participant_code']) ?></dd>
    <dt>Kode sekolah</dt><dd class="mono"><?= esc($found['school_ref']) ?></dd>
    <dt>Rombel</dt><dd><?= esc((string) ($found['rombel'] ?? '—')) ?></dd>
    <dt><?= esc(lang('Admin.consentStatus')) ?></dt><dd><?= esc(\App\Services\ConsentService::statusLabel($found['status'])) ?></dd>
  </dl>

  <form method="post" action="<?= base_url('admin/permintaan-data/baru') ?>" class="form">
    <?= csrf_field() ?>
    <input type="hidden" name="participant_code" value="<?= esc($found['participant_code'], 'attr') ?>">
    <fieldset class="field">
      <legend><?= esc(lang('Admin.requestKind')) ?></legend>
      <?php foreach (\App\Services\DataRequestService::KINDS as $kind): ?>
        <label class="check"><input type="radio" name="kind" value="<?= $kind ?>" required <?= old('kind') === $kind ? 'checked' : '' ?>> <span><?= esc(lang('Admin.requestKind_' . $kind)) ?></span></label>
      <?php endforeach ?>
    </fieldset>
    <div class="field">
      <label for="req-received"><?= esc(lang('Admin.requestReceived')) ?></label>
      <input type="datetime-local" id="req-received" name="received_at" value="<?= esc((string) old('received_at'), 'attr') ?>" required>
    </div>
    <div class="field">
      <label for="req-via"><?= esc(lang('Admin.requestVia')) ?></label>
      <select id="req-via" name="received_via" required>
        <?php foreach (\App\Services\DataRequestService::VIA as $via): ?>
          <option value="<?= $via ?>" <?= old('received_via') === $via ? 'selected' : '' ?>><?= esc(lang('Admin.requestVia_' . $via)) ?></option>
        <?php endforeach ?>
      </select>
    </div>
    <div class="field">
      <label class="check"><input type="checkbox" name="verified" value="1" required> <span><?= esc(lang('Admin.requestVerified')) ?></span></label>
      <label for="req-verified"><?= esc(lang('Admin.requestVerifiedAt')) ?></label>
      <input type="datetime-local" id="req-verified" name="verified_at" value="<?= esc((string) old('verified_at'), 'attr') ?>" required>
    </div>
    <div class="form-actions"><button class="btn btn-primary" type="submit"><?= esc(lang('Admin.requestNew')) ?></button></div>
  </form>
<?php endif ?>
<?= $this->endSection() ?>
