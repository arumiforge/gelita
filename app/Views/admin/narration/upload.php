<?php
/**
 * Unggah narasi — `/admin/konten/narasi/unggah` → NarrationController::uploadForm
 *
 * Banyak rekaman sekaligus: nama berkas = kode berkas baris naskah
 * (intro-01.mp3, kenal-magelang-03.mp3, …). Hasil impor (baru, diganti,
 * sama, nama tidak dikenal beserta saran, gagal, dan baris yang belum punya
 * rekaman) ditampilkan setelah unggah. "Periksa nama saja" menjalankan
 * pencocokan tanpa menyimpan. Tanpa JavaScript formulir tetap bekerja;
 * admin/narration-upload.js hanya memperingatkan bila pilihan melampaui
 * batas PHP sebelum dikirim.
 *
 * @var list<string>              $locales
 * @var array<string, int>        $limits  NarrationController::uploadLimits()
 * @var array<string, mixed>|null $report  NarrationImporter::importFiles()/importFolder()
 * @var string                    $folder  folder rekaman di server, relatif public/
 */
$localeNames = ['id' => 'Indonesia', 'en' => 'English'];
$mb          = static fn (int $bytes): string => $bytes <= 0 ? 'tanpa batas' : rtrim(rtrim(number_format($bytes / 1048576, 1, ',', '.'), '0'), ',') . ' MB';
$codeList    = static fn (array $codes): string => implode(', ', array_map(static fn (string $code): string => '<code>' . esc($code) . '</code>', $codes));
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php ob_start() ?>
<a class="btn btn-ghost btn-sm" href="<?= base_url('admin/konten/narasi') ?>"><?= icon('left') ?> Narasi</a>
<a class="btn btn-ghost btn-sm" href="<?= base_url('admin/konten/narasi/daftar-rekaman') ?>"><?= icon('download') ?> Unduh daftar rekaman</a>
<?php $actions = ob_get_clean() ?>
<?= component('partials/admin-head', [
    'title'   => 'Unggah narasi',
    'eyebrow' => 'Konten · rekaman naskah cerita',
    'lead'    => 'Pilih banyak rekaman sekaligus. Nama setiap berkas harus sama dengan kode berkas baris naskah, misalnya intro-01.mp3 atau dialog-magelang-09.mp3. Rekaman masuk sebagai draft.',
    'actions' => $actions,
]) ?>
<?= $this->include('partials/flash') ?>

