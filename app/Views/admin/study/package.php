<?php
/**
 * Rilis → Paket aset — `/admin/studi/rilis/paket` → PackageController::index
 * (docs/rencana-paket-aset.md §6.3, §7.2, §8.7, §9.2).
 *
 * One switch, **Mode paket: Nyala / Mati**. Switching on starts a build that
 * this page drives batch by batch (js/admin/package-build.js), then shows the
 * build summary; publishing it creates and activates a release that names
 * the package. Switching off activates a release without one. While a study
 * is running both need a tick and a reason (ReleaseActivation).
 *
 * @var bool                                      $enabled      server kill switch not set
 * @var string|null                               $activeCode   package the active release names
 * @var array<string, mixed>|null                 $active       AssetPackageService::summary() of it
 * @var array{ok: bool, issues: list<array<string, string>>, checked_at: string}|null $verification
 * @var array<string, mixed>|null                 $building     asset_packages row + progress
 * @var array<string, mixed>|null                 $pending      summary() of the build waiting for confirmation
 * @var array{count: int, package_code: string|null} $changed
 * @var bool                                      $onlineLab
 * @var list<array<string, mixed>>                $history
 * @var string|null                               $preview      package this staff member previews
 * @var array<string, mixed>|null                 $runningStudy
 */
$mb = static fn (int|string|null $bytes): string => fmt_num(((int) $bytes) / 1048576, 1) . ' MB';

$groupLabel = static function (string $group): string {
    if (str_starts_with($group, 'wilayah-')) {
        return 'Wilayah ' . ucfirst(substr($group, 8));
    }

    return admin_label('packageGroup', $group);
};

$previewUrl = static fn (string $code): string => base_url('?paket=' . rawurlencode($code));
$isOn       = $activeCode !== null;
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php ob_start() ?>
<a class="btn" href="<?= base_url('admin/studi/rilis') ?>"><?= icon('left') ?> <?= esc(lang('Admin.packageBackToReleases')) ?></a>
<?php $actions = ob_get_clean() ?>
<?= component('partials/admin-head', [
    'title'   => lang('Admin.packageTitle'),
    'eyebrow' => lang('Admin.menuReleases'),
    'lead'    => lang('Admin.packageLead'),
    'actions' => $actions,
]) ?>
<?= $this->include('partials/flash') ?>

<?php if (! $enabled): ?>
  <div class="alert alert-error" role="alert" data-package-killed>
    <?= icon('warn') ?>
    <div><p><b><?= esc(lang('Admin.packageKilled')) ?></b></p><p><?= esc(lang('Admin.packageKilledHelp')) ?></p></div>
  </div>
<?php endif ?>

<?php if ($building === null): ?>
  <?= component('admin/partials/package-notice', ['packageChanged' => $changed]) ?>
<?php endif ?>

