<?php
/**
 * Periksa paket — `/periksa-paket` → PackageController::inspect
 * (docs/rencana-paket-aset.md Stage 3, §5.5, §6.3, §8.1, §11.2).
 *
 * The teacher's check of this device before class (game/package-inspect.js):
 * the package state on this device and on the server, storage, a full sha256
 * re-check of every file (core/package.js verify()), a download of whatever is
 * missing or broken (download()), "Simpan permanen" (persist()), and
 * "Hapus paket di perangkat ini" (clear(), after a confirmation). Open
 * without login, also while package mode is off (decision log, Stage 3).
 *
 * Stage 16:9: one full-width ornate panel 32, 88, 1216 × 600 (stage.css .inspect-panel),
 * head, scrolling body (facts, "Simpan permanen" beside its fact,
 * "Hapus paket di perangkat ini" below the result), button bar (last 80 dp)
 * with the two main tasks. Teacher tone (no "kamu").
 * `data-state` on the section (loading | idle | busy) is for browser checks.
 */
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc(lang('Package.inspectTitle')) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= media_key_src('bg.welcome') ?? '' ?><?= $this->endSection() ?>
<?= $this->section('hudBack') ?><?= base_url() ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="inspect" data-screen="package-inspect" data-state="loading">
  <div class="inspect-panel ornate-panel stage-box">
    <?= $this->include('partials/ornate-frame') ?>
    <header class="register-head prepare-head">
      <span class="eyebrow"><?= esc(lang('Package.inspectEyebrow')) ?></span>
      <h1><?= esc(lang('Package.inspectTitle')) ?></h1>
    </header>

    <div class="prepare-body stage-scroll">
      <p class="prepare-lead"><?= esc(lang('Package.inspectLead')) ?></p>

      <dl class="inspect-facts">
        <div><dt><?= esc(lang('Package.inspectState')) ?></dt><dd data-inspect="state">…</dd></div>
        <div><dt><?= esc(lang('Package.inspectDevice')) ?></dt><dd data-inspect="device">…</dd></div>
        <div><dt><?= esc(lang('Package.inspectServer')) ?></dt><dd data-inspect="server">…</dd></div>
        <div><dt><?= esc(lang('Package.inspectStorage')) ?></dt><dd data-inspect="storage">…</dd></div>
        <div>
          <dt><?= esc(lang('Package.inspectPersisted')) ?></dt>
          <dd class="inspect-persisted">
            <span data-inspect="persisted">…</span>
            <button type="button" class="btn btn-ghost btn-sm" data-inspect-action="persist" disabled><?= esc(lang('Package.inspectPersist')) ?></button>
          </dd>
        </div>
      </dl>

      <div class="progress prepare-bar" role="progressbar" aria-label="<?= esc(lang('Package.inspectTitle'), 'attr') ?>"
           aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" data-inspect-bar hidden><i></i></div>
      <p class="prepare-status" role="status" aria-live="polite" data-inspect-status></p>
      <p class="inspect-keys" data-inspect-keys hidden></p>

      <noscript><p class="prepare-status"><?= esc(lang('Package.inspectNoScript')) ?></p></noscript>

      <p class="inspect-clear">
        <button type="button" class="btn btn-danger btn-sm" data-inspect-action="clear" disabled><?= icon('trash') ?> <?= esc(lang('Package.inspectClear')) ?></button>
      </p>
    </div>

    <div class="register-actions inspect-actions">
      <button type="button" class="btn btn-ghost" data-inspect-action="verify" disabled><?= icon('check') ?> <?= esc(lang('Package.inspectVerify')) ?></button>
      <button type="button" class="btn btn-primary" data-inspect-action="repair" disabled><?= icon('download') ?> <?= esc(lang('Package.inspectRepair')) ?></button>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
