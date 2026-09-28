<?php
/**
 * Unggah narasi — `/admin/konten/narasi/unggah` → NarrationController::uploadForm
 *
 * Banyak rekaman sekaligus: nama berkas = kode berkas baris naskah
 * (intro-01.mp3, kenal-magelang-03.mp3, petunjuk-tmg-4-01.mp3, …). Hasil impor (baru, diganti,
 * sama, nama tidak dikenal beserta saran, gagal, dan baris yang belum punya
 * rekaman) ditampilkan setelah unggah. "Periksa nama berkas saja" menjalankan
 * pencocokan tanpa menyimpan. Tanpa JavaScript formulir tetap bekerja;
 * admin/narration-upload.js hanya memperingatkan bila pilihan melampaui
 * batas PHP sebelum dikirim.
 *
 * Di layar, kode berkas disebut "nama rekaman" dan status draft disebut
 * "menunggu persetujuan". Rincian batas PHP (php.ini) ada di catatan untuk
 * petugas teknis.
 *
 * @var list<string>              $locales
 * @var array<string, int>        $limits  NarrationController::uploadLimits()
 * @var array<string, mixed>|null $report  NarrationImporter::importFiles()/importFolder()
 * @var string                    $folder  folder rekaman di server, relatif public/
 * @var int                       $lines   jumlah baris rekaman per bahasa (naskah + petunjuk arena cari)
 */
$mb       = static fn (int $bytes): string => $bytes <= 0 ? 'tanpa batas' : rtrim(rtrim(number_format($bytes / 1048576, 1, ',', '.'), '0'), ',') . ' MB';
$codeList = static fn (array $codes): string => implode(', ', array_map(static fn (string $code): string => '<code>' . esc($code) . '</code>', $codes));
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php ob_start() ?>
<a class="btn btn-ghost btn-sm" href="<?= base_url('admin/konten/narasi') ?>"><?= icon('left') ?> Rekaman narasi</a>
<a class="btn btn-ghost btn-sm" href="<?= base_url('admin/konten/narasi/daftar-rekaman') ?>"><?= icon('download') ?> Unduh daftar rekaman (Excel)</a>
<?php $actions = ob_get_clean() ?>
<?= component('partials/admin-head', [
    'title'   => 'Unggah rekaman narasi',
    'eyebrow' => 'Konten · suara cerita',
    'lead'    => 'Pilih banyak rekaman sekaligus. Nama setiap berkas harus sama dengan nama rekamannya di daftar rekaman, misalnya intro-01.mp3, dialog-magelang-09.mp3, atau petunjuk-tmg-4-01.mp3 (petunjuk Cari Objek). Rekaman yang diunggah menunggu persetujuan dulu sebelum terdengar oleh siswa.',
    'actions' => $actions,
]) ?>
<?= $this->include('partials/flash') ?>