<section class="panel package-switch" aria-labelledby="package-mode-title">
  <div class="panel-head">
    <h2 class="panel-title" id="package-mode-title"><?= icon('download') ?> <?= esc(lang('Admin.packageMode')) ?></h2>
    <span class="badge <?= $isOn && $enabled ? 'is-ok' : 'is-muted' ?>" data-package-mode="<?= $isOn && $enabled ? 'on' : 'off' ?>">
      <?= esc(lang($isOn && $enabled ? 'Admin.packageOn' : 'Admin.packageOff')) ?><?= $isOn && ! $enabled ? ' · ' . esc(lang('Admin.packageKilled')) : '' ?>
    </span>
  </div>

  <?php if ($isOn && $active !== null): ?>
    <div class="kpi-grid">
      <?= component('components/stat-tile', ['label' => lang('Admin.packageActiveCode'), 'value' => $activeCode, 'icon' => 'tag']) ?>
      <?= component('components/stat-tile', ['label' => lang('Admin.packageSize'), 'value' => $mb($active['total_bytes']), 'icon' => 'download']) ?>
      <?= component('components/stat-tile', ['label' => lang('Admin.packageFiles'), 'value' => fmt_num($active['file_count']), 'icon' => 'list']) ?>
      <?= component('components/stat-tile', ['label' => lang('Admin.packageBuiltAt'), 'value' => $active['built_at'] ? fmt_date($active['built_at'], false, 'id') : '—', 'icon' => 'clock']) ?>
    </div>
    <p class="muted"><?= esc(lang('Admin.packageOnHelp')) ?></p>
    <?php if ($verification !== null): ?>
      <div class="package-verify">
        <p class="<?= $verification['ok'] ? 'is-ok' : 'is-bad' ?>">
          <?= icon($verification['ok'] ? 'check' : 'warn') ?>
          <?= esc($verification['ok']
              ? lang('Admin.packageVerified', [fmt_date($verification['checked_at'], false, 'id')])
              : lang('Admin.packageVerifyIssues', [count($verification['issues']), fmt_date($verification['checked_at'], false, 'id')])) ?>
        </p>
        <form method="post" action="<?= base_url('admin/studi/rilis/paket/periksa') ?>" class="inline-form">
          <?= csrf_field() ?>
          <button class="btn btn-sm" type="submit"><?= icon('search') ?> <?= esc(lang('Admin.packageVerifyNow')) ?></button>
        </form>
      </div>
    <?php endif ?>
  <?php else: ?>
    <p class="muted"><?= esc(lang('Admin.packageOffHelp')) ?></p>
  <?php endif ?>

  <?php if ($building === null && $pending === null): ?>
    <div class="form-actions">
      <?php if ($isOn): ?>
        <form method="post" action="<?= base_url('admin/studi/rilis/paket/matikan') ?>" class="stack" data-package-off
              data-confirm="<?= esc(lang('Admin.packageTurnOffConfirm'), 'attr') ?>" data-confirm-text="<?= esc(lang('Admin.packageTurnOffText'), 'attr') ?>"
              data-confirm-ok="<?= esc(lang('Admin.packageTurnOff'), 'attr') ?>" data-confirm-style="danger">
          <?= csrf_field() ?>
          <?php if ($runningStudy !== null): ?>
            <?= component('admin/partials/study-confirm-fields', ['study' => $runningStudy, 'idPrefix' => 'off']) ?>
          <?php endif ?>
          <div><button class="btn btn-danger" type="submit"><?= icon('cross') ?> <?= esc(lang('Admin.packageTurnOff')) ?></button></div>
        </form>
      <?php else: ?>
        <form method="post" action="<?= base_url('admin/studi/rilis/paket/bangun') ?>" class="stack" data-package-on>
          <?= csrf_field() ?>
          <p class="field-help"><?= esc(lang('Admin.packageTurnOnHelp')) ?></p>
          <div><button class="btn btn-primary" type="submit"><?= icon('play') ?> <?= esc(lang('Admin.packageTurnOn')) ?></button></div>
        </form>
      <?php endif ?>
    </div>
  <?php endif ?>
</section>

<?php if ($building !== null): ?>
  <?php
    $progress = $building['progress'];
    $percent  = $progress['total'] > 0 ? (int) floor($progress['done'] * 100 / $progress['total']) : 0;
    $stepUrl  = base_url('admin/studi/rilis/paket/bangun/' . $building['package_code'] . '/lanjut');
  ?>
  <section class="panel package-build" aria-labelledby="package-build-title"
           data-package-build="<?= esc($building['package_code'], 'attr') ?>"
           data-step-url="<?= esc($stepUrl, 'attr') ?>"
           data-done-url="<?= esc(base_url('admin/studi/rilis/paket'), 'attr') ?>"
           data-text-progress="<?= esc(lang('Admin.packageProgress', ['{done}', '{total}']), 'attr') ?>"
           data-text-done="<?= esc(lang('Admin.packageBuildDone'), 'attr') ?>"
           data-text-error="<?= esc(lang('Admin.packageBuildError'), 'attr') ?>"
           data-text-retry="<?= esc(lang('Admin.packageRetry'), 'attr') ?>">
    <h2 class="panel-title" id="package-build-title"><?= icon('clock') ?> <?= esc(lang('Admin.packageBuilding')) ?> <code><?= esc($building['package_code']) ?></code></h2>
    <div class="progress package-progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= $percent ?>" aria-labelledby="package-build-title">
      <i style="width: <?= $percent ?>%"></i>
    </div>
    <p class="package-progress-text" aria-live="polite" data-package-progress-text><?= esc(lang('Admin.packageProgress', [fmt_num($progress['done']), fmt_num($progress['total'])])) ?></p>
    <p class="muted"><?= esc(lang('Admin.packageBuildingHelp')) ?></p>
    <form method="post" action="<?= esc($stepUrl, 'attr') ?>" class="inline-form" data-package-continue>
      <?= csrf_field() ?>
      <button class="btn btn-primary" type="submit"><?= icon('play') ?> <?= esc(lang('Admin.packageContinue')) ?></button>
    </form>
  </section>
