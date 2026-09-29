<?php
/**
 * Audio — `/admin/media/audio` → MediaController::audioIndex
 *
 * Audio baru selalu berstatus draft dan belum terdengar pemain. Admin
 * mendengarkan pratinjau, membaca transkrip, lalu menyetujui. Transkrip wajib:
 * siswa tanpa audio (atau dengan audio dimatikan) membaca teks yang sama.
 *
 * @var list<array<string, mixed>> $rows       audio_assets + asset_key, storage_path, media_active
 * @var array<int, list<string>>   $usage      audio_assets.id → slide dialog / kartu misi pemakainya
 * @var list<string>               $characters
 * @var list<string>               $locales
 * @var array<string, int>         $drafts     locale → narasi naskah draft (tombol "Setujui semua")
 */
$characterNames = ['jaka' => 'Jaka', 'mbah_kedu' => 'Mbah Kedu'];
$pending        = count(array_filter($rows, static fn (array $row): bool => $row['approval_status'] !== 'approved'));
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php ob_start() ?>
<a class="btn btn-ghost btn-sm" href="<?= base_url('admin/konten/narasi') ?>"><?= icon('message') ?> Rekaman narasi cerita</a>
<a class="btn btn-ghost btn-sm" href="<?= base_url('admin/media') ?>"><?= icon('image') ?> Gambar &amp; video</a>
<?php $actions = ob_get_clean() ?>
<?= component('partials/admin-head', [
    'title'   => 'Rekaman suara',
    'eyebrow' => 'Pengelolaan · suara tokoh & narasi',
    'lead'    => 'Siswa hanya mendengar rekaman yang sudah disetujui. Dengarkan dulu setiap rekaman dan cocokkan dengan teksnya, baru tekan Setujui.',
    'actions' => $actions,
]) ?>
<?= $this->include('partials/flash') ?>

<div class="kpi-grid">
  <?= component('components/stat-tile', ['label' => 'Semua rekaman', 'value' => fmt_num(count($rows)), 'icon' => 'sound']) ?>
  <?= component('components/stat-tile', ['label' => 'Menunggu persetujuan', 'value' => fmt_num($pending), 'icon' => 'clock']) ?>
</div>

<section class="panel">
  <h2 class="panel-title"><?= icon('message') ?> Rekaman narasi cerita</h2>
  <p>Rekaman untuk semua baris cerita diunggah sekaligus di halaman <a href="<?= base_url('admin/konten/narasi/unggah') ?>">Unggah rekaman narasi</a> dan dipantau di halaman <a href="<?= base_url('admin/konten/narasi') ?>">Rekaman narasi</a>. Tombol di bawah hanya menyetujui rekaman narasi cerita, bukan rekaman lain di daftar ini.</p>
  <div class="btn-row">
    <?php foreach ($locales as $code): ?>
      <?= component('admin/narration/approve-all', ['locale' => $code, 'count' => $drafts[$code] ?? 0, 'back' => 'audio']) ?>
    <?php endforeach ?>
  </div>
</section>

<details class="panel" <?= $rows === [] ? 'open' : '' ?>>
  <summary class="panel-title"><?= icon('upload') ?> Unggah satu rekaman baru</summary>
  <form method="post" action="<?= base_url('admin/media/audio/unggah') ?>" class="stack" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="form-grid">
      <div class="field">
        <label for="asset_key">Kode rekaman <span class="req">*</span></label>
        <input type="text" id="asset_key" name="asset_key" required maxlength="160" placeholder="audio.jaka.intro.1.id" spellcheck="false">
        <p class="field-help">Nama unik rekaman, tanpa spasi. Mengunggah ulang dengan kode yang sama akan mengganti rekaman lamanya.</p>
      </div>
      <div class="field">
        <label for="context_code">Tempat diputar <span class="req">*</span></label>
        <input type="text" id="context_code" name="context_code" required maxlength="80" placeholder="intro.1" spellcheck="false">
        <p class="field-help">Catatan singkat di mana rekaman ini dipakai, mis. intro.1 atau mission.tmg-2. Rekaman baru terdengar setelah dipasang di slide (halaman Cerita &amp; dialog) atau di kartu misi (halaman tantangan), lalu disetujui.</p>
      </div>
      <div class="field">
        <label for="locale">Bahasa <span class="req">*</span></label>
        <select id="locale" name="locale" required>
          <?php foreach ($locales as $code): ?>
            <option value="<?= esc($code, 'attr') ?>"><?= esc(admin_label('locale', $code)) ?></option>
          <?php endforeach ?>
        </select>
      </div>
      <div class="field">
        <label for="character_code">Tokoh</label>
        <select id="character_code" name="character_code">
          <option value="">— narator (bukan tokoh) —</option>
          <?php foreach ($characters as $character): ?>
            <option value="<?= esc($character, 'attr') ?>"><?= esc($characterNames[$character] ?? $character) ?></option>
          <?php endforeach ?>
        </select>
      </div>
      <div class="field">
        <label for="production_method">Cara membuat</label>
        <select id="production_method" name="production_method">
          <option value="own_recording">Direkam sendiri</option>
          <option value="tts">Suara buatan komputer (text-to-speech)</option>
        </select>
      </div>
      <div class="field">
        <label for="voice_profile">Pengisi suara</label>
        <input type="text" id="voice_profile" name="voice_profile" maxlength="200" placeholder="Nama pengisi suara atau nama suara komputer">
      </div>
    </div>
    <div class="field">
      <label for="transcript">Teks rekaman <span class="req">*</span></label>
      <textarea id="transcript" name="transcript" rows="4" required minlength="3"></textarea>
      <p class="field-help">Tulis persis seperti yang diucapkan. Teks ini tampil untuk siswa yang mematikan suara.</p>
    </div>
    <div class="field">
      <label for="audio-file">Berkas rekaman <span class="req">*</span></label>
      <input type="file" id="audio-file" name="file" accept="audio/mpeg,audio/ogg,audio/wav,audio/mp4" required>
      <p class="field-help">MP3 (disarankan), OGG, WAV, atau M4A; paling besar 64 MB.</p>
    </div>
    <div class="form-actions">
      <button class="btn btn-primary" type="submit"><?= icon('upload') ?> Unggah (menunggu persetujuan)</button>
    </div>
  </form>