<?php if (is_array($report)): ?>
  <?php
  $language = 'bahasa ' . admin_label('locale', (string) $report['locale']);
  $source   = $report['source'] === 'folder' ? 'folder server' : 'unggahan';
  ?>
  <section class="panel narration-report stack" aria-labelledby="narration-report-title">
    <h2 class="panel-title" id="narration-report-title">
      <?= icon('list') ?> Hasil <?= $report['dry_run'] ? 'pemeriksaan' : 'unggahan' ?> rekaman <?= esc($language) ?>
      <span class="muted">dari <?= esc($source) ?> · <?= esc($report['files']) ?> berkas</span>
    </h2>
    <?php if ($report['dry_run']): ?>
      <p class="alert alert-info" role="status"><?= icon('info') ?> Hanya diperiksa: belum ada yang disimpan. Untuk menyimpan, unggah lagi tanpa mencentang "Periksa nama berkas saja".</p>
    <?php endif ?>
    <?php if (! empty($report['truncated'])): ?>
      <p class="alert alert-error" role="alert"><?= icon('warn') ?> Server hanya menerima <?= esc($report['truncated']) ?> berkas sekali unggah. Berkas selebihnya tidak ikut terkirim; unggah sisanya pada kesempatan berikutnya.</p>
    <?php endif ?>

    <div class="kpi-grid">
      <?= component('components/stat-tile', ['label' => $report['dry_run'] ? 'Akan ditambahkan' : 'Baru', 'value' => fmt_num(count($report['created'])), 'icon' => 'sparkle']) ?>
      <?= component('components/stat-tile', ['label' => $report['dry_run'] ? 'Akan diganti' : 'Diganti', 'value' => fmt_num(count($report['replaced'])), 'icon' => 'upload', 'hint' => 'perlu disetujui lagi']) ?>
      <?= component('components/stat-tile', ['label' => 'Sama, dilewati', 'value' => fmt_num(count($report['unchanged']) + count($report['kept'])), 'icon' => 'check', 'hint' => 'persetujuan tetap']) ?>
      <?= component('components/stat-tile', ['label' => 'Nama berkas tidak dikenal', 'value' => fmt_num(count($report['unknown'])), 'icon' => 'warn']) ?>
      <?= component('components/stat-tile', ['label' => 'Gagal', 'value' => fmt_num(count($report['failed'])), 'icon' => 'cross']) ?>
      <?= component('components/stat-tile', ['label' => 'Baris belum punya rekaman', 'value' => fmt_num(count($report['missing'])), 'icon' => 'sound', 'hint' => $language]) ?>
    </div>

    <?php if ($report['created'] !== []): ?>
      <p><b><?= $report['dry_run'] ? 'Akan ditambahkan' : 'Rekaman baru' ?>:</b> <?= $codeList($report['created']) ?></p>
    <?php endif ?>
    <?php if ($report['replaced'] !== []): ?>
      <p><b><?= $report['dry_run'] ? 'Akan mengganti rekaman lama' : 'Mengganti rekaman lama (perlu disetujui lagi)' ?>:</b> <?= $codeList($report['replaced']) ?></p>
    <?php endif ?>
    <?php if ($report['unchanged'] !== []): ?>
      <p><b>Sama persis dengan rekaman yang sudah ada (dilewati):</b> <?= $codeList($report['unchanged']) ?></p>
    <?php endif ?>
    <?php if ($report['kept'] !== []): ?>
      <p><b>Dilewati, karena rekaman yang diunggah lewat panel ini lebih baru:</b> <?= $codeList($report['kept']) ?></p>
    <?php endif ?>

    <?php if ($report['unknown'] !== []): ?>
      <h3>Nama berkas tidak dikenal</h3>
      <?= component('components/admin-table', [
          'caption'  => 'Berkas dengan nama yang tidak dikenal',
          'rows'     => $report['unknown'],
          'rowClass' => static fn (): string => 'is-warn',
          'columns'  => [
              'file'       => ['label' => 'Berkas', 'format' => 'code'],
              'reason'     => 'Alasan',
              'suggestion' => ['label' => 'Mungkin maksudnya', 'render' => static fn (array $row): string => $row['suggestion'] === null ? '<span class="muted">—</span>' : '<code>' . esc($row['suggestion']) . '</code>'],
          ],
      ]) ?>
      <p class="field-help">Ganti nama berkasnya sesuai saran atau sesuai nama di daftar rekaman, lalu unggah lagi.</p>
    <?php endif ?>

    <?php if ($report['failed'] !== []): ?>
      <h3>Gagal</h3>
      <?= component('components/admin-table', [
          'caption'  => 'Berkas yang gagal dimasukkan',
          'rows'     => $report['failed'],
          'rowClass' => static fn (): string => 'is-error',
          'columns'  => ['file' => ['label' => 'Berkas', 'format' => 'code'], 'error' => 'Masalah'],
      ]) ?>
    <?php endif ?>

    <?php if ($report['missing'] !== []): ?>
      <details class="row-details">
        <summary><b><?= count($report['missing']) ?> baris naskah belum punya rekaman <?= esc($language) ?></b></summary>
        <p><?= $codeList($report['missing']) ?></p>
      </details>
    <?php else: ?>
      <p class="alert alert-ok" role="status"><?= icon('check') ?> Semua baris naskah sudah punya rekaman <?= esc($language) ?>.</p>
    <?php endif ?>

    <?php if (! $report['dry_run'] && ($report['created'] !== [] || $report['replaced'] !== [])): ?>
      <p>Rekaman yang baru masuk menunggu persetujuan. Dengarkan di halaman <a href="<?= base_url('admin/konten/narasi') ?>">Rekaman narasi</a>, lalu setujui.</p>
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
          <label class="check"><input type="radio" name="locale" value="<?= esc($code, 'attr') ?>" <?= $i === 0 ? 'checked' : '' ?> required> <?= esc(admin_label('locale', $code)) ?></label>
        <?php endforeach ?>
      </div>
    </fieldset>
    <div class="field">
      <label for="narration-files">Berkas rekaman <span class="req">*</span></label>
      <input type="file" id="narration-files" name="files[]" multiple accept="audio/*" required aria-describedby="narration-files-help">
      <p class="field-help" id="narration-files-help">Boleh memilih banyak berkas sekaligus. Jenis berkas: MP3 (disarankan), M4A, OGG, atau WAV. Nama berkas harus sama dengan nama rekamannya; huruf besar atau kecil tidak berpengaruh.</p>
    </div>
    <div class="check-row">
      <label class="check"><input type="checkbox" name="dry_run" value="1"> Periksa nama berkas saja, jangan simpan dulu</label>
    </div>
    <div class="form-actions">
      <button class="btn btn-primary" type="submit"><?= icon('upload') ?> Unggah rekaman</button>
    </div>
  </form>

  <section class="panel">
    <h2 class="panel-title"><?= icon('info') ?> Batas sekali unggah</h2>
    <ul class="plain-list">
      <li><b>Setiap berkas paling besar <?= esc($mb($limits['file_bytes'])) ?>.</b></li>
      <li><b>Sekali unggah paling banyak <?= $limits['max_files'] > 0 ? esc($limits['max_files']) . ' berkas' : 'berapa pun berkas' ?></b>, dengan ukuran total paling besar <b><?= esc($mb($limits['post_bytes'])) ?></b>.</li>
      <li>Satu rekaman MP3 biasanya hanya 0,1–0,5 MB, jadi <?= esc($lines) ?> rekaman untuk satu bahasa biasanya perlu <?= $limits['max_files'] > 0 ? esc((int) ceil(max(1, $lines) / $limits['max_files'])) . ' kali unggah' : 'satu kali unggah' ?>. Supaya mudah dilacak, unggah per bagian cerita (mis. semua berkas <code>dialog-temanggung-…</code>).</li>
      <li>Bila ukuran total terlalu besar, server menolak seluruh kiriman. Kurangi jumlah berkas, lalu coba lagi.</li>
    </ul>
    <details class="row-details">
      <summary>Catatan untuk petugas teknis</summary>
      <p class="field-help">Batas per berkas adalah yang terkecil dari batas GELITA (<code>Config\Gelita::$maxUploadBytes</code>, <?= esc($mb($limits['gelita_bytes'])) ?>) dan PHP (<code>upload_max_filesize</code>, <?= esc($mb($limits['php_file_bytes'])) ?>). Jumlah berkas per unggahan diatur <code>max_file_uploads</code>, ukuran total diatur <code>post_max_size</code>; bila total melebihi <code>post_max_size</code>, PHP membuang seluruh kiriman. Batas PHP diubah di <code>php.ini</code> server (lihat docs/08_DEPLOYMENT.md).</p>
    </details>
  </section>