<?php endif ?>

<?php if ($pending !== null): ?>
  <section class="panel package-summary" aria-labelledby="package-summary-title" data-package-pending="<?= esc($pending['package_code'], 'attr') ?>">
    <div class="panel-head">
      <h2 class="panel-title" id="package-summary-title"><?= icon('clipboard') ?> <?= esc(lang('Admin.packageSummary')) ?> <code><?= esc($pending['package_code']) ?></code></h2>
      <span class="badge is-info"><?= esc(admin_label('packageStatus', $pending['status'])) ?></span>
    </div>
    <p class="muted"><?= esc(lang('Admin.packageSummaryHelp')) ?></p>
    <div class="kpi-grid">
      <?= component('components/stat-tile', ['label' => lang('Admin.packageSize'), 'value' => $mb($pending['total_bytes']), 'icon' => 'download']) ?>
      <?= component('components/stat-tile', ['label' => lang('Admin.packageFiles'), 'value' => fmt_num($pending['file_count']), 'icon' => 'list']) ?>
      <?= component('components/stat-tile', [
          'label' => lang('Admin.packageNewFiles'),
          'value' => fmt_num($pending['new_files']) . ' · ' . $mb($pending['new_bytes']),
          'icon'  => 'upload',
          'hint'  => $pending['compared_with'] !== null ? lang('Admin.packageNewFilesHint', [$pending['compared_with']]) : lang('Admin.packageNewFilesAll'),
      ]) ?>
      <?= component('components/stat-tile', ['label' => lang('Admin.packageBuiltAt'), 'value' => $pending['built_at'] ? fmt_date($pending['built_at'], false, 'id') : '—', 'icon' => 'clock']) ?>
    </div>

    <div class="package-summary-grid">
      <div>
        <h3 class="panel-subtitle"><?= esc(lang('Admin.packageGroups')) ?></h3>
        <table class="data-table package-groups">
          <thead><tr><th scope="col"><?= esc(lang('Admin.packageGroupColumn')) ?></th><th scope="col" class="is-num"><?= esc(lang('Admin.packageSize')) ?></th></tr></thead>
          <tbody>
            <?php foreach ($pending['groups'] as $group => $bytes): ?>
              <tr><td><?= esc($groupLabel((string) $group)) ?></td><td class="is-num"><?= esc($mb($bytes)) ?></td></tr>
            <?php endforeach ?>
          </tbody>
        </table>
      </div>
      <div>
        <h3 class="panel-subtitle"><?= esc(lang('Admin.packageWarnings')) ?></h3>
        <?php if ($pending['warnings'] === []): ?>
          <p class="muted"><?= esc(lang('Admin.packageNoWarnings')) ?></p>
        <?php else: ?>
          <?php $byCode = []; foreach ($pending['warnings'] as $warning) { $byCode[(string) $warning['code']][] = (string) $warning['key']; } ?>
          <ul class="package-warnings">
            <?php foreach ($byCode as $code => $keys): ?>
              <li>
                <b><?= esc(admin_label('packageWarning', $code)) ?>: <?= fmt_num(count($keys)) ?></b>
                <span class="cell-sub">
                  <?php foreach (array_slice($keys, 0, 8) as $key): ?><code><?= esc($key) ?></code> <?php endforeach ?>
                  <?= count($keys) > 8 ? esc(lang('Admin.packageMoreKeys', [fmt_num(count($keys) - 8)])) : '' ?>
                </span>
              </li>
            <?php endforeach ?>
          </ul>
        <?php endif ?>
      </div>
    </div>

    <form method="post" action="<?= base_url('admin/studi/rilis/paket/' . $pending['package_code'] . '/terbitkan') ?>" class="stack" data-package-publish
          data-confirm="<?= esc(lang('Admin.packagePublishConfirm', [$pending['package_code']]), 'attr') ?>"
          data-confirm-text="<?= esc(lang('Admin.packagePublishText'), 'attr') ?>"
          data-confirm-ok="<?= esc(lang($isOn ? 'Admin.packagePublish' : 'Admin.packagePublishOn'), 'attr') ?>">
      <?= csrf_field() ?>
      <?php if ($runningStudy !== null): ?>
        <?= component('admin/partials/study-confirm-fields', ['study' => $runningStudy, 'idPrefix' => 'publish']) ?>
      <?php endif ?>
      <div class="form-actions">
        <a class="btn" href="<?= esc($previewUrl($pending['package_code']), 'attr') ?>" target="_blank" rel="noopener"><?= icon('eye') ?> <?= esc(lang('Admin.packagePreview')) ?></a>
        <button class="btn btn-primary" type="submit"><?= icon('check') ?> <?= esc(lang($isOn ? 'Admin.packagePublish' : 'Admin.packagePublishOn')) ?></button>
      </div>
    </form>
  </section>
