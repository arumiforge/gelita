<?php
/**
 * Rilis konten — `/admin/studi/rilis` → StudyController::releases
 *
 * Satu rilis aktif pada satu waktu. Setiap sesi mencatat release_id-nya,
 * sehingga analisis dapat memisahkan sesi yang dimainkan dengan konten atau
 * aturan bintang berbeda. Rilis baru selalu dibuat nonaktif.
 * Di layar, "rilis" disebut versi permainan.
 *
 * @var list<array<string, mixed>> $releases
 * @var array<string, mixed>|null  $active
 *
 * Asset package (docs/rencana-paket-aset.md §6.3, §7.2): a status line with a
 * link to Rilis → Paket aset, the changed-asset notice, and the running-study
 * lock. While a study is running, a release that names another package than
 * the active one opens a confirmation page instead of activating at once.
 *
 * @var string|null                $packageCode     package the active release names
 * @var bool                       $packageOn       package mode on for students
 * @var bool                       $killSwitch      server kill switch set
 * @var array<string, mixed>       $changed         AssetPackageService::changedSinceActive()
 * @var array<string, mixed>|null  $runningStudy
 * @var array<int, string|null>    $releasePackages release id => package it names
 */
$needsConfirm = static fn (array $row): bool => $runningStudy !== null
    && ($releasePackages[(int) $row['id']] ?? null) !== $packageCode;
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php ob_start() ?>
<form method="post" action="<?= base_url('admin/konten/verifikasi') ?>" class="inline-form">
  <?= csrf_field() ?>
  <button class="btn" type="submit"><?= icon('check') ?> Periksa kelengkapan konten dulu</button>
</form>
<?php $actions = ob_get_clean() ?>
<?= component('partials/admin-head', [
    'title'   => 'Rilis',
    'lead'    => 'Setiap sesi siswa mencatat versi permainan yang sedang aktif, supaya data sesi hanya dibandingkan bila soal dan aturan bintangnya sama. Buat versi baru bila soal atau aturan bintang berubah, dan aktifkan hanya di antara dua gelombang pengambilan data — jangan di tengahnya.',
    'actions' => $actions,
]) ?>
<?= $this->include('partials/flash') ?>

<?php if ($active === null): ?>
  <div class="alert alert-error" role="alert"><?= icon('warn') ?><p>Belum ada versi permainan yang aktif. Buat dan aktifkan satu versi agar sesi bermain baru tercatat dengan jelas.</p></div>
<?php else: ?>
  <div class="kpi-grid">
    <?= component('components/stat-tile', ['label' => 'Versi aktif', 'value' => $active['release_code'], 'icon' => 'play', 'hint' => $active['published_at'] ? 'sejak ' . fmt_date($active['published_at'], false, 'id') : null]) ?>
    <?= component('components/stat-tile', ['label' => 'Versi konten', 'value' => $active['content_version'], 'icon' => 'book', 'hint' => 'soal, cerita, dan bacaan']) ?>
    <?= component('components/stat-tile', ['label' => 'Versi aturan bintang', 'value' => $active['scoring_version'], 'icon' => 'target']) ?>
    <?= component('components/stat-tile', ['label' => 'Versi aplikasi', 'value' => $active['app_version'], 'icon' => 'grid']) ?>
  </div>
<?php endif ?>

<section class="panel" data-release-package>
  <div class="panel-head">
    <h2 class="panel-title"><?= icon('download') ?> <?= esc(lang('Admin.packageMode')) ?></h2>
    <span class="badge <?= $packageOn ? 'is-ok' : 'is-muted' ?>"><?= esc(lang($packageOn ? 'Admin.packageOn' : 'Admin.packageOff')) ?><?= $killSwitch ? ' · ' . esc(lang('Admin.packageKilled')) : '' ?></span>
  </div>
  <p class="muted">
    <?php if ($packageCode !== null): ?>
      <?= esc(lang('Admin.packageActiveCode')) ?>: <code><?= esc($packageCode) ?></code>.
    <?php endif ?>
    <?= esc(lang($packageCode !== null ? 'Admin.packageOnHelp' : 'Admin.packageOffHelp')) ?>
  </p>
  <div class="form-actions"><a class="btn" href="<?= base_url('admin/studi/rilis/paket') ?>"><?= icon('download') ?> <?= esc(lang('Admin.packageOpen')) ?></a></div>
</section>
<?= component('admin/partials/package-notice', ['packageChanged' => $changed]) ?>