</div>

<section class="panel">
  <h2 class="panel-title"><?= icon('download') ?> Ambil rekaman yang sudah ada di server</h2>
  <p>Pakai tombol di bawah ini bila petugas teknis sudah menyalin rekaman langsung ke server, sehingga rekaman tidak perlu diunggah ulang. Tombol "Periksa dulu" hanya mencocokkan nama berkas tanpa menyimpan.</p>
  <div class="btn-row">
    <?php foreach ($locales as $code): ?>
      <form method="post" action="<?= base_url('admin/konten/narasi/impor-folder') ?>" class="inline-form">
        <?= csrf_field() ?>
        <input type="hidden" name="locale" value="<?= esc($code, 'attr') ?>">
        <button class="btn btn-ghost btn-sm" type="submit"><?= icon('download') ?> Ambil rekaman <?= esc(admin_label('locale', $code)) ?></button>
      </form>
      <form method="post" action="<?= base_url('admin/konten/narasi/impor-folder') ?>" class="inline-form">
        <?= csrf_field() ?>
        <input type="hidden" name="locale" value="<?= esc($code, 'attr') ?>">
        <input type="hidden" name="dry_run" value="1">
        <button class="btn btn-quiet btn-sm" type="submit">Periksa dulu (<?= esc(admin_label('locale', $code)) ?>)</button>
      </form>
    <?php endforeach ?>
  </div>
  <p class="field-help">Rekaman yang isinya sama dengan yang sudah terpasang dilewati, sehingga persetujuannya tidak hilang. Rekaman yang diunggah lewat panel ini dan lebih baru juga tidak ditimpa.</p>
  <details class="row-details">
    <summary>Catatan untuk petugas teknis</summary>
    <p class="field-help">Folder rekaman di server: <code>public/<?= esc($folder) ?></code> (disalin lewat FTP, git, atau salin berkas). Sama dengan perintah <code>php spark gelita:narration:import</code>.</p>
  </details>
</section>
<?= $this->endSection() ?>