<?php endif ?>

<section class="panel" aria-labelledby="package-lab-title">
  <h2 class="panel-title" id="package-lab-title"><?= icon('building') ?> <?= esc(lang('Admin.packageOnlineLab')) ?></h2>
  <form method="post" action="<?= base_url('admin/studi/rilis/paket/izin-daring') ?>" class="stack" data-package-online-lab>
    <?= csrf_field() ?>
    <input type="hidden" name="online_lab_allowed" value="0">
    <label class="check" for="online-lab">
      <input type="checkbox" id="online-lab" name="online_lab_allowed" value="1" <?= $onlineLab ? 'checked' : '' ?>>
      <span><?= esc(lang('Admin.packageOnlineLab')) ?></span>
    </label>
    <p class="field-help"><?= esc(lang('Admin.packageOnlineLabHelp')) ?></p>
    <div><button class="btn btn-sm" type="submit"><?= icon('check') ?> <?= esc(lang('Admin.packageSave')) ?></button></div>
  </form>
</section>

<section class="panel" aria-labelledby="package-history-title">
  <h2 class="panel-title" id="package-history-title"><?= icon('list') ?> <?= esc(lang('Admin.packageHistory')) ?></h2>
  <p class="muted"><?= esc(lang('Admin.packagePreviewHelp')) ?></p>
  <?php if ($preview !== null): ?>
    <div class="alert alert-info" role="status">
      <?= icon('eye') ?>
      <p><?= esc(lang('Admin.packagePreviewNow', [$preview])) ?>
        <a href="<?= esc(base_url('?paket=mati'), 'attr') ?>"><?= esc(lang('Admin.packagePreviewStop')) ?></a></p>
    </div>
  <?php endif ?>
  <?= component('components/admin-table', [
      'caption'      => lang('Admin.packageHistory'),
      'emptyMessage' => lang('Admin.packageHistoryEmpty'),
      'rows'         => $history,
      'rowClass'     => static fn (array $row): string => $row['package_code'] === $activeCode ? 'is-good' : '',
      'columns'      => [
          'package_code' => ['label' => lang('Admin.packageTitle'), 'render' => static fn (array $row): string => '<code>' . esc($row['package_code']) . '</code>' . ($row['notes'] ? '<span class="cell-sub">' . esc($row['notes']) . '</span>' : '')],
          'status'       => ['label' => lang('Admin.packageStatusColumn'), 'render' => static fn (array $row): string => esc(admin_label('packageStatus', (string) $row['status']))],
          'total_bytes'  => ['label' => lang('Admin.packageSize'), 'render' => static fn (array $row): string => esc($mb($row['total_bytes']))],
          'file_count'   => ['label' => lang('Admin.packageFiles'), 'render' => static fn (array $row): string => esc(fmt_num($row['file_count']))],
          'built_at'     => ['label' => lang('Admin.packageBuiltAt'), 'render' => static fn (array $row): string => $row['built_at'] ? esc(fmt_date($row['built_at'], false, 'id')) : '—'],
          'published_at' => ['label' => lang('Admin.packagePublishedAt'), 'render' => static fn (array $row): string => $row['published_at'] ? esc(fmt_date($row['published_at'], false, 'id')) : '—'],
          'actions'      => ['label' => '', 'render' => static fn (array $row): string => '<a class="btn btn-sm" href="' . esc($previewUrl((string) $row['package_code']), 'attr') . '" target="_blank" rel="noopener">' . icon('eye') . ' ' . esc(lang('Admin.packagePreview')) . '</a>'],
      ],
  ]) ?>
</section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<?php if ($building !== null): ?>
  <script type="module" src="<?= asset_url_versioned('js/admin/package-build.js') ?>"></script>
<?php endif ?>
<?= $this->endSection() ?>
