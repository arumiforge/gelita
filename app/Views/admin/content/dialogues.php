<?php
/**
 * Dialog — `/admin/konten/dialog/{levelId}?konteks=…` → ContentController::dialogues
 *
 * Satu konteks naskah (docs/naskah-cerita.md) per halaman. levelId = 0:
 * konteks global `intro`, `map_intro`, `ending`; selain itu konteks wilayah
 * `region_intro`, `level_open`, `level_done`. Teks Indonesia dan English
 * wajib; judul slide, pose, dan efek opsional. Slide nonaktif tetap tampil
 * di sini (bertanda) agar dapat diaktifkan lagi. Audio narasi dipilih dari
 * aset audio; pemain baru mendengarnya setelah audio itu disetujui di
 * halaman Audio.
 *
 * Teks bawaan berasal dari naskah lewat `php spark gelita:story:update`.
 * Slide yang teksnya disunting di sini dilewati perintah itu (kecuali
 * `--force`), jadi suntingan admin tidak tertimpa diam-diam.
 *
 * @var App\Entities\Level|null      $level     null = konteks global
 * @var string                       $context
 * @var list<string>                 $contexts
 * @var list<array<string, mixed>>   $dialogues
 * @var list<string>                 $characters
 * @var array<string, list<string>>  $poses     tokoh → pose sah
 * @var list<string>                 $effects
 */
$characterNames = ['jaka' => 'Jaka', 'mbah_kedu' => 'Mbah Kedu', 'narator' => 'Narator'];
$contextNames   = [
    'intro'        => ['Cerita pembuka', 'Slide cerita yang dibaca siswa sebelum membuka peta Kedu pertama kali.'],
    'map_intro'    => ['Narasi peta', 'Narasi Jaka setelah tirai "Membuka Peta Kedu".'],
    'ending'       => ['Penutup', 'Slide setelah seluruh wilayah tuntas, sebelum Balai Refleksi.'],
    'region_intro' => ['Kenali wilayah', 'Cerita Mbah Kedu dari ikon lentera wilayah ini di peta.'],
    'level_open'   => ['Dialog masuk', 'Percakapan yang tampil saat wilayah ini baru terbuka, satu slide per baris.'],
    'level_done'   => ['Wilayah tuntas', 'Percakapan saat tantangan kelima wilayah ini selesai.'],
];
$poseNames   = [
    'idle' => 'diam', 'happy' => 'senang', 'bow' => 'membungkuk', 'sad' => 'sedih', 'afraid' => 'takut',
    'determined' => 'bertekad', 'smile' => 'tersenyum', 'worried' => 'cemas', 'weak' => 'lemah',
];
$effectNames = [
    'fog' => 'kabut datang', 'fog-lift' => 'kabut tersibak', 'glow' => 'cahaya berdenyut',
    'flash' => 'kilat cahaya', 'shake' => 'layar bergetar', 'dim' => 'layar meredup',
];
$levelId        = $level?->id ?? 0;
$rows   = $dialogues;
$rows[] = null;
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php if ($level !== null): ?>
  <?= component('partials/admin-head', [
      'title'   => $contextNames[$context][0] ?? 'Dialog wilayah',
      'eyebrow' => 'Wilayah ' . $level->sequence . ' · ' . $level->text('name', 'id'),
      'lead'    => $contextNames[$context][1] ?? null,
  ]) ?>
  <?= component('partials/content-nav', ['level' => $level, 'active' => 'dialogues']) ?>
<?php else: ?>
  <?= component('partials/admin-head', [
      'title'   => $contextNames[$context][0] ?? 'Cerita pembuka',
      'eyebrow' => 'Konten umum',
      'lead'    => $contextNames[$context][1] ?? null,
      'actions' => '<a class="btn btn-quiet btn-sm" href="' . base_url('admin/konten') . '">' . icon('left') . ' Semua konten</a>',
  ]) ?>
