<?php
/**
 * Dialog — `/admin/konten/dialog/{levelId}` → ContentController::dialogues
 *
 * Slide percakapan Jaka & Mbah Kedu yang tampil sebelum peta wilayah
 * (context_code `level_open`), atau cerita pembuka permainan (`intro`) bila
 * levelId = 0. Teks Indonesia dan English wajib; judul slide opsional.
 * Slide nonaktif tetap tampil di sini (bertanda) agar dapat diaktifkan lagi.
 * Audio narasi dipilih dari aset audio; pemain baru mendengarnya setelah
 * audio itu disetujui di halaman Audio.
 *
 * @var App\Entities\Level|null   $level  null = cerita pembuka
 * @var list<array<string, mixed>> $dialogues
 * @var list<string>               $characters
 */
$characterNames = ['jaka' => 'Jaka', 'mbah_kedu' => 'Mbah Kedu', 'narator' => 'Narator'];
$levelId        = $level?->id ?? 0;
$rows   = $dialogues;
$rows[] = null;
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php if ($level !== null): ?>
  <?= component('partials/admin-head', [
      'title'   => 'Dialog wilayah',
      'eyebrow' => 'Wilayah ' . $level->sequence . ' · ' . $level->text('name', 'id'),
      'lead'    => 'Percakapan pembuka yang tampil saat siswa masuk wilayah ini, satu slide per baris.',
  ]) ?>
  <?= component('partials/content-nav', ['level' => $level, 'active' => 'dialogues']) ?>
<?php else: ?>
  <?= component('partials/admin-head', [
      'title'   => 'Cerita pembuka',
      'eyebrow' => 'Konten umum',
      'lead'    => 'Slide cerita yang dibaca siswa sebelum membuka peta Kedu pertama kali.',
      'actions' => '<a class="btn btn-quiet btn-sm" href="' . base_url('admin/konten') . '">' . icon('left') . ' Semua konten</a>',
  ]) ?>
<?php endif ?>
<?= $this->include('partials/flash') ?>

<form method="post" action="<?= base_url('admin/konten/dialog/' . $levelId) ?>" class="stack">
  <?= csrf_field() ?>

  <?php foreach ($rows as $index => $row): ?>
    <?php $isNew = $row === null; $p = 'dlg' . $index; $who = $isNew ? '' : (string) $row['character_code']; ?>
    <fieldset class="repeat-row<?= $isNew ? ' is-new' : '' ?>">
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
