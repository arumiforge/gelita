<?php
/**
 * Rilis konten — `/admin/studi/rilis` → StudyController::releases
 *
 * Satu rilis aktif pada satu waktu. Setiap sesi mencatat release_id-nya,
 * sehingga analisis pretest–posttest dapat memisahkan sesi yang dimainkan
 * dengan konten atau skoring berbeda. Rilis baru selalu dibuat nonaktif.
 * Di layar, "rilis" disebut versi permainan.
 *
 * @var list<array<string, mixed>> $releases
 * @var array<string, mixed>|null  $active
 */
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php ob_start() ?>
<form method="post" action="<?= base_url('admin/konten/verifikasi') ?>" class="inline-form">
  <?= csrf_field() ?>
  <button class="btn btn-ghost btn-sm" type="submit"><?= icon('check') ?> Periksa kelengkapan konten dulu</button>
</form>
<?php $actions = ob_get_clean() ?>
<?= component('partials/admin-head', [
    'title'   => 'Versi permainan',
    'eyebrow' => 'Pengelolaan · pengaturan penelitian',
    'lead'    => 'Setiap sesi siswa mencatat versi permainan yang sedang aktif, supaya hasil pretest dan posttest hanya dibandingkan bila soal dan cara penilaiannya sama. Buat versi baru bila soal atau aturan penilaian berubah, dan aktifkan hanya di antara dua gelombang pengambilan data — jangan di tengahnya.',
    'actions' => $actions,
]) ?>
<ul class="sub-nav">
  <li><a href="<?= base_url('admin/studi') ?>"><?= icon('flask') ?> Studi & fase</a></li>
  <li><a href="<?= base_url('admin/studi/rilis') ?>" aria-current="page"><?= icon('list') ?> Versi permainan</a></li>
  <li><a href="<?= base_url('admin/studi/skoring') ?>"><?= icon('target') ?> Aturan penilaian</a></li>
</ul>
<?= $this->include('partials/flash') ?>

<?php if ($active === null): ?>
  <p class="alert alert-error" role="alert"><?= icon('warn') ?> Belum ada versi permainan yang aktif. Buat dan aktifkan satu versi agar sesi bermain baru tercatat dengan jelas.</p>
<?php else: ?>
  <div class="kpi-grid">
    <?= component('components/stat-tile', ['label' => 'Versi aktif', 'value' => $active['release_code'], 'icon' => 'play', 'hint' => $active['published_at'] ? 'sejak ' . fmt_date($active['published_at'], false, 'id') : null]) ?>
    <?= component('components/stat-tile', ['label' => 'Versi konten', 'value' => $active['content_version'], 'icon' => 'book', 'hint' => 'soal, cerita, dan bacaan']) ?>
    <?= component('components/stat-tile', ['label' => 'Versi aturan penilaian', 'value' => $active['scoring_version'], 'icon' => 'target']) ?>
    <?= component('components/stat-tile', ['label' => 'Versi aplikasi', 'value' => $active['app_version'], 'icon' => 'grid']) ?>
  </div>
<?php endif ?>

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
          'asset_version'   => 'Gambar & suara',
          'scoring_version' => 'Penilaian',
          'published_at'    => ['label' => 'Diaktifkan', 'format' => 'datetime'],
          'actions'         => ['label' => '', 'render' => static function (array $row): string {
              if ($row['is_active']) {
                  return '<span class="badge is-active">' . icon('check') . ' aktif</span>';
              }

              return '<details class="confirm confirm-inline">'
                  . '<summary class="btn btn-ghost btn-sm">Aktifkan</summary>'
                  . '<div class="confirm-box"><p>Versi yang aktif sekarang akan dinonaktifkan, dan sesi bermain baru memakai <code>' . esc($row['release_code']) . '</code>.</p>'
                  . '<form method="post" action="' . esc(base_url('admin/studi/rilis/' . $row['id'] . '/aktifkan'), 'attr') . '" class="inline-form">'
                  . csrf_field()
                  . '<button class="btn btn-primary btn-sm" type="submit">' . icon('check') . ' Ya, aktifkan</button></form></div>'
                  . '</details>';
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
      </div>
      <div class="field">
        <label for="scoring_version">Versi aturan penilaian <span class="req">*</span></label>
        <input type="text" id="scoring_version" name="scoring_version" required maxlength="20" value="<?= esc($active['scoring_version'] ?? '', 'attr') ?>">
        <p class="field-help">Samakan dengan versi aturan penilaian yang aktif (lihat menu Aturan penilaian).</p>
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