</details>

<section class="panel">
  <h2 class="panel-title"><?= icon('sound') ?> Daftar rekaman</h2>
  <?= component('components/admin-table', [
      'caption'      => 'Daftar rekaman suara',
      'emptyMessage' => 'Belum ada rekaman. Permainan tetap berjalan; siswa membaca teksnya.',
      'rows'         => $rows,
      'rowClass'     => static fn (array $row): string => $row['approval_status'] === 'approved' ? '' : 'is-warn',
      'columns'      => [
          'preview' => ['label' => 'Dengar', 'render' => static fn (array $row): string => '<audio class="audio-preview" controls preload="none" src="' . esc(base_url($row['storage_path']), 'attr') . '" aria-label="Dengar ' . esc($row['asset_key'], 'attr') . '"></audio>'],
          'asset_key' => ['label' => 'Rekaman', 'render' => static function (array $row) use ($characterNames): string {
              $who = $row['character_code'] === null ? 'narator' : ($characterNames[$row['character_code']] ?? $row['character_code']);

              $where = App\Libraries\NarrationCatalog::CONTEXT_LABELS[(string) $row['context_code']] ?? (string) $row['context_code'];

              return '<code>' . esc($row['asset_key']) . '</code><span class="cell-sub">' . esc(admin_label('locale', (string) $row['locale'])) . ' · ' . esc($who) . ' · ' . esc($where) . '</span>';
          }],
          'transcript' => ['label' => 'Teks', 'render' => static fn (array $row): string => '<details class="row-details"><summary><span class="cell-clip">' . esc(mb_strimwidth((string) $row['transcript'], 0, 60, '…')) . '</span></summary><p>' . esc($row['transcript']) . '</p></details>'],
          'usage' => ['label' => 'Dipakai di', 'render' => static function (array $row) use ($usage): string {
              $where = $usage[(int) $row['id']] ?? [];

              if ($where === []) {
                  return '<span class="muted">belum dipasang</span><span class="cell-sub">pasang di halaman Cerita &amp; dialog atau di halaman tantangan</span>';
              }

              return implode('', array_map(static fn (string $label): string => '<span class="cell-sub">' . esc($label) . '</span>', $where));
          }],
          'duration_ms' => ['label' => 'Durasi', 'format' => 'ms'],
          'approval_status' => ['label' => 'Status', 'render' => static function (array $row): string {
              $html = '<span class="badge is-' . esc($row['approval_status'], 'attr') . '">' . esc(admin_label('approval', (string) $row['approval_status'])) . '</span>';

              if (! $row['media_active']) {
                  $html .= '<span class="cell-sub">berkas dinonaktifkan</span>';
              }

              return $html;
          }],
          'actions' => ['label' => '', 'render' => static function (array $row): string {
              if ($row['approval_status'] === 'approved') {
                  return '<span class="cell-sub">disetujui ' . esc(fmt_date($row['approved_at'], false, 'id')) . '</span>';
              }

              return '<form method="post" action="' . esc(base_url('admin/media/audio/' . $row['id'] . '/setujui'), 'attr') . '" class="inline-form">'
                  . csrf_field()
                  . '<button class="btn btn-primary btn-sm" type="submit">' . icon('check') . ' Setujui</button></form>';
          }],
      ],
  ]) ?>
</section>
<?= $this->endSection() ?>