<?php if (is_array($report)): ?>
  <?php
  $lang   = strtoupper((string) $report['locale']);
  $source = $report['source'] === 'folder' ? 'folder server' : 'unggahan';
  ?>
  <section class="panel narration-report stack" aria-labelledby="narration-report-title">
    <h2 class="panel-title" id="narration-report-title">
      <?= icon('list') ?> Hasil <?= $report['dry_run'] ? 'pemeriksaan' : 'impor' ?> narasi <?= esc($lang) ?>
      <span class="muted">dari <?= esc($source) ?> · <?= esc($report['files']) ?> berkas</span>
    </h2>
    <?php if ($report['dry_run']): ?>
      <p class="alert alert-info" role="status"><?= icon('info') ?> Pemeriksaan saja: tidak ada yang disimpan. Kirim ulang tanpa centang "Periksa nama saja" untuk mengimpor.</p>
    <?php endif ?>
    <?php if (! empty($report['truncated'])): ?>
      <p class="alert alert-error" role="alert"><?= icon('warn') ?> PHP hanya menerima <?= esc($report['truncated']) ?> berkas per unggahan (max_file_uploads). Berkas di atas jumlah itu tidak terkirim; unggah sisanya dalam kelompok berikutnya.</p>
    <?php endif ?>

    <div class="kpi-grid">
      <?= component('components/stat-tile', ['label' => $report['dry_run'] ? 'Akan dibuat' : 'Baru', 'value' => fmt_num(count($report['created'])), 'icon' => 'sparkle']) ?>
      <?= component('components/stat-tile', ['label' => $report['dry_run'] ? 'Akan diganti' : 'Diganti (kembali draft)', 'value' => fmt_num(count($report['replaced'])), 'icon' => 'upload']) ?>
      <?= component('components/stat-tile', ['label' => 'Sama, dilewati', 'value' => fmt_num(count($report['unchanged']) + count($report['kept'])), 'icon' => 'check', 'hint' => 'persetujuan tetap']) ?>
      <?= component('components/stat-tile', ['label' => 'Nama tidak dikenal', 'value' => fmt_num(count($report['unknown'])), 'icon' => 'warn']) ?>
      <?= component('components/stat-tile', ['label' => 'Gagal', 'value' => fmt_num(count($report['failed'])), 'icon' => 'cross']) ?>
      <?= component('components/stat-tile', ['label' => 'Baris belum punya rekaman ' . $lang, 'value' => fmt_num(count($report['missing'])), 'icon' => 'sound']) ?>
    </div>

    <?php if ($report['created'] !== []): ?>
      <p><b><?= $report['dry_run'] ? 'Akan dibuat' : 'Baru' ?>:</b> <?= $codeList($report['created']) ?></p>
    <?php endif ?>
    <?php if ($report['replaced'] !== []): ?>
      <p><b><?= $report['dry_run'] ? 'Akan diganti' : 'Diganti, kembali ke draft' ?>:</b> <?= $codeList($report['replaced']) ?></p>
    <?php endif ?>
    <?php if ($report['unchanged'] !== []): ?>
      <p><b>Sama persis dengan rekaman terpasang (dilewati):</b> <?= $codeList($report['unchanged']) ?></p>
    <?php endif ?>
    <?php if ($report['kept'] !== []): ?>
      <p><b>Dilewati, rekaman yang diunggah lewat panel lebih baru:</b> <?= $codeList($report['kept']) ?></p>
    <?php endif ?>

    <?php if ($report['unknown'] !== []): ?>
      <h3>Nama tidak dikenal</h3>
      <?= component('components/admin-table', [
          'caption'  => 'Berkas dengan nama tidak dikenal',
          'rows'     => $report['unknown'],
          'rowClass' => static fn (): string => 'is-warn',
          'columns'  => [
              'file'       => ['label' => 'Berkas', 'format' => 'code'],
              'reason'     => 'Alasan',
              'suggestion' => ['label' => 'Maksudnya?', 'render' => static fn (array $row): string => $row['suggestion'] === null ? '<span class="muted">—</span>' : '<code>' . esc($row['suggestion']) . '</code>'],
          ],
      ]) ?>
      <p class="field-help">Ganti nama berkasnya sesuai saran atau kode di daftar rekaman, lalu unggah lagi.</p>
    <?php endif ?>

    <?php if ($report['failed'] !== []): ?>
      <h3>Gagal</h3>
      <?= component('components/admin-table', [
          'caption'  => 'Berkas yang gagal diimpor',
          'rows'     => $report['failed'],
          'rowClass' => static fn (): string => 'is-error',
          'columns'  => ['file' => ['label' => 'Berkas', 'format' => 'code'], 'error' => 'Masalah'],
      ]) ?>
    <?php endif ?>

    <?php if ($report['missing'] !== []): ?>
      <details class="row-details">
        <summary><b><?= count($report['missing']) ?> baris naskah belum punya rekaman <?= esc($lang) ?></b></summary>
        <p><?= $codeList($report['missing']) ?></p>
      </details>
    <?php else: ?>
      <p class="alert alert-ok" role="status"><?= icon('check') ?> Semua baris naskah sudah punya rekaman <?= esc($lang) ?>.</p>
    <?php endif ?>

    <?php if (! $report['dry_run'] && ($report['created'] !== [] || $report['replaced'] !== [])): ?>
      <p>Rekaman baru berstatus draft. Dengarkan di halaman <a href="<?= base_url('admin/konten/narasi') ?>">Narasi</a>, lalu setujui.</p>
    <?php endif ?>
  </section>
<?php endif ?>

