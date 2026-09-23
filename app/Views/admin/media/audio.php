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
 */
$characterNames = ['jaka' => 'Jaka', 'mbah_kedu' => 'Mbah Kedu'];
$statusNames    = ['draft' => 'draf', 'review' => 'ditinjau', 'approved' => 'disetujui', 'rejected' => 'ditolak'];
$pending        = count(array_filter($rows, static fn (array $row): bool => $row['approval_status'] !== 'approved'));
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php ob_start() ?>
<a class="btn btn-ghost btn-sm" href="<?= base_url('admin/media') ?>"><?= icon('image') ?> Gambar & video</a>
<?php $actions = ob_get_clean() ?>
<?= component('partials/admin-head', [
    'title'   => 'Audio',
    'eyebrow' => 'Pengelolaan · suara tokoh & narasi',
    'lead'    => 'Hanya audio berstatus disetujui dari aset aktif yang dikirim ke pemain. Dengarkan dulu, cocokkan dengan transkrip, baru setujui.',
    'actions' => $actions,
]) ?>
<?= $this->include('partials/flash') ?>

<div class="kpi-grid">
  <?= component('components/stat-tile', ['label' => 'Berkas audio', 'value' => fmt_num(count($rows)), 'icon' => 'sound']) ?>
  <?= component('components/stat-tile', ['label' => 'Menunggu persetujuan', 'value' => fmt_num($pending), 'icon' => 'clock']) ?>
</div>

<details class="panel" <?= $rows === [] ? 'open' : '' ?>>
  <summary class="panel-title"><?= icon('upload') ?> Unggah audio baru</summary>
  <form method="post" action="<?= base_url('admin/media/audio/unggah') ?>" class="stack" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="form-grid">
      <div class="field">
        <label for="asset_key">asset_key <span class="req">*</span></label>
        <input type="text" id="asset_key" name="asset_key" required maxlength="160" placeholder="audio.jaka.intro.1.id" spellcheck="false">
      </div>
      <div class="field">
        <label for="context_code">Konteks <span class="req">*</span></label>
        <input type="text" id="context_code" name="context_code" required maxlength="80" placeholder="intro.1" spellcheck="false">
        <p class="field-help">Label tempat audio diputar, mis. intro.1, level.2.open.3, mission.tmg-2. Audio diputar setelah dipasang ke slide di editor Dialog atau ke kartu misi di editor Tantangan, lalu disetujui.</p>
      </div>
      <div class="field">
        <label for="locale">Bahasa <span class="req">*</span></label>
        <select id="locale" name="locale" required>
          <?php foreach ($locales as $code): ?>
            <option value="<?= esc($code, 'attr') ?>"><?= $code === 'id' ? 'Indonesia' : ($code === 'en' ? 'English' : esc($code)) ?></option>
          <?php endforeach ?>
        </select>
      </div>
      <div class="field">
        <label for="character_code">Tokoh</label>
        <select id="character_code" name="character_code">
          <option value="">— narasi, tanpa tokoh —</option>
          <?php foreach ($characters as $character): ?>
            <option value="<?= esc($character, 'attr') ?>"><?= esc($characterNames[$character] ?? $character) ?></option>
          <?php endforeach ?>
        </select>
      </div>
      <div class="field">
        <label for="production_method">Cara produksi</label>
        <select id="production_method" name="production_method">
          <option value="own_recording">Rekaman sendiri</option>
          <option value="tts">Text-to-speech</option>
        </select>
      </div>
      <div class="field">
        <label for="voice_profile">Profil suara</label>
        <input type="text" id="voice_profile" name="voice_profile" maxlength="200" placeholder="Pengisi suara / nama suara TTS">
      </div>
    </div>
    <div class="field">
      <label for="transcript">Transkrip <span class="req">*</span></label>
      <textarea id="transcript" name="transcript" rows="4" required minlength="3"></textarea>
      <p class="field-help">Tulis persis seperti yang diucapkan. Transkrip ditampilkan sebagai teks bagi siswa yang tidak memutar audio.</p>
    </div>
    <div class="field">
      <label for="audio-file">Berkas audio <span class="req">*</span></label>
      <input type="file" id="audio-file" name="file" accept="audio/mpeg,audio/ogg,audio/wav,audio/mp4" required>
      <p class="field-help">MP3, OGG, WAV, atau M4A; maksimal 64 MB. Durasi terbaca otomatis untuk WAV.</p>
    </div>
    <div class="form-actions">
      <button class="btn btn-primary" type="submit"><?= icon('upload') ?> Unggah sebagai draf</button>
    </div>
  </form>