<?php endif ?>
<?= $this->include('partials/flash') ?>

<p class="field-help">Rekaman narasi seluruh baris naskah dapat diunggah sekaligus dan disetujui massal di halaman <a href="<?= base_url('admin/konten/narasi') ?>">Narasi</a>.</p>

<nav class="btn-row" aria-label="Konteks naskah">
  <?php foreach ($contexts as $code): ?>
    <a class="btn btn-sm <?= $code === $context ? 'btn-primary' : 'btn-ghost' ?>"
       href="<?= base_url('admin/konten/dialog/' . $levelId) ?>?konteks=<?= esc($code, 'url') ?>"
       <?= $code === $context ? 'aria-current="page"' : '' ?>><?= esc($contextNames[$code][0] ?? $code) ?></a>
  <?php endforeach ?>
</nav>

<form method="post" action="<?= base_url('admin/konten/dialog/' . $levelId) ?>" class="stack">
  <?= csrf_field() ?>
  <input type="hidden" name="context" value="<?= esc($context, 'attr') ?>">

  <?php foreach ($rows as $index => $row): ?>
    <?php $isNew = $row === null; $p = 'dlg' . $index; $who = $isNew ? '' : (string) $row['character_code']; ?>
    <fieldset class="repeat-row<?= $isNew ? ' is-new' : '' ?>"<?= $isNew ? '' : ' id="slide-' . (int) $row['sequence'] . '"' ?>>
      <legend>
        <?= icon($isNew ? 'sparkle' : 'message') ?>
        <?= $isNew ? 'Slide baru' : 'Slide ' . esc($row['sequence']) . ' · ' . esc($characterNames[$who] ?? $who) ?>
        <?php if (! $isNew && empty($row['is_active'])): ?><span class="badge is-inactive">nonaktif</span><?php endif ?>
      </legend>
      <?php if (! $isNew): ?>
        <input type="hidden" name="dialogues[<?= $index ?>][id]" value="<?= esc($row['id'], 'attr') ?>">
      <?php endif ?>

      <div class="form-grid">
        <div class="field">
          <label for="<?= $p ?>-seq">Urutan slide</label>
          <input type="number" id="<?= $p ?>-seq" name="dialogues[<?= $index ?>][sequence]" min="1" value="<?= esc($isNew ? count($dialogues) + 1 : $row['sequence'], 'attr') ?>">
        </div>
        <div class="field">
          <label for="<?= $p ?>-char">Tokoh yang berbicara</label>
          <select id="<?= $p ?>-char" name="dialogues[<?= $index ?>][character_code]">
            <?php foreach ($characters as $character): ?>
              <option value="<?= esc($character, 'attr') ?>" <?= $who === $character ? 'selected' : '' ?>><?= esc($characterNames[$character] ?? $character) ?></option>
            <?php endforeach ?>
          </select>
        </div>
        <div class="field">
          <?php $pose = $isNew ? '' : (string) ($row['pose'] ?? ''); ?>
          <label for="<?= $p ?>-pose">Pose tokoh</label>
          <select id="<?= $p ?>-pose" name="dialogues[<?= $index ?>][pose]">
            <option value="">Bawaan (diam; narator tanpa gambar)</option>
            <?php foreach ($poses as $character => $list): ?>
              <?php if ($list === []) {
                  continue;
              } ?>
              <optgroup label="<?= esc($characterNames[$character] ?? $character, 'attr') ?>">
                <?php foreach ($list as $code): ?>
                  <option value="<?= esc($code, 'attr') ?>" <?= $pose === $code && ($who === $character || $who === '') ? 'selected' : '' ?>><?= esc($code) ?> · <?= esc($poseNames[$code] ?? $code) ?></option>
                <?php endforeach ?>
              </optgroup>
            <?php endforeach ?>
          </select>
          <p class="field-help">Pose harus milik tokoh yang berbicara. Gambar pose yang belum diunggah memakai pose diam.</p>
        </div>
        <div class="field">
          <?php $effect = $isNew ? '' : (string) ($row['effect'] ?? ''); ?>
          <label for="<?= $p ?>-effect">Efek layar</label>
          <select id="<?= $p ?>-effect" name="dialogues[<?= $index ?>][effect]">
            <option value="">Tanpa efek</option>
            <?php foreach ($effects as $code): ?>
              <option value="<?= esc($code, 'attr') ?>" <?= $effect === $code ? 'selected' : '' ?>><?= esc($code) ?> · <?= esc($effectNames[$code] ?? $code) ?></option>
            <?php endforeach ?>
          </select>
        </div>
      </div>

      <div class="bilingual">
        <span class="bilingual-label">Judul slide (opsional)</span>
        <div class="field">
          <label for="<?= $p ?>-title"><span class="lang-tag">ID</span> Indonesia</label>
          <input type="text" id="<?= $p ?>-title" name="dialogues[<?= $index ?>][title_id]" maxlength="150" value="<?= esc($isNew ? '' : ($row['title_id'] ?? ''), 'attr') ?>">
        </div>
        <div class="field">
          <label for="<?= $p ?>-title-en"><span class="lang-tag">EN</span> English</label>
          <input type="text" id="<?= $p ?>-title-en" name="dialogues[<?= $index ?>][title_en]" maxlength="150" value="<?= esc($isNew ? '' : ($row['title_en'] ?? ''), 'attr') ?>">
        </div>
      </div>

      <div class="bilingual">
        <span class="bilingual-label">Ucapan<?php if (! $isNew): ?> <span class="req">*</span><?php endif ?></span>
        <div class="field">
          <label for="<?= $p ?>-text"><span class="lang-tag">ID</span> Indonesia</label>
          <textarea id="<?= $p ?>-text" name="dialogues[<?= $index ?>][text_id]" rows="3" <?= $isNew ? '' : 'required' ?>><?= esc($isNew ? '' : ($row['text_id'] ?? '')) ?></textarea>
        </div>
        <div class="field">
          <label for="<?= $p ?>-text-en"><span class="lang-tag">EN</span> English</label>
          <textarea id="<?= $p ?>-text-en" name="dialogues[<?= $index ?>][text_en]" rows="3" <?= $isNew ? '' : 'required' ?>><?= esc($isNew ? '' : ($row['text_en'] ?? '')) ?></textarea>
        </div>
        <?php if ($isNew): ?>
          <p class="field-help">Biarkan ucapan Indonesia kosong bila tidak menambah slide. Bila diisi, ucapan English juga wajib.</p>
        <?php endif ?>
      </div>

      <div class="form-grid">
        <?= component('components/audio-select', [
            'id' => $p . '-audio-id', 'name' => 'dialogues[' . $index . '][audio_id_asset_id]', 'label' => 'Audio narasi (Indonesia)',
            'audioId' => $isNew ? null : $row['audio_id_asset_id'], 'locale' => 'id',
        ]) ?>
        <?= component('components/audio-select', [
            'id' => $p . '-audio-en', 'name' => 'dialogues[' . $index . '][audio_en_asset_id]', 'label' => 'Audio narasi (English)',
            'audioId' => $isNew ? null : $row['audio_en_asset_id'], 'locale' => 'en',
        ]) ?>
      </div>

      <div class="check-row">
        <label class="check"><input type="checkbox" name="dialogues[<?= $index ?>][is_active]" value="1" <?= $isNew || ! empty($row['is_active']) ? 'checked' : '' ?>> Tampil di permainan</label>
      </div>
    </fieldset>
  <?php endforeach ?>

  <div class="form-actions">
    <button class="btn btn-primary" type="submit"><?= icon('check') ?> Simpan dialog</button>
  </div>
</form>
<?= $this->endSection() ?>