<div class="split-grid">
  <form method="post" action="<?= base_url('admin/konten/narasi/unggah') ?>" class="upload-box stack" enctype="multipart/form-data"
        data-narration-upload data-max-files="<?= esc($limits['max_files'], 'attr') ?>"
        data-max-file-bytes="<?= esc($limits['file_bytes'], 'attr') ?>" data-max-post-bytes="<?= esc($limits['post_bytes'], 'attr') ?>">
    <?= csrf_field() ?>
    <h2 class="panel-title"><?= icon('upload') ?> Unggah rekaman</h2>
    <fieldset class="field">
      <legend>Bahasa rekaman <span class="req">*</span></legend>
      <div class="check-row">
        <?php foreach ($locales as $i => $code): ?>
          <label class="check"><input type="radio" name="locale" value="<?= esc($code, 'attr') ?>" <?= $i === 0 ? 'checked' : '' ?> required> <?= esc($localeNames[$code] ?? $code) ?> (<?= esc(strtoupper($code)) ?>)</label>
        <?php endforeach ?>
      </div>
    </fieldset>
    <div class="field">
      <label for="narration-files">Berkas rekaman <span class="req">*</span></label>
      <input type="file" id="narration-files" name="files[]" multiple accept="audio/*" required aria-describedby="narration-files-help">
      <p class="field-help" id="narration-files-help">MP3 (disarankan), M4A, OGG, atau WAV. Nama berkas = kode berkas, huruf besar/kecil tidak berpengaruh.</p>
    </div>
    <div class="check-row">
      <label class="check"><input type="checkbox" name="dry_run" value="1"> Periksa nama saja, jangan simpan</label>
    </div>
    <div class="form-actions">
      <button class="btn btn-primary" type="submit"><?= icon('upload') ?> Unggah sebagai draft</button>
    </div>
  </form>

  <section class="panel">
    <h2 class="panel-title"><?= icon('info') ?> Batas unggahan server ini</h2>
    <ul class="plain-list">
      <li><b>Per berkas: <?= esc($mb($limits['file_bytes'])) ?></b>, yang terkecil dari batas GELITA (<code>Config\Gelita::$maxUploadBytes</code>, <?= esc($mb($limits['gelita_bytes'])) ?>) dan PHP (<code>upload_max_filesize</code>, <?= esc($mb($limits['php_file_bytes'])) ?>).</li>
      <li><b>Per unggahan: <?= $limits['max_files'] > 0 ? esc($limits['max_files']) . ' berkas' : 'tanpa batas jumlah' ?></b> (<code>max_file_uploads</code>) dan <b><?= esc($mb($limits['post_bytes'])) ?> total</b> (<code>post_max_size</code>).</li>
      <li>Rekaman MP3 mono 64–96 kbps berukuran ±0,1–0,5 MB per baris, jadi 88 baris satu bahasa biasanya perlu <?= $limits['max_files'] > 0 ? esc((int) ceil(88 / $limits['max_files'])) . ' kali unggah' : 'satu kali unggah' ?>. Pilih berkas per konteks (mis. semua <code>dialog-temanggung-*</code>) agar mudah dilacak.</li>
      <li>Bila jumlah total melebihi <code>post_max_size</code>, PHP membuang seluruh kiriman dan halaman menolak permintaan. Kurangi jumlah berkas per unggahan.</li>
    </ul>
    <p class="field-help">Batas PHP diubah di <code>php.ini</code> server (lihat docs/08_DEPLOYMENT.md).</p>
  </section>
</div>

<section class="panel">
  <h2 class="panel-title"><?= icon('download') ?> Impor dari folder server</h2>
  <p>Bila rekaman sudah disalin ke server (FTP, git, atau salin berkas) di <code>public/<?= esc($folder) ?></code>, impor tanpa mengunggah ulang. Sama dengan perintah <code>php spark gelita:narration:import</code>.</p>
  <div class="btn-row">
    <?php foreach ($locales as $code): ?>
      <form method="post" action="<?= base_url('admin/konten/narasi/impor-folder') ?>" class="inline-form">
        <?= csrf_field() ?>
        <input type="hidden" name="locale" value="<?= esc($code, 'attr') ?>">
        <button class="btn btn-ghost btn-sm" type="submit"><?= icon('download') ?> Impor folder <?= esc(strtoupper($code)) ?></button>
      </form>
      <form method="post" action="<?= base_url('admin/konten/narasi/impor-folder') ?>" class="inline-form">
        <?= csrf_field() ?>
        <input type="hidden" name="locale" value="<?= esc($code, 'attr') ?>">
        <input type="hidden" name="dry_run" value="1">
        <button class="btn btn-quiet btn-sm" type="submit">Periksa folder <?= esc(strtoupper($code)) ?></button>
      </form>
    <?php endforeach ?>
  </div>
  <p class="field-help">Berkas yang isinya sama dengan rekaman terpasang dilewati sehingga persetujuannya tidak hilang. Rekaman yang diunggah lewat panel dan lebih baru dari berkas folder juga tidak ditimpa.</p>
</section>
<?= $this->endSection() ?>
