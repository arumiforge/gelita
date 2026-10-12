<?php
/**
 * Activate a release during a running study — `/admin/studi/rilis/{id}/aktifkan`
 * (GET) → StudyController::confirmActivation (docs/rencana-paket-aset.md §7.2).
 *
 * Shown when the release names another asset package than the one students
 * get now. The form posts to the same address with the confirmation tick and
 * the reason that ReleaseActivation requires.
 *
 * @var array<string, mixed> $release
 * @var array{study: array<string, mixed>, package_from: string|null, package_to: string|null} $lock
 */
$package = static fn (?string $code): string => $code === null ? lang('Admin.releaseNoPackage') : $code;
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title'   => lang('Admin.releaseConfirmTitle'),
    'eyebrow' => lang('Admin.menuReleases'),
    'lead'    => lang('Admin.releaseConfirmLead', [$release['release_code']]),
]) ?>
<?= $this->include('partials/flash') ?>

<section class="panel">
  <dl class="detail-grid">
    <div><dt><?= esc(lang('Admin.releasePackageFrom')) ?></dt><dd><code><?= esc($package($lock['package_from'])) ?></code></dd></div>
    <div><dt><?= esc(lang('Admin.releasePackageTo')) ?></dt><dd><code><?= esc($package($lock['package_to'])) ?></code></dd></div>
  </dl>
  <form method="post" action="<?= base_url('admin/studi/rilis/' . (int) $release['id'] . '/aktifkan') ?>" class="stack">
    <?= csrf_field() ?>
    <?= component('admin/partials/study-confirm-fields', ['study' => $lock['study'], 'idPrefix' => 'release']) ?>
    <div class="form-actions">
      <a class="btn btn-quiet" href="<?= base_url('admin/studi/rilis') ?>"><?= esc(lang('Admin.releaseCancel')) ?></a>
      <button class="btn btn-primary" type="submit"><?= icon('check') ?> <?= esc(lang('Admin.releaseActivate')) ?></button>
    </div>
  </form>
</section>
<?= $this->endSection() ?>