<section class="panel">
  <h2 class="panel-title"><?= icon('list') ?> Semua versi</h2>
  <?= component('components/admin-table', [
      'caption'      => 'Daftar versi permainan',
      'emptyMessage' => 'Belum ada versi. Buat versi pertama di bawah.',
      'rows'         => $releases,
      'rowClass'     => static fn (array $row): string => $row['is_active'] ? 'is-good' : '',
      'columns'      => [
          'release_code'    => ['label' => 'Nama versi', 'render' => static fn (array $row): string => '<code>' . esc($row['release_code']) . '</code>' . ($row['notes'] ? '<span class="cell-sub">' . esc($row['notes']) . '</span>' : '')],
          'app_version'     => 'Aplikasi',
          'content_version' => 'Konten',
          'asset_version'   => ['label' => 'Gambar & suara', 'render' => static fn (array $row): string => ($releasePackages[(int) $row['id']] ?? null) !== null
              ? '<code>' . esc($row['asset_version']) . '</code>'
              : '<span class="muted">' . esc(lang('Admin.releaseNoPackage')) . '</span>'],
          'scoring_version' => 'Aturan bintang',
          'published_at'    => ['label' => 'Diaktifkan', 'format' => 'datetime'],
          'actions'         => ['label' => '', 'render' => static function (array $row) use ($needsConfirm): string {
              if ($row['is_active']) {
                  return '<span class="badge is-active">' . icon('check') . ' aktif</span>';
              }

              // Another asset package during a running study: confirmation page with a reason (§7.2)
              if ($needsConfirm($row)) {
                  return '<a class="btn btn-sm" href="' . esc(base_url('admin/studi/rilis/' . $row['id'] . '/aktifkan'), 'attr') . '">' . icon('check') . ' ' . esc(lang('Admin.releaseActivateMore')) . '</a>';
              }

              return '<form method="post" action="' . esc(base_url('admin/studi/rilis/' . $row['id'] . '/aktifkan'), 'attr') . '" class="inline-form"'
                  . ' data-confirm="Aktifkan versi ' . esc($row['release_code'], 'attr') . '?" data-confirm-text="Versi yang aktif sekarang dinonaktifkan; sesi bermain baru memakai versi ini." data-confirm-ok="Aktifkan">'
                  . csrf_field()
                  . '<button class="btn btn-sm" type="submit">' . icon('check') . ' Aktifkan</button></form>';
          }],
      ],
  ]) ?>
</section>

<details class="form-section" <?= $releases === [] ? 'open' : '' ?>>
  <summary class="panel-title"><?= icon('sparkle') ?> Buat versi baru</summary>
  <form method="post" action="<?= base_url('admin/studi/rilis') ?>" class="stack">
    <?= csrf_field() ?>
    <p class="muted">Isian di bawah sudah diisi dengan versi yang aktif sekarang. Ubah angka yang berubah saja, beri nama versi baru, lalu simpan. Versi baru belum aktif sampai Anda menekan Aktifkan.</p>
    <div class="form-grid">
      <div class="field">
        <label for="release_code">Nama versi <span class="req">*</span></label>
        <input type="text" id="release_code" name="release_code" required maxlength="50" spellcheck="false" placeholder="2026.10-a">
        <p class="field-help">Bebas tetapi unik, mis. tanggal: 2026.10-a.</p>
      </div>
      <div class="field">
        <label for="app_version">Versi aplikasi <span class="req">*</span></label>
        <input type="text" id="app_version" name="app_version" required maxlength="20" value="<?= esc($active['app_version'] ?? '', 'attr') ?>">
      </div>
      <div class="field">
        <label for="content_version">Versi konten <span class="req">*</span></label>
        <input type="text" id="content_version" name="content_version" required maxlength="20" value="<?= esc($active['content_version'] ?? '', 'attr') ?>">
        <p class="field-help">Naikkan bila soal, cerita, atau bacaan berubah.</p>
      </div>
      <div class="field">
        <label for="asset_version">Versi gambar & suara</label>
        <input type="text" id="asset_version" name="asset_version" maxlength="20" value="<?= esc($active['asset_version'] ?? '1', 'attr') ?>">
        <p class="field-help"><?= esc(lang('Admin.releaseAssetVersionHelp')) ?></p>
      </div>
      <div class="field">
        <label for="scoring_version">Versi aturan bintang <span class="req">*</span></label>
        <input type="text" id="scoring_version" name="scoring_version" required maxlength="20" value="<?= esc($active['scoring_version'] ?? '', 'attr') ?>">
        <p class="field-help">Naikkan bila aturan bintang berubah.</p>
      </div>
      <div class="field span-all">
        <label for="notes">Apa yang berubah?</label>
        <textarea id="notes" name="notes" rows="2" placeholder="Mis. 4 soal Magelang diperbaiki, tambah halaman Pustaka"></textarea>
      </div>
    </div>
    <div class="form-actions">
      <button class="btn btn-primary" type="submit"><?= icon('check') ?> Simpan versi (belum aktif)</button>
    </div>
  </form>
</details>
<?= $this->endSection() ?>