</details>

<section class="panel">
  <h2 class="panel-title"><?= icon('sound') ?> Daftar audio</h2>
  <?= component('components/admin-table', [
      'caption'      => 'Daftar aset audio',
      'emptyMessage' => 'Belum ada audio. Permainan tetap berjalan dengan teks transkrip.',
      'rows'         => $rows,
      'rowClass'     => static fn (array $row): string => $row['approval_status'] === 'approved' ? '' : 'is-warn',
      'columns'      => [
          'preview' => ['label' => 'Dengar', 'render' => static fn (array $row): string => '<audio class="audio-preview" controls preload="none" src="' . esc(base_url($row['storage_path']), 'attr') . '" aria-label="Pratinjau ' . esc($row['asset_key'], 'attr') . '"></audio>'],
          'asset_key' => ['label' => 'Audio', 'render' => static function (array $row) use ($characterNames): string {
              $who = $row['character_code'] === null ? 'narasi' : ($characterNames[$row['character_code']] ?? $row['character_code']);

              return '<code>' . esc($row['asset_key']) . '</code><span class="cell-sub">' . esc($row['context_code']) . ' · ' . esc(strtoupper((string) $row['locale'])) . ' · ' . esc($who) . '</span>';
          }],
          'transcript' => ['label' => 'Transkrip', 'render' => static fn (array $row): string => '<details class="row-details"><summary><span class="cell-clip">' . esc(mb_strimwidth((string) $row['transcript'], 0, 60, '…')) . '</span></summary><p>' . esc($row['transcript']) . '</p></details>'],
          'usage' => ['label' => 'Dipakai di', 'render' => static function (array $row) use ($usage): string {
              $where = $usage[(int) $row['id']] ?? [];

              if ($where === []) {
                  return '<span class="muted">belum dipasang</span><span class="cell-sub">pasang di editor Dialog atau Tantangan</span>';
              }

              return implode('', array_map(static fn (string $label): string => '<span class="cell-sub">' . esc($label) . '</span>', $where));
          }],
          'duration_ms' => ['label' => 'Durasi', 'format' => 'ms'],
          'approval_status' => ['label' => 'Status', 'render' => static function (array $row) use ($statusNames): string {
              $html = '<span class="badge is-' . esc($row['approval_status'], 'attr') . '">' . esc($statusNames[$row['approval_status']] ?? $row['approval_status']) . '</span>';

              if (! $row['media_active']) {
                  $html .= '<span class="cell-sub">aset nonaktif</span>';
              }

              return $html;
          }],
          'actions' => ['label' => '', 'render' => static function (array $row): string {
              if ($row['approval_status'] === 'approved') {
                  return '<span class="cell-sub">' . esc(fmt_date($row['approved_at'], false, 'id')) . '</span>';
              }

              return '<form method="post" action="' . esc(base_url('admin/media/audio/' . $row['id'] . '/setujui'), 'attr') . '" class="inline-form">'
                  . csrf_field()
                  . '<button class="btn btn-primary btn-sm" type="submit">' . icon('check') . ' Setujui</button></form>';
          }],
      ],
  ]) ?>
</section>
<?= $this->endSection() ?>
